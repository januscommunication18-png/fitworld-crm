import { createApp } from 'vue'
import SignupWizard from '../components/signup/SignupWizard.vue'

const el = document.getElementById('signup-app')

if (el) {
    createApp(SignupWizard, {
        csrfToken: el.dataset.csrfToken || '',
        smartyKey: el.dataset.smartyKey || '',
        authenticated: el.dataset.authenticated === 'true',
        emailVerified: el.dataset.emailVerified === 'true',
    }).mount('#signup-app')
}
