@extends('layouts.dashboard')

@section('title', 'Create Segment')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('segments.index') }}"><span class="icon-[tabler--users-group] me-1 size-4"></span> Segments</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Create</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('segments.store') }}" method="POST">
    @csrf
    <div class="space-y-6 max-w-4xl">
        {{-- Header --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('segments.index') }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <h1 class="text-2xl font-bold">Create Segment</h1>
                <p class="text-base-content/60 mt-1">Define a group of clients to target with offers and campaigns.</p>
            </div>
        </div>

        {{-- Basic Info --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Basic Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="name">Segment Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               class="input w-full @error('name') input-error @enderror"
                               placeholder="e.g., VIP Members, Inactive 30 Days" required>
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="color">Color</label>
                        <input type="color" id="color" name="color" value="{{ old('color', '#6366f1') }}"
                               class="input w-full h-10">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="label-text" for="description">Description</label>
                    <textarea id="description" name="description" rows="2"
                              class="textarea w-full @error('description') textarea-error @enderror"
                              placeholder="Describe who belongs in this segment...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
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
                                   {{ old('type', 'dynamic') === $key ? 'checked' : '' }}
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
        <div id="smart-options" class="card bg-base-100 hidden">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Score-Based Tier</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text" for="tier">Loyalty Tier</label>
                        <select id="tier" name="tier" class="select w-full">
                            <option value="">Select a tier...</option>
                            @foreach($tiers as $key => $label)
                                <option value="{{ $key }}" {{ old('tier') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="label-text" for="min_score">Minimum Score</label>
                        <input type="number" id="min_score" name="min_score" value="{{ old('min_score', 0) }}"
                               class="input w-full" min="0" max="1000">
                    </div>

                    <div>
                        <label class="label-text" for="max_score">Maximum Score</label>
                        <input type="number" id="max_score" name="max_score" value="{{ old('max_score') }}"
                               class="input w-full" min="0" max="1000" placeholder="Leave empty for no max">
                    </div>
                </div>
            </div>
        </div>

        {{-- Dynamic Segment Rules --}}
        <div id="dynamic-options" class="card bg-base-100">
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
                    {{-- Rules will be added here dynamically --}}
                </div>

                <div id="no-rules" class="text-center py-8 text-base-content/60">
                    <span class="icon-[tabler--filter-off] size-12 mx-auto mb-2"></span>
                    <p>No rules defined yet. Add rules to filter clients.</p>
                </div>

                {{-- Preview --}}
                <div id="preview-section" class="mt-6 pt-6 border-t border-base-300 hidden">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium">Preview</h3>
                            <p class="text-sm text-base-content/60">Clients matching current rules</p>
                        </div>
                        <button type="button" onclick="previewRules()" class="btn btn-sm btn-outline">
                            <span class="icon-[tabler--eye] size-4"></span>
                            Preview
                        </button>
                    </div>
                    <div id="preview-result" class="mt-4"></div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('segments.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--check] size-5"></span>
                Create Segment
            </button>
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
                    <select name="rules[__INDEX__][field]" class="select select-sm w-full rule-field" onchange="updateOperators(this)" required>
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
    let ruleIndex = 0;
    const availableFields = @json($availableFields);

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
        document.getElementById('preview-section').classList.remove('hidden');
    }

    function removeRule(btn) {
        btn.closest('.rule-item').remove();

        const container = document.getElementById('rules-container');
        if (container.children.length === 0) {
            document.getElementById('no-rules').classList.remove('hidden');
            document.getElementById('preview-section').classList.add('hidden');
        }
    }

    function updateOperators(fieldSelect) {
        const field = fieldSelect.value;
        const fieldConfig = availableFields[field];
        const operatorSelect = fieldSelect.closest('.rule-item').querySelector('.rule-operator');
        const valueInput = fieldSelect.closest('.rule-item').querySelector('.rule-value');

        // Update value input based on field type
        if (fieldConfig) {
            if (fieldConfig.type === 'boolean') {
                valueInput.style.display = 'none';
            } else if (fieldConfig.type === 'select' && fieldConfig.options) {
                // Could convert to select dropdown
                valueInput.placeholder = fieldConfig.options.join(', ');
                valueInput.style.display = '';
            } else {
                valueInput.style.display = '';
                valueInput.placeholder = fieldConfig.type === 'number' ? 'Enter number' : 'Enter value';
            }
        }
    }

    function previewRules() {
        const rules = [];
        document.querySelectorAll('.rule-item').forEach((item, index) => {
            const field = item.querySelector('.rule-field').value;
            const operator = item.querySelector('.rule-operator').value;
            const value = item.querySelector('.rule-value').value;
            const groupIndex = item.querySelector('.rule-group').value;

            if (field && operator) {
                rules.push({ field, operator, value, group_index: groupIndex });
            }
        });

        if (rules.length === 0) {
            document.getElementById('preview-result').innerHTML = '<p class="text-warning">Add at least one rule to preview.</p>';
            return;
        }

        fetch('{{ route("segments.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ rules })
        })
        .then(res => res.json())
        .then(data => {
            let html = `<div class="flex items-center gap-2 text-lg font-semibold">
                <span class="icon-[tabler--users] size-5 text-primary"></span>
                ${data.count} clients match
            </div>`;

            if (data.sample && data.sample.length > 0) {
                html += '<div class="mt-3 space-y-2">';
                data.sample.forEach(client => {
                    html += `<div class="flex items-center gap-2 text-sm">
                        <span class="icon-[tabler--user] size-4 text-base-content/60"></span>
                        ${client.first_name} ${client.last_name} <span class="text-base-content/60">(${client.email})</span>
                    </div>`;
                });
                if (data.count > 5) {
                    html += `<p class="text-sm text-base-content/60">...and ${data.count - 5} more</p>`;
                }
                html += '</div>';
            }

            document.getElementById('preview-result').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('preview-result').innerHTML = '<p class="text-error">Failed to preview. Please try again.</p>';
        });
    }

    // Initialize
    toggleTypeOptions();
</script>
@endpush
@endsection
