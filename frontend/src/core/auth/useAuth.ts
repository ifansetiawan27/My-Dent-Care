import { ref } from 'vue'
import { authApi, type AuthUser } from '@/modules/auth/api/authApi'
import router from '@/core/router'

const user = ref<AuthUser | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)

const TOKEN_KEY = 'auth_token'
const USER_KEY = 'auth_user'

function getStoredToken(): string | null {
  return localStorage.getItem(TOKEN_KEY)
}

function storeAuth(token: string, authUser: AuthUser): void {
  localStorage.setItem(TOKEN_KEY, token)
  localStorage.setItem(USER_KEY, JSON.stringify(authUser))
  user.value = authUser
}

function clearAuth(): void {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
  user.value = null
}

// Restore session on app load
const storedUser = localStorage.getItem(USER_KEY)
if (storedUser) {
  try {
    user.value = JSON.parse(storedUser) as AuthUser
  } catch { /* ignore */ }
}

export function useAuth() {
  const isAuthenticated = () => !!user.value && !!getStoredToken()

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
      storeAuth(data.token, data.user)
    } catch (e: any) {
      error.value = e?.message ?? 'Login gagal. Periksa email dan password Anda.'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function logout(): Promise<void> {
    try { await authApi.logout() } catch { /* ignore */ }
    clearAuth()
    router.push('/login')
  }

  return { user, loading, error, login, logout, isAuthenticated }
}
