<template>
    <div class="w-full max-w-[500px] mx-auto">
        <!-- Progress bar (steps 2-8) -->
        <ProgressBar v-if="currentStep >= 2 && currentStep <= 8" :current-step="currentStep" :total-steps="8" />

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
                    @next="nextStep"
                    @prev="prevStep"
                    @update="updateFormData"
                    @resend-email="resendVerificationEmail"
                    @complete="completeOnboarding"
                />
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import api, { setAuthToken, ensureCsrf } from '../../utils/api.js'
import toast from '../../utils/toast.js'
import ProgressBar from './ProgressBar.vue'
import Step1Welcome from './Step1Welcome.vue'
import Step2Account from './Step2Account.vue'
import Step3EmailVerification from './Step3EmailVerification.vue'
import Step4StudioBasics from './Step4StudioBasics.vue'
import Step5LocationSpace from './Step5LocationSpace.vue'
import Step6InstructorSetup from './Step6InstructorSetup.vue'
import Step7ClassSetup from './Step7ClassSetup.vue'
import Step8Payments from './Step8Payments.vue'
import Step9GoLive from './Step9GoLive.vue'

const props = defineProps({
    csrfToken: { type: String, default: '' },
    smartyKey: { type: String, default: '' },
})

const currentStep = ref(1)
const loading = ref(false)
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
    city: '',
    timezone: '',
    subdomain: '',
    // Step 5: Location
    address: '',
    state: '',
    zipcode: '',
    rooms: 1,
    default_capacity: 20,
    room_capacities: [],
    amenities: [],
    // Step 6: Instructors
    add_self_as_instructor: true,
    instructors: [],
    // Step 7: Class
    skip_class_setup: false,
    class_name: '',
    class_type: '',
    class_duration: 60,
    class_capacity: 20,
    class_instructor_id: null,
    class_price: null,
    // Step 8: Payments
    skip_payments: false,
    stripe_connected: false,
})

const steps = {
    1: Step1Welcome,
    2: Step2Account,
    3: Step3EmailVerification,
    4: Step4StudioBasics,
    5: Step5LocationSpace,
    6: Step6InstructorSetup,
    7: Step7ClassSetup,
    8: Step8Payments,
    9: Step9GoLive,
}

const stepComponent = computed(() => steps[currentStep.value])

/**
 * Save the current step's data to the API, then advance.
 */
async function nextStep() {
    if (currentStep.value >= 9) return

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
                city: fd.city,
                timezone: fd.timezone,
                subdomain: fd.subdomain,
            })
            break
        case 5:
            await api.post('/signup/location', {
                address: fd.address,
                rooms: fd.rooms,
                default_capacity: fd.default_capacity,
                amenities: fd.amenities,
            })
            break
        case 6:
            await api.post('/signup/instructors', {
                add_self_as_instructor: fd.add_self_as_instructor,
                instructors: fd.instructors,
            })
            break
        case 7:
            await api.post('/signup/classes', {
                skip_class_setup: fd.skip_class_setup,
                class_name: fd.class_name,
                class_type: fd.class_type,
                class_duration: fd.class_duration,
                class_capacity: fd.class_capacity,
                class_price: fd.class_price,
            })
            break
        case 8:
            await api.post('/signup/payments', {
                skip_payments: fd.skip_payments,
                stripe_connected: fd.stripe_connected,
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
