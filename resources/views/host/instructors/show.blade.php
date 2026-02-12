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
            <p class="text-sm opacity-90">This instructor cannot be assigned to classes until their profile is completed. Missing: <strong>{{ implode(', ', $instructor->getMissingProfileFields()) }}</strong></p>
        </div>
        <a href="{{ route('instructors.edit', $instructor) }}" class="btn btn-warning btn-sm">
            Complete Profile
        </a>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4 relative z-50">
        <div class="flex items-start gap-4 flex-1">
            @if($instructor->photo_url)
                <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}"
                     class="w-20 h-20 rounded-full object-cover">
            @else
                <div class="avatar placeholder">
                    <div class="bg-primary text-primary-content w-20 h-20 rounded-full font-bold text-2xl">
                        {{ $instructor->initials }}
                    </div>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold">{{ $instructor->name }}</h1>
                @if($instructor->email)
                    <p class="text-base-content/60">{{ $instructor->email }}</p>
                @endif
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    @if($instructor->status === 'pending' || !$instructor->isProfileComplete())
                        <span class="badge badge-soft badge-warning">
                            <span class="icon-[tabler--alert-triangle] size-3 mr-1"></span>
                            Pending Setup
                        </span>
                    @elseif($instructor->is_active)
                        <span class="badge badge-soft badge-success">Active</span>
                    @else
                        <span class="badge badge-soft badge-neutral">Inactive</span>
                    @endif
                    @if($instructor->hasAccount())
                        <span class="badge badge-soft badge-info badge-sm">Has Login</span>
                    @elseif($instructor->hasPendingInvitation())
                        <span class="badge badge-soft badge-warning badge-sm">Invite Pending</span>
                    @endif
                    @if($instructor->specialties)
                        @foreach(array_slice($instructor->specialties, 0, 3) as $specialty)
                            <span class="badge badge-soft badge-primary badge-sm">{{ $specialty }}</span>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="flex items-center gap-2">
            @if($instructor->hasAccount())
                <button type="button" onclick="showResetPasswordModal()" class="btn btn-soft btn-sm">
                    <span class="icon-[tabler--key] size-4"></span>
                    Reset Password
                </button>
            @else
                <button type="button" class="btn btn-soft btn-sm" disabled title="No login account linked">
                    <span class="icon-[tabler--key] size-4"></span>
                    Reset Password
                </button>
            @endif

            <a href="{{ route('instructors.edit', $instructor) }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--edit] size-4"></span>
                Edit
            </a>

            @if($instructor->is_active)
                <button type="button" onclick="showMakeInactiveModal()" class="btn btn-warning btn-sm">
                    <span class="icon-[tabler--user-off] size-4"></span>
                    Make Inactive
                </button>
            @else
                <button type="button" onclick="showActivateModal()" class="btn btn-success btn-sm">
                    <span class="icon-[tabler--user-check] size-4"></span>
                    Activate
                </button>
            @endif
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tabs tabs-bordered relative z-10" role="tablist">
        <button class="tab {{ $tab === 'overview' ? 'tab-active' : '' }}" data-tab="overview" role="tab">
            <span class="icon-[tabler--user] size-4 mr-2"></span>
            Overview
        </button>
        <button class="tab {{ $tab === 'notes' ? 'tab-active' : '' }}" data-tab="notes" role="tab">
            <span class="icon-[tabler--notes] size-4 mr-2"></span>
            Notes
            @if($instructor->notes->count() > 0)
                <span class="badge badge-sm badge-primary ml-2">{{ $instructor->notes->count() }}</span>
            @endif
        </button>
        <button class="tab {{ $tab === 'schedule' ? 'tab-active' : '' }}" data-tab="schedule" role="tab">
            <span class="icon-[tabler--calendar] size-4 mr-2"></span>
            Schedule
        </button>
        <button class="tab {{ $tab === 'assignments' ? 'tab-active' : '' }}" data-tab="assignments" role="tab">
            <span class="icon-[tabler--list-check] size-4 mr-2"></span>
            Classes & Services
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
                                    <label class="text-sm text-base-content/60">Status</label>
                                    <p class="font-medium">{{ $instructor->is_active ? 'Active' : 'Inactive' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Linked Account</label>
                                    <p class="font-medium">{{ $instructor->hasAccount() ? 'Yes' : 'No' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Created</label>
                                    <p class="font-medium">{{ $instructor->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            @if($instructor->bio)
                                <div class="mt-4">
                                    <label class="text-sm text-base-content/60">Bio</label>
                                    <p class="mt-1">{{ $instructor->bio }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Employment & Rate --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--briefcase] size-5"></span>
                                Employment & Rate
                            </h2>
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="text-sm text-base-content/60">Employment Type</label>
                                    <p class="font-medium">{{ $instructor->getFormattedEmploymentType() ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Rate</label>
                                    <p class="font-medium text-success">{{ $instructor->getFormattedRate() ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Hours per Week</label>
                                    <p class="font-medium">{{ $instructor->hours_per_week ? $instructor->hours_per_week . ' hrs' : '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Max Classes per Week</label>
                                    <p class="font-medium">{{ $instructor->max_classes_per_week ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Working Days</label>
                                    <p class="font-medium">{{ $instructor->getFormattedWorkingDays() }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Default Availability</label>
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
                                <div class="mt-4">
                                    <label class="text-sm text-base-content/60">Compensation Notes</label>
                                    <p class="mt-1">{{ $instructor->compensation_notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Teaching Info --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--school] size-5"></span>
                                Teaching Info
                            </h2>
                            <div class="space-y-4 mt-4">
                                <div>
                                    <label class="text-sm text-base-content/60">Specialties</label>
                                    @if($instructor->specialties && count($instructor->specialties) > 0)
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($instructor->specialties as $specialty)
                                                <span class="badge badge-soft badge-primary badge-sm">{{ $specialty }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-base-content/60">-</p>
                                    @endif
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Certifications</label>
                                    <p class="mt-1">{{ $instructor->certifications ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Public Visibility</label>
                                    <p class="font-medium">{{ $instructor->is_visible ? 'Visible on booking page' : 'Hidden' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Next Session --}}
                    @if($upcomingSessions->isNotEmpty())
                        <div class="card bg-base-100">
                            <div class="card-body">
                                <h2 class="card-title text-lg">
                                    <span class="icon-[tabler--calendar-event] size-5"></span>
                                    Next Session
                                </h2>
                                @php $nextSession = $upcomingSessions->first(); @endphp
                                <div class="mt-4">
                                    <p class="font-medium">{{ $nextSession->classPlan?->name ?? 'Class Session' }}</p>
                                    <p class="text-sm text-base-content/60">
                                        {{ $nextSession->start_time->format('D, M d') }} at {{ $nextSession->start_time->format('g:i A') }}
                                    </p>
                                    @if($nextSession->location)
                                        <p class="text-sm text-base-content/60">{{ $nextSession->location->name }}</p>
                                    @endif
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

                {{-- Notes List --}}
                <div class="lg:col-span-2">
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--notes] size-5"></span>
                                Notes
                            </h2>
                            <div id="notesList" class="space-y-4 mt-4">
                                @forelse($instructor->notes as $note)
                                    <div class="border-b border-base-200 pb-4 last:border-0" data-note-id="{{ $note->id }}">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <span class="{{ \App\Models\InstructorNote::getNoteTypeIcon($note->note_type) }} size-4"></span>
                                                <span class="badge badge-soft badge-sm {{ \App\Models\InstructorNote::getNoteTypeBadgeClass($note->note_type) }}">
                                                    {{ $noteTypes[$note->note_type] ?? $note->note_type }}
                                                </span>
                                                @if($note->is_visible_to_instructor)
                                                    <span class="badge badge-soft badge-info badge-xs">Instructor Visible</span>
                                                @endif
                                            </div>
                                            <details class="dropdown dropdown-end">
                                                <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                                                    <span class="icon-[tabler--dots] size-4"></span>
                                                </summary>
                                                <ul class="dropdown-content menu bg-base-100 rounded-box w-32 p-2 shadow-lg border border-base-300 z-50">
                                                    <li>
                                                        <button type="button" onclick="editNote({{ $note->id }})">
                                                            <span class="icon-[tabler--edit] size-4"></span> Edit
                                                        </button>
                                                    </li>
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

        {{-- Schedule Tab --}}
        <div class="tab-content {{ $tab === 'schedule' ? 'active' : 'hidden' }}" data-content="schedule">
            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--calendar] size-5"></span>
                            Upcoming Sessions
                        </h2>
                    </div>

                    @if($upcomingSessions->isEmpty())
                        <div class="text-center py-12">
                            <span class="icon-[tabler--calendar-off] size-12 text-base-content/20 mx-auto"></span>
                            <p class="text-base-content/60 mt-4">No upcoming sessions scheduled.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto mt-4">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Class</th>
                                        <th>Location</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingSessions as $session)
                                        <tr>
                                            <td>
                                                <div class="font-medium">{{ $session->start_time->format('D, M d') }}</div>
                                                <div class="text-sm text-base-content/60">{{ $session->start_time->format('g:i A') }} - {{ $session->end_time->format('g:i A') }}</div>
                                            </td>
                                            <td>{{ $session->classPlan?->name ?? 'N/A' }}</td>
                                            <td>{{ $session->location?->name ?? '-' }}</td>
                                            <td>
                                                @if($session->primary_instructor_id === $instructor->id)
                                                    <span class="badge badge-soft badge-primary badge-sm">Primary</span>
                                                @else
                                                    <span class="badge badge-soft badge-secondary badge-sm">Backup</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $statusBadge = match($session->status) {
                                                        'published' => 'badge-success',
                                                        'draft' => 'badge-warning',
                                                        'cancelled' => 'badge-error',
                                                        default => 'badge-neutral'
                                                    };
                                                @endphp
                                                <span class="badge badge-soft badge-sm {{ $statusBadge }}">{{ ucfirst($session->status) }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('class-sessions.show', $session) }}" class="btn btn-ghost btn-xs">
                                                    <span class="icon-[tabler--eye] size-4"></span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
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
                            <span class="icon-[tabler--yoga] size-5"></span>
                            Class Plans
                        </h2>
                        @if($classPlans->isEmpty())
                            <p class="text-base-content/60 text-center py-8">No class plans assigned.</p>
                        @else
                            <div class="space-y-3 mt-4">
                                @foreach($classPlans as $plan)
                                    @if($plan)
                                        <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                                            <div>
                                                <p class="font-medium">{{ $plan->name }}</p>
                                                <p class="text-sm text-base-content/60">{{ $plan->category ?? 'Uncategorized' }}</p>
                                            </div>
                                            <a href="{{ route('class-plans.show', $plan) }}" class="btn btn-ghost btn-xs">
                                                <span class="icon-[tabler--eye] size-4"></span>
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
                            <span class="icon-[tabler--massage] size-5"></span>
                            Service Plans
                        </h2>
                        @if($instructor->servicePlans->isEmpty())
                            <p class="text-base-content/60 text-center py-8">No service plans assigned.</p>
                        @else
                            <div class="space-y-3 mt-4">
                                @foreach($instructor->servicePlans as $plan)
                                    <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                                        <div>
                                            <p class="font-medium">{{ $plan->name }}</p>
                                            <p class="text-sm text-base-content/60">{{ $plan->category ?? 'Uncategorized' }}</p>
                                        </div>
                                        <a href="{{ route('service-plans.show', $plan) }}" class="btn btn-ghost btn-xs">
                                            <span class="icon-[tabler--eye] size-4"></span>
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
                {{-- Rate Settings --}}
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
                                <p class="font-medium text-lg text-success">
                                    {{ $instructor->rate_amount ? '$' . number_format($instructor->rate_amount, 2) : '-' }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm text-base-content/60">Employment Type</label>
                                <p class="font-medium">{{ $instructor->getFormattedEmploymentType() ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- This Month Stats --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--chart-bar] size-5"></span>
                            This Month
                        </h2>
                        <div class="space-y-4 mt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-base-content/60">Classes Taught</span>
                                <span class="font-bold text-xl">{{ $monthlyStats['classes_count'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-base-content/60">Services Delivered</span>
                                <span class="font-bold text-xl">{{ $monthlyStats['services_count'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Estimated Earnings --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--wallet] size-5"></span>
                            Estimated Earnings
                        </h2>
                        <div class="mt-4">
                            @if($monthlyStats['estimated_earnings'] !== null)
                                <p class="text-3xl font-bold text-success">${{ number_format($monthlyStats['estimated_earnings'], 2) }}</p>
                                <p class="text-sm text-base-content/60 mt-2">Based on {{ $instructor->rate_type ? \App\Models\Instructor::getRateTypes()[$instructor->rate_type] : 'rate' }} rate</p>
                            @else
                                <p class="text-base-content/60">Unable to calculate</p>
                                <p class="text-sm text-base-content/60 mt-2">Rate information required</p>
                            @endif
                        </div>
                        <div class="alert alert-soft alert-warning mt-4">
                            <span class="icon-[tabler--info-circle] size-4"></span>
                            <span class="text-sm">Estimate only. Final payout handled outside FitCRM.</span>
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
const isActive = {{ $instructor->is_active ? 'true' : 'false' }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Tab switching
document.addEventListener('DOMContentLoaded', function() {
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
        message: `Send a password reset email to ${instructorName}?`,
        email: instructorEmail,
        action: `/instructors/${instructorId}/reset-password`
    });
}

// Show Make Inactive Modal
function showMakeInactiveModal() {
    showConfirmModal({
        title: 'Make Instructor Inactive',
        message: `Are you sure you want to make "${instructorName}" inactive? They will not be assigned to new classes or services.`,
        type: 'warning',
        btnText: 'Make Inactive',
        btnIcon: 'icon-[tabler--user-off]',
        onConfirm: function() {
            toggleStatusRequest(false);
        }
    });
}

// Show Activate Modal
function showActivateModal() {
    showConfirmModal({
        title: 'Activate Instructor',
        message: `Are you sure you want to activate "${instructorName}"?`,
        type: 'success',
        btnText: 'Activate',
        btnIcon: 'icon-[tabler--user-check]',
        onConfirm: function() {
            toggleStatusRequest();
        }
    });
}

// Toggle Status Request (called after confirmation)
function toggleStatusRequest(forceConfirm = false) {
    fetch(`/instructors/${instructorId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ confirm: forceConfirm })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Status updated successfully.', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else if (data.warning) {
            // Show warning toast first
            showToast(data.message, 'warning');

            // Then show confirmation modal to proceed
            setTimeout(() => {
                showConfirmModal({
                    title: 'Proceed Anyway?',
                    message: `This instructor has ${data.future_sessions} upcoming session(s). Do you still want to make them inactive?`,
                    type: 'warning',
                    btnText: 'Yes, Make Inactive',
                    btnIcon: 'icon-[tabler--user-off]',
                    onConfirm: function() {
                        toggleStatusRequest(true);
                    }
                });
            }, 500);
        } else {
            showToast(data.message || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

// Add Note
document.getElementById('addNoteForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = {
        note_type: document.getElementById('note_type').value,
        content: document.getElementById('content').value,
        is_visible_to_instructor: document.getElementById('is_visible_to_instructor').checked
    };

    if (!formData.content.trim()) {
        alert('Please enter note content.');
        return;
    }

    fetch(`/instructors/${instructorId}/notes`, {
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
            fetch(`/instructor-notes/${noteId}`, {
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

// Edit Note (placeholder - would need a modal for full implementation)
function editNote(noteId) {
    showToast('Edit functionality coming soon. For now, please delete and re-add the note.', 'info');
}
</script>
@endpush
@endsection
