@extends('layouts.settings')

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
<form method="POST" action="{{ route('settings.booking-page.update') }}">
    @csrf
    @method('PUT')

    <div class="space-y-6">
        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-soft alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Booking Page</h1>
                <p class="text-base-content/60 text-sm">Customize your public booking page for customers</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ url('/' . $host->subdomain) }}" class="btn btn-ghost btn-sm" target="_blank">
                    <span class="icon-[tabler--external-link] size-4"></span> Preview
                </a>
                <button type="submit" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--check] size-4"></span> Save Changes
                </button>
            </div>
        </div>

        {{-- Section A: Branding & Layout --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-1">Branding & Layout</h2>
                <p class="text-base-content/60 text-sm mb-6">Customize how your booking page looks</p>

                <div class="space-y-6">
                    {{-- Logo Upload --}}
                    <div>
                        <label class="label-text mb-2 block">Logo</label>
                        <div class="flex items-center gap-4">
                            <div id="logo-preview" class="flex items-center justify-center size-20 bg-base-200 rounded-lg border-2 border-dashed border-base-content/20 overflow-hidden">
                                @if($host->logo_path)
                                <img src="{{ Storage::url($host->logo_path) }}" alt="Logo" class="w-full h-full object-contain" />
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
                    <div>
                        <label class="label-text mb-2 block">Cover Image</label>
                        <div id="cover-preview" class="relative w-full h-40 bg-base-200 rounded-lg border-2 border-dashed border-base-content/20 overflow-hidden flex items-center justify-center">
                            @if($host->cover_image_path)
                            <img src="{{ Storage::url($host->cover_image_path) }}" alt="Cover" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                <button type="button" onclick="document.getElementById('cover-input').click()" class="btn btn-sm btn-ghost text-white">
                                    <span class="icon-[tabler--edit] size-4"></span> Change
                                </button>
                                <button type="button" onclick="removeCover()" class="btn btn-sm btn-ghost text-white">
                                    <span class="icon-[tabler--trash] size-4"></span> Remove
                                </button>
                            </div>
                            @else
                            <div class="text-center">
                                <span class="icon-[tabler--photo] size-10 text-base-content/30"></span>
                                <p class="text-sm text-base-content/60 mt-2">No cover image</p>
                            </div>
                            @endif
                        </div>
                        <input type="file" id="cover-input" class="hidden" accept="image/*" />
                        @if(!$host->cover_image_path)
                        <button type="button" onclick="document.getElementById('cover-input').click()" class="btn btn-soft btn-sm mt-2">
                            <span class="icon-[tabler--upload] size-4"></span> Upload Cover Image
                        </button>
                        @endif
                        <p class="text-xs text-base-content/60 mt-1">Recommended: 1200x400px, JPG or PNG</p>
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
                </div>
            </div>
        </div>

        {{-- Section B: Public Content --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-1">Public Content</h2>
                <p class="text-base-content/60 text-sm mb-6">What information to show on your booking page</p>

                <div class="space-y-6">
                    {{-- About Text --}}
                    <div>
                        <label class="label-text" for="about_text">About Your Studio</label>
                        <textarea
                            id="about_text"
                            name="about_text"
                            class="textarea w-full"
                            rows="4"
                            placeholder="Tell customers about your studio, classes, and what makes you unique..."
                        >{{ old('about_text', $settings['about_text'] ?? '') }}</textarea>
                        <p class="text-xs text-base-content/60 mt-1">This appears on your booking page. Keep it concise.</p>
                    </div>

                    {{-- Toggle Options --}}
                    <div class="space-y-4">
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
                </div>
            </div>
        </div>

        {{-- Section C: Booking UX --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-1">Booking Experience</h2>
                <p class="text-base-content/60 text-sm mb-6">Configure how customers book classes</p>

                <div class="space-y-6">
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
                </div>
            </div>
        </div>

        {{-- Section D: Filters --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-1">Filter Options</h2>
                <p class="text-base-content/60 text-sm mb-6">Choose which filters customers can use</p>

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
            </div>
        </div>

        {{-- Booking Page URL --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-1">Your Booking Page</h2>
                <p class="text-base-content/60 text-sm mb-4">Share this link with your customers</p>

                <div class="flex items-center gap-2">
                    <div class="flex-1 p-3 bg-base-200 rounded-lg font-mono text-sm">
                        {{ config('app.url') }}/{{ $host->subdomain }}
                    </div>
                    <button type="button" onclick="copyBookingUrl()" class="btn btn-soft btn-sm">
                        <span class="icon-[tabler--copy] size-4"></span> Copy
                    </button>
                </div>
            </div>
        </div>

        {{-- Save Button (Bottom) --}}
        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--check] size-4"></span> Save Changes
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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

    var formData = new FormData();
    formData.append('cover', e.target.files[0]);

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
});

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
    var url = '{{ config("app.url") }}/{{ $host->subdomain }}';
    navigator.clipboard.writeText(url).then(function() {
        showToast('URL copied to clipboard');
    });
}
</script>
@endpush
