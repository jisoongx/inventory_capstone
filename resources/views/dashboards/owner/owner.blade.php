<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title></title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">

    <!-- jQuery + DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    
    <style>
        .nav-label {
            font-size: 0.85rem;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen" oncontextmenu="return false;">

    <aside id="sidebar" class="w-64 transition-all duration-300 bg-black text-white h-screen fixed top-0 left-0 p-4 flex flex-col justify-between z-50">
        <div>
            <div class="flex flex-col items-center justify-center mb-6 mt-5">
                <img src="{{ asset('assets/logo.png') }}" class="w-12 h-12 mb-2">
                <span id="brandName" class="font-poppins text-lg font-semibold nav-label">Shoplytix</span>
            </div>

            <nav class="space-y-2">
                <a href="{{ route('dashboards.owner.dashboard') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white" title="Dashboard">
                    <span class="material-symbols-rounded">dashboard</span>
                    <span class="nav-label">Dashboard</span>
                </a>
                <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white" title="Inventory">
                    <span class="material-symbols-rounded">shopping_cart</span>
                    <span class="nav-label">Inventory</span>
                </a>
                <div class="group">
                    <button id="reportsToggle" class="w-full flex items-center justify-between p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white"  title="Reports">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-rounded">stacked_line_chart</span>
                            <span class="nav-label">Reports</span>
                        </div>
                        <span class="nav-label material-symbols-rounded">keyboard_arrow_down</span>
                    </button>

                    <div id="reportsDropdown" class="ml-3 mt-2 space-y-1 border-l-4 border-gray-600 hidden">
                        <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white" title="Activity Log">
                            <span class="nav-label material-symbols-rounded">history_toggle_off</span>
                            <span class="nav-label">Activty Log</span>
                        </a>
                        <a href="{{ route('dashboards.owner.technical_request') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white" title="Technical Support">
                            <span class="nav-label material-symbols-rounded">support_agent</span>
                            <span class="nav-label">Technical Support</span>
                        </a>
                    </div>
                </div>
                <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white" title="Store">
                    <span class="material-symbols-rounded">local_mall</span>
                    <span class="nav-label">Store</span>
                </a>
            </nav>
        </div>
    </aside>

    <main id="mainContent" class="ml-64 flex-1 p-3 transition-all duration-300">
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
        const reportsToggle = document.getElementById('reportsToggle');
        const reportsDropdown = document.getElementById('reportsDropdown');

        reportsToggle.addEventListener('click', () => {
            reportsDropdown.classList.toggle('hidden');
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

        // document.onkeydown = function(e) {
        // if(event.keyCode == 123) {
        // return false;
        // }
        // if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)){
        // return false;
        // }
        // if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)){
        // return false;
        // }
        // if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)){
        // return false;
        // }
        // }
    </script>

</body>

</html>