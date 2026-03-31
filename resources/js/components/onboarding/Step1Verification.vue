<template>
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-xl">Verify Your Contact Information</h2>
            <p class="text-base-content/70 text-sm mb-6">
                We need to verify your email and phone number to secure your account.
            </p>

            <!-- Email Verification -->
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-3">
                    <span class="icon-[tabler--mail] size-5 text-base-content/70"></span>
                    <h3 class="font-semibold">Email Verification</h3>
                    <span v-if="formData.email_verified" class="badge badge-success badge-sm gap-1">
                        <span class="icon-[tabler--check] size-3"></span>
                        Verified
                    </span>
                </div>

                <div class="pl-8">
                    <p class="text-sm text-base-content/70 mb-2">{{ formData.email }}</p>

                    <template v-if="!formData.email_verified">
                        <p class="text-sm text-warning mb-3">
                            Please check your inbox and click the verification link we sent you.
                        </p>
                        <button
                            type="button"
                            class="btn btn-soft btn-sm"
                            :disabled="emailResending || emailCooldown > 0"
                            @click="resendEmail"
                        >
                            <span v-if="emailResending" class="loading loading-spinner loading-xs"></span>
                            <span v-else-if="emailCooldown > 0">Resend in {{ emailCooldown }}s</span>
                            <span v-else>Resend Verification Email</span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Phone Verification -->
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-3">
                    <span class="icon-[tabler--phone] size-5 text-base-content/70"></span>
                    <h3 class="font-semibold">Phone Verification</h3>
                    <span v-if="formData.phone_verified" class="badge badge-success badge-sm gap-1">
                        <span class="icon-[tabler--check] size-3"></span>
                        Verified
                    </span>
                </div>

                <div class="pl-8">
                    <template v-if="!formData.phone_verified">
                        <!-- Phone input -->
                        <div v-if="!codeSent" class="space-y-4">
                            <div class="flex gap-2">
                                <select
                                    v-model="phoneCountryCode"
                                    class="select select-bordered w-32"
                                >
                                    <option v-for="(info, code) in options.country_codes" :key="code" :value="code">
                                        {{ info.flag }} {{ code }}
                                    </option>
                                </select>
                                <input
                                    type="tel"
                                    v-model="phoneNumber"
                                    class="input input-bordered flex-1"
                                    placeholder="Phone number"
                                    @keyup.enter="sendCode"
                                >
                            </div>
                            <button
                                type="button"
                                class="btn btn-primary btn-sm"
                                :disabled="!phoneNumber || phoneSending"
                                @click="sendCode"
                            >
                                <span v-if="phoneSending" class="loading loading-spinner loading-xs"></span>
                                <span v-else>Send Verification Code</span>
                            </button>
                        </div>

                        <!-- Code input -->
                        <div v-else class="space-y-4">
                            <p class="text-sm text-base-content/70">
                                Enter the 6-digit code sent to {{ phoneCountryCode }} {{ phoneNumber }}
                            </p>
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    v-model="verificationCode"
                                    class="input input-bordered w-40 text-center tracking-widest"
                                    placeholder="000000"
                                    maxlength="6"
                                    @keyup.enter="verifyCode"
                                >
                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    :disabled="verificationCode.length !== 6 || codeVerifying"
                                    @click="verifyCode"
                                >
                                    <span v-if="codeVerifying" class="loading loading-spinner loading-xs"></span>
                                    <span v-else>Verify</span>
                                </button>
                            </div>
                            <div class="flex gap-2 text-sm">
                                <button
                                    type="button"
                                    class="link link-primary"
                                    :disabled="phoneCooldown > 0"
                                    @click="sendCode"
                                >
                                    {{ phoneCooldown > 0 ? `Resend in ${phoneCooldown}s` : 'Resend code' }}
                                </button>
                                <span class="text-base-content/50">|</span>
                                <button type="button" class="link link-secondary" @click="changePhone">
                                    Change number
                                </button>
                            </div>
                            <p v-if="devMode" class="text-xs text-info">
                                Dev mode: Use code 123456 or any 6 digits
                            </p>
                        </div>
                    </template>
                    <template v-else>
                        <p class="text-sm text-success">
                            {{ formData.phone_country_code }} {{ formData.phone }}
                        </p>
                    </template>
                </div>
            </div>

            <!-- Error message -->
            <div v-if="errorMessage" class="alert alert-error mb-4">
                <span class="alert-icon icon-[tabler--alert-circle]"></span>
                <p>{{ errorMessage }}</p>
            </div>

            <!-- Navigation -->
            <div class="flex justify-end mt-6">
                <button
                    type="button"
                    class="btn btn-primary"
                    :disabled="!canContinue"
                    @click="handleNext"
                >
                    Continue
                    <span class="icon-[tabler--arrow-right] size-4"></span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import api from '../../utils/api.js'
import toast from '../../utils/toast.js'

const props = defineProps({
    formData: { type: Object, required: true },
    options: { type: Object, default: () => ({}) },
    csrfToken: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['next', 'update'])

// Email state
const emailResending = ref(false)
const emailCooldown = ref(0)
let emailTimer = null

// Phone state
const phoneNumber = ref('')
const phoneCountryCode = ref('+1')
const phoneSending = ref(false)
const phoneCooldown = ref(0)
const codeSent = ref(false)
const verificationCode = ref('')
const codeVerifying = ref(false)
const devMode = ref(false)
const errorMessage = ref('')
let phoneTimer = null

// Initialize from form data
onMounted(() => {
    phoneNumber.value = props.formData.phone || ''
    phoneCountryCode.value = props.formData.phone_country_code || '+1'
})

onUnmounted(() => {
    if (emailTimer) clearInterval(emailTimer)
    if (phoneTimer) clearInterval(phoneTimer)
})

const canContinue = computed(() => {
    return props.formData.email_verified && props.formData.phone_verified
})

async function resendEmail() {
    emailResending.value = true
    errorMessage.value = ''
    try {
        await api.post('/api/v1/onboarding/email/resend')
        toast.success('Verification email sent!')
        startEmailCooldown()
    } catch (error) {
        errorMessage.value = error.response?.data?.meta?.message || 'Failed to send email.'
    } finally {
        emailResending.value = false
    }
}

function startEmailCooldown() {
    emailCooldown.value = 60
    if (emailTimer) clearInterval(emailTimer)
    emailTimer = setInterval(() => {
        emailCooldown.value--
        if (emailCooldown.value <= 0) {
            clearInterval(emailTimer)
        }
    }, 1000)
}

async function sendCode() {
    phoneSending.value = true
    errorMessage.value = ''
    try {
        const response = await api.post('/api/v1/onboarding/phone/send', {
            phone: phoneNumber.value,
            phone_country_code: phoneCountryCode.value,
        })
        codeSent.value = true
        devMode.value = response.data.data?.dev_mode || false
        toast.success('Verification code sent!')
        startPhoneCooldown()

        // Update parent form data
        emit('update', {
            phone: phoneNumber.value,
            phone_country_code: phoneCountryCode.value,
        })
    } catch (error) {
        errorMessage.value = error.response?.data?.meta?.message || 'Failed to send code.'
    } finally {
        phoneSending.value = false
    }
}

function startPhoneCooldown() {
    phoneCooldown.value = 60
    if (phoneTimer) clearInterval(phoneTimer)
    phoneTimer = setInterval(() => {
        phoneCooldown.value--
        if (phoneCooldown.value <= 0) {
            clearInterval(phoneTimer)
        }
    }, 1000)
}

async function verifyCode() {
    codeVerifying.value = true
    errorMessage.value = ''
    try {
        await api.post('/api/v1/onboarding/phone/verify', {
            code: verificationCode.value,
        })
        toast.success('Phone verified!')
        emit('update', { phone_verified: true })
    } catch (error) {
        errorMessage.value = error.response?.data?.meta?.message || 'Invalid code. Please try again.'
    } finally {
        codeVerifying.value = false
    }
}

function changePhone() {
    codeSent.value = false
    verificationCode.value = ''
    phoneCooldown.value = 0
    if (phoneTimer) clearInterval(phoneTimer)
}

function handleNext() {
    if (canContinue.value) {
        emit('next')
    }
}
</script>
