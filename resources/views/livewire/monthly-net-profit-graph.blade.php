<div>
    <div wire:poll.15s="monthlyNetProfit" wire:keep-alive class="hidden"></div>
        
    <p class="text-left text-black font-semibold text-xs border-b border-gray-200 pb-5">Monthly Net Profit</p>

    <div class="flex items-center justify-between pt-4 gap-6">

        <div class="flex flex-col">
            <span class="text-xl font-bold">
                {{ $dateDisplay->format('F Y') }}
            </span>
            <p class="text-xs">
                {{ $dateDisplay->format('D, d') }}
            </p>
        </div>

        <div class="flex flex-col text-right">
            @if (is_null($profitMonth) || $profitMonth === 0)
                <span class="text-xl text-red-700">
                    Empty database.
                </span>
            @else
                <span class="text-xl font-bold">
                    ₱{{ number_format($profitMonth, 2) }}
                </span>
            @endif
            <p class="text-xs">Current Net Profit</p>
        </div>

        <div class="flex-1 flex items-center justify-end gap-3">
            <a href="{{ route('dashboards.owner.expense_record') }}"
            class="bg-red-100 border border-red-900 px-6 py-2.5 rounded text-xs text-center">
                View
            </a>

            <select wire:model.live="selectedYear" wire:change="monthlyNetProfit" id="year"
                class="rounded px-6 py-2.5 border-gray-300 text-gray-700 text-xs focus:ring focus:ring-blue-200 focus:border-blue-400">
                @forelse ($year as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @empty
                    <option value="{{ now()->year }}">{{ now()->year }}</option>
                @endforelse
            </select>
        </div>
    </div>

    <div class="flex space-x-1 mt-2">
        <button onclick="zoomIn()" id="zoomIn" title="Zoom In">
            <span class="material-symbols-rounded-small text-sm" title="Zoom In">add_circle</span>
        </button>
        <button onclick="zoomOut()" id="zoomOut" title="Zoom Out"> 
            <span class="material-symbols-rounded-small text-sm" title="Zoom Out">do_not_disturb_on</span>
        </button>
        <button onclick="resetZoom()" id="zoomReset" title="Reset">
            <span class="material-symbols-rounded-small text-sm" title="Reset">reset_settings</span>
        </button>
    </div>

    <div class="w-full overflow-x-auto mt-3 scrollbar-custom">
        <div 
            id="profitChart" 
            x-data="{ updating: false }" 
            x-init="initProfitChart()"
            
            x-on:livewire-processing.self="updating = true"
            x-on:livewire-processed.self="initProfitChart(); updating = false"
            
            data-profits='@json($profits ?? [])'
            data-months='@json($months ?? [])'
            
            :class="{'opacity-0 transition-opacity duration-150': updating}"
            class="relative w-full h-[24rem]">
            <canvas></canvas>
        </div>
    </div>
    
</div>
