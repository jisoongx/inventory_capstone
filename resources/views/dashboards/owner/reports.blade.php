@extends('dashboards.owner.owner')

@section('content')

<div class="px-4">
    @livewire('expiration-container')
</div>

<div class="md:p-4 space-y-5 -mt-2">

    <div class="flex flex-col md:flex-row md:gap-4">

        <!-- Restock Card -->
        <div class="relative bg-white rounded md:p-6 flex-1 flex flex-col md:flex-row items-center gap-4 md:gap-6 shadow-lg hover:shadow-xl transition-all duration-300">

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
                <a  href="{{ $expired ? '' : route('restock_suggestion') }}"
                    class="mt-3 inline-block px-4 py-2 rounded-lg font-medium text-sm shadow transition-all
                        {{ $expired 
                            ? 'bg-gray-400 text-gray-200 cursor-not-allowed' 
                            : 'bg-green-500 text-white hover:bg-green-600 hover:shadow-md' }}"
                            onclick="{{ $expired ? 'event.preventDefault();' : '' }}">
                    Generate List
                </a>
            </div>

            <!-- Right Product Image -->
            <div class="flex-shrink-0 w-32 md:w-48">
                <img src="{{ asset('assets/products.png') }}" alt="Products" class="w-full h-auto object-contain rounded-lg bg-transparent">
            </div>
        </div>

        <!-- Seasonal Trends Card -->
        <div class="relative bg-white rounded md:p-6 flex-1 flex flex-col md:flex-row items-center gap-4 md:gap-6 shadow-lg hover:shadow-xl transition-all duration-300">

            <!-- Top Blue Bar -->
            <div class="absolute top-0 left-0 w-full h-1 bg-gray-700 rounded-t-2xl"></div>

            <!-- Left Icon -->
            <div class="flex-shrink-0 w-24 md:w-28 text-blue-500">
                <img src="{{ asset('assets/megaphone.png') }}" alt="Trends" class="w-full h-auto object-contain bg-transparent rounded-lg">
            </div>

            <!-- Text Section -->
            <div class="flex-1 min-w-0">
                <h2 class="text-gray-800 text-base md:text-lg font-semibold">Spot the Season’s Must-Have Products</h2>
                <p class="text-gray-600 mt-1 text-xs md:text-sm leading-relaxed">Discover trending items and see what’s in high demand this season.</p>
                <a 
                    href="{{ $expired ? '' : route('seasonal_trends') }}"
                    class="mt-3 inline-block px-4 py-2 rounded-lg font-medium text-sm shadow transition-all
                        {{ $expired 
                            ? 'bg-gray-400 text-gray-200 cursor-not-allowed' 
                            : 'bg-blue-500 text-white hover:bg-blue-600 hover:shadow-md' }}"
                            onclick="{{ $expired ? 'event.preventDefault();' : '' }}"
                >
                    View Seasonal Trends
                </a>
            </div>

            <!-- Right Graph Image -->
            <div class="flex-shrink-0 w-32 md:w-48">
                <img src="{{ asset('assets/graph.png') }}" alt="Graph" class="w-full h-auto object-contain rounded-lg bg-transparent">
            </div>
        </div>

    </div>
</div>
@endsection