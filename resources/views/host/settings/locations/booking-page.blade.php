@extends('layouts.settings')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}" />
@endpush

@section('title', 'Booking Page — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Booking Page</li>
    </ol>
@endsection

@section('settings-content')
    <div class="space-y-6">
        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-soft alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        {{-- Header --}}
        @php
            $bookingDomain = config('app.booking_domain', 'projectfit.com');
            $bookingScheme = app()->environment('local') ? 'http' : 'https';
            $bookingPort = app()->environment('local') ? ':8888' : '';
            $bookingUrl = "{$bookingScheme}://{$host->subdomain}.{$bookingDomain}{$bookingPort}";
        @endphp
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Booking Page</h1>
                <p class="text-base-content/60 text-sm">Customize your public booking page for customers</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ $bookingUrl }}" class="btn btn-ghost btn-sm gap-1" target="_blank">
                    <span class="icon-[tabler--external-link] size-4"></span> Preview
                </a>
            </div>
        </div>

        {{-- Section: Publish Status --}}
        @php $isPublished = ($host->booking_page_status ?? 'draft') === 'published'; @endphp
        <form method="POST" action="{{ route('settings.booking-page.update') }}" id="publish-status-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="publish_status">
            <input type="hidden" name="booking_page_status" id="booking_page_status" value="{{ $host->booking_page_status ?? 'draft' }}" />

            <div class="card border-0 overflow-hidden {{ $isPublished ? 'bg-gradient-to-r from-success/10 via-success/5 to-transparent' : 'bg-gradient-to-r from-warning/10 via-warning/5 to-transparent' }}" id="publish-card">
                <div class="card-body p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div class="size-14 rounded-2xl flex items-center justify-center {{ $isPublished ? 'bg-success/20' : 'bg-warning/20' }}" id="publish-icon-bg">
                                    <span class="{{ $isPublished ? 'icon-[tabler--world] text-success' : 'icon-[tabler--eye-off] text-warning' }} size-7" id="publish-icon"></span>
                                </div>
                                @if($isPublished)
                                <span class="absolute -top-1 -right-1 size-5 rounded-full bg-success flex items-center justify-center" id="publish-dot">
                                    <span class="icon-[tabler--check] size-3 text-success-content"></span>
                                </span>
                                @else
                                <span class="absolute -top-1 -right-1 size-5 rounded-full bg-warning flex items-center justify-center" id="publish-dot">
                                    <span class="icon-[tabler--minus] size-3 text-warning-content"></span>
                                </span>
                                @endif
                            </div>
                            <div>
                                <h2 class="text-lg font-bold" id="publish-title">{{ $isPublished ? 'Page Published' : 'Page Draft' }}</h2>
                                <p class="text-base-content/60 text-sm" id="publish-desc">{{ $isPublished ? 'Your booking page is live and visible to the public' : 'Your booking page is not visible to the public' }}</p>
                                @if($isPublished)
                                    <a href="{{ $bookingUrl }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-success font-medium mt-1 hover:underline">
                                        <span class="icon-[tabler--external-link] size-3"></span>
                                        {{ $host->subdomain }}.{{ config('app.booking_domain', 'fitcrm.app') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-3 sm:flex-col sm:items-end">
                            <label class="switch switch-success">
                                <input type="checkbox" id="booking_page_status_toggle"
                                    {{ $isPublished ? 'checked' : '' }} />
                                <span class="switch-indicator"></span>
                            </label>
                            <span class="text-xs font-medium {{ $isPublished ? 'text-success' : 'text-warning' }}" id="publish-label">{{ $isPublished ? 'Live' : 'Draft' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Accordion Sections --}}
        <div class="accordion divide-y divide-base-200 rounded-lg bg-base-100" id="booking-accordion">

            {{-- Section: Branding & Layout --}}
            <div class="accordion-item">
                <button class="accordion-toggle inline-flex items-center gap-2 px-5 py-4 w-full text-left font-medium" aria-controls="branding-content" aria-expanded="true">
                    <span class="icon-[tabler--palette] size-5 text-primary"></span>
                    <div class="flex-1">
                        <span class="text-lg font-semibold">Branding & Layout</span>
                        <span class="text-base-content/60 text-sm block font-normal">Customize how your booking page looks</span>
                    </div>
                    <span class="icon-[tabler--chevron-down] accordion-icon size-5 transition-transform" style="transform: rotate(180deg)"></span>
                </button>
                <div id="branding-content" class="accordion-content w-full overflow-hidden transition-[height]" role="region">
                    <form method="POST" action="{{ route('settings.booking-page.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="branding">
                        <div class="px-5 pb-5 space-y-6">
                            {{-- Logo Upload --}}
                            <div>
                                <label class="label-text mb-2 block">Logo</label>
                                <div class="flex items-center gap-4">
                                    <div id="logo-preview" class="flex items-center justify-center size-20 bg-base-200 rounded-lg border-2 border-dashed border-base-content/20 overflow-hidden">
                                        @if($host->logo_path)
                                        <img src="{{ Storage::disk(config('filesystems.uploads'))->url($host->logo_path) }}" alt="Logo" class="w-full h-full object-contain" />
                                        @else
                                        <span class="icon-[tabler--photo] size-8 text-base-content/30"></span>
                                        @endif
                                    </div>
                                    <div class="space-y-2">
                                        <input type="file" id="logo-input" class="hidden" accept="image/*" />
                                        <button type="button" onclick="document.getElementById('logo-input').click()" class="btn btn-soft btn-sm">
                                            <span class="icon-[tabler--upload] size-4"></span> Upload Logo
                                        </button>
                                        @if($host->logo_path)
                                        <button type="button" onclick="removeLogo()" class="btn btn-ghost btn-sm text-error">
                                            <span class="icon-[tabler--trash] size-4"></span> Remove
                                        </button>
                                        @endif
                                        <p class="text-xs text-base-content/60">Recommended: 200x200px, PNG or SVG</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Cover Image Upload --}}
                            @php
                                $coverPositionY = $host->booking_settings['cover_position_y'] ?? 50;
                            @endphp
                            <div>
                                <label class="label-text mb-2 block">Cover Image</label>
                                <div id="cover-preview" class="relative w-full h-48 bg-base-200 rounded-xl border-2 border-dashed border-base-content/20 overflow-hidden flex items-center justify-center"
                                     ondragover="event.preventDefault(); this.classList.add('border-primary', 'bg-primary/5')"
                                     ondragleave="this.classList.remove('border-primary', 'bg-primary/5')"
                                     ondrop="event.preventDefault(); this.classList.remove('border-primary', 'bg-primary/5'); handleCoverDrop(event)">
                                    @if($host->cover_image_path)
                                    <img id="cover-img" src="{{ Storage::disk(config('filesystems.uploads'))->url($host->cover_image_path) }}" alt="Cover"
                                         class="w-full h-full object-cover select-none"
                                         style="object-position: center {{ $coverPositionY }}%;"
                                         draggable="false" />

                                    {{-- Reposition hint --}}
                                    <div id="cover-reposition-hint" class="absolute top-2 left-1/2 -translate-x-1/2 bg-black/60 text-white text-xs px-3 py-1.5 rounded-full pointer-events-none hidden">
                                        <span class="icon-[tabler--arrows-vertical] size-3 inline-block align-middle mr-1"></span>
                                        Drag to reposition
                                    </div>

                                    {{-- Actions overlay --}}
                                    <div id="cover-actions" class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition-opacity flex items-end justify-center pb-3 gap-2">
                                        <button type="button" onclick="startRepositioning()" class="btn btn-xs bg-white/20 text-white border-0 hover:bg-white/30 backdrop-blur-sm">
                                            <span class="icon-[tabler--arrows-vertical] size-3.5"></span> Reposition
                                        </button>
                                        <button type="button" onclick="document.getElementById('cover-input').click()" class="btn btn-xs bg-white/20 text-white border-0 hover:bg-white/30 backdrop-blur-sm">
                                            <span class="icon-[tabler--edit] size-3.5"></span> Change
                                        </button>
                                        <button type="button" onclick="removeCover()" class="btn btn-xs bg-white/20 text-white border-0 hover:bg-white/30 backdrop-blur-sm">
                                            <span class="icon-[tabler--trash] size-3.5"></span> Remove
                                        </button>
                                    </div>

                                    {{-- Reposition save bar --}}
                                    <div id="cover-reposition-bar" class="absolute bottom-0 inset-x-0 bg-black/70 backdrop-blur-sm px-4 py-2 flex items-center justify-between hidden">
                                        <span class="text-white text-xs">Drag image up or down to adjust</span>
                                        <div class="flex gap-2">
                                            <button type="button" onclick="cancelRepositioning()" class="btn btn-xs btn-ghost text-white">Cancel</button>
                                            <button type="button" onclick="savePosition()" class="btn btn-xs btn-primary">Save Position</button>
                                        </div>
                                    </div>
                                    @else
                                    <div class="text-center cursor-pointer" onclick="document.getElementById('cover-input').click()">
                                        <span class="icon-[tabler--photo-up] size-10 text-base-content/30"></span>
                                        <p class="text-sm font-medium text-base-content/60 mt-2">Click to upload or drag & drop</p>
                                        <p class="text-xs text-base-content/40 mt-1">Recommended: 1200x400px</p>
                                    </div>
                                    @endif
                                </div>
                                <input type="file" id="cover-input" class="hidden" accept="image/*" />
                                <p class="text-xs text-base-content/50 mt-1.5">JPG, PNG or WebP. After uploading, click "Reposition" to adjust the visible area.</p>
                            </div>

                            {{-- Display Name --}}
                            <div>
                                <label class="label-text" for="display_name">Display Name</label>
                                <input
                                    id="display_name"
                                    name="display_name"
                                    type="text"
                                    class="input w-full"
                                    placeholder="{{ $host->studio_name }}"
                                    value="{{ old('display_name', $settings['display_name'] ?? '') }}"
                                />
                                <p class="text-xs text-base-content/60 mt-1">Leave blank to use your studio name</p>
                            </div>

                            {{-- Primary Color --}}
                            <div>
                                <label class="label-text" for="primary_color">Brand Color</label>
                                <div class="flex items-center gap-3">
                                    <input
                                        type="color"
                                        id="color-picker"
                                        value="{{ old('primary_color', $settings['primary_color'] ?? '#6366f1') }}"
                                        class="w-12 h-10 rounded-lg border border-base-content/20 cursor-pointer"
                                        onchange="document.getElementById('primary_color').value = this.value"
                                    />
                                    <input
                                        id="primary_color"
                                        name="primary_color"
                                        type="text"
                                        class="input w-32"
                                        value="{{ old('primary_color', $settings['primary_color'] ?? '#6366f1') }}"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        onchange="document.getElementById('color-picker').value = this.value"
                                    />
                                </div>
                            </div>

                            {{-- Theme & Font --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="label-text" for="theme">Theme</label>
                                    <select id="theme" name="theme" class="select w-full">
                                        @foreach($themes as $value => $label)
                                        <option value="{{ $value }}" {{ old('theme', $settings['theme'] ?? 'light') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="label-text" for="font">Font</label>
                                    <select id="font" name="font" class="select w-full">
                                        @foreach($fonts as $value => $label)
                                        <option value="{{ $value }}" {{ old('font', $settings['font'] ?? 'inter') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-base-200">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <span class="icon-[tabler--check] size-4"></span> Save Branding
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Section: Public Content --}}
            <div class="accordion-item">
                <button class="accordion-toggle inline-flex items-center gap-2 px-5 py-4 w-full text-left font-medium" aria-controls="public-content-content" aria-expanded="false">
                    <span class="icon-[tabler--file-text] size-5 text-info"></span>
                    <div class="flex-1">
                        <span class="text-lg font-semibold">Public Content</span>
                        <span class="text-base-content/60 text-sm block font-normal">What information to show on your booking page</span>
                    </div>
                    <span class="icon-[tabler--chevron-down] accordion-icon size-5 transition-transform"></span>
                </button>
                <div id="public-content-content" class="accordion-content w-full overflow-hidden transition-[height] hidden" role="region">
                    <form method="POST" action="{{ route('settings.booking-page.update') }}" id="public-content-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="public_content">
                        <div class="px-5 pb-5 space-y-6">
                            {{-- About Text --}}
                            <div>
                                <label class="label-text">About Your Studio</label>
                                <input type="hidden" name="about_text" id="about_text_hidden" value="{{ old('about_text', $settings['about_text'] ?? '') }}" />
                                <div id="about-text-editor" class="bg-base-100 rounded-b-lg" style="min-height: 120px;">{!! old('about_text', $settings['about_text'] ?? '') !!}</div>
                                <p class="text-xs text-base-content/60 mt-1">This appears on your booking page. Keep it concise.</p>
                            </div>

                            {{-- Toggle Options --}}
                            <div class="space-y-4">
                                <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="show_address" value="1" class="checkbox checkbox-primary mt-1"
                                        {{ old('show_address', $host->show_address ?? true) ? 'checked' : '' }} />
                                    <span class="label-text w-full text-start">
                                        <span class="text-base font-medium">Show Address</span>
                                        <span class="text-base-content/70 block">Display studio address in the hero section</span>
                                    </span>
                                </label>

                                <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="show_social_links" value="1" class="checkbox checkbox-primary mt-1"
                                        {{ old('show_social_links', $host->show_social_links ?? true) ? 'checked' : '' }} />
                                    <span class="label-text w-full text-start">
                                        <span class="text-base font-medium">Show Social Links</span>
                                        <span class="text-base-content/70 block">Display social media icons in the hero section</span>
                                    </span>
                                </label>

                                <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="show_instructors" value="1" class="checkbox checkbox-primary mt-1"
                                        {{ old('show_instructors', $settings['show_instructors'] ?? true) ? 'checked' : '' }} />
                                    <span class="label-text w-full text-start">
                                        <span class="text-base font-medium">Show Instructors</span>
                                        <span class="text-base-content/70 block">Display instructor list on booking page</span>
                                    </span>
                                </label>

                                <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="show_amenities" value="1" class="checkbox checkbox-primary mt-1"
                                        {{ old('show_amenities', $settings['show_amenities'] ?? true) ? 'checked' : '' }} />
                                    <span class="label-text w-full text-start">
                                        <span class="text-base font-medium">Show Amenities</span>
                                        <span class="text-base-content/70 block">Display studio amenities on booking page</span>
                                    </span>
                                </label>
                            </div>

                            {{-- Location Display --}}
                            <div>
                                <label class="label-text" for="location_display">Location Display</label>
                                <select
                                    id="location_display"
                                    name="location_display"
                                    data-select='{
                                        "hasSearch": true,
                                        "searchPlaceholder": "Search...",
                                        "placeholder": "Select display mode...",
                                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                        "toggleClasses": "advance-select-toggle w-full",
                                        "dropdownClasses": "advance-select-menu max-h-48 overflow-y-auto",
                                        "optionClasses": "advance-select-option selected:select-active",
                                        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                    }'
                                    class="hidden"
                                >
                                    <option value="auto" {{ old('location_display', $settings['location_display'] ?? 'auto') === 'auto' ? 'selected' : '' }}>
                                        Auto (based on number of locations)
                                    </option>
                                    <option value="single" {{ old('location_display', $settings['location_display'] ?? 'auto') === 'single' ? 'selected' : '' }}>
                                        Single location view
                                    </option>
                                    <option value="multi" {{ old('location_display', $settings['location_display'] ?? 'auto') === 'multi' ? 'selected' : '' }}>
                                        Multi-location view
                                    </option>
                                </select>
                                <p class="text-xs text-base-content/60 mt-1">You have {{ $locations->count() }} location(s)</p>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-base-200">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <span class="icon-[tabler--check] size-4"></span> Save Public Content
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Section: Booking Experience --}}
            <div class="accordion-item">
                <button class="accordion-toggle inline-flex items-center gap-2 px-5 py-4 w-full text-left font-medium" aria-controls="booking-ux-content" aria-expanded="false">
                    <span class="icon-[tabler--calendar-event] size-5 text-success"></span>
                    <div class="flex-1">
                        <span class="text-lg font-semibold">Booking Experience</span>
                        <span class="text-base-content/60 text-sm block font-normal">Configure how customers book classes</span>
                    </div>
                    <span class="icon-[tabler--chevron-down] accordion-icon size-5 transition-transform"></span>
                </button>
                <div id="booking-ux-content" class="accordion-content w-full overflow-hidden transition-[height] hidden" role="region">
                    <form method="POST" action="{{ route('settings.booking-page.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="booking_ux">
                        <div class="px-5 pb-5 space-y-6">
                            {{-- Default View --}}
                            <div>
                                <label class="label-text mb-3 block">Default Schedule View</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer p-3 border border-base-content/10 rounded-lg has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="radio" name="default_view" value="calendar" class="radio radio-primary radio-sm"
                                            {{ old('default_view', $settings['default_view'] ?? 'calendar') === 'calendar' ? 'checked' : '' }} />
                                        <span class="icon-[tabler--calendar] size-5"></span>
                                        <span>Calendar</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer p-3 border border-base-content/10 rounded-lg has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="radio" name="default_view" value="list" class="radio radio-primary radio-sm"
                                            {{ old('default_view', $settings['default_view'] ?? 'calendar') === 'list' ? 'checked' : '' }} />
                                        <span class="icon-[tabler--list] size-5"></span>
                                        <span>List</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Toggle Options --}}
                            <div class="space-y-4">
                                <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="show_class_descriptions" value="1" class="checkbox checkbox-primary mt-1"
                                        {{ old('show_class_descriptions', $settings['show_class_descriptions'] ?? true) ? 'checked' : '' }} />
                                    <span class="label-text w-full text-start">
                                        <span class="text-base font-medium">Show Class Descriptions</span>
                                        <span class="text-base-content/70 block">Display full class descriptions on the schedule</span>
                                    </span>
                                </label>

                                <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="show_instructor_photos" value="1" class="checkbox checkbox-primary mt-1"
                                        {{ old('show_instructor_photos', $settings['show_instructor_photos'] ?? true) ? 'checked' : '' }} />
                                    <span class="label-text w-full text-start">
                                        <span class="text-base font-medium">Show Instructor Photos</span>
                                        <span class="text-base-content/70 block">Display instructor profile photos next to classes</span>
                                    </span>
                                </label>

                                <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="allow_waitlist" value="1" class="checkbox checkbox-primary mt-1"
                                        {{ old('allow_waitlist', $settings['allow_waitlist'] ?? true) ? 'checked' : '' }} />
                                    <span class="label-text w-full text-start">
                                        <span class="text-base font-medium">Allow Waitlist</span>
                                        <span class="text-base-content/70 block">Let customers join a waitlist when classes are full</span>
                                    </span>
                                </label>

                                <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="require_account" value="1" class="checkbox checkbox-primary mt-1"
                                        {{ old('require_account', $settings['require_account'] ?? false) ? 'checked' : '' }} />
                                    <span class="label-text w-full text-start">
                                        <span class="text-base font-medium">Require Account to Book</span>
                                        <span class="text-base-content/70 block">Customers must create an account before booking</span>
                                    </span>
                                </label>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-base-200">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <span class="icon-[tabler--check] size-4"></span> Save Booking Experience
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Section: Filter Options --}}
            <div class="accordion-item">
                <button class="accordion-toggle inline-flex items-center gap-2 px-5 py-4 w-full text-left font-medium" aria-controls="filters-content" aria-expanded="false">
                    <span class="icon-[tabler--filter] size-5 text-warning"></span>
                    <div class="flex-1">
                        <span class="text-lg font-semibold">Filter Options</span>
                        <span class="text-base-content/60 text-sm block font-normal">Choose which filters customers can use</span>
                    </div>
                    <span class="icon-[tabler--chevron-down] accordion-icon size-5 transition-transform"></span>
                </button>
                <div id="filters-content" class="accordion-content w-full overflow-hidden transition-[height] hidden" role="region">
                    <form method="POST" action="{{ route('settings.booking-page.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="filters">
                        <div class="px-5 pb-5 space-y-6">
                            <div class="space-y-4">
                                <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="filter_class_type" value="1" class="checkbox checkbox-primary mt-1"
                                        {{ old('filter_class_type', $settings['filter_class_type'] ?? true) ? 'checked' : '' }} />
                                    <span class="label-text w-full text-start">
                                        <span class="text-base font-medium">Filter by Class Type</span>
                                        <span class="text-base-content/70 block">Allow filtering classes by type (e.g., Yoga, Pilates)</span>
                                    </span>
                                </label>

                                <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="filter_instructor" value="1" class="checkbox checkbox-primary mt-1"
                                        {{ old('filter_instructor', $settings['filter_instructor'] ?? true) ? 'checked' : '' }} />
                                    <span class="label-text w-full text-start">
                                        <span class="text-base font-medium">Filter by Instructor</span>
                                        <span class="text-base-content/70 block">Allow filtering classes by instructor</span>
                                    </span>
                                </label>

                                @if($locations->count() > 1)
                                <label class="custom-option flex flex-row items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="filter_location" value="1" class="checkbox checkbox-primary mt-1"
                                        {{ old('filter_location', $settings['filter_location'] ?? true) ? 'checked' : '' }} />
                                    <span class="label-text w-full text-start">
                                        <span class="text-base font-medium">Filter by Location</span>
                                        <span class="text-base-content/70 block">Allow filtering classes by location</span>
                                    </span>
                                </label>
                                @endif
                            </div>

                            <div class="flex justify-end pt-4 border-t border-base-200">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <span class="icon-[tabler--check] size-4"></span> Save Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>{{-- end accordion --}}

        {{-- Booking Page URL --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-1">Your Booking Page</h2>
                <p class="text-base-content/60 text-sm mb-4">Share this link with your customers</p>

                <div class="flex items-center gap-2">
                    <div class="flex-1 p-3 bg-base-200 rounded-lg font-mono text-sm flex items-center gap-2">
                        <span class="icon-[tabler--world] size-4 text-base-content/50"></span>
                        <span id="booking-url">{{ $bookingUrl }}</span>
                    </div>
                    <button type="button" onclick="copyBookingUrl()" class="btn btn-soft btn-sm gap-1">
                        <span class="icon-[tabler--copy] size-4"></span> Copy
                    </button>
                    <a href="{{ $bookingUrl }}" target="_blank" class="btn btn-ghost btn-sm gap-1">
                        <span class="icon-[tabler--external-link] size-4"></span> Open
                    </a>
                </div>

                <div class="mt-4 p-4 bg-base-200/50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--info-circle] size-5 text-info mt-0.5"></span>
                        <div>
                            <p class="text-sm font-medium">Your subdomain</p>
                            <p class="text-sm text-base-content/60 mt-1">
                                Your booking page subdomain <span class="font-mono font-semibold text-primary">{{ $host->subdomain }}</span> was set during onboarding.
                                Contact support if you need to change it.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/quill/quill.js') }}"></script>
<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Initialize Quill editor for About Your Studio
var aboutTextQuill = new Quill('#about-text-editor', {
    theme: 'snow',
    placeholder: 'Tell customers about your studio, classes, and what makes you unique...',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, 4, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'align': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['blockquote'],
            ['link'],
            ['clean']
        ]
    }
});

// Sync Quill content to hidden input on form submit
document.getElementById('public-content-form').addEventListener('submit', function() {
    var content = aboutTextQuill.root.innerHTML;
    if (content === '<p><br></p>' || content.trim() === '') {
        content = '';
    }
    document.getElementById('about_text_hidden').value = content;
});

// Handle publish status toggle
document.getElementById('booking_page_status_toggle').addEventListener('change', function() {
    var isPublished = this.checked;
    document.getElementById('booking_page_status').value = isPublished ? 'published' : 'draft';

    var card = document.getElementById('publish-card');
    var iconBg = document.getElementById('publish-icon-bg');
    var icon = document.getElementById('publish-icon');
    var dot = document.getElementById('publish-dot');
    var title = document.getElementById('publish-title');
    var desc = document.getElementById('publish-desc');
    var label = document.getElementById('publish-label');

    if (isPublished) {
        card.className = 'card border-0 overflow-hidden bg-gradient-to-r from-success/10 via-success/5 to-transparent';
        iconBg.className = 'size-14 rounded-2xl flex items-center justify-center bg-success/20';
        icon.className = 'icon-[tabler--world] text-success size-7';
        dot.className = 'absolute -top-1 -right-1 size-5 rounded-full bg-success flex items-center justify-center';
        dot.innerHTML = '<span class="icon-[tabler--check] size-3 text-success-content"></span>';
        title.textContent = 'Page Published';
        desc.textContent = 'Your booking page is live and visible to the public';
        label.className = 'text-xs font-medium text-success';
        label.textContent = 'Live';
    } else {
        card.className = 'card border-0 overflow-hidden bg-gradient-to-r from-warning/10 via-warning/5 to-transparent';
        iconBg.className = 'size-14 rounded-2xl flex items-center justify-center bg-warning/20';
        icon.className = 'icon-[tabler--eye-off] text-warning size-7';
        dot.className = 'absolute -top-1 -right-1 size-5 rounded-full bg-warning flex items-center justify-center';
        dot.innerHTML = '<span class="icon-[tabler--minus] size-3 text-warning-content"></span>';
        title.textContent = 'Page Draft';
        desc.textContent = 'Your booking page is not visible to the public';
        label.className = 'text-xs font-medium text-warning';
        label.textContent = 'Draft';
    }

    // Auto-submit the form
    document.getElementById('publish-status-form').submit();
});

function showToast(message, type) {
    type = type || 'success';
    var toast = document.createElement('div');
    toast.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[100] alert alert-' + type + ' shadow-lg max-w-sm';
    toast.innerHTML = '<span class="icon-[tabler--' + (type === 'success' ? 'check' : 'alert-circle') + '] size-5"></span><span>' + message + '</span>';
    document.body.appendChild(toast);
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}

// Logo upload
document.getElementById('logo-input').addEventListener('change', function(e) {
    if (!e.target.files[0]) return;

    var formData = new FormData();
    formData.append('logo', e.target.files[0]);

    fetch('{{ route("settings.booking-page.upload-logo") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            document.getElementById('logo-preview').innerHTML = '<img src="' + result.path + '" alt="Logo" class="w-full h-full object-contain" />';
            showToast('Logo uploaded');
            location.reload();
        } else {
            showToast('Failed to upload logo', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); });
});

// Cover upload
document.getElementById('cover-input').addEventListener('change', function(e) {
    if (!e.target.files[0]) return;
    uploadCoverFile(e.target.files[0]);
});

function handleCoverDrop(e) {
    var files = e.dataTransfer.files;
    if (files.length > 0 && files[0].type.startsWith('image/')) {
        uploadCoverFile(files[0]);
    }
}

function uploadCoverFile(file) {
    var formData = new FormData();
    formData.append('cover', file);

    fetch('{{ route("settings.booking-page.upload-cover") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            showToast('Cover image uploaded');
            location.reload();
        } else {
            showToast('Failed to upload cover', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); });
}

// Cover reposition
var isRepositioning = false;
var isDragging = false;
var dragStartY = 0;
var currentPositionY = {{ $host->booking_settings['cover_position_y'] ?? 50 }};
var savedPositionY = currentPositionY;

function startRepositioning() {
    isRepositioning = true;
    var coverImg = document.getElementById('cover-img');
    var actions = document.getElementById('cover-actions');
    var bar = document.getElementById('cover-reposition-bar');
    var hint = document.getElementById('cover-reposition-hint');

    actions.classList.add('hidden');
    bar.classList.remove('hidden');
    hint.classList.remove('hidden');
    coverImg.style.cursor = 'grab';
    savedPositionY = currentPositionY;

    setTimeout(function() { hint.classList.add('hidden'); }, 2000);
}

function cancelRepositioning() {
    isRepositioning = false;
    currentPositionY = savedPositionY;
    var coverImg = document.getElementById('cover-img');
    coverImg.style.objectPosition = 'center ' + savedPositionY + '%';
    coverImg.style.cursor = '';
    document.getElementById('cover-actions').classList.remove('hidden');
    document.getElementById('cover-reposition-bar').classList.add('hidden');
    document.getElementById('cover-reposition-hint').classList.add('hidden');
}

function savePosition() {
    fetch('{{ route("settings.booking-page.cover-position") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ position_y: currentPositionY })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            savedPositionY = currentPositionY;
            showToast('Cover position saved');
        } else {
            showToast('Failed to save position', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); });

    isRepositioning = false;
    var coverImg = document.getElementById('cover-img');
    coverImg.style.cursor = '';
    document.getElementById('cover-actions').classList.remove('hidden');
    document.getElementById('cover-reposition-bar').classList.add('hidden');
}

// Drag to reposition
(function() {
    var preview = document.getElementById('cover-preview');

    preview.addEventListener('mousedown', function(e) {
        if (!isRepositioning) return;
        isDragging = true;
        dragStartY = e.clientY;
        var coverImg = document.getElementById('cover-img');
        coverImg.style.cursor = 'grabbing';
        e.preventDefault();
    });

    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        var delta = dragStartY - e.clientY;
        dragStartY = e.clientY;
        currentPositionY = Math.max(0, Math.min(100, currentPositionY + delta * 0.5));
        var coverImg = document.getElementById('cover-img');
        coverImg.style.objectPosition = 'center ' + currentPositionY + '%';
    });

    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            var coverImg = document.getElementById('cover-img');
            if (coverImg) coverImg.style.cursor = 'grab';
        }
    });

    // Touch support
    preview.addEventListener('touchstart', function(e) {
        if (!isRepositioning) return;
        isDragging = true;
        dragStartY = e.touches[0].clientY;
        e.preventDefault();
    }, { passive: false });

    document.addEventListener('touchmove', function(e) {
        if (!isDragging) return;
        var delta = dragStartY - e.touches[0].clientY;
        dragStartY = e.touches[0].clientY;
        currentPositionY = Math.max(0, Math.min(100, currentPositionY + delta * 0.5));
        var coverImg = document.getElementById('cover-img');
        coverImg.style.objectPosition = 'center ' + currentPositionY + '%';
    }, { passive: false });

    document.addEventListener('touchend', function() {
        isDragging = false;
    });
})();

// Remove logo
function removeLogo() {
    fetch('{{ route("settings.booking-page.remove-logo") }}', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            showToast('Logo removed');
            location.reload();
        }
    });
}

// Remove cover
function removeCover() {
    fetch('{{ route("settings.booking-page.remove-cover") }}', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            showToast('Cover image removed');
            location.reload();
        }
    });
}

// Copy booking URL
function copyBookingUrl() {
    var url = document.getElementById('booking-url').textContent;
    navigator.clipboard.writeText(url).then(function() {
        showToast('URL copied to clipboard');
    });
}

// Accordion: close others when one opens
document.querySelectorAll('#booking-accordion .accordion-toggle').forEach(function(button) {
    button.addEventListener('click', function() {
        var content = document.getElementById(this.getAttribute('aria-controls'));
        var icon = this.querySelector('.accordion-icon');
        var isExpanded = this.getAttribute('aria-expanded') === 'true';

        document.querySelectorAll('#booking-accordion .accordion-toggle').forEach(function(otherBtn) {
            if (otherBtn !== button) {
                var otherContent = document.getElementById(otherBtn.getAttribute('aria-controls'));
                var otherIcon = otherBtn.querySelector('.accordion-icon');
                if (otherContent) otherContent.classList.add('hidden');
                otherBtn.setAttribute('aria-expanded', 'false');
                if (otherIcon) otherIcon.style.transform = 'rotate(0deg)';
            }
        });

        if (isExpanded) {
            content.classList.add('hidden');
            this.setAttribute('aria-expanded', 'false');
            if (icon) icon.style.transform = 'rotate(0deg)';
        } else {
            content.classList.remove('hidden');
            this.setAttribute('aria-expanded', 'true');
            if (icon) icon.style.transform = 'rotate(180deg)';
        }
    });
});
</script>
@endpush
