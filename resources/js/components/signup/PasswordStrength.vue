<template>
    <div class="mt-2">
        <!-- Strength meter -->
        <div class="flex gap-1 mb-2">
            <div v-for="i in 4" :key="i" class="h-1.5 flex-1 rounded-full transition-colors duration-300"
                :class="i <= strength ? strengthColor : 'bg-base-300'"></div>
        </div>

        <!-- Rules checklist -->
        <ul class="space-y-1 text-xs">
            <li v-for="rule in rules" :key="rule.label" class="flex items-center gap-1.5"
                :class="rule.valid ? 'text-success' : 'text-base-content/50'">
                <span :class="rule.valid ? 'icon-[tabler--circle-check]' : 'icon-[tabler--circle]'" class="size-3.5"></span>
                {{ rule.label }}
            </li>
        </ul>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    password: { type: String, default: '' },
})

const rules = computed(() => [
    { label: 'At least 8 characters', valid: props.password.length >= 8 },
    { label: 'Contains uppercase letter', valid: /[A-Z]/.test(props.password) },
    { label: 'Contains lowercase letter', valid: /[a-z]/.test(props.password) },
    { label: 'Contains number', valid: /\d/.test(props.password) },
])

const strength = computed(() => rules.value.filter(r => r.valid).length)

const strengthColor = computed(() => {
    if (strength.value <= 1) return 'bg-error'
    if (strength.value <= 2) return 'bg-warning'
    if (strength.value <= 3) return 'bg-info'
    return 'bg-success'
})
</script>
