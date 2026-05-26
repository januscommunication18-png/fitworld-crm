@extends('layouts.dashboard')

@section('title', $trans['bookings.details'] ?? 'Booking Details')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('bookings.index') }}"><span class="icon-[tabler--book] me-1 size-4"></span> {{ $trans['nav.bookings'] ?? 'Bookings' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['bookings.booking_number'] ?? 'Booking' }} #{{ $booking->id }}</li>
    </ol>
@endsection

@section('content')
<div class="w-full space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex items-start gap-4 flex-1">
            @php
                $bookableForHeader = $booking->bookable;
                $headerColor = $bookableForHeader?->classPlan?->color ?? '#6366f1';
                $headerImage = $bookableForHeader?->classPlan?->image_url ?? null;
                $headerTitle = $booking->client?->full_name
                    ?? ($trans['bookings.unknown_client'] ?? 'Unknown Client');
            @endphp
            @if($booking->client && $booking->client->avatar_url)
                <img src="{{ $booking->client->avatar_url }}" alt="{{ $headerTitle }}" class="w-24 h-24 rounded-lg object-cover">
            @elseif($booking->client)
                <div class="w-24 h-24 rounded-lg bg-primary text-primary-content flex items-center justify-center text-2xl font-semibold">
                    {{ $booking->client->initials ?? '?' }}
                </div>
            @else
                <div class="w-24 h-24 rounded-lg flex items-center justify-center" style="background-color: {{ $headerColor }}20;">
                    <span class="icon-[tabler--calendar-event] size-10" style="color: {{ $headerColor }};"></span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold">{{ $headerTitle }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="badge {{ $booking->status_badge_class }} badge-soft capitalize">
                        {{ str_replace('_', ' ', $booking->status) }}
                    </span>
                    @if($booking->isCheckedIn())
                        <span class="badge badge-success badge-soft badge-sm gap-1">
                            <span class="icon-[tabler--check] size-3"></span>
                            {{ $trans['bookings.checked_in'] ?? 'Checked In' }}
                        </span>
                    @endif
                    @if($booking->booking_type === \App\Models\Booking::TYPE_SERIES)
                        <span class="badge badge-accent badge-soft badge-sm gap-1">
                            <span class="icon-[tabler--calendar-repeat] size-3"></span>
                            {{ $trans['bookings.type_series'] ?? 'Series' }}
                        </span>
                    @endif
                    @if($booking->is_trial ?? false)
                        <span class="badge badge-success badge-soft badge-sm gap-1">
                            <span class="icon-[tabler--discount-check] size-3"></span>
                            {{ $trans['bookings.trial_class'] ?? 'Trial' }}
                        </span>
                    @endif
                </div>
                <p class="text-base-content/60 mt-1 text-sm">
                    {{ $trans['bookings.booking_number'] ?? 'Booking' }} #{{ $booking->id }}
                    @if($bookableForHeader && $bookableForHeader->start_time)
                        &bull; {{ $bookableForHeader->start_time->format('M j, Y') }}
                        &bull; {{ $bookableForHeader->start_time->format('g:i A') }}
                    @endif
                </p>
                @if($booking->client && ($booking->client->email || $booking->client->phone))
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm text-base-content/70">
                        @if($booking->client->email)
                            <a href="mailto:{{ $booking->client->email }}" class="inline-flex items-center gap-1 hover:text-primary">
                                <span class="icon-[tabler--mail] size-4"></span>
                                {{ $booking->client->email }}
                            </a>
                        @endif
                        @if($booking->client->phone)
                            <a href="tel:{{ $booking->client->phone }}" class="inline-flex items-center gap-1 hover:text-primary">
                                <span class="icon-[tabler--phone] size-4"></span>
                                {{ $booking->client->phone }}
                            </a>
                        @endif
                    </div>
                @endif

            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 flex-wrap">
            @if($booking->client)
                <a href="{{ route('clients.show', $booking->client) }}" class="btn btn-primary btn-soft btn-sm">
                    <span class="icon-[tabler--user] size-4"></span>
                    {{ $trans['clients.view_profile'] ?? 'View Profile' }}
                </a>
            @endif
            @if($booking->bookable)
                <a href="{{ route('class-sessions.show', $booking->bookable) }}" class="btn btn-primary btn-soft btn-sm">
                    <span class="icon-[tabler--calendar-event] size-4"></span>
                    {{ $trans['bookings.view_session'] ?? 'View Session' }}
                </a>
            @endif
            @if($booking->canBeCancelled() && auth()->user()->hasPermission('bookings.cancel'))
                <button type="button" class="btn btn-error btn-sm" onclick="openCancelBookingModal('cancel-modal-{{ $booking->id }}')">
                    <span class="icon-[tabler--x] size-4"></span>
                    {{ $trans['bookings.cancel_booking'] ?? 'Cancel Booking' }}
                </button>
            @endif
            @if($booking->status === \App\Models\Booking::STATUS_CANCELLED && auth()->user()->hasPermission('bookings.cancel'))
                <form action="{{ route('bookings.reactivate', $booking) }}" method="POST"
                      onsubmit="return confirm('Reactivate this booking and set it back to Confirmed?');">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <span class="icon-[tabler--rotate-clockwise] size-4"></span>
                        {{ $trans['bookings.reactivate'] ?? 'Reactivate Booking' }}
                    </button>
                </form>
            @endif
            <a href="{{ route('bookings.index') }}" class="btn btn-ghost btn-sm gap-1.5">
                <span class="icon-[tabler--arrow-left] size-4"></span> {{ $trans['btn.back'] ?? 'Back' }}
            </a>
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
                <div class="font-semibold">{{ $trans['bookings.intake_pending'] ?? 'Intake Form Pending' }}</div>
                <div class="text-sm opacity-80">{{ $trans['bookings.intake_pending_desc'] ?? 'Client has not completed the required intake form(s).' }}</div>
            </div>
            @if($booking->client && $booking->client->email)
                <form action="{{ route('bookings.resend-intake', $booking) }}" method="POST" class="ml-auto">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-warning">
                        <span class="icon-[tabler--mail-forward] size-4"></span>
                        {{ $trans['btn.resend_email'] ?? 'Resend Email' }}
                    </button>
                </form>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Session & Payment (combined) --}}
            <div class="card bg-base-100 border border-base-200">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <span class="icon-[tabler--calendar-event] size-5"></span>
                        {{ $trans['bookings.session_payment'] ?? 'Session & Payment' }}
                    </h2>

                    @if($booking->bookable)
                        @php
                            $instructorShow = $booking->bookable instanceof \App\Models\ServiceSlot
                                ? $booking->bookable->instructor
                                : $booking->bookable->primaryInstructor;
                            $methodLabels = [
                                'stripe' => $trans['payment.credit_card_stripe'] ?? 'Credit Card (Stripe)',
                                'membership' => $trans['field.membership'] ?? 'Membership',
                                'pack' => $trans['payment.class_pack'] ?? 'Class Pack',
                                'manual' => $trans['payment.manual'] ?? 'Manual Payment',
                                'cash' => $trans['payment.cash'] ?? 'Cash',
                                'comp' => $trans['payment.complimentary'] ?? 'Complimentary',
                            ];
                        @endphp
                        <div class="overflow-x-auto -mx-4 -mb-4 md:-mx-6 md:-mb-6">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="text-base-content/60 w-44">{{ $trans['bookings.class_session'] ?? 'Class' }}</td>
                                        <td class="font-medium">{{ $booking->bookable->display_title ?? $booking->bookable->title ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-base-content/60">{{ $trans['common.date'] ?? 'Date' }}</td>
                                        <td>{{ $booking->bookable->start_time->format('l, F j, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-base-content/60">{{ $trans['common.time'] ?? 'Time' }}</td>
                                        <td>{{ $booking->bookable->start_time->format('g:i A') }} – {{ $booking->bookable->end_time->format('g:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-base-content/60">{{ $trans['field.instructor'] ?? 'Instructor' }}</td>
                                        <td>{{ $instructorShow?->name ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-base-content/60">{{ $trans['field.location'] ?? 'Location' }}</td>
                                        <td>{{ $booking->bookable->location?->name ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-base-content/60">{{ $trans['field.payment_method'] ?? 'Payment Method' }}</td>
                                        <td>{{ $methodLabels[$booking->payment_method] ?? ucfirst($booking->payment_method) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-base-content/60">{{ $trans['bookings.amount_paid'] ?? 'Amount Paid' }}</td>
                                        <td class="font-semibold">
                                            @if($booking->series_id && ($seriesTotalPaid ?? 0) > 0)
                                                ${{ number_format($seriesTotalPaid, 2) }}
                                                <span class="text-xs text-base-content/60 font-normal">{{ $trans['bookings.for_series'] ?? 'for series' }}</span>
                                            @elseif($booking->price_paid > 0)
                                                ${{ number_format($booking->price_paid, 2) }}
                                            @elseif($booking->payment_method === 'comp')
                                                <span class="text-success">{{ $trans['payment.complimentary'] ?? 'Complimentary' }}</span>
                                            @elseif($booking->payment_method === 'membership')
                                                <span class="text-info">{{ $trans['field.membership'] ?? 'Membership' }}</span>
                                            @elseif($booking->payment_method === 'pack')
                                                <span class="text-info">{{ $trans['payment.class_pack'] ?? 'Class Pack' }}</span>
                                            @else
                                                $0.00
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        @if($booking->customerMembership || $booking->classPackPurchase || $booking->is_trial)
                            <div class="mt-4 space-y-2">
                                @if($booking->customerMembership)
                                    <div class="p-3 bg-info/10 rounded-lg">
                                        <div class="flex items-center gap-2 text-info">
                                            <span class="icon-[tabler--id-badge-2] size-5"></span>
                                            <span class="font-medium">{{ $booking->customerMembership->membership->name ?? 'Membership' }}</span>
                                        </div>
                                    </div>
                                @endif
                                @if($booking->classPackPurchase)
                                    <div class="p-3 bg-info/10 rounded-lg">
                                        <div class="flex items-center gap-2 text-info">
                                            <span class="icon-[tabler--package] size-5"></span>
                                            <span class="font-medium">{{ $booking->classPackPurchase->classPack->name ?? ($trans['payment.class_pack'] ?? 'Class Pack') }}</span>
                                        </div>
                                        @if($booking->credits_used)
                                            <div class="text-sm text-base-content/60 mt-1">{{ $booking->credits_used }} {{ $trans['bookings.credits_used'] ?? 'credit(s) used' }}</div>
                                        @endif
                                    </div>
                                @endif
                                @if($booking->is_trial)
                                    <div class="p-3 bg-success/10 rounded-lg">
                                        <div class="flex items-center gap-2 text-success">
                                            <span class="icon-[tabler--discount-check] size-5"></span>
                                            <span class="font-medium">{{ $trans['bookings.trial_class'] ?? 'Trial Class' }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                    @else
                        <div class="text-center py-6 text-base-content/50">
                            <span class="icon-[tabler--calendar-off] size-8 mx-auto mb-2"></span>
                            <p>{{ $trans['bookings.session_deleted'] ?? 'Session has been deleted' }}</p>
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
                        {{ $trans['bookings.intake_forms'] ?? 'Intake Forms' }}
                        @if($booking->intake_status === 'completed')
                            <span class="badge badge-success badge-sm ml-2">{{ $trans['common.completed'] ?? 'Completed' }}</span>
                        @elseif($booking->intake_status === 'pending')
                            <span class="badge badge-warning badge-sm ml-2">{{ $trans['common.pending'] ?? 'Pending' }}</span>
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
                                                {{ $trans['common.completed'] ?? 'Completed' }} {{ $response->completed_at->format('M j, Y g:i A') }}
                                            @else
                                                {{ $trans['bookings.sent'] ?? 'Sent' }} {{ $response->created_at->format('M j, Y') }} - {{ $trans['bookings.awaiting_response'] ?? 'Awaiting response' }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @if($response->isCompleted())
                                        <button type="button" class="btn btn-sm btn-outline" onclick="openDrawer('response-{{ $response->id }}', event)">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                            {{ $trans['btn.view_response'] ?? 'View Response' }}
                                        </button>
                                    @else
                                        <span class="badge badge-warning">{{ $trans['common.pending'] ?? 'Pending' }}</span>
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
                        {{ $trans['bookings.timeline'] ?? 'Timeline' }}
                    </h2>

                    <ul class="timeline timeline-vertical timeline-compact">
                        <li>
                            <div class="timeline-start text-sm text-base-content/60">{{ $booking->created_at->format('M j, g:i A') }}</div>
                            <div class="timeline-middle">
                                <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
                            </div>
                            <div class="timeline-end timeline-box">
                                <span class="font-medium">{{ $trans['bookings.booking_created'] ?? 'Booking Created' }}</span>
                                @if($booking->createdBy)
                                    <span class="text-sm text-base-content/60 block">{{ $trans['common.by'] ?? 'by' }} {{ $booking->createdBy->full_name }}</span>
                                @else
                                    <span class="text-sm text-base-content/60 block">
                                        {{ $trans['common.via'] ?? 'via' }} {{ $booking->booking_source === 'online' ? ($trans['bookings.online_booking'] ?? 'Online Booking') : ($booking->booking_source === 'internal_walkin' ? ($trans['bookings.staff_booking'] ?? 'Staff Booking') : 'API') }}
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
                                <span class="font-medium">{{ $trans['bookings.checked_in'] ?? 'Checked In' }}</span>
                                @if($booking->checkedInBy)
                                    <span class="text-sm text-base-content/60 block">{{ $trans['common.by'] ?? 'by' }} {{ $booking->checkedInBy->full_name }}</span>
                                @elseif($booking->checked_in_method)
                                    @php
                                        $methodLabels = \App\Models\Booking::getCheckInMethods();
                                    @endphp
                                    <span class="text-sm text-base-content/60 block">
                                        {{ $trans['common.via'] ?? 'via' }} {{ $methodLabels[$booking->checked_in_method] ?? ucfirst($booking->checked_in_method) }}
                                    </span>
                                @endif
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
                                <span class="font-medium">{{ $trans['common.cancelled'] ?? 'Cancelled' }}</span>
                                @if($booking->cancelledBy)
                                    <span class="text-sm text-base-content/60 block">{{ $trans['common.by'] ?? 'by' }} {{ $booking->cancelledBy->full_name }}</span>
                                @elseif(str_starts_with((string) $booking->cancellation_notes, 'Cancelled by member'))
                                    <span class="text-sm text-base-content/60 block">
                                        {{ $trans['common.by'] ?? 'by' }}
                                        {{ $booking->client?->full_name ?? 'member' }}
                                        <span class="badge badge-ghost badge-xs ml-1">{{ $trans['bookings.self_cancel'] ?? 'member' }}</span>
                                    </span>
                                @elseif($booking->cancelled_by_user_id)
                                    <span class="text-sm text-base-content/60 block">{{ $trans['bookings.by_staff_removed'] ?? 'by Staff (user removed)' }}</span>
                                @endif
                                @if($booking->cancellation_reason)
                                    <span class="text-sm text-base-content/60 block">{{ $trans['field.reason'] ?? 'Reason' }}: {{ $booking->cancellation_reason }}</span>
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
            {{-- Booking Info --}}
            <div class="card bg-base-100 border border-base-200">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        {{ $trans['common.info'] ?? 'Info' }}
                    </h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['bookings.booking_id'] ?? 'Booking ID' }}</span>
                            <span class="font-medium">#{{ $booking->id }}</span>
                        </div>
                        @if(!empty($linkedTransaction))
                            <div class="flex justify-between">
                                <span class="text-base-content/60">{{ $trans['bookings.transaction_id'] ?? 'Transaction ID' }}</span>
                                <a href="{{ route('payments.transactions.show', $linkedTransaction) }}" class="font-medium font-mono text-primary hover:underline">
                                    {{ $linkedTransaction->transaction_id ?: 'TX-' . $linkedTransaction->id }}
                                </a>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['field.source'] ?? 'Source' }}</span>
                            <span class="badge badge-sm {{ $booking->source_badge_class }} badge-soft">
                                @php
                                    $sourceLabels = [
                                        'online' => $trans['bookings.online'] ?? 'Online',
                                        'internal_walkin' => $trans['bookings.staff_booking'] ?? 'Staff Booking',
                                        'api' => 'API',
                                    ];
                                @endphp
                                {{ $sourceLabels[$booking->booking_source] ?? $booking->booking_source }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ $trans['bookings.booked_at'] ?? 'Booked At' }}</span>
                            <span class="font-medium">{{ $booking->booked_at?->format('M j, Y g:i A') ?? $booking->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                        @if($booking->notes)
                            <div class="pt-2 border-t border-base-200">
                                <div class="text-base-content/60 mb-1">{{ $trans['field.notes'] ?? 'Notes' }}</div>
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
