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
                MIN(p.stock_limit)   AS stock_limit,
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

    // Get ORIGINAL stock-in quantities per batch (before any deductions)
    // We need to get the initial stock value when the batch was first added
    $originalStockPerBatch = DB::table('inventory')
        ->where('prod_code', $prodCode)
        ->whereNotNull('batch_number')
        ->select(
            'batch_number',
            DB::raw('SUM(stock) as current_stock')
        )
        ->groupBy('batch_number')
        ->get()
        ->keyBy('batch_number');

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
        ->join('products as p', 'i.prod_code', '=', 'p.prod_code')
        ->where('p.prod_code', $prodCode)
        ->whereNotNull('i.batch_number')
        ->select('i.batch_number', DB::raw('SUM(di.damaged_quantity) as total_damaged'))
        ->groupBy('i.batch_number')
        ->pluck('total_damaged', 'batch_number');

    // Batch grouping for stock-in with CORRECT original quantities
    $batchGroups = $stockInHistory->groupBy('batch_number')->map(function($batches, $batchNumber) use ($salesPerBatch, $damagedPerBatch, $originalStockPerBatch) {
        // Get current stock in inventory (already reduced)
        $currentStock = $batches->sum('stock');
        
        // Get total sold from this batch
        $soldFromBatch = $salesPerBatch->get($batchNumber, 0);
        
        // Get total damaged from this batch
        $damagedFromBatch = $damagedPerBatch->get($batchNumber, 0);
        
        // Calculate ORIGINAL quantity: current stock + all items that went out
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
        ->join('inventory as i', 'ri.inven_code', '=', 'i.inven_code')
        ->leftJoin('staff as s', 'r.staff_id', '=', 's.staff_id')
        ->leftJoin('owners as o', 'r.owner_id', '=', 'o.owner_id')
        ->select(
            'ri.item_id',
            'ri.item_quantity as quantity_sold',
            'ri.prod_code',
            'r.receipt_id',
            'r.receipt_date',
            'i.batch_number',

            // Selling price used for this sale
            DB::raw('(
                SELECT COALESCE(
                    (SELECT ph.old_selling_price 
                    FROM pricing_history ph
                    WHERE ph.prod_code = ri.prod_code
                    AND r.receipt_date >= ph.effective_from
                    AND (r.receipt_date <= ph.effective_to OR ph.effective_to IS NULL)
                    ORDER BY ph.effective_from DESC
                    LIMIT 1),
                    p.selling_price
                )
            ) as selling_price_used'),

            // Old cost price at the time of sale
            DB::raw('(
                SELECT COALESCE(
                    (SELECT ph.old_cost_price 
                    FROM pricing_history ph
                    WHERE ph.prod_code = ri.prod_code
                    AND r.receipt_date >= ph.effective_from
                    AND (r.receipt_date <= ph.effective_to OR ph.effective_to IS NULL)
                    ORDER BY ph.effective_from DESC
                    LIMIT 1),
                    p.cost_price
                )
            ) as cost_price_used'),

            // Total amount
            DB::raw('ri.item_quantity * (
                SELECT COALESCE(
                    (SELECT ph.old_selling_price 
                    FROM pricing_history ph
                    WHERE ph.prod_code = ri.prod_code
                    AND r.receipt_date >= ph.effective_from
                    AND (r.receipt_date <= ph.effective_to OR ph.effective_to IS NULL)
                    ORDER BY ph.effective_from DESC
                    LIMIT 1),
                    p.selling_price
                )
            ) as total_amount'),

            // Who sold it
            DB::raw('COALESCE(CONCAT(s.firstname, " ", s.lastname), CONCAT(o.firstname, " ", o.lastname), "System") as sold_by')
        )
        ->where('ri.prod_code', $prodCode)
        ->orderBy('r.receipt_date', 'desc')
        ->get();


    // Stock-out from Damaged/Expired Items
    $stockOutDamagedHistory = DB::table('damaged_items as di')
        ->join('inventory as i', 'di.inven_code', '=', 'i.inven_code')
        ->leftJoin('staff as s', 'di.staff_id', '=', 's.staff_id')
        ->leftJoin('owners as o', 'di.owner_id', '=', 'o.owner_id')
        ->select(
            'di.damaged_id',
            'di.damaged_quantity',
            'di.damaged_date',
            'di.damaged_type',
            'di.damaged_reason',
            'i.batch_number',
            'i.prod_code',
            DB::raw('COALESCE(CONCAT(s.firstname, " ", s.lastname), CONCAT(o.firstname, " ", o.lastname), "System") as reported_by')
        )
        ->where('i.prod_code', $prodCode)
        ->orderBy('di.damaged_date', 'desc')
        ->get();

    // Calculate totals correctly
    $currentStockInInventory = $stockInHistory->sum('stock');
    $totalStockOutSold = $stockOutSalesHistory->sum('quantity_sold');
    $totalStockOutDamaged = $stockOutDamagedHistory->sum('damaged_quantity');
    $totalStockOut = $totalStockOutSold + $totalStockOutDamaged;
    
    // Total Stock In = Current Stock + All Stock Out (sold + damaged)
    $totalStockIn = $currentStockInInventory + $totalStockOut;
    
    // Current Stock = What's remaining in inventory
    $currentStock = $currentStockInInventory;
    
    $totalRevenue = $stockOutSalesHistory->sum('total_amount');
    $turnoverRate = $totalStockIn > 0 ? ($totalStockOutSold / $totalStockIn) * 100 : 0;

    // Count expired and damaged items using damaged_type
    $totalExpired = $stockOutDamagedHistory->where('damaged_type', 'Expired')->sum('damaged_quantity');
    $totalDamaged = $stockOutDamagedHistory->where('damaged_type', '!=', 'Expired')->sum('damaged_quantity');

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

    // Get product name
    $product = DB::selectOne("
        SELECT name 
        FROM products 
        WHERE prod_code = ? AND owner_id = ?
    ", [$prodCode, $ownerId]);

    // Get all price periods (historical and current)
    $allPricePeriods = DB::select("
        SELECT 
            ph.price_history_id,
            ph.old_cost_price as cost_price,
            ph.old_selling_price as selling_price,
            ph.effective_from,
            ph.effective_to,
            'historical' as period_type
        FROM pricing_history ph
        WHERE ph.prod_code = ? 
        AND ph.owner_id = ? 
        AND ph.effective_to IS NOT NULL
        
        UNION
        
        SELECT 
            NULL as price_history_id,
            p.cost_price,
            p.selling_price,
            COALESCE(
                (SELECT MAX(effective_to) FROM pricing_history WHERE prod_code = ? AND owner_id = ?),
                (SELECT MIN(date_added) FROM inventory WHERE prod_code = ? AND owner_id = ?)
            ) as effective_from,
            NULL as effective_to,
            'current' as period_type
        FROM products p
        WHERE p.prod_code = ? 
        AND p.owner_id = ?
        
        ORDER BY effective_from DESC
    ", [$prodCode, $ownerId, $prodCode, $ownerId, $prodCode, $ownerId, $prodCode, $ownerId]);

    // Get all batches with their date_added
    $allBatches = DB::select("
        SELECT 
            i.inven_code,
            i.batch_number,
            i.date_added,
            i.stock as current_stock
        FROM inventory i
        WHERE i.prod_code = ? 
        AND i.owner_id = ?
        AND (i.is_expired = 0 OR i.is_expired IS NULL)
        ORDER BY i.batch_number
    ", [$prodCode, $ownerId]);

    $priceHistory = [];
    $currentPrice = [];

    // First pass: Calculate when each batch actually sold out
    $batchSoldOutInfo = [];
    
    // Process periods in chronological order
    $periodsChronological = array_reverse($allPricePeriods);
    $totalPeriods = count($periodsChronological);
    
    // For each batch, track its stock through all periods to find when it sold out
    foreach ($allBatches as $batch) {
        $runningStock = null;
        
        for ($i = $totalPeriods - 1; $i >= 0; $i--) {
            $period = $periodsChronological[$i];
            $periodEnd = $period->effective_to ?? now();
            
            if ($batch->date_added > $periodEnd) {
                continue;
            }
            
            if ($runningStock === null) {
                $runningStock = $batch->current_stock;
            }
            
            if ($i < $totalPeriods - 1) {
                $nextPeriod = $periodsChronological[$i + 1];
                
                $salesAfter = DB::selectOne("
                    SELECT IFNULL(SUM(ri.item_quantity), 0) as sold_after
                    FROM receipt_item ri
                    INNER JOIN receipt r ON r.receipt_id = ri.receipt_id
                    WHERE ri.inven_code = ?
                    AND r.receipt_date > ?
                    AND r.receipt_date <= ?
                ", [$batch->inven_code, $periodEnd, $nextPeriod->effective_from]);
                
                $damageAfter = DB::selectOne("
                    SELECT IFNULL(SUM(damaged_quantity), 0) as damaged_after
                    FROM damaged_items
                    WHERE inven_code = ?
                    AND damaged_date > ?
                    AND damaged_date <= ?
                ", [$batch->inven_code, $periodEnd, $nextPeriod->effective_from]);
                
                $runningStock += ($salesAfter->sold_after ?? 0) + ($damageAfter->damaged_after ?? 0);
            }
            
            if ($runningStock <= 0 && !isset($batchSoldOutInfo[$batch->inven_code])) {
                $batchSoldOutInfo[$batch->inven_code] = [
                    'period_index' => $i,
                    'period_id' => $period->period_type === 'current' ? 'current' : $period->price_history_id
                ];
            }
            
            $salesDuring = DB::selectOne("
                SELECT IFNULL(SUM(ri.item_quantity), 0) as sold_during
                FROM receipt_item ri
                INNER JOIN receipt r ON r.receipt_id = ri.receipt_id
                WHERE ri.inven_code = ?
                AND r.receipt_date >= ?
                AND r.receipt_date <= ?
            ", [$batch->inven_code, $period->effective_from, $periodEnd]);
            
            $damageDuring = DB::selectOne("
                SELECT IFNULL(SUM(damaged_quantity), 0) as damaged_during
                FROM damaged_items
                WHERE inven_code = ?
                AND damaged_date >= ?
                AND damaged_date <= ?
            ", [$batch->inven_code, $period->effective_from, $periodEnd]);
            
            $runningStock += ($salesDuring->sold_during ?? 0) + ($damageDuring->damaged_during ?? 0);
        }
    }

    // Second pass: Build display data with correct sold out status
    foreach ($periodsChronological as $periodIndex => $period) {
        $periodStart = $period->effective_from;
        $periodEnd = $period->effective_to ?? now();
        $isCurrent = $period->period_type === 'current';
        $currentPeriodId = $isCurrent ? 'current' : $period->price_history_id;

        foreach ($allBatches as $batch) {
            if ($batch->date_added > $periodEnd) {
                continue;
            }
            
            // Check if batch sold out in a PREVIOUS period
            if (isset($batchSoldOutInfo[$batch->inven_code])) {
                $soldOutPeriodIndex = $batchSoldOutInfo[$batch->inven_code]['period_index'];
                
                if ($soldOutPeriodIndex < $periodIndex) {
                    continue;
                }
            }
            
            // Calculate stock at START of period
            $batchAddedBeforePeriod = $batch->date_added < $periodStart;
            
            if ($batchAddedBeforePeriod) {
                $salesAfterPeriodStart = DB::selectOne("
                    SELECT IFNULL(SUM(ri.item_quantity), 0) as sold_after
                    FROM receipt_item ri
                    INNER JOIN receipt r ON r.receipt_id = ri.receipt_id
                    WHERE ri.inven_code = ?
                    AND r.receipt_date >= ?
                ", [$batch->inven_code, $periodStart]);
                
                $damageAfterPeriodStart = DB::selectOne("
                    SELECT IFNULL(SUM(damaged_quantity), 0) as damaged_after
                    FROM damaged_items
                    WHERE inven_code = ?
                    AND damaged_date >= ?
                ", [$batch->inven_code, $periodStart]);
                
                $stockAtPeriodStart = $batch->current_stock + 
                                     ($salesAfterPeriodStart->sold_after ?? 0) + 
                                     ($damageAfterPeriodStart->damaged_after ?? 0);
                
                if ($stockAtPeriodStart <= 0) {
                    continue;
                }
            }
            
            // Calculate sales for this batch during this price period
            $batchData = DB::selectOne("
                SELECT 
                    IFNULL(SUM(ri.item_quantity), 0) AS batch_sold,
                    IFNULL(SUM(ri.item_quantity * ?), 0) AS batch_sales,
                    IFNULL((
                        SELECT SUM(di_sub.damaged_quantity)
                        FROM damaged_items di_sub
                        WHERE di_sub.inven_code = ?
                        AND di_sub.damaged_date BETWEEN ? AND ?
                    ), 0) AS batch_damaged
                FROM receipt_item ri
                INNER JOIN receipt r ON r.receipt_id = ri.receipt_id
                WHERE ri.inven_code = ?
                AND r.receipt_date BETWEEN ? AND ?
            ", [
                $period->selling_price,
                $batch->inven_code,
                $periodStart,
                $periodEnd,
                $batch->inven_code,
                $periodStart,
                $periodEnd
            ]);

            // Calculate available stock at END of period
            if ($isCurrent) {
                $batch_available = $batch->current_stock;
            } else {
                $salesAfterPeriod = DB::selectOne("
                    SELECT IFNULL(SUM(ri.item_quantity), 0) as sold_after
                    FROM receipt_item ri
                    INNER JOIN receipt r ON r.receipt_id = ri.receipt_id
                    WHERE ri.inven_code = ?
                    AND r.receipt_date > ?
                ", [$batch->inven_code, $periodEnd]);
                
                $damageAfterPeriod = DB::selectOne("
                    SELECT IFNULL(SUM(damaged_quantity), 0) as damaged_after
                    FROM damaged_items
                    WHERE inven_code = ?
                    AND damaged_date > ?
                ", [$batch->inven_code, $periodEnd]);
                
                $batch_available = $batch->current_stock + 
                                 ($salesAfterPeriod->sold_after ?? 0) + 
                                 ($damageAfterPeriod->damaged_after ?? 0);
            }

            // NEW LOGIC: Determine sold out status
            // A batch should show as sold out if it sold out in THIS period OR any LATER period
            $is_sold_out = false;
            $sold_out_in_this_period = false;

            if (isset($batchSoldOutInfo[$batch->inven_code])) {
                $soldOutPeriodIndex = $batchSoldOutInfo[$batch->inven_code]['period_index'];
                
                // Mark as sold out if it sold out in this period OR later
                if ($soldOutPeriodIndex >= $periodIndex) {
                    $is_sold_out = true;
                    // Only mark as "sold out in this period" if it happened exactly in this period
                    $sold_out_in_this_period = ($soldOutPeriodIndex === $periodIndex);
                }
            }

            $rowData = (object)[
                'price_history_id' => $period->price_history_id,
                'prod_code' => $prodCode,
                'cost_price' => $period->cost_price,
                'selling_price' => $period->selling_price,
                'old_cost_price' => $period->cost_price,
                'old_selling_price' => $period->selling_price,
                'effective_from' => $periodStart,
                'effective_to' => $periodEnd,
                'batch_number' => $batch->batch_number,
                'inven_code' => $batch->inven_code,
                'batch_available' => max(0, $batch_available),
                'batch_sold' => $batchData->batch_sold ?? 0,
                'batch_sales' => $batchData->batch_sales ?? 0,
                'batch_damaged' => $batchData->batch_damaged ?? 0,
                'is_sold_out' => $is_sold_out ? 1 : 0, // NEW: Shows red if sold out now or later
                'is_sold_out_in_period' => $sold_out_in_this_period ? 1 : 0, // Shows "sold out" label only in the actual period
                'owner_id' => $ownerId
            ];

            if ($isCurrent) {
                $currentPrice[] = $rowData;
            } else {
                $priceHistory[] = $rowData;
            }
        }
    }

    $productName = $product ? $product->name : 'Unknown Product';

    return view('inventory-owner-pricing-history', compact('priceHistory', 'currentPrice', 'prodCode', 'productName'));
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

/**
 * Check if product name already exists (real-time validation for edit)
 */
public function checkProductName(Request $request)
{
    $name = $request->input('name');
    $prodCode = $request->input('prod_code'); // Current product being edited
    $ownerId = session('owner_id');

    // Check for exact match (case-insensitive), excluding current product
    $exactMatch = DB::table('products')
        ->where('owner_id', $ownerId)
        ->where('prod_code', '!=', $prodCode)
        ->whereRaw('LOWER(name) = ?', [strtolower($name)])
        ->exists();

    // Check for similar matches using LIKE (for typo detection)
    $similarMatches = [];
    if (!$exactMatch) {
        $similar = DB::table('products')
            ->where('owner_id', $ownerId)
            ->where('prod_code', '!=', $prodCode)
            ->where(function($query) use ($name) {
                $query->where('name', 'LIKE', '%' . $name . '%')
                      ->orWhere('name', 'LIKE', $name . '%')
                      ->orWhere('name', 'LIKE', '%' . $name);
            })
            ->limit(5)
            ->pluck('name')
            ->toArray();

        // Filter out exact matches from similar results
        $similarMatches = array_filter($similar, function($item) use ($name) {
            return strtolower($item) !== strtolower($name);
        });
    }

    return response()->json([
        'exact_match' => $exactMatch,
        'similar_matches' => array_values($similarMatches)
    ]);
}

/**
 * Check if barcode already exists (real-time validation for edit)
 * Renamed to avoid conflict with existing checkBarcode method
 */
public function checkBarcodeEdit(Request $request)
{
    $barcode = $request->input('barcode');
    $prodCode = $request->input('prod_code'); // Current product being edited
    $ownerId = session('owner_id');

    if (empty($barcode)) {
        return response()->json([
            'exact_match' => false,
            'similar_matches' => []
        ]);
    }

    // Check for exact match (case-insensitive), excluding current product
    $exactMatch = DB::table('products')
        ->where('owner_id', $ownerId)
        ->where('prod_code', '!=', $prodCode)
        ->whereRaw('LOWER(barcode) = ?', [strtolower($barcode)])
        ->exists();

    // Check for similar matches using LIKE (for typo detection)
    $similarMatches = [];
    if (!$exactMatch) {
        $similar = DB::table('products')
            ->where('owner_id', $ownerId)
            ->where('prod_code', '!=', $prodCode)
            ->whereNotNull('barcode')
            ->where('barcode', '!=', '')
            ->where(function($query) use ($barcode) {
                $query->where('barcode', 'LIKE', '%' . $barcode . '%')
                      ->orWhere('barcode', 'LIKE', $barcode . '%')
                      ->orWhere('barcode', 'LIKE', '%' . $barcode);
            })
            ->limit(5)
            ->pluck('barcode')
            ->toArray();

        // Filter out exact matches and empty values from similar results
        $similarMatches = array_filter($similar, function($item) use ($barcode) {
            return !empty($item) && strtolower($item) !== strtolower($barcode);
        });
    }

    return response()->json([
        'exact_match' => $exactMatch,
        'similar_matches' => array_values($similarMatches)
    ]);
}


public function update(Request $request, $prodCode)
{
    $ownerId = session('owner_id');

    // Validation
    $validated = $request->validate([
        'name'             => 'required|string|max:100',
        'barcode'          => 'nullable|string|max:50',
        'cost_price'       => 'required|numeric|min:0',
        'selling_price'    => 'nullable|numeric|min:0',
        'previous_prices'  => 'nullable|numeric|min:0',
        'previous_cost_price' => 'nullable|numeric|min:0',
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

    // Server-side check for duplicate name (case-insensitive)
    $duplicateName = DB::table('products')
        ->where('owner_id', $ownerId)
        ->where('prod_code', '!=', $prodCode)
        ->whereRaw('LOWER(name) = ?', [strtolower($validated['name'])])
        ->exists();

    if ($duplicateName) {
        return back()->with('error', 'A product with this name already exists.')->withInput();
    }

    // Server-side check for duplicate barcode (case-insensitive)
    if (!empty($validated['barcode'])) {
        $duplicateBarcode = DB::table('products')
            ->where('owner_id', $ownerId)
            ->where('prod_code', '!=', $prodCode)
            ->whereRaw('LOWER(barcode) = ?', [strtolower($validated['barcode'])])
            ->exists();

        if ($duplicateBarcode) {
            return back()->with('error', 'A product with this barcode already exists.')->withInput();
        }
    }

    // Handle image upload if present
    $photoPath = null;
    if ($request->hasFile('prod_image')) {
        $photoPath = $request->file('prod_image')->store('product_images', 'public');
    }

    // Determine which prices to use (new input or previous selection)
    $finalSellingPrice = $request->previous_prices ?: $request->selling_price;
    $finalCostPrice = $request->previous_cost_price ?: $request->cost_price;

    // Validate that at least one selling price is provided
    if (!$finalSellingPrice) {
        return back()->with('error', 'Please provide or select a selling price.')->withInput();
    }

    // Prepare updated product data
    $updateData = [
        'name'          => $validated['name'],
        'barcode'       => $validated['barcode'],
        'cost_price'    => $finalCostPrice,
        'selling_price' => $finalSellingPrice,
        'unit_id'       => $validated['unit_id'],
        'stock_limit'   => $validated['stock_limit'],
        'description'   => $validated['description'] ?? null,
        'prod_status'   => $validated['prod_status'],
    ];

    if ($photoPath) {
        $updateData['prod_image'] = $photoPath;
    }

    if ($product->cost_price != $finalCostPrice || $product->selling_price != $finalSellingPrice) {

        // Close current active pricing record
        DB::table('pricing_history')
            ->where('prod_code', $prodCode)
            ->where('owner_id', $ownerId)
            ->whereNull('effective_to')
            ->update(['effective_to' => now()]);

        // Insert new active pricing record
        DB::table('pricing_history')->insert([
            'prod_code'         => $prodCode,
            'old_cost_price'    => $finalCostPrice,
            'old_selling_price' => $finalSellingPrice,
            'owner_id'          => $ownerId,
            'updated_by'        => session('staff_id') ?? null,
            'effective_from'    => now(),
            'effective_to'      => null,
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

    // Check product limits
    $ownerPlan = DB::selectOne("SELECT plan_id FROM subscriptions WHERE owner_id = ? AND status = 'active'", [$ownerId]);
    $productCount = DB::selectOne("SELECT COUNT(prod_code) as count FROM products WHERE owner_id = ?", [$ownerId])->count;

    if ($ownerPlan && $ownerPlan->plan_id == 3 && $productCount >= 50) {
        return response()->json(['success' => false, 'message' => 'Your current Basic plan allows up to 50 products only. Upgrade to add more.'], 422);
    }

    if ($ownerPlan && $ownerPlan->plan_id == 1 && $productCount >= 200) {
        return response()->json(['success' => false, 'message' => 'Your current Standard plan allows up to 200 products only. Upgrade to add more.'], 422);
    }

    $validated = $request->validate([
        'barcode' => 'required|string|max:50',
        'name' => 'required|string|max:100',
        'cost_price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'vat_category' => 'required|in:vat_exempt,vat_inclusive',
        'description' => 'nullable|string',
        'category_id' => 'nullable',
        'unit_id' => 'nullable',
        'custom_category' => 'nullable|string|max:100',
        'custom_unit' => 'nullable|string|max:50',
        'photo' => 'nullable|image|max:2048',
        'stock_limit' => 'required|integer|min:0',
        'batches' => 'nullable|array',
        'batches.*.quantity' => 'required_with:batches|integer|min:1',
        'batches.*.expiration_date' => 'nullable|date',
        'confirmed_similar' => 'nullable|string',
        'confirmed_category' => 'nullable|string',
        'confirmed_unit' => 'nullable|string'
    ]);

    // Validate expiration dates (must be at least 7 days from today)
    if (!empty($validated['batches'])) {
        $today = now()->startOfDay();
        $minDate = now()->addDays(7)->startOfDay();
        $invalidDates = [];

        foreach ($validated['batches'] as $index => $batch) {
            if (!empty($batch['expiration_date'])) {
                $expirationDate = \Carbon\Carbon::parse($batch['expiration_date'])->startOfDay();
                $daysDiff = $today->diffInDays($expirationDate, false);

                if ($expirationDate->lt($minDate)) {
                    $batchNum = $index + 1;
                    if ($daysDiff < 0) {
                        $invalidDates[] = "Batch #{$batchNum} (date is in the past)";
                    } else {
                        $invalidDates[] = "Batch #{$batchNum} (only {$daysDiff} day(s) away, needs 7 days minimum)";
                    }
                }
            }
        }

        if (!empty($invalidDates)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid expiration dates: ' . implode(', ', $invalidDates)
            ], 422);
        }
    }

    // Check for existing/similar product names
    $confirmedSimilar = $request->input('confirmed_similar') === '1';
    
    if (!$confirmedSimilar) {
        $existingProducts = DB::table('products')
            ->where('owner_id', $ownerId)
            ->get();
        
        $productMatch = $this->findProductNameMatch($validated['name'], $existingProducts);
        
        if ($productMatch && $productMatch['isExact']) {
            return response()->json([
                'success' => false,
                'message' => 'Product name already exists: ' . $productMatch['name']
            ], 422);
        }
    }

    // Handle category
    if ($validated['category_id'] === 'other' && !empty($validated['custom_category'])) {
        $confirmedCategory = $request->input('confirmed_category') === '1';
        
        if (!$confirmedCategory) {
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
        }

        $categoryId = DB::table('categories')->insertGetId([
            'category' => $validated['custom_category'],
            'owner_id' => $ownerId,
        ]);
    } else {
        $categoryId = $validated['category_id'];
    }

    // Handle unit
    if ($validated['unit_id'] === 'other' && !empty($validated['custom_unit'])) {
        $confirmedUnit = $request->input('confirmed_unit') === '1';
        
        if (!$confirmedUnit) {
            $existingUnits = DB::table('units')
                ->where('owner_id', $ownerId)
                ->get();
            
            $unitMatchResult = $this->findUnitMatch($validated['custom_unit'], $existingUnits);

            if ($unitMatchResult && $unitMatchResult['isExact']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit already exists: ' . $unitMatchResult['unit']
                ], 422);
            }
        }

        $unitId = DB::table('units')->insertGetId([
            'unit' => $validated['custom_unit'],
            'owner_id' => $ownerId,
        ]);
    } else {
        $unitId = $validated['unit_id'];
    }

    // Handle photo upload
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('product_images', 'public');
    } else {
        $photoPath = 'assets/no-product-image.png';
    }

    DB::beginTransaction();
    try {
        // Insert product
        $prodCode = DB::table('products')->insertGetId([
            'barcode' => $validated['barcode'],
            'name' => $validated['name'],
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['selling_price'],
            'vat_category' => $validated['vat_category'],
            'description' => $validated['description'] ?? null,
            'owner_id' => $ownerId,
            'staff_id' => null,
            'category_id' => $categoryId,
            'unit_id' => $unitId,
            'prod_image' => $photoPath,
            'stock_limit' => $validated['stock_limit'],
        ]);

        // Insert pricing history
        DB::table('pricing_history')->insert([
            'prod_code' => $prodCode,
            'old_cost_price' => $validated['cost_price'],
            'old_selling_price' => $validated['selling_price'],
            'owner_id' => $ownerId,
            'updated_by' => session('staff_id') ?? null,
            'effective_from' => now(),
            'effective_to' => null,
        ]);

        // ✅ Insert initial stock batches if provided
        if (!empty($validated['batches'])) {
            $batchNumber = 1; // Start from BATCH-1
            
            foreach ($validated['batches'] as $batch) {
                // Generate batch number in the format P{prodCode}-BATCH-{number}
                $batchNumberFormatted = "P{$prodCode}-BATCH-{$batchNumber}";
                
                DB::table('inventory')->insert([
                    'prod_code' => $prodCode,
                    'stock' => $batch['quantity'],
                    'batch_number' => $batchNumberFormatted,
                    'expiration_date' => $batch['expiration_date'] ?? null,
                    'owner_id' => $ownerId,
                    'date_added' => now(),
                    'last_updated' => now(),
                ]);
                
                $batchNumber++; // Increment for next batch
            }
        }

        DB::commit();

        // Log activity
        $ip = $request->ip();
        $guard = 'owner';
        $user = Auth::guard('owner')->user();
        
        $stockInfo = !empty($validated['batches']) 
            ? ' with ' . count($validated['batches']) . ' initial batch(es)' 
            : '';
            
        ActivityLogController::log(
            'Registered new product: ' . $validated['name'] . $stockInfo,
            $guard,
            $user,
            $ip
        );

        return response()->json([
            'success' => true, 
            'message' => 'Product registered successfully!' . $stockInfo
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Product registration error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to register product.'], 500);
    }
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
    $confirmedSimilar = $request->input('confirmed_similar') === '1';

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

    if ($semanticMatch && !$confirmedSimilar) {
        return response()->json([
            'success' => false, 
            'message' => 'Similar category already exists: ' . $semanticMatch,
            'isExactMatch' => false,
            'existingName' => $semanticMatch
        ]);
    }

    // ✅ FIX: Use insertGetId() instead of insert() to get the new category ID
    $categoryId = DB::table('categories')->insertGetId([
        'category' => $categoryName,
        'owner_id' => $ownerId,
    ]);

    // ✅ FIX: Return the category_id in the response
    return response()->json([
        'success' => true, 
        'message' => 'Category added successfully.',
        'category_id' => $categoryId  // ← This is what was missing!
    ]);
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
    
    // Validate expiration dates (must be at least 7 days from today)
    $today = now()->startOfDay();
    $minDate = now()->addDays(7)->startOfDay();
    $invalidDates = [];
    
    foreach ($items as $index => $it) {
        $expiration = $it['expiration_date'] ?? null;
        
        if ($expiration) {
            $expirationDate = \Carbon\Carbon::parse($expiration)->startOfDay();
            $daysDiff = $today->diffInDays($expirationDate, false);
            
            if ($expirationDate->lt($minDate)) {
                $prodCode = $it['prod_code'] ?? 'Unknown';
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
        // Process restocking items
        foreach ($items as $it) {
            $prodCode = $it['prod_code'] ?? null;
            $qty = (int) ($it['qty'] ?? 0);
            $expiration = $it['expiration_date'] ?? null;
            $categoryId = $it['category_id'] ?? null;
            
            if (!$prodCode || $qty <= 0) continue;
            
            // Get latest batch for this specific product
            $latestBatch = DB::table('inventory')
                ->where('prod_code', $prodCode)
                ->where('owner_id', $ownerId)
                ->orderBy('inven_code', 'desc')
                ->value('batch_number');
            
            // Parse batch number format P{prodCode}-BATCH-{number}
            if ($latestBatch && preg_match('/P\d+-BATCH-(\d+)/', $latestBatch, $m)) {
                $nextNumber = ((int)$m[1]) + 1;
            } else {
                $nextNumber = 1;
            }
            
            // Generate next batch number
            $nextBatchNumber = "P{$prodCode}-BATCH-{$nextNumber}";
            
            // Insert inventory record
            DB::table('inventory')->insert([
                'prod_code' => $prodCode,
                'stock' => $qty,
                'batch_number' => $nextBatchNumber,
                'expiration_date' => $expiration,
                'owner_id' => $ownerId,
                'date_added' => now(),
                'last_updated' => now(),
            ]);
        }
        
        // Handle pricing update if provided
        $updatePricing = $request->input('update_pricing');
        $pricingProdCode = $request->input('pricing_prod_code');
        
        if ($updatePricing && $pricingProdCode) {
            $newCostPrice = $request->input('new_cost_price');
            $newSellingPrice = $request->input('new_selling_price');
            $newVatCategory = $request->input('new_vat_category');
            
            // Get current product data
            $currentProduct = DB::table('products')
                ->where('prod_code', $pricingProdCode)
                ->where('owner_id', $ownerId)
                ->first(['cost_price', 'selling_price', 'vat_category']);
            
            if (!$currentProduct) {
                throw new \Exception('Product not found for pricing update.');
            }
            
            // Determine final values (use new if provided, otherwise keep current)
            $finalCostPrice = $newCostPrice ?: $currentProduct->cost_price;
            $finalSellingPrice = $newSellingPrice ?: $currentProduct->selling_price;
            $finalVatCategory = $newVatCategory ?: $currentProduct->vat_category;
            
            // Check if pricing actually changed
            $priceChanged = ($currentProduct->cost_price != $finalCostPrice) || 
                           ($currentProduct->selling_price != $finalSellingPrice) ||
                           ($currentProduct->vat_category != $finalVatCategory);
            
            if ($priceChanged) {
                // Close current active price record in pricing history
                DB::table('pricing_history')
                    ->where('prod_code', $pricingProdCode)
                    ->where('owner_id', $ownerId)
                    ->whereNull('effective_to')
                    ->update(['effective_to' => now()]);
                
                // Insert new price record in pricing history
                DB::table('pricing_history')->insert([
                    'prod_code' => $pricingProdCode,
                    'old_cost_price' => $finalCostPrice,
                    'old_selling_price' => $finalSellingPrice,
                    'owner_id' => $ownerId,
                    'updated_by' => session('staff_id') ?? null,
                    'effective_from' => now(),
                    'effective_to' => null,
                ]);
                
                // Update product table with new pricing
                DB::table('products')
                    ->where('prod_code', $pricingProdCode)
                    ->where('owner_id', $ownerId)
                    ->update([
                        'cost_price' => $finalCostPrice,
                        'selling_price' => $finalSellingPrice,
                        'vat_category' => $finalVatCategory,
                    ]);
            }
        }
        
        DB::commit();
        
        // Log activity
        $ip = $request->ip();
        $guard = 'owner';
        $user = Auth::guard('owner')->user();
        
        $activityMessage = 'Bulk Restock Products';
        if ($updatePricing && $pricingProdCode && isset($priceChanged) && $priceChanged) {
            $activityMessage .= ' with Price Update';
        }
        
        ActivityLogController::log($activityMessage, $guard, $user, $ip);
        
        $successMessage = 'Restock saved successfully.';
        if ($updatePricing && $pricingProdCode && isset($priceChanged) && $priceChanged) {
            $successMessage .= ' Pricing has been updated and recorded in history.';
        }
        
        return response()->json(['success' => true, 'message' => $successMessage]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Bulk restock error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to save bulk restock: ' . $e->getMessage()]);
    }
}


public function getProductPricing($prodCode)
{
    $ownerId = session('owner_id');
    
    $product = DB::table('products')
        ->where('prod_code', $prodCode)
        ->where('owner_id', $ownerId)
        ->first(['cost_price', 'selling_price', 'vat_category']);
    
    if (!$product) {
        return response()->json(['success' => false, 'message' => 'Product not found.']);
    }
    
    return response()->json([
        'success' => true,
        'cost_price' => $product->cost_price,
        'selling_price' => $product->selling_price,
        'vat_category' => $product->vat_category ?? 'vat_inclusive'
    ]);
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





}

    




