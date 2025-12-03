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

    .status-indicator {
        font-weight: bold;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-transform: capitalize;
    }

    .status-resolved {
        background-color: #16a34a;
        /* Green */
        color: white;
    }

    .status-cancelled {
        background-color: #dc2626;
        /* Red */
        color: white;
    }

    .status-pending {
        background-color: #facc15;
        /* Yellow */
        color: black;
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
                    <div class="flex items-center gap-2 mt-4 sm:mt-0">
                        @if($restocks->count())
                        @php
                        $latest = $restocks->first();
                        $latestItems = $restockItems->where('restock_id', $latest->restock_id);
                        $disableCancel = false;

                        if($latest->status === 'resolved') {
                        $disableCancel = true;
                        } else {
                        foreach($latestItems as $item){
                        if(in_array($item->item_status, ['complete','in_progress'])){
                        $disableCancel = true;
                        break;
                        }
                        }
                        }
                        @endphp

                        <form id="statusForm" method="POST" action="{{ route('restock.updateStatus') }}">
                            @csrf
                            <input type="hidden" name="restock_id" id="statusRestockId" value="{{ $latest->restock_id ?? '' }}">

                            <button name="status" value="cancelled"
                                class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white bg-red-500 border border-red-600 rounded-md hover:bg-red-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                @if($disableCancel) disabled @endif>
                                <span class="material-symbols-rounded text-sm">cancel</span>
                                Cancel
                            </button>
                        </form>

                        @endif
                    </div>

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

                    <!-- Status indicator -->
                    <span class="status-indicator
        @if($latest->status == 'resolved') 
            status-resolved
        @elseif($latest->status == 'cancelled') 
            status-cancelled
        @else 
            status-pending 
        @endif">
                        {{ ucfirst($latest->status) }}
                    </span>
                </div>


                <div class="overflow-x-auto overflow-y-auto max-h-[48vh] no-scrollbar border border-slate-200 rounded-lg">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 text-slate-600 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Product</th>
                                <th class="px-4 py-3 text-center font-semibold w-32">Quantity</th>
                                <th class="px-4 py-3 text-center font-semibold w-32">Status</th>
                                <th class="px-4 py-3 text-center font-semibold w-32">Restock Date</th>
                                <th class="px-4 py-3 text-center font-semibold w-32">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestItems as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->name }}</td>
                                <td class="px-4 py-3 text-center">{{ $item->item_quantity }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($item->item_status == 'complete')
                                    <span class="px-2 py-1 rounded bg-green-600 text-white text-xs">{{ ucfirst($item->item_status) }}</span>
                                    @elseif($item->item_status == 'in_progress')
                                    <span class="px-2 py-1 rounded bg-yellow-400 text-black text-xs">{{ ucfirst($item->item_status) }}</span>
                                    @else
                                    <span class="px-2 py-1 rounded bg-gray-300 text-black text-xs">{{ ucfirst($item->item_status) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">{{ $item->item_restock_date ? \Carbon\Carbon::parse($item->item_restock_date)->format('M d, Y') : '-' }}</td>
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

        <!-- History Sidebar -->
        <div class="w-full lg:w-96 bg-white shadow-md min-h-[560px] rounded p-6 sm:p-8 self-start border-t-4 border-green-300">
            <h2 class="text-md font-semibold text-green-800 mb-1">History</h2>
            <p class="text-sm text-slate-500 mb-6 text-sm">Select a past list to view its details.</p>
            <div class="flex gap-2 mb-4">
                <button class="filter-btn px-3 py-1 rounded text-xs font-medium border border-slate-300 hover:bg-green-50" data-status="all">All</button>
                <button class="filter-btn px-3 py-1 rounded text-xs font-medium border border-yellow-400 hover:bg-yellow-50" data-status="pending">Pending</button>
                <button class="filter-btn px-3 py-1 rounded text-xs font-medium border border-green-400 hover:bg-green-50" data-status="resolved">Resolved</button>
                <button class="filter-btn px-3 py-1 rounded text-xs font-medium border border-red-400 hover:bg-red-50" data-status="cancelled">Cancelled</button>
            </div>

            @if($restocks->count())
            <ul class="space-y-3 max-h-[60vh] no-scrollbar overflow-y-auto pr-2">
                @foreach($restocks as $index => $restock)
                @php
                $items = $restockItems->where('restock_id', $restock->restock_id);
                @endphp
                <li id="history-{{ $restock->restock_id }}" data-status="{{ $restock->status }}"
                    class="p-4 rounded-lg cursor-pointer transition-all duration-200 @if($index === 0) bg-green-50 border-green-500 @else border border-slate-200 hover:border-green-400 hover:bg-green-50/50 @endif"
                    onclick="showRestock('{{ $restock->restock_id }}')">

                    @php
                    $items = $restockItems->where('restock_id', $restock->restock_id);
                    @endphp

                    <!-- History info -->
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

                    <!-- Hidden template for main content -->
                    <div class="hidden" id="restock-{{ $restock->restock_id }}">
                        <div class="flex items-center justify-between mb-4">
                            <!-- Date -->
                            <span class="text-xs font-medium bg-green-100 text-green-700 px-2.5 py-1 rounded-full">
                                {{ \Carbon\Carbon::parse($restock->restock_created)->format('F d, Y • h:i A') }}
                            </span>

                            <!-- Modern Status Indicator (Tailwind only) -->
                            <span class="inline-flex items-center text-xs font-medium px-3 py-1 rounded-full shadow-sm
                @if($restock->status == 'resolved') bg-green-600 text-white
                @elseif($restock->status == 'cancelled') bg-red-600 text-white
                @else bg-yellow-400 text-gray-800 @endif">
                                <span class="w-2 h-2 rounded-full mr-2
                    @if($restock->status == 'resolved') bg-white
                    @elseif($restock->status == 'cancelled') bg-white
                    @else bg-gray-800 @endif"></span>
                                {{ ucfirst($restock->status) }}
                            </span>
                        </div>

                        <!-- Restock items table -->
                        <div class="overflow-x-auto overflow-y-auto max-h-[48vh] border border-slate-200 rounded-lg custom-scrollbar">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-100 text-slate-600 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold">Product</th>
                                        <th class="px-4 py-3 text-center font-semibold w-32">Quantity</th>
                                        <th class="px-4 py-3 text-center font-semibold w-32">Cost Price</th>
                                        <th class="px-4 py-3 text-center font-semibold w-32">Subtotal</th>
                                        <th class="px-4 py-3 text-center font-semibold w-32">Status</th>
                                        <th class="px-4 py-3 text-center font-semibold w-32">Restock Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($items as $item)
                                    <tr class="hover:bg-green-50/50 transition-colors">
                                        <td class="px-4 py-3 text-slate-800 font-medium">{{ $item->name }}</td>
                                        <td class="px-4 py-3 text-center font-mono text-slate-700">{{ $item->item_quantity }}</td>
                                        <td class="px-4 py-3 text-center">{{ number_format($item->cost_price, 2) }}</td>
                                        <td class="px-4 py-3 text-center">{{ number_format($item->subtotal, 2) }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if($item->item_status == 'complete')
                                            <span class="px-2 py-1 rounded bg-green-600 text-white text-xs">{{ ucfirst($item->item_status) }}</span>
                                            @elseif($item->item_status == 'in_progress')
                                            <span class="px-2 py-1 rounded bg-yellow-400 text-black text-xs">{{ ucfirst($item->item_status) }}</span>
                                            @else
                                            <span class="px-2 py-1 rounded bg-gray-300 text-black text-xs">{{ ucfirst($item->item_status) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">{{ $item->item_restock_date ? \Carbon\Carbon::parse($item->item_restock_date)->format('M d, Y') : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="border-t-4 border-slate-100 font-bold text-indigo-500">
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
    });

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
        document.getElementById('statusRestockId').value = id;

        activeEl.classList.remove('border', 'border-slate-200', 'hover:border-green-400', 'hover:bg-green-50/50');
        activeEl.classList.add('bg-green-50', 'border-green-500');

        
    }

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('bg-gray-100'));
            this.classList.add('bg-gray-100');

            const status = this.dataset.status;
            document.querySelectorAll('[id^="history-"]').forEach(item => {
                if (status === 'all' || item.dataset.status === status) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });


    document.getElementById("exportBtn").addEventListener("click", function(e) {
        e.preventDefault();

        let items = [];

        // Select all rows of the currently displayed restock table
        document.querySelectorAll("#restockContent table tbody tr").forEach(row => {
            let cols = row.querySelectorAll("td");

            items.push({
                name: cols[0].innerText.trim(),
                quantity: cols[1].innerText.trim(),
                cost_price: cols[2].innerText.trim(),
                subtotal: cols[3].innerText.trim(),
                item_status: cols[4].innerText.trim(),
                item_restock_date: cols[5].innerText.trim(),
            });
        });

        // Set hidden input values
        document.getElementById("exportRestockItems").value = JSON.stringify(items);

        // Also pass the restock created date
        let dateChip = document.querySelector("#restockContent span.text-xs.bg-green-100");
        if (dateChip) {
            document.getElementById("exportRestockCreated").value = dateChip.innerText.trim();
        }

        // Submit the form
        document.getElementById("exportForm").submit();
    });



    document.getElementById("printBtn").addEventListener("click", function() {
        const dateEl = document.querySelector("#restockContent span.text-xs.bg-green-100");
        const restockDate = dateEl ? dateEl.innerText.trim() : "";

        let items = [];
        document.querySelectorAll("#restockContent table tbody tr").forEach(row => {
            let cols = row.querySelectorAll("td");
            items.push({
                name: cols[0].innerText.trim(),
                quantity: cols[1].innerText.trim(),
                status: cols[4].innerText.trim(),
                restockDate: cols[5].innerText.trim(),
                cost: cols[2].innerText.trim(),
                subtotal: cols[3].innerText.trim(),
            });
        });

        let itemsRows = "";
        let grandTotal = 0;
        items.forEach(item => {
            grandTotal += parseFloat(item.subtotal.replace(/,/g, '')) || 0;
            itemsRows += `
        <div class="item-row">
            <div class="item-name">${item.name}</div>
            <div class="item-details">Qty: ${item.quantity}</div>
            <div class="item-details">Cost: ${item.cost}</div>
            <div class="item-details">Status: ${item.status}</div>
            <div class="item-details">Restock Date: ${item.restockDate}</div>
            <div class="item-price-row">
                <span>Subtotal</span>
                <span><strong>${item.subtotal}</strong></span>
            </div>
        </div>
        `;
        });

        const content = `
    <div class="header-info">
        <p><strong>Date:</strong> ${restockDate}</p>
    </div>

    <div class="items-section">
        ${itemsRows}
    </div>

    <div class="total-section">
        <div class="total-row">
            <span>GRAND TOTAL</span>
            <span>${grandTotal.toFixed(2)}</span>
        </div>
    </div>
    `;

        // Create hidden iframe
        const iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);

        const doc = iframe.contentDocument || iframe.contentWindow.document;
        doc.open();
        doc.write(`
        <html>
        <head>
            <title>Print Restock</title>
            <style>
                @page {
                    size: 58mm auto; /* Thermal printer width */
                    margin: 0;
                }
                body {
                    font-family: monospace;
                    width: 58mm;
                    margin: 0;
                    padding: 5px;
                    font-size: 14px;
                    line-height: 1.3;
                }
                h2 {
                    text-align: center;
                    font-size: 14px;
                    margin: 0 0 8px 0;
                    text-transform: uppercase;
                }
                .header-info {
                    margin-bottom: 8px;
                    border-bottom: 2px solid #000;
                }
                .header-info p {
                    margin: 2px 0;
                }
                .item-row {
                    border-bottom: 2px solid #ccc;
                    padding: 4px 0;
                }
                .item-name {
                    font-weight: bold;
                    font-size: 14px;
                    margin-bottom: 2px;
                }
                .item-details {
                    font-size: 14px;
                    margin: 1px 0;
                }
                .item-price-row {
                    display: flex;
                    justify-content: space-between;
                    margin-top: 2px;
                }
                .total-section {
                    margin-top: 6px;
                    border-top: 2px solid #000;
                    padding-top: 3px;
                }
                .total-row {
                    display: flex;
                    justify-content: space-between;
                    font-weight: bold;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <h2>Restock List</h2>
            ${content}
        </body>
        </html>
    `);
        doc.close();

        // Print the iframe content
        iframe.contentWindow.focus();
        iframe.contentWindow.print();

        // Remove iframe after printing
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 500);
    });
</script>




@endsection