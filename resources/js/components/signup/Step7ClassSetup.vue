<template>
    <div class="card">
        <div class="card-body">
            <h2 class="text-2xl font-bold mb-1">Set Up Your First Class</h2>
            <p class="text-base-content/60 mb-6">Create a class now or add them later.</p>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <label class="custom-option flex flex-row items-center gap-3 p-4 mb-4">
                    <input type="checkbox" class="checkbox checkbox-primary" v-model="localData.skip_class_setup" />
                    <span class="label-text">
                        <span class="text-base font-medium">I'll add classes later</span>
                    </span>
                </label>

                <template v-if="!localData.skip_class_setup">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text" for="class_name">Class Name</label>
                            <input id="class_name" type="text" class="input w-full" v-model="localData.class_name"
                                placeholder="e.g. Morning Vinyasa" />
                        </div>
                        <div>
                            <label class="label-text" for="class_type">Class Type</label>
                            <input id="class_type" type="text" class="input w-full" v-model="localData.class_type"
                                placeholder="e.g. Yoga, Pilates" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="label-text" for="class_duration">Duration (min)</label>
                            <input id="class_duration" type="number" class="input w-full" v-model.number="localData.class_duration"
                                min="15" max="180" step="15" />
                        </div>
                        <div>
                            <label class="label-text" for="class_capacity">Capacity</label>
                            <input id="class_capacity" type="number" class="input w-full" v-model.number="localData.class_capacity"
                                min="1" max="200" />
                        </div>
                        <div>
                            <label class="label-text" for="class_price">Price (optional)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input id="class_price" type="number" class="input grow" v-model.number="localData.class_price"
                                    min="0" step="0.01" placeholder="0.00" />
                            </div>
                        </div>
                    </div>
                </template>

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
    skip_class_setup: props.formData.skip_class_setup,
    class_name: props.formData.class_name,
    class_type: props.formData.class_type,
    class_duration: props.formData.class_duration,
    class_capacity: props.formData.class_capacity,
    class_price: props.formData.class_price,
})

function handleSubmit() {
    emit('update', { ...localData })
    emit('next')
}
</script>
