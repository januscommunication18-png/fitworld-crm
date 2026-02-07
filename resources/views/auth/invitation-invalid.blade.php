<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Invalid Invitation — {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center p-4">

    <div class="card w-full max-w-md mx-auto">
        <div class="card-body text-center">
            {{-- Logo --}}
            <div class="mb-6">
                <a href="{{ url('/') }}" class="text-2xl font-bold text-base-content no-underline">FitCRM</a>
            </div>

            {{-- Error Icon --}}
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center">
                    <span class="icon-[tabler--mail-off] size-8 text-error"></span>
                </div>
            </div>

            {{-- Error Message --}}
            <h2 class="text-xl font-semibold mb-2">Invitation Not Valid</h2>
            <p class="text-base-content/60 mb-6">{{ $error }}</p>

            {{-- Actions --}}
            <div class="space-y-3">
                <a href="{{ route('login') }}" class="btn btn-primary w-full">
                    <span class="icon-[tabler--login] size-4"></span> Sign In
                </a>
                <a href="{{ url('/') }}" class="btn btn-ghost w-full">
                    Go to Homepage
                </a>
            </div>
        </div>
    </div>

</body>
</html>
