@extends('dashboards.owner.owner')
@section('content')

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

<div class="px-4 mb-2">
    @livewire('expiration-container')
</div>

<!-- Rest of your existing blade content... -->
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

            <!-- Right Section - Report/Transactions Button -->
            <div class="ml-4 flex items-center space-x-3">
                @if(Auth::guard('owner')->check())
                    <!-- Owner: Report Button -->
                    <a href="{{ route('report-sales-performance') }}" 
                        class="px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors text-white"
                        style="background-color: #336055;"
                        onmouseover="this.style.backgroundColor='#2a4d44'"
                        onmouseout="this.style.backgroundColor='#336055'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span>Reports</span>
                    </a>
                @elseif(Auth::guard('staff')->check())
                    <!-- Staff: Transactions Button -->
                    <a href="{{ route('report-sales-performance') }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <span>Transactions</span>
                    </a>
                @endif
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

        <!-- Right Side - Cart Items -->
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
                        <p class="text-gray-500 text-xs">Scan products or tap to add them</p>
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

<style>
/* Existing styles... */
.category-pill {
    padding: 10px 12px;
    border-radius: 8px;
    border: 2px solid #e5e7eb;
    background-color: white;
    color: white;
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
    opacity: 0.9;
}

.category-pill.active {
    background-color: #ef4444;
    color: white;
    border-color: #ef4444;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.25);
}

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
    box-shadow: 0 2 4px rgba(0, 0, 0, 0.05);
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

/* Scanner Indicator Animation */
@keyframes slideUp {
    from {
        transform: translateY(100px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideDown {
    from {
        transform: translateY(0);
        opacity: 1;
    }
    to {
        transform: translateY(100px);
        opacity: 0;
    }
}

#scannerIndicator.show {
    animation: slideUp 0.3s ease-out forwards;
}

#scannerIndicator.hide {
    animation: slideDown 0.3s ease-in forwards;
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
        this.barcodeBuffer = '';
        this.barcodeTimeout = null;
        this.scannerActive = true;
        this.scannerDetected = false;
        this.scannerCheckTimeout = null;
        this.isExpired = {{ $expired ? 'true' : 'false' }};
        this.toastTimeout = null;
        this.toastProgressInterval = null;
        
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
        // Existing event bindings...
        document.addEventListener('click', (e) => {
            if (e.target.closest('.category-pill')) {
                const pill = e.target.closest('.category-pill');
                this.selectCategory(pill.dataset.category);
            }
        });

        document.getElementById('searchInput').addEventListener('input', 
            this.debounce(() => this.loadProducts(), 300));

        document.getElementById('processPaymentBtn').addEventListener('click', () => {
            if (this.isExpired) {
                this.showScannerToast(
                    'Subscription Expired',
                    'Your subscription has expired. Please renew to continue.',
                    'error',
                    3000
                );
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

        document.getElementById('closeRemoveModal').addEventListener('click', () => {
            this.hideModal('removeReasonModal');
        });

        document.querySelectorAll('.remove-reason-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const reason = btn.dataset.reason;
                this.confirmRemoveItem(reason);
            });
        });

        document.getElementById('removeReasonModal').addEventListener('click', (e) => {
            if (e.target.id === 'removeReasonModal') {
                this.hideModal('removeReasonModal');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideModal('removeReasonModal');
                this.hideScannerToast();
            }
        });
    }

    initializeBarcodeScanner() {
        console.log('%c[SCANNER]%c Initializing barcode scanner...', 
            'background: #3b82f6; color: white; padding: 2px 6px; border-radius: 3px; font-weight: bold',
            'color: #3b82f6');
        
        // Show scanner ready indicator
        this.showScannerIndicator();
        
        // Auto-hide scanner indicator after 3 seconds
        setTimeout(() => {
            this.hideScannerIndicator();
        }, 3000);

        // Check for scanner within 2 seconds of page load
        this.scannerCheckTimeout = setTimeout(() => {
            if (!this.scannerDetected) {
                console.log('%c⚠️%c Scanner not detected within timeout', 
                    'font-size: 16px',
                    'color: #f59e0b; margin-left: 5px');
                this.showScannerToast(
                    'Scanner Not Detected',
                    'Please connect your barcode scanner or select products manually from the grid.',
                    'warning',
                    3000
                );
            }
        }, 2000);

        // Listen for keypress events (barcode scanners simulate keyboard input)
        document.addEventListener('keypress', (e) => {
            // Ignore if user is typing in input fields
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }

            // Scanner detected!
            if (!this.scannerDetected) {
                this.scannerDetected = true;
                clearTimeout(this.scannerCheckTimeout);
                console.log('%c✓%c Scanner detected!', 
                    'color: #10b981; font-size: 16px; font-weight: bold',
                    'color: #10b981; margin-left: 5px');
                
                this.showScannerToast(
                    'Scanner Connected',
                    'Barcode scanner is ready. Start scanning products!',
                    'success',
                    2000
                );
            }

            // Clear previous timeout
            if (this.barcodeTimeout) {
                clearTimeout(this.barcodeTimeout);
            }

            // Add character to buffer
            if (e.key === 'Enter') {
                // Process the complete barcode
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
                
                // Set timeout to clear buffer if no more input
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
                
                this.showScannerToast(
                    'Product Added',
                    `${data.product.name} has been added to your cart.`,
                    'success',
                    2000
                );
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
        // Clear existing toast
        this.hideScannerToast();

        const toast = document.getElementById('scannerToast');
        const toastTitle = document.getElementById('toastTitle');
        const toastMessage = document.getElementById('toastMessage');
        const toastIcon = document.getElementById('toastIcon');
        const progressBar = document.querySelector('#toastProgress > div');

        // Set content
        toastTitle.textContent = title;
        toastMessage.textContent = message;

        // Set colors and icons based on type
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

        // Apply styles
        toast.className = `fixed top-4 right-4 z-[9999] transform transition-all duration-300`;
        toast.querySelector('.bg-white').className = `bg-white rounded-lg shadow-2xl ${config.borderColor} border-l-4 p-4 min-w-[320px] max-w-md`;
        toastTitle.className = `font-bold text-sm mb-1 ${config.titleColor}`;
        toastIcon.className = `flex-shrink-0 w-10 h-10 rounded-full ${config.iconBg} ${config.iconColor} flex items-center justify-center`;
        toastIcon.innerHTML = config.icon;
        progressBar.className = `h-full ${config.progressColor} transition-all duration-[${duration}ms] ease-linear w-0`;

        // Show toast with animation
        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        }, 10);

        // Start progress bar animation
        setTimeout(() => {
            progressBar.style.width = '100%';
        }, 50);

        // Auto hide after duration
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
        
        // Reset progress bar
        setTimeout(() => {
            progressBar.style.width = '0';
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

    selectCategory(categoryId) {
        this.activeCategory = categoryId;
        
        document.querySelectorAll('.category-pill').forEach(pill => {
            pill.classList.remove('active');
        });
        
        document.querySelector(`[data-category="${categoryId}"]`).classList.add('active');
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
            <div class="flex-1 flex flex-column justify-between">
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
                    <p class="text-gray-500 text-xs">Scan products or tap to add them</p>
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

// Initialize the kiosk system
let kioskSystem;
document.addEventListener('DOMContentLoaded', function() {
    kioskSystem = new KioskSystem();
    console.log('%c🚀%c Kiosk system initialized', 
        'font-size: 16px',
        'color: #10b981; margin-left: 5px; font-weight: 600');
});

// Global error handling
window.addEventListener('error', function(e) {
    console.error('%c✕%c JavaScript Error:', 
        'color: #ef4444; font-size: 16px; font-weight: bold',
        'color: #ef4444; margin-left: 5px',
        e.error);
});
</script>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection