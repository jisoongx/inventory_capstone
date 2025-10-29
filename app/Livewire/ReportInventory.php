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




    public function showAll() { // show all sa loss
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




    public function stockAlertReport() {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        DB::statement("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");

        $this->stock = collect((DB::select("
            WITH sales_data AS (
                SELECT 
                    ri.prod_code,
                    SUM(ri.item_quantity) AS total_sold_30,
                    SUM(ri.item_quantity) / 30 AS avg_daily_sales
                FROM receipt_item ri
                JOIN receipt r ON r.receipt_id = ri.receipt_id
                WHERE r.owner_id = ?
                AND r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY ri.prod_code
            ),
            expiry_data AS (
                SELECT 
                    prod_code,
                    MIN(expiration_date) AS nearest_expiry,
                    DATEDIFF(MIN(expiration_date), CURDATE()) AS days_until_expiry
                FROM inventory
                WHERE expiration_date IS NOT NULL 
                AND expiration_date > CURDATE()
                GROUP BY prod_code
            )
            SELECT 
                p.prod_code,
                p.name AS prod_name,
                p.prod_image,
                c.category AS cat_name,
                p.stock_limit,
                
                -- Current valid stock
                COALESCE(SUM(CASE 
                    WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                    THEN i.stock 
                    ELSE 0 
                END), 0) AS current_stock,
                
                -- Expired stock count (for awareness)
                COALESCE(SUM(CASE 
                    WHEN i.expiration_date IS NOT NULL AND i.expiration_date <= CURDATE() 
                    THEN i.stock 
                    ELSE 0 
                END), 0) AS expired_stock,
                
                -- Sales velocity
                COALESCE(sd.avg_daily_sales, 0) AS avg_daily_sales,
                COALESCE(sd.total_sold_30, 0) AS total_sold_30_days,
            
                
                -- Expiry risk indicator
                ed.nearest_expiry,
                ed.days_until_expiry,
                
                -- Dynamic lead time based on sales velocity
                CASE
                    WHEN COALESCE(sd.avg_daily_sales, 0) >= 10 THEN 1
                    WHEN COALESCE(sd.avg_daily_sales, 0) >= 5 THEN 2
                    WHEN COALESCE(sd.avg_daily_sales, 0) >= 2 THEN 3
                    WHEN COALESCE(sd.avg_daily_sales, 0) > 0 THEN 5
                    ELSE 7
                END AS lead_time_days,
                
                -- Smart reorder quantity that considers expiration risk
                CASE
                    -- Dead stock: No sales in 30 days AND product exists >60 days
                    WHEN COALESCE(sd.total_sold_30, 0) = 0 
                    AND (SELECT MIN(date_added) FROM inventory WHERE prod_code = p.prod_code) < DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                    THEN 0  -- Don't reorder dead stock
                    
                    -- New product: No sales yet BUT product is new (<60 days old)
                    WHEN COALESCE(sd.total_sold_30, 0) = 0 
                    AND (SELECT MIN(date_added) FROM inventory WHERE prod_code = p.prod_code) >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                    THEN p.stock_limit  -- Give it a chance, reorder to minimum
                    
                    -- Very slow mover: <10 units sold in 30 days
                    WHEN COALESCE(sd.total_sold_30, 0) > 0 AND COALESCE(sd.total_sold_30, 0) < 10
                    THEN GREATEST(0, p.stock_limit - COALESCE(SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END), 0))  -- Only reorder to reach minimum, no buffer
                    
                    -- Has expiring stock soon: Conservative reorder
                    WHEN ed.days_until_expiry IS NOT NULL AND ed.days_until_expiry <= 14 
                    THEN GREATEST(0, p.stock_limit - COALESCE(SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END), 0))
                    
                    -- Normal case: stock_limit + (avg_daily_sales * lead_time) - current_stock
                    ELSE GREATEST(0, 
                        p.stock_limit + (
                            COALESCE(sd.avg_daily_sales, 0) * 
                            CASE
                                WHEN COALESCE(sd.avg_daily_sales, 0) >= 10 THEN 1
                                WHEN COALESCE(sd.avg_daily_sales, 0) >= 5 THEN 2
                                WHEN COALESCE(sd.avg_daily_sales, 0) >= 2 THEN 3
                                WHEN COALESCE(sd.avg_daily_sales, 0) > 0 THEN 5
                                ELSE 7
                            END
                        ) - COALESCE(SUM(CASE 
                            WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                            THEN i.stock 
                            ELSE 0 
                        END), 0)
                    )
                END AS suggested_reorder,
                
                -- Turnover rate (how many times inventory sold in 30 days)
                CASE 
                    WHEN COALESCE(SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END), 0) > 0
                    THEN ROUND(COALESCE(sd.total_sold_30, 0) / COALESCE(SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END), 1), 2)
                    ELSE NULL
                END AS turnover_rate,
                
                -- Last restocked date
                (SELECT MAX(date_added)
                FROM inventory
                WHERE prod_code = p.prod_code) AS last_restocked,
                
                -- Smart alert status
                CASE
                    -- Critical alerts
                    WHEN COALESCE(SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END), 0) = 0 THEN 'Out of Stock'
                    
                    WHEN COALESCE(SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END), 0) <= 3 THEN 'Critical Low'
                    
                    -- Expiry warnings
                    WHEN ed.days_until_expiry IS NOT NULL AND ed.days_until_expiry <= 7 
                    THEN 'Expiring Soon'
                    
                    -- Stock warnings based on sales velocity
                    WHEN COALESCE(sd.avg_daily_sales, 0) > 0 
                    AND FLOOR(COALESCE(SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END), 0) / sd.avg_daily_sales) <= 3
                    THEN 'Reorder Soon'
                    
                    WHEN COALESCE(SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END), 0) <= p.stock_limit 
                    THEN 'Below Minimum'
                    
                    -- Dead stock: No sales AND been around >60 days
                    WHEN COALESCE(sd.total_sold_30, 0) = 0 
                    AND (SELECT MIN(date_added) FROM inventory WHERE prod_code = p.prod_code) < DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                    AND COALESCE(SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END), 0) > 0
                    THEN 'Dead Stock'
                    
                    -- New product: No sales BUT recently added
                    WHEN COALESCE(sd.total_sold_30, 0) = 0 
                    AND (SELECT MIN(date_added) FROM inventory WHERE prod_code = p.prod_code) >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                    THEN 'New Product'
                    
                    -- Slow mover: Very low sales
                    WHEN COALESCE(sd.total_sold_30, 0) > 0 
                    AND COALESCE(sd.total_sold_30, 0) < 10
                    THEN 'Slow Mover'
                    
                    -- Overstocking warnings
                    WHEN COALESCE(sd.avg_daily_sales, 0) > 0 
                    AND COALESCE(SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END), 0) / sd.avg_daily_sales > 60
                    THEN 'Overstocked'
                    
                    ELSE 'Normal'
                END AS alert_status,
                
                -- Action recommendation
                CASE
                    -- Dead stock
                    WHEN COALESCE(sd.total_sold_30, 0) = 0 
                    AND (SELECT MIN(date_added) FROM inventory WHERE prod_code = p.prod_code) < DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                    AND COALESCE(SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END), 0) > 0
                    THEN 'No sales in 2+ months - Promote heavily or discontinue'
                    
                    -- New product
                    WHEN COALESCE(sd.total_sold_30, 0) = 0 
                    AND (SELECT MIN(date_added) FROM inventory WHERE prod_code = p.prod_code) >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                    THEN 'New product - Monitor performance and promote'
                    
                    -- Slow mover
                    WHEN COALESCE(sd.total_sold_30, 0) > 0 
                    AND COALESCE(sd.total_sold_30, 0) < 10
                    THEN 'Slow sales - Consider small reorders only'
                    
                    WHEN ed.days_until_expiry IS NOT NULL AND ed.days_until_expiry <= 7
                    THEN CONCAT('Promote urgently - ', ed.days_until_expiry, ' days to expiry')
                    
                    WHEN COALESCE(SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END), 0) = 0
                    THEN 'Restock immediately'
                    
                    WHEN COALESCE(sd.avg_daily_sales, 0) > 0 
                    AND FLOOR(COALESCE(SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END), 0) / sd.avg_daily_sales) <= 3
                    THEN 'Reorder within 3 days'
                    
                    ELSE 'Stock level OK'
                END AS action_recommendation
                
            FROM products p
            LEFT JOIN inventory i ON p.prod_code = i.prod_code
            LEFT JOIN categories c ON c.category_id = p.category_id
            LEFT JOIN sales_data sd ON p.prod_code = sd.prod_code
            LEFT JOIN expiry_data ed ON p.prod_code = ed.prod_code
            WHERE p.owner_id = ?
            GROUP BY 
                p.prod_code, p.name, p.stock_limit, p.prod_image, c.category,
                sd.avg_daily_sales, sd.total_sold_30, 
                ed.nearest_expiry, ed.days_until_expiry
        ", [$owner_id, $owner_id])));

    }




    public function render()
    {
        $this->stockAlertReport();
        $this->loss();
        $this->expired();

        return view('livewire.report-inventory');
    }
}
