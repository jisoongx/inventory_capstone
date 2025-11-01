<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecordDamage extends Component
{
    public $products;
    public $inven = [];
    public $inventories = [];
    public $damageRecords = [];

    public $showModal = false;

    public function mount()
    {
        $this->addRecord();
    }

    public function addRecord()
    {
        $this->damageRecords[] = [
            'prod_code' => '',
            'inven_code' => '',
            'damaged_quantity' => '',
            'damaged_type' => '',
            'damaged_reason' => ''
        ];
    }
    
    public function removeRecord($index)
    {
        unset($this->damageRecords[$index]);
        $this->damageRecords = array_values($this->damageRecords); 
        
        if (empty($this->damageRecords)) {
            $this->addRecord();
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(); 
    }

    public function getProducts()
    {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        $this->products = collect(DB::select("
            SELECT p.name, p.prod_code
            FROM products p
            WHERE owner_id = ?
        ", [$owner_id]));
    }

    public function getInventory($index, $prod_code)
    {
        $inventories = collect(DB::select("
            SELECT i.inven_code, i.batch_number, i.expiration_date
            FROM inventory i
            WHERE i.prod_code = ?
        ", [$prod_code]));

        $this->inventories[$index] = $inventories;
    }



    public function saveDamageRecords()
    {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        foreach ($this->damageRecords as $record) {

            if (!empty($record['prod_code']) && !empty($record['inven_code']) &&!empty($record['damaged_quantity'])) 
            {
                DB::insert("
                    INSERT INTO damaged_items (
                        prod_code, 
                        damaged_quantity, 
                        damaged_date, 
                        damaged_type, 
                        damaged_reason, 
                        return_id, 
                        owner_id, 
                        staff_id, 
                        inven_code
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [
                    $record['prod_code'],
                    $record['damaged_quantity'],
                    now(),
                    $record['damaged_type'] ?? 'N/A',
                    $record['damaged_reason'] ?? 'N/A',
                    null,
                    $owner_id,
                    null,
                    $record['inven_code']
                ]);

                DB::update("
                    update inventory
                    set stock = stock - ?
                    where inven_code = ?
                ", [$record['damaged_quantity'], $record['inven_code']]); 
            }
        }

        session()->flash('success', 'Damaged records successfully saved!');
        $this->damageRecords = []; 
        $this->addRecord(); 
    }

    public function render()
    {
        $this->getProducts();
        return view('livewire.record-damage');
    }
}
