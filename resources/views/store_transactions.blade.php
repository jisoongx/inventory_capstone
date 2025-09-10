@extends('dashboards.owner.owner')
@section('content')
<div class="p-4">
    <!-- Start Transaction Button -->
    <div class="flex justify-end mb-6">
        <button type="button" id="startTransactionBtn"
            class="px-6 py-4 text-white font-medium rounded-lg shadow-md transition-colors duration-200"
            style="background-color:#336055;">
            Start Transaction
        </button>
    </div>

    <!-- Transactions Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header with filters -->
        <div class="flex flex-col md:flex-row justify-between md:items-center px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-700">Transactions</h2>
            
            <!-- Date Filter Options -->
            <div class="flex flex-wrap gap-3 mt-3 md:mt-0">
                <!-- Single Date Filter -->
                <form method="GET" action="{{ route('store_transactions') }}" id="dateFilterForm" class="flex gap-2">
                    <input type="date" name="date" value="{{ $date }}" id="dateFilter"
                           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#336055] focus:border-[#336055]">
                    <button type="submit" name="date" value="{{ now()->toDateString() }}"
                        class="px-4 py-2 text-sm text-white rounded-lg transition-colors duration-200"
                        style="background-color:#336055;">
                        Today
                    </button>
                </form>
                
                <!-- Date Range Toggle Button -->
                <button type="button" id="dateRangeToggle"
                    class="px-4 py-2 text-sm border border-gray-300 rounded-lg bg-white hover:bg-gray-100 transition">
                    <i class="fas fa-calendar-alt mr-2"></i>Date Range
                </button>
                
                <!-- Clear Filter -->
                <a href="{{ route('store_transactions') }}"
                   class="px-4 py-2 text-sm border border-gray-300 rounded-lg bg-white hover:bg-gray-100 transition">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
        </div>

        <!-- Date Range Filter Panel (Hidden by default) -->
        <div id="dateRangePanel" class="px-6 py-4 border-b bg-blue-50" style="display: none;">
            <form method="GET" action="{{ route('store_transactions') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-48">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $start_date }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#336055] focus:border-[#336055]">
                </div>
                
                <div class="flex-1 min-w-48">
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $end_date }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#336055] focus:border-[#336055]">
                </div>
                
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white rounded-lg transition-colors duration-200"
                        style="background-color:#336055;">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    
                    <button type="button" id="quickRangeBtn"
                        class="px-4 py-2 text-sm border border-gray-400 rounded-lg bg-white hover:bg-gray-100 transition">
                        <i class="fas fa-bolt mr-2"></i>Quick Range
                    </button>
                </div>
            </form>
        </div>

        <!-- Active Filter Display -->
        @if($date || ($start_date && $end_date))
        <div class="px-6 py-3 bg-green-50 border-b border-green-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-green-800">
                    <i class="fas fa-filter mr-2"></i>
                    <span class="font-medium">Active Filter:</span>
                    @if($date)
                        {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                    @elseif($start_date && $end_date)
                        {{ \Carbon\Carbon::parse($start_date)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($end_date)->format('F d, Y') }}
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs border-b">
                    <tr>
                        <th class="py-3 px-4">Receipt No</th>
                        <th class="py-3 px-4">Items Quantity</th>
                        <th class="py-3 px-4">Total Amount</th>
                        <th class="py-3 px-4">Time</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-800">{{ $trx->receipt_id }}</td>
                            <td class="py-3 px-4">{{ $trx->items_quantity }}</td>
                            <td class="py-3 px-4 font-semibold text-green-600">₱{{ number_format($trx->total_amount, 2) }}</td>
                            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($trx->receipt_date)->format('h:i A') }}</td>
                            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($trx->receipt_date)->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 text-center">
                                <button class="text-[#336055] font-medium hover:underline">View</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">
                                <i class="fas fa-file-alt text-3xl mb-3"></i>
                                <p class="font-medium">No transactions found</p>
                                @if($date || ($start_date && $end_date))
                                    <p class="text-sm">No transactions found for the selected date range.</p>
                                @else
                                    <p class="text-sm">Start your first transaction to see it here.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Code Modal (Simplified) -->
<div id="productModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">START NEW TRANSACTION</h5>
                <button type="button" class="close-btn" id="closeModalBtn">&times;</button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Alert Container for Errors -->
                <div id="alertContainer" style="display: none;">
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span id="alertMessage"></span>
                    </div>
                </div>

                <!-- Product Input Form -->
                <form id="productCodeForm" class="space-y-4">
                    @csrf
                    <div>
                        <label for="productCode" class="block text-sm font-medium text-gray-700 mb-2">
                            Product Code or Barcode
                        </label>
                        <input type="text" id="productCode" name="prod_code" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#336055] focus:border-[#336055] text-lg"
                               placeholder="Enter product code or scan barcode" required>
                    </div>
                    
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                            Quantity
                        </label>
                        <input type="number" id="quantity" name="quantity" min="1" value="1"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#336055] focus:border-[#336055] text-lg"
                               required>
                    </div>

                    <button type="submit" id="submitTransactionBtn"
                            class="w-full mt-6 px-6 py-3 text-white font-medium rounded-lg transition-colors duration-200"
                            style="background-color:#336055;">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Start Transaction
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quick Date Range Modal -->
<div id="quickRangeModal" class="modal-overlay" style="display: none;">
    <div class="modal-container" style="width: 400px;">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">QUICK DATE RANGES</h5>
                <button type="button" class="close-btn" id="closeQuickRangeModalBtn">&times;</button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body" style="background: white; padding: 1.5rem;">
                <div class="space-y-2">
                    <button type="button" class="quick-range-option w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                            data-days="7">Last 7 Days</button>
                    <button type="button" class="quick-range-option w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                            data-days="30">Last 30 Days</button>
                    <button type="button" class="quick-range-option w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                            data-days="90">Last 3 Months</button>
                    <button type="button" class="quick-range-option w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                            data-days="365">Last Year</button>
                    <button type="button" class="quick-range-option w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                            data-range="month">This Month</button>
                    <button type="button" class="quick-range-option w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                            data-range="year">This Year</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles for Modal -->
<style>
/* Modal Overlay */
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
}

/* Modal Container */
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
}

/* Modal Header */
.modal-header {
    background: linear-gradient(135deg, #336055, #2d5449);
    color: white;
    padding: 1.25rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: none;
}

.modal-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Close Button */
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

/* Modal Body */
.modal-body {
    padding: 1.5rem;
    background: #fafafa;
}

/* Alert Styles */
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

.alert-warning {
    background-color: #fffbeb;
    border: 1px solid #fed7aa;
    color: #d97706;
}

/* Loading indicator for date filter */
.date-loading {
    opacity: 0.6;
    pointer-events: none;
    position: relative;
}

.date-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    right: 10px;
    width: 16px;
    height: 16px;
    margin-top: -8px;
    border: 2px solid #336055;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

/* Button Loading State */
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

@keyframes spin {
    to { transform: rotate(360deg);     }
}

/* Animations */
.modal-overlay {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { 
        opacity: 0;
        backdrop-filter: blur(0px);
    }
    to { 
        opacity: 1;
        backdrop-filter: blur(4px);
    }
}

.modal-container {
    animation: slideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes slideIn {
    from { 
        transform: scale(0.8) translateY(-50px); 
        opacity: 0; 
    }
    to { 
        transform: scale(1) translateY(0); 
        opacity: 1; 
    }
}

/* Responsive Design */
@media (max-width: 640px) {
    .modal-container {
        width: 95% !important;
        margin: 10px;
    }
    
    .modal-body {
        padding: 1rem 0.8rem;
    }
}

/* Custom hover effect for buttons with #336055 */
button[style*="background-color: #336055"]:hover,
button[style*="background-color:#336055"]:hover {
    background-color: #2d5449 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(51, 96, 85, 0.3);
}

/* Prevent background scroll when modal is open */
body.modal-open {
    overflow: hidden;
}

/* Space utility classes */
.space-y-4 > * + * {
    margin-top: 1rem;
}

.space-y-2 > * + * {
    margin-top: 0.5rem;
}

/* Quick range option hover effect */
.quick-range-option:hover {
    background-color: #f9fafb;
    border-color: #336055;
}

/* Date range panel styles */
#dateRangePanel {
    border-left: 4px solid #336055;
}

/* Active filter highlight */
.bg-green-50 {
    background-color: #f0fdf4;
}
</style>

<!-- JavaScript for Modal Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startTransactionBtn = document.getElementById('startTransactionBtn');
    const productModal = document.getElementById('productModal');
    const quickRangeModal = document.getElementById('quickRangeModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const closeQuickRangeModalBtn = document.getElementById('closeQuickRangeModalBtn');
    
    const dateFilter = document.getElementById('dateFilter');
    const dateFilterForm = document.getElementById('dateFilterForm');
    const productCodeForm = document.getElementById('productCodeForm');
    const dateRangeToggle = document.getElementById('dateRangeToggle');
    const dateRangePanel = document.getElementById('dateRangePanel');
    const quickRangeBtn = document.getElementById('quickRangeBtn');
    const body = document.body;

    // Auto-submit form when date changes
    if (dateFilter && dateFilterForm) {
        dateFilter.addEventListener('change', function() {
            if (this.value) {
                dateFilter.classList.add('date-loading');
                setTimeout(() => dateFilterForm.submit(), 100);
            }
        });
    }

    // Date range toggle functionality
    if (dateRangeToggle && dateRangePanel) {
        dateRangeToggle.addEventListener('click', function() {
            const isVisible = dateRangePanel.style.display !== 'none';
            dateRangePanel.style.display = isVisible ? 'none' : 'block';
            this.textContent = isVisible ? 'Date Range' : 'Hide Range';
            
            // Update icon
            const icon = this.querySelector('i');
            if (icon) {
                icon.className = isVisible ? 'fas fa-calendar-alt mr-2' : 'fas fa-times mr-2';
            }
        });
    }

    // Quick range button functionality
    if (quickRangeBtn && quickRangeModal) {
        quickRangeBtn.addEventListener('click', function() {
            showModal(quickRangeModal);
        });
    }

    // Quick range options
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('quick-range-option')) {
            const button = e.target;
            const days = button.getAttribute('data-days');
            const range = button.getAttribute('data-range');
            
            let startDate, endDate;
            const today = new Date();
            
            if (days) {
                endDate = new Date();
                startDate = new Date();
                startDate.setDate(today.getDate() - parseInt(days));
            } else if (range === 'month') {
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            } else if (range === 'year') {
                startDate = new Date(today.getFullYear(), 0, 1);
                endDate = new Date(today.getFullYear(), 11, 31);
            }
            
            // Format dates for input fields
            const formatDate = (date) => {
                return date.toISOString().split('T')[0];
            };
            
            document.getElementById('start_date').value = formatDate(startDate);
            document.getElementById('end_date').value = formatDate(endDate);
            
            hideModal(quickRangeModal);
            
            // Show the date range panel if it's hidden
            if (dateRangePanel.style.display === 'none') {
                dateRangePanel.style.display = 'block';
                if (dateRangeToggle) {
                    dateRangeToggle.innerHTML = '<i class="fas fa-times mr-2"></i>Hide Range';
                }
            }
        }
    });

    // Modal functions
    function showModal(modal) {
        modal.style.display = 'flex';
        body.classList.add('modal-open');
    }

    function hideModal(modal) {
        modal.style.display = 'none';
        body.classList.remove('modal-open');
    }

    function hideAllModals() {
        hideModal(productModal);
        hideModal(quickRangeModal);
    }

    // Event listeners
    if (startTransactionBtn) {
    startTransactionBtn.addEventListener('click', () => {
        // Show loading state
        startTransactionBtn.classList.add('btn-loading');
        startTransactionBtn.textContent = 'Starting...';
        
        // Redirect directly to start transaction page
        window.location.href = '{{ route("store_start_transaction") }}';
    });
    }
    if (closeModalBtn) closeModalBtn.addEventListener('click', () => hideModal(productModal));
    if (closeQuickRangeModalBtn) closeQuickRangeModalBtn.addEventListener('click', () => hideModal(quickRangeModal));

    // Close modal when clicking outside
    [productModal, quickRangeModal].forEach(modal => {
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) hideModal(modal);
            });
        }
    });

    // ESC key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideAllModals();
        }
    });

    

    // Helper functions
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        const alertMessage = document.getElementById('alertMessage');
        const alert = alertContainer.querySelector('.alert');
        
        // Remove existing alert classes
        alert.classList.remove('alert-error', 'alert-success', 'alert-warning');
        
        // Add appropriate class
        let alertClass = 'alert-error';
        if (type === 'success') alertClass = 'alert-success';
        if (type === 'warning') alertClass = 'alert-warning';
        
        alert.classList.add(alertClass);
        alertMessage.textContent = message;
        alertContainer.style.display = 'block';
        
        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(() => hideAlert(), 3000);
        }
    }

    function hideAlert() {
        const alertContainer = document.getElementById('alertContainer');
        if (alertContainer) {
            alertContainer.style.display = 'none';
        }
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white z-50 transition-all duration-300 ${
            type === 'error' ? 'bg-red-500' : 
            type === 'success' ? 'bg-green-500' : 
            type === 'warning' ? 'bg-yellow-500' :
            'bg-blue-500'
        }`;
        notification.textContent = message;
        notification.style.transform = 'translateX(100%)';
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Animate out and remove
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
});

// Error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
});
</script>

<!-- Add CSRF token meta tag if not already present -->
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection