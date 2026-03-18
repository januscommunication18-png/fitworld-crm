{{-- Event Drawer --}}
{{-- Usage: @include('host.schedule.partials.event-drawer', ['event' => $event]) --}}

@php
    $statusColors = [
        'draft' => 'from-warning/10 to-warning/5',
        'published' => 'from-error/10 to-error/5',
        'cancelled' => 'from-base-300/50 to-base-300/30',
        'completed' => 'from-info/10 to-info/5',
    ];
    $statusIconColors = [
        'draft' => 'bg-warning/20 text-warning',
        'published' => 'bg-error/20 text-error',
        'cancelled' => 'bg-base-300 text-base-content/50',
        'completed' => 'bg-info/20 text-info',
    ];
    $statusBadgeColors = [
        'draft' => 'badge-warning',
        'published' => 'badge-success',
        'cancelled' => 'badge-error',
        'completed' => 'badge-info',
    ];
    // registeredAttendees returns EventAttendee models, eager load client
    $attendees = $event->registeredAttendees()->with('client')->get();
    $checkedInCount = $attendees->filter(fn($a) => $a->checked_in_at !== null)->count();
@endphp

<x-detail-drawer id="event-{{ $event->id }}" title="{{ $event->title }}" size="5xl">
    {{-- Combined Hero Section with Stats & Details --}}
    <div class="bg-gradient-to-r {{ $statusColors[$event->status] ?? 'from-error/10 to-error/5' }} rounded-xl p-4 mb-4 -mt-1">
        {{-- Top Row: Info + Actions --}}
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full {{ $statusIconColors[$event->status] ?? 'bg-error/20 text-error' }} flex items-center justify-center">
                    <span class="icon-[tabler--calendar-event] size-6"></span>
                </div>
                <div>
                    <div class="font-semibold text-lg">{{ $event->start_datetime->format('l, M j') }}</div>
                    <div class="text-base-content/70">{{ $event->start_datetime->format('g:i A') }} - {{ $event->end_datetime->format('g:i A') }}</div>
                </div>
                <span class="badge {{ $statusBadgeColors[$event->status] ?? 'badge-error' }}">
                    {{ ucfirst($event->status) }}
                </span>
                <span class="badge badge-soft badge-sm capitalize">
                    @if($event->event_type === 'in_person')
                        <span class="icon-[tabler--map-pin] size-3 mr-1"></span> In-Person
                    @elseif($event->event_type === 'online')
                        <span class="icon-[tabler--device-laptop] size-3 mr-1"></span> Online
                    @else
                        <span class="icon-[tabler--arrows-exchange] size-3 mr-1"></span> Hybrid
                    @endif
                </span>
            </div>
            {{-- Quick Actions on right --}}
            @if($event->status !== 'cancelled')
            <div class="flex flex-wrap gap-2 justify-end">
                @if($event->status === 'draft')
                    <form action="{{ route('events.publish', $event) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success btn-sm">
                            <span class="icon-[tabler--send] size-4"></span>
                            {{ $trans['btn.publish'] ?? 'Publish' }}
                        </button>
                    </form>
                @endif
                @if($event->canAddAttendees())
                    <a href="{{ route('walk-in.event', $event) }}" class="btn btn-soft btn-error btn-sm">
                        <span class="icon-[tabler--user-plus] size-4"></span>
                        {{ $trans['btn.add_attendee'] ?? 'Add Attendee' }}
                    </a>
                @endif
            </div>
            @endif
        </div>

        {{-- Stats + Details Row --}}
        <div class="grid grid-cols-6 gap-2 pt-3 border-t border-base-content/10">
            {{-- Stats --}}
            <div class="text-center">
                <div class="text-lg font-bold text-error">{{ $event->capacity ?? '∞' }}</div>
                <div class="text-xs text-base-content/60">{{ $trans['schedule.capacity'] ?? 'Capacity' }}</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-info">{{ $attendees->count() }}</div>
                <div class="text-xs text-base-content/60">{{ $trans['events.registered'] ?? 'Registered' }}</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-success">{{ $checkedInCount }}</div>
                <div class="text-xs text-base-content/60">{{ $trans['status.checked_in'] ?? 'Checked In' }}</div>
            </div>
            <div class="text-center">
                @php
                    $available = $event->capacity ? max(0, $event->capacity - $attendees->count()) : '∞';
                @endphp
                <div class="text-lg font-bold">{{ $available }}</div>
                <div class="text-xs text-base-content/60">{{ $trans['schedule.available'] ?? 'Available' }}</div>
            </div>
            {{-- Details --}}
            <div class="text-center">
                <div class="text-sm font-medium truncate">{{ $event->venue_name ?: ($event->event_type === 'online' ? 'Online' : 'TBD') }}</div>
                <div class="text-xs text-base-content/60 flex items-center justify-center gap-1">
                    <span class="icon-[tabler--map-pin] size-3"></span>
                    {{ $trans['field.venue'] ?? 'Venue' }}
                </div>
            </div>
            <div class="text-center">
                <div class="text-sm font-medium capitalize">{{ $event->visibility }}</div>
                <div class="text-xs text-base-content/60 flex items-center justify-center gap-1">
                    <span class="icon-[tabler--eye] size-3"></span>
                    {{ $trans['field.visibility'] ?? 'Visibility' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Attendees Section --}}
    <div class="bg-base-200/50 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="icon-[tabler--users] size-4 text-error"></span>
                <h4 class="text-sm font-semibold uppercase tracking-wide">{{ $trans['events.attendees'] ?? 'Attendees' }}</h4>
                <span class="badge badge-sm badge-error">{{ $attendees->count() }}</span>
            </div>
            @if($attendees->count() > 0 && $checkedInCount < $attendees->count())
                <button type="button" class="btn btn-xs btn-success" onclick="checkInAllEventAttendees({{ $event->id }})">
                    <span class="icon-[tabler--checks] size-3"></span>
                    {{ $trans['btn.check_in_all'] ?? 'Check In All' }}
                </button>
            @endif
        </div>

        @if($attendees->isEmpty())
            <div class="text-center py-6">
                <span class="icon-[tabler--users-minus] size-10 text-base-content/20 mx-auto mb-2"></span>
                <p class="text-sm text-base-content/60">{{ $trans['events.no_attendees'] ?? 'No attendees yet' }}</p>
                @if($event->canAddAttendees())
                    <a href="{{ route('walk-in.event', $event) }}" class="btn btn-sm btn-error mt-3">
                        <span class="icon-[tabler--user-plus] size-4"></span>
                        {{ $trans['btn.add_attendee'] ?? 'Add Attendee' }}
                    </a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                <table class="table table-sm">
                    <thead class="sticky top-0 bg-base-200/50 z-10">
                        <tr>
                            <th>{{ $trans['field.client'] ?? 'Attendee' }}</th>
                            <th class="text-center">{{ $trans['field.status'] ?? 'Status' }}</th>
                            <th class="text-center">{{ $trans['field.registered_at'] ?? 'Registered' }}</th>
                            <th class="text-center">{{ $trans['btn.check_in'] ?? 'Check In' }}</th>
                            <th class="w-24">{{ $trans['common.actions'] ?? 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendees as $attendee)
                            @php $client = $attendee->client; @endphp
                            @if($client)
                            <tr id="event-attendee-row-{{ $event->id }}-{{ $client->id }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <x-avatar
                                            :src="$client->avatar_url ?? null"
                                            :initials="$client->initials ?? '?'"
                                            :alt="$client->full_name ?? 'Unknown'"
                                            size="sm"
                                        />
                                        <div>
                                            <div class="font-medium">{{ $client->full_name ?? 'Unknown Attendee' }}</div>
                                            @if($client->email)
                                                <div class="text-xs text-base-content/60">{{ $client->email }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @php
                                        $attendeeStatus = $attendee->status ?? 'registered';
                                        $statusBadge = match($attendeeStatus) {
                                            'registered' => 'badge-info',
                                            'confirmed' => 'badge-primary',
                                            'attended' => 'badge-success',
                                            'no_show' => 'badge-error',
                                            'cancelled' => 'badge-ghost',
                                            'waitlisted' => 'badge-warning',
                                            default => 'badge-ghost'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusBadge }} badge-sm capitalize">
                                        {{ str_replace('_', ' ', $attendeeStatus) }}
                                    </span>
                                </td>
                                <td class="text-center text-sm">
                                    {{ $attendee->registered_at ? $attendee->registered_at->format('M j, g:i A') : '-' }}
                                </td>
                                <td class="text-center" id="event-checkin-cell-{{ $event->id }}-{{ $client->id }}">
                                    @if($attendee->checked_in_at)
                                        <div class="flex items-center justify-center gap-1 text-success">
                                            <span class="icon-[tabler--circle-check-filled] size-5"></span>
                                            <span class="text-xs">{{ $attendee->checked_in_at->format('g:i A') }}</span>
                                        </div>
                                    @else
                                        <span class="icon-[tabler--circle-dashed] size-5 text-base-content/30"></span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        @if(!$attendee->checked_in_at)
                                            <button
                                                type="button"
                                                class="btn btn-ghost btn-xs btn-square text-success hover:bg-success/10"
                                                id="event-checkin-btn-{{ $event->id }}-{{ $client->id }}"
                                                onclick="checkInEventAttendee({{ $event->id }}, {{ $client->id }})"
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
                                        <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost btn-xs btn-square" title="View Client">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                        <form action="{{ route('events.removeClient', [$event, $client]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this attendee from the event?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-xs btn-square text-error hover:bg-error/10" title="Remove">
                                                <span class="icon-[tabler--user-minus] size-4"></span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <x-slot name="footer">
        <a href="{{ route('events.edit', $event) }}" class="btn btn-soft btn-primary">
            <span class="icon-[tabler--edit] size-4 me-1"></span>
            {{ $trans['btn.edit'] ?? 'Edit' }}
        </a>
        <a href="{{ route('events.show', $event) }}" class="btn btn-primary">
            <span class="icon-[tabler--external-link] size-4 me-1"></span>
            {{ $trans['btn.view_full_details'] ?? 'View Full Details' }}
        </a>
    </x-slot>
</x-detail-drawer>

@once
@push('scripts')
<script>
function checkInEventAttendee(eventId, clientId) {
    const btn = document.getElementById(`event-checkin-btn-${eventId}-${clientId}`);
    const checkinCell = document.getElementById(`event-checkin-cell-${eventId}-${clientId}`);
    if (!btn) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

    fetch(`/events/${eventId}/clients/${clientId}/check-in`, {
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

function checkInAllEventAttendees(eventId) {
    const buttons = document.querySelectorAll(`[id^="event-checkin-btn-${eventId}-"]`);
    buttons.forEach(btn => {
        const parts = btn.id.split('-');
        const clientId = parseInt(parts[parts.length - 1]);
        checkInEventAttendee(eventId, clientId);
    });
}
</script>
@endpush
@endonce
