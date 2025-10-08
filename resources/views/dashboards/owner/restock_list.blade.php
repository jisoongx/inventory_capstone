@extends('dashboards.owner.owner')

@section('content')
<style>
    /* Hide scrollbar unless scrolling */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
        /* Chrome, Safari */
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        /* IE/Edge */
        scrollbar-width: none;
        /* Firefox */
    }
</style>
<div class="px-3 sm:px-4 lg:px-6 py-1 sm:py-2 lg:py-4 bg-slate-50 animate-slide-down">


    <div class="flex flex-col lg:flex-row gap-4">

        <div class="flex-1 bg-white shadow-md rounded p-6 sm:p-8 border-t-4 border-green-300">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between pb-6 border-b border-slate-200">
                <div>
                    <a href="{{ route('restock_suggestion') }}"
                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-green-600 transition-colors mb-3">
                        <span class="material-symbols-rounded text-xl">arrow_back</span>
                        Back
                    </a>
                    <h1 class="text-lg font-semibold text-green-800">Restock Details</h1>
                    <p class="text-slate-500 mt-1 text-sm">Review the items from a finalized restock list.</p>
                </div>

                <div class="flex items-center gap-2 mt-4 sm:mt-0">
                    <form id="exportForm" method="POST" action="{{ route('owner.exportPdf') }}">
                        @csrf
                        <input type="hidden" name="restock_id" id="exportRestockId">
                        <input type="hidden" name="restock_created" id="exportRestockCreated">
                        <input type="hidden" name="restock_items" id="exportRestockItems">
                        <button type="submit" id="exportBtn"
                            class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-slate-600 bg-white border border-slate-300 rounded-md hover:bg-slate-50 transition">
                            <span class="material-symbols-rounded text-sm">download</span>
                            Export
                        </button>

                    </form>

                    <button id="printBtn" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-slate-600 bg-white border border-slate-300 rounded-md hover:bg-slate-50 transition">
                        <span class="material-symbols-rounded text-sm">print</span>
                        Print
                    </button>

                </div>
            </div>

            <div id="restockContent" class="transition-opacity duration-300 opacity-100 pt-6 min-h-[380px]">

                @if($restocks->count())
                @php
                $latest = $restocks->first();
                $latestItems = $restockItems->where('restock_id', $latest->restock_id);
                @endphp

                <div class="flex items-center justify-between mb-4">

                    <span class="text-xs font-medium bg-green-100 text-green-700 px-2.5 py-1 rounded-full">
                        {{ \Carbon\Carbon::parse($latest->restock_created)->format('F d, Y • h:i A') }}
                    </span>
                </div>

                <div class="overflow-x-auto overflow-y-auto max-h-[48vh] no-scrollbar border border-slate-200 rounded-lg">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 text-slate-600 sticky top-0 z-10">

                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Product</th>
                                <th class="px-4 py-3 text-center font-semibold w-32">Quantity</th>
                                <th class="px-4 py-3 text-center font-semibold w-32">Cost Price</th>
                                <th class="px-4 py-3 text-center font-semibold w-32">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestItems as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->name }}</td>
                                <td class="px-4 py-3 text-center">{{ $item->item_quantity }}</td>
                                <td class="px-4 py-3 text-center">{{ number_format($item->cost_price, 2) }}</td>
                                <td class="px-4 py-3 text-center">{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 font-semibold text-slate-700">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right">Total</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($latestItems->sum('subtotal'), 2) }}
                                </td>
                            </tr>
                        </tfoot>


                    </table>
                </div>
                @else
                <div class="flex flex-col items-center justify-center text-center p-10 bg-slate-100 rounded-lg">
                    <span class="material-symbols-rounded text-5xl text-slate-400 mb-2">inventory_2</span>
                    <h3 class="font-semibold text-sm text-slate-700">No Restock Lists Found</h3>
                    <p class="text-xs text-slate-500">Create and finalize a restock list from the suggestions page first.</p>
                </div>
                @endif
            </div>
        </div>

        <div class="w-full lg:w-96 bg-white shadow-md min-h-[560px] rounded p-6 sm:p-8 self-start border-t-4 border-green-300">
            <h2 class="text-md font-semibold text-green-800 mb-1">History</h2>
            <p class="text-sm text-slate-500 mb-6 text-sm">Select a past list to view its details.</p>

            @if($restocks->count())
            <ul class="space-y-3 max-h-[60vh] no-scrollbar overflow-y-auto pr-2">
                @foreach($restocks as $index => $restock)
                @php
                $items = $restockItems->where('restock_id', $restock->restock_id);
                @endphp
                <li id="history-{{ $restock->restock_id }}"
                    class="p-4 rounded-lg cursor-pointer transition-all duration-200 @if($index === 0) bg-green-50 border-green-500 @else border border-slate-200 hover:border-green-400 hover:bg-green-50/50 @endif"
                    onclick="showRestock('{{ $restock->restock_id }}')">

                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-green-600">
                            {{ \Carbon\Carbon::parse($restock->restock_created)->format('M d, Y') }}
                        </div>
                        <div class="text-xs font-medium text-slate-500">
                            {{ $items->count() }} {{ Str::plural('item', $items->count()) }}
                        </div>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">
                        {{ \Carbon\Carbon::parse($restock->restock_created)->format('h:i A') }}
                    </div>

                    <div class="hidden" id="restock-{{ $restock->restock_id }}">
                        <div class="flex items-center justify-between mb-4">

                            <span class="text-xs font-medium bg-green-100 text-green-700 px-2.5 py-1 rounded-full">
                                {{ \Carbon\Carbon::parse($restock->restock_created)->format('F d, Y • h:i A') }}
                            </span>
                        </div>
                        <div class="overflow-x-auto overflow-y-auto max-h-[48vh] border border-slate-200 rounded-lg custom-scrollbar">
                            <table class="w-full text-sm">

                                <thead class="bg-slate-100 text-slate-600 sticky top-0 z-10">

                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold">Product</th>
                                        <th class="px-4 py-3 text-center font-semibold w-32">Quantity</th>
                                        <th class="px-4 py-3 text-center font-semibold w-32">Cost Price</th>
                                        <th class="px-4 py-3 text-center font-semibold w-32">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($items as $item)
                                    <tr class="hover:bg-green-50/50 transition-colors">
                                        <td class="px-4 py-3 text-slate-800 font-medium">{{ $item->name }}</td>
                                        <td class="px-4 py-3 text-center font-mono text-slate-700">{{ $item->item_quantity }}</td>
                                        <td class="px-4 py-3 text-center">{{ number_format($item->cost_price, 2) }}</td>
                                        <td class="px-4 py-3 text-center">{{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class=" border-t-4 border-slate-100 font-bold text-indigo-500">
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-left">Total</td>
                                        <td class="px-4 py-3 text-center">
                                            {{ number_format($items->sum('subtotal'), 2) }}
                                        </td>
                                    </tr>
                                </tfoot>

                            </table>

                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-xs text-slate-500 text-center py-4">No history to show.</p>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-select first history on page load
        const firstHistory = document.querySelector('[id^="history-"]');
        if (firstHistory) {
            const id = firstHistory.id.replace('history-', '');
            showRestock(id, false);
        }

        // EXPORT check
        const exportForm = document.getElementById('exportForm');
        exportForm?.addEventListener('submit', function(e) {
            const table = document.querySelector('#restockContent table');
            if (!table || table.querySelectorAll('tbody tr').length === 0) {
                e.preventDefault(); // stop form submit
                alert('No data to export!');
                return false;
            }
        });

        // PRINT check
        const printBtn = document.getElementById('printBtn');
        printBtn?.addEventListener('click', function() {
            const table = document.querySelector('#restockContent table');
            if (!table || table.querySelectorAll('tbody tr').length === 0) {
                alert('No data to print!');
                return;
            }

            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.open();
            printWindow.document.write(`
            <html>
            <head>
                <title>Restock List</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
                    th { background: #f1f1f1; }
                    h2 { text-align: center; margin-bottom: 10px; }
                </style>
            </head>
            <body>
                <h2>SHOPLYTIX RESTOCK LIST</h2>
                ${document.getElementById('restockContent').innerHTML}
            </body>
            </html>
        `);
            printWindow.document.close();

            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
            };
        });
    });

    /**
     * Show restock details in the main container and update export inputs
     */
    function showRestock(id, animate = true) {
        const templateEl = document.getElementById('restock-' + id);
        if (!templateEl) return;

        const container = document.getElementById('restockContent');
        if (animate) container.style.opacity = 0;

        setTimeout(() => {
            container.innerHTML = templateEl.innerHTML;
            if (animate) container.style.opacity = 1;
        }, animate ? 200 : 0);

        document.querySelectorAll('[id^="history-"]').forEach(el => {
            el.classList.remove('bg-green-50', 'border-green-500');
            el.classList.add('border', 'border-slate-200', 'hover:border-green-400', 'hover:bg-green-50/50');
        });
        const activeEl = document.getElementById('history-' + id);
        activeEl.classList.remove('border', 'border-slate-200', 'hover:border-green-400', 'hover:bg-green-50/50');
        activeEl.classList.add('bg-green-50', 'border-green-500');

        // Populate export inputs
        const restockCreatedSpan = templateEl.querySelector('span.text-xs.font-medium.bg-green-100');
        const rows = templateEl.querySelectorAll('tbody tr');
        const items = [];

        rows.forEach(row => {
            if (row.children.length >= 4) {
                items.push({
                    name: row.children[0].innerText,
                    quantity: row.children[1].innerText,
                    cost_price: row.children[2].innerText,
                    subtotal: row.children[3].innerText
                });
            }
        });

        document.getElementById('exportRestockId').value = id;
        document.getElementById('exportRestockCreated').value = restockCreatedSpan ? restockCreatedSpan.innerText : '';
        document.getElementById('exportRestockItems').value = JSON.stringify(items);
    }
</script>
@endsection