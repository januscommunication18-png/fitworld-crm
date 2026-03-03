@extends('layouts.settings')

@section('title', 'Automation Rules — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Automation Rules</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Info Banner --}}
    <div class="alert alert-info">
        <span class="icon-[tabler--robot] size-5"></span>
        <div>
            <div class="font-medium">Automated Email Campaigns</div>
            <div class="text-sm">Enable automated emails that are sent to your clients based on specific triggers. Changes take effect within 24-48 hours as scheduled tasks run.</div>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    <div id="automation-alert" class="alert hidden">
        <span id="alert-icon" class="size-5"></span>
        <span id="alert-message"></span>
    </div>

    {{-- Automation Cards --}}
    @foreach($automationTypes as $key => $type)
    @php
        $setting = $settings[$key] ?? null;
        $isEnabled = $setting?->is_enabled ?? false;
        $config = $setting?->config ?? $type['default_config'];
    @endphp
    <div class="card bg-base-100" id="card-{{ $key }}">
        <div class="card-body">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg {{ $isEnabled ? 'bg-primary/10' : 'bg-base-content/5' }}">
                        <span class="icon-[tabler--{{ $type['icon'] }}] size-6 {{ $isEnabled ? 'text-primary' : 'text-base-content/40' }}"></span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-lg">{{ $type['label'] }}</h3>
                            <span class="badge badge-sm {{ $isEnabled ? 'badge-success' : 'badge-neutral' }}" id="status-{{ $key }}">
                                {{ $isEnabled ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p class="text-base-content/60 mt-1">{{ $type['description'] }}</p>

                        {{-- Configuration Info --}}
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if($key === 'class_reminder')
                                <span class="badge badge-soft badge-sm">
                                    <span class="icon-[tabler--clock] size-3 mr-1"></span>
                                    {{ $config['hours_before'] ?? 24 }} hours before class
                                </span>
                            @elseif($key === 'welcome_email')
                                <span class="badge badge-soft badge-sm">
                                    <span class="icon-[tabler--mail-fast] size-3 mr-1"></span>
                                    {{ ($config['delay_minutes'] ?? 0) == 0 ? 'Sent immediately' : 'Delayed ' . ($config['delay_minutes'] ?? 0) . ' min' }}
                                </span>
                            @elseif($key === 'winback_campaign')
                                <span class="badge badge-soft badge-sm">
                                    <span class="icon-[tabler--calendar-off] size-3 mr-1"></span>
                                    After {{ $config['days_inactive'] ?? 60 }} days inactive
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <label class="swap swap-flip">
                        <input type="checkbox"
                            id="toggle-{{ $key }}"
                            class="automation-toggle"
                            data-key="{{ $key }}"
                            {{ $isEnabled ? 'checked' : '' }}
                        >
                        <span class="swap-on">
                            <span class="flex items-center gap-2 text-success text-sm font-medium">
                                <span class="icon-[tabler--check] size-4"></span> Enabled
                            </span>
                        </span>
                        <span class="swap-off">
                            <span class="flex items-center gap-2 text-base-content/50 text-sm">
                                <span class="icon-[tabler--x] size-4"></span> Disabled
                            </span>
                        </span>
                    </label>
                    <input type="checkbox"
                        class="toggle toggle-primary automation-toggle"
                        id="simple-toggle-{{ $key }}"
                        data-key="{{ $key }}"
                        {{ $isEnabled ? 'checked' : '' }}
                    >
                </div>
            </div>

            {{-- Expandable Configuration --}}
            @if($key !== 'welcome_email')
            <details class="mt-4 collapse collapse-arrow bg-base-200/50 rounded-lg">
                <summary class="collapse-title text-sm font-medium py-3 min-h-0">
                    <span class="icon-[tabler--settings] size-4 mr-1"></span>
                    Configure Settings
                </summary>
                <div class="collapse-content">
                    <form id="config-form-{{ $key }}" class="automation-config-form pt-4" data-key="{{ $key }}">
                        @if($key === 'class_reminder')
                        <div class="form-control max-w-xs">
                            <label class="label" for="hours_before_{{ $key }}">
                                <span class="label-text">Hours Before Class</span>
                            </label>
                            <input type="number" id="hours_before_{{ $key }}" name="hours_before"
                                value="{{ $config['hours_before'] ?? 24 }}"
                                class="input input-bordered w-full" min="1" max="168">
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">Send reminder this many hours before the class starts</span>
                            </label>
                        </div>
                        @elseif($key === 'winback_campaign')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label" for="days_inactive_{{ $key }}">
                                    <span class="label-text">Days Inactive</span>
                                </label>
                                <input type="number" id="days_inactive_{{ $key }}" name="days_inactive"
                                    value="{{ $config['days_inactive'] ?? 60 }}"
                                    class="input input-bordered w-full" min="7" max="365">
                                <label class="label">
                                    <span class="label-text-alt text-base-content/50">Send to clients who haven't booked in this many days</span>
                                </label>
                            </div>
                            <div class="form-control">
                                <label class="label" for="resend_after_{{ $key }}">
                                    <span class="label-text">Resend After (Days)</span>
                                </label>
                                <input type="number" id="resend_after_{{ $key }}" name="resend_after_days"
                                    value="{{ $config['resend_after_days'] ?? 30 }}"
                                    class="input input-bordered w-full" min="7" max="365">
                                <label class="label">
                                    <span class="label-text-alt text-base-content/50">Wait this many days before sending another win-back email</span>
                                </label>
                            </div>
                        </div>
                        @endif

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <span class="icon-[tabler--check] size-4"></span>
                                Save Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </details>
            @endif
        </div>
    </div>
    @endforeach

    {{-- Help Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h3 class="font-semibold flex items-center gap-2">
                <span class="icon-[tabler--help-circle] size-5 text-info"></span>
                How Automation Works
            </h3>
            <div class="mt-3 space-y-3 text-sm text-base-content/70">
                <div class="flex items-start gap-3">
                    <span class="icon-[tabler--bell] size-5 text-primary shrink-0 mt-0.5"></span>
                    <div>
                        <strong>Class Reminder:</strong> Automatically sends a reminder email to clients who have a booking scheduled. The email is sent the configured number of hours before the class starts.
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="icon-[tabler--mail-heart] size-5 text-success shrink-0 mt-0.5"></span>
                    <div>
                        <strong>Welcome Email:</strong> Sends a welcome email to new clients when they sign up. This helps establish a relationship and provides important information about your studio.
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="icon-[tabler--user-heart] size-5 text-warning shrink-0 mt-0.5"></span>
                    <div>
                        <strong>Win-back Campaign:</strong> Reaches out to clients who haven't booked in a while, encouraging them to return. Great for reducing client churn.
                    </div>
                </div>
            </div>
            <div class="alert alert-warning mt-4">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <div class="text-sm">
                    Changes to automation settings will take effect within <strong>24-48 hours</strong> as scheduled tasks run on our servers.
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle toggle changes
    document.querySelectorAll('.automation-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const key = this.dataset.key;
            const isEnabled = this.checked;

            // Sync both toggles (swap and simple toggle)
            document.querySelectorAll(`[data-key="${key}"]`).forEach(function(t) {
                t.checked = isEnabled;
            });

            // Show loading state
            const card = document.getElementById('card-' + key);
            card.classList.add('opacity-50');

            // Send update
            fetch(`{{ url('/settings/notifications/automation') }}/${key}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    is_enabled: isEnabled ? 1 : 0
                })
            })
            .then(response => response.json())
            .then(data => {
                card.classList.remove('opacity-50');

                if (data.success) {
                    // Update status badge
                    const statusBadge = document.getElementById('status-' + key);
                    if (statusBadge) {
                        statusBadge.textContent = isEnabled ? 'Active' : 'Inactive';
                        statusBadge.className = 'badge badge-sm ' + (isEnabled ? 'badge-success' : 'badge-neutral');
                    }

                    // Update icon color
                    const iconContainer = card.querySelector('.p-3.rounded-lg');
                    if (iconContainer) {
                        iconContainer.className = 'p-3 rounded-lg ' + (isEnabled ? 'bg-primary/10' : 'bg-base-content/5');
                        const icon = iconContainer.querySelector('span');
                        if (icon) {
                            icon.className = icon.className.replace(/text-(primary|base-content\/40)/g, isEnabled ? 'text-primary' : 'text-base-content/40');
                        }
                    }

                    // Show success message
                    showAlert('success', data.message);
                } else {
                    // Revert toggle
                    document.querySelectorAll(`[data-key="${key}"]`).forEach(function(t) {
                        t.checked = !isEnabled;
                    });
                    showAlert('error', data.message || 'Failed to update automation setting.');
                }
            })
            .catch(error => {
                card.classList.remove('opacity-50');
                // Revert toggle
                document.querySelectorAll(`[data-key="${key}"]`).forEach(function(t) {
                    t.checked = !isEnabled;
                });
                showAlert('error', 'An error occurred. Please try again.');
            });
        });
    });

    // Handle config form submissions
    document.querySelectorAll('.automation-config-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const key = this.dataset.key;
            const formData = new FormData(this);
            const config = {};

            formData.forEach((value, name) => {
                config[name] = parseInt(value) || value;
            });

            // Get current enabled state
            const toggle = document.getElementById('simple-toggle-' + key);
            const isEnabled = toggle ? toggle.checked : false;

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Saving...';
            submitBtn.disabled = true;

            fetch(`{{ url('/settings/notifications/automation') }}/${key}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    is_enabled: isEnabled ? 1 : 0,
                    config: config
                })
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
    });

    function showAlert(type, message) {
        const alert = document.getElementById('automation-alert');
        const icon = document.getElementById('alert-icon');
        const msg = document.getElementById('alert-message');

        alert.className = 'alert ' + (type === 'success' ? 'alert-success' : 'alert-error');
        icon.className = 'size-5 icon-[tabler--' + (type === 'success' ? 'check' : 'alert-circle') + ']';
        msg.textContent = message;

        alert.classList.remove('hidden');

        // Auto-hide after 5 seconds
        setTimeout(function() {
            alert.classList.add('hidden');
        }, 5000);

        // Scroll to top to show alert
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});
</script>
@endpush
@endsection
