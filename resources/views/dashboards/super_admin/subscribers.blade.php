@extends('dashboards.super_admin.super_admin')

@section('content')
&nbsp;
<div class="mb-6 flex flex-col md:flex-row md:items-start md:justify-between gap-4 mx-6">
    <!-- Left Column: Title, Search & Dropdown -->
    <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Subscription Plan Management</h1>

        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <input type="text" id="search" placeholder="Search by store name or owner name"
                autocomplete="off"
                class="w-full sm:w-[360px] p-3 pl-10 text-sm text-gray-800 border border-gray-300 rounded-full bg-gray-50 focus:ring-blue-600 focus:border-blue-600 shadow-md transition-all duration-200 ease-in-out"
                style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'currentColor\'><path fill-rule=\'evenodd\' d=\'M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.307l3.093 3.093a.75.75 0 11-1.06 1.06l-3.093-3.093A7 7 0 012 9z\' clip-rule=\'evenodd\'/></svg>'); background-repeat: no-repeat; background-position: left 0.75rem center; background-size: 1.25rem;" />

            <!-- Status Filter -->
            <div class="relative w-full sm:w-[220px]">
                <select id="statusFilter"
                    class="appearance-none w-full p-3 pl-4 pr-10 text-sm text-gray-600 border border-gray-300 rounded-full bg-gray-50 focus:ring-blue-600 focus:border-blue-600 shadow-md transition-all duration-200 ease-in-out">
                    <option disabled selected value="">Select Status</option>
                    <option value="paid">Paid</option>
                    <option value="expired">Expired</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <!-- Date Filter -->
            <div class="relative w-full sm:w-[180px]">
                <input type="date" id="dateFilter" name="dateFilter"
                    class="appearance-none w-full p-3 pl-4 pr-4 text-sm text-gray-600 border border-gray-300 rounded-full bg-gray-50 focus:ring-blue-600 focus:border-blue-600 shadow-md transition-all duration-200 ease-in-out" />
            </div>
        </div>
    </div>

    <!-- Right Column: Plan Buttons -->
    <div class="flex gap-2 mt-4 md:mt-10">
        <button type="button" class="filter-plan w-36 bg-blue-500 text-sm text-white font-semibold py-2 px-4 rounded-xl shadow-md hover:bg-blue-200 transition-all duration-200" data-plan="1">
            BASIC<br>
            <span class="text-lg font-bold">₱250</span><br>
            <span class="text-xs font-normal text-white">for 6 months</span>
        </button>
        <button type="button" class="filter-plan w-36 bg-purple-500 text-sm text-white font-semibold py-2 px-4 rounded-xl shadow-md hover:bg-purple-300 transition-all duration-200" data-plan="2">
            PREMIUM<br>
            <span class="text-lg font-bold">₱500</span><br>
            <span class="text-xs font-normal text-white">for 1 year</span>
        </button>
    </div>
</div>

@if($clients->count())
<div class="overflow-x-auto bg-white shadow-md rounded-lg mx-6">
    <table class="min-w-full table-auto border-collapse">
        <thead class="bg-green-200 text-sm font-medium text-gray-700 tracking-wider text-center">
            <tr>
                <th class="px-6 py-3">Store Name</th>
                <th class="px-6 py-3">Owner Name</th>
                <th class="px-6 py-3">Subscription Plan</th>
                <th class="px-6 py-3 text-center">Status</th>
                <th class="px-6 py-3">Start Date</th>
                <th class="px-6 py-3">Expiry Date</th>
                <th class="px-6 py-3">Days Left</th>
                <th class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="text-sm text-gray-800 text-center">
            @foreach($clients as $client)
            @foreach($client->subscriptions as $subscription)
            @php
            $start = \Carbon\Carbon::parse($subscription->subscription_start ?? now());
            $end = \Carbon\Carbon::parse($subscription->subscription_end ?? now());
            $daysLeft = now()->diffInDays($end, false);

            $planTitle = $subscription->planDetails->plan_title ?? '-';
            $planClass = match($planTitle) {
            'Basic' => 'bg-blue-500 text-white',
            'Premium' => 'bg-purple-500 text-white',
            default => 'bg-gray-100 text-gray-800',
            };

            $subStatus = $subscription->status;
            $statusClass = match($subStatus) {
            'paid' => 'border border-green-600 text-green-600',
            'expired' => 'border border-red-600 text-red-600',
            default => 'bg-gray-100 border border-gray-400 text-gray-600'
            };
            @endphp
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-4 uppercase">{{ $client->store_name }}</td>
                <td class="px-6 py-4 uppercase">{{ $client->firstname }} {{ $client->middlename ?? '' }} {{ $client->lastname }}</td>

                <td class="px-6 py-4">
                    @if ($planTitle !== '-')
                    <span class="w-24 px-2 py-1 inline-flex items-center justify-center font-medium leading-5 rounded-full {{ $planClass }}">
                        {{ $planTitle }}
                    </span>
                    @else
                    -
                    @endif
                </td>

                <td class="px-6 py-4">
                    <span class="w-24 px-2 py-1 inline-flex items-center justify-center font-medium leading-5 rounded-full {{ $statusClass }}">
                        {{ $subStatus }}
                    </span>
                </td>

                <td class="px-6 py-4">{{ $subscription->subscription_start ? \Carbon\Carbon::parse($subscription->subscription_start)->format('M j, Y') : '-' }}</td>
                <td class="px-6 py-4">{{ $subscription->subscription_end ? \Carbon\Carbon::parse($subscription->subscription_end)->format('M j, Y') : '-' }}</td>

                <td class="px-6 py-4">
                    @if ($daysLeft < 0)
                        <span class="text-red-500">Expired</span>
                        @else
                        {{ floor($daysLeft) }} day{{ floor($daysLeft) !== 1 ? 's' : '' }}
                        @endif
                </td>


                <td class="px-6 py-4">
                    <button type="button"
                        class="edit-status-btn text-blue-600 hover:text-blue-900 font-medium transition duration-150 ease-in-out inline-flex items-center"
                        data-owner-id="{{ $client->owner_id }}"
                        data-current-status="{{ $subscription->status }}">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                </td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $clients->links() }}
</div>
@else
<p class="text-gray-500 text-center">No active subscribers found.</p>
@endif

<div id="statusUpdateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white w-full max-w-md mx-auto rounded-xl shadow-2xl p-6 relative animate-fadeIn">

        <!-- Modal Header -->
        <div class="flex justify-end mb-6">
            <button id="cancelStatusBtn" class="text-gray-400 hover:text-gray-600 transition duration-200 ease-in-out">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>


        <!-- Client Info -->
        <div class="space-y-4 mb-6">
            <div class="flex justify-between text-sm text-gray-700">
                <span class="font-medium">Client ID:</span>
                <span id="modalClientId" class="font-semibold text-gray-900"></span>
            </div>
            <div class="flex justify-between text-sm text-gray-700 items-center">
                <span class="font-medium">Current Status:</span>
                <span id="modalCurrentStatus"
                    class="font-semibold text-sm px-3 py-1 rounded-full bg-gray-100 text-gray-800 inline-block">
                </span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col gap-3">
            <!-- Approve / Activate Button -->
            <button id="approveStatusBtn" data-new-status="paid"
                class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition duration-200 ease-in-out">
                <span id="approveBtnLabel">Paid</span>
            </button>

            <!-- Decline / Deactivate Button -->
            <button id="declineStatusBtn" data-new-status="expired"
                class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition duration-200 ease-in-out">
                <span id="declineBtnLabel">Expired</span>
            </button>


        </div>
    </div>
</div>


<div id="notification" class="fixed bottom-5 right-5 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg hidden" style="min-width: 250px;">
    Status updated successfully!
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let currentQuery = '';
        let currentPlan = '';
        let currentStatus = '';
        let date = '';

        // When typing in search box
        $('#search').on('input', function() {
            currentQuery = $(this).val();
            fetchClients();
        });

        // When clicking BASIC or PREMIUM buttons
        $('.filter-plan').on('click', function() {
            currentPlan = $(this).data('plan');
            fetchClients();
        });

        $('#statusFilter').on('change', function() {
            currentStatus = $(this).val(); // Get selected status
            fetchClients(); // Fetch with new filter
        });

        $('#dateFilter').on('change', function() {
            date = $(this).val(); // Get selected status
            fetchClients(); // Fetch with new filter
        });


        function fetchClients() {
            $.ajax({
                url: "{{ route('clients.sub_search') }}",
                type: "GET",
                data: {
                    query: currentQuery,
                    plan: currentPlan,
                    status: currentStatus,
                    date: date


                },
                success: function(data) {
                    let tbody = '';
                    const planMap = {
                        1: {
                            title: 'Basic',
                            color: 'bg-blue-500 text-white'
                        },
                        2: {
                            title: 'Premium',
                            color: 'bg-purple-500 text-white'
                        }
                    };

                    if (data.length > 0) {
                        data.forEach(client => {
                            function getStatusBadgeHtml(status) {
                                let bgColor = 'border border-gray-500';
                                let textColor = 'text-gray-500';
                                if (status === 'paid') {
                                    bgColor = 'border border-green-600';
                                    textColor = 'text-green-600';
                                } else if (status === 'expired') {
                                    bgColor = 'border border-red-600';
                                    textColor = 'text-red-600';
                                }
                                return `
                                <span class="w-24 text-center px-3 py-1 inline-flex items-center justify-center text-sm leading-5 font-medium rounded-full ${bgColor} ${textColor}">
                                    ${status}
                                </span>
                            `;
                            }
                            const statusBadge = getStatusBadgeHtml(client.subscription.status);
                            const plan = client.subscription?.plan_details?.plan_title ?? '-';

                            const start = client.subscription?.subscription_start ? new Date(client.subscription.subscription_start).toLocaleDateString('en-US', {
                                month: 'short',
                                day: 'numeric',
                                year: 'numeric'
                            }) : '-';
                            const end = client.subscription?.subscription_end ? new Date(client.subscription.subscription_end).toLocaleDateString('en-US', {
                                month: 'short',
                                day: 'numeric',
                                year: 'numeric'
                            }) : '-';

                            let daysLeftText = '-';
                            if (client.subscription?.subscription_end) {
                                const endDate = new Date(client.subscription.subscription_end);
                                const now = new Date();
                                const oneDay = 1000 * 60 * 60 * 24;
                                endDate.setHours(0, 0, 0, 0);
                                now.setHours(0, 0, 0, 0);
                                const diff = Math.floor((endDate.getTime() - now.getTime()) / oneDay);


                                if (diff < 0) {
                                    daysLeftText = '<span class="text-red-500">Expired</span>';
                                } else {
                                    daysLeftText = `${diff} day${diff !== 1 ? 's' : ''}`;
                                }
                            }

                            const planId = client.subscription?.plan_id;
                            const planInfo = planMap[planId];

                            const planBadge = planInfo ?
                                `<span class="w-24 text-center px-2 py-1 inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full ${planInfo.color}">
                                    ${planInfo.title}
                                </span>` : '-';

                            tbody += `
                                <tr class="border-b hover:bg-gray-50 text-sm">
                                    <td class="px-6 py-4  text-center uppercase">${client.store_name}</td>
                                    <td class="px-6 py-4  text-center uppercase">${client.firstname} ${client.middlename ?? ''} ${client.lastname}</td>
                                    <td class="px-6 py-4 text-sm text-center">${planBadge}</td>
                                    <td class="px-6 py-4">${statusBadge}</td>
                                    <td class="px-6 py-4  text-gray-900 text-center">${start}</td>
                                    <td class="px-6 py-4  text-gray-900 text-center">${end}</td>
                                    <td class="px-6 py-4  text-center">${daysLeftText}</td>
                                    <td class="px-6 py-4  text-center">
                                        <button type="button"
                                            class="edit-status-btn text-blue-600 hover:text-blue-900 font-medium transition duration-150 ease-in-out inline-flex items-center"
                                            data-owner-id="{{ $client->owner_id }}"
                                            data-current-status="{{ $subscription->status }}">
                                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>

                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        tbody = `
                            <tr>
                                <td colspan="8" class="text-center px-6 py-4 text-sm text-gray-500">
                                    No results found.
                                </td>
                            </tr>
                        `;
                    }

                    $('tbody').html(tbody);
                }
            });
        }
    });

    // --- New Status Update Logic ---
    let currentOwnerId = null; // Store the ID of the client being edited

    // Function to update status badge styling
    function updateStatusBadge(element, newStatus) {
        let bgColor = '';
        let textColor = '';

        if (newStatus === 'Paid') {
            bgColor = 'border border-green-600';
            textColor = 'text-green-600';
        } else if (newStatus === 'Expired') {
            bgColor = 'border border-red-600';
            textColor = 'text-red-600';
        } else {
            bgColor = 'border border-gray-500';
            textColor = 'text-gray-500';
        }

        element.removeClass().addClass(`w-24 text-center px-2 py-1 inline-flex items-center justify-center text-sm font-semibold rounded-full ${bgColor} ${textColor}`).text(newStatus);
    }

    // Open Modal
    $(document).on('click', '.edit-status-btn', function() {
        currentOwnerId = $(this).data('owner-id');
        let currentStatus = $(this).data('current-status');

        $('#modalClientId').text(currentOwnerId);
        $('#modalCurrentStatus').text(currentStatus);
        updateStatusBadge($('#modalCurrentStatus'), currentStatus);

        // Buttons and labels
        const approveBtn = $('#approveStatusBtn');
        const declineBtn = $('#declineStatusBtn');
        const approveLabel = $('#approveBtnLabel');
        const declineLabel = $('#declineBtnLabel');

        // Reset buttons
        approveBtn.prop('disabled', false).removeClass('opacity-30 cursor-not-allowed');
        declineBtn.prop('disabled', false).removeClass('opacity-30 cursor-not-allowed');

        // Set button labels based on current status
        if (currentStatus === 'paid') {
            approveLabel.text('Paid');
            declineLabel.text('Expired');
            approveBtn.attr('data-new-status', 'paid');
            declineBtn.attr('data-new-status', 'expired');
            // Already active, disable approve
            approveBtn.prop('disabled', true).addClass('opacity-30 cursor-not-allowed');
        } else if (currentStatus === 'expired') {
            approveLabel.text('Paid');
            declineLabel.text('Expired');
            approveBtn.attr('data-new-status', 'paid');
            declineBtn.attr('data-new-status', 'expired');
            // Already deactivated, disable decline
            declineBtn.prop('disabled', true).addClass('opacity-30 cursor-not-allowed');
        }

        $('#statusUpdateModal').removeClass('hidden').addClass('flex');
    });


    // Close Modal
    $('#cancelStatusBtn').on('click', function() {
        $('#statusUpdateModal').removeClass('flex').addClass('hidden');
        currentOwnerId = null; // Clear current ID
    });

    // Handle Status Update (Approve/Decline)
    $('#approveStatusBtn, #declineStatusBtn').on('click', function() {
        const newStatus = $(this).data('new-status');

        if (!currentOwnerId) {
            alert('Error: No client selected.');
            return;
        }

        // Show a loading indicator if desired
        // For example, disable buttons and change text

        $.ajax({
            url: `/subs/${currentOwnerId}/status`, // Laravel route for status update
            type: "PUT", // Use PUT method for updating a resource
            data: {
                _token: '{{ csrf_token() }}', // Include CSRF token for PUT/POST requests
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    $('#statusUpdateModal').removeClass('flex').addClass('hidden'); // Close modal

                    // Find the specific row and update its status badge
                    const $row = $(`tr[data-owner-id="${currentOwnerId}"]`);
                    const $statusCell = $row.find('.status-badge');

                    if ($statusCell.length) {
                        updateStatusBadge($statusCell, response.new_status);
                    }

                    // Show success notification
                    $('#notification').text('Status updated to ' + response.new_status + ' successfully!').removeClass('bg-red-500 hidden').addClass('bg-green-500 flex');
                    setTimeout(() => {
                        $('#notification').removeClass('flex').addClass('hidden');
                        location.reload();
                    }, 2000); // Hide after 3 seconds

                } else {
                    alert('Failed to update status: ' + (response.message || 'Unknown error'));
                    // Show error notification
                    $('#notification').text('Failed to update status: ' + (response.message || 'Unknown error')).removeClass('bg-green-500 hidden').addClass('bg-red-500 flex');
                    setTimeout(() => {
                        $('#notification').removeClass('flex').addClass('hidden');
                        location.reload();
                    }, 5000);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error, xhr.responseText);
                const errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred during status update.';
                alert(errorMessage);
                // Show error notification
                $('#notification').text(errorMessage).removeClass('bg-green-500 hidden').addClass('bg-red-500 flex');
                setTimeout(() => {
                    $('#notification').removeClass('flex').addClass('hidden');
                    location.reload();
                }, 5000);
            },
            complete: function() {
                // Re-enable buttons, hide loading indicator
                currentOwnerId = null; // Clear current ID
            }
        });
    });
</script>

@endsection