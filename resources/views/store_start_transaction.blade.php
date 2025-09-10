@extends('dashboards.owner.owner')
@section('content')
<div class="min-h-screen bg-gray-100 p-4">
    <!-- Top Navigation Bar -->
    <div class="bg-white shadow-lg rounded-lg mb-4 p-4">
        <div class="flex items-center justify-between">
            <!-- Left Section - Filters and Search -->
            <div class="flex items-center space-x-4 flex-1">
                <!-- Category Filter -->
                <div class="relative">
                    <select id="categoryFilter" class="appearance-none bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 p-2.5 pr-8">
                        <option value="">All Categories</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <i class="fas fa-chevron-down text-sm"></i>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Search products by name or barcode..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <!-- Right Section - Barcode Scanner Button -->
            <div class="ml-4">
                <button id="barcodeBtn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                    <i class="fas fa-barcode"></i>
                    <span>Scanner</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex gap-4" style="height: calc(100vh - 180px);">
        <!-- Left Side - Product Grid -->
        <div class="flex-1 bg-white rounded-lg shadow-lg p-4 overflow-hidden">
            <div class="h-full flex flex-col">
                <!-- Loading State -->
                <div id="loadingProducts" class="flex-1 flex items-center justify-center">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600 mx-auto mb-4"></div>
                        <p class="text-gray-600">Loading products...</p>
                    </div>
                </div>

                <!-- Products Grid -->
                <div id="productsGrid" class="hidden h-full overflow-y-auto">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-2"></div>
                </div>

                <!-- No Products Found -->
                <div id="noProducts" class="hidden flex-1 flex items-center justify-center">
                    <div class="text-center">
                        <i class="fas fa-search text-6xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600 text-lg">No products found</p>
                        <p class="text-gray-500 text-sm">Try adjusting your search or category filter</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Cart Items -->
        <div class="w-80 bg-white rounded-lg shadow-lg flex flex-col max-h-full">
            <!-- Receipt Info Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-4">
                <div class="text-center">
                    <p class="text-lg font-bold text-center">Receipt No.: {{ $receipt_no ?? '0' }}</p>
                    <p class="text-xs text-center-600">{{ now()->format('m/d/Y h:i:s A') }}</p>
                    <p class="text-xs text-center-600">User: {{ $user_firstname ?? 'User' }}</p>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4 min-h-0">
                <div id="cartItems">
                    <!-- Empty Cart State -->
                    <div id="emptyCart" class="text-center py-8">
                        <i class="fas fa-shopping-cart text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">Your cart is empty</p>
                        <p class="text-gray-500 text-sm">Tap on products to add them</p>
                    </div>
                </div>
            </div>

            <!-- Cart Summary and Actions -->
            <div class="border-t p-4 space-y-4 flex-shrink-0">
                <!-- Summary -->
                <div class="bg-gray-50 p-3 rounded-lg">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Total Items:</span>
                        <span id="totalQuantity" class="text-sm font-bold">0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-900">Total:</span>
                        <span id="totalAmount" class="text-lg font-bold text-red-600">₱0.00</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2">
                    <button id="processPaymentBtn" disabled 
                            class="w-full bg-gradient-to-r from-red-600 to-red-700 text-white py-3 px-4 rounded-lg font-bold text-lg transition-all duration-300 hover:from-red-700 hover:to-red-800 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-credit-card mr-2"></i>
                        Process Payment
                    </button>
                    <button id="clearCartBtn" disabled 
                            class="w-full bg-gray-500 text-white py-2 px-4 rounded-lg font-medium hover:bg-gray-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Clear All
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
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="space-y-4">
            <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                <i class="fas fa-camera text-4xl text-gray-400 mb-2"></i>
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
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="space-y-4">
            <p class="text-gray-700">Please select a reason for removing this item:</p>
            <div class="space-y-2">
                <button class="remove-reason-btn w-full text-left p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors" data-reason="cancel">
                    <i class="fas fa-ban mr-2 text-orange-500"></i>
                    Cancel Item
                </button>
                <button class="remove-reason-btn w-full text-left p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors" data-reason="return">
                    <i class="fas fa-undo mr-2 text-blue-500"></i>
                    Return Item
                </button>
                <button class="remove-reason-btn w-full text-left p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors" data-reason="damage">
                    <i class="fas fa-exclamation-triangle mr-2 text-red-500"></i>
                    Damaged Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 w-96 max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Process Payment</h3>
            <button id="closePaymentModal" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
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
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
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
.product-card {
    @apply bg-white border-2 border-gray-200 rounded-lg p-4 cursor-pointer transition-all duration-200 hover:shadow-lg hover:border-red-300;
}

.product-card:hover {
    @apply transform scale-105;
}

.product-card.out-of-stock {
    @apply opacity-50 cursor-not-allowed bg-gray-50;
}

.product-card.low-stock {
    @apply border-orange-300 bg-orange-50;
}

.product-image {
    @apply w-full h-24 bg-gray-100 rounded-lg mb-3 flex items-center justify-center border-2 border-dashed border-gray-300 overflow-hidden;
}

.product-image img {
    @apply w-full h-full object-cover rounded-lg;
}

.cart-item {
    @apply border border-gray-200 rounded-lg p-3 mb-2 bg-gray-50;
}

.quantity-controls {
    @apply flex items-center space-x-2;
}

.quantity-btn {
    @apply w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center hover:bg-red-700 transition-colors text-sm font-bold;
}

.quantity-btn:disabled {
    @apply bg-gray-300 cursor-not-allowed;
}

.remove-btn {
    @apply text-red-500 hover:text-red-700 p-1 rounded transition-colors;
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
    @apply fixed top-4 right-4 px-6 py-4 rounded-lg text-white z-50 transition-all duration-300 shadow-lg;
    transform: translateX(100%);
}

.toast.show {
    transform: translateX(0);
}

.toast.success {
    @apply bg-green-500;
}

.toast.error {
    @apply bg-red-500;
}

.toast.info {
    @apply bg-blue-500;
}

/* Modal backdrop blur */
body.modal-open {
    overflow: hidden;
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
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadCategories();
        this.loadProducts();
        this.loadCartItems();
    }

    bindEvents() {
        // Category filter
        document.getElementById('categoryFilter').addEventListener('change', () => {
            this.loadProducts();
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

        document.getElementById('clearCartBtn').addEventListener('click', () => {
            if (confirm('Are you sure you want to clear all items?')) {
                this.clearCart();
            }
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

        // Close modals on outside click
        ['barcodeModal', 'removeReasonModal', 'paymentModal'].forEach(modalId => {
            document.getElementById(modalId).addEventListener('click', (e) => {
                if (e.target.id === modalId) {
                    this.hideModal(modalId);
                }
            });
        });

        // ESC key to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                ['barcodeModal', 'removeReasonModal', 'paymentModal'].forEach(modalId => {
                    this.hideModal(modalId);
                });
            }
        });
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
                const select = document.getElementById('categoryFilter');
                select.innerHTML = '<option value="">All Categories</option>';
                
                data.categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.category_id;
                    option.textContent = category.category;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading categories:', error);
            this.showToast('Error loading categories', 'error');
        }
    }

    async loadProducts() {
        this.showLoading();
        
        try {
            const categoryId = document.getElementById('categoryFilter').value;
            const search = document.getElementById('searchInput').value;
            
            const params = new URLSearchParams();
            if (categoryId) params.append('category_id', categoryId);
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

        card.innerHTML = `
            <div class="product-image">
                ${product.prod_image ? 
                    `<img src="${product.prod_image}" alt="${product.name}">` :
                    `<i class="fas fa-box text-3xl text-gray-400"></i>`
                }
            </div>
            <h4 class="font-medium text-sm text-gray-900 mb-2 truncate" title="${product.name}">
                ${product.name}
            </h4>
            <p class="text-lg font-bold text-red-600 mb-2">₱${parseFloat(product.cost_price).toFixed(2)}</p>
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Stock: ${product.stock}</span>
                ${isOutOfStock ? 
                    '<span class="text-red-500 font-medium">Out of Stock</span>' :
                    isLowStock ? '<span class="text-orange-500 font-medium">Low Stock</span>' : ''
                }
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
                <div id="emptyCart" class="text-center py-8">
                    <i class="fas fa-shopping-cart text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600">Your cart is empty</p>
                    <p class="text-gray-500 text-sm">Tap on products to add them</p>
                </div>
            `;
            return;
        }

        container.innerHTML = this.cartItems.map(item => `
            <div class="cart-item" data-prod-code="${item.product.prod_code}">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-medium text-sm text-gray-900 flex-1 mr-2">${item.product.name}</h4>
                    <button class="remove-btn" onclick="kioskSystem.showRemoveModal('${item.product.prod_code}')">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
                <div class="flex justify-between items-center">
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="kioskSystem.updateCartQuantity('${item.product.prod_code}', ${item.quantity - 1})" 
                                ${item.quantity <= 1 ? 'disabled' : ''}>−</button>
                        <span class="px-3 py-1 bg-white border rounded text-sm font-medium">${item.quantity}</span>
                        <button class="quantity-btn" onclick="kioskSystem.updateCartQuantity('${item.product.prod_code}', ${item.quantity + 1})">+</button>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">₱${parseFloat(item.product.cost_price).toFixed(2)} each</p>
                        <p class="font-bold text-red-600">₱${item.amount.toFixed(2)}</p>
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
        const clearBtn = document.getElementById('clearCartBtn');
        
        processBtn.disabled = this.cartItems.length === 0;
        clearBtn.disabled = this.cartItems.length === 0;
    }

    async clearCart() {
        try {
            const response = await fetch('{{ route("clear_kiosk_cart") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateCart([], { total_amount: 0, total_quantity: 0 });
                this.showToast(data.message, 'success');
            }
        } catch (error) {
            console.error('Error clearing cart:', error);
            this.showToast('Error clearing cart', 'error');
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

    // Update the calculateChange method to include warning display
    calculateChange() {
    const amountReceived = parseFloat(document.getElementById('amountReceived').value) || 0;
    const change = amountReceived - this.totalAmount;
    
    const changeDisplay = document.getElementById('changeDisplay');
    const changeAmount = document.getElementById('changeAmount');
    const warningDisplay = document.getElementById('warningDisplay');
    const warningAmount = document.getElementById('warningAmount');
    const completeBtn = document.getElementById('completePaymentBtn');
    
    if (amountReceived > 0 && amountReceived < this.totalAmount) {
        // Show insufficient amount warning
        const shortage = this.totalAmount - amountReceived;
        warningAmount.textContent = `₱${shortage.toFixed(2)}`;
        warningDisplay.classList.remove('hidden');
        changeDisplay.classList.add('hidden');
        completeBtn.disabled = true;
        completeBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else if (amountReceived >= this.totalAmount && amountReceived > 0) {
        // Show change
        changeAmount.textContent = `₱${change.toFixed(2)}`;
        changeDisplay.classList.remove('hidden');
        warningDisplay.classList.add('hidden');
        completeBtn.disabled = false;
        completeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        // No amount entered or zero
        changeDisplay.classList.add('hidden');
        warningDisplay.classList.add('hidden');
        completeBtn.disabled = true;
        completeBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
    }

    // Update the processPayment method for better error handling
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
            setTimeout(() => {
                window.location.href = '{{ route("store_transactions") }}';
            }, 2000);
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

    // Update the showPaymentModal method to reset states
    showPaymentModal() {
    if (this.cartItems.length === 0) {
        this.showToast('Cart is empty', 'error');
        return;
    }

    document.getElementById('paymentTotalQuantity').textContent = this.totalQuantity;
    document.getElementById('paymentTotalAmount').textContent = `₱${this.totalAmount.toFixed(2)}`;
    
    // Reset input and displays
    document.getElementById('amountReceived').value = '';
    document.getElementById('changeDisplay').classList.add('hidden');
    document.getElementById('warningDisplay').classList.add('hidden');
    
    // Reset button state
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
        toast.innerHTML = `
            <div class="flex items-center gap-2">
                <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i>
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