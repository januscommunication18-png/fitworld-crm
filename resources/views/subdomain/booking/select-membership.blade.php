@extends('layouts.subdomain')

@section('title', 'Memberships & Packs — ' . $host->studio_name)

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
                <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                    Back
                </a>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <div class="flex-1 py-8">
        <div class="container-fixed">
            {{-- Header --}}
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary/10 mb-4">
                    <span class="icon-[tabler--id-badge-2] size-8 text-primary"></span>
                </div>
                <h1 class="text-3xl font-bold">Memberships & Class Packs</h1>
                <p class="text-base-content/60 mt-2 max-w-md mx-auto">
                    Unlock unlimited access or purchase class packs for flexibility
                </p>
            </div>

            {{-- Membership Plans --}}
            @if($membershipPlans->count() > 0)
            <div class="mb-12">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--id-badge-2] size-5 text-success"></span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">Membership Plans</h2>
                        <p class="text-sm text-base-content/60">Recurring access to classes</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($membershipPlans as $index => $plan)
                    @php
                        $isPopular = $index === 0 && $membershipPlans->count() > 1;
                        $cardColor = $plan->color ?? '#10b981';
                    @endphp
                    <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden {{ $isPopular ? 'ring-2 ring-success scale-[1.02]' : '' }}">
                        {{-- Colored Top Bar --}}
                        <div class="h-2" style="background-color: {{ $cardColor }};"></div>

                        @if($isPopular)
                        <div class="absolute top-4 right-4">
                            <span class="badge badge-success gap-1">
                                <span class="icon-[tabler--star-filled] size-3"></span>
                                Popular
                            </span>
                        </div>
                        @endif

                        <div class="card-body pt-6">
                            {{-- Plan Name & Type Badge --}}
                            <div class="mb-2">
                                <h3 class="card-title text-xl">{{ $plan->name }}</h3>
                                <span class="badge badge-sm mt-1" style="background-color: {{ $cardColor }}20; color: {{ $cardColor }};">
                                    @if($plan->type === 'unlimited')
                                        <span class="icon-[tabler--infinity] size-3 mr-1"></span> Unlimited
                                    @else
                                        <span class="icon-[tabler--ticket] size-3 mr-1"></span> {{ $plan->credits_per_cycle }} Classes
                                    @endif
                                </span>
                            </div>

                            @if($plan->description)
                            <p class="text-base-content/60 text-sm mb-4">{{ $plan->description }}</p>
                            @endif

                            {{-- Price --}}
                            <div class="py-4 border-y border-base-200">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl font-bold" style="color: {{ $cardColor }};">${{ number_format($plan->price, 0) }}</span>
                                    <span class="text-base-content/60">/ {{ $plan->interval }}</span>
                                </div>
                                @if($plan->type === 'credits' && $plan->credits_per_cycle > 0)
                                <p class="text-sm text-base-content/50 mt-1">
                                    ${{ number_format($plan->price / $plan->credits_per_cycle, 2) }} per class
                                </p>
                                @endif
                            </div>

                            {{-- Features List --}}
                            <ul class="space-y-3 my-4">
                                @if($plan->type === 'unlimited')
                                <li class="flex items-center gap-2 text-sm">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                    <span>Unlimited class access</span>
                                </li>
                                @else
                                <li class="flex items-center gap-2 text-sm">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                    <span>{{ $plan->credits_per_cycle }} classes per {{ $plan->interval }}</span>
                                </li>
                                @endif
                                <li class="flex items-center gap-2 text-sm">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                    <span>Auto-renews {{ $plan->interval }}ly</span>
                                </li>
                                <li class="flex items-center gap-2 text-sm">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                    <span>Cancel anytime</span>
                                </li>
                                @if($plan->eligibility_scope === 'all_classes')
                                <li class="flex items-center gap-2 text-sm">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                    <span>Access to all class types</span>
                                </li>
                                @else
                                <li class="flex items-center gap-2 text-sm">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                    <span>Access to selected classes</span>
                                </li>
                                @endif
                            </ul>

                            {{-- CTA Button --}}
                            <div class="card-actions mt-auto pt-2">
                                <form action="{{ route('booking.select-membership-plan', ['subdomain' => $host->subdomain, 'plan' => $plan->id]) }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="btn w-full {{ $isPopular ? 'btn-success' : 'btn-outline' }}" style="{{ !$isPopular ? 'border-color: ' . $cardColor . '; color: ' . $cardColor . ';' : '' }}">
                                        <span class="icon-[tabler--shopping-cart] size-5"></span>
                                        Get Started
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Class Packs --}}
            @if($classPacks->count() > 0)
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-secondary/10 flex items-center justify-center">
                        <span class="icon-[tabler--ticket] size-5 text-secondary"></span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">Class Packs</h2>
                        <p class="text-sm text-base-content/60">One-time purchase, use at your pace</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($classPacks as $pack)
                    @php
                        $packColor = $pack->color ?? '#8b5cf6';
                    @endphp
                    <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
                        {{-- Colored Top Bar --}}
                        <div class="h-2" style="background-color: {{ $packColor }};"></div>

                        <div class="card-body">
                            {{-- Pack Name & Class Count --}}
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="card-title text-xl">{{ $pack->name }}</h3>
                                    <span class="badge badge-sm mt-1" style="background-color: {{ $packColor }}20; color: {{ $packColor }};">
                                        <span class="icon-[tabler--ticket] size-3 mr-1"></span>
                                        {{ $pack->class_count ?? $pack->credits ?? 0 }} Classes
                                    </span>
                                </div>
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: {{ $packColor }}15;">
                                    <span class="text-xl font-bold" style="color: {{ $packColor }};">{{ $pack->class_count ?? $pack->credits ?? 0 }}</span>
                                </div>
                            </div>

                            @if($pack->description)
                            <p class="text-base-content/60 text-sm mb-4">{{ $pack->description }}</p>
                            @endif

                            {{-- Price --}}
                            <div class="py-4 border-y border-base-200">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl font-bold" style="color: {{ $packColor }};">${{ number_format($pack->price, 0) }}</span>
                                    <span class="text-base-content/60">one-time</span>
                                </div>
                                @php
                                    $classCount = $pack->class_count ?? $pack->credits ?? 1;
                                @endphp
                                <p class="text-sm text-base-content/50 mt-1">
                                    ${{ number_format($pack->price / max($classCount, 1), 2) }} per class
                                </p>
                            </div>

                            {{-- Details --}}
                            <ul class="space-y-3 my-4">
                                <li class="flex items-center gap-2 text-sm">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                    <span>{{ $classCount }} class credits</span>
                                </li>
                                @if($pack->validity_days)
                                <li class="flex items-center gap-2 text-sm">
                                    <span class="icon-[tabler--calendar] size-5 text-base-content/50"></span>
                                    <span>Valid for {{ $pack->validity_days }} days</span>
                                </li>
                                @else
                                <li class="flex items-center gap-2 text-sm">
                                    <span class="icon-[tabler--infinity] size-5 text-base-content/50"></span>
                                    <span>Never expires</span>
                                </li>
                                @endif
                                <li class="flex items-center gap-2 text-sm">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                    <span>Use at your own pace</span>
                                </li>
                            </ul>

                            {{-- CTA Button --}}
                            <div class="card-actions mt-auto pt-2">
                                <form action="{{ route('booking.select-class-pack', ['subdomain' => $host->subdomain, 'pack' => $pack->id]) }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="btn btn-outline w-full" style="border-color: {{ $packColor }}; color: {{ $packColor }};">
                                        <span class="icon-[tabler--shopping-cart] size-5"></span>
                                        Buy Pack
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Empty State --}}
            @if($membershipPlans->count() === 0 && $classPacks->count() === 0)
            <div class="card bg-base-100 max-w-md mx-auto shadow-lg">
                <div class="card-body text-center py-16">
                    <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mx-auto mb-4">
                        <span class="icon-[tabler--id-badge-off] size-10 text-base-content/30"></span>
                    </div>
                    <h3 class="text-xl font-semibold">No Plans Available</h3>
                    <p class="text-base-content/60 mt-2">
                        There are no membership plans or class packs available at this time.
                    </p>
                    <div class="card-actions justify-center mt-6">
                        <a href="{{ route('booking.select-class', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary">
                            <span class="icon-[tabler--calendar-plus] size-5"></span>
                            Book a Class Instead
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Help Text --}}
            @if($membershipPlans->count() > 0 || $classPacks->count() > 0)
            <div class="mt-12 text-center">
                <p class="text-sm text-base-content/50">
                    <span class="icon-[tabler--info-circle] size-4 inline-block mr-1"></span>
                    Need help choosing?
                    @if($host->studio_email)
                    <a href="mailto:{{ $host->studio_email }}" class="link link-primary">Contact us</a>
                    @else
                    Contact us for guidance.
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
