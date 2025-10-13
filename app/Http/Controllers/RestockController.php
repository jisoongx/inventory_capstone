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

        return $pdf->download('restock-' . $restockCreated . '.pdf');
    }

    public function restockSuggestion(Request $request)
    {
        $ownerId = Auth::guard('owner')->id();
        $currentYear = now()->year;

        // All products for custom restock dropdown
        $allProducts = DB::table('inventory')
            ->join('products', 'inventory.prod_code', '=', 'products.prod_code')
            ->where('inventory.owner_id', $ownerId)
            ->select(
                'inventory.inven_code',
                'products.name',
                'inventory.stock',
                'products.cost_price' // 👈 add this
            )
            ->get();


        // All categories for filter
        $categories = DB::table('categories')->get();

        // Products with suggested restock, excluding already finalized ones
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
            WHERE YEAR(r.receipt_date) = {$currentYear}
            GROUP BY ri.prod_code
        ) as sales"), 'products.prod_code', '=', 'sales.prod_code')
            ->leftJoin('restock_item as ri_finalized', 'inventory.inven_code', '=', 'ri_finalized.inven_code')
            ->leftJoin('restock as r_finalized', 'ri_finalized.restock_id', '=', 'r_finalized.restock_id')
            ->where('products.owner_id', $ownerId)
            ->whereNull('ri_finalized.item_id') // exclude products already finalized
            ->select(
                'inventory.inven_code',
                'products.category_id',
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
                DB::raw('COALESCE(sales.order_count, 0) as order_count')
            )
            ->get()
            ->map(function ($product) {

                // --- Average daily sales ---
                $avgDailySales = $product->total_sold / 365;

                // --- Dynamic lead time ---
                if ($avgDailySales >= 10) {
                    $leadTime = 1; // very fast-moving
                } elseif ($avgDailySales >= 5) {
                    $leadTime = 2; // medium-moving
                } elseif ($avgDailySales >= 2) {
                    $leadTime = 3; // slow-medium
                } else {
                    $leadTime = 5; // slow-moving
                }

                // --- Reorder point ---
                $reorderPoint = max(round($avgDailySales * $leadTime), 3);

                // --- EOQ suggested quantity ---
                $eoq = round(sqrt((2 * $product->total_sold * $product->cost_price) / 1));
                $suggestedQuantity = max($eoq - $product->stock, 0);

                // Attach computed values
                $product->lead_time_days = $leadTime;
                $product->reorder_point = $reorderPoint;
                $product->suggested_quantity = $suggestedQuantity;
                $product->eoq = $eoq;

                return $product;
            })
            ->filter(fn($product) => $product->suggested_quantity > 0) 
            ->sortByDesc('suggested_quantity')
            ->values();

        return view('dashboards.owner.restock_suggestion', compact('products', 'allProducts', 'currentYear', 'categories'));
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
        $topN = $request->input('top_n', 20); // default top 20

        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        //  same month sales from the past 3 years
        $years = [$currentYear - 1, $currentYear - 2, $currentYear - 3];

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

        //  Compute average sales (same month, past 3 years)
        $pastAverages = $pastSales
            ->groupBy('prod_code')
            ->map(function ($group) {
                $avg = $group->avg('sold');
                $first = $group->first();
                return (object)[
                    'prod_code' => $first->prod_code,
                    'name' => $first->name,
                    'prod_image' => $first->prod_image,
                    'average_past' => (int) round($avg)
                ];
            });

        // Current month sales
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

        //  Combine data
        $topProducts = $pastAverages->map(function ($product) use ($currentSales) {
            $currentData = $currentSales[$product->prod_code] ?? null;
            $current = $currentData->total_sold ?? 0;

            // Exponential smoothing (α = 0.5)
            $alpha = 0.5;
            $expectedDemand = round($alpha * $current + (1 - $alpha) * $product->average_past);

            // Growth rate
            $growth = $product->average_past > 0
                ? (($expectedDemand - $product->average_past) / $product->average_past) * 100
                : 0;

            return (object)[
                'prod_code' => $product->prod_code,
                'name' => $product->name,
                'prod_image' => $product->prod_image,
                'average_past' => (int) $product->average_past,
                'current_month_sold' => (int) $current,
                'forecasted_demand' => (int) round($expectedDemand),
                'growth_rate' => round($growth, 2)
            ];
        });

        // Sort by past 3-year average sales (highest to lowest)
        $topProducts = $topProducts
            ->sortByDesc('average_past')
            ->take($topN)
            ->values();

        $categories = DB::table('categories')->get();

        return view('dashboards.owner.seasonal_trends', [
            'topProducts' => $topProducts,
            'categories' => $categories,
            'categoryId' => $categoryId,
            'topN' => $topN
        ]);
    }
}
