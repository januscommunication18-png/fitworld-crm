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
{{-- Alert Container --}}
<div id="feature-alert" class="alert hidden max-w-5xl mx-auto mb-6">
    <span id="alert-icon" class="size-5"></span>
    <span id="alert-message"></span>
</div>

{{-- Special Layout for 1:1 Meeting Feature --}}
@if($feature->slug === 'online-1on1-meeting')
<div class="max-w-6xl mx-auto space-y-6">
    {{-- Hero Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary to-secondary text-primary-content overflow-hidden">
        <div class="card-body p-8 relative">
            {{-- Background Pattern --}}
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full translate-y-1/2 -translate-x-1/2"></div>
            </div>

            <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-start gap-5">
                    <div class="p-4 bg-white/20 backdrop-blur-sm rounded-2xl shadow-lg">
                        <span class="icon-[tabler--calendar-user] size-12"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-3xl font-bold">{{ $feature->name }}</h1>
                            <span class="badge badge-soft bg-white/20 border-0 text-white">Free</span>
                        </div>
                        <p class="text-primary-content/80 mt-2 max-w-xl">{{ $feature->description }}</p>
                        <div class="flex items-center gap-4 mt-4">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="icon-[tabler--calendar-check] size-4"></span>
                                <span>Personal booking pages</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <span class="icon-[tabler--mail] size-4"></span>
                                <span>Email confirmations</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <span class="icon-[tabler--clock] size-4"></span>
                                <span>Availability management</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Toggle --}}
                <div class="flex items-center gap-4 bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3">
                    <div class="text-right">
                        <p class="text-sm font-medium" id="status-text">{{ $isEnabled ? 'Feature Enabled' : 'Feature Disabled' }}</p>
                        <span class="text-xs text-primary-content/70" id="status-badge">{{ $isEnabled ? 'Active and ready to use' : 'Toggle to enable' }}</span>
                    </div>
                    @if($requiresUpgrade)
                        <a href="{{ route('settings.billing.plan') }}" class="btn btn-warning">
                            <span class="icon-[tabler--crown] size-5"></span>
                            Upgrade
                        </a>
                    @else
                        <input type="checkbox"
                            class="toggle toggle-lg bg-white/30 border-white/50 checked:bg-success checked:border-success"
                            id="feature-toggle"
                            data-feature-id="{{ $feature->id }}"
                            {{ $isEnabled ? 'checked' : '' }}
                        >
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Team Members --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Team Members Card --}}
            <div class="card bg-base-100 {{ !$isEnabled ? 'opacity-50 pointer-events-none' : '' }}" id="members-card">
                <div class="card-header border-b border-base-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <span class="icon-[tabler--users] size-5 text-primary"></span>
                                </span>
                                Team Members
                            </h2>
                            <p class="text-sm text-base-content/60 mt-1">
                                Grant 1:1 booking access to your team. They'll receive an email to set up their profile.
                            </p>
                        </div>
                        @if(isset($instructors) && count($instructors) > 0)
                        <div class="badge badge-primary badge-outline">
                            {{ collect($instructors)->where('has_access', true)->count() }} / {{ count($instructors) }} enabled
                        </div>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(!isset($instructors) || count($instructors) === 0)
                        <div class="p-8 text-center">
                            <span class="icon-[tabler--users-plus] size-16 text-base-content/20 mx-auto"></span>
                            <h3 class="text-lg font-semibold mt-4">No Team Members</h3>
                            <p class="text-base-content/60 mt-1">Add instructors first to enable 1:1 bookings.</p>
                            <a href="{{ route('instructors.create') }}" class="btn btn-primary btn-sm mt-4">
                                <span class="icon-[tabler--plus] size-4"></span>
                                Add Instructor
                            </a>
                        </div>
                    @else
                        <div class="divide-y divide-base-200">
                            @foreach($instructors as $item)
                                @php
                                    $instructor = $item['instructor'];
                                    $hasAccess = $item['has_access'];
                                    $isSetupComplete = $item['is_setup_complete'];
                                    $profile = $item['profile'];
                                @endphp
                                <div class="p-4 hover:bg-base-50 transition-colors" data-instructor-id="{{ $instructor->id }}">
                                    <div class="flex items-center gap-4">
                                        {{-- Avatar --}}
                                        <div class="relative">
                                            @if($instructor->photo_url)
                                                <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}" class="w-12 h-12 rounded-full object-cover ring-2 ring-base-200">
                                            @else
                                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center ring-2 ring-base-200">
                                                    <span class="text-sm font-bold text-primary">{{ $instructor->initials }}</span>
                                                </div>
                                            @endif
                                            @if($hasAccess && $isSetupComplete)
                                                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-success rounded-full flex items-center justify-center ring-2 ring-base-100">
                                                    <span class="icon-[tabler--check] size-3 text-success-content"></span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Info --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="font-semibold text-base-content">{{ $instructor->name }}</div>
                                            <div class="text-sm text-base-content/60 truncate">{{ $instructor->email ?? 'No email' }}</div>
                                        </div>

                                        {{-- Status Badge --}}
                                        <div class="hidden sm:block">
                                            @if($hasAccess)
                                                @if($isSetupComplete)
                                                    <span class="badge badge-success badge-soft">
                                                        <span class="icon-[tabler--circle-check] size-3.5 mr-1"></span>
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning badge-soft">
                                                        <span class="icon-[tabler--clock] size-3.5 mr-1"></span>
                                                        Pending Setup
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge badge-neutral badge-soft">No Access</span>
                                            @endif
                                        </div>

                                        {{-- Actions --}}
                                        <div class="flex items-center gap-1">
                                            @if($hasAccess)
                                                @if(!$isSetupComplete)
                                                    <button class="btn btn-ghost btn-sm btn-square text-primary resend-invite-btn" data-profile-id="{{ $profile->id }}" title="Resend Invitation">
                                                        <span class="icon-[tabler--mail-forward] size-5"></span>
                                                    </button>
                                                @endif
                                                @if($isSetupComplete && $profile)
                                                    <a href="{{ $profile->getPublicUrl() }}" target="_blank" class="btn btn-ghost btn-sm btn-square" title="View Booking Page">
                                                        <span class="icon-[tabler--external-link] size-5"></span>
                                                    </a>
                                                @endif
                                                <button class="btn btn-ghost btn-sm btn-square text-error revoke-access-btn" data-profile-id="{{ $profile->id }}" data-instructor-name="{{ $instructor->name }}" title="Revoke Access">
                                                    <span class="icon-[tabler--user-minus] size-5"></span>
                                                </button>
                                            @else
                                                <button class="btn btn-primary btn-sm grant-access-btn" data-instructor-id="{{ $instructor->id }}">
                                                    <span class="icon-[tabler--user-plus] size-4 mr-1"></span>
                                                    Grant Access
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Stats (only show if enabled) --}}
            @if($isEnabled && isset($instructors))
            @php
                $activeCount = collect($instructors)->where('has_access', true)->where('is_setup_complete', true)->count();
                $pendingCount = collect($instructors)->where('has_access', true)->where('is_setup_complete', false)->count();
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="card bg-base-100">
                    <div class="card-body p-4 text-center">
                        <span class="icon-[tabler--users] size-8 text-primary mx-auto"></span>
                        <div class="text-2xl font-bold mt-2">{{ count($instructors) }}</div>
                        <div class="text-sm text-base-content/60">Total Members</div>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body p-4 text-center">
                        <span class="icon-[tabler--user-check] size-8 text-success mx-auto"></span>
                        <div class="text-2xl font-bold mt-2">{{ $activeCount }}</div>
                        <div class="text-sm text-base-content/60">Active Profiles</div>
                    </div>
                </div>
                <div class="card bg-base-100 hidden sm:block">
                    <div class="card-body p-4 text-center">
                        <span class="icon-[tabler--clock] size-8 text-warning mx-auto"></span>
                        <div class="text-2xl font-bold mt-2">{{ $pendingCount }}</div>
                        <div class="text-sm text-base-content/60">Pending Setup</div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column: Settings --}}
        <div class="space-y-6">
            {{-- Configure Settings Card --}}
            @if($feature->config_schema)
            <div class="card bg-base-100 {{ !$isEnabled ? 'opacity-50 pointer-events-none' : '' }}" id="config-card">
                <div class="card-header border-b border-base-200 p-5">
                    <h2 class="text-lg font-bold flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-secondary/10 flex items-center justify-center">
                            <span class="icon-[tabler--settings] size-5 text-secondary"></span>
                        </span>
                        Settings
                    </h2>
                </div>
                <div class="card-body">
                    <form id="config-form" class="space-y-5">
                        @foreach($feature->config_schema as $key => $schema)
                            <div class="form-control">
                                <label class="label pb-1" for="config-{{ $key }}">
                                    <span class="label-text font-medium">{{ $schema['label'] ?? ucfirst(str_replace('_', ' ', $key)) }}</span>
                                </label>
                                @if(isset($schema['type']) && $schema['type'] === 'select')
                                    <select id="config-{{ $key }}"
                                        name="{{ $key }}"
                                        class="select select-bordered w-full">
                                        @foreach($schema['options'] ?? [] as $option)
                                            <option value="{{ $option }}" {{ ($config[$key] ?? $schema['default'] ?? '') == $option ? 'selected' : '' }}>
                                                {{ is_numeric($option) ? $option : ucfirst($option) }}
                                            </option>
                                        @endforeach
                                    </select>
                                @elseif(isset($schema['type']) && $schema['type'] === 'boolean')
                                    <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                                        <span class="text-sm text-base-content/70">{{ $schema['description'] ?? '' }}</span>
                                        <input type="checkbox" id="config-{{ $key }}"
                                            name="{{ $key }}"
                                            class="toggle toggle-primary toggle-sm"
                                            {{ ($config[$key] ?? $schema['default'] ?? false) ? 'checked' : '' }}>
                                    </div>
                                @elseif(isset($schema['type']) && $schema['type'] === 'number')
                                    <input type="number" id="config-{{ $key }}"
                                        name="{{ $key }}"
                                        value="{{ $config[$key] ?? $schema['default'] ?? '' }}"
                                        class="input input-bordered w-full">
                                @elseif(isset($schema['type']) && $schema['type'] === 'array')
                                    @php
                                        $arrayValue = $config[$key] ?? $schema['default'] ?? [];
                                        $arrayString = is_array($arrayValue) ? implode(', ', $arrayValue) : $arrayValue;
                                    @endphp
                                    <input type="text" id="config-{{ $key }}"
                                        name="{{ $key }}"
                                        value="{{ $arrayString }}"
                                        class="input input-bordered w-full"
                                        placeholder="e.g., 15, 30, 45, 60">
                                @else
                                    @php
                                        $value = $config[$key] ?? $schema['default'] ?? '';
                                        $displayValue = is_array($value) ? implode(', ', $value) : $value;
                                    @endphp
                                    <input type="text" id="config-{{ $key }}"
                                        name="{{ $key }}"
                                        value="{{ $displayValue }}"
                                        class="input input-bordered w-full">
                                @endif
                                @if(isset($schema['description']) && (!isset($schema['type']) || $schema['type'] !== 'boolean'))
                                    <label class="label pt-1">
                                        <span class="label-text-alt text-base-content/50">{{ $schema['description'] }}</span>
                                    </label>
                                @endif
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary w-full" id="save-config-btn">
                            <span class="icon-[tabler--check] size-5"></span>
                            Save Settings
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Quick Links Card --}}
            <div class="card bg-base-100 {{ !$isEnabled ? 'opacity-50 pointer-events-none' : '' }}">
                <div class="card-header border-b border-base-200 p-5">
                    <h2 class="text-lg font-bold flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-info/10 flex items-center justify-center">
                            <span class="icon-[tabler--link] size-5 text-info"></span>
                        </span>
                        Quick Links
                    </h2>
                </div>
                <div class="card-body space-y-2">
                    <a href="{{ route('one-on-one.index') }}" class="btn btn-ghost justify-start w-full">
                        <span class="icon-[tabler--calendar-event] size-5 text-primary"></span>
                        View All Bookings
                        <span class="icon-[tabler--chevron-right] size-4 ml-auto"></span>
                    </a>
                    <a href="{{ route('instructors.index') }}" class="btn btn-ghost justify-start w-full">
                        <span class="icon-[tabler--users] size-5 text-secondary"></span>
                        Manage Team Members
                        <span class="icon-[tabler--chevron-right] size-4 ml-auto"></span>
                    </a>
                </div>
            </div>

            {{-- Help Card --}}
            <div class="card bg-gradient-to-br from-info/5 to-info/10 border border-info/20">
                <div class="card-body p-5">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--help-circle] size-6 text-info shrink-0"></span>
                        <div>
                            <h3 class="font-semibold text-sm">How it works</h3>
                            <ol class="text-sm text-base-content/70 mt-2 space-y-1 list-decimal list-inside">
                                <li>Grant access to team members</li>
                                <li>They set up their booking profile</li>
                                <li>Clients book via their profile page</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Back Link --}}
    <div>
        <a href="{{ route('marketplace.index') }}" class="btn btn-ghost">
            <span class="icon-[tabler--arrow-left] size-5"></span>
            Back to Marketplace
        </a>
    </div>
</div>

@else
{{-- Default Layout for Other Features --}}
<div class="max-w-3xl mx-auto space-y-6">
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
                    <p class="text-sm text-base-content/60 mt-1" id="status-text-default">
                        {{ $isEnabled ? 'This feature is currently enabled for your studio.' : 'Enable this feature to start using it.' }}
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="badge {{ $isEnabled ? 'badge-success' : 'badge-neutral' }}" id="status-badge-default">
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
                        @elseif(isset($schema['type']) && $schema['type'] === 'array')
                            @php
                                $arrayValue = $config[$key] ?? $schema['default'] ?? [];
                                $arrayString = is_array($arrayValue) ? implode(', ', $arrayValue) : $arrayValue;
                            @endphp
                            <input type="text" id="config-{{ $key }}"
                                name="{{ $key }}"
                                value="{{ $arrayString }}"
                                class="input input-bordered w-full max-w-sm"
                                placeholder="e.g., 15, 30, 45, 60">
                        @else
                            @php
                                $value = $config[$key] ?? $schema['default'] ?? '';
                                $displayValue = is_array($value) ? implode(', ', $value) : $value;
                            @endphp
                            <input type="text" id="config-{{ $key }}"
                                name="{{ $key }}"
                                value="{{ $displayValue }}"
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
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const featureId = {{ $feature->id }};
    const featureSlug = '{{ $feature->slug }}';
    const featureToggle = document.getElementById('feature-toggle');
    const configCard = document.getElementById('config-card');
    const configForm = document.getElementById('config-form');
    const featureActionCard = document.getElementById('feature-action-card');
    const membersCard = document.getElementById('members-card');

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
                    // Update UI based on feature type
                    const statusBadge = document.getElementById('status-badge');
                    const statusText = document.getElementById('status-text');

                    if (featureSlug === 'online-1on1-meeting') {
                        // 1:1 Meeting specific UI updates
                        if (statusText) {
                            statusText.textContent = enable ? 'Feature Enabled' : 'Feature Disabled';
                        }
                        if (statusBadge) {
                            statusBadge.textContent = enable ? 'Active and ready to use' : 'Toggle to enable';
                        }
                    } else {
                        // Default feature UI updates
                        const statusBadgeDefault = document.getElementById('status-badge-default');
                        const statusTextDefault = document.getElementById('status-text-default');
                        const iconContainer = document.getElementById('icon-container');
                        const featureIcon = document.getElementById('feature-icon');

                        if (statusBadgeDefault) {
                            statusBadgeDefault.textContent = enable ? 'Enabled' : 'Disabled';
                            statusBadgeDefault.className = 'badge ' + (enable ? 'badge-success' : 'badge-neutral');
                        }

                        if (statusTextDefault) {
                            statusTextDefault.textContent = enable
                                ? 'This feature is currently enabled for your studio.'
                                : 'Enable this feature to start using it.';
                        }

                        if (iconContainer) {
                            iconContainer.className = 'p-4 rounded-xl ' + (enable ? 'bg-primary/10' : 'bg-base-content/5');
                        }

                        if (featureIcon) {
                            const iconClasses = featureIcon.className.split(' ');
                            const newIconClasses = iconClasses.filter(c => !c.startsWith('text-'));
                            newIconClasses.push(enable ? 'text-primary' : 'text-base-content/40');
                            featureIcon.className = newIconClasses.join(' ');
                        }
                    }

                    // Toggle config card visibility
                    if (configCard) {
                        if (enable) {
                            configCard.classList.remove('opacity-50', 'pointer-events-none');
                        } else {
                            configCard.classList.add('opacity-50', 'pointer-events-none');
                        }
                    }

                    // Toggle members card visibility (for 1:1 meeting)
                    if (membersCard) {
                        if (enable) {
                            membersCard.classList.remove('opacity-50', 'pointer-events-none');
                        } else {
                            membersCard.classList.add('opacity-50', 'pointer-events-none');
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

    // 1:1 Meeting - Grant Access
    document.querySelectorAll('.grant-access-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const instructorId = this.dataset.instructorId;
            const originalText = this.innerHTML;

            this.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Granting...';
            this.disabled = true;

            fetch('{{ route("marketplace.one-on-one.grant-access") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ instructor_id: instructorId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    // Reload page to show updated status
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.innerHTML = originalText;
                    this.disabled = false;
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                this.innerHTML = originalText;
                this.disabled = false;
                showAlert('error', 'An error occurred. Please try again.');
            });
        });
    });

    // 1:1 Meeting - Revoke Access
    document.querySelectorAll('.revoke-access-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const profileId = this.dataset.profileId;
            const instructorName = this.dataset.instructorName;

            if (!confirm(`Are you sure you want to revoke 1:1 booking access for ${instructorName}?`)) {
                return;
            }

            fetch(`{{ url('/marketplace/online-1on1-meeting/revoke-access') }}/${profileId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred. Please try again.');
            });
        });
    });

    // 1:1 Meeting - Resend Invitation
    document.querySelectorAll('.resend-invite-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const profileId = this.dataset.profileId;

            fetch(`{{ url('/marketplace/online-1on1-meeting/resend-invitation') }}/${profileId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                showAlert('error', 'An error occurred. Please try again.');
            });
        });
    });
});
</script>
@endpush
@endsection
