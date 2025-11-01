<div>
    <div class="grid p-5 bg-white rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-xs font-semibold text-gray-900 mb-5">Comparative Analysis</h3>
        
        <div x-init="setTimeout(() => { $el.scrollLeft = $el.scrollWidth }, 100)" class="overflow-x-auto scrollbar-custom rounded-lg border border-gray-200">
            <table class="text-sm text-left text-slate-700 border-collapse table-auto w-full"
                wire:poll.15s="comparativeAnalysis" wire:keep-alive>
                <thead>
                    <tr class="bg-red-600 text-xs text-slate-500 uppercase">
                        <th class="sticky left-0 z-10 bg-red-600 shadow-sm px-4 py-3 border-b border-red-400 text-white font-semibold">Metric</th>
                        @if (count($expenses) === 0)
                            <td class="px-4 py-4 text-center text-xs text-white w-full border-b border-red-400">
                            </td>
                        @else   
                            @foreach ($tableMonthNames as $index => $month)
                                <th class="px-4 py-3 border-b border-red-400 text-white font-semibold">{{ $month }}</th>

                                @if ($index < count($tableMonthNames) - 1)
                                    <th class="px-4 py-3 border-b border-red-400 text-white font-semibold whitespace-nowrap">
                                        {{ $month }}-{{ $tableMonthNames[$index + 1] }} (%)
                                    </th>
                                @endif
                            @endforeach
                        @endif
                    </tr>
                </thead>

                <tbody>
                    <tr class="bg-red-50 border-t-2 border-red-200">
                        <td colspan="25" class="sticky left-0 z-10 bg-red-50 px-4 py-2.5 text-center text-xs font-semibold text-red-800">
                            Money Spent (Negative % is Better)
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="sticky left-0 z-10 bg-white hover:bg-gray-50 px-4 py-4 border-b border-gray-200 text-xs font-semibold text-gray-900">In-Store Expenses</td>
                        @if (count($expenses) === 0)
                            <td class="px-4 py-4 text-center text-xs text-gray-500 w-full border-b border-gray-200">
                                No data available
                            </td>
                        @else
                            @foreach ($expenses as $index => $expense)
                                <td class="px-4 py-4 border-b border-gray-200 text-xs text-gray-600">
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
                                    <td class="px-4 py-4 border-b border-gray-200 text-xs font-semibold 
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
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="sticky left-0 z-10 bg-white hover:bg-gray-50 px-4 py-4 border-b border-gray-200 text-xs font-semibold text-gray-900">Revenue Loss</td>
                        @if (count($losses) === 0)
                            <td class="px-4 py-4 text-center text-xs text-gray-500 w-full border-b border-gray-200">
                                No data available
                            </td>
                        @else
                            @foreach ($losses as $index => $loss)
                                @if (is_null($loss) || $loss === '')
                                    <td class="px-4 py-4 border-b border-gray-200 text-xs text-gray-400">
                                        --
                                    </td>
                                @else
                                    <td class="px-4 py-4 border-b border-gray-200 text-xs text-gray-600">
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
                                    <td class="px-4 py-4 border-b border-gray-200 text-xs font-semibold 
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
                    <tr class="bg-green-50 border-t-2 border-green-200">
                        <td colspan="25" class="sticky left-0 z-10 bg-green-50 px-4 py-2.5 text-center text-xs font-semibold text-green-800">
                            Money Earned (Positive % is Better)
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="sticky left-0 z-10 bg-white hover:bg-gray-50 px-4 py-4 border-b border-gray-200 text-xs font-semibold text-gray-900">Total Sales</td>
                        @if (count($sales) === 0)
                            <td class="px-4 py-4 text-center text-xs text-gray-500 w-full border-b border-gray-200">
                                No data available
                            </td>
                        @else
                            @foreach ($sales as $index => $sale)
                                @if (is_null($sale) || $sale === '')
                                    <td class="px-4 py-4 border-b border-gray-200 text-xs text-gray-400">
                                        --
                                    </td>
                                @else
                                    <td class="px-4 py-4 border-b border-gray-200 text-xs text-gray-600">
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

                                    <td class="px-4 py-4 border-b border-gray-200 text-xs font-semibold 
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
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="sticky left-0 z-10 bg-white hover:bg-gray-50 px-4 py-4 border-b border-gray-200 text-xs font-semibold text-gray-900">Net Profit</td>
                        @if (count($netprofits) === 0)
                            <td class="px-4 py-4 text-center text-xs text-gray-500 w-full border-b border-gray-200">
                                No data available
                            </td>
                        @else
                            @foreach ($netprofits as $index => $profit)
                                <td class="px-4 py-4 border-b border-gray-200 text-xs text-gray-600">
                                    ₱{{ number_format($profit, 2) }}
                                </td>

                                @if ($index < count($netprofits) - 1)
                                    @php
                                        $next = $netprofits[$index + 1];
                                        $diff = $next - $profit;

                                        if ($profit == 0) {
                                            if ($next > 0) {
                                                $percent = null;
                                                $status = 'increased';
                                            } elseif ($next < 0) {
                                                $percent = -100;
                                                $status = 'decreased';
                                            } else {
                                                $percent = 0;
                                                $status = 'nochange';
                                            }
                                        } else {
                                            $percent = ($diff / abs($profit)) * 100;
                                            $status = $percent > 0 ? 'increased' : ($percent < 0 ? 'decreased' : 'nochange');
                                        }
                                    @endphp

                                    <td class="px-4 py-4 border-b border-gray-200 text-xs font-semibold 
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
    </div>
</div>