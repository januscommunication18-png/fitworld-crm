@extends('layouts.subdomain')

@section('title', $plan->name . ' — ' . $host->studio_name)

@php
    $selectedCurrency = session("currency_{$host->id}", $host->default_currency ?? 'USD');
    $currencySymbol = \App\Models\MembershipPlan::getCurrencySymbol($selectedCurrency);
    $planPrice = $plan->getPriceForCurrency($selectedCurrency);
    $hasPriceInCurrency = $planPrice !== null;
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
                <a href="{{ route('booking.select-membership', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                    Back to Plans
                </a>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <div class="flex-1 py-8">
        <div class="container-fixed max-w-2xl">
            @php
                $cardColor = $plan->color ?? '#10b981';
            @endphp

            {{-- Plan Card --}}
            <div class="card bg-base-100 shadow-xl overflow-hidden">
                {{-- Colored Top Bar --}}
                <div class="h-3" style="background-color: {{ $cardColor }};"></div>

                <div class="card-body p-8">
                    {{-- Plan Header --}}
                    <div class="text-center mb-6">
                        <h1 class="text-3xl font-bold">{{ $plan->name }}</h1>
                        <span class="badge badge-lg mt-3" style="background-color: {{ $cardColor }}20; color: {{ $cardColor }};">
                            @if($plan->type === 'unlimited')
                                <span class="icon-[tabler--infinity] size-4 mr-1"></span> Unlimited Access
                            @else
                                <span class="icon-[tabler--ticket] size-4 mr-1"></span> {{ $plan->credits_per_cycle }} Classes per {{ ucfirst($plan->interval) }}
                            @endif
                        </span>
                    </div>

                    @if($plan->description)
                    <p class="text-base-content/70 text-center mb-6">{{ $plan->description }}</p>
                    @endif

                    {{-- Price --}}
                    <div class="text-center py-6 border-y border-base-200 mb-6">
                        @if($hasPriceInCurrency)
                            <div class="flex items-baseline justify-center gap-2">
                                <span class="text-5xl font-bold" style="color: {{ $cardColor }};">
                                    {{ $currencySymbol }}{{ number_format($planPrice, 2) }}
                                </span>
                                <span class="text-xl text-base-content/60">/ {{ $plan->interval }}</span>
                            </div>
                            @if($plan->type === 'credits' && $plan->credits_per_cycle > 0)
                            <p class="text-base-content/50 mt-2">
                                {{ $currencySymbol }}{{ number_format($planPrice / $plan->credits_per_cycle, 2) }} per class
                            </p>
                            @endif
                        @else
                            <div class="text-base-content/50">
                                <span class="icon-[tabler--currency-off] size-8 block mx-auto mb-2"></span>
                                <p>This plan is not available in {{ $selectedCurrency }}</p>
                                <a href="{{ route('booking.select-membership', ['subdomain' => $host->subdomain]) }}" class="link link-primary text-sm">View other plans</a>
                            </div>
                        @endif
                    </div>

                    {{-- Features --}}
                    <div class="space-y-4 mb-8">
                        <h3 class="font-semibold text-lg">What's Included</h3>
                        <ul class="space-y-3">
                            @if($plan->type === 'unlimited')
                            <li class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-success/10 flex items-center justify-center shrink-0">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                </span>
                                <span>Unlimited class access</span>
                            </li>
                            @else
                            <li class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-success/10 flex items-center justify-center shrink-0">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                </span>
                                <span>{{ $plan->credits_per_cycle }} classes per {{ $plan->interval }}</span>
                            </li>
                            @endif

                            <li class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-success/10 flex items-center justify-center shrink-0">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                </span>
                                <span>Auto-renews {{ $plan->interval }}ly</span>
                            </li>

                            <li class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-success/10 flex items-center justify-center shrink-0">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                </span>
                                <span>Cancel anytime</span>
                            </li>

                            @if($plan->eligibility_scope === 'all_classes')
                            <li class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-success/10 flex items-center justify-center shrink-0">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                </span>
                                <span>Access to all class types</span>
                            </li>
                            @else
                            <li class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-info/10 flex items-center justify-center shrink-0">
                                    <span class="icon-[tabler--info-circle] size-5 text-info"></span>
                                </span>
                                <span>Access to selected class types</span>
                            </li>
                            @endif

                            @if($plan->addon_members > 0)
                            <li class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                    <span class="icon-[tabler--users-plus] size-5 text-primary"></span>
                                </span>
                                <span>Bring up to {{ $plan->addon_members }} {{ Str::plural('guest', $plan->addon_members) }} with you</span>
                            </li>
                            @endif
                        </ul>
                    </div>

                    {{-- Free Amenities --}}
                    @if($plan->free_amenities && count($plan->free_amenities) > 0)
                    <div class="space-y-4 mb-8">
                        <h3 class="font-semibold text-lg">Free Amenities Included</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($plan->free_amenities as $amenity)
                                <span class="badge badge-lg badge-soft badge-success gap-1">
                                    <span class="icon-[tabler--check] size-4"></span>
                                    {{ $amenity }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- CTA --}}
                    @if($hasPriceInCurrency)
                        <form action="{{ route('booking.select-membership-plan', ['subdomain' => $host->subdomain, 'plan' => $plan->id]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="currency" value="{{ $selectedCurrency }}">
                            <button type="submit" class="btn btn-lg w-full text-white" style="background-color: {{ $cardColor }}; border-color: {{ $cardColor }};">
                                <span class="icon-[tabler--shopping-cart] size-5"></span>
                                Get Started
                            </button>
                        </form>

                        {{-- Help --}}
                        <p class="text-center text-sm text-base-content/50 mt-4">
                            <span class="icon-[tabler--shield-check] size-4 inline-block mr-1"></span>
                            Secure checkout powered by Stripe
                        </p>
                    @else
                        <button type="button" class="btn btn-lg btn-disabled w-full" disabled>
                            <span class="icon-[tabler--currency-off] size-5"></span>
                            Unavailable in {{ $selectedCurrency }}
                        </button>
                    @endif
                </div>
            </div>

            {{-- Other Plans Link --}}
            <div class="text-center mt-6">
                <a href="{{ route('booking.select-membership', ['subdomain' => $host->subdomain]) }}" class="link link-primary">
                    <span class="icon-[tabler--arrow-left] size-4 inline-block mr-1"></span>
                    View all membership plans
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
