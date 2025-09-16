<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title></title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- <link rel="stylesheet" href="/build/assets/dataTables.dataTables.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"> -->
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
                <a href="{{ route('inventory-owner') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white" title="Inventory">
                    <span class="material-symbols-rounded">shopping_cart</span>
                    <span class="nav-label">Inventory</span>
                </a>
                
                <div class="relative">
                    <a href="{{ route('reports') }}" class="flex items-center justify-between p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-rounded">stacked_line_chart</span>
                            <span class="nav-label">Reports</span>
                        </div>
                        <span id="reportsArrow" class="material-symbols-rounded cursor-pointer">keyboard_arrow_down</span>
                    </a>

                    <div id="reportsDropdown" class="ml-3 mt-1 space-y-1 border-l-4 border-gray-600 hidden">
                        <a href="{{ route('actLogs') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white">
                            <span class="nav-label material-symbols-rounded">history_toggle_off</span>
                            <span class="nav-label">Activity Log</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('store_transactions') }}" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 text-slate-100 hover:text-white" title="Store">
                    <span class="material-symbols-rounded">local_mall</span>
                    <span class="nav-label">Store</span>
                </a>
            </nav>
        </div>

            <a href="{{ route('dashboards.owner.technical_request') }}">
                @livewire('technical-request-menu')
            </a>
    </aside>


    <main id="mainContent" class="ml-64 flex-1 p-3 transition-all duration-300">
        <div class="flex justify-end items-center mr-5 border-b-2 border-gray-300 relative pb-2">
            <div class="relative space-x-5">

                <div class="flex items-center gap-4">
                    
                    @livewire('notifications')

                    <button id="userButton" class="focus:outline-none">
                        <img src="{{ asset('assets/user.png') }}" class="w-8 h-8 rounded-full" alt="User Icon">
                    </button>
                </div>


                <div id="dropdownMenu" class="absolute right-0 mt-2 w-32 bg-white border rounded shadow-lg hidden z-10">
                    <form method="GET" action="{{ route('owner.profile') }}">
                        {{-- Removed @csrf as it's not needed for GET requests --}}
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</button>
                    </form>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                    </form>
                </div>
            </div>
        </div>
        
        @livewireScripts
        @yield('content')
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const reportsArrow = document.getElementById('reportsArrow');
        const reportsDropdown = document.getElementById('reportsDropdown');

        reportsArrow.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation(); // stops the Reports link from being triggered
            reportsDropdown.classList.toggle('hidden');
        });


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

    <script>
    window.addEventListener('debug-console', event => {
        console.log(event.detail.message);
    });
</script>
@livewireScripts
</body>

</html>