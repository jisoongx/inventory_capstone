<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Payment;
use App\Services\PayPalService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdatePaypalSubscriptions extends Command
{
    protected $signature = 'paypal:update-subscriptions';
    protected $description = 'Check PayPal subscriptions and update subscription_end & payment records';

    protected PayPalService $paypal;

    public function __construct(PayPalService $paypal)
    {
        parent::__construct();
        $this->paypal = $paypal;
    }

    public function handle()
    {
        $this->info('Checking PayPal subscriptions...');

        // Get all active subscriptions that have at least one PayPal payment
        $subscriptions = Subscription::where('status', 'active')
            ->whereHas('payments', function ($q) {
                $q->where('payment_mode', 'paypal')
                    ->whereNotNull('payment_acc_number');
            })
            ->get();

        foreach ($subscriptions as $subscription) {
            // Get the latest PayPal payment for this subscription
            $latestPayment = $subscription->payments()
                ->where('payment_mode', 'paypal')
                ->latest('payment_date')
                ->first();

            if (!$latestPayment) {
                $this->warn("No PayPal payment found for subscription {$subscription->subscription_id}");
                continue;
            }

            $paypalId = $latestPayment->payment_acc_number;

            // Fetch subscription details from PayPal
            $details = $this->paypal->getSubscription($paypalId);

            if (!$details) {
                $this->warn("Could not fetch PayPal subscription: {$paypalId}");
                Log::error("Could not fetch PayPal subscription details for ID: {$paypalId}");
                continue;
            }

            $status = $details['status'] ?? null;
            $lastPaymentTime = $details['billing_info']['last_payment']['time'] ?? null;
            $amount = $details['billing_info']['last_payment']['amount']['value'] ?? 0;

            // 1️⃣ Check if subscription was cancelled
            if ($status === 'CANCELLED') {
                $subscription->status = 'cancelled';
                $subscription->save();
                $this->info("Subscription {$paypalId} cancelled");
                continue;
            }

            // 2️⃣ Update subscription_end if last payment was successful
            if ($lastPaymentTime) {
                $lastPaymentCarbon = Carbon::parse($lastPaymentTime);
                $currentEnd = $subscription->subscription_end ? Carbon::parse($subscription->subscription_end) : null;

                // Only extend if subscription_end is before the last payment
                if (!$currentEnd || $currentEnd < $lastPaymentCarbon) {
                    $newEnd = $lastPaymentCarbon->copy()->addMonth(); // assumes monthly plan
                    $subscription->subscription_end = $newEnd;
                    $subscription->status = 'active';
                    $subscription->save();

                    // Create a new Payment record for this billing cycle
                    Payment::create([
                        'owner_id' => $subscription->owner_id,
                        'subscription_id' => $subscription->subscription_id,
                        'payment_mode' => 'paypal',
                        'payment_acc_number' => $paypalId,
                        'payment_amount' => $amount,
                        'payment_date' => $lastPaymentCarbon,
                    ]);

                    $this->info("Subscription {$paypalId} extended to {$newEnd}");
                    Log::info("Subscription {$paypalId} extended to {$newEnd}");
                }
            }
        }

        $this->info('PayPal subscription check complete.');
    }
}
