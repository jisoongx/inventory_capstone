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

<body class="flex items-center justify-center font-[Inter] min-h-screen bg-gray-50 p-6">
    <div class="w-full max-w-4xl bg-white p-8 rounded-xl shadow-lg">

        <!-- Logo -->
        <div class="flex items-center mb-8">
            <img src="{{ asset('assets/logo.png') }}" class="w-12 h-12 object-contain mr-3" alt="Shoplytix Logo" />
            <h1 class="text-red-600 font-bold text-2xl tracking-wide">SHOPLYTIX</h1>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('signup.submit') }}" class="space-y-6">
            @csrf

            <!-- Name Fields -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach(['firstname','middlename','lastname'] as $field)
                <div>
                    <input type="text" name="{{ $field }}" placeholder="{{ ucfirst($field) }}"
                        value="{{ old($field) }}"
                        {{ $field !== 'middlename' ? 'required' : '' }}
                        class="w-full px-4 py-3 border border-black rounded-full shadow-sm text-sm placeholder-gray-500 focus:ring-gray-500 focus:border-transparent transition" />
                    @error($field)
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endforeach
            </div>


            <!-- Store Address -->
            <div>
                <input type="text" name="store_address" placeholder="Store Address" required
                    value="{{ old('store_address') }}"
                    class="w-full px-4 py-3 border border-black rounded-full shadow-sm text-sm placeholder-gray-500  focus:ring-gray-500 focus:border-transparent transition" />
                @error('store_address')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Store Name + Email -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <input type="text" name="store_name" placeholder="Store Name" required
                        value="{{ old('store_name') }}"
                        class="w-full px-4 py-3 border border-black rounded-full shadow-sm text-sm placeholder-gray-500 focus:ring-gray-500 focus:border-transparent transition" />
                    @error('store_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <input type="email" name="email" placeholder="Email Address" required
                        value="{{ old('email') }}"
                        class="w-full px-4 py-3 border border-black rounded-full shadow-sm text-sm placeholder-gray-500  focus:ring-gray-500 focus:border-transparent transition" />
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Password + Confirm -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach([['password','Password','eye1'], ['password_confirmation','Confirm Password','eye2']] as $pw)
                <div class="relative">
                    <input type="password" id="{{ $pw[0] }}" name="{{ $pw[0] }}" placeholder="{{ $pw[1] }}" required
                        oninput="handlePasswordInput('{{ $pw[0] }}','{{ $pw[2] }}')"
                        class="w-full px-4 py-3 border border-black rounded-full shadow-sm text-sm placeholder-gray-500  focus:ring-gray-500 focus:border-transparent transition" />
                    <i id="{{ $pw[2] }}"
                        class="fas fa-eye-slash absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hidden cursor-pointer"
                        onclick="togglePassword('{{ $pw[0] }}','{{ $pw[2] }}')"></i>
                    @error($pw[0])
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endforeach
            </div>

            <!-- Contact + Checkboxes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                <div>
                    <input type="text" name="contact" placeholder="Contact Number" required
                        value="{{ old('contact') }}"
                        class="w-full px-4 py-3 border border-black rounded-full shadow-sm text-sm placeholder-gray-500 focus:ring-gray-500 focus:border-transparent transition" />
                    @error('contact')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col space-y-3 text-sm">
                    <label class="flex items-start gap-2">
                        <input type="checkbox" required class="mt-1 shrink-0 accent-red-700" />
                        <span>I agree to the <a href="#" class="text-red-600 hover:underline">Terms of Service</a> and
                            <a href="#" class="text-red-600 hover:underline">Privacy Policy</a>.</span>
                    </label>
                    <label class="flex items-start gap-2">
                        <input type="checkbox" name="marketing_opt_in" class="mt-1 shrink-0 accent-red-700" {{ old('marketing_opt_in') ? 'checked' : '' }} />
                        <span>Yes, I would like to receive marketing communication.</span>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit"
                    class="bg-black text-white px-12 py-3 rounded-full text-sm font-medium shadow-md hover:bg-gray-800 transition">Sign Up</button>
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