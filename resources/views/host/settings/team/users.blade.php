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
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Team Members</h2>
                    <p class="text-base-content/60 text-sm">Manage who has access to your studio dashboard</p>
                </div>
                <button class="btn btn-primary btn-sm" onclick="openInviteModal()">
                    <span class="icon-[tabler--plus] size-4"></span> Invite User
                </button>
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
                                        <div class="bg-{{ $user->isOwner() ? 'primary' : ($user->isAdmin() ? 'secondary' : 'base-300') }} text-{{ $user->isOwner() ? 'primary' : ($user->isAdmin() ? 'secondary' : 'base') }}-content w-10 rounded-full">
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
                                    $roleBadge = match($user->role) {
                                        'owner' => 'badge-primary',
                                        'admin' => 'badge-secondary',
                                        'staff' => 'badge-info',
                                        'instructor' => 'badge-accent',
                                        default => ''
                                    };
                                @endphp
                                <span class="badge {{ $roleBadge }} badge-soft badge-sm">{{ ucfirst($user->role) }}</span>
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
                                @if($user->id === auth()->id())
                                    <span class="text-base-content/40 text-sm">You</span>
                                @elseif(!$user->isOwner())
                                    <div class="relative">
                                        <details class="dropdown dropdown-bottom dropdown-end">
                                            <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                                                <span class="icon-[tabler--dots-vertical] size-4"></span>
                                            </summary>
                                            <ul class="dropdown-content menu bg-base-100 rounded-box w-44 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
                                                <li>
                                                    <a href="javascript:void(0)" onclick="openRoleModal({{ $user->id }}, '{{ $user->role }}', '{{ $user->full_name }}')">
                                                        <span class="icon-[tabler--edit] size-4"></span> Change Role
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
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pending Invitations --}}
    @if($invitations->count() > 0)
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
                        <span class="badge badge-primary badge-soft badge-sm">{{ $users->where('role', 'owner')->count() }} user</span>
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
                        <span class="badge badge-soft badge-sm">{{ $users->where('role', 'admin')->count() }} users</span>
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
                        <span class="badge badge-soft badge-sm">{{ $users->where('role', 'staff')->count() }} users</span>
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
                        <span class="badge badge-soft badge-sm">{{ $users->where('role', 'instructor')->count() }} users</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Invite Modal --}}
<div id="invite-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 opacity-0 pointer-events-none transition-opacity duration-200">
    <div class="card bg-base-100 w-full max-w-md mx-4 transform scale-95 transition-transform duration-200">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Invite Team Member</h3>
                <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeInviteModal()">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
            <form action="{{ route('settings.team.invite') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="label-text" for="invite-email">Email Address</label>
                        <input type="email" id="invite-email" name="email" class="input w-full" required placeholder="colleague@example.com" />
                    </div>
                    <div>
                        <label class="label-text" for="invite-role">Role</label>
                        <select id="invite-role" name="role" class="select w-full" required>
                            <option value="admin">Admin</option>
                            <option value="staff" selected>Staff</option>
                            <option value="instructor">Instructor</option>
                        </select>
                    </div>
                    <p class="text-sm text-base-content/60">
                        An invitation email will be sent with a link to join your studio. The link expires in 7 days.
                    </p>
                </div>
                <div class="flex justify-start gap-2 mt-6">
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--send] size-4"></span> Send Invitation
                    </button>
                    <button type="button" class="btn btn-ghost" onclick="closeInviteModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Role Change Modal --}}
<div id="role-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 opacity-0 pointer-events-none transition-opacity duration-200">
    <div class="card bg-base-100 w-full max-w-md mx-4 transform scale-95 transition-transform duration-200">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Change Role for <span id="role-user-name"></span></h3>
                <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeRoleModal()">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
            <form id="role-form" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="label-text" for="new-role">New Role</label>
                        <select id="new-role" name="role" class="select w-full" required>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="instructor">Instructor</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-start gap-2 mt-6">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-ghost" onclick="closeRoleModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Invite Modal
function openInviteModal() {
    var modal = document.getElementById('invite-modal');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100', 'pointer-events-auto');
    modal.querySelector('.card').classList.remove('scale-95');
    modal.querySelector('.card').classList.add('scale-100');
}

function closeInviteModal() {
    var modal = document.getElementById('invite-modal');
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100', 'pointer-events-auto');
    modal.querySelector('.card').classList.add('scale-95');
    modal.querySelector('.card').classList.remove('scale-100');
}

// Role Modal
function openRoleModal(userId, currentRole, userName) {
    document.getElementById('role-user-name').textContent = userName;
    document.getElementById('new-role').value = currentRole;
    document.getElementById('role-form').action = '/settings/team/users/' + userId + '/role';

    var modal = document.getElementById('role-modal');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100', 'pointer-events-auto');
    modal.querySelector('.card').classList.remove('scale-95');
    modal.querySelector('.card').classList.add('scale-100');
}

function closeRoleModal() {
    var modal = document.getElementById('role-modal');
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100', 'pointer-events-auto');
    modal.querySelector('.card').classList.add('scale-95');
    modal.querySelector('.card').classList.remove('scale-100');
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeInviteModal();
        closeRoleModal();
    }
});

// Close modals when clicking backdrop
document.getElementById('invite-modal').addEventListener('click', function(e) {
    if (e.target === this) closeInviteModal();
});
document.getElementById('role-modal').addEventListener('click', function(e) {
    if (e.target === this) closeRoleModal();
});
</script>
@endpush
