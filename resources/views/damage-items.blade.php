@extends('dashboards.owner.owner')

@section('content')
<div id="alertContainer" class="fixed top-20 right-5 z-50 space-y-3"></div>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Back Button -->
    <div class="flex justify-between items-center mb-5">
        <!-- Back Button -->


        <!-- Title and Description -->
        <div class="text-left">
            <h1 class="text-lg font-semibold text-red-600">Record Damaged Item</h1>
            <p class="text-gray-600">Track and manage damaged inventory items</p>
        </div>
        <div class="flex items-center">
            <a href="{{ route('inventory-owner') }}"
                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                <span class="material-symbols-outlined text-sm mr-1">assignment_return</span>
                Back
            </a>
        </div>
    </div>

    <!-- Damage Item Form -->
    <form id="damageForm" action="{{ route('damaged.store') }}" method="POST" class="bg-white shadow-lg rounded-xl p-8 mb-10 border border-gray-100">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Product Dropdown -->
            <div>
                <label for="prod_code" class="block text-sm font-semibold text-gray-700 mb-2">
                    Product <span class="text-red-500">*</span>
                </label>
                <select name="prod_code" id="prod_code" required class="form-select text-sm w-full border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                    <option value="">Select Product</option>
                    @foreach ($products as $product)
                    <option value="{{ $product->prod_code }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Damaged Quantity -->
            <div>
                <label for="damaged_quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                    Quantity <span class="text-red-500">*</span>
                </label>
                <input type="number" name="damaged_quantity" id="damaged_quantity" required min="1" class=" text-sm form-input w-full border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Damaged Type -->
            <div>
                <label for="damaged_type" class="block text-sm font-semibold text-gray-700 mb-2">
                    Damage Type <span class="text-red-500">*</span>
                </label>
                <select name="damaged_type" id="damaged_type" required class=" text-sm form-select w-full border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                    <option value="">Select Type</option>
                    <option value="Expired">Expired</option>
                    <option value="Broken">Broken</option>
                    <option value="Spoiled">Spoiled</option>
                </select>
            </div>

            <!-- Reason -->
            <div>
                <label for="damaged_reason" class="block text-sm font-semibold text-gray-700 mb-2">
                    Reason <span class="text-red-500">*</span>
                </label>
                <textarea name="damaged_reason" id="damaged_reason" rows="3" required class=" text-sm form-textarea w-full border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all resize-none"></textarea>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <button type="button" id="cancelDamage" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                Cancel
            </button>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all font-medium shadow-md hover:shadow-lg">
                Save Record
            </button>
        </div>
    </form>

    <div class="mb-6">
        <h2 class="text-lg font-semibold text-red-600">Damage History</h2>
        <p class="text-gray-600">View and filter previous damage records</p>
    </div>

    <!-- Search Bar and Filter Dropdowns -->
    <div class="bg-white shadow-md rounded-xl p-6 mb-6 border border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search Product</label>
                <div class="relative">
                    <i class="material-symbols-rounded absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl">search</i>
                    <input type="text" id="searchInput" class=" text-sm form-input w-full pl-10 pr-4 py-2.5 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all" placeholder="Search by product name">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Damage Type</label>
                <select id="categorySelect" class="form-select w-full p-2.5 text-sm border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                    <option value="">All Types</option>
                    <option value="Expired">Expired</option>
                    <option value="Broken">Broken</option>
                    <option value="Spoiled">Spoiled</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Date</label>
                <input type="date" id="dateSelect" class=" text-sm form-input w-full p-2.5 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
            </div>
        </div>
    </div>

    <!-- Table for Damage History with Scroll and Sticky Header -->
    <!-- Table for Damage History with Scroll and Sticky Header -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">
        <div class="overflow-y-auto max-h-96">
            <table class="min-w-full table-auto table-fixed border-collapse" id="damageTable">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2 border-gray-200 w-[25%]">Product</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2 border-gray-200 w-[10%]">Quantity</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2 border-gray-200 w-[10%]">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2 border-gray-200 w-[30%]">Reason</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2 border-gray-200 w-[15%]">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2 border-gray-200 w-[10%]">Batch</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse ($damagedItems as $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 text-left">{{ $item->product_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700 text-center">{{ $item->damaged_quantity }}</td>
                        <td class="px-6 py-4 text-sm text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $item->damaged_type === 'Expired' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $item->damaged_type === 'Broken' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $item->damaged_type === 'Spoiled' ? 'bg-purple-100 text-purple-800' : '' }}">
                                {{ $item->damaged_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-left text-sm text-gray-700">{{ $item->damaged_reason }}</td>
                        <td class="px-6 py-4 text-left text-sm text-gray-600">{{ $item->damaged_date }}</td>
                        <td class="px-6 py-4 text-right text-sm text-gray-600">{{ $item->batch_number }}</td>
                    </tr>
                    @empty
                    <tr id="noDataRow">
                        <td colspan="6" class="px-6 py-6 text-center text-gray-500 text-sm italic">
                            No damaged items recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>


<script>
    document.getElementById('damageForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent the form from submitting the default way

        const form = this;
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true; // Disable the submit button after the first click

        const formData = new FormData(form);

        fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                showAlert(data.success ? 'success' : 'error', data.message);
                if (data.success) {
                    form.reset(); // Reset the form only if submission was successful
                }
                submitButton.disabled = false; // Re-enable the button after response

                // Reload the page after showing the alert (to refresh the table)
                setTimeout(() => {
                    window.location.reload(); // Reload the page to update the table with the new record
                }, 2000); // 2-second delay before reloading to allow the alert to be seen
            })
            .catch(() => {
                showAlert('error', 'Something went wrong. Please try again.');
                submitButton.disabled = false; // Re-enable the button if there's an error
            });
    });

    // Function to show alert (success/error messages)
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer');
        const alert = document.createElement('div');

        const colors = type === 'success' ?
            'bg-green-600 text-white' :
            'bg-red-600 text-white';

        alert.className = `
        px-5 py-3 rounded-lg shadow-lg flex items-center justify-between
        text-sm font-medium ${colors}
        opacity-0 transform translate-y-2
        transition-all duration-300 ease-in-out
    `;

        alert.innerHTML = `
        <span>${message}</span>
    `;

        // Append & animate in
        alertContainer.appendChild(alert);
        setTimeout(() => alert.classList.remove('opacity-0', 'translate-y-2'), 50);

        // Auto fade-out after 4s
        setTimeout(() => {
            alert.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => alert.remove(), 300);
        }, 4000);
    }
</script>

<script>
    const tableBody = document.querySelector('#damageTable tbody');

    function filterTable() {
        const search = document.getElementById('searchInput').value.toUpperCase();
        const category = document.getElementById('categorySelect').value.toUpperCase();
        const date = document.getElementById('dateSelect').value;

        let rows = Array.from(tableBody.querySelectorAll('tr'));
        let visibleCount = 0;

        rows.forEach(row => {
            // Skip the permanent “no data” placeholder
            if (row.id === 'noDataRow' || row.id === 'noResultsRow') return;

            const product = row.cells[0].textContent.toUpperCase();
            const type = row.cells[2].textContent.toUpperCase();
            const dateCell = row.cells[4].textContent.trim();

            const matchesSearch = product.includes(search) || search === '';
            const matchesCategory = type.includes(category) || category === '';
            const matchesDate = dateCell.includes(date) || date === '';

            const visible = matchesSearch && matchesCategory && matchesDate;
            row.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });

        // Remove previous “no results” message if it exists
        const existingMsg = document.getElementById('noResultsRow');
        if (existingMsg) existingMsg.remove();

        // Check if table has any real data rows (not just placeholder)
        const hasRealData = rows.some(r => r.id !== 'noDataRow');

        // If all rows hidden but there are records → show “No matching results”
        if (visibleCount === 0 && hasRealData) {
            const noRow = document.createElement('tr');
            noRow.id = 'noResultsRow';
            noRow.innerHTML = `
                <td colspan="6" class="px-6 py-6 text-center text-gray-500 text-sm italic animate-fade-in">
                    No matching results found.
                </td>
            `;
            tableBody.appendChild(noRow);
        }
    }

    // Attach filter listeners
    document.getElementById('searchInput').addEventListener('input', filterTable);
    document.getElementById('categorySelect').addEventListener('change', filterTable);
    document.getElementById('dateSelect').addEventListener('change', filterTable);
</script>





@endsection