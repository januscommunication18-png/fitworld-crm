@extends('layouts.subdomain')

@section('title', 'Invalid Invitation')

@section('content')
<div class="card w-full max-w-md mx-auto">
    <div class="card-body text-center">
        <div class="mb-6">
            <div class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center mx-auto">
                <span class="icon-[tabler--link-off] size-8 text-error"></span>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-base-content mb-2">Invalid Invitation</h1>
        <p class="text-base-content/60 mb-6">
            {{ $error ?? 'This invitation link is invalid or has already been used.' }}
        </p>

        <div class="flex flex-col gap-3">
            <a href="{{ config('app.url') }}/login" class="btn btn-primary">
                <span class="icon-[tabler--login] size-4"></span>
                Sign In
            </a>
            <a href="{{ config('app.url') }}" class="btn btn-ghost">
                <span class="icon-[tabler--home] size-4"></span>
                Go to Homepage
            </a>
        </div>
    </div>
</div>
@endsection
