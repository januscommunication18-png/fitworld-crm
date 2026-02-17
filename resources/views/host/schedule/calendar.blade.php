@extends('layouts.dashboard')

@section('title', 'Studio Calendar')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('schedule.index') }}"><span class="icon-[tabler--calendar] me-1 size-4"></span> Schedule</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Studio Calendar</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Studio Calendar</h1>
            <p class="text-base-content/60 mt-1">View your classes and services in calendar format.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Current Time Display --}}
            <div class="flex items-center gap-2 px-3 py-2 bg-base-200 rounded-lg">
                <span class="icon-[tabler--clock] size-5 text-primary"></span>
                <span id="current-time" class="font-semibold text-base-content">{{ now()->format('g:i A') }}</span>
            </div>
            <a href="{{ route('class-sessions.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Class
            </a>
            <a href="{{ route('service-slots.create') }}" class="btn btn-soft btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Service
            </a>
        </div>
    </div>

    {{-- Sub Navigation --}}
    @include('host.schedule.partials.sub-nav')

    {{-- Filters Card --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body py-4 px-5">
            <div class="flex flex-wrap gap-5 items-center">
                {{-- Type Toggle --}}
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-base-content">Show:</span>
                    <div class="join">
                        <button type="button" class="join-item btn btn-sm calendar-type-btn btn-active btn-primary" data-type="all" id="type-all">
                            <span class="icon-[tabler--layout-grid] size-4 mr-1"></span>
                            All
                        </button>
                        <button type="button" class="join-item btn btn-sm calendar-type-btn" data-type="class" id="type-class">
                            <span class="icon-[tabler--yoga] size-4 mr-1"></span>
                            Classes
                        </button>
                        <button type="button" class="join-item btn btn-sm calendar-type-btn" data-type="service" id="type-service">
                            <span class="icon-[tabler--massage] size-4 mr-1"></span>
                            Services
                        </button>
                    </div>
                </div>

                <div class="h-8 w-px bg-base-300 hidden sm:block"></div>

                {{-- Location Filter --}}
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--map-pin] size-4 text-base-content/60"></span>
                    <select id="filter-location" class="select select-sm w-44 select-bordered">
                        <option value="">All Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Instructor Filter --}}
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--user] size-4 text-base-content/60"></span>
                    <select id="filter-instructor" class="select select-sm w-48 select-bordered">
                        <option value="">All Instructors</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Legend --}}
                <div class="flex items-center gap-4 ml-auto">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-primary shadow-sm"></span>
                        <span class="text-sm font-medium text-base-content">Class</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-success shadow-sm"></span>
                        <span class="text-sm font-medium text-base-content">Service</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-warning shadow-sm"></span>
                        <span class="text-sm font-medium text-base-content">Draft</span>
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
    #studio-calendar .fc .fc-event.fc-event-primary {
        background-color: #6366f1 !important;
        border-color: #6366f1 !important;
        color: #ffffff !important;
    }

    #studio-calendar .fc-event-success,
    #studio-calendar .fc .fc-event.fc-event-success {
        background-color: #10b981 !important;
        border-color: #10b981 !important;
        color: #ffffff !important;
    }

    #studio-calendar .fc-event-warning,
    #studio-calendar .fc .fc-event.fc-event-warning {
        background-color: #f59e0b !important;
        border-color: #f59e0b !important;
        color: #000000 !important;
    }

    #studio-calendar .fc-event-info,
    #studio-calendar .fc .fc-event.fc-event-info {
        background-color: #3b82f6 !important;
        border-color: #3b82f6 !important;
        color: #ffffff !important;
    }

    #studio-calendar .fc-event-error,
    #studio-calendar .fc .fc-event.fc-event-error {
        background-color: #ef4444 !important;
        border-color: #ef4444 !important;
        color: #ffffff !important;
    }

    /* Event text colors */
    #studio-calendar .fc .fc-event.fc-event-primary .fc-event-main,
    #studio-calendar .fc .fc-event.fc-event-primary .fc-event-time,
    #studio-calendar .fc .fc-event.fc-event-primary .fc-event-title,
    #studio-calendar .fc .fc-event.fc-event-success .fc-event-main,
    #studio-calendar .fc .fc-event.fc-event-success .fc-event-time,
    #studio-calendar .fc .fc-event.fc-event-success .fc-event-title,
    #studio-calendar .fc .fc-event.fc-event-info .fc-event-main,
    #studio-calendar .fc .fc-event.fc-event-info .fc-event-time,
    #studio-calendar .fc .fc-event.fc-event-info .fc-event-title {
        color: #ffffff !important;
        font-weight: 600;
    }

    #studio-calendar .fc .fc-event.fc-event-warning .fc-event-main,
    #studio-calendar .fc .fc-event.fc-event-warning .fc-event-time,
    #studio-calendar .fc .fc-event.fc-event-warning .fc-event-title {
        color: #000000 !important;
        font-weight: 600;
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

    /* Ensure events show full content */
    #studio-calendar .fc .fc-timegrid-event {
        overflow: visible;
        min-height: 50px;
    }

    #studio-calendar .fc .fc-timegrid-col-events {
        margin: 0 2px;
    }

    #studio-calendar .fc .fc-timegrid-event .fc-event-title-container {
        flex-grow: 1;
        flex-shrink: 0;
    }

    #studio-calendar .fc .fc-timegrid-event .fc-event-title {
        white-space: normal;
        overflow: visible;
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
    const calendarEl = document.getElementById('studio-calendar');
    if (!calendarEl) return;

    const eventsUrl = calendarEl.dataset.eventsUrl || '/schedule/events';
    let currentType = 'all';
    let currentInstructor = '';
    let currentLocation = '';

    // Initialize calendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        initialDate: new Date().toISOString().split('T')[0],
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
        eventMinHeight: 50,
        slotDuration: '00:30:00',
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
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

            fetch(`${eventsUrl}?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    // Map events to use FlyonUI color classes
                    const mappedEvents = data.map(event => {
                        let colorClass = 'fc-event-primary';
                        if (event.extendedProps.type === 'service') {
                            colorClass = 'fc-event-success';
                        }
                        if (event.extendedProps.status === 'draft') {
                            colorClass = 'fc-event-warning';
                        }
                        if (event.extendedProps.status === 'completed') {
                            colorClass = 'fc-event-info';
                        }
                        return {
                            ...event,
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

            // Create popover content
            const startTime = event.start ? new Date(event.start).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }) : '';
            const endTime = event.end ? new Date(event.end).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }) : '';
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

    // Update current time display
    function updateCurrentTime() {
        const timeEl = document.getElementById('current-time');
        if (timeEl) {
            const now = new Date();
            timeEl.textContent = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
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
