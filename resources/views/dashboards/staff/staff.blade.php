<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/flowbite@latest/dist/flowbite.min.js"></script>
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            /* Removed 'flex' from body as sidebar is now fixed */
        }

        .nav-label {
            font-size: 0.875rem;
        }
    </style>
</head>

<body class="bg-gradient-to-b from-white to-blue-100 min-h-screen"> {{-- Removed 'flex' from body --}}

    <aside id="sidebar" class="w-64 transition-all duration-300 bg-black text-white h-screen fixed top-0 left-0 p-4 flex flex-col justify-between z-50"> {{-- Added fixed, top-0, left-0, h-screen, z-50 --}}
        <div>
            <div class="flex flex-col items-center justify-center mb-6 mt-5">
                <img src="{{ asset('assets/logo.png') }}" class="w-12 h-12 mb-2">
                <span id="brandName" class="text-lg font-semibold nav-label">Shoplytix</span>
            </div>

            <nav class="space-y-2">
                {{-- Dashboard Link --}}
                <a href="{{ route('staff.dashboard') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                    <span class="material-symbols-rounded">dashboard</span>
                    <span class="nav-label">Dashboard</span>
                </a>
                {{-- Inventory Link --}}
                <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                    <span class="material-symbols-rounded">shopping_cart</span>
                    <span class="nav-label">Inventory</span>
                </a>
                {{-- Removed Reports section HTML as per user's request --}}
                {{-- Store Link --}}
                <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                    <span class="material-symbols-rounded">local_mall</span>
                    <span class="nav-label">Store</span>
                </a>
                <a href="{{ route('dashboards.staff.technical_request') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                    <span class="material-symbols-rounded">support_agent</span>
                    <span class="nav-label">Technical Support</span>
                </a>
            </nav>
        </div>
    </aside>

    <main id="mainContent" class="flex-1 p-3 ml-64 transition-all duration-300"> {{-- Added ID and dynamic ml- --}}
        <div class="flex justify-end items-center mr-5 border-b-2 border-gray-300 pb-2 relative">
            <div class="relative">
                <button id="userButton" class="focus:outline-none">
                    <img src="{{ asset('assets/user.png') }}" class="w-8 h-8 rounded-full" alt="User Icon">
                </button>
                <div id="dropdownMenu" class="absolute right-0 mt-2 w-32 bg-white border rounded shadow-lg hidden z-10">
                    <form method="GET" action="{{ route('staff.profile') }}">
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

    <script>
        const userButton = document.getElementById('userButton');
        const dropdownMenu = document.getElementById('dropdownMenu');

        userButton.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });
    </script>

</body>

</html>