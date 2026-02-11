<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Studio Not Found — {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center p-4">
    <div class="card w-full max-w-md mx-auto">
        <div class="card-body text-center">
            <div class="mb-6">
                <div class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center mx-auto">
                    <span class="icon-[tabler--building-community-off] size-8 text-error"></span>
                </div>
            </div>

            <h1 class="text-2xl font-bold text-base-content mb-2">Studio Not Found</h1>
            <p class="text-base-content/60 mb-6">
                We couldn't find a studio at this address. The studio may have been removed or the URL may be incorrect.
            </p>

            @if(isset($subdomain))
                <p class="text-sm text-base-content/40 mb-6">
                    Looking for: <code class="bg-base-300 px-2 py-1 rounded">{{ $subdomain }}</code>
                </p>
            @endif

            <a href="{{ config('app.url') }}" class="btn btn-primary">
                <span class="icon-[tabler--home] size-4"></span>
                Go to Homepage
            </a>
        </div>
    </div>
</body>
</html>
