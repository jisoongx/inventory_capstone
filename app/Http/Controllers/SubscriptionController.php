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
use App\Services\PayPalService;




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
        $cancelledCount = Subscription::where('status', 'cancelled')->count();

        return view('dashboards.super_admin.subscribers', compact(
            'clients',
            'activeCount',
            'expiredCount',
            'upcomingCount',
            'cancelledCount',
            'isFiltered'
        ));
    }


   
    public function create()
    {
        $plans = Plan::all();
        return view('subscription', compact('plans'));
    }




    // public function store(Request $request, $planId)
    // {
    //     $owner = Auth::guard('owner')->user();
    //     $plan = Plan::find($planId);

    //     // Check if plan exists
    //     if (!$plan) {
    //         return response()->json(['message' => 'Plan not found.'], 404);
    //     }

    //     $basicSubscription = $owner->subscription()
    //         ->where('plan_id', 3)
    //         ->first();

    //     if ($basicSubscription) {
    //         $basicSubscription->update([
    //             'status' => 'inactive',
    //         ]);
    //     }
    //         // Get the current active subscription
    //     $currentSubscription = $owner->activeSubscription()->first();

    //     // If there's a current active subscription and the plan is different, we need to handle it
    //     if ($currentSubscription) {
    //         // If the user is trying to upgrade to the same plan, return an alert
    //         if ($currentSubscription->plan_id == $plan->plan_id) {
    //             return response()->json(['message' => 'You are already subscribed to this plan.'], 409);
    //         }

    //         if ($currentSubscription->plan_id == 3) {
    //             $currentSubscription->update([
    //                 'status' => 'inactive'

    //             ]);
    //         } else {
    //             // Expire other plans
    //             $currentSubscription->update([
    //                 'status' => 'expired',
    //                 'subscription_end' => now(),
    //             ]);
    //         }
    //     }

    //     try {
    //         // If the plan price is greater than 0, validate PayPal order ID
    //         if ($plan->plan_price > 0) {
    //             $validatedData = $request->validate([
    //                 'paypal_order_id' => 'required|string',
    //                 'plan_id' => 'required|exists:plans,plan_id',
    //             ]);
    //         }


    //         // Create the new subscription with the new plan
    //         $subscription = Subscription::create([
    //             'owner_id' => $owner->owner_id,
    //             'plan_id' => $plan->plan_id,
    //             'subscription_start' => now(),
    //             'subscription_end' => $plan->plan_duration_months
    //                 ? now()->addMonths($plan->plan_duration_months)
    //                 : null, // no expiry for Basic
    //             'status' => 'active',
    //         ]);


    //         // Create the payment record
    //         Payment::create([
    //             'owner_id' => $owner->owner_id,
    //             'subscription_id' => $subscription->subscription_id,
    //             'payment_mode' => $plan->plan_price == 0 ? 'free' : 'paypal',
    //             'payment_acc_number' => $plan->plan_price == 0 ? '0' : $request->paypal_order_id,
    //             'payment_amount' => $plan->plan_price,
    //             'payment_date' => now(),
    //         ]);

    //         // Return success response
    //         return response()->json(['success' => true, 'message' => 'Subscription upgraded successfully!']);
    //     } catch (\Exception $e) {
    //         Log::error('Subscription or Payment creation failed: ' . $e->getMessage());
    //         return response()->json(['message' => 'Failed to process subscription. Please try again.'], 500);
    //     }
    // }

    // public function store(Request $request, $planId)
    // {
    //     $owner = Auth::guard('owner')->user();
    //     $plan = Plan::find($planId);

    //     // Check if plan exists
    //     if (!$plan) {
    //         return response()->json(['message' => 'Plan not found.'], 404);
    //     }

    //     // Get the current active subscription (including Basic or other plans)
    //     $currentSubscription = $owner->subscription()
    //         ->where('status', 'active')
    //         ->where(function ($q) {
    //             $q->whereNull('subscription_end')
    //                 ->orWhere('subscription_end', '>=', now());
    //         })
    //         ->orderByDesc('subscription_end')
    //         ->first();

    //     // Handle choosing Basic plan
    //     if ($planId == 3) { // Basic
    //         // Check if an old Basic subscription exists
    //         $oldBasic = $owner->subscription()
    //             ->where('plan_id', 3)
    //             ->first();

    //         if ($oldBasic) {
    //             // Mark old Basic as inactive
    //             $oldBasic->update(['status' => 'inactive']);
    //         } else {
    //             $currentSubscription->update([
    //                 'status' => 'expired',
    //                 'subscription_end' => now(),
    //             ]);
    //         }

    //         // If current subscription is Basic, mark it inactive
    //         if ($currentSubscription && $currentSubscription->plan_id == 3) {
    //             $currentSubscription->update(['status' => 'inactive']);
    //         }

    //         // Create new Basic subscription only if no active Basic exists
    //         if (!$oldBasic || $oldBasic->status != 'active') {
    //             $subscription = Subscription::create([
    //                 'owner_id' => $owner->owner_id,
    //                 'plan_id' => 3,
    //                 'subscription_start' => now(),
    //                 'subscription_end' => null, // Basic has no expiry
    //                 'status' => 'active',
    //             ]);

    //             // Create free payment record
    //             Payment::create([
    //                 'owner_id' => $owner->owner_id,
    //                 'subscription_id' => $subscription->subscription_id,
    //                 'payment_mode' => 'free',
    //                 'payment_acc_number' => '0',
    //                 'payment_amount' => 0,
    //                 'payment_date' => now(),
    //             ]);
    //         }

    //         return response()->json(['success' => true, 'message' => 'Switched to Basic plan successfully!']);
    //     }

    //     // Handle non-Basic plans (Standard, Premium, etc.)
    //     if ($currentSubscription) {
    //         if ($currentSubscription->plan_id == $plan->plan_id) {
    //             return response()->json(['message' => 'You are already subscribed to this plan.'], 409);
    //         }

    //         // If current is Basic → mark inactive, otherwise expire
    //         if ($currentSubscription->plan_id == 3) {
    //             $currentSubscription->update(['status' => 'inactive']);
    //         } else {
    //             $currentSubscription->update([
    //                 'status' => 'expired',
    //                 'subscription_end' => now(),
    //             ]);
    //         }
    //     }

    //     try {
    //         // Validate PayPal info if plan is paid
    //         if ($plan->plan_price > 0) {
    //             $request->validate([
    //                 'paypal_order_id' => 'required|string',
    //                 'plan_id' => 'required|exists:plans,plan_id',
    //             ]);
    //         }

    //         // Create new subscription
    //         $subscription = Subscription::create([
    //             'owner_id' => $owner->owner_id,
    //             'plan_id' => $plan->plan_id,
    //             'subscription_start' => now(),
    //             'subscription_end' => $plan->plan_duration_months
    //                 ? now()->addMonths($plan->plan_duration_months)
    //                 : null,
    //             'status' => 'active',
    //         ]);

    //         // Create payment record
    //         Payment::create([
    //             'owner_id' => $owner->owner_id,
    //             'subscription_id' => $subscription->subscription_id,
    //             'payment_mode' => $plan->plan_price == 0 ? 'free' : 'paypal',
    //             'payment_acc_number' => $plan->plan_price == 0 ? '0' : $request->paypal_order_id,
    //             'payment_amount' => $plan->plan_price,
    //             'payment_date' => now(),
    //         ]);

    //         return response()->json(['success' => true, 'message' => 'Subscription upgraded successfully!']);
    //     } catch (\Exception $e) {
    //         Log::error('Subscription or Payment creation failed: ' . $e->getMessage());
    //         return response()->json(['message' => 'Failed to process subscription. Please try again.'], 500);
    //     }
    // }

    public function store(Request $request, $planId)
    {
        $owner = Auth::guard('owner')->user();
        $plan = Plan::find($planId);

        // Check if plan exists
        if (!$plan) {
            return response()->json(['message' => 'Plan not found.'], 404);
        }

        // Get current active subscription (including Basic or other plans)
        $currentSubscription = $owner->subscription()
            ->whereIn('status', ['active', 'cancelled'])
            ->where(function ($q) {
                $q->whereNull('subscription_end')
                    ->orWhere('subscription_end', '>=', now());
            })
            ->orderByDesc('subscription_end')
            ->first();

        // Handle Basic plan
        if ($planId == 3) {
            if ($currentSubscription && $currentSubscription->plan_id != 3) {
                $currentSubscription->update([
                    'status' => 'expired',
                    'subscription_end' => now(),
                ]);
            }
            $oldBasic = $owner->subscription()
                ->where('plan_id', 3)
                ->orderByDesc('subscription_id')
                ->first();

            if ($oldBasic) {
                // Mark old Basic as inactive
                $oldBasic->update(['status' => 'inactive']);
            }

            // If current subscription is Basic, mark it inactive
            if ($currentSubscription && $currentSubscription->plan_id == 3) {
                $currentSubscription->update(['status' => 'inactive']);
            }

            // Create new Basic subscription only if no active Basic exists
            if (!$oldBasic || $oldBasic->status != 'active') {
                $subscription = Subscription::create([
                    'owner_id' => $owner->owner_id,
                    'plan_id' => 3,
                    'subscription_start' => now(),
                    'subscription_end' => null,
                    'status' => 'active',
                ]);

                // Only create payment if no past Basic exists
                if (!$oldBasic) {
                    Payment::create([
                        'owner_id' => $owner->owner_id,
                        'subscription_id' => $subscription->subscription_id,
                        'payment_mode' => 'free',
                        'payment_acc_number' => '0',
                        'payment_amount' => 0,
                        'payment_date' => now(),
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Switched to Basic plan successfully!']);
        }

        // Handle non-Basic plans
        if ($currentSubscription) {
            if ($currentSubscription->plan_id == $plan->plan_id) {
                return response()->json(['message' => 'You are already subscribed to this plan.'], 409);
            }

            // If current is Basic → mark inactive, otherwise expire
            if ($currentSubscription->plan_id == 3) {
                $currentSubscription->update(['status' => 'inactive']);
            } else {
                $currentSubscription->update([
                    'status' => 'expired',
                    'subscription_end' => now(),
                ]);
            }
        }

        try {
            // Validate PayPal info if plan is paid
            if ($plan->plan_price > 0) {
                $request->validate([
                    'paypal_order_id' => 'required|string',
                    'plan_id' => 'required|exists:plans,plan_id',
                ]);
            }

            // Create new subscription
            $subscription = Subscription::create([
                'owner_id' => $owner->owner_id,
                'plan_id' => $plan->plan_id,
                'subscription_start' => now(),
                'subscription_end' => $plan->plan_duration_months
                    ? now()->addMonths($plan->plan_duration_months)
                    : null,
                'status' => 'active',
            ]);

            // Create payment record
            Payment::create([
                'owner_id' => $owner->owner_id,
                'subscription_id' => $subscription->subscription_id,
                'payment_mode' => $plan->plan_price == 0 ? 'free' : 'paypal',
                'payment_acc_number' => $plan->plan_price == 0 ? '0' : $request->paypal_order_id,
                'payment_amount' => $plan->plan_price,
                'payment_date' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Subscription upgraded successfully!']);
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

        // Do not expire the old plan immediately
        // Just redirect to subscription selection page
        return redirect()->route('subscription.selection')
            ->with('info', 'Your current subscription remains active. Please choose a new plan to upgrade.');
    }



    // Make sure you have a Payment model

    public function cancel(Request $request, PayPalService $paypal)
    {
        $owner = auth()->guard('owner')->user();

        // Get latest paid subscription
        $subscription = DB::table('subscriptions')
            ->where('owner_id', $owner->owner_id)
            ->whereNotNull('subscription_end') // exclude free plan
            ->where('status', 'active')
            ->orderByDesc('subscription_end')
            ->first();

        if (!$subscription) {
            return response()->json(['error' => 'No active paid subscription found.'], 400);
        }

        // Get linked PayPal payment
        $payment = DB::table('payment')
            ->where('subscription_id', $subscription->subscription_id)
            ->where('payment_mode', 'paypal')
            ->orderByDesc('payment_date')
            ->first();

        if (!$payment || !$payment->payment_acc_number) {
            return response()->json(['error' => 'No active PayPal subscription payment found.'], 400);
        }

        // Cancel via PayPal
        $success = $paypal->cancelSubscription($payment->payment_acc_number, 'Canceled by user');

        if (!$success) {
            return response()->json(['error' => 'Failed to cancel PayPal subscription. Check API credentials & permissions.'], 500);
        }

        // Update subscription status
        DB::table('subscriptions')
            ->where('subscription_id', $subscription->subscription_id)
            ->update(['status' => 'cancelled']);

        return response()->json(['success' => 'Subscription cancelled successfully.']);
    }
}
