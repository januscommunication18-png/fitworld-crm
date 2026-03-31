<template>
    <div class="flex items-center justify-between">
        <template v-for="(step, index) in steps" :key="step.number">
            <!-- Step Circle -->
            <div class="flex flex-col items-center">
                <div
                    class="w-10 h-10 rounded-full flex items-center justify-center font-medium transition-colors"
                    :class="getStepClass(step.number)"
                >
                    <!-- Check icon for completed -->
                    <span v-if="step.number < currentStep" class="icon-[tabler--check] size-5"></span>
                    <!-- Number for current and upcoming -->
                    <span v-else>{{ step.number }}</span>
                </div>
                <div class="mt-2 text-center">
                    <div class="text-sm font-medium" :class="step.number <= currentStep ? 'text-base-content' : 'text-base-content/50'">
                        {{ step.label }}
                    </div>
                    <div class="text-xs text-base-content/60 hidden sm:block">
                        {{ step.description }}
                    </div>
                </div>
            </div>

            <!-- Connector Line (except after last step) -->
            <div
                v-if="index < steps.length - 1"
                class="flex-1 h-1 mx-2 rounded transition-colors"
                :class="step.number < currentStep ? 'bg-primary' : 'bg-base-300'"
            ></div>
        </template>
    </div>
</template>

<script setup>
defineProps({
    currentStep: {
        type: Number,
        required: true,
    },
    steps: {
        type: Array,
        required: true,
    },
})

function getStepClass(stepNumber) {
    if (stepNumber < currentStep) {
        return 'bg-primary text-primary-content'
    }
    if (stepNumber === currentStep) {
        return 'bg-primary text-primary-content ring-4 ring-primary/20'
    }
    return 'bg-base-300 text-base-content/50'
}
</script>
