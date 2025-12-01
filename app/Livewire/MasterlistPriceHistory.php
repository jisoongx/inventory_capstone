<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterlistPriceHistory extends Component
{
    public $history;
    public $years;
    public $monthNames;

    public $searchWord;
    

    public function updateSearch() {
        $this->resetPage();
    }

    public function updateMonth() {
        $this->resetPage();
    }

    public function historyList() {

        DB::connection()->getPdo()->exec("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");

        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;

        $search = '%' . $this->searchWord . '%';

        $this->history = collect(DB::select("
            SELECT
                ph.price_history_id,
                ph.prod_code,
                ph.old_cost_price,
                ph.old_selling_price,
                ph.effective_from,
                ph.effective_to,

                i.inven_code,
                i.batch_number,
                p.name as prod_name,

                (i.stock
                    + COALESCE((
                        SELECT SUM(ri.item_quantity)
                        FROM receipt_item ri
                        WHERE ri.inven_code = i.inven_code
                    ), 0)
                    + COALESCE((
                        SELECT SUM(d.damaged_quantity)
                        FROM damaged_items d
                        WHERE d.inven_code = i.inven_code
                    ), 0)
                ) AS batch_received,

                COALESCE((
                    SELECT SUM(ri.item_quantity)
                    FROM receipt_item ri
                    JOIN receipt r ON r.receipt_id = ri.receipt_id
                    WHERE ri.prod_code = ph.prod_code
                    AND ri.inven_code = i.inven_code
                    AND r.receipt_date BETWEEN ph.effective_from 
                        AND COALESCE(ph.effective_to, NOW())
                ),0) AS batch_sold_in_period,

                COALESCE((
                    SELECT SUM(d.damaged_quantity)
                    FROM damaged_items d
                    WHERE d.inven_code = i.inven_code
                    AND d.damaged_date BETWEEN ph.effective_from 
                        AND COALESCE(ph.effective_to, NOW())
                ),0) AS batch_damaged_in_period,

                (
                    i.stock
                    - COALESCE((
                        SELECT SUM(ri2.item_quantity)
                        FROM receipt_item ri2
                        WHERE ri2.inven_code = i.inven_code
                    ), 0)
                    - COALESCE((
                        SELECT SUM(d2.damaged_quantity)
                        FROM damaged_items d2
                        WHERE d2.inven_code = i.inven_code
                    ), 0)
                ) AS batch_remaining,

                (
                    COALESCE((
                        SELECT SUM(ri.item_quantity)
                        FROM receipt_item ri
                        JOIN receipt r ON r.receipt_id = ri.receipt_id
                        WHERE ri.prod_code = ph.prod_code
                        AND ri.inven_code = i.inven_code
                        AND r.receipt_date BETWEEN ph.effective_from 
                            AND COALESCE(ph.effective_to, NOW())
                    ),0) * ph.old_selling_price
                ) AS total_sales_in_period

            FROM pricing_history ph
            LEFT JOIN inventory i 
                ON i.prod_code = ph.prod_code
            join products p
                on p.prod_code = i.prod_code
            WHERE i.owner_id = ?
                and p.name like ?
            ORDER BY p.name ASC, i.inven_code DESC,
                ph.effective_from DESC
        ", [$owner_id, $search]));
    }

    public function render()
    {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;

        $this->years = collect(DB::select("
            SELECT DISTINCT YEAR(receipt_date) AS year
            FROM receipt
            WHERE receipt_date IS NOT NULL and owner_id = ?
            ORDER BY year DESC
        ", [$owner_id]))->pluck('year')->toArray();

        $this->monthNames = ["January", "February", "March", "April", "May", "June", "July", "August","September", "October", "November", "December"];
        $this->historyList();
        return view('livewire.masterlist-price-history');
    }
}
