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

    public $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    public $years;
    public $selectedYearSingle; 
    public array $selectedYears = [];
    public array $selectedMonths = [];

    public $searchWord;
    public $suggestedCategories;


    public function mount() {
        $this->currentMonth = now()->month;
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
            select DISTINCT(YEAR(receipt_date)) as year
            from receipt
            where owner_id = ?", [$owner_id]
        ));
    }
    
    public function salesByCategory() {
        $years = $this->selectedYears ?: [now()->year];
        $months = $this->selectedMonths ?: [now()->month];

        $yearPlaceholders = implode(',', array_fill(0, count($years), '?'));
        $monthPlaceholders = implode(',', array_fill(0, count($months), '?'));

        $owner_id = Auth::guard('owner')->user()->owner_id;

        
        if (!empty($this->searchWord)) {
            $search = '%' . strtolower($this->searchWord) . '%';

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
                ) AS ritems
                    ON p.prod_code = ritems.prod_code
                WHERE c.owner_id = ?
                    and LOWER(c.category) LIKE ?
                GROUP BY c.category, c.category_id
            ";

            $bindings = array_merge([$owner_id], $years, $months, [$owner_id], $years, $months, [$owner_id, $owner_id], $years, $months, [$owner_id, $search]);

            $this->sbc = collect(DB::select($sql, $bindings));

        } else {
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
                ) AS ritems
                    ON p.prod_code = ritems.prod_code
                WHERE c.owner_id = ?
                GROUP BY c.category, c.category_id
            ";

            $bindings = array_merge([$owner_id], $years, $months, [$owner_id], $years, $months, [$owner_id, $owner_id], $years, $months, [$owner_id]);

            $this->sbc = collect(DB::select($sql, $bindings));

        
        }

            $totalUnits = $this->sbc->sum('unit_sold');

            // Rule-Based / Threshold-Based Classification - ALGORITHM
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
 
    }

    public function render()
    {
        $this->salesByCategory();
        $this->displayYears();
        return view('livewire.report-sales-and-performance');
    }
}
