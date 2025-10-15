@extends('dashboards.owner.owner') 
<head>
    <title>Inventory</title>
</head>
@section('content')

        <!-- Inventory Table -->
        <div class="px-4 space-y-4">
            @livewire('expiration-container')
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Product List</h2>

            <div class="flex justify-between items-center mt-4 mb-4">
            {{-- Left side: Filter + Search --}}
            <div class="flex gap-2 items-center">
                {{-- Filter Button --}}
                <div class="relative">
                    <button 
                        id="filterToggle" 
                        type="button" 
                        class="flex items-center border border-[#FF8A00] text-[#FF8A00] bg-transparent px-4 py-2 mb-4 rounded hover:bg-orange-50 transition"
                    >
                        <span class="material-symbols-outlined mr-2 text-[#FF8A00]">filter_alt</span> Filter
                    </button>

                    <div id="categoryDropdown" 
                        class="absolute z-10 bg-white border border-gray-300 mt-2 rounded shadow hidden min-w-max max-h-60 overflow-y-auto"
                    >
                        <button 
                            onclick="filterByCategory('all')" 
                            class="block w-full text-left px-4 py-2 hover:bg-gray-100 font-semibold"
                        >
                            All
                        </button>

                        @foreach ($categories as $category)
                            <button 
                                onclick="filterByCategory('{{ $category->category_id }}')" 
                                class="block w-full text-left px-4 py-2 hover:bg-gray-100"
                            >
                                {{ $category->category }}
                            </button>
                        @endforeach
                    </div>

                </div>


                {{-- Search Bar --}}
                <form method="GET" action="{{ url('inventory-owner') }}" class="relative w-72">
                    <input 
                        type="text" 
                        id="search"
                        name="search" 
                        placeholder="Search by name or barcode" 
                        value="{{ $search ?? '' }}"
                        autocomplete="off"
                        class="rounded px-4 py-2 w-full pr-10 shadow-lg focus:border-[#FF8A00] focus:shadow-lg border border-gray-50 placeholder:text-sm placeholder-gray-400"
                    >

                    {{-- Carry status --}}
                    <input type="hidden" name="status" value="{{ $status ?? 'active' }}">

                    {{-- Suggestions Dropdown --}}
                    <div 
                        id="suggestions" 
                        class="absolute z-10 w-full bg-white border border-gray-300 rounded shadow-md hidden max-h-60 overflow-y-auto mt-1"
                    ></div>
                    {{-- Search Icon --}}
                    <button 
                        type="submit" 
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-[#FF8A00] hover:text-orange-600"
                    >
                        <span class="material-symbols-outlined text-[#FF8A00]">search</span>
                    </button>
                </form>


                <!-- Status Toggle + Settings -->
                <div class="flex items-center gap-2">
                    <form action="{{ route('inventory-owner') }}" method="GET" id="statusToggleForm" class="flex items-center gap-2">
                        <div class="relative flex bg-[#f09d39] rounded-full p-1 w-44">
                            <input type="hidden" name="status" id="statusInput" value="{{ $status ?? 'active' }}">
                            
                            <!-- Selling (Active) -->
                            <button type="button" 
                                id="activeBtn" 
                                class="flex-1 text-center text-sm py-1 rounded-full transition-all duration-300
                                {{ ($status ?? 'active') === 'active' 
                                    ? 'bg-white text-[#f09d39] shadow' 
                                    : 'text-white' }}">
                                Selling
                            </button>

                            <!-- Archived -->
                            <button type="button" 
                                id="archivedBtn" 
                                class="flex-1 text-center text-sm py-1 rounded-full transition-all duration-300
                                {{ ($status ?? 'active') === 'archived' 
                                    ? 'bg-white text-[#f09d39] shadow' 
                                    : 'text-white' }}">
                                Archived
                            </button>
                        </div>
                    </form>

                    <!-- Settings Icon -->
                    <a href="{{ route('inventory-owner-settings') }}" 
                    class="flex items-center justify-center w-10 h-10 mb-4 rounded-full bg-white shadow-lg transition" title="Category and Unit Settings">
                        <span class="material-symbols-outlined text-[#f09d39]">category</span>
                    </a>
                </div>
            </div>

            {{-- Right side: Add Product --}}
            <div>
                <button id="addProductBtn" {{ $expired ? 'disabled' : '' }}
                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-all duration-200 transform hover:scale-105
                        {{ $expired ? 'cursor-not-allowed' : '' }}">
                    Add Product
                </button>
            </div>
        </div>


            
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full table-auto border border-gray-100">
                    <thead class="bg-gray-100 text-gray-700 text-md">
                        <tr>
                            <th class="px-4 py-3">Image</th>
                            <th class="px-4 py-3">Barcode</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Cost Price</th>
                            <th class="px-4 py-3">Selling Price</th>
                            <th class="px-4 py-3">Unit</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-800">
                        @forelse ($products as $product)
                            <tr class="hover:bg-blue-50">
                                <!-- Product Image -->
                                <td class="px-4 py-2 border text-center">
                                    @if($product->prod_image)
                                        <img src="{{ Str::startsWith($product->prod_image, 'assets/') 
                                            ? asset($product->prod_image) 
                                            : asset('storage/' . $product->prod_image) }}" 
                                            alt="Product Image" 
                                            class="h-16 w-16 object-cover rounded mx-auto">
                                    @else
                                        <img src="{{ asset('assets/no-product.png') }}" 
                                            alt="Image Not Found" 
                                            class="h-16 w-16 object-cover rounded mx-auto">
                                    @endif
                                </td>

                                <!-- Barcode -->
                                <td class="px-4 py-2 border text-center">{{ $product->barcode }}</td>

                                <!-- Name -->
                                <td class="px-4 py-2 border">{{ $product->name }}</td>

                                <!-- Cost Price -->
                                <td class="px-4 py-2 border text-right">
                                    ₱{{ number_format($product->cost_price, 2) }}
                                </td>

                                <!-- Selling Price -->
                                <td class="px-4 py-2 border text-right">
                                    ₱{{ number_format($product->selling_price, 2) }}
                                </td>

                                <!-- Unit -->
                                <td class="px-4 py-2 border text-center">{{ $product->unit ?? '—' }}</td>

                                <!-- Stock -->
                                <td class="px-4 py-2 border text-center">{{ $product->stock }}</td>

                                <!-- Actions -->
                                <td class="px-4 py-2 border text-center space-x-2">
                                    <!-- Info -->
                                    <a href="{{ route('inventory-product-info', $product->prod_code) }}" 
                                    class="text-blue-500 hover:text-blue-700">
                                        <span class="material-symbols-outlined">info</span>
                                    </a>

                                    <!-- Edit -->
                                    <a href="{{$expired ? '' : route('inventory-owner-edit', $product->prod_code)}}"
                                        onclick="{{ $expired ? 'event.preventDefault();' : '' }}"
                                        title="Edit" class="text-green-500 hover:text-green-700
                                        {{ $expired ? 'cursor-not-allowed' : '' }}">
                                        <span class="material-symbols-outlined">edit</span>
                                    </a>
                                    <!-- Archive / Unarchive Button -->
                                    @if ($product->prod_status === 'active')
                                        <button type="button"
                                            class="text-red-500 hover:text-red-700 {{ $expired ? 'cursor-not-allowed' : '' }}"
                                            title="Archive"
                                            @if(!$expired)
                                                onclick="openStatusModal('archive', '{{ $product->prod_code }}', '{{ $product->name }}', '{{ $product->barcode }}', '{{ $product->prod_image ?? '' }}')"
                                            @else
                                                disabled
                                            @endif
                                        >
                                            <span class="material-symbols-outlined">archive</span>
                                        </button>

                                    @else
                                        <button type="button"
                                            class="text-orange-400 hover:text-orange-600 {{ $expired ? 'cursor-not-allowed' : '' }}"
                                            title="Unarchive"
                                            @if(!$expired)
                                                onclick="openStatusModal('unarchive', '{{ $product->prod_code }}', '{{ $product->name }}', '{{ $product->barcode }}', '{{ $product->prod_image ?? '' }}')"
                                            @else
                                                disabled
                                            @endif
                                        >
                                            <span class="material-symbols-outlined">unarchive</span>
                                        </button>

                                    @endif


                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-gray-500">
                                    No products available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>


    <!-- Archive / Unarchive Confirmation Modal -->
    <div id="statusModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
            <!-- Title -->
            <h2 id="statusModalTitle" class="text-xl font-semibold text-gray-800 mb-2"></h2>
            <p id="statusModalMessage" class="text-sm text-gray-600 mb-4"></p>

            <!-- Product Preview Card -->
            <div class="flex items-center gap-4 border rounded-lg p-4 bg-gray-50 mb-5">
                <!-- Product Image -->
                <img id="statusProductImage" src="" alt="Product Image" class="h-20 w-20 rounded object-cover border" 
                    onerror="this.src='/assets/no-product-image.png'">


                <!-- Product Details -->
                <div class="text-left flex-1 min-w-0">
                    <p class="text-sm text-gray-700">
                        <strong>Name:</strong>
                        <span id="statusProductName"
                            class="inline-block max-w-[200px] truncate align-middle"
                            title="">
                        </span>
                    </p>
                    <p class="text-sm text-gray-700">
                        <strong>Barcode:</strong>
                        <span id="statusProductBarcode"
                            class="inline-block max-w-[200px] truncate align-middle"
                            title="">
                        </span>
                    </p>
                </div>

            </div>

            <!-- Form -->
            <form id="statusForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeStatusModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button id="statusSubmitBtn" type="submit"
                            class="px-4 py-2 text-sm rounded text-white">
                    </button>
                </div>
            </form>
        </div>
    </div>


    
    <!-- Add Product Modal -->
    <div id="addProductModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg p-8 w-[90%] max-w-md min-h-[550px] shadow-lg relative">
            <!-- Close Button -->
            <button id="closeAddProductModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">
                <span class="material-symbols-outlined text-gray-500">close</span>
            </button>

            <h2 class="text-xl font-semibold mb-6 text-center text-[#B50612]">Choose Action</h2>

            <div class="grid grid-cols-1 gap-4">
                <!-- Scan Barcode Option -->
                <div onclick="openScanModal()" class="cursor-pointer border border-gray-300 rounded-lg p-4 text-center hover:shadow-lg hover:shadow-red-200 hover:border-red-200 transition-all duration-200 transform hover:scale-105">
                    <img src="{{ asset('assets/scan-barcode.png') }}" alt="Scan Barcode" class="mx-auto h-16 mb-2">
                    <p class="font-medium">Scan Barcode</p>
                </div>

                <!-- Type Barcode Option -->
                <div onclick="openTypeModal()" class="cursor-pointer border border-gray-300 rounded-lg p-4 text-center hover:shadow-lg hover:shadow-red-200 hover:border-red-200 transition-all duration-200 transform hover:scale-105">
                    <img src="{{ asset('assets/type-barcode.png') }}" alt="Type Barcode" class="mx-auto h-16 mb-2">
                    <p class="font-medium">Type Barcode</p>
                </div>

                <!-- Generate Barcode Option -->
                <div onclick="openGenerateModal()" class="cursor-pointer border border-gray-300 rounded-lg p-4 text-center hover:shadow-lg hover:shadow-red-200 hover:border-red-200 transition-all duration-200 transform hover:scale-105">
                    <img src="{{ asset('assets/generate-barcode.png') }}" alt="Generate Barcode" class="mx-auto h-16 mb-2">
                    <p class="font-medium">Generate Barcode</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Type Barcode Modal -->
    <div id="typeBarcodeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg w-[50%] min-h-[550px] shadow-lg relative flex flex-col items-center">

            <!-- Red Top Bar -->
            <div class="bg-[#B50612] w-full h-16 flex items-center justify-between px-6 rounded-t-lg">
                <h2 class="text-white text-lg font-medium">Enter Product Barcode</h2>
                <button onclick="closeTypeModal()" class="text-white hover:text-gray-200">
                    <span class="material-symbols-outlined text-white">close</span>
                </button>
            </div>

            <!-- Modal Content Center -->
            <div class="flex-1 w-full flex flex-col items-center justify-center px-6 py-8 mb-16">
                <div class="mb-4">
                    <img src="{{ asset('assets/type-barcode.png') }}" alt="Type Barcode" class="h-32 mx-auto">
                </div>

                <form id="barcodeForm" class="w-2/4 space-y-4">
                    <input
                        type="text"
                        name="barcode"
                        id="barcodeInput"
                        placeholder="Enter barcode here"
                        class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-red-600 mb-4 placeholder:text-sm" 
                        inputmode="numeric"
                        pattern="[0-9]*"
                        required 
                    >

                    <button type="submit" class="w-2/6 bg-black mx-auto block text-white py-3 rounded-3xl hover:bg-gray-800 transition-all duration-200 transform hover:scale-105">
                        Submit
                    </button>
                </form>
            </div>
        </div>
    </div>


    <!-- Scan Barcode Modal -->
    <div id="scanBarcodeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg w-[50%] min-h-[550px] shadow-lg relative flex flex-col items-center">
            
            <!-- Red Top Bar -->
            <div class="bg-[#B50612] w-full h-16 flex items-center justify-between px-6 rounded-t-lg">
                <h2 class="text-white text-lg font-medium">Scan Product Barcode</h2>
                <button onclick="closeScanModal()" class="text-white hover:text-gray-200">
                    <span class="material-symbols-outlined text-white">close</span>
                </button>
            </div>

            <!-- Modal Content Center -->
            <div class="flex-1 w-full flex flex-col items-center justify-center px-6 py-8 mb-16">
                <div class="mb-4">
                    <img src="{{ asset('assets/scan-barcode.png') }}" alt="Scan Barcode" class="h-32 mx-auto">
                </div>

                <p class="text-gray-600 mb-6 text-center text-xs">Place your cursor inside the field below, then scan the barcode using your barcode scanner device.</p>

                <input
                    type="text"
                    id="scannedBarcodeInput"
                    placeholder="Waiting for barcode..."
                    class="w-2/4 px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-red-600 mb-4 placeholder:text-sm text-center"
                    autofocus
                >

                <button type="button"
                    onclick="processScannedBarcode()"
                    class="w-2/6 bg-black mx-auto block text-white py-3 rounded-3xl hover:bg-gray-800 transition-all duration-200 transform hover:scale-105">
                    Submit
                </button>
            </div>
        </div>
    </div>


   <!-- Generate Barcode Modal -->
<div id="generateBarcodeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="bg-white rounded-lg w-[50%] min-h-[550px] shadow-lg relative flex flex-col items-center">

        <!-- Red Top Bar -->
        <div class="bg-[#B50612] w-full h-16 flex items-center justify-between px-6 rounded-t-lg">
            <h2 class="text-white text-lg font-medium">Generate New Barcode</h2>
            <button onclick="closeGenerateModal()" class="text-white hover:text-gray-200">
                <span class="material-symbols-outlined text-white">close</span>
            </button>
        </div>

        <!-- Modal Content -->
        <div class="flex-1 w-full flex flex-col items-center justify-center px-6 py-8 mb-16">

            <!-- Step 1: Choose Product Category -->
            <div class="w-1/2 mb-6">
                <select id="barcodeCategory" 
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-red-600 text-center text-sm" 
                    required>
                    <option value="">Select Product Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ strtoupper(substr($cat->category, 0, 3)) }}">
                            {{ $cat->category }}
                        </option>
                    @endforeach
                    <option value="other">Other...</option>
                </select>

                <!-- Custom Prefix (hidden initially) -->
                <input type="text" id="customPrefixInput" maxlength="5"
                    placeholder="Enter custom prefix (2–5 letters)"
                    class="hidden mt-3 w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 text-center uppercase text-sm placeholder-gray-400"
                >

                <!-- Error Message -->
                <p id="categoryError" class="text-red-600 text-sm text-center mt-2 hidden"></p>
            </div>

            <!-- Generate Button (First Step) -->
            <button id="generateBarcodeBtn"
                class="bg-black text-white text-sm px-8 py-3 rounded-3xl hover:bg-gray-800 transition-all duration-200 transform hover:scale-105">
                Generate Barcode
            </button>

            <!-- Step 2: Display Generated Barcode (Hidden Initially) -->
            <div id="generatedSection" class="hidden flex flex-col items-center mt-8 space-y-4">
                <!-- Barcode Display -->
                <div id="barcodeContainer" class="text-center">
                    <svg id="generatedBarcode"></svg>
                </div>

                <!-- Barcode Text Display -->
                <input type="text" id="generatedBarcodeInput" name="barcode" readonly
                    class="w-2/3 px-4 py-3 border border-gray-300 rounded text-center bg-gray-100 font-mono text-lg tracking-widest"
                >

                <!-- Action Buttons -->
                <div class="flex gap-4 mt-4">
                    <button id="generateNewBarcodeBtn"
                        class="bg-black text-white text-sm px-6 py-3 rounded-3xl hover:bg-gray-800 transition-all duration-200 transform hover:scale-105">
                        Generate New Barcode
                    </button>

                    <button id="useBarcodeBtn"
                        class="bg-[#B50612] text-white text-sm px-6 py-3 rounded-3xl hover:bg-red-700 transition-all duration-200 transform hover:scale-105">
                        Use This Barcode
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>






    <!-- Modal for barcode not exists -->
    <div id="barcodeNotFoundModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg w-[50%] min-h-[550px] shadow-lg relative flex flex-col items-center">

            <!-- Red Top Bar -->
            <div class="bg-[#B50612] w-full h-16 flex items-center justify-between px-6 rounded-t-lg">
                <h2 class="text-white text-lg font-medium">Barcode Not Found</h2>
                <button onclick="closeAllModals()" class="text-white hover:text-gray-200">
                    <span class="material-symbols-outlined text-white">close</span>
                </button>
            </div>

            <!-- Modal Content Center -->
            <div class="flex-1 w-full flex flex-col items-center justify-center px-6 py-8 mb-16">
                <div class="mb-10">
                    <img src="{{ asset('assets/warning-icon.png') }}" alt="Warning" class="h-20 mx-auto">
                </div>

                <p class="font-medium text-base text-black text-center mb-8">
                    Product barcode <span class="font-bold text-red-500">does not exists</span> in your inventory
                </p>

                <div class="flex justify-center gap-6">
                    <button 
                        onclick="closeAllModals()" 
                        class="w-32 bg-gray-300 text-gray-800 text-sm py-3 rounded-3xl hover:bg-gray-400 transition-all duration-200 transform hover:scale-105"
                    >
                        Exit
                    </button>
                    <button 
                        onclick="openRegisterModal()" 
                        class="w-32 bg-green-500 text-white text-sm py-3 rounded-3xl hover:bg-green-600 transition-all duration-200 transform hover:scale-105"
                    >
                        Register
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Register New Product Modal -->
<div id="registerProductModal" 
    class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">

    <div class="bg-white rounded-lg w-[50%] min-h-[550px] shadow-lg relative flex flex-col items-center">

        <!-- Red Top Bar -->
        <div class="bg-[#B50612] w-full h-16 flex items-center justify-between px-6 rounded-t-lg">
            <h2 class="text-white text-lg font-medium">REGISTER NEW PRODUCT</h2>
            <button onclick="closeRegisterModal()" class="text-white hover:text-gray-200">
                <span class="material-symbols-outlined text-white">close</span>
            </button>
        </div>

        <!-- Scrollable Modal Content -->
        <div class="flex-1 w-full flex flex-row px-6 py-6 mb-6 mt-2 overflow-y-auto space-x-6">

            <!-- Left Side (Form Fields) -->
            <form id="registerProductForm" class="w-1/2 space-y-4">

                <!-- Product Name -->
                <input type="text" name="name" placeholder="Product Name"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 placeholder:text-sm text-sm" required>

                <!-- Description -->
                <textarea name="description" placeholder="Description"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 placeholder:text-sm text-sm"></textarea>

                <!-- Category + Unit -->
                <div class="flex space-x-2">
                    <!-- Category -->
                    <div class="w-1/2">
                        <select id="categorySelect" name="category_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 text-sm mb-2"
                            required>
                            <option value="">Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->category_id }}">{{ $cat->category }}</option>
                            @endforeach
                            <option value="other">Other...</option>
                        </select>

                        <input type="text" id="customCategory" name="custom_category"
                            placeholder="Enter new category"
                            class="hidden w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 text-sm">
                    </div>

                    <!-- Unit -->
                    <div class="w-1/2">
                        <select id="unitSelect" name="unit_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 text-sm mb-2"
                            required>
                            <option value="">Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->unit_id }}">{{ $unit->unit }}</option>
                            @endforeach
                            <option value="other">Other...</option>
                        </select>

                        <input type="text" id="customUnit" name="custom_unit"
                            placeholder="Enter new unit"
                            class="hidden w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-green-600 text-sm">
                    </div>
                </div>

                <!-- Stock Limit -->
                <input type="number" name="stock_limit" placeholder="Stock Limit" min="0" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 placeholder:text-sm text-sm" required>

                <!-- Pricing -->
                <div class="border rounded-lg p-3 bg-gray-100">
                    <h3 class="font-semibold text-sm text-center mb-2">Pricing</h3>

                    <!-- Cost Price -->
                    <input type="number" step="0.01" min="0" name="cost_price" id="costPrice" placeholder="Cost Price" 
                        class="w-full px-3 py-2 mb-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 text-sm" required>

                    <!-- Markup Type -->
                    <div class="flex space-x-2 mb-2">
                        <select id="markupType" class="w-1/2 px-3 py-2 border border-gray-300 rounded text-sm">
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed</option>
                        </select>
                        <input type="number" id="markupValue" placeholder="Markup Value" oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                            class="w-1/2 px-3 py-2 border border-gray-300 rounded text-sm">
                    </div>

                    <!-- Selling Price -->
                    <input type="number" step="0.01" name="selling_price" id="sellingPrice" placeholder="Selling Price"
                        class="w-full px-3 py-2 border border-gray-300 rounded text-sm" readonly>
                </div>
            </form>

            <!-- Right Side (Photo & Barcode) -->
            <div class="flex flex-col items-center w-1/2 space-y-16">

                <!-- Upload Photo -->
                <div class="flex flex-col items-center space-y-2.5">
                    <label for="productPhoto" 
                        class="w-32 h-32 flex items-center justify-center border-2 border-dashed border-red-400 rounded-lg cursor-pointer hover:bg-gray-50 relative overflow-hidden">
                        <span class="material-symbols-outlined text-red-500 text-xl" id="uploadIcon">add_a_photo</span>
                        <img id="previewImage" class="absolute inset-0 w-full h-full object-cover hidden rounded-lg" />
                        <input type="file" id="productPhoto" name="photo" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden">
                    </label>
                    <span class="text-xs text-red-500" id="fileName">Upload Photo</span>
                </div>

                <!-- Barcode -->
                <div class="flex flex-col items-center space-y-1">
                    <img id="barcodeImage" src="{{ asset('assets/barcode.png') }}" 
                        alt="Barcode Preview" class="w-48 object-contain">
                    <div id="autoFilledBarcode" 
                        class="px-4 py-2 bg-gray-100 rounded font-mono text-base text-gray-800 tracking-widest">
                    </div>
                    <span class="text-xs text-gray-500">(auto-filled from typed barcode)</span>
                </div>

                <!-- Submit Button -->
                <button type="submit" form="registerProductForm"
                    class="inline-flex items-center justify-center px-8 py-3 text-sm font-medium rounded-lg shadow-md text-white bg-green-500 hover:green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 transform hover:scale-105">
                    Submit
                </button>
            </div>
        </div>
    </div>
</div>




    <!-- Restock Product Modal -->
<div id="restockProductModal" 
    class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">

    <div class="bg-white rounded-lg w-[50%] min-h-[550px] shadow-lg relative flex flex-col items-center">

        <!-- Red Top Bar -->
        <div class="bg-[#B50612] w-full h-16 flex items-center justify-between px-6 rounded-t-lg">
            <h2 class="text-white text-lg font-medium">RESTOCK PRODUCT</h2>
            <button onclick="closeRestockModal()" class="text-white hover:text-gray-200">
                <span class="material-symbols-outlined text-white">close</span>
            </button>
        </div>

        <!-- Scrollable Modal Content -->
        <div class="flex-1 w-full flex flex-row px-6 py-6 mb-6 mt-2 overflow-y-auto space-x-6">

            <!-- Left Side (Form Fields) -->
            <form id="restockForm" class="w-1/2 space-y-3">
                @csrf
                <!-- Hidden Inputs for Product Info -->
                <input type="hidden" name="prod_code" id="restockProdCode">
                <input type="hidden" name="category_id" id="restockCategoryId">

                <!-- Last Batch Number -->
                <label class="block text-sm font-semibold text-gray-800">Last Batch No.</label>
                <input type="text" id="last_batch_number" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-100 text-sm font-mono tracking-widest">

                <!-- Next Batch Number -->
                <label for="batch_number" class="block text-sm font-semibold text-gray-800">New Batch No.</label>
                <input type="text" name="batch_number" id="batch_number" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-100 text-sm font-mono tracking-widest">


                <!-- Quantity -->
                <label for="restockQuantityInput" class="block text-sm font-semibold text-gray-800">Quantity</label>
                <div class="flex items-center border border-gray-300 rounded px-2 py-1">
                    <button type="button" onclick="decreaseRestockQuantity()" class="px-2 text-base font-bold">−</button>
                    <input type="number" name="stock" id="restockQuantityInput" value="1" min="1"
                        class="w-full text-center outline-none border-0 text-sm">
                    <button type="button" onclick="increaseRestockQuantity()" class="px-2 text-base font-bold">+</button>
                </div>

                <!-- Date Added -->
                <label for="date_added" class="block text-sm font-semibold text-gray-800">Date Added</label>
                <input type="date" name="date_added" id="date_added"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 text-xs" required>

                <!-- Expiration Date -->
                <label for="expiration_date" class="block text-sm font-semibold text-gray-800">Expiration Date</label>
                <input type="date" name="expiration_date" id="expiration_date"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 text-xs">
            </form>

            <!-- Right Side (Preview & Submit) -->
            <div class="flex flex-col items-center w-1/2 space-y-10">

                <!-- Product Image Preview -->
                <div class="flex flex-col items-center space-y-2.5">
                    <img id="restockProdImage" 
                        src="{{ asset('assets/no-product-image.png') }}" 
                        class="w-32 h-32 object-cover rounded-lg" />
                </div>


                <!-- Barcode Display -->
                <div class="flex flex-col items-center space-y-1">
                    <img id="restockBarcodeImage" src="{{ asset('assets/barcode.png') }}" 
                        alt="Barcode Preview" class="w-48 object-contain">
                    <div id="restockBarcode" class="px-4 py-2 bg-gray-100 rounded font-mono text-base text-gray-800 tracking-widest"></div>
                    <span class="text-xs text-gray-500">(auto-filled from product barcode)</span>
                </div>

                <!-- Submit Button -->
                <button type="submit" form="restockForm"
                    class="inline-flex items-center justify-center px-8 py-3 text-sm font-medium rounded-lg shadow-md text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 transform hover:scale-105">
                    Restock
                </button>
            </div>
        </div>
    </div>
</div>










   <script>
        const filterToggle = document.getElementById('filterToggle');
        const categoryDropdown = document.getElementById('categoryDropdown');
        const searchInput = document.getElementById('search');
        const suggestionsBox = document.getElementById('suggestions');

        // Toggle dropdown on button click
        filterToggle.addEventListener('click', function (event) {
            categoryDropdown.classList.toggle('hidden');
            event.stopPropagation(); // Prevent immediate document click listener
        });

        // Prevent closing when clicking inside dropdown
        categoryDropdown.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        // Close dropdown when clicking anywhere else
        document.addEventListener('click', function () {
            categoryDropdown.classList.add('hidden');
        });

        // Filter by category (preserve status)
        function filterByCategory(categoryId) {
            const status = document.getElementById('statusInput')?.value || 'active';
            if (categoryId === 'all') {
                window.location.href = `{{ url('inventory-owner') }}?status=${status}`;
            } else {
                window.location.href = `{{ url('inventory-owner') }}?category=${categoryId}&status=${status}`;
            }
        }

        //Search functionality with autocomplete
        searchInput.addEventListener('input', function () {
            const term = searchInput.value.trim();
            if (term.length < 1) {
                suggestionsBox.classList.add('hidden');
                suggestionsBox.innerHTML = '';
                return;
            }

            fetch(`/inventory-owner/suggest?term=${encodeURIComponent(term)}`)
                .then(response => response.json())
                .then(data => {
                    suggestionsBox.innerHTML = '';

                    if (data.length === 0) {
                        suggestionsBox.classList.add('hidden');
                        return;
                    }

                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer';
                        div.textContent = item;

                        div.addEventListener('click', function () {
                            searchInput.value = item;
                            suggestionsBox.classList.add('hidden');
                            searchInput.form.submit(); // auto-submit
                        });

                        suggestionsBox.appendChild(div);
                    });

                    suggestionsBox.classList.remove('hidden');
                });
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function (event) {
            if (!searchInput.contains(event.target) && !suggestionsBox.contains(event.target)) {
                suggestionsBox.classList.add('hidden');
            }
        });
    </script>

    <script>
        const addProductBtn = document.getElementById('addProductBtn');
        const addProductModal = document.getElementById('addProductModal');
        const closeAddProductModal = document.getElementById('closeAddProductModal');

        addProductBtn.addEventListener('click', () => {
            addProductModal.classList.remove('hidden');
            addProductModal.classList.add('flex');
        });

        closeAddProductModal.addEventListener('click', () => {
            addProductModal.classList.add('hidden');
            addProductModal.classList.remove('flex');
        });

        // Example functions to handle modal navigation
        function openScanModal() {
            alert("Open Scan Barcode modal");
            // You can replace this with modal code
        }

        function openTypeModal() {
            alert("Open Type Barcode modal");
        }

        function openGenerateModal() {
            alert("Open Generate Barcode modal");
        }
    </script>


    <!-- Type Barcode Modal JavaScript -->
    <script>
        function openTypeModal() {
            const modal = document.getElementById('typeBarcodeModal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeTypeModal() {
            const modal = document.getElementById('typeBarcodeModal');
            if (modal) modal.classList.add('hidden');
        }

        function closeAllModals() {
            const modalIds = ['typeBarcodeModal', 'barcodeExistsModal', 'barcodeNotFoundModal', 'registerProductModal'];
            modalIds.forEach(id => {
                const modal = document.getElementById(id);
                if (modal) modal.classList.add('hidden');
            });
        }

        function reopenTypeModal() {
            closeAllModals();
            openTypeModal();
        }

        function openRegisterModal() {
            closeAllModals();
            const modal = document.getElementById('registerProductModal');
            if (modal) modal.classList.remove('hidden');
        }

        function checkBarcode() {
            const barcodeInput = document.getElementById('barcodeInput');
            const barcode = barcodeInput ? barcodeInput.value.trim() : '';

            if (!barcode) {
                alert("Please enter a barcode.");
                return;
            }

            fetch('/check-barcode', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ barcode })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                closeAllModals();

                if (data.exists === true && data.product) {
                    // ✅ Show the "Barcode Exists" modal
                    const existsModal = document.getElementById('barcodeExistsModal');
                    if (existsModal) existsModal.classList.remove('hidden');

                    // ✅ Attach the product info dynamically to the Restock button
                    const restockBtn = document.getElementById('barcodeExistsRestockBtn');
                    if (restockBtn) {
                        restockBtn.onclick = function () {
                            closeAllModals();
                            openRestockModal(
                                data.product.prod_code,
                                data.product.name,
                                data.product.prod_image,
                                data.product.category_id,
                                data.product.barcode
                            );
                        };
                    }
                } else if (data.exists === false) {
                    const notFoundModal = document.getElementById('barcodeNotFoundModal');
                    if (notFoundModal) notFoundModal.classList.remove('hidden');
                } else {
                    console.warn('Unexpected response structure:', data);
                }
            })
            .catch(error => {
                console.error('Error checking barcode:', error);
                alert('Something went wrong while checking the barcode.');
            });
        }


        document.addEventListener("DOMContentLoaded", () => {
            const form = document.getElementById("barcodeForm");
            const barcodeInput = document.getElementById("barcodeInput");

            // Allow only digits (0–9) while typing
            if (barcodeInput) {
                barcodeInput.addEventListener("input", function () {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }

            if (form) {
                form.addEventListener("submit", function (e) {
                    e.preventDefault(); // prevent page reload
                    checkBarcode();     // call your barcode check function
                });
            }
        });

    </script>

    <!-- Scan Barcode Modal JavaScript -->
    <script>
        function openScanModal() {
            document.getElementById('scanBarcodeModal').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('scannedBarcodeInput').focus();
            }, 300);
        }

        function closeScanModal() {
            document.getElementById('scanBarcodeModal').classList.add('hidden');
            document.getElementById('scannedBarcodeInput').value = '';
        }

        // Automatically detect scanner input and process it when Enter is pressed
        document.getElementById('scannedBarcodeInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                processScannedBarcode();
            }
        });

        function processScannedBarcode() {
            const barcode = document.getElementById('scannedBarcodeInput').value.trim();
            if (!barcode) return alert("Please scan a barcode first.");

            // Example: send scanned barcode to backend (AJAX or redirect)
            console.log("Scanned barcode:", barcode);

            // ✅ You can now either:
            // Option 1: Redirect to a route that searches for that barcode
            // window.location.href = `/products/search/${barcode}`;

            // Option 2: Send via AJAX to check product details
            // fetch(`/products/scan`, {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': '{{ csrf_token() }}'
            //     },
            //     body: JSON.stringify({ barcode })
            // }).then(res => res.json())
            // .then(data => {
            //     console.log(data);
            //     // Handle product data display here
            // });

            closeScanModal();
        }
    </script>

    <!-- Generate Barcode Modal JavaScript -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const barcodeCategory = document.getElementById("barcodeCategory");
        const customPrefixInput = document.getElementById("customPrefixInput");
        const categoryError = document.getElementById("categoryError");
        const generateBtn = document.getElementById("generateBarcodeBtn");
        const generatedSection = document.getElementById("generatedSection");
        const barcodeContainer = document.getElementById("generatedBarcode");
        const barcodeInput = document.getElementById("generatedBarcodeInput");
        const generateNewBtn = document.getElementById("generateNewBarcodeBtn");
        const useBarcodeBtn = document.getElementById("useBarcodeBtn");

        // Show custom prefix input when "Other..." is selected
        barcodeCategory.addEventListener("change", () => {
            categoryError.classList.add("hidden"); // clear error on change
            if (barcodeCategory.value === "other") {
                customPrefixInput.classList.remove("hidden");
                customPrefixInput.focus();
            } else {
                customPrefixInput.classList.add("hidden");
                customPrefixInput.value = "";
            }
        });

        // Generate random barcode (numeric portion)
        function generateRandomBarcode(prefix = "XX") {
            const randomNum = Math.floor(100000 + Math.random() * 900000); // 6 digits
            return `${prefix}${randomNum}`;
        }

        // Render barcode using JsBarcode
        function renderBarcode(code) {
            JsBarcode(barcodeContainer, code, {
                format: "CODE128",
                lineColor: "#000",
                width: 2,
                height: 80,
                displayValue: true,
                fontSize: 16,
            });
            barcodeInput.value = code;
            generatedSection.classList.remove("hidden");
        }

        //  Handle initial barcode generation
        generateBtn.addEventListener("click", () => {
            categoryError.classList.add("hidden"); // hide any previous error

            let selectedCategory = barcodeCategory.value;

            // Prevent proceeding if no category selected
            if (!selectedCategory) {
                categoryError.textContent = "Please select a product category.";
                categoryError.classList.remove("hidden");
                return;
            }

            let prefix = selectedCategory;

            // Handle custom prefix (Other option)
            if (prefix === "other") {
                prefix = customPrefixInput.value.trim().toUpperCase();

                if (!prefix) {
                    categoryError.textContent = "Please enter a custom prefix (2–5 letters).";
                    categoryError.classList.remove("hidden");
                    return;
                }

                // Validate prefix length and letters only
                if (!/^[A-Za-z]{2,5}$/.test(prefix)) {
                    categoryError.textContent = "Custom prefix must contain 2 to 5 letters only.";
                    categoryError.classList.remove("hidden");
                    return;
                }
            }

            const newCode = generateRandomBarcode(prefix);
            renderBarcode(newCode);
        });

        // Generate new barcode (same prefix)
        generateNewBtn.addEventListener("click", () => {
            let prefix = barcodeCategory.value || "OT";
            if (prefix === "other") prefix = customPrefixInput.value.trim().toUpperCase() || "OT";

            const newCode = generateRandomBarcode(prefix);
            renderBarcode(newCode);
        });

        // Placeholder action for "Use This Barcode"
        useBarcodeBtn.addEventListener("click", () => {
            alert("Barcode selected: " + barcodeInput.value);
            // Here you can proceed to another modal or save logic (e.g., AJAX save)
        });

        // Modal open/close handlers
        window.openGenerateModal = function() {
            document.getElementById('generateBarcodeModal').classList.remove('hidden');
            generatedSection.classList.add('hidden');
            barcodeCategory.value = "";
            customPrefixInput.classList.add('hidden');
            customPrefixInput.value = "";
            categoryError.classList.add("hidden");
        };

        window.closeGenerateModal = function() {
            document.getElementById('generateBarcodeModal').classList.add('hidden');
        };
    });
</script>








   <!-- Register New Product Modal JavaScript -->
    <script>
        function openRegisterModal(barcode) {
            closeAllModals();
            const modal = document.getElementById('registerProductModal');
            if (modal) modal.classList.remove('hidden');

            // Auto-fill barcode in the register modal
            const barcodeElement = document.getElementById('autoFilledBarcode');
            if (barcodeElement) barcodeElement.textContent = barcode || '';
        }

        // Update the event handler to pass the barcode from the modal
        document.querySelector('button[onclick="openRegisterModal()"]').onclick = function() {
            const barcode = document.getElementById('barcodeInput').value; // get barcode from input
            openRegisterModal(barcode); // pass it to the modal
        };
        
        function closeRegisterModal() {
            const modal = document.getElementById('registerProductModal');
            const form = document.getElementById('registerProductForm');
            if (modal) modal.classList.add('hidden');
            if (form) form.reset(); // ✅ clear form when closing
            resetPhotoPreview();
        }


        // Auto-calc Selling Price
        function calculateSellingPrice() {
            const cost = parseFloat(document.getElementById("costPrice").value) || 0;
            const type = document.getElementById("markupType").value;
            const markup = parseFloat(document.getElementById("markupValue").value) || 0;
            let selling = cost;

            if (type === "percentage") {
                selling = cost + (cost * (markup / 100));
            } else {
                selling = cost + markup;
            }

            document.getElementById("sellingPrice").value = selling.toFixed(2);
        }

        ["costPrice", "markupType", "markupValue"].forEach(id => {
            document.getElementById(id).addEventListener("input", calculateSellingPrice);
            document.getElementById(id).addEventListener("change", calculateSellingPrice);
        });

        // Reset photo preview helper
        function resetPhotoPreview() {
            const photoLabel = document.querySelector("label[for='productPhoto']");
            const uploadIcon = document.getElementById("uploadIcon");
            if (photoLabel && uploadIcon) {
                photoLabel.style.backgroundImage = "none";
                uploadIcon.style.display = "block";
            }
        }

        // Form submission with barcode handling
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.getElementById("registerProductForm");
            const photoInput = document.getElementById("productPhoto");
            const photoLabel = document.querySelector("label[for='productPhoto']");
            const uploadIcon = document.getElementById("uploadIcon");
            const previewImage = document.getElementById("previewImage");
            const fileName = document.getElementById("fileName");

            // ✅ Photo preview
            photoInput.addEventListener("change", function () {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        // Show image preview
                        previewImage.src = e.target.result;
                        previewImage.classList.remove("hidden"); // Make the preview visible
                        
                        // Change the label's background to the selected image
                        photoLabel.style.backgroundImage = `url(${e.target.result})`;
                        photoLabel.style.backgroundSize = "cover";
                        photoLabel.style.backgroundPosition = "center";
                        uploadIcon.style.display = "none"; // Hide the upload icon
                        
                        // Show the selected file name
                        fileName.textContent = this.files[0].name;
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });

            form.addEventListener("submit", function (e) {
                e.preventDefault(); // Prevent page reload

                const formData = new FormData(form);
                formData.append("barcode", document.getElementById("autoFilledBarcode").textContent); // Ensure barcode is included

                if (photoInput.files[0]) {
                    formData.append("photo", photoInput.files[0]);
                }

                fetch("/register-product", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}" // Ensure CSRF token is passed
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Product registered successfully!");
                        closeRegisterModal();
                        location.reload();
                    } else {
                        alert("Failed: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Error registering product:", error);
                    alert("Something went wrong.");
                });
            });
        });
    </script>

<!-- JS for Custom Category/Unit Toggle -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const categorySelect = document.getElementById("categorySelect");
    const customCategory = document.getElementById("customCategory");
    const unitSelect = document.getElementById("unitSelect");
    const customUnit = document.getElementById("customUnit");

    categorySelect.addEventListener("change", () => {
        if (categorySelect.value === "other") {
            customCategory.classList.remove("hidden");
            customCategory.required = true;
        } else {
            customCategory.classList.add("hidden");
            customCategory.required = false;
        }
    });

    unitSelect.addEventListener("change", () => {
        if (unitSelect.value === "other") {
            customUnit.classList.remove("hidden");
            customUnit.required = true;
        } else {
            customUnit.classList.add("hidden");
            customUnit.required = false;
        }
    });
});
</script>

<script>
    function openRestockModal(prodCode, prodName, prodImage, categoryId, barcode) {
        // Fill hidden inputs
        document.getElementById('restockProdCode').value = prodCode;
        document.getElementById('restockCategoryId').value = categoryId;

        // Product Image Preview
        const restockImg = document.getElementById('restockProdImage');
        if (prodImage) {
            restockImg.src = '/storage/' + prodImage;
            // 👇 If the file is missing, fallback automatically
            restockImg.onerror = () => restockImg.src = '/assets/no-product-image.png';
        } else {
            restockImg.src = '/assets/no-product-image.png';
        }



        // Auto-filled barcode number
        document.getElementById('restockBarcode').textContent = barcode || prodCode || '';

        // Reset quantity
        document.getElementById('restockQuantityInput').value = 1;

        // Fetch latest batch number from server
        fetch(`/inventory/latest-batch/${prodCode}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('last_batch_number').value = data.last_batch_number || 'None';
                document.getElementById('batch_number').value = data.next_batch_number || 'BATCH-1';
            })
            .catch(err => {
                console.error('Error fetching latest batch:', err);
                document.getElementById('last_batch_number').value = 'None';
                document.getElementById('batch_number').value = 'BATCH-1';
        });


        // Show modal
        document.getElementById('restockProductModal').classList.remove('hidden');
    }

    function closeRestockModal() {
        document.getElementById('restockProductModal').classList.add('hidden');
    }

    function increaseRestockQuantity() {
        const input = document.getElementById('restockQuantityInput');
        if (input) input.value = parseInt(input.value || 0) + 1;
    }
    function decreaseRestockQuantity() {
        const input = document.getElementById('restockQuantityInput');
        if (input && parseInt(input.value) > 1) input.value = parseInt(input.value) - 1;
    }

    // Handle form submission
    document.getElementById('restockForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('/inventory/restock', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Stock added successfully!');
                closeRestockModal();
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Something went wrong.'));
            }
        })
        .catch(err => console.error(err));
    });
</script>

<!-- Archive, Unarchive JavaScript -->
<script>
    function openStatusModal(action, prodCode, name, barcode, imageUrl) {
        const modal = document.getElementById('statusModal');
        const form = document.getElementById('statusForm');
        const title = document.getElementById('statusModalTitle');
        const message = document.getElementById('statusModalMessage');
        const submitBtn = document.getElementById('statusSubmitBtn');

        // Product details
        document.getElementById('statusProductName').textContent = name;
        document.getElementById('statusProductName').title = name; // tooltip

        document.getElementById('statusProductBarcode').textContent = barcode;
        document.getElementById('statusProductBarcode').title = barcode; // tooltip

        document.getElementById('statusProductImage').src = imageUrl 
            ? `/storage/${imageUrl}` 
            : "{{ asset('assets/no-product-image.png') }}";

        // Action-specific UI
        if (action === 'archive') {
            title.textContent = 'Archive Product';
            message.textContent = 'Are you sure you want to archive this product?';
            form.action = `/inventory/archive/${prodCode}`;
            submitBtn.textContent = 'Yes, Archive';
            submitBtn.className = "px-4 py-2 bg-red-500 text-white text-sm rounded hover:bg-red-600";
        } else {
            title.textContent = 'Unarchive Product';
            message.textContent = 'Do you want to unarchive this product?';
            form.action = `/inventory/unarchive/${prodCode}`;
            submitBtn.textContent = 'Yes, Unarchive';
            submitBtn.className = "px-4 py-2 bg-green-500 text-white text-sm rounded hover:bg-green-600";
        }

        modal.classList.remove('hidden');
    }

    function closeStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
    }
</script>


<!-- Toggle Option for Active and Archived Products JavaScript -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById("statusToggleForm");
        const statusInput = document.getElementById("statusInput");
        const activeBtn = document.getElementById("activeBtn");
        const archivedBtn = document.getElementById("archivedBtn");

        activeBtn.addEventListener("click", () => {
            statusInput.value = "active";
            form.submit();
        });

        archivedBtn.addEventListener("click", () => {
            statusInput.value = "archived";
            form.submit();
        });
    });
</script>






@endsection
