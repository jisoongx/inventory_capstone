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
        $latestYear = now()->year;
        $yearToUse = $selectedYear ?? $latestYear;
        
        
        $currentMonth = (int)date('n');
        $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        $tableMonths = range(0, ($currentMonth - 1));
        $tableMonthNames = array_slice($months, 0, $currentMonth);

        if (is_null($selectedYear)) {
            $months = array_slice($months, 0, $currentMonth);
            $allMonths = range(0, ($currentMonth - 1));
        } elseif ($selectedYear == $latestYear) {
            $months = array_slice($months, 0, $currentMonth);
            $allMonths = range(0, ($currentMonth - 1));
        } else {
            $allMonths = range(0, 11);
        }
        

        $netProfits = []; //compatative analysis - latest nga year
        $profits = [];  //sa graph ni


        $expenses = collect(DB::select("
            SELECT 
                m.month,
                IFNULL(e.expense_total, 0) AS expense_total
            FROM (
                SELECT 1 AS month UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION
                SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION
                SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
            ) m
            LEFT JOIN (
                SELECT 
                    MONTH(expense_created) AS month,
                    SUM(expense_amount) AS expense_total
                FROM expenses
                WHERE YEAR(expense_created) = ? AND owner_id = ?
                GROUP BY MONTH(expense_created)
            ) e ON m.month = e.month
            ORDER BY m.month
        ", [$latestYear, $owner_id]))->pluck('expense_total')->slice(0, $currentMonth)->toArray();

        $losses = collect(DB::select("
            SELECT 
                m.month,
                IFNULL(l.total_loss, 0) AS total_loss
            FROM (
                SELECT 1 AS month UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION
                SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION
                SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
            ) m
            LEFT JOIN (
                SELECT 
                    MONTH(d.damaged_date) AS month,
                    SUM(d.damaged_quantity * p.selling_price) AS total_loss
                FROM damaged_items d
                JOIN products p ON d.prod_code = p.prod_code
                WHERE d.owner_id = ? AND YEAR(d.damaged_date) = ?
                GROUP BY MONTH(d.damaged_date)
            ) l ON m.month = l.month
            ORDER BY m.month
        ", [$owner_id, $latestYear]))->pluck('total_loss')->slice(0, $currentMonth)->toArray();
     
        $sales = collect(DB::select("
            SELECT 
                m.month,
                IFNULL(s.monthly_sales, 0) AS monthly_sales
            FROM (
                SELECT 1 AS month UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION
                SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION
                SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
            ) m
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
            ) s ON m.month = s.month
            ORDER BY m.month
        ", [$owner_id, $latestYear]))->pluck('monthly_sales')->slice(0, $currentMonth)->toArray();

        foreach ($tableMonths as $month) {
            $sale     = $sales[$month]    ?? null;
            $expense  = $expenses[$month] ?? null;
            $loss     = $losses[$month]   ?? null;

            $netProfits[$month] = $sale - ($expense + $loss);
        }



        $GraphExpenses = collect(DB::select("
            SELECT 
                m.month,
                IFNULL(e.expense_total, 0) AS expense_total
            FROM (
                SELECT 1 AS month UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION
                SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION
                SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
            ) m
            LEFT JOIN (
                SELECT 
                    MONTH(expense_created) AS month,
                    SUM(expense_amount) AS expense_total
                FROM expenses
                WHERE YEAR(expense_created) = ? AND owner_id = ?
                GROUP BY MONTH(expense_created)
            ) e ON m.month = e.month
            ORDER BY m.month
        ", [$yearToUse, $owner_id]))->pluck('expense_total')->toArray();

        $GraphLosses = collect(DB::select("
            SELECT 
                m.month,
                IFNULL(l.total_loss, 0) AS total_loss
            FROM (
                SELECT 1 AS month UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION
                SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION
                SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
            ) m
            LEFT JOIN (
                SELECT 
                    MONTH(d.damaged_date) AS month,
                    SUM(d.damaged_quantity * p.selling_price) AS total_loss
                FROM damaged_items d
                JOIN products p ON d.prod_code = p.prod_code
                WHERE d.owner_id = ? AND YEAR(d.damaged_date) = ?
                GROUP BY MONTH(d.damaged_date)
            ) l ON m.month = l.month
            ORDER BY m.month
        ", [$owner_id, $yearToUse]))->pluck('total_loss')->toArray();
     
        $GraphSales = collect(DB::select("
            SELECT 
                m.month,
                IFNULL(s.monthly_sales, 0) AS monthly_sales
            FROM (
                SELECT 1 AS month UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION
                SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION
                SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
            ) m
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
            ) s ON m.month = s.month
            ORDER BY m.month
        ", [$owner_id, $yearToUse]))->pluck('monthly_sales')->toArray();

        foreach ($allMonths as $month) {
            $Gsale     = $GraphSales[$month]    ?? null;
            $Gexpense  = $GraphExpenses[$month] ?? null;
            $Gloss     = $GraphLosses[$month]   ?? null;

            $profits[$month] = $Gsale - ($Gexpense + $Gloss);
        }


        $profitMonth = $netProfits[$currentMonth - 1] ?? 0;



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


        $categories = collect(DB::select(
            "select category from categories"
        ))->pluck('category')->toArray();


        $year = collect(DB::select("
            SELECT DISTINCT YEAR(expense_created) AS year
            FROM expenses
            WHERE expense_created IS NOT NULL and owner_id = ?
            ORDER BY year DESC
        ", [$owner_id]))->pluck('year')->toArray();

        $dateDisplay = Carbon::now('Asia/Manila');

        $day = now()->day;

        $dailySales = collect(DB::select("
            select ifnull(sum(p.selling_price * ri.item_quantity), 0) as dailySales
            from receipt r
            join receipt_item ri on r.receipt_id = ri.receipt_id
            join products p on ri.prod_code = p.prod_code
            where day(receipt_date) = ?
            and r.owner_id = ?
        ", [$day, $owner_id]))->first();

        $weeklySales = collect(DB::select("
            SELECT IFNULL(SUM(p.selling_price * ri.item_quantity), 0) AS weeklySales
            FROM receipt r
            JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
            JOIN products p ON ri.prod_code = p.prod_code
            WHERE r.receipt_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
            AND r.owner_id = ?;
        ", [$owner_id]))->first();

        $monthSales = collect(DB::select("
            SELECT IFNULL(SUM(p.selling_price * ri.item_quantity), 0) AS monthSales
            FROM receipt r
            JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
            join products p on ri.prod_code = p.prod_code
            where month(receipt_date) = ?
            AND r.owner_id = ?;
        ", [$currentMonth, $owner_id]))->first();





        return view('dashboards.owner.dashboard', [
            'owner_name' => $owner_name,
            'months' => $months,
            'profits' => $profits,
            'year' => $year,
            'expenses' => $expenses,
            'dateDisplay' => $dateDisplay,
            'profitMonth' => $profitMonth,
            'losses' => $losses,
            'sales' => $sales,
            'netprofits' => $netProfits,
            'products' => $productData,
            'productsPrev' => $productPrevData,
            'categories' => $categories,
            'currentMonth' => $currentMonth,
            'latestYear' => $latestYear,
            'tableMonthNames' => $tableMonthNames,
            'dailySales' => $dailySales,
            'weeklySales' => $weeklySales,
            'monthSales' => $monthSales,
        ]);
    }
}


//wa pa nahuman ang comparative matrix