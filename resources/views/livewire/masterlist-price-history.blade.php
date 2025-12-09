<div class="p-3 bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">
    <div class="space-y-3">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

            <!-- Total Sold Card -->
            <div class="bg-white rounded-lg shadow-sm border border-emerald-100 p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="text-xs font-bold text-emerald-600 uppercase tracking-wide mb-2">Total Sold</div>
                        <div class="text-3xl font-semibold text-emerald-700 mb-1">{{ number_format($history->sum('batch_sold_in_period')) }}</div>
                        <div class="text-sm text-emerald-400">Units</div>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-green-700">shopping_bag</span>
                    </div>
                </div>
                <div class="h-1 bg-emerald-50 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-300 rounded-full" style="width: 85%"></div>
                </div>
            </div>

            <!-- Total Damaged Card -->
            <div class="bg-white rounded-lg shadow-sm border border-red-100 p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="text-xs font-bold text-red-600 uppercase tracking-wide mb-2">Total Damaged</div>
                        <div class="text-3xl font-semibold text-red-700 mb-1">{{ number_format($history->sum('batch_damaged_in_period')) }}</div>
                        <div class="text-sm text-red-400">Units</div>
                    </div>
                    <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-red-700">gpp_maybe</span>
                    </div>
                </div>
                <div class="h-1 bg-red-50 rounded-full overflow-hidden">
                    <div class="h-full bg-red-300 rounded-full" style="width: 15%"></div>
                </div>
            </div>

            <!-- Total Revenue Card -->
            <div class="bg-white rounded-lg shadow-sm border border-amber-100 p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="text-xs font-bold text-amber-600 uppercase tracking-wide mb-2">Total Revenue</div>
                        <div class="text-3xl font-semibold text-amber-700 mb-1">₱{{ number_format($history->sum('total_sales_in_period'), 2) }}</div>
                        <div class="text-sm text-amber-400">PHP</div>
                    </div>
                    <div class="w-12 h-12 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-orange-700">money_bag</span>
                    </div>
                </div>
                <div class="h-1 bg-amber-50 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-300 rounded-full" style="width: 90%"></div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div wire:poll.live="pollList" class="hidden"></div>
        <div class="bg-gradient-to-br from-slate-50 to-gray-100 rounded-xl shadow-lg border border-gray-300 h-[43rem] ">
            <!-- Header Section -->
        
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="">
                        <h2 class="text-sm font-semibold text-gray-900">Sales History Report</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Comprehensive inventory and pricing analysis</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative flex items-center">
                            <span class="absolute left-3 flex items-center pointer-events-none text-gray-400">
                                <span class="material-symbols-rounded text-base">search</span>
                            </span>
                            <input 
                                type="text"
                                wire:model.live="searchWord"
                                placeholder="Search Product Name..."
                                class="text-xs rounded-lg border border-gray-300 pl-9 pr-10 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto scrollbar-custom h-[38rem]">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100 sticky top-0 z-10">
                            <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-700">Batch #</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-700">Stock In</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-700">Sold</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-700">Damaged</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-700">Cost Price</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-700">Selling Price</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-700">Effective Period</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white">
                        @forelse($history->groupBy('prod_name') as $productName => $productRows)
                            <!-- Product Name Row -->
                            <tr class="bg-gradient-to-r from-slate-100 to-gray-100 border-t-2 border-slate-300">
                                <td colspan="7" class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1 h-3 bg-gradient-to-b from-slate-600 to-slate-700 rounded-full"></div>
                                        <span class="font-bold text-xs text-slate-800">{{ $productName }}</span>
                                    </div>
                                </td>
                            </tr>
                            
                            @foreach($productRows->groupBy('inven_code') as $invenCode => $batchRows)
                                @php
                                    // Get batch details from first row
                                    $firstRow = $batchRows->first();
                                    $totalReceived = $firstRow->batch_received;
                                    $batchRemaining = $firstRow->batch_remaining;
                                    $isDepleted = $batchRemaining <= 0;
                                    
                                    // Sort by effective_from DESC (most recent first)
                                    $sortedBatchRows = $batchRows->sortByDesc(function($row) {
                                        return \Carbon\Carbon::parse($row->effective_from)->timestamp;
                                    });
                                    
                                    $rowspan = $sortedBatchRows->count();
                                @endphp
                                
                                @foreach($sortedBatchRows as $index => $h)
                                    @php
                                        $from = \Carbon\Carbon::parse($h->effective_from)->format('M d, Y');
                                        $to = $h->effective_to ? \Carbon\Carbon::parse($h->effective_to)->format('M d, Y') : 'Present';
                                        $isActive = $to === 'Present' && !$isDepleted;
                                    @endphp
                                    
                                    <tr class="hover:bg-slate-50 border-b border-gray-200 {{ $isActive ? 'bg-emerald-100/60' : '' }} {{ $isDepleted ? 'bg-red-100/50' : '' }}">
                                        
                                        {{-- Batch Number + Stock In (first row only) --}}
                                        @if($loop->first)
                                            <td class="px-4 py-3 text-center align-middle border-r bg-slate-50" rowspan="{{ $rowspan }}">
                                                <div class="flex flex-col items-center gap-1">
                                                    <!-- Batch Number -->
                                                    <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-700 text-[10px] font-mono font-semibold rounded">
                                                        {{ $h->batch_number }}
                                                    </span>
                                                    <!-- Date Added -->
                                                    <span class="text-[9px] text-gray-500">
                                                        {{ \Carbon\Carbon::parse($h->date_added)->format('M d, Y') }}
                                                    </span>
                                                    <!-- Depleted Label -->
                                                    @if($isDepleted)
                                                        <span class="text-[9px] font-bold text-red-700 uppercase tracking-wider bg-red-100 px-1 py-0.5 rounded mt-1">
                                                            Depleted
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center align-middle font-semibold text-[10px] text-slate-700 border-r bg-slate-50" rowspan="{{ $rowspan }}">
                                                {{ number_format($totalReceived) }}
                                            </td>
                                        @endif
                                        
                                        {{-- Sold --}}
                                        <td class="px-4 py-3 text-center border-r border-gray-200">
                                            <span class="inline-block px-2 py-0.5 rounded font-bold text-[10px] {{ $h->batch_sold_in_period > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400' }}">
                                                {{ number_format($h->batch_sold_in_period) }}
                                            </span>
                                        </td>
                                        
                                        {{-- Damaged --}}
                                        <td class="px-4 py-3 text-center border-r border-gray-200">
                                            <span class="inline-block px-2 py-0.5 rounded font-bold text-[10px] {{ $h->batch_damaged_in_period > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-400' }}">
                                                {{ number_format($h->batch_damaged_in_period) }}
                                            </span>
                                        </td>
                                        
                                        {{-- Cost Price (Original batch cost) --}}
                                        <td class="px-4 py-3 text-center font-semibold text-[10px] text-gray-700 border-r border-gray-200">
                                            <span class="font-mono">₱{{ number_format($h->batch_original_cost_price, 2) }}</span>
                                        </td>
                                        
                                        {{-- Selling Price (for this period) --}}
                                        <td class="px-4 py-3 text-center border-r border-gray-200">
                                            <span class="inline-block px-2 py-0.5 bg-green-50 text-green-700 font-mono font-bold text-[10px] rounded">
                                                ₱{{ number_format($h->old_selling_price, 2) }}
                                            </span>
                                        </td>
                                        
                                        {{-- Effective Period --}}
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex flex-col items-center gap-1">
                                                <div class="text-[10px] font-semibold text-slate-700">
                                                    <span class="font-mono">{{ $from }}</span>
                                                    <span class="text-gray-400 mx-1">→</span>
                                                    <span class="font-mono {{ $isActive ? 'text-emerald-700' : '' }}">
                                                        {{ $to }}
                                                    </span>
                                                </div>
                                                
                                                {{-- Show active badge only for non-depleted batches with current period --}}
                                                @if($isActive)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-[9px] font-bold rounded-full uppercase shadow-sm">
                                                        <span class="w-1 h-1 bg-white rounded-full animate-pulse"></span>
                                                        Active
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center bg-gradient-to-b from-gray-50 to-white">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="">
                                            <span class="material-symbols-rounded-semibig text-gray-500">inventory_2</span>
                                        </div>
                                        <p class="text-gray-600 text-sm font-medium mb-1">No data available</p>
                                        <p class="text-gray-500 text-xs">There are no records to display at this time</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>