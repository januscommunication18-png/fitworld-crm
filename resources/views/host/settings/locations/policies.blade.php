@extends('layouts.settings')

@section('title', 'Policies — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Policies</li>
    </ol>
@endsection

@section('settings-content')
<form method="POST" action="{{ route('settings.policies.update') }}">
    @csrf
    @method('PUT')

    <div class="space-y-6">
        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-soft alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Policies</h1>
                <p class="text-base-content/60 text-sm">Control booking rules and reduce disputes</p>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--check] size-4"></span> Save Changes
            </button>
        </div>

        {{-- Section A: Cancellation Policy --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-1">
                    <span class="icon-[tabler--calendar-x] size-5 text-error"></span>
                    <h2 class="text-lg font-semibold">Cancellation Policy</h2>
                </div>
                <p class="text-base-content/60 text-sm mb-6">Define rules for when and how customers can cancel bookings</p>

                <div class="space-y-6">
                    {{-- Allow Cancellations --}}
                    <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="allow_cancellations" value="1" class="checkbox checkbox-primary mt-1"
                            {{ old('allow_cancellations', $policies['allow_cancellations'] ?? true) ? 'checked' : '' }} />
                        <span class="label-text w-full text-start">
                            <span class="text-base font-medium">Allow Cancellations</span>
                            <span class="text-base-content/70 block">Let customers cancel their bookings</span>
                        </span>
                    </label>

                    {{-- Cancellation Window --}}
                    <div>
                        <label class="label-text" for="cancellation_window_hours">Cancellation Window (hours)</label>
                        <div class="flex items-center gap-3">
                            <input
                                id="cancellation_window_hours"
                                name="cancellation_window_hours"
                                type="number"
                                class="input w-32"
                                min="0"
                                max="168"
                                value="{{ old('cancellation_window_hours', $policies['cancellation_window_hours'] ?? 12) }}"
                            />
                            <span class="text-sm text-base-content/60">hours before class starts</span>
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">Customers can cancel without penalty if outside this window</p>
                    </div>

                    {{-- Cancellation Fee --}}
                    <div>
                        <label class="label-text" for="cancellation_fee">Late Cancellation Fee (optional)</label>
                        <div class="input-group w-fit">
                            <select class="select select-sm w-20" disabled>
                                <option selected>{{ $host->currency ?? 'USD' }}</option>
                            </select>
                            <input
                                id="cancellation_fee"
                                name="cancellation_fee"
                                type="number"
                                class="input input-sm w-28"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                value="{{ old('cancellation_fee', $policies['cancellation_fee'] ?? '') }}"
                            />
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">Leave empty for no fee</p>
                    </div>

                    {{-- Late Cancellation Handling --}}
                    <div>
                        <label class="label-text mb-3 block">Late Cancellation Handling</label>
                        <div class="space-y-3">
                            <label class="flex items-start gap-3 cursor-pointer p-3 border border-base-content/10 rounded-lg has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="late_cancellation_handling" value="mark_late" class="radio radio-primary radio-sm mt-0.5"
                                    {{ old('late_cancellation_handling', $policies['late_cancellation_handling'] ?? 'mark_late') === 'mark_late' ? 'checked' : '' }} />
                                <div>
                                    <div class="font-medium">Mark as Late Cancel</div>
                                    <div class="text-sm text-base-content/60">Just mark the cancellation as late in the system</div>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer p-3 border border-base-content/10 rounded-lg has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="late_cancellation_handling" value="charge_fee" class="radio radio-primary radio-sm mt-0.5"
                                    {{ old('late_cancellation_handling', $policies['late_cancellation_handling'] ?? 'mark_late') === 'charge_fee' ? 'checked' : '' }} />
                                <div>
                                    <div class="font-medium">Charge Fee</div>
                                    <div class="text-sm text-base-content/60">Charge the late cancellation fee defined above</div>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer p-3 border border-base-content/10 rounded-lg has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="late_cancellation_handling" value="deduct_credit" class="radio radio-primary radio-sm mt-0.5"
                                    {{ old('late_cancellation_handling', $policies['late_cancellation_handling'] ?? 'mark_late') === 'deduct_credit' ? 'checked' : '' }} />
                                <div>
                                    <div class="font-medium">Deduct Class Credit</div>
                                    <div class="text-sm text-base-content/60">Deduct one class credit from the customer's pack</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section B: No-Show Policy --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-1">
                    <span class="icon-[tabler--user-x] size-5 text-warning"></span>
                    <h2 class="text-lg font-semibold">No-Show Policy</h2>
                </div>
                <p class="text-base-content/60 text-sm mb-6">Define consequences for customers who don't show up</p>

                <div class="space-y-6">
                    {{-- No-Show Fee --}}
                    <div>
                        <label class="label-text" for="no_show_fee">No-Show Fee (optional)</label>
                        <div class="input-group w-fit">
                            <select class="select select-sm w-20" disabled>
                                <option selected>{{ $host->currency ?? 'USD' }}</option>
                            </select>
                            <input
                                id="no_show_fee"
                                name="no_show_fee"
                                type="number"
                                class="input input-sm w-28"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                value="{{ old('no_show_fee', $policies['no_show_fee'] ?? '') }}"
                            />
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">Leave empty for no fee</p>
                    </div>

                    {{-- Grace Period --}}
                    <div>
                        <label class="label-text" for="no_show_grace_period_minutes">Grace Period</label>
                        <div class="flex items-center gap-3">
                            <input
                                id="no_show_grace_period_minutes"
                                name="no_show_grace_period_minutes"
                                type="number"
                                class="input w-32"
                                min="0"
                                max="60"
                                value="{{ old('no_show_grace_period_minutes', $policies['no_show_grace_period_minutes'] ?? 15) }}"
                            />
                            <span class="text-sm text-base-content/60">minutes after class starts</span>
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">Time allowed for late arrivals before marking as no-show</p>
                    </div>

                    {{-- No-Show Handling --}}
                    <div>
                        <label class="label-text mb-3 block">No-Show Handling</label>
                        <div class="space-y-3">
                            <label class="flex items-start gap-3 cursor-pointer p-3 border border-base-content/10 rounded-lg has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="no_show_handling" value="no_action" class="radio radio-primary radio-sm mt-0.5"
                                    {{ old('no_show_handling', $policies['no_show_handling'] ?? 'no_action') === 'no_action' ? 'checked' : '' }} />
                                <div>
                                    <div class="font-medium">No Action</div>
                                    <div class="text-sm text-base-content/60">Just mark as no-show without penalty</div>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer p-3 border border-base-content/10 rounded-lg has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="no_show_handling" value="charge_fee" class="radio radio-primary radio-sm mt-0.5"
                                    {{ old('no_show_handling', $policies['no_show_handling'] ?? 'no_action') === 'charge_fee' ? 'checked' : '' }} />
                                <div>
                                    <div class="font-medium">Charge Fee</div>
                                    <div class="text-sm text-base-content/60">Charge the no-show fee defined above</div>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer p-3 border border-base-content/10 rounded-lg has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="no_show_handling" value="deduct_credit" class="radio radio-primary radio-sm mt-0.5"
                                    {{ old('no_show_handling', $policies['no_show_handling'] ?? 'no_action') === 'deduct_credit' ? 'checked' : '' }} />
                                <div>
                                    <div class="font-medium">Deduct Class Credit</div>
                                    <div class="text-sm text-base-content/60">Deduct one class credit from the customer's pack</div>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer p-3 border border-base-content/10 rounded-lg has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="no_show_handling" value="strike" class="radio radio-primary radio-sm mt-0.5"
                                    {{ old('no_show_handling', $policies['no_show_handling'] ?? 'no_action') === 'strike' ? 'checked' : '' }} />
                                <div>
                                    <div class="font-medium">Strike System</div>
                                    <div class="text-sm text-base-content/60">Add a strike to customer's record (coming soon)</div>
                                    <span class="badge badge-soft badge-xs mt-1">Future Feature</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section C: Waitlist Policy --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-1">
                    <span class="icon-[tabler--list-numbers] size-5 text-info"></span>
                    <h2 class="text-lg font-semibold">Waitlist Policy</h2>
                </div>
                <p class="text-base-content/60 text-sm mb-6">Configure how the waitlist works when classes are full</p>

                <div class="space-y-6">
                    {{-- Enable Waitlist --}}
                    <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="enable_waitlist" value="1" class="checkbox checkbox-primary mt-1"
                            {{ old('enable_waitlist', $policies['enable_waitlist'] ?? true) ? 'checked' : '' }} />
                        <span class="label-text w-full text-start">
                            <span class="text-base font-medium">Enable Waitlist</span>
                            <span class="text-base-content/70 block">Allow customers to join a waitlist when classes are full</span>
                        </span>
                    </label>

                    {{-- Auto-Promote --}}
                    <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="waitlist_auto_promote" value="1" class="checkbox checkbox-primary mt-1"
                            {{ old('waitlist_auto_promote', $policies['waitlist_auto_promote'] ?? true) ? 'checked' : '' }} />
                        <span class="label-text w-full text-start">
                            <span class="text-base font-medium">Auto-Promote</span>
                            <span class="text-base-content/70 block">Automatically move waitlisted customers when spots open up</span>
                        </span>
                    </label>

                    {{-- Promotion Window --}}
                    <div>
                        <label class="label-text" for="waitlist_promotion_window_minutes">Promotion Window</label>
                        <div class="flex items-center gap-3">
                            <input
                                id="waitlist_promotion_window_minutes"
                                name="waitlist_promotion_window_minutes"
                                type="number"
                                class="input w-32"
                                min="0"
                                max="1440"
                                value="{{ old('waitlist_promotion_window_minutes', $policies['waitlist_promotion_window_minutes'] ?? 120) }}"
                            />
                            <span class="text-sm text-base-content/60">minutes before class starts</span>
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">Stop auto-promoting after this time to avoid last-minute changes</p>
                    </div>

                    {{-- Notify on Promotion --}}
                    <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="waitlist_notify_on_promotion" value="1" class="checkbox checkbox-primary mt-1"
                            {{ old('waitlist_notify_on_promotion', $policies['waitlist_notify_on_promotion'] ?? true) ? 'checked' : '' }} />
                        <span class="label-text w-full text-start">
                            <span class="text-base font-medium">Notify on Promotion</span>
                            <span class="text-base-content/70 block">Send email notification when customer is promoted from waitlist</span>
                        </span>
                    </label>

                    {{-- Hold Spot Duration --}}
                    <div>
                        <label class="label-text" for="waitlist_hold_spot_minutes">Hold Spot Duration</label>
                        <div class="flex items-center gap-3">
                            <input
                                id="waitlist_hold_spot_minutes"
                                name="waitlist_hold_spot_minutes"
                                type="number"
                                class="input w-32"
                                min="0"
                                max="60"
                                value="{{ old('waitlist_hold_spot_minutes', $policies['waitlist_hold_spot_minutes'] ?? 15) }}"
                            />
                            <span class="text-sm text-base-content/60">minutes</span>
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">How long to hold the spot after promotion before moving to the next person</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section D: Booking Limits --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-1">
                    <span class="icon-[tabler--bookmark] size-5 text-primary"></span>
                    <h2 class="text-lg font-semibold">Booking Limits</h2>
                </div>
                <p class="text-base-content/60 text-sm mb-6">Set restrictions on how customers can book</p>

                <div class="space-y-6">
                    {{-- Max Bookings Per Class --}}
                    <div>
                        <label class="label-text" for="max_bookings_per_class">Max Bookings Per Class</label>
                        <div class="flex items-center gap-3">
                            <input
                                id="max_bookings_per_class"
                                name="max_bookings_per_class"
                                type="number"
                                class="input w-32"
                                min="1"
                                max="10"
                                value="{{ old('max_bookings_per_class', $policies['max_bookings_per_class'] ?? 1) }}"
                            />
                            <span class="text-sm text-base-content/60">spots per customer</span>
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">Maximum spots a single customer can book for one class</p>
                    </div>

                    {{-- Max Active Bookings --}}
                    <div>
                        <label class="label-text" for="max_active_bookings">Max Active Bookings (optional)</label>
                        <div class="flex items-center gap-3">
                            <input
                                id="max_active_bookings"
                                name="max_active_bookings"
                                type="number"
                                class="input w-32"
                                min="1"
                                max="100"
                                placeholder="Unlimited"
                                value="{{ old('max_active_bookings', $policies['max_active_bookings'] ?? '') }}"
                            />
                            <span class="text-sm text-base-content/60">total active bookings</span>
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">Maximum future bookings a customer can have at once. Leave empty for unlimited.</p>
                    </div>

                    {{-- Allow Booking Without Payment --}}
                    <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="allow_booking_without_payment" value="1" class="checkbox checkbox-primary mt-1"
                            {{ old('allow_booking_without_payment', $policies['allow_booking_without_payment'] ?? false) ? 'checked' : '' }} />
                        <span class="label-text w-full text-start">
                            <span class="text-base font-medium">Allow Booking Without Payment</span>
                            <span class="text-base-content/70 block">Let customers book classes without paying upfront</span>
                        </span>
                    </label>

                    {{-- Booking Lead Time --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text" for="booking_earliest_days">Earliest Booking</label>
                            <div class="flex items-center gap-2">
                                <input
                                    id="booking_earliest_days"
                                    name="booking_earliest_days"
                                    type="number"
                                    class="input w-24"
                                    min="1"
                                    max="365"
                                    value="{{ old('booking_earliest_days', $policies['booking_earliest_days'] ?? 30) }}"
                                />
                                <span class="text-sm text-base-content/60">days ahead</span>
                            </div>
                            <p class="text-xs text-base-content/60 mt-1">How far in advance customers can book</p>
                        </div>
                        <div>
                            <label class="label-text" for="booking_latest_minutes">Latest Booking</label>
                            <div class="flex items-center gap-2">
                                <input
                                    id="booking_latest_minutes"
                                    name="booking_latest_minutes"
                                    type="number"
                                    class="input w-24"
                                    min="0"
                                    max="1440"
                                    value="{{ old('booking_latest_minutes', $policies['booking_latest_minutes'] ?? 30) }}"
                                />
                                <span class="text-sm text-base-content/60">minutes before</span>
                            </div>
                            <p class="text-xs text-base-content/60 mt-1">Cutoff time before class starts</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section E: Studio Rules --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-1">
                    <span class="icon-[tabler--gavel] size-5 text-secondary"></span>
                    <h2 class="text-lg font-semibold">Studio Rules</h2>
                </div>
                <p class="text-base-content/60 text-sm mb-6">Information displayed on your booking page</p>

                <div class="space-y-6">
                    {{-- House Rules --}}
                    <div>
                        <label class="label-text" for="house_rules">House Rules</label>
                        <textarea
                            id="house_rules"
                            name="house_rules"
                            class="textarea w-full"
                            rows="5"
                            placeholder="Enter your studio's house rules and policies for customers..."
                        >{{ old('house_rules', $policies['house_rules'] ?? '') }}</textarea>
                        <p class="text-xs text-base-content/60 mt-1">Displayed on your booking page. You can use plain text or markdown.</p>
                    </div>

                    {{-- Liability Waiver URL --}}
                    <div>
                        <label class="label-text" for="liability_waiver_url">Liability Waiver URL (optional)</label>
                        <input
                            id="liability_waiver_url"
                            name="liability_waiver_url"
                            type="url"
                            class="input w-full"
                            placeholder="https://example.com/waiver.pdf"
                            value="{{ old('liability_waiver_url', $policies['liability_waiver_url'] ?? '') }}"
                        />
                        <p class="text-xs text-base-content/60 mt-1">Link to your liability waiver document. Customers may be required to accept before booking.</p>
                    </div>

                    {{-- Arrival Instructions --}}
                    <div>
                        <label class="label-text" for="arrival_instructions">Arrival Instructions (optional)</label>
                        <textarea
                            id="arrival_instructions"
                            name="arrival_instructions"
                            class="textarea w-full"
                            rows="3"
                            placeholder="Please arrive 10 minutes early. The studio is located on the 2nd floor..."
                        >{{ old('arrival_instructions', $policies['arrival_instructions'] ?? '') }}</textarea>
                        <p class="text-xs text-base-content/60 mt-1">Included in booking confirmation emails</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Policy Summary Info --}}
        <div class="alert alert-soft alert-info">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <div>
                <strong>Policy Application</strong>
                <p class="text-sm">Policy changes apply to future actions immediately. Existing bookings follow the policy that was in effect at the time of the action.</p>
            </div>
        </div>

        {{-- Save Button (Bottom) --}}
        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--check] size-4"></span> Save Changes
            </button>
        </div>
    </div>
</form>
@endsection
