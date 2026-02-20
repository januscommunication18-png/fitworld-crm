@extends('layouts.subdomain')

@section('title', 'Booking — ' . $host->studio_name)

@section('content')
@php
    $selectedCurrency = session("currency_{$host->id}", $host->default_currency ?? 'USD');
    $currencySymbol = \App\Models\MembershipPlan::getCurrencySymbol($selectedCurrency);

    // Helper function to check if a class plan is covered by any active membership
    $isCoveredByMembership = function($classPlan) use ($activeMemberships) {
        if (!$classPlan) return null;
        foreach ($activeMemberships as $membership) {
            if ($membership->canUseForClassPlan($classPlan) && $membership->hasAvailableCredits()) {
                return $membership;
            }
        }
        return null;
    };
@endphp

<div class="min-h-screen flex flex-col">
    @include('subdomain.member.portal._nav')

    <div class="flex-1 bg-base-200">
        <div class="container-fixed py-8">
            {{-- Active Membership Banner --}}
            @if($activeMemberships->count() > 0)
            <div class="alert bg-success/10 text-success border-success/20 mb-6">
                <span class="icon-[tabler--id-badge-2] size-5"></span>
                <div>
                    <h4 class="font-semibold">Active Membership</h4>
                    <p class="text-sm opacity-80">
                        @foreach($activeMemberships as $membership)
                            <span class="font-medium">{{ $membership->membershipPlan->name }}</span>
                            @if(!$membership->is_unlimited)
                                ({{ $membership->credits_remaining }} credits remaining)
                            @else
                                (Unlimited)
                            @endif
                            @if(!$loop->last), @endif
                        @endforeach
                    </p>
                </div>
            </div>
            @endif

            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Booking</h1>
            </div>

            {{-- Tabs --}}
            <div class="tabs tabs-boxed bg-base-100 w-fit mb-6">
                <a href="{{ route('member.portal.booking', ['subdomain' => $host->subdomain]) }}"
                   class="tab {{ request()->routeIs('member.portal.booking') && !request()->routeIs('member.portal.schedule', 'member.portal.services', 'member.portal.memberships') ? 'tab-active' : '' }}">
                    All
                </a>
                <a href="{{ route('member.portal.schedule', ['subdomain' => $host->subdomain]) }}"
                   class="tab {{ request()->routeIs('member.portal.schedule') ? 'tab-active' : '' }}">
                    Classes
                </a>
                <a href="{{ route('member.portal.services', ['subdomain' => $host->subdomain]) }}"
                   class="tab {{ request()->routeIs('member.portal.services') ? 'tab-active' : '' }}">
                    Services
                </a>
                <a href="{{ route('member.portal.memberships', ['subdomain' => $host->subdomain]) }}"
                   class="tab {{ request()->routeIs('member.portal.memberships') ? 'tab-active' : '' }}">
                    Memberships
                </a>
            </div>

            {{-- Upcoming Classes Section --}}
            @if($upcomingSessions->count() > 0)
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Upcoming Classes</h2>
                    <a href="{{ route('member.portal.schedule', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                        View All
                        <span class="icon-[tabler--arrow-right] size-4"></span>
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($upcomingSessions as $session)
                    @php
                        $coveringMembership = $isCoveredByMembership($session->classPlan);
                    @endphp
                    <div class="card bg-base-100 {{ $coveringMembership ? 'border-2 border-success/30' : '' }}">
                        <div class="card-body py-4">
                            <div class="flex items-start gap-3">
                                <div class="text-center min-w-[50px] p-2 {{ $coveringMembership ? 'bg-success/10' : 'bg-primary/10' }} rounded-lg">
                                    <p class="text-lg font-bold {{ $coveringMembership ? 'text-success' : 'text-primary' }}">{{ $session->start_time->format('j') }}</p>
                                    <p class="text-xs {{ $coveringMembership ? 'text-success' : 'text-primary' }} uppercase">{{ $session->start_time->format('M') }}</p>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium truncate">{{ $session->display_title ?? $session->classPlan?->name }}</h3>
                                    <p class="text-sm text-base-content/60">
                                        {{ $session->start_time->format('g:i A') }}
                                        @if($session->primaryInstructor)
                                            • {{ $session->primaryInstructor->name }}
                                        @endif
                                    </p>
                                    @if($coveringMembership)
                                    <span class="badge badge-success badge-sm mt-1 gap-1">
                                        <span class="icon-[tabler--id-badge-2] size-3"></span>
                                        Included
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-2 mt-3">
                                <a href="{{ route('subdomain.class-request.session', ['subdomain' => $host->subdomain, 'sessionId' => $session->id]) }}"
                                   class="btn btn-ghost btn-xs flex-1">
                                    Request Info
                                </a>
                                <form action="{{ route('booking.select-class-session', ['subdomain' => $host->subdomain, 'session' => $session->id]) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="btn {{ $coveringMembership ? 'btn-success' : 'btn-primary' }} btn-xs w-full">
                                        {{ $coveringMembership ? 'Book Free' : 'Book Now' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Services Section --}}
            @if($servicePlans->count() > 0)
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Services</h2>
                    <a href="{{ route('member.portal.services', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                        View All
                        <span class="icon-[tabler--arrow-right] size-4"></span>
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($servicePlans->take(3) as $service)
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h3 class="card-title text-base">{{ $service->name }}</h3>
                            @if($service->description)
                                <p class="text-sm text-base-content/60 line-clamp-2">{{ $service->description }}</p>
                            @endif
                            <div class="flex items-center justify-between mt-2">
                                <div>
                                    @if($service->duration_minutes)
                                        <span class="text-sm text-base-content/60">{{ $service->duration_minutes }} min</span>
                                    @endif
                                    @php $servicePrice = $service->getPriceForCurrency($selectedCurrency); @endphp
                                    @if($servicePrice)
                                        <span class="font-semibold text-primary ml-2">{{ $currencySymbol }}{{ number_format($servicePrice, 2) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-2 mt-3">
                                <a href="{{ route('subdomain.service-request', ['subdomain' => $host->subdomain, 'servicePlanId' => $service->id]) }}"
                                   class="btn btn-ghost btn-sm flex-1">
                                    Request Info
                                </a>
                                <a href="{{ route('booking.select-service.filter', ['subdomain' => $host->subdomain, 'servicePlanId' => $service->id]) }}"
                                   class="btn btn-primary btn-sm flex-1">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Memberships & Class Packs Section --}}
            @if($membershipPlans->count() > 0 || $classPacks->count() > 0)
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Memberships & Passes</h2>
                    <a href="{{ route('member.portal.memberships', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                        View All
                        <span class="icon-[tabler--arrow-right] size-4"></span>
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($membershipPlans->take(2) as $plan)
                    <div class="card bg-base-100 border-2 border-primary/20">
                        <div class="card-body">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="badge badge-primary badge-sm mb-2">Membership</span>
                                    <h3 class="card-title text-base">{{ $plan->name }}</h3>
                                </div>
                            </div>
                            @if($plan->description)
                                <p class="text-sm text-base-content/60 line-clamp-2">{{ $plan->description }}</p>
                            @endif
                            <div class="mt-2">
                                @php $membershipPrice = $plan->getPriceForCurrency($selectedCurrency); @endphp
                                <span class="text-2xl font-bold text-primary">{{ $currencySymbol }}{{ number_format($membershipPrice ?? 0, 2) }}</span>
                                <span class="text-base-content/60">{{ $plan->formatted_interval }}</span>
                            </div>

                            @if($plan->addon_members > 0)
                            <div class="text-xs text-base-content/50 mt-1">
                                <span class="icon-[tabler--users-plus] size-3 inline-block"></span>
                                +{{ $plan->addon_members }} {{ Str::plural('guest', $plan->addon_members) }}
                            </div>
                            @endif

                            <form action="{{ route('booking.select-membership-plan', ['subdomain' => $host->subdomain, 'plan' => $plan->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm mt-3">
                                    Get Started
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach

                    @foreach($classPacks->take(2) as $pack)
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="badge badge-secondary badge-sm mb-2">Class Pack</span>
                                    <h3 class="card-title text-base">{{ $pack->name }}</h3>
                                </div>
                            </div>
                            <p class="text-sm text-base-content/60">
                                {{ $pack->class_count }} classes
                                @if($pack->validity_days)
                                    • Valid for {{ $pack->validity_days }} days
                                @endif
                            </p>
                            <div class="mt-2">
                                @php $packPrice = $pack->getPriceForCurrency($selectedCurrency); @endphp
                                <span class="text-2xl font-bold text-primary">{{ $currencySymbol }}{{ number_format($packPrice ?? 0, 2) }}</span>
                            </div>
                            <form action="{{ route('booking.select-class-pack', ['subdomain' => $host->subdomain, 'pack' => $pack->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm mt-3">
                                    Purchase
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Empty State --}}
            @if($upcomingSessions->count() === 0 && $servicePlans->count() === 0 && $membershipPlans->count() === 0 && $classPacks->count() === 0)
            <div class="card bg-base-100">
                <div class="card-body text-center py-12">
                    <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto"></span>
                    <h3 class="text-lg font-semibold mt-4">No Options Available</h3>
                    <p class="text-base-content/60 mt-2">
                        There are currently no classes, services, or memberships available to book.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
