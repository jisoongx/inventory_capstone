<div class="w-full h-full bg-white">
    <!-- Header -->
    <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">Return Item Process</h1>
                <p class="text-orange-100 text-sm">
                    Receipt #{{ str_pad($receiptId, 6, '0', STR_PAD_LEFT) }} • 
                    {{ \Carbon\Carbon::parse($receiptDetails->receipt_date)->format('M d, Y h:i A') }}
                </p>
            </div>
            <a href="{{ route('reports.sales_performance') }}" 
                class="bg-white text-orange-600 px-4 py-2 rounded-lg font-semibold hover:bg-orange-50 transition flex items-center gap-2">
                <span class="material-symbols-rounded text-sm">arrow_back</span>
                Back to Reports
            </a>
        </div>
    </div>

    <div class="p-6">
        <!-- Success/Error Messages -->
        @if (session()->has('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <span class="material-symbols-rounded">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <span class="material-symbols-rounded">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Store Info Card -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="font-bold text-gray-900 text-lg mb-2">{{ $store_info->store_name }}</h3>
            <p class="text-sm text-gray-600">{{ $store_info->store_address }}</p>
        </div>

        <!-- Tabs -->
        <div x-data="{ activeTab: 'returnable' }" class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-4">
                    <button @click="activeTab = 'returnable'"
                        :class="activeTab === 'returnable' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm">
                        Returnable Items
                        <span class="ml-2 bg-orange-100 text-orange-600 py-0.5 px-2 rounded-full text-xs font-bold">
                            {{ $returnableItems->count() }}
                        </span>
                    </button>
                    <button @click="activeTab = 'history'"
                        :class="activeTab === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm">
                        Return History
                        <span class="ml-2 bg-blue-100 text-blue-600 py-0.5 px-2 rounded-full text-xs font-bold">
                            {{ $returnHistoryData->count() }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Returnable Items Tab -->
            <div x-show="activeTab === 'returnable'" class="mt-6">
                @if($returnableItems->isEmpty())
                    <div class="text-center py-16 bg-gray-50 rounded-lg">
                        <span class="material-symbols-rounded text-6xl text-gray-300 mb-4">inventory_2</span>
                        <p class="text-gray-600 font-medium">No Returnable Items</p>
                        <p class="text-sm text-gray-400 mt-1">All items have been fully returned</p>
                    </div>
                @else
                    <div class="grid gap-4">
                        @foreach($returnableItems as $item)
                            <div class="border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-900 text-lg mb-2">{{ $item->product_name }}</h4>
                                        
                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <p class="text-xs text-gray-500">Original Quantity</p>
                                                <p class="text-sm font-bold text-gray-900">{{ $item->item_quantity }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Already Returned</p>
                                                <p class="text-sm font-bold text-orange-600">{{ $item->already_returned }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Returnable Quantity</p>
                                                <p class="text-sm font-bold text-green-600">{{ $item->returnable_quantity }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Unit Price</p>
                                                <p class="text-sm font-bold text-gray-900">₱{{ number_format($item->selling_price, 2) }}</p>
                                            </div>
                                        </div>

                                        @if($item->item_discount_value > 0)
                                            <div class="text-xs text-orange-600 mb-2">
                                                <span class="material-symbols-rounded text-xs align-middle">local_offer</span>
                                                Item Discount: 
                                                @if($item->item_discount_type === 'percent')
                                                    {{ $item->item_discount_value }}%
                                                @else
                                                    ₱{{ number_format($item->item_discount_value, 2) }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <button wire:click="openReturnModal({{ $item->item_id }})"
                                        class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center gap-2 ml-4">
                                        <span class="material-symbols-rounded">undo</span>
                                        Process Return
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Return History Tab -->
            <div x-show="activeTab === 'history'" class="mt-6">
                @if($returnHistoryData->isEmpty())
                    <div class="text-center py-16 bg-gray-50 rounded-lg">
                        <span class="material-symbols-rounded text-6xl text-gray-300 mb-4">history</span>
                        <p class="text-gray-600 font-medium">No Return History</p>
                        <p class="text-sm text-gray-400 mt-1">No items have been returned yet</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($returnHistoryData as $return)
                            <div class="border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-900 text-lg mb-2 flex items-center gap-2">
                                            {{ $return->product_name }}
                                            @if($return->damaged_id)
                                                <span class="bg-red-100 text-red-700 text-xs px-3 py-1 rounded-full font-medium">
                                                    Damaged: {{ $return->damaged_type }}
                                                </span>
                                            @else
                                                <span class="bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full font-medium">
                                                    Restocked
                                                </span>
                                            @endif
                                        </h4>
                                        
                                        <div class="grid grid-cols-3 gap-4">
                                            <div>
                                                <p class="text-xs text-gray-500">Quantity Returned</p>
                                                <p class="text-sm font-bold text-gray-900">{{ $return->return_quantity }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Refund Amount</p>
                                                <p class="text-sm font-bold text-blue-600">₱{{ number_format($return->refund_amount, 2) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Return Date</p>
                                                <p class="text-sm font-bold text-gray-900">
                                                    {{ \Carbon\Carbon::parse($return->return_date)->format('M d, Y h:i A') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                    <p class="text-xs font-semibold text-gray-700 mb-2">Return Reason:</p>
                                    <p class="text-sm text-gray-900">{{ $return->return_reason }}</p>
                                </div>

                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <span class="material-symbols-rounded text-sm">person</span>
                                    <span>Processed by: {{ $return->processed_by_staff ?: $return->processed_by_owner }}</span>
                                    <span class="ml-auto text-gray-400">ID: #{{ $return->return_id }}</span>
                                </div>
                            </div>
                        @endforeach

                        <!-- Summary -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
    <h4 class="font-bold text-gray-900 mb-4">Summary</h4>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-600">Total Return Transactions</p>
            <p class="text-2xl font-bold text-blue-600">{{ $returnHistoryData->count() }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Total Items Returned</p>
            <p class="text-2xl font-bold text-blue-600">{{ $returnHistoryData->sum('return_quantity') }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Total Refund Amount</p>
            <p class="text-2xl font-bold text-blue-600">₱{{ number_format($returnHistoryData->sum('refund_amount'), 2) }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Damaged Items</p>
            <p class="text-2xl font-bold text-red-600">{{ $returnHistoryData->whereNotNull('damaged_id')->sum('return_quantity') }}</p>
        </div>
    </div>
</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Return Modal -->
    @if($showReturnModal && $selectedItemForReturn)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg w-full max-w-lg mx-auto max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white p-4 rounded-t-lg sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold">Process Return</h3>
                    <button wire:click="closeReturnModal" class="text-white hover:text-gray-200">
                        <span class="material-symbols-rounded text-2xl">close</span>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- Product Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-rounded text-orange-600 text-3xl">inventory_2</span>
                        <div>
                            <h4 class="font-bold text-gray-900">{{ $selectedItemForReturn->product_name }}</h4>
                            <p class="text-sm text-gray-600">
                                Original Quantity: {{ $selectedItemForReturn->item_quantity }}
                                @if($selectedItemForReturn->already_returned > 0)
                                    <span class="text-orange-600 ml-2">
                                        ({{ $selectedItemForReturn->already_returned }} already returned)
                                    </span>
                                @endif
                            </p>
                            <p class="text-sm text-gray-600">
                                Unit Price: ₱{{ number_format($selectedItemForReturn->selling_price, 2) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Return Form -->
                <form wire:submit.prevent="submitReturn" class="space-y-4">
                    <!-- Return Quantity with Live Validation -->
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
                        
                        <!-- Warning Message -->
                        <div x-show="showWarning" 
                             x-transition
                             class="mt-2 bg-red-50 border border-red-300 rounded-lg p-3 flex items-start gap-2">
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
                        <p class="text-xs text-gray-500 mt-1">
                            Maximum returnable: <span class="font-bold">{{ $maxReturnQuantity }}</span>
                        </p>
                    </div>

                    <!-- Is Damaged? -->
                    <div>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" 
                                wire:model.live="isDamaged"
                                class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                            <span class="text-sm font-semibold text-gray-700">Item is damaged/defective</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-7">
                            Check this if the item cannot be restocked
                        </p>
                    </div>

                    <!-- Damage Type -->
                    @if($isDamaged)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <label class="block text-sm font-semibold text-red-700 mb-2">
                            Damage Type <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="damageType"
                            class="w-full border border-red-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500">
                            <option value="">Select damage type</option>
                            <option value="Physical">Physical Damage</option>
                            <option value="Expired">Expired</option>
                            <option value="Defective">Defective/Malfunction</option>
                            <option value="Contaminated">Contaminated</option>
                            <option value="Other">Other</option>
                        </select>
                        @error('damageType')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    <!-- Return Reason -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Return Reason <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <button type="button"
                                wire:click="$set('returnReason', 'Wrong product ordered')"
                                class="text-xs py-2 px-3 rounded border border-gray-300 hover:bg-gray-50 text-gray-700">
                                Wrong Product
                            </button>
                            <button type="button"
                                wire:click="$set('returnReason', 'Product is expired.')"
                                class="text-xs py-2 px-3 rounded border border-gray-300 hover:bg-gray-50 text-gray-700">
                                Expired
                            </button>
                            <button type="button"
                                wire:click="$set('returnReason', 'Product defective/damaged')"
                                class="text-xs py-2 px-3 rounded border border-gray-300 hover:bg-gray-50 text-gray-700">
                                Defective
                            </button>
                            <button type="button"
                                wire:click="$set('returnReason', 'Duplicate purchase')"
                                class="text-xs py-2 px-3 rounded border border-gray-300 hover:bg-gray-50 text-gray-700">
                                Duplicate
                            </button>
                        </div>
                        <textarea 
                            wire:model="returnReason"
                            rows="3"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"
                            placeholder="Describe the reason for return..."></textarea>
                        @error('returnReason')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Refund Calculation -->
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

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4 border-t">
                        <button type="button"
                            wire:click="closeReturnModal"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 px-4 rounded-lg font-semibold transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-3 px-4 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                            <span class="material-symbols-rounded text-sm">check_circle</span>
                            Process Return
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>