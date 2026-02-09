@php
    $emailTemplate = $emailTemplate ?? null;
    $hostId = $hostId ?? $emailTemplate?->host_id;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Form --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Basic Info --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Template Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="name">Template Name</label>
                        <input type="text" id="name" name="name"
                            value="{{ old('name', $emailTemplate?->name) }}"
                            class="input w-full @error('name') input-error @enderror"
                            placeholder="e.g., Welcome Email"
                            required>
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="key">Template Key</label>
                        <input type="text" id="key" name="key"
                            value="{{ old('key', $emailTemplate?->key) }}"
                            class="input w-full @error('key') input-error @enderror"
                            placeholder="e.g., welcome_email"
                            required>
                        <p class="text-xs text-base-content/60 mt-1">Unique identifier for programmatic access</p>
                        @error('key')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="category">Category</label>
                        <select id="category" name="category" class="select w-full @error('category') input-error @enderror" required>
                            <option value="system" {{ old('category', $emailTemplate?->category) === 'system' ? 'selected' : '' }}>System</option>
                            <option value="transactional" {{ old('category', $emailTemplate?->category) === 'transactional' ? 'selected' : '' }}>Transactional</option>
                            <option value="marketing" {{ old('category', $emailTemplate?->category) === 'marketing' ? 'selected' : '' }}>Marketing</option>
                        </select>
                        @error('category')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="host_id">Client (optional)</label>
                        <select id="host_id" name="host_id" class="select w-full @error('host_id') input-error @enderror">
                            <option value="">System Template (FitCRM)</option>
                            @foreach($hosts as $host)
                                <option value="{{ $host->id }}" {{ old('host_id', $hostId) == $host->id ? 'selected' : '' }}>
                                    {{ $host->studio_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('host_id')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="label-text" for="subject">Email Subject</label>
                    <input type="text" id="subject" name="subject"
                        value="{{ old('subject', $emailTemplate?->subject) }}"
                        class="input w-full @error('subject') input-error @enderror"
                        placeholder="e.g., Welcome to {{ '{{studio_name}}' }}!"
                        required>
                    @error('subject')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Email Body --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Email Body (HTML)</h3>
            </div>
            <div class="card-body">
                <textarea id="body_html" name="body_html" rows="15"
                    class="textarea w-full font-mono text-sm @error('body_html') input-error @enderror"
                    placeholder="Enter HTML content..."
                    required>{{ old('body_html', $emailTemplate?->body_html) }}</textarea>
                @error('body_html')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Plain Text Version --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Plain Text Version</h3>
                <span class="badge badge-soft badge-neutral badge-sm">Optional</span>
            </div>
            <div class="card-body">
                <textarea id="body_text" name="body_text" rows="8"
                    class="textarea w-full font-mono text-sm @error('body_text') input-error @enderror"
                    placeholder="Enter plain text version...">{{ old('body_text', $emailTemplate?->body_text) }}</textarea>
                @error('body_text')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
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
            <div class="card-body">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1"
                        class="toggle toggle-primary"
                        {{ old('is_active', $emailTemplate?->is_active ?? true) ? 'checked' : '' }}>
                    <div>
                        <span class="font-medium">Active</span>
                        <p class="text-xs text-base-content/60">Template can be used</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Variables --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Available Variables</h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-base-content/60 mb-3">
                    Use these placeholders in your template. They will be replaced with actual values when sending.
                </p>
                <div class="space-y-1 text-sm">
                    <div class="flex items-center gap-2">
                        <code class="bg-base-200 px-2 py-0.5 rounded text-xs">{{ '{{user_name}}' }}</code>
                        <span class="text-base-content/60">User's name</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-base-200 px-2 py-0.5 rounded text-xs">{{ '{{user_email}}' }}</code>
                        <span class="text-base-content/60">User's email</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-base-200 px-2 py-0.5 rounded text-xs">{{ '{{studio_name}}' }}</code>
                        <span class="text-base-content/60">Studio name</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-base-200 px-2 py-0.5 rounded text-xs">{{ '{{class_name}}' }}</code>
                        <span class="text-base-content/60">Class name</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-base-200 px-2 py-0.5 rounded text-xs">{{ '{{class_date}}' }}</code>
                        <span class="text-base-content/60">Class date</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-base-200 px-2 py-0.5 rounded text-xs">{{ '{{class_time}}' }}</code>
                        <span class="text-base-content/60">Class time</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-base-200 px-2 py-0.5 rounded text-xs">{{ '{{instructor_name}}' }}</code>
                        <span class="text-base-content/60">Instructor</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-base-200 px-2 py-0.5 rounded text-xs">{{ '{{booking_reference}}' }}</code>
                        <span class="text-base-content/60">Booking ref</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Custom Variables --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Custom Variables</h3>
                <span class="badge badge-soft badge-neutral badge-sm">JSON</span>
            </div>
            <div class="card-body">
                <textarea id="variables" name="variables" rows="4"
                    class="textarea w-full font-mono text-xs @error('variables') input-error @enderror"
                    placeholder='{"custom_var": "description"}'>{{ old('variables', $emailTemplate?->variables ? json_encode($emailTemplate->variables, JSON_PRETTY_PRINT) : '') }}</textarea>
                @error('variables')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="card bg-base-100">
            <div class="card-body space-y-2">
                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--check] size-5"></span>
                    {{ $emailTemplate ? 'Update Template' : 'Create Template' }}
                </button>
                @if($emailTemplate)
                <a href="{{ route('backoffice.email-templates.preview', $emailTemplate) }}" class="btn btn-soft btn-secondary w-full">
                    <span class="icon-[tabler--eye] size-5"></span>
                    Preview
                </a>
                @endif
                <a href="{{ route('backoffice.email-templates.index') }}" class="btn btn-ghost w-full">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</div>
