<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlyNetProfitGraph extends Component
{
    public $dateDisplay;
    public $day;
    public $year;
    public $owner_name;
    public $months;
    public $selectedYear;

    public $profits = [];  //sa graph ni
    public $profitMonth;

    
    public function mount()
    {
        $this->monthlyNetProfit();
        $this->fixMonthlyProfit();
    }


    public function updatedSelectedYear() {
        $this->monthlyNetProfit();
    }


    public function monthlyNetProfit() {

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;

        
        $this->dateDisplay = Carbon::now('Asia/Manila');

        $this->year = collect(DB::select("
            SELECT DISTINCT YEAR(receipt_date) AS year
            FROM receipt
            WHERE receipt_date IS NOT NULL and owner_id = ?
            ORDER BY year DESC
        ", [$owner_id]))->pluck('year')->toArray();


        $latestYear = now()->year;
        $yearToUse = $this->selectedYear ?? $latestYear;
        
        
        $currentMonth = (int) date('m');
        $this->months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        if (is_null($this->selectedYear)) {
            $this->months = array_slice($this->months, 0, $currentMonth);
            $allMonths = range(0, ($currentMonth - 1));
        } elseif ($this->selectedYear == $latestYear) {
            $this->months = array_slice($this->months, 0, $currentMonth);
            $allMonths = range(0, ($currentMonth - 1));
        } else {
            $allMonths = range(0, 11);
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
                Join inventory i on i.inven_code = d.inven_code
                JOIN products p ON i.prod_code = p.prod_code
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
                    SUM(ri.item_quantity * COALESCE(
                            (SELECT ph.old_selling_price
                            FROM pricing_history ph
                            WHERE ph.prod_code = ri.prod_code
                            AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                            ORDER BY ph.effective_from DESC
                            LIMIT 1),
                            p.selling_price
                        )) AS monthly_sales
                FROM receipt r
                JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
                JOIN products p ON p.prod_code = ri.prod_code
                WHERE r.owner_id = ? 
                AND p.owner_id = r.owner_id
                AND YEAR(r.receipt_date) = ?
                GROUP BY MONTH(r.receipt_date)
            ) s ON m.month = s.month
            ORDER BY m.month
        ", [$owner_id, $yearToUse]))->pluck('monthly_sales')->toArray();

        foreach ($allMonths as $month) {
            $Gsale     = $GraphSales[$month]    ?? null;
            $Gexpense  = $GraphExpenses[$month] ?? null;
            $Gloss     = $GraphLosses[$month]   ?? null;

            $this->profits[$month] = $Gsale - ($Gexpense + $Gloss);
        }

        // $this->profitMonth = $this->profits[$currentMonth - 1] ?? 0;

        $this->dispatch('chart-updated', [
            'profits' => array_values($this->profits),
            'months'  => $this->months
        ]);
    }

    public function fixMonthlyProfit() {

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;

        $latestYear = now()->year;
        
        
        $currentMonth = (int) date('m');
        $this->months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        if (is_null($this->selectedYear)) {
            $this->months = array_slice($this->months, 0, $currentMonth);
            $allMonths = range(0, ($currentMonth - 1));
        } elseif ($this->selectedYear == $latestYear) {
            $this->months = array_slice($this->months, 0, $currentMonth);
            $allMonths = range(0, ($currentMonth - 1));
        } else {
            $allMonths = range(0, 11);
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
        ", [$latestYear, $owner_id]))->pluck('expense_total')->toArray();

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
                Join inventory i on i.inven_code = d.inven_code
                JOIN products p ON i.prod_code = p.prod_code
                WHERE d.owner_id = ? AND YEAR(d.damaged_date) = ?
                GROUP BY MONTH(d.damaged_date)
            ) l ON m.month = l.month
            ORDER BY m.month
        ", [$owner_id, $latestYear]))->pluck('total_loss')->toArray();
     
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
                    SUM(ri.item_quantity * COALESCE(
                            (SELECT ph.old_selling_price
                            FROM pricing_history ph
                            WHERE ph.prod_code = ri.prod_code
                            AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                            ORDER BY ph.effective_from DESC
                            LIMIT 1),
                            p.selling_price
                        )) AS monthly_sales
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
        ", [$owner_id, $latestYear]))->pluck('monthly_sales')->toArray();

        foreach ($allMonths as $month) {
            $Gsale     = $GraphSales[$month]    ?? null;
            $Gexpense  = $GraphExpenses[$month] ?? null;
            $Gloss     = $GraphLosses[$month]   ?? null;

            $this->profits[$month] = $Gsale - ($Gexpense + $Gloss);
        }

        $this->profitMonth = $this->profits[$currentMonth - 1] ?? 0;
    }

    public function render()
    {   
        return view('livewire.monthly-net-profit-graph');
    }
}
