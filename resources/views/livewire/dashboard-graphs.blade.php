<div>
    <div class="grid grid-cols-2 md:grid-cols-2 gap-4">
        <div class="">
            <div wire:ignore>
                <span id="date" class="text-sm font-medium text-slate-600"></span>
                <span id="clock" class="text-sm font-medium text-slate-600"></span>
            </div>
            <h1 class="text-2xl font-semibold mb-4">Welcome, {{ ucwords($owner_name) }}!</h1>

            <div class="flex gap-3 mb-3 w-full" wire:poll.keep-alive="currencySales()">
                <div class="bg-white border-t-4 border-red-900 p-4 shadow-lg rounded flex-[2] text-center">
                    <p class="text-red-800 text-xl font-bold">₱{{ number_format($dailySales->dailySales, 2) }}</p>
                    <span class="text-gray-600 text-xs font-bold">Daily Sales</span>
                </div>

                <div class="bg-white border-t-4 border-red-700 p-4 shadow-lg rounded flex-[1] text-center" title="₱{{ number_format($weeklySales->weeklySales, 2) }}">
                    <p class="text-red-600 text-xl font-bold" title="₱{{ number_format($weeklySales->weeklySales, 2) }}">
                        ₱{{ $weeklySales->weeklySales >= 1000 ? number_format($weeklySales->weeklySales / 1000, 1) . 'k' : number_format($weeklySales->weeklySales, 2) }}
                    </p>
                    <span class="text-gray-600 text-xs">Last 7 days</span>
                </div>

                <div class="bg-white border-t-4 border-red-500 p-4 shadow-lg rounded flex-[1] text-center" title="₱{{ number_format($monthSales->monthSales, 2) }}">
                    <p class="text-red-400 text-xl font-bold" title="₱{{ number_format($monthSales->monthSales, 2) }}">
                        ₱{{ $monthSales->monthSales >= 1000 ? number_format($monthSales->monthSales / 1000, 1) . 'k' : number_format($monthSales->monthSales, 2) }}
                    </p>
                    <span class="text-gray-600 text-xs">This Month's Sales</span>
                </div>
            </div>

            <div class="flex gap-3 pr-3 w-full">

                <!-- sales by category -->
                <div class="bg-white p-5 rounded shadow border w-[50%] h-[26rem]">
                    <p class="text-left text-black font-semibold text-xs">Sales by Category</p>
                    <div class="flex justify-center gap-4 mt-3">
                        @if($year[0] ?? false)
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 bg-red-600 inline-block rounded-full"></span>
                                <span class="text-xs">{{ $year[0] ?? '' }}</span>
                            </span>
                        @endif

                        @if($year[1] ?? false)
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 bg-blue-600 inline-block rounded-full"></span>
                                <span class="text-xs">{{ $year[1] ?? '' }}</span>
                            </span>

                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 bg-yellow-300 inline-block"></span>
                            <span class="text-xs">Average</span>
                        </span>
                        @endif
                    </div>
                        @if(empty($year[0]))
                                <div class="flex flex-col items-center justify-center py-24 px-8 text-gray-500 text-center">
                                    <span class="material-symbols-rounded-big text-slate-400">bar_chart</span>
                                    <p class="mt-2 text-xs font-semibold">No sales by category found for this year.</p>
                                </div>
                        @else
                            <div wire:poll.keep-alive="salesByCategory" class="hidden"></div>
                            <div class="overflow-x-auto mt-2 scrollbar-custom" >
                                <div id="productChart" 
                                    x-data="{ updating: false }" 
                                    x-init="initProductChart()"

                                    x-on:livewire-processing.self="updating = true"
                                    x-on:livewire-processed.self="initProductChart(); updating = false"

                                    data-categories='@json($categories ?? [])' 
                                    data-products='@json($products ?? [])' 
                                    data-products-prev='@json($productsPrev ?? [])' 
                                    data-products-ave='@json($productsAve ?? [])'
                                    data-year='@json($year ?? [])'

                                    :class="{'opacity-0 transition-opacity duration-150': updating}"
                                    class="relative w-[24rem] h-[20rem]">
                                    <canvas></canvas>
                                </div>
                            </div>
                        @endif
                </div>

                <!-- sales vs loss -->
                <div class="bg-white p-5 rounded shadow border w-[50%]">
                    <p class="text-left text-black font-semibold text-xs">Sales VS Loss - {{ $dateDisplay->format('F') }}</p>
                    <div class="mt-3">
                        @if((end($losses) ?? 0) == 0 && (end($sales) ?? 0) == 0)
                            <div class="flex flex-col items-center justify-center py-24 text-gray-500 text-center">
                                <span class="material-symbols-rounded-big text-slate-400">ssid_chart</span>
                                <p class="mt-2 text-xs font-semibold">No sales or losses recorded for this month yet.</p>
                            </div>
                        @else
                            <div class="py-5 space-y-5">
                                <div class="flex justify-center items-stretch gap-4">
                                    <div class="flex flex-col justify-center text-center px-4">
                                        <p class="text-2xl font-bold text-green-700 leading-none">{{$salesPercentage}}%</p>
                                        <p class="text-xs text-gray-600 mt-1">Sales</p>
                                    </div>
                                    <div class="border-l-2 border-gray-400 self-center h-12"></div>
                                    <div class="flex flex-col justify-center text-center px-4">
                                        <p class="text-2xl font-bold text-red-700 leading-none">{{$lossPercentage}}%</p>
                                        <p class="text-xs text-gray-600 mt-1">Loss</p>
                                    </div>
                                </div>
                                <div wire:poll.keep-alive="salesVSloss" class="hidden"></div>
                                <div id="salesVSlossChart"
                                    x-data="{ updating: false }" 
                                    x-init="initSalesVSLossChart()"

                                    x-on:livewire-processing.self="updating = true"
                                    x-on:livewire-processed.self="initSalesVSLossChart(); updating = false"

                                    data-sales='@json($sales ?? [])' 
                                    data-losses='@json($losses ?? [])'
                                    
                                    :class="{'opacity-0 transition-opacity duration-150': updating}"
                                    class="relative w-full h-[6rem]">
                                    <canvas ></canvas>
                                </div>
                                    
                                <div class="mt-6 space-y-4">
                                    <div class="flex items-start text-[11px] text-gray-700">
                                        <span class="shrink-0 inline-block px-2 py-[1px] rounded-full text-[10px] font-medium w-16 text-center
                                            @if($performanceLabel === 'Excellent') bg-green-500 text-white
                                            @elseif($performanceLabel === 'Good') bg-yellow-400 text-black
                                            @elseif($performanceLabel === 'Warning') bg-orange-500 text-white
                                            @elseif($performanceLabel === 'Critical') bg-red-500 text-white
                                            @else bg-gray-400 text-white @endif">
                                            {{ $performanceLabel }}
                                        </span>
                                        <span class="ml-2 leading-tight text-[11px]">
                                            {{ $insight }}
                                        </span>
                                    </div>
                                    <div class="flex items-start text-[11px] text-gray-700">
                                        <span class="shrink-0 inline-block px-2 py-[1px] rounded-full text-[10px] font-medium w-16 text-center
                                            @if($salesState === 'Positive') bg-green-50 text-green-500 border-green-100 border
                                            @elseif($salesState === 'Negative') bg-red-50 text-red-500 border-red-100 border
                                            @elseif($salesState === 'Start') bg-blue-50 text-blue-500 border-blue-100 border
                                            @else bg-orange-50 text-orange-500 border-orange-100 border @endif">
                                            {{ $salesState }}
                                        </span>
                                        <span class="ml-2 leading-tight text-[11px]">
                                            {{ $salesInsights }}
                                        </span>
                                    </div>
                                    <div class="flex items-start text-xs text-gray-700">
                                        <span class="shrink-0 inline-block px-2 py-[1px] rounded-full text-[10px] font-medium w-16 text-center
                                            @if($lossState === 'Positive') bg-green-50 text-green-500 border-green-100 border
                                            @elseif($lossState === 'Negative') bg-red-50 text-red-500 border-red-100 border
                                            @elseif($lossState === 'Warning') bg-rose-50 text-rose-500 border-rose-100 border
                                            @elseif($salesState === 'Start') bg-blue-50 text-blue-500 border-blue-100 border
                                            @else bg-yellow-50 text-yellow-600 border-yellow-100 border @endif">
                                            {{ $lossState }}
                                        </span>
                                        <span class="ml-2 leading-tight text-[11px]">
                                            {{ $lossInsights }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- monthly net profit -->
        <div class="bg-white p-5 rounded shadow border">
                <p class="text-left text-black font-semibold text-xs border-b border-gray-200 pb-5">Monthly Net Profit</p>
            <div class="flex items-center justify-between pt-4 gap-6">

                <div class="flex flex-col">
                    <span class="text-xl font-bold">
                        {{ $dateDisplay->format('F Y') }}
                    </span>
                    <p class="text-xs">
                        {{ $dateDisplay->format('D, d') }}
                    </p>
                </div>

                <div class="flex flex-col text-right">
                    @if (is_null($profitMonth) || $profitMonth === 0)
                        <span class="text-xl text-red-700">
                            Empty database.
                        </span>
                    @else
                        <span class="text-xl font-bold">
                            ₱{{ number_format($profitMonth, 2) }}
                        </span>
                    @endif
                    <p class="text-xs">Current Net Profit</p>
                </div>

                <div class="flex-1 flex items-center justify-end gap-3">
                    <a href="{{ route('dashboards.owner.expense_record') }}"
                    class="bg-red-100 border border-red-900 px-6 py-2.5 rounded text-xs text-center">
                        View
                    </a>
 
                    <select wire:model.live="selectedYear" wire:change="monthlyNetProfit" id="year"
                        class="rounded px-6 py-2.5 border-gray-300 text-gray-700 text-xs focus:ring focus:ring-blue-200 focus:border-blue-400">
                        @forelse ($year as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @empty
                            <option value="{{ now()->year }}">{{ now()->year }}</option>
                        @endforelse
                    </select>
                </div>
            </div>

            <div class="flex space-x-1 mt-2">
                <button onclick="zoomIn()" id="zoomIn" title="Zoom In">
                    <span class="material-symbols-rounded-small text-sm" title="Zoom In">add_circle</span>
                </button>
                <button onclick="zoomOut()" id="zoomOut" title="Zoom Out"> 
                    <span class="material-symbols-rounded-small text-sm" title="Zoom Out">do_not_disturb_on</span>
                </button>
                <button onclick="resetZoom()" id="zoomReset" title="Reset">
                    <span class="material-symbols-rounded-small text-sm" title="Reset">reset_settings</span>
                </button>
            </div>
            <div wire:poll.keep-alive="monthlyNetProfit" class="hidden"></div>
            <div class="w-full overflow-x-auto mt-3 scrollbar-custom" wire:ignore.self>
                <div 
                    id="profitChart" 
                    x-data="{ updating: false }" 
                    x-init="initProfitChart()"
                    
                    x-on:livewire-processing.self="updating = true"
                    x-on:livewire-processed.self="initProfitChart(); updating = false"
                    
                    data-profits='@json($profits ?? [])'
                    data-months='@json($months ?? [])'
                    
                    :class="{'opacity-0 transition-opacity duration-150': updating}"
                    class="relative w-full h-[24rem]">
                    <canvas></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
