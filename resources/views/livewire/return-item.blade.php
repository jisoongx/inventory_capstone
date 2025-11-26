<div>
    {{-- Main Container --}}
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg w-full max-w-6xl mx-auto h-full max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white p-4 rounded-t-lg flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold mb-1">Return Item Process</h3>
                        <p class="text-sm text-orange-100">
                            Receipt #{{ str_pad($receiptId, 6, '0', STR_PAD_LEFT) }} • 
                            {{ \Carbon\Carbon::parse($receiptDetails->receipt_date)->format('M d, Y h:i A') }}
                        </p>
                        <p class="text-xs text-orange-100 mt-1">
                            {{ $store_info->store_name }}
                        </p>
                    </div>
                    <a href="{{ route('reports.sales_performance') }}" 
                        class="text-white hover:text-gray-200">
                        <span class="material-symbols-rounded text-2xl">close</span>
                    </a>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if (session()->has('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 flex items-center gap-2 mx-6 mt-4 rounded-lg">
                    <span class="material-symbols-rounded">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 flex items-center gap-2 mx-6 mt-4 rounded-lg">
                    <span class="material-symbols-rounded">error</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Tabs -->
            <div x-data="{ activeTab: 'returnable' }" class="flex-1 overflow-hidden flex flex-col">
                <div class="border-b border-gray-200 px-6 pt-4">
                    <nav class="-mb-px flex space-x-4">
                        <button @click="activeTab = 'returnable'"
                            :class="activeTab === 'returnable' ?   'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm">
                            Returnable Items
                            <span class="ml-2 bg-orange-100 text-orange-600 py-0.5 px-2 rounded-full text-xs font-bold">
                                {{ $returnableItems->count() }}
                            </span>
                        </button>
                        <button @click="activeTab = 'history'"
                            :class="activeTab === 'history' ?  'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm">
                            Return History
                            <span class="ml-2 bg-blue-100 text-blue-600 py-0.5 px-2 rounded-full text-xs font-bold">
                                {{ $returnHistoryData->count() }}
                            </span>
                        </button>
                    </nav>
                </div>

                <!-- Content Area -->
                <div class="flex-1 overflow-y-auto min-h-0 p-6">
                    <!-- Returnable Items Tab -->
                    <div x-show="activeTab === 'returnable'">
                        @if($returnableItems->isEmpty())
                            <div class="flex flex-col items-center justify-center py-16">
                                <span class="material-symbols-rounded text-6xl text-gray-300 mb-4">inventory_2</span>
                                <p class="text-gray-600 font-medium">No Returnable Items</p>
                                <p class="text-sm text-gray-400 mt-1">All items have been fully returned</p>
                            </div>
                        @else
                            <!-- Bulk Actions Bar -->
                            @if(! empty($selectedItems))
                            <div class="mb-4 bg-orange-50 border border-orange-200 rounded-lg p-4 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-orange-900">{{ count($selectedItems) }} item(s) selected</span>
                                </div>
                                <button wire:click="openBulkReturnModal"
                                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-semibold transition flex items-center gap-2">
                                    <span class="material-symbols-rounded text-sm">undo</span>
                                    Process Selected Returns
                                </button>
                            </div>
                            @endif

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-100 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs w-12">
                                                <input type="checkbox" 
                                                    wire:model. live="selectAll"
                                                    wire:click="toggleSelectAll"
                                                    class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                            </th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs">
                                                Product
                                            </th>
                                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs">
                                                Unit Price
                                            </th>
                                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs">
                                                Original Quantity
                                            </th>
                                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs">
                                                Already Returned
                                            </th>
                                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs">
                                                Returnable Quantity
                                            </th>
                                            <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase text-xs">
                                                Max Refund
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach($returnableItems as $item)
                                            <tr class="hover:bg-orange-50 transition-colors cursor-pointer"
                                                wire:click="openReturnModal({{ $item->item_id }})">
                                                <td class="px-4 py-3 text-center" 
                                                    onclick="event.stopPropagation()">
                                                    <input type="checkbox" 
                                                        wire:model.live="selectedItems"
                                                        value="{{ $item->item_id }}"
                                                        class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                                </td>
                                                <td class="px-4 py-3 font-medium text-gray-900">
                                                    {{ $item->product_name }}
                                                    @if($item->item_discount_value > 0)
                                                        <div class="text-xs text-orange-600 mt-1">
                                                            <span class="material-symbols-rounded text-xs align-middle">local_offer</span>
                                                            Discount: 
                                                            @if($item->item_discount_type === 'percent')
                                                                {{ $item->item_discount_value }}%
                                                            @else
                                                                ₱{{ number_format($item->item_discount_value, 2) }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-center font-semibold text-gray-700">
                                                    ₱{{ number_format($item->selling_price, 2) }}
                                                </td>
                                                <td class="px-4 py-3 text-center font-bold text-gray-900">
                                                    {{ $item->item_quantity }}
                                                </td>
                                                <td class="px-4 py-3 text-center font-bold text-orange-600">
                                                    {{ $item->already_returned }}
                                                </td>
                                                <td class="px-4 py-3 text-center font-bold text-green-600">
                                                    {{ $item->returnable_quantity }}
                                                </td>
                                                <td class="px-4 py-3 text-right font-bold text-blue-600">
                                                    ₱{{ number_format($item->selling_price * $item->returnable_quantity, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Instructions -->
                            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <p class="text-xs text-blue-800 flex items-start gap-2">
                                    <span class="material-symbols-rounded text-sm">info</span>
                                    <span><strong>Single Return:</strong> Click on any item row.   <strong>Multiple Returns:</strong> Check the boxes and click "Process Selected Returns".</span>
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Return History Tab -->
                    <div x-show="activeTab === 'history'">
                        @if($returnHistoryData->isEmpty())
                            <div class="flex flex-col items-center justify-center py-16">
                                <span class="material-symbols-rounded text-6xl text-gray-300 mb-4">history</span>
                                <p class="text-gray-600 font-medium">No Return History</p>
                                <p class="text-sm text-gray-400 mt-1">No items have been returned yet</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-100 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs">Return Date</th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs">Product</th>
                                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs">Quantity</th>
                                            <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase text-xs">Refund</th>
                                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs">Status</th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs">Reason</th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs">Processed By</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach($returnHistoryData as $return)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-4 py-3 text-gray-700">
                                                    {{ \Carbon\Carbon::parse($return->return_date)->format('M d, Y') }}
                                                    <div class="text-xs text-gray-500">
                                                        {{ \Carbon\Carbon::parse($return->return_date)->format('h:i A') }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 font-medium text-gray-900">{{ $return->product_name }}</td>
                                                <td class="px-4 py-3 text-center font-bold text-gray-900">{{ $return->return_quantity }}</td>
                                                <td class="px-4 py-3 text-right font-bold text-blue-600">₱{{ number_format($return->refund_amount, 2) }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    @if($return->damaged_id)
                                                        <span class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded-full font-medium">
                                                            {{ $return->damaged_type }}
                                                        </span>
                                                    @else
                                                        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">Restocked</span>
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
                                            <td colspan="2" class="px-4 py-3 text-left font-bold uppercase text-xs">Total Summary</td>
                                            <td class="px-4 py-3 text-center font-bold text-sm">{{ $returnHistoryData->sum('return_quantity') }}</td>
                                            <td class="px-4 py-3 text-right font-bold text-sm text-blue-600">₱{{ number_format($returnHistoryData->sum('refund_amount'), 2) }}</td>
                                            <td colspan="3" class="px-4 py-3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Summary Cards -->
                            <div class="grid grid-cols-4 gap-4 mt-6">
                                <div class="bg-gradient-to-br from-blue-50 to-white rounded-lg p-4 border border-blue-200">
                                    <p class="text-xs text-gray-600 font-medium mb-1">Total Returns</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ $returnHistoryData->count() }}</p>
                                </div>
                                <div class="bg-gradient-to-br from-green-50 to-white rounded-lg p-4 border border-green-200">
                                    <p class="text-xs text-gray-600 font-medium mb-1">Items Restocked</p>
                                    <p class="text-2xl font-bold text-green-600">{{ $returnHistoryData->where('damaged_id', null)->sum('return_quantity') }}</p>
                                </div>
                                <div class="bg-gradient-to-br from-red-50 to-white rounded-lg p-4 border border-red-200">
                                    <p class="text-xs text-gray-600 font-medium mb-1">Damaged Items</p>
                                    <p class="text-2xl font-bold text-red-600">{{ $returnHistoryData->whereNotNull('damaged_id')->count() }}</p>
                                </div>
                                <div class="bg-gradient-to-br from-purple-50 to-white rounded-lg p-4 border border-purple-200">
                                    <p class="text-xs text-gray-600 font-medium mb-1">Total Refunded</p>
                                    <p class="text-xl font-bold text-purple-600">₱{{ number_format($returnHistoryData->sum('refund_amount'), 2) }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Single Item Return Modal --}}
    @if($showReturnModal && ! $isBulkReturn && $selectedItemForReturn)
    <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-[100] p-4"
         x-data
         x-init="$el. focus()"
         tabindex="-1">
        <div class="bg-white rounded-lg w-full max-w-lg mx-auto max-h-[90vh] overflow-y-auto shadow-2xl"
             @click.stop>
            <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white p-4 rounded-t-lg sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold">Process Return</h3>
                    <button wire:click="closeReturnModal" type="button" class="text-white hover:text-gray-200 transition">
                        <span class="material-symbols-rounded text-2xl">close</span>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-rounded text-orange-600 text-3xl">inventory_2</span>
                        <div>
                            <h4 class="font-bold text-gray-900">{{ $selectedItemForReturn->product_name }}</h4>
                            <p class="text-sm text-gray-600">
                                Original Quantity: {{ $selectedItemForReturn->item_quantity }}
                                @if($selectedItemForReturn->already_returned > 0)
                                    <span class="text-orange-600 ml-2">({{ $selectedItemForReturn->already_returned }} already returned)</span>
                                @endif
                            </p>
                            <p class="text-sm text-gray-600">Unit Price: ₱{{ number_format($selectedItemForReturn->selling_price, 2) }}</p>
                        </div>
                    </div>
                </div>

                <form wire:submit.prevent="submitReturn" class="space-y-4">
                    <div x-data="{ 
                        quantity: @entangle('returnQuantity'). live, 
                        maxQty: {{ $maxReturnQuantity }},
                        showWarning: false,
                        checkQuantity() {
                            this.showWarning = parseInt(this.quantity) > this.maxQty;
                        }
                    }">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Return Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                            x-model="quantity"
                            @input="checkQuantity()"
                            min="1" 
                            max="{{ $maxReturnQuantity }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            placeholder="Enter quantity to return">
                        
                        <div x-show="showWarning" x-transition class="mt-2 bg-red-50 border border-red-300 rounded-lg p-3 flex items-start gap-2">
                            <span class="material-symbols-rounded text-red-600 text-lg">warning</span>
                            <div class="flex-1">
                                <p class="text-red-800 text-xs font-semibold">Quantity Exceeds Limit</p>
                                <p class="text-red-700 text-xs mt-1">
                                    You can only return up to <span class="font-bold">{{ $maxReturnQuantity }}</span> 
                                    {{ $maxReturnQuantity == 1 ? 'item' : 'items' }}.  
                                    <strong>The return will NOT be processed if you exceed this limit.</strong>
                                </p>
                            </div>
                        </div>
                        
                        @error('returnQuantity')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Maximum returnable: <span class="font-bold">{{ $maxReturnQuantity }}</span></p>
                    </div>

                    {{-- ENHANCED RETURN REASON DROPDOWN --}}
                    <div x-data="{ 
                        selectedReason: @entangle('returnReason').live,
                        isDamaged: false,
                        reasonLabel: '',
                        damagedReasons: @js(array_keys($this->damagedReasons)),
                        updateSelection() {
                            this.isDamaged = this.damagedReasons.includes(this.selectedReason);
                            if (this.selectedReason) {
                                const select = this.$refs.reasonSelect;
                                const selectedOption = select.options[select.selectedIndex];
                                this.reasonLabel = selectedOption.text. replace(/^[✓✗]\s*/, '');
                            }
                        }
                    }" x-init="$watch('selectedReason', () => updateSelection())">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Return Reason <span class="text-red-500">*</span>
                        </label>
                        
                        <select x-ref="reasonSelect"
                                wire:model.live="returnReason"
                                @change="updateSelection()" 
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200 bg-white hover:border-gray-400">
                            <option value="">🔍 Select return reason...</option>
                            
                            <optgroup label="━━━ ✓ WILL BE RESTOCKED ━━━" class="text-green-700 font-bold">
                                @foreach($this->inventoryReasons as $key => $label)
                                    <option value="{{ $key }}" class="text-green-700 py-2">↩️ {{ $label }}</option>
                                @endforeach
                            </optgroup>
                            
                            <optgroup label="━━━ ⚠ DAMAGED - NOT RESTOCKED ━━━" class="text-red-700 font-bold">
                                @foreach($this->damagedReasons as $key => $label)
                                    <option value="{{ $key }}" class="text-red-700 py-2">❌ {{ $label }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                        
                        @error('returnReason')
                            <p class="text-red-500 text-xs mt-2 flex items-center gap-1">
                                <span class="material-symbols-rounded text-sm">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                        
                        {{-- DYNAMIC FEEDBACK CARD --}}
                        <div x-show="selectedReason" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="mt-3">
                            
                            {{-- Restocked Status --}}
                            <div x-show="! isDamaged" 
                                 class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl p-4 shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="bg-green-500 rounded-full p-2 flex-shrink-0">
                                        <span class="material-symbols-rounded text-white text-xl">inventory</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-green-700 font-bold text-sm">✓ WILL BE RESTOCKED</span>
                                        </div>
                                        <p class="text-green-800 text-xs font-medium" x-text="'Reason: ' + reasonLabel"></p>
                                        <p class="text-green-700 text-xs mt-2 leading-relaxed">
                                            <span class="material-symbols-rounded text-xs align-middle">check_circle</span>
                                            Item will be <strong>returned to inventory</strong> and made available for sale again.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Damaged Status --}}
                            <div x-show="isDamaged" 
                                 class="bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-300 rounded-xl p-4 shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="bg-red-500 rounded-full p-2 flex-shrink-0">
                                        <span class="material-symbols-rounded text-white text-xl">warning</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-red-700 font-bold text-sm">⚠ DAMAGED - NOT RESTOCKED</span>
                                        </div>
                                        <p class="text-red-800 text-xs font-medium" x-text="'Reason: ' + reasonLabel"></p>
                                        <p class="text-red-700 text-xs mt-2 leading-relaxed">
                                            <span class="material-symbols-rounded text-xs align-middle">dangerous</span>
                                            Item will be <strong>marked as damaged</strong> and <strong>removed from inventory</strong>.  It will not be available for resale.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- HELPFUL INFO BOX --}}
                        <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start gap-2">
                                <span class="material-symbols-rounded text-blue-600 text-lg flex-shrink-0">info</span>
                                <div class="text-xs text-blue-800 leading-relaxed">
                                    <p class="font-semibold mb-1">Choose the appropriate reason:</p>
                                    <ul class="space-y-1 ml-1">
                                        <li class="flex items-start gap-1">
                                            <span class="text-green-600">↩️</span>
                                            <span><strong>Green options:</strong> Item can be resold (wrong item, unsealed, etc.)</span>
                                        </li>
                                        <li class="flex items-start gap-1">
                                            <span class="text-red-600">❌</span>
                                            <span><strong>Red options:</strong> Item cannot be resold (expired, broken, contaminated, etc.)</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Return Subtotal:</span>
                            <span class="text-lg font-bold text-blue-600">
                                ₱{{ number_format(floatval($selectedItemForReturn->selling_price) * intval($returnQuantity), 2) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600">
                            <span class="material-symbols-rounded text-sm align-middle">info</span>
                            This amount should be refunded to the customer
                        </p>
                    </div>

                    <div class="flex gap-3 pt-4 border-t">
                        <button type="button" wire:click="closeReturnModal" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 px-4 rounded-lg font-semibold transition">Cancel</button>
                        <button type="submit" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-3 px-4 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                            <span class="material-symbols-rounded text-sm">check_circle</span>
                            Process Return
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Bulk Return Modal --}}
    @if($showReturnModal && $isBulkReturn && ! empty($bulkReturnItems))
    <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-[100] p-4"
         x-data
         x-init="$el.focus()"
         tabindex="-1">
        <div class="bg-white rounded-lg w-full max-w-2xl mx-auto max-h-[90vh] overflow-y-auto shadow-2xl" @click.stop>
            <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white p-4 rounded-t-lg sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold">Process Multiple Returns</h3>
                        <p class="text-sm text-orange-100 mt-1">{{ count($bulkReturnItems) }} item(s) selected</p>
                    </div>
                    <button wire:click="closeReturnModal" type="button" class="text-white hover:text-gray-200">
                        <span class="material-symbols-rounded text-2xl">close</span>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <form wire:submit.prevent="submitBulkReturn" class="space-y-4">
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                            <h4 class="font-semibold text-gray-900 text-sm">Items to Return</h4>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($bulkReturnItems as $itemId => $item)
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-start gap-4">
                                    <span class="material-symbols-rounded text-orange-600 text-2xl mt-1">inventory_2</span>
                                    <div class="flex-1">
                                        <h5 class="font-semibold text-gray-900">{{ $item['product_name'] }}</h5>
                                        <p class="text-xs text-gray-600 mt-1">
                                            Unit Price: ₱{{ number_format($item['selling_price'], 2) }} • 
                                            Max Returnable: {{ $item['max_quantity'] }}
                                        </p>
                                    </div>
                                    <div class="w-24">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
                                        <input type="number"
                                            wire:model.live="bulkReturnItems. {{ $itemId }}.return_quantity"
                                            min="1"
                                            max="{{ $item['max_quantity'] }}"
                                            class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-orange-500">
                                        @error("bulkReturnItems.{$itemId}.return_quantity")
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500">Refund</p>
                                        <p class="font-bold text-blue-600">₱{{ number_format($item['selling_price'] * $item['return_quantity'], 2) }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-700">Total Refund Amount:</span>
                            <span class="text-2xl font-bold text-blue-600">
                                ₱{{ number_format(collect($bulkReturnItems)->sum(function($item) { 
                                    return $item['selling_price'] * $item['return_quantity']; 
                                }), 2) }}
                            </span>
                        </div>
                    </div>

                    {{-- ENHANCED BULK RETURN REASON DROPDOWN --}}
                    <div x-data="{ 
                        selectedReason: @entangle('returnReason').live,
                        isDamaged: false,
                        reasonLabel: '',
                        itemCount: {{ count($bulkReturnItems) }},
                        damagedReasons: @js(array_keys($this->damagedReasons)),
                        updateSelection() {
                            this. isDamaged = this.damagedReasons.includes(this. selectedReason);
                            if (this.selectedReason) {
                                const select = this.$refs.reasonSelect;
                                const selectedOption = select.options[select.selectedIndex];
                                this.reasonLabel = selectedOption. text.replace(/^[✓✗]\s*/, '');
                            }
                        }
                    }" x-init="$watch('selectedReason', () => updateSelection())">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Return Reason (applies to all items) <span class="text-red-500">*</span>
                        </label>
                        
                        <select x-ref="reasonSelect"
                                wire:model.live="returnReason"
                                @change="updateSelection()" 
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200 bg-white hover:border-gray-400">
                            <option value="">🔍 Select return reason for all items...</option>
                            
                            <optgroup label="━━━ ✓ WILL BE RESTOCKED ━━━" class="text-green-700 font-bold">
                                @foreach($this->inventoryReasons as $key => $label)
                                    <option value="{{ $key }}" class="text-green-700 py-2">↩️ {{ $label }}</option>
                                @endforeach
                            </optgroup>
                            
                            <optgroup label="━━━ ⚠ DAMAGED - NOT RESTOCKED ━━━" class="text-red-700 font-bold">
                                @foreach($this->damagedReasons as $key => $label)
                                    <option value="{{ $key }}" class="text-red-700 py-2">❌ {{ $label }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                        
                        @error('returnReason')
                            <p class="text-red-500 text-xs mt-2 flex items-center gap-1">
                                <span class="material-symbols-rounded text-sm">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                        
                        {{-- DYNAMIC FEEDBACK CARD FOR BULK --}}
                        <div x-show="selectedReason" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="mt-3">
                            
                            {{-- Restocked Status --}}
                            <div x-show="!isDamaged" 
                                 class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl p-4 shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="bg-green-500 rounded-full p-2 flex-shrink-0">
                                        <span class="material-symbols-rounded text-white text-xl">inventory</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-green-700 font-bold text-sm">✓ ALL ITEMS WILL BE RESTOCKED</span>
                                        </div>
                                        <p class="text-green-800 text-xs font-medium" x-text="'Reason: ' + reasonLabel"></p>
                                        <p class="text-green-700 text-xs mt-2 leading-relaxed">
                                            <span class="material-symbols-rounded text-xs align-middle">check_circle</span>
                                            All <strong x-text="itemCount"></strong> items will be <strong>returned to inventory</strong> and made available for sale again.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Damaged Status --}}
                            <div x-show="isDamaged" 
                                 class="bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-300 rounded-xl p-4 shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="bg-red-500 rounded-full p-2 flex-shrink-0">
                                        <span class="material-symbols-rounded text-white text-xl">warning</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-red-700 font-bold text-sm">⚠ ALL ITEMS DAMAGED - NOT RESTOCKED</span>
                                        </div>
                                        <p class="text-red-800 text-xs font-medium" x-text="'Reason: ' + reasonLabel"></p>
                                        <p class="text-red-700 text-xs mt-2 leading-relaxed">
                                            <span class="material-symbols-rounded text-xs align-middle">dangerous</span>
                                            All <strong x-text="itemCount"></strong> items will be <strong>marked as damaged</strong> and <strong>removed from inventory</strong>. They will not be available for resale. 
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- HELPFUL INFO BOX --}}
                        <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start gap-2">
                                <span class="material-symbols-rounded text-blue-600 text-lg flex-shrink-0">info</span>
                                <div class="text-xs text-blue-800 leading-relaxed">
                                    <p class="font-semibold mb-1">This reason will apply to all selected items:</p>
                                    <ul class="space-y-1 ml-1">
                                        <li class="flex items-start gap-1">
                                            <span class="text-green-600">↩️</span>
                                            <span><strong>Green options:</strong> All items can be resold (wrong item, unsealed, etc.)</span>
                                        </li>
                                        <li class="flex items-start gap-1">
                                            <span class="text-red-600">❌</span>
                                            <span><strong>Red options:</strong> All items cannot be resold (expired, broken, contaminated, etc.)</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t">
                        <button type="button" wire:click="closeReturnModal" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 px-4 rounded-lg font-semibold transition">Cancel</button>
                        <button type="submit" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-3 px-4 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                            <span class="material-symbols-rounded text-sm">check_circle</span>
                            Process All Returns
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>