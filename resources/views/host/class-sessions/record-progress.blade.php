@extends('layouts.dashboard')

@section('title', 'Record Progress - ' . $classSession->display_title)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('class-sessions.index') }}"><span class="icon-[tabler--calendar-event] me-1 size-4"></span> Class Sessions</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('class-sessions.show', $classSession) }}">{{ $classSession->display_title }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Record Progress</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('class-sessions.show', $classSession) }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <span class="icon-[tabler--{{ $progressTemplate->icon ?? 'chart-line' }}] size-6 text-primary"></span>
                <h1 class="text-2xl font-bold">Record Progress: {{ $progressTemplate->name }}</h1>
            </div>
            <p class="text-base-content/60 mt-1">
                {{ $classSession->display_title }} &bull; {{ $classSession->formatted_date }}
            </p>
        </div>
    </div>

    @if($bookings->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--users-minus] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="font-semibold text-lg mb-2">No Attendees</h3>
                <p class="text-base-content/60">There are no confirmed bookings for this session.</p>
                <a href="{{ route('class-sessions.show', $classSession) }}" class="btn btn-primary mt-4">
                    Back to Session
                </a>
            </div>
        </div>
    @else
        <form action="{{ route('class-sessions.store-batch-progress', [$classSession, $progressTemplate]) }}" method="POST">
            @csrf

            {{-- Info Card --}}
            <div class="alert alert-info mb-6">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <div>
                    <strong>Recording progress for {{ $bookings->count() }} attendee(s).</strong>
                    <p class="text-sm mt-1">Click on each attendee to expand and fill in their progress. You can skip clients if no data is available.</p>
                </div>
            </div>

            {{-- Progress Recording Accordion --}}
            <div class="accordion divide-y divide-base-content/10 rounded-lg border border-base-content/10 bg-base-100" data-accordion-always-open>
                @foreach($bookings as $index => $booking)
                    @php
                        $existingReport = $existingReports->get($booking->id);
                        $hasExistingReport = $existingReport !== null;
                        $existingValues = $hasExistingReport
                            ? $existingReport->values->keyBy('progress_template_metric_id')
                            : collect();
                    @endphp
                    <div class="accordion-item" id="client-accordion-{{ $booking->id }}">
                        <button type="button"
                                class="accordion-toggle inline-flex items-center justify-between gap-x-4 px-5 py-4 text-start w-full"
                                aria-controls="client-collapse-{{ $booking->id }}"
                                aria-expanded="false">
                            <div class="flex items-center gap-4 flex-1">
                                @if($booking->client?->avatar_url)
                                    <div class="avatar">
                                        <div class="w-10 rounded-full">
                                            <img src="{{ $booking->client->avatar_url }}" alt="{{ $booking->client->full_name }}">
                                        </div>
                                    </div>
                                @else
                                    <div class="avatar avatar-ring avatar-sm">
                                        <div class="bg-primary text-primary-content rounded-full">
                                            <span class="text-sm">{{ $booking->client?->initials ?? '?' }}</span>
                                        </div>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold">{{ $booking->client?->full_name ?? 'Unknown Client' }}</h3>
                                    <p class="text-sm text-base-content/60 truncate">{{ $booking->client?->email }}</p>
                                </div>
                                @if($hasExistingReport)
                                    <span class="badge badge-success badge-soft badge-sm gap-1">
                                        <span class="icon-[tabler--check] size-3"></span>
                                        Recorded
                                    </span>
                                @else
                                    <span class="badge badge-neutral badge-soft badge-sm gap-1">
                                        <span class="icon-[tabler--clock] size-3"></span>
                                        Not Recorded
                                    </span>
                                @endif
                            </div>
                            <span class="icon-[tabler--chevron-down] size-5 text-base-content/50 accordion-item-active:rotate-180 transition-transform duration-300"></span>
                        </button>

                        <div id="client-collapse-{{ $booking->id }}"
                             class="accordion-content w-full overflow-hidden transition-[height] duration-300"
                             aria-labelledby="client-accordion-{{ $booking->id }}"
                             role="region"
                             style="display: none;">
                            <div class="px-5 pb-5 space-y-6">
                                @foreach($progressTemplate->sections as $section)
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-2 border-b border-base-200 pb-2">
                                            <span class="icon-[tabler--{{ $section->icon ?? 'folder' }}] size-5 text-primary"></span>
                                            <h4 class="font-semibold">{{ $section->name }}</h4>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($section->metrics as $metric)
                                                <div class="form-control">
                                                    <label class="label" for="metric_{{ $booking->id }}_{{ $metric->id }}">
                                                        <span class="label-text">
                                                            {{ $metric->name }}
                                                            @if($metric->is_required)
                                                                <span class="text-error">*</span>
                                                            @endif
                                                        </span>
                                                        @if($metric->unit)
                                                            <span class="label-text-alt">{{ $metric->unit }}</span>
                                                        @endif
                                                    </label>

                                                    @php
                                                        $existingValue = $existingValues->get($metric->id);
                                                        $numericVal = $existingValue?->value_numeric;
                                                        $textVal = $existingValue?->value_text;
                                                        $jsonVal = $existingValue?->value_json ?? [];
                                                    @endphp

                                                    @switch($metric->metric_type)
                                                        @case('slider')
                                                            @php $sliderVal = $numericVal ?? $metric->min_value ?? 1; @endphp
                                                            <div class="flex items-center gap-3">
                                                                <input
                                                                    type="range"
                                                                    id="metric_{{ $booking->id }}_{{ $metric->id }}"
                                                                    name="reports[{{ $booking->id }}][metrics][{{ $metric->id }}]"
                                                                    min="{{ $metric->min_value ?? 1 }}"
                                                                    max="{{ $metric->max_value ?? 10 }}"
                                                                    step="{{ $metric->step ?? 1 }}"
                                                                    value="{{ $sliderVal }}"
                                                                    class="range range-primary flex-1"
                                                                    oninput="document.getElementById('slider_value_{{ $booking->id }}_{{ $metric->id }}').textContent = this.value"
                                                                >
                                                                <span id="slider_value_{{ $booking->id }}_{{ $metric->id }}" class="badge badge-primary w-10 text-center">{{ (int) $sliderVal }}</span>
                                                            </div>
                                                            <div class="flex justify-between text-xs text-base-content/50 mt-1">
                                                                <span>{{ $metric->min_value ?? 1 }}</span>
                                                                <span>{{ $metric->max_value ?? 10 }}</span>
                                                            </div>
                                                            @break

                                                        @case('number')
                                                            <input
                                                                type="number"
                                                                id="metric_{{ $booking->id }}_{{ $metric->id }}"
                                                                name="reports[{{ $booking->id }}][metrics][{{ $metric->id }}]"
                                                                min="{{ $metric->min_value }}"
                                                                max="{{ $metric->max_value }}"
                                                                step="{{ $metric->step ?? 'any' }}"
                                                                value="{{ $numericVal }}"
                                                                class="input input-bordered w-full"
                                                                placeholder="Enter value"
                                                            >
                                                            @break

                                                        @case('rating')
                                                            <div class="rating rating-lg" id="rating_{{ $booking->id }}_{{ $metric->id }}">
                                                                @for($i = 1; $i <= ($metric->max_value ?? 5); $i++)
                                                                    <input
                                                                        type="radio"
                                                                        name="reports[{{ $booking->id }}][metrics][{{ $metric->id }}]"
                                                                        value="{{ $i }}"
                                                                        class="mask mask-star-2 bg-warning"
                                                                        {{ (int) $numericVal === $i ? 'checked' : '' }}
                                                                    >
                                                                @endfor
                                                            </div>
                                                            @break

                                                        @case('select')
                                                            <select
                                                                id="metric_{{ $booking->id }}_{{ $metric->id }}"
                                                                name="reports[{{ $booking->id }}][metrics][{{ $metric->id }}]"
                                                                class="select select-bordered w-full"
                                                            >
                                                                <option value="">Select...</option>
                                                                @foreach($metric->options ?? [] as $option)
                                                                    <option value="{{ $option }}" {{ $textVal === $option ? 'selected' : '' }}>{{ $option }}</option>
                                                                @endforeach
                                                            </select>
                                                            @break

                                                        @case('checkbox_list')
                                                            <div class="space-y-2">
                                                                @foreach($metric->options ?? [] as $option)
                                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                                        <input
                                                                            type="checkbox"
                                                                            name="reports[{{ $booking->id }}][metrics][{{ $metric->id }}][]"
                                                                            value="{{ $option }}"
                                                                            class="checkbox checkbox-primary checkbox-sm"
                                                                            {{ in_array($option, $jsonVal) ? 'checked' : '' }}
                                                                        >
                                                                        <span class="text-sm">{{ $option }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                            @break

                                                        @case('text')
                                                        @default
                                                            <textarea
                                                                id="metric_{{ $booking->id }}_{{ $metric->id }}"
                                                                name="reports[{{ $booking->id }}][metrics][{{ $metric->id }}]"
                                                                rows="2"
                                                                class="textarea textarea-bordered w-full"
                                                                placeholder="Enter notes..."
                                                            >{{ $textVal }}</textarea>
                                                            @break
                                                    @endswitch
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Trainer Notes --}}
                                <div class="form-control pt-4 border-t border-base-200">
                                    <label class="label" for="trainer_notes_{{ $booking->id }}">
                                        <span class="label-text font-semibold">Trainer Notes</span>
                                        <span class="label-text-alt text-base-content/50">Optional</span>
                                    </label>
                                    <textarea
                                        id="trainer_notes_{{ $booking->id }}"
                                        name="reports[{{ $booking->id }}][trainer_notes]"
                                        rows="3"
                                        class="textarea textarea-bordered w-full"
                                        placeholder="Notes about this client's progress, areas of improvement, etc."
                                    >{{ $existingReport?->trainer_notes }}</textarea>
                                </div>

                                {{-- Save Toggle --}}
                                <div class="flex items-center justify-between p-4 bg-base-200/50 rounded-lg mt-4">
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            id="enable_{{ $booking->id }}"
                                            name="reports[{{ $booking->id }}][enabled]"
                                            value="1"
                                            class="switch switch-primary"
                                            {{ $hasExistingReport ? 'checked' : '' }}
                                        >
                                        <label for="enable_{{ $booking->id }}" class="cursor-pointer">
                                            <span class="font-medium">Save progress for {{ $booking->client?->first_name ?? 'this client' }}</span>
                                        </label>
                                    </div>
                                    @if($hasExistingReport)
                                        <span class="text-sm text-success flex items-center gap-1">
                                            <span class="icon-[tabler--check] size-4"></span>
                                            Previously recorded
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-between mt-6 sticky bottom-0 bg-base-100 p-4 border-t border-base-200 rounded-lg shadow-lg">
                <a href="{{ route('class-sessions.show', $classSession) }}" class="btn btn-ghost">
                    Cancel
                </a>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-base-content/60">{{ $bookings->count() }} attendee(s)</span>
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--check] size-5"></span>
                        Save All Progress Reports
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection
