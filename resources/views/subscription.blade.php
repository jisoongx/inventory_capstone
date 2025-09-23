<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Subscription Plans</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 text-gray-800 text-[14px] antialiased">

    <!-- PAYMENT MODAL -->
    <div id="paymentModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden transition-opacity duration-300 ease-in-out opacity-0">
        <div class="bg-white p-7 rounded-lg shadow-xl w-full max-w-lg relative border border-gray-200 transform scale-95 transition-transform duration-300 ease-out">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl transition-colors duration-200">
                <i class="fas fa-times"></i>
            </button>
            <h2 class="text-2xl font-semibold mb-6 text-center text-gray-800">Choose Payment Method</h2>

            <div class="space-y-4">
                <label class="block bg-gray-100 border border-gray-200 rounded-lg px-4 py-3 flex items-center cursor-pointer hover:bg-gray-200 transition-colors duration-200">
                    <input type="radio" name="paymentMethod" value="gcash" onclick="toggleInput('gcash')" class="mr-3 accent-red-600 w-4 h-4">
                    <span class="font-medium text-gray-700">GCash</span>
                </label>
                <label class="block bg-gray-100 border border-gray-200 rounded-lg px-4 py-3 flex items-center cursor-pointer hover:bg-gray-200 transition-colors duration-200">
                    <input type="radio" name="paymentMethod" value="debit" onclick="toggleInput('debit')" class="mr-3 accent-red-600 w-4 h-4">
                    <span class="font-medium text-gray-700">Debit Card</span>
                </label>
            </div>

            <div id="gcashInput" class="mt-6 hidden">
                <label for="gcash-number" class="block font-medium mb-2 text-gray-700">GCash Number:</label>
                <input id="gcash-number" type="text" placeholder="09XXXXXXXXX" class="w-full border border-gray-300 rounded-md px-4 py-2.5 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200">
            </div>

            <div id="debitInput" class="mt-6 hidden">
                <label for="card-number" class="block font-medium mb-2 text-gray-700">Card Number:</label>
                <input id="card-number" type="text" placeholder="XXXX-XXXX-XXXX-XXXX" class="w-full border border-gray-300 rounded-md px-4 py-2.5 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200">
            </div>

            <div class="mt-8 text-right">
                <form id="subscriptionForm" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" id="plan_id">
                    <input type="hidden" name="paymentMethod" id="selected_payment_method">
                    <input type="hidden" name="paymentAccNum" id="paymentAccNum">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-7 py-3 rounded-md font-semibold text-base transition-colors duration-200 shadow-md hover:shadow-lg">
                        Confirm Payment
                    </button>
                </form>
            </div>

            {{-- SUCCESS VIEW --}}
            <div id="successView" class="text-center hidden">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-rounded text-4xl text-green-500">task_alt</span>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Payment Successful!</h2>
                <p class="text-sm text-gray-600 mb-5">Your subscription is now active.</p>
                <a href="{{ route('dashboards.owner.dashboard') }}" class="w-full inline-block bg-green-500 hover:bg-green-600 text-white font-semibold py-2.5 px-6 rounded-lg transition-all text-sm">Go to Dashboard</a>
            </div>

        </div>
    </div>

    <!-- PAGE CONTENT -->
    <div class="min-h-screen flex flex-col justify-center items-center py-10 px-4">
        <div class="w-full max-w-6xl mb-10 flex items-center space-x-3">
            <img src="{{ asset('assets/logo.png') }}" alt="Shoplytix Logo" class="w-14 h-14 object-contain" />
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">SHOPLYTIX</h1>
        </div>

        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row lg:justify-center w-full border border-gray-200 rounded-lg overflow-hidden shadow-lg">

            <!-- Features -->
            <table class="w-full lg:w-[40%] bg-white rounded-none">
                <thead>
                    <tr>
                        <th class="bg-gray-100 text-left px-6 py-4 font-semibold text-base text-gray-700">Features</th>
                    </tr>
                </thead>
                <tbody class="text-base text-gray-700">
                    <tr class="border-b border-gray-100 last:border-b-0">
                        <td class="px-6 py-3.5">&nbsp;</td>
                    </tr>
                    <tr class="border-b border-gray-100 last:border-b-0">
                        <td class="px-6 py-3.5">Access to platform updates and system notices</td>
                    </tr>
                    <tr class="border-b border-gray-100 last:border-b-0">
                        <td class="px-6 py-3.5">Restock suggestion list</td>
                    </tr>
                    <tr class="border-b border-gray-100 last:border-b-0">
                        <td class="px-6 py-3.5">Daily sales tracking</td>
                    </tr>
                    <tr class="border-b border-gray-100 last:border-b-0">
                        <td class="px-6 py-3.5">Advanced sales analytics (monthly, category breakdown)</td>
                    </tr>
                    <tr class="border-b border-gray-100 last:border-b-0">
                        <td class="px-6 py-3.5">Comparative analysis of sales, losses, and profits</td>
                    </tr>
                    <tr class="border-b border-gray-100 last:border-b-0">
                        <td class="px-6 py-3.5">Hold more than 1 staff</td>
                    </tr>
                    <tr class="border-b border-gray-100 last:border-b-0">
                        <td class="px-6 py-3.5">&nbsp;</td>
                    </tr>
                </tbody>
            </table>


            <!-- Basic Plan -->
            <div class="w-full lg:w-[28%] bg-white border-l border-gray-200 flex flex-col relative z-10 transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1">
                <div class="bg-gray-800 text-white px-3 py-4 font-semibold text-lg text-center flex-shrink-0">Basic</div>
                <div class="flex flex-col flex-grow text-center text-gray-700">
                    <div class="text-red-600 font-bold py-3 text-lg flex-shrink-0">₱250.00 / 6 months</div>
                    <ul class="flex-grow">
                        <li class="py-3 border-b border-gray-100"><i class="fas fa-check-circle text-green-500 text-lg"></i></li>
                        <li class="py-3 border-b border-gray-100"><i class="fas fa-check-circle text-green-500 text-lg"></i></li>
                        <li class="py-3 border-b border-gray-100"><i class="fas fa-check-circle text-green-500 text-lg"></i></li>
                        <li class="py-3 border-b border-gray-100"><i class="fas fa-times-circle text-gray-400 text-lg"></i></li>
                        <li class="py-3 border-b border-gray-100"><i class="fas fa-times-circle text-gray-400 text-lg"></i></li>
                        <li class="py-3 border-b border-gray-100"><i class="fas fa-times-circle text-gray-400 text-lg"></i></li>
                        <li class="py-3 border-b border-gray-100"><i class="fas fa-times-circle text-gray-400 text-lg"></i></li>
                    </ul>
                    <button onclick="openModal(1)" class="bg-red-600 text-white px-5 py-3 font-semibold text-base transition-colors duration-200 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 mt-auto">
                        Subscribe Now
                    </button>
                </div>
            </div>

            <!-- Premium Plan -->
            <div class="w-full lg:w-[28%] bg-white border-l border-red-500 flex flex-col relative z-10 transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-red-600"></div> <!-- Accent bar -->
                <div class="bg-red-600 text-white px-3 py-4 font-semibold text-lg text-center relative z-10">Premium</div>
                <div class="flex flex-col flex-grow text-center text-gray-700">
                    <div class="text-red-600 font-bold py-3 text-lg flex-shrink-0">₱500.00 / year</div>
                    <ul class="flex-grow">
                        <li class="py-3 border-b border-gray-100"><i class="fas fa-check-circle text-green-500 text-lg"></i></li>
                        <li class="py-3 border-b border-gray-100"><i class="fas fa-check-circle text-green-500 text-lg"></i></li>
                        <li class="py-3 border-b border-gray-100"><i class="fas fa-check-circle text-green-500 text-lg"></i></li>
                        <li class="py-3 border-b border-gray-100"><i class="fas fa-check-circle text-green-500 text-lg"></i></li>
                        <li class="py-3 border-b border-gray-100"><i class="fas fa-check-circle text-green-500 text-lg"></i></li>
                        <li class="py-3 border-b border-gray-100"><i class="fas fa-check-circle text-green-500 text-lg"></i></li>
                        <li class="py-3 border-b border-gray-100"><i class="fas fa-check-circle text-green-500 text-lg"></i></li>
                    </ul>
                    <button onclick="openModal(2)" class="bg-red-600 text-white px-5 py-3 font-semibold text-base transition-colors duration-200 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 mt-auto">
                        Subscribe Now
                    </button>
                </div>
            </div>

        </div>

        <div class="mt-12 text-center w-full text-gray-600">
            Redirect to
            <a href="{{ route('subscription.progress') }}" class="text-blue-700 font-medium underline hover:text-blue-800 transition-colors duration-200">Status Tracker</a>
        </div>
    </div>

    <script>
        const modal = document.getElementById('paymentModal');
        const modalContent = modal.querySelector('.bg-white'); // Get the modal content div
        const gcashInput = document.getElementById('gcashInput');
        const debitInput = document.getElementById('debitInput');
        const gcashNumber = document.getElementById('gcash-number');
        const cardNumber = document.getElementById('card-number');
        const planIdInput = document.getElementById('plan_id');
        const paymentMethodInput = document.getElementById('selected_payment_method');
        const paymentAccNumInput = document.getElementById('paymentAccNum');

        function openModal(planId) {
            modal.classList.remove('hidden');
            // Animate in
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
            }, 10); // Small delay to allow 'hidden' removal to register

            planIdInput.value = planId;
            document.getElementById('subscriptionForm').action = '/subscribe/' + planId;
        }

        function closeModal() {
            // Animate out
            modal.classList.add('opacity-0');
            modalContent.classList.add('scale-95');
            modal.addEventListener('transitionend', function handler() {
                modal.classList.add('hidden');
                modal.removeEventListener('transitionend', handler); // Clean up listener
            });

            gcashInput.classList.add('hidden');
            debitInput.classList.add('hidden');
            gcashNumber.value = '';
            cardNumber.value = '';
            paymentMethodInput.value = '';
            paymentAccNumInput.value = '';
        }

            const setButtonState = (state, text) => {
                const btn = elements.buttons.submit;
                btn.disabled = (state === 'loading' || state === 'disabled');
                elements.ui.spinner.classList.toggle('hidden', state !== 'loading');
                elements.ui.buttonText.textContent = text;

                if (state === 'disabled') {
                    btn.className = 'w-full text-white px-4 py-3 rounded-lg font-semibold text-sm flex items-center justify-center bg-gray-400 cursor-not-allowed';
                } else if (state !== 'loading') {
                    const theme = btn.dataset.theme;
                    btn.className = `w-full text-white px-4 py-3 rounded-lg font-semibold text-sm flex items-center justify-center bg-${theme}-500 hover:bg-${theme}-600`;
                }
            };

            const resetForm = () => {
                elements.form.reset();
                Object.values(elements.inputs).forEach(input => {
                    if (typeof input.value !== 'undefined') input.value = '';
                    if (input.id.includes('Input')) input.classList.add('hidden');
                });
                Object.values(elements.errors).forEach(err => err.classList.add('hidden'));
                setButtonState('disabled', 'Select a payment method');
            };

            const toggleInput = (type) => {
                clearErrors();
                setButtonState('active', `Pay ₱${elements.buttons.submit.dataset.price}`);
                elements.inputs.paymentMethod.value = type;
                elements.inputs.gcash.classList.toggle('hidden', type !== 'gcash');
                elements.inputs.debit.classList.toggle('hidden', type !== 'debit');
                elements.inputs.paymentAccNum.value = (type === 'gcash') ? elements.inputs.gcashNumber.value : elements.inputs.cardNumber.value.replace(/\s+/g, '');
            };

            const showError = (errorElem, inputElem, message) => {
                errorElem.textContent = message;
                errorElem.classList.remove('hidden');
                inputElem.classList.add('border-red-500', 'focus:ring-red-400');
                inputElem.classList.remove('border-gray-300');
            };

            const clearErrors = () => {
                Object.values(elements.errors).forEach(el => el.classList.add('hidden'));
                [elements.inputs.gcashNumber, elements.inputs.cardNumber].forEach(el => {
                    el.classList.remove('border-red-500', 'focus:ring-red-400');
                    el.classList.add('border-gray-300');
                });
            };

            // --- EVENT LISTENERS ---
            elements.buttons.getStarted.addEventListener('click', () => openModal(1, 'Basic', 250, 'orange'));
            elements.buttons.goPremium.addEventListener('click', () => openModal(2, 'Premium', 500, 'red'));
            elements.buttons.closeModal.addEventListener('click', closeModal);
            elements.radios.forEach(radio => radio.addEventListener('change', () => toggleInput(radio.value)));

            elements.inputs.gcashNumber.addEventListener('input', e => {
                e.target.value = e.target.value.replace(/\D/g, '');
                elements.inputs.paymentAccNum.value = e.target.value;
            });
            elements.inputs.cardNumber.addEventListener('input', e => {
                let value = e.target.value.replace(/\D/g, '');
                e.target.value = value.replace(/(.{4})/g, '$1 ').trim();
                elements.inputs.paymentAccNum.value = value;
            });

            elements.form.addEventListener('submit', async function(event) {
                event.preventDefault();
                clearErrors();
                let isValid = true;

                const method = elements.inputs.paymentMethod.value;
                const accNum = elements.inputs.paymentAccNum.value;

                if (!method) {
                    isValid = false;
                    alert('Please select a payment method.');
                } else if (method === 'gcash' && !/^\d{11}$/.test(accNum)) {
                    isValid = false;
                    showError(elements.errors.gcash, elements.inputs.gcashNumber, 'Please enter a valid 11-digit GCash number.');
                } else if (method === 'debit' && !/^\d{16}$/.test(accNum)) {
                    isValid = false;
                    showError(elements.errors.card, elements.inputs.cardNumber, 'Please enter a valid 16-digit card number.');
                }
                if (!isValid) return;

                setButtonState('loading', 'Processing...');

                try {
                    const response = await fetch(elements.form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(elements.form)
                    });
                    const result = await response.json();

                    if (response.ok) {
                        elements.paymentFormView.classList.add('hidden');
                        elements.successView.classList.remove('hidden');
                    } else {
                        alert(`Error: ${result.message || 'An unknown error occurred.'}`);
                        setButtonState('active', `Pay ₱${elements.buttons.submit.dataset.price}`);
                    }
                } catch (error) {
                    alert('A network error occurred. Please try again.');
                    setButtonState('active', `Pay ₱${elements.buttons.submit.dataset.price}`);
                }
            });

            elements.modal.addEventListener('click', e => {
                if (e.target === elements.modal) closeModal();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && !elements.modal.classList.contains('hidden')) closeModal();
            });
    </script>
</body>

</html>