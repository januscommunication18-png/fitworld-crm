@php
    $classPass = $classPass ?? null;
@endphp

<div class="space-y-6">
        {{-- Basic Info --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Basic Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="name">Pass Name <span class="text-error">*</span></label>
                    <input type="text" id="name" name="name"
                        value="{{ old('name', $classPass?->name) }}"
                        class="input w-full @error('name') input-error @enderror"
                        placeholder="e.g., 10-Class Pack, Monthly Unlimited"
                        required>
                    @error('name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="textarea w-full @error('description') input-error @enderror"
                        placeholder="Describe what's included in this class pass...">{{ old('description', $classPass?->description) }}</textarea>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="class_count">Number of Credits <span class="text-error">*</span></label>
                        <input type="number" id="class_count" name="class_count"
                            value="{{ old('class_count', $classPass?->class_count ?? 10) }}"
                            class="input w-full @error('class_count') input-error @enderror"
                            min="1" max="999" required>
                        <p class="text-xs text-base-content/60 mt-1">How many class bookings this pass includes</p>
                        @error('class_count')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="default_credits_per_class">Credits per Class <span class="text-error">*</span></label>
                        <input type="number" id="default_credits_per_class" name="default_credits_per_class"
                            value="{{ old('default_credits_per_class', $classPass?->default_credits_per_class ?? 1) }}"
                            class="input w-full @error('default_credits_per_class') input-error @enderror"
                            min="1" max="10" required>
                        <p class="text-xs text-base-content/60 mt-1">Default credits deducted per booking</p>
                        @error('default_credits_per_class')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <x-studio-pricing-table
            title="Pricing"
            help="Leave empty for free passes. New member prices are shown on public booking (subdomain)."
            :rows="[
                ['section' => 'New Member Pricing', 'section_icon' => 'icon-[tabler--user-plus]', 'section_badge' => 'Public Booking', 'section_bg' => 'bg-info/5'],
                ['name' => 'new_member_prices', 'label' => 'Price', 'values' => $classPass?->new_member_prices ?? []],
                ['section' => 'Standard Pricing', 'section_icon' => 'icon-[tabler--users]', 'section_bg' => 'bg-base-200/50'],
                ['name' => 'prices', 'label' => 'Price', 'values' => $classPass?->prices ?? []],
            ]"
        />

        {{-- Fees & Cancellation --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Fees & Cancellation Policy</h3>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-studio-currency-inputs
                            name="registration_fees"
                            :values="$classPass?->registration_fees ?? []"
                            label="Registration Fee"
                            help="One-time fee when purchasing a billing period"
                        />

                        <x-studio-currency-inputs
                            name="cancellation_fees"
                            :values="$classPass?->cancellation_fees ?? []"
                            label="Cancellation Fee"
                            help="Fee charged for early cancellation"
                        />
                    </div>

                    <div>
                        <label class="label-text text-sm" for="cancellation_grace_hours">Grace Period</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="number" id="cancellation_grace_hours" name="cancellation_grace_hours" step="1" min="0" max="720"
                                   value="{{ old('cancellation_grace_hours', $classPass?->cancellation_grace_hours ?? 48) }}"
                                   class="input input-sm w-28" placeholder="48">
                            <span class="text-sm text-base-content/60">hours</span>
                        </div>
                        <p class="text-xs text-base-content/50 mt-1">Full refund window after purchase</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Validity & Activation --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Validity & Activation</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="validity_type">Validity Type <span class="text-error">*</span></label>
                        <x-studio-select name="validity_type" :options="$validityTypes" :selected="$classPass?->validity_type ?? 'days'" placeholder="Select validity..." :required="true" />
                        @error('validity_type')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="validity-value-section" class="{{ in_array(old('validity_type', $classPass?->validity_type ?? 'days'), ['days', 'months']) ? '' : 'hidden' }}">
                        <label class="label-text" for="validity_value">Validity Period</label>
                        <div class="flex items-center gap-2">
                            <input type="number" id="validity_value" name="validity_value"
                                value="{{ old('validity_value', $classPass?->validity_value ?? 30) }}"
                                class="input flex-1 @error('validity_value') input-error @enderror"
                                min="1" max="365">
                            <span id="validity-unit" class="text-base-content/60">days</span>
                        </div>
                        @error('validity_value')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Validity Presets --}}
                <div id="validity-presets" class="{{ in_array(old('validity_type', $classPass?->validity_type ?? 'days'), ['days', 'months']) ? '' : 'hidden' }}">
                    <label class="label-text mb-2 block">Quick Presets</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($validityPresets as $preset)
                            <button type="button" class="btn btn-sm btn-soft btn-secondary validity-preset"
                                data-type="{{ $preset['type'] }}" data-value="{{ $preset['value'] }}">
                                {{ $preset['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="label-text" for="activation_type">When Does Pass Start? <span class="text-error">*</span></label>
                    <x-studio-select name="activation_type" :options="$activationTypes" :selected="$classPass?->activation_type ?? 'on_purchase'" placeholder="Select..." :required="true" />
                    <p class="text-xs text-base-content/60 mt-1">Controls when the validity period begins</p>
                    @error('activation_type')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Auto-Renewal --}}
                <div class="border-t border-base-content/10 pt-4">
                    <label class="label-text mb-2 block">Auto-Renewal (Subscription)</label>
                    <div class="flex flex-wrap gap-2">
                        <label class="custom-option flex flex-row items-center gap-2 px-3 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors">
                            <input type="checkbox" id="is_recurring" name="is_recurring" value="1"
                                class="checkbox checkbox-primary checkbox-sm"
                                {{ old('is_recurring', $classPass?->is_recurring) ? 'checked' : '' }}>
                            <span class="label-text text-sm">Enable Auto-Renewal</span>
                        </label>

                        <label id="rollover-label" class="custom-option flex flex-row items-center gap-2 px-3 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors {{ old('is_recurring', $classPass?->is_recurring) ? '' : 'hidden' }}">
                            <input type="checkbox" id="rollover_enabled" name="rollover_enabled" value="1"
                                class="checkbox checkbox-info checkbox-sm"
                                {{ old('rollover_enabled', $classPass?->rollover_enabled) ? 'checked' : '' }}>
                            <span class="label-text text-sm">Enable Credit Rollover</span>
                        </label>
                    </div>

                    <div id="recurring-options" class="{{ old('is_recurring', $classPass?->is_recurring) ? '' : 'hidden' }} mt-3 space-y-3">
                        <div>
                            <label class="label-text" for="renewal_interval">Renewal Interval</label>
                            <x-studio-select name="renewal_interval" :options="$renewalIntervals" :selected="$classPass?->renewal_interval" placeholder="Select interval..." />
                            @error('renewal_interval')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="rollover-options" class="{{ old('rollover_enabled', $classPass?->rollover_enabled) ? '' : 'hidden' }}">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="label-text" for="max_rollover_credits">Max Rollover Credits</label>
                                    <input type="number" id="max_rollover_credits" name="max_rollover_credits"
                                        value="{{ old('max_rollover_credits', $classPass?->max_rollover_credits ?? 10) }}"
                                        class="input w-full @error('max_rollover_credits') input-error @enderror"
                                        min="0" max="100">
                                    @error('max_rollover_credits')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="label-text" for="max_rollover_periods">Max Rollover Periods</label>
                                    <input type="number" id="max_rollover_periods" name="max_rollover_periods"
                                        value="{{ old('max_rollover_periods', $classPass?->max_rollover_periods ?? 2) }}"
                                        class="input w-full @error('max_rollover_periods') input-error @enderror"
                                        min="0" max="12">
                                    @error('max_rollover_periods')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Class Eligibility --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Class Eligibility</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="eligibility_type">Which Classes Can Be Booked? <span class="text-error">*</span></label>
                    <x-studio-select name="eligibility_type" :options="$eligibilityTypes" :selected="$classPass?->eligibility_type ?? 'all'" placeholder="Select eligibility..." :required="true" />
                    @error('eligibility_type')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Specific Class Plans --}}
                <div id="class-plans-section" class="{{ old('eligibility_type', $classPass?->eligibility_type ?? 'all') === 'class_plans' ? '' : 'hidden' }}">
                    <label class="label-text mb-2 block">Select Class Plans</label>
                    @if($classPlans->isEmpty())
                        <p class="text-base-content/60 text-sm">No active class plans available.</p>
                    @else
                        @php $selectedClassPlanIds = old('eligible_class_plan_ids', $classPass?->eligible_class_plan_ids ?? []); @endphp
                        <div class="max-h-48 overflow-y-auto border border-base-300 rounded-lg p-3 space-y-2">
                            @foreach($classPlans as $plan)
                                <label class="flex items-center gap-3 cursor-pointer hover:bg-base-200 p-2 rounded">
                                    <input type="checkbox" name="eligible_class_plan_ids[]" value="{{ $plan->id }}"
                                        class="checkbox checkbox-primary checkbox-sm"
                                        {{ in_array($plan->id, $selectedClassPlanIds) ? 'checked' : '' }}>
                                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $plan->color }}"></span>
                                    <span class="font-medium">{{ $plan->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    @error('eligible_class_plan_ids')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Specific Service Plans --}}
                <div id="service-plans-section" class="{{ old('eligibility_type', $classPass?->eligibility_type ?? 'all') === 'service_plans' ? '' : 'hidden' }}">
                    <label class="label-text mb-2 block">Select Service Plans</label>
                    @if($servicePlans->isEmpty())
                        <p class="text-base-content/60 text-sm">No active service plans available.</p>
                    @else
                        @php $selectedServicePlanIds = old('eligible_service_plan_ids', $classPass?->eligible_service_plan_ids ?? []); @endphp
                        <div class="max-h-48 overflow-y-auto border border-base-300 rounded-lg p-3 space-y-2">
                            @foreach($servicePlans as $plan)
                                <label class="flex items-center gap-3 cursor-pointer hover:bg-base-200 p-2 rounded">
                                    <input type="checkbox" name="eligible_service_plan_ids[]" value="{{ $plan->id }}"
                                        class="checkbox checkbox-primary checkbox-sm"
                                        {{ in_array($plan->id, $selectedServicePlanIds) ? 'checked' : '' }}>
                                    <span class="icon-[tabler--massage] size-4 text-success"></span>
                                    <span class="font-medium">{{ $plan->name }}</span>
                                    <span class="text-sm text-base-content/50">{{ $plan->duration_minutes }} min · ${{ number_format($plan->price, 2) }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    @error('eligible_service_plan_ids')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Categories --}}
                <div id="categories-section" class="{{ old('eligibility_type', $classPass?->eligibility_type ?? 'all') === 'categories' ? '' : 'hidden' }}">
                    <label class="label-text mb-2 block">Select Categories</label>
                    @php $selectedCategories = old('eligible_categories', $classPass?->eligible_categories ?? []); @endphp
                    <div class="flex flex-wrap gap-2">
                        @foreach($classCategories as $value => $label)
                            <label class="custom-option flex flex-row items-center gap-2 px-3 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors">
                                <input type="checkbox" name="eligible_categories[]" value="{{ $value }}"
                                    class="checkbox checkbox-primary checkbox-sm"
                                    {{ in_array($value, $selectedCategories) ? 'checked' : '' }}>
                                <span class="label-text text-sm capitalize">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('eligible_categories')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Instructors --}}
                <div id="instructors-section" class="{{ old('eligibility_type', $classPass?->eligibility_type ?? 'all') === 'instructors' ? '' : 'hidden' }}">
                    <label class="label-text mb-2 block">Select Instructors</label>
                    @if($instructors->isEmpty())
                        <p class="text-base-content/60 text-sm">No active instructors available.</p>
                    @else
                        @php $selectedInstructorIds = old('eligible_instructor_ids', $classPass?->eligible_instructor_ids ?? []); @endphp
                        <div class="max-h-48 overflow-y-auto border border-base-300 rounded-lg p-3 space-y-2">
                            @foreach($instructors as $instructor)
                                <label class="flex items-center gap-3 cursor-pointer hover:bg-base-200 p-2 rounded">
                                    <input type="checkbox" name="eligible_instructor_ids[]" value="{{ $instructor->id }}"
                                        class="checkbox checkbox-primary checkbox-sm"
                                        {{ in_array($instructor->id, $selectedInstructorIds) ? 'checked' : '' }}>
                                    <span class="font-medium">{{ $instructor->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    @error('eligible_instructor_ids')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Locations --}}
                <div id="locations-section" class="{{ old('eligibility_type', $classPass?->eligibility_type ?? 'all') === 'locations' ? '' : 'hidden' }}">
                    <label class="label-text mb-2 block">Select Locations</label>
                    @if($locations->isEmpty())
                        <p class="text-base-content/60 text-sm">No locations available.</p>
                    @else
                        @php $selectedLocationIds = old('eligible_location_ids', $classPass?->eligible_location_ids ?? []); @endphp
                        <div class="max-h-48 overflow-y-auto border border-base-300 rounded-lg p-3 space-y-2">
                            @foreach($locations as $location)
                                <label class="flex items-center gap-3 cursor-pointer hover:bg-base-200 p-2 rounded">
                                    <input type="checkbox" name="eligible_location_ids[]" value="{{ $location->id }}"
                                        class="checkbox checkbox-primary checkbox-sm"
                                        {{ in_array($location->id, $selectedLocationIds) ? 'checked' : '' }}>
                                    <span class="font-medium">{{ $location->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    @error('eligible_location_ids')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Excluded Class Types --}}
                <div>
                    <label class="label-text mb-2 block">Exclude Class Types (Optional)</label>
                    <p class="text-sm text-base-content/60 mb-2">Classes of these types cannot be booked with this pass</p>
                    @php $excludedTypes = old('excluded_class_types', $classPass?->excluded_class_types ?? []); @endphp
                    <div class="flex flex-wrap gap-2">
                        @foreach($classTypes as $value => $label)
                            <label class="custom-option flex flex-row items-center gap-2 px-3 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors">
                                <input type="checkbox" name="excluded_class_types[]" value="{{ $value }}"
                                    class="checkbox checkbox-error checkbox-sm"
                                    {{ in_array($value, $excludedTypes) ? 'checked' : '' }}>
                                <span class="label-text text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('excluded_class_types')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Peak Time Settings --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Peak Time Credit Multiplier</h3>
                <p class="text-sm text-base-content/60">Charge extra credits during peak hours (optional)</p>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text" for="peak_time_multiplier">Multiplier</label>
                        <input type="number" id="peak_time_multiplier" name="peak_time_multiplier"
                            value="{{ old('peak_time_multiplier', $classPass?->peak_time_multiplier) }}"
                            class="input w-full @error('peak_time_multiplier') input-error @enderror"
                            step="0.1" min="1" max="5" placeholder="e.g., 1.5">
                        <p class="text-xs text-base-content/60 mt-1">Leave empty to disable</p>
                        @error('peak_time_multiplier')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-time-picker name="peak_time_start" :value="$classPass?->peak_time_start" label="Peak Start Time" placeholder="Select start time..." />
                    <x-time-picker name="peak_time_end" :value="$classPass?->peak_time_end" label="Peak End Time" placeholder="Select end time..." />
                </div>

                <div>
                    <label class="label-text mb-2 block">Peak Days</label>
                    @php $peakDays = old('peak_time_days', $classPass?->peak_time_days ?? []); @endphp
                    <div class="flex flex-wrap gap-2">
                        @foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $index => $day)
                            <label class="custom-option flex flex-row items-center gap-2 px-3 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors">
                                <input type="checkbox" name="peak_time_days[]" value="{{ $index }}"
                                    class="checkbox checkbox-warning checkbox-sm"
                                    {{ in_array($index, $peakDays) ? 'checked' : '' }}>
                                <span class="label-text text-sm">{{ $day }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Advanced Options --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Advanced Options</h3>
            </div>
            <div class="card-body space-y-6">
                {{-- Freeze & Extension --}}
                <div>
                    <h4 class="font-medium mb-3">Freeze & Extension</h4>
                    <div class="flex flex-wrap gap-2">
                        <label class="custom-option flex flex-row items-center gap-2 px-3 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors">
                            <input type="checkbox" name="allow_admin_extension" value="1"
                                class="checkbox checkbox-primary checkbox-sm"
                                {{ old('allow_admin_extension', $classPass?->allow_admin_extension ?? true) ? 'checked' : '' }}>
                            <span class="label-text text-sm">Allow Admin Extension</span>
                        </label>

                        <label class="custom-option flex flex-row items-center gap-2 px-3 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors">
                            <input type="checkbox" id="allow_freeze" name="allow_freeze" value="1"
                                class="checkbox checkbox-primary checkbox-sm"
                                {{ old('allow_freeze', $classPass?->allow_freeze) ? 'checked' : '' }}>
                            <span class="label-text text-sm">Allow Freeze</span>
                        </label>
                    </div>

                    <div id="freeze-options" class="{{ old('allow_freeze', $classPass?->allow_freeze) ? '' : 'hidden' }} mt-3">
                        <div>
                            <label class="label-text" for="max_freeze_days">Max Freeze Days</label>
                            <input type="number" id="max_freeze_days" name="max_freeze_days"
                                value="{{ old('max_freeze_days', $classPass?->max_freeze_days ?? 30) }}"
                                class="input w-full max-w-xs @error('max_freeze_days') input-error @enderror"
                                min="1" max="365">
                            @error('max_freeze_days')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sharing & Transfer --}}
                <div class="border-t border-base-content/10 pt-6">
                    <h4 class="font-medium mb-3">Sharing & Transfer</h4>
                    <div class="flex flex-wrap gap-2">
                        <label class="custom-option flex flex-row items-center gap-2 px-3 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors">
                            <input type="checkbox" name="allow_transfer" value="1"
                                class="checkbox checkbox-primary checkbox-sm"
                                {{ old('allow_transfer', $classPass?->allow_transfer) ? 'checked' : '' }}>
                            <span class="label-text text-sm">Allow Transfer</span>
                        </label>

                        <label class="custom-option flex flex-row items-center gap-2 px-3 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors">
                            <input type="checkbox" id="allow_family_sharing" name="allow_family_sharing" value="1"
                                class="checkbox checkbox-primary checkbox-sm"
                                {{ old('allow_family_sharing', $classPass?->allow_family_sharing) ? 'checked' : '' }}>
                            <span class="label-text text-sm">Allow Family Sharing</span>
                        </label>

                        <label class="custom-option flex flex-row items-center gap-2 px-3 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors">
                            <input type="checkbox" name="allow_gifting" value="1"
                                class="checkbox checkbox-primary checkbox-sm"
                                {{ old('allow_gifting', $classPass?->allow_gifting) ? 'checked' : '' }}>
                            <span class="label-text text-sm">Allow Gifting</span>
                        </label>
                    </div>

                    <div id="family-options" class="{{ old('allow_family_sharing', $classPass?->allow_family_sharing) ? '' : 'hidden' }} mt-3">
                        <label class="label-text" for="max_family_members">Max Family Members</label>
                        <input type="number" id="max_family_members" name="max_family_members"
                            value="{{ old('max_family_members', $classPass?->max_family_members ?? 4) }}"
                            class="input w-full max-w-xs @error('max_family_members') input-error @enderror"
                            min="1" max="10">
                        @error('max_family_members')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

            </div>
        </div>

        {{-- File Attachments --}}
        <x-studio-file-upload
            name="file_attachments"
            :files="$classPass?->file_attachments ?? []"
            title="File Attachments"
            help="Upload PDFs, documents, or images to attach to this class pass."
        />

        {{-- Image --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Image</h3>
            </div>
            <div class="card-body">
                <input type="file" id="image" name="image" class="hidden" accept="image/jpeg,image/png,image/jpg,image/webp">

                {{-- Preview (shown when image exists) --}}
                <div id="image-preview-wrapper" class="{{ $classPass?->image_path ? '' : 'hidden' }}">
                    <div class="relative group rounded-xl overflow-hidden">
                        <img id="image-preview" src="{{ $classPass?->image_url ?? '' }}" alt="Pass image" class="w-full h-44 object-cover">
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                            <button type="button" class="btn btn-sm btn-ghost text-white" onclick="document.getElementById('image').click()">
                                <span class="icon-[tabler--edit] size-4"></span> Change
                            </button>
                            <button type="button" class="btn btn-sm btn-ghost text-white" onclick="removeImage()">
                                <span class="icon-[tabler--trash] size-4"></span> Remove
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Upload zone (shown when no image) --}}
                <div id="image-upload-zone" class="border-2 border-dashed border-base-content/20 rounded-xl p-6 text-center cursor-pointer hover:border-primary/50 hover:bg-primary/5 transition-colors {{ $classPass?->image_path ? 'hidden' : '' }}"
                     onclick="document.getElementById('image').click()"
                     ondragover="event.preventDefault(); this.classList.add('border-primary', 'bg-primary/5')"
                     ondragleave="this.classList.remove('border-primary', 'bg-primary/5')"
                     ondrop="event.preventDefault(); this.classList.remove('border-primary', 'bg-primary/5'); handleImageDrop(event)">
                    <span class="icon-[tabler--photo-up] size-10 text-base-content/30 mx-auto block mb-3"></span>
                    <p class="text-sm font-medium text-base-content/70">Click to upload or drag & drop</p>
                    <p class="text-xs text-base-content/50 mt-1">JPG, PNG or WebP. Max 2MB.</p>
                </div>

                @error('image')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Status --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Status</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="status">Status <span class="text-error">*</span></label>
                    <x-studio-select name="status" :options="$statuses" :selected="$classPass?->status ?? 'draft'" placeholder="Select status..." :required="true" />
                        <p class="text-xs text-base-content/60 mt-1">Only active passes can be purchased</p>
                        @error('status')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="color">Calendar Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" id="color" name="color"
                                value="{{ old('color', $classPass?->color ?? '#6366f1') }}"
                                class="w-12 h-10 rounded cursor-pointer">
                            <input type="text" id="color_text"
                                value="{{ old('color', $classPass?->color ?? '#6366f1') }}"
                                class="input flex-1"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                placeholder="#6366f1">
                        </div>
                        @error('color')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-medium">Visible on Booking Page</span>
                        <p class="text-xs text-base-content/60">Show this pass to customers on the public booking page</p>
                    </div>
                    <label class="switch switch-primary">
                        <input type="checkbox" name="visibility_public" value="1"
                            {{ old('visibility_public', $classPass?->visibility_public ?? true) ? 'checked' : '' }} />
                        <span class="switch-indicator"></span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card bg-base-100">
            <div class="card-body space-y-2">
                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--check] size-5"></span>
                    {{ $classPass ? 'Update Class Pass' : 'Create Class Pass' }}
                </button>
                <a href="{{ route('catalog.index', ['tab' => 'class-passes']) }}" class="btn btn-ghost w-full">
                    Cancel
                </a>
            </div>
        </div>
</div>

@push('scripts')
<script>
    // Image upload preview
    var imageInput = document.getElementById('image');
    var imagePreview = document.getElementById('image-preview');
    var imagePreviewWrapper = document.getElementById('image-preview-wrapper');
    var imageUploadZone = document.getElementById('image-upload-zone');

    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            previewFile(this.files[0]);
        }
    });

    function previewFile(file) {
        if (!file.type.startsWith('image/')) return;
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be under 2MB.');
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreviewWrapper.classList.remove('hidden');
            imageUploadZone.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    function handleImageDrop(e) {
        var files = e.dataTransfer.files;
        if (files.length > 0) {
            imageInput.files = files;
            previewFile(files[0]);
        }
    }

    function removeImage() {
        imageInput.value = '';
        imagePreview.src = '';
        imagePreviewWrapper.classList.add('hidden');
        imageUploadZone.classList.remove('hidden');
    }

document.addEventListener('DOMContentLoaded', function() {
    // Filter renewal intervals based on validity period
    function filterRenewalIntervals() {
        var validityType = document.getElementById('validity_type').value;
        var validityValue = parseInt(document.getElementById('validity_value').value) || 0;
        var renewalSelect = document.getElementById('renewal_interval');
        var options = renewalSelect.querySelectorAll('option[data-days]');
        var totalDays = 0;

        if (validityType === 'days') {
            totalDays = validityValue;
        } else if (validityType === 'months') {
            totalDays = validityValue * 30;
        } else {
            totalDays = 9999; // no_expiration — show all
        }

        options.forEach(function(opt) {
            var requiredDays = parseInt(opt.dataset.days);
            var allowed = totalDays >= requiredDays;
            opt.disabled = !allowed;
            opt.hidden = !allowed;
            if (opt.selected && !allowed) {
                renewalSelect.value = '';
            }
        });
    }

    // Sync color picker with text input
    document.getElementById('color').addEventListener('input', function() {
        document.getElementById('color_text').value = this.value;
    });
    document.getElementById('color_text').addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            document.getElementById('color').value = this.value;
        }
    });

    // Validity type changes
    document.getElementById('validity_type').addEventListener('change', function() {
        var valueSection = document.getElementById('validity-value-section');
        var presetsSection = document.getElementById('validity-presets');
        var unitSpan = document.getElementById('validity-unit');

        if (this.value === 'days' || this.value === 'months') {
            valueSection.classList.remove('hidden');
            presetsSection.classList.remove('hidden');
            unitSpan.textContent = this.value;
        } else {
            valueSection.classList.add('hidden');
            presetsSection.classList.add('hidden');
        }
        filterRenewalIntervals();
    });

    // Validity presets
    document.querySelectorAll('.validity-preset').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('validity_type').value = this.dataset.type;
            document.getElementById('validity_value').value = this.dataset.value;
            document.getElementById('validity-unit').textContent = this.dataset.type;
            document.getElementById('validity-value-section').classList.remove('hidden');
            filterRenewalIntervals();
        });
    });

    // Eligibility type changes
    document.getElementById('eligibility_type').addEventListener('change', function() {
        document.getElementById('class-plans-section').classList.add('hidden');
        document.getElementById('service-plans-section').classList.add('hidden');
        document.getElementById('categories-section').classList.add('hidden');
        document.getElementById('instructors-section').classList.add('hidden');
        document.getElementById('locations-section').classList.add('hidden');

        if (this.value === 'class_plans') {
            document.getElementById('class-plans-section').classList.remove('hidden');
        } else if (this.value === 'service_plans') {
            document.getElementById('service-plans-section').classList.remove('hidden');
        } else if (this.value === 'categories') {
            document.getElementById('categories-section').classList.remove('hidden');
        } else if (this.value === 'instructors') {
            document.getElementById('instructors-section').classList.remove('hidden');
        } else if (this.value === 'locations') {
            document.getElementById('locations-section').classList.remove('hidden');
        }
    });

    // Freeze toggle
    document.getElementById('allow_freeze').addEventListener('change', function() {
        document.getElementById('freeze-options').classList.toggle('hidden', !this.checked);
    });

    // Family sharing toggle
    document.getElementById('allow_family_sharing').addEventListener('change', function() {
        document.getElementById('family-options').classList.toggle('hidden', !this.checked);
    });

    // Recurring toggle
    document.getElementById('is_recurring').addEventListener('change', function() {
        document.getElementById('recurring-options').classList.toggle('hidden', !this.checked);
        document.getElementById('rollover-label').classList.toggle('hidden', !this.checked);
    });

    // Rollover toggle
    document.getElementById('rollover_enabled').addEventListener('change', function() {
        document.getElementById('rollover-options').classList.toggle('hidden', !this.checked);
    });

    document.getElementById('validity_value').addEventListener('input', filterRenewalIntervals);
    filterRenewalIntervals();
});
</script>
@endpush
