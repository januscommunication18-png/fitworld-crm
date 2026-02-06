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

    {{-- Page-specific modals (pushed from child views) --}}
    @stack('modals')

    {{-- Test Modal (temporary - to verify FlyonUI overlay works) --}}
    <div id="test-modal" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden" role="dialog" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Test Modal</h3>
                    <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#test-modal">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>If you can see this, FlyonUI overlay system is working!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft btn-secondary" data-overlay="#test-modal">Close</button>
                </div>
            </div>
        </div>
    </div>

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

    {{-- Test Modal Trigger (temporary) --}}
    <button type="button"
        class="btn btn-circle btn-warning btn-sm fixed bottom-28 right-4 z-50 shadow-lg"
        aria-haspopup="dialog" aria-expanded="false" aria-controls="test-modal" data-overlay="#test-modal"
        title="Test Modal">
        <span class="icon-[tabler--bug] size-4"></span>
    </button>

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
