<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InventoryOwnerController extends Controller
{
   public function index(Request $request)
    {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;
        $owner_name = $owner->firstname;

        session(['owner_id' => $owner_id]);

        $search   = $request->input('search');
        $category = $request->input('category');
        $status   = $request->input('status', 'active'); // default to active

        $query = "
            SELECT
                p.prod_code,
                p.category_id,
                MIN(p.barcode)       AS barcode,
                MIN(p.name)          AS name,
                MIN(p.cost_price)    AS cost_price,
                MIN(p.selling_price) AS selling_price,
                MIN(p.prod_image)    AS prod_image,
                MIN(u.unit)          AS unit,
                MIN(c.category)      AS category,
                p.prod_status,
                COALESCE(SUM(i.stock), 0) AS stock
            FROM products p
            JOIN units u       ON p.unit_id = u.unit_id
            JOIN categories c  ON p.category_id = c.category_id
            LEFT JOIN inventory i ON i.prod_code = p.prod_code
            WHERE p.owner_id = :owner_id
            AND p.prod_status = :status
        ";

        $params = [
            'owner_id' => $owner_id,
            'status'   => $status,
        ];

        if (!empty($search)) {
            $query .= " AND (LOWER(p.name) LIKE :search_name OR LOWER(p.barcode) LIKE :search_barcode)";
            $params['search_name']    = '%' . strtolower($search) . '%';
            $params['search_barcode'] = '%' . strtolower($search) . '%';
        }

        if (!empty($category)) {
            $query .= " AND p.category_id = :category_id";
            $params['category_id'] = $category;
        }

        $query .= " GROUP BY p.prod_code, p.category_id, p.prod_status ORDER BY p.prod_code DESC";

        $products   = DB::select($query, $params);
        $categories = DB::select("SELECT category_id, category FROM categories WHERE owner_id = :owner_id ORDER BY category ASC", ['owner_id' => $owner_id]);
        $units = DB::select("SELECT unit_id, unit FROM units WHERE owner_id = :owner_id ORDER BY unit ASC", ['owner_id' => $owner_id]);
        
        return view('inventory-owner', compact('owner_name', 'products', 'categories', 'units', 'search', 'category', 'status'));
    }



    public function suggest(Request $request)
    {
        $term = $request->query('term');
        $ownerId = session('owner_id');

        if (!$term) {
            return response()->json([]);
        }

        // Check if user is typing a number (assume it's a barcode)
        $isBarcodeSearch = is_numeric($term);

        if ($isBarcodeSearch) {
            $results = DB::table('products')
                ->where('owner_id', $ownerId)
                ->where('barcode', 'LIKE', $term . '%')
                ->pluck('barcode');
        } else {
            $results = DB::table('products')
                ->where('owner_id', $ownerId)
                ->where('name', 'LIKE', $term . '%')
                ->pluck('name');
        }

        return response()->json($results);
    }

    //for the info button in the product list table
    public function showProductDetails($prodCode)
    {
        // Get product info (include stock_limit)
        $product = DB::table('products')
            ->join('units', 'products.unit_id', '=', 'units.unit_id')
            ->select('products.*', 'units.unit as unit')
            ->where('products.prod_code', $prodCode)
            ->first();

        if (!$product) {
            abort(404, 'Product not found');
        }

        // Get all restock records
        $restocks = DB::table('inventory')
            ->where('prod_code', $prodCode)
            ->whereNotNull('batch_number') // only restocks
            ->orderBy('date_added', 'desc')
            ->get();

        // Calculate total stock
        $totalStock = $restocks->sum('stock');

        // Compute threshold = 20% of stock_limit
        $lowStockThreshold = $product->stock_limit * 0.2;

        return view('inventory-owner-product-info', compact('product', 'restocks', 'totalStock', 'lowStockThreshold'));
    }


    public function edit($prodCode)
    {
        $product = DB::table('products')
            ->join('units', 'products.unit_id', '=', 'units.unit_id')
            ->select('products.*', 'units.unit as unit')
            ->where('products.prod_code', $prodCode)
            ->first();

        if (!$product) {
            abort(404, 'Product not found');
        }

        $units = DB::table('units')->get(); // dropdown
        $statuses = ['active', 'archived']; // for prod_status dropdown

        return view('inventory-owner-edit', compact('product', 'units', 'statuses'));
    }

    public function update(Request $request, $prodCode)
    {
        $ownerId = session('owner_id');

        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'barcode'       => 'nullable|string|max:50',
            'cost_price'    => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'description'   => 'nullable|string',
            'unit_id'       => 'required|integer',
            'stock_limit'   => 'nullable|integer|min:0',
            'prod_image'    => 'nullable|image|max:2048',
            'prod_status'   => 'required|in:active,archived', // ✅ added validation
        ]);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('prod_image')) {
            $photoPath = $request->file('prod_image')->store('product_images', 'public');
        }

        // Prepare update data
        $updateData = [
            'name'          => $validated['name'],
            'barcode'       => $validated['barcode'],
            'cost_price'    => $validated['cost_price'],
            'selling_price' => $validated['selling_price'],
            'unit_id'       => $validated['unit_id'],
            'stock_limit'   => $validated['stock_limit'],
            'description'   => $validated['description'] ?? null,
            'prod_status'   => $validated['prod_status'], // ✅ added here
        ];

        if ($photoPath) {
            $updateData['prod_image'] = $photoPath;
        }

        DB::table('products')
            ->where('prod_code', $prodCode)
            ->update($updateData);

        ActivityLogController::log(
            'Updated product: ' . $validated['name'],
            'owner',
            Auth::guard('owner')->user(),
            request()->ip()
        );


        return redirect()->route('inventory-owner')
            ->with('success', 'Product updated successfully.');
    }


    public function archive($prodCode)
    {
        $ownerId = session('owner_id');

        $product = DB::table('products')
            ->select('name')
            ->where('prod_code', $prodCode)
            ->where('owner_id', $ownerId)
            ->first();

        DB::table('products')
            ->where('prod_code', $prodCode)
            ->where('owner_id', $ownerId)
            ->update(['prod_status' => 'archived']);

        ActivityLogController::log(
            'Archived product: ' . ($product->name ?? 'Unknown'),
            'owner',
            Auth::guard('owner')->user(),
            request()->ip()
        );

        return redirect()->route('inventory-owner')->with('success', 'Product archived successfully.');
    }

    public function unarchive($prodCode)
    {
        $ownerId = session('owner_id');

        $product = DB::table('products')
            ->select('name')
            ->where('prod_code', $prodCode)
            ->where('owner_id', $ownerId)
            ->first();

        DB::table('products')
            ->where('prod_code', $prodCode)
            ->where('owner_id', $ownerId)
            ->update(['prod_status' => 'active']);


        ActivityLogController::log(
            'Unarchived product: ' . ($product->name ?? 'Unknown'),
            'owner',
            Auth::guard('owner')->user(),
            request()->ip()
        );

        return redirect()->route('inventory-owner')->with('success', 'Product unarchived successfully.');
    }



    public function checkBarcode(Request $request)
    {
        $barcode = $request->input('barcode');
        $ownerId = session('owner_id');

        $product = DB::table('products')
            ->where('barcode', $barcode)
            ->where('owner_id', $ownerId)
            ->first();

        if ($product) {
            return response()->json([
                'exists' => true,
                'product' => [
                    'prod_code'   => $product->prod_code,
                    'name'        => $product->name,
                    'prod_image'  => $product->prod_image,
                    'category_id' => $product->category_id,
                    'barcode'     => $product->barcode,
                ]
            ]);
        } else {
            return response()->json(['exists' => false]);
        }
    }



    public function registerProduct(Request $request)
{
    $ownerId = session('owner_id');

    $validated = $request->validate([
        'barcode' => 'required|string|max:50',
        'name' => 'required|string|max:100',
        'cost_price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'description' => 'nullable|string',
        'category_id' => 'nullable',
        'unit_id' => 'nullable',
        'custom_category' => 'nullable|string|max:100',
        'custom_unit' => 'nullable|string|max:50',
        'photo' => 'nullable|image|max:2048',
        'stock_limit' => 'required|integer|min:0',
        'expiration_date' => 'nullable|date',
        'batch_number' => 'nullable|string|max:50'
    ]);

    // ✅ Handle category
    if ($validated['category_id'] === 'other' && !empty($validated['custom_category'])) {
        $categoryId = DB::table('categories')->insertGetId([
            'category' => $validated['custom_category'],
            'owner_id' => $ownerId,
        ]);
    } else {
        $categoryId = $validated['category_id'];
    }

    // ✅ Handle unit
    if ($validated['unit_id'] === 'other' && !empty($validated['custom_unit'])) {
        $unitId = DB::table('units')->insertGetId([
            'unit' => $validated['custom_unit'],
            'owner_id' => $ownerId,
        ]);
    } else {
        $unitId = $validated['unit_id'];
    }

    // ✅ Handle photo upload with default fallback
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('product_images', 'public');
    } else {
        // Store relative path to your default image in public/assets
        $photoPath = 'assets/no-product-image.png';
    }


    // ✅ Check if product exists
    $product = DB::table('products')
        ->where('barcode', $validated['barcode'])
        ->where('owner_id', $ownerId)
        ->first();

    if ($product) {
        $prodCode = $product->prod_code;
    } else {
        // Insert product
        $prodCode = DB::table('products')->insertGetId([
            'barcode' => $validated['barcode'],
            'name' => $validated['name'],
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['selling_price'],
            'description' => $validated['description'] ?? null,
            'owner_id' => $ownerId,
            'staff_id' => null,
            'category_id' => $categoryId,
            'unit_id' => $unitId,
            'prod_image' => $photoPath,
            'stock_limit' => $validated['stock_limit'],
        ]);
    }

    // Insert inventory
    DB::table('inventory')->insert([
        'prod_code' => $prodCode,
        'owner_id' => $ownerId,
        'category_id' => $categoryId,
        'date_added' => now(),
        'expiration_date' => $validated['expiration_date'] ?? null,
        'last_updated' => now(),
        'batch_number' => $validated['batch_number'] ?? null,
    ]);

        $ip = $request->ip();
        $guard = 'owner';
        $user = Auth::guard('owner')->user();

        ActivityLogController::log(
            'Registered new product: ' . $validated['name'],
            $guard,
            $user,
            $ip
        );
    return response()->json(['success' => true]);
}




   public function restockProduct(Request $request)
{
    $prodCode = $request->input('prod_code');
    $quantity = $request->input('stock');          // from modal input
    $expiryDate = $request->input('expiration_date');     // from modal input
    $dateAdded = $request->input('date_added');       // from modal input

    // Get the latest batch
    $latestBatch = DB::table('inventory')
        ->where('prod_code', $prodCode)
        ->orderBy('inven_code', 'desc')
        ->first();

    $nextBatchNumber = $latestBatch && $latestBatch->batch_number
        ? 'BATCH-' . (((int) str_replace('BATCH-', '', $latestBatch->batch_number)) + 1)
        : 'BATCH-1';

    DB::table('inventory')->insert([
        'prod_code' => $prodCode,
        'stock' => $quantity,                 
        'expiration_date' => $expiryDate,
        'batch_number' => $nextBatchNumber,
        'date_added' => $dateAdded,           
        'last_updated' => now(),               
        'owner_id' => session('owner_id'),
        'category_id' => DB::table('products')->where('prod_code', $prodCode)->value('category_id'),
    ]);

    return response()->json([
        'success' => true,
        'batch_number' => $nextBatchNumber,
        'message' => "Product restocked successfully under $nextBatchNumber"
    ]);
}



public function getLatestBatch($prodCode)
{
    $lastBatch = DB::table('inventory')
        ->where('prod_code', $prodCode)
        ->orderBy('inven_code', 'desc')
        ->value('batch_number');

    if ($lastBatch && preg_match('/BATCH-(\d+)/', $lastBatch, $matches)) {
        $nextBatch = 'BATCH-' . (((int) $matches[1]) + 1);
    } else {
        $nextBatch = 'BATCH-1';
    }

    return response()->json([
        'success' => true,
        'last_batch_number' => $lastBatch ?: 'None',
        'next_batch_number' => $nextBatch,
    ]);
}











}

    




