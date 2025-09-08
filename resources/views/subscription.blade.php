<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Subscription</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body class="bg-white font-[Inter] text-gray-800 text-[14px]">

    <!-- PAYMENT MODAL -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-7 rounded-2xl shadow-2xl w-full max-w-lg relative border border-gray-200">
            <button onclick="closeModal()" class="absolute top-3 right-4 text-gray-500 hover:text-black text-xl">×</button>
            <h2 class="text-xl font-bold mb-5 text-center">Choose Payment Method</h2>

            <div class="space-y-4">
                <label class="block bg-gray-50 border rounded-lg px-4 py-3 hover:bg-gray-100 cursor-pointer">
                    <input type="radio" name="paymentMethod" value="gcash" onclick="toggleInput('gcash')" class="mr-2 accent-red-600"> GCash
                </label>
                <label class="block bg-gray-50 border rounded-lg px-4 py-3 hover:bg-gray-100 cursor-pointer">
                    <input type="radio" name="paymentMethod" value="debit" onclick="toggleInput('debit')" class="mr-2 accent-red-600"> Debit Card
                </label>
            </div>

            <div id="gcashInput" class="mt-5 hidden">
                <label class="block font-medium mb-2">GCash Number:</label>
                <input id="gcash-number" type="text" placeholder="09XXXXXXXXX" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500">
            </div>

            <div id="debitInput" class="mt-5 hidden">
                <label class="block font-medium mb-2">Card Number:</label>
                <input id="card-number" type="text" placeholder="XXXX-XXXX-XXXX-XXXX" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500">
            </div>

            <div class="mt-7 text-right">
                <form id="subscriptionForm" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" id="plan_id">
                    <input type="hidden" name="paymentMethod" id="selected_payment_method">
                    <input type="hidden" name="paymentAccNum" id="paymentAccNum">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">Confirm Payment</button>
                </form>
            </div>
        </div>
    </div>

    <!-- PAGE CONTENT -->
    <div class="min-h-screen flex flex-col justify-center items-center">
        <div class="w-full max-w-6xl pl-[120px] flex items-center space-x-3">
            <img src="{{ asset('assets/logo.png') }}" alt="Shoplytix Logo" class="w-12 h-12 object-contain" />
            <h1 class="text-[24px] font-bold text-red-600">SHOPLYTIX</h1>
        </div>

        <div class="max-w-6xl mx-auto px-4 py-8 flex flex-col md:flex-row md:justify-center md:space-x-8 space-y-6 md:space-y-0">

            <!-- Features -->
            <table class="w-[80%] bg-white rounded-xl shadow-md overflow-hidden">
                <thead>
                    <tr>
                        <th class="bg-yellow-300 text-left px-5 py-3 font-semibold text-[15px] rounded-t-xl">&nbsp;</th>
                    </tr>
                </thead>
                <tbody class="text-[15px]">
                    <tr class="border-b last:border-b-0">
                        <td class="px-5 py-3">&nbsp;</td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="px-5 py-3">Access to platform updates and system notices</td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="px-5 py-3">Restock suggestion list</td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="px-5 py-3">Daily sales tracking</td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="px-5 py-3">Advanced sales analytics (monthly, category breakdown)</td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="px-5 py-3">Comparative analysis of sales, losses, and profits</td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="px-5 py-3">Hold more than 1 staff</td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3">&nbsp;</td>
                    </tr>
                </tbody>
            </table>


            <!-- Basic Plan -->
            <table class="min-w-[160px] bg-white rounded-xl shadow-md overflow-hidden text-center">
                <thead>
                    <tr>
                        <th class="bg-orange-500 text-white px-3 py-3 font-semibold text-[15px] rounded-t-xl">Basic</th>
                    </tr>
                </thead>
                <tbody class="text-[15px]">
                    <tr>
                        <td class="text-orange-500 font-semibold py-3">₱250.00 / 6 months</td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="py-3"><i class="fas fa-circle-check text-green-500"></i></td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="py-3"><i class="fas fa-circle-check text-green-500"></i></td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="py-3"><i class="fas fa-circle-check text-green-500"></i></td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="py-3"><i class="fas fa-circle-xmark text-red-500"></i></td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="py-3"><i class="fas fa-circle-xmark text-red-500"></i></td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="py-3"><i class="fas fa-circle-xmark text-red-500"></i></td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="text-orange-500 font-semibold py-3 cursor-pointer underline" onclick="openModal(1)">Subscribe now!</td>
                    </tr>
                </tbody>
            </table>

            <!-- Premium Plan -->
            <table class="min-w-[160px] bg-white rounded-xl shadow-md overflow-hidden text-center">
                <thead>
                    <tr>
                        <th class="bg-red-600 text-white px-3 py-3 font-semibold text-[15px] rounded-t-xl">Premium</th>
                    </tr>
                </thead>
                <tbody class="text-[15px]">
                    <tr class="border-b last:border-b-0">
                        <td class="text-red-600 font-semibold py-3">₱500.00 / year</td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="py-3"><i class="fas fa-circle-check text-green-500"></i></td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="py-3"><i class="fas fa-circle-check text-green-500"></i></td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="py-3"><i class="fas fa-circle-check text-green-500"></i></td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="py-3"><i class="fas fa-circle-check text-green-500"></i></td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="py-3"><i class="fas fa-circle-check text-green-500"></i></td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="py-3"><i class="fas fa-circle-check text-green-500"></i></td>
                    </tr>
                    <tr class="border-b last:border-b-0">
                        <td class="text-red-600 font-semibold py-3 cursor-pointer underline" onclick="openModal(2)">Subscribe now!</td>
                    </tr>
                </tbody>
            </table>

        </div>

        <div class="mt-6 text-center w-full">
            Redirect to
            <a href="{{ route('subscription.progress') }}" class="text-blue-800 font-medium underline hover:text-blue-900">Status Tracker</a>
        </div>
    </div>

    <script>
        const modal = document.getElementById('paymentModal');
        const gcashInput = document.getElementById('gcashInput');
        const debitInput = document.getElementById('debitInput');
        const gcashNumber = document.getElementById('gcash-number');
        const cardNumber = document.getElementById('card-number');
        const planIdInput = document.getElementById('plan_id');
        const paymentMethodInput = document.getElementById('selected_payment_method');
        const paymentAccNumInput = document.getElementById('paymentAccNum');

        function openModal(planId) {
            modal.classList.remove('hidden');
            planIdInput.value = planId;
            document.getElementById('subscriptionForm').action = '/subscribe/' + planId;
        }

        function closeModal() {
            modal.classList.add('hidden');
            gcashInput.classList.add('hidden');
            debitInput.classList.add('hidden');
            gcashNumber.value = '';
            cardNumber.value = '';
            paymentMethodInput.value = '';
            paymentAccNumInput.value = '';
        }

        function toggleInput(type) {
            if (type === 'gcash') {
                gcashInput.classList.remove('hidden');
                debitInput.classList.add('hidden');
                paymentMethodInput.value = 'gcash';
                gcashNumber.oninput = e => paymentAccNumInput.value = e.target.value;
                cardNumber.value = '';
            } else {
                debitInput.classList.remove('hidden');
                gcashInput.classList.add('hidden');
                paymentMethodInput.value = 'debit';
                cardNumber.oninput = e => paymentAccNumInput.value = e.target.value;
                gcashNumber.value = '';
            }
        }
    </script>
</body>

</html>