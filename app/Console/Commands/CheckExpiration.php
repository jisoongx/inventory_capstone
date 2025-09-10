<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckExpiration extends Command
{
    protected $signature = 'check:expiration';
    protected $description = 'Check for users with accounts expiring soon and create notifications';

    public function handle(): int
    {
        $this->info('Running expiration check...');

        $reminderDays = [7, 5, 2, 1];

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

        foreach ($owners as $owner) {

            $formattedEndDate = date('F j, Y', strtotime($owner->subscription_end));

            foreach ($reminderDays as $daysBefore) {

                $notifDate = date('Y-m-d', strtotime($owner->subscription_end . " -{$daysBefore} days"));

                if ($notifDate !== date('Y-m-d')) {
                    continue;
                }

                $exists = DB::selectOne("
                    SELECT 1
                    FROM user_notification un
                    JOIN notification n ON n.notif_id = un.notif_id
                    WHERE un.usernotif_email = ?
                    AND n.notif_type = 'specific'
                    AND n.notif_date = ?
                    LIMIT 1
                ", [$owner->email, $notifDate]);

                if ($exists) {
                    continue; 
                }
                

                DB::insert("INSERT INTO notification (notif_title, notif_message, notif_target, notif_created_on, notif_date, super_id, notif_type)
                    VALUES (?, ?, ?, NOW(), ?, ?, ?)", 
                    ['Expiration Notice',
                    "Your account will expire on {$formattedEndDate}. Please renew your subscription.",
                    'owner',
                    $notifDate,
                    2,
                    'specific']);

                $notif_id = DB::getPdo()->lastInsertId();

                
                DB::insert("INSERT INTO user_notification (notif_id, usernotif_email, usernotif_is_read, usernotif_seen, usernotif_read_at)
                    VALUES (?, ?, 0, 0, NULL)", [$notif_id, $owner->email]);
            }
        }

        $this->info('Notifications processed for all users.');
        Log::info('CheckExpiration command ran at ' . now());

        return Command::SUCCESS;
    }

}


                // $exists = DB::table('notification')
                //     ->where('notif_target', 'owner')
                //     ->where('super_id', 2)
                //     ->where('notif_type', 'specific')
                //     ->where('notif_date', $notifDate)
                //     ->where('owner_id', $owner->owner_id)
                //     ->exists();
