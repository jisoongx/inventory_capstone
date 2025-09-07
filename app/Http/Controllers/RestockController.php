<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RestockController extends Controller
{
    public function restockSuggestion(Request $request)
    {
        $ownerId = Auth::guard('owner')->id();
        $days = (int) $request->input('days', 90);
        $startDate = now()->subDays($days)->toDateString();

        $products = DB::table('products')
            ->join('inventory', 'products.prod_code', '=', 'inventory.prod_code')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->leftJoin(DB::raw("(
            SELECT
                ri.prod_code,
                SUM(ri.item_quantity) as total_sold,
                COUNT(DISTINCT r.receipt_id) as order_count
            FROM receipt_item ri
            INNER JOIN receipt r ON ri.receipt_id = r.receipt_id
            WHERE r.receipt_date >= '{$startDate}'
            GROUP BY ri.prod_code
        ) as sales"), 'products.prod_code', '=', 'sales.prod_code')
            ->where('products.owner_id', $ownerId)
            ->whereNotNull('sales.total_sold')
            ->select(
                'inventory.inven_code',
                'products.prod_code',
                'products.name',
                'products.cost_price',
                'products.selling_price',
                'products.description',
                'inventory.stock',
                'products.stock_limit',
                'inventory.batch_number',
                'categories.category',
                DB::raw('COALESCE(sales.total_sold, 0) as total_sold'),
                DB::raw('COALESCE(sales.order_count, 0) as order_count'),
                DB::raw("ROUND(SQRT((2 * COALESCE(sales.total_sold, 0) * products.cost_price) / 1)) as eoq")
            )
            ->orderByDesc('sales.total_sold')
            ->get()
            ->map(function ($product) use ($days) {

                $avgDailySales = $product->total_sold / $days;

                // Dynamic lead time based on sales velocity
                if ($avgDailySales >= 10) {           // very fast-moving
                    $leadTime = 1;
                } elseif ($avgDailySales >= 5) {      // medium-moving
                    $leadTime = 2;
                } elseif ($avgDailySales >= 2) {      // slow-medium
                    $leadTime = 3;
                } else {                              // slow-moving
                    $leadTime = 5;
                }

                $reorderPoint = round($avgDailySales * $leadTime);

                $product->lead_time_days = $leadTime;
                $product->reorder_point = $reorderPoint;
                $product->suggested_quantity = ($product->stock < $reorderPoint)
                    ? round(sqrt((2 * $product->total_sold * $product->cost_price) / 1))
                    : 0;

                return $product;
            });

        return view('dashboards.owner.restock_suggestion', compact('products', 'days'));
    }





    public function finalize(Request $request)
    {
        $ownerId = auth()->guard('owner')->id();

        // Create restock header
        $restockId = DB::table('restock')->insertGetId([
            'owner_id' => $ownerId,
            'restock_created' => now(),
        ]);

        // Loop through selected products
        foreach ($request->products as $invenCode) {
            DB::table('restock_item')->insert([
                'restock_id'    => $restockId,
                'inven_code'    => $invenCode,  // ✅ matches Blade
                'item_quantity' => $request->quantities[$invenCode] ?? 0,
                'item_priority' => $request->priorities[$invenCode] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'Restock list finalized successfully!');
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
            ->select('restock_item.*', 'products.name')
            ->orderByDesc('restock_item.restock_id')
            ->get();

        return view('dashboards.owner.restock_list', compact('restocks', 'restockItems'));
    }

    public function topProducts(Request $request)
    {
        $ownerId = Auth::guard('owner')->id();
        $categoryId = $request->input('category_id');

        // Owner can choose top N products (default 15)
        $topN = $request->input('top_n', 15);

        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Past years (exclude current year)
        $years = DB::table('receipt')
            ->whereYear('receipt_date', '<', $currentYear)
            ->distinct()
            ->pluck(DB::raw('YEAR(receipt_date)'));

        // Get past years same month sales, aggregated per year
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
                DB::raw('SUM(ri.item_quantity) as sold'),
                DB::raw('YEAR(r.receipt_date) as year')
            )
            ->groupBy('p.prod_code', 'p.name', 'year')
            ->get();

        // Compute average per product
        $topProductsPast = $pastSales
            ->groupBy('prod_code')
            ->map(function ($group) {
                $avg = $group->avg('sold'); // average across years
                $first = $group->first();
                return (object)[
                    'prod_code' => $first->prod_code,
                    'name' => $first->name,
                    'average_past' => round($avg, 2)
                ];
            })
            ->sortByDesc('average_past')
            ->take($topN); // apply top N limit here

        // Current month sales
        $currentSales = DB::table('receipt_item as ri')
            ->join('receipt as r', 'ri.receipt_id', '=', 'r.receipt_id')
            ->join('products as p', 'ri.prod_code', '=', 'p.prod_code')
            ->whereYear('r.receipt_date', $currentYear)
            ->whereMonth('r.receipt_date', $currentMonth)
            ->where('p.owner_id', $ownerId)
            ->select('p.prod_code', DB::raw('SUM(ri.item_quantity) as total_sold'))
            ->groupBy('p.prod_code')
            ->pluck('total_sold', 'prod_code');

        // Combine current and past for final topProducts with forecast
        $topProducts = $topProductsPast->map(function ($product) use ($currentSales) {
            $current = $currentSales[$product->prod_code] ?? 0;

            // Growth rate
            $growth = $product->average_past > 0 ? (($current - $product->average_past) / $product->average_past) * 100 : 0;

            // Exponential smoothing: forecast = alpha*current + (1-alpha)*past_avg
            $alpha = 0.5; // can adjust smoothing factor
            $expectedDemand = round($alpha * $current + (1 - $alpha) * $product->average_past, 2);

            return (object)[
                'prod_code' => $product->prod_code,
                'name' => $product->name,
                'current_month_sold' => (int) $current,
                'last_year_sold' => $product->average_past,
                'growth_rate' => round($growth, 2),
                'expected_demand' => $expectedDemand
            ];
        });

        $categories = DB::table('categories')->get();

        return view('dashboards.owner.seasonal_trends', [
            'topProducts' => $topProducts,
            'categories' => $categories,
            'categoryId' => $categoryId,
            'topN' => $topN
        ]);
    }
}
