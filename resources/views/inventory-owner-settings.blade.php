@extends('dashboards.owner.owner')

<head>
    <title>Category and Unit Settings</title>
</head>

@section('content')
<div class="p-6">

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="max-w-5xl mx-auto mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-5xl mx-auto mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Title + Back Button in one row -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Category and Unit Settings</h2>
        <a href="{{ route('inventory-owner') }}" 
           class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
            <span class="material-symbols-outlined text-sm mr-1">assignment_return</span>
            Back
        </a>
    </div>

    <!-- Centered grid with max width -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl mx-auto">
        
        <!-- Product Categories -->
        <div class="bg-white p-4 rounded-xl shadow-lg max-w-lg mx-auto w-full flex flex-col h-[32rem]">
            <div class="bg-orange-400 text-white font-semibold text-center p-2 rounded mb-2">
                Product Categories
            </div>
            
            <!-- Sort Filter for Categories -->
            <div class="mb-3 flex justify-end">
                <select id="category-sort" class="text-xs border border-gray-300 rounded px-2 py-1 focus:border-orange-300">
                    <option value="asc">A-Z (Ascending)</option>
                    <option value="desc">Z-A (Descending)</option>
                </select>
            </div>
            
            <!-- Scrollable List -->
            <div id="category-list" class="flex-grow overflow-y-auto mb-4 space-y-2 pr-1">
                @foreach($categories->sortBy('category') as $category)
                    <li class="category-item flex justify-between items-center bg-gray-50 text-sm p-3 rounded shadow-sm" data-name="{{ $category->category }}">
                        <span>{{ $category->category }}</span>
                        <!-- Edit Form -->
                        <form action="{{ route('owner.category.update', $category->category_id) }}" 
                              method="POST" 
                              class="category-edit-form flex space-x-2"
                              data-category-id="{{ $category->category_id }}"
                              data-original-value="{{ $category->category }}">
                            @csrf
                            @method('PATCH')
                            <div class="flex flex-col items-end">
                                <div class="flex space-x-2">
                                    <input type="text" 
                                           name="category" 
                                           value="{{ $category->category }}" 
                                           class="category-edit-input border border-gray-300 focus:border-orange-300 rounded px-2 py-1 text-sm w-32" 
                                           required
                                           data-type="category">
                                    <button type="submit" 
                                            class="category-update-btn bg-orange-400 hover:bg-orange-500 text-white px-3 py-1 rounded text-sm">
                                        Update
                                    </button>
                                </div>
                                <div class="category-error-message text-xs text-red-600 mt-1 hidden"></div>
                            </div>
                        </form>
                    </li>
                @endforeach
            </div>

            <!-- Add New Category pinned at bottom -->
            <div class="mt-auto">
                <form action="{{ route('owner.category.store') }}" 
                      method="POST" 
                      id="add-category-form"
                      class="flex justify-center items-start space-x-2">
                    @csrf
                    <div class="flex flex-col">
                        <div class="flex space-x-2">
                            <input type="text" 
                                   name="category" 
                                   id="new-category-input"
                                   placeholder="New Category" 
                                   class="border border-gray-300 rounded px-2 py-1 text-sm w-48 
                                          placeholder:text-gray-400 focus:border-orange-300" 
                                   required
                                   data-type="category">
                            <button type="submit" 
                                    id="add-category-btn"
                                    class="bg-orange-400 hover:bg-orange-500 text-white text-sm px-4 py-1 rounded">
                                Add
                            </button>
                        </div>
                        <div id="new-category-error" class="text-xs text-red-600 mt-1 hidden"></div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Units -->
        <div class="bg-white p-4 rounded-xl shadow-lg max-w-lg mx-auto w-full flex flex-col h-[32rem]">
            <div class="bg-green-500 text-white font-semibold text-center p-2 rounded mb-2">
                Product Units
            </div>

            <!-- Sort Filter for Units -->
            <div class="mb-3 flex justify-end">
                <select id="unit-sort" class="text-xs border border-gray-300 rounded px-2 py-1 focus:border-green-400">
                    <option value="asc">A-Z (Ascending)</option>
                    <option value="desc">Z-A (Descending)</option>
                </select>
            </div>

            <!-- Scrollable List -->
            <div id="unit-list" class="flex-grow overflow-y-auto mb-4 space-y-2 pr-1">
                @foreach($units->sortBy('unit') as $unit)
                    <li class="unit-item flex justify-between items-center bg-gray-50 p-3 rounded text-sm shadow-sm" data-name="{{ $unit->unit }}">
                        <span>{{ $unit->unit }}</span>
                        <!-- Edit Form -->
                        <form action="{{ route('owner.unit.update', $unit->unit_id) }}" 
                              method="POST" 
                              class="unit-edit-form flex space-x-2"
                              data-unit-id="{{ $unit->unit_id }}"
                              data-original-value="{{ $unit->unit }}">
                            @csrf
                            @method('PATCH')
                            <div class="flex flex-col items-end">
                                <div class="flex space-x-2">
                                    <input type="text" 
                                           name="unit" 
                                           value="{{ $unit->unit }}" 
                                           class="unit-edit-input border border-gray-300 focus:border-green-400 rounded px-2 py-1 text-sm w-28" 
                                           required
                                           data-type="unit">
                                    <button type="submit" 
                                            class="unit-update-btn bg-green-500 hover:bg-green-600 text-white text-sm px-3 py-1 rounded">
                                        Update
                                    </button>
                                </div>
                                <div class="unit-error-message text-xs text-red-600 mt-1 hidden"></div>
                            </div>
                        </form>
                    </li>
                @endforeach
            </div>

            <!-- Add New Unit pinned at bottom -->
            <div class="mt-auto">
                <form action="{{ route('owner.unit.store') }}" 
                      method="POST" 
                      id="add-unit-form"
                      class="flex justify-center items-start space-x-2">
                    @csrf
                    <div class="flex flex-col">
                        <div class="flex space-x-2">
                            <input type="text" 
                                   name="unit" 
                                   id="new-unit-input"
                                   placeholder="New Unit" 
                                   class="border border-gray-300 rounded px-2 py-1 text-sm w-48 
                                          placeholder:text-gray-400 focus:border-green-400" 
                                   required
                                   data-type="unit">
                            <button type="submit" 
                                    id="add-unit-btn"
                                    class="bg-green-500 hover:bg-green-600 text-white text-sm px-4 py-1 rounded">
                                Add
                            </button>
                        </div>
                        <div id="new-unit-error" class="text-xs text-red-600 mt-1 hidden"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let typingTimer;
    const typingDelay = 500; // 500ms delay after user stops typing

    // =========================== SORTING FUNCTIONALITY ===========================
    
    // Category sorting
    const categorySort = document.getElementById('category-sort');
    const categoryList = document.getElementById('category-list');
    
    categorySort.addEventListener('change', function() {
        sortItems(categoryList, 'category-item', this.value);
    });
    
    // Unit sorting
    const unitSort = document.getElementById('unit-sort');
    const unitList = document.getElementById('unit-list');
    
    unitSort.addEventListener('change', function() {
        sortItems(unitList, 'unit-item', this.value);
    });
    
    function sortItems(container, itemClass, order) {
        const items = Array.from(container.querySelectorAll('.' + itemClass));
        
        items.sort((a, b) => {
            const nameA = a.dataset.name.toLowerCase();
            const nameB = b.dataset.name.toLowerCase();
            
            if (order === 'asc') {
                return nameA.localeCompare(nameB);
            } else {
                return nameB.localeCompare(nameA);
            }
        });
        
        // Clear container and re-append sorted items
        container.innerHTML = '';
        items.forEach(item => container.appendChild(item));
    }

    // =========================== REAL-TIME VALIDATION ===========================
    
    // Handle new category input
    const newCategoryInput = document.getElementById('new-category-input');
    const newCategoryError = document.getElementById('new-category-error');
    const addCategoryBtn = document.getElementById('add-category-btn');

    if (newCategoryInput) {
        newCategoryInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            const value = this.value.trim();
            
            if (value.length === 0) {
                hideError(newCategoryError);
                enableButton(addCategoryBtn);
                return;
            }

            typingTimer = setTimeout(() => {
                checkExistence(value, 'category', newCategoryError, addCategoryBtn);
            }, typingDelay);
        });
    }

    // Handle new unit input
    const newUnitInput = document.getElementById('new-unit-input');
    const newUnitError = document.getElementById('new-unit-error');
    const addUnitBtn = document.getElementById('add-unit-btn');

    if (newUnitInput) {
        newUnitInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            const value = this.value.trim();
            
            if (value.length === 0) {
                hideError(newUnitError);
                enableButton(addUnitBtn);
                return;
            }

            typingTimer = setTimeout(() => {
                checkExistence(value, 'unit', newUnitError, addUnitBtn);
            }, typingDelay);
        });
    }

    // Handle category edit inputs
    document.querySelectorAll('.category-edit-input').forEach(input => {
        const form = input.closest('.category-edit-form');
        const errorDiv = form.querySelector('.category-error-message');
        const submitBtn = form.querySelector('.category-update-btn');
        const originalValue = form.dataset.originalValue;

        input.addEventListener('input', function() {
            clearTimeout(typingTimer);
            const value = this.value.trim();
            
            // If value is same as original, clear error and enable button
            if (value === originalValue) {
                hideError(errorDiv);
                enableButton(submitBtn);
                return;
            }

            if (value.length === 0) {
                hideError(errorDiv);
                enableButton(submitBtn);
                return;
            }

            typingTimer = setTimeout(() => {
                checkExistence(value, 'category', errorDiv, submitBtn, originalValue);
            }, typingDelay);
        });
    });

    // Handle unit edit inputs
    document.querySelectorAll('.unit-edit-input').forEach(input => {
        const form = input.closest('.unit-edit-form');
        const errorDiv = form.querySelector('.unit-error-message');
        const submitBtn = form.querySelector('.unit-update-btn');
        const originalValue = form.dataset.originalValue;

        input.addEventListener('input', function() {
            clearTimeout(typingTimer);
            const value = this.value.trim();
            
            // If value is same as original, clear error and enable button
            if (value === originalValue) {
                hideError(errorDiv);
                enableButton(submitBtn);
                return;
            }

            if (value.length === 0) {
                hideError(errorDiv);
                enableButton(submitBtn);
                return;
            }

            typingTimer = setTimeout(() => {
                checkExistence(value, 'unit', errorDiv, submitBtn, originalValue);
            }, typingDelay);
        });
    });

    // =========================== FORM SUBMISSION PREVENTION ===========================
    
    // Prevent form submission if there are errors
    document.getElementById('add-category-form')?.addEventListener('submit', function(e) {
        if (addCategoryBtn.disabled) {
            e.preventDefault();
            return false;
        }
    });

    document.getElementById('add-unit-form')?.addEventListener('submit', function(e) {
        if (addUnitBtn.disabled) {
            e.preventDefault();
            return false;
        }
    });

    document.querySelectorAll('.category-edit-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('.category-update-btn');
            if (submitBtn.disabled) {
                e.preventDefault();
                return false;
            }
        });
    });

    document.querySelectorAll('.unit-edit-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('.unit-update-btn');
            if (submitBtn.disabled) {
                e.preventDefault();
                return false;
            }
        });
    });

    // =========================== HELPER FUNCTIONS ===========================
    
    function checkExistence(name, type, errorDiv, submitBtn, excludeValue = null) {
        fetch('{{ route("owner.check-existing-name") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                name: name,
                type: type,
                excludeValue: excludeValue
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                let message;
                if (data.isExactMatch) {
                    message = `${type === 'category' ? 'Category' : 'Unit'} already exists: <strong>"${data.existingName}"</strong>`;
                } else {
                    message = `Similar ${type} already exists: <strong>"${data.existingName}"</strong>. Did you mean this one?`;
                }
                showError(errorDiv, message);
                disableButton(submitBtn);
            } else {
                hideError(errorDiv);
                enableButton(submitBtn);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            hideError(errorDiv);
            enableButton(submitBtn);
        });
    }

    function showError(element, message) {
        element.innerHTML = message;
        element.classList.remove('hidden');
    }

    function hideError(element) {
        element.innerHTML = '';
        element.classList.add('hidden');
    }

    function disableButton(button) {
        button.disabled = true;
        button.classList.add('opacity-50', 'cursor-not-allowed');
    }

    function enableButton(button) {
        button.disabled = false;
        button.classList.remove('opacity-50', 'cursor-not-allowed');
    }
});
</script>
@endsection