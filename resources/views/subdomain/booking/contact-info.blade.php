@extends('layouts.subdomain')

@section('title', 'Complete Your Booking — ' . $host->studio_name)

@section('content')
@php
    $item = $bookingState['selected_item'] ?? [];
@endphp

<div class="min-h-screen flex flex-col bg-gradient-to-br from-base-200 via-base-100 to-base-200">
    {{-- Header --}}
    <nav class="bg-base-100/80 backdrop-blur-sm border-b border-base-200 sticky top-0 z-50" style="height: 70px;">
        <div class="container-fixed h-full">
            <div class="flex items-center justify-between h-full">
                {{-- Logo --}}
                <div class="flex items-center">
                    @if($host->logo_url)
                        <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center">
                            <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-10 w-auto max-w-[160px] object-contain">
                        </a>
                    @else
                        <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center">
                                <span class="text-base font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                            </div>
                            <span class="font-bold text-base hidden sm:inline">{{ $host->studio_name }}</span>
                        </a>
                    @endif
                </div>

                {{-- Progress Indicator --}}
                <div class="hidden md:flex items-center gap-2 text-sm">
                    <span class="flex items-center gap-1.5 text-primary font-medium">
                        <span class="w-6 h-6 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs font-bold">1</span>
                        Details
                    </span>
                    <span class="icon-[tabler--chevron-right] size-4 text-base-content/30"></span>
                    <span class="flex items-center gap-1.5 text-base-content/50">
                        <span class="w-6 h-6 rounded-full bg-base-300 flex items-center justify-center text-xs font-bold">2</span>
                        Payment
                    </span>
                    <span class="icon-[tabler--chevron-right] size-4 text-base-content/30"></span>
                    <span class="flex items-center gap-1.5 text-base-content/50">
                        <span class="w-6 h-6 rounded-full bg-base-300 flex items-center justify-center text-xs font-bold">3</span>
                        Done
                    </span>
                </div>

                {{-- Change Selection --}}
                <a href="{{ route('member.portal.booking', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--arrow-left] size-4"></span>
                    <span class="hidden sm:inline">Change</span>
                </a>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <div class="flex-1 py-6 md:py-10">
        <div class="container-fixed">
            <div class="max-w-4xl mx-auto">

                {{-- What You're Booking --}}
                <div class="card bg-gradient-to-r from-primary/10 via-primary/5 to-transparent border border-primary/20 mb-6">
                    <div class="card-body py-4 md:py-5">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-primary/20 flex items-center justify-center shrink-0">
                                @if(($item['type'] ?? '') === 'class_session')
                                    <span class="icon-[tabler--yoga] size-7 text-primary"></span>
                                @elseif(($item['type'] ?? '') === 'service_slot' || ($item['type'] ?? '') === 'service_plan')
                                    <span class="icon-[tabler--sparkles] size-7 text-primary"></span>
                                @elseif(($item['type'] ?? '') === 'membership_plan')
                                    <span class="icon-[tabler--id-badge-2] size-7 text-primary"></span>
                                @else
                                    <span class="icon-[tabler--calendar-check] size-7 text-primary"></span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h2 class="text-xl font-bold text-base-content">{{ $item['name'] ?? 'Your Selection' }}</h2>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-sm text-base-content/70">
                                    @if(($item['type'] ?? '') === 'membership_plan')
                                        {{-- Membership Plan Info --}}
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--id-badge-2] size-4"></span>
                                            Membership Plan
                                        </span>
                                        @if(!empty($item['billing_period']))
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--refresh] size-4"></span>
                                                Billed {{ $item['billing_period'] }}
                                            </span>
                                        @endif
                                    @elseif(($item['type'] ?? '') === 'class_pack')
                                        {{-- Class Pack Info --}}
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--ticket] size-4"></span>
                                            Class Pack
                                        </span>
                                        @if(!empty($item['class_count']))
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--check] size-4"></span>
                                                {{ $item['class_count'] }} classes
                                            </span>
                                        @endif
                                        @if(!empty($item['validity_days']))
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--clock] size-4"></span>
                                                Valid for {{ $item['validity_days'] }} days
                                            </span>
                                        @endif
                                    @elseif(($item['type'] ?? '') === 'service_plan')
                                        {{-- Service Plan Info (no slot) --}}
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--sparkles] size-4"></span>
                                            Service
                                        </span>
                                        @if(!empty($item['duration']))
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--clock] size-4"></span>
                                                {{ $item['duration'] }} minutes
                                            </span>
                                        @endif
                                    @else
                                        {{-- Class/Service Booking Info --}}
                                        @if(!empty($item['datetime']))
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--calendar] size-4"></span>
                                                {{ $item['datetime'] }}
                                            </span>
                                        @endif
                                        @if(!empty($item['instructor']))
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--user] size-4"></span>
                                                {{ $item['instructor'] }}
                                            </span>
                                        @endif
                                        @if(!empty($item['location']))
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--map-pin] size-4"></span>
                                                {{ $item['location'] }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-bold text-primary">${{ number_format($item['price'] ?? 0, 2) }}</span>
                                @if(($item['type'] ?? '') === 'membership_plan')
                                    <div class="text-sm text-base-content/60">{{ $item['billing_period'] ?? 'per month' }}</div>
                                @endif
                                @if(!empty($item['is_waitlist']))
                                    <div class="badge badge-warning badge-sm mt-1">Waitlist</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    {{-- Form --}}
                    <div class="lg:col-span-3">
                        <div class="card bg-base-100 shadow-xl">
                            <div class="card-body">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                                        <span class="icon-[tabler--user-circle] size-5 text-primary"></span>
                                    </div>
                                    <div>
                                        <h1 class="text-lg font-bold">Your Details</h1>
                                        <p class="text-sm text-base-content/60">We'll use this to confirm your booking</p>
                                    </div>
                                </div>

                                @if($errors->any())
                                    <div class="alert alert-error mb-4">
                                        <span class="icon-[tabler--alert-circle] size-5"></span>
                                        <span>{{ $errors->first() }}</span>
                                    </div>
                                @endif

                                @if($isLoggedIn)
                                    <div class="alert bg-success/10 text-success border-success/20 mb-4">
                                        <span class="icon-[tabler--check] size-5"></span>
                                        <span>Logged in as <strong>{{ $prefillData['first_name'] ?? '' }} {{ $prefillData['last_name'] ?? '' }}</strong></span>
                                    </div>
                                @endif

                                <form action="{{ route('booking.contact.save', ['subdomain' => $host->subdomain]) }}" method="POST">
                                    @csrf

                                    <div class="space-y-4">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div class="form-control">
                                                <label class="label" for="first_name">
                                                    <span class="label-text font-medium">First Name <span class="text-error">*</span></span>
                                                </label>
                                                <input type="text" id="first_name" name="first_name"
                                                       value="{{ old('first_name', $prefillData['first_name'] ?? '') }}"
                                                       required
                                                       placeholder="John"
                                                       class="input input-bordered w-full focus:input-primary @error('first_name') input-error @enderror">
                                            </div>
                                            <div class="form-control">
                                                <label class="label" for="last_name">
                                                    <span class="label-text font-medium">Last Name <span class="text-error">*</span></span>
                                                </label>
                                                <input type="text" id="last_name" name="last_name"
                                                       value="{{ old('last_name', $prefillData['last_name'] ?? '') }}"
                                                       required
                                                       placeholder="Doe"
                                                       class="input input-bordered w-full focus:input-primary @error('last_name') input-error @enderror">
                                            </div>
                                        </div>

                                        <div class="form-control">
                                            <label class="label" for="email">
                                                <span class="label-text font-medium">Email <span class="text-error">*</span></span>
                                            </label>
                                            <div class="relative">
                                                <span class="icon-[tabler--mail] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                                <input type="email" id="email" name="email"
                                                       value="{{ old('email', $prefillData['email'] ?? '') }}"
                                                       required
                                                       placeholder="john@example.com"
                                                       class="input input-bordered w-full pl-10 focus:input-primary @error('email') input-error @enderror">
                                            </div>
                                            <label class="label">
                                                <span class="label-text-alt text-base-content/50">Confirmation & receipt will be sent here</span>
                                            </label>
                                        </div>

                                        <div class="form-control">
                                            <label class="label" for="phone">
                                                <span class="label-text font-medium">Phone <span class="text-error">*</span></span>
                                            </label>
                                            <div class="relative">
                                                <span class="icon-[tabler--phone] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                                <input type="tel" id="phone" name="phone"
                                                       value="{{ old('phone', $prefillData['phone'] ?? '') }}"
                                                       required
                                                       placeholder="+1 (555) 123-4567"
                                                       class="input input-bordered w-full pl-10 focus:input-primary @error('phone') input-error @enderror">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="divider"></div>

                                    <button type="submit" class="btn btn-primary btn-lg w-full gap-2">
                                        Continue to Payment
                                        <span class="icon-[tabler--arrow-right] size-5"></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Side Info --}}
                    <div class="lg:col-span-2 space-y-4">
                        {{-- Studio Info --}}
                        <div class="card bg-base-100 shadow-lg">
                            <div class="card-body">
                                <div class="flex items-center gap-3">
                                    @if($host->logo_url)
                                        <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-12 w-12 object-contain rounded-lg">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-primary flex items-center justify-center">
                                            <span class="text-xl font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <h3 class="font-bold">{{ $host->studio_name }}</h3>
                                        @if($host->address)
                                            <p class="text-sm text-base-content/60">{{ $host->address }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Security Badge --}}
                        <div class="card bg-base-100 shadow-lg">
                            <div class="card-body py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-success/10 flex items-center justify-center">
                                        <span class="icon-[tabler--shield-check] size-5 text-success"></span>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-sm">Secure Booking</h4>
                                        <p class="text-xs text-base-content/60">Your information is protected</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Help --}}
                        <div class="card bg-base-100 shadow-lg">
                            <div class="card-body py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-info/10 flex items-center justify-center">
                                        <span class="icon-[tabler--help-circle] size-5 text-info"></span>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-sm">Need Help?</h4>
                                        @if($host->email)
                                            <a href="mailto:{{ $host->email }}" class="text-xs text-primary hover:underline">{{ $host->email }}</a>
                                        @else
                                            <p class="text-xs text-base-content/60">Contact the studio</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
