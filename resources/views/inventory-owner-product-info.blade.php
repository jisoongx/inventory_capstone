@extends('dashboards.owner.owner')
<head>
    <title>Product Information</title>
</head>
@section('content')

<div class="px-4">
    @livewire('expiration-container')
</div>

<div class="max-w-6xl mx-auto py-4"> 

    <!-- Back Button -->
    <div class="mb-6 mt-4">
        <a href="{{ route('inventory-owner') }}" 
           class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg shadow-sm hover:bg-blue-300 transition">
            <span class="material-symbols-outlined text-base mr-1">arrow_back</span>
            Inventory
        </a>
    </div>

    <!-- Product Information (Enhanced Card) -->
<div class="bg-white rounded-lg shadow-md p-6 mb-10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
        
        <!-- Product Image -->
        <div class="flex justify-center">
            @if($product->prod_image)
                <img src="{{ asset('storage/' . $product->prod_image) }}" 
                     alt="{{ $product->name }}" 
                     class="w-56 h-56 object-cover rounded-lg shadow-md border">
            @else
                <div class="w-56 h-56 flex items-center justify-center bg-gray-200 text-gray-500 rounded-lg shadow-md border">
                    No Image
                </div>
            @endif
        </div>

        <!-- Product Details -->
        <div class="space-y-4">
            <!-- Name + Badge -->
            <div class="flex items-start justify-between">
                <h2 class="text-2xl font-semibold leading-snug break-words">
                    {{ $product->name }}
                </h2>

                <!-- Stock Badge -->
                @if($totalStock <= 0)
                    <span class="px-2.5 py-0.5 text-xs bg-red-100 text-red-700 font-medium rounded-full shadow-sm">
                        Out of Stock
                    </span>
                @elseif($totalStock <= $lowStockThreshold)
                    <span class="px-2.5 py-0.5 text-xs bg-yellow-100 text-yellow-700 font-medium rounded-full shadow-sm">
                        Low Stock
                    </span>
                @else
                    <span class="px-2.5 py-0.5 text-xs bg-green-100 text-green-700 font-medium rounded-full shadow-sm">
                        In Stock
                    </span>
                @endif
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-2 gap-4 text-sm">
                <p class="text-gray-500">Barcode</p>
                <p class="font-medium">{{ $product->barcode }}</p>

                <p class="text-gray-500">Cost Price</p>
                <p class="font-medium">₱{{ number_format($product->cost_price, 2) }}</p>

                <p class="text-gray-500">Selling Price</p>
                <p class="font-medium">₱{{ number_format($product->selling_price, 2) }}</p>

                <p class="text-gray-500">Unit</p>
                <p class="font-medium">{{ $product->unit }}</p>

                <p class="text-gray-500">Stock Limit</p>
                <p class="font-medium">{{ $product->stock_limit }}</p>

                <p class="text-gray-500">Total Stock</p>
                <p class="font-medium">{{ $totalStock }}</p>
            </div>

            <!-- Description -->
            <div>
                <p class="text-gray-500 text-sm">Description</p>
                <p class="mt-1 text-xs">{{ $product->description ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>


    <!-- Restock History Section -->
    <h2 class="text-lg font-semibold mb-3">Stock History</h2>
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border border-gray-100">
                <thead class="bg-gray-100 text-gray-700 text-sm">
                    <tr>
                        <th class="px-4 py-2 border">Batch</th>
                        <th class="px-4 py-2 border">Quantity</th>
                        <th class="px-4 py-2 border">Date Added</th>
                        <th class="px-4 py-2 border">Expiration Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($restocks as $restock)
                        <tr class="hover:bg-gray-50 text-sm">
                            <td class="px-4 py-2 border text-center">{{ $restock->batch_number }}</td>
                            <td class="px-4 py-2 border text-center">{{ $restock->stock }}</td>
                            <td class="px-4 py-2 border text-center">
                                {{ \Carbon\Carbon::parse($restock->date_added)->format('F j, Y') }}
                            </td>
                            <td class="px-4 py-2 border text-center">
                                {{ $restock->expiration_date ? \Carbon\Carbon::parse($restock->expiration_date)->format('F j, Y') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-500">No restocks yet.</td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($restocks->count() > 0)
                <tfoot>
                    <tr class="bg-gray-100 font-semibold text-sm">
                        <td class="px-4 py-2 border text-right" colspan="1">Total</td>
                        <td class="px-4 py-2 border text-center">{{ $totalStock }}</td>
                        <td colspan="2" class="border"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div> {{-- end max-w container --}}

@endsection
