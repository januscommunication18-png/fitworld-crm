@extends('backoffice.layouts.app')

@section('title', 'Support Requests')
@section('page-title', 'Support Requests')

@section('content')
<div class="space-y-6">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-base-200 flex items-center justify-center">
                        <span class="icon-[tabler--messages] size-5 text-base-content/70"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
                        <p class="text-xs text-base-content/50">Total Requests</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-warning/10 border border-warning/20">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-warning/20 flex items-center justify-center">
                        <span class="icon-[tabler--clock] size-5 text-warning"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-warning">{{ $stats['pending'] }}</p>
                        <p class="text-xs text-base-content/50">Pending</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-info/10 border border-info/20">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-info/20 flex items-center justify-center">
                        <span class="icon-[tabler--loader] size-5 text-info"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-info">{{ $stats['in_progress'] }}</p>
                        <p class="text-xs text-base-content/50">In Progress</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-success/10 border border-success/20">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-success/20 flex items-center justify-center">
                        <span class="icon-[tabler--check] size-5 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-success">{{ $stats['resolved'] }}</p>
                        <p class="text-xs text-base-content/50">Resolved</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Filter --}}
    <div class="card bg-base-100">
        <div class="card-body py-4">
            <form action="{{ route('backoffice.support.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                <div class="join flex-1 max-w-md">
                    <input type="text" name="search" value="{{ $search }}"
                        class="input join-item flex-1"
                        placeholder="Search by name, email, or content...">
                    <button type="submit" class="btn btn-primary join-item">
                        <span class="icon-[tabler--search] size-5"></span>
                    </button>
                </div>
                <select name="status" class="select select-bordered" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ $currentStatus === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ $currentStatus === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ $currentStatus === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </form>
        </div>
    </div>

    {{-- Requests Table --}}
    <div class="card bg-base-100">
        <div class="card-body p-0">
            @if($supportRequests->isEmpty())
                <div class="text-center py-12">
                    <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mx-auto mb-4">
                        <span class="icon-[tabler--message-circle] size-8 text-base-content/30"></span>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">No Support Requests</h3>
                    <p class="text-base-content/60">No support requests found matching your criteria.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Studio</th>
                                <th>Contact</th>
                                <th>Request</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supportRequests as $request)
                            <tr class="hover">
                                <td class="font-mono text-sm">#{{ $request->id }}</td>
                                <td>
                                    <div>
                                        <p class="font-medium">{{ $request->host->studio_name ?? 'N/A' }}</p>
                                        <p class="text-xs text-base-content/50">ID: {{ $request->host_id }}</p>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <p class="font-medium">{{ $request->full_name }}</p>
                                        <p class="text-xs text-base-content/50">{{ $request->email }}</p>
                                        @if($request->phone)
                                            <p class="text-xs text-base-content/50">{{ $request->phone }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <p class="max-w-xs truncate text-sm">{{ Str::limit($request->note, 60) }}</p>
                                </td>
                                <td>
                                    <span class="badge {{ $request->status_badge_class }} badge-sm">
                                        {{ $request->status_label }}
                                    </span>
                                </td>
                                <td class="text-sm">
                                    <p>{{ $request->created_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-base-content/50">{{ $request->created_at->diffForHumans() }}</p>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('backoffice.support.show', $request) }}" class="btn btn-ghost btn-sm btn-circle" title="View">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                        <form action="{{ route('backoffice.support.destroy', $request) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this request?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm btn-circle text-error" title="Delete">
                                                <span class="icon-[tabler--trash] size-4"></span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($supportRequests->hasPages())
                <div class="p-4 border-t border-base-200">
                    {{ $supportRequests->withQueryString()->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
