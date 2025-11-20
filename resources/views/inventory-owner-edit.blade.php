@extends('dashboards.owner.owner')
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
            <a href="{{ route('inventory-owner') }}" 
               class="inline-flex items-center text-xs text-gray-500 hover:text-red-600 transition">
                <span class="material-symbols-outlined text-sm mr-1">cancel</span>
                Cancel
            </a>
        </div>

        <!-- Form Start -->
        <form action="{{ route('inventory-owner-update', $product->prod_code) }}" method="POST" enctype="multipart/form-data">
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

                <!-- RIGHT COLUMN: Pricing -->
                <div class="bg-gray-100 p-3 rounded-xl shadow-sm">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold text-sm text-gray-800">Pricing</h3>
                        <a href="{{ route('inventory-owner-pricing-history', $product->prod_code) }}"
                        class="text-[11px] text-blue-600 hover:underline flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">query_stats</span>
                            View History
                        </a>
                    </div>

                    <!-- Cost Price -->
                    <div class="bg-white p-2 rounded-lg border shadow-md mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Cost Price</label>
                        <input type="number" step="0.01" min="0" name="cost_price" id="costPrice"
                            value="{{ old('cost_price', $product->cost_price) }}"
                            class="w-full px-2 py-1 border-gray-200 focus:border-blue-200 focus:ring-2 focus:ring-blue-100 rounded text-sm"
                            placeholder="Cost Price" required>
                    </div>

                    <!-- Markup -->
                    <div class="bg-white p-2 rounded-lg border shadow-md mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Markup</label>
                        <div class="flex space-x-2">
                            <select id="markupType"
                                    class="w-1/2 px-2 py-1 border-gray-200 focus:border-blue-200 focus:ring-2 focus:ring-blue-100 rounded text-sm">
                                <option value="percentage">Percentage %</option>
                                <option value="fixed">Fixed ₱</option>
                            </select>
                            <input type="number" id="markupValue" placeholder="Value"
                                class="w-1/2 px-2 py-1 border-gray-200 focus:border-blue-200 focus:ring-2 focus:ring-blue-100 rounded text-sm">
                        </div>
                    </div>

                    <!-- Selling Price -->
                    <div class="bg-white p-2 rounded-lg border shadow-md mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Selling Price</label>
                        <input type="number" step="0.01" name="selling_price" id="selling_price"
                            value="{{ old('selling_price', $product->selling_price) }}"
                            class="w-full px-2 py-1 border-gray-200 focus:border-blue-200 focus:ring-2 focus:ring-blue-100 rounded text-sm">
                    </div>

                    <!-- Hidden inputs -->
                    <input type="hidden" name="previous_prices" id="previous_prices_hidden">
                    <input type="hidden" name="previous_cost_price" id="previous_cost_price_hidden">

                    <!-- Previous Selling Prices -->
                    @if($priceHistory->count())
                        <div class="bg-white p-2 rounded-lg border shadow-md">
                            <p class="text-[10px] text-gray-500 mb-1">Previous Selling Prices</p>
                            <select id="previous_prices_dropdown"
                                    class="w-full text-xs border-gray-300 rounded px-2 py-1 focus:ring-1 focus:ring-blue-100">
                                <option value="">Select previous price...</option>
                                @foreach($priceHistory as $history)
                                    @if($history->effective_to)
                                        <option value="{{ $history->old_selling_price }}" 
                                                data-cost-price="{{ $history->old_cost_price }}"
                                                data-selling-price="{{ $history->old_selling_price }}">
                                            ₱{{ number_format($history->old_selling_price, 2) }} 
                                            (Cost: ₱{{ number_format($history->old_cost_price, 2) }})
                                            — {{ \Carbon\Carbon::parse($history->effective_from)->format('M d, Y') }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    @endif
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
        
        // Auto remove after 5 seconds
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

    let validationTimeout;
    const saveButton = document.getElementById('saveButton');
    const productName = document.getElementById('productName');
    const productBarcode = document.getElementById('productBarcode');
    const nameValidation = document.getElementById('nameValidation');
    const barcodeValidation = document.getElementById('barcodeValidation');

    // Validation state
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

        // Don't validate if it's the same as original
        if (value.toLowerCase() === originalName.toLowerCase()) {
            nameValidation.innerHTML = '';
            nameExactMatch = false;
            updateSaveButton();
            return;
        }

        nameValidation.innerHTML = '<span class="text-gray-500">Checking...</span>';

        validationTimeout = setTimeout(() => {
            fetch('{{ route("inventory-owner-check-name") }}', {
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

        // Don't validate if it's the same as original
        if (value.toLowerCase() === originalBarcode.toLowerCase()) {
            barcodeValidation.innerHTML = '';
            barcodeExactMatch = false;
            updateSaveButton();
            return;
        }

        barcodeValidation.innerHTML = '<span class="text-gray-500">Checking...</span>';

        validationTimeout = setTimeout(() => {
            fetch('{{ route("inventory-owner-check-barcode-edit") }}', {
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
                    barcodeValidation.innerHTML = '<span class="text-green-600 flex items-center gap-1"><span class="material-symbols-oriented text-sm">check_circle</span>Available</span>';
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

    // === PRICING CALCULATION ===
    const costPrice = document.getElementById('costPrice');
    const markupType = document.getElementById('markupType');
    const markupValue = document.getElementById('markupValue');
    const sellingPrice = document.getElementById('selling_price');
    const previousPricesDropdown = document.getElementById('previous_prices_dropdown');
    const previousPricesHidden = document.getElementById('previous_prices_hidden');
    const previousCostPriceHidden = document.getElementById('previous_cost_price_hidden');

    function calculateSellingPrice() {
        const cost = parseFloat(costPrice.value) || 0;
        const markup = parseFloat(markupValue.value) || 0;
        let result = cost;

        if (markupType.value === 'percentage') {
            result = cost + (cost * (markup / 100));
        } else if (markupType.value === 'fixed') {
            result = cost + markup;
        }

        sellingPrice.value = result.toFixed(2);
        
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

    costPrice.addEventListener('input', calculateSellingPrice);
    markupValue.addEventListener('input', calculateSellingPrice);
    markupType.addEventListener('change', calculateSellingPrice);

    // === OLD PRICE SELECTION HANDLING ===
    if (previousPricesDropdown) {
        previousPricesDropdown.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value && selectedOption) {
                const costPriceValue = selectedOption.getAttribute('data-cost-price');
                const sellingPriceValue = selectedOption.getAttribute('data-selling-price');
                
                costPrice.value = costPriceValue;
                sellingPrice.value = sellingPriceValue;
                
                previousPricesHidden.value = sellingPriceValue;
                previousCostPriceHidden.value = costPriceValue;

                markupValue.value = '';
                
                costPrice.classList.add('bg-blue-50', 'border-blue-200');
                sellingPrice.classList.add('bg-blue-50', 'border-blue-200');
            } else {
                previousPricesHidden.value = '';
                previousCostPriceHidden.value = '';
                
                costPrice.classList.remove('bg-blue-50', 'border-blue-200');
                sellingPrice.classList.remove('bg-blue-50', 'border-blue-200');
            }
        });
    }

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

    if (sellingPrice) {
        sellingPrice.addEventListener('input', function() {
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