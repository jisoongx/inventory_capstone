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
    
    // Multi-select properties
    public $selectedItems = [];
    public $selectAll = false;
    
    // Return modal properties
    public $showReturnModal = false;
    public $selectedItemForReturn = null;
    public $returnQuantity = 1;
    public $returnReason = '';
    public $maxReturnQuantity = 0;
    
    // Bulk return properties
    public $isBulkReturn = false;
    public $bulkReturnItems = [];
    
    // Return History
    public $returnHistoryData = [];
    public $store_info = null;
    
    // ADD: Error flag
    public $receiptNotFound = false;

    // ADD: Define categorized return reasons
    private $inventoryReasons = [
        'Wrong Item' => 'Wrong Item',
        'Missing Parts' => 'Missing Parts',
        'Unsealed/Opened' => 'Unsealed/Opened',
        'Faded/Discolored' => 'Faded/Discolored',
        'Crushed' => 'Crushed',
        'Torn' => 'Torn',
        'Duplicate' => 'Duplicate'
    ];

    private $damagedReasons = [
        'Expired' => 'Expired',
        'Broken' => 'Broken',
        'Spoiled' => 'Spoiled',
        'Damaged' => 'Damaged',
        'Defective' => 'Defective',
        'Contaminated' => 'Contaminated',
        'Leaking' => 'Leaking',
        'Wet/Water Damaged' => 'Wet/Water Damaged',
        'Mold/Fungus' => 'Mold/Fungus',
        'Pest Damage' => 'Pest Damage',
        'Temperature Abuse' => 'Temperature Abuse',
        'Recalled' => 'Recalled',
        'Stolen/Lost' => 'Stolen/Lost'
    ];

    // ADD: Helper method to check if reason is damaged type
    private function isDamagedReason($reason)
    {
        return array_key_exists($reason, $this->damagedReasons);
    }

    // ADD: Getter for view to access reason categories
    public function getInventoryReasonsProperty()
    {
        return $this->inventoryReasons;
    }

    public function getDamagedReasonsProperty()
    {
        return $this->damagedReasons;
    }

    public function mount()
    {
        $this->loadStoreInfo();
        $this->loadReceiptDetails();
        
        // FIXED: Check if receipt was found before loading other data
        if (!$this->receiptDetails) {
            $this->receiptNotFound = true;
            session()->flash('error', 'Receipt not found or you do not have permission to access it.');
            return;
        }
        
        $this->loadReturnableItems();
        $this->loadReturnHistory();
    }

    public function loadStoreInfo()
    {
        if (Auth::guard('owner')->check()) {
            $owner = Auth::guard('owner')->user();
            $this->store_info = (object)[
                'store_name' => $owner->store_name ??  'Store Name',
                'store_address' => $owner->store_address ??  '',
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
                o.lastname as owner_lastname,
                o.store_name,
                s.firstname as staff_firstname,
                s.lastname as staff_lastname
            FROM receipt r
            LEFT JOIN owners o ON r.owner_id = o.owner_id
            LEFT JOIN staff s ON r.staff_id = s.staff_id
            WHERE r.receipt_id = ?
            AND r.owner_id = ? 
        ", [$this->receiptId, $owner_id]);
    }

    public function loadReturnableItems()
    {
        $owner_id = Auth::guard('owner')->check() 
            ? Auth::guard('owner')->user()->owner_id 
            : Auth::guard('staff')->user()->owner_id;
        
        $this->returnableItems = collect(DB::select("
            SELECT 
                ri. item_id,
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
            GROUP BY ri.item_id, ri.item_quantity, ri. item_discount_type, ri. item_discount_value, 
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
                ret.owner_id as return_owner_id,
                ret.staff_id as return_staff_id,
                CONCAT(COALESCE(o.firstname, ''), ' ', COALESCE(o.lastname, '')) as owner_fullname,
                CONCAT(COALESCE(s.firstname, ''), ' ', COALESCE(s.lastname, '')) as staff_fullname,
                d.damaged_id,
                d.damaged_type,
                (ret.return_quantity * p.selling_price) as refund_amount
            FROM returned_items ret
            JOIN receipt_item ri ON ret.item_id = ri.item_id
            JOIN products p ON ri.prod_code = p.prod_code
            JOIN receipt r ON ri.receipt_id = r.receipt_id
            LEFT JOIN owners o ON ret.owner_id = o.owner_id AND ret.staff_id IS NULL
            LEFT JOIN staff s ON ret.staff_id = s.staff_id AND s.owner_id = ?
            LEFT JOIN damaged_items d ON d.return_id = ret.return_id
            WHERE r.receipt_id = ?
            AND r.owner_id = ?
            ORDER BY ret.return_date DESC
        ", [$owner_id, $this->receiptId, $owner_id]));
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedItems = $this->returnableItems->pluck('item_id')->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    public function updatedSelectedItems()
    {
        $this->selectAll = count($this->selectedItems) === $this->returnableItems->count();
    }

    public function getSelectedItemsDataProperty()
    {
        return $this->returnableItems->whereIn('item_id', $this->selectedItems);
    }

    public function openBulkReturnModal()
    {
        if (empty($this->selectedItems)) {
            session()->flash('error', 'Please select at least one item to return.');
            return;
        }

        $this->isBulkReturn = true;
        $this->bulkReturnItems = [];
        
        foreach ($this->selectedItemsData as $item) {
            $this->bulkReturnItems[$item->item_id] = [
                'item_id' => $item->item_id,
                'product_name' => $item->product_name,
                'selling_price' => $item->selling_price,
                'returnable_quantity' => $item->returnable_quantity,
                'return_quantity' => 1,
                'max_quantity' => $item->returnable_quantity,
                'inven_code' => $item->inven_code,
                'prod_code' => $item->prod_code
            ];
        }
        
        $this->returnReason = '';
        $this->showReturnModal = true;
    }

    public function openReturnModal($itemId)
    {
        $item = $this->returnableItems->firstWhere('item_id', $itemId);
        
        if (!$item) {
            session()->flash('error', 'Item not found or already fully returned.');
            return;
        }

        $this->isBulkReturn = false;
        $this->selectedItemForReturn = $item;
        $this->maxReturnQuantity = $item->returnable_quantity;
        $this->returnQuantity = min(1, $this->maxReturnQuantity);
        $this->returnReason = '';
        $this->showReturnModal = true;
    }

    public function closeReturnModal()
    {
        $this->showReturnModal = false;
        $this->selectedItemForReturn = null;
        $this->isBulkReturn = false;
        $this->bulkReturnItems = [];
        $this->returnQuantity = 1;
        $this->returnReason = '';
        $this->maxReturnQuantity = 0;
    }

    public function goBackToReports()
    {
        return redirect()->route('reports. sales_performance');
    }

    public function submitBulkReturn()
    {
        $rules = [
            'returnReason' => 'required|string',
        ];

        foreach ($this->bulkReturnItems as $itemId => $item) {
            $rules["bulkReturnItems. {$itemId}.return_quantity"] = "required|integer|min:1|max:{$item['max_quantity']}";
        }

        $this->validate($rules, [
            'returnReason. required' => 'Please select a return reason.',
            'bulkReturnItems.*.return_quantity. max' => 'Return quantity exceeds available quantity.'
        ]);

        $allValidReasons = array_merge(
            array_keys($this->inventoryReasons), 
            array_keys($this->damagedReasons)
        );
        
        if (! in_array($this->returnReason, $allValidReasons)) {
            session()->flash('error', 'Invalid return reason selected.');
            return;
        }

        try {
            $owner_id = Auth::guard('owner')->check() 
                ? Auth::guard('owner')->user()->owner_id 
                : Auth::guard('staff')->user()->owner_id;
            
            $staff_id = null;
            if (Auth::guard('staff')->check()) {
                $staff_id = Auth::guard('staff')->user()->staff_id;
            }

            DB::beginTransaction();

            $totalItemsProcessed = 0;
            $totalRefund = 0;
            $isDamaged = $this->isDamagedReason($this->returnReason);

            foreach ($this->bulkReturnItems as $itemData) {
                $returnQuantity = intval($itemData['return_quantity']);
                
                if ($returnQuantity <= 0) continue;

                $returnId = DB::table('returned_items')->insertGetId([
                    'item_id' => $itemData['item_id'],
                    'return_quantity' => $returnQuantity,
                    'return_reason' => $this->returnReason,
                    'return_date' => now(),
                    'owner_id' => $staff_id ?  null : $owner_id,
                    'staff_id' => $staff_id
                ]);

                $totalRefund += floatval($itemData['selling_price']) * $returnQuantity;

                if ($isDamaged) {
                    $invenCode = $itemData['inven_code'];
                    
                    DB::table('damaged_items')->insert([
                        'damaged_quantity' => $returnQuantity,
                        'damaged_date' => now(),
                        'damaged_type' => $this->returnReason,
                        'damaged_reason' => $this->returnReason,
                        'return_id' => $returnId,
                        'owner_id' => $owner_id,
                        'staff_id' => $staff_id,
                        'inven_code' => $invenCode
                    ]);

                    if ($invenCode) {
                        DB::table('inventory')
                            ->where('inven_code', $invenCode)
                            ->decrement('stock', $returnQuantity);
                    } else {
                        $this->decrementInventoryFIFO($itemData['prod_code'], $returnQuantity, $owner_id);
                    }
                } else {
                    $latestInventory = DB::table('inventory')
                        ->where('prod_code', $itemData['prod_code'])
                        ->where('owner_id', $owner_id)
                        ->orderBy('date_added', 'desc')
                        ->orderBy('inven_code', 'desc')
                        ->first();

                    if ($latestInventory) {
                        DB::table('inventory')
                            ->where('inven_code', $latestInventory->inven_code)
                            ->increment('stock', $returnQuantity);
                    } else {
                        $product = DB::table('products')
                            ->where('prod_code', $itemData['prod_code'])
                            ->first();

                        if ($product) {
                            DB::table('inventory')->insert([
                                'prod_code' => $itemData['prod_code'],
                                'stock' => $returnQuantity,
                                'date_added' => now(),
                                'owner_id' => $owner_id,
                                'category_id' => $product->category_id
                            ]);
                        }
                    }
                }

                $totalItemsProcessed++;
            }

            DB::commit();

            session()->flash('success', "Successfully processed {$totalItemsProcessed} item(s) return.  Total refund: ₱" . number_format($totalRefund, 2) . ". " . 
                ($isDamaged ? 'Items recorded as damaged and removed from inventory.' : 'Items restocked to inventory.'));
            
            $this->selectedItems = [];
            $this->selectAll = false;
            $this->closeReturnModal();
            $this->loadReturnableItems();
            $this->loadReturnHistory();
            $this->dispatch('returnProcessed', receiptId: $this->receiptId);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error processing bulk return: ' . $e->getMessage());
            \Log::error('Error in submitBulkReturn: ' . $e->getMessage());
        }
    }

    public function submitReturn()
    {
        $this->validate([
            'returnQuantity' => 'required|integer|min:1|max:' . $this->maxReturnQuantity,
            'returnReason' => 'required|string',
        ], [
            'returnQuantity. required' => 'Please enter a return quantity.',
            'returnQuantity.max' => 'Return quantity cannot exceed ' . $this->maxReturnQuantity . '. This will not be processed.',
            'returnReason. required' => 'Please select a return reason.',
        ]);

        $allValidReasons = array_merge(
            array_keys($this->inventoryReasons), 
            array_keys($this->damagedReasons)
        );
        
        if (!in_array($this->returnReason, $allValidReasons)) {
            session()->flash('error', 'Invalid return reason selected.');
            return;
        }

        try {
            $owner_id = Auth::guard('owner')->check() 
                ? Auth::guard('owner')->user()->owner_id 
                : Auth::guard('staff')->user()->owner_id;
            
            $staff_id = null;
            if (Auth::guard('staff')->check()) {
                $staff_id = Auth::guard('staff')->user()->staff_id;
            }

            DB::beginTransaction();

            $returnId = DB::table('returned_items')->insertGetId([
                'item_id' => $this->selectedItemForReturn->item_id,
                'return_quantity' => $this->returnQuantity,
                'return_reason' => $this->returnReason,
                'return_date' => now(),
                'owner_id' => $staff_id ? null : $owner_id,
                'staff_id' => $staff_id
            ]);

            $isDamaged = $this->isDamagedReason($this->returnReason);

            if ($isDamaged) {
                $invenCode = $this->selectedItemForReturn->inven_code;
                
                DB::table('damaged_items')->insert([
                    'damaged_quantity' => $this->returnQuantity,
                    'damaged_date' => now(),
                    'damaged_type' => $this->returnReason,
                    'damaged_reason' => $this->returnReason,
                    'return_id' => $returnId,
                    'owner_id' => $owner_id,
                    'staff_id' => $staff_id,
                    'inven_code' => $invenCode
                ]);

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
                ($isDamaged ? 'Item recorded as damaged and removed from inventory.' : 'Item restocked to inventory.'));
            
            $this->closeReturnModal();
            $this->loadReturnableItems();
            $this->loadReturnHistory();
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
        // FIXED: Handle receipt not found case
        if ($this->receiptNotFound) {
            return view('livewire.return-item-error');
        }
        
        return view('livewire.return-item');
    }
}