@php
    $membershipPlan = $membershipPlan ?? null;
    $selectedClassPlanIds = $selectedClassPlanIds ?? [];
    $selectedLocationIds = $selectedLocationIds ?? [];
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
                    <label class="label-text" for="name">Plan Name</label>
                    <input type="text" id="name" name="name"
                        value="{{ old('name', $membershipPlan?->name) }}"
                        class="input w-full @error('name') input-error @enderror"
                        placeholder="e.g., Unlimited Monthly, 8 Classes/Month"
                        required>
                    @error('name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="textarea w-full @error('description') input-error @enderror"
                        placeholder="Describe the benefits and what's included in this membership...">{{ old('description', $membershipPlan?->description) }}</textarea>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Plan Type & Pricing --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Plan Type & Pricing</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="type">Plan Type</label>
                        <select id="type" name="type" class="select w-full @error('type') input-error @enderror" required>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type', $membershipPlan?->type ?? 'unlimited') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-base-content/60 mt-1">Unlimited = full access, Credits = limited bookings</p>
                        @error('type')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="interval">Billing Interval</label>
                        <select id="interval" name="interval" class="select w-full @error('interval') input-error @enderror" required>
                            @foreach($intervals as $value => $label)
                                <option value="{{ $value }}" {{ old('interval', $membershipPlan?->interval ?? 'monthly') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('interval')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Scheduled Class Toggle --}}
                <div>
                    <label class="label-text mb-2 block" for="has_scheduled_class">Is there scheduled class for membership?</label>
                    <div class="flex gap-4">
                        <label class="custom-option flex items-center gap-2 px-4 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="has_scheduled_class" value="1"
                                class="radio radio-primary radio-sm"
                                {{ old('has_scheduled_class', $membershipPlan?->has_scheduled_class ?? false) ? 'checked' : '' }}
                                onchange="toggleScheduledClassSection()">
                            <span class="label-text font-medium">Yes</span>
                        </label>
                        <label class="custom-option flex items-center gap-2 px-4 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="has_scheduled_class" value="0"
                                class="radio radio-primary radio-sm"
                                {{ !old('has_scheduled_class', $membershipPlan?->has_scheduled_class ?? false) ? 'checked' : '' }}
                                onchange="toggleScheduledClassSection()">
                            <span class="label-text font-medium">No</span>
                        </label>
                    </div>
                    <p class="text-xs text-base-content/60 mt-2">
                        <strong>Yes:</strong> Members attend classes at specific scheduled times (e.g., yoga, fitness classes)<br>
                        <strong>No:</strong> Members can visit anytime for self-workout (e.g., gym access)
                    </p>
                    @error('has_scheduled_class')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Scheduled Classes Selection (shown when has_scheduled_class = YES) --}}
                <div id="scheduled-classes-section" class="{{ old('has_scheduled_class', $membershipPlan?->has_scheduled_class ?? false) ? '' : 'hidden' }}">
                    <div class="alert alert-soft alert-info mb-3">
                        <span class="icon-[tabler--calendar-event] size-5"></span>
                        <div>
                            <p class="font-medium">Scheduled Class Membership</p>
                            <p class="text-sm">Members will be automatically enrolled into upcoming sessions of the selected classes.</p>
                        </div>
                    </div>

                    <label class="label-text mb-2 block">Select Classes for Auto-Enrollment</label>
                    @if($classPlans->isEmpty())
                        <p class="text-base-content/60 text-sm">No active class plans available. <a href="{{ route('class-plans.create') }}" class="link link-primary">Create one first</a>.</p>
                    @else
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-base-content/60">{{ $classPlans->count() }} classes available</span>
                            <label class="flex items-center gap-2 cursor-pointer text-sm">
                                <input type="checkbox" class="checkbox checkbox-xs checkbox-primary" onchange="toggleAllScheduledClasses(this)">
                                <span>Select All</span>
                            </label>
                        </div>
                        <div class="max-h-64 overflow-y-auto border border-base-300 rounded-lg p-3 space-y-2 bg-base-200/30">
                            @foreach($classPlans as $classPlan)
                                <label class="flex items-center gap-3 cursor-pointer hover:bg-base-200 p-2 rounded transition-colors">
                                    <input type="checkbox" name="scheduled_class_plan_ids[]" value="{{ $classPlan->id }}"
                                        class="checkbox checkbox-primary checkbox-sm scheduled-class-checkbox"
                                        {{ in_array($classPlan->id, old('scheduled_class_plan_ids', $selectedClassPlanIds)) ? 'checked' : '' }}>
                                    <div class="flex items-center gap-2 flex-1">
                                        <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $classPlan->color }}"></span>
                                        <span class="font-medium">{{ $classPlan->name }}</span>
                                        <span class="badge badge-ghost badge-xs">{{ ucfirst($classPlan->category) }}</span>
                                    </div>
                                    @if($classPlan->default_duration)
                                        <span class="text-xs text-base-content/60">{{ $classPlan->default_duration }} min</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-base-content/60 mt-2">
                            <span class="icon-[tabler--info-circle] size-3 inline-block"></span>
                            When a member subscribes, they will be auto-booked into all upcoming sessions of selected classes.
                        </p>
                    @endif
                    @error('scheduled_class_plan_ids')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Multi-Currency Pricing --}}
                <div>
                    <label class="label-text mb-2 block">Pricing by Currency</label>
                    <p class="text-sm text-base-content/60 mb-3">Set prices for each currency your studio accepts. Default currency price is required.</p>

                    @php
                        $existingPrices = $membershipPlan?->prices ?? [];
                        $legacyPrice = $membershipPlan?->price;
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($hostCurrencies as $currency)
                            @php
                                $isDefault = $currency === $defaultCurrency;
                                $symbol = $currencySymbols[$currency] ?? $currency;
                                // Get price from existing prices, or fall back to legacy price for default currency
                                $currentPrice = old("prices.{$currency}",
                                    $existingPrices[$currency] ??
                                    ($isDefault && $legacyPrice !== null ? $legacyPrice : '')
                                );
                            @endphp
                            <div class="relative">
                                <label class="label-text text-xs flex items-center gap-1" for="prices_{{ $currency }}">
                                    <span class="font-bold text-primary">{{ $symbol }}</span>
                                    {{ $currency }}
                                    @if($isDefault)
                                        <span class="badge badge-primary badge-xs">Default</span>
                                    @endif
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/60 font-medium">{{ $symbol }}</span>
                                    <input type="number"
                                        id="prices_{{ $currency }}"
                                        name="prices[{{ $currency }}]"
                                        value="{{ $currentPrice }}"
                                        class="input w-full pl-10 @error("prices.{$currency}") input-error @enderror"
                                        min="0" max="99999.99" step="0.01"
                                        placeholder="0.00"
                                        {{ $isDefault ? 'required' : '' }}>
                                </div>
                                @error("prices.{$currency}")
                                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    @if(count($hostCurrencies) > 1)
                        <div class="alert alert-soft alert-info mt-3">
                            <span class="icon-[tabler--info-circle] size-4"></span>
                            <span class="text-sm">Leave a currency blank if you don't want to offer this plan in that currency.</span>
                        </div>
                    @endif
                </div>

                {{-- Credits per cycle (shown when type is credits) --}}
                <div id="credits-section" class="{{ old('type', $membershipPlan?->type ?? 'unlimited') !== 'credits' ? 'hidden' : '' }}">
                    <label class="label-text" for="credits_per_cycle">Credits per Billing Cycle</label>
                    <input type="number" id="credits_per_cycle" name="credits_per_cycle"
                        value="{{ old('credits_per_cycle', $membershipPlan?->credits_per_cycle ?? 8) }}"
                        class="input w-full max-w-xs @error('credits_per_cycle') input-error @enderror"
                        min="1" max="999">
                    <p class="text-xs text-base-content/60 mt-1">Number of class bookings included per cycle</p>
                    @error('credits_per_cycle')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Addon Members --}}
                <div>
                    <label class="label-text" for="addon_members">Addon Members</label>
                    <select id="addon_members" name="addon_members" class="select w-full max-w-xs @error('addon_members') input-error @enderror">
                        <option value="0" {{ old('addon_members', $membershipPlan?->addon_members ?? 0) == 0 ? 'selected' : '' }}>Individual (no guests)</option>
                        <option value="1" {{ old('addon_members', $membershipPlan?->addon_members ?? 0) == 1 ? 'selected' : '' }}>+1 Guest</option>
                        <option value="2" {{ old('addon_members', $membershipPlan?->addon_members ?? 0) == 2 ? 'selected' : '' }}>+2 Guests</option>
                        <option value="3" {{ old('addon_members', $membershipPlan?->addon_members ?? 0) == 3 ? 'selected' : '' }}>+3 Guests</option>
                        <option value="4" {{ old('addon_members', $membershipPlan?->addon_members ?? 0) == 4 ? 'selected' : '' }}>+4 Guests</option>
                    </select>
                    <p class="text-xs text-base-content/60 mt-1">How many additional people can the member bring to classes</p>
                    @error('addon_members')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Free Items / Amenities --}}
        @php
            $hostAmenities = $host->amenities ?? [];
            // Default to all amenities selected when creating new plan
            $defaultAmenities = $membershipPlan ? ($membershipPlan->free_amenities ?? []) : $hostAmenities;
            $selectedAmenities = old('free_amenities', $defaultAmenities);
        @endphp
        @if(count($hostAmenities) > 0)
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Free Items / Amenities</h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-base-content/60 mb-3">Select which amenities are included free with this membership</p>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach($hostAmenities as $amenity)
                        <label class="custom-option flex flex-row items-center gap-2 px-3 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors">
                            <input type="checkbox"
                                   name="free_amenities[]"
                                   value="{{ $amenity }}"
                                   class="checkbox checkbox-primary checkbox-sm"
                                   {{ in_array($amenity, $selectedAmenities) ? 'checked' : '' }}>
                            <span class="label-text text-sm">{{ $amenity }}</span>
                        </label>
                    @endforeach
                </div>
                @error('free_amenities')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        @else
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Free Items / Amenities</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-soft alert-info">
                    <span class="icon-[tabler--info-circle] size-5"></span>
                    <div>
                        <p class="text-sm">No amenities configured for your studio.</p>
                        <a href="{{ route('settings.studio.profile') }}" class="link link-primary text-sm">Configure amenities in Studio Settings</a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Free Rentals --}}
        @php
            $rentalItems = $rentalItems ?? collect();
            $selectedRentalIds = old('free_rental_ids', $membershipPlan?->free_rental_ids ?? []);
        @endphp
        @if($rentalItems->isNotEmpty())
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Free Rentals</h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-base-content/60 mb-3">Select which rental items are included free with this membership</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($rentalItems as $rental)
                        <label class="custom-option flex flex-row items-center gap-3 px-3 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors">
                            <input type="checkbox"
                                   name="free_rental_ids[]"
                                   value="{{ $rental->id }}"
                                   class="checkbox checkbox-primary checkbox-sm"
                                   {{ in_array($rental->id, $selectedRentalIds) ? 'checked' : '' }}>
                            <div class="flex-1 min-w-0">
                                <span class="label-text font-medium">{{ $rental->name }}</span>
                                @if($rental->category)
                                    <span class="badge badge-ghost badge-xs ml-1">{{ $rental->formatted_category }}</span>
                                @endif
                            </div>
                            <span class="text-xs text-base-content/60">{{ $rental->getFormattedPriceForCurrency() }}</span>
                        </label>
                    @endforeach
                </div>
                @error('free_rental_ids')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        @else
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Free Rentals</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-soft alert-info">
                    <span class="icon-[tabler--info-circle] size-5"></span>
                    <div>
                        <p class="text-sm">No rental items configured.</p>
                        <a href="{{ route('rentals.create') }}" class="link link-primary text-sm">Add rental items</a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Eligibility --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Class Eligibility</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="eligibility_scope">Which classes does this membership cover?</label>
                    <select id="eligibility_scope" name="eligibility_scope" class="select w-full @error('eligibility_scope') input-error @enderror" required>
                        @foreach($eligibilityScopes as $value => $label)
                            <option value="{{ $value }}" {{ old('eligibility_scope', $membershipPlan?->eligibility_scope ?? 'all_classes') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('eligibility_scope')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Class Plans selection (shown when eligibility is selected) --}}
                <div id="class-plans-section" class="{{ old('eligibility_scope', $membershipPlan?->eligibility_scope ?? 'all_classes') !== 'selected_class_plans' ? 'hidden' : '' }}">
                    <label class="label-text mb-2 block">Select Class Plans</label>
                    @if($classPlans->isEmpty())
                        <p class="text-base-content/60 text-sm">No active class plans available. <a href="{{ route('class-plans.create') }}" class="link link-primary">Create one first</a>.</p>
                    @else
                        <div class="max-h-64 overflow-y-auto border border-base-300 rounded-lg p-3 space-y-2">
                            @foreach($classPlans as $classPlan)
                                <label class="flex items-center gap-3 cursor-pointer hover:bg-base-200 p-2 rounded">
                                    <input type="checkbox" name="class_plan_ids[]" value="{{ $classPlan->id }}"
                                        class="checkbox checkbox-primary checkbox-sm"
                                        {{ in_array($classPlan->id, old('class_plan_ids', $selectedClassPlanIds)) ? 'checked' : '' }}>
                                    <div class="flex items-center gap-2 flex-1">
                                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $classPlan->color }}"></span>
                                        <span class="font-medium">{{ $classPlan->name }}</span>
                                        <span class="text-xs text-base-content/60">{{ ucfirst($classPlan->category) }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    @error('class_plan_ids')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Location Scope --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Location Access</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="location_scope_type">Which locations can members access?</label>
                    <select id="location_scope_type" name="location_scope_type" class="select w-full @error('location_scope_type') input-error @enderror" required>
                        @foreach($locationScopes as $value => $label)
                            <option value="{{ $value }}" {{ old('location_scope_type', $membershipPlan?->location_scope_type ?? 'all') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('location_scope_type')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Locations selection (shown when scope is selected) --}}
                <div id="locations-section" class="{{ old('location_scope_type', $membershipPlan?->location_scope_type ?? 'all') !== 'selected' ? 'hidden' : '' }}">
                    <label class="label-text mb-2 block">Select Locations</label>
                    @if($locations->isEmpty())
                        <p class="text-base-content/60 text-sm">No locations available.</p>
                    @else
                        <div class="max-h-48 overflow-y-auto border border-base-300 rounded-lg p-3 space-y-2">
                            @foreach($locations as $location)
                                <label class="flex items-center gap-3 cursor-pointer hover:bg-base-200 p-2 rounded">
                                    <input type="checkbox" name="location_ids[]" value="{{ $location->id }}"
                                        class="checkbox checkbox-primary checkbox-sm"
                                        {{ in_array($location->id, old('location_ids', $selectedLocationIds)) ? 'checked' : '' }}>
                                    <span class="font-medium">{{ $location->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    @error('location_ids')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Questionnaire Attachments --}}
        @if(isset($questionnaires) && $questionnaires->count() > 0)
            @include('host.partials._questionnaire-attachments', [
                'questionnaires' => $questionnaires,
                'attachments' => $membershipPlan?->questionnaireAttachments ?? collect()
            ])
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Appearance --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Appearance</h3>
            </div>
            <div class="card-body">
                <div>
                    <label class="label-text" for="color">Display Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="color" name="color"
                            value="{{ old('color', $membershipPlan?->color ?? '#10b981') }}"
                            class="w-12 h-10 rounded cursor-pointer">
                        <input type="text" id="color_text"
                            value="{{ old('color', $membershipPlan?->color ?? '#10b981') }}"
                            class="input flex-1"
                            pattern="^#[0-9A-Fa-f]{6}$"
                            placeholder="#10b981">
                    </div>
                    @error('color')
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
                    <label class="label-text" for="status">Status</label>
                    <select id="status" name="status" class="select w-full @error('status') input-error @enderror" required>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $membershipPlan?->status ?? 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-base-content/60 mt-1">Only active plans can be purchased</p>
                    @error('status')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="visibility_public" value="1"
                        class="toggle toggle-primary"
                        {{ old('visibility_public', $membershipPlan?->visibility_public ?? true) ? 'checked' : '' }}>
                    <div>
                        <span class="font-medium">Visible on Booking Page</span>
                        <p class="text-xs text-base-content/60">Show this plan to customers</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card bg-base-100">
            <div class="card-body space-y-2">
                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--check] size-5"></span>
                    {{ $membershipPlan ? 'Update Membership' : 'Create Membership' }}
                </button>
                <a href="{{ route('catalog.index', ['tab' => 'memberships']) }}" class="btn btn-ghost w-full">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Sync color picker with text input
    document.getElementById('color').addEventListener('input', function() {
        document.getElementById('color_text').value = this.value;
    });
    document.getElementById('color_text').addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            document.getElementById('color').value = this.value;
        }
    });

    // Toggle credits section based on type
    document.getElementById('type').addEventListener('change', function() {
        var creditsSection = document.getElementById('credits-section');
        if (this.value === 'credits') {
            creditsSection.classList.remove('hidden');
        } else {
            creditsSection.classList.add('hidden');
        }
    });

    // Toggle scheduled classes section based on has_scheduled_class
    function toggleScheduledClassSection() {
        var scheduledSection = document.getElementById('scheduled-classes-section');
        var hasScheduledClass = document.querySelector('input[name="has_scheduled_class"]:checked')?.value === '1';

        if (hasScheduledClass) {
            scheduledSection.classList.remove('hidden');
        } else {
            scheduledSection.classList.add('hidden');
        }
    }

    // Toggle all scheduled class checkboxes
    function toggleAllScheduledClasses(selectAllCheckbox) {
        var checkboxes = document.querySelectorAll('.scheduled-class-checkbox');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }

    // Toggle class plans section based on eligibility scope
    document.getElementById('eligibility_scope').addEventListener('change', function() {
        var classPlansSection = document.getElementById('class-plans-section');
        if (this.value === 'selected_class_plans') {
            classPlansSection.classList.remove('hidden');
        } else {
            classPlansSection.classList.add('hidden');
        }
    });

    // Toggle locations section based on location scope
    document.getElementById('location_scope_type').addEventListener('change', function() {
        var locationsSection = document.getElementById('locations-section');
        if (this.value === 'selected') {
            locationsSection.classList.remove('hidden');
        } else {
            locationsSection.classList.add('hidden');
        }
    });

    // Initialize on page load
    toggleScheduledClassSection();
</script>
@endpush
