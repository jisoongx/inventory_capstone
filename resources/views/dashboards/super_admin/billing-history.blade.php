@extends('dashboards.super_admin.super_admin')

@section('content')
{{-- All calculations are now handled by the `billing` method in your controller. --}}
{{-- The large @php block is no longer needed here. --}}

<div class="min-h-screen flex flex-col px-2 py-6">
    <div class="container mx-auto max-w-7xl space-y-6">

        {{-- Stat Cards Grid (Top Row) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Latest Payment Card --}}
            <div class="bg-white/95 backdrop-blur-lg rounded-xl shadow-md border border-slate-200 overflow-hidden">
                <div class="bg-emerald-500 px-5 py-3 text-white font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">receipt</span>
                    <h3>Latest Payment</h3>
                </div>
                <div class="p-4">
                    @if($latest)
                    <div class="flex items-center gap-4 mb-3">
                        <div class="w-10 h-10 bg-slate-200 text-slate-700 rounded-full flex items-center justify-center font-bold text-sm">{{ strtoupper(substr($latest['owner']->firstname, 0, 1) . substr($latest['owner']->lastname, 0, 1)) }}</div>
                        <div class="flex-1">
                            <p class="font-semibold text-slate-900 text-sm">{{ $latest['owner']->firstname }} {{ $latest['owner']->lastname }}</p>
                            <span class="text-slate-500 font-medium text-[10px] flex items-center gap-1"><span class="material-symbols-outlined text-xs">{{ strtolower($latest['sub']->planDetails->plan_title ?? '') === 'premium' ? 'diamond' : 'star' }}</span>{{ $latest['sub']->planDetails->plan_title ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between items-center py-2 px-3 bg-slate-50 rounded-lg"><span class="text-slate-600 font-medium">Date</span><span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($latest['payment']->payment_date)->format('M j, Y') }}</span></div>
                        <div class="flex justify-between items-center py-2 px-3 bg-slate-50 rounded-lg"><span class="text-slate-600 font-medium">Method</span><span class="bg-slate-700 text-white px-2 py-1 rounded-md text-[10px] shadow-sm font-semibold">{{ ucfirst($latest['payment']->payment_mode) }}</span></div>
                        <div class="flex justify-between items-center py-2 px-3 bg-slate-50 rounded-lg"><span class="text-slate-600 font-medium">Amount</span><span class="font-bold text-slate-700">₱{{ number_format($latest['payment']->payment_amount, 2) }}</span></div>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <div class="w-12 h-12 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-3"><span class="material-symbols-outlined text-slate-500 text-xl">receipt</span></div>
                        <p class="text-slate-500 font-medium text-sm">No payments yet</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Subscription Revenue Card --}}
            <div class="lg:col-span-2 bg-white/95 backdrop-blur-lg rounded-xl shadow-md border border-slate-200 overflow-hidden">
                <div class="bg-red-500 px-5 py-3 text-white font-semibold flex items-center justify-between">
                    <div class="flex items-center gap-2"><span class="material-symbols-outlined text-base">analytics</span>
                        <h3>Subscription Revenue</h3>
                    </div>
                    <form action="{{ route('billing.history') }}" method="GET">
                        <select name="period" onchange="this.form.submit()" class="text-sm bg-red-700/50 border-red-500 text-white rounded-md py-1 px-2 focus:outline-none">
                            <option value="all_time" @if($period=='all_time' ) selected @endif>All Time</option>
                            <option value="this_month" @if($period=='this_month' ) selected @endif>This Month</option>
                            <option value="last_month" @if($period=='last_month' ) selected @endif>Last Month</option>
                            <option value="this_year" @if($period=='this_year' ) selected @endif>This Year</option>
                            <option value="last_year" @if($period=='last_year' ) selected @endif>Last Year</option>
                        </select>
                    </form>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div class="space-y-3 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-center mb-1"><span class="font-semibold text-slate-800">Basic </span><span class="font-medium text-orange-700">₱{{ number_format($revenue['basic'], 2) }}</span></div>
                                <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-orange-500 rounded-full" style="width: {{ $basicPercentage }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-1"><span class="font-semibold text-slate-800">Premium</span><span class="font-medium text-rose-700">₱{{ number_format($revenue['premium'], 2) }}</span></div>
                                <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-red-500 rounded-full" style="width: {{ $premiumPercentage }}%"></div>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-slate-200">
                                <div class="bg-indigo-50 rounded-lg p-3 border border-indigo-200">
                                    <div class="flex justify-between items-center"><span class="font-semibold text-slate-800">Total Revenue</span><span class="font-bold text-slate-700">₱{{ number_format($totalRevenue, 2) }}</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col justify-center space-y-3 pt-3 border-t md:border-t-0 md:border-l border-slate-200 md:pl-6 mt-3 md:mt-0">
                            <button id="showRevenueBreakdownBtn" class="group flex items-center text-left gap-4 p-3 rounded-lg hover:bg-slate-100 transition-all">
                                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110"><span class="material-symbols-outlined">paid</span></div>
                                <div><strong class="font-semibold text-slate-800 text-sm">Revenue Breakdown</strong>
                                    <p class="text-xs text-slate-500">View detailed payment reports.</p>
                                </div>
                            </button>
                            <button id="showPlanDistributionBtn" class="group flex items-center text-left gap-4 p-3 rounded-lg hover:bg-slate-100 transition-all">
                                <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110"><span class="material-symbols-outlined">pie_chart</span></div>
                                <div><strong class="font-semibold text-slate-800 text-sm">Plan Distribution</strong>
                                    <p class="text-xs text-slate-500">Analyze Basic vs. Premium stats.</p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Section --}}

        <div id="billingRecordsContainer" class="space-y-6 hidden">
            <div id="filtersContainer">
                <div class="bg-white/95 backdrop-blur-lg rounded-xl shadow-md border border-slate-200">
                    <div class="p-5">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-slate-900 text-sm">
                                <div class="flex items-center gap-2 text-slate-600"><span class="material-symbols-outlined text-lg">filter_list</span>Filters</div>
                            </h3>
                            <button id="clearFilters" class="bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all shadow-sm flex items-center gap-1"><span class="material-symbols-outlined text-xs">clear_all</span>Clear</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="relative"><span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span><input type="text" id="search" placeholder="Search owner..." class="w-full pl-9 pr-3 py-2.5 rounded-lg text-sm focus:outline-none bg-white border border-slate-200 focus:border-indigo-500 transition-all shadow-sm" /></div>
                            <input type="date" id="dateFilter" class="px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 transition-all shadow-sm" />
                            <select id="statusFilter" class="px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 transition-all shadow-sm">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                            </select>
                            <select id="planFilter" class="px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 transition-all shadow-sm">
                                <option value="">All Plans</option>
                                <option value="basic">Basic</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white/95 backdrop-blur-lg rounded-xl shadow-lg border border-slate-200 overflow-hidden">
                <div class="px-5 py-3 bg-slate-50 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-900 text-sm">
                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-indigo-600 text-lg">receipt_long</span>Billing Records <span id="recordCount" class="text-slate-500 font-normal"></span></div>
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table id="billingTable" class="min-w-full">
                        <thead class="bg-slate-50">
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
                        <tbody class="bg-white divide-y divide-slate-100">
                            @forelse($clients as $owner)
                            @if($payment = $owner->subscriptions->flatMap->payments->sortByDesc('payment_date')->first())
                            <tr class="billing-record transition-all duration-250 hover:bg-indigo-50/50" data-date="{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}" data-status="{{ $payment->subscription->status }}" data-plan="{{ strtolower($payment->subscription->planDetails->plan_title ?? '') }}" data-search-text="{{ strtolower($owner->firstname . ' ' . $owner->lastname) }}">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900 text-sm">{{ $owner->firstname }} {{ $owner->lastname }}</div>
                                </td>
                                <td class="px-6 py-4 text-center text-slate-700 text-sm font-medium">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M j, Y') }}</td>
                                <td class="px-6 py-4 text-center"><span class="bg-slate-700 text-white px-2 py-1 rounded-md text-[10px] shadow-md font-semibold">{{ ucfirst($payment->payment_mode) }}</span></td>
                                <td class="px-6 py-4 text-center font-bold text-green-600 text-sm">₱{{ number_format($payment->payment_amount, 2) }}</td>
                                <td class="px-6 py-4 text-center"><span class="w-[100px] inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-md {{ strtolower($payment->subscription->planDetails->plan_title ?? '') === 'basic' ? 'bg-gradient-to-r from-orange-400 to-orange-500 text-white' : 'bg-gradient-to-r from-rose-500 to-rose-600 text-white' }}">
                                        <div class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">{{ strtolower($payment->subscription->planDetails->plan_title ?? '') === 'premium' ? 'diamond' : 'star' }}</span>{{ $payment->subscription->planDetails->plan_title ?? 'N/A' }}</div>
                                    </span></td>
                                <td class="px-6 py-4 text-center"><span class="w-[80px] inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-md {{ $payment->subscription->status == 'active' ? 'bg-gradient-to-r from-green-500 to-green-600 text-white' : 'bg-gradient-to-r from-red-500 to-red-600 text-white' }}">{{ ucfirst($payment->subscription->status) }}</span></td>
                            </tr>
                            @endif
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-slate-500">No billing records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="noResults" class="hidden p-12 text-center">
                    <div class="w-20 h-20 mx-auto bg-blue-50 rounded-full flex items-center justify-center mb-6"><span class="material-symbols-outlined text-slate-400 text-3xl">search_off</span></div>
                    <h3 class="text-sm font-semibold text-slate-600 mb-3">No matching records found</h3>
                </div>
                <div class="px-6 py-4 border-t border-slate-200">{{ $clients->withQueryString()->links() }}</div>
            </div>
        </div>

        <div id="planDistributionContainer" class="hidden">
            <div class="bg-white/95 backdrop-blur-lg rounded-xl shadow-lg border border-slate-200 overflow-hidden">
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
                                <td class="px-6 py-3 font-medium text-slate-800">Basic</td>
                                <td class="px-6 py-3 text-center">{{$planStats['basic']['active']}}</td>
                                <td class="px-6 py-3 text-center">{{$planStats['basic']['expired']}}</td>
                                <td class="px-6 py-3 text-center font-semibold">{{$planStats['basic']['total']}}</td>
                                <td class="px-6 py-3 text-center">{{ $grandTotalSubs > 0 ? number_format(($planStats['basic']['total'] / $grandTotalSubs) * 100, 1) : 0 }}%</td>
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
            <div class="bg-white/95 backdrop-blur-lg rounded-xl shadow-lg border border-slate-200 overflow-hidden">
                <div class="px-5 py-3 bg-slate-50 border-b border-slate-200 flex justify-between items-center flex-wrap gap-2">
                    <h3 class="font-semibold text-slate-900 text-sm">
                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-blue-600 text-lg">paid</span>Revenue Breakdown</div>
                    </h3>
                    <div class="flex items-center gap-2">
                        <form action="{{ url()->current() }}" method="GET" class="flex items-center gap-2 text-xs">
                            <input type="hidden" name="view" value="revenue_breakdown">
                            <input type="date" name="start_date" value="{{ $customStart }}" class="bg-white border-slate-300 rounded-md p-1.5 focus:outline-none">
                            <span class="text-slate-500">to</span>
                            <input type="date" name="end_date" value="{{ $customEnd }}" class="bg-white border-slate-300 rounded-md p-1.5 focus:outline-none">
                            <button type="submit" class="bg-indigo-600 text-white font-semibold px-3 py-1.5 rounded-lg shadow-sm hover:bg-indigo-700">Apply</button>
                            <a href="#" id="clearDateFilterBtn" class="bg-slate-200 text-slate-700 font-semibold px-3 py-1.5 rounded-lg shadow-sm hover:bg-slate-300">Clear</a>
                        </form>
                        <button class="back-to-billing-btn bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all shadow-sm flex items-center gap-1"><span class="material-symbols-outlined text-xs">arrow_back</span>Back</button>
                    </div>
                </div>
                <div class="overflow-x-auto p-2">
                    <table class="min-w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700 uppercase">Month</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-slate-700 uppercase">Basic Revenue</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-slate-700 uppercase">Premium Revenue</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-slate-700 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            @forelse($monthlyRevenue as $month => $revenues)
                            <tr>
                                <td class="px-6 py-3 font-medium text-slate-800">{{ \Carbon\Carbon::parse($month)->format('M Y') }}</td>
                                <td class="px-6 py-3 text-center">₱{{number_format($revenues['basic'], 2)}}</td>
                                <td class="px-6 py-3 text-center">₱{{number_format($revenues['premium'], 2)}}</td>
                                <td class="px-6 py-3 text-center font-semibold text-slate-900">₱{{number_format($revenues['total'], 2)}}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-8 text-slate-500">No revenue data for the selected period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                            <tr class="font-bold">
                                <td class="px-6 py-3 text-left text-sm text-slate-900 uppercase">Total</td>
                                <td class="px-6 py-3 text-center text-sm text-slate-900">₱{{ number_format($breakdownTotalBasic, 2) }}</td>
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
        const elements = {
            // View Containers
            filtersContainer: document.getElementById('filtersContainer'),
            billingRecordsContainer: document.getElementById('billingRecordsContainer'),
            planDistributionContainer: document.getElementById('planDistributionContainer'),
            revenueBreakdownContainer: document.getElementById('revenueBreakdownContainer'),

            // View Switching Buttons
            showRevenueBtn: document.getElementById('showRevenueBreakdownBtn'),
            showPlanBtn: document.getElementById('showPlanDistributionBtn'),
            backBtns: document.querySelectorAll('.back-to-billing-btn'),
            clearRevenueDateFilterBtn: document.getElementById('clearDateFilterBtn'),
            clearPlanDateFilterBtn: document.getElementById('clearPlanDateFilterBtn'),

            // Billing Table Filter Inputs
            search: document.getElementById('search'),
            dateFilter: document.getElementById('dateFilter'),
            statusFilter: document.getElementById('statusFilter'),
            planFilter: document.getElementById('planFilter'),
            clearFiltersBtn: document.getElementById('clearFilters'),

            // Billing Table Parts
            records: document.querySelectorAll('.billing-record'),
            recordCount: document.getElementById('recordCount'),
            noResults: document.getElementById('noResults'),
            billingTable: document.getElementById('billingTable'),
        };

        const allViews = [elements.billingRecordsContainer, elements.planDistributionContainer, elements.revenueBreakdownContainer];

        function showView(viewToShow) {
            allViews.forEach(view => view && (view.style.display = 'none'));
            if (viewToShow) viewToShow.style.display = 'block';
            if (elements.filtersContainer) elements.filtersContainer.style.display = (viewToShow === elements.billingRecordsContainer) ? 'block' : 'none';
        }

        function navigateWithParams(params) {
            window.location.href = window.location.pathname + '?' + params.toString();
        }

        const filterBillingRecords = () => {
            if (!elements.billingTable || elements.billingRecordsContainer.style.display === 'none') return;

            const query = elements.search.value.toLowerCase();
            const date = elements.dateFilter.value;
            const status = elements.statusFilter.value;
            const plan = elements.planFilter.value;
            let visibleCount = 0;

            elements.records.forEach(record => {
                const isVisible =
                    (!query || record.dataset.searchText.includes(query)) &&
                    (!date || record.dataset.date === date) &&
                    (!status || record.dataset.status === status) &&
                    (!plan || record.dataset.plan === plan);

                record.style.display = isVisible ? '' : 'none';
                if (isVisible) visibleCount++;
            });

            elements.recordCount.textContent = `(${visibleCount})`;
            const hasResults = visibleCount > 0;
            elements.billingTable.style.display = hasResults ? 'table' : 'none';
            elements.noResults.style.display = hasResults ? 'none' : 'block';
        };

        // --- EVENT LISTENERS ---
        if (elements.showRevenueBtn) elements.showRevenueBtn.addEventListener('click', () => {
            const params = new URLSearchParams(window.location.search);
            params.set('view', 'revenue_breakdown');
            navigateWithParams(params);
        });

        if (elements.showPlanBtn) elements.showPlanBtn.addEventListener('click', () => {
            const params = new URLSearchParams(window.location.search);
            params.set('view', 'plan_distribution');
            navigateWithParams(params);
        });

        if (elements.backBtns) elements.backBtns.forEach(btn => btn.addEventListener('click', () => {
            const params = new URLSearchParams(window.location.search);
            ['view', 'start_date', 'end_date', 'pd_start_date', 'pd_end_date'].forEach(p => params.delete(p));
            navigateWithParams(params);
        }));

        if (elements.clearRevenueDateFilterBtn) elements.clearRevenueDateFilterBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const params = new URLSearchParams(window.location.search);
            params.delete('start_date');
            params.delete('end_date');
            navigateWithParams(params);
        });

        if (elements.clearPlanDateFilterBtn) elements.clearPlanDateFilterBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const params = new URLSearchParams(window.location.search);
            params.delete('pd_start_date');
            params.delete('pd_end_date');
            navigateWithParams(params);
        });

        if (elements.filtersContainer) {
            ['input', 'change'].forEach(event => {
                elements.search.addEventListener(event, filterBillingRecords);
                elements.dateFilter.addEventListener(event, filterBillingRecords);
                elements.statusFilter.addEventListener(event, filterBillingRecords);
                elements.planFilter.addEventListener(event, filterBillingRecords);
            });
            elements.clearFiltersBtn.addEventListener('click', () => {
                elements.search.value = '';
                elements.dateFilter.value = '';
                elements.statusFilter.value = '';
                elements.planFilter.value = '';
                filterBillingRecords();
            });
        }

        // --- INITIALIZATION ON PAGE LOAD ---
        const urlParams = new URLSearchParams(window.location.search);
        const currentView = urlParams.get('view');

        if (currentView === 'plan_distribution') {
            showView(elements.planDistributionContainer);
        } else if (currentView === 'revenue_breakdown') {
            showView(elements.revenueBreakdownContainer);
        } else {
            showView(elements.billingRecordsContainer);
            filterBillingRecords();
        }
    });
</script>
@endsection