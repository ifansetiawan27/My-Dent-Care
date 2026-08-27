import { ref } from 'vue'
import { authApi, type AuthUser } from '@/modules/auth/api/authApi'
import router from '@/core/router'

const user = ref<AuthUser | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)

export function useAuth() {
  // Auth status determined by Sanctum HttpOnly cookies — no localStorage needed
  const isAuthenticated = () => !!user.value

  async function login(identifier: string, password: string): Promise<void> {
    loading.value = true
    error.value = null
    try {
      // Step 1: lookup org_id & branch_id
      const lookup = await authApi.lookup(identifier)

      // Step 2: login dengan org & branch id yang benar
      // Sanctum will set HttpOnly cookies automatically — no token to store
      const data = await authApi.login(
        identifier,
        password,
        lookup.organization_id,
        lookup.branch_id,
      )
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
    user.value = null
    router.push('/login')
  }

  return { user, loading, error, login, logout, isAuthenticated }
}
