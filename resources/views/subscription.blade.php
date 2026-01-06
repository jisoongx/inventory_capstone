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

            <div class="mt-16 w-full max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                @foreach ($plans as $plan)

                @php
                $styles = match(strtolower($plan->plan_title)) {
                'basic' => [
                'title' => 'text-yellow-500',
                'button' => 'bg-yellow-500 hover:bg-yellow-600',
                'desc' => 'All the essentials to get your business started.'
                ],
                'standard' => [
                'title' => 'text-orange-600',
                'button' => 'bg-orange-600 hover:bg-orange-700',
                'desc' => 'Enhanced tools to keep your business organized and efficient.'
                ],
                'premium' => [
                'title' => 'text-red-600',
                'button' => 'bg-red-600 hover:bg-red-700',
                'desc' => 'Unlock powerful tools for growth and efficiency.'
                ],
                default => [
                'title' => 'text-slate-800',
                'button' => 'bg-blue-600 hover:bg-blue-700',
                'desc' => ''
                ]
                };
                @endphp

                <div class="relative bg-white rounded-2xl shadow-lg border border-slate-200 flex flex-col p-8 transition-transform hover:-translate-y-2">


                    {{-- Best Value Badge --}}
                    @if(strtolower($plan->plan_title) === 'premium')
                    <div class="absolute top-0 -mt-3.5 left-1/2 -translate-x-1/2">
                        <span class="px-4 py-1 bg-red-600 text-white text-xs font-semibold rounded-full shadow">
                            Best Value
                        </span>
                    </div>
                    @endif

                    <div class="flex-grow">
                        <h3 class="text-lg font-bold {{ $styles['title'] }}">
                            {{ $plan->plan_title }}
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            {{ $styles['desc'] }}
                        </p>

                        <p class="mt-6">
                            <span class="text-5xl font-extrabold text-slate-900">
                                ₱{{ number_format($plan->plan_price, 0) }}
                            </span>
                            @if($plan->plan_price > 0)
                            <span class="text-base text-slate-500">/month</span>
                            @else
                            <span class="text-base text-slate-500">Free</span>
                            @endif
                        </p>

                        <ul class="mt-8 space-y-4 text-sm font-medium text-slate-700">
                            @foreach (explode("\n", $plan->plan_includes) as $feature)
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-rounded text-green-500 mt-0.5">
                                    check_circle
                                </span>
                                {{ trim($feature) }}
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-8">
                        <button
                            onclick='openModal(@json($plan))'
                            class="w-full text-white py-3 rounded-lg font-semibold transition-colors {{ $styles['button'] }}">
                            {{ $plan->plan_price == 0 ? 'Get Started for FREE!' : 'Subscribe Now' }}
                        </button>
                    </div>

                </div>

                @endforeach
            </div>


            <p class="mt-10 text-center text-sm text-slate-300">
                All plans include access to the core POS system, regular updates, and customer support.
            </p>
        </main>
    </div>



    <script>
        let currentPlan = null;

        function openModal(plan) {
            currentPlan = plan;

            const modal = document.getElementById('paymentModal');
            const overview = document.getElementById('planOverview');
            const paymentForm = document.getElementById('paymentFormView');
            const successView = document.getElementById('successView');

            // Reset views
            overview.classList.remove('hidden');
            paymentForm.classList.add('hidden');
            successView.classList.add('hidden');

            // Fill plan data
            document.getElementById('overviewPlanName').textContent = plan.plan_title;
            document.getElementById('overviewPlanPrice').textContent =
                plan.plan_price == 0 ?
                '₱0.00' :
                `₱${parseFloat(plan.plan_price).toFixed(2)}`;

            // VAT breakdown
            const breakdown = document.getElementById('overviewPriceBreakdown');
            if (plan.plan_price > 0) {
                const net = plan.plan_price / 1.12;
                document.getElementById('overviewTotalAfterVat').textContent = `₱${net.toFixed(2)}`;
                document.getElementById('overviewVatAmount').textContent = `₱${(plan.plan_price - net).toFixed(2)}`;
                breakdown.classList.remove('hidden');
            } else {
                breakdown.classList.add('hidden');
            }

            // Features
            document.getElementById('overviewFeatures').innerHTML =
                plan.plan_includes.split("\n").map(feature => `
                <li class="flex items-start gap-2">
                    <span class="material-symbols-rounded text-green-500">check_circle</span>
                    ${feature.trim()}
                </li>
            `).join('');

            // Show modal
            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.remove('opacity-0'), 10);
        }

        document.getElementById('cancelOverview').onclick = closeModal;
        document.getElementById('closeModalBtn').onclick = closeModal;

        function closeModal() {
            const modal = document.getElementById('paymentModal');
            modal.classList.add('opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        document.getElementById('confirmOverview').onclick = () => {
            document.getElementById('planOverview').classList.add('hidden');
            startSubscriptionFlow();
        };

        function startSubscriptionFlow() {
            const paymentFormView = document.getElementById('paymentFormView');
            const successView = document.getElementById('successView');

            paymentFormView.classList.remove('hidden');

            document.getElementById('selectedPlanName').textContent = currentPlan.plan_title;
            document.getElementById('selectedPlanPrice').textContent =
                currentPlan.plan_price == 0 ?
                '₱0.00' :
                `₱${parseFloat(currentPlan.plan_price).toFixed(2)}`;

            // FREE PLAN
            if (currentPlan.plan_price == 0) {
                paymentFormView.classList.add('hidden');
                successView.classList.remove('hidden');
                return;
            }

            // PAID PLAN (PayPal)
            paypal.Buttons({
                style: {
                    layout: 'vertical',
                    color: 'gold',
                    label: 'subscribe'
                },
                createSubscription: function(data, actions) {
                    return actions.subscription.create({
                        plan_id: currentPlan.paypal_plan_id
                    });
                },
                onApprove: function(data) {
                    fetch(`/subscribe/${currentPlan.plan_id}`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            paypal_subscription_id: data.subscriptionID
                        })
                    }).then(() => {
                        paymentFormView.classList.add('hidden');
                        successView.classList.remove('hidden');
                    });
                }
            }).render("#paypal-button-container");
        }
    </script>




</body>

</html>