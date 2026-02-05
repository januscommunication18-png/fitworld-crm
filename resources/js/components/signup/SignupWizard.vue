<template>
    <div class="w-full max-w-2xl mx-auto">
        <!-- Progress bar (steps 2-8) -->
        <ProgressBar v-if="currentStep >= 2 && currentStep <= 8" :current-step="currentStep" :total-steps="8" />

        <!-- Step components -->
        <transition name="fade" mode="out-in">
            <component
                :is="stepComponent"
                :key="currentStep"
                :form-data="formData"
                :csrf-token="csrfToken"
                :loading="loading"
                :errors="errors"
                @next="nextStep"
                @prev="prevStep"
                @update="updateFormData"
            />
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, shallowRef } from 'vue'
import ProgressBar from './ProgressBar.vue'
import Step1Welcome from './Step1Welcome.vue'
import Step2Account from './Step2Account.vue'
import Step3EmailVerification from './Step3EmailVerification.vue'
import Step4StudioBasics from './Step4StudioBasics.vue'
import Step5LocationSpace from './Step5LocationSpace.vue'
import Step6InstructorSetup from './Step6InstructorSetup.vue'
import Step7ClassSetup from './Step7ClassSetup.vue'
import Step8Payments from './Step8Payments.vue'
import Step9GoLive from './Step9GoLive.vue'

const props = defineProps({
    csrfToken: { type: String, default: '' },
})

const currentStep = ref(1)
const loading = ref(false)
const errors = ref({})

const formData = ref({
    // Step 2: Account
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    is_studio_owner: true,
    // Step 4: Studio
    studio_name: '',
    studio_types: [],
    city: '',
    timezone: '',
    subdomain: '',
    // Step 5: Location
    address: '',
    rooms: 1,
    default_capacity: 20,
    room_capacities: [],
    amenities: [],
    // Step 6: Instructors
    add_self_as_instructor: true,
    instructors: [],
    // Step 7: Class
    skip_class_setup: false,
    class_name: '',
    class_type: '',
    class_duration: 60,
    class_capacity: 20,
    class_instructor_id: null,
    class_price: null,
    // Step 8: Payments
    skip_payments: false,
    stripe_connected: false,
})

const steps = {
    1: Step1Welcome,
    2: Step2Account,
    3: Step3EmailVerification,
    4: Step4StudioBasics,
    5: Step5LocationSpace,
    6: Step6InstructorSetup,
    7: Step7ClassSetup,
    8: Step8Payments,
    9: Step9GoLive,
}

const stepComponent = computed(() => steps[currentStep.value])

function nextStep() {
    if (currentStep.value < 9) {
        errors.value = {}
        currentStep.value++
    }
}

function prevStep() {
    if (currentStep.value > 1) {
        errors.value = {}
        currentStep.value--
    }
}

function updateFormData(data) {
    Object.assign(formData.value, data)
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
