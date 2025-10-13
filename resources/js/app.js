import './bootstrap';
import '../css/app.css';
import 'flowbite';
import { Chart, registerables } from 'chart.js';
import zoomPlugin from 'chartjs-plugin-zoom';

import 'datatables.net-dt/css/dataTables.dataTables.css';
import DataTable from 'datatables.net-dt';

// import Alpine from 'alpinejs'

// window.Alpine = Alpine
// Alpine.start()

Chart.register(...registerables, zoomPlugin);

//sa charts ni
document.addEventListener("DOMContentLoaded", () => {

    const profitChart = document.getElementById("profitChart");
    const ctx = profitChart.querySelector("canvas").getContext("2d");

    const profits = JSON.parse(profitChart.dataset.profits || "[]");
    const months = JSON.parse(profitChart.dataset.months || "[]");

    new Chart(ctx, {
        type: "line",
        data: {
            labels: months,
            datasets: [{
                label: "Profit",
                data: profits,
                borderColor: "rgba(190, 21, 21, 1)",
                backgroundColor: "rgba(254, 242, 242, 1)",
                tension: 0.2,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: "rgba(190, 21, 21, 1)",
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                zoom: {
                    zoom: { enabled: true, mode: "x" }
                }
            },
            scales: { y: { beginAtZero: true, display: true } }
        },
    });

    document.querySelector('#zoomIn').addEventListener('click', (e) => {
        lineChart.zoom(1.2); 
    })

    document.querySelector('#zoomOut').addEventListener('click', (e) => {
        lineChart.zoom(0.8); 
    })

    document.querySelector('#zoomReset').addEventListener('click', (e) => {
        lineChart.resetZoom();
    })



    const productChart = document.getElementById("productChart");
    const ctz = productChart.querySelector("canvas").getContext("2d");

    const categories = JSON.parse(productChart.dataset.categories || "[]");
    const products = JSON.parse(productChart.dataset.products || "[]");
    const productsPrev = JSON.parse(productChart.dataset.productsPrev || "[]");
    const productsAve = JSON.parse(productChart.dataset.productsAve || "[]");
    const year = JSON.parse(productChart.dataset.year || "[]");

    new Chart(ctz, {
        data: {
            labels: categories,
            datasets: [
            {
                type: 'line',
                label: year[0] || "",
                data: products,
                borderColor: "rgba(190, 21, 21, 1)",
                backgroundColor: 'transparent',
                borderWidth: 2,
                pointRadius: 2,
            },
            ...(year.length > 1 ? [{
                type: 'line',
                label: year[1],
                data: productsPrev,
                borderColor: 'rgba(67, 102, 209, 1)',
                pointBackgroundColor: 'rgba(67, 102, 209, 1)', 
                backgroundColor: 'transparent',
                borderDash: [7],
                borderWidth: 2,
                pointRadius: 2,
            }] : []), 
            {
                type: 'bar',
                label: 'Average',
                data: productsAve,
                borderColor: "rgba(250, 196, 47, 1)",
                backgroundColor: 'rgba(243, 236, 217, 1)',
                borderWidth: 1,
            }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
            legend: { display: false }
            },
            scales: {
                x: { 
                    grid: { display: true }, 
                    ticks: { 
                        display: true, 
                        minRotation: 90,
                        font: { family: "Poppins, sans-serif", size: 10 },
                        callback: function(value) {
                            const label = this.getLabelForValue(value);
                            return label.slice(0, 8);
                        }
                    } 
                },
                y: { beginAtZero: true, display: false }
            }
        }
    });


        

    const chartSaleVsLoss = document.getElementById("salesVSlossChart");
    const cty = chartSaleVsLoss.querySelector("canvas").getContext("2d");

    const sales = JSON.parse(chartSaleVsLoss.dataset.sales || "[]");
    const losses = JSON.parse(chartSaleVsLoss.dataset.losses || "[]");

    const latestSales = sales[sales.length - 1] || 0;
    const latestLoss = losses[losses.length - 1] || 0;
    // const netSales = Math.max(latestSales - latestLoss, 0); 

    const greenGradient = cty.createLinearGradient(0, 0, 400, 0);
    greenGradient.addColorStop(0, "#049942ff");
    greenGradient.addColorStop(0, "#05b54eff");
    greenGradient.addColorStop(1, "#B2FF59");

    const redGradient = cty.createLinearGradient(0, 0, 400, 0);
    redGradient.addColorStop(0, "#f00232ff");
    redGradient.addColorStop(0, "#f02951ff");
    redGradient.addColorStop(1, "#f9a29aff");

    new Chart(cty, {
        type: "bar",
        data: {
            labels: [""],
            datasets: [
            {
                label: "Sales",
                data: [latestSales],
                backgroundColor: greenGradient,
                borderRadius: 14,
            },
            {
                label: "Loss",
                data: [latestLoss],
                backgroundColor: redGradient,
                borderRadius: 14,
            },
            ],
        },
        options: {
            indexAxis: "y", 
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: "#666",
                        font: { family: "Poppins, sans-serif", size: 8 }
                    },
                },
                y: {
                    grid: { display: false },
                    ticks: { display: false },
                },
            },
            animation: {
            duration: 1500,
            easing: "easeOutQuart",
            },
        },
    });

    
});


// const AnalysisTable = new DataTable('#productAnalysisTable', {
//     pageLength: 5,
//     ordering: true,
//     searching: true,
//     order: [[0, 'desc']],
//     lengthChange: false,
//     dom: 't<"flex justify-between items-center mt-2 text-xs"ip>'
// });

// document.querySelector('#customSearchBox').addEventListener('keyup', function() {
//     AnalysisTable.search(this.value).draw();
// });



//sa monthly nga table
const table = new DataTable('#expensesTable', {
    pageLength: 5,
    ordering: true,
    searching: true,
    order: [[2, 'desc']],
    lengthChange: false,
    dom: `<"flex justify-between items-center mb-2 text-xs"f>t<"flex justify-between items-center mt-2 text-xs"ip>`

});




//sa monthly nga buttons
document.querySelector('#expensesTable').addEventListener('click', (e) => {
    if (e.target.closest('.editBtn')) {
        const row = e.target.closest('tr');

        row.querySelectorAll('.view').forEach(el => el.classList.add('hidden'));
        row.querySelectorAll('.edit').forEach(el => el.classList.remove('hidden'));

        row.querySelector('.editBtn').classList.add('hidden');
        row.querySelector('.saveBtn').classList.remove('hidden');
        row.querySelector('.cancelBtn').classList.remove('hidden');
    }

    if (e.target.closest('.cancelBtn')) {
        const row = e.target.closest('tr');

        row.querySelectorAll('.edit').forEach(el => el.classList.add('hidden'));
        row.querySelectorAll('.view').forEach(el => el.classList.remove('hidden'));

        row.querySelector('.saveBtn').classList.add('hidden');
        row.querySelector('.cancelBtn').classList.add('hidden');
        row.querySelector('.editBtn').classList.remove('hidden');
    }
});

