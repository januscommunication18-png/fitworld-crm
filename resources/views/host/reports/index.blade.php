@extends('layouts.dashboard')

@section('title', $trans['nav.insights'] ?? 'Insights')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--chart-bar] me-1 size-4"></span> {{ $trans['nav.insights'] ?? 'Insights' }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['nav.insights'] ?? 'Insights' }}</h1>
            <p class="text-base-content/60 text-sm">Overview of your studio performance</p>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 rounded-lg p-2.5">
                        <span class="icon-[tabler--currency-dollar] size-6 text-success"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">${{ number_format($quickStats['revenue_mtd'], 0) }}</div>
                        <div class="text-xs text-base-content/60">Revenue MTD</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 rounded-lg p-2.5">
                        <span class="icon-[tabler--users] size-6 text-primary"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ number_format($quickStats['active_members']) }}</div>
                        <div class="text-xs text-base-content/60">Active Members</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 rounded-lg p-2.5">
                        <span class="icon-[tabler--chart-pie] size-6 text-info"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $quickStats['attendance_rate'] }}%</div>
                        <div class="text-xs text-base-content/60">Attendance Rate</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-warning/10 rounded-lg p-2.5">
                        <span class="icon-[tabler--repeat] size-6 text-warning"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">${{ number_format($quickStats['mrr'], 0) }}</div>
                        <div class="text-xs text-base-content/60">Monthly Recurring</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Attendance Report --}}
        <a href="{{ route('reports.attendance') }}" class="card bg-base-100 hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold text-lg">
                            <span class="icon-[tabler--checks] size-5 mr-2 text-primary"></span>
                            {{ $trans['nav.insights.attendance'] ?? 'Attendance' }}
                        </h3>
                        <p class="text-sm text-base-content/60 mt-1">Track show rates, no-shows, and cancellations</p>
                    </div>
                    <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                </div>
                <div class="grid grid-cols-3 gap-4 mt-4">
                    <div class="text-center">
                        <div class="text-xl font-bold text-success">{{ $metrics['attendance']['attendance_rate'] }}%</div>
                        <div class="text-xs text-base-content/50">Show Rate</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-error">{{ $metrics['attendance']['no_show_rate'] }}%</div>
                        <div class="text-xs text-base-content/50">No-Show</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-warning">{{ $metrics['attendance']['late_cancel_rate'] }}%</div>
                        <div class="text-xs text-base-content/50">Late Cancel</div>
                    </div>
                </div>
            </div>
        </a>

        {{-- Revenue Report --}}
        <a href="{{ route('reports.revenue') }}" class="card bg-base-100 hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold text-lg">
                            <span class="icon-[tabler--coin] size-5 mr-2 text-success"></span>
                            {{ $trans['nav.insights.revenue'] ?? 'Revenue' }}
                        </h3>
                        <p class="text-sm text-base-content/60 mt-1">Revenue breakdown by source and payment method</p>
                    </div>
                    <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                </div>
                <div class="grid grid-cols-3 gap-4 mt-4">
                    <div class="text-center">
                        <div class="text-xl font-bold text-success">${{ number_format($metrics['revenue']['today']['gross'], 0) }}</div>
                        <div class="text-xs text-base-content/50">Today</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-primary">${{ number_format($metrics['revenue']['mtd']['gross'], 0) }}</div>
                        <div class="text-xs text-base-content/50">This Month</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-info">${{ number_format($metrics['revenue']['ytd']['gross'], 0) }}</div>
                        <div class="text-xs text-base-content/50">This Year</div>
                    </div>
                </div>
            </div>
        </a>

        {{-- Class Performance Report --}}
        <a href="{{ route('reports.class-performance') }}" class="card bg-base-100 hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold text-lg">
                            <span class="icon-[tabler--trophy] size-5 mr-2 text-warning"></span>
                            {{ $trans['nav.insights.class_performance'] ?? 'Class Performance' }}
                        </h3>
                        <p class="text-sm text-base-content/60 mt-1">Top classes and instructor performance</p>
                    </div>
                    <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                </div>
                <div class="mt-4">
                    @if($metrics['top_class'])
                        <div class="flex items-center gap-3 p-3 bg-warning/5 rounded-lg">
                            <span class="icon-[tabler--star-filled] size-5 text-warning"></span>
                            <div>
                                <div class="font-medium">{{ $metrics['top_class']['name'] }}</div>
                                <div class="text-xs text-base-content/50">{{ $metrics['top_class']['total_bookings'] }} bookings</div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-base-content/40">No data yet</div>
                    @endif
                </div>
            </div>
        </a>

        {{-- Retention Report --}}
        <a href="{{ route('reports.retention') }}" class="card bg-base-100 hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold text-lg">
                            <span class="icon-[tabler--trending-up] size-5 mr-2 text-info"></span>
                            {{ $trans['nav.insights.retention'] ?? 'Retention' }}
                        </h3>
                        <p class="text-sm text-base-content/60 mt-1">Membership growth, churn, and MRR</p>
                    </div>
                    <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                </div>
                <div class="grid grid-cols-3 gap-4 mt-4">
                    <div class="text-center">
                        <div class="text-xl font-bold text-success">{{ $metrics['members']['active'] }}</div>
                        <div class="text-xs text-base-content/50">Active</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-primary">+{{ $metrics['members']['new_30_days'] }}</div>
                        <div class="text-xs text-base-content/50">New (30d)</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-error">{{ $metrics['members']['cancelled_30_days'] }}</div>
                        <div class="text-xs text-base-content/50">Cancelled</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
