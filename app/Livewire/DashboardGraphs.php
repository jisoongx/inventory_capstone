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


    public $nextMonth;
    public $previousMonth;
    public int $selectedMonthSL;
    public int $selectedYearSL;

    public function currencySales() {

        DB::connection()->getPdo()->exec("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");

        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;
        $this->owner_name = $owner->firstname;

        $startYear = Auth::guard('owner')->user()->created_on ?? now()->year;
        $currentYear = now()->year;

        $this->year = range($currentYear, $startYear);

        $this->dateDisplay = Carbon::now('Asia/Manila');
        $this->day = now()->format('Y-m-d');
        $currentMonth = (int) date('m');
        $latestYear = now()->year;
        $this->selectedYear = now()->year;

        $this->dailySales = collect(DB::select("
            SELECT 
                SUM(x.item_sales - x.receipt_discount) AS dailySales
            FROM (
                SELECT 
                    r.receipt_id,
                    SUM(
                        ri.item_quantity * (
                            COALESCE(
                                (SELECT ph.old_selling_price
                                FROM pricing_history ph
                                WHERE ph.prod_code = ri.prod_code
                                AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                ORDER BY ph.effective_from DESC
                                LIMIT 1),
                                p.selling_price
                            )
                        ) - (ri.item_quantity * COALESCE(ri.item_discount_amount,0))
                    ) AS item_sales,
                    r.discount_amount AS receipt_discount
                FROM receipt r
                JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
                JOIN products p ON p.prod_code = ri.prod_code
                WHERE DATE(r.receipt_date) = ?
                AND r.owner_id = ?
                GROUP BY r.receipt_id, r.discount_amount
            ) AS x
        ", [$this->day, $owner_id]))->first() ?? (object)['dailySales' => 0];


        $this->weeklySales = collect(DB::select('
            SELECT 
                MONTH(x.receipt_date) AS month,
                COALESCE(SUM(x.item_sales) - SUM(x.discount_amount), 0) AS weeklySales
            FROM (
                SELECT 
                    r.receipt_id,
                    r.receipt_date,
                    r.discount_amount,
                    SUM(
                        ri.item_quantity * (
                            COALESCE(
                                (SELECT ph.old_selling_price
                                FROM pricing_history ph
                                WHERE ph.prod_code = ri.prod_code
                                AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                ORDER BY ph.effective_from DESC
                                LIMIT 1),
                                p.selling_price
                            )
                        ) 
                    ) - (ri.item_quantity * COALESCE(ri.item_discount_amount, 0)) AS item_sales

                FROM receipt r
                JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
                JOIN products p ON p.prod_code = ri.prod_code
                WHERE r.receipt_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
                AND r.owner_id = ?
            ) AS x
        ', [$owner_id]))->first() ?? (object)['weeklySales' => 0, 'month' => null];
        
        $this->monthSales = collect(DB::select('
            SELECT 
                MONTH(x.receipt_date) AS month,
                SUM(x.item_sales) - SUM(x.discount_amount) AS monthSales
            FROM (
                SELECT 
                    r.receipt_id,
                    r.receipt_date,
                    r.discount_amount,
                    SUM(
                        ri.item_quantity * (
                            COALESCE(
                                (SELECT ph.old_selling_price
                                FROM pricing_history ph
                                WHERE ph.prod_code = ri.prod_code
                                AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                ORDER BY ph.effective_from DESC
                                LIMIT 1),
                                p.selling_price
                            )
                        ) 
                    ) - (ri.item_quantity * COALESCE(ri.item_discount_amount, 0)) AS item_sales


                FROM receipt r
                JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
                JOIN products p ON p.prod_code = ri.prod_code
                WHERE MONTH(receipt_date) = ?
                    AND r.owner_id = ?
                    AND YEAR(receipt_date) = ?
                GROUP BY r.receipt_id
            ) AS x
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

        DB::connection()->getPdo()->exec("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')"); 

        $productCategory = collect(DB::select("
            SELECT 
                c.category,
                COALESCE(SUM(t.category_sales - t.allocated_discount), 0) AS total_amount,
                c.category_id
            FROM categories c
            LEFT JOIN (
                /* category sales per receipt */
                SELECT
                    p.category_id,
                    r.receipt_id,
                    SUM(
                        ri.item_quantity * COALESCE(
                            (SELECT ph.old_selling_price
                            FROM pricing_history ph
                            WHERE ph.prod_code = ri.prod_code
                            AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                            ORDER BY ph.effective_from DESC
                            LIMIT 1),
                            p.selling_price
                        ) - (ri.item_quantity * COALESCE(ri.item_discount_amount, 0))
                    ) AS category_sales,
                    r.discount_amount,
                    
                    /* allocate receipt discount proportionally to category */
                    (SUM(
                        ri.item_quantity * COALESCE(
                            (SELECT ph.old_selling_price
                            FROM pricing_history ph
                            WHERE ph.prod_code = ri.prod_code
                            AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                            ORDER BY ph.effective_from DESC
                            LIMIT 1),
                            p.selling_price
                        ) - (ri.item_quantity * COALESCE(ri.item_discount_amount, 0))
                    ) / (
                        SELECT SUM(
                            ri2.item_quantity * COALESCE(
                                (SELECT ph2.old_selling_price
                                FROM pricing_history ph2
                                WHERE ph2.prod_code = ri2.prod_code
                                AND r2.receipt_date BETWEEN ph2.effective_from AND ph2.effective_to
                                ORDER BY ph2.effective_from DESC
                                LIMIT 1),
                                p2.selling_price
                            ) - (ri2.item_quantity * COALESCE(ri2.item_discount_amount, 0))
                        )
                        FROM receipt_item ri2
                        JOIN products p2 ON p2.prod_code = ri2.prod_code
                        JOIN receipt r2 ON r2.receipt_id = ri2.receipt_id
                        WHERE r2.receipt_id = r.receipt_id
                    )) * r.discount_amount AS allocated_discount
                FROM receipt r
                JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
                JOIN products p ON p.prod_code = ri.prod_code
                WHERE r.owner_id = ?
                AND YEAR(r.receipt_date) = ?
                GROUP BY p.category_id, r.receipt_id, r.discount_amount
            ) AS t ON t.category_id = c.category_id
            WHERE c.owner_id = ?
            GROUP BY c.category_id, c.category
            ORDER BY c.category_id
        ", [
            $owner_id,
            $latestYear,
            $owner_id
        ]))->toArray();

        $this->categories = array_map(fn($row) => $row->category, $productCategory);
        $this->products = array_map(fn($row) => (float) $row->total_amount , $productCategory);

        $productCategoryPrev = collect(DB::select("
            SELECT 
                c.category,
                COALESCE(SUM(t.category_sales - t.allocated_discount), 0) AS total_amount,
                c.category_id
            FROM categories c
            LEFT JOIN (
                /* category sales per receipt */
                SELECT
                    p.category_id,
                    r.receipt_id,
                    SUM(
                        ri.item_quantity * COALESCE(
                            (SELECT ph.old_selling_price
                            FROM pricing_history ph
                            WHERE ph.prod_code = ri.prod_code
                            AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                            ORDER BY ph.effective_from DESC
                            LIMIT 1),
                            p.selling_price
                        ) - (ri.item_quantity * COALESCE(ri.item_discount_amount, 0))
                    ) AS category_sales,
                    r.discount_amount,
                    
                    /* allocate receipt discount proportionally to category */
                    (SUM(
                        ri.item_quantity * COALESCE(
                            (SELECT ph.old_selling_price
                            FROM pricing_history ph
                            WHERE ph.prod_code = ri.prod_code
                            AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                            ORDER BY ph.effective_from DESC
                            LIMIT 1),
                            p.selling_price
                        ) - (ri.item_quantity * COALESCE(ri.item_discount_amount, 0))
                    ) / (
                        SELECT SUM(
                            ri2.item_quantity * COALESCE(
                                (SELECT ph2.old_selling_price
                                FROM pricing_history ph2
                                WHERE ph2.prod_code = ri2.prod_code
                                AND r2.receipt_date BETWEEN ph2.effective_from AND ph2.effective_to
                                ORDER BY ph2.effective_from DESC
                                LIMIT 1),
                                p2.selling_price
                            ) - (ri2.item_quantity * COALESCE(ri2.item_discount_amount, 0))
                        )
                        FROM receipt_item ri2
                        JOIN products p2 ON p2.prod_code = ri2.prod_code
                        JOIN receipt r2 ON r2.receipt_id = ri2.receipt_id
                        WHERE r2.receipt_id = r.receipt_id
                    )) * r.discount_amount AS allocated_discount
                FROM receipt r
                JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
                JOIN products p ON p.prod_code = ri.prod_code
                WHERE r.owner_id = ?
                AND YEAR(r.receipt_date) = ?
                GROUP BY p.category_id, r.receipt_id, r.discount_amount
            ) AS t ON t.category_id = c.category_id
            WHERE c.owner_id = ?
            GROUP BY c.category_id, c.category
            ORDER BY c.category_id
        ", [  $owner_id,
            $latestYear-1, $owner_id
        ]))->toArray();
        $this->productsPrev = array_map(fn($row) => (float) $row->total_amount, $productCategoryPrev);

        $productCategoryAve = collect(DB::select("
            SELECT 
                c.category,
                COALESCE( SUM(x.category_sales - x.allocated_discount) / NULLIF(COUNT(DISTINCT YEAR(x.receipt_date)), 0), 0 ) AS avg_total_sales,
                c.category_id 
            FROM categories c
            LEFT JOIN (
                SELECT 
                    p.category_id,
                    x.receipt_id,
                    x.receipt_date,
                    SUM(x.item_sales) AS category_sales,
                    /* Proportional receipt discount for this category */
                    (SUM(x.item_sales) / NULLIF((
                        SELECT SUM(
                            ri2.item_quantity * COALESCE(
                                (SELECT ph2.old_selling_price
                                FROM pricing_history ph2
                                WHERE ph2.prod_code = ri2.prod_code
                                AND r2.receipt_date BETWEEN ph2.effective_from AND ph2.effective_to
                                ORDER BY ph2.effective_from DESC
                                LIMIT 1),
                                p2.selling_price
                            ) - (ri2.item_quantity * COALESCE(ri2.item_discount_amount, 0))
                        )
                        FROM receipt_item ri2
                        JOIN products p2 ON p2.prod_code = ri2.prod_code
                        JOIN receipt r2 ON r2.receipt_id = ri2.receipt_id
                        WHERE r2.receipt_id = x.receipt_id
                    ), 0)) * MAX(x.discount_amount) AS allocated_discount
                FROM (
                    SELECT 
                        r.receipt_id,
                        r.discount_amount,
                        ri.prod_code,
                        ri.item_quantity,
                        ri.item_discount_amount,
                        r.receipt_date,
                        (ri.item_quantity * COALESCE(
                            (SELECT ph.old_selling_price
                            FROM pricing_history ph
                            WHERE ph.prod_code = ri.prod_code
                            AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                            ORDER BY ph.effective_from DESC
                            LIMIT 1),
                            p.selling_price
                        )) - (ri.item_quantity * COALESCE(ri.item_discount_amount, 0)) AS item_sales
                    FROM receipt r
                    JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
                    JOIN products p ON p.prod_code = ri.prod_code
                    WHERE 
                        r.owner_id = ? 
                        AND p.owner_id = r.owner_id
                ) AS x
                JOIN products p ON p.prod_code = x.prod_code
                GROUP BY p.category_id, x.receipt_id
            ) AS x ON c.category_id = x.category_id
            WHERE c.owner_id = ?
            GROUP BY c.category_id, c.category
            ORDER BY c.category_id;
        ", [$owner_id, $owner_id]))->toArray();
        $this->productsAve = array_map(fn($row) => (float) $row->avg_total_sales, $productCategoryAve);

        $this->dispatch('chart-updated');

    }



    public function mount()
    {
        $this->selectedMonthSL = now()->month;
        $this->selectedYearSL = now()->year;

        $this->salesVSloss();
    }



    public function changeMonth(int $direction)
    {
        if ($direction === -1) {
            // go to previous month
            if ($this->selectedMonthSL === 1) {
                $this->selectedMonthSL = 12;
                $this->selectedYearSL--;
            } else {
                $this->selectedMonthSL--;
            }
        }

        if ($direction === 1) {
            // go to next month
            if ($this->selectedMonthSL === 12) {
                $this->selectedMonthSL = 1;
                $this->selectedYearSL++;
            } else {
                $this->selectedMonthSL++;
            }
        }

        $this->salesVSloss();
    }


    public function salesVSloss() {

        DB::connection()->getPdo()->exec("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");

        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;

        $latestYear = $this->selectedYearSL;
        $currentMonth = $this->selectedMonthSL;

        // Get ALL months data for the selected year to properly compare
        $allLosses = collect(DB::select("
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
                WHERE d.owner_id = ? AND YEAR(d.damaged_date) = ? and
                    (d.set_to_return_to_supplier is null or d.set_to_return_to_supplier = 'Damaged')
                GROUP BY MONTH(d.damaged_date)
            ) l ON m.month = l.month
            ORDER BY m.month
        ", [$owner_id, $latestYear]))->pluck('total_loss')->toArray();

        $this->losses = array_slice($allLosses, 0, $currentMonth);

        // Get ALL months data for the selected year
        $allSales = [];
        for ($month = 1; $month <= 12; $month++) {
            $salesData = collect(DB::select("
                SELECT 
                    MONTH(x.receipt_date) AS month,
                    SUM(x.item_sales) - SUM(x.discount_amount) AS monthly_sales
                FROM (
                    SELECT 
                        r.receipt_id,
                        r.receipt_date,
                        r.discount_amount,
                        SUM(
                            ri.item_quantity * (
                                COALESCE(
                                    (SELECT ph.old_selling_price
                                    FROM pricing_history ph
                                    WHERE ph.prod_code = ri.prod_code
                                    AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                    ORDER BY ph.effective_from DESC
                                    LIMIT 1),
                                    p.selling_price
                                )
                            ) 
                        ) - (ri.item_quantity * COALESCE(ri.item_discount_amount, 0)) AS item_sales
                    FROM receipt r
                    JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
                    JOIN products p ON p.prod_code = ri.prod_code
                    WHERE r.owner_id = ? 
                        AND MONTH(receipt_date) = ?
                        AND YEAR(receipt_date) = ?
                    GROUP BY r.receipt_id
                ) AS x
            ", [$owner_id, $month, $latestYear]))->pluck('monthly_sales')->first();
            
            $allSales[] = $salesData ?? 0;
        }

        $this->sales = array_slice($allSales, 0, $currentMonth);

        // Check if this is the first month of the year AND no data exists for previous months
        $isFirstMonthOfYear = ($currentMonth == 1);
        $hasPreviousYearData = false;
        
        if ($isFirstMonthOfYear) {
            // Check if there's data from the previous year
            $prevYear = $latestYear - 1;
            $prevYearData = collect(DB::select("
                SELECT COUNT(*) as count 
                FROM receipt r
                WHERE r.owner_id = ? AND YEAR(receipt_date) = ?
                UNION ALL
                SELECT COUNT(*) 
                FROM damaged_items d
                WHERE d.owner_id = ? AND YEAR(d.damaged_date) = ?
            ", [$owner_id, $prevYear, $owner_id, $prevYear]))->sum('count');
            
            $hasPreviousYearData = ($prevYearData > 0);
        }

        $latestSales = end($this->sales) ?: 0;
        $latestLoss = end($this->losses) ?: 0;
        $previousSales = count($this->sales) > 1 ? $this->sales[count($this->sales) - 2] : 0;
        $previousLoss = count($this->losses) > 1 ? $this->losses[count($this->losses) - 2] : 0;

        // Calculate percentage of total (for display)
        $totalActivity = $latestSales + $latestLoss; 
        $this->salesPercentage = $totalActivity > 0 ? round(($latestSales / $totalActivity) * 100, 1) : 0;
        $this->lossPercentage = $totalActivity > 0 ? round(($latestLoss / $totalActivity) * 100, 1) : 0;

        // For January (month 1), check if we should compare with December of previous year
        if ($currentMonth == 1 && $hasPreviousYearData) {
            // Get December data from previous year for comparison
            $prevYear = $latestYear - 1;
            
            $prevDecSales = collect(DB::select("
                SELECT 
                    SUM(x.item_sales) - SUM(x.discount_amount) AS monthly_sales
                FROM (
                    SELECT 
                        r.receipt_id,
                        r.discount_amount,
                        SUM(
                            ri.item_quantity * (
                                COALESCE(
                                    (SELECT ph.old_selling_price
                                    FROM pricing_history ph
                                    WHERE ph.prod_code = ri.prod_code
                                    AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                    ORDER BY ph.effective_from DESC
                                    LIMIT 1),
                                    p.selling_price
                                )
                            ) 
                        ) - (ri.item_quantity * COALESCE(ri.item_discount_amount, 0)) AS item_sales
                    FROM receipt r
                    JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
                    JOIN products p ON p.prod_code = ri.prod_code
                    WHERE r.owner_id = ? 
                        AND MONTH(receipt_date) = 12
                        AND YEAR(receipt_date) = ?
                    GROUP BY r.receipt_id
                ) AS x
            ", [$owner_id, $prevYear]))->pluck('monthly_sales')->first() ?: 0;
            
            $prevDecLoss = collect(DB::select("
                SELECT 
                    SUM(d.damaged_quantity * p.selling_price) AS total_loss
                FROM damaged_items d
                Join inventory i on i.inven_code = d.inven_code
                JOIN products p ON i.prod_code = p.prod_code
                WHERE d.owner_id = ? 
                    AND MONTH(d.damaged_date) = 12
                    AND YEAR(d.damaged_date) = ?
                    AND (d.set_to_return_to_supplier is null or d.set_to_return_to_supplier = 'Damaged')
            ", [$owner_id, $prevYear]))->pluck('total_loss')->first() ?: 0;
            
            $previousSales = $prevDecSales;
            $previousLoss = $prevDecLoss;
        }

        // Calculate ACTUAL percentage change for insights
        if ($previousSales > 0) {
            $salesChangePercent = (($latestSales - $previousSales) / $previousSales) * 100;
        } else {
            $salesChangePercent = $latestSales > 0 ? 100 : 0; 
        }

        if ($previousLoss > 0) {
            $lossChangePercent = (($latestLoss - $previousLoss) / $previousLoss) * 100;
        } else {
            $lossChangePercent = $latestLoss > 0 ? 100 : 0; 
        }

        // Modified baseline check - only treat as baseline if NO previous data exists at all
        $hasAnyPreviousData = ($previousSales > 0 || $previousLoss > 0 || 
                            array_sum(array_slice($this->sales, 0, -1)) > 0 || 
                            array_sum(array_slice($this->losses, 0, -1)) > 0 ||
                            $hasPreviousYearData);

        if (!$hasAnyPreviousData && $latestSales == 0 && $latestLoss == 0) {
            $this->salesInsights = "This is your baseline month. Future sales comparisons will be based on this data.";
            $this->salesState = 'Start';
            $this->lossInsights = "This is your baseline month. Future loss comparisons will be based on this data.";
            $this->lossState = 'Start';
            $this->insight = "Your store has just started operations. Insights will appear once more data is collected.";
            $this->performanceLabel = "Start";
            return;
        } else {
            
            if($salesChangePercent > 0) {
                $this->salesInsights = "Compared to " . ($currentMonth == 1 && $hasPreviousYearData ? "last December" : "last month") . ", sales improved by " . number_format(abs($salesChangePercent), 1) . "%.";
                $this->salesState = 'Positive';
            } elseif ($salesChangePercent < 0) {
                $this->salesInsights = "Compared to " . ($currentMonth == 1 && $hasPreviousYearData ? "last December" : "last month") . ", sales decreased by " . number_format(abs($salesChangePercent), 1) . "%.";
                $this->salesState = 'Negative';
            } else {
                $this->salesInsights = "Sales remained consistent at " . number_format($this->salesPercentage, 1) . "%.";
                $this->salesState = 'Stable';
            }

            if($lossChangePercent > 0) {
                $this->lossInsights = "Compared to " . ($currentMonth == 1 && $hasPreviousYearData ? "last December" : "last month") . ", loss increased by " . number_format(abs($lossChangePercent), 1) . "%.";
                $this->lossState = 'Negative';
            } elseif ($lossChangePercent < 0) {
                $this->lossInsights = "Compared to " . ($currentMonth == 1 && $hasPreviousYearData ? "last December" : "last month") . ", loss decreased by " . number_format(abs($lossChangePercent), 1) . "%.";
                $this->lossState = 'Positive';
            } else {
                $this->lossInsights = "Loss remained steady at " . number_format($this->lossPercentage, 1) . "%.";
                $this->lossState = 'Stable';
            }
        }

        if ($totalActivity === 0) {
            $this->insight = "No sales activity recorded this month. Consider reviewing your inventory or marketing strategies.";
            $this->performanceLabel = "No Activity";
        } elseif ($latestSales == 0 && $latestLoss == 0 && ($previousSales > 0 || $previousLoss > 0)) {
            $this->insight = "No sales or losses recorded for the current month yet.";
            $this->performanceLabel = "No Data";
            return;
        } elseif ($this->lossPercentage < 2) {
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