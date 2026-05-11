<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Reset Password — {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center p-4">

    <div class="card w-full max-w-md mx-auto">
        <div class="card-body">
            {{-- Logo --}}
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                    <span class="icon-[tabler--lock-check] size-8 text-primary"></span>
                </div>
                <h1 class="text-2xl font-bold">Reset Your Password</h1>
                <p class="text-base-content/60 mt-2">
                    Enter a new password for<br>
                    <span class="font-medium text-base-content">{{ $email }}</span>
                </p>
            </div>

            {{-- Error alert --}}
            @if ($errors->any())
                <div class="alert alert-soft alert-error flex items-center gap-4 mb-4" role="alert">
                    <span class="icon-[tabler--alert-circle] shrink-0 size-5"></span>
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                {{-- Password --}}
                <div class="mb-4">
                    <label class="label-text" for="password">New Password</label>
                    <input type="password" id="password" name="password"
                        class="input w-full @error('password') input-error @enderror"
                        placeholder="Enter new password" required autofocus />

                    {{-- Password Strength Indicator --}}
                    <div class="mt-2" id="password-strength">
                        <div class="flex gap-1 mb-2">
                            <div id="str-1" class="h-1.5 flex-1 rounded-full bg-base-300 transition-colors duration-300"></div>
                            <div id="str-2" class="h-1.5 flex-1 rounded-full bg-base-300 transition-colors duration-300"></div>
                            <div id="str-3" class="h-1.5 flex-1 rounded-full bg-base-300 transition-colors duration-300"></div>
                            <div id="str-4" class="h-1.5 flex-1 rounded-full bg-base-300 transition-colors duration-300"></div>
                            <div id="str-5" class="h-1.5 flex-1 rounded-full bg-base-300 transition-colors duration-300"></div>
                        </div>
                        <ul class="space-y-1 text-xs">
                            <li id="rule-length" class="flex items-center gap-1.5 text-base-content/50">
                                <span class="icon-[tabler--circle] size-3.5 rule-icon"></span> At least 8 characters
                            </li>
                            <li id="rule-upper" class="flex items-center gap-1.5 text-base-content/50">
                                <span class="icon-[tabler--circle] size-3.5 rule-icon"></span> Contains uppercase letter
                            </li>
                            <li id="rule-lower" class="flex items-center gap-1.5 text-base-content/50">
                                <span class="icon-[tabler--circle] size-3.5 rule-icon"></span> Contains lowercase letter
                            </li>
                            <li id="rule-number" class="flex items-center gap-1.5 text-base-content/50">
                                <span class="icon-[tabler--circle] size-3.5 rule-icon"></span> Contains number
                            </li>
                            <li id="rule-special" class="flex items-center gap-1.5 text-base-content/50">
                                <span class="icon-[tabler--circle] size-3.5 rule-icon"></span> Contains special character (!@#$%...)
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div class="mb-6">
                    <label class="label-text" for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="input w-full"
                        placeholder="Confirm new password" required />
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--check] size-4"></span> Reset Password
                </button>
            </form>

            {{-- Back to login --}}
            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="link link-primary text-sm no-underline">
                    <span class="icon-[tabler--arrow-left] size-4 inline-block align-middle"></span>
                    Back to login
                </a>
            </div>
        </div>
    </div>

<script>
document.getElementById('password').addEventListener('input', function() {
    var pw = this.value;
    var rules = [
        { id: 'rule-length', valid: pw.length >= 8 },
        { id: 'rule-upper', valid: /[A-Z]/.test(pw) },
        { id: 'rule-lower', valid: /[a-z]/.test(pw) },
        { id: 'rule-number', valid: /[0-9]/.test(pw) },
        { id: 'rule-special', valid: /[^A-Za-z0-9]/.test(pw) },
    ];

    var strength = 0;
    rules.forEach(function(rule) {
        var el = document.getElementById(rule.id);
        var icon = el.querySelector('.rule-icon');
        if (rule.valid) {
            strength++;
            el.className = 'flex items-center gap-1.5 text-success';
            icon.className = 'icon-[tabler--circle-check] size-3.5 rule-icon';
        } else {
            el.className = 'flex items-center gap-1.5 text-base-content/50';
            icon.className = 'icon-[tabler--circle] size-3.5 rule-icon';
        }
    });

    var colors = ['bg-base-300', 'bg-error', 'bg-warning', 'bg-warning', 'bg-info', 'bg-success'];
    var color = colors[strength] || 'bg-base-300';
    for (var i = 1; i <= 5; i++) {
        var bar = document.getElementById('str-' + i);
        bar.className = 'h-1.5 flex-1 rounded-full transition-colors duration-300 ' + (i <= strength ? color : 'bg-base-300');
    }
});
</script>
</body>
</html>
