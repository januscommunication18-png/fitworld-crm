@extends('layouts.subdomain')

@section('title', $event->title . ' — ' . $host->studio_name)

@section('content')
@php
    // Mirror the controller's withCount('registeredAttendees') for spot math.
    $spotsLeft = $event->capacity ? max(0, $event->capacity - $event->registered_attendees_count) : null;
    $canRegister = $spotsLeft !== 0 && $event->start_datetime > now();
    $hasStarted = $event->start_datetime <= now();

    $eventImage = $event->image_url;

    $typeBadgeClass = $event->event_type === 'online' ? 'badge-info' : ($event->event_type === 'hybrid' ? 'badge-warning' : 'badge-success');
    $typeIcon = $event->event_type === 'online' ? 'device-laptop' : ($event->event_type === 'hybrid' ? 'arrows-exchange' : 'map-pin');

    $files = $event->file_attachments ?? [];
@endphp

@include('subdomain.partials.navbar')

<div class="container-fixed py-8 max-w-6xl">

    {{-- Breadcrumb / back --}}
    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}"
       class="inline-flex items-center gap-1.5 text-sm text-base-content/60 hover:text-primary mb-6">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        {{ $trans['btn.back'] ?? 'Back to home' }}
    </a>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="alert alert-success mb-6">
        <span class="icon-[tabler--check] size-5"></span>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-error mb-6">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        {{ session('error') }}
    </div>
    @endif

    {{-- Hero --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        {{-- Left: details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Banner --}}
            @if($eventImage)
                <figure class="relative h-56 md:h-72 rounded-2xl overflow-hidden border border-base-300">
                    <img src="{{ $eventImage }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <div class="absolute bottom-4 left-5">
                        <span class="badge badge-lg {{ $typeBadgeClass }}">
                            <span class="icon-[tabler--{{ $typeIcon }}] size-4 mr-1"></span> {{ $event->event_type_label }}
                        </span>
                    </div>
                </figure>
            @else
                <div class="relative h-44 rounded-2xl flex items-center justify-center bg-gradient-to-br from-primary/20 to-secondary/20 border border-base-300">
                    <span class="icon-[tabler--calendar-event] size-14 text-primary/40"></span>
                    <div class="absolute bottom-4 left-5">
                        <span class="badge badge-lg {{ $typeBadgeClass }}">
                            <span class="icon-[tabler--{{ $typeIcon }}] size-4 mr-1"></span> {{ $event->event_type_label }}
                        </span>
                    </div>
                </div>
            @endif

            <div>
                <h1 class="text-3xl md:text-4xl font-bold leading-tight">{{ $event->title }}</h1>
                @if($event->short_description)
                    <p class="text-base-content/70 mt-2">{{ $event->short_description }}</p>
                @endif
            </div>

            {{-- Quick facts --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--calendar] size-4"></span> Date
                    </div>
                    <div class="font-semibold mt-0.5 text-sm">{{ $event->start_datetime->format('M j, Y') }}</div>
                </div>
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--clock] size-4"></span> Time
                    </div>
                    <div class="font-semibold mt-0.5 text-sm">{{ $event->start_datetime->format('g:i A') }}</div>
                </div>
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--{{ $typeIcon }}] size-4"></span> Format
                    </div>
                    <div class="font-semibold mt-0.5 text-sm">{{ $event->event_type_label }}</div>
                </div>
                @if($event->skill_level)
                    <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                        <div class="flex items-center gap-2 text-base-content/60 text-xs">
                            <span class="icon-[tabler--stairs-up] size-4"></span> Level
                        </div>
                        <div class="font-semibold mt-0.5 text-sm">{{ $event->skill_level_label }}</div>
                    </div>
                @endif
            </div>

            {{-- Location --}}
            @if($event->event_type !== 'online' && ($event->venue_name || $event->full_address))
            <section>
                <h2 class="text-xl font-semibold mb-2 flex items-center gap-2">
                    <span class="icon-[tabler--map-pin] size-5 text-base-content/60"></span>
                    Location
                </h2>
                <div class="bg-base-100 border border-base-300 rounded-xl p-4">
                    @if($event->venue_name)<div class="font-medium">{{ $event->venue_name }}</div>@endif
                    @if($event->full_address)<div class="text-sm text-base-content/60 mt-0.5">{{ $event->full_address }}</div>@endif
                </div>
            </section>
            @elseif($event->event_type === 'online')
            <section>
                <h2 class="text-xl font-semibold mb-2 flex items-center gap-2">
                    <span class="icon-[tabler--device-laptop] size-5 text-base-content/60"></span>
                    Online event
                </h2>
                <div class="bg-base-100 border border-base-300 rounded-xl p-4 text-sm text-base-content/70">
                    @if($event->online_platform)Hosted on <span class="font-medium">{{ $event->online_platform }}</span>. @endif
                    The join link is shared with registered attendees.
                </div>
            </section>
            @endif

            {{-- Description --}}
            @if($event->description)
            <section>
                <h2 class="text-xl font-semibold mb-2">About this event</h2>
                <div class="prose prose-sm max-w-none text-base-content/80">{!! nl2br(e($event->description)) !!}</div>
            </section>
            @endif

            {{-- File attachments --}}
            @if(!empty($files))
            @php
                $fileIcons = [
                    'pdf' => 'icon-[tabler--file-type-pdf]',
                    'doc' => 'icon-[tabler--file-type-doc]',
                    'docx' => 'icon-[tabler--file-type-doc]',
                    'jpg' => 'icon-[tabler--photo]',
                    'jpeg' => 'icon-[tabler--photo]',
                    'png' => 'icon-[tabler--photo]',
                    'webp' => 'icon-[tabler--photo]',
                ];
                $uploadsDisk = config('filesystems.uploads');
            @endphp
            <section>
                <h2 class="text-xl font-semibold mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--paperclip] size-5 text-base-content/60"></span>
                    Resources
                </h2>
                <ul class="bg-base-100 border border-base-300 rounded-xl divide-y divide-base-300">
                    @foreach($files as $file)
                        @php
                            $fileName = is_array($file) ? ($file['name'] ?? $file['original_name'] ?? '') : (string) $file;
                            $filePath = is_array($file) ? ($file['path'] ?? null) : null;
                            $fileUrl = $filePath
                                ? \Illuminate\Support\Facades\Storage::disk($uploadsDisk)->url($filePath)
                                : (is_array($file) ? ($file['url'] ?? null) : null);
                            $fileSize = is_array($file) && isset($file['size']) ? round($file['size'] / 1024 / 1024, 1) : null;
                            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                            $icon = $fileIcons[$ext] ?? 'icon-[tabler--file]';
                        @endphp
                        @if($fileName && $fileUrl)
                            <li>
                                <a href="{{ $fileUrl }}" target="_blank" rel="noopener" download="{{ $fileName }}"
                                   class="flex items-center gap-3 p-3 hover:bg-base-200/40 transition">
                                    <span class="{{ $icon }} size-5 text-base-content/60 shrink-0"></span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium truncate">{{ $fileName }}</div>
                                        @if($fileSize)<div class="text-xs text-base-content/50">{{ $fileSize }} MB</div>@endif
                                    </div>
                                    <span class="icon-[tabler--download] size-4 text-base-content/50 shrink-0"></span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </section>
            @endif

            {{-- Registration status (the CTA itself lives in the sticky card) --}}
            @if($spotsLeft === 0)
            <section>
                <div class="bg-base-100 border border-base-300 rounded-xl p-6 text-center">
                    <span class="icon-[tabler--users-minus] size-12 text-base-content/30 mx-auto mb-3"></span>
                    <h3 class="text-lg font-semibold">{{ $trans['events.event_full'] ?? 'This Event is Full' }}</h3>
                    <p class="text-base-content/60">{{ $trans['events.check_back'] ?? 'Check back for future events or contact us for more information.' }}</p>
                </div>
            </section>
            @elseif($hasStarted)
            <section>
                <div class="bg-base-100 border border-base-300 rounded-xl p-6 text-center">
                    <span class="icon-[tabler--calendar-off] size-12 text-base-content/30 mx-auto mb-3"></span>
                    <h3 class="text-lg font-semibold">{{ $trans['events.registration_closed'] ?? 'Registration Closed' }}</h3>
                    <p class="text-base-content/60">{{ $trans['events.event_started'] ?? 'This event has already started.' }}</p>
                </div>
            </section>
            @endif
        </div>

        {{-- Right: date + register card --}}
        <aside class="lg:col-span-1">
            <div class="bg-base-100 border border-base-300 rounded-2xl p-5 sticky top-6 space-y-4">
                {{-- Date block --}}
                <div class="flex items-center gap-4">
                    <div class="text-center shrink-0 w-16 h-16 rounded-xl bg-primary text-primary-content flex flex-col items-center justify-center">
                        <span class="text-[11px] uppercase tracking-wide">{{ $event->start_datetime->format('M') }}</span>
                        <span class="text-2xl font-bold leading-none">{{ $event->start_datetime->format('j') }}</span>
                        <span class="text-[10px] uppercase">{{ $event->start_datetime->format('D') }}</span>
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold">{{ $event->start_datetime->format('l') }}</div>
                        <div class="text-sm text-base-content/60">
                            {{ $event->start_datetime->format('g:i A') }} – {{ $event->end_datetime->format('g:i A') }}
                        </div>
                    </div>
                </div>

                {{-- Meta rows --}}
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-base-content/60">Format</span>
                        <span class="font-medium">{{ $event->event_type_label }}</span>
                    </div>
                    @if($event->event_type !== 'online' && $event->venue_name)
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-base-content/60 shrink-0">Venue</span>
                            <span class="font-medium text-right truncate">{{ $event->venue_name }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-base-content/60">Availability</span>
                        @if($spotsLeft === null)
                            <span class="badge badge-soft badge-success">Open</span>
                        @elseif($spotsLeft > 0)
                            <span class="badge badge-soft {{ $spotsLeft <= 5 ? 'badge-warning' : 'badge-success' }}">{{ $spotsLeft }} spots left</span>
                        @else
                            <span class="badge badge-soft badge-error">Full</span>
                        @endif
                    </div>
                </div>

                @if($canRegister)
                    <form action="{{ route('booking.select-event', ['subdomain' => $host->subdomain, 'event' => $event->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-block">
                            <span class="icon-[tabler--calendar-plus] size-4"></span>
                            {{ $trans['btn.register'] ?? 'Register Now' }}
                        </button>
                    </form>
                @elseif($spotsLeft === 0)
                    <button type="button" class="btn btn-disabled btn-block" disabled>{{ $trans['subdomain.schedule.full'] ?? 'Event Full' }}</button>
                @elseif($hasStarted)
                    <button type="button" class="btn btn-disabled btn-block" disabled>{{ $trans['events.registration_closed'] ?? 'Registration Closed' }}</button>
                @endif

                <a href="{{ route('subdomain.service-request', ['subdomain' => $host->subdomain, 'type' => 'event', 'id' => $event->id]) }}"
                   class="btn btn-soft btn-secondary btn-block">
                    <span class="icon-[tabler--info-circle] size-4"></span>
                    {{ $trans['subdomain.service_request.request_info'] ?? 'Request Info' }}
                </a>
            </div>
        </aside>
    </div>
</div>
@endsection
