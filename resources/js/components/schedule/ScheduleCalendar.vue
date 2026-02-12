<template>
  <div class="schedule-calendar">
    <!-- Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4 p-4 bg-base-100 rounded-lg border border-base-200">
      <!-- Left: Navigation -->
      <div class="flex items-center gap-2">
        <div class="btn-group">
          <button
            class="btn btn-ghost btn-sm"
            @click="navigatePrev"
            title="Previous"
          >
            <span class="icon-[tabler--chevron-left] size-5"></span>
          </button>
          <button
            class="btn btn-ghost btn-sm px-4"
            @click="navigateToday"
          >
            Today
          </button>
          <button
            class="btn btn-ghost btn-sm"
            @click="navigateNext"
            title="Next"
          >
            <span class="icon-[tabler--chevron-right] size-5"></span>
          </button>
        </div>
        <h2 class="text-lg font-semibold ml-3">{{ currentTitle }}</h2>
      </div>

      <!-- Right: View Toggle & Filters -->
      <div class="flex items-center gap-3">
        <!-- Type Filter -->
        <div class="flex items-center gap-1 bg-base-200/50 rounded-lg p-1">
          <button
            class="btn btn-xs rounded-md"
            :class="showType === 'both' ? 'btn-primary' : 'btn-ghost'"
            @click="setShowType('both')"
          >
            All
          </button>
          <button
            class="btn btn-xs rounded-md"
            :class="showType === 'classes' ? 'btn-primary' : 'btn-ghost'"
            @click="setShowType('classes')"
          >
            <span class="icon-[tabler--yoga] size-3.5 mr-1"></span>
            Classes
          </button>
          <button
            class="btn btn-xs rounded-md"
            :class="showType === 'services' ? 'btn-secondary' : 'btn-ghost'"
            @click="setShowType('services')"
          >
            <span class="icon-[tabler--massage] size-3.5 mr-1"></span>
            Services
          </button>
        </div>

        <!-- View Toggle -->
        <div class="flex items-center gap-1 bg-base-200/50 rounded-lg p-1">
          <button
            class="btn btn-xs rounded-md"
            :class="currentView === 'timeGridDay' ? 'btn-neutral' : 'btn-ghost'"
            @click="setView('timeGridDay')"
          >
            <span class="icon-[tabler--calendar-event] size-3.5 mr-1"></span>
            Day
          </button>
          <button
            class="btn btn-xs rounded-md"
            :class="currentView === 'timeGridWeek' ? 'btn-neutral' : 'btn-ghost'"
            @click="setView('timeGridWeek')"
          >
            <span class="icon-[tabler--calendar-week] size-3.5 mr-1"></span>
            Week
          </button>
        </div>

        <!-- Legend -->
        <div class="hidden lg:flex items-center gap-3 text-xs text-base-content/60 border-l border-base-200 pl-3">
          <div class="flex items-center gap-1">
            <span class="w-3 h-3 rounded bg-primary"></span>
            <span>Class</span>
          </div>
          <div class="flex items-center gap-1">
            <span class="w-3 h-3 rounded bg-secondary"></span>
            <span>Service</span>
          </div>
          <div class="flex items-center gap-1">
            <span class="w-3 h-3 rounded bg-warning"></span>
            <span>Draft</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Alert -->
    <div
      v-if="hasError"
      class="alert alert-error mb-4"
    >
      <span class="icon-[tabler--alert-triangle] size-5"></span>
      <div>
        <h4 class="font-semibold">Failed to load events</h4>
        <p class="text-sm">{{ errorMessage }}</p>
      </div>
      <button class="btn btn-sm btn-ghost" @click="getCalendarApi()?.refetchEvents()">
        <span class="icon-[tabler--refresh] size-4"></span>
        Retry
      </button>
    </div>

    <!-- Loading Indicator -->
    <div
      v-if="isLoading"
      class="absolute inset-0 bg-base-100/50 flex items-center justify-center z-10 rounded-lg"
    >
      <span class="loading loading-spinner loading-lg text-primary"></span>
    </div>

    <!-- Calendar -->
    <div class="bg-base-100 rounded-lg border border-base-200 overflow-hidden relative">
      <FullCalendar
        ref="calendarRef"
        :options="calendarOptions"
      />
    </div>

    <!-- Events count indicator -->
    <div v-if="!isLoading && !hasError" class="text-sm text-base-content/60 mt-2 text-right">
      {{ eventCount }} event{{ eventCount !== 1 ? 's' : '' }} in view
    </div>

    <!-- Event Detail Modal -->
    <EventDetailModal
      :event="selectedEvent"
      :show="showEventModal"
      @close="closeEventModal"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'
import EventDetailModal from './EventDetailModal.vue'

// Props
const props = defineProps({
  initialDate: {
    type: String,
    default: () => new Date().toISOString().split('T')[0]
  },
  initialView: {
    type: String,
    default: 'timeGridWeek'
  },
  showClasses: {
    type: Boolean,
    default: true
  },
  showServices: {
    type: Boolean,
    default: true
  }
})

// Refs
const calendarRef = ref(null)
const currentView = ref(props.initialView)
const currentTitle = ref('')
const showType = ref('both')
const selectedEvent = ref(null)
const showEventModal = ref(false)
const isLoading = ref(false)
const hasError = ref(false)
const errorMessage = ref('')
const eventCount = ref(0)

// Initialize showType based on props
if (props.showClasses && !props.showServices) {
  showType.value = 'classes'
} else if (!props.showClasses && props.showServices) {
  showType.value = 'services'
}

// Event fetcher function for FullCalendar
const fetchEventsForCalendar = async (fetchInfo, successCallback, failureCallback) => {
  try {
    isLoading.value = true
    hasError.value = false
    errorMessage.value = ''

    const params = new URLSearchParams({
      start: fetchInfo.startStr,
      end: fetchInfo.endStr,
      type: showType.value
    })

    console.log('Fetching events:', `/schedule/api/events?${params}`)

    const response = await fetch(`/schedule/api/events?${params}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      },
      credentials: 'same-origin'
    })

    console.log('Response status:', response.status)

    if (response.ok) {
      const events = await response.json()
      console.log('Fetched events:', events)
      eventCount.value = events.length
      successCallback(events)
    } else {
      const errorText = await response.text()
      console.error('Failed to fetch events:', response.status, errorText)
      hasError.value = true
      errorMessage.value = `Error ${response.status}: ${errorText.substring(0, 100)}`
      failureCallback(new Error('Failed to fetch events'))
    }
  } catch (error) {
    console.error('Error fetching events:', error)
    hasError.value = true
    errorMessage.value = error.message
    failureCallback(error)
  } finally {
    isLoading.value = false
  }
}

// Calendar options - non-reactive to avoid re-initialization issues
const calendarOptions = {
  plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
  initialView: props.initialView,
  initialDate: props.initialDate,
  headerToolbar: false,
  events: fetchEventsForCalendar,
  slotMinTime: '05:00:00',
  slotMaxTime: '23:00:00',
  slotDuration: '00:15:00',
  slotLabelInterval: '01:00:00',
  slotLabelFormat: {
    hour: 'numeric',
    minute: '2-digit',
    meridiem: 'short'
  },
  allDaySlot: false,
  nowIndicator: true,
  dayMaxEvents: 4,
  eventMaxStack: 3,
  weekends: true,
  editable: false,
  selectable: false,
  eventClick: handleEventClick,
  datesSet: handleDatesSet,
  loading: handleLoading,
  eventContent: renderEventContent,
  eventClassNames: getEventClassNames,
  height: 'auto',
  expandRows: true,
  stickyHeaderDates: true,
  dayHeaderFormat: { weekday: 'short', day: 'numeric' },
  views: {
    timeGridWeek: {
      slotDuration: '00:15:00',
      slotLabelInterval: '01:00:00',
      eventMaxStack: 2,
    },
    timeGridDay: {
      slotDuration: '00:15:00',
      slotLabelInterval: '00:30:00',
      eventMaxStack: 4,
    }
  },
  eventTimeFormat: {
    hour: 'numeric',
    minute: '2-digit',
    meridiem: 'short'
  },
  moreLinkClick: 'popover',
  moreLinkText: (num) => `+${num} more`,
}

// Methods
const getCalendarApi = () => {
  return calendarRef.value?.getApi()
}

const navigatePrev = () => {
  getCalendarApi()?.prev()
}

const navigateNext = () => {
  getCalendarApi()?.next()
}

const navigateToday = () => {
  getCalendarApi()?.today()
}

const setView = (view) => {
  currentView.value = view
  getCalendarApi()?.changeView(view)
}

const setShowType = (type) => {
  showType.value = type
  // Refetch events when type changes
  getCalendarApi()?.refetchEvents()
}

const handleEventClick = (info) => {
  info.jsEvent.preventDefault()
  selectedEvent.value = {
    id: info.event.id,
    title: info.event.title,
    start: info.event.start,
    end: info.event.end,
    type: info.event.extendedProps?.type,
    extendedProps: info.event.extendedProps
  }
  showEventModal.value = true
}

const closeEventModal = () => {
  showEventModal.value = false
  selectedEvent.value = null
}

const handleDatesSet = (info) => {
  currentTitle.value = info.view.title
}

const handleLoading = (loading) => {
  // FullCalendar's loading callback
}

const renderEventContent = (arg) => {
  const event = arg.event
  const type = event.extendedProps?.type
  const status = event.extendedProps?.status
  const hasConflict = event.extendedProps?.has_conflict
  const isClass = type === 'class'

  // Format time
  const startTime = arg.event.start ? new Date(arg.event.start).toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  }) : ''

  // Type icon
  const typeIcon = isClass
    ? '<span class="fc-event-icon icon-[tabler--yoga]"></span>'
    : '<span class="fc-event-icon icon-[tabler--massage]"></span>'

  // Status indicator
  let statusIndicator = ''
  if (status === 'draft') {
    statusIndicator = '<span class="fc-event-status-dot bg-warning"></span>'
  } else if (hasConflict) {
    statusIndicator = '<span class="fc-event-conflict-icon icon-[tabler--alert-triangle]"></span>'
  }

  // Instructor
  const instructor = event.extendedProps?.instructor
    ? `<div class="fc-event-instructor">${event.extendedProps.instructor}</div>`
    : ''

  // Capacity for classes
  const capacity = isClass && event.extendedProps?.capacity
    ? `<span class="fc-event-capacity">${event.extendedProps.booked || 0}/${event.extendedProps.capacity}</span>`
    : ''

  const html = `
    <div class="fc-event-main-frame">
      <div class="fc-event-time-row">
        ${typeIcon}
        <span class="fc-event-time">${startTime}</span>
        ${statusIndicator}
        ${capacity}
      </div>
      <div class="fc-event-title-row">
        <span class="fc-event-title">${event.title}</span>
      </div>
      ${instructor}
    </div>
  `

  return { html }
}

const getEventClassNames = (arg) => {
  const classes = ['fc-event-custom']
  const type = arg.event.extendedProps?.type
  const status = arg.event.extendedProps?.status
  const hasConflict = arg.event.extendedProps?.has_conflict

  // Type class
  if (type === 'class') {
    classes.push('fc-event-class')
  } else {
    classes.push('fc-event-service')
  }

  // Status classes
  if (hasConflict) {
    classes.push('fc-event-has-conflict')
  }
  if (status === 'draft') {
    classes.push('fc-event-draft')
  } else if (status === 'cancelled') {
    classes.push('fc-event-cancelled')
  } else if (status === 'booked') {
    classes.push('fc-event-booked')
  }

  return classes
}

// Mount
onMounted(() => {
  const api = getCalendarApi()
  if (api) {
    currentTitle.value = api.view.title
  }
})
</script>

<style>
/* FullCalendar Theme Customization */
.schedule-calendar {
  position: relative;
}

.schedule-calendar .fc {
  --fc-border-color: hsl(var(--bc) / 0.1);
  --fc-button-bg-color: hsl(var(--b1));
  --fc-button-border-color: hsl(var(--bc) / 0.2);
  --fc-button-text-color: hsl(var(--bc));
  --fc-button-hover-bg-color: hsl(var(--b2));
  --fc-today-bg-color: hsl(var(--p) / 0.03);
  --fc-now-indicator-color: hsl(var(--er));
  --fc-event-border-color: transparent;
  --fc-page-bg-color: hsl(var(--b1));
  font-family: inherit;
}

/* Header styling */
.schedule-calendar .fc-col-header {
  background: hsl(var(--b2) / 0.5);
}

.schedule-calendar .fc-col-header-cell {
  padding: 12px 4px;
  font-weight: 600;
  font-size: 0.875rem;
}

.schedule-calendar .fc-col-header-cell.fc-day-today {
  background: hsl(var(--p) / 0.1);
}

.schedule-calendar .fc-col-header-cell.fc-day-today .fc-col-header-cell-cushion {
  color: hsl(var(--p));
  font-weight: 700;
}

/* Time grid */
.schedule-calendar .fc-timegrid-slot {
  height: 2rem;
}

.schedule-calendar .fc-timegrid-slot-label {
  font-size: 0.75rem;
  color: hsl(var(--bc) / 0.5);
}

.schedule-calendar .fc-timegrid-slot-minor {
  border-top-style: dotted;
  border-color: hsl(var(--bc) / 0.05);
}

/* Event base styles */
.schedule-calendar .fc-event-custom {
  border: none !important;
  border-radius: 6px !important;
  font-size: 0.75rem;
  padding: 2px 0 !important;
  margin: 1px 2px !important;
  cursor: pointer;
  transition: all 0.15s ease;
  overflow: hidden;
}

.schedule-calendar .fc-event-custom:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px hsl(var(--bc) / 0.15);
  z-index: 10 !important;
}

/* Event type colors */
.schedule-calendar .fc-event-class {
  background: linear-gradient(135deg, hsl(var(--p)) 0%, hsl(var(--p) / 0.85) 100%) !important;
  color: hsl(var(--pc)) !important;
}

.schedule-calendar .fc-event-service {
  background: linear-gradient(135deg, hsl(var(--s)) 0%, hsl(var(--s) / 0.85) 100%) !important;
  color: hsl(var(--sc)) !important;
}

/* Event status styles */
.schedule-calendar .fc-event-draft {
  background: linear-gradient(135deg, hsl(var(--wa)) 0%, hsl(var(--wa) / 0.85) 100%) !important;
  color: hsl(var(--wac)) !important;
}

.schedule-calendar .fc-event-cancelled {
  background: hsl(var(--bc) / 0.2) !important;
  color: hsl(var(--bc) / 0.5) !important;
  text-decoration: line-through;
}

.schedule-calendar .fc-event-booked {
  background: linear-gradient(135deg, hsl(var(--in)) 0%, hsl(var(--in) / 0.85) 100%) !important;
  color: hsl(var(--inc)) !important;
}

.schedule-calendar .fc-event-has-conflict {
  box-shadow: inset 0 0 0 2px hsl(var(--er)), 0 2px 8px hsl(var(--er) / 0.3) !important;
}

/* Event content layout */
.schedule-calendar .fc-event-main-frame {
  padding: 4px 8px;
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.schedule-calendar .fc-event-time-row {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.65rem;
  opacity: 0.9;
}

.schedule-calendar .fc-event-icon {
  width: 12px;
  height: 12px;
  flex-shrink: 0;
}

.schedule-calendar .fc-event-time {
  font-weight: 500;
}

.schedule-calendar .fc-event-status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.schedule-calendar .fc-event-conflict-icon {
  width: 12px;
  height: 12px;
  color: hsl(var(--er));
  animation: pulse 1.5s ease-in-out infinite;
}

.schedule-calendar .fc-event-capacity {
  margin-left: auto;
  background: hsl(var(--b1) / 0.2);
  padding: 0 4px;
  border-radius: 3px;
  font-size: 0.6rem;
}

.schedule-calendar .fc-event-title-row {
  flex: 1;
  min-height: 0;
  overflow: hidden;
}

.schedule-calendar .fc-event-title {
  font-weight: 600;
  font-size: 0.75rem;
  line-height: 1.2;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.schedule-calendar .fc-event-instructor {
  font-size: 0.65rem;
  opacity: 0.8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* More link */
.schedule-calendar .fc-more-link {
  font-size: 0.75rem;
  font-weight: 600;
  color: hsl(var(--p));
  background: hsl(var(--p) / 0.1);
  padding: 2px 8px;
  border-radius: 4px;
  margin: 2px;
}

.schedule-calendar .fc-more-link:hover {
  background: hsl(var(--p) / 0.2);
}

/* Popover for more events */
.schedule-calendar .fc-popover {
  background: hsl(var(--b1));
  border: 1px solid hsl(var(--bc) / 0.1);
  border-radius: 8px;
  box-shadow: 0 10px 40px hsl(var(--bc) / 0.15);
}

.schedule-calendar .fc-popover-header {
  background: hsl(var(--b2) / 0.5);
  padding: 8px 12px;
  font-weight: 600;
  border-radius: 8px 8px 0 0;
}

.schedule-calendar .fc-popover-body {
  padding: 8px;
}

/* Now indicator */
.schedule-calendar .fc-timegrid-now-indicator-line {
  border-color: hsl(var(--er));
  border-width: 2px;
}

.schedule-calendar .fc-timegrid-now-indicator-arrow {
  border-color: hsl(var(--er));
  border-top-color: transparent;
  border-bottom-color: transparent;
}

/* Scrollbar */
.schedule-calendar .fc-scroller::-webkit-scrollbar {
  width: 6px;
}

.schedule-calendar .fc-scroller::-webkit-scrollbar-track {
  background: transparent;
}

.schedule-calendar .fc-scroller::-webkit-scrollbar-thumb {
  background: hsl(var(--bc) / 0.2);
  border-radius: 3px;
}

.schedule-calendar .fc-scroller::-webkit-scrollbar-thumb:hover {
  background: hsl(var(--bc) / 0.3);
}

/* Animation */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

/* Responsive */
@media (max-width: 768px) {
  .schedule-calendar .fc-event-instructor {
    display: none;
  }

  .schedule-calendar .fc-event-capacity {
    display: none;
  }

  .schedule-calendar .fc-event-title {
    -webkit-line-clamp: 1;
  }
}
</style>
