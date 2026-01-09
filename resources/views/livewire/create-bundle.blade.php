<div class="">

    <!-- Header -->
    <div>
        <div class="bg-white p-3">
            <h1 class="text-sm font-semibold text-gray-900">
                Create Product Bundle
            </h1>
            <p class="mt-1 text-xs text-gray-600">
                Select products, set quantities, and configure bundle settings
            </p>

            <!-- Tabs -->
            <div class="mt-2 border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button 
                        wire:click="setActiveTab('generate')"
                        class="py-2 px-1 border-b-2 font-medium text-xs transition-colors
                            {{ $activeTab === 'generate' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Generate Bundle
                    </button>
                    <button 
                        wire:click="setActiveTab('suggestion')"
                        class="py-2 px-1 border-b-2 font-medium text-xs transition-colors
                            {{ $activeTab === 'suggestion' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        List of Bundle Promotions
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="mt-3">
                @if($activeTab === 'generate')
                    <div class="text-gray-700">
                        <div class="grid grid-cols-2 gap-3">

                            <!-- Bundle Settings Sidebar -->
                            <div class="lg:col-span-1">
                                <div class="bg-white border border-gray-200 rounded-xl shadow-sm sticky top-8 overflow-hidden">
                                    <!-- Header -->
                                    <div class="relative px-3 py-2 border-b border-gray-200">
                                        <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="w-1 h-3.5 bg-gray-900 rounded-full"></div>
                                                <h2 class="text-xs font-black text-gray-900 uppercase tracking-wide">
                                                    Bundle Settings
                                                </h2>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-3 py-2.5 space-y-3">
                                        <!-- first setting group -->
                                        <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100">
                                            <div class="flex items-center gap-1.5 mb-2">
                                                <div class="w-4 h-4 rounded bg-gray-900 flex items-center justify-center">
                                                    <span class="text-[9px] font-black text-white">R</span>
                                                </div>
                                                <h3 class="text-[10px] font-black text-gray-900 uppercase tracking-wider">Reason / Objective</h3>
                                            </div>

                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="text-[10px] font-bold text-gray-900 mb-0.5">Objective <span class="text-red-500">*</span></label>
                                                    <select wire:model="bundleObjective" class="w-full px-2 py-1 border border-gray-300 rounded text-[11px] bg-white">
                                                        <option value="">Select objective</option>
                                                        <option value="CROSS_SELL">Cross-sell / Upsell</option>
                                                        <option value="CLEARANCE">Clearance</option>
                                                        <option value="NEW_PRODUCT">Introduce new SKU</option>
                                                        <option value="INCREASE_AOV">Increase AOV</option>
                                                        <option value="MOVE_SLOW">Move slow inventory</option>
                                                        <option value="SEASONAL">Seasonal</option>
                                                        <option value="GWP">Gift-with-purchase</option>
                                                        <option value="PROMOTIONAL">General Promotion</option>
                                                    </select>
                                                    @error('bundleObjective')
                                                        <p class="flex items-center gap-0.5 text-[9px] font-semibold text-red-600 mt-0.5">
                                                            <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                            </svg>
                                                            {{ $message }}
                                                        </p>
                                                    @enderror
                                                </div>

                                                <div>
                                                    <label class="text-[10px] font-bold text-gray-900 mb-0.5">Priority  <span class="text-red-500">*</span></label>
                                                    <select wire:model="bundlePriority" class="w-full px-2 py-1 border border-gray-300 rounded text-[11px] bg-white">
                                                        <option value="">Select priority</option>
                                                        <option value="1">Need to clear ASAP / Expiration (Lv.1)</option>
                                                        <option value="2">Promotional / Hot Sale (Lv.2)</option>
                                                        <option value="3">Slow Moving Items (Lv.3)</option>
                                                        <option value="4">Paid / Regular Price (Lv.4)</option>
                                                        <option value="5">Free / Give-away (Lv.5)</option>
                                                    </select>
                                                    @error('bundlePriority')
                                                        <p class="flex items-center gap-0.5 text-[9px] font-semibold text-red-600 mt-0.5">
                                                            <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                            </svg>
                                                            {{ $message }}
                                                        </p>
                                                    @enderror
                                                </div>

                                                <div class="col-span-2">
                                                    <label class="text-[10px] font-bold text-gray-900 mb-0.5">Goal / Notes</label>
                                                    <textarea wire:model="bundleGoal" rows="2" class="w-full px-2 py-1 border border-gray-300 rounded text-[11px] bg-white" placeholder="What success looks like (e.g. sell 500 units in 14 days)"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- second SETTINGS GROUP -->
                                        <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100">
                                            <div class="flex items-center gap-1.5 mb-2">
                                                <div class="w-4 h-4 rounded bg-gray-900 flex items-center justify-center">
                                                    <span class="text-[9px] font-black text-white">2</span>
                                                </div>
                                                <h3 class="text-[10px] font-black text-gray-900 uppercase tracking-wider">
                                                    Basic Info
                                                </h3>
                                            </div>

                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="flex items-center gap-0.5 text-[10px] font-bold text-gray-900 mb-0.5">
                                                        Code
                                                        <span class="text-red-500">*</span>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        wire:model="bundleCode"
                                                        class="w-full px-2 py-1 border border-gray-300 rounded text-[11px] font-mono focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all bg-white"
                                                        placeholder="BUNDLE-001">
                                                    @error('bundleCode')
                                                        <p class="flex items-center gap-0.5 text-[9px] font-semibold text-red-600 mt-0.5">
                                                            <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                            </svg>
                                                            {{ $message }}
                                                        </p>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label class="flex items-center gap-0.5 text-[10px] font-bold text-gray-900 mb-0.5">
                                                        Name
                                                        <span class="text-red-500">*</span>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        wire:model="bundleName"
                                                        class="w-full px-2 py-1 border border-gray-300 rounded text-[11px] focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all bg-white"
                                                        placeholder="Bundle name">
                                                    @error('bundleName')
                                                        <p class="flex items-center gap-0.5 text-[9px] font-semibold text-red-600 mt-0.5">
                                                            <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                            </svg>
                                                            {{ $message }}
                                                        </p>
                                                    @enderror
                                                </div>

                                                <div>
                                                    <label class="flex items-center gap-0.5 text-[10px] font-bold text-gray-900 mb-0.5">
                                                        Bundle Category
                                                        <span class="text-red-500">*</span>
                                                    </label>
                                                    <select
                                                        wire:model="bundleCategory"
                                                        class="w-full px-2 py-1 border border-gray-300 rounded text-[11px] focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all bg-white">
                                                        <option value="">Select</option>
                                                        <option value="HOLIDAY">Holiday</option>
                                                        <option value="THEME">Theme</option>
                                                        <option value="NEGOSYO">Negosyo</option>
                                                        <option value="PROMOTIONAL">Promotional</option>
                                                        <option value="SPECIALTY">Specialty</option>
                                                    </select>
                                                    @error('bundleCategory')
                                                        <p class="flex items-center gap-0.5 text-[9px] font-semibold text-red-600 mt-0.5">
                                                            <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                            </svg>
                                                            {{ $message }}
                                                        </p>
                                                    @enderror
                                                </div>

                                                <div>
                                                    <label class="flex items-center gap-0.5 text-[10px] font-bold text-gray-900 mb-0.5">
                                                        Bundle Type
                                                        <span class="text-red-500">*</span>
                                                    </label>
                                                    <select
                                                        wire:model.live="bundleType"
                                                        class="w-full px-2 py-1 border border-gray-300 rounded text-[11px] focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all bg-white">
                                                        <option value="">Select</option>
                                                        <option value="EXPIRY">Expiry</option>
                                                        <option value="MULTI-BUY">Multi-Buy</option>
                                                        <option value="MIXED">Mixed</option>
                                                        <option value="BOGO1">Buy X Get Y (Discounted)</option>
                                                        <option value="BOGO2">Buy X Get Y (FREE)</option>
                                                    </select>
                                                    @error('bundleType')
                                                        <p class="flex items-center gap-0.5 text-[9px] font-semibold text-red-600 mt-0.5">
                                                            <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                            </svg>
                                                            {{ $message }}
                                                        </p>
                                                    @enderror
                                                </div>

                                                <div class="col-span-2">
                                                    <label class="flex items-center gap-0.5 text-[10px] font-bold text-gray-900 mb-0.5">
                                                        Status
                                                        <span class="text-red-500">*</span>
                                                    </label>
                                                    <div class="flex gap-1.5">
                                                        <button
                                                            wire:click="$set('status', 'active')"
                                                            class="flex-1 px-2 py-1 text-[10px] font-bold rounded border-2 transition-all {{ $status === 'active' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300' }}">
                                                            Active
                                                        </button>
                                                        <button
                                                            wire:click="$set('status', 'inactive')"
                                                            class="flex-1 px-2 py-1 text-[10px] font-bold rounded border-2 transition-all {{ $status === 'inactive' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300' }}">
                                                            Inactive
                                                        </button>
                                                    </div>
                                                    @error('status')
                                                        <p class="flex items-center gap-0.5 text-[9px] font-semibold text-red-600 mt-0.5">
                                                            <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                            </svg>
                                                            {{ $message }}
                                                        </p>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- third SETTINGS GROUP -->
                                        <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100">
                                            <div class="flex items-center gap-1.5 mb-2">
                                                <div class="w-4 h-4 rounded bg-gray-900 flex items-center justify-center">
                                                    <span class="text-[9px] font-black text-white">3</span>
                                                </div>
                                                <h3 class="text-[10px] font-black text-gray-900 uppercase tracking-wider">
                                                    Pricing & Duration
                                                </h3>
                                            </div>

                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="flex items-center gap-0.5 text-[10px] font-bold text-gray-900 mb-0.5">
                                                        Start Date
                                                    </label>
                                                    <input
                                                        type="date"
                                                        wire:model="startDate"
                                                        class="w-full px-2 py-1 border border-gray-300 rounded text-[11px] focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all bg-white">
                                                    @error('startDate')
                                                        <p class="flex items-center gap-0.5 text-[9px] font-semibold text-red-600 mt-0.5">
                                                            <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                            </svg>
                                                            {{ $message }}
                                                        </p>
                                                    @enderror
                                                </div>
                                                
                                                <div>
                                                    <label class="flex items-center gap-0.5 text-[10px] font-bold text-gray-900 mb-0.5">
                                                        End Date
                                                    </label>
                                                    <input
                                                        type="date"
                                                        wire:model="endDate"
                                                        class="w-full px-2 py-1 border border-gray-300 rounded text-[11px] focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all bg-white">
                                                    @error('endDate')
                                                        <p class="flex items-center gap-0.5 text-[9px] font-semibold text-red-600 mt-0.5">
                                                            <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                            </svg>
                                                            {{ $message }}
                                                        </p>
                                                    @enderror
                                                </div>

                                                <div>
                                                    <label class="flex items-center gap-0.5 text-[10px] font-bold text-gray-900 mb-0.5">
                                                        Minimum Profit (₱)
                                                    </label>
                                                    <div class="relative">
                                                        <span class="absolute left-2 top-1.5 text-gray-500 text-sm font-semibold">₱</span>
                                                        <input
                                                            type="number"
                                                            wire:model.live="minProfit"
                                                            class="w-full px-2 py-1 pl-6 border border-gray-300 rounded text-[11px] focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all bg-white"
                                                            placeholder="0.00"
                                                            step="0.01"
                                                            min="0">
                                                        
                                                        <p class="text-[9px] text-gray-500 leading-tight mt-1">
                                                            Lowest peso profit this bundle must earn.
                                                        </p>
                                                    </div>
                                                    @error('minProfit')
                                                        <p class="flex items-center gap-0.5 text-[9px] font-semibold text-red-600 mt-0.5">
                                                            <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                            </svg>
                                                            {{ $message }}
                                                        </p>
                                                    @enderror
                                                </div>

                                                <div>
                                                    <label class="flex items-center gap-0.5 text-[10px] font-bold text-gray-900 mb-0.5">
                                                        Discount Applied
                                                        <span class="text-red-500">*</span>
                                                    </label>

                                                    <div class="flex items-center gap-2">
                                                        <!-- Dropdown -->
                                                        <!-- <select wire:model.live="discountType"
                                                                class="text-[11px] border border-gray-300 rounded px-2 py-1 focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all">
                                                            <option value="predefined">Discount Options (%)</option>
                                                            <option value="fixed">Input Fixed Number (₱)</option>
                                                        </select> -->

                                                        <!-- Input -->
                                                        <div class="relative flex-1">
                                                             {{--@if($discountType === 'predefined') wire:model="discountValue"--}}
                                                                <input type="number" disabled
                                                                    value="{{ $selectedDiscount }}"
                                                                    class="w-full px-2 py-1 pr-6 border border-gray-300 rounded text-[11px] focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all bg-white"
                                                                    placeholder="00">
                                                                <span class="absolute right-2 top-1.5 text-gray-500 text-sm font-semibold">%</span>
                                                            {{--@else--}}
                                                                <!-- <input type="number"
                                                                    wire:model="discountValue"
                                                                    class="w-full px-2 py-1 pl-6 border border-gray-300 rounded text-[11px] focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all bg-white"
                                                                    placeholder="0.00">
                                                                <span class="absolute left-2 top-1.5 text-gray-500 text-sm font-semibold">₱</span> -->
                                                            {{--@endif--}}
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                    </div>

                                    <!-- Footer gradient -->
                                    <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <!-- choosing product for bundle -->
                                <div class="mb-4 col-span-1">
                                    <div class="flex items-center justify-between max-w-md mx-auto">
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 rounded-full {{ $step === 1 ? 'bg-red-600' : 'bg-gray-500' }} flex items-center justify-center transition-all">
                                                <span class="text-xs font-semibold text-white">1</span>
                                            </div>
                                            <span class="text-xs font-semibold {{ $step === 1 ? 'text-red-600' : 'text-gray-400' }}">Select Products</span>
                                        </div>
                                        
                                        <div class="flex-1 h-0.5 mx-3 {{ $step === 2 ? 'bg-red-600' : 'bg-gray-500' }} transition-all"></div>
                                        
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 rounded-full {{ $step === 2 ? 'bg-red-600' : 'bg-gray-500' }} flex items-center justify-center transition-all">
                                                <span class="text-xs font-semibold text-white">2</span>
                                            </div>
                                            <span class="text-xs font-semibold {{ $step === 2 ? 'text-red-600' : 'text-gray-400' }}">Review Bundle</span>
                                        </div>
                                    </div>
                                    <div class="relative flex items-center gap-2 mt-2">
                                        <div class="relative flex-1">
                                            <span class="absolute left-3 top-1.5 flex items-center pointer-events-none text-gray-400">
                                                <span class="material-symbols-rounded text-base">search</span>
                                            </span>
                                            <input 
                                                type="text"
                                                wire:model.live="searchWord"
                                                placeholder="Search Product Name..."
                                                class="w-full text-xs rounded-lg border border-gray-300 pl-9 pr-3 py-2 bg-white shadow-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                            >
                                        </div>
                                        
                                        @if(count($selectedProducts) > 0 and $step === 1)
                                            <button wire:click="$set('step', 2)" 
                                                class="flex-shrink-0 px-2 py-2 text-xs font-semibold transition-all hover:text-red-500 flex items-center justify-center gap-1.5 group">
                                                <span class="text-xs inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-200 group-hover:bg-red-100 transition-all font-bold">
                                                    {{ count($selectedProducts) }}
                                                </span>
                                                <span class="material-symbols-rounded-smaller">arrow_forward_ios</span>
                                            </button>
                                        @endif
                                    </div>
                                    <div class="lg:col-span-1 h-[475px] overflow-y-auto scrollbar-custom mt-1">
                                        @if($step === 1)
                                            <!-- STEP 1: Available Products List -->
                                            <div class="bg-white">
                                                <div class="space-y-1.5 p-1">
                                                    @foreach($products as $product)
                                                        <div class="group relative bg-white shadow-lg hover:shadow-xl hover:border-gray-300 transition-all duration-300 overflow-hidden">
                                                            <div class="absolute inset-0 bg-gradient-to-br from-gray-50/0 to-gray-100/0 group-hover:from-gray-50/50 group-hover:to-gray-100/30 transition-all duration-500"></div>
                                                            
                                                            <div class="relative px-3.5 py-2">
                                                                <div class="flex items-start gap-2.5">
                                                                    <!-- Checkbox dapit-->
                                                                    <div class="relative mt-0.5">
                                                                        @php
                                                                            $isChecked = collect($selectedProducts)->contains(function($data, $key) use ($product) {
                                                                                return $data['prod_code'] === $product->prod_code && isset($data['selected']) && $data['selected'];
                                                                            });
                                                                        @endphp

                                                                        <input type="checkbox" wire:key="product-{{ $product->prod_code }}"
                                                                            wire:click="toggleProduct({{ $product->prod_code }})"
                                                                            @checked($isChecked)
                                                                            class="peer w-4 h-4 appearance-none border-2 border-gray-300 rounded-md cursor-pointer transition-all duration-200 checked:bg-gray-900 checked:border-gray-900 hover:border-gray-400 focus:ring-2 focus:ring-gray-300 focus:ring-offset-1">
                                                                    </div>
                                                                    
                                                                    <div class="flex-1 min-w-0">
                                                                        <!-- Product Header -->
                                                                        <div class="flex items-start justify-between gap-3">
                                                                            <div class="flex-1 min-w-0">
                                                                                <div class="flex items-center gap-2 mb-1">
                                                                                    <div class="w-0.5 h-4 bg-gray-900 rounded-full"></div>
                                                                                    <h3 class="text-xs font-semibold text-gray-900 truncate group-hover:text-gray-700 transition-colors">
                                                                                        {{ $product->name }}
                                                                                    </h3>
                                                                                </div>
                                                                                <div class="flex items-center gap-1.5">
                                                                                    <div class="w-1 h-1 rounded-full {{ $product->total_stock > 0 ? 'bg-gray-900' : 'bg-gray-400' }}"></div>
                                                                                    <div class="text-xs font-black {{ $product->total_stock > 0 ? 'text-gray-900' : 'text-gray-400' }} tabular-nums leading-none text-right">
                                                                                        {{ number_format($product->total_stock) }} <span class="text-[9px] font-medium">UNIT{{ $product->total_stock === 1 ? '' : 'S' }}</span><span class="text-[9px] font-medium"> LEFT</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <!-- Batch Toggle -->
                                                                            <button wire:click="toggleBatch({{ $product->prod_code }})" 
                                                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 mt-2 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 hover:border-gray-300 transition-all duration-200 shadow-sm hover:shadow group/button">
                                                                                <svg class="w-3 h-3 transition-all duration-300 {{ $expandedProductCode === $product->prod_code ? 'rotate-90' : '' }} text-gray-600" 
                                                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/>
                                                                                </svg>
                                                                                <span class="text-[10px] font-bold text-gray-700 tracking-wide">
                                                                                    {{ $product->batch_count }} BATCH{{ $product->batch_count === 1 ? '' : 'ES' }}
                                                                                </span>
                                                                            </button>
                                                                            
                                                                        </div>

                                                                        <!-- Batch Details -->
                                                                        @if($expandedProductCode === $product->prod_code)
                                                                            <div class="mt-2 animate-in slide-in-from-top-2 duration-300">
                                                                                <div class="bg-gray-50 rounded-lg border border-gray-200 p-2.5 shadow-inner">
                                                                                    <div class="flex items-center gap-1.5 mb-2 pb-2 border-b border-gray-200">
                                                                                        <div class="flex gap-0.5">
                                                                                            <div class="w-0.5 h-3 bg-gray-900 rounded-full"></div>
                                                                                            <div class="w-0.5 h-3 bg-gray-600 rounded-full"></div>
                                                                                            <div class="w-0.5 h-3 bg-gray-400 rounded-full"></div>
                                                                                        </div>
                                                                                        <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-wider">
                                                                                            Batch Inventory
                                                                                        </h4>
                                                                                    </div>
                                                                                    
                                                                                    <div class="space-y-1.5">
                                                                                        @foreach($batchDetails[$product->prod_code] as $batch)
                                                                                            <div class="group/batch flex items-center justify-between p-2 bg-white rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-md transition-all duration-200">
                                                                                                <div class="flex items-center gap-2">
                                                                                                    <div class="w-7 h-7 rounded-lg bg-gray-900 flex items-center justify-center shadow-sm group-hover/batch:shadow transition-shadow">
                                                                                                        <span class="text-[10px] font-black text-white">B</span>
                                                                                                    </div>
                                                                                                    <div>
                                                                                                        <div class="text-xs font-bold text-gray-900 font-mono leading-none">
                                                                                                            {{ $batch->batch_number }}
                                                                                                        </div>
                                                                                                        @if($batch->expiration_date)
                                                                                                            <div class="flex items-center gap-1 mt-1">
                                                                                                                <svg class="w-2.5 h-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                                                                </svg>
                                                                                                                <span class="text-[10px] font-semibold text-gray-500 tabular-nums">
                                                                                                                    {{ \Carbon\Carbon::parse($batch->expiration_date)->format('M d, Y') }}
                                                                                                                </span>
                                                                                                            </div>
                                                                                                        @endif
                                                                                                    </div>
                                                                                                </div>
                                                                                                
                                                                                                <div class="px-3 py-1.5 rounded-lg bg-gray-50 border border-gray-200 group-hover/batch:bg-gray-100 group-hover/batch:border-gray-300 transition-all duration-200">
                                                                                                    <div class="text-sm font-black text-gray-900 tabular-nums leading-none">
                                                                                                        {{ number_format($batch->stock) }}
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                        @else
                                            <!-- STEP 2: Bundle Contents Review -->
                                            <div>
                                                <label class="flex items-center text-xs font-bold text-gray-900 mb-3">
                                                    <button wire:click="$set('step', 1)" 
                                                        class="flex-shrink-0 px-2 py-2 text-xs font-semibold transition-all hover:text-red-500 flex items-center justify-center group">
                                                        <span class="material-symbols-rounded-smaller">arrow_back_ios</span>
                                                    </button>
                                                    Bundle Contents

                                                    @php
                                                        $selectedCount = collect($selectedProducts)->filter(fn($item) => isset($item['selected']) && $item['selected'])->count();
                                                    @endphp
                                                    
                                                    <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-900 text-white">
                                                        {{ $selectedCount }} {{ $selectedCount === 1 ? 'ITEM' : 'ITEMS' }}
                                                    </span>
                                                </label>
                                                
                                                <div class="relative bg-white border border-gray-200 rounded-xl shadow-sm">
                                                    <!-- Header gradient -->
                                                    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
                                                    
                                                    <div class="px-3 py-3 overflow-y-auto scrollbar-custom">
                                                        <div class="space-y-1.5">
                                                            @foreach($selectedProducts as $prodCode => $data)
                                                                @if(isset($data['selected']) && $data['selected'])
                                                                    <div class="relative flex items-center justify-between gap-3">
                                                                        <div class="flex items-center gap-2 flex-1 min-w-0">
                                                                            <div class="w-1 h-8 bg-gray-900 rounded-full"></div>
                                                                            <div class="flex-1 min-w-0">
                                                                                <p class="text-xs font-bold text-gray-900 truncate leading-tight">
                                                                                    {{ $data['product_name'] }}
                                                                                </p>
                                                                                <p class="text-[10px] font-semibold text-gray-500 font-mono mt-0.5">
                                                                                    {{ $data['product_barcode'] }}
                                                                                </p>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Buy/Get Quantity -->
                                                                        <div class="flex items-center gap-1">
                                                                            <label class="text-[10px] text-gray-500">Qty</label>
                                                                            <input type="number" min="1"
                                                                                wire:change="calculatePricingPreview"
                                                                                wire:model="selectedProducts.{{ $prodCode }}.quantity"
                                                                                class="w-12 py-0.5 px-1 border rounded text-[10px] text-center" />
                                                                        </div>

                                                                        @if(isset($bundleType) && in_array($bundleType, ['BOGO1','BOGO2']))
                                                                            <div class="flex items-center gap-1">
                                                                                <button type="button"
                                                                                        wire:click="setBogoType('{{ $prodCode }}')"
                                                                                        class="text-[10px] px-2 py-0.5 rounded border
                                                                                            {{ !empty($selectedProducts[$prodCode]['bogo_type'])
                                                                                                ? 'bg-green-500 text-white'
                                                                                                : 'bg-gray-100 text-gray-700' }}">
                                                                                    P
                                                                                </button>
                                                                            </div>
                                                                        @endif

                                                                        <!-- Remove Button -->
                                                                        <button wire:click="removeProduct('{{ $prodCode }}')" 
                                                                                class="group-hover:opacity-100 p-1.5 hover:text-red-500 rounded-md transition-all duration-200 flex-shrink-0 group/remove text-black">
                                                                            <span class="material-symbols-rounded-smaller">delete</span>
                                                                        </button>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Footer gradient -->
                                                    <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
                                                </div>
                                                
                                                @error('selectedProducts')
                                                    <div class="flex items-center gap-1.5 mt-2 px-2 py-1.5 bg-red-50 border border-red-200 rounded-lg">
                                                        <svg class="w-3 h-3 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                                                    </div>
                                                @enderror

                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Pricing preview -->
                                <div class="lg:col-span-1">
                                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

                                        <!-- Header -->
                                        <div class="px-3 py-2 border-b border-gray-200 bg-gray-50">
                                            <div class="flex items-center gap-2">
                                                <div class="w-1 h-3.5 bg-gray-900 rounded-full"></div>
                                                <h2 class="text-xs font-black text-gray-900 uppercase tracking-wide">
                                                    Pricing Preview 
                                                </h2>
                                            </div>
                                        </div>
                                        
                                        @if(!empty($pricingPreview))
                                        <div class="px-3 py-3 space-y-4">

                                            <!-- Core Numbers (2x2 layout) -->
                                            <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-[11px]">

                                                <!-- Cost Price -->
                                                <div class="ml-3">
                                                    <p class="text-gray-500 font-semibold">Cost Price</p>
                                                    <p class="font-black text-gray-900">
                                                        ₱{{ number_format($pricingPreview['total_cost'], 2) }}
                                                    </p>
                                                </div>

                                                <!-- Regular Price -->
                                                <div>
                                                    <p class="text-gray-500 font-semibold">Regular Price</p>
                                                    <p class="font-black text-gray-900">
                                                        ₱{{ number_format($pricingPreview['regular_price'], 2) }}
                                                    </p>
                                                </div>

                                                <!-- Discount -->
                                                <div class="ml-3">
                                                    <p class="text-gray-500 font-semibold">Discount Offer</p>
                                                    <p class="font-black text-green-700">
                                                        {{ number_format($selectedDiscount, 2) }}%
                                                    </p>
                                                </div>

                                                <!-- new Price -->
                                                <div>
                                                    <p class="text-gray-500 font-semibold">Bundle Price 
                                                        @if($selectedDiscount)
                                                            <span class="ml-1 bg-red-500 text-white text-[8px] p-1 rounded">NEW!</span>
                                                        @endif
                                                    </p> 
                                                    <p class="font-black text-gray-900">
                                                        @if($selectedDiscount && $bundleType != 'BOGO1')
                                                            ₱{{ number_format(
                                                                $pricingPreview['regular_price']
                                                                - ($pricingPreview['regular_price'] * ($selectedDiscount / 100)),
                                                                2
                                                            ) }}
                                                        @elseif( $bundleType == 'BOGO1' )
                                                            ₱{{ number_format($newBundlePrice, 2) }}
                                                        @elseif( $bundleType === 'BOGO2' )
                                                            ₱{{ number_format($freeBundlePrice, 2) }}
                                                        @else
                                                            ₱0.00
                                                        @endif
                                                    </p>
                                                </div>

                                                <div class="ml-3 py-1">
                                                    <p class="text-gray-500 font-semibold">Save</p>
                                                </div>
                                                <div class="bg-green-100 p-1 rounded-lg">
                                                    @if($selectedDiscount)
                                                        <p class="font-black text-gray-900">
                                                            ₱{{ number_format($pricingPreview['regular_price'] - $newBundlePrice, 2) }}
                                                        </p>
                                                    @elseif($bundleType === 'BOGO2')
                                                        <p class="font-black text-gray-900">
                                                            {{ number_format($regularBundlePrice, 2) }}
                                                        </p>
                                                    @else
                                                        <p class="font-black text-gray-900">
                                                            -
                                                        </p>
                                                    @endif
                                                </div>

                                            </div>
                                            

                                            <!-- Divider -->
                                            <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>

                                            <!-- Discount Options -->
                                            <div class="space-y-2">

                                                <p class="text-[10px] font-bold text-gray-700 uppercase tracking-wide">
                                                    Discount Options
                                                </p>

                                                @if($pricingPreview['discount_options']->isNotEmpty())
                                                    <div class="space-y-1.5 grid-cols-2 gap-2 h-[250px] overflow-y-auto scrollbar-custom">

                                                        @foreach($pricingPreview['discount_options'] as $option)
                                                            <div
                                                                wire:click="selectDiscount({{ $option['discount_percent'] }}, {{ $option['bundle_price'] }})"
                                                                class="
                                                                    border rounded-lg px-2 py-2 cursor-pointer transition
                                                                    {{ $selectedDiscount === $option['discount_percent']
                                                                        ? 'border-gray-900 bg-gray-900 text-white shadow-md'
                                                                        : 'border-gray-200 bg-gray-50 hover:bg-gray-100'
                                                                    }}
                                                                "
                                                            >
                                                                <!-- Top row -->
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-[10px] font-bold">
                                                                        {{ $option['discount_percent'] }}% Discount
                                                                    </span>
                                                                    <span class="text-[11px] font-black">
                                                                        ₱{{ number_format($option['bundle_price'], 2) }}
                                                                    </span>
                                                                </div>

                                                                <!-- Profit -->
                                                                <p class="text-[10px] font-semibold mt-0.5 text-right
                                                                    {{ $selectedDiscount === $option['discount_percent']
                                                                        ? 'text-green-200'
                                                                        : 'text-blue-700'
                                                                    }}">
                                                                    Profit: ₱{{ number_format($option['profit'], 2) }}
                                                                </p>
                                                            </div>
                                                        @endforeach


                                                    </div>
                                                @else
                                                    @if($selectedDiscount == 'BOGO2')
                                                    <p class="text-[10px] text-red-600 font-semibold">
                                                        No discount options.
                                                    </p>
                                                    @endif
                                                    <p class="text-[10px] text-red-600 font-semibold">
                                                        No discounts allowed under the current margin policy.
                                                    </p>
                                                @endif
                                            </div>

                                            <!-- Helper text -->
                                            <p class="text-[9px] text-gray-500 leading-tight">
                                                Prices and discounts are automatically calculated using your
                                                <span class="font-semibold">minimum margin rule</span>.
                                                Discounts that break profitability are blocked.
                                            </p>

                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        wire:click="createBundle"
                                        class="flex-1 bg-gray-900 text-white px-3 py-1.5 rounded-lg text-[10px] font-bold hover:bg-gray-800 transition-all shadow-sm hover:shadow-md">
                                        Create Bundle
                                    </button>
                                    <button
                                        wire:click="resetForm"
                                        class="px-3 py-1.5 bg-white text-gray-700 border-2 border-gray-200 rounded-lg text-[10px] font-bold hover:bg-gray-50 hover:border-gray-300 transition-all">
                                        Reset
                                    </button>
                                </div>
                            </div>


                        </div>
                    </div>
                @elseif($activeTab === 'suggestion')
                    <div class="container mx-auto p-6">
                        <div class="bg-white rounded-lg shadow">
                            <div class="p-6 border-b border-gray-200">
                                <h2 class="text-2xl font-bold text-gray-800">Bundle Management</h2>
                                <p class="text-gray-600 mt-1">Click on a bundle to view details</p>
                            </div>
                            
                            <div class="p-6">
                                @if(count($allBundle) > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($allBundle as $bundle)
                                    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                                        {{-- Bundle Card Header --}}
                                        <div class="p-3 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="flex-1">
                                                    <h3 class="text-xs font-semibold text-gray-900">{{ $bundle->bundle_name }}</h3>
                                                    <p class="text-[10px] text-gray-500 mt-0.5">{{ $bundle->bundle_code }}</p>
                                                </div>
                                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full 
                                                    {{ $bundle->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $bundle->status }}
                                                </span>
                                            </div>
                                            
                                            <div class="space-y-1.5">
                                                <div class="flex items-center text-[10px] text-gray-600">
                                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                    </svg>
                                                    <span>{{ $bundle->bundle_category }}</span>
                                                </div>
                                                <div class="flex items-center text-[10px] text-gray-600">
                                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                    </svg>
                                                    <span>{{ $bundle->bundle_type }}</span>
                                                </div>
                                            </div>
                                            
                                            <button 
                                                wire:click="selectBundle({{ $bundle->bundle_id }})" 
                                                class="mt-3 w-full flex items-center justify-center px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-[10px]"
                                            >
                                                <span class="mr-1.5">View Details</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        {{-- Expandable Details Section --}}
                                        @if($selectedBundle && count($selectedBundle) > 0 && $selectedBundle[0]->bundle_id == $bundle->bundle_id)
                                        <div class="border-t border-gray-200 bg-gray-50">
                                            <div class="p-3">
                                                <div class="space-y-3">
                                                    {{-- Bundle Information --}}
                                                    <div class="bg-white rounded-lg p-3 shadow-sm">
                                                        <h4 class="text-xs font-bold text-gray-900 mb-2">Bundle Information</h4>
                                                        
                                                        <div class="space-y-2">
                                                            <div>
                                                                <label class="text-[10px] font-medium text-gray-500">Category</label>
                                                                <p class="text-xs text-gray-900 mt-0.5">{{ $selectedBundle[0]->bundle_category }}</p>
                                                            </div>

                                                            <div>
                                                                <label class="text-[10px] font-medium text-gray-500">Type</label>
                                                                <p class="text-xs text-gray-900 mt-0.5">{{ $selectedBundle[0]->bundle_type }}</p>
                                                            </div>

                                                            <div>
                                                                <label class="text-[10px] font-medium text-gray-500">Status</label>
                                                                <p class="mt-0.5">
                                                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full 
                                                                        {{ $selectedBundle[0]->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                                        {{ $selectedBundle[0]->status }}
                                                                    </span>
                                                                </p>
                                                            </div>

                                                            <div class="border-t border-gray-200 pt-2">
                                                                <h5 class="text-[10px] font-medium text-gray-500 mb-2">Pricing Details</h5>
                                                                
                                                                <div class="space-y-1.5">
                                                                    <div class="flex justify-between items-center">
                                                                        <span class="text-[10px] text-gray-600">Discount</span>
                                                                        <span class="text-xs font-semibold text-green-600">{{ $selectedBundle[0]->discount_percent }}%</span>
                                                                    </div>
                                                                    <div class="flex justify-between items-center">
                                                                        <span class="text-[10px] text-gray-600">Min Margin</span>
                                                                        <span class="text-xs font-semibold text-gray-900">₱{{ $selectedBundle[0]->min_margin }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="border-t border-gray-200 pt-2">
                                                                <h5 class="text-[10px] font-medium text-gray-500 mb-2">Duration</h5>
                                                                
                                                                <div class="space-y-1">
                                                                    <div>
                                                                        <span class="text-[10px] text-gray-500">Start Date</span>
                                                                        <p class="text-xs text-gray-900">{{ \Carbon\Carbon::parse($selectedBundle[0]->start_date)->format('M d, Y') }}</p>
                                                                    </div>
                                                                    <div>
                                                                        <span class="text-[10px] text-gray-500">End Date</span>
                                                                        <p class="text-xs text-gray-900">{{ \Carbon\Carbon::parse($selectedBundle[0]->end_date)->format('M d, Y') }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Bundle Items --}}
                                                    <div class="bg-white rounded-lg shadow-sm">
                                                        <div class="p-3 border-b border-gray-200">
                                                            <h4 class="text-xs font-bold text-gray-900">Bundle Items</h4>
                                                            <p class="text-[10px] text-gray-600 mt-0.5">Products included in this bundle</p>
                                                        </div>

                                                        <div class="p-3">
                                                            <div class="overflow-x-auto">
                                                                <table class="w-full">
                                                                    <thead>
                                                                        <tr class="border-b border-gray-200">
                                                                            <th class="text-left py-2 px-2 font-semibold text-[10px] text-gray-700">Product Name</th>
                                                                            <th class="text-right py-2 px-2 font-semibold text-[10px] text-gray-700">Quantity</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($selectedBundle as $item)
                                                                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                                                            <td class="py-2 px-2">
                                                                                <div class="flex items-center">
                                                                                    <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center mr-2">
                                                                                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                                                        </svg>
                                                                                    </div>
                                                                                    <span class="text-xs text-gray-900 font-medium">{{ $item->name }}</span>
                                                                                </div>
                                                                            </td>
                                                                            <td class="py-2 px-2 text-right">
                                                                                <span class="inline-flex items-center px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-[10px] font-semibold">
                                                                                    {{ $item->quantity }}
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>

                                                            <div class="mt-3 p-2 bg-gray-50 rounded-lg">
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-[10px] text-gray-600 font-medium">Total Items</span>
                                                                    <span class="text-xs font-bold text-gray-900">{{ count($selectedBundle) }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Collapse Button --}}
                                                <div class="mt-2 text-center">
                                                    <button 
                                                        wire:click="loadAllBundle" 
                                                        class="inline-flex items-center px-3 py-1.5 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-[10px]"
                                                    >
                                                        <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                        </svg>
                                                        <span>Collapse Details</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-gray-500">No bundles available</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                @endif
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg shadow-lg">
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg shadow-lg">
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
    @endif

</div>