<?php

namespace App\Http\Controllers;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RestockController extends Controller
{

    public function exportPdf(Request $request)
    {
        $restockCreated = $request->input('restock_created');
        $items = json_decode($request->input('restock_items'), true);

        $items = array_map(function ($item) {
            return [
                'name'       => $item['name'],
                'quantity'   => (int) $item['quantity'],
                'cost_price' => (float) str_replace(',', '', $item['cost_price']),
                'subtotal'   => (float) str_replace(',', '', $item['subtotal']),
            ];
        }, $items);

        $pdf = PDF::loadView('dashboards.owner.restock_pdf', [
            'restock_created' => $restockCreated,
            'items' => $items
        ]);

        $user = auth('owner')->user();
        $ip = $request->ip();

        ActivityLogController::log(
            'Exported a restock list',
            'owner',
            $user,
            $ip
        );

        return $pdf->download('restock-' . $restockCreated . '.pdf');
    }

    //     public function restockSuggestion(Request $request)
    // {
    //     $ownerId = Auth::guard('owner')->id();
    //     $currentYear = now()->year;
    //     $currentMonth = now()->month;
    //     $daysInMonth = now()->daysInMonth;

    //     // 1️⃣ Categories
    //     $categories = DB::table('categories')
    //         ->where('owner_id', $ownerId)
    //         ->get();

    //     // 2️⃣ Aggregate inventory per product
    //     $inventoryAgg = DB::table('inventory')
    //         ->select('prod_code', DB::raw('SUM(stock) as total_stock'))
    //         ->where('owner_id', $ownerId)
    //         ->groupBy('prod_code');

    //     // 3️⃣ Products with aggregated stock + sales
    //     $products = DB::table('products')
    //         ->join('categories', 'products.category_id', '=', 'categories.category_id')
    //         ->leftJoinSub($inventoryAgg, 'inventory', function ($join) {
    //             $join->on('products.prod_code', '=', 'inventory.prod_code');
    //         })
    //         ->leftJoin(DB::raw("
    //             (
    //                 SELECT 
    //                     ri.prod_code,
    //                     SUM(CASE WHEN YEAR(r.receipt_date) = {$currentYear} AND MONTH(r.receipt_date) = {$currentMonth} THEN ri.item_quantity ELSE 0 END) AS sold_this_month,
    //                     SUM(CASE WHEN YEAR(r.receipt_date) = {$currentYear} THEN ri.item_quantity ELSE 0 END) AS sold_this_year
    //                 FROM receipt_item ri
    //                 INNER JOIN receipt r ON ri.receipt_id = r.receipt_id
    //                 GROUP BY ri.prod_code
    //             ) AS sales
    //         "), 'products.prod_code', '=', 'sales.prod_code')
    //         // 🧩 Exclude already finalized restocks
    //         ->leftJoin('restock_item', 'restock_item.inven_code', '=', 'products.prod_code')
    //         ->whereNull('restock_item.inven_code')
    //         ->where('products.owner_id', $ownerId)
    //         ->select( 
    //             'products.prod_code',
    //             'products.name',
    //             'categories.category',
    //             'products.category_id',
    //             'products.cost_price',
    //             'products.selling_price',
    //             DB::raw('COALESCE(inventory.total_stock, 0) as stock'),
    //             'products.stock_limit',
    //             DB::raw('COALESCE(sales.sold_this_month, 0) as sold_this_month'),
    //             DB::raw('COALESCE(sales.sold_this_year, 0) as sold_this_year')
    //         )
    //         ->get()
    //         ->map(function ($product) use ($daysInMonth) {

    //             // 🧮 1️⃣ Compute Safe Stock (Target Level)
    //             // Target stock = stock_limit * 2 (double the limit for safety)
    //             $targetStock = $product->stock_limit * 2;

    //             // Optional smarter adjustment:
    //             // If selling fast this month, raise the target level slightly
    //             if ($product->sold_this_month > $product->stock_limit) {
    //                 $targetStock *= 1.3; // 30% extra buffer for fast-sellers
    //             }

    //             // --- 2️⃣ Adaptive Low-Stock Formula ---
    //             // Instead of comparing just to the limit, we use safe target
    //             $multiplier = 1.2;
    //             if ($product->sold_this_month > $product->stock_limit) {
    //                 $multiplier = 1.5;
    //             }
    //             $lowStockQty = max(($targetStock * $multiplier) - $product->stock, 0);

    //             // --- 3️⃣ Top-Selling Formula ---
    //             $topSellingQty = 0;
    //             if ($product->sold_this_month > $product->stock_limit) {
    //                 $topSellingQty = max(
    //                     ceil($product->sold_this_month * 0.8) - $product->stock,
    //                     0
    //                 );
    //             }

    //             // --- 4️⃣ Suggested Quantity (choose stronger signal)
    //             if ($lowStockQty > 0 && $topSellingQty > 0) {
    //                 $suggestedQty = ceil(max($lowStockQty, $topSellingQty) * 1.25);
    //             } else {
    //                 $suggestedQty = max($lowStockQty, $topSellingQty);
    //             }
    //             $suggestedQty = (int) round($suggestedQty);

    //             // --- 5️⃣ Reason
    //             $reason = null;
    //             if ($lowStockQty > 0) $reason = '⚠️ Low Stock';
    //             if ($topSellingQty > 0) $reason = $reason ? $reason . ' + 🚀 Top Selling' : '🚀 Top Selling';

    //             $product->target_stock = (int) round($targetStock);
    //             $product->suggested_quantity = $suggestedQty;
    //             $product->reason = $reason;

    //             // --- 6️⃣ Badge color
    //             if (str_contains($reason, '⚠️')) {
    //                 $product->reason_badge = 'background-color:#fef3c7;color:#92400e;';
    //             } elseif (str_contains($reason, '🚀')) {
    //                 $product->reason_badge = 'background-color:#dcfce7;color:#166534;';
    //             } else {
    //                 $product->reason_badge = 'background-color:#e2e8f0;color:#334155;';
    //             }

    //             return $product;
    //         })
    //         ->filter(function ($product) {
    //             // show only if below stock limit and needs restock
    //             return $product->suggested_quantity > 0 && $product->stock < $product->stock_limit;
    //         })
    //         ->sortBy('stock')
    //         ->values();

    //     // --- 7️⃣ For dropdown (unchanged)
    //     $allProducts = DB::table('products')
    //         ->leftJoinSub(
    //             DB::table('inventory')
    //                 ->select('prod_code', DB::raw('SUM(stock) as total_stock'))
    //                 ->where('owner_id', $ownerId)
    //                 ->groupBy('prod_code'),
    //             'inventory',
    //             function ($join) {
    //                 $join->on('products.prod_code', '=', 'inventory.prod_code');
    //             }
    //         )
    //         ->where('products.owner_id', $ownerId)
    //         ->select(
    //             'products.prod_code as inven_code',
    //             'products.name',
    //             'products.cost_price',
    //             DB::raw('COALESCE(inventory.total_stock, 0) as stock')
    //         )
    //         ->get();

    //     return view('dashboards.owner.restock_suggestion', compact('products', 'categories', 'currentYear', 'currentMonth', 'allProducts'));
    // }

    // public function restockSuggestion(Request $request)
    // {
    //     $ownerId = Auth::guard('owner')->id();
    //     $currentYear = now()->year;
    //     $currentMonth = now()->month;
    //     $daysInMonth = now()->daysInMonth;

    //     // 1️⃣ Categories (for dropdowns)
    //     $categories = DB::table('categories')
    //         ->where('owner_id', $ownerId)
    //         ->get();

    //     // 2️⃣ Aggregate inventory per product
    //     $inventoryAgg = DB::table('inventory')
    //         ->select('prod_code', DB::raw('SUM(stock) as total_stock'))
    //         ->where('owner_id', $ownerId)
    //         ->groupBy('prod_code');

    //     // 3️⃣ Main Product Data with Sales and Stock
    //     $products = DB::table('products')
    //         ->join('categories', 'products.category_id', '=', 'categories.category_id')
    //         ->leftJoinSub($inventoryAgg, 'inventory', function ($join) {
    //             $join->on('products.prod_code', '=', 'inventory.prod_code');
    //         })
    //         ->leftJoin(DB::raw("
    //         (
    //             SELECT 
    //                 ri.prod_code,
    //                 SUM(CASE WHEN YEAR(r.receipt_date) = {$currentYear} 
    //                          AND MONTH(r.receipt_date) = {$currentMonth} 
    //                          THEN ri.item_quantity ELSE 0 END) AS sold_this_month,
    //                 SUM(CASE WHEN YEAR(r.receipt_date) = {$currentYear} 
    //                          THEN ri.item_quantity ELSE 0 END) AS sold_this_year
    //             FROM receipt_item ri
    //             INNER JOIN receipt r ON ri.receipt_id = r.receipt_id
    //             GROUP BY ri.prod_code
    //         ) AS sales
    //     "), 'products.prod_code', '=', 'sales.prod_code')
    //         // 🧩 Exclude already finalized restocks
    //         ->leftJoin('restock_item', 'restock_item.inven_code', '=', 'products.prod_code')
    //         ->whereNull('restock_item.inven_code')
    //         ->where('products.owner_id', $ownerId)
    //         ->select(
    //             'products.prod_code',
    //             'products.name',
    //             'categories.category',
    //             'products.category_id',
    //             'products.cost_price',
    //             'products.selling_price',
    //             DB::raw('COALESCE(inventory.total_stock, 0) as stock'),
    //             'products.stock_limit',
    //             DB::raw('COALESCE(sales.sold_this_month, 0) as sold_this_month'),
    //             DB::raw('COALESCE(sales.sold_this_year, 0) as sold_this_year')
    //         )
    //         ->get()
    //         ->map(function ($product) use ($daysInMonth) {

    //             // ================================
    //             // 🧮 TRUE REORDER POINT (ROP) MODEL
    //             // ================================

    //             // 1️⃣ Average Daily Demand (ADD)
    //             $avgDailyDemand = $product->sold_this_month / max($daysInMonth, 1);

    //             // 2️⃣ Lead Time (in days)
    //             // You can store this per supplier or product in future
    //             $leadTime = 5; // e.g., 5 days delivery time

    //             // 3️⃣ Safety Stock
    //             // Option A: Fixed buffer using stock limit
    //             $safetyStock = $product->stock_limit;

    //             // Option B (Adaptive): 25% of expected demand during lead time
    //             // $safetyStock = round(($avgDailyDemand * $leadTime) * 0.25);

    //             // 4️⃣ Reorder Point (ROP)
    //             $reorderPoint = round(($avgDailyDemand * $leadTime) + $safetyStock);

    //             // 5️⃣ Target Stock Level (Goal after restock)
    //             $targetStock = round($reorderPoint * 2);

    //             // 6️⃣ Suggested Quantity (how much to order)
    //             $suggestedQty = max($targetStock - $product->stock, 0);

    //             // 7️⃣ Restock Reason
    //             $reason = null;
    //             if ($product->stock <= $reorderPoint) {
    //                 $reason = '⚠️ Low stocks';
    //             }
    //             if ($avgDailyDemand > ($product->stock_limit / 2)) {
    //                 $reason = $reason ? $reason . ' + 🚀 High Demand' : '🚀 High Demand';
    //             }

    //             // 8️⃣ Badge Color Styling
    //             if (str_contains($reason, '⚠️')) {
    //                 $product->reason_badge = 'background-color:#fef3c7;color:#92400e;';
    //             } elseif (str_contains($reason, '🚀')) {
    //                 $product->reason_badge = 'background-color:#dcfce7;color:#166534;';
    //             } else {
    //                 $product->reason_badge = 'background-color:#e2e8f0;color:#334155;';
    //             }

    //             // 9️⃣ Attach Computed Fields for Display
    //             $product->avg_daily_demand = round($avgDailyDemand, 2);
    //             $product->lead_time = $leadTime;
    //             $product->safety_stock = $safetyStock;
    //             $product->reorder_point = $reorderPoint;
    //             $product->target_stock = $targetStock;
    //             $product->suggested_quantity = (int) $suggestedQty;
    //             $product->reason = $reason;

    //             return $product;
    //         })
    //         ->filter(function ($product) {
    //             // ✅ Show only products needing restock
    //             return $product->suggested_quantity > 0 && $product->stock <= $product->reorder_point;
    //         })
    //         ->sortBy('stock')
    //         ->values();

    //     // --- 7️⃣ For dropdown (unchanged)
    //     $allProducts = DB::table('products')
    //         ->leftJoinSub(
    //             DB::table('inventory')
    //                 ->select('prod_code', DB::raw('SUM(stock) as total_stock'))
    //                 ->where('owner_id', $ownerId)
    //                 ->groupBy('prod_code'),
    //             'inventory',
    //             function ($join) {
    //                 $join->on('products.prod_code', '=', 'inventory.prod_code');
    //             }
    //         )
    //         ->where('products.owner_id', $ownerId)
    //         ->select(
    //             'products.prod_code as inven_code',
    //             'products.name',
    //             'products.cost_price',
    //             DB::raw('COALESCE(inventory.total_stock, 0) as stock')
    //         )
    //         ->get();

    //     // 🏁 Return to View
    //     return view('dashboards.owner.restock_suggestion', compact('products', 'categories', 'currentYear', 'currentMonth', 'allProducts'));
    // }

    public function restockSuggestion(Request $request)
    {
        $ownerId = Auth::guard('owner')->id();
        $currentYear = now()->year;
        $currentMonth = now()->month;
        $daysInMonth = now()->daysInMonth;

        // 1️⃣ Categories
        $categories = DB::table('categories')
            ->where('owner_id', $ownerId)
            ->get();

        // 2️⃣ Aggregate inventory per product
        $inventoryAgg = DB::table('inventory')
            ->select('prod_code', DB::raw('SUM(stock) as total_stock'))
            ->where('owner_id', $ownerId)
            ->groupBy('prod_code');

        // 3️⃣ Products + Sales + Stock + Expired Stock
        $products = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->leftJoinSub($inventoryAgg, 'inventory', function ($join) {
                $join->on('products.prod_code', '=', 'inventory.prod_code');
            })
            ->leftJoin(DB::raw("
            (
                SELECT 
                    ri.prod_code,
                    SUM(CASE WHEN YEAR(r.receipt_date) = {$currentYear} 
                             AND MONTH(r.receipt_date) = {$currentMonth} 
                             THEN ri.item_quantity ELSE 0 END) AS sold_this_month,
                    SUM(CASE WHEN YEAR(r.receipt_date) = {$currentYear} 
                             THEN ri.item_quantity ELSE 0 END) AS sold_this_year
                FROM receipt_item ri
                INNER JOIN receipt r ON ri.receipt_id = r.receipt_id
                GROUP BY ri.prod_code
            ) AS sales
        "), 'products.prod_code', '=', 'sales.prod_code')
            // Join expired products to adjust stock suggestions
            ->leftJoin(DB::raw("
            (
                SELECT 
                    prod_code,
                    SUM(stock) as expired_stock
                FROM inventory
                WHERE expiration_date < CURDATE() AND is_expired = 1 -- Expired products
                GROUP BY prod_code
            ) AS expired
        "), 'products.prod_code', '=', 'expired.prod_code')
            ->leftJoin('restock_item', 'restock_item.inven_code', '=', 'products.prod_code')
            ->whereNull('restock_item.inven_code')
            ->where('products.owner_id', $ownerId)
            ->where('products.prod_status', 'active')
            ->select(
                'products.prod_code',
                'products.name',
                'categories.category',
                'products.category_id',
                'products.cost_price',
                'products.selling_price',
                DB::raw('COALESCE(inventory.total_stock, 0) as stock'),
                'products.stock_limit',
                DB::raw('COALESCE(sales.sold_this_month, 0) as sold_this_month'),
                DB::raw('COALESCE(sales.sold_this_year, 0) as sold_this_year'),
                DB::raw('COALESCE(expired.expired_stock, 0) as expired_stock')  // Include expired stock
            )
            ->get()
            ->map(function ($product) use ($daysInMonth) {

                // ================================
                // 🧮 STEP 1: BASE ROP CALCULATION
                // ================================

                $avgDailyDemand = $product->sold_this_month / max($daysInMonth, 1);
                $leadTime = 5; // configurable later per supplier
                $safetyStock = $product->stock_limit;
                $reorderPoint = round(($avgDailyDemand * $leadTime) + $safetyStock);

                // ==================================
                // ⚙️ STEP 2: ADAPTIVE OLD FORMULA
                // ==================================

                $targetStock = $product->stock_limit * 2;
                if ($product->sold_this_month > $product->stock_limit) {
                    $targetStock *= 1.3;
                }

                $multiplier = 1.2;
                if ($product->sold_this_month > $product->stock_limit) {
                    $multiplier = 1.5;
                }
                $lowStockQty = max(($targetStock * $multiplier) - $product->stock, 0);

                $topSellingQty = 0;
                if ($product->sold_this_month > $product->stock_limit) {
                    $topSellingQty = max(
                        ceil($product->sold_this_month * 0.8) - $product->stock,
                        0
                    );
                }

                // ================================
                // 🚀 STEP 3: COMBINED SMART SUGGESTION
                // ================================

                $isLowStock = $product->stock <= $reorderPoint;
                $isTopSelling = $product->sold_this_month > $product->stock_limit;

                if ($isLowStock && $isTopSelling) {
                    $multiplierFinal = 3.0;
                } elseif ($isTopSelling) {
                    $multiplierFinal = 2.5;
                } elseif ($isLowStock) {
                    $multiplierFinal = 2.0;
                } else {
                    $multiplierFinal = 1.5;
                }

                // Target stock using ROP model
                $ropTargetStock = round($reorderPoint * $multiplierFinal);

                // Final suggested quantity considering expired stock
                $suggestedQty = max(
                    ($ropTargetStock - $product->stock),
                    max($lowStockQty, $topSellingQty)
                );

                // Reduce suggestion for expired products
                if ($product->expired_stock > 0) {
                    $suggestedQty = max($suggestedQty - ($product->expired_stock * 0.5), 0);  // Decrease suggestion based on expired stock
                }

                // Final suggestion (after rounding)
                $suggestedQty = (int) round($suggestedQty);

                // ============================
                // 📋 STEP 4: REASON + BADGE
                // ============================
                $reason = null;
                if ($isLowStock) {
                    $reason = 'Low Stock';
                }

                if ($isTopSelling) {
                    $reason = isset($reason) ? $reason . ' + Top Selling' : 'Top Selling';
                }

                if (str_contains($reason, 'Low Stock')) {
                    $product->reason_badge = 'background-color:#fef3c7;color:#92400e;';
                } elseif (str_contains($reason, 'Top Selling')) {
                    $product->reason_badge = 'background-color:#dcfce7;color:#166534;';
                } else {
                    $product->reason_badge = 'background-color:#e2e8f0;color:#334155;';
                }

                // ============================
                // 📊 Attach computed fields
                // ============================
                $product->avg_daily_demand = round($avgDailyDemand, 2);
                $product->lead_time = $leadTime;
                $product->safety_stock = $safetyStock;
                $product->reorder_point = $reorderPoint;
                $product->target_stock = (int) round($ropTargetStock);
                $product->suggested_quantity = $suggestedQty;
                $product->reason = $reason;

                return $product;
            })
            ->filter(function ($product) {
                return $product->suggested_quantity > 0
                    && ($product->stock <= $product->stock_limit || $product->sold_this_month > $product->stock_limit);
            })
            ->sortBy('stock')
            ->values();

        // Dropdown data (unchanged)
        $allProducts = DB::table('products')
            ->leftJoinSub(
                DB::table('inventory')
                    ->select('prod_code', DB::raw('SUM(stock) as total_stock'))
                    ->where('owner_id', $ownerId)
                    ->groupBy('prod_code'),
                'inventory',
                function ($join) {
                    $join->on('products.prod_code', '=', 'inventory.prod_code');
                }
            )
            ->where('products.owner_id', $ownerId)
            ->select(
                'products.prod_code as inven_code',
                'products.name',
                'products.cost_price',
                DB::raw('COALESCE(inventory.total_stock, 0) as stock')
            )
            ->get();

        return view('dashboards.owner.restock_suggestion', compact('products', 'categories', 'currentYear', 'currentMonth', 'allProducts'));
    }

    
















    public function finalize(Request $request)
    {
        $ownerId = auth()->guard('owner')->id();

        // Validate inputs
        $request->validate([
            'products.*' => 'exists:inventory,inven_code',
            'quantities.*' => 'integer|min:1',
            'custom_products.*' => 'exists:inventory,inven_code',
            'custom_quantities.*' => 'integer|min:1',
        ]);

        // Create restock header
        $restockId = DB::table('restock')->insertGetId([
            'owner_id' => $ownerId,
            'restock_created' => now(),
        ]);

        $items = [];


        // Add products from main table
        if ($request->filled('products')) {
            foreach ($request->products as $code) {
                $items[] = [
                    'inven_code' => $code,
                    'item_quantity' => $request->quantities[$code] ?? 0,
                ];
            }
        }

        // Add custom products from modal
        if ($request->filled('custom_products')) {
            foreach ($request->custom_products as $code) {
                $items[] = [
                    'inven_code' => $code,
                    'item_quantity' => $request->custom_quantities[$code] ?? 0,
                ];
            }
        }

        // Combine duplicates by inven_code
        $items = collect($items)
            ->groupBy('inven_code')
            ->map(function ($group, $inven_code) use ($restockId) {
                return [
                    'restock_id' => $restockId,
                    'inven_code' => $inven_code,
                    'item_quantity' => array_sum(array_column($group->toArray(), 'item_quantity')),
                ];
            })
            ->values()
            ->toArray();

        // Insert all items at once
        if (!empty($items)) {
            DB::table('restock_item')->insert($items);
        }

        $user = auth('owner')->user();
        $ip = $request->ip();

        ActivityLogController::log(
            'Finalized a restock list',
            'owner',
            $user,
            $ip
        );

        return redirect()->route('restock_suggestion');
    }


    public function list()
    {
        $ownerId = auth()->guard('owner')->id();

        $restocks = DB::table('restock')
            ->where('owner_id', $ownerId)
            ->orderByDesc('restock_created')
            ->get();

        $restockItems = DB::table('restock_item')
            ->join('inventory', 'restock_item.inven_code', '=', 'inventory.inven_code')
            ->join('products', 'inventory.prod_code', '=', 'products.prod_code')
            ->select(
                'restock_item.*',
                'products.name',
                'products.cost_price',
                DB::raw('restock_item.item_quantity * products.cost_price as subtotal')
            )
            ->orderByDesc('restock_item.restock_id')
            ->get();


        return view('dashboards.owner.restock_list', compact('restocks', 'restockItems'));
    }

    public function topProducts(Request $request)
    {
        $ownerId = Auth::guard('owner')->id();
        $categoryId = $request->input('category_id');
        $topN = $request->input('top_n', 20); // default Top 20

        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Analyze the same month from the past 3 years
        $years = [$currentYear - 1, $currentYear - 2, $currentYear - 3];

        /**
         * 1️⃣ Fetch Past 3-Year Same-Month Sales
         */
        $pastSales = DB::table('receipt_item as ri')
            ->join('receipt as r', 'ri.receipt_id', '=', 'r.receipt_id')
            ->join('products as p', 'ri.prod_code', '=', 'p.prod_code')
            ->when($categoryId, fn($q) => $q->where('p.category_id', $categoryId))
            ->where('p.owner_id', $ownerId)
            ->whereIn(DB::raw('YEAR(r.receipt_date)'), $years)
            ->whereMonth('r.receipt_date', $currentMonth)
            ->select(
                'p.prod_code',
                'p.name',
                'p.prod_image',
                DB::raw('SUM(ri.item_quantity) as sold'),
                DB::raw('YEAR(r.receipt_date) as year')
            )
            ->groupBy('p.prod_code', 'p.name', 'p.prod_image', 'year')
            ->get();

        /**
         * 2️⃣ Compute 3-Year Average Past Sales
         */
        $pastAverages = $pastSales
            ->groupBy('prod_code')
            ->map(function ($group) {
                $avg = $group->avg('sold');
                $first = $group->first();
                return (object)[
                    'prod_code' => $first->prod_code,
                    'name' => $first->name,
                    'prod_image' => $first->prod_image,
                    'average_past' => (int) round($avg),
                ];
            });

        /**
         * 3️⃣ Fetch Current Month Sales
         */
        $currentSales = DB::table('receipt_item as ri')
            ->join('receipt as r', 'ri.receipt_id', '=', 'r.receipt_id')
            ->join('products as p', 'ri.prod_code', '=', 'p.prod_code')
            ->when($categoryId, fn($q) => $q->where('p.category_id', $categoryId))
            ->whereYear('r.receipt_date', $currentYear)
            ->whereMonth('r.receipt_date', $currentMonth)
            ->where('p.owner_id', $ownerId)
            ->select(
                'p.prod_code',
                'p.name',
                'p.prod_image',
                DB::raw('SUM(ri.item_quantity) as total_sold')
            )
            ->groupBy('p.prod_code', 'p.name', 'p.prod_image')
            ->get()
            ->keyBy('prod_code');

        /**
         * 4️⃣ Combine and Forecast Demand
         */
        $allProducts = DB::table('products')
            ->where('owner_id', $ownerId)
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->select('prod_code', 'name', 'prod_image')
            ->get();

        $topProducts = $allProducts->map(function ($product) use ($pastAverages, $currentSales) {
            $past = $pastAverages[$product->prod_code]->average_past ?? 0;
            $current = $currentSales[$product->prod_code]->total_sold ?? 0;

            // If no past or current sales, skip growth calc
            $alpha = 0.5; // exponential smoothing weight
            $forecasted = round($alpha * $current + (1 - $alpha) * $past);

            // Growth rate (based on forecasted vs past)
            $growth = $past > 0
                ? round((($forecasted - $past) / $past) * 100, 2)
                : ($current > 0 ? 100 : 0); // assume 100% growth if new product sells this month

            return (object)[
                'prod_code' => $product->prod_code,
                'name' => $product->name,
                'prod_image' => $product->prod_image,
                'average_past' => (int) $past,
                'current_month_sold' => (int) $current,
                'forecasted_demand' => (int) $forecasted,
                'growth_rate' => $growth,
            ];
        });

        /**
         * 5️⃣ Sort and Limit to Top N
         */
        $topProducts = $topProducts
            ->sortByDesc('forecasted_demand')
            ->take($topN)
            ->values();

        /**
         * 6️⃣ Fetch Categories (Owner-Based)
         */
        $categories = DB::table('categories')
            ->where('owner_id', $ownerId)
            ->get();

        /**
         * 7️⃣ Return View
         */
        return view('dashboards.owner.seasonal_trends', [
            'topProducts' => $topProducts,
            'categories' => $categories,
            'categoryId' => $categoryId,
            'topN' => $topN,
        ]);
    }
}
