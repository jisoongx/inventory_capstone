<?php

namespace App\Http\Controllers;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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



    // public function restockSuggestion(Request $request)
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

    //     // 3️⃣ Products + Sales + Stock + Expired Stock
    //     $products = DB::table('products')
    //         ->join('categories', 'products.category_id', '=', 'categories.category_id')
    //         ->leftJoinSub($inventoryAgg, 'inventory', function ($join) {
    //             $join->on('products.prod_code', '=', 'inventory.prod_code');
    //         })
    //         ->leftJoin(DB::raw("(
    //     SELECT 
    //         ri.prod_code,
    //         SUM(CASE WHEN YEAR(r.receipt_date) = {$currentYear} 
    //                  AND MONTH(r.receipt_date) = {$currentMonth} 
    //                  THEN ri.item_quantity ELSE 0 END) AS sold_this_month,
    //         SUM(CASE WHEN YEAR(r.receipt_date) = {$currentYear} 
    //                  THEN ri.item_quantity ELSE 0 END) AS sold_this_year
    //     FROM receipt_item ri
    //     INNER JOIN receipt r ON ri.receipt_id = r.receipt_id
    //     GROUP BY ri.prod_code
    // ) AS sales"), 'products.prod_code', '=', 'sales.prod_code')
    //         ->leftJoin(DB::raw("(
    //     SELECT 
    //         prod_code,
    //         SUM(stock) as expired_stock
    //     FROM inventory
    //     WHERE expiration_date < CURDATE() AND is_expired = 1
    //     GROUP BY prod_code
    // ) AS expired"), 'products.prod_code', '=', 'expired.prod_code')

    //          ->whereNotExists(function ($q) use ($ownerId) {
    //     $q->select(DB::raw(1))
    //         ->from('restock_item')
    //         ->join('inventory', 'inventory.inven_code', '=', 'restock_item.inven_code')
    //         ->join('restock', 'restock.restock_id', '=', 'restock_item.restock_id')
    //         ->where('restock.owner_id', $ownerId)
    //         ->where('restock.status', 'pending')  // don't suggest products already pending
    //         ->whereColumn('inventory.prod_code', 'products.prod_code');
    // })

    //         ->where('products.owner_id', $ownerId)
    //         ->where('products.prod_status', 'active')
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
    //             DB::raw('COALESCE(sales.sold_this_year, 0) as sold_this_year'),
    //             DB::raw('COALESCE(expired.expired_stock, 0) as expired_stock')
    //         )
    //         ->get()
    //         ->map(function ($product) use ($daysInMonth) {

    //             // ================================
    //             // 🧮 STEP 1: BASE ROP CALCULATION
    //             // ================================

    //             $avgDailyDemand = $product->sold_this_month / max($daysInMonth, 1);
    //             $leadTime = 5; // configurable later per supplier
    //             $safetyStock = $product->stock_limit;
    //             $reorderPoint = round(($avgDailyDemand * $leadTime) + $safetyStock);

    //             // ==================================
    //             // ⚙️ STEP 2: ADAPTIVE OLD FORMULA
    //             // ==================================

    //             $targetStock = $product->stock_limit * 2;
    //             if ($product->sold_this_month > $product->stock_limit) {
    //                 $targetStock *= 1.3;
    //             }

    //             $multiplier = 1.2;
    //             if ($product->sold_this_month > $product->stock_limit) {
    //                 $multiplier = 1.5;
    //             }
    //             $lowStockQty = max(($targetStock * $multiplier) - $product->stock, 0);

    //             $topSellingQty = 0;
    //             if ($product->sold_this_month > $product->stock_limit) {
    //                 $topSellingQty = max(
    //                     ceil($product->sold_this_month * 0.8) - $product->stock,
    //                     0
    //                 );
    //             }

    //             // ================================
    //             // 🚀 STEP 3: COMBINED SMART SUGGESTION
    //             // ================================

    //             $isLowStock = $product->stock <= $reorderPoint;
    //             $isTopSelling = $product->sold_this_month > $product->stock_limit;

    //             if ($isLowStock && $isTopSelling) {
    //                 $multiplierFinal = 3.0;
    //             } elseif ($isTopSelling) {
    //                 $multiplierFinal = 2.5;
    //             } elseif ($isLowStock) {
    //                 $multiplierFinal = 2.0;
    //             } else {
    //                 $multiplierFinal = 1.5;
    //             }

    //             // Target stock using ROP model
    //             $ropTargetStock = round($reorderPoint * $multiplierFinal);

    //             // Final suggested quantity considering expired stock
    //             $suggestedQty = max(
    //                 ($ropTargetStock - $product->stock),
    //                 max($lowStockQty, $topSellingQty)
    //             );

    //             // Reduce suggestion for expired products
    //             if ($product->expired_stock > 0) {
    //                 $suggestedQty = max($suggestedQty - ($product->expired_stock * 0.5), 0);  // Decrease suggestion based on expired stock
    //             }

    //             // Final suggestion (after rounding)
    //             $suggestedQty = (int) round($suggestedQty);

    //             // ============================
    //             // 📋 STEP 4: REASON + BADGE
    //             // ============================
    //             $reason = null;
    //             if ($isLowStock) {
    //                 $reason = 'Low Stock';
    //             }

    //             if ($isTopSelling) {
    //                 $reason = isset($reason) ? $reason . ' + Top Selling' : 'Top Selling';
    //             }

    //             if (str_contains($reason, 'Low Stock')) {
    //                 $product->reason_badge = 'background-color:#fef3c7;color:#92400e;';
    //             } elseif (str_contains($reason, 'Top Selling')) {
    //                 $product->reason_badge = 'background-color:#dcfce7;color:#166534;';
    //             } else {
    //                 $product->reason_badge = 'background-color:#e2e8f0;color:#334155;';
    //             }

    //             // ============================
    //             // 📊 Attach computed fields
    //             // ============================
    //             $product->avg_daily_demand = round($avgDailyDemand, 2);
    //             $product->lead_time = $leadTime;
    //             $product->safety_stock = $safetyStock;
    //             $product->reorder_point = $reorderPoint;
    //             $product->target_stock = (int) round($ropTargetStock);
    //             $product->suggested_quantity = $suggestedQty;
    //             $product->reason = $reason;

    //             return $product;
    //         })
    //         ->filter(function ($product) {
    //             return $product->suggested_quantity > 0
    //                 && ($product->stock <= $product->stock_limit || $product->sold_this_month > $product->stock_limit);
    //         })
    //         ->sortBy('stock')
    //         ->values();

    //     // Dropdown data (unchanged)
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
            ->leftJoin(DB::raw("(
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
        ) AS sales"), 'products.prod_code', '=', 'sales.prod_code')
            ->leftJoin(DB::raw("(
            SELECT 
                prod_code,
                SUM(stock) as expired_stock
            FROM inventory
            WHERE expiration_date < CURDATE() AND is_expired = 1
            GROUP BY prod_code
        ) AS expired"), 'products.prod_code', '=', 'expired.prod_code')

            ->whereNotExists(function ($q) use ($ownerId) {
                // Changed from inven_code to prod_code
                $q->select(DB::raw(1))
                    ->from('restock_item')
                    ->join('restock', 'restock.restock_id', '=', 'restock_item.restock_id')
                    ->where('restock.owner_id', $ownerId)
                    ->where('restock.status', 'pending')  // don't suggest products already pending
                    ->whereColumn('restock_item.prod_code', 'products.prod_code'); // Join based on prod_code now
            })

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
                DB::raw('COALESCE(expired.expired_stock, 0) as expired_stock')
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



    // public function finalize(Request $request)
    // {

    //     $ownerId = auth()->guard('owner')->id();

    //     // 1️⃣ Validate Inputs
    //     $request->validate([
    //         'products.*' => 'exists:products,prod_code',
    //         'quantities.*' => 'integer|min:1',
    //         'custom_products.*' => 'exists:products,prod_code', // ✅ Changed from inventory to products
    //         'custom_quantities.*' => 'integer|min:1',
    //     ]);

    //     // 2️⃣ Create Restock Header
    //     $restockId = DB::table('restock')->insertGetId([
    //         'owner_id' => $ownerId,
    //         'restock_created' => now(),
    //     ]);

    //     $items = [];

    //     // 3️⃣ Add Regular Products
    //     if ($request->filled('products')) {
    //         foreach ($request->products as $prodCode) {
    //             $inventory = DB::table('inventory')
    //                 ->where('prod_code', $prodCode)
    //                 ->where('owner_id', $ownerId)
    //                 ->first();

    //             if (!$inventory) continue;

    //             $qty = $request->quantities[$prodCode] ?? null;
    //             if (!$qty || $qty < 1) continue;

    //             $items[] = [
    //                 'restock_id' => $restockId,
    //                 'inven_code' => $inventory->inven_code,
    //                 'item_quantity' => $qty,
    //             ];
    //         }
    //     }

    //     // 4️⃣ Add Custom Products
    //     if ($request->filled('custom_products')) {
    //         foreach ($request->custom_products as $prodCode) {
    //             $qty = $request->custom_quantities[$prodCode] ?? null;
    //             if (!$qty || $qty < 1) continue;

    //             // ✅ Look up inventory record for this product code
    //             $inventory = DB::table('inventory')
    //                 ->where('prod_code', $prodCode)
    //                 ->where('owner_id', $ownerId)
    //                 ->first();

    //             if (!$inventory) continue;

    //             $items[] = [
    //                 'restock_id' => $restockId,
    //                 'inven_code' => $inventory->inven_code, // use actual inven_code
    //                 'item_quantity' => $qty,
    //             ];
    //         }
    //     }

    //     // 5️⃣ Error if no items
    //     if (empty($items)) {
    //         return redirect()->route('restock_suggestion')
    //             ->with('error', 'No products selected for restock.');
    //     }

    //     // 6️⃣ Combine duplicates
    //     $items = collect($items)
    //         ->groupBy('inven_code')
    //         ->map(function ($group) use ($restockId) {
    //             return [
    //                 'restock_id' => $restockId,
    //                 'inven_code' => $group->first()['inven_code'],
    //                 'item_quantity' => $group->sum('item_quantity'),
    //             ];
    //         })
    //         ->values()
    //         ->toArray();

    //     // 7️⃣ Insert
    //     DB::table('restock_item')->insert($items);

    //     // 8️⃣ Log activity
    //     ActivityLogController::log(
    //         'Finalized a restock list',
    //         'owner',
    //         auth('owner')->user(),
    //         $request->ip()
    //     );

    //     return redirect()->route('restock_suggestion')
    //         ->with('success', 'Restock list successfully created!');
    // }

    public function finalize(Request $request)
    {
        $ownerId = auth()->guard('owner')->id();

        // 1️⃣ Validate Inputs
        $request->validate([
            'products.*' => 'exists:products,prod_code',
            'quantities.*' => 'integer|min:1',
            'custom_products.*' => 'exists:products,prod_code',
            'custom_quantities.*' => 'integer|min:1',
        ]);

        // 2️⃣ Create Restock Header
        $restockId = DB::table('restock')->insertGetId([
            'owner_id' => $ownerId,
            'restock_created' => now(),
        ]);

        // 3️⃣ Add Regular Products (Direct Insert)
        if ($request->filled('products')) {
            foreach ($request->products as $prodCode) {
                // Directly fetch the quantity from the form input
                $qty = $request->quantities[$prodCode] ?? null;
                if (!$qty || $qty < 1) continue;

                // Directly insert the product and quantity into the restock_item table
                DB::table('restock_item')->insert([
                    'restock_id' => $restockId,
                    'prod_code' => $prodCode,  // Store prod_code instead of inven_code
                    'item_quantity' => $qty,
                ]);
            }
        }

        // 4️⃣ Add Custom Products (Direct Insert)
        if ($request->filled('custom_products')) {
            foreach ($request->custom_products as $prodCode) {
                $qty = $request->custom_quantities[$prodCode] ?? null;
                if (!$qty || $qty < 1) continue;

                // Directly insert the custom product and quantity into the restock_item table
                DB::table('restock_item')->insert([
                    'restock_id' => $restockId,
                    'prod_code' => $prodCode,  // Store prod_code instead of inven_code
                    'item_quantity' => $qty,
                ]);
            }
        }

        // 5️⃣ Error if no items
        if (!DB::table('restock_item')->where('restock_id', $restockId)->exists()) {
            return redirect()->route('restock_suggestion')
                ->with('error', 'No products selected for restock.');
        }

        // 6️⃣ Log activity
        ActivityLogController::log(
            'Finalized a restock list',
            'owner',
            auth('owner')->user(),
            $request->ip()
        );

        return redirect()->route('restock_suggestion')
            ->with('success', 'Restock list successfully created!');
    }





    public function list()
    {
        $ownerId = auth()->guard('owner')->id();

        $restocks = DB::table('restock')
            ->where('owner_id', $ownerId)
            ->orderByDesc('restock_created')
            ->select('restock_id', 'restock_created', 'status')  // Make sure to select status
            ->get();


        $restockItems = DB::table('restock_item')
            ->join('products', 'restock_item.prod_code', '=', 'products.prod_code')  // Use prod_code directly
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

    public function updateStatus(Request $request)
    {
        $request->validate([
            'restock_id' => 'required|integer',
            'status' => 'required|in:received,cancelled'
        ]);

        DB::table('restock')
            ->where('restock_id', $request->restock_id)
            ->update([
                'status' => $request->status
            ]);

        return back()->with('success', 'Restock status updated!');
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
