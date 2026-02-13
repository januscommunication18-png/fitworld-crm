@php
    $classPlan = $classPlan ?? null;
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
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="default_price">Default Price ($)</label>
                        <input type="number" id="default_price" name="default_price"
                            value="{{ old('default_price', $classPlan?->default_price) }}"
                            class="input w-full @error('default_price') input-error @enderror"
                            min="0" max="9999.99" step="0.01"
                            placeholder="0.00">
                        <p class="text-xs text-base-content/60 mt-1">Leave empty for free classes</p>
                        @error('default_price')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="drop_in_price">Drop-in Price ($)</label>
                        <input type="number" id="drop_in_price" name="drop_in_price"
                            value="{{ old('drop_in_price', $classPlan?->drop_in_price) }}"
                            class="input w-full @error('drop_in_price') input-error @enderror"
                            min="0" max="9999.99" step="0.01"
                            placeholder="0.00">
                        <p class="text-xs text-base-content/60 mt-1">Price for walk-ins without a membership</p>
                        @error('drop_in_price')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
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
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Image --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Image</h3>
            </div>
            <div class="card-body">
                @if($classPlan?->image_path)
                <div class="mb-4">
                    <img src="{{ $classPlan->image_url }}" alt="{{ $classPlan->name }}" class="w-full h-32 object-cover rounded-lg">
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
