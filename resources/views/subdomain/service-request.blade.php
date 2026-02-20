@extends('layouts.subdomain')

@section('title', 'Request Booking — ' . $host->studio_name)

@php
    $selectedCurrency = session("currency_{$host->id}", $host->default_currency ?? 'USD');
    $currencySymbol = \App\Models\MembershipPlan::getCurrencySymbol($selectedCurrency);
@endphp

@section('content')

@include('subdomain.partials.navbar')

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
                <h2 class="text-2xl font-bold text-base-content">Request a Booking</h2>
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

                {{-- Booking Type Selection --}}
                <div class="space-y-4">
                    <h3 class="font-semibold text-base-content flex items-center gap-2">
                        <span class="icon-[tabler--category] size-5 text-primary"></span>
                        What would you like to book?
                    </h3>

                    {{-- Type Tabs --}}
                    <div class="tabs tabs-boxed bg-base-200 p-1 w-fit">
                        <button type="button" id="tab-service" class="tab tab-active" onclick="switchTab('service')">
                            <span class="icon-[tabler--sparkles] size-4 mr-1"></span>
                            Service
                        </button>
                        <button type="button" id="tab-class" class="tab" onclick="switchTab('class')">
                            <span class="icon-[tabler--yoga] size-4 mr-1"></span>
                            Class
                        </button>
                    </div>

                    <input type="hidden" name="booking_type" id="booking_type" value="{{ old('booking_type', 'service') }}">

                    {{-- Service Dropdown --}}
                    <div id="service-section" class="{{ old('booking_type') === 'class' ? 'hidden' : '' }}">
                        <label class="label">
                            <span class="label-text font-medium">Service <span class="text-error">*</span></span>
                        </label>
                        <div class="relative w-full" id="service-dropdown">
                            <div class="select select-bordered w-full flex items-center justify-between cursor-pointer" id="service-display" onclick="toggleDropdown('service')">
                                <span id="service-text">{{ $selectedServicePlan ? $selectedServicePlan->name : 'Search or select a service...' }}</span>
                                <span class="icon-[tabler--chevron-down] size-4"></span>
                            </div>
                            <div id="service-dropdown-content" class="hidden absolute left-0 right-0 bg-base-100 rounded-box shadow-lg border border-base-200 w-full mt-1 z-50 max-h-80 overflow-hidden">
                                <div class="p-2 border-b border-base-200">
                                    <input type="text" id="service-search" placeholder="Search services..."
                                           class="input input-bordered input-sm w-full"
                                           onkeyup="filterServices()">
                                </div>
                                <ul class="menu p-2 max-h-60 overflow-y-auto" id="service-list">
                                    @foreach($servicePlans as $plan)
                                    <li>
                                        <a href="javascript:void(0)"
                                           onclick="selectService({{ $plan->id }}, '{{ addslashes($plan->name) }}', {{ $plan->price ?? 0 }}, {{ $plan->duration_minutes ?? 0 }})"
                                           class="service-item flex justify-between items-center"
                                           data-name="{{ strtolower($plan->name) }}">
                                            <span>{{ $plan->name }}</span>
                                            <span class="text-sm text-base-content/60">
                                                @php $servicePrice = $plan->getPriceForCurrency($selectedCurrency); @endphp
                                                @if($servicePrice){{ $currencySymbol }}{{ number_format($servicePrice, 0) }}@endif
                                                @if($plan->duration_minutes) · {{ $plan->duration_minutes }}min @endif
                                            </span>
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <input type="hidden" name="service_plan_id" id="service_plan_id" value="{{ old('service_plan_id', $selectedServicePlan?->id) }}">
                        @error('service_plan_id')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Class Dropdown --}}
                    <div id="class-section" class="{{ old('booking_type') !== 'class' ? 'hidden' : '' }}">
                        <label class="label">
                            <span class="label-text font-medium">Class <span class="text-error">*</span></span>
                        </label>
                        <div class="relative w-full" id="class-dropdown">
                            <div class="select select-bordered w-full flex items-center justify-between cursor-pointer" id="class-display" onclick="toggleDropdown('class')">
                                <span id="class-text">Search or select a class...</span>
                                <span class="icon-[tabler--chevron-down] size-4"></span>
                            </div>
                            <div id="class-dropdown-content" class="hidden absolute left-0 right-0 bg-base-100 rounded-box shadow-lg border border-base-200 w-full mt-1 z-50 max-h-80 overflow-hidden">
                                <div class="p-2 border-b border-base-200">
                                    <input type="text" id="class-search" placeholder="Search classes..."
                                           class="input input-bordered input-sm w-full"
                                           onkeyup="filterClasses()">
                                </div>
                                <ul class="menu p-2 max-h-60 overflow-y-auto" id="class-list">
                                    @foreach($classPlans ?? [] as $plan)
                                    <li>
                                        <a href="javascript:void(0)"
                                           onclick="selectClass({{ $plan->id }}, '{{ addslashes($plan->name) }}')"
                                           class="class-item"
                                           data-name="{{ strtolower($plan->name) }}">
                                            <span>{{ $plan->name }}</span>
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <input type="hidden" name="class_plan_id" id="class_plan_id" value="{{ old('class_plan_id') }}">
                        @error('class_plan_id')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Selected Item Info --}}
                    <div id="selected-info" class="alert bg-primary/10 border-primary/20 {{ !$selectedServicePlan ? 'hidden' : '' }}">
                        <span class="icon-[tabler--info-circle] size-5 text-primary"></span>
                        <div>
                            <span class="font-medium" id="selected-name">{{ $selectedServicePlan?->name }}</span>
                            <p class="text-sm mt-1" id="selected-details">
                                @if($selectedServicePlan?->description){{ $selectedServicePlan->description }}@endif
                            </p>
                        </div>
                    </div>
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

<script>
function switchTab(type) {
    const serviceTab = document.getElementById('tab-service');
    const classTab = document.getElementById('tab-class');
    const serviceSection = document.getElementById('service-section');
    const classSection = document.getElementById('class-section');
    const bookingType = document.getElementById('booking_type');

    // Close any open dropdowns
    closeAllDropdowns();

    if (type === 'service') {
        serviceTab.classList.add('tab-active');
        classTab.classList.remove('tab-active');
        serviceSection.classList.remove('hidden');
        classSection.classList.add('hidden');
        bookingType.value = 'service';
    } else {
        classTab.classList.add('tab-active');
        serviceTab.classList.remove('tab-active');
        classSection.classList.remove('hidden');
        serviceSection.classList.add('hidden');
        bookingType.value = 'class';
    }

    // Hide selected info when switching
    document.getElementById('selected-info').classList.add('hidden');
}

function toggleDropdown(type) {
    const dropdown = document.getElementById(type + '-dropdown-content');
    const isHidden = dropdown.classList.contains('hidden');

    // Close all dropdowns first
    closeAllDropdowns();

    // Toggle this one
    if (isHidden) {
        dropdown.classList.remove('hidden');
        // Focus search input
        setTimeout(() => {
            document.getElementById(type + '-search').focus();
        }, 50);
    }
}

function closeAllDropdowns() {
    document.getElementById('service-dropdown-content')?.classList.add('hidden');
    document.getElementById('class-dropdown-content')?.classList.add('hidden');
}

function selectService(id, name, price, duration) {
    document.getElementById('service_plan_id').value = id;
    document.getElementById('service-text').textContent = name;

    // Show selected info
    const infoDiv = document.getElementById('selected-info');
    document.getElementById('selected-name').textContent = name;
    let details = '';
    if (price > 0) details += '$' + price;
    if (duration > 0) details += (details ? ' · ' : '') + duration + ' minutes';
    document.getElementById('selected-details').textContent = details;
    infoDiv.classList.remove('hidden');

    // Close dropdown
    closeAllDropdowns();
}

function selectClass(id, name) {
    document.getElementById('class_plan_id').value = id;
    document.getElementById('class-text').textContent = name;

    // Show selected info
    const infoDiv = document.getElementById('selected-info');
    document.getElementById('selected-name').textContent = name;
    document.getElementById('selected-details').textContent = 'Group fitness class';
    infoDiv.classList.remove('hidden');

    // Close dropdown
    closeAllDropdowns();
}

function filterServices() {
    const search = document.getElementById('service-search').value.toLowerCase();
    const items = document.querySelectorAll('.service-item');

    items.forEach(item => {
        const name = item.getAttribute('data-name');
        if (name.includes(search)) {
            item.parentElement.style.display = '';
        } else {
            item.parentElement.style.display = 'none';
        }
    });
}

function filterClasses() {
    const search = document.getElementById('class-search').value.toLowerCase();
    const items = document.querySelectorAll('.class-item');

    items.forEach(item => {
        const name = item.getAttribute('data-name');
        if (name.includes(search)) {
            item.parentElement.style.display = '';
        } else {
            item.parentElement.style.display = 'none';
        }
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    const serviceDropdown = document.getElementById('service-dropdown');
    const classDropdown = document.getElementById('class-dropdown');

    if (serviceDropdown && !serviceDropdown.contains(e.target)) {
        document.getElementById('service-dropdown-content')?.classList.add('hidden');
    }
    if (classDropdown && !classDropdown.contains(e.target)) {
        document.getElementById('class-dropdown-content')?.classList.add('hidden');
    }
});

// Initialize based on old input
document.addEventListener('DOMContentLoaded', function() {
    const bookingType = '{{ old('booking_type', 'service') }}';
    if (bookingType === 'class') {
        switchTab('class');
    }
});
</script>
@endsection
