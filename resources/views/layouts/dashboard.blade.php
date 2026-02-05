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

    {{-- Mobile sidebar backdrop --}}
    <div id="sidebar-backdrop" class="fixed inset-0 bg-black/50 z-30 hidden" onclick="window.FitCRM.closeMobileSidebar()"></div>

    {{-- Layout wrapper: two-column flex --}}
    <div id="layout-wrapper">

        {{-- Column 1: Sidebar --}}
        @include('components.sidebar')

        {{-- Column 2: Main content --}}
        <div id="main-content">

            {{-- Toolbar --}}
            @include('components.navbar')

            {{-- Scrollable content area --}}
            <div id="content-area" class="flex-1 overflow-y-auto">
                <div class="p-6">
                    {{-- Breadcrumbs --}}
                    @include('components.breadcrumbs')

                    {{-- Page content --}}
                    @yield('content')
                </div>
            </div>

            {{-- Footer --}}
            @include('components.footer')
        </div>
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
