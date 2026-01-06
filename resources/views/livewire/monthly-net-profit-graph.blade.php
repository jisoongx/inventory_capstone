<div>
    <div wire:poll.10s="monthlyNetProfit" wire:keep-alive class="hidden"></div>
        
    <p class="text-left text-black font-semibold text-xs border-b border-gray-200 pb-5">Monthly Net Profit</p>

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between pt-4 gap-4 lg:gap-6">

        <div class="flex flex-col">
            <span class="text-xl font-bold text-gray-900">
                {{ $dateDisplay->format('F Y') }}
            </span>
            <p class="text-xs text-gray-600">
                {{ $dateDisplay->format('D, d') }}
            </p>
        </div>

        <div class="flex flex-col text-left lg:text-right">
            <span class="text-xl font-bold text-gray-900">
                ₱{{ number_format($profitMonth, 2) }}
            </span>
            <p class="text-xs text-gray-600">Current Net Profit</p>
        </div>

        <div class="flex items-center justify-start lg:justify-end gap-3 flex-wrap lg:flex-1">
            <a href="{{ route('dashboards.owner.expense_record') }}"
            class="bg-red-50 hover:bg-red-100 border border-red-200 hover:border-red-300 px-6 py-2.5 rounded text-xs text-center text-red-700 font-medium transition-colors duration-150">
                Expenses
            </a>

            <select wire:model.live="selectedYear" wire:change="monthlyNetProfit" id="year"
                class="rounded px-6 py-2.5 border-gray-300 text-gray-700 text-xs focus:ring-2 focus:ring-blue-200 focus:border-blue-400 bg-white hover:border-gray-400 transition-colors duration-150">
                @forelse ($year as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @empty
                    <option value="{{ now()->year }}">{{ now()->year }}</option>
                @endforelse
            </select>
        </div>
    </div>

    <div x-data="{ 
            updating: false,
            zoomIn() { 
                if (window.profitChartInstance) {
                    window.profitChartInstance.zoom(1.1);
                }
            },
            zoomOut() { 
                if (window.profitChartInstance) {
                    window.profitChartInstance.zoom(0.9);
                }
            },
            resetZoom() { 
                if (window.profitChartInstance) {
                    window.profitChartInstance.resetZoom();
                }
            }
        }">
        <div class="flex mt-4">
            <button @click="zoomIn()" id="zoomIn" title="Zoom In" 
                class="p-1 hover:bg-gray-100 rounded transition-colors duration-150">
                <span class="material-symbols-rounded-small text-sm text-gray-700">add_circle</span>
            </button>
            <button @click="zoomOut()" id="zoomOut" title="Zoom Out"
                class="p-1 hover:bg-gray-100 rounded transition-colors duration-150"> 
                <span class="material-symbols-rounded-small text-sm text-gray-700">do_not_disturb_on</span>
            </button>
            <button @click="resetZoom()" id="zoomReset" title="Reset"
                class="p-1 hover:bg-gray-100 rounded transition-colors duration-150">
                <span class="material-symbols-rounded-small text-sm text-gray-700">reset_settings</span>
            </button>
        </div>

        <div class="w-full overflow-x-auto mt-3 scrollbar-custom">
            <div 
                id="profitChart" 
                x-init="initProfitChart()"
                
                x-on:livewire-processing.self="updating = true"
                x-on:livewire-processed.self="initProfitChart(); updating = false"
                
                data-profits='@json($profits ?? [])'
                data-months='@json($months ?? [])'
                
                :class="{'opacity-0 transition-opacity duration-150': updating}"
                class="relative w-full h-[23rem]">
                <canvas></canvas>
            </div>
        </div>
    </div>
    
</div>