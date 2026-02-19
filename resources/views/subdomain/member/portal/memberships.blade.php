@extends('layouts.subdomain')

@section('title', 'Memberships — ' . $host->studio_name)

@section('content')
<div class="min-h-screen flex flex-col">
    @include('subdomain.member.portal._nav')

    <div class="flex-1 bg-base-200">
        <div class="container-fixed py-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Booking</h1>
            </div>

            {{-- Tabs --}}
            <div class="tabs tabs-boxed bg-base-100 w-fit mb-6">
                <a href="{{ route('member.portal.booking', ['subdomain' => $host->subdomain]) }}"
                   class="tab">
                    All
                </a>
                <a href="{{ route('member.portal.schedule', ['subdomain' => $host->subdomain]) }}"
                   class="tab">
                    Classes
                </a>
                <a href="{{ route('member.portal.services', ['subdomain' => $host->subdomain]) }}"
                   class="tab">
                    Services
                </a>
                <a href="{{ route('member.portal.memberships', ['subdomain' => $host->subdomain]) }}"
                   class="tab tab-active">
                    Memberships
                </a>
            </div>

            {{-- Your Active Plans --}}
            @if($activeMemberships->count() > 0 || $activeClassPacks->count() > 0)
            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <span class="icon-[tabler--check] size-5 text-success"></span>
                    Your Active Plans
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($activeMemberships as $membership)
                    <div class="card bg-base-100 ring-2 ring-success">
                        <div class="card-body py-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="badge badge-success badge-sm">Active</span>
                                    <h3 class="font-semibold mt-1">{{ $membership->membershipPlan?->name }}</h3>
                                </div>
                            </div>
                            <div class="text-sm text-base-content/60 mt-2">
                                @if($membership->classes_remaining !== null)
                                    {{ $membership->classes_remaining }} classes remaining
                                @endif
                                @if($membership->end_date)
                                    <br>Renews {{ $membership->end_date->format('M j, Y') }}
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach

                    @foreach($activeClassPacks as $pack)
                    <div class="card bg-base-100 ring-2 ring-success">
                        <div class="card-body py-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="badge badge-success badge-sm">Active</span>
                                    <h3 class="font-semibold mt-1">{{ $pack->classPack?->name }}</h3>
                                </div>
                            </div>
                            <div class="text-sm text-base-content/60 mt-2">
                                {{ $pack->classes_remaining }} of {{ $pack->classPack?->class_count }} classes remaining
                                @if($pack->expires_at)
                                    <br>Expires {{ $pack->expires_at->format('M j, Y') }}
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Available Membership Plans --}}
            @if($membershipPlans->count() > 0)
            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <span class="icon-[tabler--id-badge-2] size-5 text-primary"></span>
                    Membership Plans
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($membershipPlans as $plan)
                    <div class="card bg-base-100 border-2 border-primary/20 hover:border-primary/40 transition-colors">
                        <div class="card-body">
                            <h3 class="card-title text-lg">{{ $plan->name }}</h3>
                            @if($plan->description)
                                <p class="text-sm text-base-content/60 line-clamp-2">{{ $plan->description }}</p>
                            @endif

                            <div class="mt-3">
                                <span class="text-3xl font-bold text-primary">${{ number_format($plan->price, 2) }}</span>
                                <span class="text-base-content/60">{{ $plan->formatted_interval }}</span>
                            </div>

                            @if($plan->credits_per_cycle)
                            <div class="text-sm text-base-content/60 mt-2">
                                <span class="icon-[tabler--check] size-4 text-success inline-block"></span>
                                {{ $plan->credits_per_cycle }} classes {{ $plan->formatted_interval }}
                            </div>
                            @elseif($plan->type === 'unlimited')
                            <div class="text-sm text-base-content/60 mt-2">
                                <span class="icon-[tabler--check] size-4 text-success inline-block"></span>
                                Unlimited classes
                            </div>
                            @endif

                            <form action="{{ route('booking.select-membership-plan', ['subdomain' => $host->subdomain, 'plan' => $plan->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary mt-4">
                                    Get Started
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Available Class Packs --}}
            @if($classPackPlans->count() > 0)
            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <span class="icon-[tabler--ticket] size-5 text-secondary"></span>
                    Class Packs
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($classPackPlans as $pack)
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h3 class="card-title text-lg">{{ $pack->name }}</h3>
                            @if($pack->description)
                                <p class="text-sm text-base-content/60 line-clamp-2">{{ $pack->description }}</p>
                            @endif

                            <div class="mt-3">
                                <span class="text-3xl font-bold text-primary">${{ number_format($pack->price, 2) }}</span>
                            </div>

                            <div class="text-sm text-base-content/60 mt-2 space-y-1">
                                <div>
                                    <span class="icon-[tabler--check] size-4 text-success inline-block"></span>
                                    {{ $pack->class_count }} classes
                                </div>
                                @if($pack->validity_days)
                                <div>
                                    <span class="icon-[tabler--clock] size-4 text-warning inline-block"></span>
                                    Valid for {{ $pack->validity_days }} days
                                </div>
                                @else
                                <div>
                                    <span class="icon-[tabler--infinity] size-4 text-success inline-block"></span>
                                    Never expires
                                </div>
                                @endif
                            </div>

                            <form action="{{ route('booking.select-class-pack', ['subdomain' => $host->subdomain, 'pack' => $pack->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary mt-4">
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
            @if($membershipPlans->count() === 0 && $classPackPlans->count() === 0)
                <div class="card bg-base-100">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--id-badge-off] size-16 text-base-content/20 mx-auto"></span>
                        <h3 class="text-lg font-semibold mt-4">No Memberships Available</h3>
                        <p class="text-base-content/60 mt-2">
                            There are no membership plans or class packs available at this time.
                        </p>
                        <a href="{{ route('member.portal.schedule', ['subdomain' => $host->subdomain]) }}" class="btn btn-primary mt-4">
                            Browse Classes
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
