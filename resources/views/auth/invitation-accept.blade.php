<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Accept Invitation — {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center p-4">

    <div class="card w-full max-w-md mx-auto">
        <div class="card-body">
            {{-- Logo --}}
            <div class="text-center mb-6">
                <a href="{{ url('/') }}" class="text-2xl font-bold text-base-content no-underline">FitCRM</a>
                <p class="text-base-content/60 mt-1">You've been invited to join</p>
                <p class="text-lg font-semibold text-primary mt-1">{{ $host->studio_name }}</p>
            </div>

            {{-- Invitation Details --}}
            <div class="bg-base-200 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="avatar avatar-sm">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="icon-[tabler--mail] size-5 text-primary"></span>
                        </div>
                    </div>
                    <div>
                        <p class="font-medium">{{ $invitation->email }}</p>
                        <p class="text-sm text-base-content/60">Invited as <span class="font-medium text-primary">{{ ucfirst($invitation->role) }}</span></p>
                    </div>
                </div>
            </div>

            {{-- Error alert --}}
            @if ($errors->any())
                <div class="alert alert-soft alert-error flex items-center gap-4 mb-4" role="alert">
                    <span class="icon-[tabler--alert-circle] shrink-0 size-5"></span>
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('invitation.accept', $invitation->token) }}">
                @csrf

                @if($existingUser)
                    {{-- Existing User - Just need password --}}
                    <div class="alert alert-soft alert-info mb-4">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        <span>You already have an account. Enter your password to join this studio.</span>
                    </div>

                    <div class="mb-4">
                        <label class="label-text" for="password">Your Password</label>
                        <input type="password" id="password" name="password"
                            class="input w-full @error('password') input-error @enderror"
                            placeholder="Enter your password" required autofocus />
                    </div>
                @else
                    {{-- New User - Create account --}}
                    <p class="text-sm text-base-content/60 mb-4">Create your account to accept this invitation.</p>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="label-text" for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}"
                                class="input w-full @error('first_name') input-error @enderror"
                                placeholder="John" required autofocus />
                        </div>
                        <div>
                            <label class="label-text" for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}"
                                class="input w-full @error('last_name') input-error @enderror"
                                placeholder="Doe" required />
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="label-text" for="password">Password</label>
                        <input type="password" id="password" name="password"
                            class="input w-full @error('password') input-error @enderror"
                            placeholder="Create a password" required />

                        {{-- Password Strength Indicator --}}
                        <div id="password-strength" class="mt-2">
                            <div class="flex gap-1 mb-2">
                                <div id="strength-1" class="h-1.5 flex-1 rounded-full bg-base-300 transition-colors duration-300"></div>
                                <div id="strength-2" class="h-1.5 flex-1 rounded-full bg-base-300 transition-colors duration-300"></div>
                                <div id="strength-3" class="h-1.5 flex-1 rounded-full bg-base-300 transition-colors duration-300"></div>
                                <div id="strength-4" class="h-1.5 flex-1 rounded-full bg-base-300 transition-colors duration-300"></div>
                            </div>
                            <ul class="space-y-1 text-xs">
                                <li id="rule-length" class="flex items-center gap-1.5 text-base-content/50">
                                    <span class="icon-[tabler--circle] size-3.5"></span>
                                    At least 8 characters
                                </li>
                                <li id="rule-uppercase" class="flex items-center gap-1.5 text-base-content/50">
                                    <span class="icon-[tabler--circle] size-3.5"></span>
                                    Contains uppercase letter
                                </li>
                                <li id="rule-lowercase" class="flex items-center gap-1.5 text-base-content/50">
                                    <span class="icon-[tabler--circle] size-3.5"></span>
                                    Contains lowercase letter
                                </li>
                                <li id="rule-number" class="flex items-center gap-1.5 text-base-content/50">
                                    <span class="icon-[tabler--circle] size-3.5"></span>
                                    Contains number
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="label-text" for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="input w-full"
                            placeholder="Confirm your password" required />
                    </div>
                @endif

                {{-- Submit --}}
                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--check] size-4"></span> Accept Invitation
                </button>
            </form>

            {{-- Login link --}}
            <div class="text-center mt-6">
                <p class="text-sm text-base-content/60">
                    Already have an account with a different email?
                    <a href="{{ route('login') }}" class="link link-primary no-underline">Sign in</a>
                </p>
            </div>
        </div>
    </div>

@if(!$existingUser)
<script>
document.addEventListener('DOMContentLoaded', function() {
    var passwordInput = document.getElementById('password');
    if (!passwordInput) return;

    var rules = {
        length: { el: document.getElementById('rule-length'), test: function(p) { return p.length >= 8; } },
        uppercase: { el: document.getElementById('rule-uppercase'), test: function(p) { return /[A-Z]/.test(p); } },
        lowercase: { el: document.getElementById('rule-lowercase'), test: function(p) { return /[a-z]/.test(p); } },
        number: { el: document.getElementById('rule-number'), test: function(p) { return /\d/.test(p); } }
    };

    var strengthBars = [
        document.getElementById('strength-1'),
        document.getElementById('strength-2'),
        document.getElementById('strength-3'),
        document.getElementById('strength-4')
    ];

    function updateStrength() {
        var password = passwordInput.value;
        var strength = 0;

        // Check each rule
        Object.keys(rules).forEach(function(key) {
            var rule = rules[key];
            var valid = rule.test(password);
            var icon = rule.el.querySelector('span');

            if (valid) {
                strength++;
                rule.el.classList.remove('text-base-content/50');
                rule.el.classList.add('text-success');
                icon.className = 'icon-[tabler--circle-check] size-3.5';
            } else {
                rule.el.classList.add('text-base-content/50');
                rule.el.classList.remove('text-success');
                icon.className = 'icon-[tabler--circle] size-3.5';
            }
        });

        // Update strength bars
        var colorClass = strength <= 1 ? 'bg-error' : (strength <= 2 ? 'bg-warning' : (strength <= 3 ? 'bg-info' : 'bg-success'));

        strengthBars.forEach(function(bar, index) {
            bar.classList.remove('bg-error', 'bg-warning', 'bg-info', 'bg-success', 'bg-base-300');
            if (index < strength) {
                bar.classList.add(colorClass);
            } else {
                bar.classList.add('bg-base-300');
            }
        });
    }

    passwordInput.addEventListener('input', updateStrength);
});
</script>
@endif

</body>
</html>
