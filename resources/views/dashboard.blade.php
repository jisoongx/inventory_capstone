<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
<body class="bg-gradient-to-b from-white to-blue-100 min-h-screen flex p-5">

    <aside id="sidebar" class="w-64 transition-all duration-300 bg-black text-white h-auto p-4 flex flex-col justify-between rounded-lg">
        <div>
            <div class="flex items-center justify-between mb-6 mt-3">
                <img src="{{ asset('assets/logo.jpg') }}" class="w-8 h-8 rounded ml-2 mr-2">
            </div>

            <nav class="space-y-2">
                <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                    <span class="material-symbols-rounded">dashboard</span>
                    <span class="nav-label">Dashboard</span>
                </a>
                <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                    <span class="material-symbols-rounded">shopping_cart</span>
                    <span class="nav-label">Inventory</span>
                </a>
                <div class="group">
                    <button id="reportsToggle" class="w-full flex items-center justify-between p-3 rounded hover:bg-red-600 hover:text-white">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-rounded">stacked_line_chart</span>
                            <span class="nav-label">Reports</span>
                        </div>
                        <span class="nav-label material-symbols-rounded">keyboard_arrow_down</span>
                    </button>
          
                    <div id="reportsDropdown" class="hidden ml-3 mt-2 space-y-1 border-l-4 border-gray-600">
                        <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                            <span class="report-label hidden material-symbols-rounded">history_toggle_off</span>
                            <span class="report-label hidden text-sm">Activty Log</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                            <span class="report-label hidden material-symbols-rounded">support_agent</span>
                            <span class="report-label hidden text-sm">Technical Support</span>
                        </a>
                    </div>
                </div>
                <a href="#" class="flex items-center gap-3 p-3 rounded hover:bg-red-600 hover:text-white">
                    <span class="material-symbols-rounded">local_mall</span>
                    <span class="nav-label">Store</span>
                </a>
            </nav>
        </div>
    </aside>

    <main class="flex-1 duration-300">
        <!-- Top Navbar -->
        <div class="flex justify-end items-center border-b-2 border-gray-300 pb-4 px-4">
            <span class="material-symbols-rounded mr-5" style="font-variation-settings: 'FILL' 1;">
                notifications
            </span>
            <img src="{{ asset('assets/logo.jpg') }}" class="w-9 h-9 rounded-full">
        </div>

        <!-- Main Section -->
        <section class="flex p-4 gap-5">
            <!-- Left Content -->
            <div class="flex-1">
                <span class="text-sm text-gray-500">Date</span>
                <h1 class="text-2xl font-bold mb-4">Welcome, {{ $owner_name }}!</h1>

                <!-- Summary Cards -->
                <div class="flex gap-4 mb-5">
                    <div class="bg-white border-t-4 border-red-800 p-4 shadow-lg rounded w-64 text-center">
                        <p class="text-red-600 text-xl font-bold">₱14,500</p>
                        <span class="text-gray-600 text-xs font-bold">Daily Sales</span>
                    </div>
                    <div class="bg-white border-t-4 border-green-800 p-4 shadow-lg rounded w-32 text-center">
                        <p class="text-green-600 text-xl font-bold">₱20,000</p>
                        <span class="text-gray-600 text-xs">Weekly Sales</span>
                    </div>
                    <div class="bg-white border-t-4 border-blue-800 p-4 shadow-lg rounded w-32 text-center">
                        <p class="text-blue-600 text-xl font-bold">₱154,000</p>
                        <span class="text-gray-600 text-xs">Monthly Sales</span>
                    </div>
                </div>

                <div class="w-full bg-white shadow-lg rounded p-4 mb-3">
                    <h3 class="text-sm font-semibold text-slate-800 mb-5">Comparative Analysis</h3>
                    
                    <div class="w-full overflow-x-auto">
                        <table class="w-fit text-sm text-left text-slate-700 border-collapse">
                            <thead>
                                <tr class="bg-red-50 text-xs text-slate-500 uppercase">
                                    <th class="px-4 py-3 border-b border-slate-300">Metric</th>
                                    @for ($i = 0; $i < count($months); $i++)
                                        <th class="px-4 py-3 border-b border-slate-300">{{ $months[$i] }}</th>
                                        @if ($i < count($months) - 1)
                                            <th class="px-4 py-3 border-b border-slate-300">{{ $months[$i] }}-{{ $months[$i + 1] }} (%)</th>
                                        @endif
                                    @endfor
                                </tr>
                            </thead>

                            <tbody>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-4 border-b border-slate-200 text-xs font-semibold text-slate-800">In-Store Expenses</td>
                                    @for ($i = 0; $i < count($expenses); $i++)
                                        <td class="px-4 py-4 border-b border-slate-200 text-xs text-slate-500">
                                            ₱{{ number_format($expenses[$i], 2) }}
                                        </td>

                                        @if ($i < count($expenses) - 1)
                                            @php
                                                $diff = $expenses[$i + 1] - $expenses[$i];
                                                $percent = $expenses[$i] == 0 ? 0 : ($diff / $expenses[$i]) * 100;
                                            @endphp
                                            <td class="px-4 py-4 border-b border-slate-200 text-xs font-bold {{ $percent < 1 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ number_format($percent, 1) }}%
                                            </td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-4 border-b border-slate-200 text-xs font-semibold text-slate-800">Total Loss</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-4 border-b border-slate-200 text-xs font-semibold text-slate-800">Total Sales</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-4 border-b border-slate-200 text-xs font-semibold text-slate-800">Profit</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <div class="w-full max-w-md bg-white rounded p-5 shadow flex flex-col items-center">
                <p class="w-full text-left text-black font-bold text-xs border-b border-gray-200 pb-5">Monthly Profit</p>

                <div class="flex items-center w-full pt-4">
                    <div class="flex-2 mr-5">
                        <span class="text-xl font-bold text-black block mb-1">November</span>
                        <p class="text-xs text-black mb-3">Wed, 14</p>
                    </div>
                    <div class="flex-1">
                        <span class="text-xl font-bold text-black block mb-1">₱15,400</span>
                        <p class="text-xs text-black mb-3">Current Profit</p>
                    </div>
                </div>

                <div class="w-full mt-5 overflow-x-auto">
                    <div class="w-auto">
                        <canvas id="profitChart" class="w-full" height="300"></canvas>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        const reportsToggle = document.getElementById('reportsToggle');
        const reportsDropdown = document.getElementById('reportsDropdown');
        const labels = document.querySelectorAll('.report-label');

        reportsToggle.addEventListener('click', () => {
            reportsDropdown.classList.toggle('hidden');
            labels.forEach(label => label.classList.remove('hidden'));
        });

        
    </script>

    <script>
        const ctx = document.getElementById('profitChart').getContext('2d');

        const profitChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($months) !!},
                datasets: [{
                    label: 'Profit',
                    data: {!! json_encode($profits) !!},
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.2,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: 'rgb(255, 99, 132)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: 
                    { 
                        beginAtZero: true,
                        display: false,
                    },
                }
            }
        });
    </script>

</body>
</html>
