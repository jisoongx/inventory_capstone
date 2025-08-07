@extends('dashboards.owner.owner') 

@section('content')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 p-2">
            <!-- currency -->
            <div class="">
                <span class="text-sm text-gray-500">{{ $dateDisplay->format('F j, Y') }}</span>
                <h1 class="text-2xl font-bold mb-4">Welcome, {{$owner_name }}!</h1>

                <div class="flex gap-4 mb-5 w-full">
                    <!-- Daily Sales - wider -->
                    <div class="bg-white border-t-4 border-red-800 p-4 shadow-lg rounded flex-[2] text-center">
                        <p class="text-red-600 text-xl font-bold">₱14,500</p>
                        <span class="text-gray-600 text-xs font-bold">Daily Sales</span>
                    </div>

                    <!-- Weekly Sales -->
                    <div class="bg-white border-t-4 border-green-800 p-4 shadow-lg rounded flex-[1] text-center">
                        <p class="text-green-600 text-xl font-bold">₱20,000</p>
                        <span class="text-gray-600 text-xs">Weekly Sales</span>
                    </div>

                    <!-- Monthly Sales -->
                    <div class="bg-white border-t-4 border-blue-800 p-4 shadow-lg rounded flex-[1] text-center">
                        <p class="text-blue-600 text-xl font-bold">₱154,000</p>
                        <span class="text-gray-600 text-xs">Monthly Sales</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded shadow">
                    <p class="text-left text-black font-bold text-xs pb-5">Sales by Category</p>
                    <div class="mt-1">
                        <div class="">
                            <canvas id="productChart" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- profit chart -->
            <div class="bg-white p-5 rounded shadow">
                    <p class="text-left text-black font-bold text-xs border-b border-gray-200 pb-5">Monthly Net Profit</p>

                <div class="flex items-center justify-center pt-4">
                    <div class="flex-2 mr-7">
                        <span class="text-xl font-bold text-black block mb-1">
                            {{ $dateDisplay->format('F Y') }}
                        </span>
                        <p class="text-xs text-black mb-3">
                            {{ $dateDisplay->format('D, d') }}
                        </p>
                    </div>
                    <div class="flex-1">
                        @php
                            $currentMonth = (int)date('n');
                        @endphp

                        @if (is_null($profitMonth) || is_null($profitMonth->month))
                            <span class="text-xl text-red-700 block mb-1">
                                Empty database.
                            </span>
                        @elseif ($currentMonth == $profitMonth->month)
                            <span class="text-xl font-bold text-black block mb-1">
                                ₱{{ number_format($profitMonth->net_profit, 2) }}
                            </span>
                        @endif

                        <p class="text-xs text-black mb-3">Current Net Profit</p>
                    </div>

                    <div>
                        <button class="bg-red-100 border border-red-900 px-6 py-2.5 rounded text-xs mr-3">
                            <a href="{{ route('dashboards.owner.monthly_profit') }}" class="text-black">View</a>
                        </button>
                    </div>

                    <form method="GET" action="{{ route('dashboards.owner.dashboard') }}">
                        <div class="p-2 rounded border-gray-500 border text-xs">
                            <select name="year" id="year" class="form-select" onchange="this.form.submit()">
                                @foreach ($year as $y)
                                    <option value="{{ $y }}" 
                                        {{ request('year') == $y || (empty(request('year')) && $loop->first) ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>

                <div class="flex space-x-1">
                    <button onclick="zoomIn()">
                        <span class="material-symbols-rounded text-lg" title="Zoom In">add_circle</span>
                    </button>
                    <button onclick="zoomOut()"> 
                        <span class="material-symbols-rounded text-lg" title="Zoom Out">do_not_disturb_on</span>
                    </button>
                    <button onclick="resetZoom()">
                        <span class="material-symbols-rounded text-lg" title="Reset">reset_settings</span>
                    </button>
                </div>
                <div class="mt-3">
                    <div class="overflow-x-auto">
                        <canvas id="profitChart" height="400"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- table -->
        <div class="p-2">
            <div class="grid p-5 bg-white rounded shadow">
                <h3 class="text-xs font-semibold text-black mb-5">Comparative Analysis</h3>
                
                <div class="flex-wrap overflow-x-auto">
                    <table class="text-sm text-left text-slate-700 border-collapse">
                        <thead>
                            <tr class="bg-red-50 text-xs text-slate-500 uppercase">
                                <th class="px-4 py-3 border-b border-slate-300">Metric</th>
                                @foreach ($months as $index => $month)
                                    <th class="px-4 py-3 border-b border-slate-300">{{ $month }}</th>

                                    @if ($index < count($months) - 1)
                                        <th class="px-4 py-3 border-b border-slate-300">
                                            {{ $month }}-{{ $months[$index + 1] }} (%)
                                        </th>
                                    @endif
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 border-b border-slate-200 text-xs font-semibold text-slate-800">In-Store Expenses*</td>
                                @foreach ($expenses as $index => $expense)
                                    <td class="px-4 py-4 border-b border-slate-200 text-xs text-slate-500">
                                        ₱{{ number_format($expense, 2) }}
                                    </td>

                                    @if ($index < count($expenses) - 1)
                                        @php
                                            $nextExpense = $expenses[$index + 1];
                                            $diff = $nextExpense - $expense;
                                            $percent = $expense == 0 ? 0 : ($diff / abs($expense)) * 100;
                                        @endphp
                                        <td class="px-4 py-4 border-b border-slate-200 text-xs font-bold {{ $percent < 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {!! $percent < 0 ? '🠋' : '🠉' !!}{{ number_format(abs($percent), 1) }}%
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 border-b border-slate-200 text-xs font-semibold text-slate-800">Total Loss</td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 border-b border-slate-200 text-xs font-semibold text-slate-800">Total Sales</td>
                                @foreach ($sales as $index => $sale)
                                    <td class="px-4 py-4 border-b border-slate-200 text-xs text-slate-500">
                                        ₱{{ number_format($sale, 2) }}
                                    </td>

                                    @if ($index < count($sales) - 1)
                                        @php
                                            $nextSales = $sales[$index + 1];
                                            $diff = $nextSales - $sale;
                                            $percent = $sale == 0 ? 0 : ($diff / abs($sale)) * 100;
                                        @endphp
                                        <td class="px-4 py-4 border-b border-slate-200 text-xs font-bold {{ $percent < 0 ? 'text-red-600' : 'text-green-600' }}">
                                            {!! $percent < 0 ? '🠋' : '🠉' !!}{{ number_format(abs($percent), 1) }}%
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 border-b border-slate-200 text-xs font-semibold text-slate-800">Net Profit</td>
                                @foreach ($profits as $index => $profit)
                                    <td class="px-4 py-4 border-b border-slate-200 text-xs text-slate-500">
                                        ₱{{ number_format($profit, 2) }}
                                    </td>

                                    @if ($index < count($profits) - 1)
                                        @php
                                            $nextProfits= $profits[$index + 1];
                                            $diff = $nextProfits - $profit;
                                            $percent = $profit == 0 ? 0 : ($diff / abs($profit)) * 100;
                                        @endphp
                                        <td class="px-4 py-4 border-b border-slate-200 text-xs font-bold {{ $percent < 0 ? 'text-red-600' : 'text-green-600' }}">
                                            {!! $percent < 0 ? '🠋' : '🠉' !!}{{ number_format(abs($percent), 1) }}%
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-slate-400 mt-2 italic">
                    *For In-Store Expenses, a🠋 (decrease) is considered a positive outcome.
                </p>
            </div> <!-- div sa table -->
        </div>


    <script>
        const ctx = document.getElementById('profitChart').getContext('2d');
        const ctz = document.getElementById('productChart').getContext('2d');

        const profitChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($months ?? []) !!},
                datasets: [{
                    label: 'Profit',
                    data: {!! json_encode($profits ?? []) !!},
                    borderColor: 'rgba(25, 104, 169, 1)',
                    backgroundColor: 'rgba(234, 244, 254, 1)',
                    tension: 0.1,
                    fill: true,
                    pointRadius: 6,
                    pointBackgroundColor: 'rgba(25, 104, 169, 1)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    zoom: {
                        pan: {
                            enabled: true,
                            mode: 'xy'
                        },
                        zoom: {
                            enabled: true,
                            mode: 'x',
                            drag: true
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        display: true,
                    }
                }
            }
        });

        const productChart = new Chart(ctz, {
            type: 'bar',
            data: {
                labels: {!! json_encode($categories ?? []) !!},
                datasets: [
                    {
                        label: {!! json_encode(!empty($year) ? $year[0] : '') !!},
                        data: {!! json_encode(!empty($products) ? ($products) : []) !!},
                        backgroundColor: 'rgba(25, 104, 169, 1)',
                        borderRadius: {
                            topLeft: 20,
                            topRight: 20,
                            bottomLeft: 0,
                            bottomRight: 0
                        },
                        fill: true,
                    },
                    {
                        label: {!! json_encode(!empty($year) ? $year[1] : '') !!},
                        data: {!! json_encode(!empty($productsPrev) ? ($productsPrev) : []) !!},
                        backgroundColor: 'rgba(176, 78, 45, 1)',
                        borderRadius: {
                            topLeft: 20,
                            topRight: 20,
                            bottomLeft: 0,
                            bottomRight: 0
                        },
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    },
                    zoom: {
                        pan: {
                            enabled: true,
                            mode: 'xy'
                        },
                        zoom: {
                            mode: 'x'
                        }
                    }
                },
                scales: {
                    x: {
                        gridLines: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        display: false,
                    }
                }
            }
        });

        function zoomIn() {
            profitChart.zoom(1.2); // 1.2 = 20% zoom in
        }

        // Zoom Out function
        function zoomOut() {
            profitChart.zoom(0.8); // 0.8 = 20% zoom out
        }

        // Reset Zoom function
        function resetZoom() {
            profitChart.resetZoom();
        }
    </script>

@endsection