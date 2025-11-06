<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComparativeAnalysis extends Component
{
    public $netprofits = []; //compatative analysis - latest nga year
    public $expenses;
    public $losses;
    public $sales;

    public $selectedYear;
    public $yearToUse;
    public $latestYear;

    public $tableMonthNames;

    public function mount() {
        $this->comparativeAnalysis();
    }

    public function comparativeAnalysis() {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;
        
        $latestYear = now()->year;

        $currentMonth = (int) date('m');
        $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        $tableMonths = range(0, ($currentMonth - 1));
        $this->tableMonthNames = array_slice($months, 0, $currentMonth);

        $this->expenses = collect(DB::select("
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
                Join inventory i on i.inven_code = d.inven_code
                JOIN products p ON i.prod_code = p.prod_code
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

        foreach ($tableMonths as $month) {
            $sale     = $this->sales[$month]    ?? null;
            $expense  = $this->expenses[$month] ?? null;
            $loss     = $this->losses[$month]   ?? null;

            $this->netprofits[$month] = $sale - ($expense + $loss);
        }
    }

    public function render()
    {
        return view('livewire.comparative-analysis');
    }
}
