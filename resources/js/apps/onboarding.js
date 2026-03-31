import { createApp } from 'vue'
import OnboardingWizard from '../components/onboarding/OnboardingWizard.vue'

const el = document.getElementById('onboarding-app')

if (el) {
    createApp(OnboardingWizard, {
        csrfToken: el.dataset.csrfToken || '',
        emailVerified: el.dataset.emailVerified === 'true',
        phoneVerified: el.dataset.phoneVerified === 'true',
        techSupportRequested: el.dataset.techSupportRequested === 'true',
        techSupportPending: el.dataset.techSupportPending === 'true',
        currentStep: parseInt(el.dataset.currentStep) || 1,
        studioName: el.dataset.studioName || '',
        userEmail: el.dataset.userEmail || '',
        userName: el.dataset.userName || '',
    }).mount('#onboarding-app')
}
