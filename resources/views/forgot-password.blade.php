<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Forgot Password - ShopLytix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-700 via-red-800 to-black flex items-center justify-center p-4">

    <div class="w-full max-w-sm bg-white backdrop-blur-xl shadow-2xl rounded-xl p-8 flex flex-col items-center gap-6">
        <!-- Logo + Title -->
        <div class="flex flex-col items-center gap-2">
            <img src="{{ asset('assets/logo.png') }}" class="w-14 drop-shadow-md" alt="ShopLytix Logo">
            <h1 class="text-2xl font-bold text-red-600 tracking-wide">SHOPLYTIX</h1>
        </div>

        <!-- Message -->
        @if (session('status'))
        <div class="w-full bg-green-100 border border-green-300 text-green-700 text-sm px-4 py-2 rounded-md text-center">
            {{ session('status') }}
        </div>
        @endif

        <!-- Forgot Password Form -->
        <form method="POST" action="{{ route('password.email') }}" class="w-full flex flex-col gap-4">
            @csrf

            <p class="text-sm text-gray-600 text-center">
                Enter your email and we’ll send you a reset link.
            </p>

            <!-- Email -->
            <div class="w-full">
                <div class="relative">
                    <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        mail
                    </span>
                    <input type="email" name="email" placeholder="Email" required
                        class="w-full pl-12 pr-4 py-2.5 text-sm 
                               bg-white border border-gray-300 rounded-lg 
                               shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 
                               placeholder-gray-400 transition" />
                </div>
                @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit -->
            <button type="submit"
                class="w-full py-2.5 text-sm font-medium shadow-md text-white bg-red-600 rounded-lg hover:bg-red-700 hover:scale-[1.02] active:scale-[0.98] transition transform">
                Send Reset Link
            </button>
        </form>

        <!-- Back to login -->
        <p class="text-xs text-gray-700">
            Remembered your password?
            <a href="{{ route('login') }}" class="text-red-600 font-medium hover:underline">Back to Login</a>
        </p>
    </div>
</body>

</html>