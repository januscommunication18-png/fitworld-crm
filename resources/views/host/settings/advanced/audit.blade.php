@extends('layouts.settings')

@section('title', 'Audit Logs — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Audit Logs</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Audit Logs</h2>
                    <p class="text-base-content/60 text-sm">Track user sessions and activity in your studio</p>
                </div>
                <form method="GET" class="flex flex-wrap gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <select name="user" class="select select-sm" onchange="this.form.submit()">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @if($tab === 'activity')
                    <select name="category" class="select select-sm" onchange="this.form.submit()">
                        <option value="">All Actions</option>
                        @foreach($actionCategories as $key => $label)
                            <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @endif
                    <select name="period" class="select select-sm" onchange="this.form.submit()">
                        <option value="7" {{ $period == 7 ? 'selected' : '' }}>Last 7 days</option>
                        <option value="30" {{ $period == 30 ? 'selected' : '' }}>Last 30 days</option>
                        <option value="60" {{ $period == 60 ? 'selected' : '' }}>Last 60 days</option>
                        <option value="90" {{ $period == 90 ? 'selected' : '' }}>Last 90 days</option>
                    </select>
                    <a href="{{ route('export.audit-logs', ['period' => $period, 'user' => $userId, 'category' => $category]) }}" class="btn btn-sm btn-outline">
                        <span class="icon-[tabler--download] size-4"></span>
                        Export CSV
                    </a>
                </form>
            </div>

            <!-- Tabs -->
            <div class="tabs tabs-bordered mb-4">
                <a href="{{ route('settings.audit') }}?tab=sessions&period={{ $period }}&user={{ $userId }}"
                   class="tab {{ $tab === 'sessions' ? 'tab-active' : '' }}">
                    <span class="icon-[tabler--user-check] size-4 me-1"></span>
                    User Sessions
                </a>
                <a href="{{ route('settings.audit') }}?tab=activity&period={{ $period }}&user={{ $userId }}&category={{ $category }}"
                   class="tab {{ $tab === 'activity' ? 'tab-active' : '' }}">
                    <span class="icon-[tabler--history] size-4 me-1"></span>
                    Activity Trail
                </a>
            </div>

            @if($tab === 'sessions')
            <!-- User Sessions Tab -->
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Login Time</th>
                            <th>Device</th>
                            <th>Browser / OS</th>
                            <th>Location</th>
                            <th>IP Address</th>
                            <th>Duration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content w-7 rounded-full text-xs">
                                            <span>{{ $session->user ? substr($session->user->name, 0, 2) : '??' }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium">{{ $session->user?->name ?? 'Unknown' }}</div>
                                        @if(isset($concurrentSessions[$session->user_id]) && $concurrentSessions[$session->user_id] > 1 && $session->is_active)
                                            <span class="badge badge-warning badge-xs">{{ $concurrentSessions[$session->user_id] }} sessions</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm">
                                <div>{{ $session->logged_in_at?->format('M j, Y') }}</div>
                                <div class="text-xs text-base-content/60">{{ $session->logged_in_at?->format('g:i A') }}</div>
                            </td>
                            <td>
                                <span class="icon-[tabler--{{ $session->device_icon }}] size-5 text-base-content/70" title="{{ ucfirst($session->device_type ?? 'desktop') }}"></span>
                            </td>
                            <td class="text-sm">
                                <div class="flex items-center gap-1">
                                    <span class="icon-[tabler--{{ $session->browser_icon }}] size-4"></span>
                                    <span>{{ $session->browser ?? 'Unknown' }}</span>
                                </div>
                                <div class="text-xs text-base-content/60">{{ $session->platform ?? 'Unknown' }}</div>
                            </td>
                            <td class="text-sm">{{ $session->location ?? 'Unknown' }}</td>
                            <td class="text-sm font-mono text-base-content/70">{{ $session->ip_address ?? '-' }}</td>
                            <td class="text-sm">{{ $session->session_duration ?? '-' }}</td>
                            <td>
                                @if($session->is_active && $session->last_activity_at && $session->last_activity_at->diffInMinutes(now()) < 5)
                                    <span class="badge badge-success badge-sm">Online</span>
                                @elseif($session->is_active)
                                    <span class="badge badge-warning badge-sm">Idle</span>
                                @else
                                    <span class="badge badge-neutral badge-sm">Offline</span>
                                    @if($session->logged_out_at)
                                        <div class="text-xs text-base-content/60 mt-1">{{ $session->logged_out_at->format('M j, g:i A') }}</div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-base-content/60">
                                <span class="icon-[tabler--user-x] size-8 mb-2 block mx-auto opacity-50"></span>
                                No login sessions found for the selected period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sessions->hasPages())
            <div class="flex justify-center mt-4">
                {{ $sessions->appends(request()->query())->links() }}
            </div>
            @endif

            @else
            <!-- Activity Trail Tab -->
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="text-sm">
                                <div>{{ $log->created_at->format('M j, Y') }}</div>
                                <div class="text-xs text-base-content/60">{{ $log->created_at->format('g:i A') }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content w-6 rounded-full text-xs">
                                            <span>{{ $log->user ? substr($log->user->name, 0, 2) : 'SY' }}</span>
                                        </div>
                                    </div>
                                    <span class="text-sm">{{ $log->user?->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($log->action_category) {
                                        'booking' => 'badge-info',
                                        'payment' => 'badge-success',
                                        'membership' => 'badge-primary',
                                        'pack' => 'badge-secondary',
                                        'client' => 'badge-accent',
                                        default => 'badge-neutral',
                                    };
                                    if ($log->is_warning) $badgeClass = 'badge-warning';
                                    if ($log->is_error) $badgeClass = 'badge-error';
                                @endphp
                                <span class="badge badge-soft badge-sm {{ $badgeClass }}">{{ $log->action_label }}</span>
                            </td>
                            <td class="text-sm">
                                @if($log->entity_url)
                                    <a href="{{ $log->entity_url }}" class="link link-primary">{{ $log->entity_label ?? 'View' }}</a>
                                @elseif($log->auditable)
                                    @php
                                        $entityLabel = match(class_basename($log->auditable_type)) {
                                            'Booking' => 'Booking #' . $log->auditable_id,
                                            'Transaction' => 'Transaction #' . $log->auditable_id,
                                            'CustomerMembership' => 'Membership #' . $log->auditable_id,
                                            'CustomerPack' => 'Pack #' . $log->auditable_id,
                                            'Client' => $log->auditable->first_name . ' ' . $log->auditable->last_name,
                                            default => class_basename($log->auditable_type) . ' #' . $log->auditable_id,
                                        };
                                    @endphp
                                    <span class="text-base-content/70">{{ $entityLabel }}</span>
                                @else
                                    <span class="text-base-content/40">—</span>
                                @endif
                            </td>
                            <td class="text-sm text-base-content/60 max-w-xs truncate">
                                @if($log->reason)
                                    {{ $log->reason }}
                                @elseif($log->context)
                                    @php
                                        $context = $log->context;
                                        $details = [];
                                        if (isset($context['amount'])) $details[] = '$' . number_format($context['amount'], 2);
                                        if (isset($context['method'])) $details[] = ucfirst($context['method']);
                                    @endphp
                                    {{ implode(' • ', $details) ?: json_encode($context) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-xs font-mono text-base-content/50">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-base-content/60">
                                <span class="icon-[tabler--history-off] size-8 mb-2 block mx-auto opacity-50"></span>
                                No activity logs found for the selected period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
            <div class="flex justify-center mt-4">
                {{ $logs->appends(request()->query())->links() }}
            </div>
            @endif
            @endif

            <!-- Info Box -->
            <div class="alert alert-info mt-6">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <div>
                    <div class="font-medium">Audit Log Retention</div>
                    <div class="text-sm">Logs older than 90 days are automatically archived to CSV and emailed to the studio owner for record keeping.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
