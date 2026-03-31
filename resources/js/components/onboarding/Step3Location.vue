<template>
    <div class="card">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-2">Location Information</h2>
            <p class="text-base-content/70 mb-6">Add your primary studio location.</p>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <!-- Location Name -->
                <div class="form-control">
                    <label class="label" for="location_name">
                        <span class="label-text">Location Name <span class="text-error">*</span></span>
                    </label>
                    <input
                        id="location_name"
                        v-model="form.name"
                        type="text"
                        class="input input-bordered"
                        :class="{ 'input-error': errors.name }"
                        placeholder="e.g., Main Studio, Downtown Location"
                        required
                    >
                    <label v-if="errors.name" class="label">
                        <span class="label-text-alt text-error">{{ errors.name[0] }}</span>
                    </label>
                </div>

                <!-- Location Type -->
                <div class="form-control">
                    <label class="label" for="location_type">
                        <span class="label-text">Location Type <span class="text-error">*</span></span>
                    </label>
                    <select
                        id="location_type"
                        v-model="form.location_type"
                        class="select select-bordered"
                        :class="{ 'select-error': errors.location_type }"
                    >
                        <option value="in_person">In-Person (Physical Studio)</option>
                        <option value="virtual">Virtual (Online)</option>
                        <option value="public">Public Outdoor</option>
                        <option value="mobile">Mobile (Travel)</option>
                    </select>
                </div>

                <!-- Address Fields (for physical locations) -->
                <template v-if="form.location_type === 'in_person'">
                    <div class="form-control">
                        <label class="label" for="address_line_1">
                            <span class="label-text">Street Address <span class="text-error">*</span></span>
                        </label>
                        <input
                            id="address_line_1"
                            v-model="form.address_line_1"
                            type="text"
                            class="input input-bordered"
                            :class="{ 'input-error': errors.address_line_1 }"
                            placeholder="123 Main Street"
                            required
                        >
                    </div>

                    <div class="form-control">
                        <label class="label" for="address_line_2">
                            <span class="label-text">Suite/Unit (optional)</span>
                        </label>
                        <input
                            id="address_line_2"
                            v-model="form.address_line_2"
                            type="text"
                            class="input input-bordered"
                            placeholder="Suite 100"
                        >
                    </div>
                </template>

                <!-- City, State, Postal Code -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label" for="city">
                            <span class="label-text">City <span class="text-error">*</span></span>
                        </label>
                        <input
                            id="city"
                            v-model="form.city"
                            type="text"
                            class="input input-bordered"
                            :class="{ 'input-error': errors.city }"
                            placeholder="City"
                            required
                        >
                    </div>

                    <div class="form-control">
                        <label class="label" for="state">
                            <span class="label-text">State/Province</span>
                        </label>
                        <input
                            id="state"
                            v-model="form.state"
                            type="text"
                            class="input input-bordered"
                            placeholder="State"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label" for="postal_code">
                            <span class="label-text">Postal Code</span>
                        </label>
                        <input
                            id="postal_code"
                            v-model="form.postal_code"
                            type="text"
                            class="input input-bordered"
                            placeholder="12345"
                        >
                    </div>

                    <div class="form-control">
                        <label class="label" for="country">
                            <span class="label-text">Country <span class="text-error">*</span></span>
                        </label>
                        <select
                            id="country"
                            v-model="form.country"
                            class="select select-bordered"
                            :class="{ 'select-error': errors.country }"
                            required
                        >
                            <option value="">Select Country</option>
                            <option value="United States">United States</option>
                            <option value="Canada">Canada</option>
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="Australia">Australia</option>
                            <option value="Germany">Germany</option>
                            <option value="France">France</option>
                            <option value="India">India</option>
                            <option value="Mexico">Mexico</option>
                            <option value="Brazil">Brazil</option>
                        </select>
                    </div>
                </div>

                <!-- Virtual Platform (for virtual locations) -->
                <template v-if="form.location_type === 'virtual'">
                    <div class="form-control">
                        <label class="label" for="virtual_platform">
                            <span class="label-text">Virtual Platform <span class="text-error">*</span></span>
                        </label>
                        <select
                            id="virtual_platform"
                            v-model="form.virtual_platform"
                            class="select select-bordered"
                            :class="{ 'select-error': errors.virtual_platform }"
                            required
                        >
                            <option value="">Select Platform</option>
                            <option value="zoom">Zoom</option>
                            <option value="google_meet">Google Meet</option>
                            <option value="teams">Microsoft Teams</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label" for="virtual_meeting_link">
                            <span class="label-text">Default Meeting Link (optional)</span>
                        </label>
                        <input
                            id="virtual_meeting_link"
                            v-model="form.virtual_meeting_link"
                            type="url"
                            class="input input-bordered"
                            placeholder="https://zoom.us/j/..."
                        >
                    </div>
                </template>

                <!-- Contact Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label" for="phone">
                            <span class="label-text">Phone (optional)</span>
                        </label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            class="input input-bordered"
                            placeholder="(555) 123-4567"
                        >
                    </div>

                    <div class="form-control">
                        <label class="label" for="email">
                            <span class="label-text">Email (optional)</span>
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="input input-bordered"
                            placeholder="studio@example.com"
                        >
                    </div>
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

const existingLocation = props.formData.location || {}

const form = ref({
    name: existingLocation.name || '',
    location_type: existingLocation.location_type || 'in_person',
    address_line_1: existingLocation.address_line_1 || '',
    address_line_2: existingLocation.address_line_2 || '',
    city: existingLocation.city || '',
    state: existingLocation.state || '',
    postal_code: existingLocation.postal_code || '',
    country: existingLocation.country || '',
    phone: existingLocation.phone || '',
    email: existingLocation.email || '',
    virtual_platform: existingLocation.virtual_platform || '',
    virtual_meeting_link: existingLocation.virtual_meeting_link || '',
})

const isValid = computed(() => {
    if (!form.value.name || !form.value.city || !form.value.country) {
        return false
    }
    if (form.value.location_type === 'in_person' && !form.value.address_line_1) {
        return false
    }
    if (form.value.location_type === 'virtual' && !form.value.virtual_platform) {
        return false
    }
    return true
})

function handleSubmit() {
    emit('update', { location: form.value })
    emit('next', { location: form.value })
}

// Sync form data when props change
watch(() => props.formData.location, (newLocation) => {
    if (newLocation) {
        Object.assign(form.value, newLocation)
    }
}, { deep: true })
</script>
