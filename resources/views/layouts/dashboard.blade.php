<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css', 'resources/js/layout.js'])
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
    @stack('styles')
    @stack('head')
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
    @include('components.global-alert-modal')

    {{-- Global Confirmation Modals --}}
    @include('partials.modals.confirmation-modals')

    {{-- Page-specific modals (pushed from child views) --}}
    @stack('modals')

    {{-- Back to top button --}}
    <button id="back-to-top" type="button"
        class="btn btn-circle btn-primary btn-sm fixed bottom-4 right-4 z-50 hidden shadow-lg"
        aria-label="Back to top">
        <span class="icon-[tabler--arrow-up] size-4"></span>
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

    <script src="{{ asset('js/drawer.js') }}"></script>

    {{-- Global drawer change tracking: disable save buttons until form changes --}}
    <script>
    (function() {
        var drawerSnapshots = {};

        function snapshotForm(form) {
            var data = {};
            form.querySelectorAll('input, textarea, select').forEach(function(el) {
                if (!el.name && !el.id) return;
                var key = el.name || el.id;
                if (el.type === 'checkbox' || el.type === 'radio') {
                    data[key + '_' + el.value] = el.checked;
                } else if (el.type === 'file') {
                    data[key] = '';
                } else {
                    data[key] = el.value;
                }
            });
            return JSON.stringify(data);
        }

        function getSubmitBtn(drawer) {
            return drawer.querySelector('button[type="submit"], [id$="-btn"][class*="btn-primary"]:last-of-type');
        }

        function checkDrawerChanged(drawer) {
            var form = drawer.querySelector('form');
            if (!form) return;
            var id = drawer.id;
            var btn = getSubmitBtn(drawer);
            if (!btn || !drawerSnapshots[id]) return;
            // Skip file upload drawers
            if (form.querySelector('input[type="file"]') && !form.querySelector('input[type="text"], textarea')) return;
            var current = snapshotForm(form);
            btn.disabled = (current === drawerSnapshots[id]);
        }

        // Observe drawer visibility changes via MutationObserver
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(m) {
                if (m.type !== 'attributes') return;
                var drawer = m.target;
                if (!drawer.id) return;

                var isOpen = !drawer.classList.contains('translate-x-full') && !drawer.classList.contains('hidden');
                if (isOpen && !drawerSnapshots[drawer.id]) {
                    var form = drawer.querySelector('form');
                    if (form) {
                        // Snapshot after a tick so values are populated
                        setTimeout(function() {
                            drawerSnapshots[drawer.id] = snapshotForm(form);
                            var btn = getSubmitBtn(drawer);
                            if (btn) btn.disabled = true;

                            // Listen for changes
                            form.addEventListener('input', function() { checkDrawerChanged(drawer); });
                            form.addEventListener('change', function() { checkDrawerChanged(drawer); });
                        }, 50);
                    }
                } else if (!isOpen && drawerSnapshots[drawer.id]) {
                    delete drawerSnapshots[drawer.id];
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Observe all drawer elements
            document.querySelectorAll('[class*="translate-x-full"]').forEach(function(drawer) {
                observer.observe(drawer, { attributes: true, attributeFilter: ['class'] });
            });
        });

        // Expose for manual use
        window.DrawerChangeTracker = {
            snapshot: function(drawerId) {
                var drawer = document.getElementById(drawerId);
                if (drawer) {
                    var form = drawer.querySelector('form');
                    if (form) drawerSnapshots[drawerId] = snapshotForm(form);
                }
            },
            check: function(drawerId) {
                var drawer = document.getElementById(drawerId);
                if (drawer) checkDrawerChanged(drawer);
            }
        };
    })();
    </script>
    {{-- Global input validation rules --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Name fields: strip digits on input
        var nameFields = ['first_name', 'last_name', 'contact_name', 'member_name', 'instructor_name'];
        document.querySelectorAll('input[type="text"]').forEach(function(input) {
            var name = (input.name || '').toLowerCase();
            var id = (input.id || '').toLowerCase();
            var isNameField = nameFields.some(function(f) { return name === f || id === f || name.endsWith('_name') && (name.includes('first') || name.includes('last') || name.includes('contact') || name.includes('member')); });
            if (isNameField) {
                input.setAttribute('pattern', "^[A-Za-z\\s\\-']+$");
                input.setAttribute('title', 'Only letters, spaces, hyphens, or apostrophes');
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[0-9]/g, '');
                });
            }
        });
    });
    </script>
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
