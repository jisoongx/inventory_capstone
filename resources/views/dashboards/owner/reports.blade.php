@extends('dashboards.owner.owner')

@section('content')
<div class="p-6 space-y-8">

    <!-- Restock Recommendations Card -->
    <div class="bg-gradient-to-r from-gray-800 to-gray-700 rounded-2xl p-8 flex flex-col md:flex-row items-center gap-6 shadow-xl hover:shadow-2xl transition-all duration-300">

        <!-- Left Icon -->
        <div class="flex-shrink-0">
            <img src="{{ asset('assets/restock-icon.png') }}" alt="Restock" class="w-28 h-28 md:w-32 md:h-32 object-contain">
        </div>

        <!-- Text Section -->
        <div class="flex-1 min-w-0">
            <h2 class="text-white text-2xl md:text-3xl font-semibold tracking-wide">Wondering what to restock?</h2>
            <p class="text-gray-300 mt-3 text-base md:text-lg leading-relaxed">Get intelligent suggestions for your store so you never run out of popular products.</p>
            <a href="#"
                class="mt-5 inline-block bg-white text-gray-800 px-6 py-3 rounded-xl font-medium text-sm md:text-base shadow hover:shadow-lg hover:bg-gray-200 transition-all">Generate List</a>
        </div>

        <!-- Right Product Image -->
        <div class="flex-shrink-0">
            <img src="{{ asset('assets/products.png') }}" alt="Products" class="w-64 h-48 md:w-72 md:h-56 object-cover rounded-lg shadow-lg">
        </div>
    </div>

    <!-- Seasonal Trends Card -->
    <div class="bg-gradient-to-r from-gray-800 to-gray-700 rounded-2xl p-8 flex flex-col md:flex-row items-center gap-6 shadow-xl hover:shadow-2xl transition-all duration-300">

        <!-- Left Icon (Megaphone) -->
        <div class="flex-shrink-0">
            <img src="{{ asset('assets/megaphone.png') }}" alt="Trends" class="w-40 h-40 md:w-48 md:h-48 object-contain rounded-lg shadow-md">
        </div>

        <!-- Text Section -->
        <div class="flex-1 min-w-0">
            <h2 class="text-white text-2xl md:text-3xl font-semibold tracking-wide">Spot the Season’s Must-Have Products</h2>
            <p class="text-gray-300 mt-3 text-base md:text-lg leading-relaxed">Discover trending items and see what’s in high demand this season.</p>
            <a href="#"
                class="mt-5 inline-block bg-white text-gray-800 px-6 py-3 rounded-xl font-medium text-sm md:text-base shadow hover:shadow-lg hover:bg-gray-200 transition-all">View Seasonal Trends</a>
        </div>

        <!-- Right Graph Image -->
        <div class="flex-shrink-0">
            <img src="{{ asset('assets/graph.png') }}" alt="Graph" class="w-48 h-48 md:w-52 md:h-52 object-cover rounded-lg shadow-lg">
        </div>
    </div>

</div>
@endsection