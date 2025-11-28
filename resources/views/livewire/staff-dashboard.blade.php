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
    
    <div class="flex justtify-between gap-4 pt-10">

        <div wire:poll.7s="pollAll" wire:keep-alive class="hidden"></div>
        <!-- STOCK ALERT -->
        <div class="w-full max-w-sm bg-white shadow-md relative border">
            <div class="relative">
                <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-3/5 z-10">
                    <div class="bg-red-600 text-white text-center py-3 px-6 rounded-full shadow-lg">
                        <span class="text-sm font-semibold uppercase tracking-wide">Stock Alert</span>
                    </div>
                </div>
            </div>
            
            <!-- <div class="hidden" wire:poll.keep-alive="stockAlert()"></div> -->
            <div class="mt-5 p-4 space-y-3 scrollbar-custom transition-all duration-300 ease-in-out h-[29rem]" 
                :class="open ? 'overflow-y-auto' : 'overflow-hidden'">
                @forelse ($prod as $p)
                    <div class="rounded-xl p-2 flex items-center gap-4 border
                        {{ $p->status === 'Critical' ? 'border-red-500 text-red-600' : '' }}
                        {{ $p->status === 'Reorder' ? 'border-orange-500 text-orange-600' : '' }}
                        {{ $p->status === 'Normal' ? 'border-slate-500 text-slate-600' : '' }}">
                        
                        <img src="{{ asset('storage/' . ltrim($p->prod_image, '/')) }}" alt="{{ $p->prod_name }}"
                            class="w-16 h-16 object-cover rounded text-xs">
                        
                        <div class="flex-1">
                            <h3 class="text-xs font-semibold text-gray-800">{{ $p->prod_name }}</h3>
                            <p class="text-xs text-black">Total stocks: {{ $p->total_stock }}</p>
                            <p class="text-xs font-medium">{{ $p->expired_stock }} expired, 
                                <span class="font-bold">{{ $p->remaining_stock }} items left</span>
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full
                                {{ $p->status === 'Critical' ? 'bg-red-500' : '' }}
                                {{ $p->status === 'Reorder' ? 'bg-orange-500' : '' }}
                                {{ $p->status === 'Normal' ? 'bg-slate-500' : '' }}"></span>
                            <span class="font-semibold text-xs">{{ $p->status }}</span>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col justify-center items-center h-full text-center space-y-2 px-8">
                        <span class="material-symbols-rounded-semibig text-gray-400">production_quantity_limits</span>
                        <p class="text-xs text-gray-500">There are currently no products that need restocking.</p>
                    </div>
                @endforelse

            </div>

            <div class="text-left border-t p-2 flex items-center gap-1 mt-2">
                <span class="material-symbols-rounded text-red-600 text-sm">production_quantity_limits</span>
                <span class="text-red-600 text-xs font-medium">
                    {{ $prod->count() }} items needs restock.
                </span>
            </div>
        </div>


        <!-- EXPIRATION -->
        <div class="w-full max-w-sm bg-white rounded shadow-md relative border">
            <div class="relative">
                <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-3/5 z-10">
                    <div class="bg-blue-800 text-white text-center py-3 px-6 rounded-full shadow-lg">
                        <span class="text-sm font-semibold uppercase tracking-wide">Expiration Notice</span>
                    </div>
                </div>
            </div>
            
            <!-- <div class="hidden" wire:poll.keep-alive="expirationNotice()"></div> -->
            <div class="mt-5 p-4 space-y-3 overflow-y-auto scrollbar-custom transition-all duration-300 h-[29rem]">

                @forelse ($expiry as $p)
                    <div class="rounded-xl p-2 flex items-center gap-4 border
                        {{ $p->status === 'Expired' ? 'border-red-900 text-red-900' : '' }}
                        {{ $p->status === 'Critical' ? 'border-red-500 text-red-600' : '' }}
                        {{ $p->status === 'Warning' ? 'border-orange-500 text-orange-600' : '' }}
                        {{ $p->status === 'Monitor' ? 'border-yellow-500 text-yellow-500' : '' }}">
                        
                        <img src="{{ asset('storage/' . ltrim($p->prod_image, '/')) }}" alt="{{ $p->prod_name }}"
                            class="w-16 h-16 object-cover rounded text-xs">
                        
                        <div class="flex-1">
                            <h3 class="text-xs font-semibold text-gray-800">{{ $p->prod_name }}</h3>
                            <p class="text-xs font-medium"><span class="font-semibold">{{ $p->batch_number }}</span> • {{ $p->expired_stock }} items</p>
                            <p class="text-xs font-bold">{{ $p->days_until_expiry }} days left!</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full
                                {{ $p->status === 'Expired' ? 'bg-red-900' : '' }}
                                {{ $p->status === 'Critical' ? 'bg-red-500' : '' }}
                                {{ $p->status === 'Warning' ? 'bg-orange-500' : '' }}
                                {{ $p->status === 'Monitor' ? 'bg-yellow-500' : '' }}"></span>
                            <span class="font-semibold text-xs">{{ $p->status }}</span>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col justify-center items-center h-full text-center space-y-2 px-8">
                        <span class="material-symbols-rounded-semibig text-gray-400">crisis_alert</span>
                        <p class="text-xs text-gray-500">There are currently no products set to expire within the next 60 days.</p>
                    </div>
                @endforelse
            </div>

            <div class="text-left border-t p-2 flex items-center gap-1 mt-2">
                <span class="material-symbols-rounded text-red-600 text-sm">crisis_alert</span>
                <span class="text-red-600 text-xs font-medium">
                    {{ $expiry->count() }} items are set to expire in less than 60 days.
                </span>
            </div>
        </div>


        <!-- TOP SELLING PRODUCT -->
        <div class="w-full bg-white rounded shadow-md relative pt-8">
            <!-- Header Section - Floating Above -->
            <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-full z-10">
                <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 border-b border-green-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-white">Top Selling Products</h2>
                            <p class="text-green-100 text-xs mt-0.5">Best performers this month</p>
                        </div>
                        <div class="bg-green-800 px-4 py-2 rounded-full flex items-center">
                            <span class="text-sm font-bold text-white">{{ $topProd->count() }}</span>
                            <span class="text-green-200 text-xs ml-1">items</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Scrollable Products List -->
            <div class="pt-10 p-4 space-y-3 overflow-y-auto h-[30rem] scrollbar-custom ">
                @forelse ($topProd as $index => $p)
                    @if ($loop->first)
                        <!-- #1 Best Seller Card -->
                        <div class="relative rounded-xl p-2 flex items-center gap-4 border-2 border-yellow-400 bg-gradient-to-r from-yellow-50 to-amber-50 shadow-sm hover:shadow-md transition-shadow">
                            <div class="absolute -top-2 -right-2 bg-yellow-400 text-yellow-900 font-bold text-xs px-3 py-1 rounded-full shadow-md">
                                #1 BEST SELLER
                            </div>

                            <img src="{{ asset('storage/' . ltrim($p->prod_image, '/')) }}" alt="{{ $p->prod_name }}"
                                class="w-16 h-16 object-cover rounded-lg shadow">

                            <div class="flex-1">
                                <h3 class="text-sm font-bold text-gray-900">{{ $p->prod_name }}</h3>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-xs font-semibold text-gray-700">{{ $p->unit_sold }} sold</span>
                                    <span class="text-xs font-semibold text-green-700">₱{{ number_format($p->total_sales,2) }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Regular Product Cards -->
                        <div class="rounded-xl p-2 flex items-center gap-4 border border-slate-200 bg-white hover:border-blue-300 hover:shadow-md transition-all">
                            <div class="bg-slate-100 text-slate-700 font-bold text-sm px-3 py-1 rounded-lg">
                                #{{ $loop->iteration }}
                            </div>

                            <img src="{{ asset('storage/' . ltrim($p->prod_image, '/')) }}" alt="{{ $p->prod_name }}"
                                class="w-16 h-16 object-cover rounded-lg">

                            <div class="flex-1">
                                <h3 class="text-xs font-semibold text-gray-800">{{ $p->prod_name }}</h3>
                                <div class="flex items-center gap-3 mt-1.5">
                                    <span class="text-xs text-gray-600">{{ $p->unit_sold }} sold</span>
                                    <span class="text-xs font-semibold text-green-600">₱{{ number_format($p->total_sales,2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="flex flex-col justify-center items-center h-full text-center space-y-2 px-8">
                        <span class="material-symbols-rounded-semibig text-gray-400">workspace_premium</span>
                        <p class="text-xs text-gray-500">Nothing to show.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>