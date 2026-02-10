<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sign Up — {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css', 'resources/js/apps/signup.js'])
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center p-4">

    {{-- Vue mount point with skeleton loading --}}
    <div id="signup-app" data-csrf-token="{{ csrf_token() }}" data-smarty-key="{{ config('services.smarty.website_key', '') }}" data-authenticated="{{ Auth::check() ? 'true' : 'false' }}" data-email-verified="{{ request()->query('verified') ? 'true' : 'false' }}">
        {{-- Skeleton placeholder (shown until Vue mounts) --}}
        <div class="card w-full max-w-2xl mx-auto">
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

</body>
</html>
