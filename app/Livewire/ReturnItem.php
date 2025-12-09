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
    public $bulkReturnQuantities = [];
    
    // Return History
    public $returnHistoryData = [];
    public $store_info = null;
    
    // Error flag
    public $receiptNotFound = false;

    public $returnAction = 'restock';
    public $customReturnReason = '';
    
    // Replacement feature
    public $replacementBarcode = '';
    public $replacementItems = [];
    public $showReplacementSection = false;
    
    // Modal-specific error messages
    public $modalError = '';
    public $modalWarning = '';
    
    // Define single list of all reasons
    private $allReturnReasons = [
        'Wrong Item',
        'Missing Parts',
        'Unsealed/Opened',
        'Faded/Discolored',
        'Crushed',
        'Torn',
        'Duplicate',
        'Expired',
        'Broken',
        'Spoiled',
        'Damaged',
        'Defective',
        'Contaminated',
        'Leaking',
        'Wet/Water Damaged',
        'Mold/Fungus',
        'Pest Damage',
        'Temperature Abuse',
        'Recalled',
        'Stolen/Lost'
    ];

    public function getAllReturnReasonsProperty()
    {
        return $this->allReturnReasons;
    }

    public function mount()
    {
        $this->loadStoreInfo();
        $this->loadReceiptDetails();
        
        if (!  $this->receiptDetails) {
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
                'store_address' => $owner->store_address ??  '',
                'contact' => $owner->contact ?? ''
            ];
        }
    }

    public function loadReceiptDetails()
    {
        $owner_id = Auth::guard('owner')->check() 
            ? Auth::guard('owner')->user()->owner_id 
            : Auth:: guard('staff')->user()->owner_id;
        
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
            : Auth:: guard('staff')->user()->owner_id;
        
        $this->returnableItems = collect(DB::select("
            SELECT 
                ri.item_id,
                ri.item_quantity,
                ri.item_discount_type,
                ri.item_discount_value,
                ri.item_discount_amount,
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
                     ri.item_discount_amount, ri.vat_amount, ri.inven_code, ri.prod_code, p.name, p.selling_price
            HAVING returnable_quantity > 0
            ORDER BY ri.item_id
        ", [$this->receiptId, $owner_id]));
    }

    public function loadReturnHistory()
    {
        $owner_id = Auth:: guard('owner')->check() 
            ? Auth::guard('owner')->user()->owner_id 
            : Auth::guard('staff')->user()->owner_id;
        
        $this->returnHistoryData = collect(DB::select("
            SELECT 
                ret.return_id,
                ret.return_date,
                ret.return_quantity,
                ret.return_reason,
                ret.receipt_id as replacement_receipt_id,
                p.name as product_name,
                p.selling_price,
                ret.owner_id as return_owner_id,
                ret.staff_id as return_staff_id,
                CONCAT(COALESCE(o.firstname, ''), ' ', COALESCE(o.lastname, '')) as owner_fullname,
                CONCAT(COALESCE(s.firstname, ''), ' ', COALESCE(s.lastname, '')) as staff_fullname,
                d.damaged_id,
                d.damaged_type,
                (ret.return_quantity * p.selling_price) as refund_amount,
                (SELECT GROUP_CONCAT(p2.name SEPARATOR ', ')
                 FROM receipt_item ri2
                 JOIN products p2 ON ri2.prod_code = p2.prod_code
                 WHERE ri2.receipt_id = ret.receipt_id
                ) as replacement_products
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

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedItems = $this->returnableItems->pluck('item_id')->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    public function updatedSelectedItems()
    {
        $this->selectAll = count($this->selectedItems) === $this->returnableItems->count();
    }

    public function toggleReplacement()
    {
        $this->showReplacementSection = ! $this->showReplacementSection;
        if (! $this->showReplacementSection) {
            $this->replacementBarcode = '';
            $this->replacementItems = [];
        }
    }

    public function searchReplacementProduct()
    {
        $this->modalError = '';
        
        if (empty($this->replacementBarcode)) {
            $this->modalError = 'Please enter a barcode. ';
            return;
        }

        $owner_id = Auth::guard('owner')->check() 
            ? Auth::guard('owner')->user()->owner_id 
            : Auth:: guard('staff')->user()->owner_id;

        $product = DB::selectOne("
            SELECT 
                p.prod_code,
                p.name,
                p.selling_price,
                p.cost_price,
                p.barcode,
                COALESCE(SUM(i.stock), 0) as available_stock
            FROM products p
            LEFT JOIN inventory i ON p.prod_code = i.prod_code
            WHERE p.barcode = ? 
            AND p.owner_id = ?
            AND p.prod_status = 'active'
            GROUP BY p.prod_code, p.name, p.selling_price, p.cost_price, p.barcode
        ", [$this->replacementBarcode, $owner_id]);

        if (!$product) {
            $this->modalError = 'Product not found or inactive.';
            return;
        }

        if ($product->available_stock <= 0) {
            $this->modalError = 'Product out of stock.';
            return;
        }

        $existingKey = array_search($product->prod_code, array_column($this->replacementItems, 'prod_code'));
        
        if ($existingKey !== false) {
            if ($this->replacementItems[$existingKey]['quantity'] < $product->available_stock) {
                $this->replacementItems[$existingKey]['quantity']++;
            } else {
                $this->modalError = 'Maximum available stock reached for this product.';
            }
        } else {
            $this->replacementItems[] = [
                'prod_code' => $product->prod_code,
                'name' => $product->name,
                'selling_price' => floatval($product->selling_price),
                'barcode' => $product->barcode,
                'available_stock' => intval($product->available_stock),
                'quantity' => 1
            ];
        }

        $this->replacementBarcode = '';
    }

    public function removeReplacementItem($index)
    {
        unset($this->replacementItems[$index]);
        $this->replacementItems = array_values($this->replacementItems);
    }

    public function clearAllReplacements()
    {
        $this->replacementItems = [];
        $this->replacementBarcode = '';
    }

    public function openBulkReturnModal()
    {
        if (empty($this->selectedItems)) {
            session()->flash('error', 'Please select at least one item to return.');
            return;
        }
    
        $this->isBulkReturn = true;
        $this->bulkReturnItems = [];
        $this->bulkReturnQuantities = [];
        
        foreach ($this->selectedItems as $itemId) {
            $item = $this->returnableItems->firstWhere('item_id', $itemId);
            
            if ($item) {
                $this->bulkReturnItems[$itemId] = [
                    'item_id' => (int)$item->item_id,
                    'product_name' => (string)($item->product_name ??  'Unknown Product'),
                    'selling_price' => (float)($item->selling_price ?? 0),
                    'returnable_quantity' => (int)($item->returnable_quantity ?? 0),
                    'max_quantity' => (int)($item->returnable_quantity ?? 0),
                    'inven_code' => $item->inven_code ?? null,
                    'prod_code' => (int)($item->prod_code ?? 0)
                ];
                
                $this->bulkReturnQuantities[$itemId] = 1;
            }
        }
        
        if (empty($this->bulkReturnItems)) {
            session()->flash('error', 'No valid items found for return.');
            return;
        }
        
        $this->returnAction = 'restock';
        $this->returnReason = '';
        $this->customReturnReason = '';
        $this->showReplacementSection = false;
        $this->replacementItems = [];
        $this->modalError = '';
        $this->modalWarning = '';
        $this->showReturnModal = true;
    }

    public function openReturnModal($itemId)
    {
        $item = $this->returnableItems->firstWhere('item_id', $itemId);
        
        if (! $item) {
            session()->flash('error', 'Item not found or already fully returned.');
            return;
        }
    
        $this->isBulkReturn = false;
        $this->selectedItemForReturn = $item;
        $this->maxReturnQuantity = $item->returnable_quantity;
        $this->returnQuantity = min(1, $this->maxReturnQuantity);
        $this->returnReason = '';
        $this->customReturnReason = '';
        $this->returnAction = 'restock';
        $this->showReplacementSection = false;
        $this->replacementItems = [];
        $this->modalError = '';
        $this->modalWarning = '';
        $this->showReturnModal = true;
    }

    public function closeReturnModal()
    {
        $this->showReturnModal = false;
        $this->selectedItemForReturn = null;
        $this->isBulkReturn = false;
        $this->bulkReturnItems = [];
        $this->bulkReturnQuantities = [];
        $this->returnQuantity = 1;
        $this->returnReason = '';
        $this->customReturnReason = '';
        $this->returnAction = 'restock';
        $this->maxReturnQuantity = 0;
        $this->showReplacementSection = false;
        $this->replacementItems = [];
        $this->replacementBarcode = '';
        $this->modalError = '';
        $this->modalWarning = '';
    }

    public function goBackToReports()
    {
        return redirect()->route('reports.sales_performance');
    }

    private function processReplacement($returnId, $owner_id, $staff_id)
    {
        if (empty($this->replacementItems)) {
            return null;
        }

        $totalAmount = 0;
        foreach ($this->replacementItems as $item) {
            $totalAmount += floatval($item['selling_price']) * intval($item['quantity']);
        }

        $newReceiptId = DB::table('receipt')->insertGetId([
            'receipt_date' => now(),
            'owner_id' => $owner_id,
            'staff_id' => $staff_id,
            'amount_paid' => $totalAmount,
            'discount_type' => 'amount',
            'discount_value' => 0,
            'discount_amount' => 0
        ]);

        foreach ($this->replacementItems as $item) {
            $this->deductInventoryFIFO(
                (int)$item['prod_code'],
                (int)$item['quantity'],
                $owner_id,
                $newReceiptId
            );
        }

        DB::table('returned_items')
            ->where('return_id', $returnId)
            ->update(['receipt_id' => $newReceiptId]);

        return $newReceiptId;
    }

    private function deductInventoryFIFO($prodCode, $quantity, $ownerId, $receiptId)
    {
        $remaining = (int)$quantity;
        $prodCode = (int)$prodCode;
        $receiptId = (int)$receiptId;
        
        $inventoryBatches = DB::table('inventory')
            ->where('prod_code', $prodCode)
            ->where('owner_id', $ownerId)
            ->where('stock', '>', 0)
            ->orderBy('date_added', 'asc')
            ->orderBy('inven_code', 'asc')
            ->get();
    
        foreach ($inventoryBatches as $batch) {
            if ($remaining <= 0) break;
    
            $batchStock = (int)$batch->stock;
            $deductFromThisBatch = min($remaining, $batchStock);
            
            DB::table('inventory')
                ->where('inven_code', $batch->inven_code)
                ->decrement('stock', $deductFromThisBatch);
    
            DB::table('receipt_item')->insert([
                'item_quantity' => $deductFromThisBatch,
                'prod_code' => $prodCode,
                'receipt_id' => $receiptId,
                'item_discount_type' => 'amount',
                'item_discount_value' => 0,
                'item_discount_amount' => 0,
                'vat_amount' => 0,
                'inven_code' => $batch->inven_code
            ]);
            
            $remaining -= $deductFromThisBatch;
        }
    }

    // ✅ ADD BACK TO INVENTORY (RESTOCK)
    private function restockInventory($prodCode, $quantity, $ownerId, $invenCode = null)
    {
        $prodCode = (int)$prodCode;
        $quantity = (int)$quantity;
        
        // If we have the original inven_code, add it back to that batch
        if ($invenCode) {
            $batch = DB::table('inventory')
                ->where('inven_code', $invenCode)
                ->where('owner_id', $ownerId)
                ->first();
            
            if ($batch) {
                DB::table('inventory')
                    ->where('inven_code', $invenCode)
                    ->increment('stock', $quantity);
                return;
            }
        }
        
        // Otherwise, add to the most recent batch
        $latestInventory = DB::table('inventory')
            ->where('prod_code', $prodCode)
            ->where('owner_id', $ownerId)
            ->orderBy('date_added', 'desc')
            ->orderBy('inven_code', 'desc')
            ->first();

        if ($latestInventory) {
            DB::table('inventory')
                ->where('inven_code', $latestInventory->inven_code)
                ->increment('stock', $quantity);
        } else {
            // Create new inventory entry if none exists
            $product = DB::table('products')
                ->where('prod_code', $prodCode)
                ->first();

            if ($product) {
                DB::table('inventory')->insert([
                    'prod_code' => $prodCode,
                    'stock' => $quantity,
                    'date_added' => now(),
                    'owner_id' => $ownerId,
                    'category_id' => $product->category_id
                ]);
            }
        }
    }

    public function submitBulkReturn()
    {
        $this->modalError = '';
        $this->modalWarning = '';
        
        $hasError = false;
        
        if (empty($this->returnAction) || ! in_array($this->returnAction, ['restock', 'damage'])) {
            $this->modalError = 'Please select whether to restock or mark as damaged.';
            $hasError = true;
        }
        
        $finalReason = ! empty($this->customReturnReason) 
            ? trim($this->customReturnReason) 
            : $this->returnReason;
    
        if (empty($finalReason)) {
            $this->modalError = 'Please provide a return reason (either select from dropdown or type custom reason).';
            $hasError = true;
        }
        
        foreach ($this->bulkReturnItems as $itemId => $item) {
            $qty = (int)($this->bulkReturnQuantities[$itemId] ?? 0);
            if ($qty <= 0 || $qty > (int)$item['max_quantity']) {
                $this->modalError = "Invalid quantity for {$item['product_name']}. Max returnable:  {$item['max_quantity']}";
                $hasError = true;
                break;
            }
        }
        
        if ($hasError) {
            return;
        }
    
        try {
            $owner_id = Auth::guard('owner')->check() 
                ? Auth::guard('owner')->user()->owner_id 
                : Auth:: guard('staff')->user()->owner_id;
            
            $staff_id = Auth::guard('staff')->check() 
                ? Auth::guard('staff')->user()->staff_id 
                : null;
    
            DB::beginTransaction();
    
            $totalItemsProcessed = 0;
            $totalRefund = 0;
            $isDamaged = ($this->returnAction === 'damage');
            $lastReturnId = null;
            $replacementReceiptId = null;
    
            foreach ($this->bulkReturnItems as $itemId => $itemData) {
                $returnQuantity = (int)($this->bulkReturnQuantities[$itemId] ?? 0);
                $sellingPrice = (float)$itemData['selling_price'];
                $itemIdValue = (int)$itemData['item_id'];
                $prodCode = (int)$itemData['prod_code'];
                $invenCode = $itemData['inven_code'] ?? null;
                
                if ($returnQuantity <= 0) {
                    continue;
                }
    
                $returnId = DB::table('returned_items')->insertGetId([
                    'item_id' => $itemIdValue,
                    'return_quantity' => $returnQuantity,
                    'return_reason' => $finalReason,
                    'return_date' => now(),
                    'owner_id' => $staff_id ?  null : $owner_id,
                    'staff_id' => $staff_id,
                    'receipt_id' => null
                ]);

                // ✅ SUBTRACT RETURNED QUANTITY FROM ORIGINAL RECEIPT ITEM
                DB::table('receipt_item')
                ->where('item_id', $itemIdValue)
                ->decrement('item_quantity', $returnQuantity);
    
                $lastReturnId = $returnId;
                $totalRefund += $sellingPrice * $returnQuantity;
    
                if ($isDamaged) {
                    // ✅ DAMAGED:  Record in damaged_items table (NO inventory change)
                    DB::table('damaged_items')->insert([
                        'damaged_quantity' => $returnQuantity,
                        'damaged_date' => now(),
                        'damaged_type' => $finalReason,
                        'damaged_reason' => $finalReason,
                        'return_id' => $returnId,
                        'owner_id' => $owner_id,
                        'staff_id' => $staff_id,
                        'inven_code' => $invenCode
                    ]);
                } else {
                    // ✅ RESTOCK: Add back to inventory
                    $this->restockInventory($prodCode, $returnQuantity, $owner_id, $invenCode);
                }
    
                $totalItemsProcessed++;
            }
    
            if ($this->showReplacementSection && ! empty($this->replacementItems) && $lastReturnId) {
                $replacementReceiptId = $this->processReplacement($lastReturnId, $owner_id, $staff_id);
            }
    
            DB::commit();
    
            $message = "Successfully processed {$totalItemsProcessed} item(s) return. Total refund: ₱" . number_format($totalRefund, 2) . ".  " . 
                ($isDamaged ? 'Items recorded as damaged (no inventory change).' : 'Items restocked to inventory.');
            
            if ($replacementReceiptId) {
                $message .  ' Replacement receipt #' . str_pad($replacementReceiptId, 6, '0', STR_PAD_LEFT) . ' created.';
            }
    
            session()->flash('success', $message);
            
            $this->selectedItems = [];
            $this->selectAll = false;
            $this->closeReturnModal();
            $this->loadReturnableItems();
            $this->loadReturnHistory();
            $this->dispatch('returnProcessed', receiptId: $this->receiptId);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->modalError = 'Error processing bulk return: ' . $e->getMessage();
            \Log::error('Error in submitBulkReturn: ' . $e->getMessage());
        }
    }

    public function submitReturn()
    {
        $this->modalError = '';
        $this->modalWarning = '';
        
        $hasError = false;
        
        if (empty($this->returnQuantity) || $this->returnQuantity < 1 || $this->returnQuantity > $this->maxReturnQuantity) {
            $this->modalError = "Please enter a valid return quantity (1-{$this->maxReturnQuantity}).";
            $hasError = true;
        }
        
        if (empty($this->returnAction) || !in_array($this->returnAction, ['restock', 'damage'])) {
            $this->modalError = 'Please select whether to restock or mark as damaged.';
            $hasError = true;
        }
        
        $finalReason = !empty($this->customReturnReason) 
            ? trim($this->customReturnReason) 
            : $this->returnReason;
    
        if (empty($finalReason)) {
            $this->modalError = 'Please provide a return reason (either select from dropdown or type custom reason).';
            $hasError = true;
        }
        
        if ($hasError) {
            return;
        }
    
        try {
            $owner_id = Auth::guard('owner')->check() 
                ? Auth::guard('owner')->user()->owner_id 
                : Auth::guard('staff')->user()->owner_id;
            
            $staff_id = Auth::guard('staff')->check() 
                ? Auth::guard('staff')->user()->staff_id 
                : null;
    
            DB::beginTransaction();
    
            $returnId = DB::table('returned_items')->insertGetId([
                'item_id' => $this->selectedItemForReturn->item_id,
                'return_quantity' => (int)$this->returnQuantity,
                'return_reason' => $finalReason,
                'return_date' => now(),
                'owner_id' => $staff_id ? null : $owner_id,
                'staff_id' => $staff_id,
                'receipt_id' => null
            ]);

            // ✅ SUBTRACT RETURNED QUANTITY FROM ORIGINAL RECEIPT ITEM
            DB::table('receipt_item')
            ->where('item_id', $this->selectedItemForReturn->item_id)
            ->decrement('item_quantity', (int)$this->returnQuantity);
    
            $isDamaged = ($this->returnAction === 'damage');
            $invenCode = $this->selectedItemForReturn->inven_code;
            $prodCode = $this->selectedItemForReturn->prod_code;
    
            if ($isDamaged) {
                // ✅ DAMAGED: Record in damaged_items table (NO inventory change)
                DB::table('damaged_items')->insert([
                    'damaged_quantity' => (int)$this->returnQuantity,
                    'damaged_date' => now(),
                    'damaged_type' => $finalReason,
                    'damaged_reason' => $finalReason,
                    'return_id' => $returnId,
                    'owner_id' => $owner_id,
                    'staff_id' => $staff_id,
                    'inven_code' => $invenCode
                ]);
            } else {
                // ✅ RESTOCK: Add back to inventory
                $this->restockInventory($prodCode, (int)$this->returnQuantity, $owner_id, $invenCode);
            }
    
            $replacementReceiptId = null;
            if ($this->showReplacementSection && !empty($this->replacementItems)) {
                $replacementReceiptId = $this->processReplacement($returnId, $owner_id, $staff_id);
            }
    
            DB::commit();
    
            $message = 'Return processed successfully. ' . 
                ($isDamaged ? 'Item recorded as damaged (no inventory change).' : 'Item restocked to inventory.');
            
            if ($replacementReceiptId) {
                $message .= ' Replacement receipt #' . str_pad($replacementReceiptId, 6, '0', STR_PAD_LEFT) . ' created.';
            }
    
            session()->flash('success', $message);
            
            $this->closeReturnModal();
            $this->loadReturnableItems();
            $this->loadReturnHistory();
            $this->dispatch('returnProcessed', receiptId:  $this->receiptId);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->modalError = 'Error processing return: ' . $e->getMessage();
            \Log::error('Error in submitReturn: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.return-item');
    }
}