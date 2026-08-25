import { ref } from 'vue'
import { authApi, type AuthUser } from '@/modules/auth/api/authApi'
import router from '@/core/router'

const user = ref<AuthUser | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)

export function useAuth() {
  const isAuthenticated = () => !!localStorage.getItem('auth_token')

  async function login(identifier: string, password: string): Promise<void> {
    loading.value = true
    error.value = null
    try {
      // Step 1: lookup org_id & branch_id
      const lookup = await authApi.lookup(identifier)

      // Step 2: login dengan org & branch id yang benar
      const data = await authApi.login(
        identifier,
        password,
        lookup.organization_id,
        lookup.branch_id,
      )
      localStorage.setItem('auth_token', data.token)
      localStorage.setItem('auth_user', JSON.stringify(data.user))
      user.value = data.user
    } catch (e: any) {
      error.value = e?.message ?? 'Login gagal. Periksa email dan password Anda.'
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
