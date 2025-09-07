@extends('dashboards.staff.staff') 

@section('content')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-2">

            <div class="">
                <span class="text-sm text-gray-500">{{ $dateDisplay->format('F j, Y') }}</span>
                <h1 class="text-2xl font-semibold mb-4">Welcome, {{ ucwords($staff_name) }}!</h1>

                <div class="flex gap-3 mb-3 w-full">
                    <div class="bg-white border-t-4 border-red-900 p-4 shadow-lg rounded flex-[2] text-center">
                        <p class="text-red-800 text-xl font-bold">₱{{ number_format($dailySales->dailySales, 2) }}</p>
                        <span class="text-gray-600 text-xs font-bold">Daily Sales</span>
                    </div>

                    <div class="bg-white border-t-4 border-red-700 p-4 shadow-lg rounded flex-[1] text-center" title="₱{{ number_format($weeklySales->weeklySales, 2) }}">
                        <p class="text-red-600 text-xl font-bold" title="₱{{ number_format($weeklySales->weeklySales, 2) }}">
                            ₱{{ $weeklySales->weeklySales >= 1000 ? number_format($weeklySales->weeklySales / 1000, 1) . 'k' : number_format($weeklySales->weeklySales, 2) }}
                        </p>
                        <span class="text-gray-600 text-xs">Last 7 days</span>
                    </div>

                    <div class="bg-white border-t-4 border-red-500 p-4 shadow-lg rounded flex-[1] text-center" title="₱{{ number_format($monthSales->monthSales, 2) }}">
                        <p class="text-red-400 text-xl font-bold" title="₱{{ number_format($monthSales->monthSales, 2) }}">
                            ₱{{ $monthSales->monthSales >= 1000 ? number_format($monthSales->monthSales / 1000, 1) . 'k' : number_format($monthSales->monthSales, 2) }}
                        </p>
                        <span class="text-gray-600 text-xs">This Month's Sales</span>
                    </div>
                </div>
            </div>

            <div class="bg-green-700 p-5 rounded-lg flex justify-between items-center">
                <div class="flex flex-col items-start space-y-1">
                    <span class="material-symbols-rounded-big text-white">event</span>
                    <span class="font-semibold text-white">{{ $dateDisplay->format('F j') }}</span>
                </div>

                <div class="text-right space-y-1">
                    <div class="font-semibold text-white text-xl">₱{{ number_format($dailySales->dailySales, 2) }}</div>
                    <div class="text-white text-sm">Current Sales</div>
                    <button class="bg-white text-green-700 px-4 py-2 rounded text-medium text-sm">View</button>
                </div>
            </div>




    <script>

        // function zoomIn() {
        //     profitChart.zoom(1.2); // 1.2 = 20% zoom in
        // }

        // // Zoom Out function
        // function zoomOut() {
        //     profitChart.zoom(0.8); // 0.8 = 20% zoom out
        // }

        // // Reset Zoom function
        // function resetZoom() {
        //     profitChart.resetZoom();
        // }
    </script>

@endsection