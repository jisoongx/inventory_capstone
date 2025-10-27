import './bootstrap';
import '../css/app.css';
import 'flowbite';
import { Chart, registerables } from 'chart.js';
import zoomPlugin from 'chartjs-plugin-zoom';

import 'datatables.net-dt/css/dataTables.dataTables.css';
import DataTable from 'datatables.net-dt';

import JsBarcode from "jsbarcode";



// import Alpine from 'alpinejs'

// window.Alpine = Alpine
// Alpine.start()

Chart.register(...registerables, zoomPlugin);

//sa charts ni

function initProfitChart() 
{
    const profitChart = document.getElementById("profitChart");
    if (!profitChart) return;

    const canvas = profitChart.querySelector("canvas");
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    const profits = JSON.parse(profitChart.dataset.profits || "[]");
    const months = JSON.parse(profitChart.dataset.months || "[]");

    profitChart.style.height = "24rem"; 
    profitChart.style.width = "100%";

    if (!canvas.chartInstance) {
        canvas.chartInstance = new Chart(ctx, {
            type: "line",
            data: {
                labels: months,
                datasets: [{
                    label: "Profit",
                    data: profits,
                    borderColor: "rgba(190, 21, 21, 1)",
                    backgroundColor: "rgba(254, 242, 242, 1)",
                    tension: 0,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: "rgba(190, 21, 21, 1)",
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 200 },
                plugins: {
                    legend: { display: false },
                    zoom: {
                        zoom: { wheel: { enabled: true }, pinch: { enabled: true }, mode: "x" },
                        pan: { enabled: true, mode: "x" }
                    }
                },
                scales: { y: { beginAtZero: true }, x: { display: true } }
            },
        });
    } else {
        
        canvas.chartInstance.data.labels = months;
        canvas.chartInstance.data.datasets[0].data = profits;
        canvas.chartInstance.resize(); 
        canvas.chartInstance.update();
    }

    window.profitChartInstance = canvas.chartInstance;
}


function initSalesVSLossChart() 
{
    const salesVSlossChart = document.getElementById("salesVSlossChart");
    if (!salesVSlossChart) return; 

    const canvas = salesVSlossChart.querySelector("canvas");
    if (!canvas) return; 

    const cty = canvas.getContext("2d");

    const sales = JSON.parse(salesVSlossChart.dataset.sales || "[]");
    const losses = JSON.parse(salesVSlossChart.dataset.losses || "[]");

    const latestSales = sales[sales.length - 1] || 0;
    const latestLoss = losses[losses.length - 1] || 0;

    const greenGradient = cty.createLinearGradient(0, 0, 400, 0);
    greenGradient.addColorStop(0, "#049942ff");
    greenGradient.addColorStop(0, "#05b54eff");
    greenGradient.addColorStop(1, "#B2FF59");

    const redGradient = cty.createLinearGradient(0, 0, 400, 0);
    redGradient.addColorStop(0, "#f00232ff");
    redGradient.addColorStop(0, "#f02951ff");
    redGradient.addColorStop(1, "#f9a29aff");

    if (!canvas.chartInstance) {
        
        canvas.chartInstance = new Chart(cty, {
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
    } else {

        canvas.chartInstance.data.datasets[0].data = [latestSales];
        canvas.chartInstance.data.datasets[1].data = [latestLoss];
        
        canvas.chartInstance.resize(); 
        canvas.chartInstance.update();
    }

    window.salesVSlossChartInstance = canvas.chartInstance;
}


function initProductChart() 
{
    const productChart = document.getElementById("productChart");
    if (!productChart) return;

    const canvas = productChart.querySelector("canvas");
    if (!canvas) return;

    const ctz = canvas.getContext("2d");

    const categories = JSON.parse(productChart.dataset.categories || "[]");
    const products = JSON.parse(productChart.dataset.products || "[]");
    const productsPrev = JSON.parse(productChart.dataset.productsPrev || "[]");
    const productsAve = JSON.parse(productChart.dataset.productsAve || "[]");
    const year = JSON.parse(productChart.dataset.year || "[]");

    if (!canvas.chartInstance) {
        
        canvas.chartInstance = new Chart(ctz, {
            data: {
                labels: categories,
                datasets: [
                {
                    type: 'line',
                    label: year[0] || "Current Year",
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
                                return label.slice(0, 10);
                            }
                        } 
                    },
                    y: { beginAtZero: true, display: false }
                }
            }
        });
    } else {
        
        canvas.chartInstance.data.labels = categories;

        canvas.chartInstance.data.datasets[0].data = products;
        canvas.chartInstance.data.datasets[0].label = year[0] || "Current Year";
        
        if (year.length > 1 && canvas.chartInstance.data.datasets.length > 2) {
            canvas.chartInstance.data.datasets[1].data = productsPrev;
            canvas.chartInstance.data.datasets[1].label = year[1];
        }
        
        const aveIndex = canvas.chartInstance.data.datasets.length - 1;
        canvas.chartInstance.data.datasets[aveIndex].data = productsAve;

        canvas.chartInstance.resize(); 
        canvas.chartInstance.update();
    }
    
    window.productChartInstance = canvas.chartInstance;
}




function updateDateTime() {
    const clock = document.getElementById('clock');
    const dateEl = document.getElementById('date');
    if (!clock || !dateEl) return;

    const now = new Date();

    clock.textContent = now.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });

    dateEl.textContent = now.toLocaleDateString([], {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}





document.addEventListener("livewire:init", () => {
    
    initProfitChart();
    initSalesVSLossChart();
    initProductChart();

    // para real time date ni diri
    updateDateTime();
    setInterval(updateDateTime, 1000);

    Livewire.hook("morph.updating", ({ component }) => {
        
        const profitChartContainer = document.getElementById("profitChart");
        if (profitChartContainer && profitChartContainer.__x) {
            profitChartContainer.__x.set("updating", true);
        }
        
        const salesChartContainer = document.getElementById("salesVSlossChart");
        if (salesChartContainer && salesChartContainer.__x) {
            salesChartContainer.__x.set("updating", true);
        }

        const productsChartContainer = document.getElementById("productChart");
        if (productsChartContainer && productsChartContainer.__x) {
            productsChartContainer.__x.set("updating", true);
        }
    });

    Livewire.hook("morph.updated", () => {
        
        const profitChartContainer = document.getElementById("profitChart");
        if (profitChartContainer) {
            initProfitChart();
            if (profitChartContainer.__x) {
                profitChartContainer.__x.set("updating", false); 
            }
        }
        
        const salesChartContainer = document.getElementById("salesVSlossChart");
        if (salesChartContainer) {
            initSalesVSLossChart(); 
            if (salesChartContainer.__x) {
                salesChartContainer.__x.set("updating", false); 
            }
        }

        const productsChartContainer = document.getElementById("productChart");
        if (productsChartContainer) {
            initProductChart(); 
            if (productsChartContainer.__x) {
                productsChartContainer.__x.set("updating", false); 
            }
        }

    });
});












document.addEventListener("DOMContentLoaded", () => {

    document.querySelector('#zoomIn').addEventListener('click', (e) => {
        lineChart.zoom(1.2); 
    })

    document.querySelector('#zoomOut').addEventListener('click', (e) => {
        lineChart.zoom(0.8); 
    })

    document.querySelector('#zoomReset').addEventListener('click', (e) => {
        lineChart.resetZoom();
    })

    // const productChart = document.getElementById("productChart");
    // const ctz = productChart.querySelector("canvas").getContext("2d");

    // const categories = JSON.parse(productChart.dataset.categories || "[]");
    // const products = JSON.parse(productChart.dataset.products || "[]");
    // const productsPrev = JSON.parse(productChart.dataset.productsPrev || "[]");
    // const productsAve = JSON.parse(productChart.dataset.productsAve || "[]");
    // const year = JSON.parse(productChart.dataset.year || "[]");

    // new Chart(ctz, {
    //     data: {
    //         labels: categories,
    //         datasets: [
    //         {
    //             type: 'line',
    //             label: year[0] || "",
    //             data: products,
    //             borderColor: "rgba(190, 21, 21, 1)",
    //             backgroundColor: 'transparent',
    //             borderWidth: 2,
    //             pointRadius: 2,
    //         },
    //         ...(year.length > 1 ? [{
    //             type: 'line',
    //             label: year[1],
    //             data: productsPrev,
    //             borderColor: 'rgba(67, 102, 209, 1)',
    //             pointBackgroundColor: 'rgba(67, 102, 209, 1)', 
    //             backgroundColor: 'transparent',
    //             borderDash: [7],
    //             borderWidth: 2,
    //             pointRadius: 2,
    //         }] : []), 
    //         {
    //             type: 'bar',
    //             label: 'Average',
    //             data: productsAve,
    //             borderColor: "rgba(250, 196, 47, 1)",
    //             backgroundColor: 'rgba(243, 236, 217, 1)',
    //             borderWidth: 1,
    //         }
    //         ]
    //     },
    //     options: {
    //         responsive: true,
    //         maintainAspectRatio: false,
    //         plugins: {
    //         legend: { display: false }
    //         },
    //         scales: {
    //             x: { 
    //                 grid: { display: true }, 
    //                 ticks: { 
    //                     display: true, 
    //                     minRotation: 90,
    //                     font: { family: "Poppins, sans-serif", size: 10 },
    //                     callback: function(value) {
    //                         const label = this.getLabelForValue(value);
    //                         return label.slice(0, 8);
    //                     }
    //                 } 
    //             },
    //             y: { beginAtZero: true, display: false }
    //         }
    //     }
    // });


        

    // const chartSaleVsLoss = document.getElementById("salesVSlossChart");
    // const cty = chartSaleVsLoss.querySelector("canvas").getContext("2d");

    // const sales = JSON.parse(chartSaleVsLoss.dataset.sales || "[]");
    // const losses = JSON.parse(chartSaleVsLoss.dataset.losses || "[]");

    // const latestSales = sales[sales.length - 1] || 0;
    // const latestLoss = losses[losses.length - 1] || 0;
    // // const netSales = Math.max(latestSales - latestLoss, 0); 

    // const greenGradient = cty.createLinearGradient(0, 0, 400, 0);
    // greenGradient.addColorStop(0, "#049942ff");
    // greenGradient.addColorStop(0, "#05b54eff");
    // greenGradient.addColorStop(1, "#B2FF59");

    // const redGradient = cty.createLinearGradient(0, 0, 400, 0);
    // redGradient.addColorStop(0, "#f00232ff");
    // redGradient.addColorStop(0, "#f02951ff");
    // redGradient.addColorStop(1, "#f9a29aff");

    // new Chart(cty, {
    //     type: "bar",
    //     data: {
    //         labels: [""],
    //         datasets: [
    //         {
    //             label: "Sales",
    //             data: [latestSales],
    //             backgroundColor: greenGradient,
    //             borderRadius: 14,
    //         },
    //         {
    //             label: "Loss",
    //             data: [latestLoss],
    //             backgroundColor: redGradient,
    //             borderRadius: 14,
    //         },
    //         ],
    //     },
    //     options: {
    //         indexAxis: "y", 
    //         responsive: true,
    //         maintainAspectRatio: false,
    //         plugins: {
    //             legend: { display: false },
    //         },
    //         scales: {
    //             x: {
    //                 grid: { display: false },
    //                 ticks: {
    //                     color: "#666",
    //                     font: { family: "Poppins, sans-serif", size: 8 }
    //                 },
    //             },
    //             y: {
    //                 grid: { display: false },
    //                 ticks: { display: false },
    //             },
    //         },
    //         animation: {
    //         duration: 1500,
    //         easing: "easeOutQuart",
    //         },
    //     },
    // });

    
});




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


//sa generate barcode ni
window.JsBarcode = JsBarcode;
window.generateBarcode = function() {
    const randomBarcode = Math.floor(100000000000 + Math.random() * 900000000000).toString(); // 12-digit barcode
    JsBarcode("#generatedBarcode", randomBarcode, {
        format: "CODE128",      // You can change to EAN13, CODE39, etc.
        displayValue: true,     // Shows the numbers below
        fontSize: 18,
        lineColor: "#000",
        width: 2,
        height: 80,
    });
    document.getElementById("generatedBarcodeInput").value = randomBarcode;
};

document.getElementById("generateNewBarcodeBtn")?.addEventListener("click", () => {
    generateBarcode();
});


