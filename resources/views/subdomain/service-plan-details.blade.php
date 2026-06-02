@extends('layouts.subdomain')

@section('title', $servicePlan->name . ' — ' . $host->studio_name)

@section('content')
@php
    $color = $servicePlan->color ?? '#6366f1';
    $currencySymbol = \App\Models\MembershipPlan::getCurrencySymbol($selectedCurrency);
    $price = $servicePlan->getPriceForCurrency($selectedCurrency);
    $deposit = $servicePlan->getDepositForCurrency($selectedCurrency) ?? null;
    $regFee = is_array($servicePlan->registration_fees ?? null)
        ? ($servicePlan->registration_fees[$selectedCurrency] ?? null)
        : null;
    $regFee = $regFee ?? ($servicePlan->registration_fee !== null ? (float) $servicePlan->registration_fee : null);
    $cancelFee = is_array($servicePlan->cancellation_fees ?? null)
        ? ($servicePlan->cancellation_fees[$selectedCurrency] ?? null)
        : null;
    $cancelFee = $cancelFee ?? ($servicePlan->cancellation_fee !== null ? (float) $servicePlan->cancellation_fee : null);
    $files = $servicePlan->file_attachments ?? [];
    $hasSeries = $servicePlan->hasSeriesOptionForCurrency($selectedCurrency);
    $locationTypeLabels = [
        'in_studio' => 'In-studio',
        'online' => 'Online',
        'client_location' => 'Client location',
    ];
@endphp

@include('subdomain.partials.navbar')

<div class="container-fixed py-8 max-w-6xl">

    {{-- Breadcrumb / back --}}
    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}"
       class="inline-flex items-center gap-1.5 text-sm text-base-content/60 hover:text-primary mb-6">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        {{ $trans['btn.back'] ?? 'Back to home' }}
    </a>

    {{-- Hero --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        {{-- Left: name, description, slots --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-start gap-4">
                @if($servicePlan->image_url)
                    <div class="w-16 h-16 rounded-2xl overflow-hidden shrink-0 border border-base-300">
                        <img src="{{ $servicePlan->image_url }}" alt="{{ $servicePlan->name }}" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0"
                         style="background-color: {{ $color }}15;">
                        <span class="icon-[tabler--sparkles] size-9" style="color: {{ $color }};"></span>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl md:text-4xl font-bold leading-tight">{{ $servicePlan->name }}</h1>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        @if($servicePlan->category)
                            <span class="badge badge-ghost">{{ $servicePlan->category }}</span>
                        @endif
                        @if($servicePlan->location_type)
                            <span class="badge badge-soft badge-primary">{{ $locationTypeLabels[$servicePlan->location_type] ?? ucfirst(str_replace('_', ' ', $servicePlan->location_type)) }}</span>
                        @endif
                        @if($hasSeries)
                            <span class="badge badge-soft badge-accent">Series available</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quick facts --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @if($servicePlan->duration_minutes)
                    <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                        <div class="flex items-center gap-2 text-base-content/60 text-xs">
                            <span class="icon-[tabler--clock] size-4"></span> Duration
                        </div>
                        <div class="font-semibold mt-0.5">{{ $servicePlan->formatted_duration }}</div>
                    </div>
                @endif
                @if($servicePlan->max_participants)
                    <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                        <div class="flex items-center gap-2 text-base-content/60 text-xs">
                            <span class="icon-[tabler--users] size-4"></span> Max participants
                        </div>
                        <div class="font-semibold mt-0.5">{{ $servicePlan->max_participants }}</div>
                    </div>
                @endif
                @if($servicePlan->category)
                    <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                        <div class="flex items-center gap-2 text-base-content/60 text-xs">
                            <span class="icon-[tabler--category] size-4"></span> Category
                        </div>
                        <div class="font-semibold mt-0.5 truncate">{{ $servicePlan->category }}</div>
                    </div>
                @endif
                @if($servicePlan->cancellation_grace_hours)
                    <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                        <div class="flex items-center gap-2 text-base-content/60 text-xs">
                            <span class="icon-[tabler--shield-check] size-4"></span> Free cancel
                        </div>
                        <div class="font-semibold mt-0.5">{{ $servicePlan->cancellation_grace_hours }}h before</div>
                    </div>
                @endif
            </div>

            {{-- Description --}}
            @if($servicePlan->description)
            <section>
                <h2 class="text-xl font-semibold mb-2">About this service</h2>
                <div class="prose prose-sm max-w-none text-base-content/80 whitespace-pre-line">{{ $servicePlan->description }}</div>
            </section>
            @endif

            {{-- Series billing options --}}
            @if($hasSeries)
            <section>
                <h2 class="text-xl font-semibold mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--calendar-repeat] size-5 text-base-content/60"></span>
                    Series options
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
                    @foreach(['1' => '1 Month', '3' => '3 Months', '6' => '6 Months', '9' => '9 Months', '12' => '12 Months'] as $months => $label)
                        @php $total = $servicePlan->getBillingPeriodTotalForCurrency($months, $selectedCurrency); @endphp
                        @if($total > 0)
                            @php $m = (int) $months; $monthly = $m > 0 ? $total / $m : 0; @endphp
                            <div class="bg-base-100 border border-base-300 rounded-xl p-3 text-center">
                                <div class="text-xs text-base-content/60">{{ $label }}</div>
                                <div class="font-bold text-success mt-1">{{ $currencySymbol }}{{ number_format($total, 0) }}</div>
                                <div class="text-[10px] text-base-content/50 mt-0.5">{{ $currencySymbol }}{{ number_format($monthly, 2) }}/mo</div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <p class="text-xs text-base-content/50 mt-2">Choose Series at checkout to lock in these rates.</p>
            </section>
            @endif

            {{-- Policies & fees --}}
            @if($regFee !== null || $cancelFee !== null || $servicePlan->cancellation_grace_hours !== null || $servicePlan->booking_notice_hours !== null)
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
                    @if($servicePlan->cancellation_grace_hours !== null)
                        <div class="flex items-center justify-between p-4">
                            <div>
                                <div class="font-medium">Free cancellation window</div>
                                <div class="text-xs text-base-content/60 mt-0.5">Cancel without penalty within this window</div>
                            </div>
                            <div class="font-semibold">{{ $servicePlan->cancellation_grace_hours }} hours</div>
                        </div>
                    @endif
                    @if($cancelFee !== null && $cancelFee > 0)
                        <div class="flex items-center justify-between p-4">
                            <div>
                                <div class="font-medium">Late cancellation fee</div>
                                <div class="text-xs text-base-content/60 mt-0.5">Charged inside the cancellation window</div>
                            </div>
                            <div class="text-lg font-semibold text-warning">
                                {{ $currencySymbol }}{{ number_format((float) $cancelFee, 2) }}
                            </div>
                        </div>
                    @endif
                    @if($servicePlan->booking_notice_hours !== null)
                        <div class="flex items-center justify-between p-4">
                            <div>
                                <div class="font-medium">Booking notice</div>
                                <div class="text-xs text-base-content/60 mt-0.5">Minimum advance notice required to book</div>
                            </div>
                            <div class="font-semibold">{{ $servicePlan->booking_notice_hours }} hours</div>
                        </div>
                    @endif
                    @if($servicePlan->buffer_minutes)
                        <div class="flex items-center justify-between p-4">
                            <div>
                                <div class="font-medium">Buffer time</div>
                                <div class="text-xs text-base-content/60 mt-0.5">Time reserved before/after each session</div>
                            </div>
                            <div class="font-semibold">{{ $servicePlan->buffer_minutes }} minutes</div>
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

            {{-- Upcoming slots --}}
            <section>
                <h2 class="text-xl font-semibold mb-3">Upcoming slots</h2>
                @if($upcomingSlots->isEmpty())
                    <div class="bg-base-100 border border-base-300 rounded-xl p-6 text-center text-base-content/60">
                        <span class="icon-[tabler--calendar-off] size-10 inline-block mb-2 text-base-content/30"></span>
                        <p>No upcoming slots are open right now. The studio will reach out to schedule with you when you book.</p>
                    </div>
                @else
                    <ul class="bg-base-100 border border-base-300 rounded-xl divide-y divide-base-300">
                        @foreach($upcomingSlots as $slot)
                            <li>
                                <button type="button" onclick="openDrawer('service-slot-{{ $slot->id }}', event)"
                                        class="flex items-center gap-3 p-3 w-full text-left hover:bg-base-200/40 transition">
                                <div class="text-center min-w-[60px] p-2 rounded-lg" style="background-color: {{ $color }}15;">
                                    <p class="text-xl font-bold" style="color: {{ $color }};">{{ $slot->start_time->format('j') }}</p>
                                    <p class="text-[10px] uppercase" style="color: {{ $color }};">{{ $slot->start_time->format('M') }}</p>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium">{{ $slot->start_time->format('l') }}</div>
                                    <div class="text-sm text-base-content/60">
                                        {{ $slot->start_time->format('g:i A') }}
                                        @if($slot->end_time) – {{ $slot->end_time->format('g:i A') }}@endif
                                    </div>
                                    <div class="text-xs text-base-content/50 mt-0.5 flex flex-wrap gap-2">
                                        @if($slot->instructor)<span><span class="icon-[tabler--user] size-3 inline-block align-text-bottom"></span> {{ $slot->instructor->name }}</span>@endif
                                        @if($slot->location)<span><span class="icon-[tabler--map-pin] size-3 inline-block align-text-bottom"></span> {{ $slot->location->name }}</span>@endif
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

        {{-- Right: price + book card --}}
        <aside class="lg:col-span-1">
            <div class="bg-base-100 border border-base-300 rounded-2xl p-5 sticky top-6 space-y-4">
                @if($price !== null)
                    <div>
                        <div class="text-3xl font-bold" style="color: {{ $color }};">{{ $currencySymbol }}{{ number_format($price, 0) }}</div>
                        <div class="text-xs uppercase tracking-wider text-base-content/50 mt-1">per session</div>
                    </div>
                @endif
                @if($deposit !== null && $deposit > 0)
                    <div class="text-sm">
                        <span class="text-base-content/60">Deposit</span>:
                        <span class="font-medium">{{ $currencySymbol }}{{ number_format($deposit, 2) }}</span>
                    </div>
                @endif

                <form action="{{ route('booking.select-service-plan', ['subdomain' => $host->subdomain, 'servicePlan' => $servicePlan->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="icon-[tabler--calendar-plus] size-4"></span>
                        {{ $trans['btn.book_now'] ?? 'Book Now' }}
                    </button>
                </form>

                <a href="{{ route('subdomain.service-request.plan', ['subdomain' => $host->subdomain, 'servicePlanId' => $servicePlan->id]) }}"
                   class="btn btn-soft btn-secondary btn-block">
                    <span class="icon-[tabler--info-circle] size-4"></span>
                    {{ $trans['subdomain.service_request.request_info'] ?? 'Request Info' }}
                </a>
            </div>
        </aside>
    </div>
</div>

{{-- Upcoming slot drawers --}}
@foreach($upcomingSlots as $slot)
    <x-detail-drawer id="service-slot-{{ $slot->id }}"
                     title="{{ $servicePlan->name }}"
                     size="3xl"
                     :showFooter="false">
        <div class="space-y-5">
            {{-- Date + time hero --}}
            <div class="flex items-center gap-4 p-4 rounded-xl" style="background-color: {{ $color }}10;">
                <div class="text-center shrink-0 w-16 h-16 rounded-xl bg-base-100 flex flex-col items-center justify-center border border-base-300">
                    <span class="text-xs uppercase text-base-content/50">{{ $slot->start_time->format('M') }}</span>
                    <span class="text-2xl font-bold leading-none text-base-content">{{ $slot->start_time->format('d') }}</span>
                    <span class="text-[10px] uppercase text-base-content/50 mt-0.5">{{ $slot->start_time->format('D') }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-lg font-bold">
                        {{ $slot->start_time->format('g:i A') }}@if($slot->end_time) – {{ $slot->end_time->format('g:i A') }}@endif
                    </div>
                    <div class="text-sm text-base-content/60 mt-0.5">{{ $slot->start_time->format('l, F j, Y') }}</div>
                </div>
            </div>

            {{-- Meta facts --}}
            <div class="grid grid-cols-2 gap-3">
                @if($servicePlan->duration_minutes)
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--clock] size-4"></span> Duration
                    </div>
                    <div class="font-semibold mt-0.5">{{ $servicePlan->duration_minutes }} min</div>
                </div>
                @endif
                @if($slot->instructor)
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--user] size-4"></span> Instructor
                    </div>
                    <div class="font-semibold mt-0.5 truncate">{{ $slot->instructor->name }}</div>
                </div>
                @endif
                @if($slot->location)
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--map-pin] size-4"></span> Location
                    </div>
                    <div class="font-semibold mt-0.5 truncate">{{ $slot->location->name }}</div>
                </div>
                @endif
                @if($servicePlan->location_type)
                <div class="bg-base-100 border border-base-300 rounded-xl p-3">
                    <div class="flex items-center gap-2 text-base-content/60 text-xs">
                        <span class="icon-[tabler--building] size-4"></span> Format
                    </div>
                    <div class="font-semibold mt-0.5">{{ $locationTypeLabels[$servicePlan->location_type] ?? ucfirst(str_replace('_', ' ', $servicePlan->location_type)) }}</div>
                </div>
                @endif
            </div>

            {{-- Price --}}
            @if($price !== null && $price > 0)
            <div class="flex items-center justify-between bg-base-100 border border-base-300 rounded-xl p-4">
                <span class="text-sm text-base-content/60">Price</span>
                <span class="text-xl font-bold text-base-content">
                    {{ $currencySymbol }}{{ number_format($price, 2) }}
                </span>
            </div>
            @endif

            {{-- Description --}}
            @if($servicePlan->description)
            <div>
                <h3 class="font-semibold mb-2">About this service</h3>
                <p class="text-sm text-base-content/70 whitespace-pre-line">{{ $servicePlan->description }}</p>
            </div>
            @endif

            {{-- Actions --}}
            <div class="space-y-2 pt-2">
                <form action="{{ route('booking.select-service-plan', ['subdomain' => $host->subdomain, 'servicePlan' => $servicePlan->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full">
                        <span class="icon-[tabler--calendar-plus] size-5"></span>
                        {{ $trans['btn.book_now'] ?? 'Book this slot' }}
                    </button>
                </form>
                <button type="button" onclick="closeDrawer('service-slot-{{ $slot->id }}')" class="btn btn-ghost w-full">
                    Close
                </button>
            </div>
        </div>
    </x-detail-drawer>
@endforeach

@endsection
