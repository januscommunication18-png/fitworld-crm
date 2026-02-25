@extends('backoffice.layouts.app')

@section('title', 'Translations')
@section('page-title', 'Translation Management')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-base-content/60">Manage translations for all client studios.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openDrawer('add-translation-drawer')">
            <span class="icon-[tabler--plus] size-5"></span>
            Add Translation
        </button>
    </div>

    {{-- Tabs for Global vs Studio-specific --}}
    <div class="tabs tabs-bordered">
        <a href="{{ route('backoffice.translations.index', ['scope' => 'global']) }}"
           class="tab {{ ($scope ?? '') === 'global' ? 'tab-active' : '' }}">
            <span class="icon-[tabler--world] size-4 mr-2"></span>
            Global (All Studios)
        </a>
        <a href="{{ route('backoffice.translations.index', ['scope' => 'studio']) }}"
           class="tab {{ ($scope ?? 'studio') === 'studio' ? 'tab-active' : '' }}">
            <span class="icon-[tabler--building] size-4 mr-2"></span>
            Studio-Specific
        </a>
    </div>

    {{-- Host Selector & Filters --}}
    <div class="card bg-base-100">
        <div class="card-body py-4">
            <form method="GET" action="{{ route('backoffice.translations.index') }}" class="flex flex-wrap gap-4">
                <input type="hidden" name="scope" value="{{ $scope ?? 'studio' }}">

                @if(($scope ?? 'studio') === 'studio')
                {{-- Host Selector (only for studio-specific) --}}
                <div class="form-control w-full sm:w-64">
                    <select name="host_id" class="select select-bordered" onchange="this.form.submit()">
                        <option value="">Select a studio...</option>
                        @foreach($hosts as $host)
                            <option value="{{ $host->id }}" {{ $hostId == $host->id ? 'selected' : '' }}>
                                {{ $host->studio_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if($hostId || ($scope ?? '') === 'global')
                {{-- Category Filter --}}
                <div class="form-control w-full sm:w-auto">
                    <select name="category" class="select select-bordered select-sm w-full sm:w-48" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ $filters['category'] == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Page Context Filter --}}
                @if($pageContexts->count() > 0)
                <div class="form-control w-full sm:w-auto">
                    <select name="page_filter" class="select select-bordered select-sm w-full sm:w-48" onchange="this.form.submit()">
                        <option value="">All Pages</option>
                        @foreach($pageContexts as $page)
                            <option value="{{ $page }}" {{ $filters['page_filter'] == $page ? 'selected' : '' }}>{{ $page }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Search --}}
                <div class="form-control flex-1">
                    <div class="join w-full">
                        <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search by key or value..." class="input input-bordered input-sm join-item flex-1" />
                        <button type="submit" class="btn btn-sm join-item">
                            <span class="icon-[tabler--search] size-4"></span>
                        </button>
                    </div>
                </div>

                @if($filters['category'] || $filters['page_filter'] || $filters['search'])
                <a href="{{ route('backoffice.translations.index', ['scope' => $scope ?? 'studio', 'host_id' => $hostId]) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--x] size-4"></span> Clear
                </a>
                @endif
                @endif
            </form>
        </div>
    </div>

    @if(($scope ?? 'studio') === 'studio' && !$hostId)
        {{-- No Host Selected --}}
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--building] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">Select a Studio</h3>
                <p class="text-base-content/60">Choose a studio from the dropdown above to manage their translations.</p>
            </div>
        </div>
    @else
        @if(($scope ?? '') === 'global')
        {{-- Global Translations Info --}}
        <div class="alert alert-info">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <div>
                <span class="font-medium">Global Translations</span> - These translations apply to ALL studios as defaults. Studios can override them with their own translations.
            </div>
        </div>
        @else
        {{-- Copy Translations Section (only for studio-specific) --}}
        <div class="card bg-base-100">
            <div class="card-body py-3">
                <div class="flex flex-wrap items-center gap-4">
                    <span class="text-sm font-medium">Copy translations:</span>
                    <select id="source-host-select" class="select select-bordered select-sm w-48">
                        <option value="">From another studio...</option>
                        <option value="global">From Global Translations</option>
                        @foreach($hosts as $host)
                            @if($host->id != $hostId)
                            <option value="{{ $host->id }}">{{ $host->studio_name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" id="overwrite-checkbox" class="checkbox checkbox-sm checkbox-primary" />
                        Overwrite existing
                    </label>
                    <button type="button" class="btn btn-soft btn-sm btn-primary" onclick="copyTranslations()">
                        <span class="icon-[tabler--copy] size-4"></span>
                        Copy
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- Translations Table --}}
        <div class="card bg-base-100">
            <div class="card-body p-0">
                @if($translations->total() > 0)
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="w-12">#</th>
                                <th>Key</th>
                                <th>Category</th>
                                <th>Page</th>
                                <th>Value (English)</th>
                                <th class="w-24 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($translations as $index => $translation)
                            <tr class="hover">
                                <td class="text-base-content/50">{{ $translations->firstItem() + $index }}</td>
                                <td>
                                    <code class="text-xs bg-base-200 px-2 py-1 rounded">{{ $translation->translation_key }}</code>
                                </td>
                                <td>
                                    <span class="badge badge-soft badge-primary badge-sm">{{ $categories[$translation->category] ?? $translation->category }}</span>
                                </td>
                                <td>
                                    @if($translation->page_context)
                                        <span class="badge badge-ghost badge-sm">{{ $translation->page_context }}</span>
                                    @else
                                        <span class="text-base-content/30">-</span>
                                    @endif
                                </td>
                                <td class="max-w-[300px]" title="{{ $translation->value_en }}">
                                    {{ Str::limit($translation->value_en, 50) }}
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" class="btn btn-ghost btn-xs btn-circle" onclick="editTranslation({{ $translation->id }})" title="Edit">
                                            <span class="icon-[tabler--edit] size-4"></span>
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-xs btn-circle text-error" onclick="deleteTranslation({{ $translation->id }})" title="Delete">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($translations->hasPages())
                <div class="p-4 border-t border-base-200">
                    {{ $translations->links() }}
                </div>
                @endif

                @else
                <div class="text-center py-12">
                    <span class="icon-[tabler--language-off] size-16 text-base-content/20 mx-auto block mb-4"></span>
                    <h3 class="text-lg font-semibold mb-2">No Translations Found</h3>
                    <p class="text-base-content/60 mb-4">
                        @if($filters['category'] || $filters['page_filter'] || $filters['search'])
                            No translations match your filters.
                        @else
                            Get started by adding translations for this studio.
                        @endif
                    </p>
                    <button type="button" class="btn btn-primary" onclick="openDrawer('add-translation-drawer')">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Add Translation
                    </button>
                </div>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- Add Translation Drawer --}}
<div id="add-translation-drawer" class="fixed top-0 right-0 h-full w-full max-w-lg bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Add Translation</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('add-translation-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="add-translation-form" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            {{-- Category --}}
            <div class="form-control">
                <label class="label" for="add-category"><span class="label-text font-medium">Category <span class="text-error">*</span></span></label>
                <select id="add-category" name="category" class="select select-bordered" required>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Translation Key --}}
            <div class="form-control">
                <label class="label" for="add-key"><span class="label-text font-medium">Translation Key <span class="text-error">*</span></span></label>
                <input type="text" id="add-key" name="translation_key" class="input input-bordered" placeholder="e.g., booking.confirm_button" required />
                <label class="label"><span class="label-text-alt text-base-content/60">A unique identifier for this translation</span></label>
            </div>

            {{-- Page Context --}}
            <div class="form-control">
                <label class="label" for="add-page"><span class="label-text font-medium">Page Context</span></label>
                <input type="text" id="add-page" name="page_context" class="input input-bordered" placeholder="e.g., booking, dashboard, checkout" />
                <label class="label"><span class="label-text-alt text-base-content/60">Optional: Group translations by page</span></label>
            </div>

            {{-- Value (English) --}}
            <div class="form-control">
                <label class="label" for="add-value-en">
                    <span class="label-text font-medium">Value <span class="text-error">*</span></span>
                </label>
                <textarea id="add-value-en" name="value_en" class="textarea textarea-bordered" rows="3" required></textarea>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="add-translation-btn">
                <span class="loading loading-spinner loading-xs hidden" id="add-spinner"></span>
                Add Translation
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('add-translation-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Edit Translation Drawer --}}
<div id="edit-translation-drawer" class="fixed top-0 right-0 h-full w-full max-w-lg bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Edit Translation</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-translation-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="edit-translation-form" class="flex flex-col flex-1 overflow-hidden">
        <input type="hidden" id="edit-id" name="id" />
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            {{-- Category --}}
            <div class="form-control">
                <label class="label" for="edit-category"><span class="label-text font-medium">Category <span class="text-error">*</span></span></label>
                <select id="edit-category" name="category" class="select select-bordered" required>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Translation Key --}}
            <div class="form-control">
                <label class="label" for="edit-key"><span class="label-text font-medium">Translation Key <span class="text-error">*</span></span></label>
                <input type="text" id="edit-key" name="translation_key" class="input input-bordered" required />
            </div>

            {{-- Page Context --}}
            <div class="form-control">
                <label class="label" for="edit-page"><span class="label-text font-medium">Page Context</span></label>
                <input type="text" id="edit-page" name="page_context" class="input input-bordered" />
            </div>

            {{-- Value (English) --}}
            <div class="form-control">
                <label class="label" for="edit-value-en">
                    <span class="label-text font-medium">Value <span class="text-error">*</span></span>
                </label>
                <textarea id="edit-value-en" name="value_en" class="textarea textarea-bordered" rows="3" required></textarea>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="edit-translation-btn">
                <span class="loading loading-spinner loading-xs hidden" id="edit-spinner"></span>
                Save Changes
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('edit-translation-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Drawer Backdrop --}}
<div id="drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="closeAllDrawers()"></div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const currentHostId = {{ $hostId ?? 'null' }};
const currentScope = '{{ $scope ?? "studio" }}';

// Store translations data for editing
const translationsData = @json($translations->keyBy('id'));

// Drawer functions
function openDrawer(id) {
    document.getElementById(id).classList.remove('translate-x-full');
    document.getElementById('drawer-backdrop').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeDrawer(id) {
    document.getElementById(id).classList.add('translate-x-full');
    document.getElementById('drawer-backdrop').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function closeAllDrawers() {
    document.querySelectorAll('[id$="-drawer"]').forEach(function(drawer) {
        if (!drawer.id.includes('backdrop')) {
            drawer.classList.add('translate-x-full');
        }
    });
    document.getElementById('drawer-backdrop').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Add Translation
document.getElementById('add-translation-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('add-translation-btn');
    var spinner = document.getElementById('add-spinner');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    var formData = {
        host_id: currentScope === 'global' ? null : currentHostId,
        category: document.getElementById('add-category').value,
        translation_key: document.getElementById('add-key').value,
        page_context: document.getElementById('add-page').value || null,
        value_en: document.getElementById('add-value-en').value,
    };

    fetch('{{ route("backoffice.translations.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            closeDrawer('add-translation-drawer');
            showToast('Translation added successfully!');
            setTimeout(function() { location.reload(); }, 500);
        } else {
            showToast(result.message || 'Failed to add translation', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

// Edit Translation
function editTranslation(id) {
    var translation = translationsData[id];
    if (!translation) {
        showToast('Translation not found', 'error');
        return;
    }

    document.getElementById('edit-id').value = translation.id;
    document.getElementById('edit-category').value = translation.category;
    document.getElementById('edit-key').value = translation.translation_key;
    document.getElementById('edit-page').value = translation.page_context || '';
    document.getElementById('edit-value-en').value = translation.value_en || '';

    openDrawer('edit-translation-drawer');
}

document.getElementById('edit-translation-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('edit-translation-btn');
    var spinner = document.getElementById('edit-spinner');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    var id = document.getElementById('edit-id').value;
    var formData = {
        category: document.getElementById('edit-category').value,
        translation_key: document.getElementById('edit-key').value,
        page_context: document.getElementById('edit-page').value || null,
        value_en: document.getElementById('edit-value-en').value,
    };

    fetch('{{ url("backoffice/translations") }}/' + id, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            closeDrawer('edit-translation-drawer');
            showToast('Translation updated successfully!');
            setTimeout(function() { location.reload(); }, 500);
        } else {
            showToast(result.message || 'Failed to update translation', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); })
    .finally(function() { btn.disabled = false; spinner.classList.add('hidden'); });
});

// Delete Translation
function deleteTranslation(id) {
    if (!confirm('Are you sure you want to delete this translation?')) {
        return;
    }

    fetch('{{ url("backoffice/translations") }}/' + id, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            showToast('Translation deleted successfully!');
            setTimeout(function() { location.reload(); }, 500);
        } else {
            showToast(result.message || 'Failed to delete translation', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); });
}

// Copy Translations
function copyTranslations() {
    var sourceHostId = document.getElementById('source-host-select').value;
    if (!sourceHostId) {
        showToast('Please select a source studio', 'error');
        return;
    }

    var overwrite = document.getElementById('overwrite-checkbox').checked;

    if (!confirm('This will copy all translations from the selected studio. Continue?')) {
        return;
    }

    fetch('{{ route("backoffice.translations.copy") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            source_host_id: sourceHostId,
            target_host_id: currentHostId,
            overwrite: overwrite
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            showToast(result.message);
            setTimeout(function() { location.reload(); }, 1000);
        } else {
            showToast(result.message || 'Failed to copy translations', 'error');
        }
    })
    .catch(function() { showToast('An error occurred', 'error'); });
}

// Toast notification
function showToast(message, type = 'success') {
    var toast = document.createElement('div');
    toast.className = 'toast toast-top toast-end z-50';
    toast.innerHTML = '<div class="alert alert-' + type + '">' +
        '<span class="icon-[tabler--' + (type === 'success' ? 'check' : 'x') + '] size-5"></span>' +
        '<span>' + message + '</span></div>';
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 3000);
}
</script>
@endpush
@endsection
