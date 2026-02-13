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
    <div class="card bg-base-100">
        <div class="card-body">
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
                        <span class="icon-[tabler--plus] size-4"></span> Add Team Member
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Access Level</th>
                            <th>Last Active</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Existing Users --}}
                        @foreach($users as $user)
                        @php
                            $userRole = $user->pivot->role ?? $user->role;
                            $hasLogin = !is_null($user->password);
                            $isPending = $hasLogin && $user->status === 'invited';
                            $isActive = !$user->trashed() && !in_array($user->status, ['suspended', 'deactivated']);
                        @endphp
                        <tr class="{{ $user->trashed() ? 'opacity-50' : '' }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        @php
                                            $bgColor = match($userRole) {
                                                'owner' => 'bg-primary text-primary-content',
                                                'admin' => 'bg-secondary text-secondary-content',
                                                'staff' => 'bg-info text-info-content',
                                                'instructor' => 'bg-accent text-accent-content',
                                                default => 'bg-base-300 text-base-content'
                                            };
                                        @endphp
                                        <div class="{{ $bgColor }} size-10 rounded-full">
                                            <span>{{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('settings.team.users.show', $user) }}" class="font-medium hover:text-primary">{{ $user->full_name }}</a>
                                        <div class="text-sm text-base-content/60">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
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
                                @if($user->trashed())
                                    <span class="badge badge-neutral badge-soft badge-sm">Removed</span>
                                @elseif($user->status === 'suspended')
                                    <span class="badge badge-error badge-soft badge-sm">Suspended</span>
                                @elseif($user->status === 'deactivated')
                                    <span class="badge badge-neutral badge-soft badge-sm">Inactive</span>
                                @else
                                    <span class="badge badge-success badge-soft badge-sm">Active</span>
                                @endif
                            </td>
                            <td>
                                @if($hasLogin)
                                    <span class="badge badge-success badge-soft badge-sm">Granted</span>
                                    @if($isPending)
                                        <span class="badge badge-warning badge-soft badge-xs ml-1">Pending</span>
                                    @endif
                                @else
                                    <span class="badge badge-neutral badge-soft badge-sm">No Access</span>
                                @endif
                            </td>
                            <td class="text-sm text-base-content/60">
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->diffForHumans() }}
                                @else
                                    Never
                                @endif
                            </td>
                            <td>
                                @if($user->id === auth()->id())
                                    <a href="{{ route('settings.team.users.show', $user) }}" class="btn btn-ghost btn-xs btn-square" title="View Profile">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                    </a>
                                @elseif($userRole === 'owner')
                                    <a href="{{ route('settings.team.users.show', $user) }}" class="btn btn-ghost btn-xs btn-square" title="View Profile">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                    </a>
                                @else
                                    <a href="{{ route('settings.team.users.show', $user) }}" class="btn btn-ghost btn-xs btn-square" title="View Profile">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach

                        {{-- Pending Invitations (shown as team members) --}}
                        @foreach($invitations as $invitation)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        @php
                                            $bgColor = match($invitation->role) {
                                                'owner' => 'bg-primary text-primary-content',
                                                'admin' => 'bg-secondary text-secondary-content',
                                                'staff' => 'bg-info text-info-content',
                                                'instructor' => 'bg-accent text-accent-content',
                                                default => 'bg-base-300 text-base-content'
                                            };
                                            $initials = strtoupper(
                                                substr($invitation->first_name ?? $invitation->email, 0, 1) .
                                                substr($invitation->last_name ?? '', 0, 1)
                                            );
                                            if (strlen($initials) < 2) {
                                                $initials = strtoupper(substr($invitation->email, 0, 2));
                                            }
                                        @endphp
                                        <div class="{{ $bgColor }} size-10 rounded-full opacity-60">
                                            <span>{{ $initials }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium text-base-content/70">
                                            @if($invitation->first_name)
                                                {{ $invitation->first_name }} {{ $invitation->last_name }}
                                            @else
                                                {{ $invitation->email }}
                                            @endif
                                        </div>
                                        <div class="text-sm text-base-content/60">{{ $invitation->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $roleBadge = match($invitation->role) {
                                        'owner' => 'badge-primary',
                                        'admin' => 'badge-secondary',
                                        'staff' => 'badge-info',
                                        'instructor' => 'badge-accent',
                                        default => ''
                                    };
                                @endphp
                                <span class="badge {{ $roleBadge }} badge-soft badge-sm">{{ ucfirst($invitation->role) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-success badge-soft badge-sm">Active</span>
                            </td>
                            <td>
                                <span class="badge badge-success badge-soft badge-sm">Granted</span>
                                <span class="badge badge-warning badge-soft badge-xs ml-1">Pending</span>
                                @if($invitation->isExpired())
                                    <span class="badge badge-error badge-soft badge-xs ml-1">Expired</span>
                                @endif
                            </td>
                            <td class="text-sm text-base-content/60">
                                Invited {{ $invitation->created_at->diffForHumans() }}
                            </td>
                            <td>
                                @if($invitation->instructor_id && $invitation->instructor)
                                    <a href="{{ route('instructors.show', $invitation->instructor) }}" class="btn btn-ghost btn-xs btn-square" title="View Instructor Profile">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                    </a>
                                @else
                                    <span class="btn btn-ghost btn-xs btn-square opacity-30" title="Profile not available until invitation is accepted">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach

                        {{-- Instructors without login --}}
                        @foreach($instructorsWithoutLogin as $instructor)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-accent text-accent-content size-10 rounded-full">
                                            <span>{{ $instructor->initials }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('instructors.show', $instructor) }}" class="font-medium hover:text-primary">{{ $instructor->name }}</a>
                                        <div class="text-sm text-base-content/60">{{ $instructor->email ?? 'No email' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-accent badge-soft badge-sm">Instructor</span>
                            </td>
                            <td>
                                @if($instructor->is_active)
                                    <span class="badge badge-success badge-soft badge-sm">Active</span>
                                @else
                                    <span class="badge badge-neutral badge-soft badge-sm">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-neutral badge-soft badge-sm">No Access</span>
                            </td>
                            <td class="text-sm text-base-content/60">
                                —
                            </td>
                            <td>
                                <a href="{{ route('instructors.show', $instructor) }}" class="btn btn-ghost btn-xs btn-square" title="View Profile">
                                    <span class="icon-[tabler--eye] size-4"></span>
                                </a>
                            </td>
                        </tr>
                        @endforeach

                        @if($users->isEmpty() && $invitations->isEmpty() && $instructorsWithoutLogin->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center py-8">
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
            @php
                $totalCount = $users->total() + $invitations->count() + $instructorsWithoutLogin->count();
            @endphp
            @if($totalCount > 0)
            <div class="mt-4 pt-4 border-t border-base-content/10">
                @if($users->hasPages())
                    {{ $users->links() }}
                @else
                    <div class="text-sm text-base-content/60 text-center">
                        Showing <span class="font-medium text-base-content">{{ $totalCount }}</span> {{ Str::plural('member', $totalCount) }}
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>

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
                        <div class="flex items-center gap-2">
                            <span class="badge badge-soft badge-sm">{{ ($roleCounts['instructor'] ?? 0) + $instructorsWithoutLoginCount }} total</span>
                            @if($instructorsWithoutLoginCount > 0)
                                <span class="badge badge-neutral badge-soft badge-sm">{{ $instructorsWithoutLoginCount }} no access</span>
                            @endif
                        </div>
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

            window.location.href = url.toString();
        }
    });
});
</script>
@endpush
@endsection
