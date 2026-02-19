@extends('layouts.settings')

@section('title', 'Member Portal Settings — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Member Portal</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">

    {{-- Enable/Disable Member Portal --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Member Portal</h2>
                    <p class="text-base-content/60 text-sm mt-1">Allow members to log in and access their bookings, payments, and profile at your studio subdomain.</p>
                </div>
                <label class="flex cursor-pointer gap-2 items-center">
                    <input type="checkbox" id="portal_enabled" name="enabled"
                           value="1"
                           {{ ($settings['enabled'] ?? false) ? 'checked' : '' }}
                           class="toggle toggle-primary">
                    <span class="label-text font-medium" id="portal_status_label">
                        {{ ($settings['enabled'] ?? false) ? 'Enabled' : 'Disabled' }}
                    </span>
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
            <div class="mt-4 p-3 bg-base-200 rounded-lg">
                <p class="text-sm text-base-content/70">
                    <span class="icon-[tabler--link] size-4 inline-block align-middle mr-1"></span>
                    Member Portal URL:
                    <a href="{{ $portalUrl }}" target="_blank" class="link link-primary font-medium">
                        {{ $host->subdomain }}.{{ $bookingDomain }}{{ $port }}/login
                    </a>
                </p>
            </div>
            @endif
        </div>
    </div>

    {{-- Login Method --}}
    <div class="card bg-base-100" id="portal_settings_section">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-2">Login Method</h2>
            <p class="text-base-content/60 text-sm mb-6">Choose how members will authenticate to access the portal.</p>

            <div class="space-y-4">
                {{-- OTP Option --}}
                <label class="flex items-start gap-4 p-4 rounded-lg border border-base-300 cursor-pointer hover:bg-base-50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                    <input type="radio" name="login_method" value="otp"
                           {{ ($settings['login_method'] ?? 'otp') === 'otp' ? 'checked' : '' }}
                           class="radio radio-primary mt-1">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">Email + Activation Code (OTP)</span>
                            <span class="badge badge-soft badge-success badge-sm">Recommended</span>
                        </div>
                        <p class="text-sm text-base-content/60 mt-1">
                            Members enter their email and receive a one-time code to log in. No password to remember, more secure against credential theft.
                        </p>
                    </div>
                </label>

                {{-- Password Option --}}
                <label class="flex items-start gap-4 p-4 rounded-lg border border-base-300 cursor-pointer hover:bg-base-50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                    <input type="radio" name="login_method" value="password"
                           {{ ($settings['login_method'] ?? 'otp') === 'password' ? 'checked' : '' }}
                           class="radio radio-primary mt-1">
                    <div class="flex-1">
                        <span class="font-medium">Email + Password</span>
                        <p class="text-sm text-base-content/60 mt-1">
                            Members set a password during registration and use it to log in. Includes forgot password flow via email.
                        </p>
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- Session & Security Settings --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-2">Session & Security</h2>
            <p class="text-base-content/60 text-sm mb-6">Configure session duration and security settings for the member portal.</p>

            <form id="member-portal-form" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Session Timeout --}}
                    <div>
                        <label class="label-text" for="session_timeout_days">Session Timeout</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="number" id="session_timeout_days" name="session_timeout_days"
                                   value="{{ $settings['session_timeout_days'] ?? 30 }}"
                                   min="1" max="90"
                                   class="input input-bordered w-24">
                            <span class="text-base-content/60">days</span>
                        </div>
                        <p class="text-xs text-base-content/50 mt-1">How long members stay logged in before requiring re-authentication (1-90 days).</p>
                    </div>

                    {{-- Activation Code Expiry --}}
                    <div id="otp_settings">
                        <label class="label-text" for="activation_code_expiry_minutes">Activation Code Expiry</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="number" id="activation_code_expiry_minutes" name="activation_code_expiry_minutes"
                                   value="{{ $settings['activation_code_expiry_minutes'] ?? 10 }}"
                                   min="5" max="60"
                                   class="input input-bordered w-24">
                            <span class="text-base-content/60">minutes</span>
                        </div>
                        <p class="text-xs text-base-content/50 mt-1">How long the OTP code remains valid (5-60 minutes).</p>
                    </div>

                    {{-- Max OTP Resend --}}
                    <div id="otp_resend_settings">
                        <label class="label-text" for="max_otp_resend_per_hour">Max Code Resends</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="number" id="max_otp_resend_per_hour" name="max_otp_resend_per_hour"
                                   value="{{ $settings['max_otp_resend_per_hour'] ?? 3 }}"
                                   min="1" max="10"
                                   class="input input-bordered w-24">
                            <span class="text-base-content/60">per hour</span>
                        </div>
                        <p class="text-xs text-base-content/50 mt-1">Maximum number of code resend requests allowed per hour.</p>
                    </div>

                    {{-- Max Login Attempts --}}
                    <div>
                        <label class="label-text" for="max_login_attempts">Max Login Attempts</label>
                        <input type="number" id="max_login_attempts" name="max_login_attempts"
                               value="{{ $settings['max_login_attempts'] ?? 10 }}"
                               min="3" max="20"
                               class="input input-bordered w-full mt-1">
                        <p class="text-xs text-base-content/50 mt-1">Lock account after this many failed attempts (3-20).</p>
                    </div>

                    {{-- Lockout Duration --}}
                    <div>
                        <label class="label-text" for="lockout_duration_minutes">Lockout Duration</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="number" id="lockout_duration_minutes" name="lockout_duration_minutes"
                                   value="{{ $settings['lockout_duration_minutes'] ?? 30 }}"
                                   min="5" max="120"
                                   class="input input-bordered w-24">
                            <span class="text-base-content/60">minutes</span>
                        </div>
                        <p class="text-xs text-base-content/50 mt-1">How long accounts are locked after too many failed attempts.</p>
                    </div>
                </div>

                <div class="divider"></div>

                {{-- Portal Features --}}
                <div>
                    <h3 class="font-medium mb-4">Portal Features</h3>
                    <p class="text-sm text-base-content/60 mb-4">Select which features members can access in their portal.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
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

                        @foreach($allFeatures as $key => $feature)
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-base-300 cursor-pointer hover:bg-base-50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
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

                <div class="flex justify-end pt-4">
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--check] size-5"></span>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
// Toast notification function
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
    const portalToggle = document.getElementById('portal_enabled');
    const portalStatusLabel = document.getElementById('portal_status_label');
    const settingsSection = document.getElementById('portal_settings_section');
    const otpSettings = document.getElementById('otp_settings');
    const otpResendSettings = document.getElementById('otp_resend_settings');
    const loginMethodInputs = document.querySelectorAll('input[name="login_method"]');

    // Toggle status label
    portalToggle.addEventListener('change', function() {
        portalStatusLabel.textContent = this.checked ? 'Enabled' : 'Disabled';
    });

    // Toggle OTP-specific settings based on login method
    function toggleOtpSettings() {
        const selectedMethod = document.querySelector('input[name="login_method"]:checked')?.value || 'otp';
        const isOtp = selectedMethod === 'otp';
        otpSettings.style.display = isOtp ? '' : 'none';
        otpResendSettings.style.display = isOtp ? '' : 'none';
    }

    loginMethodInputs.forEach(input => {
        input.addEventListener('change', toggleOtpSettings);
    });

    // Initial state
    toggleOtpSettings();
});

// Form submission
document.getElementById('member-portal-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalHtml = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Saving...';

    const formData = new FormData(form);

    // Build the data object
    const allowedFeatures = [];
    document.querySelectorAll('input[name="allowed_features[]"]:checked').forEach(cb => {
        allowedFeatures.push(cb.value);
    });

    const data = {
        enabled: document.getElementById('portal_enabled').checked,
        login_method: document.querySelector('input[name="login_method"]:checked')?.value || 'otp',
        session_timeout_days: parseInt(document.getElementById('session_timeout_days').value) || 30,
        activation_code_expiry_minutes: parseInt(document.getElementById('activation_code_expiry_minutes').value) || 10,
        max_otp_resend_per_hour: parseInt(document.getElementById('max_otp_resend_per_hour').value) || 3,
        max_login_attempts: parseInt(document.getElementById('max_login_attempts').value) || 10,
        lockout_duration_minutes: parseInt(document.getElementById('lockout_duration_minutes').value) || 30,
        require_email_verification: false,
        allowed_features: allowedFeatures,
    };

    try {
        const response = await fetch('{{ route("settings.member-portal.update") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        });

        const result = await response.json();

        if (result.success) {
            showToast('Member portal settings saved successfully', 'success');
        } else {
            showToast(result.message || 'Failed to save settings', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred while saving settings', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
    }
});
</script>
@endpush
@endsection
