@extends('backoffice.layouts.app')

@section('title', 'Support Requests')
@section('page-title', 'Support Requests')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <p class="text-base-content/60">Manage technical support requests from onboarding users.</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 text-primary size-10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--headset] size-5"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
                        <div class="text-xs text-base-content/60">Total</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-warning/10 text-warning size-10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--clock] size-5"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['pending'] }}</div>
                        <div class="text-xs text-base-content/60">Pending</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 text-info size-10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--progress] size-5"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['in_progress'] }}</div>
                        <div class="text-xs text-base-content/60">In Progress</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 text-success size-10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--check] size-5"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['resolved'] }}</div>
                        <div class="text-xs text-base-content/60">Resolved</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-secondary/10 text-secondary size-10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--calendar-week] size-5"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['this_week'] }}</div>
                        <div class="text-xs text-base-content/60">This Week</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('backoffice.support-requests.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="label label-text text-xs">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                           class="input input-sm input-bordered w-full" placeholder="Name or email...">
                </div>
                <div class="w-36">
                    <label for="status" class="label label-text text-xs">Status</label>
                    <select id="status" name="status" class="select select-sm select-bordered w-full">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>
                <div class="w-36">
                    <label for="source" class="label label-text text-xs">Source</label>
                    <select id="source" name="source" class="select select-sm select-bordered w-full">
                        <option value="">All Sources</option>
                        <option value="onboarding" {{ request('source') === 'onboarding' ? 'selected' : '' }}>Onboarding</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <span class="icon-[tabler--filter] size-4"></span>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'source']))
                        <a href="{{ route('backoffice.support-requests.index') }}" class="btn btn-sm btn-ghost">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card bg-base-100">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Contact</th>
                            <th>Studio</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="w-24">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supportRequests as $request)
                        <tr class="hover:bg-base-200/50">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar avatar-placeholder">
                                        <div class="bg-primary/10 text-primary size-10 rounded-full text-sm font-bold">
                                            {{ strtoupper(substr($request->first_name, 0, 1) . substr($request->last_name ?? '', 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $request->first_name }} {{ $request->last_name }}</div>
                                        <a href="mailto:{{ $request->email }}" class="text-xs link link-hover text-base-content/60">{{ $request->email }}</a>
                                        @if($request->phone)
                                            <div class="text-xs text-base-content/50">{{ $request->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($request->host)
                                    <div class="font-medium text-sm">{{ $request->host->studio_name }}</div>
                                    <div class="text-xs text-base-content/50">{{ $request->host->subdomain ?? 'No subdomain' }}</div>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-ghost badge-sm capitalize">{{ $request->source }}</span>
                            </td>
                            <td>
                                @php
                                    $statusClass = match($request->status) {
                                        'pending' => 'badge-warning',
                                        'in_progress' => 'badge-info',
                                        'resolved' => 'badge-success',
                                        default => 'badge-ghost',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }} badge-sm capitalize">
                                    {{ str_replace('_', ' ', $request->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="text-sm">{{ $request->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-base-content/60">{{ $request->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="flex gap-1">
                                    <a href="{{ route('backoffice.support-requests.show', $request) }}"
                                       class="btn btn-ghost btn-xs btn-square" title="View Details">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                    </a>
                                    <form action="{{ route('backoffice.support-requests.destroy', $request) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this support request?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="Delete">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-12">
                                <div class="flex flex-col items-center gap-2 text-base-content/60">
                                    <span class="icon-[tabler--headset] size-12 opacity-30"></span>
                                    <p>No support requests yet</p>
                                    <p class="text-sm">Requests from onboarding will appear here.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($supportRequests->hasPages())
        <div class="card-body border-t border-base-content/10 py-3">
            {{ $supportRequests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
