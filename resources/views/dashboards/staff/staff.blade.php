<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title></title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .nav-label {
            font-size: 0.8rem;
        }
    </style>
</head>

<body class="bg-slate-50 p-0" oncontextmenu="return false;">

    <!-- SIDEBAR -->
    <aside id="sidebar"
        class="w-64 transition-all duration-300 bg-black text-white h-screen fixed top-0 left-0 p-4 flex flex-col justify-between z-50">

        <div>
            <!-- Branding -->
            <div class="flex flex-col items-center justify-center mb-10 mt-5">
                <img src="{{ asset('assets/logo.png') }}" class="w-12 h-12 mb-2">
                <span class="font-poppins text-xl font-semibold">Shoplytix</span>
            </div>

            <!-- Navigation -->
            <nav class="space-y-2">
                <a href="{{ route('staff.dashboard') }}"
                    class="flex items-center gap-3 p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white">
                    <span class="material-symbols-rounded">dashboard</span>
                    <span class="nav-label">Dashboard</span>
                </a>

                <a href="{{ route('inventory-staff') }}"
                    class="flex items-center gap-3 p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white">
                    <span class="material-symbols-rounded">shopping_cart</span>
                    <span class="nav-label">Inventory</span>
                </a>

                <a href=""
                    class="flex items-center gap-3 p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white">
                    <span class="material-symbols-rounded">local_mall</span>
                    <span class="nav-label">Store</span>
                </a>

                <a href="{{ route('restock.list') }}"
                    class="flex items-center gap-3 p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white">
                    <span class="material-symbols-rounded">inventory</span>
                    <span class="nav-label">Restock List</span>
                </a>
                <a href="{{ route('seasonal_trends') }}"
                    class="flex items-center gap-3 p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white">
                    <span class="material-symbols-rounded">trending_up</span>
                    <span class="nav-label">Seasonal Trends</span>
                </a>

                

                <!-- Technical Request -->
                <a href="{{ route('dashboards.staff.technical_request') }}">
                    @livewire('technical-request-menu')
                </a>
            </nav>
        </div>

    </aside>

    <!-- MAIN CONTENT -->
    <main id="mainContent" class="flex-1 p-0 ml-64 duration-300">

        <!-- TOP HEADER -->
        <div class="flex justify-between items-center mr-5 border-b-2 border-gray-300 pb-2 px-2 relative overflow-visible">

            <!-- DATE + CLOCK -->
            <div>
                <span id="date" class="text-sm font-medium text-slate-600"></span>
                <span id="clock" class="text-sm font-medium text-slate-600"></span>
            </div>

            <!-- PROFILE + NOTIFS -->
            <div class="flex items-center gap-6 relative overflow-visible">

                <!-- Livewire Notifications -->
                @livewire('notifications')

                <!-- User Profile -->
                <div class="relative overflow-visible">
                    <button id="userButton" class="focus:outline-none">
                        <img src="{{ asset('assets/user.png') }}" class="w-8 h-8 rounded-full cursor-pointer" alt="User Icon">
                    </button>

                    <!-- DROPDOWN -->
                    <div id="dropdownMenu"
                        class="absolute right-0 mt-2 w-32 bg-white border rounded shadow-lg hidden z-50">

                        <form method="GET" action="{{ route('staff.profile') }}">
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Profile
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Logout
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        </div>

        @yield('content')
    </main>

    <!-- JAVASCRIPT -->
    <script>
        /* -------------------------------
           PROFILE DROPDOWN
        --------------------------------*/
        const userButton = document.getElementById('userButton');
        const dropdownMenu = document.getElementById('dropdownMenu');

        userButton.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });

        // Close dropdown on outside click
        document.addEventListener('click', (e) => {
            if (!dropdownMenu.contains(e.target) && !userButton.contains(e.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });

        /* Debug Listener (Safe to Keep) */
        window.addEventListener('debug-console', event => {
            console.log(event.detail.message);
        });
    </script>

</body>

</html>