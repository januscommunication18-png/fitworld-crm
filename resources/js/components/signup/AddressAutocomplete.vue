<template>
    <div class="space-y-3 min-w-0">
        <!-- Search + Validate Row -->
        <div class="relative" ref="searchContainer">
            <div class="flex gap-2 min-w-0">
                <div class="relative flex-1 min-w-0">
                    <input
                        type="text"
                        class="input w-full pr-10"
                        :class="inputClass"
                        :placeholder="placeholder"
                        v-model="searchQuery"
                        @input="handleSearchInput"
                        @focus="showSuggestions = suggestions.length > 0"
                        @keydown.down.prevent="navigateDown"
                        @keydown.up.prevent="navigateUp"
                        @keydown.enter.prevent="selectHighlighted"
                        @keydown.escape="closeSuggestions"
                        autocomplete="off"
                    />
                    <span v-if="loading" class="loading loading-spinner loading-xs absolute top-1/2 end-3 -translate-y-1/2 text-primary"></span>
                </div>
                <button
                    type="button"
                    class="btn btn-outline btn-primary shrink-0"
                    :disabled="validating || !hasAddress"
                    @click="validateAddress"
                >
                    <span v-if="validating" class="loading loading-spinner loading-xs"></span>
                    <span v-else class="icon-[tabler--check] size-4"></span>
                    Validate
                </button>
            </div>

            <!-- Suggestions Dropdown -->
            <div
                v-show="showSuggestions && suggestions.length > 0"
                class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-72 overflow-y-auto"
            >
                <div
                    v-for="(suggestion, index) in suggestions"
                    :key="index"
                    class="px-4 py-3 cursor-pointer hover:bg-base-200 border-b border-base-200 last:border-b-0"
                    :class="{ 'bg-base-200': highlightedIndex === index }"
                    @click="selectSuggestion(suggestion)"
                    @mouseenter="highlightedIndex = index"
                >
                    <div class="font-medium text-sm">{{ suggestion.label || suggestion.street_line || '' }}</div>
                    <div v-if="suggestion.street_line" class="text-xs text-base-content/60">
                        {{ suggestion.city }}, {{ suggestion.state }} {{ suggestion.zipcode }}
                    </div>
                </div>
            </div>

            <!-- No results -->
            <div
                v-show="showSuggestions && suggestions.length === 0 && searchQuery.length >= 3 && !loading && hasSearched"
                class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg"
            >
                <div class="px-4 py-3 text-base-content/50 text-sm">No addresses found. Try entering more details.</div>
            </div>
        </div>

        <!-- Validation Result -->
        <div v-if="validationMessage" class="text-sm" :class="validationSuccess ? 'text-success' : 'text-error'">
            <span v-if="validationSuccess" class="icon-[tabler--circle-check] size-4 align-middle mr-1"></span>
            <span v-else class="icon-[tabler--alert-circle] size-4 align-middle mr-1"></span>
            {{ validationMessage }}
        </div>

        <!-- Address Summary (shown after address is populated, fields hidden) -->
        <div v-if="hasAddress && !editing" class="bg-base-200 rounded-lg px-4 py-3 flex items-start justify-between gap-3">
            <div class="text-sm">
                <div class="font-medium">{{ addressData.address_line_1 }}</div>
                <div v-if="addressData.address_line_2" class="text-base-content/60">{{ addressData.address_line_2 }}</div>
                <div class="text-base-content/60">
                    {{ [addressData.city, addressData.state].filter(Boolean).join(', ') }}
                    {{ addressData.zip_code ? ' ' + addressData.zip_code : '' }}
                </div>
                <div v-if="addressData.country" class="text-base-content/60">{{ addressData.country }}</div>
            </div>
            <button type="button" class="btn btn-xs btn-ghost shrink-0" @click="editing = true">
                <span class="icon-[tabler--edit] size-4"></span> Edit
            </button>
        </div>

        <!-- Editable Address Fields (shown when editing) -->
        <div v-if="editing" class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-base-200 rounded-lg">
            <!-- Address Line 1 -->
            <div class="sm:col-span-2">
                <label class="label-text" :for="'address_line_1_' + uid">Address Line 1</label>
                <input
                    type="text"
                    :id="'address_line_1_' + uid"
                    class="input w-full"
                    v-model="addressData.address_line_1"
                    placeholder="Street address"
                    @input="emitUpdate"
                />
            </div>

            <!-- Address Line 2 -->
            <div class="sm:col-span-2">
                <label class="label-text" :for="'address_line_2_' + uid">Address Line 2</label>
                <input
                    type="text"
                    :id="'address_line_2_' + uid"
                    class="input w-full"
                    v-model="addressData.address_line_2"
                    placeholder="Suite, unit, building, floor, etc."
                    @input="emitUpdate"
                />
            </div>

            <!-- City -->
            <div>
                <label class="label-text" :for="'city_' + uid">City <span class="text-error">*</span></label>
                <input
                    type="text"
                    :id="'city_' + uid"
                    class="input w-full"
                    v-model="addressData.city"
                    placeholder="City"
                    @input="emitUpdate"
                />
            </div>

            <!-- State -->
            <div>
                <label class="label-text" :for="'state_' + uid">State / Province</label>
                <input
                    type="text"
                    :id="'state_' + uid"
                    class="input w-full"
                    v-model="addressData.state"
                    placeholder="State"
                    @input="emitUpdate"
                />
            </div>

            <!-- Zip Code -->
            <div>
                <label class="label-text" :for="'zip_code_' + uid">Zip / Postal Code</label>
                <input
                    type="text"
                    :id="'zip_code_' + uid"
                    class="input w-full"
                    v-model="addressData.zip_code"
                    placeholder="Zip code"
                    @input="emitUpdate"
                />
            </div>

            <!-- Country -->
            <div>
                <label class="label-text" :for="'country_' + uid">Country</label>
                <input
                    type="text"
                    :id="'country_' + uid"
                    class="input w-full"
                    v-model="addressData.country"
                    placeholder="Country"
                    @input="emitUpdate"
                />
            </div>

            <!-- Done editing -->
            <div class="sm:col-span-2 flex justify-end">
                <button type="button" class="btn btn-sm btn-primary" @click="editing = false">
                    Done
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import api, { ensureCsrf } from '../../utils/api.js'
import { debounce } from '../../utils/debounce.js'

const props = defineProps({
    modelValue: { type: String, default: '' },
    address: { type: Object, default: () => ({}) },
    inputId: { type: String, default: 'address' },
    inputClass: { type: [String, Object, Array], default: '' },
    placeholder: { type: String, default: 'Search address, city, or zip code...' },
    maxlength: { type: Number, default: null },
})

const emit = defineEmits(['update:modelValue', 'select'])

const uid = ref(Math.random().toString(36).substring(2, 7))
const searchQuery = ref('')
const suggestions = ref([])
const showSuggestions = ref(false)
const loading = ref(false)
const hasSearched = ref(false)
const highlightedIndex = ref(-1)
const searchContainer = ref(null)
const validating = ref(false)
const validationMessage = ref('')
const validationSuccess = ref(false)
const editing = ref(false)

const addressData = reactive({
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: '',
    zip_code: '',
    country: 'United States',
})

const hasAddress = computed(() => {
    return !!(addressData.address_line_1 || addressData.city)
})

// Initialize from props
onMounted(() => {
    if (props.address) {
        addressData.address_line_1 = props.address.address_line_1 || props.modelValue || ''
        addressData.address_line_2 = props.address.address_line_2 || ''
        addressData.city = props.address.city || ''
        addressData.state = props.address.state || ''
        addressData.zip_code = props.address.zip_code || props.address.zipcode || ''
        addressData.country = props.address.country || 'United States'
    } else if (props.modelValue) {
        addressData.address_line_1 = props.modelValue
    }
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})

// Watch modelValue for external changes
watch(() => props.modelValue, (newVal) => {
    if (newVal !== buildFullAddress()) {
        addressData.address_line_1 = newVal
    }
})

function buildFullAddress() {
    const parts = [addressData.address_line_1]
    if (addressData.city) parts.push(addressData.city)
    if (addressData.state) parts.push(addressData.state)
    if (addressData.zip_code) parts.push(addressData.zip_code)
    return parts.filter(Boolean).join(', ')
}

function emitUpdate() {
    const fullAddress = buildFullAddress()
    emit('update:modelValue', fullAddress)
    emit('select', {
        street: addressData.address_line_1,
        address_line_1: addressData.address_line_1,
        address_line_2: addressData.address_line_2,
        city: addressData.city,
        state: addressData.state,
        zipcode: addressData.zip_code,
        zip_code: addressData.zip_code,
        country: addressData.country,
        fullAddress: fullAddress,
    })
}

const fetchSuggestions = debounce(async (search) => {
    if (!search || search.length < 3) {
        suggestions.value = []
        return
    }

    loading.value = true
    hasSearched.value = false
    try {
        const response = await api.get('/address/autocomplete', { params: { q: search } })
        suggestions.value = response.data || []
        showSuggestions.value = true
        highlightedIndex.value = -1
        hasSearched.value = true
    } catch (error) {
        console.error('Address autocomplete error:', error)
        suggestions.value = []
        hasSearched.value = true
    } finally {
        loading.value = false
    }
}, 300)

function handleSearchInput() {
    fetchSuggestions(searchQuery.value)
}

function selectSuggestion(suggestion) {
    addressData.address_line_1 = suggestion.street_line || ''
    addressData.city = suggestion.city || ''
    addressData.state = suggestion.state_name || suggestion.state || ''
    addressData.zip_code = suggestion.zipcode || ''
    addressData.country = 'United States'

    searchQuery.value = ''
    editing.value = false
    closeSuggestions()
    emitUpdate()
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
    if (searchContainer.value && !searchContainer.value.contains(event.target)) {
        closeSuggestions()
    }
}

async function validateAddress() {
    if (!addressData.address_line_1 && !addressData.city && !addressData.zip_code) {
        validationMessage.value = 'Please search and select an address first.'
        validationSuccess.value = false
        return
    }

    validating.value = true
    validationMessage.value = ''

    try {
        await ensureCsrf()
        const response = await api.post('/address/validate', {
            street: addressData.address_line_1,
            city: addressData.city,
            state: addressData.state,
            zipcode: addressData.zip_code,
        })

        const result = response.data

        if (result.valid) {
            if (result.street) addressData.address_line_1 = result.street
            if (result.street2) addressData.address_line_2 = result.street2
            if (result.city) addressData.city = result.city
            if (result.state_name) addressData.state = result.state_name
            else if (result.state) addressData.state = result.state
            if (result.zipcode) addressData.zip_code = result.zipcode.replace(/-$/, '')
            addressData.country = 'United States'

            validationMessage.value = 'Valid address!' + (result.county ? ' County: ' + result.county : '')
            validationSuccess.value = true
            editing.value = false
            emitUpdate()

            setTimeout(() => { validationMessage.value = '' }, 5000)
        } else {
            validationMessage.value = result.error || 'Could not validate this address.'
            validationSuccess.value = false
        }
    } catch (error) {
        validationMessage.value = 'Validation failed. Please try again.'
        validationSuccess.value = false
    } finally {
        validating.value = false
    }
}
</script>
