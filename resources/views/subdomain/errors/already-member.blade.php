@extends('layouts.subdomain')

@section('title', 'Already a Member')

@section('content')
<div class="card w-full max-w-md mx-auto">
    <div class="card-body text-center">
        <div class="mb-6">
            <div class="w-16 h-16 rounded-full bg-success/10 flex items-center justify-center mx-auto">
                <span class="icon-[tabler--user-check] size-8 text-success"></span>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-base-content mb-2">Already a Member</h1>
        <p class="text-base-content/60 mb-6">
            You're already a member of <strong class="text-primary">{{ $host->studio_name }}</strong>.
        </p>

        <a href="{{ config('app.url') }}/login" class="btn btn-primary">
            <span class="icon-[tabler--login] size-4"></span>
            Sign In
        </a>
    </div>
</div>
@endsection
