@extends('layouts.subdomain')

@section('title', 'Select a Class — ' . $host->studio_name)

@php
    $selectedCurrency = session("currency_{$host->id}", $host->default_currency ?? 'USD');
    $currencySymbol = \App\Models\MembershipPlan::getCurrencySymbol($selectedCurrency);
@endphp

@section('content')
<div class="min-h-screen flex flex-col bg-base-200">
    {{-- Header --}}
    <nav class="bg-base-100 border-b border-base-200" style="height: 75px;">
        <div class="container-fixed h-full">
            <div class="flex items-center justify-between h-full">
                {{-- Logo --}}
                <div class="flex items-center">
                    @if($host->logo_url)
                        <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center">
                            <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-12 w-auto max-w-[180px] object-contain">
                        </a>
                    @else
                        <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center">
                                <span class="text-lg font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                            </div>
                            <span class="font-bold text-lg">{{ $host->studio_name }}</span>
                        </a>
                    @endif
                </div>

                {{-- Back --}}
                <a href="{{ route('booking.select-type', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                    Back
                </a>
            </div>
        </div>
    </nav>

    {{-- Progress Steps --}}
    <div class="bg-base-100 border-b border-base-200 py-4">
        <div class="container-fixed">
            <ul class="steps steps-horizontal w-full max-w-xl mx-auto">
                <li class="step step-primary">Select</li>
                <li class="step">Contact</li>
                <li class="step">Payment</li>
                <li class="step">Confirm</li>
            </ul>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="flex-1 py-8">
        <div class="container-fixed">
            <div class="flex flex-col lg:flex-row gap-6">
                {{-- Filters Sidebar --}}
                <div class="lg:w-64 shrink-0">
                    <div class="card bg-base-100 sticky top-6">
                        <div class="card-body">
                            <h3 class="font-semibold mb-4">Filter by Class Type</h3>
                            <ul class="space-y-2">
                                <li>
                                    <a href="{{ route('booking.select-class', ['subdomain' => $host->subdomain]) }}"
                                       class="flex items-center gap-2 p-2 rounded-lg {{ !$selectedPlanId ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-base-200' }}">
                                        <span class="icon-[tabler--list] size-5"></span>
                                        All Classes
                                    </a>
                                </li>
                                @foreach($classPlans as $plan)
                                <li>
                                    <a href="{{ route('booking.select-class.filter', ['subdomain' => $host->subdomain, 'classPlanId' => $plan->id]) }}"
                                       class="flex items-center gap-2 p-2 rounded-lg {{ $selectedPlanId == $plan->id ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-base-200' }}">
                                        <span class="icon-[tabler--yoga] size-5"></span>
                                        {{ $plan->name }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Sessions List --}}
                <div class="flex-1">
                    <h1 class="text-2xl font-bold mb-6">
                        @if($selectedPlanId)
                            {{ $classPlans->firstWhere('id', $selectedPlanId)?->name ?? 'Classes' }}
                        @else
                            Upcoming Classes
                        @endif
                    </h1>

                    @if(session('error'))
                        <div class="alert alert-error mb-6">
                            <span class="icon-[tabler--alert-circle] size-5"></span>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @if($sessions->count() > 0)
                        @foreach($sessionsByDate as $date => $dateSessions)
                            <div class="mb-6">
                                <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">
                                    <span class="icon-[tabler--calendar] size-5 text-primary"></span>
                                    {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                                </h2>

                                <div class="space-y-3">
                                    @foreach($dateSessions as $session)
                                    <div class="card bg-base-100">
                                        <div class="card-body p-4">
                                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                                {{-- Time --}}
                                                <div class="text-center sm:text-left sm:w-24">
                                                    <p class="text-lg font-bold">{{ $session->start_time->format('g:i A') }}</p>
                                                    <p class="text-xs text-base-content/60">{{ $session->duration_minutes ?? 60 }} min</p>
                                                </div>

                                                {{-- Details --}}
                                                <div class="flex-1">
                                                    <h3 class="font-semibold">{{ $session->display_title }}</h3>
                                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-base-content/60 mt-1">
                                                        @if($session->primaryInstructor)
                                                        <span class="flex items-center gap-1">
                                                            <span class="icon-[tabler--user] size-4"></span>
                                                            {{ $session->primaryInstructor->name }}
                                                        </span>
                                                        @endif
                                                        @if($session->room?->location)
                                                        <span class="flex items-center gap-1">
                                                            <span class="icon-[tabler--map-pin] size-4"></span>
                                                            {{ $session->room->location->name }}
                                                        </span>
                                                        @endif
                                                        @if($session->room)
                                                        <span class="flex items-center gap-1">
                                                            <span class="icon-[tabler--door] size-4"></span>
                                                            {{ $session->room->name }}
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Spots & Price --}}
                                                <div class="flex items-center gap-4">
                                                    @php
                                                        $spotsLeft = ($session->max_capacity ?? 20) - ($session->bookings_count ?? 0);
                                                        $isFull = $spotsLeft <= 0;
                                                    @endphp
                                                    <div class="text-right">
                                                        @if($isFull)
                                                            @if($session->allow_waitlist ?? false)
                                                                <span class="badge badge-warning">Waitlist</span>
                                                            @else
                                                                <span class="badge badge-error">Full</span>
                                                            @endif
                                                        @elseif($spotsLeft <= 3)
                                                            <span class="badge badge-warning">{{ $spotsLeft }} spots left</span>
                                                        @else
                                                            <span class="text-sm text-base-content/60">{{ $spotsLeft }} spots</span>
                                                        @endif
                                                        @php
                                                            $price = $session->price ?? $session->classPlan?->getDropInPriceForCurrency($selectedCurrency) ?? 0;
                                                        @endphp
                                                        @if($price > 0)
                                                        <p class="text-lg font-bold text-primary mt-1">{{ $currencySymbol }}{{ number_format($price, 2) }}</p>
                                                        @else
                                                        <p class="text-lg font-bold text-success mt-1">Free</p>
                                                        @endif
                                                    </div>

                                                    @if(!$isFull || ($session->allow_waitlist ?? false))
                                                    <form action="{{ route('booking.select-class-session', ['subdomain' => $host->subdomain, 'session' => $session->id]) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            @if($isFull)
                                                                Join Waitlist
                                                            @else
                                                                Book Now
                                                            @endif
                                                        </button>
                                                    </form>
                                                    @else
                                                    <button class="btn btn-disabled btn-sm" disabled>Full</button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="card bg-base-100">
                            <div class="card-body text-center py-12">
                                <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto"></span>
                                <h3 class="text-lg font-semibold mt-4">No Upcoming Classes</h3>
                                <p class="text-base-content/60 mt-2">
                                    @if($selectedPlanId)
                                        There are no scheduled sessions for this class type.
                                    @else
                                        There are no classes scheduled at this time.
                                    @endif
                                </p>
                                <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary mt-4">
                                    Back to Home
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
