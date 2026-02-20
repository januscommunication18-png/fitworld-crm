@php
    $servicePlan = $servicePlan ?? null;
    $assignedInstructorIds = $assignedInstructorIds ?? [];
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
            <div class="card-body space-y-4">
                {{-- Service Price --}}
                <div>
                    <label class="label-text font-medium">Service Price</label>
                    <p class="text-xs text-base-content/60 mb-2">Leave empty for free services</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($hostCurrencies as $currency)
                            <div class="form-control">
                                <label class="label py-1 justify-start gap-2">
                                    <span class="label-text text-sm">{{ $currency }}</span>
                                    @if($currency === $defaultCurrency)
                                        <span class="badge badge-primary badge-xs">Default</span>
                                    @endif
                                </label>
                                <label class="input input-bordered flex items-center gap-2">
                                    <span class="text-base-content/60">{{ $currencySymbols[$currency] ?? $currency }}</span>
                                    <input type="number" name="prices[{{ $currency }}]" step="0.01" min="0"
                                           value="{{ old('prices.' . $currency, $servicePlan?->prices[$currency] ?? '') }}"
                                           class="grow w-full" placeholder="0.00">
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Deposit Amount --}}
                <div>
                    <label class="label-text font-medium">Deposit Amount</label>
                    <p class="text-xs text-base-content/60 mb-2">Required deposit at booking (optional)</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($hostCurrencies as $currency)
                            <div class="form-control">
                                <label class="label py-1 justify-start gap-2">
                                    <span class="label-text text-sm">{{ $currency }}</span>
                                </label>
                                <label class="input input-bordered flex items-center gap-2">
                                    <span class="text-base-content/60">{{ $currencySymbols[$currency] ?? $currency }}</span>
                                    <input type="number" name="deposit_prices[{{ $currency }}]" step="0.01" min="0"
                                           value="{{ old('deposit_prices.' . $currency, $servicePlan?->deposit_prices[$currency] ?? '') }}"
                                           class="grow w-full" placeholder="0.00">
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Instructors --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Assigned Instructors</h3>
            </div>
            <div class="card-body">
                @if($instructors->isEmpty())
                <p class="text-base-content/60">No active instructors available. <a href="{{ route('settings.team.instructors') }}" class="link link-primary">Add instructors</a> first.</p>
                @else
                <p class="text-sm text-base-content/60 mb-4">Select which instructors can offer this service.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($instructors as $instructor)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-base-content/10 cursor-pointer hover:bg-base-200">
                        <input type="checkbox" name="instructor_ids[]" value="{{ $instructor->id }}"
                            class="checkbox checkbox-primary checkbox-sm"
                            {{ in_array($instructor->id, old('instructor_ids', $assignedInstructorIds)) ? 'checked' : '' }}>
                        <div class="flex items-center gap-3 flex-1">
                            @if($instructor->photo_url)
                            <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                            <div class="avatar avatar-placeholder">
                                <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold text-sm">
                                    {{ strtoupper(substr($instructor->name, 0, 2)) }}
                                </div>
                            </div>
                            @endif
                            <div>
                                <div class="font-medium">{{ $instructor->name }}</div>
                                @if($instructor->specialties)
                                <div class="text-xs text-base-content/60">{{ implode(', ', array_slice($instructor->specialties, 0, 2)) }}</div>
                                @endif
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
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
</script>
@endpush
