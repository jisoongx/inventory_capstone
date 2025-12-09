<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ActivityLogController;

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
            'damaged_reason' => '',
            'damaged_set_to_return' => ''
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
    }

    public function cancelModal()
    {
        $this->showModal = false;
        $this->damageRecords = [];
        $this->inventories = [];
        $this->reset();
        $this->addRecord();
    }




    public function getProducts()
    {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        $this->products = collect(DB::select("
            SELECT p.name, p.prod_code, sum(i.stock) as stocks 
            FROM products p 
            join inventory i on p.prod_code = i.prod_code 
            WHERE p.owner_id = ? 
            group by p.name, p.prod_code 
            having sum(i.stock) > 0
        ", [$owner_id]));
    }


    public function getInventory($index, $prod_code)
    {
        $inventories = collect(DB::select("
            SELECT i.inven_code, i.batch_number, i.expiration_date
            FROM inventory i
            WHERE i.prod_code = ?
            and i.stock > 0
        ", [$prod_code]));

        $this->inventories[$index] = $inventories;
    }



    public function saveDamageRecords()
    {
        $owner_id = Auth::guard('owner')->user()->owner_id;
        $user = Auth::guard('owner')->check() ? Auth::guard('owner')->user() : Auth::guard('staff')->user();
        $userType = Auth::guard('owner')->check() ? 'owner' : 'staff';
        $userIdField = $userType === 'owner' ? 'owner_id' : 'staff_id';
        $userId = $user->{$userIdField};

        foreach ($this->damageRecords as $record) {

            if (!empty($record['prod_code']) && !empty($record['inven_code']) &&!empty($record['damaged_quantity'])) 
            {
                $productName = DB::table('products')
                    ->where('prod_code', $record['prod_code'])
                    ->value('name') ?? 'Unknown Product';


                DB::insert("
                    INSERT INTO damaged_items (
                        damaged_quantity, 
                        damaged_date, 
                        damaged_type, 
                        damaged_reason, 
                        return_id, 
                        owner_id, 
                        staff_id, 
                        inven_code,
                        set_to_return_to_supplier
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [
                    $record['damaged_quantity'],
                    now(),
                    $record['damaged_type'] ?? 'N/A',
                    $record['damaged_reason'] ?? 'N/A',
                    null,
                    $owner_id,
                    null,
                    $record['inven_code'],
                    !empty($record['damaged_set_to_return']) ? 'To be returned' : null,

                ]);

                DB::update("
                    update inventory
                    set stock = stock - ?
                    where inven_code = ?
                ", [$record['damaged_quantity'], $record['inven_code']]);

                session()->flash('success', 'Damaged records successfully saved!');
                $this->damageRecords = []; 
                $this->addRecord(); 

                ActivityLogController::log(
                    "Recorded {$record['damaged_quantity']} damaged item(s) for product \"{$productName}\"",
                    $userType,
                    $user,
                    request()->ip()
                );
            }
        }

    }



    public function updatedDamageRecords($value, $key)
    {
        if (!str_contains($key, 'damaged_quantity')) return;

        [$index] = explode('.', $key);
        $record = $this->damageRecords[$index] ?? null;

        if (!$record || empty($record['inven_code'])) return;

        $stock = DB::table('inventory')
            ->where('inven_code', $record['inven_code'])
            ->value('stock');

        if ($value > $stock) {
            $this->addError("damageRecords.$index.damaged_quantity", "Cannot exceed available stock ($stock).");
        } else if ($value <= 0) {
            $this->addError("damageRecords.$index.damaged_quantity", "Cannot be negative or zero.");
        }
        else {
            $this->resetErrorBag("damageRecords.$index.damaged_quantity");
        }
    }



    public function render()
    {
        $this->getProducts();
        return view('livewire.record-damage');
    }
}
