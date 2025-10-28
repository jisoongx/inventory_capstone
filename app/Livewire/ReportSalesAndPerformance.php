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
    public array $selectedYears = [];
    public array $selectedMonths = [];
    public $peak;
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

    public function salesByCategory() {
        $years = $this->selectedYears ?: [now()->year];
        $months = $this->selectedMonths ?: [now()->month];

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
                ) AS days_of_supply,

                CASE
                    WHEN COALESCE(i.stock, 0) = 0 
                        AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        AND COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 0) > 1.2
                        THEN 'URGENT: Fast-moving category out of stock. Immediate reorder required.'
                    
                    WHEN COALESCE(i.stock, 0) = 0 AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Out of stock with recent sales. Reorder needed.'
                    
                    WHEN COALESCE(i.stock, 0) = 0 AND COALESCE(SUM(ritems.item_quantity), 0) = 0
                        THEN 'Out of stock and no sales for this month. Evaluate demand before reordering.'
                    
                    WHEN COALESCE(i.stock, 0) / NULLIF(
                            COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(COUNT(DISTINCT ritems.receipt_date), 0), 0
                        ) < 3 AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Stock critically low. Will run out in less than 3 days at current rate.'
                    
                    WHEN COALESCE(i.stock, 0) / NULLIF(
                            COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(COUNT(DISTINCT ritems.receipt_date), 0), 0
                        ) BETWEEN 3 AND 7 AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Low stock. Reorder within this week to avoid shortage.'
                    
                    WHEN COALESCE(SUM(ritems.item_quantity), 0) = 0 AND COALESCE(i.stock, 0) > 0
                        THEN 'No recent sales despite stock availability. Reassess demand or consider promotions.'
                    
                    WHEN COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 0) > 1.5
                        AND ((SUM(p.selling_price * ritems.item_quantity) - SUM(p.cost_price * ritems.item_quantity))
                            / NULLIF(SUM(p.selling_price * ritems.item_quantity), 0)) * 100 > 25
                        AND COALESCE(SUM(ritems.item_quantity), 0) > GREATEST(
                            AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER () * 1.5, 10
                        )
                        THEN 'Star performer: Fast sales with strong margins. Consider expanding stock.'
                    
                    WHEN COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 0) > 1.5
                        AND ((SUM(p.selling_price * ritems.item_quantity) - SUM(p.cost_price * ritems.item_quantity))
                            / NULLIF(SUM(p.selling_price * ritems.item_quantity), 0)) * 100 < 15
                        AND COALESCE(SUM(ritems.item_quantity), 0) > GREATEST(
                            AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER () * 1.5, 10
                        )
                        THEN 'Fast-moving but low margins. Review pricing or supplier costs.'
                    
                    WHEN COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 0) > 1.2
                        AND COALESCE(SUM(ritems.item_quantity), 0) > GREATEST(
                            AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 5
                        )
                        THEN 'Good sales velocity. Maintain stock levels and monitor trends.'
                    
                    WHEN COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 0) < 0.5
                        AND ((SUM(p.selling_price * ritems.item_quantity) - SUM(p.cost_price * ritems.item_quantity))
                            / NULLIF(SUM(p.selling_price * ritems.item_quantity), 0)) * 100 < 15
                        AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Slow-moving with poor margins. Consider discontinuing or clearance.'
                    
                    WHEN COALESCE(SUM(ritems.item_quantity), 0) / NULLIF(AVG(COALESCE(SUM(ritems.item_quantity), 0)) OVER (), 0) < 0.5
                        AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Slow-moving category. Reduce stock levels to free up capital.'
                    
                    WHEN ((SUM(p.selling_price * ritems.item_quantity) - SUM(p.cost_price * ritems.item_quantity))
                        / NULLIF(SUM(p.selling_price * ritems.item_quantity), 0)) * 100 < 10
                        AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Low profit margin. Review pricing or supplier costs.'
                    
                    WHEN ((SUM(p.selling_price * ritems.item_quantity) - SUM(p.cost_price * ritems.item_quantity))
                        / NULLIF(SUM(p.selling_price * ritems.item_quantity), 0)) * 100 BETWEEN 10 AND 25
                        AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Steady sales with modest profit. Maintain visibility and monitor competition.'
                    
                    WHEN ((SUM(p.selling_price * ritems.item_quantity) - SUM(p.cost_price * ritems.item_quantity))
                        / NULLIF(SUM(p.selling_price * ritems.item_quantity), 0)) * 100 > 25
                        AND COALESCE(SUM(ritems.item_quantity), 0) > 0
                        THEN 'Strong profit margins. Consider promotions to boost volume.'
                    
                    ELSE 'Stable category performance. Continue monitoring trends.'
                END AS insight
                
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
            GROUP BY c.category, c.category_id, i.stock
            ORDER BY c.category ASC
        ";

        $bindings = array_merge(
            [$owner_id], $years, $months,
            [$owner_id], $years, $months,
            [$owner_id, $owner_id, $owner_id], $years, $months,
            [$owner_id]
        );

        $this->sbc = collect(DB::select($sql, $bindings));

        if (!empty($this->searchWord)) {
            $search = strtolower($this->searchWord);
            $this->sbc = $this->sbc->filter(function($item) use ($search) {
                return str_contains(strtolower($item->category), $search);
            })->values();
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

    public function peakHour() {
        if ($this->dateChoice === null) {
            $this->dateChoice = now()->toDateString();
        }

        $owner_id = Auth::guard('owner')->user()->owner_id;

        $this->peak = collect(DB::select("
            WITH RECURSIVE time_slots AS (
                SELECT 
                    DATE_FORMAT(MIN(receipt_date), '%Y-%m-%d %H:00:00') AS slot_start,
                    DATE_FORMAT(DATE_ADD(MIN(receipt_date), INTERVAL 1 HOUR), '%Y-%m-%d %H:00:00') AS slot_end,
                    DATE(MIN(receipt_date)) AS day
                FROM receipt
                WHERE DATE(receipt_date) = ? AND owner_id = ?

                UNION ALL

                SELECT 
                    DATE_FORMAT(slot_end, '%Y-%m-%d %H:00:00'),
                    DATE_FORMAT(DATE_ADD(slot_end, INTERVAL 1 HOUR), '%Y-%m-%d %H:00:00'),
                    day
                FROM time_slots
                WHERE slot_end < (
                    SELECT MAX(receipt_date) 
                    FROM receipt 
                    WHERE DATE(receipt_date) = ? AND owner_id = ?
                )
            )

            SELECT 
                DAYNAME(ts.day) AS dayName,
                CONCAT(DATE_FORMAT(ts.slot_start, '%h:%i %p'), ' - ', DATE_FORMAT(ts.slot_end, '%h:%i %p')) AS time_slot,
                COUNT(DISTINCT r.receipt_id) AS transactions,                        
                COALESCE(SUM(ri.item_quantity * p.selling_price), 0) AS sales,
                CASE WHEN COUNT(DISTINCT r.receipt_id) > 0
                    THEN ROUND(COALESCE(SUM(ri.item_quantity * p.selling_price), 0) / COUNT(DISTINCT r.receipt_id), 2)
                    ELSE 0 END AS avg_value
            FROM time_slots ts
            LEFT JOIN receipt r 
                ON r.receipt_date >= ts.slot_start
            AND r.receipt_date <  ts.slot_end
            AND r.owner_id = ?
            AND DATE(r.receipt_date) = ?
            LEFT JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
            LEFT JOIN products p ON ri.prod_code = p.prod_code
            WHERE DATE(ts.day) = ?
            GROUP BY ts.day, ts.slot_start, ts.slot_end
            ORDER BY ts.slot_start
        ", [$this->dateChoice, $owner_id, $this->dateChoice, $owner_id, $owner_id, $this->dateChoice, $this->dateChoice]));
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
                GROUP BY p2.owner_id
            ) total ON total.owner_id = p.owner_id
            WHERE p.owner_id = ?
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

    public function render() {
        $this->peakHour();
        $this->salesByCategory();
        $this->displayYears();
        $this->getTransactions();
        $this->getSalesAnalytics();
        $this->prodPerformance();
        
        return view('livewire.report-sales-and-performance');
    }
}