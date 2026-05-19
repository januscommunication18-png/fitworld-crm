@extends('layouts.dashboard')

@section('title', $classSession->display_title)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('membership-schedules.index') }}"><span class="icon-[tabler--id-badge-2] me-1 size-4"></span> Membership Sessions</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ Str::limit($classSession->display_title, 30) }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            @if($membershipPlan?->image_url)
                <img src="{{ $membershipPlan->image_url }}" alt="{{ $classSession->display_title }}" class="w-24 h-24 rounded-lg object-cover">
            @else
                <div class="w-24 h-24 rounded-lg flex items-center justify-center" style="background-color: {{ $membershipPlan?->color ?? '#8b5cf6' }}20;">
                    <span class="icon-[tabler--id-badge-2] size-10" style="color: {{ $membershipPlan?->color ?? '#8b5cf6' }};"></span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold">{{ $classSession->display_title }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="badge {{ $classSession->getStatusBadgeClass() }} badge-soft capitalize">{{ $classSession->status }}</span>
                    <span class="badge badge-soft badge-warning badge-sm">Membership Session</span>
                    @if($membershipPlan)
                        <span class="badge badge-soft badge-secondary badge-sm">{{ $membershipPlan->name }}</span>
                    @endif
                    @if($classSession->isRecurring())
                        <span class="badge badge-soft badge-info badge-sm">Recurring</span>
                    @endif
                    @if($membershipPlan?->qr_checkin_enabled)
                        <span class="badge badge-soft badge-accent badge-sm"><span class="icon-[tabler--qrcode] size-3 me-1"></span>QR Check-in</span>
                    @endif
                </div>
                <p class="text-base-content/60 mt-1 text-sm">{{ $classSession->start_time->format('l, F j, Y') }} &bull; {{ $classSession->formatted_time_range }} &bull; {{ $classSession->formatted_duration }}</p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 flex-wrap">
            @if($classSession->isPublished() && !$classSession->isPast() && $classSession->membershipPlans->isNotEmpty())
                <a href="{{ route('walk-in.select-membership', ['class_session_id' => $classSession->id]) }}" class="btn btn-success btn-sm">
                    <span class="icon-[tabler--user-plus] size-4"></span> Add Booking
                </a>
            @endif
            <a href="{{ route('scheduled-membership.edit', $classSession) }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--edit] size-4"></span> Edit
            </a>
            <a href="{{ route('membership-schedules.index') }}" class="btn btn-ghost btn-sm gap-1.5">
                <span class="icon-[tabler--arrow-left] size-4"></span> Back
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tabs tabs-bordered" role="tablist">
        <button class="tab tab-active" data-tab="overview" role="tab">
            <span class="icon-[tabler--info-circle] size-4 mr-2"></span>Overview
        </button>
        <button class="tab" data-tab="bookings" role="tab">
            <span class="icon-[tabler--users] size-4 mr-2"></span>Bookings
            @if($confirmedBookings->count() > 0)
                <span class="badge badge-sm badge-primary ml-1">{{ $confirmedBookings->count() }}</span>
            @endif
        </button>
    </div>

    <div class="tab-contents">
        {{-- Overview Tab --}}
        <div class="tab-content active" data-content="overview">
            <div class="space-y-6">

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="card bg-base-100">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-primary/10 rounded-lg p-2"><span class="icon-[tabler--users] size-6 text-primary"></span></div>
                            <div>
                                <p class="text-2xl font-bold">{{ $confirmedBookings->count() }}</p>
                                <p class="text-xs text-base-content/60">Booked</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-success/10 rounded-lg p-2"><span class="icon-[tabler--user-check] size-6 text-success"></span></div>
                            <div>
                                <p class="text-2xl font-bold">{{ $checkedInCount }}</p>
                                <p class="text-xs text-base-content/60">Checked In</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-warning/10 rounded-lg p-2"><span class="icon-[tabler--ticket] size-6 text-warning"></span></div>
                            <div>
                                <p class="text-2xl font-bold">{{ $classSession->capacity ? max(0, $classSession->capacity - $confirmedBookings->count()) : '∞' }}</p>
                                <p class="text-xs text-base-content/60">Spots Left</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-error/10 rounded-lg p-2"><span class="icon-[tabler--x] size-6 text-error"></span></div>
                            <div>
                                <p class="text-2xl font-bold">{{ $cancelledBookings->count() }}</p>
                                <p class="text-xs text-base-content/60">Cancelled</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Session Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        Session Details
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Date</label>
                            <p class="font-medium">{{ $classSession->start_time->format('l, F j, Y') }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Time</label>
                            <p class="font-medium">{{ $classSession->formatted_time_range }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Duration</label>
                            <p class="font-medium">{{ $classSession->formatted_duration }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Capacity</label>
                            <p class="font-medium">{{ $classSession->capacity ?? 'Unlimited' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Membership Plan</label>
                            <p class="font-medium">{{ $membershipPlan?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Status</label>
                            <p class="mt-0.5"><span class="badge {{ $classSession->getStatusBadgeClass() }} badge-soft badge-sm capitalize">{{ $classSession->status }}</span></p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Created</label>
                            <p class="font-medium">{{ $classSession->created_at->format('M j, Y') }}</p>
                        </div>
                        @if($classSession->isRecurring())
                        <div class="col-span-2">
                            <label class="text-sm text-base-content/60">Recurring Days</label>
                            <div class="flex flex-wrap gap-1.5 mt-1">
                                @php
                                    $rule = $classSession->recurrence_rule;
                                    if ($classSession->isRecurrenceChild() && $classSession->recurrenceParent) {
                                        $rule = $classSession->recurrenceParent->recurrence_rule;
                                    }
                                    $parsedRule = is_string($rule) ? app(\App\Services\Schedule\RecurrenceService::class)->parseRecurrenceRule($rule) : null;
                                    $dayMap = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
                                    $recurringDayLabels = [];
                                    if ($parsedRule && !empty($parsedRule['days_of_week'])) {
                                        $recurringDayLabels = array_map(fn($d) => $dayMap[(int)$d] ?? $d, $parsedRule['days_of_week']);
                                    }
                                @endphp
                                @if(!empty($recurringDayLabels))
                                    @foreach($recurringDayLabels as $day)
                                        <span class="badge badge-soft badge-primary badge-sm">{{ $day }}</span>
                                    @endforeach
                                @else
                                    <span class="badge badge-soft badge-sm">{{ $classSession->start_time->format('l') }}</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Assigned Staff & Instructors --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--users] size-5"></span>
                        Assigned Staff & Instructors
                    </h2>
                    @if(!$classSession->primaryInstructor && $classSession->backupInstructors->isEmpty())
                        <p class="mt-4 text-base-content/40 italic text-sm">No staff or instructors assigned.</p>
                    @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                        @if($classSession->primaryInstructor)
                        @php $pi = $classSession->primaryInstructor; $piUser = $pi->user; @endphp
                        <div class="flex items-center gap-3 p-3 bg-primary/5 border border-primary/20 rounded-xl">
                            @if($piUser?->profile_photo_url)
                                <img src="{{ $piUser->profile_photo_url }}" alt="{{ $pi->name }}" class="w-10 h-10 rounded-full object-cover">
                            @elseif($pi->photo_url)
                                <img src="{{ $pi->photo_url }}" alt="{{ $pi->name }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-primary/15 flex items-center justify-center font-bold text-sm text-primary">{{ strtoupper(substr($pi->name, 0, 2)) }}</div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm truncate">{{ $pi->name }}</div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="badge badge-soft badge-primary badge-xs">Primary</span>
                                    @if($piUser)<span class="text-xs text-base-content/50">{{ $piUser->email }}</span>@endif
                                </div>
                            </div>
                        </div>
                        @endif
                        @foreach($classSession->backupInstructors as $index => $backup)
                        @php $backupUser = $backup->user; @endphp
                        <div class="flex items-center gap-3 p-3 bg-base-200/50 border border-base-300 rounded-xl">
                            @if($backupUser?->profile_photo_url)
                                <img src="{{ $backupUser->profile_photo_url }}" alt="{{ $backup->name }}" class="w-10 h-10 rounded-full object-cover">
                            @elseif($backup->photo_url)
                                <img src="{{ $backup->photo_url }}" alt="{{ $backup->name }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center font-bold text-sm text-secondary">{{ strtoupper(substr($backup->name, 0, 2)) }}</div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm truncate">{{ $backup->name }}</div>
                                <span class="badge badge-soft badge-neutral badge-xs">Backup #{{ $index + 1 }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Location --}}
            @if($classSession->location)
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--map-pin] size-5"></span>
                        Location
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Venue</label>
                            <p class="font-medium">{{ $classSession->location->name }}</p>
                        </div>
                        @if($classSession->room)
                        <div>
                            <label class="text-sm text-base-content/60">Room</label>
                            <p class="font-medium">{{ $classSession->room->name }}</p>
                        </div>
                        @endif
                        @if($classSession->location->full_address && !$classSession->location->isVirtual())
                        <div class="col-span-2">
                            <label class="text-sm text-base-content/60">Address</label>
                            <p class="font-medium">{{ $classSession->location->full_address }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Recurrence --}}
            @if($classSession->isRecurrenceParent() && $classSession->recurrenceChildren->isNotEmpty())
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--repeat] size-5"></span>
                        Recurring Sessions
                    </h2>
                    <div class="overflow-x-auto mt-4">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classSession->recurrenceChildren->take(10) as $child)
                                <tr>
                                    <td>{{ $child->start_time->format('D, M j, Y') }}</td>
                                    <td>{{ $child->formatted_time_range }}</td>
                                    <td><span class="badge {{ $child->getStatusBadgeClass() }} badge-soft badge-xs capitalize">{{ $child->status }}</span></td>
                                    <td><a href="{{ route('scheduled-membership.show', $child) }}" class="btn btn-ghost btn-xs">View</a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($classSession->recurrenceChildren->count() > 10)
                        <div class="p-3 text-center text-sm text-base-content/60">
                            And {{ $classSession->recurrenceChildren->count() - 10 }} more...
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- QR Code Check-in --}}
            @if($membershipPlan?->qr_checkin_enabled)
            @php
                $qrCheckinUrl = route('membership-checkin.qr', $membershipPlan);
            @endphp
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--qrcode] size-5"></span>
                        QR Code Check-in
                    </h2>
                    <div class="flex flex-col md:flex-row items-start gap-6 mt-4">
                        <div class="bg-white p-4 rounded-xl border border-base-300 inline-block">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCheckinUrl) }}"
                                 alt="QR Code for check-in" class="w-48 h-48">
                        </div>
                        <div class="flex-1">
                            <h3 class="font-medium mb-2">Daily Self Check-in</h3>
                            <p class="text-sm text-base-content/60 mb-3">Members can scan this QR code to check in when they arrive. Print and display it at the entrance or front desk.</p>
                            <div class="space-y-2">
                                <div class="p-3 bg-base-200/50 rounded-lg">
                                    <label class="text-xs text-base-content/60">Check-in URL</label>
                                    <p class="text-sm font-mono break-all mt-0.5">{{ $qrCheckinUrl }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" class="btn btn-sm btn-soft" onclick="navigator.clipboard.writeText('{{ $qrCheckinUrl }}'); this.textContent='Copied!'; setTimeout(() => this.innerHTML='<span class=\'icon-[tabler--copy] size-4\'></span> Copy URL', 1500)">
                                        <span class="icon-[tabler--copy] size-4"></span> Copy URL
                                    </button>
                                    <a href="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data={{ urlencode($qrCheckinUrl) }}" download="checkin-qr-{{ Str::slug($membershipPlan->name) }}.png" class="btn btn-sm btn-soft">
                                        <span class="icon-[tabler--download] size-4"></span> Download QR
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Notes --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--notes] size-5"></span>
                        Internal Notes
                    </h2>
                    @if($classSession->notes)
                        <p class="mt-2 whitespace-pre-line">{{ $classSession->notes }}</p>
                    @else
                        <p class="mt-2 text-base-content/40 italic">No notes added.</p>
                    @endif
                </div>
            </div>

            </div>
        </div>

        {{-- Bookings Tab --}}
        <div class="tab-content hidden" data-content="bookings">
            <div class="space-y-6">
                @if($allBookings->isEmpty())
                <div class="card bg-base-100">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--users-minus] size-16 text-base-content/20 mx-auto mb-4"></span>
                        <h3 class="text-lg font-semibold mb-2">No Bookings Yet</h3>
                        <p class="text-base-content/60 mb-4">No one has booked this membership session yet.</p>
                    </div>
                </div>
                @else
                <div class="card bg-base-100">
                    <div class="card-body p-0">
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Check In</th>
                                        <th>Booked</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allBookings->sortByDesc('created_at') as $booking)
                                    <tr class="{{ $booking->status === 'cancelled' ? 'opacity-60' : '' }}">
                                        <td>
                                            <div class="flex items-center gap-3">
                                                @if($booking->client)
                                                    <x-avatar :src="$booking->client->avatar_url ?? null" :initials="$booking->client->initials ?? '?'" :alt="$booking->client->full_name ?? 'Unknown'" size="sm" />
                                                    <div>
                                                        <a href="{{ route('clients.show', $booking->client) }}" class="font-medium hover:text-primary">{{ $booking->client->full_name }}</a>
                                                        @if($booking->client->email)
                                                            <div class="text-xs text-base-content/60">{{ $booking->client->email }}</div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-base-content/50">Unknown Client</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $booking->status_badge_class }} badge-sm">{{ \App\Models\Booking::getStatuses()[$booking->status] ?? $booking->status }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($booking->status === 'cancelled')
                                                <span class="text-base-content/30">-</span>
                                            @elseif($booking->isCheckedIn())
                                                <div class="flex items-center justify-center gap-1 text-success">
                                                    <span class="icon-[tabler--circle-check-filled] size-5"></span>
                                                    <span class="text-xs">{{ $booking->checked_in_at->format('g:i A') }}</span>
                                                </div>
                                            @else
                                                <span class="icon-[tabler--circle-dashed] size-5 text-base-content/30"></span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-sm">{{ $booking->created_at->format('M j, Y') }}</div>
                                            <div class="text-xs text-base-content/60">{{ $booking->created_at->format('g:i A') }}</div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var mainTabs = document.querySelectorAll('.tabs.tabs-bordered .tab');
    var mainContents = document.querySelectorAll('.tab-content');

    mainTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var targetTab = this.dataset.tab;
            mainTabs.forEach(function(t) { t.classList.remove('tab-active'); });
            this.classList.add('tab-active');
            mainContents.forEach(function(content) {
                content.classList.toggle('hidden', content.dataset.content !== targetTab);
                content.classList.toggle('active', content.dataset.content === targetTab);
            });
        }.bind(tab));
    });
});
</script>
@endpush
