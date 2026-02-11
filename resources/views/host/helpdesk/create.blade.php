@extends('layouts.dashboard')

@section('title', 'Create Ticket')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('helpdesk.index') }}"><span class="icon-[tabler--help] me-1 size-4"></span> Help Desk</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Create Ticket</li>
    </ol>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('helpdesk.index') }}" class="btn btn-ghost btn-sm">
            <span class="icon-[tabler--arrow-left] size-4"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Create Ticket</h1>
            <p class="text-base-content/60 mt-1">Manually create a new support ticket.</p>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('helpdesk.store') }}" method="POST">
        @csrf

        <div class="space-y-6">
            {{-- Contact Information --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Contact Information</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="label-text" for="name">Name <span class="text-error">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                   class="input w-full @error('name') input-error @enderror" required>
                            @error('name')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label-text" for="email">Email <span class="text-error">*</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                       class="input w-full @error('email') input-error @enderror" required>
                                @error('email')
                                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="label-text" for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                       class="input w-full @error('phone') input-error @enderror">
                                @error('phone')
                                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ticket Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Ticket Details</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="label-text" for="source_type">Source <span class="text-error">*</span></label>
                            <select id="source_type" name="source_type"
                                    class="select w-full @error('source_type') select-error @enderror" required>
                                @foreach($sources as $key => $label)
                                    <option value="{{ $key }}" {{ old('source_type', 'manual') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('source_type')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label-text" for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}"
                                   class="input w-full @error('subject') input-error @enderror"
                                   placeholder="Brief description of the request">
                            @error('subject')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label-text" for="message">Message</label>
                            <textarea id="message" name="message" rows="4"
                                      class="textarea w-full @error('message') textarea-error @enderror"
                                      placeholder="Detailed information about the request...">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Service Request Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Service Request (Optional)</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="label-text" for="service_plan_id">Requested Service</label>
                            <select id="service_plan_id" name="service_plan_id" class="select w-full">
                                <option value="">-- No specific service --</option>
                                @foreach($servicePlans as $plan)
                                    <option value="{{ $plan->id }}" {{ old('service_plan_id') == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label-text" for="preferred_date">Preferred Date</label>
                                <input type="date" id="preferred_date" name="preferred_date" value="{{ old('preferred_date') }}"
                                       class="input w-full" min="{{ date('Y-m-d') }}">
                            </div>
                            <div>
                                <label class="label-text" for="preferred_time">Preferred Time</label>
                                <input type="time" id="preferred_time" name="preferred_time" value="{{ old('preferred_time') }}"
                                       class="input w-full">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Assignment --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="text-lg font-semibold mb-4">Assignment</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="label-text" for="assigned_user_id">Assign To</label>
                            <select id="assigned_user_id" name="assigned_user_id" class="select w-full">
                                <option value="">-- Unassigned --</option>
                                @foreach($teamMembers as $member)
                                    <option value="{{ $member->id }}" {{ old('assigned_user_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if($tags->count() > 0)
                            <div>
                                <label class="label-text mb-2 block">Tags</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($tags as $tag)
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="peer hidden"
                                                   {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}>
                                            <span class="badge peer-checked:badge-primary transition-colors" style="--badge-color: {{ $tag->color }}">
                                                {{ $tag->name }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-2">
                <a href="{{ route('helpdesk.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Create Ticket
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
