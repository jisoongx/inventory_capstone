<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturnItem extends Component
{
    public $receiptId;
    public $receiptDetails;
    public $returnableItems = [];
    
    // Return modal properties
    public $showReturnModal = false;
    public $selectedItemForReturn = null;
    public $returnQuantity = 1;
    public $returnReason = '';
    public $isDamaged = false;
    public $damageType = '';
    public $maxReturnQuantity = 0;
    
    // Return History
    public $returnHistoryData = [];
    public $store_info = null;

    public function mount()
    {
        $this->loadStoreInfo();
        $this->loadReceiptDetails();
        $this->loadReturnableItems();
        $this->loadReturnHistory();
    }

    public function loadStoreInfo()
    {
        if (Auth::guard('owner')->check()) {
            $owner = Auth::guard('owner')->user();
            $this->store_info = (object)[
                'store_name' => $owner->store_name ?? 'Store Name',
                'store_address' => $owner->store_address ?? '',
                'contact' => $owner->contact ?? ''
            ];
        } elseif (Auth::guard('staff')->check()) {
            $staff = Auth::guard('staff')->user();
            $owner = $staff->owner;
            $this->store_info = (object)[
                'store_name' => $owner->store_name ?? 'Store Name',
                'store_address' => $owner->store_address ?? '',
                'contact' => $owner->contact ?? ''
            ];
        }
    }

    public function loadReceiptDetails()
    {
        $owner_id = Auth::guard('owner')->check() 
            ? Auth::guard('owner')->user()->owner_id 
            : Auth::guard('staff')->user()->owner_id;
        
        $this->receiptDetails = DB::selectOne("
            SELECT 
                r.*,
                o.firstname as owner_firstname,
                o.store_name,
                s.firstname as staff_firstname
            FROM receipt r
            LEFT JOIN owners o ON r.owner_id = o.owner_id
            LEFT JOIN staff s ON r.staff_id = s.staff_id
            WHERE r.receipt_id = ?
            AND r.owner_id = ?
        ", [$this->receiptId, $owner_id]);

        if (!$this->receiptDetails) {
            session()->flash('error', 'Receipt not found.');
            return redirect()->route('reports.sales_performance');
        }
    }

    public function loadReturnableItems()
    {
        $owner_id = Auth::guard('owner')->check() 
            ? Auth::guard('owner')->user()->owner_id 
            : Auth::guard('staff')->user()->owner_id;
        
        $this->returnableItems = collect(DB::select("
            SELECT 
                ri.item_id,
                ri.item_quantity,
                ri.item_discount_type,
                ri.item_discount_value,
                ri.vat_amount,
                ri.inven_code,
                ri.prod_code,
                p.name as product_name,
                p.selling_price,
                COALESCE(SUM(ret.return_quantity), 0) as already_returned,
                (ri.item_quantity - COALESCE(SUM(ret.return_quantity), 0)) as returnable_quantity
            FROM receipt_item ri
            JOIN products p ON ri.prod_code = p.prod_code
            JOIN receipt r ON ri.receipt_id = r.receipt_id
            LEFT JOIN returned_items ret ON ret.item_id = ri.item_id
            WHERE r.receipt_id = ?
            AND r.owner_id = ?
            GROUP BY ri.item_id, ri.item_quantity, ri.item_discount_type, ri.item_discount_value, 
                     ri.vat_amount, ri.inven_code, ri.prod_code, p.name, p.selling_price
            HAVING returnable_quantity > 0
            ORDER BY ri.item_id
        ", [$this->receiptId, $owner_id]));
    }

    public function loadReturnHistory()
    {
        $owner_id = Auth::guard('owner')->check() 
            ? Auth::guard('owner')->user()->owner_id 
            : Auth::guard('staff')->user()->owner_id;
        
        $this->returnHistoryData = collect(DB::select("
            SELECT 
                ret.return_id,
                ret.return_date,
                ret.return_quantity,
                ret.return_reason,
                p.name as product_name,
                p.selling_price,
                CONCAT(COALESCE(o.firstname, ''), ' ', COALESCE(o.lastname, '')) as processed_by_owner,
                CONCAT(COALESCE(s.firstname, ''), ' ', COALESCE(s.lastname, '')) as processed_by_staff,
                d.damaged_id,
                d.damaged_type,
                (ret.return_quantity * p.selling_price) as refund_amount
            FROM returned_items ret
            JOIN receipt_item ri ON ret.item_id = ri.item_id
            JOIN products p ON ri.prod_code = p.prod_code
            JOIN receipt r ON ri.receipt_id = r.receipt_id
            LEFT JOIN owners o ON ret.owner_id = o.owner_id AND ret.staff_id IS NULL
            LEFT JOIN staff s ON ret.staff_id = s.staff_id
            LEFT JOIN damaged_items d ON d.return_id = ret.return_id
            WHERE r.receipt_id = ?
            AND r.owner_id = ?
            ORDER BY ret.return_date DESC
        ", [$this->receiptId, $owner_id]));
    }

    public function openReturnModal($itemId)
    {
        $item = $this->returnableItems->firstWhere('item_id', $itemId);
        
        if (!$item) {
            session()->flash('error', 'Item not found or already fully returned.');
            return;
        }

        $this->selectedItemForReturn = $item;
        $this->maxReturnQuantity = $item->returnable_quantity;
        $this->returnQuantity = min(1, $this->maxReturnQuantity);
        $this->returnReason = '';
        $this->isDamaged = false;
        $this->damageType = '';
        $this->showReturnModal = true;
    }

    public function closeReturnModal()
    {
        $this->showReturnModal = false;
        $this->selectedItemForReturn = null;
        $this->returnQuantity = 1;
        $this->returnReason = '';
        $this->isDamaged = false;
        $this->damageType = '';
        $this->maxReturnQuantity = 0;
    }

    public function submitReturn()
    {
        $this->validate([
            'returnQuantity' => 'required|integer|min:1|max:' . $this->maxReturnQuantity,
            'returnReason' => 'required|string|min:3|max:255',
            'isDamaged' => 'required|boolean',
            'damageType' => 'required_if:isDamaged,true|nullable|string|max:20'
        ], [
            'returnQuantity.required' => 'Please enter a return quantity.',
            'returnQuantity.max' => 'Return quantity cannot exceed ' . $this->maxReturnQuantity . '. This will not be processed.',
            'returnReason.required' => 'Please provide a reason for the return.',
            'returnReason.min' => 'Reason must be at least 3 characters.',
            'damageType.required_if' => 'Please select the damage type.'
        ]);

        try {
            $owner_id = Auth::guard('owner')->check() 
                ? Auth::guard('owner')->user()->owner_id 
                : Auth::guard('staff')->user()->owner_id;
            
            $staff_id = null;
            if (Auth::guard('staff')->check()) {
                $staff_id = Auth::guard('staff')->user()->staff_id;
            }

            DB::beginTransaction();

            // Create return record
            $returnId = DB::table('returned_items')->insertGetId([
                'item_id' => $this->selectedItemForReturn->item_id,
                'return_quantity' => $this->returnQuantity,
                'return_reason' => $this->returnReason,
                'return_date' => now(),
                'owner_id' => $owner_id,
                'staff_id' => $staff_id
            ]);

            if ($this->isDamaged) {
                $invenCode = $this->selectedItemForReturn->inven_code;
                
                // Record damaged item
                DB::table('damaged_items')->insert([
                    'damaged_quantity' => $this->returnQuantity,
                    'damaged_date' => now(),
                    'damaged_type' => $this->damageType,
                    'damaged_reason' => $this->returnReason,
                    'return_id' => $returnId,
                    'owner_id' => $owner_id,
                    'staff_id' => $staff_id,
                    'inven_code' => $invenCode
                ]);

                // DECREASE inventory for damaged items
                if ($invenCode) {
                    DB::table('inventory')
                        ->where('inven_code', $invenCode)
                        ->decrement('stock', $this->returnQuantity);
                } else {
                    $this->decrementInventoryFIFO(
                        $this->selectedItemForReturn->prod_code, 
                        $this->returnQuantity, 
                        $owner_id
                    );
                }
            } else {
                // Return to inventory (add to most recent batch)
                $latestInventory = DB::table('inventory')
                    ->where('prod_code', $this->selectedItemForReturn->prod_code)
                    ->where('owner_id', $owner_id)
                    ->orderBy('date_added', 'desc')
                    ->orderBy('inven_code', 'desc')
                    ->first();

                if ($latestInventory) {
                    DB::table('inventory')
                        ->where('inven_code', $latestInventory->inven_code)
                        ->increment('stock', $this->returnQuantity);
                } else {
                    // Create new inventory record
                    $product = DB::table('products')
                        ->where('prod_code', $this->selectedItemForReturn->prod_code)
                        ->first();

                    if ($product) {
                        DB::table('inventory')->insert([
                            'prod_code' => $this->selectedItemForReturn->prod_code,
                            'stock' => $this->returnQuantity,
                            'date_added' => now(),
                            'owner_id' => $owner_id,
                            'category_id' => $product->category_id
                        ]);
                    }
                }
            }

            DB::commit();

            session()->flash('success', 'Return processed successfully. ' . 
                ($this->isDamaged ? 'Item recorded as damaged and removed from inventory.' : 'Item restocked to inventory.'));
            
            // Refresh all data
            $this->closeReturnModal();
            $this->loadReturnableItems();
            $this->loadReturnHistory();
            
            // Dispatch event to parent component
            $this->dispatch('returnProcessed', receiptId: $this->receiptId);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error processing return: ' . $e->getMessage());
            \Log::error('Error in submitReturn: ' . $e->getMessage());
        }
    }

    private function decrementInventoryFIFO($prodCode, $quantity, $ownerId)
    {
        $remaining = $quantity;
        
        $inventoryBatches = DB::table('inventory')
            ->where('prod_code', $prodCode)
            ->where('owner_id', $ownerId)
            ->where('stock', '>', 0)
            ->orderBy('date_added', 'asc')
            ->orderBy('inven_code', 'asc')
            ->get();

        foreach ($inventoryBatches as $batch) {
            if ($remaining <= 0) break;

            $deductFromThisBatch = min($remaining, $batch->stock);
            
            DB::table('inventory')
                ->where('inven_code', $batch->inven_code)
                ->decrement('stock', $deductFromThisBatch);
            
            $remaining -= $deductFromThisBatch;
        }
    }

    public function render()
    {
        return view('livewire.return-item');
    }
}