<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>ShopLytix Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-700 via-red-800 to-black flex items-center justify-center p-4">

    <!-- Login Card -->
    <div class="w-full max-w-md bg-white backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 p-8">
        
        <!-- Logo Section -->
        <div class="text-center mb-8">
            <img src="{{ asset('assets/logo.png') }}" class="w-14 mx-auto drop-shadow-md mb-4" alt="ShopLytix Logo">
            <h1 class="text-2xl font-bold text-red-600 tracking-wide">SHOPLYTIX</h1>
        </div>

        {{-- Error/Success Modal --}}
        @if(session('success') || session('login_error') || session('error') || $errors->any())
        @php
        $msg = session('success') ?? session('login_error') ?? session('error') ?? $errors->first();
        $isSuccess = session('success') ? true : false;
        @endphp
        <div id="messageModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl p-6 shadow-xl max-w-sm w-full text-center">
                <div class="w-12 h-12 mx-auto mb-4 rounded-full flex items-center justify-center {{ $isSuccess ? 'bg-green-100' : 'bg-red-100' }}">
                    <span class="material-symbols-rounded text-2xl {{ $isSuccess ? 'text-green-600' : 'text-red-600' }}">
                        {{ $isSuccess ? 'check_circle' : 'error' }}
                    </span>
                </div>
                <p class="text-gray-700 mb-4">{{ $msg }}</p>
                <button onclick="document.getElementById('messageModal').remove()"
                    class="px-6 py-2 rounded-lg text-white font-medium transition {{ $isSuccess ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">
                    OK
                </button>
            </div>
        </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email Input -->
            <div>
              
                <div class="relative">
                    <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xl">person</span>
                    <input type="email" name="email" placeholder="Enter your email" required
                        class="w-full pl-11 pr-4 py-3 bg-white border border-gray-400 rounded-xl text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 placeholder-gray-500" />
                </div>
            </div>

            <!-- Password Input -->
            <div>
              
                <div class="relative">
                    <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xl">lock</span>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required
                        class="w-full pl-11 pr-12 py-3 bg-white border border-gray-400 rounded-xl text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 placeholder-gray-500" />
                    <span id="togglePasswordIcon"
                        class="material-symbols-rounded absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer opacity-0 transition-opacity duration-200 text-xl">
                    </span>
                </div>
            </div>

            <!-- Forgot Password -->
            <div class="text-right">
                <a href="{{route('password.request')}}" 
                   class="text-sm text-red-600 hover:text-red-700 font-medium transition-colors">
                    Forgot password?
                </a>
            </div>

            <!-- Login Button -->
            <button type="submit"
                class="w-full py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200">
                Login
            </button>
        </form>

        <!-- Sign Up Link -->
        <div class="mt-6 pt-6 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-600">
                Don't have an account? 
                <a href="{{ route('signup') }}" class="text-red-600 hover:text-red-700 font-semibold hover:underline transition-colors">
                    Sign up
                </a>
            </p>
        </div>
    </div>

    <script>
        const pwd = document.getElementById("password");
        const icon = document.getElementById("togglePasswordIcon");

        pwd.addEventListener("input", () => {
            if (pwd.value) {
                icon.classList.remove("opacity-0");
                icon.classList.add("opacity-100");
                icon.textContent = "visibility_off";
            } else {
                icon.classList.add("opacity-0");
                icon.classList.remove("opacity-100");
            }
        });

        icon.addEventListener("click", () => {
            const isPassword = pwd.type === "password";
            pwd.type = isPassword ? "text" : "password";
            icon.textContent = isPassword ? "visibility" : "visibility_off";
        });
    </script>

</body>

</html>