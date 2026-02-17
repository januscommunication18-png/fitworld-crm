/**
 * Schedule Calendar - FullCalendar integration
 */
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const eventsUrl = calendarEl.dataset.eventsUrl || '/schedule/events';
    let currentType = 'all';
    let currentInstructor = '';
    let currentLocation = '';

    // Initialize calendar
    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridDay,timeGridWeek,dayGridMonth,listWeek'
        },
        buttonText: {
            today: 'Today',
            day: 'Day',
            week: 'Week',
            month: 'Month',
            list: 'List'
        },
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        allDaySlot: false,
        nowIndicator: true,
        height: 'auto',
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
                .then(data => successCallback(data))
                .catch(error => {
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            showEventDetail(info.event);
        },
        eventDidMount: function(info) {
            // Add tooltip or extra info
            const props = info.event.extendedProps;
            info.el.title = `${info.event.title}\n${props.instructor}\n${props.location}`;
        },
        loading: function(isLoading) {
            // Could add a loading indicator here
        }
    });

    calendar.render();

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

    // Set initial active button
    const initialBtn = document.getElementById('type-all');
    if (initialBtn) {
        initialBtn.classList.add('btn-active', 'btn-primary');
    }

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

    // Event detail modal
    window.showEventDetail = function(event) {
        const props = event.extendedProps;
        const [type, id] = event.id.split('_');
        const startTime = event.start ? new Date(event.start).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }) : '';
        const endTime = event.end ? new Date(event.end).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }) : '';
        const dateStr = event.start ? new Date(event.start).toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' }) : '';

        let statusBadge = '';
        if (props.status === 'draft') {
            statusBadge = '<span class="badge badge-warning badge-sm">Draft</span>';
        } else if (props.status === 'published' || props.status === 'available') {
            statusBadge = '<span class="badge badge-success badge-sm">Published</span>';
        } else if (props.status === 'booked') {
            statusBadge = '<span class="badge badge-info badge-sm">Booked</span>';
        }

        let capacityInfo = '';
        if (type === 'class' && props.capacity) {
            capacityInfo = `
                <div class="flex items-center justify-between text-sm">
                    <span class="text-base-content/60">Booked</span>
                    <span class="font-medium">${props.booked}/${props.capacity}</span>
                </div>
            `;
        }

        const iconClass = type === 'class' ? 'yoga' : 'massage';
        const colorClass = type === 'class' ? 'primary' : 'success';
        const typeLabel = type === 'class' ? 'Class' : 'Service';
        const routePrefix = type === 'class' ? 'class-sessions' : 'service-slots';

        const content = `
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-${colorClass}/20 text-${colorClass} flex items-center justify-center">
                    <span class="icon-[tabler--${iconClass}] size-6"></span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">${event.title}</h3>
                    <span class="badge badge-sm badge-${colorClass}">${typeLabel}</span>
                    ${statusBadge}
                </div>
            </div>

            <div class="space-y-3 mb-4">
                <div class="flex items-center gap-2 text-sm">
                    <span class="icon-[tabler--calendar] size-4 text-base-content/60"></span>
                    <span>${dateStr}</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="icon-[tabler--clock] size-4 text-base-content/60"></span>
                    <span>${startTime} - ${endTime}</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="icon-[tabler--user] size-4 text-base-content/60"></span>
                    <span>${props.instructor}</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="icon-[tabler--map-pin] size-4 text-base-content/60"></span>
                    <span>${props.location}</span>
                </div>
                ${capacityInfo}
            </div>

            <div class="flex gap-2">
                <a href="/${routePrefix}/${id}" class="btn btn-primary flex-1">
                    <span class="icon-[tabler--external-link] size-4"></span>
                    View Details
                </a>
                <a href="/${routePrefix}/${id}/edit" class="btn btn-soft btn-primary">
                    <span class="icon-[tabler--edit] size-4"></span>
                    Edit
                </a>
            </div>
        `;

        const contentEl = document.getElementById('event-detail-content');
        const modalEl = document.getElementById('event-detail-modal');

        if (contentEl && modalEl) {
            contentEl.innerHTML = content;
            modalEl.classList.add('modal-open');
        }
    };

    window.closeEventModal = function() {
        const modalEl = document.getElementById('event-detail-modal');
        if (modalEl) {
            modalEl.classList.remove('modal-open');
        }
    };

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.closeEventModal();
        }
    });
});
