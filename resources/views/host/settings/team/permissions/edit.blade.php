@extends('layouts.settings')

@section('title', 'Edit Permissions — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.team.permissions') }}">Permissions</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $user->full_name }}</li>
    </ol>
@endsection

@section('settings-content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Form Column --}}
    <div class="lg:col-span-2 space-y-6">
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

        <form action="{{ route('settings.team.permissions.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- User Info --}}
            <div class="card bg-base-100 mb-6">
                <div class="card-header">
                    <h3 class="card-title flex items-center gap-2">
                        <span class="icon-[tabler--user] size-5 text-primary"></span>
                        Team Member
                    </h3>
                </div>
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="avatar placeholder">
                            @php
                                $bgColor = match($user->role) {
                                    'admin' => 'bg-secondary text-secondary-content',
                                    'staff' => 'bg-info text-info-content',
                                    'instructor' => 'bg-accent text-accent-content',
                                    default => 'bg-base-300 text-base-content'
                                };
                            @endphp
                            <div class="{{ $bgColor }} w-14 rounded-full">
                                <span class="text-lg">{{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-lg font-semibold">{{ $user->full_name }}</div>
                            <div class="text-sm text-base-content/60">{{ $user->email }}</div>
                            <div class="mt-2">
                                @php
                                    $roleBadge = match($user->role) {
                                        'admin' => 'badge-secondary',
                                        'staff' => 'badge-info',
                                        'instructor' => 'badge-accent',
                                        default => 'badge-ghost'
                                    };
                                    $roleIcon = match($user->role) {
                                        'admin' => 'icon-[tabler--shield]',
                                        'staff' => 'icon-[tabler--user]',
                                        'instructor' => 'icon-[tabler--yoga]',
                                        default => 'icon-[tabler--user]'
                                    };
                                @endphp
                                <span class="badge {{ $roleBadge }} badge-soft gap-1">
                                    <span class="{{ $roleIcon }} size-3.5"></span>
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                        </div>
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

                        // Get current permissions
                        $userPermissions = $user->permissions ?? [];
                        if (empty($userPermissions)) {
                            $userPermissions = \App\Models\User::getDefaultPermissionsForRole($user->role);
                        }
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
                    Save Permissions
                </button>
                <a href="{{ route('settings.team.permissions') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>

    {{-- Sidebar Column --}}
    <div class="space-y-6">
        {{-- Current Status --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title text-sm">Current Status</h3>
            </div>
            <div class="card-body pt-0">
                @php
                    $hasCustom = !empty($user->permissions);
                @endphp
                @if($hasCustom)
                    <div class="flex items-center gap-2 text-primary">
                        <span class="icon-[tabler--adjustments] size-5"></span>
                        <span class="font-medium">Custom permissions active</span>
                    </div>
                    <p class="text-xs text-base-content/60 mt-1">This user has {{ count($user->permissions) }} custom permissions set.</p>
                @else
                    <div class="flex items-center gap-2 text-base-content/70">
                        <span class="icon-[tabler--shield] size-5"></span>
                        <span class="font-medium">Using role defaults</span>
                    </div>
                    <p class="text-xs text-base-content/60 mt-1">This user inherits permissions from their {{ ucfirst($user->role) }} role.</p>
                @endif
            </div>
        </div>

        {{-- Role Guide --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title text-sm">Role Reference</h3>
            </div>
            <div class="card-body pt-0 space-y-3">
                <div class="p-3 rounded-lg border {{ $user->role === 'admin' ? 'border-secondary ring-2 ring-secondary' : 'border-secondary/20' }} bg-secondary/5">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="icon-[tabler--shield] size-4 text-secondary"></span>
                        <span class="font-medium text-sm">Admin</span>
                    </div>
                    <p class="text-xs text-base-content/60">Full access to manage scheduling, bookings, students, instructors, and team settings. Cannot access billing.</p>
                </div>
                <div class="p-3 rounded-lg border {{ $user->role === 'staff' ? 'border-info ring-2 ring-info' : 'border-info/20' }} bg-info/5">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="icon-[tabler--user] size-4 text-info"></span>
                        <span class="font-medium text-sm">Staff</span>
                    </div>
                    <p class="text-xs text-base-content/60">Can manage daily operations like bookings, check-ins, and student management. Limited settings access.</p>
                </div>
                <div class="p-3 rounded-lg border {{ $user->role === 'instructor' ? 'border-accent ring-2 ring-accent' : 'border-accent/20' }} bg-accent/5">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="icon-[tabler--yoga] size-4 text-accent"></span>
                        <span class="font-medium text-sm">Instructor</span>
                    </div>
                    <p class="text-xs text-base-content/60">Can view their own schedule and mark attendance for classes they teach. Very limited dashboard access.</p>
                </div>
            </div>
        </div>

        {{-- Tips --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title text-sm flex items-center gap-2">
                    <span class="icon-[tabler--bulb] size-4 text-warning"></span>
                    Tips
                </h3>
            </div>
            <div class="card-body pt-0">
                <ul class="space-y-2 text-xs text-base-content/70">
                    <li class="flex gap-2">
                        <span class="icon-[tabler--check] size-4 text-success shrink-0 mt-0.5"></span>
                        <span>Start with fewer permissions - you can always add more later</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="icon-[tabler--check] size-4 text-success shrink-0 mt-0.5"></span>
                        <span>Use "Reset to Role Defaults" to start fresh</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="icon-[tabler--check] size-4 text-success shrink-0 mt-0.5"></span>
                        <span>Changes take effect immediately after saving</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Info Alert --}}
        <div class="alert alert-soft alert-info">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <div>
                <div class="font-medium text-sm">Saved automatically</div>
                <p class="text-xs">Empty permissions will revert to role defaults</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Role default permissions
const roleDefaults = {
    admin: @json(\App\Models\User::getDefaultPermissionsForRole('admin')),
    staff: @json(\App\Models\User::getDefaultPermissionsForRole('staff')),
    instructor: @json(\App\Models\User::getDefaultPermissionsForRole('instructor'))
};

const currentRole = '{{ $user->role }}';

function resetToRoleDefaults() {
    const defaults = roleDefaults[currentRole] || [];

    document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
        checkbox.checked = defaults.includes(checkbox.dataset.permission);
    });

    updateAllCategoryCounts();
}

function toggleCategory(category) {
    const checkboxes = document.querySelectorAll('.permission-checkbox[data-category="' + category + '"]');
    const allChecked = Array.from(checkboxes).every(function(cb) { return cb.checked; });

    checkboxes.forEach(function(checkbox) {
        checkbox.checked = !allChecked;
    });

    updateCategoryCount(category);
}

function updateCategoryCount(category) {
    const checkboxes = document.querySelectorAll('.permission-checkbox[data-category="' + category + '"]');
    const total = checkboxes.length;
    const checked = Array.from(checkboxes).filter(function(cb) { return cb.checked; }).length;
    const countEl = document.getElementById('perm-count-' + category);
    if (countEl) {
        countEl.textContent = checked + ' of ' + total + ' enabled';
    }
}

function updateAllCategoryCounts() {
    const categories = new Set();
    document.querySelectorAll('.permission-checkbox').forEach(function(cb) {
        categories.add(cb.dataset.category);
    });
    categories.forEach(function(category) {
        updateCategoryCount(category);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Update all category counts on page load
    updateAllCategoryCounts();
});
</script>
@endpush
@endsection
