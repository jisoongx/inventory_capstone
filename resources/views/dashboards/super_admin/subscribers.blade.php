@extends('dashboards.super_admin.super_admin')

@section('content')

@php
function getBadgeClasses($type, $value) {
$base = 'inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-md text-white';
switch ($type) {
case 'plan':
return $base . ' ' . (trim($value) === 'Basic' ? 'bg-gradient-to-r from-orange-400 to-orange-500' : 'bg-gradient-to-r from-rose-500 to-rose-600');
case 'status':
return $base . ' ' . (trim($value) === 'active' ? 'bg-gradient-to-r from-green-500 to-green-600' : 'bg-gradient-to-r from-red-500 to-red-600');
case 'days':
if ($value < 0) return $base . ' bg-gradient-to-r from-red-500 to-red-600' ;
    if ($value <=7) return $base . ' bg-gradient-to-r from-red-500 to-red-600' ;
    if ($value <=14) return $base . ' bg-gradient-to-r from-orange-500 to-orange-600' ;
    if ($value <=30) return $base . ' bg-gradient-to-r from-yellow-500 to-amber-500' ;
    return $base . ' bg-gradient-to-r from-gray-500 to-gray-600' ;
    }
    }
    @endphp

    <style>
    /* Active State for Stat Cards (uses CSS variables for dynamic coloring) */
    .active-tab {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px var(--shadow-color);
    border-color: var(--border-color) !important;
    background: var(--bg-gradient);
    }
    .active-tab .top-indicator { opacity: 1 !important; }
    .group:hover .top-indicator { opacity: 1 !important; }
    </style>

    <div class="min-h-screen">
        <div class="container mx-auto px-2 py-5 max-w-7xl">

            {{-- Stat Cards --}}
            <div class="flex flex-col sm:flex-row gap-5 mb-5 w-full max-w-7xl">
                <button class="group relative flex-1 p-5 rounded-xl transition-transform duration-300 ease-in-out hover:-translate-y-1 cursor-pointer bg-gradient-to-br from-white to-emerald-50 border border-emerald-200 shadow-md overflow-hidden" data-tab="active">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-green-500 opacity-0 transition-opacity duration-300 top-indicator"></div>
                    <div class="relative z-10 text-left">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg transition-transform group-hover:scale-105"><span class="material-symbols-outlined text-white text-xl">verified</span></div>
                                <div>
                                    <div class="text-sm font-semibold text-emerald-700 uppercase tracking-wide">Active</div>
                                    <div class="text-3xl font-bold text-gray-900 leading-none">{{ $activeCount }}</div>
                                </div>
                            </div>
                            <div class="text-xs text-emerald-700 font-semibold px-3 py-1.5 bg-emerald-100 rounded-full border border-emerald-200">
                                <div class="flex items-center gap-1">
                                    <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>Live
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 font-medium">Active Subscriptions</div>
                    </div>
                </button>
                <button class="group relative flex-1 p-5 rounded-xl transition-transform duration-300 ease-in-out hover:-translate-y-1 cursor-pointer bg-gradient-to-br from-white to-red-50 border border-red-200 shadow-md overflow-hidden" data-tab="expired">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-rose-500 opacity-0 transition-opacity duration-300 top-indicator"></div>
                    <div class="relative z-10 text-left">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-lg transition-transform group-hover:scale-105"><span class="material-symbols-outlined text-white text-xl">cancel</span></div>
                                <div>
                                    <div class="text-sm font-semibold text-red-700 uppercase tracking-wide">Expired</div>
                                    <div class="text-3xl font-bold text-gray-900 leading-none">{{ $expiredCount }}</div>
                                </div>
                            </div>
                            @if($expiredCount > 0)
                            <div class="text-xs text-red-700 font-semibold px-3 py-1.5 bg-red-100 rounded-full border border-red-200">
                                <div class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">priority_high</span>Action</div>
                            </div>
                            @endif
                        </div>
                        <div class="text-sm text-gray-600 font-medium">Expired Subscriptions</div>
                    </div>
                </button>
                <button class="group relative flex-1 p-5 rounded-xl transition-transform duration-300 ease-in-out hover:-translate-y-1 cursor-pointer bg-gradient-to-br from-white to-amber-50 border border-amber-200 shadow-md overflow-hidden" data-tab="upcoming">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500 opacity-0 transition-opacity duration-300 top-indicator"></div>
                    <div class="relative z-10 text-left">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg transition-transform group-hover:scale-105"><span class="material-symbols-outlined text-white text-xl">schedule</span></div>
                                <div>
                                    <div class="text-sm font-semibold text-amber-700 uppercase tracking-wide">Upcoming</div>
                                    <div class="text-3xl font-bold text-gray-900 leading-none">{{ $upcomingCount }}</div>
                                </div>
                            </div>
                            @if($upcomingCount > 0)
                            <div class="text-xs text-amber-700 font-semibold px-3 py-1.5 bg-amber-100 rounded-full border border-amber-200">
                                <div class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">timer</span>30d</div>
                            </div>
                            @endif
                        </div>
                        <div class="text-sm text-gray-600 font-medium">Expiring Soon</div>
                    </div>
                </button>
            </div>

            {{-- Filters Card --}}
            <div class="bg-white rounded-xl shadow-md border-gray-100 mb-6 backdrop-blur-lg bg-opacity-90">
                <div class="p-6">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="relative flex-1 min-w-[20rem]">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                            <input type="text" id="search" placeholder="Search stores or owners..." class="w-full pl-10 pr-4 py-3 rounded-lg text-sm focus:outline-none transition-all border border-gray-200 focus:border-green-500 focus:ring-1 focus:ring-green-500/30" />
                        </div>
                        <input type="date" id="dateFilter" class="px-4 py-3 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 transition-all shadow-sm" />
                        <div id="planFilters" class="flex gap-2">
                            <button type="button" class="filter-plan btn-filter bg-orange-400 hover:bg-orange-500 text-white rounded-lg px-4 py-3 text-sm font-semibold shadow-lg transition-all" data-plan="1">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-base">star</span>
                                    <div class="text-left">
                                        <div class="text-sm font-semibold">Basic</div>
                                    </div>
                                </div>
                            </button>
                            <button type="button" class="filter-plan btn-filter bg-rose-500 hover:bg-rose-600 text-white rounded-lg px-4 py-3 text-sm font-semibold shadow-lg transition-all" data-plan="2">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-base">diamond</span>
                                    <div class="text-left">
                                        <div class="text-sm font-semibold">Premium</div>
                                    </div>
                                </div>
                            </button>
                        </div>
                        <div id="expiryFilters" class="hidden gap-2">
                            <button type="button" class="expiry-filter btn-filter bg-red-500 hover:bg-red-700 text-white rounded-lg px-4 py-3 text-sm font-semibold shadow-lg" data-days="7">
                                <div class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">warning</span>7 Days</div>
                            </button>
                            <button type="button" class="expiry-filter btn-filter bg-orange-500 hover:bg-orange-700 text-white rounded-lg px-4 py-3 text-sm font-semibold shadow-lg" data-days="14">
                                <div class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span>14 Days</div>
                            </button>
                            <button type="button" class="expiry-filter btn-filter bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg px-4 py-3 text-sm font-semibold shadow-lg" data-days="30">
                                <div class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">event</span>30 Days</div>
                            </button>
                        </div>
                        <button type="button" id="clearFilters" class="bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg px-4 py-3 text-sm font-semibold transition-all shadow-lg">
                            <div class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">clear_all</span>Clear</div>
                        </button>
                    </div>
                </div>
            </div>

            @if($clients->isNotEmpty())
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100" id="dataTable">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-fixed">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wide">
                                    <div class="flex items-center gap-1"><span class="material-symbols-outlined text-base">storefront</span>Store</div>
                                </th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wide">
                                    <div class="flex items-center gap-1"><span class="material-symbols-outlined text-base">person</span>Owner</div>
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700 uppercase tracking-wide w-32">
                                    <div class="flex items-center justify-center gap-1"><span class="material-symbols-outlined text-base">workspace_premium</span>Plan</div>
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700 uppercase tracking-wide w-24">
                                    <div class="flex items-center justify-center gap-1"><span class="material-symbols-outlined text-base">toggle_on</span>Status</div>
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700 uppercase tracking-wide">Start Date</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700 uppercase tracking-wide">Expiry Date</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700 uppercase tracking-wide">Days Left</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            @foreach($clients as $client)
                            @foreach($client->subscriptions as $subscription)
                            @php
                            $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($subscription->subscription_end ?? now()), false);
                            $planTitle = trim($subscription->planDetails->plan_title ?? '-');
                            $subStatus = $subscription->status;
                            @endphp
                            <tr class="subscription-row transition-all duration-250 hover:bg-indigo-50/50" data-status="{{ $subStatus }}" data-days-left="{{ $daysLeft }}" data-plan-id="{{ $subscription->plan_id }}" data-owner="{{ strtolower($client->firstname . ' ' . $client->lastname) }}" data-store="{{ strtolower($client->store_name) }}" data-expiry-date="{{ $subscription->subscription_end ? \Carbon\Carbon::parse($subscription->subscription_end)->format('Y-m-d') : '' }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center text-white text-sm font-semibold shadow-md">{{ strtoupper(substr($client->store_name, 0, 2)) }}</div>
                                        <div>
                                            <div class="font-semibold text-slate-900 text-sm">{{ $client->store_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-slate-800 font-semibold text-sm">{{ $client->firstname }} {{ $client->lastname }}</div>
                                    <div class="text-xs text-slate-500">{{ $client->middlename ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 text-center"><span class="w-[100px] {{ getBadgeClasses('plan', $planTitle) }}">
                                        <div class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">{{ $planTitle === 'Basic' ? 'star' : 'diamond' }}</span>{{ $planTitle }}</div>
                                    </span></td>
                                <td class="px-6 py-4 text-center"><span class="w-[80px] {{ getBadgeClasses('status', $subStatus) }}">{{ ucfirst($subStatus) }}</span></td>
                                <td class="px-6 py-4 text-center text-slate-700 text-sm font-medium">{{ $subscription->subscription_start ? \Carbon\Carbon::parse($subscription->subscription_start)->format('M j, Y') : '-' }}</td>
                                <td class="px-6 py-4 text-center text-slate-700 text-sm font-medium">{{ $subscription->subscription_end ? \Carbon\Carbon::parse($subscription->subscription_end)->format('M j, Y') : '-' }}</td>
                                <td class="px-6 py-4 text-center"><span class="{{ getBadgeClasses('days', $daysLeft) }}">{{ $daysLeft < 0 ? 'Expired' : floor($daysLeft) . 'd' }}</span></td>
                            </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Pagination is now INSIDE the card with padding and a border --}}
                <div id="pagination" class="px-6 py-4 border-t border-slate-200">
                    {{ $clients->links() }}
                </div>
            </div>

            <div id="noResults" class="hidden bg-white rounded-xl shadow-lg p-8 text-center border border-gray-100">
                <div class="w-16 h-16 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-4"><span class="material-symbols-outlined text-slate-500 text-2xl">search_off</span></div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No Results Found</h3>
                <p class="text-sm text-slate-500 mb-4">No subscriptions match your filters.</p>
                <button type="button" id="clearAllFilters" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-6 py-3 text-sm font-semibold transition-all shadow-lg">
                    <div class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">refresh</span>Clear Filters</div>
                </button>
            </div>

            @else
            <div class="bg-white rounded-xl shadow-lg p-8 text-center border border-gray-100">
                <div class="w-16 h-16 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-4"><span class="material-symbols-outlined text-slate-500 text-2xl">inbox</span></div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No Subscriptions</h3>
                <p class="text-sm text-slate-500">No subscription data available.</p>
            </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ui = {
                tabs: document.querySelectorAll('[data-tab]'),
                planFilters: document.getElementById('planFilters'),
                expiryFilters: document.getElementById('expiryFilters'),
                searchInput: document.getElementById('search'),
                dateFilter: document.getElementById('dateFilter'),
                clearFiltersBtn: document.getElementById('clearFilters'),
                clearAllFiltersBtn: document.getElementById('clearAllFilters'),
                subscriptionRows: document.querySelectorAll('.subscription-row'),
                noResults: document.getElementById('noResults'),
                dataTable: document.getElementById('dataTable'),
                pagination: document.getElementById('pagination'),
            };

            let currentTab = 'active';

            const colorMap = {
                'active': {
                    border: 'rgb(52, 211, 153)',
                    shadow: 'rgba(16, 185, 129, 0.12)',
                    hoverShadow: 'rgba(16, 185, 129, 0.15)',
                    bg: 'linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%)'
                },
                'expired': {
                    border: 'rgb(248, 113, 113)',
                    shadow: 'rgba(239, 68, 68, 0.12)',
                    hoverShadow: 'rgba(239, 68, 68, 0.15)',
                    bg: 'linear-gradient(135deg, #ffffff 0%, #fff5f5 100%)'
                },
                'upcoming': {
                    border: 'rgb(251, 191, 36)',
                    shadow: 'rgba(245, 158, 11, 0.12)',
                    hoverShadow: 'rgba(245, 158, 11, 0.15)',
                    bg: 'linear-gradient(135deg, #ffffff 0%, #fffbeb 100%)'
                }
            };

            const updateTabUI = (activeTabId) => {
                ui.tabs.forEach(tab => {
                    const isActive = tab.dataset.tab === activeTabId;
                    tab.classList.toggle('active-tab', isActive);
                    if (isActive) {
                        const colors = colorMap[activeTabId];
                        tab.style.setProperty('--border-color', colors.border);
                        tab.style.setProperty('--shadow-color', colors.shadow);
                        tab.style.setProperty('--hover-shadow-color', colors.hoverShadow);
                        tab.style.setProperty('--bg-gradient', colors.bg);
                    }
                });
            };

            const toggleFilterVisibility = (tab) => {
                const isUpcoming = tab === 'upcoming';
                ui.planFilters.classList.toggle('hidden', isUpcoming);
                ui.planFilters.classList.toggle('flex', !isUpcoming);
                ui.expiryFilters.classList.toggle('hidden', !isUpcoming);
                ui.expiryFilters.classList.toggle('flex', isUpcoming);
            };

            const filterRows = () => {
                const query = ui.searchInput.value.toLowerCase();
                const date = ui.dateFilter.value;
                const plan = ui.planFilters.querySelector('.selected')?.dataset.plan;
                const expiry = ui.expiryFilters.querySelector('.selected')?.dataset.days;
                let visibleCount = 0;

                ui.subscriptionRows.forEach(row => {
                    const {
                        status,
                        planId,
                        daysLeft,
                        owner,
                        store,
                        expiryDate
                    } = row.dataset;
                    let show = false;

                    if (currentTab === 'active') {
                        show = status === 'active' && (!plan || planId === plan);
                    } else if (currentTab === 'expired') {
                        show = status === 'expired' && (!plan || planId === plan);
                    } else if (currentTab === 'upcoming') {
                        const isUpcomingCandidate = status === 'active' && parseInt(daysLeft) >= 0;
                        if (isUpcomingCandidate) {
                            show = expiry ? parseInt(daysLeft) <= parseInt(expiry) : parseInt(daysLeft) <= 30;
                        }
                    }

                    if (show && query && !(owner.includes(query) || store.includes(query))) show = false;
                    if (show && date && expiryDate !== date) show = false;

                    row.style.display = show ? '' : 'none';
                    if (show) visibleCount++;
                });

                const hasVisibleRows = visibleCount > 0;
                if (ui.dataTable) ui.dataTable.classList.toggle('hidden', !hasVisibleRows);
                if (ui.noResults) ui.noResults.classList.toggle('hidden', hasVisibleRows);
            };

            const handleTabClick = (e) => {
                const tabId = e.currentTarget.dataset.tab;
                if (tabId === currentTab) return;
                currentTab = tabId;
                clearAllFilters(false);
            };

            const handleFilterButtonClick = (e) => {
                const button = e.currentTarget;
                const isSelected = button.classList.contains('selected');
                button.parentElement.querySelectorAll('.btn-filter').forEach(btn => btn.classList.remove('selected'));
                if (!isSelected) button.classList.add('selected');
                filterRows();
            };

            const clearAllFilters = (resetTab = true) => {
                ui.searchInput.value = '';
                ui.dateFilter.value = '';
                document.querySelectorAll('.btn-filter').forEach(btn => btn.classList.remove('selected'));
                if (resetTab) {
                    currentTab = 'active';
                }
                updateTabUI(currentTab);
                toggleFilterVisibility(currentTab);
                filterRows();
            };

            ui.tabs.forEach(tab => tab.addEventListener('click', handleTabClick));
            ui.searchInput.addEventListener('input', filterRows);
            ui.dateFilter.addEventListener('change', filterRows);
            document.querySelectorAll('.filter-plan, .expiry-filter').forEach(btn => btn.addEventListener('click', handleFilterButtonClick));
            ui.clearFiltersBtn.addEventListener('click', () => clearAllFilters(true));
            if (ui.clearAllFiltersBtn) {
                ui.clearAllFiltersBtn.addEventListener('click', () => clearAllFilters(true));
            }

            updateTabUI(currentTab);
            toggleFilterVisibility(currentTab);
            filterRows();
        });
    </script>

    @endsection