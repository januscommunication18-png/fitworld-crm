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

                {{-- Multi-Currency Pricing --}}
                <div>
                    <label class="label-text mb-2 block">Pricing by Currency</label>
                    <p class="text-sm text-base-content/60 mb-3">New member prices are shown on public booking (subdomain). Default currency price is required.</p>

                    @php
                        $existingPrices = $membershipPlan?->prices ?? [];
                        $existingNewMemberPrices = $membershipPlan?->new_member_prices ?? [];
                        $legacyPrice = $membershipPlan?->price;
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
                                {{-- New Member Pricing Section --}}
                                <tr class="bg-info/5">
                                    <td colspan="{{ count($hostCurrencies) + 1 }}" class="font-semibold">
                                        <span class="icon-[tabler--user-plus] size-4 me-1 align-middle"></span>
                                        New Member Pricing
                                        <span class="badge badge-soft badge-info badge-sm ms-2">Public Booking</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="label-text" for="new_member_prices_{{ $hostCurrencies[0] }}">Price</label>
                                    </td>
                                    @foreach($hostCurrencies as $currency)
                                        <td>
                                            <label class="input input-bordered input-sm flex items-center gap-1">
                                                <span class="text-base-content/60 text-sm">{{ $currencySymbols[$currency] ?? $currency }}</span>
                                                <input type="number" id="new_member_prices_{{ $currency }}" name="new_member_prices[{{ $currency }}]" step="0.01" min="0"
                                                       value="{{ old('new_member_prices.' . $currency, $existingNewMemberPrices[$currency] ?? '') }}"
                                                       class="grow w-full min-w-20" placeholder="0.00">
                                            </label>
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- Existing Member Pricing Section --}}
                                <tr class="bg-base-200/50">
                                    <td colspan="{{ count($hostCurrencies) + 1 }}" class="font-semibold">
                                        <span class="icon-[tabler--users] size-4 me-1 align-middle"></span>
                                        Existing Member Pricing
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="label-text" for="prices_{{ $hostCurrencies[0] }}">Price <span class="text-error">*</span></label>
                                    </td>
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
                                                <input type="number" id="prices_{{ $currency }}" name="prices[{{ $currency }}]" step="0.01" min="0"
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

        {{-- Default eligibility scope (all classes) --}}
        <input type="hidden" name="eligibility_scope" value="all_classes">

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

    // Toggle locations section based on location scope
    document.getElementById('location_scope_type').addEventListener('change', function() {
        var locationsSection = document.getElementById('locations-section');
        if (this.value === 'selected') {
            locationsSection.classList.remove('hidden');
        } else {
            locationsSection.classList.add('hidden');
        }
    });
</script>
@endpush
