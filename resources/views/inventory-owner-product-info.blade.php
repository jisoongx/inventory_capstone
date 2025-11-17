@extends('dashboards.owner.owner')
<head>
    <title>Product Information</title>
</head>
@section('content')

<div class="px-4">
    @livewire('expiration-container')
</div>

<div class="max-w-6xl mx-auto py-4"> 

<!-- Product Information Section (Modified) -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <!-- Back Button -->
    <div class="mb-6 mt-2">
        <a href="{{ route('inventory-owner') }}" 
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
                     class="w-36 h-36 object-cover rounded-lg shadow-md border">
            @else
                <div class="w-36 h-36 flex items-center justify-center bg-gray-200 text-gray-500 rounded-lg shadow-md border">
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
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 shadow-sm h-full flex flex-col justify-between">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-blue-600 rounded-md flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-base">barcode</span>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-800">Product Barcode</h3>
                    </div>
                    <button onclick="printProductBarcode('{{ $product->barcode }}', '{{ $product->name }}')" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 active:scale-95 transition-all duration-200 shadow-md hover:shadow-lg">
                        <span class="material-symbols-outlined text-sm">print</span>
                        Print
                    </button>
                </div>
                <div class="bg-white rounded-lg px-3 py-2.5 border border-blue-100">
                    <p class="font-mono text-base font-bold text-gray-800 tracking-wide text-center">
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
                    <p class="text-xl font-bold text-orange-900">{{ $totalStockOutSold }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                    <p class="text-xs text-purple-700 font-medium">Total Revenue</p>
                    <p class="text-xl font-bold text-purple-900">₱{{ number_format($totalRevenue, 2) }}</p>
                </div>
            </div>

            <!-- Damaged & Expired Cards -->
            <div class="grid grid-cols-2 gap-4">
                @php
                    $totalExpired = $stockOutDamagedHistory->where('damaged_reason', 'Expired')->sum('damaged_quantity');
                    $totalDamaged = $stockOutDamagedHistory->where('damaged_reason', '!=', 'Expired')->sum('damaged_quantity');
                @endphp
                
                <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                    <p class="text-xs text-orange-700 font-medium">Damaged Items</p>
                    <p class="text-xl font-bold text-amber-900">{{ $totalDamaged }}</p>
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
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-md font-semibold">Stock-In History</h2>
            <div class="flex gap-2 flex-wrap">
                <select id="stockInSort" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <option value="" disabled selected>Select sort option</option>
                    <option value="date_desc">Date (Newest First)</option>
                    <option value="date_asc">Date (Oldest First)</option>
                    <option value="batch_desc">Batch (Newest First)</option>
                    <option value="batch_asc">Batch (Oldest First)</option>
                    <option value="quantity_desc">Quantity (High to Low)</option>
                    <option value="quantity_asc">Quantity (Low to High)</option>
                </select>
                <input type="text" id="stockInSearch" placeholder="Search batch..." class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                <input type="date" id="dateFrom" placeholder="From Date" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                <input type="date" id="dateTo" placeholder="To Date" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                <button onclick="filterStockInTable()" class="text-xs bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition">Apply Filter</button>
                <button onclick="resetStockInFilters()" class="text-xs bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 transition">Reset</button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border border-gray-100">
                    <thead class="bg-gray-100 text-gray-700 text-sm">
                        <tr>
                            <th class="px-4 py-3 border">Batch Number</th>
                            <th class="px-4 py-3 border">Total Quantity</th>
                            <th class="px-4 py-3 border">Date Added</th>
                            <th class="px-4 py-3 border">Expiration Date</th>
                            <th class="px-4 py-3 border">Days Until Expiry</th>
                        </tr>
                    </thead>
                    <tbody id="stockInTableBody">
                        @forelse ($batchGroups as $batchNumber => $batches)
                            @php
                                $firstBatch = $batches->first();
                                // FIXED: Use the calculated original_quantity instead of sum('stock')
                                $totalBatchQuantity = $firstBatch->original_quantity;
                                
                                // FIXED: Calculate days including both start and end dates
                                if ($firstBatch->expiration_date) {
                                    $expirationDate = \Carbon\Carbon::parse($firstBatch->expiration_date)->startOfDay();
                                    $now = \Carbon\Carbon::now()->startOfDay();
                                    
                                    if ($expirationDate->isPast()) {
                                        // If expired, show negative days (days since expiration)
                                        // Add 1 to include today in the count
                                        $expiryDays = -($now->diffInDays($expirationDate) + 1);
                                    } else {
                                        // If not expired, show positive days (days until expiration)
                                        // Add 1 to include today in the count
                                        $expiryDays = $now->diffInDays($expirationDate) + 1;
                                    }
                                } else {
                                    $expiryDays = null;
                                }
                            @endphp
                            <tr class="hover:bg-gray-50" data-batch="{{ $batchNumber }}" data-date="{{ \Carbon\Carbon::parse($firstBatch->date_added)->timestamp }}" data-quantity="{{ $totalBatchQuantity }}">
                                <td class="px-4 py-3 border text-center text-sm">{{ $batchNumber }}</td>
                                <td class="px-4 py-3 border text-center text-sm">{{ $totalBatchQuantity }}</td>
                                <td class="px-4 py-3 border text-center text-sm">
                                    {{ \Carbon\Carbon::parse($firstBatch->date_added)->format('M j, Y') }}
                                </td>
                                <td class="px-4 py-3 border text-center text-sm">
                                    @if($firstBatch->expiration_date)
                                        {{ \Carbon\Carbon::parse($firstBatch->expiration_date)->format('M j, Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 border text-center text-sm">
                                    @if($expiryDays !== null)
                                        <span class="{{ $expiryDays <= 0 ? 'text-red-600 font-medium' : 'text-blue-600 font-medium' }}">
                                            @if($expiryDays > 0)
                                                {{ $expiryDays }} day{{ $expiryDays === 1 ? '' : 's' }} left
                                            @elseif($expiryDays == 0)
                                                Expires today
                                            @else
                                                @php
                                                    $daysAgo = abs($expiryDays);
                                                @endphp
                                                Expired {{ $daysAgo }} day{{ $daysAgo === 1 ? '' : 's' }} ago
                                            @endif
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-gray-500">No stock-in records found.</td>
                            </tr>
                        @endforelse
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
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-md font-semibold">Sales History</h3>
                <div class="flex gap-2 flex-wrap">
                    <select id="salesSort" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                        <option value="" disabled selected>Select sort option</option>
                        <option value="date_desc">Date (Newest First)</option>
                        <option value="date_asc">Date (Oldest First)</option>
                        <option value="quantity_desc">Quantity (High to Low)</option>
                        <option value="quantity_asc">Quantity (Low to High)</option>
                        <option value="amount_desc">Amount (High to Low)</option>
                    </select>
                    <!-- <input type="text" id="salesSearch" placeholder="Search receipt..." class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100"> -->
                    <input type="date" id="salesDateFrom" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <input type="date" id="salesDateTo" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <button onclick="filterSalesTable()" class="text-xs bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition">Apply Filter</button>
                    <button onclick="resetSalesFilters()" class="text-xs bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 transition">Reset</button>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border border-gray-100">
                        <thead class="bg-gray-100 text-gray-700 text-sm">
                            <tr>
                                <th class="px-4 py-3 border">Date Sold</th>
                                <th class="px-4 py-3 border">Quantity Sold</th>
                                <th class="px-4 py-3 border">Unit Price</th>
                                <th class="px-4 py-3 border">Total Amount</th>
                                <th class="px-4 py-3 border">Sold By</th>
                            </tr>
                        </thead>
                        <tbody id="salesTableBody">
                            @forelse ($stockOutSalesHistory as $sale)
                                <tr class="hover:bg-gray-50 text-sm" 
                                    data-date="{{ \Carbon\Carbon::parse($sale->receipt_date)->timestamp }}" 
                                    data-quantity="{{ $sale->quantity_sold }}" 
                                    data-amount="{{ $sale->total_amount }}">
                                    <td class="px-4 py-3 border text-center">
                                        {{ \Carbon\Carbon::parse($sale->receipt_date)->format('M j, Y g:i A') }}
                                    </td>
                                    <td class="px-4 py-3 border text-center">{{ $sale->quantity_sold }}</td>
                                    <td class="px-4 py-3 border text-center">₱{{ number_format($sale->selling_price, 2) }}</td>
                                    <td class="px-4 py-3 border text-center font-medium">₱{{ number_format($sale->total_amount, 2) }}</td>
                                    <td class="px-4 py-3 border text-center">{{ $sale->sold_by }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-gray-500">No sales records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($stockOutSalesHistory->count() > 0)
                        <tfoot class="bg-gray-50">
                            <tr class="text-sm font-semibold">
                                <td class="px-4 py-3 border text-right">Total</td>
                                <td class="px-4 py-3 border text-center">{{ $totalStockOutSold }}</td>
                                <td class="px-4 py-3 border text-center">—</td>
                                <td class="px-4 py-3 border text-center">₱{{ number_format($totalRevenue, 2) }}</td>
                                <td class="px-4 py-3 border text-center">—</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Damaged/Expired Items Sub-Tab -->
        <div id="damagedSubSection" class="subtab-content hidden">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-md font-semibold">Damaged/Expired Items</h3>
                <div class="flex gap-2 flex-wrap">
                    <select id="damagedSort" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                        <option value="" disabled selected>Select sort option</option>
                        <option value="date_desc">Date (Newest First)</option>
                        <option value="date_asc">Date (Oldest First)</option>
                        <option value="quantity_desc">Quantity (High to Low)</option>
                        <option value="quantity_asc">Quantity (Low to High)</option>
                        <option value="batch_desc">Batch (Newest First)</option>
                        <option value="batch_asc">Batch (Oldest First)</option>
                    </select>
                    <input type="text" id="damagedSearch" placeholder="Search reason..." class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <input type="date" id="damagedDateFrom" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <input type="date" id="damagedDateTo" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <button onclick="filterDamagedTable()" class="text-xs bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition">Apply Filter</button>
                    <button onclick="resetDamagedFilters()" class="text-xs bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 transition">Reset</button>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border border-gray-100">
                        <thead class="bg-gray-100 text-gray-700 text-sm">
                            <tr>
                                <th class="px-4 py-3 border">Date Out</th>
                                <th class="px-4 py-3 border">Quantity Out</th>
                                <th class="px-4 py-3 border">Reason</th>
                                <th class="px-4 py-3 border">Processed By</th>
                            </tr>
                        </thead>
                        <tbody id="damagedTableBody">
                            @forelse ($stockOutDamagedHistory as $damaged)
                                <tr> 
                                    <td class="px-4 py-3 border text-center">
                                        {{ \Carbon\Carbon::parse($damaged->damaged_date)->format('M j, Y') }}
                                    </td>
                                    <td class="px-4 py-3 border text-center">{{ $damaged->damaged_quantity }}</td>
                                    <td class="px-4 py-3 border text-center">
                                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">
                                            {{ $damaged->damaged_reason }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 border text-center">{{ $damaged->reported_by ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-6 text-gray-500">No damaged/expired items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($stockOutDamagedHistory->count() > 0)
                        <tfoot class="bg-gray-50">
                            <tr class="text-sm font-semibold">
                                <td class="px-4 py-3 border text-right">Total</td>
                                <td class="px-4 py-3 border text-center">{{ $totalStockOutDamaged }}</td>
                                <td colspan="2" class="px-4 py-3 border text-center">—</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

    <!-- Stock Comparison Section -->
    <!-- <div id="comparisonSection" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border border-gray-100">
                    <thead class="bg-gray-100 text-gray-700 text-sm">
                        <tr>
                            <th class="px-4 py-3 border">Metric</th>
                            <th class="px-4 py-3 border">Stock In</th>
                            <th class="px-4 py-3 border">Stock Out (Sales)</th>
                            <th class="px-4 py-3 border">Stock Out (Damaged)</th>
                            <th class="px-4 py-3 border">Total Stock Out</th>
                            <th class="px-4 py-3 border">Net Movement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-sm hover:bg-gray-50">
                            <td class="px-4 py-3 border text-center font-medium">All Time</td>
                            <td class="px-4 py-3 border text-center">{{ $totalStockIn }}</td>
                            <td class="px-4 py-3 border text-center">{{ $totalStockOutSold }}</td>
                            <td class="px-4 py-3 border text-center">{{ $totalStockOutDamaged }}</td>
                            <td class="px-4 py-3 border text-center">{{ $totalStockOut }}</td>
                            <td class="px-4 py-3 border text-center font-medium {{ ($totalStockIn - $totalStockOut) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $totalStockIn - $totalStockOut }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div> -->
</div>

<script>
// Tab switching functionality
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active styles from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById(tabName + 'Section').classList.remove('hidden');
    
    // Add active styles to selected tab
    document.getElementById(tabName + 'Tab').classList.add('border-blue-500', 'text-blue-600');
    document.getElementById(tabName + 'Tab').classList.remove('border-transparent', 'text-gray-500');

    // If switching to stock-out, show batch sub-tab by default
    if (tabName === 'stockOut') {
        switchSubTab('sales');
    }
}

// Sub-tab switching functionality for stock-out
function switchSubTab(subTabName) {
    // Hide all sub-tab contents
    document.querySelectorAll('.subtab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active styles from all sub-tabs
    document.querySelectorAll('.subtab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected sub-tab content
    document.getElementById(subTabName + 'SubSection').classList.remove('hidden');
    
    // Add active styles to selected sub-tab
    document.getElementById(subTabName + 'SubTab').classList.add('border-blue-500', 'text-blue-600');
    document.getElementById(subTabName + 'SubTab').classList.remove('border-transparent', 'text-gray-500');
}

document.addEventListener('DOMContentLoaded', function() {
    // Setup table filtering
    setupTableFiltering('stockInSearch', 'stockInTableBody');
    setupTableFiltering('salesSearch', 'salesTableBody');
    setupTableFiltering('damagedSearch', 'damagedTableBody');
    
    // Initialize with default sort
    sortStockInTable('date_desc');
    sortSalesTable('date_desc');
    sortDamagedTable('date_desc');
});

function setupTableFiltering(searchInputId, tableBodyId) {
    const searchInput = document.getElementById(searchInputId);
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll(`#${tableBodyId} tr`);
            
            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
}

// Stock-In Table Functions
function filterStockInTable() {
    const searchTerm = document.getElementById('stockInSearch').value.toLowerCase();
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const sortValue = document.getElementById('stockInSort').value;
    
    const rows = document.querySelectorAll('#stockInTableBody tr');
    
    rows.forEach(row => {
        const batchNumber = row.getAttribute('data-batch')?.toLowerCase() || '';
        const rowDate = new Date(parseInt(row.getAttribute('data-date')) * 1000);
        let shouldShow = true;
        
        // Search filter
        if (searchTerm && !batchNumber.includes(searchTerm)) {
            shouldShow = false;
        }
        
        // Date range filter
        if (dateFrom) {
            const fromDate = new Date(dateFrom);
            if (rowDate < fromDate) shouldShow = false;
        }
        if (dateTo) {
            const toDate = new Date(dateTo);
            toDate.setHours(23, 59, 59, 999); // End of day
            if (rowDate > toDate) shouldShow = false;
        }
        
        row.style.display = shouldShow ? '' : 'none';
    });
    
    // Apply sorting
    sortStockInTable(sortValue);
}

function sortStockInTable(sortValue = null) {
    if (!sortValue) {
        sortValue = document.getElementById('stockInSort').value;
    }
    
    const tableBody = document.getElementById('stockInTableBody');
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    
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
    
    // Clear and re-append sorted rows
    tableBody.innerHTML = '';
    rows.forEach(row => {
        tableBody.appendChild(row);
    });
}

function resetStockInFilters() {
    document.getElementById('stockInSearch').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    document.getElementById('stockInSort').value = 'date_desc';
    
    const rows = document.querySelectorAll('#stockInTableBody tr');
    rows.forEach(row => {
        row.style.display = '';
    });
    
    sortStockInTable('date_desc');
}

// Batch Stock-Out Table Functions
function filterBatchOutTable() {
    const searchTerm = document.getElementById('batchOutSearch').value.toLowerCase();
    const dateFrom = document.getElementById('batchOutDateFrom').value;
    const dateTo = document.getElementById('batchOutDateTo').value;
    const sortValue = document.getElementById('batchOutSort').value;
    
    const rows = document.querySelectorAll('#batchOutTableBody tr');
    
    rows.forEach(row => {
        const batchNumber = row.getAttribute('data-batch')?.toLowerCase() || '';
        const rowDate = new Date(parseInt(row.getAttribute('data-date')) * 1000);
        let shouldShow = true;
        
        // Search filter
        if (searchTerm && !batchNumber.includes(searchTerm)) {
            shouldShow = false;
        }
        
        // Date range filter
        if (dateFrom) {
            const fromDate = new Date(dateFrom);
            if (rowDate < fromDate) shouldShow = false;
        }
        if (dateTo) {
            const toDate = new Date(dateTo);
            toDate.setHours(23, 59, 59, 999);
            if (rowDate > toDate) shouldShow = false;
        }
        
        row.style.display = shouldShow ? '' : 'none';
    });
    
    sortBatchOutTable(sortValue);
}

function sortBatchOutTable(sortValue = null) {
    if (!sortValue) {
        sortValue = document.getElementById('batchOutSort').value;
    }
    
    const tableBody = document.getElementById('batchOutTableBody');
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    
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
    
    tableBody.innerHTML = '';
    rows.forEach(row => {
        tableBody.appendChild(row);
    });
}

function resetBatchOutFilters() {
    document.getElementById('batchOutSearch').value = '';
    document.getElementById('batchOutDateFrom').value = '';
    document.getElementById('batchOutDateTo').value = '';
    document.getElementById('batchOutSort').value = 'date_desc';
    
    const rows = document.querySelectorAll('#batchOutTableBody tr');
    rows.forEach(row => {
        row.style.display = '';
    });
    
    sortBatchOutTable('date_desc');
}

// Sales Table Functions
function filterSalesTable() {
    const searchTerm = document.getElementById('salesSearch').value.toLowerCase();
    const dateFrom = document.getElementById('salesDateFrom').value;
    const dateTo = document.getElementById('salesDateTo').value;
    const sortValue = document.getElementById('salesSort').value;
    
    const rows = document.querySelectorAll('#salesTableBody tr');
    
    rows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        const rowDate = new Date(parseInt(row.getAttribute('data-date')) * 1000);
        let shouldShow = true;
        
        // Search filter
        if (searchTerm && !rowText.includes(searchTerm)) {
            shouldShow = false;
        }
        
        // Date range filter
        if (dateFrom) {
            const fromDate = new Date(dateFrom);
            if (rowDate < fromDate) shouldShow = false;
        }
        if (dateTo) {
            const toDate = new Date(dateTo);
            toDate.setHours(23, 59, 59, 999);
            if (rowDate > toDate) shouldShow = false;
        }
        
        row.style.display = shouldShow ? '' : 'none';
    });
    
    sortSalesTable(sortValue);
}

function sortSalesTable(sortValue = null) {
    if (!sortValue) {
        sortValue = document.getElementById('salesSort').value;
    }
    
    const tableBody = document.getElementById('salesTableBody');
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    
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
            default:
                return 0;
        }
    });
    
    tableBody.innerHTML = '';
    rows.forEach(row => {
        tableBody.appendChild(row);
    });
}

function resetSalesFilters() {
    document.getElementById('salesSearch').value = '';
    document.getElementById('salesDateFrom').value = '';
    document.getElementById('salesDateTo').value = '';
    document.getElementById('salesSort').value = 'date_desc';
    
    const rows = document.querySelectorAll('#salesTableBody tr');
    rows.forEach(row => {
        row.style.display = '';
    });
    
    sortSalesTable('date_desc');
}

// Damaged Table Functions
function filterDamagedTable() {
    const searchTerm = document.getElementById('damagedSearch').value.toLowerCase();
    const dateFrom = document.getElementById('damagedDateFrom').value;
    const dateTo = document.getElementById('damagedDateTo').value;
    const sortValue = document.getElementById('damagedSort').value;
    
    const rows = document.querySelectorAll('#damagedTableBody tr');
    
    rows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        const rowDate = new Date(parseInt(row.getAttribute('data-date')) * 1000);
        let shouldShow = true;
        
        // Search filter
        if (searchTerm && !rowText.includes(searchTerm)) {
            shouldShow = false;
        }
        
        // Date range filter
        if (dateFrom) {
            const fromDate = new Date(dateFrom);
            if (rowDate < fromDate) shouldShow = false;
        }
        if (dateTo) {
            const toDate = new Date(dateTo);
            toDate.setHours(23, 59, 59, 999);
            if (rowDate > toDate) shouldShow = false;
        }
        
        row.style.display = shouldShow ? '' : 'none';
    });
    
    sortDamagedTable(sortValue);
}

function sortDamagedTable(sortValue = null) {
    if (!sortValue) {
        sortValue = document.getElementById('damagedSort').value;
    }
    
    const tableBody = document.getElementById('damagedTableBody');
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    
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
    
    tableBody.innerHTML = '';
    rows.forEach(row => {
        tableBody.appendChild(row);
    });
}

function resetDamagedFilters() {
    document.getElementById('damagedSearch').value = '';
    document.getElementById('damagedDateFrom').value = '';
    document.getElementById('damagedDateTo').value = '';
    document.getElementById('damagedSort').value = 'date_desc';
    
    const rows = document.querySelectorAll('#damagedTableBody tr');
    rows.forEach(row => {
        row.style.display = '';
    });
    
    sortDamagedTable('date_desc');
}
</script>

<!-- Print Barcode JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
function printProductBarcode(barcode, productName) {
    const userCopies = prompt(`How many barcode labels would you like to print for "${productName}"?`, '1');
    const numCopies = parseInt(userCopies) || 1;

    if (numCopies < 1) return;

    // Create iframe
    const iframe = document.createElement('iframe');
    iframe.style.cssText = 'position:fixed;width:0;height:0;border:0;visibility:hidden;';
    document.body.appendChild(iframe);

    const doc = iframe.contentWindow.document;

    // Build HTML with exact copies
    let htmlContent = '';
    for (let i = 0; i < numCopies; i++) {
        // Truncate product name if too long
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
            // Wait for JsBarcode to load
            function generateBarcodes() {
                if (typeof JsBarcode === 'undefined') {
                    setTimeout(generateBarcodes, 100);
                    return;
                }
                
                // Render all barcodes
                for (let i = 0; i < ${numCopies}; i++) {
                    JsBarcode("#barcode-" + i, "${barcode}", {
                        format: "CODE128",
                        lineColor: "#000000",
                        width: 1.1,
                        height: 35,
                        displayValue: false,
                        margin: 0
                    });
                }
                
                // Auto print after barcodes are rendered
                setTimeout(function() {
                    window.print();
                    setTimeout(function() {
                        if (window.frameElement) {
                            window.frameElement.parentNode.removeChild(window.frameElement);
                        }
                    }, 500);
                }, 300);
            }
            
            // Start the process
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

@endsection