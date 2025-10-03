@extends('dashboards.owner.owner')
<head>
    <title>Edit Product Details</title>
</head>
@section('content')
<div class="px-4">
    @livewire('expiration-container')
</div>
<div class="max-w-3xl mx-auto mt-4 mb-6">
    <div class="bg-white rounded-2xl shadow-lg p-4 space-y-2">

        <!-- Page Title -->
        <div class="flex items-center justify-between border-b pb-2 mb-4">
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

            <!-- Product Image + Status Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Product Image -->
                <div class="flex justify-center items-center">
                    <div class="flex flex-col items-center shadow-md">
                        <div class="relative group cursor-pointer w-36 h-36">
                            <img id="imagePreview"
                                src="{{ $product->prod_image && file_exists(public_path('storage/' . $product->prod_image)) 
                                    ? asset('storage/' . $product->prod_image) 
                                    : asset('assets/no-product-image.png') }}"

                                alt="Product Image"
                                class="w-36 h-36 object-cover rounded shadow-sm">

                            <!-- Overlay edit icon -->
                            <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center 
                                        opacity-0 group-hover:opacity-100 transition rounded"
                                onclick="document.getElementById('prod_image').click();">
                                <span class="material-symbols-outlined text-white text-3xl">edit</span>
                            </div>
                        </div>
                        <!-- ✅ Now inside form -->
                        <input type="file" name="prod_image" id="prod_image"
                            accept="image/png, image/jpeg, image/jpg, image/webp"
                            class="hidden">
                    </div>
                </div>

                <!-- Product Status -->
                <div class="flex justify-center items-center">
                    <div class="bg-green-50 border border-green-400 p-4 rounded-xl shadow-md w-52 flex flex-col items-center">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product Status</label>
                        <button type="button"
                            id="statusToggle"
                            class="relative inline-flex h-6 w-12 items-center rounded-full transition-colors focus:outline-none
                                {{ old('prod_status', $product->prod_status) === 'active' ? 'bg-green-500' : 'bg-gray-400' }}">
                            <span id="statusCircle"
                                class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform
                                        {{ old('prod_status', $product->prod_status) === 'active' ? 'translate-x-6' : 'translate-x-1' }}">
                            </span>
                        </button>
                        <span id="statusLabel" class="mt-1 text-xs font-medium text-gray-700">
                            {{ old('prod_status', $product->prod_status) === 'active' ? 'Selling' : 'Archived' }}
                        </span>
                        <!-- ✅ Now inside form -->
                        <input type="hidden" id="prodStatusInput" name="prod_status" value="{{ old('prod_status', $product->prod_status) }}">
                    </div>
                </div>
            </div>

            <!-- Product Info & Pricing -->
            <div class="bg-gray-100 p-3 rounded-xl shadow-sm space-y-3 mt-3">
                <h2 class="text-sm font-semibold text-gray-800">Product Information</h2>

                <!-- Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <!-- Name -->
                    <div class="bg-white p-2 rounded-lg border shadow-md">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}"
                            class="w-full border-gray-200 focus:border-blue-200 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-100" required>
                    </div>

                    <!-- Barcode -->
                    <div class="bg-white p-2 rounded-lg border shadow-md">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Barcode</label>
                        <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}"
                            class="w-full border-gray-200 focus:border-blue-200 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-100">
                    </div>

                    <!-- Unit -->
                    <div class="bg-white p-2 rounded-lg border shadow-md">
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

                    <!-- Stock Limit -->
                    <div class="bg-white p-2 rounded-lg border shadow-md">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Stock Limit</label>
                        <input type="number" name="stock_limit" value="{{ old('stock_limit', $product->stock_limit) }}"
                            class="w-full border-gray-200 focus:border-blue-200 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>

                <!-- Description + Pricing -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <!-- Description -->
                    <div class="bg-white p-2 rounded-lg border shadow-md flex flex-col">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description"
                            class="flex-1 w-full border-gray-200 focus:border-blue-200 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-100 resize-none h-24">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <!-- Pricing -->
                    <div class="bg-white p-2 rounded-lg border shadow-md flex flex-col">
                        <h3 class="font-semibold text-xs text-center mb-1">Pricing</h3>

                        <input type="number" step="0.01" min="0" name="cost_price" id="costPrice"
                            value="{{ old('cost_price', $product->cost_price) }}"
                            class="w-full px-2 py-1 mb-1 border-gray-200 focus:border-blue-200 focus:ring-2 focus:ring-blue-100 rounded text-sm" placeholder="Cost Price" required>

                        <div class="flex space-x-2 mb-1">
                            <select id="markupType" class="w-1/2 px-2 py-1 border-gray-200 focus:border-blue-200 focus:ring-2 focus:ring-blue-100 rounded text-sm">
                                <option value="percentage">Percentage %</option>
                                <option value="fixed">Fixed ₱</option>
                            </select>
                            <input type="number" id="markupValue" placeholder="Markup Value"
                                class="w-1/2 px-2 py-1 border-gray-200 focus:border-blue-200 focus:ring-2 focus:ring-blue-100 rounded text-sm">
                        </div>

                        <input 
                            type="number" step="0.01" name="selling_price" id="sellingPrice" value="{{ old('selling_price', number_format($product->selling_price, 2, '.', '')) }}"
                            class="w-full px-2 py-1 border-gray-200 focus:border-blue-200 focus:ring-2 focus:ring-blue-100 rounded text-sm" readonly
                        >

                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="mt-4 flex justify-end">
                <button type="submit" {{ $expired ? 'disabled' : '' }}
                    class="bg-green-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-600 transition-all duration-200 transform hover:scale-105 {{ $expired ? 'cursor-not-allowed' : '' }}">
                    Save Changes
                </button>
            </div>
        </form>
        <!-- Form End -->
    </div>
</div>

{{-- JS for Image Preview, Auto Pricing & Status Toggle --}}
<script>
    const input = document.getElementById('prod_image');
    const preview = document.getElementById('imagePreview');
    const costPrice = document.getElementById('costPrice');
    const markupType = document.getElementById('markupType');
    const markupValue = document.getElementById('markupValue');
    const sellingPrice = document.getElementById('sellingPrice');

    preview.addEventListener('click', () => input.click());

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            preview.src = URL.createObjectURL(file);
        }
    });

       
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
    }

    // Only trigger on changes, not on page load
    costPrice.addEventListener('input', calculateSellingPrice);
    markupValue.addEventListener('input', calculateSellingPrice);
    markupType.addEventListener('change', calculateSellingPrice);


   // Product status toggle
    const toggle = document.getElementById("statusToggle");
    const circle = document.getElementById("statusCircle");
    const label = document.getElementById("statusLabel");
    const statusInput = document.getElementById("prodStatusInput");

    toggle.addEventListener("click", function () {
        if (statusInput.value === "active") {
            statusInput.value = "archived";
            toggle.classList.remove("bg-green-500");
            toggle.classList.add("bg-gray-400");
            circle.classList.remove("translate-x-6");
            circle.classList.add("translate-x-1");
            label.textContent = "Archived";
        } else {
            statusInput.value = "active";
            toggle.classList.remove("bg-gray-400");
            toggle.classList.add("bg-green-500");
            circle.classList.remove("translate-x-1");
            circle.classList.add("translate-x-6");
            label.textContent = "Selling";
        }
    });
</script>
@endsection
