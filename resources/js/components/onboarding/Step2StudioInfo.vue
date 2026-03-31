<template>
    <div class="card">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-2">Studio Information</h2>
            <p class="text-base-content/70 mb-6">Tell us about your studio to personalize your experience.</p>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <!-- Studio Name -->
                <div class="form-control">
                    <label class="label" for="studio_name">
                        <span class="label-text">Studio Name <span class="text-error">*</span></span>
                    </label>
                    <input
                        id="studio_name"
                        v-model="form.studio_name"
                        type="text"
                        class="input input-bordered"
                        :class="{ 'input-error': errors.studio_name }"
                        placeholder="Your Studio Name"
                        required
                    >
                    <label v-if="errors.studio_name" class="label">
                        <span class="label-text-alt text-error">{{ errors.studio_name[0] }}</span>
                    </label>
                </div>

                <!-- Studio Structure -->
                <div class="form-control">
                    <label class="label" for="studio_structure">
                        <span class="label-text">How is your studio structured? <span class="text-error">*</span></span>
                    </label>
                    <select
                        id="studio_structure"
                        v-model="form.studio_structure"
                        class="select select-bordered"
                        :class="{ 'select-error': errors.studio_structure }"
                    >
                        <option value="solo">Solo (Just me)</option>
                        <option value="team">With a Team (Staff members)</option>
                    </select>
                </div>

                <!-- Subdomain -->
                <div class="form-control">
                    <label class="label" for="subdomain">
                        <span class="label-text">Booking Page URL <span class="text-error">*</span></span>
                    </label>
                    <div class="join w-full">
                        <input
                            id="subdomain"
                            v-model="form.subdomain"
                            type="text"
                            class="input input-bordered join-item flex-1"
                            :class="{ 'input-error': errors.subdomain }"
                            placeholder="your-studio"
                            @input="formatSubdomain"
                            required
                        >
                        <span class="join-item flex items-center px-4 bg-base-200 text-base-content/70 text-sm">
                            .{{ bookingDomain }}
                        </span>
                    </div>
                    <label v-if="errors.subdomain" class="label">
                        <span class="label-text-alt text-error">{{ errors.subdomain[0] }}</span>
                    </label>
                    <label v-else class="label">
                        <span class="label-text-alt text-base-content/60">Only lowercase letters, numbers, and hyphens</span>
                    </label>
                </div>

                <!-- Studio Type -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Studio Type</span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <label
                            v-for="type in studioTypes"
                            :key="type.value"
                            class="cursor-pointer"
                        >
                            <input
                                type="checkbox"
                                :value="type.value"
                                v-model="form.studio_types"
                                class="hidden"
                            >
                            <span
                                class="badge badge-lg"
                                :class="form.studio_types.includes(type.value) ? 'badge-primary' : 'badge-outline'"
                            >
                                {{ type.label }}
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Language & Currency Row -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label" for="default_language_app">
                            <span class="label-text">Language</span>
                        </label>
                        <select
                            id="default_language_app"
                            v-model="form.default_language_app"
                            class="select select-bordered"
                        >
                            <option value="en">English</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label" for="default_currency">
                            <span class="label-text">Currency</span>
                        </label>
                        <select
                            id="default_currency"
                            v-model="form.default_currency"
                            class="select select-bordered"
                        >
                            <option value="USD">USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="GBP">GBP (£)</option>
                            <option value="CAD">CAD ($)</option>
                            <option value="AUD">AUD ($)</option>
                        </select>
                    </div>
                </div>

                <!-- Cancellation Policy -->
                <div class="form-control">
                    <label class="label" for="cancellation_window_hours">
                        <span class="label-text">Booking Cancellation Window</span>
                    </label>
                    <select
                        id="cancellation_window_hours"
                        v-model="form.cancellation_window_hours"
                        class="select select-bordered"
                    >
                        <option :value="0">No cancellation window</option>
                        <option :value="1">1 hour before</option>
                        <option :value="2">2 hours before</option>
                        <option :value="6">6 hours before</option>
                        <option :value="12">12 hours before</option>
                        <option :value="24">24 hours before</option>
                        <option :value="48">48 hours before</option>
                        <option :value="72">72 hours before</option>
                    </select>
                    <label class="label">
                        <span class="label-text-alt text-base-content/60">Clients cannot cancel within this window</span>
                    </label>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between mt-6 pt-4 border-t border-base-300">
                    <button type="button" class="btn btn-ghost" @click="$emit('prev')">
                        <span class="icon-[tabler--arrow-left] size-4 mr-1"></span>
                        Back
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary"
                        :class="{ 'loading': loading }"
                        :disabled="loading || !isValid"
                    >
                        Continue
                        <span class="icon-[tabler--arrow-right] size-4 ml-1"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
    formData: { type: Object, default: () => ({}) },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['next', 'prev', 'update'])

const bookingDomain = window.location.hostname.replace('projectfit.local', 'projectfit.local:8888')

const studioTypes = [
    { value: 'yoga', label: 'Yoga' },
    { value: 'pilates', label: 'Pilates' },
    { value: 'fitness', label: 'Fitness' },
    { value: 'dance', label: 'Dance' },
    { value: 'martial_arts', label: 'Martial Arts' },
    { value: 'crossfit', label: 'CrossFit' },
    { value: 'wellness', label: 'Wellness' },
    { value: 'other', label: 'Other' },
]

const form = ref({
    studio_name: props.formData.studio_name || '',
    studio_structure: props.formData.studio_structure || 'solo',
    subdomain: props.formData.subdomain || '',
    studio_types: props.formData.studio_types || [],
    studio_categories: props.formData.studio_categories || [],
    default_language_app: props.formData.default_language_app || 'en',
    default_currency: props.formData.default_currency || 'USD',
    cancellation_window_hours: props.formData.cancellation_window_hours || 12,
})

const isValid = computed(() => {
    return form.value.studio_name && form.value.subdomain
})

function formatSubdomain() {
    form.value.subdomain = form.value.subdomain
        .toLowerCase()
        .replace(/[^a-z0-9-]/g, '')
        .replace(/--+/g, '-')
        .replace(/^-/, '')
}

function handleSubmit() {
    emit('update', form.value)
    emit('next', form.value)
}

// Sync form data when props change
watch(() => props.formData, (newData) => {
    if (newData) {
        Object.assign(form.value, newData)
    }
}, { deep: true })
</script>
