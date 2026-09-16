import { computed } from 'vue'
import {
  PORTALS,
  PORTAL_LIST,
  DEFAULT_PORTAL,
  type PortalDef,
  type PortalId,
  type NavGroup,
} from './portalConfig'
import { MODULES } from './portalConfig'
import { useAuth } from '@/core/auth/useAuth'

/**
 * Resolve which portal a user belongs to from their Spatie roles.
 * Falls back to the admin portal when no role matches (the admin portal is the
 * most permissive, and a role-less user is typically a fresh admin account).
 */
export function portalForRoles(roles: string[] | undefined): PortalId {
  if (roles && roles.length > 0) {
    for (const role of roles) {
      const portal = PORTAL_LIST.find((p) => p.roles.includes(role))
      if (portal) return portal.id
    }
  }
  return DEFAULT_PORTAL
}

/** True when the user holds the permission a module requires. */
function canUseModule(moduleKey: string, permissions: string[]): boolean {
  const def = MODULES[moduleKey]
  if (!def) return false
  // super_admin gets every permission, but roles like "doctor" are narrow.
  // The backend is the real authority; this only controls nav visibility.
  return permissions.includes(def.permission)
}

export function usePortal() {
  const { user } = useAuth()

  const roles = computed<string[]>(() => user.value?.roles ?? [])
  const permissions = computed<string[]>(() => user.value?.permissions ?? [])

  const portalId = computed<PortalId>(() => portalForRoles(roles.value))
  const portal = computed<PortalDef>(() => PORTALS[portalId.value])

  /** Navigation groups for the active portal, filtered by permission. */
  const visibleNav = computed<NavGroup[]>(() => {
    const perms = permissions.value
    return portal.value.nav
      .map((group) => ({
        ...group,
        items: group.items.filter((item) => canUseModule(item.module, perms)),
      }))
      .filter((group) => group.items.length > 0)
  })

  /** Every module the user may open in this portal (route guard uses this). */
  const allowedModules = computed<string[]>(() => {
    const perms = permissions.value
    return portal.value.nav.flatMap((g) => g.items.map((i) => i.module))
      .filter((key) => canUseModule(key, perms))
  })

  const homePath = computed<string>(() => `${portal.value.prefix}/dashboard`)

  /** Absolute route path for a module inside the active portal. */
  function modulePath(module: string): string {
    return `${portal.value.prefix}/${module}`
  }

  return {
    roles,
    permissions,
    portalId,
    portal,
    visibleNav,
    allowedModules,
    homePath,
    modulePath,
  }
}
