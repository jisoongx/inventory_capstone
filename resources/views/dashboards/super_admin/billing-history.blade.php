@extends('dashboards.super_admin.super_admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Billing History</h1>

    @if($clients->count())
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-sm text-center font-medium tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left  font-semibold text-gray-500 tracking-wider">Owner Name</th>
                        <th class="px-6 py-3   font-semibold text-gray-500 tracking-wider">Payment Date</th>
                        <th class="px-6 py-3   font-semibold text-gray-500  tracking-wider">Payment Mode</th>
                        <th class="px-6 py-3   font-semibold text-gray-500 tracking-wider">Subscription Plan</th>
                        <th class="px-6 py-3   font-semibold text-gray-500  tracking-wider">Subscription Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y text-sm divide-gray-200">
                    @foreach($clients as $owner)
                        @foreach($owner->subscriptions as $subscription)
                            @php
                                $payment = $subscription->payments; // should be only one payment
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $owner->firstname }} {{ $owner->middlename }} {{ $owner->lastname }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    {{ $payment?->payment_date ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    {{ ucfirst($payment?->payment_mode ?? 'N/A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    {{ $subscription->planDetails->plan_title ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($subscription->status)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $subscription->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $clients->links() }}
        </div>
    @else
        <p class="text-gray-500">No billing records found.</p>
    @endif
</div>
@endsection