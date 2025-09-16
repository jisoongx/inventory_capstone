<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>ShopLytix Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />
</head>

<body class="min-h-screen flex items-center justify-center bg-white font-poppins p-4">

    <div class="w-full max-w-sm bg-white p-8 flex flex-col items-center gap-6">
        <!-- Logo + Title -->
        <div class="flex flex-col items-center gap-2">
            <img src="{{ asset('assets/logo.png') }}" class="w-14 drop-shadow-md" alt="ShopLytix Logo">
            <h1 class="text-2xl font-bold text-red-600 tracking-wide">SHOPLYTIX</h1>
        </div>

        {{-- MODAL --}}
        @if(session('success') || session('login_error') || session('error') || $errors->any())
        @php
        $msg = session('success') ?? session('login_error') ?? session('error') ?? $errors->first();
        $type = session('success') ? 'success' : 'error';
        @endphp
        <div id="messageModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center px-4">
            <div class="bg-white rounded-xl p-5 shadow-lg max-w-xs w-full text-center">
                <div class="flex justify-center mb-2">
                    <span class="material-symbols-rounded text-3xl {{ $type === 'success' ? 'text-green-500' : 'text-red-500' }}">
                        {{ $type === 'success' ? 'check_circle' : 'error' }}
                    </span>
                </div>
                <p class="text-sm text-gray-700 mb-3">{{ $msg }}</p>
                <button onclick="document.getElementById('messageModal').remove()"
                    class="px-6 py-2 rounded-lg text-white font-medium shadow {{ $type === 'success' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }} transition">
                    OK
                </button>
            </div>
        </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="w-full flex flex-col gap-4">
            @csrf

            <!-- Email -->
            <div class="relative">
                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-900">
                    person
                </span>
                <input type="email" name="email" placeholder="Email" required
                    class="w-full pl-12 pr-4 py-2.5 text-sm 
                           bg-white border border-black rounded-lg 
                           shadow-sm focus:ring-1 focus:ring-black focus:border-black
                           placeholder-gray-700 transition" />
            </div>

            <!-- Password -->
            <div class="relative">
                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-900">
                    lock
                </span>
                <input type="password" id="password" name="password" placeholder="Password" required
                    class="w-full pl-12 pr-10 py-2.5 text-sm 
                           bg-white border border-black rounded-lg 
                           shadow-sm focus:ring-1 focus:ring-black focus:border-black
                           placeholder-gray-700 transition" />
                <span id="togglePasswordIcon"
                    class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 cursor-pointer hidden">
                </span>
            </div>

            <!-- Forgot Password -->
            <div class="text-center">
                <a href="{{route('password.request')}}" class="text-xs text-gray-600 hover:text-red-600 transition">Forgot password?</a>
            </div>

            <!-- Login Button -->
            <button type="submit"
                class="w-full py-2.5 text-sm font-medium shadow-md text-white bg-red-600 rounded-lg hover:bg-red-700 hover:scale-[1.02] active:scale-[0.98] transition transform">
                Login
            </button>
            
        </form>

        <!-- Sign Up Link -->
        <p class="text-xs text-gray-700">
            Don’t have an account?
            <a href="{{ route('signup') }}" class="text-red-600 font-medium hover:underline">Sign up</a>
        </p>
    </div>

    <script>
        const pwd = document.getElementById("password");
        const icon = document.getElementById("togglePasswordIcon");

        pwd.addEventListener("input", () => {
            if (pwd.value) {
                icon.classList.remove("hidden");
                icon.textContent = "visibility_off"; // default when typing starts
            } else {
                icon.classList.add("hidden");
                icon.textContent = "";
            }
        });

        icon.addEventListener("click", () => {
            const isHidden = pwd.type === "password";
            pwd.type = isHidden ? "text" : "password";
            icon.textContent = isHidden ? "visibility" : "visibility_off";
        });
    </script>
</body>

</html>