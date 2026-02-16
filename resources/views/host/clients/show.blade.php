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
                <p class="text-base-content/60">{{ $client->email }}</p>
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
            <span class="icon-[tabler--user] size-4 mr-2"></span>
            Client Info
        </button>
        <button class="tab" data-tab="questionnaires" role="tab">
            <span class="icon-[tabler--forms] size-4 mr-2"></span>
            Questionnaires
            @php
                $questionnaireResponseCount = $client->questionnaireResponses()->count();
            @endphp
            @if($questionnaireResponseCount > 0)
                <span class="badge badge-sm badge-primary ml-2">{{ $questionnaireResponseCount }}</span>
            @endif
        </button>
        <button class="tab" data-tab="notes" role="tab">
            <span class="icon-[tabler--notes] size-4 mr-2"></span>
            Notes
            @if($client->clientNotes->count() > 0)
                <span class="badge badge-sm badge-primary ml-2">{{ $client->clientNotes->count() }}</span>
            @endif
        </button>
        <button class="tab" data-tab="activity" role="tab">
            <span class="icon-[tabler--activity] size-4 mr-2"></span>
            Activity
        </button>
    </div>

    {{-- Tab Content --}}
    <div class="tab-contents relative z-0">
        {{-- Client Info Tab --}}
        <div class="tab-content active" data-content="info">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    {{-- Contact Information --}}
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--address-book] size-5"></span>
                                Contact Information
                            </h2>
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="text-sm text-base-content/60">Email</label>
                                    <p class="font-medium">{{ $client->email }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-base-content/60">Phone</label>
                                    <p class="font-medium">{{ $client->phone ?? 'Not set' }}</p>
                                </div>
                                @if($client->secondary_phone)
                                <div>
                                    <label class="text-sm text-base-content/60">Secondary Phone</label>
                                    <p class="font-medium">{{ $client->secondary_phone }}</p>
                                </div>
                                @endif
                                <div>
                                    <label class="text-sm text-base-content/60">Preferred Contact</label>
                                    <p class="font-medium">{{ \App\Models\Client::getContactMethods()[$client->preferred_contact_method] ?? 'Email' }}</p>
                                </div>
                            </div>
                            @if($client->address_line_1 || $client->city)
                            <div class="divider my-2"></div>
                            <div>
                                <label class="text-sm text-base-content/60">Address</label>
                                <p class="font-medium">
                                    @if($client->address_line_1){{ $client->address_line_1 }}<br>@endif
                                    @if($client->address_line_2){{ $client->address_line_2 }}<br>@endif
                                    @if($client->city){{ $client->city }}, @endif{{ $client->state_province }} {{ $client->postal_code }}
                                    @if($client->country)<br>{{ $client->country }}@endif
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Personal Details --}}
                    @if($client->date_of_birth || $client->gender)
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--user-circle] size-5"></span>
                                Personal Details
                            </h2>
                            <div class="grid grid-cols-2 gap-4 mt-4">
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
                    @endif

                    {{-- Emergency Contact --}}
                    @if($client->emergency_contact_name || $client->emergency_contact_phone)
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--emergency-bed] size-5"></span>
                                Emergency Contact
                            </h2>
                            <div class="grid grid-cols-2 gap-4 mt-4">
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
                    @endif

                    {{-- Health & Fitness --}}
                    @if($client->experience_level || $client->fitness_goals || $client->medical_conditions || $client->injuries || $client->limitations)
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--heartbeat] size-5"></span>
                                Health & Fitness
                            </h2>
                            <div class="grid grid-cols-2 gap-4 mt-4">
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
                            </div>
                            @if($client->fitness_goals)
                            <div class="mt-4">
                                <label class="text-sm text-base-content/60">Fitness Goals</label>
                                <p class="font-medium mt-1">{{ $client->fitness_goals }}</p>
                            </div>
                            @endif
                            @if($client->medical_conditions)
                            <div class="mt-4">
                                <label class="text-sm text-base-content/60">Medical Conditions</label>
                                <p class="font-medium mt-1 text-warning">{{ $client->medical_conditions }}</p>
                            </div>
                            @endif
                            @if($client->injuries)
                            <div class="mt-4">
                                <label class="text-sm text-base-content/60">Injuries</label>
                                <p class="font-medium mt-1 text-warning">{{ $client->injuries }}</p>
                            </div>
                            @endif
                            @if($client->limitations)
                            <div class="mt-4">
                                <label class="text-sm text-base-content/60">Limitations</label>
                                <p class="font-medium mt-1 text-warning">{{ $client->limitations }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Custom Fields --}}
                    @if($customFields['sections']->count() > 0 || $customFields['unsectionedFields']->count() > 0)
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--forms] size-5"></span>
                                Additional Information
                            </h2>

                            @foreach($customFields['sections'] as $section)
                                @if($section->activeFieldDefinitions->count() > 0)
                                <div class="mt-4">
                                    <h3 class="font-semibold text-sm text-base-content/70 uppercase tracking-wider mb-3">{{ $section->name }}</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        @foreach($section->activeFieldDefinitions as $field)
                                            @php
                                                $value = $customFields['values'][$field->id] ?? null;
                                            @endphp
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
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                @foreach($customFields['unsectionedFields'] as $field)
                                    @php
                                        $value = $customFields['values'][$field->id] ?? null;
                                    @endphp
                                    <div>
                                        <label class="text-sm text-base-content/60">{{ $field->field_label }}</label>
                                        <p class="font-medium">{{ $value?->formatted_value ?? 'Not set' }}</p>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
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

                    @php
                        $responses = $client->questionnaireResponses()
                            ->with(['version.questionnaire'])
                            ->latest()
                            ->get();
                    @endphp

                    @if($responses->isEmpty())
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
                                        <th>Status</th>
                                        <th>Sent</th>
                                        <th>Completed</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($responses as $response)
                                        <tr class="hover">
                                            <td class="font-medium">
                                                {{ $response->version?->questionnaire?->name ?? 'Unknown' }}
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

{{-- Booking Details Drawers --}}
@if(isset($bookings))
    @foreach($bookings as $booking)
        @include('host.bookings.partials.drawer', ['booking' => $booking])
    @endforeach

    {{-- Cancel Booking Modal (shared) --}}
    @include('host.bookings.partials.cancel-modal-shared')
@endif

@push('scripts')
<script>
function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copied to clipboard!');
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
    if (activeTab && ['info', 'questionnaires', 'notes', 'activity'].includes(activeTab)) {
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
});
</script>
@endpush
@endsection
