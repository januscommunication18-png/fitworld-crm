@extends('layouts.subdomain')

@section('title', $classPlan->name . ' — ' . $host->studio_name)

@php
    $currencySymbol = \App\Models\MembershipPlan::getCurrencySymbol($selectedCurrency);
    $price = $classPlan->getPriceForCurrency($selectedCurrency);
    $dropInPrice = $classPlan->getDropInPriceForCurrency($selectedCurrency);
    $newMemberPrice = $classPlan->getNewMemberPriceForCurrency($selectedCurrency);
    $newMemberDropIn = $classPlan->getNewMemberDropInPriceForCurrency($selectedCurrency);
    $regFee = is_array($classPlan->registration_fees ?? null) ? ($classPlan->registration_fees[$selectedCurrency] ?? null) : null;
    $regFee = $regFee ?? ($classPlan->registration_fee !== null ? (float) $classPlan->registration_fee : null);
    $cancelFee = is_array($classPlan->cancellation_fees ?? null) ? ($classPlan->cancellation_fees[$selectedCurrency] ?? null) : null;
    $cancelFee = $cancelFee ?? ($classPlan->cancellation_fee !== null ? (float) $classPlan->cancellation_fee : null);
    $color = $classPlan->color ?? '#6366f1';
    $equipment = $classPlan->equipment_needed ?? [];
    $files = $classPlan->file_attachments ?? [];
    $typeLabels = [
        'group' => 'Group Class',
        'workshop' => 'Workshop',
        'special_event' => 'Special Event',
    ];
@endphp

@section('content')

@include('subdomain.partials.navbar')

<div class="container-fixed py-8">
    {{-- Back link --}}
    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}"
       class="inline-flex items-center gap-1 text-sm text-base-content/60 hover:text-base-content mb-6">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        {{ $trans['btn.back'] ?? 'Back' }}
    </a>

    {{-- Hero / Header --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        {{-- Left: name, description, meta --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-start gap-4">
                @if($classPlan->image_url)
                    <button type="button" onclick="openClassPlanImage()"
                            class="relative group w-16 h-16 rounded-2xl overflow-hidden shrink-0 border border-base-300 hover:ring-2 hover:ring-primary/40 transition focus:outline-none focus:ring-2 focus:ring-primary"
                            aria-label="View larger image">
                        <img src="{{ $classPlan->image_url }}" alt="{{ $classPlan->name }}"
                             class="w-full h-full object-cover">
                        <span class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center">
                            <span class="icon-[tabler--zoom-in] size-5 text-white opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </span>
                    </button>
                @else
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0"
                         style="background-color: {{ $color }}15;">
                        <span class="icon-[tabler--yoga] size-9" style="color: {{ $color }};"></span>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl md:text-4xl font-bold leading-tight">{{ $classPlan->name }}</h1>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        @if($classPlan->type && isset($typeLabels[$classPlan->type]))
                            <span class="badge badge-soft badge-primary">{{ $typeLabels[$classPlan->type] }}</span>
                        @endif
                        @if($classPlan->category)
                            <span class="badge badge-ghost">{{ $classPlan->category }}</span>
                        @endif
                        @if($classPlan->difficulty_level)
                            <span class="badge {{ $classPlan->getDifficultyBadgeClass() }}">
                                {{ ucfirst(str_replace('_', ' ', $classPlan->difficulty_level)) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quick facts --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @if($classPlan->default_duration_minutes)
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--clock] size-4"></span>
                        Duration
                    </div>
                    <div class="font-semibold mt-0.5">{{ $classPlan->formatted_duration }}</div>
                </div>
                @endif
                @if($classPlan->default_capacity)
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--users] size-4"></span>
                        Capacity
                    </div>
                    <div class="font-semibold mt-0.5">
                        @if($classPlan->min_capacity)
                            {{ $classPlan->min_capacity }}–{{ $classPlan->default_capacity }}
                        @else
                            Max {{ $classPlan->default_capacity }}
                        @endif
                    </div>
                </div>
                @endif
                @if($classPlan->difficulty_level)
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--barbell] size-4"></span>
                        Level
                    </div>
                    <div class="font-semibold mt-0.5">{{ ucfirst(str_replace('_', ' ', $classPlan->difficulty_level)) }}</div>
                </div>
                @endif
                @if($classPlan->category)
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--category] size-4"></span>
                        Category
                    </div>
                    <div class="font-semibold mt-0.5 truncate">{{ $classPlan->category }}</div>
                </div>
                @endif
            </div>

            {{-- Description --}}
            @if($classPlan->description)
            <section>
                <h2 class="text-xl font-semibold mb-2">About this class</h2>
                <div class="prose prose-sm max-w-none text-base-content/80 whitespace-pre-line">{{ $classPlan->description }}</div>
            </section>
            @endif

            {{-- What to bring / equipment --}}
            @if(!empty($equipment))
            <section>
                <h2 class="text-xl font-semibold mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--barbell] size-5 text-base-content/60"></span>
                    What to bring
                </h2>
                <div class="bg-base-100 border border-base-300 rounded-xl p-4">
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($equipment as $item)
                            <li class="flex items-center gap-2 text-sm">
                                <span class="icon-[tabler--check] size-4 text-success shrink-0"></span>
                                {{ is_array($item) ? ($item['name'] ?? '') : $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
            @endif

            {{-- Policies & fees --}}
            @if($regFee !== null || $cancelFee !== null || $classPlan->cancellation_grace_hours !== null)
            <section>
                <h2 class="text-xl font-semibold mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--shield-check] size-5 text-base-content/60"></span>
                    Policies & fees
                </h2>
                <div class="bg-base-100 border border-base-300 rounded-xl divide-y divide-base-300">
                    @if($regFee !== null)
                    <div class="flex items-center justify-between p-4">
                        <div>
                            <div class="font-medium">Registration fee</div>
                            <div class="text-xs text-base-content/60 mt-0.5">One-time fee on first booking</div>
                        </div>
                        <div class="text-lg font-semibold" style="color: {{ $color }};">
                            {{ $currencySymbol }}{{ number_format((float) $regFee, 2) }}
                        </div>
                    </div>
                    @endif
                    @if($classPlan->cancellation_grace_hours !== null)
                    <div class="flex items-center justify-between p-4">
                        <div>
                            <div class="font-medium">Free cancellation window</div>
                            <div class="text-xs text-base-content/60 mt-0.5">Cancel without penalty if you give us notice</div>
                        </div>
                        <div class="font-semibold">{{ $classPlan->cancellation_grace_hours }} hours</div>
                    </div>
                    @endif
                    @if($cancelFee !== null && $cancelFee > 0)
                    <div class="flex items-center justify-between p-4">
                        <div>
                            <div class="font-medium">Late cancellation fee</div>
                            <div class="text-xs text-base-content/60 mt-0.5">Inside the cancellation window</div>
                        </div>
                        <div class="text-lg font-semibold text-warning">
                            {{ $currencySymbol }}{{ number_format((float) $cancelFee, 2) }}
                        </div>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            {{-- File attachments --}}
            @if(!empty($files))
            @php
                $fileIcons = [
                    'pdf' => 'icon-[tabler--file-type-pdf]',
                    'doc' => 'icon-[tabler--file-type-doc]',
                    'docx' => 'icon-[tabler--file-type-doc]',
                    'xls' => 'icon-[tabler--file-type-xls]',
                    'xlsx' => 'icon-[tabler--file-type-xls]',
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
                            $fileUrl = $filePath ? \Illuminate\Support\Facades\Storage::disk($uploadsDisk)->url($filePath) : (is_array($file) ? ($file['url'] ?? null) : null);
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
                                    @if($fileSize)
                                        <div class="text-xs text-base-content/50">{{ $fileSize }} MB</div>
                                    @endif
                                </div>
                                <span class="icon-[tabler--download] size-4 text-base-content/50 shrink-0"></span>
                            </a>
                        </li>
                        @endif
                    @endforeach
                </ul>
            </section>
            @endif

            {{-- Instructors --}}
            @if($classPlan->instructors->isNotEmpty())
            <section>
                <h2 class="text-xl font-semibold mb-3">Taught by</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($classPlan->instructors as $instructor)
                        @php
                            $initials = collect(explode(' ', $instructor->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
                        @endphp
                        <a href="{{ route('subdomain.instructor', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}"
                           class="flex items-center gap-3 p-3 rounded-xl bg-base-100 border border-base-300 hover:border-primary/40 hover:shadow-sm transition">
                            @if($instructor->photo_url)
                                <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}"
                                     class="w-12 h-12 rounded-full object-cover shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center font-semibold shrink-0">
                                    {{ $initials }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium truncate">{{ $instructor->name }}</div>
                                @if($instructor->specialties)
                                    @php
                                        $specs = is_array($instructor->specialties) ? $instructor->specialties : [$instructor->specialties];
                                    @endphp
                                    <div class="text-xs text-base-content/60 truncate">{{ implode(' · ', array_slice($specs, 0, 3)) }}</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- Upcoming sessions --}}
            <section>
                <h2 class="text-xl font-semibold mb-3">Upcoming sessions</h2>
                @if($upcomingSessions->isEmpty())
                    <div class="border border-dashed border-base-300 rounded-xl p-6 text-center text-sm text-base-content/60">
                        No upcoming sessions scheduled right now. Check back soon or request info below.
                    </div>
                @else
                    <ul class="divide-y divide-base-300 bg-base-100 border border-base-300 rounded-xl">
                        @foreach($upcomingSessions as $session)
                        <li>
                            <button type="button" onclick="openDrawer('session-{{ $session->id }}', event)"
                                    class="flex items-center gap-4 p-4 w-full text-left hover:bg-base-200/40 transition">
                                <div class="text-center shrink-0 w-14">
                                    <div class="text-xs uppercase text-base-content/50">{{ $session->start_time->format('M') }}</div>
                                    <div class="text-2xl font-bold leading-none text-base-content">{{ $session->start_time->format('d') }}</div>
                                    <div class="text-xs text-base-content/50 mt-0.5">{{ $session->start_time->format('D') }}</div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium truncate">
                                        {{ $session->start_time->format('g:i A') }} – {{ $session->end_time->format('g:i A') }}
                                    </div>
                                    <div class="text-xs text-base-content/60 truncate mt-0.5 flex items-center gap-3 flex-wrap">
                                        @if($session->primaryInstructor)
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--user] size-3.5"></span>
                                                {{ $session->primaryInstructor->name }}
                                            </span>
                                        @endif
                                        @if($session->room?->location)
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--map-pin] size-3.5"></span>
                                                {{ $session->room->location->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <span class="icon-[tabler--chevron-right] size-5 text-base-content/40 shrink-0"></span>
                            </button>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        {{-- Right: pricing + CTAs --}}
        <aside class="lg:sticky lg:top-24">
            <div class="bg-base-100 border border-base-300 rounded-2xl p-6 shadow-sm space-y-5">
                {{-- Public booking price (hero) --}}
                @if($price !== null)
                    <div class="text-center">
                        <div class="text-4xl font-bold text-base-content">
                            {{ $currencySymbol }}{{ number_format($price, 2) }}
                        </div>
                        <div class="text-sm text-base-content/50">per class</div>
                    </div>
                @endif

                {{-- New-member callout (no drop-in) --}}
                @if($newMemberPrice !== null)
                <div class="rounded-xl border border-warning/30 bg-warning/5 p-3 flex items-center gap-3">
                    <span class="icon-[tabler--sparkles] size-5 text-warning shrink-0"></span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] uppercase tracking-wider text-warning font-semibold">New members</div>
                        <div class="text-sm">
                            <span class="font-bold text-warning">{{ $currencySymbol }}{{ number_format($newMemberPrice, 2) }}</span>
                            <span class="text-base-content/60">per class</span>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Registration fee inline note --}}
                @if($regFee !== null && $regFee > 0)
                <div class="alert alert-soft alert-info text-xs p-3">
                    <span class="icon-[tabler--info-circle] size-4 shrink-0"></span>
                    <span>One-time {{ $currencySymbol }}{{ number_format((float) $regFee, 2) }} registration fee applies on first booking.</span>
                </div>
                @endif

                {{-- CTAs --}}
                <div class="space-y-2">
                    <a href="{{ route('booking.select-class-plan-type', ['subdomain' => $host->subdomain, 'classPlan' => $classPlan->id]) }}"
                       class="btn btn-primary w-full">
                        <span class="icon-[tabler--calendar-plus] size-5"></span>
                        {{ $trans['btn.book_now'] ?? 'Book Now' }}
                    </a>
                    <a href="{{ route('subdomain.class-request', ['subdomain' => $host->subdomain, 'class_plan_id' => $classPlan->id]) }}"
                       class="btn btn-soft btn-secondary w-full">
                        <span class="icon-[tabler--info-circle] size-4"></span>
                        {{ $trans['subdomain.service_request.request_info'] ?? 'Request Info' }}
                    </a>
                </div>

                {{-- At a glance --}}
                <div>
                    <div class="text-xs text-base-content/40 uppercase tracking-wider mb-2">At a glance</div>
                    <ul class="text-sm space-y-2">
                        @if($classPlan->default_duration_minutes)
                        <li class="flex items-center justify-between">
                            <span class="text-base-content/60 flex items-center gap-2">
                                <span class="icon-[tabler--clock] size-4"></span>
                                Duration
                            </span>
                            <span class="font-medium">{{ $classPlan->formatted_duration }}</span>
                        </li>
                        @endif
                        @if($classPlan->default_capacity)
                        <li class="flex items-center justify-between">
                            <span class="text-base-content/60 flex items-center gap-2">
                                <span class="icon-[tabler--users] size-4"></span>
                                Max capacity
                            </span>
                            <span class="font-medium">{{ $classPlan->default_capacity }}</span>
                        </li>
                        @endif
                        @if($classPlan->difficulty_level)
                        <li class="flex items-center justify-between">
                            <span class="text-base-content/60 flex items-center gap-2">
                                <span class="icon-[tabler--barbell] size-4"></span>
                                Level
                            </span>
                            <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $classPlan->difficulty_level)) }}</span>
                        </li>
                        @endif
                        @if($classPlan->cancellation_grace_hours !== null)
                        <li class="flex items-center justify-between">
                            <span class="text-base-content/60 flex items-center gap-2">
                                <span class="icon-[tabler--clock-x] size-4"></span>
                                Free cancel
                            </span>
                            <span class="font-medium">{{ $classPlan->cancellation_grace_hours }}h ahead</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </aside>
    </div>
</div>

{{-- Upcoming session drawers --}}
@foreach($upcomingSessions as $session)
    @php
        $sessionPrice = $session->price ?? $classPlan->getPriceForCurrency($selectedCurrency);
        $spotsLeft = $session->capacity - ($session->bookings_count ?? 0);
    @endphp
    <x-detail-drawer id="session-{{ $session->id }}"
                     title="{{ $session->title ?? $classPlan->name }}"
                     size="3xl"
                     :showFooter="false">
        <div class="space-y-5">
            {{-- Date + time hero --}}
            <div class="flex items-center gap-4 p-4 rounded-xl" style="background-color: {{ $color }}10;">
                <div class="text-center shrink-0 w-16 h-16 rounded-xl bg-base-100 flex flex-col items-center justify-center border border-base-300">
                    <span class="text-xs uppercase text-base-content/50">{{ $session->start_time->format('M') }}</span>
                    <span class="text-2xl font-bold leading-none text-base-content">{{ $session->start_time->format('d') }}</span>
                    <span class="text-[10px] uppercase text-base-content/50 mt-0.5">{{ $session->start_time->format('D') }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-lg font-bold">{{ $session->start_time->format('g:i A') }} – {{ $session->end_time->format('g:i A') }}</div>
                    <div class="text-sm text-base-content/60 mt-0.5">{{ $session->start_time->format('l, F j, Y') }}</div>
                </div>
            </div>

            {{-- Meta facts --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--clock] size-4"></span>
                        Duration
                    </div>
                    <div class="font-semibold mt-0.5">{{ $session->duration_minutes ?? $classPlan->default_duration_minutes }} min</div>
                </div>
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--users] size-4"></span>
                        Availability
                    </div>
                    <div class="font-semibold mt-0.5">
                        @if($spotsLeft > 0)
                            <span class="text-success">{{ $spotsLeft }} of {{ $session->capacity }} left</span>
                        @else
                            <span class="text-error">Full</span>
                        @endif
                    </div>
                </div>
                @if($session->primaryInstructor)
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--user] size-4"></span>
                        Instructor
                    </div>
                    <div class="font-semibold mt-0.5 truncate">{{ $session->primaryInstructor->name }}</div>
                </div>
                @endif
                @if($session->room)
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--map-pin] size-4"></span>
                        Location
                    </div>
                    <div class="font-semibold mt-0.5 truncate">
                        {{ $session->room->name }}@if($session->room->location), {{ $session->room->location->name }}@endif
                    </div>
                </div>
                @endif
            </div>

            {{-- Price --}}
            @if($sessionPrice !== null && $sessionPrice > 0)
            <div class="flex items-center justify-between bg-base-100 border border-base-300 rounded-xl p-4">
                <span class="text-sm text-base-content/60">Price</span>
                <span class="text-xl font-bold text-base-content">
                    {{ $currencySymbol }}{{ number_format($sessionPrice, 2) }}
                </span>
            </div>
            @endif

            {{-- Description --}}
            @if($classPlan->description)
            <div>
                <h3 class="font-semibold mb-2">About this class</h3>
                <p class="text-sm text-base-content/70 whitespace-pre-line">{{ $classPlan->description }}</p>
            </div>
            @endif

            {{-- Actions --}}
            <div class="space-y-2 pt-2">
                @if($spotsLeft > 0)
                    <form action="{{ route('booking.select-class-session', ['subdomain' => $host->subdomain, 'session' => $session->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full">
                            <span class="icon-[tabler--calendar-plus] size-5"></span>
                            {{ $trans['btn.book_now'] ?? 'Book this class' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('subdomain.class-request.session', ['subdomain' => $host->subdomain, 'sessionId' => $session->id, 'waitlist' => 1]) }}"
                       class="btn btn-warning w-full">
                        <span class="icon-[tabler--list-check] size-5"></span>
                        {{ $trans['btn.join_waitlist'] ?? 'Join Waitlist' }}
                    </a>
                @endif
                <button type="button" onclick="closeDrawer('session-{{ $session->id }}')" class="btn btn-ghost w-full">
                    Close
                </button>
            </div>
        </div>
    </x-detail-drawer>
@endforeach

@if($classPlan->image_url)
{{-- Image lightbox --}}
<div id="class-plan-image-modal"
     class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4"
     onclick="closeClassPlanImage()">
    <div class="absolute inset-0 bg-black/85"></div>
    <button type="button" onclick="closeClassPlanImage()"
            class="absolute top-4 right-4 z-20 bg-base-100 hover:bg-base-200 rounded-full p-2 shadow-lg transition"
            aria-label="Close">
        <span class="icon-[tabler--x] size-5"></span>
    </button>
    <img src="{{ $classPlan->image_url }}" alt="{{ $classPlan->name }}"
         class="relative z-10 max-w-[95vw] max-h-[95vh] w-auto h-auto object-contain rounded-xl shadow-2xl"
         onclick="event.stopPropagation()">
</div>

@push('scripts')
<script>
function openClassPlanImage() {
    document.getElementById('class-plan-image-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeClassPlanImage() {
    document.getElementById('class-plan-image-modal').classList.add('hidden');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var m = document.getElementById('class-plan-image-modal');
        if (m && !m.classList.contains('hidden')) {
            m.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }
});
</script>
@endpush
@endif
@endsection
