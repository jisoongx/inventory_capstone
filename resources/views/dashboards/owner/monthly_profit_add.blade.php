@extends('dashboards.owner.owner') 

@section('content')

        <div class="grid gap-5 p-2">
            <div class="bg-white rounded-lg shadow p-6 w-full">     
                <div class="text-center mb-4">
                    <p class="text-sm text-gray-700 mb-3">Year {{ $now->format('Y') }}</p>
                    <div class="flex justify-center items-center space-x-7 mt-1 p-2 bg-slate-100">
                        <button class="text-gray-500"><span class="material-symbols-rounded text-4xl">arrow_left</span></button>
                            <span class="text-lg font-semibold">{{ $now->format('F') }}</span>
                        <button class="text-gray-500"><span class="material-symbols-rounded text-4xl">arrow_right</span></button>
                    </div>
                </div>

                <table class="w-full mt-4 text-sm text-left text-gray-700">

                    <thead class="uppercase text-xs border-y font-semibold">
                        <tr>
                            <th class="p-4">Title</th>
                            <th class="p-4">Cost</th>
                            <th class="p-4">Date Recorded</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php 
                            $currentMonth = (int)date('n');
                        @endphp

                        @if ($currentMonth == $latestMonth && count($inputs) > 0)
                            @foreach ($inputs as $input)
                                <tr class="text-sm text-gray-700">
                                    <td class="p-3">{{ $input->expense_descri }}</td>
                                    <td class="p-3">{{ $input->expense_amount }}</td>
                                    <td class="p-3">{{ date('F j', strtotime($input->expense_created)) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="p-5 text-center font-semibold text-orange-700">
                                    No records available at this time.
                                </td>
                            </tr>
                        @endif
                    </tbody>

                </table>

                <button data-modal-target="add-modal" data-modal-toggle="add-modal" class="text-xs text-blue-600 mt-3 text-right font-bold" type="button">
                    + Add record
                </button>

                <div id="add-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed z-50 inset-0 justify-center items-center w-full">
                    <div class="relative p-4 w-full max-w-md max-h-full">
                        <div class="relative bg-white rounded shadow-sm">
                            <div class="flex items-center justify-between p-3 border-b rounded border-gray-200 bg-red-700">
                                <h3 class="text-sm font-semibold text-white">
                                    Add Expense
                                </h3>
                                <button type="button" class="end-2.5 text-white bg-transparent rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-hide="add-modal">
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

                                    <button type="submit" class="w-full text-white bg-green-600 hover:bg-green-800 font-medium rounded text-sm px-5 py-2.5 text-center">
                                        Add Expense
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div> 


                <div class="bg-blue-100 rounded-md p-4 mt-5 text-sm space-y-2">
                    <p><span class="font-semibold">Current Sales:</span> <span class="text-black float-right">₱{{ number_format($salesTotal->salesTotal, 2) }}</span></p>
                    <p><span class="font-semibold text-gray-700">Total In-Store Expenses:</span> <span class="text-black float-right">₱{{ number_format($expenseTotal->expenseTotal, 2) }}</span></p>
                    <p><span class="font-semibold text-red-600">Month's Net Profit:</span> <span class="text-red-600 float-right font-bold">₱{{ number_format($salesTotal->salesTotal - $expenseTotal->expenseTotal, 2) }}</span></p>
                </div>
            </div>

        </div>

        <!-- list -->
        <div class="grid gap-5 p-2">
            <div class="bg-white rounded-lg shadow p-6 w-full">     
                <p class="font-bold">Previous Records</p>

                <table class="w-full mt-4 text-sm text-left text-gray-700">
                    <thead class="uppercase text-xs border-y font-semibold bg-blue-200">
                        <tr>
                            <th>Months</th>
                            <th>In-Store Expenses</th>
                            <th>Sales</th>
                            <th class="p-4 w-96">Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const reportsToggle = document.getElementById('reportsToggle');
        const reportsDropdown = document.getElementById('reportsDropdown');
        const labels = document.querySelectorAll('.report-label');

        reportsToggle.addEventListener('click', () => {
            reportsDropdown.classList.toggle('hidden');
            labels.forEach(label => label.classList.remove('hidden'));
        });        
    </script>

@endsection
