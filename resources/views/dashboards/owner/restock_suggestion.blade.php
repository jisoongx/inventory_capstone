@extends('dashboards.owner.owner')

@section('content')
<div class="p-6 space-y-6">

  <!-- Page Header -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
    <!-- Title + Subtitle -->
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 mb-1">Restock Suggestion List</h1>
        <p class="text-gray-600 text-sm">
            See which products customers buy most, their current stock, and suggested reorder quantities.
        </p>
    </div>

    <!-- Filter + Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4 w-full lg:w-auto mt-2 lg:mt-0">
        <!-- Filter -->
        <form method="GET" action="{{ route('restock_suggestion') }}" class="flex-1 mb-2 sm:mb-0">
            <select name="days" id="days" onchange="this.form.submit()"
                class="w-full sm:w-auto px-3 py-1 text-sm rounded-md shadow-md border-gray-300 focus:ring-gray-200">
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days (Hot sellers)</option>
                <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days (Default)</option>
                <option value="365" {{ $days == 365 ? 'selected' : '' }}>Last 12 months</option>
            </select>
        </form>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-2 justify-end w-full sm:w-auto">
            <a href="{{ route('restock.list') }}"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium shadow-md transition transform hover:-translate-y-0.5 text-sm">
                View List
            </a>

            <button type="submit" form="restockForm"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium shadow-md transition transform hover:-translate-y-0.5 text-sm">
                Finalize
            </button>

            <button type="button"
                class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md font-medium shadow-md transition transform hover:-translate-y-0.5 text-sm">
                Add Restock
            </button>
        </div>
    </div>
</div>


    <!-- Table Container -->
    <div class="bg-white shadow-md rounded-md border border-gray-200 overflow-x-auto">
        <form id="restockForm" method="POST" action="{{ route('restock.finalize') }}">
            @csrf
            <table class="w-full divide-y divide-gray-200 text-sm min-w-[700px]">
                <thead class="bg-gray-100 border-b border-gray-300 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider">Product</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider">Total Sold</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider">Current Stock</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider">Suggested Restock</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider">Priority</th>
                        <th class="px-4 py-3 text-center">
                            <input type="checkbox" id="selectAll" class="form-checkbox h-4 w-4 text-red-500">
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $priorityColors = [
                    'Urgent' => 'bg-gradient-to-r from-red-500 to-red-600 text-white',
                    'In Demand' => 'bg-gradient-to-r from-yellow-400 to-yellow-500 text-white',
                    'Low Demand' => 'bg-gradient-to-r from-blue-400 to-blue-500 text-white'
                    ];
                    @endphp

                    @foreach($products as $product)
                    @php
                    $priority = $product->stock <= 3 ? 'Urgent' : ($product->stock <= 7 ? 'In Demand' : 'Low Demand' );
                            @endphp
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors {{ $product->suggested_quantity > 0 ? 'bg-yellow-50' : '' }}">
                            <td class="px-4 py-3 text-gray-900 font-medium">{{ $product->name }}</td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $product->total_sold }}</td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $product->stock }}</td>
                            <td class="px-4 py-3 text-center text-gray-700">
                                @if($product->suggested_quantity > 0)
                                {{ $product->suggested_quantity }}
                                @else
                                <span class="text-green-600 font-medium">Sufficient Stock</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $priorityColors[$priority] }}">
                                    {{ $priority }}
                                </span>
                                <input type="hidden" name="priorities[{{ $product->inven_code }}]" value="{{ $priority }}">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" name="products[]" value="{{ $product->inven_code }}"
                                    class="productCheckbox form-checkbox h-4 w-4 text-red-500">
                                <input type="hidden" name="quantities[{{ $product->inven_code }}]" value="{{ $product->suggested_quantity }}">
                            </td>
                            </tr>
                            @endforeach
                </tbody>
            </table>
        </form>
    </div>

</div>

<!-- Success Modal -->
@if(session('success'))
<div id="successModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 text-center">
        <p class="text-gray-700 mb-4">{{ session('success') }}</p>
        <div class="flex justify-center gap-3">
            <a href="{{ route('restock.list') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium shadow-md text-sm transition">
                View List
            </a>
            <button onclick="document.getElementById('successModal').classList.add('hidden')"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md font-medium shadow-md text-sm transition">
                Close
            </button>
        </div>
    </div>
</div>
@endif

<!-- Warning Modal -->
<div id="warningModal" class="fixed inset-0 hidden flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-md shadow-lg p-6 w-96 text-center">
        <h2 class="text-lg font-semibold text-red-600 mb-2">Warning</h2>
        <p class="text-gray-700 mb-4"></p>
        <div class="flex justify-center">
            <button onclick="warningModal.classList.add('hidden'); warningModal.classList.remove('flex')"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md font-medium shadow-md text-sm transition">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.productCheckbox');
    const restockForm = document.getElementById('restockForm');
    const warningModal = document.getElementById('warningModal');

    // Select all checkboxes
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });
    }

    // Sync individual checkboxes with select all
    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (!cb.checked) selectAll.checked = false;
            else if (Array.from(checkboxes).every(c => c.checked)) selectAll.checked = true;
        });
    });

    // Prevent finalize if invalid selection
    if (restockForm) {
        restockForm.addEventListener('submit', (e) => {
            let anyChecked = false;
            let invalidSelected = false;

            checkboxes.forEach(cb => {
                const quantityInput = document.querySelector(`input[name="quantities[${cb.value}]"]`);
                const quantity = parseInt(quantityInput.value);

                if (cb.checked) {
                    anyChecked = true;
                    if (quantity <= 0) invalidSelected = true;
                }
            });

            if (!anyChecked) {
                e.preventDefault();
                warningModal.querySelector('p').textContent = 'Please select one or more product before finalizing the list.';
                warningModal.classList.remove('hidden');
                warningModal.classList.add('flex');
            } else if (invalidSelected) {
                e.preventDefault();
                warningModal.querySelector('p').textContent = 'Please select only products that need restocking.';
                warningModal.classList.remove('hidden');
                warningModal.classList.add('flex');
            }
        });
    }
</script>
@endsection