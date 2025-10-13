<div x-data="{ tab: 'basket' }" class="w-full px-4">

    <div class="flex space-x-1">
        <button 
            @click="tab = 'basket'"
            :class="tab === 'basket' 
                ? 'bg-green-50 text-black border-green-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Basket Analysis
        </button>

        <button 
            @click="tab = 'frequency'"
            :class="tab === 'frequency' 
                ? 'bg-red-50 text-black border-red-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Purchase Frequency
        </button>
        
    </div>

    <div class="border bg-white p-4 rounded-b-lg mb-3 h-[40rem]"
        :class="{
            'border-green-500 bg-green-50': tab === 'basket',
            'border-red-500 bg-red-50': tab === 'frequency'
        }">


        <!-- BASKET ANALYSIS -->
        <div x-show="tab === 'basket'">
            <p class="text-gray-700">📊 <b> Basket Analysis</b> report content goes here.</p>
        </div>

        <!-- PURCHASE FREQUENCY -->
        <div x-show="tab === 'frequency'">
            <p class="text-gray-700">⚡ <b>Purchase Frequency</b> report content goes here.</p>
        </div>
    </div>
</div>

