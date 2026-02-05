import axios from 'axios'

const api = axios.create({
    baseURL: '/api/v1',
    withCredentials: true,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
})

// Read CSRF token from XSRF-TOKEN cookie (stays current after session regeneration)
function getCookieValue(name) {
    const match = document.cookie.match(new RegExp('(^|;\\s*)' + name + '=([^;]*)'))
    return match ? decodeURIComponent(match[2]) : null
}

api.interceptors.request.use(config => {
    // Prefer XSRF-TOKEN cookie (always current), fall back to meta tag
    const cookieToken = getCookieValue('XSRF-TOKEN')
    if (cookieToken) {
        config.headers['X-XSRF-TOKEN'] = cookieToken
    } else {
        const metaToken = document.querySelector('meta[name="csrf-token"]')?.content
        if (metaToken) {
            config.headers['X-CSRF-TOKEN'] = metaToken
        }
    }
    return config
})

// Handle 401 (redirect to login)
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            window.location.href = '/login'
        }
        return Promise.reject(error)
    }
)

/**
 * Fetch CSRF cookie from Sanctum before first state-changing request.
 */
let csrfReady = null
export function ensureCsrf() {
    if (!csrfReady) {
        csrfReady = axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    }
    return csrfReady
}

/**
 * Set Bearer token for authenticated API calls.
 */
export function setAuthToken(token) {
    if (token) {
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`
    } else {
        delete api.defaults.headers.common['Authorization']
    }
}

export default api
