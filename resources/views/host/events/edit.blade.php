@extends('layouts.dashboard')

@section('title', 'Edit Event')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index') }}"><span class="icon-[tabler--layout-grid] size-4"></span> Classes & Services</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'events']) }}">Events</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Edit Event</li>
    </ol>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
<style>
    .flatpickr-calendar { font-family: inherit; border-radius: 0.75rem; box-shadow: 0 10px 40px rgba(0,0,0,0.15); border: 1px solid var(--fallback-bc,oklch(var(--bc)/0.2)); }
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: oklch(var(--p)) !important; border-color: oklch(var(--p)) !important; }
    .flatpickr-day:hover { background: oklch(var(--p)/0.1) !important; border-color: oklch(var(--p)/0.1) !important; }
    .flatpickr-day.today { border-color: oklch(var(--p)) !important; }
    .flatpickr-months .flatpickr-month, .flatpickr-current-month .flatpickr-monthDropdown-months, .flatpickr-weekdays, span.flatpickr-weekday { background: oklch(var(--b1)); }
    .flatpickr-time input { font-size: 1rem !important; }
    .flatpickr-calendar.hasTime.noCalendar { width: auto !important; min-width: 200px; }
    .flatpickr-time { display: flex !important; align-items: center !important; justify-content: center !important; gap: 4px; max-height: none !important; height: auto !important; padding: 10px !important; }
    .flatpickr-time .numInputWrapper { width: 50px !important; height: 40px !important; }
    .flatpickr-time .numInputWrapper input { font-size: 1.25rem !important; }
    .flatpickr-time .flatpickr-time-separator { font-size: 1.25rem !important; line-height: 40px !important; }
    .flatpickr-time .flatpickr-am-pm { width: 50px !important; height: 40px !important; line-height: 40px !important; font-size: 0.875rem !important; }
</style>
@endpush

@php
    $isSameDay = $event->start_datetime && $event->end_datetime && $event->start_datetime->isSameDay($event->end_datetime);
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Edit Event</h1>
            <p class="text-base-content/60 mt-1">Update your event details.</p>
        </div>
        <a href="{{ route('events.show', $event) }}" class="btn btn-ghost btn-sm gap-1.5">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back
        </a>
    </div>

    <x-form-validate action="{{ route('events.update', $event) }}" method="PUT" :has-files="true">
        <div class="space-y-6">
            {{-- Cover Image --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Cover Image</h3>
                </div>
                <div class="card-body">
                    <input type="file" id="cover_image_input" name="cover_image" class="hidden" accept="image/png,image/jpeg,image/webp">

                    <div id="image-preview-wrapper" class="{{ $event->cover_image ? '' : 'hidden' }}">
                        <div class="relative group rounded-xl overflow-hidden">
                            <img id="image-preview" src="{{ $event->cover_image }}" alt="Cover image" class="w-full h-44 object-cover">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                <button type="button" class="btn btn-sm btn-ghost text-white" onclick="document.getElementById('cover_image_input').click()">
                                    <span class="icon-[tabler--edit] size-4"></span> Change
                                </button>
                                <button type="button" class="btn btn-sm btn-ghost text-white" onclick="removeCoverImage()">
                                    <span class="icon-[tabler--trash] size-4"></span> Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="image-upload-zone" class="border-2 border-dashed border-base-content/20 rounded-xl p-6 text-center cursor-pointer hover:border-primary/50 hover:bg-primary/5 transition-colors {{ $event->cover_image ? 'hidden' : '' }}"
                         onclick="document.getElementById('cover_image_input').click()">
                        <span class="icon-[tabler--photo-up] size-8 text-base-content/30 mx-auto block mb-2"></span>
                        <p class="text-sm font-medium text-base-content/70">Click to upload or drag & drop</p>
                        <p class="text-xs text-base-content/50 mt-1">PNG, JPG or WebP. Max 5MB.</p>
                    </div>

                    @error('cover_image')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Basic Info --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Basic Information</h3>
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <label class="label-text" for="title">Event Title <span class="text-error">*</span></label>
                        <input type="text" id="title" name="title" value="{{ old('title', $event->title) }}"
                               class="input w-full @error('title') is-invalid @enderror"
                               placeholder="e.g., Morning Yoga Flow" required minlength="2" maxlength="255">
                        @error('title')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label-text" for="short_description">Short Description</label>
                        <input type="text" id="short_description" name="short_description" value="{{ old('short_description', $event->short_description) }}"
                               class="input w-full" placeholder="A brief tagline for your event">
                    </div>

                    <div>
                        <label class="label-text" for="description">Full Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="textarea w-full"
                                  placeholder="Describe your event in detail...">{{ old('description', $event->description) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Date & Time --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Date & Time</h3>
                </div>
                <div class="card-body space-y-4">
                    {{-- Same day toggle --}}
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-medium">Same day event</span>
                            <p class="text-xs text-base-content/60">Event starts and ends on the same day</p>
                        </div>
                        <label class="switch switch-primary">
                            <input type="checkbox" id="same_day_event" {{ old('same_day_event', $isSameDay) ? 'checked' : '' }} />
                            <span class="switch-indicator"></span>
                        </label>
                    </div>

                    {{-- Dates + Timezone --}}
                    <div id="dates-row" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text" for="start_date">Date <span class="text-error">*</span></label>
                            <input type="text" id="start_date" name="start_date" value="{{ old('start_date', $event->start_datetime->format('Y-m-d')) }}"
                                   class="input w-full" placeholder="Select date" required readonly>
                            @error('start_date')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div id="end_date_wrapper" class="hidden">
                            <label class="label-text" for="end_date">End Date <span class="text-error">*</span></label>
                            <input type="text" id="end_date" name="end_date" value="{{ old('end_date', $event->end_datetime->format('Y-m-d')) }}"
                                   class="input w-full" placeholder="Select date" readonly>
                            @error('end_date')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="label-text" for="timezone">Timezone <span class="text-error">*</span></label>
                            <x-studio-select name="timezone" :options="array_combine($timezones, $timezones)" :selected="old('timezone', $event->timezone)" placeholder="Select timezone..." :required="true" />
                        </div>
                    </div>

                    {{-- Times --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text" for="start_time">Start Time <span class="text-error">*</span></label>
                            <input type="text" id="start_time" name="start_time" value="{{ old('start_time', $event->start_datetime->format('H:i')) }}"
                                   class="input w-full" placeholder="Select time" required readonly>
                            @error('start_time')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="label-text" for="end_time">End Time <span class="text-error">*</span></label>
                            <input type="text" id="end_time" name="end_time" value="{{ old('end_time', $event->end_datetime->format('H:i')) }}"
                                   class="input w-full" placeholder="Select time" required readonly>
                            @error('end_time')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Event Type & Location --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Event Type & Location</h3>
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <label class="label-text mb-2 block">Event Type <span class="text-error">*</span></label>
                        <div class="inline-flex p-1 bg-base-200 rounded-xl gap-1">
                            <label class="cursor-pointer">
                                <input type="radio" name="event_type" value="in_person" class="hidden peer"
                                       {{ old('event_type', $event->event_type) === 'in_person' ? 'checked' : '' }} onchange="toggleLocationFields()">
                                <span class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all peer-checked:bg-base-100 peer-checked:shadow-sm text-base-content/70 peer-checked:text-base-content">
                                    <span class="icon-[tabler--map-pin] size-4"></span> In-Person
                                </span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="event_type" value="online" class="hidden peer"
                                       {{ old('event_type', $event->event_type) === 'online' ? 'checked' : '' }} onchange="toggleLocationFields()">
                                <span class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all peer-checked:bg-base-100 peer-checked:shadow-sm text-base-content/70 peer-checked:text-base-content">
                                    <span class="icon-[tabler--device-laptop] size-4"></span> Online
                                </span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="event_type" value="hybrid" class="hidden peer"
                                       {{ old('event_type', $event->event_type) === 'hybrid' ? 'checked' : '' }} onchange="toggleLocationFields()">
                                <span class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all peer-checked:bg-base-100 peer-checked:shadow-sm text-base-content/70 peer-checked:text-base-content">
                                    <span class="icon-[tabler--arrows-exchange] size-4"></span> Hybrid
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Physical --}}
                    <div id="physical-location-fields" class="space-y-4">
                        {{-- Smarty Address Search --}}
                        <div class="relative" id="event-address-search-wrapper">
                            <label class="label-text font-medium">
                                <span class="icon-[tabler--search] size-4 mr-1"></span>
                                Quick Address Search
                            </label>
                            <div class="flex gap-2 mt-1">
                                <div class="relative flex-1">
                                    <input type="text" id="event-address-search" class="input w-full pr-10" placeholder="Search address, city, or zip code..." autocomplete="off" />
                                    <span id="event-search-loading" class="loading loading-spinner loading-xs absolute top-1/2 right-3 -translate-y-1/2 text-primary hidden"></span>
                                </div>
                                <button type="button" id="event-validate-btn" class="btn btn-outline btn-primary shrink-0" onclick="validateEventAddress()">
                                    <span class="icon-[tabler--check] size-4"></span> Validate
                                </button>
                            </div>
                            <div id="event-address-suggestions" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-72 overflow-y-auto hidden"></div>
                            <div id="event-validation-msg" class="mt-2 hidden"></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label-text" for="venue_name">Venue Name</label>
                                <input type="text" id="venue_name" name="venue_name" value="{{ old('venue_name', $event->venue_name) }}"
                                       class="input w-full" placeholder="e.g., Central Park Lawn">
                            </div>
                            <div>
                                <label class="label-text" for="address_line_1">Street Address</label>
                                <input type="text" id="address_line_1" name="address_line_1" value="{{ old('address_line_1', $event->address_line_1) }}"
                                       class="input w-full" placeholder="Street address">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="label-text" for="city">City</label>
                                <input type="text" id="city" name="city" value="{{ old('city', $event->city) }}" class="input w-full">
                            </div>
                            <div>
                                <label class="label-text" for="state">State</label>
                                <input type="text" id="state" name="state" value="{{ old('state', $event->state) }}" class="input w-full" placeholder="NY">
                            </div>
                            <div>
                                <label class="label-text" for="zip_code">ZIP Code</label>
                                <input type="text" id="zip_code" name="zip_code" value="{{ old('zip_code', $event->zip_code) }}" class="input w-full">
                            </div>
                        </div>
                    </div>

                    {{-- Online --}}
                    <div id="online-location-fields" class="space-y-4 hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label-text" for="online_platform">Platform</label>
                                @php $platforms = ['zoom' => 'Zoom', 'google_meet' => 'Google Meet', 'teams' => 'Microsoft Teams', 'youtube_live' => 'YouTube Live', 'other' => 'Other']; @endphp
                                <x-studio-select name="online_platform" :options="$platforms" :selected="old('online_platform', $event->online_platform)" placeholder="Select platform" />
                            </div>
                            <div>
                                <label class="label-text" for="online_url">Event URL</label>
                                <input type="url" id="online_url" name="online_url" value="{{ old('online_url', $event->online_url) }}"
                                       class="input w-full" placeholder="https://...">
                                <p class="text-xs text-base-content/50 mt-1">Shared with registered attendees</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Visibility --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Visibility</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-base-300 hover:border-primary/50 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                            <input type="radio" name="visibility" value="private" class="radio radio-primary radio-sm mt-0.5"
                                   {{ old('visibility', $event->visibility) === 'private' ? 'checked' : '' }}>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="icon-[tabler--lock] size-4 text-primary"></span>
                                    <span class="font-medium text-sm">Members Only</span>
                                </div>
                                <p class="text-xs text-base-content/60 mt-0.5">Only your studio clients</p>
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
                                <p class="text-xs text-base-content/60 mt-0.5">Only people with the link</p>
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
                                <p class="text-xs text-base-content/60 mt-0.5">Visible to everyone</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Capacity & Audience --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Capacity & Audience</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="label-text" for="capacity">Event Capacity</label>
                            <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $event->capacity) }}"
                                   class="input w-full" min="1" placeholder="Unlimited">
                        </div>
                        <div>
                            <label class="label-text" for="skill_level">Skill Level</label>
                            @php $skillLevels = ['all_levels' => 'All Levels', 'beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced']; @endphp
                            <x-studio-select name="skill_level" :options="$skillLevels" :selected="old('skill_level', $event->skill_level)" placeholder="Select level" />
                        </div>
                        <div>
                            <label class="label-text" for="audience_type">Audience Type</label>
                            @php $audienceTypes = ['all' => 'All Ages', 'adults' => 'Adults (18+)', 'kids' => 'Kids', 'families' => 'Families', 'seniors' => 'Seniors (60+)']; @endphp
                            <x-studio-select name="audience_type" :options="$audienceTypes" :selected="old('audience_type', $event->audience_type)" placeholder="Select audience" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Event Gallery --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Event Gallery</h3>
                </div>
                <div class="card-body">
                    <p class="text-sm text-base-content/60 mb-3">Upload photos to showcase your event. You can select multiple images at once.</p>

                    <input type="file" id="gallery-upload-input" name="gallery_images[]" class="hidden"
                           accept="image/png,image/jpeg,image/webp" multiple>

                    {{-- Existing gallery images --}}
                    @if(is_array($event->gallery_images) && count($event->gallery_images) > 0)
                        @php $uploadsDisk = config('filesystems.uploads'); @endphp
                        <div id="existing-gallery-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-3">
                            @foreach($event->gallery_images as $idx => $img)
                                <div class="relative group rounded-xl overflow-hidden aspect-square bg-base-200" id="existing-gallery-{{ $idx }}">
                                    <img src="{{ Storage::disk($uploadsDisk)->url($img['path']) }}" alt="{{ $img['name'] ?? '' }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <button type="button" class="btn btn-sm btn-circle btn-ghost text-white" onclick="removeExistingGalleryImage({{ $idx }})">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="remove_gallery_images" id="remove-gallery-images" value="">
                    @endif

                    {{-- New files preview grid --}}
                    <div id="gallery-preview-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-3 hidden"></div>

                    {{-- Upload zone --}}
                    <div id="gallery-upload-zone"
                         class="border-2 border-dashed border-base-content/20 rounded-xl p-6 text-center cursor-pointer hover:border-primary/50 hover:bg-primary/5 transition-colors"
                         onclick="document.getElementById('gallery-upload-input').click()"
                         ondragover="event.preventDefault(); this.classList.add('border-primary', 'bg-primary/5')"
                         ondragleave="this.classList.remove('border-primary', 'bg-primary/5')"
                         ondrop="event.preventDefault(); this.classList.remove('border-primary', 'bg-primary/5'); addGalleryFiles(event.dataTransfer.files)">
                        <span class="icon-[tabler--photos] size-8 text-base-content/30 mx-auto block mb-2"></span>
                        <p class="text-sm font-medium text-base-content/70">Click to upload or drag & drop</p>
                        <p class="text-xs text-base-content/50 mt-1">PNG, JPG or WebP. Max 5MB each. Up to 20 images.</p>
                    </div>

                    @error('gallery_images')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                    @error('gallery_images.*')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Documents / Attachments --}}
            <x-studio-file-upload
                name="file_attachments"
                :files="$event->file_attachments ?? []"
                title="Documents & Attachments"
                help="Upload event-related documents like schedules, waivers, or informational PDFs."
            />

            {{-- Questionnaire Attachments --}}
            @if(isset($questionnaires) && $questionnaires->count() > 0)
                @include('host.partials._questionnaire-attachments', [
                    'questionnaires' => $questionnaires,
                    'attachments' => $event->questionnaireAttachments ?? collect()
                ])
            @endif

            {{-- Additional Options --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Additional Options</h3>
                </div>
                <div class="card-body space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-medium">Enable Waitlist</span>
                            <p class="text-xs text-base-content/60">Allow clients to join a waitlist when event is full</p>
                        </div>
                        <label class="switch switch-primary">
                            <input type="checkbox" name="waitlist_enabled" value="1"
                                {{ old('waitlist_enabled', $event->waitlist_enabled) ? 'checked' : '' }} />
                            <span class="switch-indicator"></span>
                        </label>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-medium">Hide Attendee List</span>
                            <p class="text-xs text-base-content/60">Don't show attendee list publicly on the event page</p>
                        </div>
                        <label class="switch switch-primary">
                            <input type="checkbox" name="hide_attendee_list" value="1"
                                {{ old('hide_attendee_list', $event->hide_attendee_list) ? 'checked' : '' }} />
                            <span class="switch-indicator"></span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card bg-base-100">
                <div class="card-body space-y-2">
                    <button type="submit" class="btn btn-primary w-full">
                        <span class="icon-[tabler--device-floppy] size-5"></span>
                        Save Changes
                    </button>
                    <a href="{{ route('events.show', $event) }}" class="btn btn-ghost w-full">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </x-form-validate>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script>
    function toggleLocationFields() {
        var eventType = document.querySelector('input[name="event_type"]:checked')?.value || 'in_person';
        var physicalFields = document.getElementById('physical-location-fields');
        var onlineFields = document.getElementById('online-location-fields');

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

    function removeCoverImage() {
        document.getElementById('cover_image_input').value = '';
        document.getElementById('image-preview').src = '';
        document.getElementById('image-preview-wrapper').classList.add('hidden');
        document.getElementById('image-upload-zone').classList.remove('hidden');
    }

    // Track removed existing gallery images
    var _removedGalleryImages = [];

    function removeExistingGalleryImage(index) {
        var el = document.getElementById('existing-gallery-' + index);
        if (el) el.remove();
        _removedGalleryImages.push(index);
        document.getElementById('remove-gallery-images').value = _removedGalleryImages.join(',');
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleLocationFields();

        // Cover image preview
        var imageInput = document.getElementById('cover_image_input');
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('image-preview').src = e.target.result;
                        document.getElementById('image-preview-wrapper').classList.remove('hidden');
                        document.getElementById('image-upload-zone').classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Flatpickr
        var endDatePicker;

        flatpickr('#start_date', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            onChange: function(selectedDates) {
                if (selectedDates.length > 0) {
                    var sameDayCheckbox = document.getElementById('same_day_event');
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
            altFormat: 'F j, Y'
        });

        flatpickr('#start_time', {
            enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: false,
            altInput: true, altFormat: 'h:i K', altInputClass: 'input input-bordered w-full',
            minuteIncrement: 15, appendTo: document.body, static: false
        });

        flatpickr('#end_time', {
            enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: false,
            altInput: true, altFormat: 'h:i K', altInputClass: 'input input-bordered w-full',
            minuteIncrement: 15, appendTo: document.body, static: false
        });

        // Same day toggle
        var sameDayCheckbox = document.getElementById('same_day_event');
        var endDateWrapper = document.getElementById('end_date_wrapper');
        var datesRow = document.getElementById('dates-row');
        var startDateLabel = document.querySelector('label[for="start_date"]') || datesRow.querySelector('.label-text');

        function toggleEndDate() {
            if (sameDayCheckbox.checked) {
                endDateWrapper.classList.add('hidden');
                datesRow.classList.remove('md:grid-cols-3');
                datesRow.classList.add('md:grid-cols-2');
                if (startDateLabel) startDateLabel.innerHTML = 'Date <span class="text-error">*</span>';
                var startDate = document.getElementById('start_date').value;
                if (startDate) document.getElementById('end_date').value = startDate;
            } else {
                endDateWrapper.classList.remove('hidden');
                datesRow.classList.remove('md:grid-cols-2');
                datesRow.classList.add('md:grid-cols-3');
                if (startDateLabel) startDateLabel.innerHTML = 'Start Date <span class="text-error">*</span>';
            }
        }

        sameDayCheckbox.addEventListener('change', toggleEndDate);
        toggleEndDate();

        // Smarty Address Autocomplete
        var addrSearchInput = document.getElementById('event-address-search');
        var addrSuggestions = document.getElementById('event-address-suggestions');
        var addrLoading = document.getElementById('event-search-loading');
        var addrSearchTimer;

        if (addrSearchInput) {
            addrSearchInput.addEventListener('input', function() {
                clearTimeout(addrSearchTimer);
                var query = this.value.trim();

                if (query.length < 3) {
                    addrSuggestions.classList.add('hidden');
                    return;
                }

                addrLoading.classList.remove('hidden');

                addrSearchTimer = setTimeout(function() {
                    fetch('/api/v1/address/autocomplete?q=' + encodeURIComponent(query))
                        .then(function(r) { return r.json(); })
                        .then(function(results) {
                            addrLoading.classList.add('hidden');

                            if (!results || results.length === 0) {
                                addrSuggestions.innerHTML = '<div class="px-4 py-3 text-base-content/50 text-sm">No addresses found.</div>';
                                addrSuggestions.classList.remove('hidden');
                                return;
                            }

                            addrSuggestions.innerHTML = results.map(function(r, i) {
                                return '<div class="event-addr-sug px-4 py-3 hover:bg-base-200 cursor-pointer border-b border-base-200 last:border-b-0" data-idx="' + i + '">' +
                                    '<div class="font-medium text-sm">' + (r.label || r.street_line || '') + '</div>' +
                                    (r.street_line ? '<div class="text-xs text-base-content/60">' + r.city + ', ' + r.state + ' ' + r.zipcode + '</div>' : '') +
                                '</div>';
                            }).join('');
                            addrSuggestions.classList.remove('hidden');

                            addrSuggestions.querySelectorAll('.event-addr-sug').forEach(function(item) {
                                item.addEventListener('click', function() {
                                    var idx = parseInt(this.dataset.idx);
                                    var selected = results[idx];
                                    applyEventAddress(selected);
                                    addrSearchInput.value = '';
                                    addrSuggestions.classList.add('hidden');
                                });
                            });
                        })
                        .catch(function() {
                            addrLoading.classList.add('hidden');
                            addrSuggestions.classList.add('hidden');
                        });
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!addrSearchInput.contains(e.target) && !addrSuggestions.contains(e.target)) {
                    addrSuggestions.classList.add('hidden');
                }
            });
        }
    });

    function applyEventAddress(result) {
        if (result.street_line) document.getElementById('address_line_1').value = result.street_line;
        if (result.city) document.getElementById('city').value = result.city;
        if (result.state_name) document.getElementById('state').value = result.state_name;
        else if (result.state) document.getElementById('state').value = result.state;
        if (result.zipcode) document.getElementById('zip_code').value = result.zipcode;
    }

    // Gallery multi-upload
    var _galleryFiles = [];

    function addGalleryFiles(newFiles) {
        for (var i = 0; i < newFiles.length; i++) {
            if (_galleryFiles.length >= 20) break;
            var file = newFiles[i];
            if (!file.type.match(/^image\/(png|jpeg|webp)$/)) continue;
            if (file.size > 5 * 1024 * 1024) continue;
            _galleryFiles.push(file);
        }
        syncGalleryInput();
        renderGalleryPreview();
    }

    function removeGalleryFile(index) {
        _galleryFiles.splice(index, 1);
        syncGalleryInput();
        renderGalleryPreview();
    }

    function syncGalleryInput() {
        var input = document.getElementById('gallery-upload-input');
        var dt = new DataTransfer();
        for (var i = 0; i < _galleryFiles.length; i++) {
            dt.items.add(_galleryFiles[i]);
        }
        input.files = dt.files;
    }

    function renderGalleryPreview() {
        var grid = document.getElementById('gallery-preview-grid');
        grid.innerHTML = '';

        if (_galleryFiles.length === 0) {
            grid.classList.add('hidden');
            return;
        }
        grid.classList.remove('hidden');

        _galleryFiles.forEach(function(file, idx) {
            var div = document.createElement('div');
            div.className = 'relative group rounded-xl overflow-hidden aspect-square bg-base-200';

            var img = document.createElement('img');
            img.className = 'w-full h-full object-cover';
            img.src = URL.createObjectURL(file);

            var overlay = document.createElement('div');
            overlay.className = 'absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center';

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-circle btn-ghost text-white';
            removeBtn.innerHTML = '<span class="icon-[tabler--trash] size-4"></span>';
            removeBtn.onclick = function() { removeGalleryFile(idx); };

            overlay.appendChild(removeBtn);
            div.appendChild(img);
            div.appendChild(overlay);
            grid.appendChild(div);
        });
    }

    document.getElementById('gallery-upload-input').addEventListener('change', function() {
        addGalleryFiles(this.files);
    });

    function validateEventAddress() {
        var street = (document.getElementById('address_line_1')?.value || '').trim();
        var city = (document.getElementById('city')?.value || '').trim();
        var state = (document.getElementById('state')?.value || '').trim();
        var zipcode = (document.getElementById('zip_code')?.value || '').trim();
        var msgDiv = document.getElementById('event-validation-msg');
        var btn = document.getElementById('event-validate-btn');

        if (!street && !city && !zipcode) {
            msgDiv.innerHTML = '<div class="alert alert-warning alert-sm"><span class="icon-[tabler--alert-triangle] size-4"></span><span class="text-sm">Enter an address first</span></div>';
            msgDiv.classList.remove('hidden');
            setTimeout(function() { msgDiv.classList.add('hidden'); }, 3000);
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Validating...';

        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;

        fetch('/api/v1/address/validate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ street: street, city: city, state: state, zipcode: zipcode })
        })
        .then(function(r) { return r.json(); })
        .then(function(result) {
            if (result.valid) {
                if (result.street) document.getElementById('address_line_1').value = result.street;
                if (result.city) document.getElementById('city').value = result.city;
                if (result.state_name) document.getElementById('state').value = result.state_name;
                else if (result.state) document.getElementById('state').value = result.state;
                if (result.zipcode) document.getElementById('zip_code').value = result.zipcode;

                msgDiv.innerHTML = '<div class="alert alert-success alert-sm"><span class="icon-[tabler--check] size-4"></span><span class="text-sm">Address validated and updated</span></div>';
            } else {
                msgDiv.innerHTML = '<div class="alert alert-error alert-sm"><span class="icon-[tabler--x] size-4"></span><span class="text-sm">Could not validate this address</span></div>';
            }
            msgDiv.classList.remove('hidden');
            setTimeout(function() { msgDiv.classList.add('hidden'); }, 5000);
        })
        .catch(function() {
            msgDiv.innerHTML = '<div class="alert alert-error alert-sm"><span class="icon-[tabler--x] size-4"></span><span class="text-sm">Validation failed</span></div>';
            msgDiv.classList.remove('hidden');
            setTimeout(function() { msgDiv.classList.add('hidden'); }, 5000);
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<span class="icon-[tabler--check] size-4"></span> Validate';
        });
    }
</script>
@endpush
