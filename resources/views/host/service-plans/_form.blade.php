@php
    $servicePlan = $servicePlan ?? null;
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
                    <label class="label-text" for="name">Service Name</label>
                    <input type="text" id="name" name="name"
                        value="{{ old('name', $servicePlan?->name) }}"
                        class="input w-full @error('name') input-error @enderror"
                        placeholder="e.g., Private Yoga Session"
                        required>
                    @error('name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="textarea w-full @error('description') input-error @enderror"
                        placeholder="Describe what clients can expect from this service...">{{ old('description', $servicePlan?->description) }}</textarea>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="category">Category</label>
                        <select id="category" name="category" class="select w-full @error('category') input-error @enderror" required>
                            @foreach($categories as $value => $label)
                                <option value="{{ $value }}" {{ old('category', $servicePlan?->category) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="location_type">Location Type</label>
                        <select id="location_type" name="location_type" class="select w-full @error('location_type') input-error @enderror" required>
                            @foreach($locationTypes as $value => $label)
                                <option value="{{ $value }}" {{ old('location_type', $servicePlan?->location_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
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
                        <label class="label-text" for="duration_minutes">Duration (minutes)</label>
                        <input type="number" id="duration_minutes" name="duration_minutes"
                            value="{{ old('duration_minutes', $servicePlan?->duration_minutes ?? 60) }}"
                            class="input w-full @error('duration_minutes') input-error @enderror"
                            min="15" max="480" required>
                        @error('duration_minutes')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="buffer_minutes">Buffer Time (minutes)</label>
                        <input type="number" id="buffer_minutes" name="buffer_minutes"
                            value="{{ old('buffer_minutes', $servicePlan?->buffer_minutes ?? 15) }}"
                            class="input w-full @error('buffer_minutes') input-error @enderror"
                            min="0" max="120">
                        <p class="text-xs text-base-content/60 mt-1">Time between appointments</p>
                        @error('buffer_minutes')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="max_participants">Max Participants</label>
                        <input type="number" id="max_participants" name="max_participants"
                            value="{{ old('max_participants', $servicePlan?->max_participants ?? 1) }}"
                            class="input w-full @error('max_participants') input-error @enderror"
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
                            class="input w-full @error('booking_notice_hours') input-error @enderror"
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
                            class="input w-full @error('cancellation_hours') input-error @enderror"
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
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Pricing</h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-base-content/60 mb-4">Leave empty for free services. New member prices are shown on public booking (subdomain).</p>

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
                                    <label class="label-text" for="new_member_prices_{{ $hostCurrencies[0] }}">Service Price</label>
                                </td>
                                @foreach($hostCurrencies as $currency)
                                    <td>
                                        <label class="input input-bordered input-sm flex items-center gap-1">
                                            <span class="text-base-content/60 text-sm">{{ $currencySymbols[$currency] ?? $currency }}</span>
                                            <input type="number" id="new_member_prices_{{ $currency }}" name="new_member_prices[{{ $currency }}]" step="0.01" min="0"
                                                   value="{{ old('new_member_prices.' . $currency, $servicePlan?->new_member_prices[$currency] ?? '') }}"
                                                   class="grow w-full min-w-20" placeholder="0.00">
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                            <tr>
                                <td>
                                    <label class="label-text" for="new_member_deposit_prices_{{ $hostCurrencies[0] }}">Deposit</label>
                                </td>
                                @foreach($hostCurrencies as $currency)
                                    <td>
                                        <label class="input input-bordered input-sm flex items-center gap-1">
                                            <span class="text-base-content/60 text-sm">{{ $currencySymbols[$currency] ?? $currency }}</span>
                                            <input type="number" id="new_member_deposit_prices_{{ $currency }}" name="new_member_deposit_prices[{{ $currency }}]" step="0.01" min="0"
                                                   value="{{ old('new_member_deposit_prices.' . $currency, $servicePlan?->new_member_deposit_prices[$currency] ?? '') }}"
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
                                    <label class="label-text" for="prices_{{ $hostCurrencies[0] }}">Service Price</label>
                                </td>
                                @foreach($hostCurrencies as $currency)
                                    <td>
                                        <label class="input input-bordered input-sm flex items-center gap-1">
                                            <span class="text-base-content/60 text-sm">{{ $currencySymbols[$currency] ?? $currency }}</span>
                                            <input type="number" id="prices_{{ $currency }}" name="prices[{{ $currency }}]" step="0.01" min="0"
                                                   value="{{ old('prices.' . $currency, $servicePlan?->prices[$currency] ?? '') }}"
                                                   class="grow w-full min-w-20" placeholder="0.00">
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                            <tr>
                                <td>
                                    <label class="label-text" for="deposit_prices_{{ $hostCurrencies[0] }}">Deposit</label>
                                </td>
                                @foreach($hostCurrencies as $currency)
                                    <td>
                                        <label class="input input-bordered input-sm flex items-center gap-1">
                                            <span class="text-base-content/60 text-sm">{{ $currencySymbols[$currency] ?? $currency }}</span>
                                            <input type="number" id="deposit_prices_{{ $currency }}" name="deposit_prices[{{ $currency }}]" step="0.01" min="0"
                                                   value="{{ old('deposit_prices.' . $currency, $servicePlan?->deposit_prices[$currency] ?? '') }}"
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
                <p class="text-sm text-base-content/60 mb-4">Set discount percentages for longer billing periods. Clients who commit to longer periods get a better rate.</p>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    @php
                        $billingPeriods = [
                            '1' => '1 Month',
                            '3' => '3 Months',
                            '6' => '6 Months',
                            '9' => '9 Months',
                            '12' => '12 Months',
                        ];
                        $defaultDiscounts = ['1' => 0, '3' => 5, '6' => 10, '9' => 15, '12' => 20];
                    @endphp
                    @foreach($billingPeriods as $months => $label)
                    <div>
                        <label class="label-text text-sm" for="billing_discounts_{{ $months }}">{{ $label }}</label>
                        <label class="input input-bordered input-sm flex items-center gap-1 mt-1">
                            <input type="number" id="billing_discounts_{{ $months }}" name="billing_discounts[{{ $months }}]" step="1" min="0" max="100"
                                   value="{{ old('billing_discounts.' . $months, $servicePlan?->billing_discounts[$months] ?? $defaultDiscounts[$months]) }}"
                                   class="grow w-full" placeholder="0">
                            <span class="text-base-content/60 text-sm">%</span>
                        </label>
                    </div>
                    @endforeach
                </div>
                <p class="text-xs text-base-content/50 mt-3">
                    <span class="icon-[tabler--info-circle] size-3 align-middle"></span>
                    Example: A $100/month service with 10% discount for 6 months = $90/month ($540 total instead of $600)
                </p>
            </div>
        </div>

        {{-- Staff Members --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Assigned Staff Member</h3>
            </div>
            <div class="card-body">
                @if($staffMembers->isEmpty())
                <p class="text-base-content/60">No team members available. <a href="{{ route('settings.team.users') }}" class="link link-primary">Add team members</a> first.</p>
                @else
                <p class="text-sm text-base-content/60 mb-4">Select which staff members can offer this service.</p>

                {{-- Search and Selection Info --}}
                <div class="flex flex-col sm:flex-row gap-3 mb-4">
                    <div class="relative flex-1">
                        <span class="icon-[tabler--search] absolute left-3 top-1/2 -translate-y-1/2 size-4 text-base-content/50"></span>
                        <input type="text" id="staff-search" placeholder="Search staff members..."
                            class="input input-sm w-full pl-9" autocomplete="off">
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="staff-selected-count" class="badge badge-primary badge-sm">0 selected</span>
                        <button type="button" id="staff-clear-all" class="btn btn-ghost btn-xs hidden">Clear all</button>
                    </div>
                </div>

                {{-- Staff Members List --}}
                <div id="staff-members-list" class="space-y-2 max-h-64 overflow-y-auto">
                    @foreach($staffMembers as $member)
                    <label class="staff-member-item flex items-center gap-2 p-2 rounded-lg border border-base-content/10 cursor-pointer hover:bg-base-200"
                        data-name="{{ strtolower($member->name) }}" data-email="{{ strtolower($member->email) }}" data-role="{{ strtolower($member->pivot->role ?? $member->role) }}">
                        <input type="checkbox" name="staff_member_ids[]" value="{{ $member->id }}"
                            class="checkbox checkbox-primary checkbox-sm staff-checkbox"
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
                <p id="staff-no-results" class="text-base-content/50 text-sm py-2 text-center hidden">No staff found.</p>
                @endif
            </div>
        </div>

        {{-- Questionnaire Attachments --}}
        @if(isset($questionnaires) && $questionnaires->count() > 0)
            @include('host.partials._questionnaire-attachments', [
                'questionnaires' => $questionnaires,
                'attachments' => $servicePlan?->questionnaireAttachments ?? collect()
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
                @if($servicePlan?->image_path)
                <div class="mb-4">
                    <img src="{{ $servicePlan->image_url }}" alt="{{ $servicePlan->name }}" class="w-full h-32 object-cover rounded-lg">
                </div>
                @endif
                <input type="file" id="image" name="image"
                    class="file-input file-input-bordered w-full @error('image') input-error @enderror"
                    accept="image/jpeg,image/png,image/jpg,image/webp">
                <p class="text-xs text-base-content/60 mt-1">JPG, PNG or WebP. Max 2MB.</p>
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

        {{-- Status --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Status</h3>
            </div>
            <div class="card-body space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1"
                        class="toggle toggle-primary"
                        {{ old('is_active', $servicePlan?->is_active ?? true) ? 'checked' : '' }}>
                    <div>
                        <span class="font-medium">Active</span>
                        <p class="text-xs text-base-content/60">Service can be booked</p>
                    </div>
                </label>
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

    // Staff member search and selection
    (function() {
        const searchInput = document.getElementById('staff-search');
        const staffList = document.getElementById('staff-members-list');
        const noResults = document.getElementById('staff-no-results');
        const selectedCount = document.getElementById('staff-selected-count');
        const clearAllBtn = document.getElementById('staff-clear-all');

        if (!searchInput || !staffList) return;

        const staffItems = staffList.querySelectorAll('.staff-member-item');
        const checkboxes = staffList.querySelectorAll('.staff-checkbox');

        function updateSelectedCount() {
            const count = staffList.querySelectorAll('.staff-checkbox:checked').length;
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
