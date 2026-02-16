{{-- Cancel Booking Modal --}}
{{-- Usage: @include('host.bookings.partials.cancel-modal', ['booking' => $booking, 'modalId' => 'cancel-modal-123']) --}}

@props(['booking', 'modalId' => 'cancel-booking-modal'])

@if($booking->canBeCancelled())
<div id="{{ $modalId }}" class="modal" role="dialog" aria-modal="true">
    <div class="modal-box max-w-md">
        <button type="button" class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3" onclick="closeCancelBookingModal('{{ $modalId }}')">✕</button>
        <h3 class="font-bold text-lg mb-4">Cancel Booking</h3>

        @if($booking->isLateCancellation())
            <div class="alert alert-warning mb-4">
                <span class="icon-[tabler--alert-triangle] size-5"></span>
                <span>This is a late cancellation (within {{ auth()->user()->currentHost()->getPolicy('cancellation_window_hours', 12) }} hours of class start)</span>
            </div>
        @endif

        <form id="cancel-form-{{ $booking->id }}" onsubmit="submitBookingCancellation(event, {{ $booking->id }}, '{{ $modalId }}')">
            <div class="space-y-4">
                <div>
                    <label class="label" for="cancel-reason-{{ $booking->id }}">Reason for Cancellation <span class="text-error">*</span></label>
                    <select
                        id="cancel-reason-{{ $booking->id }}"
                        name="reason"
                        class="select select-bordered w-full"
                        data-select='{
                            "placeholder": "Select a reason...",
                            "hasSearch": false
                        }'
                        required
                    >
                        <option value="">Select a reason...</option>
                        <option value="Client requested">Client requested</option>
                        <option value="Schedule conflict">Schedule conflict</option>
                        <option value="Illness/injury">Illness/injury</option>
                        <option value="Emergency">Emergency</option>
                        <option value="Class cancelled">Class cancelled</option>
                        <option value="Duplicate booking">Duplicate booking</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="label" for="cancel-notes-{{ $booking->id }}">Notes (optional)</label>
                    <textarea
                        id="cancel-notes-{{ $booking->id }}"
                        name="notes"
                        class="textarea textarea-bordered w-full"
                        rows="3"
                        placeholder="Additional details about the cancellation..."
                    ></textarea>
                </div>
            </div>

            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="closeCancelBookingModal('{{ $modalId }}')">Close</button>
                <button type="submit" class="btn btn-error" id="confirm-cancel-btn-{{ $booking->id }}">
                    <span class="icon-[tabler--x] size-4"></span>
                    Cancel Booking
                </button>
            </div>
        </form>
    </div>
    <div class="modal-backdrop" onclick="closeCancelBookingModal('{{ $modalId }}')"></div>
</div>
@endif

@once
@push('scripts')
<script>
function openCancelBookingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('modal-open');
    }
}

function closeCancelBookingModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('modal-open');
    }
}

async function submitBookingCancellation(event, bookingId, modalId) {
    event.preventDefault();

    const form = event.target;
    const reason = form.querySelector('[name="reason"]').value;
    const notes = form.querySelector('[name="notes"]').value;
    const submitBtn = document.getElementById('confirm-cancel-btn-' + bookingId);

    if (!reason) {
        alert('Please select a reason for cancellation');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Cancelling...';

    try {
        const response = await fetch('/bookings/' + bookingId + '/cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ reason, notes })
        });

        const data = await response.json();

        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Failed to cancel booking');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="icon-[tabler--x] size-4"></span> Cancel Booking';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while cancelling the booking');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="icon-[tabler--x] size-4"></span> Cancel Booking';
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal.modal-open').forEach(modal => {
            modal.classList.remove('modal-open');
        });
    }
});
</script>
@endpush
@endonce
