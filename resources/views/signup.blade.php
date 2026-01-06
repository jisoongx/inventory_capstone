<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-700 via-red-800 to-black flex items-center justify-center p-4">

    <!-- Signup Card -->
    <div class="w-full max-w-3xl bg-white backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 px-8 sm:px-10 md:px-12 py-8 md:py-10">

        <!-- Logo -->
        <div class="flex items-center mb-5">
            <img src="{{ asset('assets/logo.png') }}" class="w-14 h-14 object-contain mr-3" alt="Shoplytix Logo" />
            <h1 class="text-red-600 font-bold text-2xl tracking-wide">SHOPLYTIX</h1>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('signup.submit') }}" class="space-y-6">
            @csrf

            <!-- Name Fields -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach(['firstname','middlename','lastname'] as $field)
                <div>
                    <input type="text"
                        name="{{ $field }}"
                        placeholder="{{ ucfirst($field) }}{{ $field !== 'middlename' ? ' *' : '' }}"
                        value="{{ old($field) }}"
                        {{ $field !== 'middlename' ? 'required' : '' }}
                        class="w-full px-4 py-2.5 bg-white border border-gray-400 text-black rounded-xl text-sm shadow-sm placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200" />

                    @error($field)
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endforeach
            </div>


            <!-- Store Address + TIN Number -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <input type="text" name="store_address" placeholder="Store Address *" required
                        value="{{ old('store_address') }}"
                        class="w-full px-4 py-2.5 bg-white border border-gray-400 text-black rounded-xl text-sm shadow-sm placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200" />
                    @error('store_address')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <input type="text" name="tin_number" placeholder="Tax Identifier Number *"
                        value="{{ old('tin_number') }}"
                        class="w-full px-4 py-2.5 bg-white border border-gray-400 text-black rounded-xl text-sm shadow-sm placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200" />
                    @error('tin_number')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>


            <!-- Store Name + Email -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <input type="text" name="store_name" placeholder="Store Name *" required value="{{ old('store_name') }}"
                        class="w-full px-4 py-2.5 bg-white border border-gray-400 text-black rounded-xl text-sm shadow-sm placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200" />
                    @error('store_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <input type="text" name="contact" placeholder="Contact Number *" required value="{{ old('contact') }}"
                        class="w-full px-4 py-2.5 bg-white border border-gray-400 text-black rounded-xl text-sm shadow-sm placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200" />
                    @error('contact')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Password + Confirm -->
            <!-- Password + Confirm -->

            <div>
                <input type="email" name="email" placeholder="Email Address *" required value="{{ old('email') }}"
                    class="w-full px-4 py-2.5 bg-white border border-gray-400 text-black rounded-xl text-sm shadow-sm placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200" />
                @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <!-- PASSWORD SECTION -->
            <div class="space-y-2">

                <!-- Password + Confirm (2 columns) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- LEFT: Password -->
                    <div>
                        <div class="relative">
                            <input type="password" id="password" name="password" placeholder="Password *" required
                                class="w-full pl-4 pr-10 py-2.5 bg-white border border-gray-400 text-black rounded-xl text-sm shadow-sm placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-transparent" />
                            <span id="passwordIcon"
                                class="material-symbols-rounded absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 cursor-pointer hidden"></span>
                        </div>
                        @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- RIGHT: Confirm Password -->
                    <div>
                        <div class="relative">
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password *" required
                                class="w-full pl-4 pr-10 py-2.5 bg-white border border-gray-400 text-black rounded-xl text-sm shadow-sm placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-transparent" />
                            <span id="password_confirmationIcon"
                                class="material-symbols-rounded absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 cursor-pointer hidden"></span>
                        </div>
                        @error('password_confirmation')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Password Requirements -->
                <p class="text-gray-500 text-xs mt-1 text-center md:text-left">
                    Note: Password must be at least 8 characters and include uppercase, lowercase, a number, and a special character.

                </p>


                <!-- Terms Checkbox -->
                <label class="flex items-center gap-2 mt-2 text-gray-700 text-xs cursor-pointer">
                    <input type="checkbox" required class="w-4 h-4 border-gray-400 rounded accent-red-600" />
                    <span>
                        I agree to the
                        <a href="{{ url('welcome/to/shoplytix/#terms') }}" class="text-red-600 hover:underline font-medium">Terms of Service</a>
                        and
                        <a href="{{ url('welcome/to/shoplytix/#policy') }}" class="text-red-600 hover:underline font-medium">Privacy Policy</a>.
                    </span>
                </label>

            </div>

            <!-- Sign Up Button -->
            <button class="w-full bg-red-600 text-white py-2.5 rounded-xl mt-4 hover:bg-red-700">
                Sign Up
            </button>

            <script>
                // Password visibility toggle
                document.querySelectorAll('input[type="password"]').forEach(input => {
                    const icon = document.getElementById(input.id + "Icon");

                    input.addEventListener("input", () => {
                        if (input.value) {
                            icon.classList.remove("hidden");
                            icon.textContent = "visibility_off";
                        } else {
                            icon.classList.add("hidden");
                            icon.textContent = "";
                            input.type = "password";
                        }
                    });

                    icon.addEventListener("click", () => {
                        const isHidden = input.type === "password";
                        input.type = isHidden ? "text" : "password";
                        icon.textContent = isHidden ? "visibility" : "visibility_off";
                    });
                });
            </script>