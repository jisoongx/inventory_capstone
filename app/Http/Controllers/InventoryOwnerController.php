<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
                COALESCE(SUM(i.stock), 0) AS stock
            FROM products p
            JOIN units u       ON p.unit_id = u.unit_id
            JOIN categories c  ON p.category_id = c.category_id
            LEFT JOIN inventory i ON i.prod_code = p.prod_code
            WHERE p.owner_id = :owner_id
        ";

        $params = ['owner_id' => $owner_id];

        // Case-insensitive search
        if (!empty($search)) {
            $query .= " AND (LOWER(p.name) LIKE :search_name OR LOWER(p.barcode) LIKE :search_barcode)";
            $params['search_name']    = '%' . strtolower($search) . '%';
            $params['search_barcode'] = '%' . strtolower($search) . '%';
        }

        // Category filter
        if (!empty($category)) {
            $query .= " AND p.category_id = :category_id";
            $params['category_id'] = $category;
        }

        // include p.category_id in GROUP BY
        $query .= " GROUP BY p.prod_code, p.category_id ORDER BY p.prod_code ASC";

        $products   = DB::select($query, $params);
        $categories = DB::select("SELECT category_id, category FROM categories ORDER BY category ASC");
        $units      = DB::select("SELECT unit_id, unit FROM units ORDER BY unit ASC");

        return view('inventory-owner', compact('owner_name', 'products', 'categories', 'units', 'search', 'category'));
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
            'category_id' => 'required|integer',
            'unit_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'photo' => 'nullable|image|max:2048',
            // Product-specific field now
            'stock_limit' => 'required|integer|min:0',
            // Inventory-specific
            'expiration_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:50'
        ]);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('product_images', 'public');
        }

        // ✅ Check if product already exists
        $product = DB::table('products')
            ->where('barcode', $validated['barcode'])
            ->where('owner_id', $ownerId)
            ->first();

        if ($product) {
            $prodCode = $product->prod_code;
        } else {
            // Insert into products with stock_limit
            $prodCode = DB::table('products')->insertGetId([
                'barcode' => $validated['barcode'],
                'name' => $validated['name'],
                'cost_price' => $validated['cost_price'],
                'selling_price' => $validated['selling_price'],
                'description' => $validated['description'] ?? null,
                'owner_id' => $ownerId,
                'staff_id' => null,
                'category_id' => $validated['category_id'],
                'unit_id' => $validated['unit_id'],
                'prod_image' => $photoPath,
                'stock_limit' => $validated['stock_limit'], // ✅ moved here
            ]);
        }

        // ✅ Insert inventory record (no stock_limit anymore)
        DB::table('inventory')->insert([
            'prod_code' => $prodCode,
            'owner_id' => $ownerId,
            'category_id' => $validated['category_id'],
            'stock' => $validated['quantity'],
            'date_added' => now(),
            'expiration_date' => $validated['expiration_date'] ?? null,
            'last_updated' => now(),
            'batch_number' => $validated['batch_number'] ?? null,
        ]);

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

    




