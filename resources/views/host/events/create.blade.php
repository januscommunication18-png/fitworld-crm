@extends('layouts.dashboard')

@section('title', 'Create Event')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('events.index') }}"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Events</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Create Event</li>
    </ol>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
<style>
    .flatpickr-calendar {
        font-family: inherit;
        border-radius: 0.75rem;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        border: 1px solid var(--fallback-bc,oklch(var(--bc)/0.2));
    }
    .flatpickr-day.selected,
    .flatpickr-day.selected:hover {
        background: oklch(var(--p)) !important;
        border-color: oklch(var(--p)) !important;
    }
    .flatpickr-day:hover {
        background: oklch(var(--p)/0.1) !important;
        border-color: oklch(var(--p)/0.1) !important;
    }
    .flatpickr-day.today {
        border-color: oklch(var(--p)) !important;
    }
    .flatpickr-months .flatpickr-month,
    .flatpickr-current-month .flatpickr-monthDropdown-months,
    .flatpickr-weekdays,
    span.flatpickr-weekday {
        background: oklch(var(--b1));
    }
    .flatpickr-time input {
        font-size: 1rem !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-full">
    <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid lg:grid-cols-2 gap-8">
            {{-- Left Column - Form --}}
            <div class="space-y-6">
                {{-- Essential Details --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="text-lg font-semibold mb-5">Essential Details</h2>

                        <div class="space-y-5">
                            {{-- Title --}}
                            <div>
                                <label class="block text-sm font-medium mb-2" for="title">
                                    Event Title <span class="text-error">*</span>
                                </label>
                                <input type="text" id="title" name="title" value="{{ old('title') }}"
                                       class="input input-bordered w-full @error('title') input-error @enderror"
                                       placeholder="e.g., Morning Yoga Flow" required>
                                @error('title')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Start Date & Time --}}
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" for="start_date">
                                        Start Date <span class="text-error">*</span>
                                    </label>
                                    <input type="text" id="start_date" name="start_date" value="{{ old('start_date') }}"
                                           class="input input-bordered w-full @error('start_date') input-error @enderror"
                                           placeholder="Select date" required readonly>
                                    @error('start_date')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" for="start_time">
                                        Start Time <span class="text-error">*</span>
                                    </label>
                                    <input type="text" id="start_time" name="start_time" value="{{ old('start_time') }}"
                                           class="input input-bordered w-full @error('start_time') input-error @enderror"
                                           placeholder="Select time" required readonly>
                                    @error('start_time')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Same Day Event Toggle --}}
                            <div class="flex items-center gap-3 mt-4">
                                <input type="checkbox" id="same_day_event" class="checkbox checkbox-primary checkbox-sm"
                                       {{ old('same_day_event', true) ? 'checked' : '' }}>
                                <label for="same_day_event" class="text-sm font-medium cursor-pointer">
                                    Event ends on the same day
                                </label>
                            </div>

                            {{-- End Date & Time --}}
                            <div class="grid sm:grid-cols-2 gap-4 mt-4">
                                <div id="end_date_wrapper">
                                    <label class="block text-sm font-medium mb-2" for="end_date">
                                        End Date <span class="text-error">*</span>
                                    </label>
                                    <input type="text" id="end_date" name="end_date" value="{{ old('end_date') }}"
                                           class="input input-bordered w-full @error('end_date') input-error @enderror"
                                           placeholder="Select date" readonly>
                                    @error('end_date')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" for="end_time">
                                        End Time <span class="text-error">*</span>
                                    </label>
                                    <input type="text" id="end_time" name="end_time" value="{{ old('end_time') }}"
                                           class="input input-bordered w-full @error('end_time') input-error @enderror"
                                           placeholder="Select time" required readonly>
                                    @error('end_time')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Event Type Pills --}}
                            <div>
                                <label class="block text-sm font-medium mb-3">Event Type <span class="text-error">*</span></label>
                                <div class="inline-flex p-1 bg-base-200 rounded-xl gap-1">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="event_type" value="in_person" class="hidden peer"
                                               {{ old('event_type', 'in_person') === 'in_person' ? 'checked' : '' }} onchange="toggleLocationFields()">
                                        <span class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all peer-checked:bg-base-100 peer-checked:shadow-sm text-base-content/70 peer-checked:text-base-content">
                                            <span class="icon-[tabler--map-pin] size-4"></span>
                                            In-Person
                                        </span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="event_type" value="online" class="hidden peer"
                                               {{ old('event_type') === 'online' ? 'checked' : '' }} onchange="toggleLocationFields()">
                                        <span class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all peer-checked:bg-base-100 peer-checked:shadow-sm text-base-content/70 peer-checked:text-base-content">
                                            <span class="icon-[tabler--device-laptop] size-4"></span>
                                            Online
                                        </span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="event_type" value="hybrid" class="hidden peer"
                                               {{ old('event_type') === 'hybrid' ? 'checked' : '' }} onchange="toggleLocationFields()">
                                        <span class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all peer-checked:bg-base-100 peer-checked:shadow-sm text-base-content/70 peer-checked:text-base-content">
                                            <span class="icon-[tabler--arrows-exchange] size-4"></span>
                                            Hybrid
                                        </span>
                                    </label>
                                </div>
                            </div>

                            {{-- Event Visibility --}}
                            <div>
                                <label class="block text-sm font-medium mb-3">Who can see this event? <span class="text-error">*</span></label>
                                <div class="space-y-3">
                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-base-300 hover:border-primary/50 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                        <input type="radio" name="visibility" value="private" class="radio radio-primary radio-sm mt-0.5"
                                               {{ old('visibility', 'private') === 'private' ? 'checked' : '' }}>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="icon-[tabler--lock] size-4 text-primary"></span>
                                                <span class="font-medium text-sm">Members Only</span>
                                            </div>
                                            <p class="text-xs text-base-content/60 mt-0.5">Only your studio clients can view and register</p>
                                        </div>
                                    </label>
                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-base-300 hover:border-primary/50 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                        <input type="radio" name="visibility" value="unlisted" class="radio radio-primary radio-sm mt-0.5"
                                               {{ old('visibility') === 'unlisted' ? 'checked' : '' }}>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="icon-[tabler--link] size-4 text-warning"></span>
                                                <span class="font-medium text-sm">Unlisted</span>
                                            </div>
                                            <p class="text-xs text-base-content/60 mt-0.5">Only people with the link can view this event</p>
                                        </div>
                                    </label>
                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-base-300 hover:border-primary/50 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                        <input type="radio" name="visibility" value="public" class="radio radio-primary radio-sm mt-0.5"
                                               {{ old('visibility') === 'public' ? 'checked' : '' }}>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="icon-[tabler--world] size-4 text-success"></span>
                                                <span class="font-medium text-sm">Public</span>
                                            </div>
                                            <p class="text-xs text-base-content/60 mt-0.5">Your event will be visible to everyone</p>
                                        </div>
                                    </label>
                                </div>
                                @error('visibility')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Advanced Settings (Collapsible) --}}
                <div class="card bg-base-100 overflow-hidden">
                    <button type="button" onclick="toggleAdvancedSettings()" class="w-full p-6 flex items-center justify-between hover:bg-base-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--settings] size-5 text-base-content/60"></span>
                            <span class="text-lg font-semibold">Advanced Settings</span>
                        </div>
                        <span id="advanced-toggle-icon" class="icon-[tabler--chevron-down] size-5 text-base-content/60 transition-transform duration-200"></span>
                    </button>

                    <div id="advanced-settings-content" class="hidden border-t border-base-200">
                        <div class="p-6 space-y-6">
                            {{-- Description --}}
                            <div class="space-y-4">
                                <h3 class="font-medium text-base-content/80">Description</h3>
                                <div>
                                    <label class="block text-sm font-medium mb-2" for="short_description">
                                        Short Description
                                    </label>
                                    <input type="text" id="short_description" name="short_description" value="{{ old('short_description') }}"
                                           class="input input-bordered w-full @error('short_description') input-error @enderror"
                                           placeholder="A brief tagline for your event" maxlength="500">
                                    @error('short_description')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2" for="description">
                                        Full Description
                                    </label>
                                    <textarea id="description" name="description" rows="4"
                                              class="textarea textarea-bordered w-full @error('description') textarea-error @enderror"
                                              placeholder="Describe your event in detail...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="divider my-2"></div>

                            {{-- Timezone --}}
                            <div>
                                <h3 class="font-medium text-base-content/80 mb-3">Timezone</h3>
                                <select id="timezone" name="timezone" class="select select-bordered w-full @error('timezone') select-error @enderror" required>
                                    @foreach($timezones as $tz)
                                        <option value="{{ $tz }}" {{ old('timezone', $host->timezone ?? 'America/New_York') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                                @error('timezone')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="divider my-2"></div>

                            {{-- Location Details --}}
                            <div>
                                <h3 class="font-medium text-base-content/80 mb-3">Location Details</h3>

                                {{-- Physical Location Fields --}}
                                <div id="physical-location-fields" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="venue_name">Venue Name</label>
                                        <input type="text" id="venue_name" name="venue_name" value="{{ old('venue_name', $host->studio_name ?? '') }}"
                                               class="input input-bordered w-full @error('venue_name') input-error @enderror"
                                               placeholder="e.g., Central Park Lawn">
                                        @error('venue_name')
                                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="address_line_1">Street Address</label>
                                        <input type="text" id="address_line_1" name="address_line_1" value="{{ old('address_line_1') }}"
                                               class="input input-bordered w-full @error('address_line_1') input-error @enderror"
                                               placeholder="Street address">
                                        @error('address_line_1')
                                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="address_line_2">Address Line 2</label>
                                        <input type="text" id="address_line_2" name="address_line_2" value="{{ old('address_line_2') }}"
                                               class="input input-bordered w-full" placeholder="Suite, floor, etc.">
                                    </div>

                                    <div class="grid grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-2" for="city">City</label>
                                            <input type="text" id="city" name="city" value="{{ old('city') }}"
                                                   class="input input-bordered w-full @error('city') input-error @enderror">
                                            @error('city')
                                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-2" for="state">State</label>
                                            <input type="text" id="state" name="state" value="{{ old('state') }}"
                                                   class="input input-bordered w-full @error('state') input-error @enderror"
                                                   maxlength="50" placeholder="NY">
                                            @error('state')
                                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-2" for="zip_code">ZIP Code</label>
                                            <input type="text" id="zip_code" name="zip_code" value="{{ old('zip_code') }}"
                                                   class="input input-bordered w-full @error('zip_code') input-error @enderror">
                                            @error('zip_code')
                                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Online Fields --}}
                                <div id="online-location-fields" class="space-y-4 hidden">
                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="online_platform">Platform</label>
                                        <select id="online_platform" name="online_platform"
                                                class="select select-bordered w-full @error('online_platform') select-error @enderror">
                                            <option value="">Select platform</option>
                                            <option value="zoom" {{ old('online_platform') === 'zoom' ? 'selected' : '' }}>Zoom</option>
                                            <option value="google_meet" {{ old('online_platform') === 'google_meet' ? 'selected' : '' }}>Google Meet</option>
                                            <option value="teams" {{ old('online_platform') === 'teams' ? 'selected' : '' }}>Microsoft Teams</option>
                                            <option value="youtube_live" {{ old('online_platform') === 'youtube_live' ? 'selected' : '' }}>YouTube Live</option>
                                            <option value="other" {{ old('online_platform') === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="online_url">Event URL</label>
                                        <input type="url" id="online_url" name="online_url" value="{{ old('online_url') }}"
                                               class="input input-bordered w-full @error('online_url') input-error @enderror"
                                               placeholder="https://...">
                                        <p class="text-xs text-base-content/50 mt-1">This will be shared with registered attendees</p>
                                        @error('online_url')
                                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="divider my-2"></div>

                            {{-- Capacity & Audience --}}
                            <div>
                                <h3 class="font-medium text-base-content/80 mb-3">Capacity & Audience</h3>
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="capacity">Event Capacity</label>
                                        <input type="number" id="capacity" name="capacity" value="{{ old('capacity') }}"
                                               class="input input-bordered w-full @error('capacity') input-error @enderror"
                                               min="1" placeholder="Unlimited">
                                        @error('capacity')
                                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="skill_level">Skill Level</label>
                                        <select id="skill_level" name="skill_level"
                                                class="select select-bordered w-full @error('skill_level') select-error @enderror">
                                            <option value="all_levels" {{ old('skill_level', 'all_levels') === 'all_levels' ? 'selected' : '' }}>All Levels</option>
                                            <option value="beginner" {{ old('skill_level') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                                            <option value="intermediate" {{ old('skill_level') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                            <option value="advanced" {{ old('skill_level') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="audience_type">Audience Type</label>
                                        <select id="audience_type" name="audience_type"
                                                class="select select-bordered w-full @error('audience_type') select-error @enderror">
                                            <option value="all" {{ old('audience_type', 'all') === 'all' ? 'selected' : '' }}>All Ages</option>
                                            <option value="adults" {{ old('audience_type') === 'adults' ? 'selected' : '' }}>Adults (18+)</option>
                                            <option value="kids" {{ old('audience_type') === 'kids' ? 'selected' : '' }}>Kids</option>
                                            <option value="families" {{ old('audience_type') === 'families' ? 'selected' : '' }}>Families</option>
                                            <option value="seniors" {{ old('audience_type') === 'seniors' ? 'selected' : '' }}>Seniors (60+)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="divider my-2"></div>

                            {{-- Additional Options --}}
                            <div>
                                <h3 class="font-medium text-base-content/80 mb-3">Additional Options</h3>
                                <div class="space-y-3">
                                    <label class="flex items-center justify-between p-3 rounded-xl hover:bg-base-200/50 transition-colors cursor-pointer" for="waitlist_enabled">
                                        <div>
                                            <span class="font-medium text-sm">Enable waitlist</span>
                                            <p class="text-xs text-base-content/60">Allow clients to join a waitlist when event is full</p>
                                        </div>
                                        <input type="checkbox" name="waitlist_enabled" value="1" id="waitlist_enabled"
                                               class="toggle toggle-primary"
                                               {{ old('waitlist_enabled') ? 'checked' : '' }}>
                                    </label>

                                    <label class="flex items-center justify-between p-3 rounded-xl hover:bg-base-200/50 transition-colors cursor-pointer" for="hide_attendee_list">
                                        <div>
                                            <span class="font-medium text-sm">Hide attendee list</span>
                                            <p class="text-xs text-base-content/60">Don't show attendee list publicly on the event page</p>
                                        </div>
                                        <input type="checkbox" name="hide_attendee_list" value="1" id="hide_attendee_list"
                                               class="toggle toggle-primary"
                                               {{ old('hide_attendee_list') ? 'checked' : '' }}>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn btn-primary px-8">
                        <span class="icon-[tabler--calendar-plus] size-5"></span>
                        Create Event
                    </button>
                    <a href="{{ route('events.index') }}" class="btn btn-ghost">Cancel</a>
                </div>
            </div>

            {{-- Right Column - Image Upload --}}
            <div>
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h3 class="font-semibold mb-4">Cover Image</h3>

                        {{-- Preview Area --}}
                        <div id="cover-preview" class="relative w-full h-64 bg-base-200 rounded-xl border-2 border-dashed border-base-content/20 overflow-hidden flex items-center justify-center">
                            {{-- Empty state --}}
                            <div id="upload-placeholder" class="text-center">
                                <span class="icon-[tabler--photo] size-12 text-base-content/30 block mx-auto mb-2"></span>
                                <p class="text-sm text-base-content/60">No cover image</p>
                            </div>
                            {{-- Preview image (hidden by default) --}}
                            <img id="image-preview" src="" alt="Preview" class="absolute inset-0 w-full h-full object-cover hidden" />
                            {{-- Hover overlay for changing image --}}
                            <div id="image-overlay" class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition-opacity hidden items-center justify-center gap-2">
                                <button type="button" onclick="document.getElementById('cover_image_input').click()" class="btn btn-sm btn-ghost text-white">
                                    <span class="icon-[tabler--edit] size-4"></span> Change
                                </button>
                                <button type="button" id="remove-image-btn" class="btn btn-sm btn-ghost text-white">
                                    <span class="icon-[tabler--trash] size-4"></span> Remove
                                </button>
                            </div>
                        </div>

                        {{-- Hidden file input --}}
                        <input type="file" id="cover_image_input" name="cover_image" class="hidden" accept="image/png,image/jpeg,image/webp" />

                        {{-- Upload button --}}
                        <div id="upload-btn-container" class="mt-3">
                            <button type="button" onclick="document.getElementById('cover_image_input').click()" class="btn btn-soft btn-sm w-full">
                                <span class="icon-[tabler--upload] size-4"></span> Upload Cover Image
                            </button>
                        </div>

                        <p class="text-xs text-base-content/50 mt-2 text-center">PNG, JPG, or WebP. Max 5MB. Recommended 600x800px.</p>

                        @error('cover_image')
                            <p class="text-error text-sm mt-2">{{ $message }}</p>
                        @enderror

                        {{-- Quick Tips --}}
                        <div class="mt-6 p-4 bg-base-200/50 rounded-xl">
                            <h4 class="font-medium text-sm mb-2 flex items-center gap-2">
                                <span class="icon-[tabler--bulb] size-4 text-warning"></span>
                                Quick Tips
                            </h4>
                            <ul class="text-xs text-base-content/60 space-y-1">
                                <li>Use a high-quality, eye-catching image</li>
                                <li>Portrait orientation works best</li>
                                <li>Avoid too much text on the image</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script>
    function toggleLocationFields() {
        const eventType = document.querySelector('input[name="event_type"]:checked').value;
        const physicalFields = document.getElementById('physical-location-fields');
        const onlineFields = document.getElementById('online-location-fields');

        if (eventType === 'online') {
            physicalFields.classList.add('hidden');
            onlineFields.classList.remove('hidden');
        } else if (eventType === 'in_person') {
            physicalFields.classList.remove('hidden');
            onlineFields.classList.add('hidden');
        } else {
            // hybrid - show both
            physicalFields.classList.remove('hidden');
            onlineFields.classList.remove('hidden');
        }
    }

    function toggleAdvancedSettings() {
        const content = document.getElementById('advanced-settings-content');
        const icon = document.getElementById('advanced-toggle-icon');

        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            content.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleLocationFields();

        // Cover image upload preview
        const imageInput = document.getElementById('cover_image_input');
        const imagePreview = document.getElementById('image-preview');
        const uploadPlaceholder = document.getElementById('upload-placeholder');
        const uploadBtnContainer = document.getElementById('upload-btn-container');
        const imageOverlay = document.getElementById('image-overlay');
        const removeImageBtn = document.getElementById('remove-image-btn');

        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                        uploadPlaceholder.classList.add('hidden');
                        uploadBtnContainer.classList.add('hidden');
                        imageOverlay.classList.remove('hidden');
                        imageOverlay.classList.add('flex');
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        if (removeImageBtn) {
            removeImageBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                imageInput.value = '';
                imagePreview.src = '';
                imagePreview.classList.add('hidden');
                uploadPlaceholder.classList.remove('hidden');
                uploadBtnContainer.classList.remove('hidden');
                imageOverlay.classList.add('hidden');
                imageOverlay.classList.remove('flex');
            });
        }

        // Initialize Flatpickr date/time pickers
        let endDatePicker;

        const startDatePicker = flatpickr('#start_date', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            minDate: 'today',
            onChange: function(selectedDates) {
                if (selectedDates.length > 0) {
                    const sameDayCheckbox = document.getElementById('same_day_event');
                    if (sameDayCheckbox.checked) {
                        document.getElementById('end_date').value = selectedDates[0].toISOString().split('T')[0];
                        endDatePicker.setDate(selectedDates[0]);
                    }
                    endDatePicker.set('minDate', selectedDates[0]);
                }
            }
        });

        endDatePicker = flatpickr('#end_date', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            minDate: 'today'
        });

        flatpickr('#start_time', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            altInput: true,
            altFormat: 'h:i K',
            minuteIncrement: 15
        });

        flatpickr('#end_time', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            altInput: true,
            altFormat: 'h:i K',
            minuteIncrement: 15
        });

        // Same day event toggle
        const sameDayCheckbox = document.getElementById('same_day_event');
        const endDateWrapper = document.getElementById('end_date_wrapper');

        function toggleEndDate() {
            if (sameDayCheckbox.checked) {
                endDateWrapper.classList.add('hidden');
                const startDate = document.getElementById('start_date').value;
                if (startDate) {
                    document.getElementById('end_date').value = startDate;
                }
            } else {
                endDateWrapper.classList.remove('hidden');
            }
        }

        sameDayCheckbox.addEventListener('change', toggleEndDate);
        toggleEndDate();
    });
</script>
@endpush
