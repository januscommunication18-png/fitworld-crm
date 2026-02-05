<template>
    <div class="card w-[500px]">
        <div class="card-body text-center py-16">
            <div class="mb-6">
                <span class="icon-[tabler--rocket] size-16 text-primary"></span>
            </div>
            <h1 class="text-3xl font-bold mb-3">Welcome to FitCRM</h1>
            <p class="text-base-content/60 mb-8 max-w-md mx-auto">
                Set up your studio in minutes. Manage classes, students, and payments — all in one place.
            </p>
            <div class="flex flex-col items-center gap-3">
                <button type="button" class="btn btn-primary btn-lg" @click="$emit('next')">
                    Get Started (Free)
                    <span class="icon-[tabler--arrow-right] size-5"></span>
                </button>
                <a href="/login" class="link link-primary text-sm no-underline">Already have an account? Log in</a>
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
