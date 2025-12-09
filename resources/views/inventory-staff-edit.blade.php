@extends('dashboards.staff.staff')
<head>
    <title>Edit Product Details</title>
</head>
@section('content')
<div class="px-4">
    @livewire('expiration-container')
</div>

<!-- Toast Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<div class="max-w-6xl mx-auto mt-4 mb-6">
    <div class="bg-white rounded-2xl shadow-lg p-4">

        <!-- Page Title -->
        <div class="flex items-center justify-between border-b pb-2 mb-3">
            <h1 class="text-lg font-semibold text-gray-800">Edit Product Details</h1>
            
            <!-- Back Button -->
            <a href="{{ route('inventory-staff') }}" 
               class="inline-flex items-center text-xs text-gray-500 hover:text-red-600 transition">
                <span class="material-symbols-outlined text-sm mr-1">cancel</span>
                Cancel
            </a>
        </div>

        <!-- Form Start -->
        <form action="{{ route('inventory-staff-update', $product->prod_code) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
                
                <!-- LEFT COLUMN: Image + Status -->
                <div class="space-y-3">
                    <!-- Product Image -->
                    <div class="flex justify-center items-center">
                        <div class="flex flex-col items-center shadow-md">
                            <div class="relative group cursor-pointer w-60 h-60">
                                <img id="imagePreview"
                                    src="{{ $product->prod_image && file_exists(public_path('storage/' . $product->prod_image)) 
                                        ? asset('storage/' . $product->prod_image) 
                                        : asset('assets/no-product-image.png') }}"
                                    alt="Product Image"
                                    class="w-60 h-60 object-cover rounded shadow-sm">

                                <!-- Overlay edit icon -->
                                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center 
                                            opacity-0 group-hover:opacity-100 transition rounded"
                                    onclick="document.getElementById('prod_image').click();">
                                    <span class="material-symbols-outlined text-white text-3xl">edit</span>
                                </div>
                            </div>
                            <input type="file" name="prod_image" id="prod_image"
                                accept="image/png, image/jpeg, image/jpg, image/webp"
                                class="hidden">
                        </div>
                    </div>

                    <!-- Product Status -->
                    <div class="flex justify-center items-center">
                        <div class="bg-blue-50 p-3 mt-5 rounded-xl shadow-lg w-1/2 flex flex-col items-center">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Status</label>
                            <button type="button"
                                id="statusToggle"
                                class="relative inline-flex h-6 w-12 items-center rounded-full transition-colors focus:outline-none
                                    {{ old('prod_status', $product->prod_status) === 'active' ? 'bg-blue-500' : 'bg-gray-400' }}">
                                <span id="statusCircle"
                                    class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform
                                            {{ old('prod_status', $product->prod_status) === 'active' ? 'translate-x-6' : 'translate-x-1' }}">
                                </span>
                            </button>
                            <span id="statusLabel" class="mt-1 text-xs font-medium text-gray-700">
                                {{ old('prod_status', $product->prod_status) === 'active' ? 'Selling' : 'Archived' }}
                            </span>
                            <input type="hidden" id="prodStatusInput" name="prod_status" value="{{ old('prod_status', $product->prod_status) }}">
                        </div>
                    </div>
                </div>

                <!-- MIDDLE COLUMN: Product Info -->
                <div class="bg-gray-100 p-3 rounded-xl shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-800 mb-3">Product Information</h2>

                    <!-- Name with Validation -->
                    <div class="bg-white p-2 rounded-lg border shadow-md mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" id="productName" 
                            value="{{ old('name', $product->name) }}"
                            data-original-name="{{ $product->name }}"
                            data-prod-code="{{ $product->prod_code }}"
                            class="w-full border-gray-200 focus:border-blue-200 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-100" required>
                        <div id="nameValidation" class="mt-1 text-xs"></div>
                    </div>

                    <!-- Barcode with Validation -->
                    <div class="bg-white p-2 rounded-lg border shadow-md mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Barcode</label>
                        <input type="text" name="barcode" id="productBarcode" 
                            value="{{ old('barcode', $product->barcode) }}"
                            data-original-barcode="{{ $product->barcode }}"
                            data-prod-code="{{ $product->prod_code }}"
                            class="w-full border-gray-200 focus:border-blue-200 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-100">
                        <div id="barcodeValidation" class="mt-1 text-xs"></div>
                    </div>

                    <!-- Unit -->
                    <div class="bg-white p-2 rounded-lg border shadow-md mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Unit</label>
                        <select name="unit_id" class="w-full border-gray-200 focus:border-blue-200 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-100" required>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->unit_id }}" 
                                    {{ $product->unit_id == $unit->unit_id ? 'selected' : '' }}>
                                    {{ $unit->unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Minimum Stock Limit -->
                    <div class="bg-white p-2 rounded-lg border shadow-md mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Minimum Stock Limit</label>
                        <input type="number" name="stock_limit" value="{{ old('stock_limit', $product->stock_limit) }}"
                            class="w-full border-gray-200 focus:border-blue-200 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-100">
                    </div>

                    <!-- Description -->
                    <div class="bg-white p-2 rounded-lg border shadow-md">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description"
                            class="w-full border-gray-200 focus:border-blue-200 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-100 resize-none h-20">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Pricing & Tax -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-3 shadow-sm border border-green-200">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-600 text-lg">payments</span>
                            Pricing & Tax
                        </h3>
                    </div>

                    <!-- Bulk Purchase Calculator (Collapsible) -->
                    <div class="mb-3">
                        <button type="button" onclick="toggleBulkCalculator()" 
                            class="w-full flex items-center justify-between p-2 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition text-left">
                            <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-blue-600 text-sm">calculate</span>
                                Bulk Calculator
                                <span class="text-gray-500 font-normal">(Optional)</span>
                            </span>
                            <span class="material-symbols-outlined text-blue-600 text-lg" id="bulkToggleIcon">expand_more</span>
                        </button>
                        
                        <div id="bulkCalculatorSection" class="hidden mt-2 p-2.5 bg-white rounded-lg border border-blue-200 space-y-2">
                            <p class="text-xs text-gray-600">
                                💡 Buy in bulk but sell in smaller units
                            </p>
                            
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Quantity</label>
                                    <input type="number" id="bulkQuantity" min="1" placeholder="Qty" 
                                        class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm placeholder-gray-400">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs text-gray-600 mb-1">Per</label>
                                    <select id="bulkUnit" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                                        <option value="">Select...</option>
                                        <option value="pack">Pack</option>
                                        <option value="box">Box</option>
                                        <option value="dozen">Dozen (12)</option>
                                        <option value="bundle">Bundle</option>
                                        <option value="case">Case</option>
                                        <option value="kg">Kilogram</option>
                                        <option value="sack">Sack</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Bulk Cost Price</label>
                                <input type="number" step="0.01" min="0" id="bulkCostPrice" placeholder="₱0.00"
                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm placeholder-gray-400">
                            </div>
                            
                            <div class="pt-2 border-t border-gray-200">
                                <div class="flex justify-between items-center text-xs mb-2">
                                    <span class="font-semibold text-gray-700">Cost per unit:</span>
                                    <span id="calculatedUnitCost" class="font-bold text-green-600">₱0.00</span>
                                </div>
                                <button type="button" onclick="applyBulkCost()" 
                                    class="w-full px-3 py-1.5 bg-blue-500 text-white text-xs font-medium rounded hover:bg-blue-600 transition">
                                    Apply to Cost Price
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Cost Price & Tax Category Row -->
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1 mt-2">
                                Cost Price *
                            </label>
                            <input type="number" step="0.01" min="0" name="cost_price" id="costPrice" 
                                value="{{ old('cost_price', $product->cost_price) }}"
                                placeholder="0.00" required
                                class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent text-sm placeholder-gray-400 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1 flex items-center gap-1">
                                Tax Category *
                                <button type="button" onclick="toggleVatInfo()" class="text-blue-500 hover:text-blue-700">
                                    <span class="material-symbols-outlined text-sm">info</span>
                                </button>
                            </label>
                            <select id="vatCategory" name="vat_category" required
                                class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 text-sm transition">
                                <option value="vat_exempt" {{ old('vat_category', $product->vat_category ?? 'vat_inclusive') === 'vat_exempt' ? 'selected' : '' }}>VAT-Exempt (0%)</option>
                                <option value="vat_inclusive" {{ old('vat_category', $product->vat_category ?? 'vat_inclusive') === 'vat_inclusive' ? 'selected' : '' }}>VAT-Inclusive (12%)</option>
                            </select>
                        </div>
                    </div>

                    <!-- VAT Info Panel (Hidden by default) -->
                    <div id="vatInfoPanel" class="hidden mb-3 p-2 bg-blue-50 border border-blue-200 rounded-lg text-xs">
                        <p class="font-semibold text-blue-900 mb-1">📋 Tax Guidelines:</p>
                        <ul class="space-y-1 text-blue-800 text-xs">
                            <li><strong>0%:</strong> Raw vegetables, fruits, meat, fish, eggs, rice</li>
                            <li><strong>12%:</strong> Processed foods, beverages, snacks, household items</li>
                        </ul>
                    </div>

                    <!-- Markup Row -->
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Markup</label>
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <select id="markupType" class="px-2 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 transition">
                            <option value="percentage">Percentage %</option>
                            <option value="fixed">Fixed ₱</option>
                        </select>
                        <input type="number" id="markupValue" placeholder="Markup Value" min="0" step="0.01"
                            class="px-2 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 placeholder-gray-400 transition">
                    </div>

                    <!-- Selling Price with Compact Breakdown -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Selling Price per Unit *</label>
                        <input type="number" step="0.01" name="selling_price" id="sellingPrice" 
                            value="{{ old('selling_price', $product->selling_price) }}"
                            placeholder="0.00" required
                            class="w-full px-2 py-2 border border-gray-300 rounded-lg bg-gray-100 text-sm font-semibold mb-2" readonly>
                        
                        <!-- Compact Tax Breakdown -->
                        <div id="taxBreakdown" class="p-2 bg-white rounded-lg border border-gray-200">
                            <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Cost:</span>
                                    <span id="costDisplay" class="font-medium">₱0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span id="markupLabel" class="text-blue-600">Markup:</span>
                                    <span id="markupAmount" class="font-medium text-blue-600">₱0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Base:</span>
                                    <span id="basePrice" class="font-medium">₱0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span id="taxLabel" class="text-green-700">VAT (12%):</span>
                                    <span id="taxAmount" class="font-medium text-green-700">₱0.00</span>
                                </div>
                            </div>
                            <div class="pt-1.5 mt-1.5 border-t border-gray-300 flex justify-between font-semibold text-gray-900">
                                <span>Total Price:</span>
                                <span id="totalPrice">₱0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Previous Selling Prices -->
                    @if($priceHistory->count())
                        <div class="bg-white p-2 rounded-lg border shadow-md mt-3">
                            <p class="text-[10px] text-gray-500 mb-1">Previous Selling Prices</p>
                            <select id="previous_prices_dropdown"
                                    class="w-full text-xs border-gray-300 rounded px-2 py-1 focus:ring-1 focus:ring-blue-100">
                                <option value="">Select previous price...</option>
                                @foreach($priceHistory as $history)
                                    @if($history->effective_to)
                                        <option value="{{ $history->old_selling_price }}" 
                                                data-cost-price="{{ $history->old_cost_price }}"
                                                data-selling-price="{{ $history->old_selling_price }}"
                                                data-vat-category="{{ $history->vat_category ?? 'vat_inclusive' }}">
                                            ₱{{ number_format($history->old_selling_price, 2) }} 
                                            (Cost: ₱{{ number_format($history->old_cost_price, 2) }})
                                            – {{ \Carbon\Carbon::parse($history->effective_from)->format('M d, Y') }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Hidden inputs -->
                    <input type="hidden" name="previous_prices" id="previous_prices_hidden">
                    <input type="hidden" name="previous_cost_price" id="previous_cost_price_hidden">
                </div>
            </div>

            <!-- Submit -->
            <div class="mt-4 flex justify-end">
                <button type="submit" id="saveButton"
                    {{ $expired ? 'disabled' : '' }}
                    class="bg-green-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-600 transition-all duration-200 transform hover:scale-105 disabled:bg-gray-400 disabled:cursor-not-allowed disabled:transform-none">
                    Save Changes
                </button>
            </div>
        </form>
        <!-- Form End -->
    </div>
</div>

{{-- JS for Toast, Validation, Image Preview, Auto Pricing, Status Toggle --}}
<script>
    // === TOAST NOTIFICATION SYSTEM ===
    function showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        
        const bgColors = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'warning': 'bg-yellow-500',
            'info': 'bg-blue-500'
        };
        
        const icons = {
            'success': 'check_circle',
            'error': 'error',
            'warning': 'warning',
            'info': 'info'
        };
        
        toast.className = `${bgColors[type]} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 min-w-[300px] animate-slide-in`;
        toast.innerHTML = `
            <span class="material-symbols-outlined text-xl">${icons[type]}</span>
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        `;
        
        toastContainer.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // Show toast for Laravel session messages
    @if(session('success'))
        showToast("{{ session('success') }}", 'success');
    @endif

    @if(session('error'))
        showToast("{{ session('error') }}", 'error');
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            showToast("{{ $error }}", 'error');
        @endforeach
    @endif

    // === VALIDATION VARIABLES ===
    let validationTimeout;
    const saveButton = document.getElementById('saveButton');
    const productName = document.getElementById('productName');
    const productBarcode = document.getElementById('productBarcode');
    const nameValidation = document.getElementById('nameValidation');
    const barcodeValidation = document.getElementById('barcodeValidation');

    let nameExactMatch = false;
    let barcodeExactMatch = false;

    // === REAL-TIME NAME VALIDATION ===
    productName.addEventListener('input', function() {
        clearTimeout(validationTimeout);
        const value = this.value.trim();
        const originalName = this.getAttribute('data-original-name');
        const prodCode = this.getAttribute('data-prod-code');

        if (!value) {
            nameValidation.innerHTML = '';
            nameExactMatch = false;
            updateSaveButton();
            return;
        }

        if (value.toLowerCase() === originalName.toLowerCase()) {
            nameValidation.innerHTML = '';
            nameExactMatch = false;
            updateSaveButton();
            return;
        }

        nameValidation.innerHTML = '<span class="text-gray-500">Checking...</span>';

        validationTimeout = setTimeout(() => {
            fetch('{{ route("inventory-staff-check-name") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    name: value,
                    prod_code: prodCode
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.exact_match) {
                    nameValidation.innerHTML = '<span class="text-red-600 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>This product name already exists</span>';
                    nameExactMatch = true;
                } else if (data.similar_matches && data.similar_matches.length > 0) {
                    const matches = data.similar_matches.slice(0, 3).map(m => `"${m}"`).join(', ');
                    nameValidation.innerHTML = `<span class="text-yellow-600 flex items-center gap-1"><span class="material-symbols-outlined text-sm">warning</span>Similar: ${matches}</span>`;
                    nameExactMatch = false;
                } else {
                    nameValidation.innerHTML = '<span class="text-green-600 flex items-center gap-1"><span class="material-symbols-outlined text-sm">check_circle</span>Available</span>';
                    nameExactMatch = false;
                }
                updateSaveButton();
            })
            .catch(error => {
                console.error('Error:', error);
                nameValidation.innerHTML = '<span class="text-gray-500">Check failed</span>';
                nameExactMatch = false;
                updateSaveButton();
            });
        }, 500);
    });

    // === REAL-TIME BARCODE VALIDATION ===
    productBarcode.addEventListener('input', function() {
        clearTimeout(validationTimeout);
        const value = this.value.trim();
        const originalBarcode = this.getAttribute('data-original-barcode');
        const prodCode = this.getAttribute('data-prod-code');

        if (!value) {
            barcodeValidation.innerHTML = '';
            barcodeExactMatch = false;
            updateSaveButton();
            return;
        }

        if (value.toLowerCase() === originalBarcode.toLowerCase()) {
            barcodeValidation.innerHTML = '';
            barcodeExactMatch = false;
            updateSaveButton();
            return;
        }

        barcodeValidation.innerHTML = '<span class="text-gray-500">Checking...</span>';

        validationTimeout = setTimeout(() => {
            fetch('{{ route("inventory-staff-check-barcode-edit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    barcode: value,
                    prod_code: prodCode
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.exact_match) {
                    barcodeValidation.innerHTML = '<span class="text-red-600 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>This barcode already exists</span>';
                    barcodeExactMatch = true;
                } else if (data.similar_matches && data.similar_matches.length > 0) {
                    const matches = data.similar_matches.slice(0, 3).map(m => `"${m}"`).join(', ');
                    barcodeValidation.innerHTML = `<span class="text-yellow-600 flex items-center gap-1"><span class="material-symbols-outlined text-sm">warning</span>Similar: ${matches}</span>`;
                    barcodeExactMatch = false;
                } else {
                    barcodeValidation.innerHTML = '<span class="text-green-600 flex items-center gap-1"><span class="material-symbols-outlined text-sm">check_circle</span>Available</span>';
                    barcodeExactMatch = false;
                }
                updateSaveButton();
            })
            .catch(error => {
                console.error('Error:', error);
                barcodeValidation.innerHTML = '<span class="text-gray-500">Check failed</span>';
                barcodeExactMatch = false;
                updateSaveButton();
            });
        }, 500);
    });

    // === UPDATE SAVE BUTTON STATE ===
    function updateSaveButton() {
        if (nameExactMatch || barcodeExactMatch) {
            saveButton.disabled = true;
        } else {
            saveButton.disabled = {{ $expired ? 'true' : 'false' }};
        }
    }

    // === PRODUCT IMAGE PREVIEW ===
    const input = document.getElementById('prod_image');
    const preview = document.getElementById('imagePreview');

    preview.addEventListener('click', () => input.click());

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            preview.src = URL.createObjectURL(file);
        }
    });

    // === BULK CALCULATOR FUNCTIONS ===
    function toggleBulkCalculator() {
        const section = document.getElementById('bulkCalculatorSection');
        const icon = document.getElementById('bulkToggleIcon');
        
        if (section.classList.contains('hidden')) {
            section.classList.remove('hidden');
            icon.textContent = 'expand_less';
        } else {
            section.classList.add('hidden');
            icon.textContent = 'expand_more';
        }
    }

    function calculateBulkCost() {
        const quantity = parseFloat(document.getElementById('bulkQuantity').value) || 0;
        const bulkCost = parseFloat(document.getElementById('bulkCostPrice').value) || 0;
        
        if (quantity > 0 && bulkCost > 0) {
            const unitCost = bulkCost / quantity;
            document.getElementById('calculatedUnitCost').textContent = '₱' + unitCost.toFixed(2);
            return unitCost;
        }
        
        document.getElementById('calculatedUnitCost').textContent = '₱0.00';
        return 0;
    }

    function applyBulkCost() {
        const unitCost = calculateBulkCost();
        if (unitCost > 0) {
            document.getElementById('costPrice').value = unitCost.toFixed(2);
            calculateSellingPrice();
            showToast('Bulk cost applied successfully!', 'success');
        } else {
            showToast('Please enter valid bulk quantity and cost', 'warning');
        }
    }

    // Calculate on input
    document.getElementById('bulkQuantity').addEventListener('input', calculateBulkCost);
    document.getElementById('bulkCostPrice').addEventListener('input', calculateBulkCost);

    // === VAT INFO TOGGLE ===
    function toggleVatInfo() {
        const panel = document.getElementById('vatInfoPanel');
        panel.classList.toggle('hidden');
    }

    // === PRICING CALCULATION WITH VAT ===
    const costPrice = document.getElementById('costPrice');
    const markupType = document.getElementById('markupType');
    const markupValue = document.getElementById('markupValue');
    const sellingPrice = document.getElementById('sellingPrice');
    const vatCategory = document.getElementById('vatCategory');
    const previousPricesDropdown = document.getElementById('previous_prices_dropdown');
    const previousPricesHidden = document.getElementById('previous_prices_hidden');
    const previousCostPriceHidden = document.getElementById('previous_cost_price_hidden');

    function calculateSellingPrice() {
        const cost = parseFloat(costPrice.value) || 0;
        const markup = parseFloat(markupValue.value) || 0;
        const vatCat = vatCategory.value;
        
        let basePrice = cost;
        let markupAmt = 0;

        // Calculate markup
        if (markupType.value === 'percentage') {
            markupAmt = cost * (markup / 100);
            basePrice = cost + markupAmt;
        } else if (markupType.value === 'fixed') {
            markupAmt = markup;
            basePrice = cost + markup;
        }

        // Calculate VAT
        let vatAmount = 0;
        let finalPrice = basePrice;
        
        if (vatCat === 'vat_inclusive') {
            vatAmount = basePrice * 0.12;
            finalPrice = basePrice + vatAmount;
        }

        // Update selling price
        sellingPrice.value = finalPrice.toFixed(2);

        // Update breakdown display
        document.getElementById('costDisplay').textContent = '₱' + cost.toFixed(2);
        document.getElementById('markupAmount').textContent = '₱' + markupAmt.toFixed(2);
        document.getElementById('basePrice').textContent = '₱' + basePrice.toFixed(2);
        document.getElementById('taxAmount').textContent = '₱' + vatAmount.toFixed(2);
        document.getElementById('totalPrice').textContent = '₱' + finalPrice.toFixed(2);

        // Update labels
        if (markupType.value === 'percentage') {
            document.getElementById('markupLabel').textContent = `Markup (${markup}%):`;
        } else {
            document.getElementById('markupLabel').textContent = 'Markup (₱):';
        }

        document.getElementById('taxLabel').textContent = vatCat === 'vat_inclusive' ? 'VAT (12%):' : 'VAT (0%):';

        // Clear previous price selection
        if (previousPricesDropdown) {
            previousPricesDropdown.value = '';
        }
        if (previousPricesHidden) {
            previousPricesHidden.value = '';
        }
        if (previousCostPriceHidden) {
            previousCostPriceHidden.value = '';
        }
    }

    // === REVERSE CALCULATE MARKUP FROM EXISTING PRICES ===

    function reverseCalculateMarkup() {
        const cost = parseFloat(costPrice.value) || 0;
        const selling = parseFloat(sellingPrice.value) || 0;
        const vatCat = vatCategory.value;
        
        if (cost === 0 || selling === 0) {
            return;
        }
        
        // Remove VAT from selling price to get base price
        let basePrice = selling;
        if (vatCat === 'vat_inclusive') {
            // Selling price includes 12% VAT, so base = selling / 1.12
            basePrice = selling / 1.12;
        }
        
        // Calculate markup amount
        const markupAmt = basePrice - cost;
        
        if (markupAmt <= 0) {
            markupValue.value = '';
            return;
        }
        
        // Calculate percentage markup
        const markupPercentage = (markupAmt / cost) * 100;
        
        // Default to percentage display on page load
        markupType.value = 'percentage';
        markupValue.value = markupPercentage.toFixed(2);
    }

    // Event listeners for pricing
    costPrice.addEventListener('input', calculateSellingPrice);
    markupValue.addEventListener('input', calculateSellingPrice);
    vatCategory.addEventListener('change', calculateSellingPrice);
    markupType.addEventListener('change', function() {
    const cost = parseFloat(costPrice.value) || 0;
    const selling = parseFloat(sellingPrice.value) || 0;
    const vatCat = vatCategory.value;
    
    if (cost > 0 && selling > 0) {
        // Remove VAT to get base price
        let basePrice = selling;
        if (vatCat === 'vat_inclusive') {
            basePrice = selling / 1.12;
        }
        
        const markupAmt = basePrice - cost;
        const markupPercentage = (markupAmt / cost) * 100;
        
        // Convert between percentage and fixed based on selection
        if (this.value === 'percentage') {
            markupValue.value = markupPercentage.toFixed(2);
        } else {
            markupValue.value = markupAmt.toFixed(2);
        }
    }
    
    calculateSellingPrice();
});

    // === OLD PRICE SELECTION HANDLING ===
    if (previousPricesDropdown) {
        previousPricesDropdown.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value && selectedOption) {
                const costPriceValue = selectedOption.getAttribute('data-cost-price');
                const sellingPriceValue = selectedOption.getAttribute('data-selling-price');
                const vatCat = selectedOption.getAttribute('data-vat-category') || 'vat_inclusive';
                
                costPrice.value = costPriceValue;
                sellingPrice.value = sellingPriceValue;
                vatCategory.value = vatCat;
                
                previousPricesHidden.value = sellingPriceValue;
                previousCostPriceHidden.value = costPriceValue;

                markupValue.value = '';
                
                costPrice.classList.add('bg-blue-50', 'border-blue-200');
                sellingPrice.classList.add('bg-blue-50', 'border-blue-200');

                // Update breakdown display
                calculateSellingPrice();
            } else {
                previousPricesHidden.value = '';
                previousCostPriceHidden.value = '';
                
                costPrice.classList.remove('bg-blue-50', 'border-blue-200');
                sellingPrice.classList.remove('bg-blue-50', 'border-blue-200');
            }
        });
    }

    // Clear previous price selection on manual input
    if (costPrice) {
        costPrice.addEventListener('input', function() {
            if (previousPricesDropdown) {
                previousPricesDropdown.value = '';
            }
            if (previousPricesHidden) {
                previousPricesHidden.value = '';
            }
            if (previousCostPriceHidden) {
                previousCostPriceHidden.value = '';
            }
            costPrice.classList.remove('bg-blue-50', 'border-blue-200');
            sellingPrice.classList.remove('bg-blue-50', 'border-blue-200');
        });
    }

    // === PRODUCT STATUS TOGGLE ===
    const toggle = document.getElementById("statusToggle");
    const circle = document.getElementById("statusCircle");
    const label = document.getElementById("statusLabel");
    const statusInput = document.getElementById("prodStatusInput");

    toggle.addEventListener("click", function () {
        if (statusInput.value === "active") {
            statusInput.value = "archived";
            toggle.classList.remove("bg-blue-500");
            toggle.classList.add("bg-gray-400");
            circle.classList.remove("translate-x-6");
            circle.classList.add("translate-x-1");
            label.textContent = "Archived";
        } else {
            statusInput.value = "active";
            toggle.classList.remove("bg-gray-400");
            toggle.classList.add("bg-blue-500");
            circle.classList.remove("translate-x-1");
            circle.classList.add("translate-x-6");
            label.textContent = "Selling";
        }
    });

    // Initialize pricing calculation on page load
    document.addEventListener('DOMContentLoaded', function() {
        reverseCalculateMarkup();
        calculateSellingPrice();

    });

</script>

<style>
    @keyframes slide-in {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .animate-slide-in {
        animation: slide-in 0.3s ease-out;
    }
</style>

@endsection