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

    public $dateDisplay;

    public $staff_name;

    public function mount() {
        $this->dash();
    }

    public function dash() {
        if (!Auth::guard('staff')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $staff = Auth::guard('staff')->user();
        $staff_id = $staff->staff_id;
        $this->staff_name = $staff->firstname;

        $latestYear = now()->year;
        $currentMonth = (int)date('n');
        $day = now()->format('Y-m-d');
        $this->dateDisplay = Carbon::now('Asia/Manila');



        $getOwner_id = collect(DB::select('
            select s.staff_id, o.owner_id
            from staff s
            join owners o on s.owner_id = o.owner_id
            where s.staff_id = ?
        ', [$staff_id]));


        $this->dailySales = collect(DB::select('
            select ifnull(sum(p.selling_price * ri.item_quantity), 0) as dailySales
            from receipt r
            join receipt_item ri on r.receipt_id = ri.receipt_id
            join products p on ri.prod_code = p.prod_code
            where date(receipt_date) = ?
            and r.owner_id = ?
        ', [$day, $getOwner_id]))->first();

        $this->weeklySales = collect(DB::select('
            SELECT IFNULL(SUM(p.selling_price * ri.item_quantity), 0) AS weeklySales
            FROM receipt r
            JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
            JOIN products p ON ri.prod_code = p.prod_code
            WHERE r.receipt_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
            AND r.owner_id = ?
        ', [$getOwner_id]))->first();

        $this->monthSales = collect(DB::select('
            SELECT IFNULL(SUM(p.selling_price * ri.item_quantity), 0) AS monthSales
            FROM receipt r
            JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
            join products p on ri.prod_code = p.prod_code
            where month(receipt_date) = ?
            AND r.owner_id = ?
            AND year(receipt_date) = ?
        ', [$currentMonth, $getOwner_id, $latestYear]))->first();
    }


    public function render()
    {
        return view('livewire.staff-dashboard');
    }
}
