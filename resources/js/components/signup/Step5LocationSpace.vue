<template>
    <div class="card w-[500px]">
        <div class="card-body">
            <h2 class="text-2xl font-bold mb-1">Location &amp; Space</h2>
            <p class="text-base-content/60 mb-6">Where is your studio and how is it set up?</p>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="label-text" for="address">Studio Address</label>
                    <AddressAutocomplete
                        v-model="localData.address"
                        :smarty-key="smartyKey"
                        input-id="address"
                        :input-class="{ 'input-error': errors.address }"
                        placeholder="Start typing your address..."
                        @select="handleAddressSelect"
                    />
                    <p v-if="errors.address" class="text-error text-xs mt-1">{{ errors.address[0] }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="rooms">Number of Rooms</label>
                        <input id="rooms" type="number" class="input w-full" :class="{ 'input-error': errors.rooms }"
                            v-model.number="localData.rooms" min="1" max="20" />
                        <p v-if="errors.rooms" class="text-error text-xs mt-1">{{ errors.rooms[0] }}</p>
                    </div>
                    <div>
                        <label class="label-text" for="default_capacity">Default Capacity</label>
                        <input id="default_capacity" type="number" class="input w-full" :class="{ 'input-error': errors.default_capacity }"
                            v-model.number="localData.default_capacity" min="1" max="200" />
                        <p class="text-xs mt-1" :class="errors.default_capacity ? 'text-error' : 'text-base-content/50'">
                            {{ errors.default_capacity ? errors.default_capacity[0] : 'Max students per class' }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="label-text">Amenities</label>
                    <p class="text-xs text-base-content/50 mb-2">Select all that your studio offers</p>
                    <div class="flex flex-wrap gap-2">
                        <label v-for="amenity in amenityOptions" :key="amenity"
                            class="custom-option flex flex-row items-center gap-2 px-3 py-2">
                            <input type="checkbox" class="checkbox checkbox-primary checkbox-sm"
                                :value="amenity" v-model="localData.amenities" />
                            <span class="label-text text-sm">{{ amenity }}</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-between pt-4">
                    <button type="button" class="btn btn-ghost" @click="$emit('prev')">
                        <span class="icon-[tabler--arrow-left] size-4"></span> Back
                    </button>
                    <button type="submit" class="btn btn-primary" :disabled="loading">
                        <span v-if="loading" class="loading loading-spinner loading-xs"></span>
                        <template v-else>Continue <span class="icon-[tabler--arrow-right] size-4"></span></template>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'
import AddressAutocomplete from './AddressAutocomplete.vue'

const props = defineProps({
    formData: { type: Object, required: true },
    csrfToken: { type: String, default: '' },
    smartyKey: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['next', 'prev', 'update'])

const amenityOptions = ['Changing Rooms', 'Showers', 'Parking', 'Mat Rental', 'Towel Service', 'Water Station', 'Wi-Fi', 'Sound System']

const localData = reactive({
    address: props.formData.address,
    city: props.formData.city || '',
    state: props.formData.state || '',
    zipcode: props.formData.zipcode || '',
    rooms: props.formData.rooms,
    default_capacity: props.formData.default_capacity,
    amenities: [...props.formData.amenities],
})

function handleAddressSelect(addressData) {
    localData.city = addressData.city
    localData.state = addressData.state
    localData.zipcode = addressData.zipcode
}

function handleSubmit() {
    emit('update', { ...localData })
    emit('next')
}
</script>
