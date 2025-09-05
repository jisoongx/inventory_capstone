@extends('dashboards.owner.owner')

@section('content')
<div class="p-6 space-y-6">

    <!-- Page Title -->
    <h1 class="text-2xl font-semibold text-gray-900 mb-2">Restock Suggestion List</h1>
    <p class="text-gray-600 mb-4 text-sm">
        Check which products are running low and keep your store ready for customers.
    </p>

    <!-- Action Buttons -->
    <div class="flex justify-end mb-4 gap-3">
        <a href="{{ route('restock.list') }}"
            class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-md font-medium shadow-md transition transform hover:-translate-y-0.5 text-sm">
            View List
        </a>

        <button type="submit" form="restockForm"
            class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-md font-medium shadow-md transition transform hover:-translate-y-0.5 text-sm">
            Finalize List
        </button>

        <button type="button"
            class="bg-black hover:bg-gray-800 text-white px-5 py-2 rounded-md font-medium shadow-md transition transform hover:-translate-y-0.5 text-sm">
            Add Restock
        </button>
    </div>

    <!-- Table Container -->
    <div class="bg-gray-50 rounded-xl shadow-lg border border-gray-200">
        <form id="restockForm" method="POST" action="{{ route('restock.finalize') }}">
            @csrf
            <table class="w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100 border-b border-gray-300 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider">Product</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider">Quantity Left</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider">Suggested Restock</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider">Priority</th>
                        <th class="px-4 py-3 text-left">
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
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-900 font-medium">{{ $product->name }}</td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $product->stock }}</td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $product->stock_limit ?? 10 }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $priorityColors[$priority] }}">
                                    {{ $priority }}
                                </span>
                                <input type="hidden" name="priorities[{{ $product->inven_code }}]" value="{{ $priority }}">
                            </td>
                            <td class="px-4 py-3">
                                <input type="checkbox" name="products[]" value="{{ $product->inven_code }}" class="productCheckbox form-checkbox h-4 w-4 text-red-500">
                                <input type="hidden" name="quantities[{{ $product->inven_code }}]" value="{{ $product->stock_limit ?? 10 }}">
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
        <h2 class="text-lg font-semibold text-gray-900 mb-2">✅ Success</h2>
        <p class="text-gray-700 mb-4">{{ session('success') }}</p>
        <div class="flex justify-center gap-3">
            <a href="{{ route('restock.list') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium shadow-md text-sm transition">
                Go to Restock List
            </a>
            <button onclick="document.getElementById('successModal').style.display='none'"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md font-medium shadow-md text-sm transition">
                Close
            </button>
        </div>
    </div>
</div>
@endif

<!-- Scripts -->
<script>
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.productCheckbox');

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (!cb.checked) selectAll.checked = false;
            else if (Array.from(checkboxes).every(c => c.checked)) selectAll.checked = true;
        });
    });
</script>
@endsection