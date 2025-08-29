<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Owner;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    
    public function showClients()
    {
        $clients = Owner::with('subscription.planDetails')
            ->where('status', '!=', 'Declined')
            ->orderBy('created_on', 'desc')
            ->paginate(10);

        return view('dashboards.super_admin.client', compact('clients'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $status = $request->input('status');
        $date = $request->input('date'); 
        $plan = $request->input('plan');

        $clients = Owner::with(['subscription.planDetails'])
            ->where('status', '!=', 'Declined')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('store_name', 'like', "%{$query}%")
                        ->orWhere('firstname', 'like', "%{$query}%")
                        ->orWhere('middlename', 'like', "%{$query}%")
                        ->orWhere('lastname', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($date, function ($q) use ($date) {
                $q->whereHas('subscription', function ($sub) use ($date) {
                    $sub->whereDate('created_on', $date);
                });
            })
            ->when($plan, function ($q) use ($plan) {
                $q->whereHas('subscription', function ($sub) use ($plan) {
                    $sub->where('plan_id', $plan);
                });
            })
            ->orderBy('created_on', 'desc')
            ->get();

        return response()->json($clients);
    }

    public function updateStatus(Request $request, $owner_id)
    {
        $request->validate([
            'status' => 'required|in:Active,Pending,Deactivated,Declined'
        ]);

        $owner = Owner::with('subscription')->find($owner_id);

        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found.'
            ], 404);
        }

        $owner->status = $request->input('status');
        $owner->save();

        if ($owner->subscription) {
            $subscription = $owner->subscription;

            if ($owner->status === 'Declined') {
                $subscription->status = 'Declined';
                $subscription->save();
            }
           else if ($owner->status === 'Deactivated') {
                $subscription->status = 'expired';
                $subscription->subscription_end = now();
                $subscription->save();
            } else if ($owner->status === 'Active') {
                // Always fetch latest subscription
                $subscription = $owner->latestSubscription()->first();

                if ($subscription) {
                    $newStart = now();
                    $subscription->subscription_start = $newStart;

                    // Adjust subscription_end depending on plan
                    if ($subscription->planDetails->plan_title === 'Basic') {
                        $subscription->subscription_end = $newStart->copy()->addMonths(6);
                    } elseif ($subscription->planDetails->plan_title === 'Premium') {
                        $subscription->subscription_end = $newStart->copy()->addYear();
                    }

                    $subscription->status = 'paid'; // mark as active/paid
                    $subscription->save();

                    Payment::where('owner_id', $owner->owner_id)
                        ->update(['payment_date' => $newStart]);
                }
            }


            // if ($subscription) {
            //     $today = now()->startOfDay();
            //     $endDate = \Carbon\Carbon::parse($subscription->subscription_end)->startOfDay();

            //     if ($today->lte($endDate)) {
            //         $subscription->status = 'paid';
            //         $subscription->save();
            //     } else {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Cannot activate client. Subscription already expired.',
            //         ], 400);
            //     }
            // }
        }

        $user = Auth::guard('super_admin')->user();
        $ownerName = "{$owner->firstname} {$owner->lastname}";
        $description = "Updated client ({$ownerName}) status to {$owner->status}";

        ActivityLogController::log($description,'super_admin',$user, $request->ip());

        return response()->json([
            'success' => true,
            'message' => 'Client status updated successfully.',
            'new_status' => $owner->status
        ]);
    }

}