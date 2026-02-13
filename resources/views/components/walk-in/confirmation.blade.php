{{-- Confirmation Component --}}
<div class="space-y-4">
    {{-- Summary Card --}}
    <div class="card bg-base-200/50 border border-base-300">
        <div class="card-body p-4 space-y-4">
            {{-- Client Info --}}
            <div class="flex items-center justify-between">
                <div class="text-sm text-base-content/60">Client</div>
                <div class="flex items-center gap-2">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-full w-8">
                            <span id="confirm-client-initials" class="text-xs font-medium">JD</span>
                        </div>
                    </div>
                    <span class="font-semibold" id="confirm-client-name">John Doe</span>
                </div>
            </div>

            <div class="divider my-0"></div>

            {{-- Session Info --}}
            <div class="flex items-center justify-between">
                <div class="text-sm text-base-content/60">Booking</div>
                <div class="text-right">
                    <div class="font-semibold" id="confirm-session-name">Yoga Class</div>
                    <div class="text-sm text-base-content/60" id="confirm-session-datetime">Today at 10:00 AM</div>
                </div>
            </div>

            <div class="divider my-0"></div>

            {{-- Payment Info --}}
            <div class="flex items-center justify-between">
                <div class="text-sm text-base-content/60">Payment</div>
                <div class="text-right">
                    <div class="font-semibold" id="confirm-payment-method">Cash</div>
                    <div class="text-sm text-base-content/60" id="confirm-payment-amount">$0.00</div>
                </div>
            </div>

            {{-- Intake Status (if applicable) --}}
            <div id="confirm-intake-section" class="hidden">
                <div class="divider my-0"></div>
                <div class="flex items-center justify-between">
                    <div class="text-sm text-base-content/60">Intake</div>
                    <div id="confirm-intake-status">
                        <span class="badge badge-ghost badge-sm">Not Required</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Override Warnings --}}
    <div id="confirm-warnings" class="hidden space-y-2">
        {{-- Capacity Override Warning --}}
        <div id="confirm-capacity-warning" class="hidden alert alert-warning">
            <span class="icon-[tabler--alert-triangle] size-5"></span>
            <div>
                <div class="font-semibold">Capacity Override</div>
                <div class="text-sm" id="confirm-capacity-reason">Class is at capacity</div>
            </div>
        </div>

        {{-- Intake Waived Warning --}}
        <div id="confirm-intake-warning" class="hidden alert alert-info">
            <span class="icon-[tabler--clipboard-off] size-5"></span>
            <div>
                <div class="font-semibold">Intake Waived</div>
                <div class="text-sm" id="confirm-intake-waive-reason">Reason provided</div>
            </div>
        </div>
    </div>

    {{-- Additional Options --}}
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-4 space-y-3">
            <div class="font-medium text-sm">Additional Options</div>

            {{-- Check-in Now --}}
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox"
                       id="confirm-check-in-now"
                       class="checkbox checkbox-sm checkbox-primary"
                       onchange="toggleCheckInNow(this.checked)">
                <div>
                    <span class="text-sm font-medium">Check in client now</span>
                    <span class="text-xs text-base-content/60 block">Mark as arrived immediately</span>
                </div>
            </label>
        </div>
    </div>

    {{-- Submit Button Area --}}
    <div id="confirm-error" class="hidden alert alert-error">
        <span class="icon-[tabler--x] size-5"></span>
        <span id="confirm-error-message">An error occurred</span>
    </div>
</div>
