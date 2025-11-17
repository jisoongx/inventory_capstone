<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAlert extends Component
{
    public $prod; 
    public $expiry;
    public $topProd;


    public function stockAlert() {

        $owner_id = Auth::guard('owner')->user()->owner_id;

        $results = DB::select("
            SELECT p.name AS prod_name, p.prod_image, p.stock_limit,
                
                SUM(i.stock) AS total_stock,
                
                -- Usable stock (not expired)
                SUM(CASE 
                    WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                    THEN i.stock 
                    ELSE 0 
                END) AS remaining_stock,
                
                -- Expired stock
                SUM(CASE 
                    WHEN i.expiration_date <= CURDATE() 
                    THEN i.stock 
                    ELSE 0 
                END) AS expired_stock,
                
                CASE
                    WHEN SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END) = 0 THEN 'Critical'
                    WHEN SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END) <= 3 THEN 'Critical'
                    WHEN SUM(CASE 
                        WHEN i.expiration_date IS NULL OR i.expiration_date > CURDATE() 
                        THEN i.stock 
                        ELSE 0 
                    END) <= p.stock_limit THEN 'Reorder'
                    ELSE 'Normal'
                END AS status
                
            FROM products p
            JOIN inventory i ON p.prod_code = i.prod_code
            WHERE p.owner_id = ?
                AND p.prod_status = 'active'
            GROUP BY p.prod_code, p.name, p.stock_limit, p.prod_image
            HAVING status IN ('Critical', 'Reorder')
            ORDER BY remaining_stock ASC
        ", [$owner_id]);



        $this->prod = collect($results)->map(function ($item) {
            $item->image_url = asset('storage/' . ltrim($item->prod_image, '/'));
            return $item;
        });

    }


    public function expirationNotice() {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        $results = DB::select("
            SELECT 
                p.name AS prod_name, i.stock as expired_stock, p.prod_image,
                i.expiration_date, i.batch_number,
                CASE 
                    WHEN i.expiration_date IS NULL THEN NULL
                    ELSE DATEDIFF(i.expiration_date, CURDATE())
                END AS days_until_expiry,
                CASE
                    WHEN i.expiration_date IS NULL THEN 'No Expiry'
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 0 THEN 'Expired'
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 7 THEN 'Critical'
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 30 THEN 'Warning'
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 60 THEN 'Monitor'
                    ELSE 'Safe'
                END AS status
            FROM inventory i
            JOIN products p ON i.prod_code = p.prod_code
            WHERE p.owner_id = ?
                AND i.expiration_date IS NOT NULL 
                AND DATEDIFF(i.expiration_date, CURDATE()) BETWEEN 0 AND 60
                and i.stock > 0
            ORDER BY days_until_expiry ASC;
        ", [$owner_id]);

        $this->expiry = collect($results)->map(function ($item) {
            $item->image_url = asset('storage/' . ltrim($item->prod_image, '/'));
            return $item;
        });
    }


    public function topSelling() {

        $owner_id = Auth::guard('owner')->user()->owner_id;
        $month = now()->month;
        $year = now()->year;

        $results = collect(DB::select("
            SELECT 
                p.name AS prod_name, 
                p.prod_code, 
                p.prod_image, 
                SUM(ri.item_quantity * p.selling_price) as total_sales,
                SUM(ri.item_quantity) AS unit_sold
            FROM receipt r
            JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
            JOIN products p ON ri.prod_code = p.prod_code 
            WHERE 
                r.owner_id = ?
                AND MONTH(r.receipt_date) = ?
                AND YEAR(r.receipt_date) = ?
            GROUP BY 
                p.prod_code, 
                p.name, 
                p.prod_image
            ORDER BY 
                total_sales DESC
            Limit 10
        ", [$owner_id, $month, $year] ));

         $this->topProd = collect($results)->map(function ($item) {
            $item->image_url = asset('storage/' . ltrim($item->prod_image, '/'));
            return $item;
        });

    }


    public function pollAll() {
        $this->stockAlert();
        $this->expirationNotice();
        $this->topSelling();
    }


    public function render()
    {   
        $this->pollAll();
        return view('livewire.stock-alert');
    }
}
