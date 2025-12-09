@extends('dashboards.staff.staff')

<head>
    <title>Inventory</title>
    <style>
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
            max-width: 400px;
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: pre-line;
        }

        .toast-success {
            background-color: #10b981;
            color: white;
        }

        .toast-error {
            background-color: #ef4444;
            color: white;
        }

        .toast-warning {
            background-color: #f59e0b;
            color: white;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    </style>
</head>
@section('content')


<!-- Inventory Table -->
<div class="px-4 space-y-4">
    @livewire('expiration-container')
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Product List</h2>

    <div class="flex justify-between items-center mt-4 mb-4">
        {{-- Left side: Filter + Search --}}
        <div class="flex gap-2 items-center">
            {{-- Filter Button --}}
            <div class="relative">
                <button
                    id="filterToggle"
                    type="button"
                    class="flex items-center border border-[#FF8A00] text-[#FF8A00] text-sm bg-transparent px-4 py-2 mb-4 rounded hover:bg-orange-50 transition">
                    <span class="material-symbols-outlined mr-2 text-[#FF8A00]">filter_alt</span> Category
                </button>

                <div id="categoryDropdown"
                    class="absolute z-10 bg-white border border-gray-300 mt-2 rounded shadow hidden min-w-max max-h-60 overflow-y-auto">
                    <button
                        onclick="filterByCategory('all')"
                        class="block w-full text-left px-4 py-2 hover:bg-gray-100 font-semibold">
                        All
                    </button>

                    @foreach ($categories as $category)
                    <button
                        onclick="filterByCategory('{{ $category->category_id }}')"
                        class="block w-full text-left px-4 py-2 hover:bg-gray-100">
                        {{ $category->category }}
                    </button>
                    @endforeach
                </div>

            </div>


            {{-- Search Bar --}}
            <form method="GET" action="{{ url('inventory-staff') }}" class="relative w-72">
                <input
                    type="text"
                    id="search"
                    name="search"
                    placeholder="Search by name or barcode"
                    value="{{ $search ?? '' }}"
                    autocomplete="off"
                    class="rounded px-4 py-2 w-full pr-10 shadow-lg focus:border-[#FF8A00] focus:shadow-lg border border-gray-50 placeholder:text-sm placeholder-gray-400">

                {{-- Carry status --}}
                <input type="hidden" name="status" value="{{ $status ?? 'active' }}">

                {{-- Suggestions Dropdown --}}
                <div
                    id="suggestions"
                    class="absolute z-10 w-full bg-white border border-gray-300 rounded shadow-md hidden max-h-60 overflow-y-auto mt-1"></div>
                {{-- Search Icon --}}
                <button
                    type="submit"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-[#FF8A00] hover:text-orange-600">
                    <span class="material-symbols-outlined text-[#FF8A00]">search</span>
                </button>
            </form>


            <!-- Status Toggle + Settings -->
            <div class="flex items-center gap-2">
                <form action="{{ route('inventory-staff') }}" method="GET" id="statusToggleForm" class="flex items-center gap-2">
                    <div class="relative flex bg-[#f09d39] rounded-full p-1 w-44">
                        <input type="hidden" name="status" id="statusInput" value="{{ $status ?? 'active' }}">

                        <!-- Selling (Active) -->
                        <button type="button"
                            id="activeBtn"
                            class="flex-1 text-center text-sm py-1 rounded-full transition-all duration-300
                                {{ ($status ?? 'active') === 'active' 
                                    ? 'bg-white text-[#f09d39] shadow' 
                                    : 'text-white' }}">
                            Selling
                        </button>

                        <!-- Archived -->
                        <button type="button"
                            id="archivedBtn"
                            class="flex-1 text-center text-sm py-1 rounded-full transition-all duration-300
                                {{ ($status ?? 'active') === 'archived' 
                                    ? 'bg-white text-[#f09d39] shadow' 
                                    : 'text-white' }}">
                            Archived
                        </button>
                    </div>
                </form>

                <!-- Settings Icon -->
                <a href="{{ route('inventory-staff-settings') }}"
                    class="flex items-center justify-center w-10 h-10 mb-4 rounded-full bg-white shadow-lg transition" title="Category and Unit Settings">
                    <span class="material-symbols-outlined text-[#f09d39]">category</span>
                </a>
            </div>
        </div>

        {{-- Right side: Action Buttons --}}
        <div class="flex items-center gap-3 mb-4">
            <div x-data="{ showPopup: false }" class="flex items-center gap-3 relative">
                <!-- Limit Reached Popup (shared by both buttons) -->
                <div x-show="showPopup"
                    x-transition
                    class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 
                            bg-yellow-100 border border-yellow-400 text-yellow-800 
                            text-sm rounded-lg px-3 py-2 shadow-lg z-50 whitespace-nowrap"
                    style="display: none;">
                    {{ $productLimitReached }}
                </div>

                <!-- Primary Action: Scan Button -->
                <button x-on:click="
                            @if($limitReached)
                                showPopup = true;
                                setTimeout(() => showPopup = false, 3500);
                            @endif
                        "
                    id="quickScanBtn" {{ $expired ? 'disabled' : '' }}
                    class="flex items-center gap-1.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg
                            {{ $expired ? 'opacity-50 cursor-not-allowed' : '' }}"
                    title="Quick Scan Product">
                    <span class="material-symbols-outlined text-lg">barcode_scanner</span>
                    <span class="font-medium text-sm">Scan</span>
                </button>
                
                <!-- Divider -->
                <div class="h-8 w-px bg-gray-300"></div>
                
                <!-- Add Product Button -->
                <button x-on:click="
                        @if($limitReached)
                            showPopup = true;
                            setTimeout(() => showPopup = false, 3500);
                        @else
                            $dispatch('open-add-product-modal');
                        @endif
                    "
                    id="addProductBtn" {{ $expired ? 'disabled' : '' }}
                    class="flex items-center gap-1.5 bg-green-500 text-white border-2 border-green-500 px-4 py-2 
                        rounded-lg hover:bg-green-600 transition-all duration-200 transform hover:scale-105
                        {{ $expired ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <span class="material-symbols-outlined text-lg">add_circle</span>
                    <span class="font-medium text-sm">Add Product</span>
                </button>
            </div>


            <!-- Add Stock -->
            <button id="addStockBtn" {{ $expired ? 'disabled' : '' }}
                class="flex items-center gap-1.5 bg-yellow-500 text-white border-2 border-yellow-500 px-4 py-2 rounded-lg hover:bg-yellow-600 transition-all duration-200 transform hover:scale-105
                        {{ $expired ? 'opacity-50 cursor-not-allowed' : '' }}"
                title="Add Stock by Category">
                <span class="material-symbols-outlined text-lg">inventory_2</span>
                <span class="font-medium text-sm">Add Stock</span>
            </button>

            <!-- Damage -->
            @livewire('record-damage')
        </div>
    </div>





            
<div class="overflow-x-auto bg-white rounded-lg shadow">
    <!-- Header with Tip and Filter -->
    <div class="px-4 py-2 bg-gray-100 flex justify-between items-center">
        <p class="text-xs text-gray-400 flex items-center gap-2">
            <span class="material-symbols-outlined text-xs">info</span>
            <span class="text-xs"><strong>Tip:</strong> Click on any product row to quickly restock that item</span>
        </p>
        
        <div class="flex items-center gap-2">
            <label for="stockFilter" class="text-xs text-gray-600 font-medium">Filter:</label>
            <select id="stockFilter" 
                    class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100"
                    onchange="filterProductTable()">
                <option value="all">All Products</option>
                <option value="out-of-stock">Out of Stock</option>
                <option value="low-stock">Low Stock</option>
            </select>
        </div>
    </div>

    <table class="min-w-full table-fixed border-4 border-gray-100">
        <colgroup>
            <col style="width: 100px;"> <!-- Image -->
            <col style="width: 130px;"> <!-- Barcode -->
            <col style="width: auto;"> <!-- Name (flexible) -->
            <col style="width: 120px;"> <!-- Cost Price -->
            <col style="width: 120px;"> <!-- Selling Price -->
            <col style="width: 80px;"> <!-- Unit -->
            <col style="width: 150px;"> <!-- Current Stock -->
            <col style="width: 140px;"> <!-- Actions -->
        </colgroup>
        <thead class="bg-gray-100 text-gray-700 text-sm">
            <tr>
                <th class="px-4 py-3">Image</th>
                <th class="px-4 py-3">Barcode</th>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Cost Price</th>
                <th class="px-4 py-3">Selling Price</th>
                <th class="px-4 py-3">Unit</th>
                <th class="px-4 py-3">Current Stock</th>
                <th class="px-4 py-3 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-sm text-gray-800">
            @forelse ($products as $product)
                @php
                    $rowClass = '';
                    $stockStatus = 'healthy';
                    
                    if ($product->current_stock <= 0) {
                        $rowClass = 'bg-red-50 hover:bg-red-100 border-l-4 border-red-400';
                        $stockStatus = 'out-of-stock';
                    } elseif ($product->current_stock <= $product->stock_limit) {
                        $rowClass = 'bg-yellow-50 hover:bg-yellow-100 border-l-4 border-yellow-400';
                        $stockStatus = 'low-stock';
                    } else {
                        $rowClass = 'hover:bg-gray-100';
                    }
                @endphp
                
                <tr class="cursor-pointer transition-colors duration-150 product-row {{ $rowClass }}" 
                    data-stock-status="{{ $stockStatus }}"
                    onclick="openQuickRestockForProduct(
                        '{{ $product->prod_code }}', 
                        '{{ addslashes($product->name) }}', 
                        '{{ $product->category_id }}', 
                        '{{ $product->current_stock }}',
                        event
                    )">
                    <!-- Product Image -->
                    <td class="px-4 py-2 border text-center">
                        @if($product->prod_image)
                            <img src="{{ Str::startsWith($product->prod_image, 'assets/') ? asset($product->prod_image) : asset('storage/' . $product->prod_image) }}"
                                alt="Product Image"
                                class="h-16 w-16 object-cover rounded mx-auto">
                        @else
                            <img src="{{ asset('assets/no-product.png') }}"
                                alt="Image Not Found"
                                class="h-16 w-16 object-cover rounded mx-auto">
                        @endif
                    </td>

                    <!-- Barcode -->
                    <td class="px-4 py-2 border text-center">
                        <span class="block truncate" title="{{ $product->barcode }}">
                            {{ $product->barcode }}
                        </span>
                    </td>

                    <!-- Name -->
                    <td class="px-4 py-2 border">
                        <span class="block truncate max-w-full" title="{{ $product->name }}">
                            {{ Str::limit($product->name, 45, '...') }}
                        </span>
                    </td>

                    <!-- Cost Price -->
                    <td class="px-4 py-2 border text-right">
                        ₱{{ number_format($product->cost_price, 2) }}
                    </td>

                    <!-- Selling Price -->
                    <td class="px-4 py-2 border text-right">
                        ₱{{ number_format($product->selling_price, 2) }}
                    </td>

                    <!-- Unit -->
                    <td class="px-4 py-2 border text-center">{{ $product->unit ?? '—' }}</td>

                    <!-- Remaining Stock -->
                    <td class="px-4 py-2 border text-center">
                        @php
                            $count = $product->current_stock;
                            $label = $count == 1 ? 'stock left' : 'stocks left';
                        @endphp
                        
                        <div class="flex items-center justify-center gap-2">
                            @if($count <= 0)
                                <span class="inline-flex items-center gap-1 px-2 py-2 text-xs font-semibold bg-red-100 text-red-700 rounded-full cursor-help">
                                    Out of stock
                                </span>
                            @elseif($count <= $product->stock_limit)
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full animate-pulse cursor-help" 
                                    title="LOW STOCK WARNING! Current: {{ $count }} | Minimum required: {{ $product->stock_limit }}">
                                    <span class="material-symbols-outlined text-sm">warning</span>
                                    {{ $count }} {{ $label }}
                                </span>
                            @else
                                <span class="font-semibold text-gray-800 cursor-help" 
                                    title="Stock level healthy. Current: {{ $count }} | Minimum: {{ $product->stock_limit }}">
                                    {{ $count }}
                                </span>
                            @endif
                        </div>
                    </td>

                    <!-- Actions -->
                    <td class="px-4 py-2 border text-center" onclick="event.stopPropagation()">
                        <div class="flex items-center justify-center gap-2">
                            <!-- Info -->
                            <a href="{{ route('inventory-staff-product-info', $product->prod_code) }}"
                                class="text-blue-500 hover:text-blue-700">
                                <span class="material-symbols-outlined">info</span>
                            </a>

                            <!-- Edit -->
                            @php
                                $editDisabled = $expired ? 'cursor-not-allowed' : '';
                            @endphp
                            <a href="{{ $expired ? '' : route('inventory-staff-edit', $product->prod_code) }}"
                                onclick="{{ $expired ? 'event.preventDefault();' : '' }}"
                                title="Edit" class="text-green-500 hover:text-green-700 {{ $editDisabled }}">
                                <span class="material-symbols-outlined">edit</span>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="no-products-row">
                    <td colspan="8" class="text-center py-4 text-gray-500">
                        No products available.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>




    <!-- Add Product Modal -->
    @if(!$limitReached)
    <div id="addProductModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg p-8 w-[90%] max-w-md min-h-[550px] shadow-lg relative">
            <!-- Close Button -->
            <button id="closeAddProductModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">
                <span class="material-symbols-outlined text-gray-500">close</span>
            </button>

            <h2 class="text-xl font-semibold mb-6 text-center text-[#B50612]">Choose Action</h2>

            <div class="grid grid-cols-1 gap-4">
                <!-- Scan Barcode Option -->
                <div onclick="openScanModal()" class="cursor-pointer border border-gray-300 rounded-lg p-4 text-center hover:shadow-lg hover:shadow-red-200 hover:border-red-200 transition-all duration-200 transform hover:scale-105">
                    <img src="{{ asset('assets/scan-barcode.png') }}" alt="Scan Barcode" class="mx-auto h-16 mb-2">
                    <p class="font-medium">Scan Barcode</p>
                </div>

                <!-- Type Barcode Option -->
                <div onclick="openTypeModal()" class="cursor-pointer border border-gray-300 rounded-lg p-4 text-center hover:shadow-lg hover:shadow-red-200 hover:border-red-200 transition-all duration-200 transform hover:scale-105">
                    <img src="{{ asset('assets/type-barcode.png') }}" alt="Type Barcode" class="mx-auto h-16 mb-2">
                    <p class="font-medium">Type Barcode</p>
                </div>

                <!-- Generate Barcode Option -->
                <div onclick="openGenerateModal()" class="cursor-pointer border border-gray-300 rounded-lg p-4 text-center hover:shadow-lg hover:shadow-red-200 hover:border-red-200 transition-all duration-200 transform hover:scale-105">
                    <img src="{{ asset('assets/generate-barcode.png') }}" alt="Generate Barcode" class="mx-auto h-16 mb-2">
                    <p class="font-medium">Generate Barcode</p>
                </div>
            </div>
        </div>
    </div>
    @endif



    <!-- Choose Category Modal for Restock -->
    <div id="chooseCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 w-[90%] max-w-4xl shadow-xl relative">
            <!-- Close Button -->
            <button id="closeChooseCategoryModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition">
                <span class="material-symbols-outlined">close</span>
            </button>

            <!-- Header -->
            <h2 class="text-xl font-bold text-center text-[#B50612] mb-6">Choose Category</h2>

            <!-- Categories Grid -->
            <div class="grid grid-cols-4 gap-6 max-h-[420px] overflow-y-auto pr-2">
                <!-- New Category -->
                <div onclick="openAddCategoryModal()"
                    class="cursor-pointer rounded-xl p-6 flex flex-col justify-center items-center bg-orange-50 border-2 border-orange-400 hover:shadow-md hover:-translate-y-1 transition-transform duration-200 h-36">
                    <span class="material-symbols-outlined text-4xl text-orange-400 mb-2">add_circle</span>
                    <p class="font-semibold text-gray-700">New Category</p>
                </div>

                <!-- Category Items -->
                @foreach($categories as $category)
                <div onclick="onCategorySelected('{{ $category->category_id }}', '{{ e($category->category) }}')"
                    class="cursor-pointer rounded-xl p-6 flex flex-col justify-center items-center bg-gray-50 border-2 border-gray-200 hover:border-[#B50612] hover:bg-[#FFF7F7] hover:shadow-md hover:-translate-y-1 transition-transform duration-200 h-36">
                    <p class="font-semibold text-gray-700 text-center">{{ $category->category }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 w-[90%] max-w-md shadow-xl relative">
            <!-- Close Button -->
            <button id="closeAddCategoryModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition">
                <span class="material-symbols-outlined">close</span>
            </button>

            <!-- Header -->
            <h2 class="text-xl font-bold text-center text-[#B50612] mb-6">Add New Category</h2>

            <!-- Form -->
            <form id="addCategoryForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="newCategoryName" class="block text-sm font-medium text-gray-700 mb-1">
                        Category Name
                    </label>
                    <input type="text" id="newCategoryName" name="category" required placeholder="Enter category name"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring- focus:ring-[#B50612] focus:border-[#B50612] placeholder-gray-400 text-sm">
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" id="cancelAddCategory"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="submit" id="saveCategoryBtn"
                        class="px-4 py-2 bg-[#B50612] text-white font-medium rounded-lg hover:bg-[#9E0410] transition">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>



    <!-- Choose Products Modal for Restock -->
    <div id="chooseProductsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-[95%] max-w-4xl shadow-xl relative">
            <button id="closeChooseProductsModal" class="absolute top-4 right-4 text-gray-500">
                <span class="material-symbols-outlined">close</span>
            </button>

            <!-- Title -->
            <h2 class="text-xl font-semibold text-center text-[#B50612] mb-1">Choose Products to Restock</h2>
            <!-- Category Label -->
            <p id="selectedCategoryLabel" class="text-center text-sm text-gray-500 mb-4"></p>

            <div class="mb-4 flex justify-between items-center">
                <div class="text-sm text-gray-700" id="chooseProductsInfo"></div>
                <div class="flex gap-2">
                    <button onclick="selectAllProducts()" class="px-4 py-1.5 text-sm font-medium rounded-md bg-yellow-500 text-white hover:bg-yellow-600 transition-colors duration-200">Select All</button>
                    <button onclick="deselectAllProducts()" class="px-4 py-1.5 text-sm font-medium rounded-md bg-gray-200 text-gray-800 hover:bg-gray-300 transition-colors duration-200">Clear</button>
                </div>
            </div>


            <div class="max-h-[360px] overflow-y-auto border rounded p-2">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="p-2 text-left">Select</th>
                            <th class="p-2 text-left">Product</th>
                            <th class="p-2 text-center">Current Stock</th>
                        </tr>
                    </thead>
                    <tbody id="categoryProductsList"></tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-end">
                <button id="proceedToRestockBtn" onclick="proceedToRestock()" class="bg-[#B50612] text-white px-4 py-2 rounded transition-all duration-200 transform hover:scale-105">
                    Next
                </button>
            </div>
        </div>
    </div>


    <!-- Restock Details Modal -->
    <div id="restockDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl w-[95%] max-w-5xl shadow-xl relative flex flex-col max-h-[90vh]">
            <!-- Header -->
            <div class="bg-gradient-to-r from-[#B50612] to-[#8B0410] px-6 py-4 rounded-t-2xl flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-white text-2xl">inventory</span>
                    </div>
                    <h2 class="text-white text-xl font-bold">Restock Details</h2>
                </div>
                <button id="closeRestockDetailsModal" class="text-white hover:bg-white/20 p-2 rounded-lg transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto px-6 py-6">
                <form id="bulkRestockForm" method="POST">
                    @csrf
                    <input type="hidden" name="category_id" id="restockCategoryId">

                    <!-- Products Table -->
                    <div class="mb-6">
                        <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#B50612]">inventory_2</span>
                            Stock Information
                        </h3>
                        <div class="border rounded-lg overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-100 sticky top-0">
                                    <tr>
                                        <th class="p-3 text-left">Product</th>
                                        <th class="p-3 text-center">Current Stock</th>
                                        <th class="p-3 text-center">Add Qty</th>
                                        <th class="p-3 text-center">Expiration Date</th>
                                        <th class="p-3 text-center">
                                            <span class="font-semibold">Batch No.</span>
                                            <span class="font-normal text-gray-500 text-xs">(auto-filled)</span>
                                        </th>
                                        <th class="p-3 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="restockRowsContainer"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pricing & Tax Section (Only shows when single product) -->
                    <div id="pricingSection" class="hidden mb-6">
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-5 border border-green-200">
                            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-green-600 text-lg">payments</span>
                                Update Pricing & Tax (Optional)
                            </h3>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <!-- Left Column: Current Prices Display -->
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <h4 class="text-xs font-semibold text-gray-700 mb-3">Current Prices</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Cost Price:</span>
                                            <span id="currentCostDisplay" class="font-semibold">₱0.00</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Selling Price:</span>
                                            <span id="currentSellingDisplay" class="font-semibold">₱0.00</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">VAT Category:</span>
                                            <span id="currentVatDisplay" class="font-semibold">-</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column: New Price Inputs -->
                                <div class="space-y-4">
                                    <!-- Bulk Purchase Calculator -->
                                    <div>
                                        <button type="button" onclick="toggleRestockBulkCalculator()" 
                                            class="w-full flex items-center justify-between p-2.5 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition text-left">
                                            <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-blue-600 text-sm">calculate</span>
                                                Bulk Purchase Calculator
                                                <span class="text-gray-500 font-normal">(Optional)</span>
                                            </span>
                                            <span class="material-symbols-outlined text-blue-600 text-lg" id="restockBulkToggleIcon">expand_more</span>
                                        </button>
                                        
                                        <div id="restockBulkCalculatorSection" class="hidden mt-2 p-3 bg-white rounded-lg border border-blue-200 space-y-2">
                                            <p class="text-xs text-gray-600">💡 Buy in bulk (pack/box) but sell in smaller units</p>
                                            
                                            <div class="grid grid-cols-3 gap-2">
                                                <div>
                                                    <label class="block text-xs text-gray-600 mb-1">Quantity</label>
                                                    <input type="number" id="restockBulkQuantity" min="1" placeholder="Enter quantity" 
                                                        class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                                                </div>
                                                <div class="col-span-2">
                                                    <label class="block text-xs text-gray-600 mb-1">Per</label>
                                                    <select id="restockBulkUnit" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                                                        <option value="">Select...</option>
                                                        <option value="pack">Pack</option>
                                                        <option value="box">Box</option>
                                                        <option value="dozen">Dozen (12 pcs)</option>
                                                        <option value="bundle">Bundle</option>
                                                        <option value="case">Case</option>
                                                        <option value="kg">Kilogram (kg)</option>
                                                        <option value="sack">Sack</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Bulk Cost Price</label>
                                                <input type="number" step="0.01" min="0" id="restockBulkCostPrice" placeholder="₱0.00"
                                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                                            </div>
                                            
                                            <div class="pt-2 border-t border-gray-200">
                                                <div class="flex justify-between items-center text-xs mb-2">
                                                    <span class="font-semibold text-gray-700">Cost per unit:</span>
                                                    <span id="restockCalculatedUnitCost" class="font-bold text-green-600">₱0.00</span>
                                                </div>
                                                <button type="button" onclick="applyRestockBulkCost()" 
                                                    class="w-full px-3 py-1.5 bg-blue-500 text-white text-xs font-medium rounded hover:bg-blue-600 transition">
                                                    Apply to Cost Price
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cost Price & Tax Category -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 mt-2">
                                                New Cost Price
                                            </label>
                                            <input type="number" step="0.01" min="0" name="new_cost_price" id="newCostPrice" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 text-sm">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 flex items-center gap-1">
                                                Tax Category
                                                <button type="button" onclick="toggleRestockVatInfo()" class="text-blue-500 hover:text-blue-700">
                                                    <span class="material-symbols-outlined text-sm">info</span>
                                                </button>
                                            </label>
                                            <select id="newVatCategory" name="new_vat_category"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 text-sm">
                                                <option value="vat_exempt">VAT-Exempt (0%)</option>
                                                <option value="vat_inclusive">VAT-Inclusive (12%)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- VAT Info Panel -->
                                    <div id="restockVatInfoPanel" class="hidden p-2.5 bg-blue-50 border border-blue-200 rounded-lg text-xs">
                                        <p class="font-semibold text-blue-900 mb-1.5">📋 Tax Guidelines:</p>
                                        <ul class="space-y-1 text-blue-800 text-xs">
                                            <li><strong>0%:</strong> Raw vegetables, fruits, meat, fish, eggs, rice</li>
                                            <li><strong>12%:</strong> Processed foods, beverages, snacks, household items</li>
                                        </ul>
                                    </div>

                                    <!-- Markup -->
                                    <label class="block text-xs font-semibold text-gray-700 text-center">
                                                Markup
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <select id="newMarkupType" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600">
                                            <option value="percentage">Percentage %</option>
                                            <option value="fixed">Fixed ₱</option>
                                        </select>
                                        <input type="number" id="newMarkupValue" placeholder="Markup Value" min="0" step="0.01"
                                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600">
                                    </div>

                                    <!-- New Selling Price -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">New Selling Price per Unit</label>
                                        <input type="number" step="0.01" name="new_selling_price" id="newSellingPrice" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-sm font-semibold mb-2" readonly>
                                        
                                        <!-- Compact Tax Breakdown -->
                                        <div id="newTaxBreakdown" class="p-2.5 bg-white rounded-lg border border-gray-200">
                                            <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Cost:</span>
                                                    <span id="newCostDisplay" class="font-medium">₱0.00</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span id="newMarkupLabel" class="text-blue-600">Markup:</span>
                                                    <span id="newMarkupAmount" class="font-medium text-blue-600">₱0.00</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Base:</span>
                                                    <span id="newBasePrice" class="font-medium">₱0.00</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span id="newTaxLabel" class="text-green-700">VAT (12%):</span>
                                                    <span id="newTaxAmount" class="font-medium text-green-700">₱0.00</span>
                                                </div>
                                            </div>
                                            <div class="pt-1.5 mt-1.5 border-t border-gray-300 flex justify-between font-semibold text-gray-900">
                                                <span>Total Price:</span>
                                                <span id="newTotalPrice">₱0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden field to store product code for pricing update -->
                    <input type="hidden" name="pricing_prod_code" id="pricingProdCode">
                </form>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl">
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeRestockDetails()"
                        class="px-6 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="submit" form="bulkRestockForm"
                        class="px-6 py-2.5 bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold rounded-lg hover:from-green-600 hover:to-green-700 transition shadow-lg hover:shadow-xl flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        Save Restock
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Type Barcode Modal -->
    <div id="typeBarcodeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg w-[50%] min-h-[550px] shadow-lg relative flex flex-col items-center">

            <!-- Red Top Bar -->
            <div class="bg-[#B50612] w-full h-16 flex items-center justify-between px-6 rounded-t-lg">
                <h2 class="text-white text-lg font-medium">Enter Product Barcode</h2>
                <button onclick="closeTypeModal()" class="text-white hover:text-gray-200">
                    <span class="material-symbols-outlined text-white">close</span>
                </button>
            </div>

            <!-- Modal Content Center -->
            <div class="flex-1 w-full flex flex-col items-center justify-center px-6 py-8 mb-16">
                <div class="mb-4">
                    <img src="{{ asset('assets/type-barcode.png') }}" alt="Type Barcode" class="h-32 mx-auto">
                </div>

                <form id="barcodeForm" class="w-2/4 space-y-4">
                    <input
                        type="text"
                        name="barcode"
                        id="barcodeInput"
                        placeholder="Enter barcode here"
                        class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-red-600 mb-4 placeholder:text-sm"
                        inputmode="text"
                        title="Only letters and numbers are allowed"
                        maxlength="15"
                        pattern="[A-Za-z0-9]+"
                        required>

                    <button type="submit" class="w-2/6 bg-black mx-auto block text-white py-3 rounded-3xl hover:bg-gray-800 transition-all duration-200 transform hover:scale-105">
                        Submit
                    </button>
                </form>
            </div>
        </div>
    </div>


    <!-- Scan Barcode Modal -->
    @if(!$limitReached)
    <div id="scanBarcodeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg w-[50%] min-h-[550px] shadow-lg relative flex flex-col items-center">

        <!-- Red Top Bar -->
        <div class="bg-[#B50612] w-full h-16 flex items-center justify-between px-6 rounded-t-lg">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-white text-2xl">barcode_scanner</span>
                <h2 class="text-white text-lg font-medium">Scan Product Barcode</h2>
            </div>
            <button onclick="closeScanModal()" class="text-white hover:text-gray-200">
                <span class="material-symbols-outlined text-white">close</span>
            </button>
        </div>

        <!-- Modal Content Center -->
        <div class="flex-1 w-full flex flex-col items-center justify-center px-6 py-8 mb-16">
            <div class="mb-4">
                <img src="{{ asset('assets/scan-barcode.png') }}" alt="Scan Barcode" class="h-32 mx-auto">
            </div>
            <p class="text-black mb-6 text-center text-sm">Scan barcode to register new product or restock existing item</p>
            <input
                type="text"
                id="scannedBarcodeInput"
                placeholder="Waiting for barcode..."
                class="w-2/4 px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-red-600 mb-4 placeholder:text-sm text-center"
                autofocus>

                <button type="button"
                    onclick="processScannedBarcode()"
                    class="w-2/6 bg-black mx-auto block text-white py-3 rounded-3xl hover:bg-gray-800 transition-all duration-200 transform hover:scale-105">
                    Submit
                </button>
            </div>
        </div>
    </div>
    @endif


    <!-- Choose Category Modal for Barcode Generation -->
    <div id="chooseCategoryBarcodeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-2xl p-8 w-[90%] max-w-4xl shadow-xl relative">
            <!-- Close Button -->
            <button onclick="closeChooseCategoryBarcodeModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition">
                <span class="material-symbols-outlined">close</span>
            </button>

            <!-- Header -->
            <h2 class="text-xl font-bold text-center text-[#B50612] mb-6">Choose Product Category</h2>

            <!-- Categories Grid -->
            <div class="grid grid-cols-4 gap-6 max-h-[420px] overflow-y-auto pr-2">
                <!-- New Category -->
                <div onclick="selectCategoryForBarcode('new', 'New Category')"
                    class="cursor-pointer rounded-xl p-6 flex flex-col justify-center items-center bg-orange-50 border-2 border-orange-400 hover:shadow-md hover:-translate-y-1 transition-transform duration-200 h-36">
                    <span class="material-symbols-outlined text-4xl text-orange-400 mb-2">add_circle</span>
                    <p class="font-semibold text-gray-700">New Category</p>
                </div>

                <!-- Category Items -->
                @foreach($categories as $category)
                <div onclick="selectCategoryForBarcode('{{ $category->category_id }}', '{{ e($category->category) }}')"
                    class="cursor-pointer rounded-xl p-6 flex flex-col justify-center items-center bg-gray-50 border-2 border-gray-200 hover:border-[#B50612] hover:bg-[#FFF7F7] hover:shadow-md hover:-translate-y-1 transition-transform duration-200 h-36">
                    <p class="font-semibold text-gray-700 text-center">{{ $category->category }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>


    <!-- Custom Add Category Modal for Barcode Generation-->
    <div id="customCategoryBarcodeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 w-[90%] max-w-md shadow-xl relative">
            <!-- Close Button -->
            <button onclick="closeCustomCategoryBarcodeModalCompletely()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition">
                <span class="material-symbols-outlined">close</span>
            </button>

            <!-- Header -->
            <h2 class="text-xl font-bold text-center text-[#B50612] mb-6">Add New Category</h2>

            <!-- Form -->
            <form id="customCategoryBarcodeForm" onsubmit="event.preventDefault(); confirmCustomCategory();">
                <div class="mb-4">
                    <label for="newCategoryNameBarcode" class="block text-sm font-medium text-gray-700 mb-1">
                        Category Name
                    </label>
                    <input type="text" id="newCategoryNameBarcode" name="category" required placeholder="Enter category name"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-[#B50612] focus:border-[#B50612] placeholder-gray-400 text-sm"
                        maxlength="50">
                    <!-- Error message will be inserted here dynamically -->
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeCustomCategoryBarcodeModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="submit" id="saveCategoryBarcodeBtn"
                        class="px-4 py-2 bg-[#B50612] text-white font-medium rounded-lg hover:bg-[#9E0410] transition">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Generate Barcode Modal -->
    <div id="generateBarcodeModal" class="fixed inset-0 bg-black bg-opacity-60 hidden flex justify-center items-center z-50 p-4">
        <div class="bg-white rounded-xl w-full max-w-md shadow-2xl relative flex flex-col items-center overflow-hidden">

            <!-- Header -->
            <div class="w-full bg-[#B50612] py-4 px-5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-white/20 rounded flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-sm">qr_code_2</span>
                    </div>
                    <h2 class="text-white text-lg font-bold">Generated Barcode</h2>
                </div>
                <button onclick="closeGenerateBarcodeModal()" class="text-white hover:bg-white/20 p-1 rounded transition-all duration-200">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="w-full flex-1 flex flex-col items-center px-5 py-6 space-y-5">

                <!-- Category Badge -->
                <div class="text-center">
                    <div class="inline-flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-full border border-gray-200">
                        <span class="material-symbols-outlined text-gray-500 text-xs">category</span>
                        <span class="text-xs text-gray-600">Category:</span>
                        <span id="selectedCategoryDisplay" class="font-semibold text-[#B50612] text-xs"></span>
                    </div>
                </div>

                <!-- Barcode Card -->
                <div class="w-full bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                    <div class="text-center space-y-4">

                        <!-- Barcode Display -->
                        <div class="bg-white p-3 rounded border border-gray-200">
                            <svg id="generatedBarcode" class="mx-auto w-full max-w-xs"></svg>
                        </div>

                        <!-- Barcode Number -->
                        <div class="bg-gray-50 rounded p-3 border border-gray-200">
                            <p class="text-xs text-gray-500 mb-1 font-medium">BARCODE NUMBER</p>
                            <input type="text" id="generatedBarcodeInput" readonly
                                class="w-full bg-transparent border-none text-center font-mono text-base font-bold text-gray-800 tracking-widest outline-none" />
                        </div>

                        <!-- Quick Actions -->
                        <div class="flex justify-center gap-4 pt-2">
                            <button id="generateNewBarcodeBtn"
                                class="flex items-center gap-1 px-3 py-1.5 text-xs text-gray-600 hover:text-[#B50612] transition-colors duration-200 border border-gray-300 rounded-lg hover:border-[#B50612]">
                                <span class="material-symbols-outlined text-xs">refresh</span>
                                New Code
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="w-full space-y-3">
                    <div class="grid grid-cols-2 gap-2">
                        <button id="goBackBtn"
                            class="bg-gray-500 text-white py-2.5 px-3 rounded-lg hover:bg-gray-600 transition-all duration-200 flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">arrow_back</span>
                            <span class="font-medium text-sm">Back</span>
                        </button>

                        <button id="useBarcodeBtn"
                            class="bg-[#B50612] text-white py-2.5 px-3 rounded-lg hover:bg-red-700 transition-all duration-200 flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            <span class="font-medium text-sm">Use Barcode</span>
                        </button>
                    </div>
                </div>

                <!-- Footer Note -->
                <div class="text-center pt-2">
                    <p class="text-xs text-gray-400">
                        Unique barcode for your product
                    </p>
                </div>
            </div>
        </div>
    </div>



    <!-- Modal for barcode not exists -->
    <div id="barcodeNotFoundModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg w-[50%] min-h-[550px] shadow-lg relative flex flex-col items-center">

            <!-- Red Top Bar -->
            <div class="bg-[#B50612] w-full h-16 flex items-center justify-between px-6 rounded-t-lg">
                <h2 class="text-white text-lg font-medium">Barcode Not Found</h2>
                <button onclick="closeAllModals()" class="text-white hover:text-gray-200">
                    <span class="material-symbols-outlined text-white">close</span>
                </button>
            </div>

            <!-- Modal Content Center -->
            <div class="flex-1 w-full flex flex-col items-center justify-center px-6 py-8 mb-16">
                <div class="mb-10">
                    <img src="{{ asset('assets/warning-icon.png') }}" alt="Warning" class="h-20 mx-auto">
                </div>

                <p class="font-medium text-base text-black text-center mb-8">
                    Product barcode <span class="font-bold text-red-500">does not exists</span> in your inventory
                </p>

                <div class="flex justify-center gap-6">
                    <button
                        onclick="closeAllModals()"
                        class="w-32 bg-gray-300 text-gray-800 text-sm py-3 rounded-3xl hover:bg-gray-400 transition-all duration-200 transform hover:scale-105">
                        Exit
                    </button>
                    <button
                        onclick="openRegisterModal()"
                        class="w-32 bg-green-500 text-white text-sm py-3 rounded-3xl hover:bg-green-600 transition-all duration-200 transform hover:scale-105">
                        Register
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Barcode Already Exists Modal -->
    <div id="barcodeAlreadyExistsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg w-[50%] min-h-[550px] shadow-lg relative flex flex-col items-center">

            <!-- Red Top Bar -->
            <div class="bg-[#B50612] w-full h-16 flex items-center justify-between px-6 rounded-t-lg">
                <h2 class="text-white text-lg font-medium">Barcode Already Exists</h2>
                <button onclick="goToInventory()" class="text-white hover:text-gray-200">
                    <span class="material-symbols-outlined text-white">close</span>
                </button>
            </div>

            <!-- Modal Content Center -->
            <div class="flex-1 w-full flex flex-col items-center justify-center px-6 py-8 mb-16">
                <div class="mb-10">
                    <img src="{{ asset('assets/warning-icon.png') }}" alt="Warning" class="h-20 mx-auto">
                </div>

                <p class="font-medium text-base text-black text-center mb-8">
                    Product barcode <span class="font-bold text-red-500">already exists</span> in your inventory
                </p>

            <div class="flex justify-center gap-6">
                <button
                    onclick="closeBarcodeExistsModal()"
                    class="w-32 bg-gray-300 text-gray-800 text-sm py-3 rounded-3xl hover:bg-gray-400 transition-all duration-200 transform hover:scale-105">
                    New Barcode
                </button>
                <button
                    onclick="openRestockFromBarcodeModal()"
                    class="w-32 bg-[#B50612] text-white text-sm py-3 rounded-3xl hover:bg-red-700 transition-all duration-200 transform hover:scale-105">
                    Add Stock
                </button>
            </div>
        </div>
        
        <!-- Hidden fields to store product data -->
        <input type="hidden" id="existingProductCode" value="">
        <input type="hidden" id="existingProductName" value="">
        <input type="hidden" id="existingCategoryId" value="">
        <input type="hidden" id="existingCurrentStock" value="">
    </div>
</div>

<!-- Register New Product Modal with Integrated Restock -->
<div id="registerProductModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50 p-4">

    <div class="bg-white rounded-xl w-full max-w-6xl max-h-[90vh] shadow-2xl relative flex flex-col">

        <!-- Header -->
        <div class="bg-gradient-to-r from-[#B50612] to-[#8B0410] px-6 py-4 rounded-t-xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 p-2 rounded-lg">
                    <span class="material-symbols-outlined text-white text-2xl">add_shopping_cart</span>
                </div>
                <h2 class="text-white text-xl font-bold">Register New Product</h2>
            </div>
            <button onclick="closeRegisterModal()" class="text-white hover:bg-white/20 p-2 rounded-lg transition">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto px-6 py-6">
            <form id="registerProductForm" class="space-y-5">

                <!-- Main Grid Layout - 2 Columns -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- LEFT COLUMN -->
                    <div class="space-y-5">
                        
                        <!-- Product Image & Barcode Row -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Product Image Upload -->
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 flex flex-col">
                                <h3 class="text-xs font-semibold text-gray-700 mb-3 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[#B50612] text-base">image</span>
                                    Product Image
                                </h3>
                                <div class="flex-1 flex items-center justify-center">
                                    <label for="productPhoto"
                                        class="w-full max-w-[180px] aspect-square flex items-center justify-center border-2 border-dashed border-[#B50612] rounded-lg cursor-pointer hover:bg-gray-100 transition relative overflow-hidden group">
                                        <div class="text-center">
                                            <span class="material-symbols-outlined text-[#B50612] text-3xl" id="uploadIcon">add_photo_alternate</span>
                                            <p class="text-xs text-gray-500 mt-1">Click to upload</p>
                                        </div>
                                        <img id="previewImage" class="absolute inset-0 w-full h-full object-cover hidden rounded-lg" />
                                        <input type="file" id="productPhoto" name="photo" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 mt-2 text-center" id="fileName">JPG, PNG, WEBP (Max 2MB)</p>
                            </div>

                            <!-- Barcode Display -->
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 flex flex-col">
                                <h3 class="text-xs font-semibold text-gray-700 mb-3 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[#B50612] text-base">barcode_scanner</span>
                                    Barcode
                                </h3>
                                <div class="bg-white rounded-lg p-3 text-center shadow-md flex-1 flex flex-col justify-center">
                                    <img id="barcodeImage" src="{{ asset('assets/barcode.png') }}"
                                        alt="Barcode Preview" class="w-full max-w-[160px] mx-auto object-contain mb-2">
                                    <div id="autoFilledBarcode"
                                        class="mt-2 px-3 py-1.5 bg-gray-100 rounded-lg font-mono text-xs text-gray-800 tracking-widest">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1.5">(auto-filled)</p>
                                </div>
                            </div>
                        </div>

                        <!-- Product Information -->
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                            <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#B50612] text-lg">inventory_2</span>
                                Product Information
                            </h3>

                            <!-- Product Name -->
                            <div class="mb-3">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Product Name *</label>
                                <input type="text" name="name" placeholder="Enter product name" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B50612] focus:border-transparent text-sm placeholder-gray-400 transition">
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Description</label>
                                <textarea name="description" placeholder="Enter product description (optional)" rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B50612] focus:border-transparent text-sm placeholder-gray-400 resize-none transition"></textarea>
                            </div>

                            <!-- Category & Unit Grid -->
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <!-- Category -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Category *</label>
                                    <select id="categorySelect" name="category_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B50612] focus:border-transparent text-sm transition">
                                        <option value="">Select...</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat->category_id }}">{{ $cat->category }}</option>
                                        @endforeach
                                        <option value="other">Other...</option>
                                    </select>
                                    <input type="text" id="customCategory" name="custom_category"
                                        placeholder="New category"
                                        class="hidden w-full px-3 py-2 mt-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B50612] focus:border-transparent text-sm transition">
                                </div>

                                <!-- Unit -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Unit *</label>
                                    <select id="unitSelect" name="unit_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B50612] focus:border-transparent text-sm transition">
                                        <option value="">Select...</option>
                                        @foreach($units as $unit)
                                        <option value="{{ $unit->unit_id }}">{{ $unit->unit }}</option>
                                        @endforeach
                                        <option value="other">Other...</option>
                                    </select>
                                    <input type="text" id="customUnit" name="custom_unit"
                                        placeholder="New unit"
                                        class="hidden w-full px-3 py-2 mt-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent text-sm transition">
                                </div>
                            </div>

                            <!-- Stock Limit -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Minimum Stock Alert *</label>
                                <input type="number" name="stock_limit" placeholder="e.g., 5" min="1" step="1" required
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B50612] focus:border-transparent text-sm placeholder-gray-400 transition">
                                <p class="text-xs text-gray-500 mt-1">Warn when stock is at or below this number</p>
                            </div>
                        </div>

                        <!-- Pricing Section -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-200">
                            <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-green-600 text-lg">payments</span>
                                Pricing & Tax
                            </h3>

                            <!-- Bulk Purchase Calculator (Collapsible) -->
                            <div class="mb-3">
                                <button type="button" onclick="toggleBulkCalculator()" 
                                    class="w-full flex items-center justify-between p-2.5 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition text-left">
                                    <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-blue-600 text-sm">calculate</span>
                                        Bulk Purchase Calculator
                                        <span class="text-gray-500 font-normal">(Optional)</span>
                                    </span>
                                    <span class="material-symbols-outlined text-blue-600 text-lg" id="bulkToggleIcon">expand_more</span>
                                </button>
                                
                                <div id="bulkCalculatorSection" class="hidden mt-2 p-3 bg-white rounded-lg border border-blue-200 space-y-2">
                                    <p class="text-xs text-gray-600">
                                        💡 Buy in bulk (pack/box) but sell in smaller units
                                    </p>
                                    
                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Quantity</label>
                                            <input type="number" id="bulkQuantity" min="1" placeholder="Enter quantity" 
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm placeholder-gray-400">
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-xs text-gray-600 mb-1">Per</label>
                                            <select id="bulkUnit" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                                                <option value="">Select...</option>
                                                <option value="pack">Pack</option>
                                                <option value="box">Box</option>
                                                <option value="dozen">Dozen (12 pcs)</option>
                                                <option value="bundle">Bundle</option>
                                                <option value="case">Case</option>
                                                <option value="kg">Kilogram (kg)</option>
                                                <option value="sack">Sack</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Bulk Cost Price</label>
                                        <input type="number" step="0.01" min="0" id="bulkCostPrice" placeholder="₱0.00"
                                            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm placeholder-gray-400">
                                    </div>
                                    
                                    <div class="pt-2 border-t border-gray-200">
                                        <div class="flex justify-between items-center text-xs mb-2">
                                            <span class="font-semibold text-gray-700">Cost per unit:</span>
                                            <span id="calculatedUnitCost" class="font-bold text-green-600">₱0.00</span>
                                        </div>
                                        <button type="button" onclick="applyBulkCost()" 
                                            class="w-full px-3 py-1.5 bg-blue-500 text-white text-xs font-medium rounded hover:bg-blue-600 transition">
                                            Apply to Cost Price
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Cost Price & Tax Category Row -->
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 mt-2">
                                        Cost Price *
                                    </label>
                                    <input type="number" step="0.01" min="0" name="cost_price" id="costPrice" placeholder="0.00" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent text-sm placeholder-gray-400 transition">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 flex items-center gap-1">
                                        Tax Category *
                                        <button type="button" onclick="toggleVatInfo()" class="text-blue-500 hover:text-blue-700">
                                            <span class="material-symbols-outlined text-sm">info</span>
                                        </button>
                                    </label>
                                    <select id="vatCategory" name="vat_category" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 text-sm transition">
                                        <option value="vat_exempt">VAT-Exempt (0%)</option>
                                        <option value="vat_inclusive" selected>VAT-Inclusive (12%)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- VAT Info Panel (Hidden by default) -->
                            <div id="vatInfoPanel" class="hidden mb-3 p-2.5 bg-blue-50 border border-blue-200 rounded-lg text-xs">
                                <p class="font-semibold text-blue-900 mb-1.5">📋 Tax Guidelines:</p>
                                <ul class="space-y-1 text-blue-800 text-xs">
                                    <li><strong>0%:</strong> Raw vegetables, fruits, meat, fish, eggs, rice</li>
                                    <li><strong>12%:</strong> Processed foods, beverages, snacks, household items</li>
                                </ul>
                            </div>

                            <!-- Markup Row -->
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <select id="markupType" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 transition">
                                    <option value="percentage">Percentage %</option>
                                    <option value="fixed">Fixed ₱</option>
                                </select>
                                <input type="number" id="markupValue" placeholder="Markup Value" min="0" step="0.01"
                                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 placeholder-gray-400 transition">
                            </div>

                            <!-- Selling Price with Compact Breakdown -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Selling Price per Unit *</label>
                                <input type="number" step="0.01" name="selling_price" id="sellingPrice" placeholder="0.00" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-sm font-semibold mb-2" readonly>
                                
                                <!-- Compact Tax Breakdown -->
                                <div id="taxBreakdown" class="p-2.5 bg-white rounded-lg border border-gray-200">
                                    <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Cost:</span>
                                            <span id="costDisplay" class="font-medium">₱0.00</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span id="markupLabel" class="text-blue-600">Markup:</span>
                                            <span id="markupAmount" class="font-medium text-blue-600">₱0.00</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Base:</span>
                                            <span id="basePrice" class="font-medium">₱0.00</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span id="taxLabel" class="text-green-700">VAT (12%):</span>
                                            <span id="taxAmount" class="font-medium text-green-700">₱0.00</span>
                                        </div>
                                    </div>
                                    <div class="pt-1.5 mt-1.5 border-t border-gray-300 flex justify-between font-semibold text-gray-900">
                                        <span>Total Price:</span>
                                        <span id="totalPrice">₱0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN: Initial Stock -->
                    <div class="lg:col-span-1">
                        <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl p-4 border border-orange-200 h-full flex flex-col">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-orange-600 text-lg">inventory</span>
                                    Initial Stock (Optional)
                                </h3>
                                <button type="button" id="addBatchBtn"
                                    class="px-3 py-1.5 bg-orange-500 text-white text-xs font-semibold rounded-lg hover:bg-orange-600 transition flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">add</span>
                                    Add Batch
                                </button>
                            </div>

                            <p class="text-xs text-gray-600 mb-3 bg-white/60 p-2.5 rounded-lg">
                                <span class="material-symbols-outlined text-xs align-middle">info</span>
                                Add initial stock batches. You can skip this and add stock later.
                            </p>

                            <!-- Batch Rows Container -->
                            <div id="batchRowsContainer" class="space-y-3 flex-1 overflow-y-auto pr-2">
                                <!-- Initial empty state message -->
                                <div id="emptyBatchMessage" class="text-center py-12 text-gray-400">
                                    <span class="material-symbols-outlined text-5xl">inventory_2</span>
                                    <p class="text-sm mt-2">No batches added yet</p>
                                    <p class="text-xs">Click "Add Batch" to start</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </form>
        </div>

        <!-- Footer with Action Buttons -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-xl">
            <div class="flex justify-between items-center">
                <p class="text-xs text-gray-500">* Required fields</p>
                <div class="flex gap-3">
                    <button type="button" onclick="closeRegisterModal()"
                        class="px-6 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="submit" form="registerProductForm"
                        class="px-6 py-2.5 bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold rounded-lg hover:from-green-600 hover:to-green-700 transition shadow-lg hover:shadow-xl flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        Register Product
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Batch Row Template (Hidden) -->
<template id="batchRowTemplate">
    <div class="batch-row bg-white rounded-lg p-3.5 border-2 border-orange-200 relative">
        <button type="button" class="remove-batch-btn absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition shadow-md z-10">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>
        
        <div class="space-y-2.5">
            <!-- Batch Label -->
            <div class="flex items-center justify-between bg-orange-50 px-3 py-1.5 rounded-lg">
                <span class="text-xs font-bold text-orange-700 batch-label">Batch #1</span>
                <span class="material-symbols-outlined text-orange-400 text-sm">inventory</span>
            </div>

            <!-- Quantity -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Quantity *</label>
                <input type="number" name="batches[INDEX][quantity]" min="1" placeholder="Enter quantity" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm transition"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '');">
            </div>

            <!-- Expiration Date -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Expiration Date (Optional)</label>
                <input type="date" name="batches[INDEX][expiration_date]" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm transition expiration-date-input">
                <p class="text-xs text-gray-500 mt-1">Must be at least 7 days from today</p>
            </div>
        </div>
    </div>
</template>









    <script>
        const filterToggle = document.getElementById('filterToggle');
        const categoryDropdown = document.getElementById('categoryDropdown');
        const searchInput = document.getElementById('search');
        const suggestionsBox = document.getElementById('suggestions');

        // Toggle dropdown on button click
        filterToggle.addEventListener('click', function(event) {
            categoryDropdown.classList.toggle('hidden');
            event.stopPropagation(); // Prevent immediate document click listener
        });

        // Prevent closing when clicking inside dropdown
        categoryDropdown.addEventListener('click', function(event) {
            event.stopPropagation();
        });

        // Close dropdown when clicking anywhere else
        document.addEventListener('click', function() {
            categoryDropdown.classList.add('hidden');
        });

        // Filter by category (preserve status)
        function filterByCategory(categoryId) {
            const status = document.getElementById('statusInput')?.value || 'active';
            if (categoryId === 'all') {
                window.location.href = `{{ url('inventory-staff') }}?status=${status}`;
            } else {
                window.location.href = `{{ url('inventory-staff') }}?category=${categoryId}&status=${status}`;
            }
        }

        //Search functionality with autocomplete
        searchInput.addEventListener('input', function() {
            const term = searchInput.value.trim();
            if (term.length < 1) {
                suggestionsBox.classList.add('hidden');
                suggestionsBox.innerHTML = '';
                return;
            }

            fetch(`/inventory-staff/suggest?term=${encodeURIComponent(term)}`)
                .then(response => response.json())
                .then(data => {
                    suggestionsBox.innerHTML = '';

                    if (data.length === 0) {
                        suggestionsBox.classList.add('hidden');
                        return;
                    }

                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer';
                        div.textContent = item;

                        div.addEventListener('click', function() {
                            searchInput.value = item;
                            suggestionsBox.classList.add('hidden');
                            searchInput.form.submit(); // auto-submit
                        });

                        suggestionsBox.appendChild(div);
                    });

                    suggestionsBox.classList.remove('hidden');
                });
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target) && !suggestionsBox.contains(event.target)) {
                suggestionsBox.classList.add('hidden');
            }
        });
    </script>

    
    <script> // low stock & Out of Stock filter
    function filterProductTable() {
        const filterValue = document.getElementById('stockFilter').value;
        const rows = document.querySelectorAll('.product-row');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const stockStatus = row.getAttribute('data-stock-status');
            
            if (filterValue === 'all' || filterValue === stockStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Handle "no products" message
        const noProductsRow = document.querySelector('.no-products-row');
        if (noProductsRow) {
            noProductsRow.style.display = visibleCount === 0 ? '' : 'none';
        }
    }
    </script>


    <script>
        const addProductBtn = document.getElementById('addProductBtn');
        const addProductModal = document.getElementById('addProductModal');
        const closeAddProductModal = document.getElementById('closeAddProductModal');

        addProductBtn.addEventListener('click', () => {
            addProductModal.classList.remove('hidden');
            addProductModal.classList.add('flex');
        });

    closeAddProductModal.addEventListener('click', () => {
        addProductModal.classList.add('hidden');
        addProductModal.classList.remove('flex');
    });
</script>


<script>
    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Add Stock or Restock Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const addStockBtn = document.getElementById('addStockBtn');
        const chooseCategoryModal = document.getElementById('chooseCategoryModal');
        const chooseProductsModal = document.getElementById('chooseProductsModal');
        const restockDetailsModal = document.getElementById('restockDetailsModal');
        const bulkRestockForm = document.getElementById('bulkRestockForm');

        const EXPIRATION_DAYS_LIMIT = 7; // Change to 14 for two weeks, 30 for a month, etc.

        // Helper function to get minimum expiration date
        function getMinimumExpirationDate() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const minDate = new Date(today);
            
            // Add the configured days limit + 1 for the date picker
            minDate.setDate(today.getDate() + EXPIRATION_DAYS_LIMIT + 1);
            
            return minDate.toISOString().split('T')[0];
        }

        // === Open Choose Category ===
        addStockBtn?.addEventListener('click', () => {
            document.body.classList.add('modal-open');
            chooseCategoryModal.classList.remove('hidden');
            chooseCategoryModal.classList.add('flex');
        });
        document.getElementById('closeChooseCategoryModal')?.addEventListener('click', () => {
            document.body.classList.remove('modal-open');
            chooseCategoryModal.classList.add('hidden');
            chooseCategoryModal.classList.remove('flex');
        });

        // === Open Add Category Modal ===
        window.openAddCategoryModal = function() {
            document.getElementById('chooseCategoryModal').classList.add('hidden');
            const modal = document.getElementById('addCategoryModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
                
            modal.setAttribute('data-opened-from', 'restock');
            document.getElementById('newCategoryName').focus();
        };

        // === Close Add Category Modal ===
        function closeAddCategoryModal() {
            const modal = document.getElementById('addCategoryModal');
            const saveCategoryBtn = document.getElementById('saveCategoryBtn');
            
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            
            const categoryInput = document.getElementById('newCategoryName');
            if (categoryInput) {
                categoryInput.classList.remove('border-red-500', 'border-yellow-500');
                categoryInput.value = '';
                
                const existingError = categoryInput.parentNode.querySelector('.duplicate-error');
                if (existingError) {
                    existingError.remove();
                }
            }
            
            if (saveCategoryBtn) {
                saveCategoryBtn.disabled = false;
                saveCategoryBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                saveCategoryBtn.textContent = 'Save Category';
            }
        }

        // === Reopen Choose Category Modal ===
        function reopenChooseCategoryModal() {
            const chooseModal = document.getElementById('chooseCategoryModal');
            chooseModal.classList.remove('hidden');
            chooseModal.classList.add('flex');
        }

        // === Close Buttons ===
        document.getElementById('closeAddCategoryModal')?.addEventListener('click', () => {
            const modal = document.getElementById('addCategoryModal');
            
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.removeAttribute('data-opened-from');
            
            const categoryInput = document.getElementById('newCategoryName');
            const saveCategoryBtn = document.getElementById('saveCategoryBtn');
            
            if (categoryInput) {
                categoryInput.classList.remove('border-red-500', 'border-yellow-500');
                categoryInput.value = '';
                
                const existingError = categoryInput.parentNode.querySelector('.duplicate-error');
                if (existingError) {
                    existingError.remove();
                }
            }
            
            if (saveCategoryBtn) {
                saveCategoryBtn.disabled = false;
                saveCategoryBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                saveCategoryBtn.textContent = 'Save Category';
            }
            
            document.body.classList.remove('modal-open');
        });

        document.getElementById('cancelAddCategory')?.addEventListener('click', () => {
            const modal = document.getElementById('addCategoryModal');
            const openedFrom = modal.getAttribute('data-opened-from');
            
            if (openedFrom === 'barcode') {
                closeAddCategoryModalForBarcode();
            } else {
                closeAddCategoryModal();
                reopenChooseCategoryModal();
            }
        });

        // Real-time validation for category name
        const newCategoryInput = document.getElementById('newCategoryName');
        const saveCategoryBtn = document.getElementById('saveCategoryBtn');

        if (newCategoryInput && saveCategoryBtn) {
            let categoryTimeout;
            
            newCategoryInput.addEventListener('input', function() {
                clearTimeout(categoryTimeout);
                
                const existingError = this.parentNode.querySelector('.duplicate-error');
                if (existingError) {
                    existingError.remove();
                }
                
                if (!this.value.trim()) {
                    this.classList.remove('border-red-500', 'border-yellow-500');
                    saveCategoryBtn.disabled = false;
                    saveCategoryBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    return;
                }
                
                categoryTimeout = setTimeout(async () => {
                    const checkFunction = window.checkExistingName || checkExistingNameLocal;
                    const response = await checkFunction('category', this.value);
                    
                    if (response && response.exists) {
                        const errorDiv = document.createElement('div');
                        
                        if (response.isExactMatch) {
                            errorDiv.className = 'duplicate-error text-red-600 text-xs mt-1 font-semibold';
                            errorDiv.innerHTML = `Category already exists: <strong>"${response.existingName}"</strong>`;
                            this.classList.add('border-red-500');
                            this.classList.remove('border-yellow-500');
                            
                            saveCategoryBtn.disabled = true;
                            saveCategoryBtn.classList.add('opacity-50', 'cursor-not-allowed');
                            
                        } else {
                            errorDiv.className = 'duplicate-error text-yellow-600 text-xs mt-1';
                            errorDiv.innerHTML = `Similar category exists: "<strong>${response.existingName}</strong>"<br>
                                                <span class="text-gray-600">You can proceed, but consider using the existing category</span>`;
                            this.classList.add('border-yellow-500');
                            this.classList.remove('border-red-500');
                            
                            saveCategoryBtn.disabled = false;
                            saveCategoryBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                        
                        this.parentNode.appendChild(errorDiv);
                    } else {
                        this.classList.remove('border-red-500', 'border-yellow-500');
                        saveCategoryBtn.disabled = false;
                        saveCategoryBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }, 500);
            });
        }

        // Local check function (fallback if global not available)
        async function checkExistingNameLocal(type, value) {
            if (!value.trim()) return null;
            
            try {
                const response = await fetch('/check-existing-name', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        type: type,
                        name: value
                    })
                });
                
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Error checking existing name:', error);
                return null;
            }
        }

        // === Handle Add Category Form Submission ===
        document.getElementById('addCategoryForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const categoryName = document.getElementById('newCategoryName').value.trim();
            const saveCategoryBtn = document.getElementById('saveCategoryBtn');
            
            if (!categoryName) {
                showToast('Please enter a category name.', 'error');
                return;
            }

            const checkFunction = window.checkExistingName || checkExistingNameLocal;
            const response = await checkFunction('category', categoryName);
            
            if (response && response.exists && response.isExactMatch) {
                showToast(`Cannot submit: Category "${categoryName}" already exists as "${response.existingName}"`, 'error');
                saveCategoryBtn.disabled = true;
                saveCategoryBtn.classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }
            
            let userConfirmed = false;
            if (response && response.exists && !response.isExactMatch) {
                const proceed = confirm(
                    `Similar category found: "${response.existingName}"\n\n` +
                    `You're adding: "${categoryName}"\n\n` +
                    `These appear similar but are not identical.\n` +
                    `Proceed with adding this new category?`
                );
                
                if (!proceed) {
                    return;
                }
                userConfirmed = true;
            }

            saveCategoryBtn.disabled = true;
            saveCategoryBtn.textContent = 'Saving...';

            const formData = new FormData(this);
            
            if (userConfirmed) {
                formData.append('confirmed_similar', '1');
            }

            try {
                const submitResponse = await fetch('/inventory-staff/add-category', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const data = await submitResponse.json();

                if (data.success) {
                    sessionStorage.setItem('openChooseCategoryModal', 'true');
                    showToast('Category added successfully!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    saveCategoryBtn.disabled = false;
                    saveCategoryBtn.textContent = 'Save Category';
                    
                    if (data.isExactMatch) {
                        showToast(data.message, 'error');
                    } else {
                        const proceed = confirm(`${data.message}\n\nProceed anyway?`);
                        
                        if (proceed) {
                            formData.append('confirmed_similar', '1');
                            // Retry submission logic here if needed
                        }
                    }
                }
            } catch (err) {
                console.error(err);
                showToast('Something went wrong while adding category.', 'error');
                saveCategoryBtn.disabled = false;
                saveCategoryBtn.textContent = 'Save Category';
            }
        });

        // === Auto-open Choose Category Modal after reload if needed ===
        window.addEventListener('DOMContentLoaded', () => {
            if (sessionStorage.getItem('openChooseCategoryModal') === 'true') {
                sessionStorage.removeItem('openChooseCategoryModal');
                const chooseCategoryModal = document.getElementById('chooseCategoryModal');
                chooseCategoryModal.classList.remove('hidden');
                chooseCategoryModal.classList.add('flex');
            }
        });

        // === Category Click ===
        window.onCategorySelected = function(categoryId, categoryName) {
            chooseCategoryModal.classList.add('hidden');
            fetchCategoryProducts(categoryId, categoryName);
        };

        // === AJAX: Fetch Category Products ===
        function fetchCategoryProducts(categoryId, categoryName) {
            document.getElementById('selectedCategoryLabel').textContent = `Category: ${categoryName}`;
            const list = document.getElementById('categoryProductsList');
            list.innerHTML = '<tr><td colspan="3" class="p-3 text-center">Loading…</td></tr>';

            fetch(`/inventory-staff/category-products/${categoryId}`)
                .then(r => r.json())
                .then(data => {
                    list.innerHTML = '';
                    if (!data || data.length === 0) {
                        list.innerHTML = '<tr><td colspan="3" class="p-3 text-center">No products found.</td></tr>';
                    } else {
                        data.forEach(p => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                <td class="p-2"><input type="checkbox" class="selectProductCheckbox" data-prod="${p.prod_code}" data-cat="${p.category_id}" data-name="${escapeHtml(p.name)}" data-stock="${p.stock}"></td>
                <td class="p-2">${escapeHtml(p.name)}</td>
                <td class="p-2 text-center">${p.stock}</td>
            `;
                            list.appendChild(row);
                        });
                    }
                    chooseProductsModal.classList.remove('hidden');
                    chooseProductsModal.classList.add('flex');
                    document.getElementById('restockCategoryId').value = categoryId;
                })
                .catch(err => {
                    console.error(err);
                    list.innerHTML = '<tr><td colspan="3" class="p-3 text-center">Error loading products.</td></tr>';
                });
        }

        document.getElementById('closeChooseProductsModal')?.addEventListener('click', () => {
            chooseProductsModal.classList.add('hidden');
        });

        window.selectAllProducts = function() {
            document.querySelectorAll('.selectProductCheckbox').forEach(cb => cb.checked = true);
        };
        window.deselectAllProducts = function() {
            document.querySelectorAll('.selectProductCheckbox').forEach(cb => cb.checked = false);
        };

        // === Proceed to Restock Details ===
        window.proceedToRestock = function() {
            const checked = Array.from(document.querySelectorAll('.selectProductCheckbox:checked'));
            if (checked.length === 0) {
                showToast('Please select at least one product to restock.', 'warning');
                return;
            }

            const container = document.getElementById('restockRowsContainer');
            container.innerHTML = '';
            const categoryId = document.getElementById('restockCategoryId').value;
            let index = 0;

            const promises = checked.map(cb => {
                const prodCode = cb.dataset.prod;
                const prodName = cb.dataset.name;
                const currentStock = cb.dataset.stock ?? 0;

                return fetch(`/inventory-staff/get-latest-batch/${prodCode}`)
                    .then(r => r.json())
                    .then(batchResp => {
                        const nextBatch = batchResp.next_batch || `P${prodCode}-BATCH-1`;
                        addRestockRow(prodCode, prodName, categoryId, currentStock, nextBatch, index++);
                    });
            });

            Promise.all(promises).then(() => {
                chooseProductsModal.classList.add('hidden');
                restockDetailsModal.classList.remove('hidden');
                restockDetailsModal.classList.add('flex');
            }).catch(err => {
                console.error(err);
                showToast('Failed to prepare restock details.', 'error');
            });
        };

        // === Add Restock Row Function ===
        window.addRestockRow = function(prodCode, prodName, categoryId, currentStock, batchNum, index) {
            const container = document.getElementById('restockRowsContainer');
            const tr = document.createElement('tr');
            tr.classList.add('border-b');

            const minDate = getMinimumExpirationDate();

            tr.innerHTML = `
                <td class="p-2">
                ${escapeHtml(prodName)}
                <input type="hidden" name="items[${index}][prod_code]" value="${prodCode}">
                <input type="hidden" name="items[${index}][category_id]" value="${categoryId}">
                </td>
                <td class="p-2 text-center">${currentStock}</td>
                <td class="p-2 text-center">
                <input type="number" min="1" required name="items[${index}][qty]" 
                    class="border rounded px-2 py-1 w-20 text-sm text-center">
                </td>
                <td class="p-2 text-center">
                <input type="date" name="items[${index}][expiration_date]" 
                    min="${minDate}"
                    class="border rounded px-2 py-1 w-36 text-sm text-center expiration-date-input">
                </td>
                <td class="p-2 text-center">
                <input type="text" readonly name="items[${index}][batch_number]" 
                    value="${batchNum}" 
                    class="border rounded px-2 py-1 text-sm text-center bg-gray-50">
                </td>
                <td class="p-2">
                <div class="flex justify-center gap-2">
                    <button 
                    type="button" 
                    class="flex-1 bg-yellow-500 text-white text-xs font-medium px-3 py-1 rounded hover:bg-yellow-600 transition"
                    onclick="duplicateBatchRow(this, '${prodCode}', '${escapeHtml(prodName)}', '${categoryId}', '${currentStock}')">
                    Add Batch
                    </button>
                    <button 
                    type="button" 
                    class="flex-1 bg-red-600 text-white text-xs font-medium px-3 py-1 rounded hover:bg-red-700 transition"
                    onclick="this.closest('tr').remove(); checkPricingVisibility();">
                    Remove
                    </button>
                </div>
                </td>
            `;
            container.appendChild(tr);
            
            const expirationInput = tr.querySelector('.expiration-date-input');
            if (expirationInput) {
                expirationInput.addEventListener('change', function() {
                    validateExpirationDate(this);
                });
            }
            
            // Check if pricing section should be shown
            checkPricingVisibility();
        };

        // Check if pricing section should be visible (only for single product restock)
        function checkPricingVisibility() {
            const container = document.getElementById('restockRowsContainer');
            const rows = container.querySelectorAll('tr');
            const pricingSection = document.getElementById('pricingSection');
            const pricingProdCode = document.getElementById('pricingProdCode');
            
            // Get unique product codes
            const productCodes = new Set();
            rows.forEach(row => {
                const prodCodeInput = row.querySelector('input[name*="[prod_code]"]');
                if (prodCodeInput) {
                    productCodes.add(prodCodeInput.value);
                }
            });
            
            // Show pricing section only if exactly one unique product
            if (productCodes.size === 1) {
                const prodCode = Array.from(productCodes)[0];
                pricingSection.classList.remove('hidden');
                pricingProdCode.value = prodCode;
                
                // Fetch current pricing info
                fetchProductPricingInfo(prodCode);
            } else {
                pricingSection.classList.add('hidden');
                pricingProdCode.value = '';
                clearPricingFields();
            }
        }

        // Fetch product pricing information
        function fetchProductPricingInfo(prodCode) {
            fetch(`/inventory/get-product-pricing/${prodCode}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Display current prices
                        document.getElementById('currentCostDisplay').textContent = '₱' + parseFloat(data.cost_price).toFixed(2);
                        document.getElementById('currentSellingDisplay').textContent = '₱' + parseFloat(data.selling_price).toFixed(2);
                        document.getElementById('currentVatDisplay').textContent = data.vat_category === 'vat_exempt' ? 'VAT-Exempt' : 'VAT-Inclusive';
                        
                        // ✅ AUTO-FILL the new price fields with current values
                        document.getElementById('newCostPrice').value = parseFloat(data.cost_price).toFixed(2);
                        document.getElementById('newVatCategory').value = data.vat_category || 'vat_inclusive';
                        
                        // ✅ Reverse calculate markup from existing prices
                        reverseCalculateRestockMarkup(
                            parseFloat(data.cost_price),
                            parseFloat(data.selling_price),
                            data.vat_category || 'vat_inclusive'
                        );
                        
                        // ✅ Calculate and display the selling price breakdown
                        calculateRestockSellingPrice();
                    }
                })
                .catch(error => {
                    console.error('Error fetching pricing info:', error);
                });
        }

        // ✅ NEW: Reverse calculate markup from existing prices for restock modal
        function reverseCalculateRestockMarkup(cost, selling, vatCat) {
            if (cost === 0 || selling === 0) {
                return;
            }
            
            // Remove VAT from selling price to get base price
            let basePrice = selling;
            if (vatCat === 'vat_inclusive') {
                basePrice = selling / 1.12;
            }
            
            // Calculate markup amount
            const markupAmt = basePrice - cost;
            
            if (markupAmt <= 0) {
                document.getElementById('newMarkupValue').value = '';
                return;
            }
            
            // Calculate percentage markup and set as default
            const markupPercentage = (markupAmt / cost) * 100;
            document.getElementById('newMarkupType').value = 'percentage';
            document.getElementById('newMarkupValue').value = markupPercentage.toFixed(2);
        }

        // Clear pricing input fields
        function clearPricingFields() {
            document.getElementById('newCostPrice').value = '';
            document.getElementById('newVatCategory').value = '';
            document.getElementById('newMarkupType').value = 'percentage';
            document.getElementById('newMarkupValue').value = '';
            document.getElementById('newSellingPrice').value = '';
            
            // Clear breakdown
            document.getElementById("newCostDisplay").textContent = '₱0.00';
            document.getElementById("newMarkupAmount").textContent = '₱0.00';
            document.getElementById("newBasePrice").textContent = '₱0.00';
            document.getElementById("newTaxAmount").textContent = '₱0.00';
            document.getElementById("newTotalPrice").textContent = '₱0.00';
        }

        // === Duplicate Row for the Same Product (increments batch) ===
        window.duplicateBatchRow = function(button, prodCode, prodName, categoryId, currentStock) {
            const container = document.getElementById('restockRowsContainer');
            const rows = container.querySelectorAll('tr');
            let highestBatch = 0;
            let lastSameProductRow = null;

            rows.forEach(row => {
                const batchInput = row.querySelector(`input[name*="[batch_number]"]`);
                const prodInput = row.querySelector(`input[name*="[prod_code]"]`);
                if (prodInput && prodInput.value === prodCode && batchInput) {
                    lastSameProductRow = row;
                    const match = batchInput.value.match(/P\d+-BATCH-(\d+)/);
                    if (match) {
                        const num = parseInt(match[1]);
                        if (num > highestBatch) highestBatch = num;
                    }
                }
            });

            const nextBatch = `P${prodCode}-BATCH-${highestBatch + 1}`;
            const newIndex = container.querySelectorAll('tr').length;

            const tr = document.createElement('tr');
            tr.classList.add('border-b');
            
            const minDate = getMinimumExpirationDate();
            
            tr.innerHTML = `
        <td class="p-2">
            ${escapeHtml(prodName)}
            <input type="hidden" name="items[${newIndex}][prod_code]" value="${prodCode}">
            <input type="hidden" name="items[${newIndex}][category_id]" value="${categoryId}">
        </td>
        <td class="p-2 text-center">${currentStock}</td>
        <td class="p-2 text-center">
            <input type="number" min="1" required name="items[${newIndex}][qty]" 
                class="border rounded px-2 py-1 w-20 text-sm text-center">
        </td>
        <td class="p-2 text-center">
            <input type="date" name="items[${newIndex}][expiration_date]" 
                min="${minDate}"
                class="border rounded px-2 py-1 w-36 text-sm text-center expiration-date-input">
        </td>
        <td class="p-2 text-center">
            <input type="text" readonly name="items[${newIndex}][batch_number]" 
                value="${nextBatch}" 
                class="border rounded px-2 py-1 text-sm text-center bg-gray-50">
        </td>
        <td class="p-2">
            <div class="flex justify-center gap-2">
                <button 
                    type="button" 
                    class="flex-1 bg-yellow-500 text-white text-xs font-medium px-3 py-1 rounded hover:bg-yellow-600 transition"
                    onclick="duplicateBatchRow(this, '${prodCode}', '${escapeHtml(prodName)}', '${categoryId}', '${currentStock}')">
                    Add Batch
                </button>
                <button 
                    type="button" 
                    class="flex-1 bg-red-600 text-white text-xs font-medium px-3 py-1 rounded hover:bg-red-700 transition"
                    onclick="this.closest('tr').remove()">
                    Remove
                </button>
            </div>
        </td>
    `;

            if (lastSameProductRow) {
                if (lastSameProductRow.nextSibling) {
                    container.insertBefore(tr, lastSameProductRow.nextSibling);
                } else {
                    container.appendChild(tr);
                }
            } else {
                container.appendChild(tr);
            }
            
            const expirationInput = tr.querySelector('.expiration-date-input');
            if (expirationInput) {
                expirationInput.addEventListener('change', function() {
                    validateExpirationDate(this);
                });
            }
        };

        // === Close Restock Modal ===
        window.closeRestockDetails = function() {
            const modal = document.getElementById('restockDetailsModal');
            if (modal) modal.classList.add('hidden');
        };

        document.getElementById('closeRestockDetailsModal')?.addEventListener('click', window.closeRestockDetails);

        // === Helper: Escape HTML ===
        function escapeHtml(text) {
            if (!text) return '';
            return ('' + text)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }
        
        // === Validate expiration date function ===
        function validateExpirationDate(input) {
            if (!input.value) return true;
            
            const selectedDate = new Date(input.value);
            selectedDate.setHours(0, 0, 0, 0);
            
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const minDate = new Date(today);
            minDate.setDate(today.getDate() + EXPIRATION_DAYS_LIMIT);
            
            const timeDiff = selectedDate - today;
            const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
            
            // Remove existing error
            const existingError = input.parentNode.querySelector('.expiration-error');
            if (existingError) {
                existingError.remove();
            }
            
            // Check if date is valid (must be AFTER minDate, not equal)
            if (selectedDate < minDate) {
                input.classList.add('border-red-500');
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'expiration-error text-red-500 text-xs mt-1 absolute bg-white px-1';
                
                if (daysDiff < 0) {
                    errorDiv.textContent = 'Date cannot be in the past';
                } else if (daysDiff === 0) {
                    errorDiv.textContent = `Date must be at least ${EXPIRATION_DAYS_LIMIT} days from today (selected: today)`;
                } else {
                    errorDiv.textContent = `Date must be at least ${EXPIRATION_DAYS_LIMIT} days from today (selected: ${daysDiff} day${daysDiff !== 1 ? 's' : ''})`;
                }
                
                input.parentNode.style.position = 'relative';
                input.parentNode.appendChild(errorDiv);
                
                return false;
            } else {
                input.classList.remove('border-red-500');
                return true;
            }
        }

        // === Bulk Restock Submit Handling ===
        if (bulkRestockForm) {
            bulkRestockForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate expiration dates
                const expirationInputs = document.querySelectorAll('.expiration-date-input');
                let hasInvalidDates = false;
                const invalidProducts = [];
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const minDate = new Date(today);
                minDate.setDate(today.getDate() + EXPIRATION_DAYS_LIMIT);

                expirationInputs.forEach(input => {
                    if (input.value && !validateExpirationDate(input)) {
                        hasInvalidDates = true;
                        const selectedDate = new Date(input.value);
                        selectedDate.setHours(0, 0, 0, 0);
                        const timeDiff = selectedDate - today;
                        const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
                        
                        const productRow = input.closest('tr');
                        const productName = productRow.querySelector('td:first-child').textContent.trim();
                        
                        if (daysDiff < 0) {
                            invalidProducts.push(`${productName} (${input.value} - date is in the past)`);
                        } else {
                            invalidProducts.push(`${productName} (${input.value} - only ${daysDiff} day${daysDiff !== 1 ? 's' : ''} away)`);
                        }
                    }
                });

                if (hasInvalidDates) {
                    showToast(`Cannot submit: All products must have expiration dates at least ${EXPIRATION_DAYS_LIMIT} days from today.`, 'error');
                    return;
                }
                
                const formData = new FormData(bulkRestockForm);
                
                // Add pricing data if present
                const newCostPrice = document.getElementById('newCostPrice').value;
                const newSellingPrice = document.getElementById('newSellingPrice').value;
                const newVatCategory = document.getElementById('newVatCategory').value;
                const pricingProdCode = document.getElementById('pricingProdCode').value;
                
                if (pricingProdCode && (newCostPrice || newSellingPrice || newVatCategory)) {
                    formData.append('update_pricing', '1');
                    if (newCostPrice) formData.append('new_cost_price', newCostPrice);
                    if (newSellingPrice) formData.append('new_selling_price', newSellingPrice);
                    if (newVatCategory) formData.append('new_vat_category', newVatCategory);
                    formData.append('pricing_prod_code', pricingProdCode);
                }

                fetch('/inventory-staff/bulk-restock', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            setTimeout(() => {
                                window.location.href = '/inventory-staff';
                            }, 1000);
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showToast('Something went wrong. Please try again.', 'error');
                    });
            });
        }
    });
</script>

<script>
    // Quick Restock - Open restock modal directly for a single product
    function openQuickRestockForProduct(prodCode, prodName, categoryId, currentStock, event) {
        if (event && event.target.closest('.space-x-2')) {
            return;
        }

        closeAllModals();
        
        fetch(`/inventory-staff/get-latest-batch/${prodCode}`)
            .then(r => r.json())
            .then(batchResp => {
                const nextBatch = batchResp.next_batch || 'BATCH-1';
                
                const restockDetailsModal = document.getElementById('restockDetailsModal');
                const container = document.getElementById('restockRowsContainer');
                
                if (restockDetailsModal && container) {
                    container.innerHTML = '';
                    
                    document.getElementById('restockCategoryId').value = categoryId;
                    
                    addRestockRow(
                        prodCode,
                        prodName,
                        categoryId,
                        currentStock,
                        nextBatch,
                        0
                    );
                    
                    restockDetailsModal.classList.remove('hidden');
                    restockDetailsModal.classList.add('flex');
                    
                    setTimeout(() => {
                        const qtyInput = container.querySelector('input[name*="[qty]"]');
                        if (qtyInput) {
                            qtyInput.focus();
                            qtyInput.select();
                        }
                    }, 300);
                }
            })
            .catch(err => {
                console.error('Error fetching batch info:', err);
                showToast('Failed to prepare restock details.', 'error');
            });
    }
</script>

<script>
 // ===== RESTOCK PRICING FUNCTIONALITY =====

// Toggle VAT info in restock modal
function toggleRestockVatInfo() {
    const panel = document.getElementById('restockVatInfoPanel');
    if (panel) {
        panel.classList.toggle('hidden');
    }
}

// Toggle bulk calculator in restock modal
function toggleRestockBulkCalculator() {
    const section = document.getElementById('restockBulkCalculatorSection');
    const toggleIcon = document.getElementById('restockBulkToggleIcon');
    
    if (section.classList.contains('hidden')) {
        section.classList.remove('hidden');
        toggleIcon.textContent = 'expand_less';
    } else {
        section.classList.add('hidden');
        toggleIcon.textContent = 'expand_more';
        // Clear fields
        document.getElementById('restockBulkQuantity').value = '';
        document.getElementById('restockBulkUnit').value = '';
        document.getElementById('restockBulkCostPrice').value = '';
        document.getElementById('restockCalculatedUnitCost').textContent = '₱0.00';
    }
}

// Calculate unit cost from bulk purchase in restock modal
function calculateRestockBulkUnitCost() {
    const bulkQuantity = parseFloat(document.getElementById('restockBulkQuantity').value) || 0;
    const bulkCostPrice = parseFloat(document.getElementById('restockBulkCostPrice').value) || 0;
    const bulkUnit = document.getElementById('restockBulkUnit').value;
    
    if (bulkQuantity <= 0 || bulkCostPrice <= 0) {
        document.getElementById('restockCalculatedUnitCost').textContent = '₱0.00';
        return 0;
    }
    
    let effectiveQuantity = bulkQuantity;
    if (bulkUnit === 'dozen') {
        effectiveQuantity = 12;
    }
    
    const unitCost = bulkCostPrice / effectiveQuantity;
    document.getElementById('restockCalculatedUnitCost').textContent = '₱' + unitCost.toFixed(2);
    
    return unitCost;
}

// Apply bulk cost to restock cost price
function applyRestockBulkCost() {
    const unitCost = calculateRestockBulkUnitCost();
    
    if (unitCost > 0) {
        document.getElementById('newCostPrice').value = unitCost.toFixed(2);
        calculateRestockSellingPrice();
        
        const calculatedCostEl = document.getElementById('restockCalculatedUnitCost');
        const originalText = calculatedCostEl.textContent;
        calculatedCostEl.textContent = '✓ Applied!';
        calculatedCostEl.classList.add('text-green-600');
        
        setTimeout(() => {
            calculatedCostEl.textContent = originalText;
        }, 2000);
    } else {
        alert('Please fill in all bulk purchase fields correctly.');
    }
}

// Calculate new selling price in restock modal
function calculateRestockSellingPrice() {
    const cost = parseFloat(document.getElementById("newCostPrice").value) || 0;
    const type = document.getElementById("newMarkupType").value;
    const markup = parseFloat(document.getElementById("newMarkupValue").value) || 0;
    const vatCategory = document.getElementById("newVatCategory").value;
    
    // If no cost price entered, don't calculate
    if (cost === 0) {
        document.getElementById("newSellingPrice").value = '';
        document.getElementById("newCostDisplay").textContent = '₱0.00';
        document.getElementById("newMarkupAmount").textContent = '₱0.00';
        document.getElementById("newBasePrice").textContent = '₱0.00';
        document.getElementById("newTaxAmount").textContent = '₱0.00';
        document.getElementById("newTotalPrice").textContent = '₱0.00';
        return;
    }
    
    let markupAmount = 0;
    let basePrice = cost;

    if (type === "percentage") {
        markupAmount = cost * (markup / 100);
        basePrice = cost + markupAmount;
    } else {
        markupAmount = markup;
        basePrice = cost + markupAmount;
    }

    // Determine VAT based on selection or keep current
    let taxAmount = 0;
    let taxLabel = '';
    let sellingPrice = basePrice;
    
    // Get current VAT if not changing
    const currentVat = document.getElementById('currentVatDisplay').textContent;
    const effectiveVat = vatCategory || (currentVat === 'VAT-Exempt' ? 'vat_exempt' : 'vat_inclusive');

    if (effectiveVat === 'vat_exempt') {
        taxAmount = 0;
        taxLabel = 'VAT (0%)';
        sellingPrice = basePrice;
    } else {
        taxAmount = basePrice * 0.12;
        taxLabel = 'VAT (12%)';
        sellingPrice = basePrice + taxAmount;
    }

    const markupLabel = type === "percentage" 
        ? `Markup (${markup}%):` 
        : `Markup (₱${markup}):`;

    document.getElementById("newSellingPrice").value = sellingPrice.toFixed(2);
    document.getElementById("newCostDisplay").textContent = '₱' + cost.toFixed(2);
    document.getElementById("newMarkupLabel").textContent = markupLabel;
    document.getElementById("newMarkupAmount").textContent = '₱' + markupAmount.toFixed(2);
    document.getElementById("newBasePrice").textContent = '₱' + basePrice.toFixed(2);
    document.getElementById("newTaxLabel").textContent = taxLabel + ':';
    document.getElementById("newTaxAmount").textContent = '₱' + taxAmount.toFixed(2);
    document.getElementById("newTotalPrice").textContent = '₱' + sellingPrice.toFixed(2);
    
    const taxAmountEl = document.getElementById("newTaxAmount");
    if (effectiveVat === 'vat_exempt') {
        taxAmountEl.parentElement.classList.add('text-gray-500');
        taxAmountEl.parentElement.classList.remove('text-green-700');
    } else {
        taxAmountEl.parentElement.classList.add('text-green-700');
        taxAmountEl.parentElement.classList.remove('text-gray-500');
    }
}

// Initialize pricing event listeners for restock modal
document.addEventListener("DOMContentLoaded", () => {
    ["newCostPrice", "newMarkupType", "newMarkupValue", "newVatCategory"].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener("input", calculateRestockSellingPrice);
            element.addEventListener("change", calculateRestockSellingPrice);
        }
    });
    
    // Bulk calculator listeners for restock
    const restockBulkQuantity = document.getElementById('restockBulkQuantity');
    const restockBulkCostPrice = document.getElementById('restockBulkCostPrice');
    const restockBulkUnit = document.getElementById('restockBulkUnit');
    
    if (restockBulkQuantity && restockBulkCostPrice && restockBulkUnit) {
        [restockBulkQuantity, restockBulkCostPrice, restockBulkUnit].forEach(el => {
            el.addEventListener('input', calculateRestockBulkUnitCost);
            el.addEventListener('change', calculateRestockBulkUnitCost);
        });
    }
});
</script>

<!-- Type Barcode Modal JavaScript -->
<script>
    function openTypeModal() {
        const modal = document.getElementById('typeBarcodeModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            // Clear input and focus when opening modal
            const barcodeInput = document.getElementById('barcodeInput');
            if (barcodeInput) {
                barcodeInput.value = '';
                barcodeInput.focus();
            }
        }
    }

        function closeTypeModal() {
            const modal = document.getElementById('typeBarcodeModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

    function openBarcodeExistsModal(product) {
        closeAllModals();
        
        // Populate hidden fields with product data if provided
        if (product) {
            document.getElementById('existingProductCode').value = product.prod_code || '';
            document.getElementById('existingProductName').value = product.name || '';
            document.getElementById('existingCategoryId').value = product.category_id || '';
            document.getElementById('existingCurrentStock').value = product.stock || 0;
        }
        
        const modal = document.getElementById('barcodeAlreadyExistsModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeBarcodeExistsModal() {
        const modal = document.getElementById('barcodeAlreadyExistsModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        // Reopen the type barcode modal so user can try again
        openTypeModal();
    }

        function goToInventory() {
            // Redirect to inventory page
            window.location.href = "{{ route('inventory-staff') }}";
        }

    //  Close all modals complete version
    function closeAllModals() {
        const modalIds = [
            'typeBarcodeModal', 
            'barcodeExistsModal', 
            'barcodeNotFoundModal', 
            'registerProductModal', 
            'barcodeAlreadyExistsModal',
            'restockDetailsModal',
            'scanBarcodeModal',
            'chooseProductsModal',
            'chooseCategoryModal',
            'addProductModal',
            'chooseCategoryBarcodeModal',
            'customCategoryBarcodeModal',
            'generateBarcodeModal',
            'addCategoryModal'
        ];
        
        modalIds.forEach(id => {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                // modal.classList.remove('items-center', 'justify-center');
            }
        });
        
        document.body.classList.remove('modal-open');
    }

        function reopenTypeModal() {
            closeAllModals();
            openTypeModal();
        }



        // Function to detect if we're in registration context - IMPROVED
        function isRegistrationContext() {
            // Check URL for registration-related paths
            const currentPath = window.location.pathname;
            const isRegistrationPath = currentPath.includes('register') ||
                currentPath.includes('add-product') ||
                currentPath.includes('create');

            // Check if we have a register product modal that's meant to be used
            const registerModal = document.getElementById('registerProductModal');
            const hasRegisterModal = registerModal !== null;

            // If we're on the main inventory page but have a register modal, 
            // we're likely in registration context
            return isRegistrationPath || hasRegisterModal;
        }

        function checkBarcode() {
            const barcodeInput = document.getElementById('barcodeInput');
            const barcode = barcodeInput ? barcodeInput.value.trim() : '';

            if (!barcode) {
                alert("Please enter a barcode.");
                return;
            }

            console.log('Checking barcode:', barcode);
            console.log('Registration context:', isRegistrationContext());

            fetch('/inventory-staff/check-barcode', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        barcode
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    console.log('Barcode check response:', data);
                    closeAllModals();

                if (data.exists === true && data.product) {
                    // Barcode exists - show appropriate modal based on context
                    if (isRegistrationContext()) {
                        console.log('Showing barcode exists modal for registration');
                        openBarcodeExistsModal(data.product);  // Pass product data
                    } else {
                        console.log('Showing barcode exists modal for restocking');
                        const existsModal = document.getElementById('barcodeExistsModal');
                        if (existsModal) existsModal.classList.remove('hidden');

                            // Attach the product info dynamically to the Restock button
                            const restockBtn = document.getElementById('barcodeExistsRestockBtn');
                            if (restockBtn) {
                                restockBtn.onclick = function() {
                                    closeAllModals();
                                    openRestockModal(
                                        data.product.prod_code,
                                        data.product.name,
                                        data.product.prod_image,
                                        data.product.category_id,
                                        data.product.barcode
                                    );
                                };
                            }
                        }
                    } else if (data.exists === false) {
                        // Barcode doesn't exist
                        console.log('Barcode not found in database');

                        if (isRegistrationContext()) {
                            // If in registration context, proceed with registration
                            console.log('Opening register modal with barcode:', barcode);
                            openRegisterModal(barcode);
                        } else {
                            // If in inventory context, show not found modal
                            console.log('Showing barcode not found modal');
                            const notFoundModal = document.getElementById('barcodeNotFoundModal');
                            if (notFoundModal) notFoundModal.classList.remove('hidden');
                        }
                    } else {
                        console.warn('Unexpected response structure:', data);
                    }
                })
                .catch(error => {
                    console.error('Error checking barcode:', error);
                    alert('Something went wrong while checking the barcode.');
                });
        }

        document.addEventListener("DOMContentLoaded", () => {
            const form = document.getElementById("barcodeForm");
            const barcodeInput = document.getElementById("barcodeInput");

            if (form) {
                form.addEventListener("submit", function(e) {
                    e.preventDefault(); // prevent page reload
                    checkBarcode(); // call your barcode check function
                });
            }

            // Allow Enter key to submit the form
            if (barcodeInput) {
                barcodeInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        checkBarcode();
                    }
                });
            }

            // Debug: Log modal states
            console.log('Type Barcode Modal loaded');
            console.log('Register Product Modal exists:', document.getElementById('registerProductModal') !== null);
            console.log('Current path:', window.location.pathname);
        });
    </script>


<!-- Scan Barcode Modal JavaScript -->
<script>
    // Quick Scan Button - Direct to Scan Modal
    document.addEventListener("DOMContentLoaded", () => {
        const quickScanBtn = document.getElementById('quickScanBtn');
        
        if (quickScanBtn) {
            quickScanBtn.addEventListener('click', () => {
                openQuickScanModal();
            });
        }
    });

    // New function for Quick Scan flow
    function openQuickScanModal() {
        closeAllModals();
        const modal = document.getElementById('scanBarcodeModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                document.getElementById('scannedBarcodeInput').focus();
            }, 300);
        }
    }


    function openScanModal() {
        document.getElementById('scanBarcodeModal').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('scannedBarcodeInput').focus();
        }, 300);
    }

        function closeScanModal() {
            document.getElementById('scanBarcodeModal').classList.add('hidden');
            document.getElementById('scannedBarcodeInput').value = '';
        }


        // Automatically detect scanner input and process it when Enter is pressed
        document.getElementById('scannedBarcodeInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                processScannedBarcode();
            }
        });

        function processScannedBarcode() {
        const barcode = document.getElementById('scannedBarcodeInput').value.trim();
        if (!barcode) {
            alert("Please scan a barcode first.");
            return;
        }

        console.log("=== BARCODE SCAN DEBUG START ===");
        console.log("1. Scanned barcode:", barcode);

        fetch('/check-barcode', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    barcode: barcode
                })
            })
            .then(response => {
                console.log("2. Response received, status:", response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log("3. Raw response data:", JSON.stringify(data, null, 2));
                console.log("4. data.exists:", data.exists);
                console.log("5. data.product:", data.product);
                
                if (data.product) {
                    console.log("6. Product details breakdown:");
                    console.log("   - prod_code:", data.product.prod_code);
                    console.log("   - name:", data.product.name);
                    console.log("   - category_id:", data.product.category_id);
                    console.log("   - current_stock:", data.product.current_stock);
                    console.log("   - stock:", data.product.stock);
                }
                
                if (data.exists && data.product) {
                    console.log("7. ✅ Product exists, calling openRestockModalForScannedProduct");
                    console.log("8. Passing product with stock:", data.product.current_stock || data.product.stock);
                    closeScanModal();
                    openRestockModalForScannedProduct(data.product);
                } else {
                    console.log("7. ❌ Product not found, opening register modal");
                    closeScanModal();
                    openRegisterModal(barcode);
                }
                console.log("=== BARCODE SCAN DEBUG END ===");
            })
            .catch(error => {
                console.error('❌ Error in processScannedBarcode:', error);
                alert('Error checking barcode. Please try again.');
            });
    }


    // New function to open restock modal for a scanned product
    function openRestockModalForScannedProduct(product) {
        console.log("=== OPEN RESTOCK MODAL DEBUG START ===");
        console.log("1. Function called with product:", product);
        console.log("2. Product structure check:");
        console.log("   - Has prod_code:", !!product.prod_code);
        console.log("   - Has name:", !!product.name);
        console.log("   - Has category_id:", !!product.category_id);
        console.log("   - Has current_stock:", !!product.current_stock);
        console.log("   - Has stock:", !!product.stock);
        console.log("3. Stock values:");
        console.log("   - product.current_stock:", product.current_stock);
        console.log("   - product.stock:", product.stock);
        
        closeAllModals();
        
        // Determine which stock value to use
        const currentStock = product.current_stock !== undefined ? product.current_stock : (product.stock !== undefined ? product.stock : 0);
        console.log("4. Final stock value to use:", currentStock);
        
        // Fetch the latest batch number
        fetch(`/inventory/get-latest-batch/${product.prod_code}`)
            .then(r => {
                console.log("5. Batch fetch response received");
                return r.json();
            })
            .then(batchResp => {
                console.log("6. Batch response:", batchResp);
                const nextBatch = batchResp.next_batch || 'BATCH-1';
                console.log("7. Next batch number:", nextBatch);
                
                // Open restock details modal directly
                const restockDetailsModal = document.getElementById('restockDetailsModal');
                const container = document.getElementById('restockRowsContainer');
                
                if (restockDetailsModal && container) {
                    console.log("8. Modal elements found");
                    container.innerHTML = '';
                    
                    // Set category ID
                    document.getElementById('restockCategoryId').value = product.category_id;
                    console.log("9. Category ID set:", product.category_id);
                    
                    console.log("10. Calling addRestockRow with:");
                    console.log("    - prod_code:", product.prod_code);
                    console.log("    - name:", product.name);
                    console.log("    - category_id:", product.category_id);
                    console.log("    - currentStock:", currentStock);
                    console.log("    - nextBatch:", nextBatch);
                    
                    // Call addRestockRow with the stock value
                    addRestockRow(
                        product.prod_code,
                        product.name,
                        product.category_id,
                        currentStock,  // This is the critical parameter
                        nextBatch,
                        0
                    );
                    
                    console.log("11. addRestockRow called successfully");
                    
                    restockDetailsModal.classList.remove('hidden');
                    restockDetailsModal.classList.add('flex');
                    console.log("12. Modal displayed");
                    
                    setTimeout(() => {
                        const qtyInput = container.querySelector('input[name*="[qty]"]');
                        if (qtyInput) {
                            qtyInput.focus();
                            qtyInput.select();
                            console.log("13. Quantity input focused");
                        }
                    }, 300);
                } else {
                    console.error("❌ Modal or container not found");
                    console.log("    - restockDetailsModal:", restockDetailsModal);
                    console.log("    - container:", container);
                }
                console.log("=== OPEN RESTOCK MODAL DEBUG END ===");
            })
            .catch(err => {
                console.error('❌ Error fetching batch info:', err);
                alert('Failed to prepare restock details.');
            });
    }

    function showBarcodeExistsModal(product) {
        closeScanModal();

        document.getElementById('existingProductCode').value = product.prod_code || '';
        document.getElementById('existingProductName').value = product.name || '';
        document.getElementById('existingCategoryId').value = product.category_id || '';
        document.getElementById('existingCurrentStock').value = product.stock || 0;
        
        // Show modal
        document.getElementById('barcodeAlreadyExistsModal').classList.remove('hidden');
        document.getElementById('barcodeAlreadyExistsModal').classList.add('flex');
        
        console.log('Existing product:', product);
    }

        function goToInventory() {
            window.location.href = '/inventory-staff';
        }
    </script>

<!-- Generate Barcode JavaScript -->
<script>
        let selectedCategoryData = {
            id: null,
            name: '',
            isNew: false
        };
        let currentBarcode = '';

        // Open Generate Barcode Flow
        window.openGenerateModal = function() {
            closeAllModals(); 
            
            const modal = document.getElementById('chooseCategoryBarcodeModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        // Category Selection Handler
        window.selectCategoryForBarcode = function(categoryId, categoryName) {
            if (categoryId === 'new') {
                // Close choose category modal and open custom category modal
                document.getElementById('chooseCategoryBarcodeModal').classList.add('hidden');
                document.getElementById('customCategoryBarcodeModal').classList.remove('hidden');
                document.getElementById('customCategoryBarcodeModal').classList.add('flex');
                document.getElementById('newCategoryName').value = '';
                document.getElementById('newCategoryName').focus();
            } else {
                // Proceed with existing category
                selectedCategoryData = {
                    id: categoryId,
                    name: categoryName,
                    isNew: false
                };
                proceedToBarcodeGeneration();
            }
        };

        // Confirm Custom Category for Barcode Generation
        window.confirmCustomCategory = async function() {
            const customName = document.getElementById('newCategoryNameBarcode').value.trim();
            const saveCategoryBarcodeBtn = document.getElementById('saveCategoryBarcodeBtn');
            
            if (!customName) {
                showToast("Please enter a category name.", 'error');
                return;
            }

            // Check for existing categories
            const checkFunction = window.checkExistingName || checkExistingNameLocal;
            const response = await checkFunction('category', customName);
            
            // Block exact matches
            if (response && response.exists && response.isExactMatch) {
                showToast(`Cannot submit: Category "${customName}" already exists as "${response.existingName}"`, 'error');
                saveCategoryBarcodeBtn.disabled = true;
                saveCategoryBarcodeBtn.classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }
            
            // Confirm similar matches
            let userConfirmed = false;
            if (response && response.exists && !response.isExactMatch) {
                const proceed = confirm(
                    `Similar category found: "${response.existingName}"\n\n` +
                    `You're adding: "${customName}"\n\n` +
                    `These appear similar but are not identical.\n` +
                    `Proceed with adding this new category?`
                );
                
                if (!proceed) {
                    return;
                }
                userConfirmed = true;
            }

            // Disable button during submission
            saveCategoryBarcodeBtn.disabled = true;
            saveCategoryBarcodeBtn.textContent = 'Saving...';

            // Prepare form data
            const formData = new FormData();
            formData.append('category', customName);
            
            if (userConfirmed) {
                formData.append('confirmed_similar', '1');
            }

            try {
                const submitResponse = await fetch('/inventory-staff/add-category', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await submitResponse.json();

                if (data.success) {
                    showToast('Category added successfully!', 'success');
                    
                    // ✅ ADD THE NEW CATEGORY TO THE DROPDOWN IMMEDIATELY
                    const categorySelect = document.getElementById('categorySelect');
                    if (categorySelect && data.category_id) {
                        // ✅ IMPORTANT: Remove any existing option with same ID first (prevent duplicates)
                        const existingOption = categorySelect.querySelector(`option[value="${data.category_id}"]`);
                        if (existingOption) {
                            existingOption.remove();
                        }
                        
                        // Create new option element
                        const newOption = document.createElement('option');
                        newOption.value = data.category_id; // ✅ Make sure this is a string
                        newOption.textContent = customName;
                        
                        // Insert before the "Other..." option
                        const otherOption = Array.from(categorySelect.options).find(opt => 
                            opt.value === 'other' || opt.textContent.toLowerCase().includes('other')
                        );
                        if (otherOption) {
                            categorySelect.insertBefore(newOption, otherOption);
                        } else {
                            categorySelect.appendChild(newOption);
                        }
                        
                        console.log('Added category to dropdown:', { id: data.category_id, name: customName }); // Debug log
                    }
                    
                    // Store the new category data with the actual ID from database
                    selectedCategoryData = {
                        id: String(data.category_id), // ✅ Ensure it's a string
                        name: customName,
                        isNew: false // ✅ Set to false since it's now in the dropdown
                    };
                    
                    console.log('Selected category data:', selectedCategoryData); // Debug log
                    
                    // Close custom category modal
                    document.getElementById('customCategoryBarcodeModal').classList.add('hidden');
                    document.getElementById('customCategoryBarcodeModal').classList.remove('flex');
                    
                    // Proceed to barcode generation
                    proceedToBarcodeGeneration();
                    
                } else {
                    // Re-enable button on error
                    saveCategoryBarcodeBtn.disabled = false;
                    saveCategoryBarcodeBtn.textContent = 'Save Category';
                    
                    if (data.isExactMatch) {
                        showToast(data.message, 'error');
                    } else {
                        const proceed = confirm(`${data.message}\n\nProceed anyway?`);
                        
                        if (proceed) {
                            formData.append('confirmed_similar', '1');
                            const retryResponse = await fetch('/inventory-staff/add-category', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                                },
                                body: formData
                            });
                            
                            const retryData = await retryResponse.json();
                            if (retryData.success) {
                                showToast('Category added successfully!', 'success');
                                
                                // ✅ ADD THE NEW CATEGORY TO THE DROPDOWN IMMEDIATELY
                                const categorySelect = document.getElementById('categorySelect');
                                if (categorySelect && retryData.category_id) {
                                    const newOption = document.createElement('option');
                                    newOption.value = retryData.category_id;
                                    newOption.textContent = customName;
                                    
                                    const otherOption = Array.from(categorySelect.options).find(opt => opt.value === 'other');
                                    if (otherOption) {
                                        categorySelect.insertBefore(newOption, otherOption);
                                    } else {
                                        categorySelect.appendChild(newOption);
                                    }
                                }
                                
                                selectedCategoryData = {
                                    id: retryData.category_id,
                                    name: customName,
                                    isNew: false // ✅ Set to false since it's now in the dropdown
                                };
                                
                                document.getElementById('customCategoryBarcodeModal').classList.add('hidden');
                                document.getElementById('customCategoryBarcodeModal').classList.remove('flex');
                                proceedToBarcodeGeneration();
                            } else {
                                showToast(retryData.message, 'error');
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error adding category:', error);
                showToast('Error adding category. Please try again.', 'error');
                
                // Re-enable button on error
                saveCategoryBarcodeBtn.disabled = false;
                saveCategoryBarcodeBtn.textContent = 'Save Category';
            }
        };


        // Real-time validation for barcode generation category input
        document.addEventListener("DOMContentLoaded", () => {
            const newCategoryInputBarcode = document.getElementById('newCategoryNameBarcode');
            const saveCategoryBarcodeBtn = document.getElementById('saveCategoryBarcodeBtn');

            if (newCategoryInputBarcode && saveCategoryBarcodeBtn) {
                let categoryTimeout;
                
                newCategoryInputBarcode.addEventListener('input', function() {
                    clearTimeout(categoryTimeout);
                    
                    const existingError = this.parentNode.querySelector('.duplicate-error');
                    if (existingError) {
                        existingError.remove();
                    }
                    
                    if (!this.value.trim()) {
                        this.classList.remove('border-red-500', 'border-yellow-500');
                        saveCategoryBarcodeBtn.disabled = false;
                        saveCategoryBarcodeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        return;
                    }
                    
                    categoryTimeout = setTimeout(async () => {
                        const checkFunction = window.checkExistingName || checkExistingNameLocal;
                        const response = await checkFunction('category', this.value);
                        
                        if (response && response.exists) {
                            const errorDiv = document.createElement('div');
                            
                            if (response.isExactMatch) {
                                // Red error for exact match - DISABLE BUTTON
                                errorDiv.className = 'duplicate-error text-red-600 text-xs mt-1 font-semibold';
                                errorDiv.innerHTML = `Category already exists: <strong>"${response.existingName}"</strong>`;
                                this.classList.add('border-red-500');
                                this.classList.remove('border-yellow-500');
                                
                                saveCategoryBarcodeBtn.disabled = true;
                                saveCategoryBarcodeBtn.classList.add('opacity-50', 'cursor-not-allowed');
                                
                            } else {
                                // Yellow warning for similar match - KEEP BUTTON ENABLED
                                errorDiv.className = 'duplicate-error text-yellow-600 text-xs mt-1';
                                errorDiv.innerHTML = `Similar category exists: "<strong>${response.existingName}</strong>"<br>
                                                    <span class="text-gray-600">You can proceed, but consider using the existing category</span>`;
                                this.classList.add('border-yellow-500');
                                this.classList.remove('border-red-500');
                                
                                saveCategoryBarcodeBtn.disabled = false;
                                saveCategoryBarcodeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                            }
                            
                            this.parentNode.appendChild(errorDiv);
                        } else {
                            this.classList.remove('border-red-500', 'border-yellow-500');
                            saveCategoryBarcodeBtn.disabled = false;
                            saveCategoryBarcodeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    }, 500);
                });
            }
        });


        // Proceed to Barcode Generation Modal
        function proceedToBarcodeGeneration() {
            closeAllModals();
            document.getElementById('generateBarcodeModal').classList.remove('hidden');
            document.getElementById('generateBarcodeModal').classList.add('flex');

            // Update selected category display
            document.getElementById('selectedCategoryDisplay').textContent = selectedCategoryData.name;

            // Generate and display barcode immediately
            generateAndDisplayBarcode();
        }

        // Generate Barcode Prefix
        function generateBarcodePrefix(categoryName) {
            const cleanName = categoryName.replace(/[^a-zA-Z0-9]/g, '');
            let prefix = cleanName.substring(0, 5).toUpperCase();
            return prefix.length < 2 ? prefix.padEnd(2, 'X') : prefix;
        }

        // Generate Random Barcode
        function generateRandomBarcode() {
            const prefix = generateBarcodePrefix(selectedCategoryData.name);
            const randomNum = Math.floor(100000 + Math.random() * 900000); // 6 digits
            return `${prefix}${randomNum}`;
        }

        // Generate and Display Barcode
        function generateAndDisplayBarcode() {
            const newCode = generateRandomBarcode();
            currentBarcode = newCode;

            // Render barcode with compact sizing
            JsBarcode("#generatedBarcode", newCode, {
                format: "CODE128",
                lineColor: "#000",
                width: 1.5,
                height: 50,
                displayValue: false,
                fontSize: 12,
                margin: 3
            });

            // Update barcode text input
            document.getElementById("generatedBarcodeInput").value = newCode;
        }


        // Event Listeners
        document.addEventListener("DOMContentLoaded", () => {
            // Generate New Barcode
            document.getElementById("generateNewBarcodeBtn").addEventListener("click", () => {
                generateAndDisplayBarcode();
            });

            // Use Barcode Button
            document.getElementById("useBarcodeBtn").addEventListener("click", () => {
                if (!currentBarcode) {
                    alert("No barcode generated yet.");
                    return;
                }
                proceedToRegistration();
            });

            // Go Back Button
            document.getElementById("goBackBtn").addEventListener("click", () => {
                closeGenerateBarcodeModal();
                document.getElementById('chooseCategoryBarcodeModal').classList.remove('hidden');
                document.getElementById('chooseCategoryBarcodeModal').classList.add('flex');
            });

            // Enter key in custom category input
            document.getElementById('newCategoryName')?.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    confirmCustomCategory();
                }
            });
        });

        // ✅ MODIFIED: Proceed to Registration with Category Pre-fill
        function proceedToRegistration() {
            closeAllModals();
            openRegisterModal(currentBarcode, selectedCategoryData);
        }

        // Close Modal Functions
        function closeChooseCategoryBarcodeModal() {
            document.getElementById('chooseCategoryBarcodeModal').classList.add('hidden');
            document.getElementById('chooseCategoryBarcodeModal').classList.remove('flex');
            
            // Reset selected category data
            selectedCategoryData = {
                id: null,
                name: '',
                isNew: false
            };
            
            // Remove modal-open class from body if it was added
            document.body.classList.remove('modal-open');
        }

        // Close Custom Category Modal
        window.closeCustomCategoryBarcodeModal = function() {
            const modal = document.getElementById('customCategoryBarcodeModal');
            const input = document.getElementById('newCategoryNameBarcode');
            const saveCategoryBarcodeBtn = document.getElementById('saveCategoryBarcodeBtn');
            
            // Hide modal
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            
            // Clear input and errors
            if (input) {
                input.value = '';
                input.classList.remove('border-red-500', 'border-yellow-500');
                
                const existingError = input.parentNode.querySelector('.duplicate-error');
                if (existingError) {
                    existingError.remove();
                }
            }
            
            // Re-enable button
            if (saveCategoryBarcodeBtn) {
                saveCategoryBarcodeBtn.disabled = false;
                saveCategoryBarcodeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                saveCategoryBarcodeBtn.textContent = 'Save Category';
            }
            
            // Return to category selection
            document.getElementById('chooseCategoryBarcodeModal').classList.remove('hidden');
            document.getElementById('chooseCategoryBarcodeModal').classList.add('flex');
        };

        function closeGenerateBarcodeModal() {
            document.getElementById('generateBarcodeModal').classList.add('hidden');
        }

        // Close button that exits to inventory home
        window.closeCustomCategoryBarcodeModalCompletely = function() {
            const modal = document.getElementById('customCategoryBarcodeModal');
            const input = document.getElementById('newCategoryNameBarcode');
            const saveCategoryBarcodeBtn = document.getElementById('saveCategoryBarcodeBtn');
            
            // Hide modal
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            
            // Clear input and errors
            if (input) {
                input.value = '';
                input.classList.remove('border-red-500', 'border-yellow-500');
                
                const existingError = input.parentNode.querySelector('.duplicate-error');
                if (existingError) {
                    existingError.remove();
                }
            }
            
            // Re-enable button
            if (saveCategoryBarcodeBtn) {
                saveCategoryBarcodeBtn.disabled = false;
                saveCategoryBarcodeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                saveCategoryBarcodeBtn.textContent = 'Save Category';
            }
            
            // Reset selected category data
            selectedCategoryData = {
                id: null,
                name: '',
                isNew: false
            };
            
            //  CLOSE ALL MODALS - Exit to inventory home
            closeAllModals();
            
            console.log('✅ All modals closed - returned to inventory home');
        };

</script>

<script>
    // === Barcode Generation Category Functions ===

    // Local check function (fallback if global not available)
    async function checkExistingNameLocal(type, value) {
        if (!value.trim()) return null;
        
        try {
            const response = await fetch('/check-existing-name', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    type: type,
                    name: value
                })
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error checking existing name:', error);
            return null;
        }
    }

    // Setup form for barcode generation
    function setupBarcodeCategoryForm() {
        const form = document.getElementById('addCategoryForm');
        
        if (!form) return;

        // Remove old submit listener and add new one
        form.removeEventListener('submit', handleBarcodeCategorySubmit);
        form.addEventListener('submit', handleBarcodeCategorySubmit);
    }

    // ✅ Handle category form submission for barcode generation
    async function handleBarcodeCategorySubmit(e) {
        e.preventDefault();

        const categoryName = document.getElementById('newCategoryName').value.trim();
        const saveCategoryBtn = document.getElementById('saveCategoryBtn');

        if (!categoryName) {
            alert('Category name cannot be empty.');
            return;
        }

        // ✅ Client-side validation before submission
        const checkFunction = window.checkExistingName || checkExistingNameLocal;
        const response = await checkFunction('category', categoryName);
        
        // ✅ EXACT MATCH: Block submission (Red Error)
        if (response && response.exists && response.isExactMatch) {
            alert(`Cannot submit: Category "${categoryName}" already exists as "${response.existingName}"`);
            saveCategoryBtn.disabled = true;
            saveCategoryBtn.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }
        
        // ✅ SIMILAR MATCH: Show confirmation dialog (Yellow Warning)
        let userConfirmed = false;
        if (response && response.exists && !response.isExactMatch) {
            const proceed = confirm(
                `Similar category found: "${response.existingName}"\n\n` +
                `You're adding: "${categoryName}"\n\n` +
                `These appear similar but are not identical.\n` +
                `Proceed with adding this new category?`
            );
            
            if (!proceed) {
                return; // User chose not to proceed
            }
            userConfirmed = true;
        }

        // Disable button during submission
        saveCategoryBtn.disabled = true;
        saveCategoryBtn.textContent = 'Saving...';

        // ✅ Prepare form data
        const formData = new FormData();
        formData.append('category', categoryName);
        
        // ✅ Add confirmation flag if user approved similar match
        if (userConfirmed) {
            formData.append('confirmed_similar', '1');
        }

        try {
            // Send AJAX request to add category
            const submitResponse = await fetch('/inventory-staff/add-category', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: formData
            });

            const data = await submitResponse.json();

            if (data.success) {
                // Category added successfully
                alert('Category added successfully!');
                closeAddCategoryModalForBarcode();

                // ✅ MODIFIED: Store the new category ID if returned from backend
                selectedCategoryData = {
                    id: data.category_id || 'new', // Use returned ID if available
                    name: categoryName,
                    isNew: true
                };
                proceedToBarcodeGeneration();

            } else {
                // Backend returned an error
                saveCategoryBtn.disabled = false;
                saveCategoryBtn.textContent = 'Save Category';
                
                if (data.isExactMatch) {
                    alert('❌ ' + data.message);
                } else {
                    // Similar match from backend - ask for confirmation
                    const proceed = confirm(
                        `${data.message}\n\n` +
                        `Proceed anyway?`
                    );
                    
                    if (proceed) {
                        // Retry with confirmation flag
                        formData.append('confirmed_similar', '1');
                        const retryResponse = await fetch('/inventory-staff/add-category', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                            },
                            body: formData
                        });
                        
                        const retryData = await retryResponse.json();
                        if (retryData.success) {
                            alert('Category added successfully!');
                            closeAddCategoryModalForBarcode();
                            
                            selectedCategoryData = {
                                id: retryData.category_id || 'new',
                                name: categoryName,
                                isNew: true
                            };
                            proceedToBarcodeGeneration();
                        } else {
                            alert('⚠️ ' + retryData.message);
                        }
                    }
                }
            }
        } catch (error) {
            console.error('Error adding category:', error);
            alert('Error adding category. Please try again.');
            
            // Re-enable button on error
            saveCategoryBtn.disabled = false;
            saveCategoryBtn.textContent = 'Save Category';
        }
    }

    // Update the category selection handler
    window.selectCategoryForBarcode = function(categoryId, categoryName) {
        console.log('selectCategoryForBarcode called:', categoryId, categoryName);
        
        if (categoryId === 'new') {
            // Close choose category modal
            const chooseModal = document.getElementById('chooseCategoryBarcodeModal');
            if (chooseModal) {
                chooseModal.classList.add('hidden');
                chooseModal.classList.remove('flex');
            }
            
            // Open custom category modal
            const customModal = document.getElementById('customCategoryBarcodeModal');
            if (customModal) {
                customModal.classList.remove('hidden');
                customModal.classList.add('flex');
                
                // Clear and focus input - UPDATED ID
                const input = document.getElementById('newCategoryNameBarcode');
                if (input) {
                    input.value = '';
                    input.classList.remove('border-red-500', 'border-yellow-500');
                    
                    // Clear any existing errors
                    const existingError = input.parentNode.querySelector('.duplicate-error');
                    if (existingError) {
                        existingError.remove();
                    }
                    
                    setTimeout(() => input.focus(), 100);
                }
            }
        } else {
            // Proceed with existing category
            selectedCategoryData = {
                id: categoryId,
                name: categoryName,
                isNew: false
            };
            proceedToBarcodeGeneration();
        }
    };

    // // Close Choose Category Modal for Barcode
    // function closeChooseCategoryBarcodeModal() {
    //     document.getElementById('chooseCategoryBarcodeModal').classList.add('hidden');
    // }
</script>


    <!-- Register New Product Modal JavaScript -->
    <script>
        // ✅ CORRECTED: Replace the openRegisterModal function around line 3375 with this version
       function openRegisterModal(barcode = '', categoryData = null) {
        console.log('Opening register modal with:', { barcode, categoryData }); // Debug log
        
        closeAllModals();
        const modal = document.getElementById('registerProductModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        // Auto-fill barcode in the register modal
        const barcodeElement = document.getElementById('autoFilledBarcode');
        if (barcodeElement && barcode) {
            barcodeElement.textContent = barcode;
        }

        // ✅ Pre-fill category if provided
        if (categoryData && categoryData.id && categoryData.name) {
            const categorySelect = document.getElementById('categorySelect');
            const customCategoryInput = document.getElementById('customCategory');
            
            console.log('Category select element:', categorySelect); // Debug log
            console.log('Available options:', Array.from(categorySelect?.options || []).map(o => ({ value: o.value, text: o.text }))); // Debug log
            
            if (categorySelect) {
                // ✅ FIX: Use setTimeout to ensure dropdown is fully rendered
                setTimeout(() => {
                    // Convert categoryData.id to string for comparison (dropdown values are strings)
                    const categoryId = String(categoryData.id);
                    
                    // Check if the category exists in the dropdown
                    const optionExists = Array.from(categorySelect.options).some(opt => 
                        String(opt.value) === categoryId
                    );
                    
                    console.log('Category exists in dropdown:', optionExists, 'Looking for ID:', categoryId); // Debug log
                    
                    if (optionExists) {
                        // ✅ Category exists in dropdown - select it directly
                        categorySelect.value = categoryId;
                        
                        // ✅ Trigger change event to ensure any dependent logic runs
                        categorySelect.dispatchEvent(new Event('change', { bubbles: true }));
                        
                        // Make sure custom input is hidden
                        if (customCategoryInput) {
                            customCategoryInput.classList.add('hidden');
                            customCategoryInput.value = '';
                        }
                        
                        console.log('Category pre-filled successfully:', categorySelect.value); // Debug log
                    } else {
                        // ✅ FALLBACK: Category doesn't exist in dropdown (shouldn't happen but kept for safety)
                        console.warn('Category not found in dropdown, using custom input fallback'); // Debug log
                        
                        categorySelect.value = 'other';
                        categorySelect.dispatchEvent(new Event('change', { bubbles: true }));
                        
                        if (customCategoryInput) {
                            setTimeout(() => {
                                customCategoryInput.value = categoryData.name;
                                customCategoryInput.classList.remove('hidden');
                                console.log('Using custom category input:', categoryData.name); // Debug log
                            }, 100);
                        }
                    }
                }, 150); // Give the DOM time to fully render the modal and dropdown
            }
        }
    }

        function closeRegisterModal() {
            const modal = document.getElementById('registerProductModal');
            const form = document.getElementById('registerProductForm');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex'); 
            }
            if (form) form.reset();
            resetPhotoPreview();

        // Clear the barcode display
        const barcodeElement = document.getElementById('autoFilledBarcode');
        if (barcodeElement) barcodeElement.textContent = '';
        
        // Remove all error messages
        const errorMessages = document.querySelectorAll('.duplicate-error');
        errorMessages.forEach(error => error.remove());
        
        // Remove red borders from custom inputs and product name
        const productNameInput = document.querySelector('input[name="name"]');
        const customCategory = document.getElementById('customCategory');
        const customUnit = document.getElementById('customUnit');
        if (productNameInput) {
            productNameInput.classList.remove('border-red-500', 'border-yellow-500'); 
        }
        if (customCategory) {
            customCategory.classList.remove('border-red-500', 'border-yellow-500'); 
            customCategory.value = '';
        }
        if (customUnit) {
            customUnit.classList.remove('border-red-500', 'border-yellow-500'); 
            customUnit.value = '';
        }
        
        // Reset dropdowns to default
        const categorySelect = document.getElementById('categorySelect');
        const unitSelect = document.getElementById('unitSelect');
        if (categorySelect) categorySelect.value = '';
        if (unitSelect) unitSelect.value = '';
    }

        // Auto-calc Selling Price
        function calculateSellingPrice() {
            const cost = parseFloat(document.getElementById("costPrice").value) || 0;
            const type = document.getElementById("markupType").value;
            const markup = parseFloat(document.getElementById("markupValue").value) || 0;
            let selling = cost;

            if (type === "percentage") {
                selling = cost + (cost * (markup / 100));
            } else {
                selling = cost + markup;
            }

            document.getElementById("sellingPrice").value = selling.toFixed(2);
        }

        // Reset photo preview helper
        function resetPhotoPreview() {
            const previewImage = document.getElementById("previewImage");
            const uploadIcon = document.getElementById("uploadIcon");
            const fileName = document.getElementById("fileName");

            if (previewImage) {
                previewImage.classList.add("hidden");
            }
            if (uploadIcon) {
                uploadIcon.style.display = "block";
            }
            if (fileName) {
                fileName.textContent = "Upload Photo";
            }

            const photoLabel = document.querySelector("label[for='productPhoto']");
            if (photoLabel) {
                photoLabel.style.backgroundImage = "none";
            }
        }

    // Initialize event listeners (WITHOUT form submission - handled in document 2)
    document.addEventListener("DOMContentLoaded", () => {
        const photoInput = document.getElementById("productPhoto");
        const photoLabel = document.querySelector("label[for='productPhoto']");
        const uploadIcon = document.getElementById("uploadIcon");
        const previewImage = document.getElementById("previewImage");
        const fileName = document.getElementById("fileName");

            // Initialize event listeners for pricing calculation
            ["costPrice", "markupType", "markupValue"].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener("input", calculateSellingPrice);
                    element.addEventListener("change", calculateSellingPrice);
                }
            });

        // Photo preview
        if (photoInput) {
            photoInput.addEventListener("change", function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Show image preview
                        if (previewImage) {
                            previewImage.src = e.target.result;
                            previewImage.classList.remove("hidden");
                        }

                            // Change the label's background to the selected image
                            if (photoLabel) {
                                photoLabel.style.backgroundImage = `url(${e.target.result})`;
                                photoLabel.style.backgroundSize = "cover";
                                photoLabel.style.backgroundPosition = "center";
                            }

                            if (uploadIcon) {
                                uploadIcon.style.display = "none";
                            }

                        if (fileName) {
                            fileName.textContent = photoInput.files[0].name;
                        }
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
</script>

    <!-- JS for Custom Category/Unit Toggle -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const categorySelect = document.getElementById("categorySelect");
            const customCategory = document.getElementById("customCategory");
            const unitSelect = document.getElementById("unitSelect");
            const customUnit = document.getElementById("customUnit");

            categorySelect.addEventListener("change", () => {
                if (categorySelect.value === "other") {
                    customCategory.classList.remove("hidden");
                    customCategory.required = true;
                } else {
                    customCategory.classList.add("hidden");
                    customCategory.required = false;
                }
            });

            unitSelect.addEventListener("change", () => {
                if (unitSelect.value === "other") {
                    customUnit.classList.remove("hidden");
                    customUnit.required = true;
                } else {
                    customUnit.classList.add("hidden");
                    customUnit.required = false;
                }
            });
        });
    </script>

<!-- Function to check for existing categories/units and product name in register product -->
<script>
    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        // Simply set the message text without icon
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Make this function globally accessible for reuse
    window.checkExistingName = async function(type, value) {
        if (!value.trim()) return null;
        
        try {
            const response = await fetch('/check-existing-name', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    type: type,
                    name: value
                })
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error checking existing name:', error);
            return null;
        }
    };

    // Add real-time validation for custom category input
    document.addEventListener("DOMContentLoaded", () => {
        const categorySelect = document.getElementById('categorySelect');
        const unitSelect = document.getElementById('unitSelect');
        
        // Clear category errors when changing dropdown selection
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                const customCategory = document.getElementById('customCategory');
                if (this.value !== 'other' && customCategory) {
                    const existingError = customCategory.parentNode.querySelector('.duplicate-error');
                    if (existingError) {
                        existingError.remove();
                    }
                    customCategory.classList.remove('border-red-500');
                    customCategory.value = '';
                }
            });
        }
        
        // Clear unit errors when changing dropdown selection
        if (unitSelect) {
            unitSelect.addEventListener('change', function() {
                const customUnit = document.getElementById('customUnit');
                if (this.value !== 'other' && customUnit) {
                    const existingError = customUnit.parentNode.querySelector('.duplicate-error');
                    if (existingError) {
                        existingError.remove();
                    }
                    customUnit.classList.remove('border-red-500');
                    customUnit.value = '';
                }
            });
        }
        
        // Real-time validation for product name
        const productNameInput = document.querySelector('input[name="name"]');
        if (productNameInput) {
            let productNameTimeout;
            
            productNameInput.addEventListener('input', function() {
                clearTimeout(productNameTimeout);
                
                const existingError = this.parentNode.querySelector('.duplicate-error');
                if (existingError) {
                    existingError.remove();
                }
                
                if (!this.value.trim()) {
                    this.classList.remove('border-red-500', 'border-yellow-500');
                    return;
                }
                
                productNameTimeout = setTimeout(async () => {
                    const response = await checkExistingName('product', this.value);
                    
                    if (response && response.exists) {
                        const errorDiv = document.createElement('div');
                        
                        if (response.isExactMatch) {
                            errorDiv.className = 'duplicate-error text-red-600 text-xs mt-1 font-semibold';
                            errorDiv.innerHTML = `Product already exists: <strong>"${response.existingName}"</strong>`;
                            this.classList.add('border-red-500');
                            this.classList.remove('border-yellow-500');
                        } else {
                            errorDiv.className = 'duplicate-error text-yellow-600 text-xs mt-1';
                            errorDiv.innerHTML = `Similar product exists: "<strong>${response.existingName}</strong>"<br>
                                                <span class="text-gray-600 text-xs">Did you mean this product?</span>`;
                            this.classList.add('border-yellow-500');
                            this.classList.remove('border-red-500');
                        }
                        
                        this.parentNode.appendChild(errorDiv);
                    } else {
                        this.classList.remove('border-red-500', 'border-yellow-500');
                    }
                }, 500);
            });
        }
        
        // Real-time validation for custom category
        const customCategory = document.getElementById('customCategory');
        if (customCategory) {
            let categoryTimeout;
            
            customCategory.addEventListener('input', function() {
                clearTimeout(categoryTimeout);
                
                const existingError = this.parentNode.querySelector('.duplicate-error');
                if (existingError) {
                    existingError.remove();
                }
                
                if (!this.value.trim()) {
                    this.classList.remove('border-red-500', 'border-yellow-500');
                    return;
                }
                
                categoryTimeout = setTimeout(async () => {
                    const response = await checkExistingName('category', this.value);
                    
                    if (response && response.exists) {
                        const errorDiv = document.createElement('div');
                        
                        if (response.isExactMatch) {
                            errorDiv.className = 'duplicate-error text-red-600 text-xs mt-1 font-semibold';
                            errorDiv.innerHTML = `Category already exists: <strong>"${response.existingName}"</strong>`;
                            this.classList.add('border-red-500');
                            this.classList.remove('border-yellow-500');
                        } else {
                            errorDiv.className = 'duplicate-error text-yellow-600 text-xs mt-1';
                            errorDiv.innerHTML = `Similar category exists: "<strong>${response.existingName}</strong>"<br>
                                                <span class="text-gray-600 text-xs">Proceed with caution</span>`;
                            this.classList.add('border-yellow-500');
                            this.classList.remove('border-red-500');
                        }
                        
                        this.parentNode.appendChild(errorDiv);
                    } else {
                        this.classList.remove('border-red-500', 'border-yellow-500');
                    }
                }, 500);
            });
        }

        // Real-time validation for custom unit
        const customUnit = document.getElementById('customUnit');
        if (customUnit) {
            let unitTimeout;
            
            customUnit.addEventListener('input', function() {  
                clearTimeout(unitTimeout);
                
                const existingError = this.parentNode.querySelector('.duplicate-error');
                if (existingError) {
                    existingError.remove();
                }
                
                if (!this.value.trim()) {
                    this.classList.remove('border-red-500', 'border-yellow-500');
                    return;
                }
                
                unitTimeout = setTimeout(async () => {
                    const response = await checkExistingName('unit', this.value);
                    
                    if (response && response.exists) {
                        const errorDiv = document.createElement('div');
                        
                        if (response.isExactMatch) {
                            errorDiv.className = 'duplicate-error text-red-600 text-xs mt-1 font-semibold';
                            errorDiv.innerHTML = `Unit already exists: <strong>"${response.existingName}"</strong>`;
                            this.classList.add('border-red-500');
                            this.classList.remove('border-yellow-500');
                        } else {
                            errorDiv.className = 'duplicate-error text-yellow-600 text-xs mt-1';
                            errorDiv.innerHTML = `Similar unit exists: "<strong>${response.existingName}</strong>"<br>
                                                <span class="text-gray-600 text-xs">Proceed with caution</span>`;
                            this.classList.add('border-yellow-500');
                            this.classList.remove('border-red-500');
                        }
                        
                        this.parentNode.appendChild(errorDiv);
                    } else {
                        this.classList.remove('border-red-500', 'border-yellow-500');
                    }
                }, 500);
            });
        }
    
        // Update form submission to include client-side validation with toast notifications
        const form = document.getElementById('registerProductForm');
        const photoInput = document.getElementById('productPhoto');
        
        if (form) {
            form.addEventListener("submit", async function(e) {
                e.preventDefault();

                // Product name validation - only block EXACT matches
                const productNameInput = document.querySelector('input[name="name"]');
                let userConfirmedSimilar = false;
                
                if (productNameInput && productNameInput.value.trim()) {
                    const response = await checkExistingName('product', productNameInput.value);
                    
                    if (response && response.exists && response.isExactMatch) {
                        showToast(`Cannot submit: Product "${productNameInput.value}" already exists as "${response.existingName}"`, 'error');
                        return;
                    }
                    
                    if (response && response.exists && !response.isExactMatch) {
                        const proceed = confirm(
                            `Similar product found: "${response.existingName}"\n\n` +
                            `You're adding: "${productNameInput.value}"\n\n` +
                            `These appear similar but are not identical. Proceed with registration?`
                        );
                        
                        if (!proceed) {
                            return;
                        }
                        userConfirmedSimilar = true;
                    }
                }

                // Category validation - only block EXACT matches, confirm SIMILAR
                const categorySelect = document.getElementById('categorySelect');
                const customCategory = document.getElementById('customCategory');
                let userConfirmedCategory = false;
                
                if (categorySelect && categorySelect.value === 'other' && customCategory && customCategory.value.trim()) {
                    const response = await checkExistingName('category', customCategory.value);
                    
                    if (response && response.exists && response.isExactMatch) {
                        showToast(`Cannot submit: Category "${customCategory.value}" already exists as "${response.existingName}"`, 'error');
                        return;
                    }
                    
                    if (response && response.exists && !response.isExactMatch) {
                        const proceed = confirm(
                            `Similar category found: "${response.existingName}"\n\n` +
                            `You're adding: "${customCategory.value}"\n\n` +
                            `These appear similar. Proceed anyway?`
                        );
                        
                        if (!proceed) {
                            return;
                        }
                        userConfirmedCategory = true;
                    }
                }

                // Unit validation - only block EXACT matches
                const unitSelect = document.getElementById('unitSelect');
                const customUnit = document.getElementById('customUnit');
                let userConfirmedUnit = false;
                
                if (unitSelect && unitSelect.value === 'other' && customUnit && customUnit.value.trim()) {
                    const response = await checkExistingName('unit', customUnit.value);
                    
                    if (response && response.exists && response.isExactMatch) {
                        showToast(`Cannot submit: Unit "${customUnit.value}" already exists as "${response.existingName}"`, 'error');
                        return;
                    }
                    
                    if (response && response.exists && !response.isExactMatch) {
                        const proceed = confirm(
                            `Similar unit found: "${response.existingName}"\n\n` +
                            `You're adding: "${customUnit.value}"\n\n` +
                            `These appear similar. Proceed anyway?`
                        );
                        
                        if (!proceed) {
                            return;
                        }
                        userConfirmedUnit = true;
                    }
                }

                // Proceed with form submission
                const formData = new FormData(form);
                const barcodeElement = document.getElementById("autoFilledBarcode");

                if (barcodeElement && barcodeElement.textContent) {
                    formData.append("barcode", barcodeElement.textContent);
                } else {
                    showToast("Barcode is required.", 'error');
                    return;
                }

                if (photoInput && photoInput.files[0]) {
                    formData.append("photo", photoInput.files[0]);
                }

                // Send confirmation flags to backend
                if (userConfirmedSimilar) {
                    formData.append("confirmed_similar", "1");
                }
                if (userConfirmedCategory) {
                    formData.append("confirmed_category", "1");
                }
                if (userConfirmedUnit) {
                    formData.append("confirmed_unit", "1");
                }

                fetch("/inventory-staff/register-product", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                throw new Error(data.message || 'Server error');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast("Product added successfully!", 'success');
                            closeRegisterModal();
                            // Reload page after showing toast
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            showToast("Failed Submission: " + (data.message || 'Unknown error'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error("Error registering product:", error);
                        showToast("Something went wrong: " + error.message, 'error');
                    });
            });
        }
    });
</script>


    <!-- Toggle Option for Active and Archived Products JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.getElementById("statusToggleForm");
            const statusInput = document.getElementById("statusInput");
            const activeBtn = document.getElementById("activeBtn");
            const archivedBtn = document.getElementById("archivedBtn");

            activeBtn.addEventListener("click", () => {
                statusInput.value = "active";
                form.submit();
            });

            archivedBtn.addEventListener("click", () => {
                statusInput.value = "archived";
                form.submit();
            });
        });
    </script>

<!-- for new register product with integrated add stock  -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const EXPIRATION_DAYS_LIMIT = 7;
    let batchCounter = 0;

    // Get minimum expiration date (7 days from today)
    function getMinimumExpirationDate() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const minDate = new Date(today);
        minDate.setDate(today.getDate() + EXPIRATION_DAYS_LIMIT + 1);
        return minDate.toISOString().split('T')[0];
    }

    // Generate batch number (will be converted to P{prodCode}-BATCH-{number} on backend)
    function generateBatchNumber() {
        batchCounter++;
        return `BATCH-${batchCounter}`;
    }
    
    // Note: The actual batch number stored in database will be P{prodCode}-BATCH-{number}
    // This is just a temporary display number for the user during registration

    // Add batch row
    function addBatchRow() {
        const container = document.getElementById('batchRowsContainer');
        const emptyMessage = document.getElementById('emptyBatchMessage');
        const template = document.getElementById('batchRowTemplate');
        
        // Hide empty message
        if (emptyMessage) {
            emptyMessage.style.display = 'none';
        }

        // Clone template
        const clone = template.content.cloneNode(true);
        const batchRow = clone.querySelector('.batch-row');
        
        // Increment counter
        batchCounter++;
        
        // Set batch label (for display only - backend will generate actual batch number)
        const batchLabel = clone.querySelector('.batch-label');
        batchLabel.textContent = `Batch #${batchCounter}`;
        
        // Update indices in input names
        const inputs = clone.querySelectorAll('input[name*="INDEX"]');
        inputs.forEach(input => {
            input.name = input.name.replace('INDEX', batchCounter - 1);
        });

        // Set min date for expiration
        const expirationInput = clone.querySelector('.expiration-date-input');
        expirationInput.min = getMinimumExpirationDate();
        
        // Add expiration validation
        expirationInput.addEventListener('change', function() {
            validateExpirationDate(this);
        });

        // Remove batch handler
        const removeBtn = clone.querySelector('.remove-batch-btn');
        removeBtn.addEventListener('click', function() {
            batchRow.remove();
            
            // Renumber remaining batches
            const remainingBatches = container.querySelectorAll('.batch-row');
            remainingBatches.forEach((row, index) => {
                const label = row.querySelector('.batch-label');
                if (label) {
                    label.textContent = `Batch #${index + 1}`;
                }
            });
            
            // Update batch counter
            batchCounter = remainingBatches.length;
            
            // Show empty message if no batches left
            if (remainingBatches.length === 0 && emptyMessage) {
                emptyMessage.style.display = 'block';
            }
        });

        container.appendChild(batchRow);
    }

    // Validate expiration date
    function validateExpirationDate(input) {
        if (!input.value) return true;
        
        const selectedDate = new Date(input.value);
        selectedDate.setHours(0, 0, 0, 0);
        
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const minDate = new Date(today);
        minDate.setDate(today.getDate() + EXPIRATION_DAYS_LIMIT);
        
        const timeDiff = selectedDate - today;
        const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
        
        // Remove existing error
        const existingError = input.parentNode.querySelector('.expiration-error');
        if (existingError) {
            existingError.remove();
        }
        
        if (selectedDate < minDate) {
            input.classList.add('border-red-500');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'expiration-error text-red-500 text-xs mt-1';
            
            if (daysDiff < 0) {
                errorDiv.textContent = 'Date cannot be in the past';
            } else if (daysDiff === 0) {
                errorDiv.textContent = `Date must be at least ${EXPIRATION_DAYS_LIMIT} days from today (selected: today)`;
            } else {
                errorDiv.textContent = `Date must be at least ${EXPIRATION_DAYS_LIMIT} days from today (selected: ${daysDiff} day${daysDiff !== 1 ? 's' : ''})`;
            }
            
            input.parentNode.appendChild(errorDiv);
            return false;
        } else {
            input.classList.remove('border-red-500');
            return true;
        }
    }

    // Add batch button
    document.getElementById('addBatchBtn').addEventListener('click', addBatchRow);

    // Photo preview (existing code)
    const photoInput = document.getElementById("productPhoto");
    const previewImage = document.getElementById("previewImage");
    const uploadIcon = document.getElementById("uploadIcon");
    const fileName = document.getElementById("fileName");

    if (photoInput) {
        photoInput.addEventListener("change", function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewImage.classList.remove("hidden");
                    uploadIcon.style.display = "none";
                    fileName.textContent = photoInput.files[0].name;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Pricing calculation (existing code)
    ["costPrice", "markupType", "markupValue", "vatCategory"].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener("input", calculateSellingPrice);
            element.addEventListener("change", calculateSellingPrice);
        }
    });

    // Category/Unit toggle (existing code)
    const categorySelect = document.getElementById("categorySelect");
    const customCategory = document.getElementById("customCategory");
    const unitSelect = document.getElementById("unitSelect");
    const customUnit = document.getElementById("customUnit");

    if (categorySelect) {
        categorySelect.addEventListener("change", () => {
            if (categorySelect.value === "other") {
                customCategory.classList.remove("hidden");
                customCategory.required = true;
                categorySelect.classList.remove('text-gray-400');
                categorySelect.classList.add('text-gray-900');
            } else {
                customCategory.classList.add("hidden");
                customCategory.required = false;
            }
        });
    }

    if (unitSelect) {
        unitSelect.addEventListener("change", () => {
            if (unitSelect.value === "other") {
                customUnit.classList.remove("hidden");
                customUnit.required = true;
                unitSelect.classList.remove('text-gray-400');
                unitSelect.classList.add('text-gray-900');
            } else {
                customUnit.classList.add("hidden");
                customUnit.required = false;
                // Change text color based on selection
                if (unitSelect.value === "") {
                    unitSelect.classList.add('text-gray-400');
                    unitSelect.classList.remove('text-gray-900');
                } else {
                    unitSelect.classList.remove('text-gray-400');
                    unitSelect.classList.add('text-gray-900');
                }
            }
        });
    }
});

// Calculate selling price with VAT (existing function - enhanced)
function calculateSellingPrice() {
    const cost = parseFloat(document.getElementById("costPrice").value) || 0;
    const type = document.getElementById("markupType").value;
    const markup = parseFloat(document.getElementById("markupValue").value) || 0;
    const vatCategory = document.getElementById("vatCategory").value;
    
    let markupAmount = 0;
    let basePrice = cost;

    // Apply markup to cost
    if (type === "percentage") {
        markupAmount = cost * (markup / 100);
        basePrice = cost + markupAmount;
    } else {
        markupAmount = markup;
        basePrice = cost + markupAmount;
    }

    // Calculate VAT based on category
    let taxAmount = 0;
    let taxLabel = '';
    let sellingPrice = basePrice;

    if (vatCategory === 'vat_exempt') {
        taxAmount = 0;
        taxLabel = 'VAT (0%)';
        sellingPrice = basePrice;
    } else {
        // vat_inclusive - default 12% VAT
        taxAmount = basePrice * 0.12;
        taxLabel = 'VAT (12%)';
        sellingPrice = basePrice + taxAmount;
    }

    // Update markup label based on type
    const markupLabel = type === "percentage" 
        ? `Markup (${markup}%):` 
        : `Markup (₱${markup}):`;

    // Update all display fields
    document.getElementById("sellingPrice").value = sellingPrice.toFixed(2);
    document.getElementById("costDisplay").textContent = '₱' + cost.toFixed(2);
    document.getElementById("markupLabel").textContent = markupLabel;
    document.getElementById("markupAmount").textContent = '₱' + markupAmount.toFixed(2);
    document.getElementById("basePrice").textContent = '₱' + basePrice.toFixed(2);
    document.getElementById("taxLabel").textContent = taxLabel + ':';
    document.getElementById("taxAmount").textContent = '₱' + taxAmount.toFixed(2);
    document.getElementById("totalPrice").textContent = '₱' + sellingPrice.toFixed(2);
    
    // Change tax amount color based on category
    const taxAmountEl = document.getElementById("taxAmount");
    if (vatCategory === 'vat_exempt') {
        taxAmountEl.parentElement.classList.add('text-gray-500');
        taxAmountEl.parentElement.classList.remove('text-green-700');
    } else {
        taxAmountEl.parentElement.classList.add('text-green-700');
        taxAmountEl.parentElement.classList.remove('text-gray-500');
    }
}

// Toggle VAT info panel
function toggleVatInfo() {
    const panel = document.getElementById('vatInfoPanel');
    if (panel) {
        panel.classList.toggle('hidden');
    }
}

// Toggle bulk calculator
function toggleBulkCalculator() {
    const section = document.getElementById('bulkCalculatorSection');
    const toggleIcon = document.getElementById('bulkToggleIcon');
    
    if (section.classList.contains('hidden')) {
        section.classList.remove('hidden');
        toggleIcon.textContent = 'expand_less';
    } else {
        section.classList.add('hidden');
        toggleIcon.textContent = 'expand_more';
        // Clear bulk calculator fields
        document.getElementById('bulkQuantity').value = '';
        document.getElementById('bulkUnit').value = '';
        document.getElementById('bulkCostPrice').value = '';
        document.getElementById('calculatedUnitCost').textContent = '₱0.00';
    }
}

// Calculate unit cost from bulk purchase
function calculateBulkUnitCost() {
    const bulkQuantity = parseFloat(document.getElementById('bulkQuantity').value) || 0;
    const bulkCostPrice = parseFloat(document.getElementById('bulkCostPrice').value) || 0;
    const bulkUnit = document.getElementById('bulkUnit').value;
    
    if (bulkQuantity <= 0 || bulkCostPrice <= 0) {
        document.getElementById('calculatedUnitCost').textContent = '₱0.00';
        return 0;
    }
    
    let effectiveQuantity = bulkQuantity;
    
    // Handle special units with known quantities
    if (bulkUnit === 'dozen') {
        effectiveQuantity = 12; // Always 12 pieces per dozen
    }
    
    const unitCost = bulkCostPrice / effectiveQuantity;
    document.getElementById('calculatedUnitCost').textContent = '₱' + unitCost.toFixed(2);
    
    return unitCost;
}

// Apply bulk cost to main cost price field
function applyBulkCost() {
    const unitCost = calculateBulkUnitCost();
    
    if (unitCost > 0) {
        document.getElementById('costPrice').value = unitCost.toFixed(2);
        calculateSellingPrice();
        
        // Show success message
        const calculatedCostEl = document.getElementById('calculatedUnitCost');
        const originalText = calculatedCostEl.textContent;
        calculatedCostEl.textContent = '✓ Applied!';
        calculatedCostEl.classList.add('text-green-600');
        
        setTimeout(() => {
            calculatedCostEl.textContent = originalText;
        }, 2000);
    } else {
        alert('Please fill in all bulk purchase fields correctly.');
    }
}

// Add event listeners for bulk calculator
document.addEventListener("DOMContentLoaded", function() {
    const bulkQuantityInput = document.getElementById('bulkQuantity');
    const bulkCostPriceInput = document.getElementById('bulkCostPrice');
    const bulkUnitSelect = document.getElementById('bulkUnit');
    
    if (bulkQuantityInput && bulkCostPriceInput && bulkUnitSelect) {
        [bulkQuantityInput, bulkCostPriceInput, bulkUnitSelect].forEach(el => {
            el.addEventListener('input', calculateBulkUnitCost);
            el.addEventListener('change', calculateBulkUnitCost);
        });
    }
});
</script>



@endsection