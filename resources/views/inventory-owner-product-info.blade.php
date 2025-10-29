@extends('dashboards.owner.owner')
<head>
    <title>Product Information</title>
</head>
@section('content')

<div class="px-4">
    @livewire('expiration-container')
</div>

<div class="max-w-6xl mx-auto py-4"> 

    <!-- Product Information -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <!-- Back Button -->
        <div class="mb-4 mt-2">
            <a href="{{ route('inventory-owner') }}" 
            class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                <span class="material-symbols-outlined text-sm mr-1">assignment_return</span>
                Back
            </a>
        </div>
        
        <div class="flex flex-col items-center text-center mb-6">
            <!-- Product Image -->
            <div class="flex justify-center mb-4">
                @if($product->prod_image)
                    <img src="{{ $product->prod_image && file_exists(public_path('storage/' . $product->prod_image)) 
                                        ? asset('storage/' . $product->prod_image) 
                                        : asset('assets/no-product-image.png') }}" 
                         alt="{{ $product->name }}" 
                         class="w-40 h-40 object-cover rounded-lg shadow-md border">
                @else
                    <div class="w-40 h-40 flex items-center justify-center bg-gray-200 text-gray-500 rounded-lg shadow-md border">
                        No Image
                    </div>
                @endif
            </div>

            <!-- Product Name and Stock Badge -->
            <div class="flex flex-col items-center gap-3">
                <h2 class="text-2xl font-semibold leading-snug break-words max-w-2xl">
                    {{ $product->name }}
                </h2>
                <!-- Stock Badge -->
                @if($currentStock <= 0)
                    <span class="px-3 py-1 text-sm bg-red-100 text-red-700 font-medium rounded-full shadow-sm">
                        Out of Stock
                    </span>
                @elseif($currentStock <= $product->stock_limit)
                    <span class="px-3 py-1 text-sm bg-yellow-100 text-yellow-700 font-medium rounded-full shadow-sm">
                        Low Stock
                    </span>
                @else
                    <span class="px-3 py-1 text-sm bg-green-100 text-green-700 font-medium rounded-full shadow-sm">
                        In Stock
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column: Stock Summary Cards -->
            <div class="space-y-6">
                <!-- Stock Summary Cards -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <p class="text-xs text-blue-700 font-medium">Current Stock</p>
                        <p class="text-xl font-bold text-blue-900">{{ $currentStock }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                        <p class="text-xs text-green-700 font-medium">Total Stock In</p>
                        <p class="text-xl font-bold text-green-900">{{ $totalStockIn }}</p>
                    </div>
                    <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                        <p class="text-xs text-orange-700 font-medium">Total Sold</p>
                        <p class="text-xl font-bold text-orange-900">{{ $totalStockOutSold }}</p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                        <p class="text-xs text-red-700 font-medium">Damaged/Expired</p>
                        <p class="text-xl font-bold text-red-900">{{ $totalStockOutDamaged }}</p>
                    </div>
                </div>

                <!-- Revenue & Performance Cards -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                        <p class="text-xs text-purple-700 font-medium">Total Revenue</p>
                        <p class="text-xl font-bold text-purple-900">₱{{ number_format($totalRevenue, 2) }}</p>
                    </div>
                    <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                        <p class="text-xs text-indigo-700 font-medium">Turnover Rate</p>
                        <p class="text-xl font-bold text-indigo-900">{{ number_format($turnoverRate, 1) }}%</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Product Details -->
            <div class="space-y-6">
                <!-- Details Grid -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Product Details</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-gray-500">Barcode</p>
                            <p class="font-medium">{{ $product->barcode }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Unit</p>
                            <p class="font-medium">{{ $product->unit }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Cost Price</p>
                            <p class="font-medium">₱{{ number_format($product->cost_price, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Stock Limit</p>
                            <p class="font-medium">{{ $product->stock_limit }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-gray-500">Selling Price</p>
                            <p class="font-semibold text-xl">₱{{ number_format($product->selling_price, 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Description</h3>
                    <p class="text-sm text-gray-600">{{ $product->description ?? 'No description available' }}</p>
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
                <button id="comparisonTab" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" onclick="switchTab('comparison')">
                    Stock Comparison
                </button>
            </nav>
        </div>
    </div>

    <!-- Stock-In History Section -->
    <div id="stockInSection" class="tab-content">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-md font-semibold">Stock-In History</h2>
            <div class="flex gap-2 flex-wrap">
                <select id="stockInSort" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
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
                                $totalBatchQuantity = $batches->sum('stock');
                                
                                // FIXED: Use diffInDays() for whole numbers and ensure proper calculation
                                if ($firstBatch->expiration_date) {
                                    $expirationDate = \Carbon\Carbon::parse($firstBatch->expiration_date);
                                    $now = \Carbon\Carbon::now();
                                    
                                    if ($expirationDate->isPast()) {
                                        // If expired, show days since expiration
                                        $expiryDays = -$expirationDate->diffInDays($now);
                                    } else {
                                        // If not expired, show days until expiration
                                        $expiryDays = $now->diffInDays($expirationDate);
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
                                        @php
                                            $roundedDays = (int)round($expiryDays);
                                        @endphp
                                        <span class="{{ $roundedDays <= 0 ? 'text-red-600 font-medium' : 'text-blue-600 font-medium' }}">
                                            @if($roundedDays > 0)
                                                {{ $roundedDays }} days left
                                            @else
                                                Expired {{ abs($roundedDays) }} days ago
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
                    <button id="batchSubTab" class="subtab-button py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600" onclick="switchSubTab('batch')">
                        Batch
                    </button>
                    <button id="salesSubTab" class="subtab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" onclick="switchSubTab('sales')">
                        Sales History
                    </button>
                    <button id="damagedSubTab" class="subtab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" onclick="switchSubTab('damaged')">
                        Damaged/Expired Items
                    </button>
                </nav>
            </div>
        </div>

        <!-- Batch Stock-Out Sub-Tab -->
        <div id="batchSubSection" class="subtab-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-md font-semibold">Stock-Out By Batch</h3>
                <div class="flex gap-2 flex-wrap">
                    <select id="batchOutSort" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                        <option value="date_desc">Date (Newest First)</option>
                        <option value="date_asc">Date (Oldest First)</option>
                        <option value="batch_desc">Batch (Newest First)</option>
                        <option value="batch_asc">Batch (Oldest First)</option>
                        <option value="quantity_desc">Quantity (High to Low)</option>
                        <option value="quantity_asc">Quantity (Low to High)</option>
                    </select>
                    <input type="text" id="batchOutSearch" placeholder="Search batch..." class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <input type="date" id="batchOutDateFrom" placeholder="From Date" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <input type="date" id="batchOutDateTo" placeholder="To Date" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <button onclick="filterBatchOutTable()" class="text-xs bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition">Apply Filter</button>
                    <button onclick="resetBatchOutFilters()" class="text-xs bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 transition">Reset</button>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border border-gray-100">
                        <thead class="bg-gray-100 text-gray-700 text-sm">
                            <tr>
                                <th class="px-4 py-3 border">Batch Number</th>
                                <th class="px-4 py-3 border">Date Out</th>
                                <th class="px-4 py-3 border">Quantity Out</th>
                                <th class="px-4 py-3 border">Type</th>
                                <th class="px-4 py-3 border">Reference</th>
                                <th class="px-4 py-3 border">Sold By</th>
                            </tr>
                        </thead>
                        <tbody id="batchOutTableBody">
                            @forelse ($manualBatchStockOut as $stockOut)
                                <tr class="hover:bg-gray-50 text-sm" data-batch="{{ $stockOut->batch_number }}" data-date="{{ \Carbon\Carbon::parse($stockOut->date)->timestamp }}" data-quantity="{{ $stockOut->quantity_out }}">
                                    <td class="px-4 py-3 border text-center">{{ $stockOut->batch_number }}</td>
                                    <td class="px-4 py-3 border text-center">
                                        {{ \Carbon\Carbon::parse($stockOut->date)->format('M j, Y') }}
                                    </td>
                                    <td class="px-4 py-3 border text-center">{{ $stockOut->quantity_out }}</td>
                                    <td class="px-4 py-3 border text-center">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $stockOut->type === 'sale' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($stockOut->type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 border text-center font-mono">
                                        @if($stockOut->type === 'sale')
                                            {{ $stockOut->reference }}
                                        @else
                                            {{ $stockOut->reference }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 border text-center">{{ $stockOut->sold_by ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-gray-500">No batch stock-out records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($manualBatchStockOut->count() > 0)
                        <tfoot class="bg-gray-50">
                            <tr class="text-sm font-semibold">
                                <td colspan="2" class="px-4 py-3 border text-right">Total</td>
                                <td class="px-4 py-3 border text-center">{{ $manualBatchStockOut->sum('quantity_out') }}</td>
                                <td colspan="3" class="px-4 py-3 border text-center">—</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Sales History Sub-Tab -->
        <div id="salesSubSection" class="subtab-content hidden">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-md font-semibold">Sales History</h3>
                <div class="flex gap-2 flex-wrap">
                    <select id="salesSort" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                        <option value="date_desc">Date (Newest First)</option>
                        <option value="date_asc">Date (Oldest First)</option>
                        <option value="quantity_desc">Quantity (High to Low)</option>
                        <option value="quantity_asc">Quantity (Low to High)</option>
                        <option value="amount_desc">Amount (High to Low)</option>
                    </select>
                    <input type="text" id="salesSearch" placeholder="Search receipt..." class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <input type="date" id="salesDateFrom" placeholder="From Date" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <input type="date" id="salesDateTo" placeholder="To Date" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <button onclick="filterSalesTable()" class="text-xs bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition">Apply Filter</button>
                    <button onclick="resetSalesFilters()" class="text-xs bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 transition">Reset</button>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border border-gray-100">
                        <thead class="bg-gray-100 text-gray-700 text-sm">
                            <tr>
                                <th class="px-4 py-3 border">Receipt ID</th>
                                <th class="px-4 py-3 border">Date Sold</th>
                                <th class="px-4 py-3 border">Quantity Sold</th>
                                <th class="px-4 py-3 border">Unit Price</th>
                                <th class="px-4 py-3 border">Total Amount</th>
                                <th class="px-4 py-3 border">Sold By</th>
                            </tr>
                        </thead>
                        <tbody id="salesTableBody">
                            @forelse ($stockOutSalesHistory as $sale)
                                <tr class="hover:bg-gray-50 text-sm" data-date="{{ \Carbon\Carbon::parse($sale->receipt_date)->timestamp }}" data-quantity="{{ $sale->quantity_sold }}" data-amount="{{ $sale->total_amount }}">
                                    <td class="px-4 py-3 border text-center font-mono">{{ $sale->receipt_id }}</td>
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
                                    <td colspan="6" class="text-center py-6 text-gray-500">No sales records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($stockOutSalesHistory->count() > 0)
                        <tfoot class="bg-gray-50">
                            <tr class="text-sm font-semibold">
                                <td colspan="2" class="px-4 py-3 border text-right">Total</td>
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
                        <option value="date_desc">Date (Newest First)</option>
                        <option value="date_asc">Date (Oldest First)</option>
                        <option value="quantity_desc">Quantity (High to Low)</option>
                        <option value="quantity_asc">Quantity (Low to High)</option>
                    </select>
                    <input type="text" id="damagedSearch" placeholder="Search reason..." class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <input type="date" id="damagedDateFrom" placeholder="From Date" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <input type="date" id="damagedDateTo" placeholder="To Date" class="text-xs border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-100">
                    <button onclick="filterDamagedTable()" class="text-xs bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition">Apply Filter</button>
                    <button onclick="resetDamagedFilters()" class="text-xs bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 transition">Reset</button>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border border-gray-100">
                        <thead class="bg-gray-100 text-gray-700 text-sm">
                            <tr>
                                <th class="px-4 py-3 border">Date</th>
                                <th class="px-4 py-3 border">Quantity</th>
                                <th class="px-4 py-3 border">Reason</th>
                                <th class="px-4 py-3 border">Reference ID</th>
                            </tr>
                        </thead>
                        <tbody id="damagedTableBody">
                            @forelse ($stockOutDamagedHistory as $damaged)
                                <tr class="hover:bg-gray-50 text-sm" data-date="{{ \Carbon\Carbon::parse($damaged->damaged_date)->timestamp }}" data-quantity="{{ $damaged->damaged_quantity }}">
                                    <td class="px-4 py-3 border text-center">
                                        {{ \Carbon\Carbon::parse($damaged->damaged_date)->format('M j, Y') }}
                                    </td>
                                    <td class="px-4 py-3 border text-center">{{ $damaged->damaged_quantity }}</td>
                                    <td class="px-4 py-3 border text-center">
                                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">
                                            {{ $damaged->damaged_reason }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 border text-center">DAMAGED-{{ $damaged->damaged_id }}</td>
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
                                <td colspan="1" class="px-4 py-3 border text-right">Total</td>
                                <td class="px-4 py-3 border text-center">{{ $totalStockOutDamaged }}</td>
                                <td colspan="2" class="px-4 py-3 border text-center">—</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Comparison Section -->
    <div id="comparisonSection" class="tab-content hidden">
        <!-- Stock Comparison Table -->
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
    </div>
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
        switchSubTab('batch');
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
    setupTableFiltering('batchOutSearch', 'batchOutTableBody');
    setupTableFiltering('salesSearch', 'salesTableBody');
    setupTableFiltering('damagedSearch', 'damagedTableBody');
    
    // Initialize with default sort
    sortStockInTable('date_desc');
    sortBatchOutTable('date_desc');
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

@endsection