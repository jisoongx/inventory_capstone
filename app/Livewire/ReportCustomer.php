<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Phpml\Association\Apriori;

class ReportCustomer extends Component
{
    public $ownerId;
    public $month;
    public $year;
    public $results = [];
    public $loading = false;
    public $type = "Nothing";

    public $frequency= [];
    public $frequencySelectMonth;

    public function mount()
    {
        $this->ownerId = Auth::guard('owner')->user()->owner_id;
        $this->month = now()->month;
        $this->year = now()->year;

        $this->frequencySelectMonth = now()->month;
        $this->frequencyTransac();
    }

    public function generateReport()
    {
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
        ", [$this->ownerId, $this->month, $this->year]);

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
        ", [$this->ownerId, $this->month, $this->year]);

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
                'summary' => "🔗 <b>Frequently Bought Together:</b> These products appeared together in {$pair->times_bought_together} transactions.<br>
                            💡 <b>Action:</b> Consider bundling these products or placing them near each other in your store.",
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

    private function getActionableInsight($productA, $productB, $confidencePercent, $lift)
    {
        $insights = [];
        
        if ($confidencePercent >= 70 && $lift >= 2.0) {
            $insights[] = "Place <b>{$productA}</b> and <b>{$productB}</b> near each other in your store";
            $insights[] = "Create a combo deal or bundle discount for these items";
            $insights[] = "When <b>{$productA}</b> is running low, ensure <b>{$productB}</b> is well-stocked";
        } elseif ($confidencePercent >= 60 && $lift >= 1.5) {
            $insights[] = "Consider placing <b>{$productB}</b> in the same aisle as <b>{$productA}</b>";
            $insights[] = "Suggest <b>{$productB}</b> to customers buying <b>{$productA}</b>";
            $insights[] = "Stock these items together during busy hours";
        } elseif ($confidencePercent >= 50) {
            $insights[] = "Monitor this pattern - it may become stronger over time";
            $insights[] = "Display <b>{$productB}</b> prominently when promoting <b>{$productA}</b>";
        } else {
            $insights[] = "Track this relationship to see if it strengthens with more data";
        }
        
        return $insights[array_rand($insights)];
    }



    public function updatedFrequencySelectMonth()
    {
        $this->frequencyTransac();
    }

    public function frequencyTransac() {
        $month = $this->frequencySelectMonth ?? now()->month;
        
        $owner_id = Auth::guard('owner')->user()->owner_id;
        // $month = now()->month;
        $year = now()->year;

        $this->frequency = collect(DB::select("
            select DATE(r.receipt_date) as date,
                count(DISTINCT(r.receipt_id)) as total_transaction, 
                sum(ri.item_quantity * p.selling_price) as total_sales,
                (SUM(ri.item_quantity * p.selling_price) / COUNT(DISTINCT(r.receipt_id))) AS average_sales
            from receipt r 
            join receipt_item ri on r.receipt_id = ri.receipt_id
            join products p on ri.prod_code = p.prod_code 
            where month(r.receipt_date) = ?
            and year(r.receipt_date) = ?
            and r.owner_id = ?
            group by DATE(r.receipt_date);
        ", [ $month, $year, $owner_id]));
    }

    public function render()
    {
        return view('livewire.report-customer');
    }
}
