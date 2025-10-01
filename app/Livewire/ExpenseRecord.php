<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\WithFileUploads;

class ExpenseRecord extends Component
{

    use WithFileUploads;

    public $year;
    public $month;

    public $add_expense_descri; 
    public $add_expense_category; 
    public $add_expense_amount; 
    public $add_expense_file; 

    public $addModal = false;

    public $editingId = null;
    public $editingDescri;
    public $editingAmount;

    public $fileView;

    // public $isExpired = false;


    public function mount() {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        // $this->expirationTracker();

        $this->year = now()->year;
        $this->month = now()->month;
    }

    public function previousMonth() {
        $this->month--;

        if ($this->month < 1) {
            $this->month = 12;
            $this->year--;
        }
    }

    public function nextMonth() {
        $this->month++;

        if ($this->month > 12) {
            $this->month = 1;
            $this->year++;
        }
    }



    public function render() {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner_id = Auth::guard('owner')->id();
        $year = $this->year;
        $month = $this->month;

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth   = Carbon::create($year, $month, 1)->endOfMonth();

        $startStr = $startOfMonth->toDateTimeString();
        $endStr   = $endOfMonth->toDateTimeString();

        $inputs = collect(DB::select(
            'SELECT * FROM expenses 
            WHERE owner_id = ? 
            AND expense_created BETWEEN ? AND ?
            ORDER BY expense_created DESC',
            [$owner_id, $startStr, $endStr]
        ));

        $totals = collect(DB::select("
            SELECT
                (SELECT IFNULL(SUM(e.expense_amount), 0)
                FROM expenses e
                WHERE e.owner_id = ?
                AND e.expense_created BETWEEN ? AND ?) AS expenseTotal,

                (SELECT IFNULL(SUM(p.selling_price * ri.item_quantity), 0)
                FROM receipt r
                JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
                JOIN products p ON p.prod_code = ri.prod_code
                WHERE r.owner_id = ?
                AND p.owner_id = r.owner_id
                AND r.receipt_date BETWEEN ? AND ?) AS salesTotal,

                (SELECT IFNULL(SUM(p.selling_price * d.damaged_quantity), 0)
                FROM damaged_items d
                JOIN products p ON p.prod_code = d.prod_code
                WHERE d.owner_id = ?
                AND d.damaged_date BETWEEN ? AND ?) AS lossTotal
        ", [
            $owner_id, $startOfMonth, $endOfMonth, 
            $owner_id, $startOfMonth, $endOfMonth, 
            $owner_id, $startOfMonth, $endOfMonth,
        ]))->first();

        $topCategory = collect(DB::select(
            "SELECT 
            IFNULL(c.category, 'Nothing') as category_name,
            SUM(p.selling_price * ri.item_quantity) AS category_sales,
                (SUM(p.selling_price * ri.item_quantity) / 
                    (SELECT SUM(p2.selling_price * ri2.item_quantity)
                    FROM receipt r2
                    JOIN receipt_item ri2 ON r2.receipt_id = ri2.receipt_id
                    JOIN products p2 ON ri2.prod_code = p2.prod_code
                    WHERE p2.owner_id = ?
                    AND YEAR(r2.receipt_date) = ?
                    AND MONTH(r2.receipt_date) = ?
                    )
                ) * 100 AS category_percentage
            FROM receipt r
            JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
            JOIN products p ON ri.prod_code = p.prod_code
            JOIN categories c ON p.category_id = c.category_id
            WHERE p.owner_id = ?
            AND YEAR(r.receipt_date) = ?
            AND MONTH(r.receipt_date) = ?
            GROUP BY c.category
            ORDER BY category_percentage DESC
            LIMIT 3", [$owner_id, $this->year, $this->month, $owner_id, $this->year, $this->month]
        ))->toArray();

        $topExpense = collect(DB::select(
            "SELECT 
                IFNULL(e.expense_category, 'Nothing') as category_name,
                (
                    IFNULL(SUM(e.expense_amount), 0) /
                    NULLIF((
                        SELECT SUM(e2.expense_amount)
                        FROM expenses e2
                        WHERE e2.owner_id = ?
                        AND YEAR(e2.expense_created) = ?
                        AND MONTH(e2.expense_created) = ?
                    ), 0)
                ) * 100 AS expense_percentage
            FROM expenses e
            WHERE e.owner_id = ?
            AND YEAR(e.expense_created) = ?
            AND MONTH(e.expense_created) = ?
            GROUP BY e.expense_category
            ORDER BY expense_percentage DESC
            LIMIT 3", [$owner_id, $this->year, $this->month, $owner_id, $this->year, $this->month]
        ))->toArray();

        if (empty($topExpense)) {
            $topExpense = [];
        }

        $highestEarn = collect(DB::select(
            'SELECT 
                IFNULL(SUM(p.selling_price * ri.item_quantity), 0) AS salesTotal,
                DATE(r.receipt_date) as dayTotal
            FROM receipt r
            JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
            JOIN products p ON p.prod_code = ri.prod_code
            WHERE r.owner_id = ?
            AND p.owner_id = r.owner_id
            AND YEAR(r.receipt_date) = ?
            AND MONTH(r.receipt_date) = ?
            GROUP BY DATE(r.receipt_date)
            ORDER BY DATE(r.receipt_date) DESC
            LIMIT 3', [$owner_id, $this->year, $this->month]
        ))->toArray();

        $dateDisplay = Carbon::now('Asia/Manila');

        return view('livewire.expense-record', [
            'inputs' => $inputs,
            'totals' => $totals,
            'dateDisplay' => $dateDisplay,
            'topCategory' => $topCategory,
            'topExpense' => $topExpense,
            'highestEarn' => $highestEarn,
            'year' => $this->year,
        ]);
    }




    public function addModalOpen() {
        $this->addModal = true;
    }

    public function closeModal() {
        $this->addModal = false;
    }


    public function addExpenses() {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner_id = Auth::guard('owner')->user()->owner_id;

        $validated = $this->validate([
            'add_expense_descri'   => 'required|string|max:255',
            'add_expense_category' => 'required|string|max:100',
            'add_expense_amount'   => 'required|numeric|min:0.01',
            'add_expense_file'     => 'nullable|file|mimes:pdf,docx,jpg,jpeg,png|max:15120',
        ]);

        $filePath = null;

        if ($this->add_expense_file) {
            $filePath = $this->add_expense_file->store('expenses', 'public');
        }

        DB::insert('
            INSERT INTO expenses 
            (expense_descri, expense_category, expense_amount, file_path, expense_created, owner_id) 
            VALUES (?, ?, ?, ?, NOW(), ?)
        ', [
            $validated['add_expense_descri'],
            $validated['add_expense_category'],
            $validated['add_expense_amount'],
            $filePath,
            $owner_id,
        ]);


        $this->reset(['add_expense_descri', 'add_expense_category', 'add_expense_amount', 'add_expense_file']);
        $this->addModal = false;

        session()->flash('success', 'Expense added successfully!');
    }

    public function viewAttachment($expense_id) {

        $fileView = collect(DB::select(
            "select file_path from expenses
            where expense_id = ?", [$expense_id]
        ))->first();

        if (!$fileView || !$fileView->attachment) {
            return response('No attachment available.', 404);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_buffer($finfo, $fileView->attachment);
        finfo_close($finfo);

        return response($fileView->attachment)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline; filename="attachment"');
    }





    public function editExpense($expense_id)
    {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner_id = Auth::guard('owner')->user()->owner_id;
        
        $expense = collect(DB::select("
            select * from expenses
            where expense_id = ?
            and owner_id = ?", [$expense_id, $owner_id]))->first();

        if ($expense) {
            $this->editingId = $expense->expense_id;
            $this->editingDescri = $expense->expense_descri;
            $this->editingAmount = $expense->expense_amount;
        }
    }


    public function saveExpense()
    {
        $this->validate([
            'editingDescri' => 'required|string|max:255',
            'editingAmount' => 'required|numeric|min:0.01',
        ]);

        DB::table('expenses')
            ->where('expense_id', $this->editingId)
            ->where('owner_id', Auth::guard('owner')->id())
            ->update([
                'expense_descri' => $this->editingDescri,
                'expense_amount' => $this->editingAmount,
            ]);

        $this->reset(['editingId', 'editingDescri', 'editingAmount']);

        session()->flash('success', 'Expense updated successfully!');
    }

}





