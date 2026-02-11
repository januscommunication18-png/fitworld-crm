<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Select Studio — {{ config('app.name', 'FitCRM') }}</title>

    @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center p-4">

    <div class="card w-full max-w-md mx-auto">
        <div class="card-body">
            {{-- Header --}}
            <div class="text-center mb-6">
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                    <span class="icon-[tabler--building-community] size-6 text-primary"></span>
                </div>
                <h1 class="text-2xl font-bold text-base-content">Welcome back, {{ Auth::user()->first_name }}!</h1>
                <p class="text-base-content/60 mt-1">Select a studio to continue</p>
            </div>

            {{-- Studio List --}}
            <div class="space-y-3">
                @foreach($hosts as $host)
                    <form method="POST" action="{{ route('switch-studio') }}">
                        @csrf
                        <input type="hidden" name="host_id" value="{{ $host->id }}">
                        <button type="submit" class="w-full text-left p-4 rounded-lg border border-base-content/10 hover:border-primary/50 hover:bg-base-100 transition-all group">
                            <div class="flex items-center gap-4">
                                {{-- Studio Logo/Icon --}}
                                @if($host->logo_url ?? false)
                                    <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="w-12 h-12 rounded-lg object-cover">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                                        <span class="icon-[tabler--building-community] size-6 text-primary"></span>
                                    </div>
                                @endif

                                {{-- Studio Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-base-content group-hover:text-primary transition-colors truncate">
                                        {{ $host->studio_name }}
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-base-content/60">
                                        <span class="badge badge-sm badge-soft badge-primary">{{ ucfirst($host->pivot->role) }}</span>
                                        @if($host->city ?? false)
                                            <span>{{ $host->city }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Arrow --}}
                                <span class="icon-[tabler--chevron-right] size-5 text-base-content/30 group-hover:text-primary transition-colors"></span>
                            </div>
                        </button>
                    </form>
                @endforeach
            </div>

            {{-- Logout --}}
            <div class="text-center mt-6 pt-6 border-t border-base-content/10">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="link link-hover text-sm text-base-content/60">
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
