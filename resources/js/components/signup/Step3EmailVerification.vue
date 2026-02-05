<template>
    <div class="card">
        <div class="card-body text-center py-12">
            <div class="mb-6">
                <span class="icon-[tabler--mail-check] size-16 text-primary"></span>
            </div>
            <h2 class="text-2xl font-bold mb-2">Check your email</h2>
            <p class="text-base-content/60 mb-6 max-w-md mx-auto">
                We sent a verification link to <strong>{{ formData.email }}</strong>.
                Click the link to verify your account.
            </p>

            <div class="alert alert-soft alert-info max-w-md mx-auto mb-6" role="alert">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <p class="text-sm">You can continue setting up your studio while we verify your email.</p>
            </div>

            <div class="flex flex-col items-center gap-3">
                <button type="button" class="btn btn-primary" @click="$emit('next')">
                    Continue Setup <span class="icon-[tabler--arrow-right] size-4"></span>
                </button>
                <button type="button" class="link link-primary text-sm no-underline" @click="resendEmail" :disabled="resendCooldown > 0">
                    {{ resendCooldown > 0 ? `Resend in ${resendCooldown}s` : 'Resend verification email' }}
                </button>
            </div>

            <div class="flex justify-start pt-6">
                <button type="button" class="btn btn-ghost btn-sm" @click="$emit('prev')">
                    <span class="icon-[tabler--arrow-left] size-4"></span> Back
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
    formData: { type: Object, required: true },
    csrfToken: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

defineEmits(['next', 'prev', 'update'])

const resendCooldown = ref(0)

function resendEmail() {
    resendCooldown.value = 60
    const timer = setInterval(() => {
        resendCooldown.value--
        if (resendCooldown.value <= 0) clearInterval(timer)
    }, 1000)
}
</script>
