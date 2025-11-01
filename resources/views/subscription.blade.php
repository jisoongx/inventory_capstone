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
        <div id="paymentModal"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden opacity-0 transition-opacity duration-300">
            <div id="modalContent"
                class="bg-white rounded-xl shadow-2xl w-full max-w-lg transform scale-95 transition-all duration-300">

                <!-- Step 1: Overview -->
                <div id="planOverview" class="p-8">
                    <p class="text-sm text-slate-500 mb-4">Please review your plan details before continuing.</p>

                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <p class="font-semibold text-slate-800" id="overviewPlanName"></p>
                            <p class="font-bold text-lg text-slate-900" id="overviewPlanPrice"></p>
                        </div>
                        <p class="text-xs text-slate-500" id="overviewPlanDuration"></p>
                    </div>

                    <ul id="overviewFeatures" class="space-y-3 text-sm text-slate-700 mb-6"></ul>

                    <div class="flex justify-end gap-3">
                        <button id="cancelOverview"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-sm">
                            Cancel
                        </button>
                        <button id="confirmOverview"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm">
                            Subscribe
                        </button>
                    </div>
                </div>

                <!-- Step 2: PayPal or Basic Activation -->
                <div id="paymentFormView" class="p-8 hidden">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">Confirm Purchase</h2>
                            <p class="text-sm text-slate-500 mt-1">You’re one step away from activating your plan.</p>
                        </div>
                        <button id="closeModalBtn" class="text-slate-400 hover:text-red-600 transition-colors">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-4">
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

                <!-- Step 3: Success -->
                <div id="successView" class="p-8 text-center hidden">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-rounded text-4xl text-green-600">task_alt</span>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">Payment Successful!</h2>
                    <p class="text-sm text-slate-600 mb-6">Your subscription is now active and ready to use.</p>
                    <a href="{{ route('dashboards.owner.dashboard') }}"
                        class="w-full block bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-all text-sm">
                        Go to Dashboard
                    </a>
                </div>
            </div>
        </div>


        <!-- Free Plan Modal -->
        <div id="freePlanModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden opacity-0 transition-opacity duration-300">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-8 text-center transform scale-95 transition-all duration-300">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-rounded text-4xl text-green-600">task_alt</span>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Basic Plan Availed Successfully!</h2>
                <p class="text-sm text-slate-600 mb-6">Your 1-month free access is now active.</p>
                <a href="{{ route('dashboards.owner.dashboard') }}" class="w-full block bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-all text-sm">
                    Go to Dashboard
                </a>
            </div>
        </div>




        <main class="min-h-screen flex flex-col justify-center items-center py-12 px-4">
            <div class="text-center w-full max-w-2xl mx-auto">
                <h1 class="text-2xl lg:text-5xl font-extrabold tracking-tight text-white">
                    Choose Your Plan
                </h1>
                <p class="mt-4 text-lg leading-8 text-slate-300">
                    Start with the essentials or unlock enhanced and powerful tools for growth. Simple, transparent pricing for every stage of your business.
                </p>
            </div>

            <div class="mt-16 w-full max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 items-stretch justify-center gap-8">


                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 flex flex-col p-8 transition-transform duration-300 hover:-translate-y-2">
                    <div class="flex-grow">
                        <h3 class="text-lg font-bold text-yellow-500">Basic</h3>
                        <p class="mt-2 text-sm text-slate-500">All the essentials to get your business started.</p>
                        <p class="mt-6">
                            <span class="text-5xl font-extrabold tracking-tight text-slate-900">₱0</span>
                            <span class="text-base font-medium text-slate-500">Free for 1 month</span>
                        </p>
                        <ul class="mt-8 space-y-4 text-sm font-medium text-slate-700">
                            <ul class="mt-8 space-y-4 text-sm font-medium text-slate-700">
                                <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Daily, weekly, and monthly sales tracking</li>
                                <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Comparative sales analysis</li>
                                <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Monthly net profit overview</li>
                                <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Sales by category and loss analysis</li>
                                <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Up to 50 inventory products with 10 categories & units</li>

                            </ul>


                        </ul>
                    </div>
                    <div class="mt-8">

                        @if(Auth::guard('owner')->check())
                        <button
                            type="button"
                            onclick="openModal(3, 'Basic', 0)"
                            class="w-full bg-yellow-500 text-white py-3 rounded-lg font-semibold hover:bg-yellow-600 transition-colors text-sm shadow-lg shadow-orange-500/20 hover:shadow-xl hover:shadow-orange-500/30">
                            Get Started for FREE!
                        </button>
                        @endif





                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 flex flex-col p-8 transition-transform duration-300 hover:-translate-y-2">
                    <div class="flex-grow">
                        <h3 class="text-lg font-bold text-orange-600">Standard</h3>
                        <p class="mt-2 text-sm text-slate-500">Enhanced tools to keep your business organized and efficient.</p>
                        <p class="mt-6">
                            <span class="text-5xl font-extrabold tracking-tight text-slate-900">₱250</span>
                            <span class="text-base font-medium text-slate-500">/6 months</span>
                        </p>
                        <ul class="mt-8 space-y-4 text-sm font-medium text-slate-700">
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>All features from Basic Plan</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Inventory expansion (up to 200 products, 30 categories & units)</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Stock alert & expiration notice</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Top selling products dashboard</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Sales Performance reports</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Inventory reports</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Activity logs</li>

                        </ul>


                    </div>
                    <div class="mt-8">
                        <button onclick="openModal(1, 'Standard', 250)" class="w-full bg-orange-600 text-white py-3 rounded-lg font-semibold hover:bg-orange-700 transition-colors text-sm shadow-lg shadow-orange-500/20 hover:shadow-xl hover:shadow-orange-500/30">
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
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>All features from Standard Plan</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Product performance analysis</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Restock Suggestion List</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Product trends</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Seasonal trend analysis</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Activity logs for both owner & staff</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Unlimited inventory, categories, and units</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Add and manage multiple staff</li>
                        </ul>

                    </div>
                    <div class="mt-8">
                        <button onclick="openModal(2, 'Premium', 500)" class="w-full bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors text-sm shadow-lg shadow-red-500/20 hover:shadow-xl hover:shadow-red-500/30">
                            Subscribe Now
                        </button>
                    </div>
                </div>
            </div>
            <p class="mt-10 text-center text-sm text-slate-300">
                All plans include access to the core POS system, regular updates, and customer support.
            </p>
        </main>
    </div>

    <!-- <script>
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
            selectedPlanPrice.textContent = planPrice === 0 ? "₱0" : `₱${planPrice}`;

            // Clear PayPal container
            paypalContainer.innerHTML = "";

            // Show modal
            modal.classList.remove('hidden');
            modal.style.visibility = 'visible';
            setTimeout(() => modal.classList.remove('opacity-0'), 10);

            // 🟡 Special case: BASIC plan (no PayPal)
            // 🟡 Special case: BASIC plan (no PayPal)
            if (planName.trim().toLowerCase() === "basic") {
                // Hide all views first
                paymentFormView.classList.add('hidden');
                successView.classList.add('hidden');

                // Show a temporary message while checking eligibility
                paypalContainer.innerHTML = `<div class="text-center py-4 text-slate-600">Checking your free plan eligibility...</div>`;

                fetch(`/subscribe/${planId}`, {
                        method: "POST",
                        credentials: "same-origin",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({})
                    })
                    .then(async response => {
                        let data = {};
                        try {
                            data = await response.json();
                        } catch (e) {
                            console.error(e);
                        }

                        if (response.ok && data.success) {
                            // ✅ First-time user → show activating message
                            paymentFormView.classList.remove('hidden');
                            paypalContainer.innerHTML = `<div class="text-center py-4 text-slate-600">Activating your free plan...</div>`;

                            setTimeout(() => {
                                paymentFormView.classList.add('hidden');
                                successView.classList.remove('hidden');
                                document.querySelector("#successView h2").textContent = "Basic Plan Activated!";
                                document.querySelector("#successView p").textContent = data.message || "Your 1-month free access is now active.";
                                setTimeout(() => window.location.href = "/owner/dashboard", 2000);
                            }, 1000);

                        } else {
                            // ❌ Already used → ONLY show alert or notice, do NOT show Confirm Purchase
                            alert(data.message || "You have already used our one-time free Basic plan.");
                            // optionally close modal automatically:
                            modal.classList.add('opacity-0');
                            setTimeout(() => modal.classList.add('hidden'), 300);
                        }
                    })
                    .catch(err => {
                        alert("Something went wrong checking the Basic plan.");
                        console.error(err);
                    });

                return; // Stop here, no PayPal needed
            }



            // 🟢 For Standard / Premium plans → render PayPal buttons
            paypal.Buttons({
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
        }

        // Close modal
        document.getElementById('closeModalBtn').addEventListener('click', () => {
            const modal = document.getElementById('paymentModal');
            modal.classList.add('opacity-0'); // fade-out
            setTimeout(() => modal.classList.add('hidden'), 300);
        });
    </script> -->

    <script>
        const plans = {
            3: {
                name: "Basic",
                price: 0,
                duration: "1 month free access",
                features: [
                    "Daily, weekly, and monthly sales tracking",
                    "Comparative sales analysis",
                    "Monthly net profit overview",
                    "Sales by category and loss analysis",
                    "Up to 50 inventory products with 10 categories & units"
                ]
            },
            1: {
                name: "Standard",
                price: 250,
                duration: "6 months",
                features: [
                    "All features from Basic Plan",
                    "Inventory expansion (up to 200 products, 30 categories & units)",
                    "Stock alert & expiration notice",
                    "Top selling products dashboard",
                    "Sales performance reports",
                    "Inventory reports",
                    "Activity logs"
                ]
            },
            2: {
                name: "Premium",
                price: 500,
                duration: "1 year",
                features: [
                    "All features from Standard Plan",
                    "Product performance analysis",
                    "Restock suggestion list",
                    "Product trends",
                    "Seasonal trend analysis",
                    "Activity logs for both owner & staff",
                    "Unlimited inventory, categories, and units",
                    "Add and manage multiple staff"
                ]
            }
        };

        function openModal(planId, planName, planPrice) {
            const modal = document.getElementById('paymentModal');
            const overview = document.getElementById('planOverview');
            const paymentForm = document.getElementById('paymentFormView');
            const successView = document.getElementById('successView');

            // Reset views
            overview.classList.remove('hidden');
            paymentForm.classList.add('hidden');
            successView.classList.add('hidden');

            // Fill overview content
            const plan = plans[planId];
            document.getElementById('overviewPlanName').textContent = plan.name;
            document.getElementById('overviewPlanPrice').textContent = `₱${plan.price}`;
            document.getElementById('overviewPlanDuration').textContent = plan.duration;

            const featuresList = document.getElementById('overviewFeatures');
            featuresList.innerHTML = plan.features.map(f => `
            <li class="flex items-start gap-2"><span class="material-symbols-rounded text-green-500 text-base mt-0.5">check_circle</span>${f}</li>
        `).join('');

            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.remove('opacity-0'), 10);

            // Handle "Cancel" and "Continue"
            document.getElementById('cancelOverview').onclick = () => {
                modal.classList.add('opacity-0');
                setTimeout(() => modal.classList.add('hidden'), 300);
            };

            document.getElementById('confirmOverview').onclick = () => {
                overview.classList.add('hidden');
                startPaymentFlow(planId, plan.name, plan.price);
            };
        }

        function startPaymentFlow(planId, planName, planPrice) {
            const modal = document.getElementById('paymentModal');
            const paymentFormView = document.getElementById('paymentFormView');
            const successView = document.getElementById('successView');
            const selectedPlanName = document.getElementById('selectedPlanName');
            const selectedPlanPrice = document.getElementById('selectedPlanPrice');
            const paypalContainer = document.getElementById('paypal-button-container');

            paymentFormView.classList.remove('hidden');
            successView.classList.add('hidden');
            paypalContainer.innerHTML = "";

            selectedPlanName.textContent = planName;
            selectedPlanPrice.textContent = planPrice === 0 ? "₱0" : `₱${planPrice}`;

            // BASIC PLAN → direct fetch
            if (planName.toLowerCase() === "basic") {
                paypalContainer.innerHTML = `<div class="text-center py-4 text-slate-600">Activating your free plan...</div>`;
                fetch(`/subscribe/${planId}`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({})
                    })
                    .then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (res.ok && data.success) {
                            setTimeout(() => {
                                paymentFormView.classList.add('hidden');
                                successView.classList.remove('hidden');
                            }, 1000);
                        } else {
                            alert(data.message || "You have already used the Basic plan.");
                            modal.classList.add('opacity-0');
                            setTimeout(() => modal.classList.add('hidden'), 300);
                        }
                    })
                    .catch(() => alert("Something went wrong."));
                return;
            }

            // STANDARD / PREMIUM → PayPal
            paypal.Buttons({
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
        }

        document.getElementById('closeModalBtn').addEventListener('click', () => {
            const modal = document.getElementById('paymentModal');
            modal.classList.add('opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 300);
        });
    </script>


</body>

</html>