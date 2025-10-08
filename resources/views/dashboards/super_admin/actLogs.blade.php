@extends('dashboards.super_admin.super_admin')

@section('content')
<div class="min-h p-3">

    <!-- Filters Card -->
    <h1 class="text-xl font-semibold text-gray-900 mb-5 mt-3 ml-2">Activity Logs</h1>
    <div class="flex flex-col md:flex-row mb-5 md:items-center md:justify-between gap-4">
        <!-- Search Bar -->
        <div class="relative flex-1">
            <!-- Search Icon -->
            <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                search
            </span>

            <!-- Search Input -->
            <input
                type="text"
                id="search"
                placeholder="Search by location or activity"
                autocomplete="off"
                class="w-full p-3 pl-10 text-sm shadow-md text-gray-800 border border-gray-300 rounded-lg focus:border-indigo-500 transition-all duration-200 ease-in-out">
        </div>

        <!-- Time Filter -->
        <div class="relative w-full sm:w-[180px]">
            <input
                type="time"
                id="timeFilter"
                name="timeFilter"
                class="appearance-none w-full p-3 pl-4 pr-10 text-sm shadow-md text-gray-600 border border-gray-300 rounded-lg focus:border-indigo-500 transition-all duration-200 ease-in-out" />
        </div>

        <!-- Date Filter -->
        <div class="relative w-full sm:w-[180px]">
            <input
                type="date"
                id="dateFilter"
                name="dateFilter"
                class="appearance-none w-full p-3 pl-4 pr-4  shadow-md text-sm text-gray-600 border border-gray-300 rounded-lg focus:border-indigo-500 transition-all duration-200 ease-in-out" />
        </div>
    </div>



    <!-- Table Container with scroll -->
    <div class="overflow-x-auto overflow-y-auto max-h-[430px] shadow-lg rounded bg-white">
        <table class="min-w-full text-sm text-slate-700">
            <thead class="bg-red-50 text-center text-slate-700 uppercase tracking-wider border-b border-gray-100 sticky top-0">
                <tr>
                    <th class="px-6 py-3 font-semibold text-left">Date</th>
                    <th class="px-6 py-3 font-semibold w-[140px] text-center">Time</th>
                    <th class="px-6 py-3 font-semibold">Location</th>
                    <th class="px-6 py-3 font-semibold text-left">Activity</th>
                </tr>
            </thead>
            <tbody id="logs-table-body" class="divide-y divide-gray-100">
                @forelse ($logs as $log)
                <tr class="hover:bg-blue-50 transition-colors">
                    <td class="px-6 py-4 text-left">
                        {{ \Carbon\Carbon::parse($log->log_timestamp)->format('M j, Y') }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        @php
                        $time = \Carbon\Carbon::parse($log->log_timestamp);
                        $period = $time->format('A');
                        @endphp
                        <span class="block w-full text-center rounded-full px-3 py-1 font-medium
                            @if($period === 'AM') border border-amber-500 text-amber-500
                            @elseif($period === 'PM') border border-blue-600 text-blue-700 @endif">
                            {{ $time->format('g:i A') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $log->log_location ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-gray-600 text-left">{{ $log->log_type }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-gray-500 text-center">No activity logs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search');
        const timeInput = document.getElementById('timeFilter');
        const dateInput = document.getElementById('dateFilter');
        const tableBody = document.getElementById('logs-table-body');

        let currentQuery = '';
        let time = '';
        let date = '';

        function fetchClients() {
            const params = new URLSearchParams({
                query: currentQuery,
                time: time,
                date: date
            });

            fetch("{{ route('actlogs.search') }}?" + params.toString())
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    if (data.length > 0) {
                        data.forEach(logs => {
                            const formattedDate = logs.log_timestamp ?
                                new Date(logs.log_timestamp).toLocaleDateString('en-US', {
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
                            const period = timeObj.getHours() < 12 ? 'AM' : 'PM';
                            const timeColor = period === 'AM' ? 'border border-amber-500 text-amber-500' : 'border border-blue-500 text-blue-600';

                            html += `
                            <tr class="hover:bg-blue-50 transition duration-150 ease-in-out">
                                <td class="px-6 py-4 text-sm text-gray-900 text-left">${formattedDate}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-center">
                                    <span class="block w-full text-center rounded-full px-3 py-1 ${timeColor}">
                                        ${formattedTime}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-center">${logs.log_location ?? 'N/A'}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-left">${logs.log_type}</td>
                            </tr>`;
                        });
                    } else {
                        html = `<tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No logs found.</td></tr>`;
                    }
                    tableBody.innerHTML = html;
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    tableBody.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Error loading data.</td></tr>`;
                });
        }

        searchInput.addEventListener('input', function() {
            currentQuery = this.value;
            fetchClients();
        });

        timeInput.addEventListener('change', function() {
            time = this.value;
            fetchClients();
        });

        dateInput.addEventListener('change', function() {
            date = this.value;
            fetchClients();
        });

        fetchClients();
    });
</script>
@endsection