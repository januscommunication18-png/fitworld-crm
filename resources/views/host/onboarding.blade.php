<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Complete Your Setup — {{ config('app.name', 'FitStudioHQ') }}</title>

    @vite(['resources/css/app.css', 'resources/js/apps/onboarding.js'])
</head>
<body class="bg-base-200 min-h-screen">

    {{-- Top bar with logout option --}}
    <div class="fixed top-0 right-0 p-4 z-50">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost btn-sm gap-2">
                <span class="icon-[tabler--logout] size-4"></span>
                Log Out
            </button>
        </form>
    </div>

    {{-- Vue mount point with data attributes --}}
    <div id="onboarding-app"
        class="min-h-screen flex items-center justify-center p-4 pt-16"
        data-csrf-token="{{ csrf_token() }}"
        data-email-verified="{{ $user->hasVerifiedEmail() ? 'true' : 'false' }}"
        data-phone-verified="{{ $host->hasOwnerPhoneVerified() ? 'true' : 'false' }}"
        data-tech-support-requested="{{ $host->hasTechSupportRequested() ? 'true' : 'false' }}"
        data-tech-support-pending="{{ $host->hasTechSupportPending() ? 'true' : 'false' }}"
        data-current-step="{{ $host->post_signup_step }}"
        data-studio-name="{{ $host->studio_name }}"
        data-user-email="{{ $user->email }}"
        data-user-name="{{ $user->first_name }} {{ $user->last_name }}"
    >
        {{-- Skeleton placeholder (shown until Vue mounts) --}}
        <div class="card w-full max-w-2xl mx-auto">
            <div class="card-body">
                {{-- Progress bar skeleton --}}
                <div class="flex items-center justify-between mb-8">
                    @for ($i = 1; $i <= 5; $i++)
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full bg-base-300 animate-pulse"></div>
                            <div class="h-3 w-16 bg-base-300 rounded mt-2 animate-pulse"></div>
                        </div>
                        @if ($i < 5)
                            <div class="flex-1 h-1 bg-base-300 mx-2 animate-pulse"></div>
                        @endif
                    @endfor
                </div>

                {{-- Content skeleton --}}
                <div class="animate-pulse">
                    <div class="h-8 bg-base-300 rounded w-3/4 mb-4"></div>
                    <div class="h-4 bg-base-300 rounded w-1/2 mb-8"></div>
                    <div class="space-y-4">
                        <div class="h-12 bg-base-300 rounded"></div>
                        <div class="h-12 bg-base-300 rounded"></div>
                        <div class="h-12 bg-base-300 rounded"></div>
                    </div>
                    <div class="flex justify-between mt-8">
                        <div class="h-10 bg-base-300 rounded w-24"></div>
                        <div class="h-10 bg-base-300 rounded w-24"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
