@extends('layouts.settings')

@section('title', 'Invite User — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.team.users') }}">Users & Roles</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Invite User</li>
    </ol>
@endsection

@section('settings-content')
<<<<<<< Updated upstream
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Form Column --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Flash Messages --}}
        @if(session('error'))
        <div class="alert alert-soft alert-error">
            <span class="icon-[tabler--alert-circle] size-5"></span>
            <span>{{ session('error') }}</span>
=======
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-4">
        <a href="{{ route('settings.team.users') }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Add Team Member</h1>
            <p class="text-base-content/60 mt-1">Invite a new team member or add an existing user.</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('error'))
    <div class="alert alert-soft alert-error">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <form action="{{ route('settings.team.invite') }}" method="POST">
        @csrf

        {{-- Step Tab Navigation --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-0">
                <nav class="flex overflow-x-auto border-b border-base-content/10" id="step-tabs">
                    <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-primary text-primary" data-step="1" onclick="goToStep(1)">
                        <span class="icon-[tabler--user] size-5 mr-2 inline-block align-middle"></span>
                        <span class="hidden sm:inline">1.</span> Profile
                    </button>
                    <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="2" onclick="goToStep(2)">
                        <span class="icon-[tabler--briefcase] size-5 mr-2 inline-block align-middle"></span>
                        <span class="hidden sm:inline">2.</span> Employment
                    </button>
                    <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="3" onclick="goToStep(3)">
                        <span class="icon-[tabler--chart-bar] size-5 mr-2 inline-block align-middle"></span>
                        <span class="hidden sm:inline">3.</span> Workload
                    </button>
                    <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="4" onclick="goToStep(4)">
                        <span class="icon-[tabler--calendar-week] size-5 mr-2 inline-block align-middle"></span>
                        <span class="hidden sm:inline">4.</span> Days
                    </button>
                    <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="5" onclick="goToStep(5)">
                        <span class="icon-[tabler--clock] size-5 mr-2 inline-block align-middle"></span>
                        <span class="hidden sm:inline">5.</span> Hours
                    </button>
                    <button type="button" class="step-tab flex-1 min-w-max px-6 py-4 text-sm font-medium text-center border-b-2 border-transparent text-base-content/60 hover:text-base-content" data-step="6" onclick="goToStep(6)">
                        <span class="icon-[tabler--shield-cog] size-5 mr-2 inline-block align-middle"></span>
                        <span class="hidden sm:inline">6.</span> Permissions
                    </button>
                </nav>
            </div>
>>>>>>> Stashed changes
        </div>
        @endif

        <form action="{{ route('settings.team.invite') }}" method="POST">
            @csrf

            {{-- Basic Info --}}
            <div class="card bg-base-100 mb-6">
                <div class="card-header">
                    <h3 class="card-title flex items-center gap-2">
                        <span class="icon-[tabler--user-plus] size-5 text-primary"></span>
                        Invite Team Member
                    </h3>
                    <p class="text-base-content/60 text-sm">Send an invitation to join your studio</p>
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <label class="label-text" for="email">Email Address <span class="text-error">*</span></label>
                        <input type="email" id="email" name="email" class="input w-full @error('email') input-error @enderror"
                            required placeholder="colleague@example.com" value="{{ old('email') }}" />
                        @error('email')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
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
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="staff" {{ old('role', 'staff') == 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="instructor" {{ old('role') == 'instructor' ? 'selected' : '' }}>Instructor</option>
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
                                           {{ in_array($permission, old('permissions', [])) ? 'checked' : '' }} />
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
                    <span class="icon-[tabler--send] size-5"></span>
                    Send Invitation
                </button>
                <a href="{{ route('settings.team.users') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>

    {{-- Sidebar Column --}}
    <div class="space-y-6">
        {{-- How it works --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title text-sm">How invitations work</h3>
            </div>
            <div class="card-body pt-0">
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-medium">1</div>
                        <div>
                            <div class="font-medium text-sm">Send Invitation</div>
                            <p class="text-xs text-base-content/60">An email with a secure link is sent to the recipient</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-medium">2</div>
                        <div>
                            <div class="font-medium text-sm">Accept & Create Account</div>
                            <p class="text-xs text-base-content/60">They click the link and set up their password</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-medium">3</div>
                        <div>
                            <div class="font-medium text-sm">Access Granted</div>
                            <p class="text-xs text-base-content/60">They can now log in with their assigned permissions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Role Guide --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title text-sm">Role Guide</h3>
            </div>
            <div class="card-body pt-0 space-y-3">
                <div class="p-3 rounded-lg border border-secondary/20 bg-secondary/5" id="role-info-admin">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="icon-[tabler--shield] size-4 text-secondary"></span>
                        <span class="font-medium text-sm">Admin</span>
                    </div>
                    <p class="text-xs text-base-content/60">Full access to manage scheduling, bookings, students, instructors, and team settings. Cannot access billing.</p>
                </div>
                <div class="p-3 rounded-lg border border-info/20 bg-info/5" id="role-info-staff">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="icon-[tabler--user] size-4 text-info"></span>
                        <span class="font-medium text-sm">Staff</span>
                    </div>
                    <p class="text-xs text-base-content/60">Can manage daily operations like bookings, check-ins, and student management. Limited settings access.</p>
                </div>
                <div class="p-3 rounded-lg border border-accent/20 bg-accent/5" id="role-info-instructor">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="icon-[tabler--yoga] size-4 text-accent"></span>
                        <span class="font-medium text-sm">Instructor</span>
                    </div>
                    <p class="text-xs text-base-content/60">Can view their own schedule and mark attendance for classes they teach. Very limited dashboard access.</p>
                </div>
            </div>
        </div>

        {{-- Info Alert --}}
        <div class="alert alert-soft alert-info">
            <span class="icon-[tabler--clock] size-5"></span>
            <div>
                <div class="font-medium text-sm">Link expires in 7 days</div>
                <p class="text-xs">You can resend the invitation if it expires</p>
            </div>
        </div>

        {{-- Quick Tips --}}
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
                        <span>Use Staff role for front-desk employees</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="icon-[tabler--check] size-4 text-success shrink-0 mt-0.5"></span>
                        <span>Instructors get automatic access to their own classes</span>
                    </li>
                </ul>
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

// Highlight selected role in sidebar
function highlightSelectedRole(role) {
    document.querySelectorAll('[id^="role-info-"]').forEach(function(el) {
        el.classList.remove('ring-2', 'ring-primary');
    });
    var selectedEl = document.getElementById('role-info-' + role);
    if (selectedEl) {
        selectedEl.classList.add('ring-2', 'ring-primary');
    }
}

// Update permissions based on role selection
function updatePermissionsForRole() {
    var role = document.getElementById('role').value;
    var defaults = roleDefaults[role] || [];

    document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
        checkbox.checked = defaults.includes(checkbox.dataset.permission);
    });

    updateAllCategoryCounts();
    highlightSelectedRole(role);
}

function resetToRoleDefaults() {
    updatePermissionsForRole();
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
    var roleSelect = document.getElementById('role');

    // Listen for native change event
    roleSelect.addEventListener('change', updatePermissionsForRole);

    // Also observe for HSSelect mutations
    var roleObserver = new MutationObserver(updatePermissionsForRole);
    roleObserver.observe(roleSelect, { attributes: true, childList: true, subtree: true });

    // Set initial permissions based on role
    updatePermissionsForRole();
});
</script>
@endpush
@endsection
