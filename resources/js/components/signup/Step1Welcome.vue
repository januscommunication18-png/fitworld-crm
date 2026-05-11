<template>
    <div class="card w-full">
        <div class="card-body text-center py-16">
            <div class="mb-6">
                <span class="icon-[tabler--rocket] size-16 text-primary"></span>
            </div>
            <h1 class="text-3xl font-bold mb-3">Welcome to FITStudioHQ</h1>
            <p class="text-base-content/60 mb-8 max-w-md mx-auto">
                Your all-in-one platform for studio management. Schedule classes, track attendance, handle payments, and grow your fitness business with ease.
            </p>
            <button type="button" class="btn btn-primary btn-lg" @click="$emit('next')">
                Get Started (Free)
                <span class="icon-[tabler--arrow-right] size-5"></span>
            </button>

            <div class="text-center mt-6">
                <p class="text-sm text-base-content/60">
                    Already have an account?
                    <a href="/login" class="link link-primary no-underline">Log in</a>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    formData: { type: Object, required: true },
    csrfToken: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

defineEmits(['next', 'prev', 'update'])
</script>
