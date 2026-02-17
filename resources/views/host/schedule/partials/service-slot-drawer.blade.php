{{-- Service Slot Drawer --}}
{{-- Usage: @include('host.schedule.partials.service-slot-drawer', ['serviceSlot' => $serviceSlot]) --}}

@php
    $statuses = \App\Models\ServiceSlot::getStatuses();
    $statusColors = [
        'available' => 'from-success/10 to-success/5',
        'booked' => 'from-info/10 to-info/5',
        'blocked' => 'from-neutral/10 to-neutral/5',
        'draft' => 'from-warning/10 to-warning/5',
        'cancelled' => 'from-error/10 to-error/5',
    ];
    $statusIconColors = [
        'available' => 'bg-success/20 text-success',
        'booked' => 'bg-info/20 text-info',
        'blocked' => 'bg-neutral/20 text-neutral',
        'draft' => 'bg-warning/20 text-warning',
        'cancelled' => 'bg-error/20 text-error',
    ];
    $booking = $serviceSlot->bookings->first();
@endphp

<x-detail-drawer id="service-slot-{{ $serviceSlot->id }}" title="{{ $serviceSlot->servicePlan?->name ?? 'Service Slot' }}" size="4xl">
    {{-- Status Hero Section --}}
    <div class="bg-gradient-to-r {{ $statusColors[$serviceSlot->status] ?? 'from-primary/10 to-primary/5' }} rounded-xl p-4 mb-5 -mt-1">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full {{ $statusIconColors[$serviceSlot->status] ?? 'bg-primary/20 text-primary' }} flex items-center justify-center">
                    <span class="icon-[tabler--massage] size-7"></span>
                </div>
                <div>
                    <div class="font-semibold text-lg">{{ $serviceSlot->start_time->format('l, M j') }}</div>
                    <div class="text-base-content/70">{{ $serviceSlot->formatted_time_range }}</div>
                    <span class="badge {{ $serviceSlot->getStatusBadgeClass() }} mt-1">
                        {{ $statuses[$serviceSlot->status] ?? $serviceSlot->status }}
                    </span>
                </div>
            </div>
            @if($serviceSlot->status === \App\Models\ServiceSlot::STATUS_BOOKED)
                <div class="flex items-center gap-2 bg-info/20 text-info px-3 py-2 rounded-lg">
                    <span class="icon-[tabler--calendar-check] size-5"></span>
                    <span class="font-medium text-sm">Booked</span>
                </div>
            @elseif($serviceSlot->status === \App\Models\ServiceSlot::STATUS_AVAILABLE)
                <div class="flex items-center gap-2 bg-success/20 text-success px-3 py-2 rounded-lg">
                    <span class="icon-[tabler--calendar-plus] size-5"></span>
                    <span class="font-medium text-sm">Available</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Service Details Card --}}
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--info-circle] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">Service Details</h4>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-base-100 rounded-lg p-3">
                <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                    <span class="icon-[tabler--user] size-3.5"></span>
                    Provider
                </div>
                <div class="font-medium text-sm">{{ $serviceSlot->instructor?->name ?? 'TBD' }}</div>
            </div>
            <div class="bg-base-100 rounded-lg p-3">
                <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                    <span class="icon-[tabler--map-pin] size-3.5"></span>
                    Location
                </div>
                <div class="font-medium text-sm">{{ $serviceSlot->location?->name ?? 'TBD' }}</div>
                @if($serviceSlot->room)
                    <div class="text-xs text-base-content/60">{{ $serviceSlot->room->name }}</div>
                @endif
            </div>
            <div class="bg-base-100 rounded-lg p-3">
                <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                    <span class="icon-[tabler--clock] size-3.5"></span>
                    Duration
                </div>
                <div class="font-medium text-sm">{{ $serviceSlot->duration_minutes }} min</div>
            </div>
            <div class="bg-base-100 rounded-lg p-3">
                <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                    <span class="icon-[tabler--currency-dollar] size-3.5"></span>
                    Price
                </div>
                <div class="font-medium text-sm">{{ $serviceSlot->formatted_price }}</div>
            </div>
        </div>
    </div>

    {{-- Client Card (if booked) --}}
    @if($booking && $booking->client)
        <div class="bg-base-200/50 rounded-xl p-4 mb-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="icon-[tabler--user] size-4 text-primary"></span>
                <h4 class="text-sm font-semibold uppercase tracking-wide">Client</h4>
            </div>
            <div class="flex items-center gap-4">
                <x-avatar
                    :src="$booking->client->avatar_url ?? null"
                    :initials="$booking->client->initials"
                    :alt="$booking->client->full_name"
                    size="lg"
                />
                <div class="flex-1">
                    <div class="font-semibold text-lg">{{ $booking->client->full_name }}</div>
                    @if($booking->client->email)
                        <div class="flex items-center gap-2 text-sm text-base-content/70 mt-1">
                            <span class="icon-[tabler--mail] size-4"></span>
                            {{ $booking->client->email }}
                        </div>
                    @endif
                    @if($booking->client->phone)
                        <div class="flex items-center gap-2 text-sm text-base-content/70 mt-1">
                            <span class="icon-[tabler--phone] size-4"></span>
                            {{ $booking->client->phone }}
                        </div>
                    @endif
                </div>
                <a href="{{ route('clients.show', $booking->client) }}" class="btn btn-ghost btn-sm btn-circle" title="View Client">
                    <span class="icon-[tabler--chevron-right] size-5"></span>
                </a>
            </div>

            {{-- Check-in status --}}
            <div class="mt-4 pt-4 border-t border-base-300">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="icon-[tabler--user-check] size-4 text-base-content/60"></span>
                        <span class="text-sm text-base-content/60">Check-in Status</span>
                    </div>
                    @if($booking->isCheckedIn())
                        <div class="flex items-center gap-2 text-success">
                            <span class="icon-[tabler--circle-check-filled] size-5"></span>
                            <span class="text-sm font-medium">Checked in at {{ $booking->checked_in_at->format('g:i A') }}</span>
                        </div>
                    @else
                        <button
                            type="button"
                            class="btn btn-sm btn-success"
                            id="service-checkin-btn-{{ $booking->id }}"
                            onclick="checkInBooking({{ $booking->id }})"
                        >
                            <span class="icon-[tabler--check] size-4"></span>
                            Check In
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @elseif($serviceSlot->status === \App\Models\ServiceSlot::STATUS_AVAILABLE)
        <div class="bg-base-200/50 rounded-xl p-4 mb-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="icon-[tabler--user] size-4 text-primary"></span>
                <h4 class="text-sm font-semibold uppercase tracking-wide">Client</h4>
            </div>
            <div class="text-center py-6">
                <span class="icon-[tabler--user-plus] size-10 text-base-content/20 mx-auto mb-2"></span>
                <p class="text-sm text-base-content/60 mb-3">This slot is available for booking</p>
                <a href="{{ route('walk-in.service', $serviceSlot) }}" class="btn btn-sm btn-primary">
                    <span class="icon-[tabler--user-plus] size-4"></span>
                    Book Walk-In
                </a>
            </div>
        </div>
    @endif

    {{-- Quick Actions --}}
    @if(!$serviceSlot->isCancelled())
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--bolt] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">Quick Actions</h4>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($serviceSlot->isDraft())
                <form action="{{ route('service-slots.update', $serviceSlot) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="available">
                    <button type="submit" class="btn btn-success btn-sm">
                        <span class="icon-[tabler--check] size-4"></span>
                        Make Available
                    </button>
                </form>
            @endif
            @if($serviceSlot->status === \App\Models\ServiceSlot::STATUS_AVAILABLE)
                <a href="{{ route('walk-in.service', $serviceSlot) }}" class="btn btn-soft btn-primary btn-sm">
                    <span class="icon-[tabler--user-plus] size-4"></span>
                    Walk-In Booking
                </a>
            @endif
        </div>
    </div>
    @endif

    {{-- Notes --}}
    @if($serviceSlot->notes)
    <div class="bg-base-200/50 rounded-xl p-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--notes] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">Notes</h4>
        </div>
        <p class="text-sm text-base-content/70">{{ $serviceSlot->notes }}</p>
    </div>
    @endif

    <x-slot name="footer">
        <a href="{{ route('service-slots.edit', $serviceSlot) }}" class="btn btn-soft btn-primary">
            <span class="icon-[tabler--edit] size-4 me-1"></span>
            Edit
        </a>
        <a href="{{ route('service-slots.show', $serviceSlot) }}" class="btn btn-primary">
            <span class="icon-[tabler--external-link] size-4 me-1"></span>
            View Full Details
        </a>
    </x-slot>
</x-detail-drawer>
