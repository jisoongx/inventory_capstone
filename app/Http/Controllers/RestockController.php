<?php

namespace App\Http\Controllers;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\ForecastService;
use Illuminate\Support\Facades\Log;


class RestockController extends Controller
{

    public function exportPdf(Request $request)
    {
        $restockCreated = $request->input('restock_created');
        $items = json_decode($request->input('restock_items'), true);

        // Rebuild items including the NEW columns
        $items = array_map(function ($item) {
            return [
                'name'              => $item['name'],
                'quantity'          => (int) ($item['quantity'] ?? 0),

                // NEW FIELDS
                'item_status'       => $item['item_status'] ?? '-',
                'item_restock_date' => $item['item_restock_date'] ?? '-',

                // If your table still sends these, keep them. If not, defaults are safe.
                'cost_price'        => isset($item['cost_price'])
                    ? (float) str_replace(',', '', $item['cost_price'])
                    : 0,

                'subtotal'          => isset($item['subtotal'])
                    ? (float) str_replace(',', '', $item['subtotal'])
                    : 0,
            ];
        }, $items);

        // Load PDF view
        $pdf = PDF::loadView('dashboards.owner.restock_pdf', [
            'restock_created' => $restockCreated,
            'items'           => $items
        ]);

        // Log activity
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



   

    public function restockSuggestion(Request $request)
    {
        if (!Auth::guard('owner')->check()) {
            abort(403, 'Unauthorized access.');
        }

        $ownerId      = Auth::guard('owner')->id();
        $currentYear  = now()->year;
        $currentMonth = now()->month;
        $daysInMonth  = now()->daysInMonth;

        // ------------------------------------
        // 1️⃣ LOAD CATEGORIES
        // ------------------------------------
        $categories = DB::table('categories')
            ->where('owner_id', $ownerId)
            ->get();

        // ------------------------------------
        // 2️⃣ LOAD ALL ACTIVE PRODUCTS (dropdown)
        // ------------------------------------
        $allProducts = DB::table('products')
            ->leftJoinSub(
                DB::table('inventory')
                    ->select('prod_code', DB::raw('SUM(stock) as total_stock'))
                    ->where('owner_id', $ownerId)
                    ->groupBy('prod_code'),
                'inventory',
                fn($join) => $join->on('products.prod_code', '=', 'inventory.prod_code')
            )
            ->where('products.owner_id', $ownerId)
            ->where('products.prod_status', 'active')
            ->select(
                'products.prod_code as inven_code',
                'products.name',
                'products.category_id',
                'products.cost_price',
                DB::raw('COALESCE(inventory.total_stock, 0) as stock')
            )
            ->get();

        // ------------------------------------
        // 3️⃣ LOAD PRODUCTS + STOCK + SALES + EXPIRED
        // ------------------------------------
        $inventoryAgg = DB::table('inventory')
            ->select('prod_code', DB::raw('SUM(stock) as total_stock'))
            ->where('owner_id', $ownerId)
            ->groupBy('prod_code');

        $products = DB::table('products')
            ->where('products.owner_id', $ownerId)
            ->where('products.prod_status', 'active')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->leftJoinSub(
                $inventoryAgg,
                'inventory',
                fn($join) =>
                $join->on('products.prod_code', '=', 'inventory.prod_code')
            )
            ->leftJoin(DB::raw("(
            SELECT 
                ri.prod_code,
                SUM(
                    CASE 
                        WHEN YEAR(r.receipt_date) = {$currentYear}
                        AND MONTH(r.receipt_date) = {$currentMonth}
                        THEN ri.item_quantity ELSE 0 
                    END
                ) AS sold_this_month
            FROM receipt_item ri
            INNER JOIN receipt r ON ri.receipt_id = r.receipt_id
            GROUP BY ri.prod_code
        ) AS sales_month"), 'products.prod_code', '=', 'sales_month.prod_code')
            ->leftJoin(DB::raw("(
            SELECT 
                prod_code,
                SUM(stock) AS expired_stock
            FROM inventory
            WHERE expiration_date < CURDATE()
              AND is_expired = 1
              AND MONTH(expiration_date) = {$currentMonth}
              AND YEAR(expiration_date) = {$currentYear}
            GROUP BY prod_code
        ) AS expired"), 'products.prod_code', '=', 'expired.prod_code')
            ->select(
                'products.prod_code',
                'products.name',
                'categories.category',
                'products.category_id',
                'products.cost_price',
                DB::raw('COALESCE(inventory.total_stock, 0) as stock'),
                'products.stock_limit',
                DB::raw('COALESCE(sales_month.sold_this_month, 0) as sold_this_month'),
                DB::raw('COALESCE(expired.expired_stock, 0) as expired_stock')
            )
            ->get();

        $forecastService = app(\App\Services\ForecastService::class);

        // ------------------------------------
        // 4️⃣ PROCESS EACH PRODUCT
        // ------------------------------------
        $products = $products->map(function ($product) use ($ownerId, $forecastService, $daysInMonth) {

            // A. Monthly sales series (for SES / Holt-Winters)
            $monthlySales = DB::table('receipt_item')
                ->join('receipt', 'receipt.receipt_id', '=', 'receipt_item.receipt_id')
                ->where('receipt.owner_id', $ownerId)
                ->where('receipt_item.prod_code', $product->prod_code)
                ->where('receipt.receipt_date', '>=', now()->subYears(3))
                ->selectRaw("YEAR(receipt.receipt_date) AS y, MONTH(receipt.receipt_date) AS m, SUM(item_quantity) AS total")
                ->groupBy('y', 'm')
                ->orderBy('y')
                ->orderBy('m')
                ->pluck('total')
                ->toArray();

            Log::info("[RESTOCK] Forecast input", [
                "product_code"   => $product->prod_code,
                "product_name"   => $product->name,
                "sales_history"  => $monthlySales
            ]);

            // ------------------------------------
            // B. Forecasting Logic (SES / Holt-Winters)
            // ------------------------------------
            $forecast = 0;
            $algoUsed = null;

            if (count($monthlySales) > 0) {
                $result = $forecastService->forecast($monthlySales);

                Log::info("[RESTOCK] Forecast result", [
                    "product_code" => $product->prod_code,
                    "product_name" => $product->name,
                    "ses"          => $result['ses'] ?? null,
                    "holtwinters"  => $result['holtwinters'] ?? null,
                ]);

                if (!empty($result['holtwinters'])) {
                    $forecast = $result['holtwinters'];
                    $algoUsed = "holt-winters";
                } elseif (!empty($result['ses'])) {
                    $forecast = $result['ses'];
                    $algoUsed = "ses";
                } else {
                    $forecast = 0;
                    $algoUsed = null;
                }
            }

            Log::info("[RESTOCK] Forecast chosen", [
                "product_code"   => $product->prod_code,
                "product_name"   => $product->name,
                "forecast_used"  => $forecast,
                "algorithm"      => $algoUsed
            ]);

            // ------------------------------------
            // C. SHORT-TERM SALES (last 7 days & last 3 days)
            // ------------------------------------
            $last7Sales = DB::table('receipt_item')
                ->join('receipt', 'receipt.receipt_id', '=', 'receipt_item.receipt_id')
                ->where('receipt.owner_id', $ownerId)
                ->where('receipt_item.prod_code', $product->prod_code)
                ->where('receipt.receipt_date', '>=', now()->subDays(7))
                ->sum('item_quantity');

            $last3Sales = DB::table('receipt_item')
                ->join('receipt', 'receipt.receipt_id', '=', 'receipt_item.receipt_id')
                ->where('receipt.owner_id', $ownerId)
                ->where('receipt_item.prod_code', $product->prod_code)
                ->where('receipt.receipt_date', '>=', now()->subDays(3))
                ->sum('item_quantity');

            // ------------------------------------
            // D. AVG DAILY DEMAND (Hybrid)
            // ------------------------------------
            $avgDailyDemand = 0;

            $hasMatureHistory = count($monthlySales) >= 3 && $forecast > 0;

            if ($hasMatureHistory) {
                // Mature product → use monthly forecast first
                $avgDailyDemand = $forecast / 30;

                // SPIKE OVERRIDE: if last 3 days are very strong, trust them more
                if ($last3Sales > 0 && $last3Sales >= ($forecast * 0.25)) {
                    $avgDaily3 = $last3Sales / min(3, $daysInMonth);
                    $avgDailyDemand = max($avgDailyDemand, $avgDaily3);
                }
            } else {
                // New or young product → use short-term demand
                if ($last7Sales > 0) {
                    $avgDailyDemand = $last7Sales / min(7, $daysInMonth);
                } elseif ($forecast > 0) {
                    $avgDailyDemand = $forecast / 30;
                } else {
                    $avgDailyDemand = 0.5; // very small baseline to avoid zero
                }
            }

            // ------------------------------------
            // E. Dynamic Lead Time (Frequency-based)
            // ------------------------------------
            $restockDates = DB::table('inventory')
                ->where('owner_id', $ownerId)
                ->where('prod_code', $product->prod_code)
                ->orderBy('date_added')
                ->pluck('date_added')
                ->filter()
                ->map(fn($d) => \Carbon\Carbon::parse($d))
                ->unique(fn($d) => $d->format('Y-m-d'))
                ->values()
                ->toArray();

            $leadTime = 3; // default

            if (count($restockDates) > 1) {
                $intervals = [];

                for ($i = 1; $i < count($restockDates); $i++) {
                    $intervals[] = $restockDates[$i]->diffInDays($restockDates[$i - 1]);
                }

                if (!empty($intervals)) {
                    $avgInterval = array_sum($intervals) / count($intervals);
                    $leadTime    = max(3, min(14, round($avgInterval))); // 3–14 days
                }
            }

            // ------------------------------------
            // F. Reorder Point
            // ------------------------------------
            $safetyStock  = $product->stock_limit;        // your "minimum stock"
            $reorderPoint = round(($avgDailyDemand * $leadTime) + $safetyStock);

            // ------------------------------------
            // G. High Demand (Hybrid & Spike-based)
            // ------------------------------------
            $isHighDemand = false;

            if ($hasMatureHistory) {
                // Mature: compare this month's sales vs forecast
                if ($product->sold_this_month > ($forecast * 1.20)) {
                    $isHighDemand = true;
                }
            } else {
                // New product: use last 7 days pace
                $avgDaily7 = ($last7Sales > 0)
                    ? $last7Sales / min(7, $daysInMonth)
                    : 0;

                if ($avgDaily7 >= 2) { // ≥2 per day → hot
                    $isHighDemand = true;
                }
            }

            // Spike catch: if last 3 days are strong vs monthly expectation
            if (!$isHighDemand && $forecast > 0 && $last3Sales > 0) {
                if ($last3Sales >= ($forecast * 0.25)) {
                    $isHighDemand = true;
                }
            }

            // ------------------------------------
            // H. Low Stock
            // ------------------------------------
            $isLowStock   = $product->stock <= $safetyStock; // include equals
            $isOutOfStock = $product->stock == 0;

            // ------------------------------------
            // I. Multipliers (how aggressive we restock)
            // ------------------------------------
            if ($isLowStock && $isHighDemand) {
                $multiplierFinal = 0.35;
            } elseif ($isHighDemand) {
                $multiplierFinal = 0.25;
            } elseif ($isLowStock) {
                $multiplierFinal = 0.15;
            } else {
                $multiplierFinal = 0.10;
            }

            // ------------------------------------
            // J. Suggested Quantity
            // ------------------------------------
            $bufferQty   = round($reorderPoint * $multiplierFinal);
            $targetStock = $reorderPoint + $bufferQty;

            $suggestedQty = max($targetStock - $product->stock, 0);

            if ($product->expired_stock > 0) {
                $suggestedQty = max($suggestedQty - $product->expired_stock, 0);
            }

            $suggestedQty = (int) round($suggestedQty);

            // ------------------------------------
            // K. Badges
            // ------------------------------------
            $reason = null;
            $badge  = null;

            if ($isOutOfStock) {
                $reason = "Out of Stock";
                $badge  = 'background-color:#fee2e2;color:#b91c1c;'; // red
            } elseif ($isLowStock && !$isHighDemand) {
                $reason = "Low Stock";
                $badge  = 'background-color:#fef3c7;color:#92400e;'; // yellow
            } elseif ($isHighDemand && !$isLowStock) {
                $reason = "High Demand";
                $badge  = 'background-color:#dcfce7;color:#166534;'; // green
            } elseif ($isLowStock && $isHighDemand) {
                $reason = "Low Stock + High Demand";
                $badge  = 'background-color:#dcfce7;color:#166534;'; // green
            }

            // Attach fields for blade
            $product->forecast           = round($forecast);
            $product->algo_used          = $algoUsed;
            $product->lead_time          = $leadTime;
            $product->avg_daily_demand   = round($avgDailyDemand, 2);
            $product->reorder_point      = $reorderPoint;
            $product->suggested_quantity = $suggestedQty;
            $product->reason             = $reason;
            $product->reason_badge       = $badge;
            $product->isOutOfStock       = $isOutOfStock;
            $product->isLowStock         = $isLowStock;
            $product->isHighDemand       = $isHighDemand;

            // DEBUG LOG
            Log::info("[RESTOCK DEBUG]", [
                "prod_code"            => $product->prod_code,
                "name"                 => $product->name,
                "monthly_sales"        => $monthlySales,
                "forecast_used"        => $forecast,
                "algorithm_used"       => $algoUsed,
                "last7Sales"           => $last7Sales,
                "last3Sales"           => $last3Sales,
                "avg_daily_demand"     => $avgDailyDemand,
                "lead_time"            => $leadTime,
                "current_stock"        => $product->stock,
                "safety_stock_limit"   => $product->stock_limit,
                "expired_stock"        => $product->expired_stock,
                "reorder_point"        => $reorderPoint,
                "multiplier_used"      => $multiplierFinal,
                "buffer_qty"           => $bufferQty,
                "target_stock"         => $targetStock,
                "suggested_quantity"   => $suggestedQty,
                "is_low_stock"         => $isLowStock,
                "is_high_demand"       => $isHighDemand,
                "is_out_of_stock"      => $isOutOfStock,
                "reason"               => $reason,
            ]);

            return $product;
        })

            // Only show products that actually need ordering
            ->filter(
                fn($p) =>
                $p->suggested_quantity > 0 &&
                    ($p->isLowStock || $p->isHighDemand || $p->isOutOfStock) &&
                    $p->stock < $p->reorder_point
            )
            ->values();

        // ------------------------------------
        // RETURN VIEW
        // ------------------------------------
        return view('dashboards.owner.restock_suggestion', compact(
            'products',
            'categories',
            'allProducts',
            'currentYear',
            'currentMonth'
        ));
    }






    public function finalize(Request $request)
    {
        if (!Auth::guard('owner')->check()) {
            abort(403, 'Unauthorized access.');
        }
        
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
        $isStaff = false;

        if (auth()->guard('owner')->check()) {
            $ownerId = auth()->guard('owner')->id();
        } elseif (auth()->guard('staff')->check()) {
            $ownerId = auth()->guard('staff')->user()->owner_id;
            $isStaff = true;
        } else {
            abort(403, 'Unauthorized');
        }

        
        // Fetch all restocks
        $restocks = DB::table('restock')
            ->where('owner_id', $ownerId)
            ->orderByDesc('restock_created')
            ->get();

        // Fetch all restock items + product info
        $restockItems = DB::table('restock_item')
            ->join('products', 'restock_item.prod_code', '=', 'products.prod_code')
            ->select(
                'restock_item.*',
                'products.name',
                'products.cost_price',
                DB::raw('restock_item.item_quantity * products.cost_price as subtotal')
            )
            ->orderByDesc('restock_item.restock_id')
            ->get();

        foreach ($restockItems as $item) {

            $restock = $restocks->firstWhere('restock_id', $item->restock_id);

            if (!$restock || in_array($restock->status, ['resolved', 'cancelled'])) {
                continue; // do not update these
            }

            $restockCreated = Carbon::parse($restock->restock_created);

            // Total stock added after restock created
            $totalAdded = DB::table('inventory')
                ->where('prod_code', $item->prod_code)
                ->where('owner_id', $ownerId)
                ->where('last_updated', '>=', $restockCreated)
                ->sum('stock');

            // Last stock add event
            $lastStock = DB::table('inventory')
                ->where('prod_code', $item->prod_code)
                ->where('owner_id', $ownerId)
                ->where('last_updated', '>=', $restockCreated)
                ->orderByDesc('last_updated')
                ->first();

            // Determine status
            if ($totalAdded == 0) {
                $status = 'pending';
                $lastStockDate = null;
            } elseif ($totalAdded < $item->item_quantity) {
                $status = 'in progress';
                $lastStockDate = $lastStock->last_updated ?? null;
            } else {
                $status = 'complete';
                $lastStockDate = $lastStock->last_updated ?? null;
            }

            // Update DB
            DB::table('restock_item')
                ->where('item_id', $item->item_id)
                ->update([
                    'item_status' => $status,
                    'item_restock_date' => $lastStockDate
                ]);

            // Update object for Blade rendering
            $item->item_status = $status;
            $item->item_restock_date = $lastStockDate;
        }


        // Update RESTOCK status
        foreach ($restocks as $restock) {

            if (in_array($restock->status, ['resolved', 'cancelled'])) {
                continue;
            }

            $items = $restockItems->where('restock_id', $restock->restock_id);

            if ($items->every(fn($i) => $i->item_status === 'complete')) {

                DB::table('restock')
                    ->where('restock_id', $restock->restock_id)
                    ->update(['status' => 'resolved']);

                $restock->status = 'resolved';
            }
        }

        return view('dashboards.owner.restock_list', compact('restocks', 'restockItems', 'isStaff'));
    }

   


    public function updateStatus(Request $request)
    {
        $request->validate([
            'restock_id' => 'required|integer',
            'status' => 'required|in:received,cancelled'
        ]);

        $restockId = $request->restock_id;
        $status = $request->status;

        // Update restock status
        DB::table('restock')
            ->where('restock_id', $restockId)
            ->update(['status' => $status]);

        // If cancelled, update all related restock items to cancelled
        if ($status === 'cancelled') {
            DB::table('restock_item')
                ->where('restock_id', $restockId)
                ->update(['item_status' => 'cancelled']);
        }

        ActivityLogController::log(
            "Marked restock list #{$restockId} as {$status}",
            'owner',
            auth('owner')->user(),
            $request->ip()
        );

        return back()->with('success', 'Restock status updated!');
    }



    public function topProducts(Request $request)
    {
        // Detect whether the user is owner or staff
        $isStaff = false;
        $ownerId = null;

        // Owner logged in
        if (Auth::guard('owner')->check()) {
            $ownerId = Auth::guard('owner')->id();
            $isStaff = false;

            // Staff logged in
        } elseif (Auth::guard('staff')->check()) {
            $ownerId = Auth::guard('staff')->user()->owner_id;
            $isStaff = true;

            // No valid user
        } else {
            abort(403, 'Unauthorized access');
        }

        $categoryId = $request->input('category_id');
        $topN       = $request->input('top_n', 20);

        $now          = Carbon::now();
        $currentMonth = $now->month;
        $currentYear  = $now->year;

        // Analyze same month from past 3 years
        $years = [$currentYear - 1, $currentYear - 2, $currentYear - 3];

        // --------------------------------------
        // SAME-MONTH PAST SALES (PAST 3 YEARS)
        // --------------------------------------
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

        // --------------------------------------
        // PAST AVERAGE SALES (SAME MONTH AVG)
        // --------------------------------------
        $pastAverages = $pastSales
            ->groupBy('prod_code')
            ->map(function ($group) {
                $avg   = $group->avg('sold');
                $first = $group->first();
                return (object)[
                    'prod_code'    => $first->prod_code,
                    'name'         => $first->name,
                    'prod_image'   => $first->prod_image,
                    'average_past' => (int) round($avg),
                ];
            });

        // --------------------------------------
        // CURRENT MONTH SALES
        // --------------------------------------
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

        // --------------------------------------
        // FULL MONTHLY SALES (TS SERIES)
        // --------------------------------------
        $monthlySales = DB::table('receipt_item as ri')
            ->join('receipt as r', 'ri.receipt_id', '=', 'r.receipt_id')
            ->join('products as p', 'ri.prod_code', '=', 'p.prod_code')
            ->when($categoryId, fn($q) => $q->where('p.category_id', $categoryId))
            ->where('p.owner_id', $ownerId)
            ->select(
                'p.prod_code',
                DB::raw('YEAR(r.receipt_date) as year'),
                DB::raw('MONTH(r.receipt_date) as month'),
                DB::raw('SUM(ri.item_quantity) as qty')
            )
            ->groupBy('p.prod_code', 'year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // --------------------------------------
        //  ACTIVE PRODUCTS
        // --------------------------------------
        $allProducts = DB::table('products')
            ->where('owner_id', $ownerId)
            ->where('prod_status', 'active')
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->get();

        // Use Laravel service container
        $forecastService = app(\App\Services\ForecastService::class);

        // --------------------------------------
        // BUILD FORECASTED METRICS
        // --------------------------------------
        $topProducts = $allProducts->map(function ($product) use ($pastAverages, $currentSales, $monthlySales, $forecastService) {

            $pastObj    = $pastAverages->get($product->prod_code);
            $currentObj = $currentSales->get($product->prod_code);

            $past    = $pastObj?->average_past ?? 0;
            $current = $currentObj?->total_sold ?? 0;

            // Build time series for this product
            $series = $monthlySales
                ->where('prod_code', $product->prod_code)
                ->sortBy(fn($r) => $r->year . '-' . str_pad($r->month, 2, '0', STR_PAD_LEFT))
                ->pluck('qty')
                ->map(fn($v) => (float) $v)
                ->values()
                ->toArray();

            $forecastValue = null;

            // SES / Holt-Winters PHP forecasting
            if (count($series) >= 3) {
                $forecastResult  = $forecastService->forecast($series);
                $forecastValue   = $forecastResult['forecast'] ?? null;
                $forecastAlgo    = $forecastResult['algorithm'] ?? 'SES/Holt-Winters';
            }

            // Fallback logic if forecast failed
            if ($forecastValue === null) {
                if ($past > 0 && $current > 0) {
                    $forecastValue = 0.5 * $current + 0.5 * $past;
                    $forecastAlgo  = 'Fallback: 50/50 past+current';
                } elseif ($past > 0 && $current == 0) {
                    $forecastValue = $past;
                    $forecastAlgo  = 'Fallback: past only';
                } elseif ($past == 0 && $current > 0) {
                    $forecastValue = 1.10 * $current;
                    $forecastAlgo  = 'Fallback: current only +10%';
                } else {
                    $forecastValue = 0;
                    $forecastAlgo  = 'Fallback: zero';
                }
            }

            // LOG info for seasonal trends only
            if ($past > 0) { // seasonal trend
                Log::info('Seasonal Trend Forecast', [
                    'prod_code'       => $product->prod_code,
                    'name'            => $product->name,
                    'time_series'     => $series,
                    'forecast_value'  => $forecastValue,
                    'forecast_algo'   => $forecastAlgo,
                ]);
            }

            // Growth %
            if ($past > 0) {
                $growth = round((($forecastValue - $past) / $past) * 100, 2);
            } elseif ($current > 0) {
                $growth = 100;  // newly trending
            } else {
                $growth = 0;
            }

            return (object)[
                'prod_code'          => $product->prod_code,
                'name'               => $product->name,
                'prod_image'         => $product->prod_image,
                'average_past'       => (int) $past,
                'current_month_sold' => (int) $current,
                'forecasted_demand'  => (int) round($forecastValue),
                'growth_rate'        => $growth,
            ];
        });

        // --------------------------------------
        // 7️⃣ REMOVE NEVER-SOLD PRODUCTS
        // --------------------------------------
        $topProducts = $topProducts->filter(function ($p) {
            return $p->current_month_sold > 0 || $p->average_past > 0;
        });

        // --------------------------------------
        // 8️⃣ SPLIT SEASONAL vs NEW TRENDING
        // --------------------------------------
        $seasonalTrends = $topProducts
            ->filter(fn($p) => $p->average_past > 0)
            ->sortByDesc('forecasted_demand')
            ->take($topN)
            ->values();

        $newTrending = $topProducts
            ->filter(fn($p) => $p->average_past == 0 && $p->current_month_sold > 0)
            ->sortByDesc('forecasted_demand')
            ->values();

        // --------------------------------------
        // 9️⃣ FETCH CATEGORIES
        // --------------------------------------
        $categories = DB::table('categories')
            ->where('owner_id', $ownerId)
            ->get();

        // --------------------------------------
        // 🔟 RENDER VIEW
        // --------------------------------------
        return view('dashboards.owner.seasonal_trends', [
            'topProducts' => $seasonalTrends,
            'newTrending' => $newTrending,
            'categories'  => $categories,
            'categoryId'  => $categoryId,
            'topN'        => $topN,
            'isStaff' => $isStaff
        ]);
    }
}
