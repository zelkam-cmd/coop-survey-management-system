/**
 * CampusVoice — Chart.js Glassmorphism Styling & Wrappers
 * High-contrast, modern SaaS dashboard charts (Plus Jakarta Sans typography).
 */

// Global Chart.js default styling
if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family = "'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#64748B';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.padding = 16;
    Chart.defaults.plugins.tooltip.backgroundColor = '#0F172A';
    Chart.defaults.plugins.tooltip.titleColor = '#FFFFFF';
    Chart.defaults.plugins.tooltip.bodyColor = '#F8FAFC';
    Chart.defaults.plugins.tooltip.titleFont = { weight: '700', family: "'Plus Jakarta Sans', sans-serif" };
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 10;
    Chart.defaults.plugins.tooltip.displayColors = true;
    Chart.defaults.plugins.tooltip.boxPadding = 6;
}

// Color palette for modern SaaS charts
var chartColors = {
    primary: '#0284C7',
    secondary: '#06B6D4',
    accent: '#8B5CF6',
    warning: '#F59E0B',
    error: '#EF4444',
    success: '#10B981',
    palette: [
        '#0284C7', '#06B6D4', '#8B5CF6', '#10B981', '#F59E0B', 
        '#EF4444', '#EC4899', '#6366F1', '#14B8A6', '#F97316'
    ],
    paletteLight: [
        'rgba(2,132,199,0.2)', 'rgba(6,182,212,0.2)', 'rgba(139,92,246,0.2)',
        'rgba(16,185,129,0.2)', 'rgba(245,158,11,0.2)', 'rgba(239,68,68,0.2)'
    ]
};

/**
 * Create a pie/doughnut chart for MC/Yes-No response distribution
 */
function createPieChart(canvasId, labels, data, options) {
    var ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    var config = {
        type: (options && options.doughnut) ? 'doughnut' : 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: chartColors.palette.slice(0, data.length),
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 16 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                            var pct = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + context.raw + ' (' + pct + '%)';
                        }
                    }
                }
            },
            cutout: (options && options.doughnut) ? '65%' : 0
        }
    };

    return new Chart(ctx, config);
}

/**
 * Create a bar chart for category comparisons
 */
function createBarChart(canvasId, labels, datasets, options) {
    var ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    var chartDatasets = [];
    if (Array.isArray(datasets) && datasets.length > 0 && typeof datasets[0] === 'object' && datasets[0].data) {
        // Multiple datasets
        datasets.forEach(function(ds, i) {
            chartDatasets.push({
                label: ds.label || 'Dataset ' + (i + 1),
                data: ds.data,
                backgroundColor: ds.color || chartColors.palette[i % chartColors.palette.length],
                borderColor: 'transparent',
                borderWidth: 0,
                borderRadius: 8,
                borderSkipped: false
            });
        });
    } else {
        // Single dataset
        chartDatasets.push({
            label: (options && options.label) || 'Responses',
            data: datasets,
            backgroundColor: chartColors.palette.slice(0, labels.length),
            borderColor: 'transparent',
            borderWidth: 0,
            borderRadius: 8,
            borderSkipped: false
        });
    }

    var config = {
        type: 'bar',
        data: {
            labels: labels,
            datasets: chartDatasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: chartDatasets.length > 1,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(226, 232, 240, 0.6)' },
                    ticks: { 
                        precision: 0,
                        callback: function(value) {
                            return (options && options.percentage) ? value + '%' : value;
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    };

    if (options && options.horizontal) {
        config.options.indexAxis = 'y';
    }

    return new Chart(ctx, config);
}

/**
 * Create a line chart for trends over time
 */
function createLineChart(canvasId, labels, datasets, options) {
    var ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    var chartDatasets = [];
    if (Array.isArray(datasets) && datasets.length > 0 && typeof datasets[0] === 'object' && datasets[0].data) {
        datasets.forEach(function(ds, i) {
            chartDatasets.push({
                label: ds.label || 'Dataset ' + (i + 1),
                data: ds.data,
                borderColor: ds.color || chartColors.palette[i % chartColors.palette.length],
                backgroundColor: (ds.color || chartColors.palette[i % chartColors.palette.length]).replace(')', ', 0.15)').replace('rgb', 'rgba'),
                fill: ds.fill !== undefined ? ds.fill : true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#ffffff',
                pointBorderWidth: 3
            });
        });
    } else {
        chartDatasets.push({
            label: (options && options.label) || 'Value',
            data: datasets,
            borderColor: chartColors.primary,
            backgroundColor: 'rgba(2, 132, 199, 0.12)',
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointHoverRadius: 7,
            pointBackgroundColor: '#ffffff',
            pointBorderWidth: 3,
            pointBorderColor: chartColors.primary
        });
    }

    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: chartDatasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: chartDatasets.length > 1,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(226, 232, 240, 0.6)' }
                },
                x: {
                    grid: { display: false }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

/**
 * Create a rating distribution histogram
 */
function createRatingHistogram(canvasId, distribution, options) {
    var ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    var labels = ['1 - Very Poor', '2 - Poor', '3 - Average', '4 - Good', '5 - Excellent'];
    var colors = ['#EF4444', '#F97316', '#F59E0B', '#0284C7', '#10B981'];

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Responses',
                data: [
                    distribution['1'] || 0,
                    distribution['2'] || 0,
                    distribution['3'] || 0,
                    distribution['4'] || 0,
                    distribution['5'] || 0
                ],
                backgroundColor: colors,
                borderWidth: 0,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(226, 232, 240, 0.6)' },
                    ticks: { precision: 0 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}

/**
 * Create a participation rate gauge (doughnut chart)
 */
function createGaugeChart(canvasId, value, total, options) {
    var ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    var percentage = total > 0 ? Math.round((value / total) * 100) : 0;
    var color = percentage >= 75 ? chartColors.success : 
                percentage >= 50 ? chartColors.primary : chartColors.warning;

    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Participated', 'Remaining'],
            datasets: [{
                data: [value, Math.max(0, total - value)],
                backgroundColor: [color, 'rgba(226, 232, 240, 0.7)'],
                borderWidth: 0,
                hoverOffset: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        },
        plugins: [{
            id: 'centerText',
            afterDraw: function(chart) {
                var ctx2 = chart.ctx;
                var centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                var centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;

                ctx2.save();
                ctx2.textAlign = 'center';
                ctx2.textBaseline = 'middle';
                
                ctx2.font = "800 24px 'Plus Jakarta Sans', sans-serif";
                ctx2.fillStyle = '#0F172A';
                ctx2.fillText(percentage + '%', centerX, centerY - 8);
                
                ctx2.font = "600 12px 'Plus Jakarta Sans', sans-serif";
                ctx2.fillStyle = '#64748B';
                ctx2.fillText((options && options.label) || 'Participation', centerX, centerY + 14);
                
                ctx2.restore();
            }
        }]
    });
}
