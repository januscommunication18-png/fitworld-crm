@extends('layouts.dashboard')

@section('title', 'Booking Details')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('bookings.index') }}"><span class="icon-[tabler--book] me-1 size-4"></span> Bookings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Booking #{{ $booking->id }}</li>
    </ol>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('bookings.index') }}" class="btn btn-ghost btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <h1 class="text-2xl font-bold">Booking Details</h1>
                <p class="text-base-content/60">Booking #{{ $booking->id }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="badge badge-lg {{ $booking->status_badge_class }}">
                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
            </span>
            @if($booking->isCheckedIn())
                <span class="badge badge-lg badge-success">
                    <span class="icon-[tabler--check] size-4 mr-1"></span>
                    Checked In
                </span>
            @endif
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Intake Form Warning --}}
    @if($booking->intake_status === 'pending')
        <div class="alert alert-warning">
            <span class="icon-[tabler--file-alert] size-5"></span>
            <div class="flex-1">
                <div class="font-semibold">Intake Form Pending</div>
                <div class="text-sm opacity-80">Client has not completed the required intake form(s).</div>
            </div>
            @if($booking->client && $booking->client->email)
                <form action="{{ route('bookings.resend-intake', $booking) }}" method="POST" class="ml-auto">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-warning">
                        <span class="icon-[tabler--mail-forward] size-4"></span>
                        Resend Email
                    </button>
                </form>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Class/Service Info --}}
            <div class="card bg-base-100 border border-base-200">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <span class="icon-[tabler--calendar-event] size-5"></span>
                        Session Details
                    </h2>

                    @if($booking->bookable)
                        <div class="flex items-start gap-4">
                            @php
                                $color = $booking->bookable->classPlan->color ?? '#6366f1';
                            @endphp
                            <div class="size-14 rounded-lg flex items-center justify-center shrink-0" style="background-color: {{ $color }}20;">
                                <span class="icon-[tabler--yoga] size-7" style="color: {{ $color }};"></span>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold">{{ $booking->bookable->display_title ?? $booking->bookable->title ?? 'Class Session' }}</h3>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-base-content/70">
                                    <span class="flex items-center gap-1">
                                        <span class="icon-[tabler--calendar] size-4"></span>
                                        {{ $booking->bookable->start_time->format('l, F j, Y') }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="icon-[tabler--clock] size-4"></span>
                                        {{ $booking->bookable->start_time->format('g:i A') }} - {{ $booking->bookable->end_time->format('g:i A') }}
                                    </span>
                                </div>
                                @if($booking->bookable->primaryInstructor)
                                    <div class="flex items-center gap-1 mt-2 text-base-content/70">
                                        <span class="icon-[tabler--user] size-4"></span>
                                        {{ $booking->bookable->primaryInstructor->name }}
                                    </div>
                                @endif
                                @if($booking->bookable->location)
                                    <div class="flex items-center gap-1 mt-1 text-base-content/70">
                                        <span class="icon-[tabler--map-pin] size-4"></span>
                                        {{ $booking->bookable->location->name }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-base-200">
                            <a href="{{ route('class-sessions.show', $booking->bookable) }}" class="btn btn-outline btn-sm">
                                <span class="icon-[tabler--external-link] size-4"></span>
                                View Session
                            </a>
                        </div>
                    @else
                        <div class="text-center py-6 text-base-content/50">
                            <span class="icon-[tabler--calendar-off] size-8 mx-auto mb-2"></span>
                            <p>Session has been deleted</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Payment Info --}}
            <div class="card bg-base-100 border border-base-200">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <span class="icon-[tabler--credit-card] size-5"></span>
                        Payment Details
                    </h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-base-content/60">Payment Method</div>
                            <div class="font-medium mt-1">
                                @php
                                    $methodLabels = [
                                        'stripe' => 'Credit Card (Stripe)',
                                        'membership' => 'Membership',
                                        'pack' => 'Class Pack',
                                        'manual' => 'Manual Payment',
                                        'cash' => 'Cash',
                                        'comp' => 'Complimentary',
                                    ];
                                @endphp
                                {{ $methodLabels[$booking->payment_method] ?? ucfirst($booking->payment_method) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-base-content/60">Amount Paid</div>
                            <div class="font-medium mt-1 text-lg">
                                @if($booking->price_paid > 0)
                                    ${{ number_format($booking->price_paid, 2) }}
                                @elseif($booking->payment_method === 'comp')
                                    <span class="text-success">Complimentary</span>
                                @elseif($booking->payment_method === 'membership')
                                    <span class="text-info">Membership</span>
                                @elseif($booking->payment_method === 'pack')
                                    <span class="text-info">Class Pack</span>
                                @else
                                    $0.00
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($booking->customerMembership)
                        <div class="mt-4 p-3 bg-info/10 rounded-lg">
                            <div class="flex items-center gap-2 text-info">
                                <span class="icon-[tabler--id-badge-2] size-5"></span>
                                <span class="font-medium">{{ $booking->customerMembership->membership->name ?? 'Membership' }}</span>
                            </div>
                        </div>
                    @endif

                    @if($booking->classPackPurchase)
                        <div class="mt-4 p-3 bg-info/10 rounded-lg">
                            <div class="flex items-center gap-2 text-info">
                                <span class="icon-[tabler--package] size-5"></span>
                                <span class="font-medium">{{ $booking->classPackPurchase->classPack->name ?? 'Class Pack' }}</span>
                            </div>
                            @if($booking->credits_used)
                                <div class="text-sm text-base-content/60 mt-1">{{ $booking->credits_used }} credit(s) used</div>
                            @endif
                        </div>
                    @endif

                    @if($booking->is_trial)
                        <div class="mt-4 p-3 bg-success/10 rounded-lg">
                            <div class="flex items-center gap-2 text-success">
                                <span class="icon-[tabler--discount-check] size-5"></span>
                                <span class="font-medium">Trial Class</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Intake Forms / Questionnaire Responses --}}
            @php
                $questionnaireResponses = \App\Models\QuestionnaireResponse::where('booking_id', $booking->id)
                    ->with(['version.questionnaire', 'version.blocks.questions', 'answers.question', 'client'])
                    ->get();
            @endphp
            @if($questionnaireResponses->isNotEmpty())
            <div class="card bg-base-100 border border-base-200">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <span class="icon-[tabler--file-text] size-5"></span>
                        Intake Forms
                        @if($booking->intake_status === 'completed')
                            <span class="badge badge-success badge-sm ml-2">Completed</span>
                        @elseif($booking->intake_status === 'pending')
                            <span class="badge badge-warning badge-sm ml-2">Pending</span>
                        @endif
                    </h2>

                    <div class="space-y-3">
                        @foreach($questionnaireResponses as $response)
                            <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 rounded-lg flex items-center justify-center {{ $response->isCompleted() ? 'bg-success/10' : 'bg-warning/10' }}">
                                        <span class="icon-[tabler--{{ $response->isCompleted() ? 'check' : 'clock' }}] size-5 {{ $response->isCompleted() ? 'text-success' : 'text-warning' }}"></span>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $response->version->questionnaire->name ?? 'Questionnaire' }}</div>
                                        <div class="text-sm text-base-content/60">
                                            @if($response->isCompleted())
                                                Completed {{ $response->completed_at->format('M j, Y g:i A') }}
                                            @else
                                                Sent {{ $response->created_at->format('M j, Y') }} - Awaiting response
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @if($response->isCompleted())
                                        <button type="button" class="btn btn-sm btn-outline" onclick="openDrawer('response-{{ $response->id }}', event)">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                            View Response
                                        </button>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Booking Timeline --}}
            <div class="card bg-base-100 border border-base-200">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <span class="icon-[tabler--history] size-5"></span>
                        Timeline
                    </h2>

                    <ul class="timeline timeline-vertical timeline-compact">
                        <li>
                            <div class="timeline-start text-sm text-base-content/60">{{ $booking->created_at->format('M j, g:i A') }}</div>
                            <div class="timeline-middle">
                                <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
                            </div>
                            <div class="timeline-end timeline-box">
                                <span class="font-medium">Booking Created</span>
                                @if($booking->createdBy)
                                    <span class="text-sm text-base-content/60 block">by {{ $booking->createdBy->full_name }}</span>
                                @else
                                    <span class="text-sm text-base-content/60 block">
                                        via {{ $booking->booking_source === 'online' ? 'Online Booking' : ($booking->booking_source === 'internal_walkin' ? 'Staff Booking' : 'API') }}
                                    </span>
                                @endif
                            </div>
                            <hr>
                        </li>
                        @if($booking->checked_in_at)
                        <li>
                            <hr>
                            <div class="timeline-start text-sm text-base-content/60">{{ $booking->checked_in_at->format('M j, g:i A') }}</div>
                            <div class="timeline-middle">
                                <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
                            </div>
                            <div class="timeline-end timeline-box">
                                <span class="font-medium">Checked In</span>
                            </div>
                            <hr>
                        </li>
                        @endif
                        @if($booking->cancelled_at)
                        <li>
                            <hr>
                            <div class="timeline-start text-sm text-base-content/60">{{ $booking->cancelled_at->format('M j, g:i A') }}</div>
                            <div class="timeline-middle">
                                <span class="icon-[tabler--circle-x-filled] size-5 text-error"></span>
                            </div>
                            <div class="timeline-end timeline-box">
                                <span class="font-medium">Cancelled</span>
                                @if($booking->cancelledBy)
                                    <span class="text-sm text-base-content/60 block">by {{ $booking->cancelledBy->full_name }}</span>
                                @elseif($booking->cancelled_by_user_id)
                                    <span class="text-sm text-base-content/60 block">by Staff (user removed)</span>
                                @endif
                                @if($booking->cancellation_reason)
                                    <span class="text-sm text-base-content/60 block">Reason: {{ $booking->cancellation_reason }}</span>
                                @endif
                                @if($booking->cancellation_notes)
                                    <span class="text-sm text-base-content/60 block italic">{{ $booking->cancellation_notes }}</span>
                                @endif
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Client Info --}}
            <div class="card bg-base-100 border border-base-200">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <span class="icon-[tabler--user] size-5"></span>
                        Client
                    </h2>

                    @if($booking->client)
                        <div class="flex items-center gap-3">
                            <x-avatar :src="$booking->client->avatar_url" :initials="$booking->client->initials" :alt="$booking->client->full_name" size="lg" />
                            <div>
                                <div class="font-semibold text-lg">{{ $booking->client->full_name }}</div>
                                @if($booking->client->email)
                                    <div class="text-sm text-base-content/60">{{ $booking->client->email }}</div>
                                @endif
                                @if($booking->client->phone)
                                    <div class="text-sm text-base-content/60">{{ $booking->client->phone }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-base-200">
                            <a href="{{ route('clients.show', $booking->client) }}" class="btn btn-outline btn-sm w-full">
                                <span class="icon-[tabler--user] size-4"></span>
                                View Profile
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4 text-base-content/50">
                            <span class="icon-[tabler--user-off] size-8 mx-auto mb-2"></span>
                            <p>Client not found</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card bg-base-100 border border-base-200">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <span class="icon-[tabler--bolt] size-5"></span>
                        Actions
                    </h2>

                    <div class="space-y-2">
                        @if($booking->bookable)
                            <a href="{{ route('class-sessions.show', $booking->bookable) }}" class="btn btn-outline w-full">
                                <span class="icon-[tabler--calendar-event] size-5"></span>
                                View Session
                            </a>
                        @endif

                        @if($booking->client)
                            <a href="{{ route('clients.show', $booking->client) }}" class="btn btn-outline w-full">
                                <span class="icon-[tabler--user] size-5"></span>
                                View Client
                            </a>
                        @endif

                        <a href="{{ route('walk-in.select') }}" class="btn btn-primary w-full">
                            <span class="icon-[tabler--plus] size-5"></span>
                            New Booking
                        </a>

                        @if($booking->canBeCancelled())
                            <button type="button" class="btn btn-error btn-outline w-full" onclick="openCancelBookingModal('cancel-modal-{{ $booking->id }}')">
                                <span class="icon-[tabler--x] size-5"></span>
                                Cancel Booking
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Booking Info --}}
            <div class="card bg-base-100 border border-base-200">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        Info
                    </h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Booking ID</span>
                            <span class="font-medium">#{{ $booking->id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Source</span>
                            <span class="badge badge-sm {{ $booking->source_badge_class }} badge-soft">
                                @php
                                    $sourceLabels = [
                                        'online' => 'Online',
                                        'internal_walkin' => 'Staff Booking',
                                        'api' => 'API',
                                    ];
                                @endphp
                                {{ $sourceLabels[$booking->booking_source] ?? $booking->booking_source }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Booked At</span>
                            <span class="font-medium">{{ $booking->booked_at?->format('M j, Y g:i A') ?? $booking->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                        @if($booking->notes)
                            <div class="pt-2 border-t border-base-200">
                                <div class="text-base-content/60 mb-1">Notes</div>
                                <div class="text-base-content">{{ $booking->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Cancel Booking Modal --}}
@include('host.bookings.partials.cancel-modal', ['booking' => $booking, 'modalId' => 'cancel-modal-' . $booking->id])

{{-- Questionnaire Response Drawers --}}
@if(isset($questionnaireResponses) && $questionnaireResponses->count() > 0)
    @foreach($questionnaireResponses as $response)
        @include('host.questionnaires.partials.response-drawer', ['response' => $response])
    @endforeach
@endif

@endsection
