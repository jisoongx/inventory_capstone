<div x-data="{ tab: 'association' }" class="w-full px-4">

    <div class="flex space-x-1">
        <button 
            @click="tab = 'association'"
            :class="tab === 'association' 
                ? 'bg-green-50 text-black border-green-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Product Association
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

    <div class="border bg-white rounded-b-lg h-[41rem]"
        :class="{
            'border-green-500 bg-green-50': tab === 'association',
            'border-red-500 bg-red-50': tab === 'frequency'
        }">


        <!-- Product Association -->
        <div x-show="tab === 'association'" class="bg-white rounded-lg shadow-sm">
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Product Association Analysis</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Discover product buying patterns and relationships</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select wire:model="month" 
                            class="text-xs border border-gray-300 rounded-lg px-4 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>

                        <select wire:model="year" 
                            class="text-xs border border-gray-300 rounded-lg px-4 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @forelse ($years as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @empty
                                <option value="{{ now()->year }}">{{ now()->year }}</option>
                            @endforelse
                        </select>

                        <button wire:click="generateReport"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-colors shadow-sm flex items-center gap-2">
                            <span class="material-symbols-rounded text-lg">analytics</span>
                            Generate Report
                        </button>
                    </div>
                </div>
                
                @if (!empty($type))
                    <div class="mt-3 inline-flex items-center bg-green-50 border border-green-200 rounded-lg overflow-hidden">
                        <div class="px-3 py-1.5 bg-green-100 border-r border-green-200">
                            <span class="text-xs font-medium text-gray-700">Analysis Type:</span>
                        </div>
                        <div class="px-3 py-1.5">
                            <span class="text-xs font-semibold text-green-700">{{ $type }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="overflow-y-auto overflow-x-auto scrollbar-custom h-[33.1rem]">
                <table class="w-full text-sm {{ (is_array($results) && count($results) > 0 && !isset($results['message'])) ? 'w-[85rem]' : 'w-full' }}">
                    <thead class="sticky top-0 bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]">
                        <tr class="sticky top-0 bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]">
                            <th colspan="2" class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Product Pair
                            </th>
                            
                            <th colspan="3" class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Association Metrics
                            </th>
                            
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-blue-50 border-l-2 border-gray-300">
                                Analysis
                            </th>
                        </tr>
                        <tr class="sticky bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]" style="top: 42px;">
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100">Product A</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100">Product B</th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">Times Bought Together</th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">Association Strength</th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">Relationship Score</th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">Remarks</th>
                        </tr>
                    </thead>
                    
                    <tbody class="divide-y divide-gray-200 bg-white text-xs">
                        @if (is_array($results) && count($results) > 0 && !isset($results['message']))
                            @foreach ($results as $row)
                                @if (is_array($row))
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-4 py-3.5 text-gray-900 font-semibold">
                                            {{ $row['productA'] ?? '' }}
                                        </td>
                                        <td class="px-4 py-3.5 text-gray-900 font-medium">
                                            {{ $row['productB'] ?? '' }}
                                        </td>
                                        
                                        <!-- Association Metrics -->
                                        <td class="px-4 py-3.5 text-center font-bold text-gray-900 bg-gray-50">
                                            {{ $row['supportCount'] ?? '' }}
                                        </td>
                                        <td class="px-4 py-3.5 text-center font-semibold text-gray-700 bg-gray-50">
                                            {{ $row['confidenceText'] ?? '' }}
                                        </td>
                                        <td class="px-4 py-3.5 text-center font-bold text-blue-600 bg-gray-50">
                                            {{ $row['lift'] ?? '' }}
                                        </td>
                                        <!-- Insights -->
                                        <td class="px-4 py-3.5 text-center font-semibold bg-blue-50 w-[30%] text-[10px]">
                                            {!! $row['summary'] ?? '' !!}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-16">
                                    <div class="flex flex-col justify-center items-center space-y-3">
                                        @if (isset($results['message']))
                                            <span class="material-symbols-rounded text-6xl text-gray-300">search_off</span>
                                            <div>
                                                <p class="text-gray-600 font-medium">{{ $results['message'] }}</p>
                                                <p class="text-gray-400 text-sm mt-1">Try selecting a different time period</p>
                                            </div>
                                        @else
                                            <span class="material-symbols-rounded text-6xl text-gray-300">analytics</span>
                                            <div>
                                                <p class="text-gray-600 font-medium">Ready to Analyze</p>
                                                <p class="text-gray-400 text-sm mt-1">Click "Generate Report" to discover product associations</p>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PURCHASE FREQUENCY -->
<div x-show="tab === 'frequency'" class="bg-white rounded-lg shadow-sm">
    <!-- Report Header -->
    <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Sales Frequency Analysis</h2>
                <p class="text-xs text-gray-500 mt-0.5">Daily transaction patterns and sales trends</p>
            </div>
            <div class="flex items-center gap-3">
                <select wire:model.live="frequencySelectMonth" 
                    class="text-xs border border-gray-300 rounded-lg px-4 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @foreach ($monthNames as $index => $name)
                        <option value="{{ $index + 1 }}">{{ $name }}</option>
                    @endforeach
                </select>
                
                <select wire:model.live="frequencySelectYear" 
                    class="text-xs border border-gray-300 rounded-lg px-4 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @forelse ($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @empty
                        <option value="{{ now()->year }}">{{ now()->year }}</option>
                    @endforelse
                </select>
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="overflow-y-auto overflow-x-auto scrollbar-custom h-[36rem]">
        <table class="w-full text-sm {{ $frequency->isNotEmpty() ? 'w-full' : 'w-full' }}">
            <thead class="bg-gray-100 sticky top-0 z-10">
                <tr class="sticky top-0 bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]">
                    <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                        Date
                    </th>
                    
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                        Transactions
                    </th>
                    
                    <th colspan="2" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                        Sales Performance
                    </th>
                    
                    <th colspan="2" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-blue-50 border-l-2 border-gray-300">
                        
                    </th>
                </tr>
                <tr class="sticky bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]" style="top: 42px;">
                    <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                    <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-100"></th>
                    
                    <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Total Sales</th>
                    <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Avg Transaction</th>
                    
                    <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">
                        <div class="flex items-center justify-end gap-1">
                            <span>Sales Change</span>
                            <span class="material-symbols-rounded text-gray-500 text-sm cursor-help" 
                                title="Sales change is continuous and may include comparisons from previous months">info</span>
                        </div>
                    </th>
                    <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">Peak Hour</th>
                </tr>
            </thead>
            
            <tbody class="divide-y divide-gray-200 bg-white text-xs">
                @forelse ($frequency as $row)
                    <tr class="hover:bg-gray-50 transition-colors duration-150" wire:key="freq-{{ $row->date }}">
                        <td class="px-4 py-3.5 font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}
                            <div class="text-[10px] text-gray-500">
                                {{ \Carbon\Carbon::parse($row->date)->format('l') }}
                            </div>
                        </td>
                        
                        <td class="px-4 py-3.5 text-center font-bold text-gray-900">
                            {{ number_format($row->total_transaction) }}
                        </td>
                        
                        <!-- Sales Performance -->
                        <td class="px-4 py-3.5 text-right font-bold text-green-600 bg-gray-50">
                            ₱{{ number_format($row->total_sales, 2) }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-semibold text-gray-700 bg-gray-50">
                            ₱{{ number_format($row->average_sales, 2) }}
                        </td>
                        
                        <!-- Insights -->
                        <td class="px-4 py-3.5 text-center font-bold bg-blue-50
                            @if($row->sales_change_percent > 0) text-green-600
                            @elseif($row->sales_change_percent < 0) text-red-600
                            @else text-gray-600
                            @endif">
                            @if($row->sales_change_percent > 0)
                                <span class="inline-flex items-center gap-1">
                                    <span class="material-symbols-rounded text-sm">trending_up</span>
                                    +{{ number_format($row->sales_change_percent, 1) }}%
                                </span>
                            @elseif($row->sales_change_percent < 0)
                                <span class="inline-flex items-center gap-1">
                                    <span class="material-symbols-rounded text-sm">trending_down</span>
                                    {{ number_format($row->sales_change_percent, 1) }}%
                                </span>
                            @else
                                <span class="text-gray-600">{{ number_format($row->sales_change_percent, 1) }}%</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center font-semibold text-gray-700 bg-blue-50">
                            @if($row->peak_hour !== null)
                                {{ date('g A', mktime($row->peak_hour)) }} - {{ date('g A', mktime(($row->peak_hour + 1) % 24)) }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr wire:key="freq-empty">
                        <td colspan="6" class="text-center py-16">
                            <div class="flex flex-col justify-center items-center space-y-3">
                                <span class="material-symbols-rounded text-6xl text-gray-300">event_busy</span>
                                <div>
                                    <p class="text-gray-600 font-medium">No Transactions Found</p>
                                    <p class="text-gray-400 text-sm mt-1">No transactions recorded for this period</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if($frequency->isNotEmpty())
            <tfoot class="sticky bottom-0 z-10 bg-slate-100 shadow-[0_-1px_0_0_rgb(209,213,219)]">
                <tr class="border-t-2 border-gray-600">
                    <td class="px-4 py-4 text-left font-bold uppercase text-xs tracking-wider">
                        Total Summary
                    </td>
                    <td class="px-4 py-4 text-center font-bold text-sm">
                        {{ number_format($frequency->sum('total_transaction')) }}
                    </td>
                    <td class="px-4 py-4 text-right font-bold text-sm text-green-600">
                        ₱{{ number_format($frequency->sum('total_sales'), 2) }}
                    </td>
                    <td class="px-4 py-4 text-right font-bold text-sm">
                        ₱{{ number_format($frequency->avg('average_sales'), 2) }}
                    </td>
                    <td colspan="2" class="px-4 py-4 text-center text-xs text-gray-600">
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
    </div>
</div>

