<div class="w-full">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">
    <!-- Welcome Section -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Header -->
        <div class="space-y-1">
            <p class="text-sm text-gray-500 font-medium">{{ $dateDisplay->format('l, F j, Y') }}</p>
            <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ ucwords($staff_name) }}!</h1>
        </div>

        <!-- Sales Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Daily Sales Card -->
            <div class="bg-gradient-to-br from-red-50 to-white border-l-4 border-red-600 p-6 shadow-sm hover:shadow-md transition-shadow duration-300 rounded-lg group">
                <div class="flex items-start justify-between mb-3">
                    <div class="p-2 bg-red-100 rounded-lg group-hover:bg-red-200 transition-colors">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-red-600 bg-red-100 px-2 py-1 rounded-full">TODAY</span>
                </div>
                <p class="text-3xl font-bold text-gray-900 mb-1">₱{{ number_format($dailySales->dailySales, 2) }}</p>
                <span class="text-sm text-gray-600 font-medium">Daily Sales</span>
            </div>

            <!-- Weekly Sales Card -->
            <div class="bg-gradient-to-br from-orange-50 to-white border-l-4 border-orange-500 p-6 shadow-sm hover:shadow-md transition-shadow duration-300 rounded-lg group" title="₱{{ number_format($weeklySales->weeklySales, 2) }}">
                <div class="flex items-start justify-between mb-3">
                    <div class="p-2 bg-orange-100 rounded-lg group-hover:bg-orange-200 transition-colors">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
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
                    <div class="p-2 bg-rose-100 rounded-lg group-hover:bg-rose-200 transition-colors">
                        <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
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
    <div class="lg:col-span-1">
        <div class="bg-gradient-to-br from-green-600 to-green-700 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 h-full flex flex-col justify-between">
            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <span class="material-symbols-rounded text-white text-4xl">event</span>
                        <div class="h-12 w-px bg-white/30"></div>
                        <div>
                            <p class="text-white/90 text-xs font-medium uppercase tracking-wide">Today's Date</p>
                            <p class="text-white font-bold text-lg">{{ $dateDisplay->format('F j') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Amount -->
            <div class="space-y-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <p class="text-white/80 text-sm font-medium mb-1">Current Sales</p>
                    <p class="text-white font-bold text-4xl tracking-tight">₱{{ number_format($dailySales->dailySales, 2) }}</p>
                </div>

                <!-- Action Button -->
                <button class="w-full bg-white text-green-700 px-6 py-3 rounded-lg font-semibold text-sm hover:bg-green-50 active:bg-green-100 transition-colors duration-200 shadow-md hover:shadow-lg flex items-center justify-center space-x-2">
                    <span>View Details</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    </div>


    <div class="flex justify-between gap-4 pt-5 w-full">
    <!-- STOCK ALERT -->
    <div class="w-full max-w-sm bg-white shadow-md relative border rounded-lg">
        <div class="relative">
            <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-3/5 z-10">
                <div class="bg-red-600 text-white text-center py-3 px-6 rounded-full shadow-lg">
                    <span class="text-sm font-semibold uppercase tracking-wide">Stock Alert</span>
                </div>
            </div>
        </div>
        
        <div class="mt-5 p-4 space-y-3 scrollbar-custom overflow-y-auto transition-all duration-300 ease-in-out h-[29rem]">
            <!-- Critical Stock Item -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-red-500 text-red-600">
                <img src="https://images.unsplash.com/photo-1587049352846-4a222e784422?w=100&h=100&fit=crop" alt="Pain Relief Tablets"
                    class="w-16 h-16 object-cover rounded text-xs">
                
                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Pain Relief Tablets 500mg</h3>
                    <p class="text-xs text-black">Total stocks: 15</p>
                    <p class="text-xs font-medium">2 expired, 
                        <span class="font-bold">13 items left</span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span class="font-semibold text-xs">Critical</span>
                </div>
            </div>

            <!-- Reorder Item -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-orange-500 text-orange-600">
                <img src="https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=100&h=100&fit=crop" alt="Vitamin C"
                    class="w-16 h-16 object-cover rounded text-xs">
                
                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Vitamin C 1000mg</h3>
                    <p class="text-xs text-black">Total stocks: 45</p>
                    <p class="text-xs font-medium">5 expired, 
                        <span class="font-bold">40 items left</span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                    <span class="font-semibold text-xs">Reorder</span>
                </div>
            </div>

            <!-- Critical Stock Item -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-red-500 text-red-600">
                <img src="https://images.unsplash.com/photo-1471864190281-a93a3070b6de?w=100&h=100&fit=crop" alt="Cough Syrup"
                    class="w-16 h-16 object-cover rounded text-xs">
                
                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Cough Syrup 100ml</h3>
                    <p class="text-xs text-black">Total stocks: 8</p>
                    <p class="text-xs font-medium">1 expired, 
                        <span class="font-bold">7 items left</span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span class="font-semibold text-xs">Critical</span>
                </div>
            </div>

            <!-- Reorder Item -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-orange-500 text-orange-600">
                <img src="https://images.unsplash.com/photo-1550572017-edd951aa8f72?w=100&h=100&fit=crop" alt="Antibiotic"
                    class="w-16 h-16 object-cover rounded text-xs">
                
                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Amoxicillin 500mg</h3>
                    <p class="text-xs text-black">Total stocks: 32</p>
                    <p class="text-xs font-medium">3 expired, 
                        <span class="font-bold">29 items left</span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                    <span class="font-semibold text-xs">Reorder</span>
                </div>
            </div>

            <!-- Normal Stock Item -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-slate-500 text-slate-600">
                <img src="https://images.unsplash.com/photo-1587854692152-cbe660dbde88?w=100&h=100&fit=crop" alt="Multivitamins"
                    class="w-16 h-16 object-cover rounded text-xs">
                
                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Multivitamins Daily</h3>
                    <p class="text-xs text-black">Total stocks: 120</p>
                    <p class="text-xs font-medium">0 expired, 
                        <span class="font-bold">120 items left</span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                    <span class="font-semibold text-xs">Normal</span>
                </div>
            </div>
        </div>

        <div class="text-left border-t p-2 flex items-center gap-1 mt-2">
            <span class="material-symbols-rounded text-red-600 text-sm">production_quantity_limits</span>
            <span class="text-red-600 text-xs font-medium">
                5 items needs restock.
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
        
        <div class="mt-5 p-4 space-y-3 overflow-y-auto scrollbar-custom transition-all duration-300 h-[29rem]">
            <!-- Expired Item -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-red-900 text-red-900">
                <img src="https://images.unsplash.com/photo-1587049352846-4a222e784422?w=100&h=100&fit=crop" alt="Pain Relief"
                    class="w-16 h-16 object-cover rounded text-xs">
                
                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Ibuprofen 400mg</h3>
                    <p class="text-xs font-medium">8 items</p>
                    <p class="text-xs font-bold">0 days left!</p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-900"></span>
                    <span class="font-semibold text-xs">Expired</span>
                </div>
            </div>

            <!-- Critical - Expires Soon -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-red-500 text-red-600">
                <img src="https://images.unsplash.com/photo-1471864190281-a93a3070b6de?w=100&h=100&fit=crop" alt="Cough Medicine"
                    class="w-16 h-16 object-cover rounded text-xs">
                
                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Cough Syrup 100ml</h3>
                    <p class="text-xs font-medium">12 items</p>
                    <p class="text-xs font-bold">5 days left!</p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span class="font-semibold text-xs">Critical</span>
                </div>
            </div>

            <!-- Warning -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-orange-500 text-orange-600">
                <img src="https://images.unsplash.com/photo-1550572017-edd951aa8f72?w=100&h=100&fit=crop" alt="Antibiotics"
                    class="w-16 h-16 object-cover rounded text-xs">
                
                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Amoxicillin 500mg</h3>
                    <p class="text-xs font-medium">25 items</p>
                    <p class="text-xs font-bold">15 days left!</p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                    <span class="font-semibold text-xs">Warning</span>
                </div>
            </div>

            <!-- Monitor -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-yellow-500 text-yellow-500">
                <img src="https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=100&h=100&fit=crop" alt="Vitamins"
                    class="w-16 h-16 object-cover rounded text-xs">
                
                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Vitamin C 1000mg</h3>
                    <p class="text-xs font-medium">50 items</p>
                    <p class="text-xs font-bold">35 days left!</p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                    <span class="font-semibold text-xs">Monitor</span>
                </div>
            </div>

            <!-- Monitor -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-yellow-500 text-yellow-500">
                <img src="https://images.unsplash.com/photo-1587854692152-cbe660dbde88?w=100&h=100&fit=crop" alt="Supplements"
                    class="w-16 h-16 object-cover rounded text-xs">
                
                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Omega-3 Fish Oil</h3>
                    <p class="text-xs font-medium">60 items</p>
                    <p class="text-xs font-bold">42 days left!</p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                    <span class="font-semibold text-xs">Monitor</span>
                </div>
            </div>
        </div>

        <div class="text-left border-t p-2 flex items-center gap-1 mt-2">
            <span class="material-symbols-rounded text-red-600 text-sm">crisis_alert</span>
            <span class="text-red-600 text-xs font-medium">
                5 items are set to expire in less than 60 days.
            </span>
        </div>
    </div>

    <!-- TOP SELLING PRODUCT -->
    <div class="w-full bg-white rounded shadow-md relative pt-8">
        <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-full z-10">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 border-b border-green-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-white">Top Selling Products</h2>
                        <p class="text-green-100 text-xs mt-0.5">Best performers this month</p>
                    </div>
                    <div class="bg-green-800 px-4 py-2 rounded-full flex items-center">
                        <span class="text-sm font-bold text-white">8</span>
                        <span class="text-green-200 text-xs ml-1">items</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="pt-10 p-4 space-y-3 overflow-y-auto h-[30rem] scrollbar-custom">
            <!-- #1 Best Seller -->
            <div class="relative rounded-xl p-2 flex items-center gap-4 border-2 border-yellow-400 bg-gradient-to-r from-yellow-50 to-amber-50 shadow-sm hover:shadow-md transition-shadow">
                <div class="absolute -top-2 -right-2 bg-yellow-400 text-yellow-900 font-bold text-xs px-3 py-1 rounded-full shadow-md">
                    #1 BEST SELLER
                </div>

                <img src="https://images.unsplash.com/photo-1587049352846-4a222e784422?w=100&h=100&fit=crop" alt="Pain Relief"
                    class="w-16 h-16 object-cover rounded-lg shadow">

                <div class="flex-1">
                    <h3 class="text-sm font-bold text-gray-900">Pain Relief Tablets 500mg</h3>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs font-semibold text-gray-700">1,245 sold</span>
                        <span class="text-xs font-semibold text-green-700">₱62,250.00</span>
                    </div>
                </div>
            </div>

            <!-- #2 -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-slate-200 bg-white hover:border-blue-300 hover:shadow-md transition-all">
                <div class="bg-slate-100 text-slate-700 font-bold text-sm px-3 py-1 rounded-lg">
                    #2
                </div>

                <img src="https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=100&h=100&fit=crop" alt="Vitamin C"
                    class="w-16 h-16 object-cover rounded-lg">

                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Vitamin C 1000mg</h3>
                    <div class="flex items-center gap-3 mt-1.5">
                        <span class="text-xs text-gray-600">980 sold</span>
                        <span class="text-xs font-semibold text-green-600">₱49,000.00</span>
                    </div>
                </div>
            </div>

            <!-- #3 -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-slate-200 bg-white hover:border-blue-300 hover:shadow-md transition-all">
                <div class="bg-slate-100 text-slate-700 font-bold text-sm px-3 py-1 rounded-lg">
                    #3
                </div>

                <img src="https://images.unsplash.com/photo-1471864190281-a93a3070b6de?w=100&h=100&fit=crop" alt="Cough Syrup"
                    class="w-16 h-16 object-cover rounded-lg">

                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Cough Syrup 100ml</h3>
                    <div class="flex items-center gap-3 mt-1.5">
                        <span class="text-xs text-gray-600">856 sold</span>
                        <span class="text-xs font-semibold text-green-600">₱42,800.00</span>
                    </div>
                </div>
            </div>

            <!-- #4 -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-slate-200 bg-white hover:border-blue-300 hover:shadow-md transition-all">
                <div class="bg-slate-100 text-slate-700 font-bold text-sm px-3 py-1 rounded-lg">
                    #4
                </div>

                <img src="https://images.unsplash.com/photo-1550572017-edd951aa8f72?w=100&h=100&fit=crop" alt="Antibiotics"
                    class="w-16 h-16 object-cover rounded-lg">

                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Amoxicillin 500mg</h3>
                    <div class="flex items-center gap-3 mt-1.5">
                        <span class="text-xs text-gray-600">742 sold</span>
                        <span class="text-xs font-semibold text-green-600">₱37,100.00</span>
                    </div>
                </div>
            </div>

            <!-- #5 -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-slate-200 bg-white hover:border-blue-300 hover:shadow-md transition-all">
                <div class="bg-slate-100 text-slate-700 font-bold text-sm px-3 py-1 rounded-lg">
                    #5
                </div>

                <img src="https://images.unsplash.com/photo-1587854692152-cbe660dbde88?w=100&h=100&fit=crop" alt="Multivitamins"
                    class="w-16 h-16 object-cover rounded-lg">

                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Multivitamins Daily</h3>
                    <div class="flex items-center gap-3 mt-1.5">
                        <span class="text-xs text-gray-600">685 sold</span>
                        <span class="text-xs font-semibold text-green-600">₱34,250.00</span>
                    </div>
                </div>
            </div>

            <!-- #6 -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-slate-200 bg-white hover:border-blue-300 hover:shadow-md transition-all">
                <div class="bg-slate-100 text-slate-700 font-bold text-sm px-3 py-1 rounded-lg">
                    #6
                </div>

                <img src="https://images.unsplash.com/photo-1585435557343-3b092031a831?w=100&h=100&fit=crop" alt="Allergy Relief"
                    class="w-16 h-16 object-cover rounded-lg">

                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Antihistamine 10mg</h3>
                    <div class="flex items-center gap-3 mt-1.5">
                        <span class="text-xs text-gray-600">542 sold</span>
                        <span class="text-xs font-semibold text-green-600">₱27,100.00</span>
                    </div>
                </div>
            </div>

            <!-- #7 -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-slate-200 bg-white hover:border-blue-300 hover:shadow-md transition-all">
                <div class="bg-slate-100 text-slate-700 font-bold text-sm px-3 py-1 rounded-lg">
                    #7
                </div>

                <img src="https://images.unsplash.com/photo-1631549916768-4119b2e5f926?w=100&h=100&fit=crop" alt="Pain Cream"
                    class="w-16 h-16 object-cover rounded-lg">

                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Muscle Pain Relief Gel</h3>
                    <div class="flex items-center gap-3 mt-1.5">
                        <span class="text-xs text-gray-600">485 sold</span>
                        <span class="text-xs font-semibold text-green-600">₱24,250.00</span>
                    </div>
                </div>
            </div>

            <!-- #8 -->
            <div class="rounded-xl p-2 flex items-center gap-4 border border-slate-200 bg-white hover:border-blue-300 hover:shadow-md transition-all">
                <div class="bg-slate-100 text-slate-700 font-bold text-sm px-3 py-1 rounded-lg">
                    #8
                </div>

                <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=100&h=100&fit=crop" alt="Eye Drops"
                    class="w-16 h-16 object-cover rounded-lg">

                <div class="flex-1">
                    <h3 class="text-xs font-semibold text-gray-800">Eye Drops 10ml</h3>
                    <div class="flex items-center gap-3 mt-1.5">
                        <span class="text-xs text-gray-600">412 sold</span>
                        <span class="text-xs font-semibold text-green-600">₱20,600.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>