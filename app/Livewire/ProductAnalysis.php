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
        $owner_id = Auth::guard('owner')->user()->owner_id;
        $latestYear = now()->year;

        $query = DB::table('receipt as r')
            ->join('receipt_item as ri', 'r.receipt_id', '=', 'ri.receipt_id')
            ->join('products as p', 'ri.prod_code', '=', 'p.prod_code')
            ->join('categories as c', 'p.category_id', '=', 'c.category_id')
            ->selectRaw('
                p.prod_code,
                p.name as product_name,
                c.category as category,
                SUM(ri.item_quantity) as unit_sold,
                SUM(p.selling_price * ri.item_quantity) as total_sales,
                SUM(p.cost_price * ri.item_quantity) as cogs,
                (SUM(p.selling_price * ri.item_quantity) - SUM(p.cost_price * ri.item_quantity)) as profit,
                ((SUM(p.selling_price * ri.item_quantity) - SUM(p.cost_price * ri.item_quantity))
                    / NULLIF(SUM(p.selling_price * ri.item_quantity),0)) * 100 as profit_margin_percent,
                (SUM(p.selling_price * ri.item_quantity) / NULLIF((
                    SELECT SUM(p2.selling_price * ri2.item_quantity)
                    FROM receipt r2
                    JOIN receipt_item ri2 ON r2.receipt_id = ri2.receipt_id
                    JOIN products p2 ON ri2.prod_code = p2.prod_code
                    WHERE r2.owner_id = r.owner_id
                    AND MONTH(r2.receipt_date) = ?
                    AND YEAR(r2.receipt_date) = ?
                ),0)) * 100 as contribution_percent
            ', [$this->currentMonth, $latestYear])
            ->whereMonth('r.receipt_date', $this->currentMonth)
            ->whereYear('r.receipt_date', $latestYear)
            ->where('r.owner_id', $owner_id)
            ->groupBy('p.prod_code','p.name','c.category','r.owner_id');



        if (!empty($this->searchWord)) {
            $search = '%' . $this->searchWord . '%';
            $query->where(function($q) use ($search) {
                $q->where('p.name','like',$search)
                  ->orWhere('c.category','like',$search);
            });
        }
        

        $query->orderBy($this->sortField, $this->sortDirection);

        $analysisPage = $query->paginate(7);

        return view('livewire.product-analysis', [
            'analysisPage' => $analysisPage,
        ]);
    }
}
