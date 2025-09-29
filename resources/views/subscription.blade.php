<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shoplytix Subscription Plans</title>
    <script src="https://www.sandbox.paypal.com/sdk/js?client-id=AfhEjCadB5uvvu8lW7Q1kxNbo9uyBkd0OCtDIlTgw-mid22lSDCUDPhd7YWEeHydaQVwjjyCpiyyrGuW&currency=PHP"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">



    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .scale-container {
            zoom: 0.8;
            /* works in Chrome, Edge, Safari */
        }

        #modalContent {
            max-height: 90vh;
            /* never exceeds viewport height */
            overflow-y: auto;
            /* scroll only if content overflows */
            transition: max-width 0.3s, width 0.3s;
            /* smooth resizing */
        }

        /* Optional: make scrollbar subtle */
        #modalContent::-webkit-scrollbar {
            width: 6px;
            background: transparent;
        }

        #modalContent::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }
    </style>


</head>

<body class="bg-gradient-to-br from-gray-700 via-red-800 to-black text-slate-800 antialiased">
    <div class="scale-container">
        <div id="paymentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden opacity-0 transition-opacity duration-300">
            <div id="modalContent" class="bg-white rounded-xl shadow-2xl w-full max-w-lg transform scale-95 transition-all duration-300">

                <div id="paymentFormView">
                    <div class="p-6 sm:p-8 border-b border-slate-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-2xl font-bold text-slate-900">Confirm Purchase</h2>
                                <p class="text-sm text-slate-500 mt-1">You're one step away from unlocking new features.</p>
                            </div>
                            <button id="closeModalBtn" class="text-slate-400 hover:text-red-600 transition-colors">
                                <span class="material-symbols-rounded">close</span>
                            </button>
                        </div>
                    </div>
                    <div class="p-6 sm:p-8">
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-xs text-slate-500 font-medium">YOU ARE SUBSCRIBING TO</p>
                                    <p id="selectedPlanName" class="font-bold text-lg text-slate-800"></p>
                                </div>
                                <p id="selectedPlanPrice" class="text-2xl font-extrabold text-slate-900"></p>
                            </div>
                        </div>
                        <div id="paypal-button-container" class="mt-6"></div>
                    </div>
                </div>

                <div id="successView" class="p-8 text-center hidden">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-rounded text-4xl text-green-600">task_alt</span>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">Payment Successful!</h2>
                    <p class="text-sm text-slate-600 mb-6">Your subscription is now active and ready to use.</p>
                    <a href="{{ route('dashboards.owner.dashboard') }}" class="w-full block bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-all text-sm">
                        Go to Dashboard
                    </a>
                </div>

            </div>
        </div>



        <main class="min-h-screen flex flex-col justify-center items-center py-16 px-4">
            <div class="text-center w-full max-w-2xl mx-auto">
                <h1 class="text-2xl lg:text-5xl font-extrabold tracking-tight text-white">
                    Choose Your Plan
                </h1>
                <p class="mt-4 text-lg leading-8 text-slate-300">
                    Start with the essentials or unlock powerful tools for growth. Simple, transparent pricing for every stage of your business.
                </p>
            </div>

            <div class="mt-16 w-full max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-2 items-stretch justify-center gap-8">

                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 flex flex-col p-8 transition-transform duration-300 hover:-translate-y-2">
                    <div class="flex-grow">
                        <h3 class="text-lg font-bold text-orange-600">Basic</h3>
                        <p class="mt-2 text-sm text-slate-500">All the essentials to get your business started.</p>
                        <p class="mt-6">
                            <span class="text-5xl font-extrabold tracking-tight text-slate-900">₱250</span>
                            <span class="text-base font-medium text-slate-500">/6 months</span>
                        </p>
                        <ul class="mt-8 space-y-4 text-sm font-medium text-slate-700">
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Access to platform updates</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Restock suggestion list</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Daily sales tracking</li>
                            <li class="flex items-start gap-3 text-slate-400"><span class="material-symbols-rounded mt-0.5">cancel</span><span class="line-through">Advanced sales analytics</span></li>
                            <li class="flex items-start gap-3 text-slate-400"><span class="material-symbols-rounded mt-0.5">cancel</span><span class="line-through">Comparative analysis</span></li>
                            <li class="flex items-start gap-3 text-slate-400"><span class="material-symbols-rounded mt-0.5">cancel</span><span class="line-through">Hold more than 1 staff</span></li>
                        </ul>
                    </div>
                    <div class="mt-8">
                        <button onclick="openModal(1, 'Basic', 250)" class="w-full bg-orange-600 text-white py-3 rounded-lg font-semibold hover:bg-orange-700 transition-colors text-sm shadow-lg shadow-orange-500/20 hover:shadow-xl hover:shadow-orange-500/30">
                            Subscribe Now
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-2xl ring-2 ring-red-500 flex flex-col p-8 relative transition-transform duration-300 hover:-translate-y-2">
                    <div class="absolute top-0 -mt-3.5 left-1/2 -translate-x-1/2">
                        <span class="inline-flex items-center px-4 py-1 bg-red-600 text-white text-xs font-semibold rounded-full shadow-md">Best Value</span>
                    </div>
                    <div class="flex-grow">
                        <h3 class="text-lg font-bold text-red-600">Premium</h3>
                        <p class="mt-2 text-sm text-slate-500">Unlock powerful tools for growth and efficiency.</p>
                        <p class="mt-6">
                            <span class="text-5xl font-extrabold tracking-tight text-slate-900">₱500</span>
                            <span class="text-base font-medium text-slate-500">/year</span>
                        </p>
                        <ul class="mt-8 space-y-4 text-sm font-medium text-slate-700">
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Access to platform updates</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Restock suggestion list</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Daily sales tracking</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Advanced sales analytics</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Comparative analysis</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Hold more than 1 staff</li>
                        </ul>
                    </div>
                    <div class="mt-8">
                        <button onclick="openModal(2, 'Premium', 500)" class="w-full bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors text-sm shadow-lg shadow-red-500/20 hover:shadow-xl hover:shadow-red-500/30">
                            Subscribe Now
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function openModal(planId, planName, planPrice) {
            const modal = document.getElementById('paymentModal');
            const modalContent = document.getElementById('modalContent');
            const paymentFormView = document.getElementById('paymentFormView');
            const successView = document.getElementById('successView');
            const selectedPlanName = document.getElementById('selectedPlanName');
            const selectedPlanPrice = document.getElementById('selectedPlanPrice');
            const paypalContainer = document.getElementById('paypal-button-container');

            // Reset views
            paymentFormView.classList.remove('hidden');
            successView.classList.add('hidden');

            // Update plan info
            selectedPlanName.textContent = planName;
            selectedPlanPrice.textContent = `₱${planPrice}`;

            // Clear PayPal container
            paypalContainer.innerHTML = "";

            // Show modal (use opacity + visibility instead of display: none)
            modal.classList.remove('hidden');
            modal.style.visibility = 'visible';
            setTimeout(() => modal.classList.remove('opacity-0'), 10); // fade-in effect

            // Decide modal size
            const allowCard = true; // toggle if you want card fields
            if (allowCard) {
                modalContent.classList.add('paypal-large');
                modalContent.classList.remove('paypal-small');
                paypalContainer.style.minHeight = "350px";
            } else {
                modalContent.classList.add('paypal-small');
                modalContent.classList.remove('paypal-large');
                paypalContainer.style.minHeight = "150px";
            }

            // Delay PayPal render until modal is painted
            requestAnimationFrame(() => {
                paypal.Buttons({
                    funding: {
                        disallowed: allowCard ? [] : [paypal.FUNDING.CARD]
                    },
                    createOrder: function(data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                description: planName,
                                amount: {
                                    currency_code: "PHP",
                                    value: planPrice
                                }
                            }]
                        });
                    },
                    onApprove: function(data, actions) {
                        return actions.order.capture().then(details => {
                            return fetch(`/subscribe/${planId}`, {
                                    method: 'POST',
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        paypal_order_id: data.orderID,
                                        plan_id: planId
                                    })
                                }).then(res => res.json())
                                .then(response => {
                                    paymentFormView.classList.add('hidden');
                                    successView.classList.remove('hidden');
                                });
                        });
                    },
                    onError: function(err) {
                        alert("Something went wrong with PayPal payment.");
                        console.error(err);
                    }
                }).render("#paypal-button-container");
            });
        }

        // Close modal
        document.getElementById('closeModalBtn').addEventListener('click', () => {
            const modal = document.getElementById('paymentModal');
            modal.classList.add('opacity-0'); // fade-out
            setTimeout(() => modal.classList.add('hidden'), 300);
        });
    </script>

</body>

</html>