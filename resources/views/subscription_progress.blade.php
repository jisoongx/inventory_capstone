<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
</head>

<body class="bg-white font-[Inter] h-screen flex items-center justify-center">

    <div class="flex flex-col items-center justify-center w-full max-w-4xl space-y-10 mb-20">
        <!-- Logo -->
        <div class="flex items-center gap-2 mr-auto">
            <img src="{{ asset('assets/logo.png') }}" alt="Shoplytix Logo" class="w-12 h-12 object-contain" />
            <h1 class="text-2xl font-bold text-red-500">SHOPLYTIX</h1>
        </div>


        <div class="flex items-center justify-between w-full max-w-3xl relative">
            <div class="absolute top-8 left-0 right-0 h-1 bg-gray-200 rounded-lg -z-10"></div>

            @php
            $progressWidth = 'w-1/3'; // default: only Sign Up done

            if ($subscription) {
            $progressWidth = 'w-full'; // once a plan is chosen, everything is complete
            }
            @endphp


            <div class="absolute top-8 left-0 {{ $progressWidth }} h-1 bg-gradient-to-r from-blue-500 to-blue-400 rounded-lg -z-10 shadow"></div>

            <div class="flex flex-col items-center text-blue-600">
                <div
                    class="w-14 h-14 flex items-center justify-center rounded-full bg-gradient-to-b from-white to-green-100 border-2 border-blue-500 shadow-md">
                    ✔
                </div>
                <p class="mt-3 text-sm font-semibold">Sign Up</p>
            </div>

            <div class="flex flex-col items-center text-blue-600">
                <div
                    class="w-14 h-14 flex items-center justify-center rounded-full 
                        {{ optional($subscription)->status === 'verified' 
                            ? 'bg-gradient-to-b from-blue-500 to-blue-400 text-white border-blue-500 shadow-lg' 
                            : 'bg-gradient-to-b from-white to-green-100 border-blue-500 text-gray-800 shadow-md' }} 
                        border-2">
                    <span class="text-blue-600">
                        {{ optional($subscription)->status === 'active' ? '✔' : '' }}
                    </span>
                    </span>
                </div>
                <p class="mt-3 text-sm font-semibold">Choose Plan</p>
            </div>


            <div class="flex flex-col items-center text-blue-600">
                <div
                    class="w-14 h-14 flex items-center justify-center rounded-full 
                        {{ optional($subscription)->status === 'verified' 
                            ? 'bg-gradient-to-b from-blue-500 to-blue-400 text-white border-blue-500 shadow-lg' 
                            : 'bg-gradient-to-b from-white to-green-100 border-blue-500 text-gray-800 shadow-md' }} 
                        border-2">
                    <span class="text-blue-600">
                        {{ optional($subscription)->status === 'active' ? '✔' : '' }}
                    </span>
                </div>

                <p class="mt-3 text-sm font-semibold">Status</p>
            </div>
        </div>

        <!-- Timeline Card -->
        <div class="w-full max-w-2xl bg-white rounded-2xl border border-gray-100 shadow-lg p-8">
            <div class="relative border-l-2 border-gray-200 pl-6 space-y-8">

                @if ($subscription)<!-- Step 1: Subscribed -->
                <!-- Step 1: Subscribed -->
                <div class="relative">
                    <!-- Dot -->
                    <div class="absolute top-0 left-0 w-4 h-4 rounded-full bg-blue-400 border-2 border-white shadow-md"></div>

                    <!-- Text container -->
                    <div class="ml-6">
                        <p class="text-sm text-gray-800 font-semibold">
                            Subscribed to {{ ucfirst($subscription?->planDetails?->plan_title ?? 'N/A') }}
                        </p>
                        <span class="text-xs text-gray-500">
                            {{ $subscription->subscription_start ? \Carbon\Carbon::parse($subscription->subscription_start)->format('F j, Y') : 'Not started yet' }}
                        </span>
                    </div>
                </div>



                <!-- Step 2: Verification -->
                <div class="relative">
                    <!-- Dot -->
                    <div class="absolute top-0 left-0 w-4 h-4 rounded-full {{ $subscription->status === 'verified' ? 'bg-blue-500' : 'bg-blue-400' }} border-2 border-white shadow-md"></div>

                    <!-- Text container -->
                    <div class="ml-6">
                        <p class="text-sm text-gray-800 font-semibold">
                            Admin is currently verifying your subscription:
                            <span class="{{ $subscription->status === 'active' ? 'text-green-600' : 'text-yellow-500' }}">
                                {{ $subscription->status === 'active' ? 'Verified' : ucfirst($subscription->status) }}
                            </span>
                        </p>
                        <span class="text-xs text-gray-500">
                            {{ $subscription->subscription_start ? \Carbon\Carbon::parse($subscription->subscription_start)->format('F j, Y') : 'Not started yet' }}
                        </span>
                    </div>
                </div>

                <!-- Step 3: Completed -->
                @if ($subscription->status === 'active')
                <div class="relative">
                    <!-- Dot -->
                    <div class="absolute top-0 left-0 w-4 h-4 rounded-full bg-blue-400 border-2 border-white shadow-md"></div>

                    <!-- Text container -->
                    <div class="ml-6">
                        <p class="text-sm text-gray-800 font-semibold">
                            Congratulations! You are now a {{ $subscription?->planDetails?->plan_title ?? '-' }} user.
                        </p>
                        <span class="text-xs text-gray-500">
                            Valid until: {{ $subscription->subscription_end ? \Carbon\Carbon::parse($subscription->subscription_end)->format('F j, Y') : '' }}
                        </span>
                        <div class="mt-2">
                            <a href="{{ route('dashboards.owner.dashboard') }}"
                                class="text-red-500 text-sm font-semibold hover:underline">
                                Go to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                @else

                <div>
                    <p class="text-gray-700 font-semibold">You have not subscribed to any plan yet.</p>
                    <a href="{{ route('subscription.selection') }}"
                        class="text-blue-500 font-semibold underline">Choose a plan</a>
                </div>
                @endif

                <!-- Logout Button (inside the card) -->
                @if(optional($subscription)->status !== 'active')
                <div class="flex justify-center mt-6">
                    <a href="{{ route('login') }}"
                        class="flex items-center justify-center px-6 py-2 rounded-lg bg-red-500 text-white font-semibold shadow hover:bg-red-600">
                        Logout
                    </a>
                </div>
                @endif
            </div>
        </div>

    </div>

</body>

</html>