<div>
    <div class="grid grid-cols-2 md:grid-cols-2 gap-4">

        <div class="">
            <h1 class="text-3xl font-bold mb-4">Welcome back, {{ ucwords($owner_name) }}!</h1>
            <div class="flex gap-3 mb-4 w-full grid-cols-3">
                <div class="bg-gradient-to-br from-red-50 to-white border border-red-100 p-3 shadow-sm hover:shadow-md transition-shadow duration-200 rounded-xl flex-[2]">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-red-800 uppercase tracking-wide">Today's Sales</span>
                        <span class="material-symbols-rounded text-red-600 text-xl">trending_up</span>
                    </div>
                    <p class="text-red-900 text-2xl font-bold">₱{{ number_format($dailySales->dailySales, 2) }}</p>
                </div>

                <div class="bg-gradient-to-br from-orange-50 to-white border border-orange-100 p-3 shadow-sm hover:shadow-md transition-shadow duration-200 rounded-xl flex-[1]" title="₱{{ number_format($weeklySales->weeklySales, 2) }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-orange-800 uppercase tracking-wide">Last 7 Days</span>
                    </div>
                    <p class="text-orange-900 text-2xl font-bold" title="₱{{ number_format($weeklySales->weeklySales, 2) }}">
                        ₱{{ $weeklySales->weeklySales >= 1000 ? number_format($weeklySales->weeklySales / 1000, 1) . 'k' : number_format($weeklySales->weeklySales, 2) }}
                    </p>
                </div>

                <div class="bg-gradient-to-br from-rose-50 to-white border border-rose-100 p-3 shadow-sm hover:shadow-md transition-shadow duration-200 rounded-xl flex-[1]" title="₱{{ number_format($monthSales->monthSales, 2) }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-rose-800 uppercase tracking-wide">Monthly</span>
                    </div>
                    <p class="text-rose-900 text-2xl font-bold" title="₱{{ number_format($monthSales->monthSales, 2) }}">
                        ₱{{ $monthSales->monthSales >= 1000 ? number_format($monthSales->monthSales / 1000, 1) . 'k' : number_format($monthSales->monthSales, 2) }}
                    </p>
                </div>
            </div>

            <div wire:poll.3s="pollAll" wire:keep-alive class="hidden"></div>
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
                        @else
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 bg-red-600 inline-block rounded-full"></span>
                                <span class="text-xs">{{ $year[0] ?? now()->year }}</span>
                            </span>
                        @endif

                        @if($year[1] ?? false)
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 bg-blue-600 inline-block rounded-full"></span>
                                <span class="text-xs">{{ $year[1] ?? '' }}</span>
                            </span>
                        @endif
                            @if(($year[0] ?? false) || ($year[1] ?? false))
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 bg-yellow-300 inline-block"></span>
                                    <span class="text-xs">Average</span>
                                </span>
                            @endif
                        </span>
                    </div>
                    <div class="overflow-x-auto mt-2 scrollbar-custom">
                        <div class="relative w-[50rem] h-[20rem]">
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
                                class="w-[50rem] h-[20rem]">
                                <canvas></canvas>
                            </div>

                            <!-- Overlaid Message -->
                           @if(empty($year) || count($year) === 0)
                            <p class="absolute inset-0 flex items-center justify-center 
                                    text-xs font-semibold text-gray-600">
                                No sales by category found for this year yet.
                            </p>
                            @endif
                        </div>
                    </div>

                </div>

                <!-- sales vs loss -->
                <div class="bg-white p-5 rounded shadow border w-[50%] h-[26rem]">
                    <div class="flex justify-between">
                        <p class="text-left text-black font-semibold text-xs"> Sales VS Loss - {{ \Carbon\Carbon::createFromDate(null, $selectedMonthSL, 1)->format('F') }}</p>
                        <!-- <div class="gap-2 flex">
                            <a wire:click="changeMonth(-1)" class="cursor-pointer hover:bg-slate-100 rounded">
                                <span class="material-symbols-rounded">arrow_back</span>
                            </a>

                            <a wire:click="changeMonth(1)" class="cursor-pointer hover:bg-slate-100 rounded">
                                <span class="material-symbols-rounded">arrow_forward</span>
                            </a>
                        </div> -->
                    </div>
                    <div class="mt-3">
                        @if((count($losses) ?? 0) == 0 && (count($sales) ?? 0) == 0)
                            <div class="flex flex-col items-center justify-center py-24 text-gray-500 text-center">
                                <span class="material-symbols-rounded-big text-slate-400">ssid_chart</span>
                                <p class="mt-2 text-xs font-semibold">No sales or losses recorded for this month yet.</p>
                            </div>
                        @else
                            <div class="py-5 space-y-4">
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
                                    
                                <div class="mt-5 space-y-4">
                                    <div class="flex items-start text-xs text-gray-700">
                                        <span class="shrink-0 inline-block px-2 py-[1px] rounded-full text-[11px] font-medium w-16 text-center
                                            @if($performanceLabel === 'Excellent') bg-green-500 text-white
                                            @elseif($performanceLabel === 'Good') bg-yellow-400 text-black
                                            @elseif($performanceLabel === 'Warning') bg-orange-500 text-white
                                            @elseif($performanceLabel === 'Critical') bg-red-500 text-white
                                            @elseif($performanceLabel === 'Start') bg-blue-500 text-white
                                            @else bg-gray-400 text-white @endif">
                                            {{ $performanceLabel }}
                                        </span>
                                        <span class="ml-2 leading-tight text-[11px]">
                                            {{ $insight }}
                                        </span>
                                    </div>
                                    <div class="flex items-start text-[11px] text-gray-700">
                                        <span class="shrink-0 inline-block px-2 py-[1px] rounded-full text-[11px] font-medium w-16 text-center
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
                                    <div class="flex items-start text-[11px] text-gray-700">
                                        <span class="shrink-0 inline-block px-2 py-[1px] rounded-full text-[11px] font-medium w-16 text-center
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
            @livewire('monthly-net-profit-graph')
        </div>
    </div>
</div>
