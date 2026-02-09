<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') — {{ config('app.name', 'FitCRM') }} Backoffice</title>

    @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        {{-- Logo/Branding --}}
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-2 mb-2">
                <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                    <span class="icon-[tabler--building-store] size-6 text-primary-content"></span>
                </div>
                <span class="text-2xl font-bold text-base-content">FitCRM</span>
            </div>
            <p class="text-base-content/60 text-sm">Admin Backoffice</p>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-success mb-4">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error mb-4">
            <span class="icon-[tabler--alert-circle] size-5"></span>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if(session('warning'))
        <div class="alert alert-warning mb-4">
            <span class="icon-[tabler--alert-triangle] size-5"></span>
            <span>{{ session('warning') }}</span>
        </div>
        @endif

        @if(session('info'))
        <div class="alert alert-info mb-4">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <span>{{ session('info') }}</span>
        </div>
        @endif

        {{-- Page Content --}}
        @yield('content')
    </div>
</body>
</html>
