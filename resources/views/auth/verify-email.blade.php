<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email — {{ config('app.name', 'FitCRM') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center p-4">
    <div class="card bg-base-100 w-full max-w-md">
        <div class="card-body text-center">
            <div class="mb-4">
                <span class="icon-[tabler--mail-check] size-16 text-primary mx-auto"></span>
            </div>
            <h1 class="text-2xl font-bold mb-2">Verify Your Email</h1>
            <p class="text-base-content/60 mb-6">
                Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just emailed to you.
            </p>

            @if (session('message'))
                <div class="alert alert-success mb-4">
                    <span class="icon-[tabler--check] size-5"></span>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            <div class="space-y-3">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full">
                        <span class="icon-[tabler--mail] size-4"></span>
                        Resend Verification Email
                    </button>
                </form>

                <a href="{{ route('dashboard') }}" class="btn btn-ghost w-full">
                    <span class="icon-[tabler--arrow-left] size-4"></span>
                    Back to Dashboard
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm text-base-content/60">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
