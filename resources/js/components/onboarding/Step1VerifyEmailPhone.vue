<template>
    <div class="card">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-2">Verify Your Contact Information</h2>
            <p class="text-base-content/70 mb-6">Please verify your email address to continue. Phone verification is optional.</p>

            <!-- Email Verification -->
            <div class="border border-base-300 rounded-lg p-4 mb-4">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center"
                            :class="emailVerified ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning'"
                        >
                            <span v-if="emailVerified" class="icon-[tabler--check] size-5"></span>
                            <span v-else class="icon-[tabler--mail] size-5"></span>
                        </div>
                        <div>
                            <h3 class="font-medium">Email Address</h3>
                            <p class="text-sm text-base-content/60">{{ userEmail }}</p>
                        </div>
                    </div>
                    <div>
                        <span v-if="emailVerified" class="badge badge-success">Verified</span>
                        <button
                            v-else
                            type="button"
                            class="btn btn-sm btn-outline"
                            :class="{ 'loading': loading && resendingEmail }"
                            :disabled="loading || emailCooldown > 0"
                            @click="resendEmail"
                        >
                            <template v-if="emailCooldown > 0">
                                Resend in {{ emailCooldown }}s
                            </template>
                            <template v-else>
                                Resend Verification
                            </template>
                        </button>
                    </div>
                </div>
                <p v-if="!emailVerified" class="text-sm text-base-content/60 mt-3">
                    Check your inbox for the verification link. Didn't receive it?
                </p>
            </div>

            <!-- Phone Verification (Optional) -->
            <div class="border border-base-300 rounded-lg p-4">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center"
                            :class="phoneVerified ? 'bg-success/10 text-success' : 'bg-base-200 text-base-content/50'"
                        >
                            <span v-if="phoneVerified" class="icon-[tabler--check] size-5"></span>
                            <span v-else class="icon-[tabler--phone] size-5"></span>
                        </div>
                        <div>
                            <h3 class="font-medium">Phone Number <span class="text-base-content/50 text-sm font-normal">(Optional)</span></h3>
                            <p v-if="phoneVerified" class="text-sm text-base-content/60">Verified</p>
                            <p v-else class="text-sm text-base-content/60">Verify via SMS for account recovery</p>
                        </div>
                    </div>
                    <span v-if="phoneVerified" class="badge badge-success">Verified</span>
                </div>

                <!-- Phone Input (if not verified) -->
                <div v-if="!phoneVerified && !showCodeInput" class="space-y-4">
                    <div class="flex gap-2">
                        <select
                            v-model="phoneCountryCode"
                            class="select select-bordered w-28"
                        >
                            <option value="+1">+1 (US)</option>
                            <option value="+44">+44 (UK)</option>
                            <option value="+61">+61 (AU)</option>
                            <option value="+91">+91 (IN)</option>
                            <option value="+49">+49 (DE)</option>
                            <option value="+33">+33 (FR)</option>
                            <option value="+81">+81 (JP)</option>
                            <option value="+86">+86 (CN)</option>
                            <option value="+55">+55 (BR)</option>
                            <option value="+52">+52 (MX)</option>
                        </select>
                        <input
                            v-model="phoneNumber"
                            type="tel"
                            placeholder="Phone number"
                            class="input input-bordered flex-1"
                        >
                    </div>
                    <button
                        type="button"
                        class="btn btn-primary w-full"
                        :class="{ 'loading': loading && sendingCode }"
                        :disabled="loading || !phoneNumber || phoneCooldown > 0"
                        @click="sendCode"
                    >
                        <template v-if="phoneCooldown > 0">
                            Send Code ({{ phoneCooldown }}s)
                        </template>
                        <template v-else>
                            <span class="icon-[tabler--send] size-4 mr-1"></span>
                            Send Verification Code
                        </template>
                    </button>
                </div>

                <!-- Code Input (after sending) -->
                <div v-if="!phoneVerified && showCodeInput" class="space-y-4">
                    <p class="text-sm text-base-content/60">
                        Enter the 6-digit code sent to {{ phoneCountryCode }} {{ phoneNumber }}
                    </p>
                    <div class="flex gap-2 justify-center">
                        <input
                            v-for="(_, index) in 6"
                            :key="index"
                            :ref="el => codeInputRefs[index] = el"
                            type="text"
                            maxlength="1"
                            class="input input-bordered w-12 h-12 text-center text-xl font-bold"
                            @input="handleCodeInput($event, index)"
                            @keydown="handleCodeKeydown($event, index)"
                            @paste="handleCodePaste"
                        >
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="btn btn-outline flex-1"
                            @click="showCodeInput = false"
                        >
                            Change Number
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary flex-1"
                            :class="{ 'loading': loading && verifyingCode }"
                            :disabled="loading || verificationCode.length !== 6"
                            @click="verifyCode"
                        >
                            Verify Code
                        </button>
                    </div>
                </div>
            </div>

            <!-- Next Button -->
            <div class="flex justify-end mt-6">
                <button
                    type="button"
                    class="btn btn-primary"
                    :disabled="!canProceed"
                    @click="$emit('next')"
                >
                    Continue
                    <span class="icon-[tabler--arrow-right] size-4 ml-1"></span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    formData: { type: Object, default: () => ({}) },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
    emailVerified: { type: Boolean, default: false },
    phoneVerified: { type: Boolean, default: false },
    userEmail: { type: String, default: '' },
})

const emit = defineEmits(['next', 'prev', 'update', 'email-resend', 'phone-send', 'phone-verify'])

const phoneNumber = ref(props.formData.phone_number || '')
const phoneCountryCode = ref(props.formData.country_code || '+1')
const showCodeInput = ref(false)
const verificationCode = ref('')
const codeInputRefs = ref([])
const emailCooldown = ref(0)
const phoneCooldown = ref(0)
const resendingEmail = ref(false)
const sendingCode = ref(false)
const verifyingCode = ref(false)

let emailTimer = null
let phoneTimer = null

const canProceed = computed(() => {
    // Only email verification is required, phone is optional
    return props.emailVerified
})

function resendEmail() {
    resendingEmail.value = true
    emit('email-resend')
    startEmailCooldown()
    setTimeout(() => {
        resendingEmail.value = false
    }, 1000)
}

function sendCode() {
    sendingCode.value = true
    emit('phone-send', {
        phone_number: phoneNumber.value,
        country_code: phoneCountryCode.value,
    })
    showCodeInput.value = true
    startPhoneCooldown()
    setTimeout(() => {
        sendingCode.value = false
    }, 1000)
}

function verifyCode() {
    verifyingCode.value = true
    emit('phone-verify', verificationCode.value)
    setTimeout(() => {
        verifyingCode.value = false
    }, 1000)
}

function handleCodeInput(event, index) {
    const value = event.target.value
    if (value.length === 1 && index < 5) {
        codeInputRefs.value[index + 1]?.focus()
    }
    updateVerificationCode()
}

function handleCodeKeydown(event, index) {
    if (event.key === 'Backspace' && !event.target.value && index > 0) {
        codeInputRefs.value[index - 1]?.focus()
    }
}

function handleCodePaste(event) {
    event.preventDefault()
    const paste = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6)
    paste.split('').forEach((char, index) => {
        if (codeInputRefs.value[index]) {
            codeInputRefs.value[index].value = char
        }
    })
    updateVerificationCode()
    codeInputRefs.value[Math.min(paste.length, 5)]?.focus()
}

function updateVerificationCode() {
    verificationCode.value = codeInputRefs.value
        .map(input => input?.value || '')
        .join('')
}

function startEmailCooldown() {
    emailCooldown.value = 60
    emailTimer = setInterval(() => {
        emailCooldown.value--
        if (emailCooldown.value <= 0) {
            clearInterval(emailTimer)
        }
    }, 1000)
}

function startPhoneCooldown() {
    phoneCooldown.value = 60
    phoneTimer = setInterval(() => {
        phoneCooldown.value--
        if (phoneCooldown.value <= 0) {
            clearInterval(phoneTimer)
        }
    }, 1000)
}

onUnmounted(() => {
    if (emailTimer) clearInterval(emailTimer)
    if (phoneTimer) clearInterval(phoneTimer)
})
</script>
