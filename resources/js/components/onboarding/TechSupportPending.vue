<template>
    <div class="card">
        <div class="card-body text-center py-12">
            <!-- Support Icon -->
            <div class="w-20 h-20 mx-auto rounded-full bg-primary/10 flex items-center justify-center mb-6">
                <span class="icon-[tabler--headset] size-10 text-primary"></span>
            </div>

            <!-- Title -->
            <h2 class="text-2xl font-bold mb-2">Technical Support Requested</h2>

            <!-- Description -->
            <p class="text-base-content/70 max-w-md mx-auto mb-6">
                Our team has received your request and will complete your studio setup.
                You'll receive an email when your account is ready to use.
            </p>

            <!-- Ticket Info -->
            <div class="bg-base-200 rounded-lg p-4 inline-block mx-auto">
                <div class="flex items-center gap-4 text-sm">
                    <div>
                        <span class="text-base-content/60">Request ID:</span>
                        <span class="font-mono font-medium ml-1">#{{ ticketId }}</span>
                    </div>
                    <div v-if="requestedAt">
                        <span class="text-base-content/60">Submitted:</span>
                        <span class="font-medium ml-1">{{ formattedDate }}</span>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="mt-8 flex items-center justify-center gap-2 text-base-content/70">
                <span class="loading loading-spinner loading-sm"></span>
                <span>Waiting for support team...</span>
            </div>

            <!-- Contact Info -->
            <div class="mt-8 pt-6 border-t border-base-300">
                <p class="text-sm text-base-content/60">
                    Have questions? Contact us at
                    <a href="mailto:support@fitstudiohq.com" class="link link-primary">
                        support@fitstudiohq.com
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    ticketId: { type: [Number, String], default: null },
    requestedAt: { type: String, default: null },
})

const formattedDate = computed(() => {
    if (!props.requestedAt) return ''
    const date = new Date(props.requestedAt)
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    })
})
</script>
