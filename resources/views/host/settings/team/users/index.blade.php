@extends('layouts.settings')

@section('title', 'Users & Roles — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Users & Roles</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-soft alert-success">
        <span class="icon-[tabler--check] size-5"></span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-soft alert-error">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Team Members --}}
    <div class="card bg-base-100 overflow-visible">
        <div class="card-body overflow-visible">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Team Members</h2>
                    <p class="text-base-content/60 text-sm">Manage who has access to your studio dashboard</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                        <input type="text"
                            id="search-input"
                            class="input input-sm pl-9 pr-8 w-48"
                            placeholder="Search users..."
                            value="{{ $search ?? '' }}" />
                        @if($search)
                        <a href="{{ route('settings.team.users') }}" class="absolute right-2 top-1/2 -translate-y-1/2 text-base-content/50 hover:text-base-content">
                            <span class="icon-[tabler--x] size-4"></span>
                        </a>
                        @endif
                    </div>
                    <a href="{{ route('settings.team.users.invite') }}" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--plus] size-4"></span> Invite User
                    </a>
                </div>
            </div>

            <div class="overflow-visible">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Active</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr class="{{ $user->trashed() ? 'opacity-50' : '' }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        @php
                                            // Use pivot role for multi-studio support, fallback to user role
                                            $userRole = $user->pivot->role ?? $user->role;
                                            $bgColor = match($userRole) {
                                                'owner' => 'bg-primary text-primary-content',
                                                'admin' => 'bg-secondary text-secondary-content',
                                                'staff' => 'bg-info text-info-content',
                                                'instructor' => 'bg-accent text-accent-content',
                                                default => 'bg-base-300 text-base-content'
                                            };
                                        @endphp
                                        <div class="{{ $bgColor }} w-10 rounded-full">
                                            <span>{{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $user->full_name }}</div>
                                        <div class="text-sm text-base-content/60">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $userRole = $user->pivot->role ?? $user->role;
                                    $roleBadge = match($userRole) {
                                        'owner' => 'badge-primary',
                                        'admin' => 'badge-secondary',
                                        'staff' => 'badge-info',
                                        'instructor' => 'badge-accent',
                                        default => ''
                                    };
                                @endphp
                                <span class="badge {{ $roleBadge }} badge-soft badge-sm">{{ ucfirst($userRole) }}</span>
                            </td>
                            <td>
                                @php
                                    $statusBadge = match($user->status) {
                                        'active' => 'badge-success',
                                        'invited' => 'badge-warning',
                                        'suspended' => 'badge-error',
                                        'deactivated' => 'badge-neutral',
                                        default => ''
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }} badge-soft badge-sm">{{ ucfirst($user->status) }}</span>
                            </td>
                            <td class="text-sm text-base-content/60">
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->diffForHumans() }}
                                @else
                                    Never
                                @endif
                            </td>
                            <td>
                                @php $userRole = $user->pivot->role ?? $user->role; @endphp
                                @if($user->id === auth()->id())
                                    <span class="text-base-content/40 text-sm">You</span>
                                @elseif($userRole !== 'owner')
                                    <div class="relative">
                                        <details class="dropdown dropdown-bottom dropdown-end">
                                            <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                                                <span class="icon-[tabler--dots-vertical] size-4"></span>
                                            </summary>
                                            <ul class="dropdown-content menu bg-base-100 rounded-box w-44 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
                                                <li>
                                                    <a href="{{ route('settings.team.users.edit', $user) }}">
                                                        <span class="icon-[tabler--edit] size-4"></span> Edit User
                                                    </a>
                                                </li>
                                                @if($user->status === 'active')
                                                <li>
                                                    <form action="{{ route('settings.team.users.suspend', $user) }}" method="POST" class="m-0">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left flex items-center gap-2 text-warning">
                                                            <span class="icon-[tabler--ban] size-4"></span> Suspend
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('settings.team.users.deactivate', $user) }}" method="POST" class="m-0">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                                                            <span class="icon-[tabler--user-off] size-4"></span> Deactivate
                                                        </button>
                                                    </form>
                                                </li>
                                                @elseif($user->status === 'suspended' || $user->status === 'deactivated')
                                                <li>
                                                    <form action="{{ route('settings.team.users.reactivate', $user) }}" method="POST" class="m-0">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left flex items-center gap-2 text-success">
                                                            <span class="icon-[tabler--user-check] size-4"></span> Reactivate
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                                                <li>
                                                    <form action="{{ route('settings.team.users.remove', $user) }}" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to remove {{ $user->full_name }}?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                                                            <span class="icon-[tabler--trash] size-4"></span> Remove
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </details>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @if($users->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center py-8">
                                <div class="text-base-content/50">
                                    <span class="icon-[tabler--search] size-8 mb-2 block mx-auto"></span>
                                    @if($search)
                                        No users found matching "{{ $search }}"
                                    @else
                                        No team members yet
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Pagination / Results Info --}}
            @if($users->total() > 0)
            <div class="mt-4 pt-4 border-t border-base-content/10">
                @if($users->hasPages())
                    {{ $users->links() }}
                @else
                    <div class="text-sm text-base-content/60 text-center">
                        Showing <span class="font-medium text-base-content">{{ $users->total() }}</span> {{ Str::plural('result', $users->total()) }}
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Pending Invitations --}}
    @if($invitations->total() > 0)
    <div class="card bg-base-100 overflow-visible">
        <div class="card-body overflow-visible">
            <h2 class="text-lg font-semibold mb-4">Pending Invitations</h2>
            <div class="overflow-visible">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Sent</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invitations as $invitation)
                        <tr>
                            <td class="font-medium">{{ $invitation->email }}</td>
                            <td><span class="badge badge-soft badge-sm">{{ ucfirst($invitation->role) }}</span></td>
                            <td>
                                @if($invitation->isExpired())
                                    <span class="badge badge-error badge-soft badge-sm">Expired</span>
                                @else
                                    <span class="badge badge-warning badge-soft badge-sm">Pending</span>
                                @endif
                            </td>
                            <td class="text-sm text-base-content/60">
                                {{ $invitation->created_at->diffForHumans() }}
                                @if($invitation->invitedBy)
                                    by {{ $invitation->invitedBy->first_name }}
                                @endif
                            </td>
                            <td>
                                <div class="relative">
                                    <details class="dropdown dropdown-bottom dropdown-end">
                                        <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                                            <span class="icon-[tabler--dots-vertical] size-4"></span>
                                        </summary>
                                        <ul class="dropdown-content menu bg-base-100 rounded-box w-40 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
                                            <li>
                                                <form action="{{ route('settings.team.invite.resend', $invitation) }}" method="POST" class="m-0">
                                                    @csrf
                                                    <button type="submit" class="w-full text-left flex items-center gap-2">
                                                        <span class="icon-[tabler--send] size-4"></span> Resend
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('settings.team.invite.revoke', $invitation) }}" method="POST" class="m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                                                        <span class="icon-[tabler--x] size-4"></span> Revoke
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </details>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination / Results Info --}}
            @if($invitations->total() > 0)
            <div class="mt-4 pt-4 border-t border-base-content/10">
                @if($invitations->hasPages())
                    {{ $invitations->links() }}
                @else
                    <div class="text-sm text-base-content/60 text-center">
                        Showing <span class="font-medium text-base-content">{{ $invitations->total() }}</span> pending {{ Str::plural('invitation', $invitations->total()) }}
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Available Roles --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Available Roles</h2>
            <div class="space-y-3">
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium flex items-center gap-2">
                                <span class="icon-[tabler--crown] size-4 text-primary"></span>
                                Owner
                            </div>
                            <div class="text-sm text-base-content/60">Full access to all features including billing and danger zone</div>
                        </div>
                        <span class="badge badge-primary badge-soft badge-sm">{{ $roleCounts['owner'] ?? 0 }} user</span>
                    </div>
                </div>
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium flex items-center gap-2">
                                <span class="icon-[tabler--shield] size-4 text-secondary"></span>
                                Admin
                            </div>
                            <div class="text-sm text-base-content/60">Can manage scheduling, bookings, students, instructors, and team</div>
                        </div>
                        <span class="badge badge-soft badge-sm">{{ $roleCounts['admin'] ?? 0 }} users</span>
                    </div>
                </div>
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium flex items-center gap-2">
                                <span class="icon-[tabler--user] size-4 text-info"></span>
                                Staff
                            </div>
                            <div class="text-sm text-base-content/60">Can manage bookings, students, and mark attendance</div>
                        </div>
                        <span class="badge badge-soft badge-sm">{{ $roleCounts['staff'] ?? 0 }} users</span>
                    </div>
                </div>
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium flex items-center gap-2">
                                <span class="icon-[tabler--yoga] size-4 text-accent"></span>
                                Instructor
                            </div>
                            <div class="text-sm text-base-content/60">Can view own schedule and mark attendance for their classes</div>
                        </div>
                        <span class="badge badge-soft badge-sm">{{ $roleCounts['instructor'] ?? 0 }} users</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('search-input');
    var searchTimeout = null;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(function() {
            var searchValue = searchInput.value;
            var url = new URL(window.location.href);

            if (searchValue) {
                url.searchParams.set('search', searchValue);
            } else {
                url.searchParams.delete('search');
            }

            // Reset to first page when searching
            url.searchParams.delete('page');
            url.searchParams.delete('invitations_page');

            window.location.href = url.toString();
        }, 400);
    });

    // Handle Enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            clearTimeout(searchTimeout);
            var searchValue = searchInput.value;
            var url = new URL(window.location.href);

            if (searchValue) {
                url.searchParams.set('search', searchValue);
            } else {
                url.searchParams.delete('search');
            }

            url.searchParams.delete('page');
            url.searchParams.delete('invitations_page');

            window.location.href = url.toString();
        }
    });
});
</script>
@endpush
@endsection
