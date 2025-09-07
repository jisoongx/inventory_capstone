@extends('dashboards.owner.owner') 

@section('content')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-2">

            <div class="">
                <span class="text-sm text-gray-500">{{ $dateDisplay->format('F j, Y') }}</span>
                <h1 class="text-2xl font-semibold mb-4">Welcome, {{ ucwords($owner_name) }}!</h1>

                <div class="flex gap-3 mb-3 w-full">
                    <div class="bg-white border-t-4 border-red-900 p-4 shadow-lg rounded flex-[2] text-center">
                        <p class="text-red-800 text-xl font-bold">₱{{ number_format($dailySales->dailySales, 2) }}</p>
                        <span class="text-gray-600 text-xs font-bold">Daily Sales</span>
                    </div>

                    <div class="bg-white border-t-4 border-red-700 p-4 shadow-lg rounded flex-[1] text-center" title="₱{{ number_format($weeklySales->weeklySales, 2) }}">
                        <p class="text-red-600 text-xl font-bold" title="₱{{ number_format($weeklySales->weeklySales, 2) }}">
                            ₱{{ $weeklySales->weeklySales >= 1000 ? number_format($weeklySales->weeklySales / 1000, 1) . 'k' : number_format($weeklySales->weeklySales, 2) }}
                        </p>
                        <span class="text-gray-600 text-xs">Last 7 days</span>
                    </div>

                    <div class="bg-white border-t-4 border-red-500 p-4 shadow-lg rounded flex-[1] text-center" title="₱{{ number_format($monthSales->monthSales, 2) }}">
                        <p class="text-red-400 text-xl font-bold" title="₱{{ number_format($monthSales->monthSales, 2) }}">
                            ₱{{ $monthSales->monthSales >= 1000 ? number_format($monthSales->monthSales / 1000, 1) . 'k' : number_format($monthSales->monthSales, 2) }}
                        </p>
                        <span class="text-gray-600 text-xs">This Month's Sales</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded shadow border">
                    <p class="text-left text-black font-semibold text-xs pb-5">Sales by Category</p>

                    <div class="w-full overflow-x-auto mt-3">
                        <div id="productChart" 
                            data-categories='@json($categories ?? [])' 
                            data-products='@json($products ?? [])' 
                            data-products-prev='@json($productsPrev ?? [])' 
                            data-year='@json($year ?? [])'
                            style="height: 350px;">
                            <canvas></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- profit chart -->
            <div class="bg-white p-5 rounded shadow border">
                    <p class="text-left text-black font-semibold text-xs border-b border-gray-200 pb-5">Monthly Net Profit</p>
                <div class="flex items-center justify-center pt-4">
                    <div class="flex-2 mr-7">
                        <span class="text-xl font-bold block mb-1">
                            {{ $dateDisplay->format('F Y') }}
                        </span>
                        <p class="text-xs mb-3">
                            {{ $dateDisplay->format('D, d') }}
                        </p>
                    </div>
                    <div class="flex-1">

                        @if (is_null($profitMonth) || $profitMonth === 0)
                            <span class="text-xl text-red-700 block mb-1">
                                Empty database.
                            </span>
                        @else
                            <span class="text-xl font-bold block mb-1">
                                ₱{{ number_format($profitMonth, 2) }}
                            </span>
                        @endif

                        <p class="text-xs mb-3">Current Net Profit</p>
                    </div>

                    <div>
                        <a href="{{ route('dashboards.owner.expense_record') }}"
                        class="bg-red-100 border border-red-900 px-6 py-2.5 rounded text-xs mr-3 text-black inline-block text-center">
                            View
                        </a>
                    </div>

                    <form method="GET" action="{{ route('dashboards.owner.dashboard') }}">
                        <select name="year" id="year"
                            class="rounded px-6 py-2.5 border-gray-300 text-gray-700 text-xs focus:ring focus:ring-blue-200 focus:border-blue-400"
                            onchange="this.form.submit()">
                            @forelse ($year as $y)
                                <option value="{{ $y }}"
                                    {{ request('year') == $y || (empty(request('year')) && $loop->first) ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @empty
                                <option class="text-black" value="{{ $latestYear }}">{{ $latestYear }}</option>
                            @endforelse
                        </select>
                    </form>
                </div>
                <div class="flex space-x-1 mt-2">
                    <button onclick="zoomIn()" id="zoomIn" title="Zoom In">
                        <span class="material-symbols-rounded-small text-sm" title="Zoom In">add_circle</span>
                    </button>
                    <button onclick="zoomOut()" id="zoomOut" title="Zoom Out"> 
                        <span class="material-symbols-rounded-small text-sm" title="Zoom Out">do_not_disturb_on</span>
                    </button>
                    <button onclick="resetZoom()" id="zoomReset" title="Reset">
                        <span class="material-symbols-rounded-small text-sm" title="Reset">reset_settings</span>
                    </button>
                </div>
                <div class="w-full overflow-x-auto mt-3">
                    <div id="profitChart" 
                        data-profits='@json($profits ?? [])' 
                        data-months='@json($months ?? [])'
                        style="height: 380px; width: 800px;">
                        <canvas></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- table dapit -->
        <div class="p-2">
            <div class="grid p-5 bg-white rounded shadow border">
                <h3 class="text-xs font-semibold text-black mb-5">Comparative Analysis</h3>
                
                <div class="overflow-x-auto">
                    <table class="text-sm text-left text-slate-700 border-collapse table-auto w-full">
                        <thead>
                            <tr class="bg-red-700 text-xs text-slate-500 uppercase">
                                <th class="sticky left-0 z-10 bg-red-700 shadow px-4 py-3 border-b border-slate-300 text-white">Metric</th>
                                @if (count($expenses) === 0)
                                    <td class="px-4 py-4 text-center text-xs text-white w-full border-b border-slate-200">
                                    </td>
                                @else   
                                    @foreach ($tableMonthNames as $index => $month)
                                        <th class="px-4 py-3 border-b border-red-300 text-white">{{ $month }}</th>

                                        @if ($index < count($tableMonthNames) - 1)
                                            <th class="px-4 py-3 border-b border-slate-300 text-white">
                                                {{ $month }}-{{ $tableMonthNames[$index + 1] }} (%)
                                            </th>
                                        @endif
                                    @endforeach
                                @endif
                            </tr>
                        </thead>

                        <tbody>
                            <tr class="bg-red-100">
                                <td colspan="25" class="sticky left-0 z-10 px-4 py-2 text-left text-xs font-semibold text-slate-700 justify-center align-center">
                                    Money Spent (Negative % is Better)
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="sticky left-0 z-10 bg-white px-4 py-4 border-b border-slate-200 text-xs font-semibold">In-Store Expenses</td>
                                @if (count($expenses) === 0)
                                    <td class="px-4 py-4 text-center text-xs text-slate-500 w-full border-b border-slate-200">
                                        No data available
                                    </td>
                                @else
                                    @foreach ($expenses as $index => $expense)
                                        <td class="px-4 py-4 border-b border-slate-200 text-xs text-slate-500">
                                            ₱{{ number_format($expense, 2) }}
                                        </td>

                                        @if ($index < count($expenses) - 1)
                                            @php
                                                $nextExpense = $expenses[$index + 1];
                                                $diff = $nextExpense - $expense;

                                                if ($expense == 0 && $nextExpense > 0) {
                                                    $percent = null; 
                                                } elseif ($expense == 0 && $nextExpense == 0) {
                                                    $percent = 0;
                                                } else {
                                                    $percent = ($diff / $expense) * 100;
                                                }
                                            @endphp
                                            <td class="px-4 py-4 border-b border-slate-200 text-xs font-bold 
                                                {{ is_null($percent) ? 'text-red-600' : ($percent < 0 || $percent == 0 ? 'text-green-600' : 'text-red-600') }}">
                                                @if (is_null($percent))
                                                    Increased!
                                                @elseif ($percent == 0)
                                                    —
                                                @else
                                                    {{ $percent > 0 ? '+' : '' }}{{ number_format($percent, 1) }}%
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                @endif
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="sticky left-0 z-10 bg-white px-4 py-4 border-b border-slate-200 text-xs font-semibold">Revenue Loss</td>
                                @if (count($losses) === 0)
                                    <td class="px-4 py-4 text-center text-xs text-slate-500 w-full border-b border-slate-200">
                                        No data available
                                    </td>
                                @else
                                    @foreach ($losses as $index => $loss)
                                        @if (is_null($loss) || $loss === '')
                                            <td class="px-4 py-4 border-b border-slate-200 text-xs text-slate-500">
                                                --
                                            </td>
                                        @else
                                            <td class="px-4 py-4 border-b border-slate-200 text-xs text-slate-500">
                                                ₱{{ number_format($loss, 2) }}
                                            </td>
                                        @endif

                                        @if ($index < count($losses) - 1)
                                            @php
                                                $nextLoss = $losses[$index + 1];
                                                $diff = $nextLoss - $loss;

                                                if ($loss == 0 && $nextLoss > 0) {
                                                    $percent = null; 
                                                } elseif ($loss == 0 && $nextLoss == 0) {
                                                    $percent = 0;
                                                } else {
                                                    $percent = ($diff / $loss) * 100;
                                                }
                                            @endphp
                                            <td class="px-4 py-4 border-b border-slate-200 text-xs font-bold 
                                                {{ is_null($percent) ? 'text-red-600' : ($percent < 0 || $percent == 0 ? 'text-green-600' : 'text-red-600') }}">
                                                @if (is_null($percent))
                                                    Increased!
                                                @elseif ($percent == 0)
                                                    —
                                                @else
                                                    {{ $percent > 0 ? '+' : '' }}{{ number_format($percent, 1) }}%
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                @endif
                            </tr>
                            <tr class="bg-red-100">
                                <td colspan="25" class="sticky left-0 z-10 px-4 py-2 text-left text-xs font-semibold text-slate-700 justify-center align-center">
                                    Money Earned (Positive % is Better)
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="sticky left-0 z-10 bg-white px-4 py-4 border-b border-slate-200 text-xs font-semibold">Total Sales</td>
                                @if (count($sales) === 0)
                                    <td class="px-4 py-4 text-center text-xs text-slate-500 w-full border-b border-slate-200">
                                        No data available
                                    </td>
                                @else
                                    @foreach ($sales as $index => $sale)
                                        @if (is_null($sale) || $sale === '')
                                            <td class="px-4 py-4 border-b border-slate-200 text-xs text-slate-500">
                                                --
                                            </td>
                                        @else
                                            <td class="px-4 py-4 border-b border-slate-200 text-xs text-slate-500">
                                                ₱{{ number_format($sale, 2) }}
                                            </td>
                                        @endif

                                        @if ($index < count($sales) - 1)
                                            @php
                                                $nextSales = $sales[$index + 1];
                                                $diff = $nextSales - $sale;

                                                if ($sale == 0 && $nextSales > 0) {
                                                    $percent = null; 
                                                } elseif ($sale == 0 && $nextSales == 0) {
                                                    $percent = 0;
                                                } else {
                                                    $percent = ($diff / $sale) * 100;
                                                }
                                            @endphp

                                            <td class="px-4 py-4 border-b border-slate-200 text-xs font-bold 
                                                {{ ($percent > 0 || $percent == 0 || is_null($percent) ? 'text-green-600' : 'text-red-600') }}">
                                                @if (is_null($percent))
                                                    Increased!
                                                @elseif ($percent == 0)
                                                    —
                                                @else
                                                    {{ $percent > 0 ? '+' : '' }}{{ number_format($percent, 1) }}%
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                @endif
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="sticky left-0 z-10 bg-white px-4 py-4 border-b border-slate-200 text-xs font-semibold">Net Profit</td>
                                @if (count($netprofits) === 0)
                                    <td class="px-4 py-4 text-center text-xs text-slate-500 w-full border-b border-slate-200">
                                        No data available
                                    </td>
                                @else
                                    @foreach ($netprofits as $index => $profit)
                                        <td class="px-4 py-4 border-b border-slate-200 text-xs text-slate-500">
                                            ₱{{ number_format($profit, 2) }}
                                        </td>

                                        @if ($index < count($netprofits) - 1)
                                            @php
                                                $nextProfit = $netprofits[$index + 1];
                                                $diff = $nextProfit - $profit;

                                                if ($profit == 0 && $nextProfit > 0) {
                                                    $percent = null; 
                                                } elseif ($profit == 0 && $nextProfit == 0) {
                                                    $percent = 0;
                                                } else {
                                                    $percent = ($diff / $profit) * 100;
                                                }
                                            @endphp

                                            <td class="px-4 py-4 border-b border-slate-200 text-xs font-bold 
                                                {{ ($percent > 0 || $percent == 0 || is_null($percent) ? 'text-green-600' : 'text-red-600') }}">
                                                @if (is_null($percent))
                                                    Increased!
                                                @elseif ($percent == 0)
                                                    —
                                                @else
                                                    {{ $percent > 0 ? '+' : '' }}{{ number_format($percent, 1) }}%
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                @endif
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div> <!-- div sa table -->
        </div>


    <!-- <script>

        function zoomIn() {
            profitChart.zoom(1.2); 
        }

        function zoomOut() {
            profitChart.zoom(0.8);
        }

        function resetZoom() {
            profitChart.resetZoom();
        }
    </script> -->

@endsection