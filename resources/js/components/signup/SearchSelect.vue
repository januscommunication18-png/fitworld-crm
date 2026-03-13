<template>
    <div class="relative" ref="containerRef">
        <div
            class="input w-full flex items-center cursor-pointer"
            :class="{ 'input-disabled opacity-50': disabled }"
            @click.stop="toggleDropdown"
        >
            <input
                v-if="isOpen"
                type="text"
                class="flex-1 bg-transparent outline-none border-none focus:ring-0 p-0"
                :placeholder="placeholder"
                v-model="searchQuery"
                @focus.stop="openDropdown"
                @input="onSearchInput"
                @click.stop
                :disabled="disabled"
                ref="searchInputRef"
            />
            <span v-else class="flex-1 truncate" :class="selectedLabel ? 'text-base-content' : 'text-base-content/50'">
                {{ selectedLabel || placeholder }}
            </span>
            <span class="icon-[tabler--chevron-down] size-4 text-base-content/50 transition-transform" :class="{ 'rotate-180': isOpen }"></span>
        </div>

        <div
            v-if="isOpen && !disabled"
            class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
            @click.stop
        >
            <div v-if="filteredOptions.length === 0" class="px-3 py-2 text-sm text-base-content/50">
                No options found
            </div>
            <div
                v-for="option in filteredOptions"
                :key="option.value"
                class="px-3 py-2 text-sm cursor-pointer hover:bg-base-200 flex items-center justify-between"
                :class="{ 'bg-primary/10': option.value === modelValue }"
                @click.stop="selectOption(option)"
            >
                <span>{{ option.label }}</span>
                <span v-if="option.value === modelValue" class="icon-[tabler--check] size-4 text-primary"></span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    options: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Select...' },
    disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'change'])

const isOpen = ref(false)
const searchQuery = ref('')
const containerRef = ref(null)
const searchInputRef = ref(null)

const selectedLabel = computed(() => {
    const selected = props.options.find(opt => opt.value === props.modelValue)
    return selected ? selected.label : ''
})

const filteredOptions = computed(() => {
    if (!searchQuery.value) {
        return props.options
    }
    const query = searchQuery.value.toLowerCase()
    return props.options.filter(opt => opt.label.toLowerCase().includes(query))
})

function toggleDropdown() {
    if (!props.disabled) {
        isOpen.value = !isOpen.value
        if (isOpen.value) {
            searchQuery.value = ''
        }
    }
}

function openDropdown() {
    if (!props.disabled) {
        isOpen.value = true
    }
}

function onSearchInput() {
    if (!props.disabled && !isOpen.value) {
        isOpen.value = true
    }
}

function selectOption(option) {
    emit('update:modelValue', option.value)
    emit('change', option.value)
    isOpen.value = false
    searchQuery.value = ''
}

function handleClickOutside(event) {
    if (containerRef.value && !containerRef.value.contains(event.target)) {
        isOpen.value = false
        searchQuery.value = ''
    }
}

onMounted(() => {
    // Use mousedown to catch clicks before they propagate
    document.addEventListener('mousedown', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside)
})

// Handle dropdown open/close
watch(isOpen, (newValue) => {
    if (newValue) {
        // Focus search input when dropdown opens
        nextTick(() => {
            searchInputRef.value?.focus()
        })
    } else {
        searchQuery.value = ''
    }
})
</script>
