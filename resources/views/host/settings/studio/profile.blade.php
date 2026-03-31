@extends('layouts.settings')

@section('title', $trans['settings.studio_profile_title'] ?? 'Studio Profile — Settings')

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
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> {{ $trans['nav.settings'] ?? 'Settings' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['settings.studio_profile'] ?? 'Studio Profile' }}</li>
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

$operatingCountriesList = [
    'US' => ['name' => 'United States', 'flag' => '🇺🇸'],
    'CA' => ['name' => 'Canada', 'flag' => '🇨🇦'],
    'DE' => ['name' => 'Germany', 'flag' => '🇩🇪'],
    'GB' => ['name' => 'United Kingdom', 'flag' => '🇬🇧'],
    'AU' => ['name' => 'Australia', 'flag' => '🇦🇺'],
    'IN' => ['name' => 'India', 'flag' => '🇮🇳'],
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
                    <h2 class="text-lg font-semibold">{{ $trans['settings.basic_info'] ?? 'Basic Information' }}</h2>
                    <p class="text-base-content/60 text-sm">{{ $trans['settings.basic_info_desc'] ?? 'Your studio name, type, and location' }}</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openDrawer('edit-basic-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> {{ $trans['btn.edit'] ?? 'Edit' }}
                </button>
            </div>

            {{-- Required Fields Notice --}}
            <div class="alert alert-info mb-4">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <span class="text-sm">{{ $trans['settings.required_fields_notice'] ?? 'Complete all required fields below to finish your studio setup.' }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Studio Name (Required) --}}
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60 flex items-center gap-1">
                        {{ $trans['settings.studio_name'] ?? 'Studio Name' }}
                        <span class="text-error">*</span>
                    </label>
                    <p class="font-medium" id="display-studio-name">{{ $host->studio_name ?? ($trans['settings.not_set'] ?? 'Not set') }}</p>
                </div>

                {{-- Studio Structure (Required) --}}
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60 flex items-center gap-1">
                        {{ $trans['settings.studio_structure'] ?? 'Studio Structure' }}
                        <span class="text-error">*</span>
                    </label>
                    <p class="font-medium" id="display-studio-structure">
                        @if($host->studio_structure === 'solo')
                            {{ $trans['settings.structure_solo'] ?? 'Solo (Just me)' }}
                        @elseif($host->studio_structure === 'team')
                            {{ $trans['settings.structure_team'] ?? 'With a Team (Staff members)' }}
                        @else
                            <span class="text-base-content/50">{{ $trans['settings.not_set'] ?? 'Not set' }}</span>
                        @endif
                    </p>
                </div>

                {{-- Subdomain (Required) --}}
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60 flex items-center gap-1">
                        {{ $trans['settings.subdomain'] ?? 'Sub-domain Name' }}
                        <span class="text-error">*</span>
                    </label>
                    <p class="font-medium" id="display-subdomain">{{ $host->subdomain ? $host->subdomain . '.' . config('app.booking_domain', 'fitcrm.biz') : ($trans['settings.not_set'] ?? 'Not set') }}</p>
                </div>

                {{-- Timezone --}}
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">{{ $trans['settings.timezone'] ?? 'Timezone' }}</label>
                    <p class="font-medium" id="display-timezone">{{ $host->timezone ?? ($trans['settings.not_set'] ?? 'Not set') }}</p>
                </div>
            </div>

            {{-- Optional: Short Description --}}
            <div class="mt-4 pt-4 border-t border-base-200">
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">{{ $trans['settings.short_description'] ?? 'Short Description' }}</label>
                    <p class="font-medium" id="display-short-description">{{ $host->short_description ?: ($trans['settings.not_set'] ?? 'Not set') }}</p>
                </div>
            </div>

            {{-- Location Reference --}}
            <div class="mt-4 pt-4 border-t border-base-200">
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">{{ $trans['settings.location'] ?? 'Location' }}</label>
                    @if($defaultLocation ?? null)
                        <p class="font-medium" id="display-location">{{ $defaultLocation->full_address }}</p>
                        <a href="{{ route('settings.locations.edit', $defaultLocation->id) }}" class="text-xs text-primary hover:underline">{{ $trans['settings.edit_in_location'] ?? 'Edit in Location Settings' }}</a>
                    @else
                        <p class="text-base-content/50" id="display-location">{{ $trans['settings.no_location_set'] ?? 'No location set' }}</p>
                        <a href="{{ route('settings.locations.create') }}" class="text-xs text-primary hover:underline">{{ $trans['settings.add_location'] ?? 'Add a location' }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Required Settings Quick Links --}}
    <div class="card bg-warning/5 border border-warning/20">
        <div class="card-body py-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="icon-[tabler--alert-triangle] size-5 text-warning"></span>
                <h3 class="font-semibold text-sm">{{ $trans['settings.required_settings'] ?? 'Complete These Required Settings' }}</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <button type="button" onclick="openDrawer('edit-categories-drawer')" class="flex items-center gap-2 p-3 rounded-lg bg-base-100 hover:bg-base-200 transition-colors border border-base-200 text-left">
                    <span class="icon-[tabler--category] size-5 text-primary"></span>
                    <div>
                        <p class="text-sm font-medium">{{ $trans['settings.studio_categories'] ?? 'Studio Categories' }}</p>
                        @php $hasCategories = !empty($host->studio_categories); @endphp
                        <p class="text-xs text-base-content/50">{{ $hasCategories ? 'Configured' : 'Required' }}</p>
                    </div>
                    @if($hasCategories)
                        <span class="icon-[tabler--check] size-4 text-success ml-auto"></span>
                    @else
                        <span class="icon-[tabler--alert-circle] size-4 text-warning ml-auto"></span>
                    @endif
                </button>
                <button type="button" onclick="openDrawer('edit-language-drawer')" class="flex items-center gap-2 p-3 rounded-lg bg-base-100 hover:bg-base-200 transition-colors border border-base-200 text-left">
                    <span class="icon-[tabler--language] size-5 text-primary"></span>
                    <div>
                        <p class="text-sm font-medium">{{ $trans['settings.language_settings'] ?? 'Language Settings' }}</p>
                        <p class="text-xs text-base-content/50">{{ $host->default_language_app ? 'Configured' : 'Required' }}</p>
                    </div>
                    @if($host->default_language_app)
                        <span class="icon-[tabler--check] size-4 text-success ml-auto"></span>
                    @else
                        <span class="icon-[tabler--alert-circle] size-4 text-warning ml-auto"></span>
                    @endif
                </button>
                <button type="button" onclick="openDrawer('edit-currency-drawer')" class="flex items-center gap-2 p-3 rounded-lg bg-base-100 hover:bg-base-200 transition-colors border border-base-200 text-left">
                    <span class="icon-[tabler--currency-dollar] size-5 text-primary"></span>
                    <div>
                        <p class="text-sm font-medium">{{ $trans['settings.business_currencies'] ?? 'Currency Settings' }}</p>
                        <p class="text-xs text-base-content/50">{{ $host->default_currency ? 'Configured' : 'Required' }}</p>
                    </div>
                    @if($host->default_currency)
                        <span class="icon-[tabler--check] size-4 text-success ml-auto"></span>
                    @else
                        <span class="icon-[tabler--alert-circle] size-4 text-warning ml-auto"></span>
                    @endif
                </button>
                <button type="button" onclick="openDrawer('edit-cancellation-drawer')" class="flex items-center gap-2 p-3 rounded-lg bg-base-100 hover:bg-base-200 transition-colors border border-base-200 text-left">
                    <span class="icon-[tabler--calendar-off] size-5 text-primary"></span>
                    <div>
                        <p class="text-sm font-medium">{{ $trans['settings.cancellation_policy'] ?? 'Cancellation Policy' }}</p>
                        @php $hasCancellation = isset($host->booking_settings['allow_cancellations']); @endphp
                        <p class="text-xs text-base-content/50">{{ $hasCancellation ? 'Configured' : 'Required' }}</p>
                    </div>
                    @if($hasCancellation)
                        <span class="icon-[tabler--check] size-4 text-success ml-auto"></span>
                    @else
                        <span class="icon-[tabler--alert-circle] size-4 text-warning ml-auto"></span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    {{-- Section Divider: Optional Settings --}}
    <div class="divider text-base-content/40 text-sm">
        <span class="icon-[tabler--settings] size-4 mr-1"></span> {{ $trans['settings.optional_settings'] ?? 'Optional Settings' }}
    </div>

    {{-- Branding Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">{{ $trans['settings.branding'] ?? 'Branding' }}</h2>
                    <p class="text-base-content/60 text-sm">{{ $trans['settings.branding_desc'] ?? 'Logo and cover image for your booking page' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Logo --}}
                <div class="space-y-3">
                    <label class="text-sm font-medium">{{ $trans['settings.studio_logo'] ?? 'Studio Logo' }}</label>
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
                                <span class="icon-[tabler--upload] size-4"></span> {{ $trans['settings.upload_logo'] ?? 'Upload Logo' }}
                            </button>
                            <p class="text-xs text-base-content/50 mt-1">{{ $trans['settings.logo_size_hint'] ?? '400x400px, max 5MB' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Cover Image --}}
                <div class="space-y-3">
                    <label class="text-sm font-medium">{{ $trans['settings.cover_image'] ?? 'Cover Image' }}</label>
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
                                <span class="icon-[tabler--upload] size-4"></span> {{ $trans['settings.upload_cover'] ?? 'Upload Cover' }}
                            </button>
                            <p class="text-xs text-base-content/50 mt-1">{{ $trans['settings.cover_size_hint'] ?? '1200x400px, max 5MB' }}</p>
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
                    <h2 class="text-lg font-semibold">{{ $trans['settings.about_studio'] ?? 'About Your Studio' }}</h2>
                    <p class="text-base-content/60 text-sm">{{ $trans['settings.about_studio_desc'] ?? 'Description shown on your public booking page' }}</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" id="about-edit-btn" onclick="toggleAboutEdit()">
                    <span class="icon-[tabler--edit] size-4"></span> {{ $trans['btn.edit'] ?? 'Edit' }}
                </button>
            </div>

            {{-- Display Mode --}}
            <div id="about-display" class="prose prose-sm max-w-none text-base-content/80">
                @if($host->about)
                    {!! $host->about !!}
                @else
                    <p class="text-base-content/50 italic">{{ $trans['settings.no_description'] ?? 'No description set. Click Edit to add a description.' }}</p>
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
                    <h2 class="text-lg font-semibold">{{ $trans['settings.studio_gallery'] ?? 'Studio Gallery' }}</h2>
                    <p class="text-base-content/60 text-sm">{{ $trans['settings.gallery_desc'] ?? 'Showcase your studio with photos (displays on booking page)' }}</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openDrawer('upload-gallery-drawer')">
                    <span class="icon-[tabler--plus] size-4"></span> {{ $trans['settings.add_image'] ?? 'Add Image' }}
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
                    <span class="text-sm text-base-content/50">{{ $trans['settings.add_images'] ?? 'Add Images' }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Contact Information Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">{{ $trans['settings.contact_info'] ?? 'Contact Information' }}</h2>
                    <p class="text-base-content/60 text-sm">{{ $trans['settings.contact_info_desc'] ?? 'Public and internal contact details' }}</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('edit-contact-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> {{ $trans['btn.edit'] ?? 'Edit' }}
                </button>
            </div>

            <div class="space-y-4">
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">{{ $trans['settings.studio_email'] ?? 'Studio Email (Public)' }}</label>
                    <p class="font-medium" id="display-studio-email">{{ $host->studio_email ?? ($trans['settings.not_set'] ?? 'Not set') }}</p>
                </div>
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">{{ $trans['settings.studio_phone'] ?? 'Studio Phone (Public)' }}</label>
                    <p class="font-medium" id="display-phone">{{ $host->phone ?? ($trans['settings.not_set'] ?? 'Not set') }}</p>
                </div>
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">{{ $trans['settings.contact_name'] ?? 'Contact Name (Internal)' }}</label>
                    <p class="font-medium" id="display-contact-name">{{ $host->contact_name ?? ($trans['settings.not_set'] ?? 'Not set') }}</p>
                </div>
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">{{ $trans['settings.support_email'] ?? 'Support Email (Automated)' }}</label>
                    <p class="font-medium" id="display-support-email">{{ $host->support_email ?? ($trans['settings.not_set'] ?? 'Not set') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Social Links Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">{{ $trans['settings.social_links'] ?? 'Social Links' }}</h2>
                    <p class="text-base-content/60 text-sm">{{ $trans['settings.social_links_desc'] ?? 'Connect your social media profiles' }}</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('edit-social-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> {{ $trans['btn.edit'] ?? 'Edit' }}
                </button>
            </div>

            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--brand-instagram] size-5 text-pink-500"></span>
                    <span id="display-instagram" class="text-sm">{{ $host->social_links['instagram'] ?? ($trans['settings.not_connected'] ?? 'Not connected') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--brand-facebook] size-5 text-blue-600"></span>
                    <span id="display-facebook" class="text-sm">{{ $host->social_links['facebook'] ?? ($trans['settings.not_connected'] ?? 'Not connected') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--world] size-5 text-base-content/70"></span>
                    <span id="display-website" class="text-sm">{{ $host->social_links['website'] ?? ($trans['settings.not_connected'] ?? 'Not connected') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--brand-tiktok] size-5 text-base-content"></span>
                    <span id="display-tiktok" class="text-sm">{{ $host->social_links['tiktok'] ?? ($trans['settings.not_connected'] ?? 'Not connected') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Amenities Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">{{ $trans['settings.amenities'] ?? 'Amenities' }}</h2>
                    <p class="text-base-content/60 text-sm">{{ $trans['settings.amenities_desc'] ?? 'Facilities available at your studio' }}</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('edit-amenities-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> {{ $trans['btn.edit'] ?? 'Edit' }}
                </button>
            </div>

            <div class="flex flex-wrap gap-2" id="display-amenities">
                @if($host->amenities && count($host->amenities) > 0)
                    @foreach($host->amenities as $amenity)
                        <span class="badge badge-soft badge-sm">{{ $amenity }}</span>
                    @endforeach
                @else
                    <span class="text-base-content/50 text-sm">{{ $trans['settings.no_amenities'] ?? 'No amenities selected' }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Country of Operation Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">{{ $trans['settings.countries_operation'] ?? 'Countries of Operation' }}</h2>
                    <p class="text-base-content/60 text-sm">{{ $trans['settings.countries_desc'] ?? 'Where your studio operates and serves clients' }}</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('edit-countries-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> {{ $trans['btn.edit'] ?? 'Edit' }}
                </button>
            </div>

            <div class="flex flex-wrap gap-2" id="display-operating-countries">
                @if($host->operating_countries && count($host->operating_countries) > 0)
                    @foreach($host->operating_countries as $countryCode)
                        @if(isset($operatingCountriesList[$countryCode]))
                            <div class="flex items-center gap-2 px-3 py-2 bg-base-200 rounded-lg">
                                <span class="text-lg">{{ $operatingCountriesList[$countryCode]['flag'] }}</span>
                                <span class="text-sm font-medium">{{ $operatingCountriesList[$countryCode]['name'] }}</span>
                            </div>
                        @endif
                    @endforeach
                @else
                    <span class="text-base-content/50 text-sm">{{ $trans['settings.no_countries'] ?? 'No countries selected' }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Currency Card (Required) --}}
    <div id="currency-settings" class="card bg-base-100 scroll-mt-20">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        {{ $trans['settings.business_currencies'] ?? 'Business Currencies' }}
                        <span class="badge badge-error badge-sm">Required</span>
                    </h2>
                    <p class="text-base-content/60 text-sm">{{ $trans['settings.currencies_desc'] ?? 'Currencies accepted for pricing and transactions' }}</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openDrawer('edit-currency-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> {{ $trans['btn.edit'] ?? 'Edit' }}
                </button>
            </div>

            {{-- Default Currency --}}
            <div class="mb-4">
                <label class="text-sm text-base-content/60">{{ $trans['settings.default_currency'] ?? 'Default Currency' }}</label>
                <p class="font-medium text-lg" id="display-default-currency">
                    @php $defaultCurrency = $host->default_currency ?? 'USD'; @endphp
                    @if(isset($currencies[$defaultCurrency]))
                        <span class="text-primary">{{ $currencies[$defaultCurrency]['symbol'] }}</span>
                        {{ $defaultCurrency }} - {{ $currencies[$defaultCurrency]['name'] }}
                    @else
                        {{ $defaultCurrency }}
                    @endif
                </p>
            </div>

            {{-- All Currencies --}}
            <div>
                <label class="text-sm text-base-content/60 mb-2 block">{{ $trans['settings.accepted_currencies'] ?? 'Accepted Currencies' }}</label>
                <div class="flex flex-wrap gap-2" id="display-currencies">
                    @if($host->currencies && count($host->currencies) > 0)
                        @foreach($host->currencies as $code)
                            @if(isset($currencies[$code]))
                                <div class="flex items-center gap-2 px-3 py-2 bg-base-200 rounded-lg {{ $code === $defaultCurrency ? 'ring-2 ring-primary' : '' }}">
                                    <span class="text-lg font-bold text-primary">{{ $currencies[$code]['symbol'] }}</span>
                                    <span class="text-sm font-medium">{{ $code }}</span>
                                    <span class="text-xs text-base-content/60">{{ $currencies[$code]['name'] }}</span>
                                    @if($code === $defaultCurrency)
                                        <span class="badge badge-primary badge-xs">{{ $trans['settings.default'] ?? 'Default' }}</span>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    @else
                        <span class="text-base-content/50 text-sm">{{ $trans['settings.no_currencies'] ?? 'No currencies selected' }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Section Divider: Required Settings --}}
    <div class="divider text-base-content/40 text-sm">
        <span class="icon-[tabler--asterisk] size-4 mr-1 text-error"></span> {{ $trans['settings.required_settings_section'] ?? 'Required Settings' }}
    </div>

    {{-- Studio Categories Card (Required) --}}
    @php
        $categoryGroups = [
            'Mind & Body' => ['Yoga (Hatha, Vinyasa, Power, Yin, Restorative)', 'Pilates (Mat / Reformer)', 'Meditation / Mindfulness', 'Breathwork', 'Tai Chi', 'Qigong', 'Stretching / Mobility', 'Barre'],
            'Strength & Conditioning' => ['Strength Training', 'Functional Training', 'CrossFit', 'Weightlifting (Olympic)', 'Powerlifting', 'Bodyweight Training (Calisthenics)', 'Bootcamp', 'Circuit Training'],
            'Cardio & Endurance' => ['HIIT (High-Intensity Interval Training)', 'Indoor Cycling / Spin', 'Running / Treadmill', 'Rowing', 'Step Aerobics', 'Cardio Kickboxing'],
            'Combat & Martial Arts' => ['Boxing / MMA', 'Kickboxing', 'Muay Thai', 'Jiu-Jitsu / Judo', 'Karate / Taekwondo', 'Krav Maga'],
            'Dance & Movement' => ['Dance Fitness (Zumba, etc.)', 'Hip Hop Dance', 'Ballet / Contemporary', 'Pole Fitness', 'Aerobics'],
            'Water Sports' => ['Swimming (Lessons / Laps)', 'Aqua Aerobics', 'Water Polo', 'Diving / Snorkeling'],
            'Recovery & Wellness' => ['Massage Therapy', 'Physical Therapy / Rehab', 'Foam Rolling / Myofascial Release', 'Cryotherapy', 'Sauna / Steam', 'Acupuncture'],
            'Outdoor & Adventure' => ['Hiking / Trail Running', 'Rock Climbing / Bouldering', 'Kayaking / Paddleboarding', 'Skiing / Snowboarding', 'Surfing'],
            'Team Sports & Recreation' => ['Basketball', 'Soccer / Football', 'Tennis / Racquet Sports', 'Golf', 'Volleyball'],
            'Specialty & Emerging' => ['EMS Training', 'VR Fitness', 'Trampoline Fitness', 'Obstacle Course Training', 'Animal Flow'],
        ];
        $selectedCategories = $host->studio_categories ?? [];
        if (is_string($selectedCategories)) {
            $selectedCategories = json_decode($selectedCategories, true) ?? [];
        }
    @endphp
    <div id="studio-categories-settings" class="card bg-base-100 scroll-mt-20">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        {{ $trans['settings.studio_categories'] ?? 'Studio Categories' }}
                        <span class="badge badge-error badge-sm">Required</span>
                    </h2>
                    <p class="text-base-content/60 text-sm">{{ $trans['settings.studio_categories_desc'] ?? 'What types of services does your studio offer?' }}</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openDrawer('edit-categories-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> {{ $trans['btn.edit'] ?? 'Edit' }}
                </button>
            </div>

            <div class="space-y-1">
                <label class="text-sm text-base-content/60">{{ $trans['settings.selected_categories'] ?? 'Selected Categories' }}</label>
                <div class="flex flex-wrap gap-2" id="display-studio-categories">
                    @if(count($selectedCategories) > 0)
                        @foreach($selectedCategories as $category)
                            <span class="badge badge-soft badge-primary">{{ $category }}</span>
                        @endforeach
                    @else
                        <span class="text-base-content/50">{{ $trans['settings.no_categories_selected'] ?? 'No categories selected' }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Language Settings Card (Required) --}}
    @php
        $supportedLanguages = [
            'en' => ['name' => 'English'],
            'fr' => ['name' => 'French'],
            'de' => ['name' => 'German'],
            'es' => ['name' => 'Spanish'],
        ];
    @endphp
    <div id="language-settings" class="card bg-base-100 scroll-mt-20">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        {{ $trans['settings.language_settings'] ?? 'Language Settings' }}
                        <span class="badge badge-error badge-sm">Required</span>
                    </h2>
                    <p class="text-base-content/60 text-sm">{{ $trans['settings.language_desc'] ?? 'Configure language preferences for your studio' }}</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openDrawer('edit-language-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> {{ $trans['btn.edit'] ?? 'Edit' }}
                </button>
            </div>

            <div class="space-y-4">
                {{-- Studio Languages (Multiple Selection) --}}
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">{{ $trans['settings.studio_languages'] ?? 'Studio Languages' }}</label>
                    <div class="flex flex-wrap gap-2" id="display-studio-languages">
                        @php
                            $studioLanguages = $host->studio_languages ?? ['en'];
                            if (is_string($studioLanguages)) {
                                $studioLanguages = json_decode($studioLanguages, true) ?? ['en'];
                            }
                        @endphp
                        @foreach($studioLanguages as $langCode)
                            @if(isset($supportedLanguages[$langCode]))
                            <span class="badge badge-soft badge-primary">
                                {{ $supportedLanguages[$langCode]['name'] }}
                            </span>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Default Studio Language --}}
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">{{ $trans['settings.default_studio_language'] ?? 'Default Studio Language' }}</label>
                    <div class="flex items-center gap-2" id="display-language-app">
                        @php $langApp = $host->default_language_app ?? 'en'; @endphp
                        @if(isset($supportedLanguages[$langApp]))
                            <span class="font-medium">{{ $supportedLanguages[$langApp]['name'] }}</span>
                        @else
                            <span class="text-base-content/50">Not set</span>
                        @endif
                    </div>
                </div>

                {{-- Booking Page Language --}}
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">{{ $trans['settings.booking_page_language'] ?? 'Booking Page Language' }}</label>
                    <div class="flex items-center gap-2" id="display-language-booking">
                        @php $langBooking = $host->default_language_booking ?? 'en'; @endphp
                        @if(isset($supportedLanguages[$langBooking]))
                            <span class="font-medium">{{ $supportedLanguages[$langBooking]['name'] }}</span>
                        @else
                            <span class="text-base-content/50">Not set</span>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Booking Cancellation Policy Card (Required) --}}
    <div id="cancellation-settings" class="card bg-base-100 scroll-mt-20">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        {{ $trans['settings.cancellation_policy'] ?? 'Booking Cancellation Policy' }}
                        <span class="badge badge-error badge-sm">Required</span>
                    </h2>
                    <p class="text-base-content/60 text-sm">{{ $trans['settings.cancellation_desc'] ?? 'How far in advance clients must cancel bookings' }}</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openDrawer('edit-cancellation-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> {{ $trans['btn.edit'] ?? 'Edit' }}
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
                        <div class="font-semibold text-error">{{ $trans['settings.cancellations_disabled'] ?? 'Cancellations Disabled' }}</div>
                        <p class="text-sm text-base-content/60">{{ $trans['settings.cannot_cancel'] ?? 'Clients cannot cancel their bookings' }}</p>
                    @else
                        <div class="font-semibold" id="display-cancellation-window">{{ $cancellationOptions[$cancellationHours] ?? $cancellationHours . ' hours before class' }}</div>
                        <p class="text-sm text-base-content/60">{{ $trans['settings.must_cancel_advance'] ?? 'Clients must cancel at least this far in advance' }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Assets & Certifications Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold">Assets & Certifications</h2>
                    <p class="text-sm text-base-content/60">Manage studio licenses, certifications, and important documents</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('add-certification-drawer')">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add
                </button>
            </div>

            @php
                $studioCertifications = $host->certifications()->studioLevel()->get();
            @endphp
            <div id="certifications-list">
                @if($studioCertifications->isEmpty())
                    <div class="text-center py-8">
                        <span class="icon-[tabler--certificate] size-12 text-base-content/20 mx-auto"></span>
                        <p class="text-base-content/50 mt-2">No certifications added yet</p>
                        <button type="button" class="btn btn-primary btn-sm mt-4" onclick="openDrawer('add-certification-drawer')">
                            <span class="icon-[tabler--plus] size-4"></span>
                            Add Certification
                        </button>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($studioCertifications as $cert)
                        <div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg" data-cert-id="{{ $cert->id }}">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <span class="icon-[tabler--certificate] size-5 text-primary"></span>
                                </div>
                                <div>
                                    <div class="font-medium">{{ $cert->name }}</div>
                                    @if($cert->certification_name)
                                        <div class="text-xs text-base-content/60">{{ $cert->certification_name }}</div>
                                    @endif
                                    @if($cert->expire_date)
                                        <div class="text-xs mt-1">
                                            <span class="badge {{ $cert->status_badge_class }} badge-xs">
                                                @if($cert->isExpired())
                                                    Expired {{ $cert->expire_date->format('M j, Y') }}
                                                @else
                                                    Expires {{ $cert->expire_date->format('M j, Y') }}
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                @if($cert->file_path)
                                <a href="{{ $cert->file_url }}" target="_blank" class="btn btn-ghost btn-sm btn-square" data-tooltip="View File">
                                    <span class="icon-[tabler--file-download] size-4"></span>
                                </a>
                                @endif
                                <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="editCertification({{ $cert->id }})" data-tooltip="Edit">
                                    <span class="icon-[tabler--pencil] size-4"></span>
                                </button>
                                <button type="button" class="btn btn-ghost btn-sm btn-square text-error" onclick="deleteCertification({{ $cert->id }})" data-tooltip="Delete">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
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
                {{-- Studio Name --}}
                <div>
                    <label class="label-text" for="studio_name">Studio Name <span class="text-error">*</span></label>
                    <input id="studio_name" type="text" class="input w-full" value="{{ $host->studio_name ?? '' }}" required />
                </div>

                {{-- Studio Structure --}}
                <div>
                    <label class="label-text" for="studio_structure">Studio Structure <span class="text-error">*</span></label>
                    <select id="studio_structure" class="select w-full" required>
                        <option value="">Select structure...</option>
                        <option value="solo" {{ ($host->studio_structure ?? '') == 'solo' ? 'selected' : '' }}>Solo (Just me)</option>
                        <option value="team" {{ ($host->studio_structure ?? '') == 'team' ? 'selected' : '' }}>With a Team (Staff members)</option>
                    </select>
                    <p class="text-xs text-base-content/50 mt-1">Tell us how your studio is structured</p>
                </div>

                {{-- Subdomain --}}
                <div>
                    <label class="label-text" for="subdomain">Sub-domain Name <span class="text-error">*</span></label>
                    <div class="join w-full">
                        <input id="subdomain" type="text" class="input join-item flex-1 {{ $host->subdomain ? 'input-disabled bg-base-200 cursor-not-allowed' : '' }}" value="{{ $host->subdomain ?? '' }}" {{ $host->subdomain ? 'readonly' : 'required' }} />
                        <span class="btn btn-soft join-item pointer-events-none">.{{ config('app.booking_domain', 'fitcrm.biz') }}</span>
                    </div>
                    @if($host->subdomain)
                        <p class="text-xs text-base-content/50 mt-1">Subdomain cannot be changed after setup</p>
                    @else
                        <p class="text-xs text-base-content/50 mt-1">Choose your unique booking page URL</p>
                    @endif
                </div>

                {{-- Short Description (Optional) --}}
                <div>
                    <label class="label-text" for="short_description">Short Description</label>
                    <input id="short_description" type="text" class="input w-full" value="{{ $host->short_description ?? '' }}" maxlength="200" placeholder="A brief tagline for your studio" />
                    <p class="text-xs text-base-content/50 mt-1">Shown in the hero section of your booking page (max 200 characters)</p>
                </div>

                {{-- Timezone --}}
                <div>
                    <label class="label-text" for="timezone">Timezone</label>
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
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-basic-drawer')">Cancel</button>
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
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('upload-logo-drawer')">Cancel</button>
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
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('upload-cover-drawer')">Cancel</button>
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
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-contact-drawer')">Cancel</button>
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
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-social-drawer')">Cancel</button>
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
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-amenities-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Edit Countries Drawer --}}
<div id="edit-countries-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Countries of Operation</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-countries-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="edit-countries-form" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4">
            <p class="text-sm text-base-content/60 mb-4">Select all countries where your studio operates and serves clients. This helps with regional settings and compliance.</p>
            <div class="space-y-2">
                @foreach($operatingCountriesList as $code => $info)
                <label class="custom-option flex flex-row items-center gap-3 px-4 py-3 cursor-pointer border border-base-200 rounded-lg hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                    <input type="checkbox" class="checkbox checkbox-primary country-checkbox" value="{{ $code }}" {{ in_array($code, $host->operating_countries ?? []) ? 'checked' : '' }} />
                    <span class="text-xl">{{ $info['flag'] }}</span>
                    <span class="label-text font-medium">{{ $info['name'] }}</span>
                </label>
                @endforeach
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-countries-btn">
                <span class="loading loading-spinner loading-xs hidden" id="countries-spinner"></span>
                Save Changes
            </button>
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-countries-drawer')">Cancel</button>
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
            {{-- Default Currency --}}
            <div class="mb-6">
                <label class="label-text font-medium mb-2 block">Default Currency <span class="text-error">*</span></label>
                <p class="text-sm text-base-content/60 mb-3">Primary currency for pricing. Other currencies require manual price entry.</p>
                <select id="default-currency-select" class="select w-full">
                    @foreach($currencies as $code => $info)
                        <option value="{{ $code }}" {{ ($host->default_currency ?? 'USD') === $code ? 'selected' : '' }}>
                            {{ $info['symbol'] }} {{ $code }} - {{ $info['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="divider text-xs text-base-content/50">ACCEPTED CURRENCIES</div>

            <p class="text-sm text-base-content/60 mb-4">Select all currencies you accept for payments. You'll need to set prices for each currency in your catalog.</p>
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
                    For each selected currency, you'll need to set prices manually in your memberships and class packs.
                </div>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-currency-btn">
                <span class="loading loading-spinner loading-xs hidden" id="currency-spinner"></span>
                Save Changes
            </button>
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-currency-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Edit Language Settings Drawer --}}
<div id="edit-language-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Language Settings</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-language-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="edit-language-form" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4">
            @php
                $studioLanguages = $host->studio_languages ?? ['en'];
                if (is_string($studioLanguages)) {
                    $studioLanguages = json_decode($studioLanguages, true) ?? ['en'];
                }
            @endphp

            {{-- Studio Languages (Multiple Selection) --}}
            <div class="mb-6">
                <label class="label-text font-medium mb-2 block">Studio Languages</label>
                <p class="text-sm text-base-content/60 mb-3">Select all languages your studio supports. You can add translations for each.</p>
                <div class="space-y-2">
                    @foreach($supportedLanguages as $code => $info)
                    <label class="custom-option flex flex-row items-center gap-3 px-4 py-3 cursor-pointer border border-base-200 rounded-lg hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                        <input type="checkbox" name="studio_languages[]" value="{{ $code }}" class="checkbox checkbox-primary studio-language-checkbox" {{ in_array($code, $studioLanguages) ? 'checked' : '' }} />
                        <span class="label-text font-medium">{{ $info['name'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="divider text-xs text-base-content/50">DEFAULT LANGUAGES</div>

            {{-- Default Studio Language --}}
            <div class="mb-6">
                <label class="label-text font-medium mb-2 block">Default Studio Language</label>
                <p class="text-sm text-base-content/60 mb-3">Primary language used throughout the admin dashboard.</p>
                <div class="space-y-2">
                    @foreach($supportedLanguages as $code => $info)
                    <label class="custom-option flex flex-row items-center gap-3 px-4 py-3 cursor-pointer border border-base-200 rounded-lg hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                        <input type="radio" name="default_language_app" value="{{ $code }}" class="radio radio-primary" {{ ($host->default_language_app ?? 'en') === $code ? 'checked' : '' }} />
                        <span class="label-text font-medium">{{ $info['name'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Booking Page Language --}}
            <div class="mb-6">
                <label class="label-text font-medium mb-2 block">Booking Page Language</label>
                <p class="text-sm text-base-content/60 mb-3">Default language for your public booking page.</p>
                <div class="space-y-2">
                    @foreach($supportedLanguages as $code => $info)
                    <label class="custom-option flex flex-row items-center gap-3 px-4 py-3 cursor-pointer border border-base-200 rounded-lg hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                        <input type="radio" name="default_language_booking" value="{{ $code }}" class="radio radio-primary" {{ ($host->default_language_booking ?? 'en') === $code ? 'checked' : '' }} />
                        <span class="label-text font-medium">{{ $info['name'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="alert alert-soft alert-info">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <div class="text-sm">
                    For custom translations, use the <strong>Manage Translations</strong> page to edit specific text elements.
                </div>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-language-btn">
                <span class="loading loading-spinner loading-xs hidden" id="language-spinner"></span>
                Save Changes
            </button>
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-language-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Edit Studio Categories Drawer --}}
<div id="edit-categories-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Studio Categories</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-categories-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="edit-categories-form" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4">
            <p class="text-sm text-base-content/60 mb-4">Select all categories that apply to your studio. You can choose multiple.</p>

            @php
                $allCategoriesList = [
                    'Yoga (Hatha, Vinyasa, Power, Yin, Restorative)',
                    'Pilates (Mat / Reformer)',
                    'Meditation / Mindfulness',
                    'Breathwork',
                    'Tai Chi',
                    'Qigong',
                    'Stretching / Mobility',
                    'Barre',
                    'Strength Training',
                    'Functional Training',
                    'CrossFit',
                    'Weightlifting (Olympic)',
                    'Powerlifting',
                    'Bodyweight Training (Calisthenics)',
                    'Bootcamp',
                    'Circuit Training',
                    'HIIT (High-Intensity Interval Training)',
                    'Indoor Cycling / Spin',
                    'Running / Treadmill',
                    'Rowing',
                    'Step Aerobics',
                    'Cardio Kickboxing',
                    'Boxing',
                    'Kickboxing',
                    'Muay Thai',
                    'MMA (Mixed Martial Arts)',
                    'Brazilian Jiu-Jitsu (BJJ)',
                    'Karate',
                    'Taekwondo',
                    'Self-Defense',
                    'Zumba',
                    'Dance Fitness',
                    'Hip Hop Dance',
                    'Ballet Fitness',
                    'Jazzercise',
                    'Open Gym',
                    'Personal Training',
                    'Small Group Training',
                    'Beginner Fitness',
                    'Senior Fitness',
                    'Youth Fitness',
                    'Prenatal / Postnatal Fitness',
                    'Rehab / Physical Therapy',
                    'Injury Recovery',
                    'Adaptive Fitness',
                    'EMS (Electro Muscle Stimulation)',
                    'Sports Performance Training',
                    'Athlete Conditioning',
                    'Recovery Sessions',
                    'Foam Rolling',
                    'Mobility & Flexibility',
                    'Sauna / Cold Therapy Sessions',
                    'Relaxation Therapy',
                    'Outdoor Bootcamp',
                    'Hiking Fitness',
                    'Trail Running',
                    'Cycling (Outdoor)',
                    'Adventure Fitness',
                ];
                $selectedCategoriesDrawer = $host->studio_categories ?? [];
                if (is_string($selectedCategoriesDrawer)) {
                    $selectedCategoriesDrawer = json_decode($selectedCategoriesDrawer, true) ?? [];
                }
                // Separate predefined and custom categories
                $selectedPredefined = array_intersect($selectedCategoriesDrawer, $allCategoriesList);
                $customCategories = array_diff($selectedCategoriesDrawer, $allCategoriesList);
            @endphp

            {{-- Search Input --}}
            <div class="relative mb-3">
                <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                <input type="text" id="category-search-input" class="input input-bordered w-full pl-10" placeholder="Search categories..." oninput="filterCategories(this.value)">
            </div>

            {{-- Selected Categories Tags --}}
            <div id="selected-categories-tags" class="flex flex-wrap gap-1 mb-3 {{ count($selectedPredefined) == 0 ? 'hidden' : '' }}">
                @foreach($selectedPredefined as $cat)
                <span class="badge badge-primary badge-sm gap-1 selected-tag" data-category="{{ $cat }}">
                    {{ Str::limit($cat, 25) }}
                    <button type="button" class="hover:text-primary-content/70" onclick="toggleCategory('{{ addslashes($cat) }}')">
                        <span class="icon-[tabler--x] size-3"></span>
                    </button>
                </span>
                @endforeach
            </div>

            {{-- Category Checkboxes --}}
            <div id="category-list" class="max-h-64 overflow-y-auto border border-base-200 rounded-lg p-2 space-y-1">
                @foreach($allCategoriesList as $option)
                <label class="category-item flex items-center gap-3 cursor-pointer p-2 hover:bg-base-200 rounded-lg" data-search="{{ strtolower($option) }}">
                    <input type="checkbox" name="studio_categories[]" value="{{ $option }}" class="checkbox checkbox-primary checkbox-sm category-checkbox" {{ in_array($option, $selectedCategoriesDrawer) ? 'checked' : '' }} onchange="onCategoryChange(this)" />
                    <span class="text-sm">{{ $option }}</span>
                </label>
                @endforeach
            </div>

            {{-- Others Option --}}
            <div class="mt-4 border-t border-base-200 pt-4">
                <label class="flex items-center gap-3 cursor-pointer p-2 hover:bg-base-200 rounded-lg">
                    <input type="checkbox" id="others-checkbox" class="checkbox checkbox-primary checkbox-sm" {{ count($customCategories) > 0 ? 'checked' : '' }} onchange="toggleOthersSection()">
                    <span class="text-sm font-medium">Others (Add custom categories)</span>
                </label>
            </div>

            {{-- Custom Categories Section --}}
            <div id="custom-categories-section" class="{{ count($customCategories) == 0 ? 'hidden' : '' }} mt-3 space-y-2">
                <label class="label-text text-sm font-medium">Custom Categories</label>
                <p class="text-xs text-base-content/50">Add your own categories (one per line)</p>
                <textarea id="custom-categories-textarea" class="textarea textarea-bordered w-full" rows="3" placeholder="Enter custom categories, one per line...&#10;e.g.&#10;Aerial Yoga&#10;Pole Fitness&#10;Aqua Aerobics">{{ implode("\n", $customCategories) }}</textarea>
                <div id="custom-categories-tags" class="flex flex-wrap gap-1">
                    @foreach($customCategories as $custom)
                    <span class="badge badge-secondary badge-sm">{{ $custom }}</span>
                    @endforeach
                </div>
            </div>

            {{-- Selected Count --}}
            <div class="mt-3 flex items-center justify-between text-xs text-base-content/60">
                <span id="category-count">{{ count($selectedCategoriesDrawer) }} categor{{ count($selectedCategoriesDrawer) === 1 ? 'y' : 'ies' }} selected</span>
                <button type="button" class="link link-primary" onclick="clearAllCategories()">Clear all</button>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-categories-btn">
                <span class="loading loading-spinner loading-xs hidden" id="categories-spinner"></span>
                Save Changes
            </button>
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-categories-drawer')">Cancel</button>
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
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-cancellation-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Add/Edit Certification Drawer --}}
<div id="add-certification-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold" id="certification-drawer-title">Add Certification</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('add-certification-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="certification-form" class="flex flex-col flex-1 overflow-hidden" enctype="multipart/form-data">
        <input type="hidden" id="certification-id" name="certification_id" value="" />
        <input type="hidden" id="cert_remove_file" name="remove_file" value="" />
        <div class="flex-1 overflow-y-auto p-4">
            <div class="space-y-4">
                <div>
                    <label class="label-text font-medium" for="cert_name">Name <span class="text-error">*</span></label>
                    <input type="text" id="cert_name" name="name" class="input w-full" placeholder="e.g., Business License, Insurance Policy" required />
                </div>

                <div>
                    <label class="label-text font-medium" for="cert_certification_name">Certification / Credential Name</label>
                    <input type="text" id="cert_certification_name" name="certification_name" class="input w-full" placeholder="e.g., ACE Certified, State License #12345" />
                </div>

                <div>
                    <label class="label-text font-medium" for="cert_expire_date">Expiration Date</label>
                    <input type="date" id="cert_expire_date" name="expire_date" class="input w-full" />
                    <p class="text-xs text-base-content/50 mt-1">Leave blank if no expiration</p>
                </div>

                <div>
                    <label class="label-text font-medium" for="cert_reminder_days">Reminder</label>
                    <select id="cert_reminder_days" name="reminder_days" class="select w-full">
                        <option value="">No reminder</option>
                        <option value="7">7 days before expiry</option>
                        <option value="14">14 days before expiry</option>
                        <option value="30">30 days before expiry</option>
                        <option value="60">60 days before expiry</option>
                        <option value="90">90 days before expiry</option>
                    </select>
                </div>

                <div>
                    <label class="label-text font-medium">Upload Document</label>
                    <div class="flex flex-col items-center justify-center border-2 border-dashed border-base-content/20 rounded-lg p-6 hover:border-primary transition-colors cursor-pointer" id="cert-drop-zone">
                        <input type="file" id="cert_file" name="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp" />
                        <div id="cert-upload-placeholder">
                            <span class="icon-[tabler--cloud-upload] size-8 text-base-content/30 mb-2 block mx-auto"></span>
                            <p class="text-sm text-base-content/60 text-center">Drag and drop file here, or</p>
                            <button type="button" class="btn btn-soft btn-sm mt-2 mx-auto block" id="cert-browse-btn">Browse Files</button>
                        </div>
                        <div id="cert-upload-preview" class="hidden w-full text-center">
                            <span class="icon-[tabler--file-check] size-8 text-success mb-2 block mx-auto"></span>
                            <p id="cert-preview-name" class="text-sm font-medium"></p>
                            <button type="button" class="btn btn-ghost btn-xs mt-2" id="cert-remove-preview-btn">
                                <span class="icon-[tabler--x] size-4"></span> Remove
                            </button>
                        </div>
                        <div id="cert-existing-file" class="hidden w-full text-center">
                            <span class="icon-[tabler--file-check] size-8 text-primary mb-2 block mx-auto"></span>
                            <p id="cert-existing-file-name" class="text-sm font-medium"></p>
                            <button type="button" class="btn btn-ghost btn-xs mt-2" id="cert-remove-existing-btn">
                                <span class="icon-[tabler--x] size-4"></span> Remove
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-base-content/50 text-center mt-2">PDF, JPG, PNG, WebP. Max 10MB</p>
                </div>

                <div>
                    <label class="label-text font-medium" for="cert_notes">Notes</label>
                    <textarea id="cert_notes" name="notes" class="textarea w-full" rows="2" placeholder="Additional notes..."></textarea>
                </div>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-certification-btn">
                <span class="loading loading-spinner loading-xs hidden" id="certification-spinner"></span>
                Save
            </button>
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('add-certification-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Delete Certification Modal --}}
<dialog id="delete-certification-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Delete Certification</h3>
        <p class="py-4">Are you sure you want to delete this certification? This action cannot be undone.</p>
        <input type="hidden" id="delete-certification-id" value="" />
        <div class="modal-action">
            <button type="button" class="btn btn-error" id="confirm-delete-certification-btn">Delete</button>
            <button type="button" class="btn" onclick="document.getElementById('delete-certification-modal').close()">Cancel</button>
        </div>
    </div>
</dialog>

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
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('upload-gallery-drawer')">Cancel</button>
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

// Drawer functions - store original data for reset on cancel
var originalDrawerData = {};

function captureDrawerData(id) {
    switch(id) {
        case 'edit-basic-drawer':
            originalDrawerData[id] = {
                studioName: document.getElementById('studio_name')?.value || '',
                shortDescription: document.getElementById('short_description')?.value || '',
                timezone: document.getElementById('timezone')?.value || '',
                studioCategories: Array.from(document.querySelectorAll('.studio-category-checkbox:checked')).map(function(cb) { return cb.value; })
            };
            break;
        case 'edit-contact-drawer':
            originalDrawerData[id] = {
                studioEmail: document.getElementById('studio_email')?.value || '',
                phone: document.getElementById('phone')?.value || '',
                contactName: document.getElementById('contact_name')?.value || '',
                supportEmail: document.getElementById('support_email')?.value || ''
            };
            break;
        case 'edit-social-drawer':
            originalDrawerData[id] = {
                instagram: document.getElementById('social_instagram')?.value || '',
                facebook: document.getElementById('social_facebook')?.value || '',
                website: document.getElementById('social_website')?.value || '',
                tiktok: document.getElementById('social_tiktok')?.value || ''
            };
            break;
        case 'edit-amenities-drawer':
            originalDrawerData[id] = {
                amenities: Array.from(document.querySelectorAll('.amenity-checkbox:checked')).map(function(cb) { return cb.value; })
            };
            break;
        case 'edit-countries-drawer':
            originalDrawerData[id] = {
                countries: Array.from(document.querySelectorAll('.country-checkbox:checked')).map(function(cb) { return cb.value; })
            };
            break;
        case 'edit-currency-drawer':
            originalDrawerData[id] = {
                defaultCurrency: document.getElementById('default-currency-select')?.value || 'USD',
                currencies: Array.from(document.querySelectorAll('.currency-checkbox:checked')).map(function(cb) { return cb.value; })
            };
            break;
        case 'edit-language-drawer':
            originalDrawerData[id] = {
                studioLanguages: Array.from(document.querySelectorAll('.studio-language-checkbox:checked')).map(function(cb) { return cb.value; }),
                defaultLanguageApp: document.querySelector('input[name="default_language_app"]:checked')?.value || 'en',
                defaultLanguageBooking: document.querySelector('input[name="default_language_booking"]:checked')?.value || 'en'
            };
            break;
        case 'edit-cancellation-drawer':
            originalDrawerData[id] = {
                allowCancellations: document.getElementById('allow_cancellations')?.checked || false,
                cancellationWindow: document.querySelector('input[name="cancellation_window_hours"]:checked')?.value || '12'
            };
            break;
        case 'upload-logo-drawer':
        case 'upload-cover-drawer':
        case 'upload-gallery-drawer':
            // For upload drawers, just mark that we need to reset
            originalDrawerData[id] = { needsReset: true };
            break;
    }
}

function resetDrawerData(id) {
    var data = originalDrawerData[id];
    if (!data) return;

    switch(id) {
        case 'edit-basic-drawer':
            if (document.getElementById('studio_name')) document.getElementById('studio_name').value = data.studioName;
            if (document.getElementById('short_description')) document.getElementById('short_description').value = data.shortDescription;
            if (document.getElementById('timezone')) document.getElementById('timezone').value = data.timezone;
            // Reset studio categories checkboxes
            document.querySelectorAll('.studio-category-checkbox').forEach(function(cb) {
                cb.checked = data.studioCategories.includes(cb.value);
            });
            break;
        case 'edit-contact-drawer':
            if (document.getElementById('studio_email')) document.getElementById('studio_email').value = data.studioEmail;
            if (document.getElementById('phone')) document.getElementById('phone').value = data.phone;
            if (document.getElementById('contact_name')) document.getElementById('contact_name').value = data.contactName;
            if (document.getElementById('support_email')) document.getElementById('support_email').value = data.supportEmail;
            break;
        case 'edit-social-drawer':
            if (document.getElementById('social_instagram')) document.getElementById('social_instagram').value = data.instagram;
            if (document.getElementById('social_facebook')) document.getElementById('social_facebook').value = data.facebook;
            if (document.getElementById('social_website')) document.getElementById('social_website').value = data.website;
            if (document.getElementById('social_tiktok')) document.getElementById('social_tiktok').value = data.tiktok;
            break;
        case 'edit-amenities-drawer':
            document.querySelectorAll('.amenity-checkbox').forEach(function(cb) {
                cb.checked = data.amenities.includes(cb.value);
            });
            break;
        case 'edit-countries-drawer':
            document.querySelectorAll('.country-checkbox').forEach(function(cb) {
                cb.checked = data.countries.includes(cb.value);
            });
            break;
        case 'edit-currency-drawer':
            if (document.getElementById('default-currency-select')) document.getElementById('default-currency-select').value = data.defaultCurrency;
            document.querySelectorAll('.currency-checkbox').forEach(function(cb) {
                cb.checked = data.currencies.includes(cb.value);
            });
            break;
        case 'edit-language-drawer':
            document.querySelectorAll('.studio-language-checkbox').forEach(function(cb) {
                cb.checked = data.studioLanguages.includes(cb.value);
            });
            var appRadio = document.querySelector('input[name="default_language_app"][value="' + data.defaultLanguageApp + '"]');
            if (appRadio) appRadio.checked = true;
            var bookingRadio = document.querySelector('input[name="default_language_booking"][value="' + data.defaultLanguageBooking + '"]');
            if (bookingRadio) bookingRadio.checked = true;
            break;
        case 'edit-cancellation-drawer':
            if (document.getElementById('allow_cancellations')) document.getElementById('allow_cancellations').checked = data.allowCancellations;
            var windowRadio = document.querySelector('input[name="cancellation_window_hours"][value="' + data.cancellationWindow + '"]');
            if (windowRadio) windowRadio.checked = true;
            break;
        case 'upload-logo-drawer':
            // Reset logo upload preview
            document.getElementById('logo-input').value = '';
            document.getElementById('logo-upload-placeholder')?.classList.remove('hidden');
            document.getElementById('logo-upload-preview')?.classList.add('hidden');
            document.getElementById('upload-logo-btn').disabled = true;
            break;
        case 'upload-cover-drawer':
            // Reset cover upload preview
            document.getElementById('cover-input').value = '';
            document.getElementById('cover-upload-placeholder')?.classList.remove('hidden');
            document.getElementById('cover-upload-preview')?.classList.add('hidden');
            document.getElementById('upload-cover-btn').disabled = true;
            break;
        case 'upload-gallery-drawer':
            // Reset gallery upload
            document.getElementById('gallery-input').value = '';
            document.getElementById('gallery-upload-placeholder')?.classList.remove('hidden');
            document.getElementById('gallery-upload-preview')?.classList.add('hidden');
            document.getElementById('upload-gallery-btn').disabled = true;
            break;
    }
}

function openDrawer(id) {
    var drawer = document.getElementById(id);
    var backdrop = document.getElementById('drawer-backdrop');
    if (drawer && backdrop) {
        // Capture original data before opening
        captureDrawerData(id);
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
        // Reset drawer data to original values
        resetDrawerData(id);
        drawer.classList.remove('translate-x-0');
        drawer.classList.add('translate-x-full');
        backdrop.classList.remove('opacity-100', 'pointer-events-auto');
        backdrop.classList.add('opacity-0', 'pointer-events-none');
        document.body.style.overflow = '';
    }
}

function closeAllDrawers() {
    var drawers = ['edit-basic-drawer', 'upload-logo-drawer', 'upload-cover-drawer', 'edit-contact-drawer', 'edit-social-drawer', 'edit-amenities-drawer', 'edit-currency-drawer', 'edit-language-drawer', 'edit-categories-drawer', 'edit-cancellation-drawer', 'upload-gallery-drawer', 'add-certification-drawer', 'edit-certification-drawer'];
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

// Initialize
document.addEventListener('DOMContentLoaded', function() {
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

    var studioStructure = document.getElementById('studio_structure').value;
    var subdomain = document.getElementById('subdomain').value;

    // Collect selected studio categories
    var selectedCategories = [];
    document.querySelectorAll('.studio-category-checkbox:checked').forEach(function(cb) {
        selectedCategories.push(cb.value);
    });

    fetch('{{ route("settings.studio.profile.update") }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
            studio_name: document.getElementById('studio_name').value,
            studio_structure: studioStructure,
            subdomain: subdomain,
            studio_categories: selectedCategories,
            short_description: document.getElementById('short_description').value,
            timezone: document.getElementById('timezone').value
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            document.getElementById('display-studio-name').textContent = document.getElementById('studio_name').value || 'Not set';

            // Update studio structure display
            var structureText = studioStructure === 'solo' ? 'Solo (Just me)' : (studioStructure === 'team' ? 'With a Team (Staff members)' : 'Not set');
            document.getElementById('display-studio-structure').innerHTML = studioStructure ? structureText : '<span class="text-base-content/50">Not set</span>';

            // Update subdomain display
            var subdomainDisplay = subdomain ? subdomain + '.{{ config("app.booking_domain", "fitcrm.biz") }}' : 'Not set';
            document.getElementById('display-subdomain').textContent = subdomainDisplay;

            // Update categories display - show as badges
            var categoriesHtml = selectedCategories.length > 0 ? selectedCategories.map(function(c) { return '<span class="badge badge-primary badge-soft badge-sm">' + c + '</span>'; }).join('') : '<span class="text-base-content/50">Not set</span>';
            document.getElementById('display-studio-categories').innerHTML = categoriesHtml;

            document.getElementById('display-short-description').textContent = document.getElementById('short_description').value || 'Not set';
            document.getElementById('display-timezone').textContent = document.getElementById('timezone').value || 'Not set';

            captureDrawerData('edit-basic-drawer'); // Update original data so close doesn't reset
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

// Countries of Operation
var operatingCountries = @json($operatingCountriesList);

document.getElementById('edit-countries-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-countries-btn');
    var spinner = document.getElementById('countries-spinner');
    btn.disabled = true; spinner.classList.remove('hidden');

    var selectedCountries = [];
    document.querySelectorAll('.country-checkbox:checked').forEach(function(cb) { selectedCountries.push(cb.value); });

    fetch('{{ route("settings.studio.countries.update") }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ operating_countries: selectedCountries })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            var countriesHtml = '';
            if (selectedCountries.length > 0) {
                countriesHtml = selectedCountries.map(function(code) {
                    var info = operatingCountries[code];
                    return '<div class="flex items-center gap-2 px-3 py-2 bg-base-200 rounded-lg">' +
                        '<span class="text-lg">' + info.flag + '</span>' +
                        '<span class="text-sm font-medium">' + info.name + '</span>' +
                    '</div>';
                }).join('');
            } else {
                countriesHtml = '<span class="text-base-content/50 text-sm">No countries selected</span>';
            }
            document.getElementById('display-operating-countries').innerHTML = countriesHtml;
            closeDrawer('edit-countries-drawer');
            setTimeout(function() { showToast('Countries of operation updated!'); }, 350);
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

    var defaultCurrency = document.getElementById('default-currency-select').value;

    // Ensure default currency is in the selected currencies
    if (!selectedCurrencies.includes(defaultCurrency)) {
        selectedCurrencies.push(defaultCurrency);
        // Also check the checkbox
        var checkbox = document.querySelector('.currency-checkbox[value="' + defaultCurrency + '"]');
        if (checkbox) checkbox.checked = true;
    }

    fetch('{{ route("settings.studio.currency.update") }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ currencies: selectedCurrencies, default_currency: defaultCurrency })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            // Update currencies display
            var currenciesHtml = '';
            if (selectedCurrencies.length > 0) {
                currenciesHtml = selectedCurrencies.map(function(code) {
                    var info = currencies[code];
                    var isDefault = code === defaultCurrency;
                    return '<div class="flex items-center gap-2 px-3 py-2 bg-base-200 rounded-lg' + (isDefault ? ' ring-2 ring-primary' : '') + '">' +
                        '<span class="text-lg font-bold text-primary">' + info.symbol + '</span>' +
                        '<span class="text-sm font-medium">' + code + '</span>' +
                        '<span class="text-xs text-base-content/60">' + info.name + '</span>' +
                        (isDefault ? '<span class="badge badge-primary badge-xs ml-auto">Default</span>' : '') +
                    '</div>';
                }).join('');
            } else {
                currenciesHtml = '<span class="text-base-content/50 text-sm">No currencies selected</span>';
            }
            document.getElementById('display-currencies').innerHTML = currenciesHtml;

            // Update default currency display
            var defaultInfo = currencies[defaultCurrency];
            document.getElementById('display-default-currency').innerHTML =
                '<span class="text-primary">' + defaultInfo.symbol + '</span> ' +
                defaultCurrency + ' - ' + defaultInfo.name;

            closeDrawer('edit-currency-drawer');
            setTimeout(function() { showToast('Currencies updated!'); }, 350);
        } else { showToast(result.message || 'Failed to update', 'error'); }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

// Studio Categories - Search filter
function filterCategories(query) {
    var items = document.querySelectorAll('.category-item');
    var lowerQuery = query.toLowerCase();
    items.forEach(function(item) {
        var searchText = item.getAttribute('data-search');
        item.style.display = searchText.includes(lowerQuery) ? '' : 'none';
    });
}

// Toggle category checkbox
function toggleCategory(category) {
    var checkbox = document.querySelector('.category-checkbox[value="' + category + '"]');
    if (checkbox) {
        checkbox.checked = !checkbox.checked;
        onCategoryChange(checkbox);
    }
}

// On category change - update tags and count
function onCategoryChange(checkbox) {
    updateSelectedTags();
    updateCategoryCount();
}

// Update selected tags display
function updateSelectedTags() {
    var tagsContainer = document.getElementById('selected-categories-tags');
    var checkboxes = document.querySelectorAll('.category-checkbox:checked');
    var html = '';
    checkboxes.forEach(function(cb) {
        var label = cb.value.length > 25 ? cb.value.substring(0, 22) + '...' : cb.value;
        html += '<span class="badge badge-primary badge-sm gap-1 selected-tag" data-category="' + cb.value + '">' + label + '<button type="button" class="hover:text-primary-content/70" onclick="toggleCategory(\'' + cb.value.replace(/'/g, "\\'") + '\')"><span class="icon-[tabler--x] size-3"></span></button></span>';
    });
    tagsContainer.innerHTML = html;
    tagsContainer.classList.toggle('hidden', checkboxes.length === 0);
}

// Update category count
function updateCategoryCount() {
    var predefinedCount = document.querySelectorAll('.category-checkbox:checked').length;
    var customText = document.getElementById('custom-categories-textarea').value.trim();
    var customCount = customText ? customText.split('\n').filter(function(l) { return l.trim(); }).length : 0;
    var total = predefinedCount + customCount;
    document.getElementById('category-count').textContent = total + ' categor' + (total === 1 ? 'y' : 'ies') + ' selected';
}

// Toggle Others section
function toggleOthersSection() {
    var checkbox = document.getElementById('others-checkbox');
    var section = document.getElementById('custom-categories-section');
    section.classList.toggle('hidden', !checkbox.checked);
    if (!checkbox.checked) {
        document.getElementById('custom-categories-textarea').value = '';
        document.getElementById('custom-categories-tags').innerHTML = '';
    }
    updateCategoryCount();
}

// Update custom tags on textarea input
document.getElementById('custom-categories-textarea').addEventListener('input', function() {
    var lines = this.value.split('\n').map(function(l) { return l.trim(); }).filter(function(l) { return l; });
    var html = lines.map(function(l) { return '<span class="badge badge-secondary badge-sm">' + l + '</span>'; }).join('');
    document.getElementById('custom-categories-tags').innerHTML = html;
    updateCategoryCount();
});

// Clear all categories
function clearAllCategories() {
    document.querySelectorAll('.category-checkbox:checked').forEach(function(cb) { cb.checked = false; });
    document.getElementById('others-checkbox').checked = false;
    document.getElementById('custom-categories-section').classList.add('hidden');
    document.getElementById('custom-categories-textarea').value = '';
    document.getElementById('custom-categories-tags').innerHTML = '';
    updateSelectedTags();
    updateCategoryCount();
}

// Studio Categories form submit
document.getElementById('edit-categories-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-categories-btn');
    var spinner = document.getElementById('categories-spinner');
    btn.disabled = true; spinner.classList.remove('hidden');

    // Get selected predefined categories
    var selectedCategories = [];
    document.querySelectorAll('.category-checkbox:checked').forEach(function(cb) {
        selectedCategories.push(cb.value);
    });

    // Get custom categories
    var customText = document.getElementById('custom-categories-textarea').value.trim();
    if (customText && document.getElementById('others-checkbox').checked) {
        var customCats = customText.split('\n').map(function(l) { return l.trim(); }).filter(function(l) { return l; });
        selectedCategories = selectedCategories.concat(customCats);
    }

    if (selectedCategories.length === 0) {
        showToast('Please select at least one category', 'error');
        btn.disabled = false; spinner.classList.add('hidden');
        return;
    }

    fetch('{{ route("settings.studio.categories.update") }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ studio_categories: selectedCategories })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            var displayHtml = selectedCategories.map(function(cat) { return '<span class="badge badge-soft badge-primary">' + cat + '</span>'; }).join('');
            document.getElementById('display-studio-categories').innerHTML = displayHtml;
            closeDrawer('edit-categories-drawer');
            setTimeout(function() { showToast('Studio categories updated!'); }, 350);
        } else { showToast(result.message || 'Failed to update', 'error'); }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

// Language Settings
var supportedLanguages = {
    'en': { name: 'English' },
    'fr': { name: 'French' },
    'de': { name: 'German' },
    'es': { name: 'Spanish' }
};

document.getElementById('edit-language-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-language-btn');
    var spinner = document.getElementById('language-spinner');
    btn.disabled = true; spinner.classList.remove('hidden');

    // Get selected studio languages (checkboxes)
    var studioLanguages = [];
    document.querySelectorAll('.studio-language-checkbox:checked').forEach(function(cb) {
        studioLanguages.push(cb.value);
    });

    // Ensure at least English is selected
    if (studioLanguages.length === 0) {
        studioLanguages = ['en'];
    }

    var langApp = document.querySelector('input[name="default_language_app"]:checked').value;
    var langBooking = document.querySelector('input[name="default_language_booking"]:checked').value;

    fetch('{{ route("settings.studio.language.update") }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
            studio_languages: studioLanguages,
            default_language_app: langApp,
            default_language_booking: langBooking
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            // Update studio languages display (badges)
            var studioLangHtml = '';
            studioLanguages.forEach(function(code) {
                var info = supportedLanguages[code];
                if (info) {
                    studioLangHtml += '<span class="badge badge-soft badge-primary">' + info.name + '</span>';
                }
            });
            document.getElementById('display-studio-languages').innerHTML = studioLangHtml;

            // Update default studio language display
            var langAppInfo = supportedLanguages[langApp];
            document.getElementById('display-language-app').innerHTML =
                '<span class="font-medium">' + langAppInfo.name + '</span>';

            // Update booking page language display
            var langBookingInfo = supportedLanguages[langBooking];
            document.getElementById('display-language-booking').innerHTML =
                '<span class="font-medium">' + langBookingInfo.name + '</span>';

            // Update original settings so close doesn't reset to old values
            originalLanguageSettings = {
                studioLanguages: studioLanguages,
                defaultLanguageApp: langApp,
                defaultLanguageBooking: langBooking
            };

            closeDrawer('edit-language-drawer');
            setTimeout(function() { showToast('Language settings updated!'); }, 350);
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

// ============================================
// Certifications Management
// ============================================

var editingCertificationId = null;

// Reset certification form
function resetCertificationForm() {
    editingCertificationId = null;
    document.getElementById('certification-drawer-title').textContent = 'Add Certification';
    document.getElementById('certification-id').value = '';
    document.getElementById('cert_name').value = '';
    document.getElementById('cert_certification_name').value = '';
    document.getElementById('cert_expire_date').value = '';
    document.getElementById('cert_reminder_days').value = '';
    document.getElementById('cert_notes').value = '';
    document.getElementById('cert_file').value = '';

    // Reset file preview
    var placeholder = document.getElementById('cert-upload-placeholder');
    var preview = document.getElementById('cert-upload-preview');
    var existingFile = document.getElementById('cert-existing-file');
    if (placeholder) placeholder.classList.remove('hidden');
    if (preview) preview.classList.add('hidden');
    if (existingFile) existingFile.classList.add('hidden');
}

// Edit certification - load data into form
function editCertification(id) {
    editingCertificationId = id;
    document.getElementById('certification-drawer-title').textContent = 'Edit Certification';

    // Show loading state
    var spinner = document.getElementById('certification-spinner');
    spinner.classList.remove('hidden');

    fetch('{{ url("settings/studio/certifications") }}/' + id, {
        method: 'GET',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            var cert = result.certification;
            document.getElementById('certification-id').value = cert.id;
            document.getElementById('cert_name').value = cert.name || '';
            document.getElementById('cert_certification_name').value = cert.certification_name || '';
            document.getElementById('cert_expire_date').value = cert.expire_date || '';
            document.getElementById('cert_reminder_days').value = cert.reminder_days || '';
            document.getElementById('cert_notes').value = cert.notes || '';

            // Handle existing file display
            var placeholder = document.getElementById('cert-upload-placeholder');
            var preview = document.getElementById('cert-upload-preview');
            var existingFile = document.getElementById('cert-existing-file');
            var existingFileName = document.getElementById('cert-existing-file-name');

            if (cert.file_name) {
                if (existingFile && existingFileName) {
                    existingFileName.textContent = cert.file_name;
                    existingFile.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                }
            } else {
                if (existingFile) existingFile.classList.add('hidden');
                if (placeholder) placeholder.classList.remove('hidden');
            }
            if (preview) preview.classList.add('hidden');

            openDrawer('add-certification-drawer');
        } else {
            showToast(result.message || 'Failed to load certification', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { spinner.classList.add('hidden'); });
}

// Delete certification
function deleteCertification(id) {
    document.getElementById('delete-certification-id').value = id;
    document.getElementById('delete-certification-modal').showModal();
}

// Confirm delete certification
document.getElementById('confirm-delete-certification-btn').addEventListener('click', function() {
    var btn = this;
    var id = document.getElementById('delete-certification-id').value;

    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Deleting...';

    fetch('{{ url("settings/studio/certifications") }}/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            // Remove item from list
            var item = document.querySelector('[data-cert-id="' + id + '"]');
            if (item) item.remove();

            // Show empty state if no more certifications
            var list = document.getElementById('certifications-list');
            if (list && list.querySelectorAll('[data-cert-id]').length === 0) {
                list.innerHTML = '<div class="text-center py-8">' +
                    '<span class="icon-[tabler--certificate] size-12 text-base-content/20 mx-auto"></span>' +
                    '<p class="text-base-content/50 mt-2">No certifications added yet</p>' +
                    '<button type="button" class="btn btn-primary btn-sm mt-4" onclick="openDrawer(\'add-certification-drawer\')">' +
                    '<span class="icon-[tabler--plus] size-4"></span> Add Certification</button></div>';
            }

            document.getElementById('delete-certification-modal').close();
            showToast('Certification deleted!');
        } else {
            showToast(result.message || 'Failed to delete', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = 'Delete';
    });
});

// Handle file input change for certification
(function() {
    var fileInput = document.getElementById('cert_file');
    var browseBtn = document.getElementById('cert-browse-btn');
    var dropZone = document.getElementById('cert-drop-zone');
    var placeholder = document.getElementById('cert-upload-placeholder');
    var preview = document.getElementById('cert-upload-preview');
    var previewName = document.getElementById('cert-preview-name');
    var removeBtn = document.getElementById('cert-remove-preview-btn');
    var existingFile = document.getElementById('cert-existing-file');

    if (!fileInput) return;

    if (browseBtn) {
        browseBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); fileInput.click(); });
    }

    if (dropZone) {
        dropZone.addEventListener('click', function(e) { if (!e.target.closest('button')) fileInput.click(); });
        dropZone.addEventListener('dragover', function(e) { e.preventDefault(); dropZone.classList.add('border-primary', 'bg-primary/5'); });
        dropZone.addEventListener('dragleave', function(e) { e.preventDefault(); dropZone.classList.remove('border-primary', 'bg-primary/5'); });
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.classList.remove('border-primary', 'bg-primary/5');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                handleCertFile(e.dataTransfer.files[0]);
            }
        });
    }

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) handleCertFile(this.files[0]);
    });

    if (removeBtn) {
        removeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            fileInput.value = '';
            if (preview) preview.classList.add('hidden');
            if (placeholder) placeholder.classList.remove('hidden');
        });
    }

    function handleCertFile(file) {
        var validTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            showToast('Please upload PDF, JPG, PNG, or WebP', 'error');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            showToast('File must be under 10MB', 'error');
            return;
        }

        if (previewName) previewName.textContent = file.name;
        if (placeholder) placeholder.classList.add('hidden');
        if (existingFile) existingFile.classList.add('hidden');
        if (preview) preview.classList.remove('hidden');
    }

    // Remove existing file button
    var removeExistingBtn = document.getElementById('cert-remove-existing-btn');
    if (removeExistingBtn) {
        removeExistingBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (existingFile) existingFile.classList.add('hidden');
            if (placeholder) placeholder.classList.remove('hidden');
            // Mark for removal
            var removeInput = document.getElementById('cert_remove_file');
            if (removeInput) removeInput.value = '1';
        });
    }
})();

// Certification form submit
document.getElementById('certification-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-certification-btn');
    var spinner = document.getElementById('certification-spinner');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    var formData = new FormData(this);
    var certId = document.getElementById('certification-id').value;
    var isEdit = certId && certId !== '';

    var url = isEdit
        ? '{{ url("settings/studio/certifications") }}/' + certId
        : '{{ route("settings.studio.certifications.store") }}';

    // Add remove_file flag if applicable
    var removeFileInput = document.getElementById('cert_remove_file');
    if (removeFileInput && removeFileInput.value === '1') {
        formData.append('remove_file', '1');
    }

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            var cert = result.certification;
            var list = document.getElementById('certifications-list');

            // Build certification item HTML
            var itemHtml = '<div class="flex items-center justify-between p-3 border border-base-content/10 rounded-lg" data-cert-id="' + cert.id + '">' +
                '<div class="flex items-center gap-3">' +
                '<div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">' +
                '<span class="icon-[tabler--certificate] size-5 text-primary"></span></div>' +
                '<div><div class="font-medium">' + escapeHtml(cert.name) + '</div>';

            if (cert.certification_name) {
                itemHtml += '<div class="text-xs text-base-content/60">' + escapeHtml(cert.certification_name) + '</div>';
            }

            if (cert.expire_date_formatted) {
                itemHtml += '<div class="text-xs mt-1"><span class="badge ' + cert.status_badge_class + ' badge-xs">' +
                    (cert.is_expired ? 'Expired ' : 'Expires ') + cert.expire_date_formatted + '</span></div>';
            }

            itemHtml += '</div></div><div class="flex items-center gap-1">';

            if (cert.file_url) {
                itemHtml += '<a href="' + cert.file_url + '" target="_blank" class="btn btn-ghost btn-sm btn-square" data-tooltip="View File">' +
                    '<span class="icon-[tabler--file-download] size-4"></span></a>';
            }

            itemHtml += '<button type="button" class="btn btn-ghost btn-sm btn-square" onclick="editCertification(' + cert.id + ')" data-tooltip="Edit">' +
                '<span class="icon-[tabler--pencil] size-4"></span></button>' +
                '<button type="button" class="btn btn-ghost btn-sm btn-square text-error" onclick="deleteCertification(' + cert.id + ')" data-tooltip="Delete">' +
                '<span class="icon-[tabler--trash] size-4"></span></button></div></div>';

            if (isEdit) {
                // Update existing item
                var existingItem = document.querySelector('[data-cert-id="' + cert.id + '"]');
                if (existingItem) {
                    existingItem.outerHTML = itemHtml;
                }
            } else {
                // Check if empty state exists and remove it
                var emptyState = list.querySelector('.text-center');
                if (emptyState) {
                    list.innerHTML = '<div class="space-y-3">' + itemHtml + '</div>';
                } else {
                    // Append to existing list
                    var spaceY = list.querySelector('.space-y-3');
                    if (spaceY) {
                        spaceY.insertAdjacentHTML('beforeend', itemHtml);
                    } else {
                        list.innerHTML = '<div class="space-y-3">' + itemHtml + '</div>';
                    }
                }
            }

            resetCertificationForm();
            closeDrawer('add-certification-drawer');
            setTimeout(function() { showToast(isEdit ? 'Certification updated!' : 'Certification added!'); }, 350);
        } else {
            showToast(result.message || 'Failed to save', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() {
        btn.disabled = false;
        spinner.classList.add('hidden');
        // Reset remove file flag
        var removeFileInput = document.getElementById('cert_remove_file');
        if (removeFileInput) removeFileInput.value = '';
    });
});

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Reset form when drawer opens for new certification
var originalOpenDrawer = openDrawer;
openDrawer = function(id) {
    if (id === 'add-certification-drawer' && !editingCertificationId) {
        resetCertificationForm();
    }
    originalOpenDrawer(id);
};
</script>
@endpush
