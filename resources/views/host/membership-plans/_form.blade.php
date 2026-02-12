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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                        <label class="label-text" for="price">Price ($)</label>
                        <input type="number" id="price" name="price"
                            value="{{ old('price', $membershipPlan?->price ?? '0') }}"
                            class="input w-full @error('price') input-error @enderror"
                            min="0" max="99999.99" step="0.01"
                            placeholder="0.00"
                            required>
                        @error('price')
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
            </div>
        </div>

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
</script>
@endpush
