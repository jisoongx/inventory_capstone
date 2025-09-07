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
        $ownerId = session('owner_id');

        $validated = $request->validate([
            'prod_code' => 'required|integer|exists:products,prod_code',
            'category_id' => 'required|integer',
            'stock' => 'required|integer|min:1',
            'date_added' => 'required|date',
            'expiration_date' => 'nullable|date',
        ]);

        // Get latest batch number for this product
        $lastBatch = DB::table('inventory')
            ->where('prod_code', $validated['prod_code'])
            ->orderByDesc('id')
            ->value('batch_number');

        // Compute next batch number
        if ($lastBatch && preg_match('/BATCH-(\d+)/', $lastBatch, $matches)) {
            $nextBatchNumber = 'BATCH-' . str_pad(((int)$matches[1]) + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextBatchNumber = 'BATCH-001';
        }

        // Insert new inventory record (restock)
        DB::table('inventory')->insert([
            'prod_code'       => $validated['prod_code'],
            'category_id'     => $validated['category_id'],
            'owner_id'        => $ownerId,
            'stock'           => $validated['stock'],
            'date_added'      => $validated['date_added'],
            'expiration_date' => $validated['expiration_date'] ?? null,
            'batch_number'    => $nextBatchNumber,
            'last_updated'    => now(),
        ]);

        return response()->json([
            'success' => true,
            'batch_number' => $nextBatchNumber, // Return the actual batch number inserted
        ]);
    }

    // =================== Return Last Batch Number (for JS) ===================
    public function getLatestBatch($prodCode)
    {
        $lastBatch = DB::table('inventory')
            ->where('prod_code', $prodCode)
            ->orderByDesc('id')
            ->value('batch_number');

        if ($lastBatch && preg_match('/BATCH-(\d+)/', $lastBatch, $matches)) {
            $nextBatch = 'BATCH-' . str_pad(((int)$matches[1] + 1), 3, '0', STR_PAD_LEFT);
        } else {
            $nextBatch = 'BATCH-001';
        }

        return response()->json([
            'last_batch_number' => $lastBatch,   // the latest batch in DB
            'next_batch_number' => $nextBatch,   // what JS will use for new restock
        ]);
    }







}

    




