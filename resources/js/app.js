import './bootstrap';
import '../css/app.css';
import 'flowbite';
import { Chart, registerables } from 'chart.js';
import zoomPlugin from 'chartjs-plugin-zoom';

Chart.register(...registerables, zoomPlugin);


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
                backgroundColor: "rgba(247, 233, 233, 1)",
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
                    pan: { enabled: true, mode: "xy" },
                    zoom: { enabled: true, mode: "x" }
                }
            },
            scales: { y: { beginAtZero: true, display: true } }
        },
    });

    const chartEle = document.getElementById("productChart");
    const ctz = chartEle.querySelector("canvas").getContext("2d");

    const categories = JSON.parse(chartEle.dataset.categories || "[]");
    const products = JSON.parse(chartEle.dataset.products || "[]");
    const productsPrev = JSON.parse(chartEle.dataset.productsPrev || "[]");
    const year = JSON.parse(chartEle.dataset.year || "[]");

    new Chart(ctz, {
        type: 'bar',
        data: {
            labels: categories,
            datasets: [
                {
                    label: year[0] || "",
                    data: products,
                    backgroundColor: 'rgba(187, 19, 19, 1)',
                    borderRadius: Number.MAX_VALUE,
                    fill: true
                },
                {
                    label: year[1] || "",
                    data: productsPrev,
                    backgroundColor: 'rgba(234, 132, 109, 1)',
                    borderRadius: Number.MAX_VALUE,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                zoom: {
                    pan: { enabled: true, mode: 'xy' },
                    zoom: { mode: 'x' }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, display: false }
            }
        }
    });
    
});

