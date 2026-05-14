<template>
    <div class="card w-full overflow-hidden">
        <div class="card-body min-w-0">
            <h2 class="text-2xl font-bold mb-1">Tell us about your studio</h2>
            <p class="text-base-content/60 mb-6">Basic info to get your profile started.</p>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="label-text" for="studio_name">Studio Name <span class="text-error">*</span></label>
                    <input id="studio_name" type="text" class="input w-full" :class="{ 'input-error': errors.studio_name }"
                        v-model="localData.studio_name" placeholder="e.g. Sunrise Yoga Studio" required />
                    <p v-if="errors.studio_name" class="text-error text-xs mt-1">{{ errors.studio_name[0] }}</p>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label class="label-text">Studio Categories <span class="text-error">*</span></label>
                        <button v-if="localData.studio_categories.length > 0" type="button" class="text-xs link link-primary" @click="categoriesRef?.clearAll()">Clear all</button>
                    </div>
                    <p class="text-xs text-base-content/50 mb-2">Select all categories that apply to your studio</p>
                    <MultiSelectCategories
                        ref="categoriesRef"
                        v-model="localData.studio_categories"
                        :has-error="!!errors.studio_categories"
                        placeholder="Search and select categories..."
                    />
                    <p v-if="errors.studio_categories" class="text-error text-xs mt-1">{{ errors.studio_categories[0] }}</p>
                </div>

                <!-- Studio Address (Smarty autocomplete + validation) -->
                <div>
                    <label class="label-text">Studio Address <span class="text-error">*</span></label>
                    <AddressAutocomplete
                        v-model="localData.address"
                        :address="addressProps"
                        :input-class="{ 'input-error': errors.address }"
                        placeholder="Search address, city, or zip code..."
                        @select="handleAddressSelect"
                    />
                    <p v-if="errors.address" class="text-error text-xs mt-1">{{ errors.address[0] }}</p>
                </div>

                <!-- Timezone -->
                <div>
                    <label class="label-text" for="timezone">Timezone</label>
                    <SearchSelect
                        v-model="localData.timezone"
                        :options="timezoneOptions"
                        placeholder="Search timezone..."
                    />
                </div>

                <!-- Default Currency -->
                <div>
                    <label class="label-text" for="default_currency">Default Currency <span class="text-error">*</span></label>
                    <p class="text-xs text-base-content/50 mb-2">Primary currency for pricing your services</p>
                    <SearchSelect
                        v-model="localData.default_currency"
                        :options="currencyOptions"
                        placeholder="Select currency..."
                    />
                    <p v-if="errors.default_currency" class="text-error text-xs mt-1">{{ errors.default_currency[0] }}</p>
                </div>

                <div>
                    <label class="label-text" for="subdomain">Your Studio URL <span class="text-error">*</span></label>
                    <div class="join w-full">
                        <input id="subdomain" type="text" class="input join-item flex-1"
                            :class="{ 'input-error': errors.subdomain, 'input-success': subdomainAvailable === true }"
                            v-model="localData.subdomain" placeholder="yourstudio" @input="handleSubdomainInput" />
                        <span class="btn btn-soft join-item pointer-events-none">.fitcrm.app</span>
                    </div>
                    <div class="flex items-center gap-1 mt-1">
                        <span v-if="checkingSubdomain" class="loading loading-spinner loading-xs text-base-content/40"></span>
                        <span v-else-if="subdomainAvailable === true" class="icon-[tabler--circle-check] size-4 text-success"></span>
                        <span v-else-if="subdomainAvailable === false" class="icon-[tabler--circle-x] size-4 text-error"></span>
                        <p class="text-xs" :class="{
                            'text-success': subdomainAvailable === true,
                            'text-error': subdomainAvailable === false || errors.subdomain,
                            'text-base-content/50': subdomainAvailable === null && !errors.subdomain,
                        }">
                            <template v-if="errors.subdomain">{{ errors.subdomain[0] }}</template>
                            <template v-else-if="checkingSubdomain">Checking availability...</template>
                            <template v-else-if="subdomainAvailable === true">This subdomain is available!</template>
                            <template v-else-if="subdomainAvailable === false">This subdomain is already taken.</template>
                            <template v-else>This will be your booking page URL</template>
                        </p>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="btn btn-primary" :disabled="!isValid || loading">
                        <span v-if="loading" class="loading loading-spinner loading-xs"></span>
                        <template v-else>Continue <span class="icon-[tabler--arrow-right] size-4"></span></template>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed, ref, watch } from 'vue'
import api from '../../utils/api.js'
import { debounce } from '../../utils/debounce.js'
import SearchSelect from './SearchSelect.vue'
import MultiSelectCategories from './MultiSelectCategories.vue'
import AddressAutocomplete from './AddressAutocomplete.vue'

const props = defineProps({
    formData: { type: Object, required: true },
    csrfToken: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['next', 'prev', 'update'])

const currencies = {
    'USD': { symbol: '$', name: 'US Dollar' },
    'CAD': { symbol: 'C$', name: 'Canadian Dollar' },
    'GBP': { symbol: '£', name: 'Pound Sterling' },
    'EUR': { symbol: '€', name: 'Euro' },
    'AUD': { symbol: 'A$', name: 'Australian Dollar' },
    'INR': { symbol: '₹', name: 'Indian Rupee' },
}

const timezones = [
    { value: 'America/New_York', label: 'Eastern Time (ET)' },
    { value: 'America/Chicago', label: 'Central Time (CT)' },
    { value: 'America/Denver', label: 'Mountain Time (MT)' },
    { value: 'America/Los_Angeles', label: 'Pacific Time (PT)' },
    { value: 'America/Phoenix', label: 'Arizona (AZ)' },
    { value: 'Pacific/Honolulu', label: 'Hawaii (HT)' },
    { value: 'America/Anchorage', label: 'Alaska (AKT)' },
    { value: 'America/Toronto', label: 'Toronto (ET)' },
    { value: 'America/Vancouver', label: 'Vancouver (PT)' },
    { value: 'Europe/London', label: 'London (GMT/BST)' },
    { value: 'Europe/Berlin', label: 'Berlin (CET)' },
    { value: 'Europe/Paris', label: 'Paris (CET)' },
    { value: 'Australia/Sydney', label: 'Sydney (AEST)' },
    { value: 'Australia/Melbourne', label: 'Melbourne (AEST)' },
    { value: 'Australia/Perth', label: 'Perth (AWST)' },
    { value: 'Asia/Kolkata', label: 'India (IST)' },
]

const timezoneOptions = computed(() => {
    return timezones.map(tz => ({
        value: tz.value,
        label: tz.label
    }))
})

const currencyOptions = computed(() => {
    return Object.entries(currencies).map(([code, info]) => ({
        value: code,
        label: `${info.symbol} ${code} - ${info.name}`
    }))
})

const categoriesRef = ref(null)
const subdomainAvailable = ref(null)
const checkingSubdomain = ref(false)
const subdomainManuallyEdited = ref(false)

const localData = reactive({
    studio_name: props.formData.studio_name,
    studio_categories: props.formData.studio_categories || [],
    address: props.formData.address || '',
    country: props.formData.country || '',
    city: props.formData.city || '',
    state: props.formData.state || '',
    zipcode: props.formData.zipcode || '',
    timezone: props.formData.timezone || 'America/New_York',
    subdomain: props.formData.subdomain,
    default_currency: props.formData.default_currency || 'USD',
})

// Props to pass existing address data to the AddressAutocomplete component
const addressProps = computed(() => ({
    address_line_1: localData.address,
    city: localData.city,
    state: localData.state,
    zip_code: localData.zipcode,
    country: localData.country || 'United States',
}))

const isValid = computed(() => localData.studio_name && localData.studio_categories.length > 0 && localData.address && localData.subdomain && subdomainAvailable.value !== false)

function handleAddressSelect(addressData) {
    localData.city = addressData.city || ''
    localData.state = addressData.state || ''
    localData.zipcode = addressData.zipcode || addressData.zip_code || ''
    localData.country = addressData.country || 'United States'
}

function generateSubdomainFromName(name) {
    return name
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/--+/g, '-')
        .replace(/(?:^-|-$)/g, '')
}

function formatSubdomain() {
    localData.subdomain = localData.subdomain.toLowerCase().replace(/[^a-z0-9-]/g, '').replace(/--+/g, '-')
}

const checkSubdomainAvailability = debounce(async (value) => {
    if (!value || value.length < 3) {
        subdomainAvailable.value = null
        return
    }
    checkingSubdomain.value = true
    try {
        const res = await api.get('/signup/subdomain-check', { params: { subdomain: value } })
        subdomainAvailable.value = res.data.data.available
    } catch {
        subdomainAvailable.value = null
    } finally {
        checkingSubdomain.value = false
    }
}, 500)

// Watch studio_name and auto-populate subdomain
watch(() => localData.studio_name, (newName) => {
    if (!subdomainManuallyEdited.value && newName) {
        localData.subdomain = generateSubdomainFromName(newName)
        subdomainAvailable.value = null
        checkSubdomainAvailability(localData.subdomain)
    }
}, { immediate: true })

function handleSubdomainInput() {
    subdomainManuallyEdited.value = true
    formatSubdomain()
    subdomainAvailable.value = null
    checkSubdomainAvailability(localData.subdomain)
}

function handleSubmit() {
    if (!isValid.value) return
    emit('update', { ...localData })
    emit('next')
}
</script>
