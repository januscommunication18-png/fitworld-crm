@extends('layouts.dashboard')

@section('title', 'Edit ' . $offer->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('offers.index') }}"><span class="icon-[tabler--tag] me-1 size-4"></span> Offers</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('offers.show', $offer) }}">{{ $offer->name }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Edit</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('offers.update', $offer) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="space-y-6 max-w-4xl">
        {{-- Header --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('offers.show', $offer) }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <h1 class="text-2xl font-bold">Edit Offer</h1>
                <p class="text-base-content/60 mt-1">Update offer details and settings.</p>
            </div>
        </div>

        {{-- Basic Info --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Basic Details</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="label-text" for="name">Offer Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $offer->name) }}"
                               class="input w-full @error('name') input-error @enderror"
                               placeholder="e.g., Summer Sale 20% Off" required>
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="status">Status</label>
                        <select id="status" name="status" class="select w-full">
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $offer->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="label-text" for="code">Promo Code (optional)</label>
                        <input type="text" id="code" name="code" value="{{ old('code', $offer->code) }}"
                               class="input w-full font-mono uppercase @error('code') input-error @enderror"
                               placeholder="e.g., SUMMER20">
                        @error('code')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="label-text" for="description">Description (shown to clients)</label>
                    <textarea id="description" name="description" rows="2"
                              class="textarea w-full"
                              placeholder="Describe the offer benefits...">{{ old('description', $offer->description) }}</textarea>
                </div>

                <div class="mt-4">
                    <label class="label-text" for="internal_notes">Internal Notes (staff only)</label>
                    <textarea id="internal_notes" name="internal_notes" rows="2"
                              class="textarea w-full"
                              placeholder="Notes for staff...">{{ old('internal_notes', $offer->internal_notes) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Duration --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Duration</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date"
                               value="{{ old('start_date', $offer->start_date?->format('Y-m-d')) }}"
                               class="input w-full">
                    </div>

                    <div>
                        <label class="label-text" for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date"
                               value="{{ old('end_date', $offer->end_date?->format('Y-m-d')) }}"
                               class="input w-full">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="label label-text cursor-pointer justify-start gap-2">
                        <input type="checkbox" name="auto_expire" value="1" class="checkbox checkbox-primary checkbox-sm"
                               {{ old('auto_expire', $offer->auto_expire) ? 'checked' : '' }}>
                        <span>Automatically expire offer when end date passes</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Discount Type --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Discount</h2>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4">
                    @foreach($discountTypes as $key => $label)
                        <label class="custom-option flex flex-row items-center gap-2 cursor-pointer p-3 rounded-lg border border-base-300 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="discount_type" value="{{ $key }}"
                                   class="radio radio-primary radio-sm"
                                   {{ old('discount_type', $offer->discount_type) === $key ? 'checked' : '' }}
                                   onchange="toggleDiscountFields()">
                            <span class="text-sm font-medium">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <div id="discount-value-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div id="percentage-field" class="{{ $offer->discount_type !== 'percentage' ? 'hidden' : '' }}">
                        <label class="label-text" for="discount_value">Discount Percentage</label>
                        <div class="join w-full">
                            <input type="number" id="discount_value" name="discount_value"
                                   value="{{ old('discount_value', $offer->discount_value) }}"
                                   class="input join-item w-full" min="0" max="100" step="0.01" placeholder="20">
                            <span class="btn btn-neutral join-item">%</span>
                        </div>
                    </div>

                    <div id="fixed-amount-field" class="{{ $offer->discount_type !== 'fixed_amount' ? 'hidden' : '' }}">
                        <label class="label-text">Fixed Amount Discount</label>
                        @foreach($hostCurrencies as $currency)
                            <div class="join w-full mt-2">
                                <span class="btn btn-neutral join-item">{{ $currency }}</span>
                                <input type="number" name="discount_amounts[{{ $currency }}]"
                                       value="{{ old('discount_amounts.' . $currency, $offer->discount_amounts[$currency] ?? '') }}"
                                       class="input join-item w-full" min="0" step="0.01" placeholder="10.00">
                            </div>
                        @endforeach
                    </div>

                    <div id="buy-x-get-y-fields" class="{{ $offer->discount_type !== 'buy_x_get_y' ? 'hidden' : '' }} md:col-span-2 grid grid-cols-2 gap-4">
                        <div>
                            <label class="label-text" for="buy_quantity">Buy Quantity</label>
                            <input type="number" id="buy_quantity" name="buy_quantity"
                                   value="{{ old('buy_quantity', $offer->buy_quantity ?? 2) }}"
                                   class="input w-full" min="1">
                        </div>
                        <div>
                            <label class="label-text" for="get_quantity">Get Quantity (Free)</label>
                            <input type="number" id="get_quantity" name="get_quantity"
                                   value="{{ old('get_quantity', $offer->get_quantity ?? 1) }}"
                                   class="input w-full" min="1">
                        </div>
                    </div>

                    <div id="free-classes-field" class="{{ $offer->discount_type !== 'free_class' ? 'hidden' : '' }}">
                        <label class="label-text" for="free_classes">Number of Free Classes</label>
                        <input type="number" id="free_classes" name="free_classes"
                               value="{{ old('free_classes', $offer->free_classes ?? 1) }}"
                               class="input w-full" min="1">
                    </div>
                </div>
            </div>
        </div>

        {{-- Applicability --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Applies To</h2>

                <div>
                    <label class="label-text" for="applies_to">What can this offer be used for?</label>
                    <select id="applies_to" name="applies_to" class="select w-full" onchange="toggleApplicableItems()">
                        @foreach($appliesToOptions as $key => $label)
                            <option value="{{ $key }}" {{ old('applies_to', $offer->applies_to) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @php
                    $existingItems = old('applicable_item_ids', $offer->applicable_item_ids ?? []);
                @endphp

                {{-- Classes Selection --}}
                <div id="classes-items" class="mt-4 {{ $offer->applies_to !== 'classes' ? 'hidden' : '' }}">
                    <div class="flex items-center justify-between mb-2">
                        <label class="label-text">Select Classes</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" class="checkbox checkbox-xs checkbox-primary" onchange="toggleAllItems('classes')">
                            <span>Select All</span>
                        </label>
                    </div>
                    @if($classPlans->isEmpty())
                        <p class="text-sm text-base-content/60 bg-base-200/50 rounded-lg p-3">No active classes found.</p>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto bg-base-200/50 rounded-lg p-3">
                            @foreach($classPlans as $classPlan)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-base-300/50 p-1 rounded">
                                    <input type="checkbox" name="applicable_item_ids[]" value="class_{{ $classPlan->id }}"
                                           class="checkbox checkbox-xs checkbox-primary item-checkbox-classes"
                                           {{ in_array('class_' . $classPlan->id, $existingItems) ? 'checked' : '' }}>
                                    <span class="text-sm">{{ $classPlan->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Services Selection --}}
                <div id="services-items" class="mt-4 {{ $offer->applies_to !== 'services' ? 'hidden' : '' }}">
                    <div class="flex items-center justify-between mb-2">
                        <label class="label-text">Select Services</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" class="checkbox checkbox-xs checkbox-primary" onchange="toggleAllItems('services')">
                            <span>Select All</span>
                        </label>
                    </div>
                    @if($servicePlans->isEmpty())
                        <p class="text-sm text-base-content/60 bg-base-200/50 rounded-lg p-3">No active services found.</p>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto bg-base-200/50 rounded-lg p-3">
                            @foreach($servicePlans as $servicePlan)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-base-300/50 p-1 rounded">
                                    <input type="checkbox" name="applicable_item_ids[]" value="service_{{ $servicePlan->id }}"
                                           class="checkbox checkbox-xs checkbox-primary item-checkbox-services"
                                           {{ in_array('service_' . $servicePlan->id, $existingItems) ? 'checked' : '' }}>
                                    <span class="text-sm">{{ $servicePlan->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Memberships Selection --}}
                <div id="memberships-items" class="mt-4 {{ $offer->applies_to !== 'memberships' ? 'hidden' : '' }}">
                    <div class="flex items-center justify-between mb-2">
                        <label class="label-text">Select Membership Plans</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" class="checkbox checkbox-xs checkbox-primary" onchange="toggleAllItems('memberships')">
                            <span>Select All</span>
                        </label>
                    </div>
                    @if($membershipPlans->isEmpty())
                        <p class="text-sm text-base-content/60 bg-base-200/50 rounded-lg p-3">No active membership plans found.</p>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto bg-base-200/50 rounded-lg p-3">
                            @foreach($membershipPlans as $plan)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-base-300/50 p-1 rounded">
                                    <input type="checkbox" name="applicable_item_ids[]" value="membership_{{ $plan->id }}"
                                           class="checkbox checkbox-xs checkbox-primary item-checkbox-memberships"
                                           {{ in_array('membership_' . $plan->id, $existingItems) ? 'checked' : '' }}>
                                    <span class="text-sm">{{ $plan->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Class Packs Selection --}}
                <div id="class_packs-items" class="mt-4 {{ $offer->applies_to !== 'class_packs' ? 'hidden' : '' }}">
                    <div class="flex items-center justify-between mb-2">
                        <label class="label-text">Select Class Packs</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" class="checkbox checkbox-xs checkbox-primary" onchange="toggleAllItems('class_packs')">
                            <span>Select All</span>
                        </label>
                    </div>
                    @if($classPacks->isEmpty())
                        <p class="text-sm text-base-content/60 bg-base-200/50 rounded-lg p-3">No active class packs found.</p>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto bg-base-200/50 rounded-lg p-3">
                            @foreach($classPacks as $pack)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-base-300/50 p-1 rounded">
                                    <input type="checkbox" name="applicable_item_ids[]" value="pack_{{ $pack->id }}"
                                           class="checkbox checkbox-xs checkbox-primary item-checkbox-class_packs"
                                           {{ in_array('pack_' . $pack->id, $existingItems) ? 'checked' : '' }}>
                                    <span class="text-sm">{{ $pack->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Specific Items Selection (all types) --}}
                <div id="specific-items" class="mt-4 {{ $offer->applies_to !== 'specific' ? 'hidden' : '' }}">
                    <div class="flex items-center justify-between mb-2">
                        <label class="label-text">Select Specific Items</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" class="checkbox checkbox-xs checkbox-primary" onchange="toggleAllItems('specific')">
                            <span>Select All</span>
                        </label>
                    </div>
                    <div class="max-h-64 overflow-y-auto bg-base-200/50 rounded-lg p-3 space-y-4">
                        @if($classPlans->isNotEmpty())
                            <div>
                                <h4 class="text-xs font-semibold text-base-content/70 uppercase mb-2">Classes</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($classPlans as $classPlan)
                                        <label class="flex items-center gap-2 cursor-pointer hover:bg-base-300/50 p-1 rounded">
                                            <input type="checkbox" name="applicable_item_ids[]" value="class_{{ $classPlan->id }}"
                                                   class="checkbox checkbox-xs checkbox-primary item-checkbox-specific"
                                                   {{ in_array('class_' . $classPlan->id, $existingItems) ? 'checked' : '' }}>
                                            <span class="text-sm">{{ $classPlan->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if($servicePlans->isNotEmpty())
                            <div>
                                <h4 class="text-xs font-semibold text-base-content/70 uppercase mb-2">Services</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($servicePlans as $servicePlan)
                                        <label class="flex items-center gap-2 cursor-pointer hover:bg-base-300/50 p-1 rounded">
                                            <input type="checkbox" name="applicable_item_ids[]" value="service_{{ $servicePlan->id }}"
                                                   class="checkbox checkbox-xs checkbox-primary item-checkbox-specific"
                                                   {{ in_array('service_' . $servicePlan->id, $existingItems) ? 'checked' : '' }}>
                                            <span class="text-sm">{{ $servicePlan->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if($membershipPlans->isNotEmpty())
                            <div>
                                <h4 class="text-xs font-semibold text-base-content/70 uppercase mb-2">Memberships</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($membershipPlans as $plan)
                                        <label class="flex items-center gap-2 cursor-pointer hover:bg-base-300/50 p-1 rounded">
                                            <input type="checkbox" name="applicable_item_ids[]" value="membership_{{ $plan->id }}"
                                                   class="checkbox checkbox-xs checkbox-primary item-checkbox-specific"
                                                   {{ in_array('membership_' . $plan->id, $existingItems) ? 'checked' : '' }}>
                                            <span class="text-sm">{{ $plan->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if($classPacks->isNotEmpty())
                            <div>
                                <h4 class="text-xs font-semibold text-base-content/70 uppercase mb-2">Class Packs</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($classPacks as $pack)
                                        <label class="flex items-center gap-2 cursor-pointer hover:bg-base-300/50 p-1 rounded">
                                            <input type="checkbox" name="applicable_item_ids[]" value="pack_{{ $pack->id }}"
                                                   class="checkbox checkbox-xs checkbox-primary item-checkbox-specific"
                                                   {{ in_array('pack_' . $pack->id, $existingItems) ? 'checked' : '' }}>
                                            <span class="text-sm">{{ $pack->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-4">
                    <label class="label-text" for="plan_scope">Plan Applicability</label>
                    <select id="plan_scope" name="plan_scope" class="select w-full">
                        <option value="all_plans" {{ old('plan_scope', $offer->plan_scope) === 'all_plans' ? 'selected' : '' }}>All Plans</option>
                        <option value="first_time" {{ old('plan_scope', $offer->plan_scope) === 'first_time' ? 'selected' : '' }}>First-time buyers only</option>
                        <option value="trial" {{ old('plan_scope', $offer->plan_scope) === 'trial' ? 'selected' : '' }}>Trial members only</option>
                        <option value="upgrade" {{ old('plan_scope', $offer->plan_scope) === 'upgrade' ? 'selected' : '' }}>Upgrade purchases only</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Target Audience --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Target Audience</h2>

                <div>
                    <label class="label-text" for="target_audience">Who can use this offer?</label>
                    <select id="target_audience" name="target_audience" class="select w-full" onchange="toggleSegmentField()">
                        @foreach($targetAudiences as $key => $label)
                            <option value="{{ $key }}" {{ old('target_audience', $offer->target_audience) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="segment-field" class="mt-4 {{ $offer->target_audience !== 'specific_segment' ? 'hidden' : '' }}">
                    <label class="label-text" for="segment_id">Select Segment</label>
                    <select id="segment_id" name="segment_id" class="select w-full">
                        <option value="">Choose a segment...</option>
                        @foreach($segments as $segment)
                            <option value="{{ $segment->id }}" {{ old('segment_id', $offer->segment_id) == $segment->id ? 'selected' : '' }}>
                                {{ $segment->name }} ({{ $segment->member_count }} members)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Usage Limits --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Usage Limits</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text" for="total_usage_limit">Total Usage Limit</label>
                        <input type="number" id="total_usage_limit" name="total_usage_limit"
                               value="{{ old('total_usage_limit', $offer->total_usage_limit) }}"
                               class="input w-full" min="1" placeholder="Unlimited">
                    </div>

                    <div>
                        <label class="label-text" for="per_member_limit">Per Member Limit</label>
                        <input type="number" id="per_member_limit" name="per_member_limit"
                               value="{{ old('per_member_limit', $offer->per_member_limit) }}"
                               class="input w-full" min="1" placeholder="Unlimited">
                    </div>

                    <div>
                        <label class="label-text" for="first_x_users">First X Users Only</label>
                        <input type="number" id="first_x_users" name="first_x_users"
                               value="{{ old('first_x_users', $offer->first_x_users) }}"
                               class="input w-full" min="1" placeholder="No limit">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="label label-text cursor-pointer justify-start gap-2">
                        <input type="checkbox" name="auto_stop_on_limit" value="1" class="checkbox checkbox-primary checkbox-sm"
                               {{ old('auto_stop_on_limit', $offer->auto_stop_on_limit) ? 'checked' : '' }}>
                        <span>Automatically pause offer when limit is reached</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Options --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Options</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <h3 class="font-medium text-sm">Redemption Method</h3>
                        <label class="label label-text cursor-pointer justify-start gap-2">
                            <input type="checkbox" name="auto_apply" value="1" class="checkbox checkbox-primary checkbox-sm"
                                   {{ old('auto_apply', $offer->auto_apply) ? 'checked' : '' }}>
                            <span>Auto-apply if client is eligible</span>
                        </label>
                        <label class="label label-text cursor-pointer justify-start gap-2">
                            <input type="checkbox" name="require_code" value="1" class="checkbox checkbox-primary checkbox-sm"
                                   {{ old('require_code', $offer->require_code) ? 'checked' : '' }}>
                            <span>Require promo code to redeem</span>
                        </label>
                        <label class="label label-text cursor-pointer justify-start gap-2">
                            <input type="checkbox" name="can_combine" value="1" class="checkbox checkbox-primary checkbox-sm"
                                   {{ old('can_combine', $offer->can_combine) ? 'checked' : '' }}>
                            <span>Can be combined with other offers</span>
                        </label>
                    </div>

                    <div class="space-y-3">
                        <h3 class="font-medium text-sm">Channel Restrictions</h3>
                        <label class="label label-text cursor-pointer justify-start gap-2">
                            <input type="checkbox" name="online_booking_only" value="1" class="checkbox checkbox-primary checkbox-sm"
                                   {{ old('online_booking_only', $offer->online_booking_only) ? 'checked' : '' }}>
                            <span>Online booking only</span>
                        </label>
                        <label class="label label-text cursor-pointer justify-start gap-2">
                            <input type="checkbox" name="front_desk_only" value="1" class="checkbox checkbox-primary checkbox-sm"
                                   {{ old('front_desk_only', $offer->front_desk_only) ? 'checked' : '' }}>
                            <span>Front desk only</span>
                        </label>
                        <label class="label label-text cursor-pointer justify-start gap-2">
                            <input type="checkbox" name="manual_override_allowed" value="1" class="checkbox checkbox-primary checkbox-sm"
                                   {{ old('manual_override_allowed', $offer->manual_override_allowed) ? 'checked' : '' }}>
                            <span>Allow manual override by staff</span>
                        </label>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-base-300">
                    <h3 class="font-medium text-sm mb-3">Invoice Display</h3>
                    <label class="label label-text cursor-pointer justify-start gap-2">
                        <input type="checkbox" name="show_on_invoice" value="1" class="checkbox checkbox-primary checkbox-sm"
                               {{ old('show_on_invoice', $offer->show_on_invoice) ? 'checked' : '' }}>
                        <span>Show discount line item on invoice</span>
                    </label>
                    <div class="mt-3">
                        <label class="label-text" for="invoice_line_text">Custom Invoice Text (optional)</label>
                        <input type="text" id="invoice_line_text" name="invoice_line_text"
                               value="{{ old('invoice_line_text', $offer->invoice_line_text) }}"
                               class="input w-full" placeholder="e.g., Summer Promo Discount">
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <form action="{{ route('offers.destroy', $offer) }}" method="POST" class="inline"
                  onsubmit="return confirm('Are you sure you want to delete this offer?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-ghost text-error">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete Offer
                </button>
            </form>

            <div class="flex items-center gap-3">
                <a href="{{ route('offers.show', $offer) }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--check] size-5"></span>
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    function toggleDiscountFields() {
        const type = document.querySelector('input[name="discount_type"]:checked')?.value;

        document.getElementById('percentage-field').classList.toggle('hidden', type !== 'percentage');
        document.getElementById('fixed-amount-field').classList.toggle('hidden', type !== 'fixed_amount');
        document.getElementById('buy-x-get-y-fields').classList.toggle('hidden', type !== 'buy_x_get_y');
        document.getElementById('free-classes-field').classList.toggle('hidden', type !== 'free_class');
    }

    function toggleApplicableItems() {
        const appliesTo = document.getElementById('applies_to').value;

        // Hide all item sections first
        document.getElementById('classes-items').classList.add('hidden');
        document.getElementById('services-items').classList.add('hidden');
        document.getElementById('memberships-items').classList.add('hidden');
        document.getElementById('class_packs-items').classList.add('hidden');
        document.getElementById('specific-items').classList.add('hidden');

        // Show the appropriate section based on selection
        switch (appliesTo) {
            case 'classes':
                document.getElementById('classes-items').classList.remove('hidden');
                break;
            case 'services':
                document.getElementById('services-items').classList.remove('hidden');
                break;
            case 'memberships':
                document.getElementById('memberships-items').classList.remove('hidden');
                break;
            case 'class_packs':
                document.getElementById('class_packs-items').classList.remove('hidden');
                break;
            case 'specific':
                document.getElementById('specific-items').classList.remove('hidden');
                break;
            // 'all' and 'retail' don't show any items
        }
    }

    function toggleAllItems(type) {
        const checkboxes = document.querySelectorAll(`.item-checkbox-${type}`);
        const selectAllCheckbox = event.target;
        const isChecked = selectAllCheckbox.checked;

        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
    }

    function toggleSegmentField() {
        const target = document.getElementById('target_audience').value;
        document.getElementById('segment-field').classList.toggle('hidden', target !== 'specific_segment');
    }

    // Initialize
    toggleDiscountFields();
    toggleSegmentField();
</script>
@endpush
@endsection
