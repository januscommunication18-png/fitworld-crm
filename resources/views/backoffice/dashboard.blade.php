@extends('backoffice.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Clients --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-base-content/60">Total Clients</p>
                        <p class="text-3xl font-bold">{{ number_format($stats['total_clients']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--building] size-6 text-primary"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Clients --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-base-content/60">Active Clients</p>
                        <p class="text-3xl font-bold text-success">{{ number_format($stats['active_clients']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-success/10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--circle-check] size-6 text-success"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- New Today --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-base-content/60">New Today</p>
                        <p class="text-3xl font-bold text-info">{{ number_format($stats['new_today']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-info/10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--plus] size-6 text-info"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Verification --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-base-content/60">Pending Verify</p>
                        <p class="text-3xl font-bold text-warning">{{ number_format($stats['pending_verify']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-warning/10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--clock] size-6 text-warning"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Second Row Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Inactive --}}
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--circle-off] size-5 text-base-content/40"></span>
                    <div>
                        <p class="text-sm text-base-content/60">Inactive</p>
                        <p class="text-xl font-semibold">{{ number_format($stats['inactive_clients']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Suspended --}}
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--ban] size-5 text-error"></span>
                    <div>
                        <p class="text-sm text-base-content/60">Suspended</p>
                        <p class="text-xl font-semibold">{{ number_format($stats['suspended_clients']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Conversion Rate --}}
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--percentage] size-5 text-primary"></span>
                    <div>
                        <p class="text-sm text-base-content/60">Verification Rate</p>
                        <p class="text-xl font-semibold">
                            {{ $stats['total_clients'] > 0 ? number_format(($stats['active_clients'] / $stats['total_clients']) * 100, 1) : 0 }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Admin Users --}}
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <span class="icon-[tabler--shield-check] size-5 text-secondary"></span>
                    <div>
                        <p class="text-sm text-base-content/60">Admin Users</p>
                        <p class="text-xl font-semibold">{{ \App\Models\AdminUser::where('status', 'active')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Signups --}}
        <div class="lg:col-span-2">
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Recent Signups</h3>
                    <a href="{{ route('backoffice.clients.index') }}" class="btn btn-sm btn-ghost">
                        View All
                        <span class="icon-[tabler--arrow-right] size-4"></span>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Studio</th>
                                    <th>Owner</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSignups as $host)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="avatar avatar-placeholder">
                                                <div class="bg-primary/10 text-primary size-10 rounded-lg text-sm font-bold">
                                                    {{ strtoupper(substr($host->studio_name ?? 'S', 0, 2)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ $host->studio_name ?? 'Unnamed Studio' }}</div>
                                                <div class="text-xs text-base-content/60">{{ $host->subdomain }}.fitcrm.app</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-sm">{{ $host->owner->first_name ?? '' }} {{ $host->owner->last_name ?? '' }}</div>
                                        <div class="text-xs text-base-content/60">{{ $host->owner->email ?? 'No email' }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'active' => 'badge-success',
                                                'inactive' => 'badge-neutral',
                                                'pending_verify' => 'badge-warning',
                                                'suspended' => 'badge-error',
                                            ];
                                        @endphp
                                        <span class="badge badge-soft {{ $statusColors[$host->status] ?? 'badge-neutral' }} badge-sm capitalize">
                                            {{ str_replace('_', ' ', $host->status ?? 'pending') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-sm">{{ $host->created_at->format('M d, Y') }}</div>
                                        <div class="text-xs text-base-content/60">{{ $host->created_at->format('h:i A') }}</div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-base-content/60 py-8">
                                        No clients registered yet
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div>
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body space-y-2">
                    <a href="{{ route('backoffice.clients.index') }}?tab=pending" class="btn btn-soft btn-primary w-full justify-start">
                        <span class="icon-[tabler--clock] size-5"></span>
                        Review Pending Clients
                        @if($stats['pending_verify'] > 0)
                            <span class="badge badge-primary badge-sm ml-auto">{{ $stats['pending_verify'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('backoffice.clients.index') }}?tab=today" class="btn btn-soft btn-info w-full justify-start">
                        <span class="icon-[tabler--calendar-plus] size-5"></span>
                        Today's Signups
                        @if($stats['new_today'] > 0)
                            <span class="badge badge-info badge-sm ml-auto">{{ $stats['new_today'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('backoffice.plans.index') }}" class="btn btn-soft btn-secondary w-full justify-start">
                        <span class="icon-[tabler--license] size-5"></span>
                        Manage Plans
                    </a>
                    <a href="{{ route('backoffice.email-templates.index') }}" class="btn btn-soft btn-neutral w-full justify-start">
                        <span class="icon-[tabler--mail] size-5"></span>
                        Email Templates
                    </a>
                    <a href="{{ route('backoffice.admin-members.index') }}" class="btn btn-soft btn-neutral w-full justify-start">
                        <span class="icon-[tabler--shield-check] size-5"></span>
                        Admin Members
                    </a>
                </div>
            </div>

            {{-- Monthly Trend --}}
            <div class="card bg-base-100 mt-6">
                <div class="card-header">
                    <h3 class="card-title">Monthly Signups</h3>
                </div>
                <div class="card-body">
                    <div class="flex items-end justify-between h-32 gap-2">
                        @foreach($monthlyTrend as $month)
                            @php
                                $maxCount = max(array_column($monthlyTrend, 'count'));
                                $height = $maxCount > 0 ? ($month['count'] / $maxCount) * 100 : 0;
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <div class="w-full bg-primary/20 rounded-t relative" style="height: {{ max($height, 5) }}%">
                                    <div class="absolute -top-5 left-1/2 -translate-x-1/2 text-xs font-medium">
                                        {{ $month['count'] }}
                                    </div>
                                </div>
                                <span class="text-xs text-base-content/60">{{ $month['month'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
