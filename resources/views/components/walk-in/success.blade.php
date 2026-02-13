{{-- Success Component --}}
<div class="text-center py-6">
    {{-- Success Animation --}}
    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-success/10 flex items-center justify-center">
        <span class="icon-[tabler--check] size-10 text-success"></span>
    </div>

    {{-- Success Message --}}
    <h3 class="text-xl font-bold mb-2">Booking Confirmed!</h3>
    <p class="text-base-content/60 mb-6" id="success-message">Client has been successfully booked.</p>

    {{-- Booking Summary --}}
    <div class="card bg-base-200/50 text-left mb-6">
        <div class="card-body p-4 space-y-3">
            <div class="flex items-center gap-3">
                <div class="avatar placeholder">
                    <div class="bg-primary text-primary-content rounded-full w-10">
                        <span id="success-client-initials" class="text-sm font-medium">JD</span>
                    </div>
                </div>
                <div>
                    <div class="font-semibold" id="success-client-name">John Doe</div>
                    <div class="text-sm text-base-content/60" id="success-session-info">Yoga Class - 10:00 AM</div>
                </div>
            </div>

            <div class="flex items-center justify-between text-sm">
                <span class="text-base-content/60">Payment</span>
                <span class="font-medium" id="success-payment-info">Cash - $25.00</span>
            </div>

            <div class="flex items-center justify-between text-sm">
                <span class="text-base-content/60">Status</span>
                <span id="success-status-badge" class="badge badge-success badge-sm">Confirmed</span>
            </div>

            <div id="success-checked-in" class="hidden flex items-center justify-between text-sm">
                <span class="text-base-content/60">Check-in</span>
                <span class="badge badge-info badge-sm">Checked In</span>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="flex flex-col sm:flex-row gap-2 justify-center">
        <button type="button" class="btn btn-primary" onclick="closeWalkInModal()">
            <span class="icon-[tabler--check] size-5"></span>
            Done
        </button>
        <button type="button" class="btn btn-soft" onclick="startNewWalkIn()">
            <span class="icon-[tabler--plus] size-5"></span>
            Book Another
        </button>
    </div>
</div>
