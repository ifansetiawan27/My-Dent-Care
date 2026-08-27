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

// No manual token management needed — Sanctum handles auth via HttpOnly cookies.
// The withCredentials: true flag ensures cookies are sent with every request.

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const normalized = normalizeError(error)
    // 401 means session expired — let the app router handle redirect
    return Promise.reject(normalized)
  },
)

export default api