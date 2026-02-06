<template>
    <div class="card w-[500px]">
        <div class="card-body">
            <h2 class="text-2xl font-bold mb-1">Tell us about your studio</h2>
            <p class="text-base-content/60 mb-6">Basic info to get your profile started.</p>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="label-text" for="studio_name">Studio Name</label>
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
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="city">City</label>
                        <input id="city" type="text" class="input w-full" :class="{ 'input-error': errors.city }"
                            v-model="localData.city" placeholder="e.g. Austin, TX" />
                        <p v-if="errors.city" class="text-error text-xs mt-1">{{ errors.city[0] }}</p>
                    </div>
                    <div>
                        <label class="label-text" for="timezone">Timezone</label>
                        <select id="timezone" class="select w-full" v-model="localData.timezone">
                            <option value="America/New_York">Eastern (ET)</option>
                            <option value="America/Chicago">Central (CT)</option>
                            <option value="America/Denver">Mountain (MT)</option>
                            <option value="America/Los_Angeles">Pacific (PT)</option>
                            <option value="America/Phoenix">Arizona (AZ)</option>
                            <option value="Pacific/Honolulu">Hawaii (HT)</option>
                            <option value="America/Anchorage">Alaska (AKT)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="label-text" for="subdomain">Your Studio URL</label>
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

const props = defineProps({
    formData: { type: Object, required: true },
    csrfToken: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['next', 'prev', 'update'])

const studioTypeOptions = ['Yoga', 'Pilates', 'Barre', 'Spinning', 'CrossFit', 'Dance', 'Martial Arts', 'Personal Training', 'Other']

const subdomainAvailable = ref(null)
const checkingSubdomain = ref(false)
const subdomainManuallyEdited = ref(false)

const localData = reactive({
    studio_name: props.formData.studio_name,
    studio_types: [...props.formData.studio_types],
    city: props.formData.city,
    timezone: props.formData.timezone || 'America/New_York',
    subdomain: props.formData.subdomain,
})

const isValid = computed(() => localData.studio_name && localData.subdomain && subdomainAvailable.value !== false)

function generateSubdomainFromName(name) {
    return name
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/--+/g, '-')
        .replace(/^-|-$/g, '')
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
