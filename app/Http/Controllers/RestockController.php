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

    public function restockSuggestion(Request $request)
    {
        $ownerId = Auth::guard('owner')->id();
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // 1️⃣ Categories (owner-based)
        $categories = DB::table('categories')
            ->where('owner_id', $ownerId)
            ->get();

        // 2️⃣ Products + sales
        $products = DB::table('products')
            ->join('inventory', 'products.prod_code', '=', 'inventory.prod_code')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->leftJoin(DB::raw("
            (
                SELECT 
                    ri.prod_code,
                    SUM(
                        CASE 
                            WHEN YEAR(r.receipt_date) = {$currentYear} 
                            AND MONTH(r.receipt_date) = {$currentMonth} 
                            THEN ri.item_quantity 
                            ELSE 0 
                        END
                    ) AS sold_this_month,
                    SUM(
                        CASE 
                            WHEN YEAR(r.receipt_date) = {$currentYear} 
                            THEN ri.item_quantity 
                            ELSE 0 
                        END
                    ) AS sold_this_year
                FROM receipt_item ri
                INNER JOIN receipt r ON ri.receipt_id = r.receipt_id
                GROUP BY ri.prod_code
            ) AS sales
        "), 'products.prod_code', '=', 'sales.prod_code')
            ->where('products.owner_id', $ownerId)
            ->select(
                'inventory.inven_code',
                'products.prod_code',
                'products.name',
                'categories.category',
                'products.category_id',
                'products.cost_price',
                'products.selling_price',
                'inventory.stock',
                'products.stock_limit',
                DB::raw('COALESCE(sales.sold_this_month, 0) as sold_this_month'),
                DB::raw('COALESCE(sales.sold_this_year, 0) as sold_this_year')
            )
            ->get()
            ->map(function ($product) {
                // 3️⃣ Suggested quantity logic
                $suggestedQty = max(($product->stock_limit * 2) - $product->stock, 0);

                // 4️⃣ Determine reason (Low Stock / Top Selling)
                $reason = null;

                if ($product->stock <= $product->stock_limit) {
                    $reason = '⚠️ Low Stock';
                }

                if ($product->sold_this_month >= 20) {
                    $reason = $reason ? $reason . ' + 🚀 Top Selling' : '🚀 Top Selling';
                }

                $product->suggested_quantity = $suggestedQty;
                $product->reason = $reason ?? '✅ Normal Stock';

                // 5️⃣ Optional: color badge logic
                if (str_contains($product->reason, '⚠️')) {
                    $product->reason_badge = 'background-color:#fef3c7;color:#92400e;';
                } elseif (str_contains($product->reason, '🚀')) {
                    $product->reason_badge = 'background-color:#dcfce7;color:#166534;';
                } else {
                    $product->reason_badge = 'background-color:#e2e8f0;color:#334155;';
                }

                return $product;
            })
            ->filter(fn($product) => $product->reason !== '✅ Normal Stock')
            ->sortByDesc('sold_this_month')
            ->values();

        return view('dashboards.owner.restock_suggestion', compact('products', 'categories', 'currentYear', 'currentMonth'));
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
