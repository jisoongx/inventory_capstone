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
    public $selectedLossType = null; 

    public $category;
    public $years;

    public $lossRep;
    public $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    public $selectedMonths = null;
    public $selectedYears  = null;

    public $stock;


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
                i.inven_code as inven_code,
                p.prod_code AS prod_code,
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
                        'Critical! No sales in 30 days, might apply big discount.'

                    WHEN COALESCE(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END), 0) = 0 
                    AND i.stock <= 3  
                    AND DATEDIFF(i.expiration_date, CURDATE()) <= 21 THEN 
                    'Critical! No sales in 30 days, apply a discount while promoting it.'
                    
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
                        'Urgent! Selling too slow, apply 40-50% discount if possible.'
                    
                    WHEN CEIL(i.stock / NULLIF(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0, 0)) > DATEDIFF(i.expiration_date, CURDATE()) 
                    AND DATEDIFF(i.expiration_date, CURDATE()) <= 14 THEN 
                        'Action needed! Stock will not clear in time, offer a small discount.'
                    
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
            GROUP BY p.prod_code, i.batch_number
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





    public function showAll()
    {
        if (is_null($this->selectedMonths) && is_null($this->selectedYears)) {
            
            $this->selectedMonths = now()->format('m');
            $this->selectedYears = now()->format('Y');
            $this->selectedLossType = null;
        } else {
            
            $this->selectedMonths = null;
            $this->selectedYears = null;
            $this->selectedLossType = null;
        }

        $this->loss(); 
    }

    public function updatedSelectedMonths() {
        $this->loss();
    }

    public function updatedSelectedYears() {
        $this->loss();
    }

    public function updatedSelectedLossType() {
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

        if (!empty($this->selectedLossType)) {
            $whereClause .= " AND di.damaged_type = ?";
            $bindings[] = $this->selectedLossType;
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
                END AS reported_by,
                (SELECT i.batch_number FROM inventory i WHERE i.inven_code = di.inven_code) AS batch_num
            FROM damaged_items di
            join inventory i on i.inven_code = di.inven_code
            JOIN products p ON p.prod_code = i.prod_code
            JOIN categories c ON c.category_id = p.category_id
            
            LEFT JOIN owners o ON o.owner_id = di.owner_id
            LEFT JOIN staff s ON s.staff_id = di.staff_id
            {$whereClause}
            ORDER BY di.damaged_date DESC
        ", $bindings));
    }




public function stockAlertReport()
{
    $owner_id = Auth::guard('owner')->user()->owner_id;

    DB::connection()->getPdo()->exec("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");

    $results = DB::select("
        SELECT 
            i.inven_code as inven_code,
            i.batch_number as batch_number,
            p.name AS prod_name,
            p.prod_code,
            i.stock AS usable_stock,
            i.date_added AS last_stockin,
            COALESCE(d.damaged_total, 0) AS damaged_stock,
            COALESCE(ri.sold_total, 0) AS sold_stock,
            (i.stock + COALESCE(ri.sold_total, 0) + COALESCE(d.damaged_total, 0)) AS total_stock,
            CASE 
                WHEN (i.stock + COALESCE(ri.sold_total, 0) + COALESCE(d.damaged_total, 0)) > 0 
                THEN ROUND((COALESCE(d.damaged_total, 0) / NULLIF((i.stock + COALESCE(ri.sold_total, 0) + COALESCE(d.damaged_total, 0)), 0)) * 100, 2)
                ELSE 0 
            END AS damaged_rate_percent,
            CASE 
                WHEN (i.stock + COALESCE(ri.sold_total, 0) + COALESCE(d.damaged_total, 0)) > 0 
                THEN ROUND((COALESCE(ri.sold_total, 0) / NULLIF((i.stock + COALESCE(ri.sold_total, 0) + COALESCE(d.damaged_total, 0)), 0)) * 100, 2)
                ELSE 0 
            END AS sales_rate_percent,
            CASE 
                WHEN COALESCE(ri.sold_total, 0) > 0 
                THEN ROUND(COALESCE(d.damaged_total, 0) / COALESCE(ri.sold_total, 0), 2)
                WHEN COALESCE(d.damaged_total, 0) > 0 THEN 9999
                ELSE 0 
            END AS wastage_ratio
        FROM inventory i
        JOIN products p ON i.prod_code = p.prod_code
        LEFT JOIN (
            SELECT d.inven_code, SUM(d.damaged_quantity) AS damaged_total
            FROM damaged_items d GROUP BY d.inven_code
        ) d ON i.inven_code = d.inven_code
        LEFT JOIN (
            SELECT ri.inven_code, SUM(ri.item_quantity) AS sold_total
            FROM receipt_item ri
            JOIN receipt r ON r.receipt_id = ri.receipt_id
            GROUP BY ri.inven_code
        ) ri ON i.inven_code = ri.inven_code
        WHERE p.owner_id = ?
          AND (i.is_expired = 0 OR i.is_expired IS NULL)
          AND p.prod_status = 'active'
        GROUP BY i.inven_code
    ", [$owner_id]);

    $this->stock = collect($results)->map(function ($item) {
        $damageRate = $item->damaged_rate_percent;
        $salesRate = $item->sales_rate_percent;
        $damagedStock = $item->damaged_stock;
        $soldStock = $item->sold_stock;
        $usableStock = $item->usable_stock;

        if ($damageRate > 15 && $damagedStock > $soldStock) {
            $item->insight = "Critical! Too many items are getting damaged compared to sales. Check handling or storage right away.";
            $item->insight_color = "bg-red-500 text-white"; 
        } elseif ($damageRate > 10) {
            $item->insight = "High damage alert! Review how this product is stored or displayed to prevent losses.";
            $item->insight_color = "bg-orange-500 text-white"; 
        } elseif ($soldStock == 0 && $damagedStock > 0) {
            $item->insight = "No sales but items are damaged — you might want to rethink stocking this product.";
            $item->insight_color = "bg-yellow-500 text-white";
        } elseif ($salesRate < 10 && $usableStock > 5) {
            $item->insight = "Slow-moving stock detected. Try discounts or promos to boost sales.";
            $item->insight_color = "bg-blue-600 text-white"; 
        } elseif ($salesRate > 30 && $damageRate < 5) {
            $item->insight = "Great job! This product is selling fast with minimal waste.";
            $item->insight_color = "bg-green-500 text-white";
        } else {
            $item->insight = "Performance looks steady. Keep monitoring for any changes.";
            $item->insight_color = "bg-gray-500 text-white";
        }

        return $item; 
    });


    return $this->stock;
}







    public function render()
    {
        $this->stockAlertReport();
        $this->loss();
        $this->expired();

        return view('livewire.report-inventory');
    }
}
