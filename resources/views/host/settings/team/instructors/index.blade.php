@extends('layouts.settings')

@section('title', 'Instructors — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Instructors</li>
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

    {{-- Instructors List --}}
    <div class="card bg-base-100 overflow-visible">
        <div class="card-body overflow-visible">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Instructors</h2>
                    <p class="text-base-content/60 text-sm">Manage your teaching staff and their profiles</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                        <input type="text"
                            id="search-input"
                            class="input input-sm pl-9 pr-8 w-48"
                            placeholder="Search instructors..."
                            value="{{ $search ?? '' }}" />
                        @if($search)
                        <a href="{{ route('settings.team.instructors') }}" class="absolute right-2 top-1/2 -translate-y-1/2 text-base-content/50 hover:text-base-content">
                            <span class="icon-[tabler--x] size-4"></span>
                        </a>
                        @endif
                    </div>
                    <a href="{{ route('settings.team.instructors.create') }}" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--plus] size-4"></span> Add Instructor
                    </a>
                </div>
            </div>

            <div class="overflow-visible">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Instructor</th>
                            <th>Specialties</th>
                            <th>Status</th>
                            <th>Account</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instructors as $instructor)
                        <tr class="{{ !$instructor->is_active ? 'opacity-60' : '' }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar {{ $instructor->photo_url ? '' : 'placeholder' }}">
                                        @if($instructor->photo_url)
                                        <div class="w-10 rounded-full">
                                            <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}" />
                                        </div>
                                        @else
                                        <div class="bg-accent text-accent-content w-10 rounded-full">
                                            <span>{{ strtoupper(substr($instructor->name, 0, 2)) }}</span>
                                        </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $instructor->name }}</div>
                                        @if($instructor->email)
                                        <div class="text-sm text-base-content/60">{{ $instructor->email }}</div>
                                        @else
                                        <div class="text-sm text-base-content/40 italic">No email</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($instructor->specialties && count($instructor->specialties) > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($instructor->specialties, 0, 2) as $specialty)
                                    <span class="badge badge-primary badge-soft badge-xs">{{ $specialty }}</span>
                                    @endforeach
                                    @if(count($instructor->specialties) > 2)
                                    <span class="badge badge-soft badge-xs">+{{ count($instructor->specialties) - 2 }}</span>
                                    @endif
                                </div>
                                @else
                                <span class="text-base-content/40 text-sm">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex flex-col gap-1">
                                    @if($instructor->status === 'pending' || !$instructor->isProfileComplete())
                                        <span class="badge badge-warning badge-soft badge-sm" title="Profile incomplete - cannot be assigned to classes">
                                            <span class="icon-[tabler--alert-triangle] size-3 mr-1"></span> Pending Setup
                                        </span>
                                    @elseif($instructor->is_active)
                                        <span class="badge badge-success badge-soft badge-sm">Active</span>
                                    @else
                                        <span class="badge badge-neutral badge-soft badge-sm">Inactive</span>
                                    @endif
                                    @if(!$instructor->is_visible)
                                        <span class="badge badge-neutral badge-soft badge-xs">Hidden</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($instructor->hasAccount())
                                    <span class="badge badge-success badge-soft badge-sm">
                                        <span class="icon-[tabler--user-check] size-3 mr-1"></span> Has Login
                                    </span>
                                @elseif($instructor->hasPendingInvitation())
                                    <span class="badge badge-warning badge-soft badge-sm">
                                        <span class="icon-[tabler--clock] size-3 mr-1"></span> Invite Sent
                                    </span>
                                @elseif($instructor->email)
                                    <span class="badge badge-neutral badge-soft badge-sm">Profile Only</span>
                                @else
                                    <span class="text-base-content/40 text-sm">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="relative">
                                    <details class="dropdown dropdown-bottom dropdown-end">
                                        <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                                            <span class="icon-[tabler--dots-vertical] size-4"></span>
                                        </summary>
                                        <ul class="dropdown-content menu bg-base-100 rounded-box w-48 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
                                            <li>
                                                <a href="{{ route('settings.team.instructors.edit', $instructor) }}">
                                                    <span class="icon-[tabler--edit] size-4"></span> Edit Profile
                                                </a>
                                            </li>
                                            @if(!$instructor->hasAccount() && $instructor->email && !$instructor->hasPendingInvitation())
                                            <li>
                                                <form action="{{ route('settings.team.instructors.invite', $instructor) }}" method="POST" class="m-0">
                                                    @csrf
                                                    <button type="submit" class="w-full text-left flex items-center gap-2">
                                                        <span class="icon-[tabler--send] size-4"></span> Send Login Invite
                                                    </button>
                                                </form>
                                            </li>
                                            @endif
                                            <li>
                                                <form action="{{ route('settings.team.instructors.delete', $instructor) }}" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete {{ $instructor->name }}? This cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                                                        <span class="icon-[tabler--trash] size-4"></span> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </details>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($instructors->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center py-8">
                                <div class="text-base-content/50">
                                    @if($search)
                                        <span class="icon-[tabler--search] size-8 mb-2 block mx-auto"></span>
                                        No instructors found matching "{{ $search }}"
                                    @else
                                        <span class="icon-[tabler--yoga] size-8 mb-2 block mx-auto"></span>
                                        No instructors yet
                                        <div class="mt-2">
                                            <a href="{{ route('settings.team.instructors.create') }}" class="btn btn-primary btn-sm">
                                                <span class="icon-[tabler--plus] size-4"></span> Add Instructor
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Pagination / Results Info --}}
            @if($instructors->total() > 0)
            <div class="mt-4 pt-4 border-t border-base-content/10">
                @if($instructors->hasPages())
                    {{ $instructors->links() }}
                @else
                    <div class="text-sm text-base-content/60 text-center">
                        Showing <span class="font-medium text-base-content">{{ $instructors->total() }}</span> {{ Str::plural('instructor', $instructors->total()) }}
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Pending Invitations --}}
    @if($pendingInvitations->total() > 0)
    <div class="card bg-base-100 overflow-visible">
        <div class="card-body overflow-visible">
            <h2 class="text-lg font-semibold mb-4">Pending Instructor Invitations</h2>
            <div class="overflow-visible">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Instructor</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Sent</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingInvitations as $invitation)
                        <tr>
                            <td class="font-medium">{{ $invitation->instructor?->name ?? '—' }}</td>
                            <td>{{ $invitation->email }}</td>
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
            @if($pendingInvitations->total() > 0)
            <div class="mt-4 pt-4 border-t border-base-content/10">
                @if($pendingInvitations->hasPages())
                    {{ $pendingInvitations->links() }}
                @else
                    <div class="text-sm text-base-content/60 text-center">
                        Showing <span class="font-medium text-base-content">{{ $pendingInvitations->total() }}</span> pending {{ Str::plural('invitation', $pendingInvitations->total()) }}
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Stats Overview --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Overview</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 border border-base-content/10 rounded-lg text-center">
                    <div class="text-2xl font-bold text-success">{{ $statusCounts['active'] ?? 0 }}</div>
                    <div class="text-sm text-base-content/60">Active</div>
                </div>
                <div class="p-4 border border-base-content/10 rounded-lg text-center">
                    <div class="text-2xl font-bold text-warning">{{ $statusCounts['inactive'] ?? 0 }}</div>
                    <div class="text-sm text-base-content/60">Inactive</div>
                </div>
                <div class="p-4 border border-base-content/10 rounded-lg text-center">
                    <div class="text-2xl font-bold text-info">{{ $statusCounts['with_account'] ?? 0 }}</div>
                    <div class="text-sm text-base-content/60">With Login</div>
                </div>
                <div class="p-4 border border-base-content/10 rounded-lg text-center">
                    <div class="text-2xl font-bold text-accent">{{ $statusCounts['pending_invite'] ?? 0 }}</div>
                    <div class="text-sm text-base-content/60">Pending Invites</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Role Explanation --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Instructor Profiles vs. Login Accounts</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--user] size-5 text-primary mt-0.5"></span>
                        <div>
                            <div class="font-medium">Profile Only</div>
                            <div class="text-sm text-base-content/60">
                                Instructor appears on your booking page and can be assigned to classes.
                                They don't have login access - you manage everything for them.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--user-check] size-5 text-success mt-0.5"></span>
                        <div>
                            <div class="font-medium">Profile + Login Account</div>
                            <div class="text-sm text-base-content/60">
                                Instructor has their own login to view their schedule and mark attendance.
                                Send an invite to give them access.
                            </div>
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
