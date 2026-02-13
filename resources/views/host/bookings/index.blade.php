@extends('layouts.dashboard')

@section('title', $title)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        @if($filter)
            <li><a href="{{ route('bookings.index') }}"><span class="icon-[tabler--book] me-1 size-4"></span> Bookings</a></li>
            <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
            <li aria-current="page">{{ $title }}</li>
        @else
            <li aria-current="page"><span class="icon-[tabler--book] me-1 size-4"></span> Bookings</li>
        @endif
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h1 class="text-2xl font-bold">{{ $title }}</h1>
        <a href="{{ route('walk-in.select') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            Booking
        </a>
    </div>

    {{-- Filter Tabs --}}
    <div class="tabs tabs-bordered">
        <a href="{{ route('bookings.index') }}"
           class="tab {{ !$filter ? 'tab-active' : '' }}">
            <span class="icon-[tabler--clipboard-list] size-4 mr-1"></span>
            All Bookings
        </a>
        <a href="{{ route('bookings.upcoming') }}"
           class="tab {{ $filter === 'upcoming' ? 'tab-active' : '' }}">
            <span class="icon-[tabler--clock] size-4 mr-1"></span>
            Upcoming
        </a>
        <a href="{{ route('bookings.cancelled') }}"
           class="tab {{ $filter === 'cancelled' ? 'tab-active' : '' }}">
            <span class="icon-[tabler--circle-x] size-4 mr-1"></span>
            Cancellations
        </a>
        <a href="{{ route('bookings.no-shows') }}"
           class="tab {{ $filter === 'no-shows' ? 'tab-active' : '' }}">
            <span class="icon-[tabler--user-x] size-4 mr-1"></span>
            No-Shows
        </a>
    </div>

    {{-- Search & Filters --}}
    <div class="card bg-base-100 border border-base-200">
        <div class="card-body p-4">
            <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-end gap-3">
                <div class="form-control flex-1 min-w-[200px]">
                    <label class="label" for="search"><span class="label-text">Search</span></label>
                    <div class="relative">
                        <span class="icon-[tabler--search] size-5 text-base-content/50 absolute left-3 top-1/2 -translate-y-1/2"></span>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               class="input input-bordered w-full pl-10"
                               placeholder="Search by client name or email...">
                    </div>
                </div>

                @if(!$filter)
                <div class="form-control w-40">
                    <label class="label" for="status"><span class="label-text">Status</span></label>
                    <select name="status" id="status" class="select select-bordered">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control w-40">
                    <label class="label" for="source"><span class="label-text">Source</span></label>
                    <select name="source" id="source" class="select select-bordered">
                        <option value="">All Sources</option>
                        @foreach($sources as $value => $label)
                            <option value="{{ $value }}" {{ request('source') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control w-40">
                    <label class="label" for="payment"><span class="label-text">Payment</span></label>
                    <select name="payment" id="payment" class="select select-bordered">
                        <option value="">All Methods</option>
                        @foreach($paymentMethods as $value => $label)
                            <option value="{{ $value }}" {{ request('payment') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--search] size-4"></span>
                    Search
                </button>

                @if(request()->hasAny(['search', 'status', 'source', 'payment']))
                <a href="{{ url()->current() }}" class="btn btn-ghost">
                    <span class="icon-[tabler--x] size-4"></span>
                    Clear
                </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Bookings Table --}}
    <div class="card bg-base-100 border border-base-200">
        <div class="card-body p-0">
            @if($bookings->isEmpty())
                <div class="text-center py-12">
                    <span class="icon-[tabler--calendar-off] size-12 text-base-content/30 mx-auto mb-4"></span>
                    <h3 class="font-semibold text-lg">No Bookings Found</h3>
                    <p class="text-base-content/60 text-sm mt-1">
                        @if($filter === 'upcoming')
                            There are no upcoming bookings at the moment.
                        @elseif($filter === 'cancelled')
                            No bookings have been cancelled.
                        @elseif($filter === 'no-shows')
                            No clients have been marked as no-shows.
                        @else
                            No bookings match your search criteria.
                        @endif
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Class/Service</th>
                                <th>Client</th>
                                <th>Source</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                            <tr class="hover:bg-base-200/50">
                                <td>
                                    <div class="font-medium">
                                        @if($booking->bookable && $booking->bookable->start_time)
                                            {{ $booking->bookable->start_time->format('M j, Y') }}
                                        @else
                                            {{ $booking->booked_at?->format('M j, Y') ?? '-' }}
                                        @endif
                                    </div>
                                    <div class="text-sm text-base-content/60">
                                        @if($booking->bookable && $booking->bookable->start_time)
                                            {{ $booking->bookable->start_time->format('g:i A') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="font-medium">
                                        @if($booking->bookable)
                                            {{ $booking->bookable->display_title ?? $booking->bookable->title ?? 'Unknown' }}
                                        @else
                                            <span class="text-base-content/50">Deleted</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-base-content/60">
                                        {{ class_basename($booking->bookable_type ?? '') }}
                                    </div>
                                </td>
                                <td>
                                    @if($booking->client)
                                        <div class="flex items-center gap-3">
                                            <x-avatar :src="$booking->client->avatar_url" :initials="$booking->client->initials" :alt="$booking->client->full_name" size="sm" />
                                            <div>
                                                <div class="font-medium">{{ $booking->client->full_name }}</div>
                                                <div class="text-sm text-base-content/60">{{ $booking->client->email ?: $booking->client->phone ?: '-' }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-base-content/50">Unknown Client</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-sm {{ $booking->source_badge_class }} badge-soft">
                                        {{ $sources[$booking->booking_source] ?? $booking->booking_source }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-sm {{ $booking->payment_method_badge_class }} badge-soft">
                                        {{ $paymentMethods[$booking->payment_method] ?? $booking->payment_method }}
                                    </span>
                                    @if($booking->price_paid)
                                        <div class="text-sm text-base-content/60">{{ $booking->formatted_price_paid }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-sm {{ $booking->status_badge_class }} badge-soft">
                                        {{ $statuses[$booking->status] ?? $booking->status }}
                                    </span>
                                    @if($booking->isCheckedIn())
                                        <div class="text-xs text-success mt-0.5">
                                            <span class="icon-[tabler--check] size-3"></span> Checked in
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]">
                                        <button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions">
                                            <span class="icon-[tabler--dots] size-4"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('bookings.show', $booking) }}">
                                                    <span class="icon-[tabler--eye] size-4 me-2"></span>View Details
                                                </a>
                                            </li>
                                            @if($booking->client)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('clients.show', $booking->client) }}">
                                                    <span class="icon-[tabler--user] size-4 me-2"></span>View Client
                                                </a>
                                            </li>
                                            @endif
                                            @if($booking->bookable && $booking->bookable_type === 'App\\Models\\ClassSession')
                                            <li>
                                                <a class="dropdown-item" href="{{ route('class-sessions.show', $booking->bookable_id) }}">
                                                    <span class="icon-[tabler--calendar-event] size-4 me-2"></span>View Session
                                                </a>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($bookings->hasPages())
                <div class="p-4 border-t border-base-200">
                    {{ $bookings->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
