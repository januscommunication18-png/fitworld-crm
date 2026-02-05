<template>
    <div class="relative" ref="container">
        <input
            :id="inputId"
            type="text"
            class="input w-full"
            :class="inputClass"
            :placeholder="placeholder"
            v-model="query"
            @input="handleInput"
            @focus="showSuggestions = suggestions.length > 0"
            @keydown.down.prevent="navigateDown"
            @keydown.up.prevent="navigateUp"
            @keydown.enter.prevent="selectHighlighted"
            @keydown.escape="closeSuggestions"
            autocomplete="off"
        />
        <span v-if="loading" class="loading loading-spinner loading-xs absolute top-1/2 end-3 -translate-y-1/2 text-base-content/40"></span>

        <div
            v-show="showSuggestions && suggestions.length > 0"
            class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-box shadow-lg max-h-60 overflow-y-auto"
        >
            <div
                v-for="(suggestion, index) in suggestions"
                :key="index"
                class="px-4 py-2 cursor-pointer hover:bg-base-200 text-sm"
                :class="{ 'bg-base-200': highlightedIndex === index }"
                @click="selectSuggestion(suggestion)"
                @mouseenter="highlightedIndex = index"
            >
                <div class="font-medium">{{ suggestion.street_line }}</div>
                <div class="text-base-content/60 text-xs">
                    {{ suggestion.city }}, {{ suggestion.state }} {{ suggestion.zipcode }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { debounce } from '../../utils/debounce.js'

const props = defineProps({
    modelValue: { type: String, default: '' },
    smartyKey: { type: String, required: true },
    inputId: { type: String, default: 'address' },
    inputClass: { type: [String, Object, Array], default: '' },
    placeholder: { type: String, default: 'Start typing an address...' },
})

const emit = defineEmits(['update:modelValue', 'select'])

const query = ref(props.modelValue)
const suggestions = ref([])
const showSuggestions = ref(false)
const loading = ref(false)
const highlightedIndex = ref(-1)
const container = ref(null)

const fetchSuggestions = debounce(async (search) => {
    if (!search || search.length < 3 || !props.smartyKey) {
        suggestions.value = []
        return
    }

    loading.value = true
    try {
        const params = new URLSearchParams({
            key: props.smartyKey,
            search: search,
            source: 'all',
        })

        const response = await fetch(`https://us-autocomplete-pro.api.smarty.com/lookup?${params}`)
        const data = await response.json()

        if (data.suggestions) {
            suggestions.value = data.suggestions.slice(0, 6)
            showSuggestions.value = true
            highlightedIndex.value = -1
        } else {
            suggestions.value = []
        }
    } catch (error) {
        console.error('Smarty API error:', error)
        suggestions.value = []
    } finally {
        loading.value = false
    }
}, 300)

function handleInput() {
    emit('update:modelValue', query.value)
    fetchSuggestions(query.value)
}

function selectSuggestion(suggestion) {
    const fullAddress = `${suggestion.street_line}, ${suggestion.city}, ${suggestion.state} ${suggestion.zipcode}`
    query.value = fullAddress
    emit('update:modelValue', fullAddress)
    emit('select', {
        street: suggestion.street_line,
        city: suggestion.city,
        state: suggestion.state,
        zipcode: suggestion.zipcode,
        fullAddress: fullAddress,
    })
    closeSuggestions()
}

function closeSuggestions() {
    showSuggestions.value = false
    highlightedIndex.value = -1
}

function navigateDown() {
    if (highlightedIndex.value < suggestions.value.length - 1) {
        highlightedIndex.value++
    }
}

function navigateUp() {
    if (highlightedIndex.value > 0) {
        highlightedIndex.value--
    }
}

function selectHighlighted() {
    if (highlightedIndex.value >= 0 && suggestions.value[highlightedIndex.value]) {
        selectSuggestion(suggestions.value[highlightedIndex.value])
    }
}

function handleClickOutside(event) {
    if (container.value && !container.value.contains(event.target)) {
        closeSuggestions()
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>
