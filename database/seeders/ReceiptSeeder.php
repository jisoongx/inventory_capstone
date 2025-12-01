<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReceiptSeeder extends Seeder
{
    public function run(): void
    {
        // Check owner 19 exists
        $owner = DB::table('owners')->where('owner_id', 19)->first();
        if (!$owner) {
            $this->command->error('Owner 19 not found. Please insert it first.');
            return;
        }

        // Get products for owner 19
        $products = DB::table('products')->where('owner_id', 19)->get();
        if ($products->isEmpty()) {
            $this->command->error('No products found for owner 19.');
            return;
        }

        $startYear = 2024;
        $endYear = 2025;

        for ($year = $startYear; $year <= $endYear; $year++) {
            $startMonth = ($year == $startYear) ? 1 : 1;
            $endMonth = ($year == $endYear) ? 11 : 12;

            for ($month = $startMonth; $month <= $endMonth; $month++) {

                $receiptsPerMonth = rand(5, 15);

                for ($r = 0; $r < $receiptsPerMonth; $r++) {

                    // Random timestamp for receipt
                    $receiptDate = $this->randomTimestamp($year, $month);

                    // Insert receipt
                    $receiptId = DB::table('receipt')->insertGetId([
                        'receipt_date' => $receiptDate,
                        'owner_id' => 19,
                        'staff_id' => null,
                        'amount_paid' => rand(100, 2000),
                        'discount_type' => 'percent',
                        'discount_value' => rand(0, 20)
                    ]);

                    // Pick 1–5 random products
                    $itemsCount = rand(1, min(5, $products->count()));
                    $items = $products->random($itemsCount);

                    foreach ($items as $product) {

                        // Get inventory batches for this product
                        $batches = DB::table('inventory')
                            ->where('prod_code', $product->prod_code)
                            ->where('owner_id', 19)
                            ->where('stock', '>', 0)
                            ->get();

                        if ($batches->isEmpty()) continue;

                        $quantity = rand(1, min(3, $batches->sum('stock')));
                        $remainingQty = $quantity;

                        foreach ($batches as $batch) {
                            if ($remainingQty <= 0) break;

                            $allocatedQty = min($batch->stock, $remainingQty);

                            // Decrement stock
                            DB::table('inventory')
                                ->where('inven_code', $batch->inven_code)
                                ->decrement('stock', $allocatedQty);

                            // Insert receipt item
                            DB::table('receipt_item')->insert([
                                'item_quantity' => $allocatedQty,
                                'prod_code' => $product->prod_code,
                                'receipt_id' => $receiptId,
                                'item_discount_type' => 'percent',
                                'item_discount_value' => 0,
                                'vat_amount' => 0,
                                'inven_code' => $batch->inven_code
                            ]);

                            $remainingQty -= $allocatedQty;
                        }
                    }
                }
            }
        }

        $this->command->info('Receipts seeding completed for owner 19!');
    }

    private function randomTimestamp($year, $month)
    {
        $start = Carbon::create($year, $month, 1, 8, 0, 0);
        $end = Carbon::create($year, $month, 1, 20, 0, 0)->endOfMonth();

        return Carbon::createFromTimestamp(rand($start->timestamp, $end->timestamp));
    }
}
