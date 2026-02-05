<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css', 'resources/js/layout.js'])
    @stack('styles')
</head>
<body class="bg-base-200 min-h-screen">

    {{-- Navbar --}}
    @include('components.navbar')

    {{-- Sidebar --}}
    @include('components.sidebar')

    {{-- Main content wrapper — shifts when sidebar is open --}}
    <div class="sm:overlay-layout-open:ps-64 overlay-layout-open-minified:ps-17 min-h-[calc(100vh-64px)] transition-all duration-300">
        <main class="p-4 sm:p-6">
            {{-- Breadcrumbs --}}
            @include('components.breadcrumbs')

            {{-- Page content --}}
            @yield('content')
        </main>

        {{-- Footer --}}
        @include('components.footer')
    </div>

    {{-- Overlays --}}
    @include('components.search-modal')
    @include('components.alerts-drawer')
    @include('components.app-modal')

    {{-- Back to top button --}}
    <button id="back-to-top" type="button"
        class="btn btn-circle btn-primary btn-sm fixed bottom-4 right-4 z-50 hidden shadow-lg"
        aria-label="Back to top">
        <span class="icon-[tabler--arrow-up] size-4"></span>
    </button>

    {{-- Support button --}}
    <a href="#" id="support-btn"
        class="btn btn-circle btn-secondary btn-sm fixed bottom-16 right-4 z-50 shadow-lg"
        aria-label="Support">
        <span class="icon-[tabler--headset] size-4"></span>
    </a>

    {{-- Back to top script --}}
    <script>
        (function() {
            var btn = document.getElementById('back-to-top');
            window.addEventListener('scroll', function() {
                btn.classList.toggle('hidden', window.scrollY <= 300);
            });
            btn.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
