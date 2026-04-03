@extends('layouts.dashboard')

@section('title', $trans['page.sell_membership'] ?? 'Sell Membership')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('schedule.calendar') }}"><span class="icon-[tabler--calendar] me-1 size-4"></span> {{ $trans['nav.calendar'] ?? 'Calendar' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['page.sell_membership'] ?? 'Sell Membership' }}</li>
    </ol>
@endsection

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ $selectedClassSession ? route('membership-schedules.index') : route('schedule.calendar') }}" class="btn btn-ghost btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['page.sell_membership'] ?? 'Sell Membership' }}</h1>
            <p class="text-base-content/60">{{ $trans['walk_in.sell_membership_desc'] ?? 'Sell a membership plan to a client' }}</p>
        </div>
    </div>

    {{-- Selected Class Session Info --}}
    @if($selectedClassSession)
        <div class="alert alert-info mb-6">
            <span class="icon-[tabler--calendar-event] size-5"></span>
            <div>
                <h3 class="font-semibold">Booking for: {{ $selectedClassSession->display_title }}</h3>
                <p class="text-sm">
                    {{ $selectedClassSession->start_time->format('l, M j, Y \a\t g:i A') }}
                    @if($selectedClassSession->primaryInstructor)
                        &bull; {{ $selectedClassSession->primaryInstructor->name }}
                    @endif
                    @if($selectedClassSession->location)
                        &bull; {{ $selectedClassSession->location->name }}
                    @endif
                </p>
            </div>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
    <div class="alert alert-error mb-6">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <div>
            <div class="font-medium">{{ $trans['common.fix_errors'] ?? 'Please fix the following errors:' }}</div>
            <ul class="mt-1 text-sm list-disc list-inside">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Session Error --}}
    @if (session('error'))
    <div class="alert alert-error mb-6">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <form id="membership-form" action="{{ route('walk-in.membership.book') }}" method="POST">
        @csrf

        @if($selectedClassSession)
            <input type="hidden" name="class_session_id" value="{{ $selectedClassSession->id }}">
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-3 space-y-6">
                {{-- Client Selection --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body">
                        <h2 class="card-title mb-4">
                            <span class="icon-[tabler--user] size-5"></span>
                            {{ $trans['walk_in.select_client'] ?? 'Select Client' }}
                        </h2>

                        {{-- Client Selection Form (hidden when client is selected) --}}
                        <div id="client-selection-form">
                            {{-- Client Type Selection --}}
                            <div id="client-type-selection" class="grid grid-cols-2 gap-3 mb-4">
                                <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                    <input type="radio" name="client_type" value="existing" class="radio radio-primary" checked>
                                    <span class="icon-[tabler--users] size-6 text-primary"></span>
                                    <div>
                                        <span class="font-semibold">{{ $trans['walk_in.existing_client'] ?? 'Existing Client' }}</span>
                                        <span class="text-xs text-base-content/60 block">{{ $trans['walk_in.search_client_list'] ?? 'Search client list' }}</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-4 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all">
                                    <input type="radio" name="client_type" value="new" class="radio radio-success">
                                    <span class="icon-[tabler--user-plus] size-6 text-success"></span>
                                    <div>
                                        <span class="font-semibold">{{ $trans['walk_in.new_client'] ?? 'New Client' }}</span>
                                        <span class="text-xs text-base-content/60 block">{{ $trans['walk_in.create_new_profile'] ?? 'Create new profile' }}</span>
                                    </div>
                                </label>
                            </div>

                            {{-- Existing Client Search --}}
                            <div id="existing-client-section">
                                <div class="form-control mb-4">
                                    <div class="relative">
                                        <span class="icon-[tabler--search] size-5 text-base-content/50 absolute left-3 top-1/2 -translate-y-1/2"></span>
                                        <input type="text"
                                               id="client-search"
                                               class="input input-bordered w-full pl-10"
                                               placeholder="{{ $trans['walk_in.search_placeholder'] ?? 'Search by name, email or phone...' }}">
                                    </div>
                                </div>
                                <div id="client-search-results" class="space-y-2"></div>
                            </div>

                            {{-- New Client Form --}}
                            <div id="new-client-section" class="hidden space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="form-control">
                                        <label class="label-text" for="new_first_name">{{ $trans['field.first_name'] ?? 'First Name' }}</label>
                                        <input type="text" id="new_first_name" class="input input-bordered" placeholder="John">
                                    </div>
                                    <div class="form-control">
                                        <label class="label-text" for="new_last_name">{{ $trans['field.last_name'] ?? 'Last Name' }}</label>
                                        <input type="text" id="new_last_name" class="input input-bordered" placeholder="Doe">
                                    </div>
                                </div>
                                <div class="form-control">
                                    <label class="label-text" for="new_email">{{ $trans['field.email'] ?? 'Email' }}</label>
                                    <input type="email" id="new_email" class="input input-bordered" placeholder="john@example.com">
                                </div>
                                <div class="form-control">
                                    <label class="label-text" for="new_phone">{{ $trans['field.phone'] ?? 'Phone' }}</label>
                                    <input type="tel" id="new_phone" class="input input-bordered" placeholder="+1 234 567 8900">
                                </div>
                                <button type="button" id="create-client-btn" class="btn btn-success btn-sm">
                                    <span class="icon-[tabler--plus] size-4"></span>
                                    {{ $trans['btn.create_client'] ?? 'Create Client' }}
                                </button>
                            </div>
                        </div>

                        {{-- Selected Client Display --}}
                        <div id="selected-client" class="hidden p-4 bg-primary/5 border border-primary/20 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div id="selected-client-avatar" class="avatar placeholder">
                                        <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold">
                                            <span id="selected-client-initials">JD</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div id="selected-client-name" class="font-semibold">John Doe</div>
                                        <div id="selected-client-email" class="text-sm text-base-content/60">john@example.com</div>
                                    </div>
                                </div>
                                <button type="button" onclick="clearSelectedClient()" class="btn btn-ghost btn-sm btn-circle">
                                    <span class="icon-[tabler--x] size-4"></span>
                                </button>
                            </div>
                            <input type="hidden" name="client_id" id="client_id" value="">
                        </div>
                    </div>
                </div>

                {{-- Membership Plan Selection / Display --}}
                @if($preselectedMembershipPlanId && $selectedClassSession)
                    {{-- Pre-selected membership - show as fixed selection --}}
                    @php
                        $selectedPlan = $membershipPlans->firstWhere('id', $preselectedMembershipPlanId);
                    @endphp
                    @if($selectedPlan)
                        <div class="card bg-base-100 border border-primary/30 bg-primary/5">
                            <div class="card-body">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center">
                                        <span class="icon-[tabler--id-badge-2] size-6 text-primary"></span>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold">{{ $selectedPlan->name }}</h2>
                                        <p class="text-sm text-base-content/60">Membership Enrollment</p>
                                    </div>
                                    <div class="ml-auto text-right">
                                        <div class="text-2xl font-bold text-primary">{{ $selectedPlan->getFormattedPriceForCurrency($defaultCurrency) }}</div>
                                        <div class="text-sm text-base-content/60">{{ $selectedPlan->formatted_interval }}</div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <span class="badge badge-soft {{ $selectedPlan->type_badge_class }}">{{ $selectedPlan->formatted_type }}</span>
                                    @if($selectedPlan->type === 'credits')
                                        <span class="badge badge-soft badge-info">{{ $selectedPlan->credits_per_cycle }} credits/cycle</span>
                                    @endif
                                </div>

                                @if($selectedPlan->description)
                                    <p class="text-sm text-base-content/70 mt-3">{{ $selectedPlan->description }}</p>
                                @endif

                                <input type="hidden" name="membership_plan_id" value="{{ $selectedPlan->id }}"
                                       data-price="{{ $selectedPlan->getPriceForCurrency($defaultCurrency) }}"
                                       data-name="{{ $selectedPlan->name }}"
                                       data-billing-discounts='@json($selectedPlan->billing_discounts ?? [])'
                                       data-registration-fee="{{ $selectedPlan->registration_fee ?? 0 }}"
                                       data-cancellation-fee="{{ $selectedPlan->cancellation_fee ?? 0 }}"
                                       data-grace-hours="{{ $selectedPlan->cancellation_grace_hours ?? 48 }}"
                                       data-interval="{{ $selectedPlan->interval }}"
                                       id="preselected-plan">
                            </div>
                        </div>
                    @endif
                @else
                    {{-- Regular membership plan selection --}}
                    <div class="card bg-base-100 border border-base-200">
                        <div class="card-body">
                            <h2 class="card-title mb-4">
                                <span class="icon-[tabler--id-badge-2] size-5"></span>
                                {{ $trans['walk_in.select_membership_plan'] ?? 'Select Membership Plan' }}
                            </h2>

                            @if($membershipPlans->isEmpty())
                                <div class="text-center py-8">
                                    <span class="icon-[tabler--package-off] size-12 text-base-content/20"></span>
                                    <p class="text-base-content/60 mt-2">{{ $trans['walk_in.no_membership_plans'] ?? 'No active membership plans available.' }}</p>
                                    <a href="{{ route('membership-plans.create') }}" class="btn btn-primary btn-sm mt-4">
                                        {{ $trans['btn.create_membership_plan'] ?? 'Create Membership Plan' }}
                                    </a>
                                </div>
                            @else
                                <div class="space-y-2">
                                    @foreach($membershipPlans as $plan)
                                        <label class="membership-plan-option flex items-center gap-4 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                            <input type="radio" name="membership_plan_id" value="{{ $plan->id }}"
                                                   data-price="{{ $plan->getPriceForCurrency($defaultCurrency) }}"
                                                   data-name="{{ $plan->name }}"
                                                   data-billing-discounts='@json($plan->billing_discounts ?? [])'
                                                   data-registration-fee="{{ $plan->registration_fee ?? 0 }}"
                                                   data-cancellation-fee="{{ $plan->cancellation_fee ?? 0 }}"
                                                   data-grace-hours="{{ $plan->cancellation_grace_hours ?? 48 }}"
                                                   data-interval="{{ $plan->interval }}"
                                                   class="radio radio-primary shrink-0">
                                            <div class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $plan->color }}"></div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-semibold">{{ $plan->name }}</span>
                                                    <span class="badge badge-soft badge-xs {{ $plan->type_badge_class }}">{{ $plan->formatted_type }}</span>
                                                    @if($plan->type === 'credits')
                                                        <span class="badge badge-soft badge-xs badge-info">{{ $plan->credits_per_cycle }} credits</span>
                                                    @endif
                                                </div>
                                                @if($plan->description)
                                                    <p class="text-xs text-base-content/60 mt-0.5 line-clamp-1">{{ $plan->description }}</p>
                                                @endif
                                            </div>
                                            <div class="text-right shrink-0">
                                                <div class="font-bold text-primary">{{ $plan->getFormattedPriceForCurrency($defaultCurrency) }}</div>
                                                <div class="text-xs text-base-content/50">{{ $plan->formatted_interval }}</div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Billing Period Selection --}}
                <div id="billing-period-section" class="card bg-base-100 border border-base-200 hidden">
                    <div class="card-body">
                        <h2 class="card-title mb-2">
                            <span class="icon-[tabler--discount] size-5"></span>
                            Billing Period
                        </h2>
                        <p class="text-sm text-base-content/60 mb-4">Select a billing period. Longer commitments may include discounts.</p>

                        <div id="billing-period-options" class="flex gap-2 flex-wrap"></div>

                        <input type="hidden" name="billing_period" id="billing_period" value="">
                    </div>
                </div>

                {{-- Linked Schedules --}}
                <div id="schedules-section" class="card bg-base-100 border border-base-200 hidden">
                    <div class="card-body">
                        <h2 class="card-title mb-2">
                            <span class="icon-[tabler--calendar-repeat] size-5"></span>
                            Linked Schedules
                        </h2>
                        <p class="text-sm text-base-content/60 mb-4">Select which schedules to enroll the client into.</p>

                        <div id="schedules-loading" class="hidden text-center py-4">
                            <span class="loading loading-spinner loading-md"></span>
                        </div>

                        <div id="schedules-list" class="space-y-2"></div>

                        <div id="schedules-empty" class="hidden text-center py-6">
                            <span class="icon-[tabler--calendar-off] size-8 text-base-content/20"></span>
                            <p class="text-sm text-base-content/50 mt-2">No schedules linked to this membership plan.</p>
                        </div>

                        {{-- Extra Charge for Multiple Schedules --}}
                        <div id="extra-charge-section" class="hidden mt-4 pt-4 border-t border-base-200">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="icon-[tabler--coin] size-4 text-warning"></span>
                                <span class="font-medium text-sm">Extra Schedule Charge</span>
                                <span class="badge badge-warning badge-xs">Multiple Schedules</span>
                            </div>
                            <p class="text-xs text-base-content/60 mb-2">Client is enrolling into multiple schedules. Add an extra charge if needed.</p>
                            <label class="input input-bordered input-sm flex items-center gap-1 w-full max-w-xs">
                                <span class="text-base-content/50 text-xs">$</span>
                                <input type="number" name="extra_schedule_charge" id="extra_schedule_charge" step="0.01" min="0"
                                       value="" class="grow w-full" placeholder="0.00">
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Start Date --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body">
                        <h2 class="card-title mb-4">
                            <span class="icon-[tabler--calendar] size-5"></span>
                            {{ $trans['field.start_date'] ?? 'Start Date' }}
                        </h2>
                        <div class="form-control">
                            <input type="date" name="start_date" id="start_date"
                                   class="input input-bordered w-full max-w-xs"
                                   value="{{ now()->format('Y-m-d') }}"
                                   min="{{ now()->format('Y-m-d') }}">
                            <p class="text-xs text-base-content/60 mt-1">{{ $trans['walk_in.membership_start_help'] ?? 'When should the membership start?' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Payment --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body">
                        <h2 class="card-title mb-4">
                            <span class="icon-[tabler--cash] size-5"></span>
                            {{ $trans['drawer.payment'] ?? 'Payment' }}
                        </h2>

                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                <input type="radio" name="payment_method" value="manual" class="radio radio-primary" checked>
                                <span class="icon-[tabler--wallet] size-5 text-primary"></span>
                                <span class="font-medium">{{ $trans['walk_in.manual_payment'] ?? 'Manual Payment' }}</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-success has-[:checked]:bg-success/5 transition-all">
                                <input type="radio" name="payment_method" value="comp" class="radio radio-success">
                                <span class="icon-[tabler--gift] size-5 text-success"></span>
                                <span class="font-medium">{{ $trans['walk_in.complimentary'] ?? 'Complimentary' }}</span>
                            </label>
                        </div>

                        {{-- Manual Payment Options --}}
                        <div id="manual-payment-options" class="mt-4 space-y-3">
                            <div class="form-control">
                                <label class="label-text" for="manual_method">{{ $trans['walk_in.payment_method'] ?? 'Payment Method' }}</label>
                                <select name="manual_method" id="manual_method" class="select select-bordered w-full">
                                    <option value="cash">{{ $trans['payment.cash'] ?? 'Cash' }}</option>
                                    <option value="card">{{ $trans['payment.card'] ?? 'Card' }}</option>
                                    <option value="check">{{ $trans['payment.check'] ?? 'Check' }}</option>
                                    <option value="other">{{ $trans['payment.other'] ?? 'Other' }}</option>
                                </select>
                            </div>
                            @php
                                $priceEditable = ($canOverridePrice ?? false) || (!($canOverridePrice ?? false) && !($canRequestOverride ?? false));
                            @endphp
                            <div class="form-control">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="label-text" for="price_paid">{{ $trans['walk_in.amount_paid'] ?? 'Amount Paid' }}</label>
                                    @if($priceEditable)
                                    <span class="text-success text-xs flex items-center gap-1">
                                        <span class="icon-[tabler--pencil] size-3"></span>
                                        Editable
                                    </span>
                                    @endif
                                </div>
                                <label class="input input-bordered flex items-center gap-2">
                                    <span class="text-base-content/60">{{ $currencySymbols[$defaultCurrency] ?? '$' }}</span>
                                    <input type="number" name="price_paid" id="price_paid" step="0.01" min="0"
                                           class="grow w-full" placeholder="0.00">
                                </label>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="form-control mt-4">
                            <label class="label-text" for="notes">{{ $trans['field.notes_optional'] ?? 'Notes (optional)' }}</label>
                            <textarea name="notes" id="notes" rows="2" class="textarea textarea-bordered" placeholder="{{ $trans['walk_in.payment_notes'] ?? 'Payment notes...' }}"></textarea>
                        </div>

                        {{-- Price Override --}}
                        <input type="hidden" name="price_override_code" id="price_override_code" value="">
                        <input type="hidden" name="price_override_amount" id="price_override_amount" value="">

                        @if($canOverridePrice ?? false)
                        {{-- Direct price edit for managers/owners --}}
                        <div class="mt-4 pt-4 border-t border-base-200">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="icon-[tabler--receipt-refund] size-4 text-primary"></span>
                                <span class="font-medium text-sm">Price Override</span>
                                <span class="badge badge-success badge-xs">Authorized</span>
                            </div>

                            <div id="applied-override" class="hidden mb-2">
                                <div class="alert alert-sm bg-primary/10 border-primary/20 py-2">
                                    <span class="icon-[tabler--check] size-4 text-primary"></span>
                                    <div class="flex-1 text-sm">
                                        <span class="font-semibold text-primary" id="applied-override-code">Direct Override</span>
                                        <span class="text-primary/80" id="applied-override-price"></span>
                                    </div>
                                    <button type="button" onclick="removeOverride()" class="btn btn-ghost btn-xs btn-circle">
                                        <span class="icon-[tabler--x] size-3"></span>
                                    </button>
                                </div>
                            </div>

                            <div id="override-input-section">
                                <div class="join w-full">
                                    <span class="join-item btn btn-sm no-animation">$</span>
                                    <input type="number" step="0.01" min="0" id="direct-override-price"
                                           class="input input-bordered input-sm join-item flex-1"
                                           placeholder="Enter new price...">
                                    <button type="button" onclick="applyDirectOverride()" class="btn btn-primary btn-sm join-item">
                                        Apply
                                    </button>
                                </div>
                                <p class="text-xs text-base-content/50 mt-1">You have override permission.</p>
                            </div>

                            <p id="override-error" class="text-error text-xs mt-1 hidden"></p>
                        </div>
                        @elseif($canRequestOverride ?? false)
                        {{-- Override request flow for staff --}}
                        <div class="mt-4 pt-4 border-t border-base-200">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="icon-[tabler--receipt-refund] size-4 text-primary"></span>
                                <span class="font-medium text-sm">Price Override</span>
                            </div>

                            <div id="applied-override" class="hidden mb-2">
                                <div class="alert alert-sm bg-primary/10 border-primary/20 py-2">
                                    <span class="icon-[tabler--check] size-4 text-primary"></span>
                                    <div class="flex-1 text-sm">
                                        <span class="font-semibold text-primary" id="applied-override-code"></span>
                                        <span class="text-primary/80" id="applied-override-price"></span>
                                    </div>
                                    <button type="button" onclick="removeOverride()" class="btn btn-ghost btn-xs btn-circle">
                                        <span class="icon-[tabler--x] size-3"></span>
                                    </button>
                                </div>
                            </div>

                            <div id="override-input-section">
                                <div class="flex gap-2">
                                    <input type="text" id="override_code_input" placeholder="PO-XXXXX or MY-XXXXX"
                                           class="input input-bordered input-sm flex-1 uppercase" maxlength="10">
                                    <button type="button" onclick="verifyOverrideCode()" id="verify-override-btn"
                                            class="btn btn-sm btn-outline">Verify</button>
                                </div>
                                <button type="button" onclick="showOverrideModal()" class="btn btn-outline btn-primary btn-xs btn-block mt-2">
                                    <span class="icon-[tabler--send] size-3"></span>
                                    Request Override
                                </button>
                            </div>

                            <div id="override-pending" class="hidden">
                                <div class="alert alert-sm bg-warning/10 border-warning/20 py-2">
                                    <span class="icon-[tabler--clock] size-4 text-warning animate-pulse"></span>
                                    <span class="text-sm text-warning">Pending: <span id="pending-code" class="font-mono font-bold"></span></span>
                                    <button type="button" onclick="checkOverrideStatus()" class="btn btn-ghost btn-xs"><span class="icon-[tabler--refresh] size-3"></span></button>
                                </div>
                            </div>
                            <p id="override-error" class="text-error text-xs mt-1 hidden"></p>
                            <p id="fetch-override-message" class="text-xs mt-1 hidden"></p>
                        </div>
                        @endif

                        {{-- Price Override Modals - Only show when feature is enabled --}}
                        @if($canRequestOverride ?? false)
                        {{-- Price Override Modal --}}
                        <div id="override-modal" class="hidden fixed inset-0 z-50" role="dialog">
                            <div class="fixed inset-0 bg-black/50" onclick="closeOverrideModal()"></div>
                            <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-base-100 rounded-lg shadow-xl z-10 w-full max-w-md p-6">
                                <button type="button" onclick="closeOverrideModal()" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"><span class="icon-[tabler--x] size-5"></span></button>
                                <h3 class="font-bold text-lg mb-4">Request Price Override</h3>
                                <div class="space-y-4">
                                    <div class="flex justify-between text-sm p-3 bg-base-200 rounded-lg">
                                        <span>Original Price</span><span class="font-semibold" id="modal-original-price">$0.00</span>
                                    </div>
                                    <div class="form-control">
                                        <label class="label"><span class="label-text">New Price *</span></label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50">$</span>
                                            <input type="number" id="override-new-price" step="0.01" min="0" class="input input-bordered w-full pl-8" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="form-control">
                                        <label class="label"><span class="label-text">Reason (optional)</span></label>
                                        <textarea id="override-reason" rows="2" class="textarea textarea-bordered"></textarea>
                                    </div>
                                    <p id="modal-error" class="text-error text-sm hidden"></p>
                                </div>
                                <div class="flex justify-end gap-2 mt-6">
                                    <button type="button" onclick="closeOverrideModal()" class="btn btn-ghost">Cancel</button>
                                    <button type="button" onclick="submitOverrideRequest()" id="submit-override-btn" class="btn btn-primary"><span class="icon-[tabler--send] size-4"></span> Send</button>
                                </div>
                            </div>
                        </div>

                        {{-- Personal Override Modal --}}
                        <div id="personal-override-modal" class="hidden fixed inset-0 z-50" role="dialog">
                            <div class="fixed inset-0 bg-black/50" onclick="closePersonalOverrideModal()"></div>
                            <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-base-100 rounded-lg shadow-xl z-10 w-full max-w-md p-6">
                                <button type="button" onclick="closePersonalOverrideModal()" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"><span class="icon-[tabler--x] size-5"></span></button>
                                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                                    <span class="icon-[tabler--shield-check] size-5 text-success"></span>
                                    Override Price
                                </h3>
                                <div class="alert alert-success mb-4">
                                    <span class="icon-[tabler--user-check] size-5"></span>
                                    <div>
                                        <p class="font-semibold">Supervised by</p>
                                        <p class="text-sm"><span id="personal-supervisor-name">Manager</span> (<span id="personal-supervisor-code">MY-XXXXX</span>)</p>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex justify-between text-sm p-3 bg-base-200 rounded-lg">
                                        <span>Original Price</span><span class="font-semibold" id="personal-modal-original-price">$0.00</span>
                                    </div>
                                    <div class="form-control">
                                        <label class="label"><span class="label-text">New Price *</span></label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50">$</span>
                                            <input type="number" id="personal-override-new-price" step="0.01" min="0" class="input input-bordered w-full pl-8" placeholder="0.00" oninput="updatePersonalOverridePreview()">
                                        </div>
                                    </div>
                                    <div id="personal-override-preview" class="hidden p-3 bg-success/10 border border-success/20 rounded-lg text-sm">
                                        <div class="flex justify-between"><span>Discount</span><span class="font-semibold text-success" id="personal-preview-discount">$0.00</span></div>
                                        <div class="flex justify-between mt-1"><span>Percentage</span><span class="font-semibold text-success" id="personal-preview-percent">0%</span></div>
                                    </div>
                                    <p id="personal-modal-error" class="text-error text-sm hidden"></p>
                                </div>
                                <div class="flex justify-end gap-2 mt-6">
                                    <button type="button" onclick="closePersonalOverrideModal()" class="btn btn-ghost">Cancel</button>
                                    <button type="button" onclick="applyPersonalOverride()" class="btn btn-success"><span class="icon-[tabler--check] size-4"></span> Apply Override</button>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($selectedClassSession)
                            {{-- Book into Class Option --}}
                            <div class="form-control mt-4 pt-4 border-t border-base-200">
                                <label class="cursor-pointer flex items-start gap-3">
                                    <input type="checkbox" name="book_into_class" value="1" class="checkbox checkbox-primary mt-0.5" checked>
                                    <div>
                                        <span class="font-medium">{{ $trans['walk_in.also_book_class'] ?? 'Also book into class' }}</span>
                                        <p class="text-xs text-base-content/60">{{ $trans['walk_in.book_into_class_desc'] ?? 'Book the client into' }} "{{ $selectedClassSession->display_title }}" {{ $trans['walk_in.using_membership'] ?? 'using this membership' }}</p>
                                    </div>
                                </label>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Summary --}}
                <div class="card bg-base-100 border border-base-200">
                    <div class="card-body">
                        <h2 class="card-title mb-4">
                            <span class="icon-[tabler--receipt] size-5"></span>
                            {{ $trans['walk_in.summary'] ?? 'Summary' }}
                        </h2>
                        <div id="summary-content" class="space-y-3 text-sm">
                            {{-- Info rows --}}
                            <div class="flex justify-between items-center">
                                <span class="text-base-content/60">{{ $trans['field.client'] ?? 'Client' }}</span>
                                <span id="summary-client" class="font-medium text-right">{{ $trans['common.not_selected'] ?? 'Not selected' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-base-content/60">{{ $trans['field.membership'] ?? 'Membership' }}</span>
                                <span id="summary-plan" class="font-medium text-right">{{ $trans['common.not_selected'] ?? 'Not selected' }}</span>
                            </div>
                            <div id="summary-period-row" class="flex justify-between items-center hidden">
                                <span class="text-base-content/60">Billing Period</span>
                                <span id="summary-period" class="font-medium"></span>
                            </div>
                            <div id="summary-schedules-row" class="flex justify-between items-center hidden">
                                <span class="text-base-content/60">Schedules</span>
                                <span id="summary-schedules" class="font-medium"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-base-content/60">{{ $trans['field.start_date'] ?? 'Start Date' }}</span>
                                <span id="summary-date" class="font-medium">{{ now()->format('M j, Y') }}</span>
                            </div>

                            {{-- Price breakdown --}}
                            <div class="border-t border-base-200 pt-3 space-y-2">
                                <div id="summary-base-row" class="flex justify-between hidden">
                                    <span class="text-base-content/60">Plan Price</span>
                                    <span id="summary-base-price" class="font-medium"></span>
                                </div>
                                <div id="summary-savings-row" class="flex justify-between hidden">
                                    <span class="text-base-content/60">Savings</span>
                                    <span id="summary-savings" class="font-medium text-success"></span>
                                </div>

                                {{-- Registration Fee Checkbox --}}
                                <div id="summary-regfee-row" class="hidden">
                                    <label class="flex items-center justify-between cursor-pointer py-1">
                                        <span class="flex items-center gap-2">
                                            <input type="checkbox" name="charge_registration_fee" id="charge_registration_fee" value="1"
                                                   class="checkbox checkbox-primary checkbox-xs" onchange="updateSummary()">
                                            <span class="text-base-content/60">Registration Fee</span>
                                        </span>
                                        <span id="summary-regfee" class="font-medium"></span>
                                    </label>
                                </div>

                                {{-- Extra Schedule Charge --}}
                                <div id="summary-extra-row" class="flex justify-between hidden">
                                    <span class="text-base-content/60">Extra Schedule Charge</span>
                                    <span id="summary-extra" class="font-medium"></span>
                                </div>
                            </div>

                            {{-- Cancellation policy info --}}
                            <div id="summary-cancel-info" class="hidden border-t border-base-200 pt-2">
                                <div class="flex items-start gap-2 text-xs text-base-content/50">
                                    <span class="icon-[tabler--info-circle] size-3.5 mt-0.5 shrink-0"></span>
                                    <span>
                                        Cancellation fee: <strong id="summary-cancel-fee">$0</strong> &bull;
                                        Grace period: <strong id="summary-grace-hours">48</strong>h
                                    </span>
                                </div>
                            </div>

                            {{-- Total --}}
                            <div class="border-t border-base-200 pt-3">
                                <div class="flex justify-between text-lg">
                                    <span class="font-semibold">{{ $trans['field.total'] ?? 'Total' }}</span>
                                    <span id="summary-total" class="font-bold text-primary">$0.00</span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="submit-btn" class="btn btn-primary w-full mt-4" disabled>
                            <span class="icon-[tabler--check] size-5"></span>
                            {{ $trans['btn.complete_sale'] ?? 'Complete Sale' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const clientSearch = document.getElementById('client-search');
    const clientSearchResults = document.getElementById('client-search-results');
    const existingClientSection = document.getElementById('existing-client-section');
    const newClientSection = document.getElementById('new-client-section');
    const selectedClientDiv = document.getElementById('selected-client');
    const clientIdInput = document.getElementById('client_id');
    const submitBtn = document.getElementById('submit-btn');
    const manualPaymentOptions = document.getElementById('manual-payment-options');
    const pricePaidInput = document.getElementById('price_paid');
    const startDateInput = document.getElementById('start_date');

    let searchTimeout;

    // Client type toggle
    document.querySelectorAll('input[name="client_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'existing') {
                existingClientSection.classList.remove('hidden');
                newClientSection.classList.add('hidden');
            } else {
                existingClientSection.classList.add('hidden');
                newClientSection.classList.remove('hidden');
            }
        });
    });

    // Payment method toggle
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'manual') {
                manualPaymentOptions.classList.remove('hidden');
            } else {
                manualPaymentOptions.classList.add('hidden');
            }
            updateSummary();
        });
    });

    // Client search
    clientSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            clientSearchResults.innerHTML = '';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('walk-in.clients.search') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    clientSearchResults.innerHTML = '';
                    if (data.clients.length === 0) {
                        clientSearchResults.innerHTML = '<p class="text-base-content/60 text-sm p-2">No clients found</p>';
                        return;
                    }

                    data.clients.forEach(client => {
                        const div = document.createElement('div');
                        div.className = 'flex items-center gap-3 p-3 bg-base-200/50 rounded-lg cursor-pointer hover:bg-base-200 transition-colors';
                        div.innerHTML = `
                            <div class="avatar placeholder">
                                <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold text-sm">
                                    ${client.initials || (client.first_name[0] + client.last_name[0]).toUpperCase()}
                                </div>
                            </div>
                            <div>
                                <div class="font-medium">${client.first_name} ${client.last_name}</div>
                                <div class="text-xs text-base-content/60">${client.email || client.phone || ''}</div>
                            </div>
                        `;
                        div.addEventListener('click', () => selectClient(client));
                        clientSearchResults.appendChild(div);
                    });
                });
        }, 300);
    });

    // Create new client
    document.getElementById('create-client-btn').addEventListener('click', function() {
        const firstName = document.getElementById('new_first_name').value.trim();
        const lastName = document.getElementById('new_last_name').value.trim();
        const email = document.getElementById('new_email').value.trim();
        const phone = document.getElementById('new_phone').value.trim();

        if (!firstName || !lastName) {
            alert('Please enter first and last name');
            return;
        }

        fetch('{{ route('walk-in.clients.quick-add') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                first_name: firstName,
                last_name: lastName,
                email: email,
                phone: phone
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                selectClient(data.client);
                // Clear form
                document.getElementById('new_first_name').value = '';
                document.getElementById('new_last_name').value = '';
                document.getElementById('new_email').value = '';
                document.getElementById('new_phone').value = '';
            }
        });
    });

    // Billing period state
    let selectedBillingPeriod = null;
    let currentPlanData = null;
    let selectedScheduleIds = [];
    let schedulesData = [];

    // Membership plan selection
    document.querySelectorAll('input[name="membership_plan_id"]').forEach(radio => {
        radio.addEventListener('change', function() {
            onPlanSelected(this);
        });
    });

    // Handle preselected plan on load
    const preselectedPlan = document.getElementById('preselected-plan');
    if (preselectedPlan) {
        onPlanSelected(preselectedPlan);
    }

    // Start date change
    startDateInput.addEventListener('change', updateSummary);

    function selectClient(client) {
        clientIdInput.value = client.id;
        document.getElementById('selected-client-initials').textContent = client.initials || (client.first_name[0] + client.last_name[0]).toUpperCase();
        document.getElementById('selected-client-name').textContent = `${client.first_name} ${client.last_name}`;
        document.getElementById('selected-client-email').textContent = client.email || client.phone || '';

        selectedClientDiv.classList.remove('hidden');
        document.getElementById('client-selection-form').classList.add('hidden');
        clientSearch.value = '';
        clientSearchResults.innerHTML = '';

        updateSummary();
        updateSubmitButton();
    }

    window.clearSelectedClient = function() {
        clientIdInput.value = '';
        selectedClientDiv.classList.add('hidden');
        document.getElementById('client-selection-form').classList.remove('hidden');
        updateSummary();
        updateSubmitButton();
    };

    function onPlanSelected(planInput) {
        var discounts = {};
        try { discounts = JSON.parse(planInput.dataset.billingDiscounts || '{}'); } catch(e) {}

        currentPlanData = {
            price: parseFloat(planInput.dataset.price) || 0,
            name: planInput.dataset.name,
            billingDiscounts: discounts,
            registrationFee: parseFloat(planInput.dataset.registrationFee) || 0,
            cancellationFee: parseFloat(planInput.dataset.cancellationFee) || 0,
            graceHours: parseInt(planInput.dataset.graceHours) || 48,
            interval: planInput.dataset.interval || 'monthly'
        };

        // Reset billing period
        selectedBillingPeriod = null;
        document.getElementById('billing_period').value = '';

        // Check if plan has any billing discounts
        var hasDiscounts = false;
        if (discounts) {
            for (var k in discounts) {
                if (parseFloat(discounts[k]) > 0) { hasDiscounts = true; break; }
            }
        }

        var billingSection = document.getElementById('billing-period-section');

        if (hasDiscounts) {
            billingSection.classList.remove('hidden');
            renderBillingPeriods(discounts, currentPlanData.price);
        } else {
            billingSection.classList.add('hidden');
        }

        // Reset registration fee checkbox
        var regCheckbox = document.getElementById('charge_registration_fee');
        if (regCheckbox) regCheckbox.checked = false;

        // Fetch linked schedules
        selectedScheduleIds = [];
        schedulesData = [];
        document.getElementById('extra_schedule_charge').value = '';
        fetchMembershipSchedules(planInput.value);

        updateSummary();
        updateSubmitButton();
    }

    function renderBillingPeriods(discounts, basePrice) {
        var container = document.getElementById('billing-period-options');
        var periods = { '1': '1 Month', '3': '3 Months', '6': '6 Months', '9': '9 Months', '12': '12 Months' };
        var html = '';
        var hasAny = false;

        for (var months in periods) {
            var totalAmount = parseFloat(discounts[months]) || 0;
            if (totalAmount <= 0) continue;
            hasAny = true;

            var m = parseInt(months);
            var monthlyRate = m > 0 ? (totalAmount / m) : 0;
            var totalWithout = basePrice * m;
            var savings = totalWithout - totalAmount;

            html += '<button type="button" class="billing-period-btn flex-1 min-w-[100px] flex flex-col items-center p-3 rounded-lg border-2 border-base-content/10 hover:border-success cursor-pointer transition-all" ' +
                'data-months="' + months + '" onclick="selectBillingPeriod(' + months + ')">' +
                '<div class="text-xs text-base-content/60 font-medium">' + periods[months] + '</div>' +
                '<div class="text-lg font-bold text-success">$' + totalAmount.toFixed(2) + '</div>' +
                '<div class="text-[10px] text-base-content/50">$' + monthlyRate.toFixed(2) + '/mo</div>';

            if (savings > 0) {
                html += '<div class="text-[10px] text-success mt-0.5">Save $' + savings.toFixed(2) + '</div>';
            }

            html += '</button>';
        }

        if (!hasAny) {
            container.innerHTML = '<p class="text-sm text-base-content/50">No billing period discounts configured.</p>';
            container.closest('#billing-period-section').querySelector('#billing-period-options').parentElement;
        } else {
            container.innerHTML = html;
        }
    }

    window.selectBillingPeriod = function(months) {
        selectedBillingPeriod = months;
        document.getElementById('billing_period').value = months;

        // Highlight selected
        document.querySelectorAll('.billing-period-btn').forEach(function(btn) {
            if (parseInt(btn.dataset.months) === months) {
                btn.classList.remove('border-base-content/10');
                btn.classList.add('border-success', 'bg-success/10');
            } else {
                btn.classList.add('border-base-content/10');
                btn.classList.remove('border-success', 'bg-success/10');
            }
        });

        updateSummary();
    };

    function fetchMembershipSchedules(planId) {
        var section = document.getElementById('schedules-section');
        var loading = document.getElementById('schedules-loading');
        var list = document.getElementById('schedules-list');
        var empty = document.getElementById('schedules-empty');
        var extraSection = document.getElementById('extra-charge-section');

        section.classList.remove('hidden');
        loading.classList.remove('hidden');
        list.innerHTML = '';
        empty.classList.add('hidden');
        extraSection.classList.add('hidden');

        fetch('{{ route("walk-in.membership-schedules") }}?membership_plan_id=' + planId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                loading.classList.add('hidden');
                schedulesData = data.schedules || [];

                if (schedulesData.length === 0) {
                    empty.classList.remove('hidden');
                    section.classList.add('hidden');
                    return;
                }

                renderSchedulesList(schedulesData);

                // Auto-select first schedule
                if (schedulesData.length > 0) {
                    toggleSchedule(schedulesData[0].id, true);
                }
            })
            .catch(function() {
                loading.classList.add('hidden');
                empty.classList.remove('hidden');
            });
    }

    function renderSchedulesList(schedules) {
        var list = document.getElementById('schedules-list');
        var html = '';

        schedules.forEach(function(schedule) {
            html += '<label class="schedule-option flex items-center gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">' +
                '<input type="checkbox" name="schedule_ids[]" value="' + schedule.id + '" ' +
                'class="checkbox checkbox-primary checkbox-sm schedule-checkbox" ' +
                'data-schedule-id="' + schedule.id + '" ' +
                'onchange="onScheduleToggle()">' +
                '<div class="flex-1 min-w-0">' +
                    '<div class="flex items-center gap-2">' +
                        (schedule.is_recurring
                            ? '<span class="icon-[tabler--calendar-repeat] size-4 text-primary"></span>'
                            : '<span class="icon-[tabler--calendar-event] size-4 text-base-content/40"></span>') +
                        '<span class="font-medium text-sm">' + schedule.title + '</span>' +
                    '</div>' +
                    '<div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-xs text-base-content/60">' +
                        '<span class="flex items-center gap-1">' +
                            '<span class="icon-[tabler--clock] size-3"></span>' +
                            schedule.days + ' &bull; ' + schedule.time +
                        '</span>' +
                        '<span class="flex items-center gap-1">' +
                            '<span class="icon-[tabler--user] size-3"></span>' +
                            schedule.instructor +
                        '</span>' +
                        '<span class="flex items-center gap-1">' +
                            '<span class="icon-[tabler--map-pin] size-3"></span>' +
                            schedule.location +
                        '</span>' +
                    '</div>' +
                '</div>' +
                '<span class="badge badge-soft badge-sm ' + (schedule.session_count > 0 ? 'badge-success' : 'badge-neutral') + '">' +
                    schedule.session_count + ' upcoming' +
                '</span>' +
            '</label>';
        });

        list.innerHTML = html;
    }

    function toggleSchedule(id, checked) {
        var checkbox = document.querySelector('.schedule-checkbox[data-schedule-id="' + id + '"]');
        if (checkbox) {
            checkbox.checked = checked;
            onScheduleToggle();
        }
    }

    window.onScheduleToggle = function() {
        var checkboxes = document.querySelectorAll('.schedule-checkbox:checked');
        selectedScheduleIds = [];
        checkboxes.forEach(function(cb) {
            selectedScheduleIds.push(parseInt(cb.value));
        });

        // Show extra charge input when more than 1 schedule selected
        var extraSection = document.getElementById('extra-charge-section');
        if (selectedScheduleIds.length > 1) {
            extraSection.classList.remove('hidden');
        } else {
            extraSection.classList.add('hidden');
            document.getElementById('extra_schedule_charge').value = '';
        }

        updateSummary();
    };

    // Extra charge input listener
    document.getElementById('extra_schedule_charge').addEventListener('input', function() {
        updateSummary();
    });

    function updateSummary() {
        // Client
        const clientName = document.getElementById('selected-client-name').textContent;
        document.getElementById('summary-client').textContent = clientIdInput.value ? clientName : 'Not selected';

        // Plan
        let selectedPlan = document.querySelector('input[name="membership_plan_id"]:checked');
        const preselectedPlanEl = document.getElementById('preselected-plan');
        if (!selectedPlan && preselectedPlanEl) selectedPlan = preselectedPlanEl;

        // Schedules row
        var schedulesRow = document.getElementById('summary-schedules-row');
        if (selectedScheduleIds.length > 0) {
            schedulesRow.classList.remove('hidden');
            document.getElementById('summary-schedules').textContent = selectedScheduleIds.length + ' selected';
        } else {
            schedulesRow.classList.add('hidden');
        }

        if (selectedPlan) {
            document.getElementById('summary-plan').textContent = selectedPlan.dataset.name;
            const basePrice = parseFloat(selectedPlan.dataset.price) || 0;
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

            var periodRow = document.getElementById('summary-period-row');
            var baseRow = document.getElementById('summary-base-row');
            var savingsRow = document.getElementById('summary-savings-row');
            var regfeeRow = document.getElementById('summary-regfee-row');
            var cancelInfo = document.getElementById('summary-cancel-info');

            var totalPrice = basePrice;
            var regFee = currentPlanData ? currentPlanData.registrationFee : 0;
            var cancelFee = currentPlanData ? currentPlanData.cancellationFee : 0;
            var graceHours = currentPlanData ? currentPlanData.graceHours : 48;

            // Billing period breakdown
            if (selectedBillingPeriod && currentPlanData && currentPlanData.billingDiscounts) {
                var periodTotal = parseFloat(currentPlanData.billingDiscounts[selectedBillingPeriod]) || 0;
                if (periodTotal > 0) {
                    var m = selectedBillingPeriod;
                    var totalWithout = basePrice * m;
                    var savings = totalWithout - periodTotal;

                    totalPrice = periodTotal;

                    periodRow.classList.remove('hidden');
                    document.getElementById('summary-period').textContent = m + ' month' + (m > 1 ? 's' : '');

                    baseRow.classList.remove('hidden');
                    document.getElementById('summary-base-price').textContent = '$' + totalWithout.toFixed(2) + ' (' + m + ' x $' + basePrice.toFixed(2) + ')';

                    if (savings > 0) {
                        savingsRow.classList.remove('hidden');
                        document.getElementById('summary-savings').textContent = '-$' + savings.toFixed(2);
                    } else {
                        savingsRow.classList.add('hidden');
                    }
                } else {
                    periodRow.classList.add('hidden');
                    baseRow.classList.add('hidden');
                    savingsRow.classList.add('hidden');
                }
            } else {
                periodRow.classList.add('hidden');
                baseRow.classList.add('hidden');
                savingsRow.classList.add('hidden');
            }

            // Registration fee — show checkbox if plan has one, charge only when checked
            if (regFee > 0) {
                regfeeRow.classList.remove('hidden');
                document.getElementById('summary-regfee').textContent = '+$' + regFee.toFixed(2);
                var regChecked = document.getElementById('charge_registration_fee').checked;
                if (regChecked) {
                    totalPrice += regFee;
                }
            } else {
                regfeeRow.classList.add('hidden');
            }

            // Cancellation policy info
            if (cancelFee > 0) {
                cancelInfo.classList.remove('hidden');
                document.getElementById('summary-cancel-fee').textContent = '$' + cancelFee.toFixed(2);
                document.getElementById('summary-grace-hours').textContent = graceHours;
            } else {
                cancelInfo.classList.add('hidden');
            }

            // Extra schedule charge
            var extraRow = document.getElementById('summary-extra-row');
            var extraCharge = parseFloat(document.getElementById('extra_schedule_charge').value) || 0;
            if (extraCharge > 0 && selectedScheduleIds.length > 1) {
                extraRow.classList.remove('hidden');
                document.getElementById('summary-extra').textContent = '+$' + extraCharge.toFixed(2);
                totalPrice += extraCharge;
            } else {
                extraRow.classList.add('hidden');
            }

            if (paymentMethod === 'comp') {
                document.getElementById('summary-total').textContent = '$0.00 (Comp)';
            } else {
                document.getElementById('summary-total').textContent = '$' + totalPrice.toFixed(2);
                pricePaidInput.value = totalPrice.toFixed(2);
            }
        } else {
            document.getElementById('summary-plan').textContent = 'Not selected';
            document.getElementById('summary-total').textContent = '$0.00';
            document.getElementById('summary-period-row').classList.add('hidden');
            document.getElementById('summary-base-row').classList.add('hidden');
            document.getElementById('summary-savings-row').classList.add('hidden');
            document.getElementById('summary-regfee-row').classList.add('hidden');
            document.getElementById('summary-extra-row').classList.add('hidden');
            document.getElementById('summary-cancel-info').classList.add('hidden');
            schedulesRow.classList.add('hidden');
        }

        // Date
        const dateValue = startDateInput.value;
        if (dateValue) {
            const date = new Date(dateValue + 'T00:00:00');
            document.getElementById('summary-date').textContent = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }
    }
    window.updateSummary = updateSummary;

    function updateSubmitButton() {
        const hasClient = clientIdInput.value !== '';
        const hasPlan = document.querySelector('input[name="membership_plan_id"]:checked') !== null || document.getElementById('preselected-plan') !== null;
        submitBtn.disabled = !(hasClient && hasPlan);
    }

    // Initial update
    updateSummary();
    updateSubmitButton();

    // If a plan is pre-selected via radio (not hidden input, which is handled above)
    let initialPlan = document.querySelector('input[name="membership_plan_id"]:checked');
    if (initialPlan) {
        onPlanSelected(initialPlan);
    }
});

// Price Override Functions
let pendingOverrideId = null;
let statusCheckInterval = null;
const canOverridePrice = {{ ($canOverridePrice ?? false) ? 'true' : 'false' }};

function getOriginalPrice() {
    if (!currentPlanData) {
        let selectedPlan = document.querySelector('input[name="membership_plan_id"]:checked');
        if (!selectedPlan) selectedPlan = document.getElementById('preselected-plan');
        if (selectedPlan) return parseFloat(selectedPlan.dataset.price) || 0;
        return 0;
    }

    var total = currentPlanData.price;

    // If a billing period is selected, use the period total
    if (selectedBillingPeriod && currentPlanData.billingDiscounts) {
        var periodTotal = parseFloat(currentPlanData.billingDiscounts[selectedBillingPeriod]) || 0;
        if (periodTotal > 0) total = periodTotal;
    }

    // Add registration fee if checked
    var regCheckbox = document.getElementById('charge_registration_fee');
    if (regCheckbox && regCheckbox.checked && currentPlanData.registrationFee > 0) {
        total += currentPlanData.registrationFee;
    }

    // Add extra schedule charge
    var extraCharge = parseFloat(document.getElementById('extra_schedule_charge').value) || 0;
    if (extraCharge > 0 && selectedScheduleIds.length > 1) {
        total += extraCharge;
    }

    return total;
}

// Direct override for managers/owners with permission
function applyDirectOverride() {
    const priceInput = document.getElementById('direct-override-price');
    const newPrice = parseFloat(priceInput.value);
    const originalPrice = getOriginalPrice();

    if (!newPrice || newPrice < 0) {
        showDirectOverrideError('Please enter a valid price.');
        return;
    }

    if (newPrice >= originalPrice) {
        showDirectOverrideError('Override price must be less than original price ($' + originalPrice.toFixed(2) + ').');
        return;
    }

    // Set hidden fields for form submission
    document.getElementById('price_override_code').value = 'DIRECT';
    document.getElementById('price_override_amount').value = newPrice;

    // Update UI
    document.getElementById('applied-override-code').textContent = 'Direct Override';
    document.getElementById('applied-override-price').textContent = ' - $' + newPrice.toFixed(2);
    document.getElementById('applied-override').classList.remove('hidden');
    document.getElementById('override-input-section').classList.add('hidden');

    // Update price display
    const pricePaidInput = document.getElementById('price_paid');
    if (pricePaidInput) pricePaidInput.value = newPrice.toFixed(2);
    document.getElementById('summary-total').textContent = '$' + newPrice.toFixed(2) + ' (Override)';

    // Clear the input
    priceInput.value = '';
    hideDirectOverrideError();
}

function showDirectOverrideError(message) {
    const errorEl = document.getElementById('override-error');
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    }
}

function hideDirectOverrideError() {
    const errorEl = document.getElementById('override-error');
    if (errorEl) {
        errorEl.classList.add('hidden');
    }
}

function showOverrideModal() {
    const modal = document.getElementById('override-modal');
    const originalPrice = getOriginalPrice();
    document.getElementById('modal-original-price').textContent = '$' + originalPrice.toFixed(2);
    document.getElementById('override-new-price').value = '';
    document.getElementById('override-reason').value = '';
    document.getElementById('modal-error').classList.add('hidden');
    modal.classList.remove('hidden');
}

function closeOverrideModal() {
    document.getElementById('override-modal').classList.add('hidden');
}

function submitOverrideRequest() {
    const newPrice = parseFloat(document.getElementById('override-new-price').value);
    const reason = document.getElementById('override-reason').value;
    const originalPrice = getOriginalPrice();
    const modalError = document.getElementById('modal-error');
    const submitBtn = document.getElementById('submit-override-btn');

    if (isNaN(newPrice) || newPrice < 0) {
        modalError.textContent = 'Please enter a valid price.';
        modalError.classList.remove('hidden');
        return;
    }

    if (newPrice >= originalPrice) {
        modalError.textContent = 'New price must be less than original price.';
        modalError.classList.remove('hidden');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span>';

    fetch('{{ route("price-override.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            original_price: originalPrice,
            requested_price: newPrice,
            reason: reason,
            bookable_type: 'membership',
            client_id: document.getElementById('client_id').value || null
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeOverrideModal();
            pendingOverrideId = data.data.id;
            document.getElementById('pending-code').textContent = data.data.confirmation_code;
            document.getElementById('override-input-section').classList.add('hidden');
            document.getElementById('override-pending').classList.remove('hidden');
            startStatusCheck();
        } else {
            modalError.textContent = data.message || 'Failed to create override request.';
            modalError.classList.remove('hidden');
        }
    })
    .catch(() => {
        modalError.textContent = 'Failed to create override request. Please try again.';
        modalError.classList.remove('hidden');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="icon-[tabler--send] size-4"></span> Send';
    });
}

function startStatusCheck() {
    if (statusCheckInterval) clearInterval(statusCheckInterval);
    // Check immediately, then every 5 seconds
    checkOverrideStatus();
    statusCheckInterval = setInterval(checkOverrideStatus, 5000);
}

function stopStatusCheck() {
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
        statusCheckInterval = null;
    }
}

function checkOverrideStatus() {
    if (!pendingOverrideId) return;

    fetch(`{{ url('price-override') }}/${pendingOverrideId}/status`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'approved') {
            stopStatusCheck();
            applyOverride(data.code, data.requested_price);
        } else if (data.status === 'rejected' || data.status === 'expired') {
            stopStatusCheck();
            document.getElementById('override-pending').classList.add('hidden');
            document.getElementById('override-input-section').classList.remove('hidden');
            document.getElementById('override-error').textContent = data.status === 'rejected'
                ? 'Override request was rejected.' + (data.rejection_reason ? ' Reason: ' + data.rejection_reason : '')
                : 'Override request expired.';
            document.getElementById('override-error').classList.remove('hidden');
            pendingOverrideId = null;
        }
    })
    .catch(error => {
        console.error('Status check error:', error);
    });
}

// Personal override state
let personalOverrideCode = null;
let personalOverrideSupervisor = null;

function verifyOverrideCode() {
    const code = document.getElementById('override_code_input').value.trim().toUpperCase();
    const errorEl = document.getElementById('override-error');
    const btn = document.getElementById('verify-override-btn');

    if (!code) {
        errorEl.textContent = 'Please enter a code.';
        errorEl.classList.remove('hidden');
        return;
    }

    errorEl.classList.add('hidden');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

    fetch('{{ route("price-override.verify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ code: code })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = 'Verify';

        if (data.success || data.valid) {
            if (data.is_personal_code) {
                personalOverrideCode = data.code;
                personalOverrideSupervisor = data.data?.authorized_by?.name || 'Manager';
                showPersonalOverrideModal(data.code, personalOverrideSupervisor);
            } else if (data.data?.is_approved || data.valid) {
                applyOverride(data.code || data.data?.confirmation_code, data.requested_price || data.data?.requested_price);
            } else if (data.data?.is_pending) {
                document.getElementById('pending-code').textContent = data.data.confirmation_code;
                document.getElementById('override-input-section').classList.add('hidden');
                document.getElementById('override-pending').classList.remove('hidden');
                pendingOverrideId = data.data.id;
                startStatusCheck();
            }
        } else {
            errorEl.textContent = data.message || 'Invalid or expired code.';
            errorEl.classList.remove('hidden');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = 'Verify';
        errorEl.textContent = 'Error verifying code.';
        errorEl.classList.remove('hidden');
    });
}

function showPersonalOverrideModal(code, supervisorName) {
    document.getElementById('personal-supervisor-name').textContent = supervisorName;
    document.getElementById('personal-supervisor-code').textContent = code;

    // Get current selected plan price
    let selectedPlan = document.querySelector('input[name="membership_plan_id"]:checked');
    if (!selectedPlan) selectedPlan = document.getElementById('preselected-plan');
    const originalPrice = selectedPlan ? parseFloat(selectedPlan.dataset.price) || 0 : 0;

    document.getElementById('personal-modal-original-price').textContent = '$' + originalPrice.toFixed(2);
    document.getElementById('personal-override-new-price').value = '';
    document.getElementById('personal-override-preview').classList.add('hidden');
    document.getElementById('personal-modal-error').classList.add('hidden');
    document.getElementById('personal-override-modal').classList.remove('hidden');
    setTimeout(() => document.getElementById('personal-override-new-price').focus(), 100);
}

function closePersonalOverrideModal() {
    document.getElementById('personal-override-modal').classList.add('hidden');
}

function updatePersonalOverridePreview() {
    const newPrice = parseFloat(document.getElementById('personal-override-new-price').value) || 0;
    let selectedPlan = document.querySelector('input[name="membership_plan_id"]:checked');
    if (!selectedPlan) selectedPlan = document.getElementById('preselected-plan');
    const originalPrice = selectedPlan ? parseFloat(selectedPlan.dataset.price) || 0 : 0;
    const preview = document.getElementById('personal-override-preview');

    if (newPrice > 0 && newPrice < originalPrice) {
        const discount = originalPrice - newPrice;
        document.getElementById('personal-preview-discount').textContent = '$' + discount.toFixed(2);
        document.getElementById('personal-preview-percent').textContent = ((discount / originalPrice) * 100).toFixed(1) + '%';
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
}

function applyPersonalOverride() {
    const newPrice = parseFloat(document.getElementById('personal-override-new-price').value);
    let selectedPlan = document.querySelector('input[name="membership_plan_id"]:checked');
    if (!selectedPlan) selectedPlan = document.getElementById('preselected-plan');
    const originalPrice = selectedPlan ? parseFloat(selectedPlan.dataset.price) || 0 : 0;
    const errorEl = document.getElementById('personal-modal-error');

    if (!newPrice || newPrice < 0) { errorEl.textContent = 'Enter a valid price.'; errorEl.classList.remove('hidden'); return; }
    if (newPrice >= originalPrice) { errorEl.textContent = 'Price must be less than $' + originalPrice.toFixed(2); errorEl.classList.remove('hidden'); return; }

    document.getElementById('price_override_code').value = personalOverrideCode;
    document.getElementById('price_override_amount').value = newPrice;
    document.getElementById('applied-override-code').textContent = 'Supervised by: ' + personalOverrideSupervisor + ' (' + personalOverrideCode + ')';
    document.getElementById('applied-override-price').textContent = ' - $' + newPrice.toFixed(2);
    document.getElementById('applied-override').classList.remove('hidden');
    document.getElementById('override-input-section').classList.add('hidden');

    const pricePaidInput = document.getElementById('price_paid');
    if (pricePaidInput) pricePaidInput.value = newPrice.toFixed(2);
    document.getElementById('summary-total').textContent = '$' + newPrice.toFixed(2) + ' (Personal Override)';

    closePersonalOverrideModal();
    document.getElementById('override_code_input').value = '';
}

function fetchApprovedOverride() {
    const fetchBtn = document.getElementById('fetch-override-btn');
    const msgEl = document.getElementById('fetch-override-message');
    const errorEl = document.getElementById('override-error');

    fetchBtn.disabled = true;
    fetchBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';
    msgEl.classList.add('hidden');
    errorEl.classList.add('hidden');

    fetch('{{ route("price-override.fetch-approved") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            bookable_type: 'membership',
            client_id: document.getElementById('client_id').value || null
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.request) {
            applyOverride(data.request.confirmation_code, data.request.requested_price);
            msgEl.textContent = 'Approved override applied!';
            msgEl.classList.remove('hidden', 'text-error');
            msgEl.classList.add('text-success');
        } else {
            msgEl.textContent = data.message || 'No approved override found.';
            msgEl.classList.remove('hidden', 'text-success');
            msgEl.classList.add('text-error');
        }
    })
    .catch(() => {
        msgEl.textContent = 'Error fetching approved override.';
        msgEl.classList.remove('hidden', 'text-success');
        msgEl.classList.add('text-error');
    })
    .finally(() => {
        fetchBtn.disabled = false;
        fetchBtn.innerHTML = '<span class="icon-[tabler--download] size-4"></span>';
    });
}

function applyOverride(code, price) {
    document.getElementById('price_override_code').value = code;
    document.getElementById('price_override_amount').value = price;
    document.getElementById('applied-override-code').textContent = code;
    document.getElementById('applied-override-price').textContent = ' - $' + parseFloat(price).toFixed(2);
    document.getElementById('applied-override').classList.remove('hidden');
    document.getElementById('override-input-section').classList.add('hidden');
    document.getElementById('override-pending').classList.add('hidden');
    document.getElementById('override_code_input').value = '';

    // Update the price paid field
    const pricePaidInput = document.getElementById('price_paid');
    if (pricePaidInput) {
        pricePaidInput.value = parseFloat(price).toFixed(2);
    }

    // Update summary total
    document.getElementById('summary-total').textContent = '$' + parseFloat(price).toFixed(2) + ' (Override)';
}

function removeOverride() {
    document.getElementById('price_override_code').value = '';
    document.getElementById('price_override_amount').value = '';
    document.getElementById('applied-override').classList.add('hidden');
    document.getElementById('override-input-section').classList.remove('hidden');

    // Clear input fields (handle both direct and code-based overrides)
    const codeInput = document.getElementById('override_code_input');
    if (codeInput) codeInput.value = '';
    const directInput = document.getElementById('direct-override-price');
    if (directInput) directInput.value = '';

    // Reset price using updateSummary which accounts for billing periods
    updateSummary();
}
</script>
@endpush
@endsection
