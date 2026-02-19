@props([
    'type' => 'all',           // 'all', 'class', 'service' - controls which type options are available
    'classPlanId' => null,     // Lock to specific class (hides class filter)
    'servicePlanId' => null,   // Lock to specific service (hides service filter)
    'defaultType' => null,     // Pre-select type filter (when type='all')
    'defaultClassPlanId' => null,  // Pre-select class in filter
    'defaultServicePlanId' => null, // Pre-select service in filter
    'showFilters' => true,     // Show filter dropdowns
    'showHeader' => true,      // Show header with buttons
    'showLegend' => true,      // Show legend
    'height' => 'auto',        // Calendar height
    'initialView' => 'timeGridWeek', // Default view
    'lazy' => false,           // Lazy load - init when visible
])

@php
    $host = auth()->user()->host;
    $timezone = $host->timezone ?? config('app.timezone', 'America/New_York');
    $locations = $host->locations()->orderBy('name')->get();
    $instructors = $host->instructors()->active()->orderBy('name')->get();
    $classPlans = $host->classPlans()->where('is_active', true)->orderBy('name')->get();
    $servicePlans = $host->servicePlans()->where('is_active', true)->orderBy('name')->get();
    $calendarId = 'calendar-' . uniqid();
@endphp

<div class="studio-calendar-component space-y-4" id="{{ $calendarId }}-wrapper">
    @if($showHeader)
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="flex items-center gap-2 px-3 py-2 bg-base-200 rounded-lg">
            <span class="icon-[tabler--clock] size-5 text-primary"></span>
            <span class="current-time font-semibold text-base-content">{{ now()->setTimezone($timezone)->format('g:i A') }}</span>
            <span class="text-xs text-base-content/50">{{ str_replace('_', ' ', $timezone) }}</span>
        </div>
        <div class="flex items-center gap-2">
            @if($type === 'class' && $classPlanId)
            <a href="{{ route('class-sessions.create', ['class_plan_id' => $classPlanId]) }}" class="btn btn-sm btn-primary">
                <span class="icon-[tabler--plus] size-4"></span>
                Add Session
            </a>
            @elseif($type === 'service' && $servicePlanId)
            <a href="{{ route('service-slots.create', ['service_plan_id' => $servicePlanId]) }}" class="btn btn-sm btn-primary">
                <span class="icon-[tabler--plus] size-4"></span>
                Add Slot
            </a>
            @endif
        </div>
    </div>
    @endif

    @if($showFilters)
    <div class="flex flex-wrap gap-4 items-center">
        @if($type === 'all')
        {{-- Type Filter --}}
        <div class="flex items-center gap-2">
            <span class="icon-[tabler--category] size-4 text-base-content/60"></span>
            <select class="filter-type select select-sm w-40 select-bordered">
                <option value="all" {{ $defaultType === null || $defaultType === 'all' ? 'selected' : '' }}>All Types</option>
                <option value="class" {{ $defaultType === 'class' ? 'selected' : '' }}>Classes Only</option>
                <option value="service" {{ $defaultType === 'service' ? 'selected' : '' }}>Services Only</option>
            </select>
        </div>

        {{-- Class Plan Filter --}}
        <div class="filter-class-plan-wrapper flex items-center gap-2 {{ $defaultType === 'class' ? '' : 'hidden' }}">
            <span class="icon-[tabler--yoga] size-4 text-primary"></span>
            <select class="filter-class-plan select select-sm w-48 select-bordered">
                <option value="">All Classes</option>
                @foreach($classPlans as $plan)
                    <option value="{{ $plan->id }}" {{ $defaultClassPlanId == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Service Plan Filter --}}
        <div class="filter-service-plan-wrapper flex items-center gap-2 {{ $defaultType === 'service' ? '' : 'hidden' }}">
            <span class="icon-[tabler--massage] size-4 text-success"></span>
            <select class="filter-service-plan select select-sm w-48 select-bordered">
                <option value="">All Services</option>
                @foreach($servicePlans as $plan)
                    <option value="{{ $plan->id }}" {{ $defaultServicePlanId == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Location Filter --}}
        <div class="flex items-center gap-2">
            <span class="icon-[tabler--map-pin] size-4 text-base-content/60"></span>
            <select class="filter-location select select-sm w-44 select-bordered">
                <option value="">All Locations</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Instructor Filter --}}
        <div class="flex items-center gap-2">
            <span class="icon-[tabler--user] size-4 text-base-content/60"></span>
            <select class="filter-instructor select select-sm w-48 select-bordered">
                <option value="">All Instructors</option>
                @foreach($instructors as $instructor)
                    <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                @endforeach
            </select>
        </div>

        @if($showLegend)
        {{-- Legend --}}
        <div class="flex items-center gap-4 ml-auto">
            @if($type === 'all' || $type === 'class')
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-primary shadow-sm"></span>
                <span class="text-sm font-medium text-base-content">Class</span>
            </div>
            @endif
            @if($type === 'all' || $type === 'service')
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-success shadow-sm"></span>
                <span class="text-sm font-medium text-base-content">Service</span>
            </div>
            @endif
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-warning shadow-sm"></span>
                <span class="text-sm font-medium text-base-content">Draft</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-error shadow-sm"></span>
                <span class="text-sm font-medium text-base-content">Conflict</span>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Calendar Container --}}
    <div id="{{ $calendarId }}"
         class="studio-calendar-el"
         data-events-url="{{ route('schedule.events') }}"
         data-type="{{ $type }}"
         data-default-type="{{ $defaultType ?? $type }}"
         data-class-plan-id="{{ $classPlanId }}"
         data-service-plan-id="{{ $servicePlanId }}"
         data-default-class-plan-id="{{ $defaultClassPlanId }}"
         data-default-service-plan-id="{{ $defaultServicePlanId }}"
         data-initial-view="{{ $initialView }}"
         data-height="{{ $height }}"
         data-timezone="{{ $timezone }}"
         data-lazy="{{ $lazy ? 'true' : 'false' }}">
    </div>
</div>

<style>
    #{{ $calendarId }} .fc {
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
    }

    #{{ $calendarId }} .fc .fc-toolbar-title {
        font-size: 1.125rem;
        font-weight: 600;
    }

    #{{ $calendarId }} .fc .fc-button {
        font-weight: 500;
        font-size: 0.8125rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
    }

    #{{ $calendarId }} .fc .fc-button-primary:not(:disabled).fc-button-active {
        background-color: oklch(var(--p));
        border-color: oklch(var(--p));
        color: oklch(var(--pc));
    }

    #{{ $calendarId }} .fc .fc-event {
        border-radius: 0.375rem;
        border: none;
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 2px 6px;
        cursor: pointer;
    }

    #{{ $calendarId }} .fc .fc-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    #{{ $calendarId }} .fc-event-primary { background-color: #6366f1 !important; border-color: #6366f1 !important; }
    #{{ $calendarId }} .fc-event-primary * { color: #ffffff !important; }
    #{{ $calendarId }} .fc-event-success { background-color: #10b981 !important; border-color: #10b981 !important; }
    #{{ $calendarId }} .fc-event-success * { color: #ffffff !important; }
    #{{ $calendarId }} .fc-event-warning { background-color: #f59e0b !important; border-color: #f59e0b !important; }
    #{{ $calendarId }} .fc-event-warning * { color: #000000 !important; }
    #{{ $calendarId }} .fc-event-error { background-color: #ef4444 !important; border-color: #ef4444 !important; }
    #{{ $calendarId }} .fc-event-error * { color: #ffffff !important; }

    #{{ $calendarId }} .fc .fc-daygrid-event-dot { display: none; }
    #{{ $calendarId }} .fc .fc-timegrid-event .fc-event-main { padding: 4px 6px; }

    #{{ $calendarId }} .fc .fc-timegrid-more-link {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        border: none;
        border-radius: 0.5rem;
        padding: 6px 10px;
        font-size: 0.8rem;
        font-weight: 800;
        color: #ffffff !important;
    }
</style>

<script>
(function() {
    const wrapper = document.getElementById('{{ $calendarId }}-wrapper');
    const calendarEl = document.getElementById('{{ $calendarId }}');
    if (!calendarEl) return;

    const isLazy = calendarEl.dataset.lazy === 'true';

    function initCalendar() {
        if (calendarEl.dataset.initialized === 'true') return;
        if (!window.FullCalendar) {
            console.warn('FullCalendar not loaded');
            return;
        }

        const eventsUrl = calendarEl.dataset.eventsUrl;
        const timezone = calendarEl.dataset.timezone;
        // Use default values for initial load
        let currentType = calendarEl.dataset.defaultType || calendarEl.dataset.type || 'all';
        let currentClassPlan = calendarEl.dataset.classPlanId || calendarEl.dataset.defaultClassPlanId || '';
        let currentServicePlan = calendarEl.dataset.servicePlanId || calendarEl.dataset.defaultServicePlanId || '';
        let currentInstructor = '';
        let currentLocation = '';

        const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: calendarEl.dataset.initialView || 'timeGridWeek',
        initialDate: new Date().toISOString().split('T')[0],
        timeZone: 'local',
        editable: false,
        dayMaxEvents: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        buttonText: {
            today: 'Today',
            month: 'Month',
            week: 'Week',
            day: 'Day',
            list: 'List'
        },
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        allDaySlot: false,
        nowIndicator: true,
        height: calendarEl.dataset.height === 'auto' ? 'auto' : parseInt(calendarEl.dataset.height) || 600,
        eventMaxStack: 4,
        slotDuration: '00:30:00',
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        },
        moreLinkClick: 'popover',
        events: function(info, successCallback, failureCallback) {
            const params = new URLSearchParams({
                start: info.startStr,
                end: info.endStr,
                type: currentType
            });

            if (currentInstructor) params.append('instructor_id', currentInstructor);
            if (currentLocation) params.append('location_id', currentLocation);
            if (currentClassPlan) params.append('class_plan_id', currentClassPlan);
            if (currentServicePlan) params.append('service_plan_id', currentServicePlan);

            fetch(`${eventsUrl}?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    const mappedEvents = data.map(event => {
                        let colorClass = 'fc-event-primary';
                        if (event.extendedProps.type === 'service') colorClass = 'fc-event-success';
                        if (event.extendedProps.status === 'draft') colorClass = 'fc-event-warning';
                        if (event.extendedProps.hasConflict) colorClass = 'fc-event-error';
                        return { ...event, backgroundColor: null, borderColor: null, classNames: [colorClass] };
                    });
                    successCallback(mappedEvents);
                })
                .catch(error => failureCallback(error));
        },
        eventClick: function(info) {
            const [type, id] = info.event.id.split('_');
            if (type === 'class') {
                window.location.href = '/class-sessions/' + id;
            } else {
                window.location.href = '/service-slots/' + id;
            }
        }
    });

    calendar.render();

    // Filter handlers
    const typeFilter = wrapper.querySelector('.filter-type');
    const classPlanWrapper = wrapper.querySelector('.filter-class-plan-wrapper');
    const servicePlanWrapper = wrapper.querySelector('.filter-service-plan-wrapper');
    const classPlanFilter = wrapper.querySelector('.filter-class-plan');
    const servicePlanFilter = wrapper.querySelector('.filter-service-plan');
    const locationFilter = wrapper.querySelector('.filter-location');
    const instructorFilter = wrapper.querySelector('.filter-instructor');

    if (typeFilter) {
        typeFilter.addEventListener('change', function() {
            currentType = this.value;
            if (classPlanWrapper && servicePlanWrapper) {
                classPlanWrapper.classList.toggle('hidden', this.value !== 'class');
                servicePlanWrapper.classList.toggle('hidden', this.value !== 'service');
                if (this.value !== 'class') { currentClassPlan = ''; if (classPlanFilter) classPlanFilter.value = ''; }
                if (this.value !== 'service') { currentServicePlan = ''; if (servicePlanFilter) servicePlanFilter.value = ''; }
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

    if (locationFilter) {
        locationFilter.addEventListener('change', function() {
            currentLocation = this.value;
            calendar.refetchEvents();
        });
    }

        if (instructorFilter) {
            instructorFilter.addEventListener('change', function() {
                currentInstructor = this.value;
                calendar.refetchEvents();
            });
        }

        calendarEl.dataset.initialized = 'true';
    }

    // Lazy loading support
    if (isLazy) {
        // Register for external initialization
        window.initStudioCalendar = window.initStudioCalendar || {};
        window.initStudioCalendar['{{ $calendarId }}'] = initCalendar;

        // Also try IntersectionObserver for auto-init when visible
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        initCalendar();
                        observer.disconnect();
                    }
                });
            }, { threshold: 0.1 });
            observer.observe(calendarEl);
        }
    } else {
        // Initialize immediately
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCalendar);
        } else {
            initCalendar();
        }
    }
})();
</script>
