<script setup>
import { ref, reactive, computed, onBeforeUnmount, nextTick } from 'vue'
import axios from 'axios'
import { toast } from '../utils/toast'
import { Html5Qrcode } from 'html5-qrcode'

const props = defineProps({
    csrf: String,
    resolveUrl: String,
    confirmUrl: String,
    searchUrl: String,
    allowOverride: Boolean,
    qrEnabled: Boolean,
})

axios.defaults.headers.common['X-CSRF-TOKEN'] = props.csrf
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

const mode = ref(props.qrEnabled ? 'scan' : 'manual') // 'scan' | 'manual'
const loading = ref(false)
const scanning = ref(false)
const result = reactive({ client: null, options: [], auto: false, checkedIn: null })

// --- Manual lookup ---
const query = ref('')
const searchResults = ref([])
let searchTimer = null

function onSearchInput() {
    clearTimeout(searchTimer)
    if (query.value.trim().length < 2) {
        searchResults.value = []
        return
    }
    searchTimer = setTimeout(async () => {
        try {
            const { data } = await axios.get(props.searchUrl, { params: { q: query.value.trim() } })
            searchResults.value = data.results || []
        } catch (e) {
            searchResults.value = []
        }
    }, 300)
}

function pickClient(client) {
    searchResults.value = []
    query.value = ''
    resolve({ client_id: client.id })
}

// --- Scanner ---
let html5Qr = null

async function startScanner() {
    if (scanning.value) return
    await nextTick()
    try {
        html5Qr = new Html5Qrcode('reader')
        scanning.value = true
        await html5Qr.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 240, height: 240 } },
            (decodedText) => onScan(decodedText),
            () => {} // ignore per-frame decode errors
        )
    } catch (e) {
        scanning.value = false
        toast.error('Unable to start the camera. Use manual lookup instead.')
        mode.value = 'manual'
    }
}

async function stopScanner() {
    if (html5Qr && scanning.value) {
        try { await html5Qr.stop() } catch (e) { /* noop */ }
        try { html5Qr.clear() } catch (e) { /* noop */ }
    }
    scanning.value = false
    html5Qr = null
}

let lastScan = 0
async function onScan(token) {
    const now = Date.now()
    if (now - lastScan < 2500) return // debounce repeated frames
    lastScan = now
    await stopScanner()
    resolve({ token })
}

function switchMode(next) {
    if (next === mode.value) return
    if (next === 'manual') stopScanner()
    mode.value = next
}

// --- Resolve + confirm ---
async function resolve(body) {
    loading.value = true
    clearResult()
    try {
        const { data } = await axios.post(props.resolveUrl, body)
        result.client = data.client
        result.options = data.options || []
        result.auto = data.auto || false
        result.checkedIn = data.checked_in || null
        if (data.auto && data.checked_in?.success) {
            toast.success(data.checked_in.message || 'Checked in!')
        } else if (data.checked_in && !data.checked_in.success) {
            toast.error(data.checked_in.message || 'Check-in failed.')
        }
    } catch (e) {
        toast.error(e.response?.data?.message || 'QR not recognized.')
    } finally {
        loading.value = false
    }
}

async function confirm(option, override = false) {
    loading.value = true
    try {
        const { data } = await axios.post(props.confirmUrl, {
            client_id: result.client.id,
            type: option.type,
            ref_id: option.ref_id,
            override,
        })
        if (data.success) {
            toast.success(data.message || 'Checked in!')
        } else {
            toast.error(data.message || 'Check-in failed.')
        }
        result.options = data.options || result.options
    } catch (e) {
        toast.error(e.response?.data?.message || 'Check-in failed.')
    } finally {
        loading.value = false
    }
}

function clearResult() {
    result.client = null
    result.options = []
    result.auto = false
    result.checkedIn = null
}

function reset() {
    clearResult()
    if (mode.value === 'scan') startScanner()
}

const reasonLabels = {
    too_early: 'Check-in not open yet',
    too_late: 'Window closed',
    already: 'Already checked in',
    not_confirmed: 'Not confirmed',
    unpaid: 'Payment pending',
    no_credits: 'No credits left',
    disabled: 'Unavailable',
    no_session_time: 'No session time',
}

const overridableReasons = ['unpaid', 'too_early', 'too_late']
function canOverride(option) {
    return props.allowOverride && !option.eligible && !option.already
        && overridableReasons.includes(option.reason)
}

onBeforeUnmount(() => stopScanner())
</script>

<template>
    <div class="max-w-2xl mx-auto">
        <!-- Mode tabs -->
        <div class="flex gap-2 mb-5">
            <button v-if="qrEnabled" type="button" class="btn btn-sm"
                    :class="mode === 'scan' ? 'btn-primary' : 'btn-soft'"
                    @click="switchMode('scan')">
                <span class="icon-[tabler--qrcode] size-4"></span> Scan QR
            </button>
            <button type="button" class="btn btn-sm"
                    :class="mode === 'manual' ? 'btn-primary' : 'btn-soft'"
                    @click="switchMode('manual')">
                <span class="icon-[tabler--search] size-4"></span> Manual lookup
            </button>
        </div>

        <!-- Scanner -->
        <div v-show="mode === 'scan'" class="card bg-base-100 shadow-sm mb-5">
            <div class="card-body items-center text-center">
                <div id="reader" class="w-full max-w-sm mx-auto rounded-lg overflow-hidden"></div>
                <button v-if="!scanning" type="button" class="btn btn-primary mt-4" @click="startScanner">
                    <span class="icon-[tabler--camera] size-5"></span> Start camera
                </button>
                <p v-else class="text-sm text-base-content/60 mt-3">Point the camera at the client's QR code.</p>
            </div>
        </div>

        <!-- Manual lookup -->
        <div v-show="mode === 'manual'" class="card bg-base-100 shadow-sm mb-5">
            <div class="card-body">
                <label class="label-text" for="client-search">Search client by name, email, or phone</label>
                <input id="client-search" v-model="query" type="text" class="input w-full mt-1"
                       placeholder="e.g. Sarah Johnson" autocomplete="off" @input="onSearchInput">
                <ul v-if="searchResults.length" class="menu w-full mt-2 border border-base-200 rounded-box">
                    <li v-for="c in searchResults" :key="c.id">
                        <a @click="pickClient(c)">
                            <span class="avatar avatar-placeholder">
                                <span class="bg-primary/10 text-primary rounded-full w-8 h-8 text-xs flex items-center justify-center">{{ c.initials }}</span>
                            </span>
                            <span class="flex flex-col">
                                <span class="font-medium">{{ c.full_name }}</span>
                                <span class="text-xs text-base-content/60">{{ c.email }}</span>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-6">
            <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>

        <!-- Result -->
        <div v-if="result.client && !loading" class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <span class="avatar avatar-placeholder">
                        <span class="bg-primary/10 text-primary rounded-full w-12 h-12 flex items-center justify-center font-semibold">{{ result.client.initials }}</span>
                    </span>
                    <div>
                        <h3 class="text-lg font-semibold">{{ result.client.full_name }}</h3>
                        <p class="text-sm text-base-content/60">{{ result.client.email }}</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-text ms-auto" @click="reset">
                        <span class="icon-[tabler--x] size-4"></span> Done
                    </button>
                </div>

                <div v-if="result.auto && result.checkedIn?.success"
                     class="alert alert-success alert-soft mb-4">
                    <span class="icon-[tabler--circle-check] size-5"></span>
                    <span>{{ result.checkedIn.message }}</span>
                </div>

                <p v-if="!result.options.length" class="text-sm text-base-content/60 py-4 text-center">
                    No active check-in options found for today.
                </p>

                <div v-for="option in result.options" :key="option.type + option.ref_id"
                     class="flex items-center gap-3 p-3 rounded-lg border mb-2"
                     :class="option.already ? 'border-success/40 bg-success/5'
                        : option.eligible ? 'border-base-200' : 'border-base-200 opacity-70'">
                    <span class="icon-[tabler--calendar-event] size-5 text-base-content/50"></span>
                    <div class="flex-1">
                        <p class="font-medium">{{ option.label }}</p>
                        <p class="text-xs text-base-content/60">{{ option.subtitle }}</p>
                    </div>
                    <span v-if="option.already" class="badge badge-success badge-soft">Checked in</span>
                    <button v-else-if="option.eligible" type="button" class="btn btn-sm btn-primary"
                            @click="confirm(option)">Check In</button>
                    <template v-else>
                        <span class="badge badge-soft badge-sm">{{ reasonLabels[option.reason] || 'Not eligible' }}</span>
                        <button v-if="canOverride(option)" type="button" class="btn btn-sm btn-warning btn-soft"
                                @click="confirm(option, true)">Override</button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
