@extends('dashboards.owner.owner')
<head>
    <title>Edit Product Details</title>
</head>
@section('content')
<div class="px-4">
    @livewire('expiration-container')
</div>
<div class="max-w-3xl mx-auto mt-6">
    
    <!-- Page Title -->
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-800">Edit Product</h1>
        
        <!-- Back Button -->
        <a href="{{ route('inventory-owner') }}" 
           class="inline-flex items-center text-xs text-gray-600 hover:text-gray-900">
            <span class="material-symbols-outlined text-sm mr-1">arrow_back</span>
            Inventory
        </a>
    </div>

    <!-- Edit Form Card -->
    <div class="bg-white p-4 rounded-lg shadow-md">
        <form action="{{ route('inventory-owner-update', $product->prod_code) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <!-- Name -->
                <div>
                    <label class="block text-xs font-medium text-gray-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}"
                           class="mt-1 w-full border rounded px-2 py-1 text-sm focus:ring focus:ring-orange-200" required>
                </div>

                <!-- Barcode -->
                <div>
                    <label class="block text-xs font-medium text-gray-700">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}"
                           class="mt-1 w-full border rounded px-2 py-1 text-sm focus:ring focus:ring-orange-200">
                </div>

                <!-- Unit -->
                <div>
                    <label class="block text-xs font-medium text-gray-700">Unit</label>
                    <select name="unit_id" class="mt-1 w-full border rounded px-2 py-1 text-sm focus:ring focus:ring-orange-200" required>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->unit_id }}" 
                                {{ $product->unit_id == $unit->unit_id ? 'selected' : '' }}>
                                {{ $unit->unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Stock Limit -->
                <div>
                    <label class="block text-xs font-medium text-gray-700">Stock Limit</label>
                    <input type="number" name="stock_limit" value="{{ old('stock_limit', $product->stock_limit) }}"
                           class="mt-1 w-full border rounded px-2 py-1 text-sm focus:ring focus:ring-orange-200">
                </div>
            </div>

           <!-- Description + Pricing Side by Side -->
<div class="grid grid-cols-2 gap-4 mt-3 items-stretch">
    <!-- Description -->
    <div class="flex flex-col">
        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
        <textarea name="description"
                  class="flex-1 w-full border rounded px-2 py-1 text-sm focus:ring focus:ring-orange-200 resize-none">{{ old('description', $product->description) }}</textarea>
    </div>

    <!-- Pricing -->
    <div class="border rounded p-2 bg-gray-50 flex flex-col">
        <h3 class="font-semibold text-xs text-center mb-2">Pricing</h3>

        <!-- Cost Price -->
        <input type="number" step="0.01" min="0" name="cost_price" id="costPrice"
            value="{{ old('cost_price', $product->cost_price) }}"
            class="w-full px-2 py-1 mb-2 border border-gray-300 rounded text-sm"
            placeholder="Cost Price" required>

        <!-- Markup Type & Value -->
        <div class="flex space-x-2 mb-2">
            <select id="markupType" class="w-1/2 px-2 py-1 border border-gray-300 rounded text-sm">
                <option value="percentage">Percentage %</option>
                <option value="fixed">Fixed ₱</option>
            </select>
            <input type="number" id="markupValue" placeholder="Markup Value"
                class="w-1/2 px-2 py-1 border border-gray-300 rounded text-sm">
        </div>

        <!-- Selling Price -->
        <input type="number" step="0.01" name="selling_price" id="sellingPrice"
            value="{{ old('selling_price', $product->selling_price) }}"
            class="w-full px-2 py-1 border border-gray-300 rounded text-sm" readonly>

        <!-- Spacer to push fields up (ensures equal height) -->
        <div class="flex-1"></div>
    </div>
</div>


            <!-- Product Image -->
            <div class="mt-3">
                <label class="block text-xs font-medium text-gray-700">Product Image</label>
                <input type="file" name="prod_image" id="prod_image" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden">
                <div class="mt-2 cursor-pointer inline-block">
                    <img id="imagePreview"
                         src="{{ $product->prod_image ? '/storage/' . $product->prod_image : '/assets/no-image.png' }}"
                         alt="Product Image"
                         class="w-32 h-32 object-cover rounded border shadow-sm"
                         title="Click to change image">
                </div>
            </div>

            <!-- Submit -->
            <div class="mt-4 flex justify-end">
                <button type="submit"
                    {{ $expired ? 'disabled' : '' }}
                        class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 transition
                        {{ $expired ? 'cursor-not-allowed' : '' }}">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

{{-- JS for Image Preview & Auto Pricing --}}
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

    costPrice.addEventListener('input', calculateSellingPrice);
    markupValue.addEventListener('input', calculateSellingPrice);
    markupType.addEventListener('change', calculateSellingPrice);

    calculateSellingPrice();
</script>
@endsection
