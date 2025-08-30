<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    <div class="flex">
        <aside id="sidebar" class="w-20 transition-all duration-300 bg-black text-white h-screen fixed top-0 left-0 p-4 flex flex-col justify-between z-50"> {{-- Added fixed, top-0, left-0, h-screen, z-50 --}}
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
                    <a href="{{ route('clients.index') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                        <span class="material-symbols-rounded">group</span>
                        <span class="nav-label hidden">Client</span>
                    </a>
                    <a href="{{ route('subscription') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                        <span class="material-symbols-rounded">credit_card</span>
                        <span class="nav-label hidden">Subscription</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                        <span class="material-symbols-rounded">notifications</span>
                        <span class="nav-label hidden">Notification</span>
                    </a>
                    <a href="{{ route('actLogs') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                        <span class="material-symbols-rounded">history</span>
                        <span class="nav-label hidden">Activity Log</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                        <span class="material-symbols-rounded">support_agent</span>
                        <span class="nav-label hidden">Tech Support</span>
                    </a>
                </nav>
            </div>
        </aside>

        <main id="mainContent" class="flex-1 p-5 ml-20 transition-all duration-300">
            <div class="flex justify-end items-center mr-5 border-b-2 border-gray-300 pb-5 relative">
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
    <script>
        const sidebar = document.getElementById('sidebar');
        const menu = document.getElementById('menu');
        const menuIcon = document.getElementById('menuIcon');
        const labels = sidebar.querySelectorAll('.nav-label');
        const mainContent = document.getElementById('mainContent'); // Get the main content area

        menu.addEventListener('click', () => {
            const isExpanded = sidebar.classList.contains('w-64');

            if (isExpanded) {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20');
                labels.forEach(label => label.classList.add('hidden'));
                menuIcon.textContent = 'arrow_forward_ios';
                mainContent.classList.remove('ml-64'); // Adjust main content margin
                mainContent.classList.add('ml-20'); // Adjust main content margin
            } else {
                sidebar.classList.remove('w-20');
                sidebar.classList.add('w-64');
                labels.forEach(label => label.classList.remove('hidden'));
                menuIcon.textContent = 'arrow_back_ios';
                mainContent.classList.remove('ml-20'); // Adjust main content margin
                mainContent.classList.add('ml-64'); // Adjust main content margin
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