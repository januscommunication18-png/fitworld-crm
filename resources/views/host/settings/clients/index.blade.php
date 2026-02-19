@extends('layouts.settings')

@section('title', 'Client Settings — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Client Settings</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">

    {{-- General Settings --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-2">General Settings</h2>
            <p class="text-base-content/60 text-sm mb-6">Configure how clients are managed in your studio.</p>

            <form id="client-settings-form" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Default Status --}}
                    <div>
                        <label class="label-text" for="default_status">Default Status for New Clients</label>
                        <select id="default_status" name="default_status" class="select select-bordered w-full mt-1">
                            <option value="lead" {{ ($settings['default_status'] ?? 'lead') === 'lead' ? 'selected' : '' }}>Lead</option>
                            <option value="client" {{ ($settings['default_status'] ?? '') === 'client' ? 'selected' : '' }}>Client</option>
                        </select>
                        <p class="text-xs text-base-content/50 mt-1">Status assigned when a new client is added manually.</p>
                    </div>

                    {{-- At Risk Days --}}
                    <div>
                        <label class="label-text" for="at_risk_days">At-Risk Threshold (Days)</label>
                        <input type="number" id="at_risk_days" name="at_risk_days"
                               value="{{ $settings['at_risk_days'] ?? 30 }}"
                               min="7" max="90"
                               class="input input-bordered w-full mt-1">
                        <p class="text-xs text-base-content/50 mt-1">Mark clients as at-risk after this many days without a visit.</p>
                    </div>

                    {{-- Auto Archive Days --}}
                    <div>
                        <label class="label-text" for="auto_archive_days">Auto-Archive After (Days)</label>
                        <input type="number" id="auto_archive_days" name="auto_archive_days"
                               value="{{ $settings['auto_archive_days'] ?? '' }}"
                               min="30" max="365"
                               placeholder="Leave empty to disable"
                               class="input input-bordered w-full mt-1">
                        <p class="text-xs text-base-content/50 mt-1">Automatically archive inactive clients. Leave empty to disable.</p>
                    </div>
                </div>

                <div class="divider"></div>

                {{-- Required Fields --}}
                <div>
                    <h3 class="font-medium mb-4">Required Fields</h3>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="require_phone" value="1"
                                   {{ ($settings['require_phone'] ?? false) ? 'checked' : '' }}
                                   class="checkbox checkbox-primary checkbox-sm">
                            <span>Require phone number</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="require_address" value="1"
                                   {{ ($settings['require_address'] ?? false) ? 'checked' : '' }}
                                   class="checkbox checkbox-primary checkbox-sm">
                            <span>Require address</span>
                        </label>
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

    {{-- Member Portal Settings --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-semibold">Member Portal</h2>
                <span class="badge badge-soft badge-neutral">Coming Soon</span>
            </div>
            <p class="text-base-content/60 text-sm mb-6">Allow members to log in and manage their bookings, view history, and update their profile.</p>

            <div class="space-y-4 opacity-50 pointer-events-none">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="enable_member_portal" value="1"
                           {{ ($settings['enable_member_portal'] ?? false) ? 'checked' : '' }}
                           class="checkbox checkbox-primary checkbox-sm" disabled>
                    <div>
                        <span class="font-medium">Enable Member Portal</span>
                        <p class="text-xs text-base-content/50">Allow clients to access their own portal at your subdomain.</p>
                    </div>
                </label>

                <div class="ml-8 space-y-2">
                    <p class="text-sm font-medium text-base-content/70">Portal Features:</p>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="checkbox checkbox-sm" disabled>
                        <span class="text-sm">View booking history</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="checkbox checkbox-sm" disabled>
                        <span class="text-sm">Book/cancel classes</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="checkbox checkbox-sm" disabled>
                        <span class="text-sm">Update profile information</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="checkbox checkbox-sm" disabled>
                        <span class="text-sm">View membership status</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- Lead Scoring --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-semibold">Lead Scoring</h2>
                <span class="badge badge-soft badge-neutral">Coming Soon</span>
            </div>
            <p class="text-base-content/60 text-sm mb-6">Automatically score leads based on their engagement and likelihood to convert.</p>

            <div class="opacity-50 pointer-events-none">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="enable_lead_scoring" value="1"
                           {{ ($settings['enable_lead_scoring'] ?? false) ? 'checked' : '' }}
                           class="checkbox checkbox-primary checkbox-sm" disabled>
                    <div>
                        <span class="font-medium">Enable Lead Scoring</span>
                        <p class="text-xs text-base-content/50">Automatically calculate a score for each lead based on their activity.</p>
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- Custom Fields Link --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Custom Fields</h2>
                    <p class="text-base-content/60 text-sm mt-1">Add custom fields to collect additional information from clients.</p>
                </div>
                <span class="badge badge-soft badge-neutral">Coming Soon</span>
            </div>
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

document.getElementById('client-settings-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalHtml = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Saving...';

    const formData = new FormData(form);
    const data = {
        default_status: formData.get('default_status'),
        at_risk_days: parseInt(formData.get('at_risk_days')) || 30,
        auto_archive_days: formData.get('auto_archive_days') ? parseInt(formData.get('auto_archive_days')) : null,
        require_phone: formData.has('require_phone'),
        require_address: formData.has('require_address'),
        enable_member_portal: formData.has('enable_member_portal'),
        enable_lead_scoring: formData.has('enable_lead_scoring'),
    };

    try {
        const response = await fetch('{{ route("settings.clients.update") }}', {
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
            showToast('Settings saved successfully', 'success');
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
