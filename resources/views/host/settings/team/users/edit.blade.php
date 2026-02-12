@extends('layouts.settings')

@section('title', 'Edit User — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.team.users') }}">Users & Roles</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Edit {{ $user->full_name }}</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6 max-w-2xl">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('settings.team.users.show', $user) }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Edit {{ $user->full_name }}</h1>
            <p class="text-base-content/60 mt-1">Update team member details and permissions.</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-soft alert-success">
        <span class="icon-[tabler--check] size-5"></span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-soft alert-error">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <form action="{{ route('settings.team.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- User Info --}}
        <div class="card bg-base-100 mb-6">
            <div class="card-header">
                <h3 class="card-title flex items-center gap-2">
                    <span class="icon-[tabler--user-edit] size-5 text-primary"></span>
                    Edit Team Member
                </h3>
            </div>
            <div class="card-body space-y-4">
                {{-- User Display --}}
                <div class="flex items-center gap-4 p-4 bg-base-200/50 rounded-lg">
                    <div class="avatar placeholder">
                        @php
                            $bgColor = match($user->role) {
                                'owner' => 'bg-primary text-primary-content',
                                'admin' => 'bg-secondary text-secondary-content',
                                'staff' => 'bg-info text-info-content',
                                'instructor' => 'bg-accent text-accent-content',
                                default => 'bg-base-300 text-base-content'
                            };
                        @endphp
                        <div class="{{ $bgColor }} w-12 rounded-full">
                            <span>{{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="font-medium text-lg">{{ $user->full_name }}</div>
                        <div class="text-sm text-base-content/60">{{ $user->email }}</div>
                    </div>
                </div>

                <div>
                    <label class="label-text" for="role">Role <span class="text-error">*</span></label>
                    <select id="role" name="role" class="hidden" required
                        data-select='{
                            "placeholder": "Select a role...",
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "dropdownClasses": "advance-select-menu",
                            "optionClasses": "advance-select-option selected:select-active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }'>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="staff" {{ old('role', $user->role) == 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="instructor" {{ old('role', $user->role) == 'instructor' ? 'selected' : '' }}>Instructor</option>
                    </select>
                    @error('role')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Permissions --}}
        <div class="card bg-base-100 mb-6">
            <div class="card-header">
                <h3 class="card-title flex items-center gap-2">
                    <span class="icon-[tabler--shield-cog] size-5 text-primary"></span>
                    Permissions
                </h3>
                <p class="text-base-content/60 text-sm">Customize what this user can access</p>
            </div>
            <div class="card-body">
                @php
                    $categoryIcons = [
                        'schedule' => 'icon-[tabler--calendar]',
                        'bookings' => 'icon-[tabler--clipboard-list]',
                        'students' => 'icon-[tabler--users]',
                        'offers' => 'icon-[tabler--tag]',
                        'insights' => 'icon-[tabler--chart-bar]',
                        'payments' => 'icon-[tabler--credit-card]',
                        'studio' => 'icon-[tabler--building-store]',
                        'team' => 'icon-[tabler--users-group]',
                        'billing' => 'icon-[tabler--receipt]',
                    ];
                    $categoryColors = [
                        'schedule' => 'text-primary bg-primary/10',
                        'bookings' => 'text-secondary bg-secondary/10',
                        'students' => 'text-info bg-info/10',
                        'offers' => 'text-warning bg-warning/10',
                        'insights' => 'text-accent bg-accent/10',
                        'payments' => 'text-success bg-success/10',
                        'studio' => 'text-primary bg-primary/10',
                        'team' => 'text-secondary bg-secondary/10',
                        'billing' => 'text-info bg-info/10',
                    ];
                    $userPermissions = old('permissions', $user->permissions ?? []);
                @endphp

                <div class="space-y-2">
                    @foreach($groupedPermissions as $category => $permissions)
                    <details class="group border border-base-content/10 rounded-lg overflow-hidden" id="perm-section-{{ $category }}">
                        <summary class="flex items-center gap-3 p-3 cursor-pointer hover:bg-base-200/50 transition-colors list-none">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $categoryColors[$category] ?? 'text-base-content bg-base-200' }}">
                                <span class="{{ $categoryIcons[$category] ?? 'icon-[tabler--settings]' }} size-4"></span>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-medium text-sm">{{ ucfirst($category) }}</h4>
                                <p class="text-xs text-base-content/50" id="perm-count-{{ $category }}">0 of {{ count($permissions) }} enabled</p>
                            </div>
                            <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform group-open:rotate-180"></span>
                        </summary>
                        <div class="border-t border-base-content/10 bg-base-200/30 p-3 space-y-1">
                            <div class="flex justify-end mb-2">
                                <button type="button" class="text-xs text-primary hover:underline" onclick="toggleCategory('{{ $category }}')">
                                    Toggle all
                                </button>
                            </div>
                            @foreach($permissions as $permission => $label)
                            <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-100 cursor-pointer transition-colors">
                                <input type="checkbox"
                                       name="permissions[]"
                                       value="{{ $permission }}"
                                       class="checkbox checkbox-primary checkbox-sm permission-checkbox"
                                       data-permission="{{ $permission }}"
                                       data-category="{{ $category }}"
                                       onchange="updateCategoryCount('{{ $category }}')"
                                       {{ in_array($permission, $userPermissions) ? 'checked' : '' }} />
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </details>
                    @endforeach
                </div>

                <div class="flex justify-end mt-4">
                    <button type="button" class="btn btn-ghost btn-sm gap-2" onclick="resetToRoleDefaults()">
                        <span class="icon-[tabler--refresh] size-4"></span>
                        Reset to Role Defaults
                    </button>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary gap-2">
                <span class="icon-[tabler--check] size-5"></span>
                Save Changes
            </button>
            <a href="{{ route('settings.team.users') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Role default permissions
const roleDefaults = {
    admin: @json(\App\Models\User::getDefaultPermissionsForRole('admin')),
    staff: @json(\App\Models\User::getDefaultPermissionsForRole('staff')),
    instructor: @json(\App\Models\User::getDefaultPermissionsForRole('instructor'))
};

function resetToRoleDefaults() {
    var role = document.getElementById('role').value;
    var defaults = roleDefaults[role] || [];

    document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
        checkbox.checked = defaults.includes(checkbox.dataset.permission);
    });

    updateAllCategoryCounts();
}

function toggleCategory(category) {
    var checkboxes = document.querySelectorAll('.permission-checkbox[data-category="' + category + '"]');
    var allChecked = Array.from(checkboxes).every(function(cb) { return cb.checked; });

    checkboxes.forEach(function(checkbox) {
        checkbox.checked = !allChecked;
    });

    updateCategoryCount(category);
}

function updateCategoryCount(category) {
    var checkboxes = document.querySelectorAll('.permission-checkbox[data-category="' + category + '"]');
    var total = checkboxes.length;
    var checked = Array.from(checkboxes).filter(function(cb) { return cb.checked; }).length;
    var countEl = document.getElementById('perm-count-' + category);
    if (countEl) {
        countEl.textContent = checked + ' of ' + total + ' enabled';
    }
}

function updateAllCategoryCounts() {
    var categories = new Set();
    document.querySelectorAll('.permission-checkbox').forEach(function(cb) {
        categories.add(cb.dataset.category);
    });
    categories.forEach(function(category) {
        updateCategoryCount(category);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Set initial category counts
    updateAllCategoryCounts();
});
</script>
@endpush
@endsection
