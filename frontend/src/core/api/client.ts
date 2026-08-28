import axios from 'axios'
import { normalizeError } from './errors'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  withCredentials: true, // Sanctum HttpOnly cookies
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
  (error) => {
    const normalized = normalizeError(error)
    // 401 means session expired — clear stale token and let router handle redirect
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
    return Promise.reject(normalized)
  },
)

export default api