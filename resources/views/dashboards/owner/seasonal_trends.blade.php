@extends($isStaff ? 'dashboards.staff.staff' : 'dashboards.owner.owner')

@section('content')
<div class="px-3 sm:px-4 lg:px-6 py-4 space-y-6 animate-slide-down">

    <!-- Page Header with Season Indicator + Back Button -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">

            <!-- LEFT SIDE: Title + Month -->
            <div class="flex items-center gap-3">
                <h1 class="text-lg font-semibold text-slate-800">Seasonal Trends</h1>
                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                    {{ date('F Y') }}
                </span>
            </div>

            <!-- RIGHT SIDE: Back Button -->
            @if(!$isStaff)
            <a href="{{ route('reports') }}"
                class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 
            text-gray-700 rounded-lg text-sm font-medium transition">
                <span class="material-symbols-rounded mr-1">arrow_back</span>
                Back
            </a>
            @endif


        </div>

        <p class="text-gray-600 text-sm">
            Top trending products this season based on historical sales patterns, growth velocity, and demand forecasting.
        </p>
    </div>

    <!-- Filters Card -->
    <div class="shadow-md rounded p-4 bg-white border-t-4 border-blue-300">
        <form id="filtersForm" method="GET" action="{{ route('seasonal_trends') }}" class="flex flex-wrap gap-4 items-center w-full">

            <!-- Category Filter -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Category:</label>
                <select name="category_id" id="categorySelect"
                    class="px-4 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->category_id }}" {{ ($categoryId ?? '') == $cat->category_id ? 'selected' : '' }}>
                        {{ $cat->category }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Top N Filter -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Show:</label>
                <select name="top_n" id="topNSelect"
                    class="px-4 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                    <option value="10" {{ ($topN ?? 15) == 10 ? 'selected' : '' }}>Top 10</option>
                    <option value="15" {{ ($topN ?? 15) == 15 ? 'selected' : '' }}>Top 15</option>
                    <option value="20" {{ ($topN ?? 15) == 20 ? 'selected' : '' }}>Top 20</option>
                </select>
            </div>

            <!-- Clear Filters -->
            <button type="button" id="clearFilters"
                class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium">
                Clear
            </button>

            <!-- View Toggle -->
            <div class="ml-auto flex items-center gap-2">
                <button type="button" id="gridViewBtn" class="p-2 rounded-lg hover:bg-blue-100 transition-colors" onclick="toggleView('grid')">
                    <span class="material-symbols-rounded text-blue-600">grid_view</span>
                </button>
                <button type="button" id="tableViewBtn" class="p-2 rounded-lg bg-blue-100 transition-colors" onclick="toggleView('table')">
                    <span class="material-symbols-rounded text-blue-600">table</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Results Container -->
    <div id="trendsContent">

        <!-- Grid View -->
        <div id="gridView" class="hidden grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @forelse($topProducts as $index => $product)
            <div class="relative bg-white rounded shadow-md hover:shadow-xl transition-shadow overflow-hidden border border-gray-200">
                <!-- Rank Badge -->
                <div class="absolute top-2 left-2 z-10">
                    <span class="bg-blue-400 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                        {{ $index + 1 }}
                    </span>
                </div>
                <!-- Product Image -->
                <div class="h-48 w-full overflow-hidden flex items-center justify-center bg-gray-50">
                    @if(isset($product->prod_image) && $product->prod_image)
                    <img src="{{ asset('storage/'.$product->prod_image) }}"
                        alt="{{ $product->name }}"
                        class="h-full w-auto object-contain transition-transform duration-300 hover:scale-105">
                    @else
                    <img src="{{ asset('assets/box.png') }}"
                        alt="Default image"
                        class="h-full w-auto object-contain p-2">
                    @endif
                </div>

                <!-- Product Info -->
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 text-sm mb-3 line-clamp-2 h-10">{{ $product->name }}</h3>
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="bg-blue-50 rounded p-2">
                            <p class="text-xs text-gray-600">Past Years</p>
                            <p class="text-lg font-bold text-blue-600">{{ $product->average_past }}</p>
                        </div>
                        <div class="bg-green-50 rounded p-2">
                            <p class="text-xs text-gray-600">This Month</p>
                            <p class="text-lg font-bold text-green-600">{{ $product->current_month_sold }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-gray-600">Growth Rate</span>
                        @php
                        $growthClass = $product->growth_rate > 0
                        ? 'bg-green-100 text-green-700'
                        : ($product->growth_rate < 0
                            ? 'bg-red-100 text-red-700'
                            : 'bg-gray-100 text-gray-600' );
                            @endphp

                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $growthClass }}">
                            {{ $product->growth_rate > 0 ? '↑' : ($product->growth_rate < 0 ? '↓' : '→') }} {{ abs($product->growth_rate) }}%
                            </span>
                    </div>
                    <div class="border-t pt-3 mt-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-600">Expected Demand</span>
                            <span class="text-lg font-bold text-blue-600">{{ $product->forecasted_demand }}</span>
                        </div>
                        @php
                        $maxForecast = max($topProducts->max('forecasted_demand'), 1);
                        $percent = min(($product->forecasted_demand / $maxForecast) * 100, 100);
                        @endphp
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-blue-400 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-lg shadow p-8 text-center">
                <span class="material-symbols-rounded text-gray-400 text-6xl mb-4">hourglass_empty</span>
                <p class="text-gray-500 text-lg">No seasonal trends data available</p>
            </div>
            @endforelse
        </div>

        <!-- Table View -->
        <div id="tableView" class="bg-white shadow-lg rounded overflow-hidden">
            <div class="overflow-x-auto max-h-[55vh] overflow-y-auto no-scrollbar">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-blue-100 text-gray-700 sticky top-0">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold text-sm uppercase tracking-wider">Rank</th>
                            <th class="px-6 py-4 text-left font-semibold text-sm uppercase tracking-wider">Product</th>
                            <th class="px-6 py-4 text-center font-semibold text-sm uppercase tracking-wider">Past Years</th>
                            <th class="px-6 py-4 text-center font-semibold text-sm uppercase tracking-wider">Current Month</th>
                            <th class="px-6 py-4 text-center font-semibold text-sm uppercase tracking-wider">Growth</th>
                            <th class="px-6 py-4 text-center font-semibold text-sm uppercase tracking-wider">Expected Demand</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($topProducts as $index => $product)
                        <tr class="hover:bg-blue-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-500 font-bold text-sm">
                                    {{ $index + 1 }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100">
                                        @if(isset($product->prod_image) && $product->prod_image)
                                        <img src="{{ asset('storage/' . $product->prod_image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                        <img src="{{ asset('assets/box.png') }}" alt="Default image" class="w-full h-full object-contain p-2">
                                        @endif
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center font-semibold">{{ $product->average_past }}</td>
                            <td class="px-6 py-4 text-center font-semibold">{{ $product->current_month_sold }}</td>
                            <td class="px-6 py-4 text-center">
                                @php
                                $growthClass = $product->growth_rate > 0
                                ? 'bg-green-100 text-green-700'
                                : ($product->growth_rate < 0
                                    ? 'bg-red-100 text-red-700'
                                    : 'bg-gray-100 text-gray-600' );
                                    $growthSymbol=$product->growth_rate > 0
                                    ? '↑'
                                    : ($product->growth_rate < 0 ? '↓' : '→' );
                                        @endphp

                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $growthClass }}">
                                        {{ $growthSymbol }} {{ abs($product->growth_rate) }}%
                                        </span>
                            </td>
                            <td class="px-6 py-4 text-center text-lg font-bold text-blue-600">
                                {{ $product->forecasted_demand }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <span class="material-symbols-rounded text-gray-400 text-5xl mb-3">hourglass_empty</span>
                                <p class="text-gray-500 text-lg">No data available for this month/category</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- NEW TRENDING ITEMS SECTION --}}
        @if(isset($newTrending) && $newTrending->count() > 0)
        <div class="mt-12">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                New Trending Items
                <span class="text-sm text-gray-500">(Selling for the first time this month)</span>
            </h2>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($newTrending as $product)
                <div class="relative bg-white rounded shadow-md hover:shadow-xl transition p-4 border border-gray-200">

                    <!-- Product Image -->
                    <div class="h-40 flex items-center justify-center bg-gray-50 mb-3 rounded">
                        @if($product->prod_image)
                        <img src="{{ asset('storage/'.$product->prod_image) }}"
                            class="h-full object-contain">
                        @else
                        <img src="{{ asset('assets/box.png') }}" class="h-full object-contain p-4">
                        @endif
                    </div>

                    <!-- Product Name -->
                    <h3 class="font-semibold text-gray-900 text-sm mb-2 line-clamp-2 h-10">
                        {{ $product->name }}
                    </h3>

                    <!-- This Month Sales -->
                    <p class="text-xs text-gray-600">This Month Sales</p>
                    <p class="text-xl font-bold text-green-600 mb-2">{{ $product->current_month_sold }}</p>

                    <!-- Expected Demand -->
                    <!-- <div class="flex justify-between items-center mt-3 border-t pt-3">
                        <span class="text-xs text-gray-600">Expected Demand</span>
                        <span class="text-lg font-bold text-blue-600">
                            {{ $product->forecasted_demand }}
                        </span>
                    </div> -->
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

<script>
    const form = document.getElementById('filtersForm');
    const categorySelect = document.getElementById('categorySelect');
    const topNSelect = document.getElementById('topNSelect');
    const clearBtn = document.getElementById('clearFilters');
    const content = document.getElementById('trendsContent');

    function fetchData() {
        const params = new URLSearchParams(new FormData(form));
        fetch(form.action + '?' + params.toString(), {
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(html, 'text/html');
                const newContent = newDoc.querySelector('#trendsContent');
                if (newContent) {
                    content.innerHTML = newContent.innerHTML;
                }
            });
    }

    categorySelect.addEventListener('change', fetchData);
    topNSelect.addEventListener('change', fetchData);

    clearBtn.addEventListener('click', () => {
        categorySelect.value = "";
        topNSelect.value = 15;
        fetchData();
    });

    function toggleView(view) {
        const gridView = document.getElementById('gridView');
        const tableView = document.getElementById('tableView');
        const gridBtn = document.getElementById('gridViewBtn');
        const tableBtn = document.getElementById('tableViewBtn');

        if (view === 'grid') {
            gridView.classList.remove('hidden');
            tableView.classList.add('hidden');
            gridBtn.classList.add('bg-blue-100');
            gridBtn.querySelector('span').classList.add('text-blue-600');
            tableBtn.classList.remove('bg-blue-100');
            tableBtn.querySelector('span').classList.remove('text-blue-600');
        } else {
            gridView.classList.add('hidden');
            tableView.classList.remove('hidden');
            tableBtn.classList.add('bg-blue-100');
            tableBtn.querySelector('span').classList.add('text-blue-600');
            gridBtn.classList.remove('bg-blue-100');
            gridBtn.querySelector('span').classList.remove('text-blue-600');
        }
    }
    document.addEventListener('DOMContentLoaded', () => toggleView('table'));
</script>
@endsection