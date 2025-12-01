@php
if (!function_exists('getBadgeClasses')) {
function getBadgeClasses($type, $value) {
$base = 'inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-md text-white';
switch ($type) {
case 'plan':
$value = trim($value);
switch ($value) {
case 'Basic':
return $base . ' bg-gradient-to-r from-yellow-500 to-yellow-600';
case 'Standard':
return $base . ' bg-gradient-to-r from-orange-400 to-orange-500';
case 'Premium':
return $base . ' bg-gradient-to-r from-rose-500 to-rose-600';
default:
return $base . ' bg-gradient-to-r from-gray-400 to-gray-500'; // fallback
}

case 'status':
return $base . ' ' . (trim($value) === 'active'
? 'bg-gradient-to-r from-green-500 to-green-600'
: 'bg-gradient-to-r from-red-500 to-red-600');
case 'days':
$base .= ' w-24';
if ($value < 0) return $base . ' bg-gradient-to-r from-red-600 to-red-700' ; // Expired
    if ($value <=3) return $base . ' bg-gradient-to-r from-red-500 to-red-600' ; // Urgent
    if ($value <=7) return $base . ' bg-gradient-to-r from-orange-500 to-orange-600' ; // Soon
    if ($value <=14) return $base . ' bg-gradient-to-r from-yellow-500 to-amber-500' ; // Later
    return $base . ' bg-gradient-to-r from-gray-500 to-gray-600' ; // Beyond 14 days
    }
    }
    }
    @endphp

    @if (!request()->ajax())
    @extends('dashboards.super_admin.super_admin')
    @section('page-header')
    <div class="flex items-center gap-3 text-gray-800">

        <h2 class="text-lg font-bold ml-3">Welcome back, Admin!</h2>
        <!-- <span class="material-symbols-rounded text-blue-600 align-middle">waving_hand</span> -->
        <span class="text-gray-400">|</span>
        <span id="date" class="text-sm font-medium text-slate-600"></span>
        <span id="clock" class="text-sm font-medium text-slate-600"></span>
    </div>

    <script>
        function updateDateTime() {
            const clock = document.getElementById('clock');
            const dateEl = document.getElementById('date');
            const now = new Date();

            // Format time (HH:MM:SS)
            const timeOptions = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            clock.textContent = now.toLocaleTimeString([], timeOptions);

            // Format date (Day, Month DD, YYYY)
            const dateOptions = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            dateEl.textContent = now.toLocaleDateString([], dateOptions);
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>
    @endsection



    @section('content')

    {{-- This div is used by Tailwind's JIT compiler to ensure these dynamic classes are not purged. --}}

    {{-- Tailwind safelist for dynamic classes --}}
    <div class="hidden">
        border-emerald-500 border-red-500 border-amber-500
        hover:border-emerald-400 hover:border-red-400 hover:border-amber-400
        shadow-emerald-500/10 shadow-red-500/10 shadow-amber-500/10
        text-emerald-600 text-red-600 text-amber-600
        bg-emerald-100 bg-red-100 bg-amber-100
    </div>



    @php

    // Data for stat cards, only needed on full page load
    $statCards=[ 'active'=> ['count' => $activeCount, 'label' => 'Active Subscriptions', 'icon' => 'verified', 'color' => 'emerald', 'hint' => 'Currently billed'],
    'expired' => ['count' => $expiredCount, 'label' => 'Expired Subscriptions', 'icon' => 'cancel', 'color' => 'red', 'hint' => 'Needs follow-up'],
    'upcoming' => ['count' => $upcomingCount, 'label' => 'Expiring Soon', 'icon' => 'schedule', 'color' => 'amber', 'hint' => 'Renewals due'],
    'cancelled' => [
    'count' => $cancelledCount,
    'label' => 'Cancelled Subscriptions',
    'icon' => 'block', // pick a relevant material icon
    'color' => 'gray',
    'hint' => 'User cancelled'
    ]
    ];
    $activeTab = request('status', 'active');
    @endphp

    <div class="min-h-screen">
        <div class="container mx-auto px-2 py-5 max-w-7xl">
            {{-- Stat Cards --}}
            <div id="stat-cards-container" class="flex flex-col sm:flex-row gap-5 mb-5 w-full">
                @foreach ($statCards as $type => $card)
                @php
                $isActive = $activeTab === $type;
                $color = $card['color'];
                @endphp
                {{-- The href now only contains the essential parameter for the action --}}
                <a href="?status={{ $type }}" data-filter-key="status" data-filter-value="{{ $type }}" data-color="{{ $color }}" class="filter-link stat-card-link group flex-1 p-5 rounded transition-all duration-300 ease-in-out bg-white shadow-md hover:-translate-y-1 border-l-4 {{ $isActive ? "border-{$color}-500 shadow-{$color}-500/10" : "border-gray-200 hover:border-{$color}-400" }}">
                    <div class="relative z-10 text-left">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg transition-transform group-hover:scale-105 bg-{{ $color }}-100">
                                    <span class="material-symbols-outlined text-{{ $color }}-600 text-xl">{{ $card['icon'] }}</span>
                                </div>
                                <div>
                                    <div class="text-xl font-bold text-{{ $color }}-600 leading-none">{{ $card['count'] }}</div>
                                    <div class="text-sm text-gray-600 font-medium mt-1">{{ $card['label'] }}</div>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-xs text-gray-500 font-semibold mb-1">{{ $card['hint'] }}</div>
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center transition-all duration-300 group-hover:bg-{{$color}}-500 focus-within:bg-{{$color}}-500">
                                    <span class="material-symbols-outlined stat-card-chevron text-base text-gray-400 group-hover:text-indigo-600 group-focus-within:text-indigo-600">
                                        chevron_right
                                    </span>

                                    </span>
                                </div>


                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            {{-- Filters Card --}}
            <div class="bg-white/90 backdrop-blur-lg rounded shadow-md border border-gray-100 mb-6">
                <div class="p-6">
                    <form id="filterForm" class="flex flex-wrap items-center gap-4">
                        <input type="hidden" name="status" value="{{ $activeTab }}">
                        <div class="relative flex-1 min-w-[20rem]">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                            <input type="text" name="search" placeholder="Search stores or owners..." value="{{ request('search') }}" class="w-full pl-10 pr-4 py-3 rounded-lg text-sm focus:outline-none transition-all border border-gray-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30" />
                        </div>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-4 py-3 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 transition-all shadow-sm" />

                        {{-- Plan Filters (for 'active' and 'expired') --}}
                        <div id="plan-filters" class="flex gap-2">
                            @php
                            $status = $activeTab ?? 'active';
                            @endphp
                            @if($status !== 'expired')
                            <a href="?plan=3" data-filter-key="plan" data-filter-value="3"
                                class="filter-link rounded-lg px-4 py-3 text-sm font-semibold shadow-md transition-all flex items-center gap-2 text-yellow-500 
 hover:bg-yellow-50 focus:ring-2 focus:ring-yellow-400"
                                @if(in_array($status, ['expired','cancelled'])) style="display:none" @endif>
                                <span class="material-symbols-outlined text-base text-yellow-500">magic_button</span>
                                <span>Basic</span>
                            </a>
                            @endif
                            <a href="?plan=1" data-filter-key="plan" data-filter-value="1"
                                class="filter-link rounded-lg px-4 py-3 text-sm font-semibold shadow-md transition-all flex items-center gap-2 text-orange-500 
                                hover:bg-orange-50 focus:ring-2 focus:ring-orange-400">
                                <span class="material-symbols-outlined text-orange-500">star</span>
                                <span>Standard</span>
                            </a>
                            <a href="?plan=2" data-filter-key="plan" data-filter-value="2"
                                class="filter-link rounded-lg px-4 py-3 text-sm font-semibold shadow-md transition-all flex items-center gap-2 text-rose-500 
                                 hover:bg-rose-50 focus:ring-2 focus:ring-rose-400">
                                <span class="material-symbols-outlined text-base text-rose-500">diamond</span>
                                <span>Premium</span>
                            </a>
                        </div>


                        {{-- Expiry Filters (for 'upcoming') --}}
                        <div id="expiry-filters" class="flex flex-wrap gap-2">
                            <a href="?range=urgent" data-filter-key="range" data-filter-value="urgent"
                                class="filter-link rounded-lg px-4 py-3 text-sm font-semibold shadow-md transition-all flex items-center gap-2 text-red-500 
                                 hover:bg-red-50 focus:ring-2 focus:ring-red-400">
                                <span class="material-symbols-outlined text-sm text-red-500">warning</span>
                                <span>Urgent</span>
                            </a>
                            <a href="?range=soon" data-filter-key="range" data-filter-value="soon"
                                class="filter-link rounded-lg px-4 py-3 text-sm font-semibold shadow-md transition-all flex items-center gap-2 text-orange-500 
                                 hover:bg-orange-50 focus:ring-2 focus:ring-orange-400">
                                <span class="material-symbols-outlined text-sm text-orange-500">schedule</span>
                                <span>Soon</span>
                            </a>
                            <a href="?range=later" data-filter-key="range" data-filter-value="later"
                                class="filter-link rounded-lg px-4 py-3 text-sm font-semibold shadow-md transition-all flex items-center gap-2 text-amber-500 
                                 hover:bg-amber-50 focus:ring-2 focus:ring-amber-400">
                                <span class="material-symbols-outlined text-sm text-amber-500">event_upcoming</span>
                                <span>Later</span>
                            </a>
                        </div>


                        <a href="#" data-filter-key="clear" class="filter-link bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg px-4 py-3 text-sm font-semibold transition-all shadow-md flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">clear_all</span>Clear
                        </a>
                    </form>
                </div>
            </div>
            @endif

            {{-- This container's content gets replaced by AJAX --}}
            <div id="content-container">
                @php
                if (!function_exists('getBadgeClasses')) {
                function getBadgeClasses($type, $value) { /* ... function content ... */ }
                }


                $isFiltered = !empty(array_filter([
                request('search'),
                request('plan'),
                request('range'),
                request('start_date'),
                ], fn($val) => !empty($val)));
                @endphp

                @if($isFiltered)
                <div class="mb-4 p-4 rounded-lg bg-indigo-50 border border-indigo-200 text-sm text-indigo-800">
                    Showing <span class="font-bold">{{ $clients->total() }}</span> record(s) matching your filters.
                </div>
                @endif

                @php
                $totalSubscriptions = $clients->sum(fn($client) => $client->subscriptions->count());
                @endphp


                @if($totalSubscriptions > 0)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden ">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-slate-100 sticky top-0 z-10 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">
                                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-base">storefront</span>Store</div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">
                                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-base">person</span>Owner</div>
                                    </th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700 uppercase tracking-wider">
                                        <div class="flex items-center justify-center gap-2"><span class="material-symbols-outlined text-base">workspace_premium</span>Plan</div>
                                    </th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700 uppercase tracking-wider">
                                        <div class="flex items-center justify-center gap-2"><span class="material-symbols-outlined text-base">toggle_on</span>Status</div>
                                    </th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700 uppercase tracking-wider">Start Date</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700 uppercase tracking-wider">Expiry Date</th>
                                    <th class="px-6 py-4 text-right text-sm font-semibold text-slate-700 uppercase tracking-wider">Days Left</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @foreach($clients as $client)
                                @foreach($client->subscriptions as $subscription)
                                {{-- Your table row content here --}}
                                @php
                                $planTitle = trim($subscription->planDetails->plan_title ?? '-');
                                $subStatus = $subscription->status;
                                $endDate = \Carbon\Carbon::parse($subscription->subscription_end ?? now());

                                if (now()->lte($endDate)) {
                                // inclusive (counts today + end date)
                                $daysLeft = now()->diffInDays($endDate) + 1;
                                } else {
                                // already expired
                                $daysLeft = 0;
                                }
                                @endphp

                                <tr class="transition-colors duration-200 hover:bg-blue-100">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center text-white text-sm font-semibold shadow-md">{{ strtoupper(substr($client->store_name, 0, 2)) }}</div>
                                            <div class="font-semibold text-slate-900 text-sm">{{ $client->store_name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-slate-800 font-semibold text-sm">{{ $client->firstname }} {{ $client->lastname }}</div>
                                        <div class="text-xs text-slate-500">{{ $client->middlename ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="{{ getBadgeClasses('plan', $planTitle) }} w-[100px] justify-center">
                                            <div class="flex items-center gap-1">
                                                @php
                                                $planIcon = match($planTitle) {
                                                'Basic' => 'magic_button', // or 'verified', 'layers', any symbol you like
                                                'Standard' => 'star',
                                                'Premium' => 'diamond',
                                                default => 'help'
                                                };
                                                @endphp

                                                <span class="material-symbols-outlined text-xs">{{ $planIcon }}</span>

                                                {{ $planTitle }}
                                            </div>
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center ">
                                        <span class="{{ getBadgeClasses('status', $subStatus) }} justify-center">
                                            {{ ucfirst($subStatus) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-slate-700 text-sm font-medium">{{ $subscription->subscription_start ? \Carbon\Carbon::parse($subscription->subscription_start)->format('M j, Y') : '-' }}</td>
                                    <td class="px-6 py-4 text-center text-slate-700 text-sm font-medium">{{ $subscription->subscription_end ? \Carbon\Carbon::parse($subscription->subscription_end)->format('M j, Y') : '-' }}</td>
                                    <td class="px-6 py-4 text-right"><span class=" w-24 {{ getBadgeClasses('days', $daysLeft) }}">{{ $daysLeft < 0 ? 'Expired' : floor($daysLeft) . ' days' }}</span></td>
                                </tr>
                                @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-200 pagination">
                        {{ $clients->withQueryString()->links() }}
                    </div>
                </div>
                @else
                <div class="bg-white rounded-xl shadow-lg p-8 text-center border border-gray-100">
                    <div class="w-16 h-16 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-4"><span class="material-symbols-outlined text-slate-500 text-2xl">search_off</span></div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">No Results Found</h3>
                    <p class="text-sm text-slate-500 mb-4">No subscriptions match the current filters.</p>
                </div>
                @endif


            </div>

            @if (!request()->ajax())
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contentContainer = document.getElementById('content-container');
            const filterForm = document.getElementById('filterForm');
            let debounceTimeout;

            const updatePlanFilters = (status) => {
                const planFilters = document.getElementById('plan-filters');
                if (!planFilters) return;

                // Show all buttons first
                planFilters.querySelectorAll('a').forEach(btn => btn.style.display = 'flex');

                // Hide Basic if expired
                if (status === 'expired' || status === 'cancelled') {
                    const basicBtn = planFilters.querySelector('a[data-filter-value="3"]');
                    if (basicBtn) basicBtn.style.display = 'none';
                }

                // Show/hide the entire filter container depending on status
                planFilters.style.display = ['active', 'expired', 'cancelled'].includes(status) ? 'flex' : 'none';
            };


            const syncUIWithURL = () => {
                const params = new URLSearchParams(window.location.search);
                const status = params.get('status') || 'active';
                updatePlanFilters(status);
                const plan = params.get('plan');
                const range = params.get('range');

                filterForm.querySelector('input[name="status"]').value = status;

                document.querySelectorAll('.stat-card-link').forEach(card => {
                    const cardStatus = card.dataset.filterValue;
                    const color = card.dataset.color;

                    // Reset border and shadow
                    card.classList.remove(`border-${color}-500`, `shadow-${color}-500/10`);
                    card.classList.add('border-gray-200');

                    // Reset chevron
                    const chevron = card.querySelector('.stat-card-chevron');
                    if (chevron) {
                        chevron.classList.remove('text-indigo-500');
                        chevron.classList.add('text-gray-400');
                    }

                    // Set active styles
                    if (cardStatus === status) {
                        card.classList.remove('border-gray-200');
                        card.classList.add(`border-${color}-500`, `shadow-${color}-500/10`);

                        if (chevron) {
                            chevron.classList.remove('text-gray-400');
                            chevron.classList.add('text-indigo-500'); // always indigo
                        }
                    }
                });



                document.getElementById('plan-filters').style.display = ['active', 'expired', 'cancelled'].includes(status) ? 'flex' : 'none';

                document.getElementById('expiry-filters').style.display = status === 'upcoming' ? 'flex' : 'none';

                // Update active classes for plan/range buttons
            };

            const fetchContent = async (url, pushState = true) => {
                try {
                    document.body.style.cursor = 'wait';
                    contentContainer.style.opacity = '0.5';

                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const html = await response.text();

                    if (pushState) {
                        history.pushState({}, '', url);
                    }

                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, 'text/html');
                    const newContent = newDoc.querySelector('#content-container');
                    if (newContent) contentContainer.innerHTML = newContent.innerHTML;

                    syncUIWithURL();
                } catch (error) {
                    console.error('Error fetching content:', error);
                } finally {
                    document.body.style.cursor = 'default';
                    contentContainer.style.opacity = '1';
                }
            };

            // ✅ NEW ROBUST Event Listener for Clicks
            document.body.addEventListener('click', function(event) {
                const link = event.target.closest('.filter-link, .pagination a');
                if (!link) return;

                event.preventDefault(); // Stop default navigation

                const currentParams = new URLSearchParams(new FormData(filterForm));
                const linkParams = new URL(link.href, window.location.origin).searchParams;
                const key = link.dataset.filterKey;

                // Reset page for any new filter click that is NOT pagination
                if (!link.closest('.pagination')) {
                    currentParams.delete('page');
                }

                if (key === 'status') {
                    // A stat card click is a "hard reset"
                    const newStatus = link.dataset.filterValue;
                    filterForm.reset(); // Clear visual inputs
                    currentParams.forEach((val, k) => currentParams.delete(k)); // Clear all params
                    currentParams.set('status', newStatus); // Set the new status
                } else if (key === 'clear') {
                    // 🔑 Clear everything except status
                    const status = currentParams.get('status') || 'active';
                    filterForm.reset();
                    currentParams.forEach((val, k) => currentParams.delete(k));

                    // Keep status but remove all other filters explicitly
                    currentParams.set('status', status);
                    currentParams.delete('search');
                    currentParams.delete('plan');
                    currentParams.delete('range');
                    currentParams.delete('start_date');
                } else if (key === 'plan') {
                    currentParams.set('plan', link.dataset.filterValue);
                    currentParams.delete('range');
                } else if (key === 'range') {
                    currentParams.set('range', link.dataset.filterValue);
                    currentParams.delete('plan');
                }

                // For pagination, just add the page from its href
                if (link.closest('.pagination a')) {
                    currentParams.set('page', linkParams.get('page'));
                }

                const finalUrl = `${window.location.pathname}?${currentParams.toString()}`;
                fetchContent(finalUrl);
            });


            // Debounced listener for text/date inputs
            filterForm.addEventListener('input', (event) => {
                if (event.target.type === 'hidden') return;
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => {
                    const formData = new FormData(filterForm);
                    formData.delete('page');
                    const url = new URL(window.location.pathname, window.location.origin);
                    url.search = new URLSearchParams(formData).toString();
                    fetchContent(url.toString());
                }, 500);
            });

            window.addEventListener('popstate', () => syncUIWithURL());

            // Initial UI setup on first page load
            syncUIWithURL();
        });
    </script>

    @endsection
    @endif