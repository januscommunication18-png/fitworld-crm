@php
    $user = auth()->user();
    $host = $user->currentHost() ?? $user->host;
    $selectedLang = session("studio_language_{$host->id}", $host->default_language_app ?? 'en');
    $t = \App\Services\TranslationService::make($host, $selectedLang);
    $trans = $t->all();
@endphp

@extends('layouts.dashboard')

@section('title', $trans['schedule.calendar'] ?? 'Studio Calendar')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('schedule.index') }}"><span class="icon-[tabler--calendar] me-1 size-4"></span> {{ $trans['nav.schedule'] ?? 'Schedule' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $trans['schedule.calendar'] ?? 'Studio Calendar' }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['schedule.calendar'] ?? 'Studio Calendar' }}</h1>
            <p class="text-base-content/60 mt-1">{{ $trans['schedule.calendar_description'] ?? 'View your classes and services in calendar format.' }}</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Current Time Display --}}
            <div class="flex items-center gap-2 px-3 py-2 bg-base-200 rounded-lg">
                <span class="icon-[tabler--clock] size-5 text-primary"></span>
                <span id="current-time" class="font-semibold text-base-content">{{ now()->setTimezone($timezone)->format('g:i A') }}</span>
                <span class="text-xs text-base-content/50">{{ str_replace('_', ' ', $timezone) }}</span>
            </div>

            {{-- Add Booking Dropdown --}}
            <div class="relative">
                <button type="button" class="btn btn-success" onclick="toggleDropdown('booking-dropdown')">
                    <span class="icon-[tabler--user-plus] size-5"></span>
                    {{ $trans['btn.add_booking'] ?? 'Add Booking' }}
                    <span class="icon-[tabler--chevron-down] size-4"></span>
                </button>
                <ul id="booking-dropdown" class="hidden absolute right-0 top-full mt-1 menu bg-base-100 rounded-box w-52 p-2 shadow-lg border border-base-300 z-50">
                    <li>
                        <a href="{{ route('walk-in.select') }}">
                            <span class="icon-[tabler--yoga] size-5 text-primary"></span>
                            {{ $trans['schedule.class_sessions'] ?? 'Class Session' }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('walk-in.select-service') }}">
                            <span class="icon-[tabler--massage] size-5 text-success"></span>
                            {{ $trans['schedule.service_slots'] ?? 'Service Slot' }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('walk-in.select-membership') }}">
                            <span class="icon-[tabler--id-badge-2] size-5 text-warning"></span>
                            {{ $trans['page.memberships'] ?? 'Membership' }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Add Schedule Dropdown --}}
            <div class="relative">
                <button type="button" class="btn btn-primary" onclick="toggleDropdown('schedule-dropdown')">
                    <span class="icon-[tabler--plus] size-5"></span>
                    {{ $trans['schedule.add_schedule'] ?? 'Add Schedule' }}
                    <span class="icon-[tabler--chevron-down] size-4"></span>
                </button>
                <ul id="schedule-dropdown" class="hidden absolute right-0 top-full mt-1 menu bg-base-100 rounded-box w-72 p-2 shadow-lg border border-base-300 z-50">
                    <li class="menu-title text-xs uppercase tracking-wider text-base-content/50 px-2 pt-2">{{ $trans['common.type'] ?? 'Schedule Type' }}</li>
                    <li>
                        <a href="{{ route('class-sessions.create') }}" class="flex flex-col items-start gap-0.5 py-3">
                            <span class="flex items-center gap-2">
                                <span class="icon-[tabler--yoga] size-5 text-primary"></span>
                                <span class="font-medium">{{ $trans['page.classes'] ?? 'Class' }}</span>
                            </span>
                            <span class="text-xs text-base-content/60 ml-7">{{ $trans['schedule.class_description'] ?? 'Single or recurring class session' }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('service-slots.create') }}" class="flex flex-col items-start gap-0.5 py-3">
                            <span class="flex items-center gap-2">
                                <span class="icon-[tabler--massage] size-5 text-success"></span>
                                <span class="font-medium">{{ $trans['page.services'] ?? 'Service' }}</span>
                            </span>
                            <span class="text-xs text-base-content/60 ml-7">{{ $trans['schedule.service_description'] ?? '1-on-1 appointment slot' }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('scheduled-membership.create') }}" class="flex flex-col items-start gap-0.5 py-3">
                            <span class="flex items-center gap-2">
                                <span class="icon-[tabler--calendar-user] size-5 text-warning"></span>
                                <span class="font-medium">{{ $trans['schedule.membership_schedule'] ?? 'Membership Schedule' }}</span>
                            </span>
                            <span class="text-xs text-base-content/60 ml-7">{{ $trans['schedule.membership_schedule_description'] ?? 'Recurring classes for membership holders' }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body py-4 px-5">
            <div class="flex flex-wrap gap-5 items-center">
                {{-- Type Filter --}}
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--category] size-4 text-base-content/60"></span>
                    <select id="filter-type" class="select select-sm w-44 select-bordered">
                        <option value="all">{{ $trans['common.all'] ?? 'All' }} {{ $trans['common.type'] ?? 'Types' }}</option>
                        <option value="class">{{ $trans['page.classes'] ?? 'Classes' }} {{ $trans['common.only'] ?? 'Only' }}</option>
                        <option value="service">{{ $trans['page.services'] ?? 'Services' }} {{ $trans['common.only'] ?? 'Only' }}</option>
                        <option value="membership">{{ $trans['page.memberships'] ?? 'Membership' }} {{ $trans['common.only'] ?? 'Only' }}</option>
                    </select>
                </div>

                {{-- Class Plan Filter (shown when Classes Only selected) --}}
                <div id="class-plan-filter" class="flex items-center gap-2 hidden">
                    <span class="icon-[tabler--yoga] size-4 text-primary"></span>
                    <select id="filter-class-plan" class="select select-sm w-48 select-bordered">
                        <option value="">{{ $trans['common.all'] ?? 'All' }} {{ $trans['page.classes'] ?? 'Classes' }}</option>
                        @foreach($classPlans ?? [] as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Service Plan Filter (shown when Services Only selected) --}}
                <div id="service-plan-filter" class="flex items-center gap-2 hidden">
                    <span class="icon-[tabler--massage] size-4 text-success"></span>
                    <select id="filter-service-plan" class="select select-sm w-48 select-bordered">
                        <option value="">{{ $trans['common.all'] ?? 'All' }} {{ $trans['page.services'] ?? 'Services' }}</option>
                        @foreach($servicePlans ?? [] as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Location Filter --}}
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--map-pin] size-4 text-base-content/60"></span>
                    <select id="filter-location" class="select select-sm w-44 select-bordered">
                        <option value="">{{ $trans['common.all'] ?? 'All' }} {{ $trans['settings.locations'] ?? 'Locations' }}</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Instructor Filter --}}
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--user] size-4 text-base-content/60"></span>
                    <select id="filter-instructor" class="select select-sm w-48 select-bordered">
                        <option value="">{{ $trans['common.all'] ?? 'All' }} {{ $trans['nav.instructors'] ?? 'Instructors' }}</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Legend --}}
                <div class="flex items-center gap-4 ml-auto">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full shadow-sm" style="background-color: #6366f1;"></span>
                        <span class="text-sm font-medium text-base-content">{{ $trans['page.classes'] ?? 'Class' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full shadow-sm" style="background-color: #10b981;"></span>
                        <span class="text-sm font-medium text-base-content">{{ $trans['page.services'] ?? 'Service' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full shadow-sm" style="background-color: #8b5cf6;"></span>
                        <span class="text-sm font-medium text-base-content">{{ $trans['page.memberships'] ?? 'Membership' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full shadow-sm" style="background-color: #f59e0b;"></span>
                        <span class="text-sm font-medium text-base-content">{{ $trans['common.draft'] ?? 'Draft' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Calendar Container --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-4 sm:p-6">
            <div id="studio-calendar" data-events-url="{{ route('schedule.events') }}"></div>
        </div>
    </div>
</div>

{{-- Drawer Backdrop --}}
<div id="drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="closeAllDrawers()"></div>

{{-- Class Session Drawers --}}
@foreach($classSessions as $session)
    @include('host.schedule.partials.class-session-drawer', ['classSession' => $session])
@endforeach

{{-- Service Slot Drawers --}}
@foreach($serviceSlots as $slot)
    @include('host.schedule.partials.service-slot-drawer', ['serviceSlot' => $slot])
@endforeach

@push('styles')
<style>
    /* FullCalendar Custom Styles for FlyonUI */
    #studio-calendar .fc {
        --fc-border-color: oklch(var(--bc) / 0.1);
        --fc-button-bg-color: oklch(var(--b2));
        --fc-button-border-color: oklch(var(--bc) / 0.2);
        --fc-button-text-color: oklch(var(--bc));
        --fc-button-hover-bg-color: oklch(var(--b3));
        --fc-button-hover-border-color: oklch(var(--bc) / 0.3);
        --fc-button-active-bg-color: oklch(var(--p));
        --fc-button-active-border-color: oklch(var(--p));
        --fc-today-bg-color: #f8f9fa;
        --fc-now-indicator-color: oklch(var(--er));
        --fc-page-bg-color: oklch(var(--b1));
        --fc-neutral-bg-color: oklch(var(--b2));
        --fc-list-event-hover-bg-color: oklch(var(--b2));
    }

    #studio-calendar .fc .fc-toolbar-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: oklch(var(--bc));
    }

    #studio-calendar .fc .fc-button {
        font-weight: 500;
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.15s ease;
    }

    #studio-calendar .fc .fc-button:focus {
        box-shadow: 0 0 0 2px oklch(var(--p) / 0.3);
    }

    #studio-calendar .fc .fc-button-primary:not(:disabled).fc-button-active,
    #studio-calendar .fc .fc-button-primary:not(:disabled):active {
        background-color: oklch(var(--p));
        border-color: oklch(var(--p));
        color: oklch(var(--pc));
    }

    #studio-calendar .fc .fc-col-header-cell-cushion {
        font-weight: 600;
        color: oklch(var(--bc) / 0.7);
        padding: 0.75rem 0;
    }

    #studio-calendar .fc .fc-daygrid-day-number {
        font-weight: 500;
        color: oklch(var(--bc) / 0.8);
        padding: 0.5rem;
    }

    #studio-calendar .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background-color: oklch(var(--p));
        color: oklch(var(--pc));
        border-radius: 9999px;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #studio-calendar .fc .fc-timegrid-slot-label-cushion {
        font-size: 0.75rem;
        font-weight: 500;
        color: oklch(var(--bc) / 0.6);
    }

    /* Event Styles */
    #studio-calendar .fc .fc-event {
        border-radius: 0.375rem;
        border: none;
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 2px 6px;
        cursor: pointer;
        transition: transform 0.1s ease, box-shadow 0.1s ease;
    }

    #studio-calendar .fc .fc-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Event Color Classes - Consistent across all views */
    #studio-calendar .fc-event-primary,
    #studio-calendar .fc .fc-event.fc-event-primary,
    #studio-calendar .fc-event.fc-event-primary {
        background-color: #6366f1 !important;
        border-color: #6366f1 !important;
        color: #ffffff !important;
    }
    #studio-calendar .fc-event-primary *,
    #studio-calendar .fc .fc-event.fc-event-primary *,
    #studio-calendar .fc-event.fc-event-primary * {
        color: #ffffff !important;
    }

    #studio-calendar .fc-event-success,
    #studio-calendar .fc .fc-event.fc-event-success,
    #studio-calendar .fc-event.fc-event-success {
        background-color: #10b981 !important;
        border-color: #10b981 !important;
        color: #ffffff !important;
    }
    #studio-calendar .fc-event-success *,
    #studio-calendar .fc .fc-event.fc-event-success *,
    #studio-calendar .fc-event.fc-event-success * {
        color: #ffffff !important;
    }

    /* Membership Sessions - Violet */
    #studio-calendar .fc-event-secondary,
    #studio-calendar .fc .fc-event.fc-event-secondary,
    #studio-calendar .fc-event.fc-event-secondary {
        background-color: #8b5cf6 !important;
        border-color: #8b5cf6 !important;
        color: #ffffff !important;
    }
    #studio-calendar .fc-event-secondary *,
    #studio-calendar .fc .fc-event.fc-event-secondary *,
    #studio-calendar .fc-event.fc-event-secondary * {
        color: #ffffff !important;
    }

    #studio-calendar .fc-event-warning,
    #studio-calendar .fc .fc-event.fc-event-warning,
    #studio-calendar .fc-event.fc-event-warning {
        background-color: #f59e0b !important;
        border-color: #f59e0b !important;
        color: #000000 !important;
    }
    #studio-calendar .fc-event-warning *,
    #studio-calendar .fc .fc-event.fc-event-warning *,
    #studio-calendar .fc-event.fc-event-warning * {
        color: #000000 !important;
    }

    #studio-calendar .fc-event-info,
    #studio-calendar .fc .fc-event.fc-event-info,
    #studio-calendar .fc-event.fc-event-info {
        background-color: #3b82f6 !important;
        border-color: #3b82f6 !important;
        color: #ffffff !important;
    }
    #studio-calendar .fc-event-info *,
    #studio-calendar .fc .fc-event.fc-event-info *,
    #studio-calendar .fc-event.fc-event-info * {
        color: #ffffff !important;
    }

    #studio-calendar .fc-event-error,
    #studio-calendar .fc .fc-event.fc-event-error,
    #studio-calendar .fc-event.fc-event-error {
        background-color: #ef4444 !important;
        border-color: #ef4444 !important;
        color: #ffffff !important;
    }
    #studio-calendar .fc-event-error *,
    #studio-calendar .fc .fc-event.fc-event-error *,
    #studio-calendar .fc-event.fc-event-error * {
        color: #ffffff !important;
    }

    /* Month/DayGrid view - ensure colored backgrounds with proper text */
    #studio-calendar .fc .fc-daygrid-event {
        border-radius: 4px;
        padding: 2px 4px;
        margin-bottom: 1px;
    }

    #studio-calendar .fc .fc-daygrid-event .fc-event-title,
    #studio-calendar .fc .fc-daygrid-event .fc-event-time {
        font-weight: 600;
        font-size: 0.75rem;
    }

    /* Force daygrid events to use block style with colored backgrounds */
    #studio-calendar .fc .fc-daygrid-dot-event {
        padding: 2px 4px;
        border-radius: 4px;
    }

    #studio-calendar .fc .fc-daygrid-dot-event .fc-daygrid-event-dot {
        display: none;
    }

    /* Apply color classes to month view events - using hex colors for reliability */
    #studio-calendar .fc .fc-daygrid-event.fc-event-primary,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-primary {
        background-color: #6366f1 !important;
    }
    #studio-calendar .fc .fc-daygrid-event.fc-event-primary .fc-event-title,
    #studio-calendar .fc .fc-daygrid-event.fc-event-primary .fc-event-time,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-primary .fc-event-title,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-primary .fc-event-time {
        color: #ffffff !important;
    }

    #studio-calendar .fc .fc-daygrid-event.fc-event-success,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-success {
        background-color: #10b981 !important;
    }
    #studio-calendar .fc .fc-daygrid-event.fc-event-success .fc-event-title,
    #studio-calendar .fc .fc-daygrid-event.fc-event-success .fc-event-time,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-success .fc-event-title,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-success .fc-event-time {
        color: #ffffff !important;
    }

    #studio-calendar .fc .fc-daygrid-event.fc-event-secondary,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-secondary {
        background-color: #8b5cf6 !important;
    }
    #studio-calendar .fc .fc-daygrid-event.fc-event-secondary .fc-event-title,
    #studio-calendar .fc .fc-daygrid-event.fc-event-secondary .fc-event-time,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-secondary .fc-event-title,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-secondary .fc-event-time {
        color: #ffffff !important;
    }

    #studio-calendar .fc .fc-daygrid-event.fc-event-warning,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-warning {
        background-color: #f59e0b !important;
    }
    #studio-calendar .fc .fc-daygrid-event.fc-event-warning .fc-event-title,
    #studio-calendar .fc .fc-daygrid-event.fc-event-warning .fc-event-time,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-warning .fc-event-title,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-warning .fc-event-time {
        color: #000000 !important;
    }

    #studio-calendar .fc .fc-daygrid-event.fc-event-info,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-info {
        background-color: #3b82f6 !important;
    }
    #studio-calendar .fc .fc-daygrid-event.fc-event-info .fc-event-title,
    #studio-calendar .fc .fc-daygrid-event.fc-event-info .fc-event-time,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-info .fc-event-title,
    #studio-calendar .fc .fc-daygrid-dot-event.fc-event-info .fc-event-time {
        color: #ffffff !important;
    }

    /* Block events in month view */
    #studio-calendar .fc .fc-daygrid-block-event .fc-event-title,
    #studio-calendar .fc .fc-daygrid-block-event .fc-event-time {
        color: #ffffff !important;
    }

    #studio-calendar .fc .fc-timegrid-event .fc-event-main {
        padding: 4px 6px;
    }

    #studio-calendar .fc .fc-timegrid-event .fc-event-time {
        font-size: 0.75rem;
        font-weight: 700;
    }

    /* Week/Day (timegrid) view - text colors matching background */
    #studio-calendar .fc .fc-timegrid-event.fc-event-primary,
    #studio-calendar .fc .fc-timegrid-event.fc-event-primary *,
    #studio-calendar .fc .fc-timegrid-event.fc-event-primary .fc-event-main,
    #studio-calendar .fc .fc-timegrid-event.fc-event-primary .fc-event-main-frame,
    #studio-calendar .fc .fc-timegrid-event.fc-event-primary .fc-event-time,
    #studio-calendar .fc .fc-timegrid-event.fc-event-primary .fc-event-title,
    #studio-calendar .fc .fc-timegrid-event.fc-event-primary .fc-event-title-container {
        color: #ffffff !important;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-success,
    #studio-calendar .fc .fc-timegrid-event.fc-event-success *,
    #studio-calendar .fc .fc-timegrid-event.fc-event-success .fc-event-main,
    #studio-calendar .fc .fc-timegrid-event.fc-event-success .fc-event-main-frame,
    #studio-calendar .fc .fc-timegrid-event.fc-event-success .fc-event-time,
    #studio-calendar .fc .fc-timegrid-event.fc-event-success .fc-event-title,
    #studio-calendar .fc .fc-timegrid-event.fc-event-success .fc-event-title-container {
        color: #ffffff !important;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-secondary,
    #studio-calendar .fc .fc-timegrid-event.fc-event-secondary *,
    #studio-calendar .fc .fc-timegrid-event.fc-event-secondary .fc-event-main,
    #studio-calendar .fc .fc-timegrid-event.fc-event-secondary .fc-event-main-frame,
    #studio-calendar .fc .fc-timegrid-event.fc-event-secondary .fc-event-time,
    #studio-calendar .fc .fc-timegrid-event.fc-event-secondary .fc-event-title,
    #studio-calendar .fc .fc-timegrid-event.fc-event-secondary .fc-event-title-container {
        color: #ffffff !important;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-warning,
    #studio-calendar .fc .fc-timegrid-event.fc-event-warning *,
    #studio-calendar .fc .fc-timegrid-event.fc-event-warning .fc-event-main,
    #studio-calendar .fc .fc-timegrid-event.fc-event-warning .fc-event-main-frame,
    #studio-calendar .fc .fc-timegrid-event.fc-event-warning .fc-event-time,
    #studio-calendar .fc .fc-timegrid-event.fc-event-warning .fc-event-title,
    #studio-calendar .fc .fc-timegrid-event.fc-event-warning .fc-event-title-container {
        color: #000000 !important;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-info,
    #studio-calendar .fc .fc-timegrid-event.fc-event-info *,
    #studio-calendar .fc .fc-timegrid-event.fc-event-info .fc-event-main,
    #studio-calendar .fc .fc-timegrid-event.fc-event-info .fc-event-main-frame,
    #studio-calendar .fc .fc-timegrid-event.fc-event-info .fc-event-time,
    #studio-calendar .fc .fc-timegrid-event.fc-event-info .fc-event-title,
    #studio-calendar .fc .fc-timegrid-event.fc-event-info .fc-event-title-container {
        color: #ffffff !important;
    }

    /* Ensure events show content properly */
    #studio-calendar .fc .fc-timegrid-event {
        overflow: hidden;
        min-height: 40px;
    }

    #studio-calendar .fc .fc-timegrid-col-events {
        margin: 0 1px;
    }

    #studio-calendar .fc .fc-timegrid-event .fc-event-title-container {
        flex-grow: 1;
        flex-shrink: 1;
        min-width: 0;
    }

    #studio-calendar .fc .fc-timegrid-event .fc-event-title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.75rem;
        line-height: 1.2;
    }

    /* Compact event styles when many overlap */
    #studio-calendar .fc .fc-timegrid-event.fc-event-compact {
        min-height: 30px;
        padding: 1px 2px !important;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-compact .fc-event-main {
        padding: 2px 3px;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-compact .fc-event-time {
        font-size: 0.65rem;
        display: none;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-compact .fc-event-title {
        font-size: 0.65rem;
        font-weight: 600;
    }

    /* Event stacking - show +more link when too many events */
    #studio-calendar .fc .fc-timegrid-more-link {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        border: none;
        border-radius: 0.5rem;
        padding: 6px 10px;
        font-size: 0.8rem;
        font-weight: 800;
        color: #ffffff !important;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 3px 8px rgba(249, 115, 22, 0.4);
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    #studio-calendar .fc .fc-timegrid-more-link:hover {
        background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
        box-shadow: 0 5px 12px rgba(249, 115, 22, 0.5);
        transform: translateY(-2px) scale(1.02);
    }

    /* Also style the daygrid more link */
    #studio-calendar .fc .fc-daygrid-more-link {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        border-radius: 0.375rem;
        padding: 3px 8px;
        font-size: 0.75rem;
        font-weight: 800;
        color: #ffffff !important;
        text-decoration: none;
        box-shadow: 0 2px 6px rgba(249, 115, 22, 0.35);
    }

    #studio-calendar .fc .fc-daygrid-more-link:hover {
        background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
        box-shadow: 0 3px 8px rgba(249, 115, 22, 0.5);
    }

    /* Better narrow event handling */
    #studio-calendar .fc .fc-timegrid-event[style*="left:"][style*="right:"] .fc-event-main {
        padding: 2px 4px;
    }

    /* Very narrow events - icon only mode */
    #studio-calendar .fc .fc-timegrid-event.fc-event-narrow {
        min-height: 35px;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-narrow .fc-event-main {
        padding: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-narrow .fc-event-time,
    #studio-calendar .fc .fc-timegrid-event.fc-event-narrow .fc-event-title-container {
        display: none;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-narrow .fc-event-narrow-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    /* Popover for more events */
    #studio-calendar .fc .fc-popover {
        background: oklch(var(--b1));
        border: 1px solid oklch(var(--bc) / 0.2);
        border-radius: 0.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        z-index: 9999;
    }

    #studio-calendar .fc .fc-popover-header {
        background: oklch(var(--b2));
        padding: 0.5rem 0.75rem;
        font-weight: 600;
        font-size: 0.875rem;
        border-bottom: 1px solid oklch(var(--bc) / 0.1);
    }

    #studio-calendar .fc .fc-popover-body {
        padding: 0.5rem;
        max-height: 300px;
        overflow-y: auto;
    }

    #studio-calendar .fc .fc-popover .fc-event {
        margin-bottom: 4px;
        border-radius: 4px;
    }

    /* Custom event content styling */
    #studio-calendar .fc .fc-event-main-frame {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    #studio-calendar .fc .fc-timegrid-event .fc-event-title {
        display: flex;
        align-items: flex-start;
        gap: 2px;
    }

    #studio-calendar .fc .fc-timegrid-event .event-title-text {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
        min-width: 0;
    }

    /* When event is very narrow, hide text and show only icon */
    #studio-calendar .fc .fc-timegrid-event.fc-event-narrow .fc-event-main-frame {
        align-items: center;
        justify-content: center;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-narrow .fc-event-time,
    #studio-calendar .fc .fc-timegrid-event.fc-event-narrow .event-title-text {
        display: none !important;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-narrow .fc-event-title {
        justify-content: center;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-narrow .fc-event-title .icon-\[tabler--yoga\],
    #studio-calendar .fc .fc-timegrid-event.fc-event-narrow .fc-event-title .icon-\[tabler--massage\] {
        width: 1rem;
        height: 1rem;
        margin: 0;
    }

    /* Compact events - smaller text, tighter spacing */
    #studio-calendar .fc .fc-timegrid-event.fc-event-compact .fc-event-time {
        font-size: 0.6rem;
        line-height: 1;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-compact .fc-event-title {
        font-size: 0.65rem;
        line-height: 1.1;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-compact .fc-event-title .icon-\[tabler--yoga\],
    #studio-calendar .fc .fc-timegrid-event.fc-event-compact .fc-event-title .icon-\[tabler--massage\] {
        width: 0.625rem;
        height: 0.625rem;
    }

    /* Medium width events */
    #studio-calendar .fc .fc-timegrid-event.fc-event-medium .fc-event-time {
        font-size: 0.65rem;
    }

    #studio-calendar .fc .fc-timegrid-event.fc-event-medium .fc-event-title {
        font-size: 0.7rem;
    }

    /* Ensure event hover works well for narrow events */
    #studio-calendar .fc .fc-timegrid-event:hover {
        z-index: 10 !important;
    }

    /* Week view specific - better spacing between columns */
    #studio-calendar .fc .fc-timegrid-event-harness {
        margin-right: 1px;
    }

    /* Improve readability with background gradient for long events */
    #studio-calendar .fc .fc-timegrid-event .fc-event-main {
        position: relative;
    }

    /* Event slot gap for better visual separation */
    #studio-calendar .fc .fc-timegrid-event-harness-inset {
        inset-inline-end: 1px !important;
    }

    #studio-calendar .fc .fc-list-event-title {
        font-weight: 600;
    }

    #studio-calendar .fc .fc-list-day-cushion {
        background-color: oklch(var(--b2));
        font-weight: 600;
    }

    /* List view - colored dots matching event colors */
    #studio-calendar .fc .fc-list-event.fc-event-primary .fc-list-event-dot {
        border-color: #6366f1 !important;
    }
    #studio-calendar .fc .fc-list-event.fc-event-success .fc-list-event-dot {
        border-color: #10b981 !important;
    }
    #studio-calendar .fc .fc-list-event.fc-event-secondary .fc-list-event-dot {
        border-color: #8b5cf6 !important;
    }
    #studio-calendar .fc .fc-list-event.fc-event-warning .fc-list-event-dot {
        border-color: #f59e0b !important;
    }
    #studio-calendar .fc .fc-list-event.fc-event-info .fc-list-event-dot {
        border-color: #3b82f6 !important;
    }

    #studio-calendar .fc .fc-list-event td {
        border-color: oklch(var(--bc) / 0.1);
    }

    #studio-calendar .fc .fc-list-event:hover td {
        background-color: oklch(var(--b2));
    }

    /* Join button active state */
    .join-item.calendar-type-btn.btn-active {
        background-color: oklch(var(--p));
        border-color: oklch(var(--p));
        color: oklch(var(--pc));
    }

    /* Event Popover */
    .event-popover {
        position: fixed;
        display: none;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 0.75rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
        z-index: 9999;
        max-width: 280px;
    }

    .event-popover::before {
        content: '';
        position: absolute;
        left: -7px;
        top: 14px;
        width: 14px;
        height: 14px;
        background: #ffffff;
        border-left: 1px solid rgba(0, 0, 0, 0.1);
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        transform: rotate(45deg);
        box-shadow: -3px 3px 5px rgba(0, 0, 0, 0.05);
    }

    /* Dark mode support */
    [data-theme="dark"] .event-popover,
    .dark .event-popover {
        background: #1f2937;
        border-color: rgba(255, 255, 255, 0.1);
    }

    [data-theme="dark"] .event-popover::before,
    .dark .event-popover::before {
        background: #1f2937;
        border-color: rgba(255, 255, 255, 0.1);
    }

</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dropdown toggle function
    window.toggleDropdown = function(id) {
        const dropdown = document.getElementById(id);
        const allDropdowns = document.querySelectorAll('#booking-dropdown, #schedule-dropdown');

        // Close other dropdowns
        allDropdowns.forEach(function(d) {
            if (d.id !== id) {
                d.classList.add('hidden');
            }
        });

        // Toggle this dropdown
        dropdown.classList.toggle('hidden');
    };

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            document.querySelectorAll('#booking-dropdown, #schedule-dropdown').forEach(function(d) {
                d.classList.add('hidden');
            });
        }
    });

    const calendarEl = document.getElementById('studio-calendar');
    if (!calendarEl) return;

    const eventsUrl = calendarEl.dataset.eventsUrl || '/schedule/events';
    let currentType = 'all';
    let currentInstructor = '';
    let currentLocation = '';
    let currentClassPlan = '';
    let currentServicePlan = '';

    // Studio timezone for reference (times from API are already in this timezone)
    const studioTimezone = '{{ $timezone }}';

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        initialDate: new Date().toISOString().split('T')[0],
        timeZone: 'local', // Times from API are already in host timezone
        editable: false,
        dragScroll: true,
        dayMaxEvents: true,  // Show "+more" link when too many events
        direction: 'ltr',
        selectable: false,
        headerToolbar: {
            left: 'prev,next title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        buttonText: {
            month: 'Month',
            week: 'Week',
            day: 'Day',
            list: 'List'
        },
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        allDaySlot: false,
        nowIndicator: true,
        height: 'auto',
        expandRows: true,
        slotEventOverlap: false,
        eventMaxStack: 4, // Show max 4 events, then "+more" link
        eventMinHeight: 35,
        slotDuration: '00:30:00',
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        },
        moreLinkClick: 'popover', // Show popover with all events when clicking +more
        moreLinkClassNames: ['fc-more-link-styled'],
        moreLinkContent: function(args) {
            return {
                html: `<span class="fc-more-link-text">+${args.num} more</span>`
            };
        },
        eventWillUnmount: function(info) {
            // Clean up resize handler
            if (info.el._resizeHandler) {
                window.removeEventListener('resize', info.el._resizeHandler);
            }
        },
        eventContent: function(arg) {
            // Custom event rendering for better narrow event handling
            const props = arg.event.extendedProps;
            const typeIcon = props.type === 'class' ? 'yoga' : 'massage';

            // Create custom HTML structure
            const timeText = arg.timeText || '';
            const title = arg.event.title || '';

            return {
                html: `
                    <div class="fc-event-main-frame">
                        <div class="fc-event-time">${timeText}</div>
                        <div class="fc-event-title-container">
                            <div class="fc-event-title fc-sticky" title="${title}">
                                <span class="icon-[tabler--${typeIcon}] size-3 shrink-0 mr-1 inline-block align-middle"></span>
                                <span class="event-title-text">${title}</span>
                            </div>
                        </div>
                    </div>
                `
            };
        },
        events: function(info, successCallback, failureCallback) {
            const params = new URLSearchParams({
                start: info.startStr,
                end: info.endStr,
                type: currentType
            });

            if (currentInstructor) {
                params.append('instructor_id', currentInstructor);
            }
            if (currentLocation) {
                params.append('location_id', currentLocation);
            }
            if (currentClassPlan) {
                params.append('class_plan_id', currentClassPlan);
            }
            if (currentServicePlan) {
                params.append('service_plan_id', currentServicePlan);
            }

            fetch(`${eventsUrl}?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    // Map events to use CSS color classes (remove inline colors for consistency)
                    const mappedEvents = data.map(event => {
                        let colorClass = 'fc-event-primary';
                        if (event.extendedProps.type === 'service') {
                            colorClass = 'fc-event-success';
                        } else if (event.extendedProps.type === 'membership' || event.extendedProps.isMembershipSession) {
                            colorClass = 'fc-event-secondary';
                        }
                        if (event.extendedProps.status === 'draft') {
                            colorClass = 'fc-event-warning';
                        }
                        if (event.extendedProps.status === 'completed') {
                            colorClass = 'fc-event-info';
                        }
                        return {
                            ...event,
                            backgroundColor: null,
                            borderColor: null,
                            classNames: [colorClass]
                        };
                    });
                    successCallback(mappedEvents);
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            // Open drawer
            const [type, id] = info.event.id.split('_');
            const drawerId = type === 'class' ? `class-session-${id}` : `service-slot-${id}`;
            openDrawer(drawerId, info.jsEvent);
        },
        eventDidMount: function(info) {
            const props = info.event.extendedProps;
            const event = info.event;
            const el = info.el;

            // Check event width and apply compact/narrow/medium classes
            const applyWidthClasses = () => {
                const eventWidth = el.offsetWidth;
                el.classList.remove('fc-event-narrow', 'fc-event-compact', 'fc-event-medium');

                if (eventWidth > 0 && eventWidth < 45) {
                    // Very narrow - icon only mode
                    el.classList.add('fc-event-narrow');
                } else if (eventWidth >= 45 && eventWidth < 70) {
                    // Compact mode - smaller text
                    el.classList.add('fc-event-compact');
                } else if (eventWidth >= 70 && eventWidth < 100) {
                    // Medium mode - slightly smaller text
                    el.classList.add('fc-event-medium');
                }
            };

            // Apply immediately and after a short delay (for initial render)
            setTimeout(applyWidthClasses, 10);

            // Re-apply on window resize
            const resizeHandler = () => {
                requestAnimationFrame(applyWidthClasses);
            };
            window.addEventListener('resize', resizeHandler);

            // Cleanup on unmount (FullCalendar handles this via eventWillUnmount but we store handler)
            el._resizeHandler = resizeHandler;

            // Create popover content (times are already in host timezone from API)
            const startTime = event.start ? event.start.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }) : '';
            const endTime = event.end ? event.end.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }) : '';
            const type = props.type === 'class' ? 'Class' : 'Service';
            const typeIcon = props.type === 'class' ? 'yoga' : 'massage';
            const typeColor = props.type === 'class' ? 'primary' : 'success';

            let statusBadge = '';
            if (props.status === 'draft') {
                statusBadge = '<span class="badge badge-warning badge-xs">Draft</span>';
            } else if (props.status === 'published' || props.status === 'available') {
                statusBadge = '<span class="badge badge-success badge-xs">Published</span>';
            } else if (props.status === 'booked') {
                statusBadge = '<span class="badge badge-info badge-xs">Booked</span>';
            } else if (props.status === 'completed') {
                statusBadge = '<span class="badge badge-info badge-xs">Completed</span>';
            }

            let bookingInfo = '';
            if (props.type === 'class') {
                let checkedInHtml = props.checkedIn > 0 ? `
                    <div class="flex items-center gap-2">
                        <span class="icon-[tabler--user-check] size-3 text-success"></span>
                        <span>Checked In: <span class="font-medium">${props.checkedIn}</span></span>
                    </div>
                ` : '';

                let cancelledHtml = props.cancelled > 0 ? `
                    <div class="flex items-center gap-2">
                        <span class="icon-[tabler--user-x] size-3 text-error"></span>
                        <span>Cancelled: <span class="font-medium">${props.cancelled}</span></span>
                    </div>
                ` : '';

                bookingInfo = `
                    <div class="flex items-center gap-2">
                        <span class="icon-[tabler--users] size-3 text-base-content/60"></span>
                        <span>Booked: <span class="font-medium">${props.booked || 0}</span></span>
                    </div>
                    ${checkedInHtml}
                    ${cancelledHtml}
                `;
            }

            const popoverContent = `
                <div class="p-3 min-w-[220px]">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="icon-[tabler--${typeIcon}] size-4 text-${typeColor}"></span>
                        <span class="font-semibold text-sm">${event.title}</span>
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="badge badge-${typeColor} badge-xs">${type}</span>
                        ${statusBadge}
                    </div>
                    <div class="space-y-1 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--clock] size-3 text-base-content/60"></span>
                            <span>${startTime} - ${endTime}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--user] size-3 text-base-content/60"></span>
                            <span>${props.instructor}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--map-pin] size-3 text-base-content/60"></span>
                            <span>${props.location}</span>
                        </div>
                        ${bookingInfo}
                    </div>
                    <div class="mt-2 pt-2 border-t border-base-200 text-xs text-base-content/50">
                        Click for more details
                    </div>
                </div>
            `;

            // Create popover element
            const popover = document.createElement('div');
            popover.className = 'event-popover';
            popover.innerHTML = popoverContent;
            document.body.appendChild(popover);

            // Show popover on hover
            info.el.addEventListener('mouseenter', function(e) {
                const rect = info.el.getBoundingClientRect();
                popover.style.display = 'block';
                popover.style.left = (rect.right + 10) + 'px';
                popover.style.top = rect.top + 'px';

                // Adjust if going off screen
                const popRect = popover.getBoundingClientRect();
                if (popRect.right > window.innerWidth) {
                    popover.style.left = (rect.left - popRect.width - 10) + 'px';
                }
                if (popRect.bottom > window.innerHeight) {
                    popover.style.top = (window.innerHeight - popRect.height - 10) + 'px';
                }
            });

            info.el.addEventListener('mouseleave', function() {
                popover.style.display = 'none';
            });
        }
    });

    calendar.render();

    // Update current time display using studio timezone
    function updateCurrentTime() {
        const timeEl = document.getElementById('current-time');
        if (timeEl) {
            const now = new Date();
            timeEl.textContent = now.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                timeZone: studioTimezone
            });
        }
    }
    setInterval(updateCurrentTime, 60000); // Update every minute

    // Type toggle buttons
    const typeButtons = document.querySelectorAll('.calendar-type-btn');
    typeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            currentType = this.dataset.type;
            typeButtons.forEach(b => {
                b.classList.remove('btn-active', 'btn-primary');
            });
            this.classList.add('btn-active', 'btn-primary');
            calendar.refetchEvents();
        });
    });

    // Filter change handlers
    const typeFilter = document.getElementById('filter-type');
    const classPlanFilterDiv = document.getElementById('class-plan-filter');
    const servicePlanFilterDiv = document.getElementById('service-plan-filter');
    const classPlanFilter = document.getElementById('filter-class-plan');
    const servicePlanFilter = document.getElementById('filter-service-plan');

    if (typeFilter) {
        typeFilter.addEventListener('change', function() {
            currentType = this.value;

            // Show/hide secondary filters based on type
            if (classPlanFilterDiv && servicePlanFilterDiv) {
                if (this.value === 'class') {
                    classPlanFilterDiv.classList.remove('hidden');
                    servicePlanFilterDiv.classList.add('hidden');
                    // Reset service plan filter
                    if (servicePlanFilter) {
                        servicePlanFilter.value = '';
                        currentServicePlan = '';
                    }
                } else if (this.value === 'service') {
                    classPlanFilterDiv.classList.add('hidden');
                    servicePlanFilterDiv.classList.remove('hidden');
                    // Reset class plan filter
                    if (classPlanFilter) {
                        classPlanFilter.value = '';
                        currentClassPlan = '';
                    }
                } else {
                    // All types - hide both secondary filters
                    classPlanFilterDiv.classList.add('hidden');
                    servicePlanFilterDiv.classList.add('hidden');
                    // Reset both filters
                    if (classPlanFilter) {
                        classPlanFilter.value = '';
                        currentClassPlan = '';
                    }
                    if (servicePlanFilter) {
                        servicePlanFilter.value = '';
                        currentServicePlan = '';
                    }
                }
            }

            calendar.refetchEvents();
        });
    }

    if (classPlanFilter) {
        classPlanFilter.addEventListener('change', function() {
            currentClassPlan = this.value;
            calendar.refetchEvents();
        });
    }

    if (servicePlanFilter) {
        servicePlanFilter.addEventListener('change', function() {
            currentServicePlan = this.value;
            calendar.refetchEvents();
        });
    }

    const instructorFilter = document.getElementById('filter-instructor');
    if (instructorFilter) {
        instructorFilter.addEventListener('change', function() {
            currentInstructor = this.value;
            calendar.refetchEvents();
        });
    }

    const locationFilter = document.getElementById('filter-location');
    if (locationFilter) {
        locationFilter.addEventListener('change', function() {
            currentLocation = this.value;
            calendar.refetchEvents();
        });
    }

});

// Drawer functions
function openDrawer(id, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const drawer = document.getElementById('drawer-' + id);
    const backdrop = document.getElementById('drawer-backdrop');

    if (drawer) {
        // Close any open drawers first
        document.querySelectorAll('[id^="drawer-"]').forEach(d => {
            if (d.id !== 'drawer-backdrop' && d.id !== 'drawer-' + id) {
                d.classList.add('translate-x-full', 'hidden');
            }
        });

        // Show backdrop
        if (backdrop) {
            backdrop.classList.remove('hidden');
        }

        // Show and animate drawer
        drawer.classList.remove('hidden');
        setTimeout(() => {
            drawer.classList.remove('translate-x-full');
        }, 10);

        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }
}

function closeDrawer(id) {
    const drawer = document.getElementById('drawer-' + id);
    const backdrop = document.getElementById('drawer-backdrop');

    if (drawer) {
        drawer.classList.add('translate-x-full');
        setTimeout(() => {
            drawer.classList.add('hidden');
        }, 300);
    }

    // Hide backdrop
    if (backdrop) {
        backdrop.classList.add('hidden');
    }

    // Restore body scroll
    document.body.style.overflow = '';
}

function closeAllDrawers() {
    document.querySelectorAll('[id^="drawer-"]').forEach(drawer => {
        if (drawer.id !== 'drawer-backdrop') {
            drawer.classList.add('translate-x-full');
            setTimeout(() => {
                drawer.classList.add('hidden');
            }, 300);
        }
    });

    const backdrop = document.getElementById('drawer-backdrop');
    if (backdrop) {
        backdrop.classList.add('hidden');
    }

    document.body.style.overflow = '';
}

// Close drawer on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAllDrawers();
    }
});
</script>
@endpush
@endsection
