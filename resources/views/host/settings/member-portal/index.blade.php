@extends('layouts.settings')

@section('title', 'Client & Portal Settings — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Client & Portal Settings</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold">Client & Portal Settings</h1>
        <p class="text-base-content/60 mt-1">Manage client defaults and configure the member portal experience.</p>
    </div>

    <div class="accordion space-y-1.5" id="client-portal-accordion">

        {{-- ═══ 1. Client Defaults ═══ --}}
        <div class="accordion-item bg-base-100 rounded-lg" id="client-defaults-section">
            <button class="accordion-toggle inline-flex items-center gap-2 px-5 py-4 w-full text-left font-medium" aria-controls="client-defaults-content" aria-expanded="false">
                <span class="icon-[tabler--users-cog] size-5 text-primary"></span>
                <div class="flex-1">
                    <span class="text-lg font-semibold">Client Defaults</span>
                    <span class="text-base-content/60 text-sm block font-normal">Default status, thresholds, and required fields for new clients</span>
                </div>
                <span class="icon-[tabler--chevron-down] accordion-icon size-5 transition-transform"></span>
            </button>
            <div id="client-defaults-content" class="accordion-content w-full overflow-hidden transition-[height] hidden" role="region">
                <div class="px-5 pb-5">
                    <form id="client-settings-form" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                            <div>
                                <label class="label-text" for="default_status">Default Status for New Clients</label>
                                <select id="default_status" name="default_status" class="select select-bordered w-full mt-1">
                                    <option value="lead" {{ ($clientSettings['default_status'] ?? 'lead') === 'lead' ? 'selected' : '' }}>Lead</option>
                                    <option value="client" {{ ($clientSettings['default_status'] ?? '') === 'client' ? 'selected' : '' }}>Client</option>
                                </select>
                                <p class="text-xs text-base-content/50 mt-1">Status assigned when a new client is added manually.</p>
                            </div>

                            <div>
                                <label class="label-text" for="at_risk_days">At-Risk Threshold (Days)</label>
                                <input type="number" id="at_risk_days" name="at_risk_days"
                                       value="{{ $clientSettings['at_risk_days'] ?? 30 }}"
                                       min="7" max="90"
                                       class="input input-bordered w-full mt-1">
                                <p class="text-xs text-base-content/50 mt-1">Mark clients as at-risk after this many days without a visit.</p>
                            </div>

                            <div>
                                <label class="label-text" for="auto_archive_days">Auto-Archive After (Days)</label>
                                <input type="number" id="auto_archive_days" name="auto_archive_days"
                                       value="{{ $clientSettings['auto_archive_days'] ?? '' }}"
                                       min="30" max="365"
                                       placeholder="Leave empty to disable"
                                       class="input input-bordered w-full mt-1">
                                <p class="text-xs text-base-content/50 mt-1">Automatically archive inactive clients. Leave empty to disable.</p>
                            </div>
                        </div>

                        <div class="divider my-2"></div>

                        <div>
                            <h3 class="font-medium mb-3">Required Fields</h3>
                            <div class="flex flex-wrap gap-6">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="require_phone" value="1"
                                           {{ ($clientSettings['require_phone'] ?? false) ? 'checked' : '' }}
                                           class="checkbox checkbox-primary checkbox-sm">
                                    <span class="text-sm">Require phone number</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="require_address" value="1"
                                           {{ ($clientSettings['require_address'] ?? false) ? 'checked' : '' }}
                                           class="checkbox checkbox-primary checkbox-sm">
                                    <span class="text-sm">Require address</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-start pt-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <span class="icon-[tabler--check] size-4"></span>
                                Save Client Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ═══ 2. Member Portal ═══ --}}
        <div class="accordion-item bg-base-100 rounded-lg" id="member-portal-section">
            <button class="accordion-toggle inline-flex items-center gap-2 px-5 py-4 w-full text-left font-medium" aria-controls="member-portal-content" aria-expanded="false">
                <span class="icon-[tabler--user-shield] size-5 text-primary"></span>
                <div class="flex-1">
                    <span class="text-lg font-semibold">Member Portal</span>
                    <span class="text-base-content/60 text-sm block font-normal">Authentication, security, and feature access for the member portal</span>
                </div>
                <span class="icon-[tabler--chevron-down] accordion-icon size-5 transition-transform"></span>
            </button>
            <div id="member-portal-content" class="accordion-content w-full overflow-hidden transition-[height] hidden" role="region">
                <div class="px-5 pb-5 space-y-6">

                    {{-- Enable Toggle --}}
                    <div class="flex items-center justify-between pt-2">
                        <div>
                            <span class="font-medium">Enable Member Portal</span>
                            <p class="text-xs text-base-content/60">Allow members to log in and access their bookings, payments, and profile.</p>
                        </div>
                        <label class="switch switch-primary">
                            <input type="checkbox" id="portal_enabled" name="enabled"
                                   value="1"
                                   {{ ($settings['enabled'] ?? false) ? 'checked' : '' }} />
                            <span class="switch-indicator"></span>
                        </label>
                    </div>

                    @if($host->subdomain)
                    @php
                        $bookingDomain = config('app.booking_domain', 'fitcrm.biz');
                        $isLocal = str_contains($bookingDomain, 'local');
                        $protocol = $isLocal ? 'http' : 'https';
                        $port = $isLocal ? ':8888' : '';
                        $portalUrl = "{$protocol}://{$host->subdomain}.{$bookingDomain}{$port}/login";
                    @endphp
                    <div class="p-3 bg-base-200/50 rounded-lg inline-flex items-center gap-2">
                        <span class="icon-[tabler--link] size-4 text-base-content/50"></span>
                        <span class="text-sm text-base-content/70">Portal URL:</span>
                        <a href="{{ $portalUrl }}" target="_blank" class="link link-primary text-sm font-medium">
                            {{ $host->subdomain }}.{{ $bookingDomain }}{{ $port }}/login
                        </a>
                    </div>
                    @endif

                    {{-- Portal sub-sections (collapse when disabled) --}}
                    <div id="portal-settings-accordion" class="{{ ($settings['enabled'] ?? false) ? '' : 'hidden' }}">
                        <div class="space-y-6">

                            {{-- Authentication --}}
                            <div>
                                <h3 class="font-medium flex items-center gap-2 mb-3">
                                    <span class="icon-[tabler--fingerprint] size-4 text-primary"></span>
                                    Authentication
                                </h3>
                                <p class="text-sm text-base-content/60 mb-3">Choose how members will authenticate to access the portal.</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label class="flex items-start gap-4 p-4 rounded-xl border border-base-300 cursor-pointer hover:border-primary/50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="radio" name="login_method" value="otp"
                                               {{ ($settings['login_method'] ?? 'otp') === 'otp' ? 'checked' : '' }}
                                               class="radio radio-primary mt-1">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="icon-[tabler--mail-code] size-4 text-primary"></span>
                                                <span class="font-medium text-sm">Email + Activation Code</span>
                                                <span class="badge badge-soft badge-success badge-xs">Recommended</span>
                                            </div>
                                            <p class="text-xs text-base-content/60 mt-1">One-time code sent via email. No password to remember.</p>
                                        </div>
                                    </label>

                                    <label class="flex items-start gap-4 p-4 rounded-xl border border-base-300 cursor-pointer hover:border-primary/50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="radio" name="login_method" value="password"
                                               {{ ($settings['login_method'] ?? 'otp') === 'password' ? 'checked' : '' }}
                                               class="radio radio-primary mt-1">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="icon-[tabler--password] size-4 text-base-content/60"></span>
                                                <span class="font-medium text-sm">Email + Password</span>
                                            </div>
                                            <p class="text-xs text-base-content/60 mt-1">Traditional password login with forgot-password flow.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="divider my-2"></div>

                            {{-- Session & Security --}}
                            <div>
                                <h3 class="font-medium flex items-center gap-2 mb-3">
                                    <span class="icon-[tabler--shield-lock] size-4 text-primary"></span>
                                    Session & Security
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="label-text" for="session_timeout_days">Session Timeout</label>
                                        <div class="flex items-center gap-2 mt-1">
                                            <input type="number" id="session_timeout_days" name="session_timeout_days"
                                                   value="{{ $settings['session_timeout_days'] ?? 30 }}"
                                                   min="1" max="90"
                                                   class="input input-bordered w-24">
                                            <span class="text-sm text-base-content/60">days</span>
                                        </div>
                                        <p class="text-xs text-base-content/50 mt-1">How long members stay logged in (1-90 days).</p>
                                    </div>

                                    <div id="otp_settings">
                                        <label class="label-text" for="activation_code_expiry_minutes">Code Expiry</label>
                                        <div class="flex items-center gap-2 mt-1">
                                            <input type="number" id="activation_code_expiry_minutes" name="activation_code_expiry_minutes"
                                                   value="{{ $settings['activation_code_expiry_minutes'] ?? 10 }}"
                                                   min="5" max="60"
                                                   class="input input-bordered w-24">
                                            <span class="text-sm text-base-content/60">minutes</span>
                                        </div>
                                        <p class="text-xs text-base-content/50 mt-1">How long the OTP code remains valid.</p>
                                    </div>

                                    <div id="otp_resend_settings">
                                        <label class="label-text" for="max_otp_resend_per_hour">Max Code Resends</label>
                                        <div class="flex items-center gap-2 mt-1">
                                            <input type="number" id="max_otp_resend_per_hour" name="max_otp_resend_per_hour"
                                                   value="{{ $settings['max_otp_resend_per_hour'] ?? 3 }}"
                                                   min="1" max="10"
                                                   class="input input-bordered w-24">
                                            <span class="text-sm text-base-content/60">per hour</span>
                                        </div>
                                        <p class="text-xs text-base-content/50 mt-1">Maximum resend requests allowed per hour.</p>
                                    </div>

                                    <div>
                                        <label class="label-text" for="max_login_attempts">Max Login Attempts</label>
                                        <input type="number" id="max_login_attempts" name="max_login_attempts"
                                               value="{{ $settings['max_login_attempts'] ?? 10 }}"
                                               min="3" max="20"
                                               class="input input-bordered w-full mt-1">
                                        <p class="text-xs text-base-content/50 mt-1">Lock account after this many failed attempts.</p>
                                    </div>

                                    <div>
                                        <label class="label-text" for="lockout_duration_minutes">Lockout Duration</label>
                                        <div class="flex items-center gap-2 mt-1">
                                            <input type="number" id="lockout_duration_minutes" name="lockout_duration_minutes"
                                                   value="{{ $settings['lockout_duration_minutes'] ?? 30 }}"
                                                   min="5" max="120"
                                                   class="input input-bordered w-24">
                                            <span class="text-sm text-base-content/60">minutes</span>
                                        </div>
                                        <p class="text-xs text-base-content/50 mt-1">How long accounts are locked after too many failures.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="divider my-2"></div>

                            {{-- Portal Features --}}
                            <div>
                                <h3 class="font-medium flex items-center gap-2 mb-2">
                                    <span class="icon-[tabler--apps] size-4 text-primary"></span>
                                    Portal Features
                                </h3>
                                <p class="text-xs text-base-content/60 mb-4">Select which features members can access in their portal.</p>

                                @php
                                    $allFeatures = [
                                        'schedule' => ['label' => 'View Schedule', 'icon' => 'calendar', 'description' => 'Browse upcoming classes and services'],
                                        'bookings' => ['label' => 'My Bookings', 'icon' => 'calendar-check', 'description' => 'View and manage their bookings'],
                                        'payments' => ['label' => 'Payment History', 'icon' => 'credit-card', 'description' => 'View transaction history'],
                                        'invoices' => ['label' => 'Download Invoices', 'icon' => 'file-invoice', 'description' => 'Download PDF invoices'],
                                        'profile' => ['label' => 'Edit Profile', 'icon' => 'user-edit', 'description' => 'Update their contact info'],
                                        'intake_forms' => ['label' => 'Intake Forms', 'icon' => 'forms', 'description' => 'Fill out assigned questionnaires'],
                                    ];
                                    $enabledFeatures = $settings['allowed_features'] ?? ['schedule', 'bookings', 'payments', 'invoices', 'profile'];
                                @endphp

                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                    @foreach($allFeatures as $key => $feature)
                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-base-300 cursor-pointer hover:border-primary/50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="checkbox" name="allowed_features[]" value="{{ $key }}"
                                               {{ in_array($key, $enabledFeatures) ? 'checked' : '' }}
                                               class="checkbox checkbox-primary checkbox-sm mt-0.5">
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="icon-[tabler--{{ $feature['icon'] }}] size-4 text-base-content/60"></span>
                                                <span class="text-sm font-medium">{{ $feature['label'] }}</span>
                                            </div>
                                            <p class="text-xs text-base-content/50 mt-0.5">{{ $feature['description'] }}</p>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Save Portal Settings --}}
                    <div class="flex justify-start pt-2">
                        <button type="button" id="save-portal-btn" class="btn btn-primary btn-sm">
                            <span class="icon-[tabler--check] size-4"></span>
                            Save Portal Settings
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>{{-- /accordion --}}

</div>

@push('scripts')
<script>
function showToast(message, type) {
    type = type || 'success';
    var toast = document.createElement('div');
    toast.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[100] alert alert-' + type + ' shadow-lg max-w-sm';
    toast.innerHTML = '<span class="icon-[tabler--' + (type === 'success' ? 'check' : 'alert-circle') + '] size-5"></span><span>' + message + '</span>';
    document.body.appendChild(toast);
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    var portalToggle = document.getElementById('portal_enabled');
    var portalAccordion = document.getElementById('portal-settings-accordion');
    var otpSettings = document.getElementById('otp_settings');
    var otpResendSettings = document.getElementById('otp_resend_settings');
    var loginMethodInputs = document.querySelectorAll('input[name="login_method"]');

    function togglePortalSections() {
        if (portalToggle.checked) {
            portalAccordion.classList.remove('hidden');
        } else {
            portalAccordion.classList.add('hidden');
        }
    }

    portalToggle.addEventListener('change', function() {
        togglePortalSections();

        fetch('{{ route("settings.member-portal.update") }}', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ enabled: portalToggle.checked }),
        }).catch(function() {});
    });

    function toggleOtpSettings() {
        var selectedMethod = document.querySelector('input[name="login_method"]:checked');
        var isOtp = (selectedMethod ? selectedMethod.value : 'otp') === 'otp';
        otpSettings.style.display = isOtp ? '' : 'none';
        otpResendSettings.style.display = isOtp ? '' : 'none';
    }

    loginMethodInputs.forEach(function(input) {
        input.addEventListener('change', toggleOtpSettings);
    });

    toggleOtpSettings();
});

// Client settings form
document.getElementById('client-settings-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    var form = e.target;
    var submitBtn = form.querySelector('button[type="submit"]');
    var originalHtml = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Saving...';

    var formData = new FormData(form);
    var data = {
        default_status: formData.get('default_status'),
        at_risk_days: parseInt(formData.get('at_risk_days')) || 30,
        auto_archive_days: formData.get('auto_archive_days') ? parseInt(formData.get('auto_archive_days')) : null,
        require_phone: formData.has('require_phone'),
        require_address: formData.has('require_address'),
    };

    try {
        var response = await fetch('{{ route("settings.clients.update") }}', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify(data),
        });
        var result = await response.json();
        showToast(result.success ? 'Client settings saved' : (result.message || 'Failed to save'), result.success ? 'success' : 'error');
    } catch (error) {
        showToast('An error occurred while saving', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
    }
});

// Portal settings save
document.getElementById('save-portal-btn').addEventListener('click', async function() {
    var submitBtn = this;
    var originalHtml = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Saving...';

    var allowedFeatures = [];
    document.querySelectorAll('input[name="allowed_features[]"]:checked').forEach(function(cb) {
        allowedFeatures.push(cb.value);
    });

    var selectedMethod = document.querySelector('input[name="login_method"]:checked');
    var data = {
        enabled: document.getElementById('portal_enabled').checked,
        login_method: selectedMethod ? selectedMethod.value : 'otp',
        session_timeout_days: parseInt(document.getElementById('session_timeout_days').value) || 30,
        activation_code_expiry_minutes: parseInt(document.getElementById('activation_code_expiry_minutes').value) || 10,
        max_otp_resend_per_hour: parseInt(document.getElementById('max_otp_resend_per_hour').value) || 3,
        max_login_attempts: parseInt(document.getElementById('max_login_attempts').value) || 10,
        lockout_duration_minutes: parseInt(document.getElementById('lockout_duration_minutes').value) || 30,
        require_email_verification: false,
        allowed_features: allowedFeatures,
    };

    try {
        var response = await fetch('{{ route("settings.member-portal.update") }}', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify(data),
        });
        var result = await response.json();
        showToast(result.success ? 'Portal settings saved' : (result.message || 'Failed to save'), result.success ? 'success' : 'error');
    } catch (error) {
        showToast('An error occurred while saving', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
    }
});
</script>
@endpush
@endsection
