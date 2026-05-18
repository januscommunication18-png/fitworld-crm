@php
    $membershipPlan = $membershipPlan ?? null;
    $selectedClassPlanIds = $selectedClassPlanIds ?? [];
    $selectedLocationIds = $selectedLocationIds ?? [];
@endphp

<div class="space-y-6">
        {{-- Basic Info --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Basic Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="name">Plan Name <span class="text-error">*</span></label>
                    <input type="text" id="name" name="name"
                        value="{{ old('name', $membershipPlan?->name) }}"
                        class="input w-full @error('name') is-invalid @enderror"
                        placeholder="e.g., Unlimited Monthly, 8 Classes/Month"
                        required minlength="2" maxlength="255">
                    <span class="error-message text-error text-sm mt-1 hidden">Please enter a plan name (min 2 characters)</span>
                    @error('name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="textarea w-full @error('description') is-invalid @enderror"
                        placeholder="Describe the benefits and what's included in this membership...">{{ old('description', $membershipPlan?->description) }}</textarea>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text" for="type">Plan Type <span class="text-error">*</span></label>
                        <x-studio-select name="type" :options="$types" :selected="$membershipPlan?->type ?? 'unlimited'" placeholder="Select type..." :required="true" />
                        <p class="text-xs text-base-content/60 mt-1">Unlimited = full access, Credits = limited bookings</p>
                        @error('type')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="interval">Billing Interval <span class="text-error">*</span></label>
                        <x-studio-select name="interval" :options="$intervals" :selected="$membershipPlan?->interval ?? 'monthly'" placeholder="Select interval..." :required="true" />
                        @error('interval')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="credits-section" class="{{ old('type', $membershipPlan?->type ?? 'unlimited') !== 'credits' ? 'hidden' : '' }}">
                        <label class="label-text" for="credits_per_cycle">Credits per Cycle <span class="text-error">*</span></label>
                        <input type="number" id="credits_per_cycle" name="credits_per_cycle"
                            value="{{ old('credits_per_cycle', $membershipPlan?->credits_per_cycle ?? 8) }}"
                            class="input w-full @error('credits_per_cycle') input-error @enderror"
                            min="1" max="999">
                        <p class="text-xs text-base-content/60 mt-1">Bookings included per cycle</p>
                        @error('credits_per_cycle')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        @php
            $oneMonthValues = $membershipPlan?->billing_discounts['1'] ?? $membershipPlan?->prices ?? [];
        @endphp
        <x-studio-pricing-table
            title="Pricing"
            help="Set the monthly price per currency. Longer periods are optional prepaid discounts."
            :rows="[
                ['name' => 'billing_discounts_1mo', 'label' => '1 Month', 'values' => $oneMonthValues, 'required' => true],
                ['name' => 'billing_discounts_3mo', 'label' => '3 Months', 'values' => $membershipPlan?->billing_discounts['3'] ?? []],
                ['name' => 'billing_discounts_6mo', 'label' => '6 Months', 'values' => $membershipPlan?->billing_discounts['6'] ?? []],
                ['name' => 'billing_discounts_9mo', 'label' => '9 Months', 'values' => $membershipPlan?->billing_discounts['9'] ?? []],
                ['name' => 'billing_discounts_12mo', 'label' => '12 Months', 'values' => $membershipPlan?->billing_discounts['12'] ?? []],
            ]"
        >
            <div class="mt-4">
                <p class="text-xs text-base-content/50">
                    <span class="icon-[tabler--info-circle] size-3 align-middle"></span>
                    1 Month is the base price. Longer periods are prepaid discounts — e.g., set 6 Months to $540 instead of $600 ($90/mo effective).
                </p>
            </div>
        </x-studio-pricing-table>

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
                            :values="$membershipPlan?->registration_fees ?? []"
                            label="Registration Fee"
                            help="One-time fee when purchasing a billing period"
                        />

                        <x-studio-currency-inputs
                            name="cancellation_fees"
                            :values="$membershipPlan?->cancellation_fees ?? []"
                            label="Cancellation Fee"
                            help="Fee charged for early cancellation"
                        />
                    </div>

                    <div>
                        <label class="label-text text-sm" for="cancellation_grace_hours">Grace Period</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="number" id="cancellation_grace_hours" name="cancellation_grace_hours" step="1" min="0" max="720"
                                   value="{{ old('cancellation_grace_hours', $membershipPlan?->cancellation_grace_hours ?? 48) }}"
                                   class="input input-sm w-28" placeholder="48">
                            <span class="text-sm text-base-content/60">hours</span>
                        </div>
                        <p class="text-xs text-base-content/50 mt-1">Full refund window after purchase</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Addon Members --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Additional Details</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="addon_members">Addon Members</label>
                    @php
                        $addonOptions = ['0' => 'Individual (no guests)', '1' => '+1 Guest', '2' => '+2 Guests', '3' => '+3 Guests', '4' => '+4 Guests'];
                    @endphp
                    <x-studio-select name="addon_members" :options="$addonOptions" :selected="(string) ($membershipPlan?->addon_members ?? 0)" placeholder="Select..." />
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
                    <x-studio-select name="location_scope_type" id="location_scope_type" :options="$locationScopes" :selected="$membershipPlan?->location_scope_type ?? 'all'" placeholder="Select scope..." :required="true" />
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

        {{-- File Attachments --}}
        <x-studio-file-upload
            name="file_attachments"
            :files="$membershipPlan?->file_attachments ?? []"
            title="File Attachments"
            help="Upload PDFs, documents, or images to attach to this membership plan."
        />

        {{-- Image --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Image</h3>
            </div>
            <div class="card-body">
                <input type="file" id="image" name="image" class="hidden" accept="image/jpeg,image/png,image/jpg,image/webp">

                {{-- Preview (shown when image exists) --}}
                <div id="image-preview-wrapper" class="{{ $membershipPlan?->image_path ? '' : 'hidden' }}">
                    <div class="relative group rounded-xl overflow-hidden">
                        <img id="image-preview" src="{{ $membershipPlan?->image_url ?? '' }}" alt="Membership image" class="w-full h-44 object-cover">
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
                <div id="image-upload-zone" class="border-2 border-dashed border-base-content/20 rounded-xl p-6 text-center cursor-pointer hover:border-primary/50 hover:bg-primary/5 transition-colors {{ $membershipPlan?->image_path ? 'hidden' : '' }}"
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
                        <label class="label-text" for="status">Plan Status <span class="text-error">*</span></label>
                        <x-studio-select name="status" :options="$statuses" :selected="$membershipPlan?->status ?? 'draft'" placeholder="Select status..." :required="true" />
                        <p class="text-xs text-base-content/60 mt-1">Only active plans can be purchased</p>
                        @error('status')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
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

                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-medium">Visible on Booking Page</span>
                        <p class="text-xs text-base-content/60">Show this plan to customers on the public booking page</p>
                    </div>
                    <label class="switch switch-primary">
                        <input type="checkbox" name="visibility_public" value="1"
                            {{ old('visibility_public', $membershipPlan?->visibility_public ?? true) ? 'checked' : '' }} />
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
                    {{ $membershipPlan ? 'Update Membership Plan' : 'Create Membership Plan' }}
                </button>
                <a href="{{ route('catalog.index', ['tab' => 'memberships']) }}" class="btn btn-ghost w-full">
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
