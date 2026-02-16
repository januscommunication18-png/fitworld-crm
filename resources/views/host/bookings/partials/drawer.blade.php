{{-- Booking Details Drawer --}}
{{-- Usage: @include('host.bookings.partials.drawer', ['booking' => $booking]) --}}

@php
    $statuses = $statuses ?? \App\Models\Booking::getStatuses();
    $sources = $sources ?? \App\Models\Booking::getBookingSources();
    $paymentMethods = $paymentMethods ?? \App\Models\Booking::getPaymentMethods();
@endphp

<x-detail-drawer id="booking-{{ $booking->id }}" title="Booking #{{ $booking->id }}" size="4xl">
    {{-- Status Hero Section --}}
    <div class="bg-gradient-to-r from-primary/10 to-primary/5 rounded-xl p-4 mb-5 -mt-1">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center">
                    <span class="icon-[tabler--ticket] size-6 text-primary"></span>
                </div>
                <div>
                    <div class="text-xs text-base-content/60 uppercase tracking-wide">Status</div>
                    <span class="badge {{ $booking->status_badge_class }} mt-1">
                        {{ $statuses[$booking->status] ?? $booking->status }}
                    </span>
                </div>
            </div>
            @if($booking->isCheckedIn())
                <div class="flex items-center gap-2 bg-success/20 text-success px-3 py-2 rounded-lg">
                    <span class="icon-[tabler--circle-check-filled] size-5"></span>
                    <span class="font-medium text-sm">Checked In</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Client Card --}}
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--user] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">Client</h4>
        </div>
        @if($booking->client)
            <div class="flex items-center gap-4">
                <x-avatar :src="$booking->client->avatar_url" :initials="$booking->client->initials" :alt="$booking->client->full_name" size="lg" />
                <div class="flex-1">
                    <div class="font-semibold text-lg">{{ $booking->client->full_name }}</div>
                    @if($booking->client->email)
                        <div class="flex items-center gap-2 text-sm text-base-content/70 mt-1">
                            <span class="icon-[tabler--mail] size-4"></span>
                            {{ $booking->client->email }}
                        </div>
                    @endif
                    @if($booking->client->phone)
                        <div class="flex items-center gap-2 text-sm text-base-content/70 mt-0.5">
                            <span class="icon-[tabler--phone] size-4"></span>
                            {{ $booking->client->phone }}
                        </div>
                    @endif
                </div>
                @if($booking->client)
                    <a href="{{ route('clients.show', $booking->client) }}" class="btn btn-ghost btn-sm btn-circle" title="View Client">
                        <span class="icon-[tabler--chevron-right] size-5"></span>
                    </a>
                @endif
            </div>
        @else
            <div class="flex items-center gap-3 text-base-content/50">
                <span class="icon-[tabler--user-off] size-8"></span>
                <span>Unknown Client</span>
            </div>
        @endif
    </div>

    {{-- Session Card --}}
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--calendar-event] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">Session Details</h4>
        </div>
        @if($booking->bookable)
            <div class="font-semibold text-lg mb-3">{{ $booking->bookable->display_title ?? $booking->bookable->title ?? 'Class Session' }}</div>
            <div class="grid grid-cols-2 gap-3">
                @if($booking->bookable->start_time)
                    <div class="bg-base-100 rounded-lg p-3">
                        <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                            <span class="icon-[tabler--calendar] size-3.5"></span>
                            Date
                        </div>
                        <div class="font-medium">{{ $booking->bookable->start_time->format('M j, Y') }}</div>
                        <div class="text-sm text-base-content/70">{{ $booking->bookable->start_time->format('l') }}</div>
                    </div>
                    <div class="bg-base-100 rounded-lg p-3">
                        <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                            <span class="icon-[tabler--clock] size-3.5"></span>
                            Time
                        </div>
                        <div class="font-medium">{{ $booking->bookable->start_time->format('g:i A') }}</div>
                        @if($booking->bookable->end_time)
                            <div class="text-sm text-base-content/70">to {{ $booking->bookable->end_time->format('g:i A') }}</div>
                        @endif
                    </div>
                @endif
                @if($booking->bookable->primaryInstructor)
                    <div class="bg-base-100 rounded-lg p-3">
                        <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                            <span class="icon-[tabler--user-star] size-3.5"></span>
                            Instructor
                        </div>
                        <div class="font-medium">{{ $booking->bookable->primaryInstructor->name }}</div>
                    </div>
                @endif
                @if($booking->bookable->location)
                    <div class="bg-base-100 rounded-lg p-3">
                        <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                            <span class="icon-[tabler--map-pin] size-3.5"></span>
                            Location
                        </div>
                        <div class="font-medium">{{ $booking->bookable->location->name }}</div>
                    </div>
                @endif
            </div>
        @else
            <div class="flex items-center gap-3 text-base-content/50">
                <span class="icon-[tabler--calendar-off] size-8"></span>
                <span>Session Deleted</span>
            </div>
        @endif
    </div>

    {{-- Payment & Info Grid --}}
    <div class="grid grid-cols-2 gap-4">
        {{-- Payment Card --}}
        <div class="bg-base-200/50 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="icon-[tabler--credit-card] size-4 text-primary"></span>
                <h4 class="text-sm font-semibold uppercase tracking-wide">Payment</h4>
            </div>
            <div class="space-y-3">
                @if($booking->price_paid)
                    <div class="text-center py-2 bg-base-100 rounded-lg">
                        <div class="text-2xl font-bold text-primary">${{ number_format($booking->price_paid, 2) }}</div>
                        <div class="text-xs text-base-content/60">Amount Paid</div>
                    </div>
                @endif
                <div class="flex items-center justify-between text-sm">
                    <span class="text-base-content/60">Method</span>
                    <span class="badge badge-sm {{ $booking->payment_method_badge_class }} badge-soft">
                        {{ $paymentMethods[$booking->payment_method] ?? $booking->payment_method }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-base-content/60">Source</span>
                    <span class="badge badge-sm {{ $booking->source_badge_class }} badge-soft">
                        {{ $sources[$booking->booking_source] ?? $booking->booking_source }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Booking Info Card --}}
        <div class="bg-base-200/50 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="icon-[tabler--info-circle] size-4 text-primary"></span>
                <h4 class="text-sm font-semibold uppercase tracking-wide">Info</h4>
            </div>
            <div class="space-y-3">
                <div>
                    <div class="text-xs text-base-content/60 mb-1">Booked At</div>
                    <div class="font-medium text-sm">{{ $booking->booked_at?->format('M j, Y') ?? $booking->created_at->format('M j, Y') }}</div>
                    <div class="text-xs text-base-content/60">{{ $booking->booked_at?->format('g:i A') ?? $booking->created_at->format('g:i A') }}</div>
                </div>
                @if($booking->checked_in_at)
                    <div class="pt-2 border-t border-base-300">
                        <div class="text-xs text-base-content/60 mb-1">Checked In</div>
                        <div class="font-medium text-sm text-success">{{ $booking->checked_in_at->format('M j, Y') }}</div>
                        <div class="text-xs text-success/70">{{ $booking->checked_in_at->format('g:i A') }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex items-center gap-2">
            @if($booking->canBeCancelled())
                <button type="button" class="btn btn-soft btn-error" onclick="openCancelModal({{ $booking->id }}, {{ $booking->isLateCancellation() ? 'true' : 'false' }})">
                    <span class="icon-[tabler--x] size-4 me-1"></span>
                    Cancel Booking
                </button>
            @endif
        </div>
        <a href="{{ route('bookings.show', $booking) }}" class="btn btn-primary">
            <span class="icon-[tabler--external-link] size-4 me-1"></span>
            View Full Details
        </a>
    </x-slot>
</x-detail-drawer>
