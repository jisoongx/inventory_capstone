<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;
        $owner_name = $owner->firstname;

        $selectedYear = $request->input('year');

        // Get latest year from expenses
        $latestYear = collect(DB::select("
            SELECT MAX(YEAR(expense_created)) as latestYear FROM expenses
        "))->pluck('latestYear')->first();

        $latestYear = $latestYear ?? now()->year;

        $latestMonth = collect(DB::select("
            SELECT MAX(MONTH(expense_created)) as latestMonth FROM expenses WHERE expense_created = ?
        ", [$latestYear]))->pluck('latestMonth')->first();

        $yearToUse = $selectedYear ?? $latestYear;

        $profits = collect(DB::select("
            SELECT 
                m.month,
                m.monthly_sales,
                IFNULL(e.monthly_expenses, 0) AS monthly_expenses,
                (m.monthly_sales - IFNULL(e.monthly_expenses, 0)) AS net_profit
            FROM (
                SELECT 
                    MONTH(r.receipt_date) AS month,
                    SUM(p.selling_price * ri.item_quantity) AS monthly_sales
                FROM 
                    receipt r
                JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
                JOIN products p ON p.prod_code = ri.prod_code
                WHERE 
                    r.owner_id = ? AND
                    p.owner_id = r.owner_id AND
                    YEAR(r.receipt_date) = ?
                GROUP BY MONTH(r.receipt_date)
            ) m
            LEFT JOIN (
                SELECT 
                    MONTH(e.expense_created) AS month,
                    SUM(e.expense_amount) AS monthly_expenses
                FROM 
                    expenses e
                WHERE 
                    e.owner_id = ? AND
                    YEAR(e.expense_created) = ?
                GROUP BY MONTH(e.expense_created)
            ) e ON m.month = e.month
            ORDER BY m.month;
        ", [
            $owner_id, $yearToUse,
            $owner_id, $yearToUse  
        ]))->toArray();

        $profitData = array_map(fn($row) => (float) $row->net_profit, $profits);

        $productCategory = collect(DB::select("
            SELECT 
                p.prod_code, 
                SUM(p.selling_price * ri.item_quantity) AS total_amount, 
                p.category_id
            FROM products p 
            JOIN receipt_item ri ON p.prod_code = ri.prod_code
            JOIN receipt r ON ri.receipt_id = r.receipt_id
            WHERE YEAR(r.receipt_date) = ? AND r.owner_id = ?
            GROUP BY p.category_id, p.prod_code
        ", [
            $latestYear, $owner_id
        ]))->toArray();

        $productData = array_map(fn($row) => (float) $row->total_amount, $productCategory);

        $productCategoryPrev = collect(DB::select("
            SELECT 
                p.prod_code, 
                SUM(p.selling_price * ri.item_quantity) AS total_amount, 
                p.category_id
            FROM products p 
            JOIN receipt_item ri ON p.prod_code = ri.prod_code
            JOIN receipt r ON ri.receipt_id = r.receipt_id
            WHERE YEAR(r.receipt_date) = ? AND r.owner_id = ?
            GROUP BY p.category_id, p.prod_code
        ", [
            $latestYear-1,$owner_id 
        ]))->toArray();

        $productPrevData = array_map(fn($row) => (float) $row->total_amount, $productCategoryPrev);


        $profitMonth = collect(DB::select("
            SELECT 
                months.month,
                IFNULL(m.monthly_sales, 0) AS monthly_sales,
                IFNULL(e.monthly_expenses, 0) AS monthly_expenses,
                (IFNULL(m.monthly_sales, 0) - IFNULL(e.monthly_expenses, 0)) AS net_profit
            FROM (
                SELECT DISTINCT MONTH(date) AS month
                FROM (
                    SELECT receipt_date AS date FROM receipt WHERE owner_id = ? AND YEAR(receipt_date) = ?
                    UNION
                    SELECT expense_created AS date FROM expenses WHERE owner_id = ? AND YEAR(expense_created) = ?
                ) AS all_dates
            ) AS months
            LEFT JOIN (
                SELECT 
                    MONTH(r.receipt_date) AS month,
                    SUM(p.selling_price * ri.item_quantity) AS monthly_sales
                FROM 
                    receipt r
                JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
                JOIN products p ON p.prod_code = ri.prod_code
                WHERE 
                    r.owner_id = ? AND
                    p.owner_id = r.owner_id AND
                    YEAR(r.receipt_date) = ?
                GROUP BY MONTH(r.receipt_date)
            ) m ON months.month = m.month
            LEFT JOIN (
                SELECT 
                    MONTH(e.expense_created) AS month,
                    SUM(e.expense_amount) AS monthly_expenses
                FROM 
                    expenses e
                WHERE 
                    e.owner_id = ? AND
                    YEAR(e.expense_created) = ?
                GROUP BY MONTH(e.expense_created)
            ) e ON months.month = e.month
            ORDER BY months.month DESC;
        ", [
            $owner_id, $latestYear,  // 1 - receipt subquery
            $owner_id, $latestYear,  // 2 - expense subquery
            $owner_id, $latestYear,  // 3 - sales join
            $owner_id, $latestYear   // 4 - expense join
        ]))->first();


        $categories = collect(DB::select(
            "select category from categories"
        ))->pluck('category')->toArray();

        $months = collect(DB::select(
            "select distinct month(expense_created) as month_num, 
                            monthname(expense_created) as month_name
            from expenses
            where year(expense_created) = ? and owner_id = ?
            order by month_num", [$yearToUse, $owner_id]))->pluck('month_name')->toArray();

        $expenses = collect(DB::select(
            "select month(expense_created) as expense_month, sum(expense_amount) as expense_total
            from expenses
            where year(expense_created) = ? and owner_id = ?
            group by month(expense_created)
            order by month(expense_created)", [$yearToUse, $owner_id]))->pluck('expense_total')->toArray();

        $sales = collect(DB::select(
            "select month(receipt_date), sum(receipt_total) as sales_total
            from receipt
            where year(receipt_date) = ? and owner_id = ?
            group by month(receipt_date)
            order by month(receipt_date)", [$yearToUse, $owner_id]))->pluck('sales_total')->toArray(); 

        $year = collect(DB::select("
            SELECT DISTINCT YEAR(expense_created) AS year
            FROM expenses
            WHERE expense_created IS NOT NULL and owner_id = ?
            ORDER BY year DESC
        ", [$owner_id]))->pluck('year')->toArray();

        $dateDisplay = Carbon::now('Asia/Manila');

        return view('dashboards.owner.dashboard', [
            'owner_id' => $owner_id,
            'owner_name' => $owner_name,
            'months' => $months,
            'profits' => $profitData,
            'year' => $year,
            'expenses' => $expenses,
            'dateDisplay' => $dateDisplay,
            'profitMonth' => $profitMonth,
            'sales' => $sales,
            'products' => $productData,
            'productsPrev' => $productPrevData,
            'categories' => $categories,
        ]);
    }
}
