<template>
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-xl">Staff Members</h2>
            <p class="text-base-content/70 text-sm mb-6">
                Add team members who will help run your studio. They'll receive invitations after you complete setup.
            </p>

            <!-- Staff list -->
            <div v-if="staffMembers.length > 0" class="mb-6">
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th class="w-16"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="member in staffMembers" :key="member.id">
                                <td class="font-medium">{{ member.name }}</td>
                                <td class="text-base-content/70">{{ member.email }}</td>
                                <td>
                                    <span class="badge badge-ghost badge-sm capitalize">{{ member.role }}</span>
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-ghost btn-xs btn-square text-error"
                                        @click="removeMember(member.id)"
                                        :disabled="removing === member.id"
                                    >
                                        <span v-if="removing === member.id" class="loading loading-spinner loading-xs"></span>
                                        <span v-else class="icon-[tabler--trash] size-4"></span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center py-8 text-base-content/50">
                <span class="icon-[tabler--users] size-12 mb-3 opacity-50"></span>
                <p>No staff members added yet</p>
                <p class="text-sm">Add team members or skip this step if you're a solo studio.</p>
            </div>

            <!-- Add staff form -->
            <div class="card card-bordered bg-base-200/50">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm mb-3">Add Team Member</h3>
                    <form @submit.prevent="addMember" class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="member_name" class="label label-text text-xs">Name</label>
                                <input
                                    type="text"
                                    id="member_name"
                                    v-model="newMember.name"
                                    class="input input-bordered input-sm w-full"
                                    placeholder="John Doe"
                                >
                            </div>
                            <div>
                                <label for="member_email" class="label label-text text-xs">Email</label>
                                <input
                                    type="email"
                                    id="member_email"
                                    v-model="newMember.email"
                                    class="input input-bordered input-sm w-full"
                                    placeholder="john@example.com"
                                >
                            </div>
                        </div>
                        <div class="flex items-end gap-3">
                            <div class="flex-1">
                                <label for="member_role" class="label label-text text-xs">Role</label>
                                <select
                                    id="member_role"
                                    v-model="newMember.role"
                                    class="select select-bordered select-sm w-full"
                                >
                                    <option value="staff">Staff</option>
                                    <option value="instructor">Instructor</option>
                                    <option value="manager">Manager</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <button
                                type="submit"
                                class="btn btn-primary btn-sm"
                                :disabled="!canAddMember || adding"
                            >
                                <span v-if="adding" class="loading loading-spinner loading-xs"></span>
                                <span v-else>
                                    <span class="icon-[tabler--plus] size-4"></span>
                                    Add
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Error message -->
            <div v-if="errorMessage" class="alert alert-error mt-4">
                <span class="alert-icon icon-[tabler--alert-circle]"></span>
                <p>{{ errorMessage }}</p>
            </div>

            <!-- Info note -->
            <div class="alert alert-info mt-4">
                <span class="alert-icon icon-[tabler--info-circle]"></span>
                <div>
                    <p class="font-medium">Invitations sent after setup</p>
                    <p class="text-xs">Team members will receive email invitations once you complete the onboarding process.</p>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex justify-between mt-6">
                <button type="button" class="btn btn-ghost" @click="$emit('prev')">
                    <span class="icon-[tabler--arrow-left] size-4"></span>
                    Back
                </button>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="btn btn-ghost"
                        @click="handleSkip"
                    >
                        Skip for now
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="handleNext"
                    >
                        Continue
                        <span class="icon-[tabler--arrow-right] size-4"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '../../utils/api.js'
import toast from '../../utils/toast.js'

const props = defineProps({
    formData: { type: Object, required: true },
    options: { type: Object, default: () => ({}) },
    csrfToken: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['next', 'prev', 'update'])

const staffMembers = ref([])
const newMember = reactive({
    name: '',
    email: '',
    role: 'staff',
})
const adding = ref(false)
const removing = ref(null)
const errorMessage = ref('')

// Initialize from form data
onMounted(() => {
    if (props.formData.staff_members) {
        staffMembers.value = [...props.formData.staff_members]
    }
})

const canAddMember = computed(() => {
    return newMember.name && newMember.email && newMember.role
})

async function addMember() {
    adding.value = true
    errorMessage.value = ''

    try {
        const response = await api.post('/api/v1/onboarding/staff', newMember)
        const addedMember = response.data.data
        staffMembers.value.push(addedMember)
        toast.success('Team member added!')

        // Reset form
        newMember.name = ''
        newMember.email = ''
        newMember.role = 'staff'

        // Update parent
        emit('update', { staff_members: staffMembers.value })
    } catch (error) {
        errorMessage.value = error.response?.data?.meta?.message || 'Failed to add team member.'
    } finally {
        adding.value = false
    }
}

async function removeMember(id) {
    removing.value = id
    errorMessage.value = ''

    try {
        await api.delete(`/api/v1/onboarding/staff/${id}`)
        staffMembers.value = staffMembers.value.filter(m => m.id !== id)
        toast.success('Team member removed.')
        emit('update', { staff_members: staffMembers.value })
    } catch (error) {
        errorMessage.value = error.response?.data?.meta?.message || 'Failed to remove team member.'
    } finally {
        removing.value = null
    }
}

function handleSkip() {
    emit('next')
}

function handleNext() {
    emit('update', { staff_members: staffMembers.value })
    emit('next')
}
</script>
