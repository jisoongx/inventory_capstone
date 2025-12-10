@extends('dashboards.owner.owner')

@section('content')

<div class="md:p-4 space-y-5 ">

    <div class="flex flex-col md:flex-row md:gap-4">

        @if($expired || $plan === 3)
            <div class="ml-64 absolute inset-0 flex items-center justify-center z-10">
                <div class="bg-white rounded-lg shadow-2xl border border-red-200 overflow-hidden max-w-[35rem] mx-4">

                    <div class="px-8 py-12 text-center relative">
                        <div class="absolute inset-0 overflow-hidden opacity-5">
                            <div class="absolute top-5 left-5 w-24 h-24 bg-red-600 rounded-full blur-2xl"></div>
                            <div class="absolute bottom-5 right-5 w-32 h-32 bg-rose-600 rounded-full blur-2xl"></div>
                        </div>

                        <div class="relative z-10">
                            <div class="relative inline-block mb-4">
                                <div class="absolute inset-0 bg-amber-500/30 rounded-full blur-2xl animate-pulse"></div>
                                <div class="relative w-16 h-16 bg-gradient-to-br from-orange-600 to-rose-600 rounded-full p-4 shadow-2xl flex items-center justify-center">
                                    <span class="material-symbols-rounded-semibig text-white">diamond</span>
                                </div>
                                <div class="absolute -top-1 -right-1">
                                    <svg class="w-6 h-6 text-amber-400 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </div>
                            </div>

                            <h2 class="text-xl md:text-md font-bold text-slate-800 mb-3">
                                Smart Restocking, Seasonal Insights, and Product Performance features are available on Standard and Premium Plans
                            </h2>

                            <p class="text-slate-600 text-xs xs:text-base leading-relaxed mb-6">
                                Upgrade your subscription to access advanced inventory intelligence — including 
                                automated restock suggestions, seasonal sales trend tracking, and
                                product performance analysis. Make informed decisions, stay ahead of demand, and 
                                maximize profitability with data-driven insights.
                            </p>

                            <div class="flex flex-wrap items-center justify-center gap-3 mb-8 text-xs md:text-sm text-slate-700">
                                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full shadow-sm border border-red-100">
                                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-xs font-medium">Real-time Tracking</span>
                                </div>
                                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full shadow-sm border border-red-100">
                                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-xs font-medium">Export Reports</span>
                                </div>
                                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full shadow-sm border border-red-100">
                                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-xs font-medium">Automation</span>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                                <a href="{{ route('subscription.selection' )}}" 
                                    class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-orange-600 to-rose-600 text-white font-semibold rounded-lg hover:from-red-700 hover:to-rose-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl group text-sm">
                                    <span class="text-xs">Upgrade to Now!</span>
                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                            </div>

                            <div class="mt-6 pt-4 border-t border-red-100">
                                <div class="flex flex-wrap items-center justify-center gap-4 text-xs text-slate-500">
                                    <span class="flex items-center gap-1.5">
                                        <span class="material-symbols-rounded-smaller text-red-500">encrypted</span>
                                        <span>Secure payments</span>
                                    </span>
                                    <span class="text-red-200">•</span>
                                    <span class="flex items-center gap-1.5">
                                        <span class="material-symbols-rounded-smaller text-red-500">check_circle</span>
                                        <span>Instant activation</span>
                                    </span>
                                    <span class="text-red-200">•</span>
                                    <span class="flex items-center gap-1.5">
                                        <span class="material-symbols-rounded-smaller text-red-500">handshake</span>
                                        <span>24/7 support</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Restock Card Wrapper -->
        <div class="relative flex-1">
            <!-- Restock Card -->
            <div class="{{ ($expired || $plan === 1) ? 'filter blur-sm pointer-events-none select-none' : '' }} bg-white rounded md:p-6 h-full flex flex-col md:flex-row items-center gap-4 md:gap-6 shadow-lg hover:shadow-xl transition-all duration-300">
                <!-- Top Green Bar -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gray-700 rounded-t-2xl"></div>
                <!-- Left Icon -->
                <div class="flex-shrink-0 w-20 md:w-24 text-green-500">
                    <img src="{{ asset('assets/restock-icon.png') }}" alt="Restock" class="w-full h-auto object-contain bg-transparent">
                </div>
                <!-- Text Section -->
                <div class="flex-1 min-w-0">
                    <h2 class="text-gray-800 text-base md:text-lg font-semibold">Wondering what to restock?</h2>
                    <p class="text-gray-600 mt-1 text-xs md:text-sm leading-relaxed">Get intelligent suggestions for your store so you never run out of popular products.</p>
                    <a href="{{ ($expired || $plan === 1) ? '' : route('restock_suggestion') }}"
                        class="mt-3 inline-block px-4 py-2 rounded-lg font-medium text-sm shadow transition-all
                            {{ ($expired || $plan === 1)
                                ? 'bg-gray-400 text-gray-200 cursor-not-allowed' 
                                : 'bg-green-500 text-white hover:bg-green-600 hover:shadow-md' }}"
                        onclick="{{ ($expired || $plan === 1) ? 'event.preventDefault();' : '' }}">
                        Generate List
                    </a>
                </div>
                <!-- Right Product Image -->
                <div class="flex-shrink-0 w-32 md:w-48">
                    <img src="{{ asset('assets/products.png') }}" alt="Products" class="w-full h-auto object-contain rounded-lg bg-transparent">
                </div>
            </div>
            @if($plan === 1 && !$expired)
            <div class="absolute inset-0 flex items-center justify-center rounded">
                <div class="bg-white rounded-lg p-4 shadow-xl text-center w-64 border border-red-300">
                    <div class="bg-gradient-to-r from-red-500 to-red-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-rounded text-white text-2xl">lock</span>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Premium Feature</h3>
                    <p class="text-gray-600 text-xs mb-3">
                        Upgrade to <span class="font-semibold text-red-600">Premium</span>
                    </p>
                    <a href="{{ route('subscription.selection') }}" class="inline-block bg-gradient-to-r from-red-500 to-red-600 text-white px-5 py-2 rounded-lg text-xs font-semibold hover:shadow-lg transition-all">
                        Upgrade Now
                    </a>
                </div>
            </div>
            @endif
            @if($expired)
            <div class="absolute inset-0 flex items-center justify-center rounded">
                <div class="bg-white rounded-lg p-4 shadow-xl text-center w-64 border border-red-300">
                    <div class="bg-gradient-to-r from-red-500 to-red-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-rounded text-white text-2xl">error</span>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Subscription Expired</h3>
                    <p class="text-gray-600 text-xs mb-3">
                        Renew to <span class="font-semibold text-red-600">continue</span>
                    </p>
                    <a href="{{ route('subscription.selection') }}" class="inline-block bg-gradient-to-r from-red-500 to-red-600 text-white px-5 py-2 rounded-lg text-xs font-semibold hover:shadow-lg transition-all">
                        Renew Now
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- Seasonal Trends Card Wrapper -->
        <div class="relative flex-1">
            <!-- Seasonal Trends Card -->
            <div class="{{ ($expired || $plan === 1) ? 'filter blur-sm pointer-events-none select-none' : '' }} bg-white rounded md:p-6 h-full flex flex-col md:flex-row items-center gap-4 md:gap-6 shadow-lg hover:shadow-xl transition-all duration-300">
                <!-- Top Blue Bar -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gray-700 rounded-t-2xl"></div>
                <!-- Left Icon -->
                <div class="flex-shrink-0 w-24 md:w-28 text-blue-500">
                    <img src="{{ asset('assets/megaphone.png') }}" alt="Trends" class="w-full h-auto object-contain bg-transparent rounded-lg">
                </div>
                <!-- Text Section -->
                <div class="flex-1 min-w-0">
                    <h2 class="text-gray-800 text-base md:text-lg font-semibold">Spot the Season's Must-Have Products</h2>
                    <p class="text-gray-600 mt-1 text-xs md:text-sm leading-relaxed">Discover trending items and see what's in high demand this season.</p>
                    <a href="{{ ($expired || $plan === 1) ? '' : route('seasonal_trends') }}"
                        class="mt-3 inline-block px-4 py-2 rounded-lg font-medium text-sm shadow transition-all
                            {{ ($expired || $plan === 1)
                                ? 'bg-gray-400 text-gray-200 cursor-not-allowed' 
                                : 'bg-blue-500 text-white hover:bg-blue-600 hover:shadow-md' }}"
                        onclick="{{ ($expired || $plan === 1) ? 'event.preventDefault();' : '' }}">
                        View Seasonal Trends
                    </a>
                </div>
                <!-- Right Graph Image -->
                <div class="flex-shrink-0 w-32 md:w-36">
                    <img src="{{ asset('assets/graph.png') }}" alt="Graph" class="w-full h-auto object-contain rounded-lg bg-transparent">
                </div>
            </div>
            @if($plan === 1 && !$expired)
            <div class="absolute inset-0 flex items-center justify-center rounded">
                <div class="bg-white rounded-lg p-4 shadow-xl text-center w-64 border border-red-300">
                    <div class="bg-gradient-to-r from-red-500 to-red-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-rounded text-white text-2xl">lock</span>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Premium Feature</h3>
                    <p class="text-gray-600 text-xs mb-3">
                        Upgrade to <span class="font-semibold text-red-600">Premium</span>
                    </p>
                    <a href="{{ route('subscription.selection') }}" class="inline-block bg-gradient-to-r from-red-500 to-red-600 text-white px-5 py-2 rounded-lg text-xs font-semibold hover:shadow-lg transition-all">
                        Upgrade Now
                    </a>
                </div>
            </div>
            @endif
            @if($expired)
            <div class="absolute inset-0 flex items-center justify-center rounded">
                <div class="bg-white rounded-lg p-4 shadow-xl text-center w-64 border border-red-300">
                    <div class="bg-gradient-to-r from-red-500 to-red-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-rounded text-white text-2xl">error</span>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Subscription Expired</h3>
                    <p class="text-gray-600 text-xs mb-3">
                        Renew to <span class="font-semibold text-red-600">continue</span>
                    </p>
                    <a href="{{ route('subscription.selection') }}" class="inline-block bg-gradient-to-r from-red-500 to-red-600 text-white px-5 py-2 rounded-lg text-xs font-semibold hover:shadow-lg transition-all">
                        Renew Now
                    </a>
                </div>
            </div>
            @endif
        </div>

    </div>
    <div class="space-y-4">
        @livewire('product-analysis')
    </div>
</div>
@endsection