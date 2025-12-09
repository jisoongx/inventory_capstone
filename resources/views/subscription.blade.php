<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shoplytix Subscription Plans</title>
    <script src="https://www.sandbox.paypal.com/sdk/js?client-id=AfhEjCadB5uvvu8lW7Q1kxNbo9uyBkd0OCtDIlTgw-mid22lSDCUDPhd7YWEeHydaQVwjjyCpiyyrGuW&vault=true&intent=subscription&currency=PHP"></script>
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


                        <div id="overviewPriceBreakdown" class="mt-4 space-y-2 text-sm hidden">

                            <div class="flex justify-between">
                                <span class="text-slate-600">Monthly subscription</span>
                                <span id="overviewTotalAfterVat" class="text-slate-900"></span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-slate-600">VAT (12%)</span>
                                <span id="overviewVatAmount" class="text-slate-900"></span>
                            </div>

                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <p class="font-semibold text-slate-800" id="overviewPlanName"></p>
                            <p class="font-bold text-lg text-slate-900" id="overviewPlanPrice"></p>
                        </div>
                        <!-- <p class="text-xs text-slate-500" id="overviewPlanDuration"></p> -->

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
                            <span class="text-base font-medium text-slate-500">Free</span>
                        </p>
                        <ul class="mt-8 space-y-4 text-sm font-medium text-slate-700">
                            <ul class="mt-8 space-y-4 text-sm font-medium text-slate-700">
                                <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Daily, weekly, and monthly sales tracking</li>
                                <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Stock alert, expiration notice, and top-selling products</li>
                                <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Sales and loss analysis ( chart included)</li>
                                <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Monthly net profit overview</li>
                                <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Up to 50 inventory items</li>

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
                            <span class="text-5xl font-extrabold tracking-tight text-slate-900">₱500</span>
                            <span class="text-base font-medium text-slate-500">/month</span>
                        </p>
                        <ul class="mt-8 space-y-4 text-sm font-medium text-slate-700">
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>All features from Basic Plan</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Inventory expansion (up to 200 items)</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Sales ans stock performance reports</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Stock loss, damage, and expiration reports</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Activity logs</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>One staff account</li>

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
                            <span class="text-5xl font-extrabold tracking-tight text-slate-900">₱1500</span>
                            <span class="text-base font-medium text-slate-500">/month</span>
                        </p>
                        <ul class="mt-8 space-y-4 text-sm font-medium text-slate-700">
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>All features from Standard Plan</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Product Association Analysis</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Sales Frequency Analysis</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Restock Suggestion List</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Seasonal trend analysis</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Unlimited inventory items</li>
                            <li class="flex items-start gap-3"><span class="material-symbols-rounded text-green-500 mt-0.5">check_circle</span>Add and manage multiple staff accounts</li>
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



    <script>
        const plans = {
            3: {
                name: "Basic",
                price: 0,
                duration: "Free",
                features: [
                    "Daily, weekly, and monthly sales tracking",
                    "Stock alert, expiration notice, and top-selling products",
                    "Sales and loss analysis(chart included)",
                    "Monthly net profit overview",
                    "Up to 50 inventory items"
                ]
            },
            1: {
                name: "Standard",
                price: 500,
                duration: "month",
                features: [
                    "All features from Basic Plan",
                    "Inventory expansion (up to 200 inventory items)",
                    "Sales ans stock performance reports",
                    "Stock loss, damage, and expiration reports",
                    "Activity logs",
                    "One staff account"
                ]
            },
            2: {
                name: "Premium",
                price: 1500,
                duration: "month",
                features: [
                    "All features from Standard Plan",
                    "Product association analysis",
                    "Sales frequency analysis",
                    "Restock suggestion list",
                    "Seasonal trend analysis",
                    "Unlimited inventory items",
                    "Add and manage multiple staff account"
                ]
            }
        };

        // Map plan IDs to PayPal subscription plan IDs (from your PayPal dashboard)
        const PAYPAL_PLAN_IDS = {
            1: "P-7C785523EJ448962XNEV5C6Q", // replace with your actual PayPal plan ID
            2: "P-9BL62740BA150960NNEV5EJI" // replace with your actual PayPal plan ID
        };

        function openModal(planId) {
            const modal = document.getElementById('paymentModal');
            const overview = document.getElementById('planOverview');
            const paymentForm = document.getElementById('paymentFormView');
            const successView = document.getElementById('successView');

            // Reset views
            overview.classList.remove('hidden');
            paymentForm.classList.add('hidden');
            successView.classList.add('hidden');

            // Fill plan overview
            const plan = plans[planId];
            document.getElementById('overviewPlanName').textContent = plan.name;
            document.getElementById('overviewPlanPrice').textContent =
                plan.price === 0 ? "₱0.00" : `₱${plan.price.toFixed(2)}`;

            // document.getElementById('overviewPlanDuration').textContent = plan.duration;
            // VAT calculation for overview step
            // Correct VAT breakdown: price already includes 12% VAT
            const net = plan.price / 1.12; // Monthly subscription (no VAT)
            const vat = plan.price - net; // VAT amount

            // Update UI
            const breakdown = document.getElementById("overviewPriceBreakdown");

            if (plan.price > 0) {
                document.getElementById("overviewTotalAfterVat").textContent = `₱${net.toFixed(2)}`;
                document.getElementById("overviewVatAmount").textContent = `₱${vat.toFixed(2)}`;
                breakdown.classList.remove("hidden");
            } else {
                breakdown.classList.add("hidden");
            }


            const featuresList = document.getElementById('overviewFeatures');
            featuresList.innerHTML = plan.features.map(f => `
        <li class="flex items-start gap-2">
            <span class="material-symbols-rounded text-green-500 text-base mt-0.5">check_circle</span>${f}
        </li>
    `).join('');

            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.remove('opacity-0'), 10);

            // Cancel button
            document.getElementById('cancelOverview').onclick = () => {
                modal.classList.add('opacity-0');
                setTimeout(() => modal.classList.add('hidden'), 300);
            };

            // Confirm button → start payment / subscription
            document.getElementById('confirmOverview').onclick = () => {
                overview.classList.add('hidden');
                startSubscriptionFlow(planId);
            };
        }

        function startSubscriptionFlow(planId) {
            const plan = plans[planId];
            const paymentFormView = document.getElementById('paymentFormView');
            const successView = document.getElementById('successView');
            const selectedPlanName = document.getElementById('selectedPlanName');
            const selectedPlanPrice = document.getElementById('selectedPlanPrice');
            const paypalContainer = document.getElementById('paypal-button-container');

            // Show payment form
            paymentFormView.classList.remove('hidden');
            successView.classList.add('hidden');
            selectedPlanName.textContent = plan.name;
            selectedPlanPrice.textContent =
                plan.price === 0 ? "₱0.00" : `₱${plan.price.toFixed(2)}`;

            paypalContainer.innerHTML = "";

            // Handle Basic plan → no PayPal, just direct activation
            if (plan.name.toLowerCase() === "basic") {
                // Hide payment form (if it was visible)
                paymentFormView.classList.add('hidden');

                // Show the success view
                successView.classList.remove('hidden');

                // Update success view text
                document.querySelector("#successView h2").textContent = "Basic Plan Activated!";
                document.querySelector("#successView p").textContent = "You now have access to the Basic plan forever!";

                // Optional: you can still do a backend call if you want to store that the user availed it
                fetch(`/subscribe/${planId}`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({})
                }).catch(() => console.log("Basic plan activation recorded."));

                return; // stop further execution
            }


            // Standard / Premium → PayPal subscription
            paypal.Buttons({
                style: {
                    layout: 'vertical',
                    color: 'gold',
                    shape: 'rect',
                    label: 'subscribe'
                },
                createSubscription: function(data, actions) {
                    return actions.subscription.create({
                        plan_id: PAYPAL_PLAN_IDS[planId]
                    });
                },
                onApprove: function(data, actions) {
                    fetch(`/subscribe/${planId}`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                paypal_order_id: data.subscriptionID,
                                plan_id: planId
                            })
                        })
                        .then(res => res.json())
                        .then(() => {
                            paymentFormView.classList.add('hidden');
                            successView.classList.remove('hidden');
                        })
                        .catch(() => alert("Failed to activate subscription on backend."));
                },
                onError: function(err) {
                    alert("Something went wrong with PayPal subscription.");
                    console.error(err);
                }
            }).render("#paypal-button-container");
        }

        // Close modal button
        document.getElementById('closeModalBtn').addEventListener('click', () => {
            const modal = document.getElementById('paymentModal');
            modal.classList.add('opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 300);
        });
    </script>



</body>

</html>