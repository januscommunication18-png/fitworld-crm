<template>
    <div class="card">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-2">Booking Page</h2>
            <p class="text-base-content/70 mb-6">Configure your public booking page settings.</p>

            <div class="space-y-6">
                <!-- Published Toggle -->
                <div class="border border-base-300 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium">Publish Booking Page</h3>
                            <p class="text-sm text-base-content/60 mt-1">
                                Make your booking page visible to the public
                            </p>
                        </div>
                        <input
                            type="checkbox"
                            class="toggle toggle-primary"
                            :checked="isPublished"
                            @change="togglePublished"
                        >
                    </div>
                    <div v-if="isPublished" class="mt-3 p-3 bg-success/10 rounded-lg">
                        <div class="flex items-center gap-2 text-success">
                            <span class="icon-[tabler--check] size-5"></span>
                            <span class="font-medium">Your booking page is live!</span>
                        </div>
                        <p class="text-sm text-base-content/60 mt-1">
                            Clients can now book classes and services at your studio.
                        </p>
                    </div>
                </div>

                <!-- Logo Upload -->
                <div class="border border-base-300 rounded-lg p-4">
                    <h3 class="font-medium mb-3">Studio Logo</h3>
                    <div class="flex items-start gap-4">
                        <!-- Logo Preview -->
                        <div class="w-24 h-24 rounded-lg bg-base-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                            <img
                                v-if="logoPreview || formData.logo_url"
                                :src="logoPreview || formData.logo_url"
                                alt="Studio Logo"
                                class="w-full h-full object-cover"
                            >
                            <span v-else class="icon-[tabler--photo] size-8 text-base-content/30"></span>
                        </div>

                        <div class="flex-1">
                            <p class="text-sm text-base-content/60 mb-3">
                                Upload your studio logo. Recommended size: 200x200px or larger.
                            </p>
                            <div class="flex gap-2">
                                <label class="btn btn-outline btn-sm">
                                    <span class="icon-[tabler--upload] size-4 mr-1"></span>
                                    Upload Logo
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml"
                                        class="hidden"
                                        @change="handleLogoUpload"
                                        :disabled="uploading"
                                    >
                                </label>
                                <button
                                    v-if="logoPreview || formData.logo_url"
                                    type="button"
                                    class="btn btn-ghost btn-sm text-error"
                                    @click="removeLogo"
                                >
                                    Remove
                                </button>
                            </div>
                            <p v-if="uploadError" class="text-sm text-error mt-2">{{ uploadError }}</p>
                        </div>
                    </div>
                </div>

                <!-- Preview Link -->
                <div class="alert bg-base-200">
                    <span class="icon-[tabler--external-link] size-5"></span>
                    <div>
                        <p class="font-medium">Preview your booking page</p>
                        <p class="text-sm text-base-content/60">
                            After completing setup, your page will be available at:
                            <br>
                            <span class="font-mono">{{ bookingPageUrl }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-6 pt-4 border-t border-base-300">
                <button type="button" class="btn btn-ghost" @click="$emit('prev')">
                    <span class="icon-[tabler--arrow-left] size-4 mr-1"></span>
                    Back
                </button>
                <button
                    type="button"
                    class="btn btn-primary"
                    :class="{ 'loading': loading }"
                    :disabled="loading"
                    @click="handleComplete"
                >
                    <span class="icon-[tabler--check] size-4 mr-1"></span>
                    Complete Setup
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import api, { ensureCsrf } from '../../utils/api.js'
import toast from '../../utils/toast.js'

const props = defineProps({
    formData: { type: Object, default: () => ({}) },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['next', 'prev', 'update', 'complete'])

const isPublished = ref(props.formData.booking_page_status === 'published')
const logoPreview = ref(null)
const uploading = ref(false)
const uploadError = ref('')

const bookingPageUrl = computed(() => {
    const subdomain = props.formData.subdomain || 'your-studio'
    const domain = window.location.hostname.replace('projectfit.local', 'projectfit.local:8888')
    return `https://${subdomain}.${domain}`
})

function togglePublished() {
    isPublished.value = !isPublished.value
    emit('update', {
        booking_page_status: isPublished.value ? 'published' : 'draft'
    })
}

async function handleLogoUpload(event) {
    const file = event.target.files[0]
    if (!file) return

    // Validate file size (2MB max)
    if (file.size > 2 * 1024 * 1024) {
        uploadError.value = 'File size must be less than 2MB'
        return
    }

    // Validate file type
    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']
    if (!validTypes.includes(file.type)) {
        uploadError.value = 'Please upload a valid image file (JPEG, PNG, GIF, WebP, or SVG)'
        return
    }

    uploadError.value = ''
    uploading.value = true

    // Show preview immediately
    const reader = new FileReader()
    reader.onload = (e) => {
        logoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)

    // Upload file
    try {
        await ensureCsrf()
        const formData = new FormData()
        formData.append('logo', file)

        const res = await api.post('/onboarding/booking-page/logo', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })

        emit('update', { logo_url: res.data.data.logo_url })
        toast.success('Logo uploaded successfully!')
    } catch (err) {
        uploadError.value = err.response?.data?.message || 'Failed to upload logo'
        logoPreview.value = null
    } finally {
        uploading.value = false
    }
}

function removeLogo() {
    logoPreview.value = null
    emit('update', { logo_url: null })
}

async function handleComplete() {
    emit('update', {
        booking_page_status: isPublished.value ? 'published' : 'draft'
    })
    emit('complete')
}
</script>
