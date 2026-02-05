<template>
    <div class="card">
        <div class="card-body">
            <h2 class="text-2xl font-bold mb-1">Payment Setup</h2>
            <p class="text-base-content/60 mb-6">Connect Stripe to accept payments from your students.</p>

            <div class="space-y-4">
                <div class="card bg-base-200/50">
                    <div class="card-body flex flex-row items-center gap-4">
                        <span class="icon-[tabler--credit-card] size-10 text-primary"></span>
                        <div class="flex-1">
                            <h5 class="font-semibold">Stripe Payments</h5>
                            <p class="text-sm text-base-content/60">Accept credit cards, debit cards, and digital wallets.</p>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" @click="connectStripe" :disabled="localData.stripe_connected">
                            {{ localData.stripe_connected ? 'Connected' : 'Connect Stripe' }}
                        </button>
                    </div>
                </div>

                <div v-if="localData.stripe_connected" class="alert alert-soft alert-success" role="alert">
                    <span class="icon-[tabler--circle-check] size-5"></span>
                    <p>Stripe account connected successfully.</p>
                </div>

                <label class="custom-option flex flex-row items-center gap-3 p-4">
                    <input type="checkbox" class="checkbox checkbox-primary" v-model="localData.skip_payments" />
                    <span class="label-text">
                        <span class="text-base font-medium">I'll set up payments later</span>
                        <span class="block text-base-content/60 text-sm">You can connect Stripe anytime from Settings.</span>
                    </span>
                </label>

                <div class="flex justify-between pt-4">
                    <button type="button" class="btn btn-ghost" @click="$emit('prev')">
                        <span class="icon-[tabler--arrow-left] size-4"></span> Back
                    </button>
                    <button type="button" class="btn btn-primary" :disabled="loading" @click="handleSubmit">
                        <span v-if="loading" class="loading loading-spinner loading-xs"></span>
                        <template v-else>Continue <span class="icon-[tabler--arrow-right] size-4"></span></template>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
    formData: { type: Object, required: true },
    csrfToken: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['next', 'prev', 'update'])

const localData = reactive({
    skip_payments: props.formData.skip_payments,
    stripe_connected: props.formData.stripe_connected,
})

function connectStripe() {
    // Placeholder for Stripe Connect flow
    localData.stripe_connected = true
}

function handleSubmit() {
    emit('update', { ...localData })
    emit('next')
}
</script>
