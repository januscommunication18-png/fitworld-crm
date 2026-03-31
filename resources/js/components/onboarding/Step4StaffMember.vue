<template>
    <div class="card">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-2">Team Members</h2>
            <p class="text-base-content/70 mb-6">
                Add team members who will help manage your studio. You can skip this step and add them later.
            </p>

            <div class="space-y-4">
                <!-- Info about owner -->
                <div class="alert bg-base-200">
                    <span class="icon-[tabler--info-circle] size-5 text-primary"></span>
                    <span>You are automatically added as the owner of this studio.</span>
                </div>

                <!-- Staff Members List -->
                <div v-for="(member, index) in staffMembers" :key="index" class="border border-base-300 rounded-lg p-4">
                    <div class="flex items-start justify-between mb-3">
                        <h4 class="font-medium">Team Member {{ index + 1 }}</h4>
                        <button
                            type="button"
                            class="btn btn-ghost btn-sm btn-square text-error"
                            @click="removeMember(index)"
                        >
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="form-control">
                            <label class="label py-1" :for="`first_name_${index}`">
                                <span class="label-text text-sm">First Name</span>
                            </label>
                            <input
                                :id="`first_name_${index}`"
                                v-model="member.first_name"
                                type="text"
                                class="input input-bordered input-sm"
                                placeholder="First name"
                                required
                            >
                        </div>

                        <div class="form-control">
                            <label class="label py-1" :for="`last_name_${index}`">
                                <span class="label-text text-sm">Last Name</span>
                            </label>
                            <input
                                :id="`last_name_${index}`"
                                v-model="member.last_name"
                                type="text"
                                class="input input-bordered input-sm"
                                placeholder="Last name"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <div class="form-control">
                            <label class="label py-1" :for="`email_${index}`">
                                <span class="label-text text-sm">Email Address</span>
                            </label>
                            <input
                                :id="`email_${index}`"
                                v-model="member.email"
                                type="email"
                                class="input input-bordered input-sm"
                                placeholder="email@example.com"
                                required
                            >
                        </div>

                        <div class="form-control">
                            <label class="label py-1" :for="`role_${index}`">
                                <span class="label-text text-sm">Role</span>
                            </label>
                            <select
                                :id="`role_${index}`"
                                v-model="member.role"
                                class="select select-bordered select-sm"
                                required
                            >
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                                <option value="instructor">Instructor</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Add Member Button -->
                <button
                    type="button"
                    class="btn btn-outline btn-primary w-full"
                    @click="addMember"
                    :disabled="staffMembers.length >= 10"
                >
                    <span class="icon-[tabler--plus] size-4 mr-1"></span>
                    Add Team Member
                </button>

                <p v-if="staffMembers.length > 0" class="text-sm text-base-content/60 text-center">
                    <span class="icon-[tabler--info-circle] size-4 mr-1 inline-block"></span>
                    Invitations will be sent after you complete the onboarding.
                </p>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-6 pt-4 border-t border-base-300">
                <button type="button" class="btn btn-ghost" @click="$emit('prev')">
                    <span class="icon-[tabler--arrow-left] size-4 mr-1"></span>
                    Back
                </button>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="btn btn-outline"
                        @click="handleSkip"
                    >
                        Skip for now
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        :class="{ 'loading': loading }"
                        :disabled="loading || (staffMembers.length > 0 && !isValid)"
                        @click="handleSubmit"
                    >
                        Continue
                        <span class="icon-[tabler--arrow-right] size-4 ml-1"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
    formData: { type: Object, default: () => ({}) },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['next', 'prev', 'update'])

const staffMembers = ref(props.formData.staff_members || [])

const isValid = computed(() => {
    return staffMembers.value.every(member =>
        member.first_name &&
        member.email &&
        member.role &&
        isValidEmail(member.email)
    )
})

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

function addMember() {
    staffMembers.value.push({
        first_name: '',
        last_name: '',
        email: '',
        role: 'instructor',
    })
}

function removeMember(index) {
    staffMembers.value.splice(index, 1)
}

function handleSubmit() {
    emit('update', { staff_members: staffMembers.value })
    emit('next', { staff_members: staffMembers.value })
}

function handleSkip() {
    emit('update', { staff_members: [] })
    emit('next', { staff_members: [] })
}

// Sync form data when props change
watch(() => props.formData.staff_members, (newMembers) => {
    if (newMembers) {
        staffMembers.value = [...newMembers]
    }
}, { deep: true })
</script>
