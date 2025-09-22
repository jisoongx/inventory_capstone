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


   // In app/Http/Controllers/SubscriptionController.php

public function subscribers(Request $request)
{
    // Get filter values from the URL, with 'active' as the default status
    $status = $request->input('status', 'active');
    $planId = $request->input('plan');
    $search = $request->input('search');
    $expiryDays = $request->input('days');
    $expiryDate = $request->input('date');

    // Start building the query for Owners who have subscriptions
    $query = Owner::query()->whereHas('subscriptions');

    // Apply filters to the subscriptions relationship
    $query->whereHas('subscriptions', function ($q) use ($status, $planId, $expiryDays, $expiryDate) {
        if ($status === 'upcoming') {
            $q->where('status', 'active')->whereBetween('subscription_end', [now(), now()->addDays(30)]);
        } else {
            $q->where('status', $status);
        }

        if ($planId) {
            $q->where('plan_id', $planId);
        }

        if ($expiryDays) {
            $q->where('subscription_end', '<=', now()->addDays($expiryDays));
        }

        if ($expiryDate) {
            $q->whereDate('subscription_end', $expiryDate);
        }
    });

    // Apply search filter to the Owner's name or store name
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('firstname', 'like', "%{$search}%")
              ->orWhere('lastname', 'like', "%{$search}%")
              ->orWhere('store_name', 'like', "%{$search}%");
        });
    }

    // Eager load the filtered subscriptions and their details
    $query->with(['subscriptions' => function ($q) use ($status, $planId, $expiryDays, $expiryDate) {
        if ($status === 'upcoming') {
            $q->where('status', 'active')->whereBetween('subscription_end', [now(), now()->addDays(30)]);
        } else {
            $q->where('status', $status);
        }
        if ($planId) { $q->where('plan_id', $planId); }
        if ($expiryDays) { $q->where('subscription_end', '<=', now()->addDays($expiryDays)); }
        if ($expiryDate) { $q->whereDate('subscription_end', $expiryDate); }

        $q->with('planDetails'); // Also load plan details for the filtered subs
    }]);
    
    // Paginate the final, filtered query
    $clients = $query->paginate(10)->withQueryString();

    // Get total counts for the stat cards (these should run on the whole unfiltered dataset)
    $activeCount = Subscription::where('status', 'active')->count();
    $expiredCount = Subscription::where('status', 'expired')->count();
    $upcomingCount = Subscription::where('status', 'active')->whereBetween('subscription_end', [now(), now()->addDays(30)])->count();
    
    return view('dashboards.super_admin.subscribers', [
        'clients' => $clients,
        'activeCount' => $activeCount,
        'expiredCount' => $expiredCount,
        'upcomingCount' => $upcomingCount,
    ]);
}


    // In app/Http/Controllers/SubscriptionController.php
    // In app/Http/Controllers/SubscriptionController.php
    public function billing(Request $request)
    {
        // --- 1. Get ALL Clients with Data (Master Query) ---
        $allClientsQuery = Owner::has('subscriptions.payments')->with('subscriptions.planDetails', 'subscriptions.payments');
        $allClients = $allClientsQuery->get();

        // --- 2. Perform ALL Calculations on the Full Dataset ---

        // Overall Period Filter for Revenue Card
        $period = $request->input('period', 'all_time');
        $startDate = null;
        $endDate = now();
        switch ($period) {
            case 'this_month':
                $startDate = now()->startOfMonth();
                break;
            case 'last_month':
                $startDate = now()->subMonthNoOverflow()->startOfMonth();
                $endDate = now()->subMonthNoOverflow()->endOfMonth();
                break;
            case 'this_year':
                $startDate = now()->startOfYear();
                break;
            case 'last_year':
                $startDate = now()->subYear()->startOfYear();
                $endDate = now()->subYear()->endOfYear();
                break;
        }

        $allPayments = $allClients->flatMap->subscriptions->flatMap->payments;

        // Latest Payment Calculation
        $latestPayment = $allPayments->sortByDesc('payment_date')->first();
        $latest = null;
        if ($latestPayment) {
            $latest = ['payment' => $latestPayment, 'sub' => $latestPayment->subscription, 'owner' => $latestPayment->subscription->owner];
        }

        // Subscription Revenue Card Calculation
        $paymentsInPeriod = $allPayments->filter(fn($p) => !$startDate || \Carbon\Carbon::parse($p->payment_date)->between($startDate, $endDate));
        $revenue = [
            'basic' => $paymentsInPeriod->where('subscription.planDetails.plan_title', 'Basic')->sum('payment_amount'),
            'premium' => $paymentsInPeriod->where('subscription.planDetails.plan_title', 'Premium')->sum('payment_amount')
        ];
        $totalRevenue = $revenue['basic'] + $revenue['premium'];
        $basicPercentage = $totalRevenue > 0 ? ($revenue['basic'] / $totalRevenue) * 100 : 0;
        $premiumPercentage = $totalRevenue > 0 ? ($revenue['premium'] / $totalRevenue) * 100 : 0;

        // Plan Distribution Report Calculation
        $pd_startDate = $request->input('pd_start_date');
        $pd_endDate = $request->input('pd_end_date');
        $planStats = ['basic' => ['active' => 0, 'expired' => 0], 'premium' => ['active' => 0, 'expired' => 0]];
        foreach ($allClients->flatMap->subscriptions as $sub) {
            $subStartDate = \Carbon\Carbon::parse($sub->subscription_start);
            if ($pd_startDate && $pd_endDate && !$subStartDate->between($pd_startDate, $pd_endDate)) continue;

            $planTitle = strtolower($sub->planDetails->plan_title ?? '');
            if (array_key_exists($planTitle, $planStats) && in_array($sub->status, ['active', 'expired'])) {
                $planStats[$planTitle][$sub->status]++;
            }
        }
        $planStats['basic']['total'] = $planStats['basic']['active'] + $planStats['basic']['expired'];
        $planStats['premium']['total'] = $planStats['premium']['active'] + $planStats['premium']['expired'];
        $totalActive = $planStats['basic']['active'] + $planStats['premium']['active'];
        $totalExpired = $planStats['basic']['expired'] + $planStats['premium']['expired'];
        $grandTotalSubs = $totalActive + $totalExpired;

        // Revenue Breakdown Report Calculation
        $customStart = $request->input('start_date');
        $customEnd = $request->input('end_date');
        $revStartDate = $customStart ? \Carbon\Carbon::parse($customStart)->startOfDay() : $startDate;
        $revEndDate = $customEnd ? \Carbon\Carbon::parse($customEnd)->endOfDay() : $endDate;

        $monthlyRevenue = [];
        foreach ($allPayments as $payment) {
            $paymentDate = \Carbon\Carbon::parse($payment->payment_date);
            if (!$revStartDate || $paymentDate->between($revStartDate, $revEndDate)) {
                $monthKey = $paymentDate->format('Y-m');
                if (!isset($monthlyRevenue[$monthKey])) {
                    $monthlyRevenue[$monthKey] = ['basic' => 0, 'premium' => 0, 'total' => 0];
                }

                $plan = strtolower(trim($payment->subscription->planDetails->plan_title ?? ''));
                if (isset($monthlyRevenue[$monthKey][$plan])) {
                    $monthlyRevenue[$monthKey][$plan] += $payment->payment_amount;
                }
                $monthlyRevenue[$monthKey]['total'] += $payment->payment_amount;
            }
        }
        krsort($monthlyRevenue);
        $breakdownTotalBasic = array_sum(array_column($monthlyRevenue, 'basic'));
        $breakdownTotalPremium = array_sum(array_column($monthlyRevenue, 'premium'));
        $breakdownGrandTotal = array_sum(array_column($monthlyRevenue, 'total'));

        // --- 3. Paginate the Results for the Table View ---
        $clients = Owner::has('subscriptions.payments') // <<< FIX IS HERE
            ->with('subscriptions.planDetails', 'subscriptions.payments')
            ->paginate(10);

        // --- 4. Pass Everything to the View ---
        return view('dashboards.super_admin.billing-history', compact(
            'clients',
            'latest',
            'period',
            'revenue',
            'totalRevenue',
            'basicPercentage',
            'premiumPercentage',
            'pd_startDate',
            'pd_endDate',
            'planStats',
            'totalActive',
            'totalExpired',
            'grandTotalSubs',
            'customStart',
            'customEnd',
            'monthlyRevenue',
            'breakdownTotalBasic',
            'breakdownTotalPremium',
            'breakdownGrandTotal'
        ));
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
