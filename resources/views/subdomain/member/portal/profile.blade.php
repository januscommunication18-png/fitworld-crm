@extends('layouts.subdomain')

@section('title', 'My Profile — ' . $host->studio_name)

@section('content')
<div class="min-h-screen flex flex-col">
    @include('subdomain.member.portal._nav')

    <div class="flex-1 bg-base-200">
        <div class="container-fixed py-8">
            <h1 class="text-2xl font-bold mb-6">My Profile</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Profile Form --}}
                <div class="lg:col-span-2">
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="font-semibold text-lg mb-4">Personal Information</h2>

                            <form action="{{ route('member.portal.profile.update', ['subdomain' => $host->subdomain]) }}" method="POST">
                                @csrf
                                @method('PUT')

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
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-primary-content w-20 rounded-full">
                                        <span class="text-2xl">{{ $member->initials }}</span>
                                    </div>
                                </div>
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
                                    <span class="badge badge-sm {{ $member->status === 'member' ? 'badge-success' : 'badge-neutral' }}">
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
        </div>
    </div>
</div>
@endsection
