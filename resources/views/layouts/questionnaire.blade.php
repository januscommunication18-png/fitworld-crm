<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Questionnaire') - {{ $host->studio_name ?? config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html { scroll-behavior: smooth; }

        /* Mobile-first responsive container */
        .questionnaire-container {
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            padding: 1rem;
        }

        /* Large tap targets for mobile */
        .tap-target {
            min-height: 44px;
        }

        /* Sticky submit button on mobile */
        .sticky-bottom {
            position: sticky;
            bottom: 0;
            background: linear-gradient(to top, oklch(var(--b1)) 80%, transparent);
            padding: 1rem 0;
            margin-top: 1rem;
        }

        /* Progress bar animation */
        .progress-fill {
            transition: width 0.3s ease;
        }

        /* Question card styling */
        .question-card {
            transition: border-color 0.2s ease;
        }
        .question-card:focus-within {
            border-color: oklch(var(--p));
        }
    </style>
</head>
<body class="bg-base-200 min-h-screen flex flex-col antialiased">
    {{-- Header --}}
    <header class="bg-base-100 border-b border-base-300 py-4">
        <div class="questionnaire-container">
            <div class="flex items-center gap-3">
                @if($host->logo_url ?? false)
                    <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-10 w-auto">
                @endif
                <div>
                    <h1 class="font-semibold text-lg">{{ $host->studio_name ?? 'Studio' }}</h1>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1 py-6">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="py-4 text-center border-t border-base-200 bg-base-100">
        <p class="text-xs text-base-content/50">
            Powered by <a href="{{ config('app.url') }}" class="font-medium text-primary hover:underline">FitCRM</a>
        </p>
    </footer>

    @stack('scripts')
</body>
</html>
