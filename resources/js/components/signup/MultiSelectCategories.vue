<template>
    <div class="space-y-3">
        <!-- Searchable Multiselect Dropdown -->
        <div class="relative" ref="containerRef">
            <div
                class="input w-full h-[42px] flex items-center gap-2 cursor-pointer"
                :class="{ 'border-error': hasError }"
                @click.stop="toggleDropdown"
            >
                <!-- Selected Count Badge or Placeholder -->
                <span v-if="totalSelected > 0" class="badge badge-primary badge-sm shrink-0">
                    {{ totalSelected }} selected
                </span>

                <!-- Search Input -->
                <input
                    type="text"
                    class="flex-1 min-w-[80px] bg-transparent outline-none border-none focus:ring-0 p-0 text-sm"
                    :placeholder="totalSelected === 0 ? placeholder : 'Search more...'"
                    v-model="searchQuery"
                    @focus.stop="openDropdown"
                    @input="openDropdown"
                    @click.stop
                    ref="searchInputRef"
                />
                <span class="icon-[tabler--chevron-down] size-4 text-base-content/50 transition-transform shrink-0" :class="{ 'rotate-180': isOpen }"></span>
            </div>

            <!-- Dropdown -->
            <div
                v-if="isOpen"
                class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                @click.stop
            >
                <div v-if="filteredOptions.length === 0 && !showOthersInSearch" class="px-3 py-2 text-sm text-base-content/50">
                    No matching categories found
                </div>

                <!-- Category Options -->
                <div
                    v-for="option in filteredOptions"
                    :key="option"
                    class="px-3 py-2 text-sm cursor-pointer hover:bg-base-200 flex items-center gap-2"
                    :class="{ 'bg-primary/10': isSelected(option) }"
                    @click.stop="toggleCategory(option)"
                >
                    <input
                        type="checkbox"
                        :checked="isSelected(option)"
                        class="checkbox checkbox-primary checkbox-xs"
                        @click.stop
                    />
                    <span class="flex-1">{{ option }}</span>
                </div>

                <!-- Others Option -->
                <div
                    v-if="showOthersInSearch"
                    class="px-3 py-2 text-sm cursor-pointer hover:bg-base-200 flex items-center gap-2 border-t border-base-200"
                    :class="{ 'bg-primary/10': hasOthersSelected }"
                    @click.stop="toggleOthers"
                >
                    <input
                        type="checkbox"
                        :checked="hasOthersSelected"
                        class="checkbox checkbox-primary checkbox-xs"
                        @click.stop
                    />
                    <span class="flex-1 font-medium">Others (Add custom categories)</span>
                </div>
            </div>
        </div>

        <!-- Selected Tags (shown below input) -->
        <div v-if="totalSelected > 0" class="flex flex-wrap gap-1">
            <span
                v-for="category in selectedPredefined"
                :key="category"
                class="badge badge-primary badge-soft badge-sm gap-1"
            >
                {{ truncateLabel(category) }}
                <button type="button" class="hover:opacity-70" @click="removeCategory(category)">
                    <span class="icon-[tabler--x] size-3"></span>
                </button>
            </span>
            <span
                v-for="(custom, idx) in customCategories"
                :key="'custom-' + idx"
                class="badge badge-secondary badge-soft badge-sm gap-1"
            >
                {{ custom }}
                <button type="button" @click="removeCustomCategory(idx)">
                    <span class="icon-[tabler--x] size-3"></span>
                </button>
            </span>
        </div>

        <!-- Clear All -->
        <div v-if="totalSelected > 0" class="flex justify-end">
            <button type="button" class="text-xs link link-primary" @click="clearAll">Clear all</button>
        </div>

        <!-- Others Textarea -->
        <div v-if="hasOthersSelected" class="space-y-2">
            <label class="label-text text-sm font-medium">Custom Categories</label>
            <p class="text-xs text-base-content/50">Add your own categories (one per line)</p>
            <textarea
                v-model="customCategoriesText"
                class="textarea textarea-bordered w-full"
                rows="3"
                placeholder="Enter custom categories, one per line...&#10;e.g.&#10;Aerial Yoga&#10;Pole Fitness&#10;Aqua Aerobics"
                @input="updateCustomCategories"
            ></textarea>
            <div v-if="customCategories.length > 0" class="flex flex-wrap gap-1">
                <span
                    v-for="(custom, idx) in customCategories"
                    :key="idx"
                    class="badge badge-secondary badge-sm gap-1"
                >
                    {{ custom }}
                    <button type="button" @click="removeCustomCategory(idx)">
                        <span class="icon-[tabler--x] size-3"></span>
                    </button>
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Search and select categories...' },
    hasError: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

// All predefined categories (flat list)
const allCategories = [
    'Yoga (Hatha, Vinyasa, Power, Yin, Restorative)',
    'Pilates (Mat / Reformer)',
    'Meditation / Mindfulness',
    'Breathwork',
    'Tai Chi',
    'Qigong',
    'Stretching / Mobility',
    'Barre',
    'Strength Training',
    'Functional Training',
    'CrossFit',
    'Weightlifting (Olympic)',
    'Powerlifting',
    'Bodyweight Training (Calisthenics)',
    'Bootcamp',
    'Circuit Training',
    'HIIT (High-Intensity Interval Training)',
    'Indoor Cycling / Spin',
    'Running / Treadmill',
    'Rowing',
    'Step Aerobics',
    'Cardio Kickboxing',
    'Boxing',
    'Kickboxing',
    'Muay Thai',
    'MMA (Mixed Martial Arts)',
    'Brazilian Jiu-Jitsu (BJJ)',
    'Karate',
    'Taekwondo',
    'Self-Defense',
    'Zumba',
    'Dance Fitness',
    'Hip Hop Dance',
    'Ballet Fitness',
    'Jazzercise',
    'Open Gym',
    'Personal Training',
    'Small Group Training',
    'Beginner Fitness',
    'Senior Fitness',
    'Youth Fitness',
    'Prenatal / Postnatal Fitness',
    'Rehab / Physical Therapy',
    'Injury Recovery',
    'Adaptive Fitness',
    'EMS (Electro Muscle Stimulation)',
    'Sports Performance Training',
    'Athlete Conditioning',
    'Recovery Sessions',
    'Foam Rolling',
    'Mobility & Flexibility',
    'Sauna / Cold Therapy Sessions',
    'Relaxation Therapy',
    'Outdoor Bootcamp',
    'Hiking Fitness',
    'Trail Running',
    'Cycling (Outdoor)',
    'Adventure Fitness',
]

const isOpen = ref(false)
const searchQuery = ref('')
const containerRef = ref(null)
const searchInputRef = ref(null)
const hasOthersSelected = ref(false)
const customCategoriesText = ref('')
const customCategories = ref([])

// Initialize from modelValue
onMounted(() => {
    initializeFromModelValue()
})

function initializeFromModelValue() {
    if (props.modelValue && props.modelValue.length > 0) {
        // Separate predefined and custom categories
        const predefined = []
        const custom = []

        props.modelValue.forEach(cat => {
            if (allCategories.includes(cat)) {
                predefined.push(cat)
            } else if (cat && cat.trim()) {
                custom.push(cat)
            }
        })

        if (custom.length > 0) {
            hasOthersSelected.value = true
            customCategories.value = custom
            customCategoriesText.value = custom.join('\n')
        }
    }
}

// Selected predefined categories (from modelValue)
const selectedPredefined = computed(() => {
    return props.modelValue.filter(cat => allCategories.includes(cat))
})

// Total selected count (predefined + custom)
const totalSelected = computed(() => {
    return selectedPredefined.value.length + customCategories.value.length
})

// Filtered options based on search
const filteredOptions = computed(() => {
    if (!searchQuery.value) {
        return allCategories
    }
    const query = searchQuery.value.toLowerCase()
    return allCategories.filter(cat => cat.toLowerCase().includes(query))
})

// Show Others option in search results
const showOthersInSearch = computed(() => {
    if (!searchQuery.value) return true
    return 'others'.includes(searchQuery.value.toLowerCase()) ||
           'custom'.includes(searchQuery.value.toLowerCase()) ||
           'add'.includes(searchQuery.value.toLowerCase())
})

function truncateLabel(label) {
    return label.length > 25 ? label.substring(0, 22) + '...' : label
}

function isSelected(category) {
    return props.modelValue.includes(category)
}

function toggleCategory(category) {
    const current = [...props.modelValue]
    const index = current.indexOf(category)

    if (index === -1) {
        current.push(category)
    } else {
        current.splice(index, 1)
    }

    emit('update:modelValue', current)
}

function removeCategory(category) {
    const current = props.modelValue.filter(c => c !== category)
    emit('update:modelValue', current)
}

function toggleOthers() {
    hasOthersSelected.value = !hasOthersSelected.value
    if (!hasOthersSelected.value) {
        // Remove custom categories from modelValue
        const current = props.modelValue.filter(cat => allCategories.includes(cat))
        customCategories.value = []
        customCategoriesText.value = ''
        emit('update:modelValue', current)
    }
}

function updateCustomCategories() {
    // Parse textarea into array
    const lines = customCategoriesText.value
        .split('\n')
        .map(line => line.trim())
        .filter(line => line.length > 0)

    customCategories.value = lines

    // Update modelValue with both predefined and custom
    const predefined = props.modelValue.filter(cat => allCategories.includes(cat))
    emit('update:modelValue', [...predefined, ...lines])
}

function removeCustomCategory(index) {
    customCategories.value.splice(index, 1)
    customCategoriesText.value = customCategories.value.join('\n')

    // Update modelValue
    const predefined = props.modelValue.filter(cat => allCategories.includes(cat))
    emit('update:modelValue', [...predefined, ...customCategories.value])
}

function clearAll() {
    emit('update:modelValue', [])
    hasOthersSelected.value = false
    customCategories.value = []
    customCategoriesText.value = ''
}

function toggleDropdown() {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        nextTick(() => {
            searchInputRef.value?.focus()
        })
    }
}

function openDropdown() {
    isOpen.value = true
}

function handleClickOutside(event) {
    if (containerRef.value && !containerRef.value.contains(event.target)) {
        isOpen.value = false
        searchQuery.value = ''
    }
}

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside)
})

// Watch for external changes to modelValue
watch(() => props.modelValue, () => {
    // Check if there are custom categories in the new value
    const hasCustom = props.modelValue.some(cat => !allCategories.includes(cat) && cat.trim())
    if (hasCustom && !hasOthersSelected.value) {
        hasOthersSelected.value = true
        const custom = props.modelValue.filter(cat => !allCategories.includes(cat) && cat.trim())
        customCategories.value = custom
        customCategoriesText.value = custom.join('\n')
    }
}, { deep: true })
</script>
