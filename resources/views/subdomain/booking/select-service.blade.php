@extends('layouts.subdomain')

@section('title', 'Select a Service — ' . $host->studio_name)

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
                {{-- Services Sidebar --}}
                <div class="lg:w-64 shrink-0">
                    <div class="card bg-base-100 sticky top-6">
                        <div class="card-body">
                            <h3 class="font-semibold mb-4">Select a Service</h3>
                            <ul class="space-y-2">
                                @foreach($servicePlans as $plan)
                                <li>
                                    <a href="{{ route('booking.select-service.filter', ['subdomain' => $host->subdomain, 'servicePlanId' => $plan->id]) }}"
                                       class="flex flex-col p-3 rounded-lg {{ $selectedPlanId == $plan->id ? 'bg-primary/10 border border-primary' : 'hover:bg-base-200 border border-transparent' }}">
                                        <span class="font-medium {{ $selectedPlanId == $plan->id ? 'text-primary' : '' }}">{{ $plan->name }}</span>
                                        <span class="text-sm text-base-content/60">
                                            {{ $plan->duration_minutes ?? 60 }} min •
                                            ${{ number_format($plan->price ?? 0, 2) }}
                                        </span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Slots List --}}
                <div class="flex-1">
                    @if($selectedPlan)
                        <div class="mb-6">
                            <h1 class="text-2xl font-bold">{{ $selectedPlan->name }}</h1>
                            @if($selectedPlan->description)
                            <p class="text-base-content/60 mt-2">{{ $selectedPlan->description }}</p>
                            @endif
                            <div class="flex items-center gap-4 mt-3">
                                <span class="badge badge-outline">
                                    <span class="icon-[tabler--clock] size-4 mr-1"></span>
                                    {{ $selectedPlan->duration_minutes ?? 60 }} minutes
                                </span>
                                <span class="text-lg font-bold text-primary">
                                    ${{ number_format($selectedPlan->price ?? 0, 2) }}
                                </span>
                            </div>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-error mb-6">
                                <span class="icon-[tabler--alert-circle] size-5"></span>
                                <span>{{ session('error') }}</span>
                            </div>
                        @endif

                        @if($slots->count() > 0)
                            <h2 class="text-lg font-semibold mb-4">Available Times</h2>

                            @foreach($slotsByDate as $date => $dateSlots)
                                <div class="mb-6">
                                    <h3 class="font-medium mb-3 flex items-center gap-2 text-base-content/80">
                                        <span class="icon-[tabler--calendar] size-5 text-primary"></span>
                                        {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                                    </h3>

                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                        @foreach($dateSlots as $slot)
                                        <form action="{{ route('booking.select-service-slot', ['subdomain' => $host->subdomain, 'slot' => $slot->id]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline w-full justify-start gap-2 hover:btn-primary">
                                                <span class="icon-[tabler--clock] size-4"></span>
                                                {{ $slot->start_time->format('g:i A') }}
                                                @if($slot->instructor)
                                                <span class="text-xs text-base-content/60 truncate">
                                                    {{ $slot->instructor->name }}
                                                </span>
                                                @endif
                                            </button>
                                        </form>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="card bg-base-100">
                                <div class="card-body text-center py-12">
                                    <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto"></span>
                                    <h3 class="text-lg font-semibold mt-4">No Available Times</h3>
                                    <p class="text-base-content/60 mt-2">
                                        There are no available time slots for this service at the moment.
                                    </p>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="card bg-base-100">
                            <div class="card-body text-center py-12">
                                <span class="icon-[tabler--massage] size-16 text-base-content/20 mx-auto"></span>
                                <h3 class="text-lg font-semibold mt-4">Select a Service</h3>
                                <p class="text-base-content/60 mt-2">
                                    Choose a service from the list to see available times.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
