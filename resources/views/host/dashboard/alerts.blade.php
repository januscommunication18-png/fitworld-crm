@extends('layouts.dashboard')

@section('title', $trans['nav.dashboard.alerts'] ?? 'Alerts & Reminders')

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
            <span class="icon-[tabler--bell] me-1 size-4"></span> {{ $trans['nav.dashboard.alerts'] ?? 'Alerts & Reminders' }}
        </li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['nav.dashboard.alerts'] ?? 'Alerts & Reminders' }}</h1>
            <p class="text-base-content/60 text-sm">Important notifications and action items</p>
        </div>
    </div>

    {{-- Quick Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-warning/10 rounded-lg p-2.5">
                        <span class="icon-[tabler--file-invoice] size-6 text-warning"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">${{ number_format($metrics['outstanding']['total'], 0) }}</div>
                        <div class="text-xs text-base-content/60">Outstanding Invoices</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 rounded-lg p-2.5">
                        <span class="icon-[tabler--users] size-6 text-success"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $metrics['membership']['active'] }}</div>
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
                        <div class="text-2xl font-bold">{{ $metrics['attendance']['attendance_rate'] }}%</div>
                        <div class="text-xs text-base-content/60">Attendance Rate</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts List --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">
                <span class="icon-[tabler--bell] size-5 mr-2"></span>
                Active Alerts
            </h2>

            @if($alerts->isEmpty())
                <div class="text-center py-12">
                    <span class="icon-[tabler--check-circle] size-16 mx-auto text-success/50"></span>
                    <h3 class="text-lg font-semibold mt-4 text-success">All Clear!</h3>
                    <p class="text-base-content/60 mt-2">No alerts or action items at this time.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($alerts as $alert)
                        <div class="alert alert-soft alert-{{ $alert['type'] }}">
                            <span class="icon-[{{ $alert['icon'] }}] size-5"></span>
                            <div class="flex-1">
                                <strong>{{ $alert['title'] }}</strong>
                                <p class="text-sm opacity-80">{{ $alert['message'] }}</p>
                            </div>
                            @if($alert['action_url'])
                                <a href="{{ $alert['action_url'] }}" class="btn btn-sm btn-{{ $alert['type'] }}">
                                    {{ $alert['action_text'] }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Metrics Breakdown --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Financial Health --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--currency-dollar] size-5 mr-2"></span>
                    Financial Health
                </h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-base-200/50 rounded-lg">
                        <span class="text-sm">Outstanding Invoices</span>
                        <span class="font-semibold">${{ number_format($metrics['outstanding']['total'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-base-200/50 rounded-lg">
                        <span class="text-sm">Overdue Amount</span>
                        <span class="font-semibold text-error">${{ number_format($metrics['outstanding']['overdue_total'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-base-200/50 rounded-lg">
                        <span class="text-sm">Overdue Count</span>
                        <span class="font-semibold">{{ $metrics['outstanding']['overdue_count'] }} invoices</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-base-200/50 rounded-lg">
                        <span class="text-sm">Monthly Recurring Revenue</span>
                        <span class="font-semibold text-success">${{ number_format($metrics['membership']['mrr'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Membership Health --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">
                    <span class="icon-[tabler--users] size-5 mr-2"></span>
                    Membership Health
                </h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-base-200/50 rounded-lg">
                        <span class="text-sm">Active Members</span>
                        <span class="font-semibold text-success">{{ $metrics['membership']['active'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-base-200/50 rounded-lg">
                        <span class="text-sm">New (30 days)</span>
                        <span class="font-semibold text-primary">+{{ $metrics['membership']['new_30_days'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-base-200/50 rounded-lg">
                        <span class="text-sm">Paused</span>
                        <span class="font-semibold text-warning">{{ $metrics['membership']['paused'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-base-200/50 rounded-lg">
                        <span class="text-sm">Cancelled (30 days)</span>
                        <span class="font-semibold text-error">{{ $metrics['membership']['cancelled_30_days'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance Metrics --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">
                <span class="icon-[tabler--chart-bar] size-5 mr-2"></span>
                Attendance Metrics (Last 30 Days)
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-success/5 rounded-lg">
                    <div class="text-3xl font-bold text-success">{{ $metrics['attendance']['attendance_rate'] }}%</div>
                    <div class="text-sm text-base-content/60">Attendance Rate</div>
                </div>
                <div class="text-center p-4 bg-error/5 rounded-lg">
                    <div class="text-3xl font-bold text-error">{{ $metrics['attendance']['no_show_rate'] }}%</div>
                    <div class="text-sm text-base-content/60">No-Show Rate</div>
                </div>
                <div class="text-center p-4 bg-warning/5 rounded-lg">
                    <div class="text-3xl font-bold text-warning">{{ $metrics['attendance']['late_cancel_rate'] }}%</div>
                    <div class="text-sm text-base-content/60">Late Cancel Rate</div>
                </div>
                <div class="text-center p-4 bg-info/5 rounded-lg">
                    <div class="text-3xl font-bold text-info">{{ $metrics['attendance']['capacity_utilization'] }}%</div>
                    <div class="text-sm text-base-content/60">Capacity Utilization</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
