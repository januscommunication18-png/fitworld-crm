@extends('layouts.settings')

@section('title', 'Studio Profile — Settings')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page">Studio Profile</li>
@endsection

@section('settings-content')
<div class="space-y-6">
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Studio Profile</h2>
                    <p class="text-base-content/60 text-sm">Your studio name, types, and public information</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openDrawer('edit-profile-drawer')">
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
                    <label class="text-sm text-base-content/60">City</label>
                    <p class="font-medium" id="display-city">{{ $host->city ?? 'Not set' }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Timezone</label>
                    <p class="font-medium" id="display-timezone">{{ $host->timezone ?? 'Not set' }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Phone</label>
                    <p class="font-medium" id="display-phone">{{ $host->phone ?? 'Not set' }}</p>
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
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Studio Logo</h2>
                    <p class="text-base-content/60 text-sm">Your logo appears on your booking page and emails</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('upload-logo-drawer')">
                    <span class="icon-[tabler--upload] size-4"></span> Upload
                </button>
            </div>
            <div class="flex items-center gap-4">
                <div id="logo-preview" class="w-20 h-20 bg-base-200 rounded-lg flex items-center justify-center overflow-hidden">
                    @if($host->logo_path)
                        <img src="{{ Storage::url($host->logo_path) }}" alt="Studio Logo" class="w-full h-full object-cover" />
                    @else
                        <span class="icon-[tabler--photo] size-10 text-base-content/30"></span>
                    @endif
                </div>
                <div class="text-sm text-base-content/60">
                    <p>Recommended size: 400x400px</p>
                    <p>Max file size: 2MB</p>
                    <p>Formats: PNG, JPG, SVG</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">About Your Studio</h2>
                    <p class="text-base-content/60 text-sm">This description appears on your public booking page</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" onclick="openDrawer('edit-about-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> Edit
                </button>
            </div>
            <p id="about-text" class="text-sm text-base-content/80">
                {{ $host->about ?? 'No description set. Click Edit to add a description for your studio.' }}
            </p>
        </div>
    </div>
</div>

{{-- Drawer Backdrop --}}
<div id="drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none transition-opacity duration-300" onclick="closeAllDrawers()"></div>

{{-- Edit Profile Drawer --}}
<div id="edit-profile-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Edit Studio Profile</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-profile-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="edit-profile-form" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4">
            <div class="space-y-4">
                <div>
                    <label class="label-text" for="studio_name">Studio Name</label>
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
                    <label class="label-text" for="city">City</label>
                    <input id="city" type="text" class="input w-full" value="{{ $host->city ?? '' }}" />
                </div>
                <div>
                    <label class="label-text" for="phone">Phone</label>
                    <input id="phone" type="tel" class="input w-full" value="{{ $host->phone ?? '' }}" />
                </div>
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
                    </select>
                </div>
                <div>
                    <label class="label-text">Studio Types</label>
                    <p class="text-xs text-base-content/50 mb-2">Select all that apply</p>
                    <div id="studio-types-select" class="relative">
                        <button type="button" id="types-toggle" class="advance-select-toggle w-full" onclick="toggleTypesDropdown()">
                            <span id="types-placeholder" class="text-base-content/50 hidden">Select studio types...</span>
                            <span id="types-badges" class="flex flex-wrap gap-1 pe-6"></span>
                            <span class="icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content absolute top-1/2 end-3 -translate-y-1/2"></span>
                        </button>
                        <div id="types-dropdown" class="advance-select-menu max-h-48 overflow-y-auto absolute z-50 w-full mt-1 hidden">
                            @foreach(['Yoga', 'Pilates', 'Barre', 'Spinning', 'CrossFit', 'Dance', 'Martial Arts', 'Personal Training', 'Other'] as $type)
                            <div class="advance-select-option cursor-pointer" data-type="{{ $type }}" onclick="toggleTypeOption('{{ $type }}')">
                                <div class="flex justify-between items-center flex-1">
                                    <span>{{ $type }}</span>
                                    <span class="type-check icon-[tabler--check] shrink-0 size-4 text-primary hidden"></span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" id="studio_types" name="studio_types" value="{{ $host->studio_types ? implode(',', $host->studio_types) : '' }}" />
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('edit-profile-drawer')">Cancel</button>
            <button type="submit" class="btn btn-primary" id="save-profile-btn">
                <span class="loading loading-spinner loading-xs hidden" id="profile-spinner"></span>
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
            <div class="flex flex-col items-center justify-center border-2 border-dashed border-base-content/20 rounded-lg p-8 hover:border-primary transition-colors cursor-pointer" id="drop-zone">
                <input type="file" id="logo-input" name="logo" class="hidden" accept="image/png,image/jpeg,image/svg+xml" />
                <div id="upload-placeholder" class="text-center">
                    <span class="icon-[tabler--cloud-upload] size-12 text-base-content/30 mb-2 block mx-auto"></span>
                    <p class="text-sm text-base-content/60">Drag and drop your logo here, or</p>
                    <button type="button" class="btn btn-soft btn-sm mt-2" id="browse-btn">Browse Files</button>
                </div>
                <div id="upload-preview" class="hidden text-center">
                    <img id="preview-image" src="" alt="Preview" class="w-32 h-32 object-contain rounded-lg mb-2 mx-auto" />
                    <p id="file-name" class="text-sm text-base-content/60"></p>
                    <button type="button" class="btn btn-ghost btn-xs mt-2" id="remove-preview-btn">
                        <span class="icon-[tabler--x] size-4"></span> Remove
                    </button>
                </div>
            </div>
            <p class="text-xs text-base-content/50 text-center mt-4">PNG, JPG, or SVG. Max 2MB. Recommended 400x400px.</p>
        </div>
        <div class="flex justify-end gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('upload-logo-drawer')">Cancel</button>
            <button type="submit" class="btn btn-primary" id="upload-btn" disabled>
                <span class="loading loading-spinner loading-xs hidden" id="logo-spinner"></span>
                Upload Logo
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
@endsection

@push('scripts')
<script>
// CSRF token for AJAX requests
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Studio Types Multiselect
var selectedTypes = {!! json_encode($host->studio_types ?? []) !!};
var typesDropdownOpen = false;

function toggleTypesDropdown() {
    typesDropdownOpen = !typesDropdownOpen;
    var dropdown = document.getElementById('types-dropdown');
    if (typesDropdownOpen) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

function toggleTypeOption(type) {
    var index = selectedTypes.indexOf(type);
    if (index === -1) {
        selectedTypes.push(type);
    } else {
        selectedTypes.splice(index, 1);
    }
    updateTypesDisplay();
}

function removeType(type, event) {
    event.stopPropagation();
    var index = selectedTypes.indexOf(type);
    if (index !== -1) {
        selectedTypes.splice(index, 1);
    }
    updateTypesDisplay();
}

function updateTypesDisplay() {
    var badgesContainer = document.getElementById('types-badges');
    var placeholder = document.getElementById('types-placeholder');
    var hiddenInput = document.getElementById('studio_types');

    // Update hidden input
    hiddenInput.value = selectedTypes.join(',');

    // Update badges display
    if (selectedTypes.length === 0) {
        placeholder.classList.remove('hidden');
        badgesContainer.innerHTML = '';
    } else {
        placeholder.classList.add('hidden');
        var maxVisible = 3;
        var visibleTypes = selectedTypes.slice(0, maxVisible);
        var hiddenCount = selectedTypes.length - maxVisible;

        var html = visibleTypes.map(function(type) {
            return '<span class="badge badge-soft badge-primary badge-sm gap-1">' + type +
                '<button type="button" class="hover:text-error" onclick="removeType(\'' + type + '\', event)">' +
                '<span class="icon-[tabler--x] size-3"></span></button></span>';
        }).join('');

        if (hiddenCount > 0) {
            html += '<span class="badge badge-soft badge-neutral badge-sm">+' + hiddenCount + '</span>';
        }
        badgesContainer.innerHTML = html;
    }

    // Update checkmarks in dropdown
    document.querySelectorAll('#types-dropdown .advance-select-option').forEach(function(option) {
        var type = option.dataset.type;
        var check = option.querySelector('.type-check');
        if (selectedTypes.includes(type)) {
            option.classList.add('select-active');
            check.classList.remove('hidden');
        } else {
            option.classList.remove('select-active');
            check.classList.add('hidden');
        }
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    var selectContainer = document.getElementById('studio-types-select');
    if (selectContainer && !selectContainer.contains(e.target)) {
        typesDropdownOpen = false;
        document.getElementById('types-dropdown').classList.add('hidden');
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTypesDisplay();
});

// Custom drawer functions
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
    ['edit-profile-drawer', 'upload-logo-drawer', 'edit-about-drawer'].forEach(function(id) {
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

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAllDrawers();
    }
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

(function() {
    // Edit Profile Form - AJAX submission
    document.getElementById('edit-profile-form').addEventListener('submit', function(e) {
        e.preventDefault();

        var btn = document.getElementById('save-profile-btn');
        var spinner = document.getElementById('profile-spinner');
        btn.disabled = true;
        spinner.classList.remove('hidden');

        var data = {
            studio_name: document.getElementById('studio_name').value,
            city: document.getElementById('city').value,
            phone: document.getElementById('phone').value,
            timezone: document.getElementById('timezone').value,
            studio_types: selectedTypes
        };

        fetch('{{ route("settings.studio.profile.update") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(function(response) { return response.json(); })
        .then(function(result) {
            if (result.success) {
                // Update display
                document.getElementById('display-studio-name').textContent = data.studio_name || 'Not set';
                document.getElementById('display-city').textContent = data.city || 'Not set';
                document.getElementById('display-phone').textContent = data.phone || 'Not set';
                document.getElementById('display-timezone').textContent = data.timezone || 'Not set';

                var typesHtml = selectedTypes.length > 0
                    ? selectedTypes.map(function(type) {
                        return '<span class="badge badge-primary badge-soft badge-sm">' + type + '</span>';
                    }).join('')
                    : '<span class="text-base-content/50">Not set</span>';
                document.getElementById('display-types').innerHTML = typesHtml;

                closeDrawer('edit-profile-drawer');
                setTimeout(function() { showToast('Studio profile updated successfully!'); }, 350);
            } else {
                showToast(result.message || 'Failed to update profile', 'error');
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
        })
        .finally(function() {
            btn.disabled = false;
            spinner.classList.add('hidden');
        });
    });

    // Edit About Form - AJAX submission
    document.getElementById('edit-about-form').addEventListener('submit', function(e) {
        e.preventDefault();

        var btn = document.getElementById('save-about-btn');
        var spinner = document.getElementById('about-spinner');
        btn.disabled = true;
        spinner.classList.remove('hidden');

        var aboutText = document.getElementById('about').value;

        fetch('{{ route("settings.studio.about.update") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ about: aboutText })
        })
        .then(function(response) { return response.json(); })
        .then(function(result) {
            if (result.success) {
                document.getElementById('about-text').textContent = aboutText || 'No description set. Click Edit to add a description for your studio.';
                closeDrawer('edit-about-drawer');
                setTimeout(function() { showToast('Description updated successfully!'); }, 350);
            } else {
                showToast(result.message || 'Failed to update description', 'error');
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
        })
        .finally(function() {
            btn.disabled = false;
            spinner.classList.add('hidden');
        });
    });

    // Logo Upload
    var dropZone = document.getElementById('drop-zone');
    var logoInput = document.getElementById('logo-input');
    var browseBtn = document.getElementById('browse-btn');
    var removeBtn = document.getElementById('remove-preview-btn');
    var uploadPlaceholder = document.getElementById('upload-placeholder');
    var uploadPreview = document.getElementById('upload-preview');
    var previewImage = document.getElementById('preview-image');
    var fileName = document.getElementById('file-name');
    var uploadBtn = document.getElementById('upload-btn');

    browseBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        logoInput.click();
    });

    removeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        clearLogoPreview();
    });

    dropZone.addEventListener('click', function(e) {
        if (e.target === dropZone || e.target.closest('#upload-placeholder')) {
            if (!e.target.closest('button')) {
                logoInput.click();
            }
        }
    });

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-primary', 'bg-primary/5');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-primary/5');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-primary/5');
        var files = e.dataTransfer.files;
        if (files.length > 0) {
            handleLogoFile(files[0]);
        }
    });

    logoInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleLogoFile(this.files[0]);
        }
    });

    function handleLogoFile(file) {
        var validTypes = ['image/png', 'image/jpeg', 'image/svg+xml'];
        if (!validTypes.includes(file.type)) {
            showToast('Please upload a PNG, JPG, or SVG file.', 'error');
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            showToast('File size must be less than 2MB.', 'error');
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            fileName.textContent = file.name;
            uploadPlaceholder.classList.add('hidden');
            uploadPreview.classList.remove('hidden');
            uploadBtn.disabled = false;
        };
        reader.readAsDataURL(file);
    }

    function clearLogoPreview() {
        logoInput.value = '';
        previewImage.src = '';
        fileName.textContent = '';
        uploadPlaceholder.classList.remove('hidden');
        uploadPreview.classList.add('hidden');
        uploadBtn.disabled = true;
    }

    // Logo Upload Form - AJAX submission
    document.getElementById('upload-logo-form').addEventListener('submit', function(e) {
        e.preventDefault();

        var btn = document.getElementById('upload-btn');
        var spinner = document.getElementById('logo-spinner');
        btn.disabled = true;
        spinner.classList.remove('hidden');

        var formData = new FormData();
        formData.append('logo', logoInput.files[0]);

        fetch('{{ route("settings.studio.logo.upload") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(result) {
            if (result.success) {
                var logoPreviewEl = document.getElementById('logo-preview');
                logoPreviewEl.innerHTML = '<img src="' + result.logo_url + '" alt="Studio Logo" class="w-full h-full object-cover" />';
                closeDrawer('upload-logo-drawer');
                clearLogoPreview();
                setTimeout(function() { showToast('Logo uploaded successfully!'); }, 350);
            } else {
                showToast(result.message || 'Failed to upload logo', 'error');
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
        })
        .finally(function() {
            btn.disabled = false;
            spinner.classList.add('hidden');
        });
    });
})();
</script>
@endpush
