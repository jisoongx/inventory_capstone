<div x-data="{ tab: 'sales' }" class="w-full px-4">

    <div class="flex space-x-1">
        <button 
            @click="tab = 'sales'"
            :class="tab === 'sales' 
                ? 'bg-green-50 text-black border-green-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Sales (Daily/Monthly)
        </button>

        <button 
            @click="tab = 'sales-category'"
            :class="tab === 'sales-category' 
                ? 'bg-orange-50 text-black border-orange-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Sales by Category Report
        </button>

        <button 
            @click="tab = 'product-performance'"
            :class="tab === 'product-performance' 
                ? 'bg-purple-50 text-black border-purple-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Product Performance
        </button>

        <!-- <button 
            @click="tab = 'peak-hours'"
            :class="tab === 'peak-hours' 
                ? 'bg-blue-50 text-black border-blue-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Peak Hours Operational
        </button> -->
    </div>

    <div class="border bg-white p-4 rounded-b-lg mb-3 h-[41rem]"
        :class="{
            'border-green-500 bg-green-50': tab === 'sales',
            'border-orange-500 bg-orange-50': tab === 'sales-category',
            'border-purple-500': tab === 'product-performance',
            'border-blue-500 bg-blue-50': tab === 'peak-hours'
        }">

        <!-- DAILY SALES or MONTHLY -->
        <div x-show="tab === 'sales'">
            <p class="text-gray-700">📊 <b>Sales</b> report content goes here.</p>
        </div>

        <!-- sALES BY CATEGORY -->
        <div x-show="tab === 'sales-category'">
            <div x-data="{ open: false }" class="flex items-center mb-4 space-x-2 relative justify-between">
                
                <div class="relative flex items-center text-gray-300">
                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded">search</span>
                    </span>
                    <input 
                        type="text"
                        wire:model.live.debounce.1ms="searchWord"
                        placeholder="Search Category..."
                        class="rounded border border-gray-300 pl-10 pr-3 py-2 text-xs focus:ring focus:ring-orange-200 text-black"
                    >
                </div>

                <div class="space-x-1">
                    <select wire:model.live="selectedMonths" class="border border-gray-300 rounded px-3 py-2 text-xs">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>

                    <select wire:model.live="selectedYears" class="border border-gray-300 rounded px-3 py-2 text-xs">
                            @forelse ($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @empty
                            <option value="{{ now()->year }}">{{ now()->year }}</option>
                        @endforelse
                    </select>
                </div>                
            </div>


            <div class="overflow-y-auto scrollbar-custom h-[35rem]">
                <table x-data="{ showTopProductUnit: false, showTopProductSales: false }" class="w-full text-xs text-left shadow-sm">
                    <thead class="uppercase text-xs font-semibold bg-gray-100 text-gray-600">
                        <tr class="bg-gray-100 border-b-2 border-gray-300 sticky top-0">
                            <th class=" p-3 text-left sticky top-0 bg-gray-50 w-[20%]">Category</th>
                            <th class=" p-3 sticky top-0 bg-gray-50">Units Sold
                                <!-- <button @click="showTopProductUnit = !showTopProductUnit" type="button" class="text-xs">
                                    <div class="flex items-center justify-end space-x-1">
                                        <span>UNITS SOLD</span>
                                        <span class="material-symbols-rounded-small text-gray-400">
                                            keyboard_double_arrow_right
                                        </span>
                                    </div>
                                </button> -->
                            </th>
                            <th class="p-3 sticky top-0 bg-gray-50">
                                <div class="flex gap-1">
                                    <span>Stock Left</span>
                                    <span class="material-symbols-rounded-premium text-gray-400 text-sm" title="Reflects the latest available stock in inventory.">info</span>
                                </div>
                            </th>
                            <!-- <th class="p-3 text-left text-gray-500 font-semibold text-xs sticky top-0 bg-gray-50 w-[12%]" x-show="showTopProductUnit" x-cloak>
                                Top Product by Unit
                            </th> -->
                            <th class=" p-3 sticky top-0 bg-gray-50">Total Sales (₱)
                                <!-- <button @click="showTopProductSales = !showTopProductSales" type="button" class="text-xs">
                                    <div class="flex items-center justify-end space-x-1">
                                        <span>SALES (₱)</span>
                                        <span class="material-symbols-rounded-small text-gray-400">
                                            keyboard_double_arrow_right
                                        </span>
                                    </div>
                                </button> -->
                            </th>
                            <!-- <th class="p-3 text-left text-gray-500 font-semibold text-xs sticky top-0 bg-gray-50 w-[12%]" x-show="showTopProductSales" x-cloak>
                                Top Product by Sales
                            </th> -->
                            <th class=" p-3 sticky top-0 bg-gray-50">COGS</th>
                            <th class=" p-3 sticky top-0 bg-gray-50">Gross Margin</th>
                            <th class=" p-3 sticky top-0 bg-gray-50">Insight</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 text-xs">
                        @forelse($sbc as $input)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 text-gray-900">{{ $input->category }}</td>
                                <td class="py-3 px-4 text-left text-gray-900">{{ $input->unit_sold }}</td>
                                <td class="py-3 px-4 text-left text-gray-900">{{ $input->stock_left }}</td>
                                <!-- <td x-show="showTopProductUnit" x-cloak class="px-3 py-4 text-left text-gray-900 bg-gray-50">
                                    {{ $input->top_product_unit }}
                                </td> -->
                                <td class="py-3 px-4 text-left text-gray-900">₱{{ number_format($input->total_sales, 2 )}}</td>
                                <!-- <td x-show="showTopProductSales" x-cloak class="px-3 py-4 text-left text-gray-900 bg-gray-50">
                                    {{ $input->top_product_sales }}
                                </td> -->
                                <td class="py-3 px-4 text-left text-gray-900">₱{{ number_format($input->cogs, 2 )}}</td>
                                <td class="py-3 px-4 text-left text-gray-900">{{ number_format($input->gross_margin, 0) }}%</td>
                                <td class="py-3 px-4 text-center font-medium text-[10px] text-white w-[20rem]
                                    @if (strpos($input->insight, 'URGENT') !== false) bg-red-700
                                    @elseif (strpos($input->insight, 'critically low') !== false) bg-red-600
                                    @elseif (strpos($input->insight, 'Out of stock') !== false) bg-gray-600
                                    @elseif (strpos($input->insight, 'Low stock') !== false || strpos($input->insight, 'Stock running low') !== false) bg-orange-600
                                    @elseif (strpos($input->insight, 'declining') !== false) bg-red-500
                                    @elseif (strpos($input->insight, 'trending down') !== false) bg-orange-500
                                    @elseif (strpos($input->insight, 'accelerating') !== false) bg-emerald-600
                                    @elseif (strpos($input->insight, 'trending up') !== false) bg-green-500
                                    @elseif (strpos($input->insight, 'Star performer') !== false) bg-purple-600
                                    @elseif (strpos($input->insight, 'Good sales velocity') !== false) bg-blue-800
                                    @elseif (strpos($input->insight, 'Fast-moving but low margins') !== false) bg-yellow-600
                                    @elseif (strpos($input->insight, 'Slow-moving with poor margins') !== false) bg-red-400
                                    @elseif (strpos($input->insight, 'Slow-moving') !== false) bg-amber-500
                                    @elseif (strpos($input->insight, 'No recent sales') !== false) bg-red-600
                                    @elseif (strpos($input->insight, 'Low profit margin') !== false) bg-amber-600
                                    @elseif (strpos($input->insight, 'Strong profit margins') !== false) bg-green-600
                                    @elseif (strpos($input->insight, 'Steady sales') !== false) bg-blue-600
                                    @elseif (strpos($input->insight, 'Stable') !== false) bg-slate-500
                                    @else bg-slate-600 border border-slate-300 text-slate-700
                                    @endif
                                ">
                                    {{ $input->insight }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-gray-400">Nothing to show.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PRODUCT PERFORMANCE -->
        <div x-show="tab === 'product-performance'" x-init="if(window.location.search.includes('product-performance')) tab = 'product-performance'" 
            id="product-performance"
        >
            <div class="h-[39rem]">
                <div class="flex justify-between">
                    <div>
                        <select wire:model.live="selectedCategory" class="border border-gray-300 px-3 py-2 text-xs mb-4 rounded mr-1">
                            <option value="all" class="text-gray-500">Show All Categories</option>
                            @foreach ($category as $c)
                                <option value="{{ $c->cat_id }}">{{ $c->cat_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-x-1">
                        <select wire:model.live="selectedMonth" class="border border-gray-300 rounded px-3 py-2 text-xs">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>

                        <select wire:model.live="selectedYear" class="border border-gray-300 rounded px-3 py-2 text-xs">
                             @forelse ($years as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @empty
                                <option value="{{ now()->year }}">{{ now()->year }}</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <div class="hidden" wire:poll.keep-alive="prodPerformance()"></div>
                <div class="overflow-y-auto scrollbar-custom h-[35rem]">
                    <table id="analysis-table" class="w-full text-xs text-left shadow-sm">
                        <thead class="uppercase text-xs font-semibold bg-gray-200 text-gray-600">
                            <tr class="bg-gray-100 border-b-2 border-gray-300 sticky top-0">
                                <th class="cursor-pointer p-3 text-left sticky top-0 bg-gray-50" wire:click="sortBy('product_name')">Product ↓☰↑</th>
                                <th class="cursor-pointer p-3 sticky top-0 bg-gray-50" wire:click="sortBy('unit_sold')">
                                    <div class="flex gap-1">
                                        <span>Unit Sold ↓☰↑</span>
                                        <span class="material-symbols-rounded-premium text-gray-400 text-sm" title="Stock remains reflects the latest available stock in inventory.">info</span>
                                    </div>
                                </th>
                                <th class=" p-3 sticky top-0 bg-gray-50">Total Sales (₱)</th>
                                <th class=" p-3 sticky top-0 bg-gray-50">COGS</th>
                                <th class=" p-3 sticky top-0 bg-gray-50">Profit</th>
                                <th class=" p-3 sticky top-0 bg-gray-50">% Profit Margin</th>
                                <th class=" p-3 sticky top-0 bg-gray-50">% Share</th>
                                <th class=" p-3 sticky top-0 bg-gray-50">Insight</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($perf as $row)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-4">{{ $row->product_name }}</td>
                                    <td class="py-3 px-4 text-justify">
                                        <span class="font-medium">{{ $row->unit_sold }} sold, </span>
                                        {{ $row->remaining_stocks < 10 ? 'only' : ($row->remaining_stocks < 30 ? 'around' : 'about') }} 
                                        {{ $row->remaining_stocks }} remain
                                    </td>
                                    <td class="py-3 px-4">₱{{ number_format($row->total_sales, 2) }}</td>
                                    <td class="py-3 px-4">₱{{ number_format($row->cogs, 2) }}</td>
                                    <td class="py-3 px-4">₱{{ number_format($row->profit, 2) }}</td>
                                    <td class="py-3 px-4">{{ number_format($row->profit_margin_percent, 0) }}%</td>
                                    <td class="py-3 px-4">{{ number_format($row->contribution_percent, 1) }}%</td>
                                    <td class="py-3 px-4 text-center font-medium shadow-sm text-[10px] text-white
                                        @if(strpos($row->insight, 'Out of stock. Reorder needed') !== false) bg-red-700
                                        @elseif(strpos($row->insight, 'Low stock. Reorder soon') !== false) bg-orange-600
                                        @elseif(strpos($row->insight, 'Out of stock with no recent sales') !== false) bg-gray-700
                                        @elseif(strpos($row->insight, 'Unprofitable. Losing money') !== false) bg-red-600
                                        @elseif(strpos($row->insight, 'Low margin. Review pricing') !== false) bg-yellow-600
                                        @elseif(strpos($row->insight, 'Performing well') !== false) bg-green-600
                                        @elseif(strpos($row->insight, 'Good margin, low volume') !== false) bg-blue-800
                                        @elseif(strpos($row->insight, 'Moderate performance') !== false) bg-blue-500
                                        @elseif(strpos($row->insight, 'No sales this period') !== false) bg-amber-600
                                        @elseif(strpos($row->insight, 'No activity') !== false) bg-gray-600
                                        @elseif(strpos($row->insight, 'Needs attention') !== false) bg-purple-600
                                        @else bg-slate-600
                                        @endif
                                    ">
                                        {{ $row->insight }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="flex flex-col justify-center items-center space-y-1 p-8">
                                            <span class="material-symbols-rounded-semibig text-gray-400">taunt</span>
                                            <span class="text-gray-500">Nothing to show.</span>
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
</div>
