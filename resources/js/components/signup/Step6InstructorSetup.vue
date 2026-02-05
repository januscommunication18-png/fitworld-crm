<template>
    <div class="card">
        <div class="card-body">
            <h2 class="text-2xl font-bold mb-1">Instructor Setup</h2>
            <p class="text-base-content/60 mb-6">Who teaches at your studio?</p>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <label class="custom-option flex flex-row items-center gap-3 p-4">
                    <input type="checkbox" class="checkbox checkbox-primary" v-model="localData.add_self_as_instructor" />
                    <span class="label-text">
                        <span class="text-base font-medium">Add myself as an instructor</span>
                        <span class="block text-base-content/60 text-sm">{{ formData.first_name }} {{ formData.last_name }}</span>
                    </span>
                </label>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="label-text font-medium">Additional Instructors</label>
                        <button type="button" class="btn btn-soft btn-sm" @click="addInstructor">
                            <span class="icon-[tabler--plus] size-4"></span> Add
                        </button>
                    </div>

                    <div v-if="localData.instructors.length === 0" class="text-center py-6 text-base-content/40 text-sm">
                        No additional instructors yet. You can add more anytime.
                    </div>

                    <div v-for="(instructor, index) in localData.instructors" :key="index" class="flex gap-3 mb-3">
                        <div class="flex-1">
                            <input type="text" class="input w-full" v-model="instructor.name"
                                placeholder="Instructor name" />
                        </div>
                        <div class="flex-1">
                            <input type="email" class="input w-full" v-model="instructor.email"
                                placeholder="Email address" />
                        </div>
                        <button type="button" class="btn btn-ghost btn-square btn-sm text-error" @click="removeInstructor(index)">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                </div>

                <div class="flex justify-between pt-4">
                    <button type="button" class="btn btn-ghost" @click="$emit('prev')">
                        <span class="icon-[tabler--arrow-left] size-4"></span> Back
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Continue <span class="icon-[tabler--arrow-right] size-4"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
    formData: { type: Object, required: true },
    csrfToken: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['next', 'prev', 'update'])

const localData = reactive({
    add_self_as_instructor: props.formData.add_self_as_instructor,
    instructors: props.formData.instructors.map(i => ({ ...i })),
})

function addInstructor() {
    localData.instructors.push({ name: '', email: '' })
}

function removeInstructor(index) {
    localData.instructors.splice(index, 1)
}

function handleSubmit() {
    emit('update', { ...localData, instructors: localData.instructors.filter(i => i.name) })
    emit('next')
}
</script>
