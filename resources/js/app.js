import './bootstrap';

import Alpine from 'alpinejs';
import {
    Chart,
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
);

Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, sans-serif';
Chart.defaults.color = '#64748b';
Chart.defaults.borderColor = 'rgba(148, 163, 184, 0.18)';

window.Alpine = Alpine;

Alpine.start();

const chartInstances = new WeakMap();

function initAdminCharts() {
    document.querySelectorAll('.js-admin-chart').forEach((canvas) => {
        if (chartInstances.has(canvas)) {
            return;
        }

        const configNode = canvas.parentElement?.querySelector('[data-admin-chart-config]');

        if (!configNode?.textContent) {
            return;
        }

        let config;

        try {
            config = JSON.parse(configNode.textContent);
        } catch (error) {
            console.warn('Invalid admin chart config', error);
            return;
        }

        const ctx = canvas.getContext('2d');

        if (!ctx) {
            return;
        }

        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 8,
                        boxHeight: 8,
                        usePointStyle: true,
                    },
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.92)',
                    borderColor: 'rgba(255, 255, 255, 0.12)',
                    borderWidth: 1,
                    padding: 12,
                    titleColor: '#ffffff',
                    bodyColor: '#cbd5e1',
                    cornerRadius: 14,
                },
            },
            scales: config.type === 'doughnut' ? undefined : {
                x: {
                    grid: {
                        display: false,
                    },
                    border: {
                        display: false,
                    },
                },
                y: {
                    beginAtZero: true,
                    border: {
                        display: false,
                    },
                    ticks: {
                        precision: 0,
                    },
                },
            },
        };

        const chart = new Chart(ctx, {
            ...config,
            options: {
                ...baseOptions,
                ...(config.options || {}),
                plugins: {
                    ...baseOptions.plugins,
                    ...(config.options?.plugins || {}),
                },
            },
        });

        chartInstances.set(canvas, chart);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminCharts);
} else {
    initAdminCharts();
}
