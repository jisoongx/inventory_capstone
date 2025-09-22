<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Reset Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

</head>

<body class="min-h-screen bg-gradient-to-br from-gray-700 via-red-800 to-black flex items-center justify-center p-4">

    <div class="w-full max-w-sm  rounded-2xl shadow-2xl backdrop-blur-xl p-8 flex flex-col items-center gap-6">
        <!-- Logo + Title -->
        <div class="flex flex-col items-center gap-2">
            <img src="{{ asset('assets/logo.png') }}" class="w-14 drop-shadow-md" alt="ShopLytix Logo">
            <h1 class="text-2xl font-bold text-red-600 tracking-wide">Reset Password</h1>
        </div>

        <!-- Reset Password Form -->
        <form method="POST" action="{{ route('password.update') }}" class="w-full flex flex-col gap-4">
            @csrf

            <!-- Hidden token from email link -->
            <input type="hidden" name="token" value="{{ $token }}">

            <!-- Email -->
            <div class="relative">
                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">person</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full pl-12 pr-4 py-2.5 text-sm 
                           bg-white border border-gray-300 rounded-lg 
                           shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 
                           placeholder-gray-400 transition"
                    placeholder="Email" />
                @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="relative">
                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">lock</span>
                <input type="password" id="password" name="password" required
                    class="w-full pl-12 pr-10 py-2.5 text-sm 
                           bg-white border border-gray-300 rounded-lg 
                           shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 
                           placeholder-gray-400 transition"
                    placeholder="New Password" />
                <span id="togglePasswordIcon"
                    class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer hidden"></span>
                @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="relative">
                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">lock_reset</span>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    class="w-full pl-12 pr-10 py-2.5 text-sm 
                           bg-white border border-gray-300 rounded-lg 
                           shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 
                           placeholder-gray-400 transition"
                    placeholder="Confirm Password" />
                <span id="toggleConfirmPasswordIcon"
                    class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer hidden"></span>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full py-2.5 text-sm font-medium shadow-md text-white bg-red-600 rounded-lg hover:bg-red-700 hover:scale-[1.02] active:scale-[0.98] transition transform">
                Reset Password
            </button>
        </form>
    </div>

    <script>
        // Toggle password visibility for both fields
        function setupToggle(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            input.addEventListener("input", () => {
                if (input.value) {
                    icon.classList.remove("hidden");
                    icon.textContent = "visibility_off";
                } else {
                    icon.classList.add("hidden");
                    icon.textContent = "";
                }
            });

            icon.addEventListener("click", () => {
                const isHidden = input.type === "password";
                input.type = isHidden ? "text" : "password";
                icon.textContent = isHidden ? "visibility" : "visibility_off";
            });
        }

        setupToggle("password", "togglePasswordIcon");
        setupToggle("password_confirmation", "toggleConfirmPasswordIcon");
    </script>
</body>

</html>