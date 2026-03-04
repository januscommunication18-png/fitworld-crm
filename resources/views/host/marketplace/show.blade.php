@extends('layouts.dashboard')

@section('title', $feature->name . ' - Feature Marketplace')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('marketplace.index') }}"><span class="icon-[tabler--apps] me-1 size-4"></span> Marketplace</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $feature->name }}</li>
    </ol>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Alert Container --}}
    <div id="feature-alert" class="alert hidden">
        <span id="alert-icon" class="size-5"></span>
        <span id="alert-message"></span>
    </div>

    {{-- Feature Header Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-start gap-4">
                <div class="p-4 rounded-xl {{ $isEnabled ? 'bg-primary/10' : 'bg-base-content/5' }}" id="icon-container">
                    <span class="icon-[tabler--{{ $feature->icon }}] size-10 {{ $isEnabled ? 'text-primary' : 'text-base-content/40' }}" id="feature-icon"></span>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl font-bold">{{ $feature->name }}</h1>
                        @if($feature->isPremium())
                            <span class="badge badge-warning">
                                <span class="icon-[tabler--crown] size-4 mr-1"></span>
                                Premium
                            </span>
                        @else
                            <span class="badge badge-soft badge-success">Free</span>
                        @endif
                    </div>
                    <p class="text-base-content/60 mt-2">{{ $feature->description }}</p>
                    <div class="mt-3">
                        <span class="badge badge-soft badge-neutral badge-sm">
                            {{ \App\Models\Feature::getCategories()[$feature->category] ?? ucfirst($feature->category) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Enable/Disable Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Feature Status</h2>
                    <p class="text-sm text-base-content/60 mt-1" id="status-text">
                        {{ $isEnabled ? 'This feature is currently enabled for your studio.' : 'Enable this feature to start using it.' }}
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="badge {{ $isEnabled ? 'badge-success' : 'badge-neutral' }}" id="status-badge">
                        {{ $isEnabled ? 'Enabled' : 'Disabled' }}
                    </span>
                    @if($requiresUpgrade)
                        <a href="{{ route('settings.billing.plan') }}" class="btn btn-warning">
                            <span class="icon-[tabler--crown] size-5"></span>
                            Upgrade to Enable
                        </a>
                    @else
                        <input type="checkbox"
                            class="toggle toggle-primary toggle-lg"
                            id="feature-toggle"
                            data-feature-id="{{ $feature->id }}"
                            {{ $isEnabled ? 'checked' : '' }}
                        >
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Feature Action Card (for features with dedicated pages) --}}
    @if($isEnabled && $feature->slug === 'progress-templates')
    <div class="card bg-base-100 border-2 border-primary/20" id="feature-action-card">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <span class="icon-[tabler--chart-line] size-5 text-primary"></span>
                        Progress Templates
                    </h2>
                    <p class="text-sm text-base-content/60 mt-1">
                        Browse and enable progress tracking templates for your clients.
                    </p>
                </div>
                <a href="{{ route('progress-templates.index') }}" class="btn btn-primary">
                    <span class="icon-[tabler--arrow-right] size-5"></span>
                    Go to Progress Templates
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Configuration Card (only show if feature has config and is enabled) --}}
    @if($feature->config_schema)
    <div class="card bg-base-100 {{ !$isEnabled ? 'opacity-50 pointer-events-none' : '' }}" id="config-card">
        <div class="card-body">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <span class="icon-[tabler--settings] size-5 text-primary"></span>
                Configure Settings
            </h2>
            <p class="text-sm text-base-content/60 mt-1 mb-4">
                Customize how this feature works for your studio.
            </p>

            <form id="config-form" class="space-y-4">
                @foreach($feature->config_schema as $key => $schema)
                    <div class="form-control">
                        <label class="label" for="config-{{ $key }}">
                            <span class="label-text font-medium">{{ $schema['label'] ?? ucfirst(str_replace('_', ' ', $key)) }}</span>
                        </label>
                        @if(isset($schema['type']) && $schema['type'] === 'select')
                            <select id="config-{{ $key }}"
                                name="{{ $key }}"
                                class="select select-bordered w-full max-w-sm">
                                @foreach($schema['options'] ?? [] as $option)
                                    <option value="{{ $option }}" {{ ($config[$key] ?? $schema['default'] ?? '') == $option ? 'selected' : '' }}>
                                        {{ is_numeric($option) ? $option : ucfirst($option) }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif(isset($schema['type']) && $schema['type'] === 'boolean')
                            <input type="checkbox" id="config-{{ $key }}"
                                name="{{ $key }}"
                                class="toggle toggle-primary"
                                {{ ($config[$key] ?? $schema['default'] ?? false) ? 'checked' : '' }}>
                        @elseif(isset($schema['type']) && $schema['type'] === 'color')
                            <input type="color" id="config-{{ $key }}"
                                name="{{ $key }}"
                                value="{{ $config[$key] ?? $schema['default'] ?? '#6366f1' }}"
                                class="input input-bordered w-20 h-10 p-1 cursor-pointer">
                        @elseif(isset($schema['type']) && $schema['type'] === 'number')
                            <input type="number" id="config-{{ $key }}"
                                name="{{ $key }}"
                                value="{{ $config[$key] ?? $schema['default'] ?? '' }}"
                                class="input input-bordered w-full max-w-sm">
                        @else
                            <input type="text" id="config-{{ $key }}"
                                name="{{ $key }}"
                                value="{{ $config[$key] ?? $schema['default'] ?? '' }}"
                                class="input input-bordered w-full max-w-sm">
                        @endif
                        @if(isset($schema['description']))
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">{{ $schema['description'] }}</span>
                            </label>
                        @endif
                    </div>
                @endforeach

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary" id="save-config-btn">
                        <span class="icon-[tabler--check] size-5"></span>
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Back Link --}}
    <div>
        <a href="{{ route('marketplace.index') }}" class="btn btn-ghost">
            <span class="icon-[tabler--arrow-left] size-5"></span>
            Back to Marketplace
        </a>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const featureId = {{ $feature->id }};
    const featureToggle = document.getElementById('feature-toggle');
    const configCard = document.getElementById('config-card');
    const configForm = document.getElementById('config-form');
    const featureActionCard = document.getElementById('feature-action-card');

    // Handle feature toggle
    if (featureToggle) {
        featureToggle.addEventListener('change', function() {
            const enable = this.checked;

            // Show loading state
            featureToggle.disabled = true;

            fetch(`{{ url('/marketplace') }}/${featureId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ enable: enable })
            })
            .then(response => response.json())
            .then(data => {
                featureToggle.disabled = false;

                if (data.success) {
                    // Update UI
                    const statusBadge = document.getElementById('status-badge');
                    const statusText = document.getElementById('status-text');
                    const iconContainer = document.getElementById('icon-container');
                    const featureIcon = document.getElementById('feature-icon');

                    statusBadge.textContent = enable ? 'Enabled' : 'Disabled';
                    statusBadge.className = 'badge ' + (enable ? 'badge-success' : 'badge-neutral');

                    statusText.textContent = enable
                        ? 'This feature is currently enabled for your studio.'
                        : 'Enable this feature to start using it.';

                    iconContainer.className = 'p-4 rounded-xl ' + (enable ? 'bg-primary/10' : 'bg-base-content/5');

                    // Update icon color classes
                    const iconClasses = featureIcon.className.split(' ');
                    const newIconClasses = iconClasses.filter(c => !c.startsWith('text-'));
                    newIconClasses.push(enable ? 'text-primary' : 'text-base-content/40');
                    featureIcon.className = newIconClasses.join(' ');

                    // Toggle config card visibility
                    if (configCard) {
                        if (enable) {
                            configCard.classList.remove('opacity-50', 'pointer-events-none');
                        } else {
                            configCard.classList.add('opacity-50', 'pointer-events-none');
                        }
                    }

                    // Toggle feature action card visibility
                    if (featureActionCard) {
                        if (enable) {
                            featureActionCard.classList.remove('hidden');
                        } else {
                            featureActionCard.classList.add('hidden');
                        }
                    }

                    showAlert('success', data.message);
                } else {
                    // Revert toggle
                    featureToggle.checked = !enable;
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                featureToggle.disabled = false;
                featureToggle.checked = !enable;
                showAlert('error', 'An error occurred. Please try again.');
            });
        });
    }

    // Handle config form submission
    if (configForm) {
        configForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const config = {};

            formData.forEach((value, name) => {
                const input = this.querySelector(`[name="${name}"]`);
                if (input && input.type === 'checkbox') {
                    config[name] = input.checked;
                } else {
                    config[name] = isNaN(value) ? value : (value === '' ? value : Number(value));
                }
            });

            // Also check unchecked checkboxes
            this.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                if (!config.hasOwnProperty(cb.name)) {
                    config[cb.name] = cb.checked;
                }
            });

            const submitBtn = document.getElementById('save-config-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Saving...';
            submitBtn.disabled = true;

            fetch(`{{ url('/marketplace') }}/${featureId}/config`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ config: config })
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;

                if (data.success) {
                    showAlert('success', data.message);
                } else {
                    showAlert('error', data.message || 'Failed to save configuration.');
                }
            })
            .catch(error => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showAlert('error', 'An error occurred. Please try again.');
            });
        });
    }

    function showAlert(type, message) {
        const alert = document.getElementById('feature-alert');
        const icon = document.getElementById('alert-icon');
        const msg = document.getElementById('alert-message');

        alert.className = 'alert ' + (type === 'success' ? 'alert-success' : 'alert-error');
        icon.className = 'size-5 icon-[tabler--' + (type === 'success' ? 'check' : 'alert-circle') + ']';
        msg.textContent = message;

        alert.classList.remove('hidden');

        setTimeout(function() {
            alert.classList.add('hidden');
        }, 5000);

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});
</script>
@endpush
@endsection
