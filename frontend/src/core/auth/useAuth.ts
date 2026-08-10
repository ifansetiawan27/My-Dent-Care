import { ref } from 'vue'
import { authApi, type AuthUser } from '@/modules/auth/api/authApi'
import router from '@/core/router'

const user = ref<AuthUser | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)

export function useAuth() {
  const isAuthenticated = () => !!localStorage.getItem('auth_token')

  async function login(email: string, password: string): Promise<void> {
    loading.value = true
    error.value = null
    try {
      const data = await authApi.login(email, password)
      localStorage.setItem('auth_token', data.token)
      user.value = data.user
    } catch (e) {
      error.value = 'Login failed. Please check your credentials.'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function logout(): Promise<void> {
    try { await authApi.logout() } catch { /* ignore */ }
    localStorage.removeItem('auth_token')
    user.value = null
    router.push('/login')
  }

  return { user, loading, error, login, logout, isAuthenticated }
}