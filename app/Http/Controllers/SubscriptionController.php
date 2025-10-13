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
    public function subscribers(Request $request)
    {

        Subscription::where('status', 'active')
            ->where('subscription_end', '<', now())
            ->update(['status' => 'expired']);

        $status = $request->input('status', 'active');
        $planId = $request->input('plan');
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $range = $request->input('range');

        
        $query = Owner::query();


        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('store_name', 'like', "%{$search}%");
            });
        }

    
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

    
        $clients = $query->paginate(10)->withQueryString();

        $isFiltered = ($request->has('search') && $request->input('search') !== '')
            || ($request->has('plan') && $request->input('plan') !== '')
            || ($request->has('range') && $request->input('range') !== '')
            || ($request->has('start_date') && $request->input('start_date') !== '');

        if ($request->ajax()) {
            return view('dashboards.super_admin.subscribers', compact('clients', 'isFiltered'))->render();
        }
        
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


   
    public function create()
    {
        $plans = Plan::all();
        return view('subscription', compact('plans'));
    }


    public function store(Request $request, $planId)
    {
        
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
                'payment_acc_number' => $validatedData['paypal_order_id'], 
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
