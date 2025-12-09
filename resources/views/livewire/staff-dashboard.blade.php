<div>
    <div class="w-full">
        <div class="gap-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-4">
                <!-- Welcome Section -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Header -->
                    <div class="space-y-1">
                        <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ ucwords($staff_name) }}!</h1>
                    </div>
                    <!-- Sales Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Daily Sales Card -->
                        <div class="bg-gradient-to-br from-red-50 to-white border-l-4 border-red-600 p-6 shadow-sm hover:shadow-md transition-shadow duration-300 rounded-lg group">
                            <div class="flex items-start justify-between mb-3">
                                <div class="p-2 flex items-center justify-center bg-red-100 rounded-lg group-hover:bg-red-200 transition-colors">
                                    <span class="material-symbols-rounded text-red-700">money_bag</span>
                                </div>
                                <span class="text-xs font-semibold text-red-600 bg-red-100 px-2 py-1 rounded-full">TODAY</span>
                            </div>
                            <p class="text-3xl font-bold text-gray-900 mb-1">₱{{ number_format($dailySales->dailySales, 2) }}</p>
                            <span class="text-sm text-gray-600 font-medium">Daily Sales</span>
                        </div>
                        <!-- Weekly Sales Card -->
                        <div class="bg-gradient-to-br from-orange-50 to-white border-l-4 border-orange-500 p-6 shadow-sm hover:shadow-md transition-shadow duration-300 rounded-lg group" title="₱{{ number_format($weeklySales->weeklySales, 2) }}">
                            <div class="flex items-start justify-between mb-3">
                                <div class="p-2 flex items-center justify-center bg-orange-100 rounded-lg group-hover:bg-orange-200 transition-colors">
                                    <span class="material-symbols-rounded text-orange-700">elevation</span>
                                </div>
                                <span class="text-xs font-semibold text-orange-600 bg-orange-100 px-2 py-1 rounded-full">7 DAYS</span>
                            </div>
                            <p class="text-3xl font-bold text-gray-900 mb-1">
                                ₱{{ $weeklySales->weeklySales >= 1000 ? number_format($weeklySales->weeklySales / 1000, 1) . 'k' : number_format($weeklySales->weeklySales, 2) }}
                            </p>
                            <span class="text-sm text-gray-600 font-medium">Weekly Sales</span>
                        </div>
                        <!-- Monthly Sales Card -->
                        <div class="bg-gradient-to-br from-rose-50 to-white border-l-4 border-rose-400 p-6 shadow-sm hover:shadow-md transition-shadow duration-300 rounded-lg group" title="₱{{ number_format($monthSales->monthSales, 2) }}">
                            <div class="flex items-start justify-between mb-3">
                                <div class="p-2 flex items-center justify-center bg-rose-100 rounded-lg group-hover:bg-rose-200 transition-colors">
                                    <span class="material-symbols-rounded text-rose-700">signal_cellular_alt</span>
                                </div>
                                <span class="text-xs font-semibold text-rose-600 bg-rose-100 px-2 py-1 rounded-full">MTD</span>
                            </div>
                            <p class="text-3xl font-bold text-gray-900 mb-1">
                                ₱{{ $monthSales->monthSales >= 1000 ? number_format($monthSales->monthSales / 1000, 1) . 'k' : number_format($monthSales->monthSales, 2) }}
                            </p>
                            <span class="text-sm text-gray-600 font-medium">Monthly Sales</span>
                        </div>
                    </div>
                </div>
                <!-- Highlighted Sales Card -->
                <div class="lg:col-span-1 flex flex-col">
                    <div class="bg-gradient-to-br from-green-600 to-green-700 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex-1 flex flex-col justify-between min-h-0">
                        <div class="flex items-start justify-between mb-4">
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <span class="material-symbols-rounded-semibig text-white">event</span>
                                    <div class="h-10 w-px bg-white/30"></div>
                                    <div>
                                        <p class="text-white/90 text-xs font-medium uppercase tracking-wide">Today's Date</p>
                                        <p class="text-white font-bold text-base">{{ $dateDisplay->format('F j') }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <button class="p-2 bg-white/10 hover:bg-white/20 rounded-lg transition-all duration-200 hover:scale-110 active:scale-95 backdrop-blur-sm border border-white/20 group">
                                <svg class="w-4 h-4 text-white group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-3 mt-auto">
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                                <p class="text-white/80 text-sm font-medium mb-1">Current Sales</p>
                                <p class="text-white font-bold text-3xl tracking-tight">₱{{ number_format($ownCurrentSales->ownDailySales, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="pt-5 w-full ">
        @livewire('stock-alert')
    </div>

    <div class="pt-5 pb-7">
        @livewire('product-analysis')
    </div>

</div>