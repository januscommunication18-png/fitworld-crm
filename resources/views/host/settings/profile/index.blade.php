@extends('layouts.settings')

@section('title', 'My Profile — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">My Profile</li>
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

    {{-- Profile Header Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                {{-- Profile Photo --}}
                <div class="relative">
                    <div class="avatar {{ $user->profile_photo ? '' : 'placeholder' }}">
                        @if($user->profile_photo)
                            <div class="w-24 h-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                                <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->full_name }}" />
                            </div>
                        @else
                            @php
                                $bgColor = match($role) {
                                    'owner' => 'bg-primary text-primary-content',
                                    'admin' => 'bg-secondary text-secondary-content',
                                    'staff' => 'bg-info text-info-content',
                                    'instructor' => 'bg-accent text-accent-content',
                                    default => 'bg-base-300 text-base-content'
                                };
                            @endphp
                            <div class="{{ $bgColor }} w-24 h-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2 flex items-center justify-center text-2xl font-bold">
                                {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <button type="button" onclick="document.getElementById('photo-input').click()"
                        class="absolute bottom-0 right-0 btn btn-circle btn-sm btn-primary">
                        <span class="icon-[tabler--camera] size-4"></span>
                    </button>
                    <input type="file" id="photo-input" accept="image/*" class="hidden" onchange="uploadPhoto(this)">
                </div>

                {{-- Profile Info --}}
                <div class="flex-1">
                    <h2 class="text-xl font-bold">{{ $user->full_name }}</h2>
                    <p class="text-base-content/60">{{ $user->email }}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        @php
                            $roleBadge = match($role) {
                                'owner' => 'badge-primary',
                                'admin' => 'badge-secondary',
                                'staff' => 'badge-info',
                                'instructor' => 'badge-accent',
                                default => 'badge-ghost'
                            };
                            $roleIcon = match($role) {
                                'owner' => 'icon-[tabler--crown]',
                                'admin' => 'icon-[tabler--shield]',
                                'staff' => 'icon-[tabler--user]',
                                'instructor' => 'icon-[tabler--yoga]',
                                default => 'icon-[tabler--user]'
                            };
                        @endphp
                        <span class="badge {{ $roleBadge }} badge-soft gap-1">
                            <span class="{{ $roleIcon }} size-3.5"></span>
                            {{ ucfirst($role) }}
                        </span>
                        <span class="text-base-content/40">at</span>
                        <span class="font-medium">{{ $host->studio_name }}</span>
                    </div>
                </div>

                {{-- Remove Photo Button --}}
                @if($user->profile_photo)
                <button type="button" onclick="removePhoto()" class="btn btn-ghost btn-sm text-error">
                    <span class="icon-[tabler--trash] size-4"></span>
                    Remove Photo
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Personal Information Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Personal Information</h2>
                    <p class="text-base-content/60 text-sm">Your basic account information</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openDrawer('edit-profile-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> Edit
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">First Name</label>
                    <p class="font-medium" id="display-first-name">{{ $user->first_name }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Last Name</label>
                    <p class="font-medium" id="display-last-name">{{ $user->last_name }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Email Address</label>
                    <p class="font-medium" id="display-email">{{ $user->email }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Phone Number</label>
                    <p class="font-medium" id="display-phone">{{ $user->phone ?? 'Not set' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Instructor Profile Card (if applicable) --}}
    @if($instructor)
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <span class="icon-[tabler--yoga] size-5 text-accent"></span>
                        Instructor Profile
                    </h2>
                    <p class="text-base-content/60 text-sm">Your public instructor information shown to students</p>
                </div>
                <a href="{{ route('settings.team.instructors.edit', $instructor) }}" class="btn btn-accent btn-sm btn-soft">
                    <span class="icon-[tabler--external-link] size-4"></span> View Full Profile
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Display Name</label>
                    <p class="font-medium">{{ $instructor->name }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Specialties</label>
                    <div class="flex flex-wrap gap-1">
                        @if($instructor->specialties && count($instructor->specialties) > 0)
                            @foreach($instructor->specialties as $specialty)
                                <span class="badge badge-accent badge-soft badge-sm">{{ $specialty }}</span>
                            @endforeach
                        @else
                            <span class="text-base-content/50">Not set</span>
                        @endif
                    </div>
                </div>

                <div class="space-y-1 md:col-span-2">
                    <label class="text-sm text-base-content/60">Bio</label>
                    <p class="font-medium">{{ $instructor->bio ?? 'Not set' }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Security Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Security</h2>
                    <p class="text-base-content/60 text-sm">Manage your password</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openDrawer('change-password-drawer')">
                    <span class="icon-[tabler--lock] size-4"></span> Change Password
                </button>
            </div>

            <div class="space-y-1">
                <label class="text-sm text-base-content/60">Password</label>
                <p class="font-medium">••••••••••••</p>
            </div>
        </div>
    </div>

    {{-- Role & Permissions Card (Read-only) --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="mb-6">
                <h2 class="text-lg font-semibold">Role & Permissions</h2>
                <p class="text-base-content/60 text-sm">Your access level at {{ $host->studio_name }}</p>
            </div>

            <div class="space-y-4">
                <div class="space-y-1">
                    <label class="text-sm text-base-content/60">Current Role</label>
                    <p class="font-medium flex items-center gap-2">
                        <span class="{{ $roleIcon }} size-5"></span>
                        {{ ucfirst($role) }}
                    </p>
                </div>

                @if($role !== 'owner')
                <div class="space-y-2">
                    <label class="text-sm text-base-content/60">Permissions</label>
                    @php
                        $defaultPerms = \App\Models\User::getDefaultPermissionsForRole($role);
                        // Handle permissions that might be JSON string or array
                        $permsArray = $permissions;
                        if (is_string($permissions)) {
                            $permsArray = json_decode($permissions, true) ?? [];
                        }
                        $permsArray = is_array($permsArray) ? $permsArray : [];
                        $effectivePerms = !empty($permsArray) ? array_keys(array_filter($permsArray)) : $defaultPerms;
                        $hasCustom = !empty($permsArray);
                    @endphp

                    @if($hasCustom)
                        <p class="text-sm text-primary">
                            <span class="icon-[tabler--adjustments] size-4 inline"></span>
                            You have custom permissions assigned
                        </p>
                    @else
                        <p class="text-sm text-base-content/60">Using default {{ ucfirst($role) }} permissions</p>
                    @endif

                    <div class="flex flex-wrap gap-1 mt-2">
                        @foreach($effectivePerms as $perm)
                            @php
                                $allPerms = \App\Models\User::getAllPermissions();
                                $label = null;
                                foreach ($allPerms as $category => $perms) {
                                    if (isset($perms[$perm])) {
                                        $label = $perms[$perm];
                                        break;
                                    }
                                }
                            @endphp
                            @if($label)
                                <span class="badge badge-ghost badge-sm">{{ $label }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
                @else
                <div class="alert alert-soft alert-primary">
                    <span class="icon-[tabler--crown] size-5"></span>
                    <span>As the studio owner, you have full access to all features.</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Drawer Backdrop --}}
<div id="drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none transition-opacity duration-300" onclick="closeAllDrawers()"></div>

{{-- Edit Profile Drawer --}}
<div id="edit-profile-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Edit Personal Information</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-profile-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="profile-form" onsubmit="saveProfile(event)" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4">
            <div class="space-y-4">
                <div>
                    <label class="label-text" for="first_name">First Name <span class="text-error">*</span></label>
                    <input type="text" id="first_name" name="first_name" value="{{ $user->first_name }}" class="input w-full" required>
                    <span class="error-message text-error text-sm hidden"></span>
                </div>

                <div>
                    <label class="label-text" for="last_name">Last Name <span class="text-error">*</span></label>
                    <input type="text" id="last_name" name="last_name" value="{{ $user->last_name }}" class="input w-full" required>
                    <span class="error-message text-error text-sm hidden"></span>
                </div>

                <div>
                    <label class="label-text" for="email">Email Address <span class="text-error">*</span></label>
                    <input type="email" id="email" name="email" value="{{ $user->email }}" class="input w-full" required>
                    <span class="error-message text-error text-sm hidden"></span>
                </div>

                <div>
                    <label class="label-text" for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="{{ $user->phone }}" class="input w-full" placeholder="Optional">
                    <span class="error-message text-error text-sm hidden"></span>
                </div>
            </div>
        </div>
        <div class="p-4 border-t border-base-200 flex justify-end gap-2">
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-profile-drawer')">Cancel</button>
            <button type="submit" class="btn btn-primary" id="save-profile-btn">
                <span class="loading loading-spinner loading-sm hidden"></span>
                Save Changes
            </button>
        </div>
    </form>
</div>

{{-- Change Password Drawer --}}
<div id="change-password-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Change Password</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('change-password-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="password-form" onsubmit="savePassword(event)" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4">
            <div class="space-y-4">
                <div>
                    <label class="label-text" for="current_password">Current Password <span class="text-error">*</span></label>
                    <input type="password" id="current_password" name="current_password" class="input w-full" required>
                    <span class="error-message text-error text-sm hidden"></span>
                </div>

                <div>
                    <label class="label-text" for="password">New Password <span class="text-error">*</span></label>
                    <input type="password" id="password" name="password" class="input w-full" required minlength="8">
                    <p class="text-sm text-base-content/60 mt-1">Minimum 8 characters</p>
                    <span class="error-message text-error text-sm hidden"></span>
                </div>

                <div>
                    <label class="label-text" for="password_confirmation">Confirm New Password <span class="text-error">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="input w-full" required>
                    <span class="error-message text-error text-sm hidden"></span>
                </div>
            </div>
        </div>
        <div class="p-4 border-t border-base-200 flex justify-end gap-2">
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('change-password-drawer')">Cancel</button>
            <button type="submit" class="btn btn-primary" id="save-password-btn">
                <span class="loading loading-spinner loading-sm hidden"></span>
                Update Password
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function openDrawer(id) {
    const drawer = document.getElementById(id);
    const backdrop = document.getElementById('drawer-backdrop');

    // Show backdrop
    backdrop.classList.remove('opacity-0', 'pointer-events-none');
    backdrop.classList.add('opacity-100');

    // Slide in drawer
    drawer.classList.remove('translate-x-full');
}

function closeDrawer(id) {
    const drawer = document.getElementById(id);
    const backdrop = document.getElementById('drawer-backdrop');

    // Hide backdrop
    backdrop.classList.add('opacity-0', 'pointer-events-none');
    backdrop.classList.remove('opacity-100');

    // Slide out drawer
    drawer.classList.add('translate-x-full');
}

function closeAllDrawers() {
    closeDrawer('edit-profile-drawer');
    closeDrawer('change-password-drawer');
}

function clearErrors(formId) {
    document.querySelectorAll('#' + formId + ' .error-message').forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
    });
    document.querySelectorAll('#' + formId + ' .input').forEach(el => {
        el.classList.remove('input-error');
    });
}

function showErrors(formId, errors) {
    for (const [field, messages] of Object.entries(errors)) {
        const input = document.querySelector('#' + formId + ' [name="' + field + '"]');
        const errorEl = input?.nextElementSibling?.classList.contains('error-message')
            ? input.nextElementSibling
            : input?.parentElement.querySelector('.error-message');
        if (input && errorEl) {
            input.classList.add('input-error');
            errorEl.textContent = messages[0];
            errorEl.classList.remove('hidden');
        }
    }
}

async function saveProfile(e) {
    e.preventDefault();
    clearErrors('profile-form');

    const btn = document.getElementById('save-profile-btn');
    const spinner = btn.querySelector('.loading');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    try {
        const response = await fetch('{{ route("settings.profile.update") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        });

        const result = await response.json();

        if (response.ok && result.success) {
            // Update displayed values
            document.getElementById('display-first-name').textContent = data.first_name;
            document.getElementById('display-last-name').textContent = data.last_name;
            document.getElementById('display-email').textContent = data.email;
            document.getElementById('display-phone').textContent = data.phone || 'Not set';

            closeDrawer('edit-profile-drawer');

            // Show success toast
            if (typeof Notyf !== 'undefined') {
                new Notyf().success(result.message);
            }
        } else {
            if (result.errors) {
                showErrors('profile-form', result.errors);
            }
        }
    } catch (error) {
        console.error('Error:', error);
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
}

async function savePassword(e) {
    e.preventDefault();
    clearErrors('password-form');

    const btn = document.getElementById('save-password-btn');
    const spinner = btn.querySelector('.loading');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    try {
        const response = await fetch('{{ route("settings.profile.password") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        });

        const result = await response.json();

        if (response.ok && result.success) {
            e.target.reset();
            closeDrawer('change-password-drawer');

            if (typeof Notyf !== 'undefined') {
                new Notyf().success(result.message);
            }
        } else {
            if (result.errors) {
                showErrors('password-form', result.errors);
            }
        }
    } catch (error) {
        console.error('Error:', error);
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
}

async function uploadPhoto(input) {
    if (!input.files || !input.files[0]) return;

    const formData = new FormData();
    formData.append('photo', input.files[0]);

    try {
        const response = await fetch('{{ route("settings.profile.photo") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData,
        });

        const result = await response.json();

        if (response.ok && result.success) {
            location.reload();
        } else {
            if (typeof Notyf !== 'undefined') {
                new Notyf().error(result.message || 'Failed to upload photo');
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function removePhoto() {
    if (!confirm('Are you sure you want to remove your profile photo?')) return;

    try {
        const response = await fetch('{{ route("settings.profile.photo.remove") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });

        const result = await response.json();

        if (response.ok && result.success) {
            location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}
</script>
@endpush
@endsection
