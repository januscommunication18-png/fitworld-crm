<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Support') — {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css', 'resources/js/layout.js'])
    @stack('styles')
    @stack('head')
</head>
<body class="bg-base-200 min-h-screen flex flex-col">

    {{-- Top Bar --}}
    <div class="bg-base-100 border-b border-base-content/10 sticky top-0 z-40">
        <div class="flex items-center justify-between px-6 h-16">
            <div class="flex items-center gap-3">
                <span class="icon-[tabler--headset] size-6 text-primary"></span>
                <h1 class="text-lg font-semibold">Support</h1>
            </div>
            <a href="{{ url('/dashboard') }}" class="btn btn-ghost btn-sm btn-circle" title="Close">
                <span class="icon-[tabler--x] size-5"></span>
            </a>
        </div>
    </div>

    {{-- Content --}}
    <div class="flex-1 p-6">
        <div class="max-w-6xl mx-auto">
            @yield('content')
        </div>
    </div>

    {{-- Global Confirmation Modals --}}
    @include('partials.modals.confirmation-modals')

    @stack('modals')
    @stack('scripts')
</body>
</html>
