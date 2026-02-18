@extends('layouts.subdomain')

@section('title', 'Request Submitted — ' . $host->studio_name)

@section('content')
@php
    $logoUrl = $host->logo_path ? Storage::disk(config('filesystems.uploads'))->url($host->logo_path) : null;
@endphp

{{-- Header --}}
<div class="bg-base-200 border-b border-base-300">
    <div class="max-w-3xl mx-auto px-4 py-6">
        <div class="flex items-center gap-4">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $host->studio_name }}" class="h-10 w-auto">
            @else
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--building-community] size-5 text-primary"></span>
                </div>
            @endif
            <div>
                <h1 class="font-bold text-lg text-base-content">{{ $host->studio_name }}</h1>
                <p class="text-sm text-base-content/60">Request Submitted</p>
            </div>
        </div>
    </div>
</div>

{{-- Main Content --}}
<div class="max-w-3xl mx-auto w-full px-4 py-16">
    <div class="text-center">
        {{-- Success Icon --}}
        <div class="w-20 h-20 rounded-full bg-success/10 flex items-center justify-center mx-auto mb-6">
            <span class="icon-[tabler--check] size-10 text-success"></span>
        </div>

        <h2 class="text-3xl font-bold text-base-content mb-3">Thank You!</h2>
        <p class="text-lg text-base-content/70 mb-8 max-w-md mx-auto">
            Your class request has been submitted successfully. We'll review your request and get back to you soon.
        </p>

        {{-- What's Next --}}
        <div class="card bg-base-100 border border-base-200 max-w-md mx-auto mb-8">
            <div class="card-body p-6">
                <h3 class="font-semibold text-base-content mb-4">What happens next?</h3>
                <ul class="text-left space-y-3 text-base-content/70">
                    <li class="flex items-start gap-3">
                        <span class="icon-[tabler--circle-check] size-5 text-success shrink-0 mt-0.5"></span>
                        <span>We'll review your request and availability</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="icon-[tabler--mail] size-5 text-primary shrink-0 mt-0.5"></span>
                        <span>You'll receive an email when the class is scheduled</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="icon-[tabler--calendar-plus] size-5 text-info shrink-0 mt-0.5"></span>
                        <span>Book your spot once it appears on our schedule</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}"
               class="btn btn-primary">
                <span class="icon-[tabler--calendar] size-5"></span>
                View Schedule
            </a>
            <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}"
               class="btn btn-ghost">
                <span class="icon-[tabler--home] size-5"></span>
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
