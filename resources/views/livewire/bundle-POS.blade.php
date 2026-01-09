<div class="relative">
    <!-- Button to toggle dropdown -->
    <button wire:click="loadBundles"
            class="px-3 py-1 bg-gray-900 text-white rounded text-sm mb-2">
        Show Bundles
    </button>

    <!-- Dropdown Menu -->
    @if($showBundles)
        <div class="absolute left-0 mt-1 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
            <!-- Header -->
            <div class="bg-gray-50 px-4 py-2 border-b flex justify-between items-center rounded-t-lg">
                <h3 class="text-sm font-medium text-gray-900">
                    Select Bundle
                </h3>
                <button wire:click="$set('showBundles', false)" 
                        class="text-gray-400 hover:text-gray-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Bundle List -->
            <div class="max-h-64 overflow-y-auto">
                @forelse ($bundles as $bundle)
                    <button wire:click="selectBundle({{ $bundle->bundle_id }})"
                            class="w-full text-left px-4 py-2 hover:bg-gray-50 border-b border-gray-100 transition-colors">
                        <div class="font-medium text-sm text-gray-900">{{ $bundle->bundle_name }}</div>
                        <div class="text-xs text-gray-500">{{ $bundle->bundle_code }}</div>
                    </button>
                @empty
                    <div class="px-4 py-3 text-center text-sm text-gray-500">
                        No bundles found
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    <!-- Side Modal for Bundle Details -->
    @if($selectedBundle && count($selectedBundle) > 0)
        <div class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-gray-900 bg-opacity-60 transition-opacity" 
                wire:click="$set('selectedBundle', null)"></div>

            <!-- Slide-over panel -->
            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div class="w-screen max-w-lg">
                    <div class="h-full flex flex-col bg-white shadow-2xl">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4 border-b border-gray-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-[13px] font-semibold text-white tracking-wide uppercase" id="slide-over-title">
                                        Bundle Information
                                    </h2>
                                    <p class="text-[10px] text-gray-300 mt-0.5">Detailed breakdown of bundle contents</p>
                                </div>
                                <button wire:click="$set('selectedBundle', null)"
                                        class="rounded-md text-gray-400 hover:text-white hover:bg-gray-700 p-1.5 transition-colors">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Bundle Overview -->
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Bundle Name</label>
                                    <p class="text-xs font-medium text-gray-900 mt-1">{{ $selectedBundle[0]->bundle_name }}</p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Bundle Code</label>
                                    <p class="text-xs font-medium text-gray-900 mt-1">{{ $selectedBundle[0]->bundle_code }}</p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Category</label>
                                    <p class="text-xs text-gray-900 mt-1">{{ $selectedBundle[0]->bundle_category ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Type</label>
                                    <p class="text-xs text-gray-900 mt-1">{{ $selectedBundle[0]->bundle_type ?? 'N/A' }}
                                        @if($selectedBundle[0]->bundle_type == 'BOGO1')
                                            <span class="text-xs text-gray-900" >DISCOUNTED</span>
                                        @elseif($selectedBundle[0]->bundle_type == 'BOGO2')
                                            <span class="text-xs text-gray-900" >FREE</span>
                                        @else
                                            <span class="text-xs text-gray-900" ></span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Status</label>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $selectedBundle[0]->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($selectedBundle[0]->status) }}
                                    </span>
                                </div>
                                <div>
                                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Discount</label>
                                    <p class="text-xs text-gray-900 mt-1">{{ $selectedBundle[0]->discount_percent ?? '0' }}%</p>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 overflow-y-auto px-6 py-4">
                            <div class="mb-3">
                                <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wide border-b border-gray-200 pb-2">
                                    Bundle Items
                                </h3>
                            </div>
                            
                            <div class="space-y-2">
                                @foreach($selectedBundle as $item)
                                    <div class="bg-white border border-gray-200 rounded-md hover:border-gray-300 transition-colors">
                                        <div class="px-4 py-3">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-semibold text-gray-900 truncate">{{ $item->name }}</p>
                                                    <div class="flex items-center gap-3 mt-1.5">
                                                        <span class="inline-flex items-center text-[10px] text-gray-600">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                            </svg>
                                                            Qty: <span class="font-semibold ml-0.5">{{ $item->quantity }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Bundle Settings -->
                            @if($selectedBundle[0]->start_date || $selectedBundle[0]->end_date)
                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wide mb-3">
                                    Validity Period
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    @if($selectedBundle[0]->start_date)
                                    <div>
                                        <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Start Date</label>
                                        <p class="text-xs text-gray-900 mt-1">{{ date('M d, Y', strtotime($selectedBundle[0]->start_date)) }}</p>
                                    </div>
                                    @endif
                                    @if($selectedBundle[0]->end_date)
                                    <div>
                                        <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">End Date</label>
                                        <p class="text-xs text-gray-900 mt-1">{{ date('M d, Y', strtotime($selectedBundle[0]->end_date)) }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>