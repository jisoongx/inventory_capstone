<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class TechnicalResolve extends Component
{
    public $statusFilter = null; // null = all, or "Pending", "In Progress", "Resolved"
    public $request = [];

    public function mount()
    {
        $this->loadRequest();
    }

    public function loadRequest()
    {
        $query = "SELECT req_ticket, req_title, req_date, req_status
                  FROM technical_request";

        if ($this->statusFilter) {
            $query .= " WHERE req_status = ?";
            $this->request = collect(DB::select($query, [$this->statusFilter]));
        } else {
            $query .= " ORDER BY req_id DESC";
            $this->request = collect(DB::select($query));
        }
    }

    public function filterByStatus($status)
    {
        $this->statusFilter = $status;
        $this->loadRequest(); // reload with filter applied
    }

    public function render()
    {
        return view('livewire.technical-resolve');
    }
}
