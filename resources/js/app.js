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
    const chartEl = document.getElementById("profitChart");
    const ctx = chartEl.querySelector("canvas").getContext("2d");

    const profits = JSON.parse(chartEl.dataset.profits || "[]");
    const months = JSON.parse(chartEl.dataset.months || "[]");

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



    const chartEle = document.getElementById("productChart");
    const ctz = chartEle.querySelector("canvas").getContext("2d");

    const categories = JSON.parse(chartEle.dataset.categories || "[]");
    const products = JSON.parse(chartEle.dataset.products || "[]");
    const productsPrev = JSON.parse(chartEle.dataset.productsPrev || "[]");
    const year = JSON.parse(chartEle.dataset.year || "[]");

    new Chart(ctz, {
        type: 'line',
        data: {
            labels: categories,
            datasets: [{
                    label: year[0] || "",
                    data: products,
                    borderColor: "rgba(190, 21, 21, 1)",
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 1,
                },
                ...(year.length > 1 ? [{
                    label: year[1],
                    data: productsPrev,
                    borderColor: 'rgba(67, 102, 209, 1)',
                    backgroundColor: 'transparent',
                    borderDash: [7],
                    borderWidth: 2,
                    pointRadius: 1,
                }] : [])
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: true,
                }
            },
            scales: {
                x: { grid: { display: true } },
                y: { beginAtZero: true, display: false }
            }
        }
    });

    const chartSaleVsLoss = document.getElementById("salesVSlossChart");
    const cty = chartSaleVsLoss.querySelector("canvas").getContext("2d");

    const sales = JSON.parse(chartSaleVsLoss.dataset.sales || "[]");
    const losses = JSON.parse(chartSaleVsLoss.dataset.losses || "[]");

    new Chart(cty, {
        type: "doughnut",
        data: {
            labels: ["Sales", "Loss"],
            datasets: [{
            data: [sales[sales.length - 1], losses[losses.length - 1]],
            backgroundColor: ["#4CAF50", "#F44336"]
            }]
        },
        options: {
            rotation: -90,        
            circumference: 180,   
            plugins: {
            legend: {
                display: true,
            }
            }
        }
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


