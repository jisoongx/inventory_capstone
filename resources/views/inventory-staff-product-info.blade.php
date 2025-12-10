@extends('dashboards.staff.staff')
<head>
    <title>Product Information</title>
</head>
@section('content')

<div class="px-4">
    @livewire('expiration-container')
</div>

<div class="max-w-6xl mx-auto py-3"> 

<!-- Product Information Section (Modified) -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <!-- Back Button -->
    <div class="mb-6 mt-2">
        <a href="{{ route('inventory-staff') }}" 
        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
            <span class="material-symbols-outlined text-sm mr-1">assignment_return</span>
            Back
        </a>
    </div>
    
    <!-- First Row: Product Header with Image, Name, Badge, and Barcode -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        <!-- Left: Product Image -->
        <div class="lg:col-span-2 flex justify-center lg:justify-start">
            @if($product->prod_image)
                <img src="{{ $product->prod_image && file_exists(public_path('storage/' . $product->prod_image)) 
                                    ? asset('storage/' . $product->prod_image) 
                                    : asset('assets/no-product-image.png') }}" 
                     alt="{{ $product->name }}" 
                     class="w-42 h-42 object-cover rounded-lg shadow-md border">
            @else
                <div class="w-42 h-42 flex items-center justify-center bg-gray-200 text-gray-500 rounded-lg shadow-md border">
                    No Image
                </div>
            @endif
        </div>

        <!-- Middle: Product Name and Stock Badge -->
        <div class="lg:col-span-5 flex flex-col justify-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-3 leading-tight">
                {{ $product->name }}
            </h2>
            <!-- Stock Badge -->
            @if($currentStock <= 0)
                <span class="inline-flex items-center gap-1.5 w-fit px-3 py-1.5 text-sm bg-red-100 text-red-700 font-semibold rounded-full shadow-sm">
                    <span class="material-symbols-outlined text-base">cancel</span>
                    Out of Stock
                </span>
            @elseif($currentStock <= $product->stock_limit)
                <span class="inline-flex items-center gap-1.5 w-fit px-3 py-1.5 text-sm bg-yellow-100 text-yellow-700 font-semibold rounded-full shadow-sm">
                    <span class="material-symbols-outlined text-base">warning</span>
                    Low Stock
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 w-fit px-3 py-1.5 text-sm bg-green-100 text-green-700 font-semibold rounded-full shadow-sm">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    In Stock
                </span>
            @endif
        </div>

        
        <!-- Right: Barcode Card with Print Button -->
        <div class="lg:col-span-5">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-3 shadow-sm h-full flex flex-col justify-between">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm p-1 font-semibold text-gray-800">Barcode</h3>
                    </div>
                    <button onclick="printProductBarcode('{{ $product->barcode }}', '{{ $product->name }}')" 
                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 active:scale-95 transition-all duration-200 shadow-md hover:shadow-lg">
                        <span class="material-symbols-outlined text-sm">print</span>
                        Print
                    </button>
                </div>
                <div class="bg-white rounded-lg px-2 py-2 border border-blue-100 flex flex-col items-center gap-1">
                    <!-- Barcode Image -->
                    <svg id="product-barcode-display" class="max-w-full h-16"></svg>
                    
                    <!-- Barcode Number -->
                    <p class="font-mono text-xs font-bold text-gray-800 tracking-wide text-center">
                        {{ $product->barcode }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row: Stock Summary and Product Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Left Column: Stock Summary Cards -->
        <div class="space-y-6">
            <!-- Stock Summary Cards -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 mb-2">
                    <p class="text-xs text-blue-700 font-medium">Remaining Stock</p>
                    <p class="text-xl font-bold text-blue-900">{{ $currentStock }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg border border-green-200 mb-2">
                    <p class="text-xs text-green-700 font-medium">Total Stock</p>
                    <p class="text-xl font-bold text-green-900">{{ $totalStockIn }}</p>
                </div>
                <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
                    <p class="text-xs text-amber-700 font-medium">Total Items Sold</p>
                    <p class="text-xl font-bold text-amber-900">{{ $totalStockOutSold }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                    <p class="text-xs text-purple-700 font-medium">Total Revenue</p>
                    <p class="text-xl font-bold text-purple-900">₱{{ number_format($totalRevenue, 2) }}</p>
                </div>
            </div>

            <!-- Damaged & Expired Cards -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                    <p class="text-xs text-red-700 font-medium">Damaged Items</p>
                    <p class="text-xl font-bold text-red-900">{{ $totalDamaged }}</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                    <p class="text-xs text-red-700 font-medium">Expired Items</p>
                    <p class="text-xl font-bold text-red-900">{{ $totalExpired }}</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Product Details -->
        <div class="space-y-6">


            <!-- Product Details Grid -->
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-1">
                    <span class="material-symbols-outlined text-gray-600 text-lg">info</span>
                    Product Details
                </h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="space-y-1">
                        <p class="text-gray-500 text-xs font-medium">Unit</p>
                        <p class="font-semibold text-gray-800">{{ $product->unit }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-gray-500 text-xs font-medium">Minimum Stock Limit</p>
                        <p class="font-semibold text-gray-800">{{ $product->stock_limit }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-gray-500 text-xs font-medium">Cost Price</p>
                        <p class="font-semibold text-gray-800">₱{{ number_format($product->cost_price, 2) }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-gray-500 text-xs font-medium">Selling Price</p>
                        <div class="flex items-baseline gap-1">
                            <span class="font-bold text-lg text-green-600">₱{{ number_format($product->selling_price, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-600 text-lg">description</span>
                    Description
                </h3>
                <p class="text-sm text-gray-700 leading-relaxed">{{ $product->description ?? 'No description available' }}</p>
            </div>
        </div>
    </div>
</div>

    <!-- Stock Movement Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button id="stockInTab" class="tab-button py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600" onclick="switchTab('stockIn')">
                    Stock-In History
                </button>
                <button id="stockOutTab" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" onclick="switchTab('stockOut')">
                    Stock-Out History
                </button>
                <!-- <button id="comparisonTab" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" onclick="switchTab('comparison')">
                    Stock Comparison
                </button> -->
            </nav>
        </div>
    </div>

<!-- Stock-In History Section -->
<div id="stockInSection" class="tab-content">
    <div class="bg-green-50 rounded-lg p-3 mb-4 border border-green-200">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">
            <!-- Sort By -->
            <div class="lg:col-span-3">
                <label class="flex items-center gap-1 text-[11px] font-semibold text-gray-700 mb-1.5">
                    <span class="material-symbols-outlined text-sm text-green-600">sort</span>
                    Sort By
                </label>
                <select id="stockInSort" class="w-full text-xs border-2 border-gray-300 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500 bg-white transition-all hover:border-green-400">
                    <option value="" disabled selected>Choose sorting...</option>
                    <option value="date_desc">📅 Date Added (Newest First)</option>
                    <option value="date_asc">📅 Date Added (Oldest First)</option>
                    <option value="batch_desc">📦 Batch (Newest First)</option>
                    <option value="batch_asc">📦 Batch (Oldest First)</option>
                    <option value="quantity_desc">📊 Quantity (High to Low)</option>
                    <option value="quantity_asc">📊 Quantity (Low to High)</option>
                </select>
            </div>

            <!-- Search Batch -->
            <div class="lg:col-span-3">
                <label class="flex items-center gap-1 text-[11px] font-semibold text-gray-700 mb-1.5">
                    <span class="material-symbols-outlined text-sm text-green-600">search</span>
                    Search Batch
                </label>
                <input type="text" id="stockInSearch" placeholder="Search (e.g. batch-1)" class="w-full text-xs border-2 border-gray-300 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500 bg-white transition-all hover:border-green-400">
            </div>

            <!-- Date Range -->
            <div class="lg:col-span-4">
                <label class="flex items-center justify-center gap-1 text-[11px] font-semibold text-gray-700 mb-1.5">
                    <span class="material-symbols-outlined text-sm text-green-600">calendar_month</span>
                    Date Range Filter
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <div class="relative">
                        <label class="absolute -top-1.5 left-2 bg-white rounded-md px-1 text-[9px] font-medium text-gray-700 z-10">From</label>
                        <input type="date" id="dateFrom" class="w-full text-xs border-2 border-gray-300 rounded-lg px-2 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500 bg-white transition-all hover:border-green-400">
                    </div>
                    <div class="relative">
                        <label class="absolute -top-1.5 left-2 bg-white rounded-md px-1 text-[9px] font-medium text-gray-700 z-10">To</label>
                        <input type="date" id="dateTo" class="w-full text-xs border-2 border-gray-300 rounded-lg px-2 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500 bg-white transition-all hover:border-green-400">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="lg:col-span-2 flex gap-2">
                <button onclick="filterStockInTable()" class="flex-1 flex items-center justify-center gap-1 text-xs font-semibold bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 active:scale-95 transition-all shadow-md hover:shadow-lg">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    Apply
                </button>
                <button onclick="resetStockInFilters()" class="flex-1 flex items-center justify-center gap-1 text-xs font-semibold bg-gray-500 text-white px-3 py-2 rounded-lg hover:bg-gray-600 active:scale-95 transition-all shadow-md hover:shadow-lg">
                    <span class="material-symbols-outlined text-base">refresh</span>
                    Reset
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border border-green-200">
                <thead class="bg-green-50 text-gray-700 text-sm">
                    <tr>
                        <th class="px-4 py-3">Batch Number</th>
                        <th class="px-4 py-3">Total Quantity</th>
                        <th class="px-4 py-3">Date Added</th>
                        <th class="px-4 py-3">Expiration Date</th>
                        <th class="px-4 py-3">Days Until Expiry</th>
                    </tr>
                </thead>
<tbody id="stockInTableBody">
    @forelse ($batchGroups as $batchNumber => $batches)
        @php
            // Get the first record for display (they should all have same batch info)
            $firstBatch = $batches->first();
            
            // For display: show the original quantity from the first/only record
            // If you restock the same batch multiple times, sum them up
            $totalBatchQuantity = $batches->sum('original_quantity');
            
            // Use the date_added from first batch record
            $dateAdded = $firstBatch->date_added;
            
            // Calculate expiry days if expiration date exists
            if ($firstBatch->expiration_date) {
                $expirationDate = \Carbon\Carbon::parse($firstBatch->expiration_date)->startOfDay();
                $today = \Carbon\Carbon::today();
                $expiryDays = $today->diffInDays($expirationDate, false);
            } else {
                $expiryDays = null;
            }
        @endphp
        <tr class="hover:bg-gray-50 border-t data-row" 
            data-batch="{{ $batchNumber }}" 
            data-date="{{ \Carbon\Carbon::parse($dateAdded)->timestamp }}" 
            data-quantity="{{ $totalBatchQuantity }}">
            <td class="px-4 py-3 text-center text-sm">
                <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-medium">
                    {{ $batchNumber }}
                </span>
            </td>
            <td class="px-4 py-3 text-center text-sm">{{ $totalBatchQuantity }}</td>
            <td class="px-4 py-3 text-center text-sm">
                {{ \Carbon\Carbon::parse($dateAdded)->format('M j, Y') }}
            </td>
            <td class="px-4 py-3 text-center text-sm">
                @if($firstBatch->expiration_date)
                    {{ \Carbon\Carbon::parse($firstBatch->expiration_date)->format('M j, Y') }}
                @else
                    —
                @endif
            </td>
            <td class="px-4 py-3 text-center text-sm">
                @if($expiryDays !== null)
                    <span class="{{ $expiryDays < 0 ? 'text-red-600 font-medium' : ($expiryDays == 0 ? 'text-orange-600 font-medium' : 'text-blue-600 font-medium') }}">
                        @if($expiryDays > 0)
                            {{ $expiryDays }} day{{ $expiryDays > 1 ? 's' : '' }} left
                        @elseif($expiryDays == 0)
                            Expires today
                        @else
                            @php $daysAgo = abs($expiryDays); @endphp
                            Expired {{ $daysAgo }} day{{ $daysAgo > 1 ? 's' : '' }} ago
                        @endif
                    </span>
                @else
                    —
                @endif
            </td>
        </tr>
    @empty
        <tr class="no-data-row">
            <td colspan="5" class="text-center py-8 text-gray-500">
                <div class="flex flex-col items-center gap-2">
                    <span class="material-symbols-outlined text-4xl text-gray-300">inventory_2</span>
                    <span class="font-medium">No data available</span>
                    <span class="text-xs text-gray-400">Stock-in records will appear here once added</span>
                </div>
            </td>
        </tr>
    @endforelse
    <tr class="no-results-row hidden">
        <td colspan="5" class="text-center py-8 text-gray-500">
            <div class="flex flex-col items-center gap-2">
                <span class="material-symbols-outlined text-4xl text-gray-300">search_off</span>
                <span class="font-medium">No results found</span>
                <span class="text-xs text-gray-400">Try adjusting your filters or search criteria</span>
            </div>
        </td>
    </tr>
</tbody>
            </table>
        </div>
    </div>
</div>


    <!-- Stock-Out History Section -->
    <div id="stockOutSection" class="tab-content hidden">
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button id="salesSubTab" class="subtab-button py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600" onclick="switchSubTab('sales')">
                        Sales History
                    </button>
                    <button id="damagedSubTab" class="subtab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" onclick="switchSubTab('damaged')">
                        Damaged/Expired Items
                    </button>
                </nav>
            </div>
        </div>

        <!-- Sales History Sub-Tab -->
        <div id="salesSubSection" class="subtab-content">
            <div class="bg-amber-50 rounded-lg p-3 mb-4 border border-amber-200">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">
                    <!-- Sort By -->
                    <div class="lg:col-span-3">
                        <label class="flex items-center gap-1 text-[11px] font-semibold text-gray-700 mb-1.5">
                            <span class="material-symbols-outlined text-sm text-amber-600">sort</span>
                            Sort By
                        </label>
                        <select id="salesSort" class="w-full text-xs border-2 border-gray-300 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 bg-white transition-all hover:border-amber-400">
                            <option value="" disabled selected>Choose sorting...</option>
                            <option value="date_desc">📅 Date Sold (Newest First)</option>
                            <option value="date_asc">📅 Date Sold (Oldest First)</option>
                            <option value="quantity_desc">📊 Quantity (High to Low)</option>
                            <option value="quantity_asc">📊 Quantity (Low to High)</option>
                            <option value="amount_desc">💰 Amount (High to Low)</option>
                            <option value="batch_desc">📦 Batch (Newest First)</option>
                            <option value="batch_asc">📦 Batch (Oldest First)</option>
                        </select>
                    </div>

                    <!-- Search Batch -->
                    <div class="lg:col-span-3">
                        <label class="flex items-center gap-1 text-[11px] font-semibold text-gray-700 mb-1.5">
                            <span class="material-symbols-outlined text-sm text-amber-600">search</span>
                            Search Batch
                        </label>
                        <input type="text" id="salesSearch" placeholder="Search (e.g. batch-1)" class="w-full text-xs border-2 border-gray-300 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 bg-white transition-all hover:border-amber-400">
                    </div>

                    <!-- Date Range -->
                    <div class="lg:col-span-4">
                        <label class="flex items-center justify-center gap-1 text-[11px] font-semibold text-gray-700 mb-1.5">
                            <span class="material-symbols-outlined text-sm text-amber-600">calendar_month</span>
                            Date Range Filter
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="relative">
                                <label class="absolute -top-1.5 left-2 bg-white rounded-md px-1 text-[9px] font-medium text-gray-700 z-10">From</label>
                                <input type="date" id="salesDateFrom" class="w-full text-xs border-2 border-gray-300 rounded-lg px-2 py-2 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 bg-white transition-all hover:border-amber-400">
                            </div>
                            <div class="relative">
                                <label class="absolute -top-1.5 left-2 bg-white rounded-md px-1 text-[9px] font-medium text-gray-700 z-10">To</label>
                                <input type="date" id="salesDateTo" class="w-full text-xs border-2 border-gray-300 rounded-lg px-2 py-2 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 bg-white transition-all hover:border-amber-400">
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="lg:col-span-2 flex gap-2">
                        <button onclick="filterSalesTable()" class="flex-1 flex items-center justify-center gap-1 text-xs font-semibold bg-amber-600 text-white px-3 py-2 rounded-lg hover:bg-amber-700 active:scale-95 transition-all shadow-md hover:shadow-lg">
                            <span class="material-symbols-outlined text-base">check_circle</span>
                            Apply
                        </button>
                        <button onclick="resetSalesFilters()" class="flex-1 flex items-center justify-center gap-1 text-xs font-semibold bg-gray-500 text-white px-3 py-2 rounded-lg hover:bg-gray-600 active:scale-95 transition-all shadow-md hover:shadow-lg">
                            <span class="material-symbols-outlined text-base">refresh</span>
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border border-amber-200">
                        <thead class="bg-amber-50 text-gray-700 text-sm">
                            <tr>
                                <th class="px-4 py-3">Date Sold</th>
                                <th class="px-4 py-3">Batch Number</th>
                                <th class="px-4 py-3">Quantity Sold</th>
                                <th class="px-4 py-3">Price Used</th>
                                <th class="px-4 py-3">Total Amount</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody id="salesTableBody">
                            @forelse ($stockOutSalesHistory as $sale)
                                <tr class="hover:bg-gray-50 text-sm border-t data-row" 
                                    data-date="{{ \Carbon\Carbon::parse($sale->receipt_date)->timestamp }}" 
                                    data-quantity="{{ $sale->quantity_sold }}" 
                                    data-amount="{{ $sale->total_amount }}"
                                    data-batch="{{ $sale->batch_number ?? 'N/A' }}">
                                    <td class="px-4 py-3 text-center">
                                        {{ \Carbon\Carbon::parse($sale->receipt_date)->format('M j, Y g:i A') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-medium">
                                            {{ $sale->batch_number ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-medium">{{ $sale->quantity_sold }}</td>
                                    <td class="px-4 py-3 text-center font-medium">₱{{ number_format($sale->selling_price_used, 2) }}</td>
                                    <td class="px-4 py-3 text-center font-medium">₱{{ number_format($sale->total_amount, 2) }}</td>
                                    <!-- <td class="px-4 py-3 text-center">{{ $sale->sold_by }}</td> -->
                                </tr>
                            @empty
                                <tr class="no-data-row">
                                    <td colspan="6" class="text-center py-8 text-gray-500">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="material-symbols-outlined text-4xl text-gray-300">receipt_long</span>
                                            <span class="font-medium">No data available</span>
                                            <span class="text-xs text-gray-400">Sales records will appear here once transactions are made</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            <tr class="no-results-row hidden">
                                <td colspan="6" class="text-center py-8 text-gray-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="material-symbols-outlined text-4xl text-gray-300">search_off</span>
                                        <span class="font-medium">No results found</span>
                                        <span class="text-xs text-gray-400">Try adjusting your filters or search criteria</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        @if($stockOutSalesHistory->count() > 0)
                        <tfoot class="bg-gray-50" id="salesTableFooter">
                            <tr class="text-sm font-semibold border-t-2">
                                <td class="px-4 py-3 text-right" colspan="2">Total</td>
                                <td class="px-4 py-3 text-center text-amber-600 font-bold" id="salesTotalQuantity">{{ $totalStockOutSold }}</td>
                                <td class="px-4 py-3 text-center">—</td>
                                <td class="px-4 py-3 text-center text-amber-600 font-bold" id="salesTotalAmount">₱{{ number_format($totalRevenue, 2) }}</td>
                                <td class="px-4 py-3 text-center"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Damaged/Expired Items Sub-Tab -->
        <div id="damagedSubSection" class="subtab-content hidden">
            <div class="bg-red-50 rounded-lg p-3 mb-4 border border-red-200">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">
                    <!-- Sort By -->
                    <div class="lg:col-span-3">
                        <label class="flex items-center gap-1 text-[11px] font-semibold text-gray-700 mb-1.5">
                            <span class="material-symbols-outlined text-sm text-red-600">sort</span>
                            Sort By
                        </label>
                        <select id="damagedSort" class="w-full text-xs border-2 border-gray-300 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-red-500 focus:border-red-500 bg-white transition-all hover:border-red-400">
                            <option value="" disabled selected>Choose sorting...</option>
                            <option value="date_desc">📅 Date (Newest First)</option>
                            <option value="date_asc">📅 Date (Oldest First)</option>
                            <option value="quantity_desc">📊 Quantity (High to Low)</option>
                            <option value="quantity_asc">📊 Quantity (Low to High)</option>
                            <option value="batch_desc">📦 Batch (Newest First)</option>
                            <option value="batch_asc">📦 Batch (Oldest First)</option>
                        </select>
                    </div>

                    <!-- Filter Type -->
                    <div class="lg:col-span-3">
                        <label class="flex items-center gap-1 text-[11px] font-semibold text-gray-700 mb-1.5">
                            <span class="material-symbols-outlined text-sm text-red-600">filter_alt</span>
                            Filter Type
                        </label>
                        <select id="damagedTypeFilter" class="w-full text-xs border-2 border-gray-300 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-red-500 focus:border-red-500 bg-white transition-all hover:border-red-400">
                            <option value="">All Types</option>
                            <option value="Expired">🕐 Expired</option>
                            <option value="Broken">💔 Broken</option>
                            <option value="Spoiled">🗑️ Spoiled</option>
                            <option value="Damaged">⚠️ Damaged</option>
                            <option value="Defective">🔧 Defective</option>
                            <option value="Contaminated">☣️ Contaminated</option>
                            <option value="Crushed">📦 Crushed</option>
                            <option value="Leaking">💧 Leaking</option>
                            <option value="Torn">✂️ Torn</option>
                            <option value="Wet">🌊 Wet/Water Damaged</option>
                            <option value="Mold">🦠 Mold/Fungus</option>
                            <option value="Pest">🐛 Pest Damage</option>
                            <option value="Temperature">🌡️ Temperature Abuse</option>
                            <option value="Recalled">🚫 Recalled</option>
                            <option value="Missing Parts">🧩 Missing Parts/Incomplete</option>
                            <option value="Wrong Item">❌ Wrong Item Received</option>
                            <option value="Unsealed">📭 Unsealed/Opened</option>
                            <option value="Faded">🎨 Faded/Discolored</option>
                            <option value="Stolen">🔒 Stolen/Lost</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="lg:col-span-4">
                        <label class="flex items-center justify-center gap-1 text-[11px] font-semibold text-gray-700 mb-1.5">
                            <span class="material-symbols-outlined text-sm text-red-600">calendar_month</span>
                            Date Range Filter
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="relative">
                                <label class="absolute -top-1.5 left-2 bg-white rounded-md px-1 text-[9px] font-medium text-gray-700 z-10">From</label>
                                <input type="date" id="damagedDateFrom" class="w-full text-xs border-2 border-gray-300 rounded-lg px-2 py-2 focus:ring-1 focus:ring-red-500 focus:border-red-500 bg-white transition-all hover:border-red-400">
                            </div>
                            <div class="relative">
                                <label class="absolute -top-1.5 left-2 bg-white rounded-md px-1 text-[9px] font-medium text-gray-600 z-10">To</label>
                                <input type="date" id="damagedDateTo" class="w-full text-xs border-2 border-gray-300 rounded-lg px-2 py-2 focus:ring-1 focus:ring-red-500 focus:border-red-500 bg-white transition-all hover:border-red-400">
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="lg:col-span-2 flex gap-2">
                        <button onclick="filterDamagedTable()" class="flex-1 flex items-center justify-center gap-1 text-xs font-semibold bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 active:scale-95 transition-all shadow-md hover:shadow-lg">
                            <span class="material-symbols-outlined text-base">check_circle</span>
                            Apply
                        </button>
                        <button onclick="resetDamagedFilters()" class="flex-1 flex items-center justify-center gap-1 text-xs font-semibold bg-gray-500 text-white px-3 py-2 rounded-lg hover:bg-gray-600 active:scale-95 transition-all shadow-md hover:shadow-lg">
                            <span class="material-symbols-outlined text-base">refresh</span>
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border border-red-200">
                        <thead class="bg-red-50 text-gray-700 text-sm">
                            <tr>
                                <th class="px-4 py-3">Batch Number</th>
                                <th class="px-4 py-3">Date Out</th>
                                <th class="px-4 py-3">Quantity Out</th>
                                <th class="px-4 py-3">Type</th>
                                <!-- <th class="px-4 py-3">Processed By</th> -->
                            </tr>
                        </thead>
                        <tbody id="damagedTableBody">
                            @forelse ($stockOutDamagedHistory as $damaged)
                                <tr class="hover:bg-gray-50 text-sm border-t data-row" 
                                    data-date="{{ \Carbon\Carbon::parse($damaged->damaged_date)->timestamp }}" 
                                    data-quantity="{{ $damaged->damaged_quantity }}" 
                                    data-batch="{{ $damaged->batch_number ?? 'N/A' }}">
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-medium">
                                            {{ $damaged->batch_number ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        {{ \Carbon\Carbon::parse($damaged->damaged_date)->format('M j, Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ $damaged->damaged_quantity }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $damaged->damaged_type == 'Expired' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' }}">
                                            {{ $damaged->damaged_type }}
                                        </span>
                                    </td>
                                    <!-- <td class="px-4 py-3 text-center">{{ $damaged->reported_by }}</td> -->
                                </tr>
                            @empty
                                <tr class="no-data-row">
                                    <td colspan="5" class="text-center py-8 text-gray-500">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="material-symbols-outlined text-4xl text-gray-300">broken_image</span>
                                            <span class="font-medium">No data available</span>
                                            <span class="text-xs text-gray-400">Damaged or expired items will appear here once reported</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            <tr class="no-results-row hidden">
                                <td colspan="5" class="text-center py-8 text-gray-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="material-symbols-outlined text-4xl text-gray-300">search_off</span>
                                        <span class="font-medium">No results found</span>
                                        <span class="text-xs text-gray-400">Try adjusting your filters or search criteria</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        @if($stockOutDamagedHistory->count() > 0)
                        <tfoot class="bg-gray-50" id="damagedTableFooter">
                            <tr class="text-sm font-semibold border-t-2">
                                <td class="px-4 py-3 text-right">Total</td>
                                <td class="px-4 py-3 text-center">—</td>
                                <td class="px-4 py-3 text-center font-bold text-red-600" id="damagedTotalQuantity">{{ $totalStockOutDamaged }}</td>
                                <td colspan="2" class="px-4 py-3 text-center">—</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
</div>

<script>
// Helper function to update table visibility
function updateTableVisibility(tableBodyId) {
    const tableBody = document.getElementById(tableBodyId);
    const dataRows = tableBody.querySelectorAll('.data-row');
    const noDataRow = tableBody.querySelector('.no-data-row');
    const noResultsRow = tableBody.querySelector('.no-results-row');
    
    // Count visible data rows
    const visibleRows = Array.from(dataRows).filter(row => row.style.display !== 'none');
    
    // If there are no data rows at all (empty from database)
    if (dataRows.length === 0) {
        if (noDataRow) noDataRow.classList.remove('hidden');
        if (noResultsRow) noResultsRow.classList.add('hidden');
        return;
    }
    
    // If there are data rows but none are visible (filtered out)
    if (visibleRows.length === 0) {
        if (noDataRow) noDataRow.classList.add('hidden');
        if (noResultsRow) noResultsRow.classList.remove('hidden');
    } else {
        // There are visible rows
        if (noDataRow) noDataRow.classList.add('hidden');
        if (noResultsRow) noResultsRow.classList.add('hidden');
    }
}

// Tab switching functionality
function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    document.getElementById(tabName + 'Section').classList.remove('hidden');
    
    document.getElementById(tabName + 'Tab').classList.add('border-blue-500', 'text-blue-600');
    document.getElementById(tabName + 'Tab').classList.remove('border-transparent', 'text-gray-500');

    if (tabName === 'stockOut') {
        switchSubTab('sales');
    }
}

// Sub-tab switching functionality
function switchSubTab(subTabName) {
    document.querySelectorAll('.subtab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    document.querySelectorAll('.subtab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    document.getElementById(subTabName + 'SubSection').classList.remove('hidden');
    
    document.getElementById(subTabName + 'SubTab').classList.add('border-blue-500', 'text-blue-600');
    document.getElementById(subTabName + 'SubTab').classList.remove('border-transparent', 'text-gray-500');
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize with default sort
    sortStockInTable('date_desc');
    sortSalesTable('date_desc');
    sortDamagedTable('date_desc');
    
    // Add event listeners for sort dropdowns
    const stockInSort = document.getElementById('stockInSort');
    if (stockInSort) {
        stockInSort.addEventListener('change', function() {
            sortStockInTable(this.value);
        });
    }
    
    const salesSort = document.getElementById('salesSort');
    if (salesSort) {
        salesSort.addEventListener('change', function() {
            sortSalesTable(this.value);
        });
    }
    
    const damagedSort = document.getElementById('damagedSort');
    if (damagedSort) {
        damagedSort.addEventListener('change', function() {
            sortDamagedTable(this.value);
        });
    }
    
    // Add live search for stock-in batch
    const stockInSearch = document.getElementById('stockInSearch');
    if (stockInSearch) {
        stockInSearch.addEventListener('input', function() {
            filterStockInTable();
        });
    }
    
    // Add live search for sales batch
    const salesSearch = document.getElementById('salesSearch');
    if (salesSearch) {
        salesSearch.addEventListener('input', function() {
            filterSalesTable();
        });
    }
    
    // Add live filter for damaged type
    const damagedTypeFilter = document.getElementById('damagedTypeFilter');
    if (damagedTypeFilter) {
        damagedTypeFilter.addEventListener('change', function() {
            filterDamagedTable();
        });
    }
});

// Stock-In Table Functions
function filterStockInTable() {
    const searchTerm = document.getElementById('stockInSearch').value.toLowerCase();
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const rows = document.querySelectorAll('#stockInTableBody .data-row');
    
    rows.forEach(row => {
        const batchNumber = row.getAttribute('data-batch')?.toLowerCase() || '';
        const rowDate = new Date(parseInt(row.getAttribute('data-date')) * 1000);
        rowDate.setHours(0, 0, 0, 0);
        
        let shouldShow = true;
        
        if (searchTerm && !batchNumber.includes(searchTerm)) {
            shouldShow = false;
        }
        
        if (dateFrom) {
            const fromDate = new Date(dateFrom);
            fromDate.setHours(0, 0, 0, 0);
            if (rowDate < fromDate) shouldShow = false;
        }
        if (dateTo) {
            const toDate = new Date(dateTo);
            toDate.setHours(0, 0, 0, 0);
            if (rowDate > toDate) shouldShow = false;
        }
        
        row.style.display = shouldShow ? '' : 'none';
    });
    
    updateTableVisibility('stockInTableBody');
}

function sortStockInTable(sortValue = null) {
    if (!sortValue) {
        sortValue = document.getElementById('stockInSort').value;
    }
    
    if (!sortValue || sortValue === '') {
        return;
    }
    
    const tableBody = document.getElementById('stockInTableBody');
    const rows = Array.from(tableBody.querySelectorAll('.data-row'));
    
    rows.sort((a, b) => {
        switch(sortValue) {
            case 'date_desc':
                return parseInt(b.getAttribute('data-date')) - parseInt(a.getAttribute('data-date'));
            case 'date_asc':
                return parseInt(a.getAttribute('data-date')) - parseInt(b.getAttribute('data-date'));
            case 'batch_desc':
                return b.getAttribute('data-batch').localeCompare(a.getAttribute('data-batch'));
            case 'batch_asc':
                return a.getAttribute('data-batch').localeCompare(b.getAttribute('data-batch'));
            case 'quantity_desc':
                return parseFloat(b.getAttribute('data-quantity')) - parseFloat(a.getAttribute('data-quantity'));
            case 'quantity_asc':
                return parseFloat(a.getAttribute('data-quantity')) - parseFloat(b.getAttribute('data-quantity'));
            default:
                return 0;
        }
    });
    
    // Get static rows
    const noDataRow = tableBody.querySelector('.no-data-row');
    const noResultsRow = tableBody.querySelector('.no-results-row');
    
    // Clear and re-append
    tableBody.innerHTML = '';
    rows.forEach(row => tableBody.appendChild(row));
    if (noDataRow) tableBody.appendChild(noDataRow);
    if (noResultsRow) tableBody.appendChild(noResultsRow);
    
    updateTableVisibility('stockInTableBody');
}

function resetStockInFilters() {
    document.getElementById('stockInSearch').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    document.getElementById('stockInSort').selectedIndex = 0;
    
    const rows = document.querySelectorAll('#stockInTableBody .data-row');
    rows.forEach(row => {
        row.style.display = '';
    });
    
    sortStockInTable('date_desc');
}

// Sales Table Functions
function filterSalesTable() {
    const searchTerm = document.getElementById('salesSearch').value.toLowerCase();
    const dateFrom = document.getElementById('salesDateFrom').value;
    const dateTo = document.getElementById('salesDateTo').value;
    
    const rows = document.querySelectorAll('#salesTableBody .data-row');
    
    rows.forEach(row => {
        const rowDate = new Date(parseInt(row.getAttribute('data-date')) * 1000);
        rowDate.setHours(0, 0, 0, 0);
        
        const batchNumber = row.getAttribute('data-batch')?.toLowerCase() || '';
        let shouldShow = true;
        
        if (searchTerm && !batchNumber.includes(searchTerm)) {
            shouldShow = false;
        }
        
        if (dateFrom) {
            const fromDate = new Date(dateFrom);
            fromDate.setHours(0, 0, 0, 0);
            if (rowDate < fromDate) shouldShow = false;
        }
        if (dateTo) {
            const toDate = new Date(dateTo);
            toDate.setHours(0, 0, 0, 0);
            if (rowDate > toDate) shouldShow = false;
        }
        
        row.style.display = shouldShow ? '' : 'none';
    });
    
    updateTableVisibility('salesTableBody');
    updateSalesFooter();  // ADD THIS LINE
}

function sortSalesTable(sortValue = null) {
    if (!sortValue) {
        sortValue = document.getElementById('salesSort').value;
    }
    
    if (!sortValue || sortValue === '') {
        return;
    }
    
    const tableBody = document.getElementById('salesTableBody');
    const rows = Array.from(tableBody.querySelectorAll('.data-row'));
    
    rows.sort((a, b) => {
        switch(sortValue) {
            case 'date_desc':
                return parseInt(b.getAttribute('data-date')) - parseInt(a.getAttribute('data-date'));
            case 'date_asc':
                return parseInt(a.getAttribute('data-date')) - parseInt(b.getAttribute('data-date'));
            case 'quantity_desc':
                return parseFloat(b.getAttribute('data-quantity')) - parseFloat(a.getAttribute('data-quantity'));
            case 'quantity_asc':
                return parseFloat(a.getAttribute('data-quantity')) - parseFloat(b.getAttribute('data-quantity'));
            case 'amount_desc':
                return parseFloat(b.getAttribute('data-amount')) - parseFloat(a.getAttribute('data-amount'));
            case 'amount_asc':
                return parseFloat(a.getAttribute('data-amount')) - parseFloat(b.getAttribute('data-amount'));
            case 'batch_desc':
                return b.getAttribute('data-batch').localeCompare(a.getAttribute('data-batch'));
            case 'batch_asc':
                return a.getAttribute('data-batch').localeCompare(b.getAttribute('data-batch'));
            default:
                return 0;
        }
    });
    
    // Get static rows
    const noDataRow = tableBody.querySelector('.no-data-row');
    const noResultsRow = tableBody.querySelector('.no-results-row');
    
    tableBody.innerHTML = '';
    rows.forEach(row => tableBody.appendChild(row));
    if (noDataRow) tableBody.appendChild(noDataRow);
    if (noResultsRow) tableBody.appendChild(noResultsRow);
    
    updateTableVisibility('salesTableBody');
    updateSalesFooter();  // ADD THIS LINE
}

function resetSalesFilters() {
    document.getElementById('salesSearch').value = '';
    document.getElementById('salesDateFrom').value = '';
    document.getElementById('salesDateTo').value = '';
    document.getElementById('salesSort').selectedIndex = 0;
    
    const rows = document.querySelectorAll('#salesTableBody .data-row');
    rows.forEach(row => {
        row.style.display = '';
    });
    
    sortSalesTable('date_desc');
    updateSalesFooter();  // ADD THIS LINE
}

// Damaged Table Functions
function filterDamagedTable() {
    const typeFilter = document.getElementById('damagedTypeFilter').value.toLowerCase();
    const dateFrom = document.getElementById('damagedDateFrom').value;
    const dateTo = document.getElementById('damagedDateTo').value;
    
    const rows = document.querySelectorAll('#damagedTableBody .data-row');
    
    rows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        const rowDate = new Date(parseInt(row.getAttribute('data-date')) * 1000);
        rowDate.setHours(0, 0, 0, 0);
        
        let shouldShow = true;
        
        if (typeFilter && !rowText.includes(typeFilter)) {
            shouldShow = false;
        }
        
        if (dateFrom) {
            const fromDate = new Date(dateFrom);
            fromDate.setHours(0, 0, 0, 0);
            if (rowDate < fromDate) shouldShow = false;
        }
        if (dateTo) {
            const toDate = new Date(dateTo);
            toDate.setHours(0, 0, 0, 0);
            if (rowDate > toDate) shouldShow = false;
        }
        
        row.style.display = shouldShow ? '' : 'none';
    });
    
    updateTableVisibility('damagedTableBody');
    updateDamagedFooter();  // ADD THIS LINE
}

function sortDamagedTable(sortValue = null) {
    if (!sortValue) {
        sortValue = document.getElementById('damagedSort').value;
    }
    
    if (!sortValue || sortValue === '') {
        return;
    }
    
    const tableBody = document.getElementById('damagedTableBody');
    const rows = Array.from(tableBody.querySelectorAll('.data-row'));
    
    rows.sort((a, b) => {
        switch(sortValue) {
            case 'date_desc':
                return parseInt(b.getAttribute('data-date')) - parseInt(a.getAttribute('data-date'));
            case 'date_asc':
                return parseInt(a.getAttribute('data-date')) - parseInt(b.getAttribute('data-date'));
            case 'quantity_desc':
                return parseFloat(b.getAttribute('data-quantity')) - parseFloat(a.getAttribute('data-quantity'));
            case 'quantity_asc':
                return parseFloat(a.getAttribute('data-quantity')) - parseFloat(b.getAttribute('data-quantity'));
            case 'batch_desc':
                return b.getAttribute('data-batch').localeCompare(a.getAttribute('data-batch'));
            case 'batch_asc':
                return a.getAttribute('data-batch').localeCompare(b.getAttribute('data-batch'));
            default:
                return 0;
        }
    });
    
    // Get static rows
    const noDataRow = tableBody.querySelector('.no-data-row');
    const noResultsRow = tableBody.querySelector('.no-results-row');
    
    tableBody.innerHTML = '';
    rows.forEach(row => tableBody.appendChild(row));
    if (noDataRow) tableBody.appendChild(noDataRow);
    if (noResultsRow) tableBody.appendChild(noResultsRow);
    
    updateTableVisibility('damagedTableBody');
    updateDamagedFooter();  // ADD THIS LINE
}

function resetDamagedFilters() {
    document.getElementById('damagedTypeFilter').selectedIndex = 0;
    document.getElementById('damagedDateFrom').value = '';
    document.getElementById('damagedDateTo').value = '';
    document.getElementById('damagedSort').selectedIndex = 0;
    
    const rows = document.querySelectorAll('#damagedTableBody .data-row');
    rows.forEach(row => {
        row.style.display = '';
    });
    
    sortDamagedTable('date_desc');
    updateDamagedFooter();  // ADD THIS LINE
}

// Helper function to update sales table footer totals
function updateSalesFooter() {
    const footer = document.getElementById('salesTableFooter');
    if (!footer) return;
    
    const dataRows = document.querySelectorAll('#salesTableBody .data-row');
    const visibleRows = Array.from(dataRows).filter(row => row.style.display !== 'none');
    
    // Hide footer if no visible rows
    if (visibleRows.length === 0) {
        footer.style.display = 'none';
        return;
    }
    
    // Show footer and calculate totals
    footer.style.display = '';
    
    let totalQuantity = 0;
    let totalAmount = 0;
    
    visibleRows.forEach(row => {
        totalQuantity += parseFloat(row.getAttribute('data-quantity')) || 0;
        totalAmount += parseFloat(row.getAttribute('data-amount')) || 0;
    });
    
    // Update footer cells
    document.getElementById('salesTotalQuantity').textContent = totalQuantity;
    document.getElementById('salesTotalAmount').textContent = '₱' + totalAmount.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Helper function to update damaged table footer totals
function updateDamagedFooter() {
    const footer = document.getElementById('damagedTableFooter');
    if (!footer) return;
    
    const dataRows = document.querySelectorAll('#damagedTableBody .data-row');
    const visibleRows = Array.from(dataRows).filter(row => row.style.display !== 'none');
    
    // Hide footer if no visible rows
    if (visibleRows.length === 0) {
        footer.style.display = 'none';
        return;
    }
    
    // Show footer and calculate totals
    footer.style.display = '';
    
    let totalQuantity = 0;
    
    visibleRows.forEach(row => {
        totalQuantity += parseFloat(row.getAttribute('data-quantity')) || 0;
    });
    
    // Update footer cell
    document.getElementById('damagedTotalQuantity').textContent = totalQuantity;
}
</script>

<!-- Print Barcode JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
function printProductBarcode(barcode, productName) {
    const userCopies = prompt(`How many barcode labels would you like to print for "${productName}"?`, '1');
    const numCopies = parseInt(userCopies) || 1;

    if (numCopies < 1) return;

    // Create hidden iframe
    const iframe = document.createElement('iframe');
    iframe.style.cssText = 'position:absolute;width:0;height:0;border:0;opacity:0;';
    document.body.appendChild(iframe);

    const doc = iframe.contentWindow.document;

    // Build HTML with exact copies
    let htmlContent = '';
    for (let i = 0; i < numCopies; i++) {
        const displayName = productName.length > 25 ? productName.substring(0, 25) : productName;
        
        htmlContent += `
        <div style="
            width: 2in; 
            height: 1in; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
            align-items: center;
            padding: 0.05in;
            box-sizing: border-box;
            page-break-after: ${i < numCopies - 1 ? 'always' : 'auto'};
        ">
            <div style="font-size: 6px; font-weight: bold; color: #333; width: 100%; text-align: center;">
                ${displayName.toUpperCase()}
            </div>
            <svg id="barcode-${i}"></svg>
            <div style="font-size: 8px; font-family: monospace; font-weight: bold; width: 100%; text-align: center;">
                ${barcode}
            </div>
        </div>
    `;
    }

    doc.open();
    doc.write(`
    <!DOCTYPE html>
    <html>
    <head>
        <title>Barcode Labels - ${productName}</title>
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
        <style>
            @media print {
                @page {
                    margin: 0mm !important;
                    size: 2in 1in !important;
                }
                body {
                    margin: 0 !important;
                    padding: 0 !important;
                    background: white !important;
                }
            }
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
        </style>
    </head>
    <body>
        ${htmlContent}
        <script>
            function generateBarcodes() {
                if (typeof JsBarcode === 'undefined') {
                    setTimeout(generateBarcodes, 50);
                    return;
                }
                
                // Render all barcodes
                for (let i = 0; i < ${numCopies}; i++) {
                    JsBarcode("#barcode-" + i, "${barcode}", {
                        format: "CODE128",
                        lineColor: "#000000",
                        width: 1.5,
                        height: 50,
                        displayValue: false,
                        margin: 2
                    });
                }
                
                // Trigger print immediately after barcodes are ready
                setTimeout(function() {
                    window.print();
                    
                    // Clean up iframe after printing
                    window.onafterprint = function() {
                        if (window.frameElement) {
                            window.frameElement.parentNode.removeChild(window.frameElement);
                        }
                    };
                    
                    // Fallback cleanup if onafterprint doesn't fire
                    setTimeout(function() {
                        if (window.frameElement) {
                            window.frameElement.parentNode.removeChild(window.frameElement);
                        }
                    }, 1000);
                }, 100);
            }
            
            if (document.readyState === 'complete') {
                generateBarcodes();
            } else {
                window.onload = generateBarcodes;
            }
        <\/script>
    </body>
    </html>
    `);
    doc.close();
}
</script>

<!-- Initialize Barcode Display -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const barcodeElement = document.getElementById('product-barcode-display');
    if (barcodeElement && typeof JsBarcode !== 'undefined') {
        try {
            JsBarcode(barcodeElement, "{{ $product->barcode }}", {
                format: "CODE128",
                lineColor: "#000000",
                width: 2,
                height: 50,
                displayValue: false,
                margin: 5
            });
        } catch (error) {
            console.error('Error generating barcode:', error);
            barcodeElement.style.display = 'none';
        }
    }
});
</script>


@endsection