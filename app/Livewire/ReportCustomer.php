<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Phpml\Association\Apriori;

class ReportCustomer extends Component
{
    public $month;
    public $year;
    public $years;
    public $results = [];
    public $loading = false;
    public $type = "Nothing";

    public $frequency= [];
    public $frequencySelectMonth;
    public $frequencySelectYear;

    public $thresholdInfo;

    public $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    public function mount()
    {
        if (!Auth::guard('owner')->check()) {
            abort(403, 'Unauthorized access.');
        }

        $owner_id = Auth::guard('owner')->user()->owner_id;
        $this->month = now()->month;
        $this->year = now()->year;

        $this->frequencySelectMonth = now()->month;
        $this->frequencySelectYear = now()->year;

        $this->frequencyTransac();

        $this->years = collect(DB::select("
            SELECT DISTINCT(YEAR(receipt_date)) AS year
            FROM receipt
            WHERE owner_id = ?
            ORDER BY year DESC", 
            [$owner_id]
        ))->pluck('year');
    }


public function generateReport()
{
    $owner_id = Auth::guard('owner')->user()->owner_id;
    $this->loading = true;
    $this->results = [];
    
    $transactions = DB::select("
        SELECT 
            r.receipt_id,
            GROUP_CONCAT(ri.prod_code ORDER BY ri.prod_code) AS product_codes
        FROM receipt r
        JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
        WHERE r.owner_id = ? 
        AND MONTH(r.receipt_date) = ? 
        AND YEAR(r.receipt_date) = ?
        GROUP BY r.receipt_id
    ", [$owner_id, $this->month, $this->year]);
    
    $dataset = [];
    foreach ($transactions as $t) {
        $dataset[] = explode(',', $t->product_codes);
    }
    
    if (empty($dataset)) {
        $this->results = ['message' => 'No transactions found for this period.'];
        $this->loading = false;
        return;
    }
    
    $totalTransactions = count($transactions);
    
    try {
        // Calculate thresholds purely from data distribution
        $thresholds = $this->calculateDataDrivenThresholds($dataset);
        
        $minSupport = $thresholds['minSupport'];
        $minConfidence = $thresholds['minConfidence'];
        $minSupportCount = $thresholds['minSupportCount'];
        $minLift = $thresholds['minLift'];
        
        // Store threshold info for display
        $this->thresholdInfo = $thresholds['explanation'];
        
        $associator = new Apriori($minSupport, $minConfidence);
        $associator->train($dataset, []);
        $rules = $associator->getRules();
        
        $uniquePairs = [];
        
        foreach ($rules as $rule) {
            $antecedentNames = $this->getProductNames($rule['antecedent']);
            $consequentNames = $this->getProductNames($rule['consequent']);
            
            $a = implode(', ', $antecedentNames);
            $b = implode(', ', $consequentNames);
            
            // Create normalized pair key
            $pairArray = [$a, $b];
            sort($pairArray, SORT_NATURAL | SORT_FLAG_CASE);
            $pairKey = implode(' | ', $pairArray);
            
            // Calculate metrics using standard formulas
            $supportAB = $rule['support'];
            $confidenceAB = $rule['confidence'];
            $supportCount = round($supportAB * $totalTransactions);
            
            if ($supportCount < $minSupportCount) continue;
            
            // Lift = Support(A,B) / (Support(A) × Support(B))
            $supportA = $this->calculateItemsetSupport($rule['antecedent'], $dataset);
            $supportB = $this->calculateItemsetSupport($rule['consequent'], $dataset);
            $lift = ($supportA > 0 && $supportB > 0) 
                ? round($supportAB / ($supportA * $supportB), 2) 
                : 1.00;
            
            if ($lift < $minLift) continue;
            
            if (!isset($uniquePairs[$pairKey])) {
                $uniquePairs[$pairKey] = [
                    'productA' => $pairArray[0],
                    'productB' => $pairArray[1],
                    'supportCount' => $supportCount,
                    'confidenceValues' => [$confidenceAB],
                    'liftValues' => [$lift],
                ];
            } else {
                $uniquePairs[$pairKey]['confidenceValues'][] = $confidenceAB;
                $uniquePairs[$pairKey]['liftValues'][] = $lift;
            }
        }
        
        $cleanResults = [];
        
        foreach ($uniquePairs as $pair) {
            $avgConfidence = round(array_sum($pair['confidenceValues']) / count($pair['confidenceValues']), 2);
            $avgLift = round(array_sum($pair['liftValues']) / count($pair['liftValues']), 2);
            
            if ($avgConfidence < $minConfidence || $avgLift < $minLift) continue;
            
            $cleanResults[] = [
                'productA' => $pair['productA'],
                'productB' => $pair['productB'],
                'supportCount' => "{$pair['supportCount']} / {$totalTransactions}",
                'confidenceText' => round($avgConfidence * 100) . '%',
                'lift' => "{$avgLift}x",
                'summary' => "Pattern: " . round($avgConfidence * 100) . "% of customers who buy <b>{$pair['productA']}</b> also buy <b>{$pair['productB']}</b>.<br>
                            {$this->getConfidenceInsight(round($avgConfidence * 100))} {$this->getLiftInsight($avgLift)}",
            ];
        }
        
        usort($cleanResults, fn($a, $b) =>
            intval(explode(' ', $b['supportCount'])[0]) <=> intval(explode(' ', $a['supportCount'])[0])
        );
        
        if (empty($cleanResults) || count($cleanResults) < 3) {
            $this->results = $this->runCoOccurrenceAnalysis($totalTransactions);
            $this->type = "Co-Occurrence Analysis";
            
            if (empty($this->results)) {
                $this->results = ['message' => 'No significant product associations found for the selected period.'];
                $this->type = "Nothing";
            }
        } else {
            $this->type = "Apriori Algorithm";
            $this->results = $cleanResults;
        }
        
    } catch (\Exception $e) {        
        $this->results = $this->runCoOccurrenceAnalysis($totalTransactions);
        $this->type = "Co-Occurrence Analysis";
        
        if (empty($this->results)) {
            $this->results = ['message' => 'Error generating analysis: ' . $e->getMessage()];
            $this->type = "Nothing";
        }
    }
    
    $this->loading = false;
}

/**
 * Calculate thresholds from actual data distribution - NO HARDCODED VALUES
 * 
 * Approach:
 * 1. Support: Based purely on minimum occurrence rule (3 occurrences)
 * 2. Confidence: Derived from median confidence of potential item pairs
 * 3. Lift: Always >1.0 (items must co-occur more than random chance)
 * 
 * This ensures thresholds adapt to ACTUAL patterns in the data, not arbitrary percentages
 */
    private function calculateDataDrivenThresholds($dataset)
    {
        $totalTransactions = count($dataset);
        $itemCount = [];
        $pairCount = [];

        // ======================
        // STEP 1 — COUNT ITEMS & PAIRS
        // ======================
        foreach ($dataset as $transaction) {
            $items = array_unique($transaction);

            foreach ($items as $item) {
                $itemCount[$item] = ($itemCount[$item] ?? 0) + 1;
            }

            sort($items);
            for ($i = 0; $i < count($items); $i++) {
                for ($j = $i + 1; $j < count($items); $j++) {
                    $key = $items[$i] . '||' . $items[$j];
                    $pairCount[$key] = ($pairCount[$key] ?? 0) + 1;
                }
            }
        }

        // ======================
        // STEP 2 — METRIC LISTS
        // ======================
        $supports = [];
        $confidences = [];
        $lifts = [];

        foreach ($pairCount as $pair => $count) {
            [$A,$B] = explode('||',$pair);

            $supAB = $count / $totalTransactions;
            $conf = max($count/$itemCount[$A], $count/$itemCount[$B]);

            $supA = $itemCount[$A] / $totalTransactions;
            $supB = $itemCount[$B] / $totalTransactions;

            $lift = $supAB / ($supA * $supB);

            $supports[] = $supAB;
            $confidences[] = $conf;
            $lifts[] = $lift;
        }

        sort($supports);
        sort($confidences);
        sort($lifts);

        // ======================
        // STEP 3 — Q3 THRESHOLDS
        // ======================
        $q = fn($a,$p) => $a[floor($p*(count($a)-1))];

        $minSupport    = $q($supports,0.75);
        $minConfidence = $q($confidences,0.75);
        $minLift       = $q($lifts,0.75);

        $minSupportCount = ceil($minSupport * $totalTransactions);

        return [
            'minSupport' => round($minSupport,4),
            'minConfidence' => round($minConfidence,3),
            'minLift' => round($minLift,2),
            'minSupportCount' => $minSupportCount,

            'explanation' =>
                "Thresholds derived automatically using the 75th percentile " .
                "of support, confidence and lift distributions (Dahbi et al. approach). " .
                "Only top 25% strongest rules are retained.",

            'stats' => [
                'transactions' => $totalTransactions,
                'products' => count($itemCount),
                'pairs' => count($pairCount)
            ]
        ];
    }




/**
 * Get frequency of each item in the dataset
 */
private function getItemFrequencies($dataset)
{
    $frequencies = [];
    
    foreach ($dataset as $transaction) {
        foreach ($transaction as $item) {
            $frequencies[$item] = ($frequencies[$item] ?? 0) + 1;
        }
    }
    
    return $frequencies;
}

/**
 * Calculate support for itemset: Support(A) = Count(A) / Total Transactions
 */
private function calculateItemsetSupport($itemset, $dataset)
{
    $count = 0;
    
    foreach ($dataset as $transaction) {
        $containsAll = true;
        foreach ($itemset as $item) {
            if (!in_array($item, $transaction)) {
                $containsAll = false;
                break;
            }
        }
        if ($containsAll) {
            $count++;
        }
    }
    
    return $count / count($dataset);
}

    private function runCoOccurrenceAnalysis($totalTransactions)
    {
        $owner_id = Auth::guard('owner')->user()->owner_id;
        
        $pairs = DB::select("
            SELECT 
                ri1.prod_code as product_a_code,
                ri2.prod_code as product_b_code,
                COUNT(DISTINCT ri1.receipt_id) as times_bought_together
            FROM receipt_item ri1
            JOIN receipt_item ri2 ON ri1.receipt_id = ri2.receipt_id 
                AND ri1.prod_code < ri2.prod_code
            WHERE ri1.receipt_id IN (
                SELECT receipt_id FROM receipt 
                WHERE owner_id = ? 
                AND MONTH(receipt_date) = ? 
                AND YEAR(receipt_date) = ?
            )
            GROUP BY ri1.prod_code, ri2.prod_code
            HAVING times_bought_together >= 2
            ORDER BY times_bought_together DESC
            LIMIT 15
        ", [$owner_id, $this->month, $this->year]);

        if (empty($pairs)) {
            return [];
        }

        $results = [];
        foreach ($pairs as $pair) {
            $productANames = $this->getProductNames([$pair->product_a_code]);
            $productBNames = $this->getProductNames([$pair->product_b_code]);

            $productA = implode(', ', $productANames);
            $productB = implode(', ', $productBNames);

            $percentage = round(($pair->times_bought_together / $totalTransactions) * 100);

            $results[] = [
                'productA' => $productA,
                'productB' => $productB,
                'supportCount' => "{$pair->times_bought_together} / {$totalTransactions}",
                'confidenceText' => $percentage . '%',
                'lift' => 'N/A',
                'summary' => "These products appeared together in {$pair->times_bought_together} transactions. 
                            Consider bundling these products or placing them near each other in your store.",
            ];
        }

        return $results;
    }


    // private function calculateItemsetSupport($itemset, $dataset)
    // {
    //     $count = 0;
    //     foreach ($dataset as $transaction) {
    //         $hasAll = true;
    //         foreach ($itemset as $item) {
    //             if (!in_array($item, $transaction)) {
    //                 $hasAll = false;
    //                 break;
    //             }
    //         }
    //         if ($hasAll) {
    //             $count++;
    //         }
    //     }
    //     return count($dataset) > 0 ? $count / count($dataset) : 0;
    // }

    private function getProductNames($codes)
    {
        if (empty($codes)) return [];
        return DB::table('products')
            ->whereIn('prod_code', $codes)
            ->pluck('name')
            ->toArray();
    }

    
    private function getConfidenceInsight($confidencePercent)
    {
        if ($confidencePercent >= 80) {
            return "Top 25% most frequent: very strong buying pattern.";
        } elseif ($confidencePercent >= 65) {
            return "Top 50% most frequent: strong buying pattern.";
        } elseif ($confidencePercent >= 50) {
            return "Moderately frequent: moderate buying pattern.";
        } else {
            return "Low frequency: weak buying pattern.";
        }
    }

    private function getLiftInsight($lift)
    {
        if ($lift >= 3.0) {
            return "Highly significant: customers are 3x more likely to buy these together than by chance.";
        } elseif ($lift >= 2.0) {
            return "Significant: customers are 2x more likely to buy these together than by chance.";
        } elseif ($lift >= 1.5) {
            return "Noticeable: customers are 50% more likely to buy these together than by chance.";
        } elseif ($lift >= 1.2) {
            return "Slight tendency: customers are slightly more likely to buy these together.";
        } else {
            return "No significant connection: co-purchase is almost random.";
        }
    }




    public function updatedFrequencySelectMonth()
    {
        $this->frequencyTransac();
    }

    public function updatedFrequencySelectYear()
    {
        $this->frequencyTransac();
    }

    public function frequencyTransac() {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        $this->frequencySelectMonth = (int) $this->frequencySelectMonth ?: now()->month;
        $this->frequencySelectYear = (int) $this->frequencySelectYear ?: now()->year;

        DB::statement("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");
        
        $this->frequency = collect(DB::select("
            SELECT
                day_data.date,
                day_data.total_transaction,
                day_data.total_sales,
                day_data.average_sales,
                (
                    (day_data.total_sales - LAG(day_data.total_sales) OVER (ORDER BY day_data.date))
                    / NULLIF(LAG(day_data.total_sales) OVER (ORDER BY day_data.date), 0)
                ) * 100 AS sales_change_percent,
                day_data.peak_hour
            FROM (
                SELECT
                    DATE(x.receipt_date) AS date,
                    COUNT(*) AS total_transaction,
                    SUM(x.net_sales) AS total_sales,
                    AVG(x.net_sales) AS average_sales,
                    MAX(x.peak_hour) AS peak_hour
                FROM (
                    SELECT
                        r.receipt_id,
                        r.receipt_date,
                        HOUR(r.receipt_date) AS peak_hour,
                        -- compute item sales FIRST
                        (
                            SUM(
                                ri.item_quantity * (
                                    COALESCE(
                                        (SELECT ph.old_selling_price
                                        FROM pricing_history ph
                                        WHERE ph.prod_code = ri.prod_code
                                        AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                        ORDER BY ph.effective_from DESC
                                        LIMIT 1),
                                        p.selling_price
                                    )
                                ) - ri.item_discount_amount
                            ) - r.discount_amount
                        ) AS net_sales
                    FROM receipt r
                    JOIN receipt_item ri ON ri.receipt_id = r.receipt_id
                    JOIN products p ON p.prod_code = ri.prod_code
                    WHERE r.owner_id = ?
                    GROUP BY r.receipt_id
                ) AS x
                GROUP BY DATE(x.receipt_date)
            ) AS day_data
            WHERE MONTH(day_data.date) = ?
            AND YEAR(day_data.date) = ?
            ORDER BY day_data.date
        ", [$owner_id, $this->frequencySelectMonth, $this->frequencySelectYear]));


    }

    public function render()
    {
        
        return view('livewire.report-customer');
    }
}
