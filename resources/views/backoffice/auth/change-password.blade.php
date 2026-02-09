@extends('backoffice.layouts.auth')

@section('title', 'Change Password')

@section('content')
<div class="card bg-base-100">
    <div class="card-body">
        <div class="text-center mb-6">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 bg-warning/10 rounded-full flex items-center justify-center">
                    <span class="icon-[tabler--key] size-8 text-warning"></span>
                </div>
            </div>
            <h1 class="text-xl font-bold">Change Your Password</h1>
            <p class="text-base-content/60 text-sm mt-1">Please create a new secure password</p>
        </div>

        <form action="{{ route('backoffice.password.update') }}" method="POST">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="label-text" for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password"
                        class="input w-full @error('current_password') input-error @enderror"
                        placeholder="Enter current password" required autofocus />
                    @error('current_password')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="password">New Password</label>
                    <input type="password" id="password" name="password"
                        class="input w-full @error('password') input-error @enderror"
                        placeholder="Enter new password" required />
                    @error('password')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-text" for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="input w-full"
                        placeholder="Confirm new password" required />
                </div>

                {{-- Password Requirements --}}
                <div class="bg-base-200 rounded-lg p-4">
                    <p class="text-sm font-medium mb-2">Password Requirements:</p>
                    <ul class="text-sm text-base-content/60 space-y-1">
                        <li class="flex items-center gap-2" id="req-length">
                            <span class="icon-[tabler--circle] size-3"></span>
                            At least 8 characters
                        </li>
                        <li class="flex items-center gap-2" id="req-upper">
                            <span class="icon-[tabler--circle] size-3"></span>
                            One uppercase letter
                        </li>
                        <li class="flex items-center gap-2" id="req-number">
                            <span class="icon-[tabler--circle] size-3"></span>
                            One number
                        </li>
                        <li class="flex items-center gap-2" id="req-special">
                            <span class="icon-[tabler--circle] size-3"></span>
                            One special character (@$!%*?&#)
                        </li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--check] size-4"></span>
                    Change Password
                </button>
            </div>
        </form>

        <div class="text-center mt-4">
            <form action="{{ route('backoffice.logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-sm text-base-content/60 hover:text-error">
                    <span class="icon-[tabler--logout] size-3"></span>
                    Log Out
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var passwordInput = document.getElementById('password');

    passwordInput.addEventListener('input', function() {
        var value = this.value;

        // Check requirements
        updateRequirement('req-length', value.length >= 8);
        updateRequirement('req-upper', /[A-Z]/.test(value));
        updateRequirement('req-number', /[0-9]/.test(value));
        updateRequirement('req-special', /[@$!%*?&#]/.test(value));
    });

    function updateRequirement(id, met) {
        var el = document.getElementById(id);
        var icon = el.querySelector('span');
        if (met) {
            el.classList.remove('text-base-content/60');
            el.classList.add('text-success');
            icon.className = 'icon-[tabler--check] size-3';
        } else {
            el.classList.add('text-base-content/60');
            el.classList.remove('text-success');
            icon.className = 'icon-[tabler--circle] size-3';
        }
    }
});
</script>
@endsection
