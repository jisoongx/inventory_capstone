@extends('dashboards.owner.owner')

@section('content')
&nbsp;
<div class="flex justify-between items-center mx-5">
    <h1 class="text-xl font-semibold text-gray-900 mb-5 ml-2">Activity Logs</h1>
    <a href="{{ route('staffLogs') }}" class="text-blue-500 hover:text-blue-700 underline text-sm">Staff Activity Logs</a>
</div>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 mx-6">
    <!-- Smaller, Rounded Search Bar -->
    <div class="relative flex-1">
        <!-- Search Icon -->
        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-[20px]">
            search
        </span>

        <!-- Search Input -->
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
<div class="overflow-x-auto bg-white shadow-md rounded-md mx-6">
    <div class="max-h-[430px] overflow-y-auto"> <!-- 👈 fixed height + vertical scroll -->
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