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
    public array $selectedYears = [];
    public array $selectedMonths = [];

    public $peak;
    public $dateChoice;

    public $searchWord;
    public $suggestedCategories;

    public $perf;


    public function mount() {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        $this->currentMonth = now()->month;

        $this->category = collect(DB::select("
            select category_id as cat_id, 
                category as cat_name
            from categories
            where owner_id = ? 
        ", [$owner_id]));
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
        ));

        if ($this->years->isEmpty()) {
            $this->years = collect([(object)['year' => now()->year]]);
        }

    }
    
    public function salesByCategory() {
        $years = $this->selectedYears ?: [now()->year];
        $months = $this->selectedMonths ?: [now()->month];

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
                    WHEN COALESCE(SUM(p.selling_price * ritems.item_quantity), 0) = 0
                        THEN 0
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
                ), '—') AS top_product_sales

            FROM categories c
            LEFT JOIN products p
                ON c.category_id = p.category_id
            AND c.owner_id = ?
            LEFT JOIN (
                SELECT ri.prod_code, ri.item_quantity, r.owner_id, r.receipt_date
                FROM receipt_item ri
                JOIN receipt r ON r.receipt_id = ri.receipt_id
                WHERE r.owner_id = ?
                AND YEAR(r.receipt_date) IN ($yearPlaceholders)
                AND MONTH(r.receipt_date) IN ($monthPlaceholders)
            ) AS ritems ON p.prod_code = ritems.prod_code
            WHERE c.owner_id = ?
            GROUP BY c.category, c.category_id
        ";

        $bindings = array_merge(
            [$owner_id], $years, $months,
            [$owner_id], $years, $months,
            [$owner_id, $owner_id], $years, $months,
            [$owner_id]
        );

        $this->sbc = collect(DB::select($sql, $bindings));

        $totalUnits = $this->sbc->sum('unit_sold');

        $this->sbc = $this->sbc->map(function($item) use ($totalUnits) {
            $ratio = $totalUnits ? $item->unit_sold / $totalUnits : 0;

            if ($ratio >= 0.3) {
                $salesBracket = 'High';
            } elseif ($ratio >= 0.1) {
                $salesBracket = 'Medium';
            } else {
                $salesBracket = 'Low';
            }

            if ($salesBracket == 'High' && $item->gross_margin >= 20) {
                $item->number = 1;
                $item->profit_comment = 'Selling well and profitable. Keep it promoted.';
            } elseif ($salesBracket == 'High' && $item->gross_margin < 20) {
                $item->number = 2;
                $item->profit_comment = 'High sales but low profit. Review pricing or costs.';
            } elseif ($salesBracket == 'Medium' && $item->gross_margin >= 20) {
                $item->number = 3;
                $item->profit_comment = 'Moderate sales with good profit. Promote more if possible.';
            } elseif ($salesBracket == 'Medium' && $item->gross_margin < 20) {
                $item->number = 4;
                $item->profit_comment = 'Average sales and low profit. Monitor pricing and costs.';
            } elseif ($salesBracket == 'Low' && $item->gross_margin >= 20) {
                $item->number = 5;
                $item->profit_comment = 'Low sales but profitable. Try promoting this category.';
            } else {
                $item->number = 6;
                $item->profit_comment = 'Low sales and low profit. Consider discounting or reducing stock.';
            }

            return $item;
        });

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
        $latestYear = now()->year;
        $month = now()->month;

        $perf = collect(DB::select("
            SELECT 
                p.prod_code,
                p.name AS product_name,
                c.category AS category,
                c.category_id,
                COALESCE(SUM(ri.item_quantity), 0) AS unit_sold,
                COALESCE(SUM(p.selling_price * ri.item_quantity), 0) AS total_sales,
                COALESCE(SUM(p.cost_price * ri.item_quantity), 0) AS cogs,
                (COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0)) AS profit,
                (
                    (COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0))
                    / NULLIF(COALESCE(SUM(p.selling_price * ri.item_quantity), 0), 0)
                ) * 100 AS profit_margin_percent,
                (
                    COALESCE(SUM(p.selling_price * ri.item_quantity), 0) / NULLIF((
                        SELECT 
                            SUM(p2.selling_price * ri2.item_quantity)
                        FROM receipt r2
                        JOIN receipt_item ri2 ON r2.receipt_id = ri2.receipt_id
                        JOIN products p2 ON ri2.prod_code = p2.prod_code
                        WHERE 
                            r2.owner_id = ?
                            AND MONTH(r2.receipt_date) = ?
                            AND YEAR(r2.receipt_date) = ?
                    ), 0)
                ) * 100 AS contribution_percent,
                COALESCE(sum(i.stock), 0) as remaining_stocks
            FROM products AS p
            LEFT JOIN inventory i 
                on p.prod_code = i.prod_code
            LEFT JOIN categories AS c 
                ON p.category_id = c.category_id
            LEFT JOIN receipt_item AS ri 
                ON ri.prod_code = p.prod_code
            LEFT JOIN receipt AS r 
                ON r.receipt_id = ri.receipt_id
                AND r.owner_id = ?
                AND MONTH(r.receipt_date) = ?
                AND YEAR(r.receipt_date) = ?
            WHERE p.owner_id = ?
            GROUP BY 
                p.prod_code, 
                p.name, 
                c.category, 
                p.owner_id, c.category_id
        ", [$owner_id, $month, $latestYear, $owner_id, $month, $latestYear, $owner_id]));

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
