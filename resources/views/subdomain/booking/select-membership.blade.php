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
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold">Memberships & Class Packs</h1>
                <p class="text-base-content/60 mt-2">Choose the option that works best for you</p>
            </div>

            @if($membershipPlans->count() > 0)
            <div class="mb-12">
                <h2 class="text-xl font-semibold mb-6 flex items-center gap-2">
                    <span class="icon-[tabler--id-badge-2] size-6 text-primary"></span>
                    Membership Plans
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($membershipPlans as $plan)
                    <div class="card bg-base-100 {{ $plan->is_featured ? 'ring-2 ring-primary' : '' }}">
                        @if($plan->is_featured)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="badge badge-primary">Most Popular</span>
                        </div>
                        @endif
                        <div class="card-body">
                            <h3 class="card-title text-xl">{{ $plan->name }}</h3>
                            @if($plan->description)
                            <p class="text-base-content/60 text-sm">{{ $plan->description }}</p>
                            @endif

                            <div class="py-4">
                                <span class="text-4xl font-bold">${{ number_format($plan->price, 2) }}</span>
                                <span class="text-base-content/60">/{{ $plan->billing_period ?? 'month' }}</span>
                            </div>

                            @if($plan->features && is_array($plan->features))
                            <ul class="space-y-3 mb-6">
                                @foreach($plan->features as $feature)
                                <li class="flex items-start gap-2">
                                    <span class="icon-[tabler--check] size-5 text-success shrink-0 mt-0.5"></span>
                                    <span class="text-sm">{{ $feature }}</span>
                                </li>
                                @endforeach
                            </ul>
                            @endif

                            <div class="card-actions mt-auto">
                                <form action="{{ route('booking.select-membership-plan', ['subdomain' => $host->subdomain, 'plan' => $plan->id]) }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="btn {{ $plan->is_featured ? 'btn-primary' : 'btn-outline btn-primary' }} w-full">
                                        Select Plan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($classPacks->count() > 0)
            <div>
                <h2 class="text-xl font-semibold mb-6 flex items-center gap-2">
                    <span class="icon-[tabler--ticket] size-6 text-secondary"></span>
                    Class Packs
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($classPacks as $pack)
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h3 class="card-title text-xl">{{ $pack->name }}</h3>

                            <div class="flex items-center gap-2 text-base-content/60">
                                <span class="icon-[tabler--ticket] size-5"></span>
                                <span>{{ $pack->class_count }} classes</span>
                            </div>

                            @if($pack->validity_days)
                            <div class="flex items-center gap-2 text-base-content/60">
                                <span class="icon-[tabler--calendar] size-5"></span>
                                <span>Valid for {{ $pack->validity_days }} days</span>
                            </div>
                            @endif

                            <div class="py-4">
                                <span class="text-4xl font-bold">${{ number_format($pack->price, 2) }}</span>
                                <div class="text-sm text-base-content/60 mt-1">
                                    ${{ number_format($pack->price / $pack->class_count, 2) }} per class
                                </div>
                            </div>

                            @if($pack->description)
                            <p class="text-sm text-base-content/60">{{ $pack->description }}</p>
                            @endif

                            @if($pack->applicable_classes && is_array($pack->applicable_classes) && count($pack->applicable_classes) > 0)
                            <div class="text-sm">
                                <span class="font-medium">Valid for:</span>
                                <span class="text-base-content/60">{{ implode(', ', $pack->applicable_classes) }}</span>
                            </div>
                            @else
                            <div class="text-sm text-base-content/60">
                                Valid for all classes
                            </div>
                            @endif

                            <div class="card-actions mt-4">
                                <form action="{{ route('booking.select-class-pack', ['subdomain' => $host->subdomain, 'pack' => $pack->id]) }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-secondary w-full">
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

            @if($membershipPlans->count() === 0 && $classPacks->count() === 0)
            <div class="card bg-base-100 max-w-md mx-auto">
                <div class="card-body text-center py-12">
                    <span class="icon-[tabler--id-badge-off] size-16 text-base-content/20 mx-auto"></span>
                    <h3 class="text-lg font-semibold mt-4">No Memberships Available</h3>
                    <p class="text-base-content/60 mt-2">
                        There are no membership plans or class packs available at this time.
                    </p>
                    <a href="{{ route('booking.select-type', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary mt-4">
                        Book a Class Instead
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
