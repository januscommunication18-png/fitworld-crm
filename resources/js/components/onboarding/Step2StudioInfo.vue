<template>
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-xl">Studio Information</h2>
            <p class="text-base-content/70 text-sm mb-6">
                Tell us about your studio to personalize your experience.
            </p>

            <form @submit.prevent="handleSubmit" class="space-y-5">
                <!-- Studio Name -->
                <div>
                    <label for="studio_name" class="label label-text">Studio Name *</label>
                    <input
                        type="text"
                        id="studio_name"
                        v-model="form.studio_name"
                        class="input input-bordered w-full"
                        :class="{ 'input-error': errors.studio_name }"
                        placeholder="My Awesome Studio"
                        required
                    >
                    <span v-if="errors.studio_name" class="text-error text-xs mt-1">{{ errors.studio_name }}</span>
                </div>

                <!-- Studio Structure -->
                <div>
                    <label class="label label-text">Studio Structure *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label
                            v-for="(label, value) in options.studio_structures"
                            :key="value"
                            class="card card-bordered cursor-pointer transition-all hover:border-primary"
                            :class="{ 'border-primary bg-primary/5': form.studio_structure === value }"
                        >
                            <div class="card-body p-4">
                                <div class="flex items-center gap-3">
                                    <input
                                        type="radio"
                                        v-model="form.studio_structure"
                                        :value="value"
                                        class="radio radio-primary"
                                    >
                                    <div>
                                        <span class="font-medium">{{ label }}</span>
                                        <p class="text-xs text-base-content/60">
                                            {{ value === 'solo' ? 'Just you running the studio' : 'You have instructors or staff' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Subdomain -->
                <div>
                    <label for="subdomain" class="label label-text">Your Booking URL *</label>
                    <div class="join w-full">
                        <input
                            type="text"
                            id="subdomain"
                            v-model="form.subdomain"
                            class="input input-bordered join-item flex-1"
                            :class="{ 'input-error': errors.subdomain || (subdomainChecked && !subdomainAvailable) }"
                            placeholder="my-studio"
                            @input="onSubdomainInput"
                            required
                        >
                        <span class="btn join-item pointer-events-none bg-base-200">
                            .{{ appDomain }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span v-if="checkingSubdomain" class="loading loading-spinner loading-xs text-base-content/50"></span>
                        <span v-else-if="subdomainChecked && subdomainAvailable" class="text-success text-xs flex items-center gap-1">
                            <span class="icon-[tabler--check] size-3"></span> Available
                        </span>
                        <span v-else-if="subdomainChecked && !subdomainAvailable" class="text-error text-xs flex items-center gap-1">
                            <span class="icon-[tabler--x] size-3"></span> Not available
                        </span>
                        <span v-if="errors.subdomain" class="text-error text-xs">{{ errors.subdomain }}</span>
                    </div>
                </div>

                <!-- Studio Types -->
                <div>
                    <label class="label label-text">Studio Type(s)</label>
                    <div class="flex flex-wrap gap-2">
                        <label
                            v-for="type in studioTypes"
                            :key="type"
                            class="badge badge-lg cursor-pointer transition-all"
                            :class="form.studio_types.includes(type) ? 'badge-primary' : 'badge-ghost'"
                        >
                            <input
                                type="checkbox"
                                :value="type"
                                v-model="form.studio_types"
                                class="hidden"
                            >
                            {{ type }}
                        </label>
                    </div>
                </div>

                <!-- Studio Categories -->
                <div>
                    <label class="label label-text">Categories (select all that apply)</label>
                    <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                        <div v-for="(categories, groupName) in options.studio_category_groups" :key="groupName" class="collapse collapse-arrow bg-base-200 rounded-lg">
                            <input type="checkbox" class="peer" />
                            <div class="collapse-title font-medium text-sm py-2 min-h-0">
                                {{ groupName }}
                                <span v-if="countSelectedInGroup(categories)" class="badge badge-primary badge-sm ml-2">
                                    {{ countSelectedInGroup(categories) }}
                                </span>
                            </div>
                            <div class="collapse-content">
                                <div class="flex flex-wrap gap-2 pt-2">
                                    <label
                                        v-for="category in categories"
                                        :key="category"
                                        class="badge cursor-pointer transition-all"
                                        :class="form.studio_categories.includes(category) ? 'badge-primary' : 'badge-ghost'"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="category"
                                            v-model="form.studio_categories"
                                            class="hidden"
                                        >
                                        {{ category }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Language & Currency -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="language" class="label label-text">Language</label>
                        <select id="language" v-model="form.language" class="select select-bordered w-full">
                            <option v-for="(label, code) in options.languages" :key="code" :value="code">
                                {{ label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="currency" class="label label-text">Currency</label>
                        <select id="currency" v-model="form.default_currency" class="select select-bordered w-full">
                            <option v-for="(label, code) in options.currencies" :key="code" :value="code">
                                {{ code }} - {{ label }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Cancellation Policy -->
                <div>
                    <label for="cancellation_policy" class="label label-text">Cancellation Policy</label>
                    <textarea
                        id="cancellation_policy"
                        v-model="form.cancellation_policy"
                        class="textarea textarea-bordered w-full"
                        rows="2"
                        placeholder="e.g., 24-hour cancellation notice required for a full refund..."
                    ></textarea>
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
import { ref, reactive, computed, watch } from 'vue'
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

const appDomain = window.location.hostname.replace('www.', '') || 'fitcrm.com'

const studioTypes = [
    'Yoga', 'Pilates', 'Dance', 'Martial Arts', 'CrossFit',
    'Personal Training', 'Group Fitness', 'Spinning', 'Other'
]

const form = reactive({
    studio_name: props.formData.studio_name || '',
    studio_structure: props.formData.studio_structure || '',
    subdomain: props.formData.subdomain || '',
    studio_types: props.formData.studio_types || [],
    studio_categories: props.formData.studio_categories || [],
    language: props.formData.language || 'en',
    default_currency: props.formData.default_currency || 'USD',
    cancellation_policy: props.formData.cancellation_policy || '',
})

const errors = reactive({})
const errorMessage = ref('')
const saving = ref(false)
const checkingSubdomain = ref(false)
const subdomainChecked = ref(false)
const subdomainAvailable = ref(false)
let subdomainCheckTimeout = null

const canContinue = computed(() => {
    return form.studio_name &&
           form.studio_structure &&
           form.subdomain &&
           (!subdomainChecked.value || subdomainAvailable.value)
})

function countSelectedInGroup(categories) {
    return categories.filter(c => form.studio_categories.includes(c)).length
}

function onSubdomainInput() {
    // Clean the subdomain
    form.subdomain = form.subdomain.toLowerCase().replace(/[^a-z0-9-]/g, '')

    subdomainChecked.value = false
    subdomainAvailable.value = false

    // Debounce check
    if (subdomainCheckTimeout) clearTimeout(subdomainCheckTimeout)
    subdomainCheckTimeout = setTimeout(() => {
        if (form.subdomain.length >= 3) {
            checkSubdomain()
        }
    }, 500)
}

async function checkSubdomain() {
    checkingSubdomain.value = true
    try {
        const response = await api.post('/api/v1/onboarding/subdomain/check', {
            subdomain: form.subdomain,
        })
        subdomainAvailable.value = response.data.data?.available || false
        subdomainChecked.value = true
    } catch (error) {
        console.error('Subdomain check failed:', error)
    } finally {
        checkingSubdomain.value = false
    }
}

async function handleSubmit() {
    saving.value = true
    errorMessage.value = ''
    Object.keys(errors).forEach(k => delete errors[k])

    try {
        await api.post('/api/v1/onboarding/studio', form)
        toast.success('Studio information saved!')
        emit('update', { ...form })
        emit('next')
    } catch (error) {
        if (error.response?.status === 422) {
            const validationErrors = error.response.data.errors || {}
            Object.assign(errors, validationErrors)
        }
        errorMessage.value = error.response?.data?.meta?.message || 'Failed to save studio information.'
    } finally {
        saving.value = false
    }
}
</script>
