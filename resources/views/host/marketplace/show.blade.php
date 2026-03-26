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

@elseif($feature->slug === 'fitnearyou-sync')
{{-- FitNearYou Sync Feature Layout --}}
<div class="max-w-5xl mx-auto space-y-6">
    {{-- Hero Header --}}
    <div class="card bg-base-100 overflow-hidden">
        <div class="card-body p-8">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-start gap-5">
                    <div class="p-4 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-lg">
                        <span class="icon-[tabler--cloud-share] size-12 text-white"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-3xl font-bold text-base-content">{{ $feature->name }}</h1>
                            <span class="badge badge-success badge-soft">Free</span>
                        </div>
                        <p class="text-base-content/70 mt-2 max-w-xl">{{ $feature->description }}</p>
                        <div class="flex items-center gap-3 mt-4 flex-wrap">
                            <span class="badge badge-primary badge-soft">
                                <span class="icon-[tabler--calendar] size-3.5 mr-1"></span>
                                Classes & Services
                            </span>
                            <span class="badge badge-secondary badge-soft">
                                <span class="icon-[tabler--discount] size-3.5 mr-1"></span>
                                Deals & Promotions
                            </span>
                            <span class="badge badge-warning badge-soft">
                                <span class="icon-[tabler--calendar-event] size-3.5 mr-1"></span>
                                Events
                            </span>
                            <span class="badge badge-info badge-soft">
                                <span class="icon-[tabler--clock] size-3.5 mr-1"></span>
                                Schedule
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Enable/Disable Button --}}
                <div class="flex flex-col items-end gap-2">
                    @if($requiresUpgrade)
                        <a href="{{ route('settings.billing.plan') }}" class="btn btn-warning">
                            <span class="icon-[tabler--crown] size-5"></span>
                            Upgrade
                        </a>
                    @else
                        @if($isEnabled)
                            <button type="button" id="feature-toggle-btn"
                                class="btn btn-success"
                                data-feature-id="{{ $feature->id }}" data-enabled="true">
                                <span class="icon-[tabler--circle-check] size-5"></span>
                                Enabled
                            </button>
                            <span class="text-xs text-base-content/50" id="status-badge">Click to disable</span>
                        @else
                            <button type="button" id="feature-toggle-btn"
                                class="btn btn-primary"
                                data-feature-id="{{ $feature->id }}" data-enabled="false">
                                <span class="icon-[tabler--power] size-5"></span>
                                Enable Feature
                            </button>
                            <span class="text-xs text-base-content/50" id="status-badge">Click to enable</span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: API Credentials --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- API Credentials Card --}}
            <div class="card bg-base-100 {{ !$isEnabled ? 'opacity-50 pointer-events-none' : '' }}" id="credentials-card">
                <div class="card-header border-b border-base-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <span class="icon-[tabler--key] size-5 text-primary"></span>
                                </span>
                                API Credentials
                            </h2>
                            <p class="text-sm text-base-content/60 mt-1">
                                Use these credentials in FitNearYou to sync your studio data.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $hasCredentials = !empty($config['api_key']);
                    @endphp

                    @if($hasCredentials)
                        {{-- Existing Credentials --}}
                        <div class="space-y-4">
                            <div class="form-control">
                                <label class="label" for="api-key">
                                    <span class="label-text font-medium">API Key</span>
                                </label>
                                <div class="join w-full">
                                    <input type="text" id="api-key" value="{{ $config['api_key'] }}" readonly
                                        class="input input-bordered join-item flex-1 font-mono text-sm bg-base-200">
                                    <button type="button" class="btn btn-square join-item copy-btn" data-target="api-key" title="Copy">
                                        <span class="icon-[tabler--copy] size-5"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">API Secret</span>
                                </label>
                                {{-- Hidden Secret Display (shows after verification) --}}
                                <div id="api-secret-container" class="hidden">
                                    <div class="join w-full">
                                        <input type="text" id="api-secret-revealed" readonly
                                            class="input input-bordered join-item flex-1 font-mono text-sm bg-base-200">
                                        <button type="button" class="btn btn-square join-item copy-btn" data-target="api-secret-revealed" title="Copy">
                                            <span class="icon-[tabler--copy] size-5"></span>
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2 mt-2 text-warning">
                                        <span class="icon-[tabler--clock] size-4"></span>
                                        <span class="text-sm" id="secret-countdown">Visible for 2:00</span>
                                    </div>
                                </div>
                                {{-- View Secret Button --}}
                                <div id="view-secret-btn-container">
                                    <button type="button" id="view-secret-btn" class="btn btn-outline btn-primary w-full">
                                        <span class="icon-[tabler--eye] size-5"></span>
                                        View API Secret
                                    </button>
                                    <p class="text-xs text-base-content/50 mt-2 text-center">
                                        A verification code will be sent to your email
                                    </p>
                                </div>
                            </div>

                            @if(!empty($config['credentials_generated_at']))
                            <div class="text-sm text-base-content/50">
                                <span class="icon-[tabler--clock] size-4 inline-block mr-1"></span>
                                Generated: {{ \Carbon\Carbon::parse($config['credentials_generated_at'])->format('M j, Y \a\t g:i A') }}
                            </div>
                            @endif

                            <div class="divider"></div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-sm">Regenerate Credentials</p>
                                    <p class="text-xs text-base-content/50">This will invalidate your current credentials.</p>
                                </div>
                                <button type="button" id="regenerate-credentials-btn" class="btn btn-outline btn-error btn-sm">
                                    <span class="icon-[tabler--refresh] size-4"></span>
                                    Regenerate
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- No Credentials Yet --}}
                        <div class="text-center py-8">
                            <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                                <span class="icon-[tabler--key-off] size-10 text-primary/50"></span>
                            </div>
                            <h3 class="text-lg font-semibold">No Credentials Generated</h3>
                            <p class="text-base-content/60 mt-1 max-w-sm mx-auto">
                                Generate your API credentials to start syncing your studio data with FitNearYou.
                            </p>
                            <button type="button" id="generate-credentials-btn" class="btn btn-primary mt-4">
                                <span class="icon-[tabler--key] size-5"></span>
                                Generate API Credentials
                            </button>
                        </div>
                    @endif

                    {{-- Credentials Display Modal (shown after generation) --}}
                    <div id="credentials-display" class="hidden mt-6">
                        <div class="p-4 bg-success/10 border border-success/30 rounded-lg mb-4">
                            <div class="flex items-start gap-2">
                                <span class="icon-[tabler--circle-check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                                <div>
                                    <p class="font-semibold text-success">Credentials Generated Successfully!</p>
                                    <p class="text-sm text-base-content/70 mt-1">Copy your API secret now. It will only be shown once.</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">API Key</span>
                                </label>
                                <div class="join w-full">
                                    <input type="text" id="new-api-key" readonly
                                        class="input input-bordered join-item flex-1 font-mono text-sm bg-base-200">
                                    <button type="button" class="btn btn-square join-item copy-btn" data-target="new-api-key" title="Copy">
                                        <span class="icon-[tabler--copy] size-5"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">API Secret</span>
                                    <span class="label-text-alt text-error">Copy now - shown only once!</span>
                                </label>
                                <div class="join w-full">
                                    <input type="text" id="new-api-secret" readonly
                                        class="input input-bordered join-item flex-1 font-mono text-sm bg-base-200">
                                    <button type="button" class="btn btn-square join-item copy-btn" data-target="new-api-secret" title="Copy">
                                        <span class="icon-[tabler--copy] size-5"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- How to Use Card --}}
            <div class="card bg-base-100 {{ !$isEnabled ? 'opacity-50 pointer-events-none' : '' }}">
                <div class="card-header border-b border-base-200 p-5">
                    <h2 class="text-lg font-bold flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-info/10 flex items-center justify-center">
                            <span class="icon-[tabler--help-hexagon] size-5 text-info"></span>
                        </span>
                        How to Connect with FitNearYou
                    </h2>
                </div>
                <div class="card-body">
                    <ol class="space-y-4">
                        <li class="flex gap-3">
                            <span class="w-6 h-6 rounded-full bg-primary text-primary-content text-sm font-bold flex items-center justify-center flex-shrink-0">1</span>
                            <div>
                                <p class="font-medium">Generate API Credentials</p>
                                <p class="text-sm text-base-content/60">Click the button above to generate your unique API key and secret.</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="w-6 h-6 rounded-full bg-primary text-primary-content text-sm font-bold flex items-center justify-center flex-shrink-0">2</span>
                            <div>
                                <p class="font-medium">Sign Up on FitNearYou</p>
                                <p class="text-sm text-base-content/60">Create or log into your studio account at <a href="https://fitcrm.club/" target="_blank" class="link link-primary">fitcrm.club</a></p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="w-6 h-6 rounded-full bg-primary text-primary-content text-sm font-bold flex items-center justify-center flex-shrink-0">3</span>
                            <div>
                                <p class="font-medium">Enter Your Credentials</p>
                                <p class="text-sm text-base-content/60">Go to Settings → FitCRM Sync and enter your API key and secret.</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="w-6 h-6 rounded-full bg-primary text-primary-content text-sm font-bold flex items-center justify-center flex-shrink-0">4</span>
                            <div>
                                <p class="font-medium">Sync Your Data</p>
                                <p class="text-sm text-base-content/60">Your classes, services, deals, and events will sync to FitNearYou in draft status.</p>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        {{-- Right Column: Sync Settings --}}
        <div class="space-y-6">
            {{-- Sync Settings Card --}}
            <div class="card bg-base-100 {{ !$isEnabled ? 'opacity-50 pointer-events-none' : '' }}" id="sync-settings-card">
                <div class="card-header border-b border-base-200 p-5">
                    <h2 class="text-lg font-bold flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-secondary/10 flex items-center justify-center">
                            <span class="icon-[tabler--settings] size-5 text-secondary"></span>
                        </span>
                        Sync Settings
                    </h2>
                </div>
                <div class="card-body">
                    <form id="config-form" class="space-y-4">
                        <div class="form-control">
                            <label class="cursor-pointer flex items-center justify-between p-3 bg-base-200/50 rounded-lg hover:bg-base-200 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--calendar] size-5 text-primary"></span>
                                    <span class="label-text font-medium">Sync Classes</span>
                                </div>
                                <input type="checkbox" name="sync_classes" class="toggle toggle-primary toggle-sm"
                                    {{ ($config['sync_classes'] ?? true) ? 'checked' : '' }}>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="cursor-pointer flex items-center justify-between p-3 bg-base-200/50 rounded-lg hover:bg-base-200 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--briefcase] size-5 text-secondary"></span>
                                    <span class="label-text font-medium">Sync Services</span>
                                </div>
                                <input type="checkbox" name="sync_services" class="toggle toggle-primary toggle-sm"
                                    {{ ($config['sync_services'] ?? true) ? 'checked' : '' }}>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="cursor-pointer flex items-center justify-between p-3 bg-base-200/50 rounded-lg hover:bg-base-200 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--discount] size-5 text-success"></span>
                                    <span class="label-text font-medium">Sync Deals</span>
                                </div>
                                <input type="checkbox" name="sync_deals" class="toggle toggle-primary toggle-sm"
                                    {{ ($config['sync_deals'] ?? true) ? 'checked' : '' }}>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="cursor-pointer flex items-center justify-between p-3 bg-base-200/50 rounded-lg hover:bg-base-200 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--calendar-event] size-5 text-warning"></span>
                                    <span class="label-text font-medium">Sync Events</span>
                                </div>
                                <input type="checkbox" name="sync_events" class="toggle toggle-primary toggle-sm"
                                    {{ ($config['sync_events'] ?? true) ? 'checked' : '' }}>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="cursor-pointer flex items-center justify-between p-3 bg-base-200/50 rounded-lg hover:bg-base-200 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--clock] size-5 text-info"></span>
                                    <span class="label-text font-medium">Sync Class Schedule</span>
                                </div>
                                <input type="checkbox" name="sync_schedule" class="toggle toggle-primary toggle-sm"
                                    {{ ($config['sync_schedule'] ?? true) ? 'checked' : '' }}>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-full" id="save-config-btn">
                            <span class="icon-[tabler--check] size-5"></span>
                            Save Settings
                        </button>
                    </form>
                </div>
            </div>

            {{-- Info Card --}}
            <div class="card bg-gradient-to-br from-info/5 to-info/10 border border-info/20">
                <div class="card-body p-5">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--info-circle] size-6 text-info shrink-0"></span>
                        <div>
                            <h3 class="font-semibold text-sm">About Sync</h3>
                            <ul class="text-sm text-base-content/70 mt-2 space-y-1 list-disc list-inside">
                                <li>Data syncs to FitNearYou in draft status</li>
                                <li>Review and publish items on FitNearYou</li>
                                <li>Changes here reflect on next sync</li>
                                <li>Keep credentials secure</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- External Link --}}
            <a href="https://fitcrm.club/" target="_blank" class="btn btn-outline w-full">
                <span class="icon-[tabler--external-link] size-5"></span>
                Visit FitNearYou
            </a>
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

{{-- Verification Code Modal --}}
<div id="verify-secret-modal" class="fixed inset-0 z-[9999] hidden">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" id="modal-backdrop"></div>

    {{-- Modal Content --}}
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-2xl shadow-2xl max-w-md w-full p-6 relative animate-in fade-in zoom-in duration-200">
            <button type="button" id="close-modal-btn" class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3">
                <span class="icon-[tabler--x] size-5"></span>
            </button>

            {{-- Step 1: Request Code --}}
            <div id="modal-step-request">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                        <span class="icon-[tabler--shield-lock] size-8 text-primary"></span>
                    </div>
                    <h3 class="text-lg font-bold">View API Secret</h3>
                    <p class="text-base-content/60 text-sm mt-2">
                        For security, we'll send a verification code to your email.
                    </p>
                </div>
                <button type="button" id="send-code-btn" class="btn btn-primary w-full">
                    <span class="icon-[tabler--mail] size-5"></span>
                    Send Verification Code
                </button>
            </div>

            {{-- Step 2: Enter Code --}}
            <div id="modal-step-verify" class="hidden">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-success/10 flex items-center justify-center mx-auto mb-4">
                        <span class="icon-[tabler--mail-check] size-8 text-success"></span>
                    </div>
                    <h3 class="text-lg font-bold">Enter Verification Code</h3>
                    <p class="text-base-content/60 text-sm mt-2">
                        We've sent a 6-digit code to your email. It expires in 10 minutes.
                    </p>
                </div>
                <div class="form-control mb-4">
                    <input type="text" id="verification-code-input" maxlength="6" placeholder="000000"
                        class="input input-bordered text-center text-2xl font-mono tracking-widest"
                        autocomplete="off">
                    <label class="label">
                        <span class="label-text-alt text-error hidden" id="code-error"></span>
                    </label>
                </div>
                <button type="button" id="verify-code-btn" class="btn btn-primary w-full">
                    <span class="icon-[tabler--check] size-5"></span>
                    Verify Code
                </button>
                <button type="button" id="resend-code-btn" class="btn btn-ghost btn-sm w-full mt-2">
                    Didn't receive the code? Resend
                </button>
            </div>
        </div>
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

    // Toast notification helper
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        const bgClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-error' : type === 'warning' ? 'alert-warning' : 'alert-info';
        const iconClass = type === 'success' ? 'icon-[tabler--check]' : type === 'error' ? 'icon-[tabler--x]' : type === 'warning' ? 'icon-[tabler--alert-triangle]' : 'icon-[tabler--info-circle]';

        toast.className = `alert ${bgClass} fixed top-4 right-4 z-[10000] max-w-sm shadow-lg transition-all duration-300`;
        toast.style.transform = 'translateX(100%)';
        toast.style.opacity = '0';
        toast.innerHTML = `<span class="${iconClass} size-5"></span><span>${message}</span>`;
        document.body.appendChild(toast);

        // Slide in
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        });

        // Slide out after delay
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // Handle FitNearYou feature toggle button
    const featureToggleBtn = document.getElementById('feature-toggle-btn');
    if (featureToggleBtn) {
        featureToggleBtn.addEventListener('click', function() {
            const isCurrentlyEnabled = this.dataset.enabled === 'true';
            const enable = !isCurrentlyEnabled;

            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="loading loading-spinner loading-sm"></span> ' + (enable ? 'Enabling...' : 'Disabling...');
            this.disabled = true;

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
                if (data.success) {
                    showToast(data.message, 'success');
                    // Reload page to show updated state
                    setTimeout(() => location.reload(), 1000);
                } else {
                    this.innerHTML = originalText;
                    this.disabled = false;
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                this.innerHTML = originalText;
                this.disabled = false;
                showToast('An error occurred. Please try again.', 'error');
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
                    if (typeof showToast === 'function') {
                        showToast(data.message, 'success');
                    } else {
                        showAlert('success', data.message);
                    }
                } else {
                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Failed to save configuration.', 'error');
                    } else {
                        showAlert('error', data.message || 'Failed to save configuration.');
                    }
                }
            })
            .catch(error => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                if (typeof showToast === 'function') {
                    showToast('An error occurred. Please try again.', 'error');
                } else {
                    showAlert('error', 'An error occurred. Please try again.');
                }
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

    // FitNearYou - Generate Credentials
    const generateCredentialsBtn = document.getElementById('generate-credentials-btn');
    const regenerateCredentialsBtn = document.getElementById('regenerate-credentials-btn');

    function generateCredentials(isRegenerate = false) {
        const btn = isRegenerate ? regenerateCredentialsBtn : generateCredentialsBtn;
        const originalText = btn.innerHTML;

        if (isRegenerate && !confirm('Are you sure you want to regenerate your API credentials? Your current credentials will be invalidated.')) {
            return;
        }

        btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Generating...';
        btn.disabled = true;

        const url = isRegenerate
            ? '{{ route("marketplace.fitnearyou.regenerate-credentials") }}'
            : '{{ route("marketplace.fitnearyou.generate-credentials") }}';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = originalText;
            btn.disabled = false;

            if (data.success) {
                // Show credentials display
                const credentialsDisplay = document.getElementById('credentials-display');
                if (credentialsDisplay) {
                    document.getElementById('new-api-key').value = data.credentials.api_key;
                    document.getElementById('new-api-secret').value = data.credentials.api_secret;
                    credentialsDisplay.classList.remove('hidden');

                    // Scroll to credentials
                    credentialsDisplay.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                showToast(data.message, 'success');

                // Reload page after 3 seconds to show updated UI
                setTimeout(() => location.reload(), 3000);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            showToast('An error occurred. Please try again.', 'error');
        });
    }

    if (generateCredentialsBtn) {
        generateCredentialsBtn.addEventListener('click', () => generateCredentials(false));
    }

    if (regenerateCredentialsBtn) {
        regenerateCredentialsBtn.addEventListener('click', () => generateCredentials(true));
    }

    // Copy to Clipboard functionality
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            if (input) {
                navigator.clipboard.writeText(input.value).then(() => {
                    // Show copied feedback
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '<span class="icon-[tabler--check] size-5 text-success"></span>';
                    setTimeout(() => {
                        this.innerHTML = originalHtml;
                    }, 2000);
                });
            }
        });
    });

    // View Secret Flow
    const viewSecretBtn = document.getElementById('view-secret-btn');
    const verifySecretModal = document.getElementById('verify-secret-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const modalBackdrop = document.getElementById('modal-backdrop');
    const sendCodeBtn = document.getElementById('send-code-btn');
    const verifyCodeBtn = document.getElementById('verify-code-btn');
    const resendCodeBtn = document.getElementById('resend-code-btn');
    const modalStepRequest = document.getElementById('modal-step-request');
    const modalStepVerify = document.getElementById('modal-step-verify');
    const verificationCodeInput = document.getElementById('verification-code-input');
    const codeError = document.getElementById('code-error');
    let secretCountdownTimer = null;

    function openModal() {
        verifySecretModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        verifySecretModal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    if (viewSecretBtn) {
        viewSecretBtn.addEventListener('click', function() {
            // Reset modal to initial state
            modalStepRequest.classList.remove('hidden');
            modalStepVerify.classList.add('hidden');
            if (verificationCodeInput) verificationCodeInput.value = '';
            if (codeError) {
                codeError.classList.add('hidden');
                codeError.textContent = '';
            }
            openModal();
        });
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    if (modalBackdrop) {
        modalBackdrop.addEventListener('click', closeModal);
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && verifySecretModal && !verifySecretModal.classList.contains('hidden')) {
            closeModal();
        }
    });

    if (sendCodeBtn) {
        sendCodeBtn.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Sending...';
            this.disabled = true;

            fetch('{{ route("marketplace.fitnearyou.send-secret-code") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                this.innerHTML = originalText;
                this.disabled = false;

                if (data.success) {
                    showToast(data.message, 'success');
                    // Show verification step
                    modalStepRequest.classList.add('hidden');
                    modalStepVerify.classList.remove('hidden');
                    verificationCodeInput.focus();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                this.innerHTML = originalText;
                this.disabled = false;
                showToast('An error occurred. Please try again.', 'error');
            });
        });
    }

    if (resendCodeBtn) {
        resendCodeBtn.addEventListener('click', function() {
            sendCodeBtn.click();
        });
    }

    if (verifyCodeBtn) {
        verifyCodeBtn.addEventListener('click', function() {
            const code = verificationCodeInput.value.trim();

            if (code.length !== 6) {
                codeError.textContent = 'Please enter a 6-digit code';
                codeError.classList.remove('hidden');
                return;
            }

            const originalText = this.innerHTML;
            this.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Verifying...';
            this.disabled = true;
            codeError.classList.add('hidden');

            fetch('{{ route("marketplace.fitnearyou.verify-secret-code") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ code: code })
            })
            .then(response => response.json())
            .then(data => {
                this.innerHTML = originalText;
                this.disabled = false;

                if (data.success) {
                    // Close modal
                    closeModal();

                    // Show the secret
                    const secretContainer = document.getElementById('api-secret-container');
                    const secretInput = document.getElementById('api-secret-revealed');
                    const viewSecretBtnContainer = document.getElementById('view-secret-btn-container');
                    const countdownEl = document.getElementById('secret-countdown');

                    if (secretContainer && secretInput) {
                        secretInput.value = data.api_secret;
                        secretContainer.classList.remove('hidden');
                        viewSecretBtnContainer.classList.add('hidden');

                        // Start countdown (2 minutes = 120 seconds)
                        let timeLeft = data.expires_in || 120;

                        function updateCountdown() {
                            const minutes = Math.floor(timeLeft / 60);
                            const seconds = timeLeft % 60;
                            countdownEl.textContent = `Visible for ${minutes}:${seconds.toString().padStart(2, '0')}`;

                            if (timeLeft <= 0) {
                                // Hide the secret
                                secretContainer.classList.add('hidden');
                                viewSecretBtnContainer.classList.remove('hidden');
                                secretInput.value = '';
                                clearInterval(secretCountdownTimer);
                                showToast('API secret hidden for security', 'info');
                            }
                            timeLeft--;
                        }

                        updateCountdown();
                        secretCountdownTimer = setInterval(updateCountdown, 1000);

                        // Scroll to the secret
                        secretContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }

                    showToast(data.message, 'success');
                } else {
                    codeError.textContent = data.message;
                    codeError.classList.remove('hidden');
                }
            })
            .catch(error => {
                this.innerHTML = originalText;
                this.disabled = false;
                showToast('An error occurred. Please try again.', 'error');
            });
        });
    }

    // Allow Enter key to submit code
    if (verificationCodeInput) {
        verificationCodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                verifyCodeBtn.click();
            }
        });

        // Only allow numeric input
        verificationCodeInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
});
</script>
@endpush
@endsection
