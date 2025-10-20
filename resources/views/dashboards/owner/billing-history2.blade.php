@extends('dashboards.owner.owner')

@section('content')
<div class="max-w-7xl mx-auto px-1 sm:px-5 py-10">

    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">Billing History</h2>
        <a href="{{ route('owner.profile') }}"
            class="text-sm font-medium underline text-blue-600 hover:text-blue-800 transition">
            Back to Profile
        </a>
    </div>

    @if ($payments->isEmpty())
    <div class="text-center py-10 text-gray-500">
        <p class="text-lg">No billing history yet.</p>
        <p class="text-sm mt-2">Once you subscribe to a plan, your billing details will appear here.</p>
    </div>
    @else
    <div class="overflow-y-auto bg-white shadow-md rounded max-h-[420px]">
        <table class=" text-sm min-w-full divide-y divide-slate-100">
            <thead class=" text-sm bg-blue-100 sticky top-0 text-gray-700">
                <tr>
                    <th class="px-6 py-4 text-left  font-semibold uppercase tracking-wide">Plan</th>
                    <th class="px-6 py-4 text-center font-semibold uppercase tracking-wide">Start Date</th>
                    <th class="px-6 py-4 text-center  font-semibold uppercase tracking-wide">End Date</th>
                    <th class="px-6 py-4 text-center  font-semibold uppercase tracking-wide">Amount</th>
                    <th class="px-6 py-4 text-center font-semibold uppercase tracking-wide">Status</th>
                    <th class="px-6 py-4 text-center  font-semibold uppercase tracking-wide">Payment Date</th>
                    <th class="px-6 py-4 text-center  font-semibold uppercase tracking-wide">Payment Mode</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-50">
                @foreach ($payments as $payment)
                @php
                $subscription = $payment->subscription;
                $plan = $subscription?->planDetails;
                @endphp
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-6 py-4 text-gray-800 font-medium text-sm">
                        {{ $plan->plan_title ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 text-center text-gray-600 text-sm">
                        {{ $subscription?->subscription_start?->format('M d, Y') ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 text-center text-gray-600 text-sm">
                        {{ $subscription?->subscription_end?->format('M d, Y') ?? 'N/A' }}
                    </td>
                    {{-- Amount --}}
                    <td class="px-6 py-4 text-center text-gray-800 font-semibold text-sm">
                        ₱{{ number_format($payment->payment_amount ?? 0, 2) }}
                    </td>
                    {{-- Status --}}
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-medium 
                        {{ $subscription?->status === 'active' 
                            ? 'bg-green-100 text-green-700 border border-green-200'
                            : 'bg-red-100 text-red-700 border border-red-200' }}">
                            {{ ucfirst($subscription?->status ?? 'N/A') }}
                        </span>
                    </td>
                    {{-- Payment Date --}}
                    <td class="px-6 py-4 text-center text-gray-600 text-sm">
                        {{ $payment->payment_date?->format('M d, Y') ?? 'N/A' }}
                    </td>
                    {{-- Payment Mode --}}
                    <td class="px-6 py-4 text-center text-gray-600 text-sm">
                        {{ $payment->payment_mode ?? 'N/A' }}
                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection