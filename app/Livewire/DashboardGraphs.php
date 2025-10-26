<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardGraphs extends Component
{
    public $dateDisplay;
    public $day;
    public $year;
    public $owner_name;
    public $months;

    public $dailySales;
    public $weeklySales;
    public $monthSales;

    public $categories = [];
    public $products = [];
    public $productsAve = [];
    public $productsPrev = [];

    public $sales;
    public $losses;
    public $salesPercentage;
    public $lossPercentage;
    public $salesInsights;
    public $salesState;
    public $lossInsights;
    public $lossState;
    public $insight;
    public $performanceLabel;
    public $selectedYear;


    public function currencySales() {

        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;
        $this->owner_name = $owner->firstname;

        $this->year = collect(DB::select("
            SELECT DISTINCT YEAR(expense_created) AS year
            FROM expenses
            WHERE expense_created IS NOT NULL and owner_id = ?
            ORDER BY year DESC
        ", [$owner_id]))->pluck('year')->toArray();

        $this->dateDisplay = Carbon::now('Asia/Manila');
        $this->day = now()->format('Y-m-d');
        $currentMonth = (int) date('m');
        $latestYear = now()->year;
        $this->selectedYear = now()->year;

        $this->dailySales = collect(DB::select('
            select ifnull(sum(p.selling_price * ri.item_quantity), 0) as dailySales
            from receipt r
            join receipt_item ri on r.receipt_id = ri.receipt_id
            join products p on ri.prod_code = p.prod_code
            where date(receipt_date) = ?
            and r.owner_id = ?
        ', [$this->day, $owner_id]))->first();

        $this->weeklySales = collect(DB::select('
            SELECT IFNULL(SUM(p.selling_price * ri.item_quantity), 0) AS weeklySales
            FROM receipt r
            JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
            JOIN products p ON ri.prod_code = p.prod_code
            WHERE r.receipt_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
            AND r.owner_id = ?
        ', [$owner_id]))->first();

        $this->monthSales = collect(DB::select('
            SELECT IFNULL(SUM(p.selling_price * ri.item_quantity), 0) AS monthSales
            FROM receipt r
            JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
            join products p on ri.prod_code = p.prod_code
            where month(receipt_date) = ?
            AND r.owner_id = ?
            AND year(receipt_date) = ?
        ', [$currentMonth, $owner_id, $latestYear]))->first();

    }




    public function salesByCategory() {

        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;
        $this->owner_name = $owner->firstname;

        $latestYear = now()->year;        
        $currentMonth = (int) date('m');


        $productCategory = collect(DB::select("
            SELECT
            c.category,
            COALESCE(
                SUM(
                CASE 
                    WHEN YEAR(r.receipt_date) = ? AND r.owner_id = ?
                    THEN p.selling_price * ri.item_quantity
                    ELSE 0
                END
                ), 0
            ) AS total_amount,
            c.category_id
            FROM categories c
            LEFT JOIN products p ON c.category_id = p.category_id
            LEFT JOIN receipt_item ri ON p.prod_code = ri.prod_code
            LEFT JOIN receipt r ON ri.receipt_id = r.receipt_id
            WHERE c.owner_id = ?
            GROUP BY c.category_id, c.category
            ORDER BY c.category_id;
        ", [
            $latestYear, $owner_id, $owner_id
        ]))->toArray();
        $this->categories = array_map(fn($row) => $row->category, $productCategory);
        $this->products = array_map(fn($row) => (float) $row->total_amount , $productCategory);

        $productCategoryPrev = collect(DB::select("
            SELECT
            c.category,
            COALESCE(
                SUM(
                CASE 
                    WHEN YEAR(r.receipt_date) = ? AND r.owner_id = ?
                    THEN p.selling_price * ri.item_quantity
                    ELSE 0
                END
                ), 0
            ) AS total_amount,
            c.category_id
            FROM categories c
            LEFT JOIN products p ON c.category_id = p.category_id
            LEFT JOIN receipt_item ri ON p.prod_code = ri.prod_code
            LEFT JOIN receipt r ON ri.receipt_id = r.receipt_id
            WHERE c.owner_id = ?
            GROUP BY c.category_id, c.category
            ORDER BY c.category_id;
        ", [
            $latestYear-1, $owner_id, $owner_id
        ]))->toArray();
        $this->productsPrev = array_map(fn($row) => (float) $row->total_amount, $productCategoryPrev);

        $productCategoryAve = collect(DB::select("
            SELECT 
                c.category,
                COALESCE(ROUND(AVG(t.year_total), 2), 0) AS avg_total_sales
            FROM categories c
            LEFT JOIN (
                SELECT
                    p.category_id,
                    YEAR(r.receipt_date) AS year,
                    SUM(p.selling_price * ri.item_quantity) AS year_total
                FROM products p
                JOIN receipt_item ri ON p.prod_code = ri.prod_code
                JOIN receipt r ON ri.receipt_id = r.receipt_id
                WHERE r.owner_id = ?
                GROUP BY p.category_id, YEAR(r.receipt_date)
            ) AS t ON c.category_id = t.category_id
            WHERE c.owner_id = ?
            GROUP BY c.category_id, c.category
            ORDER BY c.category_id
        ", [$owner_id, $owner_id]))->toArray();
        $this->productsAve = array_map(fn($row) => (float) $row->avg_total_sales, $productCategoryAve);

        $this->dispatch('chart-updated');

    }



    public function salesVSloss() {

        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;

        $latestYear = now()->year;        
        $currentMonth = (int) date('m');

        $this->losses = collect(DB::select("
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
     
        $this->sales = collect(DB::select("
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

        $latestSales = end($this->sales) ?: 0;
        $latestLoss = end($this->losses) ?: 0;
        $previousSales = count($this->sales) > 1 ? $this->sales[count($this->sales) - 2] : 0;
        $previousLoss = count($this->losses) > 1 ? $this->losses[count($this->losses) - 2] : 0;

        // PARA NI DAPIT WHERE GI FETCH TANAN EXCEPT SA LAST
        $previousSalesAll = array_slice($this->sales, 0, -1);
        $previousLossAll = array_slice($this->losses, 0, -1);

        $totalActivity = $latestSales + $latestLoss; 
        $totalPrevActivity = $previousSales + $previousLoss;

        $this->salesPercentage = $totalActivity > 0 ? round(($latestSales / $totalActivity) * 100, 1) : 0;
        $this->lossPercentage = $totalActivity > 0 ? round(($latestLoss / $totalActivity) * 100, 1) : 0;
        $salesPrevPercentage = $totalPrevActivity > 0 ? round(($previousSales / $totalPrevActivity) * 100, 1) : 0;
        $lossPrevPercentage = $totalPrevActivity > 0 ? round(($previousLoss / $totalPrevActivity) * 100, 1) : 0;


        $diffSales = $this->salesPercentage - $salesPrevPercentage;
        $diffLoss = $this->lossPercentage - $lossPrevPercentage;

        if(array_sum($previousSalesAll)==0) {
            $this->salesInsights = "This is your baseline month. Future sales comparisons will be based on this data.";
            $this->salesState = 'Start';
            $this->lossInsights = "This is your baseline month. Future loss comparisons will be based on this data.";
            $this->lossState = 'Start';

        } else {
            if($diffSales > 0) {
                $this->salesInsights = "Compared to last month, sales improved by " . number_format(abs($diffSales), 1) . "%.";
                $this->salesState = 'Positive';
                
            } elseif ($diffSales < 0) {
                $this->salesInsights = "Compared to last month, sales decreased by " . number_format(abs($diffSales), 1) . "%.";
                $this->salesState = 'Negative';

            } else {
                $this->salesInsights = "Sales remained consistent at " . number_format($this->salesPercentage, 1) . "%.";
                $this->salesState = 'Stable';
            }

            if($diffLoss > 0) {
                $this->lossInsights = "Compared to last month, loss increased by " . number_format(abs($diffLoss), 1) . "%.";
                $this->lossState = 'Negative';

            } elseif ($diffLoss < 0) {
                $this->lossInsights = "Compared to last month, loss decreased by " . number_format(abs($diffLoss), 1) . "%.";
                $this->lossState = 'Positive';

            } else {
                if($diffLoss < 0){
                    $this->lossInsights = "Loss remained steady at " . number_format($this->lossPercentage, 1) . "%.";
                    $this->lossState = 'Warning';  

                }else {
                    $this->lossInsights = "Good jub! Loss remained steady at " . number_format($this->lossPercentage, 1) . "%.";
                    $this->lossState = 'Stagnant';
                }
            }
        }

                
        if ($this->lossPercentage < 2) {
            $this->insight = "Good job! Strong sales with almost no losses.";
            $this->performanceLabel = "Excellent";

        } elseif ($this->lossPercentage < 5) {
            $this->insight = "Healthy performance. Sales are strong and losses are well-controlled.";
            $this->performanceLabel = "Good";

        } elseif ($this->lossPercentage < 10) {
            $this->insight = "Fair. Some losses are noticeable, monitor stock and expiries.";
            $this->performanceLabel = "Moderate";

        } elseif ($this->lossPercentage < 18) {
            $this->insight = "Losses are cutting into profits. Review inventory handling.";
            $this->performanceLabel = "Warning";

        } elseif ($this->lossPercentage < 25) {
            $this->insight = "Losses are significantly impacting sales. Take corrective action.";
            $this->performanceLabel = "Critical";

        } else {
            $this->insight = "Very high losses are severely affecting performance. Act immediately!";
            $this->performanceLabel = "Critical";
        }

    }

    public function pollAll() {
        $this->salesByCategory();
        $this->salesVSloss();
        $this->currencySales();
    }

    public function render()
    {
        $this->pollAll();
        return view('livewire.dashboard-graphs');
    }
}