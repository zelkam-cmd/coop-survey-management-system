/**
 * CampusVoice — Chart.js Wrapper Functions
 * Uses Chart.js v4 from CDN for all analytics visualizations.
 */

// Global Chart.js default styling
if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, sans-serif";
    Chart.defaults.font.size = 13;
    Chart.defaults.color = '#64748B';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.padding = 20;
    Chart.defaults.plugins.tooltip.backgroundColor = '#1E293B';
    Chart.defaults.plugins.tooltip.titleFont = { weight: '600' };
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.plugins.tooltip.displayColors = true;
    Chart.defaults.plugins.tooltip.boxPadding = 6;
}

// Color palette for charts
var chartColors = {
    primary: '#2563EB',
    secondary: '#059669',
    accent: '#7C3AED',
    warning: '#F59E0B',
    error: '#EF4444',
    info: '#06B6D4',
    palette: [
        '#2563EB', '#059669', '#7C3AED', '#F59E0B', '#EF4444', 
        '#06B6D4', '#EC4899', '#F97316', '#6366F1', '#14B8A6'
    ],
    paletteLight: [
        'rgba(37,99,235,0.15)', 'rgba(5,150,105,0.15)', 'rgba(124,58,237,0.15)',
        'rgba(245,158,11,0.15)', 'rgba(239,68,68,0.15)', 'rgba(6,182,212,0.15)',
        'rgba(236,72,153,0.15)', 'rgba(249,115,22,0.15)', 'rgba(99,102,241,0.15)',
        'rgba(20,184,166,0.15)'
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
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 8
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
            cutout: (options && options.doughnut) ? '60%' : 0
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
                borderColor: ds.color || chartColors.palette[i % chartColors.palette.length],
                borderWidth: 0,
                borderRadius: 6,
                borderSkipped: false
            });
        });
    } else {
        // Single dataset
        chartDatasets.push({
            label: (options && options.label) || 'Value',
            data: datasets,
            backgroundColor: chartColors.palette.slice(0, labels.length),
            borderColor: chartColors.palette.slice(0, labels.length),
            borderWidth: 0,
            borderRadius: 6,
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
                    grid: { color: 'rgba(0,0,0,0.04)' },
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
                backgroundColor: (ds.color || chartColors.palette[i % chartColors.palette.length]).replace(')', ', 0.1)').replace('rgb', 'rgba'),
                fill: ds.fill !== undefined ? ds.fill : true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2
            });
        });
    } else {
        chartDatasets.push({
            label: (options && options.label) || 'Value',
            data: datasets,
            borderColor: chartColors.primary,
            backgroundColor: 'rgba(37,99,235,0.08)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#fff',
            pointBorderWidth: 2,
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
                    grid: { color: 'rgba(0,0,0,0.04)' }
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
    var colors = ['#EF4444', '#F97316', '#F59E0B', '#10B981', '#059669'];

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
                borderRadius: 6,
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
                    grid: { color: 'rgba(0,0,0,0.04)' },
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
    var color = percentage >= 75 ? chartColors.secondary : 
                percentage >= 50 ? chartColors.warning : chartColors.error;

    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Participated', 'Remaining'],
            datasets: [{
                data: [value, Math.max(0, total - value)],
                backgroundColor: [color, '#E2E8F0'],
                borderWidth: 0,
                hoverOffset: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
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
                
                ctx2.font = '700 24px Inter, sans-serif';
                ctx2.fillStyle = '#1E293B';
                ctx2.fillText(percentage + '%', centerX, centerY - 8);
                
                ctx2.font = '500 12px Inter, sans-serif';
                ctx2.fillStyle = '#64748B';
                ctx2.fillText((options && options.label) || 'Participation', centerX, centerY + 14);
                
                ctx2.restore();
            }
        }]
    });
}
