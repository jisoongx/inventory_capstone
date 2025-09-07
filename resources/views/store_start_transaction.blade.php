@extends('dashboards.owner.owner')
@section('content')
<div class="p-4">
    <!-- Receipt Interface -->
    <div class="max-w-full mx-auto">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6 rounded-t-xl">
            <div class="text-center">
                <h1 class="text-2xl font-bold">Receipt</h1>
                <p class="text-lg">Receipt No: <span id="receipt-number">{{ $receipt_no ?? '0' }}</span></p>
                <p id="receipt-datetime">{{ now()->format('m/d/Y h:i:s A') }}</p>
                <p>User: {{ $user_firstname ?? 'Unknown' }}</p>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="bg-white shadow-lg rounded-b-xl">
            <div class="flex">
                <!-- Left side - Items Table (expanded to 4/5 width) -->
                <div class="w-4/5 border-r border-gray-200">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr class="border-b">
                                <th class="py-3 px-4 text-left">Name</th>
                                <th class="py-3 px-4 text-center">Price</th>
                                <th class="py-3 px-4 text-center">Quantity</th>
                                <th class="py-3 px-4 text-center">Amount</th>
                                <th class="py-3 px-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            @if(!empty($items) && count($items) > 0)
                                @foreach($items as $index => $item)
                                <tr class="border-b hover:bg-gray-50" data-index="{{ $index }}">
                                    <td class="py-3 px-4">{{ $item['product']->name }}</td>
                                    <td class="py-3 px-4 text-center">₱{{ number_format($item['product']->cost_price, 2) }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <input type="number" min="1" value="{{ $item['quantity'] }}" 
                                               class="w-20 px-2 py-1 border border-gray-300 rounded text-center quantity-input"
                                               data-price="{{ $item['product']->cost_price }}"
                                               data-index="{{ $index }}">
                                    </td>
                                    <td class="py-3 px-4 text-center amount-cell">₱{{ number_format($item['amount'], 2) }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <button type="button" class="text-red-500 hover:text-red-700 remove-item" data-index="{{ $index }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr id="noItemsRow">
                                    <td colspan="5" class="text-center py-20 text-gray-500">
                                        <i class="fas fa-shopping-bag text-6xl mb-4"></i>
                                        <p class="font-medium text-lg">No items added yet</p>
                                        <p class="text-sm">Click the + button to add your first item</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    
                    <!-- Add Item Button -->
                    <div class="p-4 border-t border-gray-200">
                        <button type="button" id="addItemBtn"
                                class="w-full bg-red-500 text-white py-2 px-4 rounded-lg text-base font-medium hover:bg-red-600 transition-colors duration-200 shadow-md">
                            <i class="fas fa-plus mr-2"></i> Add Item
                        </button>
                    </div>
                </div>

                <!-- Right side - Summary and Actions (1/5 width - smaller and more to the right) -->
                <div class="w-1/5 bg-gray-50 flex flex-col">
                    <!-- Top Section - Summary (Smaller and more compact) -->
                    <div class="p-4 flex-1 flex flex-col justify-center">
                        <div class="bg-white p-4 rounded-xl shadow-lg border border-gray-100">
                            <div class="text-center mb-4">
                                <h3 class="text-lg font-bold text-gray-800">Summary</h3>
                                <div class="w-12 h-0.5 bg-red-500 mx-auto mt-2 rounded"></div>
                            </div>
                            
                            <div class="space-y-3">
                                <!-- Total Quantity -->
                                <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                                    <div class="text-center">
                                        <span class="text-green-600 font-medium text-sm block">Total Quantity</span>
                                        <span id="itemsQuantity" class="text-2xl font-bold text-green-700">{{ $total_quantity ?? 0 }}</span>
                                    </div>
                                </div>
                                
                                <!-- Total Amount -->
                                <div class="bg-red-50 p-3 rounded-lg border-2 border-red-200">
                                    <div class="text-center">
                                        <span class="text-red-600 font-semibold text-sm block">TOTAL</span>
                                        <span id="totalAmount" class="text-2xl font-bold text-red-700">₱{{ number_format($total_amount ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Section - Action Buttons -->
                    <div class="p-4 space-y-3">
                        <!-- Process Payment Button -->
                        <button type="button" id="processPaymentBtn"
                                class="w-full text-white py-3 px-4 rounded-xl text-lg font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105"
                                style="background: linear-gradient(135deg, {{ empty($items) || count($items) == 0 ? '#9ca3af, #6b7280' : '#dc2626, #b91c1c' }});" 
                                {{ empty($items) || count($items) == 0 ? 'disabled' : '' }}>
                            <i class="fas fa-credit-card mr-2"></i>
                            Payment
                        </button>

                        <!-- Clear All Button -->
                        <button type="button" onclick="clearAllItems()" 
                                class="w-full bg-gradient-to-r from-gray-500 to-gray-600 text-white py-2 px-4 rounded-xl font-semibold hover:from-gray-600 hover:to-gray-700 transition-all duration-300 transform hover:scale-105 text-sm"
                                id="clearAllBtn" {{ empty($items) || count($items) == 0 ? 'disabled' : '' }}>
                            <i class="fas fa-trash-alt mr-1"></i>
                            Clear All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div id="addItemModal" class="modal-overlay" style="display: none;">
    <div class="modal-container" style="width: 500px;">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">ADD ITEM</h5>
                <button type="button" class="close-btn" id="closeAddItemModalBtn">&times;</button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body" style="background: white; padding: 2rem;">
                <!-- Alert Container -->
                <div id="addItemAlertContainer" style="display: none;">
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span id="addItemAlertMessage"></span>
                    </div>
                </div>

                <!-- Product Input Form -->
                <form id="addItemForm" class="space-y-4">
                    @csrf
                    <div>
                        <label for="addProductCode" class="block text-sm font-medium text-gray-700 mb-2">
                            Product Code or Barcode
                        </label>
                        <input type="text" id="addProductCode" name="prod_code" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-red-600 text-lg"
                               placeholder="Enter product code or scan barcode" required>
                    </div>
                    
                    <div>
                        <label for="addQuantity" class="block text-sm font-medium text-gray-700 mb-2">
                            Quantity
                        </label>
                        <input type="number" id="addQuantity" name="quantity" min="1" value="1"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-red-600 text-lg"
                               required>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" id="addItemSubmitBtn"
                                class="flex-1 px-6 py-3 text-white font-medium rounded-lg transition-colors duration-200"
                                style="background-color: #dc2626;">
                            <i class="fas fa-plus mr-2"></i>
                            Add Item
                        </button>
                        <button type="button" onclick="clearAddItemForm()"
                                class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            Clear
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal-overlay" style="display: none;">
    <div class="modal-container" style="width: 600px;">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">PROCESS PAYMENT</h5>
                <button type="button" class="close-btn" id="closePaymentModalBtn">&times;</button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body" style="background: white; padding: 2rem;">
                <!-- Alert Container for Payment Errors -->
                <div id="paymentAlertContainer" style="display: none;">
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span id="paymentAlertMessage"></span>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="bg-gray-50 p-6 rounded-lg mb-6">
                    <div class="flex justify-between items-center text-lg mb-3">
                        <span class="font-medium">Total Quantity:</span>
                        <span id="paymentItemsQuantity" class="font-bold">0</span>
                    </div>
                    <div class="flex justify-between items-center text-2xl font-bold border-t pt-3">
                        <span>Amount Due:</span>
                        <span id="paymentTotalAmount" class="text-red-600">₱0.00</span>
                    </div>
                </div>

                <!-- Payment Form -->
                <form id="paymentForm" class="space-y-4">
                    @csrf
                    <div>
                        <label for="amountReceived" class="block text-sm font-medium text-gray-700 mb-2">
                            Amount Received
                        </label>
                        <input type="number" id="amountReceived" name="amount_received" step="0.01" min="0"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-red-600 text-lg"
                               placeholder="Enter amount received" required>
                    </div>

                    <!-- Change Display -->
                    <div id="changeDisplay" class="bg-green-50 p-4 rounded-lg border border-green-200" style="display: none;">
                        <div class="flex justify-between items-center text-lg font-semibold">
                            <span class="text-green-700">Change:</span>
                            <span id="changeAmount" class="text-green-700">₱0.00</span>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" id="completePaymentBtn"
                                class="flex-1 px-6 py-3 text-white font-medium rounded-lg transition-colors duration-200"
                                style="background-color: #dc2626;">
                            <i class="fas fa-check mr-2"></i>
                            Complete Payment
                        </button>
                        <button type="button" onclick="clearPaymentForm()"
                                class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            Clear
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(4px);
    animation: fadeIn 0.3s ease;
}

.modal-container {
    width: 460px;
    max-width: 90%;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    margin: 0 auto;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-header {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    padding: 1.25rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.close-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-error {
    background-color: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
}

.alert-success {
    background-color: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #16a34a;
}

.btn-loading {
    opacity: 0.7;
    cursor: not-allowed;
    pointer-events: none;
}

.btn-loading::after {
    content: '';
    display: inline-block;
    width: 16px;
    height: 16px;
    margin-left: 8px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes fadeIn {
    from { opacity: 0; backdrop-filter: blur(0px); }
    to { opacity: 1; backdrop-filter: blur(4px); }
}

@keyframes slideIn {
    from { transform: scale(0.8) translateY(-50px); opacity: 0; }
    to { transform: scale(1) translateY(0); opacity: 1; }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.space-y-4 > * + * {
    margin-top: 1rem;
}

.space-y-6 > * + * {
    margin-top: 1.5rem;
}

/* Disabled button state */
button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
}

body.modal-open {
    overflow: hidden;
}

/* Table styles */
.quantity-input:focus {
    outline: none;
    ring: 2px;
    ring-color: #dc2626;
    border-color: #dc2626;
}

/* Responsive */
@media (max-width: 1024px) {
    .max-w-full {
        margin: 0 1rem;
    }
    
    .flex {
        flex-direction: column;
    }
    
    .w-5/6, .w-1/6 {
        width: 100%;
    }
    
    .border-r {
        border-right: none;
        border-bottom: 1px solid #e5e7eb;
    }
}

@media (max-width: 640px) {
    .modal-container {
        width: 95% !important;
        margin: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time Date and Time Function
    function updateDateTime() {
        const now = new Date();
        
        // Format date as MM/DD/YYYY
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const year = now.getFullYear();
        const formattedDate = `${month}/${day}/${year}`;
        
        // Format time as 12-hour format with AM/PM
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 should be 12
        hours = String(hours).padStart(2, '0');
        
        const formattedTime = `${hours}:${minutes}:${seconds} ${ampm}`;
        
        // Update the receipt datetime display
        const receiptDateTime = document.getElementById('receipt-datetime');
        if (receiptDateTime) {
            receiptDateTime.textContent = `${formattedDate} ${formattedTime}`;
        }
    }

    // Update receipt number for new receipts
    function updateReceiptNumber() {
        const receiptNumberElement = document.getElementById('receipt-number');
        if (receiptNumberElement && receiptNumberElement.textContent === '0') {
            // Generate a timestamp-based receipt number
            const timestamp = Date.now();
            receiptNumberElement.textContent = timestamp.toString().slice(-8);
        }
    }

    // Update date/time immediately and then every second
    updateDateTime();
    updateReceiptNumber();
    setInterval(updateDateTime, 1000);

    const addItemBtn = document.getElementById('addItemBtn');
    const processPaymentBtn = document.getElementById('processPaymentBtn');
    const clearAllBtn = document.getElementById('clearAllBtn');
    const addItemModal = document.getElementById('addItemModal');
    const paymentModal = document.getElementById('paymentModal');
    const closeAddItemModalBtn = document.getElementById('closeAddItemModalBtn');
    const closePaymentModalBtn = document.getElementById('closePaymentModalBtn');
    const addItemForm = document.getElementById('addItemForm');
    const paymentForm = document.getElementById('paymentForm');
    const amountReceived = document.getElementById('amountReceived');
    const body = document.body;

    let currentItems = @json($items ?? []);
    let currentTotal = parseFloat('{{ $total_amount ?? 0 }}');
    let currentQuantity = parseInt('{{ $total_quantity ?? 0 }}');

    // Modal functions
    function showModal(modal) {
        modal.style.display = 'flex';
        body.classList.add('modal-open');
    }

    function hideModal(modal) {
        modal.style.display = 'none';
        body.classList.remove('modal-open');
    }

    // Event listeners
    if (addItemBtn) {
        addItemBtn.addEventListener('click', () => {
            showModal(addItemModal);
            setTimeout(() => document.getElementById('addProductCode').focus(), 300);
        });
    }

    if (processPaymentBtn) {
        processPaymentBtn.addEventListener('click', () => {
            if (currentItems.length === 0) {
                showNotification('Please add at least one item before processing payment.', 'error');
                return;
            }
            updatePaymentSummary();
            showModal(paymentModal);
            setTimeout(() => document.getElementById('amountReceived').focus(), 300);
        });
    }

    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to clear all items?')) {
                clearAllItems();
            }
        });
    }

    // Close modal events
    if (closeAddItemModalBtn) closeAddItemModalBtn.addEventListener('click', () => hideModal(addItemModal));
    if (closePaymentModalBtn) closePaymentModalBtn.addEventListener('click', () => hideModal(paymentModal));

    // Click outside to close
    [addItemModal, paymentModal].forEach(modal => {
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) hideModal(modal);
            });
        }
    });

    // ESC key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideModal(addItemModal);
            hideModal(paymentModal);
        }
    });

    // Add item form submission
    if (addItemForm) {
        addItemForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const addBtn = document.getElementById('addItemSubmitBtn');
            
            addBtn.classList.add('btn-loading');
            addBtn.textContent = 'Adding...';
            hideAddItemAlert();
            
            fetch('{{ route("search_product") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Search product response:', data); // Debug log
                if (data.success) {
                    // Make sure we're passing integers, not strings
                    const requestedQty = parseInt(data.requested_quantity);
                    console.log('Requested quantity as integer:', requestedQty); // Debug log
                    
                    addItemToTable(data.product, requestedQty, data.total_amount);
                    clearAddItemForm();
                    hideModal(addItemModal);
                    showNotification('Item added successfully!', 'success');
                } else {
                    showAddItemAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAddItemAlert('An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                addBtn.classList.remove('btn-loading');
                addBtn.innerHTML = '<i class="fas fa-plus mr-2"></i>Add Item';
            });
        });
    }

    // Payment form submission
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const completeBtn = document.getElementById('completePaymentBtn');
            const amountDue = currentTotal;
            const amountRec = parseFloat(formData.get('amount_received'));
            
            if (amountRec < amountDue) {
                showPaymentAlert('Amount received is less than total amount due!', 'error');
                return;
            }
            
            completeBtn.classList.add('btn-loading');
            completeBtn.textContent = 'Processing...';
            
            // Add payment method as cash since it's removed from UI
            formData.append('payment_method', 'cash');
            
            fetch('{{ route("process_payment") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Payment completed successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("store_transactions") }}';
                    }, 2000);
                } else {
                    showNotification(data.message || 'Payment failed. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred during payment processing.', 'error');
            })
            .finally(() => {
                completeBtn.classList.remove('btn-loading');
                completeBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Complete Payment';
            });
        });
    }

    // Amount received input change event for calculating change
    if (amountReceived) {
        amountReceived.addEventListener('input', function() {
            const amountDue = currentTotal;
            const amountRec = parseFloat(this.value) || 0;
            const change = amountRec - amountDue;
            
            const changeDisplay = document.getElementById('changeDisplay');
            const changeAmount = document.getElementById('changeAmount');
            
            if (amountRec >= amountDue && amountRec > 0) {
                changeAmount.textContent = '₱' + change.toFixed(2);
                changeDisplay.style.display = 'block';
                
                if (change < 0) {
                    changeDisplay.className = 'bg-red-50 p-4 rounded-lg border border-red-200';
                    changeAmount.className = 'text-red-700 font-semibold';
                } else {
                    changeDisplay.className = 'bg-green-50 p-4 rounded-lg border border-green-200';
                    changeAmount.className = 'text-green-700 font-semibold';
                }
            } else {
                changeDisplay.style.display = 'none';
            }
        });
    }

    // Quantity input change events
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('quantity-input')) {
            const index = parseInt(e.target.getAttribute('data-index'));
            const newQuantity = parseInt(e.target.value);
            const price = parseFloat(e.target.getAttribute('data-price'));
            
            if (newQuantity < 1) {
                e.target.value = 1;
                return;
            }
            
            // Update the item
            currentItems[index].quantity = newQuantity;
            currentItems[index].amount = price * newQuantity;
            
            // Update the display
            const row = e.target.closest('tr');
            const amountCell = row.querySelector('.amount-cell');
            amountCell.textContent = '₱' + (price * newQuantity).toFixed(2);
            
            updateTotals();
        }
    });

    // Remove item events
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const index = parseInt(e.target.closest('.remove-item').getAttribute('data-index'));
            removeItem(index);
        }
    });

    // Helper functions
    function addItemToTable(product, quantity, totalAmount) {
        // Check if product already exists (FIXED: proper quantity accumulation)
        const existingIndex = currentItems.findIndex(item => item.product.prod_code === product.prod_code);
        
        if (existingIndex !== -1) {
            // Update existing item - ADD to existing quantity, don't replace
            const existingQuantity = currentItems[existingIndex].quantity;
            const newQuantity = existingQuantity + quantity;
            const newAmount = product.cost_price * newQuantity;
            
            currentItems[existingIndex].quantity = newQuantity;
            currentItems[existingIndex].amount = newAmount;
            
            // Update table row
            const row = document.querySelector(`tr[data-index="${existingIndex}"]`);
            const quantityInput = row.querySelector('.quantity-input');
            const amountCell = row.querySelector('.amount-cell');
            
            quantityInput.value = newQuantity;
            amountCell.textContent = '₱' + newAmount.toFixed(2);
        } else {
            // Add new item
            const newIndex = currentItems.length;
            currentItems.push({
                product: product,
                quantity: quantity,
                amount: totalAmount
            });
            
            // Add to table
            const tableBody = document.getElementById('itemsTableBody');
            const noItemsRow = document.getElementById('noItemsRow');
            
            if (noItemsRow) {
                noItemsRow.remove();
            }
            
            const newRow = document.createElement('tr');
            newRow.className = 'border-b hover:bg-gray-50';
            newRow.setAttribute('data-index', newIndex);
            newRow.innerHTML = `
                <td class="py-3 px-4">${product.name}</td>
                <td class="py-3 px-4 text-center">₱${parseFloat(product.cost_price).toFixed(2)}</td>
                <td class="py-3 px-4 text-center">
                    <input type="number" min="1" value="${quantity}" 
                           class="w-20 px-2 py-1 border border-gray-300 rounded text-center quantity-input"
                           data-price="${product.cost_price}"
                           data-index="${newIndex}">
                </td>
                <td class="py-3 px-4 text-center amount-cell">₱${parseFloat(totalAmount).toFixed(2)}</td>
                <td class="py-3 px-4 text-center">
                    <button type="button" class="text-red-500 hover:text-red-700 remove-item" data-index="${newIndex}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            tableBody.appendChild(newRow);
        }
        
        updateTotals();
        updateButtons();
    }

    function removeItem(index) {
        // Remove from array
        currentItems.splice(index, 1);
        
        // Remove from table and update indices
        const tableBody = document.getElementById('itemsTableBody');
        const rows = tableBody.querySelectorAll('tr[data-index]');
        
        // Remove all rows and re-render
        rows.forEach(row => row.remove());
        
        if (currentItems.length === 0) {
            // Add no items row
            const noItemsRow = document.createElement('tr');
            noItemsRow.id = 'noItemsRow';
            noItemsRow.innerHTML = `
                <td colspan="5" class="text-center py-20 text-gray-500">
                    <i class="fas fa-shopping-bag text-6xl mb-4"></i>
                    <p class="font-medium text-lg">No items added yet</p>
                    <p class="text-sm">Click the + button to add your first item</p>
                </td>
            `;
            tableBody.appendChild(noItemsRow);
        } else {
            // Re-render all items with correct indices
            currentItems.forEach((item, newIndex) => {
                const newRow = document.createElement('tr');
                newRow.className = 'border-b hover:bg-gray-50';
                newRow.setAttribute('data-index', newIndex);
                newRow.innerHTML = `
                    <td class="py-3 px-4">${item.product.name}</td>
                    <td class="py-3 px-4 text-center">₱${parseFloat(item.product.cost_price).toFixed(2)}</td>
                    <td class="py-3 px-4 text-center">
                        <input type="number" min="1" value="${item.quantity}" 
                               class="w-20 px-2 py-1 border border-gray-300 rounded text-center quantity-input"
                               data-price="${item.product.cost_price}"
                               data-index="${newIndex}">
                    </td>
                    <td class="py-3 px-4 text-center amount-cell">₱${parseFloat(item.amount).toFixed(2)}</td>
                    <td class="py-3 px-4 text-center">
                        <button type="button" class="text-red-500 hover:text-red-700 remove-item" data-index="${newIndex}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(newRow);
            });
        }
        
        updateTotals();
        updateButtons();
    }

    function clearAllItems() {
        currentItems = [];
        const tableBody = document.getElementById('itemsTableBody');
        const rows = tableBody.querySelectorAll('tr[data-index]');
        
        // Remove all item rows
        rows.forEach(row => row.remove());
        
        // Add no items row
        const noItemsRow = document.createElement('tr');
        noItemsRow.id = 'noItemsRow';
        noItemsRow.innerHTML = `
            <td colspan="5" class="text-center py-20 text-gray-500">
                <i class="fas fa-shopping-bag text-6xl mb-4"></i>
                <p class="font-medium text-lg">No items added yet</p>
                <p class="text-sm">Click the + button to add your first item</p>
            </td>
        `;
        tableBody.appendChild(noItemsRow);
        
        updateTotals();
        updateButtons();
        showNotification('All items cleared successfully!', 'success');
    }

    function updateTotals() {
        // Calculate total quantity (sum of all individual item quantities)
        currentQuantity = currentItems.reduce((sum, item) => sum + parseInt(item.quantity), 0);
        
        // Calculate total amount
        currentTotal = currentItems.reduce((sum, item) => sum + parseFloat(item.amount), 0);
        
        // Update display
        document.getElementById('itemsQuantity').textContent = currentQuantity;
        document.getElementById('totalAmount').textContent = '₱' + currentTotal.toFixed(2);
    }

    function updateButtons() {
        const processBtn = document.getElementById('processPaymentBtn');
        const clearBtn = document.getElementById('clearAllBtn');
        
        if (currentItems.length > 0) {
            processBtn.style.background = 'linear-gradient(135deg, #dc2626, #b91c1c)';
            processBtn.disabled = false;
            clearBtn.disabled = false;
        } else {
            processBtn.style.background = 'linear-gradient(135deg, #9ca3af, #6b7280)';
            processBtn.disabled = true;
            clearBtn.disabled = true;
        }
    }

    function updatePaymentSummary() {
        document.getElementById('paymentItemsQuantity').textContent = currentQuantity;
        document.getElementById('paymentTotalAmount').textContent = '₱' + currentTotal.toFixed(2);
    }

    function clearAddItemForm() {
        document.getElementById('addProductCode').value = '';
        document.getElementById('addQuantity').value = '1';
        hideAddItemAlert();
    }

    function clearPaymentForm() {
        document.getElementById('amountReceived').value = '';
        document.getElementById('changeDisplay').style.display = 'none';
        hidePaymentAlert();
    }

    function showPaymentAlert(message, type) {
        const alertContainer = document.getElementById('paymentAlertContainer');
        const alertMessage = document.getElementById('paymentAlertMessage');
        const alert = alertContainer.querySelector('.alert');
        
        alert.classList.remove('alert-error', 'alert-success');
        alert.classList.add(type === 'success' ? 'alert-success' : 'alert-error');
        
        alertMessage.textContent = message;
        alertContainer.style.display = 'block';
        
        if (type === 'success') {
            setTimeout(() => hidePaymentAlert(), 3000);
        }
    }

    function hidePaymentAlert() {
        document.getElementById('paymentAlertContainer').style.display = 'none';
    }

    function showAddItemAlert(message, type) {
        const alertContainer = document.getElementById('addItemAlertContainer');
        const alertMessage = document.getElementById('addItemAlertMessage');
        const alert = alertContainer.querySelector('.alert');
        
        alert.classList.remove('alert-error', 'alert-success');
        alert.classList.add(type === 'success' ? 'alert-success' : 'alert-error');
        
        alertMessage.textContent = message;
        alertContainer.style.display = 'block';
        
        if (type === 'success') {
            setTimeout(() => hideAddItemAlert(), 3000);
        }
    }

    function hideAddItemAlert() {
        document.getElementById('addItemAlertContainer').style.display = 'none';
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-4 rounded-lg text-white z-50 transition-all duration-300 shadow-lg ${
            type === 'error' ? 'bg-red-500' : 
            type === 'success' ? 'bg-green-500' : 
            'bg-blue-500'
        }`;
        notification.innerHTML = `
            <div class="flex items-center gap-2">
                <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        notification.style.transform = 'translateX(100%)';
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Initialize totals and button state on page load
    updateTotals();
    updateButtons();

    // Global functions
    window.clearAddItemForm = clearAddItemForm;
    window.clearPaymentForm = clearPaymentForm;
    window.clearAllItems = clearAllItems;
});
</script>

<!-- Add CSRF token meta tag if not already present -->
@if(!isset($__env) || !$__env->hasSection('head'))
<meta name="csrf-token" content="{{ csrf_token() }}">
@endif
@endsection