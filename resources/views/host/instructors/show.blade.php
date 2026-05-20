@extends('layouts.dashboard')

@section('title', $instructor->name . ' — ' . ($trans['nav.instructor'] ?? 'Instructor'))

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        @if(request('ref') === 'team')
        <li><a href="{{ route('settings.index') }}">{{ $trans['nav.settings'] ?? 'Settings' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.team.users') }}">{{ $trans['nav.team'] ?? 'Users & Roles' }}</a></li>
        @else
        <li><a href="{{ route('instructors.index') }}">{{ $trans['nav.instructors'] ?? 'Instructors' }}</a></li>
        @endif
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $instructor->name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Incomplete Profile Alert --}}
    @if(!$instructor->isProfileComplete())
    <div class="alert alert-warning shadow-lg flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="icon-[tabler--alert-triangle] size-6 shrink-0"></span>
            <div>
                <h3 class="font-bold">{{ $trans['instructors.profile_incomplete'] ?? 'Profile incomplete' }}</h3>
                <p class="text-sm">{{ $trans['common.missing'] ?? 'Missing' }}: <strong>{{ implode(', ', $instructor->getMissingProfileFields()) }}</strong></p>
            </div>
        </div>
        <a href="{{ route('instructors.edit', $instructor) }}" class="btn btn-sm btn-outline ml-auto shrink-0 !text-white !border-white hover:!bg-white hover:!text-warning">
            {{ $trans['instructors.complete_in_team'] ?? 'Complete in Users & Roles' }}
        </a>
    </div>
    @endif

    {{-- Hero Header --}}
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            @php
                $headerPhoto = $instructor->photo_url ?? $instructor->user?->profile_photo_url;
            @endphp
            @if($headerPhoto)
                <img src="{{ $headerPhoto }}" alt="{{ $instructor->name }}"
                     class="w-24 h-24 rounded-lg object-cover">
            @else
                <div class="w-24 h-24 rounded-lg bg-primary/10 flex items-center justify-center font-bold text-3xl text-primary">
                    {{ $instructor->initials }}
                </div>
            @endif
            <div class="min-w-0">
                <h1 class="text-2xl font-bold truncate">{{ $instructor->name }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    @if($instructor->status === 'pending' || !$instructor->isProfileComplete())
                        <span class="badge badge-soft badge-warning badge-sm">
                            <span class="icon-[tabler--alert-triangle] size-3"></span>
                            {{ $trans['instructors.pending_setup'] ?? 'Pending Setup' }}
                        </span>
                    @elseif($instructor->is_active)
                        <span class="badge badge-soft badge-success badge-sm">{{ $trans['common.active'] ?? 'Active' }}</span>
                    @else
                        <span class="badge badge-soft badge-neutral badge-sm">{{ $trans['common.inactive'] ?? 'Inactive' }}</span>
                    @endif
                    @if($instructor->is_visible)
                        <span class="badge badge-soft badge-info badge-sm">Visible on Booking</span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm text-base-content/60">
                    @if($instructor->email)
                        <span class="flex items-center gap-1.5 min-w-0"><span class="icon-[tabler--mail] size-4 shrink-0"></span><span class="truncate">{{ $instructor->email }}</span></span>
                    @endif
                    @if($instructor->phone)
                        <span class="flex items-center gap-1.5"><span class="icon-[tabler--phone] size-4 shrink-0"></span>{{ $instructor->phone }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('instructors.edit', $instructor) }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--settings] size-4"></span>
                {{ $trans['instructors.manage_in_team'] ?? 'Manage in Users & Roles' }}
            </a>

            <x-actions-dropdown width="w-56" label="Quick Actions">
                @if($instructor->hasAccount())
                    <li><button type="button" onclick="showResetPasswordModal()" class="w-full text-left flex items-center gap-2">
                        <span class="icon-[tabler--key] size-4"></span>
                        {{ $trans['btn.reset_password'] ?? 'Reset Password' }}
                    </button></li>
                @endif
                @if($instructor->is_active)
                    <li><button type="button" onclick="showMakeInactiveModal()" class="w-full text-left flex items-center gap-2 text-warning">
                        <span class="icon-[tabler--user-off] size-4"></span>
                        {{ $trans['btn.deactivate'] ?? 'Deactivate' }}
                    </button></li>
                @else
                    <li><button type="button" onclick="showActivateModal()" class="w-full text-left flex items-center gap-2 text-success">
                        <span class="icon-[tabler--user-check] size-4"></span>
                        {{ $trans['btn.activate'] ?? 'Activate' }}
                    </button></li>
                @endif
                @if($instructor->user_id)
                    <li><a href="{{ route('settings.team.users.show', $instructor->user_id) }}">
                        <span class="icon-[tabler--user] size-4"></span> View Team Profile
                    </a></li>
                @endif
            </x-actions-dropdown>

            <a href="{{ request('ref') === 'team' ? route('settings.team.users') : route('instructors.index') }}" class="btn btn-ghost btn-sm gap-1.5">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    @php
        // Default to schedule if no tab specified or removed tabs were requested
        $tab = in_array($tab, ['schedule', 'assignments', 'notes']) ? $tab : 'schedule';
    @endphp
    <div class="tabs tabs-bordered" role="tablist">
        <button class="tab {{ $tab === 'schedule' ? 'tab-active' : '' }}" data-tab="schedule" role="tab">
            <span class="icon-[tabler--calendar] size-4 mr-2"></span>{{ $trans['tabs.schedule'] ?? 'Schedule' }}
        </button>
        <button class="tab {{ $tab === 'assignments' ? 'tab-active' : '' }}" data-tab="assignments" role="tab">
            <span class="icon-[tabler--list-check] size-4 mr-2"></span>{{ $trans['tabs.classes_services'] ?? 'Classes & Services' }}
        </button>
        <button class="tab {{ $tab === 'notes' ? 'tab-active' : '' }}" data-tab="notes" role="tab">
            <span class="icon-[tabler--notes] size-4 mr-2"></span>{{ $trans['tabs.notes'] ?? 'Notes' }}
            @if($instructor->notes->count() > 0)
                <span class="badge badge-sm badge-primary ml-1">{{ $instructor->notes->count() }}</span>
            @endif
        </button>
    </div>

    {{-- Tab Contents --}}
    <div class="tab-contents">
        {{-- Schedule Tab --}}
        <div class="tab-content {{ $tab === 'schedule' ? 'active' : 'hidden' }}" data-content="schedule">
            <div class="space-y-6">
                {{-- Upcoming Sessions --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--calendar-plus] size-5 text-primary"></span>
                                Upcoming Sessions
                                @if($upcomingSessions->total() > 0)
                                    <span class="badge badge-ghost badge-sm ml-1">{{ $upcomingSessions->total() }}</span>
                                @endif
                            </h2>
                            @php
                                $rangeOptions = ['today' => 'Today', 'next3' => 'Next 3 Days', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all' => 'All'];
                            @endphp
                            <div class="join">
                                @foreach($rangeOptions as $key => $label)
                                    <a href="{{ url()->current() . '?' . http_build_query(array_merge(request()->query(), ['tab' => 'schedule', 'range' => $key])) }}"
                                        class="btn btn-sm join-item {{ ($upcomingRange ?? 'today') === $key ? 'btn-primary' : 'btn-ghost' }}">
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @if($upcomingSessions->isEmpty())
                            <div class="text-center py-8">
                                <span class="icon-[tabler--calendar-off] size-12 text-base-content/20 mx-auto"></span>
                                <p class="text-base-content/60 mt-4">No upcoming sessions scheduled.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto mt-3">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th class="whitespace-nowrap">Date</th>
                                            <th class="w-32">Time</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Location</th>
                                            <th>Role</th>
                                            <th class="w-12"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($upcomingSessions as $session)
                                            @php
                                                $linkedMembership = $session->membershipPlans->first();
                                                if ($session->classPlan) {
                                                    $typeLabel = 'Class Plan';
                                                    $typeClass = 'badge-primary';
                                                    $displayName = $session->classPlan->name;
                                                } elseif ($linkedMembership) {
                                                    $typeLabel = 'Membership';
                                                    $typeClass = 'badge-warning';
                                                    $displayName = $session->title ?: $linkedMembership->name;
                                                } else {
                                                    $typeLabel = 'Service Plan';
                                                    $typeClass = 'badge-secondary';
                                                    $displayName = $session->title ?: 'Session';
                                                }
                                                $isPrimary = $session->primary_instructor_id === $instructor->id;
                                                $isBackup = !$isPrimary && (
                                                    $session->backup_instructor_id === $instructor->id
                                                    || $session->backupInstructors->contains('id', $instructor->id)
                                                );
                                            @endphp
                                            <tr class="hover">
                                                <td class="whitespace-nowrap">
                                                    <div class="font-medium text-sm">{{ $session->start_time->format('D, M d, Y') }}</div>
                                                </td>
                                                <td class="text-sm text-base-content/70">{{ $session->start_time->format('g:i A') }} – {{ $session->end_time->format('g:i A') }}</td>
                                                <td><span class="font-medium text-sm">{{ $displayName }}</span></td>
                                                <td><span class="badge {{ $typeClass }} badge-soft badge-xs">{{ $typeLabel }}</span></td>
                                                <td class="text-sm text-base-content/70">{{ $session->location?->name ?? '-' }}</td>
                                                <td>
                                                    @if($isPrimary)
                                                        <span class="badge badge-primary badge-xs">Primary</span>
                                                    @elseif($isBackup)
                                                        <span class="badge badge-warning badge-soft badge-xs gap-1">
                                                            <span class="icon-[tabler--info-circle] size-3"></span>
                                                            Backup
                                                        </span>
                                                    @else
                                                        <span class="badge badge-ghost badge-xs">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('class-sessions.show', $session) }}" class="btn btn-ghost btn-xs btn-circle" title="View">
                                                        <span class="icon-[tabler--chevron-right] size-4"></span>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($upcomingSessions->hasPages())
                                <div class="mt-4">
                                    {{ $upcomingSessions->links() }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- Assignments Tab --}}
        <div class="tab-content {{ $tab === 'assignments' ? 'active' : 'hidden' }}" data-content="assignments">
            <div class="space-y-6">
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

        {{-- Notes Tab --}}
        <div class="tab-content {{ $tab === 'notes' ? 'active' : 'hidden' }}" data-content="notes">
            <div class="space-y-6">
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
