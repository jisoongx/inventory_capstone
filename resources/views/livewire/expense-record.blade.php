<div class="flex flex-col h-[41rem] overflow-hidden px-3">
    @livewire('expiration-container')
    <div class="flex-1 grid grid-cols-3 space-x-4 mt-4">
        <div class="bg-white p-5 rounded-lg col-span-2 flex flex-col border border-slate-20"> 
            <div class="mb-4 text-center flex-shrink-0"> 
                <p class="text-xs text-gray-700 mb-2">Year {{ $year }}</p> 
                <div class="flex items-center justify-center space-x-2 bg-slate-100 p-3">
                    <button wire:click="previousMonth" class="text-gray-500"
                    wire:loading.attr="disabled">
                        <span class="material-symbols-rounded-small">arrow_back_ios</span>
                    </button>

                    <span class="text-sm font-semibold w-[10rem]">
                        {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
                    </span>

                    <button wire:click="nextMonth" class="text-gray-500"
                    wire:loading.attr="disabled">
                        <span class="material-symbols-rounded-small">arrow_forward_ios</span>
                    </button>
                </div>
            </div>


            <div class="flex-grow overflow-y-auto">    
                <table class="w-full text-xs text-left text-gray-700">
                    <thead class="uppercase text-xs font-medium sticky top-0 z-10 bg-white shadow-sm">
                        <tr>
                            <th class="py-4 w-[26%]">Title</th>
                            <th class="py-4 w-[15%]">Cost</th>
                            <th class="py-4 w-[17%]">Date Recorded</th>
                            <th class="py-4 w-[15%]">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-left">
                        @forelse ($inputs as $input)
                        <tr>
                            <td>
                                @if ($editingId === $input->expense_id)
                                    <input type="text" wire:model="editingDescri"
                                        class="p-0 pb-1 border-0 border-b border-slate-300 focus:border-slate-500 w-[80%] text-xs font-medium rounded-none">
                                @else
                                    <a href="{{ asset('storage/' . $input->file_path) }}" target="_blank"
                                    class="text-blue-600 hover:underline text-xs font-medium">
                                        {{ $input->expense_descri }}
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if ($editingId === $input->expense_id)
                                    <input type="number" step="0.01" wire:model="editingAmount"
                                        class="p-0 pb-1 border-0 border-b border-slate-300 focus:ring-0 focus:border-slate-500 w-[90%] text-xs font-medium rounded-none">
                                @else
                                    <span>{{ $input->expense_amount }}</span>
                                @endif
                            </td>
                            <td>{{ date('F d • g:i A', strtotime($input->expense_created)) }}</td>
                            <td class="p-2 flex gap-2 items-center">
                                @if ($editingId === $input->expense_id)
                                    <button type="button" wire:click="saveExpense"
                                        class="bg-green-600 text-white px-3 py-1.5 rounded">
                                        Save
                                    </button>
                                    <button type="button" wire:click="$set('editingId', null)">
                                        <span class="material-symbols-rounded text-red-600">close_small</span>
                                    </button>
                                @else
                                <button 
                                    type="button" wire:click="editExpense({{ $input->expense_id }})"
                                    class="px-3 py-1.5 rounded bg-red-500 text-white hover:bg-red-600
                                        {{ $expired ? 'cursor-pointer hover:bg-red-500' : '' }}"
                                        {{ $expired ? 'disabled' : '' }}>Edit
                                </button>
                                    @if($expired)
                                    <div x-data="{ open: false }" class="relative inline-block">
                                        <span class="material-symbols-rounded-premium text-yellow-500 cursor-pointer"
                                            @mouseenter="open = true" @mouseleave="open = false">crown</span>

                                        <div x-show="open" x-transition class="absolute z-50 mt-2 bg-white border rounded px-2 py-1 text-[10px] shadow">
                                            Subscribe to use this feature.
                                        </div>
                                    </div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td class="p-5 text-center" colspan="4">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <span class="material-symbols-rounded-semibig text-slate-300">hourglass_disabled</span>
                                    <span>No records to show.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <button wire:click='addModalOpen()'
            class="text-xs mt-3 text-right font-bold flex-shrink-0
                {{ $expired 
                        ? 'text-gray-600 cursor-not-allowed' 
                        : 'text-blue-600 ' }}" 
                        {{ $expired ? 'disabled' : '' }} type="button">
                + Add record
            </button>

            <div class="bg-blue-100 rounded-md p-4 mt-5 text-xs space-y-1 flex-shrink-0">
                <p><span class="font-semibold">Current Sales:</span> <span class="font-semibold text-black float-right">₱{{ number_format($totals->salesTotal, 2) }}</span></p>
                <p><span class="font-semibold text-gray-700">Total In-Store Expenses:</span> <span class="text-gray-700 float-right">₱{{ number_format($totals->expenseTotal, 2) }}</span></p>
                <p><span class="font-semibold text-gray-700">Total Revenue Loss:</span> <span class="text-gray-700 float-right">₱{{ number_format($totals->lossTotal, 2) }}</span></p>
                <p><span class="font-semibold text-red-600 text-sm">Month's Net Profit:</span> <span class="text-red-600 float-right font-bold text-sm">₱{{ number_format($totals->salesTotal - ($totals->expenseTotal + $totals->lossTotal), 2) }}</span></p>
            </div>
        </div>



        <div class="bg-white pl-7 pr-5 py-5 rounded-lg flex flex-col relative space-y-3 overflow-y-auto border border-slate-20">
            <p class="font-semibold text-sm">This Month's Insights</p>
            <div class="relative flex flex-col space-y-3 items-start justify-center border-l-2 border-gray-200 ">

                <div x-data="{ active: 1 }" class="space-y-4">
                    <!-- category -->
                    <div class="relative pl-8 w-full">
                        <div class="absolute -left-4 flex items-center justify-center rounded-full bg-blue-500 text-white shadow-md p-1.5">
                            <span class="material-symbols-rounded text-xs">social_leaderboard</span>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 space-y-2">
                            <p class="text-sm text-gray-500">Revenue</p>
                            @if(empty($topCategory))
                                <p class="text-gray-800 text-base text-xs">No sales have been made.</p>
                            @else 
                                <p class="text-gray-800 text-base text-xs">{{ $topCategory[0]->category_name }} were the 
                                    <span class="font-semibold text-blue-600">top category</span>, contributing 
                                    <span class="font-semibold">{{ number_format($topCategory[0]->category_percentage, 0) }}%</span> 
                                    of sales. 
                                </p>
                                <button
                                    @click="active = active === 1 ? null : 1" 
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                                    <span x-show="active !== 1">Show Leading Categories</span>
                                    <span x-show="active === 1">Hide Leading Categories</span>
                                    <span class="material-symbols-rounded text-xs" :class="{ 'rotate-180': active === 1 }">expand_more</span>
                                </button>

                                <div 
                                    x-show="active === 1" 
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-300" 
                                    x-transition:enter-start="max-h-0 opacity-0" 
                                    x-transition:enter-end="max-h-96 opacity-100" 
                                    x-transition:leave="transition ease-in duration-200" 
                                    x-transition:leave-start="max-h-96 opacity-100" 
                                    x-transition:leave-end="max-h-0 opacity-0" 
                                    class="overflow-hidden">
                                    <ul class="mt-2 space-y-1 text-xs">
                                        @foreach($topCategory as $topCat)
                                            <li class="flex justify-between items-center p-2 rounded bg-gray-50">
                                                <span class="flex items-center gap-2">
                                                    <span class="w-5 h-5 flex items-center justify-center rounded-full text-white text-[10px] font-bold
                                                        @if($loop->iteration==1) bg-yellow-500
                                                        @elseif($loop->iteration==2) bg-gray-400
                                                        @elseif($loop->iteration==3) bg-amber-700
                                                        @else bg-blue-500 @endif">
                                                        {{ $loop->iteration }}
                                                    </span>
                                                    {{ $topCat->category_name }}
                                                </span>
                                                <span class="font-semibold">{{ number_format($topCat->category_percentage,0) }}%</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- expense -->
                    <div class="relative pl-8 w-full">
                        <div class="absolute -left-4 flex items-center justify-center rounded-full bg-red-500 text-white shadow-md p-1.5">
                            <span class="material-symbols-rounded text-xs">money_off</span>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 space-y-2">
                            <p class="text-sm text-gray-500">Expenses</p>
                            @if(empty($topExpense))
                                <p class="text-gray-800 text-base text-xs">No expenses have been recorded.</p>
                            @else 
                                <p class="text-gray-800 text-base text-xs">{{ ucwords($topExpense[0]->category_name) }} was the 
                                    <span class="font-semibold text-red-600">largest expense</span>, making up 
                                    <span class="font-semibold">{{ number_format($topExpense[0]->expense_percentage, 0) }}%</span> 
                                    of expenses.
                                </p>
                                <button 
                                    @click="active = active === 2 ? null : 2" 
                                    class="text-xs text-red-600 hover:text-red-800 font-medium flex items-center gap-1">
                                    <span x-show="active !== 2">Show Leading Expenses</span>
                                    <span x-show="active === 2">Hide Leading Expenses</span>
                                    <span class="material-symbols-rounded text-xs" :class="{ 'rotate-180': active === 2 }">expand_more</span>
                                </button>

                                <div 
                                    x-show="active === 2" 
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-300" 
                                    x-transition:enter-start="max-h-0 opacity-0" 
                                    x-transition:enter-end="max-h-96 opacity-100" 
                                    x-transition:leave="transition ease-in duration-200" 
                                    x-transition:leave-start="max-h-96 opacity-100" 
                                    x-transition:leave-end="max-h-0 opacity-0" 
                                    class="overflow-hidden">
                                    <ul class="mt-2 space-y-1 text-xs">
                                        @foreach($topExpense as $topExp)
                                            <li class="flex justify-between items-center p-2 rounded bg-gray-50">
                                                <span class="flex items-center gap-2">
                                                    <span class="w-5 h-5 flex items-center justify-center rounded-full text-white text-[10px] font-bold
                                                        @if($loop->iteration==1) bg-yellow-500
                                                        @elseif($loop->iteration==2) bg-gray-400
                                                        @elseif($loop->iteration==3) bg-amber-700
                                                        @else bg-blue-500 @endif">
                                                        {{ $loop->iteration }}
                                                    </span>
                                                    {{ ucwords($topExp->category_name) }}
                                                </span>
                                                <span class="font-semibold">{{ number_format($topExp->expense_percentage,0) }}%</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative w-full">
                <div class="bg-white p-2 rounded border-l-8 border border-green-900 space-y-2">
                    @if(empty($highestEarn))
                        <p class="text-gray-800 text-base text-xs">No data to show.</p>
                    @else 
                        <p class="text-gray-800 text-base text-xs">The {{ date('jS', strtotime($highestEarn[0]->dayTotal)) }} was your 
                            <span class="font-semibold text-green-600">peak sales day</span>, with
                            <span class="font-semibold">₱{{ number_format($highestEarn[0]->salesTotal, 2) }}</span> 
                            earned.
                        </p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    @if($addModal)
        <div id="add-modal" tabindex="-1" aria-hidden="true"
            class="overflow-y-auto overflow-x-hidden fixed z-50 inset-0 flex justify-center items-center w-full bg-black/60 backdrop-blur-sm transition-opacity duration-300">
            <div class="relative p-4 w-full max-w-2xl max-h-full" wire:click.away="closeModal">
                <div class="border border-red-800 relative bg-white rounded-xl shadow-xl border border-gray-100 transform transition-all duration-300 scale-100">

                    <div class="absolute -top-12 left-1/2 transform -translate-x-1/2">
                        <img src="{{ asset('assets/expense.jpg') }}"
                            class="w-24 h-24 rounded-full border-8 border-red-800 shadow-md">
                    </div>
                    <div class="flex items-center justify-center pt-16 pb-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">Add New Expense</h3>
                    </div>
                    
                    <form wire:submit="addExpenses" enctype="multipart/form-data" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 ">
                            <div>
                                <label for="expense_descri"
                                    class="block mb-2 text-sm font-semibold text-gray-700">Item / Purpose <span class="text-red-500">*</span></label>
                                <input wire:model.live="add_expense_descri" type="text" id="expense_descri" required
                                    class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 block w-full p-2.5 transition-all duration-200"/>
                                <p class="text-red-500 font-medium text-xs mt-2 flex items-center {{ $descriptionError ? '' : 'invisible' }}">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $descriptionError ?: 'Good' }}
                                </p>
                            </div>
                            <div>
                                <label for="expense_category"
                                    class="block mb-2 text-sm font-semibold text-gray-700">Expense Category <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-tag text-gray-400 text-sm"></i>
                                    </div>
                                    <select wire:model.defer="add_expense_category" id="expense_category" required
                                            class="pl-10 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 block w-full p-2.5 appearance-none bg-white">
                                        <option value="">-- Select Category --</option>
                                        <option value="purchases">Purchases</option>
                                        <option value="supplies">Supplies</option>
                                        <option value="utilities">Utilities</option>
                                        <option value="rent">Rent</option>
                                        <option value="transportation">Transportation</option>
                                        <option value="maintenance and repairs">Maintenance and Repairs</option>
                                        <option value="salaries">Salaries</option>
                                        <option value="marketing">Marketing</option>
                                        <option value="licenses/permits">Licenses/Permits</option>
                                        <option value="insurance">Insurance</option>
                                        <option value="software/subscriptions">Software/Subscriptions</option>
                                        <option value="taxes">Taxes</option>
                                        <option value="miscellaneous/others">Miscellaneous/Others</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="expense_amount"
                                    class="block mb-2 text-sm font-semibold text-gray-700">Amount <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500">₱</span>
                                    </div>
                                    <input wire:model.live="add_expense_amount" type="number" step="0.01" id="expense_amount" required
                                        class="pl-10 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 block w-full p-2.5 transition-all duration-200"/>
                                </div>
                                <p class="text-red-500 font-medium text-xs mt-2 flex items-center {{ $amountError ? '' : 'invisible' }}">
                                    {{ $amountError ?: 'Good' }}
                                </p>
                            </div>
                            <div >
                                <label for="add_expense_file"
                                    class="block mb-2 text-sm font-semibold text-gray-700">Attachment</label>
                                <div class="relative flex justify-center items-center border border-gray-300 text-gray-900 text-sm rounded-lg 
                                            focus-within:ring-2 focus-within:ring-red-500 focus-within:border-red-500
                                            w-full transition-all duration-200 bg-white">       
                                    <span class="material-symbols-rounded text-gray-500 absolute left-3 pointer-events-none">add_photo_alternate</span>
                                    <input
                                        wire:model="add_expense_file"
                                        type="file"
                                        id="add_expense_file"
                                        accept=".pdf,.docx,.jpg,.jpeg,.png"
                                        class="w-full text-sm text-gray-700 cursor-pointer rounded-lg"
                                    />
                                </div>
                                <p class="text-red-500 font-medium text-xs mt-2 flex items-center {{ $fileError ? '' : 'invisible' }}">
                                    {{ $fileError ?: 'Good' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                            <button type="button" wire:click="closeModal"
                                    class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 transition-colors duration-200">
                                Cancel
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                    class="px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-red-500 to-red-600 rounded-lg hover:from-red-600 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 shadow-sm transition-all duration-200 flex items-center justify-center">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Add Expense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>