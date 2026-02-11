@extends('layouts.subdomain')

@section('title', 'Invitation Expired')

@section('content')
<div class="card w-full max-w-md mx-auto">
    <div class="card-body text-center">
        <div class="mb-6">
            <div class="w-16 h-16 rounded-full bg-warning/10 flex items-center justify-center mx-auto">
                <span class="icon-[tabler--clock-off] size-8 text-warning"></span>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-base-content mb-2">Invitation Expired</h1>
        <p class="text-base-content/60 mb-6">
            This invitation expired on {{ $invitation->expires_at->format('M j, Y') }}.
        </p>

        <div class="alert alert-soft alert-info text-left mb-6">
            <span class="icon-[tabler--info-circle] size-5 shrink-0"></span>
            <p class="text-sm">
                Please contact <strong>{{ $host->studio_name }}</strong> and ask them to send you a new invitation.
            </p>
        </div>

        <a href="{{ config('app.url') }}" class="btn btn-ghost">
            <span class="icon-[tabler--home] size-4"></span>
            Go to Homepage
        </a>
    </div>
</div>
@endsection
