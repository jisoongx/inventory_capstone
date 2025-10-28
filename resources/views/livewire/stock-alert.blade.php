<div class="flex justtify-between gap-4 pt-5">

    <div wire:poll.15s="pollAll" wire:keep-alive class="hidden"></div>
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
                    <p class="text-xs text-gray-500">There are currently no products set to expire within the next 60 days.</p>
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
                        <p class="text-xs font-medium">{{ $p->expired_stock }} items</p>
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
