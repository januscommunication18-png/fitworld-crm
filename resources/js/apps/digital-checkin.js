import { createApp } from 'vue'
import DigitalCheckin from '../components/DigitalCheckin.vue'

const el = document.getElementById('digital-checkin-app')

if (el) {
    createApp(DigitalCheckin, {
        csrf: el.dataset.csrf || '',
        resolveUrl: el.dataset.resolveUrl || '',
        confirmUrl: el.dataset.confirmUrl || '',
        searchUrl: el.dataset.searchUrl || '',
        allowOverride: el.dataset.allowOverride === 'true',
        qrEnabled: el.dataset.qrEnabled === 'true',
    }).mount('#digital-checkin-app')
}
