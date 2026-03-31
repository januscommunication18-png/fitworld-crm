<template>
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-xl">Studio Location</h2>
            <p class="text-base-content/70 text-sm mb-6">
                Add your studio's primary location where clients will visit.
            </p>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <!-- Location Name -->
                <div>
                    <label for="location_name" class="label label-text">Location Name</label>
                    <input
                        type="text"
                        id="location_name"
                        v-model="form.name"
                        class="input input-bordered w-full"
                        placeholder="Main Studio (optional)"
                    >
                </div>

                <!-- Address -->
                <div>
                    <label for="address_line_1" class="label label-text">Street Address *</label>
                    <input
                        type="text"
                        id="address_line_1"
                        v-model="form.address_line_1"
                        class="input input-bordered w-full"
                        :class="{ 'input-error': errors.address_line_1 }"
                        placeholder="123 Main St"
                        required
                    >
                    <span v-if="errors.address_line_1" class="text-error text-xs mt-1">{{ errors.address_line_1 }}</span>
                </div>

                <div>
                    <label for="address_line_2" class="label label-text">Suite / Unit (optional)</label>
                    <input
                        type="text"
                        id="address_line_2"
                        v-model="form.address_line_2"
                        class="input input-bordered w-full"
                        placeholder="Suite 100"
                    >
                </div>

                <!-- City, State, Zip -->
                <div class="grid grid-cols-6 gap-3">
                    <div class="col-span-3">
                        <label for="city" class="label label-text">City *</label>
                        <input
                            type="text"
                            id="city"
                            v-model="form.city"
                            class="input input-bordered w-full"
                            :class="{ 'input-error': errors.city }"
                            placeholder="New York"
                            required
                        >
                        <span v-if="errors.city" class="text-error text-xs mt-1">{{ errors.city }}</span>
                    </div>
                    <div class="col-span-2">
                        <label for="state" class="label label-text">State *</label>
                        <input
                            type="text"
                            id="state"
                            v-model="form.state"
                            class="input input-bordered w-full"
                            :class="{ 'input-error': errors.state }"
                            placeholder="NY"
                            required
                        >
                        <span v-if="errors.state" class="text-error text-xs mt-1">{{ errors.state }}</span>
                    </div>
                    <div class="col-span-1">
                        <label for="postal_code" class="label label-text">Zip *</label>
                        <input
                            type="text"
                            id="postal_code"
                            v-model="form.postal_code"
                            class="input input-bordered w-full"
                            :class="{ 'input-error': errors.postal_code }"
                            placeholder="10001"
                            required
                        >
                        <span v-if="errors.postal_code" class="text-error text-xs mt-1">{{ errors.postal_code }}</span>
                    </div>
                </div>

                <!-- Country -->
                <div>
                    <label for="country" class="label label-text">Country *</label>
                    <select
                        id="country"
                        v-model="form.country"
                        class="select select-bordered w-full"
                        :class="{ 'select-error': errors.country }"
                        required
                    >
                        <option value="">Select country</option>
                        <option value="United States">United States</option>
                        <option value="Canada">Canada</option>
                        <option value="United Kingdom">United Kingdom</option>
                        <option value="Australia">Australia</option>
                        <option value="Germany">Germany</option>
                        <option value="France">France</option>
                        <option value="Spain">Spain</option>
                        <option value="Italy">Italy</option>
                        <option value="Japan">Japan</option>
                        <option value="India">India</option>
                        <option value="Brazil">Brazil</option>
                        <option value="Mexico">Mexico</option>
                        <option value="Other">Other</option>
                    </select>
                    <span v-if="errors.country" class="text-error text-xs mt-1">{{ errors.country }}</span>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="label label-text">Location Phone (optional)</label>
                    <input
                        type="tel"
                        id="phone"
                        v-model="form.phone"
                        class="input input-bordered w-full"
                        placeholder="(555) 123-4567"
                    >
                </div>

                <!-- Error message -->
                <div v-if="errorMessage" class="alert alert-error">
                    <span class="alert-icon icon-[tabler--alert-circle]"></span>
                    <p>{{ errorMessage }}</p>
                </div>

                <!-- Navigation -->
                <div class="flex justify-between mt-6">
                    <button type="button" class="btn btn-ghost" @click="$emit('prev')">
                        <span class="icon-[tabler--arrow-left] size-4"></span>
                        Back
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="!canContinue || saving"
                    >
                        <span v-if="saving" class="loading loading-spinner loading-sm"></span>
                        <span v-else>
                            Continue
                            <span class="icon-[tabler--arrow-right] size-4"></span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '../../utils/api.js'
import toast from '../../utils/toast.js'

const props = defineProps({
    formData: { type: Object, required: true },
    options: { type: Object, default: () => ({}) },
    csrfToken: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['next', 'prev', 'update'])

const form = reactive({
    name: '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: '',
    postal_code: '',
    country: 'United States',
    phone: '',
})

const errors = reactive({})
const errorMessage = ref('')
const saving = ref(false)

// Initialize from existing location data
onMounted(() => {
    if (props.formData.location) {
        Object.assign(form, props.formData.location)
    }
})

const canContinue = computed(() => {
    return form.address_line_1 && form.city && form.state && form.postal_code && form.country
})

async function handleSubmit() {
    saving.value = true
    errorMessage.value = ''
    Object.keys(errors).forEach(k => delete errors[k])

    try {
        const response = await api.post('/api/v1/onboarding/location', form)
        toast.success('Location saved!')
        emit('update', { location: response.data.data?.location })
        emit('next')
    } catch (error) {
        if (error.response?.status === 422) {
            const validationErrors = error.response.data.errors || {}
            Object.assign(errors, validationErrors)
        }
        errorMessage.value = error.response?.data?.meta?.message || 'Failed to save location.'
    } finally {
        saving.value = false
    }
}
</script>
