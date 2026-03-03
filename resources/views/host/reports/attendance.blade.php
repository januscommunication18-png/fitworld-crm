@extends('layouts.dashboard')

@section('title', $trans['nav.insights.attendance'] ?? 'Attendance Report')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('reports.index') }}"><span class="icon-[tabler--chart-bar] size-4"></span> {{ $trans['nav.insights'] ?? 'Insights' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--checks] me-1 size-4"></span> {{ $trans['nav.insights.attendance'] ?? 'Attendance' }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header with Filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['nav.insights.attendance'] ?? 'Attendance Report' }}</h1>
            <p class="text-base-content/60 text-sm">Track attendance patterns and identify issues</p>
        </div>

        <form id="filter-form" method="GET" class="flex items-center gap-2 flex-nowrap">
            <select name="period" id="period" class="select select-sm select-bordered" onchange="this.form.submit()">
                <option value="7" {{ $period == '7' ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ $period == '30' ? 'selected' : '' }}>Last 30 days</option>
                <option value="90" {{ $period == '90' ? 'selected' : '' }}>Last 90 days</option>
            </select>

            <select name="class_plan" id="class_plan" class="select select-sm select-bordered" onchange="this.form.submit()">
                <option value="">All Classes</option>
                @foreach($classPlans as $id => $name)
                    <option value="{{ $id }}" {{ $classPlanId == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>

            <select name="instructor" id="instructor" class="select select-sm select-bordered" onchange="this.form.submit()">
                <option value="">All Instructors</option>
                @foreach($instructors as $id => $name)
                    <option value="{{ $id }}" {{ $instructorId == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>

            @if($classPlanId || $instructorId)
                <a href="{{ route('reports.attendance', ['period' => $period]) }}" class="btn btn-ghost btn-sm btn-square" title="Clear Filters">
                    <span class="icon-[tabler--x] size-4"></span>
                </a>
            @endif
        </form>
    </div>

    {{-- Summary Stats --}}
    <div class="flex flex-wrap gap-4">
        <div class="card bg-base-100 flex-1 min-w-[120px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-success">{{ $data['summary']['attendance_rate'] }}%</div>
                <div class="text-xs text-base-content/60">Attendance Rate</div>
            </div>
        </div>
        <div class="card bg-base-100 flex-1 min-w-[120px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-error">{{ $data['summary']['no_show_rate'] }}%</div>
                <div class="text-xs text-base-content/60">No-Show Rate</div>
            </div>
        </div>
        <div class="card bg-base-100 flex-1 min-w-[120px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-warning">{{ $data['summary']['cancel_rate'] ?? $data['summary']['late_cancel_rate'] }}%</div>
                <div class="text-xs text-base-content/60">Cancel Rate</div>
            </div>
        </div>
        <div class="card bg-base-100 flex-1 min-w-[120px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-primary">{{ $data['summary']['total_bookings'] }}</div>
                <div class="text-xs text-base-content/60">Total Bookings</div>
            </div>
        </div>
        <div class="card bg-base-100 flex-1 min-w-[120px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-info">{{ $data['summary']['completed'] ?? 0 }}</div>
                <div class="text-xs text-base-content/60">Completed</div>
            </div>
        </div>
        <div class="card bg-base-100 flex-1 min-w-[120px]">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-error/70">{{ $data['summary']['no_show'] ?? 0 }}</div>
                <div class="text-xs text-base-content/60">No Shows</div>
            </div>
        </div>
    </div>

    {{-- Main Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Attendance Trend --}}
        <div class="card bg-base-100 lg:col-span-2">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--chart-line] size-5 mr-2"></span>
                    Attendance Trend
                    <span class="text-base-content/60 font-normal text-sm">
                        (Last {{ $period }} days
                        @if($classPlanId && $classPlans->has($classPlanId))
                            &middot; {{ $classPlans[$classPlanId] }}
                        @endif
                        @if($instructorId && $instructors->has($instructorId))
                            &middot; {{ $instructors[$instructorId] }}
                        @endif)
                    </span>
                </h2>
                <div id="attendance-chart" style="height: 300px;"></div>
            </div>
        </div>

        {{-- Status Breakdown Donut --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--chart-pie] size-5 mr-2"></span>
                    Status Breakdown
                </h2>
                <div id="status-donut-chart" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    {{-- By Class & By Instructor --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- By Class --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--calendar-event] size-5 mr-2"></span>
                    Attendance by Class
                </h2>
                @if(count($byClass) > 0)
                    <div id="by-class-chart" style="height: 280px;"></div>
                @else
                    <div class="flex items-center justify-center h-[280px] text-base-content/40">
                        <div class="text-center">
                            <span class="icon-[tabler--chart-bar-off] size-12 mx-auto block mb-2"></span>
                            <p>No class data available</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- By Instructor --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--user] size-5 mr-2"></span>
                    Attendance by Instructor
                </h2>
                @if(count($byInstructor) > 0)
                    <div id="by-instructor-chart" style="height: 280px;"></div>
                @else
                    <div class="flex items-center justify-center h-[280px] text-base-content/40">
                        <div class="text-center">
                            <span class="icon-[tabler--users-minus] size-12 mx-auto block mb-2"></span>
                            <p>No instructor data available</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Time-based Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- By Day of Week --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--calendar-week] size-5 mr-2"></span>
                    Attendance by Day of Week
                </h2>
                <div id="by-day-chart" style="height: 250px;"></div>
            </div>
        </div>

        {{-- By Hour --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--clock] size-5 mr-2"></span>
                    Attendance by Time of Day
                </h2>
                <div id="by-hour-chart" style="height: 250px;"></div>
            </div>
        </div>
    </div>

    {{-- Breakdown Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Status Breakdown --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">Booking Status Breakdown</h2>
                <div class="space-y-3">
                    @forelse($data['status_breakdown'] as $status => $count)
                        <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                            <div class="flex items-center gap-2">
                                @if($status === 'completed')
                                    <span class="w-3 h-3 rounded-full bg-success"></span>
                                @elseif($status === 'confirmed')
                                    <span class="w-3 h-3 rounded-full bg-info"></span>
                                @elseif($status === 'no_show')
                                    <span class="w-3 h-3 rounded-full bg-error"></span>
                                @elseif($status === 'cancelled')
                                    <span class="w-3 h-3 rounded-full bg-warning"></span>
                                @else
                                    <span class="w-3 h-3 rounded-full bg-base-content/30"></span>
                                @endif
                                <span class="capitalize">{{ str_replace('_', ' ', $status) }}</span>
                            </div>
                            <span class="font-bold">{{ $count }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-base-content/40">No data</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Source Breakdown --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">Booking Source Breakdown</h2>
                <div class="space-y-3">
                    @forelse($data['source_breakdown'] as $source => $count)
                        <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                            <div class="flex items-center gap-2">
                                @if($source === 'online' || $source === 'website')
                                    <span class="icon-[tabler--world] size-5 text-primary"></span>
                                @elseif($source === 'staff' || $source === 'admin')
                                    <span class="icon-[tabler--user-cog] size-5 text-info"></span>
                                @elseif($source === 'app')
                                    <span class="icon-[tabler--device-mobile] size-5 text-success"></span>
                                @else
                                    <span class="icon-[tabler--calendar-plus] size-5 text-warning"></span>
                                @endif
                                <span class="capitalize">{{ str_replace('_', ' ', $source ?: 'Unknown') }}</span>
                            </div>
                            <span class="font-bold">{{ $count }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-base-content/40">No data</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Classes --}}
    @if($data['todays_classes']->isNotEmpty())
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Today's Classes</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Class</th>
                            <th>Instructor</th>
                            <th>Booked</th>
                            <th>Utilization</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['todays_classes'] as $class)
                            <tr>
                                <td class="font-medium">{{ $class['time'] }}</td>
                                <td>{{ $class['name'] }}</td>
                                <td>{{ $class['instructor'] }}</td>
                                <td>{{ $class['booked'] }}/{{ $class['capacity'] }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-20 bg-base-200 rounded-full h-2">
                                            <div class="bg-primary h-2 rounded-full" style="width: {{ min($class['utilization'], 100) }}%"></div>
                                        </div>
                                        <span class="text-sm">{{ $class['utilization'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('head')
    @vite(['resources/js/apps/dashboard.js'])
    <script id="attendance-chart-data" type="application/json">@json($chartData)</script>
    <script id="by-class-data" type="application/json">@json($byClass)</script>
    <script id="by-instructor-data" type="application/json">@json($byInstructor)</script>
    <script id="by-day-data" type="application/json">@json($byDayOfWeek)</script>
    <script id="by-hour-data" type="application/json">@json($byHour)</script>
    <script id="status-breakdown-data" type="application/json">@json($data['status_breakdown'])</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = JSON.parse(document.getElementById('attendance-chart-data').textContent);
    const byClassData = JSON.parse(document.getElementById('by-class-data').textContent);
    const byInstructorData = JSON.parse(document.getElementById('by-instructor-data').textContent);
    const byDayData = JSON.parse(document.getElementById('by-day-data').textContent);
    const byHourData = JSON.parse(document.getElementById('by-hour-data').textContent);
    const statusData = JSON.parse(document.getElementById('status-breakdown-data').textContent);

    // Main Attendance Trend Chart
    if (document.querySelector("#attendance-chart")) {
        const options = {
            series: [
                { name: 'Completed', data: chartData.attendance },
                { name: 'No Shows', data: chartData.no_shows },
                { name: 'Cancelled', data: chartData.cancelled || [] }
            ],
            chart: {
                type: 'bar',
                height: 300,
                stacked: true,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            colors: ['#10b981', '#ef4444', '#f59e0b'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '60%',
                    borderRadius: 4,
                },
            },
            xaxis: {
                categories: chartData.labels,
                labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
            },
            yaxis: {
                labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
            },
            legend: { position: 'top' },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        };

        const chart = new ApexCharts(document.querySelector("#attendance-chart"), options);
        chart.render();
    }

    // Status Donut Chart
    if (document.querySelector("#status-donut-chart") && Object.keys(statusData).length > 0) {
        const statusLabels = [];
        const statusValues = [];
        const statusColors = [];

        const colorMap = {
            'completed': '#10b981',
            'confirmed': '#3b82f6',
            'no_show': '#ef4444',
            'cancelled': '#f59e0b',
            'waitlisted': '#8b5cf6'
        };

        for (const [key, value] of Object.entries(statusData)) {
            if (value > 0) {
                statusLabels.push(key.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()));
                statusValues.push(value);
                statusColors.push(colorMap[key] || '#9ca3af');
            }
        }

        const donutOptions = {
            series: statusValues,
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'inherit',
            },
            labels: statusLabels,
            colors: statusColors,
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
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false }
        };

        const donutChart = new ApexCharts(document.querySelector("#status-donut-chart"), donutOptions);
        donutChart.render();
    }

    // By Class Chart
    if (document.querySelector("#by-class-chart") && byClassData.length > 0) {
        const byClassOptions = {
            series: [{
                name: 'Completed',
                data: byClassData.map(c => c.completed)
            }, {
                name: 'No Show',
                data: byClassData.map(c => c.no_show)
            }, {
                name: 'Cancelled',
                data: byClassData.map(c => c.cancelled)
            }],
            chart: {
                type: 'bar',
                height: 280,
                stacked: true,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '70%',
                }
            },
            colors: ['#10b981', '#ef4444', '#f59e0b'],
            xaxis: {
                categories: byClassData.map(c => c.name),
                labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
            },
            yaxis: {
                labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
            },
            legend: { position: 'top', horizontalAlign: 'right' },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        };

        const byClassChart = new ApexCharts(document.querySelector("#by-class-chart"), byClassOptions);
        byClassChart.render();
    }

    // By Instructor Chart
    if (document.querySelector("#by-instructor-chart") && byInstructorData.length > 0) {
        const byInstructorOptions = {
            series: [{
                name: 'Completed',
                data: byInstructorData.map(i => i.completed)
            }, {
                name: 'No Show',
                data: byInstructorData.map(i => i.no_show)
            }],
            chart: {
                type: 'bar',
                height: 280,
                stacked: true,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '70%',
                }
            },
            colors: ['#10b981', '#ef4444'],
            xaxis: {
                categories: byInstructorData.map(i => i.name),
                labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
            },
            yaxis: {
                labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
            },
            legend: { position: 'top', horizontalAlign: 'right' },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            dataLabels: { enabled: false },
            tooltip: {
                custom: function({ series, seriesIndex, dataPointIndex }) {
                    const instructor = byInstructorData[dataPointIndex];
                    return '<div class="px-3 py-2 bg-base-100 shadow-lg rounded-lg border">' +
                        '<div class="font-semibold">' + instructor.name + '</div>' +
                        '<div class="text-sm">Rate: ' + instructor.rate + '%</div>' +
                        '<div class="text-sm">' + instructor.sessions + ' sessions</div>' +
                        '</div>';
                }
            }
        };

        const byInstructorChart = new ApexCharts(document.querySelector("#by-instructor-chart"), byInstructorOptions);
        byInstructorChart.render();
    }

    // By Day of Week Chart
    if (document.querySelector("#by-day-chart") && byDayData.length > 0) {
        const byDayOptions = {
            series: [{
                name: 'Completed',
                data: byDayData.map(d => d.completed)
            }, {
                name: 'No Show',
                data: byDayData.map(d => d.no_show)
            }],
            chart: {
                type: 'bar',
                height: 250,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '60%',
                }
            },
            colors: ['#10b981', '#ef4444'],
            xaxis: {
                categories: byDayData.map(d => d.day),
                labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
            },
            yaxis: {
                labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
            },
            legend: { position: 'top', horizontalAlign: 'right' },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        };

        const byDayChart = new ApexCharts(document.querySelector("#by-day-chart"), byDayOptions);
        byDayChart.render();
    }

    // By Hour Chart
    if (document.querySelector("#by-hour-chart") && byHourData.length > 0) {
        const byHourOptions = {
            series: [{
                name: 'Completed',
                data: byHourData.map(h => h.completed)
            }, {
                name: 'No Show',
                data: byHourData.map(h => h.no_show)
            }],
            chart: {
                type: 'area',
                height: 250,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            colors: ['#10b981', '#ef4444'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                }
            },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: byHourData.map(h => h.hour),
                labels: {
                    style: { colors: '#9ca3af', fontSize: '10px' },
                    rotate: -45,
                    rotateAlways: true
                }
            },
            yaxis: {
                labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
            },
            legend: { position: 'top', horizontalAlign: 'right' },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        };

        const byHourChart = new ApexCharts(document.querySelector("#by-hour-chart"), byHourOptions);
        byHourChart.render();
    }
});
</script>
@endpush
