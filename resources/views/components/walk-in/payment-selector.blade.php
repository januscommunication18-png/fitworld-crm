{{-- Payment Method Selector Component --}}
<div class="space-y-4">
    {{-- Payment Methods Loading --}}
    <div id="payment-methods-loading" class="flex items-center justify-center py-8 text-base-content/40">
        <span class="loading loading-spinner loading-md mr-2"></span>
        Loading payment options...
    </div>

    {{-- Payment Methods List --}}
    <div id="payment-methods-list" class="hidden space-y-3">
        {{-- Membership Option --}}
        <div id="payment-option-membership" class="hidden">
            <label class="card bg-base-100 border-2 border-base-300 hover:border-primary/50 cursor-pointer transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                <div class="card-body p-4">
                    <div class="flex items-start gap-3">
                        <input type="radio" name="payment_option" value="membership" class="radio radio-primary mt-1" onchange="selectPaymentMethod('membership')">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--id-badge-2] size-5 text-secondary"></span>
                                <span class="font-semibold">Use Membership</span>
                                <span class="badge badge-secondary badge-sm">Recommended</span>
                            </div>
                            <div class="text-sm text-base-content/60 mt-1" id="membership-details">
                                {{-- Membership name and credits --}}
                            </div>
                        </div>
                    </div>
                </div>
            </label>
        </div>

        {{-- Class Pack Option --}}
        <div id="payment-option-pack" class="hidden">
            <label class="card bg-base-100 border-2 border-base-300 hover:border-primary/50 cursor-pointer transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                <div class="card-body p-4">
                    <div class="flex items-start gap-3">
                        <input type="radio" name="payment_option" value="pack" class="radio radio-primary mt-1" onchange="selectPaymentMethod('pack')">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--ticket] size-5 text-accent"></span>
                                <span class="font-semibold">Use Class Pack</span>
                            </div>
                            <div id="pack-options" class="mt-2 space-y-2">
                                {{-- Pack options will be populated by JS --}}
                            </div>
                        </div>
                    </div>
                </div>
            </label>
        </div>

        {{-- Manual Payment Option --}}
        <div id="payment-option-manual">
            <label class="card bg-base-100 border-2 border-base-300 hover:border-primary/50 cursor-pointer transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                <div class="card-body p-4">
                    <div class="flex items-start gap-3">
                        <input type="radio" name="payment_option" value="manual" class="radio radio-primary mt-1" onchange="selectPaymentMethod('manual')">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--cash] size-5 text-success"></span>
                                <span class="font-semibold">Manual Payment</span>
                            </div>
                            <p class="text-sm text-base-content/60 mt-1">Cash, Venmo, Zelle, or other payment</p>
                        </div>
                    </div>
                </div>
            </label>

            {{-- Manual Payment Details (shown when selected) --}}
            <div id="manual-payment-details" class="hidden mt-3 p-4 border border-base-300 rounded-lg bg-base-200/30 space-y-3">
                <div class="form-control">
                    <label class="label py-1" for="manual-payment-type">
                        <span class="label-text text-sm font-medium">Payment Type <span class="text-error">*</span></span>
                    </label>
                    <select id="manual-payment-type" class="select select-bordered select-sm" onchange="updateManualMethod(this.value)">
                        <option value="cash">Cash</option>
                        <option value="venmo">Venmo</option>
                        <option value="zelle">Zelle</option>
                        <option value="paypal">PayPal</option>
                        <option value="cash_app">Cash App</option>
                        <option value="check">Check</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-control">
                    <label class="label py-1" for="manual-payment-amount">
                        <span class="label-text text-sm font-medium">Amount <span class="text-error">*</span></span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50">$</span>
                        <input type="number"
                               id="manual-payment-amount"
                               class="input input-bordered input-sm pl-7 w-full"
                               step="0.01"
                               min="0"
                               placeholder="0.00"
                               onchange="updatePricePaid(this.value)">
                    </div>
                </div>

                <div class="form-control">
                    <label class="label py-1" for="manual-payment-notes">
                        <span class="label-text text-sm">Notes (optional)</span>
                    </label>
                    <input type="text"
                           id="manual-payment-notes"
                           class="input input-bordered input-sm"
                           placeholder="Payment reference or notes">
                </div>
            </div>
        </div>

        {{-- Stripe Option (if enabled) --}}
        <div id="payment-option-stripe" class="hidden">
            <label class="card bg-base-100 border-2 border-base-300 hover:border-primary/50 cursor-pointer transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                <div class="card-body p-4">
                    <div class="flex items-start gap-3">
                        <input type="radio" name="payment_option" value="stripe" class="radio radio-primary mt-1" onchange="selectPaymentMethod('stripe')">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--credit-card] size-5 text-primary"></span>
                                <span class="font-semibold">Card Payment</span>
                            </div>
                            <p class="text-sm text-base-content/60 mt-1">Process card payment via Stripe</p>
                        </div>
                    </div>
                </div>
            </label>
        </div>

        {{-- Complimentary Option (owner/admin only) --}}
        <div id="payment-option-comp" class="hidden">
            <label class="card bg-base-100 border-2 border-base-300 hover:border-primary/50 cursor-pointer transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                <div class="card-body p-4">
                    <div class="flex items-start gap-3">
                        <input type="radio" name="payment_option" value="comp" class="radio radio-primary mt-1" onchange="selectPaymentMethod('comp')">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--gift] size-5 text-warning"></span>
                                <span class="font-semibold">Complimentary</span>
                            </div>
                            <p class="text-sm text-base-content/60 mt-1">No charge for this booking</p>
                        </div>
                    </div>
                </div>
            </label>

            {{-- Comp Notes (shown when selected) --}}
            <div id="comp-payment-details" class="hidden mt-3 p-4 border border-base-300 rounded-lg bg-base-200/30">
                <div class="form-control">
                    <label class="label py-1" for="comp-payment-notes">
                        <span class="label-text text-sm">Reason (optional)</span>
                    </label>
                    <input type="text"
                           id="comp-payment-notes"
                           class="input input-bordered input-sm"
                           placeholder="e.g., First class free, VIP guest">
                </div>
            </div>
        </div>
    </div>

    {{-- No Payment Methods Available --}}
    <div id="payment-methods-empty" class="hidden text-center py-8">
        <span class="icon-[tabler--credit-card-off] size-12 text-base-content/20 mb-2"></span>
        <p class="text-base-content/60">No payment methods available for this client.</p>
    </div>

    {{-- Intake/Questionnaire Section --}}
    <div id="intake-section" class="hidden border-t border-base-200 pt-4 mt-4">
        @include('components.walk-in.intake-status')
    </div>
</div>
