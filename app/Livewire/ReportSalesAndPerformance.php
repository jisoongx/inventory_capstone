<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Receipt;
use Illuminate\Support\Facades\Response;

class ReportSalesAndPerformance extends Component
{
    public $sbc; 
    public $currentMonth; 
    public $currentYear; 
    public $g;
    public $category;
    public $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    public $years;
    public $selectedYearSingle; 
    public $selectedYears;
    public $selectedMonths;
    public $dateChoice;
    public $searchWord;
    public $suggestedCategories;

    // Sales tab properties
    public $transactions;
    public $selectedReceipt;
    public $receiptDetails;
    public $dateFrom;
    public $dateTo;
    public $salesAnalytics;
    
    // Receipt modal properties
    public $showReceiptModal = false;
    public $store_info = null;
    public $showExportModal = false;

    // Product Performance properties
    public $perf;
    public $selectedCategory = 'all';
    public $selectedYear;
    public $selectedMonth;
    public $sortField = 'total_sales';
    public $order = 'desc';
    public $categories;

    public function mount() {
        $this->currentMonth = now()->month;
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->selectedYears = [now()->year];
        $this->selectedMonths = [now()->month];
        
        // Load store info properly
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

        $this->loadCategories();
    }

    public function loadCategories() {
        $owner_id = Auth::guard('owner')->user()->owner_id;
        $this->categories = collect(DB::select("
            SELECT category_id, category 
            FROM categories 
            WHERE owner_id = ?
            ORDER BY category ASC
        ", [$owner_id]));
    }


    

    public function setQuickDateRange($range) {
        switch ($range) {
            case '7days':
                $this->dateFrom = now()->subDays(6)->format('Y-m-d');
                $this->dateTo = now()->format('Y-m-d');
                break;
            case '30days':
                $this->dateFrom = now()->subDays(29)->format('Y-m-d');
                $this->dateTo = now()->format('Y-m-d');
                break;
            case '3months':
                $this->dateFrom = now()->subMonths(3)->format('Y-m-d');
                $this->dateTo = now()->format('Y-m-d');
                break;
            case 'thismonth':
                $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
                $this->dateTo = now()->format('Y-m-d');
                break;
            case 'thisyear':
                $this->dateFrom = now()->startOfYear()->format('Y-m-d');
                $this->dateTo = now()->format('Y-m-d');
                break;
            case 'lastyear':
                $this->dateFrom = now()->subYear()->startOfYear()->format('Y-m-d');
                $this->dateTo = now()->subYear()->endOfYear()->format('Y-m-d');
                break;
        }
    }

    public function updatedDateFrom() {
        $this->getTransactions();
        $this->getSalesAnalytics();
    }

    public function updatedDateTo() {
        $this->getTransactions();
        $this->getSalesAnalytics();
    }




    public function updatedCurrentMonth() {
        $this->resetPage();
    }

    public function updatedSelectedYearSingle($value) {
        $this->selectedYears = [(int) $value]; 
    }

    public function updatedSelectedCategory() {
        $this->prodPerformance();
    }

    public function updatedSelectedYear() {
        $this->prodPerformance();
    }

    public function updatedSelectedMonth() {
        $this->prodPerformance();
    }

    public function resetFilters() {
        $this->selectedYears = [now()->year];
        $this->selectedMonths = [now()->month];
    }

    public function resetDateFilters() {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function displayYears() {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        $this->years = collect(DB::select("
            SELECT DISTINCT(YEAR(receipt_date)) AS year
            FROM receipt
            WHERE owner_id = ?
            ORDER BY year DESC", 
            [$owner_id]
        ));

        if ($this->years->isEmpty()) {
            $this->years = collect([(object)['year' => now()->year]]);
        }
    }






    public function getTransactions() {
        $owner_id = Auth::guard('owner')->user()->owner_id;
        
        $this->transactions = collect(DB::select("
            SELECT 
                r.receipt_id,
                r.receipt_date,
                r.amount_paid,
                r.discount_type,
                r.discount_value,
                COUNT(DISTINCT ri.item_id) as total_items,
                SUM(ri.item_quantity) as total_quantity,
                
                COALESCE(SUM(p.selling_price * ri.item_quantity), 0) as subtotal,
                
                COALESCE(SUM(
                    CASE 
                        WHEN ri.item_discount_type = 'percent' 
                        THEN (p.selling_price * ri.item_quantity) * (ri.item_discount_value / 100)
                        ELSE ri.item_discount_value
                    END
                ), 0) as total_item_discounts,
                
                COALESCE(SUM(ri.vat_amount), 0) as total_vat
                
            FROM receipt r
            LEFT JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
            LEFT JOIN products p ON ri.prod_code = p.prod_code
            WHERE r.owner_id = ?
            AND DATE(r.receipt_date) BETWEEN ? AND ?
            GROUP BY r.receipt_id, r.receipt_date, r.amount_paid, r.discount_type, r.discount_value
            ORDER BY r.receipt_date DESC
        ", [$owner_id, $this->dateFrom, $this->dateTo]));
    
        $this->transactions = $this->transactions->map(function($transaction) {
            $subtotal = floatval($transaction->subtotal ?? 0);
            $totalItemDiscounts = floatval($transaction->total_item_discounts ?? 0);
            $afterItemDiscounts = $subtotal - $totalItemDiscounts;
            
            $receiptDiscountAmount = 0;
            if (isset($transaction->discount_value) && floatval($transaction->discount_value) > 0) {
                $discValue = floatval($transaction->discount_value);
                $discType = $transaction->discount_type ?? 'amount';
                
                if ($discType === 'percent') {
                    $receiptDiscountAmount = $afterItemDiscounts * ($discValue / 100.0);
                } else {
                    $receiptDiscountAmount = $discValue;
                }
            }
            
            $afterReceiptDiscount = $afterItemDiscounts - $receiptDiscountAmount;
            $totalVat = floatval($transaction->total_vat ?? 0);
            $totalAmount = $afterReceiptDiscount + $totalVat;
            $amountPaid = floatval($transaction->amount_paid ?? 0);
            $change = $amountPaid - $totalAmount;
            
            $transaction->total_amount = $totalAmount;
            $transaction->change = $change;
            $transaction->subtotal_raw = $subtotal;
            $transaction->total_item_discounts_raw = $totalItemDiscounts;
            $transaction->receipt_discount_amount = $receiptDiscountAmount;
            $transaction->total_vat_raw = $totalVat;
            
            return $transaction;
        });
    }

    public function getSalesAnalytics() {
        $owner_id = Auth::guard('owner')->user()->owner_id;
        
        $analytics = DB::selectOne("
            SELECT 
                COUNT(DISTINCT r.receipt_id) as total_transactions,
                COALESCE(SUM(ri.item_quantity), 0) as total_items_sold,
                COALESCE(SUM(p.cost_price * ri.item_quantity), 0) as total_cogs
            FROM receipt r
            LEFT JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
            LEFT JOIN products p ON ri.prod_code = p.prod_code
            WHERE r.owner_id = ?
            AND DATE(r.receipt_date) BETWEEN ? AND ?
        ", [$owner_id, $this->dateFrom, $this->dateTo]);

        $grossSales = $this->transactions->sum('total_amount');
        $avgTransaction = $analytics->total_transactions > 0 
            ? $grossSales / $analytics->total_transactions 
            : 0;

        $this->salesAnalytics = (object) [
            'total_transactions' => $analytics->total_transactions ?? 0,
            'total_items_sold' => $analytics->total_items_sold ?? 0,
            'gross_sales' => $grossSales,
            'total_cogs' => $analytics->total_cogs ?? 0,
            'net_profit' => $grossSales - ($analytics->total_cogs ?? 0),
            'avg_transaction_value' => $avgTransaction,
        ];
    }

    public function viewReceipt($receiptId) {
        try {
            $owner_id = Auth::guard('owner')->user()->owner_id;
            
            $this->receiptDetails = Receipt::with([
                'receiptItems.product:prod_code,name,selling_price',
                'owner:owner_id,firstname,store_name,store_address',
                'staff:staff_id,firstname'
            ])
            ->where('receipt_id', $receiptId)
            ->where('owner_id', $owner_id)
            ->first();
    
            if ($this->receiptDetails) {
                $items = $this->receiptDetails->receiptItems ?? collect();
                $subtotal = 0.0;
                $totalItemDiscounts = 0.0;
                $totalVat = 0.0;
    
                foreach ($items as $it) {
                    $price = floatval(data_get($it, 'product.selling_price', 0));
                    $qty = intval($it->item_quantity ?? 0);
                    $lineTotal = $price * $qty;
                    $subtotal += $lineTotal;
    
                    $itemDiscountValue = floatval($it->item_discount_value ?? 0);
                    $itemDiscountType = $it->item_discount_type ?? 'percent';
    
                    $discountAmount = 0.0;
                    if ($itemDiscountValue > 0) {
                        if ($itemDiscountType === 'percent') {
                            $discountAmount = $lineTotal * ($itemDiscountValue / 100.0);
                        } else {
                            $discountAmount = $itemDiscountValue;
                        }
                    }
                    $totalItemDiscounts += $discountAmount;
                    $totalVat += floatval($it->vat_amount ?? 0);
                }
    
                $receiptDiscountAmount = 0.0;
                $afterItemDiscounts = $subtotal - $totalItemDiscounts;
    
                if (isset($this->receiptDetails->discount_value) && floatval($this->receiptDetails->discount_value) > 0) {
                    $discValue = floatval($this->receiptDetails->discount_value);
                    $discType = $this->receiptDetails->discount_type ?? 'amount';
                    
                    if ($discType === 'percent') {
                        $receiptDiscountAmount = $afterItemDiscounts * ($discValue / 100.0);
                    } else {
                        $receiptDiscountAmount = $discValue;
                    }
                }
    
                $afterReceiptDiscount = $afterItemDiscounts - $receiptDiscountAmount;
                $finalTotal = $afterReceiptDiscount + $totalVat;
                $amountPaid = floatval($this->receiptDetails->amount_paid ?? 0);
                $change = $amountPaid - $finalTotal;
    
                $this->receiptDetails->computed_subtotal = $subtotal;
                $this->receiptDetails->total_item_discounts = $totalItemDiscounts;
                $this->receiptDetails->receipt_discount_amount = $receiptDiscountAmount;
                $this->receiptDetails->vat_amount = $totalVat;
                $this->receiptDetails->vat_applied = $totalVat > 0;
                $this->receiptDetails->computed_total = $finalTotal;
                $this->receiptDetails->computed_change = $change;
    
                $this->showReceiptModal = true;
                $this->selectedReceipt = $receiptId;
            } else {
                session()->flash('error', 'Receipt not found.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error loading receipt: ' . $e->getMessage());
        }
    }

    public function closeReceiptModal() {
        $this->showReceiptModal = false;
        $this->selectedReceipt = null;
        $this->receiptDetails = null;
    }

    public function toggleExportModal() {
        $this->showExportModal = !$this->showExportModal;
    }

    public function exportToCSV() {
        $fileName = 'Sales_Report_' . date('Ymd_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [$this->store_info->store_name]);
            fputcsv($file, ['Sales Report']);
            fputcsv($file, ['Period: ' . date('M d, Y', strtotime($this->dateFrom)) . ' - ' . date('M d, Y', strtotime($this->dateTo))]);
            fputcsv($file, []);
            
            fputcsv($file, ['Summary']);
            fputcsv($file, ['Total Transactions', $this->salesAnalytics->total_transactions]);
            fputcsv($file, ['Total Items Sold', $this->salesAnalytics->total_items_sold]);
            fputcsv($file, ['Gross Sales', number_format($this->salesAnalytics->gross_sales, 2)]);
            fputcsv($file, ['Total COGS', number_format($this->salesAnalytics->total_cogs, 2)]);
            fputcsv($file, ['Net Profit', number_format($this->salesAnalytics->net_profit, 2)]);
            fputcsv($file, ['Avg Transaction Value', number_format($this->salesAnalytics->avg_transaction_value, 2)]);
            fputcsv($file, []);
            
            fputcsv($file, [
                'Receipt No.',
                'Date & Time',
                'Total Quantity',
                'Subtotal',
                'Item Discounts',
                'Receipt Discount',
                'Total Discounts',
                'VAT',
                'Total Amount',
                'Amount Paid',
                'Change'
            ]);
            
            foreach ($this->transactions as $transaction) {
                $totalDiscounts = $transaction->total_item_discounts_raw + $transaction->receipt_discount_amount;
                
                fputcsv($file, [
                    str_pad($transaction->receipt_id, 6, '0', STR_PAD_LEFT),
                    date('m/d/Y h:i A', strtotime($transaction->receipt_date)),
                    $transaction->total_quantity,
                    number_format($transaction->subtotal_raw, 2),
                    number_format($transaction->total_item_discounts_raw, 2),
                    number_format($transaction->receipt_discount_amount, 2),
                    number_format($totalDiscounts, 2),
                    number_format($transaction->total_vat_raw, 2),
                    number_format($transaction->total_amount, 2),
                    number_format($transaction->amount_paid, 2),
                    number_format($transaction->change, 2)
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    // Add to class properties RETURN MODAL
    public $showReturnModal = false;
    public $selectedItemForReturn = null;
    public $returnQuantity = 1;
    public $returnReason = '';
    public $isDamaged = false;
    public $damageType = '';
    public $maxReturnQuantity = 0;

// Add these methods

public function openReturnModal($itemId)
{
    try {
        $owner_id = Auth::guard('owner')->user()->owner_id;
        
        // Get item details including already returned quantity
        $item = DB::selectOne("
            SELECT 
                ri.*,
                p.name as product_name,
                p.selling_price,
                COALESCE(SUM(ret.return_quantity), 0) as already_returned
            FROM receipt_item ri
            JOIN products p ON ri.prod_code = p.prod_code
            JOIN receipt r ON ri.receipt_id = r.receipt_id
            LEFT JOIN returned_items ret ON ret.item_id = ri.item_id
            WHERE ri.item_id = ?
            AND r.owner_id = ?
            GROUP BY ri.item_id, ri.item_quantity, ri.prod_code, ri.receipt_id, 
                     ri.item_discount_type, ri.item_discount_value, ri.vat_amount,
                     p.name, p.selling_price
        ", [$itemId, $owner_id]);

        if (!$item) {
            session()->flash('error', 'Item not found.');
            return;
        }

        $this->selectedItemForReturn = $item;
        $this->maxReturnQuantity = $item->item_quantity - $item->already_returned;
        
        if ($this->maxReturnQuantity <= 0) {
            session()->flash('error', 'This item has already been fully returned.');
            return;
        }
        
        $this->returnQuantity = min(1, $this->maxReturnQuantity);
        $this->returnReason = '';
        $this->isDamaged = false;
        $this->damageType = '';
        $this->showReturnModal = true;
        
    } catch (\Exception $e) {
        session()->flash('error', 'Error loading item: ' . $e->getMessage());
    }
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
        'returnQuantity.max' => 'Return quantity cannot exceed ' . $this->maxReturnQuantity,
        'returnReason.required' => 'Please provide a reason for the return.',
        'returnReason.min' => 'Reason must be at least 3 characters.',
        'damageType.required_if' => 'Please select the damage type.'
    ]);

    try {
        $owner_id = Auth::guard('owner')->user()->owner_id;
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
            // Record in damaged_items table
            DB::table('damaged_items')->insert([
                'prod_code' => $this->selectedItemForReturn->prod_code,
                'damaged_quantity' => $this->returnQuantity,
                'damaged_date' => now(),
                'damaged_type' => $this->damageType,
                'damaged_reason' => $this->returnReason,
                'return_id' => $returnId,
                'owner_id' => $owner_id,
                'staff_id' => $staff_id
            ]);

            // DECREASE inventory for damaged items (FIFO - remove from oldest stock first)
            $this->decrementInventoryStock(
                $this->selectedItemForReturn->prod_code, 
                $this->returnQuantity, 
                $owner_id
            );
        } else {
            // Return to inventory for non-damaged items (add back to newest inventory batch)
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
                // Get product details for category_id
                $product = DB::table('products')
                    ->where('prod_code', $this->selectedItemForReturn->prod_code)
                    ->first();

                DB::table('inventory')->insert([
                    'prod_code' => $this->selectedItemForReturn->prod_code,
                    'stock' => $this->returnQuantity,
                    'date_added' => now(),
                    'owner_id' => $owner_id,
                    'category_id' => $product->category_id
                ]);
            }
        }

        DB::commit();

        session()->flash('success', 'Return processed successfully. ' . 
            ($this->isDamaged ? 'Item recorded as damaged.' : 'Stock updated.'));
        
        $this->closeReturnModal();
        $this->viewReceipt($this->selectedReceipt); // Refresh receipt details
        
    } catch (\Exception $e) {
        DB::rollBack();
        session()->flash('error', 'Error processing return: ' . $e->getMessage());
    }
}








    public function salesByCategory() {
        $years = is_array($this->selectedYears) ? $this->selectedYears : [$this->selectedYears ?: now()->year];
        $months = is_array($this->selectedMonths) ? $this->selectedMonths : [$this->selectedMonths ?: now()->month];

        $yearPlaceholders = implode(',', array_fill(0, count($years), '?'));
        $monthPlaceholders = implode(',', array_fill(0, count($months), '?'));

        $owner_id = Auth::guard('owner')->user()->owner_id;
        
        $sql = "
            SELECT
                c.category,
                COALESCE(SUM(ritems.item_quantity), 0) AS unit_sold,
                COALESCE(SUM(p.selling_price * ritems.item_quantity), 0) AS total_sales,
                COALESCE(SUM(p.cost_price * ritems.item_quantity), 0) AS cogs,

                CASE
                    WHEN COALESCE(SUM(p.selling_price * ritems.item_quantity), 0) = 0 THEN 0
                    ELSE (
                        (SUM(p.selling_price * ritems.item_quantity) - SUM(p.cost_price * ritems.item_quantity))
                        / SUM(p.selling_price * ritems.item_quantity)
                    ) * 100
                END AS gross_margin,

                COALESCE((
                    SELECT p2.name
                    FROM products p2
                    JOIN receipt_item ri2 ON p2.prod_code = ri2.prod_code
                    JOIN receipt r2 ON r2.receipt_id = ri2.receipt_id
                    WHERE p2.category_id = c.category_id
                    AND r2.owner_id = ?
                    AND YEAR(r2.receipt_date) IN ($yearPlaceholders)
                    AND MONTH(r2.receipt_date) IN ($monthPlaceholders)
                    GROUP BY p2.prod_code, p2.name
                    ORDER BY SUM(ri2.item_quantity) DESC
                    LIMIT 1
                ), '—') AS top_product_unit,

                COALESCE((
                    SELECT p2.name
                    FROM products p2
                    JOIN receipt_item ri2 ON p2.prod_code = ri2.prod_code
                    JOIN receipt r2 ON r2.receipt_id = ri2.receipt_id
                    WHERE p2.category_id = c.category_id
                    AND r2.owner_id = ?
                    AND YEAR(r2.receipt_date) IN ($yearPlaceholders)
                    AND MONTH(r2.receipt_date) IN ($monthPlaceholders)
                    GROUP BY p2.prod_code, p2.name
                    ORDER BY SUM(ri2.item_quantity * p2.selling_price) DESC
                    LIMIT 1
                ), '—') AS top_product_sales,

                COALESCE(i.stock, 0) AS stock_left,

                COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(
                    AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 0
                ) AS velocity_ratio,

                COALESCE(i.stock, 0) / NULLIF(
                    COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(COUNT(DISTINCT ritems.receipt_date), 0), 0
                ) AS days_of_supply
                
            FROM categories c
            LEFT JOIN products p
                ON c.category_id = p.category_id
                AND c.owner_id = ?
            LEFT JOIN (
                SELECT c2.category_id, SUM(inv.stock) AS stock
                FROM inventory inv
                JOIN products p2 ON inv.prod_code = p2.prod_code
                JOIN categories c2 ON p2.category_id = c2.category_id
                WHERE p2.owner_id = ?
                    and p2.prod_status = 'active'
                GROUP BY c2.category_id
            ) i ON i.category_id = p.category_id
            LEFT JOIN (
                SELECT ri.prod_code, ri.item_quantity, ri.receipt_id, r.owner_id, r.receipt_date
                FROM receipt_item ri
                JOIN receipt r ON r.receipt_id = ri.receipt_id
                WHERE r.owner_id = ?
                AND YEAR(r.receipt_date) IN ($yearPlaceholders)
                AND MONTH(r.receipt_date) IN ($monthPlaceholders)
            ) AS ritems ON p.prod_code = ritems.prod_code
            WHERE c.owner_id = ?
                and p.prod_status = 'active'
            GROUP BY c.category, c.category_id, i.stock
            ORDER BY c.category ASC
        ";

        $bindings = array_merge(
            [$owner_id], $years, $months,
            [$owner_id], $years, $months,
            [$owner_id, $owner_id, $owner_id], $years, $months,
            [$owner_id]
        );



        $collection = collect(DB::select($sql, $bindings));

        $avgStock = $collection->avg('stock_left');
        $avgUnitSold = $collection->avg('unit_sold');
        $avgSales = $collection->avg('total_sales');
        $avgCogsRatio = $collection->map(function($i){
            return $i->total_sales == 0 ? 0 : $i->cogs / $i->total_sales;
        })->avg();
        $avgMargin = $collection->avg('gross_margin');

        $maxUnitSold = $collection->max('unit_sold');
        $maxSales = $collection->max('total_sales');

        $avgStockLeft = $collection->avg('stock_left');
        $medianStockLeft = $collection->median('stock_left');
        $avgTotalSales = $collection->avg('total_sales');
        $medianTotalSales = $collection->median('total_sales');
        $avgGrossMargin = $collection->avg('gross_margin');
        $medianGrossMargin = $collection->median('gross_margin');
        $medianDaysOfSupply = $collection->median('days_of_supply');

        $count = $collection->count();
        $variance = $collection->map(fn($i) => pow($i->unit_sold - $avgUnitSold, 2))->sum() / $count;
        $stddevUnitSold = sqrt($variance);

        $this->sbc = $collection->map(function ($item) use (
            $stddevUnitSold,
            $avgUnitSold,
            $medianDaysOfSupply,
            $avgTotalSales,
            $medianTotalSales,
            $avgGrossMargin,
            $medianGrossMargin
        ) {
            $insights = [];
            
            // ===== SALES VELOCITY INSIGHTS =====
            $zScore = $stddevUnitSold > 0 ? ($item->unit_sold - $avgUnitSold) / $stddevUnitSold : 0;
            
            if ($zScore > 1.5) {
                $insights[] = "This is your top performing category with exceptional sales volume.";
            } elseif ($zScore > 0.5) {
                $insights[] = "Strong performer that consistently sells above average.";
            } elseif ($zScore < -1) {
                $insights[] = "Sales performance is significantly below expectations.";
            } elseif ($zScore < -0.3) {
                $insights[] = "Moving slower than most other categories.";
            } else {
                $insights[] = "Sales performance is steady and meets expectations.";
            }
            
            // ===== PROFITABILITY INSIGHTS =====
            if ($item->gross_margin > $avgGrossMargin * 1.3) {
                $insights[] = "Excellent profit margins make this a highly profitable category.";
            } elseif ($item->gross_margin > $avgGrossMargin * 1.1) {
                $insights[] = "Generates healthy profit margins for your business.";
            } elseif ($item->gross_margin < $medianGrossMargin * 0.8 && $item->gross_margin > 0) {
                $insights[] = "Profit margins are lower than desired, consider reviewing pricing.";
            } else {
                $insights[] = "Maintains acceptable profit margins within normal range.";
            }
            
            // ===== REVENUE CONTRIBUTION INSIGHTS =====
            if ($item->total_sales > $avgTotalSales * 1.8) {
                $insights[] = "Major revenue driver contributing significantly to overall sales.";
            } elseif ($item->total_sales > $avgTotalSales * 1.3) {
                $insights[] = "Important revenue source for your business.";
            } elseif ($item->total_sales < $medianTotalSales * 0.4 && $item->total_sales > 0) {
                $insights[] = "Revenue contribution is minimal compared to other categories.";
            }
            
            // ===== STOCK LEVEL INSIGHTS =====
            if ($item->days_of_supply > $medianDaysOfSupply * 2.5) {
                $insights[] = "Inventory levels are too high, consider reducing future orders.";
            } elseif ($item->days_of_supply > $medianDaysOfSupply * 1.8) {
                $insights[] = "Stock levels are comfortable with plenty of supply on hand.";
            } elseif ($item->days_of_supply < 7 && $item->velocity_ratio > 1.2) {
                $insights[] = "Critical stock shortage for this fast-selling category, restock immediately.";
            } elseif ($item->days_of_supply < $medianDaysOfSupply * 0.4 && $item->unit_sold > 0) {
                $insights[] = "Stock is running low and needs replenishment soon.";
            } elseif ($item->days_of_supply < 14 && $item->unit_sold > 0) {
                $insights[] = "Current stock will last less than two weeks.";
            }
            
            // ===== VELOCITY RATING =====
            if ($item->velocity_ratio > 1.8) {
                $insights[] = "Products in this category are moving much faster than average.";
            } elseif ($item->velocity_ratio < 0.4 && $item->velocity_ratio > 0) {
                $insights[] = "Turnover rate is slow, items are staying on shelves longer.";
            }
            
            return (object) array_merge((array) $item, [
                'insight' => $insights,
                'insight_summary' => implode(' ', $insights)
            ]);
        });

        
        if (!empty($this->searchWord)) {
            $search = strtolower($this->searchWord);
            $this->sbc = $this->sbc->filter(function($item) use ($search) {
                return str_contains(strtolower($item->category), $search);
            })->values();
        }



    }



    public function sortBy($field) {
        if ($this->sortField === $field) {
            $this->order = $this->order === 'asc' ? 'desc' : 'asc';
        } else {
            $this->order = 'asc';
        }

        $this->sortField = $field;
        $this->prodPerformance();
    }


    public function prodPerformance() {
        $owner_id = Auth::guard('owner')->user()->owner_id;
        $latestYear = $this->selectedYear ?? now()->year;
        $month = $this->selectedMonth ?? now()->month;

        $perf = collect(DB::select("
            SELECT p.prod_code, p.name AS product_name, c.category AS category, c.category_id,
                COALESCE(SUM(ri.item_quantity), 0) AS unit_sold,
                COALESCE(SUM(p.selling_price * ri.item_quantity), 0) AS total_sales,
                COALESCE(SUM(p.cost_price * ri.item_quantity), 0) AS cogs,
                (COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0)) AS profit,

                ((COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0))
                    / NULLIF(COALESCE(SUM(p.selling_price * ri.item_quantity), 0), 0)) * 100 AS profit_margin_percent,

                COALESCE(
                    (SUM(p.selling_price * ri.item_quantity)/NULLIF(total.total_sales_all, 0)) * 100,0
                ) AS contribution_percent,

                COALESCE(inv.total_stock, 0) AS remaining_stocks,
                COALESCE(DATEDIFF(MAX(r.receipt_date), MIN(r.receipt_date)) + 1, 0) AS days_active,

                CASE 
                    WHEN COALESCE(inv.total_stock, 0) = 0 
                        AND COALESCE(SUM(ri.item_quantity), 0) > 0
                        THEN 'Out of stock. Reorder needed.'
                    
                    WHEN COALESCE(inv.total_stock, 0) = 0
                        THEN 'Out of stock with no recent sales.'
                    
                    WHEN COALESCE(inv.total_stock, 0) / NULLIF(
                            CASE 
                                WHEN DATEDIFF(MAX(r.receipt_date), MIN(r.receipt_date)) + 1 > 0 
                                THEN COALESCE(SUM(ri.item_quantity), 0) / (DATEDIFF(MAX(r.receipt_date), MIN(r.receipt_date)) + 1)
                                ELSE 0 
                            END, 0
                        ) < 3
                        AND COALESCE(SUM(ri.item_quantity), 0) > 0
                        THEN 'Low stock. Reorder soon.'
                    
                    WHEN COALESCE(SUM(ri.item_quantity), 0) = 0 
                        AND COALESCE(inv.total_stock, 0) > 0
                        THEN 'No sales this period.'
                    
                    WHEN COALESCE(SUM(ri.item_quantity), 0) = 0
                        THEN 'No activity.'
                    
                    WHEN (COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0)) <= 0 
                        THEN 'Unprofitable. Losing money.'
                    
                    WHEN ((COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0))
                        / NULLIF(COALESCE(SUM(p.selling_price * ri.item_quantity), 0), 0)) * 100 < 10
                        THEN 'Low margin. Review pricing.'
                    
                    WHEN ((COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0))
                        / NULLIF(COALESCE(SUM(p.selling_price * ri.item_quantity), 0), 0)) * 100 >= 20
                        AND COALESCE(SUM(ri.item_quantity), 0) >= 10
                        THEN 'Performing well.'
                    
                    WHEN ((COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0))
                        / NULLIF(COALESCE(SUM(p.selling_price * ri.item_quantity), 0), 0)) * 100 >= 20
                        THEN 'Good margin, low volume.'
                    
                    WHEN ((COALESCE(SUM(p.selling_price * ri.item_quantity), 0) - COALESCE(SUM(p.cost_price * ri.item_quantity), 0))
                        / NULLIF(COALESCE(SUM(p.selling_price * ri.item_quantity), 0), 0)) * 100 >= 10
                        THEN 'Moderate performance.'
                    
                    ELSE 'Needs attention.'
                END AS insight

            FROM products AS p
            LEFT JOIN (
                SELECT i.prod_code, SUM(i.stock) AS total_stock
                FROM inventory i
                JOIN products p2 ON i.prod_code = p2.prod_code
                WHERE p2.owner_id = ?
                    AND (i.expiration_date IS NULL OR i.expiration_date > CURDATE())
                    and p2.prod_status = 'active'
                GROUP BY i.prod_code
            ) inv ON inv.prod_code = p.prod_code
            LEFT JOIN categories AS c 
                ON p.category_id = c.category_id
            LEFT JOIN receipt AS r 
                ON r.owner_id = p.owner_id
                AND MONTH(r.receipt_date) = ? 
                AND YEAR(r.receipt_date) = ?
            LEFT JOIN receipt_item AS ri 
                ON ri.prod_code = p.prod_code
                AND ri.receipt_id = r.receipt_id
            LEFT JOIN (
                SELECT 
                    p2.owner_id, 
                    SUM(p2.selling_price * ri2.item_quantity) AS total_sales_all
                FROM products p2
                JOIN receipt_item ri2 ON ri2.prod_code = p2.prod_code
                JOIN receipt r2 ON r2.receipt_id = ri2.receipt_id
                WHERE MONTH(r2.receipt_date) = ? AND YEAR(r2.receipt_date) = ?
                    and p2.prod_status = 'active'
                GROUP BY p2.owner_id
            ) total ON total.owner_id = p.owner_id
            WHERE p.owner_id = ?
                and p.prod_status = 'active'
            GROUP BY p.prod_code, p.name, c.category, p.owner_id, c.category_id, total.total_sales_all, inv.total_stock
        ", [$owner_id, $month, $latestYear, $month, $latestYear, $owner_id]));

        if (!empty($this->selectedCategory) && $this->selectedCategory !== 'all') {
            $perf = $perf->where('category_id', (int) $this->selectedCategory);
        }

        $perf = $perf->sortBy(function ($item) {
            return $item->{$this->sortField};
        }, SORT_REGULAR, $this->order === 'desc')->values();

        $this->perf = $perf->values();
    }

    public function pollAll() {
        $this->salesByCategory();
        $this->prodPerformance();
        $this->getTransactions();
    }

    public function render() {
        $this->salesByCategory();
        $this->displayYears();
        $this->getTransactions();
        $this->getSalesAnalytics();
        $this->prodPerformance();
        
        return view('livewire.report-sales-and-performance');
    }
}