<div class="space-y-6">
    {{-- Basic Info --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <h3 class="card-title">{{ $trans['space_rentals.basic_info'] ?? 'Basic Information' }}</h3>
        </div>
        <div class="card-body space-y-4">
            <div>
                <label class="label-text" for="name">{{ $trans['field.name'] ?? 'Name' }} <span class="text-error">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $config?->name) }}"
                    class="input w-full @error('name') is-invalid @enderror"
                    placeholder="{{ $trans['space_rentals.name_placeholder'] ?? 'e.g., Main Studio, Room A' }}"
                    required minlength="2" maxlength="255">
                <span class="error-message text-error text-sm mt-1 hidden">Please enter a name (min 2 characters)</span>
                @error('name')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label-text" for="description">{{ $trans['field.description'] ?? 'Description' }}</label>
                <textarea name="description" id="description" rows="3"
                    class="textarea w-full @error('description') is-invalid @enderror"
                    placeholder="{{ $trans['space_rentals.description_placeholder'] ?? 'Describe the space, what it includes, and any special features' }}">{{ old('description', $config?->description) }}</textarea>
                @error('description')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            @php
                $locationOptions = $locations->pluck('name', 'id')->toArray();
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="label-text" for="location_id">{{ $trans['field.location'] ?? 'Location' }} <span class="text-error">*</span></label>
                    <x-studio-select name="location_id" :options="$locationOptions" :selected="$config?->location_id" placeholder="{{ $trans['common.select'] ?? 'Select' }}..." :required="true" />
                    @error('location_id')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="type-field" class="relative z-10">
                    <label class="label-text" for="rentable_type">{{ $trans['space_rentals.rental_type'] ?? 'What to Rent' }} <span class="text-error">*</span></label>
                    <x-studio-select name="rentable_type" :options="$types" :selected="$config?->rentable_type" placeholder="{{ $trans['common.select'] ?? 'Select' }}..." :required="true" />
                    <p class="text-xs text-base-content/60 mt-1" id="type-hint">{{ $trans['space_rentals.select_location_first'] ?? 'Select a location first' }}</p>
                    @error('rentable_type')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="hidden" id="room-field">
                    <label class="label-text" for="room_id">{{ $trans['field.room'] ?? 'Room' }} <span class="text-error">*</span></label>
                    <select name="room_id" id="room_id" class="select w-full @error('room_id') input-error @enderror">
                        <option value="">{{ $trans['common.select_room'] ?? 'Select a room' }}...</option>
                    </select>
                    <p class="text-xs text-warning mt-1 hidden" id="no-rooms-hint">{{ $trans['space_rentals.no_rooms_hint'] ?? 'No rooms found for this location' }}</p>
                    @error('room_id')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Pricing --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <h3 class="card-title">{{ $trans['space_rentals.pricing'] ?? 'Pricing' }}</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-studio-currency-inputs
                        name="hourly_rates"
                        :values="$config?->hourly_rates ?? []"
                        label="Hourly Rate"
                        help="Rate charged per hour of rental"
                        :required="true"
                    />

                    <x-studio-currency-inputs
                        name="deposit_rates"
                        :values="$config?->deposit_rates ?? []"
                        label="Security Deposit"
                        help="Optional refundable deposit"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="minimum_hours">{{ $trans['space_rentals.min_hours'] ?? 'Minimum Hours' }} <span class="text-error">*</span></label>
                        <input type="number" min="1" name="minimum_hours" id="minimum_hours"
                            value="{{ old('minimum_hours', $config?->minimum_hours ?? 2) }}"
                            class="input w-full @error('minimum_hours') is-invalid @enderror" required>
                        @error('minimum_hours')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="maximum_hours">{{ $trans['space_rentals.max_hours'] ?? 'Maximum Hours' }} <span class="text-base-content/50 font-normal text-xs">({{ $trans['common.optional'] ?? 'Optional' }})</span></label>
                        <input type="number" min="1" name="maximum_hours" id="maximum_hours"
                            value="{{ old('maximum_hours', $config?->maximum_hours) }}"
                            class="input w-full @error('maximum_hours') input-error @enderror">
                        @error('maximum_hours')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Allowed Purposes --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <h3 class="card-title">{{ $trans['space_rentals.allowed_purposes'] ?? 'Allowed Purposes' }}</h3>
        </div>
        <div class="card-body">
            <p class="text-sm text-base-content/60 mb-3">{{ $trans['space_rentals.purposes_hint'] ?? 'Select what this space can be used for. Leave empty to allow all purposes.' }}</p>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                @foreach($purposes as $key => $label)
                <label class="custom-option flex flex-row items-center gap-2 px-3 py-2 cursor-pointer rounded-lg border border-base-200 hover:bg-base-200/50 transition-colors">
                    <input type="checkbox" name="allowed_purposes[]" value="{{ $key }}"
                        class="checkbox checkbox-primary checkbox-sm"
                        {{ in_array($key, old('allowed_purposes', $config?->allowed_purposes ?? [])) ? 'checked' : '' }}>
                    <span class="icon-[tabler--{{ \App\Models\SpaceRentalConfig::getPurposeIcon($key) }}] size-4 text-base-content/60"></span>
                    <span class="label-text text-sm">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Settings --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <h3 class="card-title">{{ $trans['space_rentals.settings'] ?? 'Settings' }}</h3>
        </div>
        <div class="card-body space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label-text" for="setup_time_minutes">{{ $trans['space_rentals.setup_time'] ?? 'Setup Time (minutes)' }}</label>
                    <input type="number" min="0" step="5" name="setup_time_minutes" id="setup_time_minutes"
                        value="{{ old('setup_time_minutes', $config?->setup_time_minutes ?? 0) }}"
                        class="input w-full">
                    <p class="text-xs text-base-content/60 mt-1">{{ $trans['space_rentals.setup_hint'] ?? 'Buffer before rental starts' }}</p>
                </div>
                <div>
                    <label class="label-text" for="cleanup_time_minutes">{{ $trans['space_rentals.cleanup_time'] ?? 'Cleanup Time (minutes)' }}</label>
                    <input type="number" min="0" step="5" name="cleanup_time_minutes" id="cleanup_time_minutes"
                        value="{{ old('cleanup_time_minutes', $config?->cleanup_time_minutes ?? 15) }}"
                        class="input w-full">
                    <p class="text-xs text-base-content/60 mt-1">{{ $trans['space_rentals.cleanup_hint'] ?? 'Buffer after rental ends' }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <span class="font-medium">{{ $trans['space_rentals.require_waiver'] ?? 'Require Liability Waiver' }}</span>
                    <p class="text-xs text-base-content/60">{{ $trans['space_rentals.waiver_desc'] ?? 'Client must sign a waiver before the rental starts' }}</p>
                </div>
                <label class="switch switch-primary">
                    <input type="checkbox" name="requires_waiver" value="1"
                        {{ old('requires_waiver', $config?->requires_waiver ?? true) ? 'checked' : '' }} />
                    <span class="switch-indicator"></span>
                </label>
            </div>

            {{-- Waiver Document Upload --}}
            <div id="waiver-upload">
                <label class="label-text">{{ $trans['space_rentals.waiver_document'] ?? 'Waiver Document (PDF)' }}</label>
                <input type="file" name="waiver_document" id="waiver_document" accept=".pdf" class="hidden">

                <div id="waiver-preview-wrapper" class="mt-1 {{ $config?->waiver_document_path ? '' : 'hidden' }}">
                    <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-lg">
                        <span class="icon-[tabler--file-type-pdf] size-6 text-base-content/60 shrink-0"></span>
                        <span class="text-sm flex-1 truncate" id="waiver-file-name">{{ $config?->waiver_document_path ? basename($config->waiver_document_path) : '' }}</span>
                        <button type="button" class="btn btn-ghost btn-xs btn-circle" onclick="document.getElementById('waiver_document').click()">
                            <span class="icon-[tabler--edit] size-4"></span>
                        </button>
                        <button type="button" class="btn btn-ghost btn-xs btn-circle" onclick="removeWaiver()">
                            <span class="icon-[tabler--x] size-4"></span>
                        </button>
                    </div>
                </div>

                <div id="waiver-upload-zone" class="border-2 border-dashed border-base-content/20 rounded-xl p-6 text-center cursor-pointer hover:border-primary/50 hover:bg-primary/5 transition-colors mt-1 {{ $config?->waiver_document_path ? 'hidden' : '' }}"
                     onclick="document.getElementById('waiver_document').click()"
                     ondragover="event.preventDefault(); this.classList.add('border-primary', 'bg-primary/5')"
                     ondragleave="this.classList.remove('border-primary', 'bg-primary/5')"
                     ondrop="event.preventDefault(); this.classList.remove('border-primary', 'bg-primary/5'); handleWaiverDrop(event)">
                    <span class="icon-[tabler--file-upload] size-8 text-base-content/30 mx-auto block mb-2"></span>
                    <p class="text-sm font-medium text-base-content/70">Click to upload or drag & drop</p>
                    <p class="text-xs text-base-content/50 mt-1">PDF only. Max 5MB.</p>
                </div>

                @error('waiver_document')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label-text" for="rules">{{ $trans['space_rentals.rules'] ?? 'Space Rules & Guidelines' }}</label>
                <textarea name="rules" id="rules" rows="3"
                    class="textarea w-full"
                    placeholder="{{ $trans['space_rentals.rules_placeholder'] ?? 'List any rules, restrictions, or important information for renters' }}">{{ old('rules', $config?->rules) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Status --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <h3 class="card-title">Status</h3>
        </div>
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <span class="font-medium">{{ $trans['space_rentals.active_for_booking'] ?? 'Active for Booking' }}</span>
                    <p class="text-xs text-base-content/60">{{ $trans['space_rentals.active_hint'] ?? 'When disabled, no new rentals can be created' }}</p>
                </div>
                <label class="switch switch-primary">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $config?->is_active ?? true) ? 'checked' : '' }} />
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
                {{ $config ? ($trans['btn.update'] ?? 'Update Rentable Space') : ($trans['btn.create'] ?? 'Create Rentable Space') }}
            </button>
            <a href="{{ route('catalog.index', ['tab' => 'rental-spaces']) }}" class="btn btn-ghost w-full">
                {{ $trans['btn.cancel'] ?? 'Cancel' }}
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const locationSelect = document.getElementById('location_id');
    const typeSelect = document.getElementById('rentable_type');
    const typeHint = document.getElementById('type-hint');
    const roomField = document.getElementById('room-field');
    const roomSelect = document.getElementById('room_id');
    const noRoomsHint = document.getElementById('no-rooms-hint');

    const roomsByLocation = @json($locations->mapWithKeys(function($location) {
        return [$location->id => $location->rooms->map(function($room) {
            return ['id' => $room->id, 'name' => $room->name];
        })];
    }));

    const preselectedRoomId = {{ old('room_id', $config?->room_id ?? 'null') }};

    // Helper to get HSSelect instance
    function getHSSelect(el) {
        return window.HSStaticMethods && el.closest('[data-select]')
            ? window.HSSelect.getInstance(el.closest('[data-select]') || el)
            : null;
    }

    function updateTypeOptions() {
        const locationId = locationSelect.value;
        if (!locationId) {
            typeHint.classList.remove('hidden');
            roomField.classList.add('hidden');
            return;
        }
        typeHint.classList.add('hidden');
        updateRoomField();
    }

    function updateRoomField() {
        if (typeSelect.value === 'room' && locationSelect.value) {
            roomField.classList.remove('hidden');
            populateRooms(locationSelect.value);
        } else {
            roomField.classList.add('hidden');
        }
    }

    function populateRooms(locationId) {
        const rooms = roomsByLocation[locationId] || [];
        roomSelect.innerHTML = '<option value="">{{ $trans['common.select_room'] ?? 'Select a room' }}...</option>';
        if (rooms.length === 0) {
            if (noRoomsHint) noRoomsHint.classList.remove('hidden');
            return;
        }
        if (noRoomsHint) noRoomsHint.classList.add('hidden');
        rooms.forEach(function(room) {
            const option = document.createElement('option');
            option.value = room.id;
            option.textContent = room.name;
            if (room.id == preselectedRoomId) option.selected = true;
            roomSelect.appendChild(option);
        });

    }

    locationSelect.addEventListener('change', updateTypeOptions);
    typeSelect.addEventListener('change', updateRoomField);
    updateTypeOptions();

    // Waiver file upload
    var waiverInput = document.getElementById('waiver_document');
    var waiverPreview = document.getElementById('waiver-preview-wrapper');
    var waiverZone = document.getElementById('waiver-upload-zone');
    var waiverFileName = document.getElementById('waiver-file-name');

    if (waiverInput) {
        waiverInput.addEventListener('change', function() {
            if (this.files && this.files[0]) showWaiverPreview(this.files[0]);
        });
    }

    window.handleWaiverDrop = function(e) {
        var files = e.dataTransfer.files;
        if (files.length > 0 && files[0].type === 'application/pdf') {
            waiverInput.files = files;
            showWaiverPreview(files[0]);
        }
    };

    window.removeWaiver = function() {
        waiverInput.value = '';
        waiverPreview.classList.add('hidden');
        waiverZone.classList.remove('hidden');
    };

    function showWaiverPreview(file) {
        if (file.type !== 'application/pdf') { alert('Please upload a PDF file.'); return; }
        if (file.size > 5 * 1024 * 1024) { alert('File size must be under 5MB.'); return; }
        waiverFileName.textContent = file.name;
        waiverPreview.classList.remove('hidden');
        waiverZone.classList.add('hidden');
    }
});
</script>
@endpush
