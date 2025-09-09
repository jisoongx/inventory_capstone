@extends('dashboards.owner.owner') 

@section('content')

    <div class="flex-1 grid grid-cols-3 gap-4 p-2">
        <div class="h-[40rem] bg-white shadow-lg p-5 rounded-lg col-span-2 flex flex-col">    
            <div class="mb-4 text-center">
                <p class="text-xs text-gray-700">Year {{ $year }}</p>
                <form method="GET" class="flex items-center justify-center space-x-7 mt-2 p-2 bg-slate-100 rounded-lg">
                    <button type="submit" name="month" value="{{ $month - 1 }}" class="text-gray-500">
                        <span class="material-symbols-rounded">arrow_back_ios</span>
                    </button>

                    <span class="text-sm font-semibold">{{ date('F', mktime(0,0,0, $month, 1)) }}</span>

                    <button type="submit" name="month" value="{{ $month + 1 }}" class="text-gray-500">
                        <span class="material-symbols-rounded">arrow_forward_ios</span>
                    </button>

                    <input type="hidden" name="year" value="{{ $year }}">
                </form>
            </div>


            <div class="flex-grow">    
                <table id="expensesTable" class="w-full text-xs text-left text-gray-700">
                    <thead class="uppercase text-xs font-medium sticky top-0 z-10 bg-white shadow-sm">
                        <tr>
                            <th class="py-4 px-2">Title</th>
                            <th class="py-4">Cost</th>
                            <th class="py-4">Date Recorded</th>
                            <th class="py-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inputs as $input)
                            <tr>
                                <form method="POST" action="{{ route('dashboards.owner.expense_record_edit', ['expense_id' => $input->expense_id]) }}">
                                @csrf
                                    <td class="p-2">
                                        <a href="{{ route('expenses.attachment', ['expense_id' => $input->expense_id]) }}" target="_blank"
                                        class="view font-medium p-2">{{ $input->expense_descri }}</a>
                                        <input type="text" name="expense_descri"
                                            value="{{ old('expense_descri', $input->expense_descri) }}"
                                            class="edit hidden border p-2 rounded w-full text-xs font-medium">
                                    </td>
                                    <td class=" p-2">
                                        <span class="view p-2">{{ $input->expense_amount }}</span>
                                        <input type="number" step="0.01" name="expense_amount"
                                            value="{{ old('expense_amount', $input->expense_amount) }}"
                                            class="edit hidden border p-2 rounded w-full text-xs">
                                    </td>
                                    <td class="p-2">{{ date('F d • g:i A', strtotime($input->expense_created)) }}</td>
                                    <td class=" p-2 flex gap-2 items-center p-2">
                                        <button type="button" class="editBtn bg-red-500 text-white px-3 py-1.5 rounded">
                                            Edit
                                        </button>
                                        <button type="submit" class="saveBtn hidden bg-green-600 text-white px-3 py-1.5 rounded">
                                            Save
                                        </button>
                                        <button type="button" class="cancelBtn hidden flex items-center justify-center w-6 h-6 rounded">
                                            <span class="material-symbols-rounded text-red-500 text-base">cancel</span>
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button data-modal-target="add-modal" data-modal-toggle="add-modal" class="text-xs text-blue-600 mt-3 text-right font-bold" type="button">
                + Add record
            </button>

            <div class="bg-blue-100 rounded-md p-4 mt-5 text-xs space-y-1">
                <p><span class="font-semibold">Current Sales:</span> <span class="text-black float-right">₱{{ number_format($salesTotal->salesTotal, 2) }}</span></p>
                <p><span class="font-semibold text-gray-700">Total In-Store Expenses:</span> <span class="text-gray-700 float-right">₱{{ number_format($expenseTotal->expenseTotal, 2) }}</span></p>
                <p><span class="font-semibold text-gray-700">Total Revenue Loss:</span> <span class="text-gray-700 float-right">₱{{ number_format($lossTotal->lossTotal, 2) }}</span></p>
                <p><span class="font-semibold text-red-600 text-sm">Month's Net Profit:</span> <span class="text-red-600 float-right font-bold text-sm">₱{{ number_format($salesTotal->salesTotal - ($expenseTotal->expenseTotal + $lossTotal->lossTotal), 2) }}</span></p>
            </div>
        </div>



        <div class="h-[40rem] bg-white shadow-lg pl-7 pr-5 py-5 rounded-lg flex flex-col relative space-y-3">
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

    <div id="add-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed z-50 inset-0 flex justify-center items-center w-full">
        <div class="relative p-4 w-full max-w-3xl max-h-full">
            <div class="relative bg-white rounded shadow-sm">

                <div class="absolute -top-12 left-1/2 transform -translate-x-1/2">
                    <img src="{{ asset('assets/expense.jpg') }}" class="w-24 h-24 rounded-full border-8 border-white shadow-md">
                </div>

                <div class="flex items-center justify-center pt-16">
                    <h3 class="text-sm font-semibold">Add Expense</h3>
                </div>

                <div class="p-4">
                    <form method="POST" action="{{ route('dashboards.owner.expense_record_add') }}" class="space-y-4" enctype="multipart/form-data">
                        @csrf
                        <div class="flex space-x-4">
                            <div class="flex-1">
                                <label for="expense_descri" class="block mb-2 text-xs font-medium text-gray-900">Item / Purpose</label>
                                <input type="text" name="expense_descri" id="expense_descri" required
                                    class="border border-gray-300 text-gray-900 text-xs rounded focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" />
                            </div>
                            <div class="flex-1">
                                <label for="expense_category" class="block mb-2 text-xs font-medium text-gray-900">Expense Category</label>
                                <select name="expense_category" id="expense_category" required
                                    class="border border-gray-300 text-gray-900 text-xs rounded focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="" disabled selected>-- Select Category --</option>
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
                            </div>
                        </div>

                        <div class="flex space-x-4">
                            <div class="flex-1">
                                <label for="expense_amount" class="block mb-2 text-xs font-medium text-gray-900">Amount</label>
                                <input type="number" step="0.01" name="expense_amount" id="expense_amount" required
                                    class="border border-gray-300 text-gray-900 text-xs rounded focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" />
                            </div>

                            <div class="flex-1">
                                <label for="attachment" class="block mb-2 text-xs font-medium text-gray-900">Attachment</label>
                                <input type="file" name="attachment" id="attachment" accept=".pdf,.docx,.jpg,.jpeg,.png"
                                    class="border border-gray-300 text-gray-900 text-xs rounded focus:ring-blue-500 focus:border-blue-500 block w-full" />
                            </div>
                        </div>
                        <button type="submit" class="w-full text-white bg-red-600 hover:bg-red-700 font-medium rounded text-sm px-5 py-2.5 text-center">
                            Add Expense
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection


                        <!-- <div class="flex-1">
                            <label for="expense_date" class="block mb-2 text-xs font-medium text-gray-900">Date</label>
                            <input type="date" name="expense_date" id="expense_date"
                                class="border border-gray-300 text-gray-900 text-xs rounded focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" />
                        </div> -->
