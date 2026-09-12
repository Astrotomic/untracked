import Chart from 'chart.js/auto';
import { createIcons, icons } from 'lucide';
import svgMap from 'svgmap';
import 'svgmap/style';

createIcons({ icons });

const websiteListData = document.getElementById('website-list-data');

if (websiteListData) {
    const websites = JSON.parse(websiteListData.textContent);

    document.querySelectorAll('[data-website-sparkline]').forEach((canvas) => {
        const stats = websites[canvas.dataset.websiteSparkline];

        if (! stats) {
            return;
        }

        const color = {
            up: '#34d399',
            down: '#f87171',
            flat: '#a1a1aa',
        }[stats.trend_direction];

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: stats.sparkline.map((_, index) => index),
                datasets: [{
                    data: stats.sparkline,
                    borderColor: color,
                    borderWidth: 2,
                    fill: false,
                    pointRadius: 0,
                    tension: 0.35,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        enabled: false,
                    },
                },
                scales: {
                    x: {
                        display: false,
                    },
                    y: {
                        display: false,
                        beginAtZero: true,
                    },
                },
            },
        });
    });
}

const dashboardData = document.getElementById('analytics-dashboard-data');

if (dashboardData) {
    const { trend, countries } = JSON.parse(dashboardData.textContent);
    const chart = document.getElementById('requests-chart');

    if (chart) {
        new Chart(chart, {
            type: 'line',
            data: {
                labels: trend.map((point) => point.label),
                datasets: [
                    {
                        label: 'Human',
                        data: trend.map((point) => point.human),
                        borderColor: '#e4e4e7',
                        backgroundColor: '#e4e4e7',
                        borderWidth: 2,
                        fill: false,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointBackgroundColor: '#fafafa',
                        tension: 0.35,
                    },
                    {
                        label: 'Bots',
                        data: trend.map((point) => point.bot),
                        borderColor: '#f59e0b',
                        backgroundColor: '#f59e0b',
                        borderWidth: 2,
                        fill: false,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointBackgroundColor: '#fbbf24',
                        tension: 0.35,
                    },
                ],
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
                        labels: {
                            color: '#a1a1aa',
                            usePointStyle: true,
                            pointStyle: 'line',
                        },
                    },
                    tooltip: {
                        callbacks: {
                            title: (items) => trend[items[0].dataIndex].date,
                            label: (context) => `${context.dataset.label}: ${context.parsed.y.toLocaleString()} requests`,
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
