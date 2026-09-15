import axios from 'axios'
import router from '@/core/router'
import { normalizeError } from './errors'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  withCredentials: true, // Sanctum HttpOnly cookies
  timeout: 15000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
})

// Attach Bearer token from localStorage if present (SPA token auth fallback)
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const normalized = normalizeError(error)

    // 401 means the session is no longer valid (expired, revoked, or logged
    // out elsewhere). Clear the stale credentials and send the user back to
    // the login page. The `login` guard prevents a redirect loop when the
    // login endpoint itself rejects bad credentials.
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      if (router.currentRoute.value.name !== 'login') {
        await router.push({
          path: '/login',
          query: { redirect: router.currentRoute.value.fullPath },
        })
      }
    }

    return Promise.reject(normalized)
  },
)

export default api