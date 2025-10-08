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

    // In your SubscriptionController.php

    // In app/Http/Controllers/SubscriptionController.php

    public function subscribers(Request $request)
    {
        // 1. Get filter values from the URL
        Subscription::where('status', 'active')
            ->where('subscription_end', '<', now())
            ->update(['status' => 'expired']);

        $status = $request->input('status', 'active');
        $planId = $request->input('plan');
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $range = $request->input('range');

        // 2. Start the main query on the Owner model
        $query = Owner::query();

        // 3. Apply the global search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('store_name', 'like', "%{$search}%");
            });
        }

        // 4. Filter the subscriptions relationship
        $query->whereHas('subscriptions', function ($q) use ($status, $planId, $startDate, $range) {
            if ($status === 'upcoming') {
                $q->where('status', 'active')->whereBetween('subscription_end', [Carbon::today(), Carbon::today()->addDays(30)]);
            } else {
                $q->where('status', $status);
            }
            if ($startDate) {
                $q->whereDate('subscription_start', $startDate);
            }
            if ($planId) {
                $q->where('plan_id', $planId);
            }
            if ($status === 'upcoming' && $range) {
                switch ($range) {
                    case 'urgent':
                        $q->whereBetween('subscription_end', [Carbon::today(), Carbon::today()->addDays(7)]);
                        break;
                    case 'soon':
                        $q->whereBetween('subscription_end', [Carbon::today()->addDays(8), Carbon::today()->addDays(14)]);
                        break;
                    case 'later':
                        $q->whereBetween('subscription_end', [Carbon::today()->addDays(15), Carbon::today()->addDays(30)]);
                        break;
                }
            }
        });

        // 5. Eager load the filtered subscriptions for display
        $query->with(['subscriptions' => function ($q) use ($status, $planId, $startDate, $range) {
            if ($status === 'upcoming') {
                $q->where('status', 'active')->whereBetween('subscription_end', [Carbon::today(), Carbon::today()->addDays(30)]);
            } else {
                $q->where('status', $status);
            }
            if ($startDate) $q->whereDate('subscription_start', $startDate);
            if ($planId) $q->where('plan_id', $planId);
            if ($status === 'upcoming' && $range) {
                switch ($range) {
                    case 'urgent':
                        $q->whereBetween('subscription_end', [Carbon::today(), Carbon::today()->addDays(7)]);
                        break;
                    case 'soon':
                        $q->whereBetween('subscription_end', [Carbon::today()->addDays(8), Carbon::today()->addDays(14)]);
                        break;
                    case 'later':
                        $q->whereBetween('subscription_end', [Carbon::today()->addDays(15), Carbon::today()->addDays(30)]);
                        break;
                }
            }
            $q->with('planDetails');
        }]);

        // 6. Paginate the results
        $clients = $query->paginate(10)->withQueryString();

        $isFiltered = ($request->has('search') && $request->input('search') !== '')
            || ($request->has('plan') && $request->input('plan') !== '')
            || ($request->has('range') && $request->input('range') !== '')
            || ($request->has('start_date') && $request->input('start_date') !== '');






        // For AJAX requests, we pass only the data needed for the table partial
        if ($request->ajax()) {
            return view('dashboards.super_admin.subscribers', compact('clients', 'isFiltered'))->render();
        }

        // For the initial full page load, we pass all data including stat card counts
        $activeCount = Subscription::where('status', 'active')->count();
        $expiredCount = Subscription::where('status', 'expired')->count();
        $upcomingCount = Subscription::where('status', 'active')->whereBetween('subscription_end', [now(), now()->addDays(30)])->count();

        return view('dashboards.super_admin.subscribers', compact(
            'clients',
            'activeCount',
            'expiredCount',
            'upcomingCount',
            'isFiltered'
        ));
    }



    public function billing(Request $request)
    {
        // --- 1. Get Data for the Top Cards (Efficiently) ---
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

        $paymentsInPeriod = Payment::with('subscription.planDetails')
            ->when($startDate, fn($query) => $query->whereBetween('payment_date', [$startDate, $endDate]))
            ->get();

        $revenue = [
            'basic' => $paymentsInPeriod->where('subscription.planDetails.plan_title', 'Basic')->sum('payment_amount'),
            'premium' => $paymentsInPeriod->where('subscription.planDetails.plan_title', 'Premium')->sum('payment_amount'),
        ];
        $totalRevenue = $revenue['basic'] + $revenue['premium'];
        $basicPercentage = $totalRevenue > 0 ? ($revenue['basic'] / $totalRevenue) * 100 : 0;
        $premiumPercentage = $totalRevenue > 0 ? ($revenue['premium'] / $totalRevenue) * 100 : 0;

        $subscriptionsInPeriod = Subscription::with('planDetails')
            ->when($startDate, fn($query) => $query->whereBetween('subscription_start', [$startDate, $endDate]))
            ->get();

        $cardCounts = [
            'basic' => $subscriptionsInPeriod->where('planDetails.plan_title', 'Basic')->count(),
            'premium' => $subscriptionsInPeriod->where('planDetails.plan_title', 'Premium')->count(),
        ];

        $plans = Plan::all();
        $basicPrice = $plans->firstWhere('plan_title', 'Basic')->plan_price ?? 0;
        $premiumPrice = $plans->firstWhere('plan_title', 'Premium')->plan_price ?? 0;

        $latest = null;
        $latestPayment = Payment::with('subscription.owner')->latest('payment_date')->first();
        if ($latestPayment) {
            $latest = [
                'payment' => $latestPayment,
                'sub' => $latestPayment->subscription,
                'owner' => $latestPayment->subscription->owner
            ];
        }

        $pd_startDate = $request->input('pd_start_date');
        $pd_endDate = $request->input('pd_end_date');
        $planStats = ['basic' => ['active' => 0, 'expired' => 0], 'premium' => ['active' => 0, 'expired' => 0]];
        $allSubscriptions = Subscription::with('planDetails')
            ->when($pd_startDate && $pd_endDate, fn($query) => $query->whereBetween('subscription_start', [$pd_startDate, $pd_endDate]))
            ->get();

        foreach ($allSubscriptions as $sub) {
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

        $customStart = $request->input('start_date');
        $customEnd = $request->input('end_date');
        $revStartDate = $customStart ? Carbon::parse($customStart)->startOfDay() : $startDate;
        $revEndDate = $customEnd ? Carbon::parse($customEnd)->endOfDay() : $endDate;

        $monthlyRevenueData = Payment::selectRaw("DATE_FORMAT(payment.payment_date, '%Y-%m') as month")
            ->selectRaw("SUM(CASE WHEN `plans`.`plan_title` = 'Basic' THEN `payment`.`payment_amount` ELSE 0 END) as basic_revenue")
            ->selectRaw("SUM(CASE WHEN `plans`.`plan_title` = 'Premium' THEN `payment`.`payment_amount` ELSE 0 END) as premium_revenue")
            ->join('subscriptions', 'payment.subscription_id', '=', 'subscriptions.subscription_id')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.plan_id')
            ->when($revStartDate, fn($query) => $query->whereBetween('payment.payment_date', [$revStartDate, $revEndDate]))
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        $monthlyRevenue = $monthlyRevenueData->mapWithKeys(function ($item) {
            return [
                $item->month => [
                    'basic' => $item->basic_revenue,
                    'premium' => $item->premium_revenue,
                    'total' => $item->basic_revenue + $item->premium_revenue,
                ]
            ];
        })->toArray();

        $breakdownTotalBasic = array_sum(array_column($monthlyRevenue, 'basic'));
        $breakdownTotalPremium = array_sum(array_column($monthlyRevenue, 'premium'));
        $breakdownGrandTotal = array_sum(array_column($monthlyRevenue, 'total'));

        // --- 3. Build the Paginated Query for the Main Table ---
        $clientsQuery = Owner::has('subscriptions.payments')
            ->with('subscriptions.planDetails', 'subscriptions.payments');

        // Check if any filter is applied to set the $isFiltered flag
        $isFiltered = $request->hasAny(['search', 'date', 'status', 'plan']);

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $clientsQuery->where(function ($q) use ($searchTerm) {
                $q->where('store_name', 'like', "%{$searchTerm}%")
                    ->orWhere('firstname', 'like', "%{$searchTerm}%")
                    ->orWhere('middlename', 'like', "%{$searchTerm}%")
                    ->orWhere('lastname', 'like', "%{$searchTerm}%");
            });
        }


        if ($request->filled('date')) {
            $clientsQuery->whereHas('subscriptions.payments', fn($q) => $q->whereDate('payment_date', $request->input('date')));
        }

        if ($request->filled('status')) {
            $clientsQuery->whereHas('subscriptions', fn($q) => $q->where('status', $request->input('status')));
        }

        if ($request->filled('plan')) {
            $planTitle = ucfirst($request->input('plan'));
            $clientsQuery->whereHas('subscriptions.planDetails', fn($q) => $q->where('plan_title', $planTitle));
        }

        $clients = $clientsQuery->paginate(10)->withQueryString();

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
            'breakdownGrandTotal',
            'basicPrice',
            'premiumPrice',
            'cardCounts',
            'isFiltered' // Pass the new variable to the view
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
        // Validate incoming data
        $validatedData = $request->validate([
            'paypal_order_id' => 'required|string',
            'plan_id' => 'required|exists:plans,plan_id',
        ]);

        $owner = Auth::guard('owner')->user();
        $plan = Plan::find($planId);

        if (!$plan) {
            return response()->json(['message' => 'Plan not found.'], 404);
        }

        if ($owner->activeSubscription()->exists()) {
            return response()->json(['message' => 'You already have an active subscription.'], 409);
        }

        try {
            // Optionally: Verify PayPal order via API here before creating subscription

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
                'payment_mode' => 'paypal',
                'payment_acc_number' => $validatedData['paypal_order_id'], // store PayPal order/transaction ID
                'payment_amount' => $plan->plan_price,
                'payment_date' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Payment successful!']);
        } catch (\Exception $e) {
            Log::error('Subscription or Payment creation failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to process subscription. Please try again.'], 500);
        }
    }
}
