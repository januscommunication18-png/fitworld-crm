{{--
    Studio Member Select (single-select)

    One combined dropdown to pick a single staff member or standalone instructor.
    Posts a single instructor_id value (staff are mapped via host_user.instructor_id).

    Usage:
    <x-studio-member-select name="instructor_id" :selected="$selectedInstructorId" required />

    Props:
    - name:        Form field name (default: 'instructor_id')
    - selected:    Currently selected instructor_id (default: null)
    - placeholder: Placeholder text (default: 'Select a staff member or instructor...')
    - required:    Mark field as required (default: false)
--}}

@props([
    'name' => 'instructor_id',
    'selected' => null,
    'placeholder' => 'Select a staff member or instructor...',
    'required' => false,
])

@php
    $studio = $host ?? $currentHost ?? auth()->user()->host;
    $allTeamMembers = $studio->teamMembers()->orderBy('first_name')->orderBy('last_name')->get();
    $staffUserIds = $allTeamMembers->pluck('id')->toArray();

    // Map of user_id => Instructor for fallback when host_user.instructor_id is not populated.
    $instructorsByUserId = \App\Models\Instructor::where('host_id', $studio->id)
        ->whereIn('user_id', $staffUserIds)
        ->get()
        ->keyBy('user_id');

    $studioStaffMembers = $allTeamMembers->map(function ($m) use ($instructorsByUserId) {
        $m->resolved_instructor_id = $m->pivot->instructor_id ?? optional($instructorsByUserId->get($m->id))->id;
        return $m;
    })->filter(fn ($m) => !empty($m->resolved_instructor_id))->values();

    $studioInstructors = $studio->instructors()->orderBy('name')->get()->filter(function ($i) use ($staffUserIds) {
        return !$i->user_id || !in_array($i->user_id, $staffUserIds);
    })->values();

    $totalOptions = $studioStaffMembers->count() + $studioInstructors->count();
    $useSearch = $totalOptions >= 5;

    $selectConfig = [
        'placeholder' => $placeholder,
        'toggleTag' => '<button type="button" aria-expanded="false"></button>',
        'toggleClasses' => 'advance-select-toggle',
        'dropdownClasses' => 'advance-select-menu max-h-72 overflow-y-auto',
        'optionClasses' => 'advance-select-option selected:select-active',
        'optionTemplate' => '<div class="flex items-center gap-2 w-full"><span data-title class="flex-1 text-start"></span><span class="icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block"></span></div>',
        'extraMarkup' => '<span class="icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2"></span>',
    ];

    if ($useSearch) {
        $selectConfig['hasSearch'] = true;
        $selectConfig['isSearchDirectMatch'] = false;
        $selectConfig['searchPlaceholder'] = 'Search...';
    }

    $componentId = 'studio-member-select-' . uniqid();
    $selectedValue = (string) old($name, $selected);
@endphp

@if($studioStaffMembers->isEmpty() && $studioInstructors->isEmpty())
    <p class="text-base-content/60 text-sm">No team members available. <a href="{{ route('settings.team.users') }}" class="link link-primary">Add team members</a> first.</p>
@else
    <select id="{{ $componentId }}" name="{{ $name }}" class="hidden" {{ $required ? 'required' : '' }}
        data-select='{!! json_encode($selectConfig, JSON_UNESCAPED_SLASHES) !!}'>
        <option value="">{{ $placeholder }}</option>
        @foreach($studioStaffMembers as $member)
            <option value="{{ $member->resolved_instructor_id }}"
                data-photo="{{ $member->profile_photo_url ?? '' }}"
                data-initials="{{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}"
                {{ $selectedValue === (string) $member->resolved_instructor_id ? 'selected' : '' }}>
                {{ $member->full_name }} ({{ ucfirst($member->pivot->role) }})
            </option>
        @endforeach
        @foreach($studioInstructors as $instructor)
            <option value="{{ $instructor->id }}"
                data-photo="{{ $instructor->photo_url ?? '' }}"
                data-initials="{{ $instructor->initials ?? strtoupper(substr($instructor->name, 0, 2)) }}"
                {{ $selectedValue === (string) $instructor->id ? 'selected' : '' }}>
                {{ $instructor->name }}
            </option>
        @endforeach
    </select>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectEl = document.getElementById('{{ $componentId }}');
    if (!selectEl) return;
    var parentWrapper = selectEl.closest('div');
    if (!parentWrapper) return;

    function decorate() {
        var dropdownItems = parentWrapper.querySelectorAll('.advance-select-option');
        dropdownItems.forEach(function(item) {
            var value = item.getAttribute('data-value');
            if (!value) return;
            if (item.querySelector('.member-avatar')) return;
            var option = selectEl.querySelector('option[value="' + value + '"]');
            if (!option) return;
            var titleEl = item.querySelector('[data-title]');
            if (!titleEl) return;
            var photo = option.getAttribute('data-photo');
            var initials = option.getAttribute('data-initials');

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
    }

    setTimeout(decorate, 150);
    new MutationObserver(decorate).observe(parentWrapper, { childList: true, subtree: true });
});
</script>
@endpush
