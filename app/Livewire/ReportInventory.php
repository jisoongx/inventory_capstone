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

    public $stock;
    public $selectedStockStatus = 'active';

    public $showSuccess = false;


    public function mount() {

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
        
        $expired = collect(DB::select("
            SELECT 
                COALESCE(i.batch_number, 'Initial Batch') AS batch_num,
                i.inven_code as inven_code,
                p.prod_code AS prod_code,
                p.name AS prod_name,
                i.stock AS expired_stock,
                i.expiration_date AS date,
                c.category AS cat_name,
                c.category_id,
                p.selling_price AS cost,
                DATEDIFF(i.expiration_date, CURDATE()) AS days_until_expiry,
                SUM(p.selling_price * i.stock) AS total_loss,
                
                -- Sales speed (last 30 days)
                COALESCE(SUM(CASE 
                    WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                    THEN ri.item_quantity ELSE 0 
                END) / 30.0, 0) AS avg_daily_sales,
                
                -- Days needed to sell current stock
                CASE 
                    WHEN COALESCE(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0, 0) = 0 THEN NULL
                    ELSE CEIL(i.stock / (SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0))
                END AS days_to_sellout,
                
                -- Will it sell before expiry?
                CASE 
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 0 THEN 
                        'Already expired.'
                    WHEN COALESCE(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0, 0) = 0 THEN 
                        'No sales, unlikely to sell.'
                    WHEN CEIL(i.stock / (SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0)) <= DATEDIFF(i.expiration_date, CURDATE()) THEN 
                        'Will likely sell out before expiry.'
                    ELSE 
                        'At risk of expiring with unsold stock.'
                END AS will_sell_before_expiry,
                

                CASE 
                    -- Already expired
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 0 THEN 
                        'Expired, remove from display and update stock records.'
                    
                        
                    WHEN COALESCE(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END), 0) = 0 AND DATEDIFF(i.expiration_date, CURDATE()) <= 21 THEN 
                        'Critical! No sales in 30 days, might apply big discount.'

                    WHEN COALESCE(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END), 0) = 0 
                    AND i.stock <= 3  
                    AND DATEDIFF(i.expiration_date, CURDATE()) <= 21 THEN 
                    'Critical! No sales in 30 days, apply a discount while promoting it.'
                    
                    WHEN COALESCE(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END), 0) = 0 THEN 
                        'Warning: No sales in 30 days, reposition or start discounting.'
                    
                        
                    
                    WHEN CEIL(i.stock / NULLIF(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0, 0)) > DATEDIFF(i.expiration_date, CURDATE()) 
                    AND DATEDIFF(i.expiration_date, CURDATE()) <= 7 THEN 
                        'Urgent! Selling too slow, apply 40-50% discount if possible.'
                    
                    WHEN CEIL(i.stock / NULLIF(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0, 0)) > DATEDIFF(i.expiration_date, CURDATE()) 
                    AND DATEDIFF(i.expiration_date, CURDATE()) <= 14 THEN 
                        'Action needed! Stock will not clear in time, offer a small discount.'
                    
                    WHEN CEIL(i.stock / NULLIF(SUM(CASE 
                        WHEN r.receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                        THEN ri.item_quantity ELSE 0 
                    END) / 30.0, 0)) > DATEDIFF(i.expiration_date, CURDATE()) 
                    AND DATEDIFF(i.expiration_date, CURDATE()) <= 21 THEN 
                        'Sales pace too slow, start promoting now to avoid wastage.'
                    

                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 7 THEN 
                        'One week left, apply discount or bundle to clear stock.'
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 14 THEN 
                        'Two weeks left, check sales pace and consider promotion.'
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 21 THEN 
                        'Three weeks left, monitor closely and plan promotion if needed.'
                    WHEN DATEDIFF(i.expiration_date, CURDATE()) <= 30 THEN 
                        'One month left, review sales and adjust restock plans.'
                    ELSE 
                        'Monitor stock levels regularly.'
                END AS insight

            FROM inventory i
            JOIN products p ON i.prod_code = p.prod_code
            JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN receipt_item ri ON ri.prod_code = p.prod_code
            LEFT JOIN receipt r ON ri.receipt_id = r.receipt_id
            WHERE p.owner_id = ?
                AND i.expiration_date IS NOT NULL 
                AND i.stock > 0
                AND DATEDIFF(i.expiration_date, CURDATE()) <= 60
            GROUP BY p.prod_code, i.batch_number
            ORDER BY days_until_expiry ASC;
        ", [$owner_id]));

        DB::insert("
            INSERT INTO damaged_items (inven_code, damaged_quantity, damaged_type, damaged_reason, damaged_date, owner_id)
                SELECT inven_code, stock, 'Expired', 'System noticed that the batch has been expired.', NOW(), ?
                FROM inventory
                WHERE expiration_date <= CURDATE()
                AND stock > 0
                AND is_expired is null
        ", [$owner_id]);

        DB::statement("
            UPDATE inventory
            SET is_expired = 1
            WHERE expiration_date <= CURDATE() AND stock > 0
        ");
        
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

        $whereClause = "WHERE p.owner_id = ?";
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
            $daysInStock = max(1, $dateAdded->diffInDays(now())); // Avoid division by zero
            
            // Calculate actual daily rates (more accurate than percentages alone)
            $dailySalesRate = $soldStock / $daysInStock;
            $dailyDamageRate = $damagedStock / $daysInStock;
            
            // Calculate what percentage of initial stock remains
            $stockRemaining = $totalStock > 0 ? ($usableStock / $totalStock) * 100 : 0;
            
            // Calculate turnover efficiency: how much went to sales vs damage
            $turnoverEfficiency = ($soldStock + $damagedStock) > 0 ? 
                (($soldStock / ($soldStock + $damagedStock)) * 100) : 0;

            // CRITICAL ISSUES FIRST - SHORTER TIMEFRAMES FOR FAST-MOVING GOODS
            
            // 1. Complete stagnation - nothing moving (30 days for convenience stores)
            if ($soldStock == 0 && $damagedStock == 0 && $daysInStock > 30) {
                $item->insight = "Dead stock - " . number_format($daysInStock, 1) . " days, zero movement. Consider clearance.";
                $item->insight_color = "bg-gray-800 text-white";
            }
            // 2. Expired/very old with remaining stock (60 days max for most items)
            elseif ($daysInStock > 60 && $stockRemaining > 50) {
                $item->insight = "Expired/Slow-moving - " . number_format($daysInStock, 1) . " days old, " . number_format($stockRemaining, 0) . "% still in stock.";
                $item->insight_color = "bg-gray-800 text-white";
            }
            // 3. Damage exceeds sales (biggest red flag)
            elseif ($damagedStock > $soldStock && $damagedStock > 0) {
                $item->insight = "Critical: More damaged (" . $damagedStock . ") than sold (" . $soldStock . "). Severe loss issue.";
                $item->insight_color = "bg-red-600 text-white";
            }
            // 4. Very high damage rate (>15%) - CRITICAL FOR PERISHABLES
            elseif ($damageRate > 15) {
                $item->insight = "Urgent: " . number_format($damageRate, 1) . "% damage rate (" . $damagedStock . " units lost). Check expiry/storage.";
                $item->insight_color = "bg-red-600 text-white";
            }
            // 5. No sales but accumulating damage (7 days for fast-moving retail)
            elseif ($soldStock == 0 && $damagedStock > 0 && $daysInStock > 7) {
                $item->insight = "Zero sales but " . $damagedStock . " damaged in " . number_format($daysInStock, 1) . " days. Pure waste.";
                $item->insight_color = "bg-red-600 text-white";
            }
            
            // HIGH CONCERN ISSUES
            
            // 6. High damage rate (10-15%) - COMMON WITH PERISHABLES
            elseif ($damageRate >= 10 && $damageRate <= 15) {
                $item->insight = "High damage: " . number_format($damageRate, 1) . "% loss rate (" . $damagedStock . " units). Check refrigeration/expiry.";
                $item->insight_color = "bg-orange-500 text-white";
            }
            // 7. Very slow sales with aging (30 days for convenience stores)
            elseif ($salesRate < 15 && $daysInStock > 30 && $stockRemaining > 70) {
                $item->insight = "Stagnant: Only " . number_format($salesRate, 1) . "% sold in " . number_format($daysInStock, 1) . " days, " . number_format($stockRemaining, 0) . "% remains.";
                $item->insight_color = "bg-orange-500 text-white";
            }
            // 8. Poor turnover efficiency (<50% went to sales)
            elseif ($turnoverEfficiency > 0 && $turnoverEfficiency < 50 && $daysInStock > 14) {
                $item->insight = "Inefficient: Only " . number_format($turnoverEfficiency, 0) . "% of movement went to sales vs damage.";
                $item->insight_color = "bg-orange-500 text-white";
            }
            
            // FAST-MOVING ITEMS ANALYSIS - SPECIFIC TO CONVENIENCE STORES
            elseif ($dailySalesRate >= 3 && $damageRate < 3) {
                $item->insight = "Fast mover: " . number_format($dailySalesRate, 1) . " units/day. Ensure stock availability.";
                $item->insight_color = "bg-green-600 text-white";
            }
            elseif ($dailySalesRate >= 1 && $damageRate < 5) {
                $item->insight = "Good velocity: " . number_format($dailySalesRate, 1) . " units/day. Reliable seller.";
                $item->insight_color = "bg-blue-500 text-white";
            }
            
            // MODERATE CONCERNS
            
            // 9. Moderate damage with slow movement
            elseif ($damageRate >= 5 && $damageRate < 10 && $salesRate < 30) {
                $item->insight = "Concern: " . number_format($damageRate, 1) . "% damage + slow sales (" . number_format($salesRate, 1) . "%) in " . number_format($daysInStock, 1) . " days.";
                $item->insight_color = "bg-yellow-500 text-gray-900";
            }
            // 10. Slow movement overall (21 days for retail)
            elseif ($salesRate < 25 && $daysInStock > 21) {
                $item->insight = "Slow turnover: " . number_format($salesRate, 1) . "% sold over " . number_format($daysInStock, 1) . " days. Low velocity.";
                $item->insight_color = "bg-yellow-500 text-gray-900";
            }
            // 11. Moderate damage but good sales
            elseif ($damageRate >= 5 && $damageRate < 10 && $salesRate >= 40) {
                $item->insight = "Mixed: Good sales (" . number_format($salesRate, 1) . "%) but " . number_format($damageRate, 1) . "% damage is preventable.";
                $item->insight_color = "bg-yellow-500 text-gray-900";
            }
            
            // POSITIVE PERFORMANCE
            
            // 12. High sales velocity, minimal damage
            elseif ($salesRate >= 70 && $damageRate < 3) {
                $item->insight = "Excellent: " . number_format($salesRate, 1) . "% sold, " . number_format($damageRate, 1) . "% damage in " . number_format($daysInStock, 1) . " days. Top performer.";
                $item->insight_color = "bg-green-600 text-white";
            }
            // 13. Good sales velocity
            elseif ($salesRate >= 50 && $damageRate < 5) {
                $item->insight = "Strong: " . number_format($salesRate, 1) . "% turnover, " . number_format($damageRate, 1) . "% loss. Healthy flow.";
                $item->insight_color = "bg-green-600 text-white";
            }
            // 14. Moderate but healthy performance
            elseif ($salesRate >= 35 && $damageRate < 3) {
                $item->insight = "Healthy: " . number_format($salesRate, 1) . "% sold, minimal damage (" . number_format($damageRate, 1) . "%) over " . number_format($daysInStock, 1) . " days.";
                $item->insight_color = "bg-blue-500 text-white";
            }
            
            // NEW STOCK ANALYSIS - SHORTER TIME WINDOWS
            
            // 15. Very new stock with NO activity
            elseif ($daysInStock <= 2 && $soldStock == 0 && $damagedStock == 0) {
                $item->insight = "Just added (" . number_format($daysInStock, 1) . " days). No activity yet - monitoring.";
                $item->insight_color = "bg-gray-500 text-white";
            }
            // 16. New with fast sales (very promising) - 3 DAYS for fast retail
            elseif ($daysInStock <= 3 && $dailySalesRate >= 2 && $damageRate < 2) {
                $item->insight = "Fast start: " . number_format($dailySalesRate, 1) . " units/day in " . number_format($daysInStock, 1) . " days. Strong early demand.";
                $item->insight_color = "bg-green-600 text-white";
            }
            // 17. New with good early activity - 7 DAYS window
            elseif ($daysInStock <= 7 && $salesRate >= 20 && $damageRate < 3) {
                $item->insight = "Good start: " . number_format($salesRate, 1) . "% sold, " . number_format($damageRate, 1) . "% damage in " . number_format($daysInStock, 1) . " days.";
                $item->insight_color = "bg-blue-500 text-white";
            }
            // 18. New but with early damage concerns - 5 DAYS window
            elseif ($daysInStock <= 5 && $damageRate >= 5) {
                $item->insight = "Early warning: " . number_format($damageRate, 1) . "% already damaged in " . number_format($daysInStock, 1) . " days. Check handling/expiry.";
                $item->insight_color = "bg-yellow-500 text-gray-900";
            }
            // 19. New with slow start - 5 DAYS window
            elseif ($daysInStock <= 5 && $salesRate < 10) {
                $item->insight = "Slow start: Only " . number_format($salesRate, 1) . "% moved in " . number_format($daysInStock, 1) . " days. Early monitoring needed.";
                $item->insight_color = "bg-yellow-500 text-gray-900";
            }
            
            // DEFAULT: STABLE/MODERATE
            else {
                $item->insight = "Stable: " . number_format($salesRate, 1) . "% sold, " . number_format($damageRate, 1) . "% damaged over " . number_format($daysInStock, 1) . " days. Normal flow.";
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



    public function render()
    {
        $this->stockAlertReport();
        $this->loss();
        $this->expired();

        return view('livewire.report-inventory');
    }
}
