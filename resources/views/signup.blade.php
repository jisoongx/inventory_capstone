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
        <div class="flex items-center mb-8">
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
                    <input type="text" name="{{ $field }}" placeholder="{{ ucfirst($field) }}"
                        value="{{ old($field) }}" {{ $field !== 'middlename' ? 'required' : '' }}
                        class="w-full px-4 py-2.5 bg-white border border-gray-400 text-black rounded-xl text-sm shadow-sm placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200" />
                    @error($field)
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endforeach
            </div>

            <!-- Store Address -->
            <div>
                <input type="text" name="store_address" placeholder="Store Address" required value="{{ old('store_address') }}"
                    class="w-full px-4 py-2.5 bg-white border border-gray-400 text-black rounded-xl text-sm shadow-sm placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200" />
                @error('store_address')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Store Name + Email -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <input type="text" name="store_name" placeholder="Store Name" required value="{{ old('store_name') }}"
                        class="w-full px-4 py-2.5 bg-white border border-gray-400 text-black rounded-xl text-sm shadow-sm placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200" />
                    @error('store_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <input type="email" name="email" placeholder="Email Address" required value="{{ old('email') }}"
                        class="w-full px-4 py-2.5 bg-white border border-gray-400 text-black rounded-xl text-sm shadow-sm placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200" />
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Password + Confirm -->
            <!-- Password + Confirm -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach([['password','Password'], ['password_confirmation','Confirm Password']] as $pw)
                <div class="relative flex flex-col">
                    <!-- Input Field with Eye Icon inside the container -->
                    <div class="relative">
                        <input type="password" id="{{ $pw[0] }}" name="{{ $pw[0] }}" placeholder="{{ $pw[1] }}" required
                            class="w-full pl-4 pr-10 py-2.5 bg-white border border-gray-400 text-black rounded-xl text-sm shadow-sm placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200" />

                        <!-- Eye Icon for Password Visibility Toggle -->
                        <span id="{{ $pw[0] }}Icon"
                            class="material-symbols-rounded absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600 cursor-pointer hidden"></span>
                    </div>

                    <!-- Error Message -->
                    @error($pw[0])
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endforeach
            </div>


            <!-- Contact + Checkboxes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-start">
                <div>
                    <input type="text" name="contact" placeholder="Contact Number" required value="{{ old('contact') }}"
                        class="w-full px-4 py-2.5 bg-white border border-gray-400 text-black rounded-xl text-sm shadow-sm placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200" />
                    @error('contact')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col space-y-2 text-xs">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" required class="shrink-0 border-gray-400 accent-red-600" />
                        <span>
                            I agree to the
                            <a href="#" class="text-red-600 hover:underline">Terms of Service</a> and
                            <a href="#" class="text-red-600 hover:underline">Privacy Policy</a>.
                        </span>
                    </label>
                </div>

            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit"
                    class="bg-red-600 text-white px-16 py-3 rounded-xl text-sm font-medium shadow-lg hover:bg-red-700 hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 transform">
                    Sign Up
                </button>
            </div>
        </form>
    </div>

    <script>
        // For each password input, toggle visibility icon like in login
        document.querySelectorAll('input[type="password"]').forEach(input => {
            const icon = document.getElementById(input.id + "Icon");

            input.addEventListener("input", () => {
                if (input.value) {
                    icon.classList.remove("hidden");
                    icon.textContent = "visibility_off"; // show eye-off when typing
                } else {
                    icon.classList.add("hidden");
                    icon.textContent = "";
                    input.type = "password"; // reset type if cleared
                }
            });

            icon.addEventListener("click", () => {
                const isHidden = input.type === "password";
                input.type = isHidden ? "text" : "password";
                icon.textContent = isHidden ? "visibility" : "visibility_off";
            });
        });
    </script>
</body>

</html>