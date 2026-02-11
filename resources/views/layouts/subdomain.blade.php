<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $host->studio_name ?? config('app.name', 'FitCRM'))</title>

    @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 min-h-screen flex flex-col">

    {{-- Header with Studio Branding --}}
    <header class="py-8">
        <div class="flex flex-col items-center justify-center gap-2">
            @if($host->logo_url ?? false)
                <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-12 w-auto object-contain">
            @else
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--building-community] size-6 text-primary"></span>
                </div>
            @endif
            <h1 class="text-xl font-bold text-base-content">{{ $host->studio_name }}</h1>
            @if($host->city ?? false)
                <p class="text-sm text-base-content/60">{{ $host->city }}</p>
            @endif
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1 flex items-start justify-center px-4 pb-8">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="py-4 text-center text-sm text-base-content/40">
        Powered by <a href="{{ config('app.url') }}" class="link link-primary no-underline">FitCRM</a>
    </footer>

    @stack('scripts')
</body>
</html>
