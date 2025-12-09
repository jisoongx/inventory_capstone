<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffDashboard extends Component
{

    public $dailySales;
    public $weeklySales;
    public $monthSales;
    public $ownCurrentSales;

    public $dateDisplay;
    public $staff_name;
    
    public $prod; 
    public $expiry;
    public $topProd;
    

    public function dashboard() {

        if (!Auth::guard('staff')->check()) {
            abort(403, 'Unauthorized access.');
        }

        $staff = Auth::guard('staff')->user();
        $staff_id = $staff->staff_id;
        $this->staff_name = $staff->firstname;

        $latestYear = now()->year;
        $currentMonth = (int)date('n');
        $day = now()->format('Y-m-d');
        $this->dateDisplay = Carbon::now('Asia/Manila');



        $owner_id = collect(DB::select('
            select o.owner_id
            from staff s
            join owners o on s.owner_id = o.owner_id
            where s.staff_id = ?
        ', [$staff_id]))->first()->owner_id ?? null;


        $this->dailySales = collect(DB::select('
            select ifnull(sum(p.selling_price * ri.item_quantity), 0) as dailySales
            from receipt r
            join receipt_item ri on r.receipt_id = ri.receipt_id
            join products p on ri.prod_code = p.prod_code
            where date(receipt_date) = ?
            and r.owner_id = ?
        ', [$day, $owner_id]))->first();

        $this->weeklySales = collect(DB::select('
            SELECT IFNULL(SUM(p.selling_price * ri.item_quantity), 0) AS weeklySales
            FROM receipt r
            JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
            JOIN products p ON ri.prod_code = p.prod_code
            WHERE r.receipt_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
            AND r.owner_id = ?
        ', [$owner_id]))->first();

        $this->monthSales = collect(DB::select('
            SELECT IFNULL(SUM(p.selling_price * ri.item_quantity), 0) AS monthSales
            FROM receipt r
            JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
            join products p on ri.prod_code = p.prod_code
            where month(receipt_date) = ?
            AND r.owner_id = ?
            AND year(receipt_date) = ?
        ', [$currentMonth, $owner_id, $latestYear]))->first();

        $this->ownCurrentSales = collect(DB::select("
            select ifnull(sum(p.selling_price * ri.item_quantity), 0) as ownDailySales
            from receipt r
            join receipt_item ri on r.receipt_id = ri.receipt_id
            join products p on ri.prod_code = p.prod_code
            where date(receipt_date) = ?
            and r.staff_id = ?
        ", [$day, $staff_id]))->first();
    }

    public function stockAlert() {

        if (!Auth::guard('staff')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $staff = Auth::guard('staff')->user();
        $staff_id = $staff->staff_id;

        $owner_id = collect(DB::select('
            select o.owner_id
            from staff s
            join owners o on s.owner_id = o.owner_id
            where s.staff_id = ?
        ', [$staff_id]))->first()->owner_id ?? null;

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
        if (!Auth::guard('staff')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $staff = Auth::guard('staff')->user();
        $staff_id = $staff->staff_id;

        $owner_id = collect(DB::select('
            select o.owner_id
            from staff s
            join owners o on s.owner_id = o.owner_id
            where s.staff_id = ?
        ', [$staff_id]))->first()->owner_id ?? null;

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

        if (!Auth::guard('staff')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $staff = Auth::guard('staff')->user();
        $staff_id = $staff->staff_id;

        $owner_id = collect(DB::select('
            select o.owner_id
            from staff s
            join owners o on s.owner_id = o.owner_id
            where s.staff_id = ?
        ', [$staff_id]))->first()->owner_id ?? null;

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
        $this->dashboard();
        return view('livewire.staff-dashboard');
    }
}
