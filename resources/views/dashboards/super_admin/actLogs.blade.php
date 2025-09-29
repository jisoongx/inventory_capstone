@extends('dashboards.super_admin.super_admin')

@section('content')
<div class="bg-gradient-to-b from-white to-blue-100 min-h-screen p-4">
    <h1 class="text-lg font-semibold text-gray-900 mb-5">Activity Logs</h1>

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <!-- Search Bar -->
        <div class="flex-1">
            <input
                type="text"
                id="search"
                placeholder="Search by location or activity"
                autocomplete="off"
                class="w-full p-3 pl-10 text-sm text-gray-800 border border-gray-300 rounded-lg focus:border-indigo-500 shadow-md transition-all duration-200 ease-in-out"
                style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'currentColor\'><path fill-rule=\'evenodd\' d=\'M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.307l3.093 3.093a.75.75 0 11-1.06 1.06l-3.093-3.093A7 7 0 012 9z\' clip-rule=\'evenodd\'/></svg>'); background-repeat: no-repeat; background-position: left 0.75rem center; background-size: 1.25rem;">
        </div>

        <!-- Time Filter -->
        <div class="relative w-full sm:w-[180px]">
            <input
                type="time"
                id="timeFilter"
                name="timeFilter"
                class="appearance-none w-full p-3 pl-4 pr-10 text-sm text-gray-600 border border-gray-300 rounded-lg focus:border-indigo-500 shadow-md transition-all duration-200 ease-in-out" />
        </div>

        <!-- Date Filter -->
        <div class="relative w-full sm:w-[180px]">
            <input
                type="date"
                id="dateFilter"
                name="dateFilter"
                class="appearance-none w-full p-3 pl-4 pr-4 text-sm text-gray-600 border border-gray-300 rounded-lg focus:border-indigo-500 shadow-md transition-all duration-200 ease-in-out" />
        </div>
    </div>

    <!-- Table Container with scroll -->
    <div class="overflow-x-auto overflow-y-auto max-h-[500px] shadow-lg rounded-lg bg-white">
        <table class="min-w-full text-sm text-slate-700">
            <thead class="bg-slate-50 text-center text-slate-700 uppercase tracking-wider border-b border-gray-100 sticky top-0">
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