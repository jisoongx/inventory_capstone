<div>
    <!-- Trigger Button -->
    <button id="damageBtn" {{ $expired ? 'disabled' : '' }}
        wire:click="$toggle('showModal')"
        class="flex items-center gap-1.5 bg-red-500 text-white border-2 border-red-500 px-4 py-2 rounded-lg hover:bg-red-600 transition-all duration-200 transform hover:scale-105
                {{ $expired ? 'opacity-50 cursor-not-allowed' : '' }}"
        title="Report Damaged Items">
        <span class="material-symbols-outlined text-lg">report</span>
        <span class="font-medium text-sm">Damage</span>
    </button>


    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:click.self="closeModal()">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>
            <div class="flex items-center justify-center min-h-screen px-4 py-6">
                
                <div class="relative bg-white rounded-lg shadow-2xl max-w-6xl w-full transform transition-all" @click.stop>

                    <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4 rounded-t-lg">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Add Damage Records
                            </h3>
                            <button wire:click="closeModal()" type="button"
                                class="text-white hover:text-gray-200 transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <div class="px-6 py-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                            
                            <div class="grid grid-cols-[40px_1fr_1fr_120px_120px_150px_40px] gap-3 mb-3 px-3 text-xs font-semibold text-gray-700 uppercase tracking-wide">
                                <div>#</div>
                                <div>Product <span class="text-red-500">*</span></div>
                                <div>Batch # - Expiration Date <span class="text-red-500">*</span></div>
                                <div>Quantity <span class="text-red-500">*</span></div>
                                <div>Type <span class="text-red-500">*</span></div>
                                <div>Reason <span class="text-red-500">*</span></div>
                                <div></div>
                            </div>

                            <div class="space-y-2">
                                @foreach ($damageRecords as $index => $record)
                                <div class="grid grid-cols-[40px_1fr_1fr_120px_120px_150px_40px] gap-3 items-center bg-gray-50 p-2 rounded-lg border border-gray-200 hover:border-gray-300 transition-colors">
                                    
                                    <div class="flex items-center justify-center">
                                        <span class="bg-red-500 text-white w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold">
                                            {{ $index + 1 }}
                                        </span>
                                    </div>

                                    <div>
                                        <select wire:model="damageRecords.{{ $index }}.prod_code" 
                                                wire:change="getInventory({{ $index }}, $event.target.value)"
                                                required 
                                                class="form-select text-xs w-full border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                            <option value="">Select Product</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->prod_code }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        @error("damageRecords.{$index}.prod_code") 
                                            <span class="text-red-500 text-[10px] mt-0.5 block">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <select wire:model="damageRecords.{{ $index }}.inven_code" 
                                                required 
                                                class="form-select text-xs w-full border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                            <option value="">Select Inventory</option>
                                            @if(isset($inventories[$index]) && count($inventories[$index]) > 0)
                                                @foreach ($inventories[$index] as $row)
                                                    <option value="{{ $row->inven_code }}">
                                                        {{ $row->batch_number }} • {{ \Carbon\Carbon::parse($row->expiration_date)->format('M d, Y') }}
                                                    </option>
                                                @endforeach
                                            @else
                                                <option value="" disabled>Choose Product first</option>
                                            @endif
                                        </select>
                                        @error("damageRecords.{$index}.inven_code") 
                                            <span class="text-red-500 text-[10px] mt-0.5 block">{{ $message }}</span> 
                                        @enderror
                                    </div>

                                    <!-- Quantity -->
                                    <div>
                                        <input type="number" 
                                            wire:model="damageRecords.{{ $index }}.damaged_quantity" 
                                            required 
                                            min="1" 
                                            placeholder="0"
                                            class="form-input text-xs w-full border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                        @error("damageRecords.{$index}.damaged_quantity") 
                                            <span class="text-red-500 text-[10px] mt-0.5 block">{{ $message }}</span> 
                                        @enderror
                                    </div>

                                    <!-- Type -->
                                    <div>
                                        <select wire:model="damageRecords.{{ $index }}.damaged_type" 
                                                required 
                                                class="form-select text-xs w-full border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                            <option value="">Type</option>
                                            <option value="Expired">Expired</option>
                                            <option value="Broken">Broken</option>
                                            <option value="Spoiled">Spoiled</option>
                                        </select>
                                        @error("damageRecords.{$index}.damaged_type") 
                                            <span class="text-red-500 text-[10px] mt-0.5 block">{{ $message }}</span> 
                                        @enderror
                                    </div>

                                    <!-- Reason -->
                                    <div>
                                        <input type="text" 
                                            wire:model="damageRecords.{{ $index }}.damaged_reason" 
                                            required 
                                            placeholder="Reason"
                                            class="form-input text-xs w-full border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                        @error("damageRecords.{$index}.damaged_reason") 
                                            <span class="text-red-500 text-[10px] mt-0.5 block">{{ $message }}</span> 
                                        @enderror
                                    </div>

                                    <!-- Remove Button -->
                                    <div class="flex items-center justify-center">
                                        @if(count($damageRecords) > 1)
                                        <button type="button" 
                                                wire:click="removeRecord({{ $index }})"
                                                class="text-red-500 hover:text-red-700 hover:bg-red-100 p-1 rounded transition-all"
                                                title="Remove row">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                        @endif
                                    </div>

                                </div>
                                @endforeach
                            </div>

                            <!-- Add Row Button -->
                            <div class="mt-3 flex justify-end">
                                <button type="button" 
                                        wire:click="addRecord"
                                        class="px-3 py-1.5 bg-green-500 text-white text-xs rounded hover:bg-green-600 transition-all flex items-center gap-1.5 font-medium">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Add Row
                                </button>
                            </div>

                        </div>

                        <!-- Footer -->
                        <div class="bg-gray-50 px-6 py-3 flex justify-between items-center rounded-b-lg border-t border-gray-200">
                            <div class="text-xs text-gray-600">
                                Total: <span class="font-bold text-red-600">{{ count($damageRecords) }}</span> record(s)
                            </div>
                            <div class="flex gap-2">
                                @if (session()->has('success'))
                                    <div 
                                        x-data="{ show: true }" 
                                        x-show="show" 
                                        x-init="setTimeout(() => show = false, 5000)" 
                                        class="p-2 bg-green-100 border border-green-400 text-green-700 rounded animate-pulse transition-opacity duration-500"
                                    >
                                        <span class="font-bold text-xs">{{ session('success') }}</span>
                                    </div>
                                @endif
                                <button type="button" 
                                        wire:click="closeModal()"
                                        class="px-4 py-1.5 bg-white border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition-all text-xs font-medium">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        wire:click="saveDamageRecords()"
                                        class="px-4 py-1.5 bg-red-500 text-white rounded hover:bg-red-600 transition-all text-xs font-medium">
                                    Save All
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>