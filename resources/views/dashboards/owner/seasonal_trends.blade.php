@extends('dashboards.owner.owner')

@section('content')
<div class="p-6 space-y-6">

    <!-- Page Title -->
    <h1 class="text-2xl font-semibold text-gray-900 mb-2">Seasonal Trends</h1>
    <p class="text-gray-600 text-sm mb-6">
        This month’s top products based on historical sales, growth, and expected demand.
    </p>

    <!-- Filters Card -->
  
        <form id="filtersForm" method="GET" action="{{ route('seasonal_trends') }}" class="flex flex-wrap gap-4 items-center w-full">

            <!-- Category Filter -->
            <div class="flex items-center gap-2">
                <!-- <label class="text-sm text-gray-600" for="categorySelect">Category:</label> -->
                <select name="category_id" id="categorySelect"
                    class="px-3 py-1 text-sm shadow-md rounded-md border border-gray-300 focus:ring-2 focus:ring-GRAY-300 focus:outline-none"
                    onchange="document.getElementById('filtersForm').submit()">
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
                <!-- <label class="text-sm text-gray-600" for="topNSelect">Top Products:</label> -->
                <select name="top_n" id="topNSelect"
                    class="px-3 py-1 text-sm shadow-md rounded-md border border-gray-300 focus:ring-2 focus:ring-GRAY-300 focus:outline-none"
                    onchange="document.getElementById('filtersForm').submit()">
                    <option value="10" {{ ($topN ?? 15) == 10 ? 'selected' : '' }}>Top 10</option>
                    <option value="15" {{ ($topN ?? 15) == 15 ? 'selected' : '' }}>Top 15</option>
                    <option value="20" {{ ($topN ?? 15) == 20 ? 'selected' : '' }}>Top 20</option>
                </select>
            </div>

        </form>
   

    <!-- Chart & Table Grid -->
    <div class="grid lg:grid-cols-2 gap-6">

        <!-- Chart Card -->
        <div class="bg-white shadow-md rounded-lg p-6 flex items-center justify-center h-[450px]">
            @if($topProducts->isEmpty())
            <p class="text-gray-500 text-center">No chart data available for this month/category.</p>
            @else
            <canvas id="salesChart" class="w-full h-full"></canvas>
            @endif
        </div>

        <!-- Table Card -->
        <div class="bg-white shadow-md rounded-md p-6 overflow-y-auto h-[450px]">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100 uppercase text-gray-700 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Product</th>
                        <th class="px-4 py-3 text-center font-semibold">Past Years (QTY)</th>
                        <th class="px-4 py-3 text-center font-semibold">Current Month (QTY)</th>
                        <th class="px-4 py-3 text-center font-semibold">Growth %</th>
                        <th class="px-4 py-3 text-center font-semibold">Expected Demand</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($topProducts as $product)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-left font-medium">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-center">{{ $product->last_year_sold }}</td>
                        <td class="px-4 py-3 text-center">{{ $product->current_month_sold }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="{{ $product->growth_rate >= 0 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold' }}">
                                {{ $product->growth_rate }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $product->expected_demand }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                            No data available for this month/category.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@php
$productNames = $topProducts->pluck('name');
$currentSales = $topProducts->pluck('current_month_sold');
$avgPastYears = $topProducts->pluck('last_year_sold');
@endphp

@if($topProducts->isNotEmpty())
<script>
    const labels = @json($productNames);
    const currentData = @json($currentSales);
    const pastData = @json($avgPastYears);

    // Gradient colors for bars
    function gradientColor(ctx, colorStart, colorEnd) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, colorStart);
        gradient.addColorStop(1, colorEnd);
        return gradient;
    }

    const ctx = document.getElementById('salesChart').getContext('2d');
    const currentGradients = currentData.map((v, i) => gradientColor(ctx, v > pastData[i] ? '#22c55e' : '#3b82f6', v > pastData[i] ? '#16a34a' : '#2563eb'));
    const pastGradients = pastData.map(() => gradientColor(ctx, '#93c5fd', '#3b82f6'));

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                    label: 'Current Month',
                    data: currentData,
                    backgroundColor: currentGradients,
                    borderColor: currentGradients,
                    borderWidth: 1,
                    borderRadius: 6
                },
                {
                    label: 'Average Past Years',
                    data: pastData,
                    backgroundColor: pastGradients,
                    borderColor: pastGradients,
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
@endif
@endsection