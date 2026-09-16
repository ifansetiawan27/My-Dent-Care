import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { authApi } from '@/modules/auth/api/authApi'
import type { AuthUser } from '@/modules/auth/api/authApi'
import { useAuth } from '@/core/auth/useAuth'
import { usePortal } from './usePortal'

/**
 * Shared chrome logic for the three portal layouts: loads the signed-in user's
 * profile (which carries the authoritative roles/permissions used for portal
 * selection) and exposes display state, navigation helpers and logout.
 */
export function usePortalChrome() {
  const route = useRoute()
  const router = useRouter()
  const { logout } = useAuth()
  const { portal } = usePortal()

  const userName = ref('')
  const userInitial = ref('U')

  /** True when the given module is the active route (or a descendant of it). */
  function isActive(module: string): boolean {
    const full = `${portal.value.prefix}/${module}`
    return route.path === full || route.path.startsWith(`${full}/`)
  }

  const pageTitle = computed(() => (route.meta.title as string) ?? 'Dashboard')

  function applyUser(u: AuthUser): void {
    userName.value = u.name ?? 'User'
    userInitial.value = (u.name ?? 'U').charAt(0).toUpperCase()
  }

  async function loadProfile(): Promise<void> {
    try {
      const profile = await authApi.profile()
      applyUser(profile)
      // Keep localStorage in sync; profile is the authoritative source for
      // roles/permissions and portal selection across reloads.
      localStorage.setItem('auth_user', JSON.stringify(profile))
    } catch {
      // Fall back to the cached login payload.
      try {
        const cached = JSON.parse(localStorage.getItem('auth_user') || '{}') as AuthUser
        if (cached?.name) applyUser(cached)
      } catch { /* no cached data; show defaults */ }
    }
  }

  onMounted(() => { loadProfile() })

  async function handleLogout(): Promise<void> {
    localStorage.removeItem('auth_user')
    await logout()
  }

  function go(path: string): void {
    router.push(path)
  }

  return {
    portal,
    userName,
    userInitial,
    isActive,
    pageTitle,
    handleLogout,
    go,
  }
}
