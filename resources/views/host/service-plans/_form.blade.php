@php
    $servicePlan = $servicePlan ?? null;
    $assignedStaffMemberIds = $assignedStaffMemberIds ?? [];
@endphp

<div class="space-y-6">
        {{-- Basic Info --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Basic Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="name">Service Name <span class="text-error">*</span></label>
                    <input type="text" id="name" name="name"
                        value="{{ old('name', $servicePlan?->name) }}"
                        class="input w-full @error('name') is-invalid @enderror"
                        placeholder="e.g., Private Yoga Session"
                        required minlength="2" maxlength="255">
                    <span class="error-message text-error text-sm mt-1 hidden">Please enter a service name (min 2 characters)</span>
                    @error('name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="textarea w-full @error('description') is-invalid @enderror"
                        placeholder="Describe what clients can expect from this service...">{{ old('description', $servicePlan?->description) }}</textarea>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="category">Category <span class="text-error">*</span></label>
                        <select id="category" name="category" class="hidden" required
                            data-select='{
                                "hasSearch": true,
                                "searchPlaceholder": "Search categories...",
                                "placeholder": "Select a category...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            <option value="">Select a category...</option>
                            @foreach($categories as $value => $label)
                                <option value="{{ $value }}" {{ old('category', $servicePlan?->category) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="error-message text-error text-sm mt-1 hidden">Please select a category</span>
                        @error('category')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="location_type">Location Type <span class="text-error">*</span></label>
                        <select id="location_type" name="location_type" class="hidden" required
                            data-select='{
                                "placeholder": "Select location type...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'>
                            @foreach($locationTypes as $value => $label)
                                <option value="{{ $value }}" {{ old('location_type', $servicePlan?->location_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="error-message text-error text-sm mt-1 hidden">Please select a location type</span>
                        @error('location_type')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Duration & Scheduling --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Duration & Scheduling</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text" for="duration_minutes">Duration (minutes) <span class="text-error">*</span></label>
                        <input type="number" id="duration_minutes" name="duration_minutes"
                            value="{{ old('duration_minutes', $servicePlan?->duration_minutes ?? 60) }}"
                            class="input w-full @error('duration_minutes') is-invalid @enderror"
                            min="15" max="480" required>
                        <span class="error-message text-error text-sm mt-1 hidden">Duration must be between 15-480 minutes</span>
                        @error('duration_minutes')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="buffer_minutes">Buffer Time (minutes)</label>
                        <input type="number" id="buffer_minutes" name="buffer_minutes"
                            value="{{ old('buffer_minutes', $servicePlan?->buffer_minutes ?? 15) }}"
                            class="input w-full @error('buffer_minutes') is-invalid @enderror"
                            min="0" max="120">
                        <p class="text-xs text-base-content/60 mt-1">Time between appointments</p>
                        @error('buffer_minutes')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="max_participants">Max Participants <span class="text-error">*</span></label>
                        <input type="number" id="max_participants" name="max_participants"
                            value="{{ old('max_participants', $servicePlan?->max_participants ?? 1) }}"
                            class="input w-full @error('max_participants') is-invalid @enderror"
                            min="1" max="20" required>
                        <p class="text-xs text-base-content/60 mt-1">1 for private sessions</p>
                        @error('max_participants')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="booking_notice_hours">Minimum Booking Notice (hours)</label>
                        <input type="number" id="booking_notice_hours" name="booking_notice_hours"
                            value="{{ old('booking_notice_hours', $servicePlan?->booking_notice_hours ?? 24) }}"
                            class="input w-full @error('booking_notice_hours') is-invalid @enderror"
                            min="0" max="168">
                        <p class="text-xs text-base-content/60 mt-1">How far in advance clients must book</p>
                        @error('booking_notice_hours')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="cancellation_hours">Cancellation Window (hours)</label>
                        <input type="number" id="cancellation_hours" name="cancellation_hours"
                            value="{{ old('cancellation_hours', $servicePlan?->cancellation_hours ?? 24) }}"
                            class="input w-full @error('cancellation_hours') is-invalid @enderror"
                            min="0" max="168">
                        <p class="text-xs text-base-content/60 mt-1">Minimum notice for free cancellation</p>
                        @error('cancellation_hours')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <x-studio-pricing-table
            title="Pricing"
            help="Leave empty for free services. New member prices are shown on public booking (subdomain)."
            :rows="[
                ['section' => 'New Member Pricing', 'section_icon' => 'icon-[tabler--user-plus]', 'section_badge' => 'Public Booking', 'section_bg' => 'bg-info/5'],
                ['name' => 'new_member_prices', 'label' => 'Service Price', 'values' => $servicePlan?->new_member_prices ?? []],
                ['name' => 'new_member_deposit_prices', 'label' => 'Deposit', 'values' => $servicePlan?->new_member_deposit_prices ?? []],
                ['section' => 'Existing Member Pricing', 'section_icon' => 'icon-[tabler--users]', 'section_bg' => 'bg-base-200/50'],
                ['name' => 'prices', 'label' => 'Service Price', 'values' => $servicePlan?->prices ?? []],
                ['name' => 'deposit_prices', 'label' => 'Deposit', 'values' => $servicePlan?->deposit_prices ?? []],
            ]"
        />

        {{-- Billing Discounts --}}
        <x-studio-pricing-table
            title="Billing Period Discounts"
            help="Set the total amount for each billing period per currency. Client pays this amount upfront for the entire period."
            :rows="[
                ['name' => 'billing_discounts_1mo', 'label' => '1 Month', 'values' => $servicePlan?->billing_discounts['1'] ?? []],
                ['name' => 'billing_discounts_3mo', 'label' => '3 Months', 'values' => $servicePlan?->billing_discounts['3'] ?? []],
                ['name' => 'billing_discounts_6mo', 'label' => '6 Months', 'values' => $servicePlan?->billing_discounts['6'] ?? []],
                ['name' => 'billing_discounts_9mo', 'label' => '9 Months', 'values' => $servicePlan?->billing_discounts['9'] ?? []],
                ['name' => 'billing_discounts_12mo', 'label' => '12 Months', 'values' => $servicePlan?->billing_discounts['12'] ?? []],
            ]"
        >
            <div class="mt-4">
                <p class="text-xs text-base-content/50">
                    <span class="icon-[tabler--info-circle] size-3 align-middle"></span>
                    Example: Base price $100/month, set 6 Months to $540 — client pays $540 total ($90/mo) instead of $600
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
                    <x-studio-currency-inputs
                        name="registration_fees"
                        :values="$servicePlan?->registration_fees ?? []"
                        label="Registration Fee"
                        help="One-time fee when purchasing a billing period"
                    />

                    <x-studio-currency-inputs
                        name="cancellation_fees"
                        :values="$servicePlan?->cancellation_fees ?? []"
                        label="Cancellation Fee"
                        help="Fee charged for early cancellation"
                    />

                    <div>
                        <label class="label-text text-sm" for="cancellation_grace_hours">Grace Period</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="number" id="cancellation_grace_hours" name="cancellation_grace_hours" step="1" min="0" max="720"
                                   value="{{ old('cancellation_grace_hours', $servicePlan?->cancellation_grace_hours ?? 48) }}"
                                   class="input input-sm w-28" placeholder="48">
                            <span class="text-sm text-base-content/60">hours</span>
                        </div>
                        <p class="text-xs text-base-content/50 mt-1">Full refund window after purchase</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Questionnaire Attachments --}}
        @if(isset($questionnaires) && $questionnaires->count() > 0)
            @include('host.partials._questionnaire-attachments', [
                'questionnaires' => $questionnaires,
                'attachments' => $servicePlan?->questionnaireAttachments ?? collect()
            ])
        @endif

        {{-- Image --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Image</h3>
            </div>
            <div class="card-body">
                <input type="file" id="image" name="image" class="hidden" accept="image/jpeg,image/png,image/jpg,image/webp">

                {{-- Preview (shown when image exists) --}}
                <div id="image-preview-wrapper" class="{{ $servicePlan?->image_path ? '' : 'hidden' }}">
                    <div class="relative group rounded-xl overflow-hidden">
                        <img id="image-preview" src="{{ $servicePlan?->image_url ?? '' }}" alt="Service image" class="w-full h-44 object-cover">
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
                <div id="image-upload-zone" class="border-2 border-dashed border-base-content/20 rounded-xl p-6 text-center cursor-pointer hover:border-primary/50 hover:bg-primary/5 transition-colors {{ $servicePlan?->image_path ? 'hidden' : '' }}"
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

        {{-- Appearance --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Appearance</h3>
            </div>
            <div class="card-body">
                <div>
                    <label class="label-text" for="color">Calendar Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="color" name="color"
                            value="{{ old('color', $servicePlan?->color ?? '#8b5cf6') }}"
                            class="w-12 h-10 rounded cursor-pointer">
                        <input type="text" id="color_text"
                            value="{{ old('color', $servicePlan?->color ?? '#8b5cf6') }}"
                            class="input flex-1"
                            pattern="^#[0-9A-Fa-f]{6}$"
                            placeholder="#8b5cf6">
                    </div>
                    @error('color')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <x-studio-members
            :selected-staff="$assignedStaffMemberIds"
            :selected-instructors="[]"
            title="Assigned Staff Members"
            staff-name="staff_member_ids"
        />

        {{-- Status --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Status</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-medium">Active</span>
                        <p class="text-xs text-base-content/60">Service can be booked</p>
                    </div>
                    <label class="switch switch-primary">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $servicePlan?->is_active ?? true) ? 'checked' : '' }} />
                        <span class="switch-indicator"></span>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-medium">Visible on Booking Page</span>
                        <p class="text-xs text-base-content/60">Show this service to customers on the public booking page</p>
                    </div>
                    <label class="switch switch-primary">
                        <input type="checkbox" name="is_visible_on_booking_page" value="1"
                            {{ old('is_visible_on_booking_page', $servicePlan?->is_visible_on_booking_page ?? true) ? 'checked' : '' }} />
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
                    {{ $servicePlan ? 'Update Service Plan' : 'Create Service Plan' }}
                </button>
                <a href="{{ route('catalog.index', ['tab' => 'services']) }}" class="btn btn-ghost w-full">
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
</script>
@endpush