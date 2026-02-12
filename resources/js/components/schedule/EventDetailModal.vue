<template>
  <dialog
    ref="modal"
    class="modal modal-bottom sm:modal-middle"
    @close="handleClose"
  >
    <div class="modal-box max-w-md p-0 overflow-hidden">
      <!-- Header with gradient background -->
      <div
        class="px-6 py-5"
        :class="headerClass"
      >
        <div class="flex items-start justify-between">
          <div class="flex items-start gap-4">
            <div
              class="w-12 h-12 rounded-xl flex items-center justify-center bg-white/20 backdrop-blur"
            >
              <span
                class="size-6"
                :class="event?.type === 'class' ? 'icon-[tabler--yoga]' : 'icon-[tabler--massage]'"
              ></span>
            </div>
            <div>
              <h3 class="font-bold text-lg leading-tight">{{ event?.title }}</h3>
              <div class="flex items-center gap-2 mt-1">
                <span class="badge badge-sm bg-white/20 border-0">
                  {{ event?.type === 'class' ? 'Class' : 'Service' }}
                </span>
                <span
                  class="badge badge-sm"
                  :class="statusBadgeClass"
                >
                  {{ formatStatus(event?.extendedProps?.status) }}
                </span>
              </div>
            </div>
          </div>
          <button
            class="btn btn-sm btn-circle btn-ghost bg-white/10 hover:bg-white/20"
            @click="handleClose"
          >
            <span class="icon-[tabler--x] size-5"></span>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div
        v-if="event"
        class="px-6 py-5 space-y-4"
      >
        <!-- Conflict Warning -->
        <div
          v-if="event.extendedProps?.has_conflict"
          class="alert alert-error"
        >
          <span class="icon-[tabler--alert-triangle] size-5"></span>
          <div>
            <h4 class="font-semibold">Schedule Conflict Detected</h4>
            <p class="text-sm opacity-90">This event overlaps with another booking. Please review and resolve the conflict.</p>
          </div>
        </div>

        <!-- Date & Time -->
        <div class="flex items-start gap-4 p-4 bg-base-200/50 rounded-xl">
          <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
            <span class="icon-[tabler--calendar-event] size-5 text-primary"></span>
          </div>
          <div class="flex-1">
            <div class="font-semibold text-base-content">{{ formattedDate }}</div>
            <div class="text-sm text-base-content/70 mt-0.5">{{ formattedTime }}</div>
          </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-2 gap-3">
          <!-- Instructor -->
          <div
            v-if="event.extendedProps?.instructor"
            class="flex items-center gap-3 p-3 bg-base-200/30 rounded-lg"
          >
            <span class="icon-[tabler--user] size-5 text-base-content/50"></span>
            <div class="min-w-0">
              <div class="text-xs text-base-content/50 uppercase tracking-wide">Instructor</div>
              <div class="font-medium truncate">{{ event.extendedProps.instructor }}</div>
            </div>
          </div>

          <!-- Location -->
          <div
            v-if="event.extendedProps?.location"
            class="flex items-center gap-3 p-3 bg-base-200/30 rounded-lg"
          >
            <span class="icon-[tabler--map-pin] size-5 text-base-content/50"></span>
            <div class="min-w-0">
              <div class="text-xs text-base-content/50 uppercase tracking-wide">Location</div>
              <div class="font-medium truncate">
                {{ event.extendedProps.location }}
                <span
                  v-if="event.extendedProps.room"
                  class="text-base-content/60"
                >
                  / {{ event.extendedProps.room }}
                </span>
              </div>
            </div>
          </div>

          <!-- Price -->
          <div
            v-if="event.extendedProps?.price"
            class="flex items-center gap-3 p-3 bg-base-200/30 rounded-lg"
          >
            <span class="icon-[tabler--currency-dollar] size-5 text-success"></span>
            <div class="min-w-0">
              <div class="text-xs text-base-content/50 uppercase tracking-wide">Price</div>
              <div class="font-medium text-success">{{ formatPrice(event.extendedProps.price) }}</div>
            </div>
          </div>

          <!-- Capacity (classes only) -->
          <div
            v-if="event.type === 'class' && event.extendedProps?.capacity"
            class="flex items-center gap-3 p-3 bg-base-200/30 rounded-lg"
          >
            <span class="icon-[tabler--users] size-5 text-base-content/50"></span>
            <div class="min-w-0">
              <div class="text-xs text-base-content/50 uppercase tracking-wide">Capacity</div>
              <div class="font-medium">
                <span class="text-primary">{{ event.extendedProps.booked || 0 }}</span>
                <span class="text-base-content/50"> / {{ event.extendedProps.capacity }}</span>
              </div>
            </div>
          </div>

          <!-- Duration -->
          <div
            v-if="event.extendedProps?.duration"
            class="flex items-center gap-3 p-3 bg-base-200/30 rounded-lg"
          >
            <span class="icon-[tabler--clock] size-5 text-base-content/50"></span>
            <div class="min-w-0">
              <div class="text-xs text-base-content/50 uppercase tracking-wide">Duration</div>
              <div class="font-medium">{{ formatDuration(event.extendedProps.duration) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="px-6 py-4 bg-base-200/30 border-t border-base-200 flex items-center justify-end gap-2">
        <button
          class="btn btn-ghost"
          @click="handleClose"
        >
          Close
        </button>
        <a
          v-if="event?.extendedProps?.view_url"
          :href="event.extendedProps.view_url"
          class="btn btn-soft"
        >
          <span class="icon-[tabler--eye] size-4"></span>
          View Details
        </a>
        <a
          v-if="event?.extendedProps?.edit_url"
          :href="event.extendedProps.edit_url"
          class="btn btn-primary"
        >
          <span class="icon-[tabler--edit] size-4"></span>
          Edit
        </a>
      </div>
    </div>
    <form
      method="dialog"
      class="modal-backdrop"
    >
      <button @click="handleClose">close</button>
    </form>
  </dialog>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'

const props = defineProps({
  event: {
    type: Object,
    default: null
  },
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const modal = ref(null)

// Watch show prop to open/close modal
watch(() => props.show, async (newVal) => {
  await nextTick()
  if (newVal && modal.value) {
    modal.value.showModal()
  } else if (!newVal && modal.value) {
    modal.value.close()
  }
})

const handleClose = () => {
  if (modal.value) {
    modal.value.close()
  }
  emit('close')
}

// Header class based on type
const headerClass = computed(() => {
  const type = props.event?.type
  const hasConflict = props.event?.extendedProps?.has_conflict

  if (hasConflict) {
    return 'bg-gradient-to-br from-error to-error/80 text-error-content'
  }

  if (type === 'class') {
    return 'bg-gradient-to-br from-primary to-primary/80 text-primary-content'
  }

  return 'bg-gradient-to-br from-secondary to-secondary/80 text-secondary-content'
})

// Computed properties
const formattedDate = computed(() => {
  if (!props.event?.start) return ''
  const date = new Date(props.event.start)
  return date.toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const formattedTime = computed(() => {
  if (!props.event?.start || !props.event?.end) return ''
  const start = new Date(props.event.start)
  const end = new Date(props.event.end)

  const timeFormat = { hour: 'numeric', minute: '2-digit', hour12: true }
  const startTime = start.toLocaleTimeString('en-US', timeFormat)
  const endTime = end.toLocaleTimeString('en-US', timeFormat)

  return `${startTime} - ${endTime}`
})

const statusBadgeClass = computed(() => {
  const status = props.event?.extendedProps?.status
  const hasConflict = props.event?.extendedProps?.has_conflict

  if (hasConflict) {
    return 'badge-error'
  }

  switch (status) {
    case 'published':
    case 'available':
      return 'badge-success'
    case 'draft':
      return 'badge-warning'
    case 'booked':
      return 'badge-info'
    case 'cancelled':
      return 'badge-error'
    case 'blocked':
      return 'badge-neutral'
    default:
      return 'badge-ghost bg-white/20 border-0'
  }
})

// Methods
const formatStatus = (status) => {
  if (!status) return ''
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const formatPrice = (price) => {
  if (!price) return 'Free'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(price)
}

const formatDuration = (minutes) => {
  if (!minutes) return ''
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60

  if (hours > 0 && mins > 0) {
    return `${hours}h ${mins}m`
  } else if (hours > 0) {
    return `${hours} hour${hours > 1 ? 's' : ''}`
  }
  return `${mins} min`
}
</script>
