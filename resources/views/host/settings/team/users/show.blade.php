@extends('layouts.settings')

@section('title', $user->full_name . ' — Team Member')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.team.users') }}">Users & Roles</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $user->full_name }}</li>
    </ol>
@endsection

@section('settings-content')
@php
    $userRole = $user->pivot->role ?? $user->role;
    $hasLogin = !is_null($user->password);
    if (!$hasLogin) {
        $statusBadge = 'badge-neutral';
        $statusText = 'No Login';
    } elseif ($user->status === 'active') {
        $statusBadge = 'badge-success';
        $statusText = 'Active';
    } elseif ($user->status === 'invited') {
        $statusBadge = 'badge-warning';
        $statusText = 'Invited';
    } elseif ($user->status === 'suspended') {
        $statusBadge = 'badge-error';
        $statusText = 'Suspended';
    } elseif ($user->status === 'deactivated') {
        $statusBadge = 'badge-neutral';
        $statusText = 'Deactivated';
    } else {
        $statusBadge = '';
        $statusText = ucfirst($user->status);
    }
@endphp
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4 relative z-50">
        <div class="flex items-start gap-4 flex-1">
            <a href="{{ route('settings.team.users') }}" class="btn btn-ghost btn-sm btn-circle mt-1">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div class="avatar placeholder">
                @php
                    $bgColor = match($userRole) {
                        'owner' => 'bg-primary text-primary-content',
                        'admin' => 'bg-secondary text-secondary-content',
                        'staff' => 'bg-info text-info-content',
                        'instructor' => 'bg-accent text-accent-content',
                        default => 'bg-base-300 text-base-content'
                    };
                @endphp
                <div class="{{ $bgColor }} w-20 h-20 rounded-full font-bold text-2xl">
                    <span>{{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}</span>
                </div>
            </div>
            <div>
                <h1 class="text-2xl font-bold">{{ $user->full_name }}</h1>
                <p class="text-base-content/60">{{ $user->email }}</p>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    @php
                        $roleBadge = match($userRole) {
                            'owner' => 'badge-primary',
                            'admin' => 'badge-secondary',
                            'staff' => 'badge-info',
                            'instructor' => 'badge-accent',
                            default => ''
                        };
                    @endphp
                    <span class="badge {{ $roleBadge }} badge-soft">{{ ucfirst($userRole) }}</span>
                    <span class="badge {{ $statusBadge }} badge-soft badge-sm">{{ $statusText }}</span>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        @if($user->id !== auth()->id() && $userRole !== 'owner')
            <div class="flex items-center gap-2">
                @if($hasLogin && $user->status === 'active')
                    <button type="button" onclick="showResetPasswordModal()" class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--key] size-4"></span>
                        Reset Password
                    </button>
                @endif

                <a href="{{ route('settings.team.users.edit', $user) }}" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--edit] size-4"></span>
                    Edit
                </a>

                @if($user->status === 'active')
                    <button type="button" onclick="showSuspendModal()" class="btn btn-warning btn-sm">
                        <span class="icon-[tabler--ban] size-4"></span>
                        Suspend
                    </button>
                @elseif($user->status === 'suspended' || $user->status === 'deactivated')
                    <button type="button" onclick="showReactivateModal()" class="btn btn-success btn-sm">
                        <span class="icon-[tabler--user-check] size-4"></span>
                        Reactivate
                    </button>
                @endif
            </div>
        @endif
    </div>

    {{-- Instructor Link Alert --}}
    @if($instructor)
        <div class="alert alert-soft alert-info">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <div class="flex-1">
                <p>This team member has an instructor profile with employment details, schedule, and class assignments.</p>
            </div>
            <a href="{{ route('instructors.show', $instructor) }}" class="btn btn-info btn-sm">
                <span class="icon-[tabler--user-star] size-4"></span>
                View Instructor Profile
            </a>
        </div>
    @elseif($userRole === 'owner' && $user->id === auth()->id())
        {{-- Add as Instructor option for owner viewing their own profile --}}
        <div class="alert alert-soft alert-primary">
            <span class="icon-[tabler--user-star] size-5"></span>
            <div class="flex-1">
                <p>Want to teach classes? Create an instructor profile to be assigned to classes and appear on your public booking page.</p>
            </div>
            <form action="{{ route('settings.team.users.add-as-instructor', $user) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add Me as Instructor
                </button>
            </form>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="tabs tabs-bordered relative z-10" role="tablist">
        <button class="tab {{ $tab === 'overview' ? 'tab-active' : '' }}" data-tab="overview" role="tab">
            <span class="icon-[tabler--user] size-4 mr-2"></span>
            Overview
        </button>
        <button class="tab {{ $tab === 'notes' ? 'tab-active' : '' }}" data-tab="notes" role="tab">
            <span class="icon-[tabler--notes] size-4 mr-2"></span>
            Notes
            @if($user->notes->count() > 0)
                <span class="badge badge-sm badge-primary ml-2">{{ $user->notes->count() }}</span>
            @endif
        </button>
        <button class="tab {{ $tab === 'billing' ? 'tab-active' : '' }}" data-tab="billing" role="tab">
            <span class="icon-[tabler--wallet] size-4 mr-2"></span>
            Billing
        </button>
    </div>

    {{-- Tab Contents --}}
    <div class="tab-contents relative z-0">
        {{-- Overview Tab --}}
        <div class="tab-content {{ $tab === 'overview' ? 'active' : 'hidden' }}" data-content="overview">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Profile Info --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--user] size-5"></span>
                                Profile Information
                            </h2>
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="text-sm text-base-content/60">First Name</label>
                                    <p class="font-medium">{{ $user->first_name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Last Name</label>
                                    <p class="font-medium">{{ $user->last_name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Email</label>
                                    <p class="font-medium">{{ $user->email }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Phone</label>
                                    <p class="font-medium">{{ $user->phone ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Role</label>
                                    <p class="font-medium">{{ ucfirst($userRole) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Status</label>
                                    <p class="font-medium">{{ $statusText }}</p>
                                </div>
                            </div>

                            {{-- Specialties --}}
                            @if($instructor && !empty($instructor->specialties))
                                <div class="divider my-3"></div>
                                <div>
                                    <label class="text-sm text-base-content/60 block mb-2">Specialties</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($instructor->specialties as $specialty)
                                            <span class="badge badge-soft badge-primary">{{ $specialty }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @php
                        $dayOptions = \App\Models\Instructor::getDayOptions();
                        $dayEmojis = ['☀️', '🌙', '🔥', '💧', '⚡', '🐟', '⭐'];
                        $socialLinks = $user->social_links ?? [];
                        $userCertifications = $user->certifications;
                    @endphp

                    <div class="card bg-base-100 overflow-hidden">

                    {{-- Employment Details --}}
                    @if($instructor)
                    <details class="group show-accordion-section">
                        <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none border-b border-base-200">
                            <span class="icon-[tabler--briefcase] size-5 text-secondary"></span>
                            <h2 class="flex-1 font-semibold">Employment Details</h2>
                            <a href="{{ route('settings.team.users.edit', $user) }}?section=employment" class="btn btn-ghost btn-xs z-10" onclick="event.stopPropagation()"><span class="icon-[tabler--edit] size-4"></span></a>
                            <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                        </summary>
                        <div class="p-5 border-b border-base-200">
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="text-sm text-base-content/60">Employment Type</label><p class="font-medium">{{ $instructor->getFormattedEmploymentType() ?? '-' }}</p></div>
                                <div><label class="text-sm text-base-content/60">Rate</label><p class="font-medium">{{ $instructor->getFormattedRate() ?? '-' }}</p></div>
                                @if($instructor->compensation_notes)
                                <div class="col-span-2"><label class="text-sm text-base-content/60">Compensation Notes</label><p class="font-medium text-sm">{{ $instructor->compensation_notes }}</p></div>
                                @endif
                            </div>
                        </div>
                    </details>
                    @endif

                    {{-- Workload Limits --}}
                    @if($instructor)
                    <details class="group show-accordion-section">
                        <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none border-b border-base-200">
                            <span class="icon-[tabler--chart-bar] size-5 text-warning"></span>
                            <h2 class="flex-1 font-semibold">Workload Limits</h2>
                            <a href="{{ route('settings.team.users.edit', $user) }}?section=workload" class="btn btn-ghost btn-xs z-10" onclick="event.stopPropagation()"><span class="icon-[tabler--edit] size-4"></span></a>
                            <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                        </summary>
                        <div class="p-5 border-b border-base-200">
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="text-sm text-base-content/60">Hours per Week</label><p class="font-medium">{{ $instructor->hours_per_week ? number_format($instructor->hours_per_week, 1) . ' hrs' : '-' }}</p></div>
                                <div><label class="text-sm text-base-content/60">Max Classes per Week</label><p class="font-medium">{{ $instructor->max_classes_per_week ?? '-' }}</p></div>
                            </div>
                        </div>
                    </details>
                    @endif

                    {{-- Working Days --}}
                    @if($instructor)
                    <details class="group show-accordion-section">
                        <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none border-b border-base-200">
                            <span class="icon-[tabler--calendar-week] size-5 text-accent"></span>
                            <h2 class="flex-1 font-semibold">Working Days</h2>
                            <a href="{{ route('settings.team.users.edit', $user) }}?section=days" class="btn btn-ghost btn-xs z-10" onclick="event.stopPropagation()"><span class="icon-[tabler--edit] size-4"></span></a>
                            <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                        </summary>
                        <div class="p-5 border-b border-base-200">
                            @if(!empty($instructor->working_days))
                                <div class="flex flex-wrap gap-2">
                                    @foreach($dayOptions as $value => $label)
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm {{ in_array($value, $instructor->working_days) ? 'bg-primary/10 text-primary font-medium' : 'bg-base-200/50 text-base-content/40' }}">
                                            <span>{{ $dayEmojis[$value] }}</span>
                                            <span>{{ substr($label, 0, 3) }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-base-content/50 italic">No working days set (available all days)</p>
                            @endif
                        </div>
                    </details>
                    @endif

                    {{-- Availability Hours --}}
                    @if($instructor)
                    <details class="group show-accordion-section">
                        <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none border-b border-base-200">
                            <span class="icon-[tabler--clock] size-5 text-info"></span>
                            <h2 class="flex-1 font-semibold">Availability Hours</h2>
                            <a href="{{ route('settings.team.users.edit', $user) }}?section=hours" class="btn btn-ghost btn-xs z-10" onclick="event.stopPropagation()"><span class="icon-[tabler--edit] size-4"></span></a>
                            <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                        </summary>
                        <div class="p-5 border-b border-base-200">
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm text-base-content/60">Default Hours</label>
                                    @if($instructor->availability_default_from && $instructor->availability_default_to)
                                        <p class="font-medium">
                                            {{ \Carbon\Carbon::createFromFormat('H:i', $instructor->availability_default_from)->format('g:i A') }}
                                            —
                                            {{ \Carbon\Carbon::createFromFormat('H:i', $instructor->availability_default_to)->format('g:i A') }}
                                        </p>
                                    @else
                                        <p class="text-base-content/50 italic">Not set</p>
                                    @endif
                                </div>
                                @if(!empty($instructor->availability_by_day))
                                    @php
                                        $hasOverrides = false;
                                        foreach($instructor->availability_by_day as $day => $times) {
                                            if (!empty($times['from']) && !empty($times['to'])) { $hasOverrides = true; break; }
                                        }
                                    @endphp
                                    @if($hasOverrides)
                                        <div class="border-t border-base-200 pt-4">
                                            <label class="text-sm text-base-content/60 block mb-2">Day-Specific Overrides</label>
                                            <div class="space-y-2">
                                                @foreach($instructor->availability_by_day as $day => $times)
                                                    @if(!empty($times['from']) && !empty($times['to']))
                                                        <div class="flex items-center justify-between py-2 px-3 bg-base-200/30 rounded-lg">
                                                            <span class="font-medium text-sm">{{ $dayOptions[$day] ?? "Day $day" }}</span>
                                                            <span class="text-sm">
                                                                {{ \Carbon\Carbon::createFromFormat('H:i', $times['from'])->format('g:i A') }}
                                                                —
                                                                {{ \Carbon\Carbon::createFromFormat('H:i', $times['to'])->format('g:i A') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </details>
                    @endif

                    {{-- About --}}
                    <details class="group show-accordion-section">
                        <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none border-b border-base-200">
                            <span class="icon-[tabler--info-circle] size-5 text-primary"></span>
                            <h2 class="flex-1 font-semibold">About</h2>
                            @if($user->id !== auth()->id() && $userRole !== 'owner' || $user->id === auth()->id())
                                <button type="button" class="btn btn-ghost btn-xs z-10" onclick="event.stopPropagation(); openDrawer('edit-bio-drawer')"><span class="icon-[tabler--edit] size-4"></span></button>
                            @endif
                            <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                        </summary>
                        <div class="p-5 border-b border-base-200">
                            @if($user->bio)
                                <p class="text-base-content/80 whitespace-pre-wrap">{{ $user->bio }}</p>
                            @else
                                <p class="text-base-content/50 italic">No bio added yet.</p>
                            @endif
                        </div>
                    </details>

                    {{-- Social Links --}}
                    <details class="group show-accordion-section">
                        <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none border-b border-base-200">
                            <span class="icon-[tabler--share] size-5 text-secondary"></span>
                            <h2 class="flex-1 font-semibold">Social Links</h2>
                            @if($user->id !== auth()->id() && $userRole !== 'owner' || $user->id === auth()->id())
                                <button type="button" class="btn btn-ghost btn-xs z-10" onclick="event.stopPropagation(); openDrawer('edit-social-drawer')"><span class="icon-[tabler--edit] size-4"></span></button>
                            @endif
                            <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                        </summary>
                        <div class="p-5 border-b border-base-200">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--brand-instagram] size-5 text-pink-500"></span>
                                    @if(!empty($socialLinks['instagram'])) <a href="{{ $socialLinks['instagram'] }}" target="_blank" class="text-sm link link-primary">{{ $socialLinks['instagram'] }}</a> @else <span class="text-sm text-base-content/50">Not connected</span> @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--brand-facebook] size-5 text-blue-600"></span>
                                    @if(!empty($socialLinks['facebook'])) <a href="{{ $socialLinks['facebook'] }}" target="_blank" class="text-sm link link-primary">{{ $socialLinks['facebook'] }}</a> @else <span class="text-sm text-base-content/50">Not connected</span> @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--brand-x] size-5 text-base-content"></span>
                                    @if(!empty($socialLinks['twitter'])) <a href="{{ $socialLinks['twitter'] }}" target="_blank" class="text-sm link link-primary">{{ $socialLinks['twitter'] }}</a> @else <span class="text-sm text-base-content/50">Not connected</span> @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--brand-linkedin] size-5 text-blue-700"></span>
                                    @if(!empty($socialLinks['linkedin'])) <a href="{{ $socialLinks['linkedin'] }}" target="_blank" class="text-sm link link-primary">{{ $socialLinks['linkedin'] }}</a> @else <span class="text-sm text-base-content/50">Not connected</span> @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="icon-[tabler--world] size-5 text-base-content/70"></span>
                                    @if(!empty($socialLinks['website'])) <a href="{{ $socialLinks['website'] }}" target="_blank" class="text-sm link link-primary">{{ $socialLinks['website'] }}</a> @else <span class="text-sm text-base-content/50">Not connected</span> @endif
                                </div>
                            </div>
                            @if($userRole === 'instructor' && $instructor)
                                <div class="divider my-3"></div>
                                <div class="flex items-center justify-between">
                                    <div><label class="font-medium text-sm">Show on Public Profile</label><p class="text-xs text-base-content/60">Display social links on the public instructor page</p></div>
                                    <input type="checkbox" class="toggle toggle-primary toggle-sm" id="toggle-social-visibility" {{ $instructor->show_social_links ? 'checked' : '' }} onchange="toggleSocialVisibility(this.checked)">
                                </div>
                            @endif
                        </div>
                    </details>

                    {{-- Certifications --}}
                    <details class="group show-accordion-section">
                        <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none border-b border-base-200">
                            <span class="icon-[tabler--certificate] size-5 text-success"></span>
                            <h2 class="flex-1 font-semibold">Certifications</h2>
                            <button type="button" class="btn btn-primary btn-xs z-10" onclick="event.stopPropagation(); openUserCertDrawer()"><span class="icon-[tabler--plus] size-4"></span> Add</button>
                            <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                        </summary>
                        <div class="p-5 border-b border-base-200">
                            <div id="user-certifications-list">
                                @if($userCertifications->count() > 0)
                                    <div class="space-y-3" id="user-certs-container">
                                        @foreach($userCertifications as $cert)
                                        <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg" data-cert-id="{{ $cert->id }}">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg {{ $cert->isExpired() ? 'bg-error/10' : ($cert->isExpiringSoon() ? 'bg-warning/10' : 'bg-success/10') }} flex items-center justify-center shrink-0">
                                                    <span class="icon-[tabler--certificate] size-5 {{ $cert->isExpired() ? 'text-error' : ($cert->isExpiringSoon() ? 'text-warning' : 'text-success') }}"></span>
                                                </div>
                                                <div>
                                                    <div class="font-medium">{{ $cert->name }}</div>
                                                    @if($cert->certification_name) <div class="text-xs text-base-content/60">{{ $cert->certification_name }}</div> @endif
                                                    @if($cert->expire_date)
                                                        <div class="text-xs mt-1"><span class="badge {{ $cert->status_badge_class }} badge-xs">{{ $cert->status_label }} {{ $cert->expire_date->format('M d, Y') }}</span></div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                @if($cert->file_path)
                                                <a href="{{ $cert->file_url }}" target="_blank" class="btn btn-ghost btn-sm btn-square" title="View File"><span class="icon-[tabler--file-download] size-4"></span></a>
                                                @endif
                                                <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="editUserCert({{ $cert->id }})" title="Edit"><span class="icon-[tabler--pencil] size-4"></span></button>
                                                <button type="button" class="btn btn-ghost btn-sm btn-square text-error" onclick="deleteUserCert({{ $cert->id }})" title="Delete"><span class="icon-[tabler--trash] size-4"></span></button>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-6" id="no-user-certs-message">
                                        <span class="icon-[tabler--certificate] size-10 text-base-content/20 block mx-auto"></span>
                                        <p class="text-base-content/50 mt-2">No certifications added</p>
                                        <button type="button" class="btn btn-primary btn-sm mt-3" onclick="openUserCertDrawer()"><span class="icon-[tabler--plus] size-4"></span> Add Certification</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </details>

                    {{-- Permissions --}}
                    <details class="group show-accordion-section">
                        <summary class="flex items-center gap-3 p-4 cursor-pointer hover:bg-base-200/50 transition-colors list-none">
                            <span class="icon-[tabler--lock] size-5 text-error"></span>
                            <h2 class="flex-1 font-semibold">Permissions</h2>
                            @if($userRole !== 'owner')
                                <a href="{{ route('settings.team.permissions.edit', $user) }}" class="btn btn-ghost btn-xs z-10" onclick="event.stopPropagation()"><span class="icon-[tabler--edit] size-4"></span></a>
                            @endif
                            <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                        </summary>
                        <div class="p-5 border-t border-base-200">
                            @if($userRole === 'owner')
                                <div class="alert alert-soft alert-primary">
                                    <span class="icon-[tabler--crown] size-5"></span>
                                    <span>Owner has full access to all features including billing and danger zone.</span>
                                </div>
                            @elseif($userPermissions && count($userPermissions) > 0)
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($userPermissions as $permission)
                                        <div class="flex items-center gap-2 p-2 bg-base-200/50 rounded">
                                            <span class="icon-[tabler--check] size-4 text-success"></span>
                                            <span class="text-sm">{{ $allPermissions[$permission] ?? $permission }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-base-content/60">No specific permissions assigned. Using default permissions for {{ ucfirst($userRole) }} role.</p>
                            @endif
                        </div>
                    </details>

                    </div>{{-- end accordion card --}}
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Account Info --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--shield] size-5"></span>
                                Account Info
                            </h2>
                            <div class="space-y-4 mt-4">
                                <div>
                                    <label class="text-sm text-base-content/60">Login Access</label>
                                    <p class="font-medium">
                                        @if($hasLogin)
                                            <span class="text-success">Yes</span>
                                        @else
                                            <span class="text-base-content/50">No</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Email Verified</label>
                                    <p class="font-medium">
                                        @if($user->email_verified_at)
                                            <span class="text-success">{{ $user->email_verified_at->format('M d, Y') }}</span>
                                        @else
                                            <span class="text-base-content/50">Not verified</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Last Login</label>
                                    <p class="font-medium">
                                        @if($user->last_login_at)
                                            {{ $user->last_login_at->format('M d, Y g:i A') }}
                                            <span class="text-sm text-base-content/60 block">{{ $user->last_login_at->diffForHumans() }}</span>
                                        @else
                                            <span class="text-base-content/50">Never</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Member Since</label>
                                    <p class="font-medium">{{ $user->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    @if($user->id !== auth()->id() && $userRole !== 'owner')
                        <div class="card bg-base-100">
                            <div class="card-body">
                                <h2 class="card-title text-lg">
                                    <span class="icon-[tabler--bolt] size-5"></span>
                                    Quick Actions
                                </h2>
                                <div class="space-y-2 mt-4">
                                    @if(!$hasLogin && $user->email && !str_contains($user->email, '@nologin.local'))
                                        <form action="{{ route('settings.team.users.send-invite', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-soft btn-sm w-full justify-start">
                                                <span class="icon-[tabler--mail] size-4"></span>
                                                Send Login Invitation
                                            </button>
                                        </form>
                                    @endif

                                    @if($userRole === 'instructor' && $instructor)
                                        <a href="{{ route('instructors.show', $instructor) }}" class="btn btn-soft btn-sm w-full justify-start">
                                            <span class="icon-[tabler--user-star] size-4"></span>
                                            View Instructor Profile
                                        </a>
                                    @endif

                                    <div class="divider my-2"></div>

                                    <button type="button" onclick="showRemoveModal()" class="btn btn-soft btn-error btn-sm w-full justify-start">
                                        <span class="icon-[tabler--trash] size-4"></span>
                                        Remove from Team
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Notes Tab --}}
        <div class="tab-content {{ $tab === 'notes' ? 'active' : 'hidden' }}" data-content="notes">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Add Note Form --}}
                <div class="lg:col-span-1">
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--plus] size-5"></span>
                                Add Note
                            </h2>
                            <form id="addNoteForm" class="space-y-4 mt-4">
                                <div>
                                    <label class="label-text" for="note_type">Note Type</label>
                                    <select id="note_type" name="note_type" class="select w-full">
                                        @foreach($noteTypes as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="label-text" for="content">Content</label>
                                    <textarea id="content" name="content" rows="4" class="textarea w-full" placeholder="Enter note..."></textarea>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="is_visible_to_user" name="is_visible_to_user" class="checkbox checkbox-sm">
                                    <label for="is_visible_to_user" class="text-sm">Visible to team member</label>
                                </div>
                                <button type="submit" class="btn btn-primary w-full">
                                    <span class="icon-[tabler--plus] size-4"></span>
                                    Add Note
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Notes List --}}
                <div class="lg:col-span-2">
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--notes] size-5"></span>
                                Notes
                            </h2>
                            <div id="notesList" class="space-y-4 mt-4">
                                @forelse($user->notes as $note)
                                    <div class="border-b border-base-200 pb-4 last:border-0" data-note-id="{{ $note->id }}">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <span class="{{ \App\Models\UserNote::getNoteTypeIcon($note->note_type) }} size-4"></span>
                                                <span class="badge badge-soft badge-sm {{ \App\Models\UserNote::getNoteTypeBadgeClass($note->note_type) }}">
                                                    {{ $noteTypes[$note->note_type] ?? $note->note_type }}
                                                </span>
                                                @if($note->is_visible_to_user)
                                                    <span class="badge badge-soft badge-info badge-xs">User Visible</span>
                                                @endif
                                            </div>
                                            <details class="dropdown dropdown-end">
                                                <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                                                    <span class="icon-[tabler--dots] size-4"></span>
                                                </summary>
                                                <ul class="dropdown-content menu bg-base-100 rounded-box w-32 p-2 shadow-lg border border-base-300 z-50">
                                                    <li>
                                                        <button type="button" onclick="deleteNote({{ $note->id }})" class="text-error">
                                                            <span class="icon-[tabler--trash] size-4"></span> Delete
                                                        </button>
                                                    </li>
                                                </ul>
                                            </details>
                                        </div>
                                        <p class="mt-2">{{ $note->content }}</p>
                                        <p class="text-xs text-base-content/60 mt-2">
                                            {{ $note->author?->full_name ?? 'System' }} &bull; {{ $note->created_at->format('M d, Y g:i A') }}
                                        </p>
                                    </div>
                                @empty
                                    <p class="text-base-content/60 text-center py-8">No notes yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Billing Tab --}}
        <div class="tab-content {{ $tab === 'billing' ? 'active' : 'hidden' }}" data-content="billing">
            @if($userRole === 'instructor' && $instructor)
                {{-- For instructors, show link to instructor profile --}}
                <div class="alert alert-soft alert-info mb-6">
                    <span class="icon-[tabler--info-circle] size-5"></span>
                    <div class="flex-1">
                        <p>Billing information for instructors is managed in their instructor profile, which includes employment details, rates, and earnings.</p>
                    </div>
                    <a href="{{ route('instructors.show', ['instructor' => $instructor, 'tab' => 'billing']) }}" class="btn btn-info btn-sm">
                        <span class="icon-[tabler--wallet] size-4"></span>
                        View Instructor Billing
                    </a>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Employment Summary --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--briefcase] size-5"></span>
                            Employment
                        </h2>
                        <div class="space-y-4 mt-4">
                            <div>
                                <label class="text-sm text-base-content/60">Role</label>
                                <p class="font-medium text-lg">{{ ucfirst($userRole) }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-base-content/60">Status</label>
                                <p class="font-medium">{{ $statusText }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-base-content/60">Start Date</label>
                                <p class="font-medium">{{ $user->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Activity Summary --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--chart-bar] size-5"></span>
                            Activity
                        </h2>
                        <div class="space-y-4 mt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-base-content/60">Total Logins</span>
                                <span class="font-bold text-xl">{{ $user->last_login_at ? '—' : '0' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-base-content/60">Last Active</span>
                                <span class="font-medium">
                                    @if($user->last_login_at)
                                        {{ $user->last_login_at->diffForHumans() }}
                                    @else
                                        Never
                                    @endif
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-base-content/60">Days Active</span>
                                <span class="font-bold text-xl">{{ $user->created_at->diffInDays(now()) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Billing Notes --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--file-invoice] size-5"></span>
                            Billing Info
                        </h2>
                        <div class="mt-4">
                            @if($userRole === 'owner')
                                <p class="text-base-content/60">Owner accounts are not subject to payroll.</p>
                            @elseif($userRole === 'admin' || $userRole === 'staff')
                                <p class="text-base-content/60">Admin and staff payroll is handled outside this system.</p>
                                <div class="alert alert-soft alert-warning mt-4">
                                    <span class="icon-[tabler--info-circle] size-4"></span>
                                    <span class="text-sm">Use external payroll software for salary management.</span>
                                </div>
                            @else
                                <p class="text-base-content/60">No billing information available.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- User Certification Drawer --}}
<div id="user-cert-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold" id="user-cert-drawer-title">Add Certification</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeUserCertDrawer()">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="user-cert-form" class="flex flex-col flex-1 overflow-hidden" enctype="multipart/form-data">
        <input type="hidden" id="user-cert-id" value="" />
        <input type="hidden" id="user-cert-remove-file" value="" />
        <div class="flex-1 overflow-y-auto p-4">
            <div class="space-y-4">
                <div>
                    <label class="label-text font-medium" for="user_cert_name">Name <span class="text-error">*</span></label>
                    <input type="text" id="user_cert_name" name="name" class="input w-full" placeholder="e.g., First Aid, CPR, Management Training" required />
                </div>
                <div>
                    <label class="label-text font-medium" for="user_cert_certification_name">Certification / Credential Name</label>
                    <input type="text" id="user_cert_certification_name" name="certification_name" class="input w-full" placeholder="e.g., Red Cross Certified, License #12345" />
                </div>
                <div>
                    <label class="label-text font-medium" for="user_cert_expire_date">Expiration Date</label>
                    <input type="date" id="user_cert_expire_date" name="expire_date" class="input w-full" />
                    <p class="text-xs text-base-content/50 mt-1">Leave blank if no expiration</p>
                </div>
                <div>
                    <label class="label-text font-medium" for="user_cert_reminder_days">Reminder</label>
                    <select id="user_cert_reminder_days" name="reminder_days" class="select w-full">
                        <option value="">No reminder</option>
                        <option value="7">7 days before expiry</option>
                        <option value="14">14 days before expiry</option>
                        <option value="30">30 days before expiry</option>
                        <option value="60">60 days before expiry</option>
                        <option value="90">90 days before expiry</option>
                    </select>
                </div>
                <div>
                    <label class="label-text font-medium">Upload Document</label>
                    <div class="flex flex-col items-center justify-center border-2 border-dashed border-base-content/20 rounded-lg p-6 hover:border-primary transition-colors cursor-pointer" id="user-cert-drop-zone">
                        <input type="file" id="user_cert_file" name="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp" />
                        <div id="user-cert-upload-placeholder">
                            <span class="icon-[tabler--cloud-upload] size-8 text-base-content/30 mb-2 block mx-auto"></span>
                            <p class="text-sm text-base-content/60 text-center">Drag and drop file here, or</p>
                            <button type="button" class="btn btn-soft btn-sm mt-2 mx-auto block" id="user-cert-browse-btn">Browse Files</button>
                        </div>
                        <div id="user-cert-upload-preview" class="hidden w-full text-center">
                            <span class="icon-[tabler--file-check] size-8 text-success mb-2 block mx-auto"></span>
                            <p id="user-cert-preview-name" class="text-sm font-medium"></p>
                            <button type="button" class="btn btn-ghost btn-xs mt-2" id="user-cert-remove-preview-btn"><span class="icon-[tabler--x] size-4"></span> Remove</button>
                        </div>
                        <div id="user-cert-existing-file" class="hidden w-full text-center">
                            <span class="icon-[tabler--file-check] size-8 text-primary mb-2 block mx-auto"></span>
                            <p id="user-cert-existing-file-name" class="text-sm font-medium"></p>
                            <button type="button" class="btn btn-ghost btn-xs mt-2" id="user-cert-remove-existing-btn"><span class="icon-[tabler--x] size-4"></span> Remove</button>
                        </div>
                    </div>
                    <p class="text-xs text-base-content/50 text-center mt-2">PDF, JPG, PNG, WebP. Max 10MB</p>
                </div>
                <div>
                    <label class="label-text font-medium" for="user_cert_notes">Notes</label>
                    <textarea id="user_cert_notes" name="notes" class="textarea w-full" rows="2" placeholder="Additional notes..."></textarea>
                </div>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-user-cert-btn">
                <span class="loading loading-spinner loading-xs hidden" id="user-cert-spinner"></span>
                Save
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeUserCertDrawer()">Cancel</button>
        </div>
    </form>
</div>
<dialog id="delete-user-cert-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Delete Certification</h3>
        <p class="py-4">Are you sure you want to delete this certification? This action cannot be undone.</p>
        <input type="hidden" id="delete-user-cert-id" value="" />
        <div class="modal-action">
            <button type="button" class="btn btn-error" id="confirm-delete-user-cert-btn">Delete</button>
            <button type="button" class="btn" onclick="document.getElementById('delete-user-cert-modal').close()">Cancel</button>
        </div>
    </div>
</dialog>
<div id="user-cert-backdrop" class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none transition-opacity duration-300" onclick="closeUserCertDrawer()"></div>

@push('scripts')
<script>
const userId = {{ $user->id }};
const userName = '{{ addslashes($user->full_name) }}';
const userEmail = '{{ addslashes($user->email) }}';
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Tab switching
document.addEventListener('DOMContentLoaded', function() {
    // Accordion: exclusive open for show page sections
    var showSections = document.querySelectorAll('.show-accordion-section');
    showSections.forEach(function(details) {
        details.addEventListener('toggle', function() {
            if (this.open) {
                showSections.forEach(function(other) {
                    if (other !== details && other.open) {
                        other.removeAttribute('open');
                    }
                });
            }
        });
    });

    const tabs = document.querySelectorAll('.tabs .tab');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;

            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('tab', targetTab);
            window.history.pushState({}, '', url);

            // Switch tabs
            tabs.forEach(t => t.classList.remove('tab-active'));
            this.classList.add('tab-active');

            contents.forEach(content => {
                if (content.dataset.content === targetTab) {
                    content.classList.remove('hidden');
                    content.classList.add('active');
                } else {
                    content.classList.add('hidden');
                    content.classList.remove('active');
                }
            });
        });
    });
});

// Show Reset Password Modal
function showResetPasswordModal() {
    ConfirmModals.resetPassword({
        title: 'Reset Password',
        message: `Send a password reset email to ${userName}?`,
        email: userEmail,
        action: `/settings/team/users/${userId}/reset-password`
    });
}

// Show Suspend Modal
function showSuspendModal() {
    ConfirmModals.suspend({
        title: 'Suspend User',
        message: `Are you sure you want to suspend "${userName}"? They will not be able to log in until reactivated.`,
        btnText: 'Suspend',
        action: `/settings/team/users/${userId}/suspend`
    });
}

// Show Deactivate Modal
function showDeactivateModal() {
    ConfirmModals.deactivate({
        title: 'Deactivate User',
        message: `Are you sure you want to deactivate "${userName}"? They will not be able to log in until reactivated.`,
        btnText: 'Deactivate',
        action: `/settings/team/users/${userId}/deactivate`
    });
}

// Show Reactivate Modal
function showReactivateModal() {
    ConfirmModals.activate({
        title: 'Reactivate User',
        message: `Are you sure you want to reactivate "${userName}"?`,
        btnText: 'Reactivate',
        action: `/settings/team/users/${userId}/reactivate`
    });
}

// Show Remove Modal
function showRemoveModal() {
    ConfirmModals.delete({
        title: 'Remove Team Member',
        message: `Are you sure you want to remove "${userName}" from the team? This action cannot be undone.`,
        action: `/settings/team/users/${userId}`
    });
}

// Add Note
document.getElementById('addNoteForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = {
        note_type: document.getElementById('note_type').value,
        content: document.getElementById('content').value,
        is_visible_to_user: document.getElementById('is_visible_to_user').checked
    };

    if (!formData.content.trim()) {
        alert('Please enter note content.');
        return;
    }

    fetch(`/settings/team/users/${userId}/notes`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});

// Delete Note
function deleteNote(noteId) {
    showConfirmModal({
        title: 'Delete Note',
        message: 'Are you sure you want to delete this note? This action cannot be undone.',
        type: 'danger',
        btnText: 'Delete',
        btnIcon: 'icon-[tabler--trash]',
        onConfirm: function() {
            fetch(`/settings/team/user-notes/${noteId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-note-id="${noteId}"]`)?.remove();
                    showToast('Note deleted successfully.', 'success');
                } else {
                    showToast(data.message || 'An error occurred', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
            });
        }
    });
}

// Drawer functions
function openDrawer(drawerId) {
    const drawer = document.getElementById(drawerId);
    const backdrop = document.getElementById(drawerId + '-backdrop');
    if (drawer) {
        drawer.classList.remove('translate-x-full');
        backdrop?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeDrawer(drawerId) {
    const drawer = document.getElementById(drawerId);
    const backdrop = document.getElementById(drawerId + '-backdrop');
    if (drawer) {
        drawer.classList.add('translate-x-full');
        backdrop?.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function saveBio() {
    const bio = document.getElementById('user-bio').value;
    const btn = document.getElementById('save-bio-btn');
    const spinner = btn.querySelector('.loading');

    btn.disabled = true;
    spinner.classList.remove('hidden');

    fetch(`/settings/team/users/${userId}/profile`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ bio: bio })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        spinner.classList.add('hidden');

        if (data.success) {
            closeDrawer('edit-bio-drawer');
            window.location.reload();
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(error => {
        btn.disabled = false;
        spinner.classList.add('hidden');
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function saveSocialLinks() {
    const socialLinks = {
        instagram: document.getElementById('social-instagram').value,
        facebook: document.getElementById('social-facebook').value,
        twitter: document.getElementById('social-twitter').value,
        linkedin: document.getElementById('social-linkedin').value,
        website: document.getElementById('social-website').value
    };

    const btn = document.getElementById('save-social-btn');
    const spinner = btn.querySelector('.loading');

    btn.disabled = true;
    spinner.classList.remove('hidden');

    fetch(`/settings/team/users/${userId}/profile`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ social_links: socialLinks })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        spinner.classList.add('hidden');

        if (data.success) {
            closeDrawer('edit-social-drawer');
            window.location.reload();
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(error => {
        btn.disabled = false;
        spinner.classList.add('hidden');
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

// Close drawers on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDrawer('edit-bio-drawer');
        closeDrawer('edit-social-drawer');
    }
});

// ========== Certification Drawer ==========
var editingUserCertId = null;
var certApiBase = '{{ url("settings/team/users/" . $user->id . "/certifications") }}';

function showToast(message, type) {
    type = type || 'success';
    var toast = document.createElement('div');
    toast.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[100] alert alert-' + type + ' shadow-lg max-w-sm';
    toast.innerHTML = '<span class="icon-[tabler--' + (type === 'success' ? 'check' : 'alert-circle') + '] size-5"></span><span>' + message + '</span>';
    document.body.appendChild(toast);
    setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(function() { toast.remove(); }, 300); }, 3000);
}

function openUserCertDrawer() {
    resetUserCertForm();
    var drawer = document.getElementById('user-cert-drawer');
    var backdrop = document.getElementById('user-cert-backdrop');
    if (drawer && backdrop) {
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
        backdrop.classList.add('opacity-100', 'pointer-events-auto');
        drawer.classList.remove('translate-x-full');
        drawer.classList.add('translate-x-0');
        document.body.style.overflow = 'hidden';
    }
}

function closeUserCertDrawer() {
    var drawer = document.getElementById('user-cert-drawer');
    var backdrop = document.getElementById('user-cert-backdrop');
    if (drawer && backdrop) {
        drawer.classList.remove('translate-x-0');
        drawer.classList.add('translate-x-full');
        backdrop.classList.remove('opacity-100', 'pointer-events-auto');
        backdrop.classList.add('opacity-0', 'pointer-events-none');
        document.body.style.overflow = '';
    }
}

function resetUserCertForm() {
    editingUserCertId = null;
    document.getElementById('user-cert-drawer-title').textContent = 'Add Certification';
    document.getElementById('user-cert-id').value = '';
    document.getElementById('user_cert_name').value = '';
    document.getElementById('user_cert_certification_name').value = '';
    document.getElementById('user_cert_expire_date').value = '';
    document.getElementById('user_cert_reminder_days').value = '';
    document.getElementById('user_cert_notes').value = '';
    document.getElementById('user_cert_file').value = '';
    document.getElementById('user-cert-remove-file').value = '';
    var placeholder = document.getElementById('user-cert-upload-placeholder');
    var preview = document.getElementById('user-cert-upload-preview');
    var existingFile = document.getElementById('user-cert-existing-file');
    if (placeholder) placeholder.classList.remove('hidden');
    if (preview) preview.classList.add('hidden');
    if (existingFile) existingFile.classList.add('hidden');
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function editUserCert(id) {
    editingUserCertId = id;
    document.getElementById('user-cert-drawer-title').textContent = 'Edit Certification';
    var spinner = document.getElementById('user-cert-spinner');
    spinner.classList.remove('hidden');

    fetch(certApiBase + '/' + id, {
        method: 'GET', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            var cert = result.certification;
            document.getElementById('user-cert-id').value = cert.id;
            document.getElementById('user_cert_name').value = cert.name || '';
            document.getElementById('user_cert_certification_name').value = cert.certification_name || '';
            document.getElementById('user_cert_expire_date').value = cert.expire_date || '';
            document.getElementById('user_cert_reminder_days').value = cert.reminder_days || '';
            document.getElementById('user_cert_notes').value = cert.notes || '';

            var placeholder = document.getElementById('user-cert-upload-placeholder');
            var preview = document.getElementById('user-cert-upload-preview');
            var existingFile = document.getElementById('user-cert-existing-file');
            var existingFileName = document.getElementById('user-cert-existing-file-name');

            if (cert.file_name && existingFile && existingFileName) {
                existingFileName.textContent = cert.file_name;
                existingFile.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            } else {
                if (existingFile) existingFile.classList.add('hidden');
                if (placeholder) placeholder.classList.remove('hidden');
            }
            if (preview) preview.classList.add('hidden');
            openUserCertDrawer();
        } else { showToast(result.message || 'Failed to load', 'error'); }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { spinner.classList.add('hidden'); });
}

function deleteUserCert(id) {
    document.getElementById('delete-user-cert-id').value = id;
    document.getElementById('delete-user-cert-modal').showModal();
}

document.getElementById('confirm-delete-user-cert-btn').addEventListener('click', function() {
    var btn = this;
    var id = document.getElementById('delete-user-cert-id').value;
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Deleting...';

    fetch(certApiBase + '/' + id, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            var item = document.querySelector('[data-cert-id="' + id + '"]');
            if (item) item.remove();
            var container = document.getElementById('user-certs-container');
            if (container && container.querySelectorAll('[data-cert-id]').length === 0) {
                document.getElementById('user-certifications-list').innerHTML =
                    '<div class="text-center py-6" id="no-user-certs-message">' +
                    '<span class="icon-[tabler--certificate] size-10 text-base-content/20 block mx-auto"></span>' +
                    '<p class="text-base-content/50 mt-2">No certifications added</p>' +
                    '<button type="button" class="btn btn-primary btn-sm mt-3" onclick="openUserCertDrawer()"><span class="icon-[tabler--plus] size-4"></span> Add Certification</button></div>';
            }
            document.getElementById('delete-user-cert-modal').close();
            showToast('Certification deleted!');
        } else { showToast(result.message || 'Failed to delete', 'error'); }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; btn.innerHTML = 'Delete'; });
});

// File upload handling
(function() {
    var fileInput = document.getElementById('user_cert_file');
    var browseBtn = document.getElementById('user-cert-browse-btn');
    var dropZone = document.getElementById('user-cert-drop-zone');
    var placeholder = document.getElementById('user-cert-upload-placeholder');
    var preview = document.getElementById('user-cert-upload-preview');
    var previewName = document.getElementById('user-cert-preview-name');
    var removeBtn = document.getElementById('user-cert-remove-preview-btn');
    var existingFile = document.getElementById('user-cert-existing-file');

    if (!fileInput) return;
    if (browseBtn) browseBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); fileInput.click(); });
    if (dropZone) {
        dropZone.addEventListener('click', function(e) { if (!e.target.closest('button')) fileInput.click(); });
        dropZone.addEventListener('dragover', function(e) { e.preventDefault(); dropZone.classList.add('border-primary', 'bg-primary/5'); });
        dropZone.addEventListener('dragleave', function(e) { e.preventDefault(); dropZone.classList.remove('border-primary', 'bg-primary/5'); });
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault(); dropZone.classList.remove('border-primary', 'bg-primary/5');
            if (e.dataTransfer.files.length > 0) { fileInput.files = e.dataTransfer.files; handleCertFile(e.dataTransfer.files[0]); }
        });
    }
    fileInput.addEventListener('change', function() { if (this.files.length > 0) handleCertFile(this.files[0]); });
    if (removeBtn) removeBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); fileInput.value = ''; if (preview) preview.classList.add('hidden'); if (placeholder) placeholder.classList.remove('hidden'); });

    function handleCertFile(file) {
        var validTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
        if (!validTypes.includes(file.type)) { showToast('Please upload PDF, JPG, PNG, or WebP', 'error'); return; }
        if (file.size > 10 * 1024 * 1024) { showToast('File must be under 10MB', 'error'); return; }
        if (previewName) previewName.textContent = file.name;
        if (placeholder) placeholder.classList.add('hidden');
        if (existingFile) existingFile.classList.add('hidden');
        if (preview) preview.classList.remove('hidden');
    }

    var removeExistingBtn = document.getElementById('user-cert-remove-existing-btn');
    if (removeExistingBtn) removeExistingBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); if (existingFile) existingFile.classList.add('hidden'); if (placeholder) placeholder.classList.remove('hidden'); document.getElementById('user-cert-remove-file').value = '1'; });
})();

// Cert form submit
document.getElementById('user-cert-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-user-cert-btn');
    var spinner = document.getElementById('user-cert-spinner');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    var formData = new FormData();
    formData.append('name', document.getElementById('user_cert_name').value);
    formData.append('certification_name', document.getElementById('user_cert_certification_name').value);
    formData.append('expire_date', document.getElementById('user_cert_expire_date').value);
    formData.append('reminder_days', document.getElementById('user_cert_reminder_days').value);
    formData.append('notes', document.getElementById('user_cert_notes').value);

    var fileInput = document.getElementById('user_cert_file');
    if (fileInput.files.length > 0) formData.append('file', fileInput.files[0]);
    if (document.getElementById('user-cert-remove-file').value === '1') formData.append('remove_file', '1');
    var certId = document.getElementById('user-cert-id').value;
    var isEdit = certId && certId !== '';
    if (isEdit) formData.append('id', certId);

    fetch(certApiBase, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            var cert = result.certification;
            var list = document.getElementById('user-certifications-list');
            var statusIcon = cert.days_until_expiry !== null ? (cert.days_until_expiry < 0 ? 'bg-error/10' : (cert.days_until_expiry <= 30 ? 'bg-warning/10' : 'bg-success/10')) : 'bg-success/10';
            var statusIconColor = cert.days_until_expiry !== null ? (cert.days_until_expiry < 0 ? 'text-error' : (cert.days_until_expiry <= 30 ? 'text-warning' : 'text-success')) : 'text-success';

            var itemHtml = '<div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg" data-cert-id="' + cert.id + '">' +
                '<div class="flex items-center gap-3"><div class="w-10 h-10 rounded-lg ' + statusIcon + ' flex items-center justify-center shrink-0"><span class="icon-[tabler--certificate] size-5 ' + statusIconColor + '"></span></div>' +
                '<div><div class="font-medium">' + escapeHtml(cert.name) + '</div>';
            if (cert.certification_name) itemHtml += '<div class="text-xs text-base-content/60">' + escapeHtml(cert.certification_name) + '</div>';
            if (cert.expire_date_formatted) itemHtml += '<div class="text-xs mt-1"><span class="badge ' + cert.status_badge_class + ' badge-xs">' + (cert.days_until_expiry < 0 ? 'Expired ' : 'Expires ') + cert.expire_date_formatted + '</span></div>';
            itemHtml += '</div></div><div class="flex items-center gap-1">';
            if (cert.file_url) itemHtml += '<a href="' + cert.file_url + '" target="_blank" class="btn btn-ghost btn-sm btn-square" title="View File"><span class="icon-[tabler--file-download] size-4"></span></a>';
            itemHtml += '<button type="button" class="btn btn-ghost btn-sm btn-square" onclick="editUserCert(' + cert.id + ')" title="Edit"><span class="icon-[tabler--pencil] size-4"></span></button>' +
                '<button type="button" class="btn btn-ghost btn-sm btn-square text-error" onclick="deleteUserCert(' + cert.id + ')" title="Delete"><span class="icon-[tabler--trash] size-4"></span></button></div></div>';

            if (isEdit) {
                var existingItem = document.querySelector('[data-cert-id="' + cert.id + '"]');
                if (existingItem) existingItem.outerHTML = itemHtml;
            } else {
                var emptyState = document.getElementById('no-user-certs-message');
                if (emptyState) {
                    list.innerHTML = '<div class="space-y-3" id="user-certs-container">' + itemHtml + '</div>';
                } else {
                    var container = document.getElementById('user-certs-container');
                    if (container) container.insertAdjacentHTML('beforeend', itemHtml);
                    else list.innerHTML = '<div class="space-y-3" id="user-certs-container">' + itemHtml + '</div>';
                }
            }
            resetUserCertForm();
            closeUserCertDrawer();
            setTimeout(function() { showToast(isEdit ? 'Certification updated!' : 'Certification added!'); }, 350);
        } else { showToast(result.message || 'Failed to save', 'error'); }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

// Toggle social links visibility for instructors
@if($userRole === 'instructor' && $instructor)
const instructorId = {{ $instructor->id }};

function toggleSocialVisibility(checked) {
    const toggle = document.getElementById('toggle-social-visibility');

    fetch(`/settings/team/instructors/${instructorId}/toggle-social-visibility`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ show_social_links: checked })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show toast notification
            if (typeof showToast === 'function') {
                showToast(checked ? 'Social links visible on public profile' : 'Social links hidden from public profile');
            }
        } else {
            // Revert toggle on error
            toggle.checked = !checked;
            alert(data.message || 'Failed to update');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toggle.checked = !checked;
        alert('An error occurred. Please try again.');
    });
}
@endif
</script>
@endpush

{{-- Edit Bio Drawer --}}
<div id="edit-bio-drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="closeDrawer('edit-bio-drawer')"></div>
<div id="edit-bio-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold flex items-center gap-2">
            <span class="icon-[tabler--info-circle] size-5 text-primary"></span>
            Edit About
        </h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-bio-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto p-4">
        <p class="text-sm text-base-content/60 mb-4">Add a bio or description for this team member</p>
        <div>
            <label class="label" for="user-bio">
                <span class="label-text font-medium">Bio</span>
            </label>
            <textarea id="user-bio" class="textarea textarea-bordered w-full h-40" placeholder="Tell us about yourself...">{{ $user->bio }}</textarea>
            <p class="text-xs text-base-content/50 mt-1">This will be visible on the team profile</p>
        </div>
    </div>
    <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
        <button type="button" id="save-bio-btn" class="btn btn-primary gap-2" onclick="saveBio()">
            <span class="loading loading-spinner loading-xs hidden"></span>
            Save Changes
        </button>
        <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-bio-drawer')">Cancel</button>
    </div>
</div>

{{-- Edit Social Links Drawer --}}
<div id="edit-social-drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="closeDrawer('edit-social-drawer')"></div>
<div id="edit-social-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold flex items-center gap-2">
            <span class="icon-[tabler--share] size-5 text-primary"></span>
            Edit Social Links
        </h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-social-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto p-4">
        <p class="text-sm text-base-content/60 mb-4">Connect your social media profiles</p>

        @php
            $socialLinks = $user->social_links ?? [];
        @endphp

        <div class="space-y-4">
            <div>
                <label class="label-text flex items-center gap-2 mb-1" for="social-instagram">
                    <span class="icon-[tabler--brand-instagram] size-4 text-pink-500"></span> Instagram
                </label>
                <input id="social-instagram" type="url" class="input input-bordered w-full" value="{{ $socialLinks['instagram'] ?? '' }}" placeholder="https://instagram.com/username" />
            </div>
            <div>
                <label class="label-text flex items-center gap-2 mb-1" for="social-facebook">
                    <span class="icon-[tabler--brand-facebook] size-4 text-blue-600"></span> Facebook
                </label>
                <input id="social-facebook" type="url" class="input input-bordered w-full" value="{{ $socialLinks['facebook'] ?? '' }}" placeholder="https://facebook.com/username" />
            </div>
            <div>
                <label class="label-text flex items-center gap-2 mb-1" for="social-twitter">
                    <span class="icon-[tabler--brand-x] size-4"></span> X (Twitter)
                </label>
                <input id="social-twitter" type="url" class="input input-bordered w-full" value="{{ $socialLinks['twitter'] ?? '' }}" placeholder="https://x.com/username" />
            </div>
            <div>
                <label class="label-text flex items-center gap-2 mb-1" for="social-linkedin">
                    <span class="icon-[tabler--brand-linkedin] size-4 text-blue-700"></span> LinkedIn
                </label>
                <input id="social-linkedin" type="url" class="input input-bordered w-full" value="{{ $socialLinks['linkedin'] ?? '' }}" placeholder="https://linkedin.com/in/username" />
            </div>
            <div>
                <label class="label-text flex items-center gap-2 mb-1" for="social-website">
                    <span class="icon-[tabler--world] size-4 text-base-content/70"></span> Website
                </label>
                <input id="social-website" type="url" class="input input-bordered w-full" value="{{ $socialLinks['website'] ?? '' }}" placeholder="https://yourwebsite.com" />
            </div>
        </div>
    </div>
    <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
        <button type="button" id="save-social-btn" class="btn btn-primary gap-2" onclick="saveSocialLinks()">
            <span class="loading loading-spinner loading-xs hidden"></span>
            Save Changes
        </button>
        <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-social-drawer')">Cancel</button>
    </div>
</div>

@endsection
