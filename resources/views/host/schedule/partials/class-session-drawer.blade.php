{{-- Class Session Drawer --}}
{{-- Usage: @include('host.schedule.partials.class-session-drawer', ['classSession' => $classSession, 'isMembershipSchedule' => false]) --}}

@php
    $statuses = \App\Models\ClassSession::getStatuses();
    $statusColors = [
        'draft' => 'from-warning/10 to-warning/5',
        'published' => 'from-success/10 to-success/5',
        'cancelled' => 'from-error/10 to-error/5',
    ];
    $statusIconColors = [
        'draft' => 'bg-warning/20 text-warning',
        'published' => 'bg-success/20 text-success',
        'cancelled' => 'bg-error/20 text-error',
    ];
    $confirmedBookings = $classSession->confirmedBookings;
    $checkedInCount = $confirmedBookings->filter(fn($b) => $b->isCheckedIn())->count();
    $isMembershipSchedule = $isMembershipSchedule ?? false;
@endphp

<x-detail-drawer id="class-session-{{ $classSession->id }}" title="{{ $classSession->display_title }}" size="4xl">
    {{-- Status Hero Section --}}
    <div class="bg-gradient-to-r {{ $statusColors[$classSession->status] ?? 'from-primary/10 to-primary/5' }} rounded-xl p-4 mb-5 -mt-1">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full {{ $statusIconColors[$classSession->status] ?? 'bg-primary/20 text-primary' }} flex items-center justify-center">
                    <span class="icon-[tabler--yoga] size-7"></span>
                </div>
                <div>
                    <div class="font-semibold text-lg">{{ $classSession->start_time->format('l, M j') }}</div>
                    <div class="text-base-content/70">{{ $classSession->formatted_time_range }}</div>
                    <span class="badge {{ $classSession->getStatusBadgeClass() }} mt-1">
                        {{ $statuses[$classSession->status] ?? $classSession->status }}
                    </span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold">{{ $confirmedBookings->count() }}/{{ $classSession->getEffectiveCapacity() }}</div>
                <div class="text-sm text-base-content/60">booked</div>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-4 gap-3 mb-4">
        <div class="bg-base-200/50 rounded-lg p-3 text-center">
            <div class="text-lg font-bold text-primary">{{ $classSession->getEffectiveCapacity() }}</div>
            <div class="text-xs text-base-content/60">Capacity</div>
        </div>
        <div class="bg-base-200/50 rounded-lg p-3 text-center">
            <div class="text-lg font-bold text-info">{{ $confirmedBookings->count() }}</div>
            <div class="text-xs text-base-content/60">Booked</div>
        </div>
        <div class="bg-base-200/50 rounded-lg p-3 text-center">
            <div class="text-lg font-bold text-success">{{ $checkedInCount }}</div>
            <div class="text-xs text-base-content/60">Checked In</div>
        </div>
        <div class="bg-base-200/50 rounded-lg p-3 text-center">
            <div class="text-lg font-bold">{{ $classSession->getEffectiveCapacity() - $confirmedBookings->count() }}</div>
            <div class="text-xs text-base-content/60">Available</div>
        </div>
    </div>

    {{-- Class Details Card --}}
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--info-circle] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">Class Details</h4>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-base-100 rounded-lg p-3">
                <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                    <span class="icon-[tabler--user] size-3.5"></span>
                    Instructor
                </div>
                <div class="font-medium text-sm">{{ $classSession->primaryInstructor?->name ?? 'TBD' }}</div>
            </div>
            <div class="bg-base-100 rounded-lg p-3">
                <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                    <span class="icon-[tabler--map-pin] size-3.5"></span>
                    Location
                </div>
                <div class="font-medium text-sm">{{ $classSession->location?->name ?? 'TBD' }}</div>
                @if($classSession->room)
                    <div class="text-xs text-base-content/60">{{ $classSession->room->name }}</div>
                @endif
            </div>
            <div class="bg-base-100 rounded-lg p-3">
                <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                    <span class="icon-[tabler--clock] size-3.5"></span>
                    Duration
                </div>
                <div class="font-medium text-sm">{{ $classSession->formatted_duration }}</div>
            </div>
            <div class="bg-base-100 rounded-lg p-3">
                <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                    <span class="icon-[tabler--currency-dollar] size-3.5"></span>
                    Price
                </div>
                <div class="font-medium text-sm">{{ $classSession->formatted_price }}</div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    @if(!$classSession->isCancelled())
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--bolt] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">Quick Actions</h4>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($classSession->isDraft())
                <form action="{{ route('class-sessions.publish', $classSession) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success btn-sm">
                        <span class="icon-[tabler--check] size-4"></span>
                        Publish
                    </button>
                </form>
            @else
                <form action="{{ route('class-sessions.unpublish', $classSession) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-warning btn-sm">
                        <span class="icon-[tabler--eye-off] size-4"></span>
                        Unpublish
                    </button>
                </form>
            @endif
            @if($isMembershipSchedule)
                <a href="{{ route('walk-in.select-membership', ['class_session_id' => $classSession->id]) }}" class="btn btn-soft btn-warning btn-sm">
                    <span class="icon-[tabler--id-badge-2] size-4"></span>
                    Add Booking
                </a>
            @else
                <a href="{{ route('walk-in.select', ['session_id' => $classSession->id]) }}" class="btn btn-soft btn-primary btn-sm">
                    <span class="icon-[tabler--user-plus] size-4"></span>
                    Booking
                </a>
            @endif
        </div>
    </div>
    @endif

    {{-- Enrolled People Section --}}
    <div class="bg-base-200/50 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="icon-[tabler--users] size-4 text-primary"></span>
                <h4 class="text-sm font-semibold uppercase tracking-wide">Enrolled People</h4>
                <span class="badge badge-sm badge-primary">{{ $confirmedBookings->count() }}</span>
            </div>
            @if($confirmedBookings->count() > 0 && $checkedInCount < $confirmedBookings->count())
                <button type="button" class="btn btn-xs btn-success" onclick="checkInAll({{ $classSession->id }})">
                    <span class="icon-[tabler--checks] size-3"></span>
                    Check In All
                </button>
            @endif
        </div>

        @if($confirmedBookings->isEmpty())
            <div class="text-center py-6">
                <span class="icon-[tabler--users-minus] size-10 text-base-content/20 mx-auto mb-2"></span>
                <p class="text-sm text-base-content/60">No bookings yet</p>
                @if($isMembershipSchedule)
                    <a href="{{ route('walk-in.select-membership', ['class_session_id' => $classSession->id]) }}" class="btn btn-sm btn-warning mt-3">
                        <span class="icon-[tabler--id-badge-2] size-4"></span>
                        Add Booking
                    </a>
                @else
                    <a href="{{ route('walk-in.select', ['session_id' => $classSession->id]) }}" class="btn btn-sm btn-primary mt-3">
                        <span class="icon-[tabler--user-plus] size-4"></span>
                        Add Booking
                    </a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Payment</th>
                            <th class="text-center">Intake</th>
                            <th class="text-center">Check In</th>
                            <th class="w-24">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($confirmedBookings as $booking)
                            <tr id="booking-row-{{ $booking->id }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <x-avatar
                                            :src="$booking->client?->avatar_url ?? null"
                                            :initials="$booking->client?->initials ?? '?'"
                                            :alt="$booking->client?->full_name ?? 'Unknown'"
                                            size="sm"
                                        />
                                        <div>
                                            <div class="font-medium">{{ $booking->client?->full_name ?? 'Unknown Client' }}</div>
                                            @if($booking->client?->email)
                                                <div class="text-xs text-base-content/60">{{ $booking->client->email }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $booking->status_badge_class }} badge-sm">
                                        {{ \App\Models\Booking::getStatuses()[$booking->status] ?? $booking->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($booking->price_paid !== null)
                                        <div class="font-medium text-success">${{ number_format($booking->price_paid, 2) }}</div>
                                        @if($booking->payment_method)
                                            <div class="text-xs text-base-content/50 capitalize">{{ $booking->payment_method }}</div>
                                        @endif
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $intakeStatuses = \App\Models\Booking::getIntakeStatuses();
                                        $intakeIcons = [
                                            'completed' => 'icon-[tabler--circle-check-filled] text-success',
                                            'pending' => 'icon-[tabler--clock] text-warning',
                                            'waived' => 'icon-[tabler--circle-minus] text-info',
                                            'not_required' => 'icon-[tabler--minus] text-base-content/30',
                                        ];
                                    @endphp
                                    <div class="flex items-center justify-center gap-1">
                                        <span class="{{ $intakeIcons[$booking->intake_status] ?? 'icon-[tabler--minus] text-base-content/30' }} size-4" title="{{ $intakeStatuses[$booking->intake_status] ?? 'Unknown' }}"></span>
                                        <span class="text-xs text-base-content/60">{{ $intakeStatuses[$booking->intake_status] ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-center" id="checkin-cell-{{ $booking->id }}">
                                    @if($booking->isCheckedIn())
                                        <div class="flex items-center justify-center gap-1 text-success">
                                            <span class="icon-[tabler--circle-check-filled] size-5"></span>
                                            <span class="text-xs">{{ $booking->checked_in_at->format('g:i A') }}</span>
                                        </div>
                                    @else
                                        <span class="icon-[tabler--circle-dashed] size-5 text-base-content/30"></span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        @if(!$booking->isCheckedIn())
                                            <button
                                                type="button"
                                                class="btn btn-ghost btn-xs btn-square text-success hover:bg-success/10"
                                                id="checkin-btn-{{ $booking->id }}"
                                                onclick="checkInBooking({{ $booking->id }})"
                                                title="Check In"
                                            >
                                                <span class="icon-[tabler--login] size-4"></span>
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                class="btn btn-ghost btn-xs btn-square btn-disabled text-success"
                                                disabled
                                                title="Already Checked In"
                                            >
                                                <span class="icon-[tabler--check] size-4"></span>
                                            </button>
                                        @endif
                                        <a href="{{ route('bookings.show', $booking) }}" class="btn btn-ghost btn-xs btn-square" title="View Booking">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <x-slot name="footer">
        <a href="{{ route('class-sessions.edit', $classSession) }}" class="btn btn-soft btn-primary">
            <span class="icon-[tabler--edit] size-4 me-1"></span>
            Edit
        </a>
        <a href="{{ route('class-sessions.show', $classSession) }}" class="btn btn-primary">
            <span class="icon-[tabler--external-link] size-4 me-1"></span>
            View Full Details
        </a>
    </x-slot>
</x-detail-drawer>

@once
@push('scripts')
<script>
function checkInBooking(bookingId) {
    const btn = document.getElementById(`checkin-btn-${bookingId}`);
    const checkinCell = document.getElementById(`checkin-cell-${bookingId}`);
    if (!btn) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

    fetch(`/schedule/check-in/${bookingId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the check-in status cell
            if (checkinCell) {
                checkinCell.innerHTML = `
                    <div class="flex items-center justify-center gap-1 text-success">
                        <span class="icon-[tabler--circle-check-filled] size-5"></span>
                        <span class="text-xs">${data.checked_in_at}</span>
                    </div>
                `;
            }
            // Replace check-in button with disabled checkmark
            btn.outerHTML = `
                <button type="button" class="btn btn-ghost btn-xs btn-square btn-disabled text-success" disabled title="Already Checked In">
                    <span class="icon-[tabler--check] size-4"></span>
                </button>
            `;
        } else {
            btn.disabled = false;
            btn.innerHTML = '<span class="icon-[tabler--login] size-4"></span>';
            alert(data.message || 'Failed to check in');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = '<span class="icon-[tabler--login] size-4"></span>';
        alert('An error occurred. Please try again.');
    });
}

function checkInAll(classSessionId) {
    const buttons = document.querySelectorAll(`[id^="checkin-btn-"]`);
    buttons.forEach(btn => {
        const bookingId = btn.id.replace('checkin-btn-', '');
        checkInBooking(parseInt(bookingId));
    });
}
</script>
@endpush
@endonce
