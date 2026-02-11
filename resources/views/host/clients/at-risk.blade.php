@extends('layouts.dashboard')

@section('title', 'At-Risk Clients')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.index') }}">Clients</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--alert-triangle] me-1 size-4"></span> At-Risk</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold">At-Risk Clients</h1>
        <p class="text-base-content/60 mt-1">Clients flagged due to inactivity or other risk factors.</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-error/10 rounded-lg p-2">
                        <span class="icon-[tabler--alert-triangle] size-6 text-error"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $clients->total() }}</p>
                        <p class="text-xs text-base-content/60">At-Risk Clients</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-warning/10 rounded-lg p-2">
                        <span class="icon-[tabler--clock] size-6 text-warning"></span>
                    </div>
                    <div>
                        @php
                            $hostId = auth()->user()->currentHost()->id ?? auth()->user()->host_id;
                            $inactiveCount = \App\Models\Client::forHost($hostId)->active()->atRisk()
                                ->where('last_visit_at', '<', now()->subDays(14))
                                ->count();
                        @endphp
                        <p class="text-2xl font-bold">{{ $inactiveCount }}</p>
                        <p class="text-xs text-base-content/60">Inactive 14+ Days</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 rounded-lg p-2">
                        <span class="icon-[tabler--calendar-off] size-6 text-info"></span>
                    </div>
                    <div>
                        @php
                            $noBookingsCount = \App\Models\Client::forHost($hostId)->active()->atRisk()
                                ->whereNull('next_booking_at')
                                ->count();
                        @endphp
                        <p class="text-2xl font-bold">{{ $noBookingsCount }}</p>
                        <p class="text-xs text-base-content/60">No Upcoming Bookings</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 rounded-lg p-2">
                        <span class="icon-[tabler--check] size-6 text-success"></span>
                    </div>
                    <div>
                        @php
                            $clearedThisWeek = \App\Models\Client::forHost($hostId)->active()
                                ->where('status', '!=', 'at_risk')
                                ->where('updated_at', '>=', now()->startOfWeek())
                                ->count();
                        @endphp
                        <p class="text-2xl font-bold">{{ $clearedThisWeek }}</p>
                        <p class="text-xs text-base-content/60">Cleared This Week</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs & Actions --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="tabs tabs-bordered">
            <a href="{{ route('clients.index') }}" class="tab">
                <span class="icon-[tabler--users] size-4 mr-2"></span>
                All Clients
            </a>
            <a href="{{ route('clients.leads') }}" class="tab">
                <span class="icon-[tabler--target] size-4 mr-2"></span>
                Leads
            </a>
            <a href="{{ route('clients.members') }}" class="tab">
                <span class="icon-[tabler--user-check] size-4 mr-2"></span>
                Members
            </a>
            <a href="{{ route('clients.at-risk') }}" class="tab tab-active">
                <span class="icon-[tabler--alert-triangle] size-4 mr-2"></span>
                At-Risk
            </a>
        </div>

        <div class="flex items-center gap-2">
            {{-- View Toggle --}}
            <div class="btn-group">
                <a href="{{ route('clients.at-risk', array_merge(request()->query(), ['view' => 'list'])) }}"
                   class="btn btn-sm {{ request('view', 'list') === 'list' ? 'btn-active' : 'btn-ghost' }}" title="List View">
                    <span class="icon-[tabler--list] size-4"></span>
                </a>
                <a href="{{ route('clients.at-risk', array_merge(request()->query(), ['view' => 'grid'])) }}"
                   class="btn btn-sm {{ request('view') === 'grid' ? 'btn-active' : 'btn-ghost' }}" title="Grid View">
                    <span class="icon-[tabler--layout-grid] size-4"></span>
                </a>
            </div>
        </div>
    </div>

    {{-- Info Alert --}}
    <div class="alert alert-soft alert-warning">
        <span class="icon-[tabler--info-circle] size-5"></span>
        <div>
            <p class="font-medium">At-Risk Detection</p>
            <p class="text-sm">Clients are automatically flagged as at-risk when they haven't visited in 14 days or have no upcoming bookings. You can configure these rules in Settings.</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <form action="{{ route('clients.at-risk') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <input type="hidden" name="view" value="{{ request('view', 'list') }}">
                <div class="flex-1 min-w-[200px]">
                    <label class="label-text" for="search">Search</label>
                    <div class="relative">
                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                        <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Name, email, or phone..."
                               class="input w-full pl-10">
                    </div>
                </div>
                <div class="w-44">
                    <label class="label-text" for="risk_reason">Risk Reason</label>
                    <select id="risk_reason" name="risk_reason" class="select w-full">
                        <option value="">All Reasons</option>
                        <option value="inactive" {{ ($filters['risk_reason'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive 14+ Days</option>
                        <option value="no_bookings" {{ ($filters['risk_reason'] ?? '') === 'no_bookings' ? 'selected' : '' }}>No Upcoming Bookings</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--filter] size-5"></span>
                    Filter
                </button>
                @if(!empty(array_filter($filters ?? [])))
                    <a href="{{ route('clients.at-risk') }}" class="btn btn-ghost">
                        <span class="icon-[tabler--x] size-4"></span>
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Content --}}
    @if($clients->isEmpty())
    <div class="card bg-base-100">
        <div class="card-body text-center py-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-success/10 mb-4">
                <span class="icon-[tabler--check] size-8 text-success"></span>
            </div>
            <h3 class="text-lg font-semibold mb-2">No At-Risk Clients</h3>
            <p class="text-base-content/60">Great! All your clients are engaged and active.</p>
        </div>
    </div>
    @else
        @if(request('view') === 'grid')
        {{-- Grid View --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($clients as $client)
            <div class="card bg-base-100 hover:shadow-lg transition-shadow border-l-4 border-error">
                <div class="card-body p-4">
                    <div class="flex items-start gap-4">
                        <div class="avatar placeholder">
                            <div class="bg-error text-error-content size-14 rounded-full font-bold text-lg">
                                {{ $client->initials }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <a href="{{ route('clients.show', $client) }}" class="font-semibold hover:text-primary transition-colors">
                                        {{ $client->full_name }}
                                    </a>
                                    <p class="text-sm text-base-content/60 truncate">{{ $client->email }}</p>
                                </div>
                            </div>

                            @php
                                $reason = 'Unknown';
                                if ($client->last_visit_at && $client->last_visit_at->diffInDays(now()) > 14) {
                                    $reason = 'Inactive ' . $client->last_visit_at->diffInDays(now()) . ' days';
                                } elseif (!$client->next_booking_at) {
                                    $reason = 'No upcoming bookings';
                                }
                            @endphp
                            <span class="badge badge-soft badge-error badge-sm mt-2">{{ $reason }}</span>

                            @if($client->tags->count() > 0)
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach($client->tags->take(3) as $tag)
                                    <span class="badge badge-xs" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="divider my-2"></div>

                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-4 text-base-content/60">
                            <span title="Last Visit">
                                <span class="icon-[tabler--calendar] size-4 inline"></span>
                                {{ $client->last_visit_at?->diffForHumans() ?? 'Never' }}
                            </span>
                            <span title="Next Booking">
                                <span class="icon-[tabler--calendar-event] size-4 inline"></span>
                                {{ $client->next_booking_at?->format('M d') ?? 'None' }}
                            </span>
                        </div>
                        <div class="flex gap-1">
                            <form method="POST" action="{{ route('clients.clear-at-risk', $client) }}">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-xs btn-square text-success" title="Clear at-risk status">
                                    <span class="icon-[tabler--check] size-4"></span>
                                </button>
                            </form>
                            <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost btn-xs btn-square" title="View">
                                <span class="icon-[tabler--eye] size-4"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- List View --}}
        <div class="card bg-base-100 overflow-visible">
            <div class="card-body p-0 overflow-visible">
                <div class="overflow-x-auto overflow-y-visible">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Risk Reason</th>
                                <th>Last Visit</th>
                                <th>Next Booking</th>
                                <th class="w-32">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clients as $client)
                            <tr class="hover:bg-base-200/50">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-error/10 text-error w-10 h-10 rounded-full">
                                                <span class="text-sm font-semibold">{{ $client->initials }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="{{ route('clients.show', $client) }}" class="font-medium hover:text-primary">
                                                {{ $client->full_name }}
                                            </a>
                                            <div class="text-sm text-base-content/60">{{ $client->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $reason = 'Unknown';
                                        if ($client->last_visit_at && $client->last_visit_at->diffInDays(now()) > 14) {
                                            $reason = 'Inactive ' . $client->last_visit_at->diffInDays(now()) . ' days';
                                        } elseif (!$client->next_booking_at) {
                                            $reason = 'No upcoming bookings';
                                        }
                                    @endphp
                                    <span class="badge badge-soft badge-error">{{ $reason }}</span>
                                </td>
                                <td>
                                    <span class="text-sm">{{ $client->last_visit_at?->diffForHumans() ?? 'Never' }}</span>
                                </td>
                                <td>
                                    <span class="text-sm text-base-content/60">{{ $client->next_booking_at?->format('M d, Y') ?? 'None' }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <form method="POST" action="{{ route('clients.clear-at-risk', $client) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-xs btn-square text-success" title="Clear at-risk status">
                                                <span class="icon-[tabler--check] size-4"></span>
                                            </button>
                                        </form>
                                        <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost btn-xs btn-square" title="View">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                        <a href="{{ route('clients.edit', $client) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">
                                            <span class="icon-[tabler--edit] size-4"></span>
                                        </a>
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

        {{-- Pagination --}}
        @if($clients->hasPages())
        <div class="flex justify-center">
            {{ $clients->links() }}
        </div>
        @endif
    @endif
</div>
@endsection
