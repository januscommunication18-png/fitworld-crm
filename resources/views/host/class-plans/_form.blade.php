@php
    $classPlan = $classPlan ?? null;
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
                    <label class="label-text" for="name">Class Name <span class="text-error">*</span></label>
                    <input type="text" id="name" name="name"
                        value="{{ old('name', $classPlan?->name) }}"
                        class="input w-full @error('name') is-invalid @enderror"
                        placeholder="e.g., Vinyasa Flow Yoga"
                        required minlength="2" maxlength="255">
                    <span class="error-message text-error text-sm mt-1 hidden">Please enter a class name (min 2 characters)</span>
                    @error('name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="textarea w-full @error('description') is-invalid @enderror"
                        placeholder="Describe what participants can expect from this class...">{{ old('description', $classPlan?->description) }}</textarea>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text" for="category">Category <span class="text-error">*</span></label>
                        <select id="category" name="category" class="select w-full @error('category') is-invalid @enderror" required>
                            <option value="">Select a category...</option>
                            @foreach($categories as $value => $label)
                                <option value="{{ $value }}" {{ old('category', $classPlan?->category) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="error-message text-error text-sm mt-1 hidden">Please select a category</span>
                        @error('category')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="type">Type <span class="text-error">*</span></label>
                        <select id="type" name="type" class="select w-full @error('type') is-invalid @enderror" required>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type', $classPlan?->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="error-message text-error text-sm mt-1 hidden">Please select a type</span>
                        @error('type')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="difficulty_level">Difficulty <span class="text-error">*</span></label>
                        <select id="difficulty_level" name="difficulty_level" class="select w-full @error('difficulty_level') is-invalid @enderror" required>
                            @foreach($difficultyLevels as $value => $label)
                                <option value="{{ $value }}" {{ old('difficulty_level', $classPlan?->difficulty_level ?? 'all_levels') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="error-message text-error text-sm mt-1 hidden">Please select a difficulty level</span>
                        @error('difficulty_level')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Schedule & Capacity --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Schedule & Capacity</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text" for="default_duration_minutes">Duration (minutes) <span class="text-error">*</span></label>
                        <input type="number" id="default_duration_minutes" name="default_duration_minutes"
                            value="{{ old('default_duration_minutes', $classPlan?->default_duration_minutes ?? 60) }}"
                            class="input w-full @error('default_duration_minutes') is-invalid @enderror"
                            min="15" max="480" required>
                        <span class="error-message text-error text-sm mt-1 hidden">Duration must be between 15-480 minutes</span>
                        @error('default_duration_minutes')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="default_capacity">Max Capacity <span class="text-error">*</span></label>
                        <input type="number" id="default_capacity" name="default_capacity"
                            value="{{ old('default_capacity', $classPlan?->default_capacity ?? 20) }}"
                            class="input w-full @error('default_capacity') is-invalid @enderror"
                            min="1" max="500" required>
                        <span class="error-message text-error text-sm mt-1 hidden">Capacity must be between 1-500</span>
                        @error('default_capacity')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="min_capacity">Min Capacity</label>
                        <input type="number" id="min_capacity" name="min_capacity"
                            value="{{ old('min_capacity', $classPlan?->min_capacity ?? 1) }}"
                            class="input w-full @error('min_capacity') is-invalid @enderror"
                            min="0" max="500">
                        <p class="text-xs text-base-content/60 mt-1">Set to 0 for no minimum</p>
                        @error('min_capacity')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <x-studio-pricing-table
            title="Pricing"
            help="Leave empty for free classes. New member prices are shown on public booking (subdomain)."
            :rows="[
                ['section' => 'New Member Pricing', 'section_icon' => 'icon-[tabler--user-plus]', 'section_badge' => 'Public Booking', 'section_bg' => 'bg-info/5'],
                ['name' => 'new_member_prices', 'label' => 'Price', 'values' => $classPlan?->new_member_prices ?? []],
                ['name' => 'new_member_drop_in_prices', 'label' => 'Drop-in Price', 'values' => $classPlan?->new_member_drop_in_prices ?? []],
                ['section' => 'Existing Member Pricing', 'section_icon' => 'icon-[tabler--users]', 'section_bg' => 'bg-base-200/50'],
                ['name' => 'prices', 'label' => 'Price', 'values' => $classPlan?->prices ?? []],
                ['name' => 'drop_in_prices', 'label' => 'Drop-in Price', 'values' => $classPlan?->drop_in_prices ?? []],
            ]"
        />

        {{-- Billing Discounts --}}
        <x-studio-pricing-table
            title="Billing Period Discounts"
            help="Set the total amount for each billing period per currency. Client pays this amount upfront for the entire period."
            :rows="[
                ['name' => 'billing_discounts_1mo', 'label' => '1 Month', 'values' => $classPlan?->billing_discounts['1'] ?? []],
                ['name' => 'billing_discounts_3mo', 'label' => '3 Months', 'values' => $classPlan?->billing_discounts['3'] ?? []],
                ['name' => 'billing_discounts_6mo', 'label' => '6 Months', 'values' => $classPlan?->billing_discounts['6'] ?? []],
                ['name' => 'billing_discounts_9mo', 'label' => '9 Months', 'values' => $classPlan?->billing_discounts['9'] ?? []],
                ['name' => 'billing_discounts_12mo', 'label' => '12 Months', 'values' => $classPlan?->billing_discounts['12'] ?? []],
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
                        :values="$classPlan?->registration_fees ?? []"
                        label="Registration Fee"
                        help="One-time fee when purchasing a billing period"
                    />

                    <x-studio-currency-inputs
                        name="cancellation_fees"
                        :values="$classPlan?->cancellation_fees ?? []"
                        label="Cancellation Fee"
                        help="Fee charged for early cancellation"
                    />

                    <div>
                        <label class="label-text text-sm" for="cancellation_grace_hours">Grace Period</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="number" id="cancellation_grace_hours" name="cancellation_grace_hours" step="1" min="0" max="720"
                                   value="{{ old('cancellation_grace_hours', $classPlan?->cancellation_grace_hours ?? 48) }}"
                                   class="input input-sm w-28" placeholder="48">
                            <span class="text-sm text-base-content/60">hours</span>
                        </div>
                        <p class="text-xs text-base-content/50 mt-1">Full refund window after purchase</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Details --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Additional Details</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="equipment_needed">Equipment Needed</label>
                    <input type="text" id="equipment_needed" name="equipment_needed"
                        value="{{ old('equipment_needed', is_array($classPlan?->equipment_needed) ? implode(', ', $classPlan->equipment_needed) : '') }}"
                        class="input w-full @error('equipment_needed') input-error @enderror"
                        placeholder="e.g., Yoga mat, Block, Strap">
                    <p class="text-xs text-base-content/60 mt-1">Separate items with commas</p>
                    @error('equipment_needed')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Questionnaire Attachments --}}
        @if(isset($questionnaires) && $questionnaires->count() > 0)
            @include('host.partials._questionnaire-attachments', [
                'questionnaires' => $questionnaires,
                'attachments' => $classPlan?->questionnaireAttachments ?? collect()
            ])
        @endif

        {{-- Progress Template Attachments --}}
        @if(isset($progressTemplates) && $progressTemplates->count() > 0)
            @include('host.partials._progress-template-attachments', [
                'progressTemplates' => $progressTemplates,
                'attachments' => $classPlan?->progressTemplateAttachments ?? collect()
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
                <div id="image-preview-wrapper" class="{{ $classPlan?->image_path ? '' : 'hidden' }}">
                    <div class="relative group rounded-xl overflow-hidden">
                        <img id="image-preview" src="{{ $classPlan?->image_url ?? '' }}" alt="Class image" class="w-full h-44 object-cover">
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
                <div id="image-upload-zone" class="border-2 border-dashed border-base-content/20 rounded-xl p-6 text-center cursor-pointer hover:border-primary/50 hover:bg-primary/5 transition-colors {{ $classPlan?->image_path ? 'hidden' : '' }}"
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
                            value="{{ old('color', $classPlan?->color ?? '#6366f1') }}"
                            class="w-12 h-10 rounded cursor-pointer">
                        <input type="text" id="color_text"
                            value="{{ old('color', $classPlan?->color ?? '#6366f1') }}"
                            class="input flex-1"
                            pattern="^#[0-9A-Fa-f]{6}$"
                            placeholder="#6366f1">
                    </div>
                    @error('color')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <x-studio-members
            :selected-staff="$assignedStaffMemberIds"
            :selected-instructors="$assignedInstructorIds ?? []"
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
                        <p class="text-xs text-base-content/60">Class can be scheduled</p>
                    </div>
                    <label class="switch switch-primary">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $classPlan?->is_active ?? true) ? 'checked' : '' }} />
                        <span class="switch-indicator"></span>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-medium">Visible on Booking Page</span>
                        <p class="text-xs text-base-content/60">Show this class to customers on the public booking page</p>
                    </div>
                    <label class="switch switch-primary">
                        <input type="checkbox" name="is_visible_on_booking_page" value="1"
                            {{ old('is_visible_on_booking_page', $classPlan?->is_visible_on_booking_page ?? true) ? 'checked' : '' }} />
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
                    {{ $classPlan ? 'Update Class Plan' : 'Create Class Plan' }}
                </button>
                <a href="{{ route('catalog.index', ['tab' => 'classes']) }}" class="btn btn-ghost w-full">
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

    // Staff member search and selection
    (function() {
        const searchInput = document.getElementById('class-staff-search');
        const staffList = document.getElementById('class-staff-members-list');
        const noResults = document.getElementById('class-staff-no-results');
        const selectedCount = document.getElementById('class-staff-selected-count');
        const clearAllBtn = document.getElementById('class-staff-clear-all');

        if (!searchInput || !staffList) return;

        const staffItems = staffList.querySelectorAll('.class-staff-member-item');
        const checkboxes = staffList.querySelectorAll('.class-staff-checkbox');

        function updateSelectedCount() {
            const count = staffList.querySelectorAll('.class-staff-checkbox:checked').length;
            selectedCount.textContent = count + ' selected';
            clearAllBtn.classList.toggle('hidden', count === 0);
        }

        function filterStaff() {
            const query = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            staffItems.forEach(item => {
                const name = item.dataset.name || '';
                const email = item.dataset.email || '';
                const role = item.dataset.role || '';
                const matches = !query || name.includes(query) || email.includes(query) || role.includes(query);

                item.classList.toggle('hidden', !matches);
                if (matches) visibleCount++;
            });

            noResults.classList.toggle('hidden', visibleCount > 0);
        }

        searchInput.addEventListener('input', filterStaff);

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });

        clearAllBtn.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = false);
            updateSelectedCount();
        });

        // Initial count
        updateSelectedCount();
    })();

</script>
@endpush
