<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Subscription</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        table td,
        table th {
            border-bottom: 1px solid #e5e7eb;
        }

        table tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>

<body class="bg-white text-gray-800">
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden text-[12px]">
        <div class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-md relative border border-gray-200">
            <button type="button" onclick="closeModal()" class="absolute top-2 right-3 text-gray-500 hover:text-black text-lg">×</button>
            <h2 class="text-lg font-bold mb-4 text-black text-center">Choose Payment Method</h2>

            <div class="space-y-3">
                <label class="block bg-gray-50 border rounded-md px-3 py-2 hover:bg-gray-100 transition">
                    <input type="radio" name="paymentMethod" value="gcash" onclick="showGcash()" class="mr-2 accent-red-600">
                    GCash
                </label>
                <label class="block bg-gray-50 border rounded-md px-3 py-2 hover:bg-gray-100 transition">
                    <input type="radio" name="paymentMethod" value="debit" onclick="showDebit()" class="mr-2 accent-red-600">
                    Debit Card
                </label>
            </div>

            <div id="gcashInput" class="mt-4 hidden">
                <label for="gcash-number" class="block font-medium mb-1">GCash Number:</label>
                <input id="gcash-number" type="text" name="paymentAccNum" class="w-full border border-gray-300 rounded px-3 py-2 text-[12px] focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="09XXXXXXXXX"> {{-- CHANGED name to paymentAccNum --}}
            </div>

            <div id="debitInput" class="mt-4 hidden">
                <label for="card-number" class="block font-medium mb-1">Card Number:</label>
                <input id="card-number" type="text" name="paymentAccNum" class="w-full border border-gray-300 rounded px-3 py-2 text-[12px] focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="XXXX-XXXX-XXXX-XXXX"> {{-- CHANGED name to paymentAccNum --}}
            </div>

            <div class="mt-6 text-right">
                <form id="subscriptionForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="plan_id" id="plan_id">
                    <input type="hidden" name="paymentMethod" id="selected_payment_method">
                    <input type="hidden" name="paymentAccNum" id="paymentAccNum">

                    <div id="gcashInput" class="hidden">
                        <input type="text" id="gcash-number" placeholder="GCash Number">
                    </div>

                    <div id="debitInput" class="hidden">
                        <input type="text" id="card-number" placeholder="Debit Card Number">
                    </div>

                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow-md">
                        Confirm Payment
                    </button>
                </form>

            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="max-w-6xl mx-auto pt-7" style="padding-left: 170px;">
        <div class="flex items-center space-x-2">
            <img src="{{ asset('assets/logo.png') }}" alt="Shoplytix Logo" class="w-10 h-10 object-contain" />
            <h1 class="text-[20px] font-bold text-red-600">SHOPLYTIX</h1>
        </div>
    </div>

    <!-- Pricing Tables -->
    <div class="max-w-6xl mx-auto px-4 py-6 flex flex-col md:flex-row md:justify-center md:space-x-6 space-y-6 md:space-y-0 text-xs">

        <!-- Features Table -->
        <table class="w-[45%] bg-white rounded-2xl shadow-md overflow-hidden">
            <thead>
                <tr>
                    <th class="bg-yellow-300 text-left px-4 py-2 font-semibold rounded-t-2xl">&nbsp;</th>

                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 py-2">&nbsp;</td>
                </tr>
                <tr>
                    <td class="px-4 py-2">Access to platform updates and system notices</td>
                </tr>
                <tr>
                    <td class="px-4 py-2">Restock suggestion list</td>
                </tr>
                <tr>
                    <td class="px-4 py-2">Daily sales tracking</td>
                </tr>
                <tr>
                    <td class="px-4 py-2">Advanced sales analytics (monthly, category breakdown)</td>
                </tr>
                <tr>
                    <td class="px-4 py-2">Comparative analysis of sales, losses, and profits</td>
                </tr>
                <tr>
                    <td class="px-4 py-2">Hold more than 1 staff</td>
                </tr>
                <tr>
                    <td class="px-4 py-2">&nbsp;</td>
                </tr>
            </tbody>
        </table>

        <!-- Basic Plan -->
        <table class="min-w-[130px] bg-white rounded-2xl shadow-md overflow-hidden text-center">
            <thead>
                <tr>
                    <th class="bg-orange-500 text-white px-2 py-2 font-semibold rounded-t-2xl">Basic</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-orange-500 font-semibold py-2">₱250.00 / 6 months</td>
                </tr>
                <tr>
                    <td class="py-2"><i class="fas fa-circle-check text-green-500"></i></td>
                </tr>
                <tr>
                    <td class="py-2"><i class="fas fa-circle-check text-green-500 "></i></td>
                </tr>
                <tr>
                    <td class="py-2"><i class="fas fa-circle-check text-green-500 "></i></td>
                </tr>
                <tr>
                    <td class="py-2"><i class="fas fa-circle-xmark text-red-500 "></i></td>
                </tr>
                <tr>
                    <td class="py-2"><i class="fas fa-circle-xmark text-red-500 "></i></td>
                </tr>
                <tr>
                    <td class="py-2"><i class="fas fa-circle-xmark text-red-500 "></i></td>
                </tr>
                <tr>
                    <td class="text-orange-500 font-semibold py-2 cursor-pointer" onclick="openModal(1)">Subscribe now!</td>
                </tr>
            </tbody>
        </table>

        <!-- Premium Plan -->
        <table class="min-w-[130px] bg-white rounded-2xl shadow-md overflow-hidden text-center">
            <thead>
                <tr>
                    <th class="bg-red-600 text-white px-2 py-2 font-semibold rounded-t-2xl">Premium</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-red-600 font-semibold py-2">₱500.00 / year</td>
                </tr>
                <tr>
                    <td class="py-2"><i class="fas fa-circle-check text-green-500 "></i></td>
                </tr>
                <tr>
                    <td class="py-2"><i class="fas fa-circle-check text-green-500"></i></td>
                </tr>
                <tr>
                    <td class="py-2"><i class="fas fa-circle-check text-green-500"></i></td>
                </tr>
                <tr>
                    <td class="py-2"><i class="fas fa-circle-check text-green-500 "></i></td>
                </tr>
                <tr>
                    <td class="py-2"><i class="fas fa-circle-check text-green-500 "></i></td>
                </tr>
                <tr>
                    <td class="py-2"><i class="fas fa-circle-check text-green-500 "></i></td>
                </tr>
                <tr>
                    <td class="text-red-600 font-semibold py-2 cursor-pointer" onclick="openModal(2)">Subscribe now!</td>

                </tr>
            </tbody>
        </table>

    </div>



</body>

<script>
    function openModal(planId) {
        document.getElementById('paymentModal').classList.remove('hidden');
        document.getElementById('plan_id').value = planId;
        document.getElementById('subscriptionForm').action = '/subscribe/' + planId;
    }

    function closeModal() {
        document.getElementById('paymentModal').classList.add('hidden');

        // Hide inputs
        document.getElementById('gcashInput').classList.add('hidden');
        document.getElementById('debitInput').classList.add('hidden');

        // Clear values
        document.getElementById('gcash-number').value = '';
        document.getElementById('card-number').value = '';
        document.getElementById('paymentAccNum').value = '';
        document.getElementById('selected_payment_method').value = '';
    }

    function showGcash() {
        document.getElementById('gcashInput').classList.remove('hidden');
        document.getElementById('debitInput').classList.add('hidden');

        document.getElementById('selected_payment_method').value = 'gcash';

        // Update paymentAccNum whenever user types
        const gcashInput = document.getElementById('gcash-number');
        gcashInput.addEventListener('input', function() {
            document.getElementById('paymentAccNum').value = this.value;
        });

        // Clear the other input
        document.getElementById('card-number').value = '';
    }

    function showDebit() {
        document.getElementById('debitInput').classList.remove('hidden');
        document.getElementById('gcashInput').classList.add('hidden');

        document.getElementById('selected_payment_method').value = 'debit';

        // Update paymentAccNum whenever user types
        const debitInput = document.getElementById('card-number');
        debitInput.addEventListener('input', function() {
            document.getElementById('paymentAccNum').value = this.value;
        });

        // Clear the other input
        document.getElementById('gcash-number').value = '';
    }
</script>


</html>