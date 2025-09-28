@extends('dashboards.owner.owner')
@section('content')
<div class="px-4 mb-2">
    @livewire('expiration-container')
</div>
<div class="px-4">
    <!-- Top Navigation Bar -->
    <div class="bg-white shadow-lg rounded-lg mb-4 p-4">
        <div class="flex items-center justify-between">
            <!-- Left Section - Search Bar -->
            <div class="flex items-center space-x-4 flex-1">
                <!-- Search Bar -->
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    </div>
                    <input type="text" id="searchInput" placeholder="Search products by name or barcode..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <!-- Right Section - Barcode Scanner Button -->
            <div class="ml-4">
                <button id="barcodeBtn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                    <span>Scanner</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 grid grid-cols-3 gap-4 h-[35rem] overflow-hidden">

       <div class="flex flex-col col-span-2 min-h-0">
            <!-- Categories Filter -->
            <div class="bg-white shadow-lg rounded-lg p-3 overflow-x-auto flex-shrink-0">
                <div id="categoryPills" class="flex space-x-2">
                    <button class="category-pill active" data-category="">
                        <span class="bg-gray-500 rounded-full"></span>
                        All Categories
                    </button>
                    <!-- Categories will be loaded here dynamically -->
                </div>
            </div>

            <!-- Left Side - Product Grid -->
            <div class="flex-1 bg-white rounded-lg shadow-lg p-3 overflow-y-auto min-h-0">
                <div class="flex flex-col">
                    <!-- Loading State -->
                    <div id="loadingProducts" class="flex-1 flex items-center justify-center">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600 mx-auto mb-4"></div>
                            <p class="text-gray-600">Loading products...</p>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div id="productsGrid" class="hidden h-full overflow-y-auto p-2">
                        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-3"></div>
                    </div>

                    <!-- No Products Found -->
                    <div id="noProducts" class="hidden flex-1 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="mx-auto mb-4 w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <p class="text-gray-600 text-lg">No products found</p>
                            <p class="text-gray-500 text-sm">Try adjusting your search or category filter</p>
                        </div>
                    </div>
                </div>
            </div>
       </div>

        <!-- Right Side - Cart Items (Expanded) -->
        <div class="bg-white rounded-lg shadow-lg flex flex-col min-h-0">
            <!-- Receipt Info Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-4 flex-shrink-0">
                <div class="text-center">
                    <p class="text-sm font-bold">Receipt No.: {{ $receipt_no ?? '0' }}</p>
                    <p id="receiptDateTime" class="text-xs text-red-100 mt-1"></p>
                    <p class="text-xs text-red-100">Cashier: {{ $user_firstname ?? 'User' }}</p>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4 min-h-0">
                <div id="cartItems">
                    <!-- Empty Cart State -->
                    <div id="emptyCart" class="text-center py-8">
                        <svg class="mx-auto mb-4 w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0H17M9 19a2 2 0 11-4 0 2 2 0 014 0zM20 19a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-gray-600 text-sm">Your cart is empty</p>
                        <p class="text-gray-500 text-xs">Tap on products to add them</p>
                    </div>
                </div>
            </div>

            <!-- Cart Summary and Actions -->
            <div class="border-t p-4 space-y-3 flex-shrink-0 bg-gray-50">
                <!-- Summary -->
                <div class="bg-white p-3 rounded-lg shadow-sm border">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-medium text-gray-700">Total Items:</span>
                        <span id="totalQuantity" class="text-xs font-bold text-gray-900">0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-900">Total:</span>
                        <span id="totalAmount" class="text-sm font-bold text-red-600">₱0.00</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2">
                    <button id="processPaymentBtn" disabled {{ $expired ? 'disabled' : '' }}
                            class="text-xs w-full bg-gradient-to-r from-red-600 to-red-700 text-white py-3 px-4 rounded-lg font-bold text-base transition-all duration-300 hover:from-red-700 hover:to-red-800 disabled:opacity-50 disabled:cursor-not-allowed
                            {{ $expired ? 'cursor-not-allowed hover:bg-red-500' : '' }}">
                        Process Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Barcode Scanner Modal -->
<div id="barcodeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 w-96 max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Barcode Scanner</h3>
            <button id="closeBarcodeModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="space-y-4">
            <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                <svg class="mx-auto mb-2 w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <p class="text-gray-600 mb-2">Start Scanning</p>
                <p class="text-sm text-gray-500">Camera will activate here</p>
            </div>
            <input type="text" id="barcodeInput" placeholder="Or enter barcode manually..." 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
            <div class="flex gap-2">
                <button id="searchBarcodeBtn" class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors">
                    Search
                </button>
                <button id="cancelBarcodeBtn" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Item Reason Modal -->
<div id="removeReasonModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 w-96 max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Remove Item</h3>
            <button id="closeRemoveModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="space-y-4">
            <p class="text-gray-700">Please select a reason for removing this item:</p>
            <div class="space-y-2">
                <button class="remove-reason-btn w-full text-left p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors" data-reason="cancel">
                    <svg class="inline-block w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                    </svg>
                    Cancel Item
                </button>
                <button class="remove-reason-btn w-full text-left p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors" data-reason="damage">
                    <svg class="inline-block w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    Damaged Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Display Modal -->
<div id="receiptModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-lg w-full max-w-md mx-auto h-full max-h-[200vh] flex flex-col">
        <!-- Receipt Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-4 rounded-t-lg flex-shrink-0">
            <div class="text-center">
                <h3 class="text-xl font-bold mb-2">Payment Successful!</h3>
                <p class="text-sm text-red-100">Transaction completed successfully</p>
            </div>
        </div>

        <!-- Receipt Content - Scrollable -->
        <div class="flex-1 overflow-y-auto min-h-0" style="max-height: calc(90vh - 120px);">
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

<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 w-96 max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Process Payment</h3>
            <button id="closePaymentModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="space-y-4">
            <!-- Payment Summary -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium">Total Items:</span>
                    <span id="paymentTotalQuantity" class="text-sm font-bold">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold">Amount Due:</span>
                    <span id="paymentTotalAmount" class="text-lg font-bold text-red-600">₱0.00</span>
                </div>
            </div>

            <!-- Amount Received Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Amount Received</label>
                <input type="number" id="amountReceived" step="0.01" min="0" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 text-lg"
                       placeholder="Enter amount received">
            </div>

            <!-- Insufficient Amount Warning -->
            <div id="warningDisplay" class="bg-red-50 p-4 rounded-lg border border-red-200 hidden">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <span class="text-lg font-bold text-red-700">Insufficient Amount</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-red-700">Still needed:</span>
                    <span id="warningAmount" class="text-lg font-bold text-red-700">₱0.00</span>
                </div>
                <p class="text-xs text-red-600 mt-2">Please enter an amount equal to or greater than the total due.</p>
            </div>

            <!-- Change Display -->
            <div id="changeDisplay" class="bg-green-50 p-4 rounded-lg border border-green-200 hidden">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold text-green-700">Change:</span>
                    <span id="changeAmount" class="text-lg font-bold text-green-700">₱0.00</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button id="completePaymentBtn" class="flex-1 bg-red-600 text-white py-3 px-4 rounded-lg font-bold hover:bg-red-700 transition-colors opacity-50 cursor-not-allowed" disabled>
                    Complete Payment
                </button>
                <button id="cancelPaymentBtn" class="px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Category Pills */
.category-pill {
    padding: 10px 12px;
    border-radius: 8px;
    border: 2px solid #e5e7eb;
    background-color: white;
    color: #374151;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-align: center;
    min-height: 40px;
}

.category-pill:hover:not(.active) {
    background-color: #f9fafb;
    border-color: #9ca3af;
    transform: translateY(-1px);
}

.category-pill.active {
    background-color: #ef4444;
    color: white;
    border-color: #ef4444;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.25);
}

/* Product Cards */
.product-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    min-height: 200px;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: #f87171;
}

.product-card.out-of-stock {
    opacity: 0.6;
    cursor: not-allowed;
    background-color: #f9fafb;
}

.product-card.out-of-stock:hover {
    transform: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border-color: #e5e7eb;
}

.product-card.low-stock {
    border-color: #fbbf24;
    background-color: #fffbeb;
}

.product-image {
    width: 100%;
    height: 80px;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border-radius: 8px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px dashed #d1d5db;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
}

.product-image.has-image {
    border: 2px solid #e5e7eb;
    background: white;
}

/* Cart Items */
.cart-item {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 12px;
}

.quantity-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #ef4444;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    transition: background-color 0.2s;
    font-weight: bold;
    font-size: 16px;
}

.quantity-btn:hover:not(:disabled) {
    background-color: #dc2626;
}

.quantity-btn:disabled {
    background-color: #d1d5db;
    cursor: not-allowed;
}

.remove-btn {
    color: #ef4444;
    padding: 8px;
    border-radius: 6px;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
    background: none;
}

.remove-btn:hover {
    color: #dc2626;
    background-color: #fef2f2;
}

/* Loading animation */
@keyframes spin {
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Toast notification styles */
.toast {
    position: fixed;
    top: 16px;
    right: 16px;
    padding: 16px 24px;
    border-radius: 8px;
    color: white;
    z-index: 1000;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateX(100%);
}

.toast.show {
    transform: translateX(0);
}

.toast.success {
    background-color: #10b981;
}

.toast.error {
    background-color: #ef4444;
}

.toast.info {
    background-color: #3b82f6;
}

/* Modal backdrop blur */
body.modal-open {
    overflow: hidden;
}

/* Scrollbar styling */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Receipt Modal Specific Scrolling */
#receiptModal .overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

#receiptModal .overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

#receiptModal .overflow-y-auto::-webkit-scrollbar-track {
    background: #f8fafc;
    border-radius: 4px;
    margin: 8px 0;
}

#receiptModal .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 4px;
    border: 2px solid #f8fafc;
}

#receiptModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

/* Ensure items list has proper spacing for scrolling */
#receiptItemsList {
    max-height: none;
    padding-bottom: 8px;
}

#receiptItemsList .border-b:last-child {
    border-bottom: none;
}
</style>

<script>
class KioskSystem {
    constructor() {
        this.currentProducts = [];
        this.cartItems = [];
        this.totalAmount = 0;
        this.totalQuantity = 0;
        this.removeItemData = null;
        this.categories = [];
        this.activeCategory = '';
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadCategories();
        this.loadProducts();
        this.loadCartItems();
        this.startRealTimeClock();
    }

    bindEvents() {
        // Category pills
        document.addEventListener('click', (e) => {
            if (e.target.closest('.category-pill')) {
                const pill = e.target.closest('.category-pill');
                this.selectCategory(pill.dataset.category);
            }
        });

        // Search input
        document.getElementById('searchInput').addEventListener('input', 
            this.debounce(() => this.loadProducts(), 300));

        // Barcode scanner
        document.getElementById('barcodeBtn').addEventListener('click', () => {
            this.showModal('barcodeModal');
            setTimeout(() => document.getElementById('barcodeInput').focus(), 300);
        });

        // Barcode modal events
        document.getElementById('closeBarcodeModal').addEventListener('click', () => {
            this.hideModal('barcodeModal');
        });
        
        document.getElementById('cancelBarcodeBtn').addEventListener('click', () => {
            this.hideModal('barcodeModal');
        });

        document.getElementById('searchBarcodeBtn').addEventListener('click', () => {
            this.searchByBarcode();
        });

        document.getElementById('barcodeInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.searchByBarcode();
            }
        });

        // Cart actions
        document.getElementById('processPaymentBtn').addEventListener('click', () => {
            this.showPaymentModal();
        });

        // Remove reason modal
        document.getElementById('closeRemoveModal').addEventListener('click', () => {
            this.hideModal('removeReasonModal');
        });

        document.querySelectorAll('.remove-reason-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const reason = btn.dataset.reason;
                this.confirmRemoveItem(reason);
            });
        });

        // Payment modal
        document.getElementById('closePaymentModal').addEventListener('click', () => {
            this.hideModal('paymentModal');
        });

        document.getElementById('cancelPaymentBtn').addEventListener('click', () => {
            this.hideModal('paymentModal');
        });

        document.getElementById('completePaymentBtn').addEventListener('click', () => {
            this.processPayment();
        });

        document.getElementById('amountReceived').addEventListener('input', () => {
            this.calculateChange();
        });

        // Receipt modal
        document.getElementById('printReceiptBtn').addEventListener('click', () => {
            this.printReceipt();
        });

        document.getElementById('finishTransactionBtn').addEventListener('click', () => {
            window.location.href = '{{ route("store_transactions") }}';
        });

        // Close modals on outside click
        ['barcodeModal', 'removeReasonModal', 'paymentModal', 'receiptModal'].forEach(modalId => {
            document.getElementById(modalId).addEventListener('click', (e) => {
                if (e.target.id === modalId) {
                    this.hideModal(modalId);
                }
            });
        });

        // ESC key to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                ['barcodeModal', 'removeReasonModal', 'paymentModal', 'receiptModal'].forEach(modalId => {
                    this.hideModal(modalId);
                });
            }
        });
    }

    startRealTimeClock() {
        const updateDateTime = () => {
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
            const formattedDateTime = now.toLocaleString('en-US', options);
            document.getElementById('receiptDateTime').textContent = formattedDateTime;
        };
        
        // Update immediately
        updateDateTime();
        
        // Update every second
        setInterval(updateDateTime, 1000);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    async loadCategories() {
        try {
            const response = await fetch('{{ route("get_categories") }}');
            const data = await response.json();
            
            if (data.success) {
                this.categories = data.categories;
                this.renderCategoryPills();
            }
        } catch (error) {
            console.error('Error loading categories:', error);
            this.showToast('Error loading categories', 'error');
        }
    }

    renderCategoryPills() {
        const container = document.getElementById('categoryPills');

        const colors = [
            'red', 'blue', 'green', 'yellow', 
            'purple', 'pink', 'indigo', 'orange',
            'teal', 'cyan', 'lime', 'emerald',
            'violet', 'fuchsia', 'rose', 'skyblue',
            'gold', 'slategray'
        ];

        let pillsHTML = `
            <button class="category-pill px-4 py-2 rounded-full text-white mr-4"
                style="background-color: gray;"
                data-category="">
                All Categories
            </button>
        `;

        this.categories.forEach((category, index) => {
            const bgColor = colors[index % colors.length];
            pillsHTML += `
                <button class="category-pill px-4 py-2 rounded-full text-white mr-4"
                    style="background-color: ${bgColor};"
                    data-category="${category.category_id}">
                    ${category.category}
                </button>
            `;
        });

        container.innerHTML = pillsHTML;
    }




    selectCategory(categoryId) {
        this.activeCategory = categoryId;
        
        // Update active state
        document.querySelectorAll('.category-pill').forEach(pill => {
            pill.classList.remove('active');
        });
        
        document.querySelector(`[data-category="${categoryId}"]`).classList.add('active');
        
        // Load products for selected category
        this.loadProducts();
    }

    async loadProducts() {
        this.showLoading();
        
        try {
            const search = document.getElementById('searchInput').value;
            
            const params = new URLSearchParams();
            if (this.activeCategory) params.append('category_id', this.activeCategory);
            if (search) params.append('search', search);
            
            const response = await fetch(`{{ route("get_kiosk_products") }}?${params}`);
            const data = await response.json();
            
            if (data.success) {
                this.currentProducts = data.products;
                this.renderProducts();
            } else {
                this.showNoProducts();
            }
        } catch (error) {
            console.error('Error loading products:', error);
            this.showToast('Error loading products', 'error');
            this.showNoProducts();
        }
    }

    showLoading() {
        document.getElementById('loadingProducts').classList.remove('hidden');
        document.getElementById('productsGrid').classList.add('hidden');
        document.getElementById('noProducts').classList.add('hidden');
    }

    showNoProducts() {
        document.getElementById('loadingProducts').classList.add('hidden');
        document.getElementById('productsGrid').classList.add('hidden');
        document.getElementById('noProducts').classList.remove('hidden');
    }

    renderProducts() {
        const container = document.querySelector('#productsGrid .grid');
        container.innerHTML = '';

        if (this.currentProducts.length === 0) {
            this.showNoProducts();
            return;
        }

        this.currentProducts.forEach(product => {
            const productCard = this.createProductCard(product);
            container.appendChild(productCard);
        });

        document.getElementById('loadingProducts').classList.add('hidden');
        document.getElementById('noProducts').classList.add('hidden');
        document.getElementById('productsGrid').classList.remove('hidden');
    }

    createProductCard(product) {
        const card = document.createElement('div');
        const isOutOfStock = product.stock <= 0;
        const isLowStock = product.stock > 0 && product.stock <= product.stock_limit;
        
        let cardClass = 'product-card';
        if (isOutOfStock) cardClass += ' out-of-stock';
        else if (isLowStock) cardClass += ' low-stock';
        
        card.className = cardClass;
        card.dataset.prodCode = product.prod_code;

        const hasImage = product.prod_image && product.prod_image.trim() !== '';
        
        card.innerHTML = `
            <div class="product-image ${hasImage ? 'has-image' : ''}">
                ${hasImage ? 
                    `<img src="${product.prod_image}" alt="${product.name}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                     <div style="display:none;" class="w-full h-full flex items-center justify-center">
                         <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2z"></path>
                         </svg>
                     </div>` :
                    `<svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2z"></path>
                     </svg>`
                }
            </div>
            <div class="flex-1 flex flex-col justify-between">
                <h4 class="font-semibold text-[13px] text-gray-900 mb-2 line-clamp-2" title="${product.name}">
                    ${product.name}
                </h4>
                <div>
                    <p class="text-sm font-bold text-red-600 mb-2">₱${parseFloat(product.selling_price).toFixed(2)}</p>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">Stock: ${product.stock}</span>
                        ${isOutOfStock ? 
                            '<span class="text-red-500 font-medium px-2 py-1 bg-red-50 rounded">Out of Stock</span>' :
                            isLowStock ? '<span class="text-orange-500 font-medium px-2 py-1 bg-orange-50 rounded">Low Stock</span>' : 
                            '<span class="text-green-500 font-medium px-2 py-1 bg-green-50 rounded">Available</span>'
                        }
                    </div>
                </div>
            </div>
        `;

        if (!isOutOfStock) {
            card.addEventListener('click', () => this.addToCart(product.prod_code));
        }

        return card;
    }

    async addToCart(prodCode, quantity = 1) {
        try {
            const response = await fetch('{{ route("add_to_kiosk_cart") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    prod_code: prodCode,
                    quantity: quantity
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateCart(data.cart_items, data.cart_summary);
                this.showToast(data.message, 'success');
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showToast('Error adding item to cart', 'error');
        }
    }

    async updateCartQuantity(prodCode, quantity) {
        try {
            const response = await fetch('{{ route("update_cart_item") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    prod_code: prodCode,
                    quantity: quantity
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateCart(data.cart_items, data.cart_summary);
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Error updating cart:', error);
            this.showToast('Error updating cart', 'error');
        }
    }

    showRemoveModal(prodCode) {
        this.removeItemData = prodCode;
        this.showModal('removeReasonModal');
    }

    async confirmRemoveItem(reason) {
        if (!this.removeItemData) return;

        try {
            const response = await fetch('{{ route("remove_cart_item") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    prod_code: this.removeItemData,
                    reason: reason
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateCart(data.cart_items, data.cart_summary);
                this.showToast(data.message, 'success');
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Error removing item:', error);
            this.showToast('Error removing item', 'error');
        }

        this.hideModal('removeReasonModal');
        this.removeItemData = null;
    }

    async loadCartItems() {
        try {
            const response = await fetch('{{ route("get_cart_items") }}');
            const data = await response.json();
            
            if (data.success) {
                this.updateCart(data.cart_items, data.cart_summary);
            }
        } catch (error) {
            console.error('Error loading cart:', error);
        }
    }

    updateCart(items, summary) {
        this.cartItems = items;
        this.totalAmount = summary.total_amount;
        this.totalQuantity = summary.total_quantity;
        
        this.renderCart();
        this.updateCartSummary();
        this.updateButtons();
    }

    renderCart() {
        const container = document.getElementById('cartItems');
        
        if (this.cartItems.length === 0) {
            container.innerHTML = `
                <div id="emptyCart" class="text-center py-12">
                    <svg class="mx-auto mb-4 w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0H17M9 19a2 2 0 11-4 0 2 2 0 014 0zM20 19a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p class="text-gray-600 text-sm">Your cart is empty</p>
                    <p class="text-gray-500 text-xs">Tap on products to add them</p>
                </div>
            `;
            return;
        }

        container.innerHTML = this.cartItems.map(item => `
            <div class="cart-item" data-prod-code="${item.product.prod_code}">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1 pr-4">
                        <h4 class="font-semibold text-sm text-gray-900 leading-5 mb-1">${item.product.name}</h4>
                        <p class="text-xs text-gray-500">₱${parseFloat(item.product.selling_price).toFixed(2)} each</p>
                    </div>
                    <button class="remove-btn" onclick="kioskSystem.showRemoveModal('${item.product.prod_code}')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
                <div class="flex justify-between items-center">
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="kioskSystem.updateCartQuantity('${item.product.prod_code}', ${item.quantity - 1})" 
                                ${item.quantity <= 1 ? 'disabled' : ''}>−</button>
                        <span class="px-4 py-2 bg-gray-50 border rounded-lg text-xs font-medium min-w-[4rem] text-center">${item.quantity}</span>
                        <button class="quantity-btn" onclick="kioskSystem.updateCartQuantity('${item.product.prod_code}', ${item.quantity + 1})">+</button>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-sm text-red-600">₱${item.amount.toFixed(2)}</p>
                    </div>
                </div>
            </div>
        `).join('');
    }

    updateCartSummary() {
        document.getElementById('totalQuantity').textContent = this.totalQuantity;
        document.getElementById('totalAmount').textContent = `₱${this.totalAmount.toFixed(2)}`;
    }

    updateButtons() {
        const processBtn = document.getElementById('processPaymentBtn');

        processBtn.disabled = {{ $expired ? 'true' : 'false' }} || this.cartItems.length === 0;

        if (this.cartItems.length === 0) {
            processBtn.classList.add('opacity-50','cursor-not-allowed',
                {!! $expired ? "'cursor-not-allowed','hover:bg-red-500','disabled'" : "" !!}
            );
        } else {
            processBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    async searchByBarcode() {
        const barcode = document.getElementById('barcodeInput').value.trim();
        
        if (!barcode) {
            this.showToast('Please enter a barcode', 'error');
            return;
        }

        try {
            const response = await fetch('{{ route("process_barcode_search") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ barcode: barcode })
            });

            const data = await response.json();
            
            if (data.success) {
                this.hideModal('barcodeModal');
                document.getElementById('barcodeInput').value = '';
                await this.addToCart(data.product.prod_code);
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Error searching barcode:', error);
            this.showToast('Error searching barcode', 'error');
        }
    }

    calculateChange() {
        const amountReceived = parseFloat(document.getElementById('amountReceived').value) || 0;
        const change = amountReceived - this.totalAmount;
        
        const changeDisplay = document.getElementById('changeDisplay');
        const changeAmount = document.getElementById('changeAmount');
        const warningDisplay = document.getElementById('warningDisplay');
        const warningAmount = document.getElementById('warningAmount');
        const completeBtn = document.getElementById('completePaymentBtn');
        
        if (amountReceived > 0 && amountReceived < this.totalAmount) {
            const shortage = this.totalAmount - amountReceived;
            warningAmount.textContent = `₱${shortage.toFixed(2)}`;
            warningDisplay.classList.remove('hidden');
            changeDisplay.classList.add('hidden');
            completeBtn.disabled = true;
            completeBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else if (amountReceived >= this.totalAmount && amountReceived > 0) {
            changeAmount.textContent = `₱${change.toFixed(2)}`;
            changeDisplay.classList.remove('hidden');
            warningDisplay.classList.add('hidden');
            completeBtn.disabled = false;
            completeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            changeDisplay.classList.add('hidden');
            warningDisplay.classList.add('hidden');
            completeBtn.disabled = true;
            completeBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    async processPayment() {
        const amountReceived = parseFloat(document.getElementById('amountReceived').value) || 0;
        
        if (amountReceived <= 0) {
            this.showToast('Please enter a valid amount', 'error');
            document.getElementById('amountReceived').focus();
            return;
        }
        
        if (amountReceived < this.totalAmount) {
            this.showToast('Amount received is less than total amount due', 'error');
            document.getElementById('amountReceived').focus();
            return;
        }

        const completeBtn = document.getElementById('completePaymentBtn');
        const originalText = completeBtn.textContent;
        
        completeBtn.disabled = true;
        completeBtn.textContent = 'Processing...';
        completeBtn.classList.add('opacity-50');

        try {
            const response = await fetch('{{ route("process_payment") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    payment_method: 'cash',
                    amount_received: amountReceived
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showToast('Payment completed successfully!', 'success');
                // Hide payment modal and show receipt modal
                this.hideModal('paymentModal');
                this.showReceiptModal(data);
                
                // Reset cart
                this.cartItems = [];
                this.totalAmount = 0;
                this.totalQuantity = 0;
                this.renderCart();
                this.updateCartSummary();
                this.updateButtons();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Error processing payment:', error);
            this.showToast('Error processing payment', 'error');
        } finally {
            completeBtn.disabled = false;
            completeBtn.textContent = originalText;
            completeBtn.classList.remove('opacity-50');
        }
    }

    showReceiptModal(paymentData) {
        // Populate receipt data
        document.getElementById('receiptNumber').textContent = paymentData.receipt_id || '{{ $receipt_no ?? "0" }}';
        document.getElementById('receiptTotalItems').textContent = paymentData.total_quantity;
        document.getElementById('receiptTotalAmount').textContent = `₱${paymentData.total_amount.toFixed(2)}`;
        document.getElementById('receiptAmountPaid').textContent = `₱${paymentData.amount_received.toFixed(2)}`;
        document.getElementById('receiptChange').textContent = `₱${paymentData.change.toFixed(2)}`;
        
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
        
        // Populate items list using the receipt items from server response
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
            
            // Insert warning before footer message
            const footerDiv = itemsList.parentElement.querySelector('.text-center.mt-6');
            if (footerDiv) {
                footerDiv.parentElement.insertBefore(warningDiv, footerDiv);
            }
        }
        
        // Show the modal
        this.showModal('receiptModal');
    }

    printReceipt() {
        // Temporary function - not functional yet
        this.showToast('Print function will be implemented soon', 'info');
    }

    showPaymentModal() {
        if (this.cartItems.length === 0) {
            this.showToast('Cart is empty', 'error');
            return;
        }

        document.getElementById('paymentTotalQuantity').textContent = this.totalQuantity;
        document.getElementById('paymentTotalAmount').textContent = `₱${this.totalAmount.toFixed(2)}`;
        
        document.getElementById('amountReceived').value = '';
        document.getElementById('changeDisplay').classList.add('hidden');
        document.getElementById('warningDisplay').classList.add('hidden');
        
        const completeBtn = document.getElementById('completePaymentBtn');
        completeBtn.disabled = true;
        completeBtn.classList.add('opacity-50', 'cursor-not-allowed');
        
        this.showModal('paymentModal');
        setTimeout(() => document.getElementById('amountReceived').focus(), 300);
    }

    showModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.classList.add('modal-open');
    }

    hideModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const iconSvg = type === 'error' ? 
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>' :
            type === 'success' ? 
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>' :
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
        
        toast.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${iconSvg}
                </svg>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.add('show'), 10);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Initialize the kiosk system
let kioskSystem;
document.addEventListener('DOMContentLoaded', function() {
    kioskSystem = new KioskSystem();
});

// Global error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
});
</script>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection