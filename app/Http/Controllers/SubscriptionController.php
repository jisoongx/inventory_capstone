<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Owner;
use App\Models\Plan;
use Carbon\Carbon;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{


    public function subscribers()
    {
        // 1. Get ONLY clients who have a subscription
        $allClients = Owner::has('subscriptions')->with('subscriptions')->get();

        // 2. Calculate totals from this filtered collection
        $activeCount = $allClients->sum(fn($client) => $client->subscriptions->where('status', 'active')->count());
        $expiredCount = $allClients->sum(fn($client) => $client->subscriptions->where('status', 'expired')->count());
        $upcomingCount = $allClients->sum(fn($client) => $client->subscriptions->where('status', 'active')->whereBetween('subscription_end', [now(), now()->addDays(30)])->count());

        // 3. Paginate ONLY the clients who have a subscription
        $paginatedClients = Owner::has('subscriptions')->with('subscriptions.planDetails', 'subscriptions.payments')->paginate(10);

        // 4. Return the view with the correct data
        return view('dashboards.super_admin.subscribers', [
            'clients' => $paginatedClients,
            'activeCount' => $activeCount,
            'expiredCount' => $expiredCount,
            'upcomingCount' => $upcomingCount,
        ]);
    }


    public function billing()
    {
        // This block updates any subscriptions that have ended
        $today = now()->startOfDay();
        $subscriptionsToExpire = Subscription::where('subscription_end', '<=', $today)
            ->where('status', 'active') // Only find active ones to expire
            ->get();

        foreach ($subscriptionsToExpire as $sub) {
            $sub->status = 'expired';
            $sub->save();
        }

        // This query now correctly fetches and paginates ONLY owners with subscriptions
        $clients = Owner::whereHas('subscriptions') // <-- FIX IS HERE
            ->with([
                'subscriptions.planDetails',
                'subscriptions.payments',
            ])
            ->orderBy('created_on', 'desc')
            ->paginate(10);

        // Pass the correctly counted and paginated clients to the view
        return view('dashboards.super_admin.billing-history', compact('clients'));
    }
    
    public function create()
    {
        $plans = Plan::all();
        return view('subscription', compact('plans'));
    }

    // In app/Http/Controllers/SubscriptionController.php

    // In app/Http/Controllers/SubscriptionController.php

    public function store(Request $request, $planId)
    {
        // Validate the incoming data
        $validatedData = $request->validate([
            'paymentMethod' => 'required|in:gcash,debit',
            'paymentAccNum' => ['required', 'string', 'numeric', function ($attribute, $value, $fail) use ($request) {
                if ($request->paymentMethod === 'gcash' && strlen($value) !== 11) {
                    $fail('The GCash number must be 11 digits.');
                }
                if ($request->paymentMethod === 'debit' && strlen($value) !== 16) {
                    $fail('The debit card number must be 16 digits.');
                }
            }],
            'plan_id' => 'required|exists:plans,plan_id',
        ]);

        $owner = Auth::guard('owner')->user();
        $plan = Plan::find($planId);

        // Additional server-side checks
        if (!$plan) {
            return response()->json(['message' => 'Plan not found.'], 404);
        }
        if ($owner->activeSubscription()->exists()) {
            return response()->json(['message' => 'You already have an active subscription.'], 409);
        }

        try {
            $subscription = Subscription::create([
                'owner_id' => $owner->owner_id,
                'plan_id' => $plan->plan_id,
                'subscription_start' => now(),
                'subscription_end' => now()->addMonths($plan->plan_duration_months),
                'status' => 'active',
            ]);

            Payment::create([
                'owner_id' => $owner->owner_id,
                'subscription_id' => $subscription->subscription_id,
                'payment_mode' => $validatedData['paymentMethod'],
                'payment_acc_number' => $validatedData['paymentAccNum'],
                'payment_amount' => $plan->plan_price,
                'payment_date' => now(),
            ]);

            // **On success, return a JSON success response**
            return response()->json(['success' => true, 'message' => 'Payment successful!']);
        } catch (\Exception $e) {
            Log::error('Subscription or Payment creation failed: ' . $e->getMessage());
            // **On failure, return a JSON error response**
            return response()->json(['message' => 'Failed to process subscription. Please try again.'], 500);
        }
    }
    
}
