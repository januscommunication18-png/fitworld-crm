@extends('backoffice.layouts.app')

@section('title', 'Email Logs')
@section('page-title', 'Email Logs')

@section('content')
<div class="space-y-6">
    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="text-sm text-base-content/60">Total</div>
                <div class="text-2xl font-bold">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="text-sm text-base-content/60">Sent</div>
                <div class="text-2xl font-bold text-info">{{ number_format($stats['sent']) }}</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="text-sm text-base-content/60">Delivered</div>
                <div class="text-2xl font-bold text-success">{{ number_format($stats['delivered']) }}</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="text-sm text-base-content/60">Failed</div>
                <div class="text-2xl font-bold text-error">{{ number_format($stats['failed']) }}</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body py-4">
            <form action="{{ route('backoffice.email-logs.index') }}" method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="input w-full" placeholder="Search by email or subject...">
                </div>
                <div class="w-48">
                    <select name="host_id" class="select w-full">
                        <option value="">All Clients</option>
                        @foreach($hosts as $host)
                            <option value="{{ $host->id }}" {{ request('host_id') == $host->id ? 'selected' : '' }}>
                                {{ $host->studio_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-36">
                    <select name="status" class="select w-full">
                        <option value="">All Status</option>
                        <option value="queued" {{ request('status') === 'queued' ? 'selected' : '' }}>Queued</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="bounced" {{ request('status') === 'bounced' ? 'selected' : '' }}>Bounced</option>
                    </select>
                </div>
                <div class="w-36">
                    <input type="date" name="from_date" value="{{ request('from_date') }}"
                        class="input w-full" placeholder="From date">
                </div>
                <div class="w-36">
                    <input type="date" name="to_date" value="{{ request('to_date') }}"
                        class="input w-full" placeholder="To date">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--search] size-5"></span>
                        Filter
                    </button>
                    <a href="{{ route('backoffice.email-logs.index') }}" class="btn btn-ghost">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-between">
        <div id="bulk-actions" class="hidden">
            <form action="{{ route('backoffice.email-logs.bulk-destroy') }}" method="POST" id="bulk-delete-form"
                  onsubmit="return confirm('Are you sure you want to delete the selected logs?')">
                @csrf
                @method('DELETE')
                <input type="hidden" name="ids" id="selected-ids">
                <button type="submit" class="btn btn-error btn-sm">
                    <span class="icon-[tabler--trash] size-4"></span>
                    Delete Selected (<span id="selected-count">0</span>)
                </button>
            </form>
        </div>
        <div class="ml-auto">
            <a href="{{ route('backoffice.email-logs.export', request()->all()) }}" class="btn btn-soft btn-neutral btn-sm">
                <span class="icon-[tabler--download] size-4"></span>
                Export CSV
            </a>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="card bg-base-100">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-10">
                                <input type="checkbox" class="checkbox checkbox-sm" id="select-all">
                            </th>
                            <th>Recipient</th>
                            <th>Subject</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>Sent</th>
                            <th class="w-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <input type="checkbox" class="checkbox checkbox-sm log-checkbox" value="{{ $log->id }}">
                            </td>
                            <td>
                                <div class="text-sm font-medium">{{ $log->recipient_email }}</div>
                                @if($log->recipient_name)
                                    <div class="text-xs text-base-content/60">{{ $log->recipient_name }}</div>
                                @endif
                            </td>
                            <td class="max-w-xs truncate">{{ $log->subject }}</td>
                            <td>
                                @if($log->host)
                                    <span class="text-sm">{{ $log->host->studio_name }}</span>
                                @else
                                    <span class="text-sm text-base-content/60">System</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'queued' => 'badge-neutral',
                                        'sent' => 'badge-info',
                                        'delivered' => 'badge-success',
                                        'failed' => 'badge-error',
                                        'bounced' => 'badge-warning',
                                    ];
                                @endphp
                                <span class="badge badge-soft {{ $statusColors[$log->status] ?? 'badge-neutral' }} badge-sm capitalize">
                                    {{ $log->status }}
                                </span>
                            </td>
                            <td>
                                @if($log->sent_at)
                                    <div class="text-sm">{{ $log->sent_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-base-content/60">{{ $log->sent_at->format('h:i A') }}</div>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('backoffice.email-logs.show', $log) }}"
                                       class="btn btn-ghost btn-xs btn-square" title="View">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                    </a>
                                    <form action="{{ route('backoffice.email-logs.destroy', $log) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this log?')">
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
                            <td colspan="7" class="text-center py-12">
                                <div class="flex flex-col items-center gap-2 text-base-content/60">
                                    <span class="icon-[tabler--mail-off] size-12 opacity-30"></span>
                                    <p>No email logs found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($logs->hasPages())
            <div class="border-t border-base-content/10 px-4 py-3">
                {{ $logs->appends(request()->all())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectAll = document.getElementById('select-all');
    var checkboxes = document.querySelectorAll('.log-checkbox');
    var bulkActions = document.getElementById('bulk-actions');
    var selectedCount = document.getElementById('selected-count');
    var selectedIds = document.getElementById('selected-ids');

    function updateBulkActions() {
        var checked = document.querySelectorAll('.log-checkbox:checked');
        if (checked.length > 0) {
            bulkActions.classList.remove('hidden');
            selectedCount.textContent = checked.length;
            selectedIds.value = JSON.stringify(Array.from(checked).map(function(c) { return c.value; }));
        } else {
            bulkActions.classList.add('hidden');
        }
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(function(cb) {
            cb.checked = selectAll.checked;
        });
        updateBulkActions();
    });

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateBulkActions);
    });
});
</script>
@endsection
