@extends('layouts.subdomain')

@section('title', 'Manage Booking — ' . $host->studio_name)

@section('content')
@include('subdomain.partials.navbar')

<div class="min-h-screen flex flex-col bg-gradient-to-br from-base-200 via-base-100 to-base-200">
    {{-- Main Content --}}
    <div class="flex-1 py-8 md:py-12">
        <div class="container-fixed">
            <div class="max-w-2xl mx-auto">

                {{-- Page Header --}}
                <div class="text-center mb-8">
                    <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
                        <span class="icon-[tabler--calendar-user] size-8 text-primary"></span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">Manage Your Booking</h1>
                    <p class="text-base-content/60">View, reschedule, or cancel your meeting.</p>
                </div>

                {{-- Meeting Status --}}
                @if($booking->status === 'cancelled')
                <div class="alert alert-error mb-6">
                    <span class="icon-[tabler--calendar-off] size-5"></span>
                    <div>
                        <span class="font-medium">This meeting has been cancelled.</span>
                        <p class="text-sm mt-1">Cancelled on {{ $booking->cancelled_at?->format('M j, Y g:i A') }}</p>
                    </div>
                </div>
                @elseif($booking->start_time->isPast())
                <div class="alert alert-info mb-6">
                    <span class="icon-[tabler--clock] size-5"></span>
                    <span>This meeting has already taken place.</span>
                </div>
                @endif

                {{-- Meeting Details Card --}}
                <div class="card bg-base-100 shadow-lg mb-6">
                    <div class="card-body">
                        <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
                            <span class="icon-[tabler--calendar-event] size-5 text-primary"></span>
                            Meeting Details
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-sm text-base-content/60">With</label>
                                <p class="font-medium">{{ $profile->display_name ?? $instructor->name }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm text-base-content/60">Type</label>
                                @php
                                    $typeLabel = match($booking->meeting_type) {
                                        'in_person' => 'In-Person',
                                        'phone' => 'Phone Call',
                                        'video' => 'Video Call',
                                        default => ucfirst($booking->meeting_type),
                                    };
                                    $typeIcon = match($booking->meeting_type) {
                                        'in_person' => 'icon-[tabler--map-pin]',
                                        'phone' => 'icon-[tabler--phone]',
                                        'video' => 'icon-[tabler--video]',
                                        default => 'icon-[tabler--calendar]',
                                    };
                                @endphp
                                <p class="font-medium flex items-center gap-2">
                                    <span class="{{ $typeIcon }} size-4 text-primary"></span>
                                    {{ $typeLabel }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm text-base-content/60">Date</label>
                                <p class="font-medium">{{ $booking->start_time->format('l, F j, Y') }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm text-base-content/60">Time</label>
                                <p class="font-medium">{{ $booking->start_time->format('g:i A') }} - {{ $booking->end_time->format('g:i A') }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm text-base-content/60">Duration</label>
                                <p class="font-medium">{{ $booking->duration_minutes }} minutes</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm text-base-content/60">Status</label>
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
                        </div>

                        {{-- Meeting Type Details --}}
                        @if($booking->meeting_type === 'in_person' && $profile->in_person_location)
                        <div class="mt-4 p-4 bg-base-200 rounded-xl">
                            <div class="flex items-start gap-3">
                                <span class="icon-[tabler--map-pin] size-5 text-primary mt-0.5"></span>
                                <div>
                                    <label class="text-sm text-base-content/60">Location</label>
                                    <p class="font-medium">{{ $profile->in_person_location }}</p>
                                </div>
                            </div>
                        </div>
                        @elseif($booking->meeting_type === 'video' && $profile->video_link)
                        <div class="mt-4 p-4 bg-base-200 rounded-xl">
                            <div class="flex items-start gap-3">
                                <span class="icon-[tabler--video] size-5 text-primary mt-0.5"></span>
                                <div class="flex-1">
                                    <label class="text-sm text-base-content/60">Video Meeting Link</label>
                                    <a href="{{ $profile->video_link }}" target="_blank" class="block font-medium text-primary hover:underline break-all">
                                        Join Video Call
                                    </a>
                                </div>
                            </div>
                        </div>
                        @elseif($booking->meeting_type === 'phone' && $profile->phone_number)
                        <div class="mt-4 p-4 bg-base-200 rounded-xl">
                            <div class="flex items-start gap-3">
                                <span class="icon-[tabler--phone] size-5 text-primary mt-0.5"></span>
                                <div>
                                    <label class="text-sm text-base-content/60">Phone Number</label>
                                    <p class="font-medium">{{ $profile->phone_number }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                @if($booking->status === 'confirmed' && !$booking->start_time->isPast())
                <div class="card bg-base-100 shadow-lg mb-6">
                    <div class="card-body">
                        <h3 class="font-bold text-lg mb-4">Make Changes</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Reschedule --}}
                            @if($canReschedule)
                            <button type="button" class="btn btn-info btn-soft btn-lg flex flex-col items-center py-6 h-auto" onclick="openRescheduleModal()">
                                <span class="icon-[tabler--calendar-repeat] size-8 mb-2"></span>
                                <span class="font-semibold">Reschedule</span>
                                <span class="text-xs text-base-content/60">Pick a new time</span>
                            </button>
                            @else
                            <div class="btn btn-disabled btn-lg flex flex-col items-center py-6 h-auto">
                                <span class="icon-[tabler--calendar-repeat] size-8 mb-2"></span>
                                <span class="font-semibold">Reschedule</span>
                                <span class="text-xs">Past reschedule cutoff</span>
                            </div>
                            @endif

                            {{-- Cancel --}}
                            @if($canCancel)
                            <button type="button" class="btn btn-error btn-soft btn-lg flex flex-col items-center py-6 h-auto" onclick="openCancelModal()">
                                <span class="icon-[tabler--calendar-off] size-8 mb-2"></span>
                                <span class="font-semibold">Cancel</span>
                                <span class="text-xs text-base-content/60">Cancel this meeting</span>
                            </button>
                            @else
                            <div class="btn btn-disabled btn-lg flex flex-col items-center py-6 h-auto">
                                <span class="icon-[tabler--calendar-off] size-8 mb-2"></span>
                                <span class="font-semibold">Cancel</span>
                                <span class="text-xs">Past cancel cutoff</span>
                            </div>
                            @endif
                        </div>

                        {{-- Cutoff Info --}}
                        <div class="mt-4 text-sm text-base-content/60">
                            @if($profile->allow_reschedule)
                            <p>Rescheduling is available up to {{ $profile->reschedule_cutoff_hours }} hours before the meeting.</p>
                            @endif
                            @if($profile->allow_cancel)
                            <p>Cancellation is available up to {{ $profile->cancel_cutoff_hours }} hours before the meeting.</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- Back Button --}}
                <div class="text-center">
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost gap-2">
                        <span class="icon-[tabler--arrow-left] size-4"></span>
                        Back to Home
                    </a>
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
                <h3 class="modal-title">Cancel Meeting</h3>
                <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" onclick="closeCancelModal()">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-base-content/70">Are you sure you want to cancel this meeting?</p>
                <div class="mt-4">
                    <label class="label-text" for="cancel_reason">Reason (optional)</label>
                    <textarea id="cancel_reason" class="textarea w-full" rows="3" placeholder="Let us know why you're cancelling..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeCancelModal()">Keep Meeting</button>
                <button type="button" class="btn btn-error" id="confirm-cancel-btn" onclick="confirmCancel()">
                    <span class="loading loading-spinner loading-sm hidden"></span>
                    Cancel Meeting
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Reschedule Modal --}}
<div id="reschedule-modal" class="overlay modal overlay-open:opacity-100 hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered max-w-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Reschedule Meeting</h3>
                <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" onclick="closeRescheduleModal()">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
            <div class="modal-body">
                <div class="space-y-4">
                    <div>
                        <label class="label-text" for="reschedule_date">Select New Date</label>
                        <input type="date" id="reschedule_date" class="input w-full" min="{{ now()->format('Y-m-d') }}" onchange="loadAvailableSlots()">
                    </div>

                    <div id="slots-container" class="hidden">
                        <label class="label-text mb-2 block">Available Times</label>
                        <div id="slots-loading" class="text-center py-4 hidden">
                            <span class="loading loading-spinner loading-md"></span>
                        </div>
                        <div id="slots-list" class="grid grid-cols-3 gap-2"></div>
                        <div id="slots-empty" class="text-center py-4 text-base-content/60 hidden">
                            No available times for this date.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeRescheduleModal()">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-reschedule-btn" onclick="confirmReschedule()" disabled>
                    <span class="loading loading-spinner loading-sm hidden"></span>
                    Confirm New Time
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedTime = null;

function openCancelModal() {
    document.getElementById('cancel_reason').value = '';
    document.getElementById('cancel-modal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancel-modal').classList.add('hidden');
}

async function confirmCancel() {
    const btn = document.getElementById('confirm-cancel-btn');
    const spinner = btn.querySelector('.loading');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    const reason = document.getElementById('cancel_reason').value;

    try {
        const response = await fetch('{{ route("subdomain.meeting.cancel", ["subdomain" => $host->subdomain, "token" => $booking->manage_token]) }}', {
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
            alert(result.message || 'Failed to cancel booking');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
}

function openRescheduleModal() {
    document.getElementById('reschedule_date').value = '';
    document.getElementById('slots-container').classList.add('hidden');
    document.getElementById('slots-list').innerHTML = '';
    document.getElementById('confirm-reschedule-btn').disabled = true;
    selectedTime = null;
    document.getElementById('reschedule-modal').classList.remove('hidden');
}

function closeRescheduleModal() {
    document.getElementById('reschedule-modal').classList.add('hidden');
}

async function loadAvailableSlots() {
    const date = document.getElementById('reschedule_date').value;
    if (!date) return;

    const container = document.getElementById('slots-container');
    const loading = document.getElementById('slots-loading');
    const list = document.getElementById('slots-list');
    const empty = document.getElementById('slots-empty');

    container.classList.remove('hidden');
    loading.classList.remove('hidden');
    list.classList.add('hidden');
    empty.classList.add('hidden');
    list.innerHTML = '';
    selectedTime = null;
    document.getElementById('confirm-reschedule-btn').disabled = true;

    try {
        const response = await fetch('{{ route("subdomain.meeting.reschedule.availability", ["subdomain" => $host->subdomain, "token" => $booking->manage_token]) }}?date=' + date, {
            headers: {
                'Accept': 'application/json',
            },
        });

        const result = await response.json();

        if (response.ok && result.success && result.slots.length > 0) {
            result.slots.forEach(slot => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm btn-ghost';
                btn.textContent = slot.display;
                btn.onclick = () => selectSlot(slot.time, btn);
                list.appendChild(btn);
            });
            list.classList.remove('hidden');
        } else {
            empty.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
        empty.classList.remove('hidden');
    } finally {
        loading.classList.add('hidden');
    }
}

function selectSlot(time, btn) {
    selectedTime = time;

    // Update button states
    document.querySelectorAll('#slots-list button').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-ghost');
    });
    btn.classList.remove('btn-ghost');
    btn.classList.add('btn-primary');

    document.getElementById('confirm-reschedule-btn').disabled = false;
}

async function confirmReschedule() {
    if (!selectedTime) return;

    const btn = document.getElementById('confirm-reschedule-btn');
    const spinner = btn.querySelector('.loading');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    const date = document.getElementById('reschedule_date').value;

    try {
        const response = await fetch('{{ route("subdomain.meeting.reschedule", ["subdomain" => $host->subdomain, "token" => $booking->manage_token]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ date, time: selectedTime }),
        });

        const result = await response.json();

        if (response.ok && result.success) {
            if (result.manage_url) {
                window.location.href = result.manage_url;
            } else {
                window.location.reload();
            }
        } else {
            alert(result.message || 'Failed to reschedule');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
}
</script>
@endpush
@endsection
