@extends('layouts.subdomain')

@section('title', 'Request a Service — ' . $host->studio_name)

@section('content')
@php
    $logoUrl = $host->logo_path ? Storage::disk(config('filesystems.uploads'))->url($host->logo_path) : null;
@endphp

{{-- Header --}}
<div class="bg-base-200 border-b border-base-300">
    <div class="max-w-3xl mx-auto px-4 py-6">
        <div class="flex items-center gap-4">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $host->studio_name }}" class="h-10 w-auto">
            @else
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--building-community] size-5 text-primary"></span>
                </div>
            @endif
            <div>
                <h1 class="font-bold text-lg text-base-content">{{ $host->studio_name }}</h1>
                <p class="text-sm text-base-content/60">Request a Service</p>
            </div>
        </div>
    </div>
</div>

{{-- Main Content --}}
<div class="max-w-3xl mx-auto w-full px-4 py-8">

    {{-- Back Link --}}
    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}"
       class="inline-flex items-center gap-1 text-sm text-base-content/60 hover:text-primary transition-colors mb-6">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        Back to Home
    </a>

    {{-- Form Card --}}
    <div class="card bg-base-100 border border-base-200">
        <div class="card-body p-6 md:p-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-base-content">Request a Service</h2>
                <p class="text-base-content/60 mt-1">
                    Fill in your details below and we'll get back to you to schedule your appointment.
                </p>
            </div>

            @if($member ?? false)
                <div class="alert alert-info mb-6">
                    <span class="icon-[tabler--user-check] size-5"></span>
                    <span>Logged in as <strong>{{ $member->first_name }} {{ $member->last_name }}</strong>. Your information has been pre-filled.</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error mb-6">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error mb-6">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('subdomain.service-request.store', ['subdomain' => $host->subdomain]) }}" method="POST" class="space-y-6">
                @csrf

                {{-- Service Selection --}}
                <div class="space-y-4">
                    <h3 class="font-semibold text-base-content flex items-center gap-2">
                        <span class="icon-[tabler--sparkles] size-5 text-primary"></span>
                        Service Selection
                    </h3>

                    <div>
                        <label for="service_plan_id" class="label">
                            <span class="label-text font-medium">Service <span class="text-error">*</span></span>
                        </label>
                        <select id="service_plan_id" name="service_plan_id"
                                class="select select-bordered w-full @error('service_plan_id') select-error @enderror" required>
                            <option value="">Select a service...</option>
                            @foreach($servicePlans as $plan)
                                <option value="{{ $plan->id }}"
                                    {{ old('service_plan_id', $selectedServicePlan?->id) == $plan->id ? 'selected' : '' }}
                                    data-price="{{ $plan->price }}"
                                    data-duration="{{ $plan->duration_minutes }}">
                                    {{ $plan->name }}
                                    @if($plan->price) — ${{ number_format($plan->price, 0) }}@endif
                                    @if($plan->duration_minutes) ({{ $plan->duration_minutes }} min)@endif
                                </option>
                            @endforeach
                        </select>
                        @error('service_plan_id')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    @if($selectedServicePlan)
                        <div class="alert bg-primary/10 border-primary/20">
                            <span class="icon-[tabler--info-circle] size-5 text-primary"></span>
                            <div>
                                <span class="font-medium">{{ $selectedServicePlan->name }}</span>
                                @if($selectedServicePlan->description)
                                    <p class="text-sm mt-1">{{ $selectedServicePlan->description }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Contact Information --}}
                <div class="space-y-4">
                    <h3 class="font-semibold text-base-content flex items-center gap-2">
                        <span class="icon-[tabler--user] size-5 text-primary"></span>
                        Your Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label for="name" class="label">
                                <span class="label-text font-medium">Full Name <span class="text-error">*</span></span>
                            </label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name', $member ? $member->first_name . ' ' . $member->last_name : '') }}"
                                   class="input input-bordered w-full @error('name') input-error @enderror"
                                   placeholder="Your name" required>
                            @error('name')
                                <span class="text-error text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="label">
                                <span class="label-text font-medium">Email <span class="text-error">*</span></span>
                            </label>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email', $member?->email) }}"
                                   class="input input-bordered w-full @error('email') input-error @enderror"
                                   placeholder="your@email.com" required {{ $member ? 'readonly' : '' }}>
                            @error('email')
                                <span class="text-error text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="label">
                                <span class="label-text font-medium">Phone Number</span>
                            </label>
                            <input type="tel" id="phone" name="phone"
                                   value="{{ old('phone', $member?->phone) }}"
                                   class="input input-bordered w-full @error('phone') input-error @enderror"
                                   placeholder="(555) 123-4567">
                            @error('phone')
                                <span class="text-error text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Preferred Date & Time --}}
                <div class="space-y-4">
                    <h3 class="font-semibold text-base-content flex items-center gap-2">
                        <span class="icon-[tabler--calendar] size-5 text-primary"></span>
                        Preferred Schedule
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="preferred_date" class="label">
                                <span class="label-text font-medium">Preferred Date</span>
                            </label>
                            <input type="date" id="preferred_date" name="preferred_date"
                                   value="{{ old('preferred_date') }}"
                                   min="{{ date('Y-m-d') }}"
                                   class="input input-bordered w-full @error('preferred_date') input-error @enderror">
                            @error('preferred_date')
                                <span class="text-error text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="preferred_time" class="label">
                                <span class="label-text font-medium">Preferred Time</span>
                            </label>
                            <input type="time" id="preferred_time" name="preferred_time"
                                   value="{{ old('preferred_time') }}"
                                   class="input input-bordered w-full @error('preferred_time') input-error @enderror">
                            @error('preferred_time')
                                <span class="text-error text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Message --}}
                <div class="space-y-4">
                    <h3 class="font-semibold text-base-content flex items-center gap-2">
                        <span class="icon-[tabler--message] size-5 text-primary"></span>
                        Additional Information
                    </h3>

                    <div>
                        <label for="message" class="label">
                            <span class="label-text font-medium">Message (Optional)</span>
                        </label>
                        <textarea id="message" name="message" rows="4"
                                  class="textarea textarea-bordered w-full @error('message') textarea-error @enderror"
                                  placeholder="Any questions, special requests, or additional information...">{{ old('message') }}</textarea>
                        @error('message')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Submit --}}
                <div class="pt-4">
                    <button type="submit" class="btn btn-primary w-full md:w-auto">
                        <span class="icon-[tabler--send] size-5"></span>
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
