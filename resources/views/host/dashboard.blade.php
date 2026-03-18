@extends('layouts.dashboard')

@section('title', $trans['page.dashboard'] ?? 'Dashboard')

@section('breadcrumbs')
    <ol>
        <li>
            <a href="{{ url('/dashboard') }}">
                <span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}
            </a>
        </li>
        <li class="breadcrumbs-separator rtl:rotate-180">
            <span class="icon-[tabler--chevron-right]"></span>
        </li>
        <li aria-current="page">
            <span class="icon-[tabler--home] me-1 size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}
        </li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['nav.dashboard'] ?? 'Dashboard' }}</h1>
            <p class="text-base-content/60 text-sm">{{ now()->format('l, F j, Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-soft btn-sm">
                <span class="icon-[tabler--chart-bar] size-4 mr-1"></span>
                {{ $trans['nav.reports'] ?? 'Reports' }}
            </a>
            <a href="{{ route('walk-in.select') }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--plus] size-4 mr-1"></span>
                {{ $trans['dashboard.quick_actions'] ?? 'New Booking' }}
            </a>
        </div>
    </div>

    {{-- Quick Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Revenue Today --}}
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 rounded-lg p-2.5">
                        <span class="icon-[tabler--currency-dollar] size-6 text-success"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">${{ number_format($quickStats['revenue_today'], 0) }}</div>
                        <div class="text-xs text-base-content/60">{{ $trans['dashboard.revenue_today'] ?? 'Revenue Today' }}</div>
                    </div>
                </div>
                <div class="mt-2 text-xs text-base-content/50">
                    MTD: ${{ number_format($quickStats['revenue_mtd'], 0) }}
                </div>
            </div>
        </div>

        {{-- Active Members --}}
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 rounded-lg p-2.5">
                        <span class="icon-[tabler--users] size-6 text-primary"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ number_format($quickStats['active_members']) }}</div>
                        <div class="text-xs text-base-content/60">{{ $trans['dashboard.active_members'] ?? 'Active Members' }}</div>
                    </div>
                </div>
                <div class="mt-2 text-xs text-base-content/50">
                    +{{ $quickStats['new_members_30d'] }} new (30 days)
                </div>
            </div>
        </div>

        {{-- Classes Today --}}
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-warning/10 rounded-lg p-2.5">
                        <span class="icon-[tabler--calendar-event] size-6 text-warning"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $quickStats['classes_today'] }}</div>
                        <div class="text-xs text-base-content/60">{{ $trans['dashboard.classes_today'] ?? 'Classes Today' }}</div>
                    </div>
                </div>
                <div class="mt-2 text-xs text-base-content/50">
                    {{ $quickStats['upcoming_classes'] }} upcoming
                </div>
            </div>
        </div>

        {{-- Attendance Rate --}}
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 rounded-lg p-2.5">
                        <span class="icon-[tabler--chart-pie] size-6 text-info"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $quickStats['attendance_rate'] }}%</div>
                        <div class="text-xs text-base-content/60">{{ $trans['dashboard.attendance_rate'] ?? 'Attendance Rate' }}</div>
                    </div>
                </div>
                <div class="mt-2 text-xs text-base-content/50">
                    Last 30 days
                </div>
            </div>
        </div>
    </div>

    {{-- Second Row: Revenue Chart + Today's Classes --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Revenue Chart --}}
        <div class="lg:col-span-2 card bg-base-100">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">{{ $trans['insights.revenue_trend'] ?? 'Revenue Trend' }}</h2>
                    <div class="flex gap-1">
                        <button class="btn btn-xs btn-soft" data-chart-period="day" onclick="updateChart('day')">7D</button>
                        <button class="btn btn-xs btn-primary" data-chart-period="month" onclick="updateChart('month')">12M</button>
                    </div>
                </div>
                <div id="revenue-chart" style="height: 280px;"></div>
            </div>
        </div>

        {{-- Outstanding Invoices + MRR --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">{{ $trans['insights.financial_overview'] ?? 'Financial Overview' }}</h2>

                <div class="space-y-4">
                    {{-- MRR --}}
                    <div class="p-3 bg-base-200/50 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-base-content/70">Monthly Recurring Revenue</div>
                            <span class="icon-[tabler--repeat] size-4 text-primary"></span>
                        </div>
                        <div class="text-2xl font-bold text-primary mt-1">
                            ${{ number_format($metrics['members']['mrr'], 0) }}
                        </div>
                    </div>

                    {{-- Outstanding --}}
                    <div class="p-3 bg-base-200/50 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-base-content/70">Outstanding Invoices</div>
                            <span class="icon-[tabler--file-invoice] size-4 text-warning"></span>
                        </div>
                        <div class="text-2xl font-bold text-warning mt-1">
                            ${{ number_format($metrics['outstanding_invoices']['total'], 0) }}
                        </div>
                        <div class="text-xs text-base-content/50 mt-1">
                            {{ $metrics['outstanding_invoices']['count'] }} invoices
                            @if($metrics['outstanding_invoices']['overdue_count'] > 0)
                                <span class="text-error">({{ $metrics['outstanding_invoices']['overdue_count'] }} overdue)</span>
                            @endif
                        </div>
                    </div>

                    {{-- YTD Revenue --}}
                    <div class="p-3 bg-base-200/50 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-base-content/70">Year-to-Date Revenue</div>
                            <span class="icon-[tabler--trending-up] size-4 text-success"></span>
                        </div>
                        <div class="text-2xl font-bold text-success mt-1">
                            ${{ number_format($metrics['revenue']['ytd']['gross'], 0) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Classes --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">{{ $trans['nav.dashboard.todays_classes'] ?? "Today's Classes" }}</h2>
                <a href="{{ route('schedule.index') }}" class="btn btn-soft btn-xs">
                    <span class="icon-[tabler--calendar] size-3 mr-1"></span>
                    {{ $trans['btn.view_schedule'] ?? 'View Schedule' }}
                </a>
            </div>

            @if($metrics['todays_classes']->isEmpty())
                <div class="text-center py-8 text-base-content/50">
                    <span class="icon-[tabler--calendar-off] size-12 mx-auto block mb-2 opacity-30"></span>
                    <p>{{ $trans['dashboard.no_classes_today'] ?? 'No classes scheduled for today' }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ $trans['field.time'] ?? 'Time' }}</th>
                                <th>{{ $trans['page.classes'] ?? 'Class' }}</th>
                                <th>{{ $trans['field.instructor'] ?? 'Instructor' }}</th>
                                <th>{{ $trans['schedule.booked'] ?? 'Booked' }}</th>
                                <th>{{ $trans['common.status'] ?? 'Status' }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($metrics['todays_classes'] as $class)
                                <tr>
                                    <td class="font-medium">{{ $class['time'] }}</td>
                                    <td>{{ $class['name'] }}</td>
                                    <td>{{ $class['instructor'] }}</td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <span>{{ $class['booked'] }}/{{ $class['capacity'] }}</span>
                                            @if($class['utilization'] >= 90)
                                                <div class="w-16 bg-error/20 rounded-full h-1.5">
                                                    <div class="bg-error h-1.5 rounded-full" style="width: {{ min($class['utilization'], 100) }}%"></div>
                                                </div>
                                            @elseif($class['utilization'] >= 70)
                                                <div class="w-16 bg-warning/20 rounded-full h-1.5">
                                                    <div class="bg-warning h-1.5 rounded-full" style="width: {{ $class['utilization'] }}%"></div>
                                                </div>
                                            @else
                                                <div class="w-16 bg-success/20 rounded-full h-1.5">
                                                    <div class="bg-success h-1.5 rounded-full" style="width: {{ $class['utilization'] }}%"></div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($class['status'] === 'completed')
                                            <span class="badge badge-success badge-soft badge-sm">Completed</span>
                                        @elseif($class['utilization'] >= 100)
                                            <span class="badge badge-error badge-soft badge-sm">Full</span>
                                        @else
                                            <span class="badge badge-warning badge-soft badge-sm">Upcoming</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('class-sessions.show', $class['id']) }}" class="btn btn-ghost btn-xs">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Upcoming Events --}}
    @if(isset($upcomingEvents) && $upcomingEvents->count() > 0)
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">{{ $trans['dashboard.upcoming_events'] ?? 'Upcoming Events' }}</h2>
                <a href="{{ route('catalog.index', ['tab' => 'events']) }}" class="btn btn-soft btn-xs">
                    <span class="icon-[tabler--calendar-event] size-3 mr-1"></span>
                    {{ $trans['btn.view_all'] ?? 'View All' }}
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ $trans['field.date'] ?? 'Date' }}</th>
                            <th>{{ $trans['field.event'] ?? 'Event' }}</th>
                            <th>{{ $trans['field.type'] ?? 'Type' }}</th>
                            <th>{{ $trans['events.attendees'] ?? 'Attendees' }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcomingEvents as $event)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $event->start_datetime->format('M j') }}</div>
                                    <div class="text-xs text-base-content/60">{{ $event->start_datetime->format('g:i A') }}</div>
                                </td>
                                <td>
                                    <div class="font-medium">{{ $event->title }}</div>
                                    @if($event->venue_name)
                                        <div class="text-xs text-base-content/60">{{ $event->venue_name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-soft badge-sm capitalize">
                                        @if($event->event_type === 'in_person')
                                            <span class="icon-[tabler--map-pin] size-3 mr-1"></span> In-Person
                                        @elseif($event->event_type === 'online')
                                            <span class="icon-[tabler--device-laptop] size-3 mr-1"></span> Online
                                        @else
                                            <span class="icon-[tabler--arrows-exchange] size-3 mr-1"></span> Hybrid
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <span>{{ $event->registered_attendees_count }}@if($event->capacity)/{{ $event->capacity }}@endif</span>
                                        @if($event->capacity)
                                            @php $utilization = ($event->registered_attendees_count / $event->capacity) * 100; @endphp
                                            @if($utilization >= 90)
                                                <div class="w-12 bg-error/20 rounded-full h-1.5">
                                                    <div class="bg-error h-1.5 rounded-full" style="width: {{ min($utilization, 100) }}%"></div>
                                                </div>
                                            @elseif($utilization >= 70)
                                                <div class="w-12 bg-warning/20 rounded-full h-1.5">
                                                    <div class="bg-warning h-1.5 rounded-full" style="width: {{ $utilization }}%"></div>
                                                </div>
                                            @else
                                                <div class="w-12 bg-success/20 rounded-full h-1.5">
                                                    <div class="bg-success h-1.5 rounded-full" style="width: {{ $utilization }}%"></div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('events.show', $event) }}" class="btn btn-ghost btn-xs">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Bottom Row: Attendance Summary + Insights --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Attendance Summary --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">{{ $trans['insights.attendance_summary'] ?? 'Attendance Summary' }}</h2>
                <p class="text-sm text-base-content/60 mb-4">Last 30 days</p>

                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 bg-success/5 rounded-lg">
                        <div class="text-3xl font-bold text-success">{{ $metrics['attendance']['attendance_rate'] }}%</div>
                        <div class="text-sm text-base-content/60">Show Rate</div>
                    </div>
                    <div class="text-center p-4 bg-error/5 rounded-lg">
                        <div class="text-3xl font-bold text-error">{{ $metrics['attendance']['no_show_rate'] }}%</div>
                        <div class="text-sm text-base-content/60">No-Show Rate</div>
                    </div>
                    <div class="text-center p-4 bg-warning/5 rounded-lg">
                        <div class="text-3xl font-bold text-warning">{{ $metrics['attendance']['late_cancel_rate'] }}%</div>
                        <div class="text-sm text-base-content/60">Late Cancel</div>
                    </div>
                    <div class="text-center p-4 bg-info/5 rounded-lg">
                        <div class="text-3xl font-bold text-info">{{ $metrics['attendance']['capacity_utilization'] }}%</div>
                        <div class="text-sm text-base-content/60">Capacity Used</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Insights --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">{{ $trans['insights.quick_insights'] ?? 'Quick Insights' }}</h2>

                <div class="space-y-3">
                    @if($metrics['members']['new_30_days'] > 0)
                        <div class="alert alert-soft alert-success">
                            <span class="icon-[tabler--user-plus] size-5"></span>
                            <div>
                                <strong>{{ $metrics['members']['new_30_days'] }} new members</strong> joined this month.
                            </div>
                        </div>
                    @endif

                    @if($metrics['outstanding_invoices']['overdue_count'] > 0)
                        <div class="alert alert-soft alert-warning">
                            <span class="icon-[tabler--alert-triangle] size-5"></span>
                            <div>
                                <strong>{{ $metrics['outstanding_invoices']['overdue_count'] }} overdue invoices</strong>
                                totaling ${{ number_format($metrics['outstanding_invoices']['overdue_total'], 0) }}.
                                <a href="{{ route('payments.transactions') }}" class="link link-primary ml-1">Review</a>
                            </div>
                        </div>
                    @endif

                    @if($metrics['attendance']['no_show_rate'] > 10)
                        <div class="alert alert-soft alert-info">
                            <span class="icon-[tabler--bulb] size-5"></span>
                            <div>
                                No-show rate is {{ $metrics['attendance']['no_show_rate'] }}%. Consider sending reminders.
                            </div>
                        </div>
                    @endif

                    @if($metrics['top_class'])
                        <div class="alert alert-soft alert-primary">
                            <span class="icon-[tabler--trophy] size-5"></span>
                            <div>
                                <strong>{{ $metrics['top_class']['name'] }}</strong> is your top class with
                                {{ $metrics['top_class']['total_bookings'] }} bookings.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
    @vite(['resources/js/apps/dashboard.js'])
    <script id="revenue-chart-data" type="application/json">@json($revenueChart)</script>
@endpush
