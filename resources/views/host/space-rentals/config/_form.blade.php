<div class="max-w-4xl mx-auto space-y-6">
    {{-- Basic Info Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">{{ $trans['space_rentals.basic_info'] ?? 'Basic Information' }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                {{-- Name --}}
                <div class="form-control md:col-span-2">
                    <label for="name" class="label">
                        <span class="label-text">{{ $trans['field.name'] ?? 'Name' }} <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $config?->name) }}"
                        class="input input-bordered @error('name') input-error @enderror"
                        placeholder="{{ $trans['space_rentals.name_placeholder'] ?? 'e.g., Main Studio, Room A' }}" required>
                    @error('name')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                {{-- Location (always shown first) --}}
                <div class="form-control">
                    <label for="location_id" class="label">
                        <span class="label-text">{{ $trans['field.location'] ?? 'Location' }} <span class="text-error">*</span></span>
                    </label>
                    <select name="location_id" id="location_id" class="select select-bordered @error('location_id') select-error @enderror" required>
                        <option value="">{{ $trans['common.select'] ?? 'Select' }}...</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}"
                                data-has-rooms="{{ $location->rooms->count() > 0 ? '1' : '0' }}"
                                {{ old('location_id', $config?->location_id) == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                {{-- Rentable Type (shown after location is selected) --}}
                <div class="form-control" id="type-field">
                    <label for="rentable_type" class="label">
                        <span class="label-text">{{ $trans['space_rentals.rental_type'] ?? 'What to Rent' }} <span class="text-error">*</span></span>
                    </label>
                    <select name="rentable_type" id="rentable_type" class="select select-bordered @error('rentable_type') select-error @enderror" required>
                        <option value="">{{ $trans['common.select'] ?? 'Select' }}...</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ old('rentable_type', $config?->rentable_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <label class="label" id="type-hint">
                        <span class="label-text-alt text-base-content/60">{{ $trans['space_rentals.select_location_first'] ?? 'Select a location first' }}</span>
                    </label>
                    @error('rentable_type')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                {{-- Room (shown when room type selected) --}}
                <div class="form-control hidden" id="room-field">
                    <label for="room_id" class="label">
                        <span class="label-text">{{ $trans['field.room'] ?? 'Room' }} <span class="text-error">*</span></span>
                    </label>
                    <select name="room_id" id="room_id" class="select select-bordered @error('room_id') select-error @enderror">
                        <option value="">{{ $trans['common.select_room'] ?? 'Select a room' }}...</option>
                    </select>
                    <label class="label" id="no-rooms-hint" class="hidden">
                        <span class="label-text-alt text-warning">{{ $trans['space_rentals.no_rooms_hint'] ?? 'No rooms found for this location' }}</span>
                    </label>
                    @error('room_id')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="form-control md:col-span-2">
                    <label for="description" class="label">
                        <span class="label-text">{{ $trans['field.description'] ?? 'Description' }}</span>
                    </label>
                    <textarea name="description" id="description" rows="3"
                        class="textarea textarea-bordered @error('description') textarea-error @enderror"
                        placeholder="{{ $trans['space_rentals.description_placeholder'] ?? 'Describe the space, what it includes, and any special features' }}">{{ old('description', $config?->description) }}</textarea>
                    @error('description')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Pricing Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">{{ $trans['space_rentals.pricing'] ?? 'Pricing' }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                {{-- Hourly Rates (Multi-currency) --}}
                <div class="form-control md:col-span-2">
                    <label class="label">
                        <span class="label-text">{{ $trans['field.hourly_rate'] ?? 'Hourly Rate' }} <span class="text-error">*</span></span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($hostCurrencies as $currency)
                        <div class="join w-full">
                            <span class="join-item flex items-center justify-center w-12 bg-base-200 text-sm font-medium">
                                {{ $currencySymbols[$currency] ?? $currency }}
                            </span>
                            <input type="number" step="0.01" min="0" name="hourly_rates[{{ $currency }}]"
                                value="{{ old("hourly_rates.$currency", $config?->hourly_rates[$currency] ?? '') }}"
                                class="input input-bordered join-item flex-1 @error("hourly_rates.$currency") input-error @enderror"
                                placeholder="0.00">
                        </div>
                        @endforeach
                    </div>
                    @error('hourly_rates')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                {{-- Deposit Rates (Multi-currency) --}}
                <div class="form-control md:col-span-2">
                    <label class="label">
                        <span class="label-text">{{ $trans['space_rentals.deposit'] ?? 'Security Deposit' }}</span>
                        <span class="label-text-alt text-base-content/60">{{ $trans['space_rentals.deposit_hint'] ?? 'Optional, refundable' }}</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($hostCurrencies as $currency)
                        <div class="join w-full">
                            <span class="join-item flex items-center justify-center w-12 bg-base-200 text-sm font-medium">
                                {{ $currencySymbols[$currency] ?? $currency }}
                            </span>
                            <input type="number" step="0.01" min="0" name="deposit_rates[{{ $currency }}]"
                                value="{{ old("deposit_rates.$currency", $config?->deposit_rates[$currency] ?? '') }}"
                                class="input input-bordered join-item flex-1"
                                placeholder="0.00">
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Minimum Hours --}}
                <div class="form-control">
                    <label for="minimum_hours" class="label">
                        <span class="label-text">{{ $trans['space_rentals.min_hours'] ?? 'Minimum Hours' }} <span class="text-error">*</span></span>
                    </label>
                    <input type="number" min="1" name="minimum_hours" id="minimum_hours"
                        value="{{ old('minimum_hours', $config?->minimum_hours ?? 2) }}"
                        class="input input-bordered @error('minimum_hours') input-error @enderror" required>
                    @error('minimum_hours')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                {{-- Maximum Hours --}}
                <div class="form-control">
                    <label for="maximum_hours" class="label">
                        <span class="label-text">{{ $trans['space_rentals.max_hours'] ?? 'Maximum Hours' }}</span>
                        <span class="label-text-alt text-base-content/60">{{ $trans['common.optional'] ?? 'Optional' }}</span>
                    </label>
                    <input type="number" min="1" name="maximum_hours" id="maximum_hours"
                        value="{{ old('maximum_hours', $config?->maximum_hours) }}"
                        class="input input-bordered @error('maximum_hours') input-error @enderror">
                    @error('maximum_hours')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Allowed Purposes Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">{{ $trans['space_rentals.allowed_purposes'] ?? 'Allowed Purposes' }}</h2>
            <p class="text-sm text-base-content/60">{{ $trans['space_rentals.purposes_hint'] ?? 'Select what this space can be used for. Leave empty to allow all purposes.' }}</p>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-4">
                @foreach($purposes as $key => $label)
                <label class="flex items-center gap-3 p-3 rounded-lg border border-base-200 cursor-pointer hover:bg-base-50 transition-colors">
                    <input type="checkbox" name="allowed_purposes[]" value="{{ $key }}"
                        class="checkbox checkbox-sm checkbox-primary"
                        {{ in_array($key, old('allowed_purposes', $config?->allowed_purposes ?? [])) ? 'checked' : '' }}>
                    <div class="flex items-center gap-2">
                        <span class="icon-[tabler--{{ \App\Models\SpaceRentalConfig::getPurposeIcon($key) }}] size-5 text-base-content/60"></span>
                        <span class="text-sm">{{ $label }}</span>
                    </div>
                </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Buffer Times & Waiver Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-lg">{{ $trans['space_rentals.settings'] ?? 'Settings' }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                {{-- Setup Time --}}
                <div class="form-control">
                    <label for="setup_time_minutes" class="label">
                        <span class="label-text">{{ $trans['space_rentals.setup_time'] ?? 'Setup Time (minutes)' }}</span>
                    </label>
                    <input type="number" min="0" step="5" name="setup_time_minutes" id="setup_time_minutes"
                        value="{{ old('setup_time_minutes', $config?->setup_time_minutes ?? 0) }}"
                        class="input input-bordered">
                    <label class="label"><span class="label-text-alt text-base-content/60">{{ $trans['space_rentals.setup_hint'] ?? 'Buffer before rental starts' }}</span></label>
                </div>

                {{-- Cleanup Time --}}
                <div class="form-control">
                    <label for="cleanup_time_minutes" class="label">
                        <span class="label-text">{{ $trans['space_rentals.cleanup_time'] ?? 'Cleanup Time (minutes)' }}</span>
                    </label>
                    <input type="number" min="0" step="5" name="cleanup_time_minutes" id="cleanup_time_minutes"
                        value="{{ old('cleanup_time_minutes', $config?->cleanup_time_minutes ?? 15) }}"
                        class="input input-bordered">
                    <label class="label"><span class="label-text-alt text-base-content/60">{{ $trans['space_rentals.cleanup_hint'] ?? 'Buffer after rental ends' }}</span></label>
                </div>

                {{-- Requires Waiver --}}
                <div class="form-control md:col-span-2">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="requires_waiver" value="1"
                            class="toggle toggle-primary"
                            {{ old('requires_waiver', $config?->requires_waiver ?? true) ? 'checked' : '' }}>
                        <div>
                            <span class="label-text font-medium">{{ $trans['space_rentals.require_waiver'] ?? 'Require Liability Waiver' }}</span>
                            <p class="text-xs text-base-content/60">{{ $trans['space_rentals.waiver_desc'] ?? 'Client must sign a waiver before the rental starts' }}</p>
                        </div>
                    </label>
                </div>

                {{-- Waiver Document Upload --}}
                <div class="form-control md:col-span-2" id="waiver-upload">
                    <label for="waiver_document" class="label">
                        <span class="label-text">{{ $trans['space_rentals.waiver_document'] ?? 'Waiver Document (PDF)' }}</span>
                    </label>
                    @if($config?->waiver_document_path)
                        <div class="flex items-center gap-3 mb-2 p-3 bg-base-200 rounded-lg">
                            <span class="icon-[tabler--file-certificate] size-5 text-primary"></span>
                            <span class="text-sm">{{ $trans['space_rentals.current_waiver'] ?? 'Current waiver uploaded' }}</span>
                        </div>
                    @endif
                    <input type="file" name="waiver_document" id="waiver_document" accept=".pdf"
                        class="file-input file-input-bordered w-full">
                    @error('waiver_document')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                {{-- Rules --}}
                <div class="form-control md:col-span-2">
                    <label for="rules" class="label">
                        <span class="label-text">{{ $trans['space_rentals.rules'] ?? 'Space Rules & Guidelines' }}</span>
                    </label>
                    <textarea name="rules" id="rules" rows="4"
                        class="textarea textarea-bordered"
                        placeholder="{{ $trans['space_rentals.rules_placeholder'] ?? 'List any rules, restrictions, or important information for renters' }}">{{ old('rules', $config?->rules) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Status & Actions --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1"
                        class="toggle toggle-success"
                        {{ old('is_active', $config?->is_active ?? true) ? 'checked' : '' }}>
                    <div>
                        <span class="label-text font-medium">{{ $trans['space_rentals.active_for_booking'] ?? 'Active for Booking' }}</span>
                        <p class="text-xs text-base-content/60">{{ $trans['space_rentals.active_hint'] ?? 'When disabled, no new rentals can be created' }}</p>
                    </div>
                </label>

                <div class="flex items-center gap-3">
                    <a href="{{ route('space-rentals.config.index') }}" class="btn btn-ghost">
                        {{ $trans['btn.cancel'] ?? 'Cancel' }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--check] size-5"></span>
                        {{ $config ? ($trans['btn.update'] ?? 'Update') : ($trans['btn.create'] ?? 'Create') }}
                    </button>
                </div>
            </div>
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

    // Room data by location
    const roomsByLocation = @json($locations->mapWithKeys(function($location) {
        return [$location->id => $location->rooms->map(function($room) {
            return ['id' => $room->id, 'name' => $room->name];
        })];
    }));

    // Preselected values for edit mode
    const preselectedRoomId = {{ old('room_id', $config?->room_id ?? 'null') }};

    function updateTypeOptions() {
        const locationId = locationSelect.value;

        if (!locationId) {
            // No location selected - disable type select
            typeSelect.disabled = true;
            typeSelect.value = '';
            typeHint.classList.remove('hidden');
            roomField.classList.add('hidden');
            return;
        }

        // Location selected - enable type select
        typeSelect.disabled = false;
        typeHint.classList.add('hidden');

        // Check if location has rooms
        const rooms = roomsByLocation[locationId] || [];
        const roomOption = typeSelect.querySelector('option[value="room"]');

        if (roomOption) {
            if (rooms.length === 0) {
                // No rooms - disable room option
                roomOption.disabled = true;
                roomOption.textContent = '{{ $trans['space_rentals.room_no_rooms'] ?? 'Specific Room (no rooms available)' }}';
                // If room was selected, switch to location
                if (typeSelect.value === 'room') {
                    typeSelect.value = 'location';
                }
            } else {
                // Has rooms - enable room option
                roomOption.disabled = false;
                roomOption.textContent = '{{ $types['room'] ?? 'Specific Room' }}';
            }
        }

        updateRoomField();
    }

    function updateRoomField() {
        const type = typeSelect.value;
        const locationId = locationSelect.value;

        if (type === 'room' && locationId) {
            roomField.classList.remove('hidden');
            populateRooms(locationId);
        } else {
            roomField.classList.add('hidden');
            roomSelect.value = '';
        }
    }

    function populateRooms(locationId) {
        const rooms = roomsByLocation[locationId] || [];

        // Clear existing options
        roomSelect.innerHTML = '<option value="">{{ $trans['common.select_room'] ?? 'Select a room' }}...</option>';

        if (rooms.length === 0) {
            if (noRoomsHint) noRoomsHint.classList.remove('hidden');
            return;
        }

        if (noRoomsHint) noRoomsHint.classList.add('hidden');

        // Add room options
        rooms.forEach(function(room) {
            const option = document.createElement('option');
            option.value = room.id;
            option.textContent = room.name;
            if (room.id == preselectedRoomId) {
                option.selected = true;
            }
            roomSelect.appendChild(option);
        });
    }

    // Event listeners
    locationSelect.addEventListener('change', updateTypeOptions);
    typeSelect.addEventListener('change', updateRoomField);

    // Initial state
    updateTypeOptions();
});
</script>
@endpush
