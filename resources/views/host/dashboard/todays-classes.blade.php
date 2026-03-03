@extends('layouts.dashboard')

@section('title', $trans['nav.dashboard.todays_classes'] ?? "Today's Classes")

@section('breadcrumbs')
    <ol>
        <li>
            <a href="{{ route('dashboard') }}">
                <span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}
            </a>
        </li>
        <li class="breadcrumbs-separator rtl:rotate-180">
            <span class="icon-[tabler--chevron-right]"></span>
        </li>
        <li aria-current="page">
            <span class="icon-[tabler--calendar-event] me-1 size-4"></span> {{ $trans['nav.dashboard.todays_classes'] ?? "Today's Classes" }}
        </li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['nav.dashboard.todays_classes'] ?? "Today's Classes" }}</h1>
            <p class="text-base-content/60 text-sm">{{ $date->format('l, F j, Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('schedule.calendar') }}" class="btn btn-soft btn-sm">
                <span class="icon-[tabler--calendar] size-4 mr-1"></span>
                View Calendar
            </a>
            <a href="{{ route('class-sessions.create') }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--plus] size-4 mr-1"></span>
                New Class
            </a>
        </div>
    </div>

    {{-- Summary Stats --}}
    @php
        $totalCapacity = $classes->sum('capacity');
        $totalBooked = $classes->sum('booked');
        $totalCheckedIn = $classes->sum('checked_in');
        $totalCancelled = $classes->sum('cancelled');
        $totalNoShow = $classes->sum('no_show');
        $totalWaitlisted = $classes->sum('waitlisted');
        $avgUtilization = $totalCapacity > 0 ? round(($totalBooked / $totalCapacity) * 100) : 0;
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-primary">{{ $classes->count() }}</div>
                <div class="text-sm text-base-content/60">Classes</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-info">{{ $totalBooked }}</div>
                <div class="text-sm text-base-content/60">Booked</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-success">{{ $totalCheckedIn }}</div>
                <div class="text-sm text-base-content/60">Checked In</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-warning">{{ $totalWaitlisted }}</div>
                <div class="text-sm text-base-content/60">Waitlisted</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-error">{{ $totalCancelled }}</div>
                <div class="text-sm text-base-content/60">Cancelled</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-error/70">{{ $totalNoShow }}</div>
                <div class="text-sm text-base-content/60">No Show</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold">{{ $totalCapacity }}</div>
                <div class="text-sm text-base-content/60">Capacity</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold {{ $avgUtilization >= 70 ? 'text-success' : ($avgUtilization >= 50 ? 'text-warning' : 'text-error') }}">{{ $avgUtilization }}%</div>
                <div class="text-sm text-base-content/60">Utilization</div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    @if($classes->isNotEmpty())
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Schedule Timeline --}}
        <div class="card bg-base-100 lg:col-span-2">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--clock-hour-4] size-5 mr-2"></span>
                    Today's Schedule
                </h2>
                <div id="hourly-chart" style="height: 280px;"></div>
            </div>
        </div>

        {{-- Status Breakdown --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--chart-pie] size-5 mr-2"></span>
                    Booking Status
                </h2>
                <div id="status-chart" style="height: 280px;"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Class Breakdown --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--chart-bar] size-5 mr-2"></span>
                    Class Breakdown
                </h2>
                <div id="class-breakdown-chart" style="height: 280px;"></div>
            </div>
        </div>

        {{-- Class Utilization --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--percentage] size-5 mr-2"></span>
                    Class Utilization
                </h2>
                <div id="utilization-chart" style="height: 280px;"></div>
            </div>
        </div>
    </div>

    {{-- By Instructor (if multiple instructors) --}}
    @if(count($chartData['byInstructor']['labels']) > 1)
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">
                <span class="icon-[tabler--users] size-5 mr-2"></span>
                By Instructor
            </h2>
            <div id="instructor-chart" style="height: 200px;"></div>
        </div>
    </div>
    @endif
    @endif

    {{-- Classes List --}}
    @if($classes->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--calendar-off] size-16 mx-auto text-base-content/20"></span>
                <h3 class="text-lg font-semibold mt-4">No Classes Today</h3>
                <p class="text-base-content/60 mt-2">There are no classes scheduled for today.</p>
                <a href="{{ route('class-sessions.create') }}" class="btn btn-primary mt-4">
                    <span class="icon-[tabler--plus] size-4 mr-1"></span>
                    Schedule a Class
                </a>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach($classes as $class)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            {{-- Class Info --}}
                            <div class="flex items-start gap-4">
                                <div class="bg-primary/10 rounded-lg p-3 text-center min-w-[70px]">
                                    <div class="text-lg font-bold text-primary">{{ $class['start_time']->format('g:i') }}</div>
                                    <div class="text-xs text-primary/70">{{ $class['start_time']->format('A') }}</div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">{{ $class['name'] }}</h3>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-sm text-base-content/60">
                                        <span><span class="icon-[tabler--user] size-4 mr-1"></span>{{ $class['instructor'] }}</span>
                                        <span><span class="icon-[tabler--map-pin] size-4 mr-1"></span>{{ $class['location'] }}</span>
                                        <span><span class="icon-[tabler--clock] size-4 mr-1"></span>{{ $class['start_time']->format('g:i A') }} - {{ $class['end_time']->format('g:i A') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Stats & Status --}}
                            <div class="flex flex-wrap items-center gap-4">
                                {{-- Quick Stats --}}
                                <div class="flex gap-2">
                                    <div class="tooltip" data-tip="Booked">
                                        <span class="badge badge-info badge-soft">
                                            <span class="icon-[tabler--ticket] size-3 mr-1"></span>{{ $class['booked'] }}/{{ $class['capacity'] }}
                                        </span>
                                    </div>
                                    @if($class['checked_in'] > 0)
                                    <div class="tooltip" data-tip="Checked In">
                                        <span class="badge badge-success badge-soft">
                                            <span class="icon-[tabler--check] size-3 mr-1"></span>{{ $class['checked_in'] }}
                                        </span>
                                    </div>
                                    @endif
                                    @if($class['waitlisted'] > 0)
                                    <div class="tooltip" data-tip="Waitlisted">
                                        <span class="badge badge-warning badge-soft">
                                            <span class="icon-[tabler--clock] size-3 mr-1"></span>{{ $class['waitlisted'] }}
                                        </span>
                                    </div>
                                    @endif
                                    @if($class['cancelled'] > 0)
                                    <div class="tooltip" data-tip="Cancelled">
                                        <span class="badge badge-error badge-soft">
                                            <span class="icon-[tabler--x] size-3 mr-1"></span>{{ $class['cancelled'] }}
                                        </span>
                                    </div>
                                    @endif
                                    @if($class['no_show'] > 0)
                                    <div class="tooltip" data-tip="No Show">
                                        <span class="badge badge-error badge-outline">
                                            <span class="icon-[tabler--user-off] size-3 mr-1"></span>{{ $class['no_show'] }}
                                        </span>
                                    </div>
                                    @endif
                                </div>

                                {{-- Utilization Bar --}}
                                <div class="text-center">
                                    <div class="w-24 bg-base-200 rounded-full h-2">
                                        @if($class['utilization'] >= 90)
                                            <div class="bg-error h-2 rounded-full" style="width: {{ min($class['utilization'], 100) }}%"></div>
                                        @elseif($class['utilization'] >= 70)
                                            <div class="bg-warning h-2 rounded-full" style="width: {{ $class['utilization'] }}%"></div>
                                        @else
                                            <div class="bg-success h-2 rounded-full" style="width: {{ $class['utilization'] }}%"></div>
                                        @endif
                                    </div>
                                    <div class="text-xs text-base-content/50 mt-1">{{ $class['utilization'] }}%</div>
                                </div>

                                {{-- Status Badge --}}
                                <div>
                                    @if($class['status'] === 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @elseif($class['utilization'] >= 100)
                                        <span class="badge badge-error">Full</span>
                                    @else
                                        <span class="badge badge-warning">Upcoming</span>
                                    @endif
                                </div>

                                <a href="{{ route('class-sessions.show', $class['id']) }}" class="btn btn-soft btn-sm">
                                    <span class="icon-[tabler--eye] size-4 mr-1"></span>
                                    View
                                </a>
                            </div>
                        </div>

                        {{-- Attendees --}}
                        @if($class['bookings']->isNotEmpty())
                            <div class="mt-4 pt-4 border-t border-base-content/10">
                                <h4 class="text-sm font-semibold mb-2">Attendees ({{ $class['bookings']->count() }})</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($class['bookings'] as $booking)
                                        <div class="badge badge-soft {{ $booking['checked_in_at'] ? 'badge-success' : 'badge-neutral' }}">
                                            @if($booking['checked_in_at'])
                                                <span class="icon-[tabler--check] size-3 mr-1"></span>
                                            @endif
                                            {{ $booking['client_name'] }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('head')
    @vite(['resources/js/apps/dashboard.js'])
    <script id="todays-classes-chart-data" type="application/json">@json($chartData)</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = JSON.parse(document.getElementById('todays-classes-chart-data').textContent);

    // Hourly Schedule Chart - with check-ins
    if (document.querySelector("#hourly-chart") && chartData.hourly.labels.length > 0) {
        const hourlyOptions = {
            series: [{
                name: 'Classes',
                type: 'column',
                data: chartData.hourly.classes
            }, {
                name: 'Bookings',
                type: 'line',
                data: chartData.hourly.bookings
            }, {
                name: 'Checked In',
                type: 'line',
                data: chartData.hourly.checked_in
            }],
            chart: {
                height: 280,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            colors: ['#3b82f6', '#6366f1', '#10b981'],
            stroke: {
                width: [0, 3, 3],
                curve: 'smooth'
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '50%',
                }
            },
            fill: {
                opacity: [1, 1, 1]
            },
            markers: {
                size: [0, 4, 4]
            },
            xaxis: {
                categories: chartData.hourly.labels,
                labels: {
                    style: { colors: '#9ca3af', fontSize: '10px' },
                    rotate: -45,
                    rotateAlways: true
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { colors: '#9ca3af', fontSize: '11px' }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
            },
            grid: {
                borderColor: '#e5e7eb',
                strokeDashArray: 4,
            },
            tooltip: {
                shared: true,
                intersect: false,
            },
            dataLabels: { enabled: false }
        };

        const hourlyChart = new ApexCharts(document.querySelector("#hourly-chart"), hourlyOptions);
        hourlyChart.render();
    }

    // Status Breakdown - Donut Chart
    if (document.querySelector("#status-chart")) {
        const statusData = chartData.statusBreakdown;
        const statusLabels = [];
        const statusValues = [];
        const statusColors = [];

        if (statusData.checked_in > 0) {
            statusLabels.push('Checked In');
            statusValues.push(statusData.checked_in);
            statusColors.push('#10b981');
        }
        if (statusData.confirmed > 0) {
            statusLabels.push('Confirmed');
            statusValues.push(statusData.confirmed);
            statusColors.push('#3b82f6');
        }
        if (statusData.completed > 0) {
            statusLabels.push('Completed');
            statusValues.push(statusData.completed);
            statusColors.push('#06b6d4');
        }
        if (statusData.waitlisted > 0) {
            statusLabels.push('Waitlisted');
            statusValues.push(statusData.waitlisted);
            statusColors.push('#f59e0b');
        }
        if (statusData.cancelled > 0) {
            statusLabels.push('Cancelled');
            statusValues.push(statusData.cancelled);
            statusColors.push('#ef4444');
        }
        if (statusData.no_show > 0) {
            statusLabels.push('No Show');
            statusValues.push(statusData.no_show);
            statusColors.push('#dc2626');
        }

        if (statusValues.length > 0) {
            const statusOptions = {
                series: statusValues,
                chart: {
                    type: 'donut',
                    height: 280,
                    fontFamily: 'inherit',
                },
                labels: statusLabels,
                colors: statusColors,
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                },
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
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opt) {
                        return opt.w.config.series[opt.seriesIndex];
                    }
                }
            };

            const statusChart = new ApexCharts(document.querySelector("#status-chart"), statusOptions);
            statusChart.render();
        }
    }

    // Class Breakdown - Stacked Bar Chart
    if (document.querySelector("#class-breakdown-chart") && chartData.utilization.labels.length > 0) {
        const breakdownOptions = {
            series: [{
                name: 'Checked In',
                data: chartData.utilization.checked_in
            }, {
                name: 'Booked',
                data: chartData.utilization.booked.map((b, i) => b - chartData.utilization.checked_in[i])
            }, {
                name: 'Cancelled',
                data: chartData.utilization.cancelled
            }, {
                name: 'No Show',
                data: chartData.utilization.no_show
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
            colors: ['#10b981', '#3b82f6', '#ef4444', '#dc2626'],
            xaxis: {
                categories: chartData.utilization.labels,
                labels: {
                    style: { colors: '#9ca3af', fontSize: '11px' }
                }
            },
            yaxis: {
                labels: {
                    style: { colors: '#9ca3af', fontSize: '11px' }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
            },
            grid: {
                borderColor: '#e5e7eb',
                strokeDashArray: 4,
            },
            dataLabels: { enabled: false },
            tooltip: {
                shared: true,
                intersect: false,
            }
        };

        const breakdownChart = new ApexCharts(document.querySelector("#class-breakdown-chart"), breakdownOptions);
        breakdownChart.render();
    }

    // Utilization Chart - Horizontal Bar
    if (document.querySelector("#utilization-chart") && chartData.utilization.labels.length > 0) {
        const utilizationOptions = {
            series: [{
                name: 'Utilization',
                data: chartData.utilization.values
            }],
            chart: {
                type: 'bar',
                height: 280,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '60%',
                    distributed: true,
                    dataLabels: {
                        position: 'center'
                    }
                }
            },
            colors: chartData.utilization.values.map(v => {
                if (v >= 90) return '#ef4444';
                if (v >= 70) return '#f59e0b';
                return '#10b981';
            }),
            dataLabels: {
                enabled: true,
                formatter: function(val, opt) {
                    const idx = opt.dataPointIndex;
                    return chartData.utilization.booked[idx] + '/' + chartData.utilization.capacity[idx] + ' (' + val + '%)';
                },
                style: {
                    fontSize: '12px',
                    fontWeight: 600,
                    colors: ['#fff']
                }
            },
            xaxis: {
                categories: chartData.utilization.labels,
                labels: {
                    formatter: function(val) { return val + '%'; },
                    style: { colors: '#9ca3af', fontSize: '11px' }
                },
                max: 100
            },
            yaxis: {
                labels: {
                    style: { colors: '#9ca3af', fontSize: '11px' }
                }
            },
            grid: {
                borderColor: '#e5e7eb',
                strokeDashArray: 4,
            },
            tooltip: {
                y: {
                    formatter: function(val, opt) {
                        const idx = opt.dataPointIndex;
                        return chartData.utilization.booked[idx] + ' of ' + chartData.utilization.capacity[idx] + ' spots filled';
                    }
                }
            },
            legend: { show: false }
        };

        const utilizationChart = new ApexCharts(document.querySelector("#utilization-chart"), utilizationOptions);
        utilizationChart.render();
    }

    // Instructor Chart - Donut
    if (document.querySelector("#instructor-chart") && chartData.byInstructor.labels.length > 1) {
        const instructorOptions = {
            series: chartData.byInstructor.bookings,
            chart: {
                type: 'donut',
                height: 200,
                fontFamily: 'inherit',
            },
            labels: chartData.byInstructor.labels,
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
            legend: {
                position: 'right',
                offsetY: 0,
                fontSize: '12px',
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Bookings',
                                fontSize: '12px',
                                fontWeight: 600,
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            tooltip: {
                y: {
                    formatter: function(val, opt) {
                        const idx = opt.seriesIndex;
                        return val + ' bookings (' + chartData.byInstructor.classes[idx] + ' classes)';
                    }
                }
            }
        };

        const instructorChart = new ApexCharts(document.querySelector("#instructor-chart"), instructorOptions);
        instructorChart.render();
    }
});
</script>
@endpush
