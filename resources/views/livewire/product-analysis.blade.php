<div class="flex-grow">

    <a href="" class="flex items-center mb-4 border-b pb-4 w-full">
        <div class="group flex items-center font-semibold text-xs space-x-1.5 rounded px-2 py-1 cursor-pointer transition-all duration-300 hover:bg-gray-100 hover:scale-[1.02]">
            <span>
                {{ date('F', mktime(0, 0, 0, $currentMonth, 1)) }} Product Performance Analysis
            </span>
            <span class="material-symbols-rounded text-base transition-transform duration-300 group-hover:translate-x-1">
                arrow_right_alt
            </span>
        </div>
    </a>

    <div class="flex mb-4 justify-between">
        <div class="relative text-gray-400">
            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                <span class="material-symbols-rounded">search</span>
            </span>
            <input type="text"
                   wire:model.live.debounce.1ms="searchWord"
                   placeholder="Search..."
                   class="rounded-full border border-gray-500 p-3 pl-10 text-xs focus:ring focus:ring-blue-200 text-black">
        </div>

        <div>
            <select wire:model.live="currentMonth" id="monthFilter" class="border rounded p-3 text-xs">
                @foreach ($monthNames as $index => $name)
                    <option value="{{ $index + 1 }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <table id="analysis-table" class="w-full text-xs text-left text-gray-700 border border-gray-200 rounded-lg overflow-hidden shadow-sm">
            <thead class="uppercase text-xs font-semibold bg-gray-100 text-gray-600">
                <tr>
                    <th class="cursor-pointer px-4 py-4 text-left" wire:click="sortBy('product_name')">Product ↓☰↑</th>
                    <th class="px-4 py-4">Category</th>
                    <th class="cursor-pointer px-4 py-4" wire:click="sortBy('unit_sold')">Unit Sold ↓☰↑</th>
                    <th class="cursor-pointer px-4 py-4" wire:click="sortBy('total_sales')">Total Sales ↓☰↑</th>
                    <th class="cursor-pointer px-4 py-4" wire:click="sortBy('cogs')">COGS ↓☰↑</th>
                    <th class="cursor-pointer px-4 py-4" wire:click="sortBy('profit')">Profit ↓☰↑</th>
                    <th class="cursor-pointer px-4 py-4" wire:click="sortBy('profit_margin_percent')">% Profit Margin ↓☰↑</th>
                    <th class="cursor-pointer px-4 py-4" wire:click="sortBy('contribution_percent')">% Share ↓☰↑</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($analysisPage as $analy)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4">{{ $analy->product_name }}</td>
                        <td class="py-3 px-4">{{ $analy->category }}</td>
                        <td class="py-3 px-4">{{ $analy->unit_sold }}</td>
                        <td class="py-3 px-4">₱{{ number_format($analy->total_sales, 2) }}</td>
                        <td class="py-3 px-4">₱{{ number_format($analy->cogs, 2) }}</td>
                        <td class="py-3 px-4">₱{{ number_format($analy->profit) }}</td>
                        <td class="py-3 px-4">{{ number_format($analy->profit_margin_percent, 0) }}%</td>
                        <td class="py-3 px-4">{{ number_format($analy->contribution_percent, 0) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="flex flex-col justify-center items-center space-y-1 p-8">
                                <span class="material-symbols-rounded-semibig text-gray-400">taunt</span>
                                <span class="text-gray-400">Nothing to show.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $analysisPage->links('vendor.livewire.tailwind', ['scrollTo' => '#analysis-table']) }}
        </div>
    </div>
</div>
