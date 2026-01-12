@extends('dashboards.owner.owner')
@section('content')
<div class="px-4 mb-2">
    @livewire('expiration-container')
</div>

<!-- Payment Processing Container -->
<div class="px-4">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('store_start_transaction') }}" class="p-2 hover:bg-red-800 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold">Process Payment</h2>
                    <p class="text-sm text-red-100">Receipt No.:  <span id="headerReceiptNo">{{ $receipt_no ?? '0' }}</span></p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-red-100">Cashier: {{ $user_firstname ?? 'User' }}</p>
                <p id="headerDateTime" class="text-xs text-red-100"></p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-6">
            
            <!-- LEFT SECTION:  Cart Items -->
            <div class="flex flex-col bg-gray-50 rounded-lg shadow-md overflow-hidden h-fit">
                <div class="bg-white p-4 border-b">
                    <h3 class="text-lg font-bold text-gray-800">Order Summary</h3>
                    <p class="text-sm text-gray-600">Review your items</p>
                </div>
                
                <!-- Cart Items List -->
                <div class="overflow-y-auto p-4 space-y-3 max-h-[400px] scrollbar-custom" id="paymentCartItems">
                    <!-- Items will be populated here -->
                </div>

                <!-- Cart Subtotal -->
                <div class="bg-white p-4 border-t space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total Items:</span>
                        <span id="cartTotalItems" class="font-semibold">0</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total Quantity:</span>
                        <span id="cartTotalQuantity" class="font-semibold">0</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold pt-2 border-t">
                        <span>Subtotal:</span>
                        <span id="cartSubtotal" class="text-red-600">₱0.00</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT SECTION: Payment Details -->
            <div class="flex flex-col bg-white rounded-lg shadow-md overflow-hidden h-fit">
                <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-4">
                    <h3 class="text-lg font-bold">Payment Calculator</h3>
                    <p class="text-sm text-green-100">Enter payment details</p>
                </div>

                <div class="p-6 space-y-6">
                    
                    <!-- Quick Summary -->
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-4 rounded-xl border-2 border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-2xl font-bold text-gray-900">Total Amount: </span>
                            <span id="quickTotal" class="text-3xl font-bold text-red-600">₱0.00</span>
                        </div>
                    </div>

                    <!-- Amount Paid Input - Prominent -->
                    <div class="space-y-3">
                        <label class="block text-lg font-bold text-gray-700">Enter Amount Received</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-2xl font-bold text-gray-400">₱</span>
                            <input type="number" id="amountPaidInput" min="0" step="0.01" 
                                class="w-full pl-12 pr-4 py-4 text-2xl font-bold border-3 border-gray-300 rounded-xl focus:ring-4 focus:ring-green-500 focus:border-green-500 bg-white"
                                placeholder="0.00"
                                autofocus>
                        </div>
                        
                        <!-- Quick Amount Buttons -->
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button" class="quick-amount-btn" data-amount="100">₱100</button>
                            <button type="button" class="quick-amount-btn" data-amount="200">₱200</button>
                            <button type="button" class="quick-amount-btn" data-amount="500">₱500</button>
                            <button type="button" class="quick-amount-btn" data-amount="1000">₱1000</button>
                        </div>

                        <!-- Exact Amount Button -->
                        <button type="button" id="exactAmountBtn" class="w-full py-2 px-4 bg-blue-100 text-blue-700 rounded-lg font-semibold hover:bg-blue-200 transition">
                            Use Exact Amount
                        </button>
                    </div>

                    <!-- Change Display - Inline -->
                    <div id="changeDisplay" class="hidden">
                        <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 py-2.5 rounded-lg shadow-md flex items-center justify-between">
                            <p class="text-green-100 text-xs font-medium">CHANGE TO RETURN: </p>
                            <p id="changeAmount" class="text-white text-2xl font-bold ml-3">₱0.00</p>
                        </div>
                    </div>

                    <!-- Insufficient Amount Warning -->
                    <div id="insufficientWarning" class="hidden bg-red-50 border-2 border-red-300 p-4 rounded-xl">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-red-800 font-bold text-sm">Insufficient Amount</p>
                                <p class="text-red-600 text-xs">Still needed: <span id="amountShortage" class="font-bold">₱0.00</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Calculation Breakdown -->
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-4 rounded-xl border-2 border-gray-200 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700">Subtotal:</span>
                            <span id="calcSubtotal" class="font-semibold text-gray-900">₱0.00</span>
                        </div>
                        <div id="calcPromoDiscountsRow" class="flex justify-between text-sm hidden">
                            <span class="text-gray-700">Item Discounts:</span>
                            <span id="calcPromoDiscounts" class="font-semibold text-orange-600">₱0.00</span>
                        </div>
                        <div id="calcItemDiscountsRow" class="flex justify-between text-sm hidden">
                            <span class="text-gray-700">Item Discounts:</span>
                            <span id="calcItemDiscounts" class="font-semibold text-orange-600">₱0.00</span>
                        </div>
                        <div id="calcReceiptDiscountRow" class="flex justify-between text-sm hidden">
                            <span class="text-gray-700">Receipt Discount:</span>
                            <span id="calcReceiptDiscount" class="font-semibold text-orange-600">₱0.00</span>
                        </div>
                        <!-- ✅ VAT Breakdown - Always visible -->
                        <div class="border-t pt-2 mt-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-700">VAT-Inclusive: </span>
                                <span id="calcVATInclusive" class="font-semibold text-blue-600">₱0.00</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-700">VAT-Exempt:</span>
                                <span id="calcVATExempt" class="font-semibold text-gray-600">₱0.00</span>
                            </div>
                        </div>
                        <div class="flex justify-between text-base font-bold pt-2 border-t-2 border-gray-300">
                            <span class="text-gray-900">Total Amount:</span>
                            <span id="calcTotal" class="text-red-600">₱0.00</span>
                        </div>
                    </div>

                    <!-- Receipt Discount Only (Collapsible) -->
                    <div class="border-t pt-4">
                        <button id="toggleDiscounts" class="w-full flex items-center justify-between p-3 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            <span class="font-semibold text-gray-700">Add Receipt Discount</span>
                            <svg id="discountChevron" class="w-5 h-5 text-gray-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div id="discountsSection" class="hidden space-y-4 mt-4 p-4 bg-gray-50 rounded-lg">
                            <!-- Receipt Discount -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Receipt Discount</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <select id="receiptDiscountType" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                        <option value="percent">Percentage (%)</option>
                                        <option value="amount">Fixed (₱)</option>
                                    </select>
                                    <input type="number" id="receiptDiscountValue" min="0" step="0.01" value="0" 
                                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="p-6 border-t bg-gray-50 flex gap-3">
                    <a href="{{ route('store_start_transaction') }}" class="flex-1 px-6 py-4 border-2 border-gray-300 text-gray-700 rounded-xl font-bold hover:bg-gray-100 transition text-center">
                        Back
                    </a>
                    <button id="completePayment" class="flex-1 px-6 py-4 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl font-bold text-lg hover:from-green-700 hover:to-green-800 transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Receipt Display Modal -->
<div id="receiptModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] hidden p-4">
    <div class="bg-white rounded-lg w-full max-w-md mx-auto h-full max-h-[90vh] flex flex-col">
        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-4 rounded-t-lg flex-shrink-0">
            <div class="text-center">
                <h3 class="text-xl font-bold mb-2">Payment Successful!</h3>
                <p class="text-sm text-red-100">Transaction completed successfully</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto min-h-0 scrollbar-custom">
            <div class="p-6">
                <div class="text-center mb-6 pb-4 border-b-2 border-gray-200">
                    <h2 id="storeNameReceipt" class="text-xl font-bold text-gray-800">{{ $store_info->store_name ??  'Store Name' }}</h2>
                    <p class="text-sm text-gray-600">{{ $store_info->store_address }}</p>
                    @if(! empty($store_info->tin_number))
                        <p class="text-xs text-gray-500 mt-1">TIN: {{ $store_info->tin_number }}</p>
                    @endif
                </div>

                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Receipt No. :</span>
                        <span id="receiptNumber" class="text-sm font-bold text-gray-900">{{ $receipt_no ?? '0' }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Date & Time:</span>
                        <span id="receiptTransactionDate" class="text-sm text-gray-900"></span>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm font-medium text-gray-700">Cashier: </span>
                        <span class="text-sm text-gray-900">{{ $user_firstname ??  'User' }}</span>
                    </div>
                </div>

                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800 mb-3 pb-2 border-b border-gray-300">Items Purchased</h4>
                    <div id="receiptItemsList" class="space-y-2"></div>
                </div>

                <div class="border-t-2 border-gray-300 pt-4 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Total Quantity:</span>
                        <span id="receiptTotalItems" class="text-sm text-gray-900">0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                        <span id="receiptSubtotal" class="text-sm text-gray-900">₱0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Item Discounts:</span>
                        <span id="receiptItemDiscounts" class="text-sm text-orange-600">₱0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Receipt Discount:</span>
                        <span id="receiptReceiptDiscount" class="text-sm text-orange-600">₱0.00</span>
                    </div>
                    <div class="border-t pt-2 mt-2 space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">VAT-Inclusive:</span>
                            <span id="receiptVatInclusive" class="text-sm text-blue-600">₱0.00</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">VAT-Exempt:</span>
                            <span id="receiptVatExempt" class="text-sm text-gray-600">₱0.00</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center border-t pt-2">
                        <span class="text-lg font-bold text-gray-900">Total Amount:</span>
                        <span id="receiptTotalAmount" class="text-lg font-bold text-red-600">₱0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Amount Paid:</span>
                        <span id="receiptAmountPaid" class="text-sm text-gray-900">₱0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Change:</span>
                        <span id="receiptChange" class="text-sm text-green-600">₱0.00</span>
                    </div>
                </div>

                <div class="text-center mt-6 pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-500">Thank you for your purchase! </p>
                    <p class="text-xs text-gray-500">Please keep this receipt for your records.</p>
                </div>
            </div>
        </div>

        <div class="p-4 border-t bg-gray-50 flex gap-3 rounded-b-lg flex-shrink-0">
            <button id="printReceiptBtn" class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-bold hover:bg-blue-700 transition-colors">
                <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print
            </button>
            <button id="finishTransactionBtn" class="flex-1 bg-green-600 text-white py-3 px-4 rounded-lg font-bold hover:bg-green-700 transition-colors">
                <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                New Transaction
            </button>
        </div>
    </div>
</div>

<!-- Modal container -->
<div id="bundleModal" class="fixed inset-0 hidden z-50 flex items-center justify-center">
    <!-- Background overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="paymentProcessor.cancelBundle()"></div>

    <!-- Modal content -->
    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-md p-6 z-10">
        <h2 class="text-lg font-semibold mb-4">Eligible Bundle Detected</h2>

        <!-- Bundle list container -->
        <div id="bundleList" class="space-y-2"></div>

        <div class="flex justify-end gap-2 mt-4">
            <button id="cancelBundleBtn" class="px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
            <button id="applyBundleBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">OK</button>
        </div>
    </div>
</div>



<style>
    .scrollbar-custom::-webkit-scrollbar {
        width: 6px;
    }

    .scrollbar-custom::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .scrollbar-custom::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .scrollbar-custom::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .payment-cart-item {
        background: white;
        border:  1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        transition: all 0.2s;
    }

    .payment-cart-item:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .quick-amount-btn {
        padding: 12px;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-weight: 600;
        color: #374151;
        transition: all 0.2s;
    }

    .quick-amount-btn:hover {
        background:  linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border-color: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    /* Inline discount input styling */
    .item-discount-inline {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
        margin-top:  6px;
    }

    .item-discount-label {
        font-size: 0.65rem;
        color: #6b7280;
        font-weight: 500;
        white-space: nowrap;
        margin-right: 4px;
    }

    .item-discount-inline select,
    .item-discount-inline input {
        font-size: 0.65rem;
        padding: 4px 6px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        transition: all 0.2s;
        height: 26px;
    }

    .item-discount-inline select {
        flex:  0 0 70px;
        min-width: 70px;
    }

    .item-discount-inline input {
        flex: 0 0 80px;
        min-width: 80px;
    }

    .item-discount-inline select:focus,
    .item-discount-inline input:focus {
        outline: none;
        border-color: #f97316;
        box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.1);
    }

    .item-discount-inline select:disabled,
    .item-discount-inline input:disabled {
        background-color: #f3f4f6;
        cursor: not-allowed;
        opacity: 0.6;
    }
</style>

<script>
class PaymentProcessor {
    constructor() {
        this.cartItems = [];
        this.itemDiscounts = {};
        this.receiptDiscount = { type: 'percent', value: 0 };
        this.vatRate = 12;
        this.amountPaid = 0;
        this.totalAmount = 0;
        this.discountsExpanded = false;
        this.storageKey = 'payment_processor_state';

        this.appliedBundles = new Set(); // Track which products have bundles applied
        this.eligibleBundles = null;
        

        
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateDateTime();
        setInterval(() => this.updateDateTime(), 1000);
        this.loadCartItems();
    }

    saveState() {
        const state = {
            amountPaid: document.getElementById('amountPaidInput')?.value || '0',
            receiptDiscountType: this.receiptDiscount.type,
            receiptDiscountValue:  this.receiptDiscount.value,
            itemDiscounts: this.itemDiscounts,
            discountsExpanded: this.discountsExpanded,
            timestamp: Date.now()
        };
        localStorage.setItem(this.storageKey, JSON.stringify(state));
    }

    loadState() {
        try {
            const savedState = localStorage.getItem(this.storageKey);
            if (!savedState) return false;

            const state = JSON.parse(savedState);
            
            const oneHour = 60 * 60 * 1000;
            if (Date.now() - state.timestamp > oneHour) {
                localStorage.removeItem(this.storageKey);
                return false;
            }

            if (state.amountPaid) {
                const input = document.getElementById('amountPaidInput');
                if (input) input.value = state.amountPaid;
            }

            if (state.receiptDiscountType) {
                this.receiptDiscount.type = state.receiptDiscountType;
                const typeSelect = document.getElementById('receiptDiscountType');
                if (typeSelect) typeSelect.value = state.receiptDiscountType;
            }
            
            if (state.receiptDiscountValue) {
                this.receiptDiscount.value = state.receiptDiscountValue;
                const valueInput = document.getElementById('receiptDiscountValue');
                if (valueInput) valueInput.value = state.receiptDiscountValue;
            }

            if (state.itemDiscounts) {
                this.itemDiscounts = state.itemDiscounts;
            }

            if (state.discountsExpanded) {
                this.discountsExpanded = state.discountsExpanded;
                const section = document.getElementById('discountsSection');
                const chevron = document.getElementById('discountChevron');
                if (section && chevron) {
                    section.classList.remove('hidden');
                    chevron.style.transform = 'rotate(180deg)';
                }
            }

            return true;
        } catch (error) {
            console.error('Error loading saved state:', error);
            return false;
        }
    }

    clearState() {
        localStorage.removeItem(this.storageKey);
    }

    bindEvents() {
        document.querySelectorAll('.quick-amount-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const amount = parseFloat(e.target.dataset.amount);
                document.getElementById('amountPaidInput').value = amount;
                this.calculateChange();
                this.saveState();
            });
        });

        document.getElementById('exactAmountBtn')?.addEventListener('click', () => {
            document.getElementById('amountPaidInput').value = this.totalAmount.toFixed(2);
            this.calculateChange();
            this.saveState();
        });

        document.getElementById('toggleDiscounts')?.addEventListener('click', () => {
            this.discountsExpanded = !this.discountsExpanded;
            const section = document.getElementById('discountsSection');
            const chevron = document.getElementById('discountChevron');
            
            if (this.discountsExpanded) {
                section.classList.remove('hidden');
                chevron.style.transform = 'rotate(180deg)';
            } else {
                section.classList.add('hidden');
                chevron.style.transform = 'rotate(0deg)';
            }
            this.saveState();
        });

        document.getElementById('receiptDiscountType')?.addEventListener('change', (e) => {
            this.receiptDiscount.type = e.target.value;
            this.calculateTotals();
            this.saveState();
        });

        document.getElementById('receiptDiscountValue')?.addEventListener('input', (e) => {
            this.receiptDiscount.value = parseFloat(e.target.value) || 0;
            this.updateDiscountFieldStates();
            this.calculateTotals();
            this.saveState();
        });

        document.getElementById('amountPaidInput')?.addEventListener('input', () => {
            this.calculateChange();
            this.saveState();
        });

        document.getElementById('completePayment')?.addEventListener('click', () => {
            this.processPayment();
        });

        document.getElementById('printReceiptBtn')?.addEventListener('click', () => {
            this.printReceipt();
        });

        document.getElementById('finishTransactionBtn')?.addEventListener('click', () => {
            this.clearState();
            window.location.href = '{{ route("store_start_transaction") }}';
        });

        const backButton = document.querySelector('a[href="{{ route("store_start_transaction") }}"]');
        if (backButton) {
            backButton.addEventListener('click', () => {
                this.clearState();
            });
        }
    }

    // ✅ Helper:  Check if any item has a discount
    hasAnyItemDiscounts() {
        return Object.values(this.itemDiscounts).some(discount => discount.value > 0);
    }

    // ✅ Update field states based on mutual exclusivity
    updateDiscountFieldStates() {
        const hasItemDiscounts = this.hasAnyItemDiscounts();
        const hasReceiptDiscount = this.receiptDiscount.value > 0;
        
        const receiptDiscountType = document.getElementById('receiptDiscountType');
        const receiptDiscountValue = document.getElementById('receiptDiscountValue');
        const receiptDiscountContainer = receiptDiscountType?.closest('.space-y-2');
        
        // Disable/enable receipt discount fields
        if (hasItemDiscounts) {
            if (receiptDiscountType) {
                receiptDiscountType.disabled = true;
                receiptDiscountType.classList.add('bg-gray-200', 'cursor-not-allowed', 'opacity-50');
            }
            if (receiptDiscountValue) {
                receiptDiscountValue.disabled = true;
                receiptDiscountValue.classList.add('bg-gray-200', 'cursor-not-allowed', 'opacity-50');
                receiptDiscountValue.value = 0;
                this.receiptDiscount.value = 0;
            }
            
            // Show info message
            if (receiptDiscountContainer && ! document.getElementById('receiptDiscountWarning')) {
                const warning = document.createElement('div');
                warning.id = 'receiptDiscountWarning';
                warning.className = 'text-xs text-blue-600 bg-blue-50 border border-blue-200 rounded px-2 py-1.5 flex items-start gap-2';
                warning.innerHTML = `
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span>Receipt discount disabled. Clear item discounts to enable.</span>
                `;
                receiptDiscountContainer.appendChild(warning);
            }
        } else {
            if (receiptDiscountType) {
                receiptDiscountType.disabled = false;
                receiptDiscountType.classList.remove('bg-gray-200', 'cursor-not-allowed', 'opacity-50');
            }
            if (receiptDiscountValue) {
                receiptDiscountValue.disabled = false;
                receiptDiscountValue.classList.remove('bg-gray-200', 'cursor-not-allowed', 'opacity-50');
            }
            
            // Remove info message
            const warning = document.getElementById('receiptDiscountWarning');
            if (warning) warning.remove();
        }
        
        // Disable/enable item discount fields
        document.querySelectorAll('.item-discount-type').forEach(select => {
            if (hasReceiptDiscount) {
                select.disabled = true;
                select.classList.add('bg-gray-200', 'cursor-not-allowed', 'opacity-50');
            } else {
                select.disabled = false;
                select.classList.remove('bg-gray-200', 'cursor-not-allowed', 'opacity-50');
            }
        });
        
        document.querySelectorAll('.item-discount-value').forEach(input => {
            if (hasReceiptDiscount) {
                input.disabled = true;
                input.classList.add('bg-gray-200', 'cursor-not-allowed', 'opacity-50');
                input.value = 0;
                const productCode = input.dataset.productCode;
                if (this.itemDiscounts[productCode]) {
                    this.itemDiscounts[productCode].value = 0;
                }
            } else {
                input.disabled = false;
                input.classList.remove('bg-gray-200', 'cursor-not-allowed', 'opacity-50');
            }
        });
    }

    updateDateTime() {
        const now = new Date();
        const options = {
            month: '2-digit',
            day:  '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        const formatted = now.toLocaleString('en-US', options);
        const el = document.getElementById('headerDateTime');
        if (el) el.textContent = formatted;
    }

    

    showBundleModal(eligibleBundles) {
        const modal = document.getElementById('bundleModal');
        const bundleList = document.getElementById('bundleList');

        bundleList.innerHTML = '';

        Object.entries(eligibleBundles).forEach(([bundleId, bundleItems]) => {
            if (!bundleItems.length) return;

        
            const {
                name,
                bundle_code,
                discount_percent,
                bundle_type,
                bogoType,
            } = bundleItems[0];

            const bundleTypeLabel =
                bundle_type === 'BOGO1'
                    ? 'Buy One Get One (Discounted Product)'
                    : bundle_type === 'BOGO2'
                        ? 'Buy One Get One (Free Product)'
                        : bundle_type; 

            const bogoTypeLabel = bogoType == 'P' ? 'PAID' : '';

            const wrapper = document.createElement('div');
            wrapper.classList.add(
                'border',
                'rounded',
                'p-3',
                'mb-4',
                'bg-gray-50'
            );

            // 🔹 Bundle header
            const header = document.createElement('div');
            header.classList.add('mb-2');

            header.innerHTML = `
                <div class="font-semibold text-gray-900">${bundle_code}</div>
                <div class="text-xs text-gray-500">
                    ${bundleTypeLabel} • 
                    Discount: ${discount_percent}%
                </div>
            `;

            wrapper.appendChild(header);

            // 🔹 Product list
            const ul = document.createElement('ul');
            ul.classList.add('text-sm', 'text-gray-700', 'space-y-1');

            bundleItems.forEach(item => {
                const li = document.createElement('li');

                // Determine label and styles
                let bogoLabel = '';
                let bgColor = ''; // default no background

                if (item.bogoType === 'P') {
                    bogoLabel = 'PAID';
                    bgColor = 'bg-yellow-100 text-yellow-800 px-1 rounded'; // light yellow background for PAID
                } else if (item.bogoType && item.bogoType !== 'P') {
                    bogoLabel = 'FREE';
                    bgColor = 'bg-green-100 text-green-800 px-1 rounded'; // optional green background for free
                }

                // Build inner HTML with optional span for label
                li.innerHTML = `
                    ${item.name} × ${item.required_qty} 
                    ${bogoLabel ? `<span class="${bgColor} ml-2 text-xs font-semibold">${bogoLabel}</span>` : ''}
                `;

                ul.appendChild(li);
            });

            wrapper.appendChild(ul);
            bundleList.appendChild(wrapper);
        });

        modal.classList.remove('hidden');

        document.getElementById('applyBundleBtn').onclick = () => this.applyBundle();
        document.getElementById('cancelBundleBtn').onclick = () => this.cancelBundle();
    }

    cancelBundle() {
        const modal = document.getElementById('bundleModal');
        modal.classList.add('hidden');

        this.finalizePaymentWithoutBundle();
    }
    
    async loadCartItems() {
        try {
            const response = await fetch('{{ route("get_cart_items") }}');
            const data = await response.json();

            
            if (data.success) {
                this.cartItems = data.cart_items;

                this.cartItems = this.cartItems.map(item => ({
                    ...item,
                    bundle_applied: item.bundle_applied ?? false
                }));

                
                
                if (this.cartItems.length === 0) {
                    this.showToast('Cart is empty.  Redirecting... ', 'error');
                    this.clearState();
                    setTimeout(() => {
                        window.location.href = '{{ route("store_start_transaction") }}';
                    }, 1500);
                    return;
                }
                
                this.cartItems.forEach(item => {
                    if (! this.itemDiscounts[item.product.prod_code]) {
                        this.itemDiscounts[item.product.prod_code] = {
                            type: 'percent',
                            value: 0
                        };
                    }
                });
                
                this.renderCart();
                
                const stateLoaded = this.loadState();
                
                if (stateLoaded) {
                    this.restoreItemDiscountInputs();
                }
                
                this.calculateTotals();
                this.updateDiscountFieldStates();

                if (data.requireConfirmation) {
                    this.eligibleBundles = data.eligibleBundles;
                    this.showBundleModal(this.eligibleBundles);
                    return;
                }
            }
        } catch (error) {
            console.error('Error loading cart:', error);
            // this.showToast('Error loading cart items', 'error');
        }
    }

    applyBundle() {
        const modal = document.getElementById('bundleModal');
        modal.classList.add('hidden');

        if (!this.eligibleBundles) return;

        Object.values(this.eligibleBundles).forEach(bundleItems => {
            bundleItems.forEach(bundleItem => {
                const cartItem = this.cartItems.find(
                    ci => ci.product.prod_code === bundleItem.prod_code
                );
                if (!cartItem) return;

                // Initialize appliedBundles set if missing
                if (!cartItem.bundle_applied_units) cartItem.bundle_applied_units = 0;

                // Determine how many units this rule applies
                const ruleQty = bundleItem.required_qty || 1;
                const remainingQty = cartItem.quantity - cartItem.bundle_applied_units;
                const applicableQty = Math.min(ruleQty, remainingQty);
                if (applicableQty <= 0) return;

                switch (bundleItem.bundle_type) {
                    case 'BOGO1':
                        if (!bundleItem.bogoType) {
                            const discountPercent = bundleItem.discount_percent || 0;
                            cartItem.amount -= (cartItem.product.selling_price * applicableQty) * (discountPercent / 100);
                        }
                        break;

                    case 'BOGO2':
                        if (!bundleItem.bogoType) {
                            cartItem.amount -= cartItem.product.selling_price * applicableQty;
                        }
                        break;

                    case 'MULTI-BUY':
                    case 'EXPIRY':
                    case 'MIXED':
                        const discountPercent = bundleItem.discount_percent || 0;
                        cartItem.amount -= (cartItem.product.selling_price * applicableQty) * (discountPercent / 100);
                        break;
                }

                cartItem.bundle_applied_units += applicableQty;
                cartItem.bundle_applied = true; 
            });
        });

        this.renderCart();
        this.calculateTotals();
    }


    renderCart() {
        const container = document.getElementById('paymentCartItems');
        if (!container) return;

        container.innerHTML = this.cartItems.map(item => {
            
            const vatCategory = item.product?.vat_category || 'vat_exempt';
            const vatBadge = vatCategory === 'vat_inclusive'
                ? '<span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-medium">VAT-Inc</span>'
                : '<span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-medium">VAT-Ex</span>';


            // Check if this item has a bundle applied
            const hasBundle = item.bundle_applied === true;
            const promoBadge = hasBundle
                ? '<span class="text-[9px] bg-green-100 text-green-700 px-2 py-0.5 rounded font-medium">PROMO Applied</span>'
                : '';
            const isPromoApplied = item.bundle_applied === true;

            return `
            <div class="payment-cart-item" data-product-code="${item.product.prod_code}">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-semibold text-gray-900">${item.product.name}</h4>
                            ${vatBadge} 
                        </div>
                        <p class="text-sm text-gray-600">₱${parseFloat(item.product.selling_price).toFixed(2)} × ${item.quantity}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-900 mb-2">₱${item.amount.toFixed(2)}</p>
                        
                        <div class="item-discount-inline">
                            <span class="item-discount-label">Discount:</span>
                            <select class="item-discount-type" data-product-code="${item.product.prod_code}" ${isPromoApplied ? 'disabled' : ''}>
                                <option value="percent">% Off</option>
                                <option value="amount">₱ Off</option>
                            </select>
                            <input type="number" 
                                class="item-discount-value" 
                                data-product-code="${item.product.prod_code}"
                                min="0" 
                                step="0.01" 
                                value="0" 
                                placeholder="0.00"
                                ${isPromoApplied ? 'disabled' : ''}>
                        </div>
                        ${promoBadge}
                    </div>
                </div>
            </div>
            `;
        }).join('');

        this.bindInlineDiscountEvents();

        const totalItems = this.cartItems.length;
        const totalQuantity = this.cartItems.reduce((sum, item) => sum + item.quantity, 0);
        const subtotal = this.cartItems.reduce((sum, item) => sum + item.amount, 0);

        document.getElementById('cartTotalItems').textContent = totalItems;
        document.getElementById('cartTotalQuantity').textContent = totalQuantity;
        document.getElementById('cartSubtotal').textContent = `₱${subtotal.toFixed(2)}`;
    }

    restoreItemDiscountInputs() {
        Object.keys(this.itemDiscounts).forEach(prodCode => {
            const discount = this.itemDiscounts[prodCode];
            
            const typeSelect = document.querySelector(`.item-discount-type[data-product-code="${prodCode}"]`);
            if (typeSelect) typeSelect.value = discount.type;
            
            const valueInput = document.querySelector(`.item-discount-value[data-product-code="${prodCode}"]`);
            if (valueInput) valueInput.value = discount.value;
        });
    }


    bindInlineDiscountEvents() {
        document.querySelectorAll('.item-discount-type').forEach(select => {
            select.addEventListener('change', (e) => {
                const productCode = e.target.dataset.productCode;
                this.itemDiscounts[productCode].type = e.target.value;
                this.calculateTotals();
                this.saveState();
            });
        });

        document.querySelectorAll('.item-discount-value').forEach(input => {
            input.addEventListener('input', (e) => {
                const productCode = e.target.dataset.productCode;
                this.itemDiscounts[productCode].value = parseFloat(e.target.value) || 0;
                this.updateDiscountFieldStates();
                this.calculateTotals();
                this.saveState();
            });
        });
    }

    calculateTotals() {
        let subtotal = 0;
        let totalItemDiscounts = 0;

        this.cartItems.forEach(item => {
            // Use item.amount which already includes bundle discounts
            const lineTotal = item.amount;

            subtotal += lineTotal;

            // Apply manual item discount (entered by user)
            const discount = this.itemDiscounts[item.product.prod_code] || { type: 'percent', value: 0 };
            let discountAmount = 0;

            if (discount.value > 0) {
                if (discount.type === 'percent') {
                    discountAmount = lineTotal * (discount.value / 100);
                } else {
                    discountAmount = Math.min(discount.value * item.quantity, lineTotal);
                }
                totalItemDiscounts += discountAmount;
            }
        });

        const afterItemDiscounts = subtotal - totalItemDiscounts;

        // Receipt discount
        let receiptDiscountAmount = 0;
        if (this.receiptDiscount.value > 0) {
            if (this.receiptDiscount.type === 'percent') {
                receiptDiscountAmount = afterItemDiscounts * (this.receiptDiscount.value / 100);
            } else {
                const totalQuantity = this.cartItems.reduce((sum, item) => sum + item.quantity, 0);
                receiptDiscountAmount = Math.min(this.receiptDiscount.value, afterItemDiscounts);
            }
        }

        const afterReceiptDiscount = afterItemDiscounts - receiptDiscountAmount;

        // VAT calculation
        let vatAmountInclusive = 0;
        let vatAmountExempt = 0;

        this.cartItems.forEach(item => {
            const vatCategory = item.product?.vat_category || 'vat_exempt';
            const itemShare = item.amount / subtotal * afterReceiptDiscount; // proportion after discounts
            if (vatCategory === 'vat_inclusive') {
                vatAmountInclusive += itemShare * (this.vatRate / (100 + this.vatRate));
            } else {
                vatAmountExempt += itemShare;
            }
        });

        const totalAmount = afterReceiptDiscount;

        // Update UI
        document.getElementById('calcSubtotal').textContent = `₱${subtotal.toFixed(2)}`;
        document.getElementById('calcItemDiscountsRow').classList.toggle('hidden', totalItemDiscounts === 0);
        document.getElementById('calcItemDiscounts').textContent = `₱${totalItemDiscounts.toFixed(2)}`;
        document.getElementById('calcReceiptDiscountRow').classList.toggle('hidden', receiptDiscountAmount === 0);
        document.getElementById('calcReceiptDiscount').textContent = `₱${receiptDiscountAmount.toFixed(2)}`;
        document.getElementById('calcVATInclusive').textContent = `₱${vatAmountInclusive.toFixed(2)}`;
        document.getElementById('calcVATExempt').textContent = `₱${vatAmountExempt.toFixed(2)}`;
        document.getElementById('calcTotal').textContent = `₱${totalAmount.toFixed(2)}`;
        document.getElementById('quickTotal').textContent = `₱${totalAmount.toFixed(2)}`;

        this.totalAmount = totalAmount;
        this.calculateChange();
    }

    calculateChange() {
        const amountPaidInput = document.getElementById('amountPaidInput');
        if (!amountPaidInput) return;

        this.amountPaid = parseFloat(amountPaidInput.value) || 0;
        const change = this.amountPaid - this.totalAmount;

        const insufficientWarning = document.getElementById('insufficientWarning');
        const changeDisplay = document.getElementById('changeDisplay');
        const completeBtn = document.getElementById('completePayment');

        // ✅ FIX: Use a small threshold to handle floating-point precision issues
        const threshold = 0.01; // 1 cent tolerance
        
        if (this.amountPaid > threshold && this.amountPaid < (this.totalAmount - threshold)) {
            // Truly insufficient - more than 1 cent short
            const shortage = this.totalAmount - this.amountPaid;
            document.getElementById('amountShortage').textContent = `₱${shortage.toFixed(2)}`;
            insufficientWarning.classList.remove('hidden');
            changeDisplay.classList.add('hidden');
            completeBtn.disabled = true;
        } else if (this.amountPaid >= (this.totalAmount - threshold) && this.amountPaid > 0) {
            // Sufficient amount (including exact amount with rounding differences)
            document.getElementById('changeAmount').textContent = `₱${Math.max(0, change).toFixed(2)}`;
            changeDisplay.classList.remove('hidden');
            insufficientWarning.classList.add('hidden');
            completeBtn.disabled = false;
        } else {
            // No amount entered yet
            changeDisplay.classList.add('hidden');
            insufficientWarning.classList.add('hidden');
            completeBtn.disabled = true;
        }
    }

    async processPayment() {

        const paid  = Math.round(this.amountPaid * 100);
        const total = Math.round(this.totalAmount * 100);

        if (paid < total) {
            this.showToast(
                `Insufficient amount paid: ₱${this.amountPaid.toFixed(2)} / ₱${this.totalAmount.toFixed(2)}`,
                'error'
            );
            return;
        }

        const itemDiscounts = this.itemDiscounts || {};
        const hasItemDiscounts = Object.values(itemDiscounts)
            .some(d => (d?.value || 0) > 0);

        const receiptDiscount = this.receiptDiscount || { value: 0, type: null };
        const hasReceiptDiscount = receiptDiscount.value > 0;

        if (hasItemDiscounts && hasReceiptDiscount) {
            this.showToast(
                '❌ Cannot apply both item and receipt discounts simultaneously',
                'error'
            );
            return;
        }

        
            console.log('itemDiscounts:', this.itemDiscounts);
            console.log('receiptDiscount:', this.receiptDiscount);

        const completeBtn = document.getElementById('completePayment');
        completeBtn.disabled = true;
        completeBtn.textContent = 'Processing...';

        try {
            const paymentData = {
                payment_method: 'cash',
                amount_paid: this.amountPaid,
                receipt_discount_type: this.receiptDiscount.type,
                receipt_discount_value: this.receiptDiscount.value,
                vat_enabled: true,
                vat_rate:  this.vatRate,
                item_discounts: this.itemDiscounts
            };

            const response = await fetch('{{ route("process_payment") }}', {
                method:  'POST',
                headers:  {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(paymentData)
            });

            const data = await response.json();

            if (data.success) {
                this.clearState();
                this.showToast('Payment completed successfully!', 'success');
                this.showReceiptModal(data);
            } else {
                this.showToast(data.message || 'Payment failed', 'error');
                completeBtn.disabled = false;
                completeBtn.textContent = 'Complete Payment';
            }
        } catch (error) {

            console.error('Error processing payment:', error);
            this.showToast('Error processing payment:  ' + error.message, 'error');
            completeBtn.disabled = false;
            completeBtn.textContent = 'Complete Payment';
        }
    }

    showReceiptModal(paymentData) {
        document.getElementById('receiptNumber').textContent = paymentData.receipt_id || '{{ $receipt_no ??  "0" }}';
        document.getElementById('receiptTotalItems').textContent = paymentData.total_quantity;
        
        const subtotal = paymentData.subtotal || paymentData.receipt_items.reduce((sum, item) => {
            return sum + (item.product.selling_price * item.quantity);
        }, 0);
        
        document.getElementById('receiptSubtotal').textContent = `₱${subtotal.toFixed(2)}`;
        document.getElementById('receiptTotalAmount').textContent = `₱${paymentData.total_amount.toFixed(2)}`;
        document.getElementById('receiptAmountPaid').textContent = `₱${paymentData.amount_paid.toFixed(2)}`;
        document.getElementById('receiptChange').textContent = `₱${paymentData.change.toFixed(2)}`;

        const itemDiscountsAmount = paymentData.total_item_discounts ??  0;
        const receiptDiscountAmount = paymentData.receipt_discount_amount ?? 0;
        const vatAmountInclusive = paymentData.vat_amount_inclusive ??  0;
        const vatAmountExempt = paymentData.vat_amount_exempt ??  0;

        document.getElementById('receiptItemDiscounts').textContent = `₱${parseFloat(itemDiscountsAmount).toFixed(2)}`;
        document.getElementById('receiptReceiptDiscount').textContent = `₱${parseFloat(receiptDiscountAmount).toFixed(2)}`;
        
        document.getElementById('receiptVatInclusive').textContent = `₱${parseFloat(vatAmountInclusive).toFixed(2)}`;
        document.getElementById('receiptVatExempt').textContent = `₱${parseFloat(vatAmountExempt).toFixed(2)}`;

        const now = new Date();
        const options = {
            year: 'numeric',
            month: '2-digit',
            day:  '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        document.getElementById('receiptTransactionDate').textContent = now.toLocaleString('en-US', options);

        // --- Apply eligible bundles to receipt_items ---
        Object.values(this.eligibleBundles || {}).forEach(bundleItems => {
            bundleItems.forEach(bundleItem => {
                const cartItem = paymentData.receipt_items.find(
                    ci => ci.product.prod_code === bundleItem.prod_code
                );
                if (!cartItem) return;

                if (!cartItem.bundle_applied_units) cartItem.bundle_applied_units = 0;
                if (!cartItem.promo_lines) cartItem.promo_lines = [];

                const ruleQty = bundleItem.required_qty || 1;
                const remainingQty = cartItem.quantity - cartItem.bundle_applied_units;
                const applicableQty = Math.min(ruleQty, remainingQty);
                if (applicableQty <= 0) return;

                let promoAmount = 0;
                let promoLabel = '';

                switch (bundleItem.bundle_type) {
                    case 'BOGO1':
                        if (!bundleItem.bogoType) {
                            const discountPercent = bundleItem.discount_percent || 0;
                            promoAmount = (cartItem.product.selling_price * applicableQty) * (discountPercent / 100);
                            promoLabel = 'BOGO (Discount)';
                        }
                        break;
                    case 'BOGO2':
                        if (!bundleItem.bogoType) {
                            promoAmount = cartItem.product.selling_price * applicableQty;
                            promoLabel = 'BOGO (Free)';
                        }
                        break;
                    case 'MULTI-BUY':
                    case 'EXPIRY':
                    case 'MIXED':
                        const discountPercent = bundleItem.discount_percent || 0;
                        promoAmount = (cartItem.product.selling_price * applicableQty) * (discountPercent / 100);
                        promoLabel = 'Bundle Discount';
                        break;
                }

                if (promoAmount > 0) {
                    // Track promo lines for display
                    cartItem.promo_lines.push({
                        label: promoLabel,
                        quantity: applicableQty,
                        amount: -promoAmount
                    });

                    // Update total amount for this item
                    cartItem.amount = (cartItem.amount ?? cartItem.product.selling_price * cartItem.quantity) - promoAmount;

                    // Track applied units
                    cartItem.bundle_applied_units += applicableQty;
                    cartItem.bundle_applied = true;
                }
            });
        });

        // --- Render receipt items with promo lines ---
        const itemsList = document.getElementById('receiptItemsList');


        if (paymentData.receipt_items && paymentData.receipt_items.length > 0) {
            itemsList.innerHTML = paymentData.receipt_items.map(item => {
                // Calculate amounts
                const originalAmount = item.product.selling_price * item.quantity;
                const netAmount = item.amount || originalAmount;
                const hasPromo = (item.promo_lines || []).length > 0;
                
                // Build promo lines with consistent formatting
                const promoHtml = (item.promo_lines || []).map(promo => {
                    const discountPercent = Math.abs((parseFloat(promo.amount) / item.product.selling_price) * 100).toFixed(0);
                    return `
                    <div class="promo-line">
                        <span class="promo-text">${promo.label} -${discountPercent}% off ${promo.quantity}x</span>
                        <span class="promo-amount">-₱${Math.abs(parseFloat(promo.amount)).toFixed(2)}</span>
                    </div>
                `}).join('');

                return `
                    <div class="receipt-item">
                        <div class="item-name">${item.product.name}</div>
                        <div class="item-line">
                            <span class="item-qty">${item.quantity} × ₱${parseFloat(item.product.selling_price).toFixed(2)}</span>
                            <span class="item-amount">₱${originalAmount.toFixed(2)}</span>
                        </div>
                        ${promoHtml}
                        ${hasPromo ? `
                        <div class="net-separator"></div>
                        <div class="net-line">
                            <span class="net-label">Net Amount:</span>
                            <span class="net-amount">₱${netAmount.toFixed(2)}</span>
                        </div>
                        ` : ''}
                    </div>
                `;
            }).join('');
        } else {
            itemsList.innerHTML = '<div class="text-center py-4 text-gray-500">No items found</div>';
        }

        
        document.getElementById('receiptModal').classList.remove('hidden');
    }

    

    printReceipt() {
        const receiptContent = document.querySelector('#receiptModal .overflow-y-auto').innerHTML;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
                <head>
                    <title>Receipt - {{ $receipt_no ?? '0' }}</title>
                    <style>
                        @page {
                            size: 48mm auto;
                            margin: 0;
                        }
                        body {
                            font-family: Arial, sans-serif;
                            width: 48mm;
                            margin: 0 auto;
                            padding: 3px;
                            font-size: 10px;
                            color: #000;
                            background: #fff;
                            line-height: 1.3;
                        }

                        .text-center { text-align: center; }
                        .text-right { text-align: right; }
                        .text-left { text-align: left; }
                        .font-bold { font-weight: bold; }
                        .font-semibold { font-weight: 600; }
                        .font-medium { font-weight: 500; }

                        .text-xl { font-size: 14px; }
                        .text-lg { font-size: 12px; }
                        .text-sm { font-size: 9px; }
                        .text-xs { font-size: 8px; }

                        .mb-1 { margin-bottom: 2px; }
                        .mb-2 { margin-bottom: 4px; }
                        .mb-3 { margin-bottom: 6px; }
                        .mb-4 { margin-bottom: 8px; }
                        .mb-6 { margin-bottom: 12px; }
                        .mt-1 { margin-top: 2px; }
                        .mt-2 { margin-top: 4px; }
                        .mt-3 { margin-top: 6px; }
                        .mt-6 { margin-top: 12px; }
                        .pb-2 { padding-bottom: 4px; }
                        .pb-4 { padding-bottom: 8px; }
                        .pt-2 { padding-top: 4px; }
                        .pt-4 { padding-top: 8px; }
                        .py-2 { padding-top: 4px; padding-bottom: 4px; }
                        .pr-2 { padding-right: 4px; }
                        .pl-4 { padding-left: 8px; }

                        .border-b { border-bottom: 1px solid #ddd; }
                        .border-t { border-top: 1px solid #000; }
                        .border-b-2 { border-bottom: 2px solid #000; }
                        .border-t-2 { border-top: 2px solid #000; }
                        .border-gray-100 { border-color: transparent; }
                        .border-gray-200 { border-color: #ddd; }
                        .border-gray-300 { border-color: #999; }

                        .text-gray-500,
                        .text-gray-600,
                        .text-gray-700,
                        .text-gray-800,
                        .text-gray-900,
                        .text-red-600,
                        .text-orange-600,
                        .text-green-600,
                        .text-blue-600 {
                            color: #000;
                        }

                        .flex { display: flex; }
                        .flex-col { flex-direction: column; }
                        .justify-between { justify-content: space-between; }
                        .items-center { align-items: center; }
                        .items-start { align-items: flex-start; }
                        .flex-1 { flex: 1; }

                        .space-y-1 > * + * { margin-top: 2px; }
                        .space-y-2 > * + * { margin-top: 4px; }

                        /* Receipt header info */
                        .mb-4 .flex {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 2px;
                        }

                        .mb-4 .flex span:first-child {
                            text-align: left;
                        }

                        .mb-4 .flex span:last-child {
                            text-align: right;
                        }

                        /* Items section header */
                        h4.font-semibold {
                            border-bottom: 2px solid #000;
                            padding-bottom: 4px;
                            margin-bottom: 6px;
                            font-size: 11px;
                        }

                        /* Receipt items container */
                        #receiptItemsList {
                            margin: 4px 0;
                        }

                        /* Each item block */
                        .receipt-item {
                            margin-bottom: 8px;
                            page-break-inside: avoid;
                        }

                        /* Product name */
                        .item-name {
                            font-size: 10px;
                            font-weight: 500;
                            color: #000;
                            margin-bottom: 2px;
                        }

                        /* Quantity × Price line */
                        .item-line {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 2px;
                        }

                        .item-qty {
                            font-size: 8px;
                            color: #000;
                        }

                        .item-amount {
                            font-size: 9px;
                            font-weight: bold;
                            color: #000;
                            white-space: nowrap;
                        }

                        /* Promo line - Single line */
                        .promo-line {
                            display: flex;
                            justify-content: space-between;
                            padding-left: 12px;
                            margin-top: 1px;
                            margin-bottom: 1px;
                        }

                        .promo-text {
                            font-size: 7px;
                            color: #000;
                        }

                        .promo-amount {
                            font-size: 7px;
                            color: #000;
                            white-space: nowrap;
                        }

                        /* Promo line - Multi-line wrapper */
                        .promo-line-wrapper {
                            padding-left: 12px;
                            margin-top: 1px;
                            margin-bottom: 1px;
                        }

                        .promo-line-split {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                        }

                        .promo-line-split:first-child {
                            margin-bottom: 0;
                        }

                        .promo-text-small {
                            font-size: 7px;
                            color: #000;
                            padding-left: 6px;
                        }

                        /* Net amount separator */
                        .net-separator {
                            border-top: 1px dashed #999;
                            margin: 3px 0;
                        }

                        /* Net amount line */
                        .net-line {
                            display: flex;
                            justify-content: space-between;
                            padding-left: 12px;
                            margin-top: 3px;
                        }

                        .net-label {
                            font-size: 8px;
                            font-weight: 600;
                            color: #000;
                        }

                        .net-amount {
                            font-size: 9px;
                            font-weight: bold;
                            color: #000;
                            white-space: nowrap;
                        }

                        /* Summary section */
                        .border-t-2.border-gray-300.pt-4.space-y-2 {
                            border-top: 2px solid #000;
                            padding-top: 8px;
                            margin-top: 8px;
                        }

                        .border-t-2.border-gray-300.pt-4.space-y-2 > div {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 2px;
                        }

                        .border-t-2.border-gray-300.pt-4.space-y-2 > div:last-child {
                            border-bottom: none !important;
                        }

                        /* VAT section */
                        .border-t.pt-2.mt-2 {
                            border-top: 1px solid #000;
                            padding-top: 4px;
                            margin-top: 4px;
                        }

                        .border-t.pt-2.mt-2.space-y-1 > div {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 2px;
                        }

                        @media print {
                            body {
                                padding: 0;
                                margin: 0;
                                width: 48mm;
                                font-size: 10px;
                            }
                            
                            .receipt-item {
                                page-break-inside: avoid;
                            }
                        }
                    </style>
                </head>
                <body>
                    ${receiptContent}
                </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.focus();
        
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    }

    showToast(message, type = 'info') {
        const colors = {
            'success': '#10b981',
            'error': '#ef4444',
            'warning': '#f59e0b',
            'info': '#3b82f6'
        };
        
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-[9999] transform translate-x-full transition-transform duration-300`;
        toast.style.backgroundColor = colors[type] || colors['info'];
        toast.textContent = message;
        
        document.body.appendChild(toast);
        setTimeout(() => toast.style.transform = 'translateX(0)', 10);
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new PaymentProcessor();
});
</script>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection