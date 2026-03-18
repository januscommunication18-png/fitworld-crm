@extends('layouts.dashboard')

@section('title', 'Edit Event')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('events.index') }}"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Events</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Edit Event</li>
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
</style>
@endpush

@section('content')
<div class="max-w-full">
    <form method="POST" action="{{ route('events.update', $event) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

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
                                <input type="text" id="title" name="title" value="{{ old('title', $event->title) }}"
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
                                    <input type="text" id="start_date" name="start_date" value="{{ old('start_date', $event->start_datetime->format('Y-m-d')) }}"
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
                                    <input type="text" id="start_time" name="start_time" value="{{ old('start_time', $event->start_datetime->format('H:i')) }}"
                                           class="input input-bordered w-full @error('start_time') input-error @enderror"
                                           placeholder="Select time" required readonly>
                                    @error('start_time')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- End Date & Time --}}
                            <div class="grid sm:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" for="end_date">
                                        End Date <span class="text-error">*</span>
                                    </label>
                                    <input type="text" id="end_date" name="end_date" value="{{ old('end_date', $event->end_datetime->format('Y-m-d')) }}"
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
                                    <input type="text" id="end_time" name="end_time" value="{{ old('end_time', $event->end_datetime->format('H:i')) }}"
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
                                               {{ old('event_type', $event->event_type) === 'in_person' ? 'checked' : '' }} onchange="toggleLocationFields()">
                                        <span class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all peer-checked:bg-base-100 peer-checked:shadow-sm text-base-content/70 peer-checked:text-base-content">
                                            <span class="icon-[tabler--map-pin] size-4"></span>
                                            In-Person
                                        </span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="event_type" value="online" class="hidden peer"
                                               {{ old('event_type', $event->event_type) === 'online' ? 'checked' : '' }} onchange="toggleLocationFields()">
                                        <span class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all peer-checked:bg-base-100 peer-checked:shadow-sm text-base-content/70 peer-checked:text-base-content">
                                            <span class="icon-[tabler--device-laptop] size-4"></span>
                                            Online
                                        </span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="event_type" value="hybrid" class="hidden peer"
                                               {{ old('event_type', $event->event_type) === 'hybrid' ? 'checked' : '' }} onchange="toggleLocationFields()">
                                        <span class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all peer-checked:bg-base-100 peer-checked:shadow-sm text-base-content/70 peer-checked:text-base-content">
                                            <span class="icon-[tabler--arrows-exchange] size-4"></span>
                                            Hybrid
                                        </span>
                                    </label>
                                </div>
                            </div>

                            {{-- Event Visibility --}}
                            <div>
                                <label class="block text-sm font-medium mb-3">Visibility <span class="text-error">*</span></label>
                                <div class="space-y-3">
                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-base-300 hover:border-primary/50 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                        <input type="radio" name="visibility" value="private" class="radio radio-primary radio-sm mt-0.5"
                                               {{ old('visibility', $event->visibility) === 'private' ? 'checked' : '' }}>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="icon-[tabler--lock] size-4 text-primary"></span>
                                                <span class="font-medium text-sm">Members Only</span>
                                            </div>
                                        </div>
                                    </label>
                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-base-300 hover:border-primary/50 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                        <input type="radio" name="visibility" value="unlisted" class="radio radio-primary radio-sm mt-0.5"
                                               {{ old('visibility', $event->visibility) === 'unlisted' ? 'checked' : '' }}>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="icon-[tabler--link] size-4 text-warning"></span>
                                                <span class="font-medium text-sm">Unlisted</span>
                                            </div>
                                        </div>
                                    </label>
                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-base-300 hover:border-primary/50 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                        <input type="radio" name="visibility" value="public" class="radio radio-primary radio-sm mt-0.5"
                                               {{ old('visibility', $event->visibility) === 'public' ? 'checked' : '' }}>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="icon-[tabler--world] size-4 text-success"></span>
                                                <span class="font-medium text-sm">Public</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Advanced Settings --}}
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
                                    <label class="block text-sm font-medium mb-2" for="short_description">Short Description</label>
                                    <input type="text" id="short_description" name="short_description" value="{{ old('short_description', $event->short_description) }}"
                                           class="input input-bordered w-full" maxlength="500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" for="description">Full Description</label>
                                    <textarea id="description" name="description" rows="4" class="textarea textarea-bordered w-full">{{ old('description', $event->description) }}</textarea>
                                </div>
                            </div>

                            <div class="divider my-2"></div>

                            {{-- Timezone --}}
                            <div>
                                <label class="block text-sm font-medium mb-2" for="timezone">Timezone</label>
                                <select id="timezone" name="timezone" class="select select-bordered w-full" required>
                                    @foreach($timezones as $tz)
                                        <option value="{{ $tz }}" {{ old('timezone', $event->timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="divider my-2"></div>

                            {{-- Location Details --}}
                            <div>
                                <h3 class="font-medium text-base-content/80 mb-3">Location Details</h3>

                                <div id="physical-location-fields" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="venue_name">Venue Name</label>
                                        <input type="text" id="venue_name" name="venue_name" value="{{ old('venue_name', $event->venue_name) }}" class="input input-bordered w-full">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="address_line_1">Street Address</label>
                                        <input type="text" id="address_line_1" name="address_line_1" value="{{ old('address_line_1', $event->address_line_1) }}" class="input input-bordered w-full">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="address_line_2">Address Line 2</label>
                                        <input type="text" id="address_line_2" name="address_line_2" value="{{ old('address_line_2', $event->address_line_2) }}" class="input input-bordered w-full">
                                    </div>
                                    <div class="grid grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-2" for="city">City</label>
                                            <input type="text" id="city" name="city" value="{{ old('city', $event->city) }}" class="input input-bordered w-full">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-2" for="state">State</label>
                                            <input type="text" id="state" name="state" value="{{ old('state', $event->state) }}" class="input input-bordered w-full" maxlength="50">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-2" for="zip_code">ZIP Code</label>
                                            <input type="text" id="zip_code" name="zip_code" value="{{ old('zip_code', $event->zip_code) }}" class="input input-bordered w-full">
                                        </div>
                                    </div>
                                </div>

                                <div id="online-location-fields" class="space-y-4 hidden">
                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="online_platform">Platform</label>
                                        <select id="online_platform" name="online_platform" class="select select-bordered w-full">
                                            <option value="">Select platform</option>
                                            <option value="zoom" {{ old('online_platform', $event->online_platform) === 'zoom' ? 'selected' : '' }}>Zoom</option>
                                            <option value="google_meet" {{ old('online_platform', $event->online_platform) === 'google_meet' ? 'selected' : '' }}>Google Meet</option>
                                            <option value="teams" {{ old('online_platform', $event->online_platform) === 'teams' ? 'selected' : '' }}>Microsoft Teams</option>
                                            <option value="other" {{ old('online_platform', $event->online_platform) === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="online_url">Event URL</label>
                                        <input type="url" id="online_url" name="online_url" value="{{ old('online_url', $event->online_url) }}" class="input input-bordered w-full" placeholder="https://...">
                                    </div>
                                </div>
                            </div>

                            <div class="divider my-2"></div>

                            {{-- Capacity --}}
                            <div>
                                <h3 class="font-medium text-base-content/80 mb-3">Capacity & Audience</h3>
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="capacity">Event Capacity</label>
                                        <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $event->capacity) }}" class="input input-bordered w-full" min="1" placeholder="Unlimited">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="skill_level">Skill Level</label>
                                        <select id="skill_level" name="skill_level" class="select select-bordered w-full">
                                            <option value="all_levels" {{ old('skill_level', $event->skill_level) === 'all_levels' ? 'selected' : '' }}>All Levels</option>
                                            <option value="beginner" {{ old('skill_level', $event->skill_level) === 'beginner' ? 'selected' : '' }}>Beginner</option>
                                            <option value="intermediate" {{ old('skill_level', $event->skill_level) === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                            <option value="advanced" {{ old('skill_level', $event->skill_level) === 'advanced' ? 'selected' : '' }}>Advanced</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-2" for="audience_type">Audience Type</label>
                                        <select id="audience_type" name="audience_type" class="select select-bordered w-full">
                                            <option value="all" {{ old('audience_type', $event->audience_type) === 'all' ? 'selected' : '' }}>All Ages</option>
                                            <option value="adults" {{ old('audience_type', $event->audience_type) === 'adults' ? 'selected' : '' }}>Adults (18+)</option>
                                            <option value="kids" {{ old('audience_type', $event->audience_type) === 'kids' ? 'selected' : '' }}>Kids</option>
                                            <option value="families" {{ old('audience_type', $event->audience_type) === 'families' ? 'selected' : '' }}>Families</option>
                                            <option value="seniors" {{ old('audience_type', $event->audience_type) === 'seniors' ? 'selected' : '' }}>Seniors (60+)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="divider my-2"></div>

                            {{-- Options --}}
                            <div>
                                <h3 class="font-medium text-base-content/80 mb-3">Additional Options</h3>
                                <div class="space-y-3">
                                    <label class="flex items-center justify-between p-3 rounded-xl hover:bg-base-200/50 transition-colors cursor-pointer" for="waitlist_enabled">
                                        <div>
                                            <span class="font-medium text-sm">Enable waitlist</span>
                                            <p class="text-xs text-base-content/60">Allow clients to join a waitlist when event is full</p>
                                        </div>
                                        <input type="checkbox" name="waitlist_enabled" value="1" id="waitlist_enabled" class="toggle toggle-primary"
                                               {{ old('waitlist_enabled', $event->waitlist_enabled) ? 'checked' : '' }}>
                                    </label>
                                    <label class="flex items-center justify-between p-3 rounded-xl hover:bg-base-200/50 transition-colors cursor-pointer" for="hide_attendee_list">
                                        <div>
                                            <span class="font-medium text-sm">Hide attendee list</span>
                                            <p class="text-xs text-base-content/60">Don't show attendee list publicly</p>
                                        </div>
                                        <input type="checkbox" name="hide_attendee_list" value="1" id="hide_attendee_list" class="toggle toggle-primary"
                                               {{ old('hide_attendee_list', $event->hide_attendee_list) ? 'checked' : '' }}>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn btn-primary px-8">
                        <span class="icon-[tabler--device-floppy] size-5"></span>
                        Save Changes
                    </button>
                    <a href="{{ route('events.show', $event) }}" class="btn btn-ghost">Cancel</a>
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
                            <div id="upload-placeholder" class="text-center {{ $event->cover_image ? 'hidden' : '' }}">
                                <span class="icon-[tabler--photo] size-12 text-base-content/30 block mx-auto mb-2"></span>
                                <p class="text-sm text-base-content/60">No cover image</p>
                            </div>
                            {{-- Preview image --}}
                            <img id="image-preview" src="{{ $event->cover_image }}" alt="Preview" class="absolute inset-0 w-full h-full object-cover {{ $event->cover_image ? '' : 'hidden' }}" />
                            {{-- Hover overlay for changing image --}}
                            <div id="image-overlay" class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition-opacity {{ $event->cover_image ? 'flex' : 'hidden' }} items-center justify-center gap-2">
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
                        <div id="upload-btn-container" class="mt-3 {{ $event->cover_image ? 'hidden' : '' }}">
                            <button type="button" onclick="document.getElementById('cover_image_input').click()" class="btn btn-soft btn-sm w-full">
                                <span class="icon-[tabler--upload] size-4"></span> Upload Cover Image
                            </button>
                        </div>

                        <p class="text-xs text-base-content/50 mt-2 text-center">PNG, JPG, or WebP. Max 5MB.</p>

                        @error('cover_image')
                            <p class="text-error text-sm mt-2">{{ $message }}</p>
                        @enderror
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

        flatpickr('#start_date', { dateFormat: 'Y-m-d', altInput: true, altFormat: 'F j, Y' });
        flatpickr('#end_date', { dateFormat: 'Y-m-d', altInput: true, altFormat: 'F j, Y' });
        flatpickr('#start_time', { enableTime: true, noCalendar: true, dateFormat: 'H:i', altInput: true, altFormat: 'h:i K', minuteIncrement: 15 });
        flatpickr('#end_time', { enableTime: true, noCalendar: true, dateFormat: 'H:i', altInput: true, altFormat: 'h:i K', minuteIncrement: 15 });
    });
</script>
@endpush
