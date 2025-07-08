<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Session::get('authenticated')) {
            return redirect('/login')->with('error', 'Please login first.');
        }

        $owner_id = Session::get('owner_id');
        $owner_name = Session::get('owner_name');

        $profits = collect(DB::select("SELECT month, total FROM monthly_profit ORDER BY month_id"))
                    ->pluck('total')
                    ->toArray();

        $months = collect(DB::select("SELECT month FROM monthly_profit ORDER BY month_id"))
                ->pluck('month')
                ->toArray();

        $expenses = collect(DB::select("SELECT MONTH(expense_created) AS expense_month, SUM(expense_amount) AS expense_total
                                FROM expenses GROUP BY MONTH(expense_created) ORDER BY MONTH(expense_created)"))
                    ->pluck('expense_total')
                    ->toArray();
 

        return view('dashboard', compact('owner_id', 'owner_name', 'profits', 'months', 'expenses'));
    }
}
