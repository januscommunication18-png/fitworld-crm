@extends('layouts.dashboard')

@section('title', $event->title)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index') }}"><span class="icon-[tabler--layout-grid] size-4"></span> Classes & Services</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'events']) }}">Events</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ Str::limit($event->title, 30) }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            @if($event->cover_image)
                <img src="{{ $event->cover_image }}" alt="{{ $event->title }}"
                     class="w-24 h-24 rounded-lg object-cover">
            @else
                <div class="w-24 h-24 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--calendar-event] size-10 text-primary"></span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold">{{ $event->title }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="badge badge-soft badge-{{ $event->status_color }}">{{ $event->status_label }}</span>
                    <span class="badge badge-soft badge-sm">
                        @if($event->event_type === 'in_person')
                            <span class="icon-[tabler--map-pin] size-3.5 me-1"></span> In-Person
                        @elseif($event->event_type === 'online')
                            <span class="icon-[tabler--device-laptop] size-3.5 me-1"></span> Online
                        @else
                            <span class="icon-[tabler--arrows-exchange] size-3.5 me-1"></span> Hybrid
                        @endif
                    </span>
                    <span class="badge badge-soft badge-sm">
                        @if($event->visibility === 'public')
                            <span class="icon-[tabler--world] size-3.5 me-1"></span> Public
                        @elseif($event->visibility === 'private')
                            <span class="icon-[tabler--lock] size-3.5 me-1"></span> Members Only
                        @else
                            <span class="icon-[tabler--link] size-3.5 me-1"></span> Unlisted
                        @endif
                    </span>
                    @if($event->skill_level && $event->skill_level !== 'all_levels')
                        <span class="badge badge-soft badge-primary badge-sm">{{ $event->skill_level_label }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            @if($event->is_draft)
                <form action="{{ route('events.publish', $event) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success btn-sm">
                        <span class="icon-[tabler--send] size-4"></span>
                        Publish
                    </button>
                </form>
            @endif
            <a href="{{ route('events.edit', $event) }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--edit] size-4"></span>
                Edit
            </a>
            @if($event->canAddAttendees())
                <a href="{{ route('walk-in.event', $event) }}" class="btn btn-soft btn-sm">
                    <span class="icon-[tabler--user-plus] size-4"></span>
                    Add Attendees
                </a>
            @endif
            <a href="{{ route('catalog.index', ['tab' => 'events']) }}" class="btn btn-ghost btn-sm gap-1.5">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tabs tabs-bordered" role="tablist">
        <button class="tab tab-active" data-tab="overview" role="tab">
            <span class="icon-[tabler--info-circle] size-4 mr-2"></span>Overview
        </button>
        <button class="tab" data-tab="attendees" role="tab">
            <span class="icon-[tabler--users] size-4 mr-2"></span>Attendees
            @if($stats['total_registered'] > 0)
                <span class="badge badge-sm badge-primary ml-1">{{ $stats['total_registered'] }}</span>
            @endif
        </button>
    </div>

    {{-- Tab Contents --}}
    <div class="tab-contents">
        {{-- Overview Tab --}}
        <div class="tab-content active" data-content="overview">
            <div class="space-y-6">

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="card bg-base-100">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-primary/10 rounded-lg p-2">
                                <span class="icon-[tabler--users] size-6 text-primary"></span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ $stats['total_registered'] }}</p>
                                <p class="text-xs text-base-content/60">Registered</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-success/10 rounded-lg p-2">
                                <span class="icon-[tabler--user-check] size-6 text-success"></span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ $stats['attended'] }}</p>
                                <p class="text-xs text-base-content/60">Checked In</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-warning/10 rounded-lg p-2">
                                <span class="icon-[tabler--clock-pause] size-6 text-warning"></span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ $stats['waitlist'] }}</p>
                                <p class="text-xs text-base-content/60">Waitlisted</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-info/10 rounded-lg p-2">
                                <span class="icon-[tabler--ticket] size-6 text-info"></span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ $event->capacity ? $event->spots_remaining : '∞' }}</p>
                                <p class="text-xs text-base-content/60">Spots Left</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Short Description --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--text-caption] size-5"></span>
                        Short Description
                    </h2>
                    @if($event->short_description)
                        <p class="mt-2 text-base-content/80">{{ $event->short_description }}</p>
                    @else
                        <p class="mt-2 text-base-content/40 italic">No short description provided.</p>
                    @endif
                </div>
            </div>

            {{-- Full Description --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--file-description] size-5"></span>
                        Full Description
                    </h2>
                    @if($event->description)
                        <p class="mt-2 whitespace-pre-line">{{ $event->description }}</p>
                    @else
                        <p class="mt-2 text-base-content/40 italic">No full description provided.</p>
                    @endif
                </div>
            </div>

            {{-- Date & Time --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--calendar-event] size-5"></span>
                        Date & Time
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Start Date</label>
                            <p class="font-medium">{{ $event->start_datetime->format('l, F j, Y') }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Start Time</label>
                            <p class="font-medium">{{ $event->start_datetime->format('g:i A') }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">End Date</label>
                            <p class="font-medium">{{ $event->end_datetime->format('l, F j, Y') }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">End Time</label>
                            <p class="font-medium">{{ $event->end_datetime->format('g:i A') }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Timezone</label>
                            <p class="font-medium">{{ $event->timezone }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Duration</label>
                            <p class="font-medium">
                                @php
                                    $diff = $event->start_datetime->diff($event->end_datetime);
                                    $parts = [];
                                    if ($diff->d > 0) $parts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
                                    if ($diff->h > 0) $parts[] = $diff->h . ' hr' . ($diff->h > 1 ? 's' : '');
                                    if ($diff->i > 0) $parts[] = $diff->i . ' min';
                                @endphp
                                {{ implode(' ', $parts) ?: '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Event Type & Location --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--map-pin] size-5"></span>
                        Event Type & Location
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Event Type</label>
                            <p class="font-medium mt-0.5">
                                <span class="badge badge-soft badge-sm">
                                    @if($event->event_type === 'in_person')
                                        <span class="icon-[tabler--map-pin] size-3.5 me-1"></span> In-Person
                                    @elseif($event->event_type === 'online')
                                        <span class="icon-[tabler--device-laptop] size-3.5 me-1"></span> Online
                                    @else
                                        <span class="icon-[tabler--arrows-exchange] size-3.5 me-1"></span> Hybrid
                                    @endif
                                </span>
                            </p>
                        </div>
                        @if($event->event_type !== 'online')
                        <div>
                            <label class="text-sm text-base-content/60">Venue Name</label>
                            <p class="font-medium">{{ $event->venue_name ?: '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Street Address</label>
                            <p class="font-medium">{{ $event->address_line_1 ?: '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">City, State, ZIP</label>
                            <p class="font-medium">
                                @php
                                    $cityStateZip = array_filter([$event->city, $event->state, $event->zip_code]);
                                @endphp
                                {{ $cityStateZip ? implode(', ', $cityStateZip) : '-' }}
                            </p>
                        </div>
                        @endif
                        @if($event->event_type !== 'in_person')
                        <div>
                            <label class="text-sm text-base-content/60">Online Platform</label>
                            <p class="font-medium">{{ $event->online_platform ? ucfirst(str_replace('_', ' ', $event->online_platform)) : '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Event URL</label>
                            @if($event->online_url)
                                <a href="{{ $event->online_url }}" target="_blank" class="text-sm text-primary hover:underline inline-flex items-center gap-1 mt-0.5">
                                    <span class="icon-[tabler--external-link] size-3.5"></span> Join Link
                                </a>
                            @else
                                <p class="font-medium">-</p>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Visibility --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--eye] size-5"></span>
                        Visibility & Access
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Visibility</label>
                            <p class="font-medium mt-0.5">
                                <span class="badge badge-soft badge-sm">
                                    @if($event->visibility === 'public')
                                        <span class="icon-[tabler--world] size-3.5 me-1"></span> Public
                                    @elseif($event->visibility === 'private')
                                        <span class="icon-[tabler--lock] size-3.5 me-1"></span> Members Only
                                    @else
                                        <span class="icon-[tabler--link] size-3.5 me-1"></span> Unlisted
                                    @endif
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Attendee List</label>
                            <p class="mt-0.5">
                                @if($event->hide_attendee_list)
                                    <span class="badge badge-soft badge-neutral badge-sm">Hidden</span>
                                @else
                                    <span class="badge badge-soft badge-info badge-sm">Visible</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Capacity & Audience --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--users-group] size-5"></span>
                        Capacity & Audience
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Capacity</label>
                            <p class="font-medium">{{ $event->capacity ?: 'Unlimited' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Skill Level</label>
                            <p class="font-medium">{{ $event->skill_level_label }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Audience Type</label>
                            <p class="font-medium">
                                @php
                                    $audienceLabels = ['all' => 'All Ages', 'adults' => 'Adults (18+)', 'kids' => 'Kids', 'families' => 'Families', 'seniors' => 'Seniors (60+)'];
                                @endphp
                                {{ $audienceLabels[$event->audience_type] ?? ucfirst($event->audience_type) }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Waitlist</label>
                            <p class="mt-0.5">
                                @if($event->waitlist_enabled)
                                    <span class="badge badge-soft badge-success badge-sm">Enabled</span>
                                @else
                                    <span class="badge badge-soft badge-neutral badge-sm">Disabled</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Event Gallery --}}
            @php $uploadsDisk = config('filesystems.uploads'); @endphp
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--photos] size-5"></span>
                        Event Gallery
                    </h2>
                    @if(is_array($event->gallery_images) && count($event->gallery_images) > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mt-4">
                        @foreach($event->gallery_images as $img)
                            <div class="rounded-xl overflow-hidden aspect-square bg-base-200">
                                <img src="{{ Storage::disk($uploadsDisk)->url($img['path']) }}" alt="{{ $img['name'] ?? '' }}" class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8">
                        <span class="icon-[tabler--photos] size-10 text-base-content/20"></span>
                        <p class="text-base-content/60 mt-2">No gallery images uploaded.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- File Attachments --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--paperclip] size-5"></span>
                        Documents & Attachments
                    </h2>
                    @if(is_array($event->file_attachments) && count($event->file_attachments) > 0)
                        @php
                            $fileIcons = [
                                'pdf' => 'icon-[tabler--file-type-pdf]',
                                'doc' => 'icon-[tabler--file-type-doc]',
                                'docx' => 'icon-[tabler--file-type-doc]',
                                'xls' => 'icon-[tabler--file-type-xls]',
                                'xlsx' => 'icon-[tabler--file-type-xls]',
                                'jpg' => 'icon-[tabler--photo]',
                                'jpeg' => 'icon-[tabler--photo]',
                                'png' => 'icon-[tabler--photo]',
                                'webp' => 'icon-[tabler--photo]',
                            ];
                        @endphp
                        <div class="space-y-2 mt-3">
                            @foreach($event->file_attachments as $file)
                                @php
                                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                                    $icon = $fileIcons[$ext] ?? 'icon-[tabler--file]';
                                    $size = isset($file['size']) ? round($file['size'] / 1024 / 1024, 1) : null;
                                    $url = Storage::disk($uploadsDisk)->url($file['path']);
                                @endphp
                                <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-lg">
                                    <span class="{{ $icon }} size-6 text-base-content/60 shrink-0"></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium truncate">{{ $file['name'] }}</p>
                                        @if($size)
                                            <p class="text-xs text-base-content/50">{{ $size }} MB</p>
                                        @endif
                                    </div>
                                    <a href="{{ $url }}" target="_blank" download="{{ $file['name'] }}" class="btn btn-ghost btn-sm btn-circle">
                                        <span class="icon-[tabler--download] size-5"></span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <span class="icon-[tabler--paperclip] size-10 text-base-content/20"></span>
                            <p class="text-base-content/60 mt-2">No documents uploaded.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Intake Questionnaires --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--forms] size-5"></span>
                        Intake Questionnaires
                    </h2>
                    @if($event->questionnaireAttachments->count() > 0)
                        <div class="space-y-3 mt-4">
                            @foreach($event->questionnaireAttachments as $qa)
                                <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <span class="icon-[tabler--forms] size-5 text-primary"></span>
                                        <div>
                                            <p class="font-medium text-sm">{{ $qa->questionnaire->name }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                @php
                                                    $timingLabels = \App\Models\QuestionnaireAttachment::getCollectionTimings();
                                                    $appliesLabels = \App\Models\QuestionnaireAttachment::getAppliesTo();
                                                @endphp
                                                <span class="badge badge-soft badge-xs">{{ $timingLabels[$qa->collection_timing] ?? $qa->collection_timing }}</span>
                                                <span class="badge badge-soft badge-xs">{{ $appliesLabels[$qa->applies_to] ?? $qa->applies_to }}</span>
                                                @if($qa->is_required)
                                                    <span class="badge badge-soft badge-error badge-xs">Required</span>
                                                @else
                                                    <span class="badge badge-soft badge-neutral badge-xs">Optional</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <span class="icon-[tabler--forms] size-10 text-base-content/20"></span>
                            <p class="text-base-content/60 mt-2">No questionnaires attached.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Stats & Info --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--chart-bar] size-5"></span>
                        Stats & Info
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-base-content/60">Status</label>
                            <p class="mt-0.5">
                                <span class="badge badge-soft badge-{{ $event->status_color }} badge-sm">{{ $event->status_label }}</span>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Views</label>
                            <p class="font-bold text-lg">{{ $event->view_count }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Cancelled Attendees</label>
                            <p class="font-bold text-lg text-error">{{ $stats['cancelled'] }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-base-content/60">Created</label>
                            <p class="font-medium">{{ $event->created_at->format('M d, Y') }}</p>
                        </div>
                        @if($event->published_at)
                        <div>
                            <label class="text-sm text-base-content/60">Published</label>
                            <p class="font-medium">{{ $event->published_at->format('M d, Y') }}</p>
                        </div>
                        @endif
                        @if($event->createdBy)
                        <div>
                            <label class="text-sm text-base-content/60">Created By</label>
                            <p class="font-medium">{{ $event->createdBy->name }}</p>
                        </div>
                        @endif
                        <div>
                            <label class="text-sm text-base-content/60">Slug</label>
                            <p class="font-mono text-xs mt-0.5">{{ $event->slug }}</p>
                        </div>
                    </div>

                    @if($event->status === 'cancelled' && $event->cancellation_reason)
                        <div class="mt-4 p-3 bg-error/5 rounded-lg border border-error/20">
                            <label class="text-sm text-error font-medium">Cancellation Reason</label>
                            <p class="text-sm mt-1">{{ $event->cancellation_reason }}</p>
                        </div>
                    @endif
                </div>
            </div>

            </div>
        </div>

        {{-- Attendees Tab --}}
        <div class="tab-content hidden" data-content="attendees">
            <div class="space-y-6">
                @if($event->canAddAttendees())
                <div class="flex justify-end">
                    <a href="{{ route('walk-in.event', $event) }}" class="btn btn-primary btn-sm gap-2">
                        <span class="icon-[tabler--user-plus] size-4"></span>
                        Add Attendees
                    </a>
                </div>
                @endif

                @if($event->attendees->count() > 0)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Attendee</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($event->attendees->sortByDesc('created_at') as $attendee)
                                        <tr>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="avatar">
                                                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                                            @if($attendee->client->profile_photo)
                                                                <img src="{{ $attendee->client->profile_photo }}" alt="{{ $attendee->client->full_name }}">
                                                            @else
                                                                <span class="text-xs font-medium text-primary">{{ $attendee->client->initials }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('clients.show', $attendee->client) }}" class="font-medium hover:text-primary">
                                                            {{ $attendee->client->full_name }}
                                                        </a>
                                                        <p class="text-xs text-base-content/60">{{ $attendee->client->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-soft badge-sm badge-{{ $attendee->status_color }}">{{ $attendee->status_label }}</span>
                                            </td>
                                            <td class="text-sm text-base-content/60">
                                                {{ $attendee->created_at->format('M j, Y') }}
                                            </td>
                                            <td class="text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    @if($attendee->can_check_in)
                                                        <form action="{{ route('events.checkIn', [$event, $attendee]) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-ghost btn-xs text-success" title="Check In">
                                                                <span class="icon-[tabler--user-check] size-4"></span>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($attendee->can_cancel)
                                                        <form action="{{ route('events.removeClient', [$event, $attendee->client]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this attendee from the event?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-ghost btn-xs text-error" title="Remove">
                                                                <span class="icon-[tabler--x] size-4"></span>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div class="card bg-base-100">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--users] size-16 text-base-content/20 mx-auto mb-4"></span>
                        <h3 class="text-lg font-semibold mb-2">No Attendees Yet</h3>
                        <p class="text-base-content/60 mb-4">No one has registered for this event yet.</p>
                        @if($event->canAddAttendees())
                            <a href="{{ route('walk-in.event', $event) }}" class="btn btn-primary btn-sm gap-2">
                                <span class="icon-[tabler--user-plus] size-4"></span>
                                Add First Attendee
                            </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Cancel Event Modal --}}
<dialog id="cancel-modal" class="modal">
    <div class="modal-box">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </form>
        <h3 class="font-bold text-lg text-error mb-4">Cancel Event</h3>

        <form action="{{ route('events.cancel', $event) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <p class="text-sm text-base-content/60">Are you sure you want to cancel this event? All registered attendees will be notified.</p>

                <div>
                    <label class="label-text" for="cancellation_reason">Cancellation Reason (Optional)</label>
                    <textarea id="cancellation_reason" name="cancellation_reason" rows="3" class="textarea w-full mt-1" placeholder="Let attendees know why the event was cancelled..."></textarea>
                </div>

                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn btn-ghost">Keep Event</button>
                    </form>
                    <button type="submit" class="btn btn-error">Cancel Event</button>
                </div>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var mainTabs = document.querySelectorAll('.tabs.tabs-bordered .tab');
    var mainContents = document.querySelectorAll('.tab-content');

    mainTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var targetTab = this.dataset.tab;

            mainTabs.forEach(function(t) { t.classList.remove('tab-active'); });
            this.classList.add('tab-active');

            mainContents.forEach(function(content) {
                content.classList.toggle('hidden', content.dataset.content !== targetTab);
                content.classList.toggle('active', content.dataset.content === targetTab);
            });
        }.bind(tab));
    });
});
</script>
@endpush
