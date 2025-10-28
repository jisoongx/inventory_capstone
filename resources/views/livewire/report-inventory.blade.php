<div x-data="{ tab: 'stock' }" class="w-full px-4">

    <div class="flex space-x-1">
        <button 
            @click="tab = 'stock'"
            :class="tab === 'stock' 
                ? 'bg-red-50 text-black border-red-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Inventory Stock
        </button>

        <button 
            @click="tab = 'expiring'"
            :class="tab === 'expiring' 
                ? 'bg-blue-50 text-black border-blue-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Expiring Poducts
        </button>        

        <button 
            @click="tab = 'top-selling'"
            :class="tab === 'top-selling' 
                ? 'bg-green-50 text-black border-green-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Top Selling Product
        </button>

        <button 
            @click="tab = 'loss'"
            :class="tab === 'loss' 
                ? 'bg-gray-50 text-black border-gray-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Loss Report
        </button>
        
    </div>

    <div class="border bg-white p-4 rounded-b-lg h-[41rem]"
        :class="{
            'border-blue-500 bg-blue-50': tab === 'expiring',
            'border-red-500 bg-red-50': tab === 'stock',
            'border-green-500 bg-green-50': tab === 'top-selling',
            'border-gray-900 bg-gray-50': tab === 'loss'
        }">

        <!-- TOP SELLING -->
        <div x-show="tab === 'top-selling'">
            <p class="text-gray-700">⚡ <b>top selling</b> report content goes here.</p>
        </div>

         <div x-show="tab === 'stock'">
            <div class="overflow-y-auto overflow-x-auto scrollbar-custom h-[39rem]">
                    <table class="w-full text-xs text-left shadow-sm 
                        {{ $stock->isNotEmpty() ? 'w-[116rem]' : 'w-full' }}">
                    <thead class="uppercase text-xs font-semibold bg-gray-200 text-gray-600">
                        <tr class="border-b-2 border-gray-300">
                            <th class="p-3 text-left bg-gray-100">Product Name</th>
                            <th class="p-3 bg-gray-100">Category</th>
                            <th class="p-3 bg-gray-100 text-center">Alert Status</th>
                            <th class="p-3 bg-gray-100 text-right">Current Stock</th>
                            <th class="p-3 bg-gray-100 text-right">Stock Limit</th>
                            <th class="p-3 bg-gray-100 text-right">Avg Daily Sales</th>
                            <th class="p-3 bg-gray-100 text-right">Suggested Reorder</th>
                            <th class="p-3 bg-gray-100 text-right">Last Restocked</th>
                            <th class="p-3 bg-gray-100 text-center">Action Recommended</th>
                        </tr>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($stock as $row)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-3 font-medium text-gray-900">{{ $row->prod_name }}</td>
                                <td class="p-3 text-gray-700">{{ $row->cat_name }}</td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-1 rounded text-[10px] font-semibold
                                        @if($row->alert_status === 'Out of Stock') bg-red-600 text-white
                                        @elseif($row->alert_status === 'Critical Low') bg-orange-600 text-white
                                        @elseif($row->alert_status === 'Expiring Soon') bg-yellow-500 text-black
                                        @elseif($row->alert_status === 'Reorder Soon') bg-amber-400 text-black
                                        @elseif($row->alert_status === 'Below Minimum') bg-amber-600 text-white
                                        @elseif($row->alert_status === 'Dead Stock') bg-gray-600 text-white
                                        @elseif($row->alert_status === 'New Product') bg-blue-500 text-white
                                        @elseif($row->alert_status === 'Slow Mover') bg-indigo-500 text-white
                                        @elseif($row->alert_status === 'Overstocked') bg-green-600 text-white
                                        @else bg-gray-400 text-white
                                        @endif">
                                        {{ $row->alert_status }}
                                    </span>
                                </td>
                                <td class="p-3 text-right font-bold text-red-600">{{ $row->current_stock }}</td>
                                <td class="p-3 text-right">{{ $row->stock_limit }}</td>
                                <td class="p-3 text-right">{{ $row->avg_daily_sales }}</td>
                                <td class="p-3 text-right font-semibold text-blue-600">{{ $row->suggested_reorder }}</td>
                                <td class="p-3 text-right text-gray-600">{{ \Carbon\Carbon::parse($row->last_restocked)->format('F j, Y') }}</td>
                                <td class="p-3 text-center text-gray-600">{{ $row->action_recommendation }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">
                                    <div class="flex flex-col justify-center items-center space-y-1 p-8 sticky top-1/2">
                                        <span class="material-symbols-rounded-semibig text-gray-400">taunt</span>
                                        <span class="text-gray-500">Nothing to show.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>     
        </div>

        


        <!-- EXPIRING PRODUCTS -->
        <div wire:poll.15s="expired" wire:keep-alive class="hidden"></div>
        <div x-show="tab === 'expiring'"  class="h-[39rem]">
                <div class="flex justify-between items-center mb-4">
                    <div class="space-x-1 flex">
                        <div class="border border-gray-300 rounded-tl-lg rounded-bl-lg px-3 py-2 text-xs bg-slate-50">
                            Expiring within:
                        </div>
                        <select wire:model.live="selectedRange" class="border border-gray-300 rounded-tr-lg rounded-br-lg px-3 py-2 text-xs">
                            <option value="60">60 days</option>
                            <option value="30">30 days</option>
                            <option value="14">14 days</option>
                            <option value="7">7 days</option>
                            <option value="0">Already Expired items</option>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="overflow-y-auto overflow-x-auto scrollbar-custom h-[35rem]">
                        <table class="w-full text-xs text-left shadow-sm 
                            {{ $lossRep->isNotEmpty() ? 'w-[116rem]' : 'w-full' }}">
                            <thead class="uppercase text-xs font-semibold bg-gray-200 text-gray-600">
                                <tr class="bg-gray-100 border-b-2 border-gray-300 sticky top-0">
                                    <th class="p-3 text-left sticky top-0 bg-gray-50 w-[1rem]"></th>
                                    <th class="p-3 text-left sticky top-0 bg-gray-50">Batch #</th>
                                    <th class="p-3 sticky top-0 bg-gray-50 ">Product Name</th>
                                    <th class="p-3 sticky top-0 bg-gray-50">Category</th>
                                    <th class="p-3 sticky top-0 bg-gray-50 text-right">Expiration Date</th>
                                    <th class="p-3 sticky top-0 bg-gray-50 text-right">Days Until Expiry</th>
                                    <th class="p-3 sticky top-0 bg-gray-50 text-right">Quantity</th>
                                    <th class="p-3 sticky top-0 bg-gray-50 text-right">Cost per Unit (₱)</th>
                                    <th class="p-3 sticky top-0 bg-gray-50 text-right">Total Loss (₱)</th>
                                    <th class="p-3 sticky top-0 bg-gray-50 text-center">Will Sell?</th>
                                    <th class="p-3 sticky top-0 bg-gray-50 text-center">To Do</th>
                                    <!-- <th class=" p-3 sticky top-0 bg-gray-50">Status</th> -->
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                @forelse ($expiredProd as $row)
                                    <tr class="transition hover:bg-red-100 {{ $row->days_until_expiry <= 10 ? 'bg-rose-50' : '' }} {{ $row->days_until_expiry <=0 ? 'bg-red-200' : '' }}">
                                        <td class="py-3 px-4 text-left">
                                            @if ($row->days_until_expiry <= 10)
                                            <span class="material-symbols-rounded-premium text-red-500">priority_high</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-left">{{ $row->batch_num }}</td>
                                        <td class="py-3 px-4">{{ $row->prod_name }}</td>
                                        <td class="py-3 px-4">{{ $row->cat_name }}</td>
                                        <td class="py-3 px-4 text-right">{{ $row->date }}</td>
                                        <td class="py-3 px-4 text-right">
                                            @if($row->days_until_expiry < 0)
                                                Expired {{ abs($row->days_until_expiry) }} days ago
                                            @elseif($row->days_until_expiry == 0)
                                                Expires today
                                            @elseif($row->days_until_expiry == 1)
                                                Expires tomorrow
                                            @else
                                                {{ $row->days_until_expiry }} days left
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">{{ $row->expired_stock }}</td>
                                        <td class="py-3 px-4 text-right">₱{{ number_format($row->cost, 2) }}</td>
                                        <td class="py-3 px-4 text-right">₱{{ number_format($row->total_loss, 2) }}</td>
                                        <td class="py-3 px-4 text-center text-[10px]
                                            @if(str_contains($row->will_sell_before_expiry, 'unlikely to sell'))
                                                bg-rose-100 text-red-600 font-medium
                                            @elseif(str_contains($row->will_sell_before_expiry, 'Already expired'))
                                                bg-red-800 text-white font-medium
                                            @elseif(str_contains($row->will_sell_before_expiry, 'At risk'))
                                                bg-orange-100 text-orange-600 font-medium
                                            @elseif(str_contains($row->will_sell_before_expiry, 'Will likely sell out'))
                                                bg-green-100 text-green-600 font-medium
                                            @endif
                                        ">
                                            {{ $row->will_sell_before_expiry }}
                                        </td>

                                        <td class="py-3 px-4 text-center text-[10px]
                                            @if(str_contains($row->insight, 'Expired'))
                                                bg-gray-800 text-white font-medium
                                            @elseif(str_contains($row->insight, 'Critical') || str_contains($row->insight, 'Urgent'))
                                                bg-red-600 text-white font-medium
                                            @elseif(str_contains($row->insight, 'Action needed'))
                                                bg-orange-500 text-white font-medium
                                            @elseif(str_contains($row->insight, 'Warning') || str_contains($row->insight, 'Sales pace too slow'))
                                                bg-yellow-500 text-white font-medium
                                            @elseif(str_contains($row->insight, 'week left') || str_contains($row->insight, 'weeks left'))
                                                bg-blue-500 text-white font-medium
                                            @elseif(str_contains($row->insight, 'month left'))
                                                bg-indigo-500 text-white font-medium
                                            @else
                                                bg-green-600 text-white font-medium
                                            @endif
                                        ">
                                            {{ $row->insight }}
                                        </td>
                                    </tr>
                                @empty 
                                    <tr>
                                        <td colspan="11" class="text-center">
                                            <div class="flex flex-col justify-center items-center space-y-1 p-8 sticky top-1/2">
                                                <span class="material-symbols-rounded-semibig text-gray-400">taunt</span>
                                                <span class="text-gray-500">Nothing to show.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>

        <!-- DAMAGED/ LOSS/ EXPIRED-->
        <div x-show="tab === 'loss'">

            <div class="flex items-center mb-4 space-x-2 relative justify-between">
                <div class="space-x-1">
                    <button wire:click="showAll" class="border rounded px-3 py-2 text-xs transition-colors
                            {{ is_null($selectedMonths) && is_null($selectedYears) 
                                ? 'bg-red-600 text-white border-red-600' 
                                : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                        Show All 
                    </button>
                    <select wire:model.live="selectedMonths" class="border border-gray-300 rounded px-3 py-2 text-xs">
                        @foreach ($monthNames as $index => $name)
                            <option value="{{ $index + 1 }}">{{ $name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="selectedYears" class="border border-gray-300 rounded px-3 py-2 text-xs">
                            @forelse ($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @empty
                            <option value="{{ now()->year }}">{{ now()->year }}</option>
                        @endforelse
                    </select>
                </div>                
            </div>
            
            <div class="h-[39rem]">
                <div class="hidden"></div>
                <div class="overflow-y-auto overflow-x-auto scrollbar-custom h-[35rem]">
                    <table class="w-full text-xs text-left shadow-sm 
                        {{ $lossRep->isNotEmpty() ? 'w-[100rem]' : 'w-full' }}">
                        <thead class="uppercase text-xs font-semibold bg-gray-200 text-gray-600">
                            <tr class="bg-gray-100 border-b-2 border-gray-300 sticky top-0">
                                <th class="p-3 text-left sticky top-0 bg-gray-50">Date Reported</th>
                                <th class="p-3 sticky top-0 bg-gray-50">Product Name</th>
                                <th class="p-3 sticky top-0 bg-gray-50">Category</th>
                                <th class="p-3 sticky top-0 bg-gray-50 text-center">Loss Type</th>
                                <th class="p-3 sticky top-0 bg-gray-50 text-right">Quantity Lost</th>
                                <th class="p-3 sticky top-0 bg-gray-50 text-right">Unit Cost (₱)</th>
                                <th class="p-3 sticky top-0 bg-gray-50 text-right">Total Loss (₱)</th>
                                <th class="p-3 sticky top-0 bg-gray-50 text-right">Reported By</th>
                                <th class="p-3 sticky top-0 bg-gray-50 text-center">Remarks</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse ($lossRep as $row)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4 text-left">{{ \Carbon\Carbon::parse($row->date_reported)->format('M d, Y') }}</td>
                                    <td class="py-3 px-4">{{ $row->prod_name }}</td>
                                    <td class="py-3 px-4">{{ $row->cat_name }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-1 rounded text-[10px] font-medium
                                            @if(strtolower($row->type) === 'expired') bg-red-100 text-red-700
                                            @elseif(strtolower($row->type) === 'damaged') bg-orange-100 text-orange-700
                                            @else bg-gray-100 text-gray-700
                                            @endif">
                                            {{ ucfirst($row->type) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium">{{ number_format($row->qty) }}</td>
                                    <td class="py-3 px-4 text-right">₱{{ number_format($row->unit_cost, 2) }}</td>
                                    <td class="py-3 px-4 text-right font-semibold text-red-600">₱{{ number_format($row->total_loss, 2) }}</td>
                                    <td class="py-3 px-4 text-right">{{ ucwords($row->reported_by) }}</td>
                                    <td class="py-3 px-4 text-gray-600 text-center">{{ $row->remarks ?? '—' }}</td>
                                </tr>
                            @empty 
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="flex flex-col justify-center items-center space-y-1 p-8 sticky top-1/2">
                                            <span class="material-symbols-rounded-semibig text-gray-400">taunt</span>
                                            <span class="text-gray-500">Nothing to show.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

