@extends('dashboards.owner.owner') 
<head>
    <title>Price History - {{ $productName }}</title>
</head>
@section('content')

<div class="bg-white p-6 rounded-2xl shadow-md border border-gray-200">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="font-semibold text-gray-800 text-base">Price History</h2>
            <p class="text-xs text-gray-600 mt-1">Product: {{ $productName }}</p>
        </div>
        <a href="{{ route('inventory-owner-edit', $prodCode) }}" 
            class="flex items-center text-xs text-blue-600 hover:text-blue-800 transition">
            <span class="material-symbols-outlined text-sm mr-1">assignment_return</span>
            Back to Edit
        </a>
    </div>

    @if(count($priceHistory) > 0 || count($currentPrice) > 0)
        {{-- Sorting Controls --}}
        <div class="flex flex-wrap gap-3 mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-700">Sort by:</span>
                <select id="sortBy" class="text-xs border border-gray-300 rounded px-3 py-1.5 focus:ring-2 focus:ring-blue-100 focus:border-blue-300">
                    <option value="date_desc">Date (Newest First)</option>
                    <option value="date_asc">Date (Oldest First)</option>
                    <option value="sales_desc">Item Sales (Highest First)</option>
                    <option value="sales_asc">Item Sales (Lowest First)</option>
                    <option value="selling_price_desc">Selling Price (Highest First)</option>
                    <option value="selling_price_asc">Selling Price (Lowest First)</option>
                </select>
            </div>
            
            <div class="flex items-center gap-2 ml-auto">
                <span class="text-xs font-medium text-gray-700">Filter by:</span>
                <select id="filterBy" class="text-xs border border-gray-300 rounded px-3 py-1.5 focus:ring-2 focus:ring-blue-100 focus:border-blue-300">
                    <option value="all">All Periods</option>
                    <option value="with_sales">With Sales Only</option>
                    <option value="no_sales">No Sales Only</option>
                    <option value="current">Current Price Only</option>
                </select>
            </div>
        </div>

        {{-- Summary Cards --}}
        @php
            $allPrices = collect($priceHistory)->concat(collect($currentPrice));
            
            $priceGroups = $allPrices->groupBy(function($item) {
                return $item->old_selling_price ?? $item->selling_price;
            });
            
            $bestSellingPriceData = $priceGroups->map(function($group, $price) {
                return [
                    'price' => $price,
                    'total_sold' => $group->sum(function($item) {
                        return $item->batch_sold ?? 0;
                    })
                ];
            })->sortByDesc('total_sold')->first();
            
            $bestSellingPrice = $bestSellingPriceData['price'] ?? 0;
            $bestSellingQuantity = $bestSellingPriceData['total_sold'] ?? 0;
            
            $periodGroups = collect($priceHistory)->groupBy('price_history_id')
                ->map(function($group) {
                    return $group->sum('batch_sales');
                });
            
            $currentPeriodRevenue = collect($currentPrice)->sum('batch_sales');
            $periodGroups->push($currentPeriodRevenue);
            
            $highestRevenue = $periodGroups->max();
            $itemsLeftToSell = collect($currentPrice)->sum('batch_available');
            $totalPeriods = collect($priceHistory)->groupBy('price_history_id')->count() + (count($currentPrice) > 0 ? 1 : 0);
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <p class="text-xs text-blue-700 font-medium">Total Price Periods</p>
                <p class="text-lg font-bold text-blue-900">{{ $totalPeriods }}</p>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <p class="text-xs text-green-700 font-medium">Best Selling Price</p>
                <p class="text-lg font-bold text-green-900">₱{{ number_format($bestSellingPrice, 2) }}</p>
                <p class="text-[10px] text-green-600 mt-1">{{ $bestSellingQuantity }} items sold at this price</p>
            </div>
            
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <p class="text-xs text-purple-700 font-medium">Best-Earning Price Period</p>
                <p class="text-lg font-bold text-purple-900">₱{{ number_format($highestRevenue, 2) }}</p>
            </div>
            
            <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                <p class="text-xs text-orange-700 font-medium">Remaining Stock</p>
                <p class="text-lg font-bold text-orange-900">{{ $itemsLeftToSell }}</p>
            </div>
        </div>

        {{-- Main Table --}}
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-xs text-gray-700 border border-gray-200 rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-100 border border-red-200 text-gray-700 text-[11px] uppercase tracking-wider">
                        <th class="p-3">Effective Period</th>
                        <th class="p-3">Cost Price</th>
                        <th class="p-3">Selling Price</th>
                        <th class="p-3">Batch Number</th>
                        <th class="p-3">Items Sold</th>
                        <th class="p-3">Damaged Items</th>
                        <th class="p-3">Total Sales</th>
                        <th class="p-3">Profit Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="priceHistoryTable">
                    {{-- Current Price Section --}}
                    @if(count($currentPrice) > 0)
                        @php
                            $groupedCurrent = collect($currentPrice)->groupBy(function($item) {
                                return $item->cost_price . '-' . $item->selling_price . '-' . $item->effective_from;
                            });
                        @endphp
                        
                        @foreach($groupedCurrent as $priceGroup => $batches)
                            @php
                                $firstBatch = $batches->first();
                                $batchCount = $batches->count();
                                $daysCount = \Carbon\Carbon::parse($firstBatch->effective_from)->diffInDays(now());
                                $totalSold = $batches->sum('batch_sold');
                                $totalDamaged = $batches->sum('batch_damaged');
                                $totalSales = $batches->sum('batch_sales');
                                $profitMargin = $firstBatch->selling_price - $firstBatch->cost_price;
                                $marginPercentage = $firstBatch->cost_price > 0 ? ($profitMargin / $firstBatch->cost_price) * 100 : 0;
                                $groupId = 'current-group-' . $loop->index;
                            @endphp
                            
                            @foreach($batches as $index => $current)
                                <tr class="hover:bg-green-50 transition price-history-row bg-green-50" 
                                    data-group-id="{{ $groupId }}"
                                    data-is-first="{{ $index === 0 ? 'true' : 'false' }}"
                                    data-date="{{ \Carbon\Carbon::parse($current->effective_from)->timestamp }}"
                                    data-selling-price="{{ $current->selling_price }}"
                                    data-items-sold="{{ $totalSold }}"
                                    data-total-sales="{{ $totalSales }}"
                                    data-is-current="true">
                                    
                                    @if($index === 0)
                                        <td class="border p-3 text-center bg-green-100" rowspan="{{ $batchCount }}">
                                            <div class="flex flex-col items-center">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold bg-green-600 text-white mb-2">
                                                    CURRENT PRICE
                                                </span>
                                                <span class="text-[10px] text-gray-500">From</span>
                                                <span class="text-xs font-medium">{{ \Carbon\Carbon::parse($current->effective_from)->format('M d, Y') }}</span>
                                                <span class="text-[10px] text-gray-500 mt-1">To</span>
                                                <span class="text-xs font-medium text-green-700">Present</span>
                                                <span class="text-[10px] text-gray-400 mt-1">
                                                    {{ (int)$daysCount }} {{ $daysCount === 1 ? 'day' : 'days' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="border p-3 text-center text-green-700 font-medium bg-green-100" rowspan="{{ $batchCount }}">
                                            ₱{{ number_format($current->cost_price, 2) }}
                                        </td>
                                        <td class="border p-3 text-center text-green-700 font-bold bg-green-100" rowspan="{{ $batchCount }}">
                                            ₱{{ number_format($current->selling_price, 2) }}
                                        </td>
                                    @endif
                                    
                                    <td class="border p-3 text-center font-medium">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded {{ $current->is_sold_out ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }} text-[10px]">
                                                {{ $current->batch_number }}
                                            </span>
                                            @if($current->is_sold_out_in_period)
                                                <span class="inline-flex items-center px-1.5 py-0.5 text-gray-500 text-[10px]">
                                                    sold out
                                                </span>
                                            @elseif($current->batch_available > 0 && !$current->is_sold_out)
                                                <div class="text-[10px] text-gray-500">
                                                    {{ $current->batch_available }} available
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="border p-3 text-center">
                                        <span class="font-medium {{ $current->batch_sold > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                            {{ $current->batch_sold }}
                                        </span>
                                    </td>
                                    <td class="border p-3 text-center">
                                        <span class="font-medium {{ $current->batch_damaged > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                            {{ $current->batch_damaged }}
                                        </span>
                                    </td>
                                    <td class="border p-3 text-center font-semibold {{ $current->batch_sales > 0 ? 'text-purple-600' : 'text-gray-400' }}">
                                        ₱{{ number_format($current->batch_sales, 2) }}
                                    </td>
                                    
                                    @if($index === 0)
                                        <td class="border p-3 text-center bg-green-100" rowspan="{{ $batchCount }}">
                                            <div class="flex flex-col items-center">
                                                <span class="text-xs font-medium {{ $marginPercentage >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                                    ₱{{ number_format($profitMargin, 2) }}
                                                </span>
                                                <span class="text-[10px] {{ $marginPercentage >= 0 ? 'text-green-600' : 'text-red-500' }}">
                                                    ({{ number_format($marginPercentage, 1) }}%)
                                                </span>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach
                    @endif

                    {{-- Historical Prices Section --}}
                    @php
                        $groupedHistory = collect($priceHistory)->groupBy('price_history_id');
                    @endphp
                    
                    @foreach($groupedHistory as $historyId => $batches)
                        @php
                            $firstBatch = $batches->first();
                            $batchCount = $batches->count();
                            $daysCount = \Carbon\Carbon::parse($firstBatch->effective_from)->diffInDays($firstBatch->effective_to);
                            $totalSold = $batches->sum('batch_sold');
                            $totalDamaged = $batches->sum('batch_damaged');
                            $totalSales = $batches->sum('batch_sales');
                            $profitMargin = $firstBatch->old_selling_price - $firstBatch->old_cost_price;
                            $marginPercentage = $firstBatch->old_cost_price > 0 ? ($profitMargin / $firstBatch->old_cost_price) * 100 : 0;
                            $groupId = 'history-group-' . $historyId;
                        @endphp
                        
                        @foreach($batches as $index => $p)
                            <tr class="hover:bg-gray-50 transition price-history-row" 
                                data-group-id="{{ $groupId }}"
                                data-is-first="{{ $index === 0 ? 'true' : 'false' }}"
                                data-date="{{ \Carbon\Carbon::parse($p->effective_to)->timestamp }}"
                                data-selling-price="{{ $p->old_selling_price }}"
                                data-items-sold="{{ $totalSold }}"
                                data-total-sales="{{ $totalSales }}"
                                data-is-current="false">
                                
                                @if($index === 0)
                                    <td class="border p-3 text-center" rowspan="{{ $batchCount }}">
                                        <div class="flex flex-col items-center">
                                            <span class="text-[10px] text-gray-500">From</span>
                                            <span class="text-xs font-medium">{{ \Carbon\Carbon::parse($p->effective_from)->format('M d, Y') }}</span>
                                            <span class="text-[10px] text-gray-500 mt-1">To</span>
                                            <span class="text-xs font-medium">{{ \Carbon\Carbon::parse($p->effective_to)->format('M d, Y') }}</span>
                                            <span class="text-[10px] text-gray-400 mt-1">
                                                {{ (int)$daysCount }} {{ $daysCount === 1 ? 'day' : 'days' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="border p-3 text-center text-green-600 font-medium" rowspan="{{ $batchCount }}">
                                        ₱{{ number_format($p->old_cost_price, 2) }}
                                    </td>
                                    <td class="border p-3 text-center text-blue-600 font-medium" rowspan="{{ $batchCount }}">
                                        ₱{{ number_format($p->old_selling_price, 2) }}
                                    </td>
                                @endif
                                
                                <td class="border p-3 text-center font-medium">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="inline-flex items-center px-2 py-1 rounded {{ $p->is_sold_out ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }} text-[10px]">
                                            {{ $p->batch_number }}
                                        </span>
                                        @if($p->is_sold_out_in_period)
                                            <span class="inline-flex items-center px-1.5 py-0.5 text-gray-500 text-[10px]">
                                                sold out
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="border p-3 text-center">
                                    <span class="font-medium {{ $p->batch_sold > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                        {{ $p->batch_sold }}
                                    </span>
                                </td>
                                <td class="border p-3 text-center">
                                    <span class="font-medium {{ $p->batch_damaged > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                        {{ $p->batch_damaged }}
                                    </span>
                                </td>
                                <td class="border p-3 text-center font-semibold {{ $p->batch_sales > 0 ? 'text-purple-600' : 'text-gray-400' }}">
                                    ₱{{ number_format($p->batch_sales, 2) }}
                                </td>
                                
                                @if($index === 0)
                                    <td class="border p-3 text-center" rowspan="{{ $batchCount }}">
                                        <div class="flex flex-col items-center">
                                            <span class="text-xs font-medium {{ $marginPercentage >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                ₱{{ number_format($profitMargin, 2) }}
                                            </span>
                                            <span class="text-[10px] {{ $marginPercentage >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                                ({{ number_format($marginPercentage, 1) }}%)
                                            </span>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

    @else
        <p class="text-gray-500 text-xs text-center py-10 italic">
            No pricing history found for this product.
        </p>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('sortBy');
    const filterSelect = document.getElementById('filterBy');
    const tableBody = document.getElementById('priceHistoryTable');
    
    // Store the original order and structure
    const allRows = Array.from(document.querySelectorAll('.price-history-row'));
    
    // Group rows by their group ID
    const groupedRows = {};
    allRows.forEach(row => {
        const groupId = row.getAttribute('data-group-id');
        if (!groupedRows[groupId]) {
            groupedRows[groupId] = [];
        }
        groupedRows[groupId].push(row);
    });

    sortSelect.addEventListener('change', sortAndFilterRows);
    filterSelect.addEventListener('change', sortAndFilterRows);

    function sortAndFilterRows() {
        const sortValue = sortSelect.value;
        const filterValue = filterSelect.value;
        
        // Get first row of each group for comparison and filtering
        const groupKeys = Object.keys(groupedRows);
        
        // Filter groups
        let filteredGroupKeys = groupKeys.filter(groupKey => {
            const firstRow = groupedRows[groupKey][0];
            const itemsSold = parseInt(firstRow.getAttribute('data-items-sold'));
            const isCurrent = firstRow.getAttribute('data-is-current') === 'true';
            
            switch(filterValue) {
                case 'with_sales':
                    return itemsSold > 0;
                case 'no_sales':
                    return itemsSold === 0;
                case 'current':
                    return isCurrent;
                default:
                    return true;
            }
        });

        // Sort groups based on first row data
        filteredGroupKeys.sort((keyA, keyB) => {
            const rowA = groupedRows[keyA][0];
            const rowB = groupedRows[keyB][0];
            
            const dateA = parseInt(rowA.getAttribute('data-date'));
            const dateB = parseInt(rowB.getAttribute('data-date'));
            const isCurrentA = rowA.getAttribute('data-is-current') === 'true';
            const isCurrentB = rowB.getAttribute('data-is-current') === 'true';
            const soldA = parseInt(rowA.getAttribute('data-items-sold'));
            const soldB = parseInt(rowB.getAttribute('data-items-sold'));
            const salesA = parseFloat(rowA.getAttribute('data-total-sales'));
            const salesB = parseFloat(rowB.getAttribute('data-total-sales'));
            const priceA = parseFloat(rowA.getAttribute('data-selling-price'));
            const priceB = parseFloat(rowB.getAttribute('data-selling-price'));
            
            switch(sortValue) {
                case 'date_desc':
                    // Current price should always be first (most recent)
                    if (isCurrentA && !isCurrentB) return -1;
                    if (!isCurrentA && isCurrentB) return 1;
                    return dateB - dateA;
                    
                case 'date_asc':
                    // Current price should always be last (most recent)
                    if (isCurrentA && !isCurrentB) return 1;
                    if (!isCurrentA && isCurrentB) return -1;
                    return dateA - dateB;
                    
                case 'sales_desc':
                    return soldB - soldA;
                case 'sales_asc':
                    return soldA - soldB;
                case 'selling_price_desc':
                    return priceB - priceA;
                case 'selling_price_asc':
                    return priceA - priceB;
                default:
                    return 0;
            }
        });

        // Clear and rebuild table
        tableBody.innerHTML = '';
        
        // Append groups in sorted order
        filteredGroupKeys.forEach(groupKey => {
            const rows = groupedRows[groupKey];
            rows.forEach(row => {
                tableBody.appendChild(row.cloneNode(true));
            });
        });
    }

    // Initial sort and filter
    sortAndFilterRows();
});
</script>

@endsection