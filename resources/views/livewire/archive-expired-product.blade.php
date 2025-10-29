<div>
    <button wire:click="archived('{{ $batchNumber }}')"  class="hover:scale-110 transition-transform">
        <span class="material-symbols-rounded">archive</span>
    </button>

    @if ($toastShow)
        <div 
            x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 2500)" 
            x-show="show"
            x-transition
            class="fixed top-4 right-4 bg-green-700 text-white text-xs rounded-md px-4 py-2 shadow-md z-50"
        >
            {{ $toastMessage }}
        </div>
    @endif
</div>
