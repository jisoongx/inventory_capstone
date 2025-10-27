<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportInventory extends Component
{
    public $expiredProd;
    public $selectedCategory;
    public $selectedRange = 60; 

    public $category;
    public $years;

    public $lossRep;
    public $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    public $selectedMonths = null;
    public $selectedYears  = null;


    public function mount() {

        $owner_id = Auth::guard('owner')->user()->owner_id;

        $this->selectedMonths = now()->month;
        $this->selectedYears = now()->year;

        $this->category = collect(DB::select("
            select category_id as cat_id,
                category as cat_name
            from categories
            where owner_id = ?
            order by category
        ", [$owner_id]));

        $this->years = collect(DB::select("
            SELECT DISTINCT(YEAR(receipt_date)) AS year
            FROM receipt
            WHERE owner_id = ?
            ORDER BY year DESC", 
            [$owner_id]
        ))->pluck('year');

    }
    




    public function updatedSelectedCategory() {
        $this->expired();
    }
    
    public function expired() {
        
        $owner_id = Auth::guard('owner')->user()->owner_id;

        DB::statement("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");
        
        $expired = collect(DB::select("
            SELECT 
                COALESCE(i.batch_number, 'Initial Batch') AS batch_num,
                p.name AS prod_name,
                i.stock AS expired_stock,
                i.expiration_date AS date,
                c.category AS cat_name,
                p.selling_price AS cost,
                DATEDIFF(i.expiration_date, CURDATE()) AS days_until_expiry,
                SUM(p.selling_price * i.stock) AS total_loss,
                
                -- Sales speed (last 30 days)
                COALESCE(SUM(CASE 
                    WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                    THEN ri.item_quantity ELSE 0 
                END) / 30.0, 0) AS avg_daily_sales,
                
                -- Days needed to sell current stock
                CASE 
                    WHEN COALESCE(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0, 0) = 0 THEN NULL
                    ELSE CEIL(i.stock / (SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0))
                END AS days_to_sellout,
                
                -- Will it sell before expiry?
                CASE 
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 0 THEN 
                        'Already expired.'
                    WHEN COALESCE(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0, 0) = 0 THEN 
                        'No sales, unlikely to sell.'
                    WHEN CEIL(i.stock / (SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0)) <= DATEDIFF(i.expiration_date, CURDATE()) THEN 
                        'Will likely sell out before expiry.'
                    ELSE 
                        'At risk of expiring with unsold stock.'
                END AS will_sell_before_expiry,
                

                CASE 
                    -- Already expired
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 0 THEN 
                        'Expired, remove from display and update stock records.'
                    
                        
                    WHEN COALESCE(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END), 0) = 0 AND DATEDIFF(i.expiration_date, CURDATE()) <= 21 THEN 
                        'Critical! No sales in 30 days, apply 50% discount.'
                    
                    WHEN COALESCE(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END), 0) = 0 THEN 
                        'Warning: No sales in 30 days, reposition or start discounting.'
                    
                        
                    
                    WHEN CEIL(i.stock / NULLIF(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0, 0)) > DATEDIFF(i.expiration_date, CURDATE()) 
                    AND DATEDIFF(i.expiration_date, CURDATE()) <= 7 THEN 
                        'Urgent! Selling too slow, apply 40-50% discount now.'
                    
                    WHEN CEIL(i.stock / NULLIF(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0, 0)) > DATEDIFF(i.expiration_date, CURDATE()) 
                    AND DATEDIFF(i.expiration_date, CURDATE()) <= 14 THEN 
                        'Action needed! Stock will not clear in time, offer 30% discount.'
                    
                    WHEN CEIL(i.stock / NULLIF(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0, 0)) > DATEDIFF(i.expiration_date, CURDATE()) 
                    AND DATEDIFF(i.expiration_date, CURDATE()) <= 21 THEN 
                        'Sales pace too slow, start promoting now to avoid wastage.'
                    

                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 7 THEN 
                        'One week left, apply discount or bundle to clear stock.'
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 14 THEN 
                        'Two weeks left, check sales pace and consider promotion.'
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 21 THEN 
                        'Three weeks left, monitor closely and plan promotion if needed.'
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 30 THEN 
                        'One month left, review sales and adjust restock plans.'
                    ELSE 
                        'Monitor stock levels regularly.'
                END AS insight

            FROM inventory i
            JOIN products p ON i.prod_code = p.prod_code
            JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN receipt_item ri ON ri.prod_code = p.prod_code
            LEFT JOIN receipt r ON ri.receipt_id = r.receipt_id
            WHERE p.owner_id = ?
            AND i.expiration_date IS NOT NULL 
            AND i.stock > 0
            AND DATEDIFF(i.expiration_date, CURDATE()) <= 60
            GROUP BY p.prod_code
            ORDER BY days_until_expiry ASC;
        ", [$owner_id]));
        
        if ($this->selectedRange !== null && $this->selectedRange !== '') {
            $range = (int) $this->selectedRange;

            if ($range === 0) {
                $expired = $expired->filter(fn($item) => $item->days_until_expiry <= 0);
            } else {
                $expired = $expired->filter(fn($item) => $item->days_until_expiry > 0 && $item->days_until_expiry <= $range);
            }
        }


        if (!empty($this->selectedCategory) && $this->selectedCategory !== 'all') {
            $expired = $expired->where('category_id', (int) $this->selectedCategory);
        }



        $this->expiredProd = $expired->values();


    }




    public function showAll() {
        $this->selectedMonths = null;
        $this->selectedYears = null;
        $this->loss();
    }

    public function updatedSelectedMonths() {
        $this->loss();
    }

    public function updatedSelectedYears() {
        $this->loss();
    }

    public function loss() {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        $whereClause = "WHERE p.owner_id = ?";
        $bindings = [$owner_id];

        if (!is_null($this->selectedMonths)) {
            $whereClause .= " AND MONTH(di.damaged_date) = ?";
            $bindings[] = $this->selectedMonths;
        }

        if (!is_null($this->selectedYears)) {
            $whereClause .= " AND YEAR(di.damaged_date) = ?";
            $bindings[] = $this->selectedYears;
        }

        $this->lossRep = collect(DB::select("
            SELECT 
                di.damaged_id,
                di.damaged_date AS date_reported, 
                di.damaged_type AS type, 
                di.damaged_quantity AS qty,
                di.damaged_reason AS remarks,
                p.name AS prod_name, 
                c.category AS cat_name,
                p.cost_price AS unit_cost,
                (p.cost_price * di.damaged_quantity) AS total_loss,
                CASE 
                    WHEN s.staff_id IS NOT NULL 
                    THEN s.firstname 
                    ELSE o.firstname
                END AS reported_by
            FROM damaged_items di
            JOIN products p ON p.prod_code = di.prod_code
            JOIN categories c ON c.category_id = p.category_id
            LEFT JOIN owners o ON o.owner_id = di.owner_id
            LEFT JOIN staff s ON s.staff_id = di.staff_id
            {$whereClause}
            ORDER BY di.damaged_date DESC
        ", $bindings));
    }

    public function render()
    {
        $this->loss();
        $this->expired();
        return view('livewire.report-inventory');
    }
}
