<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductAnalysis extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $currentMonth;
    public $monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    public $sortField = 'product_name';
    public $sortDirection = 'asc';
    public $searchWord = '';

    // protected $paginationTheme = 'tailwind';
    // protected $updatesQueryString = ['currentMonth', 'searchWord'];

    public function mount()
    {
        $this->currentMonth = now()->month;
    }

    public function updatedCurrentMonth()
    {
        $this->resetPage();
    }

    public function updatingSearchWord()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
        $this->resetPage();
    }

    public function render()
    {
        DB::connection()->getPdo()->exec("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')"); 

        $ownerLoggedIn = Auth::guard('owner')->check();
        $staffLoggedIn = Auth::guard('staff')->check();

        if ($ownerLoggedIn) {
            $owner_id = Auth::guard('owner')->user()->owner_id;

        } elseif ($staffLoggedIn) {
            $owner_id = Auth::guard('staff')->user()->owner_id;

        } else {
            abort(403, 'Unauthorized access.');
        }

        $latestYear = now()->year;


        
        $query = DB::table('receipt as r')
            ->join('receipt_item as ri', 'r.receipt_id', '=', 'ri.receipt_id')
            ->join('products as p', 'ri.prod_code', '=', 'p.prod_code')
            ->join('categories as c', 'p.category_id', '=', 'c.category_id')
            ->selectRaw("
                p.prod_code,
                p.name AS product_name,
                c.category AS category,
                c.category_id,

                SUM(ri.item_quantity) AS unit_sold,

                SUM(
                    ri.item_quantity * COALESCE(
                        (SELECT ph.old_selling_price
                        FROM pricing_history ph
                        WHERE ph.prod_code = ri.prod_code
                        AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                        ORDER BY ph.effective_from DESC
                        LIMIT 1),
                        p.selling_price
                    )
                ) AS total_sales,

                SUM(
                    ri.item_quantity * COALESCE(
                        (SELECT ph.old_cost_price
                        FROM pricing_history ph
                        WHERE ph.prod_code = ri.prod_code
                        AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                        ORDER BY ph.effective_from DESC
                        LIMIT 1),
                        p.cost_price
                    )
                ) AS cogs,

                (
                    SUM(
                        ri.item_quantity * COALESCE(
                            (SELECT ph.old_selling_price
                            FROM pricing_history ph
                            WHERE ph.prod_code = ri.prod_code
                            AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                            ORDER BY ph.effective_from DESC
                            LIMIT 1),
                            p.selling_price
                        )
                    )
                    -
                    SUM(
                        ri.item_quantity * COALESCE(
                            (SELECT ph.old_cost_price
                            FROM pricing_history ph
                            WHERE ph.prod_code = ri.prod_code
                            AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                            ORDER BY ph.effective_from DESC
                            LIMIT 1),
                            p.cost_price
                        )
                    )
                ) AS profit,

                (
                    (
                        SUM(
                            ri.item_quantity * COALESCE(
                                (SELECT ph.old_selling_price
                                FROM pricing_history ph
                                WHERE ph.prod_code = ri.prod_code
                                AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                ORDER BY ph.effective_from DESC
                                LIMIT 1),
                                p.selling_price
                            )
                        )
                        -
                        SUM(
                            ri.item_quantity * COALESCE(
                                (SELECT ph.old_cost_price
                                FROM pricing_history ph
                                WHERE ph.prod_code = ri.prod_code
                                AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                ORDER BY ph.effective_from DESC
                                LIMIT 1),
                                p.cost_price
                            )
                        )
                    )
                    /
                    NULLIF(
                        SUM(
                            ri.item_quantity * COALESCE(
                                (SELECT ph.old_selling_price
                                FROM pricing_history ph
                                WHERE ph.prod_code = ri.prod_code
                                AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                ORDER BY ph.effective_from DESC
                                LIMIT 1),
                                p.selling_price
                            )
                        ),
                        0
                    )
                ) * 100 AS profit_margin_percent,

                (
                    SUM(
                        ri.item_quantity * COALESCE(
                            (SELECT ph.old_selling_price
                            FROM pricing_history ph
                            WHERE ph.prod_code = ri.prod_code
                            AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                            ORDER BY ph.effective_from DESC
                            LIMIT 1),
                            p.selling_price
                        )
                    )
                    /
                    NULLIF((
                        SELECT SUM(
                            ri2.item_quantity * COALESCE(
                                (SELECT ph2.old_selling_price
                                FROM pricing_history ph2
                                WHERE ph2.prod_code = ri2.prod_code
                                AND r2.receipt_date BETWEEN ph2.effective_from AND ph2.effective_to
                                ORDER BY ph2.effective_from DESC
                                LIMIT 1),
                                p2.selling_price
                            )
                        )
                        FROM receipt_item ri2
                        JOIN receipt r2 ON ri2.receipt_id = r2.receipt_id
                        JOIN products p2 ON ri2.prod_code = p2.prod_code
                        WHERE MONTH(r2.receipt_date) = ?
                        AND YEAR(r2.receipt_date) = ?
                        AND r2.owner_id = ?
                    ), 0)
                ) * 100 AS contribution_percent
            ", [
                $this->currentMonth,
                $latestYear,
                $owner_id
            ])
            ->where('r.owner_id', $owner_id)
            ->whereMonth('r.receipt_date', $this->currentMonth)
            ->whereYear('r.receipt_date', $latestYear)
            ->groupBy('p.prod_code', 'p.name', 'c.category', 'c.category_id')
            ->orderBy('p.prod_code');


        /* ------------------------ SEARCH ------------------------ */
        if (!empty($this->searchWord)) {
            $search = '%' . $this->searchWord . '%';
            $query->where(function($q) use ($search) {
                $q->where('p.name', 'like', $search)
                ->orWhere('c.category', 'like', $search);
            });
        }


        

        $query->orderBy($this->sortField, $this->sortDirection);

        $analysisPage = $query->paginate(7);

        return view('livewire.product-analysis', [
            'analysisPage' => $analysisPage,
        ]);
    }
}
