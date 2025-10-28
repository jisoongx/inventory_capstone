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
            <div class="flex flex-col bg-gray-50 rounded-lg shadow-lg overflow-hidden h-fit">
                <div class="bg-white p-4 border-b">
                    <h3 class="text-lg font-bold text-gray-800">Order Summary</h3>
                    <p class="text-sm text-gray-600">Review your items</p>
                </div>
                
                <!-- Cart Items List -->
                <div class="overflow-y-auto p-4 space-y-3 max-h-96 scrollbar-custom" id="paymentCartItems">
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
            <div class="flex flex-col bg-white rounded-lg shadow-lg overflow-hidden h-fit">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4">
                    <h3 class="text-lg font-bold">Payment Details</h3>
                    <p class="text-sm text-blue-100">Calculate final amount</p>
                </div>

                <div class="overflow-y-auto p-6 space-y-6 scrollbar-custom max-h-[600px]">
                    
                    <!-- Item Discounts Section -->
                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-gray-700">Item Discounts</label>
                        <div id="itemDiscountsContainer" class="space-y-2 max-h-64 overflow-y-auto scrollbar-custom">
                            <!-- Item discount controls will be populated here -->
                        </div>
                    </div>

                    <!-- Receipt-Level Discount -->
                    <div class="space-y-3 border-t pt-4">
                        <label class="block text-sm font-semibold text-gray-700">Receipt Discount</label>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Discount Type</label>
                                <select id="receiptDiscountType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="percent">Percentage (%)</option>
                                    <option value="amount">Fixed Amount (₱)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Discount Value</label>
                                <input type="number" id="receiptDiscountValue" min="0" step="0.01" value="0" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="0.00">
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-700">Discount Amount:</span>
                                <span id="receiptDiscountAmount" class="font-bold text-blue-600">₱0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- VAT Section -->
                    <div class="space-y-3 border-t pt-4">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-semibold text-gray-700">Value Added Tax (VAT)</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="enableVAT" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div id="vatControls" class="space-y-2" style="display: none;">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">VAT Rate (%)</label>
                                <input type="number" id="vatRate" min="0" max="100" step="0.01" value="12" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            </div>
                            <div class="bg-green-50 p-3 rounded-lg">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">VAT Amount:</span>
                                    <span id="vatAmount" class="font-bold text-green-600">₱0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Amount Paid -->
                    <div class="space-y-3 border-t pt-4">
                        <label class="block text-sm font-semibold text-gray-700">Amount Paid</label>
                        <input type="number" id="amountPaidInput" min="0" step="0.01" 
                            class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter amount paid">
                        
                        <!-- Insufficient Amount Warning -->
                        <div id="insufficientWarning" class="bg-red-50 border border-red-200 p-3 rounded-lg hidden">
                            <div class="flex items-center gap-2 text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <span class="text-sm font-semibold">Insufficient Amount</span>
                            </div>
                            <p class="text-xs text-red-600 mt-1">Still needed: <span id="amountShortage" class="font-bold">₱0.00</span></p>
                        </div>

                        <!-- Change Display -->
                        <div id="changeDisplay" class="bg-green-50 border border-green-200 p-3 rounded-lg hidden">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-semibold text-green-700">Change:</span>
                                <span id="changeAmount" class="text-xl font-bold text-green-700">₱0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary & Actions -->
                <div class="border-t bg-gray-50 p-6 space-y-4">
                    <!-- Final Summary -->
                    <div class="bg-white p-4 rounded-lg shadow space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span id="summarySubtotal" class="font-semibold">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Item Discounts:</span>
                            <span id="summaryItemDiscounts" class="font-semibold text-orange-600">-₱0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Receipt Discount:</span>
                            <span id="summaryReceiptDiscount" class="font-semibold text-orange-600">-₱0.00</span>
                        </div>
                        <div class="flex justify-between text-sm" id="summaryVATRow" style="display: none;">
                            <span class="text-gray-600">VAT:</span>
                            <span id="summaryVAT" class="font-semibold text-green-600">+₱0.00</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold pt-3 border-t-2">
                            <span>Total Amount:</span>
                            <span id="summaryTotal" class="text-red-600">₱0.00</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <a href="{{ route('store_start_transaction') }}" class="flex-1 px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-100 transition text-center">
                            Cancel
                        </a>
                        <button id="completePayment" class="flex-1 px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-bold hover:from-green-700 hover:to-green-800 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            Complete Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Display Modal -->
<div id="receiptModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] hidden p-4">
    <div class="bg-white rounded-lg w-full max-w-md mx-auto h-full max-h-[90vh] flex flex-col">
        <!-- Receipt Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-4 rounded-t-lg flex-shrink-0">
            <div class="text-center">
                <h3 class="text-xl font-bold mb-2">Payment Successful!</h3>
                <p class="text-sm text-red-100">Transaction completed successfully</p>
            </div>
        </div>

        <!-- Receipt Content - Scrollable -->
        <div class="flex-1 overflow-y-auto min-h-0 scrollbar-custom">
            <div class="p-6">
                <!-- Store Info -->
                <div class="text-center mb-6 pb-4 border-b-2 border-gray-200">
                    <h2 id="storeNameReceipt" class="text-xl font-bold text-gray-800">{{ $store_info->store_name ?? 'Store Name' }}</h2>
                </div>

                <!-- Transaction Details -->
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

                <!-- Items List -->
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800 mb-3 pb-2 border-b border-gray-300">Items Purchased</h4>
                    <div id="receiptItemsList" class="space-y-2">
                        <!-- Items will be populated here -->
                    </div>
                </div>

                <!-- Totals -->
                <div class="border-t-2 border-gray-300 pt-4 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Total Items:</span>
                        <span id="receiptTotalItems" class="text-sm font-bold text-gray-900">0</span>
                    </div>

                    <!-- Item Discounts -->
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Item Discounts:</span>
                        <span id="receiptItemDiscounts" class="text-sm font-bold text-orange-600">-₱0.00</span>
                    </div>

                    <!-- Receipt Discount -->
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Receipt Discount:</span>
                        <span id="receiptReceiptDiscount" class="text-sm font-bold text-orange-600">-₱0.00</span>
                    </div>

                    <!-- VAT -->
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

                <!-- Footer Message -->
                <div class="text-center mt-6 pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-500">Thank you for your purchase!</p>
                    <p class="text-xs text-gray-500">Please keep this receipt for your records.</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons - Fixed at bottom -->
        <div class="p-4 border-t bg-gray-50 flex gap-3 rounded-b-lg flex-shrink-0">
            <button id="printReceiptBtn" class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-bold hover:bg-blue-700 transition-colors">
                <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print Receipt
            </button>
            <button id="finishTransactionBtn" class="flex-1 bg-red-600 text-white py-3 px-4 rounded-lg font-bold hover:bg-red-700 transition-colors">
                <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Close
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
}

.payment-cart-item:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateDateTime();
        setInterval(() => this.updateDateTime(), 1000);
        this.loadCartItems();
    }

    bindEvents() {
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
                
                // Initialize item discounts
                this.cartItems.forEach(item => {
                    this.itemDiscounts[item.product.prod_code] = {
                        type: 'percent',
                        value: 0
                    };
                });
                
                this.renderCart();
                this.renderItemDiscounts();
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

        container.innerHTML = this.cartItems.map(item => `
            <div class="payment-cart-item">
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

        const totalItems = this.cartItems.length;
        const totalQuantity = this.cartItems.reduce((sum, item) => sum + item.quantity, 0);
        const subtotal = this.cartItems.reduce((sum, item) => sum + item.amount, 0);

        document.getElementById('cartTotalItems').textContent = totalItems;
        document.getElementById('cartTotalQuantity').textContent = totalQuantity;
        document.getElementById('cartSubtotal').textContent = `₱${subtotal.toFixed(2)}`;
    }

    renderItemDiscounts() {
        const container = document.getElementById('itemDiscountsContainer');
        if (!container) return;

        container.innerHTML = this.cartItems.map(item => `
    <div class="bg-gray-50 p-3 rounded-lg">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-700">${item.product.name}</span>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <select data-prod-code="${item.product.prod_code}" class="item-discount-type text-xs px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                <option value="percent">%</option>
                <option value="amount">₱</option>
            </select>
            <input type="number" data-prod-code="${item.product.prod_code}" class="item-discount-value text-xs px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500" min="0" step="0.01" value="0" placeholder="0.00">
        </div>
    </div>
`).join('');

        container.querySelectorAll('.item-discount-type').forEach(select => {
            select.addEventListener('change', (e) => {
                const prodCode = e.target.dataset.prodCode;
                this.itemDiscounts[prodCode].type = e.target.value;
                this.calculateTotals();
            });
        });

        container.querySelectorAll('.item-discount-value').forEach(input => {
            input.addEventListener('input', (e) => {
                const prodCode = e.target.dataset.prodCode;
                this.itemDiscounts[prodCode].value = parseFloat(e.target.value) || 0;
                this.calculateTotals();
            });
        });
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

        document.getElementById('receiptDiscountAmount').textContent = `₱${receiptDiscountAmount.toFixed(2)}`;
        document.getElementById('vatAmount').textContent = `₱${vatAmount.toFixed(2)}`;
        
        document.getElementById('summarySubtotal').textContent = `₱${subtotal.toFixed(2)}`;
        document.getElementById('summaryItemDiscounts').textContent = `-₱${totalItemDiscounts.toFixed(2)}`;
        document.getElementById('summaryReceiptDiscount').textContent = `-₱${receiptDiscountAmount.toFixed(2)}`;
        document.getElementById('summaryVAT').textContent = `+₱${vatAmount.toFixed(2)}`;
        document.getElementById('summaryTotal').textContent = `₱${totalAmount.toFixed(2)}`;

        document.getElementById('summaryVATRow').style.display = this.vatEnabled ? 'flex' : 'none';

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

            // FIXED: Use the correct route for processing payment
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
        // Populate receipt data
        document.getElementById('receiptNumber').textContent = paymentData.receipt_id || '{{ $receipt_no ?? "0" }}';
        document.getElementById('receiptTotalItems').textContent = paymentData.total_quantity;
        document.getElementById('receiptTotalAmount').textContent = `₱${paymentData.total_amount.toFixed(2)}`;
        document.getElementById('receiptAmountPaid').textContent = `₱${paymentData.amount_paid.toFixed(2)}`;
        document.getElementById('receiptChange').textContent = `₱${paymentData.change.toFixed(2)}`;

        // New fields: item discounts, receipt discount, VAT
        const itemDiscountsAmount = paymentData.total_item_discounts ?? 0;
        const receiptDiscountAmount = paymentData.receipt_discount_amount ?? 0;
        const vatAmount = paymentData.vat_amount ?? 0;

        document.getElementById('receiptItemDiscounts').textContent = `-₱${parseFloat(itemDiscountsAmount).toFixed(2)}`;
        document.getElementById('receiptReceiptDiscount').textContent = `-₱${parseFloat(receiptDiscountAmount).toFixed(2)}`;
        document.getElementById('receiptVatAmount').textContent = `+₱${parseFloat(vatAmount).toFixed(2)}`;
        document.getElementById('receiptVATRow').style.display = vatAmount > 0 ? 'flex' : 'none';

        // Set transaction date/time
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
        
        // Populate items list
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
        
        // Show low stock warning if any
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
            
            // Insert warning after items list
            itemsList.parentElement.appendChild(warningDiv);
        }
        
        // Show the modal
        document.getElementById('receiptModal').classList.remove('hidden');
    }

    printReceipt() {
        // Create print content
        const receiptContent = document.querySelector('#receiptModal .overflow-y-auto').innerHTML;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Receipt - {{ $receipt_no ?? '0' }}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        padding: 20px;
                        max-width: 400px;
                        margin: 0 auto;
                    }
                    .text-center { text-align: center; }
                    .font-bold { font-weight: bold; }
                    .text-xl { font-size: 1.25rem; }
                    .text-lg { font-size: 1.125rem; }
                    .text-sm { font-size: 0.875rem; }
                    .text-xs { font-size: 0.75rem; }
                    .mb-2 { margin-bottom: 0.5rem; }
                    .mb-3 { margin-bottom: 0.75rem; }
                    .mb-4 { margin-bottom: 1rem; }
                    .mb-6 { margin-bottom: 1.5rem; }
                    .mt-4 { margin-top: 1rem; }
                    .mt-6 { margin-top: 1.5rem; }
                    .pb-2 { padding-bottom: 0.5rem; }
                    .pb-4 { padding-bottom: 1rem; }
                    .pt-4 { padding-top: 1rem; }
                    .p-3 { padding: 0.75rem; }
                    .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
                    .border-b { border-bottom: 1px solid #e5e7eb; }
                    .border-b-2 { border-bottom: 2px solid #d1d5db; }
                    .border-t { border-top: 1px solid #e5e7eb; }
                    .border-t-2 { border-top: 2px solid #d1d5db; }
                    .border-gray-100 { border-color: #f3f4f6; }
                    .border-gray-200 { border-color: #e5e7eb; }
                    .border-gray-300 { border-color: #d1d5db; }
                    .space-y-2 > * + * { margin-top: 0.5rem; }
                    .flex { display: flex; }
                    .justify-between { justify-content: space-between; }
                    .items-center { align-items: center; }
                    .items-start { align-items: flex-start; }
                    .flex-1 { flex: 1; }
                    .pr-2 { padding-right: 0.5rem; }
                    .text-gray-500 { color: #6b7280; }
                    .text-gray-600 { color: #4b5563; }
                    .text-gray-700 { color: #374151; }
                    .text-gray-800 { color: #1f2937; }
                    .text-gray-900 { color: #111827; }
                    .text-red-600 { color: #dc2626; }
                    .text-green-600 { color: #16a34a; }
                    .text-orange-600 { color: #ea580c; }
                    .text-orange-800 { color: #9a3412; }
                    .bg-orange-50 { background-color: #fff7ed; }
                    .border-orange-200 { border: 1px solid #fed7aa; }
                    .rounded-lg { border-radius: 0.5rem; }
                    @media print {
                        body { padding: 0; }
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

// Initialize payment processor
document.addEventListener('DOMContentLoaded', () => {
    new PaymentProcessor();
});
</script>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection