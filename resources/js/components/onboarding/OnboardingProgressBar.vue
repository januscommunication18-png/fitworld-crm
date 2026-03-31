<template>
    <div class="mb-8">
        <!-- Steps indicator -->
        <div class="flex items-center justify-between">
            <div v-for="(label, index) in steps" :key="index" class="flex items-center flex-1">
                <!-- Step circle -->
                <div class="flex flex-col items-center">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm transition-all duration-300"
                        :class="stepClass(index + 1)"
                    >
                        <span v-if="index + 1 < currentStep" class="icon-[tabler--check] size-5"></span>
                        <span v-else>{{ index + 1 }}</span>
                    </div>
                    <span
                        class="text-xs mt-2 font-medium text-center hidden sm:block"
                        :class="index + 1 <= currentStep ? 'text-primary' : 'text-base-content/50'"
                    >
                        {{ label }}
                    </span>
                </div>

                <!-- Connector line (not after last step) -->
                <div
                    v-if="index < steps.length - 1"
                    class="flex-1 h-1 mx-2 rounded transition-all duration-300"
                    :class="index + 1 < currentStep ? 'bg-primary' : 'bg-base-300'"
                ></div>
            </div>
        </div>

        <!-- Mobile step label -->
        <div class="sm:hidden text-center mt-4">
            <span class="text-sm font-medium text-primary">
                Step {{ currentStep }}: {{ steps[currentStep - 1] }}
            </span>
        </div>
    </div>
</template>

<script setup>
defineProps({
    currentStep: {
        type: Number,
        default: 1,
    },
    steps: {
        type: Array,
        default: () => ['Step 1', 'Step 2', 'Step 3', 'Step 4', 'Step 5'],
    },
})

function stepClass(step) {
    if (step < currentStep) {
        return 'bg-primary text-primary-content'
    } else if (step === currentStep) {
        return 'bg-primary text-primary-content ring-4 ring-primary/30'
    } else {
        return 'bg-base-300 text-base-content/50'
    }
}
</script>
