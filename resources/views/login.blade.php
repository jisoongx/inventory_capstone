<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>ShopLytix Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
</head>

<body class="bg-white font-[Inter] flex items-center justify-center min-h-screen p-6">
    <div class="w-full max-w-[340px] text-center flex flex-col items-center gap-6">
        <img src="{{ asset('assets/logo.png') }}" class="w-14 -mb-5 -ml-2" alt="ShopLytix Logo">
        <h1 class="text-2xl font-bold text-red-600">SHOPLYTIX</h1>

        {{-- MODAL --}}
        @if(session('success') || session('login_error') || session('error') || $errors->any())
        @php
        $msg = session('success') ?? session('login_error') ?? session('error') ?? $errors->first();
        $type = session('success') ? 'success' : 'error';
        @endphp
        <div id="messageModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center px-4">
            <div class="bg-white rounded-xl p-4 shadow-lg max-w-xs w-full text-center">
                <p class="text-sm text-gray-700 mb-4">{{ $msg }}</p>
                <button onclick="document.getElementById('messageModal').remove()"
                    class="px-6 py-2 rounded-md text-white font-medium {{ $type === 'success' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">
                    OK
                </button>
            </div>
        </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="w-full flex flex-col gap-5 items-center">
            @csrf
            <div class="relative w-full max-w-[280px]">
                <i class="fas fa-user absolute left-5 top-1/2 -translate-y-1/2 text-black"></i>
                <input type="email" name="email" placeholder="User" required
                    class="w-full pl-12 pr-4 py-4 text-sm border border-black rounded-full shadow font-medium  focus:ring-gray-700  placeholder-gray-600" />
            </div>
            <div class="relative w-full max-w-[280px]">
                <i class="fas fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-black"></i>
                <input type="password" id="password" name="password" placeholder="Password" required
                    class="w-full pl-12 pr-10 py-4 text-sm border border-black rounded-full shadow font-medium  focus:ring-gray-700  placeholder-gray-600" />
                <i id="togglePasswordIcon"
                    class="fas fa-eye-slash absolute right-5 top-1/2 -translate-y-1/2 text-gray-500 cursor-pointer hidden"></i>
            </div>
            <button type="submit"
                class="px-10 py-4 text-sm font-medium shadow-md text-white bg-black rounded-full hover:bg-gray-800 transition max-w-[160px] w-full">
                Login
            </button>
        </form>

        <p class="text-sm text-black">
            Don’t have an account?
            <a href="{{ route('signup') }}" class="text-red-600 font-bold underline">Sign up</a>
        </p>
    </div>

    <script>
        const pwd = document.getElementById("password");
        const icon = document.getElementById("togglePasswordIcon");

        pwd.addEventListener("input", () => icon.style.display = pwd.value ? "block" : "none");
        icon.addEventListener("click", () => {
            pwd.type = pwd.type === "password" ? "text" : "password";
            icon.classList.toggle("fa-eye");
            icon.classList.toggle("fa-eye-slash");
        });
    </script>
</body>

</html>