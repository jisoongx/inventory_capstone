<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        .nav-label {
            font-size: 0.85rem;
        }
    </style>
</head>

<body class="bg-gradient-to-b from-white to-blue-100 min-h-screen">
    <div class="flex">
        <aside id="sidebar" class="w-64 transition-all duration-300 bg-black text-white h-screen fixed top-0 left-0 p-4 flex flex-col justify-between z-50"> {{-- Added fixed, top-0, left-0, h-screen, z-50 --}}
            <div>
                <div class="flex flex-col items-center justify-center mb-10 mt-5">
                    <img src="{{ asset('assets/logo.png') }}" class="w-12 h-12 mb-2">
                    <span id="brandName" class="font-poppins text-xl font-semibold">Shoplytix</span>
                </div>

                <nav class="space-y-2">

                    <a href="{{ route('subscription') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                        <span class="material-symbols-rounded">credit_card</span>
                        <span class="nav-label">Subscription</span>
                    </a>
                    <a href="{{ route('billing.history') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                        <span class="material-symbols-rounded">receipt_long</span>
                        <span class="nav-label">Billing History</span>
                    </a>
                    <a href="{{ route('dashboards.super_admin.notification') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                        <span class="material-symbols-rounded">notifications</span>
                        <span class="nav-label">Notification</span>
                    </a>
                    <a href="{{ route('actLogs') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                        <span class="material-symbols-rounded">history</span>
                        <span class="nav-label">Activity Log</span>
                    </a>
                    <a href="{{ route('dashboards.super_admin.technical') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                        <span class="material-symbols-rounded">support_agent</span>
                        <span class="nav-label">Tech Support</span>
                    </a>
                </nav>
            </div>
        </aside>

        <main id="mainContent" class="flex-1 p-3 ml-64 transition-all duration-300">
            <div class="flex justify-end items-center mr-5 border-b-2 border-gray-300 relative pb-2">
                <div class="relative">
                    <button id="userButton" class="focus:outline-none">
                        <img src="{{ asset('assets/user.png') }}" class="w-8 h-8 rounded-full" alt="User Icon">
                    </button>
                    <div id="dropdownMenu" class="absolute right-0 mt-2 w-32 bg-white border rounded shadow-lg hidden z-10">
                        <form method="GET" action="{{ route('super_admin.profile') }}">
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</button>
                        </form>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
            @yield('content')

        </main>
    </div>
    
     @livewireScripts
    <script>
        const userButton = document.getElementById('userButton');
        const dropdownMenu = document.getElementById('dropdownMenu');

        userButton.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!userButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
    </script>

</body>

</html>