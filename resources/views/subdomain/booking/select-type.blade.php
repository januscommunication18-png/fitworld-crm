@extends('layouts.subdomain')

@section('title', 'Book Now — ' . $host->studio_name)

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
                <h1 class="text-2xl font-bold">What would you like to book?</h1>
                <p class="text-base-content/60 mt-2">Choose from classes, services, or memberships</p>
            </div>

            @if(session('error'))
                <div class="alert alert-error mb-6 max-w-2xl mx-auto">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Booking Type Tabs --}}
            <div class="max-w-4xl mx-auto">
                <div role="tablist" class="tabs tabs-boxed bg-base-100 p-1 mb-6">
                    @if($hasClasses)
                    <button type="button" role="tab" class="tab tab-active" data-tab="classes">
                        <span class="icon-[tabler--yoga] size-5 mr-2"></span>
                        Classes
                    </button>
                    @endif
                    @if($hasServices)
                    <button type="button" role="tab" class="tab" data-tab="services">
                        <span class="icon-[tabler--massage] size-5 mr-2"></span>
                        Services
                    </button>
                    @endif
                    @if($hasMemberships)
                    <button type="button" role="tab" class="tab" data-tab="memberships">
                        <span class="icon-[tabler--id-badge-2] size-5 mr-2"></span>
                        Memberships
                    </button>
                    @endif
                </div>

                {{-- Classes Tab Content --}}
                @if($hasClasses)
                <div id="tab-classes" class="tab-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($classPlans as $plan)
                        <a href="{{ route('booking.select-class.filter', ['subdomain' => $host->subdomain, 'classPlanId' => $plan->id]) }}"
                           class="card bg-base-100 hover:shadow-lg transition-shadow cursor-pointer">
                            <div class="card-body">
                                @if($plan->image_url)
                                <figure class="rounded-lg overflow-hidden mb-4 -mt-2 -mx-2">
                                    <img src="{{ $plan->image_url }}" alt="{{ $plan->name }}" class="w-full h-32 object-cover">
                                </figure>
                                @endif
                                <h3 class="card-title text-lg">{{ $plan->name }}</h3>
                                @if($plan->description)
                                <p class="text-sm text-base-content/60 line-clamp-2">{{ $plan->description }}</p>
                                @endif
                                <div class="flex items-center justify-between mt-4">
                                    <div class="flex items-center gap-2 text-sm text-base-content/60">
                                        <span class="icon-[tabler--clock] size-4"></span>
                                        {{ $plan->duration_minutes ?? 60 }} min
                                    </div>
                                    @if($plan->drop_in_price)
                                    <span class="text-lg font-bold text-primary">${{ number_format($plan->drop_in_price, 2) }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>

                    <div class="text-center mt-6">
                        <a href="{{ route('booking.select-class', ['subdomain' => $host->subdomain]) }}"
                           class="btn btn-primary">
                            <span class="icon-[tabler--calendar] size-5"></span>
                            View All Class Sessions
                        </a>
                    </div>
                </div>
                @endif

                {{-- Services Tab Content --}}
                @if($hasServices)
                <div id="tab-services" class="tab-content hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($servicePlans as $plan)
                        <a href="{{ route('booking.select-service.filter', ['subdomain' => $host->subdomain, 'servicePlanId' => $plan->id]) }}"
                           class="card bg-base-100 hover:shadow-lg transition-shadow cursor-pointer">
                            <div class="card-body">
                                @if($plan->image_url)
                                <figure class="rounded-lg overflow-hidden mb-4 -mt-2 -mx-2">
                                    <img src="{{ $plan->image_url }}" alt="{{ $plan->name }}" class="w-full h-32 object-cover">
                                </figure>
                                @endif
                                <h3 class="card-title text-lg">{{ $plan->name }}</h3>
                                @if($plan->description)
                                <p class="text-sm text-base-content/60 line-clamp-2">{{ $plan->description }}</p>
                                @endif
                                <div class="flex items-center justify-between mt-4">
                                    <div class="flex items-center gap-2 text-sm text-base-content/60">
                                        <span class="icon-[tabler--clock] size-4"></span>
                                        {{ $plan->duration_minutes ?? 60 }} min
                                    </div>
                                    @if($plan->price)
                                    <span class="text-lg font-bold text-primary">${{ number_format($plan->price, 2) }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Memberships Tab Content --}}
                @if($hasMemberships)
                <div id="tab-memberships" class="tab-content hidden">
                    @if($membershipPlans->count() > 0)
                    <h3 class="text-lg font-semibold mb-4">Membership Plans</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                        @foreach($membershipPlans as $plan)
                        <div class="card bg-base-100">
                            <div class="card-body">
                                <h3 class="card-title">{{ $plan->name }}</h3>
                                @if($plan->description)
                                <p class="text-sm text-base-content/60">{{ $plan->description }}</p>
                                @endif
                                <div class="mt-4">
                                    <span class="text-2xl font-bold text-primary">${{ number_format($plan->price, 2) }}</span>
                                    <span class="text-base-content/60">/{{ $plan->billing_period ?? 'month' }}</span>
                                </div>
                                @if($plan->features)
                                <ul class="mt-4 space-y-2">
                                    @foreach($plan->features as $feature)
                                    <li class="flex items-center gap-2 text-sm">
                                        <span class="icon-[tabler--check] size-4 text-success"></span>
                                        {{ $feature }}
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                                <div class="card-actions mt-4">
                                    <form action="{{ route('booking.select-membership-plan', ['subdomain' => $host->subdomain, 'plan' => $plan->id]) }}" method="POST" class="w-full">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-full">
                                            Select Plan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($classPacks->count() > 0)
                    <h3 class="text-lg font-semibold mb-4">Class Packs</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($classPacks as $pack)
                        <div class="card bg-base-100">
                            <div class="card-body">
                                <h3 class="card-title">{{ $pack->name }}</h3>
                                <p class="text-sm text-base-content/60">
                                    {{ $pack->class_count }} classes
                                    @if($pack->validity_days)
                                    • Valid for {{ $pack->validity_days }} days
                                    @endif
                                </p>
                                <div class="mt-4">
                                    <span class="text-2xl font-bold text-primary">${{ number_format($pack->price, 2) }}</span>
                                    <span class="text-sm text-base-content/60 ml-2">
                                        (${{ number_format($pack->price / $pack->class_count, 2) }}/class)
                                    </span>
                                </div>
                                <div class="card-actions mt-4">
                                    <form action="{{ route('booking.select-class-pack', ['subdomain' => $host->subdomain, 'pack' => $pack->id]) }}" method="POST" class="w-full">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-full">
                                            Buy Pack
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('[role="tab"]');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = 'tab-' + this.dataset.tab;

            // Update active tab
            tabs.forEach(t => t.classList.remove('tab-active'));
            this.classList.add('tab-active');

            // Show target content
            tabContents.forEach(content => {
                if (content.id === targetId) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            });
        });
    });
});
</script>
@endpush
@endsection
