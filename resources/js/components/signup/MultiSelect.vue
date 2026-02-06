<template>
    <div class="relative" ref="container">
        <button
            type="button"
            class="advance-select-toggle w-full"
            :class="{ 'select-disabled': disabled }"
            @click="toggleDropdown"
            aria-expanded="isOpen"
        >
            <span v-if="selectedItems.length === 0" class="text-base-content/50">{{ placeholder }}</span>
            <span v-else class="flex flex-wrap gap-1 pe-6">
                <span
                    v-for="item in visibleItems"
                    :key="item"
                    class="badge badge-soft badge-primary badge-sm gap-1"
                >
                    {{ item }}
                    <button type="button" class="hover:text-error" @click.stop="removeItem(item)">
                        <span class="icon-[tabler--x] size-3"></span>
                    </button>
                </span>
                <span v-if="hiddenCount > 0" class="badge badge-soft badge-neutral badge-sm">
                    +{{ hiddenCount }}
                </span>
            </span>
            <span class="icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content absolute top-1/2 end-3 -translate-y-1/2"></span>
        </button>

        <div
            v-show="isOpen"
            class="advance-select-menu max-h-48 overflow-y-auto absolute z-50 w-full mt-1"
        >
            <div
                v-for="option in options"
                :key="option"
                class="advance-select-option cursor-pointer"
                :class="{ 'select-active': selectedItems.includes(option) }"
                @click="toggleItem(option)"
            >
                <div class="flex justify-between items-center flex-1">
                    <span>{{ option }}</span>
                    <span
                        v-if="selectedItems.includes(option)"
                        class="icon-[tabler--check] shrink-0 size-4 text-primary"
                    ></span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    options: { type: Array, required: true },
    placeholder: { type: String, default: 'Select options...' },
    disabled: { type: Boolean, default: false },
    maxVisible: { type: Number, default: 3 },
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const container = ref(null)
const selectedItems = ref([...props.modelValue])

const visibleItems = computed(() => selectedItems.value.slice(0, props.maxVisible))
const hiddenCount = computed(() => Math.max(0, selectedItems.value.length - props.maxVisible))

function toggleDropdown() {
    if (!props.disabled) {
        isOpen.value = !isOpen.value
    }
}

function toggleItem(item) {
    const index = selectedItems.value.indexOf(item)
    if (index === -1) {
        selectedItems.value.push(item)
    } else {
        selectedItems.value.splice(index, 1)
    }
    emit('update:modelValue', [...selectedItems.value])
}

function removeItem(item) {
    const index = selectedItems.value.indexOf(item)
    if (index !== -1) {
        selectedItems.value.splice(index, 1)
        emit('update:modelValue', [...selectedItems.value])
    }
}

function handleClickOutside(event) {
    if (container.value && !container.value.contains(event.target)) {
        isOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>
