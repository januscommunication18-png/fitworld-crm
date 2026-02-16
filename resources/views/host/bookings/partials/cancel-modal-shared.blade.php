{{-- Shared Cancel Booking Modal --}}
{{-- This modal is used on listing pages where multiple bookings can be cancelled --}}
{{-- Usage: @include('host.bookings.partials.cancel-modal-shared') --}}

<div id="cancel-booking-modal" class="fixed inset-0 z-[60] hidden">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeCancelModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl w-full max-w-md transform transition-all">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-error/20 flex items-center justify-center">
                        <span class="icon-[tabler--calendar-x] size-6 text-error"></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Cancel Booking</h3>
                        <p class="text-sm text-base-content/60">This action cannot be undone</p>
                    </div>
                </div>

                {{-- Late Cancellation Warning --}}
                <div id="late-cancellation-warning" class="alert alert-warning mb-4 hidden">
                    <span class="icon-[tabler--alert-triangle] size-5"></span>
                    <div>
                        <div class="font-semibold">Late Cancellation</div>
                        <div class="text-sm">This booking is past the cancellation deadline and will be marked as a late cancellation.</div>
                    </div>
                </div>

                <form id="cancel-booking-form">
                    <input type="hidden" id="cancel-booking-id" value="">

                    {{-- Reason Select --}}
                    <div class="form-control mb-4">
                        <label class="label" for="cancellation_reason_select">
                            <span class="label-text font-medium">Reason for Cancellation <span class="text-error">*</span></span>
                        </label>
                        <select
                            id="cancellation_reason_select"
                            data-select='{
                                "placeholder": "Select a reason...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "hasSearch": false,
                                "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'
                            class="hidden"
                        >
                            <option value="">Select a reason...</option>
                            <option value="Client requested">Client requested cancellation</option>
                            <option value="Schedule conflict">Schedule conflict</option>
                            <option value="Instructor unavailable">Instructor unavailable</option>
                            <option value="Class cancelled">Class/Session cancelled</option>
                            <option value="Medical/Emergency">Medical or emergency</option>
                            <option value="No show - client">Client did not show up</option>
                            <option value="Duplicate booking">Duplicate booking</option>
                            <option value="Payment issue">Payment issue</option>
                            <option value="Weather/Natural event">Weather or natural event</option>
                            <option value="Other">Other reason</option>
                        </select>
                    </div>

                    {{-- Notes Field --}}
                    <div class="form-control mb-4">
                        <label class="label" for="cancellation_notes">
                            <span class="label-text font-medium">Additional Notes</span>
                            <span class="label-text-alt text-base-content/50">Optional</span>
                        </label>
                        <textarea
                            id="cancellation_notes"
                            class="textarea textarea-bordered w-full"
                            rows="3"
                            placeholder="Add any additional details about this cancellation..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" class="btn btn-ghost" onclick="closeCancelModal()">
                            Keep Booking
                        </button>
                        <button type="submit" class="btn btn-error" id="confirm-cancel-btn">
                            <span class="loading loading-spinner loading-xs hidden" id="cancel-spinner"></span>
                            <span class="icon-[tabler--x] size-4 me-1"></span>
                            Cancel Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
var currentCancelBookingId = null;

function openCancelModal(bookingId, isLate) {
    currentCancelBookingId = bookingId;
    document.getElementById('cancel-booking-id').value = bookingId;
    document.getElementById('cancellation_notes').value = '';

    // Reset the advanced select
    var selectEl = document.getElementById('cancellation_reason_select');
    if (selectEl && window.HSSelect) {
        var instance = HSSelect.getInstance(selectEl);
        if (instance) {
            instance.setValue('');
        }
    }

    var warning = document.getElementById('late-cancellation-warning');
    if (isLate) {
        warning.classList.remove('hidden');
    } else {
        warning.classList.add('hidden');
    }

    document.getElementById('cancel-booking-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCancelModal() {
    document.getElementById('cancel-booking-modal').classList.add('hidden');
    document.body.style.overflow = '';
    currentCancelBookingId = null;
}

document.getElementById('cancel-booking-form')?.addEventListener('submit', function(e) {
    e.preventDefault();

    var selectEl = document.getElementById('cancellation_reason_select');
    var reason = selectEl.value;
    var notes = document.getElementById('cancellation_notes').value.trim();

    if (!reason) {
        alert('Please select a reason for cancellation');
        return;
    }

    var btn = document.getElementById('confirm-cancel-btn');
    var spinner = document.getElementById('cancel-spinner');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    fetch('/bookings/' + currentCancelBookingId + '/cancel', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            reason: reason,
            notes: notes || null
        })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            closeCancelModal();
            if (typeof closeDrawer === 'function') {
                closeDrawer('booking-' + currentCancelBookingId);
            }
            location.reload();
        } else {
            alert(data.message || 'Failed to cancel booking');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('An error occurred while cancelling the booking');
    })
    .finally(function() {
        btn.disabled = false;
        spinner.classList.add('hidden');
    });
});

// Close modal on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        var modal = document.getElementById('cancel-booking-modal');
        if (modal && !modal.classList.contains('hidden')) {
            closeCancelModal();
        }
    }
});
</script>
@endpush
@endonce
