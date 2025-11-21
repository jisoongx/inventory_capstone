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
                    <p class="text-sm text-red-100">Receipt No.: <span id="headerReceiptNo">{{ $receipt_no ?? '0' }}</span></p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-red-100">Cashier: {{ $user_firstname ?? 'User' }}</p>
                <p id="headerDateTime" class="text-xs text-red-100"></p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-6">
            
            <!-- LEFT SECTION: Cart Items -->
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
                            <span class="text-2xl font-bold text-gray-900">Total Amount:</span>
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

                    <!-- Change Display - Large and Clear -->
                    <div id="changeDisplay" class="hidden">
                        <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded-xl shadow-lg">
                            <p class="text-green-100 text-sm font-semibold mb-1">CHANGE TO RETURN</p>
                            <p id="changeAmount" class="text-white text-5xl font-bold">₱0.00</p>
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
                        <div id="calcItemDiscountsRow" class="flex justify-between text-sm hidden">
                            <span class="text-gray-700">Item Discounts:</span>
                            <span id="calcItemDiscounts" class="font-semibold text-orange-600">-₱0.00</span>
                        </div>
                        <div id="calcReceiptDiscountRow" class="flex justify-between text-sm hidden">
                            <span class="text-gray-700">Receipt Discount:</span>
                            <span id="calcReceiptDiscount" class="font-semibold text-orange-600">-₱0.00</span>
                        </div>
                        <div id="calcVATRow" class="flex justify-between text-sm hidden">
                            <span class="text-gray-700">VAT (<span id="calcVATRate">12</span>%):</span>
                            <span id="calcVAT" class="font-semibold text-green-600">+₱0.00</span>
                        </div>
                        <div class="flex justify-between text-base font-bold pt-2 border-t-2 border-gray-300">
                            <span class="text-gray-900">Total Amount:</span>
                            <span id="calcTotal" class="text-red-600">₱0.00</span>
                        </div>
                    </div>

                    <!-- Discounts Toggle (Collapsible) -->
                    <div class="border-t pt-4">
                        <button id="toggleDiscounts" class="w-full flex items-center justify-between p-3 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            <span class="font-semibold text-gray-700">Add Discounts & VAT (Optional)</span>
                            <svg id="discountChevron" class="w-5 h-5 text-gray-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div id="discountsSection" class="hidden space-y-4 mt-4 p-4 bg-gray-50 rounded-lg">
                            <!-- VAT -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-semibold text-gray-700">VAT</label>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="enableVAT" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                <div id="vatControls" class="hidden">
                                    <input type="number" id="vatRate" min="0" max="100" step="0.01" value="12" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="12%">
                                </div>
                            </div>

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

                            <!-- Item Discounts -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Item Discounts</label>
                                <div id="itemDiscountsList" class="space-y-2 max-h-[200px] overflow-y-auto scrollbar-custom">
                                    <!-- Item discounts will be populated here -->
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
                    <h2 id="storeNameReceipt" class="text-xl font-bold text-gray-800">{{ $store_info->store_name ?? 'Store Name' }}</h2>
                </div>

                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Receipt No.:</span>
                        <span id="receiptNumber" class="text-sm font-bold text-gray-900">{{ $receipt_no ?? '0' }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Date & Time:</span>
                        <span id="receiptTransactionDate" class="text-sm text-gray-900"></span>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm font-medium text-gray-700">Cashier:</span>
                        <span class="text-sm text-gray-900">{{ $user_firstname ?? 'User' }}</span>
                    </div>
                </div>

                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800 mb-3 pb-2 border-b border-gray-300">Items Purchased</h4>
                    <div id="receiptItemsList" class="space-y-2"></div>
                </div>

                <div class="border-t-2 border-gray-300 pt-4 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Total Items:</span>
                        <span id="receiptTotalItems" class="text-sm font-bold text-gray-900">0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Item Discounts:</span>
                        <span id="receiptItemDiscounts" class="text-sm font-bold text-orange-600">-₱0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Receipt Discount:</span>
                        <span id="receiptReceiptDiscount" class="text-sm font-bold text-orange-600">-₱0.00</span>
                    </div>
                    <div class="flex justify-between items-center" id="receiptVATRow" style="display:none;">
                        <span class="text-sm font-medium text-gray-700">VAT:</span>
                        <span id="receiptVatAmount" class="text-sm font-bold text-green-600">+₱0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-900">Total Amount:</span>
                        <span id="receiptTotalAmount" class="text-lg font-bold text-red-600">₱0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Amount Paid:</span>
                        <span id="receiptAmountPaid" class="text-sm text-gray-900">₱0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Change:</span>
                        <span id="receiptChange" class="text-sm font-bold text-green-600">₱0.00</span>
                    </div>
                </div>

                <div class="text-center mt-6 pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-500">Thank you for your purchase!</p>
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
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    transition: all 0.2s;
    cursor: pointer;
}

.payment-cart-item:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.payment-cart-item.active {
    border: 2px solid #3b82f6;
    background: #eff6ff;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.item-discount-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 12px;
    transition: all 0.2s;
}

.item-discount-card.highlighted {
    border: 2px solid #3b82f6;
    background: #eff6ff;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    animation: highlight-pulse 0.5s ease-out;
}

@keyframes highlight-pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.02);
    }
    100% {
        transform: scale(1);
    }
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
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border-color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}
</style>

<script>
class PaymentProcessor {
    constructor() {
        this.cartItems = [];
        this.itemDiscounts = {};
        this.receiptDiscount = { type: 'percent', value: 0 };
        this.vatEnabled = false;
        this.vatRate = 12;
        this.amountPaid = 0;
        this.totalAmount = 0;
        this.discountsExpanded = false;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateDateTime();
        setInterval(() => this.updateDateTime(), 1000);
        this.loadCartItems();
    }

    bindEvents() {
        // Quick amount buttons
        document.querySelectorAll('.quick-amount-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const amount = parseFloat(e.target.dataset.amount);
                const currentAmount = parseFloat(document.getElementById('amountPaidInput').value) || 0;
                document.getElementById('amountPaidInput').value = currentAmount + amount;
                this.calculateChange();
            });
        });

        // Exact amount button
        document.getElementById('exactAmountBtn')?.addEventListener('click', () => {
            document.getElementById('amountPaidInput').value = this.totalAmount.toFixed(2);
            this.calculateChange();
        });

        // Toggle discounts
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
        });

        // Receipt discount
        document.getElementById('receiptDiscountType')?.addEventListener('change', (e) => {
            this.receiptDiscount.type = e.target.value;
            this.calculateTotals();
        });

        document.getElementById('receiptDiscountValue')?.addEventListener('input', (e) => {
            this.receiptDiscount.value = parseFloat(e.target.value) || 0;
            this.calculateTotals();
        });

        // VAT toggle
        document.getElementById('enableVAT')?.addEventListener('change', (e) => {
            this.vatEnabled = e.target.checked;
            document.getElementById('vatControls').style.display = this.vatEnabled ? 'block' : 'none';
            this.calculateTotals();
        });

        document.getElementById('vatRate')?.addEventListener('input', (e) => {
            this.vatRate = parseFloat(e.target.value) || 0;
            this.calculateTotals();
        });

        // Amount paid
        document.getElementById('amountPaidInput')?.addEventListener('input', () => {
            this.calculateChange();
        });

        // Complete payment
        document.getElementById('completePayment')?.addEventListener('click', () => {
            this.processPayment();
        });

        // Receipt modal buttons
        document.getElementById('printReceiptBtn')?.addEventListener('click', () => {
            this.printReceipt();
        });

        document.getElementById('finishTransactionBtn')?.addEventListener('click', () => {
            window.location.href = '{{ route("store_start_transaction") }}';
        });
    }

    updateDateTime() {
        const now = new Date();
        const options = {
            month: '2-digit',
            day: '2-digit',
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

    async loadCartItems() {
        try {
            const response = await fetch('{{ route("get_cart_items") }}');
            const data = await response.json();
            
            if (data.success) {
                this.cartItems = data.cart_items;
                
                if (this.cartItems.length === 0) {
                    this.showToast('Cart is empty. Redirecting...', 'error');
                    setTimeout(() => {
                        window.location.href = '{{ route("store_start_transaction") }}';
                    }, 1500);
                    return;
                }
                
                this.cartItems.forEach(item => {
                    this.itemDiscounts[item.product.prod_code] = {
                        type: 'percent',
                        value: 0
                    };
                });
                
                this.renderCart();
                this.calculateTotals();
            }
        } catch (error) {
            console.error('Error loading cart:', error);
            this.showToast('Error loading cart items', 'error');
        }
    }

    renderCart() {
        const container = document.getElementById('paymentCartItems');
        if (!container) return;

        container.innerHTML = this.cartItems.map((item, index) => `
            <div class="payment-cart-item" data-product-code="${item.product.prod_code}">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900">${item.product.name}</h4>
                        <p class="text-sm text-gray-600">₱${parseFloat(item.product.selling_price).toFixed(2)} × ${item.quantity}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-900">₱${item.amount.toFixed(2)}</p>
                    </div>
                </div>
            </div>
        `).join('');

        // Add click handlers to cart items
        document.querySelectorAll('.payment-cart-item').forEach(itemEl => {
            itemEl.addEventListener('click', () => {
                const productCode = itemEl.dataset.productCode;
                this.highlightItemDiscount(productCode);
            });
        });

        const totalItems = this.cartItems.length;
        const totalQuantity = this.cartItems.reduce((sum, item) => sum + item.quantity, 0);
        const subtotal = this.cartItems.reduce((sum, item) => sum + item.amount, 0);

        document.getElementById('cartTotalItems').textContent = totalItems;
        document.getElementById('cartTotalQuantity').textContent = totalQuantity;
        document.getElementById('cartSubtotal').textContent = `₱${subtotal.toFixed(2)}`;
        
        // Render item discounts section
        this.renderItemDiscounts();
    }

    renderItemDiscounts() {
        const container = document.getElementById('itemDiscountsList');
        if (!container) return;

        container.innerHTML = this.cartItems.map(item => `
            <div class="item-discount-card" data-product-code="${item.product.prod_code}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-800">${item.product.name}</span>
                    <span class="text-xs text-gray-600">₱${item.amount.toFixed(2)}</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <select class="item-discount-type px-2 py-1.5 border border-gray-300 rounded text-xs" data-product-code="${item.product.prod_code}">
                        <option value="percent">% Off</option>
                        <option value="amount">₱ Off</option>
                    </select>
                    <input type="number" 
                        class="item-discount-value px-2 py-1.5 border border-gray-300 rounded text-xs" 
                        data-product-code="${item.product.prod_code}"
                        min="0" 
                        step="0.01" 
                        value="0" 
                        placeholder="0.00">
                </div>
            </div>
        `).join('');

        // Add event listeners for item discounts
        document.querySelectorAll('.item-discount-type').forEach(select => {
            select.addEventListener('change', (e) => {
                const productCode = e.target.dataset.productCode;
                this.itemDiscounts[productCode].type = e.target.value;
                this.calculateTotals();
            });
        });

        document.querySelectorAll('.item-discount-value').forEach(input => {
            input.addEventListener('input', (e) => {
                const productCode = e.target.dataset.productCode;
                this.itemDiscounts[productCode].value = parseFloat(e.target.value) || 0;
                this.calculateTotals();
            });
        });
    }

    highlightItemDiscount(productCode) {
        // Open discounts section if closed
        if (!this.discountsExpanded) {
            document.getElementById('toggleDiscounts').click();
        }

        // Remove all active states from cart items
        document.querySelectorAll('.payment-cart-item').forEach(el => {
            el.classList.remove('active');
        });

        // Remove all highlights from discount cards
        document.querySelectorAll('.item-discount-card').forEach(el => {
            el.classList.remove('highlighted');
        });

        // Add active state to clicked cart item
        const cartItem = document.querySelector(`.payment-cart-item[data-product-code="${productCode}"]`);
        if (cartItem) {
            cartItem.classList.add('active');
        }

        // Highlight and scroll to the corresponding discount card
        const discountCard = document.querySelector(`.item-discount-card[data-product-code="${productCode}"]`);
        if (discountCard) {
            discountCard.classList.add('highlighted');
            
            // Scroll the discount card into view
            setTimeout(() => {
                discountCard.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }, 300);

            // Remove highlight after animation
            setTimeout(() => {
                discountCard.classList.remove('highlighted');
            }, 2000);
        }
    }

    calculateTotals() {
        let subtotal = 0;
        let totalItemDiscounts = 0;

        this.cartItems.forEach(item => {
            const itemTotal = item.amount;
            const discount = this.itemDiscounts[item.product.prod_code];
            let discountAmount = 0;

            if (discount.type === 'percent') {
                discountAmount = itemTotal * (discount.value / 100);
            } else {
                discountAmount = discount.value;
            }

            totalItemDiscounts += discountAmount;
            subtotal += itemTotal;
        });

        const afterItemDiscounts = subtotal - totalItemDiscounts;

        let receiptDiscountAmount = 0;
        if (this.receiptDiscount.type === 'percent') {
            receiptDiscountAmount = afterItemDiscounts * (this.receiptDiscount.value / 100);
        } else {
            receiptDiscountAmount = this.receiptDiscount.value;
        }

        const afterReceiptDiscount = afterItemDiscounts - receiptDiscountAmount;

        let vatAmount = 0;
        if (this.vatEnabled) {
            vatAmount = afterReceiptDiscount * (this.vatRate / 100);
        }

        const totalAmount = afterReceiptDiscount + vatAmount;

        // Update calculation breakdown
        document.getElementById('calcSubtotal').textContent = `₱${subtotal.toFixed(2)}`;
        
        // Show/hide and update item discounts row
        if (totalItemDiscounts > 0) {
            document.getElementById('calcItemDiscountsRow').classList.remove('hidden');
            document.getElementById('calcItemDiscounts').textContent = `-₱${totalItemDiscounts.toFixed(2)}`;
        } else {
            document.getElementById('calcItemDiscountsRow').classList.add('hidden');
        }
        
        // Show/hide and update receipt discount row
        if (receiptDiscountAmount > 0) {
            document.getElementById('calcReceiptDiscountRow').classList.remove('hidden');
            document.getElementById('calcReceiptDiscount').textContent = `-₱${receiptDiscountAmount.toFixed(2)}`;
        } else {
            document.getElementById('calcReceiptDiscountRow').classList.add('hidden');
        }
        
        // Show/hide and update VAT row
        if (vatAmount > 0) {
            document.getElementById('calcVATRow').classList.remove('hidden');
            document.getElementById('calcVATRate').textContent = this.vatRate.toFixed(2);
            document.getElementById('calcVAT').textContent = `+₱${vatAmount.toFixed(2)}`;
        } else {
            document.getElementById('calcVATRow').classList.add('hidden');
        }
        
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

        if (this.amountPaid > 0 && this.amountPaid < this.totalAmount) {
            const shortage = this.totalAmount - this.amountPaid;
            document.getElementById('amountShortage').textContent = `₱${shortage.toFixed(2)}`;
            insufficientWarning.classList.remove('hidden');
            changeDisplay.classList.add('hidden');
            completeBtn.disabled = true;
        } else if (this.amountPaid >= this.totalAmount && this.amountPaid > 0) {
            document.getElementById('changeAmount').textContent = `₱${change.toFixed(2)}`;
            changeDisplay.classList.remove('hidden');
            insufficientWarning.classList.add('hidden');
            completeBtn.disabled = false;
        } else {
            changeDisplay.classList.add('hidden');
            insufficientWarning.classList.add('hidden');
            completeBtn.disabled = true;
        }
    }

    async processPayment() {
        if (this.amountPaid < this.totalAmount) {
            this.showToast('Insufficient amount paid', 'error');
            return;
        }

        const completeBtn = document.getElementById('completePayment');
        completeBtn.disabled = true;
        completeBtn.textContent = 'Processing...';

        try {
            const paymentData = {
                payment_method: 'cash',
                amount_paid: this.amountPaid,
                receipt_discount_type: this.receiptDiscount.type,
                receipt_discount_value: this.receiptDiscount.value,
                vat_enabled: this.vatEnabled,
                vat_rate: this.vatRate,
                item_discounts: this.itemDiscounts
            };

            const response = await fetch('{{ route("process_payment") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(paymentData)
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('Payment completed successfully!', 'success');
                this.showReceiptModal(data);
            } else {
                this.showToast(data.message || 'Payment failed', 'error');
                completeBtn.disabled = false;
                completeBtn.textContent = 'Complete Payment';
            }
        } catch (error) {
            console.error('Error processing payment:', error);
            this.showToast('Error processing payment: ' + error.message, 'error');
            completeBtn.disabled = false;
            completeBtn.textContent = 'Complete Payment';
        }
    }

    showReceiptModal(paymentData) {
        document.getElementById('receiptNumber').textContent = paymentData.receipt_id || '{{ $receipt_no ?? "0" }}';
        document.getElementById('receiptTotalItems').textContent = paymentData.total_quantity;
        document.getElementById('receiptTotalAmount').textContent = `₱${paymentData.total_amount.toFixed(2)}`;
        document.getElementById('receiptAmountPaid').textContent = `₱${paymentData.amount_paid.toFixed(2)}`;
        document.getElementById('receiptChange').textContent = `₱${paymentData.change.toFixed(2)}`;

        const itemDiscountsAmount = paymentData.total_item_discounts ?? 0;
        const receiptDiscountAmount = paymentData.receipt_discount_amount ?? 0;
        const vatAmount = paymentData.vat_amount ?? 0;

        document.getElementById('receiptItemDiscounts').textContent = `-₱${parseFloat(itemDiscountsAmount).toFixed(2)}`;
        document.getElementById('receiptReceiptDiscount').textContent = `-₱${parseFloat(receiptDiscountAmount).toFixed(2)}`;
        document.getElementById('receiptVatAmount').textContent = `+₱${parseFloat(vatAmount).toFixed(2)}`;
        document.getElementById('receiptVATRow').style.display = vatAmount > 0 ? 'flex' : 'none';

        const now = new Date();
        const options = {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        document.getElementById('receiptTransactionDate').textContent = now.toLocaleString('en-US', options);
        
        const itemsList = document.getElementById('receiptItemsList');
        if (paymentData.receipt_items && paymentData.receipt_items.length > 0) {
            itemsList.innerHTML = paymentData.receipt_items.map(item => `
                <div class="flex justify-between items-start py-2 border-b border-gray-100 last:border-b-0">
                    <div class="flex-1 pr-2">
                        <div class="text-sm font-medium text-gray-900">${item.product.name}</div>
                        <div class="text-xs text-gray-500">${item.quantity} × ₱${parseFloat(item.product.selling_price).toFixed(2)}</div>
                    </div>
                    <div class="text-sm font-bold text-gray-900">₱${item.amount.toFixed(2)}</div>
                </div>
            `).join('');
        } else {
            itemsList.innerHTML = '<div class="text-center py-4 text-gray-500">No items found</div>';
        }
        
        if (paymentData.low_stock_warning && paymentData.low_stock_warning.length > 0) {
            const warningHtml = paymentData.low_stock_warning.map(product => 
                `<p class="text-xs text-orange-600">⚠️ ${product.name}: ${product.remaining_stock} left</p>`
            ).join('');
            
            const warningDiv = document.createElement('div');
            warningDiv.className = 'mt-4 p-3 bg-orange-50 border border-orange-200 rounded-lg';
            warningDiv.innerHTML = `
                <p class="text-sm font-semibold text-orange-800 mb-1">Low Stock Alert:</p>
                ${warningHtml}
            `;
            
            itemsList.parentElement.appendChild(warningDiv);
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
                            font-size: 10.5px;
                            color: #000;
                            background: #fff;
                            line-height: 1.05;
                        }

                        .text-center { text-align: center; }
                        .font-bold { font-weight: bold; }

                        .text-xl { font-size: 1rem; }
                        .text-lg { font-size: 0.95rem; }
                        .text-sm { font-size: 0.8rem; }
                        .text-xs { font-size: 0.7rem; }

                        .mb-1 { margin-bottom: 1px; }
                        .mb-2 { margin-bottom: 2px; }
                        .mt-1 { margin-top: 1px; }
                        .mt-2 { margin-top: 2px; }

                        .border-b { border-bottom: 1px solid #000; }
                        .border-t { border-top: 1px solid #000; }

                        .flex { display: flex; }
                        .justify-between { justify-content: space-between; }
                        .items-center { align-items: center; }
                        .items-start { align-items: flex-start; }
                        .flex-1 { flex: 1; }

                        .p-1 { padding: 1px; }
                        .py-1 { padding-top: 1px; padding-bottom: 1px; }

                        .space-y-1 > * + * { margin-top: 1px; }

                        @media print {
                            body {
                                padding: 0;
                                margin: 0;
                                width: 48mm;
                                font-size: 10px;
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
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-[9999] transform translate-x-full transition-transform duration-300`;
        toast.style.backgroundColor = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6';
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