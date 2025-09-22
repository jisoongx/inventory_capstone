<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Subscription Plans</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-scale-in {
            animation: scaleIn 0.3s ease-out forwards;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            animation: spin 0.6s linear infinite;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-700 via-red-800 to-black font-sans flex items-center justify-center p-4">

    {{-- A SINGLE MODAL that handles both Payment and Success states --}}
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">
        <div id="modalContent" class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-md relative animate-scale-in">

            {{-- PAYMENT VIEW --}}
            <div id="paymentFormView">
                <button id="closeModalBtn" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-rounded">close</span>
                </button>
                <div class="text-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-1" id="modalPlanTitle">Complete Your Purchase</h2>
                    <p class="text-sm text-gray-500">Choose your preferred payment method.</p>
                </div>
                <form id="subscriptionForm" method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="plan_id" id="plan_id">
                    <input type="hidden" name="paymentMethod" id="selected_payment_method">
                    <input type="hidden" name="paymentAccNum" id="paymentAccNum">

                    <div class="space-y-3 mb-6">
                        <label class="block p-4 rounded-lg border-2 border-gray-200 cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 transition-all">
                            <div class="flex items-center">
                                <input type="radio" name="paymentOption" value="gcash" class="w-5 h-5 accent-blue-600">
                                <div class="ml-4 flex-1">
                                    <p class="font-semibold text-gray-800">GCash</p>
                                    <p class="text-xs text-gray-500">Pay with your GCash wallet</p>
                                </div>
                                <span class="material-symbols-rounded text-3xl text-blue-500">account_balance_wallet</span>
                            </div>
                        </label>
                        <label class="block p-4 rounded-lg border-2 border-gray-200 cursor-pointer has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 transition-all">
                            <div class="flex items-center">
                                <input type="radio" name="paymentOption" value="debit" class="w-5 h-5 accent-indigo-600">
                                <div class="ml-4 flex-1">
                                    <p class="font-semibold text-gray-800">Debit/Credit Card</p>
                                    <p class="text-xs text-gray-500">Visa, Mastercard, etc.</p>
                                </div>
                                <span class="material-symbols-rounded text-3xl text-indigo-500">credit_card</span>
                            </div>
                        </label>
                    </div>

                    <div id="gcashInput" class="mb-4 hidden">
                        <input id="gcash-number" type="tel" placeholder="09XXXXXXXXX" maxlength="11" class="w-full border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all text-sm" />
                        <p id="gcash-error" class="text-red-500 text-xs mt-1 ml-1 hidden"></p>
                    </div>
                    <div id="debitInput" class="mb-4 hidden">
                        <input id="card-number" type="tel" placeholder="XXXX XXXX XXXX XXXX" maxlength="19" class="w-full border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all text-sm" />
                        <p id="card-error" class="text-red-500 text-xs mt-1 ml-1 hidden"></p>
                    </div>

                    <button type="submit" id="modalSubmitButton" class="w-full text-white px-4 py-3 rounded-lg font-semibold text-sm transition-all flex items-center justify-center bg-gray-400 cursor-not-allowed" disabled>
                        <span id="buttonText">Select a payment method</span>
                        <div id="loadingSpinner" class="w-5 h-5 rounded-full spinner hidden ml-2"></div>
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

    {{-- Main Page Content --}}
    <div class="container mx-auto py-10 px-4">
        <header class="text-center mb-10">
            <h1 class="text-3xl font-extrabold text-white mb-2">Choose Your Plan</h1>
            <p class="text-md text-gray-300">Select the perfect plan to grow your business.</p>
        </header>
        <main class="flex flex-col lg:flex-row justify-center items-center gap-6">
            {{-- Basic Plan --}}
            <div class="bg-white w-full max-w-xs rounded-2xl shadow-lg border border-gray-200 p-6 transition-all hover:shadow-2xl hover:-translate-y-1">
                <h3 class="text-xl font-bold text-orange-500">Basic</h3>
                <p class="text-gray-500 text-sm mt-2 mb-5">Ideal for getting started and organizing your sales.</p>
                <div class="mb-5"><span class="text-4xl font-extrabold text-gray-900">₱250</span><span class="text-gray-500 font-medium text-sm"> / 6 months</span></div>
                <button id="getStartedBtn" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-lg transition-all">Get Started</button>
                <ul class="mt-6 space-y-3 text-sm">
                    <li class="flex items-center"><span class="material-symbols-rounded text-green-500 mr-2">check_circle</span>Access to platform updates</li>
                    <li class="flex items-center"><span class="material-symbols-rounded text-green-500 mr-2">check_circle</span>Restock suggestion list</li>
                    <li class="flex items-center"><span class="material-symbols-rounded text-green-500 mr-2">check_circle</span>Daily sales tracking</li>
                    <li class="flex items-center text-gray-400"><span class="material-symbols-rounded text-gray-400 mr-2">cancel</span>Advance sales analytics</li>
                    <li class="flex items-center text-gray-400"><span class="material-symbols-rounded text-gray-400 mr-2">cancel</span>Comparative analysis</li>
                    <li class="flex items-center text-gray-400"><span class="material-symbols-rounded text-gray-400 mr-2">cancel</span>Unlimited staff accounts</li>
                </ul>
            </div>
            {{-- Premium Plan --}}
            <div class="bg-white w-full max-w-xs rounded-2xl shadow-xl border-2 border-red-500 p-6 transition-all hover:shadow-2xl hover:-translate-y-1 relative">
                <div class="absolute top-0 -translate-y-1/2 left-1/2 -translate-x-1/2 bg-red-500 text-white text-xs font-bold px-4 py-1 rounded-full">MOST POPULAR</div>
                <h3 class="text-xl font-bold text-red-500">Premium</h3>
                <p class="text-gray-500 text-sm mt-2 mb-5">For power users who need advanced insights and tools.</p>
                <div class="mb-5"><span class="text-4xl font-extrabold text-gray-900">₱500</span><span class="text-gray-500 font-medium text-sm"> / year</span></div>
                <button id="goPremiumBtn" class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 rounded-lg transition-all">Go Premium</button>
                <ul class="mt-6 space-y-3 text-sm">
                    <li class="flex items-center"><span class="material-symbols-rounded text-green-500 mr-2">check_circle</span>Access to platform updates</li>
                    <li class="flex items-center"><span class="material-symbols-rounded text-green-500 mr-2">check_circle</span>Restock suggestion list</li>
                    <li class="flex items-center"><span class="material-symbols-rounded text-green-500 mr-2">check_circle</span>Daily sales tracking</li>
                    <li class="flex items-center"><span class="material-symbols-rounded text-green-500 mr-2">check_circle</span>Advance sales analytics</li>
                    <li class="flex items-center"><span class="material-symbols-rounded text-green-500 mr-2">check_circle</span>Comparative analysis</li>
                    <li class="flex items-center"><span class="material-symbols-rounded text-green-500 mr-2">check_circle</span>Unlimited staff accounts</li>
                </ul>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- ELEMENT SELECTORS ---
            const elements = {
                modal: document.getElementById('paymentModal'),
                paymentFormView: document.getElementById('paymentFormView'),
                successView: document.getElementById('successView'),
                form: document.getElementById('subscriptionForm'),
                inputs: {
                    gcash: document.getElementById('gcashInput'),
                    debit: document.getElementById('debitInput'),
                    gcashNumber: document.getElementById('gcash-number'),
                    cardNumber: document.getElementById('card-number'),
                    planId: document.getElementById('plan_id'),
                    paymentMethod: document.getElementById('selected_payment_method'),
                    paymentAccNum: document.getElementById('paymentAccNum'),
                },
                errors: {
                    gcash: document.getElementById('gcash-error'),
                    card: document.getElementById('card-error'),
                },
                buttons: {
                    getStarted: document.getElementById('getStartedBtn'),
                    goPremium: document.getElementById('goPremiumBtn'),
                    closeModal: document.getElementById('closeModalBtn'),
                    submit: document.getElementById('modalSubmitButton'),
                },
                ui: {
                    modalTitle: document.getElementById('modalPlanTitle'),
                    buttonText: document.getElementById('buttonText'),
                    spinner: document.getElementById('loadingSpinner'),
                },
                radios: document.querySelectorAll('input[name="paymentOption"]'),
            };

            // --- FUNCTIONS ---
            const openModal = (planId, planName, planPrice, themeColor) => {
                elements.paymentFormView.classList.remove('hidden');
                elements.successView.classList.add('hidden');
                elements.modal.classList.remove('hidden');
                elements.inputs.planId.value = planId;
                elements.form.action = `/subscribe/${planId}`;
                elements.ui.modalTitle.textContent = `Subscribe to ${planName}`;
                elements.buttons.submit.dataset.price = planPrice;
                elements.buttons.submit.dataset.theme = themeColor;
            };

            const closeModal = () => {
                elements.modal.classList.add('hidden');
                resetForm();
            };

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
        });
    </script>
</body>

</html>