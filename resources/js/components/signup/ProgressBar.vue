<template>
    <div class="mb-8">
        <div class="flex justify-between mb-2 text-xs text-base-content/60">
            <span>Step {{ currentStep }} of {{ totalSteps }}</span>
            <span>{{ Math.round(progress) }}%</span>
        </div>
        <div class="progress h-2" role="progressbar" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar progress-primary transition-all duration-500" :style="{ width: progress + '%' }"></div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    currentStep: { type: Number, required: true },
    totalSteps: { type: Number, default: 8 },
})

const progress = computed(() => ((props.currentStep - 1) / (props.totalSteps - 1)) * 100)
</script>
