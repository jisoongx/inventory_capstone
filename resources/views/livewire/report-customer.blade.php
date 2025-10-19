<div x-data="{ tab: 'basket' }" class="w-full px-4">

    <div class="flex space-x-1">
        <button 
            @click="tab = 'basket'"
            :class="tab === 'basket' 
                ? 'bg-green-50 text-black border-green-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Basket Analysis
        </button>

        <button 
            @click="tab = 'frequency'"
            :class="tab === 'frequency' 
                ? 'bg-red-50 text-black border-red-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Purchase Frequency
        </button>
        
    </div>

    <div class="border bg-white p-4 rounded-b-lg mb-3 h-[40rem]"
        :class="{
            'border-green-500 bg-green-50': tab === 'basket',
            'border-red-500 bg-red-50': tab === 'frequency'
        }">


        <!-- BASKET ANALYSIS -->
        <div x-show="tab === 'basket'">

            <div class="">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <select wire:model="month" class="border border-gray-300 rounded px-3 py-2 text-xs">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>

                        <select wire:model="year" class="border border-gray-300 rounded px-3 py-2 text-xs">
                            @for ($y = now()->year; $y >= now()->year - 3; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>

                        <button wire:click="generateReport"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-xs font-medium">
                            Generate Report
                        </button>
                    </div>
                    @if (!empty($type))
                        <div class=" mb-2">
                            <div class="inline-flex items-center px-3 py-2 rounded-tl-lg rounded-bl-lg border border-green-500 space-x-1 bg-green-100">
                                <span class="text-xs">Analysis Type:</span>
                            </div>

                            <div class="inline-flex items-center px-3 py-2 rounded-tr-lg rounded-br-lg border border-green-500 space-x-1 w-[15.3rem]">
                                <span class="text-xs font-medium">{{ $type }}</span>
                            </div>
                        </div>
                    @endif
                </div>


                <div class="overflow-y-auto scrollbar-custom h-[33rem]">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-100 border-b-2 border-gray-300 sticky top-0">
                                <th class="p-3 text-left font-semibold text-gray-700 text-xs sticky top-0">Product A</th>
                                <th class="p-3 text-left font-semibold text-gray-700 text-xs sticky top-0">Product B</th>
                                <th class="p-3 text-center font-semibold text-gray-700 text-xs sticky top-0">Times Bought Together</th>
                                <th class="p-3 text-center font-semibold text-gray-700 text-xs sticky top-0">Association Strength</th>
                                <th class="p-3 text-center font-semibold text-gray-700 text-xs sticky top-0">Relationship Score</th>
                                <th class="p-3 text-left font-semibold text-gray-700 text-xs w-[20rem] sticky top-0">Insights</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (is_array($results) && count($results) > 0 && !isset($results['message']))
                                @foreach ($results as $row)
                                    @if (is_array($row))
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="p-3 text-gray-800 font-medium text-xs">{{ $row['productA'] ?? '' }}</td>
                                            <td class="p-3 text-gray-800 font-medium text-xs">{{ $row['productB'] ?? '' }}</td>
                                            <td class="p-3 text-center text-gray-700 text-xs">{{ $row['supportCount'] ?? '' }}</td>
                                            <td class="p-3 text-center text-gray-700 text-xs">{{ $row['confidenceText'] ?? '' }}</td>
                                            <td class="p-3 text-center text-gray-700 text-xs">{{ $row['lift'] ?? '' }}</td>
                                            <td class="p-3 text-sm text-gray-700 text-xs">{!! $row['summary'] ?? '' !!}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            @else
                                @if (isset($results['message']))
                                    <tr>
                                        <td colspan="6" class="p-6 py-52 text-center text-gray-500 text-xs">
                                            <div class="flex flex-col justify-center items-center space-y-3">
                                                <span class="material-symbols-rounded-semibig text-gray-400">taunt</span>
                                                <span class="text-gray-500 text-xs">{{ $results['message'] }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="6" class="p-6 py-52 text-center text-gray-500 text-xs">
                                            <div class="flex flex-col justify-center items-center space-y-3">
                                                <span class="material-symbols-rounded-semibig text-gray-400">ads_click</span>
                                                <span class="text-gray-500 text-xs">No data yet. Click “Generate Report” to start analysis.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>


        </div>

        <!-- PURCHASE FREQUENCY -->
        <div x-show="tab === 'frequency'">
            <p class="text-gray-700">⚡ <b>Purchase Frequency</b> report content goes here.</p>
        </div>
    </div>
</div>

