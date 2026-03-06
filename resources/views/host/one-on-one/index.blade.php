@extends('layouts.dashboard')

@section('title', ($showConfiguration ?? false) ? '1:1 Meeting Setup' : ($isOwner ? 'All 1:1 Bookings' : 'My 1:1 Bookings'))

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">1:1 Meetings</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">
                @if($showConfiguration ?? false)
                    1:1 Meeting Setup
                @else
                    {{ $isOwner ? 'All 1:1 Bookings' : 'My 1:1 Bookings' }}
                @endif
            </h1>
            <p class="text-base-content/60 mt-1">
                @if($showConfiguration ?? false)
                    Configure your availability and booking preferences for 1:1 meetings.
                @elseif($isOwner)
                    View and manage all 1:1 meetings across your team.
                @else
                    Manage your upcoming and past 1:1 meetings.
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{-- Manage Access Button (Owner/Admin only) --}}
            @if($isOwner)
            <a href="{{ route('marketplace.show', 'online-1on1-meeting') }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--users-cog] size-5"></span>
                <span class="hidden sm:inline">Manage Access</span>
            </a>
            @endif

            {{-- Send Invite Button (Owner/Admin only) --}}
            @if($isOwner)
            <button type="button" class="btn btn-ghost btn-sm" onclick="openInviteModal()">
                <span class="icon-[tabler--send] size-5"></span>
                <span class="hidden sm:inline">Send Invite</span>
            </button>
            @endif

            {{-- Meeting Configuration Button (Members with profile, only when NOT showing inline config) --}}
            @if(!$isOwner && $profile && !($showConfiguration ?? false))
            <a href="{{ route('one-on-one-setup.index') }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--settings] size-5"></span>
                <span class="hidden sm:inline">Meeting Configuration</span>
            </a>
            @endif

            {{-- View Public Page (if setup complete) --}}
            @if($profile && $profile->is_setup_complete)
            <a href="{{ $profile->getPublicUrl() }}" target="_blank" class="btn btn-primary btn-soft btn-sm">
                <span class="icon-[tabler--external-link] size-5"></span>
                <span class="hidden sm:inline">View Public Page</span>
            </a>
            @endif
        </div>
    </div>

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

    {{-- Configuration Form (shown when setup is not complete) --}}
    @if($showConfiguration ?? false)
        @include('host.one-on-one.partials.configuration-form')
    @else

    {{-- Filters Row --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        {{-- Status Tabs --}}
        <div class="tabs tabs-bordered">
            <a href="{{ route('one-on-one.index', array_merge(['status' => 'pending'], $isOwner && $selectedInstructorId ? ['instructor_id' => $selectedInstructorId] : [])) }}"
                class="tab {{ $currentStatus === 'pending' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--clock-hour-4] size-4 me-1"></span>
                Pending
            </a>
            <a href="{{ route('one-on-one.index', array_merge(['status' => 'upcoming'], $isOwner && $selectedInstructorId ? ['instructor_id' => $selectedInstructorId] : [])) }}"
                class="tab {{ $currentStatus === 'upcoming' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--calendar-event] size-4 me-1"></span>
                Upcoming
            </a>
            <a href="{{ route('one-on-one.index', array_merge(['status' => 'past'], $isOwner && $selectedInstructorId ? ['instructor_id' => $selectedInstructorId] : [])) }}"
                class="tab {{ $currentStatus === 'past' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--history] size-4 me-1"></span>
                Past
            </a>
            <a href="{{ route('one-on-one.index', array_merge(['status' => 'cancelled'], $isOwner && $selectedInstructorId ? ['instructor_id' => $selectedInstructorId] : [])) }}"
                class="tab {{ $currentStatus === 'cancelled' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--calendar-off] size-4 me-1"></span>
                Cancelled
            </a>
            @if($isOwner)
            <a href="{{ route('one-on-one.index', ['status' => 'invites']) }}"
                class="tab {{ $currentStatus === 'invites' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--send] size-4 me-1"></span>
                Sent Invites
            </a>
            @endif
        </div>

        {{-- Instructor Filter (Owner only) --}}
        @if($isOwner && $instructorsWithProfiles->isNotEmpty())
        <div class="flex items-center gap-2">
            <label class="text-sm text-base-content/60" for="instructor_filter">Filter by:</label>
            <select id="instructor_filter" class="select select-sm select-bordered w-48" onchange="filterByInstructor(this.value)">
                <option value="">All Team Members</option>
                @foreach($instructorsWithProfiles as $inst)
                    <option value="{{ $inst->id }}" {{ $selectedInstructorId == $inst->id ? 'selected' : '' }}>
                        {{ $inst->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    {{-- Invites Tab Content --}}
    @if($currentStatus === 'invites' && $isOwner)
        @if($invites->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--send] size-16 text-base-content/20 mx-auto"></span>
                <h3 class="text-lg font-semibold mt-4">No Invites Sent</h3>
                <p class="text-base-content/60 mt-1">You haven't sent any booking invites yet.</p>
                <button type="button" onclick="openInviteModal()" class="btn btn-primary btn-sm mt-4">
                    <span class="icon-[tabler--send] size-4"></span>
                    Send Your First Invite
                </button>
            </div>
        </div>
        @else
        <div class="card bg-base-100">
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Staff Member</th>
                                <th>Sent By</th>
                                <th>Sent At</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invites as $invite)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <span class="icon-[tabler--mail] size-4 text-base-content/50"></span>
                                        {{ $invite->email }}
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        @if($invite->instructor?->photo_url)
                                            <img src="{{ $invite->instructor->photo_url }}" alt="{{ $invite->instructor->name }}" class="w-8 h-8 rounded-full object-cover">
                                        @else
                                            <div class="avatar avatar-placeholder">
                                                <div class="bg-primary text-primary-content w-8 h-8 rounded-full text-xs font-bold">
                                                    {{ strtoupper(substr($invite->instructor?->name ?? 'U', 0, 1)) }}
                                                </div>
                                            </div>
                                        @endif
                                        <span class="text-sm">{{ $invite->instructor?->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td class="text-sm text-base-content/70">
                                    {{ $invite->sentBy?->name ?? 'Unknown' }}
                                </td>
                                <td>
                                    <div class="text-sm">{{ $invite->sent_at->format('M j, Y') }}</div>
                                    <div class="text-xs text-base-content/50">{{ $invite->sent_at->format('g:i A') }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $invite->status_badge }} badge-soft badge-sm">
                                        {{ ucfirst($invite->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" class="btn btn-ghost btn-xs" title="Resend Invite" onclick="resendInvite({{ $invite->id }})">
                                            <span class="icon-[tabler--refresh] size-4"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        @if($invites->hasPages())
        <div class="flex justify-center">
            {{ $invites->links() }}
        </div>
        @endif
        @endif
    @else
    {{-- Bookings List --}}
    @if($bookings->isEmpty())
    <div class="card bg-base-100">
        <div class="card-body text-center py-12">
            @if($currentStatus === 'pending')
            <span class="icon-[tabler--clock-hour-4] size-16 text-base-content/20 mx-auto"></span>
            <h3 class="text-lg font-semibold mt-4">No Pending Requests</h3>
            <p class="text-base-content/60 mt-1">{{ $isOwner ? 'There are' : 'You don\'t have' }} no pending booking requests.</p>
            @elseif($currentStatus === 'upcoming')
            <span class="icon-[tabler--calendar-event] size-16 text-base-content/20 mx-auto"></span>
            <h3 class="text-lg font-semibold mt-4">No Upcoming Bookings</h3>
            @if($isOwner)
            <p class="text-base-content/60 mt-1">There are no upcoming 1:1 meetings scheduled.</p>
            @else
            <p class="text-base-content/60 mt-1">You don't have any upcoming 1:1 meetings scheduled.</p>
            @if($profile && $profile->is_setup_complete)
            <p class="text-base-content/60 mt-2">Share your booking link to get started:</p>
            <div class="bg-base-200 rounded-lg p-3 mt-3 max-w-md mx-auto">
                <p class="text-sm font-mono break-all">{{ $profile->getPublicUrl() }}</p>
            </div>
            <button type="button" onclick="copyToClipboard('{{ $profile->getPublicUrl() }}')" class="btn btn-primary btn-sm mt-3">
                <span class="icon-[tabler--copy] size-4"></span>
                Copy Link
            </button>
            @endif
            @endif
            @elseif($currentStatus === 'past')
            <span class="icon-[tabler--history] size-16 text-base-content/20 mx-auto"></span>
            <h3 class="text-lg font-semibold mt-4">No Past Bookings</h3>
            <p class="text-base-content/60 mt-1">{{ $isOwner ? 'There are' : 'You don\'t have' }} no past 1:1 meetings yet.</p>
            @elseif($currentStatus === 'cancelled')
            <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto"></span>
            <h3 class="text-lg font-semibold mt-4">No Cancelled Bookings</h3>
            <p class="text-base-content/60 mt-1">{{ $isOwner ? 'There are' : 'You don\'t have' }} no cancelled 1:1 meetings.</p>
            @endif
        </div>
    </div>
    @else
    <div class="card bg-base-100">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Guest</th>
                            @if($isOwner)
                            <th>Team Member</th>
                            @endif
                            <th>Date & Time</th>
                            <th>Duration</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar avatar-placeholder">
                                        <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold text-sm">
                                            {{ strtoupper(substr($booking->guest_first_name, 0, 1) . substr($booking->guest_last_name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $booking->guest_first_name }} {{ $booking->guest_last_name }}</div>
                                        <div class="text-sm text-base-content/60">{{ $booking->guest_email }}</div>
                                    </div>
                                </div>
                            </td>
                            @if($isOwner)
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($booking->bookingProfile?->instructor?->photo_url)
                                        <img src="{{ $booking->bookingProfile->instructor->photo_url }}" alt="{{ $booking->bookingProfile->instructor->name }}" class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <div class="avatar avatar-placeholder">
                                            <div class="bg-secondary text-secondary-content w-8 h-8 rounded-full text-xs font-bold">
                                                {{ strtoupper(substr($booking->bookingProfile?->instructor?->name ?? 'U', 0, 1)) }}
                                            </div>
                                        </div>
                                    @endif
                                    <span class="text-sm">{{ $booking->bookingProfile?->instructor?->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            @endif
                            <td>
                                <div class="font-medium">{{ $booking->start_time->format('M j, Y') }}</div>
                                <div class="text-sm text-base-content/60">{{ $booking->start_time->format('g:i A') }} - {{ $booking->end_time->format('g:i A') }}</div>
                            </td>
                            <td>{{ $booking->duration_minutes }} min</td>
                            <td>
                                @php
                                    $typeIcon = match($booking->meeting_type) {
                                        'in_person' => 'icon-[tabler--map-pin]',
                                        'phone' => 'icon-[tabler--phone]',
                                        'video' => 'icon-[tabler--video]',
                                        default => 'icon-[tabler--calendar]',
                                    };
                                    $typeLabel = match($booking->meeting_type) {
                                        'in_person' => 'In-Person',
                                        'phone' => 'Phone',
                                        'video' => 'Video',
                                        default => ucfirst($booking->meeting_type),
                                    };
                                @endphp
                                <span class="flex items-center gap-1 text-sm">
                                    <span class="{{ $typeIcon }} size-4"></span>
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusBadge = match($booking->status) {
                                        'pending' => 'badge-warning',
                                        'confirmed' => 'badge-success',
                                        'declined' => 'badge-error',
                                        'completed' => 'badge-info',
                                        'cancelled' => 'badge-neutral',
                                        'no_show' => 'badge-error',
                                        default => 'badge-ghost',
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }} badge-soft badge-sm">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Accept/Decline for Pending --}}
                                    @if($booking->status === 'pending')
                                        <button type="button" class="btn btn-success btn-xs" title="Accept" onclick="openAcceptModal({{ $booking->id }})">
                                            <span class="icon-[tabler--check] size-4"></span>
                                            Accept
                                        </button>
                                        <button type="button" class="btn btn-error btn-soft btn-xs" title="Decline" onclick="openDeclineModal({{ $booking->id }})">
                                            <span class="icon-[tabler--x] size-4"></span>
                                            Decline
                                        </button>
                                    @endif

                                    {{-- View Button --}}
                                    <a href="{{ route('one-on-one.show', $booking) }}" class="btn btn-ghost btn-xs btn-square" title="View Details">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                    </a>

                                    @if($booking->status === 'confirmed')
                                        @if($booking->start_time->isPast())
                                        {{-- Complete Button --}}
                                        <button type="button" class="btn btn-ghost btn-xs btn-square text-success" title="Mark Completed" onclick="markComplete({{ $booking->id }})">
                                            <span class="icon-[tabler--check] size-4"></span>
                                        </button>
                                        {{-- No-Show Button --}}
                                        <button type="button" class="btn btn-ghost btn-xs btn-square text-warning" title="Mark No-Show" onclick="markNoShow({{ $booking->id }})">
                                            <span class="icon-[tabler--user-off] size-4"></span>
                                        </button>
                                        @else
                                        {{-- Cancel Button --}}
                                        <button type="button" class="btn btn-ghost btn-xs btn-square text-error" title="Cancel Booking" onclick="openCancelModal({{ $booking->id }})">
                                            <span class="icon-[tabler--x] size-4"></span>
                                        </button>
                                        @endif
                                    @endif

                                    {{-- More Actions Dropdown --}}
                                    <div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]">
                                        <button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions">
                                            <span class="icon-[tabler--dots] size-4"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-44" role="menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('one-on-one.show', $booking) }}">
                                                    <span class="icon-[tabler--eye] size-4 me-2"></span>View Details
                                                </a>
                                            </li>
                                            @if($booking->guest_email)
                                            <li>
                                                <a class="dropdown-item" href="mailto:{{ $booking->guest_email }}">
                                                    <span class="icon-[tabler--mail] size-4 me-2"></span>Email Guest
                                                </a>
                                            </li>
                                            @endif
                                            @if($booking->guest_phone)
                                            <li>
                                                <a class="dropdown-item" href="tel:{{ $booking->guest_phone }}">
                                                    <span class="icon-[tabler--phone] size-4 me-2"></span>Call Guest
                                                </a>
                                            </li>
                                            @endif
                                            @if($booking->status === 'confirmed')
                                                @if($booking->start_time->isPast())
                                                <li class="border-t border-base-200 mt-1 pt-1">
                                                    <button type="button" class="dropdown-item" onclick="markComplete({{ $booking->id }})">
                                                        <span class="icon-[tabler--check] size-4 me-2 text-success"></span>Mark Completed
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item" onclick="markNoShow({{ $booking->id }})">
                                                        <span class="icon-[tabler--user-off] size-4 me-2 text-warning"></span>Mark No-Show
                                                    </button>
                                                </li>
                                                @else
                                                <li class="border-t border-base-200 mt-1 pt-1">
                                                    <button type="button" class="dropdown-item text-error" onclick="openCancelModal({{ $booking->id }})">
                                                        <span class="icon-[tabler--x] size-4 me-2"></span>Cancel Booking
                                                    </button>
                                                </li>
                                                @endif
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if($bookings->hasPages())
    <div class="flex justify-center">
        {{ $bookings->links() }}
    </div>
    @endif
    @endif
    @endif {{-- End of invites/bookings conditional --}}

    @endif {{-- End of @else (listing view) --}}
</div>

{{-- Send Invite Modal (Owner/Admin only) --}}
@if($isOwner)
<div id="invite-modal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeInviteModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-2xl w-full relative">
            <div class="p-8">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--send] size-8 text-primary"></span>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold">Send Booking Invite</h3>
                        <p class="text-base text-base-content/60">Invite a client to book a 1:1 meeting</p>
                    </div>
                </div>

                @php
                    $instructorsWithBooking = \App\Models\Instructor::where('host_id', $host->id)
                        ->whereHas('bookingProfile', fn($q) => $q->where('is_enabled', true)->where('is_setup_complete', true))
                        ->get();
                @endphp

                @if($instructorsWithBooking->isEmpty())
                <div class="alert alert-soft alert-warning">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span>No team members have completed their 1:1 booking setup yet.</span>
                </div>
                @else
                <form id="invite-form" class="space-y-6">
                    <div>
                        <label class="label-text mb-2 block text-base" for="invite_instructor_id">Select Staff / Instructor</label>
                        <select id="invite_instructor_id" class="hidden" required
                            data-select='{
                                "hasSearch": true,
                                "searchPlaceholder": "Search staff...",
                                "placeholder": "Choose a team member...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle w-full",
                                "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            <option value="">Choose a team member...</option>
                            @foreach($instructorsWithBooking as $inst)
                                <option value="{{ $inst->id }}">
                                    {{ $inst->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label-text mb-2 block text-base" for="invite_email">Email Address</label>
                        <input type="email" id="invite_email" class="input input-bordered w-full input-lg" placeholder="client@example.com" required>
                    </div>
                </form>
                @endif
            </div>
            <div class="flex justify-end gap-3 p-6 border-t border-base-200">
                <button type="button" class="btn btn-ghost btn-lg" onclick="closeInviteModal()">Cancel</button>
                @if($instructorsWithBooking->isNotEmpty())
                <button type="button" class="btn btn-primary btn-lg" id="send-invite-btn" onclick="sendInvite()">
                    <span class="loading loading-spinner loading-sm hidden"></span>
                    <span class="icon-[tabler--send] size-5"></span>
                    Send Invite
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- Accept Confirmation Modal --}}
<div id="accept-modal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeAcceptModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-md w-full relative">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--check] size-6 text-success"></span>
                    </div>
                    <h3 class="text-lg font-semibold">Accept Booking</h3>
                </div>
                <p class="text-base-content/70">Are you sure you want to accept this booking request? The guest will receive a confirmation email.</p>
            </div>
            <div class="flex justify-end gap-2 p-4 border-t border-base-200">
                <button type="button" class="btn btn-ghost" onclick="closeAcceptModal()">Cancel</button>
                <button type="button" class="btn btn-success" id="confirm-accept-btn" onclick="confirmAccept()">
                    <span class="loading loading-spinner loading-sm hidden"></span>
                    Accept Booking
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Decline Modal --}}
<div id="decline-modal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeDeclineModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-md w-full relative">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-error/10 flex items-center justify-center">
                        <span class="icon-[tabler--x] size-6 text-error"></span>
                    </div>
                    <h3 class="text-lg font-semibold">Decline Booking</h3>
                </div>
                <p class="text-base-content/70 mb-4">Are you sure you want to decline this booking request? The guest will be notified via email.</p>
                <div>
                    <label class="label-text mb-1 block" for="decline_reason">Reason (optional)</label>
                    <textarea id="decline_reason" class="textarea textarea-bordered w-full" rows="3" placeholder="Provide a reason for declining..."></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 p-4 border-t border-base-200">
                <button type="button" class="btn btn-ghost" onclick="closeDeclineModal()">Cancel</button>
                <button type="button" class="btn btn-error" id="confirm-decline-btn" onclick="confirmDecline()">
                    <span class="loading loading-spinner loading-sm hidden"></span>
                    Decline Booking
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div id="cancel-modal" class="overlay modal overlay-open:opacity-100 hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Cancel Booking</h3>
                <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" onclick="closeCancelModal()">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-base-content/70">Are you sure you want to cancel this booking? The guest will be notified via email.</p>
                <div class="mt-4">
                    <label class="label-text" for="cancel_reason">Reason (optional)</label>
                    <textarea id="cancel_reason" class="textarea w-full" rows="3" placeholder="Provide a reason for cancellation..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeCancelModal()">Keep Booking</button>
                <button type="button" class="btn btn-error" id="confirm-cancel-btn" onclick="confirmCancel()">
                    <span class="loading loading-spinner loading-sm hidden"></span>
                    Cancel Booking
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let bookingToCancel = null;
let bookingToDecline = null;

function openInviteModal() {
    const selectEl = document.getElementById('invite_instructor_id');
    const emailEl = document.getElementById('invite_email');

    // Reset email field
    if (emailEl) emailEl.value = '';

    // Reset select - handle advance-select
    if (selectEl) {
        selectEl.value = '';
        // If using HSSelect, reset it
        if (window.HSSelect) {
            const hsSelectInstance = HSSelect.getInstance(selectEl);
            if (hsSelectInstance) {
                hsSelectInstance.setValue('');
            }
        }
    }

    document.getElementById('invite-modal').classList.remove('hidden');

    // Reinitialize advance-select after modal opens
    setTimeout(() => {
        if (window.HSSelect) {
            HSSelect.autoInit();
        }
    }, 100);
}

function closeInviteModal() {
    document.getElementById('invite-modal').classList.add('hidden');
}

function showNotification(type, message) {
    if (window.notyf) {
        window.notyf[type](message);
    } else if (typeof Notyf !== 'undefined') {
        new Notyf()[type](message);
    } else {
        alert(message);
    }
}

async function sendInvite() {
    const instructorSelect = document.getElementById('invite_instructor_id');
    const emailInput = document.getElementById('invite_email');
    const instructorId = instructorSelect.value;
    const email = emailInput.value;

    if (!instructorId || !email) {
        showNotification('error', 'Please select a staff member and enter an email address');
        return;
    }

    const btn = document.getElementById('send-invite-btn');
    const spinner = btn.querySelector('.loading');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    try {
        const response = await fetch('{{ route("one-on-one.send-invite") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                instructor_id: instructorId,
                email: email,
            }),
        });

        const result = await response.json();

        if (response.ok && result.success) {
            showNotification('success', 'Invite sent successfully!');
            closeInviteModal();
        } else {
            showNotification('error', result.message || 'Failed to send invite');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('error', 'An error occurred while sending invite');
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
}

async function resendInvite(inviteId) {
    if (!confirm('Resend this invite?')) return;

    try {
        const response = await fetch(`/one-on-one/resend-invite/${inviteId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });

        const result = await response.json();

        if (response.ok && result.success) {
            showNotification('success', 'Invite resent successfully!');
        } else {
            showNotification('error', result.message || 'Failed to resend invite');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('error', 'An error occurred');
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        if (typeof Notyf !== 'undefined') {
            new Notyf().success('Link copied to clipboard!');
        }
    });
}

function filterByInstructor(instructorId) {
    const url = new URL(window.location.href);
    if (instructorId) {
        url.searchParams.set('instructor_id', instructorId);
    } else {
        url.searchParams.delete('instructor_id');
    }
    window.location.href = url.toString();
}

function openCancelModal(bookingId) {
    bookingToCancel = bookingId;
    document.getElementById('cancel_reason').value = '';
    document.getElementById('cancel-modal').classList.remove('hidden');
}

function closeCancelModal() {
    bookingToCancel = null;
    document.getElementById('cancel-modal').classList.add('hidden');
}

async function confirmCancel() {
    if (!bookingToCancel) return;

    const btn = document.getElementById('confirm-cancel-btn');
    const spinner = btn.querySelector('.loading');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    const reason = document.getElementById('cancel_reason').value;

    try {
        const response = await fetch(`/one-on-one/${bookingToCancel}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ reason }),
        });

        const result = await response.json();

        if (response.ok && result.success) {
            window.location.reload();
        } else {
            if (typeof Notyf !== 'undefined') {
                new Notyf().error(result.message || 'Failed to cancel booking');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        if (typeof Notyf !== 'undefined') {
            new Notyf().error('An error occurred');
        }
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
}

async function markComplete(bookingId) {
    if (!confirm('Mark this booking as completed?')) return;

    try {
        const response = await fetch(`/one-on-one/${bookingId}/complete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });

        const result = await response.json();

        if (response.ok && result.success) {
            window.location.reload();
        } else {
            if (typeof Notyf !== 'undefined') {
                new Notyf().error(result.message || 'Failed to update booking');
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function markNoShow(bookingId) {
    if (!confirm('Mark this booking as no-show?')) return;

    try {
        const response = await fetch(`/one-on-one/${bookingId}/no-show`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });

        const result = await response.json();

        if (response.ok && result.success) {
            window.location.reload();
        } else {
            if (typeof Notyf !== 'undefined') {
                new Notyf().error(result.message || 'Failed to update booking');
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

let bookingToAccept = null;

function openAcceptModal(bookingId) {
    bookingToAccept = bookingId;
    document.getElementById('accept-modal').classList.remove('hidden');
}

function closeAcceptModal() {
    bookingToAccept = null;
    document.getElementById('accept-modal').classList.add('hidden');
}

async function confirmAccept() {
    if (!bookingToAccept) return;

    const btn = document.getElementById('confirm-accept-btn');
    const spinner = btn.querySelector('.loading');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    try {
        const response = await fetch(`/one-on-one/${bookingToAccept}/accept`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });

        const result = await response.json();

        if (response.ok && result.success) {
            if (typeof Notyf !== 'undefined') {
                new Notyf().success('Booking accepted!');
            }
            window.location.reload();
        } else {
            if (typeof Notyf !== 'undefined') {
                new Notyf().error(result.message || 'Failed to accept booking');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        if (typeof Notyf !== 'undefined') {
            new Notyf().error('An error occurred');
        }
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
}

function openDeclineModal(bookingId) {
    bookingToDecline = bookingId;
    document.getElementById('decline_reason').value = '';
    document.getElementById('decline-modal').classList.remove('hidden');
}

function closeDeclineModal() {
    bookingToDecline = null;
    document.getElementById('decline-modal').classList.add('hidden');
}

async function confirmDecline() {
    if (!bookingToDecline) return;

    const btn = document.getElementById('confirm-decline-btn');
    const spinner = btn.querySelector('.loading');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    const reason = document.getElementById('decline_reason').value;

    try {
        const response = await fetch(`/one-on-one/${bookingToDecline}/decline`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ reason }),
        });

        const result = await response.json();

        if (response.ok && result.success) {
            if (typeof Notyf !== 'undefined') {
                new Notyf().success('Booking declined');
            }
            window.location.reload();
        } else {
            if (typeof Notyf !== 'undefined') {
                new Notyf().error(result.message || 'Failed to decline booking');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        if (typeof Notyf !== 'undefined') {
            new Notyf().error('An error occurred');
        }
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
}
</script>
@endpush
@endsection
