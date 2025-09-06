@extends('dashboards.super_admin.super_admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Left Column -->
        <div class="lg:w-1/3 flex flex-col gap-6">
            <!-- Latest Bill Card -->
            <div class="bg-white shadow-md rounded-lg p-6 border-t-4 border-red-500">
                <h2 class="text-lg font-semibold text-red-700">Latest Billing</h2>

                @php
                $latestOwner = $clients->first();
                $latestSubscription = $latestOwner?->subscriptions->first();
                $latestPayment = $latestSubscription?->payments->first();
                $ownerInitials = $latestOwner
                ? strtoupper(substr($latestOwner->firstname,0,1).substr($latestOwner->lastname,0,1))
                : '';
                @endphp

                @if($latestOwner && $latestSubscription)
                <div class="flex items-center gap-4 mt-5">
                    <div class="w-12 h-12 bg-red-500 text-white rounded-full flex items-center justify-center font-bold text-lg">
                        {{ $ownerInitials }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">
                            {{ $latestOwner->firstname }} {{ $latestOwner->middlename ?? '' }} {{ $latestOwner->lastname }}
                        </p>
                        <p class="text-gray-500 text-sm">
                            {{ $latestSubscription->planDetails->plan_title ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 space-y-2 text-sm">
                    <p class="text-gray-700">Payment Date: <span class="font-medium">{{ $latestPayment?->payment_date ?? 'N/A' }}</span></p>
                    <p class="text-gray-700">Payment Mode: <span class="font-medium">{{ ucfirst($latestPayment?->payment_mode ?? 'N/A') }}</span></p>
                    <p class="text-gray-700">Amount: <span class="font-medium">₱{{ number_format($latestPayment?->payment_amount ?? 0, 2) }}</span></p>
                    <p class="text-gray-700">Status:
                        <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                {{ $latestSubscription->status == 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' }}">
                            {{ ucfirst($latestSubscription->status) }}
                        </span>
                    </p>
                </div>
                @else
                <p class="text-gray-500 mt-4">No latest billing available.</p>
                @endif
            </div>

            <!-- Subscription Revenue Card -->
            <div class="bg-white shadow-md rounded-lg p-6 border-t-4 border-orange-500">
                <h2 class="text-lg font-semibold text-orange-700">Subscription Revenue</h2>

                @php
                $basicRevenue = 0;
                $premiumRevenue = 0;
                foreach($clients as $owner) {
                foreach($owner->subscriptions as $subscription) {
                $payment = $subscription->payments->first();
                if($payment) {
                $plan = strtolower(trim($subscription->planDetails->plan_title ?? ''));
                if($plan === 'basic') $basicRevenue += $payment->payment_amount ?? 0;
                if($plan === 'premium') $premiumRevenue += $payment->payment_amount ?? 0;
                }
                }
                }
                $totalRevenue = $basicRevenue + $premiumRevenue;
                $basicPercent = $totalRevenue > 0 ? ($basicRevenue / $totalRevenue) * 100 : 0;
                $premiumPercent = $totalRevenue > 0 ? ($premiumRevenue / $totalRevenue) * 100 : 0;
                @endphp

                <!-- Basic Revenue -->
                <div class="mt-4">
                    <div class="flex justify-between text-sm font-medium text-gray-700">
                        <span>Basic</span>
                        <span>₱{{ number_format($basicRevenue, 2) }}</span>
                    </div>
                    <div class="w-full h-2 bg-orange-100 rounded-full mt-1">
                        <div class="h-2 bg-orange-300 rounded-full" style="width: {{ $basicPercent }}%;"></div>
                    </div>
                </div>

                <!-- Premium Revenue -->
                <div class="mt-3">
                    <div class="flex justify-between text-sm font-medium text-gray-700">
                        <span>Premium</span>
                        <span>₱{{ number_format($premiumRevenue, 2) }}</span>
                    </div>
                    <div class="w-full h-2 bg-red-100 rounded-full mt-1">
                        <div class="h-2 bg-red-300 rounded-full" style="width: {{ $premiumPercent }}%;"></div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="mt-4 pt-3 border-t border-gray-200 text-sm font-semibold flex justify-between text-gray-800">
                    <span>Total</span>
                    <span>₱{{ number_format($totalRevenue, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="lg:w-2/3 flex flex-col gap-6">
            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-4">
                <input type="text" id="search" placeholder="Search by owner name"
                    autocomplete="off"
                    class="w-full  sm:w-[360px] p-3 pl-10 text-sm text-gray-800 border border-gray-300 rounded-lg bg-white focus:ring-red-500 focus:border-red-500 shadow-sm transition-all duration-200 ease-in-out"
                    style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%236B7280\'><path fill-rule=\'evenodd\' d=\'M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.307l3.093 3.093a.75.75 0 11-1.06 1.06l-3.093-3.093A7 7 0 012 9z\' clip-rule=\'evenodd\'/></svg>'); background-repeat: no-repeat; background-position: left 0.75rem center; background-size: 1.25rem;" />
                
                <input type="date" id="dateFilter" name="dateFilter"
                    class="w-full  sm:w-[180px] p-3 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-gray-400 focus:border-gray-400" />
            </div>

            <!-- Responsive Billing Table -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <!-- Desktop Table -->
                <table class="min-w-full hidden md:table divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-100 text-gray-700 font-semibold uppercase tracking-wide">
                        <tr>
                            <th class="px-6 py-3 text-left">Owner Name</th>
                            <th class="px-6 py-3 text-center">Date</th>
                            <th class="px-6 py-3 text-center">Method</th>
                            <th class="px-6 py-3 text-center">Amount</th>
                            <th class="px-6 py-3 text-center">Plan</th>
                            <th class="px-6 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="billingTableBody" class="divide-y divide-gray-100">
                        @foreach($clients as $owner)
                        @foreach($owner->subscriptions as $subscription)
                        @php $payment = $subscription->payments->first(); @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $owner->firstname }} {{ $owner->middlename ?? '' }} {{ $owner->lastname }}</td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                {{ $payment?->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">{{ ucfirst($payment?->payment_mode ?? 'N/A') }}</td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">₱{{ number_format($payment?->payment_amount ?? 0, 2) }}</td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">{{ $subscription->planDetails->plan_title ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                            {{ $subscription->status == 'active' ? 'bg-green-100 text-green-700' : 'bg-red-200 text-red-700' }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>

                <!-- Mobile Cards -->
                <div id="billingCards" class="md:hidden divide-y divide-gray-100">
                    @foreach($clients as $owner)
                    @foreach($owner->subscriptions as $subscription)
                    @php $payment = $subscription->payments->first(); @endphp
                    <div class="p-4">
                        <p class="font-semibold text-gray-900">{{ $owner->firstname }} {{ $owner->lastname }}</p>
                        <p class="text-sm text-gray-600">Payment Date: {{ $payment?->payment_date ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600">Mode: {{ ucfirst($payment?->payment_mode ?? 'N/A') }}</p>
                        <p class="text-sm text-gray-600">Amount: ₱{{ number_format($payment?->payment_amount ?? 0, 2) }}</p>
                        <p class="text-sm text-gray-600">Plan: {{ $subscription->planDetails->plan_title ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600">Status:
                            <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                {{ $subscription->status == 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' }}">
                                {{ ucfirst($subscription->status) }}
                            </span>
                        </p>
                    </div>
                    @endforeach
                    @endforeach
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $clients->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search');
        const dateInput = document.getElementById('dateFilter');
        const tableBody = document.getElementById('billingTableBody');
        const cardContainer = document.getElementById('billingCards');
        const tableRows = Array.from(tableBody.getElementsByTagName('tr'));
        const cardItems = Array.from(cardContainer.getElementsByTagName('div'));

        function filterTable() {
            const query = searchInput.value.toLowerCase();
            const selectedDate = dateInput.value;

            // Filter desktop rows
            tableRows.forEach(row => {
                const ownerName = row.cells[0].textContent.toLowerCase();
                const paymentDate = row.cells[1].textContent.trim();
                const matchesName = ownerName.includes(query);
                const matchesDate = selectedDate ? paymentDate.startsWith(selectedDate) : true;
                row.style.display = (matchesName && matchesDate) ? '' : 'none';
            });

            // Filter mobile cards
            cardItems.forEach(card => {
                const text = card.textContent.toLowerCase();
                const matchesName = text.includes(query);
                const matchesDate = selectedDate ? text.includes(selectedDate) : true;
                card.style.display = (matchesName && matchesDate) ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterTable);
        dateInput.addEventListener('change', filterTable);
    });
</script>