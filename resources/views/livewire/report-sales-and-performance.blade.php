<div x-data="{ tab: 'sales' }" class="w-full px-4 {{ ($expired || $plan === 3 || $plan === 1) ? 'blur-sm pointer-events-none select-none' : '' }}">

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
    </div>

    <div class="border bg-white p-4 rounded-b-lg mb-3 h-[41rem]"
        :class="{
            'border-green-500 bg-green-50': tab === 'sales',
            'border-orange-500 bg-orange-50': tab === 'sales-category',
            'border-purple-500 bg-purple-50': tab === 'product-performance'
        }">

        <!-- DAILY/MONTHLY SALES TAB -->
        <div x-show="tab === 'sales'" class="h-full flex flex-col">
            <!-- Sales Analytics Cards -->
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="bg-white rounded-lg p-3 border border-green-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Total Transactions</p>
                            <p class="text-xl font-bold text-gray-900">{{ $salesAnalytics->total_transactions ?? 0 }}</p>
                        </div>
                        <span class="material-symbols-rounded text-green-600 text-3xl">receipt_long</span>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-3 border border-green-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Gross Sales</p>
                            <p class="text-xl font-bold text-green-600">₱{{ number_format($salesAnalytics->gross_sales ?? 0, 2) }}</p>
                        </div>
                        <span class="material-symbols-rounded text-green-600 text-3xl">trending_up</span>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-3 border border-green-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Net Profit</p>
                            <p class="text-xl font-bold text-blue-600">₱{{ number_format($salesAnalytics->net_profit ?? 0, 2) }}</p>
                        </div>
                        <span class="material-symbols-rounded text-blue-600 text-3xl">payments</span>
                    </div>
                </div>
            </div>

            <!-- Quick Date Range Filters -->
            <div x-data="{ showQuickDates: false }" class="flex justify-between items-center mb-4 gap-3">
                <!-- Left side: Analytics -->
                <div class="flex gap-2 text-xs">
                    <div class="bg-white px-3 py-2 rounded border border-gray-300">
                        <span class="text-gray-500">Items Sold:</span>
                        <span class="font-bold text-gray-900 ml-1">{{ $salesAnalytics->total_items_sold ?? 0 }}</span>
                    </div>
                    <div class="bg-white px-3 py-2 rounded border border-gray-300">
                        <span class="text-gray-500">Avg. Transaction:</span>
                        <span class="font-bold text-gray-900 ml-1">₱{{ number_format($salesAnalytics->avg_transaction_value ?? 0, 2) }}</span>
                    </div>
                </div>
            
                <!-- Right side: Actions -->
                <div class="flex items-center gap-2">
                    <!-- Quick Date Range -->
                    <div class="relative">
                        <button @click="showQuickDates = !showQuickDates" 
                            class="bg-white px-3 py-2 rounded border border-gray-300 hover:bg-gray-50 transition text-xs flex items-center gap-1">
                            <span class="material-symbols-rounded text-sm">schedule</span>
                            Quick Range
                        </button>
                        <div x-show="showQuickDates" @click.away="showQuickDates = false" x-cloak
                            class="absolute right-0 mt-2 w-48 bg-white border border-gray-300 rounded-lg shadow-lg z-50 py-2">
                            <button wire:click="setQuickDateRange('7days')" 
                                class="w-full text-left px-4 py-2 text-xs hover:bg-gray-100 transition">
                                Last 7 Days
                            </button>
                            <button wire:click="setQuickDateRange('30days')" 
                                class="w-full text-left px-4 py-2 text-xs hover:bg-gray-100 transition">
                                Last 30 Days
                            </button>
                            <button wire:click="setQuickDateRange('3months')" 
                                class="w-full text-left px-4 py-2 text-xs hover:bg-gray-100 transition">
                                Last 3 Months
                            </button>
                            <div class="border-t border-gray-200 my-1"></div>
                            <button wire:click="setQuickDateRange('thismonth')" 
                                class="w-full text-left px-4 py-2 text-xs hover:bg-gray-100 transition">
                                This Month
                            </button>
                            <button wire:click="setQuickDateRange('thisyear')" 
                                class="w-full text-left px-4 py-2 text-xs hover:bg-gray-100 transition">
                                This Year
                            </button>
                            <button wire:click="setQuickDateRange('lastyear')" 
                                class="w-full text-left px-4 py-2 text-xs hover:bg-gray-100 transition">
                                Last Year
                            </button>
                        </div>
                    </div>

                    <!-- Date Range Filter --> 
                    <div class="flex items-center gap-2 bg-white px-3 py-2 rounded border border-gray-300">
                        <input type="date" wire:model.live="dateFrom"
                            class="text-xs border-0 focus:ring-0 p-0 text-gray-700"/>
                        <span class="text-green-600">to</span>
                        <input type="date" wire:model.live="dateTo"
                            class="text-xs border-0 focus:ring-0 p-0 text-gray-700"/>
                        <button wire:click="resetDateFilters" 
                            class="text-gray-400 hover:text-gray-600"
                            title="Reset dates">
                            <span class="material-symbols-rounded text-lg">refresh</span>
                        </button>
                    </div>

                    <!-- Export Button -->
                    <button wire:click="toggleExportModal" title="Export Report" 
                        class="bg-green-600 hover:bg-green-700 text-white p-2 rounded transition">
                        <span class="material-symbols-rounded text-xl">description</span>
                    </button>

                    <!-- Start Transaction Button -->
                    <a href="{{ route('store_start_transaction') }}" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-medium text-xs flex items-center gap-1 transition">
                        <span class="material-symbols-rounded text-lg">add_shopping_cart</span>
                        Start Transaction
                    </a>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="flex-1 overflow-y-auto scrollbar-custom bg-white rounded border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Receipt No.
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Date & Time
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Total Quantity
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Total Amount
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Amount Paid
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Change
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                View Receipt
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-xs font-medium text-gray-900">
                                    #{{ str_pad($transaction->receipt_id, 6, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700">
                                    {{ \Carbon\Carbon::parse($transaction->receipt_date)->format('m/d/Y, h:i A') }}
                                </td>
                                <td class="px-4 py-3 text-xs text-center font-medium text-gray-900">
                                    {{ $transaction->total_quantity }}
                                </td>
                                <td class="px-4 py-3 text-xs text-right font-bold text-green-600">
                                    ₱{{ number_format($transaction->total_amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-xs text-right text-gray-700">
                                    ₱{{ number_format($transaction->amount_paid, 2) }}
                                </td>
                                <td class="px-4 py-3 text-xs text-right font-medium text-blue-600">
                                    ₱{{ number_format($transaction->change, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="viewReceipt({{ $transaction->receipt_id }})"
                                        class="text-green-600 hover:text-green-800 transition"
                                        title="View Receipt">
                                        <span class="material-symbols-rounded text-xl">visibility</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8">
                                    <div class="flex flex-col items-center space-y-2">
                                        <span class="material-symbols-rounded text-gray-400 text-5xl">receipt_long</span>
                                        <span class="text-gray-400 text-sm">No transactions found</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SALES BY CATEGORY TAB -->
        <div x-show="tab === 'sales-category'" class="h-full flex flex-col">
            <div x-data="{ open: false }" class="flex items-center mb-4 space-x-2 relative justify-between">
                
                <div class="relative flex items-center text-gray-300">
                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded">search</span>
                    </span>
                    <input 
                        type="text"
                        wire:model.live.debounce.300ms="searchWord"
                        placeholder="Search Category..."
                        class="rounded border border-gray-300 pl-10 pr-3 py-2 text-xs focus:ring focus:ring-orange-200 text-black"
                    >
                </div>

                <div class="relative">
                    <button @click="open = !open" type="button" class="py-2 px-3 border border-orange-500 rounded hover:bg-orange-50">
                        <div class="flex justify-center gap-1">
                            <span class="material-symbols-rounded text-orange-700" title="Filter">tune</span>
                            <span class="text-orange-700 text-xs font-semibold">Filter</span>
                        </div>
                    </button>
                    
                    <div x-show="open" x-cloak @click.away="open = false" 
                        class="absolute top-full right-0 mt-2 w-64 bg-white border border-orange-200 rounded-xl shadow-lg z-50 p-4 space-y-4">

                        <div>
                            <span class="text-[11px] font-semibold text-orange-700">Year:</span>
                            <div class="grid grid-cols-3 gap-2 mt-2">
                                @foreach($years as $yr)
                                    <label class="flex items-center justify-center cursor-pointer">
                                        <input type="radio" name="selectedYear" value="{{ $yr->year }}" wire:model.live="selectedYearSingle" class="hidden peer">
                                        <span class="peer-checked:bg-orange-600 peer-checked:text-white 
                                                    text-orange-600 bg-orange-100 hover:bg-orange-200 
                                                    rounded-full py-1 px-2 text-center text-[11px] transition">
                                            {{ $yr->year }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        

                        <div class="flex gap-2 mt-3">
                            <button wire:click="salesByCategory" 
                                class="flex-1 bg-orange-700 hover:bg-orange-800 text-white text-[11px] py-2 rounded-lg transition">
                                Proceed
                            </button>
                            <button wire:click="resetFilters" 
                                class="flex-1 bg-orange-100 hover:bg-orange-200 text-orange-700 text-[11px] py-2 rounded-lg transition">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto scrollbar-custom">
                <table x-data="{ showTopProductUnit: false, showTopProductSales: false }" class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50">
                        <tr class="text-gray-700 uppercase text-xs tracking-wider border-b">
                            <th class="px-2 py-3 text-left font-semibold text-xs sticky top-0 bg-gray-50 w-[15%]">Category</th>
                            <th class="px-2 py-3 text-center font-semibold sticky top-0 bg-gray-50 w-[8%]">Units Sold</th>
                            <th class="px-2 py-3 text-center font-semibold sticky top-0 bg-gray-50 w-[8%]" 
                                x-show="showTopProductUnit" x-cloak>
                                Top Product (Unit)
                            </th>
                            <th class="px-2 py-3 text-center font-semibold sticky top-0 bg-gray-50 w-[8%]">Stock Left</th>
                            <th class="px-2 py-3 text-right font-semibold sticky top-0 bg-gray-50 w-[10%]">Sales (₱)</th>
                            <th class="px-2 py-3 text-center font-semibold sticky top-0 bg-gray-50 w-[8%]" 
                                x-show="showTopProductSales" x-cloak>
                                Top Product (Sales)
                            </th>
                            <th class="px-2 py-3 text-right font-semibold sticky top-0 bg-gray-50 w-[10%]">COGS (₱)</th>
                            <th class="px-2 py-3 text-center font-semibold sticky top-0 bg-gray-50 w-[8%]">Margin %</th>
                            <th class="px-2 py-3 text-left font-semibold sticky top-0 bg-gray-50">Insight</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 text-xs">
                        @forelse($sbc as $input)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-2 text-gray-900 font-medium">{{ $input->category }}</td>
                                <td class="py-3 px-2 text-center text-gray-900">{{ $input->unit_sold }}</td>
                                <td class="py-3 px-2 text-center text-gray-600 text-[10px]" x-show="showTopProductUnit" x-cloak>
                                    {{ $input->top_product_unit }}
                                </td>
                                <td class="py-3 px-2 text-center text-gray-900">{{ $input->stock_left }}</td>
                                <td class="py-3 px-2 text-right text-green-600 font-semibold">₱{{ number_format($input->total_sales, 2) }}</td>
                                <td class="py-3 px-2 text-center text-gray-600 text-[10px]" x-show="showTopProductSales" x-cloak>
                                    {{ $input->top_product_sales }}
                                </td>
                                <td class="py-3 px-2 text-right text-gray-900">₱{{ number_format($input->cogs, 2) }}</td>
                                <td class="py-3 px-2 text-center font-semibold
                                    @if($input->gross_margin >= 30) text-green-600
                                    @elseif($input->gross_margin >= 20) text-blue-600
                                    @elseif($input->gross_margin >= 10) text-yellow-600
                                    @else text-red-600
                                    @endif">
                                    {{ number_format($input->gross_margin, 1) }}%
                                </td>
                                <td class="py-3 px-2 text-center font-medium text-[10px] text-white rounded
                                    @if (strpos($input->insight, 'URGENT') !== false) bg-red-700
                                    @elseif (strpos($input->insight, 'critically low') !== false) bg-red-600
                                    @elseif (strpos($input->insight, 'Out of stock') !== false) bg-gray-600
                                    @elseif (strpos($input->insight, 'Low stock') !== false) bg-orange-600
                                    @elseif (strpos($input->insight, 'Star performer') !== false) bg-purple-600
                                    @elseif (strpos($input->insight, 'Good sales velocity') !== false) bg-blue-600
                                    @elseif (strpos($input->insight, 'Fast-moving but low margins') !== false) bg-yellow-600
                                    @elseif (strpos($input->insight, 'Slow-moving with poor margins') !== false) bg-red-400
                                    @elseif (strpos($input->insight, 'Slow-moving') !== false) bg-amber-500
                                    @elseif (strpos($input->insight, 'No recent sales') !== false) bg-red-500
                                    @elseif (strpos($input->insight, 'Low profit margin') !== false) bg-amber-600
                                    @elseif (strpos($input->insight, 'Strong profit margins') !== false) bg-green-600
                                    @elseif (strpos($input->insight, 'Steady sales') !== false) bg-blue-500
                                    @elseif (strpos($input->insight, 'Stable') !== false) bg-slate-500
                                    @else bg-slate-600
                                    @endif
                                ">
                                    {{ $input->insight }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-8">
                                    <div class="flex flex-col justify-center items-center space-y-2">
                                        <span class="material-symbols-rounded text-gray-400 text-5xl">category</span>
                                        <span class="text-gray-500">No category data available</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PRODUCT PERFORMANCE TAB -->
        <div x-show="tab === 'product-performance'" class="h-full flex flex-col">
            <!-- Filters -->
            <div class="flex justify-between items-center mb-4 gap-3">
                <div class="flex gap-2">
                    <!-- Category Filter -->
                    <select wire:model.live="selectedCategory" 
                        class="text-xs border border-purple-300 rounded px-3 py-2 focus:ring focus:ring-purple-200">
                        <option value="all">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->category_id }}">{{ $cat->category }}</option>
                        @endforeach
                    </select>

                    <!-- Month Filter -->
                    <select wire:model.live="selectedMonth" 
                        class="text-xs border border-purple-300 rounded px-3 py-2 focus:ring focus:ring-purple-200">
                        @foreach($monthNames as $index => $name)
                            <option value="{{ $index + 1 }}">{{ $name }}</option>
                        @endforeach
                    </select>

                    <!-- Year Filter -->
                    <select wire:model.live="selectedYear" 
                        class="text-xs border border-purple-300 rounded px-3 py-2 focus:ring focus:ring-purple-200">
                        @foreach($years as $yr)
                            <option value="{{ $yr->year }}">{{ $yr->year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="text-xs text-gray-600">
                    <span class="font-semibold">Total Products:</span> {{ count($perf ?? []) }}
                </div>
            </div>

            <!-- Products Table -->
            <div class="flex-1 overflow-y-auto scrollbar-custom">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="uppercase bg-gray-50 sticky top-0">
                        <tr class="text-gray-700 uppercase tracking-wider">
                            <th class="px-3 py-3 text-left font-semibold w-[10rem]">
                                <button wire:click="sortBy('product_name')" class="uppercase flex items-center gap-1 hover:text-purple-600">
                                    Product Name
                                    @if($sortField === 'product_name')
                                        <span class="material-symbols-rounded text-sm">
                                            {{ $order === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                        </span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-3 text-left font-semibold">Category</th>
                            <th class="px-3 py-3 text-center font-semibold">
                                <button wire:click="sortBy('unit_sold')" class="uppercase flex items-center gap-1 hover:text-purple-600">
                                    Units Sold
                                    @if($sortField === 'unit_sold')
                                        <span class="material-symbols-rounded text-sm">
                                            {{ $order === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                        </span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-3 text-right font-semibold">
                                <button wire:click="sortBy('total_sales')" class="uppercase flex items-center gap-1 hover:text-purple-600">
                                    Total Sales (₱)
                                    @if($sortField === 'total_sales')
                                        <span class="material-symbols-rounded text-sm">
                                            {{ $order === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                        </span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-3 text-right font-semibold">COGS (₱)</th>
                            <th class="px-3 py-3 text-right font-semibold">
                                <button wire:click="sortBy('profit')" class="uppercase flex items-center gap-1 hover:text-purple-600">
                                    Profit (₱)
                                    @if($sortField === 'profit')
                                        <span class="material-symbols-rounded text-sm">
                                            {{ $order === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                        </span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-3 text-center font-semibold">
                                <button wire:click="sortBy('profit_margin_percent')" class="uppercase flex items-center gap-1 hover:text-purple-600">
                                    Margin %
                                    @if($sortField === 'profit_margin_percent')
                                        <span class="material-symbols-rounded text-sm">
                                            {{ $order === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                        </span>
                                    @endif
                                </button>
                            </th>
                            <th class="uppercase px-3 py-3 text-center font-semibold">Stock</th>
                            <th class="uppercase px-3 py-3 text-left font-semibold">Insight</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($perf ?? [] as $product)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-3 py-3 text-gray-900 font-medium">{{ $product->product_name }}</td>
                                <td class="px-3 py-3 text-gray-600">{{ $product->category ?? 'N/A' }}</td>
                                <td class="px-3 py-3 text-center text-gray-900">{{ $product->unit_sold }}</td>
                                <td class="px-3 py-3 text-right text-green-600 font-semibold">
                                    ₱{{ number_format($product->total_sales, 2) }}
                                </td>
                                <td class="px-3 py-3 text-right text-gray-900">
                                    ₱{{ number_format($product->cogs, 2) }}
                                </td>
                                <td class="px-3 py-3 text-right font-semibold
                                    @if($product->profit > 0) text-green-600
                                    @elseif($product->profit < 0) text-red-600
                                    @else text-gray-600
                                    @endif">
                                    ₱{{ number_format($product->profit, 2) }}
                                </td>
                                <td class="px-3 py-3 text-center font-semibold
                                    @if($product->profit_margin_percent >= 30) text-green-600
                                    @elseif($product->profit_margin_percent >= 20) text-blue-600
                                    @elseif($product->profit_margin_percent >= 10) text-yellow-600
                                    @else text-red-600
                                    @endif">
                                    {{ number_format($product->profit_margin_percent, 1) }}%
                                </td>
                                <td class="px-3 py-3 text-center
                                    @if($product->remaining_stocks == 0) text-red-600 font-bold
                                    @elseif($product->remaining_stocks < 10) text-orange-600 font-semibold
                                    @else text-gray-900
                                    @endif">
                                    {{ $product->remaining_stocks }}
                                </td>
                                <td class="px-3 py-3 text-center font-medium text-[10px] text-white rounded
                                    @if (strpos($product->insight, 'Out of stock') !== false) bg-red-600
                                    @elseif (strpos($product->insight, 'Low stock') !== false) bg-orange-600
                                    @elseif (strpos($product->insight, 'No sales') !== false) bg-gray-600
                                    @elseif (strpos($product->insight, 'Unprofitable') !== false) bg-red-700
                                    @elseif (strpos($product->insight, 'Low margin') !== false) bg-yellow-600
                                    @elseif (strpos($product->insight, 'Performing well') !== false) bg-green-600
                                    @elseif (strpos($product->insight, 'Good margin') !== false) bg-blue-600
                                    @elseif (strpos($product->insight, 'Moderate') !== false) bg-blue-500
                                    @else bg-slate-600
                                    @endif">
                                    {{ $product->insight }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-8">
                                    <div class="flex flex-col items-center space-y-2">
                                        <span class="material-symbols-rounded text-gray-400 text-5xl">inventory_2</span>
                                        <span class="text-gray-500">No product data available</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
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

    <!-- Receipt Display Modal -->
    @if($showReceiptModal && $receiptDetails)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg w-full max-w-md mx-auto h-full max-h-[90vh] flex flex-col">
            <!-- Receipt Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-4 rounded-t-lg flex-shrink-0">
                <div class="text-center">
                    <h3 class="text-xl font-bold mb-2">Receipt Details</h3>
                    <p class="text-sm text-red-100">Transaction Record</p>
                </div>
            </div>

            <!-- Receipt Content - Scrollable -->
            <div class="flex-1 overflow-y-auto min-h-0" style="max-height: calc(90vh - 120px);">
                <div class="p-6">
                    <!-- Store Info -->
                    <div class="text-center mb-6 pb-4 border-b-2 border-gray-200">
                        <h2 class="text-xl font-bold text-gray-800">
                            {{ $receiptDetails->owner->store_name ?? $store_info->store_name ?? 'Store Name' }}
                        </h2>
                    </div>

                    <!-- Transaction Details -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Receipt No.:</span>
                            <span class="text-sm font-bold text-gray-900">#{{ str_pad($receiptDetails->receipt_id, 6, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Date & Time:</span>
                            <span class="text-sm text-gray-900">{{ $receiptDetails->receipt_date->format('m/d/Y, h:i:s A') }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm font-medium text-gray-700">Cashier:</span>
                            <span class="text-sm text-gray-900">
                                {{ $receiptDetails->staff ? $receiptDetails->staff->firstname : ($receiptDetails->owner ? $receiptDetails->owner->firstname : 'N/A') }}
                            </span>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-800 mb-3 pb-2 border-b border-gray-300">Items Purchased</h4>
                        <div class="space-y-2">
                            @foreach($receiptDetails->receiptItems as $item)
                                <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                    <div class="flex-1 pr-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $item->item_quantity }} × ₱{{ number_format($item->product->selling_price, 2) }}
                                        </div>
                                        @if(($item->item_discount_value ?? 0) > 0)
                                            <div class="text-xs text-orange-600">
                                                Discount: 
                                                @if(($item->item_discount_type ?? 'percent') == 'percent')
                                                    {{ $item->item_discount_value }}%
                                                @else
                                                    ₱{{ number_format($item->item_discount_value, 2) }}
                                                @endif
                                            </div>
                                        @endif
                                        @if(($item->vat_amount ?? 0) > 0)
                                            <div class="text-xs text-green-600">
                                                VAT: ₱{{ number_format($item->vat_amount, 2) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-sm font-bold text-gray-900">
                                        ₱{{ number_format(($item->product->selling_price * $item->item_quantity), 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="border-t-2 border-gray-300 pt-4 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                            <span class="text-sm font-bold text-gray-900">
                                ₱{{ number_format($receiptDetails->computed_subtotal ?? 0, 2) }}
                            </span>
                        </div>

                        @if(($receiptDetails->total_item_discounts ?? 0) > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-orange-600">Item Discounts:</span>
                            <span class="text-sm font-bold text-orange-600">
                                -₱{{ number_format($receiptDetails->total_item_discounts, 2) }}
                            </span>
                        </div>
                        @endif

                        @if(($receiptDetails->receipt_discount_amount ?? 0) > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-orange-600">Receipt Discount:</span>
                            <span class="text-sm font-bold text-orange-600">
                                @if(($receiptDetails->discount_type ?? '') == 'percent')
                                    -{{ $receiptDetails->discount_value ?? 0 }}% (₱{{ number_format($receiptDetails->receipt_discount_amount, 2) }})
                                @else
                                    -₱{{ number_format($receiptDetails->receipt_discount_amount, 2) }}
                                @endif
                            </span>
                        </div>
                        @endif

                        @if(($receiptDetails->vat_amount ?? 0) > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-green-700">VAT:</span>
                            <span class="text-sm font-bold text-green-600">
                                +₱{{ number_format($receiptDetails->vat_amount, 2) }}
                            </span>
                        </div>
                        @endif

                        <div class="flex justify-between items-center pt-2 border-t">
                            <span class="text-lg font-bold text-gray-900">Total Amount:</span>
                            <span class="text-lg font-bold text-red-600">
                                ₱{{ number_format($receiptDetails->computed_total ?? 0, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Amount Paid:</span>
                            <span class="text-sm text-gray-900">
                                ₱{{ number_format($receiptDetails->amount_paid ?? 0, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Change:</span>
                            <span class="text-sm font-bold text-green-600">
                                ₱{{ number_format($receiptDetails->computed_change ?? 0, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Button - Fixed at bottom -->
            <div class="p-4 border-t bg-gray-50 flex gap-3 rounded-b-lg flex-shrink-0">
                <button wire:click="closeReceiptModal" 
                    class="flex-1 bg-red-600 text-white py-3 px-4 rounded-lg font-bold hover:bg-red-700 transition-colors">
                    <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Return
                </button>
            </div>
        </div>
    </div>
    @endif

</div>