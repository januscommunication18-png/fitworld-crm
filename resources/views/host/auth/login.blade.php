<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Log In — {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center p-4">

    <div class="card w-full max-w-md mx-auto">
        <div class="card-body">
            {{-- Logo --}}
            <div class="text-center mb-6">
                <a href="{{ url('/') }}" class="text-2xl font-bold text-base-content no-underline">FitCRM</a>
                <p class="text-base-content/60 mt-1">Sign in to your studio dashboard</p>
            </div>

            {{-- Error alert --}}
            @if ($errors->any())
                <div class="alert alert-soft alert-error flex items-center gap-4 mb-4" role="alert">
                    <span class="icon-[tabler--alert-circle] shrink-0 size-5"></span>
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            {{-- Status message (e.g. after signup) --}}
            @if (session('status'))
                <div class="alert alert-soft alert-success flex items-center gap-4 mb-4" role="alert">
                    <span class="icon-[tabler--circle-check] shrink-0 size-5"></span>
                    <p>{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label class="label-text" for="login-email">Email address</label>
                    <input type="email" id="login-email" name="email" value="{{ old('email') }}"
                        class="input w-full @error('email') input-error @enderror"
                        placeholder="you@example.com" required autofocus />
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label class="label-text" for="login-password">Password</label>
                    <input type="password" id="login-password" name="password"
                        class="input w-full @error('password') input-error @enderror"
                        placeholder="Enter your password" required />
                </div>

                {{-- Remember me --}}
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="checkbox checkbox-primary checkbox-sm"
                            {{ old('remember') ? 'checked' : '' }} />
                        <span class="label-text text-sm">Remember me</span>
                    </label>
                    {{-- <a href="#" class="link link-primary text-sm no-underline">Forgot password?</a> --}}
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--login] size-4"></span> Sign In
                </button>
            </form>

            {{-- Signup link --}}
            <div class="text-center mt-6">
                <p class="text-sm text-base-content/60">
                    Don't have an account?
                    <a href="{{ route('signup') }}" class="link link-primary no-underline">Sign up free</a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>
