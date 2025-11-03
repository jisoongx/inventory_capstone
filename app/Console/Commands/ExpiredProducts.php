<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// php artisan check:expiredProduct

class ExpiredProducts extends Command
{
    protected $signature = 'check:expiredProduct';
    protected $description = 'Automate expired products to loss report.';

    public function handle(): int
    {
        try {

            $owners = collect(DB::select("
                SELECT o.owner_id, o.email, s.subscription_end
                FROM subscriptions s
                JOIN owners o ON s.owner_id = o.owner_id
                WHERE s.subscription_end >= CURDATE()
            "));

            if ($owners->isEmpty()) {
                $this->info('No accounts found for reminders.');
                return Command::SUCCESS;
            }

            $sql = "
                INSERT INTO damaged_items (inven_code, damaged_quantity, damaged_reason, damaged_date, owner_id)
                SELECT inven_code, stock, 'Expired', NOW(), ?
                FROM inventory
                WHERE expiration_date < CURDATE()
                AND stock > 0
                AND is_expired = 0
            ";

            foreach ($owners as $owner) {
                DB::insert($sql, [$owner->owner_id]);
            } 

            DB::statement("
                UPDATE inventory
                SET stock = 0, is_expired = 1
                WHERE expiration_date < CURDATE() AND stock > 0
            ");

            $this->info("Expired products successfully moved to damaged_items table.");
            Log::info("✅ Expired products moved to damaged_items table at " . now());

            return Command::SUCCESS;


        } catch (\Exception $e) {
            Log::error("❌ Error while moving expired products: " . $e->getMessage());
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
