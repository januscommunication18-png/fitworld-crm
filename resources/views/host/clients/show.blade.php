@extends('layouts.dashboard')

@section('title', $client->full_name . ' — Client')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.index') }}">Clients</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $client->full_name }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4 relative z-50">
        <div class="flex items-start gap-4 flex-1">
            <a href="{{ route('clients.index') }}" class="btn btn-ghost btn-sm btn-circle mt-1">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div class="avatar placeholder">
                <div class="bg-primary/10 text-primary w-16 h-16 rounded-full">
                    <span class="text-xl font-bold">{{ $client->initials }}</span>
                </div>
            </div>
            <div>
                <h1 class="text-2xl font-bold">{{ $client->full_name }}</h1>
                <div class="flex flex-wrap items-center gap-3 text-base-content/60">
                    <span class="flex items-center gap-1">
                        <span class="icon-[tabler--mail] size-4"></span>
                        {{ $client->email }}
                    </span>
                    @if($client->phone)
                    <span class="flex items-center gap-1">
                        <span class="icon-[tabler--phone] size-4"></span>
                        {{ $client->phone }}
                    </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    @php
                        $statusBadge = match($client->status) {
                            'lead' => 'badge-warning',
                            'client' => 'badge-info',
                            'member' => 'badge-success',
                            'at_risk' => 'badge-error',
                            default => 'badge-ghost'
                        };
                    @endphp
                    <span class="badge badge-soft {{ $statusBadge }}">
                        {{ $statuses[$client->status] ?? ucfirst($client->status) }}
                    </span>
                    @foreach($client->tags as $tag)
                        <span class="badge badge-sm" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}; border-color: {{ $tag->color }}40;">
                            {{ $tag->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
        {{-- Right-aligned buttons --}}
        <div class="flex items-center gap-2 md:ml-auto">
            {{-- Add Booking Dropdown --}}
            <div class="relative">
                <button type="button" class="btn btn-success" onclick="this.nextElementSibling.classList.toggle('hidden')">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Book
                    <span class="icon-[tabler--chevron-down] size-4"></span>
                </button>
                <ul class="hidden absolute right-0 top-full mt-1 menu bg-base-100 rounded-box w-52 p-2 shadow-lg border border-base-300 z-50">
                    <li>
                        <a href="{{ route('walk-in.select', ['client_id' => $client->id]) }}">
                            <span class="icon-[tabler--yoga] size-5 text-primary"></span>
                            Class Session
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('walk-in.select-service', ['client_id' => $client->id]) }}">
                            <span class="icon-[tabler--massage] size-5 text-success"></span>
                            Service Slot
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('walk-in.select-membership', ['client_id' => $client->id]) }}">
                            <span class="icon-[tabler--id-badge-2] size-5 text-warning"></span>
                            Membership
                        </a>
                    </li>
                </ul>
            </div>
            @if(isset($todaysClasses) && $todaysClasses->count() > 0 && isset($hasProgressTemplates) && $hasProgressTemplates)
            <button type="button" class="btn btn-success" onclick="document.getElementById('record-progress-modal').classList.remove('hidden')">
                <span class="icon-[tabler--chart-line] size-5"></span>
                Record Progress
            </button>
            @endif
            <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary">
                <span class="icon-[tabler--edit] size-5"></span>
                Edit
            </a>
            <div class="relative z-[100]">
                <button type="button" class="btn btn-ghost btn-square" onclick="this.nextElementSibling.classList.toggle('hidden')">
                    <span class="icon-[tabler--dots-vertical] size-5"></span>
                </button>
                <ul class="hidden absolute right-0 top-full mt-1 menu bg-base-100 rounded-box w-52 p-2 shadow-xl border border-base-300 z-[9999]">
                    @if($client->status === 'lead')
                    <li>
                        <form method="POST" action="{{ route('clients.convert-to-client', $client) }}" class="m-0">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-2">
                                <span class="icon-[tabler--user-check] size-4"></span> Convert to Client
                            </button>
                        </form>
                    </li>
                    @endif
                    @if($client->status !== 'member')
                    <li>
                        <form method="POST" action="{{ route('clients.convert-to-member', $client) }}" class="m-0">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-2">
                                <span class="icon-[tabler--id-badge] size-4"></span> Convert to Member
                            </button>
                        </form>
                    </li>
                    @endif
                    @if($client->status === 'at_risk')
                    <li>
                        <form method="POST" action="{{ route('clients.clear-at-risk', $client) }}" class="m-0">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-2">
                                <span class="icon-[tabler--check] size-4"></span> Clear At-Risk Status
                            </button>
                        </form>
                    </li>
                    @endif
                    <li class="divider my-1"></li>
                    <li>
                        <form method="POST" action="{{ route('clients.archive', $client) }}" class="m-0"
                              onsubmit="return confirm('Are you sure you want to archive this client?')">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                                <span class="icon-[tabler--archive] size-4"></span> Archive Client
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tabs tabs-bordered relative z-10" role="tablist">
        <button class="tab tab-active" data-tab="info" role="tab">
            <span class="icon-[tabler--layout-dashboard] size-4 mr-2"></span>
            Overview
        </button>
        <button class="tab" data-tab="bookings" role="tab">
            <span class="icon-[tabler--calendar-event] size-4 mr-2"></span>
            Bookings
            @if($bookings->count() > 0)
                <span class="badge badge-sm badge-primary ml-2">{{ $bookings->count() }}</span>
            @endif
        </button>
        <button class="tab" data-tab="questionnaires" role="tab">
            <span class="icon-[tabler--forms] size-4 mr-2"></span>
            Questionnaires
            @if($questionnaireResponses->count() > 0)
                <span class="badge badge-sm badge-primary ml-2">{{ $questionnaireResponses->count() }}</span>
            @endif
        </button>
        <button class="tab" data-tab="notes" role="tab">
            <span class="icon-[tabler--notes] size-4 mr-2"></span>
            Notes
            @if($client->clientNotes->count() > 0)
                <span class="badge badge-sm badge-primary ml-2">{{ $client->clientNotes->count() }}</span>
            @endif
        </button>
        @if(isset($progressReports))
        <button class="tab" data-tab="progress" role="tab">
            <span class="icon-[tabler--chart-line] size-4 mr-2"></span>
            Progress
            @if($progressReports->total() > 0)
                <span class="badge badge-sm badge-primary ml-2">{{ $progressReports->total() }}</span>
            @endif
        </button>
        @endif
        <button class="tab" data-tab="activity" role="tab">
            <span class="icon-[tabler--activity] size-4 mr-2"></span>
            Activity
        </button>
    </div>

    {{-- Tab Content --}}
    <div class="tab-contents relative z-0">
        {{-- Overview Tab --}}
        <div class="tab-content active" data-content="info">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    {{-- Accordion Sections --}}
                    <div class="accordion divide-y divide-base-200 rounded-lg bg-base-100" id="client-info-accordion">
                        {{-- Quick Notes - Open by default --}}
                        <div class="accordion-item">
                            <button class="accordion-toggle inline-flex items-center gap-2 px-4 py-3 w-full text-left font-medium" aria-controls="notes-content" aria-expanded="true">
                                <span class="icon-[tabler--notes] size-5 text-primary"></span>
                                Notes
                                <span class="icon-[tabler--chevron-down] accordion-icon size-5 ml-auto transition-transform" style="transform: rotate(180deg)"></span>
                                @if($client->clientNotes->count() > 0)
                                    <span class="badge badge-primary badge-sm">{{ $client->clientNotes->count() }}</span>
                                @endif
                            </button>
                            <div id="notes-content" class="accordion-content w-full overflow-hidden transition-[height]" role="region">
                                <div class="px-4 pb-4">
                                    <form id="quick-note-form">
                                        <div class="space-y-3">
                                            <div>
                                                <textarea id="quick-note-input" name="content" rows="2"
                                                    class="textarea textarea-bordered w-full"
                                                    placeholder="What would you like to note about this client?" required></textarea>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm text-base-content/60">Type:</span>
                                                    <div class="flex gap-1">
                                                        <label class="cursor-pointer">
                                                            <input type="radio" name="note_type" value="note" class="hidden peer" checked>
                                                            <span class="badge badge-outline peer-checked:badge-primary peer-checked:text-primary-content transition-all">
                                                                <span class="icon-[tabler--note] size-3 mr-1"></span> Note
                                                            </span>
                                                        </label>
                                                        <label class="cursor-pointer">
                                                            <input type="radio" name="note_type" value="call" class="hidden peer">
                                                            <span class="badge badge-outline peer-checked:badge-primary peer-checked:text-primary-content transition-all">
                                                                <span class="icon-[tabler--phone] size-3 mr-1"></span> Call
                                                            </span>
                                                        </label>
                                                        <label class="cursor-pointer">
                                                            <input type="radio" name="note_type" value="email" class="hidden peer">
                                                            <span class="badge badge-outline peer-checked:badge-primary peer-checked:text-primary-content transition-all">
                                                                <span class="icon-[tabler--mail] size-3 mr-1"></span> Email
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-sm" id="quick-note-btn">
                                                    <span class="icon-[tabler--send] size-4"></span>
                                                    Add Note
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    @if($client->clientNotes->count() > 0)
                                        @php $latestNote = $client->clientNotes->first(); @endphp
                                        <div class="flex gap-3 p-3 mt-4 rounded-lg bg-base-200/50 border border-base-300" id="latest-note-display">
                                            <span class="{{ \App\Models\ClientNote::getNoteTypeIcon($latestNote->note_type) }} size-4 mt-0.5 shrink-0 text-base-content/60"></span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm">{{ $latestNote->content }}</p>
                                                <p class="text-xs text-base-content/50 mt-1">{{ $latestNote->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <button class="btn btn-ghost btn-xs mt-2" onclick="document.querySelector('[data-tab=notes]').click()">
                                            View All Notes
                                            <span class="icon-[tabler--arrow-right] size-4"></span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        {{-- Progress Tracking --}}
                        <div class="accordion-item">
                            <button class="accordion-toggle inline-flex items-center gap-2 px-4 py-3 w-full text-left font-medium" aria-controls="progress-content" aria-expanded="false">
                                <span class="icon-[tabler--chart-line] size-5 text-primary"></span>
                                Progress Tracking
                                <span class="icon-[tabler--chevron-down] accordion-icon size-5 ml-auto transition-transform"></span>
                                @if(isset($progressByTemplate) && $progressByTemplate->count() > 0)
                                    <span class="badge badge-primary badge-sm">{{ $progressByTemplate->count() }}</span>
                                @endif
                            </button>
                            <div id="progress-content" class="accordion-content hidden w-full overflow-hidden transition-[height]" role="region">
                                <div class="px-4 pb-4">
                                    @if(isset($progressByTemplate) && $progressByTemplate->count() > 0)
                                        <div class="space-y-4">
                                            @foreach($progressByTemplate as $templateData)
                                                <div class="border border-base-200 rounded-xl p-4">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <div class="flex items-center gap-3">
                                                            <div class="p-2 rounded-lg bg-primary/10">
                                                                <span class="icon-[tabler--{{ $templateData['template_icon'] }}] size-5 text-primary"></span>
                                                            </div>
                                                            <div>
                                                                <h3 class="font-semibold">{{ $templateData['template_name'] }}</h3>
                                                                <p class="text-xs text-base-content/60">{{ $templateData['total_reports'] }} reports &bull; Last: {{ $templateData['latest_date'] }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            <div class="text-2xl font-bold {{ $templateData['latest_score'] >= 70 ? 'text-success' : ($templateData['latest_score'] >= 40 ? 'text-warning' : 'text-error') }}">
                                                                {{ $templateData['latest_score'] }}%
                                                            </div>
                                                            <div class="flex items-center justify-end gap-1 text-sm {{ $templateData['trend'] >= 0 ? 'text-success' : 'text-error' }}">
                                                                <span class="icon-[tabler--trending-{{ $templateData['trend'] >= 0 ? 'up' : 'down' }}] size-4"></span>
                                                                {{ $templateData['trend'] >= 0 ? '+' : '' }}{{ $templateData['trend'] }}%
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if($templateData['chart_data']->count() > 1)
                                                        <div class="h-20 flex items-end justify-between gap-1 mb-3">
                                                            @foreach($templateData['chart_data'] as $point)
                                                                <div class="flex-1 flex flex-col items-center gap-1 group relative">
                                                                    <div class="w-full rounded-t cursor-pointer transition-all hover:opacity-80 {{ $point['score'] >= 70 ? 'bg-success' : ($point['score'] >= 40 ? 'bg-warning' : 'bg-error') }}"
                                                                         style="height: {{ max(4, $point['score'] * 0.75) }}px;"
                                                                         title="{{ $point['class'] }}: {{ $point['score'] }}%"></div>
                                                                    <span class="text-[10px] text-base-content/50">{{ $point['date'] }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    @if($templateData['by_class']->count() > 0)
                                                        <div class="border-t border-base-200 pt-3">
                                                            <p class="text-xs font-medium text-base-content/60 uppercase tracking-wide mb-2">By Class</p>
                                                            <div class="grid grid-cols-2 gap-2">
                                                                @foreach($templateData['by_class'] as $className => $classData)
                                                                    <div class="flex items-center justify-between p-2 bg-base-200/50 rounded-lg">
                                                                        <span class="text-sm truncate max-w-[100px]" title="{{ $className }}">{{ $className }}</span>
                                                                        <span class="font-semibold text-sm {{ $classData['latest_score'] >= 70 ? 'text-success' : ($classData['latest_score'] >= 40 ? 'text-warning' : 'text-error') }}">
                                                                            {{ $classData['latest_score'] }}%
                                                                        </span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-base-content/50">No progress data yet.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- This Week's Schedule --}}
                        <div class="accordion-item">
                            <button class="accordion-toggle inline-flex items-center gap-2 px-4 py-3 w-full text-left font-medium" aria-controls="schedule-content" aria-expanded="false">
                                <span class="icon-[tabler--calendar-week] size-5 text-primary"></span>
                                This Week's Schedule
                                <span class="icon-[tabler--chevron-down] accordion-icon size-5 ml-auto transition-transform"></span>
                                @if(isset($thisWeekSchedule) && $thisWeekSchedule->count() > 0)
                                    <span class="badge badge-primary badge-sm">{{ $thisWeekSchedule->count() }}</span>
                                @endif
                            </button>
                            <div id="schedule-content" class="accordion-content hidden w-full overflow-hidden transition-[height]" role="region">
                                <div class="px-4 pb-4">
                                    @if(isset($thisWeekSchedule) && $thisWeekSchedule->count() > 0)
                                        <div class="space-y-2">
                                            @foreach($thisWeekSchedule as $booking)
                                                @php
                                                    $isServiceSlot = $booking->bookable instanceof \App\Models\ServiceSlot;
                                                    $icon = $isServiceSlot ? 'icon-[tabler--massage]' : 'icon-[tabler--yoga]';
                                                    $title = $isServiceSlot
                                                        ? ($booking->bookable->servicePlan->name ?? 'Service')
                                                        : ($booking->bookable->display_title ?? $booking->bookable->classPlan->name ?? 'Class');
                                                    $isToday = $booking->bookable->start_time->isToday();
                                                    $isPast = $booking->bookable->start_time->isPast();
                                                @endphp
                                                <div class="flex items-center gap-3 p-2 rounded-lg {{ $isToday ? 'bg-primary/10' : 'bg-base-200/50' }} {{ $isPast && !$isToday ? 'opacity-60' : '' }}">
                                                    <span class="{{ $icon }} size-5 {{ $isServiceSlot ? 'text-secondary' : 'text-primary' }}"></span>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="font-medium text-sm">{{ $title }}</div>
                                                        <div class="text-xs text-base-content/60">{{ $booking->bookable->start_time->format('D, M j \a\t g:i A') }}</div>
                                                    </div>
                                                    @if($isToday)
                                                        <span class="badge badge-primary badge-xs">Today</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-base-content/50 mb-3">No bookings this week.</p>
                                        <div class="flex gap-2">
                                            <a href="{{ route('walk-in.select', ['client_id' => $client->id]) }}" class="btn btn-primary btn-xs">Book Class</a>
                                            <a href="{{ route('walk-in.select-service', ['client_id' => $client->id]) }}" class="btn btn-secondary btn-xs">Book Service</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Personal Details --}}
                        @if($client->date_of_birth || $client->gender)
                        <div class="accordion-item">
                            <button class="accordion-toggle inline-flex items-center gap-2 px-4 py-3 w-full text-left font-medium" aria-controls="personal-content" aria-expanded="false">
                                <span class="icon-[tabler--user-circle] size-5 text-primary"></span>
                                Personal Details
                                <span class="icon-[tabler--chevron-down] accordion-icon size-5 ml-auto transition-transform"></span>
                            </button>
                            <div id="personal-content" class="accordion-content hidden w-full overflow-hidden transition-[height]" role="region">
                                <div class="px-4 pb-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        @if($client->date_of_birth)
                                        <div>
                                            <label class="text-sm text-base-content/60">Date of Birth</label>
                                            <p class="font-medium">{{ $client->date_of_birth->format('M d, Y') }}</p>
                                        </div>
                                        @endif
                                        @if($client->gender)
                                        <div>
                                            <label class="text-sm text-base-content/60">Gender</label>
                                            <p class="font-medium">{{ \App\Models\Client::getGenders()[$client->gender] ?? ucfirst($client->gender) }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Emergency Contact --}}
                        @if($client->emergency_contact_name || $client->emergency_contact_phone)
                        <div class="accordion-item">
                            <button class="accordion-toggle inline-flex items-center gap-2 px-4 py-3 w-full text-left font-medium" aria-controls="emergency-content" aria-expanded="false">
                                <span class="icon-[tabler--emergency-bed] size-5 text-primary"></span>
                                Emergency Contact
                                <span class="icon-[tabler--chevron-down] accordion-icon size-5 ml-auto transition-transform"></span>
                            </button>
                            <div id="emergency-content" class="accordion-content hidden w-full overflow-hidden transition-[height]" role="region">
                                <div class="px-4 pb-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        @if($client->emergency_contact_name)
                                        <div>
                                            <label class="text-sm text-base-content/60">Name</label>
                                            <p class="font-medium">{{ $client->emergency_contact_name }}</p>
                                        </div>
                                        @endif
                                        @if($client->emergency_contact_relationship)
                                        <div>
                                            <label class="text-sm text-base-content/60">Relationship</label>
                                            <p class="font-medium">{{ $client->emergency_contact_relationship }}</p>
                                        </div>
                                        @endif
                                        @if($client->emergency_contact_phone)
                                        <div>
                                            <label class="text-sm text-base-content/60">Phone</label>
                                            <p class="font-medium">{{ $client->emergency_contact_phone }}</p>
                                        </div>
                                        @endif
                                        @if($client->emergency_contact_email)
                                        <div>
                                            <label class="text-sm text-base-content/60">Email</label>
                                            <p class="font-medium">{{ $client->emergency_contact_email }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Health & Fitness --}}
                        @if($client->experience_level || $client->fitness_goals || $client->medical_conditions || $client->injuries || $client->limitations)
                        <div class="accordion-item">
                            <button class="accordion-toggle inline-flex items-center gap-2 px-4 py-3 w-full text-left font-medium" aria-controls="health-content" aria-expanded="false">
                                <span class="icon-[tabler--heartbeat] size-5 text-primary"></span>
                                Health & Fitness
                                <span class="icon-[tabler--chevron-down] accordion-icon size-5 ml-auto transition-transform"></span>
                                @if($client->medical_conditions || $client->injuries || $client->limitations)
                                    <span class="badge badge-warning badge-sm">!</span>
                                @endif
                            </button>
                            <div id="health-content" class="accordion-content hidden w-full overflow-hidden transition-[height]" role="region">
                                <div class="px-4 pb-4">
                                    <div class="space-y-3">
                                        @if($client->experience_level)
                                        <div>
                                            <label class="text-sm text-base-content/60">Experience Level</label>
                                            <p class="font-medium">{{ \App\Models\Client::getExperienceLevels()[$client->experience_level] ?? ucfirst($client->experience_level) }}</p>
                                        </div>
                                        @endif
                                        @if($client->pregnancy_status)
                                        <div>
                                            <label class="text-sm text-base-content/60">Pregnancy Status</label>
                                            <span class="badge badge-warning">Currently Pregnant</span>
                                        </div>
                                        @endif
                                        @if($client->fitness_goals)
                                        <div>
                                            <label class="text-sm text-base-content/60">Fitness Goals</label>
                                            <p class="font-medium">{{ $client->fitness_goals }}</p>
                                        </div>
                                        @endif
                                        @if($client->medical_conditions)
                                        <div>
                                            <label class="text-sm text-base-content/60">Medical Conditions</label>
                                            <p class="font-medium text-warning">{{ $client->medical_conditions }}</p>
                                        </div>
                                        @endif
                                        @if($client->injuries)
                                        <div>
                                            <label class="text-sm text-base-content/60">Injuries</label>
                                            <p class="font-medium text-warning">{{ $client->injuries }}</p>
                                        </div>
                                        @endif
                                        @if($client->limitations)
                                        <div>
                                            <label class="text-sm text-base-content/60">Limitations</label>
                                            <p class="font-medium text-warning">{{ $client->limitations }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Custom Fields --}}
                        @if($customFields['sections']->count() > 0 || $customFields['unsectionedFields']->count() > 0)
                        <div class="accordion-item">
                            <button class="accordion-toggle inline-flex items-center gap-2 px-4 py-3 w-full text-left font-medium" aria-controls="custom-content" aria-expanded="false">
                                <span class="icon-[tabler--forms] size-5 text-primary"></span>
                                Additional Information
                                <span class="icon-[tabler--chevron-down] accordion-icon size-5 ml-auto transition-transform"></span>
                            </button>
                            <div id="custom-content" class="accordion-content hidden w-full overflow-hidden transition-[height]" role="region">
                                <div class="px-4 pb-4">
                                    @foreach($customFields['sections'] as $section)
                                        @if($section->activeFieldDefinitions->count() > 0)
                                        <div class="mb-4">
                                            <h3 class="font-semibold text-sm text-base-content/70 uppercase tracking-wider mb-2">{{ $section->name }}</h3>
                                            <div class="grid grid-cols-2 gap-3">
                                                @foreach($section->activeFieldDefinitions as $field)
                                                    @php $value = $customFields['values'][$field->id] ?? null; @endphp
                                                    <div>
                                                        <label class="text-sm text-base-content/60">{{ $field->field_label }}</label>
                                                        <p class="font-medium">{{ $value?->formatted_value ?? 'Not set' }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                    @if($customFields['unsectionedFields']->count() > 0)
                                    <div class="grid grid-cols-2 gap-3">
                                        @foreach($customFields['unsectionedFields'] as $field)
                                            @php $value = $customFields['values'][$field->id] ?? null; @endphp
                                            <div>
                                                <label class="text-sm text-base-content/60">{{ $field->field_label }}</label>
                                                <p class="font-medium">{{ $value?->formatted_value ?? 'Not set' }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Client Score Card --}}
                    <div class="card bg-base-100">
                        <div class="card-body p-4">
                            <div class="flex items-center justify-between">
                                <h2 class="card-title text-base">
                                    <span class="icon-[tabler--chart-donut-3] size-5 text-primary"></span>
                                    Client Score
                                </h2>
                                <div class="flex items-center gap-2">
                                    <span class="text-3xl font-bold">{{ $clientScore['overall'] }}</span>
                                    <div class="badge badge-lg badge-{{ $clientScore['grade']['color'] }}">{{ $clientScore['grade']['label'] }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 mt-4">
                                {{-- Engagement --}}
                                <div class="text-center p-2 rounded-lg bg-base-200/50">
                                    <span class="icon-[tabler--activity-heartbeat] size-4 text-primary"></span>
                                    <div class="text-lg font-bold text-primary">{{ $clientScore['engagement']['score'] }}</div>
                                    <div class="text-[10px] text-base-content/60 uppercase">Engage</div>
                                    <div class="w-full bg-base-300 rounded-full h-1 mt-1">
                                        <div class="bg-primary h-1 rounded-full" style="width: {{ $clientScore['engagement']['score'] }}%"></div>
                                    </div>
                                </div>

                                {{-- Usage --}}
                                <div class="text-center p-2 rounded-lg bg-base-200/50">
                                    <span class="icon-[tabler--chart-bar] size-4 text-secondary"></span>
                                    <div class="text-lg font-bold text-secondary">{{ $clientScore['usage']['score'] }}</div>
                                    <div class="text-[10px] text-base-content/60 uppercase">Usage</div>
                                    <div class="w-full bg-base-300 rounded-full h-1 mt-1">
                                        <div class="bg-secondary h-1 rounded-full" style="width: {{ $clientScore['usage']['score'] }}%"></div>
                                    </div>
                                </div>

                                {{-- Revenue --}}
                                <div class="text-center p-2 rounded-lg bg-base-200/50">
                                    <span class="icon-[tabler--currency-dollar] size-4 text-success"></span>
                                    <div class="text-lg font-bold text-success">{{ $clientScore['revenue']['score'] }}</div>
                                    <div class="text-[10px] text-base-content/60 uppercase">Revenue</div>
                                    <div class="w-full bg-base-300 rounded-full h-1 mt-1">
                                        <div class="bg-success h-1 rounded-full" style="width: {{ $clientScore['revenue']['score'] }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-3 space-y-1">
                                <span class="text-xs text-base-content/50">{{ $clientScore['grade']['description'] }} · Last visit {{ $clientScore['engagement']['days_since_visit'] }}d ago</span>
                                <div>
                                    <button type="button" onclick="document.getElementById('score-calculation-modal').classList.remove('hidden')" class="text-xs text-primary hover:underline">
                                        Score calculation
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">Summary</h2>
                            <div class="space-y-4 mt-4">
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Status</span>
                                    <span class="badge badge-soft {{ $statusBadge }}">{{ $statuses[$client->status] ?? ucfirst($client->status) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Source</span>
                                    <span class="font-medium">{{ \App\Models\Client::getLeadSources()[$client->lead_source] ?? $client->lead_source }}</span>
                                </div>
                                @if($client->referral_source)
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Referral</span>
                                    <span class="font-medium">{{ $client->referral_source }}</span>
                                </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Last Visit</span>
                                    <span class="font-medium">{{ $client->last_visit_at?->diffForHumans() ?? 'Never' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Next Booking</span>
                                    <span class="font-medium">{{ $client->next_booking_at?->format('M d, Y') ?? 'None' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Created</span>
                                    <span class="font-medium">{{ $client->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Membership Info --}}
                    @if($client->membership_status && $client->membership_status !== 'none')
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">Membership</h2>
                            <div class="space-y-4 mt-4">
                                @if(isset($activeCustomerMembership) && $activeCustomerMembership->membershipPlan)
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Plan</span>
                                    <span class="font-medium">{{ $activeCustomerMembership->membershipPlan->name }}</span>
                                </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Status</span>
                                    @php
                                        $membershipBadge = match($client->membership_status) {
                                            'active' => 'badge-success',
                                            'paused' => 'badge-warning',
                                            'cancelled' => 'badge-error',
                                            default => 'badge-ghost'
                                        };
                                    @endphp
                                    <span class="badge badge-soft {{ $membershipBadge }}">{{ \App\Models\Client::getMembershipStatuses()[$client->membership_status] ?? ucfirst($client->membership_status) }}</span>
                                </div>
                                @if($client->membership_start_date)
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Start Date</span>
                                    <span class="font-medium">{{ $client->membership_start_date->format('M d, Y') }}</span>
                                </div>
                                @endif
                                @if($client->membership_end_date)
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">End Date</span>
                                    <span class="font-medium">{{ $client->membership_end_date->format('M d, Y') }}</span>
                                </div>
                                @endif
                                @if($client->membership_renewal_date)
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Renewal</span>
                                    <span class="font-medium">{{ $client->membership_renewal_date->format('M d, Y') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Communication Preferences --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">Communication</h2>
                            <div class="space-y-3 mt-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-base-content/60">Preferred Contact</span>
                                    <span class="font-medium">{{ \App\Models\Client::getContactMethods()[$client->preferred_contact_method] ?? 'Email' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-base-content/60">Email Opt-in</span>
                                    @if($client->email_opt_in)
                                        <span class="badge badge-soft badge-success badge-sm">Yes</span>
                                    @else
                                        <span class="badge badge-soft badge-ghost badge-sm">No</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-base-content/60">SMS Opt-in</span>
                                    @if($client->sms_opt_in)
                                        <span class="badge badge-soft badge-success badge-sm">Yes</span>
                                    @else
                                        <span class="badge badge-soft badge-ghost badge-sm">No</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-base-content/60">Marketing Opt-in</span>
                                    @if($client->marketing_opt_in)
                                        <span class="badge badge-soft badge-success badge-sm">Yes</span>
                                    @else
                                        <span class="badge badge-soft badge-ghost badge-sm">No</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Internal Notes --}}
                    @if($client->notes)
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">Internal Notes</h2>
                            <p class="mt-2 text-sm text-base-content/70">{{ $client->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bookings Tab --}}
        <div class="tab-content hidden" data-content="bookings">
            {{-- Booking Summary Card --}}
            @if(isset($bookingSummary))
            <div class="card bg-base-100 mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--report-analytics] size-5"></span>
                        Activity Summary
                    </h2>

                    <div class="grid grid-cols-5 gap-6">
                        {{-- Left Column - Totals (2 cols = 40%) --}}
                        <div class="col-span-2">
                            <div class="grid grid-cols-2 gap-3">
                                {{-- Classes --}}
                                <div class="p-3 rounded-xl bg-primary/10 border border-primary/20 text-center">
                                    <span class="icon-[tabler--yoga] size-6 text-primary"></span>
                                    <p class="text-2xl font-bold mt-1">{{ $bookingSummary['total_classes'] }}</p>
                                    <p class="text-xs text-base-content/60">Classes</p>
                                </div>
                                {{-- Services --}}
                                <div class="p-3 rounded-xl bg-secondary/10 border border-secondary/20 text-center">
                                    <span class="icon-[tabler--massage] size-6 text-secondary"></span>
                                    <p class="text-2xl font-bold mt-1">{{ $bookingSummary['total_services'] }}</p>
                                    <p class="text-xs text-base-content/60">Services</p>
                                </div>
                                {{-- Memberships --}}
                                <div class="p-3 rounded-xl bg-warning/10 border border-warning/20 text-center">
                                    <span class="icon-[tabler--id-badge-2] size-6 text-warning"></span>
                                    <p class="text-2xl font-bold mt-1">{{ $bookingSummary['total_memberships'] ?? 0 }}</p>
                                    <p class="text-xs text-base-content/60">Memberships</p>
                                </div>
                                {{-- Catalog Items --}}
                                <div class="p-3 rounded-xl bg-info/10 border border-info/20 text-center">
                                    <span class="icon-[tabler--package] size-6 text-info"></span>
                                    <p class="text-2xl font-bold mt-1">{{ $bookingSummary['total_catalog'] ?? 0 }}</p>
                                    <p class="text-xs text-base-content/60">Packages</p>
                                </div>
                            </div>

                            {{-- Top Items --}}
                            @if($bookingSummary['top_class'] || $bookingSummary['top_service'])
                            <div class="mt-4 space-y-2">
                                @if($bookingSummary['top_class'])
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-base-200/50">
                                    <span class="icon-[tabler--crown] size-4 text-primary"></span>
                                    <span class="text-xs text-base-content/60">Top Class:</span>
                                    <span class="text-sm font-medium truncate">{{ $bookingSummary['top_class'] }}</span>
                                    <span class="badge badge-primary badge-xs ml-auto">{{ $bookingSummary['top_class_count'] }}x</span>
                                </div>
                                @endif
                                @if($bookingSummary['top_service'])
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-base-200/50">
                                    <span class="icon-[tabler--crown] size-4 text-secondary"></span>
                                    <span class="text-xs text-base-content/60">Top Service:</span>
                                    <span class="text-sm font-medium truncate">{{ $bookingSummary['top_service'] }}</span>
                                    <span class="badge badge-secondary badge-xs ml-auto">{{ $bookingSummary['top_service_count'] }}x</span>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>

                        {{-- Right Column - Breakdown Lists (3 cols = 60%) --}}
                        <div class="col-span-3">
                            <div class="grid grid-cols-2 gap-4">
                                {{-- Classes Breakdown --}}
                                <div>
                                    <h3 class="text-xs font-semibold text-base-content/70 uppercase tracking-wide mb-2">Classes</h3>
                                    @if($bookingSummary['classes']->count() > 0)
                                    <div class="space-y-1">
                                        @foreach($bookingSummary['classes']->take(5) as $className => $data)
                                        <div class="flex items-center justify-between gap-2 px-2 py-1 rounded bg-base-200/50 text-xs">
                                            <span>{{ $className }}</span>
                                            <span class="badge badge-primary badge-xs shrink-0">{{ $data['count'] }}</span>
                                        </div>
                                        @endforeach
                                        @if($bookingSummary['classes']->count() > 5)
                                        <p class="text-xs text-base-content/50">+ {{ $bookingSummary['classes']->count() - 5 }} more</p>
                                        @endif
                                    </div>
                                    @else
                                    <p class="text-xs text-base-content/40">No classes yet</p>
                                    @endif
                                </div>

                                {{-- Services Breakdown --}}
                                <div>
                                    <h3 class="text-xs font-semibold text-base-content/70 uppercase tracking-wide mb-2">Services</h3>
                                    @if($bookingSummary['services']->count() > 0)
                                    <div class="space-y-1">
                                        @foreach($bookingSummary['services']->take(5) as $serviceName => $data)
                                        <div class="flex items-center justify-between gap-2 px-2 py-1 rounded bg-base-200/50 text-xs">
                                            <span>{{ $serviceName }}</span>
                                            <span class="badge badge-secondary badge-xs shrink-0">{{ $data['count'] }}</span>
                                        </div>
                                        @endforeach
                                        @if($bookingSummary['services']->count() > 5)
                                        <p class="text-xs text-base-content/50">+ {{ $bookingSummary['services']->count() - 5 }} more</p>
                                        @endif
                                    </div>
                                    @else
                                    <p class="text-xs text-base-content/40">No services yet</p>
                                    @endif
                                </div>

                                {{-- Memberships Breakdown --}}
                                <div>
                                    <h3 class="text-xs font-semibold text-base-content/70 uppercase tracking-wide mb-2">Memberships</h3>
                                    @if(isset($bookingSummary['memberships']) && $bookingSummary['memberships']->count() > 0)
                                    <div class="space-y-1">
                                        @foreach($bookingSummary['memberships']->take(5) as $membershipName => $data)
                                        <div class="flex items-center justify-between gap-2 px-2 py-1 rounded bg-base-200/50 text-xs">
                                            <span>{{ $membershipName }}</span>
                                            <div class="flex items-center gap-1 shrink-0">
                                                @if($data['active'] > 0)
                                                <span class="badge badge-success badge-xs">Active</span>
                                                @endif
                                                <span class="badge badge-warning badge-xs">{{ $data['count'] }}</span>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <p class="text-xs text-base-content/40">No memberships yet</p>
                                    @endif
                                </div>

                                {{-- Catalog Items Breakdown --}}
                                <div>
                                    <h3 class="text-xs font-semibold text-base-content/70 uppercase tracking-wide mb-2">Packages & Credits</h3>
                                    @if(isset($bookingSummary['catalog']) && $bookingSummary['catalog']->count() > 0)
                                    <div class="space-y-1">
                                        @foreach($bookingSummary['catalog']->take(5) as $itemName => $data)
                                        <div class="flex items-center justify-between gap-2 px-2 py-1 rounded bg-base-200/50 text-xs">
                                            <span>{{ $itemName }}</span>
                                            <span class="badge badge-info badge-xs shrink-0">{{ $data['count'] }}</span>
                                        </div>
                                        @endforeach
                                        @if($bookingSummary['catalog']->count() > 5)
                                        <p class="text-xs text-base-content/50">+ {{ $bookingSummary['catalog']->count() - 5 }} more</p>
                                        @endif
                                    </div>
                                    @else
                                    <p class="text-xs text-base-content/40">No packages yet</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--calendar-event] size-5"></span>
                            All Bookings
                        </h2>
                    </div>

                    {{-- Booking Stats --}}
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
                        <div class="stat bg-base-200/50 rounded-lg p-3">
                            <div class="stat-title text-xs">Total</div>
                            <div class="stat-value text-xl">{{ $bookingStats['total'] }}</div>
                        </div>
                        <div class="stat bg-base-200/50 rounded-lg p-3">
                            <div class="stat-title text-xs">Confirmed</div>
                            <div class="stat-value text-xl text-info">{{ $bookingStats['confirmed'] }}</div>
                        </div>
                        <div class="stat bg-base-200/50 rounded-lg p-3">
                            <div class="stat-title text-xs">Attended</div>
                            <div class="stat-value text-xl text-success">{{ $bookingStats['attended'] }}</div>
                        </div>
                        <div class="stat bg-base-200/50 rounded-lg p-3">
                            <div class="stat-title text-xs">Cancelled</div>
                            <div class="stat-value text-xl text-warning">{{ $bookingStats['cancelled'] }}</div>
                        </div>
                        <div class="stat bg-base-200/50 rounded-lg p-3">
                            <div class="stat-title text-xs">No Show</div>
                            <div class="stat-value text-xl text-error">{{ $bookingStats['no_show'] }}</div>
                        </div>
                    </div>

                    @if($bookings->isEmpty())
                        <div class="text-center py-12">
                            <span class="icon-[tabler--calendar-off] size-12 text-base-content/20 mx-auto"></span>
                            <p class="text-base-content/50 mt-4">No bookings yet</p>
                            <p class="text-sm text-base-content/40">Book a class or service for this client.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Class/Service</th>
                                        <th>Date & Time</th>
                                        <th>Instructor</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookings as $booking)
                                        @php
                                            $isServiceSlot = $booking->bookable instanceof \App\Models\ServiceSlot;
                                            $icon = $isServiceSlot ? 'icon-[tabler--massage]' : 'icon-[tabler--yoga]';
                                            $title = $isServiceSlot
                                                ? ($booking->bookable->servicePlan->name ?? 'Service')
                                                : ($booking->bookable->display_title ?? $booking->bookable->classPlan->name ?? 'Class');
                                            $instructor = $isServiceSlot
                                                ? ($booking->bookable->instructor ?? null)
                                                : ($booking->bookable->primaryInstructor ?? null);
                                        @endphp
                                        <tr class="hover">
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $isServiceSlot ? 'bg-secondary/10' : 'bg-primary/10' }}">
                                                        <span class="{{ $icon }} size-5 {{ $isServiceSlot ? 'text-secondary' : 'text-primary' }}"></span>
                                                    </div>
                                                    <div>
                                                        <div class="font-medium">{{ $title }}</div>
                                                        <div class="text-xs text-base-content/50">
                                                            {{ $isServiceSlot ? 'Service' : 'Class' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($booking->bookable && $booking->bookable->start_time)
                                                    <div class="font-medium">{{ $booking->bookable->start_time->format('M j, Y') }}</div>
                                                    <div class="text-sm text-base-content/60">{{ $booking->bookable->start_time->format('g:i A') }}</div>
                                                @else
                                                    <span class="text-base-content/40">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($instructor)
                                                    {{ $instructor->name }}
                                                @else
                                                    <span class="text-base-content/40">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-sm {{ $booking->status_badge_class }} badge-soft">
                                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                                </span>
                                                @if($booking->checked_in_at)
                                                    <span class="badge badge-xs badge-success ml-1">Checked In</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($booking->price_paid)
                                                    <span class="font-medium">${{ number_format($booking->price_paid, 2) }}</span>
                                                    <div class="text-xs text-base-content/50">{{ ucfirst($booking->payment_method ?? 'N/A') }}</div>
                                                @else
                                                    <span class="text-base-content/40">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-ghost btn-xs btn-square" title="View Details" onclick="openDrawer('booking-{{ $booking->id }}', event)">
                                                    <span class="icon-[tabler--eye] size-4"></span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Questionnaires Tab --}}
        <div class="tab-content hidden" data-content="questionnaires">
            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="card-title text-lg">Questionnaire Responses</h2>
                        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('send-questionnaire-modal').showModal()">
                            <span class="icon-[tabler--send] size-4"></span>
                            Send Questionnaire
                        </button>
                    </div>

                    @if($questionnaireResponses->isEmpty())
                        <div class="text-center py-12">
                            <span class="icon-[tabler--forms] size-12 text-base-content/20 mx-auto"></span>
                            <p class="text-base-content/50 mt-4">No questionnaires sent yet</p>
                            <p class="text-sm text-base-content/40">Send a questionnaire to collect intake information.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Questionnaire</th>
                                        <th>For Class/Service</th>
                                        <th>Status</th>
                                        <th>Sent</th>
                                        <th>Completed</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($questionnaireResponses as $response)
                                        @php
                                            $bookingInfo = null;
                                            $bookingIcon = 'icon-[tabler--calendar]';
                                            if ($response->booking && $response->booking->bookable) {
                                                $bookable = $response->booking->bookable;
                                                if ($bookable instanceof \App\Models\ServiceSlot) {
                                                    $bookingInfo = $bookable->servicePlan->name ?? 'Service';
                                                    $bookingIcon = 'icon-[tabler--massage]';
                                                } elseif ($bookable instanceof \App\Models\ClassSession) {
                                                    $bookingInfo = $bookable->classPlan->name ?? $bookable->display_title ?? 'Class';
                                                    $bookingIcon = 'icon-[tabler--yoga]';
                                                }
                                            }
                                        @endphp
                                        <tr class="hover">
                                            <td class="font-medium">
                                                {{ $response->version?->questionnaire?->name ?? 'Unknown' }}
                                            </td>
                                            <td>
                                                @if($bookingInfo)
                                                    <div class="flex items-center gap-2">
                                                        <span class="{{ $bookingIcon }} size-4 text-base-content/50"></span>
                                                        <div>
                                                            <div class="font-medium text-sm">{{ $bookingInfo }}</div>
                                                            @if($response->booking->bookable->start_time)
                                                                <div class="text-xs text-base-content/50">
                                                                    {{ $response->booking->bookable->start_time->format('M j, Y \a\t g:i A') }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-base-content/40 text-sm">General / Not linked</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ \App\Models\QuestionnaireResponse::getStatusBadgeClass($response->status) }}">
                                                    {{ \App\Models\QuestionnaireResponse::getStatuses()[$response->status] ?? $response->status }}
                                                </span>
                                            </td>
                                            <td class="text-sm">{{ $response->created_at->format('M j, Y') }}</td>
                                            <td class="text-sm">
                                                @if($response->completed_at)
                                                    {{ $response->completed_at->format('M j, Y') }}
                                                @else
                                                    <span class="text-base-content/40">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($response->isCompleted())
                                                    <a href="{{ route('questionnaires.responses.show', [$response->version->questionnaire, $response]) }}" class="btn btn-ghost btn-xs">
                                                        <span class="icon-[tabler--eye] size-4"></span>
                                                        View
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-ghost btn-xs" onclick="copyLink('{{ $response->getResponseUrl() }}')">
                                                        <span class="icon-[tabler--link] size-4"></span>
                                                        Copy Link
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Notes Tab --}}
        <div class="tab-content hidden" data-content="notes">
            <div class="card bg-base-100">
                <div class="card-body">
                    {{-- Add Note Form --}}
                    <div class="border border-base-300 rounded-xl p-5 mb-6 bg-gradient-to-br from-primary/5 to-transparent">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <span class="icon-[tabler--message-plus] size-5 text-primary"></span>
                            </div>
                            <div>
                                <h3 class="font-semibold">Add New Note</h3>
                                <p class="text-xs text-base-content/50">Record interactions with this client</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('clients.note', $client) }}">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <textarea id="content" name="content" rows="3"
                                        class="textarea textarea-bordered w-full text-base"
                                        placeholder="What would you like to note about this client?" required></textarea>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-base-content/60">Type:</span>
                                        <div class="flex gap-1">
                                            <label class="cursor-pointer">
                                                <input type="radio" name="note_type" value="note" class="hidden peer" checked>
                                                <span class="badge badge-outline peer-checked:badge-primary peer-checked:text-primary-content transition-all">
                                                    <span class="icon-[tabler--note] size-3 mr-1"></span> Note
                                                </span>
                                            </label>
                                            <label class="cursor-pointer">
                                                <input type="radio" name="note_type" value="call" class="hidden peer">
                                                <span class="badge badge-outline peer-checked:badge-primary peer-checked:text-primary-content transition-all">
                                                    <span class="icon-[tabler--phone] size-3 mr-1"></span> Call
                                                </span>
                                            </label>
                                            <label class="cursor-pointer">
                                                <input type="radio" name="note_type" value="email" class="hidden peer">
                                                <span class="badge badge-outline peer-checked:badge-primary peer-checked:text-primary-content transition-all">
                                                    <span class="icon-[tabler--mail] size-3 mr-1"></span> Email
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <span class="icon-[tabler--send] size-4"></span>
                                        Add Note
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Notes List --}}
                    @if($client->clientNotes->count() > 0)
                        <div class="space-y-4">
                            @foreach($client->clientNotes as $note)
                                <div class="flex gap-3 p-4 rounded-lg bg-base-200/50 border border-base-300">
                                    <span class="{{ \App\Models\ClientNote::getNoteTypeIcon($note->note_type) }} size-5 mt-0.5 shrink-0 text-base-content/60"></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm">{{ $note->content }}</p>
                                        <p class="text-xs text-base-content/50 mt-2">
                                            <span class="font-medium">{{ $note->author?->full_name ?? 'System' }}</span>
                                            &bull; {{ $note->created_at->format('M d, Y \a\t g:i A') }}
                                            ({{ $note->created_at->diffForHumans() }})
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <span class="icon-[tabler--notes-off] size-12 text-base-content/20 mx-auto"></span>
                            <p class="text-base-content/50 mt-4">No notes yet</p>
                            <p class="text-sm text-base-content/40">Add a note to keep track of interactions with this client.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Progress Tab --}}
        <div class="tab-content hidden" data-content="progress" id="progress">
            <div class="space-y-6">
                {{-- Quick Stats Summary --}}
                @if(isset($latestMeasurement) && $latestMeasurement)
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @if($latestMeasurement->weight)
                    <div class="stat bg-base-100 rounded-lg p-4 border border-base-200">
                        <div class="stat-figure text-primary">
                            <span class="icon-[tabler--scale] size-6"></span>
                        </div>
                        <div class="stat-title text-xs">Weight</div>
                        <div class="stat-value text-2xl">{{ number_format($latestMeasurement->weight, 1) }}</div>
                        <div class="stat-desc">{{ $latestMeasurement->weight_unit }}</div>
                    </div>
                    @endif
                    @if($latestMeasurement->body_fat)
                    <div class="stat bg-base-100 rounded-lg p-4 border border-base-200">
                        <div class="stat-figure text-info">
                            <span class="icon-[tabler--percentage] size-6"></span>
                        </div>
                        <div class="stat-title text-xs">Body Fat</div>
                        <div class="stat-value text-2xl">{{ number_format($latestMeasurement->body_fat, 1) }}%</div>
                        <div class="stat-desc">Latest</div>
                    </div>
                    @endif
                    @if($latestMeasurement->waist)
                    <div class="stat bg-base-100 rounded-lg p-4 border border-base-200">
                        <div class="stat-figure text-warning">
                            <span class="icon-[tabler--ruler-measure] size-6"></span>
                        </div>
                        <div class="stat-title text-xs">Waist</div>
                        <div class="stat-value text-2xl">{{ number_format($latestMeasurement->waist, 1) }}</div>
                        <div class="stat-desc">{{ $latestMeasurement->measurement_unit }}</div>
                    </div>
                    @endif
                    @if($latestMeasurement->chest)
                    <div class="stat bg-base-100 rounded-lg p-4 border border-base-200">
                        <div class="stat-figure text-success">
                            <span class="icon-[tabler--ruler-measure] size-6"></span>
                        </div>
                        <div class="stat-title text-xs">Chest</div>
                        <div class="stat-value text-2xl">{{ number_format($latestMeasurement->chest, 1) }}</div>
                        <div class="stat-desc">{{ $latestMeasurement->measurement_unit }}</div>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Body Measurements Card --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--ruler-measure] size-5"></span>
                                Body Measurements
                            </h2>
                            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('add-measurement-modal').classList.remove('hidden')">
                                <span class="icon-[tabler--plus] size-4"></span>
                                Add Measurement
                            </button>
                        </div>

                        @if(isset($measurements) && $measurements->count() > 0)
                            {{-- Measurements Chart --}}
                            @if(isset($measurementChartData) && $measurementChartData->count() > 1)
                            <div class="mb-6">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="text-sm font-medium">Progress Chart</span>
                                    <select id="measurement-chart-field" class="select select-sm select-bordered" onchange="updateMeasurementChart()">
                                        <option value="weight">Weight</option>
                                        <option value="body_fat">Body Fat %</option>
                                        <option value="waist">Waist</option>
                                        <option value="chest">Chest</option>
                                    </select>
                                </div>
                                <div class="h-48 bg-base-200/30 rounded-lg">
                                    <div id="measurementChart"></div>
                                </div>
                            </div>
                            @endif

                            {{-- Measurements Table --}}
                            <div class="overflow-x-auto">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Weight</th>
                                            <th>Body Fat</th>
                                            <th>Waist</th>
                                            <th>Chest</th>
                                            <th>Hips</th>
                                            <th>Notes</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($measurements as $measurement)
                                        <tr class="hover">
                                            <td class="font-medium">{{ $measurement->measured_at->format('M j, Y') }}</td>
                                            <td>
                                                @if($measurement->weight)
                                                    {{ number_format($measurement->weight, 1) }} {{ $measurement->weight_unit }}
                                                    @php
                                                        $weightChange = $measurement->getChangeFromPrevious('weight');
                                                    @endphp
                                                    @if($weightChange)
                                                        <span class="text-xs {{ $weightChange['direction'] === 'down' ? 'text-success' : ($weightChange['direction'] === 'up' ? 'text-error' : 'text-base-content/50') }}">
                                                            {{ $weightChange['direction'] === 'down' ? '-' : '+' }}{{ abs($weightChange['change']) }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-base-content/30">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($measurement->body_fat)
                                                    {{ number_format($measurement->body_fat, 1) }}%
                                                @else
                                                    <span class="text-base-content/30">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($measurement->waist)
                                                    {{ number_format($measurement->waist, 1) }} {{ $measurement->measurement_unit }}
                                                @else
                                                    <span class="text-base-content/30">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($measurement->chest)
                                                    {{ number_format($measurement->chest, 1) }} {{ $measurement->measurement_unit }}
                                                @else
                                                    <span class="text-base-content/30">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($measurement->hips)
                                                    {{ number_format($measurement->hips, 1) }} {{ $measurement->measurement_unit }}
                                                @else
                                                    <span class="text-base-content/30">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($measurement->notes)
                                                    <span class="tooltip" data-tip="{{ $measurement->notes }}">
                                                        <span class="icon-[tabler--notes] size-4 text-base-content/50"></span>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <details class="dropdown dropdown-end">
                                                    <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                                                        <span class="icon-[tabler--dots-vertical] size-4"></span>
                                                    </summary>
                                                    <ul class="dropdown-content menu bg-base-100 rounded-box w-40 p-2 shadow-lg border border-base-300 z-50">
                                                        <li>
                                                            <button type="button" onclick="viewMeasurementDetails({{ $measurement->id }})">
                                                                <span class="icon-[tabler--eye] size-4"></span> View Details
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('clients.measurements.destroy', [$client->id, $measurement->id]) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this measurement?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                                                                    <span class="icon-[tabler--trash] size-4"></span> Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </details>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($measurements->hasPages())
                                <div class="mt-4 pt-4 border-t border-base-200">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-base-content/60">
                                            Showing {{ $measurements->firstItem() }}-{{ $measurements->lastItem() }} of {{ $measurements->total() }}
                                        </span>
                                        <div class="join">
                                            @if($measurements->onFirstPage())
                                                <button class="join-item btn btn-sm btn-disabled"><span class="icon-[tabler--chevron-left] size-4"></span></button>
                                            @else
                                                <a href="{{ $measurements->previousPageUrl() }}" class="join-item btn btn-sm"><span class="icon-[tabler--chevron-left] size-4"></span></a>
                                            @endif
                                            <button class="join-item btn btn-sm btn-active">{{ $measurements->currentPage() }}</button>
                                            @if($measurements->hasMorePages())
                                                <a href="{{ $measurements->nextPageUrl() }}" class="join-item btn btn-sm"><span class="icon-[tabler--chevron-right] size-4"></span></a>
                                            @else
                                                <button class="join-item btn btn-sm btn-disabled"><span class="icon-[tabler--chevron-right] size-4"></span></button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-12">
                                <span class="icon-[tabler--ruler-off] size-12 text-base-content/20 mx-auto"></span>
                                <p class="text-base-content/50 mt-4">No measurements recorded yet</p>
                                <p class="text-sm text-base-content/40">Click "Add Measurement" to start tracking progress.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Progress Reports Card --}}
                @if(isset($progressReports))
                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--chart-line] size-5"></span>
                                Progress Reports
                            </h2>
                        </div>

                        @if($progressReports->count() > 0)
                            <div class="space-y-4">
                                @foreach($progressReports as $report)
                                    <button type="button"
                                            onclick="openProgressDrawer('progress-report-{{ $report->id }}')"
                                            class="w-full text-left border border-base-200 rounded-lg p-4 hover:bg-base-50 hover:border-primary/30 transition-colors cursor-pointer">
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-start gap-4">
                                                <div class="p-3 rounded-xl bg-primary/10">
                                                    <span class="icon-[tabler--{{ $report->template->icon ?? 'chart-line' }}] size-6 text-primary"></span>
                                                </div>
                                                <div>
                                                    <h4 class="font-semibold">{{ $report->template->name }}</h4>
                                                    <p class="text-sm text-base-content/60">
                                                        {{ $report->report_date->format('M j, Y') }}
                                                        @if($report->classSession)
                                                            &bull; {{ $report->classSession->classPlan?->name ?? 'Class Session' }}
                                                        @endif
                                                    </p>
                                                    @if($report->recordedBy)
                                                        <p class="text-xs text-base-content/50 mt-1">
                                                            Recorded by {{ $report->recordedBy->name }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                @if($report->overall_score !== null)
                                                    <div class="text-right">
                                                        <div class="text-2xl font-bold {{ $report->overall_score >= 70 ? 'text-success' : ($report->overall_score >= 40 ? 'text-warning' : 'text-error') }}">
                                                            {{ number_format($report->overall_score, 0) }}%
                                                        </div>
                                                        <div class="text-xs text-base-content/50">Score</div>
                                                    </div>
                                                @endif
                                                <span class="icon-[tabler--eye] size-5 text-base-content/30"></span>
                                            </div>
                                        </div>

                                        @if($report->trainer_notes)
                                            <div class="mt-3 pt-3 border-t border-base-200">
                                                <p class="text-sm text-base-content/70">
                                                    <strong>Notes:</strong> {{ Str::limit($report->trainer_notes, 150) }}
                                                </p>
                                            </div>
                                        @endif
                                    </button>
                                @endforeach
                            </div>

                            @if($progressReports->hasPages())
                                <div class="mt-4 pt-4 border-t border-base-200">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-base-content/60">
                                            Showing {{ $progressReports->firstItem() }}-{{ $progressReports->lastItem() }} of {{ $progressReports->total() }} reports
                                        </span>
                                        <div class="join">
                                            @if($progressReports->onFirstPage())
                                                <button class="join-item btn btn-sm btn-disabled">
                                                    <span class="icon-[tabler--chevron-left] size-4"></span>
                                                </button>
                                            @else
                                                <a href="{{ $progressReports->previousPageUrl() }}" class="join-item btn btn-sm">
                                                    <span class="icon-[tabler--chevron-left] size-4"></span>
                                                </a>
                                            @endif
                                            <button class="join-item btn btn-sm btn-active">{{ $progressReports->currentPage() }}</button>
                                            @if($progressReports->hasMorePages())
                                                <a href="{{ $progressReports->nextPageUrl() }}" class="join-item btn btn-sm">
                                                    <span class="icon-[tabler--chevron-right] size-4"></span>
                                                </a>
                                            @else
                                                <button class="join-item btn btn-sm btn-disabled">
                                                    <span class="icon-[tabler--chevron-right] size-4"></span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-12">
                                <span class="icon-[tabler--chart-line-off] size-12 text-base-content/20 mx-auto"></span>
                                <p class="text-base-content/50 mt-4">No progress reports yet</p>
                                <p class="text-sm text-base-content/40">Progress reports will appear here when recorded from class sessions.</p>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Activity Tab --}}
        <div class="tab-content hidden" data-content="activity">
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Activity Timeline</h2>

                    <div class="space-y-4">
                        {{-- Activity Stats --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="stat bg-base-200/50 rounded-lg p-4">
                                <div class="stat-title text-xs">Total Bookings</div>
                                <div class="stat-value text-2xl">{{ $client->bookings_count ?? 0 }}</div>
                            </div>
                            <div class="stat bg-base-200/50 rounded-lg p-4">
                                <div class="stat-title text-xs">Attended</div>
                                <div class="stat-value text-2xl text-success">{{ $client->attended_count ?? 0 }}</div>
                            </div>
                            <div class="stat bg-base-200/50 rounded-lg p-4">
                                <div class="stat-title text-xs">No Shows</div>
                                <div class="stat-value text-2xl text-error">{{ $client->no_show_count ?? 0 }}</div>
                            </div>
                            <div class="stat bg-base-200/50 rounded-lg p-4">
                                <div class="stat-title text-xs">Cancelled</div>
                                <div class="stat-value text-2xl text-warning">{{ $client->cancelled_count ?? 0 }}</div>
                            </div>
                        </div>

                        {{-- Recent Bookings --}}
                        <div class="divider text-sm text-base-content/50">Recent Bookings</div>

                        @if(isset($bookings) && $bookings->count() > 0)
                            <div class="space-y-3">
                                @foreach($bookings as $booking)
                                    <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg hover:bg-base-200 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $booking->status === 'confirmed' ? 'bg-success/10' : ($booking->status === 'cancelled' ? 'bg-error/10' : 'bg-base-300') }}">
                                                <span class="icon-[tabler--calendar-event] size-5 {{ $booking->status === 'confirmed' ? 'text-success' : ($booking->status === 'cancelled' ? 'text-error' : 'text-base-content/50') }}"></span>
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ $booking->bookable->display_title ?? $booking->bookable->title ?? 'Session' }}</div>
                                                <div class="text-sm text-base-content/60">
                                                    @if($booking->bookable && $booking->bookable->start_time)
                                                        {{ $booking->bookable->start_time->format('M j, Y \a\t g:i A') }}
                                                    @else
                                                        {{ $booking->created_at->format('M j, Y') }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="badge badge-sm {{ $booking->status_badge_class }} badge-soft">
                                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                            </span>
                                            <button type="button" class="btn btn-ghost btn-xs btn-square" title="View Details" onclick="openDrawer('booking-{{ $booking->id }}', event)">
                                                <span class="icon-[tabler--eye] size-4"></span>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($bookings->count() >= 10)
                                <div class="text-center mt-4">
                                    <a href="{{ route('bookings.index', ['search' => $client->email]) }}" class="btn btn-ghost btn-sm">
                                        View All Bookings
                                        <span class="icon-[tabler--arrow-right] size-4"></span>
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-8">
                                <span class="icon-[tabler--calendar-off] size-12 text-base-content/20 mx-auto"></span>
                                <p class="text-base-content/50 mt-4">No bookings yet</p>
                                <p class="text-sm text-base-content/40">Client's booking history will appear here.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Send Questionnaire Modal --}}
<dialog id="send-questionnaire-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Send Questionnaire</h3>

        @php
            $host = auth()->user()->currentHost() ?? auth()->user()->host;
            $availableQuestionnaires = $host->questionnaires()->active()->get();
        @endphp

        @if($availableQuestionnaires->isEmpty())
            <div class="text-center py-4">
                <p class="text-base-content/60">No published questionnaires available.</p>
                <a href="{{ route('questionnaires.create') }}" class="btn btn-primary btn-sm mt-4">Create Questionnaire</a>
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('send-questionnaire-modal').close()">Close</button>
            </div>
        @else
            <form id="send-questionnaire-form">
                @csrf
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                <div class="form-control">
                    <label class="label" for="questionnaire_select">
                        <span class="label-text">Select Questionnaire</span>
                    </label>
                    <select name="questionnaire_id" id="questionnaire_select" class="select select-bordered" required>
                        <option value="">Choose a questionnaire...</option>
                        @foreach($availableQuestionnaires as $q)
                            <option value="{{ $q->id }}">{{ $q->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('send-questionnaire-modal').close()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--link] size-4"></span>
                        Generate Link
                    </button>
                </div>
            </form>
        @endif
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

{{-- Record Progress Modal --}}
@if(isset($todaysClasses) && $todaysClasses->count() > 0 && isset($hasProgressTemplates) && $hasProgressTemplates)
<div id="record-progress-modal" class="fixed inset-0 z-[9999] hidden">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('record-progress-modal').classList.add('hidden')"></div>
    {{-- Modal Content --}}
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-xl shadow-2xl w-full max-w-lg pointer-events-auto">
            <div class="flex items-center justify-between p-4 border-b border-base-200">
                <h3 class="text-lg font-bold">Record Progress for {{ $client->first_name }}</h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="document.getElementById('record-progress-modal').classList.add('hidden')">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
            <div class="p-4 max-h-[70vh] overflow-y-auto">
                <p class="text-sm text-base-content/60 mb-4">Select a class from today to record progress.</p>
                <div class="space-y-3">
                    @foreach($todaysClasses as $classSession)
                        @if($classSession->classPlan && $classSession->classPlan->progressTemplates->count() > 0)
                        <div class="border border-base-300 rounded-lg p-4 hover:border-primary/50 hover:bg-primary/5 transition-colors">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <span class="icon-[tabler--yoga] size-5 text-primary"></span>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold">{{ $classSession->classPlan->name }}</div>
                                    <div class="text-sm text-base-content/60">
                                        {{ $classSession->start_time->format('g:i A') }} - {{ $classSession->end_time->format('g:i A') }}
                                        @if($classSession->primaryInstructor)
                                            &bull; {{ $classSession->primaryInstructor->name }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($classSession->classPlan->progressTemplates as $template)
                                    <a href="{{ route('class-sessions.record-progress', [$classSession, $template]) }}?client={{ $client->id }}"
                                       class="btn btn-sm btn-primary">
                                        <span class="icon-[tabler--{{ $template->icon ?? 'chart-line' }}] size-4"></span>
                                        {{ $template->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="flex justify-end p-4 border-t border-base-200">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('record-progress-modal').classList.add('hidden')">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Add Measurement Modal --}}
<div id="add-measurement-modal" class="fixed inset-0 z-[9999] hidden">
    <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('add-measurement-modal').classList.add('hidden')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-xl shadow-2xl w-full max-w-2xl pointer-events-auto max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-4 border-b border-base-200">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <span class="icon-[tabler--ruler-measure] size-5 text-primary"></span>
                    Record Body Measurements
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="document.getElementById('add-measurement-modal').classList.add('hidden')">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
            <form action="{{ route('clients.measurements.store', $client->id) }}" method="POST">
                @csrf
                <div class="p-4 space-y-6">
                    {{-- Date --}}
                    <div>
                        <label class="label-text" for="measured_at">Measurement Date</label>
                        <input type="date" id="measured_at" name="measured_at" value="{{ date('Y-m-d') }}" class="input w-full" required>
                    </div>

                    {{-- Weight & Body Fat --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label-text" for="weight">Weight</label>
                            <div class="flex gap-2">
                                <input type="number" id="weight" name="weight" step="0.1" min="0" max="500" class="input flex-1" placeholder="e.g. 70.5">
                                <select name="weight_unit" class="select w-20">
                                    <option value="kg">kg</option>
                                    <option value="lbs">lbs</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="label-text" for="body_fat">Body Fat %</label>
                            <div class="flex gap-2 items-center">
                                <input type="number" id="body_fat" name="body_fat" step="0.1" min="0" max="100" class="input flex-1" placeholder="e.g. 18.5">
                                <span class="text-base-content/60">%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Measurement Unit Selector --}}
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-medium">Body measurements in:</span>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="measurement_unit" value="cm" class="radio radio-sm radio-primary" checked>
                            <span class="text-sm">Centimeters (cm)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="measurement_unit" value="in" class="radio radio-sm radio-primary">
                            <span class="text-sm">Inches (in)</span>
                        </label>
                    </div>

                    {{-- Upper Body --}}
                    <div>
                        <h4 class="font-medium text-sm text-base-content/70 mb-3 flex items-center gap-2">
                            <span class="icon-[tabler--stretching] size-4"></span>
                            Upper Body
                        </h4>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="label-text text-xs" for="chest">Chest</label>
                                <input type="number" id="chest" name="chest" step="0.1" min="0" max="300" class="input input-sm w-full" placeholder="0.0">
                            </div>
                            <div>
                                <label class="label-text text-xs" for="shoulders">Shoulders</label>
                                <input type="number" id="shoulders" name="shoulders" step="0.1" min="0" max="300" class="input input-sm w-full" placeholder="0.0">
                            </div>
                            <div>
                                <label class="label-text text-xs" for="neck">Neck</label>
                                <input type="number" id="neck" name="neck" step="0.1" min="0" max="100" class="input input-sm w-full" placeholder="0.0">
                            </div>
                        </div>
                    </div>

                    {{-- Core --}}
                    <div>
                        <h4 class="font-medium text-sm text-base-content/70 mb-3 flex items-center gap-2">
                            <span class="icon-[tabler--activity] size-4"></span>
                            Core
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label-text text-xs" for="waist">Waist</label>
                                <input type="number" id="waist" name="waist" step="0.1" min="0" max="300" class="input input-sm w-full" placeholder="0.0">
                            </div>
                            <div>
                                <label class="label-text text-xs" for="hips">Hips</label>
                                <input type="number" id="hips" name="hips" step="0.1" min="0" max="300" class="input input-sm w-full" placeholder="0.0">
                            </div>
                        </div>
                    </div>

                    {{-- Arms --}}
                    <div>
                        <h4 class="font-medium text-sm text-base-content/70 mb-3 flex items-center gap-2">
                            <span class="icon-[tabler--barbell] size-4"></span>
                            Arms
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label-text text-xs" for="biceps_left">Left Bicep</label>
                                <input type="number" id="biceps_left" name="biceps_left" step="0.1" min="0" max="100" class="input input-sm w-full" placeholder="0.0">
                            </div>
                            <div>
                                <label class="label-text text-xs" for="biceps_right">Right Bicep</label>
                                <input type="number" id="biceps_right" name="biceps_right" step="0.1" min="0" max="100" class="input input-sm w-full" placeholder="0.0">
                            </div>
                        </div>
                    </div>

                    {{-- Legs --}}
                    <div>
                        <h4 class="font-medium text-sm text-base-content/70 mb-3 flex items-center gap-2">
                            <span class="icon-[tabler--walk] size-4"></span>
                            Legs
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label-text text-xs" for="thigh_left">Left Thigh</label>
                                <input type="number" id="thigh_left" name="thigh_left" step="0.1" min="0" max="150" class="input input-sm w-full" placeholder="0.0">
                            </div>
                            <div>
                                <label class="label-text text-xs" for="thigh_right">Right Thigh</label>
                                <input type="number" id="thigh_right" name="thigh_right" step="0.1" min="0" max="150" class="input input-sm w-full" placeholder="0.0">
                            </div>
                            <div>
                                <label class="label-text text-xs" for="calf_left">Left Calf</label>
                                <input type="number" id="calf_left" name="calf_left" step="0.1" min="0" max="100" class="input input-sm w-full" placeholder="0.0">
                            </div>
                            <div>
                                <label class="label-text text-xs" for="calf_right">Right Calf</label>
                                <input type="number" id="calf_right" name="calf_right" step="0.1" min="0" max="100" class="input input-sm w-full" placeholder="0.0">
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="label-text" for="measurement_notes">Notes (optional)</label>
                        <textarea id="measurement_notes" name="notes" rows="2" class="textarea w-full" placeholder="Any notes about this measurement session..."></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 p-4 border-t border-base-200">
                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('add-measurement-modal').classList.add('hidden')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--check] size-4"></span>
                        Save Measurement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Score Calculation Modal --}}
<div id="score-calculation-modal" class="fixed inset-0 z-[9999] hidden">
    <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('score-calculation-modal').classList.add('hidden')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-box shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-4 border-b border-base-200">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <span class="icon-[tabler--calculator] size-5 text-primary"></span>
                    Client Score Calculation
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="document.getElementById('score-calculation-modal').classList.add('hidden')">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
            <div class="p-4 space-y-6">
                {{-- Overview --}}
                <div class="alert alert-info">
                    <span class="icon-[tabler--info-circle] size-5"></span>
                    <div>
                        <p class="font-medium">Overall Score = (Engagement × 40%) + (Usage × 30%) + (Revenue × 30%)</p>
                        <p class="text-sm mt-1">Each component is scored from 0-100, then weighted to calculate the final score.</p>
                    </div>
                </div>

                {{-- This Client's Calculation --}}
                <div class="bg-base-200/50 rounded-lg p-4">
                    <h4 class="font-semibold mb-3">{{ $client->full_name }}'s Score Breakdown</h4>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-primary">{{ $clientScore['engagement']['score'] }}</div>
                            <div class="text-xs text-base-content/60">Engagement × 40%</div>
                            <div class="text-sm font-medium">= {{ round($clientScore['engagement']['score'] * 0.4, 1) }}</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-secondary">{{ $clientScore['usage']['score'] }}</div>
                            <div class="text-xs text-base-content/60">Usage × 30%</div>
                            <div class="text-sm font-medium">= {{ round($clientScore['usage']['score'] * 0.3, 1) }}</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-success">{{ $clientScore['revenue']['score'] }}</div>
                            <div class="text-xs text-base-content/60">Revenue × 30%</div>
                            <div class="text-sm font-medium">= {{ round($clientScore['revenue']['score'] * 0.3, 1) }}</div>
                        </div>
                    </div>
                    <div class="text-center mt-4 pt-4 border-t border-base-300">
                        <span class="text-base-content/60">Total:</span>
                        <span class="text-3xl font-bold ml-2">{{ $clientScore['overall'] }}</span>
                        <span class="badge badge-{{ $clientScore['grade']['color'] }} ml-2">{{ $clientScore['grade']['label'] }}</span>
                    </div>
                </div>

                {{-- Engagement Details --}}
                <div>
                    <h4 class="font-semibold flex items-center gap-2 mb-3">
                        <span class="icon-[tabler--activity-heartbeat] size-5 text-primary"></span>
                        Engagement Score (0-100)
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Factor</th>
                                    <th>Weight</th>
                                    <th>How It's Calculated</th>
                                    <th class="text-right">This Client</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Attendance Rate</td>
                                    <td>40 pts</td>
                                    <td class="text-xs text-base-content/60">(Attended ÷ Booked) × 40</td>
                                    <td class="text-right font-medium">{{ $clientScore['engagement']['attendance_rate'] }}%</td>
                                </tr>
                                <tr>
                                    <td>Recency</td>
                                    <td>30 pts</td>
                                    <td class="text-xs text-base-content/60">≤7d: 30 | ≤14d: 25 | ≤30d: 20 | ≤60d: 10 | ≤90d: 5</td>
                                    <td class="text-right font-medium">{{ $clientScore['engagement']['days_since_visit'] }} days ago</td>
                                </tr>
                                <tr>
                                    <td>Frequency</td>
                                    <td>30 pts</td>
                                    <td class="text-xs text-base-content/60">≥8/mo: 30 | ≥4/mo: 25 | ≥2/mo: 20 | ≥1/mo: 15 | ≥0.5/mo: 10</td>
                                    <td class="text-right font-medium">{{ $clientScore['engagement']['bookings_per_month'] }}/mo</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Usage Details --}}
                <div>
                    <h4 class="font-semibold flex items-center gap-2 mb-3">
                        <span class="icon-[tabler--chart-bar] size-5 text-secondary"></span>
                        Usage Score (0-100)
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Factor</th>
                                    <th>Weight</th>
                                    <th>How It's Calculated</th>
                                    <th class="text-right">This Client</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Classes Attended</td>
                                    <td>40 pts</td>
                                    <td class="text-xs text-base-content/60">Relative to studio average (2× avg = 40 pts)</td>
                                    <td class="text-right font-medium">{{ $clientScore['usage']['total_classes'] }} classes</td>
                                </tr>
                                <tr>
                                    <td>Services Booked</td>
                                    <td>30 pts</td>
                                    <td class="text-xs text-base-content/60">10 services = 30 pts (max)</td>
                                    <td class="text-right font-medium">{{ $clientScore['usage']['total_services'] }} services</td>
                                </tr>
                                <tr>
                                    <td>Membership Status</td>
                                    <td>30 pts</td>
                                    <td class="text-xs text-base-content/60">Active: 30 | Paused: 15 | Member status: 20 | Client: 10</td>
                                    <td class="text-right font-medium">{{ ucfirst($clientScore['usage']['membership_status']) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Revenue Details --}}
                <div>
                    <h4 class="font-semibold flex items-center gap-2 mb-3">
                        <span class="icon-[tabler--currency-dollar] size-5 text-success"></span>
                        Revenue Score (0-100)
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Factor</th>
                                    <th>How It's Calculated</th>
                                    <th class="text-right">This Client</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Lifetime Value vs Average</td>
                                    <td class="text-xs text-base-content/60">≥2× avg: 100 | ≥1.5×: 85 | ≥1×: 70 | ≥0.75×: 55 | ≥0.5×: 40 | ≥0.25×: 25 | >0: 15</td>
                                    <td class="text-right font-medium">${{ number_format($clientScore['revenue']['lifetime_value'], 0) }} (avg: ${{ number_format($clientScore['revenue']['avg_lifetime_value'], 0) }})</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Grade Scale --}}
                <div>
                    <h4 class="font-semibold mb-3">Grade Scale</h4>
                    <div class="flex flex-wrap gap-2">
                        <span class="badge badge-success gap-1"><strong>A+</strong> 90-100</span>
                        <span class="badge badge-success gap-1"><strong>A</strong> 80-89</span>
                        <span class="badge badge-info gap-1"><strong>B</strong> 70-79</span>
                        <span class="badge badge-warning gap-1"><strong>C</strong> 60-69</span>
                        <span class="badge badge-warning gap-1"><strong>D</strong> 50-59</span>
                        <span class="badge badge-error gap-1"><strong>F</strong> 0-49</span>
                    </div>
                </div>
            </div>
            <div class="flex justify-end p-4 border-t border-base-200">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('score-calculation-modal').classList.add('hidden')">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Booking Details Drawers --}}
@if(isset($bookings))
    @foreach($bookings as $booking)
        @include('host.bookings.partials.drawer', ['booking' => $booking])
    @endforeach

    {{-- Cancel Booking Modal (shared) --}}
    @include('host.bookings.partials.cancel-modal-shared')
@endif

{{-- Progress Report Drawers --}}
@if(isset($progressReports) && $progressReports->count() > 0)
<div id="progress-drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="closeProgressDrawer()"></div>

@foreach($progressReports as $report)
<div
    id="drawer-progress-report-{{ $report->id }}"
    class="fixed top-0 right-0 h-full w-full max-w-4xl bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out hidden flex flex-col"
    role="dialog"
    tabindex="-1"
>
    {{-- Header --}}
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-primary/10">
                <span class="icon-[tabler--{{ $report->template->icon ?? 'chart-line' }}] size-5 text-primary"></span>
            </div>
            <div>
                <h3 class="text-lg font-semibold">{{ $report->template->name }}</h3>
                <p class="text-sm text-base-content/60">{{ $report->report_date->format('F d, Y') }}</p>
            </div>
        </div>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" aria-label="Close" onclick="closeProgressDrawer()">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>

    {{-- Body --}}
    <div class="flex-1 overflow-y-auto p-4">
        {{-- Overall Score --}}
        @if($report->overall_score !== null)
        <div class="mb-5">
            <div class="bg-gradient-to-r from-primary/10 to-primary/5 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-base-content/60 uppercase tracking-wide">Overall Score</p>
                        <p class="text-3xl font-bold mt-1 {{ $report->overall_score >= 70 ? 'text-success' : ($report->overall_score >= 40 ? 'text-warning' : 'text-error') }}">
                            {{ number_format($report->overall_score, 1) }}%
                        </p>
                    </div>
                    <div class="radial-progress text-primary" style="--value:{{ min(100, $report->overall_score) }}; --size:4rem;" role="progressbar">
                        <span class="text-sm font-semibold">{{ round($report->overall_score) }}%</span>
                    </div>
                </div>
                <div class="w-full bg-base-300 rounded-full h-2 mt-3">
                    <div class="h-2 rounded-full {{ $report->overall_score >= 70 ? 'bg-success' : ($report->overall_score >= 40 ? 'bg-warning' : 'bg-error') }}"
                         style="width: {{ min(100, $report->overall_score) }}%"></div>
                </div>
            </div>
        </div>
        @endif

        {{-- Before/After Photos --}}
        @php
            $beforePhoto = $report->photos->where('photo_type', 'before')->first();
            $afterPhoto = $report->photos->where('photo_type', 'after')->first();
        @endphp
        @if($beforePhoto || $afterPhoto)
        <div class="mb-5">
            <div class="bg-base-200/50 rounded-xl p-4">
                <h4 class="font-semibold flex items-center gap-2 mb-3">
                    <span class="icon-[tabler--camera] size-4 text-primary"></span>
                    Progress Photos
                </h4>
                <div class="grid grid-cols-2 gap-4">
                    @if($beforePhoto)
                    <div>
                        <p class="text-xs text-base-content/60 uppercase tracking-wide mb-2">Before</p>
                        <a href="{{ $beforePhoto->url }}" target="_blank" class="block">
                            <img src="{{ $beforePhoto->url }}" alt="Before photo" class="w-full h-48 object-cover rounded-lg hover:opacity-90 transition-opacity cursor-zoom-in">
                        </a>
                    </div>
                    @else
                    <div class="flex items-center justify-center h-48 bg-base-300/50 rounded-lg">
                        <div class="text-center">
                            <span class="icon-[tabler--photo-off] size-8 text-base-content/20"></span>
                            <p class="text-xs text-base-content/40 mt-1">No before photo</p>
                        </div>
                    </div>
                    @endif

                    @if($afterPhoto)
                    <div>
                        <p class="text-xs text-base-content/60 uppercase tracking-wide mb-2">After</p>
                        <a href="{{ $afterPhoto->url }}" target="_blank" class="block">
                            <img src="{{ $afterPhoto->url }}" alt="After photo" class="w-full h-48 object-cover rounded-lg hover:opacity-90 transition-opacity cursor-zoom-in">
                        </a>
                    </div>
                    @else
                    <div class="flex items-center justify-center h-48 bg-base-300/50 rounded-lg">
                        <div class="text-center">
                            <span class="icon-[tabler--photo-off] size-8 text-base-content/20"></span>
                            <p class="text-xs text-base-content/40 mt-1">No after photo</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Metrics by Section --}}
        <div class="space-y-4">
            @foreach($report->template->sections as $section)
            <div class="bg-base-200/50 rounded-xl p-4">
                <h4 class="font-semibold flex items-center gap-2 mb-3">
                    <span class="icon-[tabler--{{ $section->icon ?? 'folder' }}] size-4 text-primary"></span>
                    {{ $section->name }}
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($section->metrics as $metric)
                        @php
                            $value = $report->values->where('progress_template_metric_id', $metric->id)->first();
                        @endphp
                        <div class="bg-base-100 p-3 rounded-lg">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-base-content/70">{{ $metric->name }}</span>
                                @if($metric->unit && !in_array($metric->metric_type, ['slider', 'number']))
                                    <span class="text-xs text-base-content/40">{{ $metric->unit }}</span>
                                @endif
                            </div>
                            @if($value)
                                @switch($metric->metric_type)
                                    @case('slider')
                                    @case('number')
                                        <div class="text-lg font-bold text-primary">
                                            {{ number_format($value->value_numeric, ($metric->step ?? 1) < 1 ? 1 : 0) }}
                                            @if($metric->unit)
                                                <span class="text-xs font-normal text-base-content/60">{{ $metric->unit }}</span>
                                            @endif
                                        </div>
                                        @if($metric->metric_type === 'slider' && $metric->min_value !== null && $metric->max_value !== null)
                                            @php
                                                $percentage = (($value->value_numeric - $metric->min_value) / ($metric->max_value - $metric->min_value)) * 100;
                                            @endphp
                                            <div class="w-full bg-base-300 rounded-full h-1.5 mt-1">
                                                <div class="bg-primary h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                            </div>
                                            <div class="flex justify-between text-xs text-base-content/40 mt-0.5">
                                                <span>{{ $metric->min_value }}</span>
                                                <span>{{ $metric->max_value }}</span>
                                            </div>
                                        @endif
                                        @break

                                    @case('rating')
                                        <div class="flex items-center gap-0.5">
                                            @for($i = 1; $i <= ($metric->max_value ?? 5); $i++)
                                                <span class="mask mask-star-2 size-4 {{ $i <= $value->value_numeric ? 'bg-warning' : 'bg-base-300' }}"></span>
                                            @endfor
                                        </div>
                                        <div class="text-xs text-base-content/60">{{ $value->value_numeric }} / {{ $metric->max_value ?? 5 }}</div>
                                        @break

                                    @case('select')
                                        <div class="font-semibold">{{ $value->value_text ?? '-' }}</div>
                                        @break

                                    @case('checkbox_list')
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($value->value_json ?? [] as $item)
                                                <span class="badge badge-primary badge-soft badge-sm">{{ $item }}</span>
                                            @endforeach
                                        </div>
                                        @break

                                    @case('text')
                                    @default
                                        <div class="text-sm">{{ $value->value_text ?: '-' }}</div>
                                        @break
                                @endswitch
                            @else
                                <div class="text-base-content/40 italic text-sm">Not recorded</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        {{-- Trainer Notes --}}
        @if($report->trainer_notes)
        <div class="mt-5">
            <div class="bg-base-200/50 rounded-xl p-4">
                <h4 class="font-semibold flex items-center gap-2 mb-2">
                    <span class="icon-[tabler--notes] size-4 text-primary"></span>
                    Trainer Notes
                </h4>
                <p class="text-base-content/80 whitespace-pre-wrap">{{ $report->trainer_notes }}</p>
            </div>
        </div>
        @endif

        {{-- Meta Info --}}
        <div class="mt-5 pt-4 border-t border-base-200">
            <div class="grid grid-cols-2 gap-3 text-sm">
                @if($report->recordedBy)
                <div>
                    <span class="text-base-content/60">Recorded by</span>
                    <p class="font-medium">{{ $report->recordedBy->name }}</p>
                </div>
                @endif
                @if($report->classSession)
                <div>
                    <span class="text-base-content/60">Class Session</span>
                    <p class="font-medium">{{ $report->classSession->classPlan?->name ?? 'Class Session' }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="p-4 border-t border-base-200"></div>
</div>
@endforeach
@endif

@push('scripts')
<script>
// Simple Toast Notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-[9999] alert alert-${type} shadow-lg max-w-sm animate-in slide-in-from-right`;
    toast.innerHTML = `
        <span class="icon-[tabler--${type === 'success' ? 'check' : 'x'}] size-5"></span>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        showToast('Link copied to clipboard!');
    });
}

// Handle send questionnaire form
document.getElementById('send-questionnaire-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const questionnaireId = formData.get('questionnaire_id');
    const clientId = formData.get('client_id');

    try {
        const response = await fetch(`/questionnaires/${questionnaireId}/responses/create`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ client_id: clientId })
        });

        if (response.redirected) {
            window.location.reload();
        } else {
            window.location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tabs .tab');
    const contents = document.querySelectorAll('.tab-content');

    // Function to switch to a specific tab
    function switchToTab(targetTab) {
        tabs.forEach(t => {
            if (t.dataset.tab === targetTab) {
                t.classList.add('tab-active');
            } else {
                t.classList.remove('tab-active');
            }
        });

        contents.forEach(content => {
            if (content.dataset.content === targetTab) {
                content.classList.remove('hidden');
                content.classList.add('active');
            } else {
                content.classList.add('hidden');
                content.classList.remove('active');
            }
        });
    }

    // Check URL for tab parameter on page load
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    if (activeTab && ['info', 'bookings', 'questionnaires', 'notes', 'progress', 'activity'].includes(activeTab)) {
        switchToTab(activeTab);
    }

    // Tab click handlers
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            switchToTab(this.dataset.tab);
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdowns = document.querySelectorAll('.relative > ul.menu');
        dropdowns.forEach(dropdown => {
            const button = dropdown.previousElementSibling;
            if (!dropdown.classList.contains('hidden') && !dropdown.contains(e.target) && !button.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    });

    // Accordion Toggle Handler (Exclusive - only one open at a time)
    document.querySelectorAll('.accordion-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const content = document.getElementById(this.getAttribute('aria-controls'));
            const icon = this.querySelector('.accordion-icon');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';

            // Close all other accordions first
            document.querySelectorAll('.accordion-toggle').forEach(otherButton => {
                if (otherButton !== this) {
                    const otherContent = document.getElementById(otherButton.getAttribute('aria-controls'));
                    const otherIcon = otherButton.querySelector('.accordion-icon');
                    if (otherContent) {
                        otherContent.classList.add('hidden');
                        otherButton.setAttribute('aria-expanded', 'false');
                        if (otherIcon) otherIcon.style.transform = 'rotate(0deg)';
                    }
                }
            });

            // Toggle current accordion
            if (isExpanded) {
                content.classList.add('hidden');
                this.setAttribute('aria-expanded', 'false');
                icon.style.transform = 'rotate(0deg)';
            } else {
                content.classList.remove('hidden');
                this.setAttribute('aria-expanded', 'true');
                icon.style.transform = 'rotate(180deg)';
            }
        });
    });

    // Quick Note Form AJAX Handler
    const quickNoteForm = document.getElementById('quick-note-form');
    if (quickNoteForm) {
        quickNoteForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const input = document.getElementById('quick-note-input');
            const btn = document.getElementById('quick-note-btn');
            const noteType = quickNoteForm.querySelector('input[name="note_type"]:checked')?.value || 'note';
            const content = input.value.trim();

            if (!content) return;

            btn.disabled = true;
            btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span>';

            try {
                const response = await fetch('{{ route('clients.note', $client) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        note_type: noteType,
                        content: content
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Show success toast
                    showToast(data.message, 'success');

                    // Clear input
                    input.value = '';

                    // Update the latest note display
                    const cardBody = quickNoteForm.closest('.card-body');
                    let latestNote = document.getElementById('latest-note-display');

                    const noteHtml = `
                        <div class="flex gap-3 p-3 mt-4 rounded-lg bg-base-200/50 border border-base-300" id="latest-note-display">
                            <span class="${data.note.icon} size-4 mt-0.5 shrink-0 text-base-content/60"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm">${data.note.content}</p>
                                <p class="text-xs text-base-content/50 mt-1">${data.note.created_at}</p>
                            </div>
                        </div>
                    `;

                    if (latestNote) {
                        latestNote.outerHTML = noteHtml;
                    } else {
                        quickNoteForm.insertAdjacentHTML('afterend', noteHtml);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Failed to add note. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="icon-[tabler--send] size-4"></span> Add Note';
            }
        });
    }
});

// Progress Report Drawer Functions
function openProgressDrawer(id) {
    const drawer = document.getElementById('drawer-' + id);
    const backdrop = document.getElementById('progress-drawer-backdrop');

    if (drawer) {
        // Close any other open progress drawers
        document.querySelectorAll('[id^="drawer-progress-report-"]').forEach(d => {
            if (d.id !== 'drawer-' + id) {
                d.classList.add('translate-x-full', 'hidden');
            }
        });

        backdrop.classList.remove('hidden');
        drawer.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            drawer.classList.remove('translate-x-full');
        }, 10);
    }
}

function closeProgressDrawer() {
    const backdrop = document.getElementById('progress-drawer-backdrop');

    document.querySelectorAll('[id^="drawer-progress-report-"]').forEach(drawer => {
        drawer.classList.add('translate-x-full');
        setTimeout(() => {
            drawer.classList.add('hidden');
        }, 300);
    });

    if (backdrop) {
        backdrop.classList.add('hidden');
    }

    document.body.style.overflow = '';
}

// Close drawer on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeProgressDrawer();
    }
});

// Measurement Chart
let measurementChart = null;
const measurementChartData = @json($measurementChartData ?? []);

function initMeasurementChart() {
    const chartEl = document.getElementById('measurementChart');
    if (!chartEl || measurementChartData.length < 2 || typeof ApexCharts === 'undefined') return;

    const field = document.getElementById('measurement-chart-field')?.value || 'weight';

    const labels = measurementChartData.map(m => m.date);
    const data = measurementChartData.map(m => parseFloat(m[field]) || null).filter(v => v !== null);

    if (measurementChart) {
        measurementChart.destroy();
    }

    const fieldLabels = {
        'weight': 'Weight',
        'body_fat': 'Body Fat %',
        'waist': 'Waist',
        'chest': 'Chest'
    };

    const options = {
        series: [{
            name: fieldLabels[field] || field,
            data: data
        }],
        chart: {
            type: 'area',
            height: 180,
            toolbar: { show: false },
            fontFamily: 'inherit',
        },
        colors: ['#6366f1'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.1,
            }
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: labels,
            labels: {
                style: { fontSize: '11px' }
            }
        },
        yaxis: {
            labels: {
                style: { fontSize: '11px' }
            }
        },
        grid: {
            borderColor: '#e5e7eb',
            strokeDashArray: 3,
        },
        markers: {
            size: 4,
            hover: { size: 6 }
        },
        tooltip: {
            y: {
                formatter: (val) => field === 'body_fat' ? `${val}%` : val
            }
        }
    };

    measurementChart = new ApexCharts(chartEl, options);
    measurementChart.render();
}

function updateMeasurementChart() {
    initMeasurementChart();
}

// Initialize chart on page load if tab is progress
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'progress') {
        setTimeout(initMeasurementChart, 100);
    }

    // Initialize chart when progress tab is clicked
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function() {
            if (this.dataset.tab === 'progress') {
                setTimeout(initMeasurementChart, 100);
            }
        });
    });
});

// View measurement details
function viewMeasurementDetails(measurementId) {
    fetch(`/clients/{{ $client->id }}/measurements/${measurementId}`)
        .then(response => response.json())
        .then(data => {
            const m = data.measurement;
            let details = `Measurement Date: ${new Date(m.measured_at).toLocaleDateString()}\n\n`;
            if (m.weight) details += `Weight: ${m.weight} ${m.weight_unit}\n`;
            if (m.body_fat) details += `Body Fat: ${m.body_fat}%\n`;
            if (m.chest) details += `Chest: ${m.chest} ${m.measurement_unit}\n`;
            if (m.waist) details += `Waist: ${m.waist} ${m.measurement_unit}\n`;
            if (m.hips) details += `Hips: ${m.hips} ${m.measurement_unit}\n`;
            if (m.shoulders) details += `Shoulders: ${m.shoulders} ${m.measurement_unit}\n`;
            if (m.neck) details += `Neck: ${m.neck} ${m.measurement_unit}\n`;
            if (m.biceps_left) details += `Left Bicep: ${m.biceps_left} ${m.measurement_unit}\n`;
            if (m.biceps_right) details += `Right Bicep: ${m.biceps_right} ${m.measurement_unit}\n`;
            if (m.thigh_left) details += `Left Thigh: ${m.thigh_left} ${m.measurement_unit}\n`;
            if (m.thigh_right) details += `Right Thigh: ${m.thigh_right} ${m.measurement_unit}\n`;
            if (m.calf_left) details += `Left Calf: ${m.calf_left} ${m.measurement_unit}\n`;
            if (m.calf_right) details += `Right Calf: ${m.calf_right} ${m.measurement_unit}\n`;
            if (m.notes) details += `\nNotes: ${m.notes}`;

            alert(details);
        })
        .catch(error => {
            console.error('Error:', error);
        });
}
</script>
<script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
@endpush
@endsection
