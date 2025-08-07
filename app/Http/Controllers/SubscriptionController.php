<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Owner;
use App\Models\Plan;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owner');
    }

    public function create()
    {
        $plans = Plan::all();
        return view('subscription', compact('plans'));
    }

    /**
     * Store a newly created subscription and associated payment in storage.
     * This method handles the form submission from the subscription selection page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $planId The ID of the plan being subscribed to.
     * @return \Illuminate\Http\RedirectResponse
     */
   

   public function store(Request $request, $planId)

    {
        // --- DEBUGGING START: See ALL incoming request data directly from the form ---
        // dd($request->all()); // <-- This line is now commented out as its debugging purpose is served.
        // --- DEBUGGING END ---

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
            // 1. Create the Subscription record
            $subscription = Subscription::create([
                'owner_id' => $owner->owner_id,
                'plan_id' => $plan->plan_id,
                'subscription_start' => now(),
                'subscription_end' => now()->addMonths($plan->plan_duration_months),
                'status' => 'Active', // This status is for the subscription itself
            ]);

            // Get the payment account number using the unified name
            $paymentAccNumber = $request->input('paymentAccNum');

            // Prepare data for Payment creation (without status and transaction_id)
            $paymentData = [
                'owner_id' => $owner->owner_id,
                'payment_mode' => $request->paymentMethod,
                'payment_acc_number' => $paymentAccNumber,
                'payment_amount' => $plan->plan_price,
                'payment_date' => now(),
            ];

            // --- DEBUGGING: Confirm data just before payment creation (after processing) ---
            Log::info('Attempting to create payment with data:', $paymentData);
            // dd($paymentData); // Uncomment this line if you need to see this specific array after dd($request->all())
            // --- DEBUGGING END ---

            // 2. Create the Payment record
            $payment = Payment::create($paymentData);

            Log::info('Payment record created successfully for owner ID: ' . $owner->owner_id);

            // Redirect to the new success page
            return redirect()->route('subscription.success'); // <-- CHANGED THIS LINE
        } catch (\Exception $e) {
            Log::error('Subscription or Payment creation failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Failed to process subscription. Please try again.')->withInput();
        }
    }

    /**
     * Display the subscription success page.
     *
     * @return \Illuminate\View\View
     */
    public function showSubscriptionSuccess() // <-- ADDED THIS NEW METHOD
    {
        return view('subscription_success');
    }

    public function show(Subscription $subscription)
    {
        if ($subscription->owner_id !== Auth::guard('owner')->id()) {
            abort(403, 'Unauthorized action.');
        }
        $subscription->load('payments', 'plan');
        return view('subscription', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        if ($subscription->owner_id !== Auth::guard('owner')->id()) {
            abort(403, 'Unauthorized action.');
        }
        $plans = Plan::all();
        return view('subscription', compact('subscription', 'plans'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        if ($subscription->owner_id !== Auth::guard('owner')->id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:Active,Cancelled,Expired',
            'plan_id' => 'required|exists:plans,plan_id',
        ]);

        try {
            $subscription->update($request->all());
            return redirect()->route('subscriptions.index')->with('success', 'Subscription updated successfully.');
        } catch (\Exception $e) {
            Log::error('Subscription update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update subscription. Please try again.')->withInput();
        }
    }

    public function destroy(Subscription $subscription)
    {
        if ($subscription->owner_id !== Auth::guard('owner')->id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $subscription->update(['status' => 'Cancelled', 'subscription_end' => now()]);
            return redirect()->route('subscriptions.index')->with('success', 'Subscription cancelled successfully.');
        } catch (\Exception $e) {
            Log::error('Subscription cancellation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to cancel subscription. Please try again.');
        }
    }
}
