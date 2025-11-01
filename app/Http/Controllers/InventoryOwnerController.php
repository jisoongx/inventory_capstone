<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                COALESCE(SUM(i.stock), 0) AS total_stock_in,
                COALESCE((
                    SELECT SUM(ri.item_quantity) 
                    FROM receipt_item ri 
                    JOIN receipt r ON ri.receipt_id = r.receipt_id 
                    WHERE ri.prod_code = p.prod_code
                ), 0) AS total_stock_out_sales,
                COALESCE((
                    SELECT SUM(damaged_quantity) 
                    FROM damaged_items 
                    WHERE prod_code = p.prod_code
                ), 0) AS total_stock_out_damaged,
                (COALESCE(SUM(i.stock), 0) - 
                COALESCE((
                    SELECT SUM(ri.item_quantity) 
                    FROM receipt_item ri 
                    JOIN receipt r ON ri.receipt_id = r.receipt_id 
                    WHERE ri.prod_code = p.prod_code
                ), 0) - 
                COALESCE((
                    SELECT SUM(damaged_quantity) 
                    FROM damaged_items 
                    WHERE prod_code = p.prod_code
                ), 0)) AS total_stock
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


public function showProductDetails($prodCode)
{
    // Get product info
    $product = DB::table('products')
        ->join('units', 'products.unit_id', '=', 'units.unit_id')
        ->select('products.*', 'units.unit as unit')
        ->where('products.prod_code', $prodCode)
        ->first();

    if (!$product) {
        abort(404, 'Product not found');
    }

    // Stock-in History (from inventory table) - Only batches with quantity > 0
    $stockInHistory = DB::table('inventory')
        ->where('prod_code', $prodCode)
        ->where('stock', '>', 0)
        ->whereNotNull('batch_number')
        ->orderBy('date_added', 'desc')
        ->orderBy('batch_number', 'desc')
        ->get();

    // Stock-out from Sales (from receipt_item table)
    $stockOutSalesHistory = DB::table('receipt_item as ri')
        ->join('receipt as r', 'ri.receipt_id', '=', 'r.receipt_id')
        ->join('products as p', 'ri.prod_code', '=', 'p.prod_code')
        ->leftJoin('staff as s', 'r.staff_id', '=', 's.staff_id')
        ->leftJoin('owners as o', 'r.owner_id', '=', 'o.owner_id')
        ->select(
            'ri.item_id',
            'ri.item_quantity as quantity_sold',
            'ri.prod_code',
            'r.receipt_id',
            'r.receipt_date',
            'p.selling_price',
            DB::raw('(ri.item_quantity * p.selling_price) as total_amount'),
            DB::raw('COALESCE(CONCAT(s.firstname, " ", s.lastname), CONCAT(o.firstname, " ", o.lastname), "System") as sold_by')
        )
        ->where('ri.prod_code', $prodCode)
        ->orderBy('r.receipt_date', 'desc')
        ->get();

    // Stock-out from Damaged/Expired Items
    $stockOutDamagedHistory = DB::table('damaged_items as di')
        ->leftJoin('staff as s', 'di.staff_id', '=', 's.staff_id')
        ->leftJoin('owners as o', 'di.owner_id', '=', 'o.owner_id')
        ->select(
            'di.*',
            DB::raw('COALESCE(CONCAT(s.firstname, " ", s.lastname), CONCAT(o.firstname, " ", o.lastname), "System") as reported_by')
        )
        ->where('di.prod_code', $prodCode)
        ->orderBy('di.damaged_date', 'desc')
        ->get();

    // Batch Stock-out History - Track inventory reductions per batch
    $manualBatchStockOut = collect();

    // Get all inventory changes for this product, ordered by batch and date
    $inventoryChanges = DB::table('inventory')
        ->where('prod_code', $prodCode)
        ->whereNotNull('batch_number')
        ->orderBy('batch_number')
        ->orderBy('last_updated', 'asc')
        ->get();

    // Group by batch number to track each batch separately
    $batches = $inventoryChanges->groupBy('batch_number');

    foreach ($batches as $batchNumber => $batchRecords) {
        // Sort batch records by date
        $sortedRecords = $batchRecords->sortBy('last_updated');
        
        $previousStock = null;
        
        foreach ($sortedRecords as $record) {
            if ($previousStock !== null && $record->stock < $previousStock) {
                // Stock decreased - this is a stock-out event
                $quantityOut = $previousStock - $record->stock;
                
                $manualBatchStockOut->push((object)[
                    'batch_number' => $batchNumber,
                    'date' => $record->last_updated,
                    'quantity_out' => $quantityOut,
                    'type' => 'sale',
                    'reference' => 'INV-' . $record->inven_code,
                    'sold_by' => 'System'
                ]);
            }
            $previousStock = $record->stock;
        }
    }

    // Add damaged items with batch number tracing
    foreach ($stockOutDamagedHistory as $damaged) {
        // Find which batch was active when the damage occurred
        $batchForDamage = DB::table('inventory')
            ->where('prod_code', $prodCode)
            ->whereNotNull('batch_number')
            ->where('date_added', '<=', $damaged->damaged_date)
            ->where(function($query) use ($damaged) {
                $query->whereNull('expiration_date')
                    ->orWhere('expiration_date', '>=', $damaged->damaged_date);
            })
            ->orderBy('date_added', 'desc')
            ->first();

        $batchNumber = $batchForDamage ? $batchForDamage->batch_number : 'N/A';

        $manualBatchStockOut->push((object)[
            'batch_number' => $batchNumber,
            'date' => $damaged->damaged_date,
            'quantity_out' => $damaged->damaged_quantity,
            'type' => 'damaged',
            'reference' => 'DAMAGED-' . $damaged->damaged_id,
            'sold_by' => $damaged->reported_by // Use the reported_by from the join
        ]);
    }

    // Sort all stock-out events by date
    $manualBatchStockOut = $manualBatchStockOut->sortByDesc('date')->values();

    // Batch grouping for stock-in
    $batchGroups = $stockInHistory->groupBy('batch_number');

    // Summary calculations
    $totalStockIn = $stockInHistory->sum('stock');
    $totalStockOutSold = $stockOutSalesHistory->sum('quantity_sold');
    $totalStockOutDamaged = $stockOutDamagedHistory->sum('damaged_quantity');
    $totalStockOut = $totalStockOutSold + $totalStockOutDamaged;
    $currentStock = $totalStockIn - $totalStockOut;
    $totalRevenue = $stockOutSalesHistory->sum('total_amount');
    $turnoverRate = $totalStockIn > 0 ? ($totalStockOutSold / $totalStockIn) * 100 : 0;

        // Count expired items (damaged_reason = 'Expired')
    $totalExpired = DB::table('damaged_items')
        ->where('prod_code', $prodCode)
        ->where('damaged_reason', 'Expired')
        ->sum('damaged_quantity');

    // Count damaged items (all except 'Expired')
    $totalDamaged = DB::table('damaged_items')
        ->where('prod_code', $prodCode)
        ->where('damaged_reason', '!=', 'Expired')
        ->sum('damaged_quantity');

    return view('inventory-owner-product-info', compact(
        'product',
        'stockInHistory',
        'stockOutSalesHistory',
        'stockOutDamagedHistory',
        'manualBatchStockOut',
        'batchGroups',
        'totalStockIn',
        'totalStockOut',
        'totalStockOutSold',
        'totalStockOutDamaged',
        'totalExpired', 
        'totalDamaged', 
        'currentStock',
        'totalRevenue',
        'turnoverRate'
    ));
}


    public function pricingHistory($prodCode)
    {
        $ownerId = session('owner_id');

        $priceHistory = DB::select("
            SELECT 
                ph.price_history_id,
                ph.prod_code,
                ph.old_cost_price,
                ph.old_selling_price,
                ph.effective_from,
                ph.effective_to,
                ph.updated_by,
                ph.owner_id,
                IFNULL(SUM(
                    CASE 
                        WHEN r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                        THEN ri.item_quantity
                        ELSE 0
                    END
                ), 0) AS total_sold,
                IFNULL(SUM(
                    CASE 
                        WHEN r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                        THEN ri.item_quantity * ph.old_selling_price
                        ELSE 0
                    END
                ), 0) AS total_sales
            FROM pricing_history ph
            LEFT JOIN receipt_item ri ON ri.prod_code = ph.prod_code
            LEFT JOIN receipt r ON r.receipt_id = ri.receipt_id
            WHERE ph.prod_code = ? 
            AND ph.owner_id = ? 
            AND ph.effective_to IS NOT NULL   -- ✅ only show completed/old prices
            GROUP BY 
                ph.price_history_id,
                ph.prod_code,
                ph.old_cost_price,
                ph.old_selling_price,
                ph.effective_from,
                ph.effective_to,
                ph.updated_by,
                ph.owner_id
            ORDER BY ph.effective_to DESC, ph.effective_from DESC
        ", [$prodCode, $ownerId]);



        return view('inventory-owner-pricing-history', compact('priceHistory', 'prodCode'));
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

    $units = DB::table('units')->get();
    $statuses = ['active', 'archived'];

    // ✅ Fetch price history for dropdown
    $priceHistory = DB::table('pricing_history')
        ->where('prod_code', $prodCode)
        ->where('owner_id', session('owner_id'))
        ->orderBy('effective_from', 'desc')
        ->limit(5)
        ->get();

    return view('inventory-owner-edit', compact('product', 'units', 'statuses', 'priceHistory'));
}


public function update(Request $request, $prodCode)
{
    $ownerId = session('owner_id');

    // 🔹 Updated validation to include previous_cost_price
    $validated = $request->validate([
        'name'             => 'required|string|max:100',
        'barcode'          => 'nullable|string|max:50',
        'cost_price'       => 'required|numeric|min:0',
        'selling_price'    => 'nullable|numeric|min:0',
        'previous_prices'  => 'nullable|numeric|min:0',
        'previous_cost_price' => 'nullable|numeric|min:0', // 🔹 NEW
        'description'      => 'nullable|string',
        'unit_id'          => 'required|integer',
        'stock_limit'      => 'nullable|integer|min:0',
        'prod_image'       => 'nullable|image|max:2048',
        'prod_status'      => 'required|in:active,archived',
    ]);

    // Fetch current product data
    $product = DB::table('products')
        ->where('prod_code', $prodCode)
        ->first();

    if (!$product) {
        return redirect()->route('inventory-owner')->with('error', 'Product not found.');
    }

    // Handle image upload if present
    $photoPath = null;
    if ($request->hasFile('prod_image')) {
        $photoPath = $request->file('prod_image')->store('product_images', 'public');
    }

    // 🔹 Determine which prices to use (new input or previous selection)
    $finalSellingPrice = $request->previous_prices ?: $request->selling_price;
    $finalCostPrice = $request->previous_cost_price ?: $request->cost_price;

    // 🔹 Validate that at least one selling price is provided
    if (!$finalSellingPrice) {
        return back()->with('error', 'Please provide or select a selling price.')->withInput();
    }

    // Prepare updated product data
    $updateData = [
        'name'          => $validated['name'],
        'barcode'       => $validated['barcode'],
        'cost_price'    => $finalCostPrice, // 🔹 Use the determined cost price
        'selling_price' => $finalSellingPrice,
        'unit_id'       => $validated['unit_id'],
        'stock_limit'   => $validated['stock_limit'],
        'description'   => $validated['description'] ?? null,
        'prod_status'   => $validated['prod_status'],
    ];

    if ($photoPath) {
        $updateData['prod_image'] = $photoPath;
    }

    // 🔹 Check if price changed compared to current product
    if ($product->cost_price != $finalCostPrice || $product->selling_price != $finalSellingPrice) {

        // Close the current active price record (old price)
        DB::table('pricing_history')
            ->where('prod_code', $prodCode)
            ->where('owner_id', $ownerId)
            ->whereNull('effective_to')
            ->update(['effective_to' => now()]);

        // Insert the new active price record (new price)
        DB::table('pricing_history')->insert([
            'prod_code'         => $prodCode,
            'old_cost_price'    => $finalCostPrice,    // 🔹 Use final cost price
            'old_selling_price' => $finalSellingPrice, // 🔹 Use final selling price
            'owner_id'          => $ownerId,
            'updated_by'        => session('staff_id') ?? null,
            'effective_from'    => now(),
            'effective_to'      => null,  // active price
        ]);
    }

    // Update product table
    DB::table('products')
        ->where('prod_code', $prodCode)
        ->update($updateData);

    // Log activity
    ActivityLogController::log(
        'Updated product "' . $validated['name'] . '".',
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

    // Handle category
    if ($validated['category_id'] === 'other' && !empty($validated['custom_category'])) {
        $categoryId = DB::table('categories')->insertGetId([
            'category' => $validated['custom_category'],
            'owner_id' => $ownerId,
        ]);
    } else {
        $categoryId = $validated['category_id'];
    }

    // Handle unit
    if ($validated['unit_id'] === 'other' && !empty($validated['custom_unit'])) {
        $unitId = DB::table('units')->insertGetId([
            'unit' => $validated['custom_unit'],
            'owner_id' => $ownerId,
        ]);
    } else {
        $unitId = $validated['unit_id'];
    }

    // Handle photo upload with default fallback
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('product_images', 'public');
    } else {
        // Store relative path to your default image in public/assets
        $photoPath = 'assets/no-product-image.png';
    }


    // Check if product exists
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

    // Insert initial pricing record for the newly registered product
    DB::table('pricing_history')->insert([
        'prod_code'         => $prodCode,
        'old_cost_price'    => $validated['cost_price'],
        'old_selling_price' => $validated['selling_price'],
        'owner_id'          => $ownerId,
        'updated_by'        => session('staff_id') ?? null,
        'effective_from'    => now(),
        'effective_to'      => null, // current active price
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


public function addCategory(Request $request)
{
    $ownerId = session('owner_id');
    $categoryName = trim($request->input('category'));

    if (empty($categoryName)) {
        return response()->json(['success' => false, 'message' => 'Category name cannot be empty.']);
    }

    // Check for duplicates
    $exists = DB::table('categories')
        ->where('owner_id', $ownerId)
        ->whereRaw('LOWER(category) = ?', [strtolower($categoryName)])
        ->exists();

    if ($exists) {
        return response()->json(['success' => false, 'message' => 'Category already exists.']);
    }

    // Insert new category (your table only has 3 columns)
    DB::table('categories')->insert([
        'category' => $categoryName,
        'owner_id' => $ownerId,
    ]);

    return response()->json(['success' => true, 'message' => 'Category added successfully.']);
}




public function getCategoryProducts($categoryId)
{
    try {
        $ownerId = session('owner_id');

        if (!$ownerId) {
            return response()->json(['error' => 'Unauthorized. Please log in again.'], 403);
        }

        $products = DB::select("
            SELECT 
                p.prod_code,
                p.name,
                p.category_id,
                COALESCE(SUM(i.stock), 0) AS stock
            FROM products p
            LEFT JOIN inventory i ON p.prod_code = i.prod_code
            WHERE p.category_id = :category_id
            AND p.owner_id = :owner_id
            GROUP BY p.prod_code, p.name, p.category_id
            ORDER BY p.name ASC
        ", [
            'category_id' => $categoryId,
            'owner_id' => $ownerId
        ]);

        return response()->json($products);

    } catch (\Exception $e) {
        \Log::error('Error fetching products by category: ' . $e->getMessage());
        return response()->json(['error' => 'Server error. Please check logs.'], 500);
    }
}



// Return the next batch identifier for a product (e.g. BATCH-3)
public function getLatestBatch($prod_code)
{
    $ownerId = session('owner_id');

    $lastBatch = DB::table('inventory')
        ->where('prod_code', $prod_code)
        ->where('owner_id', $ownerId)
        ->orderBy('inven_code', 'desc')
        ->value('batch_number');

    // Parse BATCH-# if present, else default to BATCH-0 then +1
    if ($lastBatch && preg_match('/BATCH-(\d+)/', $lastBatch, $m)) {
        $next = 'BATCH-' . (((int)$m[1]) + 1);
    } else {
        $next = 'BATCH-1';
    }

    return response()->json(['next_batch' => $next, 'last_batch' => $lastBatch]);
}

public function bulkRestock(Request $request)
{
    $ownerId = session('owner_id');
    $items = $request->input('items', []);

    if (empty($items)) {
        return response()->json(['success' => false, 'message' => 'No products provided for restocking.']);
    }

    DB::beginTransaction();
    try {
        foreach ($items as $it) {
            $prodCode = $it['prod_code'] ?? null;
            $qty = (int) ($it['qty'] ?? 0);
            $expiration = $it['expiration_date'] ?? null;
            $categoryId = $it['category_id'] ?? null;

            if (!$prodCode || $qty <= 0) continue;

            // Always increment the batch per new restock entry
            $latestBatch = DB::table('inventory')
                ->where('prod_code', $prodCode)
                ->where('owner_id', $ownerId)
                ->orderBy('inven_code', 'desc')
                ->value('batch_number');

            $nextBatchNumber = ($latestBatch && preg_match('/BATCH-(\d+)/', $latestBatch, $m))
                ? 'BATCH-' . (((int)$m[1]) + 1)
                : 'BATCH-1';

            DB::table('inventory')->insert([
                'prod_code' => $prodCode,
                'category_id' => $categoryId,
                'stock' => $qty,
                'batch_number' => $nextBatchNumber,
                'expiration_date' => $expiration,
                'owner_id' => $ownerId,
                'date_added' => now(),
                'last_updated' => now(),
            ]);
        }

        DB::commit();

        $ip = $request->ip();
        $guard = 'owner';
        $user = Auth::guard('owner')->user();
        ActivityLogController::log('Bulk Restock Products', $guard, $user, $ip);

        return response()->json(['success' => true, 'message' => 'Restock saved successfully.']);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Bulk restock error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to save bulk restock. Check logs.']);
    }
}

    public function store(Request $request)
    {
        $ownerId = Auth::guard('owner')->id();

        try {
            $validated = $request->validate([
                'prod_code' => 'required|exists:products,prod_code',
                'damaged_quantity' => 'required|integer|min:1',
                'damaged_type' => 'required|string|max:20',
                'damaged_reason' => 'required|string|max:255',
            ]);

            $totalStock = DB::table('inventory')
                ->where('prod_code', $validated['prod_code'])
                ->where('owner_id', $ownerId)
                ->sum('stock');

            if ($totalStock === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product stock is zero. Cannot record damage.'
                ]);
            }

            if ($validated['damaged_quantity'] > $totalStock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Damaged quantity exceeds available stock.'
                ]);
            }

            DB::table('damaged_items')->insert([
                'prod_code' => $validated['prod_code'],
                'damaged_quantity' => $validated['damaged_quantity'],
                'damaged_type' => $validated['damaged_type'],
                'damaged_reason' => $validated['damaged_reason'],
                'owner_id' => $ownerId,
                'damaged_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Damaged item recorded successfully!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.'
            ]);
        }
    }


    public function showDamageItemsForm()
    {
        $ownerId = Auth::guard('owner')->id();

        // Fetch all products for the product dropdown
        $products = DB::table('products')
            ->where('owner_id', $ownerId)
            ->get();

        // Fetch all damaged items recorded by the logged-in owner
        $damagedItems = DB::table('damaged_items')
            ->join('products', 'damaged_items.prod_code', '=', 'products.prod_code')
            ->where('damaged_items.owner_id', $ownerId)
            ->select('damaged_items.*', 'products.name as product_name')
            ->orderBy('damaged_items.damaged_date', 'desc') // ✅ Show latest records first
            ->get();


        // Return the view with both products and damaged items data
        return view('damage-items', compact('damagedItems', 'products'));
    }
}

    




