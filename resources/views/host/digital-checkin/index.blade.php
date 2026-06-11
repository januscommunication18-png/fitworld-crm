@extends('layouts.dashboard')

@section('title', 'Digital Check-In')

@section('breadcrumbs')
    <ol>
        <li>
            <a href="{{ url('/dashboard') }}">
                <span class="icon-[tabler--home] size-4"></span> Dashboard
            </a>
        </li>
        <li class="breadcrumbs-separator rtl:rotate-180">
            <span class="icon-[tabler--chevron-right]"></span>
        </li>
        <li aria-current="page">
            <span class="icon-[tabler--qrcode] me-1 size-4"></span> Digital Check-In
        </li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-semibold">Digital Check-In</h1>
        <p class="text-sm text-base-content/60 mt-1">
            Scan a client's QR code (or look them up) to check them into today's classes, services, or open gym.
        </p>
    </div>

    <div id="digital-checkin-app"
         data-csrf="{{ csrf_token() }}"
         data-resolve-url="{{ route('digital-checkin.resolve') }}"
         data-confirm-url="{{ route('digital-checkin.confirm') }}"
         data-search-url="{{ route('digital-checkin.search') }}"
         data-allow-override="{{ $host->getPolicy('allow_staff_override', true) ? 'true' : 'false' }}"
         data-qr-enabled="{{ $host->getPolicy('enable_qr_checkin', true) ? 'true' : 'false' }}">
        {{-- Skeleton while the Vue app boots --}}
        <div class="max-w-2xl mx-auto">
            <div class="flex gap-2 mb-5">
                <div class="skeleton h-8 w-28 rounded-md"></div>
                <div class="skeleton h-8 w-32 rounded-md"></div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body items-center">
                    <div class="skeleton h-64 w-full max-w-sm rounded-lg"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
    @vite(['resources/js/apps/digital-checkin.js'])
@endpush
