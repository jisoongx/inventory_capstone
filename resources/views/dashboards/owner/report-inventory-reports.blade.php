@extends('dashboards.owner.owner')

@section('content')

    <div class="">
        <div class="px-4 pb-4">
            @livewire('expiration-container')
        </div>

        @if($expired || $plan === 3)
            <div class="ml-64 fixed inset-0 z-[99] flex items-center justify-center">
                <!-- 🔹 Background overlay -->
                <div class="absolute inset-0 backdrop-blur-sm"></div>

                <!-- 🔹 Modal card -->
                <div class="relative bg-white rounded-lg shadow-2xl border border-red-200 overflow-hidden max-w-[35rem] mx-4">
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
                                Inventory Reports Available on Premium Plans
                            </h2>

                            <p class="text-slate-600 text-xs xs:text-base leading-relaxed mb-6">
                                Upgrade your subscription to access expiration monitoring, and loss reports, 
                                to help you manage supplies efficiently and reduce waste.
                            </p>

                            <div class="flex flex-wrap items-center justify-center gap-3 mb-8 text-xs md:text-sm text-slate-700">
                                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full shadow-sm border border-red-100">
                                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-xs font-medium">Data-driven Reports</span>
                                </div>
                                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full shadow-sm border border-red-100">
                                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-xs font-medium">Real-time</span>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                                <a href="{{ route('subscription.selection') }}" 
                                    class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-orange-600 to-rose-600 text-white font-semibold rounded-lg hover:from-red-700 hover:to-rose-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl group text-sm">
                                    <span class="text-xs">Upgrade Now!</span>
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
        @livewire('report-inventory')

    </div>


@endsection