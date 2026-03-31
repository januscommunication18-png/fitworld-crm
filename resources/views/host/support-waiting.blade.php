<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Support Request Submitted — {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 min-h-screen flex flex-col">

    {{-- Minimal header --}}
    <header class="bg-base-100 border-b border-base-300">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2">
                <img src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }}" class="h-8">
                <span class="font-semibold text-lg hidden sm:inline">{{ config('app.name') }}</span>
            </a>

            {{-- Logout only --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm gap-2">
                    <span class="icon-[tabler--logout] size-4"></span>
                    <span class="hidden sm:inline">Logout</span>
                </button>
            </form>
        </div>
    </header>

    {{-- Main content --}}
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="text-center max-w-md">
            {{-- Success icon --}}
            <div class="mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-success/10">
                    <span class="icon-[tabler--headset] size-10 text-success"></span>
                </div>
            </div>

            {{-- Message --}}
            <h1 class="text-2xl font-bold mb-3">Support Request Submitted</h1>
            <p class="text-base-content/70 mb-6">
                Our team will contact you shortly to help you get set up. We typically respond within 24 hours during business days.
            </p>

            {{-- Contact info --}}
            @if($supportRequest ?? null)
                <div class="card bg-base-100 text-left mb-6">
                    <div class="card-body py-4">
                        <h3 class="text-sm font-semibold text-base-content/70 mb-2">Your Contact Information</h3>
                        <div class="space-y-1 text-sm">
                            <p><span class="font-medium">Name:</span> {{ $supportRequest->first_name }} {{ $supportRequest->last_name }}</p>
                            <p><span class="font-medium">Email:</span> {{ $supportRequest->email }}</p>
                            @if($supportRequest->phone)
                                <p><span class="font-medium">Phone:</span> {{ $supportRequest->phone }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Alternative contact --}}
            <div class="text-sm text-base-content/60">
                <p>Need immediate assistance?</p>
                <p class="mt-1">
                    Email us at <a href="mailto:support@{{ config('app.domain', 'fitcrm.com') }}" class="link link-primary">support@{{ config('app.domain', 'fitcrm.com') }}</a>
                </p>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="py-4 text-center text-sm text-base-content/50">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </footer>

</body>
</html>
