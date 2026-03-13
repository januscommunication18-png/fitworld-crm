<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Forgot Password — {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center p-4">

    <div class="card w-full max-w-md mx-auto">
        <div class="card-body">
            {{-- Logo --}}
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                    <span class="icon-[tabler--lock-question] size-8 text-primary"></span>
                </div>
                <h1 class="text-2xl font-bold">Forgot Password?</h1>
                <p class="text-base-content/60 mt-2">Enter your email and we'll send you a link to reset your password.</p>
            </div>

            {{-- Success message --}}
            @if (session('status'))
                <div class="alert alert-soft alert-success flex items-center gap-4 mb-4" role="alert">
                    <span class="icon-[tabler--circle-check] shrink-0 size-5"></span>
                    <p>{{ session('status') }}</p>
                </div>
            @endif

            {{-- Error alert --}}
            @if ($errors->any())
                <div class="alert alert-soft alert-error flex items-center gap-4 mb-4" role="alert">
                    <span class="icon-[tabler--alert-circle] shrink-0 size-5"></span>
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-6">
                    <label class="label-text" for="email">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                        class="input w-full @error('email') input-error @enderror"
                        placeholder="you@example.com" required autofocus />
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--mail] size-4"></span> Send Reset Link
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

</body>
</html>
