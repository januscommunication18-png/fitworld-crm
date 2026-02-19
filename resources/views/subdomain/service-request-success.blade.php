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
                <p class="text-sm text-base-content/60">Service Request</p>
            </div>
        </div>
    </div>
</div>

{{-- Main Content --}}
<div class="max-w-3xl mx-auto w-full px-4 py-8">
    <div class="card bg-base-100 border border-base-200">
        <div class="card-body p-8 text-center">
            {{-- Success Icon --}}
            <div class="w-20 h-20 rounded-full bg-success/10 flex items-center justify-center mx-auto mb-6">
                <span class="icon-[tabler--check] size-10 text-success"></span>
            </div>

            <h2 class="text-2xl font-bold text-base-content mb-2">Request Submitted!</h2>
            <p class="text-base-content/60 mb-6">
                Thank you for your interest! We've received your service request and will get back to you shortly to confirm your appointment.
            </p>

            {{-- What's Next --}}
            <div class="bg-base-200 rounded-lg p-6 text-left mb-6">
                <h3 class="font-semibold mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--list-check] size-5 text-primary"></span>
                    What happens next?
                </h3>
                <ol class="list-decimal list-inside space-y-2 text-base-content/70">
                    <li>Our team will review your request</li>
                    <li>We'll reach out to confirm availability</li>
                    <li>You'll receive a confirmation email with appointment details</li>
                </ol>
            </div>

            {{-- Contact Info --}}
            @if($host->phone || $host->studio_email)
            <p class="text-sm text-base-content/60 mb-6">
                Questions? Contact us at
                @if($host->phone)
                    <a href="tel:{{ $host->phone }}" class="link link-primary">{{ $host->phone }}</a>
                @endif
                @if($host->phone && $host->studio_email) or @endif
                @if($host->studio_email)
                    <a href="mailto:{{ $host->studio_email }}" class="link link-primary">{{ $host->studio_email }}</a>
                @endif
            </p>
            @endif

            {{-- Back to Home --}}
            <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary">
                <span class="icon-[tabler--arrow-left] size-5"></span>
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
