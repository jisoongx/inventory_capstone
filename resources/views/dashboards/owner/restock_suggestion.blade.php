@extends('dashboards.owner.owner')

@section('content')

@once
@push('styles')
<style>
    /* Custom styles from your original code */
    .modal-backdrop {
        transition: opacity 0.3s ease-in-out;
    }

    .modal-content {
        transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
    }

    .modal.hidden .modal-backdrop {
        opacity: 0;
    }

    .modal.hidden .modal-content {
        opacity: 0;
        transform: scale(0.95);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-slide-down {
        animation: slideDown 0.3s ease-out;
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-success {
        background-color: #dcfce7;
        color: #166534;
    }

    .badge-warning {
        background-color: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background-color: #fee2e2;
        color: #991b1b;
    }

    tbody tr {
        transition: all 0.2s ease;
    }

    tbody tr:hover {
        transform: translateX(2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    input:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    * {
        transition-property: background-color, border-color, color, fill, stroke;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 150ms;
    }
</style>
@endpush
@endonce

<div class="px-3 sm:px-4 lg:px-6 py-4 space-y-6 animate-slide-down">



    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-lg font-semibold text-green-800 flex items-center gap-2">

                Restock Suggestions
            </h1>
            <p class=" text-slate-500 mt-1 text-sm">
                Review product sales and stock levels to create your next restock order.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('restock.list') }}" class="inline-flex items-center shadow-md justify-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition ">
                <span class="material-symbols-rounded mr-2 text-lg">list_alt</span>
                View Lists
            </a>
            <button type="submit" form="restockForm" class="inline-flex items-center shadow-lg justify-center px-5 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 transition">
                <span class="material-symbols-rounded mr-2 text-lg">fact_check</span>
                Confirm Selection
            </button>
        </div>
    </div>

    <div class="bg-white shadow-md rounded p-4 border-t-4 border-green-300">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative ">
                <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input type="text" id="searchInput" placeholder="Search products..." class="w-full pl-10 pr-4 py-2.5 border  border-slate-300 rounded-lg text-sm focus:border-red-500 focus:ring-2 focus:ring-red-100">
            </div>
            <div class="flex gap-2">
                <select id="categoryFilter" class="px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:border-red-500 focus:ring-2 focus:ring-red-100">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->category_id }}">{{ $category->category }}</option>
                    @endforeach
                </select>
                <button id="clearFilters" class="px-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-600 hover:bg-slate-50 flex items-center gap-1">
                    <span class="material-symbols-rounded text-lg">clear_all</span>
                    Clear
                </button>
            </div>
        </div>
        <div id="resultsCount" class="mt-3 text-sm text-slate-600"></div>
    </div>

    <div class="bg-white rounded shadow-md overflow-hidden">
        <form id="restockForm" method="POST" action="{{ route('restock.finalize') }}">
            @csrf
            <div class="overflow-x-auto custom-scrollbar max-h-[352px] min-h-[350px] overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class=" text-gray-700 sticky uppercase top-0sticky top-0 border-b border-slate-200 bg-green-100">
                        <tr>
                            <th class="p-4 text-center w-12">
                                <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                            </th>
                            <th class="p-4 text-left font-semibold">Product</th>
                            <th class="p-4 text-center font-semibold">Cost Price</th>
                            <th class="p-4 text-center font-semibold">Total Sold</th>
                            <th class="p-4 text-center font-semibold">Current Stock</th>
                            <th class="p-4 text-center font-semibold">Suggested Restock</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody" class="divide-y divide-slate-100">
                        @forelse($products as $product)
                        <tr class="hover:bg-blue-50 transition-all product-row"
                            data-product-name="{{ $product->name }}"
                            data-category-id="{{ $product->category_id }}"
                            data-cost="{{ $product->cost_price }}"
                            data-quantity="{{ $product->suggested_quantity }}">
                            <td class="p-4 text-center">
                                <input type="checkbox" name="products[]" value="{{ $product->inven_code }}" class="productCheckbox rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                                <input type="hidden" name="quantities[{{ $product->inven_code }}]" value="{{ $product->suggested_quantity }}">
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-lg flex items-center justify-center">
                                        <span class="material-symbols-rounded text-lg">inventory</span>
                                    </div>
                                    <span class="font-medium text-slate-800">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <span class="badge badge-success">₱{{ number_format($product->cost_price, 2) }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="badge badge-success">{{ $product->total_sold }} sold</span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="font-semibold text-slate-700">{{ $product->stock }}</span>
                                @if($product->stock <= 10)
                                    <span class="material-symbols-rounded text-red-500 text-sm ml-1 align-middle">warning</span>
                                    @elseif($product->stock <= 50)
                                        <span class="material-symbols-rounded text-yellow-500 text-sm ml-1 align-middle">info</span>
                                        @endif
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center justify-center px-3 py-1 bg-red-50 text-red-700 font-bold rounded-lg border border-red-200">
                                    {{ $product->suggested_quantity }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyState">
                            <td colspan="6" class="text-center p-12">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                        <span class="material-symbols-rounded text-slate-400 text-4xl">inventory_2</span>
                                    </div>
                                    <p class="font-semibold text-md text-slate-700">No products need restocking.</p>
                                    <p class=" text-sm text-slate-500">All products currently have sufficient stock.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                        {{-- This is where the 'No results' row will be added by JavaScript --}}
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>


<div id="toast" class="fixed bottom-5 right-5 z-50 hidden bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2">
    <span class="material-symbols-rounded text-lg">check_circle</span>
    <span id="toastMessage">Success!</span>
</div>





<div id="summaryModal" class="fixed inset-0 hidden items-center justify-center z-50">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"></div>
    <div class="relative z-10 w-full max-w-2xl flex flex-col max-h-[80vh] bg-white rounded-2xl shadow-2xl">
        <div class="p-4 sm:p-6 overflow-y-auto custom-scrollbar flex-1 flex flex-col gap-4">
            <h3 class="text-md font-semibold text-green-600">Confirm Restock List</h3>
            <div class="overflow-y-auto max-h-64 border border-slate-200 rounded-xl custom-scrollbar">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 sticky top-0">
                        <tr>
                            <th class="p-3 text-left font-semibold">Product</th>
                            <th class="p-3 text-center font-semibold">Quantity</th>
                            <th class="p-3 text-right font-semibold">Cost</th>
                            <th class="p-3 text-right font-semibold">Subtotal</th>
                            <th class="p-3 text-center font-semibold w-20">Action</th>
                        </tr>
                    </thead>
                    <tbody id="summaryBody" class="divide-y divide-slate-100">
                    </tbody>
                </table>
            </div>
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 border border-green-200">
                <h4 class="font-medium text-sm text-slate-800 mb-3 flex items-center gap-2">
                    <span class="material-symbols-rounded text-green-600">add_circle</span>
                    Add custom restock
                </h4>
                <div class="flex flex-col sm:flex-row gap-3">
                    <select id="customProductSelect" class="flex-1 px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        <option value="">Select a product...</option>
                        @foreach($allProducts ?? [] as $inventoryProduct)
                        <option value="{{ $inventoryProduct->inven_code }}" data-name="{{ $inventoryProduct->name }}" data-cost="{{ $inventoryProduct->cost_price }}">
                            {{ $inventoryProduct->name }} (Stock: {{ $inventoryProduct->stock }})
                        </option>
                        @endforeach
                    </select>
                    <input type="number" id="customProductQty" placeholder="Qty" min="1" value="1" class="w-full sm:w-32 px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    <button type="button" onclick="addCustomProduct()" class="px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium flex items-center justify-center gap-1 whitespace-nowrap">
                        <span class="material-symbols-rounded text-lg">add</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="bg-slate-100 p-4 rounded-b-2xl flex justify-between items-center border-t border-slate-200">
            <div class="text-sm">
                <span class="font-medium text-slate-700">Total Products: </span>
                <span id="totalProducts" class="text-md font-bold text-gray-900">0</span>
                <span class="mx-2 text-slate-300">|</span>
                <span class="font-medium text-slate-700">Total Estimated Cost: </span>
                <span id="totalCost" class="text-md font-bold text-gray-900">₱0.00</span>
            </div>
            <div class="flex gap-3">
                <button onclick="closeSummaryModal()" class="px-6 py-2.5 bg-slate-200 rounded-lg text-slate-700 hover:bg-slate-300 font-medium transition">Cancel</button>
                <button id="confirmSubmit" class="px-6 py-2.5 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 font-medium shadow-lg transition">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const restockForm = document.getElementById('restockForm');
        const checkboxes = document.querySelectorAll('.productCheckbox');
        const summaryModal = document.getElementById('summaryModal');
        const summaryBody = document.getElementById('summaryBody');
        const confirmSubmit = document.getElementById('confirmSubmit');
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const clearFilters = document.getElementById('clearFilters');
        const resultsCount = document.getElementById('resultsCount');
        const productTableBody = document.getElementById('productTableBody');

        let customProducts = [];

        const formatCurrency = (value) => `₱${parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}`;

        function filterProducts() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedCategory = categoryFilter.value;
            const rows = document.querySelectorAll('.product-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const productName = row.dataset.productName.toLowerCase();
                const productCategory = row.dataset.categoryId;
                const matchesSearch = productName.includes(searchTerm);
                const matchesCategory = selectedCategory === '' || productCategory === selectedCategory;
                if (matchesSearch && matchesCategory) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // --- NEW: LOGIC FOR "NO RESULTS" MESSAGE ---
            let noResultsRow = document.getElementById('noResultsRow');
            if (visibleCount === 0 && rows.length > 0) {
                if (!noResultsRow) {
                    noResultsRow = document.createElement('tr');
                    noResultsRow.id = 'noResultsRow';
                    noResultsRow.innerHTML = `
                        <td colspan="6" class="text-center p-12">
                            <div class="flex flex-col items-center justify-center">
                                <span class="material-symbols-rounded text-slate-400 text-4xl mb-2">search_off</span>
                                <p class="font-semibold text-slate-700">No products found</p>
                                <p class="text-sm text-slate-500">Try adjusting your search or filters.</p>
                            </div>
                        </td>
                    `;
                    productTableBody.appendChild(noResultsRow);
                }
                noResultsRow.style.display = '';
            } else if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
            // --- END OF NEW LOGIC ---

            resultsCount.textContent = `Showing ${visibleCount} of ${rows.length} products`;
        }

        searchInput.addEventListener('input', filterProducts);
        categoryFilter.addEventListener('change', filterProducts);
        clearFilters.addEventListener('click', () => {
            searchInput.value = '';
            categoryFilter.value = '';
            filterProducts();
        });
        filterProducts();

        restockForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const selectedProducts = Array.from(checkboxes).filter(cb => cb.checked);
            if (selectedProducts.length === 0 && customProducts.length === 0) {
                alert('Please select at least one product before finalizing.');
                return;
            }
            renderSummary(selectedProducts);
            summaryModal.classList.remove('hidden');
            summaryModal.classList.add('flex');
        });

        function renderSummary(selectedProducts) {
            summaryBody.innerHTML = '';
            selectedProducts.forEach(cb => {
                const row = cb.closest('tr');
                const name = row.dataset.productName;
                const qty = row.dataset.quantity;
                const cost = row.dataset.cost;
                addSummaryRow(name, qty, cost, 'table', cb.value);
            });
            customProducts.forEach((product) => {
                addSummaryRow(product.name, product.quantity, product.cost, 'custom', product.code);
            });
            updateTotals();
        }

        function addSummaryRow(name, quantity, cost, type, id) {
            const row = document.createElement('tr');
            row.className = 'hover:bg-slate-50';
            row.dataset.type = type;
            row.dataset.id = id;
            row.dataset.cost = cost;
            const subtotal = quantity * cost;
            row.innerHTML = `
                <td class="p-3 text-left font-medium text-slate-700">${name}</td>
                <td class="p-3 text-center">
                    <input type="number" value="${quantity}" min="1" class="quantity-input w-20 px-2 py-1 border border-slate-300 rounded text-center" onchange="updateRow(this)">
                </td>
                <td class="p-3 text-right font-mono text-slate-500">${formatCurrency(cost)}</td>
                <td class="subtotal-cell p-3 text-right font-mono font-semibold text-slate-800">${formatCurrency(subtotal)}</td>
                <td class="p-3 text-center">
                    <button type="button" onclick="removeSummaryRow(this, '${type}', '${id}')" class="text-red-500 hover:text-red-700 transition">
                        <span class="material-symbols-rounded text-xl">delete</span>
                    </button>
                </td>
            `;
            summaryBody.appendChild(row);
        }

        window.updateRow = function(input) {
            const row = input.closest('tr');
            let quantity = parseInt(input.value);
            if (isNaN(quantity) || quantity < 1) {
                quantity = 1;
                input.value = 1;
            }
            const cost = parseFloat(row.dataset.cost);
            const subtotal = quantity * cost;
            row.querySelector('.subtotal-cell').textContent = formatCurrency(subtotal);
            updateTotals();
        };

        window.removeSummaryRow = function(button, type, id) {
            button.closest('tr').remove();
            if (type === 'table') {
                const checkbox = document.querySelector(`input[value="${id}"].productCheckbox`);
                if (checkbox) checkbox.checked = false;
            } else if (type === 'custom') {
                customProducts = customProducts.filter(p => p.code != id);
            }
            updateTotals();
        };

        window.addCustomProduct = function() {
            const selectElement = document.getElementById('customProductSelect');
            const qtyInput = document.getElementById('customProductQty');
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            if (!selectElement.value) return alert('Please select a product.');

            const productCode = selectElement.value;
            const productName = selectedOption.dataset.name;
            const productCost = selectedOption.dataset.cost;
            const quantity = parseInt(qtyInput.value) || 1;

            if (customProducts.find(p => p.code === productCode) || Array.from(checkboxes).some(cb => cb.checked && cb.value === productCode)) {
                return alert('This product is already in the list.');
            }

            customProducts.push({
                code: productCode,
                name: productName,
                quantity,
                cost: productCost
            });
            addSummaryRow(productName, quantity, productCost, 'custom', productCode);
            updateTotals();

            selectElement.selectedIndex = 0;
            qtyInput.value = '1';
        };

        function updateTotals() {
            const rows = summaryBody.querySelectorAll('tr');
            let totalCost = 0;
            rows.forEach(row => {
                const quantity = row.querySelector('.quantity-input').value;
                const cost = row.dataset.cost;
                totalCost += quantity * cost;
            });
            document.getElementById('totalProducts').textContent = rows.length;
            document.getElementById('totalCost').textContent = formatCurrency(totalCost);
        }

        window.closeSummaryModal = () => {
            summaryModal.classList.add('hidden');
            summaryModal.classList.remove('flex');
        };

        confirmSubmit.addEventListener('click', (e) => {
            e.preventDefault();

            summaryBody.querySelectorAll('tr').forEach(row => {
                const type = row.dataset.type;
                const id = row.dataset.id;
                const qty = row.querySelector('.quantity-input').value;

                if (type === 'table') {
                    document.querySelector(`input[name="quantities[${id}]"]`).value = qty;
                } else if (type === 'custom') {
                    if (!restockForm.querySelector(`input[name="custom_products[]"][value="${id}"]`)) {
                        const codeInput = document.createElement('input');
                        codeInput.type = 'hidden';
                        codeInput.name = `custom_products[]`;
                        codeInput.value = id;
                        const qtyInput = document.createElement('input');
                        qtyInput.type = 'hidden';
                        qtyInput.name = `custom_quantities[${id}]`;
                        qtyInput.value = qty;
                        restockForm.appendChild(codeInput);
                        restockForm.appendChild(qtyInput);
                    }
                }
            });

            // 👉 Submit the form to Laravel
            sessionStorage.setItem('showSuccess', '1');

            restockForm.submit();
        });



        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', e => {
                const visibleCheckboxes = Array.from(checkboxes).filter(cb => cb.closest('tr').style.display !== 'none');
                visibleCheckboxes.forEach(cb => cb.checked = e.target.checked);
            });
        }


    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (sessionStorage.getItem('showSuccess') === '1') {
            sessionStorage.removeItem('showSuccess'); // so it only triggers once
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            toastMessage.textContent = "Restock list successfully created!";
            toast.classList.remove('hidden');
            toast.classList.add('flex');
            setTimeout(() => {
                toast.classList.add('hidden');
                toast.classList.remove('flex');
            }, 4000);
        }
    });
</script>



@endsection