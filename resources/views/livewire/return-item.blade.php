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
                            : class="activeTab === 'returnable' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm">
                            Returnable Items
                            <span class="ml-2 bg-orange-100 text-orange-600 py-0.5 px-2 rounded-full text-xs font-bold">
                                {{ $returnableItems->count() }}
                            </span>
                        </button>
                        <button @click="activeTab = 'history'"
                            :class="activeTab === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
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
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-100 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs w-12">
                                                <input type="checkbox" 
                                                    wire:model.live="selectAll"
                                                    class="w-4 h-4 text-orange-600 border-gray-300 rounded focus: ring-orange-500">
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
                                                    @if(($item->item_discount_amount ??  0) > 0)
                                                        <div class="text-xs text-orange-600 mt-1">
                                                            <span class="material-symbols-rounded text-xs align-middle">local_offer</span>
                                                            Discount: ₱{{ number_format($item->item_discount_amount, 2) }}
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

                            <!-- Bulk Actions Bar -->
                            @if(! empty($selectedItems))
                            <div class="mt-4 bg-orange-50 border border-orange-200 rounded-lg p-4 flex items-center justify-between">
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

                            <!-- Instructions -->
                            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <p class="text-xs text-blue-800 flex items-start gap-2">
                                    <span class="material-symbols-rounded text-sm">info</span>
                                    <span><strong>Single Return:</strong> Click on any item row.  <strong>Multiple Returns:</strong> Check the boxes and click "Process Selected Returns".</span>
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
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs">Product Returned</th>
                                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs">Quantity</th>
                                            <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase text-xs">Refund</th>
                                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs">Status</th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase text-xs">Reason</th>
                                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase text-xs">Replacement</th>
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
                                                <td class="px-4 py-3 text-center">
                                                    @if($return->replacement_receipt_id)
                                                        <div class="flex flex-col items-center gap-1">
                                                            <span class="text-blue-600 font-semibold flex items-center gap-1">
                                                                <span class="material-symbols-rounded text-sm">swap_horiz</span>
                                                                #{{ str_pad($return->replacement_receipt_id, 6, '0', STR_PAD_LEFT) }}
                                                            </span>
                                                            @if($return->replacement_products)
                                                                <span class="text-xs text-gray-600 max-w-[150px] truncate" title="{{ $return->replacement_products }}">
                                                                    {{ $return->replacement_products }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-gray-400">—</span>
                                                    @endif
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
                                            <td colspan="4" class="px-4 py-3 text-center text-xs text-gray-600">
                                                <span class="font-semibold">Replacements:  {{ $returnHistoryData->whereNotNull('replacement_receipt_id')->count() }}</span>
                                            </td>
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
                                    <p class="text-xs text-gray-600 font-medium mb-1">Replacements</p>
                                    <p class="text-2xl font-bold text-purple-600">{{ $returnHistoryData->whereNotNull('replacement_receipt_id')->count() }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Single Item Return Modal - EXPANDED --}}
@if($showReturnModal && !  $isBulkReturn && $selectedItemForReturn)
<div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-[100] p-4"
     x-data="{ 
        returnQty: @entangle('returnQuantity').live,
        returnReason: @entangle('returnReason').live,
        customReason: @entangle('customReturnReason').live,
        returnAction: @entangle('returnAction').live,
        replacementItems: @entangle('replacementItems').live,
        isValid() {
            const qty = parseInt(this.returnQty) || 0;
            const hasReason = (this.returnReason && this.returnReason.trim() !== '') || 
                             (this.customReason && this.customReason.trim() !== '');
            const hasAction = this.returnAction !== '';
            return qty > 0 && qty <= {{ $maxReturnQuantity }} && hasReason && hasAction;
        },
        getTotalReplacementCost() {
            let total = 0;
            this.replacementItems.forEach(item => {
                total += parseFloat(item.selling_price) * parseInt(item.quantity);
            });
            return total;
        }
     }"
     x-init="$el.focus()"
     tabindex="-1">
    <div class="bg-white rounded-lg w-full max-w-5xl mx-auto max-h-[90vh] overflow-y-auto shadow-2xl"
         @click.stop>
        <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white p-4 rounded-t-lg sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold">Process Return</h3>
                <button wire:click="closeReturnModal" type="button" class="text-white hover:text-gray-200 transition">
                    <span class="material-symbols-rounded text-2xl">close</span>
                </button>
            </div>
        </div>

        {{-- ✅ Modal Error Messages --}}
        @if($modalError)
            <div class="mx-6 mt-4 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg flex items-start gap-2">
                <span class="material-symbols-rounded text-red-600 flex-shrink-0">error</span>
                <div class="flex-1">
                    <p class="font-semibold text-sm">Error</p>
                    <p class="text-sm">{{ $modalError }}</p>
                </div>
                <button wire:click="$set('modalError', '')" class="text-red-600 hover:text-red-800">
                    <span class="material-symbols-rounded text-lg">close</span>
                </button>
            </div>
        @endif

        @if($modalWarning)
            <div class="mx-6 mt-4 bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg flex items-start gap-2">
                <span class="material-symbols-rounded text-yellow-600 flex-shrink-0">warning</span>
                <div class="flex-1">
                    <p class="font-semibold text-sm">Warning</p>
                    <p class="text-sm">{{ $modalWarning }}</p>
                </div>
                <button wire:click="$set('modalWarning', '')" class="text-yellow-600 hover:text-yellow-800">
                    <span class="material-symbols-rounded text-lg">close</span>
                </button>
            </div>
        @endif

        <div class="p-6">
            {{-- ✅ SIDE-BY-SIDE LAYOUT --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- LEFT COLUMN: Return Information --}}
                <div class="space-y-5">
                    <div class="bg-gray-50 rounded-lg p-4">
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

                    <form wire:submit.prevent="submitReturn" class="space-y-5">
                        {{-- Return Quantity --}}
                        <div x-data="{ 
                            quantity: @entangle('returnQuantity').live, 
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
                                    </p>
                                </div>
                            </div>
                            
                            @error('returnQuantity')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Maximum returnable:  <span class="font-bold">{{ $maxReturnQuantity }}</span></p>
                        </div>

                        {{-- Return Action Radio Buttons --}}
                        <div x-data="{ action: @entangle('returnAction').live }">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Return Action <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                                       : class="action === 'restock' ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-gray-400'">
                                    <input type="radio" 
                                           wire:model.live="returnAction" 
                                           value="restock"
                                           class="w-5 h-5 text-green-600 border-gray-300 focus:ring-green-500">
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-rounded text-green-600">inventory</span>
                                            <span class="font-semibold text-gray-900">Restock</span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1">Return to inventory</p>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                                       :class="action === 'damage' ? 'border-red-500 bg-red-50' : 'border-gray-300 hover:border-gray-400'">
                                    <input type="radio" 
                                           wire:model.live="returnAction" 
                                           value="damage"
                                           class="w-5 h-5 text-red-600 border-gray-300 focus: ring-red-500">
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-rounded text-red-600">warning</span>
                                            <span class="font-semibold text-gray-900">Damaged</span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1">Mark as damaged</p>
                                    </div>
                                </label>
                            </div>
                            @error('returnAction')
                                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Return Reason Dropdown --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Return Reason (Select from list)
                            </label>
                            <select wire:model.live="returnReason" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <option value="">-- Select a reason --</option>
                                @foreach($this->allReturnReasons as $reason)
                                    <option value="{{ $reason }}">{{ $reason }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Custom Return Reason --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Or Type Custom Reason
                            </label>
                            <textarea wire:model.live="customReturnReason" 
                                      rows="2"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                      placeholder="Type your custom return reason here..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                Note: Custom reason will be used if provided, otherwise the dropdown selection will be used.
                            </p>
                        </div>

                        {{-- Refund Summary --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Return Subtotal: </span>
                                <span class="text-lg font-bold text-blue-600">
                                    ₱{{ number_format(floatval($selectedItemForReturn->selling_price) * intval($returnQuantity), 2) }}
                                </span>
                            </div>
                            <template x-if="replacementItems.length > 0">
                                <div>
                                    <div class="flex justify-between items-center mb-2 text-sm">
                                        <span class="text-gray-700">Replacement Cost:</span>
                                        <span class="font-semibold text-gray-900" x-text="'-₱' + getTotalReplacementCost().toFixed(2)"></span>
                                    </div>
                                    <div class="border-t border-blue-300 pt-2 flex justify-between items-center">
                                        <span class="text-sm font-bold text-gray-700">Net Refund:</span>
                                        <span class="text-lg font-bold"
                                              : class="({{ floatval($selectedItemForReturn->selling_price) * intval($returnQuantity) }} - getTotalReplacementCost()) >= 0 ? 'text-green-600' : 'text-red-600'"
                                              x-text="(({{ floatval($selectedItemForReturn->selling_price) * intval($returnQuantity) }} - getTotalReplacementCost()) >= 0 ? '₱' : '-₱') + Math.abs({{ floatval($selectedItemForReturn->selling_price) * intval($returnQuantity) }} - getTotalReplacementCost()).toFixed(2)">
                                        </span>
                                    </div>
                                </div>
                            </template>
                            <p class="text-xs text-gray-600 mt-2">
                                <span class="material-symbols-rounded text-sm align-middle">info</span>
                                <template x-if="replacementItems.length > 0">
                                    <span x-text="({{ floatval($selectedItemForReturn->selling_price) * intval($returnQuantity) }} - getTotalReplacementCost()) >= 0 ? 'Customer should receive ₱' + ({{ floatval($selectedItemForReturn->selling_price) * intval($returnQuantity) }} - getTotalReplacementCost()).toFixed(2) + ' refund' : 'Customer should pay additional ₱' + Math.abs({{ floatval($selectedItemForReturn->selling_price) * intval($returnQuantity) }} - getTotalReplacementCost()).toFixed(2)"></span>
                                </template>
                                <template x-if="replacementItems.length === 0">
                                    <span>This amount should be refunded to the customer</span>
                                </template>
                            </p>
                        </div>
                        {{-- Action Buttons --}}
<div class="flex gap-3 pt-4 border-t">
    <button type="button" wire:click="closeReturnModal" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 px-4 rounded-lg font-semibold transition">
        Cancel
    </button>
    <button type="submit" 
            class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-3 px-4 rounded-lg font-semibold transition flex items-center justify-center gap-2">
        <span class="material-symbols-rounded text-sm">check_circle</span>
        Process All Returns
    </button>
</div>
                    </form>
                </div>

                {{-- RIGHT COLUMN:  Replacement Items --}}
                <div class="space-y-5">
                    <div class="border-l-4 border-blue-500 pl-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-900 text-lg flex items-center gap-2">
                                <span class="material-symbols-rounded text-blue-600">swap_horiz</span>
                                Replacement Items
                            </h4>
                            <button type="button" 
                                    wire:click="toggleReplacement"
                                    class="text-sm font-semibold px-3 py-1 rounded-lg transition"
                                    :class="$wire.showReplacementSection ? 'bg-red-100 text-red-700 hover:bg-red-200' :  'bg-blue-100 text-blue-700 hover:bg-blue-200'">
                                <span x-text="$wire.showReplacementSection ? 'Hide' : 'Show'"></span>
                            </button>
                        </div>

                        @if($showReplacementSection)
                        <div class="space-y-3">
                            {{-- Barcode Scanner --}}
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Scan or Enter Barcode
                                </label>
                                <div class="flex gap-2">
                                    <input type="text" 
                                           wire:model="replacementBarcode"
                                           wire:keydown.enter.prevent="searchReplacementProduct"
                                           placeholder="Scan barcode or type manually"
                                           class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                                    <button type="button"
                                            wire:click="searchReplacementProduct"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold text-sm flex items-center gap-1">
                                        <span class="material-symbols-rounded text-sm">add</span>
                                        Add
                                    </button>
                                </div>
                            </div>

                            {{-- Replacement Items List --}}
                            @if(! empty($replacementItems))
                            <div class="bg-white border border-gray-300 rounded-lg overflow-hidden">
                                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex justify-between items-center">
                                    <h5 class="font-semibold text-gray-900 text-sm">Added Items ({{ count($replacementItems) }})</h5>
                                    <button type="button" 
                                            wire:click="clearAllReplacements"
                                            class="text-red-600 hover: text-red-700 text-xs font-semibold">
                                        Clear All
                                    </button>
                                </div>
                                <div class="divide-y divide-gray-200 max-h-[400px] overflow-y-auto">
                                    @foreach($replacementItems as $index => $item)
                                    <div class="p-3 hover:bg-gray-50 transition">
                                        <div class="flex items-start gap-3">
                                            <span class="material-symbols-rounded text-blue-600 text-xl mt-1">shopping_bag</span>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $item['name'] }}</p>
                                                <p class="text-xs text-gray-600">
                                                    Barcode: {{ $item['barcode'] }} • 
                                                    Available: {{ $item['available_stock'] }}
                                                </p>
                                                <p class="text-xs text-blue-600 font-semibold mt-1">
                                                    ₱{{ number_format($item['selling_price'], 2) }} each
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="w-20">
                                                    <input type="number" 
                                                           wire:model.live="replacementItems.{{ $index }}.quantity"
                                                           min="1"
                                                           max="{{ $item['available_stock'] }}"
                                                           class="w-full border border-gray-300 rounded px-2 py-1 text-sm text-center">
                                                    @error("replacementItems.{$index}.quantity")
                                                        <p class="text-red-500 text-xs mt-1">Max:  {{ $item['available_stock'] }}</p>
                                                    @enderror
                                                </div>
                                                <div class="text-right min-w-[80px]">
                                                    <p class="text-xs text-gray-500">Total</p>
                                                    <p class="font-bold text-gray-900 text-sm">
                                                        ₱{{ number_format(floatval($item['selling_price']) * intval($item['quantity']), 2) }}
                                                    </p>
                                                </div>
                                                <button type="button" 
                                                        wire:click="removeReplacementItem({{ $index }})"
                                                        class="text-red-600 hover:text-red-700 p-1">
                                                    <span class="material-symbols-rounded text-lg">close</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                {{-- Total Summary --}}
                                <div class="bg-blue-50 px-4 py-3 border-t-2 border-blue-300">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-gray-700">Replacement Total:</span>
                                        <span class="text-xl font-bold text-blue-600">
                                            @php
                                                $replacementTotal = 0;
                                                foreach($replacementItems as $item) {
                                                    $replacementTotal += floatval($item['selling_price']) * intval($item['quantity']);
                                                }
                                            @endphp
                                            ₱{{ number_format($replacementTotal, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                                <span class="material-symbols-rounded text-4xl text-gray-400 mb-2">add_shopping_cart</span>
                                <p class="text-gray-600 font-medium">No replacement items added</p>
                                <p class="text-sm text-gray-500 mt-1">Scan barcodes to add replacement products</p>
                            </div>
                            @endif
                        </div>
                        @else
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                            <span class="material-symbols-rounded text-4xl text-gray-400 mb-2">visibility_off</span>
                            <p class="text-gray-600 font-medium">Replacement Section Hidden</p>
                            <p class="text-sm text-gray-500 mt-1">Click "Show" to add replacement items</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Bulk Return Modal - EXPANDED --}}
@if($showReturnModal && $isBulkReturn && ! empty($bulkReturnItems))
<div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-[100] p-4"
     x-data="{ 
        quantities: @entangle('bulkReturnQuantities').live,
        reason: @entangle('returnReason').live,
        customReason: @entangle('customReturnReason').live,
        replacementItems: @entangle('replacementItems').live,
        isValid() {
            const hasReason = this.reason || this.customReason;
            return hasReason && Object.keys(this.quantities).length > 0;
        },
        getTotalReplacementCost() {
            let total = 0;
            this.replacementItems.forEach(item => {
                total += parseFloat(item.selling_price) * parseInt(item.quantity);
            });
            return total;
        }
     }"
     x-init="$el.focus()"
     tabindex="-1">
    {{-- ✅ EXPANDED MODAL:  Changed max-w-4xl to max-w-7xl --}}
    <div class="bg-white rounded-lg w-full max-w-7xl mx-auto max-h-[90vh] overflow-y-auto shadow-2xl" @click.stop>
        <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white p-4 rounded-t-lg sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold">Process Multiple Returns with Replacement</h3>
                    <p class="text-sm text-orange-100 mt-1">{{ count($bulkReturnItems) }} item(s) selected for return</p>
                </div>
                <button wire:click="closeReturnModal" type="button" class="text-white hover:text-gray-200">
                    <span class="material-symbols-rounded text-2xl">close</span>
                </button>
            </div>
        </div>

        {{-- ✅ Modal Error Messages --}}
        @if($modalError)
            <div class="mx-6 mt-4 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg flex items-start gap-2">
                <span class="material-symbols-rounded text-red-600 flex-shrink-0">error</span>
                <div class="flex-1">
                    <p class="font-semibold text-sm">Error</p>
                    <p class="text-sm">{{ $modalError }}</p>
                </div>
                <button wire:click="$set('modalError', '')" class="text-red-600 hover: text-red-800">
                    <span class="material-symbols-rounded text-lg">close</span>
                </button>
            </div>
        @endif

        @if($modalWarning)
            <div class="mx-6 mt-4 bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg flex items-start gap-2">
                <span class="material-symbols-rounded text-yellow-600 flex-shrink-0">warning</span>
                <div class="flex-1">
                    <p class="font-semibold text-sm">Warning</p>
                    <p class="text-sm">{{ $modalWarning }}</p>
                </div>
                <button wire:click="$set('modalWarning', '')" class="text-yellow-600 hover:text-yellow-800">
                    <span class="material-symbols-rounded text-lg">close</span>
                </button>
            </div>
        @endif

        <div class="p-6">
            {{-- ✅ SIDE-BY-SIDE LAYOUT --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- LEFT COLUMN: Return Items (2/3 width) --}}
                <div class="lg:col-span-2 space-y-5">
                    <form wire:submit.prevent="submitBulkReturn" class="space-y-5">
                        {{-- Items List --}}
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                                <h4 class="font-semibold text-gray-900 text-sm">Items to Return</h4>
                            </div>
                            <div class="divide-y divide-gray-200 max-h-[300px] overflow-y-auto">
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
                                                wire:model.live="bulkReturnQuantities.{{ $itemId }}"
                                                min="1"
                                                max="{{ $item['max_quantity'] }}"
                                                class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-orange-500">
                                            @error("bulkReturnQuantities.{$itemId}")
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500">Refund</p>
                                            <p class="font-bold text-blue-600">
                                                ₱{{ number_format($item['selling_price'] * ($bulkReturnQuantities[$itemId] ?? 1), 2) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Total Refund --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-semibold text-gray-700">Total Return Amount:</span>
                                <span class="text-2xl font-bold text-blue-600">
                                    @php
                                        $totalRefund = 0;
                                        foreach($bulkReturnItems as $itemId => $item) {
                                            $quantity = $bulkReturnQuantities[$itemId] ?? 1;
                                            $totalRefund += $item['selling_price'] * $quantity;
                                        }
                                    @endphp
                                    ₱{{ number_format($totalRefund, 2) }}
                                </span>
                            </div>
                            <template x-if="replacementItems.length > 0">
                                <div class="mt-3 pt-3 border-t border-blue-300">
                                    <div class="flex justify-between items-center text-sm mb-2">
                                        <span class="text-gray-700">Replacement Cost:</span>
                                        <span class="font-semibold text-gray-900" x-text="'-₱' + getTotalReplacementCost().toFixed(2)"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-gray-700">Net Refund:</span>
                                        <span class="text-xl font-bold"
                                              : class="({{ $totalRefund }} - getTotalReplacementCost()) >= 0 ? 'text-green-600' : 'text-red-600'"
                                              x-text="(({{ $totalRefund }} - getTotalReplacementCost()) >= 0 ? '₱' :  '-₱') + Math.abs({{ $totalRefund }} - getTotalReplacementCost()).toFixed(2)">
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Return Action Radio Buttons --}}
                        <div x-data="{ action: @entangle('returnAction').live }">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Return Action (Applies to all items) <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                                       :class="action === 'restock' ? 'border-green-500 bg-green-50' :  'border-gray-300 hover:border-gray-400'">
                                    <input type="radio" 
                                           wire:model.live="returnAction" 
                                           value="restock"
                                           class="w-5 h-5 text-green-600 border-gray-300 focus:ring-green-500">
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-rounded text-green-600">inventory</span>
                                            <span class="font-semibold text-gray-900">Restock All</span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1">Return all items to inventory</p>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                                       :class="action === 'damage' ? 'border-red-500 bg-red-50' : 'border-gray-300 hover:border-gray-400'">
                                    <input type="radio" 
                                           wire:model.live="returnAction" 
                                           value="damage"
                                           class="w-5 h-5 text-red-600 border-gray-300 focus: ring-red-500">
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-rounded text-red-600">warning</span>
                                            <span class="font-semibold text-gray-900">Mark All Damaged</span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1">Mark all items as damaged</p>
                                    </div>
                                </label>
                            </div>
                            @error('returnAction')
                                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Return Reason Dropdown --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Return Reason (Select from list)
                            </label>
                            <select wire:model.live="returnReason" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <option value="">-- Select a reason --</option>
                                @foreach($this->allReturnReasons as $reason)
                                    <option value="{{ $reason }}">{{ $reason }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Custom Return Reason --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Or Type Custom Reason
                            </label>
                            <textarea wire:model.live="customReturnReason" 
                                      rows="2"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus: ring-2 focus:ring-orange-500 focus:border-transparent"
                                      placeholder="Type your custom return reason here..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                Note: This reason will apply to all selected items.  Custom reason will be used if provided, otherwise the dropdown selection will be used.
                            </p>
                        </div>

                        {{-- Action Buttons --}}
<div class="flex gap-3 pt-4 border-t">
    <button type="button" wire:click="closeReturnModal" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 px-4 rounded-lg font-semibold transition">
        Cancel
    </button>
    <button type="submit" 
            class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-3 px-4 rounded-lg font-semibold transition flex items-center justify-center gap-2">
        <span class="material-symbols-rounded text-sm">check_circle</span>
        Process Return
    </button>
</div>>
                    </form>
                </div>

                {{-- RIGHT COLUMN:  Replacement Items (1/3 width) --}}
                <div class="space-y-5">
                    <div class="border-l-4 border-blue-500 pl-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-900 text-base flex items-center gap-2">
                                <span class="material-symbols-rounded text-blue-600">swap_horiz</span>
                                Replacements
                            </h4>
                            <button type="button" 
                                    wire:click="toggleReplacement"
                                    class="text-xs font-semibold px-2 py-1 rounded-lg transition"
                                    :class="$wire.showReplacementSection ? 'bg-red-100 text-red-700 hover:bg-red-200' :  'bg-blue-100 text-blue-700 hover:bg-blue-200'">
                                <span x-text="$wire.showReplacementSection ? 'Hide' : 'Show'"></span>
                            </button>
                        </div>

                        @if($showReplacementSection)
                        <div class="space-y-3">
                            {{-- Barcode Scanner --}}
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <label class="block text-xs font-semibold text-gray-700 mb-2">
                                    Scan Barcode
                                </label>
                                <div class="flex gap-2">
                                    <input type="text" 
                                           wire:model="replacementBarcode"
                                           wire:keydown.enter.prevent="searchReplacementProduct"
                                           placeholder="Scan barcode"
                                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                                    <button type="button"
                                            wire:click="searchReplacementProduct"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold text-xs">
                                        <span class="material-symbols-rounded text-sm">add</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Replacement Items List --}}
                            @if(! empty($replacementItems))
                            <div class="bg-white border border-gray-300 rounded-lg overflow-hidden">
                                <div class="bg-gray-50 px-3 py-2 border-b border-gray-200 flex justify-between items-center">
                                    <h5 class="font-semibold text-gray-900 text-xs">Items ({{ count($replacementItems) }})</h5>
                                    <button type="button" 
                                            wire:click="clearAllReplacements"
                                            class="text-red-600 hover:text-red-700 text-xs font-semibold">
                                        Clear
                                    </button>
                                </div>
                                <div class="divide-y divide-gray-200 max-h-[400px] overflow-y-auto">
                                    @foreach($replacementItems as $index => $item)
                                    <div class="p-2 hover:bg-gray-50 transition">
                                        <div class="flex items-start gap-2">
                                            <span class="material-symbols-rounded text-blue-600 text-lg mt-1">shopping_bag</span>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-gray-900 text-xs truncate">{{ $item['name'] }}</p>
                                                <p class="text-xs text-gray-600">
                                                    ₱{{ number_format($item['selling_price'], 2) }}
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <input type="number" 
                                                       wire:model.live="replacementItems.{{ $index }}.quantity"
                                                       min="1"
                                                       max="{{ $item['available_stock'] }}"
                                                       class="w-14 border border-gray-300 rounded px-1 py-1 text-xs text-center">
                                                <button type="button" 
                                                        wire:click="removeReplacementItem({{ $index }})"
                                                        class="text-red-600 hover:text-red-700 p-1">
                                                    <span class="material-symbols-rounded text-sm">close</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="text-right mt-1">
                                            <p class="font-bold text-gray-900 text-xs">
                                            ₱{{ number_format(floatval($item['selling_price']) * intval($item['quantity']), 2) }}
                                            </p>
                                        </div>
                                        @error("replacementItems.{$index}.quantity")
                                            <p class="text-red-500 text-xs mt-1">Max:  {{ $item['available_stock'] }}</p>
                                        @enderror
                                    </div>
                                    @endforeach
                                </div>
                                {{-- Total Summary --}}
                                <div class="bg-blue-50 px-3 py-2 border-t-2 border-blue-300">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-gray-700 text-xs">Total:</span>
                                        <span class="text-lg font-bold text-blue-600">
                                            @php
                                                $replacementTotal = 0;
                                                foreach($replacementItems as $item) {
                                                    $replacementTotal += floatval($item['selling_price']) * intval($item['quantity']);
                                                }
                                            @endphp
                                            ₱{{ number_format($replacementTotal, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                <span class="material-symbols-rounded text-3xl text-gray-400 mb-2">add_shopping_cart</span>
                                <p class="text-gray-600 font-medium text-sm">No items added</p>
                                <p class="text-xs text-gray-500 mt-1">Scan barcodes to add</p>
                            </div>
                            @endif
                        </div>
                        @else
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                            <span class="material-symbols-rounded text-3xl text-gray-400 mb-2">visibility_off</span>
                            <p class="text-gray-600 font-medium text-sm">Section Hidden</p>
                            <p class="text-xs text-gray-500 mt-1">Click "Show" to add items</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
</div>