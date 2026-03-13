<template>
    <div class="card w-[500px]">
        <div class="card-body">
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
                    <label class="label-text">Studio Types</label>
                    <p class="text-xs text-base-content/50 mb-2">Select all that apply</p>
                    <MultiSelect
                        v-model="localData.studio_types"
                        :options="studioTypeOptions"
                        placeholder="Select studio types..."
                    />
                    <div v-if="localData.studio_types.includes('Other')" class="mt-2">
                        <input
                            type="text"
                            class="input w-full"
                            v-model="localData.custom_studio_type"
                            placeholder="Enter your studio type..."
                            maxlength="50"
                        />
                    </div>
                </div>

                <!-- Country + State Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="country">Country <span class="text-error">*</span></label>
                        <SearchSelect
                            v-model="localData.country"
                            :options="countryOptions"
                            placeholder="Search country..."
                            @change="onCountryChange"
                        />
                        <p v-if="errors.country" class="text-error text-xs mt-1">{{ errors.country[0] }}</p>
                    </div>
                    <div>
                        <label class="label-text" for="state">State / Province</label>
                        <SearchSelect
                            v-model="localData.state"
                            :options="stateOptions"
                            placeholder="Search state..."
                            :disabled="!localData.country"
                        />
                        <p v-if="errors.state" class="text-error text-xs mt-1">{{ errors.state[0] }}</p>
                    </div>
                </div>

                <!-- City + Timezone Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="city">City</label>
                        <input id="city" type="text" class="input w-full" :class="{ 'input-error': errors.city }"
                            v-model="localData.city" placeholder="e.g. Austin" />
                        <p v-if="errors.city" class="text-error text-xs mt-1">{{ errors.city[0] }}</p>
                    </div>
                    <div>
                        <label class="label-text" for="timezone">Timezone</label>
                        <SearchSelect
                            v-model="localData.timezone"
                            :options="timezoneOptions"
                            placeholder="Search timezone..."
                        />
                    </div>
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

                <div class="flex justify-between pt-4">
                    <button type="button" class="btn btn-ghost" @click="$emit('prev')">
                        <span class="icon-[tabler--arrow-left] size-4"></span> Back
                    </button>
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
import MultiSelect from './MultiSelect.vue'
import SearchSelect from './SearchSelect.vue'

const props = defineProps({
    formData: { type: Object, required: true },
    csrfToken: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['next', 'prev', 'update'])

const studioTypeOptions = ['Yoga', 'Pilates', 'Barre', 'Spinning', 'CrossFit', 'Dance', 'Martial Arts', 'Personal Training', 'Other']

const countries = {
    'US': { name: 'United States', flag: '🇺🇸', timezone: 'America/New_York' },
    'CA': { name: 'Canada', flag: '🇨🇦', timezone: 'America/Toronto' },
    'GB': { name: 'United Kingdom', flag: '🇬🇧', timezone: 'Europe/London' },
    'DE': { name: 'Germany', flag: '🇩🇪', timezone: 'Europe/Berlin' },
    'AU': { name: 'Australia', flag: '🇦🇺', timezone: 'Australia/Sydney' },
    'IN': { name: 'India', flag: '🇮🇳', timezone: 'Asia/Kolkata' },
}

const statesByCountry = {
    'US': [
        'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware',
        'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky',
        'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi',
        'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico',
        'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania',
        'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont',
        'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming', 'District of Columbia'
    ],
    'CA': [
        'Alberta', 'British Columbia', 'Manitoba', 'New Brunswick', 'Newfoundland and Labrador',
        'Nova Scotia', 'Ontario', 'Prince Edward Island', 'Quebec', 'Saskatchewan',
        'Northwest Territories', 'Nunavut', 'Yukon'
    ],
    'GB': [
        'England', 'Scotland', 'Wales', 'Northern Ireland'
    ],
    'DE': [
        'Baden-Württemberg', 'Bavaria', 'Berlin', 'Brandenburg', 'Bremen', 'Hamburg', 'Hesse',
        'Lower Saxony', 'Mecklenburg-Vorpommern', 'North Rhine-Westphalia', 'Rhineland-Palatinate',
        'Saarland', 'Saxony', 'Saxony-Anhalt', 'Schleswig-Holstein', 'Thuringia'
    ],
    'AU': [
        'New South Wales', 'Victoria', 'Queensland', 'Western Australia', 'South Australia',
        'Tasmania', 'Australian Capital Territory', 'Northern Territory'
    ],
    'IN': [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat',
        'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh',
        'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
        'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh',
        'Uttarakhand', 'West Bengal', 'Delhi'
    ],
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

const countryOptions = computed(() => {
    return Object.entries(countries).map(([code, info]) => ({
        value: code,
        label: info.name
    }))
})

const stateOptions = computed(() => {
    if (!localData.country || !statesByCountry[localData.country]) {
        return []
    }
    return statesByCountry[localData.country].map(state => ({
        value: state,
        label: state
    }))
})

const timezoneOptions = computed(() => {
    return timezones.map(tz => ({
        value: tz.value,
        label: tz.label
    }))
})

const subdomainAvailable = ref(null)
const checkingSubdomain = ref(false)
const subdomainManuallyEdited = ref(false)

const localData = reactive({
    studio_name: props.formData.studio_name,
    studio_types: [...props.formData.studio_types],
    custom_studio_type: props.formData.custom_studio_type || '',
    country: props.formData.country || '',
    city: props.formData.city,
    state: props.formData.state || '',
    timezone: props.formData.timezone || 'America/New_York',
    subdomain: props.formData.subdomain,
})

const isValid = computed(() => localData.studio_name && localData.country && localData.subdomain && subdomainAvailable.value !== false)

function onCountryChange() {
    // Reset state when country changes
    localData.state = ''
    // Auto-set timezone based on country
    const countryInfo = countries[localData.country]
    if (countryInfo && countryInfo.timezone) {
        localData.timezone = countryInfo.timezone
    }
}

function generateSubdomainFromName(name) {
    return name
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/--+/g, '-')
        .replace(/(?:^-|-$)/g, '')
}

// Watch studio_name and auto-populate subdomain
watch(() => localData.studio_name, (newName) => {
    if (!subdomainManuallyEdited.value) {
        localData.subdomain = generateSubdomainFromName(newName)
        subdomainAvailable.value = null
        checkSubdomainAvailability(localData.subdomain)
    }
})

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
