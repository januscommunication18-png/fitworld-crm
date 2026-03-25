<template>
    <div class="w-full max-w-[500px] mx-auto">
        <!-- Loading state while fetching progress -->
        <div v-if="initialLoading" class="card w-[500px]">
            <div class="card-body animate-pulse">
                <div class="h-8 bg-base-300 rounded w-3/4 mb-6"></div>
                <div class="h-4 bg-base-300 rounded w-1/2 mb-8"></div>
                <div class="space-y-4">
                    <div class="h-10 bg-base-300 rounded"></div>
                    <div class="h-10 bg-base-300 rounded"></div>
                    <div class="h-10 bg-base-300 rounded"></div>
                </div>
                <div class="h-12 bg-base-300 rounded mt-8 w-1/3 ml-auto"></div>
            </div>
        </div>

        <template v-else>
            <!-- Progress bar (steps 2-5) -->
            <ProgressBar v-if="currentStep >= 2 && currentStep <= 5" :current-step="currentStep" :total-steps="5" />

            <!-- Step components -->
            <transition name="fade" mode="out-in">
                <div :key="currentStep" class="w-full">
                    <component
                    :is="stepComponent"
                    :form-data="formData"
                    :csrf-token="csrfToken"
                    :smarty-key="smartyKey"
                    :loading="loading"
                    :errors="errors"
                    :email-verified="isEmailVerified"
                    @next="nextStep"
                    @prev="prevStep"
                    @update="updateFormData"
                    @resend-email="resendVerificationEmail"
                    @complete="completeOnboarding"
                    />
                </div>
            </transition>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api, { setAuthToken, ensureCsrf } from '../../utils/api.js'
import toast from '../../utils/toast.js'
import ProgressBar from './ProgressBar.vue'
import Step1Welcome from './Step1Welcome.vue'
import Step2Account from './Step2Account.vue'
import Step3EmailVerification from './Step3EmailVerification.vue'
import Step4StudioBasics from './Step4StudioBasics.vue'
import Step5LocationSpace from './Step5LocationSpace.vue'
import Step6GoLive from './Step9GoLive.vue'

const props = defineProps({
    csrfToken: { type: String, default: '' },
    smartyKey: { type: String, default: '' },
    authenticated: { type: Boolean, default: false },
    emailVerified: { type: Boolean, default: false },
})

const isEmailVerified = ref(false)

const currentStep = ref(1)
const loading = ref(false)
const initialLoading = ref(false)
const errors = ref({})

const formData = ref({
    // Step 2: Account
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    is_studio_owner: true,
    // Step 4: Studio
    studio_name: '',
    studio_types: [],
    custom_studio_type: '',
    country: '',
    city: '',
    state: '',
    timezone: '',
    subdomain: '',
    default_currency: 'USD',
    // Step 5: Location
    address: '',
    state: '',
    zipcode: '',
    rooms: 1,
    default_capacity: 20,
    room_capacities: [],
    amenities: [],
})

const steps = {
    1: Step1Welcome,
    2: Step2Account,
    3: Step3EmailVerification,
    4: Step4StudioBasics,
    5: Step5LocationSpace,
    6: Step6GoLive,
}

const stepComponent = computed(() => steps[currentStep.value])

/**
 * Load saved progress on mount if user is authenticated.
 */
onMounted(async () => {
    // Handle email verification redirect
    if (props.emailVerified && props.authenticated) {
        isEmailVerified.value = true
        currentStep.value = 3
        toast.success('Email verified successfully!')
        // Clean up the URL query param
        window.history.replaceState({}, '', '/signup')

        // Load the rest of the progress
        initialLoading.value = true
        try {
            await ensureCsrf()
            const res = await api.get('/signup/progress')
            const { form_data } = res.data.data
            Object.assign(formData.value, form_data)
        } catch (err) {
            // Ignore errors, just show step 3
        } finally {
            initialLoading.value = false
        }
        return
    }

    if (!props.authenticated) return

    initialLoading.value = true
    try {
        await ensureCsrf()
        const res = await api.get('/signup/progress')
        const { step, form_data } = res.data.data

        // Restore form data
        Object.assign(formData.value, form_data)

        // Restore step (minimum step 4 for authenticated users)
        currentStep.value = Math.max(step, 4)
    } catch (err) {
        // If 401, user is not authenticated via API (session only)
        // Start at step 4 for logged-in users
        if (err.response?.status !== 401) {
            console.error('Failed to load progress:', err)
        }
        currentStep.value = 4
    } finally {
        initialLoading.value = false
    }
})

/**
 * Save the current step's data to the API, then advance.
 */
async function nextStep() {
    if (currentStep.value >= 6) return

    // Step 1 has no API call — just advance
    if (currentStep.value === 1) {
        currentStep.value++
        return
    }

    loading.value = true
    errors.value = {}

    try {
        await ensureCsrf()
        await saveCurrentStep()
        currentStep.value++
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors || {}
            toast.error('Please fix the errors below.')
        } else {
            toast.error(err.response?.data?.meta?.message || 'Something went wrong. Please try again.')
        }
    } finally {
        loading.value = false
    }
}

/**
 * Route each step to its API endpoint.
 */
async function saveCurrentStep() {
    const fd = formData.value

    switch (currentStep.value) {
        case 2: {
            const res = await api.post('/signup/register', {
                first_name: fd.first_name,
                last_name: fd.last_name,
                email: fd.email,
                password: fd.password,
                is_studio_owner: fd.is_studio_owner,
            })
            // Set auth token for subsequent requests
            setAuthToken(res.data.data.token)
            toast.success('Account created!')
            break
        }
        case 3:
            // Email verification step — no required save, just advance
            break
        case 4:
            await api.post('/signup/studio', {
                studio_name: fd.studio_name,
                studio_types: fd.studio_types,
                custom_studio_type: fd.custom_studio_type,
                country: fd.country,
                city: fd.city,
                state: fd.state,
                timezone: fd.timezone,
                subdomain: fd.subdomain,
                default_currency: fd.default_currency,
            })
            break
        case 5:
            await api.post('/signup/location', {
                address: fd.address,
                city: fd.city,
                state: fd.state,
                zipcode: fd.zipcode,
                rooms: fd.rooms,
                default_capacity: fd.default_capacity,
                amenities: fd.amenities,
            })
            break
    }
}

function prevStep() {
    if (currentStep.value > 1) {
        errors.value = {}
        currentStep.value--
    }
}

function updateFormData(data) {
    Object.assign(formData.value, data)
}

async function resendVerificationEmail() {
    try {
        await ensureCsrf()
        await api.post('/signup/verify-email')
        toast.success('Verification email resent!')
    } catch {
        toast.error('Could not resend email. Please try again.')
    }
}

async function completeOnboarding() {
    loading.value = true
    try {
        await ensureCsrf()
        await api.post('/signup/complete')
        toast.success('Your studio is live!')
        window.location.href = '/dashboard'
    } catch {
        toast.error('Something went wrong. Please try again.')
        // Still redirect — data is saved
        window.location.href = '/dashboard'
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
