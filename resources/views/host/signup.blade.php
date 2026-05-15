<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sign Up — {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css', 'resources/js/apps/signup.js'])
</head>
<body class="bg-base-200 min-h-screen flex flex-col overflow-y-scroll">

    {{-- Top Navbar --}}
    <div id="signup-navbar" class="bg-base-100 border-b border-base-content/10 sticky top-0 z-40">
        <div class="flex items-center justify-between px-6 h-14">
            <div class="flex items-center gap-2">
                <span class="icon-[tabler--activity] size-6 text-primary"></span>
                <span id="navbar-title" class="text-lg font-bold">{{ Auth::check() ? '' : config('app.name', 'FitCRM') }}</span>
                <span id="navbar-welcome" class="{{ Auth::check() ? '' : 'hidden' }} text-sm font-semibold">Welcome, <span id="navbar-username">{{ Auth::user()?->first_name ?? '' }}</span></span>
            </div>
            <button id="navbar-logout" type="button" class="btn btn-outline btn-sm gap-1 {{ Auth::check() ? '' : 'hidden' }}" title="Log Off" onclick="doLogout()">
                <span class="icon-[tabler--logout] size-4"></span>
                <span class="hidden sm:inline">Log Off</span>
            </button>
            <script>
            function doLogout() {
                // Read XSRF-TOKEN cookie (stays current after session regeneration)
                var cookieMatch = document.cookie.match(/(^|;\s*)XSRF-TOKEN=([^;]*)/);
                var token = cookieMatch ? decodeURIComponent(cookieMatch[2]) : (document.querySelector('meta[name="csrf-token"]')?.content || '');
                fetch('/logout', {
                    method: 'POST',
                    headers: { 'X-XSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    credentials: 'same-origin'
                }).then(function() {
                    window.location.href = '/login';
                }).catch(function() {
                    window.location.href = '/login';
                });
            }
            </script>
        </div>
    </div>

    {{-- Content --}}
    <div class="flex-1 flex justify-center p-4 pt-8">
    {{-- Vue mount point with skeleton loading --}}
    <div id="signup-app" class="w-full mx-auto" style="max-width: 600px" data-csrf-token="{{ csrf_token() }}" data-smarty-key="{{ config('services.smarty.website_key', '') }}" data-authenticated="{{ Auth::check() ? 'true' : 'false' }}" data-email-verified="{{ request()->query('verified') ? 'true' : 'false' }}" data-user-name="{{ Auth::user()?->first_name ?? '' }}">
        {{-- Skeleton placeholder (shown until Vue mounts) --}}
        <div class="card w-full mx-auto" style="max-width: 600px">
            <div class="card-body animate-pulse">
                <div class="h-8 bg-base-300 rounded w-3/4 mx-auto mb-6"></div>
                <div class="h-4 bg-base-300 rounded w-1/2 mx-auto mb-8"></div>
                <div class="space-y-4">
                    <div class="h-10 bg-base-300 rounded"></div>
                    <div class="h-10 bg-base-300 rounded"></div>
                    <div class="h-10 bg-base-300 rounded"></div>
                </div>
                <div class="h-12 bg-base-300 rounded mt-8 w-1/3 mx-auto"></div>
            </div>
        </div>
    </div>
    </div>

</body>
</html>
