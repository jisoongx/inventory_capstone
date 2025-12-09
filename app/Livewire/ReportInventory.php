<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Row;

class ReportInventory extends Component
{
    public $expiredProd;
    public $selectedCategory;
    public $selectedRange = 60; 
    public $selectedLossType = null; 

    public $category;
    public $years;

    public $lossRep;
    public $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    public $selectedMonths = null;
    public $selectedYears  = null;

    public $damagedRep;
    public $highlightRow = null;
    public $showReasonModal = false;
    public $damagedReason; 
    public $damagedQuantitySummary;
    public $currentDamagedId;

    public $stock;
    public $selectedStockStatus = 'active';

    public $showSuccess = false;


    public function mount() {

        if (!Auth::guard('owner')->check()) {
            abort(403, 'Unauthorized access.');
        }
        
        $owner_id = Auth::guard('owner')->user()->owner_id;

        $this->selectedMonths = now()->month;
        $this->selectedYears = now()->year;

        $this->category = collect(DB::select("
            select category_id as cat_id,
                category as cat_name
            from categories
            where owner_id = ?
            order by category
        ", [$owner_id]));

        $this->years = collect(DB::select("
            SELECT DISTINCT(YEAR(receipt_date)) AS year
            FROM receipt
            WHERE owner_id = ?
            ORDER BY year DESC", 
            [$owner_id]
        ))->pluck('year');

    }
    




    public function updatedSelectedCategory() {
        $this->expired();
    }
    
    public function expired() {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        DB::statement("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");
        
        // Get raw data (same SQL as before)
        $expired = collect(DB::select("
            SELECT 
                COALESCE(i.batch_number, 'Initial Batch') AS batch_num,
                i.inven_code,
                p.prod_code,
                p.name AS prod_name,
                i.stock AS expired_stock,
                i.expiration_date AS date,
                c.category AS cat_name,
                c.category_id,
                p.selling_price AS cost,
                DATEDIFF(i.expiration_date, CURDATE()) AS days_until_expiry,
                SUM(p.selling_price * i.stock) AS total_loss,
                MONTH(i.expiration_date) AS expiry_month,
                
                CASE 
                    WHEN c.category LIKE '%Meat%' 
                    OR c.category LIKE '%Seafood%' 
                    OR c.category LIKE '%Dairy%' 
                    OR c.category LIKE '%Bakery%' 
                    OR c.category LIKE '%Produce%' 
                    OR c.category LIKE '%Vegetable%' 
                    OR c.category LIKE '%Fruit%' 
                    OR c.category LIKE '%Poultry%' 
                    THEN 1
                    ELSE 0
                END AS is_highly_perishable,
                
                COALESCE(SUM(CASE 
                    WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                    THEN ri.item_quantity ELSE 0 
                END) / 30.0, 0) AS avg_daily_sales_current,
                
                COALESCE(sf.monthly_forecast, 0) AS seasonal_forecast,
                COALESCE(sf.avg_all_past_years, 0) AS seasonal_avg_past,
                COALESCE(sf.years_of_history, 0) AS years_of_history,
                COALESCE(sf.monthly_forecast / DAY(LAST_DAY(i.expiration_date)), 0) AS seasonal_daily_rate,
                
                CASE 
                    WHEN COALESCE(sf.avg_all_past_years, 0) > 0 THEN 
                        COALESCE(sf.monthly_forecast / DAY(LAST_DAY(i.expiration_date)), 0)
                    ELSE 
                        COALESCE(SUM(CASE 
                            WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                            THEN ri.item_quantity ELSE 0 
                        END) / 30.0, 0)
                END AS best_daily_rate,
                
                CASE 
                    WHEN COALESCE(sf.avg_all_past_years, 0) > 0 THEN 1
                    ELSE 0
                END AS has_seasonal_data
                
            FROM inventory i
            JOIN products p ON i.prod_code = p.prod_code
            JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN receipt_item ri ON ri.prod_code = p.prod_code
            LEFT JOIN receipt r ON ri.receipt_id = r.receipt_id
            LEFT JOIN vw_product_seasonal_forecast_all_months sf 
                ON p.prod_code = sf.prod_code 
                AND p.owner_id = sf.owner_id
                AND MONTH(i.expiration_date) = sf.month_num
            WHERE p.owner_id = ?
                AND i.expiration_date IS NOT NULL 
                AND i.stock > 0
                AND DATEDIFF(i.expiration_date, CURDATE()) <= 60
            GROUP BY p.prod_code, i.batch_number
            ORDER BY i.expiration_date ASC
        ", [$owner_id]));

        // FIFO Simulation: Sequential selling with proper continuation tracking
        $expired = $expired->groupBy('prod_code')->flatMap(function($batches) {
            $dailyRate = $batches->first()->best_daily_rate ?? 0;
            
            // Sort batches by expiration date (FIFO order)
            $batches = $batches->sortBy('date')->values();
            
            $cumulativeDaysToStart = 0; // When this batch will START selling
            
            return $batches->map(function($batch, $index) use ($batches, $dailyRate, &$cumulativeDaysToStart) {
                $daysUntilExpiry = $batch->days_until_expiry;
                
                if ($daysUntilExpiry <= 0) {
                    $batch->expected_sales = 0;
                    $batch->sales_rate_pct = 0;
                    $batch->effective_days_left = 0;
                    $batch->days_until_selling_starts = 0;
                    $batch->days_to_deplete = 0;
                    $batch->batch_sequence = "Expired";
                    $batch->risk_category = 'Expired';
                    $batch->insight = 'Expired - Remove from display and update stock records.';
                    return $batch;
                }
                
                // Calculate when this batch will START selling (after earlier batches)
                $daysUntilSellingStarts = $cumulativeDaysToStart;
                
                // Calculate effective selling window for this batch
                $effectiveDaysLeft = max(0, $daysUntilExpiry - $daysUntilSellingStarts);
                
                if ($effectiveDaysLeft <= 0) {
                    // This batch will expire before it can be sold
                    $batch->expected_sales = 0;
                    $batch->sales_rate_pct = 0;
                    $batch->effective_days_left = 0;
                    $batch->days_until_selling_starts = $daysUntilSellingStarts;
                    $batch->days_to_deplete = 0;
                    $batch->batch_sequence = $index == 0 ? "Selling now" : "Will expire before earlier batches clear";
                    $batch->risk_category = 'High risk of waste!';
                    $batch->insight = $this->generateFIFOInsight($batch);
                    return $batch;
                }
                
                // Calculate expected sales during the effective window
                $expectedDemand = $dailyRate * $effectiveDaysLeft;
                $expectedSales = min($expectedDemand, $batch->expired_stock);
                
                $salesRate = $batch->expired_stock > 0 
                    ? ($expectedSales / $batch->expired_stock) * 100 
                    : 0;
                
                // Calculate how many days it will take to deplete THIS batch
                $daysToDeplete = 0;
                if ($dailyRate > 0 && $expectedSales > 0) {
                    $daysToDeplete = ceil($expectedSales / $dailyRate);
                }
                
                // Update cumulative days for next batch
                if ($dailyRate > 0 && $batch->expired_stock > 0) {
                    $cumulativeDaysToStart += ceil($batch->expired_stock / $dailyRate);
                }
                
                // Determine risk based on perishability
                $isPerishable = $batch->is_highly_perishable ?? 0;
                if ($dailyRate == 0) {
                    $risk = 'No Sales Data';
                } elseif ($isPerishable) {
                    if ($salesRate >= 85) $risk = 'Likely to sell';
                    elseif ($salesRate >= 50) $risk = 'May not sell all stock';
                    else $risk = 'High risk of waste!';
                } else {
                    if ($salesRate >= 80) $risk = 'Likely to sell';
                    elseif ($salesRate >= 40) $risk = 'May not sell all stock';
                    else $risk = 'High risk of waste!';
                }
                
                // Determine batch sequence message
                if ($index == 0) {
                    $sequence = "Selling now";
                } else {
                    $sequence = "After earlier batch(es)";
                }
                
                // Add calculated fields to batch
                $batch->expected_sales = round($expectedSales, 2);
                $batch->unsold_stock = round($batch->expired_stock - $expectedSales, 2);
                $batch->sales_rate_pct = round($salesRate, 1);
                $batch->risk_category = $risk;
                $batch->effective_days_left = $effectiveDaysLeft;
                $batch->days_until_selling_starts = round($daysUntilSellingStarts, 1);
                $batch->days_to_deplete = round($daysToDeplete, 1);
                $batch->batch_sequence = $sequence;
                $batch->batch_position = $index + 1;
                
                return $batch;
            });
        });

        // Apply insights
        $expired = $expired->map(function($item) {
            $item->insight = $this->generateFIFOInsight($item);
            return $item;
        });

        // Handle expired items
        DB::insert("
            INSERT INTO damaged_items (inven_code, damaged_quantity, damaged_type, damaged_reason, damaged_date, owner_id)
                SELECT inven_code, stock, 'Expired', 'System noticed that the batch has been expired.', NOW(), ?
                FROM inventory
                WHERE expiration_date <= CURDATE()
                AND stock > 0
                AND is_expired IS NULL
                AND owner_id = ?
        ", [$owner_id, $owner_id]);

        DB::statement("
            UPDATE inventory
            SET is_expired = 1
            and stock = 0
            WHERE expiration_date <= CURDATE() AND stock > 0
        ");
        
        // Apply filters
        if ($this->selectedRange !== null && $this->selectedRange !== '') {
            $range = (int) $this->selectedRange;
            if ($range === 0) {
                $expired = $expired->filter(fn($item) => $item->days_until_expiry <= 0);
            } else {
                $expired = $expired->filter(fn($item) => $item->days_until_expiry > 0 && $item->days_until_expiry <= $range);
            }
        }

        if (!empty($this->selectedCategory) && $this->selectedCategory !== 'all') {
            $expired = $expired->where('category_id', (int) $this->selectedCategory);
        }

        $this->expiredProd = $expired->values();
    }

    private function generateFIFOInsight($item) {
        $daysLeft = $item->effective_days_left ?? $item->days_until_expiry;
        $risk = $item->risk_category;
        $hasSeasonalData = $item->has_seasonal_data ?? 0;
        $salesRate = $item->sales_rate_pct ?? 0;
        $sequence = $item->batch_sequence ?? '';
        $daysUntilStart = $item->days_until_selling_starts ?? 0;
        $daysToDeplete = $item->days_to_deplete ?? 0;
        $position = $item->batch_position ?? 1;
        
        if ($daysLeft <= 0 || $risk === 'Expired') {
            return 'Expired - Remove from display and update stock records.';
        }
        
        if ($risk === 'No Sales Data') {
            if ($position == 1) {
                return "No sales history - First batch to sell.";
            }
            return "No sales history - starts selling in ~{$daysUntilStart} days. {$daysLeft} days left after that.";
        }
        
        $context = $hasSeasonalData == 1 ? ' (seasonal forecast)' : ' (current pace)';
        
        // First batch (currently selling)
        if ($position == 1) {
            if ($risk === 'Likely to sell') {
                return "Selling now{$context}. Expected to deplete in ~{$daysToDeplete} days.";
            }
            if ($risk === 'May not sell all stock') {
                $unsold = $item->unsold_stock ?? 0;
                return "Selling now{$context}. May have units unsold. Consider promotion.";
            }
            if ($risk === 'High risk of waste!') {
                if ($daysLeft <= 0) {
                    return "Will expire before it can be sold. Urgent: discount heavily or donate!";
                }
                $unsold = $item->unsold_stock ?? 0;
                return "Selling now but very slow{$context}. High risk. Urgent discount needed!";
            }
        }
        
        // Subsequent batches (waiting for earlier batches)
        if ($position > 1) {
            if ($risk === 'Likely to sell') {
                return "Starts selling in ~{$daysUntilStart} days (after earlier batch). Expected to deplete in ~{$daysToDeplete} days.";
            }
            if ($risk === 'May not sell all stock') {
                $unsold = $item->unsold_stock ?? 0;
                return "Starts selling in ~{$daysUntilStart} days. May have ~{$unsold} units unsold. Monitor closely.";
            }
            if ($risk === 'High risk of waste!') {
                if ($daysLeft <= 0) {
                    return "Will expire before earlier batches clear (~{$daysUntilStart} days). Urgent: Sell immediately or donate!";
                }
                $unsold = $item->unsold_stock ?? 0;
                return "Starts in ~{$daysUntilStart} days but may expire before selling everything. High risk. Urgent action!";
            }
        }
        
        return 'Monitor stock levels regularly.';
    }




    public function showAll() {
        if (is_null($this->selectedMonths) && is_null($this->selectedYears)) {
            
            $this->selectedMonths = now()->format('m');
            $this->selectedYears = now()->format('Y');
            $this->selectedLossType = null;
        } else {
            
            $this->selectedMonths = null;
            $this->selectedYears = null;
            $this->selectedLossType = null;
        }

        $this->loss(); 
    }

    public function updatedSelectedMonths() {
        $this->loss();
    }

    public function updatedSelectedYears() {
        $this->loss();
    }

    public function updatedSelectedLossType() {
        $this->loss();
    }

    public function loss() {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        $whereClause = "WHERE p.owner_id = ? AND (set_to_return_to_supplier IS NULL OR set_to_return_to_supplier = ?)";
        $bindings = [$owner_id, 'Damaged'];

        if (!is_null($this->selectedMonths)) {
            $whereClause .= " AND MONTH(di.damaged_date) = ?";
            $bindings[] = $this->selectedMonths;
        }

        if (!is_null($this->selectedYears)) {
            $whereClause .= " AND YEAR(di.damaged_date) = ?";
            $bindings[] = $this->selectedYears;
        }

        if (!empty($this->selectedLossType)) {
            $whereClause .= " AND di.damaged_type = ?";
            $bindings[] = $this->selectedLossType;
        }

        $this->lossRep = collect(DB::select("
            SELECT 
                di.damaged_id,
                di.damaged_date AS date_reported, 
                di.damaged_type AS type, 
                di.damaged_quantity AS qty,
                di.damaged_reason AS remarks,
                p.name AS prod_name, 
                c.category AS cat_name,
                p.selling_price AS unit_cost,
                (p.selling_price * di.damaged_quantity) AS total_loss,
                CASE 
                    WHEN s.staff_id IS NOT NULL 
                    THEN s.firstname 
                    ELSE o.firstname
                END AS reported_by,
                (SELECT i.batch_number FROM inventory i WHERE i.inven_code = di.inven_code) AS batch_num
            FROM damaged_items di
            join inventory i on i.inven_code = di.inven_code
            JOIN products p ON p.prod_code = i.prod_code
            JOIN categories c ON c.category_id = p.category_id
            
            LEFT JOIN owners o ON o.owner_id = di.owner_id
            LEFT JOIN staff s ON s.staff_id = di.staff_id
            {$whereClause}
            ORDER BY di.damaged_date DESC
        ", $bindings));
    }

    public function exportLossReport() {

        if (!$this->lossRep || $this->lossRep->isEmpty()) {
            session()->flash('error', 'No stock data to export');
            return;
        }


        $exportData = $this->lossRep->map(function ($row) {
            
            return [
                'Date Reported'     => $row->date_reported,
                'Batch #'           => $row->batch_num,
                'Product Name'      => $row->prod_name,
                'Category'          => $row->cat_name,
                'Loss Type'         => $row->type,
                'Quantity Lost'     => $row->qty,
                'Unit Cost'         => ($row->unit_cost ?? 0),
                'Total Loss'        => ($row->total_loss ?? 0),
                'Reported By'       => $row->reported_by,
                'Remarks'           => $row->remarks,
            ];
        });

        $filename = 'Loss_Report_' . now()->format('Ymd_His') . '.xlsx';
        $filePath = storage_path('app/' . $filename);

        $qtyLost = $this->lossRep->sum('qty');
        $totalLoss    = $this->lossRep->sum('total_loss');
        $totalIncident = $this->lossRep->count();

        $totalsRow = [
            'Date Reported'     => 'TOTAL LOSS SUMMARY',
            'Batch #'           => '',
            'Product Name'          => '',
            'Category'          => '',
            'Type'              => '',
            'Quantity Loss'     => $qtyLost . ' units',
            'Unit Cost'         => '',
            'Total Loss'        => '₱' . $totalLoss,
            'Reported By'       => '',
            'Remarks'           => $totalIncident . ' incedent(s) reported',
        ];


        $exportData->push($totalsRow);

        
        $filename = 'Loss_Report_' . now()->format('Ymd_His') . '.xlsx';
        $filePath = storage_path('app/' . $filename);

        $writer = new Writer();
        $writer->openToFile($filePath);

        
        $headerStyle = (new Style())
            ->setFontBold()
            ->setFontSize(12)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::rgb(76, 175, 80))
            ->setCellAlignment(CellAlignment::CENTER);

        $dateStyle = (new Style())
            ->setFontBold()
            ->setFontSize(10)
            ->setFontColor(Color::rgb(100, 100, 100));

        $totalStyle = (new Style())
            ->setFontBold()
            ->setBackgroundColor(Color::rgb(230, 230, 230));


        $sheet = $writer->getCurrentSheet();
        $sheet->setColumnWidth(18, 1); // Date Reported
        $sheet->setColumnWidth(12, 2); // Batch #
        $sheet->setColumnWidth(22, 3); // Product Name
        $sheet->setColumnWidth(18, 4); // Category
        $sheet->setColumnWidth(15, 5); // Loss Type
        $sheet->setColumnWidth(14, 6); // Quantity Lost
        $sheet->setColumnWidth(12, 7); // Unit Cost
        $sheet->setColumnWidth(15, 8); // Total Loss
        $sheet->setColumnWidth(18, 9); // Reported By
        $sheet->setColumnWidth(25, 10); // Remarks

        $exportDate = 'Exported on: ' . now()->format('F d, Y h:i A');
        $writer->addRow(Row::fromValues([$exportDate], $dateStyle));

        // empty row para naay space
        $writer->addRow(Row::fromValues(['']));

        
        $headers = array_keys($exportData->first());
        $writer->addRow(Row::fromValues($headers, $headerStyle));

        foreach ($exportData as $dataRow) {
            $rowStyle = null;

            if ($dataRow['Date Reported'] === 'TOTAL LOSS SUMMARY') {
                $rowStyle = (new Style())
                    ->setFontBold()
                    ->setBackgroundColor(Color::rgb(230, 230, 230));
            }

            $writer->addRow(Row::fromValues(array_values($dataRow), $rowStyle));
        }

        $writer->close();
        

        $this->showSuccess = true;
        return response()->download($filePath)->deleteFileAfterSend(true);
        
    }





    public function selectedStockStat() {
        $this->stockAlertReport();
    }

    public function stockAlertReport()
    {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        DB::connection()->getPdo()->exec("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");

        $results = collect(DB::select("
            SELECT 
                p.category_id,
                i.inven_code as inven_code,
                i.batch_number as batch_number,
                p.name AS prod_name,
                p.prod_code,
                i.date_added,
                i.stock AS usable_stock,
                i.date_added AS last_stockin,
                COALESCE(d.damaged_total, 0) AS damaged_stock,
                COALESCE(ri.sold_total, 0) AS sold_stock,
                (i.stock + COALESCE(ri.sold_total, 0) + COALESCE(d.damaged_total, 0)) AS total_stock,
                CASE 
                    WHEN (i.stock + COALESCE(ri.sold_total, 0) + COALESCE(d.damaged_total, 0)) > 0 
                    THEN ROUND((COALESCE(d.damaged_total, 0) / NULLIF((i.stock + COALESCE(ri.sold_total, 0) + COALESCE(d.damaged_total, 0)), 0)) * 100, 2)
                    ELSE 0 
                END AS damaged_rate_percent,
                CASE 
                    WHEN (i.stock + COALESCE(ri.sold_total, 0) + COALESCE(d.damaged_total, 0)) > 0 
                    THEN ROUND((COALESCE(ri.sold_total, 0) / NULLIF((i.stock + COALESCE(ri.sold_total, 0) + COALESCE(d.damaged_total, 0)), 0)) * 100, 2)
                    ELSE 0 
                END AS sales_rate_percent,
                CASE 
                    WHEN COALESCE(ri.sold_total, 0) > 0 
                    THEN ROUND(COALESCE(d.damaged_total, 0) / COALESCE(ri.sold_total, 0), 2)
                    WHEN COALESCE(d.damaged_total, 0) > 0 THEN 9999
                    ELSE 0 
                END AS wastage_ratio
            FROM inventory i
            JOIN products p ON i.prod_code = p.prod_code
            LEFT JOIN (
                SELECT d.inven_code, SUM(d.damaged_quantity) AS damaged_total
                FROM damaged_items d GROUP BY d.inven_code
            ) d ON i.inven_code = d.inven_code
            LEFT JOIN (
                SELECT ri.inven_code, SUM(ri.item_quantity) AS sold_total
                FROM receipt_item ri
                JOIN receipt r ON r.receipt_id = ri.receipt_id
                GROUP BY ri.inven_code
            ) ri ON i.inven_code = ri.inven_code
            WHERE p.owner_id = ?
            AND (i.is_expired = 0 OR i.is_expired IS NULL)
            AND p.prod_status = 'active'
            GROUP BY i.inven_code
            order by p.name asc
        ", [$owner_id]));

        $this->stock = collect($results)->map(function ($item) {
            $damageRate = $item->damaged_rate_percent;
            $salesRate = $item->sales_rate_percent;
            $damagedStock = $item->damaged_stock;
            $soldStock = $item->sold_stock;
            $usableStock = $item->usable_stock;
            $totalStock = $usableStock + $soldStock + $damagedStock;
            
            // Calculate days since added
            $dateAdded = \Carbon\Carbon::parse($item->date_added);
            $daysInStock = max(1, $dateAdded->diffInDays(now()));
            
            // Calculate actual daily rates
            $dailySalesRate = $soldStock / $daysInStock;
            $dailyDamageRate = $damagedStock / $daysInStock;
            
            // Calculate what percentage of initial stock remains
            $stockRemaining = $totalStock > 0 ? ($usableStock / $totalStock) * 100 : 0;
            
            // Calculate turnover efficiency
            $turnoverEfficiency = ($soldStock + $damagedStock) > 0 ? 
                (($soldStock / ($soldStock + $damagedStock)) * 100) : 0;
            
            // CRITICAL ISSUES FIRST
            
            // 1. Complete stagnation - nothing moving
            if ($soldStock == 0 && $damagedStock == 0 && $daysInStock > 30) {
                $item->insight = "Dead stock with zero movement. Consider immediate clearance or removal.";
                $item->insight_color = "bg-gray-800 text-white";
            }
            // 2. Expired/very old with remaining stock
            elseif ($daysInStock > 60 && $stockRemaining > 50) {
                $item->insight = "Severely aged item with most stock still remaining. Promote this product.";
                $item->insight_color = "bg-gray-800 text-white";
            }
            // 3. Damage exceeds sales
            elseif ($damagedStock > $soldStock && $damagedStock > 0) {
                $item->insight = "Critical loss issue where damaged units exceed sold units. Investigate storage and handling immediately.";
                $item->insight_color = "bg-red-600 text-white";
            }
            // 4. Very high damage rate
            elseif ($damageRate > 15) {
                $item->insight = "Urgent attention needed due to extremely high damage rate. Check expiry dates and storage conditions.";
                $item->insight_color = "bg-red-600 text-white";
            }
            // 5. No sales but accumulating damage
            elseif ($soldStock == 0 && $damagedStock > 0 && $daysInStock > 7) {
                $item->insight = "Zero sales but accumulating damaged stock. Pure waste occurring with no revenue generation.";
                $item->insight_color = "bg-red-600 text-white";
            }
            
            // HIGH CONCERN ISSUES
            
            // 6. High damage rate
            elseif ($damageRate >= 10 && $damageRate <= 15) {
                $item->insight = "High damage rate detected. Check refrigeration systems and expiry date management.";
                $item->insight_color = "bg-orange-500 text-white";
            }
            // 7. Very slow sales with aging
            elseif ($salesRate < 15 && $daysInStock > 30 && $stockRemaining > 70) {
                $item->insight = "Stagnant inventory with very slow sales and most stock still remaining. Consider promotional pricing.";
                $item->insight_color = "bg-orange-500 text-white";
            }
            // 8. Poor turnover efficiency
            elseif ($turnoverEfficiency > 0 && $turnoverEfficiency < 50 && $daysInStock > 14) {
                $item->insight = "Inefficient turnover where more stock is going to damage than to sales. Review handling procedures.";
                $item->insight_color = "bg-orange-500 text-white";
            }
            
            // FAST-MOVING ITEMS ANALYSIS
            elseif ($dailySalesRate >= 3 && $damageRate < 3) {
                $item->insight = "Fast-moving item with excellent daily velocity. Ensure consistent stock availability to meet demand.";
                $item->insight_color = "bg-green-600 text-white";
            }
            elseif ($dailySalesRate >= 1 && $damageRate < 5) {
                $item->insight = "Good daily sales velocity with minimal losses. Reliable seller performing well.";
                $item->insight_color = "bg-blue-500 text-white";
            }
            
            // MODERATE CONCERNS
            
            // 9. Moderate damage with slow movement
            elseif ($damageRate >= 5 && $damageRate < 10 && $salesRate < 30) {
                $item->insight = "Moderate damage combined with slow sales movement. Monitor closely for deterioration.";
                $item->insight_color = "bg-yellow-500 text-gray-900";
            }
            // 10. Slow movement overall
            elseif ($salesRate < 25 && $daysInStock > 21) {
                $item->insight = "Slow turnover with low sales velocity. Consider adjusting order quantities or marketing approach.";
                $item->insight_color = "bg-yellow-500 text-gray-900";
            }
            // 11. Moderate damage but good sales
            elseif ($damageRate >= 5 && $damageRate < 10 && $salesRate >= 40) {
                $item->insight = "Good sales performance but damage rate is higher than optimal. Improve handling to reduce preventable losses.";
                $item->insight_color = "bg-yellow-500 text-gray-900";
            }
            
            // POSITIVE PERFORMANCE
            
            // 12. High sales velocity, minimal damage
            elseif ($salesRate >= 70 && $damageRate < 3) {
                $item->insight = "Excellent performance with high sales turnover and minimal losses. Top performer in current inventory.";
                $item->insight_color = "bg-green-600 text-white";
            }
            // 13. Good sales velocity
            elseif ($salesRate >= 50 && $damageRate < 5) {
                $item->insight = "Strong sales turnover with low damage rate. Healthy inventory flow and profitability.";
                $item->insight_color = "bg-green-600 text-white";
            }
            // 14. Moderate but healthy performance
            elseif ($salesRate >= 35 && $damageRate < 3) {
                $item->insight = "Healthy performance with good sales movement and minimal damage. Stable and profitable item.";
                $item->insight_color = "bg-blue-500 text-white";
            }
            
            // NEW STOCK ANALYSIS
            
            // 15. Very new stock with NO activity
            elseif ($daysInStock <= 2 && $soldStock == 0 && $damagedStock == 0) {
                $item->insight = "Recently added to inventory with no activity yet. Currently monitoring initial performance.";
                $item->insight_color = "bg-gray-500 text-white";
            }
            // 16. New with fast sales
            elseif ($daysInStock <= 3 && $dailySalesRate >= 2 && $damageRate < 2) {
                $item->insight = "Excellent early performance with fast daily sales rate. Strong initial customer demand detected.";
                $item->insight_color = "bg-green-600 text-white";
            }
            // 17. New with good early activity
            elseif ($daysInStock <= 7 && $salesRate >= 20 && $damageRate < 3) {
                $item->insight = "Good start with positive early sales activity and low damage. Promising new addition to inventory.";
                $item->insight_color = "bg-blue-500 text-white";
            }
            // 18. New but with early damage concerns
            elseif ($daysInStock <= 5 && $damageRate >= 5) {
                $item->insight = "Early warning signs with damage occurring soon after stocking. Check handling procedures and expiry dates.";
                $item->insight_color = "bg-yellow-500 text-gray-900";
            }
            // 19. New with slow start
            elseif ($daysInStock <= 5 && $salesRate < 10) {
                $item->insight = "Slow initial movement detected. Early monitoring needed to assess customer interest and positioning.";
                $item->insight_color = "bg-yellow-500 text-gray-900";
            }
            
            // DEFAULT: STABLE/MODERATE
            else {
                $item->insight = "Stable performance with normal sales flow and acceptable damage levels. Standard monitoring applies.";
                $item->insight_color = "bg-gray-500 text-white";
            }
            
            return $item;
        });

        if (!empty($this->selectedCategory) && $this->selectedCategory !== 'all') {
            $results = $results->where('category_id', (int) $this->selectedCategory);
        }

        if (!empty($this->selectedStockStatus) && $this->selectedStockStatus !== 'all') {
            if ($this->selectedStockStatus === 'active') {
                $results = $results->filter(fn($row) => $row->usable_stock > 0);
            } elseif ($this->selectedStockStatus === 'depleted') {
                $results = $results->filter(fn($row) => $row->usable_stock == 0);
            }
        }


        $this->stock = $results->values();
    }

    public function exportStockReport() {

        if (!$this->stock || $this->stock->isEmpty()) {
            session()->flash('error', 'No stock data to export');
            return;
        }

        $exportData = $this->stock->map(function ($row) {
            $initialStock = ($row->usable_stock ?? 0) + ($row->sold_stock ?? 0) + ($row->damaged_stock ?? 0);
            return [
                'Product Name'  => $row->prod_name,
                'Batch #'       => $row->batch_number,
                'Initial Stock' => $initialStock,
                'Current'       => $row->usable_stock,
                'Sold'          => $row->sold_stock,
                'Damaged'       => $row->damaged_stock,
                'Sales Rate'    => ($row->sales_rate_percent ?? 0) . '%',
                'Damage Rate'   => ($row->damaged_rate_percent ?? 0) . '%',
                'Insights'      => $row->insight,
            ];
        });

        $filename = 'Stock_Report_' . now()->format('Ymd_His') . '.xlsx';
        $filePath = storage_path('app/' . $filename);

        $totalInitial = $this->stock->sum(fn($row) => ($row->usable_stock ?? 0) + ($row->sold_stock ?? 0) + ($row->damaged_stock ?? 0));
        $totalCurrent = $this->stock->sum('usable_stock');
        $totalSold    = $this->stock->sum('sold_stock');
        $totalDamaged = $this->stock->sum('damaged_stock');

        $totalsRow = [
            'Product Name'  => 'TOTAL',
            'Batch #'       => '',
            'Initial Stock' => $totalInitial,
            'Current'       => $totalCurrent,
            'Sold'          => $totalSold,
            'Damaged'       => $totalDamaged,
            'Sales Rate'    => '',
            'Damage Rate'   => '',
        ];


        $exportData->push($totalsRow);

        
        $filename = 'Stock_Report_' . now()->format('Ymd_His') . '.xlsx';
        $filePath = storage_path('app/' . $filename);

        $writer = new Writer();
        $writer->openToFile($filePath);

        
        $headerStyle = (new Style())
            ->setFontBold()
            ->setFontSize(12)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::rgb(76, 175, 80))
            ->setCellAlignment(CellAlignment::CENTER);

        $dateStyle = (new Style())
            ->setFontBold()
            ->setFontSize(10)
            ->setFontColor(Color::rgb(100, 100, 100));

        $totalStyle = (new Style())
            ->setFontBold()
            ->setBackgroundColor(Color::rgb(230, 230, 230));


            
        $maxNameLength = $this->stock->max(fn($row) => strlen($row->prod_name)) ?? 15;
        $maxBatchLength = $this->stock->max(fn($row) => strlen($row->batch_number)) ?? 10;
        $maxInsightLength = $this->stock->max(fn($row) => strlen($row->insight)) ?? 25;

        $sheet = $writer->getCurrentSheet();
        $sheet->setColumnWidth(max($maxNameLength, 20), 1); // Product Name
        $sheet->setColumnWidth(max($maxBatchLength, 12), 2); // Batch #
        $sheet->setColumnWidth(15, 3); // Initial Stock
        $sheet->setColumnWidth(12, 4); // Current
        $sheet->setColumnWidth(12, 5); // Sold
        $sheet->setColumnWidth(12, 6); // Damaged
        $sheet->setColumnWidth(15, 7); // Sales Rate
        $sheet->setColumnWidth(15, 8); // Damage Rate
        $sheet->setColumnWidth(max($maxInsightLength, 12), 9); // Insight

        $exportDate = 'Exported on: ' . now()->format('F d, Y h:i A');
        $writer->addRow(Row::fromValues([$exportDate], $dateStyle));

        // empty row para naay space
        $writer->addRow(Row::fromValues(['']));

        
        $headers = array_keys($exportData->first());
        $writer->addRow(Row::fromValues($headers, $headerStyle));

        foreach ($exportData as $dataRow) {
            $rowStyle = null;

            if ($dataRow['Product Name'] === 'TOTAL') {
                $rowStyle = (new Style())
                    ->setFontBold()
                    ->setBackgroundColor(Color::rgb(230, 230, 230));
            }

            $writer->addRow(Row::fromValues(array_values($dataRow), $rowStyle));
        }

        $writer->close();
        

        $this->showSuccess = true;
        return response()->download($filePath)->deleteFileAfterSend(true);
        
    }




    public function damagedReport() {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        $whereClause = "WHERE p.owner_id = ? and set_to_return_to_supplier is not null";
        $bindings = [$owner_id];

        if (!is_null($this->selectedMonths)) {
            $whereClause .= " AND MONTH(di.damaged_date) = ?";
            $bindings[] = $this->selectedMonths;
        }

        if (!is_null($this->selectedYears)) {
            $whereClause .= " AND YEAR(di.damaged_date) = ?";
            $bindings[] = $this->selectedYears;
        }

        if (!empty($this->selectedLossType)) {
            $whereClause .= " AND di.damaged_type = ?";
            $bindings[] = $this->selectedLossType;
        }

        $this->damagedRep = collect(DB::select("
            SELECT 
                di.damaged_id,
                di.damaged_date AS date_reported, 
                di.damaged_type AS type, 
                di.damaged_quantity AS qty,
                di.damaged_reason AS remarks,
                p.name AS prod_name, 
                c.category AS cat_name,
                p.selling_price AS unit_cost,
                di.set_to_return_to_supplier as status,
                (p.selling_price * di.damaged_quantity) AS total_loss,
                CASE 
                    WHEN s.staff_id IS NOT NULL 
                    THEN s.firstname 
                    ELSE o.firstname
                END AS reported_by,
                (SELECT i.batch_number FROM inventory i WHERE i.inven_code = di.inven_code) AS batch_num
            FROM damaged_items di
            join inventory i on i.inven_code = di.inven_code
            JOIN products p ON p.prod_code = i.prod_code
            JOIN categories c ON c.category_id = p.category_id
            
            LEFT JOIN owners o ON o.owner_id = di.owner_id
            LEFT JOIN staff s ON s.staff_id = di.staff_id
            {$whereClause}
            ORDER BY di.damaged_date DESC
        ", $bindings));
    }

    public function updateStatus($id, $value)
    {
        if ($value == '') {
            $value = null;

        } elseif ($value == 'Completed') {

            $qty = collect(DB::select("
                SELECT damaged_quantity, inven_code
                FROM damaged_items
                WHERE damaged_id = ?
            ", [$id]))->first();

            $this->damagedQuantitySummary = $qty->damaged_quantity;

            DB::update("
                UPDATE inventory
                SET stock = stock + ?
                WHERE inven_code = ?
            ", [
                (int)$qty->damaged_quantity,
                $qty->inven_code
            ]);

            DB::update("
                UPDATE damaged_items
                SET damaged_quantity = 0
                WHERE damaged_id = ?
            ", [$id]);

            $this->showReasonModal = true;

        } elseif ($value == 'Damaged') {
            
            $this->showReasonModal = true;
        }

        DB::update("
            update damaged_items
            set set_to_return_to_supplier = ?
            where damaged_id = ?
        ", [$value, $id]);

        $this->currentDamagedId = $id;

        $this->dispatch('row-updated', rowId: $id);
        session()->flash('success', 'Status updated successfully.');
        
        $this->skipRender(); 
    }

    public function submitReason()
    {
        // Safely fetch old reason
        $old = collect(DB::select("
            SELECT damaged_reason 
            FROM damaged_items
            WHERE damaged_id = ?
        ", [$this->currentDamagedId]))->first();

        $oldReason = $old->damaged_reason ?? '';

        // NEW REASON BLOCK
        $newPart = trim($this->damagedReason);

        // FIX HERE ↓↓  use damaged_quantity instead of damagedQuantitySummary
        $quantitySummary = "Reported quantity: {$this->damagedQuantitySummary}
                            Items were successfully returned and replaced.";

        // Prepend new reason
        if ($oldReason !== '') {
            $finalReason = "‼️{$newPart}\n{$quantitySummary}\n\n{$oldReason}";
        } else {
            $finalReason = "‼️{$newPart}\n\n{$quantitySummary}";
        }

        DB::update("
            UPDATE damaged_items
            SET damaged_reason = ?
            WHERE damaged_id = ?
        ", [
            $finalReason,
            $this->currentDamagedId
        ]);

        // reset
        $this->damagedReason = '';
        $this->showReasonModal = false;
    }



    public function closeReasonModal() {
        $this->showReasonModal = false;
    }


    public function render()
    {
        $this->stockAlertReport();
        $this->loss();
        $this->expired();
        $this->damagedReport();

        return view('livewire.report-inventory');
    }
}
