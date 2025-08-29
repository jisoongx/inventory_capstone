@extends('dashboards.owner.owner') 

@section('content')

    <div class="flex-1 grid grid-cols-3 gap-4 p-2">
        <div class="h-[40rem] bg-white shadow-lg p-5 rounded-lg col-span-2 flex flex-col">    
            <div class="text-center mb-4">
                <p class="text-xs text-gray-700">Year {{ $now->format('Y') }}</p>
                <div class="flex justify-center items-center space-x-7 mt-2 p-2 bg-slate-100">
                    <button class="text-gray-500"><span class="material-symbols-rounded text-4xl">arrow_left</span></button>
                        <span class="text-sm font-semibold">{{ $now->format('F') }}</span>
                    <button class="text-gray-500"><span class="material-symbols-rounded">arrow_right</span></button>
                </div>
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
                                <form method="POST" action="{{ route('dashboards.owner.monthly_profit_edit', ['expense_id' => $input->expense_id]) }}">
                                @csrf
                                    <td class="p-2">
                                        <span class="view font-medium p-2">{{ $input->expense_descri }}</span>
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
                <p><span class="font-semibold text-gray-700">Total In-Store Expenses:</span> <span class="text-black float-right">₱{{ number_format($expenseTotal->expenseTotal, 2) }}</span></p>
                <p><span class="font-semibold text-red-600">Month's Net Profit:</span> <span class="text-red-600 float-right font-bold">₱{{ number_format($salesTotal->salesTotal - $expenseTotal->expenseTotal, 2) }}</span></p>
            </div>
        </div>

        <div class="h-[40rem] bg-white shadow-lg p-4 rounded-lg flex flex-col relative space-y-5">
            <p class="font-semibold text-sm">Insights</p>
            <div class="border-blue-500 border-l-4">
                <p class="ml-2 text-xs">Your profit margin was 25%, up 5% from last month.</p>
            </div>
            <div class="border-blue-500 border-l-4">
                <p class="ml-2 text-xs">Compared to August 2024, net profit increased by ₱20,000.</p>
            </div>
            <div class="border-blue-500 border-l-4">
                <p class="ml-2 text-xs">You covered all expenses by the 18th of the month. You covered all expenses by the 18th of the month. You covered all expenses by the 18th of the month.</p>
            </div>
        </div>

    </div>

    <div id="add-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed z-50 inset-0 justify-center items-center w-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded shadow-sm">
                <div class="flex items-center justify-between p-3 border-b rounded border-gray-200">
                    <h3 class="text-sm font-semibold">
                        Add Expense
                    </h3>
                    <button type="button" class="end-2.5 bg-transparent rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center hover:text-gray-700" data-modal-hide="add-modal">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                
                <div class="p-4">
                    <form method="POST" action="{{ route('dashboards.owner.monthly_profit_add') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="expense_descri" class="block mb-2 text-xs font-medium text-gray-900">Item / Purpose</label>
                            <input type="text" name="expense_descri" id="expense_descri" required
                                class="border border-gray-300 text-gray-900 text-sm rounded focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" />
                        </div>

                        <div>
                            <label for="expense_amount" class="block mb-2 text-xs font-medium text-gray-900">Amount</label>
                            <input type="number" step="0.01" name="expense_amount" id="expense_amount" required
                                class="border border-gray-300 text-gray-900 text-sm rounded focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" />
                        </div>

                        <button type="submit" class="w-full text-white bg-green-600 hover:bg-green-700 font-medium rounded text-sm px-5 py-2.5 text-center">
                            Add Expense
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div> 

@endsection
