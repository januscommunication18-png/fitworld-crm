@extends('layouts.dashboard')

@section('title', 'Schedule Calendar')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--calendar] me-1 size-4"></span> Schedule</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold">Schedule</h1>
            {{-- View Toggle --}}
            <div class="btn-group">
                <a href="{{ route('schedule.today') }}" class="btn btn-sm btn-soft">
                    <span class="icon-[tabler--calendar-time] size-4"></span>
                    <span class="hidden sm:inline">Today</span>
                </a>
                <a href="{{ route('schedule.calendar', ['date' => $date->format('Y-m-d'), 'view' => $view]) }}"
                   class="btn btn-sm btn-primary">
                    <span class="icon-[tabler--calendar-month] size-4"></span>
                    <span class="hidden sm:inline">Calendar</span>
                </a>
                <a href="{{ route('schedule.list') }}" class="btn btn-sm btn-soft">
                    <span class="icon-[tabler--list] size-4"></span>
                    <span class="hidden sm:inline">List</span>
                </a>
            </div>
        </div>

        {{-- Quick Add Dropdown --}}
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--plus] size-4"></span>
                Add New
            </div>
            <ul tabindex="0" class="dropdown-menu w-48">
                <li>
                    <a href="{{ route('class-sessions.create') }}" class="dropdown-item">
                        <span class="icon-[tabler--yoga] size-4"></span>
                        Class Session
                    </a>
                </li>
                <li>
                    <a href="{{ route('service-slots.create') }}" class="dropdown-item">
                        <span class="icon-[tabler--massage] size-4"></span>
                        Service Slot
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Calendar Container --}}
    <div
        id="schedule-calendar"
        data-initial-date="{{ $date->format('Y-m-d') }}"
        data-initial-view="{{ $view }}"
        data-show-classes="true"
        data-show-services="true"
        class="min-h-[600px]"
    >
        {{-- Skeleton Loading --}}
        <div class="skeleton-placeholder">
            <div class="card bg-base-100">
                <div class="card-body">
                    {{-- Toolbar Skeleton --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="skeleton h-8 w-8 rounded-full"></div>
                            <div class="skeleton h-8 w-16 rounded"></div>
                            <div class="skeleton h-8 w-8 rounded-full"></div>
                            <div class="skeleton h-6 w-40 rounded ml-2"></div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="skeleton h-8 w-32 rounded"></div>
                            <div class="skeleton h-8 w-24 rounded"></div>
                        </div>
                    </div>

                    {{-- Calendar Grid Skeleton --}}
                    <div class="grid grid-cols-7 gap-1">
                        {{-- Header --}}
                        @for($i = 0; $i < 7; $i++)
                            <div class="skeleton h-10 rounded"></div>
                        @endfor
                        {{-- Time slots --}}
                        @for($i = 0; $i < 42; $i++)
                            <div class="skeleton h-16 rounded"></div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/apps/schedule.js')
<script>
    // Hide skeleton when Vue app mounts
    document.addEventListener('DOMContentLoaded', function() {
        const observer = new MutationObserver(function(mutations) {
            const placeholder = document.querySelector('.skeleton-placeholder');
            const hasVueContent = document.querySelector('.schedule-calendar');
            if (hasVueContent && placeholder) {
                placeholder.style.display = 'none';
                observer.disconnect();
            }
        });

        const calendarEl = document.getElementById('schedule-calendar');
        if (calendarEl) {
            observer.observe(calendarEl, { childList: true, subtree: true });
        }
    });
</script>
@endpush
