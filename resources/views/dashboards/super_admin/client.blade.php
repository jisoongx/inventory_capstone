@extends('dashboards.super_admin.super_admin')

@section('content')
&nbsp;
<h1 class="text-2xl font-extrabold text-gray-900 mb-6 mx-6">Clients</h1>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 mx-6">
    <!-- Smaller, Rounded Search Bar -->
    <div class="flex-1">
        <input
            type="text"
            id="search"
            placeholder="Search by store name or owner name"
            autocomplete="off"
            class="w-full p-3 pl-10 text-sm text-gray-800 border border-gray-300 rounded-full bg-gray-50 focus:ring-blue-600 focus:border-blue-600 shadow-md transition-all duration-200 ease-in-out"
            style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'currentColor\'><path fill-rule=\'evenodd\' d=\'M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.307l3.093 3.093a.75.75 0 11-1.06 1.06l-3.093-3.093A7 7 0 012 9z\' clip-rule=\'evenodd\'/></svg>'); background-repeat: no-repeat; background-position: left 0.75rem center; background-size: 1.25rem;">
    </div>
    <div class="relative w-full sm:w-[180px]">
        <select id="planFilter"
            class="appearance-none w-full p-3 pl-4 pr-10 text-sm text-gray-600 border border-gray-300 rounded-full bg-gray-50 focus:ring-blue-600 focus:border-blue-600 shadow-md transition-all duration-200 ease-in-out">
            <option disabled selected value="">Select Plan</option>
            <option value="1">Basic</option>
            <option value="2">Premium</option>
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>
    <div class="relative w-full sm:w-[180px]">
        <input
            type="date"
            id="dateFilter"
            name="dateFilter"
            class="appearance-none w-full p-3 pl-4 pr-4 text-sm text-gray-600 border border-gray-300 rounded-full bg-gray-50 focus:ring-blue-600 focus:border-blue-600 shadow-md transition-all duration-200 ease-in-out" />
    </div>




    <!-- Status Filter Buttons -->
    <div class="flex flex-wrap gap-2 mt-2 md:mt-0 text-sm">
        @php
        $statuses = [
        'Pending' => 'bg-orange-500 hover:bg-orange-600',
        'Active' => 'bg-green-600 hover:bg-green-700',
        'Deactivated' => 'bg-red-500 hover:bg-red-600',
        ];
        @endphp

        @foreach ($statuses as $status => $color)
        <button
            class="status-filter-btn px-4 py-2 rounded-full text-white text-sm font-medium transition shadow-md {{ $color }}"
            data-status="{{ $status }}">
            {{ $status }}
        </button>
        @endforeach
    </div>

</div>


<div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mx-6">
    <div class="overflow-x-auto">
        <table class="min-w-full leading-normal text-sm">
            <thead>
                <tr class="bg-green-200 border-b border-emerald-200 text-sm font-normal text-gray-700 tracking-wider text-center">
                    <th class="px-6 py-3">Store Name</th>
                    <th class="px-6 py-3">Owner Name</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Date Registered</th>
                    <th class="px-6 py-3">Subscription Plan</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="client-table-body" class="bg-white divide-y divide-gray-200">
                {{-- Helper for Blade to get plan badge classes --}}


                {{-- Initial data loaded via Blade --}}
                @forelse ($clients as $client)

                <tr class="hover:bg-gray-50 transition duration-150 ease-in-out text-center" data-owner-id="{{ $client->owner_id }}">
                    <td class="px-6 py-4  text-gray-900 uppercase">{{ $client->store_name }}</td>
                    <td class="px-6 py-4  text-gray-900 uppercase">{{ $client->firstname }} {{ $client->middlename }} {{ $client->lastname }}</td>
                    <td class="px-6 py-4  text-center text-sm">
                        <span class="status-badge w-28 text-center px-3 py-1 inline-flex items-center justify-center leading-5 font-medium rounded-full
                            @if($client->status == 'Active') border border-green-600 text-green-600
                            @elseif($client->status == 'Pending') border border-orange-600 text-orange-600
                            @elseif($client->status == 'Deactivated') border border-red-600 text-red-600
                            @else border border-gray-500 text-gray-500
                            @endif">
                            {{ $client->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4  text-gray-900">

                        {{ $client->created_on ? \Carbon\Carbon::parse($client->created_on)->format('M j, Y') : '-' }}
                    </td>
                    @php
                    $planTitle = $client->subscription->planDetails->plan_title ?? '-';
                    $planClass = match($planTitle) {
                    'Basic' => 'bg-blue-500 text-white',
                    'Premium' => 'bg-purple-500 text-white',
                    default => 'bg-gray-100 text-gray-800',
                    };
                    @endphp

                    <td class="px-6 py-4">
                        @if ($planTitle !== '-')
                        <span class="w-28 px-3 py-1 inline-flex items-center justify-center leading-5 font-medium rounded-full {{ $planClass }}">
                            {{ $planTitle }}
                        </span>
                        @else
                        -
                        @endif
                    </td>




                    <td class="px-6 py-4">
                        <button type="button"
                            class="edit-status-btn text-blue-600 hover:text-blue-900 font-medium transition duration-150 ease-in-out inline-flex items-center"
                            data-owner-id="{{ $client->owner_id }}"
                            data-current-status="{{ $client->status }}">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>

                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-gray-900">No clients found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($clients->hasPages())
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end" id="pagination-links">
        {{ $clients->links() }}
    </div>
    @endif

</div>



<!-- Status Update Modal -->
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
            <button id="approveStatusBtn" data-new-status="Active"
                class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition duration-200 ease-in-out">
                <span id="approveBtnLabel">Activate</span>
            </button>

            <!-- Decline / Deactivate Button -->
            <button id="declineStatusBtn" data-new-status="Deactivated"
                class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition duration-200 ease-in-out">
                <span id="declineBtnLabel">Deactivate</span>
            </button>


        </div>
    </div>
</div>


<div id="notification" class="fixed bottom-5 right-5 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg hidden" style="min-width: 250px;">
    Status updated successfully!
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // --- Existing Search Logic (Untouched) ---
    $(document).ready(function() {
        let currentQuery = '';
        let currentStatus = '';
        let date = '';
        let selectedPlan = '';


        $('#search').on('input', function() {
            currentQuery = $(this).val();
            fetchClients();
        });

        $('.status-filter-btn').on('click', function() {
            currentStatus = $(this).data('status');
            fetchClients();
        });

        $('#dateFilter').on('change', function() {
            date = $(this).val(); // Get selected status
            fetchClients(); // Fetch with new filter
        });

        $('#planFilter').on('change', function() {
            selectedPlan = $(this).val(); // Get selected status
            fetchClients(); // Fetch with new filter
        });


        function fetchClients() {
            $.ajax({
                url: "{{ route('clients.search') }}",
                type: "GET",
                data: {
                    query: currentQuery,
                    status: currentStatus,
                    date: date,
                    plan: selectedPlan

                },
                success: function(data) {
                    let html = '';
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
                                if (status === 'Active') {
                                    bgColor = 'border border-green-600';
                                    textColor = 'text-green-600';
                                } else if (status === 'Pending') {
                                    bgColor = 'border border-orange-600';
                                    textColor = 'text-orange-600';
                                } else if (status === 'Deactivated') {
                                    bgColor = 'border border-red-600';
                                    textColor = 'text-red-600';
                                }
                                return `<span class="w-28 text-center px-3 py-1 inline-flex items-center justify-center text-sm leading-5 font-medium rounded-full ${bgColor} ${textColor}">${status}</span>`;
                            }

                            const statusBadge = getStatusBadgeHtml(client.status);
                            const formattedDate = client.created_on ? new Date(client.created_on).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            }) : '-';

                            const planId = client.subscription?.plan_id;
                            const planInfo = planMap[planId];

                            const planBadge = planInfo ?
                                `<span class="w-28 text-center px-2 py-1 inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full ${planInfo.color}">${planInfo.title}</span>` :
                                '-';

                            html += `
                            <tr class="hover:bg-gray-50 transition duration-150 ease-in-out text-sm" data-owner-id="${client.owner_id}">
                                <td class="px-6 py-4 text-gray-900 text-center uppercase">${client.store_name}</td>
                                <td class="px-6 py-4  text-gray-900 text-center uppercase">${client.firstname} ${client.middlename ?? ''} ${client.lastname}</td>
                                <td class="px-6 py-4  text-center">${statusBadge}</td>
                                <td class="px-6 py-4  text-gray-900 text-center">${formattedDate}</td>
                                <td class="px-6 py-4  text-gray-900 text-center">${planBadge}</td>
                                <td class="px-6 py-4  text-center">
                                    <button type="button"
                                            class="edit-status-btn text-blue-600 hover:text-blue-900 font-medium transition duration-150 ease-in-out inline-flex items-center"
                                            data-owner-id="${client.owner_id}"
                                            data-current-status="${client.status}">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        `;
                        });
                    } else {
                        html = `<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No clients found.</td></tr>`;
                    }

                    $('#client-table-body').html(html);
                    $('#pagination-links').hide();
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.log('Status:', status);
                    console.log('Response:', xhr.responseText);

                    $('#client-table-body').html(`
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

    let currentOwnerId = null;

    function updateStatusBadge(element, newStatus) {
        let bgColor = '';
        let textColor = '';

        if (newStatus === 'Active') {
            bgColor = 'border border-green-600';
            textColor = 'text-green-600';
        } else if (newStatus === 'Pending') {
            bgColor = 'border border-orange-600';
            textColor = 'text-orange-600';
        } else if (newStatus === 'Deactivated') {
            bgColor = 'border border-red-600';
            textColor = 'text-red-600';
        } else {
            bgColor = 'border border-gray-500';
            textColor = 'text-gray-500';
        }

        element.removeClass().addClass(`w-28 text-center px-2 py-1 inline-flex items-center justify-center text-sm font-semibold rounded-full ${bgColor} ${textColor}`).text(newStatus);
    }

    $(document).on('click', '.edit-status-btn', function() {
        currentOwnerId = $(this).data('owner-id'); // ✅ use global
        const currentStatus = $(this).data('current-status');

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
        if (currentStatus === 'Pending') {
            approveLabel.text('Approve'); // will become Active
            declineLabel.text('Decline'); // will become Declined
            approveBtn.attr('data-new-status', 'Active');
            declineBtn.attr('data-new-status', 'Declined');
        } else if (currentStatus === 'Active') {
            approveLabel.text('Activate');
            declineLabel.text('Deactivate');
            approveBtn.attr('data-new-status', 'Active');
            declineBtn.attr('data-new-status', 'Deactivated');
            // Already active, disable approve
            approveBtn.prop('disabled', true).addClass('opacity-30 cursor-not-allowed');
        } else if (currentStatus === 'Deactivated') {
            approveLabel.text('Activate');
            declineLabel.text('Deactivate');
            approveBtn.attr('data-new-status', 'Active');
            declineBtn.attr('data-new-status', 'Deactivated');
            // Already deactivated, disable decline
            declineBtn.prop('disabled', true).addClass('opacity-30 cursor-not-allowed');
        } else if (currentStatus === 'Declined') {
            approveLabel.text('Approve');
            declineLabel.text('Decline');
            approveBtn.attr('data-new-status', 'Active');
            declineBtn.attr('data-new-status', 'Declined');
            // Already declined, disable decline
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
            url: `/clients/${currentOwnerId}/status`, // Laravel route for status update
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