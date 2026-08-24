import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'landing',
      component: () => import('@/modules/landing/LandingPage.vue'),
      meta: { guestOnly: false },
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/modules/dashboard/DashboardPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/modules/auth/LoginPage.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/subscription',
      name: 'subscription',
      component: () => import('@/modules/subscription/SubscriptionPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/settings',
      name: 'settings',
      component: () => import('@/modules/settings/SettingsPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
})

router.beforeEach((to, _from, next) => {
  const token = localStorage.getItem('auth_token')

  // Redirect authenticated users away from guest-only pages (e.g. /login)
  if (to.meta.guestOnly && token) {
    next({ name: 'dashboard' })
    return
  }

  // Redirect unauthenticated users away from protected pages
  if (to.meta.requiresAuth && !token) {
    next({ name: 'login' })
    return
  }

  next()
})

export default router