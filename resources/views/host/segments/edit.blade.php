@extends('layouts.dashboard')

@section('title', 'Edit ' . $segment->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('segments.index') }}"><span class="icon-[tabler--users-group] me-1 size-4"></span> Segments</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('segments.show', $segment) }}">{{ $segment->name }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Edit</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('segments.update', $segment) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="space-y-6 max-w-4xl">
        {{-- Header --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('segments.show', $segment) }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <h1 class="text-2xl font-bold">Edit Segment</h1>
                <p class="text-base-content/60 mt-1">Update segment details and rules.</p>
            </div>
        </div>

        {{-- Basic Info --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Basic Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="name">Segment Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $segment->name) }}"
                               class="input w-full @error('name') input-error @enderror"
                               placeholder="e.g., VIP Members, Inactive 30 Days" required>
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="color">Color</label>
                        <input type="color" id="color" name="color" value="{{ old('color', $segment->color) }}"
                               class="input w-full h-10">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="label-text" for="description">Description</label>
                    <textarea id="description" name="description" rows="2"
                              class="textarea w-full @error('description') textarea-error @enderror"
                              placeholder="Describe who belongs in this segment...">{{ old('description', $segment->description) }}</textarea>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label class="label label-text cursor-pointer justify-start gap-2">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary checkbox-sm"
                               {{ old('is_active', $segment->is_active) ? 'checked' : '' }}>
                        <span>Segment is active</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Segment Type --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Segment Type</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($types as $key => $label)
                        <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                            <input type="radio" name="type" value="{{ $key }}"
                                   class="radio radio-primary mt-1"
                                   {{ old('type', $segment->type) === $key ? 'checked' : '' }}
                                   onchange="toggleTypeOptions()">
                            <span class="label-text w-full text-start">
                                <span class="text-base font-medium">{{ $label }}</span>
                                <span class="block text-sm text-base-content/60">
                                    @if($key === 'static')
                                        Manually add/remove clients
                                    @elseif($key === 'dynamic')
                                        Auto-update based on rules
                                    @else
                                        Based on engagement score
                                    @endif
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Smart Segment Options --}}
        <div id="smart-options" class="card bg-base-100 {{ $segment->type !== 'smart' ? 'hidden' : '' }}">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Score-Based Tier</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text" for="tier">Loyalty Tier</label>
                        <select id="tier" name="tier" class="select w-full">
                            <option value="">Select a tier...</option>
                            @foreach($tiers as $key => $label)
                                <option value="{{ $key }}" {{ old('tier', $segment->tier) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="label-text" for="min_score">Minimum Score</label>
                        <input type="number" id="min_score" name="min_score" value="{{ old('min_score', $segment->min_score ?? 0) }}"
                               class="input w-full" min="0" max="1000">
                    </div>

                    <div>
                        <label class="label-text" for="max_score">Maximum Score</label>
                        <input type="number" id="max_score" name="max_score" value="{{ old('max_score', $segment->max_score) }}"
                               class="input w-full" min="0" max="1000" placeholder="Leave empty for no max">
                    </div>
                </div>
            </div>
        </div>

        {{-- Dynamic Segment Rules --}}
        <div id="dynamic-options" class="card bg-base-100 {{ $segment->type === 'static' ? 'hidden' : '' }}">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">Segment Rules</h2>
                    <button type="button" onclick="addRule()" class="btn btn-sm btn-outline">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Add Rule
                    </button>
                </div>

                <p class="text-sm text-base-content/60 mb-4">
                    Clients matching <strong>all rules within a group</strong> (AND) will be included.
                    Multiple groups act as OR conditions.
                </p>

                <div id="rules-container" class="space-y-4">
                    {{-- Existing rules --}}
                    @foreach($segment->rules as $index => $rule)
                        <div class="rule-item bg-base-200/50 rounded-lg p-4" data-rule-index="{{ $index }}">
                            <div class="flex items-start gap-4">
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="label-text text-sm">Field</label>
                                        <select name="rules[{{ $index }}][field]" class="select select-sm w-full rule-field" required>
                                            <option value="">Select field...</option>
                                            @foreach($availableFields as $key => $field)
                                                <option value="{{ $key }}" {{ $rule->field === $key ? 'selected' : '' }}>{{ $field['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-text text-sm">Operator</label>
                                        <select name="rules[{{ $index }}][operator]" class="select select-sm w-full rule-operator" required>
                                            <option value="">Select operator...</option>
                                            @foreach($operators as $key => $label)
                                                <option value="{{ $key }}" {{ $rule->operator === $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-text text-sm">Value</label>
                                        <input type="text" name="rules[{{ $index }}][value]" class="input input-sm w-full rule-value"
                                               value="{{ $rule->value }}" placeholder="Value">
                                    </div>
                                </div>
                                <input type="hidden" name="rules[{{ $index }}][group_index]" value="{{ $rule->group_index }}" class="rule-group">
                                <button type="button" onclick="removeRule(this)" class="btn btn-ghost btn-sm btn-square text-error mt-6">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div id="no-rules" class="text-center py-8 text-base-content/60 {{ $segment->rules->isNotEmpty() ? 'hidden' : '' }}">
                    <span class="icon-[tabler--filter-off] size-12 mx-auto mb-2"></span>
                    <p>No rules defined yet. Add rules to filter clients.</p>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <form action="{{ route('segments.destroy', $segment) }}" method="POST" class="inline"
                  onsubmit="return confirm('Are you sure you want to delete this segment?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-ghost text-error">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete Segment
                </button>
            </form>

            <div class="flex items-center gap-3">
                <a href="{{ route('segments.show', $segment) }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--check] size-5"></span>
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</form>

{{-- Rule Template --}}
<template id="rule-template">
    <div class="rule-item bg-base-200/50 rounded-lg p-4" data-rule-index="__INDEX__">
        <div class="flex items-start gap-4">
            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="label-text text-sm">Field</label>
                    <select name="rules[__INDEX__][field]" class="select select-sm w-full rule-field" required>
                        <option value="">Select field...</option>
                        @foreach($availableFields as $key => $field)
                            <option value="{{ $key }}">{{ $field['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label-text text-sm">Operator</label>
                    <select name="rules[__INDEX__][operator]" class="select select-sm w-full rule-operator" required>
                        <option value="">Select operator...</option>
                        @foreach($operators as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label-text text-sm">Value</label>
                    <input type="text" name="rules[__INDEX__][value]" class="input input-sm w-full rule-value" placeholder="Value">
                </div>
            </div>
            <input type="hidden" name="rules[__INDEX__][group_index]" value="0" class="rule-group">
            <button type="button" onclick="removeRule(this)" class="btn btn-ghost btn-sm btn-square text-error mt-6">
                <span class="icon-[tabler--trash] size-4"></span>
            </button>
        </div>
    </div>
</template>

@push('scripts')
<script>
    let ruleIndex = {{ $segment->rules->count() }};

    function toggleTypeOptions() {
        const type = document.querySelector('input[name="type"]:checked')?.value;
        document.getElementById('smart-options').classList.toggle('hidden', type !== 'smart');
        document.getElementById('dynamic-options').classList.toggle('hidden', type === 'static');
    }

    function addRule() {
        const container = document.getElementById('rules-container');
        const template = document.getElementById('rule-template');
        const html = template.innerHTML.replace(/__INDEX__/g, ruleIndex++);

        container.insertAdjacentHTML('beforeend', html);
        document.getElementById('no-rules').classList.add('hidden');
    }

    function removeRule(btn) {
        btn.closest('.rule-item').remove();

        const container = document.getElementById('rules-container');
        if (container.children.length === 0) {
            document.getElementById('no-rules').classList.remove('hidden');
        }
    }

    // Initialize
    toggleTypeOptions();
</script>
@endpush
@endsection
