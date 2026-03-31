<template>
    <div class="w-full">
        <!-- Loading state while fetching progress -->
        <div v-if="initialLoading" class="animate-pulse">
            <!-- Progress bar skeleton -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-2">
                    <div v-for="i in 5" :key="i" class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-base-300"></div>
                        <div v-if="i < 5" class="w-12 sm:w-20 h-1 bg-base-300 mx-2"></div>
                    </div>
                </div>
            </div>
            <!-- Card skeleton -->
            <div class="card bg-base-100">
                <div class="card-body">
                    <div class="h-6 bg-base-300 rounded w-1/2 mb-2"></div>
                    <div class="h-4 bg-base-300 rounded w-3/4 mb-6"></div>
                    <div class="space-y-4">
                        <div class="h-12 bg-base-300 rounded"></div>
                        <div class="h-12 bg-base-300 rounded"></div>
                        <div class="h-12 bg-base-300 rounded"></div>
                    </div>
                    <div class="flex justify-end mt-6">
                        <div class="h-10 bg-base-300 rounded w-32"></div>
                    </div>
                </div>
            </div>
        </div>

        <template v-else>
            <!-- Progress bar -->
            <OnboardingProgressBar :current-step="currentStep" :steps="stepLabels" />

            <!-- Step components -->
            <transition name="fade" mode="out-in">
                <div :key="currentStep" class="w-full">
                    <component
                        :is="stepComponent"
                        :form-data="formData"
                        :options="options"
                        :csrf-token="csrfToken"
                        :smarty-key="smartyKey"
                        :loading="loading"
                        :errors="errors"
                        @next="nextStep"
                        @prev="prevStep"
                        @update="updateFormData"
                        @complete="completeOnboarding"
                    />
                </div>
            </transition>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api, { ensureCsrf } from '../../utils/api.js'
import toast from '../../utils/toast.js'
import OnboardingProgressBar from './OnboardingProgressBar.vue'
import Step1Verification from './Step1Verification.vue'
import Step2StudioInfo from './Step2StudioInfo.vue'
import Step3Location from './Step3Location.vue'
import Step4StaffMembers from './Step4StaffMembers.vue'
import Step5BookingPage from './Step5BookingPage.vue'

const props = defineProps({
    csrfToken: { type: String, default: '' },
    smartyKey: { type: String, default: '' },
    userId: { type: Number, default: 0 },
    hostId: { type: Number, default: 0 },
    emailVerified: { type: Boolean, default: false },
    phoneVerified: { type: Boolean, default: false },
})

const stepLabels = [
    'Verification',
    'Studio Info',
    'Location',
    'Staff',
    'Booking Page'
]

const currentStep = ref(1)
const loading = ref(false)
const initialLoading = ref(true)
const errors = ref({})
const options = ref({
    studio_structures: {},
    studio_category_groups: {},
    country_codes: {},
    currencies: {},
    languages: {},
})

const formData = ref({
    // Step 1: Verification
    email: '',
    email_verified: false,
    phone: '',
    phone_country_code: '+1',
    phone_verified: false,

    // Step 2: Studio Info
    studio_name: '',
    studio_structure: '',
    subdomain: '',
    studio_types: [],
    studio_categories: [],
    language: 'en',
    default_currency: 'USD',
    cancellation_policy: '',

    // Step 3: Location
    location: null,

    // Step 4: Staff
    staff_members: [],

    // Step 5: Booking Page
    is_live: false,
    logo_url: null,
})

// Map steps to components
const stepComponents = {
    1: Step1Verification,
    2: Step2StudioInfo,
    3: Step3Location,
    4: Step4StaffMembers,
    5: Step5BookingPage,
}

const stepComponent = computed(() => stepComponents[currentStep.value] || Step1Verification)

// Fetch current progress on mount
onMounted(async () => {
    await ensureCsrf()
    await fetchProgress()
})

async function fetchProgress() {
    initialLoading.value = true
    try {
        const response = await api.get('/api/v1/onboarding/progress')
        const data = response.data.data

        // Update form data from server
        Object.assign(formData.value, data.form_data)
        formData.value.email_verified = data.email_verified
        formData.value.phone_verified = data.phone_verified

        // Store options
        options.value = data.options || options.value

        // Set current step
        currentStep.value = data.step || 1

        // If support was requested, redirect
        if (data.support_requested) {
            window.location.href = '/support-waiting'
        }
    } catch (error) {
        console.error('Failed to fetch progress:', error)
        toast.error('Failed to load progress. Please refresh the page.')
    } finally {
        initialLoading.value = false
    }
}

function updateFormData(updates) {
    Object.assign(formData.value, updates)
}

async function nextStep(stepData = {}) {
    // Update form data if provided
    if (Object.keys(stepData).length > 0) {
        updateFormData(stepData)
    }

    // Move to next step
    if (currentStep.value < 5) {
        currentStep.value++
    }
}

function prevStep() {
    if (currentStep.value > 1) {
        currentStep.value--
    }
}

async function completeOnboarding() {
    loading.value = true
    try {
        const response = await api.post('/api/v1/onboarding/complete')
        toast.success(response.data.meta?.message || 'Onboarding complete!')

        // Redirect to plans page
        const redirectUrl = response.data.data?.redirect_url || '/plans'
        window.location.href = redirectUrl
    } catch (error) {
        console.error('Failed to complete onboarding:', error)
        const message = error.response?.data?.meta?.message || 'Failed to complete onboarding.'
        toast.error(message)
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
