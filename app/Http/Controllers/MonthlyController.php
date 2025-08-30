<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlyController extends Controller
{ 
    public function index(Request $request)
    {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;

        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        if ($month < 1) {
            $month = 12;
            $year--;
        }
        if ($month > 12) {
            $month = 1;
            $year++;
        }

        $inputs = collect(DB::select(
            "SELECT * FROM expenses 
            WHERE MONTH(expense_created) = ? 
            AND YEAR(expense_created) = ? 
            AND owner_id = ?
            ORDER BY expense_created DESC",
            [$month, $year, Auth::guard('owner')->id()]
        ));

        $expenseTotal = collect(DB::select("SELECT SUM(expense_amount) as expenseTotal FROM expenses WHERE MONTH(expense_created) = ? AND YEAR(expense_created) = ? AND owner_id = ?", [$month, $year, $owner_id]))->first();
        $salesTotal = collect(DB::select(
            "SELECT 
                IFNULL(SUM(p.selling_price * ri.item_quantity), 0) AS salesTotal
            FROM receipt r
            JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
            JOIN products p ON p.prod_code = ri.prod_code
            WHERE r.owner_id = ?
            AND p.owner_id = r.owner_id
            AND YEAR(r.receipt_date) = ?
            AND MONTH(r.receipt_date) = ?;", [$owner_id, $year, $month]))->first();  

        $lossTotal =  collect(DB::select("SELECT IFNULL(SUM(p.selling_price * d.damaged_quantity), 0) as lossTotal FROM damaged_items d JOIN products p ON p.prod_code = d.prod_code
        WHERE MONTH(damaged_date) = ? AND YEAR(damaged_date) = ? AND d.owner_id = ?", [$month, $year, $owner_id]))->first(); 
        
        $topCategory = collect(DB::select(
            "SELECT 
                MONTH(r.receipt_date) AS month,
                c.category as category_name,
                IFNULL(SUM(p.selling_price * ri.item_quantity), 0) AS topCategory
            FROM receipt r
            JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
            JOIN products p ON p.prod_code = ri.prod_code
            JOIN categories c ON p.category_id = c.category_id
            WHERE r.owner_id = 3
                AND p.owner_id = r.owner_id
                AND YEAR(r.receipt_date) = 2025
                AND MONTH(r.receipt_date) = 7
            GROUP BY p.category_id, MONTH(r.receipt_date), c.category
            ORDER BY topCategory DESC
            LIMIT 1;", ))->first(); 

        $dateDisplay = Carbon::now('Asia/Manila');

        return view('dashboards.owner.monthly_profit_add', 
        [
            'month' => $month,
            'year' => $year,
            'inputs' => $inputs,
            'now' => $dateDisplay,
            'expenseTotal' => $expenseTotal,
            'salesTotal' => $salesTotal,
            'lossTotal' => $lossTotal,
            'topCategory' => $topCategory,
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
            'expense_category' => 'required|string|max:100',
            'expense_amount' => 'required|numeric|min:0.01',
            'attachment' => 'nullable|file|mimes:pdf,docx,jpg,jpeg,png|max:15120', 
        ]);

        
        $attachmentData = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentData = file_get_contents($file->getRealPath());
        }

        
        DB::insert('
            INSERT INTO expenses 
            (expense_descri, expense_category, expense_amount, attachment, expense_created, owner_id) 
            VALUES (?, ?, ?, ?, NOW(), ?)
        ', [
            $validated['expense_descri'],
            $validated['expense_category'],
            $validated['expense_amount'],
            $attachmentData,
            $owner_id,
        ]);

        return redirect()->back()->with('success', 'Expense added successfully!');
    }



    public function edit(Request $request, $expense_id = null)
    {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        if (!$expense_id) {
            $expense_id = $request->input('expense_id');
        }
        if (!$expense_id) {
            return back()->with('error', 'Missing expense_id.');
        }

        $owner_id = Auth::guard('owner')->id();

        $validated = $request->validate([
            'expense_descri' => 'required|string|max:255',
            'expense_amount' => 'required|numeric|min:0.01',
        ]);

        $updated = DB::update("
            UPDATE expenses
            SET expense_descri = ?, expense_amount = ?
            WHERE expense_id = ? AND owner_id = ?
        ", [
            $validated['expense_descri'],
            $validated['expense_amount'],
            $expense_id,
            $owner_id
        ]);

        return back()->with($updated ? 'success' : 'error', $updated ? 'Expense edited successfully!' : 'Nothing updated or not found.');
    }


    public function viewAttachment($expense_id)
    {
        $expense = DB::table('expenses')->where('expense_id', $expense_id)->first();

        if (!$expense || !$expense->attachment) {
            return response('No attachment available.', 404);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_buffer($finfo, $expense->attachment);
        finfo_close($finfo);

        return response($expense->attachment)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline; filename="attachment"');
    }




}


?>