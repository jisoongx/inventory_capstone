<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
    <script src="https://unpkg.com/flowbite@latest/dist/flowbite.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:FILL@0..1" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .nav-label {
            font-size: 0.875rem;
        }
    </style>
</head>

<body class="bg-gradient-to-b from-white to-blue-100 min-h-screen">

    <aside id="sidebar" class="w-20 transition-all duration-300 bg-black text-white h-screen fixed top-0 left-0 p-4 flex flex-col justify-between z-50">
        <div>
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('assets/logo.png') }}" class="w-8 h-8 rounded">
                    <span id="brandName" class="text-lg font-semibold nav-label hidden">Shoplytix</span>
                </div>
                <button id="menu" class="ml-auto">
                    <span id="menuIcon" class="material-symbols-rounded text-white text-sm">arrow_forward_ios</span>
                </button>
            </div>

            <nav class="space-y-2">
                <a href="{{ route('dashboards.owner.dashboard') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                    <span class="material-symbols-rounded">dashboard</span>
                    <span class="nav-label hidden">Dashboard</span>
                </a>
                <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                    <span class="material-symbols-rounded">shopping_cart</span>
                    <span class="nav-label hidden">Inventory</span>
                </a>
                <div class="group">
                    <button id="reportsToggle" class="w-full flex items-center justify-between p-3 rounded hover:bg-red-600 hover:text-white">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-rounded">stacked_line_chart</span>
                            <span class="nav-label hidden">Reports</span>
                        </div>
                        <span class="nav-label hidden material-symbols-rounded">keyboard_arrow_down</span>
                    </button>

                    <div id="reportsDropdown" class="hidden ml-3 mt-2 space-y-1 border-l-4 border-gray-600">
                        <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                            <span class="nav-label hidden material-symbols-rounded">history_toggle_off</span>
                            <span class="nav-label hidden text-sm">Activty Log</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                            <span class="nav-label hidden material-symbols-rounded">support_agent</span>
                            <span class="nav-label hidden text-sm">Technical Support</span>
                        </a>
                    </div>
                </div>
                <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                    <span class="material-symbols-rounded">local_mall</span>
                    <span class="nav-label hidden">Store</span>
                </a>
            </nav>
        </div>
    </aside>

    <main id="mainContent" class="flex-1 p-3 ml-20 transition-all duration-300">
        <div class="flex justify-end items-center mr-5 border-b-2 border-gray-300 relative pb-2">
            <div class="relative">
                <button id="userButton" class="focus:outline-none">
                    <img src="{{ asset('assets/user.png') }}" class="w-8 h-8 rounded-full" alt="User Icon">
                </button>
                <div id="dropdownMenu" class="absolute right-0 mt-2 w-32 bg-white border rounded shadow-lg hidden z-10">
                    <form method="GET" action="{{ route('owner.profile') }}">
                        {{-- Removed @csrf as it's not needed for GET requests --}}
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-red-100">Profile</button>
                    </form>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-red-100">Logout</button>
                    </form>
                </div>
            </div>
        </div>
        @yield('content')
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const menu = document.getElementById('menu');
        const menuIcon = document.getElementById('menuIcon');
        const labels = sidebar.querySelectorAll('.nav-label');
        const reportsToggle = document.getElementById('reportsToggle');
        const reportsDropdown = document.getElementById('reportsDropdown');
        const mainContent = document.getElementById('mainContent'); // Added this line

        reportsToggle.addEventListener('click', () => {
            reportsDropdown.classList.toggle('hidden');
        });

        menu.addEventListener('click', () => {
            const isExpanded = sidebar.classList.contains('w-64');

            if (isExpanded) {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20');
                labels.forEach(label => label.classList.add('hidden'));
                menuIcon.textContent = 'arrow_forward_ios';
                reportsDropdown.classList.add('hidden');
                mainContent.classList.remove('ml-64'); // Added this line
                mainContent.classList.add('ml-20'); // Added this line
            } else {
                sidebar.classList.remove('w-20');
                sidebar.classList.add('w-64');
                labels.forEach(label => label.classList.remove('hidden'));
                menuIcon.textContent = 'arrow_back_ios';
                reportsDropdown.classList.add('hidden');
                mainContent.classList.remove('ml-20'); // Added this line
                mainContent.classList.add('ml-64'); // Added this line
            }
        });

        // User dropdown toggle
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