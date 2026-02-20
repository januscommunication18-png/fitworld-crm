@php
    $rental = $rental ?? null;
    $selectedClassPlanIds = $selectedClassPlanIds ?? [];
    $requiredClassPlanIds = $requiredClassPlanIds ?? [];
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
                    <label class="label-text" for="name">Item Name <span class="text-error">*</span></label>
                    <input type="text" id="name" name="name"
                        value="{{ old('name', $rental?->name) }}"
                        class="input w-full @error('name') input-error @enderror"
                        placeholder="e.g., Premium Yoga Mat"
                        required>
                    @error('name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="textarea w-full @error('description') input-error @enderror"
                        placeholder="Describe the rental item...">{{ old('description', $rental?->description) }}</textarea>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="category">Category</label>
                        <select id="category" name="category" class="select w-full @error('category') input-error @enderror">
                            <option value="">Select Category</option>
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}" {{ old('category', $rental?->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="sku">SKU / Item Code</label>
                        <input type="text" id="sku" name="sku"
                            value="{{ old('sku', $rental?->sku) }}"
                            class="input w-full @error('sku') input-error @enderror"
                            placeholder="e.g., MAT-001">
                        @error('sku')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Inventory --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Inventory</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text" for="total_inventory">Total Quantity <span class="text-error">*</span></label>
                        <input type="number" id="total_inventory" name="total_inventory"
                            value="{{ old('total_inventory', $rental?->total_inventory ?? 1) }}"
                            class="input w-full @error('total_inventory') input-error @enderror"
                            min="0" required>
                        @error('total_inventory')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="max_rental_days">Max Rental Days</label>
                        <input type="number" id="max_rental_days" name="max_rental_days"
                            value="{{ old('max_rental_days', $rental?->max_rental_days) }}"
                            class="input w-full @error('max_rental_days') input-error @enderror"
                            min="1" max="365"
                            placeholder="No limit">
                        <p class="text-xs text-base-content/60 mt-1">Leave empty for no limit</p>
                        @error('max_rental_days')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text">Return Required</label>
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-base-content/10 cursor-pointer hover:bg-base-200 h-12">
                            <input type="checkbox" name="requires_return" value="1"
                                class="checkbox checkbox-primary checkbox-sm"
                                {{ old('requires_return', $rental?->requires_return ?? true) ? 'checked' : '' }}>
                            <span class="text-sm">Must be returned</span>
                        </label>
                    </div>
                </div>

                @if($rental)
                    <div class="alert alert-info">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        <span>Currently <strong>{{ $rental->available_inventory }}</strong> of <strong>{{ $rental->total_inventory }}</strong> available.</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Pricing --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Pricing</h3>
            </div>
            <div class="card-body space-y-4">
                {{-- Rental Price --}}
                <div>
                    <label class="label-text font-medium">Rental Price</label>
                    <p class="text-xs text-base-content/60 mb-2">Price charged per rental period</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($hostCurrencies as $currency)
                            <div>
                                <label class="text-xs text-base-content/60 flex items-center gap-1 mb-1">
                                    {{ $currency }}
                                    @if($currency === $defaultCurrency)
                                        <span class="badge badge-primary badge-xs">Default</span>
                                    @endif
                                </label>
                                <label class="input input-bordered flex items-center gap-2">
                                    <span class="text-base-content/60">{{ $currencySymbols[$currency] ?? $currency }}</span>
                                    <input type="number" name="prices[{{ $currency }}]" step="0.01" min="0"
                                           value="{{ old('prices.' . $currency, $rental?->prices[$currency] ?? '') }}"
                                           class="grow w-full" placeholder="0.00">
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Security Deposit --}}
                <div>
                    <label class="label-text font-medium">Security Deposit</label>
                    <p class="text-xs text-base-content/60 mb-2">Refundable deposit collected at checkout</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($hostCurrencies as $currency)
                            <div>
                                <label class="text-xs text-base-content/60 mb-1 block">{{ $currency }}</label>
                                <label class="input input-bordered flex items-center gap-2">
                                    <span class="text-base-content/60">{{ $currencySymbols[$currency] ?? $currency }}</span>
                                    <input type="number" name="deposit_prices[{{ $currency }}]" step="0.01" min="0"
                                           value="{{ old('deposit_prices.' . $currency, $rental?->deposit_prices[$currency] ?? '') }}"
                                           class="grow w-full" placeholder="0.00">
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Class Plan Association --}}
        @if($classPlans->isNotEmpty())
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Associated Classes</h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-base-content/60 mb-4">Select classes where this item is suggested or required during booking.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($classPlans as $classPlan)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-base-content/10 cursor-pointer hover:bg-base-200">
                        <input type="checkbox" name="class_plan_ids[]" value="{{ $classPlan->id }}"
                            class="checkbox checkbox-primary checkbox-sm class-plan-checkbox"
                            data-id="{{ $classPlan->id }}"
                            {{ in_array($classPlan->id, old('class_plan_ids', $selectedClassPlanIds)) ? 'checked' : '' }}>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium">{{ $classPlan->name }}</div>
                        </div>
                        <label class="flex items-center gap-1 text-xs">
                            <input type="checkbox" name="required_class_plan_ids[]" value="{{ $classPlan->id }}"
                                class="checkbox checkbox-warning checkbox-xs required-checkbox"
                                data-for="{{ $classPlan->id }}"
                                {{ in_array($classPlan->id, old('required_class_plan_ids', $requiredClassPlanIds)) ? 'checked' : '' }}>
                            <span class="text-warning font-medium">Required</span>
                        </label>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Eligibility --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Who Can Rent</h3>
            </div>
            <div class="card-body space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="eligibility_type" value="all"
                        class="radio radio-primary"
                        {{ old('eligibility_type', 'all') === 'all' ? 'checked' : '' }}>
                    <div>
                        <span class="font-medium">Everyone</span>
                        <p class="text-xs text-base-content/60">All customers can rent this item</p>
                    </div>
                </label>

                @if($membershipPlans->isNotEmpty())
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="eligibility_type" value="membership"
                        class="radio radio-primary"
                        {{ old('eligibility_type') === 'membership' ? 'checked' : '' }}>
                    <div>
                        <span class="font-medium">Membership Holders Only</span>
                        <p class="text-xs text-base-content/60">Only members with specific plans can rent</p>
                    </div>
                </label>

                <div class="ml-8 space-y-2 hidden" id="membership_options">
                    @foreach($membershipPlans as $plan)
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-base-content/10 cursor-pointer hover:bg-base-200">
                            <input type="checkbox" name="eligible_membership_ids[]" value="{{ $plan->id }}"
                                class="checkbox checkbox-primary checkbox-sm">
                            <span class="flex-1">{{ $plan->name }}</span>
                            <label class="flex items-center gap-1 text-xs text-success">
                                <input type="checkbox" name="free_membership_ids[]" value="{{ $plan->id }}"
                                    class="checkbox checkbox-success checkbox-xs">
                                <span class="font-medium">Free</span>
                            </label>
                        </label>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Image --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Image</h3>
            </div>
            <div class="card-body">
                @if($rental && !empty($rental->images))
                <div class="grid grid-cols-2 gap-2 mb-4">
                    @foreach($rental->images as $image)
                        <div class="relative group">
                            <img src="{{ Storage::url($image) }}" alt="" class="w-full aspect-square object-cover rounded-lg">
                            <label class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-lg cursor-pointer">
                                <input type="checkbox" name="delete_images[]" value="{{ $image }}" class="checkbox checkbox-error checkbox-sm">
                                <span class="text-white text-xs ml-2">Delete</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @endif
                <input type="file" id="images" name="images[]"
                    class="file-input file-input-bordered w-full @error('images') input-error @enderror"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    multiple>
                <p class="text-xs text-base-content/60 mt-1">JPG, PNG or WebP. Max 2MB each.</p>
                @error('images')
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
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1"
                        class="toggle toggle-success"
                        {{ old('is_active', $rental?->is_active ?? true) ? 'checked' : '' }}>
                    <div>
                        <span class="font-medium">Active</span>
                        <p class="text-xs text-base-content/60">Item is available for rent</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card bg-base-100">
            <div class="card-body space-y-2">
                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--check] size-5"></span>
                    {{ $rental ? 'Update Item' : 'Create Item' }}
                </button>
                <a href="{{ route('rentals.index') }}" class="btn btn-ghost w-full">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle eligibility type radio buttons
    const eligibilityRadios = document.querySelectorAll('input[name="eligibility_type"]');
    const membershipOptions = document.getElementById('membership_options');

    function toggleMembershipOptions() {
        const selected = document.querySelector('input[name="eligibility_type"]:checked');
        if (membershipOptions) {
            membershipOptions.classList.toggle('hidden', selected?.value !== 'membership');
        }
    }

    eligibilityRadios.forEach(radio => {
        radio.addEventListener('change', toggleMembershipOptions);
    });

    toggleMembershipOptions();

    // Handle required checkbox - only enable if parent class is checked
    document.querySelectorAll('.class-plan-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const requiredCheckbox = document.querySelector(`.required-checkbox[data-for="${this.dataset.id}"]`);
            if (requiredCheckbox) {
                requiredCheckbox.disabled = !this.checked;
                if (!this.checked) {
                    requiredCheckbox.checked = false;
                }
            }
        });

        // Initial state
        const requiredCheckbox = document.querySelector(`.required-checkbox[data-for="${checkbox.dataset.id}"]`);
        if (requiredCheckbox) {
            requiredCheckbox.disabled = !checkbox.checked;
        }
    });
});
</script>
@endpush
