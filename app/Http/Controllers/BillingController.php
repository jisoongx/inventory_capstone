<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        $date = $request->input('date');

        $clients = Owner::with(['subscriptions.planDetails', 'subscriptions.payments'])
    
            ->when($query, function ($q) use ($query) {
                $q->where(function ($q2) use ($query) {
                    $q2->where('firstname', 'like', "%{$query}%")
                        ->orWhere('middlename', 'like', "%{$query}%")
                        ->orWhere('lastname', 'like', "%{$query}%");
                });
            })
            ->when($date, function ($q) use ($date) {
                $q->whereHas('subscriptions', function ($sub) use ($date) {
                    $sub->whereDate('created_on', $date);
                });
            })
            ->orderBy('created_on', 'desc')
            ->get();

        return response()->json($clients->toArray());
    }

    public function billing(Request $request)
    {

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
            'standard' => $paymentsInPeriod->where('subscription.planDetails.plan_title', 'Standard')->sum('payment_amount'),
            'premium' => $paymentsInPeriod->where('subscription.planDetails.plan_title', 'Premium')->sum('payment_amount'),
        ];
        $totalRevenue = $revenue['standard'] + $revenue['premium'];
        $standardPercentage = $totalRevenue > 0 ? ($revenue['standard'] / $totalRevenue) * 100 : 0;
        $premiumPercentage = $totalRevenue > 0 ? ($revenue['premium'] / $totalRevenue) * 100 : 0;

        $subscriptionsInPeriod = Subscription::with('planDetails')
            ->when($startDate, fn($query) => $query->whereBetween('subscription_start', [$startDate, $endDate]))
            ->get();

        $cardCounts = [
            'standard' => $subscriptionsInPeriod->where('planDetails.plan_title', 'Standard')->count(),
            'premium' => $subscriptionsInPeriod->where('planDetails.plan_title', 'Premium')->count(),
        ];

        $plans = Plan::all();
        $standardPrice = $plans->firstWhere('plan_title', 'Standard')->plan_price ?? 0;
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
        $planStats = ['standard' => ['active' => 0, 'expired' => 0], 'premium' => ['active' => 0, 'expired' => 0]];
        $allSubscriptions = Subscription::with('planDetails')
            ->when($pd_startDate && $pd_endDate, fn($query) => $query->whereBetween('subscription_start', [$pd_startDate, $pd_endDate]))
            ->get();

        foreach ($allSubscriptions as $sub) {
            $planTitle = strtolower($sub->planDetails->plan_title ?? '');
            if (array_key_exists($planTitle, $planStats) && in_array($sub->status, ['active', 'expired'])) {
                $planStats[$planTitle][$sub->status]++;
            }
        }
        $planStats['standard']['total'] = $planStats['standard']['active'] + $planStats['standard']['expired'];
        $planStats['premium']['total'] = $planStats['premium']['active'] + $planStats['premium']['expired'];
        $totalActive = $planStats['standard']['active'] + $planStats['premium']['active'];
        $totalExpired = $planStats['standard']['expired'] + $planStats['premium']['expired'];
        $grandTotalSubs = $totalActive + $totalExpired;

        $customStart = $request->input('start_date');
        $customEnd = $request->input('end_date');
        $revStartDate = $customStart ? Carbon::parse($customStart)->startOfDay() : $startDate;
        $revEndDate = $customEnd ? Carbon::parse($customEnd)->endOfDay() : $endDate;

        $monthlyRevenueData = Payment::selectRaw("DATE_FORMAT(payment.payment_date, '%Y-%m') as month")
            ->selectRaw("SUM(CASE WHEN `plans`.`plan_title` = 'Standard' THEN `payment`.`payment_amount` ELSE 0 END) as standard_revenue")
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
                    'standard' => $item->standard_revenue,
                    'premium' => $item->premium_revenue,
                    'total' => $item->standard_revenue + $item->premium_revenue,
                ]
            ];
        })->toArray();

        $breakdownTotalStandard = array_sum(array_column($monthlyRevenue, 'standard'));
        $breakdownTotalPremium = array_sum(array_column($monthlyRevenue, 'premium'));
        $breakdownGrandTotal = array_sum(array_column($monthlyRevenue, 'total'));


        $clientsQuery = Owner::whereHas('subscriptions.payments')
            ->with([
                'subscriptions' => function ($q) {
                    $q->with(['planDetails', 'payments']);
                }
            ]);



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
            'standardPercentage',
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
            'breakdownTotalStandard',
            'breakdownTotalPremium',
            'breakdownGrandTotal',
            'standardPrice',
            'premiumPrice',
            'cardCounts',
            'isFiltered'
        ));
    }

    public function billingOwner()
    {
        // Assuming the logged-in user is an Owner
        $owner = Auth::guard('owner')->user();

        // Fetch all payments with subscription & plan details
        $payments = Payment::with([
            'subscription.planDetails'
        ])
            ->where('owner_id', $owner->owner_id)
            ->orderByDesc('payment_date')
            ->get();

        return view('dashboards.owner.billing-history2', compact('payments'));
    }
}