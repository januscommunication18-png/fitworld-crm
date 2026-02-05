<template>
    <div class="card">
        <div class="card-body text-center py-16">
            <div class="mb-6">
                <span class="icon-[tabler--confetti] size-16 text-primary"></span>
            </div>
            <h2 class="text-3xl font-bold mb-3">You're all set!</h2>
            <p class="text-base-content/60 mb-8 max-w-md mx-auto">
                Your studio <strong>{{ formData.studio_name }}</strong> is ready to go live.
            </p>

            <div class="card bg-base-200/50 max-w-sm mx-auto mb-8">
                <div class="card-body text-left space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="icon-[tabler--world] size-5 text-primary"></span>
                        <div>
                            <div class="text-xs text-base-content/50">Your booking page</div>
                            <div class="font-semibold">{{ formData.subdomain }}.fitcrm.app</div>
                        </div>
                    </div>
                    <div v-if="formData.class_name && !formData.skip_class_setup" class="flex items-center gap-3">
                        <span class="icon-[tabler--calendar] size-5 text-success"></span>
                        <div>
                            <div class="text-xs text-base-content/50">First class</div>
                            <div class="font-semibold">{{ formData.class_name }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="icon-[tabler--credit-card] size-5" :class="formData.stripe_connected ? 'text-success' : 'text-warning'"></span>
                        <div>
                            <div class="text-xs text-base-content/50">Payments</div>
                            <div class="font-semibold">{{ formData.stripe_connected ? 'Stripe connected' : 'Not connected yet' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <h3 class="font-semibold text-base-content/80">Next steps</h3>
                <ul class="text-sm text-base-content/60 space-y-1">
                    <li>Add your class schedule</li>
                    <li>Invite your students</li>
                    <li>Share your booking page</li>
                </ul>
            </div>

            <div class="mt-8">
                <a href="/dashboard" class="btn btn-primary btn-lg">
                    Go to Dashboard <span class="icon-[tabler--arrow-right] size-5"></span>
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    formData: { type: Object, required: true },
    csrfToken: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

defineEmits(['next', 'prev', 'update'])
</script>
