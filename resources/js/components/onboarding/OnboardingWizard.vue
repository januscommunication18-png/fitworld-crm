<template>
    <div class="w-full max-w-2xl mx-auto">
        <!-- Tech Support Pending State -->
        <TechSupportPending
            v-if="techSupportPending"
            :ticket-id="techSupportTicketId"
            :requested-at="techSupportRequestedAt"
        />

        <!-- Normal Onboarding Flow -->
        <template v-else>
            <!-- Progress Bar -->
            <OnboardingProgressBar
                :current-step="currentStepNumber"
                :steps="steps"
            />

            <!-- Loading State -->
            <div v-if="initialLoading" class="card mt-6">
                <div class="card-body animate-pulse">
                    <div class="h-8 bg-base-300 rounded w-3/4 mb-4"></div>
                    <div class="h-4 bg-base-300 rounded w-1/2 mb-8"></div>
                    <div class="space-y-4">
                        <div class="h-12 bg-base-300 rounded"></div>
                        <div class="h-12 bg-base-300 rounded"></div>
                    </div>
                </div>
            </div>

            <!-- Step Components -->
            <transition name="fade" mode="out-in" v-else>
                <div :key="currentStepNumber" class="mt-6">
                    <component
                        :is="stepComponent"
                        :form-data="formData"
                        :loading="loading"
                        :errors="errors"
                        :email-verified="isEmailVerified"
                        :phone-verified="isPhoneVerified"
                        :user-email="userEmail"
                        @next="nextStep"
                        @prev="prevStep"
                        @update="updateFormData"
                        @email-resend="resendEmailVerification"
                        @phone-send="sendPhoneCode"
                        @phone-verify="verifyPhoneCode"
                        @complete="completeOnboarding"
                    />
                </div>
            </transition>

            <!-- Tech Support Button -->
            <div class="mt-8 text-center">
                <button
                    type="button"
                    class="btn btn-ghost btn-sm text-base-content/60"
                    @click="showTechSupportModal = true"
                >
                    <span class="icon-[tabler--headset] size-4 mr-1"></span>
                    Request Technical Support
                </button>
            </div>
        </template>

        <!-- Tech Support Modal -->
        <TechSupportModal
            v-if="showTechSupportModal"
            :user-name="userName"
            :user-email="userEmail"
            :loading="techSupportLoading"
            @close="showTechSupportModal = false"
            @submit="submitTechSupport"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api, { ensureCsrf } from '../../utils/api.js'
import toast from '../../utils/toast.js'
import OnboardingProgressBar from './OnboardingProgressBar.vue'
import Step1VerifyEmailPhone from './Step1VerifyEmailPhone.vue'
import Step2StudioInfo from './Step2StudioInfo.vue'
import Step3Location from './Step3Location.vue'
import Step4StaffMember from './Step4StaffMember.vue'
import Step5BookingPage from './Step5BookingPage.vue'
import TechSupportModal from './TechSupportModal.vue'
import TechSupportPending from './TechSupportPending.vue'

const props = defineProps({
    csrfToken: { type: String, default: '' },
    emailVerified: { type: Boolean, default: false },
    phoneVerified: { type: Boolean, default: false },
    techSupportRequested: { type: Boolean, default: false },
    techSupportPending: { type: Boolean, default: false },
    currentStep: { type: Number, default: 1 },
    studioName: { type: String, default: '' },
    userEmail: { type: String, default: '' },
    userName: { type: String, default: '' },
})

const steps = [
    { number: 1, label: 'Verify', description: 'Email' },
    { number: 2, label: 'Studio', description: 'Information' },
    { number: 3, label: 'Location', description: 'Details' },
    { number: 4, label: 'Team', description: 'Members' },
    { number: 5, label: 'Booking', description: 'Page' },
]

const stepComponents = {
    1: Step1VerifyEmailPhone,
    2: Step2StudioInfo,
    3: Step3Location,
    4: Step4StaffMember,
    5: Step5BookingPage,
}

const currentStepNumber = ref(props.currentStep)
const loading = ref(false)
const initialLoading = ref(true)
const errors = ref({})
const isEmailVerified = ref(props.emailVerified)
const isPhoneVerified = ref(props.phoneVerified)
const techSupportPending = ref(props.techSupportPending)
const techSupportTicketId = ref(null)
const techSupportRequestedAt = ref(null)
const showTechSupportModal = ref(false)
const techSupportLoading = ref(false)

const formData = ref({
    // Step 2: Studio Info
    studio_name: props.studioName || '',
    studio_structure: 'solo',
    subdomain: '',
    studio_types: [],
    studio_categories: [],
    default_language_app: 'en',
    default_currency: 'USD',
    cancellation_window_hours: 12,
    // Step 3: Location
    location: null,
    // Step 4: Staff Members
    staff_members: [],
    // Step 5: Booking Page
    booking_page_status: 'draft',
    logo_url: null,
    // Phone verification
    phone_number: '',
    country_code: '+1',
})

const stepComponent = computed(() => stepComponents[currentStepNumber.value])

// Determine if step 1 is complete
const canProceedFromStep1 = computed(() => {
    // Only email verification is required, phone is optional
    return isEmailVerified.value
})

/**
 * Load current progress from API
 */
async function loadProgress() {
    try {
        await ensureCsrf()
        const res = await api.get('/onboarding/progress')
        const data = res.data.data

        if (data.tech_support_pending) {
            techSupportPending.value = true
            techSupportTicketId.value = data.tech_support_ticket_id
            techSupportRequestedAt.value = data.tech_support_requested_at
            return
        }

        currentStepNumber.value = data.current_step || 1
        isEmailVerified.value = data.email_verified
        isPhoneVerified.value = data.phone_verified

        if (data.form_data) {
            Object.assign(formData.value, data.form_data)
        }
    } catch (err) {
        console.error('Failed to load progress:', err)
        toast.error('Failed to load progress')
    } finally {
        initialLoading.value = false
    }
}

/**
 * Move to next step
 */
async function nextStep(data = {}) {
    // Merge any data from the step
    Object.assign(formData.value, data)

    // Save current step data
    const saved = await saveCurrentStep()
    if (!saved) return

    // Move to next step
    if (currentStepNumber.value < 5) {
        currentStepNumber.value++
    }
}

/**
 * Move to previous step
 */
function prevStep() {
    if (currentStepNumber.value > 1) {
        currentStepNumber.value--
    }
}

/**
 * Update form data from child component
 */
function updateFormData(data) {
    Object.assign(formData.value, data)
}

/**
 * Save current step data
 */
async function saveCurrentStep() {
    loading.value = true
    errors.value = {}

    try {
        await ensureCsrf()

        let endpoint = ''
        let payload = {}

        switch (currentStepNumber.value) {
            case 2:
                endpoint = '/onboarding/studio-info'
                payload = {
                    studio_name: formData.value.studio_name,
                    studio_structure: formData.value.studio_structure,
                    subdomain: formData.value.subdomain,
                    studio_types: formData.value.studio_types,
                    studio_categories: formData.value.studio_categories,
                    default_language_app: formData.value.default_language_app,
                    default_currency: formData.value.default_currency,
                    cancellation_window_hours: formData.value.cancellation_window_hours,
                }
                break
            case 3:
                endpoint = '/onboarding/location'
                payload = formData.value.location || {}
                break
            case 4:
                endpoint = '/onboarding/staff-member'
                payload = { staff_members: formData.value.staff_members }
                break
            case 5:
                endpoint = '/onboarding/booking-page'
                payload = { booking_page_status: formData.value.booking_page_status }
                break
            default:
                return true
        }

        await api.post(endpoint, payload)
        return true
    } catch (err) {
        if (err.response?.data?.errors) {
            errors.value = err.response.data.errors
        } else {
            toast.error(err.response?.data?.message || 'Failed to save')
        }
        return false
    } finally {
        loading.value = false
    }
}

/**
 * Resend email verification
 */
async function resendEmailVerification() {
    loading.value = true
    try {
        await ensureCsrf()
        await api.post('/onboarding/resend-email')
        toast.success('Verification email sent!')
    } catch (err) {
        if (err.response?.status === 429) {
            toast.error(err.response.data.error || 'Please wait before requesting another email')
        } else {
            toast.error('Failed to send verification email')
        }
    } finally {
        loading.value = false
    }
}

/**
 * Send phone verification code
 */
async function sendPhoneCode(phoneData) {
    loading.value = true
    try {
        await ensureCsrf()
        await api.post('/onboarding/send-phone-code', phoneData)
        toast.success('Verification code sent!')
        formData.value.phone_number = phoneData.phone_number
        formData.value.country_code = phoneData.country_code
    } catch (err) {
        if (err.response?.status === 429) {
            toast.error(err.response.data.error || 'Too many requests')
        } else {
            toast.error(err.response?.data?.error || 'Failed to send verification code')
        }
    } finally {
        loading.value = false
    }
}

/**
 * Verify phone code
 */
async function verifyPhoneCode(code) {
    loading.value = true
    try {
        await ensureCsrf()
        await api.post('/onboarding/verify-phone-code', {
            code,
            phone_number: formData.value.phone_number,
            country_code: formData.value.country_code,
        })
        isPhoneVerified.value = true
        toast.success('Phone verified successfully!')

        // Auto-advance if email is also verified
        if (isEmailVerified.value && currentStepNumber.value === 1) {
            currentStepNumber.value = 2
        }
    } catch (err) {
        const errorMsg = err.response?.data?.error || 'Invalid verification code'
        toast.error(errorMsg)
        if (err.response?.data?.attempts_remaining !== undefined) {
            toast.info(`${err.response.data.attempts_remaining} attempts remaining`)
        }
    } finally {
        loading.value = false
    }
}

/**
 * Complete onboarding
 */
async function completeOnboarding() {
    loading.value = true
    try {
        await ensureCsrf()
        const res = await api.post('/onboarding/complete')
        toast.success('Setup complete!')

        // Redirect to dashboard
        const redirectUrl = res.data.data?.redirect_url || '/dashboard'
        window.location.href = redirectUrl
    } catch (err) {
        toast.error(err.response?.data?.error || 'Failed to complete setup')
    } finally {
        loading.value = false
    }
}

/**
 * Submit tech support request
 */
async function submitTechSupport(data) {
    techSupportLoading.value = true
    try {
        await ensureCsrf()
        const res = await api.post('/onboarding/tech-support', data)
        techSupportPending.value = true
        techSupportTicketId.value = res.data.data?.ticket_id
        techSupportRequestedAt.value = new Date().toISOString()
        showTechSupportModal.value = false
        toast.success('Support request submitted!')
    } catch (err) {
        toast.error(err.response?.data?.error || 'Failed to submit request')
    } finally {
        techSupportLoading.value = false
    }
}

onMounted(() => {
    loadProgress()
})
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
