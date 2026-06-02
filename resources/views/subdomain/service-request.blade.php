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

                {{-- Offering Selection --}}
                <div class="space-y-4">
                    <h3 class="font-semibold text-base-content flex items-center gap-2">
                        <span class="icon-[tabler--category] size-5 text-primary"></span>
                        What would you like to ask about?
                    </h3>

                    @php $selectedAlias = old('requested_type_alias', $selectedAlias ?? null); @endphp

                    @if(!empty($offeringsByType))
                        @php $selectedOfferingId = old('requested_offering_id', $selectedOfferingId ?? null); @endphp

                        {{-- Step 1: Category --}}
                        <div>
                            <label for="requested_type_alias" class="label">
                                <span class="label-text font-medium">Category <span class="text-error">*</span></span>
                            </label>
                            <select id="requested_type_alias" name="requested_type_alias"
                                    class="select select-bordered w-full @error('requested_type_alias') select-error @enderror" required>
                                <option value="">Select a category...</option>
                                @foreach($offeringsByType as $alias => $group)
                                    <option value="{{ $alias }}" {{ $selectedAlias === $alias ? 'selected' : '' }}>{{ $group['label'] }}</option>
                                @endforeach
                            </select>
                            @error('requested_type_alias')
                                <span class="text-error text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Step 2: Per-category offering picker. Only the one matching the
                              chosen category is shown; its inner select writes the value into
                              the single hidden #requested_offering_id input the form posts. --}}
                        <input type="hidden" name="requested_offering_id" id="requested_offering_id" value="{{ $selectedOfferingId }}">
                        <div id="offering-pickers" class="{{ $selectedAlias ? '' : 'hidden' }}">
                            @foreach($offeringsByType as $alias => $group)
                                @php $isActiveAlias = $selectedAlias === $alias; @endphp
                                <div data-offering-picker="{{ $alias }}" class="{{ $isActiveAlias ? '' : 'hidden' }}">
                                    <label class="label">
                                        <span class="label-text font-medium">{{ $group['label'] }} <span class="text-error">*</span></span>
                                    </label>
                                    <select data-inner-offering
                                            class="select select-bordered w-full @error('requested_offering_id') select-error @enderror">
                                        <option value="">Select a {{ strtolower($group['label']) }}...</option>
                                        @foreach($group['items'] as $item)
                                            <option value="{{ $item->id }}" {{ $isActiveAlias && (string) $selectedOfferingId === (string) $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                            @error('requested_offering_id')
                                <span class="text-error text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    @else
                        <div class="alert alert-info">
                            <span class="icon-[tabler--info-circle] size-5"></span>
                            <span>Tell us what you're interested in using the message box below and we'll be in touch.</span>
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

<script>
// Two-step offering picker: the category select drives which inner picker is
// shown. Each inner select copies its value into the single hidden
// #requested_offering_id input that the form actually posts; switching category
// clears the previous selection so the server never gets a value from the
// wrong type.
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('requested_type_alias');
    const offeringHidden = document.getElementById('requested_offering_id');
    const pickersWrap = document.getElementById('offering-pickers');
    if (!typeSelect || !offeringHidden || !pickersWrap) return;

    const pickers = pickersWrap.querySelectorAll('[data-offering-picker]');

    function showPickerFor(alias) {
        pickers.forEach(p => {
            const matches = p.dataset.offeringPicker === alias;
            p.classList.toggle('hidden', !matches);
            if (!matches) {
                const inner = p.querySelector('[data-inner-offering]');
                if (inner && inner.value) inner.value = '';
            }
        });
        pickersWrap.classList.toggle('hidden', !alias);
    }

    typeSelect.addEventListener('change', function () {
        offeringHidden.value = '';
        showPickerFor(this.value);
    });

    pickers.forEach(picker => {
        const inner = picker.querySelector('[data-inner-offering]');
        if (!inner) return;
        inner.addEventListener('change', function () {
            if (picker.dataset.offeringPicker === typeSelect.value) {
                offeringHidden.value = this.value || '';
            }
        });
    });

    // Initial state (handles pre-selection and validation round-trips).
    showPickerFor(typeSelect.value);
});
</script>
@endsection
