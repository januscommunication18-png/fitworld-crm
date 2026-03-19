@extends('layouts.subdomain')

@section('title', $event->title . ' — ' . $host->studio_name)

@section('content')
@php
    $spotsLeft = $event->capacity ? max(0, $event->capacity - $event->registered_attendees_count) : null;
    $canRegister = $spotsLeft !== 0 && $event->start_datetime > now();
@endphp

@include('subdomain.partials.navbar')

{{-- Main Content --}}
<div class="max-w-5xl mx-auto w-full px-4 py-8 space-y-6">

    {{-- Back Link --}}
    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}"
       class="inline-flex items-center gap-1 text-sm text-base-content/60 hover:text-primary transition-colors">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        {{ $trans['btn.back'] ?? 'Back' }}
    </a>

    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div class="alert alert-success">
        <span class="icon-[tabler--check] size-5"></span>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        {{ session('error') }}
    </div>
    @endif

    {{-- Event Details Card --}}
    <div class="card bg-base-100 border border-base-200 overflow-hidden">
        {{-- Event Image Header --}}
        @if($event->image_url)
        <figure class="relative h-48 md:h-64">
            <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            <div class="absolute bottom-4 left-6 right-6">
                <span class="badge badge-lg {{ $event->event_type === 'online' ? 'badge-info' : ($event->event_type === 'hybrid' ? 'badge-warning' : 'badge-success') }}">
                    @if($event->event_type === 'in_person')
                        <span class="icon-[tabler--map-pin] size-4 mr-1"></span> {{ $trans['events.in_person'] ?? 'In-Person' }}
                    @elseif($event->event_type === 'online')
                        <span class="icon-[tabler--device-laptop] size-4 mr-1"></span> {{ $trans['events.online'] ?? 'Online' }}
                    @else
                        <span class="icon-[tabler--arrows-exchange] size-4 mr-1"></span> {{ $trans['events.hybrid'] ?? 'Hybrid' }}
                    @endif
                </span>
            </div>
        </figure>
        @endif

        <div class="card-body p-6 md:p-8">
            <div class="flex flex-col md:flex-row gap-6">
                {{-- Date/Time Badge --}}
                <div class="shrink-0 flex flex-col items-center">
                    <div class="w-24 h-24 rounded-2xl bg-primary text-primary-content flex flex-col items-center justify-center">
                        <span class="text-sm font-medium uppercase">{{ $event->start_datetime->format('M') }}</span>
                        <span class="text-3xl font-bold leading-none">{{ $event->start_datetime->format('j') }}</span>
                        <span class="text-sm">{{ $event->start_datetime->format('D') }}</span>
                    </div>
                    <div class="text-center mt-2">
                        <div class="text-lg font-bold text-base-content">{{ $event->start_datetime->format('g:i A') }}</div>
                        <div class="text-sm text-base-content/60">{{ $event->end_datetime->format('g:i A') }}</div>
                    </div>
                </div>

                {{-- Details --}}
                <div class="flex-1">
                    <h1 class="text-2xl md:text-3xl font-bold text-base-content">
                        {{ $event->title }}
                    </h1>

                    @if(!$event->image_url)
                    <span class="badge mt-2 {{ $event->event_type === 'online' ? 'badge-info' : ($event->event_type === 'hybrid' ? 'badge-warning' : 'badge-success') }}">
                        @if($event->event_type === 'in_person')
                            <span class="icon-[tabler--map-pin] size-3 mr-1"></span> {{ $trans['events.in_person'] ?? 'In-Person' }}
                        @elseif($event->event_type === 'online')
                            <span class="icon-[tabler--device-laptop] size-3 mr-1"></span> {{ $trans['events.online'] ?? 'Online' }}
                        @else
                            <span class="icon-[tabler--arrows-exchange] size-3 mr-1"></span> {{ $trans['events.hybrid'] ?? 'Hybrid' }}
                        @endif
                    </span>
                    @endif

                    {{-- Meta Info --}}
                    <div class="flex flex-wrap gap-4 mt-4">
                        @if($event->venue_name && $event->event_type !== 'online')
                        <div class="flex items-center gap-2 text-base-content/60">
                            <span class="icon-[tabler--map-pin] size-5"></span>
                            <span>{{ $event->venue_name }}</span>
                        </div>
                        @endif

                        @if($event->venue_address && $event->event_type !== 'online')
                        <div class="flex items-center gap-2 text-base-content/60">
                            <span class="icon-[tabler--building] size-5"></span>
                            <span>{{ $event->venue_address }}</span>
                        </div>
                        @endif

                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--users] size-5 text-base-content/60"></span>
                            @if($spotsLeft === null)
                                <span class="badge badge-success">{{ $trans['subdomain.home.open'] ?? 'Open Registration' }}</span>
                            @elseif($spotsLeft > 0)
                                <span class="badge badge-success">{{ $spotsLeft }} {{ $trans['subdomain.home.spots'] ?? 'spots' }} {{ $trans['schedule.available'] ?? 'available' }}</span>
                            @else
                                <span class="badge badge-error">{{ $trans['subdomain.schedule.full'] ?? 'Event Full' }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Description --}}
                    @if($event->description)
                    <div class="mt-6 prose prose-sm max-w-none text-base-content/80">
                        {!! nl2br(e($event->description)) !!}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Registration Form --}}
    @if($canRegister)
    <div class="card bg-base-100 border border-base-200">
        <div class="card-body p-6">
            <h3 class="text-lg font-bold text-base-content flex items-center gap-2 mb-4">
                <span class="icon-[tabler--user-plus] size-5 text-primary"></span>
                {{ $trans['events.register'] ?? 'Register for this Event' }}
            </h3>

            <form action="{{ route('subdomain.event.register', ['subdomain' => $host->subdomain, 'event' => $event->id]) }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label for="first_name" class="label">
                            <span class="label-text">{{ $trans['field.first_name'] ?? 'First Name' }} *</span>
                        </label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}"
                               class="input input-bordered @error('first_name') input-error @enderror" required>
                        @error('first_name')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label for="last_name" class="label">
                            <span class="label-text">{{ $trans['field.last_name'] ?? 'Last Name' }} *</span>
                        </label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
                               class="input input-bordered @error('last_name') input-error @enderror" required>
                        @error('last_name')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label for="email" class="label">
                            <span class="label-text">{{ $trans['field.email'] ?? 'Email' }} *</span>
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               class="input input-bordered @error('email') input-error @enderror" required>
                        @error('email')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label for="phone" class="label">
                            <span class="label-text">{{ $trans['field.phone'] ?? 'Phone' }}</span>
                        </label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                               class="input input-bordered @error('phone') input-error @enderror">
                        @error('phone')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn btn-primary btn-lg w-full md:w-auto">
                        <span class="icon-[tabler--calendar-plus] size-5"></span>
                        {{ $trans['btn.register'] ?? 'Register Now' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @elseif($spotsLeft === 0)
    <div class="card bg-base-100 border border-base-200">
        <div class="card-body p-6 text-center">
            <span class="icon-[tabler--users-minus] size-12 text-base-content/30 mx-auto mb-3"></span>
            <h3 class="text-lg font-semibold">{{ $trans['events.event_full'] ?? 'This Event is Full' }}</h3>
            <p class="text-base-content/60">{{ $trans['events.check_back'] ?? 'Check back for future events or contact us for more information.' }}</p>
        </div>
    </div>
    @elseif($event->start_datetime <= now())
    <div class="card bg-base-100 border border-base-200">
        <div class="card-body p-6 text-center">
            <span class="icon-[tabler--calendar-off] size-12 text-base-content/30 mx-auto mb-3"></span>
            <h3 class="text-lg font-semibold">{{ $trans['events.registration_closed'] ?? 'Registration Closed' }}</h3>
            <p class="text-base-content/60">{{ $trans['events.event_started'] ?? 'This event has already started.' }}</p>
        </div>
    </div>
    @endif

    {{-- Additional Info --}}
    @if($event->notes)
    <div class="card bg-base-100 border border-base-200">
        <div class="card-body p-6">
            <h3 class="text-lg font-bold text-base-content flex items-center gap-2 mb-3">
                <span class="icon-[tabler--info-circle] size-5 text-primary"></span>
                {{ $trans['events.additional_info'] ?? 'Additional Information' }}
            </h3>
            <div class="prose prose-sm max-w-none text-base-content/70">
                {!! nl2br(e($event->notes)) !!}
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
