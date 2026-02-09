<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') — {{ config('app.name', 'FitCRM') }} Backoffice</title>

    @vite(['resources/css/app.css'])
    @stack('styles')
</head>
<body class="bg-base-200 min-h-screen">

    {{-- Mobile sidebar backdrop --}}
    <div id="admin-sidebar-backdrop" class="fixed inset-0 bg-black/50 z-30 hidden"></div>

    {{-- Layout wrapper: two-column flex --}}
    <div id="admin-layout-wrapper">

        {{-- Column 1: Sidebar --}}
        @include('backoffice.components.sidebar')

        {{-- Column 2: Main content --}}
        <div id="admin-main-content">

            {{-- Navbar --}}
            @include('backoffice.components.navbar')

            {{-- Scrollable content area --}}
            <div class="flex-1 overflow-y-auto">
                <div class="p-6">
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

                    {{-- Page content --}}
                    @yield('content')
                </div>
            </div>

            {{-- Footer --}}
            <footer class="border-t border-base-content/10 bg-base-100 px-6 py-4">
                <div class="flex items-center justify-between text-sm text-base-content/60">
                    <span>&copy; {{ date('Y') }} {{ config('app.name', 'FitCRM') }}. All rights reserved.</span>
                    <span>Admin Backoffice v1.0</span>
                </div>
            </footer>
        </div>
    </div>

    {{-- Page-specific modals --}}
    @stack('modals')

    {{-- Mobile sidebar toggle script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var sidebar = document.getElementById('admin-sidebar');
            var backdrop = document.getElementById('admin-sidebar-backdrop');
            var toggleBtn = document.getElementById('mobile-menu-btn');

            function openMobileSidebar() {
                sidebar.classList.add('mobile-open');
                backdrop.classList.add('show');
                backdrop.classList.remove('hidden');
            }

            function closeMobileSidebar() {
                sidebar.classList.remove('mobile-open');
                backdrop.classList.remove('show');
                backdrop.classList.add('hidden');
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    if (sidebar.classList.contains('mobile-open')) {
                        closeMobileSidebar();
                    } else {
                        openMobileSidebar();
                    }
                });
            }

            if (backdrop) {
                backdrop.addEventListener('click', closeMobileSidebar);
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
