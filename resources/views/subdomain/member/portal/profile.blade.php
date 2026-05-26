@extends('layouts.subdomain')

@section('title', 'My Profile — ' . $host->studio_name)

@section('content')
<x-portal-shell :host="$host" :member="$member">
    <h1 class="text-2xl font-bold mb-6">My Profile</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Profile Form --}}
                <div class="lg:col-span-2">
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="font-semibold text-lg mb-4">Personal Information</h2>

                            <form id="profile-form" action="{{ route('member.portal.profile.update', ['subdomain' => $host->subdomain]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <input type="hidden" name="remove_profile_photo" id="remove_profile_photo" value="">

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="label-text" for="first_name">First Name</label>
                                        <input type="text" id="first_name" name="first_name"
                                               value="{{ old('first_name', $member->first_name) }}"
                                               required
                                               class="input input-bordered w-full mt-1 @error('first_name') input-error @enderror">
                                        @error('first_name')
                                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="label-text" for="last_name">Last Name</label>
                                        <input type="text" id="last_name" name="last_name"
                                               value="{{ old('last_name', $member->last_name) }}"
                                               required
                                               class="input input-bordered w-full mt-1 @error('last_name') input-error @enderror">
                                        @error('last_name')
                                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="label-text" for="email">Email</label>
                                    <input type="email" id="email" name="email"
                                           value="{{ old('email', $member->email) }}"
                                           required
                                           class="input input-bordered w-full mt-1 @error('email') input-error @enderror">
                                    @error('email')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="label-text" for="phone">Phone</label>
                                    <input type="tel" id="phone" name="phone"
                                           value="{{ old('phone', $member->phone) }}"
                                           class="input input-bordered w-full mt-1 @error('phone') input-error @enderror">
                                    @error('phone')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="label-text" for="date_of_birth">Date of Birth</label>
                                    <input type="date" id="date_of_birth" name="date_of_birth"
                                           value="{{ old('date_of_birth', $member->date_of_birth?->format('Y-m-d')) }}"
                                           class="input input-bordered w-full mt-1 @error('date_of_birth') input-error @enderror">
                                    @error('date_of_birth')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="divider"></div>

                                <h3 class="font-medium mb-4">Emergency Contact</h3>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="label-text" for="emergency_contact_name">Contact Name</label>
                                        <input type="text" id="emergency_contact_name" name="emergency_contact_name"
                                               value="{{ old('emergency_contact_name', $member->emergency_contact_name) }}"
                                               class="input input-bordered w-full mt-1">
                                    </div>
                                    <div>
                                        <label class="label-text" for="emergency_contact_phone">Contact Phone</label>
                                        <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone"
                                               value="{{ old('emergency_contact_phone', $member->emergency_contact_phone) }}"
                                               class="input input-bordered w-full mt-1">
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="icon-[tabler--check] size-5"></span>
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Password Change (only for password-based login) --}}
                    @if($host->getMemberPortalSetting('login_method') === 'password')
                    <div class="card bg-base-100 mt-6">
                        <div class="card-body">
                            <h2 class="font-semibold text-lg mb-4">Change Password</h2>

                            <form action="{{ route('member.portal.profile.password', ['subdomain' => $host->subdomain]) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-4">
                                    <label class="label-text" for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password"
                                           required
                                           class="input input-bordered w-full mt-1 @error('current_password') input-error @enderror">
                                    @error('current_password')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="label-text" for="password">New Password</label>
                                    <input type="password" id="password" name="password"
                                           required
                                           class="input input-bordered w-full mt-1 @error('password') input-error @enderror">
                                    @error('password')
                                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="label-text" for="password_confirmation">Confirm New Password</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                           required
                                           class="input input-bordered w-full mt-1">
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="btn btn-outline">
                                        <span class="icon-[tabler--lock] size-5"></span>
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Account Summary --}}
                <div class="lg:col-span-1">
                    <div class="card bg-base-100 sticky top-6">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                {{-- Clickable photo block — opens the file picker which is
                                     linked to the profile form above so the upload submits
                                     alongside the rest of the form via Save Changes. --}}
                                <label for="profile_photo_input" class="inline-block cursor-pointer group relative">
                                    @if($member->avatar_url)
                                        <div class="avatar">
                                            <div class="w-20 h-20 rounded-full overflow-hidden">
                                                <img src="{{ $member->avatar_url }}" alt="{{ $member->full_name }}" class="object-cover w-full h-full" id="profile_photo_preview">
                                            </div>
                                        </div>
                                    @else
                                        <div class="avatar placeholder">
                                            <div class="bg-primary text-primary-content w-20 rounded-full">
                                                <span class="text-2xl" id="profile_photo_initials">{{ $member->initials }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    <span class="absolute bottom-0 right-0 inline-flex items-center justify-center w-7 h-7 rounded-full bg-primary text-primary-content border-2 border-base-100 group-hover:bg-primary/90">
                                        <span class="icon-[tabler--camera] size-4"></span>
                                    </span>
                                </label>
                                <input type="file" name="profile_photo" id="profile_photo_input"
                                       accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                       form="profile-form"
                                       class="hidden"
                                       onchange="onProfilePhotoSelected(this)">
                                <p class="text-xs text-base-content/50 mt-2">JPG, PNG, GIF, WEBP up to 2MB</p>
                                <p id="profile_photo_filename" class="text-xs text-primary mt-1 hidden"></p>
                                @if($member->avatar_url)
                                    <button type="button"
                                            class="text-xs text-error hover:underline mt-1"
                                            onclick="markPhotoForRemoval()">
                                        Remove photo
                                    </button>
                                @endif
                                @error('profile_photo')
                                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <h3 class="font-semibold text-lg mt-3">{{ $member->full_name }}</h3>
                                <p class="text-sm text-base-content/60">{{ $member->email }}</p>
                            </div>

                            <div class="divider"></div>

                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Member Since</span>
                                    <span>{{ $member->created_at->format('M Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Status</span>
                                    <span class="badge badge-sm {{ $member->is_member ? 'badge-success' : 'badge-neutral' }}">
                                        {{ ucfirst($member->status) }}
                                    </span>
                                </div>
                                @if($member->portal_last_login_at)
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Last Login</span>
                                    <span>{{ $member->portal_last_login_at->diffForHumans() }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
</x-portal-shell>

<script>
function onProfilePhotoSelected(input) {
    var name = document.getElementById('profile_photo_filename');
    if (input.files && input.files[0]) {
        var f = input.files[0];
        name.textContent = 'Selected: ' + f.name + ' — click Save Changes to upload';
        name.classList.remove('hidden');

        // Live preview if the avatar img is present
        var preview = document.getElementById('profile_photo_preview');
        if (preview) {
            var reader = new FileReader();
            reader.onload = function (e) { preview.src = e.target.result; };
            reader.readAsDataURL(f);
        }
        // Cancel any pending removal — uploading a new file overrides remove.
        var rm = document.getElementById('remove_profile_photo');
        if (rm) rm.value = '';
    } else {
        name.textContent = '';
        name.classList.add('hidden');
    }
}

function markPhotoForRemoval() {
    if (!confirm('Remove your profile photo? Click Save Changes to apply.')) return;
    var rm = document.getElementById('remove_profile_photo');
    if (rm) rm.value = '1';
    var name = document.getElementById('profile_photo_filename');
    if (name) {
        name.textContent = 'Photo will be removed when you click Save Changes.';
        name.classList.remove('hidden');
    }
    // Also clear the file input so removal isn't overridden by a previous selection.
    var input = document.getElementById('profile_photo_input');
    if (input) input.value = '';
}
</script>
@endsection
