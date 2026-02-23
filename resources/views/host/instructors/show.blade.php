@extends('layouts.dashboard')

@section('title', $instructor->name . ' — Instructor')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('instructors.index') }}">Instructors</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $instructor->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Incomplete Profile Alert --}}
    @if(!$instructor->isProfileComplete())
    <div class="alert alert-soft alert-warning">
        <span class="icon-[tabler--alert-triangle] size-5"></span>
        <div class="flex-1">
            <p class="font-medium">Instructor profile is incomplete</p>
            <p class="text-sm opacity-90">Missing: <strong>{{ implode(', ', $instructor->getMissingProfileFields()) }}</strong></p>
        </div>
        <a href="{{ route('instructors.edit', $instructor) }}" class="btn btn-warning btn-sm">Complete Profile</a>
    </div>
    @endif

    {{-- Hero Header --}}
    <div class="card bg-gradient-to-r from-primary/10 via-primary/5 to-transparent border-0 overflow-hidden">
        <div class="card-body p-6">
            <div class="flex flex-col lg:flex-row lg:items-start gap-6">
                {{-- Profile Photo & Basic Info --}}
                <div class="flex items-start gap-4 flex-1">
                    <a href="{{ route('instructors.index') }}" class="btn btn-ghost btn-sm btn-circle">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                    </a>
                    <div class="relative">
                        @if($instructor->photo_url)
                            <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}"
                                 class="w-24 h-24 rounded-2xl object-cover shadow-lg ring-4 ring-white">
                        @else
                            <div class="avatar placeholder">
                                <div class="bg-gradient-to-br from-primary to-primary/70 text-primary-content w-24 h-24 rounded-2xl font-bold text-3xl shadow-lg">
                                    {{ $instructor->initials }}
                                </div>
                            </div>
                        @endif
                        @if($instructor->is_active)
                            <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-success rounded-full border-2 border-white"></span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-2xl font-bold">{{ $instructor->name }}</h1>
                            @if($instructor->status === 'pending' || !$instructor->isProfileComplete())
                                <span class="badge badge-warning gap-1">
                                    <span class="icon-[tabler--alert-triangle] size-3"></span>
                                    Pending Setup
                                </span>
                            @elseif($instructor->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-neutral">Inactive</span>
                            @endif
                        </div>
                        @if($instructor->email)
                            <p class="text-base-content/60 mt-1 flex items-center gap-2">
                                <span class="icon-[tabler--mail] size-4"></span>
                                {{ $instructor->email }}
                            </p>
                        @endif
                        @if($instructor->phone)
                            <p class="text-base-content/60 text-sm flex items-center gap-2">
                                <span class="icon-[tabler--phone] size-4"></span>
                                {{ $instructor->phone }}
                            </p>
                        @endif
                        <div class="flex flex-wrap items-center gap-2 mt-3">
                            @if($instructor->specialties)
                                @foreach(array_slice($instructor->specialties, 0, 4) as $specialty)
                                    <span class="badge badge-primary badge-sm">{{ $specialty }}</span>
                                @endforeach
                                @if(count($instructor->specialties) > 4)
                                    <span class="badge badge-ghost badge-sm">+{{ count($instructor->specialties) - 4 }} more</span>
                                @endif
                            @endif
                        </div>

                        {{-- Social Links --}}
                        @if($instructor->social_links && count($instructor->social_links) > 0)
                            <div class="flex items-center gap-2 mt-3">
                                @foreach($instructor->social_links as $platform => $url)
                                    @if($url)
                                        <a href="{{ $url }}" target="_blank" class="btn btn-ghost btn-sm btn-circle" title="{{ ucfirst($platform) }}">
                                            @switch($platform)
                                                @case('instagram')
                                                    <span class="icon-[tabler--brand-instagram] size-5"></span>
                                                    @break
                                                @case('facebook')
                                                    <span class="icon-[tabler--brand-facebook] size-5"></span>
                                                    @break
                                                @case('twitter')
                                                    <span class="icon-[tabler--brand-x] size-5"></span>
                                                    @break
                                                @case('linkedin')
                                                    <span class="icon-[tabler--brand-linkedin] size-5"></span>
                                                    @break
                                                @case('youtube')
                                                    <span class="icon-[tabler--brand-youtube] size-5"></span>
                                                    @break
                                                @case('website')
                                                    <span class="icon-[tabler--world] size-5"></span>
                                                    @break
                                                @default
                                                    <span class="icon-[tabler--link] size-5"></span>
                                            @endswitch
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="flex items-center gap-2 flex-wrap">
                    @if($instructor->hasAccount())
                        <button type="button" onclick="showResetPasswordModal()" class="btn btn-ghost btn-sm">
                            <span class="icon-[tabler--key] size-4"></span>
                            Reset Password
                        </button>
                    @endif
                    <a href="{{ route('instructors.edit', $instructor) }}" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--edit] size-4"></span>
                        Edit Profile
                    </a>
                    @if($instructor->is_active)
                        <button type="button" onclick="showMakeInactiveModal()" class="btn btn-warning btn-sm">
                            <span class="icon-[tabler--user-off] size-4"></span>
                            Deactivate
                        </button>
                    @else
                        <button type="button" onclick="showActivateModal()" class="btn btn-success btn-sm">
                            <span class="icon-[tabler--user-check] size-4"></span>
                            Activate
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--yoga] size-6 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ number_format($allTimeStats['total_classes']) }}</p>
                        <p class="text-sm text-base-content/60">Classes Taught</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center">
                        <span class="icon-[tabler--massage] size-6 text-secondary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ number_format($allTimeStats['total_services']) }}</p>
                        <p class="text-sm text-base-content/60">Services Given</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--users] size-6 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ number_format($allTimeStats['total_clients']) }}</p>
                        <p class="text-sm text-base-content/60">Clients Served</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-warning/10 flex items-center justify-center">
                        <span class="icon-[tabler--calendar-stats] size-6 text-warning"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $allTimeStats['years_teaching'] > 0 ? $allTimeStats['years_teaching'] . '+' : '<1' }}</p>
                        <p class="text-sm text-base-content/60">Years with Studio</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tabs tabs-bordered" role="tablist">
        <button class="tab {{ $tab === 'overview' ? 'tab-active' : '' }}" data-tab="overview" role="tab">
            <span class="icon-[tabler--user] size-4 mr-2"></span>Overview
        </button>
        <button class="tab {{ $tab === 'schedule' ? 'tab-active' : '' }}" data-tab="schedule" role="tab">
            <span class="icon-[tabler--calendar] size-4 mr-2"></span>Schedule
        </button>
        <button class="tab {{ $tab === 'assignments' ? 'tab-active' : '' }}" data-tab="assignments" role="tab">
            <span class="icon-[tabler--list-check] size-4 mr-2"></span>Classes & Services
        </button>
        <button class="tab {{ $tab === 'billing' ? 'tab-active' : '' }}" data-tab="billing" role="tab">
            <span class="icon-[tabler--wallet] size-4 mr-2"></span>Billing
        </button>
        <button class="tab {{ $tab === 'notes' ? 'tab-active' : '' }}" data-tab="notes" role="tab">
            <span class="icon-[tabler--notes] size-4 mr-2"></span>Notes
            @if($instructor->notes->count() > 0)
                <span class="badge badge-sm badge-primary ml-1">{{ $instructor->notes->count() }}</span>
            @endif
        </button>
    </div>

    {{-- Tab Contents --}}
    <div class="tab-contents">
        {{-- Overview Tab --}}
        <div class="tab-content {{ $tab === 'overview' ? 'active' : 'hidden' }}" data-content="overview">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Bio --}}
                    @if($instructor->bio)
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--quote] size-5"></span>
                                About
                            </h2>
                            <p class="mt-2 text-base-content/80 leading-relaxed">{{ $instructor->bio }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Profile Information --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--user-circle] size-5"></span>
                                Profile Information
                            </h2>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mt-4">
                                <div>
                                    <label class="text-sm text-base-content/60">Full Name</label>
                                    <p class="font-medium">{{ $instructor->name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Email</label>
                                    <p class="font-medium">{{ $instructor->email ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Phone</label>
                                    <p class="font-medium">{{ $instructor->phone ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Employment Type</label>
                                    <p class="font-medium">{{ $instructor->getFormattedEmploymentType() ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Linked Account</label>
                                    <p class="font-medium flex items-center gap-1">
                                        @if($instructor->hasAccount())
                                            <span class="icon-[tabler--check] size-4 text-success"></span> Yes
                                        @elseif($instructor->hasPendingInvitation())
                                            <span class="icon-[tabler--clock] size-4 text-warning"></span> Invite Pending
                                        @else
                                            <span class="icon-[tabler--x] size-4 text-error"></span> No
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Public Visibility</label>
                                    <p class="font-medium">{{ $instructor->is_visible ? 'Visible' : 'Hidden' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Employment & Compensation --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--briefcase] size-5"></span>
                                Employment & Compensation
                            </h2>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mt-4">
                                <div>
                                    <label class="text-sm text-base-content/60">Rate</label>
                                    <p class="font-medium text-lg text-success">{{ $instructor->getFormattedRate() ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Hours per Week</label>
                                    <p class="font-medium">{{ $instructor->hours_per_week ? $instructor->hours_per_week . ' hrs' : '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Max Classes/Week</label>
                                    <p class="font-medium">{{ $instructor->max_classes_per_week ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Working Days</label>
                                    <p class="font-medium">{{ $instructor->getFormattedWorkingDays() ?: '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Default Hours</label>
                                    <p class="font-medium">
                                        @if($instructor->availability_default_from && $instructor->availability_default_to)
                                            {{ $instructor->availability_default_from }} - {{ $instructor->availability_default_to }}
                                        @else
                                            -
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if($instructor->compensation_notes)
                                <div class="mt-4 p-3 bg-base-200/50 rounded-lg">
                                    <label class="text-sm text-base-content/60">Compensation Notes</label>
                                    <p class="mt-1">{{ $instructor->compensation_notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Certifications --}}
                    @if($instructor->studioCertifications && $instructor->studioCertifications->count() > 0)
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--certificate] size-5"></span>
                                Certifications
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                @foreach($instructor->studioCertifications as $cert)
                                    <div class="flex items-start gap-3 p-3 bg-base-200/50 rounded-lg">
                                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                                            <span class="icon-[tabler--certificate] size-5 text-primary"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium">{{ $cert->name }}</p>
                                            @if($cert->issuing_organization)
                                                <p class="text-sm text-base-content/60">{{ $cert->issuing_organization }}</p>
                                            @endif
                                            <div class="flex items-center gap-2 mt-1 text-xs text-base-content/60">
                                                @if($cert->issue_date)
                                                    <span>Issued: {{ $cert->issue_date->format('M Y') }}</span>
                                                @endif
                                                @if($cert->expiry_date)
                                                    <span class="{{ $cert->expiry_date->isPast() ? 'text-error' : '' }}">
                                                        Expires: {{ $cert->expiry_date->format('M Y') }}
                                                        @if($cert->expiry_date->isPast())
                                                            <span class="badge badge-error badge-xs ml-1">Expired</span>
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- This Month Stats --}}
                    <div class="card bg-gradient-to-br from-success/10 to-success/5 border-success/20">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--chart-bar] size-5"></span>
                                This Month
                            </h2>
                            <div class="space-y-4 mt-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-base-content/70">Classes</span>
                                    <span class="font-bold text-xl">{{ $monthlyStats['classes_count'] }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-base-content/70">Services</span>
                                    <span class="font-bold text-xl">{{ $monthlyStats['services_count'] }}</span>
                                </div>
                                <hr class="border-base-300">
                                <div>
                                    <p class="text-sm text-base-content/60">Estimated Earnings</p>
                                    @if($monthlyStats['estimated_earnings'] !== null)
                                        <p class="text-2xl font-bold text-success">${{ number_format($monthlyStats['estimated_earnings'], 2) }}</p>
                                    @else
                                        <p class="text-base-content/60">N/A</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Next Session --}}
                    @if($upcomingSessions->isNotEmpty())
                        @php $nextSession = $upcomingSessions->first(); @endphp
                        <div class="card bg-base-100">
                            <div class="card-body">
                                <h2 class="card-title text-lg">
                                    <span class="icon-[tabler--calendar-event] size-5"></span>
                                    Next Session
                                </h2>
                                <div class="mt-4 p-3 bg-primary/5 rounded-lg border border-primary/10">
                                    <p class="font-semibold text-primary">{{ $nextSession->classPlan?->name ?? 'Class Session' }}</p>
                                    <p class="text-sm mt-1 flex items-center gap-1">
                                        <span class="icon-[tabler--calendar] size-4"></span>
                                        {{ $nextSession->start_time->format('D, M d \a\t g:i A') }}
                                    </p>
                                    @if($nextSession->location)
                                        <p class="text-sm text-base-content/60 flex items-center gap-1">
                                            <span class="icon-[tabler--map-pin] size-4"></span>
                                            {{ $nextSession->location->name }}
                                        </p>
                                    @endif
                                    <a href="{{ route('class-sessions.show', $nextSession) }}" class="btn btn-primary btn-sm btn-block mt-3">
                                        View Session
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Upcoming Service Bookings --}}
                    @if($upcomingServiceBookings->isNotEmpty())
                        <div class="card bg-base-100">
                            <div class="card-body">
                                <h2 class="card-title text-lg">
                                    <span class="icon-[tabler--sparkles] size-5"></span>
                                    Upcoming Services
                                </h2>
                                <div class="space-y-3 mt-4">
                                    @foreach($upcomingServiceBookings->take(3) as $booking)
                                        <div class="flex items-center gap-3 p-2 bg-base-200/50 rounded-lg">
                                            <div class="avatar placeholder">
                                                <div class="bg-secondary/10 text-secondary w-8 h-8 rounded-full text-xs">
                                                    {{ $booking->client?->initials ?? '?' }}
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-sm truncate">{{ $booking->servicePlan?->name }}</p>
                                                <p class="text-xs text-base-content/60">{{ $booking->start_time->format('M d, g:i A') }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Quick Info --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--info-circle] size-5"></span>
                                Quick Info
                            </h2>
                            <div class="space-y-3 mt-4 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Member Since</span>
                                    <span class="font-medium">{{ $instructor->created_at->format('M d, Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Last Updated</span>
                                    <span class="font-medium">{{ $instructor->updated_at->diffForHumans() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Class Plans</span>
                                    <span class="font-medium">{{ $classPlans->count() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Service Plans</span>
                                    <span class="font-medium">{{ $instructor->servicePlans->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Schedule Tab --}}
        <div class="tab-content {{ $tab === 'schedule' ? 'active' : 'hidden' }}" data-content="schedule">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Upcoming Sessions --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--calendar-plus] size-5 text-primary"></span>
                            Upcoming Sessions
                        </h2>
                        @if($upcomingSessions->isEmpty())
                            <div class="text-center py-8">
                                <span class="icon-[tabler--calendar-off] size-12 text-base-content/20 mx-auto"></span>
                                <p class="text-base-content/60 mt-4">No upcoming sessions scheduled.</p>
                            </div>
                        @else
                            <div class="space-y-3 mt-4">
                                @foreach($upcomingSessions as $session)
                                    <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-lg hover:bg-base-200 transition-colors">
                                        <div class="text-center min-w-14">
                                            <p class="text-xs text-base-content/60 uppercase">{{ $session->start_time->format('M') }}</p>
                                            <p class="text-2xl font-bold">{{ $session->start_time->format('d') }}</p>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium truncate">{{ $session->classPlan?->name ?? 'Class Session' }}</p>
                                            <p class="text-sm text-base-content/60">{{ $session->start_time->format('g:i A') }} - {{ $session->end_time->format('g:i A') }}</p>
                                            @if($session->location)
                                                <p class="text-xs text-base-content/50">{{ $session->location->name }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($session->primary_instructor_id === $instructor->id)
                                                <span class="badge badge-primary badge-sm">Primary</span>
                                            @else
                                                <span class="badge badge-ghost badge-sm">Backup</span>
                                            @endif
                                            <a href="{{ route('class-sessions.show', $session) }}" class="btn btn-ghost btn-xs btn-circle">
                                                <span class="icon-[tabler--chevron-right] size-4"></span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Recent Sessions --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--history] size-5 text-success"></span>
                            Recent Sessions
                        </h2>
                        @if($recentSessions->isEmpty())
                            <div class="text-center py-8">
                                <span class="icon-[tabler--calendar-check] size-12 text-base-content/20 mx-auto"></span>
                                <p class="text-base-content/60 mt-4">No past sessions yet.</p>
                            </div>
                        @else
                            <div class="space-y-3 mt-4">
                                @foreach($recentSessions as $session)
                                    <div class="flex items-center gap-3 p-3 bg-success/5 rounded-lg">
                                        <div class="text-center min-w-14">
                                            <p class="text-xs text-base-content/60 uppercase">{{ $session->start_time->format('M') }}</p>
                                            <p class="text-2xl font-bold text-success">{{ $session->start_time->format('d') }}</p>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium truncate">{{ $session->classPlan?->name ?? 'Class Session' }}</p>
                                            <p class="text-sm text-base-content/60">{{ $session->start_time->format('g:i A') }}</p>
                                        </div>
                                        <span class="icon-[tabler--check] size-5 text-success"></span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Assignments Tab --}}
        <div class="tab-content {{ $tab === 'assignments' ? 'active' : 'hidden' }}" data-content="assignments">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Class Plans --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--yoga] size-5 text-primary"></span>
                            Assigned Class Plans
                        </h2>
                        @if($classPlans->isEmpty())
                            <div class="text-center py-8">
                                <span class="icon-[tabler--yoga] size-12 text-base-content/20 mx-auto"></span>
                                <p class="text-base-content/60 mt-4">No class plans assigned.</p>
                            </div>
                        @else
                            <div class="space-y-3 mt-4">
                                @foreach($classPlans as $plan)
                                    @if($plan)
                                        <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-lg hover:bg-base-200 transition-colors">
                                            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                                <span class="icon-[tabler--yoga] size-5 text-primary"></span>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium">{{ $plan->name }}</p>
                                                <p class="text-sm text-base-content/60">{{ $plan->category ?? 'Uncategorized' }}</p>
                                            </div>
                                            <a href="{{ route('class-plans.show', $plan) }}" class="btn btn-ghost btn-xs">
                                                <span class="icon-[tabler--external-link] size-4"></span>
                                            </a>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Service Plans --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--massage] size-5 text-secondary"></span>
                            Assigned Service Plans
                        </h2>
                        @if($instructor->servicePlans->isEmpty())
                            <div class="text-center py-8">
                                <span class="icon-[tabler--massage] size-12 text-base-content/20 mx-auto"></span>
                                <p class="text-base-content/60 mt-4">No service plans assigned.</p>
                            </div>
                        @else
                            <div class="space-y-3 mt-4">
                                @foreach($instructor->servicePlans as $plan)
                                    <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-lg hover:bg-base-200 transition-colors">
                                        <div class="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center">
                                            <span class="icon-[tabler--massage] size-5 text-secondary"></span>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-medium">{{ $plan->name }}</p>
                                            <p class="text-sm text-base-content/60">{{ $plan->duration_minutes }} min</p>
                                        </div>
                                        <a href="{{ route('service-plans.show', $plan) }}" class="btn btn-ghost btn-xs">
                                            <span class="icon-[tabler--external-link] size-4"></span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Billing Tab --}}
        <div class="tab-content {{ $tab === 'billing' ? 'active' : 'hidden' }}" data-content="billing">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--currency-dollar] size-5"></span>
                            Rate Settings
                        </h2>
                        <div class="space-y-4 mt-4">
                            <div>
                                <label class="text-sm text-base-content/60">Rate Type</label>
                                <p class="font-medium text-lg">{{ $instructor->rate_type ? \App\Models\Instructor::getRateTypes()[$instructor->rate_type] : '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-base-content/60">Rate Amount</label>
                                <p class="font-medium text-2xl text-success">{{ $instructor->getFormattedRate() ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-base-content/60">Employment Type</label>
                                <p class="font-medium">{{ $instructor->getFormattedEmploymentType() ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--chart-bar] size-5"></span>
                            This Month Activity
                        </h2>
                        <div class="space-y-4 mt-4">
                            <div class="flex items-center justify-between p-3 bg-primary/5 rounded-lg">
                                <span class="text-base-content/70">Classes Taught</span>
                                <span class="font-bold text-2xl">{{ $monthlyStats['classes_count'] }}</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-secondary/5 rounded-lg">
                                <span class="text-base-content/70">Services Delivered</span>
                                <span class="font-bold text-2xl">{{ $monthlyStats['services_count'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card bg-gradient-to-br from-success/10 to-success/5 border-success/20">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--wallet] size-5"></span>
                            Estimated Earnings
                        </h2>
                        <div class="mt-4">
                            @if($monthlyStats['estimated_earnings'] !== null)
                                <p class="text-4xl font-bold text-success">${{ number_format($monthlyStats['estimated_earnings'], 2) }}</p>
                                <p class="text-sm text-base-content/60 mt-2">Based on {{ $instructor->rate_type ? \App\Models\Instructor::getRateTypes()[$instructor->rate_type] : 'rate' }}</p>
                            @else
                                <p class="text-base-content/60">Unable to calculate</p>
                                <p class="text-sm text-base-content/60 mt-2">Rate information required</p>
                            @endif
                        </div>
                        <div class="alert alert-soft alert-warning mt-4 text-xs">
                            <span class="icon-[tabler--info-circle] size-4"></span>
                            <span>Estimate only. Final payout handled outside FitCRM.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes Tab --}}
        <div class="tab-content {{ $tab === 'notes' ? 'active' : 'hidden' }}" data-content="notes">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
                                    <input type="checkbox" id="is_visible_to_instructor" name="is_visible_to_instructor" class="checkbox checkbox-sm">
                                    <label for="is_visible_to_instructor" class="text-sm">Visible to instructor</label>
                                </div>
                                <button type="submit" class="btn btn-primary w-full">
                                    <span class="icon-[tabler--plus] size-4"></span>
                                    Add Note
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--notes] size-5"></span>
                                Notes History
                            </h2>
                            <div id="notesList" class="space-y-4 mt-4">
                                @forelse($instructor->notes as $note)
                                    <div class="p-4 bg-base-200/50 rounded-lg" data-note-id="{{ $note->id }}">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <span class="{{ \App\Models\InstructorNote::getNoteTypeIcon($note->note_type) }} size-4"></span>
                                                <span class="badge badge-soft badge-sm {{ \App\Models\InstructorNote::getNoteTypeBadgeClass($note->note_type) }}">
                                                    {{ $noteTypes[$note->note_type] ?? $note->note_type }}
                                                </span>
                                                @if($note->is_visible_to_instructor)
                                                    <span class="badge badge-soft badge-info badge-xs">Visible to Instructor</span>
                                                @endif
                                            </div>
                                            <details class="dropdown dropdown-end">
                                                <summary class="btn btn-ghost btn-xs btn-square cursor-pointer list-none">
                                                    <span class="icon-[tabler--dots] size-4"></span>
                                                </summary>
                                                <ul class="dropdown-content menu bg-base-100 rounded-box w-32 p-2 shadow-lg border z-50">
                                                    <li><button type="button" onclick="deleteNote({{ $note->id }})" class="text-error"><span class="icon-[tabler--trash] size-4"></span> Delete</button></li>
                                                </ul>
                                            </details>
                                        </div>
                                        <p class="mt-2">{{ $note->content }}</p>
                                        <p class="text-xs text-base-content/60 mt-3">
                                            {{ $note->author?->full_name ?? 'System' }} &bull; {{ $note->created_at->format('M d, Y g:i A') }}
                                        </p>
                                    </div>
                                @empty
                                    <div class="text-center py-8">
                                        <span class="icon-[tabler--notes] size-12 text-base-content/20 mx-auto"></span>
                                        <p class="text-base-content/60 mt-4">No notes yet. Add the first note above.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const instructorId = {{ $instructor->id }};
const instructorName = '{{ addslashes($instructor->name) }}';
const instructorEmail = '{{ addslashes($instructor->email ?? "") }}';
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Tab switching
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tabs .tab');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', targetTab);
            window.history.pushState({}, '', url);

            tabs.forEach(t => t.classList.remove('tab-active'));
            this.classList.add('tab-active');

            contents.forEach(content => {
                content.classList.toggle('hidden', content.dataset.content !== targetTab);
                content.classList.toggle('active', content.dataset.content === targetTab);
            });
        });
    });
});

function showResetPasswordModal() {
    ConfirmModals.resetPassword({
        title: 'Reset Password',
        message: `Send a password reset email to ${instructorName}?`,
        email: instructorEmail,
        action: `/instructors/${instructorId}/reset-password`
    });
}

function showMakeInactiveModal() {
    showConfirmModal({
        title: 'Deactivate Instructor',
        message: `Are you sure you want to deactivate "${instructorName}"?`,
        type: 'warning',
        btnText: 'Deactivate',
        btnIcon: 'icon-[tabler--user-off]',
        onConfirm: () => toggleStatusRequest(false)
    });
}

function showActivateModal() {
    showConfirmModal({
        title: 'Activate Instructor',
        message: `Are you sure you want to activate "${instructorName}"?`,
        type: 'success',
        btnText: 'Activate',
        btnIcon: 'icon-[tabler--user-check]',
        onConfirm: () => toggleStatusRequest()
    });
}

function toggleStatusRequest(forceConfirm = false) {
    fetch(`/instructors/${instructorId}/toggle-status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ confirm: forceConfirm })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Status updated.', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else if (data.warning) {
            showToast(data.message, 'warning');
            setTimeout(() => {
                showConfirmModal({
                    title: 'Proceed Anyway?',
                    message: `This instructor has ${data.future_sessions} upcoming session(s). Deactivate anyway?`,
                    type: 'warning',
                    btnText: 'Yes, Deactivate',
                    onConfirm: () => toggleStatusRequest(true)
                });
            }, 500);
        } else {
            showToast(data.message || 'Error occurred', 'error');
        }
    });
}

document.getElementById('addNoteForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = {
        note_type: document.getElementById('note_type').value,
        content: document.getElementById('content').value,
        is_visible_to_instructor: document.getElementById('is_visible_to_instructor').checked
    };
    if (!formData.content.trim()) { alert('Please enter note content.'); return; }

    fetch(`/instructors/${instructorId}/notes`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) window.location.reload();
        else alert(data.message || 'Error occurred');
    });
});

function deleteNote(noteId) {
    showConfirmModal({
        title: 'Delete Note',
        message: 'Delete this note? This cannot be undone.',
        type: 'danger',
        btnText: 'Delete',
        onConfirm: () => {
            fetch(`/instructor-notes/${noteId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-note-id="${noteId}"]`)?.remove();
                    showToast('Note deleted.', 'success');
                }
            });
        }
    });
}
</script>
@endpush
@endsection
