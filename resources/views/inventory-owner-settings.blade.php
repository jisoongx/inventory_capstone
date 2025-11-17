@extends('dashboards.owner.owner')

<head>
    <title>Category and Unit Settings</title>
    <style>
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
            max-width: 400px;
        }

        .toast-success {
            background-color: #10b981;
            color: white;
        }

        .toast-error {
            background-color: #22c55e;
            color: white;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    </style>
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
                                <div class="category-error-message text-xs mt-1 hidden"></div>
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
                        <div id="new-category-error" class="text-xs mt-1 hidden"></div>
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
                                <div class="unit-error-message text-xs mt-1 hidden"></div>
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
                        <div id="new-unit-error" class="text-xs mt-1 hidden"></div>
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
                this.classList.remove('border-red-500', 'border-yellow-500');
                return;
            }

            typingTimer = setTimeout(() => {
                checkExistence(value, 'category', newCategoryError, addCategoryBtn, this);
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
                this.classList.remove('border-red-500', 'border-yellow-500');
                return;
            }

            typingTimer = setTimeout(() => {
                checkExistence(value, 'unit', newUnitError, addUnitBtn, this);
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
                this.classList.remove('border-red-500', 'border-yellow-500');
                return;
            }

            if (value.length === 0) {
                hideError(errorDiv);
                enableButton(submitBtn);
                this.classList.remove('border-red-500', 'border-yellow-500');
                return;
            }

            typingTimer = setTimeout(() => {
                checkExistence(value, 'category', errorDiv, submitBtn, this, originalValue);
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
                this.classList.remove('border-red-500', 'border-yellow-500');
                return;
            }

            if (value.length === 0) {
                hideError(errorDiv);
                enableButton(submitBtn);
                this.classList.remove('border-red-500', 'border-yellow-500');
                return;
            }

            typingTimer = setTimeout(() => {
                checkExistence(value, 'unit', errorDiv, submitBtn, this, originalValue);
            }, typingDelay);
        });
    });

    // =========================== FORM SUBMISSION WITH CONFIRMATION ===========================
    
    // Prevent form submission if there are errors and handle confirmation
    document.getElementById('add-category-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (addCategoryBtn.disabled) {
            return false;
        }

        const value = newCategoryInput.value.trim();
        const response = await checkExistenceForSubmit(value, 'category', null);
        
        if (response && response.exists) {
            if (response.isExactMatch) {
                alert(`Cannot submit: Category "${value}" already exists as "${response.existingName}"`);
                return false;
            } else {
                // Similar match - ask for confirmation
                const proceed = confirm(
                    `Similar category found: "${response.existingName}"\n\n` +
                    `You're adding: "${value}"\n\n` +
                    `These appear similar. Proceed anyway?`
                );
                
                if (!proceed) {
                    return false;
                }
                
                // User confirmed - submit with flag using fetch
                await submitFormWithConfirmation(this, 'confirmed_similar');
                return false;
            }
        }
        
        // No conflicts - submit using fetch to ensure consistent behavior
        await submitFormDirectly(this);
    });

    document.getElementById('add-unit-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (addUnitBtn.disabled) {
            return false;
        }

        const value = newUnitInput.value.trim();
        const response = await checkExistenceForSubmit(value, 'unit', null);
        
        if (response && response.exists) {
            if (response.isExactMatch) {
                alert(`Cannot submit: Unit "${value}" already exists as "${response.existingName}"`);
                return false;
            } else {
                // Similar match - ask for confirmation
                const proceed = confirm(
                    `Similar unit found: "${response.existingName}"\n\n` +
                    `You're adding: "${value}"\n\n` +
                    `These appear similar. Proceed anyway?`
                );
                
                if (!proceed) {
                    return false;
                }
                
                // User confirmed - submit with flag using fetch
                await submitFormWithConfirmation(this, 'confirmed_similar');
                return false;
            }
        }
        
        // No conflicts - submit using fetch to ensure consistent behavior
        await submitFormDirectly(this);
    });

    document.querySelectorAll('.category-edit-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('.category-update-btn');
            if (submitBtn.disabled) {
                return false;
            }

            const input = form.querySelector('.category-edit-input');
            const value = input.value.trim();
            const originalValue = form.dataset.originalValue;
            
            // If same as original, just submit
            if (value === originalValue) {
                await submitFormDirectly(this);
                return;
            }

            const response = await checkExistenceForSubmit(value, 'category', originalValue);
            
            if (response && response.exists) {
                if (response.isExactMatch) {
                    alert(`Cannot submit: Category "${value}" already exists as "${response.existingName}"`);
                    return false;
                } else {
                    // Similar match - ask for confirmation
                    const proceed = confirm(
                        `Similar category found: "${response.existingName}"\n\n` +
                        `You're updating to: "${value}"\n\n` +
                        `These appear similar. Proceed anyway?`
                    );
                    
                    if (!proceed) {
                        return false;
                    }
                    
                    // User confirmed - submit with flag
                    await submitFormWithConfirmation(this, 'confirmed_similar');
                    return false;
                }
            }
            
            // No conflicts - submit normally
            await submitFormDirectly(this);
        });
    });

    document.querySelectorAll('.unit-edit-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('.unit-update-btn');
            if (submitBtn.disabled) {
                return false;
            }

            const input = form.querySelector('.unit-edit-input');
            const value = input.value.trim();
            const originalValue = form.dataset.originalValue;
            
            // If same as original, just submit
            if (value === originalValue) {
                await submitFormDirectly(this);
                return;
            }

            const response = await checkExistenceForSubmit(value, 'unit', originalValue);
            
            if (response && response.exists) {
                if (response.isExactMatch) {
                    alert(`Cannot submit: Unit "${value}" already exists as "${response.existingName}"`);
                    return false;
                } else {
                    // Similar match - ask for confirmation
                    const proceed = confirm(
                        `Similar unit found: "${response.existingName}"\n\n` +
                        `You're updating to: "${value}"\n\n` +
                        `These appear similar. Proceed anyway?`
                    );
                    
                    if (!proceed) {
                        return false;
                    }
                    
                    // User confirmed - submit with flag
                    await submitFormWithConfirmation(this, 'confirmed_similar');
                    return false;
                }
            }
            
            // No conflicts - submit normally
            await submitFormDirectly(this);
        });
    });


    // =========================== HELPER FUNCTIONS ===========================
    
    function checkExistence(name, type, errorDiv, submitBtn, inputElement, excludeValue = null) {
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
                    // Red error for exact match
                    message = `${type === 'category' ? 'Category' : 'Unit'} already exists: <strong>"${data.existingName}"</strong>`;
                    showError(errorDiv, message, 'red');
                    disableButton(submitBtn);
                    inputElement.classList.add('border-red-500');
                    inputElement.classList.remove('border-yellow-500');
                } else {
                    // Yellow warning for similar match
                    message = `Similar ${type} exists: "<strong>${data.existingName}</strong>"<br><span class="text-gray-600 text-xs">Proceed with caution</span>`;
                    showError(errorDiv, message, 'yellow');
                    enableButton(submitBtn); // Allow submission but show warning
                    inputElement.classList.add('border-yellow-500');
                    inputElement.classList.remove('border-red-500');
                }
            } else {
                hideError(errorDiv);
                enableButton(submitBtn);
                inputElement.classList.remove('border-red-500', 'border-yellow-500');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            hideError(errorDiv);
            enableButton(submitBtn);
            inputElement.classList.remove('border-red-500', 'border-yellow-500');
        });
    }

    async function checkExistenceForSubmit(name, type, excludeValue = null) {
        try {
            const response = await fetch('{{ route("owner.check-existing-name") }}', {
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
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error:', error);
            return null;
        }
    }

    // Add this function to show toasts
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Updated submitFormDirectly with dynamic UI update
    async function submitFormDirectly(form) {
        const formData = new FormData(form);
        const isAddForm = form.id === 'add-category-form' || form.id === 'add-unit-form';
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message || 'Operation successful!', 'success');
                
                // Clear input and reload page after short delay
                if (isAddForm) {
                    form.querySelector('input[type="text"]').value = '';
                }
                
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Operation failed', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Something went wrong. Please try again.', 'error');
        }
    }

    async function submitFormWithConfirmation(form, confirmField) {
        const formData = new FormData(form);
        formData.append(confirmField, '1');
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message || 'Operation successful!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Operation failed', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Something went wrong. Please try again.', 'error');
        }
    }

    function showError(element, message, color = 'red') {
        element.innerHTML = message;
        element.classList.remove('hidden');
        if (color === 'red') {
            element.classList.add('text-red-600', 'font-semibold');
            element.classList.remove('text-yellow-600');
        } else {
            element.classList.add('text-yellow-600');
            element.classList.remove('text-red-600', 'font-semibold');
        }
    }

    function hideError(element) {
        element.innerHTML = '';
        element.classList.add('hidden');
        element.classList.remove('text-red-600', 'text-yellow-600', 'font-semibold');
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