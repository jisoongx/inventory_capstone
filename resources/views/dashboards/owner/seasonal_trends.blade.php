@extends('dashboards.owner.owner')

@section('content')
<div class="p-6 space-y-6">

    <!-- Page Title -->
    <h1 class="text-2xl font-semibold text-gray-900 mb-2">Seasonal Trends</h1>
    <p class="text-gray-600 mb-4 text-sm">
        Compare your top-selling products this month with last year, see growth, and expected demand.
    </p>

    <!-- Category Filter -->
    <div class="mb-4 flex items-center gap-2">
        <label class="text-sm text-gray-600" for="categorySelect">Filter by Category:</label>
        <form id="categoryForm" method="GET" action="{{ route('seasonal_trends') }}">
            <select name="category_id" id="categorySelect" class="border rounded-md px-2 py-1 text-sm" onchange="document.getElementById('categoryForm').submit()">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->category_id }}" {{ ($categoryId ?? '') == $cat->category_id ? 'selected' : '' }}>
                    {{ $cat->category }}
                </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Chart -->
    <div class="bg-white shadow rounded-xl p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Top Products Sales Comparison</h2>
        <canvas id="salesChart" class="w-full h-64"></canvas>
    </div>

    <!-- Table -->
    <div class="bg-white shadow rounded-xl p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Top Products Table</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 divide-y divide-gray-200">

                <thead class="bg-gray-100 text-center">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm  font-medium text-gray-700">Product Name</th>
                        <th class="px-4 py-2  text-sm font-medium text-gray-700">Current Month</th>
                        <th class="px-4 py-2  text-sm font-medium text-gray-700">Last Year</th>
                        <th class="px-4 py-2  text-sm font-medium text-gray-700">Growth Rate (%)</th>
                        <th class="px-4 py-2  text-sm font-medium text-gray-700">Expected Demand</th>


                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($topProducts as $product)
                    <tr>
                        <td class="px-4 py-2">{{ $product->name }}</td>

                        <td class="px-4 py-2 text-center">{{ $product->current_month_sold }}</td>
                        <td class="px-4 py-2 text-center">{{ $product->last_year_sold }}</td>
                        <td class="px-4 py-2 text-center">

                            <span class="{{ $product->growth_rate >= 0 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold' }}">
                                {{ $product->growth_rate }}%
                            </span>
                        </td>

                        <td class="px-4 py-2 text-center">{{ $product->expected_demand }}</td>


                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@php
$productNames = $topProducts->pluck('name');
$currentSales = $topProducts->pluck('current_month_sold');
$lastYearSales = $topProducts->pluck('last_year_sold');
@endphp
<script>
    const labels = @json($productNames);
    const currentData = @json($currentSales);
    const lastYearData = @json($lastYearSales);

    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                    label: 'Current Month',
                    data: currentData,
                    backgroundColor: 'rgba(34,197,94,0.7)', // green
                    borderColor: 'rgba(34,197,94,1)',
                    borderWidth: 1,
                    borderRadius: 6
                },
                {
                    label: 'Last Year Same Month',
                    data: lastYearData,
                    backgroundColor: 'rgba(59,130,246,0.7)', // blue
                    borderColor: 'rgba(59,130,246,1)',
                    borderWidth: 1,
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
@endsection