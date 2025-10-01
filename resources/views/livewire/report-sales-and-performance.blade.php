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
            @click="tab = 'top-selling'"
            :class="tab === 'top-selling' 
                ? 'bg-yellow-50 text-black border-yellow-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Top Selling Product
        </button>

        <button 
            @click="tab = 'sales-category'"
            :class="tab === 'sales-category' 
                ? 'bg-orange-50 text-black border-orange-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Sales by Category
        </button>

        <button 
            @click="tab = 'peak-hours'"
            :class="tab === 'peak-hours' 
                ? 'bg-blue-50 text-black border-blue-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Peak Hours
        </button>
    </div>

    <div class="border bg-white p-4 rounded-b-lg mb-3 h-[40rem]"
        :class="{
            'border-green-500 bg-green-50': tab === 'sales',
            'border-yellow-500 bg-yellow-50': tab === 'top-selling',
            'border-orange-500 bg-orange-50': tab === 'sales-category',
            'border-blue-500 bg-blue-50': tab === 'peak-hours'
        }">

        <!-- DAILY SALES or MONTHLY -->
        <div x-show="tab === 'sales'">
            <p class="text-gray-700">📊 <b>Sales</b> report content goes here.</p>
        </div>

        <!-- TOP SELLING -->
        <div x-show="tab === 'top-selling'">
            <p class="text-gray-700">⚡ <b>top selling</b> report content goes here.</p>
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
                        class="rounded border border-gray-400 pl-10 pr-3 py-2 text-xs focus:ring focus:ring-blue-200 text-black"
                    >
                </div>

                <div class="relative">
                    <button @click="open = !open" type="button" class="p-2">
                        <span class="material-symbols-rounded text-orange-500 hover:text-black" title="Filter">discover_tune</span>
                    </button>
                    
                    <div x-show="open" x-cloak @click.away="open = false" 
                        class="absolute top-full right-0 mt-2 w-64 bg-white border border-orange-200 rounded-xl shadow-lg z-50 p-4 space-y-4">

                        <div>
                            <span class="text-[11px] font-semibold text-orange-700">Year:</span>
                            <div class="grid grid-cols-3 gap-2 mt-2">
                                @foreach($years as $yr)
                                    <label class="flex items-center justify-center cursor-pointer">
                                        <input 
                                            type="radio" 
                                            name="selectedYear" 
                                            value="{{ $yr->year }}" 
                                            wire:model="selectedYearSingle" 
                                            class="hidden peer"
                                        >
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
                <table class="text-sm text-left w-full">
                    <thead class="bg-gray-50">
                        <tr class="text-gray-700 uppercase text-xs tracking-wider border-b">
                            <th class="px-6 py-3 font-semibold text-xs sticky top-0 bg-gray-50">Category</th>
                            <th class="px-6 py-3 text-right font-semibold sticky top-0 bg-gray-50">Units Sold</th>
                            <th class="px-6 py-3 text-right font-semibold sticky top-0 bg-gray-50">Total Sales</th>
                            <!-- <th class="px-6 py-3 text-right font-semibold sticky top-0 bg-gray-50">Avg. Price/Unit</th> -->
                            <th class="px-6 py-3 text-right font-semibold sticky top-0 bg-gray-50">Gross Margin %</th>
                            <th class="px-6 py-3 text-right font-semibold sticky top-0 bg-gray-50">Stock Turnover</th>
                            <th class="px-6 py-3 text-right font-semibold sticky top-0 bg-gray-50 relative"
                                x-data="{ open: false }">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Growth</span>
                                    <button @click="open = !open" class="p-1 rounded hover:bg-gray-200">
                                    <span class="material-symbols-rounded">more_vert</span>
                                    </button>
                                </div>

                                <div x-show="open" x-cloak @click.away="open = false" class="absolute right-5 mt-2 w-40 bg-white border border-gray-200 rounded shadow-lg z-50">
                                    <div class="flex flex-col text-xs text-gray-700">
                                        <button wire:click="fetchLastMonth" class="inline-flex items-center px-3 py-3 text-left rounded hover:bg-gray-100">
                                            From last Month
                                        </button>
                                        <button wire:click="fetchLastYear" class="inline-flex items-center px-3 py-3 text-left rounded hover:bg-gray-100">
                                            From last Year
                                        </button>
                                    </div>
                                </div>
                            </th>

                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-xs">
                        @forelse($sbc as $input)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $input->category }}</td>
                            <td class="px-6 py-4 text-right">{{ $input->unit_sold }}</td>
                            <td class="px-6 py-4 text-right">{{ number_format($input->total_sales, 2) }}</td>
                            <!-- <td class="px-6 py-4 text-right">40%</td> -->
                            <!-- <td class="px-6 py-4 text-right">₱69.10</td> -->
                            <td class="px-6 py-4 text-right
                                @if($input->gross_margin >= 20)
                                    text-green-600 font-bold
                                @elseif($input->gross_margin >= 10)
                                    text-yellow-600 font-semibold
                                @else
                                    text-red-600 font-semibold
                                @endif">
                                
                                @if($input->gross_margin >= 20)
                                    High
                                @elseif($input->gross_margin >= 10)
                                    Medium
                                @else
                                    Low
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right">3.5x</td>
                            <td class="px-6 py-4 text-right text-green-600 font-semibold">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="flex flex-col justify-center items-center space-y-1 pt-8">
                                    <span class="material-symbols-rounded-semibig text-gray-400">taunt</span>
                                    <span class="text-gray-400">Nothing to show.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PEAK HOURS -->
        <div x-show="tab === 'peak-hours'">
            <p class="text-gray-700"> <b>this is peak hours</b> report content goes here.</p>
        </div>
    </div>
</div>
