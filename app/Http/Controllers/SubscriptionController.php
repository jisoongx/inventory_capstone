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
       
        $clients = Owner::whereHas('subscriptions', function ($query) {
            // Filter the subscriptions to only include those with a 'paid' status.
            // $query->where('status', 'active');
        })
            // Use `with` to eager load the filtered subscriptions for each owner.
            // This prevents the N+1 query problem.
            ->with(['subscriptions' => function ($query) {
                // $query->where('status', 'active')
                //     ->with('planDetails');
            }])
            ->orderBy('created_on', 'asc') // Assuming the column is 'created_at' in the table
            ->paginate(10);

        return view('dashboards.super_admin.subscribers', compact('clients'));
    }

    public function showExpiredSubscribers()
    {
        $today = now()->startOfDay();
        $subscriptions = Subscription::where('subscription_end', '<=', $today)
            ->where('status', ['expired', 'active'])
            ->get();

        foreach ($subscriptions as $sub) {
            $sub->status = 'expired';
            $sub->save();

            $owner = Owner::find($sub->owner_id);
            if ($owner) {
                $owner->status = 'Deactivated';
                $owner->save();
            }
        }

        $clients = Owner::with([
            'subscriptions.planDetails',   // include subscription + plan
            'subscriptions.payments',      // include all payments under each subscription
        ])
            ->withCount('subscriptions')       // optional: count subs
            ->orderBy('created_on', 'desc')
            ->paginate(10);

        // Pass the collection of owners to the view, which can now display their names.
        return view('dashboards.super_admin.billing-history', compact('clients'));
    }


    public function sub_search(Request $request)
    {
        $query = $request->input('query');
        $plan = $request->input('plan');
        $status = $request->input('status');
        $date = $request->input('date');
        $clients = Owner::with(['subscription' => function ($q) {
            // Load all subscriptions with plan details
            $q->with('planDetails');
        }])
            ->whereHas('subscription') // ensure owner has at least one subscription (any status)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('store_name', 'like', "%{$query}%")
                        ->orWhere('firstname', 'like', "%{$query}%")
                        ->orWhere('middlename', 'like', "%{$query}%")
                        ->orWhere('lastname', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                });
            })
            ->when($plan, function ($q) use ($plan) {
                $q->whereHas('subscription', function ($sub) use ($plan) {
                    $sub->where('plan_id', $plan);
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->whereHas('subscription', function ($sub) use ($status) {
                    $sub->where('status', $status);
                });
            })
            ->when($date, function ($q) use ($date) {
                $q->whereHas('subscription', function ($sub) use ($date) {
                    $sub->whereDate('created_on', $date);
                });
            })
            ->orderBy('created_on', 'desc')
            ->get();


        return response()->json($clients);
    }

    public function updateSubStatus(Request $request, $owner_id)
    {

        $request->validate([
            'status' => 'required|in:paid,expired'
        ]);

        $subscription = Subscription::where('owner_id', $owner_id)->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found for this client.'
            ], 404);
        }

        $requestedStatus = $request->input('status');

        if ($requestedStatus === 'expired') {
            // Set the subscription's status to 'expired'
            $subscription->status = 'expired';

            // Set the subscription's expiry date to the current date and time
            $subscription->subscription_end = Carbon::now();

            $subscription->save();

            $owner = Owner::find($owner_id);
            if ($owner) {
                // Deactivate the owner if they exist
                $owner->status = 'Deactivated';
                $owner->save();
            }
        } elseif ($requestedStatus === 'paid') {
            $today = now()->startOfDay();
            $endDate = \Carbon\Carbon::parse($subscription->subscription_end)->startOfDay();

            if ($today->lte($endDate)) {
                $subscription->status = 'paid';
                $subscription->save();

                $owner = Owner::find($owner_id);
                if ($owner) {
                    $owner->status = 'Active';
                    $owner->save();
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot mark as paid. Subscription already expired.',
                ], 400);
            }
        }

        $user = Auth::guard('super_admin')->user();
        $ownerName = "{$owner->firstname} {$owner->lastname}";
        $subscriptionStatus = $owner->subscription?->status ?? 'Unknown';
        $description = "Updated client ({$ownerName}) subscription status to {$subscriptionStatus}";
        ActivityLogController::log($description, 'super_admin', $user, $request->ip());

        return response()->json([
            'success' => true,
            'message' => 'Subscription status updated successfully.',
            'new_status' => $subscription->status
        ]);
    }

    public function create()
    {
        $plans = Plan::all();
        return view('subscription', compact('plans'));
    }

    public function store(Request $request, $planId)
    {
        $owner = Auth::guard('owner')->user();
        $request->validate([
            'paymentMethod' => 'required|in:gcash,debit',
            'paymentAccNum' => 'nullable|string|max:20', // VALIDATE THE UNIFIED NAME
            'plan_id' => 'required|exists:plans,plan_id',
        ]);

        if ((int)$planId !== (int)$request->plan_id) {
            Log::error("Plan ID mismatch: Route ID {$planId}, Form ID {$request->plan_id}");
            return back()->with('error', 'Plan ID mismatch. Please try again.');
        }

        $plan = Plan::find($planId);

        if (!$plan) {
            return back()->with('error', 'Selected plan not found.');
        }

        if (is_null($plan->plan_price) || is_null($plan->plan_duration_months)) {
            Log::error("Plan ID {$planId} is missing plan_price or plan_duration_months.", ['plan' => $plan->toArray()]);
            return back()->with('error', 'Selected plan data is incomplete. Please contact support.')->withInput();
        }

        if ($owner->activeSubscription()->exists()) {
            return redirect()->route('owner.dashboard')->with('warning', 'You already have an active subscription.');
        }

        try {
            $subscription = Subscription::create([
                'owner_id' => $owner->owner_id,
                'plan_id' => $plan->plan_id,
                'subscription_start' => now(),
                'subscription_end' => now()->addMonths($plan->plan_duration_months),
                'status' => 'active',
            ]);

            $paymentAccNumber = $request->input('paymentAccNum');

            $paymentData = [
                'owner_id' => $owner->owner_id,
                'subscription_id' => $subscription->subscription_id, // ✅ Link payment to subscription
                'payment_mode' => $request->paymentMethod,
                'payment_acc_number' => $paymentAccNumber,
                'payment_amount' => $plan->plan_price,
                'payment_date' => now(),
            ];

            $payment = Payment::create($paymentData);

            $owner->update([
                'status' => 'Active'
            ]);

            return redirect()->route('subscription.progress');
        } catch (\Exception $e) {
            Log::error('Subscription or Payment creation failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Failed to process subscription. Please try again.')->withInput();
        }
    }

    public function progress()
    {
        $owner = Auth::guard('owner')->user();
        $subscription = $owner->latestSubscription()
            ->with('planDetails')
            ->orderBy('subscription_start', 'desc')
            ->first();

        return view('subscription_progress', compact('subscription'));
    }
}
