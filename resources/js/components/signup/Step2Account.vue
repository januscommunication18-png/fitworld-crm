<template>
    <div class="card w-[500px]">
        <div class="card-body">
            <h2 class="text-2xl font-bold mb-1">Create your account</h2>
            <p class="text-base-content/60 mb-6">Let's start with the basics.</p>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="first_name">First Name</label>
                        <input id="first_name" type="text" class="input w-full" :class="{ 'input-error': errors.first_name }"
                            v-model="localData.first_name" @input="localData.first_name = sanitizeName(localData.first_name)" maxlength="50" placeholder="Jane" required />
                        <p v-if="errors.first_name" class="text-error text-xs mt-1">{{ errors.first_name[0] }}</p>
                    </div>
                    <div>
                        <label class="label-text" for="last_name">Last Name</label>
                        <input id="last_name" type="text" class="input w-full" :class="{ 'input-error': errors.last_name }"
                            v-model="localData.last_name" @input="localData.last_name = sanitizeName(localData.last_name)" maxlength="50" placeholder="Smith" required />
                        <p v-if="errors.last_name" class="text-error text-xs mt-1">{{ errors.last_name[0] }}</p>
                    </div>
                </div>

                <div>
                    <label class="label-text" for="email">Email</label>
                    <input id="email" type="email" class="input w-full" :class="{ 'input-error': errors.email }"
                        v-model="localData.email" placeholder="jane@yourstudio.com" required />
                    <p v-if="errors.email" class="text-error text-xs mt-1">{{ errors.email[0] }}</p>
                </div>

                <div>
                    <label class="label-text" for="password">Password</label>
                    <input id="password" type="password" class="input w-full" :class="{ 'input-error': errors.password }"
                        v-model="localData.password" placeholder="Create a strong password" required />
                    <p v-if="errors.password" class="text-error text-xs mt-1">{{ errors.password[0] }}</p>
                    <PasswordStrength :password="localData.password" />
                </div>

                <!-- Terms & Conditions and Privacy Policy -->
                <div v-if="hasLegalPages" class="space-y-3 pt-2">
                    <!-- Terms & Conditions -->
                    <label v-if="legalPages.has_terms" class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" class="checkbox checkbox-primary mt-0.5" v-model="localData.agreed_terms" />
                        <span class="text-sm">
                            I agree to the
                            <button type="button" @click="showModal('terms')" class="text-primary hover:underline font-medium">
                                Terms & Conditions
                            </button>
                        </span>
                    </label>

                    <!-- Privacy Policy -->
                    <label v-if="legalPages.has_privacy" class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" class="checkbox checkbox-primary mt-0.5" v-model="localData.agreed_privacy" />
                        <span class="text-sm">
                            I agree to the
                            <button type="button" @click="showModal('privacy')" class="text-primary hover:underline font-medium">
                                Privacy Policy
                            </button>
                        </span>
                    </label>

                    <p v-if="showAgreementError" class="text-error text-xs">
                        Please agree to the required terms to continue.
                    </p>
                </div>

                <!-- Google SSO placeholder -->
                <div class="divider text-base-content/40 text-xs">or</div>
                <button type="button" class="btn btn-soft w-full" disabled>
                    <span class="icon-[tabler--brand-google] size-5"></span>
                    Continue with Google
                    <span class="badge badge-xs badge-soft ms-2">Coming Soon</span>
                </button>

                <div class="flex justify-between pt-4">
                    <button type="button" class="btn btn-ghost" @click="$emit('prev')">
                        <span class="icon-[tabler--arrow-left] size-4"></span> Back
                    </button>
                    <button type="submit" class="btn btn-primary" :disabled="!isValid || loading">
                        <span v-if="loading" class="loading loading-spinner loading-xs"></span>
                        <template v-else>Continue <span class="icon-[tabler--arrow-right] size-4"></span></template>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Legal Page Modal -->
    <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeModal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>

        <!-- Modal Content -->
        <div class="relative bg-base-100 rounded-xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-base-200">
                <h3 class="text-lg font-bold">{{ modalTitle }}</h3>
                <button type="button" @click="closeModal" class="btn btn-ghost btn-sm btn-square">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto px-6 py-4">
                <div class="prose prose-sm max-w-none" v-html="modalContent"></div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end px-6 py-4 border-t border-base-200">
                <button type="button" @click="closeModal" class="btn btn-primary">
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed, ref } from 'vue'
import PasswordStrength from './PasswordStrength.vue'

const props = defineProps({
    formData: { type: Object, required: true },
    csrfToken: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
    legalPages: { type: Object, default: () => ({ has_terms: false, has_privacy: false, terms: null, privacy: null }) },
})

const emit = defineEmits(['next', 'prev', 'update'])

const localData = reactive({
    first_name: props.formData.first_name,
    last_name: props.formData.last_name,
    email: props.formData.email,
    password: props.formData.password,
    is_studio_owner: true, // Always true for host signup
    agreed_terms: props.formData.agreed_terms || false,
    agreed_privacy: props.formData.agreed_privacy || false,
})

const showAgreementError = ref(false)

// Modal state
const modalOpen = ref(false)
const modalType = ref('terms')

const modalTitle = computed(() => {
    if (modalType.value === 'terms') {
        return props.legalPages.terms?.title || 'Terms & Conditions'
    }
    return props.legalPages.privacy?.title || 'Privacy Policy'
})

const modalContent = computed(() => {
    if (modalType.value === 'terms') {
        return props.legalPages.terms?.content || ''
    }
    return props.legalPages.privacy?.content || ''
})

const hasLegalPages = computed(() => {
    return props.legalPages.has_terms || props.legalPages.has_privacy
})

function showModal(type) {
    modalType.value = type
    modalOpen.value = true
    document.body.style.overflow = 'hidden'
}

function closeModal() {
    modalOpen.value = false
    document.body.style.overflow = ''
}

function sanitizeName(value) {
    // Only allow letters (including accented), spaces, hyphens, and apostrophes
    // Remove numbers and special characters
    return value.replace(/[^a-zA-ZÀ-ÿ\s\-']/g, '')
}

const isValid = computed(() => {
    const firstName = localData.first_name?.trim()
    const lastName = localData.last_name?.trim()
    const password = localData.password
    const passwordValid = password.length >= 8 &&
        /[A-Z]/.test(password) &&
        /[a-z]/.test(password) &&
        /\d/.test(password)

    // Check legal agreements
    const termsOk = !props.legalPages.has_terms || localData.agreed_terms
    const privacyOk = !props.legalPages.has_privacy || localData.agreed_privacy

    return firstName && lastName && localData.email && passwordValid && termsOk && privacyOk
})

function handleSubmit() {
    // Check agreements
    const termsOk = !props.legalPages.has_terms || localData.agreed_terms
    const privacyOk = !props.legalPages.has_privacy || localData.agreed_privacy

    if (!termsOk || !privacyOk) {
        showAgreementError.value = true
        return
    }

    showAgreementError.value = false

    if (!isValid.value) return
    emit('update', { ...localData })
    emit('next')
}
</script>
