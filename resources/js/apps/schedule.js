import { createApp } from 'vue'
import ScheduleCalendar from '../components/schedule/ScheduleCalendar.vue'

const el = document.getElementById('schedule-calendar')

if (el) {
    createApp(ScheduleCalendar, {
        initialDate: el.dataset.initialDate || new Date().toISOString().split('T')[0],
        initialView: el.dataset.initialView || 'timeGridWeek',
        showClasses: el.dataset.showClasses !== 'false',
        showServices: el.dataset.showServices !== 'false',
    }).mount('#schedule-calendar')
}
