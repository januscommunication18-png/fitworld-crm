@php
    $classPass = $classPass ?? null;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Form --}}
    <div class="lg:col-span-2 space-y-6">
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
                        <label class="label-text" for="default_credits_per_class">Credits per Class</label>
                        <input type="number" id="default_credits_per_class" name="default_credits_per_class"
                            value="{{ old('default_credits_per_class', $classPass?->default_credits_per_class ?? 1) }}"
                            class="input w-full @error('default_credits_per_class') input-error @enderror"
                            min="1" max="10">
                        <p class="text-xs text-base-content/60 mt-1">Default credits deducted per booking</p>
                        @error('default_credits_per_class')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Pricing</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text mb-2 block">Price by Currency</label>
                    <p class="text-sm text-base-content/60 mb-3">New member prices are shown on public booking. Default currency price is required.</p>

                    @php
                        $existingPrices = $classPass?->prices ?? [];
                        $existingNewMemberPrices = $classPass?->new_member_prices ?? [];
                        $legacyPrice = $classPass?->price;
                    @endphp

                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th class="w-48">Price Type</th>
                                    @foreach($hostCurrencies as $currency)
                                        <th class="text-center">
                                            {{ $currency }}
                                            @if($currency === $defaultCurrency)
                                                <span class="badge badge-primary badge-xs ms-1">Default</span>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                {{-- New Member Pricing --}}
                                <tr class="bg-info/5">
                                    <td colspan="{{ count($hostCurrencies) + 1 }}" class="font-semibold">
                                        <span class="icon-[tabler--user-plus] size-4 me-1 align-middle"></span>
                                        New Member Pricing
                                        <span class="badge badge-soft badge-info badge-sm ms-2">Public Booking</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label class="label-text">Price</label></td>
                                    @foreach($hostCurrencies as $currency)
                                        <td>
                                            <label class="input input-bordered input-sm flex items-center gap-1">
                                                <span class="text-base-content/60 text-sm">{{ $currencySymbols[$currency] ?? $currency }}</span>
                                                <input type="number" name="new_member_prices[{{ $currency }}]" step="0.01" min="0"
                                                       value="{{ old('new_member_prices.' . $currency, $existingNewMemberPrices[$currency] ?? '') }}"
                                                       class="grow w-full min-w-20" placeholder="0.00">
                                            </label>
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- Existing Member Pricing --}}
                                <tr class="bg-base-200/50">
                                    <td colspan="{{ count($hostCurrencies) + 1 }}" class="font-semibold">
                                        <span class="icon-[tabler--users] size-4 me-1 align-middle"></span>
                                        Standard Pricing
                                    </td>
                                </tr>
                                <tr>
                                    <td><label class="label-text">Price <span class="text-error">*</span></label></td>
                                    @foreach($hostCurrencies as $currency)
                                        @php
                                            $isDefault = $currency === $defaultCurrency;
                                            $currentPrice = old("prices.{$currency}",
                                                $existingPrices[$currency] ??
                                                ($isDefault && $legacyPrice !== null ? $legacyPrice : '')
                                            );
                                        @endphp
                                        <td>
                                            <label class="input input-bordered input-sm flex items-center gap-1 @error("prices.{$currency}") input-error @enderror">
                                                <span class="text-base-content/60 text-sm">{{ $currencySymbols[$currency] ?? $currency }}</span>
                                                <input type="number" name="prices[{{ $currency }}]" step="0.01" min="0"
                                                       value="{{ $currentPrice }}"
                                                       class="grow w-full min-w-20" placeholder="0.00"
                                                       {{ $isDefault ? 'required' : '' }}>
                                            </label>
                                            @error("prices.{$currency}")
                                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
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
                        <select id="validity_type" name="validity_type" class="select w-full @error('validity_type') input-error @enderror" required>
                            @foreach($validityTypes as $value => $label)
                                <option value="{{ $value }}" {{ old('validity_type', $classPass?->validity_type ?? 'days') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="activation_type">When Does Pass Start? <span class="text-error">*</span></label>
                        <select id="activation_type" name="activation_type" class="select w-full @error('activation_type') input-error @enderror" required>
                            @foreach($activationTypes as $value => $label)
                                <option value="{{ $value }}" {{ old('activation_type', $classPass?->activation_type ?? 'on_purchase') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-base-content/60 mt-1">Controls when the validity period begins</p>
                        @error('activation_type')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="grace_period_days">Grace Period (Days)</label>
                        <input type="number" id="grace_period_days" name="grace_period_days"
                            value="{{ old('grace_period_days', $classPass?->grace_period_days ?? 0) }}"
                            class="input w-full @error('grace_period_days') input-error @enderror"
                            min="0" max="30">
                        <p class="text-xs text-base-content/60 mt-1">Extra days after expiry for booking</p>
                        @error('grace_period_days')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
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
                    <select id="eligibility_type" name="eligibility_type" class="select w-full @error('eligibility_type') input-error @enderror" required>
                        @foreach($eligibilityTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('eligibility_type', $classPass?->eligibility_type ?? 'all') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
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
                    <div>
                        <label class="label-text" for="peak_time_start">Peak Start Time</label>
                        <input type="time" id="peak_time_start" name="peak_time_start"
                            value="{{ old('peak_time_start', $classPass?->peak_time_start) }}"
                            class="input w-full @error('peak_time_start') input-error @enderror">
                        @error('peak_time_start')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="peak_time_end">Peak End Time</label>
                        <input type="time" id="peak_time_end" name="peak_time_end"
                            value="{{ old('peak_time_end', $classPass?->peak_time_end) }}"
                            class="input w-full @error('peak_time_end') input-error @enderror">
                        @error('peak_time_end')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
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
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="allow_admin_extension" value="1"
                                class="toggle toggle-primary"
                                {{ old('allow_admin_extension', $classPass?->allow_admin_extension ?? true) ? 'checked' : '' }}>
                            <div>
                                <span class="font-medium">Allow Admin Extension</span>
                                <p class="text-xs text-base-content/60">Admins can extend the expiry date</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="allow_freeze" name="allow_freeze" value="1"
                                class="toggle toggle-primary"
                                {{ old('allow_freeze', $classPass?->allow_freeze) ? 'checked' : '' }}>
                            <div>
                                <span class="font-medium">Allow Freeze</span>
                                <p class="text-xs text-base-content/60">Members can pause their pass</p>
                            </div>
                        </label>

                        <div id="freeze-options" class="{{ old('allow_freeze', $classPass?->allow_freeze) ? '' : 'hidden' }} pl-10 space-y-3">
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
                </div>

                {{-- Sharing & Transfer --}}
                <div class="border-t border-base-content/10 pt-6">
                    <h4 class="font-medium mb-3">Sharing & Transfer</h4>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="allow_transfer" value="1"
                                class="toggle toggle-primary"
                                {{ old('allow_transfer', $classPass?->allow_transfer) ? 'checked' : '' }}>
                            <div>
                                <span class="font-medium">Allow Transfer</span>
                                <p class="text-xs text-base-content/60">Pass can be transferred to another member</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="allow_family_sharing" name="allow_family_sharing" value="1"
                                class="toggle toggle-primary"
                                {{ old('allow_family_sharing', $classPass?->allow_family_sharing) ? 'checked' : '' }}>
                            <div>
                                <span class="font-medium">Allow Family Sharing</span>
                                <p class="text-xs text-base-content/60">Credits can be shared with family members</p>
                            </div>
                        </label>

                        <div id="family-options" class="{{ old('allow_family_sharing', $classPass?->allow_family_sharing) ? '' : 'hidden' }} pl-10">
                            <label class="label-text" for="max_family_members">Max Family Members</label>
                            <input type="number" id="max_family_members" name="max_family_members"
                                value="{{ old('max_family_members', $classPass?->max_family_members ?? 4) }}"
                                class="input w-full max-w-xs @error('max_family_members') input-error @enderror"
                                min="1" max="10">
                            @error('max_family_members')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="allow_gifting" value="1"
                                class="toggle toggle-primary"
                                {{ old('allow_gifting', $classPass?->allow_gifting) ? 'checked' : '' }}>
                            <div>
                                <span class="font-medium">Allow Gifting</span>
                                <p class="text-xs text-base-content/60">Pass can be purchased as a gift</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Auto-Renewal --}}
                <div class="border-t border-base-content/10 pt-6">
                    <h4 class="font-medium mb-3">Auto-Renewal (Subscription)</h4>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="is_recurring" name="is_recurring" value="1"
                                class="toggle toggle-primary"
                                {{ old('is_recurring', $classPass?->is_recurring) ? 'checked' : '' }}>
                            <div>
                                <span class="font-medium">Enable Auto-Renewal</span>
                                <p class="text-xs text-base-content/60">Pass automatically renews and charges the member</p>
                            </div>
                        </label>

                        <div id="recurring-options" class="{{ old('is_recurring', $classPass?->is_recurring) ? '' : 'hidden' }} pl-10 space-y-3">
                            <div>
                                <label class="label-text" for="renewal_interval">Renewal Interval</label>
                                <select id="renewal_interval" name="renewal_interval" class="select w-full max-w-xs @error('renewal_interval') input-error @enderror">
                                    <option value="">Select interval...</option>
                                    @foreach($renewalIntervals as $value => $label)
                                        <option value="{{ $value }}" {{ old('renewal_interval', $classPass?->renewal_interval) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('renewal_interval')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" id="rollover_enabled" name="rollover_enabled" value="1"
                                    class="toggle toggle-info"
                                    {{ old('rollover_enabled', $classPass?->rollover_enabled) ? 'checked' : '' }}>
                                <div>
                                    <span class="font-medium">Enable Credit Rollover</span>
                                    <p class="text-xs text-base-content/60">Unused credits roll over to next period</p>
                                </div>
                            </label>

                            <div id="rollover-options" class="{{ old('rollover_enabled', $classPass?->rollover_enabled) ? '' : 'hidden' }} space-y-3">
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
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Appearance --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Appearance</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="color">Display Color</label>
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

                <div>
                    <label class="label-text" for="image">Cover Image</label>
                    @if($classPass?->image_path)
                        <div class="mb-2 relative inline-block">
                            <img src="{{ $classPass->image_url }}" alt="{{ $classPass->name }}" class="w-32 h-20 object-cover rounded-lg">
                            <label class="absolute -top-2 -right-2 btn btn-xs btn-circle btn-error cursor-pointer">
                                <input type="checkbox" name="remove_image" value="1" class="hidden" onchange="this.closest('.relative').style.display='none'">
                                <span class="icon-[tabler--x] size-3"></span>
                            </label>
                        </div>
                    @endif
                    <input type="file" id="image" name="image"
                        class="file-input w-full @error('image') file-input-error @enderror"
                        accept="image/jpeg,image/png,image/jpg,image/webp">
                    <p class="text-xs text-base-content/60 mt-1">Max 2MB, JPEG/PNG/WebP</p>
                    @error('image')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Publishing --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Publishing</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="status">Status <span class="text-error">*</span></label>
                    <select id="status" name="status" class="select w-full @error('status') input-error @enderror" required>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $classPass?->status ?? 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-base-content/60 mt-1">Only active passes can be purchased</p>
                    @error('status')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="visibility_public" value="1"
                        class="toggle toggle-primary"
                        {{ old('visibility_public', $classPass?->visibility_public ?? true) ? 'checked' : '' }}>
                    <div>
                        <span class="font-medium">Visible on Booking Page</span>
                        <p class="text-xs text-base-content/60">Show this pass to customers</p>
                    </div>
                </label>
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
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
        const valueSection = document.getElementById('validity-value-section');
        const presetsSection = document.getElementById('validity-presets');
        const unitSpan = document.getElementById('validity-unit');

        if (this.value === 'days' || this.value === 'months') {
            valueSection.classList.remove('hidden');
            presetsSection.classList.remove('hidden');
            unitSpan.textContent = this.value;
        } else {
            valueSection.classList.add('hidden');
            presetsSection.classList.add('hidden');
        }
    });

    // Validity presets
    document.querySelectorAll('.validity-preset').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('validity_type').value = this.dataset.type;
            document.getElementById('validity_value').value = this.dataset.value;
            document.getElementById('validity-unit').textContent = this.dataset.type;
            document.getElementById('validity-value-section').classList.remove('hidden');
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
    });

    // Rollover toggle
    document.getElementById('rollover_enabled').addEventListener('change', function() {
        document.getElementById('rollover-options').classList.toggle('hidden', !this.checked);
    });
});
</script>
@endpush
