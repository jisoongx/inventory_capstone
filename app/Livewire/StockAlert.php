<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAlert extends Component
{
    public $prod; 
    public $showAll = false;

    public $expiry;

    public $topProd;

    public function toggleViewAll()
    {
        $this->showAll = !$this->showAll;
    }

    public function stockAlert() {

        $owner_id = Auth::guard('owner')->user()->owner_id;

        $results = DB::select("
            SELECT 
                p.name AS prod_name,
                p.prod_image, 
                p.stock_limit,
                sum(i.stock) AS remaining_stock,
                CASE
                    WHEN sum(i.stock) <= 3 THEN 'Critical'
                    WHEN sum(i.stock) <= p.stock_limit THEN 'Reorder'
                    ELSE 'Normal'
                END AS status
            FROM products p
            JOIN inventory i ON p.prod_code = i.prod_code
            WHERE p.owner_id = ?
            GROUP BY p.prod_code, p.name, p.stock_limit, p.prod_image
            Having status in ('Critical', 'Reorder')
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
                i.expiration_date,
                DATEDIFF(i.expiration_date, CURDATE()) AS days_until_expiry,
                CASE
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 0 THEN 'Expired'
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 7 THEN 'Critical'
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 30 THEN 'Warning'
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 60 THEN 'Monitor'
                    ELSE 'Safe'
                END AS status
            FROM inventory i
            JOIN products p ON i.prod_code = p.prod_code
            WHERE p.owner_id = ?
                AND DATEDIFF(i.expiration_date, CURDATE()) <= 60
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
                SUM(ri.item_quantity * p.selling_price) AS total_sales,
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
                total_sales DESC;
        ", [$owner_id, $month, $year] ));

         $this->topProd = collect($results)->map(function ($item) {
            $item->image_url = asset('storage/' . ltrim($item->prod_image, '/'));
            return $item;
        });

    }


    public function render()
    {
        $this->stockAlert();
        $this->expirationNotice();
        $this->topSelling();
        return view('livewire.stock-alert');
    }
}
