@extends('dashboards.owner.owner')
@section('content')

<!-- Expired Product Warning Modal (Auto-dismiss) -->
<div id="expiredProductModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] hidden">
    <div class="bg-white rounded-lg p-6 w-96 max-w-md mx-4 shadow-2xl transform transition-all duration-300">
        <div class="flex items-center justify-center mb-4">
            <div class="bg-red-100 rounded-full p-3">
                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-. 77-.833-1.964-. 833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
        </div>
        <h3 class="text-xl font-bold text-center mb-2 text-gray-900">Product Unavailable</h3>
        <p id="expiredProductMessage" class="text-center text-gray-600 mb-4"></p>
        
        <!-- Progress bar showing auto-dismiss countdown -->
        <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
            <div id="expiredModalProgress" class="bg-red-600 h-full transition-all ease-linear" style="width: 100%;"></div>
        </div>
        <p class="text-center text-xs text-gray-500 mt-2">Auto-closing... </p>
    </div>
</div>

<!-- Scanner Status Toast (Auto-dismiss) -->
<div id="scannerToast" class="fixed top-4 right-4 z-[9999] transform transition-all duration-300 translate-x-full opacity-0">
    <div class="bg-white rounded-lg shadow-2xl border-l-4 p-4 min-w-[320px] max-w-md">
        <div class="flex items-start gap-3">
            <div id="toastIcon" class="flex-shrink-0"></div>
            <div class="flex-1">
                <h4 id="toastTitle" class="font-bold text-sm mb-1"></h4>
                <p id="toastMessage" class="text-xs text-gray-600"></p>
            </div>
            <button onclick="kioskSystem.hideScannerToast()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="toastProgress" class="mt-3 h-1 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full bg-current transition-all duration-[3000ms] ease-linear w-0"></div>
        </div>
    </div>
</div>

<!-- Scanner Ready Indicator -->
<div id="scannerIndicator" class="fixed bottom-4 left-4 z-50 bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-2 rounded-full shadow-lg flex items-center gap-2 transform transition-all duration-300 translate-y-20 opacity-0">
    <div class="relative">
        <div class="w-3 h-3 bg-white rounded-full animate-pulse"></div>
        <div class="absolute inset-0 w-3 h-3 bg-white rounded-full animate-ping"></div>
    </div>
    <span class="text-sm font-medium">Scanner Ready</span>
</div>

<!-- Confirm Remove Item Modal -->
<div id="confirmRemoveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 w-96 max-w-md mx-4 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900">Remove Item</h3>
            <button id="closeConfirmRemoveModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <p class="text-gray-700 mb-6">Are you sure you want to remove this item from the cart?</p>

        <div class="flex gap-3">
            <button id="cancelConfirmRemove" class="flex-1 px-4 py-2.5 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                Cancel
            </button>
            <button id="confirmRemoveBtn" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors">
                Remove
            </button>
        </div>
    </div>
</div>

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
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" placeholder="Search products by name or barcode" 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                </div>
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
                        All Categories
                    </button>
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

        <!-- Right Side - Cart Items -->
        <div class="bg-white rounded-lg shadow-lg flex flex-col" style="height: 560px;">
            <!-- Receipt Info Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-4 flex-shrink-0">
                <div class="text-center">
                    <p class="text-sm font-bold">Receipt No.: {{ $receipt_no ?? '0' }}</p>
                    <p id="receiptDateTime" class="text-xs text-red-100 mt-1"></p>
                    <p class="text-xs text-red-100">Cashier: {{ $user_firstname ?? 'User' }}</p>
                </div>
            </div>

            <!-- Cart Items - Flexible height to show 4-5 items -->
            <div class="flex-1 overflow-y-auto p-4 min-h-0">
                <div id="cartItems">
                    <!-- Empty Cart State -->
                    <div id="emptyCart" class="text-center py-8">
                        <svg class="mx-auto mb-4 w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0H17M9 19a2 2 0 11-4 0 2 2 0 014 0zM20 19a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-gray-600 text-sm">Your cart is empty</p>
                        <p class="text-gray-500 text-xs">Scan products or tap to add them</p>
                    </div>
                </div>
            </div>

            <!-- Cart Summary and Actions -->
            <div class="border-t p-4 space-y-3 flex-shrink-0 bg-gray-50">
                <!-- Summary -->
                <div class="bg-white p-3 rounded-lg shadow-sm border">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-medium text-gray-700">Total Quantity:</span>
                        <span id="totalQuantity" class="text-xs font-bold text-gray-900">0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-900">Total:</span>
                        <span id="totalAmount" class="text-sm font-bold text-red-600">₱0.00</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2">
                    <button id="processPaymentBtn" 
                             {{ $expired ? 'disabled' : '' }}
                            data-expired="{{ $expired ? 'true' : 'false' }}"
                            class="text-xs w-full bg-gradient-to-r from-red-600 to-red-700 text-white py-3 px-4 rounded-lg font-bold text-base transition-all duration-300 hover:from-red-700 hover:to-red-800 disabled:opacity-50 disabled:cursor-not-allowed {{ $expired ? 'cursor-not-allowed' : '' }}">
                        Process Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
    white-space: nowrap;
}

.category-pill:hover:not(.active) {
    transform: translateY(-1px);
    border-color: #ef4444;
    opacity: 0.9;
}

.category-pill.active {
    background-color: #ef4444;
    color: white;
    border-color: #ef4444;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.25);
    transform: scale(1.05);
}

.product-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    min-height: 220px;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: #f87171;
}

.product-card.out-of-stock {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #f9fafb;
}

.product-card.expired {
    opacity: 0.4;
    cursor: not-allowed;
    background-color: #fef2f2;
    border-color: #dc2626;
}

.product-card.out-of-stock:hover,
.product-card.expired:hover {
    transform: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.product-card.low-stock {
    border-color: #fbbf24;
    background-color: #fffbeb;
}

.product-image {
    width: 100%;
    height: 100px;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border-radius: 8px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px dashed #d1d5db;
    overflow: hidden;
    flex-shrink: 0;
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

.product-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 0;
}

.product-name {
    font-weight: 600;
    font-size: 13px;
    color: #111827;
    margin-bottom: 8px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 36px;
}

.product-price {
    font-size: 15px;
    font-weight: 700;
    color: #dc2626;
    margin-bottom: 8px;
}

.product-stock-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
    margin-top: auto;
}

.stock-label {
    color: #6b7280;
    font-weight: 500;
}

.stock-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stock-badge.available {
    background-color: #d1fae5;
    color: #065f46;
}

.stock-badge.low {
    background-color: #fed7aa;
    color: #92400e;
}

.stock-badge.out {
    background-color: #fee2e2;
    color: #991b1b;
}

.stock-badge.expired {
    background-color: #fecaca;
    color: #7f1d1d;
}

.cart-item {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 8px;
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

/* ✅ NEW: Compact cart item text */
.cart-item h4 {
    font-size: 0.875rem;
    line-height: 1.25rem;
    margin-bottom: 4px;
}

.cart-item.text-xs {
    font-size: 0.75rem;
}

/* Highlight animation for newly added items */
.cart-item.newly-added {
    animation: highlightPulse 1s ease-in-out;
    border-color: #10b981;
    background: linear-gradient(to right, #d1fae5 0%, #ffffff 100%);
}

@keyframes highlightPulse {
    0% {
        transform: scale(1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    25% {
        transform: scale(1.02);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    }
    50% {
        transform: scale(1);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
}

/* Expired modal animations */
#expiredProductModal.bg-white {
    transform: scale(0.95);
    opacity: 0;
    transition: all 0.3s ease-out;
}

#expiredProductModal.show.bg-white {
    transform: scale(1);
    opacity: 1;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.quantity-btn {
    width: 28px;
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
    font-size: 14px;
}

.quantity-btn:hover:not(:disabled) {
    background-color: #dc2626;
}

.quantity-btn:disabled {
    background-color: #d1d5db;
    cursor: not-allowed;
}

.quantity-display {
    padding: 8px 12px;
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    text-align: center;
    font-weight: 600;
    min-width: 60px;
}

.quantity-input {
    width: 60px;
    padding: 6px 4px;
    background-color: #ffffff;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    text-align: center;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s;
    -moz-appearance: textfield;
}

.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.quantity-input:focus {
    outline: none;
    border-color: #ef4444;
    background-color: #fffbeb;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.quantity-input:hover:not(:focus) {
    border-color: #d1d5db;
    background-color: #f9fafb;
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

@keyframes spin {
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

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

body.modal-open {
    overflow: hidden;
}

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
        this.activeCategoryName = 'All Categories';
        this.barcodeBuffer = '';
        this.barcodeTimeout = null;
        this.scannerActive = true;
        this.scannerDetected = false;
        this.scannerCheckTimeout = null;
        this.isExpired = {{ $expired ? 'true' : 'false' }};
        this.toastTimeout = null;
        this.toastProgressInterval = null;
        this.lastAddedProdCode = null;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadCategories();
        this.loadProducts();
        this.loadCartItems();
        this.startRealTimeClock();
        this.initializeBarcodeScanner();
    }

    bindEvents() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.category-pill')) {
                const pill = e.target.closest('.category-pill');
                const categoryName = pill.textContent.trim();
                this.selectCategory(pill.dataset.category, categoryName);
            }
        });

        document.getElementById('searchInput').addEventListener('input', 
            this.debounce(() => this.loadProducts(), 300));

        document.getElementById('processPaymentBtn').addEventListener('click', () => {
            if (this.isExpired) {
                this.showExpiredModal('Subscription Expired', 'Your subscription has expired. Please renew to continue.');
                return;
            }
            
            if (this.cartItems.length === 0) {
                this.showScannerToast(
                    'Cart Empty',
                    'Please add items to cart before processing payment.',
                    'warning',
                    3000
                );
                return;
            }
            
            window.location.href = '{{ route("store_payment_processor") }}';
        });

        // Confirm Remove Modal Events
        document.getElementById('closeConfirmRemoveModal').addEventListener('click', () => {
            this.hideModal('confirmRemoveModal');
            this.removeItemData = null;
        });

        document.getElementById('cancelConfirmRemove').addEventListener('click', () => {
            this.hideModal('confirmRemoveModal');
            this.removeItemData = null;
        });

        document.getElementById('confirmRemoveBtn').addEventListener('click', () => {
            this.confirmRemoveItem();
        });

        // Close modals on click outside
        document.getElementById('confirmRemoveModal').addEventListener('click', (e) => {
            if (e.target.id === 'confirmRemoveModal') {
                this.hideModal('confirmRemoveModal');
                this.removeItemData = null;
            }
        });

        document.getElementById('expiredProductModal').addEventListener('click', (e) => {
            if (e.target.id === 'expiredProductModal') {
                this.hideModal('expiredProductModal');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideModal('confirmRemoveModal');
                this.hideExpiredModal();
                this.hideScannerToast();
                this.removeItemData = null;
            }
        });
    }

    initializeBarcodeScanner() {
        console.log('%c[SCANNER]%c Initializing barcode scanner...', 
            'background: #3b82f6; color: white; padding: 2px 6px; border-radius: 3px; font-weight: bold',
            'color: #3b82f6');
        
        this.showScannerIndicator();
        
        setTimeout(() => {
            this.hideScannerIndicator();
        }, 3000);

        this.scannerCheckTimeout = setTimeout(() => {
            if (!this.scannerDetected) {
                console.log('%c⚠️%c Scanner not detected within timeout', 
                    'font-size: 16px',
                    'color: #f59e0b; margin-left: 5px');
            }
        }, 2000);

        document.addEventListener('keypress', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }

            if (!this.scannerDetected) {
                this.scannerDetected = true;
                clearTimeout(this.scannerCheckTimeout);
                console.log('%c✓%c Scanner detected!', 
                    'color: #10b981; font-size: 16px; font-weight: bold',
                    'color: #10b981; margin-left: 5px');
            }

            if (this.barcodeTimeout) {
                clearTimeout(this.barcodeTimeout);
            }

            if (e.key === 'Enter') {
                if (this.barcodeBuffer.length > 0) {
                    console.log('%c📋%c Barcode scanned: %c' + this.barcodeBuffer, 
                        'font-size: 16px',
                        'color: #6366f1; margin-left: 5px',
                        'background: #f3f4f6; padding: 2px 6px; border-radius: 3px; font-family: monospace');
                    this.processScannedBarcode(this.barcodeBuffer);
                    this.barcodeBuffer = '';
                }
            } else {
                this.barcodeBuffer += e.key;
                
                this.barcodeTimeout = setTimeout(() => {
                    this.barcodeBuffer = '';
                }, 100);
            }
        });
    }

    async processScannedBarcode(barcode) {
        if (!this.scannerActive || !barcode.trim()) {
            return;
        }

        console.log('%c🔍%c Processing barcode: %c' + barcode, 
            'font-size: 16px',
            'color: #8b5cf6; margin-left: 5px',
            'background: #f3f4f6; padding: 2px 6px; border-radius: 3px; font-family: monospace');

        try {
            const response = await fetch('{{ route("process_barcode_search") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ barcode: barcode.trim() })
            });

            const data = await response.json();
            
            if (data.success) {
                console.log('%c✓%c Product found: %c' + data.product.name, 
                    'color: #10b981; font-size: 16px; font-weight: bold',
                    'color: #10b981; margin-left: 5px',
                    'background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 3px; font-weight: 600');
                await this.addToCart(data.product.prod_code);
            } else {
                console.log('%c✕%c Product not found for barcode: %c' + barcode, 
                    'color: #ef4444; font-size: 16px; font-weight: bold',
                    'color: #ef4444; margin-left: 5px',
                    'background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 3px; font-family: monospace');
                this.showScannerToast(
                    'Product Not Found',
                    `No product found with barcode: ${barcode}`,
                    'error',
                    3000
                );
            }
        } catch (error) {
            console.error('%c✕%c Error processing scanned barcode:', 
                'color: #ef4444; font-size: 16px; font-weight: bold',
                'color: #ef4444; margin-left: 5px',
                error);
            this.showScannerToast(
                'Scan Error',
                'Failed to process barcode. Please try again.',
                'error',
                3000
            );
        }
    }

    showScannerIndicator() {
        const indicator = document.getElementById('scannerIndicator');
        indicator.classList.remove('translate-y-20', 'opacity-0');
        indicator.classList.add('show');
    }

    hideScannerIndicator() {
        const indicator = document.getElementById('scannerIndicator');
        indicator.classList.add('translate-y-20', 'opacity-0');
        indicator.classList.remove('show');
    }

    showScannerToast(title, message, type = 'info', duration = 3000) {
        this.hideScannerToast();

        const toast = document.getElementById('scannerToast');
        const toastTitle = document.getElementById('toastTitle');
        const toastMessage = document.getElementById('toastMessage');
        const toastIcon = document.getElementById('toastIcon');
        const progressBar = document.querySelector('#toastProgress > div');

        toastTitle.textContent = title;
        toastMessage.textContent = message;

        const configs = {
            success: {
                borderColor: 'border-green-500',
                titleColor: 'text-green-800',
                iconBg: 'bg-green-100',
                iconColor: 'text-green-600',
                progressColor: 'bg-green-500',
                icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            },
            error: {
                borderColor: 'border-red-500',
                titleColor: 'text-red-800',
                iconBg: 'bg-red-100',
                iconColor: 'text-red-600',
                progressColor: 'bg-red-500',
                icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>'
            },
            warning: {
                borderColor: 'border-orange-500',
                titleColor: 'text-orange-800',
                iconBg: 'bg-orange-100',
                iconColor: 'text-orange-600',
                progressColor: 'bg-orange-500',
                icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>'
            },
            info: {
                borderColor: 'border-blue-500',
                titleColor: 'text-blue-800',
                iconBg: 'bg-blue-100',
                iconColor: 'text-blue-600',
                progressColor: 'bg-blue-500',
                icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            }
        };

        const config = configs[type] || configs.info;

        toast.className = `fixed top-4 right-4 z-[9999] transform transition-all duration-300`;
        toast.querySelector('.bg-white').className = `bg-white rounded-lg shadow-2xl ${config.borderColor} border-l-4 p-4 min-w-[320px] max-w-md`;
        toastTitle.className = `font-bold text-sm mb-1 ${config.titleColor}`;
        toastIcon.className = `flex-shrink-0 w-10 h-10 rounded-full ${config.iconBg} ${config.iconColor} flex items-center justify-center`;
        toastIcon.innerHTML = config.icon;
        progressBar.className = `h-full ${config.progressColor} transition-all duration-[${duration}ms] ease-linear w-0`;

        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        }, 10);

        setTimeout(() => {
            progressBar.style.width = '100%';
        }, 50);

        this.toastTimeout = setTimeout(() => {
            this.hideScannerToast();
        }, duration);
    }

    hideScannerToast() {
        const toast = document.getElementById('scannerToast');
        const progressBar = document.querySelector('#toastProgress > div');
        
        if (this.toastTimeout) {
            clearTimeout(this.toastTimeout);
            this.toastTimeout = null;
        }

        toast.classList.remove('translate-x-0', 'opacity-100');
        toast.classList.add('translate-x-full', 'opacity-0');
        
        setTimeout(() => {
            progressBar.style.width = '0';
        }, 300);
    }

    showExpiredModal(title, message, duration = 1500) {
    document.getElementById('expiredProductMessage').textContent = message;
    
    const modal = document.getElementById('expiredProductModal');
    const progressBar = document.getElementById('expiredModalProgress');
    
    // Show modal with fade-in animation
    modal.classList. remove('hidden');
    setTimeout(() => {
        modal.querySelector('.bg-white').style.transform = 'scale(1)';
        modal. querySelector('.bg-white').style. opacity = '1';
    }, 10);
    
    // Reset progress bar
    progressBar.style.transition = 'none';
    progressBar.style.width = '100%';
    
    // Start countdown animation
    setTimeout(() => {
        progressBar. style.transition = `width ${duration}ms linear`;
        progressBar.style. width = '0%';
    }, 50);
    
    // Auto-dismiss after duration
    setTimeout(() => {
        this.hideExpiredModal();
    }, duration);
}

// Add new method for hiding with animation
hideExpiredModal() {
    const modal = document.getElementById('expiredProductModal');
    const modalContent = modal.querySelector('.bg-white');
    
    // Fade out animation
    modalContent.style.transform = 'scale(0. 95)';
    modalContent.style.opacity = '0';
    
    setTimeout(() => {
        modal.classList.add('hidden');
        // Reset for next time
        modalContent.style.transform = 'scale(1)';
        modalContent.style.opacity = '1';
    }, 300);
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
        
        updateDateTime();
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
            '#dc2626', '#2563eb', '#16a34a', '#ca8a04', '#9333ea', '#db2777',
            '#4f46e5', '#ea580c', '#0d9488', '#0891b2', '#65a30d', '#059669',
            '#7c3aed', '#c026d3', '#e11d48', '#0284c7', '#ca8a04', '#475569'
        ];

        let pillsHTML = `
            <button class="category-pill active" data-category="">
                All Categories
            </button>
        `;

        this.categories.forEach((category, index) => {
            const bgColor = colors[index % colors.length];
            pillsHTML += `
                <button class="category-pill" 
                    style="background-color: ${bgColor}; color: white; border-color: ${bgColor};"
                    data-category="${category.category_id}">
                    ${category.category}
                </button>
            `;
        });

        container.innerHTML = pillsHTML;
    }

    selectCategory(categoryId, categoryName) {
        this.activeCategory = categoryId;
        this.activeCategoryName = categoryName;
        
        document.querySelectorAll('.category-pill').forEach(pill => {
            pill.classList.remove('active');
        });
        
        const selectedPill = document.querySelector(`[data-category="${categoryId}"]`);
        if (selectedPill) {
            selectedPill.classList.add('active');
        }
        
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
    const hasExpiredOnly = product.has_expired_only || false;
    
    // 🔍 DEBUG: Log image URL to console
    console.log('===================');
    console.log('Product:', product.name);
    console.log('Image URL:', product.prod_image);
    console.log('Has Image:', product. prod_image ? 'YES' : 'NO');
    console.log('===================');
    
    let cardClass = 'product-card';
    let stockBadge = '';
    let stockBadgeClass = 'stock-badge';
    
    if (isOutOfStock) {
        if (hasExpiredOnly) {
            cardClass += ' expired';
            stockBadge = 'Expired';
            stockBadgeClass += ' expired';
        } else {
            cardClass += ' out-of-stock';
            stockBadge = 'Out of Stock';
            stockBadgeClass += ' out';
        }
    } else if (isLowStock) {
        cardClass += ' low-stock';
        stockBadge = 'Low Stock';
        stockBadgeClass += ' low';
    } else {
        stockBadge = 'Available';
        stockBadgeClass += ' available';
    }
    
    card.className = cardClass;
    card.dataset.prodCode = product.prod_code;

    const hasImage = product.prod_image && product.prod_image.trim() !== '';
    
    card.innerHTML = `
        <div class="product-image ${hasImage ? 'has-image' : ''}">
            ${hasImage ? 
                `<img src="${product.prod_image}" alt="${product.name}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'; console.error('Failed to load image:', '${product.prod_image}');">
                 <div style="display:none;" class="w-full h-full flex items-center justify-center">
                     <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012. 828 0L20 14m-6-6h.01M6 2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2z"></path>
                     </svg>
                 </div>` :
                `<svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2z"></path>
                 </svg>`
            }
        </div>
        <div class="product-info">
            <h4 class="product-name" title="${product.name}">
                ${product.name}
            </h4>
            <div>
                <p class="product-price">₱${parseFloat(product.selling_price).toFixed(2)}</p>
                <div class="product-stock-info">
                    <span class="stock-label">Stock: ${product.stock}</span>
                    <span class="${stockBadgeClass}">${stockBadge}</span>
                </div>
            </div>
        </div>
    `;

    if (! isOutOfStock && !hasExpiredOnly) {
        card.addEventListener('click', () => this.addToCart(product.prod_code));
        card.style.cursor = 'pointer';
    } else {
        card.addEventListener('click', () => {
            if (hasExpiredOnly) {
                this. showExpiredModal(
                    'Product Expired',
                    `${product.name} only has expired stock available and cannot be sold.`
                );
            } else {
                this.showExpiredModal(
                    'Out of Stock',
                    `${product.name} is currently out of stock. `
                );
            }
        });
        card.style.cursor = 'not-allowed';
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
            body: JSON. stringify({
                prod_code: prodCode,
                quantity: quantity
            })
        });

        const data = await response.json();
        
        if (data.success) {
            this.lastAddedProdCode = data.newly_added_prod_code || prodCode;
            this.updateCart(data.cart_items, data.cart_summary);
        } else if (data.expired_product) {
            this.showExpiredModal(
                'Expired Product',
                data.message || 'This product only has expired stock and cannot be sold.'
            );
        } else {
            // Toast for other errors
            this.showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        this. showToast('Error adding item to cart', 'error');
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
        this.showModal('confirmRemoveModal');
    }

    async confirmRemoveItem() {
        if (!this.removeItemData) return;

        try {
            const response = await fetch('{{ route("remove_cart_item") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    prod_code: this.removeItemData
                })
            });

            const data = await response.json();

            if (data.success) {
                this.updateCart(data.cart_items, data.cart_summary);
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Error removing item:', error);
            this.showToast('Error removing item', 'error');
        } finally {
            this.hideModal('confirmRemoveModal');
            this.removeItemData = null;
        }
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
                    <p class="text-gray-500 text-xs">Scan products or tap to add them</p>
                </div>
            `;
            return;
        }

        container.innerHTML = this.cartItems.map(item => {
            const isNewlyAdded = this.lastAddedProdCode && this.lastAddedProdCode === item.product.prod_code;
            return `
            <div class="cart-item ${isNewlyAdded ? 'newly-added' : ''}" data-prod-code="${item.product.prod_code}">
                <div class="flex justify-between items-start mb-3">
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
                        <input 
                            type="number" 
                            class="quantity-input" 
                            value="${item.quantity}"
                            min="1"
                            max="${item.current_stock}"
                            data-prod-code="${item.product.prod_code}"
                            onchange="kioskSystem.handleQuantityInputChange(this)"
                            onkeypress="if(event.key === 'Enter') this.blur()"
                        >
                        <button class="quantity-btn" onclick="kioskSystem.updateCartQuantity('${item.product.prod_code}', ${item.quantity + 1})" 
                                ${item.quantity >= item.current_stock ? 'disabled' : ''}>+</button>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-sm text-red-600">₱${item.amount.toFixed(2)}</p>
                    </div>
                </div>
            </div>
            `;
        }).join('');

        // Auto-scroll to newly added item
        if (this.lastAddedProdCode) {
            setTimeout(() => {
                const newItem = container.querySelector(`[data-prod-code="${this.lastAddedProdCode}"]`);
                if (newItem) {
                    newItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                
                // Remove highlight class after animation
                setTimeout(() => {
                    const items = container.querySelectorAll('.cart-item.newly-added');
                    items.forEach(item => item.classList.remove('newly-added'));
                    this.lastAddedProdCode = null;
                }, 1000);
            }, 100);
        }
    }

    handleQuantityInputChange(input) {
        const prodCode = input.dataset.prodCode;
        let newQuantity = parseInt(input.value);
        const max = parseInt(input.max);
        const min = parseInt(input.min);

        if (isNaN(newQuantity) || newQuantity < min) {
            newQuantity = min;
            input.value = min;
        } else if (newQuantity > max) {
            newQuantity = max;
            input.value = max;
            this.showToast(`Maximum available stock is ${max}`, 'error');
        }

        this.updateCartQuantity(prodCode, newQuantity);
    }

    updateCartSummary() {
        document.getElementById('totalQuantity').textContent = this.totalQuantity;
        document.getElementById('totalAmount').textContent = `₱${this.totalAmount.toFixed(2)}`;
    }

    updateButtons() {
        const processBtn = document.getElementById('processPaymentBtn');
        if (!processBtn) return;

        const shouldDisable = this.isExpired || this.cartItems.length === 0;
        
        processBtn.disabled = shouldDisable;

        if (shouldDisable) {
            processBtn.classList.add('opacity-50', 'cursor-not-allowed');
            processBtn.classList.remove('hover:from-red-700', 'hover:to-red-800');
        } else {
            processBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            processBtn.classList.add('hover:from-red-700', 'hover:to-red-800');
        }
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

let kioskSystem;
document.addEventListener('DOMContentLoaded', function() {
    kioskSystem = new KioskSystem();
    console.log('%c🚀%c Kiosk system initialized', 
        'font-size: 16px',
        'color: #10b981; margin-left: 5px; font-weight: 600');
});

window.addEventListener('error', function(e) {
    console.error('%c✕%c JavaScript Error:', 
        'color: #ef4444; font-size: 16px; font-weight: bold',
        'color: #ef4444; margin-left: 5px',
        e.error);
});
</script>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection