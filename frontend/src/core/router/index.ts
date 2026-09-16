import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import {
  PORTALS,
  PORTAL_LIST,
  MODULES,
  type PortalDef,
  type PortalId,
} from '@/core/portal/portalConfig'
import { portalForRoles } from '@/core/portal/usePortal'

/** Layout shell for each portal paradigm. */
const PORTAL_LAYOUTS: Record<PortalId, () => Promise<{ default: any }>> = {
  admin: () => import('@/core/layout/portal/AdminLayout.vue'),
  doctor: () => import('@/core/layout/portal/DoctorLayout.vue'),
  receptionist: () => import('@/core/layout/portal/ReceptionistLayout.vue'),
}

/** Build the route subtree for one portal from its declared navigation. */
function portalRoutes(portal: PortalDef): RouteRecordRaw {
  const children = portal.nav
    .flatMap((group) => group.items)
    .map((item) => {
      const mod = MODULES[item.module]
      // The dashboard is portal-specific; every other module shares its page.
      const component = item.module === 'dashboard' ? portal.dashboard : mod.component
      if (!component) {
        throw new Error(`Module "${item.module}" has no page component for portal "${portal.id}"`)
      }
      return {
        path: mod.path,
        name: `${portal.id}.${item.module}`,
        component,
        meta: {
          title: mod.meta?.title ?? mod.label,
          // ModulePage reads `meta.module` to pick its config.
          module: mod.meta?.module ?? item.module,
          portal: portal.id,
        },
      }
    })

  return {
    path: portal.prefix,
    component: PORTAL_LAYOUTS[portal.id],
    meta: { requiresAuth: true, portal: portal.id },
    children: [
      { path: '', redirect: { name: `${portal.id}.dashboard` } },
      ...children,
    ],
  }
}

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'landing',
      component: () => import('@/modules/landing/LandingPage.vue'),
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/modules/auth/LoginPage.vue'),
      meta: { guestOnly: true },
    },
    ...PORTAL_LIST.map((p) => portalRoutes(p)),
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
})

/** Read roles/permissions from the cached auth payload. */
function cachedAuth(): { roles: string[]; permissions: string[] } {
  try {
    const raw = JSON.parse(localStorage.getItem('auth_user') || '{}')
    return {
      roles: Array.isArray(raw.roles) ? raw.roles : [],
      permissions: Array.isArray(raw.permissions) ? raw.permissions : [],
    }
  } catch {
    return { roles: [], permissions: [] }
  }
}

router.beforeEach((to, _from, next) => {
  // Public pages.
  if (to.name === 'landing' || to.name === 'login') {
    next()
    return
  }

  const token = localStorage.getItem('auth_token')
  if (!token) {
    next({ path: '/login', query: { redirect: to.fullPath } })
    return
  }

  // Enforce the portal boundary: a user may only browse their own portal.
  const targetPortal = to.meta.portal as PortalId | undefined
  if (targetPortal) {
    const { roles, permissions } = cachedAuth()
    const userPortal = portalForRoles(roles)

    if (userPortal !== targetPortal) {
      next({ path: `${PORTALS[userPortal].prefix}/dashboard` })
      return
    }

    // Enforce module visibility inside the portal.
    const moduleKey = typeof to.name === 'string' ? to.name.split('.')[1] : undefined
    if (moduleKey) {
      const mod = MODULES[moduleKey]
      if (mod && !permissions.includes(mod.permission)) {
        next({ path: `${PORTALS[userPortal].prefix}/dashboard` })
        return
      }
    }
  }

  next()
})

export default router
