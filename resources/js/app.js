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

    profitChart.style.height = "23rem"; 
    profitChart.style.width = "100%";

    // Custom plugin for permanent labels on data points
    // const profitLabelsPlugin = {
    //     id: 'profitLabels',
    //     afterDatasetsDraw: function(chart) {
    //         const ctx = chart.ctx;
    //         chart.data.datasets.forEach((dataset, i) => {
    //             const meta = chart.getDatasetMeta(i);
                
    //             meta.data.forEach((point, index) => {
    //                 const value = dataset.data[index];
                    
    //                 ctx.save();
    //                 ctx.font = 'semibold 5px Poppins, sans-serif';
    //                 ctx.textBaseline = 'bottom';
                    
    //                 const text = '₱ ' + value.toLocaleString('en-US', {
    //                     minimumFractionDigits: 2,
    //                     maximumFractionDigits: 2
    //                 });
                    
    //                 // Measure text width
    //                 const textWidth = ctx.measureText(text).width;
    //                 const padding = 6;
    //                 const boxWidth = textWidth + (padding * 2);
    //                 const boxHeight = 18;
                    
    //                 // Position above the point
    //                 const x = point.x - boxWidth / 2;
    //                 const y = point.y - boxHeight - 8;
                    
    //                 // Red background matching the line color
    //                 ctx.fillStyle = 'rgba(190, 21, 21, 0.95)';
    //                 ctx.shadowColor = 'rgba(190, 21, 21, 0.3)';
    //                 ctx.shadowBlur = 6;
    //                 ctx.shadowOffsetX = 0;
    //                 ctx.shadowOffsetY = 2;
                    
    //                 // Draw rounded rectangle background
    //                 ctx.beginPath();
    //                 ctx.roundRect(x, y, boxWidth, boxHeight, 5);
    //                 ctx.fill();
                    
    //                 // Reset shadow for text
    //                 ctx.shadowBlur = 0;
    //                 ctx.shadowOffsetX = 0;
    //                 ctx.shadowOffsetY = 0;
                    
    //                 // Draw text in white
    //                 ctx.fillStyle = '#ffffff';
    //                 ctx.textAlign = 'center';
    //                 ctx.fillText(text, point.x, y + boxHeight - 4);
                    
    //                 ctx.restore();
    //             });
    //         });
    //     }
    // };

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
                layout: {
                    padding: {
                        top: 35,
                        right: 10,
                        bottom: 10,
                        left: 10
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#444',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        titleFont: {
                            family: 'Poppins, sans-serif',
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: 'Poppins, sans-serif',
                            size: 12
                        },
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                return 'Profit: ' + context.parsed.y.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    },
                    zoom: {
                        zoom: { wheel: { enabled: true }, pinch: { enabled: true }, mode: "x" },
                        pan: { enabled: true, mode: "x" }
                    }
                },
                scales: { 
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            font: { family: "Poppins, sans-serif" }
                        }
                    }, 
                    x: { 
                        display: true,
                        ticks: {
                            font: { family: "Poppins, sans-serif" }
                        }
                    } 
                }
            },
            // plugins: [profitLabelsPlugin]
        });
    } else {
        
        canvas.chartInstance.data.labels = months;
        canvas.chartInstance.data.datasets[0].data = profits;
        canvas.chartInstance.resize(); 
        canvas.chartInstance.update();
    }

    const chart = canvas.chartInstance;
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

    // const valuePlugin = {
    //     id: 'valueLabels',
    //     afterDatasetsDraw: function(chart) {
    //         const ctx = chart.ctx;
    //         chart.data.datasets.forEach((dataset, i) => {
    //             const meta = chart.getDatasetMeta(i);
    //             const isSales = dataset.label === 'Sales';
                
    //             meta.data.forEach((bar, index) => {
    //                 const value = dataset.data[index];
                    
    //                 ctx.save();
    //                 ctx.font = '11px Poppins, sans-serif';
    //                 ctx.textBaseline = 'middle';
                    
    //                 const text = '₱ ' + value.toLocaleString('en-US', {
    //                     minimumFractionDigits: 2,
    //                     maximumFractionDigits: 2
    //                 });
                    
    //                 // Measure text width
    //                 const textWidth = ctx.measureText(text).width;
    //                 const padding = 4;
    //                 const boxWidth = textWidth + (padding * 2);
    //                 const boxHeight = 20;
                    
    //                 // Position at the start of the bar
    //                 const x = bar.x - boxWidth - 8;
    //                 const y = bar.y;
                    
    //                 // Different colors based on Sales or Loss
    //                 if (isSales) {
    //                     // Green background for Sales
    //                     ctx.fillStyle = 'transparent';
    //                     ctx.shadowColor = 'rgba(5, 181, 78, 0.3)';
    //                 } else {
    //                     // Red background for Loss
    //                     ctx.fillStyle = 'transparent';
    //                     ctx.shadowColor = 'rgba(240, 41, 81, 0.3)';
    //                 }
                    
    //                 // Add subtle shadow
    //                 ctx.shadowBlur = 8;
    //                 ctx.shadowOffsetX = 0;
    //                 ctx.shadowOffsetY = 2;
                    
    //                 // Draw rounded rectangle background
    //                 ctx.beginPath();
    //                 ctx.roundRect(x, y - boxHeight/2, boxWidth, boxHeight, 6);
    //                 ctx.fill();
                    
    //                 // Reset shadow for text
    //                 ctx.shadowBlur = 0;
    //                 ctx.shadowOffsetX = 0;
    //                 ctx.shadowOffsetY = 0;
                    
    //                 // Draw text in white
    //                 ctx.fillStyle = '#ffffff';
    //                 ctx.textAlign = 'center';
    //                 ctx.fillText(text, x + boxWidth/2, y);
                    
    //                 ctx.restore();
    //             });
    //         });
    //     }
    // };

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
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#444',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        titleFont: {
                            family: 'Poppins, sans-serif',
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: 'Poppins, sans-serif',
                            size: 12
                        },
                        callbacks: {
                            title: function(context) {
                                return context[0].dataset.label;
                            },
                            label: function(context) {
                                let label = context.parsed.x || 0;
                                return 'Amount: ' + label.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
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
            // plugins: [valuePlugin]
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

    // document.querySelector('#zoomIn').addEventListener('click', (e) => {
    //     lineChart.zoom(1.2); 
    // })

    // document.querySelector('#zoomOut').addEventListener('click', (e) => {
    //     lineChart.zoom(0.8); 
    // })

    // document.querySelector('#zoomReset').addEventListener('click', (e) => {
    //     lineChart.resetZoom();
    // })

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


