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
                    <button type="button" class="btn btn-soft btn-primary btn-sm" onclick="openQuickInviteModal()">
                        <span class="icon-[tabler--bolt] size-4"></span> Quick Invite
                    </button>
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
                                <a href="{{ route('settings.team.users.show', $user) }}" class="btn btn-ghost btn-xs btn-square" title="View Profile">
                                    <span class="icon-[tabler--eye] size-4"></span>
                                </a>
                            </td>
                        </tr>
                        @endforeach

                        {{-- Pending Invitations (shown as team members) --}}
                        @foreach($invitations as $invitation)
                        @php
                            // Check if a user exists with this email (for linking to profile)
                            $hostId = auth()->user()->currentHost()?->id;
                            $invitationUser = \App\Models\User::where('email', $invitation->email)
                                ->where(function($q) use ($hostId) {
                                    $q->where('host_id', $hostId)
                                      ->orWhereHas('hosts', fn($q) => $q->where('hosts.id', $hostId));
                                })
                                ->first();
                        @endphp
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
                                <div class="flex items-center gap-1">
                                    <form action="{{ route('settings.team.invite.resend', $invitation) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-primary" title="Resend Invitation">
                                            <span class="icon-[tabler--mail-forward] size-4"></span>
                                        </button>
                                    </form>
                                    @if($invitation->instructor_id && $invitation->instructor)
                                        <a href="{{ route('instructors.show', $invitation->instructor) }}" class="btn btn-ghost btn-xs btn-square" title="View Instructor Profile">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                    @elseif($invitationUser)
                                        <a href="{{ route('settings.team.users.show', $invitationUser) }}" class="btn btn-ghost btn-xs btn-square" title="View Profile">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-ghost btn-xs btn-square" title="View Invitation Details" onclick="showInvitationDetails('{{ $invitation->first_name }} {{ $invitation->last_name }}', '{{ $invitation->email }}', '{{ ucfirst($invitation->role) }}', '{{ $invitation->created_at->format('M d, Y') }}', '{{ $invitation->expires_at->format('M d, Y') }}')">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </button>
                                    @endif
                                </div>
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
                $currentPageCount = $users->count() + $invitations->count() + $instructorsWithoutLogin->count();
                $totalCount = $users->total() + $invitations->count() + $instructorsWithoutLogin->count();
            @endphp
            @if($totalCount > 0)
            <div class="mt-4 pt-4 border-t border-base-content/10">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-base-content/60">
                        Showing <span class="font-medium text-base-content">{{ $currentPageCount }}</span> of <span class="font-medium text-base-content">{{ $totalCount }}</span> {{ Str::plural('member', $totalCount) }}
                    </div>
                    @if($users->hasPages())
                        <div class="join">
                            @if($users->onFirstPage())
                                <span class="join-item btn btn-sm btn-disabled">
                                    <span class="icon-[tabler--chevron-left] size-4"></span>
                                </span>
                            @else
                                <a href="{{ $users->previousPageUrl() }}" class="join-item btn btn-sm">
                                    <span class="icon-[tabler--chevron-left] size-4"></span>
                                </a>
                            @endif

                            <span class="join-item btn btn-sm btn-disabled">
                                Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
                            </span>

                            @if($users->hasMorePages())
                                <a href="{{ $users->nextPageUrl() }}" class="join-item btn btn-sm">
                                    <span class="icon-[tabler--chevron-right] size-4"></span>
                                </a>
                            @else
                                <span class="join-item btn btn-sm btn-disabled">
                                    <span class="icon-[tabler--chevron-right] size-4"></span>
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
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

{{-- Quick Invite Modal --}}
<div id="quick-invite-modal" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50" onclick="closeQuickInviteModal()"></div>

    {{-- Modal Content --}}
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-md w-full p-6 pointer-events-auto relative">
            <button type="button" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" onclick="closeQuickInviteModal()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>

            <h3 class="font-bold text-lg flex items-center gap-2">
                <span class="icon-[tabler--bolt] size-5 text-primary"></span>
                Quick Invite
            </h3>
            <p class="text-sm text-base-content/60 mt-1">Send a quick invitation to join your team</p>

            <form id="quick-invite-form" action="{{ route('settings.team.invite') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="quick_invite" value="1" />

                <div class="form-control">
                    <label class="label" for="quick-email">
                        <span class="label-text font-medium">Email Address <span class="text-error">*</span></span>
                    </label>
                    <div class="relative">
                        <span class="icon-[tabler--mail] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                        <input type="email" id="quick-email" name="email" class="input input-bordered w-full pl-10" required placeholder="colleague@example.com" />
                    </div>
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Role <span class="text-error">*</span></span>
                    </label>
                    <div class="flex flex-col gap-2">
                        <label class="cursor-pointer flex items-center gap-3 p-3 rounded-lg border border-base-content/10 has-[:checked]:border-secondary has-[:checked]:bg-secondary/5 hover:border-secondary/30 transition-all">
                            <input type="radio" name="role" value="admin" class="radio radio-secondary radio-sm" />
                            <div class="flex-1">
                                <span class="font-medium text-sm flex items-center gap-2">
                                    <span class="icon-[tabler--shield] size-4 text-secondary"></span>
                                    Admin
                                </span>
                            </div>
                        </label>
                        <label class="cursor-pointer flex items-center gap-3 p-3 rounded-lg border border-base-content/10 has-[:checked]:border-info has-[:checked]:bg-info/5 hover:border-info/30 transition-all">
                            <input type="radio" name="role" value="staff" class="radio radio-info radio-sm" checked />
                            <div class="flex-1">
                                <span class="font-medium text-sm flex items-center gap-2">
                                    <span class="icon-[tabler--user] size-4 text-info"></span>
                                    Staff
                                </span>
                            </div>
                        </label>
                        <label class="cursor-pointer flex items-center gap-3 p-3 rounded-lg border border-base-content/10 has-[:checked]:border-accent has-[:checked]:bg-accent/5 hover:border-accent/30 transition-all">
                            <input type="radio" name="role" value="instructor" class="radio radio-accent radio-sm" />
                            <div class="flex-1">
                                <span class="font-medium text-sm flex items-center gap-2">
                                    <span class="icon-[tabler--yoga] size-4 text-accent"></span>
                                    Instructor
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <label class="cursor-pointer flex items-center gap-3 p-4 rounded-xl border-2 border-dashed border-base-content/10 hover:border-primary/30 hover:bg-primary/5 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                    <input type="checkbox" name="send_invite" value="1" class="checkbox checkbox-primary" checked />
                    <div class="flex-1">
                        <span class="font-medium flex items-center gap-2">
                            <span class="icon-[tabler--mail-forward] size-5 text-primary"></span>
                            Grant Login Access
                        </span>
                        <span class="text-sm text-base-content/60 block mt-0.5">Team member will receive an email invitation to create their account</span>
                    </div>
                </label>

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" class="btn btn-ghost" onclick="closeQuickInviteModal()">Cancel</button>
                    <button type="submit" id="quick-invite-submit-btn" class="btn btn-primary gap-2">
                        <span id="quick-invite-btn-icon" class="icon-[tabler--send] size-4"></span>
                        <span id="quick-invite-btn-text">Send Invite</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Quick Invite Modal Functions
function openQuickInviteModal() {
    document.getElementById('quick-invite-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    // Focus on email input
    setTimeout(function() {
        document.getElementById('quick-email').focus();
    }, 100);
}

function closeQuickInviteModal() {
    document.getElementById('quick-invite-modal').classList.add('hidden');
    document.body.style.overflow = '';
    // Reset form
    document.getElementById('quick-invite-form').reset();
    // Reset staff as default
    document.querySelector('input[name="role"][value="staff"]').checked = true;
    document.querySelector('input[name="send_invite"]').checked = true;
    // Reset button text
    updateQuickInviteButton(true);
}

function updateQuickInviteButton(isInvite) {
    var btnIcon = document.getElementById('quick-invite-btn-icon');
    var btnText = document.getElementById('quick-invite-btn-text');

    if (isInvite) {
        btnIcon.className = 'icon-[tabler--send] size-4';
        btnText.textContent = 'Send Invite';
    } else {
        btnIcon.className = 'icon-[tabler--user-plus] size-4';
        btnText.textContent = 'Add Team Member';
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var quickInviteModal = document.getElementById('quick-invite-modal');
        var invitationModal = document.getElementById('invitation-details-modal');
        if (!quickInviteModal.classList.contains('hidden')) {
            closeQuickInviteModal();
        }
        if (invitationModal && !invitationModal.classList.contains('hidden')) {
            closeInvitationDetailsModal();
        }
    }
});

// Show Invitation Details Modal
function showInvitationDetails(name, email, role, invitedAt, expiresAt) {
    document.getElementById('inv-detail-name').textContent = name || email;
    document.getElementById('inv-detail-email').textContent = email;
    document.getElementById('inv-detail-role').textContent = role;
    document.getElementById('inv-detail-invited').textContent = invitedAt;
    document.getElementById('inv-detail-expires').textContent = expiresAt;
    document.getElementById('invitation-details-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeInvitationDetailsModal() {
    document.getElementById('invitation-details-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', function() {
    // Grant Login Access checkbox listener
    var sendInviteCheckbox = document.querySelector('input[name="send_invite"]');
    if (sendInviteCheckbox) {
        sendInviteCheckbox.addEventListener('change', function() {
            updateQuickInviteButton(this.checked);
        });
    }

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

{{-- Invitation Details Modal --}}
<div id="invitation-details-modal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeInvitationDetailsModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-sm w-full p-6 pointer-events-auto relative">
            <button type="button" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" onclick="closeInvitationDetailsModal()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>

            <h3 class="font-bold text-lg flex items-center gap-2">
                <span class="icon-[tabler--mail] size-5 text-primary"></span>
                Invitation Details
            </h3>

            <div class="mt-4 space-y-3">
                <div>
                    <label class="text-sm text-base-content/60">Name</label>
                    <p id="inv-detail-name" class="font-medium"></p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Email</label>
                    <p id="inv-detail-email" class="font-medium"></p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Role</label>
                    <p id="inv-detail-role" class="font-medium"></p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Invited On</label>
                    <p id="inv-detail-invited" class="font-medium"></p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Expires On</label>
                    <p id="inv-detail-expires" class="font-medium"></p>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" class="btn btn-ghost" onclick="closeInvitationDetailsModal()">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
