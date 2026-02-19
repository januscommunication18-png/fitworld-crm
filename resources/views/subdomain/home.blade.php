@extends('layouts.subdomain')

@section('title', $host->studio_name . ' — Book a Class')

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

            {{-- Tagline/About --}}
            @if($bookingSettings['about_text'] ?? $host->about)
            <p class="text-white/90 text-lg md:text-xl max-w-2xl mx-auto mb-6">
                {{ Str::limit($bookingSettings['about_text'] ?? $host->about, 150) }}
            </p>
            @endif

            {{-- Location --}}
            @if(($host->show_address ?? true) && $host->address)
            <p class="text-white/80 text-base flex items-center justify-center gap-2">
                <span class="icon-[tabler--map-pin] size-5"></span>
                {{ is_array($host->address) ? ($host->address['city'] ?? $host->address['street'] ?? '') : $host->address }}
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
                <span class="icon-[tabler--home] size-4 me-1"></span> Home
            </a>
            <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}" class="tab">
                <span class="icon-[tabler--calendar] size-4 me-1"></span> Schedule
            </a>
            @if($bookingSettings['show_instructors'] ?? true)
            <a href="{{ route('subdomain.instructors', ['subdomain' => $host->subdomain]) }}" class="tab">
                <span class="icon-[tabler--users] size-4 me-1"></span> Instructors
            </a>
            @endif
        </div>
    </div>

    {{-- 1. CLASSES SECTION --}}
    <section class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold">Upcoming Classes</h2>
                <p class="text-base-content/60 mt-1">Join our group fitness sessions</p>
            </div>
            <a href="{{ route('subdomain.schedule', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                View All <span class="icon-[tabler--arrow-right] size-4 ms-1"></span>
            </a>
        </div>

        @if($upcomingSessions->isEmpty())
        <div class="card bg-base-100 shadow border border-base-200">
            <div class="card-body items-center text-center py-16">
                <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-4">
                    <span class="icon-[tabler--calendar-off] size-10 text-base-content/30"></span>
                </div>
                <h3 class="text-lg font-semibold">No Upcoming Classes</h3>
                <p class="text-base-content/60">Check back soon for new classes!</p>
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
                            <span class="badge badge-error">Full</span>
                            <a href="{{ route('subdomain.class-request.session', ['subdomain' => $host->subdomain, 'sessionId' => $session->id, 'waitlist' => 1]) }}"
                               class="btn btn-warning btn-sm">
                                Join Waitlist
                            </a>
                        @else
                            @if($spotsLeft <= 3)
                                <span class="badge badge-warning">{{ $spotsLeft }} left</span>
                            @else
                                <span class="text-xs text-base-content/40">{{ $spotsLeft }} spots</span>
                            @endif
                            <div class="flex items-center gap-2">
                                <a href="{{ route('subdomain.class', ['subdomain' => $host->subdomain, 'classSession' => $session->id]) }}"
                                   class="btn btn-ghost btn-sm">
                                    <span class="icon-[tabler--info-circle] size-4"></span>
                                    Details
                                </a>
                                <form action="{{ route('booking.select-class-session', ['subdomain' => $host->subdomain, 'session' => $session->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <span class="icon-[tabler--calendar-plus] size-4"></span>
                                        Book
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
            <h2 class="text-2xl font-bold">Memberships & Services</h2>
            <p class="text-base-content/60 mt-1">Join our studio or book a private session</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Membership Plans --}}
            @if(isset($membershipPlans) && $membershipPlans->isNotEmpty())
                @foreach($membershipPlans as $plan)
                <div class="card bg-base-100 shadow-md hover:shadow-xl transition-shadow border border-base-200">
                    <div class="card-body">
                        {{-- Icon & Price --}}
                        <div class="flex items-start justify-between">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-success/10">
                                <span class="icon-[tabler--id-badge-2] size-7 text-success"></span>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-success">
                                    ${{ number_format($plan->price, 0) }}
                                </div>
                                <div class="text-xs text-base-content/50">/ {{ $plan->interval }}</div>
                            </div>
                        </div>

                        {{-- Name & Description --}}
                        <h3 class="card-title text-lg mt-4">{{ $plan->name }}</h3>
                        <span class="badge badge-success badge-sm">Membership</span>
                        @if($plan->description)
                        <p class="text-sm text-base-content/60 line-clamp-2">{{ $plan->description }}</p>
                        @endif

                        {{-- Benefits --}}
                        <div class="text-sm text-base-content/50 mt-2">
                            @if($plan->type === 'unlimited')
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--infinity] size-4 text-success"></span>
                                    Unlimited Classes
                                </span>
                            @else
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--ticket] size-4"></span>
                                    {{ $plan->credits_per_cycle }} classes per {{ $plan->interval }}
                                </span>
                            @endif
                        </div>

                        {{-- Button --}}
                        <div class="card-actions mt-4">
                            <form action="{{ route('booking.select-membership-plan', ['subdomain' => $host->subdomain, 'plan' => $plan->id]) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="btn btn-success w-full">
                                    <span class="icon-[tabler--shopping-cart] size-5"></span>
                                    Get Started
                                </button>
                            </form>
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
                        @if($service->price)
                        <div class="text-2xl font-bold" style="color: {{ $service->color ?? '#6366f1' }};">
                            ${{ number_format($service->price, 0) }}
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
                            {{ $service->duration_minutes }} min
                        </span>
                        @endif
                    </div>

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
    </section>
    @endif

    {{-- 3. ABOUT US SECTION --}}
    @if($bookingSettings['about_text'] ?? $host->about)
    <section class="mb-12">
        <div class="card bg-base-100 shadow border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">
                    <span class="icon-[tabler--info-circle] size-6 text-primary"></span>
                    About Us
                </h2>
                <p class="text-base-content/80 leading-relaxed">
                    {{ $bookingSettings['about_text'] ?? $host->about }}
                </p>

                {{-- Instructors Preview --}}
                @if(($bookingSettings['show_instructors'] ?? true) && $instructors->isNotEmpty())
                <div class="divider"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold">Meet Our Instructors</h3>
                        <p class="text-sm text-base-content/60">Expert guidance for your fitness journey</p>
                    </div>
                    <a href="{{ route('subdomain.instructors', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                        View All <span class="icon-[tabler--arrow-right] size-4 ms-1"></span>
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

    {{-- 4. LOCATION & ADDRESS SECTION --}}
    @if($host->address)
    <section class="mb-12">
        <div class="card bg-base-100 shadow border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">
                    <span class="icon-[tabler--map-pin] size-6 text-primary"></span>
                    Location
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Address Details --}}
                    <div class="space-y-4">
                        @php
                            $address = $host->address;
                            $isArrayAddress = is_array($address);
                        @endphp

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                <span class="icon-[tabler--building] size-6 text-primary"></span>
                            </div>
                            <div>
                                <p class="text-xs text-base-content/50 uppercase font-medium">Address</p>
                                @if($isArrayAddress)
                                    <p class="font-semibold">{{ $address['street'] ?? '' }}</p>
                                    <p class="text-base-content/70">
                                        {{ $address['city'] ?? '' }}@if(!empty($address['state'])), {{ $address['state'] }}@endif @if(!empty($address['zip'])){{ $address['zip'] }}@endif
                                    </p>
                                    @if(!empty($address['country']))
                                    <p class="text-base-content/60">{{ $address['country'] }}</p>
                                    @endif
                                @else
                                    <p class="font-semibold">{{ $address }}</p>
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
                            $mapQuery = $isArrayAddress
                                ? urlencode(implode(', ', array_filter([$address['street'] ?? '', $address['city'] ?? '', $address['state'] ?? '', $address['zip'] ?? ''])))
                                : urlencode($address);
                        @endphp
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}"
                           target="_blank"
                           class="btn btn-primary btn-sm">
                            <span class="icon-[tabler--external-link] size-4"></span>
                            Get Directions
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
                    Follow Us
                </h2>
                <p class="text-base-content/60 mb-6">Stay connected and follow our journey</p>

                <div class="flex items-center justify-center gap-4 flex-wrap">
                    @foreach(['instagram' => ['brand-instagram', 'Instagram', 'bg-gradient-to-br from-purple-500 to-pink-500'], 'facebook' => ['brand-facebook', 'Facebook', 'bg-blue-600'], 'tiktok' => ['brand-tiktok', 'TikTok', 'bg-black'], 'twitter' => ['brand-x', 'X / Twitter', 'bg-black'], 'youtube' => ['brand-youtube', 'YouTube', 'bg-red-600'], 'website' => ['world', 'Website', 'bg-primary']] as $key => [$icon, $label, $bgClass])
                        @if(!empty($host->social_links[$key]))
                        <a href="{{ $host->social_links[$key] }}" target="_blank" rel="noopener"
                           class="flex flex-col items-center gap-2 p-4 rounded-xl bg-base-100 hover:shadow-lg transition-all group min-w-[100px]">
                            <div class="w-14 h-14 rounded-full {{ $bgClass }} flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                <span class="icon-[tabler--{{ $icon }}] size-7"></span>
                            </div>
                            <span class="text-sm font-medium">{{ $label }}</span>
                        </a>
                        @endif
                    @endforeach
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
