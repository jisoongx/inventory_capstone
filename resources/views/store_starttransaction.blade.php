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
    <!-- Header with filter -->
    <div class="flex flex-col md:flex-row justify-between md:items-center px-6 py-4 border-b bg-gray-50">
        <h2 class="text-lg font-semibold text-gray-700">Transactions</h2>
        
        <form method="GET" action="{{ route('store_starttransaction') }}" id="dateFilterForm"
              class="flex flex-wrap gap-2 mt-3 md:mt-0">
            <input type="date" name="date" value="{{ $date }}" id="dateFilter"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#336055] focus:border-[#336055]">
            <a href="{{ route('store_starttransaction') }}"
               class="px-4 py-2 text-sm border border-gray-300 rounded-lg bg-white hover:bg-gray-100 transition">
                Clear
            </a>
            <button type="submit" name="date" value="{{ now()->toDateString() }}"
                class="px-4 py-2 text-sm text-white rounded-lg transition-colors duration-200"
                style="background-color:#336055;">
                Today
            </button>
        </form>
    </div>

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
                            <p class="text-sm">Start your first transaction to see it here.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- Custom Modal Overlay -->
<div id="productModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">PRODUCT CODE OPTIONS</h5>
                <button type="button" class="close-btn" id="closeModalBtn">&times;</button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <div class="option-container">
                    <div class="option-item" onclick="handleScanProduct()">
                        <div class="option-icon">
                            <i class="fas fa-barcode"></i>
                        </div>
                        <p>Scan Product Code</p>
                    </div>
                    <div class="option-item" onclick="handleTypeProduct()">
                        <div class="option-icon">
                            <i class="fas fa-keyboard"></i>
                        </div>
                        <p>Type Product Code</p>
                    </div>
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
}

/* Modal Header */
.modal-header {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
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

/* Option Container */
.option-container {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
}

/* Option Items */
.option-item {
    text-align: center;
    cursor: pointer;
    padding: 1rem;
    border-radius: 12px;
    transition: all 0.3s ease;
    flex: 1;
    min-width: 120px;
    background: white;
    border: 2px solid #e5e7eb;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.option-item:hover {
    background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
    border-color: #dc2626;
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(220, 38, 38, 0.15);
}

.option-item:active {
    transform: translateY(-2px);
}

/* Option Icons */
.option-icon {
    margin-bottom: 1rem;
}

.option-item i {
    font-size: 5rem;
    color: #374151;
    transition: color 0.3s ease;
}

.option-item:hover i {
    color: #dc2626;
}

.option-item p {
    margin: 0;
    color: #374151;
    font-weight: 600;
    font-size: 1rem;
    transition: color 0.3s ease;
}

.option-item:hover p {
    color: #dc2626;
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

@keyframes spin {
    to { transform: rotate(360deg); }
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
        width: 90%;
        margin: 10px;
    }
    
    .option-container {
        flex-direction: column;
        gap: 0.8rem;
    }
    
    .option-item {
        min-width: auto;
    }
    
    .modal-body {
        padding: 1rem 0.8rem;
    }
}

/* Custom hover effect for buttons with #336055 */
button[style*="background-color: #336055"]:hover {
    background-color: #2d5449 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(51, 96, 85, 0.3);
}

/* Loading state for buttons */
.btn-loading {
    opacity: 0.7;
    cursor: not-allowed;
    pointer-events: none;
}

/* Prevent background scroll when modal is open */
body.modal-open {
    overflow: hidden;
}
</style>

<!-- JavaScript for Modal Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startTransactionBtn = document.getElementById('startTransactionBtn');
    const productModal = document.getElementById('productModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const dateFilter = document.getElementById('dateFilter');
    const dateFilterForm = document.getElementById('dateFilterForm');
    const body = document.body;

    // Auto-submit form when date changes
    if (dateFilter && dateFilterForm) {
        dateFilter.addEventListener('change', function() {
            // Add loading state
            dateFilter.classList.add('date-loading');
            
            // Add a small delay for better UX
            setTimeout(() => {
                dateFilterForm.submit();
            }, 100);
        });
    }

    function showModal() {
        productModal.style.display = 'flex';
        body.classList.add('modal-open');
        setTimeout(() => closeModalBtn.focus(), 300);
    }

    function hideModal() {
        productModal.style.display = 'none';
        body.classList.remove('modal-open');
        startTransactionBtn.focus();
    }

    if (startTransactionBtn) startTransactionBtn.addEventListener('click', showModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', hideModal);
    if (productModal) {
        productModal.addEventListener('click', function(e) {
            if (e.target === productModal) hideModal();
        });
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && productModal.style.display === 'flex') hideModal();
    });

    window.handleScanProduct = function() {
        showNotification('Scan feature coming soon!', 'info');
        return false;
    };
    window.handleTypeProduct = function() {
        showNotification('Type feature coming soon!', 'info');
        return false;
    };
});

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white z-50 transition-all duration-300 ${
        type === 'error' ? 'bg-red-500' : 
        type === 'success' ? 'bg-green-500' : 
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

window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
});
</script>
@endsection