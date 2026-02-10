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

    {{-- Overview Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center">
                        <span class="icon-[tabler--shield] size-5 text-secondary"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $roleCounts['admin'] ?? 0 }}</div>
                        <div class="text-xs text-base-content/60">Admins</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center">
                        <span class="icon-[tabler--user] size-5 text-info"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $roleCounts['staff'] ?? 0 }}</div>
                        <div class="text-xs text-base-content/60">Staff</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center">
                        <span class="icon-[tabler--yoga] size-5 text-accent"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $roleCounts['instructor'] ?? 0 }}</div>
                        <div class="text-xs text-base-content/60">Instructors</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--adjustments] size-5 text-primary"></span>
                    </div>
                    <div>
                        @php
                            $customCount = $users->filter(fn($u) => !empty($u->permissions))->count();
                        @endphp
                        <div class="text-2xl font-bold">{{ $customCount }}</div>
                        <div class="text-xs text-base-content/60">Custom Overrides</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Default Role Permissions - Compact Summary --}}
    <div class="card bg-base-100">
        <details class="group" id="defaults-details">
            <summary class="flex items-center justify-between p-5 cursor-pointer list-none hover:bg-base-200/50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--list-check] size-5 text-primary"></span>
                    </div>
                    <div>
                        <h3 class="font-semibold">Default Role Permissions</h3>
                        <p class="text-base-content/60 text-sm">Reference for what each role can access by default</p>
                    </div>
                </div>
                <span class="btn btn-sm btn-ghost gap-1 group-open:hidden">
                    <span class="icon-[tabler--eye] size-4"></span>
                    View Permissions
                </span>
                <span class="btn btn-sm btn-ghost gap-1 hidden group-open:inline-flex">
                    <span class="icon-[tabler--eye-off] size-4"></span>
                    Hide
                </span>
            </summary>
            <div class="card-body border-t border-base-content/10">
                {{-- Role Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    @php
                        $ownerPerms = \App\Models\User::getDefaultPermissionsForRole('owner');
                        $adminPerms = \App\Models\User::getDefaultPermissionsForRole('admin');
                        $staffPerms = \App\Models\User::getDefaultPermissionsForRole('staff');
                        $instructorPerms = \App\Models\User::getDefaultPermissionsForRole('instructor');
                    @endphp
                    <div class="p-4 rounded-lg border border-secondary/20 bg-secondary/5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="icon-[tabler--shield] size-5 text-secondary"></span>
                            <span class="font-semibold">Admin</span>
                            <span class="badge badge-secondary badge-soft badge-sm ml-auto">{{ count($adminPerms) }} permissions</span>
                        </div>
                        <p class="text-xs text-base-content/60">Full management access except billing. Can manage team, schedule, students, and all settings.</p>
                    </div>
                    <div class="p-4 rounded-lg border border-info/20 bg-info/5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="icon-[tabler--user] size-5 text-info"></span>
                            <span class="font-semibold">Staff</span>
                            <span class="badge badge-info badge-soft badge-sm ml-auto">{{ count($staffPerms) }} permissions</span>
                        </div>
                        <p class="text-xs text-base-content/60">Daily operations access. Can manage bookings, check-ins, and view student info.</p>
                    </div>
                    <div class="p-4 rounded-lg border border-accent/20 bg-accent/5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="icon-[tabler--yoga] size-5 text-accent"></span>
                            <span class="font-semibold">Instructor</span>
                            <span class="badge badge-accent badge-soft badge-sm ml-auto">{{ count($instructorPerms) }} permissions</span>
                        </div>
                        <p class="text-xs text-base-content/60">Limited access. Can view own schedule and mark attendance for assigned classes.</p>
                    </div>
                </div>

                {{-- Detailed Permission Table --}}
                <div class="overflow-x-auto border border-base-content/10 rounded-lg">
                    <table class="table table-sm table-zebra">
                        <thead class="bg-base-200">
                            <tr>
                                <th class="font-semibold">Permission</th>
                                <th class="text-center w-24">
                                    <span class="flex items-center justify-center gap-1">
                                        <span class="icon-[tabler--shield] size-4 text-secondary"></span>
                                        Admin
                                    </span>
                                </th>
                                <th class="text-center w-24">
                                    <span class="flex items-center justify-center gap-1">
                                        <span class="icon-[tabler--user] size-4 text-info"></span>
                                        Staff
                                    </span>
                                </th>
                                <th class="text-center w-24">
                                    <span class="flex items-center justify-center gap-1">
                                        <span class="icon-[tabler--yoga] size-4 text-accent"></span>
                                        Instructor
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $currentCategory = ''; @endphp
                            @foreach($allPermissions as $permission => $label)
                                @php
                                    $parts = explode('.', $permission);
                                    $category = ucfirst($parts[0]);
                                @endphp
                                @if($category !== $currentCategory)
                                    @php $currentCategory = $category; @endphp
                                    <tr>
                                        <td colspan="4" class="bg-base-300/50 font-semibold text-sm py-2">
                                            <span class="flex items-center gap-2">
                                                @php
                                                    $catIcon = match(strtolower($category)) {
                                                        'schedule' => 'icon-[tabler--calendar]',
                                                        'bookings' => 'icon-[tabler--clipboard-list]',
                                                        'students' => 'icon-[tabler--users]',
                                                        'offers' => 'icon-[tabler--tag]',
                                                        'insights' => 'icon-[tabler--chart-bar]',
                                                        'payments' => 'icon-[tabler--credit-card]',
                                                        'studio' => 'icon-[tabler--building-store]',
                                                        'team' => 'icon-[tabler--users-group]',
                                                        'billing' => 'icon-[tabler--receipt]',
                                                        default => 'icon-[tabler--settings]'
                                                    };
                                                @endphp
                                                <span class="{{ $catIcon }} size-4"></span>
                                                {{ $category }}
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="text-sm pl-8">{{ $label }}</td>
                                    <td class="text-center">
                                        @if(in_array($permission, $adminPerms))
                                            <span class="icon-[tabler--check] size-5 text-success"></span>
                                        @else
                                            <span class="icon-[tabler--minus] size-5 text-base-content/20"></span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(in_array($permission, $staffPerms))
                                            <span class="icon-[tabler--check] size-5 text-success"></span>
                                        @else
                                            <span class="icon-[tabler--minus] size-5 text-base-content/20"></span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(in_array($permission, $instructorPerms))
                                            <span class="icon-[tabler--check] size-5 text-success"></span>
                                        @else
                                            <span class="icon-[tabler--minus] size-5 text-base-content/20"></span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </details>
    </div>

    {{-- User Permission Overrides --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <div>
                <h3 class="card-title flex items-center gap-2">
                    <span class="icon-[tabler--shield-cog] size-5 text-primary"></span>
                    User Permission Overrides
                </h3>
                <p class="text-base-content/60 text-sm mt-1">Customize permissions for individual team members</p>
            </div>
        </div>
        <div class="card-body">
            {{-- Search --}}
            <div class="flex flex-col sm:flex-row gap-4 mb-6">
                <div class="flex-1">
                    <div class="relative">
                        <span class="icon-[tabler--search] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                        <input type="text" id="search-input" placeholder="Search by name or email..."
                            class="input w-full pl-10 pr-10" autocomplete="off" />
                        <button type="button" id="clear-search" class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/50 hover:text-base-content hidden">
                            <span class="icon-[tabler--x] size-5"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Results Info --}}
            <div class="text-sm text-base-content/60 mb-4" id="results-info">
                Showing <span id="showing-count">{{ $users->count() }}</span> of {{ $users->count() }} team members
            </div>

            {{-- No Results (hidden by default) --}}
            <div id="no-results" class="text-center py-12 hidden">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-base-200 rounded-full mb-4">
                    <span class="icon-[tabler--search-off] size-8 text-base-content/40"></span>
                </div>
                <h3 class="text-lg font-medium mb-2">No results found</h3>
                <p class="text-base-content/60 mb-4">No team members match your search</p>
                <button type="button" id="clear-search-empty" class="btn btn-ghost btn-sm gap-2">
                    <span class="icon-[tabler--x] size-4"></span>
                    Clear search
                </button>
            </div>

            {{-- Users Table --}}
            @if($users->count() > 0)
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Team Member</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Permissions</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        @foreach($users as $user)
                        <tr class="user-row {{ $user->trashed() ? 'opacity-60' : '' }}" data-search="{{ strtolower($user->full_name . ' ' . $user->email . ' ' . $user->role) }}">
                            <td>
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
                                        <div class="{{ $bgColor }} w-10 rounded-full {{ $user->trashed() ? 'grayscale' : '' }}">
                                            <span>{{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $user->full_name }}</div>
                                        <div class="text-sm text-base-content/60">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
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
                            </td>
                            <td>
                                @if($user->trashed())
                                    <span class="badge badge-error badge-soft gap-1">
                                        <span class="icon-[tabler--user-off] size-3.5"></span>
                                        Removed
                                    </span>
                                @elseif($user->status === 'suspended')
                                    <span class="badge badge-warning badge-soft gap-1">
                                        <span class="icon-[tabler--ban] size-3.5"></span>
                                        Suspended
                                    </span>
                                @elseif($user->status === 'deactivated')
                                    <span class="badge badge-ghost gap-1">
                                        <span class="icon-[tabler--user-pause] size-3.5"></span>
                                        Deactivated
                                    </span>
                                @else
                                    <span class="badge badge-success badge-soft gap-1">
                                        <span class="icon-[tabler--check] size-3.5"></span>
                                        Active
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $userPermissions = $user->permissions;
                                    $hasOverrides = !empty($userPermissions);
                                @endphp
                                @if($hasOverrides)
                                    @php
                                        $permCount = is_array($userPermissions) ? count($userPermissions) : 0;
                                    @endphp
                                    <span class="badge badge-primary badge-soft">
                                        {{ $permCount }} custom permission{{ $permCount !== 1 ? 's' : '' }}
                                    </span>
                                @else
                                    <span class="text-base-content/50 text-sm">Using role defaults</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if(!$user->trashed())
                                <a href="{{ route('settings.team.permissions.edit', $user) }}" class="btn btn-sm btn-ghost gap-1">
                                    <span class="icon-[tabler--adjustments] size-4"></span>
                                    Customize
                                </a>
                                @else
                                <span class="text-base-content/40 text-sm">N/A</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @else
            <div class="text-center py-12" id="empty-state">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-base-200 rounded-full mb-4">
                    <span class="icon-[tabler--users] size-8 text-base-content/40"></span>
                </div>
                <h3 class="text-lg font-medium mb-2">No team members</h3>
                <p class="text-base-content/60">Add team members in Users & Roles to customize their permissions.</p>
                <a href="{{ route('settings.team.users') }}" class="btn btn-primary btn-sm mt-4 gap-2">
                    <span class="icon-[tabler--users] size-4"></span>
                    Go to Users & Roles
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Permission Categories Explanation --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <h3 class="card-title flex items-center gap-2">
                <span class="icon-[tabler--info-circle] size-5 text-primary"></span>
                Permission Categories
            </h3>
        </div>
        <div class="card-body">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const clearBtn = document.getElementById('clear-search');
    const clearBtnEmpty = document.getElementById('clear-search-empty');
    const rows = document.querySelectorAll('.user-row');
    const showingCount = document.getElementById('showing-count');
    const noResults = document.getElementById('no-results');
    const tableWrapper = document.querySelector('#users-table-body')?.closest('.overflow-x-auto');
    const totalCount = {{ $users->count() }};

    function performSearch() {
        const query = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        rows.forEach(function(row) {
            const searchData = row.dataset.search || '';
            if (query === '' || searchData.includes(query)) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });

        // Update showing count
        if (showingCount) {
            showingCount.textContent = visibleCount;
        }

        // Show/hide clear button
        if (clearBtn) {
            if (query) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
        }

        // Show/hide no results message
        if (visibleCount === 0 && query !== '') {
            if (noResults) noResults.classList.remove('hidden');
            if (tableWrapper) tableWrapper.classList.add('hidden');
        } else {
            if (noResults) noResults.classList.add('hidden');
            if (tableWrapper) tableWrapper.classList.remove('hidden');
        }
    }

    function clearSearch() {
        searchInput.value = '';
        performSearch();
        searchInput.focus();
    }

    if (searchInput) {
        searchInput.addEventListener('input', performSearch);
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                clearSearch();
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', clearSearch);
    }

    if (clearBtnEmpty) {
        clearBtnEmpty.addEventListener('click', clearSearch);
    }
});
</script>
@endpush
@endsection
