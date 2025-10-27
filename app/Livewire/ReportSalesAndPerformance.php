<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportSalesAndPerformance extends Component
{
    public $sbc; 
    public $currentMonth; 
    public $currentYear; 

    public $g;

    public $category;
    public $selectedCategory;
    public $sortField = 'product_name';
    public $order = 'asc';


    public $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    public $years;
    public $selectedYearSingle; 
    public $selectedMonth; 
    public $selectedYear; 
    public $selectedYears;
    public $selectedMonths;

    public $peak;
    public $dateChoice;

    public $searchWord;
    public $suggestedCategories;

    public $perf;


    public function mount() {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        // $this->currentMonth = now()->month;
        $this->selectedMonth = now()->month;
        $this->selectedMonths = now()->month;
        $this->selectedYear = now()->year;
        $this->selectedYears = now()->year;


        $this->category = collect(DB::select("
            select category_id as cat_id,
                category as cat_name
            from categories
            where owner_id = ?
            order by category
        ", [$owner_id]));

        $this->displayYears();
    }


    public function updatedCurrentMonth() {
        $this->resetPage();
    }


    public function updatedSelectedYearSingle($value) {
        $this->selectedYears = [(int) $value]; 
    }

    public function resetFilters() {
        $this->selectedYears = [now()->year];
        $this->selectedMonths = [now()->month];

    }


    public function displayYears() {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        $this->years = collect(DB::select("
            SELECT DISTINCT(YEAR(receipt_date)) AS year
            FROM receipt
            WHERE owner_id = ?
            ORDER BY year DESC", 
            [$owner_id]
        ))->pluck('year');

    }

    
    
    public function salesByCategory() {
        $years = $this->selectedYears ? [$this->selectedYears] : [now()->year];
        $months = $this->selectedMonths ? [$this->selectedMonths] : [now()->month];

        $yearPlaceholders = implode(',', array_fill(0, count($years), '?'));
        $monthPlaceholders = implode(',', array_fill(0, count($months), '?'));

        $owner_id = Auth::guard('owner')->user()->owner_id;
                
        $sql = "
            SELECT
                c.category,
                COALESCE(SUM(ritems.item_quantity), 0) AS unit_sold,
                COALESCE(SUM(p.selling_price * ritems.item_quantity), 0) AS total_sales,
                COALESCE(SUM(p.cost_price * ritems.item_quantity), 0) AS cogs,

                CASE
                    WHEN COALESCE(SUM(p.selling_price * ritems.item_quantity), 0) = 0 THEN 0
                    ELSE (
                        (SUM(p.selling_price * ritems.item_quantity) - SUM(p.cost_price * ritems.item_quantity))
                        / SUM(p.selling_price * ritems.item_quantity)
                    ) * 100
                END AS gross_margin,

                COALESCE((
                    SELECT p2.name
                    FROM products p2
                    JOIN receipt_item ri2 ON p2.prod_code = ri2.prod_code
                    JOIN receipt r2 ON r2.receipt_id = ri2.receipt_id
                    WHERE p2.category_id = c.category_id
                    AND r2.owner_id = ?
                    AND YEAR(r2.receipt_date) IN ($yearPlaceholders)
                    AND MONTH(r2.receipt_date) IN ($monthPlaceholders)
                    GROUP BY p2.prod_code, p2.name
                    ORDER BY SUM(ri2.item_quantity) DESC
                    LIMIT 1
                ), '—') AS top_product_unit,

                COALESCE((
                    SELECT p2.name
                    FROM products p2
                    JOIN receipt_item ri2 ON p2.prod_code = ri2.prod_code
                    JOIN receipt r2 ON r2.receipt_id = ri2.receipt_id
                    WHERE p2.category_id = c.category_id
                    AND r2.owner_id = ?
                    AND YEAR(r2.receipt_date) IN ($yearPlaceholders)
                    AND MONTH(r2.receipt_date) IN ($monthPlaceholders)
                    GROUP BY p2.prod_code, p2.name
                    ORDER BY SUM(ri2.item_quantity * p2.selling_price) DESC
                    LIMIT 1
                ), '—') AS top_product_sales,

                COALESCE(i.stock, 0) AS stock_left,

                COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(
                    AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 0
                ) AS velocity_ratio, -- supposed to be speed_ratio

                COALESCE(i.stock, 0) / NULLIF(
                    COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(COUNT(DISTINCT ritems.receipt_date), 0), 0
                ) AS days_of_supply,

                CASE
                    WHEN COALESCE(i.stock, 0) = 0 
                        AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        AND COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 0) > 1.2
                        THEN 'URGENT: Fast-moving category out of stock. Immediate reorder required.'
                    
                    WHEN COALESCE(i.stock, 0) = 0 AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Out of stock with recent sales. Reorder needed.'
                    
                    WHEN COALESCE(i.stock, 0) = 0 AND COALESCE(SUM(ritems.item_quantity), 0) = 0
                        THEN 'Out of stock and no sales for this month. Evaluate demand before reordering.'
                    
                    WHEN COALESCE(i.stock, 0) / NULLIF(
                            COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(COUNT(DISTINCT ritems.receipt_date), 0), 0
                        ) < 3 AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Stock critically low. Will run out in less than 3 days at current rate.'
                    
                    WHEN COALESCE(i.stock, 0) / NULLIF(
                            COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(COUNT(DISTINCT ritems.receipt_date), 0), 0
                        ) BETWEEN 3 AND 7 AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Low stock. Reorder within this week to avoid shortage.'
                    
                    WHEN COALESCE(SUM(ritems.item_quantity), 0) = 0 AND COALESCE(i.stock, 0) > 0
                        THEN 'No recent sales despite stock availability. Reassess demand or consider promotions.'
                    
                    WHEN COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 0) > 1.5
                        AND ((SUM(p.selling_price * ritems.item_quantity) - SUM(p.cost_price * ritems.item_quantity))
                            / NULLIF(SUM(p.selling_price * ritems.item_quantity), 0)) * 100 > 25
                        AND COALESCE(SUM(ritems.item_quantity), 0) > GREATEST(
                            AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER () * 1.5, 10
                        )
                        THEN 'Star performer: Fast sales with strong margins. Consider expanding stock.'
                    
                    WHEN COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 0) > 1.5
                        AND ((SUM(p.selling_price * ritems.item_quantity) - SUM(p.cost_price * ritems.item_quantity))
                            / NULLIF(SUM(p.selling_price * ritems.item_quantity), 0)) * 100 < 15
                        AND COALESCE(SUM(ritems.item_quantity), 0) > GREATEST(
                            AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER () * 1.5, 10
                        )
                        THEN 'Fast-moving but low margins. Review pricing or supplier costs.'
                    
                    WHEN COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 0) > 1.2
                        AND COALESCE(SUM(ritems.item_quantity), 0) > GREATEST(
                            AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 5
                        )
                        THEN 'Good sales velocity. Maintain stock levels and monitor trends.'
                    
                    WHEN COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 0) < 0.5
                        AND ((SUM(p.selling_price * ritems.item_quantity) - SUM(p.cost_price * ritems.item_quantity))
                            / NULLIF(SUM(p.selling_price * ritems.item_quantity), 0)) * 100 < 15
                        AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Slow-moving with poor margins. Consider discontinuing or clearance.'
                    
                    WHEN COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 0) < 0.5
                        AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Slow-moving category. Reduce stock levels to free up capital.'
                    
                    WHEN ((SUM(p.selling_price * ritems.item_quantity) - SUM(p.cost_price * ritems.item_quantity))
                        / NULLIF(SUM(p.selling_price * ritems.item_quantity), 0)) * 100 < 10
                        AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Low profit margin. Review pricing or supplier costs.'
                    
                    WHEN ((SUM(p.selling_price * ritems.item_quantity) - SUM(p.cost_price * ritems.item_quantity))
                        / NULLIF(SUM(p.selling_price * ritems.item_quantity), 0)) * 100 BETWEEN 10 AND 25
                        AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Steady sales with modest profit. Maintain visibility and monitor competition.'
                    
                    WHEN ((SUM(p.selling_price * ritems.item_quantity) - SUM(p.cost_price * ritems.item_quantity))
                        / NULLIF(SUM(p.selling_price * ritems.item_quantity), 0)) * 100 > 25
                        AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Strong profit margins. Consider promotions to boost volume.'
                    
                    ELSE 'Stable category performance. Continue monitoring trends.'
                END AS insight
                
            FROM categories c
            LEFT JOIN products p
                ON c.category_id = p.category_id
                AND c.owner_id = ?
            LEFT JOIN (
                SELECT c2.category_id, SUM(inv.stock) AS stock
                FROM inventory inv
                JOIN products p2 ON inv.prod_code = p2.prod_code
                JOIN categories c2 ON p2.category_id = c2.category_id
                WHERE p2.owner_id = ?
                GROUP BY c2.category_id
            ) i ON i.category_id = p.category_id
            LEFT JOIN (
                SELECT ri.prod_code, ri.item_quantity, ri.receipt_id, r.owner_id, r.receipt_date
                FROM receipt_item ri
                JOIN receipt r ON r.receipt_id = ri.receipt_id
                WHERE r.owner_id = ?
                AND YEAR(r.receipt_date) IN ($yearPlaceholders)
                AND MONTH(r.receipt_date) IN ($monthPlaceholders)
            ) AS ritems ON p.prod_code = ritems.prod_code
            WHERE c.owner_id = ?
            GROUP BY c.category, c.category_id, i.stock
            order by c.category asc
        ";

        $bindings = array_merge(
            [$owner_id], $years, $months,
            [$owner_id], $years, $months,
            [$owner_id, $owner_id, $owner_id], $years, $months,
            [$owner_id]
        );


        $this->sbc = collect(DB::select($sql, $bindings));

        $totalUnits = $this->sbc->sum('unit_sold');

        if (!empty($this->searchWord)) {
            $search = strtolower($this->searchWord);
            $this->sbc = $this->sbc->filter(function($item) use ($search) {
                return str_contains(strtolower($item->category), $search);
            })->values();
        }

 
    }



    public function peakHour() {

        // $date = $this->selectedYears ?: now()->toDateString(); // ✅ String: "2025-10-03"
        // $date = '2025-09-18';

        if ($this->dateChoice === null) {
            $this->dateChoice = $this->selectedYears ?: now()->toDateString();
        }

        $owner_id = Auth::guard('owner')->user()->owner_id;


        $this->peak = collect(DB::select("
            WITH RECURSIVE time_slots AS (
                SELECT 
                    DATE_FORMAT(MIN(receipt_date), '%Y-%m-%d %H:00:00') AS slot_start,
                    DATE_FORMAT(DATE_ADD(MIN(receipt_date), INTERVAL 1 HOUR), '%Y-%m-%d %H:00:00') AS slot_end,
                    DATE(MIN(receipt_date)) AS day
                FROM receipt
                WHERE DATE(receipt_date) = ?

                UNION ALL

                SELECT 
                    DATE_FORMAT(slot_end, '%Y-%m-%d %H:00:00'),
                    DATE_FORMAT(DATE_ADD(slot_end, INTERVAL 1 HOUR), '%Y-%m-%d %H:00:00'),
                    day
                FROM time_slots
                WHERE slot_end < (
                    SELECT MAX(receipt_date) 
                    FROM receipt 
                    WHERE DATE(receipt_date) = ?
                )
            )

            SELECT 
                DAYNAME(ts.day) AS dayName,
                CONCAT(DATE_FORMAT(ts.slot_start, '%h:%i %p'), ' - ', DATE_FORMAT(ts.slot_end, '%h:%i %p')) AS time_slot,
                COUNT(DISTINCT r.receipt_id) AS transactions,                        
                COALESCE(SUM(ri.item_quantity * p.selling_price), 0) AS sales,
                CASE WHEN COUNT(DISTINCT r.receipt_id) > 0
                    THEN ROUND(COALESCE(SUM(ri.item_quantity * p.selling_price), 0) / COUNT(DISTINCT r.receipt_id), 2)
                    ELSE 0 END AS avg_value
            FROM time_slots ts
            LEFT JOIN receipt r 
                ON r.receipt_date >= ts.slot_start
            AND r.receipt_date <  ts.slot_end
            AND r.owner_id = ?
            AND DATE(r.receipt_date) = ?
            LEFT JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
            LEFT JOIN products p ON ri.prod_code = p.prod_code
            WHERE DATE(ts.day) = ?
            GROUP BY ts.day, ts.slot_start, ts.slot_end
            ORDER BY ts.slot_start
        ", [$this->dateChoice, $this->dateChoice, $owner_id, $this->dateChoice, $this->dateChoice]));


    }





    public function updatedSelectedCategory() {
        $this->prodPerformance();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->order = $this->order === 'asc' ? 'desc' : 'asc';
        } else {
            $this->order = 'asc';
        }

        $this->sortField = $field;
        $this->prodPerformance();
    }


    public function prodPerformance() {

        $owner_id = Auth::guard('owner')->user()->owner_id;
        $latestYear = $this->selectedYear ?? now()->year;
        $month = $this->selectedMonth ?? now()->month;


        $perf = collect(DB::select("
            SELECT p.prod_code, p.name AS product_name, c.category AS category, c.category_id,
                COALESCE(SUM(ri.item_quantity), 0) AS unit_sold,
                COALESCE(SUM(p.selling_price * ri.item_quantity), 0) AS total_sales,
                COALESCE(SUM(p.cost_price * ri.item_quantity), 0) AS cogs,
                (COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0)) AS profit,

                ((COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0))
                    / NULLIF(COALESCE(SUM(p.selling_price * ri.item_quantity), 0), 0)) * 100 AS profit_margin_percent,

                COALESCE(
                    (SUM(p.selling_price * ri.item_quantity)/NULLIF(total.total_sales_all, 0)) * 100,0
                ) AS contribution_percent,

                COALESCE(inv.total_stock, 0) AS remaining_stocks,
                COALESCE(DATEDIFF(MAX(r.receipt_date), MIN(r.receipt_date)) + 1, 0) AS days_active,

                CASE 
                    WHEN COALESCE(inv.total_stock, 0) = 0 
                        AND COALESCE(SUM(ri.item_quantity), 0) > 0
                        THEN 'Out of stock. Reorder needed.'
                    
                    WHEN COALESCE(inv.total_stock, 0) = 0
                        THEN 'Out of stock with no recent sales.'
                    
                    WHEN COALESCE(inv.total_stock, 0) / NULLIF(
                            CASE 
                                WHEN DATEDIFF(MAX(r.receipt_date), MIN(r.receipt_date)) + 1 > 0 
                                THEN COALESCE(SUM(ri.item_quantity), 0) / (DATEDIFF(MAX(r.receipt_date), MIN(r.receipt_date)) + 1)
                                ELSE 0 
                            END, 0
                        ) < 3
                        AND COALESCE(SUM(ri.item_quantity), 0) > 0
                        THEN 'Low stock. Reorder soon.'
                    
                    WHEN COALESCE(SUM(ri.item_quantity), 0) = 0 
                        AND COALESCE(inv.total_stock, 0) > 0
                        THEN 'No sales this period.'
                    
                    WHEN COALESCE(SUM(ri.item_quantity), 0) = 0
                        THEN 'No activity.'
                    
                    WHEN (COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0)) <= 0 
                        THEN 'Unprofitable. Losing money.'
                    
                    WHEN ((COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0))
                        / NULLIF(COALESCE(SUM(p.selling_price * ri.item_quantity), 0), 0)) * 100 < 10
                        THEN 'Low margin. Review pricing.'
                    
                    WHEN ((COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0))
                        / NULLIF(COALESCE(SUM(p.selling_price * ri.item_quantity), 0), 0)) * 100 >= 20
                        AND COALESCE(SUM(ri.item_quantity), 0) >= 10
                        THEN 'Performing well.'
                    
                    WHEN ((COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0))
                        / NULLIF(COALESCE(SUM(p.selling_price * ri.item_quantity), 0), 0)) * 100 >= 20
                        THEN 'Good margin, low volume.'
                    
                    WHEN ((COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0))
                        / NULLIF(COALESCE(SUM(p.selling_price * ri.item_quantity), 0), 0)) * 100 >= 10
                        THEN 'Moderate performance.'
                    
                    ELSE 'Needs attention.'
                END AS insight

            FROM products AS p
            LEFT JOIN (
                SELECT i.prod_code, SUM(i.stock) AS total_stock
                FROM inventory i
                JOIN products p2 ON i.prod_code = p2.prod_code
                WHERE p2.owner_id = ?  -- ← Added this filter!
                GROUP BY i.prod_code
            ) inv ON inv.prod_code = p.prod_code
            LEFT JOIN categories AS c 
                ON p.category_id = c.category_id
            LEFT JOIN receipt AS r 
                ON r.owner_id = p.owner_id
                AND MONTH(r.receipt_date) = ? 
                AND YEAR(r.receipt_date) = ?
            LEFT JOIN receipt_item AS ri 
                ON ri.prod_code = p.prod_code
                AND ri.receipt_id = r.receipt_id
            LEFT JOIN (
                SELECT 
                    p2.owner_id, 
                    SUM(p2.selling_price * ri2.item_quantity) AS total_sales_all
                FROM products p2
                JOIN receipt_item ri2 ON ri2.prod_code = p2.prod_code
                JOIN receipt r2 ON r2.receipt_id = ri2.receipt_id
                WHERE MONTH(r2.receipt_date) = ? AND YEAR(r2.receipt_date) = ?
                GROUP BY p2.owner_id
            ) total ON total.owner_id = p.owner_id
            WHERE p.owner_id = ?
            GROUP BY p.prod_code, p.name, c.category, p.owner_id, c.category_id, total.total_sales_all, inv.total_stock
        ", [ $owner_id, $month, $latestYear, $month, $latestYear, $owner_id]));

        

        if (!empty($this->selectedCategory) && $this->selectedCategory !== 'all') {
            $perf = $perf->where('category_id', (int) $this->selectedCategory);
        }

        $perf = $perf->sortBy(function ($item) {
            return $item->{$this->sortField};
        }, SORT_REGULAR, $this->order === 'desc')->values();


        $this->perf = $perf->values();

    }

    public function render()
    {
        $this->peakHour();
        $this->salesByCategory();
        $this->displayYears();
        $this->prodPerformance();
        return view('livewire.report-sales-and-performance');
    }
}
