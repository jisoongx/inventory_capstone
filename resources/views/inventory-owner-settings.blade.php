@extends('dashboards.owner.owner')

<head>
    <title>Category and Unit Settings</title>
</head>

@section('content')
<div class="p-6">

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
            <div class="bg-orange-400 text-white font-semibold text-center p-2 rounded mb-4">
                Product Categories
            </div>
            
            <!-- Scrollable List -->
            <div class="flex-grow overflow-y-auto mb-4 space-y-2 pr-1">
                @foreach($categories as $category)
                    <li class="flex justify-between items-center bg-gray-50 text-sm p-3 rounded shadow-sm">
                        <span>{{ $category->category }}</span>
                        <!-- Edit Form -->
                        <form action="{{ route('owner.category.update', $category->category_id) }}" method="POST" class="flex space-x-2 mt-3">
                            @csrf
                            @method('PATCH')
                            <input type="text" name="category" value="{{ $category->category }}" 
                                   class="border border-gray-300 focus:border-orange-300 rounded px-2 py-1 text-sm w-32" required>
                            <button type="submit" 
                                    class="bg-orange-400 hover:bg-orange-500 text-white px-3 py-1 rounded text-sm">
                                Update
                            </button>
                        </form>
                    </li>
                @endforeach
            </div>

            <!-- Add New Category pinned at bottom -->
            <div class="mt-auto">
                <form action="{{ route('owner.category.store') }}" method="POST" 
                    class="flex justify-center items-center space-x-2">
                    @csrf
                    <input type="text" name="category" placeholder="New Category" 
                        class="border border-gray-300 rounded px-2 py-1 text-sm w-48 
                                placeholder:text-gray-400 focus:border-orange-300" required>
                    <button type="submit" 
                            class="bg-orange-400 hover:bg-orange-500 text-white text-sm px-4 py-1 rounded">
                        Add
                    </button>
                </form>
            </div>
        </div>

        <!-- Product Units -->
        <div class="bg-white p-4 rounded-xl shadow-lg max-w-lg mx-auto w-full flex flex-col h-[32rem]">
            <div class="bg-green-500 text-white font-semibold text-center p-2 rounded mb-4">
                Product Units
            </div>

            <!-- Scrollable List -->
            <div class="flex-grow overflow-y-auto mb-4 space-y-2 pr-1">
                @foreach($units as $unit)
                    <li class="flex justify-between items-center bg-gray-50 p-3 rounded text-sm shadow-sm">
                        <span>{{ $unit->unit }}</span>
                        <!-- Edit Form -->
                        <form action="{{ route('owner.unit.update', $unit->unit_id) }}" method="POST" class="flex space-x-2 mt-3">
                            @csrf
                            @method('PATCH')
                            <input type="text" name="unit" value="{{ $unit->unit }}" 
                                   class="border border-gray-300 focus:border-green-400 rounded px-2 py-1 text-sm w-28" required>
                            <button type="submit" 
                                    class="bg-green-500 hover:bg-green-600 text-white text-sm px-3 py-1 rounded">
                                Update
                            </button>
                        </form>
                    </li>
                @endforeach
            </div>

            <!-- Add New Unit pinned at bottom -->
            <div class="mt-auto">
                <form action="{{ route('owner.unit.store') }}" method="POST" 
                    class="flex justify-center items-center space-x-2">
                    @csrf
                    <input type="text" name="unit" placeholder="New Unit" 
                        class="border border-gray-300 rounded px-2 py-1 text-sm w-48 
                                placeholder:text-gray-400 focus:border-green-400" required>
                    <button type="submit" 
                            class="bg-green-500 hover:bg-green-600 text-white text-sm px-4 py-1 rounded">
                        Add
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
