@extends('layouts.settings')

@section('title', 'Studio Profile — Settings')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page">Studio Profile</li>
@endsection

@php
$currencies = [
    'USD' => ['symbol' => '$', 'name' => 'US Dollar'],
    'CAD' => ['symbol' => 'C$', 'name' => 'Canadian Dollar'],
    'GBP' => ['symbol' => '£', 'name' => 'Pound Sterling'],
    'EUR' => ['symbol' => '€', 'name' => 'Euro'],
    'AUD' => ['symbol' => 'A$', 'name' => 'Australian Dollar'],
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
                    <label class="text-sm text-base-content/60">Subdomain</label>
                    <p class="font-medium" id="display-subdomain">{{ $host->subdomain ? $host->subdomain . '.fitcrm.app' : 'Not set' }}</p>
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
                    <label class="text-sm text-base-content/60">City</label>
                    <p class="font-medium" id="display-city">{{ $host->city ?? 'Not set' }}</p>
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
                                <img src="{{ Storage::url($host->logo_path) }}" alt="Studio Logo" class="w-full h-full object-cover" />
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
                                <img src="{{ Storage::url($host->cover_image_path) }}" alt="Cover Image" class="w-full h-full object-cover" />
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

    {{-- About Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">About Your Studio</h2>
                    <p class="text-base-content/60 text-sm">Description shown on your public booking page</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('edit-about-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> Edit
                </button>
            </div>
            <p id="about-text" class="text-sm text-base-content/80">
                {{ $host->about ?? 'No description set. Click Edit to add a description.' }}
            </p>
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                    <h2 class="text-lg font-semibold">Business Currency</h2>
                    <p class="text-base-content/60 text-sm">Currency used for pricing and transactions</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('edit-currency-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> Edit
                </button>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                    <span class="text-xl font-bold text-primary" id="display-currency-symbol">{{ $currencies[$host->currency ?? 'USD']['symbol'] }}</span>
                </div>
                <div>
                    <p class="font-medium" id="display-currency-name">{{ ($host->currency ?? 'USD') }} — {{ $currencies[$host->currency ?? 'USD']['name'] }}</p>
                    <p class="text-xs text-base-content/50">All prices will be displayed in this currency</p>
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
                    <label class="label-text" for="subdomain">Subdomain</label>
                    <div class="join w-full">
                        <input id="subdomain" type="text" class="input join-item flex-1 input-disabled bg-base-200 cursor-not-allowed" value="{{ $host->subdomain ?? '' }}" readonly />
                        <span class="btn btn-soft join-item pointer-events-none">.fitcrm.app</span>
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
                <div>
                    <label class="label-text" for="city">City</label>
                    <input id="city" type="text" class="input w-full" value="{{ $host->city ?? '' }}" placeholder="e.g. Austin, TX" />
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('edit-basic-drawer')">Cancel</button>
            <button type="submit" class="btn btn-primary" id="save-basic-btn">
                <span class="loading loading-spinner loading-xs hidden" id="basic-spinner"></span>
                Save Changes
            </button>
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
        <div class="flex justify-end gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('upload-logo-drawer')">Cancel</button>
            <button type="submit" class="btn btn-primary" id="upload-logo-btn" disabled>
                <span class="loading loading-spinner loading-xs hidden" id="logo-spinner"></span>
                Upload Logo
            </button>
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
        <div class="flex justify-end gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('upload-cover-drawer')">Cancel</button>
            <button type="submit" class="btn btn-primary" id="upload-cover-btn" disabled>
                <span class="loading loading-spinner loading-xs hidden" id="cover-spinner"></span>
                Upload Cover
            </button>
        </div>
    </form>
</div>

{{-- Edit About Drawer --}}
<div id="edit-about-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Edit Studio Description</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-about-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="edit-about-form" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4">
            <div>
                <label class="label-text" for="about">About Your Studio</label>
                <textarea id="about" class="textarea w-full" rows="8" placeholder="Tell students about your studio...">{{ $host->about ?? '' }}</textarea>
                <p class="text-xs text-base-content/50 mt-1">This appears on your public booking page</p>
            </div>
        </div>
        <div class="flex justify-end gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('edit-about-drawer')">Cancel</button>
            <button type="submit" class="btn btn-primary" id="save-about-btn">
                <span class="loading loading-spinner loading-xs hidden" id="about-spinner"></span>
                Save Changes
            </button>
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
        <div class="flex justify-end gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('edit-contact-drawer')">Cancel</button>
            <button type="submit" class="btn btn-primary" id="save-contact-btn">
                <span class="loading loading-spinner loading-xs hidden" id="contact-spinner"></span>
                Save Changes
            </button>
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
        <div class="flex justify-end gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('edit-social-drawer')">Cancel</button>
            <button type="submit" class="btn btn-primary" id="save-social-btn">
                <span class="loading loading-spinner loading-xs hidden" id="social-spinner"></span>
                Save Changes
            </button>
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
        <div class="flex justify-end gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('edit-amenities-drawer')">Cancel</button>
            <button type="submit" class="btn btn-primary" id="save-amenities-btn">
                <span class="loading loading-spinner loading-xs hidden" id="amenities-spinner"></span>
                Save Changes
            </button>
        </div>
    </form>
</div>

{{-- Edit Currency Drawer --}}
<div id="edit-currency-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Business Currency</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-currency-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="edit-currency-form" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4">
            <div class="space-y-4">
                <div>
                    <label class="label-text" for="currency">Select Currency</label>
                    <select id="currency" class="select w-full">
                        @foreach($currencies as $code => $info)
                        <option value="{{ $code }}" {{ ($host->currency ?? 'USD') == $code ? 'selected' : '' }}>
                            {{ $code }} ({{ $info['symbol'] }}) — {{ $info['name'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="alert alert-soft alert-warning">
                    <span class="icon-[tabler--alert-triangle] size-5"></span>
                    <div class="text-sm">
                        <strong>Important:</strong> This currency will be used for all pricing on your booking page.
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('edit-currency-drawer')">Cancel</button>
            <button type="submit" class="btn btn-primary" id="save-currency-btn">
                <span class="loading loading-spinner loading-xs hidden" id="currency-spinner"></span>
                Save Changes
            </button>
        </div>
    </form>
</div>

{{-- Currency Change Confirmation Modal --}}
<div id="currency-confirm-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 opacity-0 pointer-events-none transition-opacity duration-200">
    <div class="card bg-base-100 w-full max-w-md mx-4 transform scale-95 transition-transform duration-200">
        <div class="card-body">
            <div class="flex items-start gap-4">
                <div class="flex items-center justify-center size-12 rounded-full bg-warning/20 shrink-0">
                    <span class="icon-[tabler--alert-triangle] size-6 text-warning"></span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Change Currency?</h3>
                    <p class="text-sm text-base-content/70 mt-2">
                        Changing your currency will <strong>not</strong> convert existing prices or transactions. This will only apply to new bookings and payments.
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" class="btn btn-ghost" onclick="closeCurrencyModal()">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirm-currency-btn">Confirm Change</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var selectedTypes = {!! json_encode($host->studio_types ?? []) !!};
var typesDropdownOpen = false;
var currencies = @json($currencies);
var pendingCurrency = null;

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
    var drawers = ['edit-basic-drawer', 'upload-logo-drawer', 'upload-cover-drawer', 'edit-about-drawer', 'edit-contact-drawer', 'edit-social-drawer', 'edit-amenities-drawer', 'edit-currency-drawer'];
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

// Currency modal
function openCurrencyModal() {
    var modal = document.getElementById('currency-confirm-modal');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100', 'pointer-events-auto');
    modal.querySelector('.card').classList.remove('scale-95');
    modal.querySelector('.card').classList.add('scale-100');
}

function closeCurrencyModal() {
    var modal = document.getElementById('currency-confirm-modal');
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100', 'pointer-events-auto');
    modal.querySelector('.card').classList.add('scale-95');
    modal.querySelector('.card').classList.remove('scale-100');
    pendingCurrency = null;
}

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
            city: document.getElementById('city').value,
            timezone: document.getElementById('timezone').value,
            studio_types: selectedTypes
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            document.getElementById('display-studio-name').textContent = document.getElementById('studio_name').value || 'Not set';
            document.getElementById('display-city').textContent = document.getElementById('city').value || 'Not set';
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

document.getElementById('edit-about-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-about-btn');
    var spinner = document.getElementById('about-spinner');
    btn.disabled = true; spinner.classList.remove('hidden');

    fetch('{{ route("settings.studio.about.update") }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ about: document.getElementById('about').value })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            document.getElementById('about-text').textContent = document.getElementById('about').value || 'No description set. Click Edit to add a description.';
            closeDrawer('edit-about-drawer');
            setTimeout(function() { showToast('Description updated!'); }, 350);
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
    pendingCurrency = document.getElementById('currency').value;
    saveCurrency(false);
});

function saveCurrency(confirmed) {
    var btn = document.getElementById('save-currency-btn');
    var spinner = document.getElementById('currency-spinner');
    btn.disabled = true; spinner.classList.remove('hidden');

    fetch('{{ route("settings.studio.currency.update") }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ currency: pendingCurrency, confirmed: confirmed })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.requires_confirmation) {
            openCurrencyModal();
        } else if (result.success) {
            var info = currencies[pendingCurrency];
            document.getElementById('display-currency-symbol').textContent = info.symbol;
            document.getElementById('display-currency-name').textContent = pendingCurrency + ' — ' + info.name;
            closeDrawer('edit-currency-drawer');
            closeCurrencyModal();
            setTimeout(function() { showToast('Currency updated!'); }, 350);
        } else { showToast(result.message || 'Failed to update', 'error'); }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
}

document.getElementById('confirm-currency-btn').addEventListener('click', function() {
    saveCurrency(true);
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
</script>
@endpush
