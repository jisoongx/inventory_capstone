<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</head>

<body class="flex items-center justify-center font-[Inter] min-h-screen bg-white p-6">
    <div class="w-full max-w-4xl">

        <div class="flex items-center mb-5">
            <img src="{{ asset('assets/logo.png') }}" class="w-12 h-12 object-contain mr-2" alt="Shoplytix Logo" />
            <h1 class="text-red-600 font-bold text-2xl">SHOPLYTIX</h1>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('signup.submit') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <input type="text" name="firstname" placeholder="First Name" required
                        value="{{ old('firstname') }}"
                        class="w-full px-4 py-3 border border-black rounded-full shadow text-sm placeholder-gray-600
                         @error('firstname') @enderror" />
                    @error('firstname')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <input type="text" name="middlename" placeholder="Middle Name"
                        value="{{ old('middlename') }}"
                        class="w-full px-4 py-3 border border-black rounded-full shadow text-sm placeholder-gray-600
                        @error('middlename') @enderror" />
                    @error('middlename')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <input type="text" name="lastname" placeholder="Last Name" required
                        value="{{ old('lastname') }}"
                        class="w-full px-4 py-3 border border-black rounded-full shadow text-sm placeholder-gray-600
                         @error('lastname') @enderror" />
                    @error('lastname')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <input type="text" name="store_address" placeholder="Store Address" required
                    value="{{ old('store_address') }}"
                    class="w-full px-4 py-3 border border-black rounded-full shadow text-sm placeholder-gray-600
                    @error('store_address') @enderror" />
                @error('store_address')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <input type="text" name="store_name" placeholder="Store Name" required
                        value="{{ old('store_name') }}"
                        class="w-full px-4 py-3 border border-black rounded-full shadow text-sm placeholder-gray-600
                        @error('store_name') @enderror" />
                    @error('store_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <input type="email" name="email" placeholder="Email Address" required
                        value="{{ old('email') }}"
                        class="w-full px-4 py-3 border border-black rounded-full shadow text-sm placeholder-gray-600
                        @error('email') @enderror" />
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="relative">
                        <input type="password" id="password" name="password" placeholder="Password" required
                            oninput="handlePasswordInput('password','eye1')"
                            class="w-full px-4 py-3 border border-black rounded-full shadow text-sm placeholder-gray-600
                @error('password') @enderror" />
                        <i id="eye1"
                            class="fas fa-eye-slash absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hidden cursor-pointer"
                            onclick="togglePassword('password','eye1')"></i>
                    </div>
                    @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="relative">
                        <input type="password" id="confirm" name="password_confirmation" placeholder="Confirm Password" required
                            oninput="handlePasswordInput('confirm','eye2')"
                            class="w-full px-4 py-3 border border-black rounded-full shadow text-sm placeholder-gray-600
                @error('password_confirmation') @enderror" />
                        <i id="eye2"
                            class="fas fa-eye-slash absolute right-4 top-1/2 -translate-y-1/2 text-gray-800 hidden cursor-pointer"
                            onclick="togglePassword('confirm','eye2')"></i>
                    </div>
                    @error('password_confirmation')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                <div>
                    <input type="text" name="contact" placeholder="Contact Number" required
                        value="{{ old('contact') }}"
                        class="w-full px-4 py-3 border border-black rounded-full shadow text-sm placeholder-gray-600
                        @error('contact')  @enderror" />
                    @error('contact')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col space-y-3 text-sm">
                    <label class="flex items-start gap-2">
                        <input type="checkbox" required class="mt-1 shrink-0" />
                        <span>I agree to the <a href="#" class="text-blue-600">Terms of Service</a> and
                            <a href="#" class="text-blue-600">Privacy Policy</a>.
                        </span>
                    </label>
                    <label class="flex items-start gap-2">
                        <input type="checkbox" name="marketing_opt_in" class="mt-1 shrink-0" {{ old('marketing_opt_in') ? 'checked' : '' }} />

                        <span>Yes, I would like to receive marketing communication.</span>
                    </label>
                </div>
            </div>

            <div class="text-center">
                <button type="submit"
                    class="bg-black text-white px-10 py-3 rounded-2xl text-sm hover:bg-gray-800">Sign Up</button>
            </div>
        </form>
    </div>

    <script>
        function togglePassword(id, eye) {
            const input = document.getElementById(id);
            const icon = document.getElementById(eye);
            const hidden = input.type === "password";
            input.type = hidden ? "text" : "password";
            icon.classList.toggle("fa-eye", hidden);
            icon.classList.toggle("fa-eye-slash", !hidden);
        }

        function handlePasswordInput(id, eye) {
            const input = document.getElementById(id);
            const icon = document.getElementById(eye);
            icon.style.display = input.value.trim() ? "block" : "none";
            if (!input.value.trim()) {
                input.type = "password";
                icon.classList.add("fa-eye-slash");
                icon.classList.remove("fa-eye");
            }
        }
    </script>
</body>

</html>