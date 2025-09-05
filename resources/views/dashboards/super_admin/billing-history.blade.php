@extends('dashboards.super_admin.super_admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Billing History</h1>
    <div class="flex flex-col sm:flex-row gap-4 mb-8">
        <!-- Search -->
        <input type="text" id="search" placeholder="Search by owner name"
            autocomplete="off"
            class="w-full sm:w-[360px] p-3 pl-10 text-sm text-gray-800 border border-gray-300 rounded-full bg-gray-50 focus:ring-blue-600 focus:border-blue-600 shadow-md transition-all duration-200 ease-in-out"
            style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'currentColor\'><path fill-rule=\'evenodd\' d=\'M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.307l3.093 3.093a.75.75 0 11-1.06 1.06l-3.093-3.093A7 7 0 012 9z\' clip-rule=\'evenodd\'/></svg>'); background-repeat: no-repeat; background-position: left 0.75rem center; background-size: 1.25rem;" />


        <!-- Date Filter -->
        <div class="relative w-full sm:w-[180px]">
            <input type="date" id="dateFilter" name="dateFilter"
                class="appearance-none w-full p-3 pl-4 pr-4 text-sm text-gray-600 border border-gray-300 rounded-full bg-gray-50 focus:ring-blue-600 focus:border-blue-600 shadow-md transition-all duration-200 ease-in-out" />
        </div>
    </div>

    @if($clients->count())
    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-emerald-100 text-sm text-center text-gray-700 font-medium tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left  font-semibold  tracking-wider">Owner Name</th>
                    <th class="px-6 py-3   font-semibold  tracking-wider">Payment Date</th>
                    <th class="px-6 py-3   font-semibold   tracking-wider">Payment Mode</th>
                    <th class="px-6 py-3   font-semibold  tracking-wider">Subscription Plan</th>
                    <th class="px-6 py-3   font-semibold   tracking-wider">Subscription Status</th>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        function fetchBilling() {
            const query = $('#search').val();
            const date = $('#dateFilter').val();

            $.ajax({
                url: "{{ route('billing.search') }}",
                type: "GET",
                data: {
                    query,
                    date
                },
                success: function(clients) {
                    let tbody = '';

                    if (clients.length > 0) {
                        clients.forEach(owner => {
                            owner.subscriptions.forEach(subscription => {
                                // Ensure payments is an array
                                const paymentsArray = subscription.payments || [];
                                const payment = paymentsArray.length > 0 ? paymentsArray[0] : null;

                                const paymentDate = payment && payment.payment_date ? payment.payment_date : 'N/A';
                                const paymentMode = payment && payment.payment_mode ? payment.payment_mode : 'N/A';
                                const planTitle = subscription.plan_details?.plan_title ?? 'N/A';

                                tbody += `
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        ${owner.firstname} ${owner.middlename ?? ''} ${owner.lastname}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        ${paymentDate}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        ${paymentMode}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        ${planTitle}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                            subscription.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                        }">
                                            ${subscription.status}
                                        </span>
                                    </td>
                                </tr>
                            `;
                            });
                        });
                    } else {
                        tbody = `<tr><td colspan="5" class="text-center py-4 text-gray-500">No billing records found.</td></tr>`;
                    }

                    $('tbody').html(tbody);
                }
            });
        }

        $('#search').on('input', fetchBilling);
        $('#dateFilter').on('change', fetchBilling);
    });
</script>