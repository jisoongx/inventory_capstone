@extends('dashboards.owner.owner')

@section('content')
&nbsp;

@if($expired || $plan === 3 || $plan === 1)
    <div class="ml-64 absolute inset-0 flex items-center justify-center z-10">
        <div class="bg-white rounded-lg shadow-2xl border border-red-200 overflow-hidden max-w-[35rem] mx-4">

            <div class="px-8 py-12 text-center relative">
                <div class="absolute inset-0 overflow-hidden opacity-5">
                    <div class="absolute top-5 left-5 w-24 h-24 bg-red-600 rounded-full blur-2xl"></div>
                    <div class="absolute bottom-5 right-5 w-32 h-32 bg-rose-600 rounded-full blur-2xl"></div>
                </div>

                <div class="relative z-10">
                    <div class="relative inline-block mb-4">
                        <div class="absolute inset-0 bg-amber-500/30 rounded-full blur-2xl animate-pulse"></div>
                        <div class="relative w-16 h-16 bg-gradient-to-br from-orange-600 to-rose-600 rounded-full p-4 shadow-2xl flex items-center justify-center">
                            <span class="material-symbols-rounded-semibig text-white">diamond</span>
                        </div>
                        <div class="absolute -top-1 -right-1">
                            <svg class="w-6 h-6 text-amber-400 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>

                    <h2 class="text-xl md:text-md font-bold text-slate-800 mb-3">
                        Activity Logs Available on Premium Plans
                    </h2>

                    <p class="text-slate-600 text-xs xs:text-base leading-relaxed mb-6">
                        Upgrade your subscription to access detailed activity logs. 
                        Track user actions, monitor login history, and review all system 
                        activities with timestamps and location data to better understand 
                        your business operations.
                    </p>

                    <div class="flex flex-wrap items-center justify-center gap-3 mb-8 text-xs md:text-sm text-slate-700">
                        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full shadow-sm border border-red-100">
                            <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs font-medium">Real-time Tracking</span>
                        </div>
                        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full shadow-sm border border-red-100">
                            <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs font-medium">Export Reports</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                        <a href="" 
                            class="inline-flex items-center justify-center px-6 py-3 bg-white text-slate-700 font-semibold rounded-lg hover:bg-red-50 border-2 border-red-200 hover:border-red-300 transition-all duration-200 shadow-sm hover:shadow-md text-sm">
                            <span class="text-xs">View Plans</span>
                        </a>
                        <a href="" 
                            class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-orange-600 to-rose-600 text-white font-semibold rounded-lg hover:from-red-700 hover:to-rose-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl group text-sm">
                            <span class="text-xs">Upgrade to Premium</span>
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>

                    <div class="mt-6 pt-4 border-t border-red-100">
                        <div class="flex flex-wrap items-center justify-center gap-4 text-xs text-slate-500">
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-rounded-smaller text-red-500">encrypted</span>
                                <span>Secure payments</span>
                            </span>
                            <span class="text-red-200">•</span>
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-rounded-smaller text-red-500">check_circle</span>
                                <span>Instant activation</span>
                            </span>
                            <span class="text-red-200">•</span>
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-rounded-smaller text-red-500">handshake</span>
                                <span>24/7 support</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="relative mx-6">
    <div class="{{ ($expired || $plan === 3 || $plan === 1) ? 'blur-sm pointer-events-none select-none' : '' }}">
        <div class="flex justify-between items-center mx-5">
            <h1 class="text-xl font-semibold text-gray-900 mb-5 ml-2">Activity Logs</h1>
            <a href="{{ route('staffLogs') }}" class="text-blue-500 hover:text-blue-700 underline text-sm">Staff Activity Logs</a>
        </div>

        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 mx-6">
            <div class="relative flex-1">
                <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-[20px]">
                    search
                </span>
                <input
                    type="text"
                    id="search"
                    placeholder="Search by location or activity"
                    autocomplete="off"
                    class="w-full p-3 pl-11 text-sm text-gray-800 border border-gray-300 rounded-md bg-white focus:border-indigo-500 shadow-md transition-all duration-200 ease-in-out">
            </div>
            <div class="relative w-full sm:w-[180px]">
                <input
                    type="time"
                    id="timeFilter"
                    name="timeFilter"
                    class="appearance-none w-full p-3 pl-4 pr-10 text-sm text-gray-600 border border-gray-300 rounded-lg focus:ring-gray-300 focus:border-gray-500 shadow-md transition-all duration-200 ease-in-out" />

            </div>
            <div class="relative w-full sm:w-[180px]">
                <input
                    type="date"
                    id="dateFilter"
                    name="dateFilter"
                    class="appearance-none w-full p-3 pl-4 pr-4 text-sm text-gray-600 border border-gray-300 rounded-lg focus:ring-gray-300 focus:border-gray-500 shadow-md transition-all duration-200 ease-in-out" />
            </div>
        </div>
    </div>


    <div class="overflow-x-auto bg-white shadow-md rounded-md {{ ($expired || $plan === 3 || $plan === 1) ? 'blur-sm pointer-events-none select-none' : '' }}">
        <div class="max-h-[430px] overflow-y-auto">
            <table class="min-w-full text-sm text-center text-slate-700">
                <thead class="bg-red-50 text-center uppercase tracking-wider border-b border-gray-100 sticky top-0">
                    <tr>
                        <th class="px-6 py-3 font-semibold text-left">Date</th>
                        <th class="px-6 py-3 font-semibold">Time</th>
                        <th class="px-6 py-3 font-semibold">Location</th>
                        <th class="px-6 py-3 text-left font-semibold">Activity</th>
                    </tr>
                </thead>
                <tbody id="logs-table-body" class="divide-y divide-gray-200">
                    @forelse ($logs as $log)
                    <tr class="hover:bg-blue-50 transition-colors">
                        <td class="px-6 py-4 text-left">
                            {{ \Carbon\Carbon::parse($log->log_timestamp)->format('M j, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            @php
                            $time = \Carbon\Carbon::parse($log->log_timestamp);
                            $period = $time->format('A'); // AM or PM
                            @endphp
                            <span class="rounded-full px-3 py-1 inline-block font-medium
                                @if($period === 'AM')
                                    border border-amber-500 text-amber-500
                                @elseif($period === 'PM')
                                    border border-blue-600 text-blue-700
                                @endif">
                                {{ $time->format('g:i A') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $log->log_location ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-gray-600 text-left">
                            {{ $log->log_type }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-gray-500">
                            No activity logs found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let currentQuery = '';
        let time = '';
        let date = '';

        $('#search').on('input', function() {
            currentQuery = $(this).val();
            fetchClients();
        });



        $('#timeFilter').on('change', function() {
            time = $(this).val();
            fetchClients();
        });


        $('#dateFilter').on('change', function() {
            date = $(this).val(); // Get selected status
            fetchClients(); // Fetch with new filter
        });


        function fetchClients() {
            $.ajax({
                url: "{{ route('actlogs.search') }}",
                type: "GET",
                data: {
                    query: currentQuery,
                    time: time,
                    date: date,


                },
                success: function(data) {
                    let html = '';

                    if (data.length > 0) {
                        data.forEach(logs => {
                            const formattedDate = logs.log_timestamp ? new Date(logs.log_timestamp).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            }) : '-';

                            const timeObj = new Date(logs.log_timestamp);
                            const formattedTime = timeObj.toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: true
                            });

                            const hours = timeObj.getHours();
                            const period = hours < 12 ? 'AM' : 'PM';

                            const timeColor = period === 'AM' ?
                                'border border-amber-500 text-amber-500' :
                                'border border-blue-500 text-blue-600';


                            html += `
           <tr class="hover:bg-blue-50 transition duration-150 ease-in-out" data-log-id="${logs.log_id}">
                <td class="px-6 py-4 text-sm text-gray-900 text-left">${formattedDate}</td>
                <td class="px-6 py-4 text-sm text-gray-900 text-center">
                    <span class="rounded-full px-3 py-1 inline-block ${timeColor}">
                        ${formattedTime}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 text-center">${logs.log_location ?? 'N/A'}</td>
                <td class="px-6 py-4 text-sm text-gray-900 text-left">${logs.log_type}</td>
            </tr>
        `;
                        });
                    } else {
                        html = `<tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No logs found.</td></tr>`;
                    }

                    $('#logs-table-body').html(html);
                    $('#pagination-links').hide();
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.log('Status:', status);
                    console.log('Response:', xhr.responseText);

                    $('#logs-table-body').html(`
        <tr>
            <td colspan="6" class="px-6 py-4 text-center text-red-500">
                Error loading data. Check console for details.
            </td>
        </tr>
    `);
                }

            });
        }

        // Call it initially to load default data
        fetchClients();
    });
</script>
@endsection