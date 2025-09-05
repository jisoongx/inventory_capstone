@extends('dashboards.owner.owner') 
<head>
    <title>Inventory</title>
</head>
@section('content')

        <!-- Inventory Table -->
        <div class="p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Product List</h2>

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
                            <i class="fas fa-filter mr-2"></i> Filter
                        </button>

                        <div id="categoryDropdown" 
                            class="absolute z-10 bg-white border border-gray-300 mt-2 rounded shadow hidden min-w-max whitespace-nowrap overflow-auto"
                        >
                            <!-- All Category Option -->
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
                            autocomplete="off"
                            class="rounded px-4 py-2 w-full pr-10 shadow-lg focus:outline-none focus:shadow-lg border border-gray-50 placeholder:text-sm placeholder-gray-400"

                        >

                        {{-- Suggestions Dropdown --}}
                        <div 
                            id="suggestions" 
                            class="absolute z-10 w-full bg-white border border-gray-300 rounded shadow-md hidden max-h-60 overflow-y-auto mt-1"
                        ></div>

                        {{-- Search Icon (acts as submit button) --}}
                        <button 
                            type="submit" 
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-[#FF8A00] hover:text-orange-600"
                        >
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                {{-- Right side: Add Product --}}
                <div>
                    <button id="addProductBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-all duration-200 transform hover:scale-105">
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
                        @foreach ($products as $product)
                            <tr class="hover:bg-blue-50">
                                <!-- Product Image -->
                                <td class="px-4 py-2 border text-center">
                                    @if($product->prod_image)
                                        <img src="{{ asset('storage/' . $product->prod_image) }}" 
                                            alt="Product Image" 
                                            class="h-16 w-16 object-cover rounded mx-auto">
                                    @else
                                        —
                                    @endif
                                </td>

                                <!-- Barcode -->
                                <td class="px-4 py-2 border text-center">{{ $product->barcode }}</td>

                                <!-- Name -->
                                <td class="px-4 py-2 border">{{ $product->name }}</td>

                                <!-- Cost Price -->
                                <td class="px-4 py-2 border text-right">₱{{ number_format($product->cost_price, 2) }}</td>

                                <!-- Selling Price -->
                                <td class="px-4 py-2 border text-right">₱{{ number_format($product->selling_price, 2) }}</td>

                                <!-- Unit -->
                                <td class="px-4 py-2 border text-center">{{ $product->unit ?? '—' }}</td>

                                <!-- Stock (from inventory) -->
                                <td class="px-4 py-2 border text-center">{{ $product->stock }}</td>

                                <!-- Action Icons -->
                                <td class="px-4 py-2 border text-center space-x-2">
                                    <!-- Information Icon -->
                                    <a href="#" title="Info" class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                    <!-- Edit Icon -->
                                    <a href="#" title="Edit" class="text-green-500 hover:text-green-700">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <!-- Delete Icon -->
                                    <form action="#" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        @if (count($products) === 0)
                            <tr>
                                <td colspan="8" class="text-center py-4 text-gray-500">No products available.</td>
                            </tr>
                        @endif
                    </tbody>

                </table>
            </div>

        </div>

    
    <!-- Add Product Modal -->
    <div id="addProductModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg p-8 w-[90%] max-w-md min-h-[550px] shadow-lg relative">
            <!-- Close Button -->
            <button id="closeAddProductModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">
                <i class="fas fa-times"></i>
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
                    <i class="fas fa-times text-xl"></i>
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



    <!-- Modal for barcode already exists -->
    <div id="barcodeExistsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg w-[50%] min-h-[550px] shadow-lg relative flex flex-col items-center">

            <!-- Red Top Bar -->
            <div class="bg-[#B50612] w-full h-16 flex items-center justify-between px-6 rounded-t-lg">
                <h2 class="text-white text-lg font-medium">Barcode Already Exists</h2>
                <button onclick="closeAllModals()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
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
                        onclick="openRestockModal(
                            '{{ $product->prod_code }}',
                            '{{ addslashes($product->name) }}',
                            '{{ $product->prod_image }}',
                            '{{ $product->category_id }}',
                            '{{ $product->barcode }}'
                        )" 
                        class="w-32 bg-[#F18301] text-white text-sm py-3 rounded-3xl hover:bg-[#cc6900] transition-all duration-200 transform hover:scale-105"
                    >
                        Restock
                    </button>

                        Add Stock
                    </button>

                    <button 
                        onclick="reopenTypeModal()" 
                        class="w-48 bg-black text-white text-sm py-3 rounded-3xl hover:bg-gray-800 transition-all duration-200 transform hover:scale-105"
                    >
                        Enter New Barcode
                    </button>
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
                    <i class="fas fa-times text-xl"></i>
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
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Scrollable Modal Content Center -->
            <div class="flex-1 w-full flex flex-row px-6 py-6 mb-6 overflow-y-auto space-x-6">

                <!-- Left Side (Form Fields) -->
                <form id="registerProductForm" class="w-1/2 space-y-3">

                    <!-- Product Name -->
                    <input type="text" name="name" placeholder="Product Name"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 placeholder:text-sm text-sm" required>

                    <!-- Description -->
                    <textarea name="description" placeholder="Description"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 placeholder:text-sm text-sm"></textarea>

                    <!-- Category + Unit in one row -->
                    <div class="flex space-x-2">
                        <select name="category_id"
                            class="w-1/2 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 text-sm"
                            style="position: relative; z-index: 50;" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->category_id }}">{{ $cat->category }}</option>
                            @endforeach
                        </select>

                        <select name="unit_id"
                            class="w-1/2 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 text-sm"
                            style="position: relative; z-index: 50;" required>
                            <option value="">Select Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->unit_id }}">{{ $unit->unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Quantity to Add -->
                    <div class="flex items-center border border-gray-300 rounded px-2 py-1">
                        <button type="button" onclick="decreaseQuantity()" class="px-2 text-base font-bold">−</button>
                        <input type="number" name="quantity" id="quantityInput" value="1" min="1"
                            class="w-full text-center outline-none border-0 text-sm">
                        <button type="button" onclick="increaseQuantity()" class="px-2 text-base font-bold">+</button>
                    </div>

                    <!-- Stock Limit -->
                    <input type="number" name="stock_limit" placeholder="Stock Limit"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 placeholder:text-sm text-sm" required>


                    <!-- Pricing -->
                    <div class="border rounded-lg p-3">
                        <h3 class="font-semibold text-sm mb-2">Pricing</h3>

                        <!-- Cost Price -->
                        <input type="number" step="0.01" name="cost_price" id="costPrice" placeholder="Cost Price"
                            class="w-full px-3 py-2 mb-2 border border-gray-300 rounded focus:outline-none focus:border-red-600 text-sm" required>

                        <!-- Markup Type -->
                        <div class="flex space-x-2 mb-2">
                            <select id="markupType" class="w-1/2 px-3 py-2 border border-gray-300 rounded text-sm">
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed</option>
                            </select>
                            <input type="number" id="markupValue" placeholder="Markup Value"
                                class="w-1/2 px-3 py-2 border border-gray-300 rounded text-sm">
                        </div>

                        <!-- Selling Price (auto-calculated) -->
                        <input type="number" step="0.01" name="selling_price" id="sellingPrice" placeholder="Selling Price"
                            class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-100 text-sm" readonly>
                    </div>
                </form>

                <!-- Right Side (Photo Upload & Barcode Display) -->
                <div class="flex flex-col items-center w-1/2 space-y-3">
                    <!-- Upload Photo -->
                    <label for="productPhoto" 
                        class="w-32 h-32 flex items-center justify-center border-2 border-dashed border-red-400 rounded-lg cursor-pointer hover:bg-gray-50 relative overflow-hidden">
                        <i class="fas fa-plus text-red-600 text-2xl" id="uploadIcon"></i>
                        <img id="previewImage" class="absolute inset-0 w-full h-full object-cover hidden rounded-lg" />
                        <input type="file" id="productPhoto" name="photo" accept="image/png, image/jpeg, image/jpg, image/webp" class="hidden">
                    </label>
                    <span class="text-xs text-gray-500" id="fileName">Upload Photo</span>

                    <!-- Auto-filled Barcode -->
                    <div id="autoFilledBarcode" class="text-base font-semibold text-gray-800"></div>
                    <span class="text-xs text-gray-500">(auto-filled from typed barcode)</span>

                    <!-- Submit Button -->
                    <button type="submit" form="registerProductForm"
                        class="w-32 bg-green-500 text-white py-2 rounded-3xl hover:bg-green-600 transition-all duration-200 transform hover:scale-105 text-sm">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Restock Product Modal -->
    <div id="restockModal" 
        class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">

        <div class="bg-white rounded-lg w-[50%] min-h-[550px] shadow-lg relative flex flex-col items-center">

            <!-- Orange Top Bar -->
            <div class="bg-[#F18301] w-full h-16 flex items-center justify-between px-6 rounded-t-lg">
                <h2 class="text-white text-lg font-medium">RESTOCK PRODUCT</h2>
                <button onclick="closeRestockModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Scrollable Modal Content Center -->
            <div class="flex-1 w-full flex flex-row px-6 py-6 mb-6 overflow-y-auto space-x-6">

                <!-- Left Side (Form Fields) -->
                <form id="restockForm" class="w-1/2 space-y-3">
                    @csrf
                    <input type="hidden" name="prod_code" id="restockProdCode">
                    <input type="hidden" name="category_id" id="restockCategoryId">

                    <!-- Quantity to Add -->
                    <div class="flex items-center border border-gray-300 rounded px-2 py-1">
                        <button type="button" onclick="decreaseRestockQuantity()" class="px-2 text-base font-bold">−</button>
                        <input type="number" name="stock" id="restockQuantityInput" value="1" min="1"
                            class="w-full text-center outline-none border-0 text-sm">
                        <button type="button" onclick="increaseRestockQuantity()" class="px-2 text-base font-bold">+</button>
                    </div>

                    <!-- Date Added -->
                    <input type="date" name="date_added"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-orange-500 placeholder:text-sm text-sm" required>

                    <!-- Expiration Date -->
                    <input type="date" name="expiration_date"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-orange-500 placeholder:text-sm text-sm">

                    <!-- Batch Number -->
                    <input type="text" name="batch_number" placeholder="Batch Number"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-orange-500 placeholder:text-sm text-sm">

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-32 bg-[#F18301] text-white py-2 rounded-3xl hover:bg-[#d96f00] transition-all duration-200 transform hover:scale-105 text-sm">
                        Add Stock
                    </button>
                </form>

                <!-- Right Side (Product Preview & Barcode) -->
                <div class="flex flex-col items-center w-1/2 space-y-3">
                    <!-- Product Image -->
                    <img id="restockProdImage" src="" alt="Product Image"
                        class="w-32 h-32 object-cover rounded-lg border">

                    <!-- Product Name -->
                    <h3 id="restockProdName" class="text-lg font-semibold text-gray-800"></h3>

                    <!-- Auto-filled Barcode -->
                    <div id="restockBarcode" class="text-base font-semibold text-gray-800"></div>
                    <span class="text-xs text-gray-500">(auto-filled from selected product)</span>
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

        // Filter by category
        function filterByCategory(categoryId) {
            if (categoryId === 'all') {
                window.location.href = `{{ url('inventory-owner') }}`;
            } else {
                window.location.href = `{{ url('inventory-owner') }}?category=${categoryId}`;
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
                    // ✅ Instead of just opening the "exists" modal, go directly to restock modal
                    openRestockModal(
                        data.product.prod_code,
                        data.product.name,
                        data.product.prod_image,
                        data.product.category_id,
                        data.product.barcode
                    );
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

        // Quantity increment/decrement
        function increaseQuantity() {
            const input = document.getElementById('quantityInput');
            if (input) input.value = parseInt(input.value || 0) + 1;
        }
        function decreaseQuantity() {
            const input = document.getElementById('quantityInput');
            if (input && parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
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

    <!-- Add Stock Modal JavaScript -->
    <script>
        function openRestockModal(prodCode, prodName, prodImage, categoryId, barcode) {
            // Fill hidden inputs
            document.getElementById('restockProdCode').value = prodCode;
            document.getElementById('restockCategoryId').value = categoryId;

            // Display product details
            document.getElementById('restockProdName').textContent = prodName || '';
            document.getElementById('restockProdImage').src = prodImage 
                ? `/storage/${prodImage}` 
                : '/assets/no-image.png';

            // Auto-fill barcode display
            const barcodeElement = document.getElementById('restockBarcode');
            if (barcodeElement) {
                barcodeElement.textContent = barcode || prodCode || '';
            }

            // Reset quantity to 1 when opening modal
            document.getElementById('restockQuantityInput').value = 1;

            // Show modal
            document.getElementById('restockModal').classList.remove('hidden');
        }

        // Close modal
        function closeRestockModal() {
            document.getElementById('restockModal').classList.add('hidden');
        }

        // Quantity increment/decrement
        function increaseRestockQuantity() {
            const input = document.getElementById('restockQuantityInput');
            if (input) input.value = parseInt(input.value || 0) + 1;
        }
        function decreaseRestockQuantity() {
            const input = document.getElementById('restockQuantityInput');
            if (input && parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }

        // Handle form submission
        document.getElementById('restockForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('/owner/restock', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                }
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






@endsection
