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
use Illuminate\Support\Facades\DB;



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
                        $q->whereBetween('subscription_end', [Carbon::today(), Carbon::today()->addDays(3)]);
                        break;
                    case 'soon':
                        $q->whereBetween('subscription_end', [Carbon::today()->addDays(4), Carbon::today()->addDays(7)]);
                        break;
                    case 'later':
                        $q->whereBetween('subscription_end', [Carbon::today()->addDays(8), Carbon::today()->addDays(14)]);
                        break;
                }
            }
        });

    
        $query->with(['subscriptions' => function ($q) use ($status, $planId, $startDate, $range) {
            if ($status === 'upcoming') {
                $q->where('status', 'active')->whereBetween('subscription_end', [Carbon::today(), Carbon::today()->addDays(14)]);
            } else {
                $q->where('status', $status);
            }
            if ($startDate) $q->whereDate('subscription_start', $startDate);
            if ($planId) $q->where('plan_id', $planId);
            if ($status === 'upcoming' && $range) {
                switch ($range) {
                    case 'urgent':
                        $q->whereBetween('subscription_end', [Carbon::today(), Carbon::today()->addDays(3)]);
                        break;
                    case 'soon':
                        $q->whereBetween('subscription_end', [Carbon::today()->addDays(4), Carbon::today()->addDays(7)]);
                        break;
                    case 'later':
                        $q->whereBetween('subscription_end', [Carbon::today()->addDays(8), Carbon::today()->addDays(14)]);
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
        $upcomingCount = Subscription::where('status', 'active')->whereBetween('subscription_end', [now(), now()->addDays(14)])->count();

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
        $owner = Auth::guard('owner')->user();
        $plan = Plan::find($planId);

        if (!$plan) {
            return response()->json(['message' => 'Plan not found.'], 404);
        }

        if (strtolower($plan->plan_title) === 'basic') {
            $hasUsedBasic = Subscription::where('owner_id', $owner->owner_id)
                ->whereHas('planDetails', fn($q) => $q->where('plan_title', 'Basic'))
                ->exists();

            if ($hasUsedBasic) {
                // ✅ Custom 409 response — this should show in your JS alert
                return response()->json([
                    'message' => 'You have already used our one-time free Basic plan.'
                ], 409);
            }
        }

        // Check if already subscribed
        if ($owner->activeSubscription()->exists()) {
            return response()->json(['message' => 'You already have an active subscription.'], 409);
        }

        try {
            // ✅ If Basic plan (price 0) — skip PayPal validation
            if ($plan->plan_price > 0) {
                $validatedData = $request->validate([
                    'paypal_order_id' => 'required|string',
                    'plan_id' => 'required|exists:plans,plan_id',
                ]);
            }

            // Create subscription
            $subscription = Subscription::create([
                'owner_id' => $owner->owner_id,
                'plan_id' => $plan->plan_id,
                'subscription_start' => now(),
                'subscription_end' => now()->addMonths($plan->plan_duration_months),
                'status' => 'active',
            ]);

            // Create payment record
            Payment::create([
                'owner_id' => $owner->owner_id,
                'subscription_id' => $subscription->subscription_id,
                'payment_mode' => $plan->plan_price == 0 ? 'free trial' : 'paypal',
                'payment_acc_number' => $plan->plan_price == 0 ? '0' : $request->paypal_order_id,
                'payment_amount' => $plan->plan_price,
                'payment_date' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Subscription activated successfully!']);
        } catch (\Exception $e) {
            Log::error('Subscription or Payment creation failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to process subscription. Please try again.'], 500);
        }
    }

    public function upgrade()
    {
        $ownerId = Auth::guard('owner')->user()->owner_id;

        // Get current active subscription
        $current = Subscription::where('owner_id', $ownerId)
            ->where('status', 'active')
            ->first();

        // 1️⃣ Terminate current subscription (if any)
        if ($current) {
            $current->update([
                'status' => 'expired',
                'subscription_end' => now(),
            ]);
        }

        // 2️⃣ Redirect to subscription plans page (user chooses and pays again)
        return redirect()->route('subscription.selection')
            ->with('info', 'Your current subscription has been ended. Please choose a new plan to continue.');
    }
}
