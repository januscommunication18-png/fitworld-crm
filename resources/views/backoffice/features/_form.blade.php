@php
    $feature = $feature ?? null;
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="name">Feature Name</label>
                        <input type="text" id="name" name="name"
                            value="{{ old('name', $feature?->name) }}"
                            class="input w-full @error('name') input-error @enderror"
                            placeholder="e.g., Google Calendar Sync"
                            required>
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="slug">Slug</label>
                        <input type="text" id="slug" name="slug"
                            value="{{ old('slug', $feature?->slug) }}"
                            class="input w-full @error('slug') input-error @enderror"
                            placeholder="e.g., google-calendar-sync">
                        <p class="text-xs text-base-content/60 mt-1">Leave empty to auto-generate from name</p>
                        @error('slug')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="label-text" for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="textarea w-full @error('description') input-error @enderror"
                        placeholder="Brief description of this feature...">{{ old('description', $feature?->description) }}</textarea>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="icon">Icon</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 icon-[tabler--{{ old('icon', $feature?->icon ?? 'puzzle') }}] size-5 text-base-content/40"></span>
                            <input type="text" id="icon" name="icon"
                                value="{{ old('icon', $feature?->icon ?? 'puzzle') }}"
                                class="input w-full pl-10 @error('icon') input-error @enderror"
                                placeholder="e.g., brand-google">
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">
                            <a href="https://tabler.io/icons" target="_blank" class="link link-primary">Browse Tabler Icons</a>
                        </p>
                        @error('icon')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order"
                            value="{{ old('sort_order', $feature?->sort_order ?? 0) }}"
                            class="input w-full @error('sort_order') input-error @enderror"
                            min="0">
                        <p class="text-xs text-base-content/60 mt-1">Lower numbers appear first</p>
                        @error('sort_order')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Classification --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Classification</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="type">Type</label>
                        <select id="type" name="type" class="select w-full @error('type') input-error @enderror" required>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type', $feature?->type ?? 'free') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-base-content/60 mt-1">Premium features require plan access or manual grant</p>
                        @error('type')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="category">Category</label>
                        <select id="category" name="category" class="select w-full @error('category') input-error @enderror" required>
                            @foreach($categories as $value => $label)
                                <option value="{{ $value }}" {{ old('category', $feature?->category ?? 'tools') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Configuration Schema --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Configuration</h3>
                <span class="badge badge-soft badge-neutral badge-sm">Optional</span>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="config_schema">Config Schema (JSON)</label>
                    <textarea id="config_schema" name="config_schema" rows="5"
                        class="textarea w-full font-mono text-sm @error('config_schema') input-error @enderror"
                        placeholder='{"meeting_duration": {"type": "select", "label": "Duration", "options": [15, 30, 45, 60]}}'>{{ old('config_schema', $feature?->config_schema ? json_encode($feature->config_schema, JSON_PRETTY_PRINT) : '') }}</textarea>
                    <p class="text-xs text-base-content/60 mt-1">Define configurable options for this feature</p>
                    @error('config_schema')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="default_config">Default Config (JSON)</label>
                    <textarea id="default_config" name="default_config" rows="3"
                        class="textarea w-full font-mono text-sm @error('default_config') input-error @enderror"
                        placeholder='{"meeting_duration": 30}'>{{ old('default_config', $feature?->default_config ? json_encode($feature->default_config, JSON_PRETTY_PRINT) : '') }}</textarea>
                    <p class="text-xs text-base-content/60 mt-1">Default values for the configuration</p>
                    @error('default_config')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Status --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Status</h3>
            </div>
            <div class="card-body space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1"
                        class="toggle toggle-primary"
                        {{ old('is_active', $feature?->is_active ?? true) ? 'checked' : '' }}>
                    <div>
                        <span class="font-medium">Active</span>
                        <p class="text-xs text-base-content/60">Feature is available in marketplace</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--check] size-5"></span>
                    {{ $feature ? 'Update Feature' : 'Create Feature' }}
                </button>
                <a href="{{ route('backoffice.features.index') }}" class="btn btn-ghost w-full">
                    Cancel
                </a>
            </div>
        </div>

        @if($feature)
        {{-- Usage Stats --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Usage</h3>
            </div>
            <div class="card-body">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Hosts enabled</dt>
                        <dd class="font-medium">{{ $feature->hosts()->wherePivot('is_enabled', true)->count() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Total host records</dt>
                        <dd class="font-medium">{{ $feature->hosts()->count() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Created</dt>
                        <dd>{{ $feature->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Last Updated</dt>
                        <dd>{{ $feature->updated_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Danger Zone --}}
        <div class="card bg-base-100 border border-error/20">
            <div class="card-header">
                <h3 class="card-title text-error">Danger Zone</h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-base-content/60 mb-3">Permanently delete this feature. This action cannot be undone.</p>
                <form action="{{ route('backoffice.features.destroy', $feature) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this feature? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error btn-soft w-full">
                        <span class="icon-[tabler--trash] size-5"></span>
                        Delete Feature
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
