@extends('layouts.subdomain')

@section('title', 'Request a Class — ' . $host->studio_name)

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
                <h2 class="text-2xl font-bold text-base-content">Request a Class</h2>
                <p class="text-base-content/60 mt-1">
                    Can't find the class you're looking for? Let us know what you'd like to see on our schedule!
                </p>
            </div>

            @if($member ?? false)
                <div class="alert alert-info mb-6">
                    <span class="icon-[tabler--user-check] size-5"></span>
                    <span>Logged in as <strong>{{ $member->full_name }}</strong>. Your information has been pre-filled.</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error mb-6">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <x-form-validate :action="route('subdomain.class-request.store', ['subdomain' => $host->subdomain])" class="space-y-6">

                {{-- Contact Information --}}
                <div class="space-y-4">
                    <h3 class="font-semibold text-base-content flex items-center gap-2">
                        <span class="icon-[tabler--user] size-5 text-primary"></span>
                        Your Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="label">
                                <span class="label-text font-medium">First Name <span class="text-error">*</span></span>
                            </label>
                            <input type="text" id="first_name" name="first_name"
                                   value="{{ old('first_name', $member?->first_name) }}"
                                   class="input input-bordered w-full @error('first_name') input-error @enderror"
                                   placeholder="First name" required minlength="2" maxlength="50"
                                   pattern="[A-Za-zÀ-ÖØ-öø-ÿ' \-]+"
                                   title="Letters only — no numbers or special characters.">
                            <span class="error-message text-error text-sm mt-1 hidden">Letters only, at least 2 characters — no numbers.</span>
                            @error('first_name')
                                <span class="text-error text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="label">
                                <span class="label-text font-medium">Last Name <span class="text-error">*</span></span>
                            </label>
                            <input type="text" id="last_name" name="last_name"
                                   value="{{ old('last_name', $member?->last_name) }}"
                                   class="input input-bordered w-full @error('last_name') input-error @enderror"
                                   placeholder="Last name" required minlength="2" maxlength="50"
                                   pattern="[A-Za-zÀ-ÖØ-öø-ÿ' \-]+"
                                   title="Letters only — no numbers or special characters.">
                            <span class="error-message text-error text-sm mt-1 hidden">Letters only, at least 2 characters — no numbers.</span>
                            @error('last_name')
                                <span class="text-error text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="label">
                                <span class="label-text font-medium">Email <span class="text-error">*</span></span>
                            </label>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email', $member?->email) }}"
                                   class="input input-bordered w-full @error('email') input-error @enderror"
                                   placeholder="your@email.com" required {{ $member ? 'readonly' : '' }}>
                            <span class="error-message text-error text-sm mt-1 hidden">Please enter a valid email address.</span>
                            @error('email')
                                <span class="text-error text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <x-phone-input
                                name="phone"
                                :value="old('phone', $member?->phone)"
                                label="Phone Number"
                                :required="true"
                                :host="$host"
                                id-suffix="class-request"
                            />
                            @error('phone')
                                <span class="text-error text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Class Selection --}}
                <div class="space-y-4">
                    <h3 class="font-semibold text-base-content flex items-center gap-2">
                        <span class="icon-[tabler--yoga] size-5 text-primary"></span>
                        Class Selection
                    </h3>

                    <div>
                        <label for="class_plan_id" class="label">
                            <span class="label-text font-medium">Class Name <span class="text-error">*</span></span>
                        </label>
                        <select id="class_plan_id" name="class_plan_id"
                                class="select select-bordered w-full @error('class_plan_id') select-error @enderror" required>
                            <option value="">Select a class...</option>
                            @foreach($classPlans as $plan)
                                <option value="{{ $plan->id }}"
                                    {{ old('class_plan_id', $selectedClassPlan?->id) == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="error-message text-error text-sm mt-1 hidden">Please choose a class.</span>
                        @error('class_plan_id')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    @if($selectedSession)
                        <div class="alert bg-primary/10 border-primary/20">
                            <span class="icon-[tabler--info-circle] size-5 text-primary"></span>
                            <div>
                                <span class="font-medium">Requesting similar to:</span>
                                {{ $selectedSession->classPlan->name }} on {{ $selectedSession->start_time->format('l, M j') }} at {{ $selectedSession->start_time->format('g:i A') }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Message --}}
                <div class="space-y-4">
                    <h3 class="font-semibold text-base-content flex items-center gap-2">
                        <span class="icon-[tabler--message] size-5 text-primary"></span>
                        Message to Studio
                    </h3>

                    <div>
                        <label for="message" class="label">
                            <span class="label-text font-medium">Your Message</span>
                        </label>
                        <textarea id="message" name="message" rows="4"
                                  class="textarea textarea-bordered w-full @error('message') textarea-error @enderror"
                                  placeholder="Tell us about your preferred days, times, or any other details...">{{ old('message') }}</textarea>
                        @error('message')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Waitlist Checkbox --}}
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="waitlist_requested" value="1"
                           class="checkbox checkbox-primary shrink-0"
                           {{ old('waitlist_requested') ? 'checked' : '' }}>
                    <span class="text-sm">Add me to the waitlist for this class</span>
                </label>

                {{-- Submit --}}
                <div class="pt-4">
                    <button type="submit" class="btn btn-primary w-full md:w-auto">
                        <span class="icon-[tabler--send] size-5"></span>
                        Create Request
                    </button>
                </div>
            </x-form-validate>
        </div>
    </div>
</div>
@endsection
