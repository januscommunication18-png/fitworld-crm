@extends('layouts.onboarding')

@section('title', 'Setup Your Studio')

@push('head')
    @vite(['resources/js/apps/onboarding.js'])
@endpush

@section('content')
    {{-- Vue mount point with skeleton loading --}}
    <div id="onboarding-app"
        data-csrf-token="{{ csrf_token() }}"
        data-smarty-key="{{ config('services.smarty.website_key', '') }}"
        data-user-id="{{ auth()->id() }}"
        data-host-id="{{ auth()->user()->host_id }}"
        data-email-verified="{{ auth()->user()->hasVerifiedEmail() ? 'true' : 'false' }}"
        data-phone-verified="{{ auth()->user()->hasVerifiedPhone() ? 'true' : 'false' }}">

        {{-- Skeleton placeholder (shown until Vue mounts) --}}
        <div class="animate-pulse">
            {{-- Progress bar skeleton --}}
            <div class="mb-8">
                <div class="flex items-center justify-between mb-2">
                    @for ($i = 1; $i <= 5; $i++)
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-base-300"></div>
                            @if ($i < 5)
                                <div class="w-12 sm:w-20 h-1 bg-base-300 mx-2"></div>
                            @endif
                        </div>
                    @endfor
                </div>
                <div class="flex justify-between text-xs">
                    <div class="h-3 bg-base-300 rounded w-16"></div>
                    <div class="h-3 bg-base-300 rounded w-16"></div>
                    <div class="h-3 bg-base-300 rounded w-16"></div>
                    <div class="h-3 bg-base-300 rounded w-16"></div>
                    <div class="h-3 bg-base-300 rounded w-16"></div>
                </div>
            </div>

            {{-- Card skeleton --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="h-6 bg-base-300 rounded w-1/2 mb-2"></div>
                    <div class="h-4 bg-base-300 rounded w-3/4 mb-6"></div>

                    <div class="space-y-4">
                        <div class="h-12 bg-base-300 rounded"></div>
                        <div class="h-12 bg-base-300 rounded"></div>
                        <div class="h-12 bg-base-300 rounded"></div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <div class="h-10 bg-base-300 rounded w-32"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
