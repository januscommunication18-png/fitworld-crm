@extends('layouts.settings')

@section('title', 'Permissions — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Permissions</li>
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

    @if(session('error'))
    <div class="alert alert-soft alert-error">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Role Permissions Overview --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-2">Default Role Permissions</h2>
            <p class="text-base-content/60 text-sm mb-6">These are the default permissions for each role. You can override these for individual users below.</p>

            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Permission</th>
                            <th class="text-center">
                                <span class="flex items-center justify-center gap-1">
                                    <span class="icon-[tabler--crown] size-4 text-primary"></span>
                                    Owner
                                </span>
                            </th>
                            <th class="text-center">
                                <span class="flex items-center justify-center gap-1">
                                    <span class="icon-[tabler--shield] size-4 text-secondary"></span>
                                    Admin
                                </span>
                            </th>
                            <th class="text-center">
                                <span class="flex items-center justify-center gap-1">
                                    <span class="icon-[tabler--user] size-4 text-info"></span>
                                    Staff
                                </span>
                            </th>
                            <th class="text-center">
                                <span class="flex items-center justify-center gap-1">
                                    <span class="icon-[tabler--yoga] size-4 text-accent"></span>
                                    Instructor
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $ownerPerms = \App\Models\User::getDefaultPermissionsForRole('owner');
                            $adminPerms = \App\Models\User::getDefaultPermissionsForRole('admin');
                            $staffPerms = \App\Models\User::getDefaultPermissionsForRole('staff');
                            $instructorPerms = \App\Models\User::getDefaultPermissionsForRole('instructor');
                            $currentCategory = '';
                        @endphp
                        @foreach($allPermissions as $permission => $label)
                            @php
                                $parts = explode('.', $permission);
                                $category = ucfirst($parts[0]);
                            @endphp
                            @if($category !== $currentCategory)
                                @php $currentCategory = $category; @endphp
                                <tr>
                                    <td colspan="5" class="bg-base-200 font-semibold text-sm">{{ $category }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-sm">{{ $label }}</td>
                                <td class="text-center">
                                    @if(in_array($permission, $ownerPerms))
                                        <span class="icon-[tabler--check] size-5 text-success"></span>
                                    @else
                                        <span class="icon-[tabler--x] size-5 text-base-content/20"></span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(in_array($permission, $adminPerms))
                                        <span class="icon-[tabler--check] size-5 text-success"></span>
                                    @else
                                        <span class="icon-[tabler--x] size-5 text-base-content/20"></span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(in_array($permission, $staffPerms))
                                        <span class="icon-[tabler--check] size-5 text-success"></span>
                                    @else
                                        <span class="icon-[tabler--x] size-5 text-base-content/20"></span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(in_array($permission, $instructorPerms))
                                        <span class="icon-[tabler--check] size-5 text-success"></span>
                                    @else
                                        <span class="icon-[tabler--x] size-5 text-base-content/20"></span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Per-User Permission Overrides --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-2">User Permission Overrides</h2>
            <p class="text-base-content/60 text-sm mb-6">Grant or revoke specific permissions for individual team members, overriding their role defaults.</p>

            @if($users->count() > 0)
            <div class="space-y-4">
                @foreach($users as $user)
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                @php
                                    $bgColor = match($user->role) {
                                        'admin' => 'bg-secondary text-secondary-content',
                                        'staff' => 'bg-info text-info-content',
                                        'instructor' => 'bg-accent text-accent-content',
                                        default => 'bg-base-300 text-base-content'
                                    };
                                @endphp
                                <div class="{{ $bgColor }} w-10 rounded-full">
                                    <span>{{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="font-medium">{{ $user->full_name }}</div>
                                <div class="text-sm text-base-content/60">{{ ucfirst($user->role) }}</div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline" onclick="openPermissionsModal({{ $user->id }})">
                            <span class="icon-[tabler--adjustments] size-4"></span> Customize
                        </button>
                    </div>

                    @php
                        $userPermissions = $user->permissions;
                        $hasOverrides = !empty($userPermissions);
                    @endphp

                    @if($hasOverrides)
                    <div class="flex flex-wrap gap-2">
                        @foreach($userPermissions as $key => $value)
                            @php
                                // Handle both array formats: ['perm1', 'perm2'] and ['perm1' => true]
                                $permKey = is_string($value) ? $value : $key;
                            @endphp
                            @if(is_string($value) || $value === true)
                            <span class="badge badge-primary badge-soft badge-sm">
                                {{ $allPermissions[$permKey] ?? $permKey }}
                            </span>
                            @endif
                        @endforeach
                    </div>
                    @else
                    <div class="text-sm text-base-content/50">
                        Using default {{ ucfirst($user->role) }} permissions
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-base-200 rounded-full mb-4">
                    <span class="icon-[tabler--users] size-8 text-base-content/40"></span>
                </div>
                <h3 class="text-lg font-medium mb-2">No team members to customize</h3>
                <p class="text-base-content/60">Add team members in Users & Roles to set custom permissions.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Permission Categories Explanation --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Permission Categories</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="icon-[tabler--calendar] size-5 text-primary"></span>
                        <div class="font-medium">Schedule</div>
                    </div>
                    <div class="text-sm text-base-content/60">
                        View and manage classes, appointments, and the studio calendar.
                    </div>
                </div>
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="icon-[tabler--users] size-5 text-secondary"></span>
                        <div class="font-medium">Students</div>
                    </div>
                    <div class="text-sm text-base-content/60">
                        View student profiles, check-in students, and manage memberships.
                    </div>
                </div>
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="icon-[tabler--credit-card] size-5 text-info"></span>
                        <div class="font-medium">Payments</div>
                    </div>
                    <div class="text-sm text-base-content/60">
                        View transactions, process refunds, and manage payment settings.
                    </div>
                </div>
                <div class="p-4 border border-base-content/10 rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="icon-[tabler--settings] size-5 text-accent"></span>
                        <div class="font-medium">Settings</div>
                    </div>
                    <div class="text-sm text-base-content/60">
                        Manage studio settings, team members, billing, and integrations.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Permissions Modal --}}
<div id="permissions-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 opacity-0 pointer-events-none transition-opacity duration-200 overflow-y-auto py-8">
    <div class="card bg-base-100 w-full max-w-3xl mx-4 transform scale-95 transition-transform duration-200">
        <div class="card-body p-0">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-6 pb-4 border-b border-base-content/10">
                <div>
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <span class="icon-[tabler--shield-cog] size-5 text-primary"></span>
                        Customize Permissions
                    </h3>
                    <p class="text-base-content/60 text-sm mt-1">
                        Configuring access for <span id="permissions-user-name" class="font-semibold text-primary"></span>
                        <span id="permissions-user-role" class="badge badge-soft badge-sm ml-1"></span>
                    </p>
                </div>
                <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closePermissionsModal()">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>

            <form id="permissions-form" method="POST">
                @csrf
                @method('PUT')

                {{-- Permissions Grid --}}
                <div class="max-h-[60vh] overflow-y-auto p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                'schedule' => 'text-primary',
                                'bookings' => 'text-secondary',
                                'students' => 'text-info',
                                'offers' => 'text-warning',
                                'insights' => 'text-accent',
                                'payments' => 'text-success',
                                'studio' => 'text-primary',
                                'team' => 'text-secondary',
                                'billing' => 'text-info',
                            ];
                        @endphp

                        @foreach($groupedPermissions as $category => $permissions)
                        <div class="bg-base-200/50 rounded-xl p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="{{ $categoryIcons[$category] ?? 'icon-[tabler--settings]' }} size-5 {{ $categoryColors[$category] ?? 'text-base-content' }}"></span>
                                <h4 class="font-semibold text-sm">{{ ucfirst($category) }}</h4>
                                <button type="button" class="ml-auto text-xs text-primary hover:underline" onclick="toggleCategory('{{ $category }}')">
                                    Toggle all
                                </button>
                            </div>
                            <div class="space-y-2">
                                @foreach($permissions as $permission => $label)
                                <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-100 cursor-pointer transition-colors group">
                                    <input type="checkbox"
                                           name="permissions[]"
                                           value="{{ $permission }}"
                                           class="checkbox checkbox-primary checkbox-sm permission-checkbox"
                                           data-permission="{{ $permission }}"
                                           data-category="{{ $category }}" />
                                    <span class="text-sm group-hover:text-base-content">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-between p-6 pt-4 border-t border-base-content/10 bg-base-200/30">
                    <button type="button" class="btn btn-ghost btn-sm gap-2" onclick="resetToRoleDefaults()">
                        <span class="icon-[tabler--refresh] size-4"></span>
                        Reset to Defaults
                    </button>
                    <div class="flex gap-2">
                        <button type="button" class="btn btn-ghost" onclick="closePermissionsModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary gap-2">
                            <span class="icon-[tabler--check] size-4"></span>
                            Save Permissions
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Store users data
const usersData = @json($users->keyBy('id'));
const roleDefaults = {
    admin: @json(\App\Models\User::getDefaultPermissionsForRole('admin')),
    staff: @json(\App\Models\User::getDefaultPermissionsForRole('staff')),
    instructor: @json(\App\Models\User::getDefaultPermissionsForRole('instructor'))
};

const roleBadgeClasses = {
    admin: 'badge-secondary',
    staff: 'badge-info',
    instructor: 'badge-accent'
};

let currentUserId = null;

function openPermissionsModal(userId) {
    const user = usersData[userId];
    if (!user) return;

    currentUserId = userId;

    // Set form action
    document.getElementById('permissions-form').action = '/settings/team/permissions/' + userId;

    // Set user name and role badge
    document.getElementById('permissions-user-name').textContent = user.full_name;
    const roleBadge = document.getElementById('permissions-user-role');
    roleBadge.textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    roleBadge.className = 'badge badge-soft badge-sm ml-1 ' + (roleBadgeClasses[user.role] || '');

    // Get permissions to check - handle both array formats
    let permissions = [];
    if (user.permissions && typeof user.permissions === 'object') {
        if (Array.isArray(user.permissions)) {
            permissions = user.permissions;
        } else {
            // Handle object format {permission: true/false}
            permissions = Object.keys(user.permissions).filter(k => user.permissions[k]);
        }
    }

    // If no custom permissions, use role defaults
    if (permissions.length === 0) {
        permissions = roleDefaults[user.role] || [];
    }

    // Update checkboxes
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.checked = permissions.includes(checkbox.dataset.permission);
    });

    // Show modal
    var modal = document.getElementById('permissions-modal');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100', 'pointer-events-auto');
    modal.querySelector('.card').classList.remove('scale-95');
    modal.querySelector('.card').classList.add('scale-100');
}

function closePermissionsModal() {
    var modal = document.getElementById('permissions-modal');
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100', 'pointer-events-auto');
    modal.querySelector('.card').classList.add('scale-95');
    modal.querySelector('.card').classList.remove('scale-100');
}

function resetToRoleDefaults() {
    if (!currentUserId) return;

    const user = usersData[currentUserId];
    if (!user) return;

    const defaults = roleDefaults[user.role] || [];

    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.checked = defaults.includes(checkbox.dataset.permission);
    });
}

function toggleCategory(category) {
    const checkboxes = document.querySelectorAll('.permission-checkbox[data-category="' + category + '"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);

    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePermissionsModal();
    }
});

// Close modal when clicking backdrop
document.getElementById('permissions-modal').addEventListener('click', function(e) {
    if (e.target === this) closePermissionsModal();
});
</script>
@endpush
