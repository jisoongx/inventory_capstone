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


    public function stockAlert()
    {
        $ownerLoggedIn = Auth::guard('owner')->check();
        $staffLoggedIn = Auth::guard('staff')->check();

        if ($ownerLoggedIn) {
            $owner_id = Auth::guard('owner')->user()->owner_id;

        } elseif ($staffLoggedIn) {
            $owner_id = Auth::guard('staff')->user()->owner_id;

        } else {
            abort(403, 'Unauthorized access.');
        }

        $results = DB::select("
            SELECT 
                v.prod_code,
                v.name AS prod_name,
                p.prod_image,
                v.current_stock as total_stock,
                v.safety_stock,
                v.reorder_point,
                v.inventory_status as stock_status
            FROM vw_inventory_status v
            JOIN products p ON p.prod_code = v.prod_code
            WHERE p.owner_id = ?
            AND p.prod_status = 1
            AND v.inventory_status IN ('Critical', 'Warning', 'Out of Stock')
            ORDER BY v.current_stock ASC
        ", [$owner_id]);

        $this->prod = collect($results)->map(function ($item) {
            $item->image_url = asset('storage/' . ltrim($item->prod_image, '/'));
            return $item;
        });
    }




    public function expirationNotice() {
        
        DB::connection()->getPdo()->exec("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");

        $results = DB::select("
            select * from vw_expiration_status
        ");

        $this->expiry = collect($results)->map(function ($item) {
            $item->image_url = asset('storage/' . ltrim($item->prod_image, '/'));
            return $item;
        });
    }


    public function topSelling() {
        
        $ownerLoggedIn = Auth::guard('owner')->check();
        $staffLoggedIn = Auth::guard('staff')->check();

        if ($ownerLoggedIn) {
            $owner_id = Auth::guard('owner')->user()->owner_id;

        } elseif ($staffLoggedIn) {
            $owner_id = Auth::guard('staff')->user()->owner_id;

        } else {
            abort(403, 'Unauthorized access.');
        }

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
