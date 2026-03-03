@extends('layouts.dashboard')

@section('title', $trans['nav.dashboard.upcoming_bookings'] ?? 'Upcoming Bookings')

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
            <span class="icon-[tabler--book] me-1 size-4"></span> {{ $trans['nav.dashboard.upcoming_bookings'] ?? 'Upcoming Bookings' }}
        </li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['nav.dashboard.upcoming_bookings'] ?? 'Upcoming Bookings' }}</h1>
            <p class="text-base-content/60 text-sm">All confirmed bookings for future classes</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('walk-in.select') }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--plus] size-4 mr-1"></span>
                New Booking
            </a>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-primary">{{ $summary['total'] }}</div>
                <div class="text-sm text-base-content/60">Total Upcoming</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-success">{{ $summary['confirmed'] }}</div>
                <div class="text-sm text-base-content/60">Confirmed</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-warning">{{ $summary['waitlisted'] }}</div>
                <div class="text-sm text-base-content/60">Waitlisted</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-info">{{ $summary['today'] }}</div>
                <div class="text-sm text-base-content/60">Today</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold">{{ $summary['this_week'] }}</div>
                <div class="text-sm text-base-content/60">This Week</div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Bookings by Day --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--calendar-stats] size-5 mr-2"></span>
                    Next 7 Days
                </h2>
                <div id="bookings-by-day-chart" style="height: 250px;"></div>
            </div>
        </div>

        {{-- Bookings by Class --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--chart-pie] size-5 mr-2"></span>
                    By Class Type
                </h2>
                @if(count($chartData['byClass']['labels']) > 0)
                    <div id="bookings-by-class-chart" style="height: 250px;"></div>
                @else
                    <div class="flex items-center justify-center h-[250px] text-base-content/40">
                        <div class="text-center">
                            <span class="icon-[tabler--chart-pie-off] size-12 mx-auto block mb-2"></span>
                            <p>No class data available</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Bookings Table --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">
                <span class="icon-[tabler--list] size-5 mr-2"></span>
                All Upcoming Bookings
            </h2>
            @if($bookings->isEmpty())
                <div class="text-center py-12">
                    <span class="icon-[tabler--calendar-off] size-16 mx-auto text-base-content/20"></span>
                    <h3 class="text-lg font-semibold mt-4">No Upcoming Bookings</h3>
                    <p class="text-base-content/60 mt-2">There are no confirmed bookings for future classes.</p>
                    <a href="{{ route('walk-in.select') }}" class="btn btn-primary mt-4">
                        <span class="icon-[tabler--plus] size-4 mr-1"></span>
                        Create Booking
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Class</th>
                                <th>Date & Time</th>
                                <th>Instructor</th>
                                <th>Status</th>
                                <th>Booked</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                                @php
                                    $session = $booking->bookable;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="avatar placeholder">
                                                <div class="bg-primary/10 text-primary rounded-full w-10">
                                                    <span class="text-sm">{{ substr($booking->client?->first_name ?? '?', 0, 1) }}{{ substr($booking->client?->last_name ?? '?', 0, 1) }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ $booking->client?->full_name ?? 'Unknown' }}</div>
                                                <div class="text-xs text-base-content/50">{{ $booking->client?->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-medium">{{ $session?->classPlan?->name ?? $session?->title ?? 'Class' }}</span>
                                    </td>
                                    <td>
                                        @if($session?->start_time)
                                            <div class="font-medium">{{ $session->start_time->format('M j, Y') }}</div>
                                            <div class="text-xs text-base-content/50">{{ $session->start_time->format('g:i A') }}</div>
                                        @else
                                            <span class="text-base-content/50">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $session?->primaryInstructor?->name ?? 'TBA' }}</td>
                                    <td>
                                        @if($booking->status === 'confirmed')
                                            <span class="badge badge-success badge-soft badge-sm">Confirmed</span>
                                        @elseif($booking->status === 'waitlisted')
                                            <span class="badge badge-warning badge-soft badge-sm">Waitlisted</span>
                                        @else
                                            <span class="badge badge-neutral badge-soft badge-sm">{{ ucfirst($booking->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-xs text-base-content/50">
                                            {{ $booking->booked_at?->format('M j, g:i A') ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex gap-1">
                                            <a href="{{ route('clients.show', $booking->client_id) }}" class="btn btn-ghost btn-xs" title="View Client">
                                                <span class="icon-[tabler--user] size-4"></span>
                                            </a>
                                            @if($session)
                                                <a href="{{ route('class-sessions.show', $session->id) }}" class="btn btn-ghost btn-xs" title="View Class">
                                                    <span class="icon-[tabler--calendar-event] size-4"></span>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('head')
    @vite(['resources/js/apps/dashboard.js'])
    <script id="bookings-chart-data" type="application/json">@json($chartData)</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = JSON.parse(document.getElementById('bookings-chart-data').textContent);

    // Bookings by Day - Bar Chart
    if (document.querySelector("#bookings-by-day-chart")) {
        const byDayOptions = {
            series: [{
                name: 'Bookings',
                data: chartData.byDay.values
            }],
            chart: {
                type: 'bar',
                height: 250,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            colors: ['#3b82f6'],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '60%',
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '12px',
                    fontWeight: 600,
                }
            },
            xaxis: {
                categories: chartData.byDay.labels,
                labels: {
                    style: { colors: '#9ca3af', fontSize: '11px' }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
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
                custom: function({ series, seriesIndex, dataPointIndex }) {
                    const count = series[seriesIndex][dataPointIndex];
                    const fullDate = chartData.byDay.fullDates[dataPointIndex];
                    return '<div class="px-3 py-2 bg-base-100 shadow-lg rounded-lg border">' +
                        '<div class="font-semibold">' + fullDate + '</div>' +
                        '<div class="text-primary">' + count + ' booking' + (count !== 1 ? 's' : '') + '</div>' +
                        '</div>';
                }
            }
        };

        const byDayChart = new ApexCharts(document.querySelector("#bookings-by-day-chart"), byDayOptions);
        byDayChart.render();
    }

    // Bookings by Class - Donut Chart
    if (document.querySelector("#bookings-by-class-chart") && chartData.byClass.labels.length > 0) {
        const byClassOptions = {
            series: chartData.byClass.values,
            chart: {
                type: 'donut',
                height: 250,
                fontFamily: 'inherit',
            },
            labels: chartData.byClass.labels,
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
            legend: {
                position: 'right',
                offsetY: 0,
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
                enabled: false
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + ' booking' + (val !== 1 ? 's' : '');
                    }
                }
            }
        };

        const byClassChart = new ApexCharts(document.querySelector("#bookings-by-class-chart"), byClassOptions);
        byClassChart.render();
    }
});
</script>
@endpush
