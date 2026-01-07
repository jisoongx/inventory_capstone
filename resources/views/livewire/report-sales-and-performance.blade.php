<div x-data="{ tab: 'sales' }" class="w-full px-4 {{ ($expired || $plan === 3) ? 'blur-sm pointer-events-none select-none' : '' }}">

    <div class="flex space-x-1">
        <button 
            @click="tab = 'sales'"
            :class="tab === 'sales' 
                ? 'bg-green-50 text-black border-green-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Sales (Daily/Monthly)
        </button>

        <button 
            @click="tab = 'sales-category'"
            :class="tab === 'sales-category' 
                ? 'bg-orange-50 text-black border-orange-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Sales by Category
        </button>

        <button 
            @click="tab = 'product-performance'"
            :class="tab === 'product-performance' 
                ? 'bg-purple-50 text-black border-purple-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Product Performance
        </button>

         <button 
            @click="tab = 'loss'"
            :class="tab === 'loss' 
                ? 'bg-blue-50 text-black border-blue-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Profit Loss Report
        </button>

    </div>

    <div class="border bg-white rounded-b-lg h-[41rem]"
        :class="{
            'border-green-500 bg-green-50': tab === 'sales',
            'border-orange-500 bg-orange-50': tab === 'sales-category',
            'border-purple-500 bg-purple-50': tab === 'product-performance',
            'border-blue-500 bg-blue-50': tab === 'loss'
        }">

<!-- Conditional Polling - Only when no modals are open -->
@if(!$showOriginalReceiptModal && !$showReturnReceiptModal && !$showExportModal && !$showGlobalReturnHistory)
    <div wire:poll.15s="pollAll" class="hidden"></div>
@endif

        <!-- DAILY/MONTHLY SALES TAB -->
        <div x-show="tab === 'sales'" class="rounded-lg shadow-sm">
            <!-- Sales Analytics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 px-6 pt-4">
                <div class="bg-gradient-to-br from-green-50 to-white rounded-lg p-4 border border-green-200 shadow-sm hover:shadow-md transition-shadow duration-150">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-600 font-medium mb-1">Total Transactions</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $salesAnalytics->total_transactions ?? 0 }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <span class="material-symbols-rounded text-green-600 text-3xl">receipt_long</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-white rounded-lg p-4 border border-green-200 shadow-sm hover:shadow-md transition-shadow duration-150">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-600 font-medium mb-1">Gross Sales</p>
                            <p class="text-2xl font-bold text-green-600">₱{{ number_format($salesAnalytics->gross_sales ?? 0, 2) }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <span class="material-symbols-rounded text-green-600 text-3xl">trending_up</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-white rounded-lg p-4 border border-blue-200 shadow-sm hover:shadow-md transition-shadow duration-150">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-600 font-medium mb-1">Net Profit</p>
                            <p class="text-2xl font-bold text-blue-600">₱{{ number_format($salesAnalytics->net_profit ?? 0, 2) }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <span class="material-symbols-rounded text-blue-600 text-3xl">payments</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Header -->
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4 mt-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Sales Transactions Report</h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Items Sold: <span class="font-bold text-gray-900">{{ $salesAnalytics->total_items_sold ?? 0 }}</span>
                            <span class="mx-2">•</span>
                            Avg. Transaction: <span class="font-bold text-gray-900">₱{{ number_format($salesAnalytics->avg_transaction_value ?? 0, 2) }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-3" x-data="{ showQuickDates: false }">
                        <!-- Quick Date Range -->
                        <div class="relative">
                            <button @click="showQuickDates = !showQuickDates" 
                                class="text-xs border border-gray-300 rounded-lg px-4 py-2 bg-white shadow-sm hover:bg-gray-50 transition-colors flex items-center gap-2">
                                <span class="material-symbols-rounded text-sm">schedule</span>
                                Quick Range
                            </button>
                            <div x-show="showQuickDates" @click.away="showQuickDates = false" x-cloak
                                class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50 py-2">
                                <button wire:click="setQuickDateRange('7days')" 
                                    class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 transition-colors">
                                    Last 7 Days
                                </button>
                                <button wire:click="setQuickDateRange('30days')" 
                                    class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 transition-colors">
                                    Last 30 Days
                                </button>
                                <button wire:click="setQuickDateRange('3months')" 
                                    class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 transition-colors">
                                    Last 3 Months
                                </button>
                                <div class="border-t border-gray-200 my-1"></div>
                                <button wire:click="setQuickDateRange('thismonth')" 
                                    class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 transition-colors">
                                    This Month
                                </button>
                                <button wire:click="setQuickDateRange('thisyear')" 
                                    class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 transition-colors">
                                    This Year
                                </button>
                                <button wire:click="setQuickDateRange('lastyear')" 
                                    class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 transition-colors">
                                    Last Year
                                </button>
                            </div>
                        </div>

                        <!-- Date Range Filter --> 
                        <input type="date" wire:model.live="dateFrom"
                            class="text-xs border border-gray-300 rounded-lg px-3 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"/>
                        <input type="date" wire:model.live="dateTo"
                            class="text-xs border border-gray-300 rounded-lg px-3 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"/>
                        
                        <button wire:click="resetDateFilters" 
                            class="text-xs border border-gray-300 rounded-lg p-2 bg-white shadow-sm hover:bg-gray-50 transition-colors"
                            title="Reset dates">
                            <span class="material-symbols-rounded text-lg">refresh</span>
                        </button>

                        <!-- Global Return History Button -->
                        <button wire:click="viewGlobalReturnHistory" 
                            title="View Return History" 
                            class="bg-orange-600 hover:bg-orange-700 text-white p-2 rounded-lg transition-colors shadow-sm">
                            <span class="material-symbols-rounded text-xl">history</span>
                        </button>

                        <!-- Export Button -->
                        <button wire:click="toggleExportModal" title="Export Report" 
                            class="bg-green-600 hover:bg-green-700 text-white p-2 rounded-lg transition-colors shadow-sm">
                            <span class="material-symbols-rounded text-xl">description</span>
                        </button>

                        <!-- Start Transaction Button -->
                        <a href="{{ route('store_start_transaction') }}" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-xs flex items-center gap-2 transition-colors shadow-sm">
                            <span class="material-symbols-rounded text-lg">add_shopping_cart</span>
                            Start Transaction
                        </a>
                    </div>
                </div>
            </div>

            <!-- Table Container -->
            <div class="overflow-y-auto overflow-x-auto scrollbar-custom h-[28.5rem]">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 sticky top-0 z-10">
                        <tr class="sticky top-0 bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]">
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Receipt No.
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Date & Time
                            </th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Total Quantity
                            </th>
                            
                            <th colspan="3" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Transaction Details
                            </th>
                            
                            <th class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-blue-50 border-l-2 border-gray-300">
                                Actions
                            </th>
                        </tr>
                        <tr class="sticky bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]" style="top: 42px;">
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-100"></th>
                            
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Total Amount</th>
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Amount Paid</th>
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Change</th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">View / Return</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white text-xs">
                            @forelse($transactions as $transaction)
                                <tr class="hover:bg-gray-50 transition-colors duration-150 cursor-pointer" 
                                    wire:click="viewReceipt({{ $transaction->receipt_id }})">                                
                                    <td class="px-4 py-3.5 font-bold text-gray-900">
                                        <div class="flex items-center gap-2">
                                            #{{ str_pad($transaction->receipt_id, 6, '0', STR_PAD_LEFT) }}
                                            @if(in_array($transaction->receipt_id, $hasReturns))
                                                <span class="material-symbols-rounded text-orange-600 text-base" title="Has returns">
                                                    undo
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-gray-700">
                                        {{ \Carbon\Carbon::parse($transaction->receipt_date)->format('M d, Y') }}
                                        <div class="text-[10px] text-gray-500">
                                            {{ \Carbon\Carbon::parse($transaction->receipt_date)->format('h:i A') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-bold text-gray-900">
                                        {{ number_format($transaction->total_quantity) }}
                                    </td>
                                    
                                    <td class="px-4 py-3.5 text-right bg-gray-50">
                                        <div class="flex flex-col items-end gap-1">
                                            <span class="font-bold text-green-600">
                                                ₱{{ number_format($transaction->total_amount, 2) }}
                                            </span>
                                            
                                            <div class="flex items-center gap-1.5">
                                                @if($transaction->has_receipt_discount)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full text-[11px] font-bold border border-orange-300"
                                                        title="Receipt discount applied">
                                                        RD:  ₱{{ number_format($transaction->receipt_discount_amount, 2) }}
                                                    </span>
                                                @endif
                                                
                                                @if($transaction->has_item_discounts && !   $transaction->has_receipt_discount)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-[11px] font-bold border border-blue-300"
                                                        title="Item discounts applied">
                                                        ID: ₱{{ number_format($transaction->total_item_discounts_raw, 2) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-semibold text-gray-700 bg-gray-50">
                                        ₱{{ number_format($transaction->amount_paid, 2) }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-bold text-blue-600 bg-gray-50">
                                        ₱{{ number_format($transaction->change, 2) }}
                                    </td>

                                    <td class="px-4 py-3.5 text-center bg-blue-50">
                                        <div class="flex items-center justify-center gap-2">
                                            <button wire:click="viewReceipt({{ $transaction->receipt_id }})"
                                                class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-700 hover:bg-blue-100 rounded-lg transition-colors"
                                                title="View Receipt">
                                                <span class="material-symbols-rounded text-xl">visibility</span>
                                            </button>
                                            
                                            <a href="{{ route('return_item', $transaction->receipt_id) }}"
                                                class="inline-flex items-center justify-center w-8 h-8 text-orange-600 hover:text-orange-700 hover:bg-orange-100 rounded-lg transition-colors"
                                                title="Return Items">
                                                <span class="material-symbols-rounded text-xl">undo</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-16">
                                        <div class="flex flex-col justify-center items-center space-y-3">
                                            <span class="material-symbols-rounded text-6xl text-gray-300">receipt_long</span>
                                            <div>
                                                <p class="text-gray-600 font-medium">No Transactions Found</p>
                                                <p class="text-gray-400 text-sm mt-1">No sales transactions for the selected period</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                    </tbody>

                    @if($transactions->isNotEmpty())
                    <tfoot class="sticky bottom-0 z-10 bg-slate-100 shadow-[0_-1px_0_0_rgb(209,213,219)]">
                        <tr class="border-t-2 border-gray-600">
                            <td colspan="2" class="px-4 py-3 text-left font-bold uppercase text-xs tracking-wider">
                                Total Summary
                            </td>
                            <td class="px-4 text-center font-bold text-sm">
                                {{ number_format($transactions->sum('total_quantity')) }}
                            </td>
                            <td class="px-4 text-right font-bold text-sm text-green-600">
                                ₱{{ number_format($transactions->sum('total_amount'), 2) }}
                            </td>
                            <td class="px-4 text-right font-bold text-sm">
                                ₱{{ number_format($transactions->sum('amount_paid'), 2) }}
                            </td>
                            <td class="px-4 text-right font-bold text-sm text-blue-600">
                                ₱{{ number_format($transactions->sum('change'), 2) }}
                            </td>
                            <td class="px-4 text-center text-xs text-gray-400">
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>


        <!-- SALES BY CATEGORY TAB -->
        <div x-show="tab === 'sales-category'" class="bg-white rounded-lg shadow-sm">
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Sales by Category Report</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Analyze sales performance and profitability across product categories</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative flex items-center">
                            <span class="absolute left-3 flex items-center pointer-events-none text-gray-400">
                                <span class="material-symbols-rounded text-base">search</span>
                            </span>
                            <input 
                                type="text"
                                wire:model.live="searchWord"
                                placeholder="Search Category..."
                                class="text-xs rounded-lg border border-gray-300 pl-9 pr-3 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>

                        <label class="text-xs font-medium text-gray-700">Filter by Period:</label>
                        <select wire:model.live="selectedMonths"
                            class="text-xs border border-gray-300 rounded-lg px-4 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($monthNames as $index => $name)
                                <option value="{{ $index + 1 }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="selectedYears"
                            class="text-xs border border-gray-300 rounded-lg px-4 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($years as $yr)
                                <option value="{{ $yr->year }}">{{ $yr->year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="overflow-y-auto overflow-x-auto scrollbar-custom h-[36.3rem]">
                <table class="w-full text-sm {{ $sbc->isNotEmpty() ? 'min-w-[65rem]' : 'w-full' }}">
                    <thead class="bg-gray-100 sticky top-0 z-10">
                        <tr class="sticky top-0 bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]">
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Category
                            </th>
                            
                            <th colspan="2" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Stock Status
                            </th>
                            
                            <th class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Financial Performance
                            </th>
                            
                            <th class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Profitability
                            </th>
                            
                            <th class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-blue-50 border-l-2 border-gray-300">
                                Analysis
                            </th>
                        </tr>
                        <tr class="sticky bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]" style="top: 42px;">
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">Units Sold</th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">
                                <div class="flex items-center justify-center gap-1">
                                    <span>Stock Left</span>
                                    <span class="material-symbols-rounded text-gray-500 text-sm cursor-help" title="Current stock remaining in inventory">info</span>
                                </div>
                            </th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">Total Sales</th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">Gross Margin %</th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">Insight</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white text-xs">
                        @forelse($sbc as $input)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-4 py-3.5 text-gray-900 font-semibold">
                                    {{ $input->category }}
                                </td>
                                
                                <!-- Stock Status -->
                                <td class="px-4 py-3.5 text-center font-bold text-gray-900 bg-gray-50">
                                    {{ number_format($input->unit_sold) }}
                                </td>
                                <td class="px-4 py-3.5 text-center font-semibold bg-gray-50
                                    @if($input->stock_left == 0) text-red-600
                                    @elseif($input->stock_left < 10) text-orange-600
                                    @elseif($input->stock_left < 50) text-yellow-600
                                    @else text-blue-600
                                    @endif">
                                    {{ number_format($input->stock_left) }}
                                </td>
                                
                                <!-- Financial Performance -->
                                <td class="px-4 py-3.5 text-center font-bold text-green-600 bg-gray-50">
                                    ₱{{ number_format($input->total_amount, 2) }}
                                </td>
                                
                                <!-- Profitability -->
                                <td class="px-4 py-3.5 text-center bg-gray-50">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold
                                        @if($input->gross_margin >= 30) 
                                            bg-green-100 text-green-700 border border-green-300
                                        @elseif($input->gross_margin >= 20) 
                                            bg-blue-100 text-blue-700 border border-blue-300
                                        @elseif($input->gross_margin >= 10) 
                                            bg-yellow-100 text-yellow-700 border border-yellow-300
                                        @else 
                                            bg-red-100 text-red-700 border border-red-300
                                        @endif">
                                        {{ number_format($input->gross_margin, 1) }}%
                                    </span>
                                </td>
                                
                                <!-- Analysis -->
                                <td class="px-4 py-3.5 text-center bg-gray-50 text-[10px] font-medium w-[25rem]">
                                    {{ $input->insight }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-16">
                                    <div class="flex flex-col justify-center items-center space-y-3">
                                        <span class="material-symbols-rounded text-6xl text-gray-300">category</span>
                                        <div>
                                            <p class="text-gray-600 font-medium">No Category Data Available</p>
                                            <p class="text-gray-400 text-sm mt-1">No sales data found for the selected period</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($sbc->isNotEmpty())
                    <tfoot class="sticky bottom-0 z-10 bg-slate-100 shadow-[0_-1px_0_0_rgb(209,213,219)]">
                        <tr class="border-t-2 border-gray-600">
                            <td class="px-4 py-3 text-left font-bold uppercase text-xs tracking-wider">
                                Total Summary
                            </td>
                            <td class="px-4 text-center font-bold text-xs">
                                {{ number_format($sbc->sum('unit_sold')) }}
                            </td>
                            <td class="px-4 text-center font-bold text-xs text-blue-600">
                                {{ number_format($sbc->sum('stock_left')) }}
                            </td>
                            <td class="px-4 text-center font-bold text-xs text-green-600">
                                ₱{{ number_format($sbc->sum('total_amount'), 2) }}
                            </td>
                            <td class="px-4 text-center font-bold text-xs text-yellow-700">
                                {{ $sbc->sum('total_amount') > 0 ? number_format((($sbc->sum('total_amount') - $sbc->sum('cogs')) / $sbc->sum('total_amount')) * 100, 1) : '0.0' }}%
                            </td>
                            <td class="px-4 text-center text-xs text-gray-400">
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- PRODUCT PERFORMANCE TAB -->
        <div x-show="tab === 'product-performance'" class="bg-white rounded-lg shadow-sm">
            <!-- Report Header -->
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Product Performance Report</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Detailed analysis of individual product sales and profitability</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select wire:model.live="selectedCategory" 
                            class="text-xs border border-gray-300 rounded-lg px-4 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->category_id }}">{{ $cat->category }}</option>
                            @endforeach
                        </select>
                        
                        <select wire:model.live="selectedMonth" 
                            class="text-xs border border-gray-300 rounded-lg px-4 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($monthNames as $index => $name)
                                <option value="{{ $index + 1 }}">{{ $name }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="selectedYear" 
                            class="text-xs border border-gray-300 rounded-lg px-4 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($years as $yr)
                                <option value="{{ $yr->year }}">{{ $yr->year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table Container -->
            <div class="overflow-y-auto overflow-x-auto scrollbar-custom h-[36.3rem]">
                <table class="w-full text-sm {{ count($perf ?? []) > 0 ? 'min-w-[80rem]' : 'w-full' }}">
                    <thead class="bg-gray-100 sticky top-0 z-10">
                        <tr class="sticky top-0 bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]">
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                <button wire:click="sortBy('product_name')" class="flex items-center gap-1 hover:text-blue-600">
                                    Product Name
                                    @if($sortField === 'product_name')
                                        <span class="material-symbols-rounded text-sm">
                                            {{ $order === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                        </span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Category
                            </th>
                            
                            <th colspan="2" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Sales Metrics
                            </th>
                            
                            <th colspan="3" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Financial Performance
                            </th>
                            
                            <th class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Inventory
                            </th>
                            
                            <th class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-blue-50 border-l-2 border-gray-300">
                                Analysis
                            </th>
                        </tr>
                        <tr class="sticky bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]" style="top: 42px;">
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">
                                <button wire:click="sortBy('unit_sold')" class="flex items-center gap-1 justify-center hover:text-blue-600">
                                    Units Sold
                                    <span class="text-xs font-bold">↓↑</span>
                                </button>
                            </th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">
                                <button wire:click="sortBy('total_sales')" class="flex items-center gap-1 justify-center hover:text-blue-600">
                                    Total Sales
                                    <span class="text-sm">↓↑</span>
                                </button>
                            </th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">COGS</th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">
                                <button wire:click="sortBy('profit')" class="flex items-center gap-1 justify-center hover:text-blue-600">
                                    Profit
                                    <span class="text-sm">↓↑</span>
                                </button>
                            </th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">
                                Gross Margin %
                            </th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Stock Left</span>
                                    <span class="material-symbols-rounded text-gray-500 text-sm cursor-help"
                                        title="Current stock remaining in inventory">info</span>
                                </div>
                            </th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">Insight</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white text-xs">
                        @forelse($perf ?? [] as $product)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-4 py-3.5 text-gray-900 font-semibold">
                                    {{ $product->product_name }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 font-medium">
                                    {{ $product->category ?? 'N/A' }}
                                </td>
                                
                                <!-- Sales Metrics -->
                                <td class="px-4 py-3.5 text-center font-bold text-gray-900 bg-gray-50">
                                    {{ number_format($product->unit_sold) }}
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold text-green-600 bg-gray-50">
                                    ₱{{ number_format($product->total_sales, 2) }}
                                </td>
                                
                                <!-- Financial Performance -->
                                <td class="px-4 py-3.5 text-center font-semibold text-gray-700 bg-gray-50">
                                    ₱{{ number_format($product->cogs, 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold bg-gray-50
                                    @if($product->profit > 0) text-green-600
                                    @elseif($product->profit < 0) text-red-600
                                    @else text-gray-600
                                    @endif">
                                    ₱{{ number_format($product->profit, 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-center bg-gray-50">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold
                                        @if($product->profit_margin_percent >= 30) 
                                            bg-green-100 text-green-700 border border-green-300
                                        @elseif($product->profit_margin_percent >= 20) 
                                            bg-blue-100 text-blue-700 border border-blue-300
                                        @elseif($product->profit_margin_percent >= 10) 
                                            bg-yellow-100 text-yellow-700 border border-yellow-300
                                        @else 
                                            bg-red-100 text-red-700 border border-red-300
                                        @endif">
                                        {{ number_format($product->profit_margin_percent, 1) }}%
                                    </span>
                                </td>
                                
                                <!-- Inventory -->
                                <td class="px-4 py-3.5 text-center font-bold bg-gray-50
                                    @if($product->remaining_stocks == 0) text-red-600
                                    @elseif($product->remaining_stocks < 10) text-orange-600
                                    @elseif($product->remaining_stocks < 50) text-yellow-600
                                    @else text-blue-600
                                    @endif">
                                    {{ number_format($product->remaining_stocks) }}
                                </td>
                                
                                <!-- Analysis -->
                                <td class="px-4 py-3.5 text-center text-[10px] font-semibold bg-blue-50 w-[13rem]
                                    @if (str_contains($product->insight, 'Out of stock')) 
                                        bg-gray-800 text-white border-gray-950
                                    @elseif (str_contains($product->insight, 'Low stock')) 
                                        bg-orange-600 text-white border-orange-800
                                    @elseif (str_contains($product->insight, 'No sales')) 
                                        bg-red-600 text-white border-red-800
                                    @elseif (str_contains($product->insight, 'Unprofitable')) 
                                        bg-red-700 text-white border-red-900
                                    @elseif (str_contains($product->insight, 'Low margin')) 
                                        bg-yellow-500 text-gray-900 border-yellow-700
                                    @elseif (str_contains($product->insight, 'Performing well')) 
                                        bg-green-600 text-white border-green-800
                                    @elseif (str_contains($product->insight, 'Good margin')) 
                                        bg-blue-600 text-white border-blue-800
                                    @elseif (str_contains($product->insight, 'Moderate')) 
                                        bg-blue-500 text-white border-blue-700
                                    @else 
                                        bg-gray-600 text-white border-gray-800
                                    @endif">
                                    {{ $product->insight }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-16">
                                    <div class="flex flex-col justify-center items-center space-y-3">
                                        <span class="material-symbols-rounded text-6xl text-gray-300">inventory_2</span>
                                        <div>
                                            <p class="text-gray-600 font-medium">No Product Data Available</p>
                                            <p class="text-gray-400 text-sm mt-1">No sales data found for the selected period</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if(count($perf ?? []) > 0)
                    <tfoot class="sticky bottom-0 z-10 bg-slate-100 shadow-[0_-1px_0_0_rgb(209,213,219)]">
                        <tr class="border-t-2 border-gray-600">
                            <td colspan="2" class="px-4 py-3 text-left font-bold uppercase text-xs tracking-wider">
                                Total Summary
                            </td>
                            <td class="px-4 text-center font-bold text-xs">
                                {{ number_format(collect($perf)->sum('unit_sold')) }}
                            </td>
                            <td class="px-4 text-center font-bold text-xs text-green-600">
                                ₱{{ number_format(collect($perf)->sum('total_sales'), 2) }}
                            </td>
                            <td class="px-4 text-center font-bold text-xs">
                                ₱{{ number_format(collect($perf)->sum('cogs'), 2) }}
                            </td>
                            <td class="px-4 text-center font-bold text-xs text-green-600">
                                ₱{{ number_format(collect($perf)->sum('profit'), 2) }}
                            </td>
                            <td class="px-4 text-center font-bold text-xs text-yellow-600">
                                {{ collect($perf)->sum('total_sales') > 0 ? number_format((collect($perf)->sum('profit') / collect($perf)->sum('total_sales')) * 100, 1) : '0.0' }}%
                            </td>
                            <td class="px-4 text-center font-bold text-xs text-blue-600">
                                {{ number_format(collect($perf)->sum('remaining_stocks')) }}
                            </td>
                            <td class="px-4 text-center text-xs text-gray-400">
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div x-show="tab === 'loss'" class="bg-white rounded-lg shadow-sm">
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Stock Loss & Damage Report</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Track and analyze all inventory losses and damage incidents</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-medium text-gray-700">Period:</label>
                            <select wire:model.live="lossSelectedMonths" 
                                class="text-xs border border-gray-300 rounded-lg px-3 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @foreach ($monthNames as $index => $name)
                                    <option value="{{ $index + 1 }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <select wire:model.live="lossSelectedYears" 
                                class="text-xs border border-gray-300 rounded-lg px-3 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @forelse ($lossYears as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @empty
                                    <option value="{{ now()->year }}">{{ now()->year }}</option>
                                @endforelse
                            </select>
                        </div>
                        
                        <button wire:click="showAll" 
                            class="text-xs border rounded-lg px-3 py-2 font-medium transition-colors
                                {{ (is_null($lossSelectedMonths) || $lossSelectedMonths === '') 
                                    && (is_null($lossSelectedYears) || $lossSelectedYears === '')
                                    ? 'bg-blue-600 text-white border-blue-600 shadow-sm' 
                                    : 'border-gray-300 text-gray-700 hover:bg-gray-50 bg-white' }}">
                            Show All
                        </button>

                        <select wire:model.live="selectedLossType" 
                            class="text-xs border border-gray-300 rounded-lg px-3 py-2 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Damage Types</option>
                            <option value="Expired">🕐 Expired</option>
                            <option value="Broken">💔 Broken</option>
                            <option value="Spoiled">🗑️ Spoiled</option>
                            <option value="Damaged">⚠️ Damaged</option>
                            <option value="Defective">🔧 Defective</option>
                            <option value="Contaminated">☣️ Contaminated</option>
                            <option value="Crushed">📦 Crushed</option>
                            <option value="Leaking">💧 Leaking</option>
                            <option value="Torn">✂️ Torn</option>
                            <option value="Wet">🌊 Wet/Water Damaged</option>
                            <option value="Mold">🦠 Mold/Fungus</option>
                            <option value="Pest">🐛 Pest Damage</option>
                            <option value="Temperature">🌡️ Temperature Abuse</option>
                            <option value="Recalled">🚫 Recalled</option>
                            <option value="Missing Parts">🧩 Missing Parts/Incomplete</option>
                            <option value="Wrong Item">❌ Wrong Item Received</option>
                            <option value="Unsealed">📭 Unsealed/Opened</option>
                            <option value="Faded">🎨 Faded/Discolored</option>
                            <option value="Stolen">🔒 Stolen/Lost</option>
                        </select>
                        
                        <button 
                            wire:click="exportLossReport" 
                            class="border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hover:bg-slate-50 flex items-center justify-center p-1.5 gap-1.5"
                            @if(!$lossRep || $lossRep->isEmpty()) disabled @endif>
                            <span class="material-symbols-rounded">file_export</span>
                            <span class="text-xs">Export</span>
                        </button>
                    </div>  
                </div>
            </div>

            <!-- Table Container -->
            <div class="overflow-y-auto overflow-x-auto scrollbar-custom h-[36.3rem]">
                <table class="w-full text-sm {{ $lossRep->isNotEmpty() ? 'min-w-[95rem]' : 'w-full' }}">
                    <thead class="bg-gray-100 sticky top-0 z-10">
                        <tr class="sticky top-0 bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]">
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Date Reported
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Batch #
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Product Name
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-100">
                                Category
                            </th>
                            
                            <th class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Loss Type
                            </th>
                            
                            <th colspan="3" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-gray-50 border-l-2 border-gray-300">
                                Financial Impact
                            </th>
                            
                            <th colspan="2" class="px-4 py-2 text-center font-semibold text-gray-700 uppercase text-xs tracking-wider bg-blue-50 border-l-2 border-gray-300">
                                Additional Details
                            </th>
                        </tr>
                        <tr class="sticky bg-gray-100 shadow-[0_2px_0_0_rgb(209,213,219)]" style="top: 42px;">
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-600 bg-gray-100"></th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-gray-50">Type</th>
                            
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Quantity Lost</th>
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Unit Cost</th>
                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-600 bg-gray-50">Total Loss</th>
                            
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">Reported By</th>
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-600 bg-blue-50">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white text-xs">
                       
                        @forelse ($lossRep as $row)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-4 py-3.5 text-gray-700 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($row->date_reported)->format('M d, Y') }}
                                    <div class="text-[10px] text-gray-500">
                                        {{ \Carbon\Carbon::parse($row->date_reported)->format('h:i A') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-medium text-gray-900 whitespace-nowrap">
                                    {{ $row->batch_num }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-700 font-medium">
                                    {{ $row->prod_name }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-600">
                                    {{ $row->cat_name }}
                                </td>
                                
                                <!-- Loss Type -->
                                <td class="px-4 py-3.5 text-center bg-gray-50">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold
                                        @if(strtolower($row->type) === 'expired') 
                                            bg-red-100 text-red-700 border border-red-300
                                        @elseif(strtolower($row->type) === 'broken') 
                                            bg-orange-100 text-orange-700 border border-orange-300
                                        @elseif(strtolower($row->type) === 'spoiled') 
                                            bg-amber-100 text-amber-700 border border-amber-300
                                        @elseif(strtolower($row->type) === 'damaged') 
                                            bg-orange-100 text-orange-700 border border-orange-300
                                        @elseif(strtolower($row->type) === 'defective') 
                                            bg-yellow-100 text-yellow-700 border border-yellow-300
                                        @elseif(strtolower($row->type) === 'contaminated') 
                                            bg-red-100 text-red-700 border border-red-300
                                        @elseif(strtolower($row->type) === 'crushed') 
                                            bg-orange-100 text-orange-700 border border-orange-300
                                        @elseif(strtolower($row->type) === 'leaking') 
                                            bg-blue-100 text-blue-700 border border-blue-300
                                        @elseif(strtolower($row->type) === 'torn') 
                                            bg-amber-100 text-amber-700 border border-amber-300
                                        @elseif(strtolower($row->type) === 'wet') 
                                            bg-cyan-100 text-cyan-700 border border-cyan-300
                                        @elseif(strtolower($row->type) === 'mold') 
                                            bg-green-100 text-green-700 border border-green-300
                                        @elseif(strtolower($row->type) === 'pest') 
                                            bg-lime-100 text-lime-700 border border-lime-300
                                        @elseif(strtolower($row->type) === 'temperature') 
                                            bg-rose-100 text-rose-700 border border-rose-300
                                        @elseif(strtolower($row->type) === 'recalled') 
                                            bg-red-100 text-red-700 border border-red-300
                                        @elseif(strtolower($row->type) === 'missing parts') 
                                            bg-indigo-100 text-indigo-700 border border-indigo-300
                                        @elseif(strtolower($row->type) === 'wrong item') 
                                            bg-violet-100 text-violet-700 border border-violet-300
                                        @elseif(strtolower($row->type) === 'unsealed') 
                                            bg-pink-100 text-pink-700 border border-pink-300
                                        @elseif(strtolower($row->type) === 'faded') 
                                            bg-slate-100 text-slate-700 border border-slate-300
                                        @elseif(strtolower($row->type) === 'stolen') 
                                            bg-purple-100 text-purple-700 border border-purple-300
                                        @else 
                                            bg-gray-100 text-gray-700 border border-gray-300
                                        @endif">
                                        {{ ucfirst($row->type) }}
                                    </span>
                                </td>
                                
                                <!-- Financial Impact -->
                                <td class="px-4 py-3.5 text-right font-bold text-gray-900 bg-gray-50">
                                    {{ number_format($row->qty) }}
                                </td>
                                <td class="px-4 py-3.5 text-right text-gray-700 bg-gray-50">
                                    ₱{{ number_format($row->unit_cost, 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-bold text-red-600 bg-gray-50">
                                    ₱{{ number_format($row->total_loss, 2) }}
                                </td>
                                
                                <!-- Additional Details -->
                                <td class="px-4 py-3.5 text-center font-medium text-gray-700 bg-blue-50">
                                    @if(str_contains(strtolower($row->remarks ?? ''), 'system'))
                                        System
                                    @else
                                        {{ ucwords($row->reported_by ?? 'N/A') }}
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 text-center bg-blue-50">
                                    @if($row->remarks)
                                        <div class="max-w-xs truncate" title="{{ $row->remarks }}">
                                            {{ $row->remarks }}
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty 
                            <tr>
                                <td colspan="10" class="text-center py-16">
                                    <div class="flex flex-col justify-center items-center space-y-3">
                                        <span class="material-symbols-rounded text-6xl text-gray-300">check_circle</span>
                                        <div>
                                            <p class="text-gray-600 font-medium">No Loss Records Found</p>
                                            <p class="text-gray-400 text-sm mt-1">No damage or loss incidents for the selected period</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($lossRep->isNotEmpty())
                    <tfoot class="sticky bottom-0 z-10 bg-slate-100 shadow-[0_-1px_0_0_rgb(209,213,219)]">
                        <tr class="border-t-2 border-gray-600">
                            <td colspan="5" class="px-4 py-3 text-left font-bold uppercase text-xs tracking-wider">
                                Total Loss Summary
                            </td>
                            <td class="px-4 text-right font-bold text-xs">
                                {{ number_format($lossRep->sum('qty')) }} units
                            </td>
                            <td class="px-4 text-center text-xs text-gray-400">
                            </td>
                            <td class="px-4 text-right font-bold text-xs text-red-700">
                                ₱{{ number_format($lossRep->sum('total_loss'), 2) }}
                            </td>
                            <td colspan="2" class="px-4 text-center text-xs text-black">
                                {{ $lossRep->count() }} incident(s) reported
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>      


    </div>


























     <!-- Export Modal -->
    @if($showExportModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-full max-w-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Export Sales Report</h3>
            <p class="text-sm text-gray-600 mb-6">Choose your preferred export format:</p>
            
            <div class="space-y-3">
                <button wire:click="exportToCSV" 
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-lg font-medium flex items-center justify-center gap-2 transition">
                    <span class="material-symbols-rounded">table_chart</span>
                    Export to CSV (Excel Compatible)
                </button>
            </div>

            <button wire:click="toggleExportModal" 
                class="w-full mt-4 bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg font-medium transition">
                Cancel
            </button>
        </div>
    </div>
    @endif

<!-- Global Return History Modal -->
@if($showGlobalReturnHistory)
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-6xl mx-auto h-full max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white p-4 rounded-t-lg flex-shrink-0">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold">Global Return History</h3>
                    <p class="text-sm text-orange-100">
                        All returned items from {{ \Carbon\Carbon::parse($returnDateFrom)->format('M d, Y') }} 
                        to {{ \Carbon\Carbon::parse($returnDateTo)->format('M d, Y') }}
                    </p>
                </div>
                <button wire:click="closeGlobalReturnHistory" class="text-white hover:text-gray-200">
                    <span class="material-symbols-rounded text-2xl">close</span>
                </button>
            </div>
            
            <!-- Filters -->
            <div class="flex items-center gap-3 mt-4">
                <label class="text-sm font-medium text-orange-100">Date Range:</label>
                <input type="date" wire:model.live="returnDateFrom"
                    class="text-xs border border-orange-300 rounded-lg px-3 py-2 bg-white text-gray-900 shadow-sm focus:ring-2 focus:ring-orange-300"/>
                <input type="date" wire:model.live="returnDateTo"
                    class="text-xs border border-orange-300 rounded-lg px-3 py-2 bg-white text-gray-900 shadow-sm focus:ring-2 focus:ring-orange-300"/>
                
                <label class="text-sm font-medium text-orange-100 ml-4">Category:</label>
                <select wire:model.live="returnSelectedCategory"
                    class="text-xs border border-orange-300 rounded-lg px-4 py-2 bg-white text-gray-900 shadow-sm focus:ring-2 focus:ring-orange-300">
                    <option value="all">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->category_id }}">{{ $cat->category }}</option>
                    @endforeach
                </select>
                
                <button wire:click="resetReturnFilters" 
                    class="text-xs border border-orange-300 rounded-lg p-2 bg-white text-gray-900 shadow-sm hover:bg-orange-50 transition-colors"
                    title="Reset filters">
                    <span class="material-symbols-rounded text-lg">refresh</span>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto min-h-0 p-6">
            @if($globalReturnHistory->isEmpty())
                <div class="flex flex-col items-center justify-center py-16">
                    <span class="material-symbols-rounded text-6xl text-gray-300 mb-4">history</span>
                    <p class="text-gray-600 font-medium">No Returns Found</p>
                    <p class="text-sm text-gray-400 mt-1">No items were returned during this period</p>
                </div>
            @else
                <!-- Table View -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs">
                                    Return Date
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs">
                                    Receipt #
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs">
                                    Product
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs">
                                    Category
                                </th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs">
                                    Qty
                                </th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase text-xs">
                                    Refund
                                </th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs">
                                    Status
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs">
                                    Reason
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs">
                                    Processed By
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($globalReturnHistory as $return)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ \Carbon\Carbon::parse($return->return_date)->format('M d, Y') }}
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($return->return_date)->format('h:i A') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <button wire:click="viewReceipt({{ $return->receipt_id }})"
                                            class="font-bold text-blue-600 hover:text-blue-700 hover:underline">
                                            #{{ str_pad($return->receipt_id, 6, '0', STR_PAD_LEFT) }}
                                        </button>
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($return->receipt_date)->format('M d, Y') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        {{ $return->product_name }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $return->category }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-gray-900">
                                        {{ $return->return_quantity }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-blue-600">
                                        ₱{{ number_format($return->refund_amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($return->damaged_id)
                                            <span class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded-full font-medium">
                                                {{ $return->damaged_type }}
                                            </span>
                                        @else
                                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">
                                                Restocked
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 text-xs max-w-xs truncate" title="{{ $return->return_reason }}">
                                        {{ $return->return_reason }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 text-xs">
                                        {{ $return->return_staff_id ? trim($return->staff_fullname) : trim($return->owner_fullname) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="sticky bottom-0 bg-slate-100 border-t-2 border-gray-600">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-left font-bold uppercase text-xs">
                                    Total Summary
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-sm">
                                    {{ $globalReturnHistory->sum('return_quantity') }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-sm text-blue-600">
                                    ₱{{ number_format($globalReturnHistory->sum('refund_amount'), 2) }}
                                </td>
                                <td colspan="3" class="px-4 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-3 gap-4 mt-6">
                    <div class="bg-gradient-to-br from-blue-50 to-white rounded-lg p-4 border border-blue-200">
                        <p class="text-xs text-gray-600 font-medium mb-1">Total Returns</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $globalReturnHistory->count() }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-white rounded-lg p-4 border border-green-200">
                        <p class="text-xs text-gray-600 font-medium mb-1">Items Restocked</p>
                        <p class="text-2xl font-bold text-green-600">
                            {{ $globalReturnHistory->where('damaged_id', null)->sum('return_quantity') }}
                        </p>
                    </div>
                    <div class="bg-gradient-to-br from-red-50 to-white rounded-lg p-4 border border-red-200">
                        <p class="text-xs text-gray-600 font-medium mb-1">Damaged Items</p>
                        <p class="text-2xl font-bold text-red-600">
                            {{ $globalReturnHistory->whereNotNull('damaged_id')->count() }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- Original Receipt Modal --}}
@if($showOriginalReceiptModal && $originalReceiptDetails)
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="flex items-center justify-center gap-4 w-full max-w-6xl mx-auto">
        {{-- Original Receipt --}}
        <div class="bg-white rounded-lg w-full {{ $showReturnReceiptModal ? 'max-w-md' : 'max-w-md mx-auto' }} h-full max-h-[90vh] flex flex-col relative">
            <button wire:click="closeAllReceiptModals" 
                class="absolute top-4 right-4 z-10 text-white p-2 rounded-lg hover:bg-white/20 transition"
                title="Close Receipt">
                <span class="material-symbols-rounded text-xl">close</span>
            </button>

            <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-4 rounded-t-lg flex-shrink-0">
                <div class="text-center">
                    <h3 class="text-xl font-bold mb-2">Original Receipt</h3>
                    <p class="text-sm text-green-100">As Issued</p>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto min-h-0" style="max-height: calc(90vh - 120px);">
                <div class="p-6">
                    <div class="text-center mb-6 pb-4 border-b-2 border-gray-200">
                        <h2 class="text-xl font-bold text-gray-800">
                            {{ $originalReceiptDetails->owner->store_name ??  $store_info->store_name ??  'Store Name' }}
                        </h2>
                        <p class="text-sm text-gray-600">{{ $store_info->store_address }}</p>
                        @if(! empty($store_info->tin_number))
                            <p class="text-xs text-gray-500 mt-1">TIN: {{ $store_info->tin_number }}</p>
                        @endif
                    </div>

                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Receipt No. :</span>
                            <span class="text-sm font-bold text-gray-900">#{{ str_pad($originalReceiptDetails->receipt_id, 6, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Date & Time:</span>
                            <span class="text-sm text-gray-900">{{ $originalReceiptDetails->receipt_date->format('m/d/Y, h:i: s A') }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm font-medium text-gray-700">Cashier:</span>
                            <span class="text-sm text-gray-900">
                                {{ $originalReceiptDetails->staff ?  $originalReceiptDetails->staff->firstname : ($originalReceiptDetails->owner ?  $originalReceiptDetails->owner->firstname : 'N/A') }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-800 mb-3 pb-2 border-b border-gray-300">Items Purchased</h4>
                        <div class="space-y-2">
                            @foreach($originalReceiptDetails->receiptItems as $item)
                                <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1 pr-4">
                                            <div class="text-sm font-medium text-gray-900 mb-1">{{ $item->product->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                Quantity: {{ $item->item_quantity }} × ₱{{ number_format($item->product->selling_price, 2) }}
                                            </div>
                                            @if(($item->item_discount_amount ??  0) > 0)
                                                <div class="text-xs text-orange-600 mt-1 flex items-center gap-1">
                                                    Item Discount: 
                                                    <span class="font-bold">₱{{ number_format($item->item_discount_amount, 2) }}</span>
                                                    @if(($item->item_discount_type ?? 'percent') == 'percent' && ($item->item_discount_value ??  0) > 0)
                                                        <span class="text-gray-500">({{ $item->item_discount_value }}%)</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-bold text-gray-900">
                                                ₱{{ number_format(($item->product->selling_price * $item->item_quantity), 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t-2 border-gray-300 pt-4 space-y-2">
                        <div class="flex justify-between items-center bg-blue-50 -mx-2 px-4 py-2 rounded-lg">
                            <span class="text-sm font-bold text-gray-900">Total Quantity:</span>
                            <span class="text-sm font-bold text-blue-600">
                                {{ $originalReceiptDetails->receiptItems->sum('item_quantity') }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                            <span class="text-sm font-bold text-gray-900">
                                ₱{{ number_format($originalReceiptDetails->computed_subtotal ??  0, 2) }}
                            </span>
                        </div>

                        @if(($originalReceiptDetails->total_item_discounts ?? 0) > 0)
                            <div class="flex justify-between items-center bg-blue-50 -mx-2 px-4 py-2 rounded-lg">
                                <span class="text-sm font-medium text-blue-700 flex items-center gap-1">
                                    Item Discounts:
                                </span>
                                <span class="text-sm font-bold text-blue-700">
                                    ₱{{ number_format($originalReceiptDetails->total_item_discounts, 2) }}
                                </span>
                            </div>
                        @endif

                        @if(($originalReceiptDetails->receipt_discount_amount ?? 0) > 0)
                            <div class="flex justify-between items-center bg-orange-50 -mx-2 px-4 py-2 rounded-lg">
                                <span class="text-sm font-medium text-orange-700 flex items-center gap-1">
                                    Receipt Discount:
                                </span>
                                <span class="text-sm font-bold text-orange-700">
                                    ₱{{ number_format($originalReceiptDetails->receipt_discount_amount, 2) }}
                                    @if(($originalReceiptDetails->discount_type ?? '') == 'percent' && ($originalReceiptDetails->discount_value ??  0) > 0)
                                        <span class="text-xs text-gray-600">({{ $originalReceiptDetails->discount_value }}%)</span>
                                    @endif
                                </span>
                            </div>
                        @endif

                        <div class="flex justify-between items-center pt-2 border-t">
                            <span class="text-lg font-bold text-gray-900">Total Amount:</span>
                            <span class="text-lg font-bold text-blue-600">
                                ₱{{ number_format($originalReceiptDetails->computed_total ??  0, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Amount Paid:</span>
                            <span class="text-sm text-gray-900">
                                ₱{{ number_format($originalReceiptDetails->amount_paid ??  0, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Change:</span>
                            <span class="text-sm font-bold text-green-600">
                                ₱{{ number_format($originalReceiptDetails->computed_change ?? 0, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Return Receipt - Only shown when there are returns --}}
        @if($showReturnReceiptModal && $returnReceiptDetails)
        <div class="bg-white rounded-lg w-full max-w-md h-full max-h-[90vh] flex flex-col relative">
            <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white p-4 rounded-t-lg flex-shrink-0">
                <div class="text-center">
                    <h3 class="text-xl font-bold mb-2">Return Receipt</h3>
                    <p class="text-sm text-orange-100">After Returns</p>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto min-h-0" style="max-height:  calc(90vh - 120px);">
                <div class="p-6">
                    <div class="text-center mb-6 pb-4 border-b-2 border-gray-200">
                        <h2 class="text-xl font-bold text-gray-800">
                            {{ $returnReceiptDetails->owner->store_name ?? $store_info->store_name ??  'Store Name' }}
                        </h2>
                        <p class="text-sm text-gray-600">{{ $store_info->store_address }}</p>
                        @if(! empty($store_info->tin_number))
                            <p class="text-xs text-gray-500 mt-1">TIN: {{ $store_info->tin_number }}</p>
                        @endif
                        <div class="mt-2 inline-flex items-center gap-1 bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-xs font-semibold">
                            <span class="material-symbols-rounded text-sm">undo</span>
                            Adjusted for Returns
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Receipt No. :</span>
                            <span class="text-sm font-bold text-gray-900">#{{ str_pad($returnReceiptDetails->receipt_id, 6, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Date & Time:</span>
                            <span class="text-sm text-gray-900">{{ $returnReceiptDetails->receipt_date->format('m/d/Y, h:i:s A') }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm font-medium text-gray-700">Cashier:</span>
                            <span class="text-sm text-gray-900">
                                {{ $returnReceiptDetails->staff ? $returnReceiptDetails->staff->firstname : ($returnReceiptDetails->owner ? $returnReceiptDetails->owner->firstname : 'N/A') }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-800 mb-3 pb-2 border-b border-gray-300">Remaining Items</h4>
                        <div class="space-y-2">
                            @foreach($returnReceiptDetails->receiptItems as $item)
                                <div class="border rounded-lg p-3 {{ $item->returned_quantity > 0 ? 'border-orange-300 bg-orange-50' : 'border-gray-200 bg-gray-50' }}">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1 pr-4">
                                            <div class="text-sm font-medium text-gray-900 mb-1">{{ $item->product->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                Remaining: {{ $item->item_quantity }} × ₱{{ number_format($item->product->selling_price, 2) }}
                                            </div>
                                            @if($item->returned_quantity > 0)
                                                <div class="text-xs text-orange-700 mt-1 font-semibold flex items-center gap-1">
                                                    <span class="material-symbols-rounded text-xs">undo</span>
                                                    Returned: {{ $item->returned_quantity }} (Original: {{ $item->original_quantity }})
                                                </div>
                                                @if($item->replacement_products)
                                                    <div class="text-xs text-blue-700 mt-1 font-semibold flex items-center gap-1">
                                                        <span class="material-symbols-rounded text-xs">swap_horiz</span>
                                                        Replaced with: {{ $item->replacement_products }}
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-bold text-gray-900">
                                                ₱{{ number_format(($item->product->selling_price * $item->item_quantity), 2) }}
                                            </div>
                                            @if($item->returned_quantity > 0)
                                                <div class="text-xs text-gray-500 line-through">
                                                    ₱{{ number_format(($item->product->selling_price * $item->original_quantity), 2) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t-2 border-gray-300 pt-4 space-y-2">
                        <div class="flex justify-between items-center bg-blue-50 -mx-2 px-4 py-2 rounded-lg">
                            <span class="text-sm font-bold text-gray-900">Remaining Quantity:</span>
                            <span class="text-sm font-bold text-blue-600">
                                {{ $returnReceiptDetails->receiptItems->sum('item_quantity') }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                            <span class="text-sm font-bold text-gray-900">
                                ₱{{ number_format($returnReceiptDetails->computed_subtotal ??  0, 2) }}
                            </span>
                        </div>

                        @if(($returnReceiptDetails->total_returned ??  0) > 0)
                            <div class="flex justify-between items-center bg-red-50 -mx-2 px-4 py-2 rounded-lg">
                                <span class="text-sm font-medium text-red-700 flex items-center gap-1">
                                    <span class="material-symbols-rounded text-sm">remove_circle</span>
                                    Returned Amount: 
                                </span>
                                <span class="text-sm font-bold text-red-700">
                                    -₱{{ number_format($returnReceiptDetails->total_returned, 2) }}
                                </span>
                            </div>
                        @endif

                        <div class="flex justify-between items-center pt-2 border-t">
                            <span class="text-lg font-bold text-gray-900">Adjusted Total:</span>
                            <span class="text-lg font-bold text-blue-600">
                                ₱{{ number_format($returnReceiptDetails->computed_total ?? 0, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Adjusted Amount Paid:</span>
                            <span class="text-sm text-gray-900">
                                ₱{{ number_format($returnReceiptDetails->amount_paid ?? 0, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Adjusted Change:</span>
                            <span class="text-sm font-bold text-green-600">
                                ₱{{ number_format($returnReceiptDetails->computed_change ?? 0, 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 bg-orange-50 border border-orange-200 rounded-lg p-3">
                        <p class="text-xs text-orange-800 flex items-start gap-2">
                            <span class="material-symbols-rounded text-sm">info</span>
                            <span>This receipt shows adjusted values after processing returns.  Original receipt values remain unchanged in the system.</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif

</div>

</div>