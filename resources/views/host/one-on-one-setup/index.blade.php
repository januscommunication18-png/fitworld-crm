@extends('layouts.dashboard')

@section('title', '1:1 Booking Setup')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">1:1 Booking Setup</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">1:1 Booking Setup</h1>
            <p class="text-base-content/60 mt-1">Configure your availability and booking preferences for 1:1 meetings.</p>
        </div>
        @if($profile->is_setup_complete)
        <a href="{{ $profile->getPublicUrl() }}" target="_blank" class="btn btn-primary btn-soft">
            <span class="icon-[tabler--external-link] size-5"></span>
            View Public Page
        </a>
        @endif
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-soft alert-success">
        <span class="icon-[tabler--check] size-5"></span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-soft alert-error">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <form id="setup-form" method="POST" action="{{ route('one-on-one-setup.update') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Form --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Profile Information --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">Profile Information</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="label-text" for="display_name">Display Name</label>
                            <input type="text" id="display_name" name="display_name"
                                value="{{ old('display_name', $profile->display_name ?? $instructor->name) }}"
                                class="input w-full @error('display_name') input-error @enderror"
                                placeholder="e.g., John Smith">
                            <p class="text-xs text-base-content/60 mt-1">Leave empty to use your instructor name</p>
                            @error('display_name')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label-text" for="title">Title</label>
                            <input type="text" id="title" name="title"
                                value="{{ old('title', $profile->title) }}"
                                class="input w-full @error('title') input-error @enderror"
                                placeholder="e.g., Personal Trainer, Yoga Instructor">
                            @error('title')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label-text" for="bio">Bio</label>
                            <textarea id="bio" name="bio" rows="3"
                                class="textarea w-full @error('bio') input-error @enderror"
                                placeholder="Tell clients about yourself and what they can expect from a meeting...">{{ old('bio', $profile->bio ?? $instructor->bio) }}</textarea>
                            <p class="text-xs text-base-content/60 mt-1">Leave empty to use your instructor bio</p>
                            @error('bio')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Meeting Types --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">Meeting Types</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <p class="text-sm text-base-content/60">Select the types of meetings you offer and provide the necessary details.</p>

                        @php
                            $selectedTypes = old('meeting_types', $profile->meeting_types ?? []);
                        @endphp

                        {{-- In-Person --}}
                        <div class="border border-base-content/10 rounded-lg p-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="meeting_types[]" value="in_person"
                                    class="checkbox checkbox-primary"
                                    {{ in_array('in_person', $selectedTypes) ? 'checked' : '' }}
                                    onchange="toggleMeetingTypeField('in_person', this.checked)">
                                <div class="flex items-center gap-2">
                                    <span class="icon-[tabler--map-pin] size-5 text-primary"></span>
                                    <span class="font-medium">In-Person</span>
                                </div>
                            </label>
                            <div id="in_person_fields" class="mt-3 {{ in_array('in_person', $selectedTypes) ? '' : 'hidden' }}">
                                <label class="label-text" for="in_person_location">Location Address</label>
                                <textarea id="in_person_location" name="in_person_location" rows="2"
                                    class="textarea w-full @error('in_person_location') input-error @enderror"
                                    placeholder="Enter the address where meetings will take place">{{ old('in_person_location', $profile->in_person_location) }}</textarea>
                                @error('in_person_location')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Phone --}}
                        <div class="border border-base-content/10 rounded-lg p-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="meeting_types[]" value="phone"
                                    class="checkbox checkbox-primary"
                                    {{ in_array('phone', $selectedTypes) ? 'checked' : '' }}
                                    onchange="toggleMeetingTypeField('phone', this.checked)">
                                <div class="flex items-center gap-2">
                                    <span class="icon-[tabler--phone] size-5 text-primary"></span>
                                    <span class="font-medium">Phone Call</span>
                                </div>
                            </label>
                            <div id="phone_fields" class="mt-3 {{ in_array('phone', $selectedTypes) ? '' : 'hidden' }}">
                                <label class="label-text" for="phone_number">Phone Number</label>
                                <input type="tel" id="phone_number" name="phone_number"
                                    value="{{ old('phone_number', $profile->phone_number) }}"
                                    class="input w-full @error('phone_number') input-error @enderror"
                                    placeholder="+1 (555) 123-4567">
                                @error('phone_number')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Video --}}
                        <div class="border border-base-content/10 rounded-lg p-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="meeting_types[]" value="video"
                                    class="checkbox checkbox-primary"
                                    {{ in_array('video', $selectedTypes) ? 'checked' : '' }}
                                    onchange="toggleMeetingTypeField('video', this.checked)">
                                <div class="flex items-center gap-2">
                                    <span class="icon-[tabler--video] size-5 text-primary"></span>
                                    <span class="font-medium">Video Call</span>
                                </div>
                            </label>
                            <div id="video_fields" class="mt-3 {{ in_array('video', $selectedTypes) ? '' : 'hidden' }}">
                                <label class="label-text" for="video_link">Video Meeting Link</label>
                                <input type="url" id="video_link" name="video_link"
                                    value="{{ old('video_link', $profile->video_link) }}"
                                    class="input w-full @error('video_link') input-error @enderror"
                                    placeholder="https://zoom.us/j/...">
                                <p class="text-xs text-base-content/60 mt-1">Zoom, Google Meet, or other video call link</p>
                                @error('video_link')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        @error('meeting_types')
                            <p class="text-error text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Duration Options --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">Duration Options</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="label-text mb-2 block">Allowed Durations</label>
                            <p class="text-sm text-base-content/60 mb-3">Select which meeting durations you want to offer.</p>

                            @php
                                $selectedDurations = old('allowed_durations', $profile->allowed_durations ?? [30, 60]);
                            @endphp

                            <div class="flex flex-wrap gap-3">
                                @foreach($durationOptions as $minutes => $label)
                                <label class="flex items-center gap-2 px-4 py-2 border border-base-content/10 rounded-lg cursor-pointer hover:bg-base-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                    <input type="checkbox" name="allowed_durations[]" value="{{ $minutes }}"
                                        class="checkbox checkbox-primary checkbox-sm"
                                        {{ in_array($minutes, $selectedDurations) ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('allowed_durations')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label-text" for="default_duration">Default Duration</label>
                            <select id="default_duration" name="default_duration" class="select w-full @error('default_duration') input-error @enderror">
                                @foreach($durationOptions as $minutes => $label)
                                    <option value="{{ $minutes }}" {{ old('default_duration', $profile->default_duration ?? 30) == $minutes ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-base-content/60 mt-1">Pre-selected duration when clients book</p>
                            @error('default_duration')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Availability --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">Availability</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="label-text mb-2 block">Working Days</label>
                            <p class="text-sm text-base-content/60 mb-3">Select the days you're available for meetings.</p>

                            @php
                                $selectedDays = old('working_days', $profile->working_days ?? [1, 2, 3, 4, 5]);
                            @endphp

                            <div class="flex flex-wrap gap-2">
                                @foreach($dayOptions as $dayNum => $dayName)
                                <label class="flex items-center justify-center w-12 h-12 border border-base-content/10 rounded-lg cursor-pointer hover:bg-base-200 has-[:checked]:border-primary has-[:checked]:bg-primary has-[:checked]:text-primary-content">
                                    <input type="checkbox" name="working_days[]" value="{{ $dayNum }}"
                                        class="hidden"
                                        {{ in_array($dayNum, $selectedDays) ? 'checked' : '' }}>
                                    <span class="font-medium">{{ substr($dayName, 0, 2) }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('working_days')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label-text" for="default_start_time">Start Time</label>
                                <input type="time" id="default_start_time" name="default_start_time"
                                    value="{{ old('default_start_time', $profile->default_start_time ? substr($profile->default_start_time, 0, 5) : '09:00') }}"
                                    class="input w-full @error('default_start_time') input-error @enderror">
                                @error('default_start_time')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="label-text" for="default_end_time">End Time</label>
                                <input type="time" id="default_end_time" name="default_end_time"
                                    value="{{ old('default_end_time', $profile->default_end_time ? substr($profile->default_end_time, 0, 5) : '17:00') }}"
                                    class="input w-full @error('default_end_time') input-error @enderror">
                                @error('default_end_time')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Buffer & Limits (Studio Settings - Read Only) --}}
                <div class="card bg-base-100 border-base-300">
                    <div class="card-header">
                        <h3 class="card-title flex items-center gap-2">
                            Buffer & Limits
                            <span class="badge badge-ghost badge-sm">Studio Settings</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-soft alert-info mb-4">
                            <span class="icon-[tabler--info-circle] size-5"></span>
                            <span class="text-sm">These settings are managed by your studio owner and apply to all 1:1 bookings.</span>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-base-200/50 rounded-lg p-3">
                                <p class="text-xs text-base-content/60 mb-1">Buffer Before</p>
                                <p class="font-semibold">{{ $studioSettings['buffer_before'] }} min</p>
                            </div>
                            <div class="bg-base-200/50 rounded-lg p-3">
                                <p class="text-xs text-base-content/60 mb-1">Buffer After</p>
                                <p class="font-semibold">{{ $studioSettings['buffer_after'] }} min</p>
                            </div>
                            <div class="bg-base-200/50 rounded-lg p-3">
                                <p class="text-xs text-base-content/60 mb-1">Minimum Notice</p>
                                <p class="font-semibold">{{ $studioSettings['min_notice_hours'] }} hours</p>
                            </div>
                            <div class="bg-base-200/50 rounded-lg p-3">
                                <p class="text-xs text-base-content/60 mb-1">Max Advance</p>
                                <p class="font-semibold">{{ $studioSettings['max_advance_days'] }} days</p>
                            </div>
                        </div>

                        {{-- Daily Max Meetings - Instructor can set this --}}
                        <div class="mt-4 pt-4 border-t border-base-content/10">
                            <div class="max-w-xs">
                                <label class="label-text" for="daily_max_meetings">Daily Max Meetings</label>
                                <input type="number" id="daily_max_meetings" name="daily_max_meetings"
                                    value="{{ old('daily_max_meetings', $profile->daily_max_meetings) }}"
                                    class="input w-full @error('daily_max_meetings') input-error @enderror"
                                    min="1" max="20" placeholder="No limit">
                                <p class="text-xs text-base-content/60 mt-1">Leave empty for unlimited (you can customize this)</p>
                                @error('daily_max_meetings')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Reschedule & Cancellation Policy (Studio Settings - Read Only) --}}
                <div class="card bg-base-100 border-base-300">
                    <div class="card-header">
                        <h3 class="card-title flex items-center gap-2">
                            Reschedule & Cancellation Policy
                            <span class="badge badge-ghost badge-sm">Studio Settings</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-soft alert-info mb-4">
                            <span class="icon-[tabler--info-circle] size-5"></span>
                            <span class="text-sm">These policies are set by your studio owner and apply to all 1:1 bookings.</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Reschedule Policy --}}
                            <div class="bg-base-200/50 rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="icon-[tabler--calendar-repeat] size-5 text-primary"></span>
                                    <span class="font-medium">Rescheduling</span>
                                </div>
                                @if($studioSettings['allow_reschedule'])
                                <p class="text-sm text-success flex items-center gap-1">
                                    <span class="icon-[tabler--check] size-4"></span>
                                    Allowed
                                </p>
                                <p class="text-xs text-base-content/60 mt-1">
                                    Clients can reschedule up to {{ $studioSettings['reschedule_cutoff_hours'] }} hours before the meeting
                                </p>
                                @else
                                <p class="text-sm text-error flex items-center gap-1">
                                    <span class="icon-[tabler--x] size-4"></span>
                                    Not Allowed
                                </p>
                                <p class="text-xs text-base-content/60 mt-1">Clients cannot reschedule their meetings</p>
                                @endif
                            </div>

                            {{-- Cancellation Policy --}}
                            <div class="bg-base-200/50 rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="icon-[tabler--calendar-off] size-5 text-primary"></span>
                                    <span class="font-medium">Cancellation</span>
                                </div>
                                @if($studioSettings['allow_cancel'])
                                <p class="text-sm text-success flex items-center gap-1">
                                    <span class="icon-[tabler--check] size-4"></span>
                                    Allowed
                                </p>
                                <p class="text-xs text-base-content/60 mt-1">
                                    Clients can cancel up to {{ $studioSettings['cancel_cutoff_hours'] }} hours before the meeting
                                </p>
                                @else
                                <p class="text-sm text-error flex items-center gap-1">
                                    <span class="icon-[tabler--x] size-4"></span>
                                    Not Allowed
                                </p>
                                <p class="text-xs text-base-content/60 mt-1">Clients cannot cancel their meetings</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Public URL --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title flex items-center gap-2">
                            <span class="icon-[tabler--link] size-5"></span>
                            Your Booking Link
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($profile->is_setup_complete)
                        <div class="bg-base-200 rounded-lg p-3">
                            <p class="text-sm font-mono break-all">{{ $profile->getPublicUrl() }}</p>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button type="button" onclick="copyToClipboard('{{ $profile->getPublicUrl() }}')" class="btn btn-primary btn-sm flex-1">
                                <span class="icon-[tabler--copy] size-4"></span>
                                Copy Link
                            </button>
                            <a href="{{ $profile->getPublicUrl() }}" target="_blank" class="btn btn-ghost btn-sm">
                                <span class="icon-[tabler--external-link] size-4"></span>
                            </a>
                        </div>
                        @else
                        <div class="alert alert-soft alert-info">
                            <span class="icon-[tabler--info-circle] size-5"></span>
                            <span>Complete your setup to get your public booking link.</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Status --}}
                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">Status</h3>
                    </div>
                    <div class="card-body">
                        @if($profile->is_setup_complete)
                        <div class="flex items-center gap-2 text-success">
                            <span class="icon-[tabler--circle-check] size-5"></span>
                            <span class="font-medium">Setup Complete</span>
                        </div>
                        <p class="text-sm text-base-content/60 mt-1">
                            Completed {{ $profile->setup_completed_at?->diffForHumans() }}
                        </p>
                        @else
                        <div class="flex items-center gap-2 text-warning">
                            <span class="icon-[tabler--alert-circle] size-5"></span>
                            <span class="font-medium">Setup Incomplete</span>
                        </div>
                        <p class="text-sm text-base-content/60 mt-1">
                            Save your settings to activate your booking page.
                        </p>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card bg-base-100">
                    <div class="card-body space-y-2">
                        <button type="submit" class="btn btn-primary w-full" id="save-btn">
                            <span class="loading loading-spinner loading-sm hidden"></span>
                            <span class="icon-[tabler--check] size-5"></span>
                            Save Settings
                        </button>
                    </div>
                </div>

                {{-- Help --}}
                <div class="card bg-primary/5 border border-primary/20">
                    <div class="card-body">
                        <h4 class="font-semibold flex items-center gap-2">
                            <span class="icon-[tabler--help-circle] size-5 text-primary"></span>
                            How It Works
                        </h4>
                        <ul class="text-sm text-base-content/70 space-y-2 mt-3">
                            <li class="flex items-start gap-2">
                                <span class="icon-[tabler--circle-filled] size-2 mt-1.5 text-primary"></span>
                                <span>Configure your availability and meeting preferences</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="icon-[tabler--circle-filled] size-2 mt-1.5 text-primary"></span>
                                <span>Share your booking link with clients</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="icon-[tabler--circle-filled] size-2 mt-1.5 text-primary"></span>
                                <span>Clients book directly from your instructor page</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="icon-[tabler--circle-filled] size-2 mt-1.5 text-primary"></span>
                                <span>Receive email notifications for new bookings</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function toggleMeetingTypeField(type, isChecked) {
    const fields = document.getElementById(type + '_fields');
    if (fields) {
        fields.classList.toggle('hidden', !isChecked);
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        if (typeof Notyf !== 'undefined') {
            new Notyf().success('Link copied to clipboard!');
        }
    });
}

// Form submission with loading state
document.getElementById('setup-form').addEventListener('submit', function(e) {
    const btn = document.getElementById('save-btn');
    const spinner = btn.querySelector('.loading');
    const icon = btn.querySelector('.icon-\\[tabler--check\\]');

    btn.disabled = true;
    spinner.classList.remove('hidden');
    icon.classList.add('hidden');
});
</script>
@endpush
@endsection
