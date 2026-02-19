@extends('layouts.subdomain')

@section('title', 'Services — ' . $host->studio_name)

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
                   class="tab tab-active">
                    Services
                </a>
                <a href="{{ route('member.portal.memberships', ['subdomain' => $host->subdomain]) }}"
                   class="tab">
                    Memberships
                </a>
            </div>

            @if($servicePlans->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($servicePlans as $service)
                    <div class="card bg-base-100 shadow-md hover:shadow-xl transition-shadow border border-base-200">
                        <div class="card-body">
                            {{-- Icon & Price --}}
                            <div class="flex items-start justify-between">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background-color: {{ $service->color ?? '#6366f1' }}15;">
                                    <span class="icon-[tabler--sparkles] size-7" style="color: {{ $service->color ?? '#6366f1' }};"></span>
                                </div>
                                @if($service->price)
                                <div class="text-2xl font-bold" style="color: {{ $service->color ?? '#6366f1' }};">
                                    ${{ number_format($service->price, 0) }}
                                </div>
                                @endif
                            </div>

                            {{-- Name & Description --}}
                            <h3 class="card-title text-lg mt-4">{{ $service->name }}</h3>
                            @if($service->description)
                            <p class="text-sm text-base-content/60 line-clamp-3">{{ $service->description }}</p>
                            @endif

                            {{-- Meta --}}
                            <div class="flex items-center gap-4 text-sm text-base-content/50 mt-2">
                                @if($service->duration_minutes)
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--clock] size-4"></span>
                                    {{ $service->duration_minutes }} min
                                </span>
                                @endif
                                @if($service->session_type)
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--{{ $service->session_type === 'virtual' ? 'video' : 'building' }}] size-4"></span>
                                    {{ ucfirst($service->session_type) }}
                                </span>
                                @endif
                            </div>

                            {{-- Instructors --}}
                            @if($service->instructors && $service->instructors->isNotEmpty())
                            <div class="mt-3">
                                <p class="text-xs text-base-content/50 mb-2">Available with:</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($service->instructors->take(3) as $instructor)
                                    <span class="badge badge-sm badge-ghost">{{ $instructor->name }}</span>
                                    @endforeach
                                    @if($service->instructors->count() > 3)
                                    <span class="badge badge-sm badge-ghost">+{{ $service->instructors->count() - 3 }} more</span>
                                    @endif
                                </div>
                            </div>
                            @endif

                            {{-- Buttons --}}
                            <div class="card-actions mt-4 flex-col gap-2">
                                <a href="{{ route('booking.select-service.filter', ['subdomain' => $host->subdomain, 'servicePlanId' => $service->id]) }}"
                                   class="btn btn-primary w-full">
                                    <span class="icon-[tabler--calendar-plus] size-5"></span>
                                    Book Now
                                </a>
                                <a href="{{ route('subdomain.service-request.plan', ['subdomain' => $host->subdomain, 'servicePlanId' => $service->id]) }}"
                                   class="btn btn-ghost btn-sm w-full">
                                    <span class="icon-[tabler--info-circle] size-4"></span>
                                    Request Info
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="card bg-base-100">
                    <div class="card-body text-center py-16">
                        <span class="icon-[tabler--sparkles-off] size-16 text-base-content/20 mx-auto"></span>
                        <h3 class="text-lg font-semibold mt-4">No Services Available</h3>
                        <p class="text-base-content/60 mt-2">
                            There are no services available at this time. Check back later or browse our class schedule.
                        </p>
                        <a href="{{ route('member.portal.schedule', ['subdomain' => $host->subdomain]) }}"
                           class="btn btn-primary btn-sm mt-4">
                            View Schedule
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
