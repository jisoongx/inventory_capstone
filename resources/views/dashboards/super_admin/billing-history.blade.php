@php
if (!function_exists('getBadgeClasses')) {
function getBadgeClasses($type, $value) {
$base = 'inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-md text-white';
switch (ucfirst(strtolower(trim($value)))) {
case 'Basic':
return $base . ' bg-gradient-to-r from-yellow-500 to-yellow-600';
case 'Standard':
return $base . ' bg-gradient-to-r from-orange-400 to-orange-500';
case 'Premium':
return $base . ' bg-gradient-to-r from-rose-500 to-rose-600';
default:
return $base . ' bg-gradient-to-r from-gray-400 to-gray-500';
}
}
}
@endphp



@extends('dashboards.super_admin.super_admin')

@section('content')
<div class="min-h-screen flex flex-col px-2 py-5">
    <div class="container mx-auto max-w-7xl space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Total Revenue Card --}}
            <div class="group p-5 rounded bg-white shadow-md border-t-4 border-green-400 flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-indigo-100 text-green-600">
                                <span class="material-symbols-outlined">payments</span>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-green-700 uppercase tracking-wide">Total Revenue</div>
                                <div class="text-xl font-bold text-gray-900 leading-none">₱{{ number_format($totalRevenue, 2) }}</div>
                            </div>
                        </div>
                        <form id="periodForm" action="{{ route('billing.history') }}" method="GET" class="relative -top-2 -right-2">
                            <select name="period" onchange="this.form.submit()" class="text-xs bg-slate-100 border-slate-200 text-slate-700 font-semibold rounded-lg py-1 px-2 focus:outline-none hover:bg-slate-200 transition-all">
                                <option value="all_time" @if($period=='all_time' ) selected @endif>All Time</option>
                                <option value="this_month" @if($period=='this_month' ) selected @endif>This Month</option>
                                <option value="last_month" @if($period=='last_month' ) selected @endif>Last Month</option>
                                <option value="this_year" @if($period=='this_year' ) selected @endif>This Year</option>
                                <option value="last_year" @if($period=='last_year' ) selected @endif>Last Year</option>
                            </select>
                        </form>
                    </div>
                </div>
                <div class="pt-4 mt-4 border-t border-slate-200 flex items-center justify-between gap-4">
                    <button id="showRevenueBreakdownBtn" class="text-sm font-semibold text-green-600 hover:text-green-800 flex items-center gap-1 transition-all">
                        Revenue Breakdown <span class="material-symbols-outlined text-base">arrow_forward</span>
                    </button>
                    <button id="showPlanDistributionBtn" class="text-sm font-semibold text-green-600 hover:text-green-800 flex items-center gap-1 transition-all">
                        Plan Distribution <span class="material-symbols-outlined text-base">arrow_forward</span>
                    </button>
                </div>
            </div>

            {{-- Premium Revenue Card --}}
            <div class="group p-5 rounded bg-white shadow-md border-t-4 border-rose-400">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-rose-100 text-rose-600">
                            <span class="material-symbols-outlined">diamond</span>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-rose-700 uppercase tracking-wide">Premium Revenue</div>
                            <div class="text-xl font-bold text-gray-900 leading-none">₱{{ number_format($revenue['premium'], 2) }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-semibold text-slate-500">Plan Price</div>
                        <div class="font-bold text-rose-600">₱{{ number_format($premiumPrice, 2) }}</div>
                    </div>
                </div>
                <p class="text-xs text-slate-500 font-medium mt-4 pt-3 border-t border-slate-200">{{ $cardCounts['premium'] }} subscriptions.</p>
            </div>

            {{-- Basic Revenue Card --}}
            <div class="group p-5 rounded bg-white shadow-md border-t-4 border-orange-400 ">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-orange-100 text-orange-600">
                            <span class="material-symbols-outlined">star</span>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-orange-700 uppercase tracking-wide">Standard Revenue</div>
                            <div class="text-xl font-bold text-gray-900 leading-none">₱{{ number_format($revenue['standard'], 2) }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-semibold text-slate-500">Plan Price</div>
                        <div class="font-bold text-orange-600">₱{{ number_format($standardPrice, 2) }}</div>
                    </div>
                </div>
                <p class="text-xs text-slate-500 font-medium mt-4 pt-3 border-t border-slate-200">{{ $cardCounts['standard'] }} subscriptions.</p>
            </div>
        </div>

        {{-- Main Content Section --}}
        <div id="billingRecordsContainer" class="space-y-6 hidden">
            <div id="filtersContainer">
                <div class="bg-white/95 backdrop-blur-lg rounded shadow-md border border-slate-200">
                    <div class="p-5">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-slate-900 text-sm">
                                <div class="flex items-center gap-2 text-slate-600"><span class="material-symbols-outlined text-lg">filter_list</span>Filters</div>
                            </h3>
                            <a href="{{ route('billing.history', ['view' => 'billing_history']) }}" class="filter-link bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all shadow-sm flex items-center gap-1"><span class="material-symbols-outlined text-xs">clear_all</span>Clear</a>
                        </div>
                        <form id="filterForm" action="{{ route('billing.history') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                            <input type="hidden" name="view" value="billing_history">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search owner..." class="w-full pl-9 pr-3 py-2.5 rounded-lg text-sm focus:outline-none bg-white border border-slate-200 focus:border-indigo-500 transition-all shadow-sm" />
                            </div>
                            <input type="date" name="date" value="{{ request('date') }}" class="px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 transition-all shadow-sm" />
                            <select name="status" class="px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 transition-all shadow-sm">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                            <select name="plan" class="px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 transition-all shadow-sm">
                                <option value="">All Plans</option>
                                <option value="standard" {{ request('plan') == 'standard' ? 'selected' : '' }}>Standard</option>
                                <option value="premium" {{ request('plan') == 'premium' ? 'selected' : '' }}>Premium</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Dynamic content will be loaded here via AJAX --}}
            <div id="billing-table-content">
                @php
                $filtersApplied = request('search') || request('date') || request('status') || request('plan');
                @endphp

                @if($filtersApplied)
                <div id="pagination-summary" class="p-4 rounded-lg bg-indigo-50 mb-5 border border-indigo-200 text-sm text-indigo-800">
                    Showing {{ $clients->total() }} record(s) matching your filters
                </div>
                @endif


                @if($clients->isEmpty())
                <div class="bg-white rounded-xl shadow-lg p-8 text-center border border-gray-100">
                    <div class="w-16 h-16 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-4"><span class="material-symbols-outlined text-slate-500 text-2xl">search_off</span></div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">No Results Found</h3>
                    <p class="text-sm text-slate-500 mb-4">No subscriptions match the current filters.</p>
                </div>
                @else
                <div class="bg-white/95 backdrop-blur-lg rounded-lg shadow-lg border border-slate-200 overflow-hidden">
                    <div class="px-5 py-3 bg-slate-50 border-b border-slate-200">
                        <h3 class="font-semibold text-slate-900 text-sm">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-indigo-600 text-lg">receipt_long</span>Billing Records</div>
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table id="billingTable" class="min-w-full">
                            <thead class="bg-slate-100 border-b border-slate-200 sticky top-0">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase">
                                        <div class="flex items-center gap-1"><span class="material-symbols-outlined text-base">person</span> Owner</div>
                                    </th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700 uppercase">Date</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700 uppercase">Method</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700 uppercase">Amount</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700 uppercase w-32">
                                        <div class="flex items-center justify-center gap-1"><span class="material-symbols-outlined text-base">workspace_premium</span> Plan</div>
                                    </th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700 uppercase w-24">
                                        <div class="flex items-center justify-center gap-1"><span class="material-symbols-outlined text-base">toggle_on</span> Status</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="billingTableBody" class="bg-white divide-y divide-slate-100">
                                @foreach ($clients as $owner)
                                @foreach ($owner->subscriptions->when(request('status'), function ($q) {
                                return $q->where('status', request('status'));
                                }) as $subscription)
                                @foreach ($subscription->payments as $payment)

                                @php
                                $planTitle = $subscription->planDetails->plan_title ?? 'N/A';
                                $status = $subscription->status ?? 'N/A';
                                $ownerName = $owner->firstname . ' ' . $owner->lastname;
                                $paymentDate = \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y');
                                $paymentMode = $payment->payment_mode ?? 'N/A';
                                $paymentAmount = number_format($payment->payment_amount ?? 0, 2);
                                @endphp
                                <tr class="transition-colors duration-200 hover:bg-blue-100">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-900 text-sm">{{ $ownerName }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-slate-700 text-sm font-medium">{{ $paymentDate }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="bg-slate-700 text-white px-2 py-1 rounded-md text-[10px] shadow-md font-semibold">{{ $paymentMode }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-green-600 text-sm">₱{{ $paymentAmount }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="{{ getBadgeClasses('plan', $planTitle) }}">
                                            <div class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-xs">
                                                    {{ strtolower($planTitle) === 'premium' ? 'diamond' : (strtolower($planTitle) === 'standard' ? 'star' : 'magic_button') }}
                                                </span>
                                                {{ $planTitle }}
                                            </div>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="w-[80px] inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-md {{ $status === 'active' ? 'bg-gradient-to-r from-green-500 to-green-600 text-white' : 'bg-gradient-to-r from-red-500 to-red-600 text-white' }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                @endforeach
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-200">
                        {{ $clients->withQueryString()->links('pagination::tailwind') }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div id="planDistributionContainer" class="hidden">
            <div class="bg-white/95 backdrop-blur-lg rounded-lg shadow-lg border border-slate-200 overflow-hidden">
                <div class="px-5 py-3 bg-slate-50 border-b border-slate-200 flex justify-between items-center flex-wrap gap-2">
                    <h3 class="font-semibold text-slate-900 text-sm">
                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-green-600 text-lg">pie_chart</span>Plan Distribution</div>
                    </h3>
                    <div class="flex items-center gap-2">
                        <form action="{{ url()->current() }}" method="GET" class="flex items-center gap-2 text-xs">
                            <input type="hidden" name="view" value="plan_distribution">
                            <input type="date" name="pd_start_date" value="{{ $pd_startDate }}" class="bg-white border-slate-300 rounded-md p-1.5 focus:outline-none" title="Filter by subscription start date">
                            <span class="text-slate-500">to</span>
                            <input type="date" name="pd_end_date" value="{{ $pd_endDate }}" class="bg-white border-slate-300 rounded-md p-1.5 focus:outline-none" title="Filter by subscription start date">
                            <button type="submit" class="bg-indigo-600 text-white font-semibold px-3 py-1.5 rounded-lg shadow-sm hover:bg-indigo-700">Apply</button>
                            <a href="#" id="clearPlanDateFilterBtn" class="bg-slate-200 text-slate-700 font-semibold px-3 py-1.5 rounded-lg shadow-sm hover:bg-slate-300">Clear</a>
                        </form>
                        <button class="back-to-billing-btn bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all shadow-sm flex items-center gap-1"><span class="material-symbols-outlined text-xs">arrow_back</span>Back</button>
                    </div>
                </div>
                <div class="overflow-x-auto p-2">
                    <table class="min-w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700 uppercase">Plan</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-slate-700 uppercase">Active</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-slate-700 uppercase">Expired</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-slate-700 uppercase">Total</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-slate-700 uppercase">%</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <tr>
                                <td class="px-6 py-3 font-medium text-slate-800">Standard</td>
                                <td class="px-6 py-3 text-center">{{$planStats['standard']['active']}}</td>
                                <td class="px-6 py-3 text-center">{{$planStats['standard']['expired']}}</td>
                                <td class="px-6 py-3 text-center font-semibold">{{$planStats['standard']['total']}}</td>
                                <td class="px-6 py-3 text-center">{{ $grandTotalSubs > 0 ? number_format(($planStats['standard']['total'] / $grandTotalSubs) * 100, 1) : 0 }}%</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-3 font-medium text-slate-800">Premium</td>
                                <td class="px-6 py-3 text-center">{{$planStats['premium']['active']}}</td>
                                <td class="px-6 py-3 text-center">{{$planStats['premium']['expired']}}</td>
                                <td class="px-6 py-3 text-center font-semibold">{{$planStats['premium']['total']}}</td>
                                <td class="px-6 py-3 text-center">{{ $grandTotalSubs > 0 ? number_format(($planStats['premium']['total'] / $grandTotalSubs) * 100, 1) : 0 }}%</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold">
                            <tr class="text-slate-900">
                                <td class="px-6 py-3 text-left text-sm uppercase">Total</td>
                                <td class="px-6 py-3 text-center text-sm">{{$totalActive}}</td>
                                <td class="px-6 py-3 text-center text-sm">{{$totalExpired}}</td>
                                <td class="px-6 py-3 text-center text-sm">{{$grandTotalSubs}}</td>
                                <td class="px-6 py-3 text-center text-sm">100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div id="revenueBreakdownContainer" class="hidden">
            <div class="bg-white/95 backdrop-blur-lg rounded-lg shadow-lg border border-slate-200 overflow-hidden">
                <div class="px-5 py-3 bg-slate-50 border-b border-slate-200 flex justify-between items-center flex-wrap gap-2">
                    <h3 class="font-semibold text-slate-900 text-sm">
                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-blue-600 text-lg">paid</span>Revenue Breakdown</div>
                    </h3>
                    <div class="flex items-center gap-2">
                        <form action="{{ url()->current() }}" method="GET" class="flex items-center gap-2 text-xs">
                            <input type="hidden" name="view" value="revenue_breakdown">
                            <input type="date" name="start_date" value="{{ $customStart }}" class="bg-white border-slate-300 rounded-md p-1.5 focus-outline-none">
                            <span class="text-slate-500">to</span>
                            <input type="date" name="end_date" value="{{ $customEnd }}" class="bg-white border-slate-300 rounded-md p-1.5 focus-outline-none">
                            <button type="submit" class="bg-indigo-600 text-white font-semibold px-3 py-1.5 rounded-lg shadow-sm hover:bg-indigo-700">Apply</button>
                            <a href="#" id="clearDateFilterBtn" class="bg-slate-200 text-slate-700 font-semibold px-3 py-1.5 rounded-lg shadow-sm hover:bg-slate-300">Clear</a>
                        </form>
                        <button class="back-to-billing-btn bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all shadow-sm flex items-center gap-1"><span class="material-symbols-outlined text-xs">arrow_back</span>Back</button>
                    </div>
                </div>

                <div class="overflow-auto max-h-[70vh] p-2">
                    <table class="min-w-full">
                        <thead class="sticky top-0 z-10 bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700 uppercase">Month</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-slate-700 uppercase">Standard Revenue</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-slate-700 uppercase">Premium Revenue</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-slate-700 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            @forelse($monthlyRevenue as $month => $revenues)
                            <tr>
                                <td class="px-6 py-3 font-medium text-slate-800">{{ \Carbon\Carbon::parse($month)->format('M Y') }}</td>
                                <td class="px-6 py-3 text-center">₱{{number_format($revenues['standard'], 2)}}</td>
                                <td class="px-6 py-3 text-center">₱{{number_format($revenues['premium'], 2)}}</td>
                                <td class="px-6 py-3 text-center font-semibold text-slate-900">₱{{number_format($revenues['total'], 2)}}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-8 text-slate-500">No revenue data for the selected period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="sticky bottom-0 z-10 bg-slate-50 border-t-2 border-slate-200">
                            <tr class="font-bold">
                                <td class="px-6 py-3 text-left text-sm text-slate-900 uppercase">Total</td>
                                <td class="px-6 py-3 text-center text-sm text-slate-900">₱{{ number_format($breakdownTotalStandard, 2) }}</td>
                                <td class="px-6 py-3 text-center text-sm text-slate-900">₱{{ number_format($breakdownTotalPremium, 2) }}</td>
                                <td class="px-6 py-3 text-center text-sm text-indigo-700">₱{{ number_format($breakdownGrandTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const contentContainer = document.getElementById('billing-table-content');
        const filterForm = document.getElementById('filterForm');
        let debounceTimeout;

        // ---------- FETCH FUNCTION ----------
        const fetchContent = async (url, pushState = true) => {
            try {
                document.body.style.cursor = 'wait';

                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const html = await response.text();

                const parser = new DOMParser();
                const newDoc = parser.parseFromString(html, 'text/html');

                // Detect which view we're in
                const urlParams = new URL(url).searchParams;
                const view = urlParams.get('view') || 'billing_history';

                let containerId;
                if (view === 'plan_distribution') containerId = 'planDistributionContainer';
                else if (view === 'revenue_breakdown') containerId = 'revenueBreakdownContainer';
                else containerId = 'billing-table-content';

                const container = document.getElementById(containerId);
                const newContent = newDoc.getElementById(containerId).innerHTML;

                container.innerHTML = newContent;
                initFiltersAndButtons();

                if (pushState) history.pushState({}, '', url);
            } catch (error) {
                console.error('Error fetching content:', error);
            } finally {
                document.body.style.cursor = 'default';
            }
        };

        // ---------- FILTER FORM ----------
        if (filterForm) {
            const handleFormChange = () => {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => {
                    const formData = new FormData(filterForm);
                    const url = new URL(filterForm.action);
                    const params = new URLSearchParams();

                    formData.forEach((value, key) => {
                        if (value) params.set(key, value);
                    });

                    url.search = params.toString();
                    fetchContent(url.toString());
                }, 300);
            };

            filterForm.querySelectorAll('input, select').forEach(el => {
                el.addEventListener('change', handleFormChange);
                el.addEventListener('input', handleFormChange);
            });
        }

        // ---------- PAGINATION ----------
        document.addEventListener('click', e => {
            const paginationLink = e.target.closest('nav[role="navigation"] a');
            if (paginationLink) {
                e.preventDefault();

                const url = new URL(paginationLink.href);
                const page = url.searchParams.get('page');

                const formParams = new URLSearchParams(new FormData(filterForm));
                if (page) formParams.set('page', page);

                const newUrl = new URL(filterForm.action);
                newUrl.search = formParams.toString();

                fetchContent(newUrl.toString());
            }
        });

        // ---------- CLEAR FILTER LINK ----------
        const clearFilterLink = document.querySelector('.filter-link');
        if (clearFilterLink) {
            clearFilterLink.addEventListener('click', e => {
                e.preventDefault();
                filterForm.reset();
                fetchContent(clearFilterLink.href);
            });
        }

        // ---------- HANDLE BACK/FORWARD ----------
        window.addEventListener('popstate', () => {
            fetchContent(document.location.href, false);
        });

        // ---------- VIEW SWITCHING ----------
        const elements = {
            billingRecordsContainer: document.getElementById('billingRecordsContainer'),
            planDistributionContainer: document.getElementById('planDistributionContainer'),
            revenueBreakdownContainer: document.getElementById('revenueBreakdownContainer'),
            showRevenueBtn: document.getElementById('showRevenueBreakdownBtn'),
            showPlanBtn: document.getElementById('showPlanDistributionBtn')
        };
        const allViews = [elements.billingRecordsContainer, elements.planDistributionContainer, elements.revenueBreakdownContainer];

        function showView(viewToShow) {
            allViews.forEach(view => view && (view.style.display = 'none'));
            if (viewToShow) viewToShow.style.display = 'block';
        }

        if (elements.showRevenueBtn) {
            elements.showRevenueBtn.addEventListener('click', e => {
                e.preventDefault();
                showView(elements.revenueBreakdownContainer);
            });
        }

        if (elements.showPlanBtn) {
            elements.showPlanBtn.addEventListener('click', e => {
                e.preventDefault();
                showView(elements.planDistributionContainer);
            });
        }

        // ---------- INIT ALL FILTERS & BUTTONS ----------
        function initFiltersAndButtons() {
            ['planDistributionContainer', 'revenueBreakdownContainer'].forEach(id => {
                const container = document.getElementById(id);
                if (!container) return;

                const form = container.querySelector('form');
                if (form) {
                    const clearBtn = container.querySelector('a[id^="clear"]');

                    form.onsubmit = e => {
                        e.preventDefault();

                        const formData = new FormData(form);
                        const params = new URLSearchParams();
                        formData.forEach((value, key) => {
                            if (value) params.set(key, value);
                        });

                        params.set('view', id === 'planDistributionContainer' ? 'plan_distribution' : 'revenue_breakdown');
                        fetchContent(`${form.action}?${params.toString()}`);
                    };

                    if (clearBtn) {
                        clearBtn.onclick = e => {
                            e.preventDefault();
                            form.reset();
                            form.querySelectorAll('input[type="date"]').forEach(input => input.value = '');
                            const params = new URLSearchParams();
                            params.set('view', id === 'planDistributionContainer' ? 'plan_distribution' : 'revenue_breakdown');
                            fetchContent(`${form.action}?${params.toString()}`);
                        };
                    }
                }

                // Back buttons
                container.querySelectorAll('.back-to-billing-btn').forEach(btn => {
                    btn.onclick = e => {
                        e.preventDefault();
                        showView(elements.billingRecordsContainer);
                    };
                });
            });
        }

        // ---------- INITIAL VIEW ----------
        const urlParams = new URLSearchParams(window.location.search);
        const currentView = urlParams.get('view');
        if (currentView === 'plan_distribution') {
            showView(elements.planDistributionContainer);
        } else if (currentView === 'revenue_breakdown') {
            showView(elements.revenueBreakdownContainer);
        } else {
            showView(elements.billingRecordsContainer);
        }

        // init on first load
        initFiltersAndButtons();
    });
</script>


@endsection