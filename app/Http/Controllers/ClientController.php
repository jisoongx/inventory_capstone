<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Owner;
use App\Models\Subscription;

class ClientController extends Controller
{
    /**
     * Display a paginated list of clients in the view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $clients = Owner::with('subscription.planDetails')
            ->where('status', '!=', 'Declined') // Exclude Declined
            ->orderBy('created_on', 'desc')
            ->paginate(10);

        return view('dashboards.super_admin.client', compact('clients'));
    }

    public function subscribers()
    {
        $clients = Owner::with('subscription.planDetails')
            ->whereIn('status', ['Active', 'Deactivated'])           
            ->orderBy('created_on', 'desc')
            ->paginate(10);

        return view('dashboards.super_admin.subscribers', compact('clients'));
    }
   

    public function sub_search(Request $request)
    {
        $query = $request->input('query');
        $plan = $request->input('plan');       // e.g., 1 or 2
        $status = $request->input('status');   // e.g., 'paid' or 'expired'
        $date = $request->input('date');       // e.g., '2025-07'

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
                    $sub->whereRaw("DATE_FORMAT(subscription_start, '%Y-%m') = ?", [$date]);
                });
            })
            ->orderBy('created_on', 'desc')
            ->get();

        return response()->json($clients);
    }

    /**
     * Handle AJAX search requests for clients.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $status = $request->input('status');
        $date = $request->input('date'); // format: YYYY-MM
        $plan = $request->input('plan'); // plan_id (e.g., 1 for Basic, 2 for Premium)

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
                    $sub->whereYear('created_on', substr($date, 0, 4))
                        ->whereMonth('created_on', substr($date, 5, 2));
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

    /**
     * Filter clients by status via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function filterByStatus(Request $request)
    // {
    //     $status = $request->status;

    //     $query = Owner::with('subscription.planDetails')
    //         ->where('status', '!=', 'Declined'); // Always exclude Declined

    //     if ($status && $status !== 'All') {
    //         $query->where('status', $status);
    //     }

    //     $clients = $query->orderBy('created_on', 'desc')->get();

    //     return response()->json($clients);
    // }


    /**
     * Update the status of a specific client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $owner_id
     * @return \Illuminate\Http\JsonResponse
     */
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

        // Update subscription status based on owner status
        if ($owner->subscription) {
            $subscription = $owner->subscription;

            if ($owner->status === 'Deactivated') {
                $subscription->status = 'expired';
                $subscription->save();
            } elseif ($owner->status === 'Active') {
                // ✅ Use correct column name: subscription_end
                $today = now()->startOfDay();
                $endDate = \Carbon\Carbon::parse($subscription->subscription_end)->startOfDay();

                if ($today->lte($endDate)) {
                    $subscription->status = 'paid';
                    $subscription->save();
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot activate client. Subscription already expired.',
                    ], 400);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Client status updated successfully.',
            'new_status' => $owner->status
        ]);
    }


    public function updateSubStatus(Request $request, $owner_id)
    {
        $request->validate([
            'status' => 'required|in:paid,expired'
        ]);

        // Find the subscription based on the owner_id
        $subscription = Subscription::where('owner_id', $owner_id)->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found for this client.'
            ], 404);
        }

        $requestedStatus = $request->input('status');

        // Handle 'expired' status
        if ($requestedStatus === 'expired') {
            $subscription->status = 'expired';
            $subscription->save();

            $owner = Owner::find($owner_id);
            if ($owner) {
                $owner->status = 'Deactivated';
                $owner->save();
            }
        }

        // Handle 'paid' status
        elseif ($requestedStatus === 'paid') {
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

        return response()->json([
            'success' => true,
            'message' => 'Subscription status updated successfully.',
            'new_status' => $subscription->status
        ]);
    }
}