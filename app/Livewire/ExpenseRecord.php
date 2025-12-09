<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use App\Http\Controllers\ActivityLogController;

class ExpenseRecord extends Component
{

    use WithFileUploads;

    public $year;
    public $month;

    public $descriptionError;
    public $amountError;
    public $fileError;

    public $add_expense_descri = ''; 
    public $add_expense_category = ''; 
    public $add_expense_amount = ''; 
    public $add_expense_file = null;

    public $addModal = false;

    public $editingId = null;
    public $editingDescri;
    public $editingAmount;

    public $editingDescriptionError;
    public $editingAmountError;

    public $fileView;

    // public $isExpired = false;


    public function mount() {
        if (!Auth::guard('owner')->check()) {
            abort(403, 'Unauthorized access.');
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

        DB::connection()->getPdo()->exec("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");

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
                -- total expenses for the month
                IFNULL((SELECT SUM(e.expense_amount)
                        FROM expenses e
                        WHERE e.owner_id = ?
                        AND e.expense_created BETWEEN ? AND ?), 0) AS expenseTotal,

                -- total sales for the month
                IFNULL((
                    SELECT SUM(x.item_sales - x.discount_amount)
                    FROM (
                        SELECT r.receipt_id, r.discount_amount,
                            SUM(ri.item_quantity * 
                                COALESCE(
                                    (SELECT ph.old_selling_price
                                        FROM pricing_history ph
                                        WHERE ph.prod_code = ri.prod_code
                                        AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                        ORDER BY ph.effective_from DESC LIMIT 1),
                                    p.selling_price
                                ) - ri.item_discount_amount
                            ) AS item_sales
                        FROM receipt r
                        JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
                        JOIN products p ON p.prod_code = ri.prod_code
                        WHERE r.owner_id = ? AND r.receipt_date BETWEEN ? AND ?
                        GROUP BY r.receipt_id
                    ) x
                ), 0) AS salesTotal,

                -- total damaged items loss for the month
                IFNULL((SELECT SUM(d.damaged_quantity * p.selling_price)
                        FROM damaged_items d
                        JOIN inventory i ON i.inven_code = d.inven_code
                        JOIN products p ON i.prod_code = p.prod_code
                        WHERE d.owner_id = ?
                        AND d.damaged_date BETWEEN ? AND ?
                        AND (d.set_to_return_to_supplier IS NULL OR d.set_to_return_to_supplier = 'Damaged')
                ), 0) AS lossTotal
        ", [
            $owner_id, $startOfMonth, $endOfMonth,  // expenses
            $owner_id, $startOfMonth, $endOfMonth,  // sales
            $owner_id, $startOfMonth, $endOfMonth,  // losses
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
            ORDER BY SUM(p.selling_price * ri.item_quantity) DESC
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




    public function updated($propertyName, $value) {
        
        if ($propertyName === 'add_expense_descri') {
            
            if (empty($value)) {
                $this->descriptionError = 'Cannot be empty';
                return;
            }

            if (preg_match('/^\d+$/', $value)) {
                $this->descriptionError = 'Purpose cannot contain only numbers.';
                return;
            }
            
            if (preg_match('/^[^a-zA-Z0-9]+$/', $value)) {
                $this->descriptionError = 'Purpose cannot contain only symbols.';
                return;
            }

            if (!preg_match('/[a-zA-Z]/', $value)) {
                $this->descriptionError = 'Purpose must contain at least one letter.';
                return;
            }
            
            if (!preg_match('/^[a-zA-Z0-9\s_&*()\/%#]*$/', $value)) {
                $this->descriptionError = 'Purpose should only contain letters, numbers, spaces, and symbols: # _ & * ( ) / %';
                return;
            }

            $this->descriptionError = '';
        }

        
        if ($propertyName === 'add_expense_amount') {

            $value = floatval($value);

            if ($value <= 0) {
                $this->amountError = 'Invalid amount. Must be a positive number.';
                return;
            }

            $decimalPlaces = strlen(substr(strrchr((string)$value, "."), 1) ?? '');
            if ($decimalPlaces > 2) {
                $this->amountError = 'Invalid amount. Only up to 2 decimal places allowed.';
                return;
            }

            $this->amountError = '';
        }

        if ($propertyName === 'add_expense_file') {

            if ($this->add_expense_file) {
                $fileSize = $this->add_expense_file->getSize();
                $maxSize = 300 * 1024;
                
                if ($fileSize > $maxSize) {
                    $this->fileError = 'File exceeds 300KB.';
                    return;
                    // $this->add_expense_file = null; 
                } else {
                    $this->fileError = '';
                }
            } else {
                $this->fileError = '';
            }
        }

        if ($propertyName === 'editingDescri') {

            if (empty($value)) {
                $this->editingDescriptionError = 'Cannot be empty';
                return;
            }

            if (preg_match('/^\d+$/', $value)) {
                $this->editingDescriptionError = 'Purpose cannot contain only numbers.';
                return;
            }
            
            if (preg_match('/^[^a-zA-Z0-9]+$/', $value)) {
                $this->editingDescriptionError = 'Purpose cannot contain only symbols.';
                return;
            }

            if (!preg_match('/[a-zA-Z]/', $value)) {
                $this->editingDescriptionError = 'Purpose must contain at least one letter.';
                return;
            }
            
            if (!preg_match('/^[a-zA-Z0-9\s_&*()\/%#]*$/', $value)) {
                $this->editingDescriptionError = 'Purpose should only contain letters, numbers, spaces, and symbols: # _ & * ( ) / %';
                return;
            }

            $this->editingDescriptionError = '';
        }

        if ($propertyName === 'editingAmount') {


            $value = floatval($value);

            if ($value <= 0) {
                $this->editingAmountError = 'Invalid amount. Must be a positive number.';
                return;
            }

            $decimalPlaces = strlen(substr(strrchr((string)$value, "."), 1) ?? '');
            if ($decimalPlaces > 2) {
                $this->editingAmountError = 'Invalid amount. Only up to 2 decimal places allowed.';
                return;
            }

            $this->editingAmountError = '';

            $value = floatval($value);

        }

    }



    public function addModalOpen() {
        $this->addModal = true;
    }

     public function closeModal() {
        $this->addModal = false;
    }

    public function cancelModal() {
        
        $this->addModal = false;

        $this->add_expense_descri = '';
        $this->add_expense_category = '';
        $this->add_expense_amount = '';
        $this->add_expense_file = '';


    }


    public function addExpenses() {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner_id = Auth::guard('owner')->user()->owner_id;

        if($this->descriptionError === '') {

            $validated = $this->validate([
                'add_expense_descri'   => 'required|string|max:255',
                'add_expense_category' => 'required|string|max:100',
                'add_expense_amount'   => 'required|numeric|min:0.01',
                'add_expense_file'     => 'nullable|file|mimes:pdf,docx,jpg,jpeg,png|max:300',
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

            $user = Auth::guard('owner')->user();
            $ip = request()->ip();

            ActivityLogController::log(
                'Added expense record: ' . $validated['add_expense_descri'],
                'owner',
                $user,
                $ip
            );


            session()->flash('success', 'Expense added successfully!');
        } 


    }





    public function viewAttachment($expense_id) {

        $fileView = collect(DB::select(
            "select file_path from expenses
            where expense_id = ?", [$expense_id]
        ))->first();

        if (!$fileView || !$fileView->file_path) {
            return response('No attachment available.', 404);
        }

        $path = storage_path('app/public/' . $fileView->file_path);

        if (!file_exists($path)) {
            return response('File not found.', 404);
        }

        $mime = mime_content_type($path);

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    public function editExpense($expense_id) {
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


    public function saveExpense() {
        if($this->editingDescriptionError === '' || $this->editingAmountError === '') { 

            $this->validate([
                'editingDescri' => 'required|string|max:255',
                'editingAmount' => 'required|numeric|min:0.01',
            ]);

            $ownerId = Auth::guard('owner')->id();

            // $oldExpense = DB::selectOne("
            //     SELECT * FROM expenses
            //     WHERE expense_id = :expense_id
            //     AND owner_id = :owner_id
            // ", [
            //     'expense_id' => $this->editingId,
            //     'owner_id' => $ownerId
            // ]);

            // if (!$oldExpense) {
            //     session()->flash('error', 'Expense not found.');
            //     return;
            // }

            // // Determine hist_type_of_edit
            // $histType = '';
            // if ($oldExpense->expense_descri !== $this->editingDescri && $oldExpense->expense_amount != $this->editingAmount) {
            //     $histType = 'edited both';
            // } elseif ($oldExpense->expense_descri !== $this->editingDescri) {
            //     $histType = 'edited the purpose';
            // } elseif ($oldExpense->expense_amount != $this->editingAmount) {
            //     $histType = 'edited the amount';
            // }

            // Insert into expense_record_history using DB::insert
            // DB::insert("
            //     INSERT INTO expense_record_history
            //         (expense_id, hist_type_of_edit, hist_old_info, hist_date_edited)
            //     VALUES
            //         (:expense_id, :hist_type, :hist_old_info, NOW())
            // ", [
            //     'expense_id' => $this->editingId,
            //     'hist_type' => $histType,
            //     'hist_old_info' => 'expense_descri: '.$oldExpense->expense_descri.', expense_amount: '.$oldExpense->expense_amount
            // ]);

            // Update the expense
            DB::update("
                UPDATE expenses 
                SET expense_descri = :descri, 
                    expense_amount = :amount 
                WHERE expense_id = :expense_id 
                AND owner_id = :owner_id
            ", [
                'descri' => $this->editingDescri,
                'amount' => $this->editingAmount,
                'expense_id' => $this->editingId,
                'owner_id' => $ownerId
            ]);

            $this->reset(['editingId', 'editingDescri', 'editingAmount', 'editingDescriptionError', 'editingAmountError']);

            session()->flash('success', 'Expense updated successfully!');
        }
    }


}





