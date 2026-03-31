<template>
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-xl">Booking Page Setup</h2>
            <p class="text-base-content/70 text-sm mb-6">
                Customize your public booking page and decide when to go live.
            </p>

            <div class="space-y-6">
                <!-- Logo Upload -->
                <div>
                    <label class="label label-text">Studio Logo</label>
                    <div class="flex items-start gap-4">
                        <div class="avatar">
                            <div class="w-24 h-24 rounded-lg bg-base-200 flex items-center justify-center overflow-hidden">
                                <img v-if="logoPreview" :src="logoPreview" alt="Logo preview" class="object-cover w-full h-full">
                                <span v-else class="icon-[tabler--building-store] size-10 text-base-content/30"></span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <input
                                type="file"
                                ref="fileInput"
                                class="hidden"
                                accept="image/*"
                                @change="handleFileSelect"
                            >
                            <button
                                type="button"
                                class="btn btn-outline btn-sm"
                                @click="$refs.fileInput.click()"
                                :disabled="uploading"
                            >
                                <span v-if="uploading" class="loading loading-spinner loading-xs"></span>
                                <span v-else class="icon-[tabler--upload] size-4"></span>
                                {{ logoPreview ? 'Change Logo' : 'Upload Logo' }}
                            </button>
                            <p class="text-xs text-base-content/50 mt-2">
                                Recommended: Square image, at least 200x200px. Max 2MB.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Publish Toggle -->
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold">Publish Booking Page</h3>
                        <p class="text-sm text-base-content/70">
                            Make your booking page visible to the public.
                        </p>
                    </div>
                    <input
                        type="checkbox"
                        v-model="isLive"
                        class="toggle toggle-primary toggle-lg"
                    >
                </div>

                <!-- Preview card -->
                <div class="card card-bordered bg-base-200/50">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3 mb-3">
                            <span :class="isLive ? 'icon-[tabler--world] text-success' : 'icon-[tabler--world-off] text-base-content/50'" class="size-5"></span>
                            <span class="font-medium">
                                {{ isLive ? 'Your booking page will be live' : 'Your booking page will be hidden' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-base-content/70">Your URL:</span>
                            <code class="bg-base-300 px-2 py-1 rounded text-xs">
                                {{ formData.subdomain || 'your-studio' }}.{{ appDomain }}
                            </code>
                        </div>
                    </div>
                </div>

                <!-- What happens next -->
                <div class="alert">
                    <span class="alert-icon icon-[tabler--info-circle]"></span>
                    <div>
                        <p class="font-medium">What happens next?</p>
                        <ul class="text-xs mt-1 space-y-1">
                            <li>After completing setup, you'll choose a plan</li>
                            <li v-if="staffCount > 0">Team invitations will be sent to {{ staffCount }} member(s)</li>
                            <li>You can always change these settings later</li>
                        </ul>
                    </div>
                </div>

                <!-- Error message -->
                <div v-if="errorMessage" class="alert alert-error">
                    <span class="alert-icon icon-[tabler--alert-circle]"></span>
                    <p>{{ errorMessage }}</p>
                </div>

                <!-- Navigation -->
                <div class="flex justify-between mt-6">
                    <button type="button" class="btn btn-ghost" @click="$emit('prev')">
                        <span class="icon-[tabler--arrow-left] size-4"></span>
                        Back
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        :disabled="saving"
                        @click="handleComplete"
                    >
                        <span v-if="saving" class="loading loading-spinner loading-sm"></span>
                        <span v-else>
                            <span class="icon-[tabler--check] size-4 mr-1"></span>
                            Complete Setup
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../utils/api.js'
import toast from '../../utils/toast.js'

const props = defineProps({
    formData: { type: Object, required: true },
    options: { type: Object, default: () => ({}) },
    csrfToken: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['next', 'prev', 'update', 'complete'])

const appDomain = window.location.hostname.replace('www.', '') || 'fitcrm.com'

const isLive = ref(false)
const logoPreview = ref(null)
const uploading = ref(false)
const saving = ref(false)
const errorMessage = ref('')

const staffCount = computed(() => props.formData.staff_members?.length || 0)

// Initialize from form data
onMounted(() => {
    isLive.value = props.formData.is_live || false
    logoPreview.value = props.formData.logo_url || null
})

async function handleFileSelect(event) {
    const file = event.target.files[0]
    if (!file) return

    // Validate file
    if (!file.type.startsWith('image/')) {
        toast.error('Please select an image file.')
        return
    }
    if (file.size > 2 * 1024 * 1024) {
        toast.error('Image must be less than 2MB.')
        return
    }

    uploading.value = true
    errorMessage.value = ''

    try {
        const formData = new FormData()
        formData.append('logo', file)

        const response = await api.post('/api/v1/onboarding/booking-page/logo', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })

        logoPreview.value = response.data.data?.logo_url
        toast.success('Logo uploaded!')
        emit('update', { logo_url: logoPreview.value })
    } catch (error) {
        errorMessage.value = error.response?.data?.meta?.message || 'Failed to upload logo.'
    } finally {
        uploading.value = false
    }
}

async function handleComplete() {
    saving.value = true
    errorMessage.value = ''

    try {
        // Save booking page settings first
        await api.post('/api/v1/onboarding/booking-page', {
            is_live: isLive.value,
        })

        emit('update', { is_live: isLive.value })

        // Trigger completion
        emit('complete')
    } catch (error) {
        errorMessage.value = error.response?.data?.meta?.message || 'Failed to complete setup.'
        saving.value = false
    }
}
</script>
