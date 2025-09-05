<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Owner;

class BillingController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        $date = $request->input('date');

        $clients = Owner::with(['subscriptions.planDetails', 'subscriptions.payments'])
            // Remove status filtering to show all subscriptions
            ->when($query, function ($q) use ($query) {
                $q->where(function ($q2) use ($query) {
                    $q2->where('firstname', 'like', "%{$query}%")
                        ->orWhere('middlename', 'like', "%{$query}%")
                        ->orWhere('lastname', 'like', "%{$query}%");
                });
            })
            ->when($date, function ($q) use ($date) {
                $q->whereHas('subscriptions', function ($sub) use ($date) {
                    $sub->whereDate('created_on', $date); // Filter by payment/subscription creation date
                });
            })
            ->orderBy('created_on', 'desc')
            ->get();

        return response()->json($clients->toArray());
    }
}