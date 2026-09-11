import Chart from 'chart.js/auto';
import { createIcons, icons } from 'lucide';
import svgMap from 'svgmap';
import 'svgmap/dist/svg-map.css';

createIcons({ icons });

const dashboardData = document.getElementById('analytics-dashboard-data');

if (dashboardData) {
    const { trend, countries } = JSON.parse(dashboardData.textContent);
    const chart = document.getElementById('requests-chart');

    if (chart) {
        new Chart(chart, {
            type: 'line',
            data: {
                labels: trend.map((point) => point.label),
                datasets: [{
                    data: trend.map((point) => point.count),
                    borderColor: '#e4e4e7',
                    backgroundColor: 'rgba(161, 161, 170, 0.12)',
                    borderWidth: 2,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    pointBackgroundColor: '#fafafa',
                    tension: 0.35,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        displayColors: false,
                        callbacks: {
                            title: (items) => trend[items[0].dataIndex].date,
                            label: (context) => `${context.parsed.y.toLocaleString()} requests`,
                        },
                    },
                },
                scales: {
                    x: {
                        border: {
                            display: false,
                        },
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#71717a',
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 8,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        border: {
                            display: false,
                        },
                        grid: {
                            color: 'rgba(63, 63, 70, 0.45)',
                        },
                        ticks: {
                            color: '#71717a',
                            precision: 0,
                        },
                    },
                },
            },
        });
    }

    if (document.getElementById('country-map')) {
        new svgMap({
            targetElementID: 'country-map',
            colorMin: '#52525b',
            colorMax: '#fafafa',
            colorNoData: '#27272a',
            flagType: 'emoji',
            mouseWheelZoomEnabled: false,
            showZoomReset: true,
            data: {
                data: {
                    requests: {
                        name: 'Requests',
                        format: '{0}',
                        thousandSeparator: ',',
                    },
                },
                applyData: 'requests',
                values: countries,
            },
        });
    }
}
