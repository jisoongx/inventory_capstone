@extends('dashboards.owner.owner')

@section('content')
<div class="p-6 flex flex-col lg:flex-row gap-6">

    <!-- 📄 Current Restock (Main Content) -->
    <div class="flex-1 bg-white shadow rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800">Restock Details</h1>
                <p class="text-gray-500 text-sm">View details of your selected restock list</p>
            </div>
            @if($restocks->count())
            <div class="text-sm text-blue-400">
                Latest: {{ \Carbon\Carbon::parse($restocks->first()->restock_created)->format('M d, Y • h:i A') }}
            </div>
            @endif
        </div>

        <!-- Toolbar (optional future actions) -->
        <div class="flex justify-end mb-3 gap-2">
            <button
                class="px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-md hover:bg-gray-50">
                Export
            </button>
            <button
                class="px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-md hover:bg-gray-50">
                Print
            </button>
        </div>

        <!-- Dynamic content -->
        <div id="restockContent" class="transition-opacity duration-200 opacity-100">
            @if($restocks->count())
            @php
            $latest = $restocks->first();
            $latestItems = $restockItems->where('restock_id', $latest->restock_id);
            @endphp

            <h2 class="text-sm font-medium text-gray-700 mb-3">
                {{ \Carbon\Carbon::parse($latest->restock_created)->format('F d, Y • h:i A') }}
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium">Product</th>
                            <th class="px-4 py-2 text-center font-medium">Quantity</th>
                           
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($latestItems as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-2 text-gray-800">{{ $item->name }}</td>
                            <td class="px-4 py-2 text-center">{{ $item->item_quantity }}</td>
                            
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="flex items-center gap-2 text-gray-500 bg-gray-50 border border-gray-200 p-4 rounded-md text-sm">
                <span>📦</span> No restock has been finalized yet.
            </div>
            @endif
        </div>
    </div>

    <!-- 📜 Restock History (Sidebar) -->
    <div class="w-full lg:w-80 bg-white shadow rounded-xl p-6">
        <h2 class="text-md font-semibold text-gray-800 mb-3">History</h2>
        <p class="text-xs text-gray-500 mb-4">Click a record to view details</p>

        @if($restocks->count())
        <ul class="space-y-2">
            @foreach($restocks as $index => $restock)
            @php
            $items = $restockItems->where('restock_id', $restock->restock_id);
            @endphp
            <li id="history-{{ $restock->restock_id }}"
                class="p-3 rounded-md cursor-pointer transition
                @if($index === 0) bg-blue-50 border-l-4 border-blue-500 @else hover:bg-gray-50 border border-gray-200 @endif"
                onclick="showRestock('{{ $restock->restock_id }}')">
                <div class="text-sm font-medium text-gray-800">
                    {{ \Carbon\Carbon::parse($restock->restock_created)->format('M d, Y • h:i A') }}
                </div>
                <div class="text-xs text-gray-500">
                    {{ $items->count() }} items
                </div>

                <!-- Hidden content -->
                <div class="hidden" id="restock-{{ $restock->restock_id }}">
                    <h2 class="text-sm font-medium text-gray-700 mb-3">
                        {{ \Carbon\Carbon::parse($restock->restock_created)->format('F d, Y • h:i A') }}
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium">Product</th>
                                    <th class="px-4 py-2 text-center font-medium">Quantity</th>
                                   
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($items as $item)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2 text-gray-800">{{ $item->name }}</td>
                                    <td class="px-4 py-2 text-center">{{ $item->item_quantity }}</td>
                                   
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
        @else
        <p class="text-sm text-gray-500">No restock history found.</p>
        @endif
    </div>
</div>

<!-- Script -->
<script>
    function showRestock(id) {
        // Swap content
        const content = document.getElementById('restock-' + id).innerHTML;
        const container = document.getElementById('restockContent');
        container.style.opacity = 0;
        setTimeout(() => {
            container.innerHTML = content;
            container.style.opacity = 1;
        }, 200);

        // Highlight active history item
        document.querySelectorAll('[id^="history-"]').forEach(el => {
            el.classList.remove('bg-blue-50', 'border-l-4', 'border-blue-500');
            el.classList.add('hover:bg-gray-50', 'border', 'border-gray-200');
        });
        const active = document.getElementById('history-' + id);
        active.classList.remove('hover:bg-gray-50', 'border', 'border-gray-200');
        active.classList.add('bg-blue-50', 'border-l-4', 'border-blue-500');
    }
</script>
@endsection