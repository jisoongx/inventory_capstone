<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RestockController extends Controller
{
    public function lowStock()
    {
        // Get the logged-in owner's ID
        $ownerId = Auth::guard('owner')->id(); // adjust guard if needed

        // Query inventory and join with products
        $products = DB::table('inventory')
            ->join('products', 'inventory.prod_code', '=', 'products.prod_code')
            ->where('inventory.owner_id', $ownerId)
            ->where('inventory.stock', '<=', 100)
            ->select(
                'inventory.inven_code',     // ✅ add this
                'products.prod_code',       // ✅ keep product code too
                'products.name',
                'products.cost_price',
                'products.selling_price',
                'products.description',
                'inventory.stock',
                'inventory.stock_limit',
                'inventory.batch_number'
            )
            ->get();

        return view('dashboards.owner.restock_suggestion', compact('products'));
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
        $ownerId = auth()->guard('owner')->id();
        $categoryId = $request->input('category_id'); // optional filter from dropdown

        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();

<<<<<<< Updated upstream
        $lastYear = $now->copy()->subYear();
        $lastYearMonthStart = $lastYear->copy()->startOfMonth();
        $lastYearMonthEnd = $lastYear->copy()->endOfMonth();
=======
        // Last year same month
        $lastYearMonthStart = $now->copy()->subYear()->startOfMonth();
        $lastYearMonthEnd = $now->copy()->subYear()->endOfMonth();
>>>>>>> Stashed changes

        // Current month sales
        $currentMonthQuery = DB::table('receipt_item')
            ->select(
                'receipt_item.prod_code',
                'products.name',
                DB::raw('SUM(receipt_item.item_quantity) as current_month_sold')
            )
            ->join('receipt', 'receipt_item.receipt_id', '=', 'receipt.receipt_id')
            ->join('products', 'receipt_item.prod_code', '=', 'products.prod_code')
            ->where('receipt.owner_id', $ownerId)
            ->whereBetween('receipt.receipt_date', [$currentMonthStart, $currentMonthEnd]);

        if ($categoryId) {
            $currentMonthQuery->where('products.category_id', $categoryId);
        }

        $currentMonthQuery->groupBy('receipt_item.prod_code', 'products.name');

        $currentMonthSales = DB::table(DB::raw("({$currentMonthQuery->toSql()}) as cm"))
            ->mergeBindings($currentMonthQuery)
            ->select('cm.prod_code', 'cm.name', 'cm.current_month_sold');

        // Last year same month sales
        $lastYearQuery = DB::table('receipt_item')
            ->select(
                'receipt_item.prod_code',
                DB::raw('SUM(receipt_item.item_quantity) as last_year_sold')
            )
            ->join('receipt', 'receipt_item.receipt_id', '=', 'receipt.receipt_id')
            ->join('products', 'receipt_item.prod_code', '=', 'products.prod_code')
            ->where('receipt.owner_id', $ownerId)
            ->whereBetween('receipt.receipt_date', [$lastYearMonthStart, $lastYearMonthEnd]);

        if ($categoryId) {
            $lastYearQuery->where('products.category_id', $categoryId);
        }

        $lastYearQuery->groupBy('receipt_item.prod_code');

        // Merge current and last year sales
        $merged = DB::table(DB::raw("({$currentMonthSales->toSql()}) as curr"))
            ->mergeBindings($currentMonthSales)
            ->leftJoin(DB::raw("({$lastYearQuery->toSql()}) as ly"), 'curr.prod_code', '=', 'ly.prod_code')
            ->mergeBindings($lastYearQuery)
<<<<<<< Updated upstream
            ->select('curr.prod_code', 'curr.name', 'curr.current_month_sold', DB::raw('COALESCE(ly.last_year_sold, 0) as last_year_sold'))
=======
            ->select(
                'curr.prod_code',
                'curr.name',
                'curr.current_month_sold',
                DB::raw('COALESCE(ly.last_year_sold, 0) as last_year_sold')
            )
>>>>>>> Stashed changes
            ->orderByDesc('curr.current_month_sold')
            ->limit(10)
            ->get();

        // Map to add growth_rate and expected_demand
        $topProducts = $merged->map(function ($item) {
            $current = $item->current_month_sold;
            $last = $item->last_year_sold;

<<<<<<< Updated upstream
            // Growth rate calculation (%)
            $growth = $last > 0 ? (($current - $last) / $last) * 100 : 100;

            // Expected demand (simple forecast: current + growth difference)
=======
            // Growth rate (%)
            $growth = $last > 0 ? (($current - $last) / $last) * 100 : 100;

            // Expected demand (simple forecast)
>>>>>>> Stashed changes
            $expected = $current + ($current - $last);

            return (object) [
                'prod_code' => $item->prod_code,
                'name' => $item->name,
                'current_month_sold' => $current,
                'last_year_sold' => $last,
                'growth_rate' => round($growth, 2),
                'expected_demand' => round($expected, 0),
            ];
        });

        // Get categories for dropdown
        $categories = DB::table('categories')->get();

        return view('dashboards.owner.seasonal_trends', [
            'topProducts' => $topProducts,
            'categories' => $categories,
            'categoryId' => $categoryId,
        ]);
    }
}
