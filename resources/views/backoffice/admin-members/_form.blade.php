@php
    $adminMember = $adminMember ?? null;
    $permissions = $adminMember ? ($adminMember->permissions ?? []) : [];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Form --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Basic Info --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Basic Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name"
                            value="{{ old('first_name', $adminMember?->first_name) }}"
                            class="input w-full @error('first_name') input-error @enderror"
                            required>
                        @error('first_name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name"
                            value="{{ old('last_name', $adminMember?->last_name) }}"
                            class="input w-full @error('last_name') input-error @enderror"
                            required>
                        @error('last_name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="label-text" for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                        value="{{ old('email', $adminMember?->email) }}"
                        class="input w-full @error('email') input-error @enderror"
                        required>
                    @error('email')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                    @if(!$adminMember)
                    <p class="text-xs text-base-content/60 mt-1">A temporary password will be sent to this email.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Role & Permissions --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Role & Permissions</h3>
            </div>
            <div class="card-body space-y-6">
                <div>
                    <label class="label-text">Role</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                        <label class="flex items-start gap-3 p-4 rounded-lg border border-base-content/10 cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="role" value="administrator"
                                class="radio radio-primary mt-0.5"
                                {{ old('role', $adminMember?->role ?? 'administrator') === 'administrator' ? 'checked' : '' }}
                                onchange="togglePermissions()">
                            <div>
                                <span class="font-medium">Administrator</span>
                                <p class="text-xs text-base-content/60 mt-1">Full access to all features and settings</p>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-4 rounded-lg border border-base-content/10 cursor-pointer hover:bg-base-200/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="role" value="team_member"
                                class="radio radio-primary mt-0.5"
                                {{ old('role', $adminMember?->role) === 'team_member' ? 'checked' : '' }}
                                onchange="togglePermissions()">
                            <div>
                                <span class="font-medium">Team Member</span>
                                <p class="text-xs text-base-content/60 mt-1">Limited access based on permissions below</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div id="permissions-section" class="{{ old('role', $adminMember?->role) === 'team_member' ? '' : 'hidden' }}">
                    <label class="label-text">Section Access</label>
                    <p class="text-xs text-base-content/60 mb-3">Select which sections this team member can access.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @php
                            $availablePermissions = [
                                'dashboard' => ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'default' => true],
                                'clients' => ['label' => 'Clients', 'icon' => 'building', 'default' => true],
                                'email_templates' => ['label' => 'Email Templates', 'icon' => 'mail', 'default' => true],
                                'email_logs' => ['label' => 'Email Logs', 'icon' => 'mail-opened', 'default' => true],
                                'plans' => ['label' => 'Plans', 'icon' => 'license', 'default' => false, 'warning' => 'Sensitive'],
                                'admin_members' => ['label' => 'Admin Members', 'icon' => 'shield-check', 'default' => false, 'warning' => 'Sensitive'],
                                'settings' => ['label' => 'Settings', 'icon' => 'settings', 'default' => false, 'warning' => 'Sensitive'],
                            ];
                        @endphp

                        @foreach($availablePermissions as $key => $info)
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-base-content/10 cursor-pointer hover:bg-base-200/50">
                            <input type="checkbox" name="perm_{{ $key }}" value="1"
                                class="checkbox checkbox-sm checkbox-primary"
                                {{ old("perm_{$key}", $permissions[$key] ?? $info['default']) ? 'checked' : '' }}>
                            <span class="icon-[tabler--{{ $info['icon'] }}] size-5 text-base-content/40"></span>
                            <span class="text-sm flex-1">{{ $info['label'] }}</span>
                            @if(isset($info['warning']))
                                <span class="badge badge-soft badge-warning badge-xs">{{ $info['warning'] }}</span>
                            @endif
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Actions --}}
        <div class="card bg-base-100">
            <div class="card-body space-y-2">
                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--check] size-5"></span>
                    {{ $adminMember ? 'Update Member' : 'Send Invitation' }}
                </button>
                <a href="{{ route('backoffice.admin-members.index') }}" class="btn btn-ghost w-full">
                    Cancel
                </a>
            </div>
        </div>

        @if($adminMember)
        {{-- Member Info --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Info</h3>
            </div>
            <div class="card-body">
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-base-content/60">Status</dt>
                        <dd>
                            @if($adminMember->status === 'active')
                                <span class="badge badge-soft badge-success badge-sm">Active</span>
                            @else
                                <span class="badge badge-soft badge-warning badge-sm capitalize">{{ $adminMember->status }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">Created</dt>
                        <dd>{{ $adminMember->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">Last Login</dt>
                        <dd>{{ $adminMember->last_login_at ? $adminMember->last_login_at->format('M d, Y h:i A') : 'Never' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body space-y-2">
                <form action="{{ route('backoffice.admin-members.reset-password', $adminMember) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-soft btn-warning w-full"
                            onclick="return confirm('Reset password? A new password will be sent to their email.')">
                        <span class="icon-[tabler--key] size-5"></span>
                        Reset Password
                    </button>
                </form>

                @if($adminMember->id !== auth('admin')->id())
                <form action="{{ route('backoffice.admin-members.toggle-status', $adminMember) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-soft {{ $adminMember->status === 'active' ? 'btn-warning' : 'btn-success' }} w-full">
                        @if($adminMember->status === 'active')
                            <span class="icon-[tabler--user-off] size-5"></span>
                            Suspend
                        @else
                            <span class="icon-[tabler--user-check] size-5"></span>
                            Reactivate
                        @endif
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function togglePermissions() {
    var role = document.querySelector('input[name="role"]:checked').value;
    var section = document.getElementById('permissions-section');
    if (role === 'team_member') {
        section.classList.remove('hidden');
    } else {
        section.classList.add('hidden');
    }
}
</script>
