@php
    $classPlan = $classPlan ?? null;
    $assignedStaffMemberIds = $assignedStaffMemberIds ?? [];
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
                    <label class="label-text" for="name">Class Name</label>
                    <input type="text" id="name" name="name"
                        value="{{ old('name', $classPlan?->name) }}"
                        class="input w-full @error('name') input-error @enderror"
                        placeholder="e.g., Vinyasa Flow Yoga"
                        required>
                    @error('name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="textarea w-full @error('description') input-error @enderror"
                        placeholder="Describe what participants can expect from this class...">{{ old('description', $classPlan?->description) }}</textarea>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text" for="category">Category</label>
                        <select id="category" name="category" class="select w-full @error('category') input-error @enderror" required>
                            <option value="">Select a category...</option>
                            @foreach($categories as $value => $label)
                                <option value="{{ $value }}" {{ old('category', $classPlan?->category) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="type">Type</label>
                        <select id="type" name="type" class="select w-full @error('type') input-error @enderror" required>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type', $classPlan?->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="difficulty_level">Difficulty</label>
                        <select id="difficulty_level" name="difficulty_level" class="select w-full @error('difficulty_level') input-error @enderror" required>
                            @foreach($difficultyLevels as $value => $label)
                                <option value="{{ $value }}" {{ old('difficulty_level', $classPlan?->difficulty_level ?? 'all_levels') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
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
                        <label class="label-text" for="default_duration_minutes">Duration (minutes)</label>
                        <input type="number" id="default_duration_minutes" name="default_duration_minutes"
                            value="{{ old('default_duration_minutes', $classPlan?->default_duration_minutes ?? 60) }}"
                            class="input w-full @error('default_duration_minutes') input-error @enderror"
                            min="15" max="480" required>
                        @error('default_duration_minutes')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="default_capacity">Max Capacity</label>
                        <input type="number" id="default_capacity" name="default_capacity"
                            value="{{ old('default_capacity', $classPlan?->default_capacity ?? 20) }}"
                            class="input w-full @error('default_capacity') input-error @enderror"
                            min="1" max="500" required>
                        @error('default_capacity')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="min_capacity">Min Capacity</label>
                        <input type="number" id="min_capacity" name="min_capacity"
                            value="{{ old('min_capacity', $classPlan?->min_capacity ?? 1) }}"
                            class="input w-full @error('min_capacity') input-error @enderror"
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
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Pricing</h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-base-content/60 mb-4">Leave empty for free classes. New member prices are shown on public booking (subdomain).</p>

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
                                                   value="{{ old('new_member_prices.' . $currency, $classPlan?->new_member_prices[$currency] ?? '') }}"
                                                   class="grow w-full min-w-20" placeholder="0.00">
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                            <tr>
                                <td>
                                    <label class="label-text" for="new_member_drop_in_prices_{{ $hostCurrencies[0] }}">Drop-in Price</label>
                                </td>
                                @foreach($hostCurrencies as $currency)
                                    <td>
                                        <label class="input input-bordered input-sm flex items-center gap-1">
                                            <span class="text-base-content/60 text-sm">{{ $currencySymbols[$currency] ?? $currency }}</span>
                                            <input type="number" id="new_member_drop_in_prices_{{ $currency }}" name="new_member_drop_in_prices[{{ $currency }}]" step="0.01" min="0"
                                                   value="{{ old('new_member_drop_in_prices.' . $currency, $classPlan?->new_member_drop_in_prices[$currency] ?? '') }}"
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
                                    <label class="label-text" for="prices_{{ $hostCurrencies[0] }}">Price</label>
                                </td>
                                @foreach($hostCurrencies as $currency)
                                    <td>
                                        <label class="input input-bordered input-sm flex items-center gap-1">
                                            <span class="text-base-content/60 text-sm">{{ $currencySymbols[$currency] ?? $currency }}</span>
                                            <input type="number" id="prices_{{ $currency }}" name="prices[{{ $currency }}]" step="0.01" min="0"
                                                   value="{{ old('prices.' . $currency, $classPlan?->prices[$currency] ?? '') }}"
                                                   class="grow w-full min-w-20" placeholder="0.00">
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                            <tr>
                                <td>
                                    <label class="label-text" for="drop_in_prices_{{ $hostCurrencies[0] }}">Drop-in Price</label>
                                </td>
                                @foreach($hostCurrencies as $currency)
                                    <td>
                                        <label class="input input-bordered input-sm flex items-center gap-1">
                                            <span class="text-base-content/60 text-sm">{{ $currencySymbols[$currency] ?? $currency }}</span>
                                            <input type="number" id="drop_in_prices_{{ $currency }}" name="drop_in_prices[{{ $currency }}]" step="0.01" min="0"
                                                   value="{{ old('drop_in_prices.' . $currency, $classPlan?->drop_in_prices[$currency] ?? '') }}"
                                                   class="grow w-full min-w-20" placeholder="0.00">
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Billing Discounts --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Billing Period Discounts</h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-base-content/60 mb-4">Set the total amount for each billing period. Client pays this amount upfront for the entire period.</p>

                @php
                    $billingPeriods = ['1' => '1 Mo', '3' => '3 Mo', '6' => '6 Mo', '9' => '9 Mo', '12' => '12 Mo'];
                    $defaultDiscounts = ['1' => 0, '3' => 0, '6' => 0, '9' => 0, '12' => 0];
                @endphp
                <div class="flex items-end gap-2">
                    @foreach($billingPeriods as $months => $label)
                    <div class="flex-1 min-w-0">
                        <label class="label-text text-xs text-center block mb-1" for="billing_discounts_{{ $months }}">{{ $label }}</label>
                        <label class="input input-bordered input-sm flex items-center gap-0.5">
                            <span class="text-base-content/50 text-xs">$</span>
                            <input type="number" id="billing_discounts_{{ $months }}" name="billing_discounts[{{ $months }}]" step="0.01" min="0"
                                   value="{{ old('billing_discounts.' . $months, $classPlan?->billing_discounts[$months] ?? $defaultDiscounts[$months]) }}"
                                   class="grow w-full text-center" placeholder="0">
                        </label>
                    </div>
                    @endforeach
                </div>
                <p class="text-xs text-base-content/50 mt-3">
                    <span class="icon-[tabler--info-circle] size-3 align-middle"></span>
                    Example: Base price $100/month, set 6 Mo to $540 — client pays $540 total ($90/mo) instead of $600
                </p>

                <div class="divider text-base-content/40 text-xs mt-6 mb-4">FEES & CANCELLATION POLICY</div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text text-sm" for="registration_fee">Registration Fee</label>
                        <label class="input input-bordered input-sm flex items-center gap-0.5 mt-1">
                            <span class="text-base-content/50 text-xs">$</span>
                            <input type="number" id="registration_fee" name="registration_fee" step="0.01" min="0"
                                   value="{{ old('registration_fee', $classPlan?->registration_fee ?? '') }}"
                                   class="grow w-full" placeholder="0.00">
                        </label>
                        <p class="text-xs text-base-content/50 mt-1">One-time fee when purchasing a billing period</p>
                    </div>
                    <div>
                        <label class="label-text text-sm" for="cancellation_fee">Cancellation Fee</label>
                        <label class="input input-bordered input-sm flex items-center gap-0.5 mt-1">
                            <span class="text-base-content/50 text-xs">$</span>
                            <input type="number" id="cancellation_fee" name="cancellation_fee" step="0.01" min="0"
                                   value="{{ old('cancellation_fee', $classPlan?->cancellation_fee ?? '') }}"
                                   class="grow w-full" placeholder="0.00">
                        </label>
                        <p class="text-xs text-base-content/50 mt-1">Fee charged for early cancellation</p>
                    </div>
                    <div>
                        <label class="label-text text-sm" for="cancellation_grace_hours">Grace Period</label>
                        <label class="input input-bordered input-sm flex items-center gap-0.5 mt-1">
                            <input type="number" id="cancellation_grace_hours" name="cancellation_grace_hours" step="1" min="0" max="720"
                                   value="{{ old('cancellation_grace_hours', $classPlan?->cancellation_grace_hours ?? 48) }}"
                                   class="grow w-full" placeholder="48">
                            <span class="text-base-content/50 text-xs">hrs</span>
                        </label>
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
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
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

        {{-- Status --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Status</h3>
            </div>
            <div class="card-body space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1"
                        class="toggle toggle-primary"
                        {{ old('is_active', $classPlan?->is_active ?? true) ? 'checked' : '' }}>
                    <div>
                        <span class="font-medium">Active</span>
                        <p class="text-xs text-base-content/60">Class can be scheduled</p>
                    </div>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_visible_on_booking_page" value="1"
                        class="toggle toggle-primary"
                        {{ old('is_visible_on_booking_page', $classPlan?->is_visible_on_booking_page ?? true) ? 'checked' : '' }}>
                    <div>
                        <span class="font-medium">Visible on Booking Page</span>
                        <p class="text-xs text-base-content/60">Show this class to customers on the public booking page</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Staff Members --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Assigned Staff Member</h3>
            </div>
            <div class="card-body">
                @if($staffMembers->isEmpty())
                <p class="text-base-content/60 text-sm">No team members available. <a href="{{ route('settings.team.users') }}" class="link link-primary">Add team members</a> first.</p>
                @else
                <p class="text-sm text-base-content/60 mb-3">Select staff members who can teach this class.</p>

                {{-- Search and Selection Info --}}
                <div class="flex flex-col gap-2 mb-3">
                    <div class="relative">
                        <span class="icon-[tabler--search] absolute left-3 top-1/2 -translate-y-1/2 size-4 text-base-content/50"></span>
                        <input type="text" id="class-staff-search" placeholder="Search..."
                            class="input input-sm w-full pl-9" autocomplete="off">
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="class-staff-selected-count" class="badge badge-primary badge-xs">0 selected</span>
                        <button type="button" id="class-staff-clear-all" class="btn btn-ghost btn-xs hidden">Clear</button>
                    </div>
                </div>

                {{-- Staff Members List --}}
                <div id="class-staff-members-list" class="space-y-2 max-h-64 overflow-y-auto">
                    @foreach($staffMembers as $member)
                    <label class="class-staff-member-item flex items-center gap-2 p-2 rounded-lg border border-base-content/10 cursor-pointer hover:bg-base-200"
                        data-name="{{ strtolower($member->name) }}" data-email="{{ strtolower($member->email) }}" data-role="{{ strtolower($member->pivot->role ?? $member->role) }}">
                        <input type="checkbox" name="staff_member_ids[]" value="{{ $member->id }}"
                            class="checkbox checkbox-primary checkbox-sm class-staff-checkbox"
                            {{ in_array($member->id, old('staff_member_ids', $assignedStaffMemberIds)) ? 'checked' : '' }}>
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            @if($member->profile_photo_url)
                            <img src="{{ $member->profile_photo_url }}" alt="{{ $member->name }}" class="w-8 h-8 rounded-full object-cover shrink-0">
                            @else
                            <div class="avatar avatar-placeholder shrink-0">
                                <div class="bg-primary text-primary-content w-8 h-8 rounded-full font-bold text-xs">
                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                </div>
                            </div>
                            @endif
                            <div class="min-w-0">
                                <div class="font-medium text-sm truncate">{{ $member->name }}</div>
                                <div class="text-xs text-base-content/60">{{ ucfirst($member->pivot->role ?? $member->role) }}</div>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
                <p id="class-staff-no-results" class="text-base-content/50 text-sm py-2 text-center hidden">No staff found.</p>
                @endif
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
