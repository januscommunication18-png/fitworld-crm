@extends('layouts.subdomain')

@section('title', 'Wrong Studio')

@section('content')
<div class="card w-full max-w-md mx-auto">
    <div class="card-body text-center">
        <div class="mb-6">
            <div class="w-16 h-16 rounded-full bg-warning/10 flex items-center justify-center mx-auto">
                <span class="icon-[tabler--arrows-exchange] size-8 text-warning"></span>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-base-content mb-2">Wrong Studio</h1>
        <p class="text-base-content/60 mb-6">
            This invitation is for <strong class="text-primary">{{ $correctHost->studio_name }}</strong>, not {{ $host->studio_name }}.
        </p>

        <p class="text-sm text-base-content/60 mb-6">
            Click the button below to go to the correct studio's invitation page.
        </p>

        <a href="{{ $correctUrl }}" class="btn btn-primary w-full">
            <span class="icon-[tabler--external-link] size-4"></span>
            Go to {{ $correctHost->studio_name }}
        </a>
    </div>
</div>
@endsection
