@extends('layouts.dashboard')

@section('title', $trans['nav.insights.revenue'] ?? 'Revenue Report')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('reports.index') }}"><span class="icon-[tabler--chart-bar] size-4"></span> {{ $trans['nav.insights'] ?? 'Insights' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--coin] me-1 size-4"></span> {{ $trans['nav.insights.revenue'] ?? 'Revenue' }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header with Filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['nav.insights.revenue'] ?? 'Revenue Report' }}</h1>
            <p class="text-base-content/60 text-sm">Financial performance and revenue breakdown</p>
        </div>

        <form id="filter-form" method="GET" class="flex items-center gap-2 flex-nowrap">
            <select name="period" id="period" class="select select-sm select-bordered" onchange="this.form.submit()">
                <option value="7" {{ $period == '7' ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ $period == '30' ? 'selected' : '' }}>Last 30 days</option>
                <option value="90" {{ $period == '90' ? 'selected' : '' }}>Last 90 days</option>
                <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                <option value="year" {{ $period == 'year' ? 'selected' : '' }}>This Year</option>
            </select>

            <select name="payment_method" id="payment_method" class="select select-sm select-bordered" onchange="this.form.submit()">
                <option value="">All Methods</option>
                @foreach($paymentMethods as $method)
                    <option value="{{ $method }}" {{ $paymentMethod == $method ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $method)) }}</option>
                @endforeach
            </select>

            <select name="type" id="type" class="select select-sm select-bordered" onchange="this.form.submit()">
                <option value="">All Types</option>
                @foreach($revenueTypes as $type)
                    <option value="{{ $type }}" {{ $revenueType == $type ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </select>

            @if($paymentMethod || $revenueType)
                <a href="{{ route('reports.revenue', ['period' => $period]) }}" class="btn btn-ghost btn-sm btn-square" title="Clear Filters">
                    <span class="icon-[tabler--x] size-4"></span>
                </a>
            @endif
        </form>
    </div>

    {{-- Summary Stats --}}
    <div class="flex flex-wrap gap-4">
        <div class="card bg-base-100 flex-1 min-w-[140px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-success">${{ number_format($data['summary']['gross'], 2) }}</div>
                <div class="text-xs text-base-content/60">Gross Revenue</div>
            </div>
        </div>
        <div class="card bg-base-100 flex-1 min-w-[140px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-primary">${{ number_format($data['summary']['net'], 2) }}</div>
                <div class="text-xs text-base-content/60">Net Revenue</div>
            </div>
        </div>
        <div class="card bg-base-100 flex-1 min-w-[140px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-warning">${{ number_format($data['summary']['tax'], 2) }}</div>
                <div class="text-xs text-base-content/60">Tax Collected</div>
            </div>
        </div>
        <div class="card bg-base-100 flex-1 min-w-[140px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-error">${{ number_format($data['summary']['refunds'], 2) }}</div>
                <div class="text-xs text-base-content/60">Refunds</div>
            </div>
        </div>
        <div class="card bg-base-100 flex-1 min-w-[140px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-info">{{ $data['summary']['transaction_count'] ?? 0 }}</div>
                <div class="text-xs text-base-content/60">Transactions</div>
            </div>
        </div>
    </div>

    {{-- Main Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Revenue Trend --}}
        <div class="card bg-base-100 lg:col-span-2">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--chart-line] size-5 mr-2"></span>
                    Revenue Trend
                    <span class="text-base-content/60 font-normal text-sm">
                        ({{ is_numeric($period) ? "Last {$period} days" : ucfirst($period) }}
                        @if($paymentMethod) &middot; {{ ucwords(str_replace('_', ' ', $paymentMethod)) }} @endif
                        @if($revenueType) &middot; {{ ucwords(str_replace('_', ' ', $revenueType)) }} @endif)
                    </span>
                </h2>
                <div id="revenue-chart" style="height: 300px;"></div>
            </div>
        </div>

        {{-- Revenue by Catalog --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--chart-pie] size-5 mr-2"></span>
                    By Category
                </h2>
                @if(count($byCatalog) > 0)
                    <div id="catalog-chart" style="height: 300px;"></div>
                @else
                    <div class="flex items-center justify-center h-[300px] text-base-content/40">
                        <div class="text-center">
                            <span class="icon-[tabler--chart-pie-off] size-12 mx-auto block mb-2"></span>
                            <p>No data available</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Revenue by Type Stacked Chart --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">
                <span class="icon-[tabler--chart-area-line] size-5 mr-2"></span>
                Revenue by Category Over Time
            </h2>
            <div id="revenue-by-type-chart" style="height: 300px;"></div>
        </div>
    </div>

    {{-- By Day & Payment Method --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- By Day of Week --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--calendar-week] size-5 mr-2"></span>
                    Revenue by Day of Week
                </h2>
                <div id="by-day-chart" style="height: 250px;"></div>
            </div>
        </div>

        {{-- By Payment Method Chart --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--credit-card] size-5 mr-2"></span>
                    By Payment Method
                </h2>
                @if(count($data['by_payment_method']) > 0)
                    <div id="payment-method-chart" style="height: 250px;"></div>
                @else
                    <div class="flex items-center justify-center h-[250px] text-base-content/40">
                        <div class="text-center">
                            <span class="icon-[tabler--credit-card-off] size-12 mx-auto block mb-2"></span>
                            <p>No data available</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Breakdown Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- By Payment Method --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">Payment Method Breakdown</h2>
                <div class="space-y-3">
                    @forelse($data['by_payment_method'] as $item)
                        @php $method = $item['method'] ?? 'unknown'; @endphp
                        <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                            <div class="flex items-center gap-2">
                                @if($method === 'stripe')
                                    <span class="icon-[tabler--brand-stripe] size-5 text-primary"></span>
                                @elseif($method === 'cash')
                                    <span class="icon-[tabler--cash] size-5 text-success"></span>
                                @elseif($method === 'card')
                                    <span class="icon-[tabler--credit-card] size-5 text-info"></span>
                                @else
                                    <span class="icon-[tabler--wallet] size-5 text-warning"></span>
                                @endif
                                <span class="capitalize">{{ str_replace('_', ' ', $method) }}</span>
                                <span class="text-xs text-base-content/50">({{ $item['count'] ?? 0 }})</span>
                            </div>
                            <span class="font-bold">${{ number_format($item['total'] ?? 0, 2) }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-base-content/40">No data</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- By Type --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">Revenue Type Breakdown</h2>
                <div class="space-y-3">
                    @forelse($data['by_type'] as $type => $item)
                        <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                            <div class="flex items-center gap-2">
                                @if(str_contains($type, 'membership'))
                                    <span class="icon-[tabler--id-badge-2] size-5 text-primary"></span>
                                @elseif(str_contains($type, 'pack'))
                                    <span class="icon-[tabler--stack-2] size-5 text-info"></span>
                                @elseif(str_contains($type, 'drop_in') || str_contains($type, 'booking'))
                                    <span class="icon-[tabler--calendar-check] size-5 text-success"></span>
                                @else
                                    <span class="icon-[tabler--receipt] size-5 text-warning"></span>
                                @endif
                                <span class="capitalize">{{ str_replace('_', ' ', $type) }}</span>
                                <span class="text-xs text-base-content/50">({{ $item['count'] ?? 0 }})</span>
                            </div>
                            <span class="font-bold">${{ number_format($item['total'] ?? 0, 2) }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-base-content/40">No data</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Outstanding Invoices --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Outstanding Invoices</h2>
                <a href="{{ route('payments.transactions') }}" class="btn btn-soft btn-sm">View All</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 bg-warning/10 rounded-lg text-center">
                    <div class="text-2xl font-bold text-warning">${{ number_format($data['outstanding']['total'], 2) }}</div>
                    <div class="text-sm text-base-content/60">Total Outstanding</div>
                </div>
                <div class="p-4 bg-error/10 rounded-lg text-center">
                    <div class="text-2xl font-bold text-error">${{ number_format($data['outstanding']['overdue_total'], 2) }}</div>
                    <div class="text-sm text-base-content/60">Overdue</div>
                </div>
                <div class="p-4 bg-base-200/50 rounded-lg text-center">
                    <div class="text-2xl font-bold">{{ $data['outstanding']['count'] }}</div>
                    <div class="text-sm text-base-content/60">Invoices ({{ $data['outstanding']['overdue_count'] }} overdue)</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
    @vite(['resources/js/apps/dashboard.js'])
    <script id="revenue-chart-data" type="application/json">@json($chartData)</script>
    <script id="chart-data-by-type" type="application/json">@json($chartDataByType)</script>
    <script id="by-catalog-data" type="application/json">@json($byCatalog)</script>
    <script id="by-day-data" type="application/json">@json($byDayOfWeek)</script>
    <script id="by-payment-method-data" type="application/json">@json($data['by_payment_method'])</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = JSON.parse(document.getElementById('revenue-chart-data').textContent);
    const chartDataByType = JSON.parse(document.getElementById('chart-data-by-type').textContent);
    const byCatalogData = JSON.parse(document.getElementById('by-catalog-data').textContent);
    const byDayData = JSON.parse(document.getElementById('by-day-data').textContent);
    const byPaymentMethodData = JSON.parse(document.getElementById('by-payment-method-data').textContent);

    // Main Revenue Trend Chart
    if (document.querySelector("#revenue-chart")) {
        const options = {
            series: [{ name: 'Revenue', data: chartData.values }],
            chart: {
                type: 'area',
                height: 300,
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
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: chartData.labels,
                labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
            },
            yaxis: {
                labels: {
                    formatter: function(val) { return '$' + val.toLocaleString(); },
                    style: { colors: '#9ca3af', fontSize: '11px' }
                }
            },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            tooltip: {
                y: { formatter: function(val) { return '$' + val.toLocaleString(); } }
            },
            dataLabels: { enabled: false }
        };

        const chart = new ApexCharts(document.querySelector("#revenue-chart"), options);
        chart.render();
    }

    // Revenue by Catalog - Donut Chart
    if (document.querySelector("#catalog-chart") && byCatalogData.length > 0) {
        const catalogOptions = {
            series: byCatalogData.map(c => c.total),
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'inherit',
            },
            labels: byCatalogData.map(c => c.name),
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
            legend: { position: 'bottom', fontSize: '12px' },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '14px',
                                fontWeight: 600,
                                formatter: function(w) {
                                    return '$' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                                }
                            }
                        }
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) { return '$' + val.toLocaleString(); }
                }
            },
            dataLabels: { enabled: false }
        };

        const catalogChart = new ApexCharts(document.querySelector("#catalog-chart"), catalogOptions);
        catalogChart.render();
    }

    // Revenue by Type Over Time - Stacked Area
    if (document.querySelector("#revenue-by-type-chart")) {
        const byTypeOptions = {
            series: [
                { name: 'Memberships', data: chartDataByType.memberships },
                { name: 'Class Packs', data: chartDataByType.class_packs },
                { name: 'Drop-ins', data: chartDataByType.drop_ins },
                { name: 'Other', data: chartDataByType.other }
            ],
            chart: {
                type: 'area',
                height: 300,
                stacked: true,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.5,
                    opacityTo: 0.1,
                }
            },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: chartDataByType.labels,
                labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
            },
            yaxis: {
                labels: {
                    formatter: function(val) { return '$' + val.toLocaleString(); },
                    style: { colors: '#9ca3af', fontSize: '11px' }
                }
            },
            legend: { position: 'top' },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            tooltip: {
                y: { formatter: function(val) { return '$' + val.toLocaleString(); } }
            },
            dataLabels: { enabled: false }
        };

        const byTypeChart = new ApexCharts(document.querySelector("#revenue-by-type-chart"), byTypeOptions);
        byTypeChart.render();
    }

    // By Day of Week Chart
    if (document.querySelector("#by-day-chart") && byDayData.length > 0) {
        const byDayOptions = {
            series: [{
                name: 'Revenue',
                data: byDayData.map(d => d.total)
            }],
            chart: {
                type: 'bar',
                height: 250,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '60%',
                    distributed: true,
                }
            },
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'],
            xaxis: {
                categories: byDayData.map(d => d.day),
                labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
            },
            yaxis: {
                labels: {
                    formatter: function(val) { return '$' + val.toLocaleString(); },
                    style: { colors: '#9ca3af', fontSize: '11px' }
                }
            },
            legend: { show: false },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            tooltip: {
                y: { formatter: function(val) { return '$' + val.toLocaleString(); } }
            },
            dataLabels: { enabled: false }
        };

        const byDayChart = new ApexCharts(document.querySelector("#by-day-chart"), byDayOptions);
        byDayChart.render();
    }

    // By Payment Method Chart
    if (document.querySelector("#payment-method-chart") && byPaymentMethodData.length > 0) {
        const pmOptions = {
            series: byPaymentMethodData.map(p => p.total),
            chart: {
                type: 'donut',
                height: 250,
                fontFamily: 'inherit',
            },
            labels: byPaymentMethodData.map(p => (p.method || 'Unknown').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())),
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
            legend: { position: 'bottom', fontSize: '12px' },
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%',
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) { return '$' + val.toLocaleString(); }
                }
            },
            dataLabels: { enabled: false }
        };

        const pmChart = new ApexCharts(document.querySelector("#payment-method-chart"), pmOptions);
        pmChart.render();
    }
});
</script>
@endpush
