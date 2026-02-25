@extends('layouts.subdomain')

@section('title', $host->studio_name . ' — ' . ($trans['subdomain.home.book_class'] ?? 'Book a Class'))

@php
    $selectedCurrency = session("currency_{$host->id}", $host->default_currency ?? 'USD');
    $currencySymbol = \App\Models\MembershipPlan::getCurrencySymbol($selectedCurrency);
    $selectedLang = session("language_{$host->id}", $host->default_language_booking ?? 'en');
    $t = \App\Services\TranslationService::make($host, $selectedLang);
    $trans = $t->all();
@endphp

@section('content')

@include('subdomain.partials.navbar')

{{-- Hero Section --}}
<section class="relative min-h-[320px] md:min-h-[400px] flex items-center"
         @if($host->cover_image_url)
         style="background-image: url('{{ $host->cover_image_url }}'); background-size: cover; background-position: center;"
         @endif>

    {{-- Gradient Overlay --}}
    @if($host->cover_image_url)
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"></div>
    @else
        <div class="absolute inset-0 bg-gradient-to-br from-primary to-secondary"></div>
    @endif

    {{-- Hero Content --}}
    <div class="relative w-full py-12">
        <div class="container-fixed text-center">
            {{-- Studio Name --}}
            <h1 class="text-4xl md:text-6xl font-bold text-white mb-4 drop-shadow-lg">
                {{ $bookingSettings['display_name'] ?? $host->studio_name }}
            </h1>

            {{-- Tagline/Short Description --}}
            @if($host->short_description)
            <p class="text-white/90 text-lg md:text-xl max-w-2xl mx-auto mb-6">
                {{ $host->short_description }}
            </p>
            @endif

            {{-- Location --}}
            @if(($host->show_address ?? true) && ($defaultLocation ?? null))
            <p class="text-white/80 text-base flex items-center justify-center gap-2">
                <span class="icon-[tabler--map-pin] size-5"></span>
                {{ $defaultLocation->full_address }}
            </p>
            @endif
        </div>
    </div>
</section>

{{-- Main Content --}}
<div class="container-fixed py-10">

    {{-- Page Navigation Tabs --}}
    <div class="flex justify-center mb-10">
        <div class="tabs tabs-boxed bg-base-200 p-1">
            <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="tab tab-active">
                <span class="icon-[tabler--home] size-4 me-1"></span> {{ $trans['nav.dashboard'] ?? 'Home' }}
            </a>
            <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}" class="tab">
                <span class="icon-[tabler--calendar] size-4 me-1"></span> {{ $trans['nav.schedule'] ?? 'Schedule' }}
            </a>
            @if($bookingSettings['show_instructors'] ?? true)
            <a href="{{ route('subdomain.instructors', ['subdomain' => $host->subdomain]) }}" class="tab">
                <span class="icon-[tabler--users] size-4 me-1"></span> {{ $trans['nav.instructors'] ?? 'Instructors' }}
            </a>
            @endif
        </div>
    </div>

    {{-- 1. CLASSES SECTION --}}
    <section class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold">{{ $trans['dashboard.upcoming_classes'] ?? 'Upcoming Classes' }}</h2>
                <p class="text-base-content/60 mt-1">{{ $trans['subdomain.home.join_sessions'] ?? 'Join our group fitness sessions' }}</p>
            </div>
            <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                {{ $trans['btn.view_all'] ?? 'View All' }} <span class="icon-[tabler--arrow-right] size-4 ms-1"></span>
            </a>
        </div>

        @if($upcomingSessions->isEmpty())
        <div class="card bg-base-100 shadow border border-base-200">
            <div class="card-body items-center text-center py-16">
                <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-4">
                    <span class="icon-[tabler--calendar-off] size-10 text-base-content/30"></span>
                </div>
                <h3 class="text-lg font-semibold">{{ $trans['dashboard.no_classes_today'] ?? 'No Upcoming Classes' }}</h3>
                <p class="text-base-content/60">{{ $trans['subdomain.home.check_back'] ?? 'Check back soon for new classes!' }}</p>
            </div>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($upcomingSessions->take(6) as $session)
            <div class="card card-side bg-base-100 shadow border border-base-200 overflow-hidden hover:shadow-lg transition-shadow">
                {{-- Date Badge --}}
                <div class="flex flex-col items-center justify-center w-24 bg-primary text-primary-content shrink-0">
                    <span class="text-sm font-medium uppercase">{{ $session->start_time->format('M') }}</span>
                    <span class="text-3xl font-bold leading-none">{{ $session->start_time->format('j') }}</span>
                    <span class="text-sm">{{ $session->start_time->format('D') }}</span>
                </div>

                {{-- Content --}}
                <div class="card-body p-4">
                    <h3 class="card-title text-base">{{ $session->title ?? ($session->classPlan->name ?? 'Class') }}</h3>

                    <div class="text-sm text-base-content/60 space-y-1">
                        <p class="flex items-center gap-2">
                            <span class="icon-[tabler--clock] size-4"></span>
                            {{ $session->start_time->format('g:i A') }} - {{ $session->end_time->format('g:i A') }}
                        </p>
                        @if($session->primaryInstructor)
                        <p class="flex items-center gap-2">
                            <span class="icon-[tabler--user] size-4"></span>
                            {{ $session->primaryInstructor->name }}
                        </p>
                        @endif
                        @if($session->room)
                        <p class="flex items-center gap-2">
                            <span class="icon-[tabler--map-pin] size-4"></span>
                            {{ $session->room->name }}
                        </p>
                        @endif
                    </div>

                    <div class="card-actions justify-between items-center mt-auto pt-2">
                        @php $spotsLeft = $session->capacity - ($session->bookings_count ?? 0); @endphp
                        @if($spotsLeft <= 0)
                            <span class="badge badge-error">{{ $trans['subdomain.schedule.full'] ?? 'Full' }}</span>
                            <a href="{{ route('subdomain.class-request.session', ['subdomain' => $host->subdomain, 'sessionId' => $session->id, 'waitlist' => 1]) }}"
                               class="btn btn-warning btn-sm">
                                {{ $trans['btn.join_waitlist'] ?? 'Join Waitlist' }}
                            </a>
                        @else
                            @if($spotsLeft <= 3)
                                <span class="badge badge-warning">{{ $spotsLeft }} {{ $trans['subdomain.home.left'] ?? 'left' }}</span>
                            @else
                                <span class="text-xs text-base-content/40">{{ $spotsLeft }} {{ $trans['subdomain.home.spots'] ?? 'spots' }}</span>
                            @endif
                            <div class="flex items-center gap-2">
                                <a href="{{ route('subdomain.class', ['subdomain' => $host->subdomain, 'classSession' => $session->id]) }}"
                                   class="btn btn-ghost btn-sm">
                                    <span class="icon-[tabler--info-circle] size-4"></span>
                                    {{ $trans['subdomain.class.details'] ?? 'Details' }}
                                </a>
                                <form action="{{ route('booking.select-class-session', ['subdomain' => $host->subdomain, 'session' => $session->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <span class="icon-[tabler--calendar-plus] size-4"></span>
                                        {{ $trans['btn.book_now'] ?? 'Book' }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </section>

    {{-- 2. MEMBERSHIPS & SERVICES SECTION --}}
    @if($servicePlans->isNotEmpty() || isset($membershipPlans) && $membershipPlans->isNotEmpty())
    <section class="mb-12">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold">{{ $trans['subdomain.home.memberships'] ?? 'Memberships' }} & {{ $trans['page.services'] ?? 'Services' }}</h2>
            <p class="text-base-content/60 mt-1">{{ $trans['subdomain.home.join_studio'] ?? 'Join our studio or book a private session' }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Membership Plans --}}
            @if(isset($membershipPlans) && $membershipPlans->isNotEmpty())
                @foreach($membershipPlans as $plan)
                @php
                    $planPrice = $plan->getPriceForCurrency($selectedCurrency);
                    $hasPriceInCurrency = $planPrice !== null;
                @endphp
                <div class="card bg-base-100 shadow-md hover:shadow-xl transition-shadow border border-base-200">
                    <div class="card-body">
                        {{-- Icon & Price --}}
                        <div class="flex items-start justify-between">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-success/10">
                                <span class="icon-[tabler--id-badge-2] size-7 text-success"></span>
                            </div>
                            <div class="text-right">
                                @if($hasPriceInCurrency)
                                <div class="text-2xl font-bold text-success">
                                    {{ $currencySymbol }}{{ number_format($planPrice, 0) }}
                                </div>
                                <div class="text-xs text-base-content/50">/ {{ $plan->interval }}</div>
                                @else
                                <div class="text-sm text-base-content/50">
                                    {{ $trans['subdomain.home.not_available'] ?? 'Not available in' }} {{ $selectedCurrency }}
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Name & Description --}}
                        <h3 class="card-title text-lg mt-4">{{ $plan->name }}</h3>
                        <span class="badge badge-success badge-sm">{{ $trans['page.memberships'] ?? 'Membership' }}</span>
                        @if($plan->description)
                        <p class="text-sm text-base-content/60 line-clamp-2">{{ $plan->description }}</p>
                        @endif

                        {{-- Benefits --}}
                        <div class="text-sm text-base-content/50 mt-2 space-y-1">
                            @if($plan->type === 'unlimited')
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--infinity] size-4 text-success"></span>
                                    {{ $trans['subdomain.home.unlimited_classes'] ?? 'Unlimited Classes' }}
                                </span>
                            @else
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--ticket] size-4"></span>
                                    {{ $plan->credits_per_cycle }} {{ $trans['page.classes'] ?? 'classes' }} {{ $trans['common.per'] ?? 'per' }} {{ $plan->interval }}
                                </span>
                            @endif

                            @if($plan->addon_members > 0)
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--users-plus] size-4 text-primary"></span>
                                    {{ $trans['subdomain.home.bring'] ?? 'Bring' }} +{{ $plan->addon_members }} {{ Str::plural('guest', $plan->addon_members) }}
                                </span>
                            @endif
                        </div>

                        {{-- Free Amenities --}}
                        @if($plan->free_amenities && count($plan->free_amenities) > 0)
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach(array_slice($plan->free_amenities, 0, 3) as $amenity)
                                <span class="badge badge-ghost badge-xs">{{ $amenity }}</span>
                            @endforeach
                            @if(count($plan->free_amenities) > 3)
                                <span class="badge badge-ghost badge-xs">+{{ count($plan->free_amenities) - 3 }} {{ $trans['common.more'] ?? 'more' }}</span>
                            @endif
                        </div>
                        @endif

                        {{-- Button --}}
                        <div class="card-actions mt-4">
                            @if($hasPriceInCurrency)
                            <form action="{{ route('booking.select-membership-plan', ['subdomain' => $host->subdomain, 'plan' => $plan->id]) }}" method="POST" class="w-full">
                                @csrf
                                <input type="hidden" name="currency" value="{{ $selectedCurrency }}">
                                <button type="submit" class="btn btn-success w-full">
                                    <span class="icon-[tabler--shopping-cart] size-5"></span>
                                    {{ $trans['subdomain.home.get_started'] ?? 'Get Started' }}
                                </button>
                            </form>
                            @else
                            <button type="button" class="btn btn-disabled w-full" disabled>
                                {{ $trans['subdomain.home.unavailable'] ?? 'Unavailable' }}
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            @endif

            {{-- Service Plans --}}
            @foreach($servicePlans as $service)
            <div class="card bg-base-100 shadow-md hover:shadow-xl transition-shadow border border-base-200">
                <div class="card-body">
                    {{-- Icon & Price --}}
                    <div class="flex items-start justify-between">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background-color: {{ $service->color ?? '#6366f1' }}15;">
                            <span class="icon-[tabler--sparkles] size-7" style="color: {{ $service->color ?? '#6366f1' }};"></span>
                        </div>
                        @php
                            $servicePrice = $service->getPriceForCurrency($selectedCurrency);
                        @endphp
                        @if($servicePrice)
                        <div class="text-2xl font-bold" style="color: {{ $service->color ?? '#6366f1' }};">
                            {{ $currencySymbol }}{{ number_format($servicePrice, 0) }}
                        </div>
                        @endif
                    </div>

                    {{-- Name & Description --}}
                    <h3 class="card-title text-lg mt-4">{{ $service->name }}</h3>
                    @if($service->description)
                    <p class="text-sm text-base-content/60 line-clamp-2">{{ $service->description }}</p>
                    @endif

                    {{-- Meta --}}
                    <div class="flex items-center gap-4 text-sm text-base-content/50 mt-2">
                        @if($service->duration_minutes)
                        <span class="flex items-center gap-1">
                            <span class="icon-[tabler--clock] size-4"></span>
                            {{ $service->duration_minutes }} {{ $trans['common.minutes'] ?? 'min' }}
                        </span>
                        @endif
                    </div>

                    {{-- Buttons --}}
                    <div class="card-actions mt-4 flex-col gap-2">
                        <form action="{{ route('booking.select-service-plan', ['subdomain' => $host->subdomain, 'servicePlan' => $service->id]) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="btn btn-primary w-full">
                                <span class="icon-[tabler--calendar-plus] size-5"></span>
                                {{ $trans['btn.book_now'] ?? 'Book Now' }}
                            </button>
                        </form>
                        <a href="{{ route('subdomain.service-request.plan', ['subdomain' => $host->subdomain, 'servicePlanId' => $service->id]) }}"
                           class="btn btn-ghost btn-sm w-full">
                            <span class="icon-[tabler--info-circle] size-4"></span>
                            {{ $trans['subdomain.service_request.request_info'] ?? 'Request Info' }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- 3. ABOUT US SECTION --}}
    @if($bookingSettings['about_text'] ?? $host->about)
    <section class="mb-12">
        <div class="card bg-base-100 shadow border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">
                    <span class="icon-[tabler--info-circle] size-6 text-primary"></span>
                    {{ $trans['subdomain.home.about_us'] ?? 'About Us' }}
                </h2>
                <div class="prose prose-sm max-w-none text-base-content/80">
                    {!! $bookingSettings['about_text'] ?? $host->about !!}
                </div>

                {{-- Instructors Preview --}}
                @if(($bookingSettings['show_instructors'] ?? true) && $instructors->isNotEmpty())
                <div class="divider"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold">{{ $trans['subdomain.instructors.meet_team'] ?? 'Meet Our Instructors' }}</h3>
                        <p class="text-sm text-base-content/60">{{ $trans['subdomain.home.expert_guidance'] ?? 'Expert guidance for your fitness journey' }}</p>
                    </div>
                    <a href="{{ route('subdomain.instructors', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                        {{ $trans['btn.view_all'] ?? 'View All' }} <span class="icon-[tabler--arrow-right] size-4 ms-1"></span>
                    </a>
                </div>
                <div class="flex flex-wrap gap-3 mt-4">
                    @foreach($instructors->take(6) as $instructor)
                    @php
                        $initials = collect(explode(' ', $instructor->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
                    @endphp
                    <a href="{{ route('subdomain.instructor', ['subdomain' => $host->subdomain, 'instructor' => $instructor->id]) }}"
                       class="flex items-center gap-2 p-2 pr-4 rounded-full bg-base-200 hover:bg-primary/10 transition-colors">
                        @if($instructor->photo_url && ($bookingSettings['show_instructor_photos'] ?? true))
                        <div class="avatar">
                            <div class="w-10 rounded-full">
                                <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}" />
                            </div>
                        </div>
                        @else
                        <div class="avatar placeholder">
                            <div class="bg-primary text-primary-content w-10 rounded-full">
                                <span class="text-sm">{{ $initials }}</span>
                            </div>
                        </div>
                        @endif
                        <span class="font-medium text-sm">{{ $instructor->name }}</span>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    {{-- 3.5 STUDIO GALLERY SECTION --}}
    @if(isset($galleryImages) && $galleryImages->isNotEmpty())
    <section class="mb-12">
        <div class="card bg-base-100 shadow border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">
                    <span class="icon-[tabler--photo] size-6 text-primary"></span>
                    {{ $trans['subdomain.home.studio_gallery'] ?? 'Studio Gallery' }}
                </h2>

                {{-- Auto-scrolling Gallery --}}
                <div class="gallery-scroll-container relative overflow-hidden">
                    <div class="gallery-scroll-track flex gap-4 animate-scroll" style="width: max-content;">
                        {{-- First set of images --}}
                        @foreach($galleryImages as $image)
                        <div class="gallery-slide flex-shrink-0 w-72 h-48 rounded-xl overflow-hidden shadow-md">
                            <img src="{{ $image->image_url }}"
                                 alt="{{ $image->caption ?? ($trans['subdomain.home.studio_gallery'] ?? 'Studio gallery') }}"
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            @if($image->caption)
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3">
                                <p class="text-white text-sm">{{ $image->caption }}</p>
                            </div>
                            @endif
                        </div>
                        @endforeach
                        {{-- Duplicate for seamless loop --}}
                        @foreach($galleryImages as $image)
                        <div class="gallery-slide flex-shrink-0 w-72 h-48 rounded-xl overflow-hidden shadow-md">
                            <img src="{{ $image->image_url }}"
                                 alt="{{ $image->caption ?? ($trans['subdomain.home.studio_gallery'] ?? 'Studio gallery') }}"
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            @if($image->caption)
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3">
                                <p class="text-white text-sm">{{ $image->caption }}</p>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Gallery Navigation Dots (optional, for accessibility) --}}
                @if($galleryImages->count() > 3)
                <div class="flex justify-center gap-2 mt-4">
                    <button class="btn btn-circle btn-sm btn-ghost gallery-pause-btn" onclick="toggleGalleryScroll()" title="Pause/Play">
                        <span class="icon-[tabler--player-pause] size-4 pause-icon"></span>
                        <span class="icon-[tabler--player-play] size-4 play-icon hidden"></span>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    {{-- 4. LOCATION & ADDRESS SECTION --}}
    @if($defaultLocation ?? null)
    <section class="mb-12">
        <div class="card bg-base-100 shadow border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">
                    <span class="icon-[tabler--map-pin] size-6 text-primary"></span>
                    {{ $trans['subdomain.home.location'] ?? 'Location' }}
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Address Details --}}
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                <span class="icon-[tabler--building] size-6 text-primary"></span>
                            </div>
                            <div>
                                <p class="text-xs text-base-content/50 uppercase font-medium">{{ $trans['field.address'] ?? 'Address' }}</p>
                                @if($defaultLocation->address_line_1)
                                    <p class="font-semibold">{{ $defaultLocation->address_line_1 }}</p>
                                    @if($defaultLocation->address_line_2)
                                        <p class="text-base-content/70">{{ $defaultLocation->address_line_2 }}</p>
                                    @endif
                                    <p class="text-base-content/70">
                                        {{ $defaultLocation->city }}@if($defaultLocation->state), {{ $defaultLocation->state }}@endif @if($defaultLocation->postal_code) {{ $defaultLocation->postal_code }}@endif
                                    </p>
                                    @if($defaultLocation->country)
                                        <p class="text-base-content/60">{{ $defaultLocation->country }}</p>
                                    @endif
                                @else
                                    <p class="font-semibold">{{ $defaultLocation->full_address }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Contact Info --}}
                        @if($host->phone || $host->studio_email)
                        <div class="divider my-2"></div>
                        <div class="space-y-3">
                            @if($host->phone)
                            <a href="tel:{{ $host->phone }}" class="flex items-center gap-3 hover:text-primary transition-colors">
                                <span class="icon-[tabler--phone] size-5 text-base-content/50"></span>
                                <span class="font-medium">{{ $host->phone }}</span>
                            </a>
                            @endif
                            @if($host->studio_email)
                            <a href="mailto:{{ $host->studio_email }}" class="flex items-center gap-3 hover:text-primary transition-colors">
                                <span class="icon-[tabler--mail] size-5 text-base-content/50"></span>
                                <span class="font-medium">{{ $host->studio_email }}</span>
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Map Placeholder / Directions --}}
                    <div class="bg-base-200 rounded-xl p-6 flex flex-col items-center justify-center min-h-[200px]">
                        <span class="icon-[tabler--map-2] size-12 text-base-content/20 mb-3"></span>
                        @php
                            $mapQuery = urlencode($defaultLocation->full_address);
                        @endphp
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}"
                           target="_blank"
                           class="btn btn-primary btn-sm">
                            <span class="icon-[tabler--external-link] size-4"></span>
                            {{ $trans['subdomain.home.get_directions'] ?? 'Get Directions' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- 5. SOCIAL MEDIA SECTION --}}
    @if(($host->show_social_links ?? true) && $host->social_links && count(array_filter((array)$host->social_links)))
    <section class="mb-12">
        <div class="card bg-gradient-to-br from-primary/5 to-secondary/5 border border-base-200">
            <div class="card-body text-center">
                <h2 class="card-title justify-center text-2xl mb-2">
                    {{ $trans['subdomain.home.follow_us'] ?? 'Follow Us' }}
                </h2>
                <p class="text-base-content/60 mb-6">{{ $trans['subdomain.home.stay_connected'] ?? 'Stay connected and follow our journey' }}</p>

                <div class="flex items-center justify-center gap-4 flex-wrap">
                    @if(!empty($host->social_links['instagram']))
                    <a href="{{ $host->social_links['instagram'] }}" target="_blank" rel="noopener"
                       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-base-100 hover:shadow-lg transition-all group min-w-[100px]">
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                            <span class="icon-[tabler--brand-instagram] size-7"></span>
                        </div>
                        <span class="text-sm font-medium">Instagram</span>
                    </a>
                    @endif

                    @if(!empty($host->social_links['facebook']))
                    <a href="{{ $host->social_links['facebook'] }}" target="_blank" rel="noopener"
                       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-base-100 hover:shadow-lg transition-all group min-w-[100px]">
                        <div class="w-14 h-14 rounded-full bg-blue-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                            <span class="icon-[tabler--brand-facebook] size-7"></span>
                        </div>
                        <span class="text-sm font-medium">Facebook</span>
                    </a>
                    @endif

                    @if(!empty($host->social_links['tiktok']))
                    <a href="{{ $host->social_links['tiktok'] }}" target="_blank" rel="noopener"
                       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-base-100 hover:shadow-lg transition-all group min-w-[100px]">
                        <div class="w-14 h-14 rounded-full bg-black flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                            <span class="icon-[tabler--brand-tiktok] size-7"></span>
                        </div>
                        <span class="text-sm font-medium">TikTok</span>
                    </a>
                    @endif

                    @if(!empty($host->social_links['twitter']))
                    <a href="{{ $host->social_links['twitter'] }}" target="_blank" rel="noopener"
                       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-base-100 hover:shadow-lg transition-all group min-w-[100px]">
                        <div class="w-14 h-14 rounded-full bg-black flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                            <span class="icon-[tabler--brand-x] size-7"></span>
                        </div>
                        <span class="text-sm font-medium">X / Twitter</span>
                    </a>
                    @endif

                    @if(!empty($host->social_links['youtube']))
                    <a href="{{ $host->social_links['youtube'] }}" target="_blank" rel="noopener"
                       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-base-100 hover:shadow-lg transition-all group min-w-[100px]">
                        <div class="w-14 h-14 rounded-full bg-red-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                            <span class="icon-[tabler--brand-youtube] size-7"></span>
                        </div>
                        <span class="text-sm font-medium">YouTube</span>
                    </a>
                    @endif

                    @if(!empty($host->social_links['website']))
                    <a href="{{ $host->social_links['website'] }}" target="_blank" rel="noopener"
                       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-base-100 hover:shadow-lg transition-all group min-w-[100px]">
                        <div class="w-14 h-14 rounded-full bg-primary flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                            <span class="icon-[tabler--world] size-7"></span>
                        </div>
                        <span class="text-sm font-medium">Website</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif

</div>

{{-- Toast --}}
@if(session('success'))
<div class="toast toast-top toast-center z-50">
    <div class="alert alert-success"><span class="icon-[tabler--check] size-5"></span> {{ session('success') }}</div>
</div>
<script>setTimeout(() => document.querySelector('.toast')?.remove(), 4000);</script>
@endif

@endsection

@push('scripts')
<script>
// Gallery pause/play toggle
function toggleGalleryScroll() {
    var track = document.querySelector('.gallery-scroll-track');
    var pauseIcon = document.querySelector('.pause-icon');
    var playIcon = document.querySelector('.play-icon');

    if (track) {
        track.classList.toggle('paused');
        if (pauseIcon && playIcon) {
            pauseIcon.classList.toggle('hidden');
            playIcon.classList.toggle('hidden');
        }
    }
}
</script>
@endpush
