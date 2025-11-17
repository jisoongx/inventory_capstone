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

        DB::statement("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");

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
                -- Stock in Inventory (already reduced by sales)
                COALESCE(SUM(i.stock), 0) AS inventory_stock,
                -- Total Stock Out from Sales
                COALESCE((
                    SELECT SUM(ri.item_quantity) 
                    FROM receipt_item ri 
                    JOIN receipt r ON ri.receipt_id = r.receipt_id 
                    WHERE ri.prod_code = p.prod_code
                ), 0) AS total_stock_out_sales,
                -- Total Damaged Items (prevent duplicates by grouping by inven_code)
                COALESCE((
                    SELECT SUM(di.damaged_quantity)
                    FROM damaged_items di
                    WHERE di.inven_code = i.inven_code
                    AND di.damaged_id IN (
                        SELECT MIN(damaged_id)
                        FROM damaged_items di2
                        INNER JOIN inventory i2 ON di2.inven_code = i2.inven_code
                        INNER JOIN products p2 ON i2.prod_code = p2.prod_code
                        WHERE i2.prod_code = i.prod_code  -- Connect to main query
                        GROUP BY di2.inven_code
                    )
                ), 0) AS total_stock_out_damaged,
                -- Current Stock: Just the sum of stock from inventory table
                COALESCE((
                    SELECT SUM(i.stock)
                    FROM inventory i
                    WHERE i.prod_code = p.prod_code
                ), 0) AS current_stock,
                -- Total Stock In (Original): Current Stock + Sales + Damaged
                (COALESCE(SUM(i.stock), 0) + 
                COALESCE((
                    SELECT SUM(ri.item_quantity) 
                    FROM receipt_item ri 
                    JOIN receipt r ON ri.receipt_id = r.receipt_id 
                    WHERE ri.prod_code = p.prod_code
                ), 0)) AS total_stock_in
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

    // Stock-in History (from inventory table)
    $stockInHistory = DB::table('inventory')
        ->where('prod_code', $prodCode)
        ->whereNotNull('batch_number')
        ->orderBy('date_added', 'desc')
        ->orderBy('batch_number', 'desc')
        ->get();

    // Get sales data per batch to calculate original quantities
    $salesPerBatch = DB::table('receipt_item as ri')
        ->join('inventory as i', 'ri.inven_code', '=', 'i.inven_code')
        ->where('ri.prod_code', $prodCode)
        ->whereNotNull('i.batch_number')
        ->select('i.batch_number', DB::raw('SUM(ri.item_quantity) as total_sold'))
        ->groupBy('i.batch_number')
        ->pluck('total_sold', 'batch_number');

    // Get damaged items per batch
    $damagedPerBatch = DB::table('damaged_items as di')
        ->join('inventory as i', 'di.inven_code', '=', 'i.inven_code')
        ->join('products as p', 'i.prod_code', '=', 'p.prod_code') // Add this join
        ->where('p.prod_code', $prodCode) // Use p.prod_code instead of di.prod_code
        ->whereNotNull('i.batch_number')
        ->whereIn('di.damaged_id', function($query) use ($prodCode) {
            $query->select(DB::raw('MIN(damaged_id)'))
                ->from('damaged_items as di2')
                ->join('inventory as i2', 'di2.inven_code', '=', 'i2.inven_code') // Join in subquery
                ->where('i2.prod_code', $prodCode) // Use i2.prod_code
                ->groupBy('di2.inven_code');
        })
        ->select('i.batch_number', DB::raw('SUM(di.damaged_quantity) as total_damaged'))
        ->groupBy('i.batch_number')
        ->pluck('total_damaged', 'batch_number');

    // Batch grouping for stock-in with original quantities calculated
    $batchGroups = $stockInHistory->groupBy('batch_number')->map(function($batches, $batchNumber) use ($salesPerBatch, $damagedPerBatch) {
        $currentStock = $batches->sum('stock');
        $soldFromBatch = $salesPerBatch->get($batchNumber, 0);
        $damagedFromBatch = $damagedPerBatch->get($batchNumber, 0);
        
        // Calculate original quantity: current + sold + damaged
        $originalQuantity = $currentStock + $soldFromBatch + $damagedFromBatch;
        
        // Add original_quantity to each batch in the group
        return $batches->map(function($batch) use ($originalQuantity) {
            $batch->original_quantity = $originalQuantity;
            return $batch;
        });
    });

    // Stock-out from Sales
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
    // FIXED: Prevent duplicate counting by grouping by inven_code
    // Take the first (oldest) damaged record per inven_code to avoid counting the same item twice
    $stockOutDamagedHistory = DB::table('damaged_items as di')
        ->leftJoin('staff as s', 'di.staff_id', '=', 's.staff_id')
        ->leftJoin('owners as o', 'di.owner_id', '=', 'o.owner_id')
        ->leftJoin('inventory as i', 'di.inven_code', '=', 'i.inven_code') // Add this join
        ->select(
            'di.*',
            DB::raw('COALESCE(CONCAT(s.firstname, " ", s.lastname), CONCAT(o.firstname, " ", o.lastname), "System") as reported_by')
        )
        ->where('i.prod_code', $prodCode) // Use i.prod_code instead of di.prod_code
        ->whereIn('di.damaged_id', function($query) use ($prodCode) {
            // Get only the first damaged record for each unique inven_code
            $query->select(DB::raw('MIN(damaged_id)'))
                ->from('damaged_items as di2')
                ->leftJoin('inventory as i2', 'di2.inven_code', '=', 'i2.inven_code') // Join in subquery
                ->where('i2.prod_code', $prodCode) // Use i2.prod_code
                ->groupBy('di2.inven_code');
        })
        ->orderBy('di.damaged_date', 'desc')
        ->get();

    // FIXED: Calculate totals correctly
    // Step 1: Get current remaining stock from inventory
    $currentStockInInventory = $stockInHistory->sum('stock');
    
    // Step 2: Get total sold quantity
    $totalStockOutSold = $stockOutSalesHistory->sum('quantity_sold');
    
    // Step 3: Get total damaged/expired quantity
    $totalStockOutDamaged = $stockOutDamagedHistory->sum('damaged_quantity');
    
    // Step 4: Calculate total stock out
    $totalStockOut = $totalStockOutSold + $totalStockOutDamaged;
    
    // Step 5: Calculate TOTAL STOCK (Original stock that was added)
    // Total Stock = Current Stock in Inventory + All items that went out (sold + damaged)
    $totalStockIn = $currentStockInInventory + $totalStockOut;
    
    // Step 6: Current/Remaining Stock is what's left in inventory
    $currentStock = $currentStockInInventory;
    
    // Revenue and other calculations
    $totalRevenue = $stockOutSalesHistory->sum('total_amount');
    $turnoverRate = $totalStockIn > 0 ? ($totalStockOutSold / $totalStockIn) * 100 : 0;

    // Count expired and damaged items
    $totalExpired = $stockOutDamagedHistory->where('damaged_reason', 'Expired')->sum('damaged_quantity');
    $totalDamaged = $stockOutDamagedHistory->where('damaged_reason', '!=', 'Expired')->sum('damaged_quantity');

    // Batch Stock-out History
    $manualBatchStockOut = collect();

    $inventoryChanges = DB::table('inventory')
        ->where('prod_code', $prodCode)
        ->whereNotNull('batch_number')
        ->orderBy('batch_number')
        ->orderBy('last_updated', 'asc')
        ->get();

    $batches = $inventoryChanges->groupBy('batch_number');

    foreach ($batches as $batchNumber => $batchRecords) {
        $sortedRecords = $batchRecords->sortBy('last_updated');
        $previousStock = null;
        
        foreach ($sortedRecords as $record) {
            if ($previousStock !== null && $record->stock < $previousStock) {
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

    foreach ($stockOutDamagedHistory as $damaged) {
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
            'sold_by' => $damaged->reported_by
        ]);
    }

    $manualBatchStockOut = $manualBatchStockOut->sortByDesc('date')->values();

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

    $units = DB::table('units')
        ->where('owner_id', session('owner_id'))
        ->orderBy('unit', 'asc')
        ->get();
    
    $statuses = ['active', 'archived'];

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
        'batch_number' => 'nullable|string|max:50',
        'confirmed_similar' => 'nullable|string',
        'confirmed_category' => 'nullable|string',
        'confirmed_unit' => 'nullable|string'
    ]);

    // Check for existing/similar product names BUT skip this check if user already confirmed similar product
    $confirmedSimilar = $request->input('confirmed_similar') === '1';
    
    if (!$confirmedSimilar) {
        $existingProducts = DB::table('products')
            ->where('owner_id', $ownerId)
            ->get();
        
        $productMatch = $this->findProductNameMatch($validated['name'], $existingProducts);
        
        if ($productMatch) {
            // Only block exact matches
            if ($productMatch['isExact']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product name already exists: ' . $productMatch['name']
                ], 422);
            }
        }
    }

    // ✅ Combined validation approach for categories
    if ($validated['category_id'] === 'other' && !empty($validated['custom_category'])) {
        $confirmedCategory = $request->input('confirmed_category') === '1'; // 🆕 NEW
        
        if (!$confirmedCategory) { // 🆕 Only validate if not confirmed
            $existingCategories = DB::table('categories')
                ->where('owner_id', $ownerId)
                ->get();
            
            // Check 1: Exact case-insensitive match
            $exactMatch = DB::table('categories')
                ->where('owner_id', $ownerId)
                ->whereRaw('LOWER(category) = ?', [strtolower($validated['custom_category'])])
                ->first();
            
            if ($exactMatch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category already exists: ' . $exactMatch->category
                ], 422);
            }
            
            // Check 2: Semantic similarity - DON'T BLOCK, just inform
            $normalizedInput = $this->normalizeName($validated['custom_category']);
            $semanticMatch = $this->findSemanticMatch($normalizedInput, $existingCategories, 'category');

            if ($semanticMatch) {
                // REMOVED: The blocking return statement
                // Frontend will handle the confirmation dialog
                // Just continue to create the category
            }
        }

        // Both checks passed (or user confirmed) - create the new category
        $categoryId = DB::table('categories')->insertGetId([
            'category' => $validated['custom_category'],
            'owner_id' => $ownerId,
        ]);
    } else {
        $categoryId = $validated['category_id'];
    }

    //Handle unit - Check for duplicates (with parenthesis awareness)
    if ($validated['unit_id'] === 'other' && !empty($validated['custom_unit'])) {
        $confirmedUnit = $request->input('confirmed_unit') === '1'; // 🆕 NEW
        
        if (!$confirmedUnit) { // 🆕 Only validate if not confirmed
            $existingUnits = DB::table('units')
                ->where('owner_id', $ownerId)
                ->get();
            
            $unitMatchResult = $this->findUnitMatch($validated['custom_unit'], $existingUnits);

            if ($unitMatchResult) {
                // 🔴 MODIFIED: Only block exact matches
                if ($unitMatchResult['isExact']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unit already exists: ' . $unitMatchResult['unit']
                    ], 422);
                }
                // For similar matches, don't block - frontend will handle confirmation
            }
        }

        // If no exact match found (or user confirmed), create the new unit
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


public function checkExistingName(Request $request)
{
    $ownerId = session('owner_id');
    $type = $request->type; // 'category', 'unit', or 'product'
    $name = $request->name;
    
    if (!$ownerId || !$type || !$name) {
        return response()->json(['exists' => false]);
    }
    
    // Normalize the input for semantic comparison
    $normalizedInput = $this->normalizeName($name);
    
    if ($type === 'category') {
        // ✅ STEP 1: Check for exact case-insensitive match FIRST
        $exactMatch = DB::table('categories')
            ->where('owner_id', $ownerId)
            ->whereRaw('LOWER(category) = ?', [strtolower($name)])
            ->first();
        
        if ($exactMatch) {
            return response()->json([
                'exists' => true,
                'existingName' => $exactMatch->category,
                'isExactMatch' => true
            ]);
        }
        
        // ✅ STEP 2: Check for semantic/similar matches
        $existingCategories = DB::table('categories')
            ->where('owner_id', $ownerId)
            ->get();
        
        $normalizedInput = $this->normalizeName($name);
        $semanticMatch = $this->findSemanticMatch($normalizedInput, $existingCategories, 'category');
        
        if ($semanticMatch) {
            // It's a semantic match but NOT an exact match (since we already checked above)
            return response()->json([
                'exists' => true,
                'existingName' => $semanticMatch,
                'isExactMatch' => false // ✅ Always false here since exact was already checked
            ]);
        }
        
        return response()->json(['exists' => false]);
    }
    
    else if ($type === 'unit') {
        // ✅ STEP 1: Check for exact match first
        $exactMatch = DB::table('units')
            ->where('owner_id', $ownerId)
            ->whereRaw('LOWER(unit) = ?', [strtolower($name)])
            ->first();
        
        if ($exactMatch) {
            return response()->json([
                'exists' => true,
                'existingName' => $exactMatch->unit,
                'isExactMatch' => true
            ]);
        }
        
        // ✅ STEP 2: Check for similar matches
        $existingUnits = DB::table('units')
            ->where('owner_id', $ownerId)
            ->get();
        
        $unitMatchResult = $this->findUnitMatch($name, $existingUnits);
        
        if ($unitMatchResult) {
            return response()->json([
                'exists' => true,
                'existingName' => $unitMatchResult['unit'],
                'isExactMatch' => $unitMatchResult['isExact'] // ✅ Use the flag from findUnitMatch
            ]);
        }
        
        return response()->json(['exists' => false]);
    }
    
    else if ($type === 'product') {
        // ✅ NEW: Check for existing product names
        $existingProducts = DB::table('products')
            ->where('owner_id', $ownerId)
            ->get();
        
        $productMatch = $this->findProductNameMatch($name, $existingProducts);
        
        if ($productMatch) {
            return response()->json([
                'exists' => true,
                'existingName' => $productMatch['name'],
                'isExactMatch' => $productMatch['isExact']
            ]);
        }
        
        return response()->json(['exists' => false]);
    }
    
    return response()->json(['exists' => false]);
}

// ✅ FIXED: Find product name matches with STRICT validation
private function findProductNameMatch($input, $existingProducts)
{
    $inputLower = strtolower(trim($input));
    $normalizedInput = $this->normalizeName($input);
    
    foreach ($existingProducts as $product) {
        $existingName = $product->name;
        $existingNameLower = strtolower($existingName);
        $normalizedExisting = $this->normalizeName($existingName);
        
        // ✅ EXACT MATCH ONLY (case-insensitive)
        if ($inputLower === $existingNameLower) {
            return ['name' => $existingName, 'isExact' => true];
        }
        
        // ✅ Exact normalized match
        if ($normalizedInput === $normalizedExisting) {
            return ['name' => $existingName, 'isExact' => true];
        }
    }
    
    // ✅ SECOND PASS: Check for similar products (non-blocking warnings)
    $bestMatch = null;
    $highestSimilarity = 0;
    
    foreach ($existingProducts as $product) {
        $existingName = $product->name;
        $existingNameLower = strtolower($existingName);
        $normalizedExisting = $this->normalizeName($existingName);
        
        // ✅ Check similarity only if products share meaningful base name
        $similarity = $this->calculateProductSimilarity($normalizedInput, $normalizedExisting, $inputLower, $existingNameLower);
        
        if ($similarity > $highestSimilarity && $similarity >= 0.75) {
            $highestSimilarity = $similarity;
            $bestMatch = ['name' => $existingName, 'isExact' => false, 'similarity' => $similarity];
        }
    }
    
    return $bestMatch;
}

// ✅ NEW: Calculate product similarity with strict criteria
private function calculateProductSimilarity($normalizedInput, $normalizedExisting, $inputLower, $existingLower)
{
    // Skip if strings are too short
    if (strlen($normalizedInput) < 3 || strlen($normalizedExisting) < 3) {
        return 0;
    }
    
    $inputWords = array_filter(explode(' ', $normalizedInput), function($word) {
        return strlen($word) >= 3; // Only consider words with 3+ characters
    });
    
    $existingWords = array_filter(explode(' ', $normalizedExisting), function($word) {
        return strlen($word) >= 3;
    });
    
    if (empty($inputWords) || empty($existingWords)) {
        return 0;
    }
    
    // ✅ Extract numbers (sizes/quantities) from both strings
    preg_match_all('/\d+\s*(?:ml|l|g|kg|oz|lb|pc|pcs|pieces?)?/', $inputLower, $inputNumbers);
    preg_match_all('/\d+\s*(?:ml|l|g|kg|oz|lb|pc|pcs|pieces?)?/', $existingLower, $existingNumbers);
    
    $inputHasNumbers = !empty($inputNumbers[0]);
    $existingHasNumbers = !empty($existingNumbers[0]);
    
    // ✅ If both have numbers but they're different, they're different products
    if ($inputHasNumbers && $existingHasNumbers) {
        $inputNumStr = implode('', $inputNumbers[0]);
        $existingNumStr = implode('', $existingNumbers[0]);
        
        if ($inputNumStr !== $existingNumStr) {
            // Different sizes = different products, but might still be similar
            // Only suggest if the base name is VERY similar
            $baseInputWords = array_values($inputWords);
            $baseExistingWords = array_values($existingWords);
            
            $commonWords = array_intersect($baseInputWords, $baseExistingWords);
            $matchRatio = count($commonWords) / max(count($baseInputWords), count($baseExistingWords));
            
            // Only suggest if 70%+ of non-numeric words match
            return $matchRatio >= 0.7 ? $matchRatio : 0;
        }
    }
    
    // ✅ Count exact word matches (case-insensitive)
    $exactMatches = 0;
    foreach ($inputWords as $inputWord) {
        foreach ($existingWords as $existingWord) {
            if ($inputWord === $existingWord) {
                $exactMatches++;
                break;
            }
        }
    }
    
    // ✅ Calculate match ratio
    $totalUniqueWords = count(array_unique(array_merge($inputWords, $existingWords)));
    $matchRatio = $exactMatches / $totalUniqueWords;
    
    // ✅ Check for typo similarity only if there's some word overlap
    if ($exactMatches > 0) {
        $typoMatches = 0;
        foreach ($inputWords as $inputWord) {
            if (strlen($inputWord) < 4) continue; // Skip short words for typo check
            
            foreach ($existingWords as $existingWord) {
                if (strlen($existingWord) < 4) continue;
                
                if ($inputWord === $existingWord) continue; // Already counted
                
                $distance = levenshtein($inputWord, $existingWord);
                $maxLength = max(strlen($inputWord), strlen($existingWord));
                $wordSimilarity = 1 - ($distance / $maxLength);
                
                // 85% similarity for typos (stricter than before)
                if ($wordSimilarity >= 0.85) {
                    $typoMatches++;
                    break;
                }
            }
        }
        
        $totalMatches = $exactMatches + $typoMatches;
        $matchRatio = $totalMatches / $totalUniqueWords;
    }
    
    // ✅ Check string containment (one is substring of other)
    if (strpos($normalizedExisting, $normalizedInput) !== false || 
        strpos($normalizedInput, $normalizedExisting) !== false) {
        $matchRatio = max($matchRatio, 0.8);
    }
    
    return $matchRatio;
}

//Normalize name for semantic comparison
private function normalizeName($name)
{
    $name = strtolower(trim($name));
    
    // Replace common variations
    $replacements = [
        ' and ' => ' & ',
        ' + ' => ' & ',
        ' with ' => ' & ',
        ' plus ' => ' & ',
        // Remove common filler words
        'the ' => '',
        ' of ' => ' ',
        ' in ' => ' ',
    ];
    
    $name = str_replace(array_keys($replacements), array_values($replacements), $name);
    
    // Remove extra spaces and special characters
    $name = preg_replace('/[^\w&]/', ' ', $name);
    $name = preg_replace('/\s+/', ' ', $name);
    
    return trim($name);
}



//Find semantic matches in existing categories (FIXED VERSION)
private function findSemanticMatch($normalizedInput, $existingItems, $column)
{
    $inputWords = array_filter(explode(' ', $normalizedInput));
    
    // Skip if input is empty
    if (empty($inputWords)) {
        return null;
    }
    
    foreach ($existingItems as $item) {
        $existingName = $item->{$column};
        $normalizedExisting = $this->normalizeName($existingName);
        
        // ✅ Check for EXACT normalized match ONLY
        if ($normalizedInput === $normalizedExisting) {
            return $existingName;
        }
        
        // ❌ REMOVED: Substring checks that were too aggressive
        // These were causing "beverages hot" to match "beverages"
        
        $existingWords = array_filter(explode(' ', $normalizedExisting));
        
        // ✅ Only flag as semantic match if:
        // 1. Input and existing have SAME NUMBER of words, OR
        // 2. ALL input words match AND input represents significant portion
        
        // Skip multi-word input vs single-word existing (like "beverages hot" vs "beverages")
        if (count($inputWords) > count($existingWords)) {
            continue; // Input has more words, so it's likely a more specific category
        }
        
        // ✅ Check if ALL input words have matches in existing category
        $allInputWordsMatched = true;
        $matchedCount = 0;
        
        foreach ($inputWords as $inputWord) {
            if (strlen($inputWord) < 2) continue;
            
            $foundMatch = false;
            
            // First check for exact word match
            foreach ($existingWords as $existingWord) {
                if ($inputWord === $existingWord) {
                    $foundMatch = true;
                    $matchedCount++;
                    break;
                }
            }
            
            // If no exact match, check for typo similarity
            if (!$foundMatch) {
                foreach ($existingWords as $existingWord) {
                    if (strlen($existingWord) < 3) continue;
                    
                    $distance = levenshtein($inputWord, $existingWord);
                    $maxLength = max(strlen($inputWord), strlen($existingWord));
                    $similarity = 1 - ($distance / $maxLength);
                    
                    if ($similarity >= 0.80 && strlen($inputWord) >= 4 && strlen($existingWord) >= 4) {
                        $foundMatch = true;
                        $matchedCount++;
                        break;
                    }
                    
                    // Check if one word contains the other (singular/plural)
                    if (strlen($inputWord) >= 4 && strlen($existingWord) >= 4) {
                        if (strpos($existingWord, $inputWord) !== false || 
                            strpos($inputWord, $existingWord) !== false) {
                            $foundMatch = true;
                            $matchedCount++;
                            break;
                        }
                    }
                }
            }
            
            if (!$foundMatch) {
                $allInputWordsMatched = false;
                break;
            }
        }
        
        // ✅ MODIFIED: Only return match if word counts are equal OR single-word exact match
        if ($allInputWordsMatched && $matchedCount > 0) {
            // Case 1: Both have same number of words (e.g., "hot beverages" vs "cold beverages")
            if (count($inputWords) === count($existingWords)) {
                return $existingName;
            }
            
            // Case 2: Single word that matches exactly
            if (count($inputWords) === 1 && count($existingWords) === 1) {
                return $existingName;
            }
            
            // ❌ REMOVED: The logic that returned matches when input was subset
            // This was causing "beverages" to match "beverages hot"
        }
    }
    
    return null;
}


//Find unit matches considering parenthesis notation AND similarity
private function findUnitMatch($input, $existingUnits)
{
    $inputLower = strtolower(trim($input));
    $bestMatch = null;
    $isExactMatch = false;
    
    foreach ($existingUnits as $unit) {
        $existingUnit = $unit->unit;
        $existingUnitLower = strtolower($existingUnit);
        
        // Exact match (case-insensitive)
        if ($inputLower === $existingUnitLower) {
            return ['unit' => $existingUnit, 'isExact' => true];
        }
        
        // Extract the main name and abbreviation from format "Name (abbr)"
        if (preg_match('/^(.+?)\s*\((.+?)\)$/', $existingUnit, $matches)) {
            $unitName = strtolower(trim($matches[1])); // e.g., "bottle"
            $unitAbbr = strtolower(trim($matches[2])); // e.g., "btl"
            
            // Check if input matches the name part exactly
            if ($inputLower === $unitName) {
                return ['unit' => $existingUnit, 'isExact' => true];
            }
            
            // Check if input matches the abbreviation part exactly
            if ($inputLower === $unitAbbr) {
                return ['unit' => $existingUnit, 'isExact' => true];
            }
            
            //Check for similarity with the name part (e.g., "battle" vs "bottle")
            if (!$bestMatch && $this->isSimilarString($inputLower, $unitName)) {
                $bestMatch = ['unit' => $existingUnit, 'isExact' => false];
            }
            
            //Check for similarity with the abbreviation (e.g., "bttle" vs "btl")
            if (!$bestMatch && $this->isSimilarString($inputLower, $unitAbbr)) {
                $bestMatch = ['unit' => $existingUnit, 'isExact' => false];
            }
            
            // Check if input is trying to create "Name (abbr)" that already exists
            if (preg_match('/^(.+?)\s*\((.+?)\)$/', $input, $inputMatches)) {
                $inputName = strtolower(trim($inputMatches[1]));
                $inputAbbr = strtolower(trim($inputMatches[2]));
                
                // Same name or same abbreviation
                if ($inputName === $unitName || $inputAbbr === $unitAbbr) {
                    return ['unit' => $existingUnit, 'isExact' => true];
                }
                
                //Check for similarity in formatted units
                if (!$bestMatch && ($this->isSimilarString($inputName, $unitName) || $this->isSimilarString($inputAbbr, $unitAbbr))) {
                    $bestMatch = ['unit' => $existingUnit, 'isExact' => false];
                }
            }
        } else {
            // Existing unit doesn't have parenthesis format
            // Check if user is trying to add parenthesis version
            if (preg_match('/^(.+?)\s*\((.+?)\)$/', $input, $inputMatches)) {
                $inputName = strtolower(trim($inputMatches[1]));
                
                if ($inputName === $existingUnitLower) {
                    return ['unit' => $existingUnit, 'isExact' => true];
                }
                
                //Check for similarity
                if (!$bestMatch && $this->isSimilarString($inputName, $existingUnitLower)) {
                    $bestMatch = ['unit' => $existingUnit, 'isExact' => false];
                }
            } else {
                //Simple unit vs simple unit similarity check
                if (!$bestMatch && $this->isSimilarString($inputLower, $existingUnitLower)) {
                    $bestMatch = ['unit' => $existingUnit, 'isExact' => false];
                }
            }
        }
    }
    
    return $bestMatch;
}

//Helper function to check string similarity (for typos)
private function isSimilarString($str1, $str2)
{
    // Skip very short strings
    if (strlen($str1) < 3 || strlen($str2) < 3) {
        return false;
    }
    
    // Calculate Levenshtein distance
    $distance = levenshtein($str1, $str2);
    $maxLength = max(strlen($str1), strlen($str2));
    $similarity = 1 - ($distance / $maxLength);
    
    // If strings are 70% similar or more, consider them similar
    return $similarity >= 0.70;
}


public function addCategory(Request $request)
{
    $ownerId = session('owner_id');
    $categoryName = trim($request->input('category'));
    $confirmedSimilar = $request->input('confirmed_similar') === '1'; // ✅ NEW

    if (empty($categoryName)) {
        return response()->json(['success' => false, 'message' => 'Category name cannot be empty.']);
    }

    // Get all existing categories for semantic comparison
    $existingCategories = DB::table('categories')
        ->where('owner_id', $ownerId)
        ->get();
    
    // Check 1: Exact case-insensitive match (ALWAYS BLOCK)
    $exactMatch = DB::table('categories')
        ->where('owner_id', $ownerId)
        ->whereRaw('LOWER(category) = ?', [strtolower($categoryName)])
        ->first();
    
    if ($exactMatch) {
        return response()->json([
            'success' => false, 
            'message' => 'Category already exists: ' . $exactMatch->category,
            'isExactMatch' => true,
            'existingName' => $exactMatch->category
        ]);
    }
    
    // Check 2: Semantic similarity (ALLOW if user confirmed)
    $normalizedInput = $this->normalizeName($categoryName);
    $semanticMatch = $this->findSemanticMatch($normalizedInput, $existingCategories, 'category');

    if ($semanticMatch && !$confirmedSimilar) { // ✅ MODIFIED: Only block if not confirmed
        return response()->json([
            'success' => false, 
            'message' => 'Similar category already exists: ' . $semanticMatch,
            'isExactMatch' => false,
            'existingName' => $semanticMatch
        ]);
    }

    // Both checks passed OR user confirmed similar match - insert new category
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



public function getLatestBatch($prod_code)
{
    $ownerId = session('owner_id');
    
    // MODIFIED: Get latest batch for specific product
    $latestBatch = DB::table('inventory')
        ->where('prod_code', $prod_code)
        ->where('owner_id', $ownerId)
        ->orderBy('inven_code', 'desc')
        ->value('batch_number');
    
    // MODIFIED: Parse new format P{prodCode}-BATCH-{number}
    if ($latestBatch && preg_match('/P\d+-BATCH-(\d+)/', $latestBatch, $matches)) {
        $nextNumber = ((int)$matches[1]) + 1;
    } else {
        $nextNumber = 1; // First batch for this product
    }
    
    // Return next batch in new format
    $nextBatch = "P{$prod_code}-BATCH-{$nextNumber}";
    
    return response()->json(['next_batch' => $nextBatch]);
}


public function bulkRestock(Request $request)
{
    $ownerId = session('owner_id');
    $items = $request->input('items', []);
    
    if (empty($items)) {
        return response()->json(['success' => false, 'message' => 'No products provided for restocking.']);
    }
    
    // ✅ Validate expiration dates (must be at least 7 days from today)
    $today = now()->startOfDay();
    $minDate = now()->addDays(7)->startOfDay(); // 7 days from today
    $invalidDates = [];
    
    foreach ($items as $index => $it) {
        $expiration = $it['expiration_date'] ?? null;
        
        if ($expiration) {
            $expirationDate = \Carbon\Carbon::parse($expiration)->startOfDay();
            
            // Calculate days difference
            $daysDiff = $today->diffInDays($expirationDate, false); // false = can be negative
            
            // Check if expiration date is less than 7 days from today
            if ($expirationDate->lt($minDate)) {
                $prodCode = $it['prod_code'] ?? 'Unknown';
                
                // Get product name for better error message
                $product = DB::table('products')
                    ->where('prod_code', $prodCode)
                    ->where('owner_id', $ownerId)
                    ->first();
                
                $productName = $product ? $product->name : "Product #{$prodCode}";
                
                if ($daysDiff < 0) {
                    $invalidDates[] = "{$productName} (Expiration: {$expiration} - date is in the past)";
                } else {
                    $invalidDates[] = "{$productName} (Expiration: {$expiration} - only {$daysDiff} day(s) away, needs 7 days minimum)";
                }
            }
        }
    }
    
    // ✅ If any invalid dates found, return error
    if (!empty($invalidDates)) {
        $message = 'Cannot restock: All products must have expiration dates at least 7 days from today.<br><br>' . implode('<br>', $invalidDates);
        return response()->json([
            'success' => false, 
            'message' => $message,
            'invalidDates' => $invalidDates
        ], 422);
    }
    
    DB::beginTransaction();
    try {
        foreach ($items as $it) {
            $prodCode = $it['prod_code'] ?? null;
            $qty = (int) ($it['qty'] ?? 0);
            $expiration = $it['expiration_date'] ?? null;
            $categoryId = $it['category_id'] ?? null;
            
            if (!$prodCode || $qty <= 0) continue;
            
            // ✅ MODIFIED: Get latest batch for THIS specific product using new format
            $latestBatch = DB::table('inventory')
                ->where('prod_code', $prodCode)
                ->where('owner_id', $ownerId)
                ->orderBy('inven_code', 'desc')
                ->value('batch_number');
            
            // ✅ MODIFIED: Parse new format P{prodCode}-BATCH-{number}
            if ($latestBatch && preg_match('/P\d+-BATCH-(\d+)/', $latestBatch, $m)) {
                $nextNumber = ((int)$m[1]) + 1;
            } else {
                $nextNumber = 1; // First batch for this product
            }
            
            // ✅ MODIFIED: Generate batch number in format P{prodCode}-BATCH-{number}
            $nextBatchNumber = "P{$prodCode}-BATCH-{$nextNumber}";
            
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

    // public function store(Request $request)
    // {
    //     $ownerId = Auth::guard('owner')->id();

    //     try {
    //         $validated = $request->validate([
    //             'prod_code' => 'required|exists:products,prod_code',
    //             'damaged_quantity' => 'required|integer|min:1',
    //             'damaged_type' => 'required|string|max:20',
    //             'damaged_reason' => 'required|string|max:255',
    //         ]);

    //         $totalStock = DB::table('inventory')
    //             ->where('prod_code', $validated['prod_code'])
    //             ->where('owner_id', $ownerId)
    //             ->sum('stock');

    //         if ($totalStock === 0) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Product stock is zero. Cannot record damage.'
    //             ]);
    //         }

    //         if ($validated['damaged_quantity'] > $totalStock) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Damaged quantity exceeds available stock.'
    //             ]);
    //         }

    //         DB::table('damaged_items')->insert([
    //             'prod_code' => $validated['prod_code'],
    //             'damaged_quantity' => $validated['damaged_quantity'],
    //             'damaged_type' => $validated['damaged_type'],
    //             'damaged_reason' => $validated['damaged_reason'],
    //             'owner_id' => $ownerId,
    //             'damaged_date' => now(),
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Damaged item recorded successfully!'
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->validator->errors()->first()
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An unexpected error occurred.'
    //         ]);
    //     }
    // }
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

            // Fetch all inventory records for the selected product
            $inventoryRecords = DB::table('inventory')
                ->where('prod_code', $validated['prod_code'])
                ->where('owner_id', $ownerId)
                ->get();

            // Filter out inventory records with zero stock and sort by the first added inventory (oldest)
            $availableInventory = $inventoryRecords->filter(function($inventory) {
                return $inventory->stock > 0;  // Only keep inventory records with stock > 0
            })->sortBy('date_added')->first();  // Sort by first added (ascending order)

            // If no inventory with stock > 0 is found
            if (!$availableInventory) {
                return response()->json([
                    'success' => false,
                    'message' => 'No stock to record as damaged.'
                ]);
            }

            $totalStock = $availableInventory->stock;

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

            // Insert the damaged item record, including inven_code from the first added inventory
            DB::table('damaged_items')->insert([
                'prod_code' => $validated['prod_code'],
                'damaged_quantity' => $validated['damaged_quantity'],
                'damaged_type' => $validated['damaged_type'],
                'damaged_reason' => $validated['damaged_reason'],
                'owner_id' => $ownerId,
                'damaged_date' => now(),
                'inven_code' => $availableInventory->inven_code, // Store the inven_code from the first added inventory
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



    // public function showDamageItemsForm()
    // {
    //     $ownerId = Auth::guard('owner')->id();

    //     // Fetch all products for the product dropdown
    //     $products = DB::table('products')
    //         ->where('owner_id', $ownerId)
    //         ->get();

    //     // Fetch all damaged items recorded by the logged-in owner
    //     $damagedItems = DB::table('damaged_items')
    //         ->join('products', 'damaged_items.prod_code', '=', 'products.prod_code')
    //         ->join('inventory', 'damaged_items.inven_code', '=', 'inventory.inven_code') // Join with inventory to get batch_number
    //         ->where('damaged_items.owner_id', $ownerId)
    //         ->select('damaged_items.*', 'products.name as product_name', 'inventory.batch_number') // Include batch_number from inventory
    //         ->orderBy('damaged_items.damaged_date', 'desc') // Show latest records first
    //         ->get();

    //     // Return the view with both products and damaged items data
    //     return view('damage-items', compact('damagedItems', 'products'));
    // }

    // public function showDamageItemsForm()
    // {
    //     $ownerId = Auth::guard('owner')->id();

    //     $expiredInventories = DB::table('inventory')
    //         ->where('owner_id', $ownerId)
    //         ->where('stock', '>', 0)
    //         ->where(function ($query) {
    //             $query->whereDate('expiration_date', '<=', now())  
    //                 ->orWhere('is_expired', 1);
    //         })
    //         ->get();

    //     foreach ($expiredInventories as $expired) {

    //         $alreadyRecorded = DB::table('damaged_items')
    //             ->where('inven_code', $expired->inven_code)
    //             ->where('damaged_type', 'Expired')
    //             ->exists();

    //         if (!$alreadyRecorded) {
    //             DB::table('damaged_items')->insert([
    //                 'prod_code' => $expired->prod_code,
    //                 'damaged_quantity' => $expired->stock,
    //                 'damaged_type' => 'Expired',
    //                 'damaged_reason' => 'Product has reached its expiration date.',
    //                 'owner_id' => $ownerId,
    //                 'damaged_date' => now(),
    //                 'inven_code' => $expired->inven_code
                   
    //             ]);

    //             DB::table('inventory')
    //                 ->where('inven_code', $expired->inven_code)
    //                 ->update(['is_expired' => 1, 'stock' => 0]);
    //         }
    //     }

    //     $products = DB::table('products')
    //         ->where('owner_id', $ownerId)
    //         ->get();

    //     $damagedItems = DB::table('damaged_items')
    //         ->join('products', 'damaged_items.prod_code', '=', 'products.prod_code')
    //         ->join('inventory', 'damaged_items.inven_code', '=', 'inventory.inven_code')
    //         ->where('damaged_items.owner_id', $ownerId)
    //         ->select('damaged_items.*', 'products.name as product_name', 'inventory.batch_number')
    //         ->orderBy('damaged_items.damaged_date', 'desc')
    //         ->get();

    //     return view('damage-items', compact('damagedItems', 'products'));
    // }
}

    




