@extends('layouts.subdomain')

@section('title', "Join {$host->studio_name}")

@section('content')
@php
    $logoUrl = $host->logo_url ?? null;
    $coverUrl = $host->cover_image_url ?? null;
    $inviter = $invitation->invitedBy;
    $studioInitials = collect(explode(' ', trim($host->studio_name)))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->join('');
@endphp

<div class="w-full max-w-lg mx-auto pt-10 sm:pt-16">
    <div class="card bg-base-100 shadow-xl overflow-hidden">
        {{-- Cover / brand banner --}}
        <div class="relative h-32 bg-gradient-to-br from-primary via-primary to-primary/70"
            @if($coverUrl) style="background-image: url('{{ $coverUrl }}'); background-size: cover; background-position: center;" @endif>
            @if($coverUrl)
                <div class="absolute inset-0 bg-gradient-to-b from-black/20 to-black/60"></div>
            @endif
            <div class="absolute -bottom-10 left-1/2 -translate-x-1/2">
                <div class="size-20 rounded-2xl bg-base-100 shadow-lg border-4 border-base-100 flex items-center justify-center overflow-hidden">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $host->studio_name }}" class="size-full object-cover">
                    @else
                        <div class="size-full bg-primary/10 flex items-center justify-center text-primary font-bold text-2xl">
                            {{ $studioInitials ?: 'S' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body pt-12">
            {{-- Compact studio + invitation header --}}
            <div class="text-center">
                <p class="text-[11px] uppercase tracking-wider text-base-content/50">You're invited to join</p>
                <h1 class="text-xl font-bold leading-tight">{{ $host->studio_name }}</h1>
                <p class="text-xs text-base-content/60 mt-1 flex items-center justify-center gap-1.5 flex-wrap">
                    @if($inviter)
                        <span>from <span class="font-medium text-base-content">{{ $inviter->full_name ?? $inviter->name ?? 'Studio team' }}</span></span>
                        <span class="text-base-content/30">·</span>
                    @endif
                    <span class="truncate">{{ $invitation->email }}</span>
                    <span class="text-base-content/30">·</span>
                    <span class="badge badge-primary badge-soft badge-xs">{{ ucfirst($invitation->role) }}</span>
                </p>
            </div>

            {{-- Error alerts --}}
            @if ($errors->any())
                <div class="alert alert-soft alert-error flex items-center gap-3 mt-4" role="alert">
                    <span class="icon-[tabler--alert-circle] shrink-0 size-5"></span>
                    <p class="text-sm">{{ $errors->first() }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-soft alert-error flex items-center gap-3 mt-4" role="alert">
                    <span class="icon-[tabler--alert-circle] shrink-0 size-5"></span>
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('subdomain.invite.accept', ['subdomain' => $host->subdomain, 'token' => $invitation->token]) }}" class="mt-5 space-y-4">
                @csrf

                @if($existingUser)
                    <div class="alert alert-soft alert-info">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        <span class="text-sm">You already have an account. Enter your password to join this studio.</span>
                    </div>

                    <div>
                        <label class="label-text" for="password">Your Password</label>
                        <input type="password" id="password" name="password"
                            class="input w-full @error('password') input-error @enderror"
                            placeholder="Enter your password" required autofocus />
                    </div>
                @else
                    <p class="text-sm text-base-content/60">Create your account to accept this invitation.</p>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label-text" for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $invitation->first_name) }}"
                                class="input w-full @error('first_name') input-error @enderror"
                                placeholder="John" required autofocus />
                        </div>
                        <div>
                            <label class="label-text" for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $invitation->last_name) }}"
                                class="input w-full @error('last_name') input-error @enderror"
                                placeholder="Doe" required />
                        </div>
                    </div>

                    <div>
                        <label class="label-text" for="password">Password</label>
                        <input type="password" id="password" name="password"
                            class="input w-full @error('password') input-error @enderror"
                            placeholder="Create a password" required />

                        <div id="password-strength" class="mt-2">
                            <div class="flex gap-1 mb-2">
                                <div id="strength-1" class="h-1.5 flex-1 rounded-full bg-base-300 transition-colors duration-300"></div>
                                <div id="strength-2" class="h-1.5 flex-1 rounded-full bg-base-300 transition-colors duration-300"></div>
                                <div id="strength-3" class="h-1.5 flex-1 rounded-full bg-base-300 transition-colors duration-300"></div>
                                <div id="strength-4" class="h-1.5 flex-1 rounded-full bg-base-300 transition-colors duration-300"></div>
                            </div>
                            <ul class="grid grid-cols-2 gap-y-1 gap-x-3 text-xs">
                                <li id="rule-length" class="flex items-center gap-1.5 text-base-content/50">
                                    <span class="icon-[tabler--circle] size-3.5"></span>
                                    8+ characters
                                </li>
                                <li id="rule-uppercase" class="flex items-center gap-1.5 text-base-content/50">
                                    <span class="icon-[tabler--circle] size-3.5"></span>
                                    Uppercase letter
                                </li>
                                <li id="rule-lowercase" class="flex items-center gap-1.5 text-base-content/50">
                                    <span class="icon-[tabler--circle] size-3.5"></span>
                                    Lowercase letter
                                </li>
                                <li id="rule-number" class="flex items-center gap-1.5 text-base-content/50">
                                    <span class="icon-[tabler--circle] size-3.5"></span>
                                    Number
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div>
                        <label class="label-text" for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="input w-full"
                            placeholder="Confirm your password" required />
                    </div>
                @endif

                <button type="submit" class="btn btn-primary w-full btn-lg">
                    <span class="icon-[tabler--check] size-5"></span>
                    {{ $existingUser ? 'Join ' . $host->studio_name : 'Create Account & Join' }}
                </button>
            </form>

            <p class="text-center text-xs text-base-content/40 mt-4">
                By accepting, you agree to {{ $host->studio_name }}'s terms.
            </p>
        </div>
    </div>

</div>
@endsection

@if(!$existingUser)
@push('scripts')
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
@endpush
@endif
