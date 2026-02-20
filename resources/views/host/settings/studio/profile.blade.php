@extends('layouts.settings')

@section('title', 'Studio Profile — Settings')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}" />
<style>
#about-editor .ql-container { font-size: 0.875rem; min-height: 150px; }
#about-editor .ql-toolbar { border-radius: 0.5rem 0.5rem 0 0; border-color: hsl(var(--bc) / 0.2); }
#about-editor .ql-container { border-radius: 0 0 0.5rem 0.5rem; border-color: hsl(var(--bc) / 0.2); }
</style>
@endpush

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Studio Profile</li>
    </ol>
@endsection

@php
$currencies = [
    'USD' => ['symbol' => '$', 'name' => 'US Dollar'],
    'CAD' => ['symbol' => 'C$', 'name' => 'Canadian Dollar'],
    'GBP' => ['symbol' => '£', 'name' => 'Pound Sterling'],
    'EUR' => ['symbol' => '€', 'name' => 'Euro'],
    'AUD' => ['symbol' => 'A$', 'name' => 'Australian Dollar'],
    'INR' => ['symbol' => '₹', 'name' => 'Indian Rupee'],
];

$amenitiesList = [
    'Parking', 'Showers', 'Lockers', 'Mats Provided', 'Towels Provided',
    'Reformer Equipment', 'Wheelchair Accessible', 'Water Station',
    'Changing Rooms', 'Air Conditioning', 'Wifi', 'Retail Shop'
];

$studioTypesList = ['Yoga', 'Pilates (Mat)', 'Pilates (Reformer)', 'Fitness', 'CrossFit', 'Barre', 'Dance', 'Martial Arts', 'Personal Training', 'Other'];
@endphp

@section('settings-content')
<div class="space-y-6">

    {{-- Basic Info Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Basic Information</h2>
                    <p class="text-base-content/60 text-sm">Your studio name, type, and location</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openDrawer('edit-basic-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> Edit
                </button>
            </div>

            <div class="space-y-4">
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Studio Name</label>
                    <p class="font-medium" id="display-studio-name">{{ $host->studio_name ?? 'Not set' }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Short Description</label>
                    <p class="font-medium" id="display-short-description">{{ $host->short_description ?: 'Not set' }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Subdomain</label>
                    <p class="font-medium" id="display-subdomain">{{ $host->subdomain ? $host->subdomain . '.' . config('app.booking_domain', 'fitcrm.biz') : 'Not set' }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Studio Types</label>
                    <div class="flex flex-wrap gap-1" id="display-types">
                        @if($host->studio_types && count($host->studio_types) > 0)
                            @foreach($host->studio_types as $type)
                                <span class="badge badge-primary badge-soft badge-sm">{{ $type }}</span>
                            @endforeach
                        @else
                            <span class="text-base-content/50">Not set</span>
                        @endif
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Timezone</label>
                    <p class="font-medium" id="display-timezone">{{ $host->timezone ?? 'Not set' }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Location</label>
                    @if($defaultLocation ?? null)
                        <p class="font-medium" id="display-location">{{ $defaultLocation->full_address }}</p>
                        <a href="{{ route('settings.locations.edit', $defaultLocation->id) }}" class="text-xs text-primary hover:underline">Edit in Location Settings</a>
                    @else
                        <p class="text-base-content/50" id="display-location">No location set</p>
                        <a href="{{ route('settings.locations.create') }}" class="text-xs text-primary hover:underline">Add a location</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Branding Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Branding</h2>
                    <p class="text-base-content/60 text-sm">Logo and cover image for your booking page</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Logo --}}
                <div class="space-y-3">
                    <label class="text-sm font-medium">Studio Logo</label>
                    <div class="flex items-center gap-4">
                        <div id="logo-preview" class="w-20 h-20 bg-base-200 rounded-lg flex items-center justify-center overflow-hidden border border-base-300">
                            @if($host->logo_path)
                                <img src="{{ Storage::disk(config('filesystems.uploads'))->url($host->logo_path) }}" alt="Studio Logo" class="w-full h-full object-cover" />
                            @else
                                <span class="icon-[tabler--photo] size-8 text-base-content/30"></span>
                            @endif
                        </div>
                        <div>
                            <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('upload-logo-drawer')">
                                <span class="icon-[tabler--upload] size-4"></span> Upload Logo
                            </button>
                            <p class="text-xs text-base-content/50 mt-1">400x400px, max 5MB</p>
                        </div>
                    </div>
                </div>

                {{-- Cover Image --}}
                <div class="space-y-3">
                    <label class="text-sm font-medium">Cover Image</label>
                    <div class="flex items-center gap-4">
                        <div id="cover-preview" class="w-32 h-20 bg-base-200 rounded-lg flex items-center justify-center overflow-hidden border border-base-300">
                            @if($host->cover_image_path)
                                <img src="{{ Storage::disk(config('filesystems.uploads'))->url($host->cover_image_path) }}" alt="Cover Image" class="w-full h-full object-cover" />
                            @else
                                <span class="icon-[tabler--photo] size-8 text-base-content/30"></span>
                            @endif
                        </div>
                        <div>
                            <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('upload-cover-drawer')">
                                <span class="icon-[tabler--upload] size-4"></span> Upload Cover
                            </button>
                            <p class="text-xs text-base-content/50 mt-1">1200x400px, max 5MB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- About Card with Inline Edit --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold">About Your Studio</h2>
                    <p class="text-base-content/60 text-sm">Description shown on your public booking page</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" id="about-edit-btn" onclick="toggleAboutEdit()">
                    <span class="icon-[tabler--edit] size-4"></span> Edit
                </button>
            </div>

            {{-- Display Mode --}}
            <div id="about-display" class="prose prose-sm max-w-none text-base-content/80">
                @if($host->about)
                    {!! $host->about !!}
                @else
                    <p class="text-base-content/50 italic">No description set. Click Edit to add a description.</p>
                @endif
            </div>

            {{-- Edit Mode --}}
            <div id="about-edit-container" class="hidden">
                <div id="about-editor" class="bg-base-100 border border-base-content/20 rounded-lg min-h-[200px]"></div>
                <p class="text-xs text-base-content/50 mt-2">This appears on your public booking page</p>
                <div class="flex items-center gap-2 mt-4">
                    <button type="button" class="btn btn-primary btn-sm" id="save-about-inline-btn" onclick="saveAbout()">
                        <span class="loading loading-spinner loading-xs hidden" id="about-inline-spinner"></span>
                        Save
                    </button>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="cancelAboutEdit()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Studio Gallery Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold">Studio Gallery</h2>
                    <p class="text-base-content/60 text-sm">Showcase your studio with photos (displays on booking page)</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openDrawer('upload-gallery-drawer')">
                    <span class="icon-[tabler--plus] size-4"></span> Add Image
                </button>
            </div>

            {{-- Gallery Grid --}}
            <div id="gallery-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($host->galleryImages as $image)
                <div class="gallery-item relative group aspect-video bg-base-200 rounded-lg overflow-hidden" data-id="{{ $image->id }}">
                    <img src="{{ $image->image_url }}" alt="{{ $image->caption ?? 'Gallery image' }}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <button type="button" class="btn btn-circle btn-sm btn-ghost text-white gallery-drag-handle cursor-move" title="Drag to reorder">
                            <span class="icon-[tabler--grip-vertical] size-4"></span>
                        </button>
                        <button type="button" class="btn btn-circle btn-sm btn-ghost text-white" onclick="editGalleryImage({{ $image->id }}, '{{ addslashes($image->caption ?? '') }}')" title="Edit caption">
                            <span class="icon-[tabler--edit] size-4"></span>
                        </button>
                        <button type="button" class="btn btn-circle btn-sm btn-ghost text-white hover:text-error" onclick="deleteGalleryImage({{ $image->id }})" title="Delete">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                    @if($image->caption)
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent px-2 py-1">
                        <p class="text-white text-xs truncate">{{ $image->caption }}</p>
                    </div>
                    @endif
                </div>
                @endforeach

                {{-- Add More Card - Always visible --}}
                <button type="button" id="gallery-add-more-btn" onclick="openDrawer('upload-gallery-drawer')" class="aspect-video bg-base-200 hover:bg-base-300 border-2 border-dashed border-base-content/20 hover:border-primary rounded-lg flex flex-col items-center justify-center gap-2 transition-colors cursor-pointer">
                    <span class="icon-[tabler--plus] size-8 text-base-content/40"></span>
                    <span class="text-sm text-base-content/50">Add Images</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Contact Information Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Contact Information</h2>
                    <p class="text-base-content/60 text-sm">Public and internal contact details</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('edit-contact-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> Edit
                </button>
            </div>

            <div class="space-y-4">
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Studio Email (Public)</label>
                    <p class="font-medium" id="display-studio-email">{{ $host->studio_email ?? 'Not set' }}</p>
                </div>
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Studio Phone (Public)</label>
                    <p class="font-medium" id="display-phone">{{ $host->phone ?? 'Not set' }}</p>
                </div>
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Contact Name (Internal)</label>
                    <p class="font-medium" id="display-contact-name">{{ $host->contact_name ?? 'Not set' }}</p>
                </div>
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Support Email (Automated)</label>
                    <p class="font-medium" id="display-support-email">{{ $host->support_email ?? 'Not set' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Social Links Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Social Links</h2>
                    <p class="text-base-content/60 text-sm">Connect your social media profiles</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('edit-social-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> Edit
                </button>
            </div>

            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--brand-instagram] size-5 text-pink-500"></span>
                    <span id="display-instagram" class="text-sm">{{ $host->social_links['instagram'] ?? 'Not connected' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--brand-facebook] size-5 text-blue-600"></span>
                    <span id="display-facebook" class="text-sm">{{ $host->social_links['facebook'] ?? 'Not connected' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--world] size-5 text-base-content/70"></span>
                    <span id="display-website" class="text-sm">{{ $host->social_links['website'] ?? 'Not connected' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--brand-tiktok] size-5 text-base-content"></span>
                    <span id="display-tiktok" class="text-sm">{{ $host->social_links['tiktok'] ?? 'Not connected' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Amenities Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Amenities</h2>
                    <p class="text-base-content/60 text-sm">Facilities available at your studio</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('edit-amenities-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> Edit
                </button>
            </div>

            <div class="flex flex-wrap gap-2" id="display-amenities">
                @if($host->amenities && count($host->amenities) > 0)
                    @foreach($host->amenities as $amenity)
                        <span class="badge badge-soft badge-sm">{{ $amenity }}</span>
                    @endforeach
                @else
                    <span class="text-base-content/50 text-sm">No amenities selected</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Currency Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Business Currencies</h2>
                    <p class="text-base-content/60 text-sm">Currencies accepted for pricing and transactions</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('edit-currency-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> Edit
                </button>
            </div>

            <div class="flex flex-wrap gap-2" id="display-currencies">
                @if($host->currencies && count($host->currencies) > 0)
                    @foreach($host->currencies as $code)
                        @if(isset($currencies[$code]))
                            <div class="flex items-center gap-2 px-3 py-2 bg-base-200 rounded-lg">
                                <span class="text-lg font-bold text-primary">{{ $currencies[$code]['symbol'] }}</span>
                                <span class="text-sm font-medium">{{ $code }}</span>
                                <span class="text-xs text-base-content/60">{{ $currencies[$code]['name'] }}</span>
                            </div>
                        @endif
                    @endforeach
                @else
                    <span class="text-base-content/50 text-sm">No currencies selected</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Booking Cancellation Policy Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Booking Cancellation Policy</h2>
                    <p class="text-base-content/60 text-sm">How far in advance clients must cancel bookings</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('edit-cancellation-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> Edit
                </button>
            </div>

            @php
                $cancellationHours = $host->getPolicy('cancellation_window_hours', 12);
                $allowCancellations = $host->getPolicy('allow_cancellations', true);
                $cancellationOptions = [
                    0 => 'No advance notice required',
                    2 => '2 hours before class',
                    6 => '6 hours before class',
                    12 => '12 hours before class',
                    24 => '24 hours before class',
                    48 => '2 days before class',
                    72 => '3 days before class',
                ];
            @endphp

            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-warning/20 flex items-center justify-center">
                    <span class="icon-[tabler--clock-cancel] size-7 text-warning"></span>
                </div>
                <div>
                    @if(!$allowCancellations)
                        <div class="font-semibold text-error">Cancellations Disabled</div>
                        <p class="text-sm text-base-content/60">Clients cannot cancel their bookings</p>
                    @else
                        <div class="font-semibold" id="display-cancellation-window">{{ $cancellationOptions[$cancellationHours] ?? $cancellationHours . ' hours before class' }}</div>
                        <p class="text-sm text-base-content/60">Clients must cancel at least this far in advance</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Drawer Backdrop --}}
<div id="drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none transition-opacity duration-300" onclick="closeAllDrawers()"></div>

{{-- Edit Basic Info Drawer --}}
<div id="edit-basic-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Edit Basic Information</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-basic-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="edit-basic-form" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4">
            <div class="space-y-4">
                <div>
                    <label class="label-text" for="studio_name">Studio Name <span class="text-error">*</span></label>
                    <input id="studio_name" type="text" class="input w-full" value="{{ $host->studio_name ?? '' }}" required />
                </div>
                <div>
                    <label class="label-text" for="short_description">Short Description</label>
                    <input id="short_description" type="text" class="input w-full" value="{{ $host->short_description ?? '' }}" maxlength="200" placeholder="A brief tagline for your studio" />
                    <p class="text-xs text-base-content/50 mt-1">Shown in the hero section of your booking page (max 200 characters)</p>
                </div>
                <div>
                    <label class="label-text" for="subdomain">Subdomain</label>
                    <div class="join w-full">
                        <input id="subdomain" type="text" class="input join-item flex-1 input-disabled bg-base-200 cursor-not-allowed" value="{{ $host->subdomain ?? '' }}" readonly />
                        <span class="btn btn-soft join-item pointer-events-none">.{{ config('app.booking_domain', 'fitcrm.biz') }}</span>
                    </div>
                    <p class="text-xs text-base-content/50 mt-1">Subdomain cannot be changed after setup</p>
                </div>
                <div>
                    <label class="label-text">Studio Types <span class="text-error">*</span></label>
                    <p class="text-xs text-base-content/50 mb-2">Select all that apply</p>
                    <div id="studio-types-select" class="relative">
                        <button type="button" id="types-toggle" class="advance-select-toggle w-full" onclick="toggleTypesDropdown()">
                            <span id="types-placeholder" class="text-base-content/50 hidden">Select studio types...</span>
                            <span id="types-badges" class="flex flex-wrap gap-1 pe-6"></span>
                            <span class="icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content absolute top-1/2 end-3 -translate-y-1/2"></span>
                        </button>
                        <div id="types-dropdown" class="advance-select-menu max-h-48 overflow-y-auto absolute z-50 w-full mt-1 hidden">
                            @foreach($studioTypesList as $type)
                            <div class="advance-select-option cursor-pointer" data-type="{{ $type }}" onclick="toggleTypeOption('{{ $type }}')">
                                <div class="flex justify-between items-center flex-1">
                                    <span>{{ $type }}</span>
                                    <span class="type-check icon-[tabler--check] shrink-0 size-4 text-primary hidden"></span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div>
                    <label class="label-text" for="timezone">Timezone <span class="text-error">*</span></label>
                    <select id="timezone" class="select w-full">
                        <option value="America/New_York" {{ ($host->timezone ?? '') == 'America/New_York' ? 'selected' : '' }}>Eastern (ET)</option>
                        <option value="America/Chicago" {{ ($host->timezone ?? '') == 'America/Chicago' ? 'selected' : '' }}>Central (CT)</option>
                        <option value="America/Denver" {{ ($host->timezone ?? '') == 'America/Denver' ? 'selected' : '' }}>Mountain (MT)</option>
                        <option value="America/Los_Angeles" {{ ($host->timezone ?? '') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific (PT)</option>
                        <option value="America/Phoenix" {{ ($host->timezone ?? '') == 'America/Phoenix' ? 'selected' : '' }}>Arizona (AZ)</option>
                        <option value="Pacific/Honolulu" {{ ($host->timezone ?? '') == 'Pacific/Honolulu' ? 'selected' : '' }}>Hawaii (HT)</option>
                        <option value="America/Anchorage" {{ ($host->timezone ?? '') == 'America/Anchorage' ? 'selected' : '' }}>Alaska (AKT)</option>
                        <option value="Europe/London" {{ ($host->timezone ?? '') == 'Europe/London' ? 'selected' : '' }}>London (GMT)</option>
                        <option value="Europe/Paris" {{ ($host->timezone ?? '') == 'Europe/Paris' ? 'selected' : '' }}>Paris (CET)</option>
                        <option value="Australia/Sydney" {{ ($host->timezone ?? '') == 'Australia/Sydney' ? 'selected' : '' }}>Sydney (AEST)</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-basic-btn">
                <span class="loading loading-spinner loading-xs hidden" id="basic-spinner"></span>
                Save Changes
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('edit-basic-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Upload Logo Drawer --}}
<div id="upload-logo-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Upload Studio Logo</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('upload-logo-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="upload-logo-form" class="flex flex-col flex-1 overflow-hidden" enctype="multipart/form-data">
        <div class="flex-1 overflow-y-auto p-4">
            <div class="flex flex-col items-center justify-center border-2 border-dashed border-base-content/20 rounded-lg p-8 hover:border-primary transition-colors cursor-pointer" id="logo-drop-zone">
                <input type="file" id="logo-input" name="logo" class="hidden" accept="image/png,image/jpeg,image/webp" />
                <div id="logo-upload-placeholder" class="text-center">
                    <span class="icon-[tabler--cloud-upload] size-12 text-base-content/30 mb-2 block mx-auto"></span>
                    <p class="text-sm text-base-content/60">Drag and drop your logo here, or</p>
                    <button type="button" class="btn btn-soft btn-sm mt-2" id="logo-browse-btn">Browse Files</button>
                </div>
                <div id="logo-upload-preview" class="hidden text-center">
                    <img id="logo-preview-image" src="" alt="Preview" class="w-32 h-32 object-contain rounded-lg mb-2 mx-auto" />
                    <p id="logo-file-name" class="text-sm text-base-content/60"></p>
                    <button type="button" class="btn btn-ghost btn-xs mt-2" id="logo-remove-preview-btn">
                        <span class="icon-[tabler--x] size-4"></span> Remove
                    </button>
                </div>
            </div>
            <p class="text-xs text-base-content/50 text-center mt-4">PNG, JPG, or WebP. Max 5MB. Recommended 400x400px.</p>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="upload-logo-btn" disabled>
                <span class="loading loading-spinner loading-xs hidden" id="logo-spinner"></span>
                Upload Logo
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('upload-logo-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Upload Cover Drawer --}}
<div id="upload-cover-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Upload Cover Image</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('upload-cover-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="upload-cover-form" class="flex flex-col flex-1 overflow-hidden" enctype="multipart/form-data">
        <div class="flex-1 overflow-y-auto p-4">
            <div class="flex flex-col items-center justify-center border-2 border-dashed border-base-content/20 rounded-lg p-8 hover:border-primary transition-colors cursor-pointer" id="cover-drop-zone">
                <input type="file" id="cover-input" name="cover" class="hidden" accept="image/png,image/jpeg,image/webp" />
                <div id="cover-upload-placeholder" class="text-center">
                    <span class="icon-[tabler--cloud-upload] size-12 text-base-content/30 mb-2 block mx-auto"></span>
                    <p class="text-sm text-base-content/60">Drag and drop your cover image here, or</p>
                    <button type="button" class="btn btn-soft btn-sm mt-2" id="cover-browse-btn">Browse Files</button>
                </div>
                <div id="cover-upload-preview" class="hidden text-center">
                    <img id="cover-preview-image" src="" alt="Preview" class="w-full h-32 object-cover rounded-lg mb-2 mx-auto" />
                    <p id="cover-file-name" class="text-sm text-base-content/60"></p>
                    <button type="button" class="btn btn-ghost btn-xs mt-2" id="cover-remove-preview-btn">
                        <span class="icon-[tabler--x] size-4"></span> Remove
                    </button>
                </div>
            </div>
            <p class="text-xs text-base-content/50 text-center mt-4">PNG, JPG, or WebP. Max 5MB. Recommended 1200x400px.</p>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="upload-cover-btn" disabled>
                <span class="loading loading-spinner loading-xs hidden" id="cover-spinner"></span>
                Upload Cover
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('upload-cover-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Edit Contact Drawer --}}
<div id="edit-contact-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Edit Contact Information</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-contact-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="edit-contact-form" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4">
            <div class="space-y-4">
                <div class="alert alert-soft alert-info">
                    <span class="icon-[tabler--info-circle] size-5"></span>
                    <span class="text-sm">Public contact info is shown on your booking page.</span>
                </div>
                <div>
                    <label class="label-text" for="studio_email">Studio Email (Public)</label>
                    <input id="studio_email" type="email" class="input w-full" value="{{ $host->studio_email ?? '' }}" placeholder="hello@yourstudio.com" />
                </div>
                <div>
                    <label class="label-text" for="phone">Studio Phone (Public)</label>
                    <input id="phone" type="tel" class="input w-full" value="{{ $host->phone ?? '' }}" placeholder="(555) 123-4567" />
                </div>
                <div class="divider text-xs text-base-content/50">Internal Use Only</div>
                <div>
                    <label class="label-text" for="contact_name">Contact Name</label>
                    <input id="contact_name" type="text" class="input w-full" value="{{ $host->contact_name ?? '' }}" placeholder="Studio Manager" />
                    <p class="text-xs text-base-content/50 mt-1">For internal reference only</p>
                </div>
                <div>
                    <label class="label-text" for="support_email">Support Email</label>
                    <input id="support_email" type="email" class="input w-full" value="{{ $host->support_email ?? '' }}" placeholder="support@yourstudio.com" />
                    <p class="text-xs text-base-content/50 mt-1">Used for automated email replies</p>
                </div>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-contact-btn">
                <span class="loading loading-spinner loading-xs hidden" id="contact-spinner"></span>
                Save Changes
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('edit-contact-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Edit Social Links Drawer --}}
<div id="edit-social-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Edit Social Links</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-social-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="edit-social-form" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4">
            <div class="space-y-4">
                <div>
                    <label class="label-text flex items-center gap-2" for="social_instagram">
                        <span class="icon-[tabler--brand-instagram] size-4 text-pink-500"></span> Instagram
                    </label>
                    <input id="social_instagram" type="url" class="input w-full" value="{{ $host->social_links['instagram'] ?? '' }}" placeholder="https://instagram.com/yourstudio" />
                </div>
                <div>
                    <label class="label-text flex items-center gap-2" for="social_facebook">
                        <span class="icon-[tabler--brand-facebook] size-4 text-blue-600"></span> Facebook
                    </label>
                    <input id="social_facebook" type="url" class="input w-full" value="{{ $host->social_links['facebook'] ?? '' }}" placeholder="https://facebook.com/yourstudio" />
                </div>
                <div>
                    <label class="label-text flex items-center gap-2" for="social_website">
                        <span class="icon-[tabler--world] size-4 text-base-content/70"></span> Website
                    </label>
                    <input id="social_website" type="url" class="input w-full" value="{{ $host->social_links['website'] ?? '' }}" placeholder="https://yourstudio.com" />
                </div>
                <div>
                    <label class="label-text flex items-center gap-2" for="social_tiktok">
                        <span class="icon-[tabler--brand-tiktok] size-4"></span> TikTok
                    </label>
                    <input id="social_tiktok" type="url" class="input w-full" value="{{ $host->social_links['tiktok'] ?? '' }}" placeholder="https://tiktok.com/@yourstudio" />
                </div>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-social-btn">
                <span class="loading loading-spinner loading-xs hidden" id="social-spinner"></span>
                Save Changes
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('edit-social-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Edit Amenities Drawer --}}
<div id="edit-amenities-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Edit Amenities</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-amenities-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="edit-amenities-form" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4">
            <p class="text-sm text-base-content/60 mb-4">Select all amenities available at your studio</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach($amenitiesList as $amenity)
                <label class="custom-option flex flex-row items-center gap-2 px-3 py-2 cursor-pointer">
                    <input type="checkbox" class="checkbox checkbox-primary checkbox-sm amenity-checkbox" value="{{ $amenity }}" {{ in_array($amenity, $host->amenities ?? []) ? 'checked' : '' }} />
                    <span class="label-text text-sm">{{ $amenity }}</span>
                </label>
                @endforeach
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-amenities-btn">
                <span class="loading loading-spinner loading-xs hidden" id="amenities-spinner"></span>
                Save Changes
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('edit-amenities-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Edit Currency Drawer --}}
<div id="edit-currency-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Business Currencies</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-currency-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="edit-currency-form" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4">
            <p class="text-sm text-base-content/60 mb-4">Select all currencies you accept for payments</p>
            <div class="space-y-2">
                @foreach($currencies as $code => $info)
                <label class="custom-option flex flex-row items-center gap-3 px-3 py-2 cursor-pointer">
                    <input type="checkbox" class="checkbox checkbox-primary checkbox-sm currency-checkbox" value="{{ $code }}" {{ in_array($code, $host->currencies ?? []) ? 'checked' : '' }} />
                    <span class="text-lg font-bold text-primary w-6">{{ $info['symbol'] }}</span>
                    <span class="label-text text-sm font-medium">{{ $code }}</span>
                    <span class="label-text text-sm text-base-content/60">{{ $info['name'] }}</span>
                </label>
                @endforeach
            </div>
            <div class="alert alert-soft alert-info mt-4">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <div class="text-sm">
                    Selected currencies will be available for pricing on your booking page.
                </div>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-currency-btn">
                <span class="loading loading-spinner loading-xs hidden" id="currency-spinner"></span>
                Save Changes
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('edit-currency-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Edit Cancellation Policy Drawer --}}
<div id="edit-cancellation-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Booking Cancellation Policy</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-cancellation-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="edit-cancellation-form" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4">
            <div class="space-y-5">
                {{-- Allow Cancellations Toggle --}}
                <div class="flex items-center justify-between p-4 bg-base-200/50 rounded-lg">
                    <div>
                        <label class="label-text font-medium" for="allow_cancellations">Allow Cancellations</label>
                        <p class="text-xs text-base-content/60 mt-1">Enable clients to cancel their bookings</p>
                    </div>
                    <input type="checkbox" id="allow_cancellations" class="toggle toggle-primary" {{ $host->getPolicy('allow_cancellations', true) ? 'checked' : '' }} />
                </div>

                {{-- Cancellation Window --}}
                <div id="cancellation-window-section">
                    <label class="label-text font-medium" for="cancellation_window_hours">Cancellation Window</label>
                    <p class="text-xs text-base-content/60 mb-3">How far in advance clients must cancel</p>
                    <div class="space-y-2">
                        @php
                            $currentWindow = $host->getPolicy('cancellation_window_hours', 12);
                            $windowOptions = [
                                ['value' => 0, 'label' => 'No advance notice required', 'desc' => 'Clients can cancel anytime'],
                                ['value' => 2, 'label' => '2 hours before', 'desc' => 'Short notice cancellation'],
                                ['value' => 6, 'label' => '6 hours before', 'desc' => 'Same day cancellation'],
                                ['value' => 12, 'label' => '12 hours before', 'desc' => 'Half day notice'],
                                ['value' => 24, 'label' => '24 hours before', 'desc' => '1 day notice'],
                                ['value' => 48, 'label' => '2 days before', 'desc' => '48 hours notice'],
                                ['value' => 72, 'label' => '3 days before', 'desc' => '72 hours notice'],
                            ];
                        @endphp
                        @foreach($windowOptions as $option)
                        <label class="custom-option flex flex-row items-center gap-3 px-4 py-3 cursor-pointer rounded-lg border border-base-300 hover:border-primary/50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="cancellation_window_hours" class="radio radio-primary radio-sm" value="{{ $option['value'] }}" {{ $currentWindow == $option['value'] ? 'checked' : '' }} />
                            <div class="flex-1">
                                <span class="label-text font-medium">{{ $option['label'] }}</span>
                                <p class="text-xs text-base-content/60">{{ $option['desc'] }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="alert alert-soft alert-warning">
                    <span class="icon-[tabler--alert-triangle] size-5"></span>
                    <div class="text-sm">
                        Cancellations made after this window may be marked as late cancellations.
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-cancellation-btn">
                <span class="loading loading-spinner loading-xs hidden" id="cancellation-spinner"></span>
                Save Changes
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('edit-cancellation-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Upload Gallery Image Drawer --}}
<div id="upload-gallery-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Add Gallery Image</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('upload-gallery-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="upload-gallery-form" class="flex flex-col flex-1 overflow-hidden" enctype="multipart/form-data">
        <div class="flex-1 overflow-y-auto p-4">
            <div class="flex flex-col items-center justify-center border-2 border-dashed border-base-content/20 rounded-lg p-8 hover:border-primary transition-colors cursor-pointer" id="gallery-drop-zone">
                <input type="file" id="gallery-input" name="images[]" class="hidden" accept="image/png,image/jpeg,image/webp" multiple />
                <div id="gallery-upload-placeholder" class="text-center">
                    <span class="icon-[tabler--cloud-upload] size-12 text-base-content/30 mb-2 block mx-auto"></span>
                    <p class="text-sm text-base-content/60">Drag and drop images here, or</p>
                    <button type="button" class="btn btn-soft btn-sm mt-2" id="gallery-browse-btn">Browse Files</button>
                    <p class="text-xs text-base-content/40 mt-2">You can select multiple images</p>
                </div>
                <div id="gallery-upload-preview" class="hidden w-full">
                    <div id="gallery-preview-grid" class="grid grid-cols-3 gap-2 mb-3"></div>
                    <p id="gallery-file-count" class="text-sm text-base-content/60 text-center"></p>
                    <button type="button" class="btn btn-ghost btn-xs mt-2 mx-auto block" id="gallery-remove-preview-btn">
                        <span class="icon-[tabler--x] size-4"></span> Clear All
                    </button>
                </div>
            </div>
            <p class="text-xs text-base-content/50 text-center mt-4">PNG, JPG, or WebP. Max 5MB each.</p>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="upload-gallery-btn" disabled>
                <span class="loading loading-spinner loading-xs hidden" id="gallery-spinner"></span>
                Upload Image
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('upload-gallery-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Edit Gallery Caption Modal --}}
<dialog id="edit-gallery-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Edit Caption</h3>
        <form id="edit-gallery-form">
            <input type="hidden" id="edit-gallery-id" value="" />
            <div class="form-control">
                <label class="label" for="edit-gallery-caption">
                    <span class="label-text">Caption</span>
                </label>
                <input type="text" id="edit-gallery-caption" class="input input-bordered w-full" placeholder="Describe this image..." maxlength="255" />
            </div>
            <div class="modal-action">
                <button type="submit" class="btn btn-primary">
                    <span class="loading loading-spinner loading-xs hidden" id="edit-gallery-spinner"></span>
                    Save
                </button>
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('edit-gallery-modal').close()">Cancel</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

{{-- Delete Gallery Confirmation Modal --}}
<dialog id="delete-gallery-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Delete Image?</h3>
        <p class="py-4 text-base-content/70">This image will be permanently removed from your gallery. This action cannot be undone.</p>
        <input type="hidden" id="delete-gallery-id" value="" />
        <div class="modal-action">
            <button type="button" class="btn btn-error" id="confirm-delete-gallery-btn">
                <span class="loading loading-spinner loading-xs hidden" id="delete-gallery-spinner"></span>
                Delete
            </button>
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('delete-gallery-modal').close()">Cancel</button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
@endsection

@push('scripts')
<script src="{{ asset('vendor/quill/quill.js') }}"></script>
<script src="{{ asset('vendor/sortablejs/Sortable.min.js') }}"></script>
<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var selectedTypes = {!! json_encode($host->studio_types ?? []) !!};
var typesDropdownOpen = false;
var currencies = @json($currencies);

// Toast function
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

// Drawer functions
function openDrawer(id) {
    var drawer = document.getElementById(id);
    var backdrop = document.getElementById('drawer-backdrop');
    if (drawer && backdrop) {
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
        backdrop.classList.add('opacity-100', 'pointer-events-auto');
        drawer.classList.remove('translate-x-full');
        drawer.classList.add('translate-x-0');
        document.body.style.overflow = 'hidden';
    }
}

function closeDrawer(id) {
    var drawer = document.getElementById(id);
    var backdrop = document.getElementById('drawer-backdrop');
    if (drawer && backdrop) {
        drawer.classList.remove('translate-x-0');
        drawer.classList.add('translate-x-full');
        backdrop.classList.remove('opacity-100', 'pointer-events-auto');
        backdrop.classList.add('opacity-0', 'pointer-events-none');
        document.body.style.overflow = '';
    }
}

function closeAllDrawers() {
    var drawers = ['edit-basic-drawer', 'upload-logo-drawer', 'upload-cover-drawer', 'edit-contact-drawer', 'edit-social-drawer', 'edit-amenities-drawer', 'edit-currency-drawer', 'edit-cancellation-drawer', 'upload-gallery-drawer'];
    drawers.forEach(function(id) {
        var drawer = document.getElementById(id);
        if (drawer) {
            drawer.classList.remove('translate-x-0');
            drawer.classList.add('translate-x-full');
        }
    });
    var backdrop = document.getElementById('drawer-backdrop');
    if (backdrop) {
        backdrop.classList.remove('opacity-100', 'pointer-events-auto');
        backdrop.classList.add('opacity-0', 'pointer-events-none');
    }
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAllDrawers();
});

// Studio Types Multi-select
function toggleTypesDropdown() {
    typesDropdownOpen = !typesDropdownOpen;
    document.getElementById('types-dropdown').classList.toggle('hidden', !typesDropdownOpen);
}

function toggleTypeOption(type) {
    var index = selectedTypes.indexOf(type);
    if (index === -1) selectedTypes.push(type);
    else selectedTypes.splice(index, 1);
    updateTypesDisplay();
}

function removeType(type, event) {
    event.stopPropagation();
    var index = selectedTypes.indexOf(type);
    if (index !== -1) selectedTypes.splice(index, 1);
    updateTypesDisplay();
}

function updateTypesDisplay() {
    var badgesContainer = document.getElementById('types-badges');
    var placeholder = document.getElementById('types-placeholder');

    if (selectedTypes.length === 0) {
        placeholder.classList.remove('hidden');
        badgesContainer.innerHTML = '';
    } else {
        placeholder.classList.add('hidden');
        var html = selectedTypes.slice(0, 3).map(function(type) {
            return '<span class="badge badge-soft badge-primary badge-sm gap-1">' + type + '<button type="button" class="hover:text-error" onclick="removeType(\'' + type + '\', event)"><span class="icon-[tabler--x] size-3"></span></button></span>';
        }).join('');
        if (selectedTypes.length > 3) html += '<span class="badge badge-soft badge-neutral badge-sm">+' + (selectedTypes.length - 3) + '</span>';
        badgesContainer.innerHTML = html;
    }

    document.querySelectorAll('#types-dropdown .advance-select-option').forEach(function(option) {
        var type = option.dataset.type;
        var check = option.querySelector('.type-check');
        var isSelected = selectedTypes.includes(type);
        option.classList.toggle('select-active', isSelected);
        check.classList.toggle('hidden', !isSelected);
    });
}

document.addEventListener('click', function(e) {
    var selectContainer = document.getElementById('studio-types-select');
    if (selectContainer && !selectContainer.contains(e.target)) {
        typesDropdownOpen = false;
        document.getElementById('types-dropdown').classList.add('hidden');
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateTypesDisplay();
    initImageUpload('logo');
    initImageUpload('cover');
});

// Image upload handler
function initImageUpload(type) {
    var dropZone = document.getElementById(type + '-drop-zone');
    var input = document.getElementById(type + '-input');
    var browseBtn = document.getElementById(type + '-browse-btn');
    var removeBtn = document.getElementById(type + '-remove-preview-btn');
    var placeholder = document.getElementById(type + '-upload-placeholder');
    var preview = document.getElementById(type + '-upload-preview');
    var previewImg = document.getElementById(type + '-preview-image');
    var fileName = document.getElementById(type + '-file-name');
    var uploadBtn = document.getElementById('upload-' + type + '-btn');

    browseBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); input.click(); });
    removeBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); clearPreview(); });
    dropZone.addEventListener('click', function(e) { if (!e.target.closest('button')) input.click(); });
    dropZone.addEventListener('dragover', function(e) { e.preventDefault(); dropZone.classList.add('border-primary', 'bg-primary/5'); });
    dropZone.addEventListener('dragleave', function(e) { e.preventDefault(); dropZone.classList.remove('border-primary', 'bg-primary/5'); });
    dropZone.addEventListener('drop', function(e) { e.preventDefault(); dropZone.classList.remove('border-primary', 'bg-primary/5'); if (e.dataTransfer.files.length > 0) handleFile(e.dataTransfer.files[0]); });
    input.addEventListener('change', function() { if (this.files.length > 0) handleFile(this.files[0]); });

    function handleFile(file) {
        if (!['image/png', 'image/jpeg', 'image/webp'].includes(file.type)) { showToast('Please upload PNG, JPG, or WebP', 'error'); return; }
        if (file.size > 5 * 1024 * 1024) { showToast('File must be under 5MB', 'error'); return; }
        var reader = new FileReader();
        reader.onload = function(e) { previewImg.src = e.target.result; fileName.textContent = file.name; placeholder.classList.add('hidden'); preview.classList.remove('hidden'); uploadBtn.disabled = false; };
        reader.readAsDataURL(file);
    }

    function clearPreview() { input.value = ''; previewImg.src = ''; fileName.textContent = ''; placeholder.classList.remove('hidden'); preview.classList.add('hidden'); uploadBtn.disabled = true; }
}

// Form submissions
document.getElementById('edit-basic-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-basic-btn');
    var spinner = document.getElementById('basic-spinner');
    btn.disabled = true; spinner.classList.remove('hidden');

    fetch('{{ route("settings.studio.profile.update") }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
            studio_name: document.getElementById('studio_name').value,
            short_description: document.getElementById('short_description').value,
            timezone: document.getElementById('timezone').value,
            studio_types: selectedTypes
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            document.getElementById('display-studio-name').textContent = document.getElementById('studio_name').value || 'Not set';
            document.getElementById('display-short-description').textContent = document.getElementById('short_description').value || 'Not set';
            document.getElementById('display-timezone').textContent = document.getElementById('timezone').value || 'Not set';
            var typesHtml = selectedTypes.length > 0 ? selectedTypes.map(function(t) { return '<span class="badge badge-primary badge-soft badge-sm">' + t + '</span>'; }).join('') : '<span class="text-base-content/50">Not set</span>';
            document.getElementById('display-types').innerHTML = typesHtml;
            closeDrawer('edit-basic-drawer');
            setTimeout(function() { showToast('Basic information updated!'); }, 350);
        } else { showToast(result.message || 'Failed to update', 'error'); }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

document.getElementById('edit-contact-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-contact-btn');
    var spinner = document.getElementById('contact-spinner');
    btn.disabled = true; spinner.classList.remove('hidden');

    fetch('{{ route("settings.studio.contact.update") }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
            studio_email: document.getElementById('studio_email').value,
            phone: document.getElementById('phone').value,
            contact_name: document.getElementById('contact_name').value,
            support_email: document.getElementById('support_email').value
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            document.getElementById('display-studio-email').textContent = document.getElementById('studio_email').value || 'Not set';
            document.getElementById('display-phone').textContent = document.getElementById('phone').value || 'Not set';
            document.getElementById('display-contact-name').textContent = document.getElementById('contact_name').value || 'Not set';
            document.getElementById('display-support-email').textContent = document.getElementById('support_email').value || 'Not set';
            closeDrawer('edit-contact-drawer');
            setTimeout(function() { showToast('Contact info updated!'); }, 350);
        } else { showToast(result.message || 'Failed to update', 'error'); }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

document.getElementById('edit-social-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-social-btn');
    var spinner = document.getElementById('social-spinner');
    btn.disabled = true; spinner.classList.remove('hidden');

    fetch('{{ route("settings.studio.social.update") }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
            social_links: {
                instagram: document.getElementById('social_instagram').value || null,
                facebook: document.getElementById('social_facebook').value || null,
                website: document.getElementById('social_website').value || null,
                tiktok: document.getElementById('social_tiktok').value || null
            }
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            document.getElementById('display-instagram').textContent = document.getElementById('social_instagram').value || 'Not connected';
            document.getElementById('display-facebook').textContent = document.getElementById('social_facebook').value || 'Not connected';
            document.getElementById('display-website').textContent = document.getElementById('social_website').value || 'Not connected';
            document.getElementById('display-tiktok').textContent = document.getElementById('social_tiktok').value || 'Not connected';
            closeDrawer('edit-social-drawer');
            setTimeout(function() { showToast('Social links updated!'); }, 350);
        } else { showToast(result.message || 'Failed to update', 'error'); }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

document.getElementById('edit-amenities-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-amenities-btn');
    var spinner = document.getElementById('amenities-spinner');
    btn.disabled = true; spinner.classList.remove('hidden');

    var amenities = [];
    document.querySelectorAll('.amenity-checkbox:checked').forEach(function(cb) { amenities.push(cb.value); });

    fetch('{{ route("settings.studio.amenities.update") }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ amenities: amenities })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            var amenitiesHtml = amenities.length > 0 ? amenities.map(function(a) { return '<span class="badge badge-soft badge-sm">' + a + '</span>'; }).join('') : '<span class="text-base-content/50 text-sm">No amenities selected</span>';
            document.getElementById('display-amenities').innerHTML = amenitiesHtml;
            closeDrawer('edit-amenities-drawer');
            setTimeout(function() { showToast('Amenities updated!'); }, 350);
        } else { showToast(result.message || 'Failed to update', 'error'); }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

document.getElementById('edit-currency-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-currency-btn');
    var spinner = document.getElementById('currency-spinner');
    btn.disabled = true; spinner.classList.remove('hidden');

    var selectedCurrencies = [];
    document.querySelectorAll('.currency-checkbox:checked').forEach(function(cb) { selectedCurrencies.push(cb.value); });

    fetch('{{ route("settings.studio.currency.update") }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ currencies: selectedCurrencies })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            // Update display
            var currenciesHtml = '';
            if (selectedCurrencies.length > 0) {
                currenciesHtml = selectedCurrencies.map(function(code) {
                    var info = currencies[code];
                    return '<div class="flex items-center gap-2 px-3 py-2 bg-base-200 rounded-lg">' +
                        '<span class="text-lg font-bold text-primary">' + info.symbol + '</span>' +
                        '<span class="text-sm font-medium">' + code + '</span>' +
                        '<span class="text-xs text-base-content/60">' + info.name + '</span>' +
                    '</div>';
                }).join('');
            } else {
                currenciesHtml = '<span class="text-base-content/50 text-sm">No currencies selected</span>';
            }
            document.getElementById('display-currencies').innerHTML = currenciesHtml;
            closeDrawer('edit-currency-drawer');
            setTimeout(function() { showToast('Currencies updated!'); }, 350);
        } else { showToast(result.message || 'Failed to update', 'error'); }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

// Cancellation Policy
var cancellationOptions = {
    0: 'No advance notice required',
    2: '2 hours before class',
    6: '6 hours before class',
    12: '12 hours before class',
    24: '24 hours before class',
    48: '2 days before class',
    72: '3 days before class'
};

// Toggle cancellation window section based on allow_cancellations
document.getElementById('allow_cancellations').addEventListener('change', function() {
    var section = document.getElementById('cancellation-window-section');
    if (this.checked) {
        section.classList.remove('opacity-50', 'pointer-events-none');
    } else {
        section.classList.add('opacity-50', 'pointer-events-none');
    }
});

document.getElementById('edit-cancellation-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-cancellation-btn');
    var spinner = document.getElementById('cancellation-spinner');
    btn.disabled = true; spinner.classList.remove('hidden');

    var allowCancellations = document.getElementById('allow_cancellations').checked;
    var selectedRadio = document.querySelector('input[name="cancellation_window_hours"]:checked');
    var windowHours = selectedRadio ? parseInt(selectedRadio.value) : 12;

    fetch('{{ route("settings.studio.cancellation.update") }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
            allow_cancellations: allowCancellations,
            cancellation_window_hours: windowHours
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            var displayEl = document.getElementById('display-cancellation-window');
            if (displayEl) {
                displayEl.textContent = cancellationOptions[windowHours] || windowHours + ' hours before class';
            }
            closeDrawer('edit-cancellation-drawer');
            setTimeout(function() { showToast('Cancellation policy updated!'); }, 350);
        } else { showToast(result.message || 'Failed to update', 'error'); }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

// Image upload forms
document.getElementById('upload-logo-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('upload-logo-btn');
    var spinner = document.getElementById('logo-spinner');
    btn.disabled = true; spinner.classList.remove('hidden');

    var formData = new FormData();
    formData.append('logo', document.getElementById('logo-input').files[0]);

    fetch('{{ route("settings.studio.logo.upload") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            document.getElementById('logo-preview').innerHTML = '<img src="' + result.logo_url + '" alt="Studio Logo" class="w-full h-full object-cover" />';
            closeDrawer('upload-logo-drawer');
            setTimeout(function() { showToast('Logo uploaded!'); }, 350);
        } else { showToast(result.message || 'Failed to upload', 'error'); }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

document.getElementById('upload-cover-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('upload-cover-btn');
    var spinner = document.getElementById('cover-spinner');
    btn.disabled = true; spinner.classList.remove('hidden');

    var formData = new FormData();
    formData.append('cover', document.getElementById('cover-input').files[0]);

    fetch('{{ route("settings.studio.cover.upload") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            document.getElementById('cover-preview').innerHTML = '<img src="' + result.cover_url + '" alt="Cover Image" class="w-full h-full object-cover" />';
            closeDrawer('upload-cover-drawer');
            setTimeout(function() { showToast('Cover image uploaded!'); }, 350);
        } else { showToast(result.message || 'Failed to upload', 'error'); }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

// About inline edit with Quill
var aboutQuill = null;
var originalAboutContent = '';

function toggleAboutEdit() {
    var display = document.getElementById('about-display');
    var editContainer = document.getElementById('about-edit-container');
    var editBtn = document.getElementById('about-edit-btn');

    display.classList.add('hidden');
    editContainer.classList.remove('hidden');
    editBtn.classList.add('hidden');

    // Initialize Quill if not already done
    if (!aboutQuill) {
        aboutQuill = new Quill('#about-editor', {
            theme: 'snow',
            placeholder: 'Tell students about your studio...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link'],
                    ['clean']
                ]
            }
        });
    }

    // Store original content and set editor content
    originalAboutContent = display.innerHTML;
    var content = display.querySelector('p.italic') ? '' : display.innerHTML;
    aboutQuill.root.innerHTML = content;
}

function cancelAboutEdit() {
    var display = document.getElementById('about-display');
    var editContainer = document.getElementById('about-edit-container');
    var editBtn = document.getElementById('about-edit-btn');

    editContainer.classList.add('hidden');
    display.classList.remove('hidden');
    editBtn.classList.remove('hidden');

    // Restore original content
    if (aboutQuill) {
        aboutQuill.root.innerHTML = originalAboutContent;
    }
}

function saveAbout() {
    var btn = document.getElementById('save-about-inline-btn');
    var spinner = document.getElementById('about-inline-spinner');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    var content = aboutQuill.root.innerHTML;
    // Check if content is empty or just whitespace
    if (content === '<p><br></p>' || content.trim() === '') {
        content = '';
    }

    fetch('{{ route("settings.studio.about.update") }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ about: content })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            var display = document.getElementById('about-display');
            var editContainer = document.getElementById('about-edit-container');
            var editBtn = document.getElementById('about-edit-btn');

            // Update display
            if (content) {
                display.innerHTML = content;
            } else {
                display.innerHTML = '<p class="text-base-content/50 italic">No description set. Click Edit to add a description.</p>';
            }

            // Hide edit mode
            editContainer.classList.add('hidden');
            display.classList.remove('hidden');
            editBtn.classList.remove('hidden');

            showToast('Description updated!');
        } else {
            showToast(result.message || 'Failed to update', 'error');
        }
    })
    .catch(function() {
        showToast('An error occurred', 'error');
    })
    .finally(function() {
        btn.disabled = false;
        spinner.classList.add('hidden');
    });
}

// ============================================
// Gallery Management
// ============================================

// Initialize gallery upload (multi-file)
(function() {
    var dropZone = document.getElementById('gallery-drop-zone');
    var input = document.getElementById('gallery-input');
    var browseBtn = document.getElementById('gallery-browse-btn');
    var removeBtn = document.getElementById('gallery-remove-preview-btn');
    var placeholder = document.getElementById('gallery-upload-placeholder');
    var preview = document.getElementById('gallery-upload-preview');
    var previewGrid = document.getElementById('gallery-preview-grid');
    var fileCount = document.getElementById('gallery-file-count');
    var uploadBtn = document.getElementById('upload-gallery-btn');

    if (!dropZone) return;

    browseBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); input.click(); });
    removeBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); clearGalleryPreview(); });
    dropZone.addEventListener('click', function(e) { if (!e.target.closest('button')) input.click(); });
    dropZone.addEventListener('dragover', function(e) { e.preventDefault(); dropZone.classList.add('border-primary', 'bg-primary/5'); });
    dropZone.addEventListener('dragleave', function(e) { e.preventDefault(); dropZone.classList.remove('border-primary', 'bg-primary/5'); });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-primary/5');
        if (e.dataTransfer.files.length > 0) handleGalleryFiles(e.dataTransfer.files);
    });
    input.addEventListener('change', function() { if (this.files.length > 0) handleGalleryFiles(this.files); });

    function handleGalleryFiles(files) {
        previewGrid.innerHTML = '';
        var validFiles = [];

        Array.from(files).forEach(function(file) {
            if (!['image/png', 'image/jpeg', 'image/webp'].includes(file.type)) {
                showToast('Skipped: ' + file.name + ' (invalid type)', 'error');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                showToast('Skipped: ' + file.name + ' (over 5MB)', 'error');
                return;
            }
            validFiles.push(file);

            var reader = new FileReader();
            reader.onload = function(e) {
                var div = document.createElement('div');
                div.className = 'aspect-square rounded-lg overflow-hidden bg-base-200';
                div.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover" />';
                previewGrid.appendChild(div);
            };
            reader.readAsDataURL(file);
        });

        if (validFiles.length > 0) {
            fileCount.textContent = validFiles.length + ' image' + (validFiles.length > 1 ? 's' : '') + ' selected';
            placeholder.classList.add('hidden');
            preview.classList.remove('hidden');
            uploadBtn.disabled = false;
        }
    }

    window.clearGalleryPreview = function() {
        input.value = '';
        previewGrid.innerHTML = '';
        fileCount.textContent = '';
        placeholder.classList.remove('hidden');
        preview.classList.add('hidden');
        uploadBtn.disabled = true;
    };
})();

// Upload gallery form (multi-file)
document.getElementById('upload-gallery-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('upload-gallery-btn');
    var spinner = document.getElementById('gallery-spinner');
    var files = document.getElementById('gallery-input').files;

    if (files.length === 0) return;

    btn.disabled = true;
    spinner.classList.remove('hidden');

    var formData = new FormData();
    Array.from(files).forEach(function(file, index) {
        formData.append('images[]', file);
    });

    fetch('{{ route("settings.studio.gallery.upload") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            // Hide empty state if present
            var emptyState = document.getElementById('gallery-empty');
            if (emptyState) emptyState.remove();

            // Add new images to grid (before the "Add More" button)
            var grid = document.getElementById('gallery-grid');
            var addMoreBtn = document.getElementById('gallery-add-more-btn');
            result.images.forEach(function(img) {
                var div = document.createElement('div');
                div.className = 'gallery-item relative group aspect-video bg-base-200 rounded-lg overflow-hidden';
                div.setAttribute('data-id', img.id);
                div.innerHTML = '<img src="' + img.image_url + '" alt="Gallery image" class="w-full h-full object-cover">' +
                    '<div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">' +
                    '<button type="button" class="btn btn-circle btn-sm btn-ghost text-white gallery-drag-handle cursor-move" title="Drag to reorder"><span class="icon-[tabler--grip-vertical] size-4"></span></button>' +
                    '<button type="button" class="btn btn-circle btn-sm btn-ghost text-white" onclick="editGalleryImage(' + img.id + ', \'\')" title="Edit caption"><span class="icon-[tabler--edit] size-4"></span></button>' +
                    '<button type="button" class="btn btn-circle btn-sm btn-ghost text-white hover:text-error" onclick="deleteGalleryImage(' + img.id + ')" title="Delete"><span class="icon-[tabler--trash] size-4"></span></button>' +
                    '</div>';
                grid.insertBefore(div, addMoreBtn);
            });

            // Re-initialize sortable
            initGallerySortable();

            clearGalleryPreview();
            closeDrawer('upload-gallery-drawer');
            setTimeout(function() { showToast(result.images.length + ' image' + (result.images.length > 1 ? 's' : '') + ' uploaded!'); }, 350);
        } else {
            showToast(result.message || 'Failed to upload', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

// Edit gallery image caption
function editGalleryImage(id, caption) {
    document.getElementById('edit-gallery-id').value = id;
    document.getElementById('edit-gallery-caption').value = caption;
    document.getElementById('edit-gallery-modal').showModal();
}

document.getElementById('edit-gallery-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = this.querySelector('button[type="submit"]');
    var spinner = document.getElementById('edit-gallery-spinner');
    var id = document.getElementById('edit-gallery-id').value;
    var caption = document.getElementById('edit-gallery-caption').value;

    btn.disabled = true;
    spinner.classList.remove('hidden');

    fetch('{{ url("settings/studio/gallery") }}/' + id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ caption: caption })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            // Update caption display
            var item = document.querySelector('.gallery-item[data-id="' + id + '"]');
            if (item) {
                var captionEl = item.querySelector('.absolute.bottom-0 p');
                if (caption) {
                    if (captionEl) {
                        captionEl.textContent = caption;
                    } else {
                        var captionDiv = document.createElement('div');
                        captionDiv.className = 'absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent px-2 py-1';
                        captionDiv.innerHTML = '<p class="text-white text-xs truncate">' + caption + '</p>';
                        item.appendChild(captionDiv);
                    }
                } else if (captionEl) {
                    captionEl.parentElement.remove();
                }
                // Update onclick handler
                var editBtn = item.querySelector('button[onclick^="editGalleryImage"]');
                if (editBtn) {
                    editBtn.setAttribute('onclick', 'editGalleryImage(' + id + ', \'' + caption.replace(/'/g, "\\'") + '\')');
                }
            }
            document.getElementById('edit-gallery-modal').close();
            showToast('Caption updated!');
        } else {
            showToast(result.message || 'Failed to update', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

// Delete gallery image
function deleteGalleryImage(id) {
    document.getElementById('delete-gallery-id').value = id;
    document.getElementById('delete-gallery-modal').showModal();
}

document.getElementById('confirm-delete-gallery-btn').addEventListener('click', function() {
    var btn = this;
    var spinner = document.getElementById('delete-gallery-spinner');
    var id = document.getElementById('delete-gallery-id').value;

    btn.disabled = true;
    spinner.classList.remove('hidden');

    fetch('{{ url("settings/studio/gallery") }}/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            var item = document.querySelector('.gallery-item[data-id="' + id + '"]');
            if (item) item.remove();

            document.getElementById('delete-gallery-modal').close();
            showToast('Image deleted!');
        } else {
            showToast(result.message || 'Failed to delete', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

// Gallery sortable (drag to reorder)
var gallerySortable = null;
function initGallerySortable() {
    var grid = document.getElementById('gallery-grid');
    if (!grid || typeof Sortable === 'undefined') return;

    if (gallerySortable) gallerySortable.destroy();

    gallerySortable = Sortable.create(grid, {
        animation: 150,
        handle: '.gallery-drag-handle',
        ghostClass: 'opacity-50',
        filter: '#gallery-add-more-btn',
        onEnd: function() {
            var order = [];
            grid.querySelectorAll('.gallery-item').forEach(function(item) {
                order.push(parseInt(item.dataset.id));
            });

            fetch('{{ route("settings.studio.gallery.reorder") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ order: order })
            })
            .then(function(r) { return r.json(); })
            .then(function(result) {
                if (!result.success) showToast('Failed to save order', 'error');
            })
            .catch(function() { showToast('Failed to save order', 'error'); });
        }
    });
}

// Initialize sortable on page load
document.addEventListener('DOMContentLoaded', function() {
    initGallerySortable();
});
</script>
@endpush
