<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Reset Password - ShopLytix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-700 via-red-800 to-black flex items-center justify-center p-4">

    <div class="w-full max-w-sm bg-white backdrop-blur-xl shadow-2xl rounded-xl p-8 flex flex-col items-center gap-6">
        <!-- Logo -->
        <div class="flex flex-col items-center gap-2">
            <img src="{{ asset('assets/logo.png') }}" class="w-14 drop-shadow-md" alt="ShopLytix Logo">
            <h1 class="text-2xl font-bold text-red-600 tracking-wide">SHOPLYTIX</h1>
        </div>

        <!-- Reset Form -->
        <form method="POST" action="{{ route('password.update') }}" class="w-full flex flex-col gap-4">
            @csrf

            <!-- Hidden Fields -->
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <!-- New Password -->
            <div class="relative">
                <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xl">lock</span>
                <input type="password" id="password" name="password" placeholder="New Password" required
                    class="w-full pl-11 pr-12 py-3 bg-white border border-gray-400 rounded-xl text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 placeholder-gray-500" />
                <span id="togglePasswordIcon"
                    class="material-symbols-rounded absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer opacity-0 transition-opacity duration-200 text-xl"></span>
                @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="relative">
                <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xl">lock</span>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password" required
                    class="w-full pl-11 pr-12 py-3 bg-white border border-gray-400 rounded-xl text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 placeholder-gray-500" />
                <span id="toggleConfirmIcon"
                    class="material-symbols-rounded absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer opacity-0 transition-opacity duration-200 text-xl"></span>
            </div>

            <!-- Submit -->
            <button type="submit"
                class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200">
                Reset Password
            </button>
        </form>

       
    </div>

    <!-- JS for Toggle Visibility -->
    <script>
        // For new password
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

        // For confirm password
        const confirmPwd = document.getElementById("password_confirmation");
        const confirmIcon = document.getElementById("toggleConfirmIcon");

        confirmPwd.addEventListener("input", () => {
            if (confirmPwd.value) {
                confirmIcon.classList.remove("opacity-0");
                confirmIcon.classList.add("opacity-100");
                confirmIcon.textContent = "visibility_off";
            } else {
                confirmIcon.classList.add("opacity-0");
                confirmIcon.classList.remove("opacity-100");
            }
        });

        confirmIcon.addEventListener("click", () => {
            const isPassword = confirmPwd.type === "password";
            confirmPwd.type = isPassword ? "text" : "password";
            confirmIcon.textContent = isPassword ? "visibility" : "visibility_off";
        });
    </script>
</body>

</html>