@extends('layouts.dashboard')

@section('title', 'Booking Details')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('one-on-one.index') }}">{{ $isOwner ? 'All 1:1 Bookings' : 'My 1:1 Bookings' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Booking Details</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('one-on-one.index') }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold">Booking Details</h1>
            <p class="text-base-content/60 mt-1">
                Meeting with {{ $booking->guest_first_name }} {{ $booking->guest_last_name }}
            </p>
        </div>
        @php
            $statusBadge = match($booking->status) {
                'confirmed' => 'badge-success',
                'completed' => 'badge-info',
                'cancelled' => 'badge-error',
                'no_show' => 'badge-warning',
                default => 'badge-ghost',
            };
        @endphp
        <span class="badge {{ $statusBadge }} badge-soft">
            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
        </span>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-soft alert-success">
        <span class="icon-[tabler--check] size-5"></span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Meeting Info --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title flex items-center gap-2">
                        <span class="icon-[tabler--calendar-event] size-5"></span>
                        Meeting Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-sm text-base-content/60">Date</label>
                            <p class="font-medium text-lg">{{ $booking->start_time->format('l, F j, Y') }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm text-base-content/60">Time</label>
                            <p class="font-medium text-lg">{{ $booking->start_time->format('g:i A') }} - {{ $booking->end_time->format('g:i A') }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm text-base-content/60">Duration</label>
                            <p class="font-medium">{{ $booking->duration_minutes }} minutes</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm text-base-content/60">Meeting Type</label>
                            @php
                                $typeIcon = match($booking->meeting_type) {
                                    'in_person' => 'icon-[tabler--map-pin]',
                                    'phone' => 'icon-[tabler--phone]',
                                    'video' => 'icon-[tabler--video]',
                                    default => 'icon-[tabler--calendar]',
                                };
                                $typeLabel = match($booking->meeting_type) {
                                    'in_person' => 'In-Person',
                                    'phone' => 'Phone Call',
                                    'video' => 'Video Call',
                                    default => ucfirst($booking->meeting_type),
                                };
                            @endphp
                            <p class="font-medium flex items-center gap-2">
                                <span class="{{ $typeIcon }} size-5 text-primary"></span>
                                {{ $typeLabel }}
                            </p>
                        </div>
                    </div>

                    {{-- Meeting Details based on type --}}
                    @if($booking->meeting_type === 'in_person' && $profile->in_person_location)
                    <div class="mt-6 p-4 bg-base-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <span class="icon-[tabler--map-pin] size-5 text-primary mt-0.5"></span>
                            <div>
                                <label class="text-sm text-base-content/60">Location</label>
                                <p class="font-medium mt-1">{{ $profile->in_person_location }}</p>
                            </div>
                        </div>
                    </div>
                    @elseif($booking->meeting_type === 'video' && $profile->video_link)
                    <div class="mt-6 p-4 bg-base-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <span class="icon-[tabler--video] size-5 text-primary mt-0.5"></span>
                            <div class="flex-1">
                                <label class="text-sm text-base-content/60">Video Meeting Link</label>
                                <div class="flex items-center gap-2 mt-1">
                                    <a href="{{ $profile->video_link }}" target="_blank" class="font-medium text-primary hover:underline break-all">
                                        {{ $profile->video_link }}
                                    </a>
                                    <button type="button" onclick="copyToClipboard('{{ $profile->video_link }}')" class="btn btn-ghost btn-xs">
                                        <span class="icon-[tabler--copy] size-4"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($booking->meeting_type === 'phone' && $profile->phone_number)
                    <div class="mt-6 p-4 bg-base-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <span class="icon-[tabler--phone] size-5 text-primary mt-0.5"></span>
                            <div>
                                <label class="text-sm text-base-content/60">Phone Number</label>
                                <p class="font-medium mt-1">{{ $profile->phone_number }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Guest Info --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title flex items-center gap-2">
                        <span class="icon-[tabler--user] size-5"></span>
                        Guest Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="flex items-start gap-4">
                        <div class="avatar avatar-placeholder">
                            <div class="bg-primary text-primary-content w-16 h-16 rounded-full font-bold text-xl">
                                {{ strtoupper(substr($booking->guest_first_name, 0, 1) . substr($booking->guest_last_name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-1 space-y-3">
                            <div>
                                <label class="text-sm text-base-content/60">Name</label>
                                <p class="font-medium text-lg">{{ $booking->guest_first_name }} {{ $booking->guest_last_name }}</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-base-content/60">Email</label>
                                    <p class="font-medium">
                                        <a href="mailto:{{ $booking->guest_email }}" class="text-primary hover:underline">
                                            {{ $booking->guest_email }}
                                        </a>
                                    </p>
                                </div>
                                @if($booking->guest_phone)
                                <div>
                                    <label class="text-sm text-base-content/60">Phone</label>
                                    <p class="font-medium">
                                        <a href="tel:{{ $booking->guest_phone }}" class="text-primary hover:underline">
                                            {{ $booking->guest_phone }}
                                        </a>
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($booking->guest_notes)
                    <div class="mt-6 p-4 bg-base-200 rounded-lg">
                        <label class="text-sm text-base-content/60">Notes from Guest</label>
                        <p class="mt-1">{{ $booking->guest_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Cancellation Info (if cancelled) --}}
            @if($booking->status === 'cancelled')
            <div class="card bg-error/5 border border-error/20">
                <div class="card-body">
                    <h3 class="font-semibold flex items-center gap-2 text-error">
                        <span class="icon-[tabler--calendar-off] size-5"></span>
                        Cancellation Details
                    </h3>
                    <div class="mt-4 space-y-2">
                        <p><span class="text-base-content/60">Cancelled:</span> {{ $booking->cancelled_at?->format('M j, Y g:i A') }}</p>
                        <p><span class="text-base-content/60">Cancelled by:</span> {{ ucfirst($booking->cancelled_by) }}</p>
                        @if($booking->cancellation_reason)
                        <p><span class="text-base-content/60">Reason:</span> {{ $booking->cancellation_reason }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Actions --}}
            @if($booking->status === 'confirmed')
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body space-y-2">
                    @if($booking->start_time->isPast())
                    <form action="{{ route('one-on-one.complete', $booking) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-full">
                            <span class="icon-[tabler--check] size-5"></span>
                            Mark as Completed
                        </button>
                    </form>
                    <form action="{{ route('one-on-one.no-show', $booking) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-soft w-full">
                            <span class="icon-[tabler--user-off] size-5"></span>
                            Mark as No-Show
                        </button>
                    </form>
                    @else
                    <button type="button" class="btn btn-error btn-soft w-full" onclick="openCancelModal()">
                        <span class="icon-[tabler--x] size-5"></span>
                        Cancel Booking
                    </button>
                    @endif
                </div>
            </div>
            @endif

            {{-- Team Member Info (for owners) --}}
            @if($isOwner && $instructor)
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Team Member</h3>
                </div>
                <div class="card-body">
                    <div class="flex items-center gap-3">
                        @if($instructor->photo_url)
                            <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}" class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div class="avatar avatar-placeholder">
                                <div class="bg-secondary text-secondary-content w-12 h-12 rounded-full font-bold">
                                    {{ strtoupper(substr($instructor->name, 0, 1)) }}
                                </div>
                            </div>
                        @endif
                        <div>
                            <p class="font-medium">{{ $instructor->name }}</p>
                            @if($instructor->email)
                            <p class="text-sm text-base-content/60">{{ $instructor->email }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Booking Meta --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Booking Info</h3>
                </div>
                <div class="card-body space-y-3">
                    <div>
                        <label class="text-sm text-base-content/60">Booked At</label>
                        <p class="font-medium">{{ $booking->booked_at?->format('M j, Y g:i A') }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-base-content/60">Timezone</label>
                        <p class="font-medium">{{ $booking->timezone }}</p>
                    </div>
                    @if($booking->reschedule_count > 0)
                    <div>
                        <label class="text-sm text-base-content/60">Times Rescheduled</label>
                        <p class="font-medium">{{ $booking->reschedule_count }}</p>
                    </div>
                    @endif
                </div>
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
            <form action="{{ route('one-on-one.cancel', $booking) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-base-content/70">Are you sure you want to cancel this booking? The guest will be notified via email.</p>
                    <div class="mt-4">
                        <label class="label-text" for="reason">Reason (optional)</label>
                        <textarea id="reason" name="reason" class="textarea w-full" rows="3" placeholder="Provide a reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" onclick="closeCancelModal()">Keep Booking</button>
                    <button type="submit" class="btn btn-error">Cancel Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        if (typeof Notyf !== 'undefined') {
            new Notyf().success('Copied to clipboard!');
        }
    });
}

function openCancelModal() {
    document.getElementById('cancel-modal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancel-modal').classList.add('hidden');
}
</script>
@endpush
@endsection
