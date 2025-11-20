@extends('dashboards.owner.owner') 
<head>
    <title>Pricing History</title>
</head>
@section('content')

<div class="bg-white p-6 rounded-2xl shadow-md border border-gray-200">
    <div class="flex justify-between items-center mb-6">
        <h2 class="font-semibold text-gray-800 text-base">Pricing History & Sales Comparison</h2>
        <a href="{{ route('inventory-owner-edit', $prodCode) }}" 
            class="flex items-center text-xs text-blue-600 hover:text-blue-800 transition">
            <span class="material-symbols-outlined text-sm mr-1">assignment_return</span>
            Back to Edit
        </a>
    </div>

    @if(count($priceHistory))
        {{-- Sorting Controls --}}
        <div class="flex flex-wrap gap-3 mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-700">Sort by:</span>
                <select id="sortBy" class="text-xs border border-gray-300 rounded px-3 py-1.5 focus:ring-2 focus:ring-blue-100 focus:border-blue-300">
                    <option value="date_desc">Date (Newest First)</option>
                    <option value="date_asc">Date (Oldest First)</option>
                    <option value="sales_desc">Sales (Highest First)</option>
                    <option value="sales_asc">Sales (Lowest First)</option>
                    <option value="revenue_desc">Revenue (Highest First)</option>
                    <option value="revenue_asc">Revenue (Lowest First)</option>
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
                </select>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <p class="text-xs text-blue-700 font-medium">Total Periods</p>
                <p class="text-lg font-bold text-blue-900">{{ count($priceHistory) }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <p class="text-xs text-green-700 font-medium">Best Selling Price</p>
                <p class="text-lg font-bold text-green-900">₱{{ number_format(collect($priceHistory)->max('old_selling_price'), 2) }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <p class="text-xs text-purple-700 font-medium">Highest Revenue</p>
                <p class="text-lg font-bold text-purple-900">₱{{ number_format(collect($priceHistory)->max('total_sales'), 2) }}</p>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                <p class="text-xs text-orange-700 font-medium">Most Items Sold</p>
                <p class="text-lg font-bold text-orange-900">{{ collect($priceHistory)->max('total_sold') }}</p>
            </div>
        </div>

        {{-- Summary Table --}}
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-xs text-gray-700 border border-gray-200 rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-blue-50 text-gray-700 text-[11px] uppercase tracking-wider">
                        <th class="p-3 border cursor-pointer sortable" data-sort="date">
                            Effective Period
                            <span class="material-symbols-outlined text-xs ml-1">unfold_more</span>
                        </th>
                        <th class="p-3 border">Cost Price</th>
                        <th class="p-3 border cursor-pointer sortable" data-sort="selling_price">
                            Selling Price
                            <span class="material-symbols-outlined text-xs ml-1">unfold_more</span>
                        </th>
                        <th class="p-3 border cursor-pointer sortable" data-sort="items_sold">
                            Items Sold
                            <span class="material-symbols-outlined text-xs ml-1">unfold_more</span>
                        </th>
                        <th class="p-3 border cursor-pointer sortable" data-sort="total_sales">
                            Total Sales
                            <span class="material-symbols-outlined text-xs ml-1">unfold_more</span>
                        </th>
                        <th class="p-3 border">Profit Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="priceHistoryTable">
                    @foreach($priceHistory as $p)
                        @php
                            $profitMargin = $p->old_selling_price - $p->old_cost_price;
                            $marginPercentage = $p->old_cost_price > 0 ? ($profitMargin / $p->old_cost_price) * 100 : 0;
                            
                            // Calculate days difference as whole number
                            $daysCount = \Carbon\Carbon::parse($p->effective_from)->diffInDays($p->effective_to);
                        @endphp
                        <tr class="hover:bg-gray-50 transition price-history-row" 
                            data-date="{{ \Carbon\Carbon::parse($p->effective_to)->timestamp }}"
                            data-selling-price="{{ $p->old_selling_price }}"
                            data-items-sold="{{ $p->total_sold }}"
                            data-total-sales="{{ $p->total_sales }}">
                            <td class="border p-3 text-center">
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
                            <td class="border p-3 text-center text-green-600 font-medium">₱{{ number_format($p->old_cost_price, 2) }}</td>
                            <td class="border p-3 text-center text-blue-600 font-medium">₱{{ number_format($p->old_selling_price, 2) }}</td>
                            <td class="border p-3 text-center">
                                <span class="font-medium {{ $p->total_sold > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $p->total_sold }}
                                </span>
                            </td>
                            <td class="border p-3 text-center font-semibold {{ $p->total_sales > 0 ? 'text-purple-600' : 'text-gray-400' }}">
                                ₱{{ number_format($p->total_sales, 2) }}
                            </td>
                            <td class="border p-3 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-xs font-medium {{ $marginPercentage >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        ₱{{ number_format($profitMargin, 2) }}
                                    </span>
                                    <span class="text-[10px] {{ $marginPercentage >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                        ({{ number_format($marginPercentage, 1) }}%)
                                    </span>
                                </div>
                            </td>
                        </tr>
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
    const rows = Array.from(document.querySelectorAll('.price-history-row'));


    // Sorting functionality
    sortSelect.addEventListener('change', function() {
        sortAndFilterRows();
    });

    // Filtering functionality
    filterSelect.addEventListener('change', function() {
        sortAndFilterRows();
    });



    function sortAndFilterRows() {
        const sortValue = sortSelect.value;
        const filterValue = filterSelect.value;
        
        let filteredRows = rows.filter(row => {
            const itemsSold = parseInt(row.getAttribute('data-items-sold'));
            
            switch(filterValue) {
                case 'with_sales':
                    return itemsSold > 0;
                case 'no_sales':
                    return itemsSold === 0;
                default:
                    return true;
            }
        });

        // Sort the filtered rows
        filteredRows.sort((a, b) => {
            switch(sortValue) {
                case 'date_desc':
                    return parseInt(b.getAttribute('data-date')) - parseInt(a.getAttribute('data-date'));
                case 'date_asc':
                    return parseInt(a.getAttribute('data-date')) - parseInt(b.getAttribute('data-date'));
                case 'sales_desc':
                    return parseInt(b.getAttribute('data-items-sold')) - parseInt(a.getAttribute('data-items-sold'));
                case 'sales_asc':
                    return parseInt(a.getAttribute('data-items-sold')) - parseInt(b.getAttribute('data-items-sold'));
                case 'revenue_desc':
                    return parseFloat(b.getAttribute('data-total-sales')) - parseFloat(a.getAttribute('data-total-sales'));
                case 'revenue_asc':
                    return parseFloat(a.getAttribute('data-total-sales')) - parseFloat(b.getAttribute('data-total-sales'));
                case 'selling_price_desc':
                    return parseFloat(b.getAttribute('data-selling-price')) - parseFloat(a.getAttribute('data-selling-price'));
                case 'selling_price_asc':
                    return parseFloat(a.getAttribute('data-selling-price')) - parseFloat(b.getAttribute('data-selling-price'));
                default:
                    return 0;
            }
        });

        // Clear and repopulate table
        tableBody.innerHTML = '';
        filteredRows.forEach(row => tableBody.appendChild(row));
    }

    // Initialize
    sortAndFilterRows();
});
</script>

@endsection