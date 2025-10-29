<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArchiveExpiredProduct extends Component
{

    public $batchNumber;
    public $toastShow = false;
    public $toastMessage = '';

    public function mount($batchNumber = null)
    {
        $this->batchNumber = $batchNumber;
    }

    public function archived()
    {
        $inventory = collect(DB::select("
            SELECT prod_code, stock, expiration_date, owner_id, is_expired,
            FROM inventory
            WHERE batch_number = ?
            LIMIT 1
        ", [$this->batchNumber]))->first();

        if (!$inventory) {
            $this->toastMessage = "Batch not found.";
            $this->toastShow = true;
            return;
        }

        if ($inventory->is_expired == 1) {
            $this->toastMessage = "Batch #{$this->batchNumber} already archived.";
            $this->toastShow = true;
            return;
        }

        DB::update("
            UPDATE inventory
            SET is_expired = 1
            WHERE expiration_date <= CURDATE()
            AND batch_number = ?
        ", [$this->batchNumber]);

        DB::insert("
            INSERT INTO damaged_items 
            (prod_code, damaged_quantity, damaged_date, damaged_type, damaged_reason, return_id, owner_id)
            VALUES (?, ?, NOW(), ?, ?, ?, ?)
        ", [
            $inventory->prod_code,
            $inventory->stock,
            'Expired',
            "{$this->batchNumber} expired before selling out.",
            null,
            $inventory->owner_id,
        ]);

        $this->toastMessage = "Batch #{$this->batchNumber} archived successfully and logged in damaged items.";
        $this->toastShow = true;

    }


    public function render()
    {
        return view('livewire.archive-expired-product');
    }
}
