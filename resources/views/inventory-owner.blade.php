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
                    Register Product
                </button>
                <button id="addStockBtn" {{ $expired ? 'disabled' : '' }}
                        class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition-all duration-200 transform hover:scale-105
                        {{ $expired ? 'cursor-not-allowed' : '' }}">
                    Add Stock
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



<!-- Choose Category Modal -->
<div id="chooseCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-2xl p-8 w-[90%] max-w-4xl shadow-xl relative">
    <!-- Close Button -->
    <button id="closeChooseCategoryModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition">
      <span class="material-symbols-outlined">close</span>
    </button>

    <!-- Header -->
    <h2 class="text-xl font-bold text-center text-[#B50612] mb-6">Choose Category</h2>

    <!-- Categories Grid -->
    <div class="grid grid-cols-4 gap-6 max-h-[420px] overflow-y-auto pr-2">
      <!-- New Category -->
      <div onclick="openAddCategoryModal()"
           class="cursor-pointer rounded-xl p-6 flex flex-col justify-center items-center bg-[#FFF5F5] border border-[#F5B5B5] hover:shadow-md hover:-translate-y-1 transition-transform duration-200 h-36">
        <span class="material-symbols-outlined text-4xl text-[#B50612] mb-2">add_circle</span>
        <p class="font-semibold text-gray-700">New Category</p>
      </div>

      <!-- Category Items -->
      @foreach($categories as $category)
        <div onclick="onCategorySelected('{{ $category->category_id }}', '{{ e($category->category) }}')"
             class="cursor-pointer rounded-xl p-6 flex flex-col justify-center items-center bg-white border border-gray-200 hover:border-[#B50612] hover:bg-[#FFF7F7] hover:shadow-md hover:-translate-y-1 transition-transform duration-200 h-36">
          <p class="font-semibold text-gray-700 text-center">{{ $category->category }}</p>
        </div>
      @endforeach
    </div>
  </div>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-2xl p-8 w-[90%] max-w-md shadow-xl relative">
    <!-- Close Button -->
    <button id="closeAddCategoryModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition">
      <span class="material-symbols-outlined">close</span>
    </button>

    <!-- Header -->
    <h2 class="text-xl font-bold text-center text-[#B50612] mb-6">Add New Category</h2>

    <!-- Form -->
    <form id="addCategoryForm" method="POST">
      @csrf
      <div class="mb-4">
        <label for="newCategoryName" class="block text-sm font-medium text-gray-700 mb-1">
          Category Name
        </label>
        <input type="text" id="newCategoryName" name="category" required placeholder="Enter category name"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring- focus:ring-[#B50612] focus:border-[#B50612] placeholder-gray-400 text-sm">
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <button type="button" id="cancelAddCategory"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
          Cancel
        </button>
        <button type="submit"
                class="px-4 py-2 bg-[#B50612] text-white font-medium rounded-lg hover:bg-[#9E0410] transition">
          Save Category
        </button>
      </div>
    </form>
  </div>
</div>



<!-- Choose Products Modal -->
<div id="chooseProductsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-2xl p-6 w-[95%] max-w-4xl shadow-xl relative">
    <button id="closeChooseProductsModal" class="absolute top-4 right-4 text-gray-500">
      <span class="material-symbols-outlined">close</span>
    </button>

    <!-- Title -->
    <h2 class="text-xl font-semibold text-center text-[#B50612] mb-1">Choose Products to Restock</h2>
    <!-- Category Label -->
    <p id="selectedCategoryLabel" class="text-center text-sm text-gray-500 mb-4"></p>

    <div class="mb-4 flex justify-between items-center">
        <div class="text-sm text-gray-700" id="chooseProductsInfo"></div>
        <div class="flex gap-2">
            <button onclick="selectAllProducts()" class="px-4 py-1.5 text-sm font-medium rounded-md bg-yellow-500 text-white hover:bg-yellow-600 transition-colors duration-200">Select All</button>
            <button onclick="deselectAllProducts()" class="px-4 py-1.5 text-sm font-medium rounded-md bg-gray-200 text-gray-800 hover:bg-gray-300 transition-colors duration-200">Clear</button>
        </div>
    </div>


    <div class="max-h-[360px] overflow-y-auto border rounded p-2">
      <table class="w-full text-sm">
        <thead class="bg-gray-100 sticky top-0">
          <tr>
            <th class="p-2 text-left">Select</th>
            <th class="p-2 text-left">Product</th>
            <th class="p-2 text-center">Current Stock</th>
          </tr>
        </thead>
        <tbody id="categoryProductsList"></tbody>
      </table>
    </div>

    <div class="mt-4 flex justify-end">
      <button id="proceedToRestockBtn" onclick="proceedToRestock()" class="bg-[#B50612] text-white px-4 py-2 rounded transition-all duration-200 transform hover:scale-105">
        Next
      </button>
    </div>
  </div>
</div>


<!-- Restock Details Modal -->
<div id="restockDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-2xl p-6 w-[95%] max-w-4xl shadow-xl relative">
    <button id="closeRestockDetailsModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
      <span class="material-symbols-outlined">close</span>
    </button>
    <h2 class="text-xl font-semibold text-center text-[#B50612] mb-4">Restock Details</h2>

    <form id="bulkRestockForm" method="POST">
      @csrf
      <input type="hidden" name="category_id" id="restockCategoryId">

      <div class="max-h-[360px] overflow-y-auto border rounded p-2">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 sticky top-0">
            <tr>
              <th class="p-2 text-left">Product</th>
              <th class="p-2 text-center">Current Stock</th>
              <th class="p-2 text-center">Add Qty</th>
              <th class="p-2 text-center">Expiration Date</th>
              <th class="p-2 text-center">
                <span class="font-semibold">Batch No.</span> 
                <span class="font-normal text-gray-500 text-xs">(auto-filled)</span>
              </th>
              <th class="p-2 text-center">Actions</th>
            </tr>
          </thead>
          <tbody id="restockRowsContainer"></tbody>
        </table>
      </div>

      <div class="mt-4 flex justify-end gap-2">
        <button type="button" onclick="closeRestockDetails()" 
          class="px-4 py-2 border rounded bg-gray-300 hover:bg-gray-400 transition">
          Cancel
        </button>
        <button type="submit" 
          class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-all duration-200 transform hover:scale-105">
          Save Restock
        </button>
      </div>
    </form>
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
                    inputmode="text"
                    title="Only letters and numbers are allowed"
                    maxlength="25"
                    pattern="[A-Za-z0-9]+"
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

<!-- Barcode Already Exists Modal -->
<div id="barcodeAlreadyExistsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="bg-white rounded-lg w-[50%] min-h-[550px] shadow-lg relative flex flex-col items-center">

        <!-- Red Top Bar -->
        <div class="bg-[#B50612] w-full h-16 flex items-center justify-between px-6 rounded-t-lg">
            <h2 class="text-white text-lg font-medium">Barcode Already Exists</h2>
            <button onclick="closeBarcodeExistsModal()" class="text-white hover:text-gray-200">
                <span class="material-symbols-outlined text-white">close</span>
            </button>
        </div>

        <!-- Modal Content Center -->
        <div class="flex-1 w-full flex flex-col items-center justify-center px-6 py-8 mb-16">
            <div class="mb-10">
                <img src="{{ asset('assets/warning-icon.png') }}" alt="Warning" class="h-20 mx-auto">
            </div>

            <p class="font-medium text-base text-black text-center mb-8">
                Product barcode <span class="font-bold text-red-500">already exists</span> in your inventory
            </p>

            <div class="flex justify-center gap-6">
                <button 
                    onclick="closeBarcodeExistsModal()" 
                    class="w-32 bg-gray-300 text-gray-800 text-sm py-3 rounded-3xl hover:bg-gray-400 transition-all duration-200 transform hover:scale-105"
                >
                    Go Back
                </button>
                <button 
                    onclick="goToInventory()" 
                    class="w-32 bg-[#B50612] text-white text-sm py-3 rounded-3xl hover:bg-red-700 transition-all duration-200 transform hover:scale-105"
                >
                    Exit
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
                <input type="number" name="stock_limit" placeholder="Minimum Stock Limit" min="0" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');"
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
                            <option value="percentage">Percentage %</option>
                            <option value="fixed">Fixed ₱</option>
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

    <script>
    document.addEventListener('DOMContentLoaded', function () {
    const addStockBtn = document.getElementById('addStockBtn');
    const chooseCategoryModal = document.getElementById('chooseCategoryModal');
    const chooseProductsModal = document.getElementById('chooseProductsModal');
    const restockDetailsModal = document.getElementById('restockDetailsModal');
    const bulkRestockForm = document.getElementById('bulkRestockForm');

    // === Open Choose Category ===
    addStockBtn?.addEventListener('click', () => {
        chooseCategoryModal.classList.remove('hidden');
        chooseCategoryModal.classList.add('flex');
    });
    document.getElementById('closeChooseCategoryModal')?.addEventListener('click', () => {
        chooseCategoryModal.classList.add('hidden');
    });

    // === Open Add Category Modal ===
    window.openAddCategoryModal = function() {
        document.getElementById('chooseCategoryModal').classList.add('hidden');
        const modal = document.getElementById('addCategoryModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('newCategoryName').focus();
    };

    // === Close Add Category Modal ===
    function closeAddCategoryModal() {
        const modal = document.getElementById('addCategoryModal');
        modal.classList.add('hidden');
    }

    // === Reopen Choose Category Modal ===
    function reopenChooseCategoryModal() {
        const chooseModal = document.getElementById('chooseCategoryModal');
        chooseModal.classList.remove('hidden');
        chooseModal.classList.add('flex');
    }

    // === Close Buttons ===
    document.getElementById('closeAddCategoryModal')?.addEventListener('click', () => {
        closeAddCategoryModal();
        reopenChooseCategoryModal();
    });

    document.getElementById('cancelAddCategory')?.addEventListener('click', () => {
        closeAddCategoryModal();
        reopenChooseCategoryModal();
    });

    // === Handle Add Category Form Submission ===
    document.getElementById('addCategoryForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const categoryName = document.getElementById('newCategoryName').value.trim();
        if (!categoryName) {
        alert('Please enter a category name.');
        return;
        }

        const formData = new FormData(this);

        try {
        const response = await fetch('/inventory/add-category', {
            method: 'POST',
            headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Set flag to reopen modal after reload
            sessionStorage.setItem('openChooseCategoryModal', 'true');

            alert('Category added successfully!');
            window.location.reload(); // reload page to get new category with proper ID
        } else {
            alert('⚠️ ' + data.message);
        }
        } catch (err) {
        console.error(err);
        alert('Something went wrong while adding category.');
        }
    });

    // === Auto-open Choose Category Modal after reload if needed ===
    window.addEventListener('DOMContentLoaded', () => {
        if (sessionStorage.getItem('openChooseCategoryModal') === 'true') {
        sessionStorage.removeItem('openChooseCategoryModal'); // clear flag
        const chooseCategoryModal = document.getElementById('chooseCategoryModal');
        chooseCategoryModal.classList.remove('hidden');
        chooseCategoryModal.classList.add('flex');
        }
    });


    // === Category Click ===
    window.onCategorySelected = function(categoryId, categoryName) {
        chooseCategoryModal.classList.add('hidden');
        fetchCategoryProducts(categoryId, categoryName);
    };

    // === AJAX: Fetch Category Products ===
    function fetchCategoryProducts(categoryId, categoryName) {
        document.getElementById('selectedCategoryLabel').textContent = `Category: ${categoryName}`;
        const list = document.getElementById('categoryProductsList');
        list.innerHTML = '<tr><td colspan="3" class="p-3 text-center">Loading…</td></tr>';

        fetch(`/inventory/category-products/${categoryId}`)
            .then(r => r.json())
            .then(data => {
            list.innerHTML = '';
            if (!data || data.length === 0) {
                list.innerHTML = '<tr><td colspan="3" class="p-3 text-center">No products found.</td></tr>';
            } else {
                data.forEach(p => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="p-2"><input type="checkbox" class="selectProductCheckbox" data-prod="${p.prod_code}" data-cat="${p.category_id}" data-name="${escapeHtml(p.name)}" data-stock="${p.stock}"></td>
                    <td class="p-2">${escapeHtml(p.name)}</td>
                    <td class="p-2 text-center">${p.stock}</td>
                `;
                list.appendChild(row);
                });
            }
            chooseProductsModal.classList.remove('hidden');
            chooseProductsModal.classList.add('flex');
            document.getElementById('restockCategoryId').value = categoryId;
            })
            .catch(err => {
            console.error(err);
            list.innerHTML = '<tr><td colspan="3" class="p-3 text-center">Error loading products.</td></tr>';
            });
    }


    document.getElementById('closeChooseProductsModal')?.addEventListener('click', () => {
        chooseProductsModal.classList.add('hidden');
    });

    window.selectAllProducts = function() {
        document.querySelectorAll('.selectProductCheckbox').forEach(cb => cb.checked = true);
    };
    window.deselectAllProducts = function() {
        document.querySelectorAll('.selectProductCheckbox').forEach(cb => cb.checked = false);
    };

    // === Proceed to Restock Details ===
    window.proceedToRestock = function() {
    const checked = Array.from(document.querySelectorAll('.selectProductCheckbox:checked'));
    if (checked.length === 0) {
        alert('Please select at least one product to restock.');
        return;
    }

    const container = document.getElementById('restockRowsContainer');
    container.innerHTML = '';
    const categoryId = document.getElementById('restockCategoryId').value;
    let index = 0;

    const promises = checked.map(cb => {
        const prodCode = cb.dataset.prod;
        const prodName = cb.dataset.name;
        const currentStock = cb.dataset.stock ?? 0;

        return fetch(`/inventory/get-latest-batch/${prodCode}`)
        .then(r => r.json())
        .then(batchResp => {
            const nextBatch = batchResp.next_batch || 'BATCH-1';
            addRestockRow(prodCode, prodName, categoryId, currentStock, nextBatch, index++);
        });
    });

    Promise.all(promises).then(() => {
        chooseProductsModal.classList.add('hidden');
        restockDetailsModal.classList.remove('hidden');
        restockDetailsModal.classList.add('flex');
    }).catch(err => {
        console.error(err);
        alert('Failed to prepare restock details.');
    });
    };

    // === Add Restock Row Function ===
    window.addRestockRow = function(prodCode, prodName, categoryId, currentStock, batchNum, index) {
    const container = document.getElementById('restockRowsContainer');
    const tr = document.createElement('tr');
    tr.classList.add('border-b');

    tr.innerHTML = `
        <td class="p-2">
        ${escapeHtml(prodName)}
        <input type="hidden" name="items[${index}][prod_code]" value="${prodCode}">
        <input type="hidden" name="items[${index}][category_id]" value="${categoryId}">
        </td>
        <td class="p-2 text-center">${currentStock}</td>
        <td class="p-2 text-center">
        <input type="number" min="1" required name="items[${index}][qty]" 
            class="border rounded px-2 py-1 w-20 text-sm text-center">
        </td>
        <td class="p-2 text-center">
        <input type="date" name="items[${index}][expiration_date]" 
            class="border rounded px-2 py-1 w-36 text-sm text-center">
        </td>
        <td class="p-2 text-center">
        <input type="text" readonly name="items[${index}][batch_number]" 
            value="${batchNum}" 
            class="border rounded px-2 py-1 text-sm text-center bg-gray-50">
        </td>
        <td class="p-2">
        <div class="flex justify-center gap-2">
            <button 
            type="button" 
            class="flex-1 bg-blue-600 text-white text-xs font-medium px-3 py-1 rounded hover:bg-blue-700 transition"
            onclick="duplicateBatchRow(this, '${prodCode}', '${escapeHtml(prodName)}', '${categoryId}', '${currentStock}')">
            Add Batch
            </button>
            <button 
            type="button" 
            class="flex-1 bg-red-600 text-white text-xs font-medium px-3 py-1 rounded hover:bg-red-700 transition"
            onclick="this.closest('tr').remove()">
            Remove
            </button>
        </div>
        </td>
    `;
    container.appendChild(tr);
    };

    // === Duplicate Row for the Same Product (increments batch) ===
    window.duplicateBatchRow = function(button, prodCode, prodName, categoryId, currentStock) {
    const container = document.getElementById('restockRowsContainer');
    const rows = container.querySelectorAll('tr');
    let highestBatch = 0;
    let lastSameProductRow = null;

    rows.forEach(row => {
        const batchInput = row.querySelector(`input[name*="[batch_number]"]`);
        const prodInput = row.querySelector(`input[name*="[prod_code]"]`);
        if (prodInput && prodInput.value === prodCode && batchInput) {
            lastSameProductRow = row; // keep updating to get the last occurrence
            const match = batchInput.value.match(/BATCH-(\d+)/);
            if (match) {
                const num = parseInt(match[1]);
                if (num > highestBatch) highestBatch = num;
            }
        }
    });

    const nextBatch = 'BATCH-' + (highestBatch + 1);
    const newIndex = container.querySelectorAll('tr').length;

    // Create the new row
    const tr = document.createElement('tr');
    tr.classList.add('border-b');
    tr.innerHTML = `
        <td class="p-2">
            ${escapeHtml(prodName)}
            <input type="hidden" name="items[${newIndex}][prod_code]" value="${prodCode}">
            <input type="hidden" name="items[${newIndex}][category_id]" value="${categoryId}">
        </td>
        <td class="p-2 text-center">${currentStock}</td>
        <td class="p-2 text-center">
            <input type="number" min="1" required name="items[${newIndex}][qty]" 
                class="border rounded px-2 py-1 w-20 text-sm text-center">
        </td>
        <td class="p-2 text-center">
            <input type="date" name="items[${newIndex}][expiration_date]" 
                class="border rounded px-2 py-1 w-36 text-sm text-center">
        </td>
        <td class="p-2 text-center">
            <input type="text" readonly name="items[${newIndex}][batch_number]" 
                value="${nextBatch}" 
                class="border rounded px-2 py-1 text-sm text-center bg-gray-50">
        </td>
        <td class="p-2">
            <div class="flex justify-center gap-2">
                <button 
                    type="button" 
                    class="flex-1 bg-blue-600 text-white text-xs font-medium px-3 py-1 rounded hover:bg-blue-700 transition"
                    onclick="duplicateBatchRow(this, '${prodCode}', '${escapeHtml(prodName)}', '${categoryId}', '${currentStock}')">
                    Add Batch
                </button>
                <button 
                    type="button" 
                    class="flex-1 bg-red-600 text-white text-xs font-medium px-3 py-1 rounded hover:bg-red-700 transition"
                    onclick="this.closest('tr').remove()">
                    Remove
                </button>
            </div>
        </td>
    `;

    // Insert after the last row with the same product, or append if none found
    if (lastSameProductRow) {
        if (lastSameProductRow.nextSibling) {
            container.insertBefore(tr, lastSameProductRow.nextSibling);
        } else {
            container.appendChild(tr);
        }
    } else {
        container.appendChild(tr);
    }
};



    // === Close Restock Modal ===
    window.closeRestockDetails = function() {
    const modal = document.getElementById('restockDetailsModal');
    if (modal) modal.classList.add('hidden');
    };

    document.getElementById('closeRestockDetailsModal')?.addEventListener('click', window.closeRestockDetails);

    // === Helper: Escape HTML ===
    function escapeHtml(text) {
    if (!text) return '';
    return ('' + text)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
    }

    // === Bulk Restock Submit Handling ===
    if (bulkRestockForm) {
    bulkRestockForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(bulkRestockForm);

        fetch('/inventory/bulk-restock', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: formData
        })
        .then(res => res.json())
        .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = '/inventory-owner';
        } else {
            alert('⚠️ ' + data.message);
        }
        })
        .catch(err => {
        console.error(err);
        alert('Something went wrong. Please try again.');
        });
    });
    }

    });
</script>


  


<!-- Type Barcode Modal JavaScript -->
<script>
    function openTypeModal() {
        const modal = document.getElementById('typeBarcodeModal');
        if (modal) {
            modal.classList.remove('hidden');
            // Clear input and focus when opening modal
            const barcodeInput = document.getElementById('barcodeInput');
            if (barcodeInput) {
                barcodeInput.value = '';
                barcodeInput.focus();
            }
        }
    }

    function closeTypeModal() {
        const modal = document.getElementById('typeBarcodeModal');
        if (modal) modal.classList.add('hidden');
    }

    function openBarcodeExistsModal() {
        closeAllModals();
        const modal = document.getElementById('barcodeAlreadyExistsModal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeBarcodeExistsModal() {
        const modal = document.getElementById('barcodeAlreadyExistsModal');
        if (modal) modal.classList.add('hidden');
        // Reopen the type barcode modal so user can try again
        openTypeModal();
    }

    function goToInventory() {
        // Redirect to inventory page
        window.location.href = "{{ route('inventory-owner') }}";
    }

    function closeAllModals() {
        const modalIds = ['typeBarcodeModal', 'barcodeExistsModal', 'barcodeNotFoundModal', 'registerProductModal', 'barcodeAlreadyExistsModal'];
        modalIds.forEach(id => {
            const modal = document.getElementById(id);
            if (modal) modal.classList.add('hidden');
        });
    }

    function reopenTypeModal() {
        closeAllModals();
        openTypeModal();
    }

    // Your existing function - keep this exactly as is
    function openRegisterModal(barcode) {
        closeAllModals();
        const modal = document.getElementById('registerProductModal');
        if (modal) modal.classList.remove('hidden');

        // Auto-fill barcode in the register modal
        const barcodeElement = document.getElementById('autoFilledBarcode');
        if (barcodeElement) barcodeElement.textContent = barcode || '';
    }

    // Function to detect if we're in registration context - IMPROVED
    function isRegistrationContext() {
        // Check URL for registration-related paths
        const currentPath = window.location.pathname;
        const isRegistrationPath = currentPath.includes('register') || 
                                 currentPath.includes('add-product') || 
                                 currentPath.includes('create');
        
        // Check if we have a register product modal that's meant to be used
        const registerModal = document.getElementById('registerProductModal');
        const hasRegisterModal = registerModal !== null;
        
        // If we're on the main inventory page but have a register modal, 
        // we're likely in registration context
        return isRegistrationPath || hasRegisterModal;
    }

    function checkBarcode() {
        const barcodeInput = document.getElementById('barcodeInput');
        const barcode = barcodeInput ? barcodeInput.value.trim() : '';

        if (!barcode) {
            alert("Please enter a barcode.");
            return;
        }

        console.log('Checking barcode:', barcode);
        console.log('Registration context:', isRegistrationContext());

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
            console.log('Barcode check response:', data);
            closeAllModals();

            if (data.exists === true && data.product) {
                // Barcode exists - show appropriate modal based on context
                if (isRegistrationContext()) {
                    console.log('Showing barcode exists modal for registration');
                    openBarcodeExistsModal();
                } else {
                    console.log('Showing barcode exists modal for restocking');
                    const existsModal = document.getElementById('barcodeExistsModal');
                    if (existsModal) existsModal.classList.remove('hidden');

                    // Attach the product info dynamically to the Restock button
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
                }
            } else if (data.exists === false) {
                // Barcode doesn't exist
                console.log('Barcode not found in database');
                
                if (isRegistrationContext()) {
                    // If in registration context, proceed with registration
                    console.log('Opening register modal with barcode:', barcode);
                    openRegisterModal(barcode);
                } else {
                    // If in inventory context, show not found modal
                    console.log('Showing barcode not found modal');
                    const notFoundModal = document.getElementById('barcodeNotFoundModal');
                    if (notFoundModal) notFoundModal.classList.remove('hidden');
                }
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

        if (form) {
            form.addEventListener("submit", function (e) {
                e.preventDefault(); // prevent page reload
                checkBarcode();     // call your barcode check function
            });
        }

        // Allow Enter key to submit the form
        if (barcodeInput) {
            barcodeInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    checkBarcode();
                }
            });
        }

        // Debug: Log modal states
        console.log('Type Barcode Modal loaded');
        console.log('Register Product Modal exists:', document.getElementById('registerProductModal') !== null);
        console.log('Current path:', window.location.pathname);
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
function openBulkRestockModal() {
    let selected = document.querySelectorAll('input[name="productSelect"]:checked');
    if (selected.length === 0) {
        alert("Please select at least one product!");
        return;
    }

    // Collect IDs
    let ids = [];
    selected.forEach(cb => ids.push(cb.value));
    document.getElementById("selectedProductIds").value = ids.join(',');

    document.getElementById("bulkRestockModal").classList.remove("hidden");
}

function closeBulkRestockModal() {
    document.getElementById("bulkRestockModal").classList.add("hidden");
}

function toggleSelectAll(masterCheckbox) {
    document.querySelectorAll('input[name="productSelect"]').forEach(cb => {
        cb.checked = masterCheckbox.checked;
    });
}
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
