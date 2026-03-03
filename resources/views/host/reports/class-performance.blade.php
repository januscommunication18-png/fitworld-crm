@extends('layouts.dashboard')

@section('title', 'Catalog Performance')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('reports.index') }}"><span class="icon-[tabler--chart-bar] size-4"></span> {{ $trans['nav.insights'] ?? 'Insights' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--trophy] me-1 size-4"></span> Catalog Performance</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header with Filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Catalog Performance</h1>
            <p class="text-base-content/60 text-sm">Performance across all catalog types: Classes, Services, Memberships, Class Packs, Rentals</p>
        </div>

        <form id="filter-form" method="GET" class="flex items-center gap-2 flex-nowrap">
            <select name="period" id="period" class="select select-sm select-bordered" onchange="this.form.submit()">
                <option value="7" {{ $period == '7' ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ $period == '30' ? 'selected' : '' }}>Last 30 days</option>
                <option value="90" {{ $period == '90' ? 'selected' : '' }}>Last 90 days</option>
                <option value="180" {{ $period == '180' ? 'selected' : '' }}>Last 6 months</option>
            </select>
        </form>
    </div>

    {{-- Overall Summary Stats --}}
    <div class="flex flex-wrap gap-4">
        <div class="card bg-base-100 flex-1 min-w-[140px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-success">${{ number_format($overallSummary['total_revenue'], 2) }}</div>
                <div class="text-xs text-base-content/60">Total Revenue</div>
            </div>
        </div>
        <div class="card bg-base-100 flex-1 min-w-[140px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-primary">{{ $overallSummary['total_bookings'] }}</div>
                <div class="text-xs text-base-content/60">Total Bookings</div>
            </div>
        </div>
        <div class="card bg-base-100 flex-1 min-w-[140px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-info">{{ $overallSummary['class_sessions'] }}</div>
                <div class="text-xs text-base-content/60">Class Sessions</div>
            </div>
        </div>
        <div class="card bg-base-100 flex-1 min-w-[140px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-warning">{{ $overallSummary['active_memberships'] }}</div>
                <div class="text-xs text-base-content/60">Active Memberships</div>
            </div>
        </div>
        <div class="card bg-base-100 flex-1 min-w-[140px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-secondary">{{ $overallSummary['class_packs_sold'] }}</div>
                <div class="text-xs text-base-content/60">Class Packs Sold</div>
            </div>
        </div>
    </div>

    {{-- Catalog Overview Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Bookings by Catalog Type --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--chart-pie] size-5 mr-2"></span>
                    Bookings by Catalog Type
                </h2>
                <div id="catalog-bookings-chart" style="height: 300px;"></div>
            </div>
        </div>

        {{-- Revenue by Catalog Type --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--coin] size-5 mr-2"></span>
                    Revenue by Catalog Type
                </h2>
                <div id="catalog-revenue-chart" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    {{-- Catalog Trend Chart --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">
                <span class="icon-[tabler--chart-line] size-5 mr-2"></span>
                Bookings Trend by Catalog Type
                <span class="text-base-content/60 font-normal text-sm">({{ is_numeric($period) ? "Last {$period} days" : $period }})</span>
            </h2>
            <div id="catalog-trend-chart" style="height: 300px;"></div>
        </div>
    </div>

    {{-- CLASS SESSIONS Section --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">
                    <span class="icon-[tabler--calendar-event] size-5 mr-2 text-primary"></span>
                    Class Sessions
                </h2>
                <div class="flex gap-4 text-sm">
                    <span class="badge badge-primary">{{ $classSessionsData['summary']['total_sessions'] }} Sessions</span>
                    <span class="badge badge-success">{{ $classSessionsData['summary']['total_bookings'] }} Bookings</span>
                    <span class="badge badge-info">{{ $classSessionsData['summary']['utilization'] }}% Utilization</span>
                    <span class="badge badge-warning">${{ number_format($classSessionsData['summary']['revenue'], 2) }} Revenue</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- By Class --}}
                <div>
                    <h3 class="font-medium mb-2">Top Classes</h3>
                    @if(count($classSessionsData['byClass']) > 0)
                        <div id="classes-by-type-chart" style="height: 250px;"></div>
                    @else
                        <div class="text-center py-8 text-base-content/40">No class data</div>
                    @endif
                </div>

                {{-- By Instructor --}}
                <div>
                    <h3 class="font-medium mb-2">Top Instructors</h3>
                    @if(count($classSessionsData['byInstructor']) > 0)
                        <div id="classes-by-instructor-chart" style="height: 250px;"></div>
                    @else
                        <div class="text-center py-8 text-base-content/40">No instructor data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- SERVICES Section --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">
                    <span class="icon-[tabler--massage] size-5 mr-2 text-success"></span>
                    Services
                </h2>
                <div class="flex gap-4 text-sm">
                    <span class="badge badge-primary">{{ $servicesData['summary']['total_slots'] }} Slots</span>
                    <span class="badge badge-success">{{ $servicesData['summary']['total_bookings'] }} Bookings</span>
                    <span class="badge badge-warning">${{ number_format($servicesData['summary']['revenue'], 2) }} Revenue</span>
                </div>
            </div>

            @if(count($servicesData['byService']) > 0)
                <div id="services-chart" style="height: 250px;"></div>
            @else
                <div class="text-center py-8 text-base-content/40">No services data in this period</div>
            @endif
        </div>
    </div>

    {{-- MEMBERSHIPS & CLASS PACKS Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Memberships --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">
                        <span class="icon-[tabler--id-badge-2] size-5 mr-2 text-warning"></span>
                        Memberships
                    </h2>
                    <span class="badge badge-warning">${{ number_format($membershipsData['summary']['revenue'], 2) }}</span>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="p-3 bg-base-200/50 rounded-lg text-center">
                        <div class="text-xl font-bold">{{ $membershipsData['summary']['total_purchases'] }}</div>
                        <div class="text-xs text-base-content/60">New Purchases</div>
                    </div>
                    <div class="p-3 bg-base-200/50 rounded-lg text-center">
                        <div class="text-xl font-bold text-success">{{ $membershipsData['summary']['active_count'] }}</div>
                        <div class="text-xs text-base-content/60">Active Members</div>
                    </div>
                </div>

                @if(count($membershipsData['byPlan']) > 0)
                    <div id="memberships-chart" style="height: 200px;"></div>
                @else
                    <div class="text-center py-4 text-base-content/40">No membership data</div>
                @endif
            </div>
        </div>

        {{-- Class Packs --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">
                        <span class="icon-[tabler--ticket] size-5 mr-2 text-secondary"></span>
                        Class Packs
                    </h2>
                    <span class="badge badge-secondary">${{ number_format($classPacksData['summary']['revenue'], 2) }}</span>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="p-3 bg-base-200/50 rounded-lg text-center">
                        <div class="text-xl font-bold">{{ $classPacksData['summary']['total_purchases'] }}</div>
                        <div class="text-xs text-base-content/60">Packs Sold</div>
                    </div>
                    <div class="p-3 bg-base-200/50 rounded-lg text-center">
                        <div class="text-xl font-bold text-info">{{ $classPacksData['summary']['total_credits_sold'] }}</div>
                        <div class="text-xs text-base-content/60">Credits Sold</div>
                    </div>
                </div>

                @if(count($classPacksData['byPack']) > 0)
                    <div id="classpacks-chart" style="height: 200px;"></div>
                @else
                    <div class="text-center py-4 text-base-content/40">No class pack data</div>
                @endif
            </div>
        </div>
    </div>

    {{-- RENTALS Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Rental Items --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">
                        <span class="icon-[tabler--shopping-bag] size-5 mr-2 text-error"></span>
                        Rental Items
                    </h2>
                    <div class="flex gap-2">
                        <span class="badge badge-primary">{{ $rentalsData['summary']['total_bookings'] }} Bookings</span>
                        <span class="badge badge-error">${{ number_format($rentalsData['summary']['revenue'], 2) }}</span>
                    </div>
                </div>

                @if(count($rentalsData['byItem']) > 0)
                    <div id="rentals-chart" style="height: 200px;"></div>
                @else
                    <div class="text-center py-8 text-base-content/40">No rental data in this period</div>
                @endif
            </div>
        </div>

        {{-- Space Rentals --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">
                        <span class="icon-[tabler--building] size-5 mr-2 text-info"></span>
                        Space/Studio Rentals
                    </h2>
                    <div class="flex gap-2">
                        <span class="badge badge-primary">{{ $spaceRentalsData['summary']['total_bookings'] }} Bookings</span>
                        <span class="badge badge-info">${{ number_format($spaceRentalsData['summary']['revenue'], 2) }}</span>
                    </div>
                </div>

                @if(count($spaceRentalsData['bySpace']) > 0)
                    <div id="space-rentals-chart" style="height: 200px;"></div>
                @else
                    <div class="text-center py-8 text-base-content/40">No space rental data in this period</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Catalog Performance Table --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">
                <span class="icon-[tabler--table] size-5 mr-2"></span>
                Catalog Summary
            </h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Catalog Type</th>
                            <th class="text-center">Bookings/Purchases</th>
                            <th class="text-center">Revenue</th>
                            <th class="text-center">% of Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($catalogOverview as $catalog)
                            <tr>
                                <td class="font-medium">{{ $catalog['name'] }}</td>
                                <td class="text-center">{{ $catalog['bookings'] }}</td>
                                <td class="text-center font-bold">${{ number_format($catalog['revenue'], 2) }}</td>
                                <td class="text-center">
                                    @if($overallSummary['total_revenue'] > 0)
                                        {{ round(($catalog['revenue'] / $overallSummary['total_revenue']) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-bold">
                            <td>Total</td>
                            <td class="text-center">{{ $overallSummary['total_bookings'] }}</td>
                            <td class="text-center">${{ number_format($overallSummary['total_revenue'], 2) }}</td>
                            <td class="text-center">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
    @vite(['resources/js/apps/dashboard.js'])
    <script id="catalog-overview-data" type="application/json">@json($catalogOverviewChart)</script>
    <script id="catalog-trend-data" type="application/json">@json($catalogTrend)</script>
    <script id="classes-by-type-data" type="application/json">@json($classSessionsData['byClass'])</script>
    <script id="classes-by-instructor-data" type="application/json">@json($classSessionsData['byInstructor'])</script>
    <script id="services-data" type="application/json">@json($servicesData['byService'])</script>
    <script id="memberships-data" type="application/json">@json($membershipsData['byPlan'])</script>
    <script id="classpacks-data" type="application/json">@json($classPacksData['byPack'])</script>
    <script id="rentals-data" type="application/json">@json($rentalsData['byItem'])</script>
    <script id="space-rentals-data" type="application/json">@json($spaceRentalsData['bySpace'])</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const catalogData = JSON.parse(document.getElementById('catalog-overview-data').textContent);
    const trendData = JSON.parse(document.getElementById('catalog-trend-data').textContent);
    const classesByType = JSON.parse(document.getElementById('classes-by-type-data').textContent);
    const classesByInstructor = JSON.parse(document.getElementById('classes-by-instructor-data').textContent);
    const servicesData = JSON.parse(document.getElementById('services-data').textContent);
    const membershipsData = JSON.parse(document.getElementById('memberships-data').textContent);
    const classpacksData = JSON.parse(document.getElementById('classpacks-data').textContent);
    const rentalsData = JSON.parse(document.getElementById('rentals-data').textContent);
    const spaceRentalsData = JSON.parse(document.getElementById('space-rentals-data').textContent);

    const colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'];

    // Catalog Bookings Donut Chart
    if (document.querySelector("#catalog-bookings-chart")) {
        new ApexCharts(document.querySelector("#catalog-bookings-chart"), {
            series: catalogData.bookings,
            chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
            labels: catalogData.labels,
            colors: colors,
            legend: { position: 'bottom' },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '14px' } } } } },
            dataLabels: { enabled: false }
        }).render();
    }

    // Catalog Revenue Donut Chart
    if (document.querySelector("#catalog-revenue-chart")) {
        new ApexCharts(document.querySelector("#catalog-revenue-chart"), {
            series: catalogData.revenue,
            chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
            labels: catalogData.labels,
            colors: colors,
            legend: { position: 'bottom' },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '14px', formatter: (w) => '$' + w.globals.seriesTotals.reduce((a,b) => a+b, 0).toLocaleString() } } } } },
            tooltip: { y: { formatter: (val) => '$' + val.toLocaleString() } },
            dataLabels: { enabled: false }
        }).render();
    }

    // Catalog Trend Stacked Area Chart
    if (document.querySelector("#catalog-trend-chart")) {
        new ApexCharts(document.querySelector("#catalog-trend-chart"), {
            series: [
                { name: 'Classes', data: trendData.classes },
                { name: 'Services', data: trendData.services },
                { name: 'Memberships', data: trendData.memberships },
                { name: 'Class Packs', data: trendData.class_packs },
                { name: 'Rentals', data: trendData.rentals }
            ],
            chart: { type: 'area', height: 300, stacked: true, toolbar: { show: false }, fontFamily: 'inherit' },
            colors: colors,
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1 } },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: { categories: trendData.labels, labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
            yaxis: { labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
            legend: { position: 'top' },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        }).render();
    }

    // Classes by Type Bar Chart
    if (document.querySelector("#classes-by-type-chart") && classesByType.length > 0) {
        new ApexCharts(document.querySelector("#classes-by-type-chart"), {
            series: [{ name: 'Bookings', data: classesByType.map(c => c.bookings) }],
            chart: { type: 'bar', height: 250, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
            colors: ['#3b82f6'],
            xaxis: { categories: classesByType.map(c => c.name), labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
            yaxis: { labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            dataLabels: { enabled: true, offsetX: 15, style: { fontSize: '11px', colors: ['#374151'] } }
        }).render();
    }

    // Classes by Instructor Bar Chart
    if (document.querySelector("#classes-by-instructor-chart") && classesByInstructor.length > 0) {
        new ApexCharts(document.querySelector("#classes-by-instructor-chart"), {
            series: [{ name: 'Bookings', data: classesByInstructor.map(i => i.bookings) }],
            chart: { type: 'bar', height: 250, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
            colors: ['#10b981'],
            xaxis: { categories: classesByInstructor.map(i => i.name), labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
            yaxis: { labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            dataLabels: { enabled: true, offsetX: 15, style: { fontSize: '11px', colors: ['#374151'] } }
        }).render();
    }

    // Services Bar Chart
    if (document.querySelector("#services-chart") && servicesData.length > 0) {
        new ApexCharts(document.querySelector("#services-chart"), {
            series: [{ name: 'Bookings', data: servicesData.map(s => s.bookings) }],
            chart: { type: 'bar', height: 250, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
            colors: ['#10b981'],
            xaxis: { categories: servicesData.map(s => s.name), labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
            yaxis: { labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            dataLabels: { enabled: true, offsetX: 15, style: { fontSize: '11px', colors: ['#374151'] } }
        }).render();
    }

    // Memberships Donut Chart
    if (document.querySelector("#memberships-chart") && membershipsData.length > 0) {
        new ApexCharts(document.querySelector("#memberships-chart"), {
            series: membershipsData.map(m => m.purchases),
            chart: { type: 'donut', height: 200, fontFamily: 'inherit' },
            labels: membershipsData.map(m => m.name),
            colors: colors,
            legend: { position: 'bottom', fontSize: '11px' },
            plotOptions: { pie: { donut: { size: '60%' } } },
            dataLabels: { enabled: false }
        }).render();
    }

    // Class Packs Donut Chart
    if (document.querySelector("#classpacks-chart") && classpacksData.length > 0) {
        new ApexCharts(document.querySelector("#classpacks-chart"), {
            series: classpacksData.map(c => c.purchases),
            chart: { type: 'donut', height: 200, fontFamily: 'inherit' },
            labels: classpacksData.map(c => c.name),
            colors: colors,
            legend: { position: 'bottom', fontSize: '11px' },
            plotOptions: { pie: { donut: { size: '60%' } } },
            dataLabels: { enabled: false }
        }).render();
    }

    // Rentals Bar Chart
    if (document.querySelector("#rentals-chart") && rentalsData.length > 0) {
        new ApexCharts(document.querySelector("#rentals-chart"), {
            series: [{ name: 'Bookings', data: rentalsData.map(r => r.bookings) }],
            chart: { type: 'bar', height: 200, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
            colors: ['#ef4444'],
            xaxis: { categories: rentalsData.map(r => r.name), labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
            yaxis: { labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            dataLabels: { enabled: true, offsetX: 15, style: { fontSize: '11px', colors: ['#374151'] } }
        }).render();
    }

    // Space Rentals Bar Chart
    if (document.querySelector("#space-rentals-chart") && spaceRentalsData.length > 0) {
        new ApexCharts(document.querySelector("#space-rentals-chart"), {
            series: [{ name: 'Bookings', data: spaceRentalsData.map(s => s.bookings) }],
            chart: { type: 'bar', height: 200, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
            colors: ['#06b6d4'],
            xaxis: { categories: spaceRentalsData.map(s => s.name), labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
            yaxis: { labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            dataLabels: { enabled: true, offsetX: 15, style: { fontSize: '11px', colors: ['#374151'] } }
        }).render();
    }
});
</script>
@endpush
