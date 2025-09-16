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
            <form method="GET" action="{{ route('store_transactions') }}" class="flex flex-wrap gap-3 items-end" id="dateRangeForm">
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
                                <button onclick="viewReceipt({{ $trx->receipt_id }})" 
                                        class="text-[#336055] font-medium hover:underline view-receipt-btn">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">
                                <!-- Modern Document Icon -->
                                <svg class="mx-auto mb-4 w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
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

<!-- Receipt View Modal -->
<div id="receiptViewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-lg w-full max-w-md mx-auto h-full max-h-[90vh] flex flex-col">
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
                <!-- Loading State -->
                <div id="receiptLoading" class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#336055] mx-auto mb-4"></div>
                    <p class="text-gray-600">Loading receipt details...</p>
                </div>

                <!-- Error State -->
                <div id="receiptError" class="hidden text-center py-8">
                    <svg class="mx-auto mb-4 w-12 h-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <p class="text-red-600 text-lg font-medium">Error loading receipt</p>
                    <p class="text-gray-500 text-sm" id="receiptErrorMessage">Please try again later.</p>
                </div>

                <!-- Receipt Details Content -->
                <div id="receiptContent" class="hidden">
                    <!-- Store Info -->
                    <div class="text-center mb-6 pb-4 border-b-2 border-gray-200">
                        <h2 id="storeNameReceipt" class="text-xl font-bold text-gray-800">Store Name</h2>
                    </div>

                    <!-- Transaction Details -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Receipt No.:</span>
                            <span id="receiptNumber" class="text-sm font-bold text-gray-900">-</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Date & Time:</span>
                            <span id="receiptTransactionDate" class="text-sm text-gray-900">-</span>
                        </div>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm font-medium text-gray-700">Cashier:</span>
                            <span id="receiptCashier" class="text-sm text-gray-900">-</span>
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
                            <span id="receiptTotalAmount" class="text-lg font-bold text-[#336055]">₱0.00</span>
                        </div>
                    </div>

                    <!-- Footer Message -->
                    <div class="text-center mt-6 pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500">Thank you for your purchase!</p>
                        <p class="text-xs text-gray-500">Please keep this receipt for your records.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons - Fixed at bottom -->
        <div class="p-4 border-t bg-gray-50 flex gap-3 rounded-b-lg flex-shrink-0">
            <button id="returnItemBtn" class="flex-1 text-white py-3 px-4 rounded-lg font-bold transition-colors" style="background-color: #336055;" onmouseover="this.style.backgroundColor='#2d5449'" onmouseout="this.style.backgroundColor='#336055'">
                Return Item
            </button>
            <button id="closeReceiptModalBtn" class="flex-1 bg-gray-600 text-white py-3 px-4 rounded-lg font-bold hover:bg-gray-700 transition-colors">
                Close
            </button>
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
                            data-range="last-3-months">Last 3 Months</button>
                    <button type="button" class="quick-range-option w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                            data-range="last-year">Last Year</button>
                    <button type="button" class="quick-range-option w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                            data-range="this-month">This Month</button>
                    <button type="button" class="quick-range-option w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                            data-range="this-year">This Year</button>
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

/* Receipt Modal Specific Scrolling */
#receiptViewModal .overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

#receiptViewModal .overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

#receiptViewModal .overflow-y-auto::-webkit-scrollbar-track {
    background: #f8fafc;
    border-radius: 4px;
    margin: 8px 0;
}

#receiptViewModal .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 4px;
    border: 2px solid #f8fafc;
}

#receiptViewModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

/* Loading animation */
@keyframes spin {
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* View Receipt Button Hover */
.view-receipt-btn:hover {
    color: #2d5449 !important;
    text-decoration: underline;
    cursor: pointer;
}
</style>

<!-- JavaScript for Modal Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startTransactionBtn = document.getElementById('startTransactionBtn');
    const productModal = document.getElementById('productModal');
    const quickRangeModal = document.getElementById('quickRangeModal');
    const receiptViewModal = document.getElementById('receiptViewModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const closeQuickRangeModalBtn = document.getElementById('closeQuickRangeModalBtn');
    const closeReceiptModalBtn = document.getElementById('closeReceiptModalBtn');
    
    const dateFilter = document.getElementById('dateFilter');
    const dateFilterForm = document.getElementById('dateFilterForm');
    const productCodeForm = document.getElementById('productCodeForm');
    const dateRangeToggle = document.getElementById('dateRangeToggle');
    const dateRangePanel = document.getElementById('dateRangePanel');
    const dateRangeForm = document.getElementById('dateRangeForm');
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
                // For "Last X Days" - go back X days from today
                endDate = new Date(today);
                startDate = new Date(today);
                startDate.setDate(today.getDate() - parseInt(days));
            } else if (range === 'last-3-months') {
                // Last 3 months means 3 months ago
                // If today is September 2025, 3 months ago is June 2025
                endDate = new Date(today);
                startDate = new Date(today.getFullYear(), today.getMonth() - 3, 1);
                endDate = new Date(today.getFullYear(), today.getMonth() - 3 + 1, 0); // Last day of 3 months ago
            } else if (range === 'last-year') {
                // Last year means the entire previous year
                // If current year is 2025, last year is 2024 (Jan 1 to Dec 31, 2024)
                const lastYear = today.getFullYear() - 1;
                startDate = new Date(lastYear, 0, 1); // January 1 of last year
                endDate = new Date(lastYear, 11, 31); // December 31 of last year
            } else if (range === 'this-month') {
                // This month - from 1st to last day of current month
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            } else if (range === 'this-year') {
                // This year - from Jan 1 to Dec 31 of current year
                startDate = new Date(today.getFullYear(), 0, 1);
                endDate = new Date(today.getFullYear(), 11, 31);
            }
            
            // Format dates for input fields
            const formatDate = (date) => {
                return date.toISOString().split('T')[0];
            };
            
            // Set the date inputs
            document.getElementById('start_date').value = formatDate(startDate);
            document.getElementById('end_date').value = formatDate(endDate);
            
            // Close the quick range modal
            hideModal(quickRangeModal);
            
            // Show the date range panel if it's hidden
            if (dateRangePanel.style.display === 'none') {
                dateRangePanel.style.display = 'block';
                if (dateRangeToggle) {
                    dateRangeToggle.innerHTML = '<i class="fas fa-times mr-2"></i>Hide Range';
                }
            }
            
            // Automatically submit the form to show results immediately
            if (dateRangeForm) {
                // Add loading state
                button.classList.add('btn-loading');
                button.textContent = 'Loading...';
                
                // Submit the form
                setTimeout(() => {
                    dateRangeForm.submit();
                }, 100);
            }
        }
    });

    // Modal functions
    function showModal(modal) {
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
        body.classList.add('modal-open');
    }

    function hideModal(modal) {
        modal.style.display = 'none';
        modal.classList.add('hidden');
        body.classList.remove('modal-open');
        
        // Reset receipt modal state when closing
        if (modal.id === 'receiptViewModal') {
            resetReceiptModal();
        }
    }

    function hideAllModals() {
        hideModal(productModal);
        hideModal(quickRangeModal);
        hideModal(receiptViewModal);
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
    if (closeReceiptModalBtn) closeReceiptModalBtn.addEventListener('click', () => hideModal(receiptViewModal));

    // Close modal when clicking outside
    [productModal, quickRangeModal, receiptViewModal].forEach(modal => {
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

    // Event delegation for View Receipt buttons (ensures they work after DOM changes)
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('view-receipt-btn')) {
            e.preventDefault();
            const receiptId = e.target.getAttribute('data-receipt-id') || 
                              e.target.getAttribute('onclick')?.match(/viewReceipt\((\d+)\)/)?.[1];
            if (receiptId) {
                window.viewReceipt(parseInt(receiptId));
            }
        }
    });

    // Return Item button functionality
    document.getElementById('returnItemBtn').addEventListener('click', function() {
        // Get the current receipt ID
        const receiptId = document.getElementById('receiptNumber').textContent;
        
        // Show notification - you can implement actual return functionality later
        showNotification(`Return item functionality for receipt ${receiptId} will be implemented soon`, 'info');
    });

    // Reset receipt modal function
    function resetReceiptModal() {
        const receiptContent = document.getElementById('receiptContent');
        const receiptLoading = document.getElementById('receiptLoading');
        const receiptError = document.getElementById('receiptError');
        
        // Reset all states
        receiptContent.classList.add('hidden');
        receiptError.classList.add('hidden');
        receiptLoading.classList.remove('hidden');
        
        // Clear content
        document.getElementById('receiptNumber').textContent = '-';
        document.getElementById('receiptTransactionDate').textContent = '-';
        document.getElementById('receiptCashier').textContent = '-';
        document.getElementById('receiptItemsList').innerHTML = '';
        document.getElementById('receiptTotalItems').textContent = '0';
        document.getElementById('receiptTotalAmount').textContent = '₱0.00';
    }

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

// Global function to view receipt
window.viewReceipt = async function(receiptId) {
    console.log('ViewReceipt called with ID:', receiptId); // Debug log
    
    const receiptViewModal = document.getElementById('receiptViewModal');
    const receiptContent = document.getElementById('receiptContent');
    const receiptLoading = document.getElementById('receiptLoading');
    const receiptError = document.getElementById('receiptError');
    const receiptErrorMessage = document.getElementById('receiptErrorMessage');
    
    // Show modal and loading state
    receiptViewModal.style.display = 'flex';
    receiptViewModal.classList.remove('hidden');
    document.body.classList.add('modal-open');
    
    receiptContent.classList.add('hidden');
    receiptError.classList.add('hidden');
    receiptLoading.classList.remove('hidden');
    
    try {
        // Fetch receipt details from server
        const response = await fetch(`/api/receipt/${receiptId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            populateReceiptModal(data.receipt, data.items, data.store_info);
        } else {
            throw new Error(data.message || 'Failed to load receipt details');
        }
        
    } catch (error) {
        console.error('Error fetching receipt:', error);
        receiptLoading.classList.add('hidden');
        receiptError.classList.remove('hidden');
        receiptErrorMessage.textContent = error.message || 'Unable to load receipt details. Please try again.';
    }
};

function populateReceiptModal(receipt, items, storeInfo) {
    const receiptContent = document.getElementById('receiptContent');
    const receiptLoading = document.getElementById('receiptLoading');
    const receiptError = document.getElementById('receiptError');
    
    // Hide loading and error states
    receiptLoading.classList.add('hidden');
    receiptError.classList.add('hidden');
    
    // Populate store information
    if (storeInfo) {
        document.getElementById('storeNameReceipt').textContent = storeInfo.store_name || 'Store Name';
    }
    
    // Populate receipt details
    document.getElementById('receiptNumber').textContent = receipt.receipt_id || '-';
    
    // Format date and time
    if (receipt.receipt_date) {
        const date = new Date(receipt.receipt_date);
        const options = {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        document.getElementById('receiptTransactionDate').textContent = date.toLocaleString('en-US', options);
    }
    
    // Populate cashier information
    const cashierName = receipt.owner_name || receipt.staff_name || 'Cashier';
    document.getElementById('receiptCashier').textContent = cashierName;
    
    // Populate items list
    const itemsList = document.getElementById('receiptItemsList');
    let totalItems = 0;
    let totalAmount = 0;
    
    if (items && items.length > 0) {
        itemsList.innerHTML = items.map(item => {
            const itemTotal = parseFloat(item.item_quantity) * parseFloat(item.selling_price);
            totalItems += parseInt(item.item_quantity);
            totalAmount += itemTotal;
            
            return `
                <div class="flex justify-between items-start py-2 border-b border-gray-100 last:border-b-0">
                    <div class="flex-1 pr-2">
                        <div class="text-sm font-medium text-gray-900">${item.product_name}</div>
                        <div class="text-xs text-gray-500">${item.item_quantity} × ₱${parseFloat(item.selling_price).toFixed(2)}</div>
                    </div>
                    <div class="text-sm font-bold text-gray-900">₱${itemTotal.toFixed(2)}</div>
                </div>
            `;
        }).join('');
    } else {
        itemsList.innerHTML = '<div class="text-center py-4 text-gray-500">No items found</div>';
    }
    
    // Populate totals
    document.getElementById('receiptTotalItems').textContent = totalItems;
    document.getElementById('receiptTotalAmount').textContent = `₱${totalAmount.toFixed(2)}`;
    
    // Show content
    receiptContent.classList.remove('hidden');
}

// Error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
});
</script>

<!-- Add CSRF token meta tag if not already present -->
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection