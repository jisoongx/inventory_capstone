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

    public $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    public function mount()
    {
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

        // Try Apriori first
        try {
            // Adaptive thresholds
            if ($totalTransactions <= 30) {
                $minSupport = 0.10;
                $minConfidence = 0.60; 
                $minSupportCount = 3;
                $minLift = 1.2;
            } elseif ($totalTransactions <= 70) {
                $minSupport = 0.05;
                $minConfidence = 0.55;
                $minSupportCount = 4;
                $minLift = 1.2;
            } elseif ($totalTransactions <= 150) {
                $minSupport = 0.03;
                $minConfidence = 0.60;
                $minSupportCount = 5;
                $minLift = 1.2;
            } else {
                $minSupport = 0.02;
                $minConfidence = 0.60;
                $minSupportCount = 8;
                $minLift = 1.2;
            }

            $associator = new Apriori($minSupport, $minConfidence);
            $associator->train($dataset, []);
            $rules = $associator->getRules();

            $uniquePairs = [];

            foreach ($rules as $rule) {
                $antecedentNames = $this->getProductNames($rule['antecedent']);
                $consequentNames = $this->getProductNames($rule['consequent']);

                $a = implode(', ', $antecedentNames);
                $b = implode(', ', $consequentNames);

                $pairArray = [$a, $b];
                sort($pairArray, SORT_NATURAL | SORT_FLAG_CASE);
                $pairKey = implode(' | ', $pairArray);

                $supportAB = $rule['support'];
                $confidenceAB = $rule['confidence'];
                $supportCount = round($supportAB * $totalTransactions);

                if ($supportCount < $minSupportCount) continue;

                $supportA = $this->calculateItemsetSupport($rule['antecedent'], $dataset);
                $supportB = $this->calculateItemsetSupport($rule['consequent'], $dataset);
                $lift = ($supportA > 0 && $supportB > 0) ? round($supportAB / ($supportA * $supportB), 2) : 1.00;

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

                if ($avgConfidence < 0.6 || $avgLift < 1.2) continue;

                $cleanResults[] = [
                    'productA' => $pair['productA'],
                    'productB' => $pair['productB'],
                    'supportCount' => "{$pair['supportCount']} / {$totalTransactions}",
                    'confidenceText' => round($avgConfidence * 100) . '%',
                    'lift' => "{$avgLift}x",
                    'summary' => "Pattern: " . round($avgConfidence * 100) . "% of customers who buy <b>{$pair['productA']}</b> also buy <b>{$pair['productB']}</b>.<br>
                                Strength: {$this->getConfidenceInsight(round($avgConfidence * 100))} {$this->getLiftInsight($avgLift)}",
                ];
            }

            usort($cleanResults, fn($a, $b) =>
                intval(explode(' ', $b['supportCount'])[0]) <=> intval(explode(' ', $a['supportCount'])[0])
            );

            
            if (empty($cleanResults) || count($cleanResults) < 3) {
                
                $this->results = $this->runCoOccurrenceAnalysis($totalTransactions);
                $this->type = "Co-Occurence Analysis";
                
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
            
            if (empty($this->results)) {
                $this->results = ['message' => 'Error generating analysis: ' . $e->getMessage()];
            }
        }

        $this->loading = false;
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


    private function calculateItemsetSupport($itemset, $dataset)
    {
        $count = 0;
        foreach ($dataset as $transaction) {
            $hasAll = true;
            foreach ($itemset as $item) {
                if (!in_array($item, $transaction)) {
                    $hasAll = false;
                    break;
                }
            }
            if ($hasAll) {
                $count++;
            }
        }
        return count($dataset) > 0 ? $count / count($dataset) : 0;
    }

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
            return "This is a <b>very strong</b> buying pattern.";
        } elseif ($confidencePercent >= 65) {
            return "This is a <b>strong</b> buying pattern.";
        } elseif ($confidencePercent >= 50) {
            return "This is a <b>moderate</b> buying pattern.";
        } else {
            return "This is a <b>weak</b> buying pattern.";
        }
    }

    private function getLiftInsight($lift)
    {
        if ($lift >= 3.0) {
            return "Customers are <b>3 times more likely</b> to buy these together than separately.";
        } elseif ($lift >= 2.0) {
            return "Customers are <b>twice as likely</b> to buy these together than separately.";
        } elseif ($lift >= 1.5) {
            return "Customers are <b>50% more likely</b> to buy these together than by chance.";
        } elseif ($lift >= 1.2) {
            return "There is a <b>slight tendency</b> to buy these together.";
        } else {
            return "These products show <b>no special connection</b>.";
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
                SELECT *
                FROM (
                    SELECT 
                        DATE(r.receipt_date) AS date,
                        COUNT(DISTINCT r.receipt_id) AS total_transaction,
                        SUM(ri.item_quantity * COALESCE(
                                (SELECT ph.old_selling_price
                                FROM pricing_history ph
                                WHERE ph.prod_code = ri.prod_code
                                AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                ORDER BY ph.effective_from DESC
                                LIMIT 1),
                                p.selling_price
                            )) AS total_sales,
                        (SUM(ri.item_quantity * COALESCE(
                                (SELECT ph.old_selling_price
                                FROM pricing_history ph
                                WHERE ph.prod_code = ri.prod_code
                                AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                ORDER BY ph.effective_from DESC
                                LIMIT 1),
                                p.selling_price
                            )) / COUNT(DISTINCT r.receipt_id)) AS average_sales,
                        (
                            (
                                SUM(ri.item_quantity * COALESCE(
                                        (SELECT ph.old_selling_price
                                        FROM pricing_history ph
                                        WHERE ph.prod_code = ri.prod_code
                                        AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                        ORDER BY ph.effective_from DESC
                                        LIMIT 1),
                                        p.selling_price
                                    ))
                                - LAG(SUM(ri.item_quantity * COALESCE(
                                            (SELECT ph.old_selling_price
                                            FROM pricing_history ph
                                            WHERE ph.prod_code = ri.prod_code
                                            AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                            ORDER BY ph.effective_from DESC
                                            LIMIT 1),
                                            p.selling_price
                                        ))) OVER (ORDER BY DATE(r.receipt_date))
                            ) / NULLIF(
                                LAG(SUM(ri.item_quantity * COALESCE(
                                            (SELECT ph.old_selling_price
                                            FROM pricing_history ph
                                            WHERE ph.prod_code = ri.prod_code
                                            AND r.receipt_date BETWEEN ph.effective_from AND ph.effective_to
                                            ORDER BY ph.effective_from DESC
                                            LIMIT 1),
                                            p.selling_price
                                        ))) OVER (ORDER BY DATE(r.receipt_date)),
                                0
                            )
                        ) * 100 AS sales_change_percent,
                        HOUR(r.receipt_date) AS peak_hour
                    FROM receipt r
                    JOIN receipt_item ri ON r.receipt_id = ri.receipt_id
                    JOIN products p ON ri.prod_code = p.prod_code
                    WHERE r.owner_id = ?
                    GROUP BY DATE(r.receipt_date)
                ) AS full_data
                WHERE MONTH(full_data.date) = ?
                AND YEAR(full_data.date) = ?
                ORDER BY full_data.date;
                ", [$owner_id, $this->frequencySelectMonth, $this->frequencySelectYear]));

    }

    public function render()
    {
        
        return view('livewire.report-customer');
    }
}
