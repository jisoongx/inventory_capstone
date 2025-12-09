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

        <button 
            @click="tab = 'damaged'"
            :class="tab === 'damaged' 
                ? 'bg-green-50 text-black border-green-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Damage Product Report
        </button>
        
    </div>

    <div class="border bg-white rounded-b-lg h-[41rem]"
        :class="{
            'border-blue-500 bg-blue-50': tab === 'expiring',
            'border-red-500 bg-red-50': tab === 'stock',
            'border-green-500 bg-green-50': tab === 'damaged',
            'border-gray-900 bg-gray-50': tab === 'loss'
        }">

        <!-- STOCK -->
        <div wire:poll.live="stockAlertReport" wire:keep-alive class="hidden"></div>
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
                    <table class="w-full text-sm {{ $stock->isNotEmpty() ? 'w-[90rem]' : 'w-full' }}">
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
                                
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Stock In</th>
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
                                            <td class="px-4 py-3.5 font-semibold" rowspan="{{ $batchCount }}">
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
                                        
                                        <td class="px-4 py-3.5 text-center text-[10px] font-semibold bg-blue-50 w-[27%]">
                                            {{ $row->insight }}
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
                <table class="w-full text-sm {{ $expiredProd->isNotEmpty() ? 'w-[80rem]' : 'w-full' }}">
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
                            
                            <th colspan="2" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
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
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Total Loss</th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">Will Sell?</th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">
                                <div class="flex items-center justify-center gap-1">
                                    <span>Action</span>
                                    <span 
                                        class="material-symbols-rounded cursor-pointer" 
                                        title="Estimated days and units only. Actual sales may vary."
                                    >
                                        info
                                    </span>
                                </div>
                            </th>
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
                                <td class="px-4 py-3.5 text-right font-bold text-red-600 bg-gray-50">
                                    ₱{{ number_format($row->total_loss, 2) }}
                                </td>
                                
                                <!-- Analysis -->
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-semibold
                                        @if($row->risk_category === 'Likely to sell') bg-green-50 text-green-700 border border-green-700
                                        @elseif($row->risk_category === 'May not sell all stock') bg-orange-50 text-orange-700  border border-orange-700
                                        @elseif($row->risk_category === 'High risk of waste!') bg-red-50 text-red-700  border border-red-700
                                        @else bg-slate-50 text-slate-700  border border-slate-700
                                        @endif">
                                        {{ $row->risk_category }}
                                    </span>
                                </td>
            
                                <td class="px-4 py-3.5 text-center text-xs font-semibold bg-blue-50 w-[15rem]">
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
                                {{ number_format($expiredProd->sum('expired_stock')) }} units
                            </td>
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


        <!-- DAMAGED-->
        <div></div>
        <div x-show="tab === 'damaged'" class="bg-white rounded-lg shadow-sm">
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Inventory Damage Report</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Track and analyze all inventory losses and damage incidents</p>
                    </div>
                </div>
            </div>

            <!-- Table Container -->
            <div class="overflow-y-auto overflow-x-auto scrollbar-custom h-[36.3rem]">
                <table class="w-full text-sm {{ $lossRep->isNotEmpty() ? 'w-full' : 'w-full' }}">
                    <thead class="bg-gray-100 sticky top-0 z-10">
                        <tr class="sticky top-0 bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]">
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Date Reported
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Product Name
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Loss Type
                            </th>      
                            <th colspan="2" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Financial Impact
                            </th>      
                            <th colspan="3" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-blue-50 border-l-2 border-gray-300">
                                Additional Details
                            </th>
                        </tr>
                        <tr class="sticky bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]" style="top: 42px;">
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">Type</th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">Quantity</th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">Total</th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">Reported By</th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">Remarks</th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white text-xs">
                        @forelse ($damagedRep as $row)
                        <tr wire:key="row-{{ $row->damaged_id }}"
                            id="row-{{ $row->damaged_id }}"
                            class="hover:bg-gray-50"
                            x-data="{ flash: false }"
                            x-on:row-updated.window="if ($event.detail.rowId === {{ $row->damaged_id }}) { flash = true; setTimeout(() => flash = false, 1000) }"
                            :class="{ 'flash-green-gradient': flash }">
                            <td class="px-4 py-3.5 text-gray-700 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($row->date_reported)->format('M d, Y') }}
                                <div class="text-[10px] text-gray-500">
                                    {{ \Carbon\Carbon::parse($row->date_reported)->format('h:i A') }}
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-gray-700 font-medium">
                                {{ $row->prod_name }}
                            </td>
                            
                            <!-- Loss Type -->
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold
                                    @if(strtolower($row->type) === 'expired') 
                                        bg-red-100 text-red-700 border border-red-300
                                    @elseif(strtolower($row->type) === 'broken') 
                                        bg-orange-100 text-orange-700 border border-orange-300
                                    @elseif(strtolower($row->type) === 'spoiled') 
                                        bg-amber-100 text-amber-700 border border-amber-300
                                    @elseif(strtolower($row->type) === 'damaged') 
                                        bg-orange-100 text-orange-700 border border-orange-300
                                    @elseif(strtolower($row->type) === 'defective') 
                                        bg-yellow-100 text-yellow-700 border border-yellow-300
                                    @elseif(strtolower($row->type) === 'contaminated') 
                                        bg-red-100 text-red-700 border border-red-300
                                    @elseif(strtolower($row->type) === 'crushed') 
                                        bg-orange-100 text-orange-700 border border-orange-300
                                    @elseif(strtolower($row->type) === 'leaking') 
                                        bg-blue-100 text-blue-700 border border-blue-300
                                    @elseif(strtolower($row->type) === 'torn') 
                                        bg-amber-100 text-amber-700 border border-amber-300
                                    @elseif(strtolower($row->type) === 'wet') 
                                        bg-cyan-100 text-cyan-700 border border-cyan-300
                                    @elseif(strtolower($row->type) === 'mold') 
                                        bg-green-100 text-green-700 border border-green-300
                                    @elseif(strtolower($row->type) === 'pest') 
                                        bg-lime-100 text-lime-700 border border-lime-300
                                    @elseif(strtolower($row->type) === 'temperature') 
                                        bg-rose-100 text-rose-700 border border-rose-300
                                    @elseif(strtolower($row->type) === 'recalled') 
                                        bg-red-100 text-red-700 border border-red-300
                                    @elseif(strtolower($row->type) === 'missing parts') 
                                        bg-indigo-100 text-indigo-700 border border-indigo-300
                                    @elseif(strtolower($row->type) === 'wrong item') 
                                        bg-violet-100 text-violet-700 border border-violet-300
                                    @elseif(strtolower($row->type) === 'unsealed') 
                                        bg-pink-100 text-pink-700 border border-pink-300
                                    @elseif(strtolower($row->type) === 'faded') 
                                        bg-slate-100 text-slate-700 border border-slate-300
                                    @elseif(strtolower($row->type) === 'stolen') 
                                        bg-purple-100 text-purple-700 border border-purple-300
                                    @else 
                                        bg-gray-100 text-gray-700 border border-gray-300
                                    @endif">
                                    {{ ucfirst($row->type) }}
                                </span>
                            </td>
                            
                            <!-- Financial Impact -->
                            <td class="px-4 py-3.5 text-center font-bold text-gray-900">
                                {{ number_format($row->qty) }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-red-600">
                                ₱{{ number_format($row->total_loss, 2) }}
                            </td>
                            
                            <!-- Additional Details -->
                            <td class="px-4 py-3.5 text-center font-medium text-gray-700">
                                @if(str_contains(strtolower($row->remarks ?? ''), 'system'))
                                    System
                                @else
                                    {{ ucwords($row->reported_by ?? 'N/A') }}
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-gray-600 text-center">
                                @if($row->remarks)
                                <div x-data="{ open: false }" class="relative">
                                    <div 
                                        class="max-w-xs truncate hover:underline cursor-pointer"
                                        @click="open = true"
                                    >
                                        {{ $row->remarks }}
                                    </div>

                                    <div 
                                        x-show="open"
                                        @click.outside="open = false"
                                        x-transition
                                        class="absolute left-0 w-64 bg-white border border-gray-300 rounded-lg shadow-lg p-5 z-50"
                                        style="display: none;"
                                    >
                                        <div class="text-xs text-gray-700 whitespace-pre-line">
                                            {{ $row->remarks }}
                                        </div>
                                    </div>
                                </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-gray-800 text-center">
                                <select 
                                    wire:change="updateStatus({{ $row->damaged_id }}, $event.target.value)"
                                    class="border rounded px-2 py-0.5 text-[10px]"
                                    {{ in_array($row->status, ['Completed', 'Damaged']) ? 'disabled' : '' }}
                                >
                                    <option disabled>Select status</option>
                                    <option value="To be returned" {{ $row->status == 'To be returned' ? 'selected' : '' }}>To be returned</option>
                                    <option value="Processing" {{ $row->status == 'Processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="Completed" {{ $row->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="Damaged" {{ $row->status == 'Damaged' ? 'selected' : '' }}>Unable to return</option>
                                </select>
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

                    @if($damagedRep->isNotEmpty())
                    <tfoot class="sticky bottom-0 z-10 bg-slate-100 shadow-[0_-1px_0_0_rgb(209,213,219)]">
                        <tr class="border-t-2 border-gray-600">
                            <td colspan="3" class="px-4 py-3 text-left font-bold uppercase text-xs tracking-wider">
                                Total Loss Summary
                            </td>
                            <td class="px-4 text-center font-bold text-xs">
                                {{ number_format($damagedRep->sum('qty')) }} units
                            </td>
                            <td class="px-4 text-center font-bold text-xs text-red-700">
                                ₱{{ number_format($damagedRep->sum('total_loss'), 2) }}
                            </td>
                            <td colspan="3" class="px-4 text-center text-xs text-black">
                                {{ $damagedRep->count() }} incident(s) reported
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @if($showReasonModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white w-full max-w-md rounded-xl shadow-lg p-6">

            <h2 class="text-sm font-semibold text-gray-800 mb-3">
                Provide a Reason
            </h2>

            <textarea 
                wire:model="damagedReason"
                rows="7"
                class="w-full border border-gray-300 rounded-sm p-3 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                placeholder="Enter reason here..."></textarea>

            <div class="flex justify-end gap-3 mt-2">
                <button 
                    wire:click="closeReasonModal"
                    class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs">
                    Nevermind
                </button>

                <button 
                    wire:click="submitReason"
                    class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-xs">
                    Confirm
                </button>
            </div>

        </div>
    </div>
    @endif

</div>

