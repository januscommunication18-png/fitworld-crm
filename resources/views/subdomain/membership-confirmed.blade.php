@extends('layouts.subdomain')

@section('title', 'Schedule Confirmed — ' . $host->studio_name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-base-200 via-base-100 to-base-200">
    {{-- Header --}}
    <nav class="bg-base-100/80 backdrop-blur-sm border-b border-base-200 sticky top-0 z-50" style="height: 70px;">
        <div class="container-fixed h-full">
            <div class="flex items-center justify-between h-full">
                @if($host->logo_url)
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center">
                        <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-10 w-auto max-w-[160px] object-contain">
                    </a>
                @else
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center">
                            <span class="text-base font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                        </div>
                        <span class="font-bold text-base hidden sm:inline">{{ $host->studio_name }}</span>
                    </a>
                @endif
            </div>
        </div>
    </nav>

    <div class="container-fixed py-10">
        <div class="max-w-lg mx-auto text-center">
            {{-- Success Icon --}}
            <div class="w-24 h-24 rounded-full bg-success/10 flex items-center justify-center mx-auto mb-6">
                <span class="icon-[tabler--circle-check-filled] size-16 text-success"></span>
            </div>

            {{-- Thank You Message --}}
            <h1 class="text-3xl font-bold mb-2">Thank You, {{ $client->first_name }}!</h1>
            <p class="text-lg text-base-content/60 mb-6">Your schedule has been confirmed.</p>

            @if($enrolledCount > 0)
            <div class="alert alert-success mb-6 justify-center">
                <span class="icon-[tabler--calendar-check] size-5"></span>
                <span>You've been enrolled into <strong>{{ $enrolledCount }}</strong> upcoming {{ Str::plural('session', $enrolledCount) }}.</span>
            </div>
            @endif

            {{-- Membership Info --}}
            <div class="card bg-base-100 shadow-lg border border-base-200 mb-6 text-left">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-success/10 flex items-center justify-center shrink-0">
                            <span class="icon-[tabler--id-badge-2] size-6 text-success"></span>
                        </div>
                        <div>
                            <h3 class="font-bold">{{ $membershipPlan->name }}</h3>
                            <div class="flex items-center gap-2 text-sm text-base-content/60">
                                <span class="badge badge-success badge-xs">Active</span>
                                @if($membership->expires_at)
                                    <span>Expires {{ $membership->expires_at->format('M j, Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- What's Next --}}
            <div class="card bg-base-100 shadow border border-base-200 mb-6 text-left">
                <div class="card-body">
                    <h3 class="font-semibold mb-3 flex items-center gap-2">
                        <span class="icon-[tabler--list-check] size-5 text-primary"></span>
                        What's Next
                    </h3>
                    <ul class="space-y-2 text-sm text-base-content/70">
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5 shrink-0"></span>
                            <span>You'll receive a confirmation email with your schedule details.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5 shrink-0"></span>
                            <span>Show up at your scheduled class times — you're already booked in!</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5 shrink-0"></span>
                            <span>Contact the studio if you need to make any changes.</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('subdomain.membership-access', ['subdomain' => $host->subdomain, 'accessToken' => $token]) }}" class="btn btn-outline">
                    <span class="icon-[tabler--arrow-left] size-4"></span>
                    View My Schedule
                </a>
                <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary">
                    <span class="icon-[tabler--home] size-4"></span>
                    Visit {{ $host->studio_name }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
