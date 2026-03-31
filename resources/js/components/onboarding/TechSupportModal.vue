<template>
    <div class="modal modal-open">
        <div class="modal-box">
            <button
                type="button"
                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                @click="$emit('close')"
            >
                <span class="icon-[tabler--x] size-5"></span>
            </button>

            <h3 class="text-lg font-bold mb-4">Request Technical Support</h3>
            <p class="text-base-content/70 mb-6">
                Our team will help you complete your studio setup. Fill out the form below and we'll be in touch shortly.
            </p>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label" for="first_name">
                            <span class="label-text">First Name <span class="text-error">*</span></span>
                        </label>
                        <input
                            id="first_name"
                            v-model="form.first_name"
                            type="text"
                            class="input input-bordered"
                            required
                        >
                    </div>

                    <div class="form-control">
                        <label class="label" for="last_name">
                            <span class="label-text">Last Name <span class="text-error">*</span></span>
                        </label>
                        <input
                            id="last_name"
                            v-model="form.last_name"
                            type="text"
                            class="input input-bordered"
                            required
                        >
                    </div>
                </div>

                <div class="form-control">
                    <label class="label" for="email">
                        <span class="label-text">Email Address <span class="text-error">*</span></span>
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="input input-bordered"
                        required
                    >
                </div>

                <div class="form-control">
                    <label class="label" for="phone">
                        <span class="label-text">Phone Number</span>
                    </label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        class="input input-bordered"
                        placeholder="(555) 123-4567"
                    >
                </div>

                <div class="form-control">
                    <label class="label" for="note">
                        <span class="label-text">How can we help?</span>
                    </label>
                    <textarea
                        id="note"
                        v-model="form.note"
                        class="textarea textarea-bordered h-24"
                        placeholder="Tell us about your studio and what help you need..."
                    ></textarea>
                </div>

                <div class="modal-action">
                    <button
                        type="button"
                        class="btn btn-ghost"
                        @click="$emit('close')"
                        :disabled="loading"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary"
                        :class="{ 'loading': loading }"
                        :disabled="loading || !isValid"
                    >
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
        <div class="modal-backdrop bg-base-content/20" @click="$emit('close')"></div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    userName: { type: String, default: '' },
    userEmail: { type: String, default: '' },
    loading: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'submit'])

// Parse the user name into first and last name
const nameParts = props.userName.trim().split(' ')
const firstName = nameParts[0] || ''
const lastName = nameParts.slice(1).join(' ') || ''

const form = ref({
    first_name: firstName,
    last_name: lastName,
    email: props.userEmail,
    phone: '',
    note: '',
})

const isValid = computed(() => {
    return form.value.first_name &&
           form.value.last_name &&
           form.value.email &&
           isValidEmail(form.value.email)
})

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

function handleSubmit() {
    emit('submit', { ...form.value })
}
</script>
