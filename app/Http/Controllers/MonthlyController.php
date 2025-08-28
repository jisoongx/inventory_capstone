<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlyController extends Controller
{ 
    public function index(){
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;

        $latestYear = collect(DB::select("
            SELECT MAX(YEAR(expense_created)) as latestYear FROM expenses
        "))->pluck('latestYear')->first();

        $latestMonth = collect(DB::select("
            SELECT MAX(MONTH(expense_created)) as latestMonth FROM expenses WHERE YEAR(expense_created) = ?  AND owner_id = ?
        ", [$latestYear, $owner_id]))->pluck('latestMonth')->first();

        $inputs = collect(DB::select("SELECT * FROM expenses WHERE MONTH(expense_created) = ? AND YEAR(expense_created) = ? AND owner_id = ? order by expense_created desc",  [$latestMonth, $latestYear, $owner_id]));

        $expenseTotal = collect(DB::select("SELECT SUM(expense_amount) as expenseTotal FROM expenses WHERE MONTH(expense_created) = ? AND YEAR(expense_created) = ? AND owner_id = ?", [$latestMonth, $latestYear, $owner_id]))->first();
        $salesTotal = collect(DB::select("SELECT SUM(receipt_total) as salesTotal FROM receipt WHERE MONTH(receipt_date) = ? AND YEAR(receipt_date) = ? AND owner_id = ?", [$latestMonth, $latestYear, $owner_id]))->first();  

        $dateDisplay = Carbon::now('Asia/Manila');

        return view('dashboards.owner.monthly_profit_add', 
        [
            'latestMonth' => $latestMonth,
            'inputs' => $inputs,
            'expenseTotal' => $expenseTotal,
            'now' => $dateDisplay,
            'salesTotal' => $salesTotal,
        ]);

    }

    public function add(Request $request)
    {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;

        $validated = $request->validate([
            'expense_descri' => 'required|string|max:255',
            'expense_amount' => 'required|numeric|min:0.01',
        ]);

        DB::insert('
            INSERT INTO expenses (expense_descri, expense_amount, expense_created, owner_id) 
            VALUES (?, ?, NOW(), ?)
        ', [
            $validated['expense_descri'],
            $validated['expense_amount'],
            $owner_id,
        ]);

        return redirect()->back()->with('success', 'Expense added successfully!');
    }

    public function edit(Request $request, $expense_id)
    {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner_id = Auth::guard('owner')->id();

        $validated = $request->validate([
            'expense_descri' => 'required|string|max:255',
            'expense_amount' => 'required|numeric|min:0.01',
        ]);

        DB::update("
            UPDATE expenses
            SET expense_descri = ?, expense_amount = ?
            WHERE expense_id = ? AND owner_id = ?
        ", [
            $validated['expense_descri'],
            $validated['expense_amount'],
            $expense_id,
            $owner_id
        ]);

        return redirect()->back()->with('success', 'Expense edited successfully!');
    }




}


?>