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
                                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->full_name }}" />
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

                <div class="flex items-center gap-2 ml-auto">
                    @if($user->isOwner() || $user->hasPermission('team.instructor_admin'))
                    <a href="{{ route('settings.team.users.edit', ['user' => $user->id, 'section' => 'employment']) }}"
                        class="btn btn-ghost btn-sm">
                        <span class="icon-[tabler--shield-cog] size-4"></span>
                        Manage as admin
                    </a>
                    @endif

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

                <div class="space-y-1 md:col-span-2">
                    <label class="text-sm text-base-content/60">Bio</label>
                    <p class="whitespace-pre-line {{ $user->bio ? 'font-medium' : 'text-base-content/40 italic' }}" id="display-bio">
                        {{ $user->bio ?? 'Not set' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
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

    {{-- Personal Override Code Card (for users with override permission) --}}
    @if($user->canApprovePriceOverride($host))
    @php
        $personalOverrideCode = $user->getPersonalOverrideCode($host);
    @endphp
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <span class="icon-[tabler--key] size-5 text-primary"></span>
                        Personal Override Code
                    </h2>
                    <p class="text-base-content/60 text-sm">Use this code to authorize price changes during walk-in bookings</p>
                </div>
            </div>

            <div class="bg-primary/5 border border-primary/20 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm text-base-content/60">Your Personal Code</label>
                        <div class="flex items-center gap-3">
                            @if($personalOverrideCode)
                            <p class="text-2xl font-mono font-bold text-primary tracking-wider" id="personal-code-display">
                                ••••••••
                            </p>
                            <input type="hidden" id="personal-code-value" value="{{ $personalOverrideCode }}">
                            @else
                            <p class="text-2xl font-mono font-bold text-base-content/40" id="personal-code-display">Not assigned</p>
                            <input type="hidden" id="personal-code-value" value="">
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2" id="override-code-actions">
                        @if($personalOverrideCode)
                        <button type="button" onclick="toggleCodeVisibility()" id="toggle-code-btn" class="btn btn-ghost btn-sm btn-circle" title="Show code">
                            <span class="icon-[tabler--eye] size-5" id="toggle-code-icon"></span>
                        </button>
                        <button type="button" onclick="copyOverrideCode()" id="copy-code-btn" class="btn btn-primary btn-sm hidden">
                            <span class="icon-[tabler--copy] size-4"></span>
                            Copy
                        </button>
                        <button type="button" onclick="regenerateOverrideCode()" id="regenerate-code-btn" class="btn btn-ghost btn-sm" title="Regenerate code">
                            <span class="icon-[tabler--refresh] size-4"></span>
                        </button>
                        @else
                        <button type="button" onclick="generateOverrideCode()" id="generate-code-btn" class="btn btn-primary btn-sm">
                            <span class="icon-[tabler--key] size-4"></span>
                            Generate Code
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-4 text-sm text-base-content/60">
                <p class="flex items-start gap-2">
                    <span class="icon-[tabler--info-circle] size-4 mt-0.5 shrink-0"></span>
                    <span>When staff is making a booking and you want to change the price, enter this code in the Price Override section. The price will be changed immediately without needing approval.</span>
                </p>
            </div>
        </div>
    </div>

    <script>
    let codeVisible = false;
    let hideTimeout = null;

    function toggleCodeVisibility() {
        const display = document.getElementById('personal-code-display');
        const code = document.getElementById('personal-code-value').value;
        const icon = document.getElementById('toggle-code-icon');
        const copyBtn = document.getElementById('copy-code-btn');

        if (codeVisible) {
            // Hide the code
            hideCode();
        } else {
            // Show the code
            display.textContent = code;
            icon.className = 'icon-[tabler--eye-off] size-5';
            copyBtn.classList.remove('hidden');
            codeVisible = true;

            // Clear any existing timeout
            if (hideTimeout) {
                clearTimeout(hideTimeout);
            }

            // Auto-hide after 30 seconds
            hideTimeout = setTimeout(() => {
                hideCode();
            }, 30000);
        }
    }

    function hideCode() {
        const display = document.getElementById('personal-code-display');
        const icon = document.getElementById('toggle-code-icon');
        const copyBtn = document.getElementById('copy-code-btn');

        display.textContent = '••••••••';
        icon.className = 'icon-[tabler--eye] size-5';
        copyBtn.classList.add('hidden');
        codeVisible = false;

        if (hideTimeout) {
            clearTimeout(hideTimeout);
            hideTimeout = null;
        }
    }

    function copyOverrideCode() {
        const code = document.getElementById('personal-code-value').value;
        navigator.clipboard.writeText(code).then(() => {
            const btn = document.getElementById('copy-code-btn');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="icon-[tabler--check] size-4"></span> Copied!';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 2000);
        });
    }

    async function generateOverrideCode() {
        const btn = document.getElementById('generate-code-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Generating...';

        try {
            const response = await fetch('{{ route("price-override.personal-code") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            });

            const result = await response.json();

            if (result.success && result.code) {
                document.getElementById('personal-code-display').textContent = result.code;
                document.getElementById('personal-code-display').classList.remove('text-base-content/40');
                document.getElementById('personal-code-display').classList.add('text-primary');
                document.getElementById('personal-code-value').value = result.code;

                // Replace generate button with show/copy buttons
                btn.parentElement.innerHTML = '<button type="button" onclick="toggleCodeVisibility()" id="toggle-code-btn" class="btn btn-ghost btn-sm btn-circle" title="Show code"><span class="icon-[tabler--eye] size-5" id="toggle-code-icon"></span></button><button type="button" onclick="copyOverrideCode()" id="copy-code-btn" class="btn btn-primary btn-sm"><span class="icon-[tabler--copy] size-4"></span> Copy</button>';

                showToast('Override code generated successfully!', 'success');
            } else {
                showToast(result.message || 'Failed to generate code.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<span class="icon-[tabler--key] size-4"></span> Generate Code';
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<span class="icon-[tabler--key] size-4"></span> Generate Code';
        }
    }

    function regenerateOverrideCode() {
        showConfirmModal({
            title: 'Regenerate Override Code',
            message: 'This will replace your current code. Any saved references to the old code will stop working. Continue?',
            type: 'warning',
            btnText: 'Regenerate',
            btnIcon: 'icon-[tabler--refresh]',
            onConfirm: async function() {
                var btn = document.getElementById('regenerate-code-btn');
                btn.disabled = true;
                btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

                try {
                    var response = await fetch('{{ route("price-override.personal-code") }}?regenerate=1', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                    });

                    var result = await response.json();

                    if (result.success && result.code) {
                        document.getElementById('personal-code-display').textContent = result.code;
                        document.getElementById('personal-code-display').classList.remove('text-base-content/40');
                        document.getElementById('personal-code-display').classList.add('text-primary');
                        document.getElementById('personal-code-value').value = result.code;
                        codeVisible = true;

                        var copyBtn = document.getElementById('copy-code-btn');
                        if (copyBtn) copyBtn.classList.remove('hidden');
                        var icon = document.getElementById('toggle-code-icon');
                        if (icon) icon.className = 'icon-[tabler--eye-off] size-5';

                        showToast('Override code regenerated successfully!', 'success');
                    } else {
                        showToast(result.message || 'Failed to regenerate code.', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('An error occurred. Please try again.', 'error');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<span class="icon-[tabler--refresh] size-4"></span>';
                }
            }
        });
    }
    </script>
    @endif

    @php
        $dayOptions = \App\Models\Instructor::getDayOptions();
        $dayEmojis = ['☀️', '🌙', '🔥', '💧', '⚡', '🐟', '⭐'];
    @endphp

    {{-- Specialties --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold flex items-center gap-2">
                    <span class="icon-[tabler--sparkles] size-5 text-primary"></span>
                    Specialties
                </h2>
                <button type="button" class="btn btn-primary btn-sm" onclick="openDrawer('edit-specialties-drawer')">
                    <span class="icon-[tabler--edit] size-4"></span> Edit
                </button>
            </div>
            <div id="display-specialties">
                @if($instructor && !empty($instructor->specialties))
                    <div class="flex flex-wrap gap-2">
                        @foreach($instructor->specialties as $specialty)
                            <span class="badge badge-soft badge-primary">{{ $specialty }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-base-content/40 italic">No specialties added yet.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Employment Details (read-only) --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <span class="icon-[tabler--briefcase] size-5 text-secondary"></span>
                Employment Details
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                <div>
                    <label class="text-sm text-base-content/60">Employment Type</label>
                    <p class="font-medium">{{ $instructor?->getFormattedEmploymentType() ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Rate</label>
                    <p class="font-medium">{{ $instructor?->getFormattedRate() ?? '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm text-base-content/60">Compensation Notes</label>
                    @if($instructor && $instructor->compensation_notes)
                        <p class="text-sm whitespace-pre-line">{{ $instructor->compensation_notes }}</p>
                    @else
                        <p class="text-sm text-base-content/40 italic">No notes added.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Workload Limits (read-only) --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <span class="icon-[tabler--chart-bar] size-5 text-warning"></span>
                Workload Limits
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                <div>
                    <label class="text-sm text-base-content/60">Hours per Week</label>
                    <p class="font-medium">{{ $instructor?->hours_per_week ? number_format($instructor->hours_per_week, 1) . ' hrs' : '-' }}</p>
                </div>
                <div>
                    <label class="text-sm text-base-content/60">Max Classes per Week</label>
                    <p class="font-medium">{{ $instructor?->max_classes_per_week ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Working Days (read-only) --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <span class="icon-[tabler--calendar-week] size-5 text-accent"></span>
                Working Days
            </h2>
            <div class="mt-3">
                @if($instructor && !empty($instructor->working_days))
                    <div class="flex flex-wrap gap-2">
                        @foreach($dayOptions as $value => $label)
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm {{ in_array($value, $instructor->working_days) ? 'bg-primary/10 text-primary font-medium' : 'bg-base-200/50 text-base-content/40' }}">
                                <span>{{ $dayEmojis[$value] ?? '' }}</span>
                                <span>{{ substr($label, 0, 3) }}</span>
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-base-content/40 italic">No working days set.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Availability Hours (read-only) --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <span class="icon-[tabler--clock] size-5 text-info"></span>
                Availability Hours
            </h2>
            <div class="space-y-4 mt-3">
                <div>
                    <label class="text-sm text-base-content/60">Default Hours</label>
                    @if($instructor && $instructor->availability_default_from && $instructor->availability_default_to)
                        <p class="font-medium">
                            {{ \Carbon\Carbon::parse($instructor->availability_default_from)->format('g:i A') }}
                            —
                            {{ \Carbon\Carbon::parse($instructor->availability_default_to)->format('g:i A') }}
                        </p>
                    @else
                        <p class="text-sm text-base-content/40 italic">Not set.</p>
                    @endif
                </div>
                @if($instructor && !empty($instructor->availability_by_day))
                    @php
                        $hasOverrides = false;
                        foreach($instructor->availability_by_day as $day => $times) {
                            if (!empty($times['from']) && !empty($times['to'])) { $hasOverrides = true; break; }
                        }
                    @endphp
                    @if($hasOverrides)
                        <div class="border-t border-base-200 pt-4">
                            <label class="text-sm text-base-content/60 block mb-2">Day-Specific Overrides</label>
                            <div class="space-y-2">
                                @foreach($instructor->availability_by_day as $day => $times)
                                    @if(!empty($times['from']) && !empty($times['to']))
                                        <div class="flex items-center justify-between py-2 px-3 bg-base-200/30 rounded-lg">
                                            <span class="font-medium text-sm">{{ $dayOptions[$day] ?? "Day $day" }}</span>
                                            <span class="text-sm">
                                                {{ \Carbon\Carbon::parse($times['from'])->format('g:i A') }}
                                                —
                                                {{ \Carbon\Carbon::parse($times['to'])->format('g:i A') }}
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>


</div>

{{-- Drawer Backdrop --}}
<div id="drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none transition-opacity duration-300" onclick="closeAllDrawers()"></div>

{{-- Edit Profile Drawer --}}
<div id="edit-profile-drawer" class="fixed top-0 right-0 h-full w-full max-w-3xl bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
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
                    <input type="text" id="first_name" name="first_name" value="{{ $user->first_name }}" class="input w-full" required pattern="^[A-Za-z\s\-']+$" title="Name should only contain letters, spaces, hyphens, or apostrophes" oninput="this.value = this.value.replace(/[0-9]/g, '')">
                    <span class="error-message text-error text-sm hidden"></span>
                </div>

                <div>
                    <label class="label-text" for="last_name">Last Name <span class="text-error">*</span></label>
                    <input type="text" id="last_name" name="last_name" value="{{ $user->last_name }}" class="input w-full" required pattern="^[A-Za-z\s\-']+$" title="Name should only contain letters, spaces, hyphens, or apostrophes" oninput="this.value = this.value.replace(/[0-9]/g, '')">
                    <span class="error-message text-error text-sm hidden"></span>
                </div>

                <div>
                    <label class="label-text" for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ $user->email }}" class="input w-full" readonly>
                </div>

                <x-phone-input name="phone" :value="$user->phone" label="Phone Number" id-suffix="profile" />

                <div>
                    <label class="label-text" for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="4" class="textarea w-full" placeholder="A brief introduction about yourself...">{{ $user->bio }}</textarea>
                    <p class="text-xs text-base-content/50 mt-1">Shown on your profile and any public-facing listings.</p>
                </div>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-profile-btn">
                <span class="loading loading-spinner loading-sm hidden"></span>
                Save Changes
            </button>
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-profile-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Edit Specialties Drawer --}}
<div id="edit-specialties-drawer" class="fixed top-0 right-0 h-full w-full max-w-3xl bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Edit Specialties</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('edit-specialties-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="specialties-form" onsubmit="saveSpecialties(event)" class="flex flex-col flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4 space-y-5">
            @php
                $selectedSpecialties = $instructor?->specialties ?? [];
                $specialtyGroups = \App\Models\Instructor::getSpecialtyGroups();
            @endphp
            <p class="text-sm text-base-content/60">Select the areas you specialize in. These appear on your profile and any public listings.</p>
            @foreach($specialtyGroups as $group => $items)
                <div>
                    <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider mb-2">{{ $group }}</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($items as $specialty)
                            <label class="cursor-pointer inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-base-content/10 bg-base-100 has-[:checked]:border-primary has-[:checked]:bg-primary/10 has-[:checked]:text-primary transition-all hover:border-primary/30 text-sm">
                                <input type="checkbox" name="specialties[]" value="{{ $specialty }}" class="hidden"
                                    {{ in_array($specialty, $selectedSpecialties) ? 'checked' : '' }} />
                                <span>{{ $specialty }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-specialties-btn">
                <span class="loading loading-spinner loading-sm hidden"></span>
                Save Specialties
            </button>
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('edit-specialties-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Change Password Drawer --}}
<div id="change-password-drawer" class="fixed top-0 right-0 h-full w-full max-w-3xl bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
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
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-password-btn">
                <span class="loading loading-spinner loading-sm hidden"></span>
                Update Password
            </button>
            <button type="button" class="btn btn-ghost" onclick="closeDrawer('change-password-drawer')">Cancel</button>
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
    closeDrawer('edit-specialties-drawer');
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

async function saveSpecialties(e) {
    e.preventDefault();
    const btn = document.getElementById('save-specialties-btn');
    const spinner = btn.querySelector('.loading');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    const form = e.target;
    const selected = Array.from(form.querySelectorAll('input[name="specialties[]"]:checked'))
        .map(el => el.value);

    // The profile update endpoint accepts full personal info; send the current
    // user's required fields alongside specialties so validation passes.
    const payload = {
        first_name: @json($user->first_name),
        last_name: @json($user->last_name),
        email: @json($user->email),
        phone: @json($user->phone),
        bio: @json($user->bio),
        specialties: selected,
    };

    try {
        const response = await fetch('{{ route("settings.profile.update") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });
        const result = await response.json();
        if (response.ok && result.success) {
            // Re-render the display block
            const display = document.getElementById('display-specialties');
            if (selected.length === 0) {
                display.innerHTML = '<p class="text-sm text-base-content/40 italic">No specialties added yet.</p>';
            } else {
                display.innerHTML = '<div class="flex flex-wrap gap-2">' +
                    selected.map(function (s) {
                        return '<span class="badge badge-soft badge-primary">' +
                            s.replace(/</g, '&lt;').replace(/>/g, '&gt;') +
                            '</span>';
                    }).join('') +
                    '</div>';
            }
            closeDrawer('edit-specialties-drawer');
            showToast(result.message || 'Specialties updated.', 'success');
        } else {
            showToast(result.message || 'Failed to save specialties.', 'error');
        }
    } catch (err) {
        console.error('Error:', err);
        showToast('An error occurred. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
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

    // Use the phone component to get the full number with country code
    if (typeof PhoneInput_profile !== 'undefined') {
        data.phone = PhoneInput_profile.getFullNumber();
    }

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
            var bioEl = document.getElementById('display-bio');
            if (bioEl) {
                if (data.bio && data.bio.trim() !== '') {
                    bioEl.textContent = data.bio;
                    bioEl.classList.remove('text-base-content/40', 'italic');
                    bioEl.classList.add('font-medium');
                } else {
                    bioEl.textContent = 'Not set';
                    bioEl.classList.add('text-base-content/40', 'italic');
                    bioEl.classList.remove('font-medium');
                }
            }

            closeDrawer('edit-profile-drawer');
            showToast(result.message || 'Profile updated successfully!', 'success');
        } else {
            if (result.errors) {
                showErrors('profile-form', result.errors);
            } else {
                showToast(result.message || 'Failed to save. Please try again.', 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
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
            showToast(result.message || 'Password changed successfully!', 'success');
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

    const file = input.files[0];
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (!allowedTypes.includes(file.type)) {
        input.value = '';
        showToast('Unsupported format. Please upload a JPG, PNG, GIF, or WebP image.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('photo', file);

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
            showToast('Photo uploaded successfully!', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(result.message || 'Failed to upload photo.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred while uploading.', 'error');
    }
}

function removePhoto() {
    showConfirmModal({
        title: 'Remove Photo',
        message: 'Are you sure you want to remove your profile photo?',
        type: 'danger',
        btnText: 'Remove',
        btnIcon: 'icon-[tabler--trash]',
        onConfirm: async function() {
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
                    showToast('Photo removed successfully!', 'success');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(result.message || 'Failed to remove photo.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
            }
        }
    });
}

function copyOverrideCode() {
    const code = document.getElementById('personal-code-display').textContent.trim();
    if (!code || code === 'Not assigned') return;

    navigator.clipboard.writeText(code).then(() => {
        if (typeof Notyf !== 'undefined') {
            new Notyf().success('Override code copied to clipboard!');
        } else {
            alert('Code copied: ' + code);
        }
    }).catch(err => {
        console.error('Failed to copy:', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = code;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        if (typeof Notyf !== 'undefined') {
            new Notyf().success('Override code copied to clipboard!');
        }
    });
}
</script>
@endpush
@endsection
