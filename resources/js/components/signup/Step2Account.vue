<template>
    <div class="card">
        <div class="card-body">
            <h2 class="text-2xl font-bold mb-1">Create your account</h2>
            <p class="text-base-content/60 mb-6">Let's start with the basics.</p>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="first_name">First Name</label>
                        <input id="first_name" type="text" class="input w-full" v-model="localData.first_name"
                            placeholder="Jane" required />
                    </div>
                    <div>
                        <label class="label-text" for="last_name">Last Name</label>
                        <input id="last_name" type="text" class="input w-full" v-model="localData.last_name"
                            placeholder="Smith" required />
                    </div>
                </div>

                <div>
                    <label class="label-text" for="email">Email</label>
                    <input id="email" type="email" class="input w-full" v-model="localData.email"
                        placeholder="jane@yourstudio.com" required />
                </div>

                <div>
                    <label class="label-text" for="password">Password</label>
                    <input id="password" type="password" class="input w-full" v-model="localData.password"
                        placeholder="Create a strong password" required />
                    <PasswordStrength :password="localData.password" />
                </div>

                <label class="custom-option flex flex-row items-start gap-3">
                    <input type="checkbox" class="checkbox checkbox-primary mt-1" v-model="localData.is_studio_owner" />
                    <span class="label-text">
                        <span class="text-base font-medium">I own or manage a fitness studio</span>
                    </span>
                </label>

                <!-- Google SSO placeholder -->
                <div class="divider text-base-content/40 text-xs">or</div>
                <button type="button" class="btn btn-soft w-full" disabled>
                    <span class="icon-[tabler--brand-google] size-5"></span>
                    Continue with Google
                    <span class="badge badge-xs badge-soft ms-2">Coming Soon</span>
                </button>

                <div class="flex justify-between pt-4">
                    <button type="button" class="btn btn-ghost" @click="$emit('prev')">
                        <span class="icon-[tabler--arrow-left] size-4"></span> Back
                    </button>
                    <button type="submit" class="btn btn-primary" :disabled="!isValid">
                        Continue <span class="icon-[tabler--arrow-right] size-4"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed } from 'vue'
import PasswordStrength from './PasswordStrength.vue'

const props = defineProps({
    formData: { type: Object, required: true },
    csrfToken: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['next', 'prev', 'update'])

const localData = reactive({
    first_name: props.formData.first_name,
    last_name: props.formData.last_name,
    email: props.formData.email,
    password: props.formData.password,
    is_studio_owner: props.formData.is_studio_owner,
})

const isValid = computed(() => {
    return localData.first_name && localData.last_name && localData.email && localData.password.length >= 8
})

function handleSubmit() {
    if (!isValid.value) return
    emit('update', { ...localData })
    emit('next')
}
</script>
