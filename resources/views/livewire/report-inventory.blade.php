<div x-data="{ tab: 'top-selling' }" class="w-full px-4">

    <div class="flex space-x-1">
        <button 
            @click="tab = 'top-selling'"
            :class="tab === 'top-selling' 
                ? 'bg-yellow-50 text-black border-yellow-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Top Selling Product
        </button>

        <button 
            @click="tab = 'expiring'"
            :class="tab === 'expiring' 
                ? 'bg-green-50 text-black border-green-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Expiring Poducts
        </button>

        <button 
            @click="tab = 'loss'"
            :class="tab === 'loss' 
                ? 'bg-red-50 text-black border-red-500 border-t border-l border-r rounded-t-lg' 
                : 'bg-gray-200 text-gray-600 hover:text-black rounded-t-lg'"
            class="px-6 py-3 font-medium text-xs">
            Loss Report
        </button>
        
    </div>

    <div class="border bg-white p-4 rounded-b-lg mb-3 h-[41rem]"
        :class="{
            'border-green-500 bg-green-50': tab === 'expiring',
            'border-yellow-500 bg-yellow-50': tab === 'top-selling',
            'border-red-500 bg-red-50': tab === 'loss'
        }">

        <!-- TOP SELLING -->
        <div x-show="tab === 'top-selling'">
            <p class="text-gray-700">⚡ <b>top selling</b> report content goes here.</p>
        </div>

        <!-- EXPIRING PRODUCTS -->
        <div x-show="tab === 'expiring'">
            <p class="text-gray-700">📊 <b>Expiring</b> report content goes here.</p>
        </div>

        <!-- EXPIRED PRODUCTS / DAMAGED/ LOSS -->
        <div x-show="tab === 'loss'">
            <p class="text-gray-700">⚡ <b>Loss</b> report content goes here.</p>
        </div>
    </div>
</div>

