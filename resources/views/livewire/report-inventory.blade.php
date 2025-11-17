<div x-data="{ tab: 'stock' }" class="w-full px-4 {{ ($expired || $plan === 3) ? 'blur-sm pointer-events-none select-none' : '' }}">

    <div class="flex space-x-1">
        <button 
            @click="tab = 'stock'"
            :class="tab === 'stock' 
                ? 'bg-red-50 text-black border-red-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Stock In & Out
        </button>

        <button 
            @click="tab = 'expiring'"
            :class="tab === 'expiring' 
                ? 'bg-blue-50 text-black border-blue-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Expiring Poducts
        </button>        

        <!-- <button 
            @click="tab = 'top-selling'"
            :class="tab === 'top-selling' 
                ? 'bg-green-50 text-black border-green-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Top Selling Product
        </button> -->

        <button 
            @click="tab = 'loss'"
            :class="tab === 'loss' 
                ? 'bg-gray-50 text-black border-gray-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Loss Report
        </button>
        
    </div>

    <div class="border bg-white rounded-b-lg h-[41rem]"
        :class="{
            'border-blue-500 bg-blue-50': tab === 'expiring',
            'border-red-500 bg-red-50': tab === 'stock',
            'border-green-500 bg-green-50': tab === 'top-selling',
            'border-gray-900 bg-gray-50': tab === 'loss'
        }">

        <!-- TOP SELLING -->
        <div x-show="tab === 'top-selling'">
            <p class="text-gray-700">⚡ <b>top selling</b> report content goes here.</p>
        </div>

        <!-- STOCK -->
        <div wire:poll.15s="stockAlertReport" wire:keep-alive class="hidden"></div>
        <div x-show="tab === 'stock'">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Stock Performance Report</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Analysis of inventory status and insights</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="text-xs font-medium text-gray-700">Filter by Category:</label>
                            <select wire:model.live="selectedCategory" 
                                class="text-xs border border-gray-300 rounded-lg px-4 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="all">All Categories</option>
                                @foreach($category as $cat)
                                    <option value="{{ $cat->cat_id }}">{{ $cat->cat_name }}</option>
                                @endforeach
                            </select>
                            <button 
                                wire:click="exportStockReport" 
                                class="border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hover:bg-slate-50 flex items-center justify-center p-1.5 gap-1.5"
                                @if(!$stock || $stock->isEmpty()) disabled @endif>
                                <span class="material-symbols-rounded">file_export</span>
                                <span class="text-xs">Export</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center gap-6 text-xs">
                        <span class="font-medium text-gray-700">Insight Legend:</span>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-green-100 border border-green-300"></span>
                            <span class="text-gray-600">Excellent Performance</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-blue-100 border border-blue-300"></span>
                            <span class="text-gray-600">Good Performance</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-yellow-100 border border-yellow-300"></span>
                            <span class="text-gray-600">Needs Attention</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-red-100 border border-red-300"></span>
                            <span class="text-gray-600">Critical Issue</span>
                        </div>
                    </div>
                </div> -->

                <div class="overflow-y-auto overflow-x-auto scrollbar-custom h-[36.3rem]">
                    <table class="w-full text-sm {{ $stock->isNotEmpty() ? 'min-w-[95rem]' : 'w-full' }}">
                        <thead class="bg-gray-100 sticky top-0 z-10">
                            <tr class="sticky top-0 bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]">
                                
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                    Product Name
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                    Batch #
                                </th>
                                <th colspan="4" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                    Stock Metrics
                                </th>
                                <th colspan="2" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                    Performance Rates
                                </th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-blue-50 border-l-2 border-gray-300">
                                    Insight
                                </th>
                            </tr>
                            <tr class="sticky bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]" style="top: 42px;">
                                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100">
                                    <div class="flex items-center gap-2 text-xs font-medium text-gray-600">
                                        <span class="material-symbols-rounded cursor-pointer" title="Use to filter out products by their stock status">info</span>
                                        <button 
                                            wire:click="$set('selectedStockStatus', 'active')" 
                                            class="hover:text-gray-900 transition"
                                            :class="{ 'text-gray-900 font-semibold underline underline-offset-4' : @entangle('selectedFilter') === 'active' }">
                                            Active
                                        </button>

                                        <span class="text-gray-400">|</span>

                                        <button 
                                            wire:click="$set('selectedStockStatus', 'depleted')" 
                                            class="hover:text-gray-900 transition"
                                            :class="{ 'text-gray-900 font-semibold underline underline-offset-4' : @entangle('selectedFilter') === 'depleted' }">
                                            Depleted
                                        </button>

                                        <span class="text-gray-400">|</span>

                                        <button 
                                            wire:click="$set('selectedStockStatus', 'all')" 
                                            class="hover:text-gray-900 transition"
                                            :class="{ 'text-gray-900 font-semibold underline underline-offset-4' : @entangle('selectedFilter') === 'all' }">
                                            All
                                        </button>
                                    </div>
                                </th>
                                <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                                
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Initial Stock</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Current</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Sold</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Damaged</th>
                                
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Sales Rate</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Damage Rate</th>
                                
                                <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white text-xs">
                            @php
                                $groupedStock = $stock->groupBy('prod_code');
                                $totalRows = 0;
                            @endphp
                            
                            @forelse($groupedStock as $prodCode => $productBatches)
                                @php
                                    $batchCount = count($productBatches);
                                    $totalRows += $batchCount;
                                @endphp
                                
                                @foreach($productBatches as $index => $row)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        @if($index === 0)
                                            <td class="px-4 py-3.5 text-gray-700 font-medium" rowspan="{{ $batchCount }}">
                                                {{ $row->prod_name }}
                                            </td>
                                        @endif
                                        <td class="px-4 py-3.5 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $row->batch_number }}
                                        </td>                    
                                        <td class="px-4 py-3.5 text-right font-semibold text-gray-900 bg-gray-50">
                                            {{ number_format($row->usable_stock + $row->sold_stock + $row->damaged_stock) }}
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-bold text-blue-600 bg-gray-50">
                                            {{ number_format($row->usable_stock) }}
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-semibold text-green-600 bg-gray-50">
                                            {{ number_format($row->sold_stock) }}
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-semibold text-red-600 bg-gray-50">
                                            {{ number_format($row->damaged_stock) }}
                                        </td>

                                        <td class="px-4 py-3.5 text-center bg-gray-50">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold
                                                {{ $row->sales_rate_percent > 70 ? 'bg-green-100 text-green-700' : 
                                                ($row->sales_rate_percent > 40 ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700') }}">
                                                {{ number_format($row->sales_rate_percent, 1) }}%
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 text-center bg-gray-50">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold
                                                {{ $row->damaged_rate_percent > 10 ? 'bg-red-100 text-red-700' : 
                                                ($row->damaged_rate_percent > 5 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                                {{ number_format($row->damaged_rate_percent, 1) }}%
                                            </span>
                                        </td>
                                        
                                        <td class="py-3.5 {{ $row->insight_color }}">
                                            <div class="flex items-center justify-center gap-2 text-[10px] font-medium">
                                                {{ $row->insight }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-16">
                                        <div class="flex flex-col justify-center items-center space-y-3">
                                            <span class="material-symbols-rounded text-6xl text-gray-300">inventory_2</span>
                                            <div>
                                                <p class="text-gray-600 font-medium">No Stock Data Available</p>
                                                <p class="text-gray-400 text-sm mt-1">Try adjusting your category filter</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        
                        @if($totalRows > 0)
                        <tfoot class="sticky bottom-0 z-10 bg-slate-100 shadow-[0_-1px_0_0_rgb(209,213,219)]">
                            <tr class="border-t-2 border-gray-600">
                                <td colspan="2" class="px-4 py-3 text-left font-bold uppercase text-xs tracking-wider">
                                    Total
                                </td>
                                <td class="px-4 text-right font-bold text-xs">
                                    {{ number_format($stock->sum(function($row) { return $row->usable_stock + $row->sold_stock + $row->damaged_stock; })) }}
                                </td>
                                <td class="px-4 text-right font-bold text-xs text-blue-600">
                                    {{ number_format($stock->sum('usable_stock')) }}
                                </td>
                                <td class="px-4 text-right font-bold text-xs text-green-600">
                                    {{ number_format($stock->sum('sold_stock')) }}
                                </td>
                                <td class="px-4 text-right font-bold text-xs text-red-600">
                                    {{ number_format($stock->sum('damaged_stock')) }}
                                </td>
                                <td colspan="3" class="px-2 py-2 text-center text-xs text-gray-400">
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                
                <!-- <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
                    <p class="text-xs text-gray-500">
                        <span class="font-medium">Total Records:</span> {{ $stock->count() }} items
                        <span class="mx-2">•</span>
                        <span class="font-medium">Generated:</span> {{ now()->format('M d, Y - h:i A') }}
                    </p>
                </div> -->
            </div>
        </div>

        @if($showSuccess)
            <div 
                x-data="{ show: true }"
                x-init="
                    setTimeout(() => {
                        show = false;
                        setTimeout(() => @this.set('showSuccess', false), 300);
                    }, 3000)
                "
                x-show="show"
                x-transition.opacity.duration.300ms
                class="fixed bottom-6 left-6 bg-gray-800 text-white text-sm px-4 py-3 pr-4 rounded shadow-lg z-50 flex items-center gap-3"
            >
                <span class="material-symbols-rounded text-3xl text-green-300">
                    download_done
                </span>
                <span>Report has been exported to XLSX file successfully.</span>
            </div>  
        @endif
    

        <!-- EXPIRING PRODUCTS -->
        <div wire:poll.15s="expired" wire:keep-alive class="hidden"></div>
        <div x-show="tab === 'expiring'" class="bg-white rounded-lg shadow-sm">
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Expiring Stock Report</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Monitor products approaching expiration to minimize waste</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-xs font-medium text-gray-700">Expiring within:</label>
                        <select wire:model.live="selectedRange" 
                            class="text-xs border border-gray-300 rounded-lg px-4 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="60">60 days</option>
                            <option value="30">30 days</option>
                            <option value="14">14 days</option>
                            <option value="7">7 days</option>
                            <option value="0">Already Expired items</option>
                        </select>

                        <label class="text-xs font-medium text-gray-700">Filter by Category:</label>
                        <select wire:model.live="selectedCategory" 
                            class="text-xs border border-gray-300 rounded-lg px-4 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all">All Categories</option>
                            @foreach($category as $cat)
                                <option value="{{ $cat->cat_id }}">{{ $cat->cat_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-y-auto overflow-x-auto scrollbar-custom h-[36.3rem]">
                <table class="w-full text-sm {{ $expiredProd->isNotEmpty() ? 'min-w-[99rem]' : 'w-full' }}">
                    <thead class="bg-gray-100 sticky top-0 z-10">
                        <tr class="sticky top-0 bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]">
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100 w-5"></th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Batch #
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Product Name
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Category
                            </th>
                            
                            <th colspan="2" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Expiration Status
                            </th>
                            
                            <th colspan="3" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Financial Impact
                            </th>
                            
                            <th colspan="2" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-blue-50 border-l-2 border-gray-300">
                                Analysis
                            </th>
                        </tr>
                        <tr class="sticky bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]" style="top: 42px;">
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-100"></th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Expiry Date</th>
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Days Left</th>
                            
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Quantity</th>
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Cost/Unit</th>
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Total Loss</th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">Will Sell?</th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">Action</th>
                        </tr>
                    </thead>
                    
                    <tbody class="divide-y divide-gray-200 bg-white text-xs">
                        @forelse ($expiredProd as $row)
                            <tr class="hover:bg-gray-50 transition-colors duration-150
                                {{ $row->days_until_expiry <= 0 ? 'bg-red-50' : '' }}
                                {{ $row->days_until_expiry > 0 && $row->days_until_expiry <= 7 ? 'bg-orange-50' : '' }}">
                                
                                <td class="px-4 py-3.5 text-center justify-center">
                                    @if ($row->days_until_expiry <= 0)
                                        <span class="material-symbols-rounded text-red-600 text-lg">error</span>
                                    @elseif ($row->days_until_expiry <= 7)
                                        <span class="material-symbols-rounded text-orange-500 text-lg">e911_emergency</span>
                                    @elseif ($row->days_until_expiry <= 14)
                                        <span class="material-symbols-rounded text-yellow-500 text-lg">schedule</span>
                                    @endif
                                </td>
                                
                                <td class="px-4 py-3.5 font-medium text-gray-900 whitespace-nowrap">
                                    {{ $row->batch_num }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-700 font-medium">
                                    {{ $row->prod_name }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-600">
                                    {{ $row->cat_name }}
                                </td>
                                
                                <!-- Expiration Status -->
                                <td class="px-4 py-3.5 text-right font-semibold text-gray-900 bg-gray-50">
                                    {{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-bold bg-gray-50
                                    {{ $row->days_until_expiry <= 0 ? 'text-red-600' : '' }}
                                    {{ $row->days_until_expiry > 0 && $row->days_until_expiry <= 7 ? 'text-orange-600' : '' }}
                                    {{ $row->days_until_expiry > 7 && $row->days_until_expiry <= 14 ? 'text-yellow-600' : '' }}
                                    {{ $row->days_until_expiry > 14 ? 'text-blue-600' : '' }}">
                                    @if($row->days_until_expiry < 0)
                                        Expired {{ abs($row->days_until_expiry) }}d ago
                                    @elseif($row->days_until_expiry == 0)
                                        Expires today
                                    @elseif($row->days_until_expiry == 1)
                                        Tomorrow
                                    @else
                                        {{ $row->days_until_expiry }} days
                                    @endif
                                </td>
                                
                                <!-- Financial Impact -->
                                <td class="px-4 py-3.5 text-right font-semibold text-gray-900 bg-gray-50">
                                    {{ number_format($row->expired_stock) }}
                                </td>
                                <td class="px-4 py-3.5 text-right text-gray-700 bg-gray-50">
                                    ₱{{ number_format($row->cost, 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-bold text-red-600 bg-gray-50">
                                    ₱{{ number_format($row->total_loss, 2) }}
                                </td>
                                
                                <!-- Analysis -->
                                <td class="px-4 py-3.5 text-center bg-blue-50">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold
                                        @if(str_contains($row->will_sell_before_expiry, 'unlikely to sell'))
                                            bg-rose-100 text-red-700 border border-red-300
                                        @elseif(str_contains($row->will_sell_before_expiry, 'Already expired'))
                                            bg-red-200 text-red-900 border border-red-400
                                        @elseif(str_contains($row->will_sell_before_expiry, 'At risk'))
                                            bg-orange-100 text-orange-700 border border-orange-300
                                        @elseif(str_contains($row->will_sell_before_expiry, 'Will likely sell out'))
                                            bg-green-100 text-green-700 border border-green-300
                                        @else
                                            bg-gray-100 text-gray-700 border border-gray-300
                                        @endif
                                    ">
                                        {{ $row->will_sell_before_expiry }}
                                    </span>
                                </td>
            
                                <td class="px-4 py-3.5 text-center text-xs font-semibold bg-blue-50
                                    @if(str_contains($row->insight, 'Expired'))
                                        bg-gray-900 text-white border-gray-950
                                    @elseif(str_contains($row->insight, 'Critical') || str_contains($row->insight, 'Urgent'))
                                        bg-red-600 text-white  border-red-800
                                    @elseif(str_contains($row->insight, 'Action needed'))
                                        bg-orange-500 text-white  border-orange-700
                                    @elseif(str_contains($row->insight, 'Warning') || str_contains($row->insight, 'Sales pace too slow'))
                                        bg-yellow-400 text-gray-900  border-yellow-600
                                    @elseif(str_contains($row->insight, 'week left') || str_contains($row->insight, 'weeks left'))
                                        bg-blue-500 text-white  border-blue-700
                                    @elseif(str_contains($row->insight, 'month left'))
                                        bg-indigo-500 text-white  border-indigo-700
                                    @else
                                        bg-green-600 text-white  border-green-800
                                    @endif">
                                    <div class="flex items-center justify-center gap-2 text-[10px] font-medium">
                                        {{ $row->insight }}
                                    </div>
                                </td>
                            </tr>
                        @empty 
                            <tr>
                                <td colspan="11" class="text-center py-16">
                                    <div class="flex flex-col justify-center items-center space-y-3">
                                        <span class="material-symbols-rounded text-6xl text-gray-300">event_available</span>
                                        <div>
                                            <p class="text-gray-600 font-medium">No Expiring Products Found</p>
                                            <p class="text-gray-400 text-sm mt-1">All products are well within expiration dates</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($expiredProd->isNotEmpty())
                    <tfoot class="sticky bottom-0 z-10 bg-slate-100 shadow-[0_-1px_0_0_rgb(209,213,219)]">
                        <tr class="border-t-2 border-gray-600">
                            <td colspan="6" class="px-4 py-3 text-left font-bold uppercase text-xs tracking-wider">
                                Total Potential Loss
                            </td>
                            <td class="px-4 text-right font-bold text-xs">
                                {{ number_format($expiredProd->sum('expired_stock')) }}
                            </td>
                            <td class="px-4 text-center text-xs text-gray-400"></td>
                            <td class="px-4 text-right font-bold text-xs text-red-700">
                                ₱{{ number_format($expiredProd->sum('total_loss'), 2) }}
                            </td>
                            <td colspan="2" class="px-4 text-center text-xs text-gray-400"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>




        <!-- DAMAGED/ LOSS/ EXPIRED-->
        <div wire:poll.15s="loss" wire:keep-alive class="hidden"></div>
        <div x-show="tab === 'loss'" class="bg-white rounded-lg shadow-sm">
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Stock Loss & Damage Report</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Track and analyze all inventory losses and damage incidents</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-medium text-gray-700">Period:</label>
                            <select wire:model.live="selectedMonths" 
                                class="text-xs border border-gray-300 rounded-lg px-3 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @foreach ($monthNames as $index => $name)
                                    <option value="{{ $index + 1 }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <select wire:model.live="selectedYears" 
                                class="text-xs border border-gray-300 rounded-lg px-3 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @forelse ($years as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @empty
                                    <option value="{{ now()->year }}">{{ now()->year }}</option>
                                @endforelse
                            </select>
                        </div>
                        
                        <button wire:click="showAll" 
                            class="text-xs border rounded-lg px-3 py-2 font-medium transition-colors
                                {{ is_null($selectedMonths) && is_null($selectedYears) 
                                    ? 'bg-blue-600 text-white border-blue-600 shadow-sm' 
                                    : 'border-gray-300 text-gray-700 hover:bg-gray-50 bg-white' }}">
                            Show All
                        </button>

                        <select wire:model.live="selectedLossType" 
                            class="text-xs border border-gray-300 rounded-lg px-3 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Damage Types</option>
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
                        
                        <button 
                            wire:click="exportLossReport" 
                            class="border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hover:bg-slate-50 flex items-center justify-center p-1.5 gap-1.5"
                            @if(!$stock || $stock->isEmpty()) disabled @endif>
                            <span class="material-symbols-rounded">file_export</span>
                            <span class="text-xs">Export</span>
                        </button>
                    </div>  
                </div>
            </div>

            <!-- Table Container -->
            <div class="overflow-y-auto overflow-x-auto scrollbar-custom h-[36.3rem]">
                <table class="w-full text-sm {{ $lossRep->isNotEmpty() ? 'min-w-[95rem]' : 'w-full' }}">
                    <thead class="bg-gray-100 sticky top-0 z-10">
                        <tr class="sticky top-0 bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]">
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Date Reported
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Batch #
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Product Name
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Category
                            </th>
                            
                            <th class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Loss Type
                            </th>
                            
                            <th colspan="3" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Financial Impact
                            </th>
                            
                            <th colspan="2" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-blue-50 border-l-2 border-gray-300">
                                Additional Details
                            </th>
                        </tr>
                        <tr class="sticky bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]" style="top: 42px;">
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">Type</th>
                            
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Quantity Lost</th>
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Unit Cost</th>
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Total Loss</th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">Reported By</th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">Remarks</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white text-xs">
                        @forelse ($lossRep as $row)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-4 py-3.5 text-gray-700 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($row->date_reported)->format('M d, Y') }}
                                    <div class="text-[10px] text-gray-500">
                                        {{ \Carbon\Carbon::parse($row->date_reported)->format('h:i A') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-medium text-gray-900 whitespace-nowrap">
                                    {{ $row->batch_num }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-700 font-medium">
                                    {{ $row->prod_name }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-600">
                                    {{ $row->cat_name }}
                                </td>
                                
                                <!-- Loss Type -->
                                <td class="px-4 py-3.5 text-center bg-gray-50">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold
                                        @if(strtolower($row->type) === 'expired') 
                                            bg-red-100 text-red-700 border border-red-300
                                        @elseif(strtolower($row->type) === 'broken') 
                                            bg-orange-100 text-orange-700 border border-orange-300
                                        @elseif(strtolower($row->type) === 'spoiled') 
                                            bg-red-100 text-red-700 border border-red-300
                                        @elseif(strtolower($row->type) === 'damaged') 
                                            bg-orange-100 text-orange-700 border border-orange-300
                                        @elseif(strtolower($row->type) === 'stolen') 
                                            bg-purple-100 text-purple-700 border border-purple-300
                                        @elseif(strtolower($row->type) === 'contaminated') 
                                            bg-red-100 text-red-700 border border-red-300
                                        @else 
                                            bg-gray-100 text-gray-700 border border-gray-300
                                        @endif">
                                        {{ ucfirst($row->type) }}
                                    </span>
                                </td>
                                
                                <!-- Financial Impact -->
                                <td class="px-4 py-3.5 text-right font-bold text-gray-900 bg-gray-50">
                                    {{ number_format($row->qty) }}
                                </td>
                                <td class="px-4 py-3.5 text-right text-gray-700 bg-gray-50">
                                    ₱{{ number_format($row->unit_cost, 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-bold text-red-600 bg-gray-50">
                                    ₱{{ number_format($row->total_loss, 2) }}
                                </td>
                                
                                <!-- Additional Details -->
                                <td class="px-4 py-3.5 text-center font-medium text-gray-700 bg-blue-50">
                                    {{ ucwords($row->reported_by) }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 text-center bg-blue-50">
                                    @if($row->remarks)
                                        <div class="max-w-xs truncate" title="{{ $row->remarks }}">
                                            {{ $row->remarks }}
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty 
                            <tr>
                                <td colspan="10" class="text-center py-16">
                                    <div class="flex flex-col justify-center items-center space-y-3">
                                        <span class="material-symbols-rounded text-6xl text-gray-300">check_circle</span>
                                        <div>
                                            <p class="text-gray-600 font-medium">No Loss Records Found</p>
                                            <p class="text-gray-400 text-sm mt-1">No damage or loss incidents for the selected period</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($lossRep->isNotEmpty())
                    <tfoot class="sticky bottom-0 z-10 bg-slate-100 shadow-[0_-1px_0_0_rgb(209,213,219)]">
                        <tr class="border-t-2 border-gray-600">
                            <td colspan="5" class="px-4 py-3 text-left font-bold uppercase text-xs tracking-wider">
                                Total Loss Summary
                            </td>
                            <td class="px-4 text-right font-bold text-xs">
                                {{ number_format($lossRep->sum('qty')) }} units
                            </td>
                            <td class="px-4 text-center text-xs text-gray-400">
                            </td>
                            <td class="px-4 text-right font-bold text-xs text-red-700">
                                ₱{{ number_format($lossRep->sum('total_loss'), 2) }}
                            </td>
                            <td colspan="2" class="px-4 text-center text-xs text-black">
                                {{ $lossRep->count() }} incident(s) reported
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

