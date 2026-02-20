@extends('layouts.settings')

@section('title', $user->full_name . ' — Team Member')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.team.users') }}">Users & Roles</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $user->full_name }}</li>
    </ol>
@endsection

@section('settings-content')
@php
    $userRole = $user->pivot->role ?? $user->role;
    $hasLogin = !is_null($user->password);
    if (!$hasLogin) {
        $statusBadge = 'badge-neutral';
        $statusText = 'No Login';
    } elseif ($user->status === 'active') {
        $statusBadge = 'badge-success';
        $statusText = 'Active';
    } elseif ($user->status === 'invited') {
        $statusBadge = 'badge-warning';
        $statusText = 'Invited';
    } elseif ($user->status === 'suspended') {
        $statusBadge = 'badge-error';
        $statusText = 'Suspended';
    } elseif ($user->status === 'deactivated') {
        $statusBadge = 'badge-neutral';
        $statusText = 'Deactivated';
    } else {
        $statusBadge = '';
        $statusText = ucfirst($user->status);
    }
@endphp
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4 relative z-50">
        <div class="flex items-start gap-4 flex-1">
            <a href="{{ route('settings.team.users') }}" class="btn btn-ghost btn-sm btn-circle mt-1">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
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
                <div class="{{ $bgColor }} w-20 h-20 rounded-full font-bold text-2xl">
                    <span>{{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}</span>
                </div>
            </div>
            <div>
                <h1 class="text-2xl font-bold">{{ $user->full_name }}</h1>
                <p class="text-base-content/60">{{ $user->email }}</p>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    @php
                        $roleBadge = match($userRole) {
                            'owner' => 'badge-primary',
                            'admin' => 'badge-secondary',
                            'staff' => 'badge-info',
                            'instructor' => 'badge-accent',
                            default => ''
                        };
                    @endphp
                    <span class="badge {{ $roleBadge }} badge-soft">{{ ucfirst($userRole) }}</span>
                    <span class="badge {{ $statusBadge }} badge-soft badge-sm">{{ $statusText }}</span>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        @if($user->id !== auth()->id() && $userRole !== 'owner')
            <div class="flex items-center gap-2">
                @if($hasLogin && $user->status === 'active')
                    <button type="button" onclick="showResetPasswordModal()" class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--key] size-4"></span>
                        Reset Password
                    </button>
                @endif

                <a href="{{ route('settings.team.users.edit', $user) }}" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--edit] size-4"></span>
                    Edit
                </a>

                @if($user->status === 'active')
                    <button type="button" onclick="showSuspendModal()" class="btn btn-warning btn-sm">
                        <span class="icon-[tabler--ban] size-4"></span>
                        Suspend
                    </button>
                @elseif($user->status === 'suspended' || $user->status === 'deactivated')
                    <button type="button" onclick="showReactivateModal()" class="btn btn-success btn-sm">
                        <span class="icon-[tabler--user-check] size-4"></span>
                        Reactivate
                    </button>
                @endif
            </div>
        @endif
    </div>

    {{-- Instructor Link Alert --}}
    @if($userRole === 'instructor' && $instructor)
        <div class="alert alert-soft alert-info">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <div class="flex-1">
                <p>This team member has an instructor profile with employment details, schedule, and class assignments.</p>
            </div>
            <a href="{{ route('instructors.show', $instructor) }}" class="btn btn-info btn-sm">
                <span class="icon-[tabler--user-star] size-4"></span>
                View Instructor Profile
            </a>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="tabs tabs-bordered relative z-10" role="tablist">
        <button class="tab {{ $tab === 'overview' ? 'tab-active' : '' }}" data-tab="overview" role="tab">
            <span class="icon-[tabler--user] size-4 mr-2"></span>
            Overview
        </button>
        <button class="tab {{ $tab === 'notes' ? 'tab-active' : '' }}" data-tab="notes" role="tab">
            <span class="icon-[tabler--notes] size-4 mr-2"></span>
            Notes
            @if($user->notes->count() > 0)
                <span class="badge badge-sm badge-primary ml-2">{{ $user->notes->count() }}</span>
            @endif
        </button>
        <button class="tab {{ $tab === 'billing' ? 'tab-active' : '' }}" data-tab="billing" role="tab">
            <span class="icon-[tabler--wallet] size-4 mr-2"></span>
            Billing
        </button>
    </div>

    {{-- Tab Contents --}}
    <div class="tab-contents relative z-0">
        {{-- Overview Tab --}}
        <div class="tab-content {{ $tab === 'overview' ? 'active' : 'hidden' }}" data-content="overview">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Profile Info --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--user] size-5"></span>
                                Profile Information
                            </h2>
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="text-sm text-base-content/60">First Name</label>
                                    <p class="font-medium">{{ $user->first_name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Last Name</label>
                                    <p class="font-medium">{{ $user->last_name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Email</label>
                                    <p class="font-medium">{{ $user->email }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Phone</label>
                                    <p class="font-medium">{{ $user->phone ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Role</label>
                                    <p class="font-medium">{{ ucfirst($userRole) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Status</label>
                                    <p class="font-medium">{{ $statusText }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- About --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <div class="flex items-center justify-between">
                                <h2 class="card-title text-lg">
                                    <span class="icon-[tabler--info-circle] size-5"></span>
                                    About
                                </h2>
                                @if($user->id !== auth()->id() && $userRole !== 'owner' || $user->id === auth()->id())
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="openDrawer('edit-bio-drawer')">
                                        <span class="icon-[tabler--edit] size-4"></span>
                                        Edit
                                    </button>
                                @endif
                            </div>
                            <div class="mt-4">
                                @if($user->bio)
                                    <p class="text-base-content/80 whitespace-pre-wrap">{{ $user->bio }}</p>
                                @else
                                    <p class="text-base-content/50 italic">No bio added yet.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Social Links --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <div class="flex items-center justify-between">
                                <h2 class="card-title text-lg">
                                    <span class="icon-[tabler--share] size-5"></span>
                                    Social Links
                                </h2>
                                @if($user->id !== auth()->id() && $userRole !== 'owner' || $user->id === auth()->id())
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="openDrawer('edit-social-drawer')">
                                        <span class="icon-[tabler--edit] size-4"></span>
                                        Edit
                                    </button>
                                @endif
                            </div>
                            <div class="space-y-3 mt-4">
                                @php
                                    $socialLinks = $user->social_links ?? [];
                                @endphp
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--brand-instagram] size-5 text-pink-500"></span>
                                    @if(!empty($socialLinks['instagram']))
                                        <a href="{{ $socialLinks['instagram'] }}" target="_blank" class="text-sm link link-primary">{{ $socialLinks['instagram'] }}</a>
                                    @else
                                        <span class="text-sm text-base-content/50">Not connected</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--brand-facebook] size-5 text-blue-600"></span>
                                    @if(!empty($socialLinks['facebook']))
                                        <a href="{{ $socialLinks['facebook'] }}" target="_blank" class="text-sm link link-primary">{{ $socialLinks['facebook'] }}</a>
                                    @else
                                        <span class="text-sm text-base-content/50">Not connected</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--brand-x] size-5 text-base-content"></span>
                                    @if(!empty($socialLinks['twitter']))
                                        <a href="{{ $socialLinks['twitter'] }}" target="_blank" class="text-sm link link-primary">{{ $socialLinks['twitter'] }}</a>
                                    @else
                                        <span class="text-sm text-base-content/50">Not connected</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--brand-linkedin] size-5 text-blue-700"></span>
                                    @if(!empty($socialLinks['linkedin']))
                                        <a href="{{ $socialLinks['linkedin'] }}" target="_blank" class="text-sm link link-primary">{{ $socialLinks['linkedin'] }}</a>
                                    @else
                                        <span class="text-sm text-base-content/50">Not connected</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--world] size-5 text-base-content/70"></span>
                                    @if(!empty($socialLinks['website']))
                                        <a href="{{ $socialLinks['website'] }}" target="_blank" class="text-sm link link-primary">{{ $socialLinks['website'] }}</a>
                                    @else
                                        <span class="text-sm text-base-content/50">Not connected</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Permissions --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <div class="flex items-center justify-between">
                                <h2 class="card-title text-lg">
                                    <span class="icon-[tabler--lock] size-5"></span>
                                    Permissions
                                </h2>
                                @if($userRole !== 'owner')
                                    <a href="{{ route('settings.team.permissions.edit', $user) }}" class="btn btn-ghost btn-sm">
                                        <span class="icon-[tabler--edit] size-4"></span>
                                        Edit Permissions
                                    </a>
                                @endif
                            </div>

                            @if($userRole === 'owner')
                                <div class="alert alert-soft alert-primary mt-4">
                                    <span class="icon-[tabler--crown] size-5"></span>
                                    <span>Owner has full access to all features including billing and danger zone.</span>
                                </div>
                            @elseif($userPermissions && count($userPermissions) > 0)
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-4">
                                    @foreach($userPermissions as $permission)
                                        <div class="flex items-center gap-2 p-2 bg-base-200/50 rounded">
                                            <span class="icon-[tabler--check] size-4 text-success"></span>
                                            <span class="text-sm">{{ $allPermissions[$permission] ?? $permission }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-base-content/60 mt-4">No specific permissions assigned. Using default permissions for {{ ucfirst($userRole) }} role.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Account Info --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--shield] size-5"></span>
                                Account Info
                            </h2>
                            <div class="space-y-4 mt-4">
                                <div>
                                    <label class="text-sm text-base-content/60">Login Access</label>
                                    <p class="font-medium">
                                        @if($hasLogin)
                                            <span class="text-success">Yes</span>
                                        @else
                                            <span class="text-base-content/50">No</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Email Verified</label>
                                    <p class="font-medium">
                                        @if($user->email_verified_at)
                                            <span class="text-success">{{ $user->email_verified_at->format('M d, Y') }}</span>
                                        @else
                                            <span class="text-base-content/50">Not verified</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Last Login</label>
                                    <p class="font-medium">
                                        @if($user->last_login_at)
                                            {{ $user->last_login_at->format('M d, Y g:i A') }}
                                            <span class="text-sm text-base-content/60 block">{{ $user->last_login_at->diffForHumans() }}</span>
                                        @else
                                            <span class="text-base-content/50">Never</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Member Since</label>
                                    <p class="font-medium">{{ $user->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    @if($user->id !== auth()->id() && $userRole !== 'owner')
                        <div class="card bg-base-100">
                            <div class="card-body">
                                <h2 class="card-title text-lg">
                                    <span class="icon-[tabler--bolt] size-5"></span>
                                    Quick Actions
                                </h2>
                                <div class="space-y-2 mt-4">
                                    @if(!$hasLogin && $user->email && !str_contains($user->email, '@nologin.local'))
                                        <form action="{{ route('settings.team.users.send-invite', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-soft btn-sm w-full justify-start">
                                                <span class="icon-[tabler--mail] size-4"></span>
                                                Send Login Invitation
                                            </button>
                                        </form>
                                    @endif

                                    @if($userRole === 'instructor' && $instructor)
                                        <a href="{{ route('instructors.show', $instructor) }}" class="btn btn-soft btn-sm w-full justify-start">
                                            <span class="icon-[tabler--user-star] size-4"></span>
                                            View Instructor Profile
                                        </a>
                                    @endif

                                    <div class="divider my-2"></div>

                                    <button type="button" onclick="showRemoveModal()" class="btn btn-soft btn-error btn-sm w-full justify-start">
                                        <span class="icon-[tabler--trash] size-4"></span>
                                        Remove from Team
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Notes Tab --}}
        <div class="tab-content {{ $tab === 'notes' ? 'active' : 'hidden' }}" data-content="notes">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Add Note Form --}}
                <div class="lg:col-span-1">
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--plus] size-5"></span>
                                Add Note
                            </h2>
                            <form id="addNoteForm" class="space-y-4 mt-4">
                                <div>
                                    <label class="label-text" for="note_type">Note Type</label>
                                    <select id="note_type" name="note_type" class="select w-full">
                                        @foreach($noteTypes as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="label-text" for="content">Content</label>
                                    <textarea id="content" name="content" rows="4" class="textarea w-full" placeholder="Enter note..."></textarea>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="is_visible_to_user" name="is_visible_to_user" class="checkbox checkbox-sm">
                                    <label for="is_visible_to_user" class="text-sm">Visible to team member</label>
                                </div>
                                <button type="submit" class="btn btn-primary w-full">
                                    <span class="icon-[tabler--plus] size-4"></span>
                                    Add Note
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Notes List --}}
                <div class="lg:col-span-2">
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--notes] size-5"></span>
                                Notes
                            </h2>
                            <div id="notesList" class="space-y-4 mt-4">
                                @forelse($user->notes as $note)
                                    <div class="border-b border-base-200 pb-4 last:border-0" data-note-id="{{ $note->id }}">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <span class="{{ \App\Models\UserNote::getNoteTypeIcon($note->note_type) }} size-4"></span>
                                                <span class="badge badge-soft badge-sm {{ \App\Models\UserNote::getNoteTypeBadgeClass($note->note_type) }}">
                                                    {{ $noteTypes[$note->note_type] ?? $note->note_type }}
                                                </span>
                                                @if($note->is_visible_to_user)
                                                    <span class="badge badge-soft badge-info badge-xs">User Visible</span>
                                                @endif
                                            </div>
                                            <details class="dropdown dropdown-end">
                                                <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                                                    <span class="icon-[tabler--dots] size-4"></span>
                                                </summary>
                                                <ul class="dropdown-content menu bg-base-100 rounded-box w-32 p-2 shadow-lg border border-base-300 z-50">
                                                    <li>
                                                        <button type="button" onclick="deleteNote({{ $note->id }})" class="text-error">
                                                            <span class="icon-[tabler--trash] size-4"></span> Delete
                                                        </button>
                                                    </li>
                                                </ul>
                                            </details>
                                        </div>
                                        <p class="mt-2">{{ $note->content }}</p>
                                        <p class="text-xs text-base-content/60 mt-2">
                                            {{ $note->author?->full_name ?? 'System' }} &bull; {{ $note->created_at->format('M d, Y g:i A') }}
                                        </p>
                                    </div>
                                @empty
                                    <p class="text-base-content/60 text-center py-8">No notes yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Billing Tab --}}
        <div class="tab-content {{ $tab === 'billing' ? 'active' : 'hidden' }}" data-content="billing">
            @if($userRole === 'instructor' && $instructor)
                {{-- For instructors, show link to instructor profile --}}
                <div class="alert alert-soft alert-info mb-6">
                    <span class="icon-[tabler--info-circle] size-5"></span>
                    <div class="flex-1">
                        <p>Billing information for instructors is managed in their instructor profile, which includes employment details, rates, and earnings.</p>
                    </div>
                    <a href="{{ route('instructors.show', ['instructor' => $instructor, 'tab' => 'billing']) }}" class="btn btn-info btn-sm">
                        <span class="icon-[tabler--wallet] size-4"></span>
                        View Instructor Billing
                    </a>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Employment Summary --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--briefcase] size-5"></span>
                            Employment
                        </h2>
                        <div class="space-y-4 mt-4">
                            <div>
                                <label class="text-sm text-base-content/60">Role</label>
                                <p class="font-medium text-lg">{{ ucfirst($userRole) }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-base-content/60">Status</label>
                                <p class="font-medium">{{ $statusText }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-base-content/60">Start Date</label>
                                <p class="font-medium">{{ $user->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Activity Summary --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--chart-bar] size-5"></span>
                            Activity
                        </h2>
                        <div class="space-y-4 mt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-base-content/60">Total Logins</span>
                                <span class="font-bold text-xl">{{ $user->last_login_at ? '—' : '0' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-base-content/60">Last Active</span>
                                <span class="font-medium">
                                    @if($user->last_login_at)
                                        {{ $user->last_login_at->diffForHumans() }}
                                    @else
                                        Never
                                    @endif
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-base-content/60">Days Active</span>
                                <span class="font-bold text-xl">{{ $user->created_at->diffInDays(now()) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Billing Notes --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--file-invoice] size-5"></span>
                            Billing Info
                        </h2>
                        <div class="mt-4">
                            @if($userRole === 'owner')
                                <p class="text-base-content/60">Owner accounts are not subject to payroll.</p>
                            @elseif($userRole === 'admin' || $userRole === 'staff')
                                <p class="text-base-content/60">Admin and staff payroll is handled outside this system.</p>
                                <div class="alert alert-soft alert-warning mt-4">
                                    <span class="icon-[tabler--info-circle] size-4"></span>
                                    <span class="text-sm">Use external payroll software for salary management.</span>
                                </div>
                            @else
                                <p class="text-base-content/60">No billing information available.</p>
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
const userId = {{ $user->id }};
const userName = '{{ addslashes($user->full_name) }}';
const userEmail = '{{ addslashes($user->email) }}';
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Tab switching
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tabs .tab');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;

            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('tab', targetTab);
            window.history.pushState({}, '', url);

            // Switch tabs
            tabs.forEach(t => t.classList.remove('tab-active'));
            this.classList.add('tab-active');

            contents.forEach(content => {
                if (content.dataset.content === targetTab) {
                    content.classList.remove('hidden');
                    content.classList.add('active');
                } else {
                    content.classList.add('hidden');
                    content.classList.remove('active');
                }
            });
        });
    });
});

// Show Reset Password Modal
function showResetPasswordModal() {
    ConfirmModals.resetPassword({
        title: 'Reset Password',
        message: `Send a password reset email to ${userName}?`,
        email: userEmail,
        action: `/settings/team/users/${userId}/reset-password`
    });
}

// Show Suspend Modal
function showSuspendModal() {
    ConfirmModals.suspend({
        title: 'Suspend User',
        message: `Are you sure you want to suspend "${userName}"? They will not be able to log in until reactivated.`,
        btnText: 'Suspend',
        action: `/settings/team/users/${userId}/suspend`
    });
}

// Show Deactivate Modal
function showDeactivateModal() {
    ConfirmModals.deactivate({
        title: 'Deactivate User',
        message: `Are you sure you want to deactivate "${userName}"? They will not be able to log in until reactivated.`,
        btnText: 'Deactivate',
        action: `/settings/team/users/${userId}/deactivate`
    });
}

// Show Reactivate Modal
function showReactivateModal() {
    ConfirmModals.activate({
        title: 'Reactivate User',
        message: `Are you sure you want to reactivate "${userName}"?`,
        btnText: 'Reactivate',
        action: `/settings/team/users/${userId}/reactivate`
    });
}

// Show Remove Modal
function showRemoveModal() {
    ConfirmModals.delete({
        title: 'Remove Team Member',
        message: `Are you sure you want to remove "${userName}" from the team? This action cannot be undone.`,
        action: `/settings/team/users/${userId}`
    });
}

// Add Note
document.getElementById('addNoteForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = {
        note_type: document.getElementById('note_type').value,
        content: document.getElementById('content').value,
        is_visible_to_user: document.getElementById('is_visible_to_user').checked
    };

    if (!formData.content.trim()) {
        alert('Please enter note content.');
        return;
    }

    fetch(`/settings/team/users/${userId}/notes`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});

// Delete Note
function deleteNote(noteId) {
    showConfirmModal({
        title: 'Delete Note',
        message: 'Are you sure you want to delete this note? This action cannot be undone.',
        type: 'danger',
        btnText: 'Delete',
        btnIcon: 'icon-[tabler--trash]',
        onConfirm: function() {
            fetch(`/settings/team/user-notes/${noteId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-note-id="${noteId}"]`)?.remove();
                    showToast('Note deleted successfully.', 'success');
                } else {
                    showToast(data.message || 'An error occurred', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
            });
        }
    });
}

// Drawer functions
function openDrawer(drawerId) {
    const drawer = document.getElementById(drawerId);
    const backdrop = document.getElementById(drawerId + '-backdrop');
    if (drawer) {
        drawer.classList.remove('translate-x-full');
        backdrop?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeDrawer(drawerId) {
    const drawer = document.getElementById(drawerId);
    const backdrop = document.getElementById(drawerId + '-backdrop');
    if (drawer) {
        drawer.classList.add('translate-x-full');
        backdrop?.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function saveBio() {
    const bio = document.getElementById('user-bio').value;
    const btn = document.getElementById('save-bio-btn');
    const spinner = btn.querySelector('.loading');

    btn.disabled = true;
    spinner.classList.remove('hidden');

    fetch(`/settings/team/users/${userId}/profile`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ bio: bio })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        spinner.classList.add('hidden');

        if (data.success) {
            closeDrawer('edit-bio-drawer');
            window.location.reload();
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(error => {
        btn.disabled = false;
        spinner.classList.add('hidden');
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function saveSocialLinks() {
    const socialLinks = {
        instagram: document.getElementById('social-instagram').value,
        facebook: document.getElementById('social-facebook').value,
        twitter: document.getElementById('social-twitter').value,
        linkedin: document.getElementById('social-linkedin').value,
        website: document.getElementById('social-website').value
    };

    const btn = document.getElementById('save-social-btn');
    const spinner = btn.querySelector('.loading');

    btn.disabled = true;
    spinner.classList.remove('hidden');

    fetch(`/settings/team/users/${userId}/profile`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ social_links: socialLinks })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        spinner.classList.add('hidden');

        if (data.success) {
            closeDrawer('edit-social-drawer');
            window.location.reload();
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(error => {
        btn.disabled = false;
        spinner.classList.add('hidden');
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

// Close drawers on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDrawer('edit-bio-drawer');
        closeDrawer('edit-social-drawer');
    }
});
</script>
@endpush

{{-- Edit Bio Drawer --}}
<div id="edit-bio-drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="closeDrawer('edit-bio-drawer')"></div>
<div id="edit-bio-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold flex items-center gap-2">
            <span class="icon-[tabler--info-circle] size-5 text-primary"></span>
            Edit About
        </h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-bio-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto p-4">
        <p class="text-sm text-base-content/60 mb-4">Add a bio or description for this team member</p>
        <div>
            <label class="label" for="user-bio">
                <span class="label-text font-medium">Bio</span>
            </label>
            <textarea id="user-bio" class="textarea textarea-bordered w-full h-40" placeholder="Tell us about yourself...">{{ $user->bio }}</textarea>
            <p class="text-xs text-base-content/50 mt-1">This will be visible on the team profile</p>
        </div>
    </div>
    <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
        <button type="button" id="save-bio-btn" class="btn btn-primary gap-2" onclick="saveBio()">
            <span class="loading loading-spinner loading-xs hidden"></span>
            Save Changes
        </button>
        <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-bio-drawer')">Cancel</button>
    </div>
</div>

{{-- Edit Social Links Drawer --}}
<div id="edit-social-drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="closeDrawer('edit-social-drawer')"></div>
<div id="edit-social-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold flex items-center gap-2">
            <span class="icon-[tabler--share] size-5 text-primary"></span>
            Edit Social Links
        </h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-social-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto p-4">
        <p class="text-sm text-base-content/60 mb-4">Connect your social media profiles</p>

        @php
            $socialLinks = $user->social_links ?? [];
        @endphp

        <div class="space-y-4">
            <div>
                <label class="label-text flex items-center gap-2 mb-1" for="social-instagram">
                    <span class="icon-[tabler--brand-instagram] size-4 text-pink-500"></span> Instagram
                </label>
                <input id="social-instagram" type="url" class="input input-bordered w-full" value="{{ $socialLinks['instagram'] ?? '' }}" placeholder="https://instagram.com/username" />
            </div>
            <div>
                <label class="label-text flex items-center gap-2 mb-1" for="social-facebook">
                    <span class="icon-[tabler--brand-facebook] size-4 text-blue-600"></span> Facebook
                </label>
                <input id="social-facebook" type="url" class="input input-bordered w-full" value="{{ $socialLinks['facebook'] ?? '' }}" placeholder="https://facebook.com/username" />
            </div>
            <div>
                <label class="label-text flex items-center gap-2 mb-1" for="social-twitter">
                    <span class="icon-[tabler--brand-x] size-4"></span> X (Twitter)
                </label>
                <input id="social-twitter" type="url" class="input input-bordered w-full" value="{{ $socialLinks['twitter'] ?? '' }}" placeholder="https://x.com/username" />
            </div>
            <div>
                <label class="label-text flex items-center gap-2 mb-1" for="social-linkedin">
                    <span class="icon-[tabler--brand-linkedin] size-4 text-blue-700"></span> LinkedIn
                </label>
                <input id="social-linkedin" type="url" class="input input-bordered w-full" value="{{ $socialLinks['linkedin'] ?? '' }}" placeholder="https://linkedin.com/in/username" />
            </div>
            <div>
                <label class="label-text flex items-center gap-2 mb-1" for="social-website">
                    <span class="icon-[tabler--world] size-4 text-base-content/70"></span> Website
                </label>
                <input id="social-website" type="url" class="input input-bordered w-full" value="{{ $socialLinks['website'] ?? '' }}" placeholder="https://yourwebsite.com" />
            </div>
        </div>
    </div>
    <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
        <button type="button" id="save-social-btn" class="btn btn-primary gap-2" onclick="saveSocialLinks()">
            <span class="loading loading-spinner loading-xs hidden"></span>
            Save Changes
        </button>
        <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-social-drawer')">Cancel</button>
    </div>
</div>

@endsection
