import { createApp } from 'vue'
import OnboardingWizard from '../components/onboarding/OnboardingWizard.vue'

const el = document.getElementById('onboarding-app')

if (el) {
    createApp(OnboardingWizard, {
        csrfToken: el.dataset.csrfToken || '',
        smartyKey: el.dataset.smartyKey || '',
        userId: parseInt(el.dataset.userId || '0'),
        hostId: parseInt(el.dataset.hostId || '0'),
        emailVerified: el.dataset.emailVerified === 'true',
        phoneVerified: el.dataset.phoneVerified === 'true',
    }).mount('#onboarding-app')
}
