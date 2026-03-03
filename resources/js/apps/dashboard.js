/**
 * Dashboard App - FitCRM
 *
 * Handles revenue chart and dashboard interactivity
 */
import ApexCharts from 'apexcharts';

// Expose ApexCharts globally for inline scripts
window.ApexCharts = ApexCharts;

document.addEventListener('DOMContentLoaded', function() {
    initRevenueChart();
});

/**
 * Initialize Revenue Chart with ApexCharts
 */
function initRevenueChart() {
    const chartElement = document.querySelector('#revenue-chart');
    if (!chartElement) return;

    // Get initial data from the page
    const chartDataElement = document.getElementById('revenue-chart-data');
    if (!chartDataElement) return;

    let chartData;
    try {
        chartData = JSON.parse(chartDataElement.textContent);
    } catch (e) {
        console.error('Failed to parse chart data:', e);
        return;
    }

    const options = {
        series: [{
            name: 'Revenue',
            data: chartData.values || []
        }],
        chart: {
            type: 'area',
            height: 280,
            toolbar: { show: false },
            fontFamily: 'inherit',
        },
        colors: ['#10b981'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.1,
                stops: [0, 90, 100]
            }
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        xaxis: {
            categories: chartData.labels || [],
            labels: {
                style: {
                    colors: '#9ca3af',
                    fontSize: '11px'
                }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return '$' + val.toLocaleString();
                },
                style: {
                    colors: '#9ca3af',
                    fontSize: '11px'
                }
            }
        },
        grid: {
            borderColor: '#e5e7eb',
            strokeDashArray: 4,
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return '$' + val.toLocaleString();
                }
            }
        },
        dataLabels: { enabled: false }
    };

    const chart = new ApexCharts(chartElement, options);
    chart.render();

    // Expose chart update function globally
    window.updateChart = function(period) {
        // Update button states
        document.querySelectorAll('[data-chart-period]').forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-soft');
        });
        const activeBtn = document.querySelector(`[data-chart-period="${period}"]`);
        if (activeBtn) {
            activeBtn.classList.remove('btn-soft');
            activeBtn.classList.add('btn-primary');
        }

        // Fetch new data
        fetch(`/api/dashboard/revenue-chart?period=${period}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                chart.updateOptions({
                    xaxis: { categories: data.labels }
                });
                chart.updateSeries([{ data: data.values }]);
            })
            .catch(err => console.error('Failed to update chart:', err));
    };
}
