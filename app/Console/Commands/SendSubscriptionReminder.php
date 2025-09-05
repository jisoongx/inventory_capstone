<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SendSubscriptionReminder extends Command
{
    protected $signature = 'subscription:reminder';
    protected $description = 'Send reminders for subscriptions approaching expiration';

    public function handle()
    {
        $today = Carbon::today();

        // Fetch subscriptions ending in the next 14 days
        $subscriptions = DB::table('subscriptions')
            ->whereDate('subscription_end', '>=', $today)
            ->whereDate('subscription_end', '<=', $today->copy()->addDays(14))
            ->get();

        foreach ($subscriptions as $sub) {
            // Get owner email from owner_id
            $ownerEmail = DB::table('owners')
                ->where('id', $sub->owner_id)
                ->value('email');

            if (!$ownerEmail) continue; // skip if no email found

            // Insert notification
            $notifId = DB::table('notification')->insertGetId([
                'notif_title' => 'Subscription Expiring Soon',
                'notif_message' => "Your subscription will expire on {$sub->subscription_end}. Please renew.",
                'notif_created_on' => now(),
            ]);

            // Insert user_notification
            DB::table('user_notification')->insert([
                'notif_id' => $notifId,
                'usernotif_email' => $ownerEmail,
                'usernotif_type' => 'subscription',
                'usernotif_is_read' => 0,
            ]);
        }

        $this->info('Subscription reminders inserted successfully!');
    }
}
