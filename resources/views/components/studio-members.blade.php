{{--
    Studio Members Component

    Multi-select dropdowns for assigning staff members and instructors.
    Automatically loads the studio's team members and standalone instructors.

    Usage:
    <x-studio-members
        :selected-staff="$assignedStaffMemberIds"
        :selected-instructors="$assignedInstructorIds ?? []"
    />

    Props:
    - selected-staff:      Array of selected staff member IDs (default: [])
    - selected-instructors: Array of selected instructor IDs (default: [])
    - title:               Card title (default: 'Assigned Staff & Instructors')
    - staff-label:         Label for staff dropdown (default: 'Staff Members')
    - instructor-label:    Label for instructor dropdown (default: 'Instructors')
    - staff-name:          Form field name for staff (default: 'staff_member_ids')
    - instructor-name:     Form field name for instructors (default: 'instructor_ids')
    - no-card:             Render without card wrapper (default: false)
--}}

@props([
    'selectedStaff' => [],
    'selectedInstructors' => [],
    'title' => 'Assigned Staff & Instructors',
    'staffLabel' => 'Staff Members',
    'instructorLabel' => 'Instructors',
    'staffName' => 'staff_member_ids',
    'instructorName' => 'instructor_ids',
    'noCard' => false,
])

@php
    $studio = $host ?? $currentHost ?? auth()->user()->host;
    $studioStaffMembers = $studio->teamMembers()->orderBy('first_name')->orderBy('last_name')->get();
    $staffUserIds = $studioStaffMembers->pluck('id')->toArray();
    $studioInstructors = $studio->instructors()->orderBy('name')->get()->filter(function ($i) use ($staffUserIds) {
        return !$i->user_id || !in_array($i->user_id, $staffUserIds);
    });
    $componentId = 'studio-members-' . uniqid();
@endphp

@if(!$noCard)
<div class="card bg-base-100">
    <div class="card-header">
        <h3 class="card-title">{{ $title }}</h3>
    </div>
    <div class="card-body space-y-4">
@else
<div class="space-y-4">
@endif

    @if($studioStaffMembers->isEmpty() && $studioInstructors->isEmpty())
        <p class="text-base-content/60 text-sm">No team members available. <a href="{{ route('settings.team.users') }}" class="link link-primary">Add team members</a> first.</p>
    @else
        {{-- Staff Members Multi-Select --}}
        @if($studioStaffMembers->isNotEmpty())
        <div>
            <label class="label-text font-medium mb-2 block">{{ $staffLabel }}</label>
            <select id="{{ $componentId }}-staff" name="{{ $staffName }}[]" multiple class="hidden"
                data-select='{
                    "hasSearch": true,
                    "isSearchDirectMatch": false,
                    "searchPlaceholder": "Search {{ strtolower($staffLabel) }}...",
                    "placeholder": "Select {{ strtolower($staffLabel) }}...",
                    "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                    "toggleClasses": "advance-select-toggle select-disabled:pointer-events-none select-disabled:opacity-40",
                    "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                    "optionClasses": "advance-select-option selected:select-active",
                    "optionTemplate": "<div class=\"flex items-center gap-2 w-full\"><span data-title class=\"flex-1 text-start\"></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                    "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                }'>
                <option value="">Choose</option>
                @foreach($studioStaffMembers as $member)
                    <option value="{{ $member->id }}"
                        data-photo="{{ $member->profile_photo_url ?? '' }}"
                        data-initials="{{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}"
                        {{ in_array($member->id, old($staffName, $selectedStaff)) ? 'selected' : '' }}>
                        {{ $member->full_name }} ({{ ucfirst($member->pivot->role) }})
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Standalone Instructors Multi-Select --}}
        @if($studioInstructors->isNotEmpty())
        <div>
            <label class="label-text font-medium mb-2 block">{{ $instructorLabel }}</label>
            <select id="{{ $componentId }}-instructors" name="{{ $instructorName }}[]" multiple class="hidden"
                data-select='{
                    "hasSearch": true,
                    "isSearchDirectMatch": false,
                    "searchPlaceholder": "Search {{ strtolower($instructorLabel) }}...",
                    "placeholder": "Select {{ strtolower($instructorLabel) }}...",
                    "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                    "toggleClasses": "advance-select-toggle select-disabled:pointer-events-none select-disabled:opacity-40",
                    "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
                    "optionClasses": "advance-select-option selected:select-active",
                    "optionTemplate": "<div class=\"flex items-center gap-2 w-full\"><span data-title class=\"flex-1 text-start\"></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                    "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                }'>
                <option value="">Choose</option>
                @foreach($studioInstructors as $instructor)
                    <option value="{{ $instructor->id }}"
                        data-photo="{{ $instructor->photo_url ?? '' }}"
                        data-initials="{{ $instructor->initials ?? strtoupper(substr($instructor->name, 0, 2)) }}"
                        {{ in_array($instructor->id, old($instructorName, $selectedInstructors)) ? 'selected' : '' }}>
                        {{ $instructor->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
    @endif

@if(!$noCard)
    </div>
</div>
@else
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inject avatars into advance-select dropdown options
    function injectAvatars(selectId) {
        var selectEl = document.getElementById(selectId);
        if (!selectEl) return;

        var options = selectEl.querySelectorAll('option[data-initials]');
        var parentWrapper = selectEl.closest('div');
        if (!parentWrapper) return;

        // Wait for FlyonUI to render the dropdown
        setTimeout(function() {
            var dropdownItems = parentWrapper.querySelectorAll('.advance-select-option');

            dropdownItems.forEach(function(item) {
                var value = item.getAttribute('data-value');
                if (!value) return;

                // Find matching option
                var option = selectEl.querySelector('option[value="' + value + '"]');
                if (!option) return;

                var photo = option.getAttribute('data-photo');
                var initials = option.getAttribute('data-initials');
                var titleEl = item.querySelector('[data-title]');
                if (!titleEl) return;

                // Don't inject twice
                if (item.querySelector('.member-avatar')) return;

                var avatar = document.createElement('span');
                avatar.className = 'member-avatar shrink-0 size-6 rounded-full flex items-center justify-center text-xs font-bold me-2';

                if (photo) {
                    avatar.innerHTML = '<img src="' + photo + '" class="size-6 rounded-full object-cover" />';
                } else if (initials) {
                    avatar.className += ' bg-primary/15 text-primary';
                    avatar.textContent = initials;
                }

                titleEl.parentNode.insertBefore(avatar, titleEl);
            });
        }, 150);
    }

    @if($studioStaffMembers->isNotEmpty())
    injectAvatars('{{ $componentId }}-staff');
    @endif
    @if($studioInstructors->isNotEmpty())
    injectAvatars('{{ $componentId }}-instructors');
    @endif
});
</script>
@endpush
