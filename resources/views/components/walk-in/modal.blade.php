{{-- Walk-In Booking Modal --}}
<div id="walk-in-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden" role="dialog" tabindex="-1">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50" onclick="closeWalkInModal()"></div>
    {{-- Modal Content --}}
    <div class="relative bg-base-100 rounded-xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-hidden">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between p-4 border-b border-base-200">
            <h3 class="text-lg font-semibold">Walk-In Booking</h3>
            <button type="button" class="btn btn-ghost btn-circle btn-sm" aria-label="Close" onclick="closeWalkInModal()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="p-4 overflow-y-auto max-h-[60vh]">
                {{-- Progress Steps --}}
                <div class="flex items-center justify-center gap-4 mb-6 pb-4 border-b border-base-200">
                    <div class="flex items-center gap-2 walk-in-step" data-step="1">
                        <div class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm font-medium step-indicator">1</div>
                        <span class="text-sm font-medium hidden sm:inline">Client</span>
                    </div>
                    <div class="w-8 h-0.5 bg-base-300"></div>
                    <div class="flex items-center gap-2 walk-in-step opacity-50" data-step="2">
                        <div class="w-8 h-8 rounded-full bg-base-300 flex items-center justify-center text-sm font-medium step-indicator">2</div>
                        <span class="text-sm font-medium hidden sm:inline">Payment</span>
                    </div>
                    <div class="w-8 h-0.5 bg-base-300"></div>
                    <div class="flex items-center gap-2 walk-in-step opacity-50" data-step="3">
                        <div class="w-8 h-8 rounded-full bg-base-300 flex items-center justify-center text-sm font-medium step-indicator">3</div>
                        <span class="text-sm font-medium hidden sm:inline">Confirm</span>
                    </div>
                </div>

                {{-- Session Info Card --}}
                <div id="walk-in-session-info" class="alert alert-soft alert-info mb-4">
                    <span class="icon-[tabler--calendar-event] size-6" id="session-type-icon"></span>
                    <div>
                        <h4 class="font-semibold" id="session-name">Loading...</h4>
                        <div class="text-sm">
                            <span id="session-datetime">---</span>
                            <span id="session-spots" class="ml-2 badge badge-sm">--</span>
                        </div>
                    </div>
                </div>

                {{-- Step 1: Client Selection --}}
                <div id="walk-in-step-1" class="walk-in-step-content">
                    {{-- Search Input --}}
                    <div class="form-control mb-4">
                        <label class="label" for="client-search">
                            <span class="label-text font-medium">Search Client</span>
                        </label>
                        <div class="relative">
                            <span class="icon-[tabler--search] size-5 text-base-content/50 absolute left-3 top-1/2 -translate-y-1/2"></span>
                            <input type="text"
                                   id="client-search"
                                   class="input input-bordered w-full pl-10"
                                   placeholder="Search by name, email, or phone..."
                                   autocomplete="off"
                                   oninput="searchClients(this.value)">
                            <div id="client-search-loading" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                                <span class="loading loading-spinner loading-sm"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Search Results --}}
                    <div id="client-search-results" class="hidden mb-4">
                        <div class="text-sm text-base-content/60 mb-2">Search Results</div>
                        <div id="client-results-list" class="space-y-2 max-h-48 overflow-y-auto"></div>
                    </div>

                    {{-- Recent Clients --}}
                    <div id="client-recent-section" class="mb-4">
                        <div class="text-sm text-base-content/60 mb-2">Recent Clients</div>
                        <div id="client-recent-list" class="space-y-2 max-h-48 overflow-y-auto">
                            <div class="flex items-center justify-center py-4 text-base-content/40">
                                <span class="loading loading-spinner loading-sm mr-2"></span>
                                Loading...
                            </div>
                        </div>
                    </div>

                    {{-- Quick Add --}}
                    <div id="client-quick-add-section">
                        <div class="divider text-sm">OR</div>
                        <button type="button" class="btn btn-outline btn-primary btn-block" id="client-quick-add-toggle" onclick="toggleQuickAddForm()">
                            <span class="icon-[tabler--user-plus] size-5"></span>
                            Add New Client
                        </button>

                        <div id="client-quick-add-form" class="hidden mt-4 p-4 border border-base-300 rounded-lg">
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="label py-1" for="quick-add-first-name"><span class="label-text">First Name *</span></label>
                                    <input type="text" id="quick-add-first-name" class="input input-bordered input-sm w-full" placeholder="John">
                                </div>
                                <div>
                                    <label class="label py-1" for="quick-add-last-name"><span class="label-text">Last Name *</span></label>
                                    <input type="text" id="quick-add-last-name" class="input input-bordered input-sm w-full" placeholder="Doe">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="label py-1" for="quick-add-phone"><span class="label-text">Phone</span></label>
                                <input type="tel" id="quick-add-phone" class="input input-bordered input-sm w-full" placeholder="(555) 123-4567">
                            </div>
                            <div class="mb-3">
                                <label class="label py-1" for="quick-add-email"><span class="label-text">Email</span></label>
                                <input type="email" id="quick-add-email" class="input input-bordered input-sm w-full" placeholder="john@example.com">
                            </div>
                            <label class="flex items-center gap-2 mb-3">
                                <input type="checkbox" id="quick-add-send-emails" class="checkbox checkbox-sm">
                                <span class="text-sm">Send confirmation emails</span>
                            </label>
                            <button type="button" class="btn btn-primary btn-sm btn-block" onclick="createQuickAddClient()">
                                Create & Select
                            </button>
                        </div>
                    </div>

                    {{-- Selected Client --}}
                    <div id="selected-client-display" class="hidden mt-4">
                        <div class="alert alert-success">
                            <div class="flex items-center gap-3 w-full">
                                <div class="avatar placeholder">
                                    <div class="bg-success-content text-success rounded-full w-10">
                                        <span id="selected-client-initials" class="text-sm font-bold">JD</span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold" id="selected-client-name">John Doe</div>
                                    <div class="text-sm opacity-80" id="selected-client-contact">john@example.com</div>
                                </div>
                                <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="clearSelectedClient()">
                                    <span class="icon-[tabler--x] size-4"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Payment Selection --}}
                <div id="walk-in-step-2" class="walk-in-step-content hidden">
                    @include('components.walk-in.payment-selector')
                </div>

                {{-- Step 3: Confirmation --}}
                <div id="walk-in-step-3" class="walk-in-step-content hidden">
                    @include('components.walk-in.confirmation')
                </div>

            {{-- Success Screen --}}
            <div id="walk-in-success" class="walk-in-step-content hidden">
                @include('components.walk-in.success')
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="flex items-center justify-between p-4 border-t border-base-200" id="walk-in-footer">
            <button type="button" class="btn btn-ghost" onclick="closeWalkInModal()">Cancel</button>
            <div class="flex gap-2">
                <button type="button" class="btn btn-ghost hidden" id="walk-in-back-btn" onclick="walkInPrevStep()">
                    <span class="icon-[tabler--arrow-left] size-4"></span> Back
                </button>
                <button type="button" class="btn btn-primary" id="walk-in-next-btn" onclick="walkInNextStep()" disabled>
                    Continue <span class="icon-[tabler--arrow-right] size-4"></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden form for data --}}
<form id="walk-in-form" class="hidden">
    <input type="hidden" name="session_type" id="walk-in-session-type" value="">
    <input type="hidden" name="session_id" id="walk-in-session-id" value="">
    <input type="hidden" name="client_id" id="walk-in-client-id" value="">
    <input type="hidden" name="payment_method" id="walk-in-payment-method" value="">
    <input type="hidden" name="manual_method" id="walk-in-manual-method" value="">
    <input type="hidden" name="price_paid" id="walk-in-price-paid" value="">
    <input type="hidden" name="customer_membership_id" id="walk-in-membership-id" value="">
    <input type="hidden" name="class_pack_purchase_id" id="walk-in-pack-id" value="">
    <input type="hidden" name="intake_status" id="walk-in-intake-status" value="not_required">
    <input type="hidden" name="intake_waived" id="walk-in-intake-waived" value="0">
    <input type="hidden" name="intake_waived_reason" id="walk-in-intake-waived-reason" value="">
    <input type="hidden" name="capacity_override" id="walk-in-capacity-override" value="0">
    <input type="hidden" name="capacity_override_reason" id="walk-in-capacity-override-reason" value="">
    <input type="hidden" name="check_in_now" id="walk-in-check-in-now" value="0">
</form>
