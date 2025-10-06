@extends('dashboards.owner.owner') 
<head>
    <title>Dashboard</title>
</head>
@section('content')

    <div class="px-4 space-y-4">
        @livewire('expiration-container')
        <div class="grid grid-cols-2 md:grid-cols-2 gap-4">
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

                <div class="flex gap-3 pr-3">
                    <div class="bg-white p-5 rounded shadow border w-[50%] h-[26rem]">
                        <p class="text-left text-black font-semibold text-xs">Sales by Category</p>
                        <div class="flex justify-center gap-4 mt-3">
                            @if($year[0] ?? false)
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 bg-red-600 inline-block rounded-full"></span>
                                    <span class="text-xs">{{ $year[0] ?? '' }}</span>
                                </span>
                            @endif

                            @if($year[1] ?? false)
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 bg-blue-600 inline-block rounded-full"></span>
                                    <span class="text-xs">{{ $year[1] ?? '' }}</span>
                                </span>
                            @endif
                        </div>
                            @if(empty($year[0]))
                                    <div class="flex flex-col items-center justify-center py-24 px-8 text-gray-500 text-center">
                                        <span class="material-symbols-rounded-big text-slate-400">bar_chart</span>
                                        <p class="mt-2 text-xs font-semibold">No sales by category found for this year.</p>
                                    </div>
                            @else
                                <div class="overflow-x-auto mt-2 scrollbar-custom">
                                    <div id="productChart" 
                                        data-categories='@json($categories ?? [])' 
                                        data-products='@json($products ?? [])' 
                                        data-products-prev='@json($productsPrev ?? [])' 
                                        data-products-ave='@json($productsAve ?? [])'
                                        data-year='@json($year ?? [])'
                                        style="height: 250px; min-width: 390px;" 
                                        class="w-full">
                                        <canvas></canvas>
                                    </div>
                                </div>
                            @endif
                    </div>
                    <div class="bg-white p-5 rounded shadow border">
                        <p class="text-left text-black font-semibold text-xs">Sales VS Loss - {{ $dateDisplay->format('F') }}</p>
                        <div class="w-full mt-3">
                            @if((end($losses) ?? 0) == 0 || (end($sales) ?? 0) == 0)
                                <div class="flex flex-col items-center justify-center py-24 text-gray-500 text-center">
                                    <span class="material-symbols-rounded-big text-slate-400">donut_small</span>
                                    <p class="mt-2 text-xs font-semibold">No sales or losses recorded for this month yet.</p>
                                </div>
                            @else
                                <div id="salesVSlossChart" 
                                    data-sales='@json($sales ?? [])' 
                                    data-losses='@json($losses ?? [])'
                                    style="height: 350px;">
                                    <canvas></canvas>
                                    
                                    <div class="flex justify-center items-stretch gap-6 mt-4">
                                        <div class="flex flex-col justify-center text-center px-4">
                                            <p class="text-4xl font-bold text-green-700 leading-none"> %</p>
                                            <p class="text-xs text-gray-600 mt-1">Sales</p>
                                        </div>
                                        <div class="border-l-2 border-gray-400 self-center h-12"></div>
                                        <div class="flex flex-col justify-center text-center px-4">
                                            <p class="text-4xl font-bold text-red-700 leading-none">28%</p>
                                            <p class="text-xs text-gray-600 mt-1">Loss</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            <!-- profit chart -->
            <div class="bg-white p-5 rounded shadow border">
                    <p class="text-left text-black font-semibold text-xs border-b border-gray-200 pb-5">Monthly Net Profit</p>
                <div class="flex items-center justify-between pt-4 gap-6">

                    <div class="flex flex-col">
                        <span class="text-xl font-bold">
                            {{ $dateDisplay->format('F Y') }}
                        </span>
                        <p class="text-xs">
                            {{ $dateDisplay->format('D, d') }}
                        </p>
                    </div>

                    <div class="flex flex-col text-right">
                        @if (is_null($profitMonth) || $profitMonth === 0)
                            <span class="text-xl text-red-700">
                                Empty database.
                            </span>
                        @else
                            <span class="text-xl font-bold">
                                ₱{{ number_format($profitMonth, 2) }}
                            </span>
                        @endif
                        <p class="text-xs">Current Net Profit</p>
                    </div>

                    <div class="flex-1 flex items-center justify-end gap-3">
                        <a href="{{ route('dashboards.owner.expense_record') }}"
                        class="bg-red-100 border border-red-900 px-6 py-2.5 rounded text-xs text-center">
                            View
                        </a>

                        <!-- Year Selector -->
                        <form method="GET" action="{{ route('dashboards.owner.dashboard') }}">
                            <select name="year" id="year"
                                class="rounded px-6 py-2.5 mt-4 border-gray-300 text-gray-700 text-xs focus:ring focus:ring-blue-200 focus:border-blue-400"
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
                <div x-data x-init="$el.scrollLeft = $el.scrollWidth" class="w-full overflow-x-auto mt-3 scrollbar-custom">
                    <div id="profitChart" 
                        data-profits='@json($profits ?? [])' 
                        data-months='@json($months ?? [])'
                        style="height: 355px; width: 600px;">
                        <canvas></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- table dapit -->
        <div class="space-y-4">
            <div class="grid p-5 bg-white rounded shadow border">
                <h3 class="text-xs font-semibold text-black mb-5">Comparative Analysis</h3>
                
                <div x-init="setTimeout(() => { $el.scrollLeft = $el.scrollWidth }, 100)" class="overflow-x-auto scrollbar-custom">
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
                                                $next = $netprofits[$index + 1];
                                                $diff = $next - $profit;

                                                if ($profit == 0) {
                                                    if ($next > 0) {
                                                        $percent = null;   // treat as infinity growth
                                                        $status = 'increased';
                                                    } elseif ($next < 0) {
                                                        $percent = -100;   // from 0 to negative → 100% drop
                                                        $status = 'decreased';
                                                    } else {
                                                        $percent = 0;      // 0 to 0
                                                        $status = 'nochange';
                                                    }
                                                } else {
                                                    $percent = ($diff / abs($profit)) * 100;
                                                    $status = $percent > 0 ? 'increased' : ($percent < 0 ? 'decreased' : 'nochange');
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
            <div class="grid p-5 bg-white rounded shadow border">
                @livewire('product-analysis')
            </div>
        </div>
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