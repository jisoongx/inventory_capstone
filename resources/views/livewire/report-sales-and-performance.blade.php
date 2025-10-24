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
            <div x-data="{ open: false }" class="flex items-center mb-4 space-x-2 relative">
                
                <div class="relative flex items-center text-gray-400">
                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded">search</span>
                    </span>
                    <input 
                        type="text"
                        wire:model.live.debounce.1ms="searchWord"
                        placeholder="Search Category..."
                        class="rounded border border-gray-400 pl-10 pr-3 py-2 text-xs focus:ring focus:ring-orange-200 text-black"
                    >
                </div>

                <div class="relative">
                    <button @click="open = !open" type="button" class="py-2 px-3 border border-orange-500 rounded hover:bg-orange-50">
                        <div class="flex justify-center gap-1">
                            <span class="material-symbols-rounded-premium text-orange-700" title="Filter">discover_tune</span>
                            <span class="text-orange-700 text-xs font-semibold">Filter</span>
                        </div>
                    </button>
                    
                    <div x-show="open" x-cloak @click.away="open = false" 
                        class="absolute top-full right-0 mt-2 w-64 bg-white border border-orange-200 rounded-xl shadow-lg z-50 p-4 space-y-4">

                        <div>
                            <span class="text-[11px] font-semibold text-orange-700">Year:</span>
                            <div class="grid grid-cols-3 gap-2 mt-2">
                                @foreach($years as $yr)
                                    <label class="flex items-center justify-center cursor-pointer">
                                        <input type="radio" name="selectedYear" value="{{ $yr->year }}" wire:model="selectedYearSingle" class="hidden peer">
                                        <span class="peer-checked:bg-orange-600 peer-checked:text-white 
                                                    text-orange-600 bg-orange-100 hover:bg-orange-200 
                                                    rounded-full py-1 px-2 text-center text-[11px] transition">
                                            {{ $yr->year }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <span class="text-[11px] font-semibold text-orange-700">Months:</span>
                            <div class="grid grid-cols-3 gap-2 mt-2">
                                @foreach($monthNames as $index => $name)
                                    <label class="flex items-center justify-center cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            value="{{ $index + 1 }}" 
                                            wire:model="selectedMonths"
                                            class="hidden peer"
                                        >
                                        <span class="peer-checked:bg-orange-600 peer-checked:text-white 
                                                    text-orange-600 bg-orange-100 hover:bg-orange-200 
                                                    rounded-full py-1 px-2 text-center text-[11px] transition">
                                            {{ $name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex gap-2 mt-3">
                            <button wire:click="salesByCategory" 
                                class="flex-1 bg-orange-700 hover:bg-orange-800 text-white text-[11px] py-2 rounded-lg transition">
                                Proceed
                            </button>
                            <button wire:click="resetFilters" 
                                class="flex-1 bg-orange-100 hover:bg-orange-200 text-orange-700 text-[11px] py-2 rounded-lg transition">
                                Reset
                            </button>
                        </div>
                    </div>

                </div>
            </div>


            <div class="overflow-y-auto scrollbar-custom h-[35rem]">
                <table x-data="{ showTopProductUnit: false, showTopProductSales: false }" class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 h-[4rem]">
                        <tr class="text-gray-700 uppercase text-xs tracking-wider border-b">
                            <th class="px-2 py-3 text-left font-semibold text-xs sticky top-0 bg-gray-50 w-[20%]">Category</th>
                            <th class="px-2 py-3 text-left font-semibold sticky top-0 bg-gray-50  w-[12%]">                                    
                                <button @click="showTopProductUnit = !showTopProductUnit" type="button" class="text-xs">
                                    <div class="flex items-center justify-end space-x-1">
                                        <span>UNITS SOLD</span>
                                        <span class="material-symbols-rounded-small text-gray-400">
                                            keyboard_double_arrow_right
                                        </span>
                                    </div>
                                </button>
                            </th>
                            <th class="px-2 py-3 text-left text-gray-500 font-semibold text-xs sticky top-0 bg-gray-50 w-[12%]" x-show="showTopProductUnit" x-cloak>
                                Top Product by Unit
                            </th>
                            <th class="px-2 py-3 text-left font-semibold sticky top-0 bg-gray-50 w-[12%]">                                    
                                <button @click="showTopProductSales = !showTopProductSales" type="button" class="text-xs">
                                    <div class="flex items-center justify-end space-x-1">
                                        <span>SALES (₱)</span>
                                        <span class="material-symbols-rounded-small text-gray-400">
                                            keyboard_double_arrow_right
                                        </span>
                                    </div>
                                </button>
                            </th>
                            <th class="px-2 py-3 text-left text-gray-500 font-semibold text-xs sticky top-0 bg-gray-50 w-[12%]" x-show="showTopProductSales" x-cloak>
                                Top Product by Sales
                            </th>
                            <th class="px-2 py-3 text-left font-semibold sticky top-0 bg-gray-50 w-[10%]">COGS (₱)</th>
                            <th class="px-2 py-3 text-left font-semibold sticky top-0 bg-gray-50 w-[13%]">GROSS MARGIN</th>
                            <th class="px-2 py-3 text-left font-semibold sticky top-0 bg-gray-50"></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 text-xs">
                        @forelse($sbc as $input)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-2 py-4 font-medium text-gray-900">{{ $input->category }}</td>
                                <td class="px-2 py-4 text-left font-medium text-gray-900">{{ $input->unit_sold }}</td>
                                <td x-show="showTopProductUnit" x-cloak class="px-3 py-4 text-left font-medium text-gray-900 bg-gray-50">
                                    {{ $input->top_product_unit }}
                                </td>
                                <td class="px-2 py-4 text-left font-medium text-gray-900">₱{{ number_format($input->total_sales, 2 )}}</td>
                                <td x-show="showTopProductSales" x-cloak class="px-3 py-4 text-left font-medium text-gray-900 bg-gray-50">
                                    {{ $input->top_product_sales }}
                                </td>
                                <td class="px-2 py-4 text-left font-medium text-gray-900">₱{{ number_format($input->cogs, 2 )}}</td>
                                <td class="px-2 py-4 text-left font-medium text-gray-900">{{ number_format($input->gross_margin, 0) }}%</td>
                                <td class="px-2 py-4 text-center font-medium text-white text-[10px]
                                    @if($input->number == 1) bg-green-600
                                    @elseif($input->number == 2) bg-yellow-600
                                    @elseif($input->number == 3) bg-blue-600
                                    @elseif($input->number == 4) bg-purple-600
                                    @elseif($input->number == 5) bg-pink-600
                                    @elseif($input->number == 6) bg-red-600
                                    @endif
                                ">
                                    {{ $input->profit_comment }}
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
        <div x-show="tab === 'product-performance'">
            <div class="overflow-y-auto scrollbar-custom h-[39rem]">
                <select wire:model.live="selectedCategory" class="border border-gray-300 rounded px-3 py-2 text-xs mb-4">
                    <option value="all">All</option>
                    @foreach ($category as $c)
                        <option value="{{ $c->cat_id }}">{{ $c->cat_name }}</option>
                    @endforeach
                </select>

                <table id="analysis-table" class="w-full text-xs text-left text-gray-700 border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                    <thead class="uppercase text-xs font-semibold bg-gray-100 text-gray-600">
                        <tr>
                            <th class="cursor-pointer px-4 py-4 text-left" wire:click="sortBy('product_name')">Product ↓☰↑</th>
                            <th class="cursor-pointer px-4 py-4">Unit Sold</th>
                            <th class="cursor-pointer px-4 py-4">Total Sales</th>
                            <th class="cursor-pointer px-4 py-4">COGS</th>
                            <th class="cursor-pointer px-4 py-4">Profit</th>
                            <th class="cursor-pointer px-4 py-4">% Profit Margin</th>
                            <th class="cursor-pointer px-4 py-4">% Share</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($perf as $row)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4">{{ $row->product_name }}</td>
                                <td class="py-3 px-4">{{ $row->unit_sold }} sold, only {{ $row->remaining_stocks }} remain</td>
                                <td class="py-3 px-4">₱{{ number_format($row->total_sales, 2) }}</td>
                                <td class="py-3 px-4">₱{{ number_format($row->cogs, 2) }}</td>
                                <td class="py-3 px-4">₱{{ number_format($row->profit, 2) }}</td>
                                <td class="py-3 px-4">{{ number_format($row->profit_margin_percent, 0) }}%</td>
                                <td class="py-3 px-4">{{ number_format($row->contribution_percent, 1) }}%</td>
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

        <!-- PEAK HOURS -->
        <!-- <div x-show="tab === 'peak-hours'">
        
            <div class="relative flex items-center text-gray-400 mb-4">
                <input type="date" wire:model.live="dateChoice"
                    class="text-xs border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"/>
            </div>
            <div class="overflow-y-auto scrollbar-custom h-[35rem]">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="sticky top-0 bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 sticky top-0 text-left text-xs font-medium uppercase tracking-wider">
                                Day
                            </th>
                            <th scope="col" class="px-6 py-3 sticky top-0 text-left text-xs font-medium uppercase tracking-wider">
                                Time Slot
                            </th>

                            <th scope="col" class="px-6 py-3 sticky top-0 text-right text-xs font-medium uppercase tracking-wider">
                                Transactions
                            </th>
                            <th scope="col" class="px-6 py-3 sticky top-0 text-right text-xs font-medium uppercase tracking-wider">
                                Sales (₱)
                            </th>
                            <th scope="col" class="px-6 py-3 sticky top-0 text-right text-xs font-medium uppercase tracking-wider">
                                Avg. Sales Value (₱)
                            </th>
                        </tr>
                    </thead>
                    
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($peak as $pk)
                            <tr class="">
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-900">{{ $pk->dayName }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-900">{{ $pk->time_slot }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-right">{{ $pk->transactions }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-medium text-green-600 text-right">₱{{ number_format($pk->sales, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-right">₱{{ number_format($pk->avg_value, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="flex flex-col justify-center items-center space-y-1 p-24">
                                        <span class="material-symbols-rounded-semibig text-gray-400">taunt</span>
                                        <span class="text-gray-400 text-xs">Nothing to show.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div> -->
    </div>
</div>
